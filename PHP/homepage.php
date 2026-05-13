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
      <p>At Cubiertos, our mission is simple — to bring warmth, comfort, and connection through food made from the heart. Every meal we serve celebrates home and togetherness, proving that good food isn’t just about taste — it’s about belonging.</p>
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
        <div>
          <h3>Maria Santos</h3>
          <span>Birthday Celebration</span>
        </div>
      </div>

      <p>
        “The ambiance was warm and elegant. The food was amazing and the staff were very accommodating throughout our celebration.”
      </p>

      <div class="stars">★★★★★</div>
    </div>

    <div class="testimonial-card featured">
      <div class="testimonial-top">
        <img src="../IMAGES/user2.jpg" alt="Customer">
        <div>
          <h3>John Ramirez</h3>
          <span>Wedding Reception</span>
        </div>
      </div>

      <p>
        “Cubiertos made our wedding unforgettable. Everything from the setup to the service exceeded our expectations.”
      </p>

      <div class="stars">★★★★★</div>
    </div>

    <div class="testimonial-card">
      <div class="testimonial-top">
        <img src="../IMAGES/user3.jpg" alt="Customer">
        <div>
          <h3>Angela Cruz</h3>
          <span>Family Gathering</span>
        </div>
      </div>

      <p>
        “One of the best dining experiences in Catanduanes. Cozy atmosphere, delicious food, and excellent customer service.”
      </p>

      <div class="stars">★★★★★</div>
    </div>

  </div>

</section>



<!-- ================= BOOKING FORM ================= -->
<section id="booking" class="booking-section">

  <div class="booking-container">

    <h3>Book an Appointment</h3>

    <p class="intro-text">
      Reserve your table for birthdays, weddings,
      or special events at
      <b>Cubiertos Food Hub</b>.
    </p>

    <!-- ================= FORM ================= -->

    <form
      id="bookingForm"
      class="booking-form"
      method="POST"
    >

      <!-- FULL NAME -->
      <p>
        <i>Full Name</i><br>

        <input
          type="text"
          name="name"
          placeholder="Enter your full name"
          required
        >
      </p>

      <!-- EMAIL -->
      <p>
        <i>Email Address</i><br>

        <input
          type="email"
          name="email"
          placeholder="Enter your email"
          required
        >
      </p>

      <!-- PHONE -->
      <p>
        <i>Phone Number</i><br>

        <input
          type="tel"
          name="phone"
          placeholder="e.g. 0981 027 0704"
          required
        >
      </p>

      <!-- OCCASION -->
      <p>
        <i>Type of Occasion</i><br>

        <select
          name="occasion"
          required
        >

          <option value="">
            -- Select Occasion --
          </option>

          <option value="dine-in">
            Dine In
          </option>

          <option value="birthday">
            Birthday
          </option>

          <option value="wedding">
            Wedding
          </option>

          <option value="corporate">
            Corporate Event
          </option>

          <option value="other">
            Other
          </option>

        </select>
      </p>

      <!-- GUESTS -->
      <p>
        <i>Number of Guests</i><br>

        <input
          type="number"
          name="guests"
          placeholder="Enter number of guests"
          min="1"
          max="100"
          required
        >
      </p>

      <!-- DATE -->
      <p>
        <i>Preferred Date & Time</i><br>

        <input
          type="datetime-local"
          name="datetime"
          required
        >
      </p>

      <!-- PACKAGE -->
      <p>
        <i>Package Choice</i><br>

        <select name="package">

          <option value="">
            -- Choose a Package --
          </option>

          <option value="basic">
            Basic Package (₱2,000 - Small Group)
          </option>

          <option value="standard">
            Standard Package (₱5,000 - 10 Guests)
          </option>

          <option value="premium">
            Premium Package (₱10,000+ - Events)
          </option>

        </select>
      </p>

      <!-- ================= PAYMENT ================= -->

      <div class="payment-wrapper">

        <i class="payment-title">
          Preferred Payment Method
        </i>

        <div class="payment-methods">

          <!-- GCASH -->
          <label class="payment-option">

            <input
              type="radio"
              name="payment"
              value="GCash"
              required
            >

            <span class="payment-card">

              <div class="payment-icon">
                📱
              </div>

              <div>
                <h4>GCash</h4>
                <small>
                  Fast mobile payment
                </small>
              </div>

            </span>

          </label>

          <!-- MAYA -->
          <label class="payment-option">

            <input
              type="radio"
              name="payment"
              value="Maya"
            >

            <span class="payment-card">

              <div class="payment-icon">
                💳
              </div>

              <div>
                <h4>Maya</h4>
                <small>
                  Digital wallet payment
                </small>
              </div>

            </span>

          </label>

          <!-- BANK -->
          <label class="payment-option">

            <input
              type="radio"
              name="payment"
              value="Online Banking"
            >

            <span class="payment-card">

              <div class="payment-icon">
                🏦
              </div>

              <div>
                <h4>Online Banking</h4>
                <small>
                  BPI, BDO, Metrobank
                </small>
              </div>

            </span>

          </label>

          <!-- CARD -->
          <label class="payment-option">

            <input
              type="radio"
              name="payment"
              value="Credit / Debit Card"
            >

            <span class="payment-card">

              <div class="payment-icon">
                
              </div>

              <div>
                <h4>Credit / Debit Card</h4>
                <small>
                  Visa & Mastercard
                </small>
              </div>

            </span>

          </label>

          <!-- CASH -->
          <label class="payment-option">

            <input
              type="radio"
              name="payment"
              value="Cash"
            >

            <span class="payment-card">

              <div class="payment-icon">
                
              </div>

              <div>
                <h4>Cash Payment</h4>
                <small>
                  Pay at the venue
                </small>
              </div>

            </span>

          </label>

        </div>
      </div>

      <!-- NOTES -->
      <p>
        <i>Special Notes</i><br>

        <textarea
          name="message"
          rows="4"
          placeholder="Any special requests or details?"
        ></textarea>
      </p>

      <!-- BUTTONS -->
      <div class="form-actions">

        <button
          type="submit"
          class="btn-submit"
        >
          Submit Booking
        </button>

        <button
          type="reset"
          class="btn-reset"
        >
          Clear Form
        </button>

      </div>

    </form>

    <!-- INFO -->
    <div class="booking-info">

      <h4>Need Help?</h4>

      <p>
        Contact us at
        <b>0981 027 0704</b>
        <br>

        or visit

        <em>
          Imelda Blvd., Rawis,
          Virac, Catanduanes
        </em>
      </p>

    </div>

  </div>

</section>

<!-- ================= JS ================= -->

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

    const bookingForm =
        document.getElementById(
            "bookingForm"
        );

    // ================= GET VALUE =================

    function val(name) {

        const el =
            bookingForm.querySelector(
                `[name="${name}"]`
            );

        return el
            ? String(el.value).trim()
            : "";
    }

    // ================= PACKAGE PRICE =================

    function getAmount(packageType) {

        if (packageType === "basic") {
            return 2000;
        }

        if (packageType === "standard") {
            return 5000;
        }

        if (packageType === "premium") {
            return 10000;
        }

        return 0;
    }

    // ================= OCCASION LABEL =================

    function niceOccasionLabel(code) {

        const map = {

            "dine-in": "Dine In",

            "birthday": "Birthday",

            "wedding": "Wedding",

            "corporate": "Corporate Event",

            "other": "Other"
        };

        return map[code] || code;
    }

    // ================= SUBMIT =================

    bookingForm.addEventListener(
        "submit",
        function (e) {

        e.preventDefault();

        // ================= PAYMENT =================

        const selectedPayment =
            bookingForm.querySelector(
                'input[name="payment"]:checked'
            );

        const paymentMethod =
            selectedPayment
            ? selectedPayment.value
            : "Not Selected";

        // ================= PACKAGE =================

        const selectedPackage =
            val("package");

        const amount =
            getAmount(selectedPackage);

        // ================= TICKET =================

        const ticket =
            "T" +
            Date.now()
                .toString()
                .slice(-6);

        // ================= ORDER =================

        const newOrder = {

            ticket: ticket,

            name: val("name"),

            contact: val("phone"),

            email: val("email"),

            service:
                niceOccasionLabel(
                    val("occasion")
                ),

            guests:
                Number(val("guests")) || 0,

            amount: amount,

            payment: paymentMethod,

            package: selectedPackage,

            status: "Pending",

            datetime: val("datetime"),

            notes: val("message"),

            createdAt:
                new Date().toISOString()
        };

        // ================= DEBUG =================

        console.log(
            "NEW ORDER:",
            newOrder
        );

        // ================= GET ORDERS =================

        let orders =
            JSON.parse(
                localStorage.getItem(
                    "orders"
                )
            ) || [];

        // ================= SAVE =================

        orders.push(newOrder);

        localStorage.setItem(
            "orders",
            JSON.stringify(orders)
        );

        console.log(
            "UPDATED ORDERS:",
            orders
        );

        // ================= SUCCESS =================

        alert(
            "✅ Booking Submitted Successfully!"
        );

        // ================= RESET =================

        bookingForm.reset();

    });

});

</script>
    
     
    </div>
</body>
</html>