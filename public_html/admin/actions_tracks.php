<?php
// /domains/audio.icejockey.app/public_html/admin/actions_tracks.php
declare(strict_types=1);

require __DIR__ . '/lib.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('POST only');
}

require_csrf();

$action = (string)($_POST['action'] ?? '');
$id = trim((string)($_POST['id'] ?? ''));

if ($id === '') {
  http_response_code(400);
  exit('Missing id');
}

$db = read_json($DB_PATH, ['items' => []]);
$items = is_array($db['items'] ?? null) ? $db['items'] : [];

$idx = -1;
for ($i=0; $i<count($items); $i++){
  if (!is_array($items[$i])) continue;
  if ((string)($items[$i]['id'] ?? '') === $id) { $idx = $i; break; }
}
if ($idx < 0) {
  http_response_code(404);
  exit('Track not found');
}

function redirect_msg(string $m, string $lang='sv'): void {
  $q = '?msg=' . urlencode($m);
  if ($lang === 'en' || $lang === 'sv') $q .= '&lang=' . urlencode($lang);
  header('Location: /admin/tracks.php' . $q);
  exit;
}

$lang = (string)($_GET['lang'] ?? ($_POST['lang'] ?? 'sv'));
if ($lang !== 'en') $lang = 'sv';

$it = $items[$idx];
if (!is_array($it)) $it = [];

if ($action === 'toggle_block') {
  $active = ij_is_track_active($it);

  if ($active) {
    $items[$idx]['status'] = 'blocked';
  } else {
    $items[$idx]['status'] = 'active';
  }

  $db['items'] = $items;
  write_json_atomic($DB_PATH, $db);
  regen_library_json($BASE_URL, $DB_PATH, $LIBRARY_PATH);

  redirect_msg($active ? 'Spärrad' : 'Aktiverad', $lang);
}

if ($action === 'delete_track') {
  // försök radera mp3 + cover från disk (best effort)
  $url = (string)($it['url'] ?? '');
  $img = (string)($it['image'] ?? '');

  $urlPath = ij_public_path($url, $BASE_URL);
  $imgPath = ij_public_path($img, $BASE_URL);

  $diskMp3 = ij_public_to_disk($urlPath, $ROOT);
  $diskImg = ij_public_to_disk($imgPath, $ROOT);

  if ($diskMp3 && is_file($diskMp3)) @unlink($diskMp3);
  if ($diskImg && is_file($diskImg)) @unlink($diskImg);

  // ta bort från DB
  array_splice($items, $idx, 1);
  $db['items'] = array_values($items);
  write_json_atomic($DB_PATH, $db);
  regen_library_json($BASE_URL, $DB_PATH, $LIBRARY_PATH);

  redirect_msg($lang === 'en' ? 'Deleted' : 'Raderad', $lang);
}

http_response_code(400);
exit('Unknown action');