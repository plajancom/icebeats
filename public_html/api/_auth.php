<?php
// /api/_auth.php
declare(strict_types=1);

header('X-Content-Type-Options: nosniff');

/**
 * CORS helper: svara alltid med CORS-headers baserat på Origin om den finns.
 * Viktigt: preflight (OPTIONS) måste kunna få svar utan att auth blockar den.
 */
function ij_cors_headers(): void {
  $cfg = ij_auth_cfg();

  $origin = (string)($_SERVER['HTTP_ORIGIN'] ?? '');
  $hostO  = ij_auth_host_from_url($origin);

  $allowed = false;
  if ($hostO !== '') {
    foreach ($cfg['same_site_hosts'] as $h) {
      $h = strtolower((string)$h);
      if ($hostO === $h) {
        $allowed = true;
        break;
      }
    }
  }

  if ($allowed && $origin !== '') {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
  } else {
    // Ingen match -> sätt inte en "fel" allow-origin
    // (för same-origin requests behövs ändå inte CORS)
    header('Access-Control-Allow-Origin: *');
  }

  header('Access-Control-Allow-Headers: Content-Type, Range, X-API-Key');
  header('Access-Control-Allow-Methods: POST, OPTIONS, GET');
  header('Access-Control-Expose-Headers: Accept-Ranges, Content-Range, Content-Length');
}

/**
 * Preflight: svara 204 direkt (ingen auth här).
 */
function ij_handle_preflight(): void {
  if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    ij_cors_headers();
    http_response_code(204);
    exit;
  }
}

function ij_auth_cfg(): array {
  // Lagra nycklar + rate limit state i en katalog som INTE är publik
  $base = realpath(__DIR__ . '/../admin');
  if (!$base) $base = __DIR__ . '/../admin';

  $dataDir = $base . '/data';
  if (!is_dir($dataDir)) {
    @mkdir($dataDir, 0755, true);
  }

  return [
    'data_dir'   => $dataDir,
    'keys_file'  => $dataDir . '/api_keys.json',
    'rl_dir'     => $dataDir . '/api_rl',

    // ✅ Tillåt "egna sidor" utan key
    'same_site_hosts' => [
      // gamla audio
      'audio.icejockey.app',
      'www.audio.icejockey.app',

      // nya brandet
      'icebeats.io',
      'www.icebeats.io',

      // ev. framtida subdomäner
      'beats.icejockey.app',
      'www.beats.icejockey.app',

      // webappen
      'icejockey.app',
      'www.icejockey.app',
    ],

    // Rate limits
    'rl_key_per_min'  => 120,
    'rl_anon_per_min' => 30,
  ];
}

function ij_auth_load_keys(): array {
  $cfg = ij_auth_cfg();
  $file = $cfg['keys_file'];

  if (!is_file($file)) {
    return ['keys' => []];
  }

  $raw = @file_get_contents($file);
  if ($raw === false || $raw === '') {
    return ['keys' => []];
  }

  $j = json_decode($raw, true);
  if (!is_array($j)) {
    return ['keys' => []];
  }

  if (!isset($j['keys']) || !is_array($j['keys'])) {
    $j['keys'] = [];
  }

  return $j;
}

/**
 * ✅ Saknades tidigare: spara nyckelfilen säkert
 */
function ij_auth_save_keys(array $db): bool {
  $cfg = ij_auth_cfg();
  $file = $cfg['keys_file'];
  $dir  = dirname($file);

  if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
    throw new RuntimeException('Could not create API key data directory.');
  }

  if (!isset($db['keys']) || !is_array($db['keys'])) {
    $db['keys'] = [];
  }

  $json = json_encode($db, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
  if ($json === false) {
    throw new RuntimeException('Could not encode API key JSON.');
  }

  $tmp = $file . '.tmp';

  if (@file_put_contents($tmp, $json, LOCK_EX) === false) {
    throw new RuntimeException('Could not write temporary API key file.');
  }

  if (!@rename($tmp, $file)) {
    @unlink($tmp);
    throw new RuntimeException('Could not replace API key file.');
  }

  @chmod($file, 0644);

  return true;
}

function ij_auth_client_ip(): string {
  $ip = $_SERVER['REMOTE_ADDR'] ?? '';
  return preg_match('/^[0-9a-fA-F:\.]{3,}$/', $ip) ? $ip : '0.0.0.0';
}

function ij_auth_host_from_url(?string $url): string {
  if (!$url) return '';
  $parts = @parse_url($url);
  return strtolower((string)($parts['host'] ?? ''));
}

function ij_auth_is_same_site(): bool {
  $cfg = ij_auth_cfg();

  $origin  = (string)($_SERVER['HTTP_ORIGIN'] ?? '');
  $referer = (string)($_SERVER['HTTP_REFERER'] ?? '');

  $hostO = ij_auth_host_from_url($origin);
  $hostR = ij_auth_host_from_url($referer);

  // ✅ Fallback: om Origin/Referer saknas, använd Host
  $hostH = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
  $hostH = preg_replace('~:\d+$~', '', $hostH) ?: $hostH;

  foreach ($cfg['same_site_hosts'] as $h) {
    $h = strtolower((string)$h);
    if ($hostO === $h || $hostR === $h || $hostH === $h) {
      return true;
    }
  }

  return false;
}

function ij_auth_get_key_from_request(): string {
  $k = $_SERVER['HTTP_X_API_KEY'] ?? '';
  $k = trim((string)$k);
  if ($k !== '') return $k;

  // Fallback: query ?api_key=
  $k2 = $_GET['api_key'] ?? '';
  $k2 = trim((string)$k2);
  return $k2;
}

function ij_auth_hash_key(string $key): string {
  return hash('sha256', $key);
}

function ij_auth_key_exists(string $key): ?array {
  $db = ij_auth_load_keys();
  $h = ij_auth_hash_key($key);

  foreach (($db['keys'] ?? []) as $row) {
    if (!is_array($row)) continue;
    if (($row['hash'] ?? '') === $h && !($row['revoked'] ?? false)) {
      return $row;
    }
  }

  return null;
}

function ij_rl_dir(): string {
  $cfg = ij_auth_cfg();
  $dir = $cfg['rl_dir'];

  if (!is_dir($dir)) {
    @mkdir($dir, 0755, true);
  }

  return $dir;
}

function ij_rl_check_and_inc(string $bucketId, int $limitPerMin): bool {
  $dir = ij_rl_dir();
  $file = $dir . '/' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $bucketId) . '.json';

  $now = time();
  $minute = intdiv($now, 60);
  $state = ['t' => $minute, 'c' => 0];

  $raw = @file_get_contents($file);
  if ($raw) {
    $j = json_decode($raw, true);
    if (is_array($j) && isset($j['t'], $j['c'])) {
      $state = $j;
    }
  }

  if ((int)$state['t'] !== $minute) {
    $state = ['t' => $minute, 'c' => 0];
  }

  $state['c'] = (int)$state['c'] + 1;
  @file_put_contents($file, json_encode($state), LOCK_EX);

  return $state['c'] <= $limitPerMin;
}

function ij_api_deny(int $code, string $msg): void {
  ij_cors_headers();
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  header('Cache-Control: no-store');
  echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
  exit;
}

/**
 * Require API access.
 * - If X-API-Key is valid: allow (+ higher rate limit).
 * - Else allow ONLY if same-site (Origin/Referer/Host matches allowlist), with lower rate limit.
 */
function ij_api_require_access(string $scope = 'default'): array {
  ij_handle_preflight();
  ij_cors_headers();

  $cfg = ij_auth_cfg();

  $key = ij_auth_get_key_from_request();
  $ip  = ij_auth_client_ip();

  if ($key !== '') {
    $row = ij_auth_key_exists($key);
    if (!$row) {
      ij_api_deny(401, 'Invalid API key');
    }

    $bucket = 'key_' . ($row['hash'] ?? ij_auth_hash_key($key)) . '_' . $scope;
    if (!ij_rl_check_and_inc($bucket, (int)$cfg['rl_key_per_min'])) {
      ij_api_deny(429, 'Rate limited');
    }

    return [
      'mode'  => 'key',
      'name'  => (string)($row['name'] ?? ''),
      'scope' => $scope,
    ];
  }

  if (!ij_auth_is_same_site()) {
    ij_api_deny(401, 'API key required');
  }

  $bucket = 'ip_' . preg_replace('/[^0-9a-fA-F:\.]/', '_', $ip) . '_' . $scope;
  if (!ij_rl_check_and_inc($bucket, (int)$cfg['rl_anon_per_min'])) {
    ij_api_deny(429, 'Rate limited');
  }

  return [
    'mode'  => 'same_site',
    'ip'    => $ip,
    'scope' => $scope,
  ];
}