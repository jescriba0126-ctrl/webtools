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

// Convert "2025-12-25T19:00" → "2025-12-25 19:00:00"
$bookingDT = date('Y-m-d H:i:s', strtotime($datetime));
if (!$bookingDT || $bookingDT === '1970-01-01 00:00:00') {
    echo json_encode(['success' => false, 'message' => 'Invalid date/time.']);
    exit;
}

// ── capacity check ───────────────────────────────────────────
$bookingDate = date('Y-m-d', strtotime($bookingDT));

// Read the limit from the DB (falls back to 100 if not set)
$limitStmt   = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'daily_capacity'");
$DAILY_LIMIT = (int)($limitStmt->fetchColumn() ?: 100);

// Sum all non-cancelled guests already booked on that date
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
// ── end capacity check ───────────────────────────────────────

// ── generate unique ticket number ────────────────────────────
$ticket = 'T' . strtoupper(substr(uniqid(), -8));

// ── get amount from package ───────────────────────────────────
$amount = getAmount($package);

// ── optional: link to logged-in user ─────────────────────────
$userId = $_SESSION['id'] ?? null;

// ── insert ────────────────────────────────────────────────────
try {
    $stmt = $pdo->prepare("
        INSERT INTO bookings
            (ticket, user_id, name, email, phone, occasion, guests,
             package, amount, payment_method, booking_datetime, special_notes, status)
        VALUES
            (:ticket, :user_id, :name, :email, :phone, :occasion, :guests,
             :package, :amount, :payment, :datetime, :notes, 'Pending')
    ");

    $stmt->execute([
        ':ticket'   => $ticket,
        ':user_id'  => $userId,
        ':name'     => $name,
        ':email'    => $email,
        ':phone'    => $phone,
        ':occasion' => $occasion,
        ':guests'   => $guests,
        ':package'  => $package,
        ':amount'   => $amount,
        ':payment'  => $payment,
        ':datetime' => $bookingDT,
        ':notes'    => $notes,
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