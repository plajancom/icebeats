<?php
declare(strict_types=1);

require __DIR__ . '/../../_partials/i18n.php';
require __DIR__ . '/../../_partials/meta.php';
require __DIR__ . '/../../_partials/creator_lib.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$lang = ij_lang();

$token = trim((string)($_GET['token'] ?? ''));
$tokenRow = $token !== '' ? ij_creator_find_valid_token($token) : null;

$creator = null;
$creatorSlug = '';
if ($tokenRow && is_array($tokenRow)) {
    $creatorSlug = trim((string)($tokenRow['creator_slug'] ?? ''));
    if ($creatorSlug !== '') {
        $creator = ij_creator_find_by_slug($creatorSlug);
    }
}

if (empty($_SESSION['creator_edit_csrf'])) {
    $_SESSION['creator_edit_csrf'] = bin2hex(random_bytes(16));
}

$invalid = !$tokenRow || !$creator || !is_array($creator);

$pageTitleText = ($lang === 'en')
  ? 'iceBeats.io – Edit creator profile'
  : 'iceBeats.io – Redigera creatorprofil';

$pageDescText = ($lang === 'en')
  ? 'Edit your creator profile on iceBeats.io.'
  : 'Redigera din creatorprofil på iceBeats.io.';

$canonical = ij_abs('/artist/edit/?lang=' . rawurlencode($lang));

$meta = ij_build_meta([
  'title' => $pageTitleText,
  'description' => $pageDescText,
  'canonical' => $canonical,
  'image' => ij_abs('/share/og.jpg'),
  'type' => 'website',
  'extra' => [
    ['name' => 'robots', 'content' => 'noindex,nofollow'],
  ],
]);

$pageTitle = $meta['title'];
$pageHead = ij_render_meta($meta);

require __DIR__ . '/../../_partials/header.php';

$T = [
  'sv' => [
    'h1' => 'Redigera creatorprofil',
    'sub' => 'Uppdatera din profilinformation för iceBeats.io.',
    'invalidTitle' => 'Ogiltig eller utgången länk',
    'invalidText' => 'Den här redigeringslänken är ogiltig eller har gått ut. Begär en ny länk från creator-sidan.',
    'creator' => 'Creator',
    'bioSv' => 'Bio (svenska)',
    'bioEn' => 'Bio (engelska)',
    'image' => 'Profilbild URL',
    'imageUpload' => 'Ladda upp profilbild',
    'imageHelp' => 'JPG, PNG, WEBP eller GIF. Max 4 MB.',
    'currentImage' => 'Nuvarande profilbild',
    'website' => 'Webbplats',
    'instagram' => 'Instagram',
    'spotify' => 'Spotify',
    'save' => 'Spara profil',
    'saving' => 'Sparar…',
    'saved' => 'Profilen sparades.',
    'error' => 'Något gick fel. Försök igen.',
    'backCreator' => 'Öppna creator-sidan',
    'phBioSv' => 'Beskriv dig själv eller din musik på svenska…',
    'phBioEn' => 'Describe yourself or your music in English…',
    'phImage' => 'https://...',
    'phWebsite' => 'https://...',
    'phInstagram' => 'https://instagram.com/...',
    'phSpotify' => 'https://open.spotify.com/...',
  ],
  'en' => [
    'h1' => 'Edit creator profile',
    'sub' => 'Update your creator profile information for iceBeats.io.',
    'invalidTitle' => 'Invalid or expired link',
    'invalidText' => 'This edit link is invalid or has expired. Request a new link from the creator page.',
    'creator' => 'Creator',
    'bioSv' => 'Bio (Swedish)',
    'bioEn' => 'Bio (English)',
    'image' => 'Profile image URL',
    'imageUpload' => 'Upload profile image',
    'imageHelp' => 'JPG, PNG, WEBP or GIF. Max 4 MB.',
    'currentImage' => 'Current profile image',
    'website' => 'Website',
    'instagram' => 'Instagram',
    'spotify' => 'Spotify',
    'save' => 'Save profile',
    'saving' => 'Saving…',
    'saved' => 'Profile saved.',
    'error' => 'Something went wrong. Please try again.',
    'backCreator' => 'Open creator page',
    'phBioSv' => 'Describe yourself or your music in Swedish…',
    'phBioEn' => 'Describe yourself or your music in English…',
    'phImage' => 'https://...',
    'phWebsite' => 'https://...',
    'phInstagram' => 'https://instagram.com/...',
    'phSpotify' => 'https://open.spotify.com/...',
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
.formWrap{max-width:760px}
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
  min-height:140px;
  resize:vertical;
}
.fileInput{
  width:100%;
  border:1px dashed #334155;
  background:#0b1220;
  color:#e5e7eb;
  border-radius:12px;
  padding:12px 14px;
  box-sizing:border-box;
}
.previewWrap{
  margin-top:8px;
  display:flex;
  align-items:center;
  gap:12px;
  flex-wrap:wrap;
}
.previewImg{
  width:96px;
  height:96px;
  border-radius:18px;
  object-fit:cover;
  border:1px solid #24324f;
  background:#08101f;
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
  transition:transform .18s ease, box-shadow .18s ease, opacity .18s ease;
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
.ctaRow{
  display:flex;
  gap:10px;
  flex-wrap:wrap;
  margin-top:14px;
}
.help{
  margin-top:6px;
  color:#94a3b8;
  font-size:12px;
  line-height:1.6;
}
</style>

<div class="wrap">

  <?php if ($invalid): ?>
    <div class="card">
      <h1 class="heroTitle"><?= h($L['invalidTitle']) ?></h1>
      <div class="muted"><?= h($L['invalidText']) ?></div>
    </div>
  <?php else: ?>
    <div class="card">
      <h1 class="heroTitle"><?= h($L['h1']) ?></h1>
      <div class="muted"><?= h($L['sub']) ?></div>

      <div class="ctaRow">
        <a class="ij-btnGhost" href="<?= h(ij_url('/artist/?name=' . rawurlencode((string)$creator['name']) . '&lang=' . $lang)) ?>">
          <?= h($L['backCreator']) ?>
        </a>
      </div>
    </div>

    <div class="card">
      <div class="formWrap">
        <form id="editForm" method="post" enctype="multipart/form-data" novalidate>
          <input type="hidden" name="lang" value="<?= h($lang) ?>">
          <input type="hidden" name="csrf" value="<?= h($_SESSION['creator_edit_csrf']) ?>">
          <input type="hidden" name="token" value="<?= h($token) ?>">

          <div class="field">
            <label class="label"><?= h($L['creator']) ?></label>
            <input class="input" type="text" value="<?= h((string)$creator['name']) ?>" disabled>
          </div>

          <?php if (!empty($creator['image'])): ?>
            <div class="field">
              <label class="label"><?= h($L['currentImage']) ?></label>
              <div class="previewWrap">
                <img class="previewImg" src="<?= h((string)$creator['image']) ?>" alt="">
              </div>
            </div>
          <?php endif; ?>

          <div class="field">
            <label class="label" for="profile_image"><?= h($L['imageUpload']) ?></label>
            <input class="fileInput" type="file" id="profile_image" name="profile_image" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif">
            <div class="help"><?= h($L['imageHelp']) ?></div>
          </div>

          <div class="field">
            <label class="label" for="image"><?= h($L['image']) ?></label>
            <input class="input" type="url" id="image" name="image" value="<?= h((string)($creator['image'] ?? '')) ?>" placeholder="<?= h($L['phImage']) ?>">
          </div>

          <div class="field">
            <label class="label" for="bio_sv"><?= h($L['bioSv']) ?></label>
            <textarea class="textarea" id="bio_sv" name="bio_sv" placeholder="<?= h($L['phBioSv']) ?>"><?= h((string)($creator['bio_sv'] ?? '')) ?></textarea>
          </div>

          <div class="field">
            <label class="label" for="bio_en"><?= h($L['bioEn']) ?></label>
            <textarea class="textarea" id="bio_en" name="bio_en" placeholder="<?= h($L['phBioEn']) ?>"><?= h((string)($creator['bio_en'] ?? '')) ?></textarea>
          </div>

          <div class="field">
            <label class="label" for="website"><?= h($L['website']) ?></label>
            <input class="input" type="url" id="website" name="website" value="<?= h((string)($creator['links']['website'] ?? '')) ?>" placeholder="<?= h($L['phWebsite']) ?>">
          </div>

          <div class="field">
            <label class="label" for="instagram"><?= h($L['instagram']) ?></label>
            <input class="input" type="url" id="instagram" name="instagram" value="<?= h((string)($creator['links']['instagram'] ?? '')) ?>" placeholder="<?= h($L['phInstagram']) ?>">
          </div>

          <div class="field">
            <label class="label" for="spotify"><?= h($L['spotify']) ?></label>
            <input class="input" type="url" id="spotify" name="spotify" value="<?= h((string)($creator['links']['spotify'] ?? '')) ?>" placeholder="<?= h($L['phSpotify']) ?>">
          </div>

          <div class="actions">
            <button class="btn" id="saveBtn" type="submit">
              <span class="btnSpinner" aria-hidden="true"></span>
              <span id="saveBtnText"><?= h($L['save']) ?></span>
            </button>

            <div id="formStatus" class="status muted"></div>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?>

</div>

<?php if (!$invalid): ?>
<script>
const STR = <?= json_encode([
  'save' => $L['save'],
  'saving' => $L['saving'],
  'saved' => $L['saved'],
  'error' => $L['error'],
], JSON_UNESCAPED_UNICODE) ?>;

const form = document.getElementById('editForm');
const statusEl = document.getElementById('formStatus');
const saveBtn = document.getElementById('saveBtn');
const saveBtnText = document.getElementById('saveBtnText');

function setStatus(text, cls = 'muted'){
  statusEl.className = 'status ' + cls;
  statusEl.textContent = text || '';
}

function setSaving(on){
  saveBtn.disabled = !!on;
  saveBtn.classList.toggle('isSending', !!on);
  saveBtnText.textContent = on ? STR.saving : STR.save;
}

form.addEventListener('submit', async (e) => {
  e.preventDefault();

  const fd = new FormData(form);

  setSaving(true);
  setStatus(STR.saving, 'muted');

  try {
    const res = await fetch('/api/creator_save_profile.php', {
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

    setStatus(json.message || STR.saved, 'ok');

    if (json.image) {
      const current = document.querySelector('.previewImg');
      if (current) current.src = json.image + '?v=' + Date.now();
    }
  } catch (err) {
    setStatus((err && err.message) ? err.message : STR.error, 'err');
  } finally {
    setSaving(false);
  }
});
</script>
<?php endif; ?>

<?php require __DIR__ . '/../../_partials/footer.php'; ?>