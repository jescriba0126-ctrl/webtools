<?php
/**
 * check_slot.php  (fixed)
 *
 * MODE A — calendar (called by homepage.php):
 *   GET ?date=2026-05-28&slot=morning
 *   Returns: { available, remaining, booked, capacity }
 *
 * MODE B — legacy datetime conflict check:
 *   GET ?datetime=2026-05-28 08:00:00
 *   Returns: { available, message }
 */

// ── Suppress warnings/notices so they never corrupt JSON output ───────────────
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
include("connect.php");   // provides $pdo

define('BUFFER_HOURS', 2);

$slotTimes = [
    'morning'   => '08:00:00',
    'afternoon' => '14:00:00',
    'evening'   => '19:00:00',
];

// ── Read daily capacity from settings ────────────────────────────────────────
$DAILY_LIMIT = 100;
try {
    $limitStmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'daily_capacity' LIMIT 1");
    if ($limitStmt) {
        $val = $limitStmt->fetchColumn();
        if ($val !== false && (int)$val > 0) {
            $DAILY_LIMIT = (int)$val;
        }
    }
} catch (Throwable $e) {
    $DAILY_LIMIT = 100;
}

$hasDateSlot = isset($_GET['date'], $_GET['slot']);
$hasDatetime = !$hasDateSlot && isset($_GET['datetime']) && trim($_GET['datetime']) !== '';

// ══════════════════════════════════════════════════════════════════════════════
// MODE A — date + slot  (used by the availability calendar)
// ══════════════════════════════════════════════════════════════════════════════
if ($hasDateSlot) {

    $date = trim($_GET['date']);
    $slot = strtolower(trim($_GET['slot']));

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
        // 1. Guests already booked this specific slot
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

    } catch (Throwable $e) {
        echo json_encode(['available' => false, 'remaining' => 0, 'message' => 'DB error: ' . $e->getMessage()]);
        exit;
    }

    $remaining = max(0, $DAILY_LIMIT - $bookedDay);
    // Slot is available if day has remaining capacity AND this specific slot has no bookings yet
    $available = ($remaining > 0 && $bookedSlot === 0);

    echo json_encode([
        'available' => $available,
        'remaining' => $remaining,
        'booked'    => $bookedSlot,
        'capacity'  => $DAILY_LIMIT,
    ]);
    exit;
}

// ══════════════════════════════════════════════════════════════════════════════
// MODE B — legacy datetime conflict check
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

    try {
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
    } catch (Throwable $e) {
        echo json_encode(['available' => false, 'message' => 'DB error: ' . $e->getMessage()]);
        exit;
    }

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