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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

  <header>
    <div class="logo">
      <img src="../IMAGES/logo.jpg" alt="Cubiertos Food Hub Logo">
    </div>
    <nav>
        <a href="../HTML/main.html">Home</a>
        <a href="../HTML/about.html">About</a>
        <a href="../HTML/Store.html">Our Stores</a>
        <a href="../HTML/contacts.html">Contacts</a>
        <a href="profile.php" class="login"><i>👤</i> Profile</a>
    </nav>
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
    <div class="testimonial-container">
      <div class="testimonial-card">
        <div class="testimonial-top">
          <img src="../IMAGES/user1.jpg" alt="Customer">
          <div><h3>Maria Santos</h3><span>Birthday Celebration</span></div>
        </div>
        <p>"The ambiance was warm and elegant. The food was amazing and the staff were very accommodating throughout our celebration."</p>
        <div class="stars">★★★★★</div>
      </div>
      <div class="testimonial-card featured">
        <div class="testimonial-top">
          <img src="../IMAGES/user2.jpg" alt="Customer">
          <div><h3>John Ramirez</h3><span>Wedding Reception</span></div>
        </div>
        <p>"Cubiertos made our wedding unforgettable. Everything from the setup to the service exceeded our expectations."</p>
        <div class="stars">★★★★★</div>
      </div>
      <div class="testimonial-card">
        <div class="testimonial-top">
          <img src="../IMAGES/user3.jpg" alt="Customer">
          <div><h3>Angela Cruz</h3><span>Family Gathering</span></div>
        </div>
        <p>"One of the best dining experiences in Catanduanes. Cozy atmosphere, delicious food, and excellent customer service."</p>
        <div class="stars">★★★★★</div>
      </div>
    </div>
  </section>

  <!-- ================= BOOKING FORM ================= -->
  <section id="booking" class="booking-section">
    <div class="booking-container">

      <h3>Book an Appointment</h3>
      <p class="intro-text">
        Reserve your table for birthdays, weddings, or special events at
        <b>Cubiertos Food Hub</b>.
      </p>

      <!-- SUCCESS / ERROR BANNER -->
      <div id="formMessage" style="display:none; padding:14px 18px; border-radius:8px;
           margin-bottom:18px; font-size:15px; font-weight:500;"></div>

      <form id="bookingForm" class="booking-form">

        <!-- FULL NAME -->
        <p>
          <i>Full Name</i><br>
          <?php
          // Pre-fill if user is logged in
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

        <!-- DATE -->
        <p>
          <i>Preferred Date &amp; Time</i><br>
          <input type="datetime-local" name="datetime" required
                 min="<?= date('Y-m-d\TH:i') ?>">
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

        <!-- PAYMENT -->
        <div class="payment-wrapper">
          <i class="payment-title">Preferred Payment Method</i>
          <div class="payment-methods">

            <label class="payment-option">
              <input type="radio" name="payment" value="GCash" required>
              <span class="payment-card">
                <div class="payment-icon">📱</div>
                <div><h4>GCash</h4><small>Fast mobile payment</small></div>
              </span>
            </label>

            <label class="payment-option">
              <input type="radio" name="payment" value="Maya">
              <span class="payment-card">
                <div class="payment-icon">💳</div>
                <div><h4>Maya</h4><small>Digital wallet payment</small></div>
              </span>
            </label>

            <label class="payment-option">
              <input type="radio" name="payment" value="Online Banking">
              <span class="payment-card">
                <div class="payment-icon">🏦</div>
                <div><h4>Online Banking</h4><small>BPI, BDO, Metrobank</small></div>
              </span>
            </label>

            <label class="payment-option">
              <input type="radio" name="payment" value="Credit / Debit Card">
              <span class="payment-card">
                <div class="payment-icon">💳</div>
                <div><h4>Credit / Debit Card</h4><small>Visa &amp; Mastercard</small></div>
              </span>
            </label>

            <label class="payment-option">
              <input type="radio" name="payment" value="Cash">
              <span class="payment-card">
                <div class="payment-icon">💵</div>
                <div><h4>Cash Payment</h4><small>Pay at the venue</small></div>
              </span>
            </label>

          </div>
        </div>

        <!-- NOTES -->
        <p>
          <i>Special Notes</i><br>
          <textarea name="message" rows="4"
                    placeholder="Any special requests or details?"></textarea>
        </p>

        <!-- BUTTONS -->
        <div class="form-actions">
          <button type="submit" class="btn-submit" id="submitBtn">
            Submit Booking
          </button>
          <button type="reset" class="btn-reset">Clear Form</button>
        </div>

      </form>

      <div class="booking-info">
        <h4>Need Help?</h4>
        <p>Contact us at <b>0981 027 0704</b><br>
        or visit <em>Imelda Blvd., Rawis, Virac, Catanduanes</em></p>
      </div>

    </div>
  </section>

  <!-- ================= JS ================= -->
  <script>
  document.addEventListener("DOMContentLoaded", function () {

    const form       = document.getElementById("bookingForm");
    const msgBox     = document.getElementById("formMessage");
    const submitBtn  = document.getElementById("submitBtn");

    function showMsg(text, isSuccess) {
      msgBox.textContent = text;
      msgBox.style.display = "block";
      msgBox.style.background = isSuccess ? "#d1fae5" : "#fee2e2";
      msgBox.style.color      = isSuccess ? "#065f46" : "#991b1b";
      msgBox.style.border     = "1px solid " + (isSuccess ? "#6ee7b7" : "#fca5a5");
      msgBox.scrollIntoView({ behavior: "smooth", block: "center" });
    }

    form.addEventListener("submit", async function (e) {
      e.preventDefault();

      // Basic client-side check for payment
      const paymentChecked = form.querySelector('input[name="payment"]:checked');
      if (!paymentChecked) {
        showMsg("⚠️ Please select a payment method.", false);
        return;
      }

      submitBtn.disabled   = true;
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
        } else {
          showMsg("❌ " + (json.message || "Something went wrong."), false);
        }

      } catch (err) {
        showMsg("❌ Network error. Please try again.", false);
        console.error(err);
      } finally {
        submitBtn.disabled   = false;
        submitBtn.textContent = "Submit Booking";
      }
    });

  });
  </script>

</body>
</html>