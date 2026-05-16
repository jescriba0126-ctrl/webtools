<?php

 */

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

// ── Read daily capacity from settings (same as booking_submit.php) ────────────
try {
    $limitStmt   = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'daily_capacity'");
    $DAILY_LIMIT = (int)($limitStmt->fetchColumn() ?: 100);
} catch (Exception $e) {
    $DAILY_LIMIT = 100;   // fallback if settings table missing
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
            DATE(booking_datetime)       AS bdate,
            TIME(booking_datetime)       AS btime,
            COALESCE(SUM(guests), 0)     AS booked_guests
        FROM bookings
        WHERE DATE(booking_datetime) BETWEEN ? AND ?
          AND status != 'Cancelled'
        GROUP BY DATE(booking_datetime), TIME(booking_datetime)
    ");
    $stmt->execute([$monthStart, $monthEnd]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    exit;
}

// ── Build lookup: $booked['2026-05-28']['morning'] = 40 ───────────────────────
$booked = [];
foreach ($rows as $row) {
    // TIME() may return "08:00:00" or "8:00:00" depending on MySQL version
    // Normalise to H:i:s with leading zero
    $t        = $row['btime'];
    $tPadded  = strlen($t) === 7 ? '0' . $t : $t;   // "8:00:00" → "08:00:00"
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
        $slotBooked = $booked[$dateStr][$slot] ?? 0;
        // A slot is "full" if adding 1 more guest would exceed daily limit
        // OR the slot itself already has bookings ≥ DAILY_LIMIT
        if ($slotBooked >= $DAILY_LIMIT) {
            $fullSlots++;
        }
    }

    // Remaining guest capacity for the whole day
    $remaining = max(0, $DAILY_LIMIT - $totalBookedDay);

    if ($remaining === 0) {
        $availability[$dateStr] = ['status' => 'full', 'remaining' => 0];
    } elseif ($fullSlots > 0 || $totalBookedDay > 0) {
        // Some bookings exist but still space left
        $availability[$dateStr] = ['status' => 'partial', 'remaining' => $remaining];
    } else {
        $availability[$dateStr] = ['status' => 'open', 'remaining' => $remaining];
    }
}

echo json_encode(['success' => true, 'availability' => $availability]);