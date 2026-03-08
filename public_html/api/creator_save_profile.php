<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require __DIR__ . '/../_partials/creator_lib.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function out(array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function post_str(string $key): string {
    return trim((string)($_POST[$key] ?? ''));
}

function valid_optional_url_or_path(string $value): bool {
    if ($value === '') return true;

    // Tillåt interna paths som /uploads/_profiles/...
    if (str_starts_with($value, '/')) {
        return true;
    }

    // Tillåt fulla URL:er
    return (bool)filter_var($value, FILTER_VALIDATE_URL);
}

function safe_slug_part(string $s): string {
    $s = mb_strtolower(trim($s), 'UTF-8');
    $s = preg_replace('~[^a-z0-9\-_]+~u', '-', $s) ?? $s;
    $s = preg_replace('~-+~', '-', $s) ?? $s;
    return trim($s, '-');
}

function ensure_creator_upload_dir(): string {
    $dir = realpath(__DIR__ . '/../uploads') ?: (__DIR__ . '/../uploads');
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    $sub = $dir . '/creator_profiles';
    if (!is_dir($sub)) {
        @mkdir($sub, 0775, true);
    }

    return $sub;
}

function creator_webp_path(string $creatorSlug): array {
    $uploadDir = ensure_creator_upload_dir();
    $safeSlug = safe_slug_part($creatorSlug !== '' ? $creatorSlug : 'creator');
    $filename = $safeSlug . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.webp';
    $abs = $uploadDir . '/' . $filename;
    $rel = '/uploads/creator_profiles/' . $filename;
    return [$abs, $rel];
}

function image_create_from_file(string $tmp, string $mime) {
    return match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($tmp),
        'image/png'  => @imagecreatefrompng($tmp),
        'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmp) : false,
        'image/gif'  => @imagecreatefromgif($tmp),
        default      => false,
    };
}

function image_resize_to_webp($srcImg, int $srcW, int $srcH, string $destAbs, int $maxSide = 1200, int $quality = 84): void {
    if ($srcW <= 0 || $srcH <= 0) {
        throw new RuntimeException('Ogiltig bildstorlek.');
    }

    $scale = min(1, $maxSide / max($srcW, $srcH));
    $dstW = max(1, (int)round($srcW * $scale));
    $dstH = max(1, (int)round($srcH * $scale));

    $dstImg = imagecreatetruecolor($dstW, $dstH);
    if (!$dstImg) {
        throw new RuntimeException('Kunde inte skapa målbild.');
    }

    imagealphablending($dstImg, false);
    imagesavealpha($dstImg, true);

    $transparent = imagecolorallocatealpha($dstImg, 0, 0, 0, 127);
    imagefill($dstImg, 0, 0, $transparent);

    if (!imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH)) {
        imagedestroy($dstImg);
        throw new RuntimeException('Kunde inte skala bilden.');
    }

    if (!function_exists('imagewebp')) {
        imagedestroy($dstImg);
        throw new RuntimeException('Servern saknar stöd för WEBP.');
    }

    if (!imagewebp($dstImg, $destAbs, $quality)) {
        imagedestroy($dstImg);
        throw new RuntimeException('Kunde inte spara WEBP-bilden.');
    }

    imagedestroy($dstImg);
}

function delete_old_creator_image(?string $oldPath): void {
    $oldPath = trim((string)$oldPath);
    if ($oldPath === '' || !str_starts_with($oldPath, '/uploads/creator_profiles/')) {
        return;
    }

    $abs = realpath(__DIR__ . '/../') ?: (__DIR__ . '/../');
    $file = $abs . ltrim($oldPath, '/');
    if (is_file($file)) {
        @unlink($file);
    }
}

function save_uploaded_profile_image(array $file, string $creatorSlug, ?string $oldImagePath = null): string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Bilduppladdningen misslyckades.');
    }

    $tmp = (string)($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        throw new RuntimeException('Ogiltig uppladdad fil.');
    }

    $size = (int)($file['size'] ?? 0);
    if ($size <= 0 || $size > 4 * 1024 * 1024) {
        throw new RuntimeException('Bilden är för stor. Max 4 MB.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? (string)finfo_file($finfo, $tmp) : '';
    if ($finfo) {
        finfo_close($finfo);
    }

    $allowed = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    if (!in_array($mime, $allowed, true)) {
        throw new RuntimeException('Ogiltigt bildformat. Tillåtna format: JPG, PNG, WEBP, GIF.');
    }

    $img = image_create_from_file($tmp, $mime);
    if (!$img) {
        throw new RuntimeException('Kunde inte läsa bildfilen.');
    }

    $srcW = imagesx($img);
    $srcH = imagesy($img);

    [$destAbs, $destRel] = creator_webp_path($creatorSlug);

    try {
        image_resize_to_webp($img, $srcW, $srcH, $destAbs, 1200, 84);
    } finally {
        imagedestroy($img);
    }

    @chmod($destAbs, 0644);

    // Ta bort gammal lokal creator-bild om den finns
    delete_old_creator_image($oldImagePath);

    return $destRel;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    out(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$lang = trim((string)($_POST['lang'] ?? 'sv'));
$lang = ($lang === 'en') ? 'en' : 'sv';

$MSG = [
    'sv' => [
        'invalid_csrf' => 'Ogiltig säkerhetstoken. Ladda om sidan och försök igen.',
        'invalid_token' => 'Ogiltig eller utgången redigeringslänk.',
        'bad_url' => 'En eller flera länkar är ogiltiga.',
        'saved' => 'Profilen sparades.',
        'server' => 'Serverfel. Försök igen senare.',
    ],
    'en' => [
        'invalid_csrf' => 'Invalid security token. Reload the page and try again.',
        'invalid_token' => 'Invalid or expired edit link.',
        'bad_url' => 'One or more URLs are invalid.',
        'saved' => 'Profile saved.',
        'server' => 'Server error. Please try again later.',
    ],
];
$L = $MSG[$lang] ?? $MSG['sv'];

$csrf = post_str('csrf');
if ($csrf === '' || !hash_equals((string)($_SESSION['creator_edit_csrf'] ?? ''), $csrf)) {
    out(['ok' => false, 'error' => $L['invalid_csrf']], 400);
}

$token = post_str('token');
$tokenRow = $token !== '' ? ij_creator_find_valid_token($token) : null;

if (!$tokenRow || !is_array($tokenRow)) {
    out(['ok' => false, 'error' => $L['invalid_token']], 403);
}

$creatorSlug = trim((string)($tokenRow['creator_slug'] ?? ''));
$creator = $creatorSlug !== '' ? ij_creator_find_by_slug($creatorSlug) : null;

if (!$creator || !is_array($creator)) {
    out(['ok' => false, 'error' => $L['invalid_token']], 403);
}

$bioSv = post_str('bio_sv');
$bioEn = post_str('bio_en');
$image = post_str('image');
$website = post_str('website');
$instagram = post_str('instagram');
$spotify = post_str('spotify');

foreach ([$image, $website, $instagram, $spotify] as $url) {
    if (!valid_optional_url_or_path($url)) {
        out(['ok' => false, 'error' => $L['bad_url']], 422);
    }
}

try {
    $uploadedImagePath = '';
    if (isset($_FILES['profile_image']) && is_array($_FILES['profile_image'])) {
        $uploadedImagePath = save_uploaded_profile_image(
            $_FILES['profile_image'],
            $creatorSlug,
            (string)($creator['image'] ?? '')
        );
    }

    $creator['bio_sv'] = $bioSv;
    $creator['bio_en'] = $bioEn;

    // Prioritet:
    // 1. Uppladdad fil
    // 2. Manuell image-url/path
    if ($uploadedImagePath !== '') {
        $creator['image'] = $uploadedImagePath;
    } else {
        $creator['image'] = $image;
    }

    if (!isset($creator['links']) || !is_array($creator['links'])) {
        $creator['links'] = [];
    }

    $creator['links']['website'] = $website;
    $creator['links']['instagram'] = $instagram;
    $creator['links']['spotify'] = $spotify;
    $creator['updated_at'] = time();

    $ok = ij_creator_upsert_profile($creator);
    if (!$ok) {
        out(['ok' => false, 'error' => $L['server']], 500);
    }

    out([
        'ok' => true,
        'message' => $L['saved'],
        'creator_slug' => $creatorSlug,
        'image' => (string)($creator['image'] ?? ''),
    ]);
} catch (Throwable $e) {
    out([
        'ok' => false,
        'error' => $e->getMessage() !== '' ? $e->getMessage() : $L['server'],
    ], 500);
}