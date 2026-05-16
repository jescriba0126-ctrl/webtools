<?php
/**
 * check_slot.php
 *
 * MODE A — new calendar (called by homepage.php):
 *   GET ?date=2026-05-28&slot=morning
 *   Returns: { available, remaining, booked, capacity }
 *
 * MODE B — legacy (your original, untouched):
 *   GET ?datetime=2026-05-28 08:00:00
 *   Returns: { available, message }
 *
 * Matches booking_submit.php exactly:
 *   - booking_datetime  DATETIME
 *   - guests            INT
 *   - status != 'Cancelled'   (capital C)
 *   - daily_capacity from settings table
 *
 * Slot → time mapping (same as booking_submit.php $slotMap):
 *   morning   → 08:00:00
 *   afternoon → 14:00:00
 *   evening   → 19:00:00
 */

header('Content-Type: application/json');
include("connect.php");   // provides $pdo

define('BUFFER_HOURS', 2);   // kept for MODE B

$slotTimes = [
    'morning'   => '08:00:00',
    'afternoon' => '14:00:00',
    'evening'   => '19:00:00',
];

// ── Read daily capacity from settings (same as booking_submit.php) ────────────
try {
    $limitStmt   = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'daily_capacity'");
    $DAILY_LIMIT = (int)($limitStmt->fetchColumn() ?: 100);
} catch (Exception $e) {
    $DAILY_LIMIT = 100;
}

$hasDateSlot = isset($_GET['date'], $_GET['slot']);
$hasDatetime = !$hasDateSlot && isset($_GET['datetime']) && trim($_GET['datetime']) !== '';

// ══════════════════════════════════════════════════════════════════════════════
// MODE A — date + slot  (used by the availability calendar)
// ══════════════════════════════════════════════════════════════════════════════
if ($hasDateSlot) {

    $date = trim($_GET['date']);
    $slot = trim($_GET['slot']);

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        echo json_encode(['available' => false, 'remaining' => 0, 'message' => 'Invalid date format']);
        exit;
    }
    if ($date < date('Y-m-d')) {
        echo json_encode(['available' => false, 'remaining' => 0, 'message' => 'Date is in the past']);
        exit;
    }
    if (!array_key_exists($slot, $slotTimes)) {
        echo json_encode(['available' => false, 'remaining' => 0, 'message' => 'Invalid slot']);
        exit;
    }

    try {
        // 1. How many guests already booked this specific slot
        $slotTime = $slotTimes[$slot];
        $slotStmt = $pdo->prepare("
            SELECT COALESCE(SUM(guests), 0) AS booked_slot
            FROM bookings
            WHERE DATE(booking_datetime) = ?
              AND TIME(booking_datetime) = ?
              AND status != 'Cancelled'
        ");
        $slotStmt->execute([$date, $slotTime]);
        $bookedSlot = (int)$slotStmt->fetchColumn();

        // 2. Total guests across ALL slots that day (for daily cap check)
        $dayStmt = $pdo->prepare("
            SELECT COALESCE(SUM(guests), 0) AS booked_day
            FROM bookings
            WHERE DATE(booking_datetime) = ?
              AND status != 'Cancelled'
        ");
        $dayStmt->execute([$date]);
        $bookedDay = (int)$dayStmt->fetchColumn();

    } catch (Exception $e) {
        echo json_encode(['available' => false, 'remaining' => 0, 'message' => 'DB error: ' . $e->getMessage()]);
        exit;
    }

    // Remaining = how many more guests can be added today overall
    $remaining = max(0, $DAILY_LIMIT - $bookedDay);
    $available = $remaining > 0 && $bookedSlot === 0;  // slot must also be empty

    echo json_encode([
        'available' => $available,
        'remaining' => $remaining,
        'booked'    => $bookedSlot,
        'capacity'  => $DAILY_LIMIT,
    ]);
    exit;
}

// ══════════════════════════════════════════════════════════════════════════════
// MODE B — legacy datetime conflict check (original logic, 100% unchanged)
// ══════════════════════════════════════════════════════════════════════════════
if ($hasDatetime) {

    $datetime = trim($_GET['datetime']);
    $ts       = strtotime($datetime);

    if (!$ts) {
        echo json_encode(['available' => false, 'message' => 'Invalid date/time.']);
        exit;
    }

    $bufferSecs  = BUFFER_HOURS * 3600;
    $windowStart = date('Y-m-d H:i:s', $ts - $bufferSecs);
    $windowEnd   = date('Y-m-d H:i:s', $ts + $bufferSecs);

    $stmt = $pdo->prepare("
        SELECT booking_datetime
        FROM bookings
        WHERE booking_datetime BETWEEN :start AND :end
          AND status != 'Cancelled'
        ORDER BY booking_datetime ASC
        LIMIT 1
    ");
    $stmt->execute([':start' => $windowStart, ':end' => $windowEnd]);
    $conflict = $stmt->fetch();

    if ($conflict) {
        $conflictTime = date('F j, Y \a\t g:i A', strtotime($conflict['booking_datetime']));
        echo json_encode([
            'available' => false,
            'message'   => "That time slot is unavailable. There is already a booking near {$conflictTime}. Please choose a time at least 2 hours apart."
        ]);
    } else {
        echo json_encode(['available' => true, 'message' => 'Time slot is available.']);
    }
    exit;
}

// ── Neither mode matched ──────────────────────────────────────────────────────
echo json_encode(['available' => false, 'message' => 'No valid parameters provided.']);