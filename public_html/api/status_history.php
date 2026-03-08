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
$historyPath = $root . '/data/status_history.json';

if (!is_file($historyPath)) {
  out([
    'ok' => true,
    'count' => 0,
    'history' => [],
  ]);
}

$raw = @file_get_contents($historyPath);
if ($raw === false || $raw === '') {
  out(['ok' => false, 'error' => 'Could not read history file'], 500);
}

$history = json_decode($raw, true);
if (!is_array($history)) {
  out(['ok' => false, 'error' => 'Invalid history JSON'], 500);
}

$limit = (int)($_GET['limit'] ?? 288);
if ($limit < 1) $limit = 1;
if ($limit > 5000) $limit = 5000;

$outHistory = array_slice($history, -$limit);

out([
  'ok' => true,
  'count' => count($outHistory),
  'history' => array_values($outHistory),
]);