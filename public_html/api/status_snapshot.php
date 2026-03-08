<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function out(array $arr, int $code = 200): void {
  http_response_code($code);
  echo json_encode($arr, JSON_UNESCAPED_UNICODE);
  exit;
}

$root = realpath(__DIR__ . '/..') ?: dirname(__DIR__);
$dataDir = $root . '/data';
$historyPath = $dataDir . '/status_history.json';

if (!is_dir($dataDir)) {
  if (!@mkdir($dataDir, 0775, true) && !is_dir($dataDir)) {
    out(['ok' => false, 'error' => 'Could not create data dir'], 500);
  }
}

$healthUrl = ((isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== '')
  ? (((
      (!empty($_SERVER['HTTP_CF_VISITOR']) && (($j = json_decode((string)$_SERVER['HTTP_CF_VISITOR'], true)) && is_array($j) && (($j['scheme'] ?? '') === 'https'))) ||
      (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') ||
      (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
      ((int)($_SERVER['SERVER_PORT'] ?? 80) === 443)
    ) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'])
  : 'https://icebeats.io') . '/api/health.php';

$t0 = microtime(true);
$ctx = stream_context_create([
  'http' => [
    'method' => 'GET',
    'timeout' => 10,
    'ignore_errors' => true,
    'header' => "Cache-Control: no-store\r\n",
  ],
]);
$raw = @file_get_contents($healthUrl, false, $ctx);
$latencyMs = (int)round((microtime(true) - $t0) * 1000);

if ($raw === false || $raw === '') {
  out(['ok' => false, 'error' => 'Could not fetch /api/health.php'], 500);
}

$health = json_decode($raw, true);
if (!is_array($health)) {
  out(['ok' => false, 'error' => 'Invalid JSON from /api/health.php'], 500);
}

$snapshot = [
  'ts' => time(),
  'ok' => (bool)($health['ok'] ?? false),
  'latencyMs' => $latencyMs,
  'tracks' => (int)($health['library']['tracks'] ?? 0),
  'librarySizeBytes' => (int)($health['library']['sizeBytes'] ?? 0),
  'libraryUpdated' => isset($health['library']['updated']) ? (int)$health['library']['updated'] : null,
  'latestPlayTs' => isset($health['db']['latestPlayTs']) ? (int)$health['db']['latestPlayTs'] : null,
  'latestPlayTrackId' => (string)($health['db']['latestPlayTrackId'] ?? ''),
  'latestRollupTs' => isset($health['db']['latestRollupTs']) ? (int)$health['db']['latestRollupTs'] : null,
  'latestRollupDay' => (string)($health['db']['latestRollupDay'] ?? ''),
  'checks' => [],
];

foreach (($health['checks'] ?? []) as $key => $val) {
  if (!is_array($val)) continue;
  $snapshot['checks'][$key] = [
    'ok' => (bool)($val['ok'] ?? false),
    'updated' => isset($val['updated']) ? (int)$val['updated'] : null,
  ];
}

$fp = @fopen($historyPath, 'c+');
if (!$fp) {
  out(['ok' => false, 'error' => 'Could not open history file'], 500);
}

try {
  if (!flock($fp, LOCK_EX)) {
    throw new RuntimeException('Could not lock history file');
  }

  rewind($fp);
  $existingRaw = stream_get_contents($fp);
  $history = json_decode((string)$existingRaw, true);
  if (!is_array($history)) $history = [];

  $history[] = $snapshot;

  // Behåll ca 7 dygn om cron kör var 5:e minut: 12 * 24 * 7 = 2016
  $maxItems = 2016;
  if (count($history) > $maxItems) {
    $history = array_slice($history, -$maxItems);
  }

  $json = json_encode($history, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
  if ($json === false) {
    throw new RuntimeException('Could not encode history JSON');
  }

  ftruncate($fp, 0);
  rewind($fp);
  fwrite($fp, $json);
  fflush($fp);
  flock($fp, LOCK_UN);
  fclose($fp);

  out([
    'ok' => true,
    'saved' => true,
    'path' => $historyPath,
    'items' => count($history),
    'snapshot' => $snapshot,
  ]);
} catch (Throwable $e) {
  @flock($fp, LOCK_UN);
  @fclose($fp);
  out(['ok' => false, 'error' => $e->getMessage()], 500);
}