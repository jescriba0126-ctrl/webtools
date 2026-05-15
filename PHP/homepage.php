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

      <a href="profile.php" class="login-btn">
          <i>👤</i> Profile
      </a>

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

<!-- ================= TESTIMONIAL SECTION ================= -->

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
        <div>
          <h3>lance_vs_zombies@017</h3>
          <span>Birthday Celebration</span>
        </div>
      </div>
      <p>"The ambiance was warm and elegant. The food was amazing and the staff were very accommodating throughout our celebration."</p>
      <div class="stars">★★★★★</div>
    </div>

    <div class="testimonial-card featured-card">
      <div class="quote-icon">❝</div>
      <div class="testimonial-top">
        <img src="../IMAGES/customer2.jpg" alt="Customer">
        <div>
          <h3>pplyant_</h3>
          <span>Wedding Reception</span>
        </div>
      </div>
      <p>"Cubiertos made our wedding unforgettable. Everything from the setup to the service exceeded our expectations."</p>
      <div class="stars">★★★★★</div>
    </div>

    <div class="testimonial-card light-card">
      <div class="quote-icon">❝</div>
      <div class="testimonial-top">
        <img src="../IMAGES/customer3.jpg" alt="Customer">
        <div>
          <h3>spro.usv</h3>
          <span>Family Gathering</span>
        </div>
      </div>
      <p>"One of the best dining experiences in Catanduanes. Cozy atmosphere, delicious food, and excellent customer service."</p>
      <div class="stars">★★★★★</div>
    </div>

    <div class="testimonial-card featured-card">
      <div class="quote-icon">❝</div>
      <div class="testimonial-top">
        <img src="../IMAGES/customer4.jpg" alt="Customer">
        <div>
          <h3>Ches_ru@12</h3>
          <span>Wedding Reception</span>
        </div>
      </div>
      <p>"We celebrated our Wedding at Cubiertos and everything was perfect. The food was flavorful, the place felt cozy, and the staff treated us so well."</p>
      <div class="stars">★★★★★</div>
    </div>

  </div>

</section>

<!-- ================= BOOKING STATS ================= -->

<section class="booking-stats">
  <div class="stat-card">
    <h3>500+</h3>
    <p>Successful Events</p>
  </div>
  <div class="stat-card">
    <h3>4.9★</h3>
    <p>Customer Satisfaction</p>
  </div>
  <div class="stat-card">
    <h3>24/7</h3>
    <p>Reservation Support</p>
  </div>
  <div class="stat-card">
    <h3>100%</h3>
    <p>Freshly Prepared Meals</p>
  </div>
</section>

  <!-- ================= BOOKING FORM ================= -->
  <section id="booking" class="booking-section">
    <div class="booking-container">

      <h3>Book an Appointment</h3>
      <p class="intro-text">
        Reserve your table for birthdays, weddings, or special events at
        <b>Cubiertos Food Hub</b>.
        <br><small style="color:#bc6c25; font-weight:600; display:block; margin-top:6px;">
          ⏰ Bookings accepted daily from 8:00 AM to 10:00 PM only. Each event has a 2-hour slot.
        </small>
      </p>

      <!-- SUCCESS / ERROR BANNER -->
      <div id="formMessage" style="display:none; padding:14px 18px; border-radius:8px;
           margin-bottom:18px; font-size:15px; font-weight:500;"></div>

      <form id="bookingForm" class="booking-form">

        <!-- FULL NAME -->
        <p>
          <i>Full Name</i><br>
          <?php
          $prefill_name  = '';
          $prefill_email = '';
          $prefill_phone = '';
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
          <input type="text" name="name" placeholder="Enter your full name"
                 value="<?= $prefill_name ?>" required>
        </p>

        <!-- EMAIL -->
        <p>
          <i>Email Address</i><br>
          <input type="email" name="email" placeholder="Enter your email"
                 value="<?= $prefill_email ?>" required>
        </p>

        <!-- PHONE -->
        <p>
          <i>Phone Number</i><br>
          <input type="tel" name="phone" placeholder="e.g. 0981 027 0704"
                 value="<?= $prefill_phone ?>" required>
        </p>

        <!-- OCCASION -->
        <p>
          <i>Type of Occasion</i><br>
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
        <p>
          <i>Number of Guests</i><br>
          <input type="number" name="guests" placeholder="Enter number of guests"
                 min="1" max="500" required>
        </p>

        <!-- DATE & TIME -->
        <p>
          <i>Preferred Date &amp; Time</i><br>
          <input type="datetime-local" name="datetime" id="datetimeInput" required
                 min="<?= date('Y-m-d\TH:i') ?>">
          <!-- Hint shown instantly when user picks a bad time -->
          <small id="timeHint" style="color:#bc6c25; font-size:0.82rem; margin-top:4px; display:none;"></small>
        </p>

        <!-- PACKAGE -->
        <p>
          <i>Package Choice</i><br>
          <select name="package">
            <option value="">-- Choose a Package --</option>
            <option value="basic">Basic Package (₱2,000 - Small Group)</option>
            <option value="standard">Standard Package (₱5,000 - 10 Guests)</option>
            <option value="premium">Premium Package (₱10,000+ - Events)</option>
          </select>
        </p>

<!-- ================= PAYMENT ================= -->

<div class="payment-wrapper">

  <div class="section-top">
    <h4>Choose Payment Method</h4>
    <p>Select your preferred payment option for reservation confirmation.</p>
  </div>

  <div class="payment-methods modern-payment">

    <!-- GCASH -->
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

    <!-- CASH -->
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

  <!-- ================= GCASH DETAILS ================= -->

  <div class="gcash-box" id="gcashBox">
    <div class="gcash-content">
      <div class="gcash-layout">

        <!-- LEFT SIDE -->
        <div class="gcash-info-side">
          <div class="gcash-header">
            <div class="gcash-icon">📲</div>
            <div>
              <h4>GCash Reservation Details</h4>
              <p>Send your reservation payment to:</p>
            </div>
          </div>
          <div class="gcash-details">
            <div class="detail-item">
              <span>Account Name</span>
              <strong>Cubiertos Food Hub</strong>
            </div>
            <div class="detail-item">
              <span>GCash Number</span>
              <strong>0981 027 0704</strong>
            </div>
          </div>
          <div class="upload-proof">
            <label>Upload Payment Screenshot</label>
            <input type="file" name="proof" accept="image/*">
          </div>
        </div>

        <!-- RIGHT SIDE -->
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

  <!-- ================= SPECIAL NOTES ================= -->

  <p>
    <i>Special Notes</i><br>
    <textarea name="message" rows="4"
              placeholder="Any special requests or details?"></textarea>
  </p>

  <!-- ================= BUTTONS ================= -->

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
        <p class="footer-tagline">
          "Savor the flavors where every bite tells a story."
        </p>
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
        <a href="https://www.facebook.com/profile.php?id=61555258696901" target="_blank" aria-label="Facebook">
          <img src="../IMAGES/Facebook.png" alt="Facebook" />
        </a>
        <a href="https://www.instagram.com/cubiertos2024/" target="_blank" aria-label="Instagram">
          <img src="../IMAGES/Instagram.png" alt="Instagram" />
        </a>
        <a href="https://mail.google.com/mail/u/0/#inbox?compose=new" target="_blank" aria-label="Mail">
          <img src="../IMAGES/Mail.png" alt="Mail" />
        </a>
      </div>
    </div>
  </footer>

  

  <!-- ================= JS ================= -->
  <script>
  document.addEventListener("DOMContentLoaded", function () {

    const form          = document.getElementById("bookingForm");
    const msgBox        = document.getElementById("formMessage");
    const submitBtn     = document.getElementById("submitBtn");
    const datetimeInput = document.getElementById("datetimeInput");
    const timeHint      = document.getElementById("timeHint");

    const BOOKING_OPEN  = 8;   // 8:00 AM
    const BOOKING_CLOSE = 22;  // 10:00 PM
    const BUFFER_HOURS  = 2;   // 2-hour buffer

    // ── Keep min = now so past times stay greyed out ──────────
    function updateMin() {
      const now = new Date();
      const pad = n => String(n).padStart(2, '0');
      datetimeInput.min =
        `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}` +
        `T${pad(now.getHours())}:${pad(now.getMinutes())}`;
    }
    updateMin();
    setInterval(updateMin, 60000);

    // ── Live hint when user picks a time ─────────────────────
    datetimeInput.addEventListener("change", async function () {
      timeHint.style.display = "none";
      timeHint.textContent   = "";
      if (!this.value) return;

      const chosen = new Date(this.value);
      const hour   = chosen.getHours();

      // Past check
      if (chosen <= new Date()) {
        timeHint.textContent   = "⚠️ That date and time has already passed.";
        timeHint.style.display = "block";
        return;
      }

      // Business hours check
      if (hour < BOOKING_OPEN || hour >= BOOKING_CLOSE) {
        timeHint.textContent   = "⚠️ Please choose a time between 8:00 AM and 10:00 PM.";
        timeHint.style.display = "block";
        return;
      }

      // Live time slot conflict check against the server
      try {
        const res  = await fetch(`check_slot.php?datetime=${encodeURIComponent(this.value)}`);
        const json = await res.json();
        if (!json.available) {
          timeHint.textContent   = "⚠️ " + json.message;
          timeHint.style.display = "block";
        }
      } catch (err) {
        // Silently fail — server will catch it on submit anyway
      }
    });

    // ── Message helper ────────────────────────────────────────
    function showMsg(text, isSuccess) {
      msgBox.textContent = text;
      msgBox.style.display = "block";
      msgBox.style.background = isSuccess ? "#d1fae5" : "#fee2e2";
      msgBox.style.color      = isSuccess ? "#065f46" : "#991b1b";
      msgBox.style.border     = "1px solid " + (isSuccess ? "#6ee7b7" : "#fca5a5");
      msgBox.scrollIntoView({ behavior: "smooth", block: "center" });
    }

    // ── Form submit ───────────────────────────────────────────
    form.addEventListener("submit", async function (e) {
      e.preventDefault();

      // 1. Payment check
      const paymentChecked = form.querySelector('input[name="payment"]:checked');
      if (!paymentChecked) {
        showMsg("⚠️ Please select a payment method.", false);
        return;
      }

      // 2. Date/time present
      if (!datetimeInput.value) {
        showMsg("⚠️ Please select a date and time.", false);
        return;
      }

      // 3. Past date/time
      const chosenDT = new Date(datetimeInput.value);
      if (chosenDT <= new Date()) {
        showMsg("⚠️ Please select a future date and time.", false);
        return;
      }

      // 4. Business hours
      const chosenHour = chosenDT.getHours();
      if (chosenHour < BOOKING_OPEN || chosenHour >= BOOKING_CLOSE) {
        showMsg("⚠️ Bookings are only accepted between 8:00 AM and 10:00 PM. Please choose a valid time.", false);
        return;
      }

      submitBtn.disabled    = true;
      submitBtn.textContent = "Submitting…";

      const data = new FormData(form);

      try {
        const res  = await fetch("booking_submit.php", {
          method: "POST",
          body:   data,
        });

        const json = await res.json();

        if (json.success) {
          showMsg(
            "✅ Booking submitted! Your ticket number is: " + json.ticket +
            ". We'll contact you to confirm.",
            true
          );
          form.reset();
          timeHint.style.display = "none";
        } else {
          showMsg("❌ " + (json.message || "Something went wrong."), false);
        }

      } catch (err) {
        showMsg("❌ Network error. Please try again.", false);
        console.error(err);
      } finally {
        submitBtn.disabled    = false;
        submitBtn.textContent = "Submit Booking";
      }
    });

  });

  // ================= GCASH TOGGLE =================

  const gcashRadio = document.querySelector('input[value="GCash"]');
  const cashRadio  = document.querySelector('input[value="Cash"]');
  const gcashBox   = document.getElementById("gcashBox");

  function toggleGCashBox() {
    gcashBox.style.display = gcashRadio.checked ? "block" : "none";
  }

  gcashRadio.addEventListener("change", toggleGCashBox);
  cashRadio.addEventListener("change",  toggleGCashBox);
  toggleGCashBox();

  // Glass Header Scroll Effect
  window.addEventListener("scroll", function () {
    const header = document.querySelector(".glass-header");
    if(header) header.classList.toggle("scrolled", window.scrollY > 50);
  });
  </script>

</body>
</html>