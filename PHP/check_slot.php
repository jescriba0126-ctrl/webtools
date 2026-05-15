<?php
// check_slot.php
// Called via GET from the booking form to check if a time slot is available
// before the user even submits — gives instant feedback

header('Content-Type: application/json');
include("connect.php");

define('BUFFER_HOURS', 2);

$datetime = trim($_GET['datetime'] ?? '');

if (!$datetime) {
    echo json_encode(['available' => false, 'message' => 'No datetime provided.']);
    exit;
}

$ts = strtotime($datetime);
if (!$ts || $ts === false) {
    echo json_encode(['available' => false, 'message' => 'Invalid date/time.']);
    exit;
}

$bookingDT   = date('Y-m-d H:i:s', $ts);
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
$stmt->execute([
    ':start' => $windowStart,
    ':end'   => $windowEnd,
]);
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
?>