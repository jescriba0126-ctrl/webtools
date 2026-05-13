<?php
// apply_promo.php — called via AJAX from checkout.php
session_start();
include 'connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit();
}

$code = strtoupper(trim($_POST['code'] ?? ''));

if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a promo code.']);
    exit();
}

$stmt = $conn->prepare("SELECT code, description, discount FROM promos WHERE code=? AND is_active=1");
$stmt->bind_param("s", $code);
$stmt->execute();
$promo = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($promo) {
    echo json_encode([
        'success'     => true,
        'code'        => $promo['code'],
        'description' => $promo['description'],
        'discount'    => (float)$promo['discount'],
        'message'     => '🎉 Promo applied! ' . $promo['discount'] . '% off'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired promo code.']);
}
?>