<?php
// /domains/audio.icejockey.app/public_html/admin/tracks.php
declare(strict_types=1);

require __DIR__ . '/lib.php';
require __DIR__ . '/../_partials/i18n.php';

$lang = ij_lang();
$msg = (string)($_GET['msg'] ?? '');

$db = read_json($DB_PATH, ['items' => []]);
$items = is_array($db['items'] ?? null) ? $db['items'] : [];

usort($items, fn($a,$b)=> (int)($b['createdAt'] ?? 0) <=> (int)($a['createdAt'] ?? 0));

$active = 0;
foreach ($items as $it) {
  if (is_array($it) && ij_is_track_active($it)) $active++;
}
$blocked = max(0, count($items) - $active);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$STR = [
  'sv' => [
    'title' => 'Hantera tracks',
    'back' => '← Till dashboard',
    'total' => 'Totalt',
    'active' => 'Aktiva',
    'blocked' => 'Spärrade',
    'search' => 'Sök titel/artist/id…',
    'status' => 'Status',
    'status_active' => 'Aktiv',
    'status_blocked' => 'Spärrad',
    'preview' => 'Förhandslyssna',
    'actions' => 'Åtgärder',
    'block' => 'Spärra',
    'unblock' => 'Aktivera',
    'delete' => 'Radera',
    'deleteConfirm' => 'Radera låten? Detta tar bort från biblioteket och försöker radera filerna.',
    'blockedNote' => 'Spärrade låtar syns inte publikt (library.json filtreras).',
  ],
  'en' => [
    'title' => 'Manage tracks',
    'back' => '← Back to dashboard',
    'total' => 'Total',
    'active' => 'Active',
    'blocked' => 'Blocked',
    'search' => 'Search title/artist/id…',
    'status' => 'Status',
    'status_active' => 'Active',
    'status_blocked' => 'Blocked',
    'preview' => 'Preview',
    'actions' => 'Actions',
    'block' => 'Block',
    'unblock' => 'Unblock',
    'delete' => 'Delete',
    'deleteConfirm' => 'Delete this track? This removes it from the library and attempts to delete files.',
    'blockedNote' => 'Blocked tracks are not public (library.json is filtered).',
  ],
];

$S = $STR[$lang] ?? $STR['sv'];

$pageTitle = 'IceJockey Admin – ' . $S['title'];
$pageHead = '';
require __DIR__ . '/../_partials/header.php';
?>
<style>
  .wrap{max-width:1100px;margin:0 auto;padding:0 16px}
  .topbar{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:flex-end;margin-top:14px}
  .card{background:#0f172a;border:1px solid #1f2a44;border-radius:14px;padding:16px;margin:14px 0}
  h1{margin:0;font-size:20px}
  .muted{color:#94a3b8;font-size:12px}
  .btnGhost{
    cursor:pointer;border-radius:10px;padding:10px 12px;border:1px solid #334155;background:#0b1220;color:#e5e7eb;font-weight:900;
    text-decoration:none;display:inline-flex;align-items:center;gap:8px
  }
  .btnGhost:hover{border-color:#475569}
  .btn{cursor:pointer;border:0;border-radius:10px;padding:9px 11px;background:#2563eb;color:white;font-weight:900}
  .btnDanger{background:#ef4444}
  .btnWarn{background:#f59e0b;color:#111827}
  .badge{display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:3px 10px;font-weight:900;font-size:12px}
  .badge.on{background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.35);color:#86efac}
  .badge.off{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.35);color:#fca5a5}
  .toolbar{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-top:10px}
  .inp{flex:1;min-width:240px;padding:10px;border-radius:10px;border:1px solid #24324f;background:#0b1220;color:#e5e7eb}
  table{width:100%;border-collapse:collapse;margin-top:10px}
  td,th{padding:10px;border-top:1px solid #1f2a44;vertical-align:top;text-align:left}
  th{color:#cbd5e1;background:#0b1220;position:sticky;top:0}
  audio{width:260px;max-width:100%}
  code{background:#0b1220;border:1px solid #24324f;border-radius:8px;padding:2px 6px}
  .rowTitle{font-weight:950}
  .actions{white-space:nowrap}
  .actions form{display:inline}
  .actions form + form{margin-left:8px}
  @media (max-width: 720px){
    .actions{white-space:normal}
    .actions form{display:block}
    .actions form + form{margin-left:0;margin-top:8px}
    audio{width:100%}
  }
</style>

<div class="wrap">

  <div class="topbar">
    <div>
      <a class="btnGhost" href="/admin/?lang=<?= h($lang) ?>"><?= h($S['back']) ?></a>
      <h1 style="margin-top:10px;"><?= h($S['title']) ?></h1>
      <div class="muted" style="margin-top:6px;">
        <?= h($S['total']) ?>: <b><?= (int)count($items) ?></b> •
        <?= h($S['active']) ?>: <b><?= (int)$active ?></b> •
        <?= h($S['blocked']) ?>: <b><?= (int)$blocked ?></b>
      </div>
      <div class="muted" style="margin-top:6px;"><?= h($S['blockedNote']) ?></div>
    </div>

    <div class="card" style="margin:0; padding:12px 14px;">
      <?php if ($msg): ?>
        ✅ <?= h($msg) ?>
      <?php else: ?>
        <span class="muted">—</span>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="toolbar">
      <input class="inp" id="q" placeholder="<?= h($S['search']) ?>">
    </div>

    <table id="tbl">
      <thead>
        <tr>
          <th>Info</th>
          <th><?= h($S['status']) ?></th>
          <th><?= h($S['preview']) ?></th>
          <th><?= h($S['actions']) ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $it):
          if (!is_array($it)) continue;
          $id = (string)($it['id'] ?? '');
          $title = (string)($it['title'] ?? '—');
          $artist = (string)($it['artist'] ?? '');
          $genre = sanitize_genre((string)($it['genre'] ?? 'Övrigt'));
          $createdAt = (int)($it['createdAt'] ?? 0);
          $statusActive = ij_is_track_active($it);

          $url = (string)($it['url'] ?? '');
          $previewUrl = $url ? h($url) : '';
        ?>
        <tr data-hay="<?= h(strtolower($id.' '.$title.' '.$artist)) ?>">
          <td>
            <div class="rowTitle"><?= h($title) ?></div>
            <div class="muted"><?= h($artist) ?></div>
            <div class="muted" style="margin-top:6px;">
              <span class="tag" style="display:inline-block;font-size:12px;border:1px solid #24324f;background:#0b1220;border-radius:999px;padding:2px 8px;">
                <?= h($genre) ?>
              </span>
              <span class="muted" style="margin-left:8px;"><?= h('ID:') ?> <code><?= h($id) ?></code></span>
              <?php if ($createdAt): ?>
                <span class="muted" style="margin-left:8px;"><?= h(date('Y-m-d H:i', $createdAt)) ?></span>
              <?php endif; ?>
            </div>
          </td>
          <td>
            <?php if ($statusActive): ?>
              <span class="badge on"><?= h($S['status_active']) ?></span>
            <?php else: ?>
              <span class="badge off"><?= h($S['status_blocked']) ?></span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($previewUrl): ?>
              <audio controls preload="none" src="<?= $previewUrl ?>"></audio>
            <?php else: ?>
              <span class="muted">—</span>
            <?php endif; ?>
          </td>
          <td class="actions">
            <form method="post" action="/admin/actions_tracks.php">
              <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= h($id) ?>">
              <input type="hidden" name="action" value="toggle_block">
              <?php if ($statusActive): ?>
                <button class="btn btnWarn" type="submit"><?= h($S['block']) ?></button>
              <?php else: ?>
                <button class="btn" type="submit"><?= h($S['unblock']) ?></button>
              <?php endif; ?>
            </form>

            <form method="post" action="/admin/actions_tracks.php" onsubmit="return confirm(<?= json_encode($S['deleteConfirm'], JSON_UNESCAPED_UNICODE) ?>)">
              <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= h($id) ?>">
              <input type="hidden" name="action" value="delete_track">
              <button class="btn btnDanger" type="submit"><?= h($S['delete']) ?></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<script>
(function(){
  const q = document.getElementById('q');
  const rows = Array.from(document.querySelectorAll('#tbl tbody tr'));
  function apply(){
    const term = (q.value || '').trim().toLowerCase();
    rows.forEach(tr=>{
      const hay = (tr.getAttribute('data-hay') || '');
      const ok = !term || hay.includes(term);
      tr.style.display = ok ? '' : 'none';
    });
  }
  q.addEventListener('input', apply);
})();
</script>

<?php require __DIR__ . '/../_partials/footer.php'; ?>