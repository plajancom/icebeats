<?php
// /_partials/header.php
declare(strict_types=1);

require_once __DIR__ . '/i18n.php';

$lang = ij_lang();
if (!isset($pageTitle) || !$pageTitle) $pageTitle = ij_t('brand', $lang);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$pageHead = $pageHead ?? '';

$svHref = preg_replace('~([?&])lang=(sv|en)~', '$1lang=sv', $_SERVER['REQUEST_URI'] ?? '/');
if (strpos($svHref, 'lang=') === false) $svHref .= (strpos($svHref, '?') === false ? '?' : '&') . 'lang=sv';

$enHref = preg_replace('~([?&])lang=(sv|en)~', '$1lang=en', $_SERVER['REQUEST_URI'] ?? '/');
if (strpos($enHref, 'lang=') === false) $enHref .= (strpos($enHref, '?') === false ? '?' : '&') . 'lang=en';

$PLAYER = [
  'sv' => [
    'prev' => 'Föregående',
    'next' => 'Nästa',
    'play' => 'Spela',
    'pause' => 'Pausa',
    'shuffle' => 'Shuffle',
    'auto' => 'Nästa auto',
    'hide' => 'Dölj',
    'no_track' => 'Ingen låt vald',
    'pill_np' => 'Spelas nu',
    'cast' => 'Casta',
  ],
  'en' => [
    'prev' => 'Previous',
    'next' => 'Next',
    'play' => 'Play',
    'pause' => 'Pause',
    'shuffle' => 'Shuffle',
    'auto' => 'Auto next',
    'hide' => 'Hide',
    'no_track' => 'No track selected',
    'pill_np' => 'Now playing',
    'cast' => 'Cast',
  ],
];
$PL = $PLAYER[$lang] ?? $PLAYER['sv'];
?>
<!doctype html>
<html lang="<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars((string)$pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="<?= htmlspecialchars(ij_asset('/assets/app.css'), ENT_QUOTES, 'UTF-8') ?>">
  <?= $pageHead ?>

  <!-- Google Cast Web Sender SDK -->
  <script>
    window.__onGCastApiAvailable = window.__onGCastApiAvailable || function(){};
  </script>
  <script src="https://www.gstatic.com/cv/js/sender/v1/cast_sender.js?loadCastFramework=1"></script>

  <style>
    .ij-now{
      display:flex;
      align-items:center;
      gap:.45rem;
      padding:.28rem .55rem;
      border-radius:999px;
      border:1px solid rgba(255,255,255,.10);
      background:rgba(0,0,0,.22);
      max-width:160px;
      min-width:120px;
      font-size:.85rem;
      line-height:1;
      user-select:none;
      text-decoration:none;
      color:inherit;
      overflow:hidden;
    }
    .ij-now:hover{
      border-color: rgba(255,255,255,.18);
      background: rgba(0,0,0,.28);
    }
    .ij-now-dot{
      width:.55rem;
      height:.55rem;
      border-radius:999px;
      background:#999;
      flex:0 0 auto;
      box-shadow: 0 0 0 2px rgba(0,0,0,.25) inset;
    }
    .ij-now-marquee{
      position:relative;
      flex:1 1 auto;
      overflow:hidden;
      white-space:nowrap;
      min-width:0;
    }
    .ij-now-strip{
      display:inline-flex;
      align-items:center;
      white-space:nowrap;
      will-change:transform;
      animation: ijNowMarquee 12s linear infinite;
    }
    .ij-now-block{
      display:inline-flex;
      align-items:center;
      white-space:nowrap;
    }
    .ij-now-item{
      display:inline-block;
      padding-right:1.25rem;
    }
    .ij-now-sep{
      display:inline-block;
      opacity:.6;
      padding:0 .75rem;
    }
    @keyframes ijNowMarquee{
      0%   { transform: translateX(0); }
      100% { transform: translateX(-50%); }
    }
    .ij-now:hover .ij-now-strip{
      animation-play-state: paused;
    }
    .ij-now-sr{
      position:absolute;
      width:1px;height:1px;
      padding:0;margin:-1px;
      overflow:hidden;clip:rect(0,0,0,0);
      white-space:nowrap;border:0;
    }

    #ijAppMain{
      transition: opacity .16s ease;
    }
    body.ij-shell-loading #ijAppMain{
      opacity:.55;
    }

    /* ===== Cast button ===== */
    google-cast-launcher{
      display:inline-flex;
      width:28px;
      height:28px;
      align-items:center;
      justify-content:center;
      border-radius:12px;
      border:1px solid var(--border2);
      background:var(--card2);
      cursor:pointer;
      --connected-color: #60a5fa;
      --disconnected-color: #e2e8f0;
    }
    google-cast-launcher:hover{
      border-color:#475569;
    }
    .ij-castWrap{
      display:inline-flex;
      align-items:center;
      justify-content:center;
    }

    /* ===== Global persistent player ===== */
    body.has-player{ padding-bottom: 120px; }

    .ij-global-playerbar{
      position:fixed; left:0; right:0; bottom:0;
      background:rgba(15, 23, 42, .92);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border-top:1px solid var(--border);
      z-index:9999;
      padding:10px 12px;
      opacity:0;
      transform: translateY(14px);
      pointer-events:none;
      transition: opacity .18s ease, transform .18s ease;
    }
    .ij-global-playerbar.show{ opacity:1; transform: translateY(0); pointer-events:auto; }

    .ij-global-playerwrap{
      max-width: var(--max-header);
      margin: 0 auto;
      padding: 0 16px;
      display:grid;
      grid-template-columns: 52px 1fr minmax(220px, 360px) auto;
      gap:10px;
      align-items:center;
    }

    .ij-gp-cover{
      width:52px;height:52px;border-radius:12px;
      object-fit:cover;border:1px solid #24324f;background:var(--card2);
    }
    .ij-gp-meta{min-width:0}
    .ij-gp-title{font-weight:950;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .ij-gp-artist{color:var(--muted);font-size:12px;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

    .ij-gp-seek{
      grid-column: 3; grid-row: 1;
      display:flex; align-items:center; gap:10px;
    }
    .ij-gp-time{color:var(--muted);font-size:12px;min-width:46px;text-align:center}
    .ij-gp-slider{
      flex:1; appearance:none; height:6px; border-radius:999px; background:#24324f; outline:none;
    }
    .ij-gp-slider::-webkit-slider-thumb{
      appearance:none; width:16px;height:16px;border-radius:50%;
      background:var(--accent2);border:2px solid var(--card2); cursor:pointer;
    }
    .ij-gp-slider::-moz-range-thumb{
      width:16px;height:16px;border-radius:50%;
      background:var(--accent2);border:2px solid var(--card2); cursor:pointer;
    }

    .ij-gp-actions{
      grid-column: 4; grid-row: 1;
      display:flex; align-items:center; gap:8px; justify-content:flex-end; flex-wrap:wrap;
    }
    .ij-gp-btn{
      width:42px;height:42px;
      border-radius:12px;
      border:1px solid var(--border2);
      background:var(--card2);
      color:var(--text);
      font-weight:900;
      display:inline-flex;align-items:center;justify-content:center;
      cursor:pointer;padding:0;
    }
    .ij-gp-btn:hover{ border-color:#475569; }
    .ij-gp-btn.primary{ background:var(--accent);border-color:var(--accent);color:white; }

    .ij-gp-toggle{
      height:42px;
      padding:0 12px;
      border-radius:12px;
      border:1px solid var(--border2);
      background:var(--card2);
      color:var(--text);
      font-weight:900;
      cursor:pointer;
      display:inline-flex;align-items:center;gap:8px;
      white-space:nowrap;
    }
    .ij-gp-toggle.on{ border-color:var(--accent2); }

    .ij-gp-castActive{
      border-color: rgba(96,165,250,.55) !important;
      box-shadow: 0 0 0 1px rgba(96,165,250,.18) inset;
    }

    .ij-gp-volwrap{ position:relative; display:inline-flex; }

    .ij-gp-volpop{
      position:absolute;
      right:0;
      bottom:54px;
      display:none;
      background:rgba(11,18,32,.96);
      border:1px solid var(--border2);
      border-radius:16px;
      padding:10px 8px 8px;
      box-shadow: 0 12px 38px rgba(0,0,0,.35);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      z-index:10001;
      width:max-content;
    }

    .ij-gp-volwrap.open .ij-gp-volpop{ display:block; }

    .ij-gp-volpop:before{
      content:"";
      position:absolute;
      right:12px;
      bottom:-7px;
      width:14px;height:14px;
      background:rgba(11,18,32,.96);
      border-right:1px solid var(--border2);
      border-bottom:1px solid var(--border2);
      transform: rotate(45deg);
    }

    .ij-gp-volpop-inner{
      display:flex;
      flex-direction:column;
      align-items:center;
      gap:8px;
      padding-bottom:2px;
      width:34px;
    }

    .ij-gp-volslider{
      -webkit-appearance: slider-vertical;
      appearance: slider-vertical;
      writing-mode: bt-lr;
      width:26px;
      height:150px;
      background:transparent;
      padding:0;
    }

    .ij-gp-volslider::-webkit-slider-runnable-track{
      height:6px;
      border-radius:999px;
      background:#24324f;
    }
    .ij-gp-volslider::-webkit-slider-thumb{
      -webkit-appearance:none;
      width:16px;height:16px;border-radius:50%;
      background:var(--accent2);
      border:2px solid var(--card2);
      cursor:pointer;
      margin-top:-5px;
    }
    .ij-gp-volslider::-moz-range-track{
      height:6px;
      border-radius:999px;
      background:#24324f;
    }
    .ij-gp-volslider::-moz-range-thumb{
      width:16px;height:16px;border-radius:50%;
      background:var(--accent2);
      border:2px solid var(--card2);
      cursor:pointer;
    }

    .ij-gp-volval{
      font-size:12px;
      color:var(--muted);
      font-weight:900;
      min-width:44px;
      text-align:center;
    }

    .ij-global-pill{
      position:fixed; left:12px; right:12px; bottom:12px;
      z-index:10000;
      display:none;
      align-items:center;
      gap:10px;
      padding:10px 12px;
      border-radius:16px;
      border:1px solid var(--border2);
      background:rgba(11,18,32,.92);
      color:var(--text);
      cursor:pointer;
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      box-shadow: 0 10px 30px rgba(0,0,0,.25);
    }
    .ij-global-pill.show{ display:flex; }
    .ij-global-pill:hover{ border-color:#475569; }

    .ij-gpill-cover{
      width:34px;height:34px;border-radius:12px; border:1px solid #24324f;
      background:var(--card2); object-fit:cover; flex:0 0 auto;
    }
    .ij-gpill-text{ min-width:0; flex:1; }
    .ij-gpill-title{
      font-weight:950; font-size:13px; line-height:1.1;
      white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
    }
    .ij-gpill-sub{
      margin-top:2px; font-size:11px; color:var(--muted);
      white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
    }
    .ij-gpill-state{
      width:36px;height:36px;border-radius:14px; border:1px solid var(--border2);
      background:var(--card2); display:flex;align-items:center;justify-content:center;
      font-weight:900; flex:0 0 auto;
    }

    @media (min-width: 720px){
      .ij-global-pill{
        left:50%; right:auto; transform:translateX(-50%);
        width:min(620px, calc(100vw - 24px));
      }
    }

    @media (max-width:1200px){
      .ij-now{ max-width:220px; }
    }
    @media (max-width:1000px){
      .ij-now{ max-width:180px; }
    }
    @media (max-width: 640px){
      .ij-now{
        max-width:130px;
        min-width:110px;
      }
    }
    @media (max-width: 620px){
      body.has-player{ padding-bottom:150px; }
      .ij-global-playerwrap{
        grid-template-columns: 52px 1fr auto;
        grid-template-rows: auto auto;
      }
      .ij-gp-actions{
        grid-column: 1 / -1;
        grid-row: 2;
        justify-content:center;
        flex-wrap:nowrap;
        gap:6px;
      }
      .ij-gp-btn{ width:34px; height:34px; border-radius:12px; }
      .ij-gp-toggle{
        height:34px; padding:0; width:34px; justify-content:center; gap:0;
      }
      .ij-gp-toggle span{ display:none; }
      .ij-gp-seek{ gap:6px; justify-content:flex-end; }
      .ij-gp-seek .ij-gp-slider{ width:120px; }
      .ij-gp-time.dur{ display:none; }
      .ij-gp-time{ min-width:42px; }
      .ij-gp-volpop{ bottom:46px; }
    }

    @media (prefers-reduced-motion: reduce){
      .ij-now-strip{ animation:none !important; }
    }
  </style>
</head>
<body>
  <div class="ij-overlay" id="ijOverlay" aria-hidden="true"></div>

  <aside class="ij-drawer" id="ijDrawer" aria-hidden="true" aria-label="Mobilnavigation">
    <div class="ij-drawer-head">
      <a class="ij-drawer-brand" href="<?= htmlspecialchars(ij_url('/'), ENT_QUOTES, 'UTF-8') ?>">
        <img class="ij-drawer-logo" src="<?= htmlspecialchars(ij_asset('/assets/icebeats-logo.webp'), ENT_QUOTES, 'UTF-8') ?>" alt="Icebeats Audio">
      </a>
      <button class="ij-drawer-close" type="button" id="ijDrawerClose" aria-label="Stäng">✕</button>
    </div>

    <nav class="ij-drawer-nav" aria-label="Mobilmeny">
      <a data-ij-nav="/" class="ij-link<?= ij_nav_active('/', $path) ?>" href="<?= htmlspecialchars(ij_url('/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ij_t('home', $lang), ENT_QUOTES, 'UTF-8') ?></a>
      <a data-ij-nav="/top" class="ij-link<?= ij_nav_active('/top', $path) ?>" href="<?= htmlspecialchars(ij_url('/top/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ij_t('top', $lang), ENT_QUOTES, 'UTF-8') ?></a>
      <a data-ij-nav="/library" class="ij-link<?= ij_nav_active('/library', $path) ?>" href="<?= htmlspecialchars(ij_url('/library/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ij_t('library', $lang), ENT_QUOTES, 'UTF-8') ?></a>
      <a data-ij-nav="/upload" class="ij-link<?= ij_nav_active('/upload', $path) ?>" href="<?= htmlspecialchars(ij_url('/upload/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ij_t('upload', $lang), ENT_QUOTES, 'UTF-8') ?></a>
      <a data-ij-nav="/api" class="ij-link<?= ij_nav_active('/api', $path) ?>" href="<?= htmlspecialchars(ij_url('/api/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ij_t('api', $lang), ENT_QUOTES, 'UTF-8') ?></a>
      <a data-ij-nav="/contact" class="ij-link<?= ij_nav_active('/contact', $path) ?>" href="<?= htmlspecialchars(ij_url('/contact/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($lang === 'en' ? 'Contact' : 'Kontakt', ENT_QUOTES, 'UTF-8') ?></a>
    </nav>

    <div class="ij-drawer-foot">
      <div class="ij-lang" role="group" aria-label="<?= htmlspecialchars(ij_t('lang_label', $lang), ENT_QUOTES, 'UTF-8') ?>">
        <a
          class="ij-lang-btn<?= $lang==='sv' ? ' active' : '' ?>"
          data-lang-switch="sv"
          data-no-ajax="1"
          href="<?= htmlspecialchars($svHref, ENT_QUOTES, 'UTF-8') ?>"
        >SV</a>
        <a
          class="ij-lang-btn<?= $lang==='en' ? ' active' : '' ?>"
          data-lang-switch="en"
          data-no-ajax="1"
          href="<?= htmlspecialchars($enHref, ENT_QUOTES, 'UTF-8') ?>"
        >EN</a>
      </div>

      <div class="ij-drawer-badge"><?= htmlspecialchars(ij_host(), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
  </aside>

  <header class="ij-topbar" id="ijHeader">
    <div class="ij-topbar-inner">
      <a class="ij-brand" href="<?= htmlspecialchars(ij_url('/'), ENT_QUOTES, 'UTF-8') ?>">
        <img class="ij-logo ij-logo-full" src="<?= htmlspecialchars(ij_asset('/assets/icebeats-logo.webp'), ENT_QUOTES, 'UTF-8') ?>" alt="Icebeats Audio">
        <img class="ij-logo ij-logo-dot"  src="<?= htmlspecialchars(ij_asset('/assets/icebeats-logo.webp'), ENT_QUOTES, 'UTF-8') ?>" alt="Icebeats Audio">
      </a>

      <nav class="ij-nav" aria-label="Huvudnavigation">
        <a data-ij-nav="/" class="ij-link<?= ij_nav_active('/', $path) ?>" href="<?= htmlspecialchars(ij_url('/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ij_t('home', $lang), ENT_QUOTES, 'UTF-8') ?></a>
        <a data-ij-nav="/top" class="ij-link<?= ij_nav_active('/top', $path) ?>" href="<?= htmlspecialchars(ij_url('/top/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ij_t('top', $lang), ENT_QUOTES, 'UTF-8') ?></a>
        <a data-ij-nav="/library" class="ij-link<?= ij_nav_active('/library', $path) ?>" href="<?= htmlspecialchars(ij_url('/library/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ij_t('library', $lang), ENT_QUOTES, 'UTF-8') ?></a>
        <a data-ij-nav="/upload" class="ij-link<?= ij_nav_active('/upload', $path) ?>" href="<?= htmlspecialchars(ij_url('/upload/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ij_t('upload', $lang), ENT_QUOTES, 'UTF-8') ?></a>
        <a data-ij-nav="/api" class="ij-link<?= ij_nav_active('/api', $path) ?>" href="<?= htmlspecialchars(ij_url('/api/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ij_t('api', $lang), ENT_QUOTES, 'UTF-8') ?></a>
        <a data-ij-nav="/contact" class="ij-link<?= ij_nav_active('/contact', $path) ?>" href="<?= htmlspecialchars(ij_url('/contact/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($lang === 'en' ? 'Contact' : 'Kontakt', ENT_QUOTES, 'UTF-8') ?></a>
      </nav>

      <a class="ij-now" id="ijNow" aria-live="polite" title="Senast spelad (globalt)" href="#">
        <span class="ij-now-dot" id="ijNowDot"></span>

        <span class="ij-now-marquee" aria-hidden="true">
          <span class="ij-now-strip" id="ijNowStrip">
            <span class="ij-now-block" id="ijNowBlockA">
              <span class="ij-now-item" id="ijNowLabelA">–</span>
              <span class="ij-now-sep">•</span>
            </span>
            <span class="ij-now-block" id="ijNowBlockB">
              <span class="ij-now-item" id="ijNowLabelB">–</span>
              <span class="ij-now-sep">•</span>
            </span>
          </span>
        </span>

        <span class="ij-now-sr" id="ijNowText">–</span>
      </a>

      <div class="ij-topbar-right">
        <div class="ij-castWrap" title="<?= htmlspecialchars($PL['cast'], ENT_QUOTES, 'UTF-8') ?>">
          <google-cast-launcher id="ijCastLauncher"></google-cast-launcher>
        </div>

        <div class="ij-lang" role="group" aria-label="<?= htmlspecialchars(ij_t('lang_label', $lang), ENT_QUOTES, 'UTF-8') ?>">
          <a
            class="ij-lang-btn<?= $lang==='sv' ? ' active' : '' ?>"
            data-lang-switch="sv"
            data-no-ajax="1"
            href="<?= htmlspecialchars($svHref, ENT_QUOTES, 'UTF-8') ?>"
          >SV</a>
          <a
            class="ij-lang-btn<?= $lang==='en' ? ' active' : '' ?>"
            data-lang-switch="en"
            data-no-ajax="1"
            href="<?= htmlspecialchars($enHref, ENT_QUOTES, 'UTF-8') ?>"
          >EN</a>
        </div>

        <span class="ij-badge"><?= htmlspecialchars(ij_host(), ENT_QUOTES, 'UTF-8') ?></span>

        <button class="ij-burger" type="button" id="ijBurger" aria-label="Meny" aria-expanded="false" aria-controls="ijDrawer">☰</button>
      </div>
    </div>
  </header>

  <!-- ===== Global persistent player ===== -->
  <div class="ij-global-playerbar" id="ijGlobalPlayerbar" aria-label="Player">
    <div class="ij-global-playerwrap">
      <img id="ijGpCover" class="ij-gp-cover" alt="" src="" />
      <div class="ij-gp-meta">
        <div id="ijGpTitle" class="ij-gp-title">—</div>
        <div id="ijGpArtist" class="ij-gp-artist"><?= htmlspecialchars($PL['no_track'], ENT_QUOTES, 'UTF-8') ?></div>
      </div>

      <div class="ij-gp-seek">
        <span id="ijGpCur" class="ij-gp-time cur">0:00</span>
        <input id="ijGpSeek" class="ij-gp-slider" type="range" min="0" max="1000" value="0" />
        <span id="ijGpDur" class="ij-gp-time dur">0:00</span>
      </div>

      <div class="ij-gp-actions">
        <button id="ijGpPrev" class="ij-gp-btn" type="button" title="<?= htmlspecialchars($PL['prev'], ENT_QUOTES, 'UTF-8') ?>">⏮</button>
        <button id="ijGpPlay" class="ij-gp-btn primary" type="button" title="<?= htmlspecialchars($PL['play'], ENT_QUOTES, 'UTF-8') ?>">▶</button>
        <button id="ijGpNext" class="ij-gp-btn" type="button" title="<?= htmlspecialchars($PL['next'], ENT_QUOTES, 'UTF-8') ?>">⏭</button>

        <button id="ijGpShuffle" class="ij-gp-toggle" type="button" title="<?= htmlspecialchars($PL['shuffle'], ENT_QUOTES, 'UTF-8') ?>">🔀 <span><?= htmlspecialchars($PL['shuffle'], ENT_QUOTES, 'UTF-8') ?></span></button>
        <button id="ijGpAuto" class="ij-gp-toggle" type="button" title="<?= htmlspecialchars($PL['auto'], ENT_QUOTES, 'UTF-8') ?>">⏩ <span><?= htmlspecialchars($PL['auto'], ENT_QUOTES, 'UTF-8') ?></span></button>

        <div class="ij-gp-volwrap" id="ijGpVolWrap">
          <button id="ijGpVolBtn" class="ij-gp-btn" type="button" title="Volume"><span id="ijGpVolIcon">🔊</span></button>
          <div id="ijGpVolPop" class="ij-gp-volpop" role="dialog" aria-label="Volume">
            <div class="ij-gp-volpop-inner">
              <input id="ijGpVol" class="ij-gp-volslider" type="range" min="0" max="100" value="90" orient="vertical" />
              <div id="ijGpVolVal" class="ij-gp-volval">90%</div>
            </div>
          </div>
        </div>

        <div class="ij-castWrap">
          <google-cast-launcher id="ijGpCastBtn"></google-cast-launcher>
        </div>

        <button id="ijGpHide" class="ij-gp-btn" type="button" title="<?= htmlspecialchars($PL['hide'], ENT_QUOTES, 'UTF-8') ?>">✕</button>
      </div>
    </div>
  </div>

  <button id="ijGlobalPill" class="ij-global-pill" type="button" aria-label="<?= htmlspecialchars($PL['pill_np'], ENT_QUOTES, 'UTF-8') ?>">
    <img id="ijGpPillCover" class="ij-gpill-cover" alt="" src="" />
    <div class="ij-gpill-text">
      <div id="ijGpPillTitle" class="ij-gpill-title">—</div>
      <div id="ijGpPillSub" class="ij-gpill-sub"></div>
    </div>
    <div id="ijGpPillState" class="ij-gpill-state">▶</div>
  </button>

  <script>
    (function(){
      const burger = document.getElementById('ijBurger');
      const overlay = document.getElementById('ijOverlay');
      const drawer = document.getElementById('ijDrawer');
      const closeBtn = document.getElementById('ijDrawerClose');

      function openNav(){
        document.body.classList.add('ij-nav-open');
        if (burger) burger.setAttribute('aria-expanded','true');
        if (overlay) overlay.setAttribute('aria-hidden','false');
        if (drawer) drawer.setAttribute('aria-hidden','false');
        const first = drawer ? drawer.querySelector('a') : null;
        if (first) setTimeout(()=>first.focus(), 0);
      }

      function closeNav(){
        document.body.classList.remove('ij-nav-open');
        if (burger) burger.setAttribute('aria-expanded','false');
        if (overlay) overlay.setAttribute('aria-hidden','true');
        if (drawer) drawer.setAttribute('aria-hidden','true');
        if (burger) burger.focus();
      }

      function toggleNav(){
        document.body.classList.contains('ij-nav-open') ? closeNav() : openNav();
      }

      if (burger) burger.addEventListener('click', (e)=>{ e.preventDefault(); toggleNav(); });
      if (overlay) overlay.addEventListener('click', closeNav);
      if (closeBtn) closeBtn.addEventListener('click', closeNav);

      if (drawer){
        drawer.addEventListener('click', (e)=>{
          const a = e.target.closest('a');
          if (a) closeNav();
        });
      }

      document.addEventListener('keydown', (e)=>{
        if (e.key === 'Escape') closeNav();
      });

      window.addEventListener('resize', ()=>{
        if (window.innerWidth > 820) closeNav();
      });

      window.__ijCloseNav = closeNav;
    })();

    (function(){
      function buildLangHref(targetLang){
        const url = new URL(window.location.href);
        url.searchParams.set('lang', targetLang);
        return url.pathname + url.search + url.hash;
      }

      function updateLangSwitchers(){
        const links = document.querySelectorAll('[data-lang-switch]');
        if (!links.length) return;

        const current = new URL(window.location.href);
        const currentLang = current.searchParams.get('lang') || <?= json_encode($lang) ?>;

        links.forEach((link) => {
          const targetLang = String(link.getAttribute('data-lang-switch') || '').trim();
          if (!targetLang) return;

          link.setAttribute('href', buildLangHref(targetLang));
          link.classList.toggle('active', currentLang === targetLang);
        });
      }

      document.addEventListener('click', (e) => {
        const a = e.target.closest('[data-lang-switch]');
        if (!a) return;

        e.preventDefault();
        window.location.href = a.href;
      });

      window.addEventListener('ij:shell-after-swap', updateLangSwitchers);
      window.addEventListener('popstate', updateLangSwitchers);
      updateLangSwitchers();
    })();

    (function(){
      const dot  = document.getElementById('ijNowDot');
      const la   = document.getElementById('ijNowLabelA');
      const lb   = document.getElementById('ijNowLabelB');
      const sr   = document.getElementById('ijNowText');
      const wrap = document.getElementById('ijNow');
      const strip= document.getElementById('ijNowStrip');
      if (!dot || !la || !lb || !sr || !wrap || !strip) return;

      const url = '/api/now_public.php?lang=' + encodeURIComponent(<?= json_encode($lang) ?>);

      function setSpeedByText(text){
        const len = (text || '').length;
        const dur = Math.min(22, Math.max(10, Math.round(len / 2.2)));
        strip.style.animationDuration = dur + 's';
      }

      function setState({ label, live, href }){
        const text = (label && String(label).trim()) ? String(label).trim() : '–';
        la.textContent = text;
        lb.textContent = text;
        sr.textContent = text;
        dot.style.background = live ? '#22c55e' : '#999';

        if (href && String(href).trim()) {
          wrap.href = String(href);
          wrap.style.cursor = 'pointer';
        } else {
          wrap.href = '#';
          wrap.style.cursor = 'default';
        }

        setSpeedByText(text);
      }

      async function tick(){
        try{
          const r = await fetch(url, { cache: 'no-store' });
          const j = await r.json();
          if (!j || !j.ok) { setState({ label:'–', live:false, href:null }); return; }
          setState({
            label: (j.label ?? j.track_id ?? '–'),
            live: !!j.now,
            href: (j.url ?? null)
          });
        } catch(e){
          setState({ label:'–', live:false, href:null });
        }
      }

      tick();
      setInterval(tick, 3000);
    })();

    /* ===== Global Cast + Player ===== */
    (function(){
      if (window.__ijPlayer) return;

      const STR = {
        play: <?= json_encode($PL['play']) ?>,
        pause: <?= json_encode($PL['pause']) ?>,
        noTrack: <?= json_encode($PL['no_track']) ?>
      };

      const bar = document.getElementById('ijGlobalPlayerbar');
      const cover = document.getElementById('ijGpCover');
      const title = document.getElementById('ijGpTitle');
      const artist = document.getElementById('ijGpArtist');
      const playBtn = document.getElementById('ijGpPlay');
      const prevBtn = document.getElementById('ijGpPrev');
      const nextBtn = document.getElementById('ijGpNext');
      const shuffleBtn = document.getElementById('ijGpShuffle');
      const autoBtn = document.getElementById('ijGpAuto');
      const hideBtn = document.getElementById('ijGpHide');
      const seek = document.getElementById('ijGpSeek');
      const cur = document.getElementById('ijGpCur');
      const dur = document.getElementById('ijGpDur');

      const pill = document.getElementById('ijGlobalPill');
      const pillCover = document.getElementById('ijGpPillCover');
      const pillTitle = document.getElementById('ijGpPillTitle');
      const pillSub = document.getElementById('ijGpPillSub');
      const pillState = document.getElementById('ijGpPillState');

      const volWrap = document.getElementById('ijGpVolWrap');
      const volBtn = document.getElementById('ijGpVolBtn');
      const vol = document.getElementById('ijGpVol');
      const volIcon = document.getElementById('ijGpVolIcon');
      const volVal = document.getElementById('ijGpVolVal');

      const castBtns = [
        document.getElementById('ijCastLauncher'),
        document.getElementById('ijGpCastBtn')
      ].filter(Boolean);

      const audio = new Audio();
      audio.preload = 'none';

      let current = null;
      let queue = [];
      let hidden = false;
      let shuffle = (localStorage.getItem('ij_shuffle') === '1');
      let autoNext = (localStorage.getItem('ij_auto_next') === '1');
      let seekDragging = false;
      let historyStack = [];

      let volPct = Number(localStorage.getItem('ij_vol') || '90');
      if (!isFinite(volPct)) volPct = 90;
      volPct = Math.max(0, Math.min(100, volPct));
      audio.volume = volPct / 100;
      if (vol) vol.value = String(volPct);

      let _broadcastKey = '';
      let _playTimer = null;
      let _loggedTrackId = '';

      const castState = {
        apiReady: false,
        connected: false,
        context: null,
        remotePlayer: null,
        remoteController: null
      };

      function fmt(sec){
        if (!isFinite(sec) || sec < 0) sec = 0;
        sec = Math.floor(sec);
        const m = Math.floor(sec / 60);
        const s = sec % 60;
        return m + ':' + String(s).padStart(2, '0');
      }

      function getTrim(track){
        const startSec = Math.max(0, (track?.startMs || 0) / 1000);
        const endSecRaw = (track?.endMs || 0) / 1000;
        const endSec = endSecRaw > 0 ? Math.max(endSecRaw, startSec) : 0;
        return { startSec, endSec };
      }

      function effectiveDuration(track){
        const { startSec, endSec } = getTrim(track);

        if (isCasting() && castState.remotePlayer) {
          const rp = castState.remotePlayer;
          const rawDur = Number(rp.duration || 0);
          const end = endSec > 0 ? Math.min(endSec, rawDur || endSec) : rawDur;
          const eff = Math.max(0, (end || 0) - startSec);
          return { startSec, end, eff };
        }

        const d = isFinite(audio.duration) ? audio.duration : 0;
        const end = endSec > 0 ? Math.min(endSec, d || endSec) : d;
        const eff = Math.max(0, (end || 0) - startSec);
        return { startSec, end, eff };
      }

      function currentTimeRaw(){
        if (isCasting() && castState.remotePlayer) {
          return Number(castState.remotePlayer.currentTime || 0);
        }
        return Number(audio.currentTime || 0);
      }

      function isPausedNow(){
        if (isCasting() && castState.remotePlayer) {
          return !!castState.remotePlayer.isPaused;
        }
        return !!audio.paused;
      }

      function updateVolIcon(){
        const v = Number(vol?.value || 0);
        if (volIcon) volIcon.textContent = (v <= 0) ? '🔇' : (v < 35 ? '🔈' : (v < 70 ? '🔉' : '🔊'));
        if (volVal) volVal.textContent = Math.round(v) + '%';
      }

      function postPlayToApi(track, playedMs = 5000){
        try{
          const tid = String(track?.id || '').trim();
          if (!tid) return;

          const body = new URLSearchParams();
          body.set('track_id', tid);
          body.set('client_id', 'audio_site');
          body.set('event_key', 'audio-site');
          body.set('played_ms', String(playedMs || 5000));
          if (track?.title) body.set('title', String(track.title).trim());
          if (track?.artist) body.set('artist', String(track.artist).trim());
          body.set('track_url', '/track/?id=' + encodeURIComponent(tid));

          fetch('/api/track_play.php', {
            method: 'POST',
            headers: { 'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8' },
            body: body.toString(),
            keepalive: true,
            cache: 'no-store',
          }).catch(()=>{});
        }catch{}
      }

      function clearPlayTimer(){
        if (_playTimer) {
          clearTimeout(_playTimer);
          _playTimer = null;
        }
      }

      function schedulePlayLog(){
        clearPlayTimer();
        if (!current || !current.id) return;
        const tid = String(current.id);

        _playTimer = setTimeout(()=>{
          if (!current || String(current.id) !== tid) return;
          if (isPausedNow()) return;
          _loggedTrackId = tid;
          postPlayToApi(current, 5000);
        }, 5200);
      }

      function persist(){
        try{
          sessionStorage.setItem('ij_player_state', JSON.stringify({
            current,
            queue,
            hidden,
            shuffle,
            autoNext,
            currentTime: Number(currentTimeRaw() || 0),
            paused: !!isPausedNow(),
            volume: Number(isCasting() && castState.remotePlayer ? castState.remotePlayer.volumeLevel : audio.volume || 0)
          }));
        }catch{}
      }

      function restore(){
        try{
          const raw = sessionStorage.getItem('ij_player_state');
          if (!raw) return;
          const s = JSON.parse(raw);
          current = s.current || null;
          queue = Array.isArray(s.queue) ? s.queue : [];
          hidden = !!s.hidden;
          shuffle = !!s.shuffle;
          autoNext = !!s.autoNext;

          if (typeof s.volume === 'number' && isFinite(s.volume)) {
            const vv = Math.max(0, Math.min(1, s.volume));
            audio.volume = vv;
            if (vol) vol.value = String(Math.round(vv * 100));
          }

          if (current && current._resolvedUrl){
            audio.src = current._resolvedUrl;
            if (isFinite(s.currentTime)) {
              try { audio.currentTime = Number(s.currentTime || 0); } catch {}
            }
            if (!s.paused) {
              audio.play().then(()=> schedulePlayLog()).catch(()=>{});
            }
          }
        }catch{}
      }

      function dispatch(force = false){
        const key = JSON.stringify({
          id: current?.id || '',
          paused: !!isPausedNow(),
          hidden,
          shuffle,
          autoNext,
          casting: isCasting(),
          q: queue.map(x => x?.id || '')
        });

        if (!force && key === _broadcastKey) return;
        _broadcastKey = key;

        window.dispatchEvent(new CustomEvent('ij:player-state', {
          detail: {
            current,
            queue,
            paused: isPausedNow(),
            hidden,
            shuffle,
            autoNext,
            currentTime: Number(currentTimeRaw() || 0),
            duration: Number(isCasting() && castState.remotePlayer ? castState.remotePlayer.duration || 0 : audio.duration || 0),
            casting: isCasting()
          }
        }));
      }

      function setCastButtonsActive(on){
        castBtns.forEach(btn => btn.classList.toggle('ij-gp-castActive', !!on));
      }

      function updateUI(forceDispatch = false){
        if (shuffleBtn) shuffleBtn.classList.toggle('on', !!shuffle);
        if (autoBtn) autoBtn.classList.toggle('on', !!autoNext);
        updateVolIcon();
        setCastButtonsActive(isCasting());

        if (!current){
          if (title) title.textContent = '—';
          if (artist) artist.textContent = STR.noTrack;
          if (cover) cover.src = '';
          if (playBtn) playBtn.textContent = '▶';
          if (cur) cur.textContent = '0:00';
          if (dur) dur.textContent = '0:00';
          if (seek) seek.value = 0;
          if (prevBtn) prevBtn.disabled = true;
          if (nextBtn) nextBtn.disabled = true;
          if (bar) bar.classList.remove('show');
          document.body.classList.remove('has-player');
          if (pill) pill.classList.remove('show');
          if (pillTitle) pillTitle.textContent = '—';
          if (pillSub) pillSub.textContent = '';
          if (pillState) pillState.textContent = '▶';
          persist();
          dispatch(forceDispatch);
          return;
        }

        if (title) title.textContent = current.title || '—';
        if (artist) artist.textContent = current.artist || '';
        if (cover) cover.src = current.image || '';
        if (playBtn) {
          playBtn.textContent = isPausedNow() ? '▶' : '⏸';
          playBtn.title = isPausedNow() ? STR.play : STR.pause;
        }

        const { startSec, eff } = effectiveDuration(current);
        const c = Math.max(0, (currentTimeRaw() || 0) - startSec);
        if (cur) cur.textContent = fmt(c);
        if (dur) dur.textContent = fmt(eff);

        if (!seekDragging && seek && eff > 0){
          seek.value = String(Math.max(0, Math.min(1000, Math.round((c / eff) * 1000))));
        }

        if (prevBtn) prevBtn.disabled = queue.length < 2;
        if (nextBtn) nextBtn.disabled = queue.length < 2;

        if (pillCover) pillCover.src = current.image || '';
        if (pillTitle) pillTitle.textContent = current.title || '—';
        if (pillSub) pillSub.textContent = current.artist || '';
        if (pillState) pillState.textContent = isPausedNow() ? '▶' : '⏸';

        if (hidden){
          if (bar) bar.classList.remove('show');
          document.body.classList.remove('has-player');
          if (pill) pill.classList.add('show');
        } else {
          if (bar) bar.classList.add('show');
          document.body.classList.add('has-player');
          if (pill) pill.classList.remove('show');
        }

        persist();
        dispatch(forceDispatch);
      }

      function setQueue(list){
        queue = Array.isArray(list) ? list.slice() : [];
        updateUI(true);
      }

      function isCasting(){
        return !!(castState.connected && castState.context && castState.context.getCurrentSession());
      }

      async function loadToCast(track, autoplay = true){
        if (!track || !track._resolvedUrl || !castState.context) return false;
        const session = castState.context.getCurrentSession();
        if (!session || !window.chrome || !chrome.cast || !chrome.cast.media) return false;

        const mime = String(track._resolvedUrl).toLowerCase().endsWith('.m3u8')
          ? 'application/x-mpegURL'
          : (String(track._resolvedUrl).toLowerCase().endsWith('.mp3') ? 'audio/mpeg' : 'audio/*');

        const mediaInfo = new chrome.cast.media.MediaInfo(track._resolvedUrl, mime);
        mediaInfo.metadata = new chrome.cast.media.MusicTrackMediaMetadata();
        mediaInfo.metadata.title = track.title || '';
        mediaInfo.metadata.artist = track.artist || '';
        if (track.image) {
          mediaInfo.metadata.images = [new chrome.cast.Image(track.image)];
        }

        const req = new chrome.cast.media.LoadRequest(mediaInfo);
        req.autoplay = !!autoplay;
        const trim = getTrim(track);
        req.currentTime = trim.startSec || 0;

        try{
          await session.loadMedia(req);
          _loggedTrackId = '';
          schedulePlayLog();
          return true;
        }catch(e){
          console.error('Cast load failed', e);
          return false;
        }
      }

      async function playTrack(track, opts = {}){
        if (!track || !track._resolvedUrl) return;
        if (Array.isArray(opts.queue)) queue = opts.queue.slice();

        current = track;
        hidden = false;
        _loggedTrackId = '';

        if (isCasting()){
          const ok = await loadToCast(track, true);
          if (ok) {
            updateUI(true);
            return;
          }
        }

        if (audio.src !== track._resolvedUrl){
          audio.pause();
          audio.currentTime = 0;
          audio.src = track._resolvedUrl;
        }

        const { startSec } = getTrim(track);
        if ((audio.currentTime === 0 || audio.currentTime < startSec)){
          try { audio.currentTime = startSec; } catch {}
        }

        try{
          await audio.play();
          schedulePlayLog();
        }catch(e){
          console.error(e);
        }

        updateUI(true);
      }

      function stopToStart(){
        if (!current) return;
        clearPlayTimer();
        const { startSec } = getTrim(current);

        if (isCasting() && castState.remotePlayer && castState.remoteController){
          try{
            castState.remotePlayer.currentTime = startSec;
            castState.remoteController.seek();
            castState.remoteController.playOrPause();
            if (!castState.remotePlayer.isPaused) {
              castState.remoteController.playOrPause();
            }
          }catch(e){}
          updateUI(true);
          return;
        }

        audio.pause();
        try { audio.currentTime = startSec; } catch {}
        updateUI(true);
      }

      function toggle(){
        if (!current){
          if (queue.length) playTrack(queue[0], { queue });
          return;
        }

        if (isCasting() && castState.remoteController && castState.remotePlayer){
          try{
            castState.remoteController.playOrPause();
          }catch(e){}
          updateUI(true);
          return;
        }

        if (audio.paused){
          audio.play().then(()=> schedulePlayLog()).catch(()=>{});
        } else {
          clearPlayTimer();
          audio.pause();
        }
        updateUI(true);
      }

      function next(){
        if (!queue.length || !current) return;
        clearPlayTimer();

        if (shuffle){
          historyStack.push(current.id);
          if (queue.length === 1) {
            playTrack(queue[0], { queue });
            return;
          }
          let tries = 0;
          let pick = queue[Math.floor(Math.random() * queue.length)];
          while (pick.id === current.id && tries < 10){
            pick = queue[Math.floor(Math.random() * queue.length)];
            tries++;
          }
          playTrack(pick, { queue });
          return;
        }

        const idx = queue.findIndex(x => x.id === current.id);
        const nextIdx = (idx >= 0 ? idx + 1 : 0) % queue.length;
        playTrack(queue[nextIdx], { queue });
      }

      function prev(){
        if (!queue.length || !current) return;
        clearPlayTimer();

        if (shuffle){
          const lastId = historyStack.pop();
          const t = queue.find(x => x.id === lastId);
          if (t) {
            playTrack(t, { queue });
            return;
          }
        }

        const idx = queue.findIndex(x => x.id === current.id);
        const prevIdx = (idx >= 0 ? (idx - 1 + queue.length) : 0) % queue.length;
        playTrack(queue[prevIdx], { queue });
      }

      function setShuffle(on){
        shuffle = !!on;
        localStorage.setItem('ij_shuffle', shuffle ? '1' : '0');
        if (!shuffle) historyStack = [];
        updateUI(true);
      }

      function setAutoNext(on){
        autoNext = !!on;
        localStorage.setItem('ij_auto_next', autoNext ? '1' : '0');
        updateUI(true);
      }

      function closeVolPop(){
        if (volWrap) volWrap.classList.remove('open');
      }

      function bindCastEvents(){
        if (!castState.context) return;

        castState.context.addEventListener(
          cast.framework.CastContextEventType.SESSION_STATE_CHANGED,
          (ev) => {
            const connected = !!castState.context.getCurrentSession() &&
              (ev.sessionState === cast.framework.SessionState.SESSION_STARTED ||
               ev.sessionState === cast.framework.SessionState.SESSION_RESUMED ||
               ev.sessionState === cast.framework.SessionState.SESSION_STARTING);

            castState.connected = connected;

            if (!connected) {
              updateUI(true);
              return;
            }

            const session = castState.context.getCurrentSession();
            if (session && current && current._resolvedUrl) {
              const media = session.getMediaSession();
              if (!media) {
                loadToCast(current, !audio.paused);
              }
            }

            updateUI(true);
          }
        );

        if (castState.remoteController) {
          const RTC = cast.framework.RemotePlayerEventType;
          const events = [
            RTC.IS_PAUSED_CHANGED,
            RTC.CURRENT_TIME_CHANGED,
            RTC.DURATION_CHANGED,
            RTC.VOLUME_LEVEL_CHANGED,
            RTC.IS_MUTED_CHANGED,
            RTC.IS_CONNECTED_CHANGED,
            RTC.PLAYER_STATE_CHANGED
          ];

          events.forEach(evType => {
            castState.remoteController.addEventListener(evType, () => {
              if (vol && castState.remotePlayer) {
                const vv = Math.round((Number(castState.remotePlayer.volumeLevel || 0)) * 100);
                vol.value = String(Math.max(0, Math.min(100, vv)));
              }
              updateUI(false);
            });
          });
        }
      }

      function initCast(){
        if (!window.cast || !window.cast.framework || !window.chrome || !chrome.cast) return;

        castState.apiReady = true;
        castState.context = cast.framework.CastContext.getInstance();
        castState.context.setOptions({
          receiverApplicationId: chrome.cast.media.DEFAULT_MEDIA_RECEIVER_APP_ID,
          autoJoinPolicy: chrome.cast.AutoJoinPolicy.ORIGIN_SCOPED
        });

        castState.remotePlayer = new cast.framework.RemotePlayer();
        castState.remoteController = new cast.framework.RemotePlayerController(castState.remotePlayer);
        bindCastEvents();
        castState.connected = !!castState.context.getCurrentSession();
        updateUI(true);
      }

      window.__onGCastApiAvailable = function(isAvailable){
        if (isAvailable) initCast();
      };

      if (prevBtn) prevBtn.addEventListener('click', prev);
      if (nextBtn) nextBtn.addEventListener('click', next);
      if (playBtn) playBtn.addEventListener('click', toggle);
      if (hideBtn) hideBtn.addEventListener('click', ()=>{ hidden = true; updateUI(true); });
      if (pill) pill.addEventListener('click', ()=>{ hidden = false; updateUI(true); });
      if (shuffleBtn) shuffleBtn.addEventListener('click', ()=> setShuffle(!shuffle));
      if (autoBtn) autoBtn.addEventListener('click', ()=> setAutoNext(!autoNext));

      if (volBtn) {
        volBtn.addEventListener('click', (e)=>{
          e.preventDefault();
          e.stopPropagation();
          if (volWrap) volWrap.classList.toggle('open');
        });
      }
      document.addEventListener('pointerdown', (e)=>{
        if (!volWrap || !volWrap.classList.contains('open')) return;
        if (volWrap.contains(e.target)) return;
        closeVolPop();
      });
      document.addEventListener('keydown', (e)=>{
        if (e.key === 'Escape') closeVolPop();
      });
      if (vol) {
        vol.addEventListener('input', ()=>{
          const v = Math.max(0, Math.min(100, Number(vol.value) || 0));
          localStorage.setItem('ij_vol', String(v));

          if (isCasting() && castState.remotePlayer && castState.remoteController) {
            castState.remotePlayer.volumeLevel = v / 100;
            try { castState.remoteController.setVolumeLevel(); } catch(e){}
          } else {
            audio.volume = v / 100;
          }

          updateUI(false);
        });
      }

      if (seek) {
        seek.addEventListener('pointerdown', ()=>{ seekDragging = true; });
        seek.addEventListener('pointerup', ()=>{
          seekDragging = false;
          if (!current) return;
          const { startSec, eff } = effectiveDuration(current);
          if (eff <= 0) return;
          const ratio = (Number(seek.value) || 0) / 1000;
          const target = startSec + (ratio * eff);

          if (isCasting() && castState.remotePlayer && castState.remoteController) {
            castState.remotePlayer.currentTime = target;
            try { castState.remoteController.seek(); } catch(e){}
          } else {
            try { audio.currentTime = target; } catch {}
          }

          updateUI(false);
        });
        seek.addEventListener('input', ()=>{
          if (!current) return;
          const { eff } = effectiveDuration(current);
          if (eff <= 0) return;
          const ratio = (Number(seek.value) || 0) / 1000;
          if (cur) cur.textContent = fmt(ratio * eff);
        });
      }

      audio.addEventListener('loadedmetadata', ()=> updateUI(false));
      audio.addEventListener('timeupdate', ()=>{
        if (!current || isCasting()) return;
        const { endSec } = getTrim(current);
        if (endSec > 0 && audio.currentTime >= endSec - 0.03){
          clearPlayTimer();
          if (autoNext) next();
          else stopToStart();
          return;
        }
        updateUI(false);
      });
      audio.addEventListener('play', ()=> updateUI(true));
      audio.addEventListener('pause', ()=> {
        clearPlayTimer();
        updateUI(true);
      });
      audio.addEventListener('ended', ()=>{
        clearPlayTimer();
        if (autoNext) next();
        else updateUI(true);
      });

      window.__ijPlayer = {
        audio,
        getState(){
          return {
            current,
            queue,
            paused: isPausedNow(),
            hidden,
            shuffle,
            autoNext,
            casting: isCasting()
          };
        },
        setQueue,
        playTrack,
        toggle,
        next,
        prev,
        setShuffle,
        setAutoNext,
        stopToStart,
        updateUI,
        isCasting,
        cast: castState
      };

      restore();
      updateVolIcon();
      updateUI(true);
    })();

    /* ===== Shell foundation ===== */
    (function(){
      if (window.__ijShell) return;

      const ALLOW_RE = /^(\/($|\?)|\/library\/?|\/top\/?|\/track\/?|\/artist\/?)/i;

      const shell = {
        busy: false,
        cleanupFns: [],

        registerCleanup(fn){
          if (typeof fn === 'function') this.cleanupFns.push(fn);
        },

        runCleanup(){
          const list = this.cleanupFns.slice();
          this.cleanupFns = [];
          for (const fn of list){
            try { fn(); } catch(e){ console.error('Cleanup failed', e); }
          }
        },

        shouldHandleLink(a, ev){
          if (!a) return false;
          if (ev.defaultPrevented) return false;
          if (a.target && a.target !== '_self') return false;
          if (a.hasAttribute('download')) return false;
          if (a.dataset.noAjax === '1') return false;
          if (ev.metaKey || ev.ctrlKey || ev.shiftKey || ev.altKey) return false;

          const href = a.getAttribute('href') || '';
          if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) return false;

          const url = new URL(a.href, location.href);
          if (url.origin !== location.origin) return false;
          if (url.hash && url.pathname === location.pathname && url.search === location.search) return false;

          return ALLOW_RE.test(url.pathname);
        },

        updateNavActive(url){
          const path = new URL(url, location.href).pathname;
          const links = document.querySelectorAll('[data-ij-nav]');

          links.forEach(link => {
            const navPath = String(link.getAttribute('data-ij-nav') || '').trim();
            let active = false;

            if (navPath === '/') {
              active = (path === '/');
            } else if (navPath !== '') {
              active = path === navPath || path.startsWith(navPath + '/');
            }

            link.classList.toggle('active', active);
          });
        },

        async executeScripts(container){
          const scripts = Array.from(container.querySelectorAll('script'));
          for (const oldScript of scripts){
            const newScript = document.createElement('script');

            for (const attr of oldScript.attributes){
              newScript.setAttribute(attr.name, attr.value);
            }

            if (oldScript.src) {
              await new Promise((resolve, reject) => {
                newScript.onload = resolve;
                newScript.onerror = reject;
                oldScript.parentNode.replaceChild(newScript, oldScript);
              }).catch((e) => {
                console.error('Script load failed', e);
              });
            } else {
              newScript.textContent = oldScript.textContent || '';
              oldScript.parentNode.replaceChild(newScript, oldScript);
            }
          }
        },

        updateTitle(doc){
          const t = doc.querySelector('title');
          if (t) document.title = t.textContent || document.title;
        },

        updateLang(doc){
          const html = doc.documentElement;
          if (html && html.lang) document.documentElement.lang = html.lang;
        },

        async swapFromHtml(html, url, options = {}){
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const nextMain = doc.querySelector('#ijAppMain');
          const curMain = document.querySelector('#ijAppMain');

          if (!nextMain || !curMain) {
            location.href = url;
            return;
          }

          this.runCleanup();
          curMain.innerHTML = nextMain.innerHTML;
          this.updateTitle(doc);
          this.updateLang(doc);
          this.updateNavActive(url);

          if (typeof window.__ijCloseNav === 'function') {
            window.__ijCloseNav();
          }

          await this.executeScripts(curMain);

          if (options.replace) {
            history.replaceState({ url }, '', url);
          } else {
            history.pushState({ url }, '', url);
          }

          if (options.scroll !== false) {
            window.scrollTo({ top: 0, left: 0, behavior: 'auto' });
          }

          window.dispatchEvent(new CustomEvent('ij:shell-after-swap', {
            detail: { url, main: curMain }
          }));
        },

        async navigate(url, options = {}){
          if (this.busy) return;
          this.busy = true;
          document.body.classList.add('ij-shell-loading');

          try{
            const res = await fetch(url, {
              method: 'GET',
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-IJ-Shell': '1'
              },
              credentials: 'same-origin',
              cache: 'no-store'
            });

            if (!res.ok) throw new Error('HTTP ' + res.status);

            const html = await res.text();
            await this.swapFromHtml(html, url, options);
          } catch (e){
            console.error('Shell navigation failed, fallback to hard load', e);
            location.href = url;
          } finally {
            this.busy = false;
            document.body.classList.remove('ij-shell-loading');
          }
        },

        init(){
          this.updateNavActive(location.href);

          document.addEventListener('click', (ev) => {
            const a = ev.target.closest('a');
            if (!this.shouldHandleLink(a, ev)) return;
            ev.preventDefault();
            this.navigate(a.href);
          });

          window.addEventListener('popstate', () => {
            this.navigate(location.href, { replace: true, scroll: false });
          });

          window.__ijRegisterPageCleanup = (fn) => this.registerCleanup(fn);
          window.__ijShellNavigate = (url, options = {}) => this.navigate(url, options);
        }
      };

      window.__ijShell = shell;
      shell.init();
    })();
  </script>

  <main class="ij-main" id="ijAppMain">