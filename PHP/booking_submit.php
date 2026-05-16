<?php
// booking_submit.php
// Called by the booking form via fetch (AJAX POST)
// Returns JSON so the frontend can show success/error without a page reload

session_start();
header('Content-Type: application/json');

include("connect.php");  // gives us $pdo

// ── helpers ──────────────────────────────────────────────────
function clean(string $v): string {
    return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
}

function getAmount(string $pkg): float {
    return match($pkg) {
        'basic'    => 2000.00,
        'standard' => 5000.00,
        'premium'  => 10000.00,
        default    => 0.00,
    };
}

// ── Booking hours (24h) ───────────────────────────────────────
define('BOOKING_OPEN',   8);  // 8:00 AM
define('BOOKING_CLOSE', 22);  // 10:00 PM
define('BUFFER_HOURS',   2);  // 2-hour buffer between bookings

// ── validate method ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// ── collect & validate fields ────────────────────────────────
$name     = clean($_POST['name']     ?? '');
$email    = clean($_POST['email']    ?? '');
$phone    = clean($_POST['phone']    ?? '');
$occasion = clean($_POST['occasion'] ?? '');
$guests   = (int) ($_POST['guests']  ?? 0);
$package  = clean($_POST['package']  ?? '');
$payment  = clean($_POST['payment']  ?? '');
$datetime = clean($_POST['datetime'] ?? '');    // "2025-12-25T19:00"
$notes    = clean($_POST['message']  ?? '');

$allowed_occasions = ['dine-in','birthday','wedding','corporate','other'];
$allowed_payments  = ['GCash','Maya','Online Banking','Credit / Debit Card','Cash'];

if (!$name || !$email || !$phone || !$occasion || !$guests || !$payment || !$datetime) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

if (!in_array($occasion, $allowed_occasions)) {
    echo json_encode(['success' => false, 'message' => 'Invalid occasion selected.']);
    exit;
}

if (!in_array($payment, $allowed_payments)) {
    echo json_encode(['success' => false, 'message' => 'Invalid payment method.']);
    exit;
}

if ($guests < 1 || $guests > 500) {
    echo json_encode(['success' => false, 'message' => 'Guest count must be between 1 and 500.']);
    exit;
}

// ── Convert "2025-12-25T19:00" -> "2025-12-25 19:00:00" ──────
$bookingDT = date('Y-m-d H:i:s', strtotime($datetime));
if (!$bookingDT || $bookingDT === '1970-01-01 00:00:00') {
    echo json_encode(['success' => false, 'message' => 'Invalid date/time.']);
    exit;
}

// ── Block past dates & times ──────────────────────────────────
if (strtotime($bookingDT) <= time()) {
    echo json_encode(['success' => false, 'message' => 'Please select a future date and time.']);
    exit;
}

// ── Block outside business hours ─────────────────────────────
$bookingHour = (int) date('G', strtotime($bookingDT));
if ($bookingHour < BOOKING_OPEN || $bookingHour >= BOOKING_CLOSE) {
    echo json_encode([
        'success' => false,
        'message' => 'Bookings are only accepted between 8:00 AM and 10:00 PM. Please choose a valid time.'
    ]);
    exit;
}

// ── GCash fields (only required when payment = GCash) ────────
$gcash_name      = clean($_POST['gcash_name']      ?? '');
$gcash_number    = clean($_POST['gcash_number']    ?? '');
$gcash_reference = clean($_POST['gcash_reference'] ?? '');

if ($payment === 'GCash') {
    if (!$gcash_name || !$gcash_number || !$gcash_reference) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all GCash details (name, number, and reference number).']);
        exit;
    }
    if (!preg_match('/^09\d{9}$/', $gcash_number)) {
        echo json_encode(['success' => false, 'message' => 'Invalid GCash number. Must be 11 digits starting with 09.']);
        exit;
    }
}

// ── generate unique ticket number (collision-safe) ───────────
// Uses random_bytes for stronger uniqueness, retries on collision
$ticket = null;
for ($i = 0; $i < 5; $i++) {
    $candidate = 'T' . strtoupper(substr(bin2hex(random_bytes(5)), 0, 8));
    $chk = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE ticket_number = ?");
    $chk->execute([$candidate]);
    if ((int)$chk->fetchColumn() === 0) {
        $ticket = $candidate;
        break;
    }
}
if (!$ticket) {
    echo json_encode(['success' => false, 'message' => 'Could not generate a unique ticket. Please try again.']);
    exit;
}

// ── Handle proof of payment upload ───────────────────────────
//
//  FOLDER STRUCTURE:
//    /your-project/
//      PHP/
//        booking_submit.php   <- __DIR__
//        payment_admin.php
//        uploads/
//          proofs/            <- images saved here
//
//  proof_path stored in DB as a root-relative web URL:
//    /PHP/uploads/proofs/proof_TICKET_TIME.jpg
//  This works as <img src="/PHP/uploads/proofs/..."> from ANY page.
//
$proof_path = null;

if ($payment === 'GCash') {

    if (empty($_FILES['proof']['tmp_name'])) {
        echo json_encode(['success' => false, 'message' => 'Please upload your GCash payment screenshot.']);
        exit;
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_type     = mime_content_type($_FILES['proof']['tmp_name']);

    if (!in_array($file_type, $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Proof must be a JPG, PNG, GIF, or WEBP image.']);
        exit;
    }

    if ($_FILES['proof']['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Proof image must be under 5MB.']);
        exit;
    }

    // Physical disk path — inside the PHP/ folder alongside this script
    $upload_dir = __DIR__ . '/uploads/proofs/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $ext       = strtolower(pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION));
    $filename  = 'proof_' . $ticket . '_' . time() . '.' . $ext;
    $full_path = $upload_dir . $filename;

    if (!move_uploaded_file($_FILES['proof']['tmp_name'], $full_path)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save proof image. Please try again.']);
        exit;
    }

    // Root-relative web URL — works as <img src="..."> from any page on the site
    $proof_path = '/PHP/uploads/proofs/' . $filename;
}

// ── Time slot conflict check (2-hour buffer) ──────────────────
$bufferSecs  = BUFFER_HOURS * 3600;
$windowStart = date('Y-m-d H:i:s', strtotime($bookingDT) - $bufferSecs);
$windowEnd   = date('Y-m-d H:i:s', strtotime($bookingDT) + $bufferSecs);

$conflictStmt = $pdo->prepare("
    SELECT COUNT(*) AS conflicts
    FROM bookings
    WHERE booking_datetime BETWEEN :start AND :end
    AND status != 'Cancelled'
");
$conflictStmt->execute([
    ':start' => $windowStart,
    ':end'   => $windowEnd,
]);
$conflicts = (int) $conflictStmt->fetchColumn();

if ($conflicts > 0) {
    $nextStmt = $pdo->prepare("
        SELECT booking_datetime
        FROM bookings
        WHERE booking_datetime BETWEEN :start AND :end
        AND status != 'Cancelled'
        ORDER BY booking_datetime ASC
        LIMIT 1
    ");
    $nextStmt->execute([
        ':start' => $windowStart,
        ':end'   => $windowEnd,
    ]);
    $conflictRow  = $nextStmt->fetch();
    $conflictTime = $conflictRow
        ? date('F j, Y \a\t g:i A', strtotime($conflictRow['booking_datetime']))
        : 'that time';

    echo json_encode([
        'success' => false,
        'message' => "Sorry, that time slot is unavailable. There is already a booking near {$conflictTime}. Please choose a time at least 2 hours apart."
    ]);
    exit;
}

// ── Daily capacity check ──────────────────────────────────────
$bookingDate = date('Y-m-d', strtotime($bookingDT));

$limitStmt   = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'daily_capacity'");
$DAILY_LIMIT = (int)($limitStmt->fetchColumn() ?: 100);

$capStmt = $pdo->prepare("
    SELECT COALESCE(SUM(guests), 0) AS total_guests
    FROM bookings
    WHERE DATE(booking_datetime) = ?
    AND status != 'Cancelled'
");
$capStmt->execute([$bookingDate]);
$bookedGuests = (int) $capStmt->fetchColumn();

if (($bookedGuests + $guests) > $DAILY_LIMIT) {
    $remaining = max(0, $DAILY_LIMIT - $bookedGuests);
    echo json_encode([
        'success' => false,
        'message' => "Sorry, we're fully booked for that date. Only {$remaining} guest slot(s) remaining."
    ]);
    exit;
}

// ── get amount from package ───────────────────────────────────
$amount = getAmount($package);

// ── optional: link to logged-in user ─────────────────────────
$userId = $_SESSION['id'] ?? null;

// ── insert ────────────────────────────────────────────────────
try {
    $stmt = $pdo->prepare("
        INSERT INTO bookings
            (ticket_number, user_id, name, email, phone, occasion, guests,
             package, amount, payment_method, booking_datetime, special_notes,
             gcash_name, gcash_number, gcash_reference, proof_path, status)
        VALUES
            (:ticket, :user_id, :name, :email, :phone, :occasion, :guests,
             :package, :amount, :payment, :datetime, :notes,
             :gcash_name, :gcash_number, :gcash_reference, :proof_path, 'Pending')
    ");

    $stmt->execute([
        ':ticket'          => $ticket,
        ':user_id'         => $userId,
        ':name'            => $name,
        ':email'           => $email,
        ':phone'           => $phone,
        ':occasion'        => $occasion,
        ':guests'          => $guests,
        ':package'         => $package,
        ':amount'          => $amount,
        ':payment'         => $payment,
        ':datetime'        => $bookingDT,
        ':notes'           => $notes,
        ':gcash_name'      => $gcash_name,
        ':gcash_number'    => $gcash_number,
        ':gcash_reference' => $gcash_reference,
        ':proof_path'      => $proof_path,
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Booking submitted successfully!',
        'ticket'  => $ticket,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>