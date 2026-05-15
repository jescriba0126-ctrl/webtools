<?php
// admin_bookings.php
// AJAX endpoint — returns bookings as JSON for the admin dashboard.
// Handles: list, update_status, delete, set_capacity.
// Uses mysqli ($conn) to match connect.php in this project.

session_start();
header('Content-Type: application/json');

// Only admins may call this endpoint
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include 'connect.php'; // provides $conn (mysqli)

$action = $_GET['action'] ?? 'list';

// ── LIST bookings ─────────────────────────────────────────────
if ($action === 'list') {

    // Build WHERE clause
    $conditions = [];
    $types      = '';
    $binds      = [];

    $status = $_GET['status'] ?? 'all';
    if ($status !== 'all' && in_array($status, ['Pending','Approved','Completed','Cancelled'])) {
        $conditions[] = "status = ?";
        $types       .= 's';
        $binds[]      = $status;
    }

    $search = trim($_GET['search'] ?? '');
    if ($search !== '') {
        $conditions[] = "(name LIKE ? OR email LIKE ?)";
        $types       .= 'ss';
        $like         = '%' . $search . '%';
        $binds[]      = $like;
        $binds[]      = $like;
    }

    $where = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $sql  = "SELECT id, ticket, name, email, phone, occasion, guests,
                    package, amount, payment_method, booking_datetime,
                    special_notes, status, created_at
             FROM bookings $where
             ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    if ($types) {
        $stmt->bind_param($types, ...$binds);
    }
    $stmt->execute();
    $result   = $stmt->get_result();
    $bookings = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Overview stats
    $statsRes = $conn->query("
        SELECT
          COUNT(*)                                                         AS total,
          SUM(status = 'Pending')                                          AS pending,
          SUM(status = 'Approved')                                         AS approved,
          SUM(status = 'Completed')                                        AS completed,
          SUM(status = 'Cancelled')                                        AS cancelled,
          COALESCE(SUM(CASE WHEN status IN ('Approved','Completed')
                            THEN amount ELSE 0 END), 0)                   AS revenue
        FROM bookings
    ");
    $stats = $statsRes->fetch_assoc();

    // Today's bookings for capacity bar
    $todayRes = $conn->query("
        SELECT COUNT(*) AS today_count,
               COALESCE(SUM(guests), 0) AS today_guests
        FROM bookings
        WHERE DATE(booking_datetime) = CURDATE()
          AND status != 'Cancelled'
    ");
    $today = $todayRes->fetch_assoc();

    // Daily capacity from settings
    $capRes   = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'daily_capacity'");
    $capRow   = $capRes ? $capRes->fetch_assoc() : null;
    $capacity = (int)($capRow['setting_value'] ?? 100);

    echo json_encode([
        'success'  => true,
        'bookings' => $bookings,
        'stats'    => $stats,
        'today'    => $today,
        'capacity' => $capacity,
    ]);
    exit;
}

// ── UPDATE STATUS ─────────────────────────────────────────────
// Called from the admin dashboard when the admin changes a booking status.
// Because profile.php reads directly from the bookings table, the user's
// profile page will reflect the new status on their next page load.
if ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $id        = (int)($_POST['id']     ?? 0);
    $newStatus = trim($_POST['status']  ?? '');
    $allowed   = ['Pending', 'Approved', 'Completed', 'Cancelled'];

    if (!$id || !in_array($newStatus, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        echo json_encode(['success' => true, 'message' => "Status updated to $newStatus."]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Booking not found or status unchanged.']);
    }
    exit;
}

// ── DELETE booking ────────────────────────────────────────────
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = (int)($_POST['id'] ?? 0);

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    echo json_encode([
        'success' => $affected > 0,
        'message' => $affected > 0 ? 'Booking deleted.' : 'Booking not found.',
    ]);
    exit;
}

// ── SET CAPACITY ──────────────────────────────────────────────
if ($action === 'set_capacity' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $limit = (int)($_POST['limit'] ?? 0);

    if ($limit < 1) {
        echo json_encode(['success' => false, 'message' => 'Limit must be at least 1.']);
        exit;
    }

    // INSERT … ON DUPLICATE KEY UPDATE (requires unique key on setting_key)
    $stmt = $conn->prepare("
        INSERT INTO settings (setting_key, setting_value)
        VALUES ('daily_capacity', ?)
        ON DUPLICATE KEY UPDATE setting_value = ?
    ");
    $stmt->bind_param("ss", $limit, $limit);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true, 'message' => 'Capacity updated.']);
    exit;
}

echo json_encode(['error' => 'Unknown action.']);