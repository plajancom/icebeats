<?php
// /domains/audio.icejockey.app/public_html/admin/cron_rollup.php
declare(strict_types=1);

// Kör via cron t.ex:
// */10 * * * * /usr/bin/php /domains/audio.icejockey.app/public_html/admin/cron_rollup.php >/dev/null 2>&1

require __DIR__ . '/lib.php';

// ======= Konfig =======
const IJ_ROLLUP_CACHE_DIR = __DIR__ . '/../api/cache';
const IJ_WEEK_DAYS  = 7;
const IJ_MONTH_DAYS = 30;

// Om du *vet* exakt tabell/kolumn kan du låsa dem här:
// const IJ_FORCE_TABLE = 'ij_track_plays';
// const IJ_FORCE_TS_COL = 'created_at';
// const IJ_FORCE_TRACK_COL = 'track_id';

// Annars: auto-detektering (default)
const IJ_TABLE_CANDIDATES = [
  'ij_track_plays',
  'track_plays',
  'ij_plays',
  'plays',
  'trackplays',
  'ij_trackplay',
];

const IJ_TS_COL_CANDIDATES = [
  'created_at', 'createdAt',
  'played_at', 'playedAt',
  'ts', 'time', 't',
];

const IJ_TRACK_COL_CANDIDATES = [
  'track_id', 'trackId',
  'track', 'id',
];

// ======= Helpers =======
function ij_json_out(array $arr): void {
  echo json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
}

function ij_mkdirp(string $dir): void {
  if (!is_dir($dir)) @mkdir($dir, 0755, true);
}

function ij_driver(PDO $pdo): string {
  try { return (string)$pdo->getAttribute(PDO::ATTR_DRIVER_NAME); } catch(Throwable) { return ''; }
}

function ij_table_exists(PDO $pdo, string $table): bool {
  $drv = ij_driver($pdo);

  try {
    if ($drv === 'sqlite') {
      $st = $pdo->prepare("SELECT 1 FROM sqlite_master WHERE type='table' AND name=? LIMIT 1");
      $st->execute([$table]);
      return (bool)$st->fetchColumn();
    }
    // MySQL/MariaDB
    $st = $pdo->prepare("SHOW TABLES LIKE ?");
    $st->execute([$table]);
    return (bool)$st->fetchColumn();
  } catch(Throwable) {
    return false;
  }
}

function ij_columns(PDO $pdo, string $table): array {
  $drv = ij_driver($pdo);
  $cols = [];

  try {
    if ($drv === 'sqlite') {
      $st = $pdo->query("PRAGMA table_info(" . preg_replace('/[^a-zA-Z0-9_]/', '', $table) . ")");
      foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $cols[] = (string)($r['name'] ?? '');
      }
      return array_values(array_filter($cols));
    }

    // MySQL/MariaDB
    $st = $pdo->query("DESCRIBE `" . str_replace('`', '', $table) . "`");
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
      $cols[] = (string)($r['Field'] ?? '');
    }
    return array_values(array_filter($cols));
  } catch(Throwable) {
    return [];
  }
}

function ij_pick_table(PDO $pdo): ?string {
  if (defined('IJ_FORCE_TABLE')) {
    $t = constant('IJ_FORCE_TABLE');
    return ij_table_exists($pdo, $t) ? $t : null;
  }
  foreach (IJ_TABLE_CANDIDATES as $t) {
    if (ij_table_exists($pdo, $t)) return $t;
  }
  return null;
}

function ij_pick_col(array $cols, array $candidates): ?string {
  $set = array_flip($cols);
  foreach ($candidates as $c) {
    if (isset($set[$c])) return $c;
  }
  return null;
}

function ij_is_unix_seconds_column(PDO $pdo, string $table, string $tsCol): bool {
  // Heuristik: om vi kan läsa max(tsCol) och det är > 10^9 så är det troligen unix-sek.
  try {
    $sql = "SELECT MAX(`$tsCol`) AS mx FROM `" . str_replace('`', '', $table) . "`";
    $mx = (int)($pdo->query($sql)->fetchColumn() ?: 0);
    return $mx > 1_000_000_000; // ungefär 2001+
  } catch(Throwable) {
    return true; // anta unix om osäkert
  }
}

function ij_build_time_where(PDO $pdo, string $table, string $tsCol, int $sinceTs): array {
  // Returnerar [sqlWhere, params]
  // Stöd: unix-sekundkolumn eller DATETIME/TIMESTAMP.
  $unix = ij_is_unix_seconds_column($pdo, $table, $tsCol);

  if ($unix) {
    return ["`$tsCol` >= ?", [$sinceTs]];
  }

  // DATETIME/TIMESTAMP: jämför mot datetime-sträng
  $dt = gmdate('Y-m-d H:i:s', $sinceTs);
  return ["`$tsCol` >= ?", [$dt]];
}

function ij_rollup(PDO $pdo, string $table, string $trackCol, string $tsCol, int $days): array {
  $since = time() - ($days * 86400);
  [$where, $params] = ij_build_time_where($pdo, $table, $tsCol, $since);

  $sql =
    "SELECT `$trackCol` AS track_id, COUNT(*) AS plays
     FROM `" . str_replace('`', '', $table) . "`
     WHERE $where
     GROUP BY `$trackCol`";

  $st = $pdo->prepare($sql);
  $st->execute($params);

  $map = [];
  while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
    $id = (string)($r['track_id'] ?? '');
    if ($id === '') continue;
    $map[$id] = (int)($r['plays'] ?? 0);
  }
  return $map;
}

// ======= Main =======
try {
  if (!function_exists('ij_db')) {
    throw new RuntimeException('DB not configured (ij_db missing).');
  }
  $pdo = ij_db();

  $table = ij_pick_table($pdo);
  if (!$table) {
    throw new RuntimeException('Could not find plays table. Set IJ_FORCE_TABLE in cron_rollup.php.');
  }

  $cols = ij_columns($pdo, $table);
  if (!$cols) {
    throw new RuntimeException('Could not read columns for table: ' . $table);
  }

  $trackCol = defined('IJ_FORCE_TRACK_COL') ? constant('IJ_FORCE_TRACK_COL') : ij_pick_col($cols, IJ_TRACK_COL_CANDIDATES);
  $tsCol    = defined('IJ_FORCE_TS_COL') ? constant('IJ_FORCE_TS_COL') : ij_pick_col($cols, IJ_TS_COL_CANDIDATES);

  if (!$trackCol) throw new RuntimeException('Could not detect track id column. Set IJ_FORCE_TRACK_COL.');
  if (!$tsCol)    throw new RuntimeException('Could not detect timestamp column. Set IJ_FORCE_TS_COL.');

  $weekMap  = ij_rollup($pdo, $table, $trackCol, $tsCol, IJ_WEEK_DAYS);
  $monthMap = ij_rollup($pdo, $table, $trackCol, $tsCol, IJ_MONTH_DAYS);

  ij_mkdirp(IJ_ROLLUP_CACHE_DIR);

  $now = time();
  $weekPayload = [
    'updatedAt' => gmdate('c', $now),
    'updatedTs' => $now,
    'days'      => IJ_WEEK_DAYS,
    'count'     => count($weekMap),
    'playsByTrackId' => $weekMap,
  ];

  $monthPayload = [
    'updatedAt' => gmdate('c', $now),
    'updatedTs' => $now,
    'days'      => IJ_MONTH_DAYS,
    'count'     => count($monthMap),
    'playsByTrackId' => $monthMap,
  ];

  $weekFile  = IJ_ROLLUP_CACHE_DIR . '/stats_plays_week.json';
  $monthFile = IJ_ROLLUP_CACHE_DIR . '/stats_plays_month.json';

  file_put_contents($weekFile, json_encode($weekPayload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
  file_put_contents($monthFile, json_encode($monthPayload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

  // Output för manuell körning / logg
  ij_json_out([
    'ok' => true,
    'table' => $table,
    'trackCol' => $trackCol,
    'tsCol' => $tsCol,
    'week' => ['tracks' => count($weekMap), 'file' => $weekFile],
    'month'=> ['tracks' => count($monthMap), 'file' => $monthFile],
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  ij_json_out([
    'ok' => false,
    'error' => $e->getMessage(),
    'hint' => 'Om tabell/kolumn inte hittas: sätt IJ_FORCE_TABLE / IJ_FORCE_TS_COL / IJ_FORCE_TRACK_COL i cron_rollup.php.',
  ]);
}