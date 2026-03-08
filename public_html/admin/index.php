<?php
// /admin/index.php
declare(strict_types=1);

require __DIR__ . '/lib.php';
require __DIR__ . '/../_partials/i18n.php';
require __DIR__ . '/../_partials/meta.php';

$lang = ij_lang();
$msg = (string)($_GET['msg'] ?? '');

/* Pending list */
$pending = [];
foreach (glob($PENDING_DIR . '/*', GLOB_ONLYDIR) ?: [] as $dir) {
  $meta = read_json($dir . '/meta.json', null);
  if ($meta) $pending[] = $meta;
}
usort($pending, fn($a,$b)=> ($b['createdAt'] ?? 0) <=> ($a['createdAt'] ?? 0));

$db = read_json($DB_PATH, ['items' => []]);
$allItems = is_array($db['items'] ?? null) ? $db['items'] : [];
$approvedCount = count($allItems);

// ✅ nya stats (active/blocked) – bygger på ij_is_track_active() från lib.php
$activeCount = 0;
foreach ($allItems as $it) {
  if (is_array($it) && function_exists('ij_is_track_active') && ij_is_track_active($it)) {
    $activeCount++;
  } elseif (!function_exists('ij_is_track_active')) {
    // fallback: om någon råkar köra gammal lib.php
    $activeCount++;
  }
}
$blockedCount = max(0, $approvedCount - $activeCount);

$genreSuggestions = [
  "Arena / Jingle",
  "Organ / Charge",
  "Rock",
  "EDM",
  "Hip-hop",
  "Pop",
  "Mål",
  "Utvisning",
  "Periodpaus",
  "Warmup",
  "Timeout",
  "Övrigt",
];

$STR = [
  'sv' => [
    'title' => 'iceBeats.io - Admin',
    'statsPublic' => 'Public:',
    'approved' => 'Totalt:',
    'active' => 'Aktiva:',
    'blocked' => 'Spärrade:',
    'pending' => 'Pending:',
    'uploadH' => 'Ladda upp ny låt (hamnar i Pending)',
    'uploadSub' => 'Lägger filerna i pending så du kan godkänna/avslå.',
    'artist' => 'Artist',
    'trackTitle' => 'Titel',
    'genre' => 'Genre',
    'genreHint' => 'Välj gärna en konsekvent genre (för filtrering på publika sidan).',
    'mp3' => 'MP3-fil',
    'cover' => 'Omslagsbild',
    'max' => 'Max',
    'btnPending' => '⬆️ Lägg i pending',
    'pendingH' => 'Pending (kräver godkännande)',
    'noPending' => 'Inga pending-låtar just nu.',
    'colInfo' => 'Info',
    'colPreview' => 'Förhandslyssna',
    'colAction' => 'Åtgärd',
    'noMp3' => 'Ingen MP3',
    'id' => 'ID:',
    'approve' => '✅ Godkänn',
    'reject' => '🗑️ Neka',
    'rejectConfirm' => 'Ta bort från pending?',
    'links' => 'Snabblänkar',
    'toPublic' => '🌐 Tracks',
    'toTop' => '🔥 Top',
    'toApiDocs' => '🧩 API Docs',
    'toApiKeys' => '🔑 API Keys',
    'toManage' => '🎛 Hantera tracks',
    'toCreatorClaims' => '🪪 Hantera Artist claims',
  ],
  'en' => [
    'title' => 'iceBeats.io - Admin',
    'statsPublic' => 'Public:',
    'approved' => 'Total:',
    'active' => 'Active:',
    'blocked' => 'Blocked:',
    'pending' => 'Pending:',
    'uploadH' => 'Upload new track (goes to Pending)',
    'uploadSub' => 'Uploads to pending so you can approve/reject.',
    'artist' => 'Artist',
    'trackTitle' => 'Title',
    'genre' => 'Genre',
    'genreHint' => 'Pick a consistent genre (used for filtering on the public site).',
    'mp3' => 'MP3 file',
    'cover' => 'Cover image',
    'max' => 'Max',
    'btnPending' => '⬆️ Add to pending',
    'pendingH' => 'Pending (needs approval)',
    'noPending' => 'No pending tracks right now.',
    'colInfo' => 'Info',
    'colPreview' => 'Preview',
    'colAction' => 'Action',
    'noMp3' => 'No MP3',
    'id' => 'ID:',
    'approve' => '✅ Approve',
    'reject' => '🗑️ Reject',
    'rejectConfirm' => 'Remove from pending?',
    'links' => 'Quick links',
    'toPublic' => '🌐 Tracks',
    'toTop' => '🔥 Top',
    'toApiDocs' => '🧩 API Docs',
    'toApiKeys' => '🔑 API Keys',
    'toManage' => '🎛 Manage tracks',
    'toCreatorClaims' => '🪪 Creator claims',
  ],
];

$S = $STR[$lang] ?? $STR['sv'];

$pageDescText = ($lang === 'en')
  ? 'Admin dashboard for managing pending uploads, tracks and audio moderation on iceBeats.io.'
  : 'Adminpanel för att hantera pending-uppladdningar, låtar och moderering på iceBeats.io.';

$meta = ij_build_meta([
  'title' => $S['title'],
  'description' => $pageDescText,
  'canonical' => ij_abs('/admin/?lang=' . $lang),
  'image' => ij_abs('/share/og.jpg'),
  'type' => 'website',
  'extra' => [
    ['name' => 'robots', 'content' => 'noindex,nofollow'],
  ],
]);

$pageTitle = $meta['title'];
$pageHead = ij_render_meta($meta);

require __DIR__ . '/../_partials/header.php';

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<style>
  .wrap{max-width:980px;margin:0 auto;padding:0 16px}
  .card{background:#0f172a;border:1px solid #1f2a44;border-radius:14px;padding:16px;margin:14px 0}
  h1{margin:0;font-size:20px}
  h2{margin:0 0 8px;font-size:16px}
  label{display:block;font-size:12px;color:#a1a1aa;margin:10px 0 4px}
  input[type=text],input[type=file],select{
    width:100%;padding:10px;border-radius:10px;border:1px solid #24324f;background:#0b1220;color:#e5e7eb
  }
  .row{display:flex;gap:12px;flex-wrap:wrap}
  .row > div{flex:1;min-width:220px}
  .muted{color:#94a3b8;font-size:12px}
  .topbar{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;margin-top:14px}
  .btn{
    cursor:pointer;border:0;border-radius:10px;padding:10px 12px;background:#2563eb;color:white;font-weight:900
  }
  .btnDanger{background:#ef4444}
  .btnGhost{
    cursor:pointer;border-radius:10px;padding:10px 12px;border:1px solid #334155;background:#0b1220;color:#e5e7eb;font-weight:900;
    text-decoration:none;display:inline-flex;align-items:center;gap:8px
  }
  .btnGhost:hover{border-color:#475569}
  code{background:#0b1220;border:1px solid #24324f;border-radius:8px;padding:2px 6px}
  table{width:100%;border-collapse:collapse;margin-top:10px}
  td,th{padding:10px;border-top:1px solid #1f2a44;vertical-align:top;text-align:left}
  th{color:#cbd5e1;background:#0b1220;position:sticky;top:0}
 
  audio{width:260px;max-width:100%}
  .tag{display:inline-block;font-size:12px;color:#e5e7eb;background:#0b1220;border:1px solid #24324f;border-radius:999px;padding:2px 8px}
  .notice{border-color:#334155;background:#0b1220}
  .actionsRight{white-space:nowrap;text-align:right}
  .actionsRight form{display:inline}
  .actionsRight form + form{margin-left:8px}
  .linkRow{display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:flex-end}

.card table img{
  max-width:72px;
  border-radius:10px;
  border:1px solid #1f2a44;
}

  @media (max-width: 720px){
    .actionsRight{white-space:normal}
    .actionsRight form{display:block}
    .actionsRight form + form{margin-left:0;margin-top:8px}
    audio{width:100%}
    .linkRow{justify-content:flex-start}
  }
</style>

<div class="wrap">

  <div class="topbar">
    <div>
      <h1><?= h($S['title']) ?></h1>
      <div class="muted" style="margin-top:6px;">
        <?= h($S['statsPublic']) ?> <code>/library.json</code> •
        <?= h($S['approved']) ?> <b><?= (int)$approvedCount ?></b> •
        <?= h($S['active']) ?> <b><?= (int)$activeCount ?></b> •
        <?= h($S['blocked']) ?> <b><?= (int)$blockedCount ?></b> •
        <?= h($S['pending']) ?> <b><?= (int)count($pending) ?></b>
      </div>
    </div>

    <div>
      <div class="muted" style="margin-bottom:6px;"><?= h($S['links']) ?></div>
      <div class="linkRow">
        <a class="btnGhost" href="/?lang=<?= h($lang) ?>"><?= h($S['toPublic']) ?></a>
        <a class="btnGhost" href="/top/?lang=<?= h($lang) ?>"><?= h($S['toTop']) ?></a>
        <a class="btnGhost" href="/api/?lang=<?= h($lang) ?>"><?= h($S['toApiDocs']) ?></a>
        <a class="btnGhost" href="/admin/api_keys.php?lang=<?= h($lang) ?>"><?= h($S['toApiKeys']) ?></a>
        <a class="btnGhost" href="/admin/creator_claims/?lang=<?= h($lang) ?>"><?= h($S['toCreatorClaims']) ?></a>
        <a class="btnGhost" href="/admin/tracks.php?lang=<?= h($lang) ?>"><?= h($S['toManage']) ?></a>
      </div>
    </div>
  </div>

  <?php if ($msg): ?>
    <div class="card notice">
      ✅ <?= h($msg) ?>
    </div>
  <?php endif; ?>

  <div class="card">
    <h2><?= h($S['uploadH']) ?></h2>
    <div class="muted" style="margin-bottom:8px;"><?= h($S['uploadSub']) ?></div>

    <form method="post" action="actions.php" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="action" value="upload">

      <div class="row">
        <div>
          <label><?= h($S['artist']) ?></label>
          <input type="text" name="artist" required>
        </div>
        <div>
          <label><?= h($S['trackTitle']) ?></label>
          <input type="text" name="title" required>
        </div>
        <div>
          <label><?= h($S['genre']) ?></label>
          <select name="genre" required>
            <?php foreach ($genreSuggestions as $g): ?>
              <option value="<?= h($g) ?>"><?= h($g) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="muted"><?= h($S['genreHint']) ?></div>
        </div>
      </div>

      <div class="row">
        <div>
          <label><?= h($S['mp3']) ?></label>
          <input type="file" name="mp3" accept=".mp3,audio/mpeg" required>
          <div class="muted"><?= h($S['max']) ?> <?= (int)$MAX_MP3_MB ?> MB</div>
        </div>
        <div>
          <label><?= h($S['cover']) ?></label>
          <input type="file" name="cover" accept=".jpg,.jpeg,.png,.webp,.gif,image/*" required>
          <div class="muted"><?= h($S['max']) ?> <?= (int)$MAX_IMG_MB ?> MB</div>
        </div>
      </div>

      <div style="margin-top:12px;">
        <button class="btn" type="submit"><?= h($S['btnPending']) ?></button>
      </div>
    </form>
  </div>

  <div class="card">
    <h2><?= h($S['pendingH']) ?></h2>

    <?php if (!count($pending)): ?>
      <div class="muted"><?= h($S['noPending']) ?></div>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th><?= h($S['colInfo']) ?></th>
            <th><?= h($S['colPreview']) ?></th>
            <th class="actionsRight"><?= h($S['colAction']) ?></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($pending as $p):
          $id = (string)($p['id'] ?? '');
          $cover = 'pending/' . $id . '/' . ($p['cover'] ?? '');
          $mp3   = 'pending/' . $id . '/' . ($p['mp3'] ?? '');
          $genre = htmlspecialchars(sanitize_genre((string)($p['genre'] ?? 'Övrigt')), ENT_QUOTES, 'UTF-8');
        ?>
          <tr>
            <td>
              <div style="display:flex;gap:12px;align-items:flex-start;">
                <div>
                  <?php if (!empty($p['cover'])): ?>
                    <img src="<?= h($cover) ?>" alt="">
                  <?php endif; ?>
                </div>
                <div style="min-width:0">
                  <div style="font-weight:900;"><?= h($p['title'] ?? '—') ?></div>
                  <div class="muted"><?= h($p['artist'] ?? '') ?></div>
                  <div style="margin-top:6px;"><span class="tag"><?= $genre ?></span></div>
                  <div class="muted" style="margin-top:6px;"><?= h($S['id']) ?> <code><?= h($id) ?></code></div>
                </div>
              </div>
            </td>
            <td>
              <?php if (!empty($p['mp3'])): ?>
                <audio controls preload="none" src="<?= h($mp3) ?>"></audio>
              <?php else: ?>
                <span class="muted"><?= h($S['noMp3']) ?></span>
              <?php endif; ?>
            </td>
            <td class="actionsRight">
              <form method="post" action="actions.php">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="id" value="<?= h($id) ?>">
                <button class="btn" type="submit"><?= h($S['approve']) ?></button>
              </form>

              <form method="post" action="actions.php" onsubmit="return confirm(<?= json_encode($S['rejectConfirm'], JSON_UNESCAPED_UNICODE) ?>)">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="id" value="<?= h($id) ?>">
                <button class="btn btnDanger" type="submit"><?= h($S['reject']) ?></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

</div>

<?php require __DIR__ . '/../_partials/footer.php'; ?>