<?php
// /upload/index.php
declare(strict_types=1);

require __DIR__ . '/../_partials/i18n.php';
require __DIR__ . '/../_partials/meta.php';

$lang = ij_lang();

$TOKEN = 'ijUpl_7f4bFQp2mW9cZx1N6tR8yK3vH0sA5dJ2lG8nP6qS1uE9rT4xY7cV5bN0mQ3aL';

$pageTitleText = ($lang === 'en')
  ? 'iceBeats.io – Upload tracks'
  : 'iceBeats.io – Ladda upp spår';

$pageDescText = ($lang === 'en')
  ? 'Upload your tracks, jingles and sound effects to iceBeats.io and share them with others.'
  : 'Ladda upp dina låtar, jinglar och ljudeffekter till iceBeats.io och dela dem med andra.';

$meta = ij_build_meta([
  'title' => $pageTitleText,
  'description' => $pageDescText,
  'canonical' => ij_abs('/upload/?lang=' . $lang),
  'image' => ij_abs('/share/og.jpg'),
  'type' => 'website',
]);

$pageTitle = $meta['title'];
$pageHead = ij_render_meta($meta);

require __DIR__ . '/../_partials/header.php';

$L = [
  'sv' => [
    'docTitle' => 'Icebeats – Ladda upp spår',
    'h1' => 'Ladda upp ny låt',
    'sub' => 'Låten publiceras direkt. Icebeats kan ta bort/spärra vid behov.',
    'artist' => 'Artist',
    'title' => 'Titel',
    'genre' => 'Genre',
    'mp3' => 'MP3',
    'cover' => 'Omslagsbild',
    'phArtist' => 't.ex. Icebeat',
    'phTitle' => 't.ex. Goal Horn Remix',
    'upload' => '⬆️ Ladda upp',
    'uploading' => 'Laddar upp...',
    'ok' => '✅ Uppladdat! (ID: %s)',
    'fail' => '❌ Misslyckades: %s',
    'mustAgree' => '❌ Du måste godkänna villkoren innan uppladdning.',
    'termsTitle' => 'Villkor (Arena-licens)',
    'termsHint' => 'Läs igenom och godkänn för att ladda upp.',
    'agreeLabel' => 'Jag godkänner villkoren ovan.',
    'agreeSmall' => 'Du kan när som helst be oss ta bort din låt.',
  ],
  'en' => [
    'docTitle' => 'Icebeats – Upload tracks',
    'h1' => 'Upload new track',
    'sub' => 'The track is published immediately. Icebeats may remove or block it if necessary.',
    'artist' => 'Artist',
    'title' => 'Title',
    'genre' => 'Genre',
    'mp3' => 'MP3',
    'cover' => 'Cover image',
    'phArtist' => 'e.g. Icebeat',
    'phTitle' => 'e.g. Goal Horn Remix',
    'upload' => '⬆️ Upload',
    'uploading' => 'Uploading...',
    'ok' => '✅ Uploaded! (ID: %s)',
    'fail' => '❌ Failed: %s',
    'mustAgree' => '❌ You must accept the terms before uploading.',
    'termsTitle' => 'Terms (Arena license)',
    'termsHint' => 'Please read and accept to upload.',
    'agreeLabel' => 'I agree to the terms above.',
    'agreeSmall' => 'You can request removal of your track at any time.',
  ],
];

$TERMS = [
  'sv' =>
"GENOM ATT LADDA UPP ETT SPÅR TILL ICEBEATS / ICEJOCKEY INTYGAR DU ATT:

1. Du är den ursprungliga skaparen av spåret (eller har alla nödvändiga rättigheter/licenser för att ladda upp det).
2. Spåret gör inte intrång i tredje parts upphovsrätt, varumärken eller andra rättigheter.
3. Du ger Icebeats en icke-exklusiv, världsomspännande licens att:
   – lagra spåret
   – streama/spela upp spåret via IceJockey-appen och webbplatsen
   – använda spåret i live-sportmiljöer (t.ex. ishallar och sportevenemang)
4. Du behåller fullt ägande och upphovsrätt till ditt verk.
5. Icebeats får visa ditt artistnamn för cred/attribuering.
6. Licensen är royalty-fri om inget annat avtalats skriftligen.
7. Om spåret skapats med AI-verktyg (t.ex. Suno) intygar du att du har de kommersiella rättigheter som krävs för att licensiera spåret för offentlig uppspelning.

Icebeats förbehåller sig rätten att när som helst ta bort vilket spår som helst efter eget gottfinnande.",

  'en' =>
"BY UPLOADING A TRACK TO ICEBEATS / ICEJOCKEY YOU CONFIRM THAT:

1. You are the original creator of the track (or you have all necessary rights/licenses to upload it).
2. The track does not infringe any third-party copyrights, trademarks, or other rights.
3. You grant Icebeats a non-exclusive, worldwide license to:
   – store the track
   – stream/play the track via the IceJockey app and website
   – use the track in live sports environments (e.g., arenas and sporting events)
4. You retain full ownership and copyright of your work.
5. Icebeats may display your artist name for credit/attribution.
6. This license is royalty-free unless otherwise agreed in writing.
7. If the track was created using AI tools (e.g., Suno), you confirm you have the commercial rights required to license it for public performance.

Icebeats reserves the right to remove any track at its discretion."
];

$S = $L[$lang] ?? $L['sv'];
?>
<style>
  .upl-wrap{max-width:980px;margin:0 auto;padding:0 16px}
  .card{
    max-width:720px;margin:14px auto 0;background:#0f172a;border:1px solid #1f2a44;
    border-radius:14px;padding:16px
  }
  h1{margin:0 0 6px;font-size:18px}
  .muted{color:#94a3b8;font-size:12px}
  label{display:block;font-size:12px;color:#a1a1aa;margin:10px 0 4px}
  input,select{width:100%;padding:10px;border-radius:10px;border:1px solid #24324f;background:#0b1220;color:#e5e7eb}
  .btn{cursor:pointer;border:0;border-radius:10px;padding:10px 12px;background:#2563eb;color:white;font-weight:900;width:100%;margin-top:12px}
  .btn:disabled{opacity:.55; cursor:not-allowed}
  .ok{color:#86efac;margin-top:10px}
  .err{color:#fca5a5;margin-top:10px}

  .termsBox{margin-top:12px;border:1px solid #24324f;background:#0b1220;border-radius:12px;padding:12px}
  .termsTitle{display:flex;align-items:center;justify-content:space-between;gap:10px;font-weight:900;font-size:13px;color:#e5e7eb;margin:0 0 8px}
  .termsText{
    max-height:180px;overflow:auto;padding:10px;border-radius:10px;border:1px solid #1f2a44;
    background:#0a1020;color:#cbd5e1;font-size:12px;line-height:1.45;white-space:pre-wrap
  }
  .agreeRow{display:flex;gap:10px;align-items:flex-start;margin-top:10px}
  .agreeRow input[type="checkbox"]{width:auto;margin-top:2px;transform:scale(1.15);accent-color:#60a5fa}
  .agreeLabel{font-size:12px;color:#e5e7eb;line-height:1.35;user-select:none}
  .agreeLabel small{display:block;color:#94a3b8;margin-top:4px;font-size:11px}
</style>

<div class="upl-wrap">
  <div class="card">
    <h1 id="h1"><?= htmlspecialchars($S['h1'], ENT_QUOTES, 'UTF-8') ?></h1>
    <div class="muted" id="sub"><?= htmlspecialchars($S['sub'], ENT_QUOTES, 'UTF-8') ?></div>

    <form id="f" enctype="multipart/form-data">
      <label id="labArtist"><?= htmlspecialchars($S['artist'], ENT_QUOTES, 'UTF-8') ?></label>
      <input name="artist" id="inpArtist" required placeholder="<?= htmlspecialchars($S['phArtist'], ENT_QUOTES, 'UTF-8') ?>">

      <label id="labTitle"><?= htmlspecialchars($S['title'], ENT_QUOTES, 'UTF-8') ?></label>
      <input name="title" id="inpTitle" required placeholder="<?= htmlspecialchars($S['phTitle'], ENT_QUOTES, 'UTF-8') ?>">

      <label id="labGenre"><?= htmlspecialchars($S['genre'], ENT_QUOTES, 'UTF-8') ?></label>
      <select name="genre" id="selGenre" required></select>

      <label id="labMp3"><?= htmlspecialchars($S['mp3'], ENT_QUOTES, 'UTF-8') ?></label>
      <input type="file" name="mp3" accept=".mp3,audio/mpeg" required>

      <label id="labCover"><?= htmlspecialchars($S['cover'], ENT_QUOTES, 'UTF-8') ?></label>
      <input type="file" name="cover" accept=".jpg,.jpeg,.png,.webp,.gif,image/*" required>

      <div class="termsBox" id="termsBox">
        <div class="termsTitle">
          <span id="termsTitle"><?= htmlspecialchars($S['termsTitle'], ENT_QUOTES, 'UTF-8') ?></span>
          <span class="muted" id="termsHint"><?= htmlspecialchars($S['termsHint'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <div class="termsText" id="termsText"></div>

        <div class="agreeRow">
          <input type="checkbox" id="agree" name="agree" value="1" required />
          <label for="agree" class="agreeLabel" id="agreeLabel">
            <span id="agreeText"><?= htmlspecialchars($S['agreeLabel'], ENT_QUOTES, 'UTF-8') ?></span>
            <small id="agreeSmall"><?= htmlspecialchars($S['agreeSmall'], ENT_QUOTES, 'UTF-8') ?></small>
          </label>
        </div>
      </div>

      <button class="btn" id="btnUpload" type="submit"><?= htmlspecialchars($S['upload'], ENT_QUOTES, 'UTF-8') ?></button>
      <div id="msg"></div>
    </form>
  </div>
</div>

<script>
const TOKEN = <?= json_encode($TOKEN) ?>;
const LANG  = <?= json_encode($lang, JSON_UNESCAPED_UNICODE) ?>;

const STR = <?= json_encode($L, JSON_UNESCAPED_UNICODE) ?>;
const TERMS = <?= json_encode($TERMS, JSON_UNESCAPED_UNICODE) ?>;

const GENRES = [
  { value:"Arena / Jingle", sv:"Arena / Jingle", en:"Arena / Jingle" },
  { value:"Organ / Charge", sv:"Organ / Charge", en:"Organ / Charge" },
  { value:"Rock", sv:"Rock", en:"Rock" },
  { value:"EDM", sv:"EDM", en:"EDM" },
  { value:"Hip-hop", sv:"Hip-hop", en:"Hip-hop" },
  { value:"Pop", sv:"Pop", en:"Pop" },
  { value:"Mål", sv:"Mål", en:"Goal" },
  { value:"Utvisning", sv:"Utvisning", en:"Penalty" },
  { value:"Periodpaus", sv:"Periodpaus", en:"Intermission" },
  { value:"Warmup", sv:"Warmup", en:"Warm-up" },
  { value:"Timeout", sv:"Timeout", en:"Timeout" },
  { value:"Övrigt", sv:"Övrigt", en:"Other" },
];

let lang = (LANG === "en") ? "en" : "sv";

const tg = (k, ...a) => {
  const v = STR[lang]?.[k];
  return typeof v === "function" ? v(...a) : (v ?? k);
};

function rebuildGenreOptions(){
  const sel = document.getElementById("selGenre");
  const keep = sel.value || "Övrigt";
  sel.innerHTML = GENRES.map(g => {
    const label = (lang === "en") ? g.en : g.sv;
    return `<option value="${g.value.replace(/"/g,'&quot;')}">${label}</option>`;
  }).join('');
  sel.value = keep;
  if (!sel.value) sel.value = "Övrigt";
}

function applyLang(){
  document.documentElement.lang = lang;

  document.getElementById("h1").textContent = STR[lang]?.h1 || "";
  document.getElementById("sub").textContent = STR[lang]?.sub || "";

  document.getElementById("labArtist").textContent = STR[lang]?.artist || "";
  document.getElementById("labTitle").textContent = STR[lang]?.title || "";
  document.getElementById("labGenre").textContent = STR[lang]?.genre || "";
  document.getElementById("labMp3").textContent = STR[lang]?.mp3 || "";
  document.getElementById("labCover").textContent = STR[lang]?.cover || "";

  document.getElementById("inpArtist").placeholder = STR[lang]?.phArtist || "";
  document.getElementById("inpTitle").placeholder = STR[lang]?.phTitle || "";

  document.getElementById("btnUpload").textContent = STR[lang]?.upload || "";

  document.getElementById("termsTitle").textContent = STR[lang]?.termsTitle || "";
  document.getElementById("termsHint").textContent = STR[lang]?.termsHint || "";
  document.getElementById("termsText").textContent = (TERMS[lang] || TERMS.en || "");

  document.getElementById("agreeText").textContent = STR[lang]?.agreeLabel || "";
  document.getElementById("agreeSmall").textContent = STR[lang]?.agreeSmall || "";

  rebuildGenreOptions();
}

applyLang();

document.getElementById('f').addEventListener('submit', async (e) => {
  e.preventDefault();

  const agree = document.getElementById("agree");
  if (!agree.checked) {
    const msg = document.getElementById('msg');
    msg.textContent = STR[lang]?.mustAgree || "You must accept the terms.";
    msg.className = 'err';
    return;
  }

  const msg = document.getElementById('msg');
  msg.textContent = STR[lang]?.uploading || "Uploading...";
  msg.className = 'muted';

  const fd = new FormData(e.target);
  fd.append('token', TOKEN);
  fd.append('lang', lang);
  fd.append('terms_version', 'ij-arena-license-v1');

  try {
    const res = await fetch('/api/upload.php', { method:'POST', body: fd });
    const text = await res.text();
    if (!res.ok) throw new Error(text || ('HTTP ' + res.status));
    let json = null;
    try { json = JSON.parse(text); } catch {}

    const id = (json && json.id) ? json.id : "ok";
    msg.textContent = (STR[lang]?.ok || "Uploaded: %s").replace('%s', id);
    msg.className = 'ok';

    e.target.reset();
    rebuildGenreOptions();
    applyLang();
  } catch (err) {
    const m = (err?.message || err || '').toString();
    msg.textContent = (STR[lang]?.fail || "Failed: %s").replace('%s', m);
    msg.className = 'err';
  }
});
</script>

<?php require __DIR__ . '/../_partials/footer.php'; ?>