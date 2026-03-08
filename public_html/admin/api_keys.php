<?php
// /admin/api_keys.php
declare(strict_types=1);

require __DIR__ . '/lib.php';
require __DIR__ . '/../_partials/i18n.php';
require __DIR__ . '/../api/_auth.php';

$lang = ij_lang();
$pageTitle = 'iceBeats API Keys';
require __DIR__ . '/../_partials/header.php';

function ij_gen_key(): string {
  $b = random_bytes(32);
  $s = rtrim(strtr(base64_encode($b), '+/', '-_'), '=');
  return 'ijk_' . $s;
}

function ij_norm_name(string $s): string {
  $s = trim($s);
  $s = preg_replace('/\s+/u', ' ', $s);
  return mb_strtolower($s, 'UTF-8');
}

function ij_name_exists(array $db, string $nameNorm): bool {
  foreach (($db['keys'] ?? []) as $row) {
    if (!is_array($row)) continue;
    if (!empty($row['revoked'])) continue;

    $n = ij_norm_name((string)($row['name'] ?? ''));
    if ($n !== '' && $n === $nameNorm) return true;
  }
  return false;
}

$db = ij_auth_load_keys();

$flash = '';
$flashClass = 'muted';
$newKeyPlain = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $csrf = (string)($_POST['csrf'] ?? '');

  if ($csrf !== csrf_token()) {
    $flash = 'CSRF error';
    $flashClass = 'err';
  } else {

    $action = (string)($_POST['action'] ?? '');

    if ($action === 'create') {

      $nameRaw = (string)($_POST['name'] ?? '');
      $name = trim($nameRaw);
      $name = preg_replace('/\s+/u', ' ', $name);

      if ($name === '') {
        $flash = 'Name required';
        $flashClass = 'err';
      } else {

        $nameNorm = ij_norm_name($name);

        if (ij_name_exists($db, $nameNorm)) {
          $flash = 'Name already exists';
          $flashClass = 'err';
        } else {

          $plain = ij_gen_key();
          $hash = ij_auth_hash_key($plain);

          $db['keys'][] = [
            'hash' => $hash,
            'name' => mb_substr($name, 0, 64),
            'createdAt' => time(),
            'revoked' => false,
          ];

          ij_auth_save_keys($db);

          $newKeyPlain = $plain;
          $flash = 'API key created — copy it now.';
          $flashClass = 'ok';
        }
      }
    }

    if ($action === 'revoke') {

      $hash = (string)($_POST['hash'] ?? '');

      foreach ($db['keys'] as &$row) {
        if (($row['hash'] ?? '') === $hash) {
          $row['revoked'] = true;
          $row['revokedAt'] = time();
        }
      }

      ij_auth_save_keys($db);

      $flash = 'Key revoked';
      $flashClass = 'ok';
    }

    if ($action === 'delete_revoked') {

      $db['keys'] = array_values(array_filter(
        $db['keys'],
        fn($row) => empty($row['revoked'])
      ));

      ij_auth_save_keys($db);

      $flash = 'Revoked keys removed';
      $flashClass = 'ok';
    }
  }
}

function h($s){
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

$active = 0;
$revoked = 0;

foreach(($db['keys'] ?? []) as $k){
  if (!empty($k['revoked'])) $revoked++;
  else $active++;
}

?>

<style>

.wrap{
max-width:1000px;
margin:0 auto;
padding:0 16px;
}

.card{
background:#0f172a;
border:1px solid #1f2a44;
border-radius:16px;
padding:16px;
margin:14px 0;
}

h1{margin:0 0 6px;font-size:20px}
h2{margin:0 0 10px;font-size:16px}

.muted{
color:#94a3b8;
font-size:12px
}

.row{
display:flex;
gap:10px;
flex-wrap:wrap;
align-items:center;
}

input{
padding:10px 12px;
border-radius:12px;
border:1px solid #24324f;
background:#0b1220;
color:#e5e7eb;
}

.btn{
cursor:pointer;
border:0;
border-radius:12px;
padding:10px 12px;
background:#2563eb;
color:white;
font-weight:900
}

.btnDanger{
background:#ef4444
}

.btnGhost{
cursor:pointer;
border-radius:12px;
padding:10px 12px;
border:1px solid #334155;
background:#0b1220;
color:#e5e7eb;
font-weight:900
}

.table{
width:100%;
border-collapse:collapse;
margin-top:10px
}

.table th,.table td{
padding:10px;
border-top:1px solid #1f2a44;
text-align:left
}

.ok{color:#86efac;font-weight:900}
.err{color:#fca5a5;font-weight:900}

.keyCode{
display:block;
background:#0b1220;
border:1px solid #24324f;
border-radius:10px;
padding:10px;
font-family:monospace;
cursor:pointer
}

.stats{
display:flex;
gap:12px;
flex-wrap:wrap
}

.stat{
background:#08101f;
border:1px solid #24324f;
border-radius:12px;
padding:10px 12px;
font-size:13px
}

</style>

<div class="wrap">

<div class="card">
<h1>iceBeats API Keys</h1>
<div class="muted">
Manage API keys for external integrations and apps.
</div>

<div class="stats" style="margin-top:12px">

<div class="stat">
Active keys: <b><?= $active ?></b>
</div>

<div class="stat">
Revoked: <b><?= $revoked ?></b>
</div>

</div>

</div>

<?php if ($flash): ?>
<div class="card">
<div class="<?= h($flashClass) ?>">
<?= h($flash) ?>
</div>

<?php if ($newKeyPlain): ?>

<div style="margin-top:12px">

<div class="muted">Copy this key now (shown once):</div>

<div id="newKey" class="keyCode"><?= h($newKeyPlain) ?></div>

</div>

<?php endif; ?>

</div>
<?php endif; ?>

<div class="card">

<h2>Create API key</h2>

<form method="post">

<input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
<input type="hidden" name="action" value="create">

<div class="row">

<input
type="text"
name="name"
placeholder="Arena scoreboard"
required
/>

<button class="btn" type="submit">
Create
</button>

</div>

</form>

</div>

<div class="card">

<div style="display:flex;justify-content:space-between">

<h2>Keys</h2>

<form method="post">

<input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
<input type="hidden" name="action" value="delete_revoked">

<button class="btnGhost">
Clean revoked
</button>

</form>

</div>

<table class="table">

<thead>
<tr>
<th>Name</th>
<th>Hash</th>
<th>Created</th>
<th>Status</th>
<th></th>
</tr>
</thead>

<tbody>

<?php foreach(($db['keys'] ?? []) as $row): ?>

<tr>

<td><?= h($row['name'] ?? '') ?></td>

<td><code><?= h(substr($row['hash'],0,16)) ?>…</code></td>

<td class="muted">
<?= date('Y-m-d H:i',$row['createdAt'] ?? time()) ?>
</td>

<td>

<?php if(!empty($row['revoked'])): ?>
<span class="err">revoked</span>
<?php else: ?>
<span class="ok">active</span>
<?php endif; ?>

</td>

<td>

<?php if(empty($row['revoked'])): ?>

<form method="post">

<input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
<input type="hidden" name="action" value="revoke">
<input type="hidden" name="hash" value="<?= h($row['hash']) ?>">

<button class="btn btnDanger">
Revoke
</button>

</form>

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

<script>

const key = document.getElementById('newKey');

if(key){

key.onclick = async ()=>{

try{

await navigator.clipboard.writeText(key.textContent);

key.style.borderColor="#22c55e";

}catch{}

}

}

</script>

<?php require __DIR__ . '/../_partials/footer.php'; ?>