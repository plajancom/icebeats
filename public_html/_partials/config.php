<?php
// /_partials/config.php
declare(strict_types=1);

/**
 * Central config för domän / origin / absoluta URL:er.
 * Målet är att slippa hårdkoda audio.icejockey.app eller icebeats.io i PHP-filer.
 */

function ij_is_https(): bool {
  static $https = null;
  if ($https !== null) return $https;

  // Cloudflare
  if (!empty($_SERVER['HTTP_CF_VISITOR'])) {
    $j = json_decode((string)$_SERVER['HTTP_CF_VISITOR'], true);
    if (is_array($j) && (($j['scheme'] ?? '') === 'https')) {
      $https = true;
      return $https;
    }
  }

  // Reverse proxy / load balancer
  if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    $https = (strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
    return $https;
  }

  // Standard PHP / Apache / LiteSpeed
  if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') {
    $https = true;
    return $https;
  }

  if ((string)($_SERVER['SERVER_PORT'] ?? '') === '443') {
    $https = true;
    return $https;
  }

  $https = false;
  return $https;
}

function ij_host(): string {
  static $host = null;
  if ($host !== null) return $host;

  $raw = (string)($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'icebeats.io');
  $raw = trim($raw);

  // Ta bort ev. port
  $raw = preg_replace('~:\d+$~', '', $raw) ?? $raw;

  // Enkel fallback
  if ($raw === '') $raw = 'icebeats.io';

  $host = strtolower($raw);
  return $host;
}

function ij_origin(): string {
  static $origin = null;
  if ($origin !== null) return $origin;

  $origin = (ij_is_https() ? 'https' : 'http') . '://' . ij_host();
  return $origin;
}

/**
 * Alias om du vill använda ett mer självförklarande namn i framtiden.
 */
function ij_base_url(): string {
  return ij_origin();
}

/**
 * Bygg absolut intern URL.
 * Ex:
 *   ij_abs('/track/?id=abc') => https://icebeats.io/track/?id=abc
 */
function ij_abs(string $path = '/'): string {
  $path = trim($path);
  if ($path === '') $path = '/';

  // Om full URL redan skickas in, returnera som den är
  if (preg_match('~^https?://~i', $path)) {
    return $path;
  }

  if ($path[0] !== '/') {
    $path = '/' . $path;
  }

  return rtrim(ij_origin(), '/') . $path;
}

/**
 * Absolut asset-URL om du behöver den i OG/meta eller externa sammanhang.
 */
function ij_asset_abs(string $path): string {
  return ij_abs($path);
}