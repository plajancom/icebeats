<?php
declare(strict_types=1);

/**
 * Kontaktformulär-konfiguration
 * Lägg denna fil i: /_partials/contact_config.php
 */

return [
    'smtp' => [
        'host' => 'smtp.smtp2go.com',
        'port' => 587,
        'security' => 'tls', // tls
        'username' => 'pedesign.se',
        'password' => 'cmM2xkQnnIAJ7ZRt',
        'timeout' => 20,
    ],

    'mail' => [
        'to_email' => 'hello@icebeats.io',
        'to_name' => 'iceBeats.io',
        'from_email' => 'noreply@icebeats.io',
        'from_name' => 'iceBeats.io',
        'autoreply_subject_sv' => 'Tack för ditt meddelande till iceBeats.io',
        'autoreply_subject_en' => 'Thanks for contacting iceBeats.io',
    ],

    'security' => [
        'min_send_seconds' => 3,
        'max_send_seconds' => 7200,
        'max_per_window' => 5,
        'window_seconds' => 3600,
    ],
];