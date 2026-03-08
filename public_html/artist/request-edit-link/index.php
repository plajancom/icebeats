<?php
declare(strict_types=1);

require __DIR__ . '/../../_partials/i18n.php';
require __DIR__ . '/../../_partials/meta.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$lang = ij_lang();

if (empty($_SESSION['creator_editlink_csrf'])) {
    $_SESSION['creator_editlink_csrf'] = bin2hex(random_bytes(16));
}

$prefillName = trim((string)($_GET['name'] ?? ''));

$pageTitleText = ($lang === 'en')
  ? 'iceBeats.io – Request edit link'
  : 'iceBeats.io – Begär redigeringslänk';

$pageDescText = ($lang === 'en')
  ? 'Request a magic edit link for your artist profile on iceBeats.io.'
  : 'Begär en magic link för att redigera din artistprofil på iceBeats.io.';

$canonical = ij_abs('/artist/request-edit-link/?lang=' . rawurlencode($lang));
if ($prefillName !== '') {
    $canonical .= '&name=' . rawurlencode($prefillName);
}

$meta = ij_build_meta([
  'title' => $pageTitleText,
  'description' => $pageDescText,
  'canonical' => $canonical,
  'image' => ij_abs('/share/og.jpg'),
  'type' => 'website',
  'extra' => [
    ['name' => 'robots', 'content' => 'noindex,follow'],
  ],
]);

$pageTitle = $meta['title'];
$pageHead = ij_render_meta($meta);

require __DIR__ . '/../../_partials/header.php';

$T = [
  'sv' => [
    'h1' => 'Begär redigeringslänk',
    'sub' => 'Om du redan har en verifierad artistprofil kan du få en tillfällig redigeringslänk skickad till din e-post.',
    'creator' => 'Artist',
    'email' => 'Din e-post',
    'send' => 'Skicka redigeringslänk',
    'sending' => 'Skickar…',
    'wait' => 'Vänta',
    'ph_creator' => 't.ex. Legendai',
    'ph_email' => 'din@email.se',
    'help' => 'Av säkerhetsskäl visar vi samma svar även om uppgifterna inte matchar en verifierad profil.',
    'required' => 'Fyll i artist och e-post.',
    'email_invalid' => 'Ange en giltig e-postadress.',
    'success' => 'Om uppgifterna stämmer har en redigeringslänk skickats till e-posten.',
    'error' => 'Något gick fel. Försök igen.',
    'back_creator' => 'Till artist-sidan',
  ],
  'en' => [
    'h1' => 'Request edit link',
    'sub' => 'If you already have a claimed artist profile, you can receive a temporary edit link by email.',
    'creator' => 'Artist',
    'email' => 'Your email',
    'send' => 'Send edit link',
    'sending' => 'Sending…',
    'wait' => 'Wait',
    'ph_creator' => 'e.g. Legendai',
    'ph_email' => 'your@email.com',
    'help' => 'For security reasons, we show the same response even if the details do not match a claimed profile.',
    'required' => 'Please fill in artist and email.',
    'email_invalid' => 'Please enter a valid email address.',
    'success' => 'If the details matched, an edit link has been sent to the email address.',
    'error' => 'Something went wrong. Please try again.',
    'back_creator' => 'Back to artist page',
  ],
];

$L = $T[$lang] ?? $T['sv'];

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>

<style>
.wrap{max-width:980px;margin:0 auto;padding:0 16px}
.card{
  background:#0f172a;
  border:1px solid #1f2a44;
  border-radius:16px;
  padding:20px;
  margin:14px 0;
}
.heroTitle{
  margin:0 0 8px;
  font-size:28px;
  line-height:1.08;
  color:#e5e7eb;
}
.muted{
  color:#94a3b8;
  font-size:13px;
  line-height:1.7;
}
.formWrap{max-width:680px}
.field{margin-top:12px}
.label{
  display:block;
  margin-bottom:6px;
  color:#cbd5e1;
  font-size:12px;
  font-weight:800;
}
.input{
  width:100%;
  border:1px solid #24324f;
  background:#0b1220;
  color:#e5e7eb;
  border-radius:12px;
  padding:12px 14px;
  font:inherit;
  box-sizing:border-box;
}
.input:focus{
  outline:none;
  border-color:#60a5fa;
  box-shadow:0 0 0 3px rgba(96,165,250,.12);
}
.actions{
  display:flex;
  align-items:center;
  gap:12px;
  flex-wrap:wrap;
  margin-top:16px;
}
.btn{
  cursor:pointer;
  border:0;
  border-radius:12px;
  padding:12px 16px;
  background:#2563eb;
  color:#fff;
  font-weight:900;
  position:relative;
  transition:transform .18s ease, box-shadow .18s ease, opacity .18s ease, background .18s ease;
  box-shadow:0 10px 24px rgba(37,99,235,.22);
}
.btn:hover:not(:disabled){
  transform:translateY(-1px);
  box-shadow:0 14px 32px rgba(37,99,235,.28);
}
.btn:disabled{
  opacity:.72;
  cursor:not-allowed;
  box-shadow:none;
}
.btn.isLocked{
  background:#334155;
  color:#cbd5e1;
}
.btn.isReady{
  animation:btnReadyPulse .6s ease;
}
@keyframes btnReadyPulse{
  0%{ box-shadow:0 0 0 rgba(37,99,235,0); transform:scale(1); }
  45%{ box-shadow:0 0 0 10px rgba(37,99,235,.10); transform:scale(1.015); }
  100%{ box-shadow:0 10px 24px rgba(37,99,235,.22); transform:scale(1); }
}
.btn.isSending{
  padding-left:42px;
}
.btnSpinner{
  position:absolute;
  left:14px;
  top:50%;
  width:16px;
  height:16px;
  margin-top:-8px;
  border-radius:50%;
  border:2px solid rgba(255,255,255,.28);
  border-top-color:#fff;
  animation:spin .8s linear infinite;
  display:none;
}
.btn.isSending .btnSpinner{
  display:block;
}
@keyframes spin{
  to{ transform:rotate(360deg); }
}
.status{
  font-size:13px;
  font-weight:800;
}
.status.ok{color:#86efac}
.status.err{color:#fca5a5}
.status.muted{color:#94a3b8}
.help{
  margin-top:6px;
  color:#94a3b8;
  font-size:12px;
  line-height:1.65;
}
.ctaRow{
  display:flex;
  gap:10px;
  flex-wrap:wrap;
  margin-top:14px;
}
.hp{
  position:absolute !important;
  left:-9999px !important;
  width:1px !important;
  height:1px !important;
  overflow:hidden !important;
}
</style>

<div class="wrap">
  <div class="card">
    <h1 class="heroTitle"><?= h($L['h1']) ?></h1>
    <div class="muted"><?= h($L['sub']) ?></div>

    <?php if ($prefillName !== ''): ?>
      <div class="ctaRow">
        <a class="ij-btnGhost" href="<?= h(ij_url('/artist/?name=' . rawurlencode($prefillName) . '&lang=' . $lang)) ?>">
          <?= h($L['back_creator']) ?>
        </a>
      </div>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="formWrap">
      <form id="editLinkForm" novalidate>
        <input type="hidden" name="lang" value="<?= h($lang) ?>">
        <input type="hidden" name="csrf" value="<?= h($_SESSION['creator_editlink_csrf']) ?>">
        <input type="hidden" name="form_loaded_at" id="form_loaded_at" value="">

        <div class="hp" aria-hidden="true">
          <label for="website">Website</label>
          <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="field">
          <label class="label" for="creator_name"><?= h($L['creator']) ?></label>
          <input class="input" type="text" id="creator_name" name="creator_name" value="<?= h($prefillName) ?>" placeholder="<?= h($L['ph_creator']) ?>" required>
        </div>

        <div class="field">
          <label class="label" for="email"><?= h($L['email']) ?></label>
          <input class="input" type="email" id="email" name="email" placeholder="<?= h($L['ph_email']) ?>" required>
        </div>

        <div class="actions">
          <button class="btn isLocked" id="sendBtn" type="submit">
            <span class="btnSpinner" aria-hidden="true"></span>
            <span id="sendBtnText"><?= h($L['send']) ?></span>
          </button>

          <div id="formStatus" class="status muted"></div>
        </div>

        <div class="help"><?= h($L['help']) ?></div>
      </form>
    </div>
  </div>
</div>

<script>
const STR = <?= json_encode([
  'required' => $L['required'],
  'email_invalid' => $L['email_invalid'],
  'sending' => $L['sending'],
  'success' => $L['success'],
  'error' => $L['error'],
  'send' => $L['send'],
  'wait' => $L['wait'],
], JSON_UNESCAPED_UNICODE) ?>;

const MIN_WAIT = 3;

document.getElementById('form_loaded_at').value = String(Math.floor(Date.now() / 1000));

const form = document.getElementById('editLinkForm');
const statusEl = document.getElementById('formStatus');
const sendBtn = document.getElementById('sendBtn');
const sendBtnText = document.getElementById('sendBtnText');

let waitLeft = MIN_WAIT;

function setStatus(text, cls = 'muted'){
  statusEl.className = 'status ' + cls;
  statusEl.textContent = text || '';
}

function setButtonLocked(secondsLeft){
  sendBtn.disabled = true;
  sendBtn.classList.add('isLocked');
  sendBtn.classList.remove('isReady', 'isSending');
  sendBtnText.textContent = `${STR.wait} ${secondsLeft}s`;
}

function setButtonReady(){
  sendBtn.disabled = false;
  sendBtn.classList.remove('isLocked', 'isSending');
  sendBtn.classList.add('isReady');
  sendBtnText.textContent = STR.send;
  setTimeout(() => sendBtn.classList.remove('isReady'), 700);
}

function setButtonSending(){
  sendBtn.disabled = true;
  sendBtn.classList.remove('isLocked', 'isReady');
  sendBtn.classList.add('isSending');
  sendBtnText.textContent = STR.sending;
}

setButtonLocked(waitLeft);

const waitTimer = setInterval(() => {
  waitLeft--;
  if (waitLeft <= 0){
    clearInterval(waitTimer);
    setButtonReady();
  } else {
    setButtonLocked(waitLeft);
  }
}, 1000);

form.addEventListener('submit', async (e) => {
  e.preventDefault();

  const fd = new FormData(form);

  const creator = String(fd.get('creator_name') || '').trim();
  const email = String(fd.get('email') || '').trim();

  if (!creator || !email) {
    setStatus(STR.required, 'err');
    return;
  }

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    setStatus(STR.email_invalid, 'err');
    return;
  }

  setButtonSending();
  setStatus(STR.sending, 'muted');

  try {
    const res = await fetch('/api/creator_send_edit_link.php', {
      method: 'POST',
      body: fd,
      cache: 'no-store',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });

    const text = await res.text();
    let json = {};
    try { json = JSON.parse(text); } catch {}

    if (!res.ok || !json.ok) {
      throw new Error(json.error || STR.error);
    }

    setStatus(json.message || STR.success, 'ok');
    setButtonReady();

  } catch (err) {
    setStatus((err && err.message) ? err.message : STR.error, 'err');
    setButtonReady();
  }
});
</script>

<?php require __DIR__ . '/../../_partials/footer.php'; ?>