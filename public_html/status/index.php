<?php
declare(strict_types=1);

require __DIR__ . '/../_partials/i18n.php';
require __DIR__ . '/../_partials/meta.php';

$lang = ij_lang();

$pageTitleText = ($lang === 'en')
  ? 'iceBeats.io – System Status'
  : 'iceBeats.io – Systemstatus';

$pageDescText = ($lang === 'en')
  ? 'System status and health checks for the iceBeats.io audio service.'
  : 'Systemstatus och hälsokontroller för iceBeats.io ljudtjänst.';

$meta = ij_build_meta([
  'title' => $pageTitleText,
  'description' => $pageDescText,
  'canonical' => ij_abs('/status/?lang=' . $lang),
  'image' => ij_abs('/share/og.jpg'),
  'type' => 'website',
  'extra' => [
    ['name' => 'robots', 'content' => 'noindex,nofollow'],
  ],
]);

$pageTitle = $meta['title'];
$pageHead = ij_render_meta($meta);

require __DIR__ . '/../_partials/header.php';
?>

<style>
.wrap{max-width:1120px;margin:0 auto;padding:0 16px}
.card{
  background:#0f172a;
  border:1px solid #1f2a44;
  border-radius:16px;
  padding:18px;
  margin:14px 0;
}
.heroTitle{margin:0 0 6px;font-size:22px}
.muted{color:#94a3b8;font-size:12px;line-height:1.6}
.ok{color:#86efac;font-weight:800}
.err{color:#fca5a5;font-weight:800}
.warn{color:#fbbf24;font-weight:800}

.grid{
  display:grid;
  grid-template-columns:repeat(4,1fr);
  gap:12px;
}
@media (max-width: 980px){
  .grid{grid-template-columns:repeat(2,1fr)}
}
@media (max-width: 620px){
  .grid{grid-template-columns:1fr}
}

.stat{
  background:#0b1220;
  border:1px solid #24324f;
  border-radius:14px;
  padding:14px;
}
.statLabel{
  font-size:11px;
  color:#94a3b8;
  text-transform:uppercase;
  letter-spacing:.5px;
  margin-bottom:8px;
}
.statValue{
  font-size:22px;
  font-weight:950;
  color:#e5e7eb;
  line-height:1.15;
}
.statSub{
  margin-top:6px;
  color:#94a3b8;
  font-size:12px;
}
.statBar{
  margin-top:10px;
  height:8px;
  border-radius:999px;
  background:#1f2a44;
  overflow:hidden;
}
.statBarFill{
  height:100%;
  border-radius:999px;
  background:linear-gradient(90deg,#60a5fa,#86efac);
  transition:width .7s ease;
}

.sectionTitle{
  margin:0 0 10px;
  font-size:16px;
  color:#e5e7eb;
}

.checkList{
  display:grid;
  grid-template-columns:repeat(2,1fr);
  gap:12px;
}
@media (max-width: 820px){
  .checkList{grid-template-columns:1fr}
}

.checkCard{
  background:#0b1220;
  border:1px solid #24324f;
  border-radius:14px;
  padding:14px;
  position:relative;
  overflow:hidden;
}
.checkCard::after{
  content:"";
  position:absolute;
  right:-30px;
  bottom:-30px;
  width:110px;
  height:110px;
  background:radial-gradient(circle, rgba(96,165,250,.10), transparent 70%);
  pointer-events:none;
}
.checkHead{
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:10px;
  margin-bottom:8px;
}
.checkName{
  font-weight:900;
  color:#e5e7eb;
}
.badge{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-width:68px;
  padding:6px 10px;
  border-radius:999px;
  font-size:11px;
  font-weight:900;
  letter-spacing:.4px;
  border:1px solid #334155;
  background:#08101f;
}
.badge.ok{color:#86efac}
.badge.err{color:#fca5a5}

.kv{
  display:grid;
  grid-template-columns:150px 1fr;
  gap:8px 12px;
  font-size:12px;
}
@media (max-width: 560px){
  .kv{grid-template-columns:1fr}
}
.kvKey{color:#94a3b8}
.kvVal{color:#e5e7eb}

.mono{
  font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}

.heroTop{
  display:grid;
  grid-template-columns:200px 1fr;
  gap:14px;
  margin-top:16px;
}
@media (max-width:820px){
  .heroTop{grid-template-columns:1fr}
}

.ringWrap{
  width:150px;
  height:150px;
  margin:auto;
  position:relative;
}
.ringSvg{
  width:150px;
  height:150px;
  transform:rotate(-90deg);
}
.ringBg{
  stroke:#1f2a44;
  stroke-width:10;
  fill:none;
}
.ringFg{
  stroke:#60a5fa;
  stroke-width:10;
  fill:none;
  stroke-linecap:round;
  stroke-dasharray:439.82;
  stroke-dashoffset:439.82;
  transition:stroke-dashoffset .6s ease, stroke .3s ease;
}
.ringCenter{
  position:absolute;
  inset:0;
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
}
.ringScore{
  font-size:32px;
  font-weight:900;
}

.liveBadge{
  display:inline-flex;
  gap:8px;
  align-items:center;
  margin-top:10px;
  font-size:12px;
  padding:6px 10px;
  border-radius:999px;
  border:1px solid #334155;
  background:#0b1220;
}
.liveDot{
  width:8px;
  height:8px;
  background:#86efac;
  border-radius:999px;
  animation:pulse 1.5s infinite;
}
@keyframes pulse{
  0%{opacity:1}
  50%{opacity:.3}
  100%{opacity:1}
}

.chartGrid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:12px;
}
@media (max-width: 900px){
  .chartGrid{grid-template-columns:1fr}
}

.chartCard{
  background:#0b1220;
  border:1px solid #24324f;
  border-radius:14px;
  padding:14px;
}
.chartHead{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
  margin-bottom:10px;
}
.chartTitle{
  font-size:14px;
  font-weight:900;
  color:#e5e7eb;
}
.chartMeta{
  font-size:11px;
  color:#94a3b8;
}
.chartWrap{
  width:100%;
  overflow:hidden;
}
.chartSvg{
  width:100%;
  height:220px;
  display:block;
}
.axisText{
  fill:#94a3b8;
  font-size:11px;
}
.lineLatency{
  fill:none;
  stroke:#60a5fa;
  stroke-width:2.5;
}
.lineTracks{
  fill:none;
  stroke:#86efac;
  stroke-width:2.5;
}
.lineHealth{
  fill:none;
  stroke:#fbbf24;
  stroke-width:2.5;
}
.areaLatency{
  fill:rgba(96,165,250,.12);
}
.areaTracks{
  fill:rgba(134,239,172,.10);
}
.areaHealth{
  fill:rgba(251,191,36,.10);
}
.dotLatency{ fill:#60a5fa; }
.dotTracks{ fill:#86efac; }
.dotHealth{ fill:#fbbf24; }

.timeline{
  display:grid;
  grid-template-columns:repeat(24, 1fr);
  gap:4px;
  margin-top:12px;
}
.timelineCell{
  height:18px;
  border-radius:6px;
  background:#1f2a44;
  border:1px solid #24324f;
}
.timelineCell.ok{ background:rgba(134,239,172,.18); border-color:rgba(134,239,172,.25); }
.timelineCell.err{ background:rgba(252,165,165,.18); border-color:rgba(252,165,165,.25); }

.subtleNote{
  margin-top:10px;
  color:#94a3b8;
  font-size:11px;
}

.loadingPulse{
  opacity:.8;
  animation: pulseFade 1.2s ease-in-out infinite;
}
@keyframes pulseFade{
  0%,100%{opacity:.45}
  50%{opacity:1}
}

.chartStage{
  position:relative;
}
.chartTooltip{
  position:absolute;
  display:none;
  min-width:140px;
  max-width:220px;
  padding:10px 12px;
  border-radius:12px;
  border:1px solid #334155;
  background:rgba(11,18,32,.96);
  color:#e5e7eb;
  font-size:12px;
  line-height:1.45;
  box-shadow:0 10px 30px rgba(0,0,0,.28);
  pointer-events:none;
  z-index:20;
  transform:translate(-50%, -110%);
  backdrop-filter:blur(10px);
  -webkit-backdrop-filter:blur(10px);
}
.chartTooltip.show{
  display:block;
}
.chartTooltip .ttTitle{
  font-weight:900;
  margin-bottom:4px;
}
.chartTooltip .ttRow{
  display:flex;
  justify-content:space-between;
  gap:10px;
}
.chartTooltip .ttKey{
  color:#94a3b8;
}
.chartTooltip .ttVal{
  color:#e5e7eb;
  font-weight:700;
}
.chartHoverLine{
  stroke:rgba(148,163,184,.35);
  stroke-width:1;
  stroke-dasharray:4 4;
}
.chartPoint{
  cursor:pointer;
}
.chartGridLine{
  stroke:#1f2a44;
  stroke-width:1;
}
.chartAxisLine{
  stroke:#24324f;
  stroke-width:1;
}
.axisTickText{
  fill:#94a3b8;
  font-size:10px;
}
.axisTickBold{
  fill:#cbd5e1;
  font-size:10px;
  font-weight:800;
}

.incidentBanner{
  display:none;
  margin-top:14px;
  padding:14px 16px;
  border-radius:14px;
  border:1px solid rgba(252,165,165,.25);
  background:linear-gradient(180deg, rgba(127,29,29,.28), rgba(69,10,10,.22));
  color:#fecaca;
  box-shadow: inset 0 0 0 1px rgba(252,165,165,.05);
}
.incidentBanner.show{
  display:block;
  animation: incidentIn .28s ease;
}
@keyframes incidentIn{
  from{opacity:0; transform:translateY(-4px)}
  to{opacity:1; transform:translateY(0)}
}
.incidentTitle{
  font-weight:950;
  font-size:14px;
  margin-bottom:6px;
  letter-spacing:.2px;
}
.incidentText{
  font-size:12px;
  line-height:1.6;
  color:#fecaca;
}
.incidentList{
  margin-top:8px;
  display:flex;
  gap:8px;
  flex-wrap:wrap;
}
.incidentChip{
  display:inline-flex;
  align-items:center;
  gap:6px;
  padding:6px 10px;
  border-radius:999px;
  border:1px solid rgba(252,165,165,.22);
  background:rgba(127,29,29,.22);
  color:#fee2e2;
  font-size:11px;
  font-weight:900;
}

.incidentHistory{
  margin-top:14px;
  display:grid;
  gap:10px;
}
.incidentRow{
  background:#0b1220;
  border:1px solid #24324f;
  border-radius:14px;
  padding:12px 14px;
}
.incidentRowHead{
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:10px;
  flex-wrap:wrap;
}
.incidentRowTitle{
  font-weight:900;
  color:#e5e7eb;
}
.incidentRowMeta{
  color:#94a3b8;
  font-size:12px;
}
.incidentState{
  display:inline-flex;
  align-items:center;
  gap:6px;
  padding:4px 8px;
  border-radius:999px;
  border:1px solid #334155;
  font-size:11px;
  font-weight:900;
}
.incidentState.open{
  color:#fecaca;
  border-color:rgba(252,165,165,.25);
  background:rgba(127,29,29,.18);
}
.incidentState.closed{
  color:#cbd5e1;
  background:#08101f;
}

.uptimeHero{
  margin-top:10px;
  display:flex;
  gap:10px;
  flex-wrap:wrap;
  align-items:center;
}
.uptimePill{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:8px 12px;
  border-radius:999px;
  border:1px solid #334155;
  background:#0b1220;
  color:#e5e7eb;
  font-size:12px;
  font-weight:800;
}

.sevBadge{
  display:inline-flex;
  align-items:center;
  gap:6px;
  padding:4px 8px;
  border-radius:999px;
  font-size:11px;
  font-weight:900;
  border:1px solid #334155;
}
.sev-warning{
  color:#fbbf24;
  border-color:rgba(251,191,36,.25);
  background:rgba(120,53,15,.18);
}
.sev-major{
  color:#fb923c;
  border-color:rgba(251,146,60,.25);
  background:rgba(124,45,18,.20);
}
.sev-critical{
  color:#fca5a5;
  border-color:rgba(252,165,165,.25);
  background:rgba(127,29,29,.22);
}

.heroIncidentSummary{
  margin-top:12px;
  display:none;
  gap:10px;
  flex-wrap:wrap;
  align-items:center;
}
.heroIncidentSummary.show{
  display:flex;
}
.heroIncidentPill{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:8px 12px;
  border-radius:999px;
  border:1px solid #334155;
  background:#0b1220;
  color:#e5e7eb;
  font-size:12px;
  font-weight:800;
}
.heroIncidentPill.critical{
  color:#fecaca;
  border-color:rgba(252,165,165,.25);
  background:rgba(127,29,29,.22);
}
.heroIncidentPill.major{
  color:#fdba74;
  border-color:rgba(251,146,60,.25);
  background:rgba(124,45,18,.20);
}
.heroIncidentPill.warning{
  color:#fde68a;
  border-color:rgba(251,191,36,.25);
  background:rgba(120,53,15,.18);
}
</style>

<div class="wrap">

  <div class="card">
    <h1 class="heroTitle"><?= $lang === 'en' ? 'System Status' : 'Systemstatus' ?></h1>

    <div class="muted">
      <?= $lang === 'en'
        ? 'Live overview of the audio service, API and recent status history.'
        : 'Liveöversikt över ljudtjänsten, API:t och den senaste statushistoriken.' ?>
    </div>

    <div class="liveBadge">
      <span class="liveDot"></span>
      <span id="liveText"><?= $lang==='en' ? 'Live' : 'Live' ?></span>
      &nbsp;
      <span id="refreshCountdown">30s</span>
    </div>

    <div id="incidentBanner" class="incidentBanner" aria-live="polite">
      <div class="incidentTitle"><?= $lang === 'en' ? 'Incident detected' : 'Incident upptäckt' ?></div>
      <div class="incidentText" id="incidentText">
        <?= $lang === 'en' ? 'Checking recent snapshots…' : 'Kontrollerar senaste snapshots…' ?>
      </div>
      <div class="incidentList" id="incidentList"></div>
    </div>

    <div id="heroIncidentSummary" class="heroIncidentSummary show">
      <div id="heroIncidentActive" class="heroIncidentPill">—</div>
      <div id="heroIncidentLast" class="heroIncidentPill">—</div>
    </div>

    <div class="heroTop">
      <div>
        <div class="ringWrap">
          <svg class="ringSvg" viewBox="0 0 160 160">
            <circle class="ringBg" cx="80" cy="80" r="70"></circle>
            <circle id="healthRing" class="ringFg" cx="80" cy="80" r="70"></circle>
          </svg>

          <div class="ringCenter">
            <div class="ringScore" id="healthScore">--</div>
            <div class="muted"><?= $lang === 'en' ? 'Health' : 'Hälsa' ?></div>
          </div>
        </div>
      </div>

      <div id="overviewGrid" class="grid">
        <div class="stat loadingPulse"><div class="statLabel">Status</div><div class="statValue">—</div></div>
        <div class="stat loadingPulse"><div class="statLabel">API</div><div class="statValue">—</div></div>
        <div class="stat loadingPulse"><div class="statLabel">Tracks</div><div class="statValue">—</div></div>
        <div class="stat loadingPulse"><div class="statLabel">Library</div><div class="statValue">—</div></div>
      </div>
    </div>
  </div>

  <div class="card">
    <h2 class="sectionTitle"><?= $lang === 'en' ? 'History & trends' : 'Historik & trender' ?></h2>

    <div class="chartGrid">

      <div class="chartCard">
        <div class="chartHead">
          <div class="chartTitle"><?= $lang === 'en' ? 'API latency history' : 'Historik API-latens' ?></div>
          <div class="chartMeta" id="latencyMeta">—</div>
        </div>
        <div class="chartStage">
          <div class="chartTooltip" id="latencyTooltip"></div>
          <div class="chartWrap">
            <svg id="latencyChart" class="chartSvg" viewBox="0 0 520 220" preserveAspectRatio="none"></svg>
          </div>
        </div>
      </div>

      <div class="chartCard">
        <div class="chartHead">
          <div class="chartTitle"><?= $lang === 'en' ? 'Track count history' : 'Historik antal tracks' ?></div>
          <div class="chartMeta" id="tracksMeta">—</div>
        </div>
        <div class="chartStage">
          <div class="chartTooltip" id="tracksTooltip"></div>
          <div class="chartWrap">
            <svg id="tracksChart" class="chartSvg" viewBox="0 0 520 220" preserveAspectRatio="none"></svg>
          </div>
        </div>
      </div>

      <div class="chartCard">
        <div class="chartHead">
          <div class="chartTitle"><?= $lang === 'en' ? 'Health score history' : 'Historik hälsopoäng' ?></div>
          <div class="chartMeta" id="healthMeta">—</div>
        </div>
        <div class="chartStage">
          <div class="chartTooltip" id="healthTooltip"></div>
          <div class="chartWrap">
            <svg id="healthChart" class="chartSvg" viewBox="0 0 520 220" preserveAspectRatio="none"></svg>
          </div>
        </div>
      </div>

    </div>

    <div style="margin-top:14px;">
      <div class="chartTitle"><?= $lang === 'en' ? 'Recent uptime timeline' : 'Senaste uptime-tidslinje' ?></div>
      <div id="uptimeTimeline" class="timeline"></div>
      <div class="subtleNote" id="uptimeNote">—</div>
    </div>

    <div class="uptimeHero" id="uptimeHero">
      <div class="uptimePill"><?= $lang === 'en' ? '24h uptime' : '24h uptime' ?>: <strong id="uptimePercent">—</strong></div>
      <div class="uptimePill"><?= $lang === 'en' ? 'Incidents' : 'Incidenter' ?>: <strong id="incidentCount">—</strong></div>
    </div>

    <div style="margin-top:14px;">
      <div class="chartTitle"><?= $lang === 'en' ? 'Incident history' : 'Incidenthistorik' ?></div>
      <div id="incidentHistory" class="incidentHistory">
        <div class="muted">—</div>
      </div>
    </div>
  </div>

  <div class="card">
    <h2 class="sectionTitle"><?= $lang === 'en' ? 'Subsystems' : 'Delsystem' ?></h2>
    <div id="checksBox" class="checkList">
      <div class="checkCard loadingPulse">Loading...</div>
    </div>
  </div>

</div>

<script>
const REFRESH = 30;
const CIRC = 439.82;
const HISTORY_LIMIT = 288;
const PERF_WARN_MS = 250;
const PERF_MAJOR_MS = 500;
const PERF_CRITICAL_MS = 900;
const PERF_MIN_CONSECUTIVE = 3;

const LANG = <?= json_encode($lang, JSON_UNESCAPED_UNICODE) ?>;

const T = {
  sv: {
    online: 'ONLINE',
    offline: 'OFFLINE',
    ok: 'OK',
    fail: 'FEL',
    status: 'Status',
    apiLatency: 'API-latens',
    tracks: 'Tracks',
    library: 'Library',
    latestLibrary: 'Senaste library update',
    latestPlay: 'Senaste spelning',
    latestTrack: 'Senaste track',
    latestRollup: 'Senaste rollup',
    rollupDay: 'Rollup-dag',
    endpoint: 'Endpoint',
    state: 'Status',
    updated: 'Uppdaterad',
    unknown: 'Okänd',
    items: 'spår',
    ms: 'ms',
    refreshedNow: 'Uppdaterad nyss',
    noHistory: 'Ingen historik ännu',
    snapshots: 'snapshots',
    avg: 'snitt',
    min: 'min',
    max: 'max',
    uptimeLast: 'Uptime senaste snapshots',
    incidentTitle: 'Incident upptäckt',
    incidentNone: 'Inga aktiva incidenter',
    incidentActive: 'En eller flera kontroller har fallerat flera snapshots i rad.',
    incidentChecks: 'Påverkade checks',
    timeAxisStart: 'Start',
    timeAxisNow: 'Nu',
    uptime24: '24h uptime',
    incidents: 'Incidenter',
    incidentHistory: 'Incidenthistorik',
    incidentStarted: 'Startade',
    incidentEnded: 'Avslutades',
    incidentDuration: 'Varaktighet',
    incidentOpen: 'Pågår',
    incidentClosed: 'Avslutad',
    noIncidents: 'Inga incidenter i historiken',
    checksAffected: 'Påverkade checks',
    severity: 'Allvar',
    severityWarning: 'Varning',
    severityMajor: 'Allvarlig',
    severityCritical: 'Kritisk',
    perfIncident: 'Prestanda',
    latencyHigh: 'Hög API-latens',
    activeIncidents: 'Aktiva incidenter',
    lastIncidentEnded: 'Senaste incident avslutades',
    noRecentIncidents: 'Inga nyliga incidenter',
  },
  en: {
    online: 'ONLINE',
    offline: 'OFFLINE',
    ok: 'OK',
    fail: 'FAIL',
    status: 'Status',
    apiLatency: 'API latency',
    tracks: 'Tracks',
    library: 'Library',
    latestLibrary: 'Latest library update',
    latestPlay: 'Latest play',
    latestTrack: 'Latest track',
    latestRollup: 'Latest rollup',
    rollupDay: 'Rollup day',
    endpoint: 'Endpoint',
    state: 'State',
    updated: 'Updated',
    unknown: 'Unknown',
    items: 'tracks',
    ms: 'ms',
    refreshedNow: 'Updated just now',
    noHistory: 'No history yet',
    snapshots: 'snapshots',
    avg: 'avg',
    min: 'min',
    max: 'max',
    uptimeLast: 'Uptime last snapshots',
    incidentTitle: 'Incident detected',
    incidentNone: 'No active incidents',
    incidentActive: 'One or more checks have failed for multiple snapshots in a row.',
    incidentChecks: 'Affected checks',
    timeAxisStart: 'Start',
    timeAxisNow: 'Now',
    uptime24: '24h uptime',
    incidents: 'Incidents',
    incidentHistory: 'Incident history',
    incidentStarted: 'Started',
    incidentEnded: 'Ended',
    incidentDuration: 'Duration',
    incidentOpen: 'Open',
    incidentClosed: 'Closed',
    noIncidents: 'No incidents in history',
    checksAffected: 'Affected checks',
    severity: 'Severity',
    severityWarning: 'Warning',
    severityMajor: 'Major',
    severityCritical: 'Critical',
    perfIncident: 'Performance',
    latencyHigh: 'High API latency',
    activeIncidents: 'Active incidents',
    lastIncidentEnded: 'Last incident ended',
    noRecentIncidents: 'No recent incidents',
  }
};

function tg(k){ return T[LANG]?.[k] ?? k; }

function esc(s){
  return String(s ?? '').replace(/[&<>"']/g, m => ({
    "&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"
  }[m]));
}

function fmtBytes(b){
  const n = Number(b || 0);
  if (!n) return '0 KB';
  if (n > 1024 * 1024) return (n / 1024 / 1024).toFixed(2) + ' MB';
  return Math.round(n / 1024) + ' KB';
}

function fmtDate(ts){
  if (!ts) return '-';
  return new Date(Number(ts) * 1000).toLocaleString(LANG === 'en' ? 'en-GB' : 'sv-SE', { hour12:false });
}

function setRing(score){
  score = Math.max(0, Math.min(100, Number(score) || 0));
  const off = CIRC - (CIRC * score / 100);
  const ring = document.getElementById('healthRing');
  const scoreEl = document.getElementById('healthScore');

  ring.style.strokeDashoffset = off;
  scoreEl.innerText = score + '%';

  if (score >= 90) ring.style.stroke = '#86efac';
  else if (score >= 70) ring.style.stroke = '#fbbf24';
  else ring.style.stroke = '#fca5a5';
}

function badge(ok){
  return ok
    ? '<span class="badge ok">' + esc(tg('ok')) + '</span>'
    : '<span class="badge err">' + esc(tg('fail')) + '</span>';
}

function average(arr){
  if (!arr.length) return 0;
  return arr.reduce((a,b)=>a+b,0) / arr.length;
}

function calcScoreFromSnapshot(s){
  const checks = s?.checks || {};
  const vals = Object.values(checks);
  if (!vals.length) return Number(s?.ok ? 100 : 0);
  const passed = vals.filter(v => !!v?.ok).length;
  return Math.round((passed / vals.length) * 100);
}

function positionTooltip(stageEl, tooltipEl, x, y, html){
  if (!stageEl || !tooltipEl) return;

  tooltipEl.innerHTML = html;
  tooltipEl.classList.add('show');

  const rect = stageEl.getBoundingClientRect();
  const localX = (x / 520) * rect.width;
  const localY = (y / 220) * rect.height;

  tooltipEl.style.left = localX + 'px';
  tooltipEl.style.top = localY + 'px';
}

function hideTooltip(tooltipEl){
  if (!tooltipEl) return;
  tooltipEl.classList.remove('show');
}

function drawLineChart(svgId, values, opts = {}){
  const svg = document.getElementById(svgId);
  if (!svg) return;

  const width = 520;
  const height = 220;
  const padL = 44;
  const padR = 14;
  const padT = 14;
  const padB = 34;

  const clean = (values || []).map(v => Number(v) || 0);

  if (!clean.length){
    svg.innerHTML = `<text x="20" y="40" class="axisText">${esc(tg('noHistory'))}</text>`;
    return;
  }

  const min = Math.min(...clean);
  const max = Math.max(...clean);
  const range = Math.max(1, max - min);

  const pts = clean.map((v, i) => {
    const x = padL + (i * ((width - padL - padR) / Math.max(1, clean.length - 1)));
    const y = padT + ((max - v) / range) * (height - padT - padB);
    return { x, y, v, i };
  });

  const line = pts.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x.toFixed(2)} ${p.y.toFixed(2)}`).join(' ');
  const area = [
    `M ${pts[0].x.toFixed(2)} ${height - padB}`,
    ...pts.map(p => `L ${p.x.toFixed(2)} ${p.y.toFixed(2)}`),
    `L ${pts[pts.length - 1].x.toFixed(2)} ${height - padB}`,
    'Z'
  ].join(' ');

  const clsLine = opts.lineClass || 'lineLatency';
  const clsArea = opts.areaClass || 'areaLatency';
  const clsDot = opts.dotClass || 'dotLatency';
  const labels = opts.labels || [];

  const yTicks = [
    max,
    Math.round((max + min) / 2),
    min
  ];

  const approxTickCount = Math.min(6, clean.length);
  const xTickEvery = Math.max(1, Math.floor((clean.length - 1) / Math.max(1, approxTickCount - 1)));
  const xTickIndexes = [];
  for (let i = 0; i < clean.length; i += xTickEvery) xTickIndexes.push(i);
  if (xTickIndexes[xTickIndexes.length - 1] !== clean.length - 1) {
    xTickIndexes.push(clean.length - 1);
  }

  svg.innerHTML = `
    ${yTicks.map(v => {
      const y = padT + ((max - v) / range) * (height - padT - padB);
      return `
        <line x1="${padL}" y1="${y}" x2="${width-padR}" y2="${y}" class="chartGridLine"></line>
        <text x="8" y="${y + 4}" class="axisText">${esc(String(v))}</text>
      `;
    }).join('')}

    <line x1="${padL}" y1="${height-padB}" x2="${width-padR}" y2="${height-padB}" class="chartAxisLine"></line>
    <line x1="${padL}" y1="${padT}" x2="${padL}" y2="${height-padB}" class="chartAxisLine"></line>

    ${xTickIndexes.map(idx => {
      const p = pts[idx];
      const label = labels[idx] || (idx === 0 ? tg('timeAxisStart') : (idx === clean.length - 1 ? tg('timeAxisNow') : ''));
      return `
        <line x1="${p.x}" y1="${height-padB}" x2="${p.x}" y2="${height-padB+4}" class="chartAxisLine"></line>
        <text x="${p.x}" y="${height-8}" text-anchor="middle" class="${idx === clean.length - 1 ? 'axisTickBold' : 'axisTickText'}">${esc(label)}</text>
      `;
    }).join('')}

    <path d="${area}" class="${clsArea}"></path>
    <path d="${line}" class="${clsLine}"></path>

    <line id="${svgId}HoverLine" x1="${padL}" y1="${padT}" x2="${padL}" y2="${height-padB}" class="chartHoverLine" style="display:none"></line>

    ${pts.map((p) => `
      <circle
        class="chartPoint ${clsDot}"
        cx="${p.x.toFixed(2)}"
        cy="${p.y.toFixed(2)}"
        r="4.5"
        data-index="${p.i}"
        data-value="${p.v}"
      ></circle>
    `).join('')}
  `;

  const stage = svg.closest('.chartStage');
  const tooltip = stage ? stage.querySelector('.chartTooltip') : null;
  const hoverLine = document.getElementById(svgId + 'HoverLine');

  svg.querySelectorAll('.chartPoint').forEach((node) => {
    node.addEventListener('mouseenter', () => {
      const idx = Number(node.getAttribute('data-index') || 0);
      const val = Number(node.getAttribute('data-value') || 0);
      const x = Number(node.getAttribute('cx') || 0);
      const y = Number(node.getAttribute('cy') || 0);

      if (hoverLine){
        hoverLine.style.display = '';
        hoverLine.setAttribute('x1', x);
        hoverLine.setAttribute('x2', x);
      }

      const label = labels[idx] || ('#' + (idx + 1));

      positionTooltip(
        stage,
        tooltip,
        x,
        y,
        `
          <div class="ttTitle">${esc(label)}</div>
          <div class="ttRow">
            <span class="ttKey">${esc(opts.valueLabel || 'Value')}</span>
            <span class="ttVal">${esc(String(val))}${opts.valueSuffix ? ' ' + esc(opts.valueSuffix) : ''}</span>
          </div>
        `
      );
    });

    node.addEventListener('mouseleave', () => {
      if (hoverLine) hoverLine.style.display = 'none';
      hideTooltip(tooltip);
    });
  });

  if (stage){
    stage.addEventListener('mouseleave', () => {
      if (hoverLine) hoverLine.style.display = 'none';
      hideTooltip(tooltip);
    });
  }
}

function renderTimeline(history){
  const el = document.getElementById('uptimeTimeline');
  const note = document.getElementById('uptimeNote');
  if (!el || !note) return;

  if (!history.length){
    el.innerHTML = '';
    note.textContent = tg('noHistory');
    return;
  }

  const recent = history.slice(-24);
  const okCount = recent.filter(x => !!x.ok).length;
  const pct = Math.round((okCount / recent.length) * 100);

  el.innerHTML = recent.map(item => {
    const cls = item.ok ? 'ok' : 'err';
    const title = `${fmtDate(item.ts)} • ${item.ok ? tg('ok') : tg('fail')}`;
    return `<div class="timelineCell ${cls}" title="${esc(title)}"></div>`;
  }).join('');

  note.textContent = `${tg('uptimeLast')}: ${pct}% • ${recent.length} ${tg('snapshots')}`;
}

async function fetchJson(url){
  const res = await fetch(url, { cache:'no-store' });
  const txt = await res.text();
  let j = {};
  try { j = JSON.parse(txt); } catch {}
  return { ok: res.ok, json: j };
}

async function fetchStatus(){
  try{
    const t0 = performance.now();
    const healthRes = await fetch('/api/health.php', { cache:'no-store' });
    const healthTxt = await healthRes.text();
    const t1 = performance.now();

    let health = {};
    try { health = JSON.parse(healthTxt); } catch {}

    const latency = Math.round(t1 - t0);
    const checks = health.checks || {};
    const checkKeys = Object.keys(checks);
    const okCount = Object.values(checks).filter(c => c && c.ok).length;
    const score = checkKeys.length ? Math.round(okCount / checkKeys.length * 100) : 0;

    setRing(score);

    const tracks = Number(health.library?.tracks || 0);
    const libSize = Number(health.library?.sizeBytes || 0);
    const libUpd = Number(health.library?.updated || 0);

    document.getElementById("overviewGrid").innerHTML = `
      <div class="stat">
        <div class="statLabel">${esc(tg('status'))}</div>
        <div class="statValue ${health.ok ? 'ok' : 'err'}">${esc(health.ok ? tg('online') : tg('offline'))}</div>
        <div class="statSub">Health ${score}%</div>
        <div class="statBar"><div class="statBarFill" style="width:${score}%"></div></div>
      </div>

      <div class="stat">
        <div class="statLabel">${esc(tg('apiLatency'))}</div>
        <div class="statValue">${latency} ${esc(tg('ms'))}</div>
        <div class="statSub">/api/health.php</div>
        <div class="statBar"><div class="statBarFill" style="width:${Math.min(100, latency / 5)}%"></div></div>
      </div>

      <div class="stat">
        <div class="statLabel">${esc(tg('tracks'))}</div>
        <div class="statValue">${tracks}</div>
        <div class="statSub">${esc(tg('items'))}</div>
      </div>

      <div class="stat">
        <div class="statLabel">${esc(tg('library'))}</div>
        <div class="statValue">${esc(fmtBytes(libSize))}</div>
        <div class="statSub">${esc(tg('latestLibrary'))}: ${esc(fmtDate(libUpd))}</div>
      </div>
    `;

    document.getElementById("checksBox").innerHTML =
      Object.entries(checks).map(([k,v]) => `
        <div class="checkCard">
          <div class="checkHead">
            <div class="checkName mono">${esc(k)}</div>
            ${badge(!!v.ok)}
          </div>

          <div class="kv">
            <div class="kvKey">${esc(tg('endpoint'))}</div>
            <div class="kvVal mono">${esc(k)}</div>

            <div class="kvKey">${esc(tg('state'))}</div>
            <div class="kvVal">${v.ok ? `<span class="ok">${esc(tg('ok'))}</span>` : `<span class="err">${esc(tg('fail'))}</span>`}</div>

            <div class="kvKey">${esc(tg('updated'))}</div>
            <div class="kvVal">${v.updated ? esc(fmtDate(v.updated)) : esc(tg('unknown'))}</div>

            ${k === 'library' ? `
              <div class="kvKey">${esc(tg('tracks'))}</div>
              <div class="kvVal">${esc(String(v.tracks ?? 0))}</div>

              <div class="kvKey">${esc(tg('library'))}</div>
              <div class="kvVal">${esc(fmtBytes(v.sizeBytes ?? 0))}</div>
            ` : ''}

            ${k === 'db' ? `
              <div class="kvKey">${esc(tg('latestPlay'))}</div>
              <div class="kvVal">${esc(fmtDate(v.latestPlayTs ?? 0))}</div>

              <div class="kvKey">${esc(tg('latestTrack'))}</div>
              <div class="kvVal mono">${esc(v.latestPlayTrackId || tg('unknown'))}</div>

              <div class="kvKey">${esc(tg('latestRollup'))}</div>
              <div class="kvVal">${esc(fmtDate(v.latestRollupTs ?? 0))}</div>

              <div class="kvKey">${esc(tg('rollupDay'))}</div>
              <div class="kvVal mono">${esc(v.latestRollupDay || tg('unknown'))}</div>
            ` : ''}
          </div>
        </div>
      `).join('');

    document.getElementById('liveText').textContent = tg('refreshedNow');

  } catch(e){
    document.getElementById("overviewGrid").innerHTML =
      `<div class="stat"><div class="statLabel">${esc(tg('status'))}</div><div class="statValue err">${esc(tg('offline'))}</div></div>`;
  }
}

function findIncidentChecks(history, consecutiveFails = 3){
  const out = [];
  if (!Array.isArray(history) || !history.length) return out;

  const latest = history.slice(-consecutiveFails);
  if (latest.length < consecutiveFails) return out;

  const checkNames = new Set();
  latest.forEach(s => {
    Object.keys(s?.checks || {}).forEach(k => checkNames.add(k));
  });

  for (const name of checkNames){
    const failedAll = latest.every(s => {
      const c = s?.checks?.[name];
      return c && c.ok === false;
    });
    if (failedAll) out.push(name);
  }

  return out;
}

function renderIncidentBanner(history){
  const banner = document.getElementById('incidentBanner');
  const text = document.getElementById('incidentText');
  const list = document.getElementById('incidentList');

  if (!banner || !text || !list) return;

  const incidents = findIncidentChecks(history, 3);

  if (!incidents.length){
    banner.classList.remove('show');
    text.textContent = tg('incidentNone');
    list.innerHTML = '';
    return;
  }

  banner.classList.add('show');
  text.textContent = tg('incidentActive');
  list.innerHTML = incidents.map(name => `
    <span class="incidentChip">${esc(name)}</span>
  `).join('');
}

function formatDurationSec(sec){
  sec = Math.max(0, Number(sec) || 0);
  const h = Math.floor(sec / 3600);
  const m = Math.floor((sec % 3600) / 60);

  if (h > 0) return `${h}h ${m}m`;
  return `${m}m`;
}

function calcUptimePercent(history){
  if (!Array.isArray(history) || !history.length) return 0;
  const okCount = history.filter(x => !!x.ok).length;
  return Math.round((okCount / history.length) * 10000) / 100;
}

function severityLabel(level){
  if (level === 'critical') return tg('severityCritical');
  if (level === 'major') return tg('severityMajor');
  return tg('severityWarning');
}

function severityClass(level){
  if (level === 'critical') return 'sev-critical';
  if (level === 'major') return 'sev-major';
  return 'sev-warning';
}

function incidentSeverity(checks, snapshots){
  const list = Array.isArray(checks) ? checks : [];
  const count = Number(snapshots || 0);

  if (list.includes('db') || list.includes('library') || list.includes('track_play')) {
    return 'critical';
  }
  if (list.length >= 2) {
    return 'critical';
  }

  if (list.includes('storage') || list.includes('upload') || list.includes('stats_top')) {
    return 'major';
  }
  if (count >= 6) {
    return 'major';
  }

  return 'warning';
}

function perfSeverityFromLatency(ms){
  const n = Number(ms || 0);
  if (n >= PERF_CRITICAL_MS) return 'critical';
  if (n >= PERF_MAJOR_MS) return 'major';
  if (n >= PERF_WARN_MS) return 'warning';
  return '';
}

function buildPerformanceIncidents(history){
  if (!Array.isArray(history) || !history.length) return [];

  const incidents = [];
  let active = null;

  for (const snap of history){
    const latency = Number(snap?.latencyMs || 0);
    const sev = perfSeverityFromLatency(latency);

    if (sev){
      if (!active){
        active = {
          kind: 'performance',
          title: tg('latencyHigh'),
          checks: ['api_latency'],
          startTs: Number(snap.ts || 0),
          endTs: Number(snap.ts || 0),
          open: true,
          count: 1,
          severity: sev,
          maxLatency: latency,
        };
      } else {
        active.endTs = Number(snap.ts || active.endTs || 0);
        active.count += 1;
        active.maxLatency = Math.max(Number(active.maxLatency || 0), latency);

        const currentRank = ['warning', 'major', 'critical'].indexOf(active.severity);
        const newRank = ['warning', 'major', 'critical'].indexOf(sev);
        if (newRank > currentRank) active.severity = sev;
      }
    } else {
      if (active){
        if (active.count >= PERF_MIN_CONSECUTIVE){
          active.open = false;
          incidents.push(active);
        }
        active = null;
      }
    }
  }

  if (active && active.count >= PERF_MIN_CONSECUTIVE){
    incidents.push(active);
  }

  return incidents.reverse();
}

function buildIncidentHistory(history){
  if (!Array.isArray(history) || !history.length) return [];

  const incidents = [];
  let active = null;

  for (const snap of history){
    const checks = snap?.checks || {};
    const failing = Object.entries(checks)
      .filter(([, v]) => v && v.ok === false)
      .map(([k]) => k)
      .sort();

    if (failing.length){
      const key = failing.join('|');

      if (!active){
        active = {
          kind: 'check',
          key,
          checks: failing,
          startTs: Number(snap.ts || 0),
          endTs: Number(snap.ts || 0),
          open: true,
          count: 1,
        };
      } else if (active.kind === 'check' && active.key === key){
        active.endTs = Number(snap.ts || active.endTs || 0);
        active.count += 1;
      } else {
        if (active.kind === 'check'){
          active.open = false;
          active.severity = incidentSeverity(active.checks, active.count);
          incidents.push(active);
        }

        active = {
          kind: 'check',
          key,
          checks: failing,
          startTs: Number(snap.ts || 0),
          endTs: Number(snap.ts || 0),
          open: true,
          count: 1,
        };
      }
    } else {
      if (active && active.kind === 'check'){
        active.open = false;
        active.severity = incidentSeverity(active.checks, active.count);
        incidents.push(active);
        active = null;
      }
    }
  }

  if (active && active.kind === 'check'){
    active.severity = incidentSeverity(active.checks, active.count);
    incidents.push(active);
  }

  const perfIncidents = buildPerformanceIncidents(history);

  return [...incidents, ...perfIncidents].sort((a, b) => Number(b.startTs || 0) - Number(a.startTs || 0));
}

function severityRank(level){
  if (level === 'critical') return 3;
  if (level === 'major') return 2;
  return 1;
}

function renderHeroIncidentSummary(incidents){
  const wrap = document.getElementById('heroIncidentSummary');
  const activeEl = document.getElementById('heroIncidentActive');
  const lastEl = document.getElementById('heroIncidentLast');

  if (!wrap || !activeEl || !lastEl) return;

  const list = Array.isArray(incidents) ? incidents : [];
  const active = list.filter(x => !!x.open);
  const closed = list.filter(x => !x.open);

  wrap.classList.add('show');

  if (active.length){
    const top = active.slice().sort((a,b) => severityRank(b.severity) - severityRank(a.severity))[0];
    const sev = top?.severity || 'warning';

    activeEl.className = `heroIncidentPill ${sev}`;
    activeEl.textContent = `${tg('activeIncidents')}: ${active.length} • ${severityLabel(sev)}`;
  } else {
    activeEl.className = 'heroIncidentPill';
    activeEl.textContent = `${tg('activeIncidents')}: 0`;
  }

  if (closed.length){
    const lastClosed = closed.slice().sort((a,b) => Number(b.endTs || 0) - Number(a.endTs || 0))[0];
    lastEl.className = 'heroIncidentPill';
    lastEl.textContent = `${tg('lastIncidentEnded')}: ${fmtDate(lastClosed.endTs)}`;
  } else if (active.length){
    lastEl.className = 'heroIncidentPill';
    lastEl.textContent = tg('incidentOpen');
  } else {
    lastEl.className = 'heroIncidentPill';
    lastEl.textContent = tg('noRecentIncidents');
  }
}

function renderIncidentHistory(history){
  const wrap = document.getElementById('incidentHistory');
  const countEl = document.getElementById('incidentCount');
  const uptimeEl = document.getElementById('uptimePercent');

  if (!wrap || !countEl || !uptimeEl) return;

  const incidents = buildIncidentHistory(history);
  renderHeroIncidentSummary(incidents);
  const uptime = calcUptimePercent(history);

  uptimeEl.textContent = uptime.toFixed(2) + '%';
  countEl.textContent = String(incidents.length);

  if (!incidents.length){
    wrap.innerHTML = `<div class="muted">${esc(tg('noIncidents'))}</div>`;
    return;
  }

  wrap.innerHTML = incidents.map((inc) => {
    const started = fmtDate(inc.startTs);
    const ended = inc.open ? tg('incidentOpen') : fmtDate(inc.endTs);
    const durSec = Math.max(0, Number(inc.endTs || 0) - Number(inc.startTs || 0));
    const duration = formatDurationSec(durSec);
    const sev = inc.severity || 'warning';

    const title = (inc.kind === 'performance')
      ? `${tg('perfIncident')}: ${tg('latencyHigh')}`
      : `${tg('checksAffected')}: ${inc.checks.map(esc).join(', ')}`;

    const extraPerf = (inc.kind === 'performance')
      ? `
        <div class="kvKey">Max latency</div>
        <div class="kvVal">${esc(String(inc.maxLatency || 0))} ${esc(tg('ms'))}</div>
      `
      : '';

    return `
      <div class="incidentRow">
        <div class="incidentRowHead">
          <div class="incidentRowTitle">${title}</div>
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <span class="sevBadge ${severityClass(sev)}">${esc(severityLabel(sev))}</span>
            <div class="incidentState ${inc.open ? 'open' : 'closed'}">
              ${esc(inc.open ? tg('incidentOpen') : tg('incidentClosed'))}
            </div>
          </div>
        </div>

        <div class="kv" style="margin-top:10px;">
          <div class="kvKey">${esc(tg('incidentStarted'))}</div>
          <div class="kvVal">${esc(started)}</div>

          <div class="kvKey">${esc(tg('incidentEnded'))}</div>
          <div class="kvVal">${esc(ended)}</div>

          <div class="kvKey">${esc(tg('incidentDuration'))}</div>
          <div class="kvVal">${esc(duration)}</div>

          <div class="kvKey">${esc(tg('severity'))}</div>
          <div class="kvVal">${esc(severityLabel(sev))}</div>

          ${extraPerf}
        </div>

        <div class="incidentRowMeta">${inc.count} snapshots</div>
      </div>
    `;
  }).join('');
}

async function fetchHistory(){
  try{
    const { ok, json } = await fetchJson('/api/status_history.php?limit=' + HISTORY_LIMIT);
    const history = (ok && Array.isArray(json.history)) ? json.history : [];

    const labels = history.map(x => {
      if (!x?.ts) return '';
      const d = new Date(Number(x.ts) * 1000);
      const hh = String(d.getHours()).padStart(2, '0');
      const mm = String(d.getMinutes()).padStart(2, '0');
      return `${hh}:${mm}`;
    });

    const latencySeries = history.map(x => Number(x.latencyMs || 0));
    const tracksSeries = history.map(x => Number(x.tracks || 0));
    const healthSeries = history.map(x => calcScoreFromSnapshot(x));

    drawLineChart('latencyChart', latencySeries, {
      lineClass: 'lineLatency',
      areaClass: 'areaLatency',
      dotClass: 'dotLatency',
      labels,
      valueLabel: LANG === 'en' ? 'Latency' : 'Latens',
      valueSuffix: tg('ms')
    });

    drawLineChart('tracksChart', tracksSeries, {
      lineClass: 'lineTracks',
      areaClass: 'areaTracks',
      dotClass: 'dotTracks',
      labels,
      valueLabel: 'Tracks',
      valueSuffix: ''
    });

    drawLineChart('healthChart', healthSeries, {
      lineClass: 'lineHealth',
      areaClass: 'areaHealth',
      dotClass: 'dotHealth',
      labels,
      valueLabel: LANG === 'en' ? 'Health' : 'Hälsa',
      valueSuffix: '%'
    });

    const latMeta = document.getElementById('latencyMeta');
    const trkMeta = document.getElementById('tracksMeta');
    const hlthMeta = document.getElementById('healthMeta');

    if (latencySeries.length) {
      const min = Math.min(...latencySeries);
      const max = Math.max(...latencySeries);
      const avg = Math.round(average(latencySeries));
      latMeta.textContent = `${tg('min')}: ${min}${tg('ms')} • ${tg('max')}: ${max}${tg('ms')} • ${tg('avg')}: ${avg}${tg('ms')}`;
    } else {
      latMeta.textContent = tg('noHistory');
    }

    if (tracksSeries.length) {
      const min = Math.min(...tracksSeries);
      const max = Math.max(...tracksSeries);
      const avg = Math.round(average(tracksSeries));
      trkMeta.textContent = `${tg('min')}: ${min} • ${tg('max')}: ${max} • ${tg('avg')}: ${avg}`;
    } else {
      trkMeta.textContent = tg('noHistory');
    }

    if (healthSeries.length) {
      const min = Math.min(...healthSeries);
      const max = Math.max(...healthSeries);
      const avg = Math.round(average(healthSeries));
      hlthMeta.textContent = `${tg('min')}: ${min}% • ${tg('max')}: ${max}% • ${tg('avg')}: ${avg}%`;
    } else {
      hlthMeta.textContent = tg('noHistory');
    }

    renderTimeline(history);
    renderIncidentHistory(history);
    renderIncidentBanner(history);

  } catch(e){
    drawLineChart('latencyChart', []);
    drawLineChart('tracksChart', []);
    drawLineChart('healthChart', []);
    renderTimeline([]);
    renderIncidentHistory([]);
    renderIncidentBanner([]);
  }
}

let left = REFRESH;

function tick(){
  left--;
  if (left < 0) {
    left = REFRESH;
    fetchStatus();
    fetchHistory();
  }
  document.getElementById("refreshCountdown").innerText = left + 's';
}

setInterval(tick, 1000);

fetchStatus();
fetchHistory();
</script>

<?php require __DIR__ . '/../_partials/footer.php'; ?>