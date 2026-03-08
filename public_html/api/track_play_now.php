<?php
// /api/track_play_now.php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Range, X-API-Key');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Expose-Headers: Accept-Ranges, Content-Range, Content-Length');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
  http_response_code(204);
  exit;
}

require __DIR__ . '/_auth.php';
ij_api_require_access('track_play_now');

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

if (!function_exists('ij_db')) {
  out(['ok' => false, 'error' => 'DB not configured'], 500);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
  out(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$clientId = trim((string)($_GET['client_id'] ?? ''));
$clientId = $clientId !== '' ? substr($clientId, 0, 64) : '';

$nowWindowSec = (int)($_GET['window_sec'] ?? 25);
if ($nowWindowSec < 5) $nowWindowSec = 5;
if ($nowWindowSec > 120) $nowWindowSec = 120;

try {
  $pdo = ij_db();

  $args = [];
  $sql = 'SELECT id, ts, track_id, client_id, event_key, played_ms
          FROM track_plays';

  if ($clientId !== '') {
    $sql .= ' WHERE client_id = ?';
    $args[] = $clientId;
  }

  $sql .= ' ORDER BY id DESC LIMIT 1';

  $st = $pdo->prepare($sql);
  $st->execute($args);

  $row = $st->fetch(PDO::FETCH_ASSOC);
  if (!$row) out(['ok' => true, 'now' => false, 'row' => null]);

  $ts = (int)($row['ts'] ?? 0);
  $age = time() - $ts;

  out([
    'ok' => true,
    'now' => ($age >= 0 && $age <= $nowWindowSec),
    'age_sec' => $age,
    'row' => [
      'id' => (int)($row['id'] ?? 0),
      'ts' => $ts,
      'track_id' => (string)($row['track_id'] ?? ''),
      'client_id' => (string)($row['client_id'] ?? ''),
      'event_key' => $row['event_key'] !== null ? (string)$row['event_key'] : null,
      'played_ms' => (int)($row['played_ms'] ?? 0),
    ],
  ]);

} catch (Throwable $e) {
  out(['ok' => false, 'error' => 'Server error'], 500);
}