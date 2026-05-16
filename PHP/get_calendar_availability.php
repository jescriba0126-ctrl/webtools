<?php
// ── Suppress warnings/notices so they never corrupt JSON output ───────────────
error_reporting(0);
ini_set('display_errors', 0);

session_start();
include("connect.php");

header('Content-Type: application/json');

// ── Input ─────────────────────────────────────────────────────────────────────
$year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');

if ($year < 2020 || $year > 2100 || $month < 1 || $month > 12) {
    echo json_encode(['success' => false, 'message' => 'Invalid year/month']);
    exit;
}

// ── Read daily capacity from settings ────────────────────────────────────────
$DAILY_LIMIT = 100; // safe default
try {
    $limitStmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'daily_capacity' LIMIT 1");
    if ($limitStmt) {
        $val = $limitStmt->fetchColumn();
        if ($val !== false && (int)$val > 0) {
            $DAILY_LIMIT = (int)$val;
        }
    }
} catch (Throwable $e) {
    // settings table may not exist yet — use default 100
    $DAILY_LIMIT = 100;
}

// Slot names and their times — must match booking_submit.php $slotMap
$slotTimes = [
    'morning'   => '08:00:00',
    'afternoon' => '14:00:00',
    'evening'   => '19:00:00',
];
$timeToSlot = array_flip($slotTimes);
$slotNames  = array_keys($slotTimes);

// ── Date range ────────────────────────────────────────────────────────────────
$monthStart = sprintf('%04d-%02d-01', $year, $month);
$monthEnd   = date('Y-m-t', strtotime($monthStart));

// ── Fetch all active bookings for this month ──────────────────────────────────
try {
    $stmt = $pdo->prepare("
        SELECT
            DATE(booking_datetime)   AS bdate,
            TIME(booking_datetime)   AS btime,
            COALESCE(SUM(guests), 0) AS booked_guests
        FROM bookings
        WHERE DATE(booking_datetime) BETWEEN ? AND ?
          AND status != 'Cancelled'
        GROUP BY DATE(booking_datetime), TIME(booking_datetime)
    ");
    $stmt->execute([$monthStart, $monthEnd]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    exit;
}

// ── Build lookup: $booked['2026-05-28']['morning'] = 40 ───────────────────────
$booked = [];
foreach ($rows as $row) {
    $t       = $row['btime'];
    // Normalise "8:00:00" → "08:00:00" (MySQL version differences)
    $tPadded = (strlen($t) === 7) ? '0' . $t : $t;
    $slotName = $timeToSlot[$tPadded] ?? null;
    if ($slotName !== null) {
        $booked[$row['bdate']][$slotName] = (int)$row['booked_guests'];
    }
}

// ── Compute per-day availability ──────────────────────────────────────────────
$todayStr    = date('Y-m-d');
$daysInMonth = (int)date('t', strtotime($monthStart));
$availability = [];

for ($d = 1; $d <= $daysInMonth; $d++) {
    $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);

    if ($dateStr < $todayStr) {
        $availability[$dateStr] = ['status' => 'past'];
        continue;
    }

    // Total guests already booked that day (all slots combined)
    $totalBookedDay = 0;
    foreach ($slotNames as $slot) {
        $totalBookedDay += $booked[$dateStr][$slot] ?? 0;
    }

    // How many slots are individually at capacity
    $fullSlots = 0;
    foreach ($slotNames as $slot) {
        if (($booked[$dateStr][$slot] ?? 0) >= $DAILY_LIMIT) {
            $fullSlots++;
        }
    }

    $remaining = max(0, $DAILY_LIMIT - $totalBookedDay);

    if ($remaining === 0) {
        $availability[$dateStr] = ['status' => 'full', 'remaining' => 0];
    } elseif ($fullSlots > 0 || $totalBookedDay > 0) {
        $availability[$dateStr] = ['status' => 'partial', 'remaining' => $remaining];
    } else {
        $availability[$dateStr] = ['status' => 'open', 'remaining' => $remaining];
    }
}

echo json_encode(['success' => true, 'availability' => $availability]);