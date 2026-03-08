<?php
// /public_html/api/index.php
declare(strict_types=1);

require __DIR__ . '/../_partials/i18n.php';
require __DIR__ . '/../_partials/meta.php';

$lang = ij_lang();

$pageTitleText = 'iceBeats.io – API';
$pageDescText = ($lang === 'en')
  ? 'API documentation and playground for library, top stats and playback logging on iceBeats.io.'
  : 'API-dokumentation och playground för bibliotek, topplistor och loggning av uppspelningar på iceBeats.io.';

$BASE = function_exists('ij_base_url')
  ? ij_base_url()
  : ((isset($_SERVER['HTTP_HOST']) ? 'https://' . $_SERVER['HTTP_HOST'] : 'https://icebeats.io'));

$STR = [
  'sv' => [
    'h1' => 'iceBeats Audio API',
    'intro' => 'Dokumentation och testverktyg för bibliotek, topplistor och loggning av uppspelningar.',
    'base' => 'Base URL',
    'quick' => 'Snabbtest (Playground)',
    'auth' => 'Autentisering',
    'authText' =>
      "Externa integratörer behöver API-nyckel för topplista och loggning.\n" .
      "Dina egna sidor på samma domän kan fortsätta utan nyckel (same-site via Origin/Referer/Host).",
    'authNeed' => 'Kräver API key:',
    'authNoNeed' => 'Publikt:',
    'need1' => 'GET /api/stats_top.php',
    'need2' => 'POST /api/track_play.php',
    'pub1' => 'GET /library.json',
    'pub2' => 'GET /api/health.php',
    'apiKey' => 'API key (valfri här)',
    'btnHealth' => 'Testa /api/health.php',
    'btnLibrary' => 'Hämta /library.json',
    'hintTop' => 'Hämtar topplista från stats_top.php',
    'hintLog' => 'Skickar en test-logg till track_play.php',
    'range' => 'Period',
    'week' => 'Vecka',
    'month' => 'Månad',
    'limit' => 'Limit',
    'client' => 'client_id (valfri)',
    'trackId' => 'track_id',
    'playedMs' => 'played_ms',
    'eventKey' => 'event_key',
    'title' => 'title (valfri)',
    'artist' => 'artist (valfri)',
    'trackUrl' => 'track_url (valfri)',
    'run' => 'Kör',
    'copy' => 'Kopiera',
    'response' => 'Svar',
    'endpoints' => 'Endpoints',
    'examples' => 'Exempel',
    'rate' => 'Rate limiting',
    'rateText' => 'Externa requests begränsas per API-nyckel eller IP. Dedupla gärna loggning per track/klient.',
    'cooldown' => 'Tips: logga bara om låten spelat minst 5 sekunder och max 1 gång/minut per track och klient.',
    'curlCopy' => 'Kopiera',
    'curlWithKey' => 'Med API key',
    'curlNoKey' => 'Utan API key',
    'overview' => 'Översikt',
    'method' => 'Metod',
    'path' => 'Path',
    'access' => 'Åtkomst',
    'desc' => 'Beskrivning',
    'public' => 'Publik',
    'protected' => 'Skyddad',
    'params' => 'Parametrar',
    'exampleResponse' => 'Exempelsvar',
    'field' => 'Fält',
    'required' => 'Krävs',
    'yes' => 'Ja',
    'no' => 'Nej',
    'libraryDesc' => 'Returnerar hela biblioteket med spår och metadata.',
    'healthDesc' => 'En enkel statuskontroll för API och bakomliggande delar.',
    'topDesc' => 'Returnerar mest spelade låtar för vald period.',
    'playDesc' => 'Loggar en uppspelning för statistik och sync.',
    'notes' => 'Noteringar',
    'sameSiteNote' => 'Same-site requests från icebeats.io och icejockey.app kan tillåtas utan API-nyckel beroende på endpoint.',
    'playgroundHelp' => 'Fyll i API-nyckel om du vill testa skyddade endpoints.',
  ],
  'en' => [
    'h1' => 'iceBeats Audio API',
    'intro' => 'Documentation and playground for library, top stats and playback logging.',
    'base' => 'Base URL',
    'quick' => 'Quick test (Playground)',
    'auth' => 'Authentication',
    'authText' =>
      "External integrators need an API key for top stats and play logging.\n" .
      "Your own pages on the same domain can keep working without a key (same-site via Origin/Referer/Host).",
    'authNeed' => 'API key required:',
    'authNoNeed' => 'Public:',
    'need1' => 'GET /api/stats_top.php',
    'need2' => 'POST /api/track_play.php',
    'pub1' => 'GET /library.json',
    'pub2' => 'GET /api/health.php',
    'apiKey' => 'API key (optional here)',
    'btnHealth' => 'Test /api/health.php',
    'btnLibrary' => 'Fetch /library.json',
    'hintTop' => 'Fetch top list from stats_top.php',
    'hintLog' => 'Post a test log to track_play.php',
    'range' => 'Range',
    'week' => 'Week',
    'month' => 'Month',
    'limit' => 'Limit',
    'client' => 'client_id (optional)',
    'trackId' => 'track_id',
    'playedMs' => 'played_ms',
    'eventKey' => 'event_key',
    'title' => 'title (optional)',
    'artist' => 'artist (optional)',
    'trackUrl' => 'track_url (optional)',
    'run' => 'Run',
    'copy' => 'Copy',
    'response' => 'Response',
    'endpoints' => 'Endpoints',
    'examples' => 'Examples',
    'rate' => 'Rate limiting',
    'rateText' => 'External requests are rate limited per API key or IP. Please dedupe play logging per track/client.',
    'cooldown' => 'Tip: log only if the track has played at least 5 seconds and at most once/minute per track and client.',
    'curlCopy' => 'Copy',
    'curlWithKey' => 'With API key',
    'curlNoKey' => 'Without API key',
    'overview' => 'Overview',
    'method' => 'Method',
    'path' => 'Path',
    'access' => 'Access',
    'desc' => 'Description',
    'public' => 'Public',
    'protected' => 'Protected',
    'params' => 'Parameters',
    'exampleResponse' => 'Example response',
    'field' => 'Field',
    'required' => 'Required',
    'yes' => 'Yes',
    'no' => 'No',
    'libraryDesc' => 'Returns the full library with tracks and metadata.',
    'healthDesc' => 'Simple health check for the API and related systems.',
    'topDesc' => 'Returns the most played tracks for the selected period.',
    'playDesc' => 'Logs a playback event for stats and sync.',
    'notes' => 'Notes',
    'sameSiteNote' => 'Same-site requests from icebeats.io and icejockey.app may be allowed without an API key depending on endpoint.',
    'playgroundHelp' => 'Fill in an API key if you want to test protected endpoints.',
  ]
];

$S = $STR[$lang] ?? $STR['sv'];

$meta = ij_build_meta([
  'title' => $pageTitleText,
  'description' => $pageDescText,
  'canonical' => ij_abs('/api/?lang=' . $lang),
  'image' => ij_abs('/share/og.jpg'),
  'type' => 'website',
  'extra' => [
    ['name' => 'robots', 'content' => 'index,follow'],
  ],
]);

$pageTitle = $meta['title'];
$pageHead = ij_render_meta($meta);

require __DIR__ . '/../_partials/header.php';

function h($s): string {
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
?>
<style>
  .wrap{max-width:1080px;margin:0 auto;padding:0 16px}
  .card{background:#0f172a;border:1px solid #1f2a44;border-radius:16px;padding:18px;margin:14px 0}
  h1{margin:0 0 6px;font-size:22px}
  h2{margin:0 0 10px;font-size:17px}
  h3{margin:0 0 8px;font-size:14px;color:#e2e8f0}
  .muted{color:#94a3b8;font-size:12px;white-space:pre-line;line-height:1.6}
  .row{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-top:10px}
  input,select{
    padding:10px 12px;border-radius:12px;border:1px solid #24324f;background:#0b1220;color:#e5e7eb
  }
  select{min-width:160px}
  input{min-width:180px}
  .btn{cursor:pointer;border:0;border-radius:12px;padding:10px 12px;background:#2563eb;color:white;font-weight:900}
  .btnGhost{cursor:pointer;border-radius:12px;padding:10px 12px;border:1px solid #334155;background:#0b1220;color:#e5e7eb;font-weight:900}
  .btnGhost:hover{border-color:#475569}
  code{background:#0b1220;border:1px solid #24324f;border-radius:8px;padding:2px 6px}
  pre{
    background:#0b1220;border:1px solid #24324f;border-radius:12px;
    padding:12px;overflow:auto;max-height:420px;margin:10px 0 0
  }
  .grid2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  .grid3{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
  @media (max-width: 960px){ .grid2,.grid3{grid-template-columns:1fr} }
  .pill{display:inline-flex;gap:8px;align-items:center;padding:6px 10px;border-radius:999px;border:1px solid #24324f;background:#0b1220;font-size:12px}
  .ok{color:#86efac;font-weight:900}
  .err{color:#fca5a5;font-weight:900}
  .kbd{font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace}
  .cols{display:flex;gap:18px;flex-wrap:wrap}
  .col{flex:1;min-width:260px}
  .endpoint{
    border:1px solid #24324f;
    border-radius:14px;
    padding:14px;
    background:#0b1220;
  }
  .endpointHead{
    display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:8px
  }
  .method{
    font-size:11px;
    font-weight:900;
    letter-spacing:.4px;
    padding:6px 8px;
    border-radius:999px;
    border:1px solid #334155;
    background:#08101f;
  }
  .tagPublic,.tagProtected{
    font-size:11px;
    font-weight:900;
    letter-spacing:.4px;
    padding:6px 8px;
    border-radius:999px;
    border:1px solid #334155;
  }
  .tagPublic{color:#86efac}
  .tagProtected{color:#fbbf24}
  .table{
    width:100%;
    border-collapse:collapse;
    margin-top:8px;
  }
  .table th,.table td{
    text-align:left;
    padding:8px;
    border-top:1px solid #1f2a44;
    vertical-align:top;
    font-size:12px;
  }
  .table th{color:#cbd5e1}
</style>

<div class="wrap">

  <div class="card">
    <h1><?= h($S['h1']) ?></h1>
    <div class="muted"><?= h($S['intro']) ?></div>
    <div class="row" style="margin-top:12px">
      <span class="pill"><b><?= h($S['base']) ?>:</b> <code><?= h($BASE) ?></code></span>
    </div>
  </div>

  <div class="card">
    <h2><?= h($S['auth']) ?></h2>
    <div class="muted"><?= h($S['authText']) ?></div>

    <div class="cols" style="margin-top:12px;">
      <div class="col">
        <div class="muted"><b><?= h($S['authNeed']) ?></b></div>
        <div class="muted">• <code><?= h($S['need1']) ?></code></div>
        <div class="muted">• <code><?= h($S['need2']) ?></code></div>
      </div>
      <div class="col">
        <div class="muted"><b><?= h($S['authNoNeed']) ?></b></div>
        <div class="muted">• <code><?= h($S['pub1']) ?></code></div>
        <div class="muted">• <code><?= h($S['pub2']) ?></code></div>
      </div>
    </div>

    <div class="row" style="margin-top:12px;">
      <span class="pill"><b>X-API-Key</b> <code class="kbd">X-API-Key: DIN_NYCKEL</code></span>
    </div>

    <div class="muted" style="margin-top:10px;"><?= h($S['sameSiteNote']) ?></div>
  </div>

  <div class="card">
    <h2><?= h($S['overview']) ?></h2>

    <table class="table">
      <thead>
        <tr>
          <th><?= h($S['method']) ?></th>
          <th><?= h($S['path']) ?></th>
          <th><?= h($S['access']) ?></th>
          <th><?= h($S['desc']) ?></th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><code>GET</code></td>
          <td><code>/library.json</code></td>
          <td><?= h($S['public']) ?></td>
          <td><?= h($S['libraryDesc']) ?></td>
        </tr>
        <tr>
          <td><code>GET</code></td>
          <td><code>/api/health.php</code></td>
          <td><?= h($S['public']) ?></td>
          <td><?= h($S['healthDesc']) ?></td>
        </tr>
        <tr>
          <td><code>GET</code></td>
          <td><code>/api/stats_top.php</code></td>
          <td><?= h($S['protected']) ?></td>
          <td><?= h($S['topDesc']) ?></td>
        </tr>
        <tr>
          <td><code>POST</code></td>
          <td><code>/api/track_play.php</code></td>
          <td><?= h($S['protected']) ?></td>
          <td><?= h($S['playDesc']) ?></td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="card">
    <h2><?= h($S['endpoints']) ?></h2>

    <div class="grid2">

      <div class="endpoint">
        <div class="endpointHead">
          <span class="method">GET</span>
          <code>/library.json</code>
          <span class="tagPublic"><?= h($S['public']) ?></span>
        </div>
        <div class="muted"><?= h($S['libraryDesc']) ?></div>

        <h3 style="margin-top:12px;"><?= h($S['exampleResponse']) ?></h3>
<pre class="kbd">{
  "baseUrl": "<?= h($BASE) ?>",
  "items": [
    {
      "id": "goal_horn_1",
      "title": "Goal Horn",
      "artist": "iceBeats",
      "genre": "Mål",
      "image": "/covers/goal-horn.webp",
      "url": "/tracks/goal-horn.mp3",
      "createdAt": 1740000000
    }
  ]
}</pre>
      </div>

      <div class="endpoint">
        <div class="endpointHead">
          <span class="method">GET</span>
          <code>/api/health.php</code>
          <span class="tagPublic"><?= h($S['public']) ?></span>
        </div>
        <div class="muted"><?= h($S['healthDesc']) ?></div>

        <h3 style="margin-top:12px;"><?= h($S['exampleResponse']) ?></h3>
<pre class="kbd">{
  "ok": true,
  "checks": {
    "library": { "ok": true, "updated": 1740000000 },
    "db": { "ok": true },
    "storage": { "ok": true }
  }
}</pre>
      </div>

      <div class="endpoint">
        <div class="endpointHead">
          <span class="method">GET</span>
          <code>/api/stats_top.php</code>
          <span class="tagProtected"><?= h($S['protected']) ?></span>
        </div>
        <div class="muted"><?= h($S['topDesc']) ?></div>

        <h3 style="margin-top:12px;"><?= h($S['params']) ?></h3>
        <table class="table">
          <thead>
            <tr>
              <th><?= h($S['field']) ?></th>
              <th><?= h($S['required']) ?></th>
              <th><?= h($S['desc']) ?></th>
            </tr>
          </thead>
          <tbody>
            <tr><td><code>range</code></td><td><?= h($S['no']) ?></td><td><code>week</code> eller <code>month</code></td></tr>
            <tr><td><code>limit</code></td><td><?= h($S['no']) ?></td><td>1–50</td></tr>
            <tr><td><code>client_id</code></td><td><?= h($S['no']) ?></td><td>Valfritt klient-id</td></tr>
          </tbody>
        </table>

        <h3 style="margin-top:12px;"><?= h($S['exampleResponse']) ?></h3>
<pre class="kbd">{
  "ok": true,
  "range": "week",
  "days": 7,
  "clientId": "arena_1",
  "updatedAt": "2026-03-06T08:00:00Z",
  "top": [
    {
      "trackId": "goal_horn_1",
      "plays": 42,
      "msTotal": 123000,
      "lastTs": 1741248000,
      "title": "Goal Horn",
      "artist": "iceBeats",
      "genre": "Mål",
      "image": "/covers/goal-horn.webp",
      "url": "/tracks/goal-horn.mp3"
    }
  ]
}</pre>
      </div>

      <div class="endpoint">
        <div class="endpointHead">
          <span class="method">POST</span>
          <code>/api/track_play.php</code>
          <span class="tagProtected"><?= h($S['protected']) ?></span>
        </div>
        <div class="muted"><?= h($S['playDesc']) ?></div>

        <h3 style="margin-top:12px;"><?= h($S['params']) ?></h3>
        <table class="table">
          <thead>
            <tr>
              <th><?= h($S['field']) ?></th>
              <th><?= h($S['required']) ?></th>
              <th><?= h($S['desc']) ?></th>
            </tr>
          </thead>
          <tbody>
            <tr><td><code>track_id</code></td><td><?= h($S['yes']) ?></td><td>Unikt spår-id</td></tr>
            <tr><td><code>client_id</code></td><td><?= h($S['no']) ?></td><td>Klient eller integration</td></tr>
            <tr><td><code>event_key</code></td><td><?= h($S['no']) ?></td><td>Typ av event, t.ex. goal eller library_play</td></tr>
            <tr><td><code>played_ms</code></td><td><?= h($S['no']) ?></td><td>Spelad tid i millisekunder</td></tr>
            <tr><td><code>title</code></td><td><?= h($S['no']) ?></td><td>Titel för spåret</td></tr>
            <tr><td><code>artist</code></td><td><?= h($S['no']) ?></td><td>Artistnamn</td></tr>
            <tr><td><code>track_url</code></td><td><?= h($S['no']) ?></td><td>Delbar URL till spårsidan</td></tr>
          </tbody>
        </table>

        <h3 style="margin-top:12px;"><?= h($S['exampleResponse']) ?></h3>
<pre class="kbd">{
  "ok": true,
  "trackId": "goal_horn_1",
  "logged": true
}</pre>
      </div>

    </div>
  </div>

  <div class="card">
    <h2><?= h($S['quick']) ?></h2>
    <div class="muted"><?= h($S['playgroundHelp']) ?></div>

    <div class="grid2">
      <div>
        <div class="row">
          <label class="muted"><?= h($S['apiKey']) ?></label>
          <input id="apiKey" placeholder="ijk_..." style="flex:1;min-width:240px" />
        </div>

        <div class="row" style="margin-top:12px;">
          <button class="btnGhost" id="btnHealth" type="button"><?= h($S['btnHealth']) ?></button>
          <button class="btnGhost" id="btnLibrary" type="button"><?= h($S['btnLibrary']) ?></button>
        </div>

        <hr style="border:0;border-top:1px solid #1f2a44;margin:14px 0">

        <div class="muted"><?= h($S['hintTop']) ?></div>
        <div class="row">
          <label class="muted"><?= h($S['range']) ?></label>
          <select id="topRange">
            <option value="week"><?= h($S['week']) ?></option>
            <option value="month"><?= h($S['month']) ?></option>
          </select>

          <label class="muted"><?= h($S['limit']) ?></label>
          <input id="topLimit" value="10" style="width:90px;min-width:90px" />

          <label class="muted"><?= h($S['client']) ?></label>
          <input id="topClient" placeholder="arena_1" />

          <button class="btn" id="btnTop" type="button"><?= h($S['run']) ?></button>
        </div>

        <hr style="border:0;border-top:1px solid #1f2a44;margin:14px 0">

        <div class="muted"><?= h($S['hintLog']) ?></div>
        <div class="row">
          <label class="muted"><?= h($S['trackId']) ?></label>
          <input id="logTrack" placeholder="goal_horn_1" />

          <label class="muted"><?= h($S['client']) ?></label>
          <input id="logClient" placeholder="web_api_playground" />

          <label class="muted"><?= h($S['eventKey']) ?></label>
          <input id="logEvent" value="api_playground" />

          <label class="muted"><?= h($S['playedMs']) ?></label>
          <input id="logMs" value="5000" style="width:100px;min-width:100px" />
        </div>

        <div class="row">
          <label class="muted"><?= h($S['title']) ?></label>
          <input id="logTitle" placeholder="Goal Horn" />

          <label class="muted"><?= h($S['artist']) ?></label>
          <input id="logArtist" placeholder="iceBeats" />

          <label class="muted"><?= h($S['trackUrl']) ?></label>
          <input id="logTrackUrl" placeholder="<?= h($BASE) ?>/track/?id=goal_horn_1" style="min-width:280px;flex:1" />
        </div>

        <div class="row">
          <button class="btn" id="btnLog" type="button"><?= h($S['run']) ?></button>
        </div>
      </div>

      <div>
        <div class="row" style="justify-content:space-between">
          <div class="muted"><b><?= h($S['response']) ?></b> <span id="badge"></span></div>
          <button class="btnGhost" id="btnCopyJson" type="button"><?= h($S['copy']) ?></button>
        </div>
        <pre id="out" class="kbd">{}</pre>
      </div>
    </div>
  </div>

  <div class="card">
    <h2><?= h($S['examples']) ?></h2>

    <div class="row" style="justify-content:space-between">
      <div class="muted"><b><?= h($S['curlWithKey']) ?></b></div>
      <button class="btnGhost" id="btnCopyCurlKey" type="button"><?= h($S['curlCopy']) ?></button>
    </div>

<pre id="curlKey" class="kbd"># top week (requires key)
curl -s "<?= h($BASE) ?>/api/stats_top.php?range=week&limit=10&client_id=arena_1" \
  -H "X-API-Key: DIN_NYCKEL"

# log play (requires key)
curl -s -X POST "<?= h($BASE) ?>/api/track_play.php" \
  -H "X-API-Key: DIN_NYCKEL" \
  -F "track_id=goal_horn_1" \
  -F "client_id=arena_1" \
  -F "event_key=goal_home" \
  -F "played_ms=5000" \
  -F "title=Goal Horn" \
  -F "artist=iceBeats" \
  -F "track_url=<?= h($BASE) ?>/track/?id=goal_horn_1"</pre>

    <div class="row" style="justify-content:space-between;margin-top:14px;">
      <div class="muted"><b><?= h($S['curlNoKey']) ?></b></div>
      <button class="btnGhost" id="btnCopyCurlPub" type="button"><?= h($S['curlCopy']) ?></button>
    </div>

<pre id="curlPub" class="kbd"># library (public)
curl -s "<?= h($BASE) ?>/library.json"

# health (public)
curl -s "<?= h($BASE) ?>/api/health.php"</pre>
  </div>

  <div class="card">
    <h2><?= h($S['rate']) ?></h2>
    <div class="muted"><?= h($S['rateText']) ?></div>
    <div class="muted" style="margin-top:6px;"><?= h($S['cooldown']) ?></div>
  </div>

</div>

<script>
const out = document.getElementById('out');
const badge = document.getElementById('badge');

function setBadge(ok){
  badge.innerHTML = ok
    ? ' <span class="ok">OK</span>'
    : ' <span class="err">ERR</span>';
}

function pretty(obj){
  try { return JSON.stringify(obj, null, 2); } catch { return String(obj); }
}

function setOut(obj, ok=true){
  out.textContent = pretty(obj);
  setBadge(ok);
}

function apiHeaders(){
  const k = (document.getElementById('apiKey').value || '').trim();
  const h = {};
  if (k) h['X-API-Key'] = k;
  return h;
}

async function fetchJson(url, opts={}){
  const res = await fetch(url, { cache:'no-store', ...opts });
  const txt = await res.text();
  let j = null;
  try { j = JSON.parse(txt); } catch { j = { raw: txt }; }
  return { ok: res.ok, status: res.status, json: j, raw: txt };
}

document.getElementById('btnHealth').onclick = async ()=>{
  setOut({ loading:true });
  try{
    const r = await fetchJson('/api/health.php');
    setOut({ status:r.status, ...r.json }, r.ok);
  }catch(e){
    setOut({ error: String(e) }, false);
  }
};

document.getElementById('btnLibrary').onclick = async ()=>{
  setOut({ loading:true });
  try{
    const r = await fetchJson('/library.json');
    setOut({ status:r.status, ...r.json }, r.ok);
  }catch(e){
    setOut({ error: String(e) }, false);
  }
};

document.getElementById('btnTop').onclick = async ()=>{
  setOut({ loading:true });
  try{
    const range = document.getElementById('topRange').value || 'week';
    const limit = Math.max(1, Math.min(50, Number(document.getElementById('topLimit').value || 10)));
    const cid = (document.getElementById('topClient').value || '').trim();

    const qs = new URLSearchParams();
    qs.set('range', range);
    qs.set('limit', String(limit));
    if (cid) qs.set('client_id', cid);

    const r = await fetchJson('/api/stats_top.php?' + qs.toString(), { headers: apiHeaders() });
    setOut({ status:r.status, ...r.json }, r.ok);
  }catch(e){
    setOut({ error: String(e) }, false);
  }
};

document.getElementById('btnLog').onclick = async ()=>{
  setOut({ loading:true });
  try{
    const trackId  = (document.getElementById('logTrack').value || '').trim() || 'api_playground_track';
    const clientId = (document.getElementById('logClient').value || '').trim() || 'web_api_playground';
    const eventKey = (document.getElementById('logEvent').value || '').trim() || 'api_playground';
    const playedMs = Math.max(0, Number(document.getElementById('logMs').value || 5000) | 0);
    const title    = (document.getElementById('logTitle').value || '').trim();
    const artist   = (document.getElementById('logArtist').value || '').trim();
    const trackUrl = (document.getElementById('logTrackUrl').value || '').trim();

    const fd = new FormData();
    fd.append('track_id', trackId);
    fd.append('client_id', clientId);
    fd.append('event_key', eventKey);
    fd.append('played_ms', String(playedMs));
    if (title) fd.append('title', title);
    if (artist) fd.append('artist', artist);
    if (trackUrl) fd.append('track_url', trackUrl);

    const res = await fetch('/api/track_play.php', {
      method:'POST',
      body: fd,
      cache:'no-store',
      headers: apiHeaders()
    });

    const txt = await res.text();
    let j = null;
    try { j = JSON.parse(txt); } catch { j = { raw: txt }; }
    setOut({ status: res.status, ...j }, res.ok);
  }catch(e){
    setOut({ error: String(e) }, false);
  }
};

document.getElementById('btnCopyJson').onclick = async ()=>{
  try{ await navigator.clipboard.writeText(out.textContent || ''); }catch{}
};

document.getElementById('btnCopyCurlKey').onclick = async ()=>{
  try{ await navigator.clipboard.writeText(document.getElementById('curlKey').textContent || ''); }catch{}
};

document.getElementById('btnCopyCurlPub').onclick = async ()=>{
  try{ await navigator.clipboard.writeText(document.getElementById('curlPub').textContent || ''); }catch{}
};

setOut({
  info: <?= json_encode($S['playgroundHelp'], JSON_UNESCAPED_UNICODE) ?>
}, true);
</script>

<?php require __DIR__ . '/../_partials/footer.php'; ?>