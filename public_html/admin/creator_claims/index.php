<?php
declare(strict_types=1);

require __DIR__ . '/../../admin/lib.php';
require __DIR__ . '/../../_partials/i18n.php';
require __DIR__ . '/../../_partials/creator_lib.php';

$lang = ij_lang();
$msg = trim((string)($_GET['msg'] ?? ''));
$statusFilter = trim((string)($_GET['status'] ?? ''));
if (!in_array($statusFilter, ['pending', 'approved', 'rejected'], true)) {
    $statusFilter = 'pending';
}

$claims = ij_creator_claims_load_all();

usort($claims, function(array $a, array $b): int {
    return (int)($b['created_at'] ?? 0) <=> (int)($a['created_at'] ?? 0);
});

if ($statusFilter !== '') {
    $claims = array_values(array_filter($claims, function(array $claim) use ($statusFilter): bool {
        return (string)($claim['status'] ?? 'pending') === $statusFilter;
    }));
}

$counts = [
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0,
];
foreach (ij_creator_claims_load_all() as $c) {
    $st = (string)($c['status'] ?? 'pending');
    if (isset($counts[$st])) $counts[$st]++;
}

$STR = [
    'sv' => [
        'title' => 'iceBeats Admin – Creator claims',
        'sub' => 'Granska och hantera claims för creator-profiler.',
        'pending' => 'Pending',
        'approved' => 'Godkända',
        'rejected' => 'Nekade',
        'allPending' => 'Visa pending',
        'allApproved' => 'Visa godkända',
        'allRejected' => 'Visa nekade',
        'none' => 'Inga claims i denna vy.',
        'creator' => 'Creator',
        'claimant' => 'Anspråk från',
        'proof' => 'Bevis',
        'message' => 'Meddelande',
        'status' => 'Status',
        'created' => 'Skapad',
        'actions' => 'Åtgärder',
        'approve' => '✅ Godkänn',
        'reject' => '🗑️ Neka',
        'rejectConfirm' => 'Är du säker på att du vill neka denna claim?',
        'openCreator' => 'Öppna creator',
        'email' => 'E-post',
        'name' => 'Namn',
        'noProof' => 'Ingen länk',
        'backAdmin' => '← Till admin',
        'successPrefix' => 'Klart:',
    ],
    'en' => [
        'title' => 'iceBeats Admin – Creator claims',
        'sub' => 'Review and manage claims for creator profiles.',
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'allPending' => 'Show pending',
        'allApproved' => 'Show approved',
        'allRejected' => 'Show rejected',
        'none' => 'No claims in this view.',
        'creator' => 'Creator',
        'claimant' => 'Claim from',
        'proof' => 'Proof',
        'message' => 'Message',
        'status' => 'Status',
        'created' => 'Created',
        'actions' => 'Actions',
        'approve' => '✅ Approve',
        'reject' => '🗑️ Reject',
        'rejectConfirm' => 'Are you sure you want to reject this claim?',
        'openCreator' => 'Open creator',
        'email' => 'Email',
        'name' => 'Name',
        'noProof' => 'No URL',
        'backAdmin' => '← Back to admin',
        'successPrefix' => 'Done:',
    ],
];
$S = $STR[$lang] ?? $STR['sv'];

$pageTitle = $S['title'];
$pageHead = '';
require __DIR__ . '/../../_partials/header.php';

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function fmt_ts(int $ts, string $lang): string {
    if ($ts <= 0) return '—';
    return date($lang === 'en' ? 'Y-m-d H:i' : 'Y-m-d H:i', $ts);
}
?>
<style>
.wrap{max-width:1180px;margin:0 auto;padding:0 16px}
.card{
  background:#0f172a;
  border:1px solid #1f2a44;
  border-radius:16px;
  padding:18px;
  margin:14px 0;
}
.topbar{
  display:flex;
  justify-content:space-between;
  gap:14px;
  flex-wrap:wrap;
  align-items:flex-start;
}
.h1{
  margin:0;
  font-size:22px;
  color:#e5e7eb;
}
.muted{
  color:#94a3b8;
  font-size:12px;
  line-height:1.7;
}
.filters{
  display:flex;
  gap:10px;
  flex-wrap:wrap;
  margin-top:14px;
}
.pill{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:8px 12px;
  border-radius:999px;
  border:1px solid #334155;
  background:#0b1220;
  color:#cbd5e1;
  text-decoration:none;
  font-size:12px;
  font-weight:800;
}
.pill.active{
  border-color:#3b82f6;
  color:#fff;
  background:rgba(37,99,235,.18);
}
.notice{
  margin-top:12px;
  padding:12px 14px;
  border-radius:12px;
  border:1px solid rgba(134,239,172,.22);
  background:rgba(22,101,52,.14);
  color:#bbf7d0;
  font-size:13px;
  font-weight:800;
}
.claimList{
  display:grid;
  gap:14px;
}
.claimCard{
  background:#0b1220;
  border:1px solid #24324f;
  border-radius:16px;
  padding:16px;
}
.claimHead{
  display:flex;
  justify-content:space-between;
  gap:12px;
  flex-wrap:wrap;
  align-items:flex-start;
  margin-bottom:10px;
}
.claimTitle{
  font-size:18px;
  font-weight:950;
  color:#e5e7eb;
  line-height:1.2;
}
.claimSub{
  margin-top:4px;
  color:#94a3b8;
  font-size:12px;
}
.badge{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-width:84px;
  padding:6px 10px;
  border-radius:999px;
  border:1px solid #334155;
  background:#08101f;
  font-size:11px;
  font-weight:900;
  letter-spacing:.4px;
}
.badge.pending{ color:#fde68a; }
.badge.approved{ color:#86efac; }
.badge.rejected{ color:#fca5a5; }

.grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:12px;
}
@media (max-width: 900px){
  .grid{grid-template-columns:1fr}
}
.box{
  background:#0a1020;
  border:1px solid #1f2a44;
  border-radius:14px;
  padding:14px;
}
.boxTitle{
  font-size:12px;
  font-weight:900;
  color:#cbd5e1;
  text-transform:uppercase;
  letter-spacing:.4px;
  margin-bottom:8px;
}
.kv{
  display:grid;
  grid-template-columns:110px 1fr;
  gap:8px 10px;
}
@media (max-width: 560px){
  .kv{grid-template-columns:1fr}
}
.kvKey{color:#94a3b8;font-size:12px}
.kvVal{color:#e5e7eb;font-size:12px;word-break:break-word}
.actions{
  display:flex;
  gap:10px;
  flex-wrap:wrap;
  margin-top:14px;
}
.btn{
  cursor:pointer;
  border:0;
  border-radius:12px;
  padding:10px 14px;
  background:#2563eb;
  color:white;
  font-weight:900;
}
.btnDanger{
  background:#dc2626;
}
.btnGhost{
  cursor:pointer;
  border-radius:12px;
  padding:10px 14px;
  border:1px solid #334155;
  background:#0b1220;
  color:#e5e7eb;
  text-decoration:none;
  font-weight:900;
  display:inline-flex;
  align-items:center;
}
.empty{
  color:#94a3b8;
  font-size:14px;
  line-height:1.8;
}
code{
  background:#0b1220;
  border:1px solid #24324f;
  border-radius:8px;
  padding:2px 6px;
}
</style>

<div class="wrap">
  <div class="card">
    <div class="topbar">
      <div>
        <h1 class="h1"><?= h($S['title']) ?></h1>
        <div class="muted"><?= h($S['sub']) ?></div>
      </div>

      <div>
        <a class="btnGhost" href="<?= h(ij_url('/admin/?lang=' . $lang)) ?>"><?= h($S['backAdmin']) ?></a>
      </div>
    </div>

    <div class="filters">
      <a class="pill<?= $statusFilter === 'pending' ? ' active' : '' ?>" href="<?= h(ij_url('/admin/creator_claims/?status=pending&lang=' . $lang)) ?>">
        <?= h($S['pending']) ?> <strong><?= (int)$counts['pending'] ?></strong>
      </a>

      <a class="pill<?= $statusFilter === 'approved' ? ' active' : '' ?>" href="<?= h(ij_url('/admin/creator_claims/?status=approved&lang=' . $lang)) ?>">
        <?= h($S['approved']) ?> <strong><?= (int)$counts['approved'] ?></strong>
      </a>

      <a class="pill<?= $statusFilter === 'rejected' ? ' active' : '' ?>" href="<?= h(ij_url('/admin/creator_claims/?status=rejected&lang=' . $lang)) ?>">
        <?= h($S['rejected']) ?> <strong><?= (int)$counts['rejected'] ?></strong>
      </a>
    </div>

    <?php if ($msg !== ''): ?>
      <div class="notice"><?= h($S['successPrefix'] . ' ' . $msg) ?></div>
    <?php endif; ?>
  </div>

  <div class="claimList">
    <?php if (!$claims): ?>
      <div class="card">
        <div class="empty"><?= h($S['none']) ?></div>
      </div>
    <?php else: ?>

      <?php foreach ($claims as $claim):
        $id = (string)($claim['id'] ?? '');
        $creatorSlug = (string)($claim['creator_slug'] ?? '');
        $creatorName = (string)($claim['creator_name'] ?? '');
        $name = (string)($claim['name'] ?? '');
        $email = (string)($claim['email'] ?? '');
        $proof = (string)($claim['proof_url'] ?? '');
        $messageText = (string)($claim['message'] ?? '');
        $status = (string)($claim['status'] ?? 'pending');
        $createdAt = (int)($claim['created_at'] ?? 0);

        $creatorUrl = ij_url('/artist/?name=' . rawurlencode($creatorName) . '&lang=' . $lang);
      ?>
        <div class="claimCard">
          <div class="claimHead">
            <div>
              <div class="claimTitle"><?= h($creatorName !== '' ? $creatorName : $creatorSlug) ?></div>
              <div class="claimSub">
                <code><?= h($creatorSlug) ?></code> • <?= h($S['created']) ?>: <?= h(fmt_ts($createdAt, $lang)) ?>
              </div>
            </div>

            <div class="badge <?= h($status) ?>"><?= h(strtoupper($status)) ?></div>
          </div>

          <div class="grid">
            <div class="box">
              <div class="boxTitle"><?= h($S['claimant']) ?></div>
              <div class="kv">
                <div class="kvKey"><?= h($S['name']) ?></div>
                <div class="kvVal"><?= h($name) ?></div>

                <div class="kvKey"><?= h($S['email']) ?></div>
                <div class="kvVal"><?= h($email) ?></div>

                <div class="kvKey"><?= h($S['proof']) ?></div>
                <div class="kvVal">
                  <?php if ($proof !== ''): ?>
                    <a class="ij-trackLink" href="<?= h($proof) ?>" target="_blank" rel="noopener noreferrer"><?= h($proof) ?></a>
                  <?php else: ?>
                    <?= h($S['noProof']) ?>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <div class="box">
              <div class="boxTitle"><?= h($S['message']) ?></div>
              <div class="muted" style="white-space:pre-wrap;"><?= h($messageText !== '' ? $messageText : '—') ?></div>
            </div>
          </div>

          <div class="actions">
            <a class="btnGhost" href="<?= h($creatorUrl) ?>" target="_blank" rel="noopener"><?= h($S['openCreator']) ?></a>

            <?php if ($status === 'pending'): ?>
              <form method="post" action="<?= h(ij_url('/admin/creator_claims/actions.php')) ?>" style="display:inline;">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="id" value="<?= h($id) ?>">
                <input type="hidden" name="lang" value="<?= h($lang) ?>">
                <button class="btn" type="submit"><?= h($S['approve']) ?></button>
              </form>

              <form method="post" action="<?= h(ij_url('/admin/creator_claims/actions.php')) ?>" style="display:inline;" onsubmit="return confirm(<?= json_encode($S['rejectConfirm'], JSON_UNESCAPED_UNICODE) ?>)">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="id" value="<?= h($id) ?>">
                <input type="hidden" name="lang" value="<?= h($lang) ?>">
                <button class="btn btnDanger" type="submit"><?= h($S['reject']) ?></button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>

    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../../_partials/footer.php'; ?>