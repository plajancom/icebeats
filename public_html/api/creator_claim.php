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

function valid_email(string $email): bool {
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}

function valid_optional_url(string $url): bool {
    if ($url === '') return true;
    return (bool)filter_var($url, FILTER_VALIDATE_URL);
}

function too_fast(int $minSeconds = 3): bool {
    $loadedAt = (int)($_POST['form_loaded_at'] ?? 0);
    if ($loadedAt <= 0) return true;
    return (time() - $loadedAt) < $minSeconds;
}

function duplicate_pending_claim(string $creatorSlug, string $email): bool {
    $claims = ij_creator_claims_load_all();
    foreach ($claims as $claim) {
        if (!is_array($claim)) continue;

        $sameSlug = ((string)($claim['creator_slug'] ?? '') === $creatorSlug);
        $sameEmail = (mb_strtolower(trim((string)($claim['email'] ?? '')), 'UTF-8') === mb_strtolower($email, 'UTF-8'));
        $pending = ((string)($claim['status'] ?? 'pending') === 'pending');

        if ($sameSlug && $sameEmail && $pending) {
            return true;
        }
    }
    return false;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    out(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$lang = trim((string)($_POST['lang'] ?? 'sv'));
$lang = ($lang === 'en') ? 'en' : 'sv';

$MSG = [
    'sv' => [
        'invalid_csrf' => 'Ogiltig säkerhetstoken. Ladda om sidan och försök igen.',
        'spam' => 'Förfrågan stoppades av spam-skyddet.',
        'required' => 'Fyll i artist, namn och e-post.',
        'email' => 'Ange en giltig e-postadress.',
        'proof' => 'Bevislänken måste vara en giltig URL.',
        'duplicate' => 'Det finns redan en väntande claim för denna artist från samma e-postadress.',
        'ok' => 'Claim mottagen. Vi granskar den manuellt.',
        'server' => 'Serverfel. Försök igen senare.',
    ],
    'en' => [
        'invalid_csrf' => 'Invalid security token. Reload the page and try again.',
        'spam' => 'The request was blocked by spam protection.',
        'required' => 'Please fill in artist, name and email.',
        'email' => 'Please enter a valid email address.',
        'proof' => 'Proof URL must be a valid URL.',
        'duplicate' => 'There is already a pending claim for this artist from the same email address.',
        'ok' => 'Claim received. We will review it manually.',
        'server' => 'Server error. Please try again later.',
    ],
];
$L = $MSG[$lang] ?? $MSG['sv'];

$csrf = post_str('csrf');
if ($csrf === '' || !hash_equals((string)($_SESSION['creator_claim_csrf'] ?? ''), $csrf)) {
    out(['ok' => false, 'error' => $L['invalid_csrf']], 400);
}

// Honeypot
if (post_str('website') !== '') {
    out(['ok' => false, 'error' => $L['spam']], 400);
}

// Min send time
if (too_fast(3)) {
    out(['ok' => false, 'error' => $L['spam']], 400);
}

$creatorName = post_str('creator_name');
$name        = post_str('name');
$email       = post_str('email');
$proofUrl    = post_str('proof_url');
$message     = post_str('message');

if ($creatorName === '' || $name === '' || $email === '') {
    out(['ok' => false, 'error' => $L['required']], 422);
}

if (!valid_email($email)) {
    out(['ok' => false, 'error' => $L['email']], 422);
}

if (!valid_optional_url($proofUrl)) {
    out(['ok' => false, 'error' => $L['proof']], 422);
}

try {
    $creator = ij_creator_find_or_create_by_name($creatorName);
    $slug = trim((string)($creator['slug'] ?? ''));

    if ($slug === '') {
        out(['ok' => false, 'error' => $L['server']], 500);
    }

    if (duplicate_pending_claim($slug, $email)) {
        out(['ok' => false, 'error' => $L['duplicate']], 409);
    }

    $claim = ij_creator_create_claim([
        'creator_name' => $creatorName,
        'name'         => $name,
        'email'        => $email,
        'proof_url'    => $proofUrl,
        'message'      => $message,
    ]);

    out([
        'ok' => true,
        'message' => $L['ok'],
        'claim_id' => (string)($claim['id'] ?? ''),
        'creator_slug' => $slug,
    ]);
} catch (Throwable $e) {
    out(['ok' => false, 'error' => $L['server']], 500);
}