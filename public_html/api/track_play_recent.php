<?php
// /api/track_play_recent.php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// =====================
// CORS / PRE-FLIGHT
// =====================
$origin = (string)($_SERVER['HTTP_ORIGIN'] ?? '');
$allowOrigins = [
  'https://icejockey.app',
  'https://www.icejockey.app',
  'https://audio.icejockey.app',
  'http://127.0.0.1:5173',
  'http://localhost:5173',
];

if ($origin && in_array($origin, $allowOrigins, true)) {
  header('Access-Control-Allow-Origin: ' . $origin);
  header('Vary: Origin');
} else {
  header('Access-Control-Allow-Origin: *');
}

header('Access-Control-Allow-Headers: Content-Type, Range, X-API-Key');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Expose-Headers: Accept-Ranges, Content-Range, Content-Length');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
  http_response_code(204);
  exit;
}

// =====================
// Auth
// =====================
require __DIR__ . '/_auth.php';
ij_api_require_access('track_play_recent');

// =====================
// JSON response + no-cache
// =====================
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
  out(['ok' => false, 'error' => 'DB not configured (ij_db missing)'], 500);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
  out(['ok' => false, 'error' => 'Method not allowed'], 405);
}

// Params
$limit = (int)($_GET['limit'] ?? 20);
if ($limit < 1) $limit = 1;
if ($limit > 200) $limit = 200;

$clientId = trim((string)($_GET['client_id'] ?? ''));
$trackId  = trim((string)($_GET['track_id'] ?? ''));

$clientId = $clientId !== '' ? substr($clientId, 0, 64) : '';
$trackId  = $trackId !== '' ? substr($trackId, 0, 190) : '';

try {
  $pdo = ij_db();

  $where = [];
  $args  = [];

  if ($clientId !== '') {
    $where[] = 'client_id = ?';
    $args[] = $clientId;
  }
  if ($trackId !== '') {
    $where[] = 'track_id = ?';
    $args[] = $trackId;
  }

  $sql = 'SELECT id, ts, track_id, client_id, event_key, played_ms, ip, ua
          FROM track_plays';

  if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);

  $sql .= ' ORDER BY id DESC LIMIT ' . (int)$limit;

  $st = $pdo->prepare($sql);
  $st->execute($args);

  $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

  // Typ-säkra lite
  foreach ($rows as &$r) {
    $r['id'] = (int)($r['id'] ?? 0);
    $r['ts'] = (int)($r['ts'] ?? 0);
    $r['played_ms'] = (int)($r['played_ms'] ?? 0);
    $r['track_id'] = (string)($r['track_id'] ?? '');
    $r['client_id'] = (string)($r['client_id'] ?? '');
    $r['event_key'] = $r['event_key'] !== null ? (string)$r['event_key'] : null;
    $r['ip'] = (string)($r['ip'] ?? '');
    $r['ua'] = (string)($r['ua'] ?? '');
  }
  unset($r);

  out([
    'ok' => true,
    'count' => count($rows),
    'limit' => $limit,
    'filters' => [
      'client_id' => $clientId !== '' ? $clientId : null,
      'track_id'  => $trackId !== '' ? $trackId : null,
    ],
    'rows' => $rows,
  ]);

} catch (Throwable $e) {
  out(['ok' => false, 'error' => 'Server error'], 500);
}