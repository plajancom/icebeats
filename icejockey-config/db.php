<?php
// KEEP THIS FILE OUTSIDE public_html

define('IJ_DB_HOST', 'localhost');
define('IJ_DB_NAME', 'icejockey_audio');
define('IJ_DB_USER', 'icejockey_audio');
define('IJ_DB_PASS', 'AfuSUkVyRv2NS2Qhydrj');

function ij_db(): PDO {
  static $pdo = null;
  if ($pdo) return $pdo;

  $dsn = 'mysql:host=' . IJ_DB_HOST . ';dbname=' . IJ_DB_NAME . ';charset=utf8mb4';

  $opts = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ];

  $pdo = new PDO($dsn, IJ_DB_USER, IJ_DB_PASS, $opts);
  return $pdo;
}