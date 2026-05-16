<?php
// booking_submit.php
// Called by the booking form via fetch (AJAX POST)
// Returns JSON so the frontend can show success/error without a page reload

session_start();
header('Content-Type: application/json');

include("connect.php");

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

// ── Slot map ─────────────────────────────────────────────────
$slotMap = [
    'morning'   => '08:00:00',
    'afternoon' => '14:00:00',
    'evening'   => '19:00:00',
];

$slotLabels = [
    'morning'   => 'Morning (8:00 AM)',
    'afternoon' => 'Afternoon (2:00 PM)',
    'evening'   => 'Evening (7:00 PM)',
];

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
$date     = clean($_POST['date']     ?? '');   // "2026-05-28"
$slot     = clean($_POST['slot']     ?? '');   // "morning" | "afternoon" | "evening"
$notes    = clean($_POST['message']  ?? '');

$allowed_occasions = ['dine-in','birthday','wedding','corporate','other'];
$allowed_payments  = ['GCash','Maya','Online Banking','Credit / Debit Card','Cash'];

if (!$name || !$email || !$phone || !$occasion || !$guests || !$payment || !$date || !$slot) {
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

if (!array_key_exists($slot, $slotMap)) {
    echo json_encode(['success' => false, 'message' => 'Invalid slot selected.']);
    exit;
}

// ── Build booking datetime from date + slot ───────────────────
$bookingDT = $date . ' ' . $slotMap[$slot];

if (!strtotime($bookingDT)) {
    echo json_encode(['success' => false, 'message' => 'Invalid date.']);
    exit;
}

// ── Block past slots ──────────────────────────────────────────
if (strtotime($bookingDT) <= time()) {
    echo json_encode(['success' => false, 'message' => 'That slot has already passed. Please choose a future date.']);
    exit;
}

// ── GCash fields ─────────────────────────────────────────────
$gcash_name      = clean($_POST['gcash_name']      ?? '');
$gcash_number    = clean($_POST['gcash_number']    ?? '');
$gcash_reference = clean($_POST['gcash_reference'] ?? '');

if ($payment === 'GCash') {
    if (!$gcash_name || !$gcash_number || !$gcash_reference) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all GCash details.']);
        exit;
    }
    if (!preg_match('/^09\d{9}$/', $gcash_number)) {
        echo json_encode(['success' => false, 'message' => 'Invalid GCash number. Must be 11 digits starting with 09.']);
        exit;
    }
}

// ── Generate unique ticket ────────────────────────────────────
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

    $upload_dir = __DIR__ . '/uploads/proofs/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    $ext       = strtolower(pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION));
    $filename  = 'proof_' . $ticket . '_' . time() . '.' . $ext;
    $full_path = $upload_dir . $filename;

    if (!move_uploaded_file($_FILES['proof']['tmp_name'], $full_path)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save proof image. Please try again.']);
        exit;
    }

    $proof_path = '/webtools-main/PHP/uploads/proofs/' . $filename;
}

// ── Check if slot is already taken ───────────────────────────
$slotTime = $slotMap[$slot];
$slotStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM bookings
    WHERE DATE(booking_datetime) = ?
      AND TIME(booking_datetime) = ?
      AND status != 'Cancelled'
");
$slotStmt->execute([$date, $slotTime]);
if ((int) $slotStmt->fetchColumn() > 0) {
    echo json_encode([
        'success' => false,
        'message' => "Sorry, the {$slotLabels[$slot]} slot on " . date('F j, Y', strtotime($date)) . " is already taken. Please choose another slot or date."
    ]);
    exit;
}

// ── Daily capacity check ──────────────────────────────────────
$limitStmt   = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'daily_capacity'");
$DAILY_LIMIT = (int)($limitStmt->fetchColumn() ?: 100);

$capStmt = $pdo->prepare("
    SELECT COALESCE(SUM(guests), 0)
    FROM bookings
    WHERE DATE(booking_datetime) = ?
      AND status != 'Cancelled'
");
$capStmt->execute([$date]);
$bookedGuests = (int) $capStmt->fetchColumn();

if (($bookedGuests + $guests) > $DAILY_LIMIT) {
    $remaining = max(0, $DAILY_LIMIT - $bookedGuests);
    echo json_encode([
        'success' => false,
        'message' => "Sorry, we're fully booked for that date. Only {$remaining} guest slot(s) remaining."
    ]);
    exit;
}

// ── Get amount from package ───────────────────────────────────
$amount = getAmount($package);
$userId = $_SESSION['id'] ?? null;

// ── Insert booking ────────────────────────────────────────────
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
        'slot'    => $slotLabels[$slot],
        'date'    => date('F j, Y', strtotime($date)),
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>