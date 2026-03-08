<?php
declare(strict_types=1);

require __DIR__ . '/../_partials/i18n.php';
require __DIR__ . '/../_partials/meta.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$lang = ij_lang();

if (empty($_SESSION['contact_csrf'])) {
    $_SESSION['contact_csrf'] = bin2hex(random_bytes(16));
}

$pageTitleText = ($lang === 'en')
  ? 'iceBeats.io – Contact'
  : 'iceBeats.io – Kontakt';

$pageDescText = ($lang === 'en')
  ? 'Contact iceBeats.io regarding support, uploads, rights, partnerships and general questions.'
  : 'Kontakta iceBeats.io om support, uppladdningar, rättigheter, samarbeten och allmänna frågor.';

$meta = ij_build_meta([
  'title' => $pageTitleText,
  'description' => $pageDescText,
  'canonical' => ij_abs('/contact/?lang=' . $lang),
  'image' => ij_abs('/share/og.jpg'),
  'type' => 'website',
]);

$pageTitle = $meta['title'];
$pageHead = ij_render_meta($meta);

require __DIR__ . '/../_partials/header.php';

$T = [
  'sv' => [
    'h1' => 'Kontakta oss',
    'sub' => 'Har du frågor om låtar, uppladdningar, rättigheter eller samarbeten? Skicka ett meddelande så återkommer vi.',
    'card1_title' => 'E-post',
    'card1_text' => 'Skicka ett meddelande direkt till vårt team via formuläret nedan.',
    'card2_title' => 'Ärendetyp',
    'card2_text' => 'Vanliga ämnen är support, uploads, rättigheter och samarbeten.',
    'card3_title' => 'Svarstid',
    'card3_text' => 'Vi försöker normalt svara så snabbt vi kan.',
    'direct_title' => 'Föredrar du e-post?',
    'direct_text' => 'Du kan även kontakta oss direkt via e-post.',
    'name' => 'Namn',
    'email' => 'E-post',
    'subject' => 'Ämne',
    'message' => 'Meddelande',
    'ph_name' => 'Ditt namn',
    'ph_email' => 'din@email.se',
    'ph_subject' => 'Vad gäller det?',
    'ph_message' => 'Skriv ditt meddelande här…',
    'send' => 'Skicka meddelande',
    'sending' => 'Skickar…',
    'success' => 'Tack! Ditt meddelande är skickat.',
    'error' => 'Något gick fel. Försök igen.',
    'required' => 'Fyll i alla fält.',
    'email_invalid' => 'Ange en giltig e-postadress.',
    'foot' => 'Du får ett autosvar till din e-post när meddelandet skickats.',
    'wait' => 'Vänta',
    'delivered' => 'Meddelandet skickat',
    'reply_24h' => 'Vi svarar normalt inom 24h',
  ],
  'en' => [
    'h1' => 'Contact us',
    'sub' => 'Do you have questions about tracks, uploads, rights or partnerships? Send us a message and we will get back to you.',
    'card1_title' => 'Email',
    'card1_text' => 'Send a message directly to our team using the form below.',
    'card2_title' => 'Topics',
    'card2_text' => 'Common topics include support, uploads, rights and partnerships.',
    'card3_title' => 'Response time',
    'card3_text' => 'We normally try to reply as quickly as possible.',
    'direct_title' => 'Prefer email?',
    'direct_text' => 'You can also contact us directly by email.',
    'name' => 'Name',
    'email' => 'Email',
    'subject' => 'Subject',
    'message' => 'Message',
    'ph_name' => 'Your name',
    'ph_email' => 'your@email.com',
    'ph_subject' => 'What is it about?',
    'ph_message' => 'Write your message here…',
    'send' => 'Send message',
    'sending' => 'Sending…',
    'success' => 'Thanks! Your message has been sent.',
    'error' => 'Something went wrong. Please try again.',
    'required' => 'Please fill in all fields.',
    'email_invalid' => 'Please enter a valid email address.',
    'foot' => 'You will receive an auto-reply by email when the message has been sent.',
    'wait' => 'Wait',
    'delivered' => 'Message delivered',
    'reply_24h' => 'We normally reply within 24h',
  ],
];

$L = $T[$lang] ?? $T['sv'];

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
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
.heroTitle{
  margin:0 0 8px;
  font-size:28px;
  line-height:1.1;
  letter-spacing:.2px;
}
.muted{
  color:#94a3b8;
  font-size:13px;
  line-height:1.7;
}

.infoGrid{
  display:grid;
  grid-template-columns:repeat(3,1fr);
  gap:12px;
  margin-top:14px;
}
@media (max-width: 900px){
  .infoGrid{grid-template-columns:1fr}
}
.infoCard{
  background:#0b1220;
  border:1px solid #24324f;
  border-radius:14px;
  padding:16px;
  position:relative;
  overflow:hidden;
}
.infoCard::after{
  content:"";
  position:absolute;
  right:-24px;
  bottom:-24px;
  width:100px;
  height:100px;
  background:radial-gradient(circle, rgba(96,165,250,.10), transparent 70%);
  pointer-events:none;
}
.infoTitle{
  font-size:14px;
  font-weight:950;
  color:#e5e7eb;
  margin-bottom:6px;
}
.infoText{
  color:#94a3b8;
  font-size:12px;
  line-height:1.65;
}

.directContact{
  display:flex;
  align-items:center;
  gap:16px;
  flex-wrap:wrap;
}
.directContactIcon{
  font-size:28px;
  width:52px;
  height:52px;
  display:flex;
  align-items:center;
  justify-content:center;
  border-radius:14px;
  background:#0b1220;
  border:1px solid #24324f;
}
.directContactText{
  flex:1;
  min-width:200px;
}
.directContactTitle{
  font-size:15px;
  font-weight:900;
  color:#e5e7eb;
}
.directContactDesc{
  font-size:12px;
  color:#94a3b8;
  margin-top:4px;
}
.directContactBtn{
  text-decoration:none;
  padding:10px 14px;
  border-radius:12px;
  background:#0b1220;
  border:1px solid #24324f;
  color:#60a5fa;
  font-weight:800;
  transition:all .18s ease;
}
.directContactBtn:hover{
  border-color:#60a5fa;
  background:#08101f;
}

.formWrap{
  max-width:760px;
}
.row{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:12px;
}
@media (max-width: 720px){
  .row{grid-template-columns:1fr}
}
.field{margin-top:12px}
.label{
  display:block;
  margin-bottom:6px;
  color:#cbd5e1;
  font-size:12px;
  font-weight:800;
}
.input,.textarea{
  width:100%;
  border:1px solid #24324f;
  background:#0b1220;
  color:#e5e7eb;
  border-radius:12px;
  padding:12px 14px;
  font:inherit;
  box-sizing:border-box;
}
.input:focus,.textarea:focus{
  outline:none;
  border-color:#60a5fa;
  box-shadow:0 0 0 3px rgba(96,165,250,.12);
}
.textarea{
  min-height:180px;
  resize:vertical;
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
  transition:
    transform .18s ease,
    box-shadow .18s ease,
    opacity .18s ease,
    background .18s ease;
  box-shadow:0 10px 24px rgba(37,99,235,.22);
}
.btn:hover:not(:disabled){
  transform:translateY(-1px);
  box-shadow:0 14px 32px rgba(37,99,235,.28);
}
.btn:active:not(:disabled){
  transform:translateY(0);
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

.deliveryBadge{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:8px 12px;
  border-radius:999px;
  border:1px solid rgba(134,239,172,.25);
  background:rgba(22,101,52,.18);
  color:#bbf7d0;
  font-size:12px;
  font-weight:900;
  opacity:0;
  transform:translateY(6px) scale(.98);
  transition:opacity .24s ease, transform .24s ease;
  pointer-events:none;
}
.deliveryBadge.show{
  opacity:1;
  transform:translateY(0) scale(1);
}
.deliveryDot{
  width:8px;
  height:8px;
  border-radius:999px;
  background:#86efac;
  box-shadow:0 0 10px rgba(134,239,172,.55);
}

.responseBadge{
  display:inline-flex;
  align-items:center;
  gap:8px;
  margin-top:14px;
  padding:8px 12px;
  border-radius:999px;
  border:1px solid rgba(96,165,250,.22);
  background:rgba(37,99,235,.12);
  color:#bfdbfe;
  font-size:12px;
  font-weight:800;
}
.responseBadgeDot{
  width:8px;
  height:8px;
  border-radius:999px;
  background:#60a5fa;
  box-shadow:0 0 10px rgba(96,165,250,.45);
}

.note{
  margin-top:12px;
  color:#94a3b8;
  font-size:12px;
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

    <div class="infoGrid">
      <div class="infoCard">
        <div class="infoTitle"><?= h($L['card1_title']) ?></div>
        <div class="infoText"><?= h($L['card1_text']) ?></div>
      </div>
      <div class="infoCard">
        <div class="infoTitle"><?= h($L['card2_title']) ?></div>
        <div class="infoText"><?= h($L['card2_text']) ?></div>
      </div>
      <div class="infoCard">
        <div class="infoTitle"><?= h($L['card3_title']) ?></div>
        <div class="infoText"><?= h($L['card3_text']) ?></div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="directContact">
      <div class="directContactIcon">✉️</div>

      <div class="directContactText">
        <div class="directContactTitle"><?= h($L['direct_title']) ?></div>
        <div class="directContactDesc"><?= h($L['direct_text']) ?></div>
      </div>

      <a class="directContactBtn" href="mailto:hello@icebeats.io?subject=iceBeats contact">
        hello@icebeats.io
      </a>
    </div>
  </div>

  <div class="card">
    <div class="formWrap">
      <form id="contactForm" novalidate>
        <input type="hidden" name="lang" value="<?= h($lang) ?>">
        <input type="hidden" name="csrf" value="<?= h($_SESSION['contact_csrf']) ?>">
        <input type="hidden" name="form_loaded_at" id="form_loaded_at" value="">

        <div class="hp" aria-hidden="true">
          <label for="website">Website</label>
          <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="row">
          <div class="field">
            <label class="label" for="name"><?= h($L['name']) ?></label>
            <input class="input" type="text" id="name" name="name" placeholder="<?= h($L['ph_name']) ?>" required>
          </div>
          <div class="field">
            <label class="label" for="email"><?= h($L['email']) ?></label>
            <input class="input" type="email" id="email" name="email" placeholder="<?= h($L['ph_email']) ?>" required>
          </div>
        </div>

        <div class="field">
          <label class="label" for="subject"><?= h($L['subject']) ?></label>
          <input class="input" type="text" id="subject" name="subject" placeholder="<?= h($L['ph_subject']) ?>" required>
        </div>

        <div class="field">
          <label class="label" for="message"><?= h($L['message']) ?></label>
          <textarea class="textarea" id="message" name="message" placeholder="<?= h($L['ph_message']) ?>" required></textarea>
        </div>

        <div class="actions">
          <button class="btn isLocked" id="sendBtn" type="submit">
            <span class="btnSpinner" aria-hidden="true"></span>
            <span id="sendBtnText"><?= h($L['send']) ?></span>
          </button>

          <div id="formStatus" class="status muted"></div>

          <div id="deliveryBadge" class="deliveryBadge" aria-live="polite">
            <span class="deliveryDot"></span>
            <span id="deliveryBadgeText"><?= h($L['delivered']) ?></span>
          </div>
        </div>

        <div class="responseBadge">
          <span class="responseBadgeDot"></span>
          <span><?= h($L['reply_24h']) ?></span>
        </div>

        <div class="note"><?= h($L['foot']) ?></div>
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
  'delivered' => $L['delivered'],
], JSON_UNESCAPED_UNICODE) ?>;

const MIN_WAIT = 3;

document.getElementById('form_loaded_at').value = String(Math.floor(Date.now() / 1000));

const form = document.getElementById('contactForm');
const statusEl = document.getElementById('formStatus');
const sendBtn = document.getElementById('sendBtn');
const sendBtnText = document.getElementById('sendBtnText');
const deliveryBadge = document.getElementById('deliveryBadge');
const deliveryBadgeText = document.getElementById('deliveryBadgeText');

let waitLeft = MIN_WAIT;

function setStatus(text, cls = 'muted'){
  statusEl.className = 'status ' + cls;
  statusEl.textContent = text || '';
}

function hideDeliveryBadge(){
  deliveryBadge.classList.remove('show');
}

function showDeliveryBadge(text){
  deliveryBadgeText.textContent = text || STR.delivered;
  deliveryBadge.classList.add('show');
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

  hideDeliveryBadge();

  const fd = new FormData(form);
  const name = String(fd.get('name') || '').trim();
  const email = String(fd.get('email') || '').trim();
  const subject = String(fd.get('subject') || '').trim();
  const message = String(fd.get('message') || '').trim();

  if (!name || !email || !subject || !message) {
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
    const res = await fetch('/api/contact.php', {
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

    form.reset();
    document.getElementById('form_loaded_at').value = String(Math.floor(Date.now() / 1000));

    setStatus(json.message || STR.success, 'ok');
    showDeliveryBadge(STR.delivered);
    setButtonReady();

  } catch (err) {
    setStatus((err && err.message) ? err.message : STR.error, 'err');
    setButtonReady();
  }
});
</script>

<?php require __DIR__ . '/../_partials/footer.php'; ?>