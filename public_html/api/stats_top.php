<?php
declare(strict_types=1);

require __DIR__ . '/../admin/lib.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require __DIR__ . '/_auth.php';
ij_api_require_access('stats_top');

if (!function_exists('ij_db')) {
  http_response_code(500);
  echo json_encode(['ok'=>false, 'error'=>'DB not configured'], JSON_UNESCAPED_UNICODE);
  exit;
}

$range = strtolower(trim((string)($_GET['range'] ?? 'week'))); // week|month
$limit = (int)($_GET['limit'] ?? 10);
$clientId = trim((string)($_GET['client_id'] ?? ''));

if ($limit < 1) $limit = 1;
if ($limit > 50) $limit = 50;

$days = 7;
if ($range === 'month') $days = 30;
if ($range === 'week') $days = 7;

// Notera: din nuvarande rollup är GLOBAL (ingen client_id i tabellen).
$clientId = ($clientId !== '') ? substr($clientId, 0, 64) : '';

try {
  $pdo = ij_db();

  // Hämta topplista från rollup-tabellen
  $st = $pdo->prepare("
    SELECT
      track_id,
      SUM(plays)   AS plays,
      SUM(ms_total) AS ms_total,
      MAX(last_ts) AS last_ts
    FROM track_play_rollup_day
    WHERE day_date >= (CURDATE() - INTERVAL ? DAY)
    GROUP BY track_id
    ORDER BY plays DESC
    LIMIT ?
  ");
  $st->execute([$days, $limit]);

  $top = $st->fetchAll(PDO::FETCH_ASSOC);

  // Map metadata from library.json if available
  // Förutsätter att lib.php definierar read_json() och $LIBRARY_PATH (som du redan använder)
  $lib = read_json($LIBRARY_PATH, ['items'=>[]]);
  $map = [];
  foreach (($lib['items'] ?? []) as $it) {
    if (!empty($it['id'])) $map[(string)$it['id']] = $it;
  }

  $out = [];
  foreach ($top as $row) {
    $tid = (string)($row['track_id'] ?? '');
    $plays = (int)($row['plays'] ?? 0);
    $it = $map[$tid] ?? null;

    $out[] = [
      'trackId' => $tid,
      'plays'   => $plays,

      // Extra (kan vara bra för debug/UI)
      'msTotal' => (int)($row['ms_total'] ?? 0),
      'lastTs'  => (int)($row['last_ts'] ?? 0),

      // Metadata från library.json
      'title'  => $it['title'] ?? null,
      'artist' => $it['artist'] ?? null,
      'genre'  => $it['genre'] ?? null,
      'image'  => $it['image'] ?? null,
      'url'    => $it['url'] ?? null,
    ];
  }

  echo json_encode([
    'ok' => true,
    'range' => $range,
    'days' => $days,

    // Vi accepterar parametern för kompatibilitet men rollupen är global just nu
    'clientId' => $clientId !== '' ? $clientId : null,
    'note' => ($clientId !== '')
      ? 'client_id filter is currently ignored because rollup is global (no client_id in rollup table).'
      : null,

    // updatedAt = när API svarar (cronens körning styr själva datats färskhet)
    'updatedAt' => gmdate('c'),
    'top' => $out
  ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false, 'error'=>'Server error'], JSON_UNESCAPED_UNICODE);
}