<?php
// /api/track_play.php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/../_partials/config.php';
require __DIR__ . '/_auth.php';
ij_api_require_access('track_play');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require __DIR__ . '/../admin/lib.php';

function out(array $arr, int $code = 200): void {
  http_response_code($code);
  echo json_encode($arr, JSON_UNESCAPED_UNICODE);
  exit;
}

function log_to_file(string $msg): string {
  $id = date('Ymd-His') . '-' . substr(bin2hex(random_bytes(6)), 0, 12);
  $line = '[' . date('c') . '] #' . $id . ' ' . $msg . "\n";
  $path = __DIR__ . '/track_play_error.log';
  @file_put_contents($path, $line, FILE_APPEND);
  return $id;
}

function norm_str(?string $s, int $max): ?string {
  $s = trim((string)$s);
  if ($s === '') return null;
  if (function_exists('mb_strlen') && function_exists('mb_substr')) {
    if (mb_strlen($s, 'UTF-8') > $max) $s = mb_substr($s, 0, $max, 'UTF-8');
  } else {
    if (strlen($s) > $max) $s = substr($s, 0, $max);
  }
  return $s;
}

function norm_url(?string $u, int $max = 255): ?string {
  $u = trim((string)$u);
  if ($u === '') return null;
  if (strlen($u) > $max) $u = substr($u, 0, $max);
  if (!preg_match('~^https?://~i', $u)) return null;
  return $u;
}

function make_default_track_url(string $trackId): ?string {
  $trackId = trim($trackId);
  if ($trackId === '' || $trackId === '-') return null;
  return ij_abs('/library/?q=' . rawurlencode($trackId));
}

/**
 * ✅ library.json ligger i public root:
 * /public_html/library.json
 */
function ij_audio_catalog_path(): string {
  $p = realpath(__DIR__ . '/../library.json');
  if ($p) return $p;
  return __DIR__ . '/../library.json';
}

/**
 * Läs JSON-katalogen och slå upp meta för en track_id.
 * Klarar både:
 *  - { items: [ ... ] }
 *  - [ ... ]
 */
function lookup_from_json_catalog(string $trackId): ?array {
  $trackId = trim($trackId);
  if ($trackId === '' || $trackId === '-') return null;

  $path = ij_audio_catalog_path();
  if (!is_file($path)) return null;

  $raw = @file_get_contents($path);
  if (!$raw) return null;

  $j = json_decode($raw, true);
  if (!is_array($j)) return null;

  $items = $j['items'] ?? null;
  if (!is_array($items)) {
    // om filen är en ren array
    $items = $j;
  }
  if (!is_array($items)) return null;

  foreach ($items as $it) {
    if (!is_array($it)) continue;
    $id = (string)($it['id'] ?? '');
    if ($id !== $trackId) continue;

    $title  = norm_str((string)($it['title'] ?? ''), 190);
    $artist = norm_str((string)($it['artist'] ?? ''), 190);

    // Klick ska gå till låtsök på audio-sidan (stabilt)
    $trackUrl = make_default_track_url($trackId);

    return [
      'title' => $title,
      'artist' => $artist,
      'track_url' => $trackUrl,
    ];
  }

  return null;
}

if (!function_exists('ij_db')) {
  out(['ok'=>false, 'error'=>'DB not configured (ij_db missing)'], 500);
}

if (isset($_GET['test'])) {
  out([
    'ok' => true,
    'msg' => 'track_play.php alive',
    'method' => $_SERVER['REQUEST_METHOD'] ?? '',
    'php' => PHP_VERSION,
    'catalog_path' => ij_audio_catalog_path(),
    'catalog_exists' => is_file(ij_audio_catalog_path()),
  ]);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  out(['ok'=>false, 'error'=>'Method not allowed'], 405);
}

// Payload
$trackId  = trim((string)($_POST['track_id'] ?? ''));
$clientId = trim((string)($_POST['client_id'] ?? ''));
$eventKey = trim((string)($_POST['event_key'] ?? ''));
$playedMs = (int)($_POST['played_ms'] ?? 0);

// valfria fält
$title    = norm_str((string)($_POST['title'] ?? ''), 190);
$artist   = norm_str((string)($_POST['artist'] ?? ''), 190);
$trackUrl = norm_url((string)($_POST['track_url'] ?? ''), 255);

if ($trackId === '') $trackId = '-';
if ($clientId === '') $clientId = 'unknown';

$trackId  = substr($trackId, 0, 190);
$clientId = substr($clientId, 0, 64);
$eventKey = ($eventKey !== '') ? substr($eventKey, 0, 64) : null;

// Anti-spam
if ($playedMs > 0 && $playedMs < 800) {
  out(['ok'=>true, 'ignored'=>true, 'reason'=>'too_short']);
}

$ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
$ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
$ts = time();

try {
  $pdo = ij_db();

  // ✅ Om title/artist saknas → fyll från library.json
  if (($title === null || $title === '') && ($artist === null || $artist === '')) {
    $hit = lookup_from_json_catalog($trackId);
    if ($hit) {
      $title = $hit['title'] ?? $title;
      $artist = $hit['artist'] ?? $artist;
      $trackUrl = $hit['track_url'] ?? $trackUrl;
    }
  }

  // ✅ URL fallback
  if ($trackUrl === null) {
    $trackUrl = make_default_track_url($trackId);
  }

  $st = $pdo->prepare('
    INSERT INTO track_plays (ts, track_id, client_id, event_key, played_ms, ip, ua, title, artist, track_url)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  ');

  $st->execute([
    $ts,
    $trackId,
    $clientId,
    $eventKey,
    $playedMs,
    substr($ip, 0, 64),
    substr($ua, 0, 255),
    $title ?: null,
    $artist ?: null,
    $trackUrl ?: null,
  ]);

  out([
    'ok'=>true,
    'id'=>(int)$pdo->lastInsertId(),
    'ts'=>$ts,
    'saved'=>[
      'track_id'=>$trackId,
      'title'=>$title ?: null,
      'artist'=>$artist ?: null,
      'track_url'=>$trackUrl ?: null,
      'catalog_path'=>ij_audio_catalog_path(),
      'catalog_exists'=>is_file(ij_audio_catalog_path()),
    ]
  ]);

} catch (Throwable $e) {
  $msg = $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine();
  $logId = log_to_file($msg);
  out(['ok'=>false, 'error'=>'Server error', 'logId'=>$logId], 500);
}