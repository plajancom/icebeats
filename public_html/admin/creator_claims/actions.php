<?php
declare(strict_types=1);

require __DIR__ . '/../../admin/lib.php';
require __DIR__ . '/../../_partials/i18n.php';
require __DIR__ . '/../../_partials/creator_lib.php';

$lang = trim((string)($_POST['lang'] ?? $_GET['lang'] ?? 'sv'));
$lang = ($lang === 'en') ? 'en' : 'sv';

function ij_claims_redirect(string $status, string $lang, string $msg): void {
    header('Location: ' . ij_url('/admin/creator_claims/?status=' . rawurlencode($status) . '&lang=' . rawurlencode($lang) . '&msg=' . rawurlencode($msg)));
    exit;
}

function ij_claims_fail(string $msg, int $code = 400): void {
    http_response_code($code);
    echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    ij_claims_fail('Method not allowed', 405);
}

/**
 * Försök validera CSRF på ett sätt som passar din befintliga kodbas.
 * Prioritet:
 * 1) csrf_check()
 * 2) csrf_validate()
 * 3) session-token jämförelse som fallback
 */
$csrf = (string)($_POST['csrf'] ?? '');
$csrfOk = false;

if (function_exists('csrf_check')) {
    $csrfOk = (bool)csrf_check($csrf);
} elseif (function_exists('csrf_validate')) {
    $csrfOk = (bool)csrf_validate($csrf);
} else {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    $sessionToken = (string)($_SESSION['csrf'] ?? $_SESSION['csrf_token'] ?? '');
    if ($sessionToken !== '' && hash_equals($sessionToken, $csrf)) {
        $csrfOk = true;
    }
}

if (!$csrfOk) {
    ij_claims_fail('Invalid CSRF token', 400);
}

$action = trim((string)($_POST['action'] ?? ''));
$id = trim((string)($_POST['id'] ?? ''));

if ($id === '') {
    ij_claims_redirect('pending', $lang, 'Missing claim id');
}

/**
 * Säkerhetskontroller så vi får tydligare fel om creator_lib.php inte innehåller funktionerna ännu.
 */
if ($action === 'approve') {
    if (!function_exists('ij_creator_approve_claim')) {
        ij_claims_fail('Missing function: ij_creator_approve_claim()', 500);
    }

    try {
        $ok = ij_creator_approve_claim($id);
        ij_claims_redirect($ok ? 'approved' : 'pending', $lang, $ok ? 'Claim approved' : 'Could not approve claim');
    } catch (Throwable $e) {
        ij_claims_fail('Approve failed: ' . $e->getMessage(), 500);
    }
}

if ($action === 'reject') {
    if (!function_exists('ij_creator_reject_claim')) {
        ij_claims_fail('Missing function: ij_creator_reject_claim()', 500);
    }

    try {
        $ok = ij_creator_reject_claim($id);
        ij_claims_redirect('rejected', $lang, $ok ? 'Claim rejected' : 'Could not reject claim');
    } catch (Throwable $e) {
        ij_claims_fail('Reject failed: ' . $e->getMessage(), 500);
    }
}

ij_claims_redirect('pending', $lang, 'Unknown action');