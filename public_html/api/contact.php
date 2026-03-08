<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require __DIR__ . '/../_partials/i18n.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$config = require __DIR__ . '/../_partials/contact_config.php';

function out(array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function client_ip(): string {
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        $v = trim((string)($_SERVER[$key] ?? ''));
        if ($v !== '') {
            if ($key === 'HTTP_X_FORWARDED_FOR') {
                $parts = array_map('trim', explode(',', $v));
                return (string)($parts[0] ?? '');
            }
            return $v;
        }
    }
    return '0.0.0.0';
}

function rate_limit_check(string $ip, array $security): bool {
    $dir = sys_get_temp_dir() . '/icebeats_contact_rate';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    $file = $dir . '/' . sha1($ip) . '.json';
    $now = time();
    $window = (int)($security['window_seconds'] ?? 3600);
    $max = (int)($security['max_per_window'] ?? 5);

    $data = ['hits' => []];
    if (is_file($file)) {
        $raw = json_decode((string)file_get_contents($file), true);
        if (is_array($raw)) $data = $raw;
    }

    $hits = array_values(array_filter((array)($data['hits'] ?? []), static function ($ts) use ($now, $window) {
        return is_numeric($ts) && (int)$ts >= ($now - $window);
    }));

    if (count($hits) >= $max) {
        return false;
    }

    $hits[] = $now;
    file_put_contents($file, json_encode(['hits' => $hits], JSON_UNESCAPED_UNICODE));
    return true;
}

function smtp_expect($socket, array $codes): string {
    $response = '';
    while (!feof($socket)) {
        $line = fgets($socket, 515);
        if ($line === false) break;
        $response .= $line;
        if (preg_match('/^\d{3}\s/', $line)) {
            break;
        }
    }

    $code = (int)substr($response, 0, 3);
    if (!in_array($code, $codes, true)) {
        throw new RuntimeException('SMTP error: ' . trim($response));
    }

    return $response;
}

function smtp_write($socket, string $command): void {
    fwrite($socket, $command . "\r\n");
}

function build_multipart_email(
    string $fromEmail,
    string $fromName,
    string $toEmail,
    string $replyTo,
    string $subject,
    string $text,
    string $html
): string {
    $boundary = 'b_' . bin2hex(random_bytes(12));

    $headers = [];
    $headers[] = 'From: ' . mb_encode_mimeheader($fromName, 'UTF-8') . ' <' . $fromEmail . '>';
    $headers[] = 'To: <' . $toEmail . '>';
    if ($replyTo !== '') {
        $headers[] = 'Reply-To: ' . $replyTo;
    }
    $headers[] = 'Subject: ' . mb_encode_mimeheader($subject, 'UTF-8');
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
    $headers[] = 'Date: ' . date(DATE_RFC2822);
    $headers[] = 'Message-ID: <' . bin2hex(random_bytes(12)) . '@icebeats.io>';

    $body = [];
    $body[] = 'This is a multi-part message in MIME format.';
    $body[] = '--' . $boundary;
    $body[] = 'Content-Type: text/plain; charset=UTF-8';
    $body[] = 'Content-Transfer-Encoding: 8bit';
    $body[] = '';
    $body[] = $text;
    $body[] = '--' . $boundary;
    $body[] = 'Content-Type: text/html; charset=UTF-8';
    $body[] = 'Content-Transfer-Encoding: 8bit';
    $body[] = '';
    $body[] = $html;
    $body[] = '--' . $boundary . '--';
    $body[] = '';

    return implode("\r\n", $headers) . "\r\n\r\n" . implode("\r\n", $body);
}

function smtp_send_mail(
    array $smtp,
    string $fromEmail,
    string $fromName,
    string $toEmail,
    string $replyTo,
    string $subject,
    string $textBody,
    string $htmlBody
): void {
    $host = (string)$smtp['host'];
    $port = (int)$smtp['port'];
    $timeout = (int)($smtp['timeout'] ?? 20);
    $user = (string)$smtp['username'];
    $pass = (string)$smtp['password'];
    $security = strtolower((string)($smtp['security'] ?? 'tls'));

    $socket = @stream_socket_client(
        'tcp://' . $host . ':' . $port,
        $errno,
        $errstr,
        $timeout,
        STREAM_CLIENT_CONNECT
    );

    if (!$socket) {
        throw new RuntimeException('SMTP connect failed: ' . $errstr);
    }

    stream_set_timeout($socket, $timeout);

    smtp_expect($socket, [220]);
    smtp_write($socket, 'EHLO icebeats.io');
    smtp_expect($socket, [250]);

    if ($security === 'tls') {
        smtp_write($socket, 'STARTTLS');
        smtp_expect($socket, [220]);

        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new RuntimeException('Could not enable TLS');
        }

        smtp_write($socket, 'EHLO icebeats.io');
        smtp_expect($socket, [250]);
    }

    smtp_write($socket, 'AUTH LOGIN');
    smtp_expect($socket, [334]);
    smtp_write($socket, base64_encode($user));
    smtp_expect($socket, [334]);
    smtp_write($socket, base64_encode($pass));
    smtp_expect($socket, [235]);

    smtp_write($socket, 'MAIL FROM:<' . $fromEmail . '>');
    smtp_expect($socket, [250]);

    smtp_write($socket, 'RCPT TO:<' . $toEmail . '>');
    smtp_expect($socket, [250, 251]);

    smtp_write($socket, 'DATA');
    smtp_expect($socket, [354]);

    $message = build_multipart_email($fromEmail, $fromName, $toEmail, $replyTo, $subject, $textBody, $htmlBody);

    $message = preg_replace("/(?<!\r)\n/", "\r\n", $message) ?? $message;
    $message = preg_replace('/^\./m', '..', $message) ?? $message;

    fwrite($socket, $message . "\r\n.\r\n");
    smtp_expect($socket, [250]);

    smtp_write($socket, 'QUIT');
    fclose($socket);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    out(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$lang = trim((string)($_POST['lang'] ?? 'sv'));
$lang = ($lang === 'en') ? 'en' : 'sv';

$S = [
    'sv' => [
        'success' => 'Tack! Ditt meddelande är skickat.',
        'generic_error' => 'Något gick fel. Försök igen.',
        'required' => 'Fyll i alla fält.',
        'email_invalid' => 'Ange en giltig e-postadress.',
        'spam' => 'Meddelandet kunde inte skickas.',
        'rate_limited' => 'För många försök just nu. Vänta en stund och försök igen.',
        'subject_admin' => 'Ny kontaktförfrågan från iceBeats.io',
        'autoreply_html' => function(string $name): string {
            return '<html><body style="font-family:Arial,sans-serif;background:#0b1220;color:#e5e7eb;padding:24px;">'
                . '<h2 style="margin-top:0;color:#fff;">Tack för ditt meddelande, ' . h($name) . '!</h2>'
                . '<p>Vi har tagit emot ditt meddelande och återkommer så snart vi kan.</p>'
                . '<p>Med vänliga hälsningar<br>iceBeats.io</p>'
                . '</body></html>';
        },
        'autoreply_text' => function(string $name): string {
            return "Tack för ditt meddelande, {$name}!\n\nVi har tagit emot ditt meddelande och återkommer så snart vi kan.\n\nMed vänliga hälsningar\niceBeats.io";
        },
    ],
    'en' => [
        'success' => 'Thanks! Your message has been sent.',
        'generic_error' => 'Something went wrong. Please try again.',
        'required' => 'Please fill in all fields.',
        'email_invalid' => 'Please enter a valid email address.',
        'spam' => 'The message could not be sent.',
        'rate_limited' => 'Too many attempts right now. Please wait a while and try again.',
        'subject_admin' => 'New contact message from iceBeats.io',
        'autoreply_html' => function(string $name): string {
            return '<html><body style="font-family:Arial,sans-serif;background:#0b1220;color:#e5e7eb;padding:24px;">'
                . '<h2 style="margin-top:0;color:#fff;">Thanks for your message, ' . h($name) . '!</h2>'
                . '<p>We have received your message and will get back to you as soon as we can.</p>'
                . '<p>Best regards<br>iceBeats.io</p>'
                . '</body></html>';
        },
        'autoreply_text' => function(string $name): string {
            return "Thanks for your message, {$name}!\n\nWe have received your message and will get back to you as soon as we can.\n\nBest regards\niceBeats.io";
        },
    ],
];

$L = $S[$lang];

$csrf = (string)($_POST['csrf'] ?? '');
if ($csrf === '' || !hash_equals((string)($_SESSION['contact_csrf'] ?? ''), $csrf)) {
    out(['ok' => false, 'error' => $L['generic_error']], 400);
}

$honeypot = trim((string)($_POST['website'] ?? ''));
if ($honeypot !== '') {
    out(['ok' => false, 'error' => $L['spam']], 400);
}

$loadedAt = (int)($_POST['form_loaded_at'] ?? 0);
$elapsed = time() - $loadedAt;
$minSeconds = (int)($config['security']['min_send_seconds'] ?? 4);
$maxSeconds = (int)($config['security']['max_send_seconds'] ?? 7200);

if ($loadedAt <= 0 || $elapsed < $minSeconds || $elapsed > $maxSeconds) {
    out(['ok' => false, 'error' => $L['spam']], 400);
}

$ip = client_ip();
if (!rate_limit_check($ip, (array)$config['security'])) {
    out(['ok' => false, 'error' => $L['rate_limited']], 429);
}

$name = trim((string)($_POST['name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$subject = trim((string)($_POST['subject'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));

if ($name === '' || $email === '' || $subject === '' || $message === '') {
    out(['ok' => false, 'error' => $L['required']], 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    out(['ok' => false, 'error' => $L['email_invalid']], 422);
}

if (mb_strlen($name) > 120 || mb_strlen($email) > 190 || mb_strlen($subject) > 200 || mb_strlen($message) > 5000) {
    out(['ok' => false, 'error' => $L['generic_error']], 422);
}

$toEmail = (string)$config['mail']['to_email'];
$fromEmail = (string)$config['mail']['from_email'];
$fromName = (string)$config['mail']['from_name'];

$adminSubject = $L['subject_admin'] . ': ' . $subject;

$adminText = implode("\n", [
    "Name: {$name}",
    "Email: {$email}",
    "IP: {$ip}",
    "Lang: {$lang}",
    "Subject: {$subject}",
    "",
    "Message:",
    $message,
]);

$adminHtml = '<html><body style="font-family:Arial,sans-serif;background:#0b1220;color:#e5e7eb;padding:24px;">'
    . '<h2 style="margin-top:0;color:#fff;">' . h($adminSubject) . '</h2>'
    . '<p><strong>Name:</strong> ' . h($name) . '</p>'
    . '<p><strong>Email:</strong> ' . h($email) . '</p>'
    . '<p><strong>IP:</strong> ' . h($ip) . '</p>'
    . '<p><strong>Lang:</strong> ' . h($lang) . '</p>'
    . '<p><strong>Subject:</strong> ' . h($subject) . '</p>'
    . '<hr style="border-color:#24324f;">'
    . '<p style="white-space:pre-wrap;">' . h($message) . '</p>'
    . '</body></html>';

try {
    smtp_send_mail(
        (array)$config['smtp'],
        $fromEmail,
        $fromName,
        $toEmail,
        $email,
        $adminSubject,
        $adminText,
        $adminHtml
    );

    $autoSubject = (string)($lang === 'en'
        ? $config['mail']['autoreply_subject_en']
        : $config['mail']['autoreply_subject_sv']);

    $autoText = $L['autoreply_text']($name);
    $autoHtml = $L['autoreply_html']($name);

    smtp_send_mail(
        (array)$config['smtp'],
        $fromEmail,
        $fromName,
        $email,
        '',
        $autoSubject,
        $autoText,
        $autoHtml
    );

    out([
        'ok' => true,
        'message' => $L['success'],
    ]);
} catch (Throwable $e) {
    out([
        'ok' => false,
        'error' => $L['generic_error'],
    ], 500);
}