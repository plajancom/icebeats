<?php
declare(strict_types=1);

// --- Web protection (optional manual trigger) ---
$secret = 'ijRollup_8hJ29KsPq7LmN4tX';

if (PHP_SAPI !== 'cli') {
  if (!isset($_GET['key']) || $_GET['key'] !== $secret) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
  }
}

// /admin/cron_rollup.php
// Inkrementell rollup (snål): tar bara nya track_plays sedan senaste last_id.
// Loggar output till STDOUT (som du redan redirectar till cron_rollup_out.log).

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');

require __DIR__ . '/lib.php';
if (!function_exists('ij_db')) {
  http_response_code(500);
  echo "no-db\n";
  exit;
}

$pdo = ij_db();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Snäll budget per körning
$BATCH = 2000;

// Förhindra dubbelkörning (nice-to-have)
$locked = false;
try {
  $pdo->query("SELECT GET_LOCK('ij_rollup_lock', 1)");
  $locked = true;
} catch (Throwable $e) {
  // Om hosten inte tillåter GET_LOCK, kör ändå.
  // (Inte fatal)
}

// Läs state
$st = $pdo->query("SELECT last_id FROM track_rollup_state WHERE id=1");
$lastId = (int)($st->fetchColumn() ?: 0);

// Hämta nya rows sedan sist
$q = $pdo->prepare("
  SELECT id, ts, track_id, played_ms
  FROM track_plays
  WHERE id > ?
  ORDER BY id ASC
  LIMIT $BATCH
");
$q->execute([$lastId]);
$rows = $q->fetchAll(PDO::FETCH_ASSOC);

if (!$rows) {
  $pdo->exec("UPDATE track_rollup_state SET updated_ts=UNIX_TIMESTAMP() WHERE id=1");
  if ($locked) {
    try { $pdo->query("SELECT RELEASE_LOCK('ij_rollup_lock')"); } catch (Throwable $e) {}
  }
  echo "ok: nothing new\n";
  exit;
}

// Aggregera i PHP (minimerar DB-queries)
$agg = [];
$maxId = $lastId;

foreach ($rows as $r) {
  $id = (int)$r['id'];
  $ts = (int)$r['ts'];
  $track = (string)$r['track_id'];
  $ms = (int)($r['played_ms'] ?? 0);

  if ($id > $maxId) $maxId = $id;

  $day = date('Y-m-d', $ts);
  $k = $day . '|' . $track;

  if (!isset($agg[$k])) {
    $agg[$k] = ['day'=>$day, 'track'=>$track, 'plays'=>0, 'ms'=>0, 'last_ts'=>0];
  }
  $agg[$k]['plays'] += 1;
  $agg[$k]['ms'] += max(0, $ms);
  if ($ts > $agg[$k]['last_ts']) $agg[$k]['last_ts'] = $ts;
}

$pdo->beginTransaction();

$ins = $pdo->prepare("
  INSERT INTO track_play_rollup_day (day_date, track_id, plays, ms_total, last_ts)
  VALUES (?, ?, ?, ?, ?)
  ON DUPLICATE KEY UPDATE
    plays = plays + VALUES(plays),
    ms_total = ms_total + VALUES(ms_total),
    last_ts = GREATEST(last_ts, VALUES(last_ts))
");

foreach ($agg as $a) {
  $ins->execute([$a['day'], $a['track'], $a['plays'], $a['ms'], $a['last_ts']]);
}

// Uppdatera state
$now = time();
$lastTs = (int)($rows[count($rows) - 1]['ts'] ?? 0);

$upd = $pdo->prepare("UPDATE track_rollup_state SET last_id=?, last_ts=?, updated_ts=? WHERE id=1");
$upd->execute([$maxId, $lastTs, $now]);

$pdo->commit();

if ($locked) {
  try { $pdo->query("SELECT RELEASE_LOCK('ij_rollup_lock')"); } catch (Throwable $e) {}
}

echo "ok: processed " . count($rows) . " rows, advanced to id=" . $maxId . "\n";