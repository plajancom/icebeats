<?php
// /domains/audio.icejockey.app/public_html/api/upload.php
declare(strict_types=1);

require __DIR__ . '/../admin/lib.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  http_response_code(405);
  exit('POST only');
}

$token = (string)($_POST['token'] ?? '');
if ($token === '' || !hash_equals(UPLOAD_TOKEN, $token)) {
  http_response_code(403);
  exit('Forbidden');
}

/* =========================
   REQUIRE TERMS AGREEMENT
   ========================= */
$agreed = (string)($_POST['agree'] ?? '');
if ($agreed !== '1') {
  http_response_code(400);
  exit('Terms must be accepted');
}

$termsVersion = trim((string)($_POST['terms_version'] ?? 'unknown'));
$termsLang    = trim((string)($_POST['lang'] ?? 'unknown'));

/* =========================
   BASIC FIELDS (sanitize)
   ========================= */
$title  = trim((string)($_POST['title'] ?? ''));
$artist = trim((string)($_POST['artist'] ?? ''));
$genre  = sanitize_genre((string)($_POST['genre'] ?? ''));

// Begränsa längder (skydd mot "megafält")
if ($title !== '')  $title  = mb_substr($title, 0, 120);
if ($artist !== '') $artist = mb_substr($artist, 0, 120);

if ($title === '' || $artist === '') {
  http_response_code(400);
  exit('Missing title/artist');
}

/* =========================
   FILE VALIDATION
   ========================= */
if (empty($_FILES['mp3']) || ($_FILES['mp3']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
  http_response_code(400);
  exit('MP3 upload failed');
}
if (empty($_FILES['cover']) || ($_FILES['cover']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
  http_response_code(400);
  exit('Cover upload failed');
}

$mp3Size = (int)($_FILES['mp3']['size'] ?? 0);
$imgSize = (int)($_FILES['cover']['size'] ?? 0);

if ($mp3Size <= 0) {
  http_response_code(400);
  exit('MP3 missing/empty');
}
if ($imgSize <= 0) {
  http_response_code(400);
  exit('Cover missing/empty');
}

if ($mp3Size > ($MAX_MP3_MB * 1024 * 1024)) {
  http_response_code(413);
  exit('MP3 too large');
}
if ($imgSize > ($MAX_IMG_MB * 1024 * 1024)) {
  http_response_code(413);
  exit('Image too large');
}

$mp3Tmp = (string)($_FILES['mp3']['tmp_name'] ?? '');
$imgTmp = (string)($_FILES['cover']['tmp_name'] ?? '');

if ($mp3Tmp === '' || !is_file($mp3Tmp)) {
  http_response_code(400);
  exit('MP3 temp missing');
}
if ($imgTmp === '' || !is_file($imgTmp)) {
  http_response_code(400);
  exit('Cover temp missing');
}

// MIME checks
$mp3Mime = mime_of($mp3Tmp);
// Lite striktare än "innehåller audio"
$okAudio = in_array($mp3Mime, ['audio/mpeg', 'audio/mp3', 'audio/x-mpeg'], true) || str_contains($mp3Mime, 'audio/');
if (!$okAudio) {
  http_response_code(400);
  exit('Not audio');
}

$imgMime = mime_of($imgTmp);
$okImg = in_array($imgMime, ['image/jpeg','image/png','image/webp','image/gif'], true);
if (!$okImg) {
  http_response_code(400);
  exit('Bad image type');
}

$coverExt = match($imgMime) {
  'image/png'  => 'png',
  'image/webp' => 'webp',
  'image/gif'  => 'gif',
  default      => 'jpg'
};

/* =========================
   CREATE ID
   ========================= */
$id = date('YmdHis') . '-' . bin2hex(random_bytes(3));

/* =========================
   STORE FILES (AUTO-APPROVE)
   - MP3   -> /tracks/<id>.mp3
   - Cover -> /covers/<id>.<ext>
   ========================= */
ensure_dirs([$TRACKS_DIR, $COVERS_DIR, $DATA_DIR]);

$mp3File   = $id . '.mp3';
$coverFile = $id . '.' . $coverExt;

$mp3Abs   = rtrim($TRACKS_DIR, '/\\') . '/' . $mp3File;
$coverAbs = rtrim($COVERS_DIR, '/\\') . '/' . $coverFile;

if (!move_uploaded_file($mp3Tmp, $mp3Abs)) {
  http_response_code(500);
  exit('Save mp3 failed');
}
if (!move_uploaded_file($imgTmp, $coverAbs)) {
  @unlink($mp3Abs);
  http_response_code(500);
  exit('Save cover failed');
}

/* =========================
   LEGAL LOGGING DATA
   ========================= */
$timestampUnix = time();
$timestampIso  = gmdate('c'); // UTC ISO

$ip        = ij_client_ip();
$userAgent = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 512);

/* =========================
   DB ITEM (ACTIVE BY DEFAULT)
   status: active | blocked
   ========================= */
$item = [
  'id'      => $id,
  'title'   => $title,
  'artist'  => $artist,
  'genre'   => $genre,
  'url'     => '/tracks/' . $mp3File,                 // relativt
  'image'   => '/covers/' . $coverFile,
  'mime'    => 'audio/mpeg',
  'kind'    => 'audio',
  'startMs' => 0,
  'endMs'   => 0,

  'createdAt'    => $timestampUnix,
  'createdAtIso' => $timestampIso,

  // ✅ Admin-spärr (library.json filtrerar bort blocked)
  'status' => 'active', // active | blocked

  // Terms acceptance evidence
  'termsAccepted'      => true,
  'termsVersion'       => $termsVersion,
  'termsLanguage'      => $termsLang,
  'termsAcceptedAt'    => $timestampUnix,
  'termsAcceptedAtIso' => $timestampIso,
  'uploaderIp'         => $ip,
  'uploaderUserAgent'  => $userAgent,
];

/* =========================
   WRITE DB + REGEN library.json
   (fil-lås så samtidiga uploads inte krockar)
   ========================= */
$lockFile = rtrim($DATA_DIR, '/\\') . '/.db.lock';
$lockFp = @fopen($lockFile, 'c+');
if ($lockFp) { @flock($lockFp, LOCK_EX); }

$db = read_json($DB_PATH, ['items' => []]);
if (!is_array($db)) $db = ['items' => []];
if (!isset($db['items']) || !is_array($db['items'])) $db['items'] = [];

// Skydd mot dup (om samma id mot förmodan skulle finnas)
$db['items'] = array_values(array_filter($db['items'], function($it) use ($id){
  return is_array($it) ? (string)($it['id'] ?? '') !== (string)$id : true;
}));

$db['items'][] = $item;

write_json_atomic($DB_PATH, $db);
regen_library_json($BASE_URL, $DB_PATH, $LIBRARY_PATH);

if ($lockFp) { @flock($lockFp, LOCK_UN); @fclose($lockFp); }

/* =========================
   RESPONSE
   ========================= */
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => true, 'id' => $id, 'approved' => true], JSON_UNESCAPED_UNICODE);