<?php
// /api/now_public.php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require __DIR__ . '/../admin/lib.php';

function out(array $arr, int $code = 200): void {
  http_response_code($code);
  echo json_encode($arr, JSON_UNESCAPED_UNICODE);
  exit;
}

if (!function_exists('ij_db')) {
  out(['ok' => false, 'error' => 'DB missing'], 500);
}

function build_label(?string $artist, ?string $title, string $fallback): string {
  $a = trim((string)$artist);
  $t = trim((string)$title);
  if ($a !== '' && $t !== '') return $a . ' – ' . $t;
  if ($t !== '') return $t;
  return $fallback !== '' ? $fallback : '–';
}

// Ditt ID-format: 14 siffror + "-" + 6 hex
function is_canonical_track_id(string $s): bool {
  $s = trim($s);
  return (bool)preg_match('~^\d{14}-[a-f0-9]{6}$~i', $s);
}

function norm_ci(string $s): string {
  $s = trim(mb_strtolower($s, 'UTF-8'));
  $s = preg_replace('/\s+/', ' ', $s) ?? $s;
  return $s;
}

/**
 * Ladda library.json en gång.
 * Returnerar:
 * [
 *   'baseUrl' => 'https://icebeats.io',
 *   'items'   => [...]
 * ]
 */
function load_library(): ?array {
  static $cache = null;
  if ($cache !== null) return $cache;

  $libPath = realpath(__DIR__ . '/../library.json') ?: (__DIR__ . '/../library.json');
  if (!is_file($libPath)) {
    $cache = null;
    return null;
  }

  $json = json_decode((string)file_get_contents($libPath), true);
  if (!is_array($json)) {
    $cache = null;
    return null;
  }

  $cache = [
    'baseUrl' => (string)($json['baseUrl'] ?? ''),
    'items'   => is_array($json['items'] ?? null) ? $json['items'] : [],
  ];
  return $cache;
}

function resolve_library_url(string $baseUrl, string $u): string {
  $u = trim($u);
  if ($u === '') return '';
  if (preg_match('~^https?://~i', $u)) return $u;
  return rtrim($baseUrl, '/') . '/' . ltrim($u, '/');
}

/**
 * Försök hitta rätt track i library.json.
 * Prio:
 * 1) exakt id-match
 * 2) title + artist
 * 3) title
 *
 * Returnerar ett item från library.json eller null.
 */
function lookup_in_library(?string $trackId, ?string $title, ?string $artist): ?array {
  $lib = load_library();
  if (!$lib) return null;

  $items = $lib['items'] ?? [];
  if (!$items) return null;

  $tid = trim((string)$trackId);
  $t   = trim((string)$title);
  $a   = trim((string)$artist);

  // 1) ID-match
  if ($tid !== '') {
    foreach ($items as $it) {
      if (!is_array($it)) continue;
      if ((string)($it['id'] ?? '') === $tid) return $it;
    }
  }

  // 2) title (+ artist)
  $tn = norm_ci($t !== '' ? $t : $tid);
  $an = norm_ci($a);

  if ($tn !== '') {
    if ($an !== '') {
      foreach ($items as $it) {
        if (!is_array($it)) continue;
        $itT = norm_ci((string)($it['title'] ?? ''));
        $itA = norm_ci((string)($it['artist'] ?? ''));
        if ($itT === $tn && $itA === $an) return $it;
      }
    }

    foreach ($items as $it) {
      if (!is_array($it)) continue;
      $itT = norm_ci((string)($it['title'] ?? ''));
      if ($itT === $tn) return $it;
    }
  }

  return null;
}

/**
 * Bygg publik klick-url från library-item.
 */
function public_track_url(array $item, string $lang = ''): string {
  $id = trim((string)($item['id'] ?? ''));
  if ($id !== '' && is_canonical_track_id($id)) {
    $u = '/track/?id=' . rawurlencode($id);
    if ($lang === 'sv' || $lang === 'en') {
      $u .= '&lang=' . rawurlencode($lang);
    }
    return $u;
  }

  $q = trim((string)($item['title'] ?? ''));
  if ($q === '') $q = trim((string)($item['id'] ?? ''));
  $u = '/library/?q=' . rawurlencode($q);
  if ($lang === 'sv' || $lang === 'en') {
    $u .= '&lang=' . rawurlencode($lang);
  }
  return $u;
}

/**
 * Hämta senaste PUBLIKA spelningen.
 * Vi kollar ett antal senaste rader och väljer första som går att matcha mot library.json.
 * Då ignoreras lokala filer helt i public now playing.
 */
function find_latest_public_row(PDO $pdo, int $lookbackRows = 50): ?array {
  $lookbackRows = max(1, min(200, $lookbackRows));

  $sql = "
    SELECT id, ts, track_id, title, artist, track_url
    FROM track_plays
    ORDER BY id DESC
    LIMIT {$lookbackRows}
  ";
  $st = $pdo->query($sql);
  $rows = $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];

  if (!$rows) return null;

  foreach ($rows as $row) {
    $trackId = trim((string)($row['track_id'] ?? ''));
    $title   = trim((string)($row['title'] ?? ''));
    $artist  = trim((string)($row['artist'] ?? ''));

    $hit = lookup_in_library($trackId, $title, $artist);
    if ($hit && is_array($hit)) {
      return [
        'row' => $row,
        'item' => $hit,
      ];
    }
  }

  return null;
}

try {
  $pdo = ij_db();

  $lang = strtolower(trim((string)($_GET['lang'] ?? '')));
  if ($lang !== 'sv' && $lang !== 'en') $lang = '';

  $found = find_latest_public_row($pdo, 50);

  // Ingen publik spelning hittad alls
  if (!$found) {
    out([
      'ok' => true,
      'now' => false,
      'age' => 999999,
      'track_id' => '',
      'label' => '–',
      'url' => '',
    ]);
  }

  $row = $found['row'];
  $hit = $found['item'];

  $ts  = (int)($row['ts'] ?? 0);
  $age = time() - $ts;
  $now = ($age >= 0 && $age <= 30);

  $resolvedId = trim((string)($hit['id'] ?? ''));
  $title      = trim((string)($hit['title'] ?? ($row['title'] ?? '')));
  $artist     = trim((string)($hit['artist'] ?? ($row['artist'] ?? '')));
  $label      = build_label($artist, $title, $resolvedId);

  $finalUrl = public_track_url($hit, $lang);

  out([
    'ok' => true,
    'now' => $now,
    'age' => $age,
    'track_id' => $resolvedId,
    'label' => $label,
    'url' => $finalUrl,
  ]);

} catch (Throwable $e) {
  out(['ok' => false, 'error' => 'Server error'], 500);
}