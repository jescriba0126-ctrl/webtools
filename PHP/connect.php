<?php
// ── Suppress PHP warnings/notices so they never corrupt JSON output ───────────
error_reporting(0);
ini_set('display_errors', 0);

$host   = 'localhost';
$dbname = 'login';
$user   = 'root';
$pass   = '';

// ── mysqli (legacy, kept for any scripts that use $conn) ─────────────────────
$conn = new mysqli($host, $user, $pass, $dbname);
// NOTE: do NOT echo here — it breaks JSON responses from API scripts

// ── PDO (used by booking scripts, calendar, check_slot, etc.) ────────────────
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    // Only output JSON error — never raw HTML
    die(json_encode(['success' => false, 'error' => 'DB connection failed: ' . $e->getMessage()]));
}