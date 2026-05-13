<?php
session_start();
include("connect.php");

// Redirect to login if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: http://localhost/webtools-main/HTML/login.html");
    exit();
}

$email = $_SESSION['email'];

// Pre-fill from user profile
$stmt = $conn->prepare("SELECT username, full_name, phone FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$success = false;
$error   = "";

// ── Handle booking submission ──
if (isset($_POST['placeBooking'])) {
    $name          = trim($_POST['name']);
    $phone         = trim($_POST['phone']);
    $occasion      = trim($_POST['occasion']);
    $guests        = (int)$_POST['guests'];
    $event_datetime = trim($_POST['datetime']);
    $package       = trim($_POST['package']);
    $notes         = trim($_POST['notes']);
    $promo_code    = strtoupper(trim($_POST['promo_code']));
    $payment_type  = $_POST['payment_type'] === 'full' ? 'full' : 'downpayment';

    // Package base prices
    $prices = ['basic' => 2000, 'standard' => 5000, 'premium' => 10000];
    $base_amount = $prices[$package] ?? 0;

    if ($base_amount === 0 || empty($name) || empty($occasion) || empty($event_datetime)) {
        $error = "Please fill in all required fields.";
    } else {
        // Validate promo if provided
        $discount_pct = 0;
        $discount_amt = 0;
        if (!empty($promo_code)) {
            $stmt = $conn->prepare("SELECT discount FROM promos WHERE code=? AND is_active=1");
            $stmt->bind_param("s", $promo_code);
            $stmt->execute();
            $promo = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($promo) {
                $discount_pct = (float)$promo['discount'];
                $discount_amt = $base_amount * ($discount_pct / 100);
            } else {
                $error = "Invalid or expired promo code.";
            }
        }

        if (empty($error)) {
            $total_amount = $base_amount - $discount_amt;
            $amount_paid  = $payment_type === 'full' ? $total_amount : round($total_amount * 0.5, 2);

            // Generate unique ticket number
            $ticket = 'CB-' . strtoupper(substr(md5(uniqid()), 0, 6));

            $stmt = $conn->prepare("
                INSERT INTO bookings 
                (ticket, user_email, name, phone, occasion, guests, event_datetime, package, notes,
                 promo_code, discount_pct, base_amount, discount_amt, total_amount,
                 payment_type, amount_paid, payment_status, status, created_at)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'partial','pending', NOW())
            ");
            $payment_status = $payment_type === 'full' ? 'paid' : 'partial';
            $stmt->bind_param(
                "sssssissssdddddsd",
                $ticket, $email, $name, $phone, $occasion, $guests, $event_datetime,
                $package, $notes, $promo_code, $discount_pct,
                $base_amount, $discount_amt, $total_amount,
                $payment_type, $amount_paid
            );

            // Fix: use correct param types
            $stmt->close();

            // Simpler insert
            $ticket_s      = $conn->real_escape_string($ticket);
            $email_s       = $conn->real_escape_string($email);
            $name_s        = $conn->real_escape_string($name);
            $phone_s       = $conn->real_escape_string($phone);
            $occasion_s    = $conn->real_escape_string($occasion);
            $event_dt_s    = $conn->real_escape_string($event_datetime);
            $package_s     = $conn->real_escape_string($package);
            $notes_s       = $conn->real_escape_string($notes);
            $promo_s       = $conn->real_escape_string($promo_code);
            $pay_type_s    = $conn->real_escape_string($payment_type);
            $pay_status_s  = $payment_type === 'full' ? 'paid' : 'partial';

            $sql = "INSERT INTO bookings 
                (ticket, user_email, name, phone, occasion, guests, event_datetime, package, notes,
                 promo_code, discount_pct, base_amount, discount_amt, total_amount,
                 payment_type, amount_paid, payment_status, status, created_at)
                VALUES 
                ('$ticket_s','$email_s','$name_s','$phone_s','$occasion_s',$guests,'$event_dt_s',
                 '$package_s','$notes_s','$promo_s',$discount_pct,$base_amount,$discount_amt,$total_amount,
                 '$pay_type_s',$amount_paid,'$pay_status_s','pending', NOW())";

            if ($conn->query($sql)) {
                $success = true;
                $_SESSION['last_ticket'] = $ticket;
                $_SESSION['last_amount_paid'] = $amount_paid;
                $_SESSION['last_payment_type'] = $payment_type;
            } else {
                $error = "Booking failed. Please try again.";
            }
        }
    }
}

$prefill_name  = $user['full_name'] ?: $user['username'];
$prefill_phone = $user['phone'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book an Event – Cubiertos Food Hub</title>
    <link rel="stylesheet" href="../CSS/book.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ── Payment & Promo additions ── */
        .promo-row { display: flex; gap: 8px; margin-top: 4px; }
        .promo-row input {
            flex: 1;
            text-transform: uppercase;
        }
        .promo-apply {
            padding: 0 18px;
            background: #2d4a2d;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
        }
        .promo-apply:hover { background: #1e3a1e; }
        .promo-msg { font-size: 12px; margin-top: 5px; min-height: 16px; }
        .promo-msg.ok  { color: #15803d; font-weight: 500; }
        .promo-msg.err { color: #b91c1c; }

        .payment-options { display: flex; gap: 12px; margin-top: 8px; }
        .pay-opt {
            flex: 1;
            border: 1.5px solid #ddd;
            border-radius: 10px;
            padding: 14px 10px;
            text-align: center;
            cursor: pointer;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            color: #555;
            transition: all 0.15s;
            user-select: none;
            background: #fff;
        }
        .pay-opt.selected { border-color: #2d4a2d; background: #f0f7f0; color: #2d4a2d; font-weight: 600; }
        .pay-opt .pay-icon { font-size: 24px; display: block; margin-bottom: 6px; }
        .pay-opt .pay-label { font-weight: 600; display: block; }
        .pay-opt .pay-sub { font-size: 11px; color: #999; margin-top: 2px; display: block; }
        .pay-opt.selected .pay-sub { color: #4a7c4a; }

        .summary-box {
            background: #f8f6f2;
            border: 0.5px solid #e0dbd2;
            border-radius: 10px;
            padding: 14px 16px;
            margin: 12px 0;
            font-size: 13px;
        }
        .summary-row { display: flex; justify-content: space-between; padding: 4px 0; color: #555; }
        .summary-row.discount { color: #15803d; font-weight: 500; }
        .summary-row.total { font-weight: 700; font-size: 15px; color: #1a1a1a; border-top: 1px solid #e0dbd2; margin-top: 6px; padding-top: 8px; }
        .summary-row.paying { color: #2d4a2d; font-weight: 600; }

        .alert-error { background: #fee2e2; color: #b91c1c; padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 1rem; }

        /* Success page */
        .success-section {
            min-height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .success-card {
            background: #fff;
            border-radius: 20px;
            padding: 3rem 2rem;
            text-align: center;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 8px 40px rgba(0,0,0,0.1);
        }
        .success-icon { font-size: 60px; display: block; margin-bottom: 12px; }
        .success-card h2 { font-size: 24px; color: #1a1a1a; margin-bottom: 8px; }
        .success-card p  { font-size: 14px; color: #888; line-height: 1.8; }
        .ticket-badge {
            display: inline-block;
            background: #f0f7f0;
            color: #2d4a2d;
            font-size: 18px;
            font-weight: 700;
            padding: 8px 24px;
            border-radius: 30px;
            margin: 16px 0;
            letter-spacing: 2px;
            border: 1.5px dashed #4a7c4a;
        }
        .success-actions { display: flex; gap: 10px; justify-content: center; margin-top: 1.5rem; flex-wrap: wrap; }
        .btn-outline { padding: 10px 22px; border: 1.5px solid #2d4a2d; color: #2d4a2d; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600; font-family: 'Poppins', sans-serif; }
        .btn-filled  { padding: 10px 22px; background: #2d4a2d; color: #fff; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600; font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <img src="../IMAGES/logo.jpg" alt="Cubiertos Food Hub Logo">
    </div>
    <nav>
        <a href="main.html">Home</a>
        <a href="about.html">About</a>
        <a href="Store.html">Our Stores</a>
        <a href="contacts.html">Contacts</a>
        <a href="profile.php" class="login">👤 Account</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="socials">
    <a href="#"><img src="../IMAGES/Facebook.png" alt="Facebook"></a>
    <a href="#"><img src="../IMAGES/Instagram.png" alt="Instagram"></a>
    <a href="#"><img src="../IMAGES/Mail.png" alt="Mail"></a>
</div>

<?php if ($success):
    $ticket      = $_SESSION['last_ticket'] ?? '';
    $amount_paid = $_SESSION['last_amount_paid'] ?? 0;
    $pay_type    = $_SESSION['last_payment_type'] ?? 'downpayment';
?>
<!-- ── SUCCESS STATE ── -->
<div class="success-section">
    <div class="success-card">
        <span class="success-icon">🎉</span>
        <h2>Booking Confirmed!</h2>
        <div class="ticket-badge"><?= htmlspecialchars($ticket) ?></div>
        <p>
            Your event booking is now <strong>pending approval</strong>.<br>
            <?= $pay_type === 'full' ? 'Full payment' : 'Downpayment' ?> of
            <strong>₱<?= number_format($amount_paid, 2) ?></strong> recorded.<br><br>
            We'll contact you at <strong><?= htmlspecialchars($email) ?></strong> once approved.
        </p>
        <div class="success-actions">
            <a href="book.php" class="btn-outline">Book another</a>
            <a href="profile.php" class="btn-filled">View my bookings</a>
        </div>
    </div>
</div>

<?php else: ?>
<!-- ── BOOKING FORM ── -->

<section class="mainpage">
    <div class="mainpage-context">
        <h2>BOOK NOW<span><br>To Experience</span><br> a Memorable Day</h2>
        <p>Every journey has its ups and downs, and Cubiertos is no exception.</p>
        <a href="#booking" class="book-btn">Make an appointment</a>
    </div>
</section>

<section class="story-section">
    <div class="mission-part">
        <div class="mission-text">
            <h2>Our Purpose</h2>
            <p>At Cubiertos, our mission is simple — to bring warmth, comfort, and connection through food made from the heart.</p>
        </div>
        <div class="mission-img">
            <img src="../IMAGES/event.jpg" alt="Cubiertos Mission">
        </div>
    </div>
</section>

<section id="booking" class="booking-section">
    <div class="booking-container">
        <h3>🍴 Book an Event</h3>
        <p class="intro-text">Reserve for birthdays, weddings, or special events at <b>Cubiertos Food Hub</b>.</p>

        <?php if ($error): ?>
            <div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form id="bookingForm" class="booking-form" method="POST" action="book.php">
            <input type="hidden" name="placeBooking" value="1">
            <input type="hidden" name="promo_code"   id="h_promo" value="">
            <input type="hidden" name="payment_type" id="h_payment" value="downpayment">

            <p>
                <i>Full Name</i><br>
                <input type="text" name="name" value="<?= htmlspecialchars($prefill_name) ?>" placeholder="Enter your full name" required>
            </p>

            <p>
                <i>Email Address</i><br>
                <input type="email" value="<?= htmlspecialchars($email) ?>" disabled style="background:#f5f5f5;color:#999;">
            </p>

            <p>
                <i>Phone Number</i><br>
                <input type="tel" name="phone" value="<?= htmlspecialchars($prefill_phone) ?>" placeholder="e.g. 0981 027 0704" required>
            </p>

            <p>
                <i>Type of Occasion</i><br>
                <select name="occasion" id="sel_occasion" required>
                    <option value="">-- Select Occasion --</option>
                    <option value="Dine In">Dine In</option>
                    <option value="Birthday">Birthday</option>
                    <option value="Wedding">Wedding</option>
                    <option value="Corporate Event">Corporate Event</option>
                    <option value="Other">Other</option>
                </select>
            </p>

            <p>
                <i>Number of Guests</i><br>
                <input type="number" name="guests" placeholder="Enter number of guests" min="1" max="500" required>
            </p>

            <p>
                <i>Preferred Date & Time</i><br>
                <input type="datetime-local" name="datetime" required>
            </p>

            <p>
                <i>Package Choice</i><br>
                <select name="package" id="sel_package" onchange="updateSummary()" required>
                    <option value="">-- Choose a Package --</option>
                    <option value="basic">Basic Package (₱2,000 – Small Group)</option>
                    <option value="standard">Standard Package (₱5,000 – Up to 30 Guests)</option>
                    <option value="premium">Premium Package (₱10,000+ – Large Events)</option>
                </select>
            </p>

            <!-- PROMO CODE -->
            <p>
                <i>Promo Code (optional)</i><br>
                <div class="promo-row">
                    <input type="text" id="promoInput" placeholder="e.g. WELCOME10" maxlength="30">
                    <button type="button" class="promo-apply" onclick="applyPromo()">Apply</button>
                </div>
                <div class="promo-msg" id="promoMsg"></div>
            </p>

            <!-- PAYMENT TYPE -->
            <p>
                <i>Payment Option</i><br>
                <div class="payment-options">
                    <div class="pay-opt selected" id="opt-down" onclick="selectPayment('downpayment')">
                        <span class="pay-icon">💵</span>
                        <span class="pay-label">Downpayment</span>
                        <span class="pay-sub">Pay 50% now</span>
                    </div>
                    <div class="pay-opt" id="opt-full" onclick="selectPayment('full')">
                        <span class="pay-icon">✅</span>
                        <span class="pay-label">Full Payment</span>
                        <span class="pay-sub">Pay 100% now</span>
                    </div>
                </div>
            </p>

            <!-- PRICE SUMMARY -->
            <div class="summary-box" id="summaryBox" style="display:none;">
                <div class="summary-row"><span>Package price</span><span id="s_base">₱0.00</span></div>
                <div class="summary-row discount" id="s_discount_row" style="display:none;"><span id="s_discount_label">Promo discount</span><span id="s_discount_amt">-₱0.00</span></div>
                <div class="summary-row total"><span>Total</span><span id="s_total">₱0.00</span></div>
                <div class="summary-row paying"><span id="s_paying_label">You pay now (50%)</span><span id="s_paying_amt">₱0.00</span></div>
            </div>

            <p>
                <i>Special Notes</i><br>
                <textarea name="notes" rows="4" placeholder="Any special requests or details?"></textarea>
            </p>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Confirm Booking</button>
                <button type="reset"  class="btn-reset" onclick="resetPromo()">Clear Form</button>
            </div>

            <!-- CONFIRMATION PREVIEW MODAL -->
            <div id="previewModal" class="preview-modal" aria-hidden="true">
                <div class="preview-modal-content" role="dialog" aria-modal="true">
                    <h3>Confirm your booking</h3>
                    <div id="previewBody"></div>
                    <div class="preview-actions">
                        <button type="button" id="confirmBookingBtn" class="btn confirm">Confirm & Pay</button>
                        <button type="button" id="editBookingBtn"    class="btn edit">Edit</button>
                    </div>
                </div>
            </div>

        </form>

        <div class="booking-info">
            <h4>📞 Need Help?</h4>
            <p>Contact us at <b>0981 027 0704</b><br>
            or visit <em>Imelda Blvd., Rawis, Virac, Catanduanes</em></p>
        </div>
    </div>
</section>

<?php endif; ?>

<footer>
    <div class="footer-info">
        <nav>
            <a href="main.html">Home</a>
            <a href="about.html">About</a>
            <a href="Store.html">Our Stores</a>
            <a href="contacts.html">Contacts</a>
            <a href="login.html">👤 Log-in</a>
        </nav>
        <p>Food & Drink · Virac, Philippines, 4800 | Contact Info: 0981 027 0704</p>
        <p>Copyright © 2025 Cubiertos.food.hub</p>
    </div>
    <div class="footer-socials">
        <a href="https://www.facebook.com/profile.php?id=61555258696901" target="_blank"><img src="../IMAGES/Facebook.png" alt="Facebook"></a>
        <a href="https://www.instagram.com/cubiertos2024/" target="_blank"><img src="../IMAGES/Instagram.png" alt="Instagram"></a>
        <a href="#"><img src="../IMAGES/Mail.png" alt="Mail"></a>
    </div>
</footer>

<script>
// ── Package prices ──
const prices = { basic: 2000, standard: 5000, premium: 10000 };
let discount  = 0;
let promoCode = '';
let payType   = 'downpayment';

function updateSummary() {
    const pkg = document.getElementById('sel_package').value;
    if (!pkg) { document.getElementById('summaryBox').style.display = 'none'; return; }

    const base     = prices[pkg] || 0;
    const discAmt  = base * (discount / 100);
    const total    = base - discAmt;
    const paying   = payType === 'full' ? total : total * 0.5;

    document.getElementById('summaryBox').style.display = 'block';
    document.getElementById('s_base').textContent    = '₱' + base.toLocaleString('en-PH', {minimumFractionDigits:2});
    document.getElementById('s_total').textContent   = '₱' + total.toLocaleString('en-PH', {minimumFractionDigits:2});
    document.getElementById('s_paying_amt').textContent = '₱' + paying.toLocaleString('en-PH', {minimumFractionDigits:2});
    document.getElementById('s_paying_label').textContent = payType === 'full' ? 'You pay now (100%)' : 'You pay now (50%)';

    if (discount > 0) {
        document.getElementById('s_discount_row').style.display = 'flex';
        document.getElementById('s_discount_label').textContent = 'Promo (' + discount + '% off)';
        document.getElementById('s_discount_amt').textContent   = '-₱' + discAmt.toLocaleString('en-PH', {minimumFractionDigits:2});
    } else {
        document.getElementById('s_discount_row').style.display = 'none';
    }
}

function selectPayment(type) {
    payType = type;
    document.getElementById('h_payment').value = type;
    document.getElementById('opt-down').classList.toggle('selected', type === 'downpayment');
    document.getElementById('opt-full').classList.toggle('selected', type === 'full');
    updateSummary();
}

function applyPromo() {
    const code = document.getElementById('promoInput').value.trim();
    const msg  = document.getElementById('promoMsg');
    if (!code) { msg.textContent = 'Please enter a promo code.'; msg.className = 'promo-msg err'; return; }

    fetch('apply_promo.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'code=' + encodeURIComponent(code)
    })
    .then(r => r.json())
    .then(data => {
        msg.textContent  = data.message;
        msg.className    = 'promo-msg ' + (data.success ? 'ok' : 'err');
        if (data.success) {
            discount  = data.discount;
            promoCode = data.code;
            document.getElementById('h_promo').value = promoCode;
        } else {
            discount  = 0;
            promoCode = '';
            document.getElementById('h_promo').value = '';
        }
        updateSummary();
    });
}

function resetPromo() {
    discount = 0; promoCode = '';
    document.getElementById('h_promo').value = '';
    document.getElementById('promoMsg').textContent = '';
    document.getElementById('promoInput').value = '';
    document.getElementById('summaryBox').style.display = 'none';
}

// ── Preview modal ──
const bookingForm  = document.getElementById('bookingForm');
const previewModal = document.getElementById('previewModal');
const previewBody  = document.getElementById('previewBody');

const packageLabels = {
    basic:    'Basic Package (₱2,000)',
    standard: 'Standard Package (₱5,000)',
    premium:  'Premium Package (₱10,000+)'
};

bookingForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const pkg  = document.getElementById('sel_package').value;
    const base = prices[pkg] || 0;
    const disc = base * (discount / 100);
    const total = base - disc;
    const paying = payType === 'full' ? total : total * 0.5;

    previewBody.innerHTML = `
        <table style="width:100%;font-size:13px;border-collapse:collapse;">
            <tr><td style="padding:6px 0;color:#999;width:45%">Name</td><td style="font-weight:500">${bookingForm.querySelector('[name=name]').value}</td></tr>
            <tr><td style="padding:6px 0;color:#999">Occasion</td><td style="font-weight:500">${document.getElementById('sel_occasion').value || '—'}</td></tr>
            <tr><td style="padding:6px 0;color:#999">Guests</td><td style="font-weight:500">${bookingForm.querySelector('[name=guests]').value}</td></tr>
            <tr><td style="padding:6px 0;color:#999">Date & Time</td><td style="font-weight:500">${new Date(bookingForm.querySelector('[name=datetime]').value).toLocaleString()}</td></tr>
            <tr><td style="padding:6px 0;color:#999">Package</td><td style="font-weight:500">${packageLabels[pkg] || '—'}</td></tr>
            ${promoCode ? `<tr><td style="padding:6px 0;color:#999">Promo</td><td style="font-weight:500;color:#15803d">${promoCode} (${discount}% off)</td></tr>` : ''}
            <tr><td style="padding:6px 0;color:#999">Total</td><td style="font-weight:700;font-size:15px">₱${total.toLocaleString('en-PH',{minimumFractionDigits:2})}</td></tr>
            <tr><td style="padding:6px 0;color:#2d4a2d;font-weight:600">You pay now</td><td style="font-weight:700;color:#2d4a2d;font-size:15px">₱${paying.toLocaleString('en-PH',{minimumFractionDigits:2})}</td></tr>
        </table>
    `;

    previewModal.classList.add('show');
    previewModal.setAttribute('aria-hidden', 'false');
});

document.getElementById('editBookingBtn').addEventListener('click', () => {
    previewModal.classList.remove('show');
    previewModal.setAttribute('aria-hidden', 'true');
});

document.getElementById('confirmBookingBtn').addEventListener('click', () => {
    previewModal.classList.remove('show');
    bookingForm.submit();
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        previewModal.classList.remove('show');
        previewModal.setAttribute('aria-hidden', 'true');
    }
});

// Header scroll
window.addEventListener('scroll', () => {
    document.querySelector('header').classList.toggle('scrolled', window.scrollY > 50);
});
</script>

</body>
</html>