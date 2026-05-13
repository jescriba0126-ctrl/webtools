<?php
// admin_bookings.php
// AJAX endpoint — returns bookings as JSON for the admin dashboard
// Also handles status updates

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include("connect.php");

$action = $_GET['action'] ?? 'list';

// ── LIST bookings ─────────────────────────────────────────────
if ($action === 'list') {

    $where  = "1=1";
    $params = [];

    // Filter by status
    $status = $_GET['status'] ?? 'all';
    if ($status !== 'all' && in_array($status, ['Pending','Approved','Completed','Cancelled'])) {
        $where   .= " AND status = :status";
        $params[':status'] = $status;
    }

    // Search by name or email
    $search = trim($_GET['search'] ?? '');
    if ($search !== '') {
        $where   .= " AND (name LIKE :search OR email LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    $stmt = $pdo->prepare("
        SELECT id, ticket, name, email, phone, occasion, guests,
               package, amount, payment_method, booking_datetime,
               special_notes, status, created_at
        FROM bookings
        WHERE $where
        ORDER BY created_at DESC
    ");
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();

    // ── Stats for overview cards ──────────────────────────────
    $stats = $pdo->query("
        SELECT
          COUNT(*)                                        AS total,
          SUM(status = 'Pending')                         AS pending,
          SUM(status = 'Approved')                        AS approved,
          SUM(status = 'Completed')                       AS completed,
          SUM(status = 'Cancelled')                       AS cancelled,
          COALESCE(SUM(CASE WHEN status IN ('Approved','Completed')
                            THEN amount ELSE 0 END), 0)  AS revenue
        FROM bookings
    ")->fetch();

    // ── Today's bookings count (for capacity bar) ─────────────
    $today = $pdo->query("
        SELECT COUNT(*) AS today_count,
               COALESCE(SUM(guests),0) AS today_guests
        FROM bookings
        WHERE DATE(booking_datetime) = CURDATE()
          AND status NOT IN ('Cancelled')
    ")->fetch();

    echo json_encode([
        'success'  => true,
        'bookings' => $bookings,
        'stats'    => $stats,
        'today'    => $today,
    ]);
    exit;
}

// ── UPDATE status ─────────────────────────────────────────────
if ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $id        = (int) ($_POST['id']     ?? 0);
    $newStatus = trim($_POST['status']   ?? '');
    $allowed   = ['Pending','Approved','Completed','Cancelled'];

    if (!$id || !in_array($newStatus, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE bookings SET status = :s WHERE id = :id");
    $stmt->execute([':s' => $newStatus, ':id' => $id]);

    echo json_encode(['success' => true, 'message' => 'Status updated.']);
    exit;
}

// ── DELETE booking ────────────────────────────────────────────
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = (int) ($_POST['id'] ?? 0);

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['success' => true, 'message' => 'Booking deleted.']);
    exit;
}

echo json_encode(['error' => 'Unknown action.']);
?>