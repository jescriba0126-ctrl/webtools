<?php
session_start();
include("connect.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cubiertos Food Hub Book</title>
  <link rel="stylesheet" href="../CSS/book.css">
  <link rel="icon" type="image/jpg" href="/IMAGES/logo.jpg">
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

  <header class="glass-header">
    <div class="nav-container">
      <a href="../HTML/main.html" class="logo">
        <img src="../IMAGES/logo2.jpg" alt="Cubiertos Logo">
        <div class="logo-text">
          <h2>Cubiertos</h2>
          <span>FOOD HUB</span>
        </div>
      </a>
      <nav class="navbar">
        <a href="../HTML/main.html">Home</a>
        <a href="../HTML/about.html">About</a>
        <a href="../HTML/Store.html">Stores</a>
        <a href="../HTML/contacts.html">Contacts</a>
      </nav>
      <a href="profile.php" class="login-btn"><i>👤</i> Profile</a>
      <div class="menu-toggle" id="menuToggle">☰</div>
    </div>
  </header>

  <div class="socials">
    <a href="#"><img src="../IMAGES/Facebook.png" alt="Facebook"></a>
    <a href="#"><img src="../IMAGES/Instagram.png" alt="Instagram"></a>
    <a href="#"><img src="../IMAGES/Mail.png" alt="YouTube"></a>
  </div>

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
        <p>At Cubiertos, our mission is simple — to bring warmth, comfort, and connection through food made from the heart. Every meal we serve celebrates home and togetherness, proving that good food isn't just about taste — it's about belonging.</p>
      </div>
      <div class="mission-img">
        <img src="../IMAGES/event.jpg" alt="Cubiertos Mission">
      </div>
    </div>
  </section>

  <section class="testimonial-section">
    <div class="testimonial-header">
      <h2>What Our Guests Say</h2>
      <p>Real experiences shared by customers who celebrated with Cubiertos Food Hub.</p>
    </div>
    <div class="testimonial-grid">
      <div class="testimonial-card light-card">
        <div class="quote-icon">❝</div>
        <div class="testimonial-top">
          <img src="../IMAGES/customer1.jpg" alt="Customer">
          <div><h3>lance_vs_zombies@017</h3><span>Birthday Celebration</span></div>
        </div>
        <p>"The ambiance was warm and elegant. The food was amazing and the staff were very accommodating throughout our celebration."</p>
        <div class="stars">★★★★★</div>
      </div>
      <div class="testimonial-card featured-card">
        <div class="quote-icon">❝</div>
        <div class="testimonial-top">
          <img src="../IMAGES/customer2.jpg" alt="Customer">
          <div><h3>pplyant_</h3><span>Wedding Reception</span></div>
        </div>
        <p>"Cubiertos made our wedding unforgettable. Everything from the setup to the service exceeded our expectations."</p>
        <div class="stars">★★★★★</div>
      </div>
      <div class="testimonial-card light-card">
        <div class="quote-icon">❝</div>
        <div class="testimonial-top">
          <img src="../IMAGES/customer3.jpg" alt="Customer">
          <div><h3>spro.usv</h3><span>Family Gathering</span></div>
        </div>
        <p>"One of the best dining experiences in Catanduanes. Cozy atmosphere, delicious food, and excellent customer service."</p>
        <div class="stars">★★★★★</div>
      </div>
      <div class="testimonial-card featured-card">
        <div class="quote-icon">❝</div>
        <div class="testimonial-top">
          <img src="../IMAGES/customer4.jpg" alt="Customer">
          <div><h3>Ches_ru@12</h3><span>Wedding Reception</span></div>
        </div>
        <p>"We celebrated our Wedding at Cubiertos and everything was perfect. The food was flavorful, the place felt cozy, and the staff treated us so well."</p>
        <div class="stars">★★★★★</div>
      </div>
    </div>
  </section>

  <section class="booking-stats">
    <div class="stat-card"><h3>500+</h3><p>Successful Events</p></div>
    <div class="stat-card"><h3>4.9★</h3><p>Customer Satisfaction</p></div>
    <div class="stat-card"><h3>24/7</h3><p>Reservation Support</p></div>
    <div class="stat-card"><h3>100%</h3><p>Freshly Prepared Meals</p></div>
  </section>

  <!-- ================= BOOKING FORM ================= -->
  <section id="booking" class="booking-section">
    <div class="booking-container">

      <h3>Book an Appointment</h3>
      <p class="intro-text">
        Reserve your table for birthdays, weddings, or special events at <b>Cubiertos Food Hub</b>.
        <br><small style="color:#bc6c25; font-weight:600; display:block; margin-top:6px;">
          ⏰ Available slots: Morning (8:00 AM) · Afternoon (2:00 PM) · Evening (7:00 PM)
        </small>
      </p>

      <div id="formMessage" style="display:none; padding:14px 18px; border-radius:8px; margin-bottom:18px; font-size:15px; font-weight:500;"></div>

      <form id="bookingForm" class="booking-form" enctype="multipart/form-data">

        <!-- FULL NAME -->
        <p><i>Full Name</i><br>
          <?php
          $prefill_name = $prefill_email = $prefill_phone = '';
          if (isset($_SESSION['id'])) {
              try {
                  $s = $pdo->prepare("SELECT full_name, email, phone FROM users WHERE Id = ?");
                  $s->execute([$_SESSION['id']]);
                  $u = $s->fetch();
                  if ($u) {
                      $prefill_name  = htmlspecialchars($u['full_name'] ?? '');
                      $prefill_email = htmlspecialchars($u['email']     ?? '');
                      $prefill_phone = htmlspecialchars($u['phone']     ?? '');
                  }
              } catch (Exception $e) {}
          }
          ?>
          <input type="text" name="name" placeholder="Enter your full name" value="<?= $prefill_name ?>" required>
        </p>

        <!-- EMAIL -->
        <p><i>Email Address</i><br>
          <input type="email" name="email" placeholder="Enter your email" required>
        </p>

        <!-- PHONE -->
        <p><i>Phone Number</i><br>
          <input type="tel" name="phone" placeholder="e.g. 0981 027 0704" value="<?= $prefill_phone ?>" required>
        </p>

        <!-- OCCASION -->
        <p><i>Type of Occasion</i><br>
          <select name="occasion" required>
            <option value="">-- Select Occasion --</option>
            <option value="dine-in">Dine In</option>
            <option value="birthday">Birthday</option>
            <option value="wedding">Wedding</option>
            <option value="corporate">Corporate Event</option>
            <option value="other">Other</option>
          </select>
        </p>

        <!-- GUESTS -->
        <p><i>Number of Guests</i><br>
          <input type="number" name="guests" id="guestsInput" placeholder="Enter number of guests" min="1" max="500" required>
          <span id="guestWarning" style="display:none;">
            ⚠️ Your guest count exceeds the remaining capacity for this date.
          </span>
        </p>

        <!-- ================= AVAILABILITY CALENDAR ================= -->
        <div class="avail-calendar-wrap">
          <div class="avail-cal-header">
            <button type="button" class="avail-cal-nav" id="calPrev">&#8249;</button>
            <h4 id="calMonthLabel">Loading...</h4>
            <button type="button" class="avail-cal-nav" id="calNext">&#8250;</button>
          </div>
          <div class="avail-day-labels">
            <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
          </div>
          <div class="avail-grid" id="availGrid">
            <!-- Rendered by JS -->
          </div>
          <div class="avail-legend">
            <span><i class="legend-dot open"></i> Available</span>
            <span><i class="legend-dot partial"></i> Partially Booked</span>
            <span><i class="legend-dot full"></i> Fully Booked</span>
            <span><i class="legend-dot past"></i> Past</span>
          </div>
        </div>

        <!-- Hidden date input -->
        <input type="hidden" name="date" id="dateInput">
        <p id="selectedDateLabel" style="font-size:0.9rem; color:#bc6c25; font-weight:600; margin-bottom:10px; display:none;">
          📅 Selected: <span id="selectedDateText"></span>
        </p>

        <!-- SLOT SELECTION -->
        <div class="slot-section">
          <i>Preferred Time Slot</i>
          <div class="slot-grid" id="slotGrid">
            <label class="slot-card" id="slotMorning">
              <input type="radio" name="slot" value="morning" required>
              <div class="slot-icon">🌅</div>
              <div class="slot-label">Morning</div>
              <div class="slot-time">8:00 AM</div>
              <div class="slot-status" id="statusMorning">Select a date first</div>
            </label>
            <label class="slot-card" id="slotAfternoon">
              <input type="radio" name="slot" value="afternoon">
              <div class="slot-icon">☀️</div>
              <div class="slot-label">Afternoon</div>
              <div class="slot-time">2:00 PM</div>
              <div class="slot-status" id="statusAfternoon">Select a date first</div>
            </label>
            <label class="slot-card" id="slotEvening">
              <input type="radio" name="slot" value="evening">
              <div class="slot-icon">🌙</div>
              <div class="slot-label">Evening</div>
              <div class="slot-time">7:00 PM</div>
              <div class="slot-status" id="statusEvening">Select a date first</div>
            </label>
          </div>
        </div>

        <!-- PACKAGE -->
        <p><i>Package Choice</i><br>
          <select name="package">
            <option value="">-- Choose a Package --</option>
            <option value="basic">Basic Package (₱2,000 - Small Group)</option>
            <option value="standard">Standard Package (₱5,000 - 10 Guests)</option>
            <option value="premium">Premium Package (₱10,000+ - Events)</option>
          </select>
        </p>

        <!-- PAYMENT -->
        <div class="payment-wrapper">
          <div class="section-top">
            <h4>Choose Payment Method</h4>
            <p>Select your preferred payment option for reservation confirmation.</p>
          </div>
          <div class="payment-methods modern-payment">
            <label class="payment-option">
              <input type="radio" name="payment" value="GCash" required>
              <span class="payment-card">
                <div class="payment-left">
                  <div class="payment-icon">📱</div>
                  <div class="payment-info">
                    <h4>GCash</h4>
                    <small>Secure mobile wallet payment for fast reservation confirmation.</small>
                  </div>
                </div>
                <div class="payment-check">✓</div>
              </span>
            </label>
            <label class="payment-option">
              <input type="radio" name="payment" value="Cash">
              <span class="payment-card">
                <div class="payment-left">
                  <div class="payment-icon">💵</div>
                  <div class="payment-info">
                    <h4>Cash Payment</h4>
                    <small>Pay directly at Cubiertos Food Hub during your reservation.</small>
                  </div>
                </div>
                <div class="payment-check">✓</div>
              </span>
            </label>
          </div>

          <!-- GCASH DETAILS -->
          <div class="gcash-box" id="gcashBox" style="display:none;">
            <div class="gcash-content">
              <div class="gcash-layout">
                <div class="gcash-info-side">
                  <div class="gcash-header">
                    <div class="gcash-icon">📲</div>
                    <div>
                      <h4>GCash Reservation Details</h4>
                      <p>Send your reservation payment to:</p>
                    </div>
                  </div>
                  <div class="gcash-details">
                    <div class="detail-item"><span>Account Name</span><strong>Cubiertos Food Hub</strong></div>
                    <div class="detail-item"><span>GCash Number</span><strong>0981 027 0704</strong></div>
                  </div>
                  <div class="gcash-sender-info">
                    <h5>Your GCash Information</h5>
                    <div class="gcash-field">
                      <label for="gcashName">GCash Account Name <span class="required">*</span></label>
                      <input type="text" id="gcashName" name="gcash_name" placeholder="e.g. Juan Dela Cruz" autocomplete="off">
                      <small class="field-hint">Name registered on your GCash account</small>
                    </div>
                    <div class="gcash-field">
                      <label for="gcashNumber">GCash Number <span class="required">*</span></label>
                      <input type="tel" id="gcashNumber" name="gcash_number" placeholder="e.g. 0917 123 4567" maxlength="11" autocomplete="off">
                      <small class="field-hint">11-digit mobile number linked to GCash</small>
                    </div>
                    <div class="gcash-field">
                      <label for="gcashRef">Reference Number <span class="required">*</span></label>
                      <input type="text" id="gcashRef" name="gcash_reference" placeholder="e.g. 1234567890" maxlength="20" autocomplete="off">
                      <small class="field-hint">Found in your GCash transaction history</small>
                    </div>
                  </div>
                  <div class="upload-proof">
                    <label>Upload Payment Screenshot <span class="required">*</span></label>
                    <input type="file" name="proof" accept="image/*" id="proofUpload">
                    <small class="field-hint">Attach a screenshot of your successful GCash transaction</small>
                  </div>
                </div>
                <div class="gcash-qr-side">
                  <div class="qr-card">
                    <h4>Scan QR Code</h4>
                    <img src="../IMAGES/gcash.jpg" alt="GCash QR Code">
                    <p>Scan using your GCash app for faster payment processing.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <p><i>Special Notes</i><br>
            <textarea name="message" rows="4" placeholder="Any special requests or details?"></textarea>
          </p>

          <div class="form-actions">
            <button type="submit" class="btn-submit" id="submitBtn">Submit Booking</button>
            <button type="reset" class="btn-reset">Clear Form</button>
          </div>
        </div>
      </form>

      <div class="booking-info">
        <h4>Need Help?</h4>
        <p>Contact us at <b>0981 027 0704</b><br>
        or visit <em>Imelda Blvd., Rawis, Virac, Catanduanes</em></p>
      </div>
    </div>
  </section>

  <footer>
    <div class="footer-top">
      <div class="footer-brand">
        <img src="../IMAGES/logo.jpg" alt="Cubiertos Food Hub" />
        <p class="footer-tagline">"Savor the flavors where every bite tells a story."</p>
      </div>
      <div class="footer-links">
        <a href="../HTML/main.html">Home</a>
        <a href="../HTML/about.html">About</a>
        <a href="../HTML/Store.html">Our Stores</a>
        <a href="../HTML/contacts.html">Contacts</a>
        <a href="../HTML/login.html">Log in</a>
      </div>
      <div class="footer-contact">
        <strong>Get in touch</strong>
        Food & Drink · Virac, Philippines 4800<br />
        Contact: 0981 027 0704
      </div>
    </div>
    <div class="footer-bottom">
      <span>© 2025 Cubiertos.food.hub — All rights reserved.</span>
      <div class="footer-socials">
        <a href="https://www.facebook.com/profile.php?id=61555258696901" target="_blank"><img src="../IMAGES/Facebook.png" alt="Facebook" /></a>
        <a href="https://www.instagram.com/cubiertos2024/" target="_blank"><img src="../IMAGES/Instagram.png" alt="Instagram" /></a>
        <a href="https://mail.google.com/mail/u/0/#inbox?compose=new" target="_blank"><img src="../IMAGES/Mail.png" alt="Mail" /></a>
      </div>
    </div>
  </footer>

  <!-- ================= STYLES ================= -->
  <style>
    /* ── Availability Calendar ── */
    .avail-calendar-wrap {
      background: #fff;
      border-radius: 20px;
      padding: 22px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.07);
      margin-bottom: 20px;
      border: 1px solid #f0e9df;
    }
    .avail-cal-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 14px;
    }
    .avail-cal-header h4 {
      font-size: 1rem;
      color: #283618;
      margin: 0;
      font-weight: 700;
    }
    .avail-cal-nav {
      background: none;
      border: 2px solid #e0d9ce;
      border-radius: 50%;
      width: 32px; height: 32px;
      cursor: pointer;
      font-size: 1.1rem;
      color: #bc6c25;
      display: flex; align-items: center; justify-content: center;
      transition: all 0.2s;
      line-height: 1;
    }
    .avail-cal-nav:hover { background: #bc6c25; color: #fff; border-color: #bc6c25; }

    .avail-day-labels {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      text-align: center;
      font-size: 0.72rem;
      font-weight: 700;
      color: #aaa;
      margin-bottom: 6px;
    }
    .avail-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 4px;
    }
    .avail-day {
      aspect-ratio: 1;
      border-radius: 10px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      font-size: 0.82rem;
      font-weight: 600;
      cursor: pointer;
      border: 2px solid transparent;
      transition: all 0.18s;
      min-height: 40px;
      user-select: none;
      position: relative;
    }
    .avail-day.empty   { cursor: default; background: transparent; }
    .avail-day.past    { color: #ccc; cursor: not-allowed; background: #f9f9f9; }
    .avail-day.open    { background: #e8f5e9; color: #2e7d32; border-color: #a5d6a7; }
    .avail-day.open:hover    { background: #c8e6c9; border-color: #66bb6a; transform: scale(1.06); }
    .avail-day.partial { background: #fff8e1; color: #e65100; border-color: #ffe082; }
    .avail-day.partial:hover { background: #ffecb3; border-color: #ffd54f; transform: scale(1.06); }
    .avail-day.full    { background: #fce4ec; color: #b71c1c; border-color: #ef9a9a; cursor: not-allowed; }
    .avail-day.selected-day  { outline: 3px solid #bc6c25; outline-offset: 2px; }
    .avail-day.today-ring    { font-weight: 800; text-decoration: underline; }

    .day-dot {
      width: 5px; height: 5px; border-radius: 50%; margin-top: 2px;
    }
    .avail-day.open    .day-dot { background: #2e7d32; }
    .avail-day.partial .day-dot { background: #e65100; }
    .avail-day.full    .day-dot { background: #b71c1c; }

    /* Legend */
    .avail-legend {
      display: flex; gap: 14px; flex-wrap: wrap;
      margin-top: 12px; font-size: 0.76rem; color: #666;
    }
    .avail-legend span { display: flex; align-items: center; gap: 5px; }
    .legend-dot {
      width: 10px; height: 10px; border-radius: 50%; display: inline-block;
    }
    .legend-dot.open    { background: #2e7d32; }
    .legend-dot.partial { background: #e65100; }
    .legend-dot.full    { background: #b71c1c; }
    .legend-dot.past    { background: #ccc; }

    /* ── Slot Cards ── */
    .slot-section { margin-bottom: 20px; }
    .slot-section i { display: block; margin-bottom: 10px; font-style: normal; font-weight: 600; color: #283618; }
    .slot-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .slot-card {
      border: 2px solid #e0d9ce; border-radius: 16px; padding: 16px 12px;
      text-align: center; cursor: pointer; transition: all 0.25s ease;
      background: #fff; position: relative;
    }
    .slot-card input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; }
    .slot-card:hover:not(.taken):not(.over-capacity) { border-color: #bc6c25; background: #fff8f3; }
    .slot-card.selected  { border-color: #bc6c25; background: #fff3e8; box-shadow: 0 4px 12px rgba(188,108,37,0.15); }
    .slot-card.taken     { opacity: 0.5; cursor: not-allowed; background: #f5f5f5; }
    .slot-card.over-capacity {
      opacity: 0.65; cursor: not-allowed;
      background: #fff0f0; border-color: #ffcccc;
    }
    .slot-icon  { font-size: 1.8rem; margin-bottom: 6px; }
    .slot-label { font-weight: 700; color: #283618; font-size: 0.95rem; }
    .slot-time  { color: #bc6c25; font-size: 0.85rem; font-weight: 600; margin: 2px 0 6px; }
    .slot-status { font-size: 0.75rem; color: #888; }
    .slot-card.taken .slot-status         { color: #e74c3c; }
    .slot-card.available .slot-status     { color: #27ae60; }
    .slot-card.over-capacity .slot-status { color: #e74c3c; font-weight: 700; }

    /* ── Guest Warning ── */
    #guestWarning {
      display: none;
      color: #c0392b;
      font-weight: 600;
      margin-top: 8px;
      padding: 10px 14px;
      background: #fce4ec;
      border-radius: 8px;
      border-left: 4px solid #e74c3c;
      font-size: 0.85rem;
    }

    /* ── Capacity Banner ── */
    #capacityBanner {
      display: none;
      background: #fff3cd;
      border: 1px solid #ffc107;
      border-left: 4px solid #e6a000;
      border-radius: 10px;
      padding: 12px 16px;
      margin-bottom: 12px;
      font-size: 0.87rem;
      font-weight: 600;
      color: #7a4f00;
    }

    /* ── Calendar retry button ── */
    #calRetryBtn {
      display: inline-block;
      margin-top: 10px;
      padding: 8px 18px;
      background: #bc6c25;
      color: #fff;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 0.85rem;
      font-weight: 600;
      font-family: inherit;
      transition: background 0.2s;
    }
    #calRetryBtn:hover { background: #a05a1e; }

    @media (max-width: 500px) {
      .slot-grid { grid-template-columns: 1fr; }
    }
  </style>

  <!-- ================= JS ================= -->
  <script>
  document.addEventListener("DOMContentLoaded", function () {

    const form           = document.getElementById("bookingForm");
    const msgBox         = document.getElementById("formMessage");
    const submitBtn      = document.getElementById("submitBtn");
    const dateInput      = document.getElementById("dateInput");
    const guestsInput    = document.getElementById("guestsInput");
    const guestWarning   = document.getElementById("guestWarning");
    const selectedLabel  = document.getElementById("selectedDateLabel");
    const selectedText   = document.getElementById("selectedDateText");

    const SLOTS = ['morning', 'afternoon', 'evening'];

    // ── State ─────────────────────────────────────────────────────────────────
    let calYear       = new Date().getFullYear();
    let calMonth      = new Date().getMonth() + 1;   // 1–12
    let availability  = {};
    let selectedDate  = '';
    let slotRemaining = {};

    // ── CAPACITY BANNER ───────────────────────────────────────────────────────
    const slotSection    = document.querySelector('.slot-section');
    const capacityBanner = document.createElement('div');
    capacityBanner.id    = 'capacityBanner';
    slotSection.insertBefore(capacityBanner, slotSection.querySelector('.slot-grid'));

    // ── CALENDAR ──────────────────────────────────────────────────────────────

    async function loadCalendar(year, month) {
      const label = document.getElementById('calMonthLabel');
      const grid  = document.getElementById('availGrid');

      label.textContent = 'Loading...';
      grid.innerHTML    = '<div style="grid-column:1/-1;text-align:center;color:#aaa;padding:18px;font-size:0.85rem;">Fetching availability…</div>';

      try {
        // ── FIX: use absolute path so it always resolves correctly ────────────
        const res = await fetch('get_calendar_availability.php?year=' + year + '&month=' + month, {
          method: 'GET',
          headers: { 'Accept': 'application/json' },
          cache: 'no-cache'
        });

        // Check HTTP status first
        if (!res.ok) {
          throw new Error('HTTP ' + res.status);
        }

        // Read raw text first so we can diagnose non-JSON responses
        const raw = await res.text();

        let data;
        try {
          data = JSON.parse(raw);
        } catch (parseErr) {
          // PHP is sending HTML/errors — log it and show friendly message
          console.error('Calendar response was not valid JSON:', raw.substring(0, 300));
          throw new Error('Invalid response from server');
        }

        if (data.success) {
          availability = data.availability;
          renderCalendar(year, month);
        } else {
          showCalError(label, grid, data.message || 'Could not load availability.');
        }

      } catch (e) {
        console.error('Calendar fetch error:', e.message);
        showCalError(label, grid, 'Could not load availability. Please refresh.');
      }
    }

    function showCalError(label, grid, message) {
      label.textContent = 'Failed to load';
      grid.innerHTML = `
        <div style="grid-column:1/-1;text-align:center;color:#e74c3c;padding:18px;font-size:0.85rem;">
          ⚠️ ${message}
          <br>
          <button id="calRetryBtn" onclick="retryCalendar()">🔄 Retry</button>
        </div>`;
    }

    // Exposed globally so the inline onclick works
    window.retryCalendar = function() {
      loadCalendar(calYear, calMonth);
    };

    function renderCalendar(year, month) {
      const monthNames = ['January','February','March','April','May','June',
                          'July','August','September','October','November','December'];
      document.getElementById('calMonthLabel').textContent = monthNames[month - 1] + ' ' + year;

      const grid        = document.getElementById('availGrid');
      grid.innerHTML    = '';
      const firstDay    = new Date(year, month - 1, 1).getDay();
      const daysInMonth = new Date(year, month, 0).getDate();
      const todayStr    = new Date().toISOString().split('T')[0];

      for (let i = 0; i < firstDay; i++) {
        const empty = document.createElement('div');
        empty.className = 'avail-day empty';
        grid.appendChild(empty);
      }

      for (let d = 1; d <= daysInMonth; d++) {
        const dateStr = year + '-' + String(month).padStart(2,'0') + '-' + String(d).padStart(2,'0');
        const info    = availability[dateStr] || { status: 'past' };
        const cell    = document.createElement('div');

        cell.className = 'avail-day ' + info.status;
        if (dateStr === todayStr)     cell.classList.add('today-ring');
        if (dateStr === selectedDate) cell.classList.add('selected-day');

        cell.innerHTML = '<span class="day-num">' + d + '</span>' +
          (info.status !== 'past' ? '<span class="day-dot"></span>' : '');

        const tipMap = { open: 'Available', partial: 'Partially Booked', full: 'Fully Booked', past: 'Past date' };
        cell.title = tipMap[info.status] || '';
        if (info.remaining !== undefined && info.status !== 'full') {
          cell.title += ' · ' + info.remaining + ' guest slots remaining';
        }

        if (info.status === 'open' || info.status === 'partial') {
          cell.addEventListener('click', () => selectDate(dateStr, cell));
        }

        grid.appendChild(cell);
      }
    }

    function selectDate(dateStr, cell) {
      document.querySelectorAll('.avail-day.selected-day')
        .forEach(c => c.classList.remove('selected-day'));
      cell.classList.add('selected-day');

      selectedDate    = dateStr;
      dateInput.value = dateStr;

      const d = new Date(dateStr + 'T00:00:00');
      selectedText.textContent = d.toLocaleDateString('en-PH', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
      });
      selectedLabel.style.display = 'block';

      checkAllSlots(dateStr);
    }

    // ── SLOT CHECKING ─────────────────────────────────────────────────────────

    async function checkAllSlots(date) {
      slotRemaining = {};

      SLOTS.forEach(slot => {
        const card   = document.getElementById('slot' + cap(slot));
        const status = document.getElementById('status' + cap(slot));
        card.classList.remove('taken', 'available', 'selected', 'over-capacity');
        card.querySelector('input').disabled = true;
        card.querySelector('input').checked  = false;
        status.textContent = 'Checking…';
      });

      capacityBanner.style.display = 'none';
      guestWarning.style.display   = 'none';

      await Promise.all(SLOTS.map(slot => fetchSlotStatus(date, slot)));
      applyGuestCapacityCheck();
    }

    async function fetchSlotStatus(date, slot) {
      const card   = document.getElementById('slot' + cap(slot));
      const status = document.getElementById('status' + cap(slot));
      const radio  = card.querySelector('input');

      try {
        const res = await fetch(
          'check_slot.php?date=' + encodeURIComponent(date) + '&slot=' + slot,
          { cache: 'no-cache', headers: { 'Accept': 'application/json' } }
        );

        if (!res.ok) throw new Error('HTTP ' + res.status);

        const raw = await res.text();
        let json;
        try {
          json = JSON.parse(raw);
        } catch (e) {
          console.error('Slot check non-JSON response:', raw.substring(0, 200));
          throw new Error('Invalid slot response');
        }

        if (json.available) {
          slotRemaining[slot] = json.remaining;
          card.classList.add('available');
          card.classList.remove('taken');
          radio.disabled     = false;
          status.textContent = json.remaining + ' guest slots left';
        } else {
          slotRemaining[slot] = 0;
          card.classList.add('taken');
          card.classList.remove('available');
          radio.disabled = true;
          radio.checked  = false;
          card.classList.remove('selected');
          status.textContent = 'Unavailable';
        }
      } catch (e) {
        console.error('fetchSlotStatus error for', slot, ':', e.message);
        slotRemaining[slot] = 999;   // fail-safe: let user try
        radio.disabled      = false;
        status.textContent  = 'Status unknown';
      }
    }

    // ── GUEST CAPACITY VALIDATION ─────────────────────────────────────────────

    function applyGuestCapacityCheck() {
      const guests = parseInt(guestsInput.value) || 0;

      if (guests < 1 || !selectedDate) {
        SLOTS.forEach(slot => {
          const card  = document.getElementById('slot' + cap(slot));
          const radio = card.querySelector('input');
          if (card.classList.contains('over-capacity')) {
            card.classList.remove('over-capacity');
            radio.disabled = false;
            document.getElementById('status' + cap(slot)).textContent =
              slotRemaining[slot] !== undefined ? slotRemaining[slot] + ' guest slots left' : 'Select a date first';
          }
        });
        guestWarning.style.display   = 'none';
        capacityBanner.style.display = 'none';
        return;
      }

      let anyExceedsCapacity = false;

      SLOTS.forEach(slot => {
        const card    = document.getElementById('slot' + cap(slot));
        const radio   = card.querySelector('input');
        const isTaken = card.classList.contains('taken');
        const status  = document.getElementById('status' + cap(slot));

        if (!isTaken) {
          const remaining = slotRemaining[slot] !== undefined ? slotRemaining[slot] : 999;

          if (guests > remaining) {
            card.classList.add('over-capacity');
            radio.disabled = true;
            radio.checked  = false;
            card.classList.remove('selected');
            status.textContent = '⚠️ Only ' + remaining + ' slots — your ' + guests + ' guests exceed this';
            anyExceedsCapacity = true;
          } else {
            card.classList.remove('over-capacity');
            radio.disabled     = false;
            status.textContent = remaining + ' guest slots left';
          }
        }
      });

      if (anyExceedsCapacity) {
        guestWarning.style.display = 'block';
        guestWarning.textContent   = '⚠️ Your guest count (' + guests + ') exceeds the remaining capacity for one or more time slots. Please lower the number of guests or choose a different date.';
        capacityBanner.style.display = 'block';
        capacityBanner.innerHTML     = '⚠️ Some slots below can\'t accommodate <strong>' + guests + ' guests</strong>. Reduce your guest count or pick a date with more availability.';
      } else {
        guestWarning.style.display   = 'none';
        capacityBanner.style.display = 'none';
      }
    }

    guestsInput.addEventListener('input', applyGuestCapacityCheck);

    // ── HIGHLIGHT SELECTED SLOT CARD ──────────────────────────────────────────
    document.querySelectorAll('.slot-card input[type="radio"]').forEach(radio => {
      radio.addEventListener('change', function () {
        document.querySelectorAll('.slot-card').forEach(c => c.classList.remove('selected'));
        if (this.checked) this.closest('.slot-card').classList.add('selected');
      });
    });

    document.querySelectorAll('.slot-card').forEach(card => {
      card.addEventListener('click', function (e) {
        if (this.classList.contains('taken') || this.classList.contains('over-capacity')) {
          e.preventDefault();
          e.stopPropagation();
        }
      });
    });

    // ── CALENDAR NAV ──────────────────────────────────────────────────────────
    document.getElementById('calPrev').addEventListener('click', () => {
      calMonth--;
      if (calMonth < 1) { calMonth = 12; calYear--; }
      loadCalendar(calYear, calMonth);
    });
    document.getElementById('calNext').addEventListener('click', () => {
      calMonth++;
      if (calMonth > 12) { calMonth = 1; calYear++; }
      loadCalendar(calYear, calMonth);
    });

    // ── GCASH TOGGLE ──────────────────────────────────────────────────────────
    const gcashRadio = document.querySelector('input[value="GCash"]');
    const cashRadio  = document.querySelector('input[value="Cash"]');
    const gcashBox   = document.getElementById('gcashBox');
    function toggleGCashBox() {
      gcashBox.style.display = gcashRadio.checked ? 'block' : 'none';
    }
    gcashRadio.addEventListener('change', toggleGCashBox);
    cashRadio.addEventListener('change',  toggleGCashBox);
    toggleGCashBox();

    // ── FORM SUBMIT ───────────────────────────────────────────────────────────
    function showMsg(text, isSuccess) {
      msgBox.textContent      = text;
      msgBox.style.display    = 'block';
      msgBox.style.background = isSuccess ? '#d1fae5' : '#fee2e2';
      msgBox.style.color      = isSuccess ? '#065f46' : '#991b1b';
      msgBox.style.border     = '1px solid ' + (isSuccess ? '#6ee7b7' : '#fca5a5');
      msgBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    form.addEventListener('submit', async function (e) {
      e.preventDefault();

      if (!dateInput.value) {
        showMsg('⚠️ Please select a date from the calendar.', false); return;
      }
      if (!form.querySelector('input[name="slot"]:checked')) {
        showMsg('⚠️ Please select a time slot.', false); return;
      }
      if (!form.querySelector('input[name="payment"]:checked')) {
        showMsg('⚠️ Please select a payment method.', false); return;
      }

      if (guestWarning.style.display !== 'none') {
        showMsg('⚠️ Your guest count exceeds the remaining capacity. Please adjust your guest count or pick another date.', false);
        return;
      }

      const checkedSlot = form.querySelector('input[name="slot"]:checked');
      if (checkedSlot) {
        const chosenSlot  = checkedSlot.value;
        const guests      = parseInt(guestsInput.value) || 0;
        const remaining   = slotRemaining[chosenSlot] !== undefined ? slotRemaining[chosenSlot] : 999;
        if (guests > remaining) {
          showMsg('⚠️ The selected slot only has ' + remaining + ' spots left but you entered ' + guests + ' guests.', false);
          return;
        }
      }

      submitBtn.disabled    = true;
      submitBtn.textContent = 'Submitting…';

      try {
        const res  = await fetch('booking_submit.php', { method: 'POST', body: new FormData(form) });
        const raw  = await res.text();
        let json;
        try {
          json = JSON.parse(raw);
        } catch (e) {
          console.error('booking_submit non-JSON:', raw.substring(0, 300));
          showMsg('❌ Server error. Please try again.', false);
          return;
        }

        if (json.success) {
          showMsg('✅ Booking submitted! Your ticket: ' + json.ticket + '. Slot: ' + json.slot + ' on ' + json.date + '. We\'ll contact you to confirm.', true);
          form.reset();
          selectedDate  = '';
          slotRemaining = {};
          dateInput.value              = '';
          selectedLabel.style.display  = 'none';
          guestWarning.style.display   = 'none';
          capacityBanner.style.display = 'none';
          SLOTS.forEach(slot => {
            const card = document.getElementById('slot' + cap(slot));
            card.classList.remove('selected','available','taken','over-capacity');
            card.querySelector('input').disabled = true;
            card.querySelector('input').checked  = false;
            document.getElementById('status' + cap(slot)).textContent = 'Select a date first';
          });
          document.querySelectorAll('.avail-day.selected-day')
            .forEach(c => c.classList.remove('selected-day'));
          loadCalendar(calYear, calMonth);
        } else {
          showMsg('❌ ' + (json.message || 'Something went wrong.'), false);
        }
      } catch (err) {
        console.error('Submit error:', err);
        showMsg('❌ Network error. Please try again.', false);
      } finally {
        submitBtn.disabled    = false;
        submitBtn.textContent = 'Submit Booking';
      }
    });

    // ── SCROLL EFFECT ─────────────────────────────────────────────────────────
    window.addEventListener('scroll', function () {
      const header = document.querySelector('.glass-header');
      if (header) header.classList.toggle('scrolled', window.scrollY > 50);
    });

    // ── INIT ──────────────────────────────────────────────────────────────────
    loadCalendar(calYear, calMonth);

  });

  function cap(str) { return str.charAt(0).toUpperCase() + str.slice(1); }
  </script>

</body>
</html>