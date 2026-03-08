<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/../admin/lib.php';

$ok = true;
$checks = [];
$meta = [];

function set_check(array &$checks, string $key, bool $isOk, array $extra = []): void {
  $checks[$key] = array_merge(['ok' => $isOk], $extra);
}

function is_assoc(array $a): bool {
  return array_keys($a) !== range(0, count($a) - 1);
}

function try_json_file(string $path): ?array {
  if (!is_file($path)) return null;
  $raw = @file_get_contents($path);
  if ($raw === false || $raw === '') return null;

  $json = json_decode($raw, true);
  return is_array($json) ? $json : null;
}

function fmt_bytes_int(int $bytes): int {
  return max(0, $bytes);
}

$meta['time'] = time();
$meta['php'] = PHP_VERSION;

/* =========================
   library.json
   ========================= */
$libPath = realpath(__DIR__ . '/../library.json') ?: (__DIR__ . '/../library.json');

if (is_file($libPath)) {
  $libUpdated = @filemtime($libPath) ?: null;
  $libSize = @filesize($libPath);
  if ($libSize === false) $libSize = 0;

  $libJson = try_json_file($libPath);
  $items = [];

  if (is_array($libJson)) {
    if (isset($libJson['items']) && is_array($libJson['items'])) {
      $items = $libJson['items'];
    } elseif (!is_assoc($libJson)) {
      $items = $libJson;
    }
  }

  set_check($checks, 'library', true, [
    'path' => $libPath,
    'updated' => $libUpdated,
    'sizeBytes' => fmt_bytes_int((int)$libSize),
    'tracks' => count($items),
    'jsonOk' => is_array($libJson),
  ]);

  $meta['library'] = [
    'updated' => $libUpdated,
    'sizeBytes' => fmt_bytes_int((int)$libSize),
    'tracks' => count($items),
  ];
} else {
  $ok = false;
  set_check($checks, 'library', false, [
    'path' => $libPath,
  ]);
}

/* =========================
   endpoint files
   ========================= */
$statsPath = __DIR__ . '/stats_top.php';
$uploadPath = __DIR__ . '/upload.php';
$trackPlayPath = __DIR__ . '/track_play.php';

$statsOk = is_file($statsPath);
$uploadOk = is_file($uploadPath);
$trackPlayOk = is_file($trackPlayPath);

set_check($checks, 'stats_top', $statsOk, ['path' => $statsPath]);
set_check($checks, 'upload', $uploadOk, ['path' => $uploadPath]);
set_check($checks, 'track_play', $trackPlayOk, ['path' => $trackPlayPath]);

if (!$statsOk || !$uploadOk || !$trackPlayOk) {
  $ok = false;
}

/* =========================
   storage
   ========================= */
$tracksDir = realpath(__DIR__ . '/../tracks') ?: (__DIR__ . '/../tracks');
$coversDir = realpath(__DIR__ . '/../covers') ?: (__DIR__ . '/../covers');

$tracksDirOk = is_dir($tracksDir);
$coversDirOk = is_dir($coversDir);

$storageOk = $tracksDirOk && $coversDirOk;

set_check($checks, 'storage', $storageOk, [
  'tracksDir' => $tracksDir,
  'tracksDirOk' => $tracksDirOk,
  'coversDir' => $coversDir,
  'coversDirOk' => $coversDirOk,
]);

if (!$storageOk) {
  $ok = false;
}

/* =========================
   DB + senaste spelning / rollup
   ========================= */
$dbOk = false;
$latestPlay = null;
$latestRollup = null;

if (function_exists('ij_db')) {
  try {
    $pdo = ij_db();
    $dbOk = (bool)$pdo;

    if ($dbOk) {
      // Senaste spelning
      try {
        $st = $pdo->query("
          SELECT id, ts, track_id, client_id, event_key, played_ms
          FROM track_plays
          ORDER BY id DESC
          LIMIT 1
        ");
        $latestPlay = $st ? $st->fetch(PDO::FETCH_ASSOC) : null;
      } catch (Throwable $e) {
        $latestPlay = null;
      }

      // Senaste rollup
      try {
        $st = $pdo->query("
          SELECT day_date, last_ts
          FROM track_play_rollup_day
          ORDER BY last_ts DESC
          LIMIT 1
        ");
        $latestRollup = $st ? $st->fetch(PDO::FETCH_ASSOC) : null;
      } catch (Throwable $e) {
        $latestRollup = null;
      }
    }
  } catch (Throwable $e) {
    $dbOk = false;
  }
}

set_check($checks, 'db', $dbOk, [
  'latestPlayTs' => isset($latestPlay['ts']) ? (int)$latestPlay['ts'] : null,
  'latestPlayTrackId' => isset($latestPlay['track_id']) ? (string)$latestPlay['track_id'] : null,
  'latestPlayClientId' => isset($latestPlay['client_id']) ? (string)$latestPlay['client_id'] : null,
  'latestRollupTs' => isset($latestRollup['last_ts']) ? (int)$latestRollup['last_ts'] : null,
  'latestRollupDay' => isset($latestRollup['day_date']) ? (string)$latestRollup['day_date'] : null,
]);

if (!$dbOk) {
  $ok = false;
}

$meta['db'] = [
  'latestPlayTs' => isset($latestPlay['ts']) ? (int)$latestPlay['ts'] : null,
  'latestPlayTrackId' => isset($latestPlay['track_id']) ? (string)$latestPlay['track_id'] : null,
  'latestRollupTs' => isset($latestRollup['last_ts']) ? (int)$latestRollup['last_ts'] : null,
  'latestRollupDay' => isset($latestRollup['day_date']) ? (string)$latestRollup['day_date'] : null,
];

/* =========================
   Response
   ========================= */
echo json_encode([
  'ok' => $ok,
  'time' => $meta['time'],
  'php' => $meta['php'],
  'library' => $meta['library'] ?? null,
  'db' => $meta['db'] ?? null,
  'checks' => $checks,
], JSON_UNESCAPED_UNICODE);