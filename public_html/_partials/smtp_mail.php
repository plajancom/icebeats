<?php
declare(strict_types=1);

/**
 * Enkel SMTP-mailer för magic links.
 * Fyll i konstanterna nedan med dina SMTP2GO-uppgifter.
 */

if (!defined('IJ_SMTP_HOST')) {
    define('IJ_SMTP_HOST', 'smtp.smtp2go.com');
}
if (!defined('IJ_SMTP_PORT')) {
    define('IJ_SMTP_PORT', 587);
}
if (!defined('IJ_SMTP_SECURE')) {
    // 'ssl' för 465, 'tls' för 587, eller '' för ingen kryptering
    define('IJ_SMTP_SECURE', 'tsl');
}
if (!defined('IJ_SMTP_USER')) {
    define('IJ_SMTP_USER', 'pedesign.se');
}
if (!defined('IJ_SMTP_PASS')) {
    define('IJ_SMTP_PASS', 'cmM2xkQnnIAJ7ZRt');
}
if (!defined('IJ_MAIL_FROM')) {
    define('IJ_MAIL_FROM', 'noreply@icebeats.io');
}
if (!defined('IJ_MAIL_FROM_NAME')) {
    define('IJ_MAIL_FROM_NAME', 'iceBeats.io');
}

if (!function_exists('ij_smtp_read')) {
    function ij_smtp_read($fp): string {
        $data = '';
        while (!feof($fp)) {
            $line = fgets($fp, 515);
            if ($line === false) break;
            $data .= $line;
            if (preg_match('/^\d{3}\s/', $line)) break;
        }
        return $data;
    }
}

if (!function_exists('ij_smtp_expect')) {
    function ij_smtp_expect($fp, array $codes): string {
        $resp = ij_smtp_read($fp);
        $ok = false;
        foreach ($codes as $code) {
            if (str_starts_with($resp, (string)$code)) {
                $ok = true;
                break;
            }
        }
        if (!$ok) {
            throw new RuntimeException('SMTP unexpected response: ' . trim($resp));
        }
        return $resp;
    }
}

if (!function_exists('ij_smtp_cmd')) {
    function ij_smtp_cmd($fp, string $cmd, array $expectCodes): string {
        fwrite($fp, $cmd . "\r\n");
        return ij_smtp_expect($fp, $expectCodes);
    }
}

if (!function_exists('ij_mail_header_b64')) {
    function ij_mail_header_b64(string $text): string {
        return '=?UTF-8?B?' . base64_encode($text) . '?=';
    }
}

if (!function_exists('ij_send_smtp_mail')) {
    function ij_send_smtp_mail(string $to, string $subject, string $textBody): bool {
        $host = trim((string)IJ_SMTP_HOST);
        $port = (int)IJ_SMTP_PORT;
        $secure = trim((string)IJ_SMTP_SECURE);
        $user = trim((string)IJ_SMTP_USER);
        $pass = (string)IJ_SMTP_PASS;
        $from = trim((string)IJ_MAIL_FROM);
        $fromName = trim((string)IJ_MAIL_FROM_NAME);

        if ($host === '' || $port <= 0 || $user === '' || $pass === '' || $from === '') {
            throw new RuntimeException('SMTP config missing');
        }

        $transportHost = $host;
        if ($secure === 'ssl') {
            $transportHost = 'ssl://' . $host;
        }

        $fp = @stream_socket_client(
            $transportHost . ':' . $port,
            $errno,
            $errstr,
            20,
            STREAM_CLIENT_CONNECT
        );

        if (!$fp) {
            throw new RuntimeException('SMTP connect failed: ' . $errstr . ' (' . $errno . ')');
        }

        stream_set_timeout($fp, 20);

        try {
            ij_smtp_expect($fp, [220]);

            $ehloHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
            ij_smtp_cmd($fp, 'EHLO ' . $ehloHost, [250]);

            if ($secure === 'tls') {
                ij_smtp_cmd($fp, 'STARTTLS', [220]);
                $cryptoOk = @stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                if (!$cryptoOk) {
                    throw new RuntimeException('Could not enable TLS');
                }
                ij_smtp_cmd($fp, 'EHLO ' . $ehloHost, [250]);
            }

            ij_smtp_cmd($fp, 'AUTH LOGIN', [334]);
            ij_smtp_cmd($fp, base64_encode($user), [334]);
            ij_smtp_cmd($fp, base64_encode($pass), [235]);

            ij_smtp_cmd($fp, 'MAIL FROM:<' . $from . '>', [250]);
            ij_smtp_cmd($fp, 'RCPT TO:<' . $to . '>', [250, 251]);
            ij_smtp_cmd($fp, 'DATA', [354]);

            $headers = [];
            $headers[] = 'Date: ' . date(DATE_RFC2822);
            $headers[] = 'From: ' . ij_mail_header_b64($fromName) . ' <' . $from . '>';
            $headers[] = 'To: <' . $to . '>';
            $headers[] = 'Subject: ' . ij_mail_header_b64($subject);
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
            $headers[] = 'Content-Transfer-Encoding: 8bit';

            $body = str_replace(["\r\n", "\r"], "\n", $textBody);
            $body = str_replace("\n.", "\n..", $body);
            $body = str_replace("\n", "\r\n", $body);

            $payload = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
            fwrite($fp, $payload . "\r\n");
            ij_smtp_expect($fp, [250]);

            ij_smtp_cmd($fp, 'QUIT', [221]);
            fclose($fp);
            return true;
        } catch (Throwable $e) {
            fclose($fp);
            throw $e;
        }
    }
}