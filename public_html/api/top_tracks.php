<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

require __DIR__ . '/../admin/lib.php';

if (!function_exists('ij_db')) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'no-db']);
  exit;
}

$pdo = ij_db();

$days = (int)($_GET['days'] ?? 1);
$limit = (int)($_GET['limit'] ?? 20);

if ($days < 1) $days = 1;
if ($limit < 1 || $limit > 100) $limit = 20;

if ($days === 1) {

  $st = $pdo->prepare("
    SELECT track_id, plays, ms_total, last_ts
    FROM track_play_rollup_day
    WHERE day_date = CURDATE()
    ORDER BY plays DESC
    LIMIT ?
  ");
  $st->execute([$limit]);
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);

} else {

  $st = $pdo->prepare("
    SELECT track_id,
           SUM(plays) AS plays,
           SUM(ms_total) AS ms_total,
           MAX(last_ts) AS last_ts
    FROM track_play_rollup_day
    WHERE day_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
    GROUP BY track_id
    ORDER BY plays DESC
    LIMIT ?
  ");
  $st->execute([$days, $limit]);
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode([
  'ok' => true,
  'days' => $days,
  'data' => $rows
], JSON_UNESCAPED_UNICODE);