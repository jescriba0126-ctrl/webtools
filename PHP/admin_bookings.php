<?php
// admin_bookings.php
// AJAX endpoint — returns bookings as JSON for the admin dashboard.
// Database: login | Table: bookings
// Handles: list, update_status, delete, set_capacity

session_start();
header('Content-Type: application/json');

// Only admins may call this endpoint
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include 'connect.php'; // provides $conn (mysqli) connected to `login` DB

$action = $_GET['action'] ?? 'list';

// ── LIST bookings ─────────────────────────────────────────────
if ($action === 'list') {

    // Build WHERE clause for optional filters
    $conditions = [];
    $types      = '';
    $binds      = [];

    $status = $_GET['status'] ?? 'all';
    if ($status !== 'all' && in_array($status, ['Pending', 'Approved', 'Completed', 'Cancelled'])) {
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

    // Select all columns that match your bookings table schema exactly
    $sql = "SELECT
                id,
                ticket,
                ticket_number,
                user_id,
                name,
                email,
                phone,
                occasion,
                guests,
                package,
                amount,
                payment_method,
                payment_status,
                booking_datetime,
                special_notes,
                gcash_name,
                gcash_number,
                gcash_reference,
                proof_path,
                status,
                created_at,
                updated_at
            FROM bookings
            $where
            ORDER BY booking_datetime ASC";

    $stmt = $conn->prepare($sql);
    if ($types) {
        $stmt->bind_param($types, ...$binds);
    }
    $stmt->execute();
    $result   = $stmt->get_result();
    $bookings = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // ── Overview stats (always from full table, ignoring filters) ──
    $statsRes = $conn->query("
        SELECT
            COUNT(*)                                                AS total,
            SUM(status = 'Pending')                                 AS pending,
            SUM(status = 'Approved')                                AS approved,
            SUM(status = 'Completed')                               AS completed,
            SUM(status = 'Cancelled')                               AS cancelled,
            COALESCE(SUM(CASE WHEN status = 'Completed'
                              THEN amount ELSE 0 END), 0)           AS revenue
        FROM bookings
    ");
    $stats = $statsRes->fetch_assoc();

    // ── Today's bookings for capacity bar ──────────────────────────
    $todayRes = $conn->query("
        SELECT
            COUNT(*) AS today_count,
            COALESCE(SUM(guests), 0) AS today_guests
        FROM bookings
        WHERE DATE(booking_datetime) = CURDATE()
          AND status NOT IN ('Cancelled', 'Completed')
    ");
    $today = $todayRes->fetch_assoc();

    // ── Upcoming events (next 7 days, Pending or Approved) ─────────
    $upcomingRes = $conn->query("
        SELECT
            id, name, occasion, guests, amount, payment_method,
            booking_datetime, status, special_notes, phone, email
        FROM bookings
        WHERE status IN ('Pending', 'Approved')
          AND booking_datetime BETWEEN
                DATE_SUB(NOW(), INTERVAL 3 DAY)
            AND DATE_ADD(NOW(), INTERVAL 7 DAY)
        ORDER BY booking_datetime ASC
    ");
    $upcomingEvents = $upcomingRes->fetch_all(MYSQLI_ASSOC);

    // ── Daily capacity from settings table ─────────────────────────
    $capRes   = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'daily_capacity'");
    $capRow   = $capRes ? $capRes->fetch_assoc() : null;
    $capacity = (int)($capRow['setting_value'] ?? 100);

    echo json_encode([
        'success'        => true,
        'bookings'       => $bookings,
        'upcomingEvents' => $upcomingEvents,
        'stats'          => $stats,
        'today'          => $today,
        'capacity'       => $capacity,
    ]);
    exit;
}

// ── UPDATE STATUS ─────────────────────────────────────────────
if ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $id        = (int)($_POST['id']    ?? 0);
    $newStatus = trim($_POST['status'] ?? '');
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

    // Uses the settings table (setting_key = 'daily_capacity' already exists in your DB)
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