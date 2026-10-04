<?php
/**
 * Database Connection File
 * -------------------------------------------------------------
 * This file MUST sit directly inside C:\xampp\htdocs\solid\ ,
 * as a sibling of index.php, config.php, header.php, footer.php.
 *
 * Uses mysqli with exception mode so every query/prepare call
 * throws on failure instead of silently returning false, which
 * lets pages wrap risky queries in try/catch blocks.
 *
 * Defaults below match a standard local XAMPP setup:
 *   host: localhost | user: root | password: (empty) | port: 3306
 * Edit the constants below if your setup is different.
 */

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'solidwaste_scheduling');
define('DB_PORT', 3306);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    die(
        '<div style="font-family:-apple-system,Segoe UI,Roboto,sans-serif;max-width:600px;margin:4rem auto;padding:2rem;border:1px solid #f5c6cb;background:#f8d7da;color:#721c24;border-radius:12px;">'
        . '<h2 style="margin-bottom:0.75rem;">Database Connection Failed</h2>'
        . '<p>Could not connect to the <strong>' . htmlspecialchars(DB_NAME) . '</strong> database.</p>'
        . '<p>Please check that MySQL is running (e.g. via XAMPP Control Panel) and that the credentials in <code>db.php</code> are correct.</p>'
        . '<p style="opacity:0.75;font-size:0.85rem;margin-top:1rem;">Error detail: ' . htmlspecialchars($e->getMessage()) . '</p>'
        . '</div>'
    );
}
