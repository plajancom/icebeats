<?php
// /api/stats_plays.php
declare(strict_types=1);

require __DIR__ . '/../admin/lib.php';

header('Content-Type: application/json; charset=utf-8');

if (!function_exists('ij_db')) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'DB not configured'], JSON_UNESCAPED_UNICODE);
  exit;
}

$range = strtolower(trim((string)($_GET['range'] ?? 'week'))); // week|month
$days = ($range === 'month') ? 30 : 7;

try {
  $pdo = ij_db();

  // Summera från rollup-tabellen (snabbt)
  // day_date är DATE (YYYY-MM-DD)
  $sql = "
    SELECT track_id, SUM(plays) AS plays
    FROM track_play_rollup_day
    WHERE day_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
    GROUP BY track_id
  ";

  $st = $pdo->prepare($sql);
  $st->bindValue(':days', $days, PDO::PARAM_INT);
  $st->execute();

  $map = [];
  while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
    $tid = (string)($r['track_id'] ?? '');
    if ($tid === '') continue;
    $map[$tid] = (int)($r['plays'] ?? 0);
  }

  echo json_encode([
    'ok' => true,
    'range' => ($days === 30 ? 'month' : 'week'),
    'days' => $days,
    'updatedAt' => date('c'),
    'count' => count($map),
    'playsByTrackId' => $map,
  ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Server error'], JSON_UNESCAPED_UNICODE);
}