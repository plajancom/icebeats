<?php
require __DIR__ . '/../admin/lib.php';
header('Content-Type: text/plain');

$pdo = ij_db();

$st = $pdo->prepare('
  INSERT INTO track_plays (ts, track_id, client_id, event_key, played_ms)
  VALUES (?, ?, ?, ?, ?)
');

$st->execute([
  time(),
  'manual-test-track',
  'top_web',
  'test',
  5000
]);

echo "Inserted ID: " . $pdo->lastInsertId();