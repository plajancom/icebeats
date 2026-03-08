<?php
// /track/index.php
declare(strict_types=1);

require __DIR__ . '/../_partials/i18n.php';
require __DIR__ . '/../_partials/meta.php';

$lang = ij_lang();

$id = trim((string)($_GET['id'] ?? ''));
$id = preg_replace('~[^a-zA-Z0-9\-_]~', '', $id);

$libraryPath = realpath(__DIR__ . '/../library.json') ?: (__DIR__ . '/../library.json');
$lib = is_file($libraryPath) ? json_decode((string)file_get_contents($libraryPath), true) : null;
if (!is_array($lib)) $lib = ['baseUrl' => ij_base_url(), 'items' => []];
$baseUrl  = (string)($lib['baseUrl'] ?? ij_base_url());
$itemsRaw = is_array($lib['items'] ?? null) ? $lib['items'] : [];

$track = null;
if ($id !== '') {
  foreach ($itemsRaw as $it) {
    if (is_array($it) && (string)($it['id'] ?? '') === $id) { $track = $it; break; }
  }
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function resolve_url(string $base, string $u): string {
  if ($u === '') return '';
  if (preg_match('~^https?://~i', $u)) return $u;
  return rtrim($base, '/').'/'.ltrim($u, '/');
}

function norm_artist_soft(string $s): string {
  $s = trim($s);
  if ($s === '') return '';

  $s = mb_strtolower($s, 'UTF-8');
  $s = str_replace(['&', '＋', '+'], ' and ', $s);
  $s = preg_replace('~\s*\b(feat|ft|featuring)\b\.?\s+.*$~iu', '', $s) ?? $s;
  $s = preg_replace('~\([^)]*\)~u', ' ', $s) ?? $s;
  $s = preg_replace('~\[[^\]]*\]~u', ' ', $s) ?? $s;
  $s = preg_replace('~[^a-z0-9åäö\s]+~iu', ' ', $s) ?? $s;
  $s = preg_replace('/\s+/u', ' ', $s) ?? $s;

  return trim($s);
}

function norm_genre_soft(string $s): string {
  $s = trim($s);
  if ($s === '') return '';
  $s = mb_strtolower($s, 'UTF-8');
  $s = preg_replace('/\s+/u', ' ', $s) ?? $s;
  return trim($s);
}

if (!$track) {
  http_response_code(404);

  $title = ($lang === 'en') ? 'Track not found – iceBeats.io' : 'Låten hittades inte – iceBeats.io';
  $desc  = ($lang === 'en') ? 'This track does not exist (or has been removed).' : 'Den här låten finns inte (eller har tagits bort).';
  $canonical = ij_abs('/track/?id=' . rawurlencode($id) . '&lang=' . rawurlencode($lang));

  $meta = ij_build_meta([
    'title' => $title,
    'description' => $desc,
    'canonical' => $canonical,
    'image' => ij_abs('/share/og.jpg'),
    'type' => 'music.song',
  ]);

  $pageTitle = $meta['title'];
  $pageHead = ij_render_meta($meta);

  require __DIR__ . '/../_partials/header.php';
  ?>
  <div style="max-width:980px;margin:0 auto;padding:0 16px;">
    <div class="ij-card" style="margin-top:14px;">
      <h1 class="ij-h1"><?= h($lang === 'en' ? 'Track not found' : 'Låten hittades inte') ?></h1>
      <div class="ij-muted" style="margin-top:8px;"><?= h($desc) ?></div>
      <div style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap;">
        <a class="ij-btnGhost" href="<?= h(ij_url('/library/?lang='.$lang)) ?>"><?= h($lang === 'en' ? 'Open Tracks' : 'Öppna Låtar') ?></a>
        <a class="ij-btnGhost" href="<?= h(ij_url('/?lang='.$lang)) ?>"><?= h($lang === 'en' ? 'Home' : 'Startsida') ?></a>
      </div>
    </div>
  </div>
  <?php
  require __DIR__ . '/../_partials/footer.php';
  exit;
}

$trackTitle  = (string)($track['title'] ?? '—');
$trackArtist = (string)($track['artist'] ?? '');
$trackGenre  = (string)($track['genre'] ?? '');
$trackImg    = (string)($track['image'] ?? '');
$trackImgAbs = $trackImg !== '' ? resolve_url($baseUrl, $trackImg) : ij_abs('/share/og.jpg');
$trackUrlRel = (string)($track['url'] ?? '');
$playUrl     = resolve_url($baseUrl, $trackUrlRel);

$title = $trackTitle . ' – ' . $trackArtist . ' | iceBeats.io';
$desc = ($lang === 'en')
  ? trim($trackArtist . ($trackGenre ? " • $trackGenre" : '') . ' — Play this track directly on iceBeats.io.')
  : trim($trackArtist . ($trackGenre ? " • $trackGenre" : '') . ' — Spela låten direkt på iceBeats.io.');

$canonical = ij_abs('/track/?id=' . rawurlencode($id) . '&lang=' . rawurlencode($lang));

$meta = ij_build_meta([
  'title' => $title,
  'description' => $desc,
  'canonical' => $canonical,
  'image' => $trackImgAbs,
  'type' => 'music.song',
  'extra' => [
    ['property' => 'og:title', 'content' => trim($trackTitle . ' – ' . $trackArtist)],
    ['name' => 'twitter:title', 'content' => trim($trackTitle . ' – ' . $trackArtist)],
  ],
]);

$pageTitle = $meta['title'];
$pageHead = ij_render_meta($meta);

require __DIR__ . '/../_partials/header.php';

$S = [
  'sv' => [
    'play' => '▶ Spela',
    'stop' => '⏹ Stoppa',
    'copy' => '⧉',
    'share' => '🔗',
    'shared' => 'Delat ✅',
    'copied' => 'Kopierad ✅',
    'cantCopy' => 'Kunde inte kopiera (browser blockerade).',
    'openLibrary' => 'Öppna i Låtar',
    'home' => 'Startsida',
    'moreTitle' => 'Fler låtar',
    'groupArtist' => 'Samma artist',
    'groupGenre' => 'Samma genre',
    'groupNewest' => 'Nyast',
    'playsWeek' => 'Spelningar (vecka)',
    'playsMonth' => 'Spelningar (månad)',
    'rangeWeek' => 'Vecka',
    'rangeMonth' => 'Månad',
    'noMore' => 'Inga fler låtar att visa.',
    'loading' => 'Laddar…',
    'actions' => 'Åtgärder',
    'track' => 'Låt',
  ],
  'en' => [
    'play' => '▶ Play',
    'stop' => '⏹ Stop',
    'copy' => '⧉',
    'share' => '🔗',
    'shared' => 'Shared ✅',
    'copied' => 'Copied ✅',
    'cantCopy' => 'Could not copy (browser blocked).',
    'openLibrary' => 'Open in Tracks',
    'home' => 'Home',
    'moreTitle' => 'More tracks',
    'groupArtist' => 'Same artist',
    'groupGenre' => 'Same genre',
    'groupNewest' => 'Newest',
    'playsWeek' => 'Plays (week)',
    'playsMonth' => 'Plays (month)',
    'rangeWeek' => 'Week',
    'rangeMonth' => 'Month',
    'noMore' => 'No more tracks to show.',
    'loading' => 'Loading…',
    'actions' => 'Actions',
    'track' => 'Track',
  ],
];
$L = $S[$lang] ?? $S['sv'];

$N = 12;

$curArtistSoft = norm_artist_soft((string)($track['artist'] ?? ''));
$curGenreSoft  = norm_genre_soft((string)($track['genre'] ?? ''));

$others = [];
foreach ($itemsRaw as $it) {
  if (!is_array($it)) continue;
  $tid = (string)($it['id'] ?? '');
  if ($tid === '' || $tid === $id) continue;
  $others[] = $it;
}

$artistGroup = [];
if ($curArtistSoft !== '') {
  foreach ($others as $it) {
    $a = norm_artist_soft((string)($it['artist'] ?? ''));
    if ($a !== '' && $a === $curArtistSoft) $artistGroup[] = $it;
  }
}

$genreGroup = [];
if ($curGenreSoft !== '') {
  foreach ($others as $it) {
    $g = norm_genre_soft((string)($it['genre'] ?? ''));
    if ($g !== '' && $g === $curGenreSoft) $genreGroup[] = $it;
  }
}

$newestGroup = $others;
usort($newestGroup, function($a, $b){
  $ac = (int)($a['createdAt'] ?? $a['created'] ?? 0);
  $bc = (int)($b['createdAt'] ?? $b['created'] ?? 0);
  return $bc <=> $ac;
});

$seen = [];

$finalArtist = [];
foreach ($artistGroup as $it) {
  $tid = (string)($it['id'] ?? '');
  if ($tid === '' || isset($seen[$tid])) continue;
  $seen[$tid] = true;
  $finalArtist[] = $it;
  if (count($finalArtist) >= $N) break;
}

$finalGenre = [];
foreach ($genreGroup as $it) {
  $tid = (string)($it['id'] ?? '');
  if ($tid === '' || isset($seen[$tid])) continue;
  $seen[$tid] = true;
  $finalGenre[] = $it;
  if (count($finalArtist) + count($finalGenre) >= $N) break;
}

$finalNewest = [];
foreach ($newestGroup as $it) {
  $tid = (string)($it['id'] ?? '');
  if ($tid === '' || isset($seen[$tid])) continue;
  $seen[$tid] = true;
  $finalNewest[] = $it;
  if (count($finalArtist) + count($finalGenre) + count($finalNewest) >= $N) break;
}

function to_payload(array $it, string $baseUrl): array {
  $uRel = (string)($it['url'] ?? '');
  $imgRel = (string)($it['image'] ?? '');
  return [
    'id' => (string)($it['id'] ?? ''),
    'title' => (string)($it['title'] ?? $it['name'] ?? ''),
    'artist' => (string)($it['artist'] ?? ''),
    'genre' => (string)($it['genre'] ?? ''),
    'image' => $imgRel !== '' ? resolve_url($baseUrl, $imgRel) : '',
    'createdAt' => (int)($it['createdAt'] ?? $it['created'] ?? 0),
    'startMs' => (int)($it['startMs'] ?? 0),
    'endMs' => (int)($it['endMs'] ?? 0),
    '_resolvedUrl' => resolve_url($baseUrl, $uRel),
  ];
}

$groupsPayload = [
  'artist' => array_map(fn($it)=>to_payload($it, $baseUrl), $finalArtist),
  'genre'  => array_map(fn($it)=>to_payload($it, $baseUrl), $finalGenre),
  'newest' => array_map(fn($it)=>to_payload($it, $baseUrl), $finalNewest),
];

$currentPayload = [
  'id' => $id,
  'title' => $trackTitle,
  'artist' => $trackArtist,
  'genre' => $trackGenre,
  'image' => $trackImgAbs,
  'startMs' => (int)($track['startMs'] ?? 0),
  'endMs' => (int)($track['endMs'] ?? 0),
  '_resolvedUrl' => $playUrl,
];
?>
<style>
  :root{ color-scheme: dark; }

  .ij-track-wrap{max-width:980px;margin:0 auto;padding:0 16px}
  .ij-track-card{
    margin-top:14px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow:hidden;
    transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
  }
  .ij-track-card.isPlaying{
    border-color: rgba(96,165,250,.55);
    box-shadow: 0 16px 44px rgba(37,99,235,.10), 0 18px 40px rgba(0,0,0,.25);
  }

  .ij-track-top{
    display:grid;
    grid-template-columns: 140px 1fr;
    gap:14px;
    padding:16px;
    align-items:center;
  }
  .ij-track-cover{
    width:140px;height:140px;border-radius:16px;
    object-fit:cover;border:1px solid var(--border2);
    background: var(--card2);
    display:block;
  }

  .ij-track-meta{min-width:0}
  .ij-track-titleRow{display:flex; align-items:center; gap:10px; flex-wrap:wrap}
  .ij-track-title{font-weight:950;font-size:20px;line-height:1.15}
  .ij-track-artist{margin-top:6px;color:var(--muted);font-weight:400}
  .ij-track-genre{margin-top:10px;display:inline-flex;gap:8px;align-items:center;flex-wrap:wrap}
  .ij-tag{display:inline-flex;font-size:12px;font-weight:500;color:var(--text);background:var(--card2);border:1px solid #24324f;border-radius:999px;padding:3px 10px}

  .ij-track-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}
  .ij-track-actions button{cursor:pointer}
  .ij-track-status{padding:0 16px 16px;color:var(--muted);font-size:12px;font-weight:900}

  .ij-more-card{
    margin-top:12px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow:hidden;
  }
  .ij-more-head{
    display:flex; align-items:center; justify-content:space-between; gap:10px;
    padding:14px 16px;
    border-bottom: 1px solid var(--border);
  }
  .ij-more-title{ margin:0; font-weight:950; font-size:14px; color:#cbd5e1; letter-spacing:.2px; }
  .ij-more-sub{ margin-top:4px; color:var(--muted); font-size:12px; font-weight:400; }
  .ij-more-toggle{ display:flex; gap:8px; align-items:center; flex-wrap:wrap; justify-content:flex-end; }
  .ij-more-toggle .ij-btnGhost{ padding:8px 10px; border-radius:12px; font-size:12px; }
  .ij-more-toggle .ij-btnGhost.active{ border-color: rgba(96,165,250,.55); }

  .ij-trackLink{ color:inherit; text-decoration:none; font-weight:950; }
  .ij-trackLink:hover{ text-decoration: underline; }

  .eqWrap{
    display:inline-flex;
    align-items:flex-end;
    gap:3px;
    height:14px;
    padding:3px 6px;
    border-radius:999px;
    border:1px solid rgba(148,163,184,.18);
    background: rgba(11,18,32,.45);
    opacity:.22;
    transform: translateY(-1px);
    transition: opacity .18s ease, transform .18s ease, border-color .18s ease, background .18s ease;
    flex: 0 0 auto;
  }
  .eqBar{
    width:3px;
    height:4px;
    border-radius:2px;
    background: rgba(226,232,240,.85);
    transform-origin: bottom;
    opacity:.9;
  }

  .ij-track-card:hover .eqWrap,
  tr:hover .eqWrap,
  .ij-mTrack:hover .eqWrap{
    opacity:.65;
    transform: translateY(0);
    border-color: rgba(96,165,250,.28);
    background: rgba(11,18,32,.55);
  }

  .ij-track-card.isPlaying .eqWrap,
  tr.isPlaying .eqWrap,
  .ij-mTrack.isPlaying .eqWrap,
  .eqWrap.playing{
    opacity:1;
    transform: translateY(0);
    border-color: rgba(96,165,250,.45);
    background: rgba(11,18,32,.62);
  }

  .ij-track-card.isPlaying .eqBar,
  tr.isPlaying .eqBar,
  .ij-mTrack.isPlaying .eqBar,
  .eqWrap.playing .eqBar{
    animation: ijEq 0.9s ease-in-out infinite;
  }
  .eqWrap.playing .eqBar:nth-child(2),
  .ij-track-card.isPlaying .eqBar:nth-child(2),
  tr.isPlaying .eqBar:nth-child(2),
  .ij-mTrack.isPlaying .eqBar:nth-child(2){ animation-duration: 0.75s; }
  .eqWrap.playing .eqBar:nth-child(3),
  .ij-track-card.isPlaying .eqBar:nth-child(3),
  tr.isPlaying .eqBar:nth-child(3),
  .ij-mTrack.isPlaying .eqBar:nth-child(3){ animation-duration: 0.6s; }
  .eqWrap.playing .eqBar:nth-child(4),
  .ij-track-card.isPlaying .eqBar:nth-child(4),
  tr.isPlaying .eqBar:nth-child(4),
  .ij-mTrack.isPlaying .eqBar:nth-child(4){ animation-duration: 1.05s; }

  @keyframes ijEq{
    0%{ transform: scaleY(.35); }
    30%{ transform: scaleY(1.0); }
    60%{ transform: scaleY(.55); }
    100%{ transform: scaleY(.35); }
  }

  .ij-library-mobile{ padding: 0 16px 16px; display:none; }
  .ij-library-tableWrap{ padding: 0 16px 16px; }
  .ij-library-table{ width:100%; border-collapse: collapse; table-layout: fixed; }
  .ij-library-table th, .ij-library-table td{ padding: 10px 8px; border-bottom: 1px solid rgba(148,163,184,.12); vertical-align: middle; }
  .ij-library-table th{ color: var(--muted); font-size: 12px; text-align:left; font-weight: 900; }
  .ij-library-table .rank{ width: 40px; color: var(--muted); font-weight: 900; }
  .ij-library-table .coverCell{ width: 54px; }
  .ij-library-table .coverCell .img{
    width:44px; height:44px;
    border-radius:12px;
    overflow:hidden;
    border:1px solid rgba(148,163,184,.18);
    background: var(--card2);
    display:block;
  }
  .ij-library-table .coverCell img{ width:100%; height:100%; object-fit:cover; display:block; }
  .ij-library-table .num{ text-align:right; white-space:nowrap; }
  .ij-library-table .tag{ display:inline-flex; margin-left:8px; font-size:11px; font-weight:900; color:var(--text); background: var(--card2); border:1px solid #24324f; border-radius:999px; padding:2px 8px; }

  .ij-library-table tr.isPlaying{ background: rgba(37,99,235,.08); }
  .ij-library-table tr.isPlaying td{ border-bottom-color: rgba(96,165,250,.18); }

  .ij-library-table tr.trackRow:hover{ background: rgba(148,163,184,.06); }

  .actions{ display:flex; gap:8px; justify-content:flex-end; flex-wrap:wrap; }
  .hoverOnly{
    opacity: 0;
    pointer-events: none;
    transform: translateY(1px);
    transition: opacity .14s ease, transform .14s ease;
  }
  tr.trackRow:hover .hoverOnly,
  tr.trackRow.isPlaying .hoverOnly{
    opacity: 1;
    pointer-events: auto;
    transform: translateY(0);
  }
  @media (hover: none){
    .hoverOnly{ opacity: 1; pointer-events: auto; transform: none; }
  }

  .trackMeta{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
  }
  .metaText{ min-width:0; }
  .metaText .title{
    font-weight:950;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
  }
  .metaText .artist{
    color: var(--muted);
    font-weight:900;
    font-size:12px;
    margin-top:2px;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
  }

  .ij-relSectionRow td{
    padding: 12px 8px;
    border-bottom: 1px solid rgba(148,163,184,.12);
    background: rgba(148,163,184,.04);
  }
  .ij-relSectionTitle{
    font-weight: 950;
    font-size: 12px;
    color: #cbd5e1;
    letter-spacing: .2px;
    display:flex;
    align-items:center;
    gap:8px;
  }
  .ij-relSectionBadge{
    font-size: 11px;
    font-weight: 900;
    color: var(--muted);
    border: 1px solid rgba(148,163,184,.18);
    background: rgba(11,18,32,.35);
    padding: 2px 8px;
    border-radius: 999px;
  }
  .ij-relSectionMobile{
    margin-top: 10px;
    padding: 8px 10px;
    border: 1px solid rgba(148,163,184,.14);
    border-radius: 14px;
    background: rgba(148,163,184,.05);
    color: #cbd5e1;
    font-weight: 950;
    font-size: 12px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
  }

  .ij-mTrack{ border:1px solid rgba(148,163,184,.14); border-radius:16px; padding:10px; margin-top:10px; background: rgba(11,18,32,.35); }
  .ij-mTrack.isPlaying{ border-color: rgba(96,165,250,.45); box-shadow: 0 14px 38px rgba(37,99,235,.10); }
  .ij-mTop{ display:grid; grid-template-columns: 28px 52px 1fr; gap:10px; align-items:center; }
  .ij-mRank{ color: var(--muted); font-weight:900; font-size:12px; }
  .ij-mCover .img{ width:52px;height:52px;border-radius:14px; overflow:hidden; border:1px solid rgba(148,163,184,.18); background: var(--card2); display:block; }
  .ij-mCover img{ width:100%; height:100%; object-fit:cover; display:block; }
  .ij-mMeta .titleRow{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
  .ij-mMeta .title{ font-weight:950; }
  .ij-mMeta .artist{ margin-top:2px; color: var(--muted); font-size:12px; font-weight:900; }
  .ij-mBottom{ margin-top:10px; display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; }
  .ij-mPlaysVal{ font-weight:950; }
  .ij-mActions{ display:flex; gap:8px; align-items:center; flex-wrap:wrap; justify-content:flex-end; }
  .ij-btnGhost.small{ padding:8px 10px; border-radius:12px; font-size:12px; }
  .ij-btn.small{ padding:8px 10px; border-radius:12px; font-size:12px; }

  @media (max-width: 520px){
    .ij-track-top{grid-template-columns: 1fr; }
    .ij-track-cover{width:100%;height:auto;aspect-ratio:1/1}
  }
  @media (max-width: 720px){
    .ij-library-mobile{ display:block; }
    .ij-library-tableWrap{ display:none; }
  }
</style>

<div class="ij-track-wrap" id="ijTrackPage">
  <div class="ij-track-card" id="trackCard">
    <div class="ij-track-top">
      <img class="ij-track-cover" src="<?= h($trackImgAbs) ?>" alt="">
      <div class="ij-track-meta">
        <div class="ij-track-titleRow">
          <div class="ij-track-title"><?= h($trackTitle) ?></div>
          <div class="eqWrap" id="eqMain" aria-hidden="true">
            <span class="eqBar"></span><span class="eqBar"></span><span class="eqBar"></span><span class="eqBar"></span>
          </div>
        </div>
        <div class="ij-track-artist">
          <a class="ij-trackLink" href="<?= h(ij_url('/artist/?name=' . rawurlencode($trackArtist) . '&lang=' . $lang)) ?>">
            <?= h($trackArtist) ?>
          </a>
        </div>
        <div class="ij-track-genre">
          <?php if ($trackGenre !== ''): ?><span class="ij-tag"><?= h($trackGenre) ?></span><?php endif; ?>
          <span class="ij-muted" style="font-size:12px;">ID: <code><?= h($id) ?></code></span>
        </div>

        <div class="ij-track-actions">
          <button class="ij-btn" id="btnPlay" type="button"><?= h($L['play']) ?></button>
          <button class="ij-btnGhost" id="btnShare" type="button"><?= h($L['share']) ?></button>
          <button class="ij-btnGhost" id="btnCopy" type="button"><?= h($L['copy']) ?></button>

          <a class="ij-btnGhost" href="<?= h(ij_url('/library/?lang='.$lang.'&t='.$id)) ?>"><?= h($L['openLibrary']) ?></a>
          <a class="ij-btnGhost" href="<?= h(ij_url('/?lang='.$lang)) ?>"><?= h($L['home']) ?></a>
        </div>
      </div>
    </div>
    <div class="ij-track-status" id="status"></div>
  </div>

  <div class="ij-more-card" id="moreCard">
    <div class="ij-more-head">
      <div>
        <h2 class="ij-more-title" id="moreTitle"><?= h($L['moreTitle']) ?></h2>
        <div class="ij-more-sub" id="moreSub"></div>
      </div>

      <div class="ij-more-toggle">
        <button class="ij-btnGhost" id="btnRangeWeek" type="button"><?= h($L['rangeWeek']) ?></button>
        <button class="ij-btnGhost" id="btnRangeMonth" type="button"><?= h($L['rangeMonth']) ?></button>
      </div>
    </div>

    <div class="ij-library-mobile" id="relMobile"></div>

    <div class="ij-library-tableWrap">
      <table class="ij-library-table" aria-label="Related tracks">
        <thead>
          <tr>
            <th class="rank">#</th>
            <th class="coverCell"></th>
            <th><?= h($L['track']) ?></th>
            <th class="num playsCol" id="relPlaysHead"><?= h($L['playsWeek']) ?></th>
            <th class="num actionsHead"><?= h($L['actions']) ?></th>
          </tr>
        </thead>
        <tbody id="relTbody">
          <tr><td colspan="5" class="ij-muted"><?= h($L['loading']) ?></td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
(function(){
  const SHARE_URL = <?= json_encode($canonical, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) ?>;
  const STR       = <?= json_encode($L, JSON_UNESCAPED_UNICODE) ?>;
  const TRACK     = <?= json_encode($currentPayload, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) ?>;
  const REL_GROUPS = <?= json_encode($groupsPayload, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) ?>;

  const root = document.getElementById('ijTrackPage');
  const btnPlay   = document.getElementById('btnPlay');
  const btnShare  = document.getElementById('btnShare');
  const btnCopy   = document.getElementById('btnCopy');
  const statusEl  = document.getElementById('status');
  const trackCard = document.getElementById('trackCard');
  const eqMain    = document.getElementById('eqMain');

  const moreSub      = document.getElementById('moreSub');
  const relTbody     = document.getElementById('relTbody');
  const relMobile    = document.getElementById('relMobile');
  const relPlaysHead = document.getElementById('relPlaysHead');

  const btnRangeWeek  = document.getElementById('btnRangeWeek');
  const btnRangeMonth = document.getElementById('btnRangeMonth');

  let playsMap = Object.create(null);
  let playsRange = localStorage.getItem('ij_track_rel_range') || 'week';
  if (playsRange !== 'month') playsRange = 'week';

  function setStatus(t, cls){
    statusEl.textContent = t || '';
    statusEl.className = cls ? ('ij-track-status ' + cls) : 'ij-track-status';
  }

  function esc(s){
    return String(s ?? '').replace(/[&<>"']/g, (c)=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));
  }

  function flashBtn(btn, text, ms = 1000){
  if (!btn) return;
  const old = btn.__oldText ?? btn.textContent;
  btn.__oldText = old;
  btn.textContent = text;
  btn.disabled = true;
  clearTimeout(btn.__flashTimer);
  btn.__flashTimer = setTimeout(()=>{
    btn.textContent = old;
    btn.disabled = false;
  }, ms);
}

  function shareUrlFor(id){
    const u = new URL('/track/', location.origin);
    u.searchParams.set('id', String(id||''));
    try{
      const cur = new URL(location.href);
      const l = cur.searchParams.get('lang');
      if (l === 'sv' || l === 'en') u.searchParams.set('lang', l);
    }catch{}
    return u.toString();
  }

  async function shareOrCopy({ url, title = '', text = '' }){
    url = String(url||'');
    title = String(title||'');
    text = String(text||'');

    try{
      if (navigator.share){
        if (navigator.canShare){
          const data = { url, title, text };
          if (navigator.canShare(data)){
            await navigator.share(data);
            return { ok:true, mode:'share' };
          }
        } else {
          await navigator.share({ url, title, text });
          return { ok:true, mode:'share' };
        }
      }
    }catch{}
    try{
      await navigator.clipboard.writeText(url);
      return { ok:true, mode:'copy' };
    }catch{
      return { ok:false, mode:'none' };
    }
  }

  function getPlayer(){
    return window.__ijPlayer || null;
  }

  function getPlayerState(){
    const p = getPlayer();
    return (p && typeof p.getState === 'function') ? p.getState() : null;
  }

  function isMainPlaying(){
    const st = getPlayerState();
    return !!(st && st.current && String(st.current.id) === String(TRACK.id) && !st.paused);
  }

  function setMainPlayingUI(isPlaying){
    trackCard.classList.toggle('isPlaying', !!isPlaying);
    btnPlay.textContent = isPlaying ? STR.stop : STR.play;
    if (eqMain) eqMain.classList.toggle('playing', !!isPlaying);
  }

  function updateMainUI(){
    setMainPlayingUI(isMainPlaying());
  }

btnShare.addEventListener('click', async ()=>{
  const title = (TRACK.title || 'iceBeats Track');
  const text  = TRACK.artist ? `${TRACK.title} – ${TRACK.artist}` : (TRACK.title || '');

  const res = await shareOrCopy({ url: SHARE_URL, title, text });
  if (res?.ok){
    flashBtn(btnShare, '✅', 900);
    setStatus(res.mode === 'share' ? STR.shared : STR.copied, 'ij-ok');
    setTimeout(()=>setStatus('', ''), 1200);
  }else{
    flashBtn(btnShare, '⚠', 1000);
    setStatus(STR.cantCopy, 'ij-err');
    setTimeout(()=>setStatus('', ''), 1600);
  }
});

btnCopy.addEventListener('click', async ()=>{
  try{
    await navigator.clipboard.writeText(SHARE_URL);
    flashBtn(btnCopy, '✅', 900);
    setStatus(STR.copied, 'ij-ok');
    setTimeout(()=>setStatus('', ''), 1200);
  }catch{
    flashBtn(btnCopy, '⚠', 1000);
    setStatus(STR.cantCopy, 'ij-err');
    setTimeout(()=>setStatus('', ''), 1600);
  }
});

  btnPlay.addEventListener('click', async ()=>{
    const player = getPlayer();
    if (!player) return;

    const st = getPlayerState();
    const sameTrack = !!(st && st.current && String(st.current.id) === String(TRACK.id));

    const fullQueue = [
      TRACK,
      ...(REL_GROUPS.artist || []),
      ...(REL_GROUPS.genre || []),
      ...(REL_GROUPS.newest || [])
    ];

    const seen = new Set();
    const queue = fullQueue.filter(it => {
      const id = String(it?.id || '');
      if (!id || seen.has(id)) return false;
      seen.add(id);
      return true;
    });

    player.setQueue(queue);

    if (sameTrack && !st.paused){
      player.stopToStart();
      return;
    }

    await player.playTrack(TRACK, { queue });
    updateMainUI();
    renderRelated();
  });

  function setRangeButtons(){
    btnRangeWeek.classList.toggle('active', playsRange === 'week');
    btnRangeMonth.classList.toggle('active', playsRange === 'month');
    relPlaysHead.textContent = (playsRange === 'month') ? STR.playsMonth : STR.playsWeek;
  }
  setRangeButtons();

  async function loadPlaysMap(){
    const range = (playsRange === 'month') ? 'month' : 'week';
    try{
      const res = await fetch('/api/stats_plays.php?range=' + encodeURIComponent(range), { cache:'no-store' });
      if(!res.ok) throw new Error('HTTP ' + res.status);
      const json = await res.json();

      const map = (json && json.playsByTrackId && typeof json.playsByTrackId === 'object')
        ? json.playsByTrackId
        : {};

      playsMap = Object.create(null);
      for (const [k, v] of Object.entries(map)){
        playsMap[String(k)] = Number(v) || 0;
      }
    }catch{
      playsMap = Object.create(null);
    }
  }

  function getPlaysFor(it){
    const v = playsMap[String(it?.id ?? '')];
    return (typeof v === 'number') ? v : null;
  }

  function sortGroup(list, mode){
    const arr = (list || []).slice();

    const hasPlays = arr.some(x => (getPlaysFor(x) ?? 0) > 0);
    if (hasPlays){
      arr.sort((a,b)=>{
        const ap = (getPlaysFor(a) ?? 0);
        const bp = (getPlaysFor(b) ?? 0);
        if (bp !== ap) return bp - ap;
        return String(a.title||'').localeCompare(String(b.title||''), 'sv');
      });
      return arr;
    }

    if (mode === 'newest'){
      arr.sort((a,b)=> (Number(b.createdAt||0) - Number(a.createdAt||0)));
      return arr;
    }

    arr.sort((a,b)=> String(a.title||'').localeCompare(String(b.title||''), 'sv'));
    return arr;
  }

  function buildGroupedList(){
    const artist = sortGroup(REL_GROUPS?.artist || [], 'artist');
    const genre  = sortGroup(REL_GROUPS?.genre  || [], 'genre');
    const newest = sortGroup(REL_GROUPS?.newest || [], 'newest');

    const total = artist.length + genre.length + newest.length;
    return { artist, genre, newest, total };
  }

  function isRelPlaying(it){
    const st = getPlayerState();
    return !!(st && st.current && String(st.current.id) === String(it?.id || '') && !st.paused);
  }

  function sectionRowHtml(title, badge){
    return `
      <tr class="ij-relSectionRow">
        <td colspan="5">
          <div class="ij-relSectionTitle">
            <span>${esc(title)}</span>
            <span class="ij-relSectionBadge">${esc(badge)}</span>
          </div>
        </td>
      </tr>
    `;
  }

  function sectionMobileHtml(title, badge){
    return `
      <div class="ij-relSectionMobile">
        <span>${esc(title)}</span>
        <span class="ij-relSectionBadge">${esc(badge)}</span>
      </div>
    `;
  }

  function renderRelated(){
    const { artist, genre, newest, total } = buildGroupedList();

    const aCount = artist.length;
    const gCount = genre.length;
    const nCount = newest.length;

    const sub = `${STR.groupArtist}: ${aCount} • ${STR.groupGenre}: ${gCount} • ${STR.groupNewest}: ${nCount}`;
    moreSub.textContent = (total > 0) ? sub : (STR.noMore || '');

    if (!total){
      relTbody.innerHTML = `<tr><td colspan="5" class="ij-muted">${esc(STR.noMore)}</td></tr>`;
      relMobile.innerHTML = `<div class="ij-muted">${esc(STR.noMore)}</div>`;
      return;
    }

    const makeRows = (list, offsetRank)=> list.map((it, idx)=>{
      const img = it.image ? `<img src="${esc(it.image)}" alt="" loading="lazy">` : '';
      const plays = getPlaysFor(it);
      const playsTxt = (plays == null) ? '—' : String(plays);
      const trackLink = shareUrlFor(it.id);

      const playing = isRelPlaying(it);
      const rowCls = `trackRow${playing ? ' isPlaying' : ''}`;
      const playLabel = playing ? '⏹' : '▶';

      const shareTitle = (it.title || 'iceBeats Track');
      const shareText  = it.artist ? `${it.title || ''} – ${it.artist}` : (it.title || '');

      return `
        <tr class="${rowCls}">
          <td class="rank">${offsetRank + idx + 1}</td>
          <td class="coverCell"><div class="img">${img}</div></td>
          <td>
            <div class="trackMeta">
              <div class="metaText">
                <div class="title"><a class="ij-trackLink" href="${esc(trackLink)}">${esc(it.title || '—')}</a></div>
                <div class="artist">${esc(it.artist || '')}${it.genre ? `<span class="tag">${esc(it.genre)}</span>` : ''}</div>
              </div>
              <div class="eqWrap ${playing ? 'playing' : ''}" aria-hidden="true">
                <span class="eqBar"></span><span class="eqBar"></span><span class="eqBar"></span><span class="eqBar"></span>
              </div>
            </div>
          </td>
          <td class="num playsCol">${esc(playsTxt)}</td>
          <td class="num">
            <div class="actions">
              <button class="ij-btnGhost small hoverOnly" type="button"
                data-share="${esc(it.id)}"
                data-share-title="${esc(shareTitle)}"
                data-share-text="${esc(shareText)}"
              >${esc(STR.share)}</button>

              <button class="ij-btnGhost small hoverOnly" type="button" data-copy="${esc(trackLink)}">${esc(STR.copy)}</button>

              <button class="ij-btn small playBtn hoverOnly" type="button"
                data-play="${esc(it.id)}"
              >${playLabel}</button>
            </div>
          </td>
        </tr>
      `;
    }).join('');

    let rank = 0;
    let html = '';

    if (aCount){
      html += sectionRowHtml(STR.groupArtist + (TRACK.artist ? `: ${TRACK.artist}` : ''), String(aCount));
      html += makeRows(artist, rank);
      rank += aCount;
    }
    if (gCount){
      html += sectionRowHtml(STR.groupGenre + (TRACK.genre ? `: ${TRACK.genre}` : ''), String(gCount));
      html += makeRows(genre, rank);
      rank += gCount;
    }
    if (nCount){
      html += sectionRowHtml(STR.groupNewest, String(nCount));
      html += makeRows(newest, rank);
      rank += nCount;
    }

    relTbody.innerHTML = html;

    const rangeLabel = (playsRange === 'month') ? STR.playsMonth : STR.playsWeek;

    const makeMobile = (list)=> list.map((it)=>{
      const img = it.image ? `<img src="${esc(it.image)}" alt="" loading="lazy">` : '';
      const plays = getPlaysFor(it);
      const playsTxt = (plays == null) ? '—' : String(plays);
      const trackLink = shareUrlFor(it.id);

      const playing = isRelPlaying(it);
      const playLabel = playing ? '⏹' : '▶';

      const shareTitle = (it.title || 'iceBeats Track');
      const shareText  = it.artist ? `${it.title || ''} – ${it.artist}` : (it.title || '');

      return `
        <div class="ij-mTrack ${playing ? 'isPlaying' : ''}">
          <div class="ij-mTop">
            <div class="ij-mRank"></div>
            <div class="ij-mCover"><div class="img">${img}</div></div>
            <div class="ij-mMeta">
              <div class="titleRow">
                <div class="title"><a class="ij-trackLink" href="${esc(trackLink)}">${esc(it.title || '—')}</a></div>
                <div class="eqWrap ${playing ? 'playing' : ''}" aria-hidden="true">
                  <span class="eqBar"></span><span class="eqBar"></span><span class="eqBar"></span><span class="eqBar"></span>
                </div>
              </div>
              <div class="artist">${esc(it.artist || '')}${it.genre ? `<span class="tag">${esc(it.genre)}</span>` : ''}</div>
            </div>
          </div>

          <div class="ij-mBottom">
            <div class="ij-mPlays" title="${esc(rangeLabel)}">
              <span class="ij-muted">${esc(rangeLabel)}:</span>
              <span class="ij-mPlaysVal">${esc(playsTxt)}</span>
            </div>

            <div class="ij-mActions">
              <button class="ij-btnGhost small" type="button"
                data-share="${esc(it.id)}"
                data-share-title="${esc(shareTitle)}"
                data-share-text="${esc(shareText)}"
              >${esc(STR.share)}</button>
              <button class="ij-btnGhost small" type="button" data-copy="${esc(trackLink)}">${esc(STR.copy)}</button>
              <button class="ij-btn small" type="button"
                data-play="${esc(it.id)}"
              >${playLabel}</button>
            </div>
          </div>
        </div>
      `;
    }).join('');

    let m = '';
    if (aCount){
      m += sectionMobileHtml(STR.groupArtist + (TRACK.artist ? `: ${TRACK.artist}` : ''), String(aCount));
      m += makeMobile(artist);
    }
    if (gCount){
      m += sectionMobileHtml(STR.groupGenre + (TRACK.genre ? `: ${TRACK.genre}` : ''), String(gCount));
      m += makeMobile(genre);
    }
    if (nCount){
      m += sectionMobileHtml(STR.groupNewest, String(nCount));
      m += makeMobile(newest);
    }

    relMobile.innerHTML = m;
  }

  function buildFullQueue(){
    const all = [TRACK, ...(REL_GROUPS.artist || []), ...(REL_GROUPS.genre || []), ...(REL_GROUPS.newest || [])];
    const seen = new Set();
    return all.filter(it => {
      const id = String(it?.id || '');
      if (!id || seen.has(id)) return false;
      seen.add(id);
      return true;
    });
  }

  async function handleRootClick(e){
    const btn = e.target.closest('button');
    if(!btn || !root.contains(btn)) return;

    const shareId = btn.getAttribute('data-share');
if (shareId){
  const url = shareUrlFor(shareId);
  const title = btn.getAttribute('data-share-title') || 'iceBeats Track';
  const text  = btn.getAttribute('data-share-text') || '';
  const res = await shareOrCopy({ url, title, text });

  if (res?.ok){
    flashBtn(btn, '✅', 900);
    setStatus(res.mode === 'share' ? STR.shared : STR.copied, 'ij-ok');
    setTimeout(()=>setStatus('', ''), 1200);
  }else{
    flashBtn(btn, '⚠', 1000);
    setStatus(STR.cantCopy, 'ij-err');
    setTimeout(()=>setStatus('', ''), 1600);
  }
  return;
}

const copy = btn.getAttribute('data-copy');
if (copy){
  try{
    await navigator.clipboard.writeText(copy);
    flashBtn(btn, '✅', 900);
    setStatus(STR.copied, 'ij-ok');
    setTimeout(()=>setStatus('', ''), 1200);
  }catch{
    flashBtn(btn, '⚠', 1000);
    setStatus(STR.cantCopy, 'ij-err');
    setTimeout(()=>setStatus('', ''), 1600);
  }
  return;
}

    const playId = btn.getAttribute('data-play');
    if (playId){
      const player = getPlayer();
      if (!player) return;

      const queue = buildFullQueue();
      player.setQueue(queue);

      const target = queue.find(it => String(it.id) === String(playId));
      if (!target) return;

      const st = getPlayerState();
      const samePlaying = !!(st && st.current && String(st.current.id) === String(target.id) && !st.paused);

      if (samePlaying) {
        player.stopToStart();
      } else {
        await player.playTrack(target, { queue });
      }

      updateMainUI();
      renderRelated();
    }
  }

  function handlePlayerState(){
    updateMainUI();
    renderRelated();
  }

  btnRangeWeek.addEventListener('click', async ()=>{
    playsRange = 'week';
    localStorage.setItem('ij_track_rel_range', playsRange);
    setRangeButtons();
    await loadPlaysMap();
    renderRelated();
  });

  btnRangeMonth.addEventListener('click', async ()=>{
    playsRange = 'month';
    localStorage.setItem('ij_track_rel_range', playsRange);
    setRangeButtons();
    await loadPlaysMap();
    renderRelated();
  });

  root.addEventListener('click', handleRootClick);
  window.addEventListener('ij:player-state', handlePlayerState);

  function cleanup(){
    root.removeEventListener('click', handleRootClick);
    window.removeEventListener('ij:player-state', handlePlayerState);
  }

  if (window.__ijRegisterPageCleanup) {
    window.__ijRegisterPageCleanup(cleanup);
  }

  (async function init(){
    const player = getPlayer();
    if (player) {
      player.setQueue(buildFullQueue());
    }

    updateMainUI();
    relTbody.innerHTML = `<tr><td colspan="5" class="ij-muted">${esc(STR.loading)}</td></tr>`;
    relMobile.innerHTML = `<div class="ij-muted">${esc(STR.loading)}</div>`;
    await loadPlaysMap();
    setRangeButtons();
    renderRelated();
  })();
})();
</script>

<?php require __DIR__ . '/../_partials/footer.php'; ?>