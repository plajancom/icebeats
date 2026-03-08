<?php
// /_partials/meta.php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/i18n.php';

/**
 * HTML-escape
 */
function ij_meta_h(?string $s): string {
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/**
 * Standardvärden för hela sajten
 */
function ij_meta_defaults(): array {
  $lang = ij_lang();

  return [
    'site_name' => 'iceBeats.io',

    'title' => 'iceBeats.io',
    'description' => ($lang === 'en')
      ? 'Upload and share arena music, tracks, jingles and sound effects.'
      : 'Ladda upp och dela arenamusik, jinglar och ljudeffekter.',

    'canonical' => ij_abs('/'),
    'image' => ij_abs('/share/og.jpg'),

    'type' => 'website',
    'twitter_card' => 'summary_large_image',

    'locale' => ($lang === 'en') ? 'en_US' : 'sv_SE',
    'alternate_locale' => ($lang === 'en') ? 'sv_SE' : 'en_US',

    /**
     * valfria extra taggar:
     * [
     *   ['name' => 'robots', 'content' => 'index,follow'],
     *   ['property' => 'article:author', 'content' => '...'],
     * ]
     */
    'extra' => [],
  ];
}

/**
 * Bygg komplett meta-array från defaults + overrides
 */
function ij_build_meta(array $overrides = []): array {
  $meta = array_merge(ij_meta_defaults(), $overrides);

  // Säkerställ strängar där det behövs
  $meta['site_name'] = (string)($meta['site_name'] ?? 'iceBeats.io');
  $meta['title'] = trim((string)($meta['title'] ?? ''));
  $meta['description'] = trim((string)($meta['description'] ?? ''));
  $meta['canonical'] = trim((string)($meta['canonical'] ?? ''));
  $meta['image'] = trim((string)($meta['image'] ?? ''));
  $meta['type'] = trim((string)($meta['type'] ?? 'website'));
  $meta['twitter_card'] = trim((string)($meta['twitter_card'] ?? 'summary_large_image'));
  $meta['locale'] = trim((string)($meta['locale'] ?? ''));
  $meta['alternate_locale'] = trim((string)($meta['alternate_locale'] ?? ''));

  if (!is_array($meta['extra'] ?? null)) {
    $meta['extra'] = [];
  }

  // Fallbacks
  if ($meta['title'] === '') {
    $meta['title'] = 'iceBeats.io';
  }
  if ($meta['description'] === '') {
    $meta['description'] = ($lang = ij_lang()) === 'en'
      ? 'Upload and share arena music, tracks, jingles and sound effects.'
      : 'Ladda upp och dela arenamusik, jinglar och ljudeffekter.';
  }
  if ($meta['canonical'] === '') {
    $meta['canonical'] = ij_abs('/');
  }
  if ($meta['image'] === '') {
    $meta['image'] = ij_abs('/share/og.jpg');
  }
  if ($meta['type'] === '') {
    $meta['type'] = 'website';
  }
  if ($meta['twitter_card'] === '') {
    $meta['twitter_card'] = 'summary_large_image';
  }

  return $meta;
}

/**
 * Rendera meta-taggar till HTML för <head>
 */
function ij_render_meta(array $meta): string {
  $meta = ij_build_meta($meta);

  $siteName = ij_meta_h($meta['site_name']);
  $title = ij_meta_h($meta['title']);
  $desc = ij_meta_h($meta['description']);
  $canonical = ij_meta_h($meta['canonical']);
  $image = ij_meta_h($meta['image']);
  $type = ij_meta_h($meta['type']);
  $twitterCard = ij_meta_h($meta['twitter_card']);
  $locale = ij_meta_h($meta['locale']);
  $altLocale = ij_meta_h($meta['alternate_locale']);

  $html = [];

  $html[] = '<meta name="description" content="' . $desc . '" />';
  $html[] = '<link rel="canonical" href="' . $canonical . '" />';

  $html[] = '<meta property="og:type" content="' . $type . '" />';
  $html[] = '<meta property="og:site_name" content="' . $siteName . '" />';
  $html[] = '<meta property="og:title" content="' . $title . '" />';
  $html[] = '<meta property="og:description" content="' . $desc . '" />';
  $html[] = '<meta property="og:url" content="' . $canonical . '" />';
  $html[] = '<meta property="og:image" content="' . $image . '" />';

  if ($locale !== '') {
    $html[] = '<meta property="og:locale" content="' . $locale . '" />';
  }
  if ($altLocale !== '') {
    $html[] = '<meta property="og:locale:alternate" content="' . $altLocale . '" />';
  }

  $html[] = '<meta name="twitter:card" content="' . $twitterCard . '" />';
  $html[] = '<meta name="twitter:title" content="' . $title . '" />';
  $html[] = '<meta name="twitter:description" content="' . $desc . '" />';
  $html[] = '<meta name="twitter:image" content="' . $image . '" />';

  foreach ($meta['extra'] as $tag) {
    if (!is_array($tag)) continue;

    $content = ij_meta_h((string)($tag['content'] ?? ''));
    if ($content === '') continue;

    if (!empty($tag['name'])) {
      $name = ij_meta_h((string)$tag['name']);
      $html[] = '<meta name="' . $name . '" content="' . $content . '" />';
      continue;
    }

    if (!empty($tag['property'])) {
      $property = ij_meta_h((string)$tag['property']);
      $html[] = '<meta property="' . $property . '" content="' . $content . '" />';
      continue;
    }
  }

  return implode("\n", $html) . "\n";
}