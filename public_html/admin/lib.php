<?php
declare(strict_types=1);

session_start();

$ROOT = realpath(__DIR__ . '/..');                 // docroot
if ($ROOT === false) {
  http_response_code(500);
  exit('Invalid ROOT');
}

$config = $ROOT . '/_partials/config.php';
if (!is_file($config)) {
  http_response_code(500);
  exit('Config missing: ' . $config);
}
require_once $config;
$BASE_URL = rtrim(ij_origin(), '/');

// ✅ Token som krävs för att lägga upp i pending från /upload/
const UPLOAD_TOKEN = 'ijUpl_7f4bFQp2mW9cZx1N6tR8yK3vH0sA5dJ2lG8nP6qS1uE9rT4xY7cV5bN0mQ3aL';

$TRACKS_DIR  = $ROOT . '/tracks';
$COVERS_DIR  = $ROOT . '/covers';
$ADMIN_DIR   = $ROOT . '/admin';
$PENDING_DIR = $ADMIN_DIR . '/pending';
$DATA_DIR    = $ADMIN_DIR . '/data';

$DB_PATH      = $DATA_DIR . '/library.db.json';   // intern “databas”
$LIBRARY_PATH = $ROOT . '/library.json';          // publik fil som appen + publiken läser

$MAX_MP3_MB = 40;
$MAX_IMG_MB = 8;

// ✅ MySQL/PDO config (utanför public_html)
$DB_CONFIG = __DIR__ . '/../../icejockey-config/db.php';
if (is_file($DB_CONFIG)) {
  require_once $DB_CONFIG;
}

/** PHP 7-kompatibel startsWith */
function ij_starts_with(string $haystack, string $needle): bool {
  return $needle === '' || strpos($haystack, $needle) === 0;
}

/** Säker IP-extraktion (för loggning/rate-limit). */
function ij_client_ip(): string {
  $xff = (string)($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');
  if ($xff !== '') {
    $parts = explode(',', $xff);
    $ip = trim((string)($parts[0] ?? ''));
    if ($ip !== '') return substr($ip, 0, 64);
  }
  $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
  return substr($ip !== '' ? $ip : 'unknown', 0, 64);
}

function ensure_dirs(array $dirs): void {
  foreach ($dirs as $d) {
    if (!is_dir($d)) @mkdir($d, 0775, true);
  }
}

ensure_dirs([$TRACKS_DIR, $COVERS_DIR, $PENDING_DIR, $DATA_DIR]);

function csrf_token(): string {
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
  }
  return (string)$_SESSION['csrf'];
}

function require_csrf(): void {
  $t = (string)($_POST['csrf'] ?? '');
  if ($t === '' || !hash_equals((string)($_SESSION['csrf'] ?? ''), $t)) {
    http_response_code(403);
    exit('CSRF mismatch');
  }
}

function read_json(string $path, $fallback) {
  if (!is_file($path)) return $fallback;
  $raw = file_get_contents($path);
  if ($raw === false || $raw === '') return $fallback;
  $j = json_decode($raw, true);
  return is_array($j) ? $j : $fallback;
}

function write_json_atomic(string $path, $data): void {
  $tmp = $path . '.' . bin2hex(random_bytes(6)) . '.tmp';
  $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
  if ($json === false) {
    throw new RuntimeException('Could not encode JSON');
  }
  if (file_put_contents($tmp, $json) === false) {
    throw new RuntimeException('Could not write temp JSON');
  }
  if (!@rename($tmp, $path)) {
    @unlink($tmp);
    throw new RuntimeException('Could not replace JSON');
  }
}

function slugify(string $s): string {
  $s = strtolower(trim($s));
  $s = preg_replace('~[^a-z0-9]+~', '-', $s);
  $s = trim((string)$s, '-');
  return $s !== '' ? $s : 'track';
}

function sanitize_genre(string $g): string {
  $g = trim($g);
  $g = preg_replace('~\s+~', ' ', $g);
  $g = mb_substr((string)$g, 0, 40);
  return $g !== '' ? $g : 'Övrigt';
}

function mime_of(string $tmpPath): string {
  if (function_exists('finfo_open')) {
    $f = finfo_open(FILEINFO_MIME_TYPE);
    if ($f) {
      $m = finfo_file($f, $tmpPath) ?: '';
      finfo_close($f);
      return (string)$m;
    }
  }
  return function_exists('mime_content_type') ? (string)(mime_content_type($tmpPath) ?: '') : '';
}

function rrmdir(string $dir): void {
  if (!is_dir($dir)) return;
  $it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::CHILD_FIRST
  );
  foreach ($it as $f) {
    $p = $f->getPathname();
    $f->isDir() ? @rmdir($p) : @unlink($p);
  }
  @rmdir($dir);
}

/** Track “active?” – default är aktiv om status saknas. */
function ij_is_track_active(array $it): bool {
  $status = strtolower(trim((string)($it['status'] ?? 'active')));
  if ($status === '' || $status === 'active' || $status === 'approved') return true;
  if ($status === 'blocked' || $status === 'disabled' || $status === 'hidden') return false;
  return true;
}

/** Normalisera en URL/relativ path till en “/path” (utan domän). */
function ij_public_path(string $urlOrPath, string $BASE_URL): string {
  $u = trim($urlOrPath);
  if ($u === '') return '';

  if (ij_starts_with($u, 'http://') || ij_starts_with($u, 'https://')) {
    $p = parse_url($u, PHP_URL_PATH);
    return is_string($p) ? $p : '';
  }

  return $u;
}

/** Försök mappa en public path (/tracks/... eller /covers/...) till diskpath under $ROOT. */
function ij_public_to_disk(string $publicPath, string $ROOT): ?string {
  $p = trim($publicPath);
  if ($p === '') return null;

  if (!ij_starts_with($p, '/tracks/') && !ij_starts_with($p, '/covers/')) {
    return null;
  }

  $full = $ROOT . $p;
  $real = realpath($full);
  if ($real === false) return $full; // filen kanske saknas, men vi vill ändå försöka unlinka
  $rootReal = realpath($ROOT) ?: $ROOT;

  // skydda mot traversal
  if (ij_starts_with($real, $rootReal)) return $real;
  return null;
}

/** Regenerera library.json — filtrerar bort spärrade tracks. */
function regen_library_json(string $BASE_URL, string $DB_PATH, string $LIBRARY_PATH): void {
  $db = read_json($DB_PATH, ['items' => []]);
  $items = $db['items'] ?? [];
  if (!is_array($items)) $items = [];

  // endast aktiva tracks i publik fil
  $items = array_values(array_filter($items, function($it){
    return is_array($it) && ij_is_track_active($it);
  }));

  // sort nyast först
  usort($items, function($a, $b) {
    return (int)($b['createdAt'] ?? 0) <=> (int)($a['createdAt'] ?? 0);
  });

  $public = [
    'baseUrl' => rtrim($BASE_URL, '/'),
    'items'   => array_map(function($it) {
      $image = (string)($it['image'] ?? '');

      // Gör om absoluta gamla bild-URL:er till relativa paths
      if ($image !== '' && (strpos($image, 'http://') === 0 || strpos($image, 'https://') === 0)) {
        $path = parse_url($image, PHP_URL_PATH);
        $image = is_string($path) ? $path : '';
      }

      if ($image !== '' && strpos($image, '/') !== 0) {
        $image = '/' . ltrim($image, '/');
      }

      $url = (string)($it['url'] ?? '');
      if ($url !== '' && strpos($url, '/') !== 0) {
        $url = '/' . ltrim($url, '/');
      }

      return [
        'id'        => (string)($it['id'] ?? ''),
        'title'     => (string)($it['title'] ?? ''),
        'artist'    => (string)($it['artist'] ?? ''),
        'genre'     => (string)($it['genre'] ?? 'Övrigt'),
        'url'       => $url,
        'image'     => $image,
        'mime'      => (string)($it['mime'] ?? 'audio/mpeg'),
        'kind'      => (string)($it['kind'] ?? 'audio'),
        'startMs'   => (int)($it['startMs'] ?? 0),
        'endMs'     => (int)($it['endMs'] ?? 0),
        'createdAt' => (int)($it['createdAt'] ?? 0),
      ];
    }, $items),
  ];

  write_json_atomic($LIBRARY_PATH, $public);
}