<?php
/* /domains/icebeats.io/public_html/artist/index.php */
declare(strict_types=1);

require __DIR__ . '/../_partials/i18n.php';
require __DIR__ . '/../_partials/meta.php';
require __DIR__ . '/../_partials/creator_lib.php';

$lang = ij_lang();

function h(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function norm_artist(string $s): string {
  $s = trim(mb_strtolower($s, 'UTF-8'));
  $s = preg_replace('/\s+/u', ' ', $s) ?? $s;
  return $s;
}

function resolve_url(string $base, string $u): string {
  if ($u === '') return '';
  if (preg_match('~^https?://~i', $u)) return $u;
  return rtrim($base, '/') . '/' . ltrim($u, '/');
}

$creatorName = trim((string)($_GET['name'] ?? ''));
$creatorSlugFromQuery = trim((string)($_GET['slug'] ?? ''));

$creatorProfile = null;

if ($creatorSlugFromQuery !== '') {
  $creatorProfile = ij_creator_find_by_slug($creatorSlugFromQuery);
}

if (!$creatorProfile && $creatorName !== '') {
  $creatorProfile = ij_creator_find_by_name($creatorName);
}

$creatorNorm = norm_artist($creatorName);
if ($creatorNorm === '' && $creatorProfile && is_array($creatorProfile)) {
  $creatorNorm = norm_artist((string)($creatorProfile['name'] ?? ''));
}

$libraryPath = realpath(__DIR__ . '/../library.json') ?: (__DIR__ . '/../library.json');
$lib = is_file($libraryPath) ? json_decode((string)file_get_contents($libraryPath), true) : null;
if (!is_array($lib)) $lib = ['baseUrl' => ij_base_url(), 'items' => []];

$baseUrl = (string)($lib['baseUrl'] ?? ij_base_url());
$itemsRaw = is_array($lib['items'] ?? null) ? $lib['items'] : [];

$creatorTracks = [];
foreach ($itemsRaw as $it) {
  if (!is_array($it)) continue;
  $artist = trim((string)($it['artist'] ?? ''));
  if ($creatorNorm === '' || norm_artist($artist) !== $creatorNorm) continue;
  $creatorTracks[] = $it;
}

usort($creatorTracks, function(array $a, array $b): int {
  $ac = (int)($a['createdAt'] ?? $a['created'] ?? 0);
  $bc = (int)($b['createdAt'] ?? $b['created'] ?? 0);
  return $bc <=> $ac;
});

$displayName = '—';

if ($creatorProfile && is_array($creatorProfile) && trim((string)($creatorProfile['name'] ?? '')) !== '') {
  $displayName = trim((string)$creatorProfile['name']);
} elseif ($creatorName !== '') {
  $displayName = $creatorName;
} elseif (!empty($creatorTracks[0]['artist'])) {
  $displayName = (string)$creatorTracks[0]['artist'];
}

$trackCount = count($creatorTracks);

if (!$creatorProfile && $displayName !== '—') {
  $creatorProfile = ij_creator_find_by_name($displayName);
}

$creatorSlug = '';
if ($creatorProfile && is_array($creatorProfile)) {
  $creatorSlug = trim((string)($creatorProfile['slug'] ?? ''));
}

$profileImage = '';
$bioSv = '';
$bioEn = '';
$verified = false;
$ownerEmail = '';
$links = [
  'website' => '',
  'instagram' => '',
  'spotify' => '',
];

if ($creatorProfile && is_array($creatorProfile)) {
  $profileImage = trim((string)($creatorProfile['image'] ?? ''));
  $bioSv = trim((string)($creatorProfile['bio_sv'] ?? ''));
  $bioEn = trim((string)($creatorProfile['bio_en'] ?? ''));
  $verified = !empty($creatorProfile['verified']);
  $ownerEmail = trim((string)($creatorProfile['owner_email'] ?? ''));

  if (isset($creatorProfile['links']) && is_array($creatorProfile['links'])) {
    $links['website'] = trim((string)($creatorProfile['links']['website'] ?? ''));
    $links['instagram'] = trim((string)($creatorProfile['links']['instagram'] ?? ''));
    $links['spotify'] = trim((string)($creatorProfile['links']['spotify'] ?? ''));
  }
}

$aboutText = ($lang === 'en')
  ? ($bioEn !== '' ? $bioEn : ($bioSv !== '' ? $bioSv : ''))
  : ($bioSv !== '' ? $bioSv : ($bioEn !== '' ? $bioEn : ''));

$pageTitleText = ($lang === 'en')
  ? ($displayName !== '—' ? $displayName . ' – Artist on iceBeats.io' : 'Artist – iceBeats.io')
  : ($displayName !== '—' ? $displayName . ' – Artist på iceBeats.io' : 'Artist – iceBeats.io');

$pageDescText = ($lang === 'en')
  ? ($displayName !== '—'
      ? 'Explore tracks and artist profile for ' . $displayName . ' on iceBeats.io.'
      : 'Explore artists and tracks on iceBeats.io.')
  : ($displayName !== '—'
      ? 'Utforska låtar och artistprofil för ' . $displayName . ' på iceBeats.io.'
      : 'Utforska artister och låtar på iceBeats.io.');

$canonical = ij_abs('/artist/?name=' . rawurlencode($displayName));
if ($lang === 'en' || $lang === 'sv') $canonical .= '&lang=' . rawurlencode($lang);

$metaImage = $profileImage !== '' ? $profileImage : ij_abs('/share/og.jpg');

$meta = ij_build_meta([
  'title' => $pageTitleText,
  'description' => $pageDescText,
  'canonical' => $canonical,
  'image' => $metaImage,
  'type' => 'profile',
]);

$pageTitle = $meta['title'];
$pageHead = ij_render_meta($meta);

require __DIR__ . '/../_partials/header.php';

$T = [
  'sv' => [
    'heroSub' => 'Artistsida på iceBeats.io',
    'tracks' => 'låtar',
    'noTracks' => 'Inga låtar hittades för denna artist.',
    'openTrack' => 'Öppna låt',
    'playTrack' => 'Spela',
    'pauseTrack' => 'Pausa',
    'copyLink' => 'Kopiera länk',
    'share' => 'Dela',
    'copied' => 'Kopierad ✅',
    'shared' => 'Delat ✅',
    'copyFailed' => 'Kunde inte kopiera.',
    'openLibrary' => 'Öppna Låtar',
    'upload' => 'Ladda upp',
    'latest' => 'Senaste uppladdningar',
    'genre' => 'Genre',
    'aboutCreator' => 'Om denna artist',
    'aboutFallback' => 'Den här sidan samlar artist-relaterade tracks från iceBeats-biblioteket på ett ställe.',
    'verified' => 'Verifierad artist',
    'unclaimed' => 'Ej verifierad ännu',
    'claim' => 'Verifiera profil',
    'requestEdit' => 'Begär redigeringslänk',
    'links' => 'Länkar',
    'website' => 'Webbplats',
    'instagram' => 'Instagram',
    'spotify' => 'Spotify',
    'latestSummaryFallback' => 'Ingen uppladdningshistorik ännu.',
    'tracksBy' => 'Tracks av',
    'noLinks' => 'Inga länkar tillagda ännu.',
    'artistProfile' => 'Artistprofil',
  ],
  'en' => [
    'heroSub' => 'Artist page on iceBeats.io',
    'tracks' => 'tracks',
    'noTracks' => 'No tracks were found for this artist.',
    'openTrack' => 'Open track',
    'playTrack' => 'Play',
    'pauseTrack' => 'Pause',
    'copyLink' => 'Copy link',
    'share' => 'Share',
    'copied' => 'Copied ✅',
    'shared' => 'Shared ✅',
    'copyFailed' => 'Could not copy.',
    'openLibrary' => 'Open Tracks',
    'upload' => 'Upload',
    'latest' => 'Latest uploads',
    'genre' => 'Genre',
    'aboutCreator' => 'About this artist',
    'aboutFallback' => 'This page collects artist-related tracks from the iceBeats library in one place.',
    'verified' => 'Verified artist',
    'unclaimed' => 'Not claimed yet',
    'claim' => 'Claim profile',
    'requestEdit' => 'Request edit link',
    'links' => 'Links',
    'website' => 'Website',
    'instagram' => 'Instagram',
    'spotify' => 'Spotify',
    'latestSummaryFallback' => 'No upload history yet.',
    'tracksBy' => 'Tracks by',
    'noLinks' => 'No links added yet.',
    'artistProfile' => 'Artist profile',
  ],
];
$L = $T[$lang] ?? $T['sv'];

$claimHref = ij_url('/artist/claim/?name=' . rawurlencode($displayName) . '&lang=' . rawurlencode($lang));
$requestEditHref = ij_url('/artist/request-edit-link/?name=' . rawurlencode($displayName) . '&lang=' . rawurlencode($lang));

$firstLetter = mb_strtoupper(mb_substr($displayName !== '—' ? $displayName : 'C', 0, 1, 'UTF-8'), 'UTF-8');

$tracksPayload = [];
foreach ($creatorTracks as $track) {
  $id = (string)($track['id'] ?? '');
  $title = (string)($track['title'] ?? $track['name'] ?? '—');
  $artist = (string)($track['artist'] ?? '');
  $genre = (string)($track['genre'] ?? '');
  $image = resolve_url($baseUrl, (string)($track['image'] ?? ''));
  $url = resolve_url($baseUrl, (string)($track['url'] ?? ''));
  $tracksPayload[] = [
    'id' => $id,
    'title' => $title,
    'artist' => $artist,
    'genre' => $genre,
    'image' => $image,
    '_resolvedUrl' => $url,
    'startMs' => (int)($track['startMs'] ?? 0),
    'endMs' => (int)($track['endMs'] ?? 0),
    'createdAt' => (int)($track['createdAt'] ?? $track['created'] ?? 0),
  ];
}
?>

<style>
.wrap{
  max-width:1120px;
  margin:0 auto;
  padding:0 16px;
}

.card{
  background:#0f172a;
  border:1px solid #1f2a44;
  border-radius:18px;
  padding:22px;
  margin:14px 0;
}

.heroCard{
  position:relative;
  overflow:hidden;
  background:
    radial-gradient(circle at top right, rgba(96,165,250,.16), transparent 28%),
    linear-gradient(180deg, rgba(11,18,32,.96), rgba(15,23,42,.96));
}

.heroLayout{
  display:grid;
  grid-template-columns:140px minmax(0, 1fr) auto;
  gap:22px;
  align-items:center;
}
@media (max-width: 980px){
  .heroLayout{
    grid-template-columns:1fr;
    align-items:start;
  }
}

.avatarCol{
  display:flex;
  align-items:center;
  justify-content:center;
}

.profileAvatar{
  width:140px;
  height:140px;
  border-radius:26px;
  object-fit:cover;
  border:1px solid #24324f;
  background:#0b1220;
  display:block;
  box-shadow:0 20px 45px rgba(0,0,0,.18);
}
.profileAvatarFallback{
  width:140px;
  height:140px;
  border-radius:26px;
  border:1px solid #24324f;
  background:
    radial-gradient(circle at 35% 30%, rgba(96,165,250,.18), transparent 35%),
    linear-gradient(180deg, #0b1220, #08101f);
  color:#e5e7eb;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:42px;
  font-weight:950;
  box-shadow:0 20px 45px rgba(0,0,0,.18);
}

.heroMain{
  min-width:0;
}

.kicker{
  display:inline-flex;
  align-items:center;
  gap:8px;
  font-size:11px;
  font-weight:900;
  letter-spacing:.55px;
  text-transform:uppercase;
  color:#94a3b8;
  margin-bottom:10px;
}
.kickerDot{
  width:8px;
  height:8px;
  border-radius:999px;
  background:#60a5fa;
  box-shadow:0 0 0 6px rgba(96,165,250,.10);
}

.heroTitleRow{
  display:flex;
  align-items:center;
  gap:10px;
  flex-wrap:wrap;
}
.heroTitle{
  margin:0;
  font-size:44px;
  line-height:1;
  color:#f8fafc;
  letter-spacing:-.02em;
}
@media (max-width: 620px){
  .heroTitle{
    font-size:34px;
  }
}

.heroSub{
  color:#9fb0ca;
  font-size:15px;
  line-height:1.75;
  margin-top:10px;
  max-width:760px;
}

.heroPills{
  display:flex;
  gap:10px;
  flex-wrap:wrap;
  margin-top:18px;
}
.heroPill{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:8px 12px;
  border-radius:999px;
  border:1px solid #24324f;
  background:#0b1220;
  color:#d9e2f1;
  font-size:12px;
  font-weight:800;
}
.heroPill.verified{
  color:#86efac;
  border-color:rgba(134,239,172,.24);
  background:rgba(22,101,52,.16);
}
.heroPill.unclaimed{
  color:#fbbf24;
  border-color:rgba(251,191,36,.24);
  background:rgba(120,53,15,.16);
}

.ctaCol{
  display:flex;
  align-items:flex-start;
  justify-content:flex-end;
}

.ctaRow{
  display:flex;
  gap:10px;
  flex-wrap:wrap;
  align-items:center;
  justify-content:flex-end;
  max-width:320px;
}
@media (max-width: 980px){
  .ctaCol{
    justify-content:flex-start;
  }
  .ctaRow{
    justify-content:flex-start;
    max-width:none;
  }
}

.infoGrid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:14px;
  align-items:stretch;
}
@media (max-width: 900px){
  .infoGrid{
    grid-template-columns:1fr;
  }
}

.infoCard{
  display:flex;
  flex-direction:column;
  min-height:280px;
}

.sectionTitle{
  margin:0;
  font-size:18px;
  line-height:1.2;
  color:#f1f5f9;
  font-weight:950;
}

.sectionBody{
  margin-top:18px;
  flex:1 1 auto;
  display:flex;
  flex-direction:column;
  justify-content:flex-start;
}

.sectionText{
  color:#9fb0ca;
  font-size:15px;
  line-height:1.95;
  white-space:pre-line;
  margin:0;
  padding:0;
  max-width:100%;
}

.linkList{
  display:flex;
  gap:10px;
  flex-wrap:wrap;
  margin:0;
}
.linkChip{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:10px 13px;
  border-radius:999px;
  border:1px solid #24324f;
  background:#0b1220;
  color:#e5e7eb;
  text-decoration:none;
  font-size:12px;
  font-weight:800;
  transition:border-color .18s ease, transform .18s ease, background .18s ease;
}
.linkChip:hover{
  border-color:#3b82f6;
  transform:translateY(-1px);
  background:#0d1527;
}

.emptyLinks{
  color:#94a3b8;
  font-size:14px;
  line-height:1.8;
  margin:0;
}

.tracksCard{
  overflow:hidden;
}

.tracksHead{
  display:flex;
  align-items:flex-end;
  justify-content:space-between;
  gap:16px;
  flex-wrap:wrap;
  margin-bottom:16px;
}
.tracksHeadText{
  min-width:0;
}
.tracksSub{
  margin-top:8px;
  color:#94a3b8;
  font-size:14px;
  line-height:1.7;
}

.tracksGrid{
  display:grid;
  grid-template-columns:repeat(4, 1fr);
  gap:14px;
}
@media (max-width: 1100px){
  .tracksGrid{
    grid-template-columns:repeat(3, 1fr);
  }
}
@media (max-width: 860px){
  .tracksGrid{
    grid-template-columns:repeat(2, 1fr);
  }
}
@media (max-width: 620px){
  .wrap{
    padding: 0 10px;
  }

  .card{
    padding: 16px;
    border-radius: 16px;
  }

  .heroTitle{
    font-size: 30px;
    line-height: 1.02;
  }

  .heroSub{
    font-size: 14px;
    line-height: 1.6;
  }

  .heroPills{
    gap:8px;
    margin-top: 14px;
  }

  .heroPill{
    font-size: 11px;
    padding: 7px 10px;
  }

  .ctaRow{
    display:grid;
    grid-template-columns: 1fr;
    width:100%;
    gap:8px;
  }

  .ctaRow a{
    justify-content:center;
    min-height:42px;
  }

  .tracksGrid{
    grid-template-columns:repeat(2, minmax(0, 1fr));
    gap:10px;
  }

  .trackCard{
    border-radius:14px;
  }

  .trackBody{
    padding:10px;
  }

  .trackTitle{
    font-size:14px;
    line-height:1.25;
  }

  .trackMeta{
    font-size:11px;
    line-height:1.55;
    margin-top:6px;
  }

  .trackActions{
    margin-top:10px;
    gap:6px;
  }

  .trackActionBtn,
  .trackActions .ij-btnGhost{
    min-height:34px;
    padding: 8px 10px;
    font-size:11px;
    border-radius:12px;
  }

  .trackPlayBadge,
  .trackShareBtn{
    width:34px;
    height:34px;
    border-radius:12px;
    top:8px;
  }

  .trackPlayBadge{ right:8px; }
  .trackShareBtn{ right:46px; }
}

@media (max-width: 420px){
  .tracksGrid{
    grid-template-columns:repeat(2, minmax(0, 1fr));
  }

  .trackTitle{
    font-size:13px;
  }

  .trackActions{
    display:grid;
    grid-template-columns:1fr;
  }

  .trackActionBtn,
  .trackActions .ij-btnGhost{
    width:100%;
    justify-content:center;
  }
}

.trackCard{
  background:#0b1220;
  border:1px solid #24324f;
  border-radius:16px;
  overflow:hidden;
  transition:transform .18s ease, border-color .18s ease, box-shadow .18s ease;
}
.trackCard:hover{
  transform:translateY(-3px);
  border-color:#3b82f6;
  box-shadow:0 16px 34px rgba(0,0,0,.20);
}
.trackCard.isPlaying{
  border-color: rgba(96,165,250,.65);
  box-shadow: 0 16px 44px rgba(37,99,235,.12), 0 18px 40px rgba(0,0,0,.35);
}
.trackCoverWrap{
  position:relative;
}
.trackCover{
  display:block;
  width:100%;
  aspect-ratio:1 / 1;
  object-fit:cover;
  background:#08101f;
  border-bottom:1px solid #1f2a44;
}
.trackBody{
  padding:14px;
}
.trackTitle{
  font-size:16px;
  font-weight:950;
  color:#e5e7eb;
  line-height:1.3;
}
.trackMeta{
  margin-top:7px;
  color:#94a3b8;
  font-size:12px;
  line-height:1.7;
}
.trackActions{
  margin-top:12px;
  display:flex;
  gap:8px;
  flex-wrap:wrap;
}
.trackActions a{
  text-decoration:none;
}

.trackActionBtn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-height:38px;
}

.trackPlayBadge,
.trackShareBtn{
  position:absolute;
  top:10px;
  width:38px;
  height:38px;
  border-radius:14px;
  border:1px solid rgba(148,163,184,.25);
  background:rgba(11,18,32,.72);
  color:#fff;
  font-weight:950;
  display:flex;
  align-items:center;
  justify-content:center;
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}
.trackPlayBadge{
  right:10px;
  pointer-events:none;
}
.trackShareBtn{
  right:54px;
  cursor:pointer;
}

.emptyState{
  color:#94a3b8;
  font-size:14px;
  line-height:1.8;
}
</style>

<div class="wrap" id="ijArtistPage">

  <div class="card heroCard">
    <div class="heroLayout">

      <div class="avatarCol">
        <?php if ($profileImage !== ''): ?>
          <img class="profileAvatar" src="<?= h($profileImage) ?>" alt="<?= h($displayName) ?>">
        <?php else: ?>
          <div class="profileAvatarFallback" aria-hidden="true"><?= h($firstLetter) ?></div>
        <?php endif; ?>
      </div>

      <div class="heroMain">
        <div class="kicker">
          <span class="kickerDot"></span>
          <span><?= h($L['artistProfile']) ?></span>
        </div>

        <div class="heroTitleRow">
          <h1 class="heroTitle"><?= h($displayName) ?></h1>

          <?php if ($verified): ?>
            <span class="heroPill verified">✓ <?= h($L['verified']) ?></span>
          <?php else: ?>
            <span class="heroPill unclaimed"><?= h($L['unclaimed']) ?></span>
          <?php endif; ?>
        </div>

        <div class="heroSub"><?= h($L['heroSub']) ?></div>

        <div class="heroPills">
          <span class="heroPill"><?= h((string)$trackCount) ?> <?= h($L['tracks']) ?></span>
          <span class="heroPill">iceBeats.io</span>
          <?php if ($creatorSlug !== ''): ?>
            <span class="heroPill"><?= h($creatorSlug) ?></span>
          <?php endif; ?>
        </div>
      </div>

      <div class="ctaCol">
        <div class="ctaRow">
          <?php if ($verified && $ownerEmail !== ''): ?>
            <a class="ij-btnGhost" href="<?= h($requestEditHref) ?>"><?= h($L['requestEdit']) ?></a>
          <?php else: ?>
            <a class="ij-btnGhost" href="<?= h($claimHref) ?>"><?= h($L['claim']) ?></a>
          <?php endif; ?>

          <a class="ij-btnGhost" href="<?= h(ij_url('/library/?lang=' . $lang)) ?>"><?= h($L['openLibrary']) ?></a>
          <a class="ij-btn" href="<?= h(ij_url('/upload/?lang=' . $lang)) ?>" data-no-ajax="1"><?= h($L['upload']) ?></a>
        </div>
      </div>

    </div>
  </div>

  <div class="infoGrid">
    <div class="card infoCard">
      <h2 class="sectionTitle"><?= h($L['aboutCreator']) ?></h2>
      <div class="sectionBody">
        <div class="sectionText"><?= h($aboutText !== '' ? $aboutText : $L['aboutFallback']) ?></div>
      </div>
    </div>

    <div class="card infoCard">
      <h2 class="sectionTitle"><?= h($L['links']) ?></h2>
      <div class="sectionBody">
        <?php if ($links['website'] !== '' || $links['instagram'] !== '' || $links['spotify'] !== ''): ?>
          <div class="linkList">
            <?php if ($links['website'] !== ''): ?>
              <a class="linkChip" href="<?= h($links['website']) ?>" target="_blank" rel="noopener noreferrer">
                🌐 <?= h($L['website']) ?>
              </a>
            <?php endif; ?>

            <?php if ($links['instagram'] !== ''): ?>
              <a class="linkChip" href="<?= h($links['instagram']) ?>" target="_blank" rel="noopener noreferrer">
                📸 <?= h($L['instagram']) ?>
              </a>
            <?php endif; ?>

            <?php if ($links['spotify'] !== ''): ?>
              <a class="linkChip" href="<?= h($links['spotify']) ?>" target="_blank" rel="noopener noreferrer">
                🎵 <?= h($L['spotify']) ?>
              </a>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div class="emptyLinks"><?= h($L['noLinks']) ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="card tracksCard">
    <div class="tracksHead">
      <div class="tracksHeadText">
        <h2 class="sectionTitle"><?= h($L['latest']) ?></h2>
        <div class="tracksSub">
          <?= $trackCount > 0
            ? h($L['tracksBy'] . ' ' . $displayName . ' • ' . $trackCount . ' ' . $L['tracks'])
            : h($L['latestSummaryFallback']) ?>
        </div>
      </div>
    </div>

    <?php if (!$trackCount): ?>
      <div class="emptyState"><?= h($L['noTracks']) ?></div>
    <?php else: ?>
      <div class="tracksGrid">
        <?php foreach ($tracksPayload as $track):
          $trackHref = ij_url('/track/?id=' . rawurlencode((string)$track['id']) . '&lang=' . rawurlencode($lang));
          $trackHrefAbs = ij_abs('/track/?id=' . rawurlencode((string)$track['id']) . '&lang=' . rawurlencode($lang));
        ?>
          <article class="trackCard" data-track-id="<?= h((string)$track['id']) ?>">
            <div class="trackCoverWrap">
              <?php if ((string)$track['image'] !== ''): ?>
                <a href="<?= h($trackHref) ?>">
                  <img class="trackCover" src="<?= h((string)$track['image']) ?>" alt="">
                </a>
              <?php else: ?>
                <a href="<?= h($trackHref) ?>" class="trackCover" style="display:block;"></a>
              <?php endif; ?>

              <button class="trackShareBtn" type="button" data-share="<?= h((string)$track['id']) ?>" title="<?= h($L['share']) ?>">🔗</button>
              <div class="trackPlayBadge" data-play-badge="<?= h((string)$track['id']) ?>">▶</div>
            </div>

            <div class="trackBody">
              <div class="trackTitle"><?= h((string)$track['title']) ?></div>

              <div class="trackMeta">
                <?= h((string)$track['artist']) ?>
                <?php if ((string)$track['genre'] !== ''): ?>
                  <br><?= h($L['genre']) ?>: <?= h((string)$track['genre']) ?>
                <?php endif; ?>
              </div>

              <div class="trackActions">
                <button class="ij-btn trackActionBtn" type="button" data-play-track="<?= h((string)$track['id']) ?>"><?= h($L['playTrack']) ?></button>
                <a class="ij-btnGhost trackActionBtn" href="<?= h($trackHref) ?>"><?= h($L['openTrack']) ?></a>
                <button
                  class="ij-btnGhost trackActionBtn"
                  type="button"
                  data-copy-link="<?= h($trackHrefAbs) ?>"
                  data-copy-label="<?= h($L['copyLink']) ?>"
                  data-copied-label="<?= h($L['copied']) ?>"
                  data-failed-label="<?= h($L['copyFailed']) ?>"
                ><?= h($L['copyLink']) ?></button>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</div>

<script>
(function(){
  const root = document.getElementById('ijArtistPage');
  if (!root) return;

  const STR = <?= json_encode([
    'playTrack' => $L['playTrack'],
    'pauseTrack' => $L['pauseTrack'],
    'copied' => $L['copied'],
    'shared' => $L['shared'],
    'copyFailed' => $L['copyFailed'],
  ], JSON_UNESCAPED_UNICODE) ?>;

  const TRACKS = <?= json_encode($tracksPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  const LANG = <?= json_encode($lang, JSON_UNESCAPED_UNICODE) ?>;

  function getPlayer(){
    return window.__ijPlayer || null;
  }

  function getPlayerState(){
    const p = getPlayer();
    return (p && typeof p.getState === 'function') ? p.getState() : null;
  }

  function shareUrlFor(id){
    const u = new URL('/track/', location.origin);
    u.searchParams.set('id', String(id || ''));
    if (LANG === 'sv' || LANG === 'en') u.searchParams.set('lang', LANG);
    return u.toString();
  }

  async function shareOrCopy({ url, title = '', text = '' }){
    url = String(url || '');
    title = String(title || '');
    text = String(text || '');

    try{
      if (navigator.share){
        await navigator.share({ url, title, text });
        return { ok:true, mode:'share' };
      }
    }catch{}

    try{
      await navigator.clipboard.writeText(url);
      return { ok:true, mode:'copy' };
    }catch{
      return { ok:false, mode:'none' };
    }
  }

  function findTrack(id){
    return TRACKS.find(t => String(t.id) === String(id || '')) || null;
  }

  function syncPlayingStateOnly(){
    const st = getPlayerState();
    const currentId = st && st.current ? String(st.current.id) : '';
    const paused = !!(st ? st.paused : true);

    root.querySelectorAll('[data-track-id]').forEach((card)=>{
      const id = String(card.getAttribute('data-track-id') || '');
      const playing = !!currentId && id === currentId && !paused;

      card.classList.toggle('isPlaying', playing);

      const btn = card.querySelector('[data-play-track]');
      if (btn) btn.textContent = playing ? STR.pauseTrack : STR.playTrack;

      const badge = card.querySelector('[data-play-badge]');
      if (badge) badge.textContent = playing ? '⏸' : '▶';
    });
  }

  async function handleRootClick(e){
    const shareBtn = e.target.closest('[data-share]');
    if (shareBtn && root.contains(shareBtn)){
      const id = shareBtn.getAttribute('data-share') || '';
      const track = findTrack(id);
      if (!track) return;

      const res = await shareOrCopy({
        url: shareUrlFor(track.id),
        title: track.title || 'iceBeats Track',
        text: track.artist ? `${track.artist} – ${track.title || ''}` : (track.title || '')
      });

      if (res.ok) {
        shareBtn.textContent = '✅';
        setTimeout(()=>{ shareBtn.textContent = '🔗'; }, 900);
      }
      return;
    }

const copyBtn = e.target.closest('[data-copy-link]');
if (copyBtn && root.contains(copyBtn)){
  const url = copyBtn.getAttribute('data-copy-link') || '';
  const defaultLabel = copyBtn.getAttribute('data-copy-label') || copyBtn.textContent || '';
  const copiedLabel = copyBtn.getAttribute('data-copied-label') || STR.copied;
  const failedLabel = copyBtn.getAttribute('data-failed-label') || STR.copyFailed;

  try{
    await navigator.clipboard.writeText(url);
    copyBtn.textContent = copiedLabel;
    copyBtn.disabled = true;
    setTimeout(()=>{
      copyBtn.textContent = defaultLabel;
      copyBtn.disabled = false;
    }, 1100);
  }catch{
    copyBtn.textContent = failedLabel;
    copyBtn.disabled = true;
    setTimeout(()=>{
      copyBtn.textContent = defaultLabel;
      copyBtn.disabled = false;
    }, 1400);
  }
  return;
}

    const playBtn = e.target.closest('[data-play-track]');
    if (playBtn && root.contains(playBtn)){
      const id = playBtn.getAttribute('data-play-track') || '';
      const track = findTrack(id);
      const player = getPlayer();

      if (!track || !player || typeof player.playTrack !== 'function') return;

      const st = getPlayerState();
      const samePlaying = !!(st && st.current && String(st.current.id) === String(track.id) && !st.paused);

      player.setQueue(TRACKS);

      if (samePlaying) {
        if (typeof player.stopToStart === 'function') player.stopToStart();
      } else {
        await player.playTrack(track, { queue: TRACKS });
      }

      syncPlayingStateOnly();
    }
  }

  function handlePlayerState(){
    syncPlayingStateOnly();
  }

  function cleanup(){
    root.removeEventListener('click', handleRootClick);
    window.removeEventListener('ij:player-state', handlePlayerState);
  }

  root.addEventListener('click', handleRootClick);
  window.addEventListener('ij:player-state', handlePlayerState);

  if (window.__ijRegisterPageCleanup) {
    window.__ijRegisterPageCleanup(cleanup);
  }

  const player = getPlayer();
  if (player && typeof player.setQueue === 'function') {
    player.setQueue(TRACKS);
  }

  syncPlayingStateOnly();
})();
</script>

<?php require __DIR__ . '/../_partials/footer.php'; ?>