<?php
// /_partials/i18n.php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Enkel i18n: sv/en
 * - ?lang=sv|en -> sätter cookie
 * - annars cookie
 * - annars Accept-Language
 */
function ij_lang(): string {
  static $lang = null;
  if ($lang !== null) return $lang;

  $q = strtolower(trim((string)($_GET['lang'] ?? '')));
  if ($q === 'sv' || $q === 'en') {
    $lang = $q;

    // 180 dagar
    @setcookie('ij_lang', $lang, [
      'expires' => time() + 60*60*24*180,
      'path' => '/',
      'secure' => ij_is_https(), // ✅ proxy/cloudflare-safe
      'httponly' => false,
      'samesite' => 'Lax',
    ]);
    return $lang;
  }

  $c = strtolower(trim((string)($_COOKIE['ij_lang'] ?? '')));
  if ($c === 'sv' || $c === 'en') {
    $lang = $c;
    return $lang;
  }

  $al = strtolower((string)($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
  $lang = (strpos($al, 'sv') !== false) ? 'sv' : 'en';
  return $lang;
}

function ij_set_lang(string $lang): void {
  $lang = strtolower(trim($lang));
  if ($lang !== 'sv' && $lang !== 'en') $lang = 'sv';

  @setcookie('ij_lang', $lang, [
    'expires' => time() + 60*60*24*180,
    'path' => '/',
    'secure' => ij_is_https(), // ✅ proxy/cloudflare-safe
    'httponly' => false,
    'samesite' => 'Lax',
  ]);
}

/** Liten översättningstabell för gemensamma UI-texter */
function ij_t(string $key, ?string $lang = null): string {
  $lang = $lang ?: ij_lang();

  static $dict = [
    'sv' => [
      'brand' => 'Icebeats Audio',
      'home' => 'Start',
      'top' => 'Topplista',
      'library' => 'Låtar',
      'upload' => 'Ladda upp',
      'api' => 'API',
      'admin' => 'Admin',
      'status' => 'Status',
      'contact' => 'Kontakt',
      'copyright' => 'Arena-ljudbibliotek',
      'lang_sv' => 'Svenska',
      'lang_en' => 'English',
      'lang_label' => 'Språk',
    ],
    'en' => [
      'brand' => 'Icebeats Audio',
      'home' => 'Home',
      'top' => 'Top',
      'library' => 'Tracks',
      'upload' => 'Upload',
      'api' => 'API',
      'admin' => 'Admin',
      'status' => 'Status',
      'contact' => 'Contact',
      'copyright' => 'Arena audio library',
      'lang_sv' => 'Swedish',
      'lang_en' => 'English',
      'lang_label' => 'Language',
    ],
  ];

  return $dict[$lang][$key] ?? $dict['sv'][$key] ?? $key;
}

/**
 * Bygger en intern URL och ser till att lang alltid finns.
 * ✅ Behåller ev. query-param som redan finns i $path (t.ex. ?id=123)
 *
 * Ex:
 *  ij_url('/track/?id=abc') -> /track/?id=abc&lang=sv
 *  ij_url('/top/?range=week') -> /top/?range=week&lang=sv
 */
function ij_url(string $path): string {
  $lang = ij_lang();

  // Tillåt att man skickar in "track/?id=.." utan inledande /
  $path = '/' . ltrim($path, '/');

  $parts = parse_url($path);
  $p = $parts['path'] ?? '/';
  $qs = $parts['query'] ?? '';

  // Plocka befintliga query-param och lägg/ersätt lang
  $params = [];
  if ($qs !== '') {
    parse_str($qs, $params);
    if (!is_array($params)) $params = [];
  }
  $params['lang'] = $lang;

  $newQs = http_build_query($params);

  return $p . ($newQs ? ('?' . $newQs) : '');
}

/** Returnera " active" om aktuell path matchar prefix */
function ij_nav_active(string $prefix, string $path): string {
  if ($prefix === '/') return ($path === '/' ? ' active' : '');
  return (strpos($path, $prefix) === 0) ? ' active' : '';
}

/** Cache-busta assets */
function ij_asset(string $webPath): string {
  $doc = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
  $fs  = $doc . $webPath;
  if ($doc && is_file($fs)) {
    $t = @filemtime($fs);
    if ($t) return $webPath . '?v=' . $t;
  }
  return $webPath;
}