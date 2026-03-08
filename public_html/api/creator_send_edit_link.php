<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require __DIR__ . '/../_partials/creator_lib.php';
require __DIR__ . '/../_partials/smtp_mail.php';
require __DIR__ . '/../_partials/i18n.php';

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

function too_fast(int $minSeconds = 3): bool {
    $loadedAt = (int)($_POST['form_loaded_at'] ?? 0);
    if ($loadedAt <= 0) return true;
    return (time() - $loadedAt) < $minSeconds;
}

function recent_token_exists(string $creatorSlug, string $email, int $cooldownSeconds = 300): bool {
    $tokens = ij_creator_tokens_load_all();
    $cutoff = time() - $cooldownSeconds;
    $emailNorm = mb_strtolower(trim($email), 'UTF-8');

    foreach ($tokens as $row) {
        if (!is_array($row)) continue;
        if ((string)($row['creator_slug'] ?? '') !== $creatorSlug) continue;
        if (mb_strtolower(trim((string)($row['email'] ?? '')), 'UTF-8') !== $emailNorm) continue;
        if ((int)($row['created_at'] ?? 0) >= $cutoff) {
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
        'required' => 'Fyll i artist och e-post.',
        'email' => 'Ange en giltig e-postadress.',
        'generic_ok' => 'Om uppgifterna stämmer har en redigeringslänk skickats till e-posten.',
        'server' => 'Serverfel. Försök igen senare.',
        'mail_subject' => 'Din redigeringslänk till iceBeats.io',
        'mail_body' =>
"Du har begärt en redigeringslänk till din artistprofil på iceBeats.io.

Öppna länken nedan för att redigera din profil:
%s

Länken är tillfällig och gäller under en begränsad tid.

Om du inte begärde detta kan du ignorera detta mejl.

iceBeats.io",
    ],
    'en' => [
        'invalid_csrf' => 'Invalid security token. Reload the page and try again.',
        'spam' => 'The request was blocked by spam protection.',
        'required' => 'Please fill in artist and email.',
        'email' => 'Please enter a valid email address.',
        'generic_ok' => 'If the details matched, an edit link has been sent to the email address.',
        'server' => 'Server error. Please try again later.',
        'mail_subject' => 'Your iceBeats.io edit link',
        'mail_body' =>
"You requested an edit link for your artist profile on iceBeats.io.

Open the link below to edit your profile:
%s

This link is temporary and only valid for a limited time.

If you did not request this, you can ignore this email.

iceBeats.io",
    ],
];
$L = $MSG[$lang] ?? $MSG['sv'];

$csrf = post_str('csrf');
if ($csrf === '' || !hash_equals((string)($_SESSION['creator_editlink_csrf'] ?? ''), $csrf)) {
    out(['ok' => false, 'error' => $L['invalid_csrf']], 400);
}

if (post_str('website') !== '') {
    out(['ok' => false, 'error' => $L['spam']], 400);
}

if (too_fast(3)) {
    out(['ok' => false, 'error' => $L['spam']], 400);
}

$creatorName = post_str('creator_name');
$email = post_str('email');

if ($creatorName === '' || $email === '') {
    out(['ok' => false, 'error' => $L['required']], 422);
}

if (!valid_email($email)) {
    out(['ok' => false, 'error' => $L['email']], 422);
}

// För säkerhet: returnera samma svar även om något inte matchar
$genericResponse = ['ok' => true, 'message' => $L['generic_ok']];

try {
    $creator = ij_creator_find_by_name($creatorName);

    if (!$creator || !is_array($creator)) {
        out($genericResponse);
    }

    $ownerEmail = trim((string)($creator['owner_email'] ?? ''));
    $verified = !empty($creator['verified']);
    $slug = trim((string)($creator['slug'] ?? ''));

    if (!$verified || $ownerEmail === '' || $slug === '') {
        out($genericResponse);
    }

    if (mb_strtolower($ownerEmail, 'UTF-8') !== mb_strtolower($email, 'UTF-8')) {
        out($genericResponse);
    }

    if (recent_token_exists($slug, $email, 300)) {
        out($genericResponse);
    }

    $tokenData = ij_creator_generate_edit_token($slug, $email, 1800);
    $plainToken = (string)($tokenData['plain_token'] ?? '');

    if ($plainToken === '') {
        out($genericResponse);
    }

    $editUrl = ij_abs('/artist/edit/?token=' . rawurlencode($plainToken) . '&lang=' . rawurlencode($lang));
    $subject = $L['mail_subject'];
    $body = sprintf($L['mail_body'], $editUrl);

    ij_send_smtp_mail($email, $subject, $body);

    out($genericResponse);
} catch (Throwable $e) {
    out(['ok' => false, 'error' => $L['server']], 500);
}