<?php
// /top/index.php
declare(strict_types=1);

require __DIR__ . '/../_partials/i18n.php';
require __DIR__ . '/../_partials/meta.php';

$lang = ij_lang();

// Sida-specifika texter (sv/en) för Topplistan
function tt(string $key, ?string $lang = null): string {
  $lang = $lang ?: ij_lang();

  static $d = [
    'sv' => [
      'page_title'   => 'iceBeats.io – Topplista',
      'h1'           => '🔥 Topplista',
      'sub'          => 'Data kommer från',
      'sub2'         => 'Kör din rollup-cron regelbundet för färsk statistik.',
      'week'         => 'Vecka',
      'month'        => 'Månad',
      'client_id'    => 'client_id (valfritt)',
      'load'         => '↻ Ladda',
      'loading'      => 'Laddar…',
      'no_data'      => 'Ingen data ännu.',
      'track'        => 'Låt',
      'plays'        => 'Spelningar',
      'actions'      => 'Åtgärder',
      'copy_link'    => '⧉',
      'share'        => '🔗',
      'shared'       => 'Delat ✅',
      'copied'       => 'Kopierat ✅',
      'copy_failed'  => 'Kunde inte kopiera (webbläsaren blockerade).',
      'updated'      => 'Uppdaterad',
      'range'        => 'Period',
      'failed_load'  => 'Kunde inte ladda',
      'failed_short' => 'Misslyckades att ladda.',
      'play'         => '▶ Spela',
      'pause'        => '⏸ Pausa',
    ],
    'en' => [
      'page_title'   => 'iceBeats.io – Top Tracks',
      'h1'           => '🔥 Top Tracks',
      'sub'          => 'Data comes from',
      'sub2'         => 'Run your rollup cron regularly for fresh stats.',
      'week'         => 'Week',
      'month'        => 'Month',
      'client_id'    => 'client_id (optional)',
      'load'         => '↻ Load',
      'loading'      => 'Loading…',
      'no_data'      => 'No data yet.',
      'track'        => 'Track',
      'plays'        => 'Plays',
      'actions'      => 'Actions',
      'copy_link'    => '⧉',
      'share'        => '🔗',
      'shared'       => 'Shared ✅',
      'copied'       => 'Copied ✅',
      'copy_failed'  => 'Could not copy (browser blocked).',
      'updated'      => 'Updated',
      'range'        => 'Range',
      'failed_load'  => 'Failed to load',
      'failed_short' => 'Failed to load.',
      'play'         => '▶ Play',
      'pause'        => '⏸ Pause',
    ],
  ];

  return $d[$lang][$key] ?? $d['sv'][$key] ?? $key;
}

$pageTitleText = tt('page_title', $lang);
$pageDescText = ($lang === 'en')
  ? 'See the most played tracks on iceBeats.io. Browse weekly and monthly top tracks.'
  : 'Se de mest spelade låtarna på iceBeats.io. Bläddra i topplistan för vecka och månad.';

$BASE = (isset($_SERVER['HTTP_HOST']) ? ('https://' . $_SERVER['HTTP_HOST']) : 'https://icebeats.io');

$meta = ij_build_meta([
  'title' => $pageTitleText,
  'description' => $pageDescText,
  'canonical' => ij_abs('/top/?lang=' . $lang),
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

<div class="ij-library" id="ijTopPage">
  <div class="ij-card">
    <h1 class="ij-h1"><?= htmlspecialchars(tt('h1', $lang), ENT_QUOTES, 'UTF-8') ?></h1>
    <div class="ij-muted">
      <?= htmlspecialchars(tt('sub', $lang), ENT_QUOTES, 'UTF-8') ?>
      <code class="ij-code">/api/stats_top.php</code>.
      <?= htmlspecialchars(tt('sub2', $lang), ENT_QUOTES, 'UTF-8') ?>
    </div>

    <div class="ij-row ij-library-filters">
      <button class="ij-btnGhost" id="btnWeek" type="button"><?= htmlspecialchars(tt('week', $lang), ENT_QUOTES, 'UTF-8') ?></button>
      <button class="ij-btnGhost" id="btnMonth" type="button"><?= htmlspecialchars(tt('month', $lang), ENT_QUOTES, 'UTF-8') ?></button>

      <div style="flex:1"></div>

      <label class="ij-muted" for="clientId" style="margin-right:6px"><?= htmlspecialchars(tt('client_id', $lang), ENT_QUOTES, 'UTF-8') ?></label>
      <input class="ij-input" id="clientId" placeholder="arena_1" style="width:180px" />

      <button class="ij-btn" id="btnLoad" type="button"><?= htmlspecialchars(tt('load', $lang), ENT_QUOTES, 'UTF-8') ?></button>
    </div>

    <div id="status" class="ij-toast ij-muted"></div>

    <div class="ij-tableWrap ij-library-tableWrap">
      <table class="ij-library-table">
        <thead>
          <tr>
            <th class="rank">#</th>
            <th class="coverCell"></th>
            <th><?= htmlspecialchars(tt('track', $lang), ENT_QUOTES, 'UTF-8') ?></th>
            <th class="num playsCol"><?= htmlspecialchars(tt('plays', $lang), ENT_QUOTES, 'UTF-8') ?></th>
            <th class="num actionsHead"><?= htmlspecialchars(tt('actions', $lang), ENT_QUOTES, 'UTF-8') ?></th>
          </tr>
        </thead>
        <tbody id="tbody">
          <tr><td colspan="5" class="ij-muted"><?= htmlspecialchars(tt('loading', $lang), ENT_QUOTES, 'UTF-8') ?></td></tr>
        </tbody>
      </table>
    </div>

    <div class="ij-library-mobile" id="mobileList"></div>
  </div>
</div>

<script>
(function(){
  const BASE = <?= json_encode($BASE) ?>;
  const LANG = <?= json_encode($lang, JSON_UNESCAPED_UNICODE) ?>;

  const I18N = <?= json_encode([
    'loading'      => tt('loading', $lang),
    'no_data'      => tt('no_data', $lang),
    'copy_link'    => tt('copy_link', $lang),
    'share'        => tt('share', $lang),
    'shared'       => tt('shared', $lang),
    'copied'       => tt('copied', $lang),
    'copy_failed'  => tt('copy_failed', $lang),
    'updated'      => tt('updated', $lang),
    'range'        => tt('range', $lang),
    'failed_load'  => tt('failed_load', $lang),
    'failed_short' => tt('failed_short', $lang),
    'play'         => tt('play', $lang),
    'pause'        => tt('pause', $lang),
  ], JSON_UNESCAPED_UNICODE) ?>;

  const root = document.getElementById('ijTopPage');

  const els = {
    btnWeek: document.getElementById('btnWeek'),
    btnMonth: document.getElementById('btnMonth'),
    btnLoad: document.getElementById('btnLoad'),
    clientId: document.getElementById('clientId'),
    tbody: document.getElementById('tbody'),
    mobileList: document.getElementById('mobileList'),
    status: document.getElementById('status'),
  };

  function setStatus(text, cls="ij-muted"){
    els.status.className = "ij-toast " + cls;
    els.status.textContent = text || "";
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

  function setRange(r){
    localStorage.setItem('ij_top_range', r);
    els.btnWeek.classList.toggle('active', r === 'week');
    els.btnMonth.classList.toggle('active', r === 'month');
    syncUrlState();
  }

  function getRange(){
    const r = (localStorage.getItem('ij_top_range') || 'week').toLowerCase();
    return (r === 'month') ? 'month' : 'week';
  }

  function getClientId(){
    return (els.clientId.value || '').trim();
  }

  function creatorUrlFor(name){
    const u = new URL('/artist/', location.origin);
    u.searchParams.set('name', String(name || ''));
    if (LANG === 'sv' || LANG === 'en') u.searchParams.set('lang', LANG);
    return u.toString();
  }

  function shareUrlFor(trackId){
    const u = new URL('/track/', location.origin);
    u.searchParams.set('id', String(trackId || ''));
    if (LANG === 'sv' || LANG === 'en') u.searchParams.set('lang', LANG);
    return u.toString();
  }

  function buildCurrentUrl(){
    const u = new URL('/top/', location.origin);
    if (LANG === 'sv' || LANG === 'en') u.searchParams.set('lang', LANG);

    const range = getRange();
    const clientId = getClientId();

    if (range) u.searchParams.set('range', range);
    if (clientId) u.searchParams.set('client_id', clientId);

    return u;
  }

  function syncUrlState(){
    try{
      const u = buildCurrentUrl();
      history.replaceState(history.state, '', u.toString());
    }catch{}
  }

  async function doShare(trackId, title, artist, btn = null){
  const url = shareUrlFor(trackId);
  try{
    if (navigator.share){
      await navigator.share({
        title: title || 'Icebeats Track',
        text: artist ? `${title} – ${artist}` : (title || ''),
        url
      });
      flashBtn(btn, '✅', 900);
      setStatus(I18N.shared, 'ij-ok');
      setTimeout(()=>setStatus('', 'ij-muted'), 1200);
      return true;
    }
  }catch{}
  try{
    await navigator.clipboard.writeText(url);
    flashBtn(btn, '✅', 900);
    setStatus(I18N.copied, 'ij-ok');
    setTimeout(()=>setStatus('', 'ij-muted'), 1200);
    return true;
  }catch{
    flashBtn(btn, '⚠', 1000);
    setStatus(I18N.copy_failed, 'ij-err');
    return false;
  }
}

  let topItems = [];

  function getPlayerState(){
    return (window.__ijPlayer && typeof window.__ijPlayer.getState === 'function')
      ? window.__ijPlayer.getState()
      : null;
  }

  function rowIsPlaying(id){
    const st = getPlayerState();
    return !!(st && st.current && String(st.current.id) === String(id) && !st.paused);
  }

  function resolvePlayUrl(r, baseUrlOverride){
    const u = r?.url ? String(r.url) : '';
    if (!u) return '';
    if (/^https?:\/\//i.test(u)) return u;

    if (baseUrlOverride){
      return String(baseUrlOverride).replace(/\/+$/, '') + '/' + u.replace(/^\/+/, '');
    }
    return BASE.replace(/\/+$/, '') + u;
  }

  let _libraryAllowIds = null;

  function isBlockedItem(it){
    return !!(it?.blocked || it?.disabled || it?.hidden || it?.removed);
  }

  async function loadLibraryAllowSet(){
    if (_libraryAllowIds) return _libraryAllowIds;

    const set = new Set();

    try{
      const res = await fetch('/library.json', { cache:'no-store' });
      if(!res.ok) throw new Error('HTTP ' + res.status);
      const json = await res.json();

      const baseUrl = json?.baseUrl || location.origin;
      const list = Array.isArray(json?.items) ? json.items : [];

      for (const it of list){
        if (isBlockedItem(it)) continue;

        if (it?.id) set.add(String(it.id));

        const playUrl = resolvePlayUrl({ url: it?.url }, baseUrl);
        if (playUrl) set.add(String(playUrl));
      }
    }catch(e){
      console.warn('Could not load /library.json for filtering:', e);
      _libraryAllowIds = null;
      return null;
    }

    _libraryAllowIds = set;
    return set;
  }

  function render(){
    if (!topItems.length){
      els.tbody.innerHTML = `<tr><td colspan="5" class="ij-muted">${esc(I18N.no_data)}</td></tr>`;
      els.mobileList.innerHTML = '';
      return;
    }

    els.tbody.innerHTML = topItems.map((r, i)=>{
      const rank = i + 1;
      const titleTxt = r.title || r.trackId || 'Track';
      const artistTxt = r.artist || '';
      const title = esc(titleTxt);
      const artist = esc(artistTxt);
      const img = r.image ? `<img src="${esc(r.image)}" alt="">` : '';
      const plays = Number(r.plays || 0);
      const playUrl = resolvePlayUrl(r);

      const id = String(r.trackId || playUrl || rank);
      const playing = rowIsPlaying(id);
      const trackLink = shareUrlFor(r.trackId || id);

      return `
        <tr class="trackRow${playing ? ' isPlaying' : ''}" data-id="${esc(id)}">
          <td class="rank">${rank}</td>
          <td class="coverCell"><div class="img">${img}</div></td>
          <td>
            <div class="trackMeta">
              <div class="metaText">
                <div class="title"><a class="ij-trackLink" href="${esc(trackLink)}">${title}</a></div>
                <div class="artist">
                  ${artistTxt ? `<a class="ij-trackLink" href="${esc(creatorUrlFor(artistTxt))}">${artist}</a>` : ''}
                </div>
              </div>
              <div class="eqWrap" aria-hidden="true">
                <span class="eqBar"></span><span class="eqBar"></span><span class="eqBar"></span><span class="eqBar"></span>
              </div>
            </div>
          </td>
          <td class="num playsCol">${plays.toLocaleString('sv-SE')}</td>
          <td class="num">
            <div class="actions">
              <button class="ij-btnGhost small hoverOnly" type="button"
                data-share="${esc(r.trackId || id)}"
                data-title="${esc(titleTxt)}"
                data-artist="${esc(artistTxt)}"
              >${esc(I18N.share)}</button>

              <button class="ij-btnGhost small hoverOnly" type="button" data-copy="${esc(trackLink)}">${esc(I18N.copy_link)}</button>

              ${playUrl ? `<button class="ij-btn small playBtn hoverOnly" type="button"
                data-play="${esc(r.trackId || id)}"
              >${playing ? '⏸' : '▶'}</button>` : ''}
            </div>
          </td>
        </tr>
      `;
    }).join('');

    els.mobileList.innerHTML = topItems.map((r, i)=>{
      const rank = i + 1;
      const titleTxt = r.title || r.trackId || 'Track';
      const artistTxt = r.artist || '';
      const title = esc(titleTxt);
      const artist = esc(artistTxt);
      const img = r.image ? `<img src="${esc(r.image)}" alt="">` : '';
      const plays = Number(r.plays || 0);
      const playUrl = resolvePlayUrl(r);
      const id = String(r.trackId || playUrl || rank);
      const playing = rowIsPlaying(id);
      const trackLink = shareUrlFor(r.trackId || id);

      return `
        <div class="ij-mTrack${playing ? ' isPlaying' : ''}" data-id="${esc(id)}">
          <div class="ij-mTop">
            <div class="ij-mRank">${rank}</div>
            <div class="img ij-mCover">${img ? img : ''}</div>
            <div class="ij-mMeta">
              <div class="title"><a class="ij-trackLink" href="${esc(trackLink)}">${title}</a></div>
              <div class="artist">
                ${artistTxt ? `<a class="ij-trackLink" href="${esc(creatorUrlFor(artistTxt))}">${artist}</a>` : ''}
              </div>
            </div>
            <div class="eqWrap" aria-hidden="true">
              <span class="eqBar"></span><span class="eqBar"></span><span class="eqBar"></span><span class="eqBar"></span>
            </div>
          </div>

          <div class="ij-mBottom">
            <div class="ij-mPlays">
              <div class="ij-muted">Plays</div>
              <div class="ij-mPlaysVal">${plays.toLocaleString('sv-SE')}</div>
            </div>

            <div class="ij-mActions">
              <button class="ij-btnGhost small" type="button"
                data-share="${esc(r.trackId || id)}"
                data-title="${esc(titleTxt)}"
                data-artist="${esc(artistTxt)}"
              >${esc(I18N.share)}</button>

              <button class="ij-btnGhost small" type="button" data-copy="${esc(trackLink)}">${esc(I18N.copy_link)}</button>

              ${playUrl ? `<button class="ij-btn small" type="button"
                data-play="${esc(r.trackId || id)}"
              >${playing ? '⏸' : '▶'}</button>` : ''}
            </div>
          </div>
        </div>
      `;
    }).join('');
  }

  function formatUpdatedAtMinus1h(v){
    if (!v) return '';
    let d = null;

    if (typeof v === 'string') {
      const t = Date.parse(v);
      if (!Number.isNaN(t)) d = new Date(t);
    }

    if (!d && (typeof v === 'number' || /^\d+$/.test(String(v)))) {
      const n = Number(v);
      if (Number.isFinite(n)) d = new Date(n < 1e12 ? n * 1000 : n);
    }

    if (!d) return String(v);

    d = new Date(d.getTime() - 60 * 60 * 1000);

    try {
      return d.toLocaleString('sv-SE', { hour12:false });
    } catch {
      return d.toISOString();
    }
  }

  async function loadTop(){
    const range = getRange();
    const qs = new URLSearchParams();
    qs.set('range', range);
    qs.set('limit', '50');

    const cid = getClientId();
    if (cid) qs.set('client_id', cid);

    const url = BASE.replace(/\/+$/, '') + '/api/stats_top.php?' + qs.toString();
    setStatus(I18N.loading, 'ij-muted');

    els.tbody.innerHTML = `<tr><td colspan="5" class="ij-muted">${esc(I18N.loading)}</td></tr>`;
    els.mobileList.innerHTML = '';

    syncUrlState();

    try{
      const res = await fetch(url, { cache:'no-store' });
      if(!res.ok) throw new Error('HTTP ' + res.status);

      const json = await res.json();
      const top = Array.isArray(json?.top) ? json.top : [];

      setStatus(`${I18N.updated}: ${formatUpdatedAtMinus1h(json?.updatedAt || '')} • ${I18N.range}: ${range}`, 'ij-muted');

      topItems = top.map(r => ({
        trackId: r.trackId || '',
        title: r.title || '',
        artist: r.artist || '',
        image: r.image || '',
        plays: Number(r.plays || 0),
        url: r.url || '',
        startMs: Number(r.startMs || 0) || 0,
        endMs: Number(r.endMs || 0) || 0,
        _resolvedUrl: resolvePlayUrl(r),
        id: r.trackId || resolvePlayUrl(r) || ''
      }));

      const allow = await loadLibraryAllowSet();
      if (allow && allow.size){
        topItems = topItems.filter(r => {
          const playUrl = resolvePlayUrl(r);
          const id = String(r.trackId || '');
          return allow.has(id) || (playUrl && allow.has(String(playUrl)));
        });
      }

      if (window.__ijPlayer && typeof window.__ijPlayer.setQueue === 'function') {
        window.__ijPlayer.setQueue(topItems);
      }

      render();
      syncPlayingStateOnly();
    }catch(e){
      setStatus(I18N.failed_load + ': ' + (e?.message || e), 'ij-err');
      topItems = [];
      render();
      els.tbody.innerHTML = `<tr><td colspan="5" class="ij-err">${esc(I18N.failed_short)}</td></tr>`;
    }
  }

  function findTopItemById(id){
    return topItems.find(r => String(r.trackId || r.id || '') === String(id || '')) || null;
  }

  async function handleRootClick(e){
    const btn = e.target.closest('button');
    if (!btn || !root.contains(btn)) return;

if (btn.hasAttribute('data-share')) {
  const tid = btn.getAttribute('data-share') || '';
  const t = btn.getAttribute('data-title') || '';
  const a = btn.getAttribute('data-artist') || '';
  await doShare(tid, t, a, btn);
  return;
}

if (btn.hasAttribute('data-copy')) {
  const copy = btn.getAttribute('data-copy') || '';
  try{
    await navigator.clipboard.writeText(copy);
    flashBtn(btn, '✅', 900);
    setStatus(I18N.copied, 'ij-ok');
    setTimeout(()=>setStatus('', 'ij-muted'), 1400);
  }catch{
    flashBtn(btn, '⚠', 1000);
    setStatus(I18N.copy_failed, 'ij-err');
  }
  return;
}

    if (btn.hasAttribute('data-play')) {
      const id  = btn.getAttribute('data-play') || '';
      const player = window.__ijPlayer;
      const it = findTopItemById(id);

      if (!player || !it || typeof player.playTrack !== 'function') {
        return;
      }

      const st = player.getState ? player.getState() : null;
      const samePlaying = !!(st && st.current && String(st.current.id) === String(it.trackId || it.id) && !st.paused);

      player.setQueue(topItems);

      if (samePlaying) {
        if (typeof player.stopToStart === 'function') player.stopToStart();
      } else {
        await player.playTrack(it, { queue: topItems });
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
    btn.textContent = playing ? '⏸' : '▶';

    const row = btn.closest('.trackRow, .ij-mTrack');
    if (row) row.classList.toggle('isPlaying', playing);
  });
}

function handlePlayerState(){
  syncPlayingStateOnly();
}

  function handleWeek(){
    setRange('week');
    loadTop();
  }

  function handleMonth(){
    setRange('month');
    loadTop();
  }

  function handleLoad(){
    loadTop();
  }

  function handleClientChange(){
    localStorage.setItem('ij_top_client', els.clientId.value.trim());
    syncUrlState();
  }

  function cleanup(){
    els.btnWeek.removeEventListener('click', handleWeek);
    els.btnMonth.removeEventListener('click', handleMonth);
    els.btnLoad.removeEventListener('click', handleLoad);
    els.clientId.removeEventListener('change', handleClientChange);
    root.removeEventListener('click', handleRootClick);
    window.removeEventListener('ij:player-state', handlePlayerState);
  }

  els.clientId.value = new URLSearchParams(location.search).get('client_id') || localStorage.getItem('ij_top_client') || '';

  els.btnWeek.addEventListener('click', handleWeek);
  els.btnMonth.addEventListener('click', handleMonth);
  els.btnLoad.addEventListener('click', handleLoad);
  els.clientId.addEventListener('change', handleClientChange);
  root.addEventListener('click', handleRootClick);
  window.addEventListener('ij:player-state', handlePlayerState);

  if (window.__ijRegisterPageCleanup) {
    window.__ijRegisterPageCleanup(cleanup);
  }

  const qsp = new URLSearchParams(location.search);
  const range0 = qsp.get('range');
  if (range0 === 'week' || range0 === 'month') {
    localStorage.setItem('ij_top_range', range0);
  }

  setRange(getRange());
  loadTop();
})();
</script>

<?php require __DIR__ . '/../_partials/footer.php'; ?>