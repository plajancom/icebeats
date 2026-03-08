<?php
// /library/index.php
declare(strict_types=1);

require __DIR__ . '/../_partials/i18n.php';
require __DIR__ . '/../_partials/meta.php';

$lang = ij_lang();

$pageTitleText = ($lang === 'en')
  ? 'iceBeats.io – Tracks'
  : 'iceBeats.io – Låtar';

$pageDescText = ($lang === 'en')
  ? 'Browse the full track library on iceBeats.io. Search by title, artist, genre and plays.'
  : 'Bläddra i hela låtbiblioteket på iceBeats.io. Sök på titel, artist, genre och spelningar.';

$GENRE_CANON = [
  "Arena / Jingle" => ["sv"=>"Arena / Jingle", "en"=>"Arena / Jingle"],
  "Organ / Charge" => ["sv"=>"Organ / Charge", "en"=>"Organ / Charge"],
  "Rock" => ["sv"=>"Rock", "en"=>"Rock"],
  "EDM" => ["sv"=>"EDM", "en"=>"EDM"],
  "Hip-hop" => ["sv"=>"Hip-hop", "en"=>"Hip-hop"],
  "Pop" => ["sv"=>"Pop", "en"=>"Pop"],
  "Mål" => ["sv"=>"Mål", "en"=>"Goal"],
  "Utvisning" => ["sv"=>"Utvisning", "en"=>"Penalty"],
  "Periodpaus" => ["sv"=>"Periodpaus", "en"=>"Intermission"],
  "Warmup" => ["sv"=>"Warmup", "en"=>"Warm-up"],
  "Timeout" => ["sv"=>"Timeout", "en"=>"Timeout"],
  "Övrigt" => ["sv"=>"Övrigt", "en"=>"Other"],
];

$GENRE_ALIASES = [
  "Arena / Jingle" => ["arena","jingle","jingl","arena / jingle"],
  "Organ / Charge" => ["organ","orgel","charge","organ / charge","hammond"],
  "Rock" => ["rock"],
  "EDM" => ["edm","electronic","dance","electro"],
  "Hip-hop" => ["hip-hop","hiphop","rap"],
  "Pop" => ["pop"],
  "Mål" => ["mål","mal","goal","goal horn","goalhorn"],
  "Utvisning" => ["utvisning","penalty","penalties"],
  "Periodpaus" => ["periodpaus","intermission","period break","break"],
  "Warmup" => ["warmup","warm-up","warm up","uppvärmning","uppvarmning"],
  "Timeout" => ["timeout","time out","time-out"],
  "Övrigt" => ["övrigt","ovrigt","other","misc","miscellaneous"],
];

function ij_canon_genre(string $input, array $GENRE_CANON, array $GENRE_ALIASES): string {
  $g = trim($input);
  if ($g === '') return 'Övrigt';
  if (isset($GENRE_CANON[$g])) return $g;

  $norm = strtolower(preg_replace('/\s+/', ' ', $g) ?? $g);
  $norm = trim($norm);

  foreach ($GENRE_ALIASES as $canon => $aliases) {
    if (strtolower($canon) === $norm) return $canon;
    foreach (($aliases ?? []) as $a) {
      if ($a === $norm) return $canon;
    }
  }

  return $g !== '' ? $g : 'Övrigt';
}

$prefillQ = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$prefillSort = isset($_GET['sort']) ? trim((string)$_GET['sort']) : '';
$prefillGenreRaw = isset($_GET['genre']) ? trim((string)$_GET['genre']) : '';
$prefillRange = isset($_GET['range']) ? trim((string)$_GET['range']) : '';

$prefillGenre = $prefillGenreRaw !== '' ? ij_canon_genre($prefillGenreRaw, $GENRE_CANON, $GENRE_ALIASES) : '';

if ($prefillSort !== 'az' && $prefillSort !== 'new' && $prefillSort !== 'plays') $prefillSort = 'az';
if ($prefillRange !== 'week' && $prefillRange !== 'month') $prefillRange = '';

$meta = ij_build_meta([
  'title' => $pageTitleText,
  'description' => $pageDescText,
  'canonical' => ij_abs('/library/?lang=' . $lang),
  'image' => ij_abs('/share/og.jpg'),
  'type' => 'website',
]);

$pageTitle = $meta['title'];
$pageHead = ij_render_meta($meta) . '
<style>
.ij-trackLink{ color:inherit; text-decoration:none; font-weight:950; }
.ij-trackLink:hover{ text-decoration: underline; }
</style>
';

require __DIR__ . '/../_partials/header.php';
?>

<div class="ij-library">
  <div class="ij-card">
    <h1 class="ij-h1" id="h1"><?= $lang === 'en' ? '🎵 Tracks' : '🎵 Låtar' ?></h1>

    <div class="ij-muted" id="sub">
      <?= $lang === 'en'
        ? 'Browse the full track library. Press play to use the global player.'
        : 'Bläddra i hela ljudbiblioteket. Tryck play för att använda den globala spelaren.' ?>
    </div>

    <div class="ij-row ij-library-filters" style="margin-top:12px;">
      <input
        class="ij-input"
        id="q"
        value="<?= htmlspecialchars($prefillQ, ENT_QUOTES, 'UTF-8') ?>"
        placeholder="<?= $lang === 'en' ? 'Search title or artist…' : 'Sök titel eller artist…' ?>"
      >

      <select class="ij-input" id="genre" data-prefill="<?= htmlspecialchars($prefillGenre, ENT_QUOTES, 'UTF-8') ?>">
        <option value=""><?= $lang === 'en' ? 'All genres' : 'Alla genrer' ?></option>
      </select>

      <select class="ij-input" id="sort">
        <option value="az" <?= $prefillSort === 'az' ? 'selected' : '' ?>><?= $lang === 'en' ? 'Sort: A–Z' : 'Sort: A–Ö' ?></option>
        <option value="new" <?= $prefillSort === 'new' ? 'selected' : '' ?>><?= $lang === 'en' ? 'Sort: Newest' : 'Sort: Nyast' ?></option>
        <option value="plays" <?= $prefillSort === 'plays' ? 'selected' : '' ?>><?= $lang === 'en' ? 'Sort: Plays' : 'Sort: Spelningar' ?></option>
      </select>

      <select class="ij-input" id="playsRange" title="<?= $lang === 'en' ? 'Plays range' : 'Spelningar period' ?>">
        <option value="week" <?= $prefillRange === 'week' ? 'selected' : '' ?>><?= $lang === 'en' ? 'Plays: Week' : 'Spelningar: Vecka' ?></option>
        <option value="month" <?= $prefillRange === 'month' ? 'selected' : '' ?>><?= $lang === 'en' ? 'Plays: Month' : 'Spelningar: Månad' ?></option>
      </select>

      <div style="flex:1"></div>

      <button class="ij-btnGhost" id="btnReload" type="button">↻ <?= $lang === 'en' ? 'Reload' : 'Ladda om' ?></button>
    </div>

    <div id="status" class="ij-muted ij-library-status" style="margin-top:10px;"></div>

    <div class="ij-library-mobile" id="mobileList" aria-label="<?= $lang === 'en' ? 'Track list' : 'Låtlista' ?>"></div>

    <div class="ij-library-tableWrap" style="margin-top:10px;">
      <table class="ij-library-table" aria-label="<?= $lang === 'en' ? 'Track table' : 'Låttabell' ?>">
        <thead>
          <tr>
            <th class="rank">#</th>
            <th class="coverCell"></th>
            <th><?= $lang === 'en' ? 'Track' : 'Låt' ?></th>
            <th class="num playsCol" id="playsHead"><?= $lang === 'en' ? 'Plays' : 'Spelningar' ?></th>
            <th class="num actionsHead"><?= $lang === 'en' ? 'Actions' : 'Åtgärder' ?></th>
          </tr>
        </thead>
        <tbody id="tbody">
          <tr><td colspan="5" class="ij-muted"><?= $lang === 'en' ? 'Loading…' : 'Laddar…' ?></td></tr>
        </tbody>
      </table>
    </div>

    <div class="ij-muted" id="playsNote" style="margin-top:10px;">
      <?= $lang === 'en'
        ? 'Note: plays are loaded from stats for the selected range.'
        : 'Obs: spelningar hämtas från statistik för vald period.' ?>
    </div>
  </div>
</div>

<script>
(function(){
  const lang = <?= json_encode($lang, JSON_UNESCAPED_UNICODE) ?>;
  const BASE = location.origin;
  const SITE_NAME = 'iceBeats';

  const GENRE_CANON = <?= json_encode($GENRE_CANON, JSON_UNESCAPED_UNICODE) ?>;
  const GENRE_ALIASES = <?= json_encode($GENRE_ALIASES, JSON_UNESCAPED_UNICODE) ?>;

  function canonicalGenre(input){
    const g0 = String(input || '').trim();
    if (!g0) return 'Övrigt';
    if (GENRE_CANON[g0]) return g0;

    const norm = g0.toLowerCase().replace(/\s+/g, ' ').trim();
    for (const [canon, aliases] of Object.entries(GENRE_ALIASES)){
      if (canon.toLowerCase() === norm) return canon;
      if ((aliases || []).some(a => a === norm)) return canon;
    }
    return g0 || 'Övrigt';
  }

  function displayGenre(canon){
    const c = canonicalGenre(canon);
    const l = (lang === 'en') ? 'en' : 'sv';
    return (GENRE_CANON[c]?.[l] || c);
  }

  const root = document.querySelector('.ij-library');

  const els = {
    q: document.getElementById('q'),
    genre: document.getElementById('genre'),
    sort: document.getElementById('sort'),
    playsRange: document.getElementById('playsRange'),
    tbody: document.getElementById('tbody'),
    mobileList: document.getElementById('mobileList'),
    status: document.getElementById('status'),
    btnReload: document.getElementById('btnReload'),
    playsHead: document.getElementById('playsHead'),
  };

  const STR = {
    sv: {
      loading: 'Laddar…',
      loaded: (n)=> `Laddat: ${n} spår`,
      noMatch: 'Inga spår matchar.',
      play: '▶ Spela',
      pause: '⏸ Pausa',
      copy: '⧉',
      share: '🔗',
      shared: 'Delat ✅',
      copied: 'Kopierad ✅',
      cantCopy: 'Kunde inte kopiera.',
      cantPlay: 'Kunde inte starta uppspelning.',
      allGenres: 'Alla genrer',
      playsWeek: 'Spelningar (vecka)',
      playsMonth: 'Spelningar (månad)',
      rangeWeek: 'Spelningar: Vecka',
      rangeMonth: 'Spelningar: Månad',
      playsShort: 'Spelningar',
      loadFailed: 'Kunde inte ladda biblioteket.',
    },
    en: {
      loading: 'Loading…',
      loaded: (n)=> `Loaded: ${n} tracks`,
      noMatch: 'No tracks match.',
      play: '▶ Play',
      pause: '⏸ Pause',
      copy: '⧉',
      share: '🔗',
      shared: 'Shared ✅',
      copied: 'Copied ✅',
      cantCopy: 'Could not copy.',
      cantPlay: 'Could not start playback.',
      allGenres: 'All genres',
      playsWeek: 'Plays (week)',
      playsMonth: 'Plays (month)',
      rangeWeek: 'Plays: Week',
      rangeMonth: 'Plays: Month',
      playsShort: 'Plays',
      loadFailed: 'Could not load library.',
    }
  };

  function tg(k, ...a){
    const v = STR[lang]?.[k];
    return (typeof v === 'function') ? v(...a) : (v ?? k);
  }

  function setStatus(text, cls='ij-muted'){
    els.status.className = cls + ' ij-library-status';
    els.status.textContent = text || '';
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

  function uniq(arr){
    return Array.from(new Set((arr || []).filter(Boolean)));
  }

  function trackKey(v){
    return String(v ?? '').trim();
  }

  function resolveUrl(baseUrl, u){
    if (!u) return '';
    if (/^https?:\/\//i.test(u)) return u;
    return (baseUrl || '').replace(/\/+$/, '') + '/' + String(u).replace(/^\/+/, '');
  }

  function resolveMaybeUrl(baseUrl, u){
    const s = String(u || '').trim();
    if (!s) return '';
    return resolveUrl(baseUrl, s);
  }

  function creatorUrlFor(name){
    const u = new URL('/artist/', BASE);
    u.searchParams.set('name', String(name || ''));
    if (lang === 'sv' || lang === 'en') u.searchParams.set('lang', lang);
    return u.toString();
  }

  function shareUrlFor(id){
    const u = new URL('/track/', BASE);
    u.searchParams.set('id', trackKey(id));
    if (lang === 'sv' || lang === 'en') u.searchParams.set('lang', lang);
    return u.toString();
  }

  function buildCurrentUrl(){
    const u = new URL('/library/', BASE);
    if (lang === 'sv' || lang === 'en') u.searchParams.set('lang', lang);

    const q = String(els.q.value || '').trim();
    const genre = String(els.genre.value || '').trim();
    const sort = String(els.sort.value || '').trim();
    const range = String(els.playsRange.value || '').trim();

    if (q) u.searchParams.set('q', q);
    if (genre) u.searchParams.set('genre', genre);
    if (sort && sort !== 'az') u.searchParams.set('sort', sort);
    if (range) u.searchParams.set('range', range);

    return u;
  }

  function syncUrlState(){
    try{
      const u = buildCurrentUrl();
      history.replaceState(history.state, '', u.toString());
    }catch{}
  }

  async function doShare(it, btn = null){
  const url = shareUrlFor(it?.id);
  try{
    if (navigator.share){
      await navigator.share({
        title: it?.title || `${SITE_NAME} Track`,
        text: it?.artist ? `${it.title} – ${it.artist}` : (it?.title || ''),
        url
      });
      flashBtn(btn, '✅', 900);
      setStatus(tg('shared'), 'ij-ok');
      setTimeout(()=> setStatus('', 'ij-muted'), 1200);
      return true;
    }
  }catch{}
  try{
    await navigator.clipboard.writeText(url);
    flashBtn(btn, '✅', 900);
    setStatus(tg('copied'), 'ij-ok');
    setTimeout(()=> setStatus('', 'ij-muted'), 1200);
    return true;
  }catch{
    flashBtn(btn, '⚠', 1000);
    setStatus(tg('cantCopy'), 'ij-err');
    return false;
  }
}

  let items = [];
  let filtered = [];
  let playsMap = Object.create(null);

  let playsRange = (new URLSearchParams(location.search).get('range') || localStorage.getItem('ij_library_plays_range') || 'week');
  if (playsRange !== 'month') playsRange = 'week';
  els.playsRange.value = playsRange;

  function updatePlaysRangeLabels(){
    const optWeek = els.playsRange.querySelector('option[value="week"]');
    const optMonth = els.playsRange.querySelector('option[value="month"]');
    if (optWeek) optWeek.textContent = tg('rangeWeek');
    if (optMonth) optMonth.textContent = tg('rangeMonth');
  }

  function updatePlaysHeader(){
    const r = els.playsRange.value || 'week';
    if (els.playsHead) els.playsHead.textContent = (r === 'month') ? tg('playsMonth') : tg('playsWeek');
    updatePlaysRangeLabels();
  }
  updatePlaysHeader();

  async function loadPlaysMap(){
    const range = (els.playsRange.value === 'month') ? 'month' : 'week';
    localStorage.setItem('ij_library_plays_range', range);

    try{
      const res = await fetch('/api/stats_plays.php?range=' + encodeURIComponent(range), { cache:'no-store' });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const json = await res.json();

      const map = (json && json.playsByTrackId && typeof json.playsByTrackId === 'object')
        ? json.playsByTrackId
        : {};

      playsMap = Object.create(null);
      for (const [k, v] of Object.entries(map)){
        playsMap[trackKey(k)] = Number(v) || 0;
      }
    }catch{
      playsMap = Object.create(null);
    }

    updatePlaysHeader();
  }

  function buildGenreOptions(){
    const genres = uniq(items.map(x => canonicalGenre(x.genre)));
    genres.sort((a,b)=>a.localeCompare(b, 'sv'));

    const cur = els.genre.value || '';
    els.genre.innerHTML =
      `<option value="">${esc(tg('allGenres'))}</option>` +
      genres.map(g => `<option value="${esc(g)}">${esc(displayGenre(g))}</option>`).join('');

    const prefill = (els.genre.getAttribute('data-prefill') || '').trim();
    if (prefill){
      els.genre.value = prefill;
      els.genre.removeAttribute('data-prefill');
    } else {
      els.genre.value = cur;
    }
  }

  function getPlaysFor(it){
    const v = playsMap[trackKey(it.id)];
    return (typeof v === 'number') ? v : null;
  }

  function getPlayerState(){
    return (window.__ijPlayer && typeof window.__ijPlayer.getState === 'function')
      ? window.__ijPlayer.getState()
      : null;
  }

  function rowIsPlaying(it){
    const st = getPlayerState();
    return !!(st && st.current && trackKey(st.current.id) === trackKey(it.id) && !st.paused);
  }

  function applyFilters(){
    const term = (els.q.value || '').trim().toLowerCase();
    const gSel = (els.genre.value || '').trim();
    const gCanon = gSel ? canonicalGenre(gSel) : '';

    filtered = items.filter(it => {
      const itG = canonicalGenre(it.genre);
      if (gCanon && itG !== gCanon) return false;
      if (!term) return true;
      const hay = `${it.title || ''} ${it.artist || ''}`.toLowerCase();
      return hay.includes(term);
    });

    const s = els.sort.value || 'az';
    if (s === 'new'){
      filtered.sort((a,b)=> Number(b.createdAt || 0) - Number(a.createdAt || 0));
    } else if (s === 'plays'){
      filtered.sort((a,b)=>{
        const ap = getPlaysFor(a);
        const bp = getPlaysFor(b);
        const av = (ap == null) ? -1 : ap;
        const bv = (bp == null) ? -1 : bp;
        if (bv !== av) return bv - av;
        return String(a.title || '').localeCompare(String(b.title || ''), 'sv');
      });
    } else {
      filtered.sort((a,b)=> String(a.title || '').localeCompare(String(b.title || ''), 'sv'));
    }

    syncUrlState();
    render();
    syncPlayingStateOnly();
  }

  function renderTable(){
    if (!filtered.length){
      els.tbody.innerHTML = `<tr><td colspan="5" class="ij-muted">${esc(tg('noMatch'))}</td></tr>`;
      return;
    }

    els.tbody.innerHTML = filtered.map((it, idx)=>{
      const img = it.image ? `<img src="${esc(it.image)}" alt="">` : '';
      const playing = rowIsPlaying(it);
      const playLabel = playing ? tg('pause') : tg('play');
      const plays = getPlaysFor(it);
      const playsTxt = (plays == null) ? '—' : String(plays);
      const trackLink = shareUrlFor(it.id);

      return `
        <tr class="trackRow${playing ? ' isPlaying' : ''}">
          <td class="rank">${idx+1}</td>
          <td class="coverCell"><div class="img">${img}</div></td>
          <td>
            <div class="trackMeta">
              <div class="metaText">
                <div class="title"><a class="ij-trackLink" href="${esc(trackLink)}">${esc(it.title || '—')}</a></div>
                <div class="artist">
                  ${it.artist ? `<a class="ij-trackLink" href="${esc(creatorUrlFor(it.artist))}">${esc(it.artist)}</a>` : ''}
                  ${it.genre ? `<span class="tag">${esc(displayGenre(it.genre))}</span>` : ''}
                </div>
              </div>
              <div class="eqWrap" aria-hidden="true">
                <span class="eqBar"></span><span class="eqBar"></span><span class="eqBar"></span><span class="eqBar"></span>
              </div>
            </div>
          </td>
          <td class="num playsCol">${esc(playsTxt)}</td>
          <td class="num">
            <div class="actions">
              <button class="ij-btnGhost small hoverOnly" type="button" data-share="${esc(trackKey(it.id))}">${esc(tg('share'))}</button>
              <button class="ij-btnGhost small hoverOnly" type="button" data-copy="${esc(trackLink)}">${esc(tg('copy'))}</button>
              <button
                class="ij-btn small playBtn hoverOnly"
                type="button"
                data-play="${esc(trackKey(it.id))}"
              >${esc(playLabel)}</button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  }

  function renderMobile(){
    if (!filtered.length){
      els.mobileList.innerHTML = `<div class="ij-muted">${esc(tg('noMatch'))}</div>`;
      return;
    }

    const rangeLabel = (els.playsRange.value === 'month') ? tg('playsMonth') : tg('playsWeek');

    els.mobileList.innerHTML = filtered.map((it, idx)=>{
      const img = it.image ? `<img src="${esc(it.image)}" alt="">` : '';
      const playing = rowIsPlaying(it);
      const playLabel = playing ? tg('pause') : tg('play');
      const plays = getPlaysFor(it);
      const playsTxt = (plays == null) ? '—' : String(plays);
      const trackLink = shareUrlFor(it.id);

      return `
        <div class="ij-mTrack ${playing ? 'isPlaying' : ''}">
          <div class="ij-mTop">
            <div class="ij-mRank">${idx+1}</div>
            <div class="ij-mCover"><div class="img">${img}</div></div>
            <div class="ij-mMeta">
              <div class="title"><a class="ij-trackLink" href="${esc(trackLink)}">${esc(it.title || '—')}</a></div>
              <div class="artist">
                ${it.artist ? `<a class="ij-trackLink" href="${esc(creatorUrlFor(it.artist))}">${esc(it.artist)}</a>` : ''}
                ${it.genre ? `<span class="tag">${esc(displayGenre(it.genre))}</span>` : ''}
              </div>
            </div>
          </div>

          <div class="ij-mBottom">
            <div class="ij-mPlays" title="${esc(rangeLabel)}">
              <span class="ij-muted">${esc(tg('playsShort'))}:</span>
              <span class="ij-mPlaysVal">${esc(playsTxt)}</span>
            </div>

            <div class="ij-mActions">
              <button class="ij-btnGhost small" type="button" data-share="${esc(trackKey(it.id))}">${esc(tg('share'))}</button>
              <button class="ij-btnGhost small" type="button" data-copy="${esc(trackLink)}">${esc(tg('copy'))}</button>
              <button
                class="ij-btn small"
                type="button"
                data-play="${esc(trackKey(it.id))}"
              >${esc(playLabel)}</button>
            </div>
          </div>
        </div>
      `;
    }).join('');
  }

  function render(){
    renderMobile();
    renderTable();
  }

  async function load(){
    setStatus(tg('loading'), 'ij-muted');
    els.tbody.innerHTML = `<tr><td colspan="5" class="ij-muted">${esc(tg('loading'))}</td></tr>`;
    els.mobileList.innerHTML = `<div class="ij-muted">${esc(tg('loading'))}</div>`;

    try{
      const res = await fetch('/library.json', { cache:'no-store' });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const json = await res.json();

      const baseUrl = json.baseUrl || BASE;
      const list = Array.isArray(json.items) ? json.items : [];

      items = list.map(it => {
        const url = resolveUrl(baseUrl, it.url);
        const image = resolveMaybeUrl(baseUrl, it.image || '');
        return {
          id: trackKey(it.id || url),
          title: it.title || it.name || '',
          artist: it.artist || '',
          genre: canonicalGenre(it.genre || 'Övrigt'),
          image,
          createdAt: Number(it.createdAt || it.created || 0) || 0,
          startMs: Number(it.startMs || 0) || 0,
          endMs: Number(it.endMs || 0) || 0,
          _resolvedUrl: url,
        };
      });

      buildGenreOptions();
      await loadPlaysMap();

      if (window.__ijPlayer && typeof window.__ijPlayer.setQueue === 'function') {
        window.__ijPlayer.setQueue(items);
      }

      applyFilters();
      setStatus(tg('loaded', items.length), 'ij-muted');
    }catch(e){
      setStatus(tg('loadFailed') + ' ' + String(e?.message || e || ''), 'ij-err');
      els.tbody.innerHTML = `<tr><td colspan="5" class="ij-err">${esc(tg('loadFailed'))}</td></tr>`;
      els.mobileList.innerHTML = `<div class="ij-err">${esc(tg('loadFailed'))}</div>`;
    }
  }

  function handleFilterInput(){
    applyFilters();
  }

  async function handleRangeChange(){
    await loadPlaysMap();
    applyFilters();
  }

  function findItemById(id){
    const key = trackKey(id);
    return (items || []).find(x => trackKey(x.id) === key) || null;
  }

  async function handleRootClick(e){
    const btn = e.target.closest('button');
    if (!btn || !root.contains(btn)) return;

if (btn.hasAttribute('data-share')){
  const shareId = btn.getAttribute('data-share') || '';
  const it = findItemById(shareId);
  if (it) await doShare(it, btn);
  return;
}

if (btn.hasAttribute('data-copy')){
  const copy = btn.getAttribute('data-copy') || '';
  try{
    await navigator.clipboard.writeText(copy);
    flashBtn(btn, '✅', 900);
    setStatus(tg('copied'), 'ij-ok');
    setTimeout(()=> setStatus('', 'ij-muted'), 1200);
  }catch{
    flashBtn(btn, '⚠', 1000);
    setStatus(tg('cantCopy'), 'ij-err');
  }
  return;
}

    if (btn.hasAttribute('data-play')){
      const playId = btn.getAttribute('data-play') || '';
      const it = findItemById(playId);
      const player = window.__ijPlayer;

      if (!it || !player || typeof player.playTrack !== 'function') {
        setStatus(tg('cantPlay'), 'ij-err');
        return;
      }

      const st = player.getState ? player.getState() : null;
      const samePlaying = !!(st && st.current && trackKey(st.current.id) === trackKey(it.id) && !st.paused);

      player.setQueue(filtered.length ? filtered : items);

      if (samePlaying) {
        if (typeof player.stopToStart === 'function') player.stopToStart();
      } else {
        await player.playTrack(it, { queue: filtered.length ? filtered : items });
      }

      render();
      return;
    }
  }

function syncPlayingStateOnly(){
  const st = getPlayerState();
  const currentId = st && st.current ? String(st.current.id) : '';
  const paused = !!(st ? st.paused : true);

  root.querySelectorAll('[data-play]').forEach((btn)=>{
    const id = String(btn.getAttribute('data-play') || '');
    const playing = !!currentId && id === currentId && !paused;
    btn.textContent = playing ? tg('pause') : tg('play');

    const row = btn.closest('.trackRow, .ij-mTrack');
    if (row) row.classList.toggle('isPlaying', playing);
  });
}

function handlePlayerState(){
  syncPlayingStateOnly();
}

  function cleanup(){
    els.q.removeEventListener('input', handleFilterInput);
    els.genre.removeEventListener('change', handleFilterInput);
    els.sort.removeEventListener('change', handleFilterInput);
    els.btnReload.removeEventListener('click', load);
    els.playsRange.removeEventListener('change', handleRangeChange);
    root.removeEventListener('click', handleRootClick);
    window.removeEventListener('ij:player-state', handlePlayerState);
  }

  els.q.addEventListener('input', handleFilterInput);
  els.genre.addEventListener('change', handleFilterInput);
  els.sort.addEventListener('change', handleFilterInput);
  els.btnReload.addEventListener('click', load);
  els.playsRange.addEventListener('change', handleRangeChange);
  root.addEventListener('click', handleRootClick);
  window.addEventListener('ij:player-state', handlePlayerState);

  if (window.__ijRegisterPageCleanup) {
    window.__ijRegisterPageCleanup(cleanup);
  }

  load();
})();
</script>

<?php require __DIR__ . '/../_partials/footer.php'; ?>