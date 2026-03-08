<?php
// /domains/icebeats.io/public_html/index.php
declare(strict_types=1);

require __DIR__ . '/_partials/i18n.php';
require __DIR__ . '/_partials/meta.php';

$lang = ij_lang();

$title = ($lang === 'en')
  ? 'Share music, tracks & jingles - iceBeats.io'
  : 'iceBeats.io – Dela musik, tracks & jinglar';

$desc = ($lang === 'en')
  ? 'Upload tracks, jingles and sound effects. Build your artist presence, share direct links and let others play your music instantly on iceBeats.io.'
  : 'Ladda upp låtar, jinglar och ljudeffekter. Bygg din artistnärvaro, dela länkar och låt andra spela din musik direkt på iceBeats.io.';

$canonical = ij_abs('/?lang=' . $lang);

$meta = ij_build_meta([
  'title' => $title,
  'description' => $desc,
  'canonical' => $canonical,
  'image' => ij_abs('/share/og.jpg'),
  'type' => 'website',
]);

$pageTitle = $meta['title'];
$pageHead = ij_render_meta($meta);

require __DIR__ . '/_partials/header.php';

$T = [
  'sv' => [
    'hero_kicker' => 'iceBeats.io',
    'h1' => 'Dela din musik med världen',
    'sub' => 'Ladda upp låtar, jinglar och sound effects. Bygg din artistprofil, bli upptäckt och låt fler spela dina tracks direkt i webben.',
    'hero_badge_1' => 'Direktuppspelning',
    'hero_badge_2' => 'Artistprofiler',
    'hero_badge_3' => 'Delbara länkar',
    'hero_stat_1' => 'Tracks, jinglar & SFX',
    'hero_stat_2' => 'Spela direkt i webben',
    'hero_stat_3' => 'Dela med en länk',
    'cta_library' => '🎵 Utforska låtar',
    'cta_top' => '🏆 Se topplistan',
    'cta_upload' => '⬆️ Ladda upp musik',
    'searchPh' => 'Sök titel eller artist…',
    'genreAll' => 'Alla genrer',
    'quick_search' => 'Snabbsök',

    'value_title' => 'Bygg, upptäck och spela',
    'value_sub' => 'iceBeats.io är byggt för dig som vill dela musik, hitta rätt track snabbt och spela upp direkt utan krångel.',
    'value_card_1_title' => 'För artister',
    'value_card_1_text' => 'Ladda upp musik, bygg en tydlig artistprofil och gör det enkelt för fler att hitta och spela dina tracks.',
    'value_card_2_title' => 'För arenor & DJs',
    'value_card_2_text' => 'Hitta rätt låt snabbt, spela direkt i webben och dela länkar till specifika tracks när tempot är högt.',
    'value_card_3_title' => 'För creators',
    'value_card_3_text' => 'Samla jinglar, introspår och sound effects på ett ställe med enkel åtkomst för både test, delning och uppspelning.',

    'popular' => 'Populärt just nu',
    'newest' => 'Senast uppladdat',
    'categories' => 'Kategorier',
    'week' => 'Vecka',
    'month' => 'Månad',
    'loading' => 'Laddar…',
    'loadErr' => 'Kunde inte ladda /library.json.',
    'open_library_filtered' => 'Öppna i Låtar →',
    'share' => 'Dela',
    'popular_sub' => 'De mest spelade spåren just nu.',
    'newest_sub' => 'Färska uppladdningar från biblioteket.',
    'categories_sub' => 'Hitta rätt vibe snabbare.',
  ],
  'en' => [
    'hero_kicker' => 'iceBeats.io',
    'h1' => 'Share your music with the world',
    'sub' => 'Upload tracks, jingles and sound effects. Build your artist profile, get discovered and let others play your music instantly in the browser.',
    'hero_badge_1' => 'Instant playback',
    'hero_badge_2' => 'Artist profiles',
    'hero_badge_3' => 'Shareable links',
    'hero_stat_1' => 'Tracks, jingles & SFX',
    'hero_stat_2' => 'Play instantly in browser',
    'hero_stat_3' => 'Share with one link',
    'cta_library' => '🎵 Explore tracks',
    'cta_top' => '🏆 View top list',
    'cta_upload' => '⬆️ Upload music',
    'searchPh' => 'Search title or artist…',
    'genreAll' => 'All genres',
    'quick_search' => 'Quick search',

    'value_title' => 'Build, discover and play',
    'value_sub' => 'iceBeats.io is built for people who want to share music, find the right track fast and play instantly without friction.',
    'value_card_1_title' => 'For artists',
    'value_card_1_text' => 'Upload music, build a clear artist profile and make it easier for more listeners to discover and play your tracks.',
    'value_card_2_title' => 'For arenas & DJs',
    'value_card_2_text' => 'Find the right track fast, play instantly in the browser and share direct links when timing matters.',
    'value_card_3_title' => 'For creators',
    'value_card_3_text' => 'Collect jingles, intro tracks and sound effects in one place with easy access for testing, sharing and playback.',

    'popular' => 'Popular right now',
    'newest' => 'Newest uploads',
    'categories' => 'Categories',
    'week' => 'Week',
    'month' => 'Month',
    'loading' => 'Loading…',
    'loadErr' => 'Could not load /library.json.',
    'open_library_filtered' => 'Open in Tracks →',
    'share' => 'Share',
    'popular_sub' => 'The most played tracks right now.',
    'newest_sub' => 'Fresh uploads from the library.',
    'categories_sub' => 'Find the right vibe faster.',
  ],
];
$L = $T[$lang] ?? $T['sv'];
?>

<style>
  :root { color-scheme: dark; }

  .ij-home-wrap{
    max-width: var(--max-header);
    margin: 0 auto;
    padding: 0 16px;
  }

  .ij-hero{
    max-width: var(--max-header);
    margin: 0 auto;
    padding: 10px 16px 0;
  }

  .ij-heroCard{
    position:relative;
    overflow:hidden;
    border:1px solid var(--border);
    border-radius:26px;
    padding:28px;
    background:
      radial-gradient(circle at top right, rgba(96,165,250,.18), transparent 28%),
      radial-gradient(circle at bottom left, rgba(37,99,235,.16), transparent 26%),
      linear-gradient(180deg, rgba(11,18,32,.96), rgba(15,23,42,.96));
    box-shadow: 0 20px 60px rgba(0,0,0,.22);
  }

  .ij-heroCard::before{
    content:"";
    position:absolute;
    inset:0;
    pointer-events:none;
    background: linear-gradient(135deg, rgba(255,255,255,.02), transparent 36%);
  }

  .ij-heroCard::after{
    content:"";
    position:absolute;
    inset:auto -80px -80px auto;
    width:220px;
    height:220px;
    border-radius:999px;
    background: radial-gradient(circle, rgba(96,165,250,.10), transparent 68%);
    pointer-events:none;
  }

  .ij-heroHead{
    display:grid;
    grid-template-columns:minmax(0, 1fr) auto;
    gap:24px;
    align-items:end;
  }

  .ij-heroMain{ min-width:0; }

  .ij-heroKicker{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    margin-bottom:14px;
    padding:7px 12px;
    border-radius:999px;
    border:1px solid rgba(96,165,250,.22);
    background:rgba(11,18,32,.55);
    color:#cbd5e1;
    font-size:11px;
    font-weight:900;
    letter-spacing:.55px;
    text-transform:uppercase;
  }

  .ij-heroKicker::before{
    content:"";
    width:8px;
    height:8px;
    border-radius:999px;
    background:var(--accent2);
    box-shadow:0 0 0 6px rgba(96,165,250,.10);
  }

  .ij-heroTitle{
    margin:0;
    font-size:50px;
    line-height:1.02;
    letter-spacing:-.03em;
    color:#f8fafc;
    max-width:780px;
  }

  .ij-heroSub{
    margin-top:14px;
    color:#9fb0ca;
    font-size:15px;
    line-height:1.85;
    max-width:760px;
  }

  .ij-heroBadgeGrid{
    display:grid;
    grid-template-columns:repeat(3, minmax(0, 1fr));
    gap:10px;
    margin-top:20px;
  }

  .ij-heroBadgeCard{
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    text-align:center;
    gap:8px;
    min-height:104px;
    padding:16px 12px;
    border-radius:20px;
    border:1px solid #24324f;
    background:rgba(11,18,32,.70);
    color:#dbe6f5;
  }

  .ij-heroBadgeIcon{
    width:52px;
    height:52px;
    border-radius:18px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
    border:1px solid rgba(96,165,250,.24);
    background:rgba(16,25,42,.82);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.03);
  }

  .ij-heroBadgeText{
    font-size:13px;
    font-weight:900;
    line-height:1.35;
  }

  .ij-ctaCol{
    display:flex;
    align-items:flex-end;
    justify-content:flex-end;
  }

  .ij-ctaRow{
    display:grid;
    grid-template-columns:1fr;
    gap:10px;
    width:min(340px, 100%);
  }

  .ij-ctaRow a{
    display:flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    font-weight:900;
    min-height:48px;
    padding:0 16px;
    border-radius:16px;
    text-align:center;
  }

  .ij-heroFeatureGrid{
    display:grid;
    grid-template-columns:repeat(3, minmax(0, 1fr));
    gap:12px;
    margin-top:20px;
  }

  .ij-heroFeature{
    display:flex;
    align-items:flex-start;
    gap:12px;
    border:1px solid #24324f;
    border-radius:20px;
    background:rgba(11,18,32,.55);
    padding:16px;
    min-width:0;
  }

  .ij-heroFeatureIcon{
    flex:0 0 auto;
    width:42px;
    height:42px;
    border-radius:14px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:20px;
    background:rgba(18,29,49,.95);
    border:1px solid rgba(96,165,250,.20);
    color:#bfdbfe;
  }

  .ij-heroFeatureText{
    min-width:0;
    color:#dbe6f5;
    font-size:13px;
    font-weight:850;
    line-height:1.55;
  }

  .ij-homeBarWrap{
    margin-top:18px;
    padding-top:18px;
    border-top:1px solid rgba(148,163,184,.10);
  }

  .ij-homeBarLabel{
    color:#94a3b8;
    font-size:12px;
    font-weight:900;
    letter-spacing:.2px;
    margin-bottom:10px;
  }

  .ij-homeBar{
    display:grid;
    grid-template-columns:minmax(0, 1fr) 220px auto;
    gap:10px;
    align-items:center;
  }

  .ij-homeBar .ij-input{
    min-width:0;
  }

  #btnGoLibrary{
    min-height:46px;
    padding:0 16px;
    border-radius:15px;
    white-space:nowrap;
  }

  .ij-valueSection{
    padding-top:18px;
  }

  .ij-valueCard{
    background: var(--card);
    border:1px solid var(--border);
    border-radius:22px;
    padding:22px;
  }

  .ij-valueHead{
    margin-bottom:16px;
  }

  .ij-valueTitle{
    margin:0;
    font-size:22px;
    line-height:1.15;
    letter-spacing:-.02em;
    color:#f8fafc;
  }

  .ij-valueSub{
    margin-top:10px;
    color:#94a3b8;
    font-size:14px;
    line-height:1.8;
    max-width:760px;
  }

  .ij-valueGrid{
    display:grid;
    grid-template-columns:repeat(3, minmax(0, 1fr));
    gap:14px;
  }

  .ij-valueItem{
    position:relative;
    border:1px solid #24324f;
    border-radius:18px;
    padding:18px;
    background:linear-gradient(180deg, rgba(11,18,32,.72), rgba(15,23,42,.72));
    min-height:180px;
  }

  .ij-valueIcon{
    width:52px;
    height:52px;
    border-radius:16px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:34px;
    border:1px solid rgba(96,165,250,.24);
    background:rgba(11,18,32,.72);
    margin-bottom:14px;
  }

  .ij-valueItemTitle{
    margin:0;
    font-size:16px;
    font-weight:950;
    color:#f1f5f9;
    letter-spacing:-.01em;
  }

  .ij-valueItemText{
    margin-top:10px;
    color:#9fb0ca;
    font-size:14px;
    line-height:1.8;
  }

  .ij-sections{
    padding: 18px 0 0;
  }

  .ij-secHead{
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap:14px;
    margin: 22px 0 12px;
    flex-wrap:wrap;
  }

  .ij-secHeadText{ min-width:0; }

  .ij-secTitle{
    font-size:18px;
    font-weight:950;
    letter-spacing:-.01em;
    color:#f1f5f9;
    margin:0;
  }

  .ij-secSub{
    margin-top:6px;
    color:#94a3b8;
    font-size:13px;
    line-height:1.6;
  }

  .ij-toggle{
    display:flex;
    gap:8px;
    align-items:center;
  }

  .ij-toggle .ij-btnGhost{
    padding: 8px 10px;
    border-radius: 12px;
    font-size: 12px;
  }

  .ij-grid{
    display:grid;
    grid-template-columns: repeat(auto-fill, minmax(168px, 1fr));
    gap: 12px;
  }

  .ij-coverCard{
    position:relative;
    border-radius:18px;
    overflow:hidden;
    aspect-ratio: 1 / 1;
    background: var(--card2);
    border: 1px solid var(--border);
    cursor:pointer;
    transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    user-select:none;
    -webkit-tap-highlight-color: transparent;
  }

  .ij-coverCard:hover{
    transform: translateY(-4px);
    border-color: rgba(96,165,250,.35);
    box-shadow: 0 18px 40px rgba(0,0,0,.35);
  }

  .ij-coverCard img{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
  }

  .ij-coverOverlay{
    position:absolute;
    inset:0;
    background: linear-gradient(to top, rgba(0,0,0,.78), rgba(0,0,0,.10) 58%, rgba(0,0,0,0));
    display:flex;
    flex-direction:column;
    justify-content:flex-end;
    padding:14px;
    pointer-events:none;
  }

  .ij-coverTitle{
    font-weight:950;
    font-size:14px;
    color:#fff;
    line-height:1.2;
    margin-bottom:2px;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
  }

  .ij-coverArtist{
    font-size:12px;
    color:#cbd5e1;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
  }

  .ij-rankBadge{
    position:absolute;
    top:10px;
    left:10px;
    background: rgba(37,99,235,.92);
    color:white;
    font-weight:950;
    font-size:12px;
    padding:4px 9px;
    border-radius:999px;
    border:1px solid rgba(96,165,250,.35);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    pointer-events:none;
  }

  .ij-playBadge{
    position:absolute;
    top:10px;
    right:10px;
    width:38px;
    height:38px;
    border-radius:14px;
    border:1px solid rgba(148,163,184,.25);
    background: rgba(11,18,32,.72);
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:950;
    color:#fff;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    pointer-events:none;
  }

  .ij-shareBtn{
    position:absolute;
    top:10px;
    right:54px;
    width:38px;
    height:38px;
    border-radius:14px;
    border:1px solid rgba(148,163,184,.25);
    background: rgba(11,18,32,.72);
    color:#fff;
    font-weight:950;
    display:flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
  }

  .ij-coverCard.isPlaying{
    border-color: rgba(96,165,250,.65);
    box-shadow: 0 16px 44px rgba(37,99,235,.12), 0 18px 40px rgba(0,0,0,.35);
  }

  .ij-miniLink{
    color: var(--accent2);
    font-weight: 900;
    font-size: 12px;
    text-decoration: none;
  }

  .ij-miniLink:hover{
    text-decoration: underline;
  }

  .ij-chip{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:6px;
    font-size: 12px;
    font-weight: 900;
    color: var(--text);
    background: var(--card2);
    border: 1px solid #24324f;
    border-radius: 999px;
    padding: 8px 13px;
    text-decoration:none;
    white-space:nowrap;
    transition: border-color .18s ease, transform .18s ease, background .18s ease;
    text-align:center;
  }

  .ij-chip:hover{
    border-color: rgba(96,165,250,.55);
    transform: translateY(-1px);
    background: rgba(11,18,32,.72);
  }

  @media (max-width: 980px){
    .ij-heroHead{
      grid-template-columns:1fr;
      align-items:start;
    }

    .ij-ctaCol{
      justify-content:flex-start;
      width:100%;
    }

    .ij-ctaRow{
      width:100%;
      max-width:none;
    }

    .ij-heroFeatureGrid{
      grid-template-columns:1fr;
    }

    .ij-valueGrid{
      grid-template-columns:1fr;
    }

    .ij-homeBar{
      grid-template-columns:minmax(0, 1fr) 220px;
    }

    #btnGoLibrary{
      grid-column:1 / -1;
    }
  }

  @media (max-width: 820px){
    .ij-hero{
      padding: 6px 12px 0;
    }

    .ij-home-wrap{
      padding: 0 12px;
    }

    .ij-heroCard{
      padding:22px 18px;
      border-radius:22px;
    }

    .ij-heroHead{
      gap:16px;
      margin-bottom: 6px;
    }

    .ij-heroMain{
      text-align:center;
    }

    .ij-heroKicker{
      margin-left:auto;
      margin-right:auto;
    }

    .ij-heroTitle{
      font-size: 34px;
      line-height: 1.03;
      max-width:100%;
      margin-left:auto;
      margin-right:auto;
    }

    .ij-heroSub{
      font-size: 14px;
      line-height: 1.65;
      max-width: 100%;
      margin-top: 10px;
      margin-left:auto;
      margin-right:auto;
    }

    .ij-heroBadgeGrid{
      gap:8px;
    }

    .ij-heroBadgeCard{
      min-height:96px;
      padding:14px 8px;
      border-radius:18px;
    }

    .ij-heroBadgeIcon{
      width:46px;
      height:46px;
      border-radius:16px;
      font-size:22px;
    }

    .ij-heroBadgeText{
      font-size:12px;
    }

    .ij-ctaCol{
      width:100%;
    }

    .ij-ctaRow{
      width:100%;
      display:grid;
      grid-template-columns:1fr;
      gap:8px;
    }

    .ij-ctaRow a{
      min-height: 48px;
      padding: 0 12px;
      border-radius: 15px;
      width:100%;
    }

    .ij-heroFeatureGrid{
      gap:10px;
      margin-top:16px;
    }

    .ij-heroFeature{
      padding:14px;
      border-radius:18px;
    }

    .ij-homeBarWrap{
      margin-top:16px;
      padding-top:16px;
    }

    .ij-homeBarLabel{
      text-align:center;
      margin-bottom:10px;
    }

    .ij-homeBar{
      grid-template-columns: 1fr 1fr;
      gap:8px;
      margin-top: 0;
    }

    .ij-homeBar .ij-input:first-child{
      grid-column: 1 / -1;
    }

    #btnGoLibrary{
      grid-column: 1 / -1;
      min-height: 46px;
      border-radius: 15px;
      width:100%;
    }

    .ij-valueCard{
      padding:18px;
      border-radius:18px;
    }

    .ij-valueHead{
      text-align:center;
    }

    .ij-valueTitle{
      font-size:20px;
    }

    .ij-valueSub{
      max-width:100%;
      font-size:13px;
      line-height:1.7;
    }

    .ij-valueItem{
      min-height:auto;
      text-align:center;
      padding:18px 16px;
    }

    .ij-valueIcon{
      margin-left:auto;
      margin-right:auto;
      width:78px;
      height:78px;
      font-size:48px;
      border-radius:18px;
    }

    .ij-secHead{
      align-items:flex-start;
      gap:8px;
      margin: 18px 0 10px;
    }

    .ij-secHeadText{
      width:100%;
    }

    .ij-secTitle{
      font-size: 16px;
    }

    .ij-secSub{
      font-size:12px;
    }

    .ij-toggle{
      gap:6px;
    }

    .ij-toggle .ij-btnGhost{
      min-height: 36px;
      padding: 8px 10px;
    }
  }

  @media (max-width: 620px){
    body.has-player{
      padding-bottom: 150px;
    }

    .ij-home-wrap{
      padding: 0 10px;
    }

    .ij-hero{
      padding: 4px 10px 0;
    }

    .ij-heroCard{
      padding:20px 14px 16px;
      border-radius:20px;
    }

    .ij-heroTitle{
      font-size: 30px;
    }

    .ij-heroSub{
      font-size: 13px;
      line-height: 1.6;
    }

    .ij-heroBadgeGrid{
      grid-template-columns:repeat(3, minmax(0, 1fr));
      gap:8px;
    }

    .ij-heroBadgeCard{
      min-height:92px;
      padding:12px 6px;
    }

    .ij-heroBadgeIcon{
      width:42px;
      height:42px;
      border-radius:14px;
      font-size:20px;
    }

    .ij-heroBadgeText{
      font-size:11px;
      line-height:1.25;
    }

    .ij-heroFeatureGrid{
      grid-template-columns:1fr;
    }

    .ij-homeBar{
      grid-template-columns:1fr;
    }

    .ij-grid{
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 10px;
    }

    .ij-coverCard{
      border-radius: 16px;
    }

    .ij-coverOverlay{
      padding: 10px;
    }

    .ij-coverTitle{
      font-size: 13px;
      line-height: 1.2;
    }

    .ij-coverArtist{
      font-size: 11px;
    }

    .ij-rankBadge{
      top:8px;
      left:8px;
      font-size:11px;
      padding:4px 8px;
    }

    .ij-playBadge,
    .ij-shareBtn{
      width:34px;
      height:34px;
      border-radius:12px;
      top:8px;
    }

    .ij-playBadge{ right:8px; }
    .ij-shareBtn{ right:46px; }

    .ij-chip{
      font-size: 11px;
      padding: 6px 10px;
    }
  }

  @media (max-width: 390px){
    .ij-heroTitle{
      font-size: 27px;
    }

    .ij-grid{
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }
</style>

<div class="ij-hero">
  <div class="ij-home-wrap">
    <div class="ij-heroCard">
      <div class="ij-heroHead">
        <div class="ij-heroMain">
          <div class="ij-heroKicker"><?= htmlspecialchars($L['hero_kicker'], ENT_QUOTES, 'UTF-8') ?></div>
          <h1 class="ij-heroTitle"><?= htmlspecialchars($L['h1'], ENT_QUOTES, 'UTF-8') ?></h1>
          <div class="ij-heroSub"><?= htmlspecialchars($L['sub'], ENT_QUOTES, 'UTF-8') ?></div>

          <div class="ij-heroBadgeGrid">
            <div class="ij-heroBadgeCard">
              <div class="ij-heroBadgeIcon">▶</div>
              <div class="ij-heroBadgeText"><?= htmlspecialchars($L['hero_badge_1'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="ij-heroBadgeCard">
              <div class="ij-heroBadgeIcon">👤</div>
              <div class="ij-heroBadgeText"><?= htmlspecialchars($L['hero_badge_2'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="ij-heroBadgeCard">
              <div class="ij-heroBadgeIcon">🔗</div>
              <div class="ij-heroBadgeText"><?= htmlspecialchars($L['hero_badge_3'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
          </div>
        </div>

        <div class="ij-ctaCol">
          <div class="ij-ctaRow">
            <a class="ij-btnGhost" href="<?= htmlspecialchars(ij_url('/library/'), ENT_QUOTES, 'UTF-8') ?>">
              <?= htmlspecialchars($L['cta_library'], ENT_QUOTES, 'UTF-8') ?>
            </a>
            <a class="ij-btnGhost" href="<?= htmlspecialchars(ij_url('/top/'), ENT_QUOTES, 'UTF-8') ?>">
              <?= htmlspecialchars($L['cta_top'], ENT_QUOTES, 'UTF-8') ?>
            </a>
            <a class="ij-btn" href="<?= htmlspecialchars(ij_url('/upload/'), ENT_QUOTES, 'UTF-8') ?>" data-no-ajax="1">
              <?= htmlspecialchars($L['cta_upload'], ENT_QUOTES, 'UTF-8') ?>
            </a>
          </div>
        </div>
      </div>


      <div class="ij-homeBarWrap">
        <div class="ij-homeBarLabel"><?= htmlspecialchars($L['quick_search'], ENT_QUOTES, 'UTF-8') ?></div>
        <div class="ij-homeBar">
          <input class="ij-input" id="q" placeholder="<?= htmlspecialchars($L['searchPh'], ENT_QUOTES, 'UTF-8') ?>" />
          <select class="ij-input ij-genre" id="genre">
            <option value=""><?= htmlspecialchars($L['genreAll'], ENT_QUOTES, 'UTF-8') ?></option>
          </select>
          <button class="ij-btnGhost" id="btnGoLibrary" type="button"><?= htmlspecialchars($L['cta_library'], ENT_QUOTES, 'UTF-8') ?></button>
        </div>
      </div>
    </div>
  </div>
</div>

<section class="ij-valueSection">
  <div class="ij-home-wrap">
    <div class="ij-valueCard">
      <div class="ij-valueHead">
        <h2 class="ij-valueTitle"><?= htmlspecialchars($L['value_title'], ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="ij-valueSub"><?= htmlspecialchars($L['value_sub'], ENT_QUOTES, 'UTF-8') ?></div>
      </div>

      <div class="ij-valueGrid">
        <article class="ij-valueItem">
          <div class="ij-valueIcon">🎤</div>
          <h3 class="ij-valueItemTitle"><?= htmlspecialchars($L['value_card_1_title'], ENT_QUOTES, 'UTF-8') ?></h3>
          <div class="ij-valueItemText"><?= htmlspecialchars($L['value_card_1_text'], ENT_QUOTES, 'UTF-8') ?></div>
        </article>

        <article class="ij-valueItem">
          <div class="ij-valueIcon">🏟️</div>
          <h3 class="ij-valueItemTitle"><?= htmlspecialchars($L['value_card_2_title'], ENT_QUOTES, 'UTF-8') ?></h3>
          <div class="ij-valueItemText"><?= htmlspecialchars($L['value_card_2_text'], ENT_QUOTES, 'UTF-8') ?></div>
        </article>

        <article class="ij-valueItem">
          <div class="ij-valueIcon">✨</div>
          <h3 class="ij-valueItemTitle"><?= htmlspecialchars($L['value_card_3_title'], ENT_QUOTES, 'UTF-8') ?></h3>
          <div class="ij-valueItemText"><?= htmlspecialchars($L['value_card_3_text'], ENT_QUOTES, 'UTF-8') ?></div>
        </article>
      </div>
    </div>
  </div>
</section>

<main class="ij-sections">
  <div class="ij-home-wrap">

    <div class="ij-secHead">
      <div class="ij-secHeadText">
        <h2 class="ij-secTitle"><?= htmlspecialchars($L['popular'], ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="ij-secSub"><?= htmlspecialchars($L['popular_sub'], ENT_QUOTES, 'UTF-8') ?></div>
      </div>
      <div class="ij-toggle">
        <button class="ij-btnGhost" id="popWeek" type="button"><?= htmlspecialchars($L['week'], ENT_QUOTES, 'UTF-8') ?></button>
        <button class="ij-btnGhost" id="popMonth" type="button"><?= htmlspecialchars($L['month'], ENT_QUOTES, 'UTF-8') ?></button>
      </div>
    </div>
    <div id="popularGrid" class="ij-grid"></div>
    <div style="margin-top:8px;">
      <a class="ij-miniLink" id="popularMore" href="<?= htmlspecialchars(ij_url('/top/'), ENT_QUOTES, 'UTF-8') ?>">
        <?= htmlspecialchars($L['cta_top'], ENT_QUOTES, 'UTF-8') ?> →
      </a>
    </div>

    <div class="ij-secHead">
      <div class="ij-secHeadText">
        <h2 class="ij-secTitle"><?= htmlspecialchars($L['newest'], ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="ij-secSub"><?= htmlspecialchars($L['newest_sub'], ENT_QUOTES, 'UTF-8') ?></div>
      </div>
      <a class="ij-miniLink" id="newestMore" href="<?= htmlspecialchars(ij_url('/library/'), ENT_QUOTES, 'UTF-8') ?>">
        <?= htmlspecialchars($L['open_library_filtered'], ENT_QUOTES, 'UTF-8') ?>
      </a>
    </div>
    <div id="newestGrid" class="ij-grid"></div>

    <div class="ij-secHead">
      <div class="ij-secHeadText">
        <h2 class="ij-secTitle"><?= htmlspecialchars($L['categories'], ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="ij-secSub"><?= htmlspecialchars($L['categories_sub'], ENT_QUOTES, 'UTF-8') ?></div>
      </div>
    </div>
    <div id="chips" style="display:flex;gap:8px;flex-wrap:wrap;"></div>

  </div>
</main>

<script>
(function(){
  const LANG = <?= json_encode($lang, JSON_UNESCAPED_UNICODE) ?>;
  const BASE_LIBRARY = <?= json_encode(ij_url('/library/'), JSON_UNESCAPED_UNICODE) ?>;
  const BASE_TOP = <?= json_encode(ij_url('/top/'), JSON_UNESCAPED_UNICODE) ?>;
  const SHARE_LABEL = <?= json_encode($L['share'], JSON_UNESCAPED_UNICODE) ?>;
  const LOAD_ERR = <?= json_encode($L['loadErr'], JSON_UNESCAPED_UNICODE) ?>;

  const q = document.getElementById('q');
  const genreSel = document.getElementById('genre');
  const btnGoLibrary = document.getElementById('btnGoLibrary');
  const popularGrid = document.getElementById('popularGrid');
  const newestGrid = document.getElementById('newestGrid');
  const chips = document.getElementById('chips');
  const popWeekBtn = document.getElementById('popWeek');
  const popMonthBtn = document.getElementById('popMonth');

  const GENRE_CANON = {
    "Arena / Jingle": { sv:"Arena / Jingle", en:"Arena / Jingle" },
    "Organ / Charge": { sv:"Organ / Charge", en:"Organ / Charge" },
    "Rock": { sv:"Rock", en:"Rock" },
    "EDM": { sv:"EDM", en:"EDM" },
    "Hip-hop": { sv:"Hip-hop", en:"Hip-hop" },
    "Pop": { sv:"Pop", en:"Pop" },
    "Mål": { sv:"Mål", en:"Goal" },
    "Utvisning": { sv:"Utvisning", en:"Penalty" },
    "Periodpaus": { sv:"Periodpaus", en:"Intermission" },
    "Warmup": { sv:"Warmup", en:"Warm-up" },
    "Timeout": { sv:"Timeout", en:"Timeout" },
    "Övrigt": { sv:"Övrigt", en:"Other" },
  };

  const GENRE_ALIASES = {
    "Arena / Jingle": ["arena", "jingle", "jingl", "arena / jingle"],
    "Organ / Charge": ["organ", "orgel", "charge", "organ / charge", "hammond"],
    "Rock": ["rock"],
    "EDM": ["edm", "electronic", "dance", "electro"],
    "Hip-hop": ["hip-hop", "hiphop", "rap"],
    "Pop": ["pop"],
    "Mål": ["mål", "mal", "goal", "goal horn", "goalhorn"],
    "Utvisning": ["utvisning", "penalty", "penalties"],
    "Periodpaus": ["periodpaus", "intermission", "period break", "break"],
    "Warmup": ["warmup", "warm-up", "warm up", "uppvärmning", "uppvarmning"],
    "Timeout": ["timeout", "time out", "time-out"],
    "Övrigt": ["övrigt", "ovrigt", "other", "misc", "miscellaneous"],
  };

  let items = [];
  let playsMap = Object.create(null);
  let popularRange = localStorage.getItem('ij_home_pop_range') || 'week';
  if (popularRange !== 'month') popularRange = 'week';

  function esc(s){
    return String(s ?? '').replace(/[&<>"']/g, (c)=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));
  }

  function uniq(arr){
    return Array.from(new Set((arr||[]).filter(Boolean)));
  }

  function resolveUrl(baseUrl, u){
    if(!u) return '';
    if(/^https?:\/\//i.test(u)) return u;
    return (baseUrl || '').replace(/\/+$/,'') + '/' + String(u).replace(/^\/+/, '');
  }

  function canonicalGenre(input){
    const g = String(input || '').trim();
    if (!g) return "Övrigt";
    if (GENRE_CANON[g]) return g;
    const norm = g.toLowerCase().replace(/\s+/g,' ').trim();
    for (const [canon, aliases] of Object.entries(GENRE_ALIASES)){
      if (canon.toLowerCase() === norm) return canon;
      if ((aliases || []).some(a => a === norm)) return canon;
    }
    return g || "Övrigt";
  }

  function displayGenre(canon){
    const c = canonicalGenre(canon);
    const l = (LANG === "en") ? "en" : "sv";
    return (GENRE_CANON[c]?.[l] || c);
  }

  function buildLibraryUrl({ q = '', genre = '', sort = '', range = '' } = {}){
    const u = new URL(BASE_LIBRARY, location.origin);
    if (LANG === 'sv' || LANG === 'en') u.searchParams.set('lang', LANG);

    const qq = String(q || '').trim();
    const gg = String(genre || '').trim();
    const ss = String(sort || '').trim();
    const rr = String(range || '').trim();

    if (qq) u.searchParams.set('q', qq);
    if (gg) u.searchParams.set('genre', gg);
    if (ss) u.searchParams.set('sort', ss);
    if (rr) u.searchParams.set('range', rr);

    return u.toString();
  }

  function shareUrlFor(id){
    const u = new URL('/track/', location.origin);
    u.searchParams.set('id', String(id||''));
    if (LANG === 'en' || LANG === 'sv') u.searchParams.set('lang', LANG);
    return u.toString();
  }

  async function shareOrCopy({ url, title = '', text = '' }){
    url = String(url || '');
    title = String(title || '');
    text = String(text || '');

    try{
      if (navigator.share){
        await navigator.share({ url, title, text });
        return { ok:true };
      }
    }catch{}

    try{
      await navigator.clipboard.writeText(url);
      return { ok:true };
    }catch{
      return { ok:false };
    }
  }

  function setPopButtons(){
    popWeekBtn.classList.toggle('active', popularRange === 'week');
    popMonthBtn.classList.toggle('active', popularRange === 'month');
  }

  async function loadPlays(range){
    const r = (range === 'month') ? 'month' : 'week';
    try{
      const res = await fetch('/api/stats_plays.php?range=' + encodeURIComponent(r), { cache:'no-store' });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const json = await res.json();
      const map = (json && json.playsByTrackId && typeof json.playsByTrackId === 'object') ? json.playsByTrackId : {};
      playsMap = Object.create(null);
      for (const [k,v] of Object.entries(map)){
        playsMap[String(k)] = Number(v) || 0;
      }
    }catch{
      playsMap = Object.create(null);
    }
  }

  function getPlaysFor(track){
    const id = String(track?.id ?? '');
    const v = playsMap[id];
    return (typeof v === 'number') ? v : 0;
  }

  function buildGenreOptions(){
    const genres = uniq(items.map(x => canonicalGenre(x.genre || 'Övrigt')))
      .sort((a,b)=>a.localeCompare(b,'sv'));

    genreSel.innerHTML =
      `<option value="">${esc(<?= json_encode($L['genreAll'], JSON_UNESCAPED_UNICODE) ?>)}</option>` +
      genres.map(g=>`<option value="${esc(g)}">${esc(displayGenre(g))}</option>`).join('');
  }

  function buildChips(){
    const counts = new Map();
    for (const it of items){
      const g = canonicalGenre(it.genre || 'Övrigt');
      counts.set(g, (counts.get(g) || 0) + 1);
    }

    const top = Array.from(counts.entries())
      .sort((a,b)=> b[1]-a[1])
      .slice(0, 10)
      .map(([g])=>g);

    chips.innerHTML = '';

    top.forEach(g=>{
      const a = document.createElement('a');
      a.className = 'ij-chip';
      a.href = buildLibraryUrl({ genre: g });
      a.textContent = displayGenre(g);
      chips.appendChild(a);
    });

    const all = document.createElement('a');
    all.className = 'ij-chip';
    all.href = buildLibraryUrl();
    all.textContent = (LANG === 'en') ? 'All tracks' : 'Alla låtar';
    chips.appendChild(all);
  }

  function getNewestList(limit = 6){
    return items.slice()
      .sort((a,b)=> Number(b.createdAt || 0) - Number(a.createdAt || 0))
      .slice(0, limit);
  }

  function getPopularFallbackList(limit = 6){
    const newest = getNewestList(limit);
    const newestIds = new Set(newest.map(t => String(t.id)));

    let fallback = items
      .filter(t => !newestIds.has(String(t.id)))
      .slice()
      .sort((a,b)=> Number(b.createdAt || 0) - Number(a.createdAt || 0))
      .slice(0, limit);

    if (fallback.length < limit){
      const used = new Set(fallback.map(t => String(t.id)));
      newest.forEach(t => {
        const id = String(t.id);
        if (fallback.length < limit && !used.has(id)) {
          fallback.push(t);
          used.add(id);
        }
      });
    }

    return fallback;
  }

  function isTrackPlaying(track){
    const player = window.__ijPlayer;
    if (!player) return false;
    const st = player.getState();
    return !!(st.current && st.current.id === track.id && !st.paused);
  }

  function makeCoverCard(track, rank = null){
    const wrap = document.createElement('div');
    wrap.className = 'ij-coverCard';

    const img = document.createElement('img');
    img.alt = '';
    img.loading = 'lazy';
    img.src = track.image || '';

    const overlay = document.createElement('div');
    overlay.className = 'ij-coverOverlay';

    const t = document.createElement('div');
    t.className = 'ij-coverTitle';
    t.textContent = track.title || '—';

    const a = document.createElement('div');
    a.className = 'ij-coverArtist';
    a.textContent = track.artist || '';

    overlay.appendChild(t);
    overlay.appendChild(a);

    if (rank !== null){
      const badge = document.createElement('div');
      badge.className = 'ij-rankBadge';
      badge.textContent = '#' + rank;
      wrap.appendChild(badge);
    }

    const shareBtn = document.createElement('button');
    shareBtn.type = 'button';
    shareBtn.className = 'ij-shareBtn';
    shareBtn.title = SHARE_LABEL;
    shareBtn.textContent = '🔗';
    shareBtn.onclick = async (ev)=>{
      ev.preventDefault();
      ev.stopPropagation();

      const res = await shareOrCopy({
        url: shareUrlFor(track.id),
        title: track.title || 'Track',
        text: track.artist ? `${track.artist} – ${track.title || ''}` : (track.title || '')
      });

      if (res?.ok) shareBtn.textContent = '✅';
      setTimeout(()=>{ shareBtn.textContent = '🔗'; }, 900);
    };
    wrap.appendChild(shareBtn);

    const playBadge = document.createElement('div');
    playBadge.className = 'ij-playBadge';
    playBadge.textContent = isTrackPlaying(track) ? '⏸' : '▶';
    wrap.appendChild(playBadge);

    wrap.appendChild(img);
    wrap.appendChild(overlay);

    if (isTrackPlaying(track)) wrap.classList.add('isPlaying');

    wrap.onclick = async ()=>{
      const player = window.__ijPlayer;
      if (!player || !track._resolvedUrl) return;

      const st = player.getState();
      const samePlaying = !!(st.current && st.current.id === track.id && !st.paused);

      player.setQueue(items);

      if (samePlaying){
        player.stopToStart();
        return;
      }

      await player.playTrack(track, { queue: items });
    };

    return wrap;
  }

  function renderPopular(){
    const played = items
      .map(t => ({ ...t, _plays: getPlaysFor(t) }))
      .filter(t => t._plays > 0)
      .sort((a, b) => {
        if (b._plays !== a._plays) return b._plays - a._plays;
        return Number(b.createdAt || 0) - Number(a.createdAt || 0);
      });

    const list = played.length ? played.slice(0, 6) : getPopularFallbackList(6);

    popularGrid.innerHTML = '';
    list.forEach((t, i) => popularGrid.appendChild(makeCoverCard(t, i + 1)));

    const more = document.getElementById('popularMore');
    if (more){
      const u = new URL(BASE_TOP, location.origin);
      if (LANG === 'sv' || LANG === 'en') u.searchParams.set('lang', LANG);
      u.searchParams.set('range', popularRange);
      more.href = u.toString();
    }
  }

  function renderNewest(){
    const list = getNewestList(6);
    newestGrid.innerHTML = '';
    list.forEach((t) => newestGrid.appendChild(makeCoverCard(t)));
  }

  function renderAll(){
    renderPopular();
    renderNewest();
  }

  function applyQuickFilters(){
    const term = (q.value || '').trim();
    const g = (genreSel.value || '').trim();
    const url = buildLibraryUrl({ q: term, genre: g });

    if (window.__ijShellNavigate) {
      window.__ijShellNavigate(url);
    } else {
      location.href = url;
    }
  }

  const onPlayerState = ()=> renderAll();

  async function init(){
    setPopButtons();

    const res = await fetch('/library.json', { cache:'no-store' });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    const json = await res.json();

    const baseUrl = json.baseUrl || location.origin;
    const list = Array.isArray(json.items) ? json.items : [];

    items = list.map(it=>{
      const u = resolveUrl(baseUrl, it.url);
      return {
        id: it.id || u,
        title: it.title || it.name || '',
        artist: it.artist || '',
        genre: canonicalGenre(it.genre || 'Övrigt'),
        image: resolveUrl(baseUrl, it.image || ''),
        startMs: (it.startMs ?? 0) | 0,
        endMs: (it.endMs ?? 0) | 0,
        createdAt: Number(it.createdAt || it.created || 0) || 0,
        _resolvedUrl: u,
      };
    });

    buildGenreOptions();
    buildChips();

    if (window.__ijPlayer) {
      window.__ijPlayer.setQueue(items);
    }

    await loadPlays(popularRange);
    renderAll();

    btnGoLibrary.addEventListener('click', applyQuickFilters);
    q.addEventListener('keydown', onQKeydown);
    popWeekBtn.addEventListener('click', onWeek);
    popMonthBtn.addEventListener('click', onMonth);
    window.addEventListener('ij:player-state', onPlayerState);
  }

  function onQKeydown(e){
    if (e.key === 'Enter') applyQuickFilters();
  }

  async function onWeek(){
    popularRange = 'week';
    localStorage.setItem('ij_home_pop_range', popularRange);
    setPopButtons();
    await loadPlays(popularRange);
    renderPopular();
  }

  async function onMonth(){
    popularRange = 'month';
    localStorage.setItem('ij_home_pop_range', popularRange);
    setPopButtons();
    await loadPlays(popularRange);
    renderPopular();
  }

  function cleanup(){
    btnGoLibrary.removeEventListener('click', applyQuickFilters);
    q.removeEventListener('keydown', onQKeydown);
    popWeekBtn.removeEventListener('click', onWeek);
    popMonthBtn.removeEventListener('click', onMonth);
    window.removeEventListener('ij:player-state', onPlayerState);
  }

  if (window.__ijRegisterPageCleanup) {
    window.__ijRegisterPageCleanup(cleanup);
  }

  init().catch((e)=>{
    console.error(e);
    popularGrid.innerHTML = `<div class="ij-muted">${esc(LOAD_ERR)}</div>`;
    newestGrid.innerHTML = '';
  });
})();
</script>

<?php require __DIR__ . '/_partials/footer.php'; ?>