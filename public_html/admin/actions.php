<?php
require __DIR__ . '/lib.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('POST only');
}
require_csrf();

$action = $_POST['action'] ?? '';

function redirect_with(string $msg): void {
  header('Location: index.php?msg=' . urlencode($msg));
  exit;
}

if ($action === 'upload') {
  $title  = trim((string)($_POST['title'] ?? ''));
  $artist = trim((string)($_POST['artist'] ?? ''));
  $genre  = sanitize_genre((string)($_POST['genre'] ?? ''));

  if ($title === '' || $artist === '') redirect_with('Fyll i artist och titel.');

  if (empty($_FILES['mp3']) || $_FILES['mp3']['error'] !== UPLOAD_ERR_OK) {
    redirect_with('MP3-uppladdning misslyckades.');
  }
  if (empty($_FILES['cover']) || $_FILES['cover']['error'] !== UPLOAD_ERR_OK) {
    redirect_with('Omslagsbild-uppladdning misslyckades.');
  }

  $mp3Size = (int)$_FILES['mp3']['size'];
  $imgSize = (int)$_FILES['cover']['size'];
  if ($mp3Size > ($MAX_MP3_MB * 1024 * 1024)) redirect_with('MP3 är för stor.');
  if ($imgSize > ($MAX_IMG_MB * 1024 * 1024)) redirect_with('Bild är för stor.');

  $mp3Tmp = $_FILES['mp3']['tmp_name'];
  $imgTmp = $_FILES['cover']['tmp_name'];

  $mp3Mime = mime_of($mp3Tmp);
  if (!str_contains($mp3Mime, 'audio')) redirect_with('Filen verkar inte vara audio.');

  $imgMime = mime_of($imgTmp);
  $okImg = in_array($imgMime, ['image/jpeg','image/png','image/webp','image/gif'], true);
  if (!$okImg) redirect_with('Omslaget måste vara jpg/png/webp/gif.');

  $id = date('YmdHis') . '-' . bin2hex(random_bytes(3));
  $pendDir = $PENDING_DIR . '/' . $id;
  ensure_dirs([$pendDir]);

  $coverExt = match($imgMime) {
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/gif' => 'gif',
    default => 'jpg'
  };

  $mp3Name = 'track.mp3';
  $coverName = 'cover.' . $coverExt;

  if (!move_uploaded_file($mp3Tmp, $pendDir . '/' . $mp3Name)) redirect_with('Kunde inte spara MP3.');
  if (!move_uploaded_file($imgTmp, $pendDir . '/' . $coverName)) redirect_with('Kunde inte spara omslag.');

  $meta = [
    'id' => $id,
    'title' => $title,
    'artist' => $artist,
    'genre' => $genre,          // ✅ NYTT
    'mp3' => $mp3Name,
    'cover' => $coverName,
    'mime' => 'audio/mpeg',
    'kind' => 'audio',
    'createdAt' => time(),
  ];
  write_json_atomic($pendDir . '/meta.json', $meta);

  redirect_with('Uppladdat till pending. Godkänn för att publicera.');
}

if ($action === 'approve') {
  $id = preg_replace('~[^a-zA-Z0-9\-]~', '', (string)($_POST['id'] ?? ''));
  if (!$id) redirect_with('Saknar ID.');

  $pendDir = $PENDING_DIR . '/' . $id;
  $meta = read_json($pendDir . '/meta.json', null);
  if (!$meta) redirect_with('Hittar inte pending-meta.');

  $title  = (string)($meta['title'] ?? 'Track');
  $artist = (string)($meta['artist'] ?? '');
  $genre  = sanitize_genre((string)($meta['genre'] ?? 'Övrigt'));

  $base = $id . '-' . slugify($artist . '-' . $title);
  $trackFile = $base . '.mp3';

  $coverExt = pathinfo((string)($meta['cover'] ?? ''), PATHINFO_EXTENSION) ?: 'jpg';
  $coverFile = $base . '.' . $coverExt;

  @rename($pendDir . '/' . $meta['mp3'], $TRACKS_DIR . '/' . $trackFile);
  @rename($pendDir . '/' . $meta['cover'], $COVERS_DIR . '/' . $coverFile);

  $db = read_json($DB_PATH, ['items' => []]);
  $items = $db['items'] ?? [];

  $item = [
    'id' => $id,
    'title' => $title,
    'artist' => $artist,
    'genre' => $genre, // ✅ NYTT
    'url' => '/tracks/' . $trackFile,
    'image' => $BASE_URL . '/covers/' . $coverFile,
    'mime' => 'audio/mpeg',
    'kind' => 'audio',
    'startMs' => 0,
    'endMs' => 0,
    'createdAt' => (int)($meta['createdAt'] ?? time()),
  ];

  $items = array_values(array_filter($items, fn($x) => ($x['id'] ?? '') !== $id));
  $items[] = $item;

  $db['items'] = $items;
  write_json_atomic($DB_PATH, $db);

  regen_library_json($BASE_URL, $DB_PATH, $LIBRARY_PATH);
  rrmdir($pendDir);

  redirect_with('Godkänd och publicerad.');
}

if ($action === 'reject') {
  $id = preg_replace('~[^a-zA-Z0-9\-]~', '', (string)($_POST['id'] ?? ''));
  if (!$id) redirect_with('Saknar ID.');
  rrmdir($PENDING_DIR . '/' . $id);
  redirect_with('Borttagen från pending.');
}

redirect_with('Okänd action.');
