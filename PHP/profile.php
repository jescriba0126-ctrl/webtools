<?php
session_start();
ob_start();
include 'connect.php';

// Redirect to login if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: http://localhost/webtools-main/HTML/login.html");
    exit();
}

$email = $_SESSION['email'];

// ── Handle profile update ──
if (isset($_POST['updateProfile'])) {
    $full_name = trim($_POST['full_name']);
    $phone     = trim($_POST['phone']);
    $address   = trim($_POST['address']);
    $birthday  = !empty($_POST['birthday']) ? $_POST['birthday'] : NULL;

    $stmt = $conn->prepare("UPDATE users SET full_name=?, phone=?, address=?, birthday=? WHERE email=?");
    $stmt->bind_param("sssss", $full_name, $phone, $address, $birthday, $email);
    $stmt->execute();
    $stmt->close();

    header("Location: profile.php?saved=1");
    exit();
}

// ── Handle review submission ──
$review_error   = "";
$review_success = "";

if (isset($_POST['submitReview'])) {
    $rating  = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);

    if ($rating < 1 || $rating > 5) {
        $review_error = "Please select a star rating before submitting.";
    } else {
        $check = $conn->prepare("SELECT id FROM reviews WHERE user_email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE reviews SET rating=?, comment=?, created_at=NOW() WHERE user_email=?");
            $stmt->bind_param("iss", $rating, $comment, $email);
            $stmt->execute();
            $stmt->close();
            $review_success = "Your review has been updated!";
        } else {
            $stmt = $conn->prepare("INSERT INTO reviews (user_email, rating, comment, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sis", $email, $rating, $comment);
            $stmt->execute();
            $stmt->close();
            $review_success = "Thank you for your review!";
        }
        $check->close();
    }
}

// ── Fetch user data (now includes firstName, lastName) ──
$stmt = $conn->prepare("SELECT firstName, lastName, email, full_name, phone, address, birthday FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// ── Auto-fill full_name from firstName + lastName if not set yet ──
if (empty($user['full_name']) && !empty($user['firstName'])) {
    $user['full_name'] = trim($user['firstName'] . ' ' . $user['lastName']);
}

// ── Fetch bookings ──
$bookings = [];
$tableCheck = $conn->query("SHOW TABLES LIKE 'bookings'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    $stmt = $conn->prepare("SELECT ticket, occasion, package, event_datetime, total_amount, payment_type, amount_paid, status FROM bookings WHERE user_email=? ORDER BY created_at DESC");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// ── Fetch existing review ──
$stmt = $conn->prepare("SELECT rating, comment FROM reviews WHERE user_email=? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$existing_review = $stmt->get_result()->fetch_assoc();
$stmt->close();

// ── Format birthday ──
$bday_display = "";
$bday_value   = "";
if (!empty($user['birthday'])) {
    $bday_value   = $user['birthday'];
    $bday_display = date("F j, Y", strtotime($user['birthday']));
}
$is_birthday = ($bday_value === date("Y-m-d"));

// ── Helpers ──
function statusBadge($status) {
    $map = [
        'pending'   => ['Pending',   '#b45309', '#fef3c7'],
        'approved'  => ['Approved',  '#1d6fa4', '#e0f0fb'],
        'completed' => ['Completed', '#15803d', '#dcfce7'],
        'cancelled' => ['Cancelled', '#b91c1c', '#fee2e2'],
    ];
    $s = strtolower($status);
    [$label, $color, $bg] = $map[$s] ?? [ucfirst($status), '#555', '#f3f4f6'];
    return "<span style=\"background:$bg;color:$color;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;\">$label</span>";
}
function val($v)      { return !empty($v) ? htmlspecialchars($v) : ''; }
function ph($v, $lbl) { return empty($v)  ? "<span class=\"empty\">$lbl</span>" : htmlspecialchars($v); }

// ── Display name (firstName for hero, full name for profile) ──
$display_first = htmlspecialchars($user['firstName'] ?? 'User');
$default_fullname = val($user['full_name'] ?: trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? '')));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile – Cubiertos Food Hub</title>
    <link rel="stylesheet" href="../CSS/profile.css">
</head>
<body>
<div class="page">

    <!-- HERO -->
    <div class="hero">
        <nav class="nav">
            <div class="logo">
                <div class="logo-box">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>
                    </svg>
                </div>
                <div>
                    <span class="logo-name">Cubiertos</span>
                    <span class="logo-sub">FOOD HUB</span>
                </div>
            </div>
            <div class="nav-links">
                <a href="homepage.php">Home</a>
                <a href="about.php">About</a>
                <a href="stores.php">Our Stores</a>
                <a href="contacts.php">Contacts</a>
                <a href="logout.php" class="nav-logout">Logout</a>
            </div>
        </nav>

        <div class="hero-bottom">
            <div class="hero-left">
                <div class="avatar">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.8)" stroke-width="1.5">
                        <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                    </svg>
                </div>
                <div>
                    <!-- firstName shown as display name next to avatar -->
                    <div class="hero-name"><?= $display_first ?></div>
                    <span class="hero-badge">🌱 New Customer</span>
                    <?php if ($is_birthday): ?>
                        <span class="hero-badge" style="margin-left:6px;">🎂 Happy Birthday!</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="welcome-box">
                <div class="welcome-title">Welcome to Cubiertos!</div>
                <div class="welcome-sub">Your favourite food, delivered fresh.</div>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['saved'])): ?>
        <div class="toast" id="toast">✅ Profile saved successfully!</div>
    <?php endif; ?>

    <!-- CONTENT -->
    <div class="content">

        <!-- LEFT COL -->
        <div class="left-col">

            <!-- PERSONAL INFO -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                        Personal information
                    </div>
                    <button class="edit-btn" id="editBtn" type="button" onclick="toggleEdit()">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit
                    </button>
                </div>
                <form method="POST" action="profile.php" id="profileForm">
                    <input type="hidden" name="updateProfile" value="1">

                    <!-- FULL NAME (auto-filled from firstName + lastName) -->
                    <div class="field">
                        <div class="field-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg></div>
                        <div class="field-body">
                            <div class="field-label">Full name</div>
                            <div class="field-value" id="v-name"><?= ph($user['full_name'] ?: trim(($user['firstName'] ?? '').' '.($user['lastName'] ?? '')), 'Not set') ?></div>
                            <input class="field-input" id="i-name" name="full_name" value="<?= $default_fullname ?>" placeholder="e.g. Juan dela Cruz" />
                        </div>
                    </div>

                    <!-- EMAIL (read-only) -->
                    <div class="field">
                        <div class="field-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
                        <div class="field-body">
                            <div class="field-label">Email</div>
                            <div class="field-value"><?= htmlspecialchars($user['email']) ?></div>
                        </div>
                    </div>

                    <!-- PHONE -->
                    <div class="field">
                        <div class="field-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.69 3.22 2 2 0 0 1 3.68 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.65a16 16 0 0 0 6 6l1.02-1.02a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div>
                        <div class="field-body">
                            <div class="field-label">Phone number</div>
                            <div class="field-value" id="v-phone"><?= ph($user['phone'], 'Not set') ?></div>
                            <input class="field-input" id="i-phone" name="phone" value="<?= val($user['phone']) ?>" placeholder="+63 912 345 6789" />
                        </div>
                    </div>

                    <!-- ADDRESS -->
                    <div class="field">
                        <div class="field-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
                        <div class="field-body">
                            <div class="field-label">Delivery address</div>
                            <div class="field-value" id="v-addr"><?= ph($user['address'], 'Not set') ?></div>
                            <input class="field-input" id="i-addr" name="address" value="<?= val($user['address']) ?>" placeholder="Street, City, Province" />
                        </div>
                    </div>

                    <!-- BIRTHDAY -->
                    <div class="field">
                        <div class="field-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                        <div class="field-body">
                            <div class="field-label">Birthday</div>
                            <div class="field-value" id="v-bday"><?= !empty($bday_display) ? $bday_display : '<span class="empty">Not set</span>' ?></div>
                            <input class="field-input" id="i-bday" name="birthday" type="date" value="<?= val($bday_value) ?>" />
                            <?php if (!empty($bday_value)): ?>
                                <div class="promo-tag">🎁 Birthday promo unlocked!</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-actions" id="formActions" style="display:none;">
                        <button type="button" class="cancel-btn" onclick="cancelEdit()">Cancel</button>
                        <button type="submit" class="save-btn">Save changes</button>
                    </div>
                </form>
            </div>

            <!-- REVIEW -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <?= $existing_review ? 'Update your review' : 'Leave a review' ?>
                    </div>
                </div>

                <?php if ($review_success): ?>
                    <div class="alert-success">✅ <?= htmlspecialchars($review_success) ?></div>
                <?php endif; ?>
                <?php if ($review_error): ?>
                    <div class="alert-error">⚠️ <?= htmlspecialchars($review_error) ?></div>
                <?php endif; ?>

                <form method="POST" action="profile.php">
                    <input type="hidden" name="submitReview" value="1">
                    <div class="stars" id="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?= ($existing_review && $existing_review['rating'] >= $i) ? 'on' : '' ?>" data-v="<?= $i ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating" id="ratingInput" value="<?= $existing_review ? (int)$existing_review['rating'] : 0 ?>">
                    <div class="hint" id="hint">
                        <?php
                            $rl = ['','Poor','Fair','Good','Great','Excellent'];
                            echo $existing_review ? $existing_review['rating'].' / 5 — '.$rl[$existing_review['rating']] : 'Select a rating';
                        ?>
                    </div>
                    <textarea name="comment" placeholder="What did you think of Cubiertos? (optional)"><?= $existing_review ? htmlspecialchars($existing_review['comment']) : '' ?></textarea>
                    <button type="submit" class="sub-btn"><?= $existing_review ? 'Update review' : 'Submit review' ?></button>
                </form>
            </div>

        </div>

        <!-- RIGHT COL -->
        <div class="right-col">

            <!-- BOOKING HISTORY -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Booking History
                    </div>
                    <?php if (count($bookings) > 0): ?>
                        <span class="order-count"><?= count($bookings) ?> booking<?= count($bookings) > 1 ? 's' : '' ?></span>
                    <?php endif; ?>
                </div>

                <?php if (empty($bookings)): ?>
                    <div class="order-empty">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:block;margin:0 auto 10px;color:#ccc;"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                        <p>No bookings yet.<br>Book an event with us!</p>
                        <a href="book.php" class="browse-btn">Book now</a>
                    </div>
                <?php else: ?>
                    <div class="order-list">
                        <?php foreach ($bookings as $b): ?>
                            <div class="order-row">
                                <div class="order-info">
                                    <div class="order-name">
                                        <?= htmlspecialchars($b['occasion']) ?>
                                        <span style="font-size:11px;color:#aaa;font-weight:400;margin-left:6px;">#<?= htmlspecialchars($b['ticket']) ?></span>
                                    </div>
                                    <div class="order-meta">
                                        <?= ucfirst($b['package']) ?> &nbsp;·&nbsp;
                                        ₱<?= number_format($b['total_amount'], 2) ?> &nbsp;·&nbsp;
                                        <?= date("M j, Y", strtotime($b['event_datetime'])) ?>
                                    </div>
                                    <div class="order-meta" style="margin-top:2px;">
                                        Paid: ₱<?= number_format($b['amount_paid'], 2) ?>
                                        (<?= $b['payment_type'] === 'full' ? 'Full payment' : 'Downpayment' ?>)
                                    </div>
                                </div>
                                <div><?= statusBadge($b['status']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script>
// ── Inline edit ──
const viewEls  = ['v-name','v-phone','v-addr','v-bday'];
const inputEls = ['i-name','i-phone','i-addr','i-bday'];

function toggleEdit() {
    viewEls.forEach(id  => document.getElementById(id).style.display = 'none');
    inputEls.forEach(id => document.getElementById(id).style.display = 'block');
    document.getElementById('editBtn').style.display     = 'none';
    document.getElementById('formActions').style.display = 'flex';
}
function cancelEdit() {
    viewEls.forEach(id  => document.getElementById(id).style.display = 'block');
    inputEls.forEach(id => document.getElementById(id).style.display = 'none');
    document.getElementById('editBtn').style.display     = 'inline-flex';
    document.getElementById('formActions').style.display = 'none';
}

// ── Star rating ──
const rLabels = ['','Poor','Fair','Good','Great','Excellent'];
let rating = parseInt(document.getElementById('ratingInput').value) || 0;
const stars = document.querySelectorAll('#stars .star');
stars.forEach(s => {
    s.addEventListener('click', () => {
        rating = +s.dataset.v;
        document.getElementById('ratingInput').value = rating;
        updateStars(rating);
    });
    s.addEventListener('mouseover', () => updateStars(+s.dataset.v));
    s.addEventListener('mouseout',  () => updateStars(rating));
});
function updateStars(n) {
    stars.forEach(s => s.classList.toggle('on', +s.dataset.v <= n));
    document.getElementById('hint').textContent = n ? n+' / 5 — '+rLabels[n] : 'Select a rating';
}

// ── Auto-hide toast ──
const toast = document.getElementById('toast');
if (toast) setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.5s'; }, 3000);
</script>

</body>
</html>