<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: register.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Calendar — Cubiertos Admin</title>
  <link rel="stylesheet" href="../CSS/adminsamp.css">
  <link rel="icon" type="image/jpg" href="/IMAGES/logo.jpg">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
</head>

<body>

<!-- ── SIDEBAR ─────────────────────────────── -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <img src="../IMAGES/logo.jpg" alt="Cubiertos logo">
    <h2>Cubiertos</h2>
  </div>
  <ul class="sidebar-menu">
    <li><a href="admin.php"><span class="nav-icon">⊞</span><span class="nav-label">Dashboard</span></a></li>
    <li><a href="Revenue.php"><span class="nav-icon">₱</span><span class="nav-label">Revenue</span></a></li>
    <li class="active"><a href="calendar.php"><span class="nav-icon">◫</span><span class="nav-label">Calendar</span></a></li>
    <li><a href="customer.php"><span class="nav-icon">◎</span><span class="nav-label">Customers</span></a></li>
    <li>
  <a href="payment_admin.php">
    <span class="nav-icon">📲</span>
    <span class="nav-label">Payments</span></a>
  </li>
    <li><a href="report.php"><span class="nav-icon">▤</span><span class="nav-label">Reports</span></a></li>
  </ul>
  <div class="sidebar-footer">
    <p>Cubiertos Food Hub &copy; 2025</p>
  </div>
</aside>

<!-- ── HEADER ──────────────────────────────── -->
<header id="adminHeader">
  <div class="logo">
    <h1><span>Calendar</span> Dashboard</h1>
  </div>
  <nav>
    <a href="main.html">Home</a>
    <a href="logout.php" class="btn logout">Logout</a>
  </nav>
</header>

<!-- ── MAIN ────────────────────────────────── -->
<main class="dashboard-container">

  <!-- STAT CARDS -->
  <section class="cal-stats">
    <div class="cal-stat-card" style="--sc:#283618">
      <h3>Total Reservations</h3>
      <p id="totalReservations">0</p>
      <span class="tag">All Bookings</span>
    </div>
    <div class="cal-stat-card" style="--sc:#bc6c25">
      <h3>Today's Bookings</h3>
      <p id="todayBookings">0</p>
      <span class="tag">Scheduled Today</span>
    </div>
    <div class="cal-stat-card" style="--sc:#e67e22">
      <h3>Pending</h3>
      <p id="pendingBookings">0</p>
      <span class="tag">Awaiting Approval</span>
    </div>
    <div class="cal-stat-card" style="--sc:#2980b9">
      <h3>Approved</h3>
      <p id="approvedBookings">0</p>
      <span class="tag">Confirmed</span>
    </div>
  </section>

  <!-- RESERVATION LIST -->
  <section class="res-list-card">
    <div class="res-list-header">
      <h2>Client Reservation List</h2>
      <div class="res-list-controls">
        <div class="search-box">
          <input type="text" id="searchClient" placeholder="Search client…">
        </div>
        <div class="filter-box">
          <select id="statusFilter">
            <option value="all">All Status</option>
            <option value="Pending">Pending</option>
            <option value="Approved">Approved</option>
            <option value="Completed">Completed</option>
            <option value="Cancelled">Cancelled</option>
          </select>
        </div>
      </div>
    </div>
    <div class="res-table-wrap">
      <table class="res-table">
        <thead>
          <tr>
            <th>#</th><th>Name</th><th>Contact</th><th>Service</th>
            <th>Guests</th><th>Amount</th><th>Payment</th><th>Status</th><th>Date</th>
          </tr>
        </thead>
        <tbody id="reservationTableBody"></tbody>
      </table>
    </div>
  </section>

  <!-- CALENDAR + DETAILS -->
  <div class="cal-wrapper">

    <!-- LEFT: calendar + capacity -->
    <div class="cal-left">

      <div class="cal-card">
        <div class="cal-header">
          <button id="prevMonth" class="cal-nav-btn">&#8249;</button>
          <h2 id="monthDisplay"></h2>
          <button id="nextMonth" class="cal-nav-btn">&#8250;</button>
        </div>
        <div class="cal-day-labels">
          <span>Sun</span><span>Mon</span><span>Tue</span>
          <span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
        </div>
        <div id="calendarGrid" class="cal-grid"></div>
      </div>

      <!-- CAPACITY -->
      <div class="cap-card">
        <div class="cap-header">
          <h3>Daily Capacity</h3>
          <button class="cap-set-btn" onclick="setNewLimit()">Set Limit</button>
        </div>
        <p class="cap-label">Maximum Guests</p>
        <p class="cap-value"><span id="maxCapacity">100</span> <small>/ Day</small></p>
        <div class="cap-bar-track">
          <div id="capacityFill" class="cap-bar-fill"></div>
        </div>
        <div class="cap-footer">
          <span>Booked: <strong id="currentBooked" style="color:var(--brand)">0</strong></span>
          <span>Available: <strong id="slotsAvailable">100</strong></span>
        </div>

      </div> <!-- end cap-card -->

    </div> <!-- end cal-left -->

    <!-- RIGHT: booking detail panel -->
    <div class="det-card">
      <div class="det-header">
        <h2>Client Reservations</h2>
      </div>
      <div id="bookingDetails">
        <p class="det-empty">Select a date to view reservations.</p>
      </div>
    </div>

  </div>

</main>

<style>
/* ── CALENDAR PAGE STYLES ──────────────────────────────── */

.cal-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.cal-stat-card {
  background: var(--surface);
  border-radius: var(--r-lg);
  padding: 22px 24px;
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
  position: relative;
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
}

.cal-stat-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0;
  width: 100%; height: 3px;
  background: var(--sc, var(--brand));
  border-radius: var(--r-lg) var(--r-lg) 0 0;
}

.cal-stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }

.cal-stat-card h3 {
  font-size: 0.72rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  color: var(--text-3);
  margin-bottom: 10px;
}

.cal-stat-card p {
  font-size: 2rem;
  font-weight: 700;
  color: var(--sc, var(--brand));
  line-height: 1;
}

.cal-stat-card .tag {
  display: inline-block;
  margin-top: 10px;
  font-size: 0.7rem;
  color: var(--text-3);
  background: var(--surface-2);
  padding: 3px 9px;
  border-radius: 99px;
}

.res-list-card {
  background: var(--surface);
  border-radius: var(--r-xl);
  padding: 26px 28px;
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
  margin-bottom: 24px;
}

.res-list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 22px;
}

.res-list-header h2 {
  font-family: 'Syne', sans-serif;
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--text-1);
}

.res-list-controls { display: flex; gap: 10px; flex-wrap: wrap; }
.res-table-wrap { overflow-x: auto; }

.res-table {
  width: 100%;
  border-collapse: collapse;
}

.res-table thead th {
  font-size: 0.68rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  color: var(--text-3);
  padding: 0 12px 12px;
  text-align: left;
  border-bottom: 1px solid var(--border);
  white-space: nowrap;
}

.res-table tbody tr { border-bottom: 1px solid var(--border); transition: background 0.15s; }
.res-table tbody tr:last-child { border-bottom: none; }
.res-table tbody tr:hover { background: var(--surface-2); }

.res-table td {
  padding: 12px;
  font-size: 0.82rem;
  color: var(--text-1);
  vertical-align: middle;
  white-space: nowrap;
}

.res-table tbody tr td:first-child { font-weight: 700; color: var(--brand); }

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border-radius: 99px;
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.2px;
}

.status-badge::before {
  content: '';
  width: 5px; height: 5px;
  border-radius: 50%;
  background: currentColor;
  flex-shrink: 0;
}

.status-badge.pending   { background: rgba(230,126,34,.12); color: #e67e22; }
.status-badge.approved  { background: rgba(41,128,185,.12); color: #2980b9; }
.status-badge.completed { background: rgba(39,174,96,.12);  color: #27ae60; }
.status-badge.cancelled { background: rgba(231,76,60,.10);  color: #e74c3c; }

.cal-wrapper {
  display: grid;
  grid-template-columns: 360px 1fr;
  gap: 20px;
  align-items: start;
}

.cal-left { display: flex; flex-direction: column; gap: 16px; }

.cal-card {
  background: var(--surface);
  border-radius: var(--r-xl);
  padding: 24px;
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
}

.cal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.cal-header h2 {
  font-family: 'Syne', sans-serif;
  font-size: 1rem;
  font-weight: 700;
  color: var(--text-1);
}

.cal-nav-btn {
  width: 34px; height: 34px;
  border: 1px solid var(--border);
  border-radius: 50%;
  background: var(--surface-2);
  color: var(--text-1);
  font-size: 1.1rem;
  cursor: pointer;
  transition: background 0.2s, color 0.2s;
  display: flex; align-items: center; justify-content: center;
}

.cal-nav-btn:hover { background: var(--brand); color: #fff; border-color: var(--brand); }

.cal-day-labels {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  text-align: center;
  margin-bottom: 8px;
}

.cal-day-labels span {
  font-size: 0.68rem;
  font-weight: 600;
  color: var(--text-3);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 4px 0;
}

.cal-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 5px;
}

.day {
  aspect-ratio: 1;
  background: var(--surface-2);
  border-radius: 10px;
  padding: 4px;
  cursor: pointer;
  border: 1px solid transparent;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 2px;
  transition: border-color 0.15s, transform 0.15s, background 0.15s;
  min-width: 0;
  overflow: hidden;
}

.day:hover { border-color: var(--brand); transform: translateY(-2px); }

.day span {
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--text-1);
  line-height: 1;
}

.day.has-booking { border-color: var(--brand); background: var(--brand-lt); }
.day.has-booking span { color: var(--brand-dk); }

.day.has-booking small {
  font-size: 0.58rem;
  font-weight: 700;
  color: var(--brand);
  text-align: center;
  line-height: 1;
}

.day.today { background: var(--brand); border-color: var(--brand-dk); }
.day.today span { color: #fff; }
.day.today small { color: rgba(255,255,255,0.85); }

.day.selected {
  border-color: var(--brand-dk);
  box-shadow: 0 0 0 2px var(--brand);
  transform: translateY(-2px);
}

/* CAPACITY CARD */
.cap-card {
  background: var(--surface);
  border-radius: var(--r-xl);
  padding: 22px 24px;
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
  position: relative;
}

.cap-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.cap-header h3 {
  font-family: 'Syne', sans-serif;
  font-size: 1rem;
  font-weight: 700;
  color: var(--text-1);
}

.cap-set-btn {
  border: none;
  background: var(--brand);
  color: #fff;
  padding: 8px 16px;
  border-radius: 99px;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  font-family: 'DM Sans', sans-serif;
  transition: background 0.2s, transform 0.15s;
}

.cap-set-btn:hover { background: var(--brand-dk); transform: translateY(-1px); }

.cap-label {
  font-size: 0.72rem;
  color: var(--text-3);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
  margin-bottom: 6px;
}

.cap-value {
  font-size: 1.8rem;
  font-weight: 700;
  color: var(--text-1);
  line-height: 1;
  margin-bottom: 14px;
}

.cap-value small {
  font-size: 0.9rem;
  color: var(--text-3);
  font-weight: 400;
}

.cap-bar-track {
  width: 100%;
  height: 8px;
  background: var(--surface-2);
  border-radius: 99px;
  overflow: hidden;
  margin-bottom: 12px;
}

.cap-bar-fill {
  width: 0%;
  height: 100%;
  background: var(--brand);
  border-radius: 99px;
  transition: width 0.6s ease, background 0.4s ease;
}

.cap-footer {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  color: var(--text-2);
}


/* DETAIL CARD */
.det-card {
  background: var(--surface);
  border-radius: var(--r-xl);
  padding: 24px 26px;
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
  min-height: 420px;
}

.det-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--border);
}

.det-header h2 {
  font-family: 'Syne', sans-serif;
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--text-1);
}

.det-empty {
  color: var(--text-3);
  font-size: 0.875rem;
  text-align: center;
  padding: 40px 0;
}

.booking-card {
  background: var(--surface-2);
  border-radius: var(--r-lg);
  padding: 18px 20px;
  margin-bottom: 14px;
  border: 1px solid var(--border);
  transition: box-shadow 0.2s;
}

.booking-card:hover { box-shadow: var(--shadow-md); }

.booking-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
}

.booking-top h3 {
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--text-1);
}

.status {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border-radius: 99px;
  font-size: 0.68rem;
  font-weight: 700;
}

.status::before {
  content: '';
  width: 5px; height: 5px;
  border-radius: 50%;
  background: currentColor;
}

.status.pending   { background: rgba(230,126,34,.12); color: #e67e22; }
.status.approved  { background: rgba(41,128,185,.12); color: #2980b9; }
.status.completed { background: rgba(39,174,96,.12);  color: #27ae60; }
.status.cancelled { background: rgba(231,76,60,.10);  color: #e74c3c; }

.booking-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
  margin-bottom: 16px;
}

.booking-grid p {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--r-sm);
  padding: 10px 12px;
  font-size: 0.78rem;
  color: var(--text-1);
  line-height: 1.5;
}

.booking-grid p b { color: var(--text-3); font-weight: 600; }

.booking-actions { display: flex; gap: 8px; flex-wrap: wrap; }

.booking-actions button {
  border: none;
  padding: 8px 14px;
  border-radius: var(--r-sm);
  font-size: 0.75rem;
  font-weight: 700;
  cursor: pointer;
  font-family: 'DM Sans', sans-serif;
  transition: opacity 0.15s, transform 0.15s;
  background: rgba(41,128,185,.12);
  color: #2980b9;
}

.booking-actions button:hover { opacity: 0.8; transform: translateY(-1px); }
.booking-actions button:nth-child(2) { background: rgba(39,174,96,.12); color: #27ae60; }
.booking-actions button.danger { background: rgba(231,76,60,.10); color: #e74c3c; }

@media (max-width: 1100px) { .cal-wrapper { grid-template-columns: 1fr; } }
@media (max-width: 700px) {
  .cal-stats { grid-template-columns: 1fr 1fr; }
  .res-list-controls { flex-direction: column; }
}
</style>

<script>
let nav = 0;
let orders = [];
let dbCapacity = 100;
let selectedDateKey = null; // null = show today's capacity

/* ── FETCH (called every 5 seconds) ─────────────────────── */
async function fetchOrders() {
  try {
    const response = await fetch("admin_bookings.php?action=list");
    const data = await response.json();

    if (!data.success) return;

    orders = data.bookings;

    if (data.capacity) {
      dbCapacity = parseInt(data.capacity) || 100;
    }

    updateStats();
    updateCapacity();
    renderCalendar();
    loadReservationList();

  } catch (error) {
    console.error("fetchOrders error:", error);
  }
}

/* ── CAPACITY ────────────────────────────────────────────────
   If a date is selected on the calendar, shows capacity for
   that date. Otherwise defaults to today.
───────────────────────────────────────────────────────────── */
function updateCapacity(dateKey) {
  // If a date was just clicked, store it; otherwise use whatever was stored
  if (dateKey !== undefined) selectedDateKey = dateKey;

  const today    = new Date();
  const todayKey = `${today.getFullYear()}-${today.getMonth()}-${today.getDate()}`;
  const targetKey = selectedDateKey || todayKey;

  // Sum guests for the target date, excluding Cancelled
  const bookedGuests = orders.reduce((sum, order) => {
    if (order.status === "Cancelled") return sum;
    if (!order.booking_datetime) return sum;
    const d   = new Date(order.booking_datetime);
    const key = `${d.getFullYear()}-${d.getMonth()}-${d.getDate()}`;
    return key === targetKey ? sum + Number(order.guests || 0) : sum;
  }, 0);

  const available = Math.max(0, dbCapacity - bookedGuests);
  const pct       = Math.min((bookedGuests / dbCapacity) * 100, 100);

  // Update the cap-label to show which date we're viewing
  const isToday   = targetKey === todayKey;
  const labelEl   = document.querySelector(".cap-label");
  if (labelEl) {
    if (isToday) {
      labelEl.textContent = "Maximum Guests — Today";
    } else {
      // Format the selected date nicely e.g. "May 16, 2026"
      const [y, m, d] = targetKey.split("-").map(Number);
      const dateLabel  = new Date(y, m, d).toLocaleDateString("en-us", {
        month: "long", day: "numeric", year: "numeric"
      });
      labelEl.textContent = `Maximum Guests — ${dateLabel}`;
    }
  }

  document.getElementById("maxCapacity").textContent    = dbCapacity;
  document.getElementById("currentBooked").textContent  = bookedGuests;
  document.getElementById("slotsAvailable").textContent = available;

  const fill = document.getElementById("capacityFill");
  fill.style.width      = pct + "%";
  fill.style.background = bookedGuests >= dbCapacity ? "#e74c3c" : "var(--brand)";
}

/* ── STATS CARDS ────────────────────────── */
function updateStats() {
  const today    = new Date();
  const todayStr = `${today.getFullYear()}-${today.getMonth()}-${today.getDate()}`;

  const todayCount = orders.filter(o => {
    if (!o.booking_datetime) return false;
    const d   = new Date(o.booking_datetime);
    const key = `${d.getFullYear()}-${d.getMonth()}-${d.getDate()}`;
    return key === todayStr;
  }).length;

  document.getElementById("totalReservations").textContent = orders.length;
  document.getElementById("todayBookings").textContent     = todayCount;
  document.getElementById("pendingBookings").textContent   = orders.filter(o => o.status === "Pending").length;
  document.getElementById("approvedBookings").textContent  = orders.filter(o => o.status === "Approved").length;
}

/* ── CALENDAR RENDER ────────────────────── */
function renderCalendar() {
  const calendarGrid = document.getElementById("calendarGrid");
  const monthDisplay = document.getElementById("monthDisplay");

  const dt = new Date();
  if (nav !== 0) dt.setMonth(new Date().getMonth() + nav);

  const month       = dt.getMonth();
  const year        = dt.getFullYear();
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const paddingDays = new Date(year, month, 1).getDay();

  monthDisplay.innerText = dt.toLocaleDateString("en-us", { month: "long", year: "numeric" });
  calendarGrid.innerHTML = "";

  // Group orders by date
  const ordersByDate = {};
  orders.forEach(order => {
    if (!order.booking_datetime) return;
    const d   = new Date(order.booking_datetime);
    const key = `${d.getFullYear()}-${d.getMonth()}-${d.getDate()}`;
    if (!ordersByDate[key]) ordersByDate[key] = [];
    ordersByDate[key].push(order);
  });

  const today = new Date();

  for (let i = 1; i <= paddingDays + daysInMonth; i++) {
    const daySquare = document.createElement("div");

    if (i > paddingDays) {
      const dayNumber = i - paddingDays;
      daySquare.classList.add("day");
      daySquare.innerHTML = `<span>${dayNumber}</span>`;

      const key      = `${year}-${month}-${dayNumber}`;
      const bookings = ordersByDate[key];

      if (bookings) {
        daySquare.classList.add("has-booking");
        const badge = document.createElement("small");
        badge.innerText = bookings.length + (bookings.length === 1 ? " booking" : " bookings");
        daySquare.appendChild(badge);
        daySquare.addEventListener("click", (e) => {
          document.querySelectorAll(".day").forEach(d => d.classList.remove("selected"));
          e.currentTarget.classList.add("selected");
          showBookings(bookings, key);
        });
      } else {
        // Clicking an empty day resets capacity to that day (0 guests)
        daySquare.addEventListener("click", (e) => {
          document.querySelectorAll(".day").forEach(d => d.classList.remove("selected"));
          e.currentTarget.classList.add("selected");
          selectedDateKey = key;
          updateCapacity(key);
          document.getElementById("bookingDetails").innerHTML =
            `<p class="det-empty">No bookings on this date.</p>`;
        });
      }

      if (
        dayNumber === today.getDate()  &&
        month     === today.getMonth() &&
        year      === today.getFullYear()
      ) {
        daySquare.classList.add("today");
      }
    }

    calendarGrid.appendChild(daySquare);
  }
}

/* ── SHOW BOOKINGS IN DETAIL PANEL ──────── */
function showBookings(bookings, dateKey) {
  // Update capacity bar for the clicked date
  updateCapacity(dateKey);

  // Highlight selected day on calendar
  document.querySelectorAll(".day").forEach(d => d.classList.remove("selected"));
  if (event && event.currentTarget) event.currentTarget.classList.add("selected");

  const container = document.getElementById("bookingDetails");
  container.innerHTML = "";

  // Show a back-to-today button if not today
  const today    = new Date();
  const todayKey = `${today.getFullYear()}-${today.getMonth()}-${today.getDate()}`;
  if (dateKey !== todayKey) {
    container.innerHTML += `
      <div style="margin-bottom:14px;">
        <button onclick="resetToToday()" style="
          border:1px solid var(--border);
          background:var(--surface-2);
          color:var(--text-2);
          padding:6px 14px;
          border-radius:99px;
          font-size:0.75rem;
          font-weight:600;
          cursor:pointer;
          font-family:'DM Sans',sans-serif;
        ">← Back to Today</button>
      </div>
    `;
  }

  bookings.forEach(order => {
    container.innerHTML += `
      <div class="booking-card">
        <div class="booking-top">
          <h3>${order.name}</h3>
          <span class="status ${order.status.toLowerCase()}">${order.status}</span>
        </div>
        <div class="booking-grid">
          <p><b>📞 Contact</b><br>${order.phone}</p>
          <p><b>🎉 Service</b><br>${order.occasion}</p>
          <p><b>👥 Guests</b><br>${order.guests}</p>
          <p><b>💳 Payment</b><br>${order.payment_method}</p>
          <p><b>💰 Amount</b><br>₱${Number(order.amount).toLocaleString()}</p>
          <p><b>📅 Schedule</b><br>${new Date(order.booking_datetime).toLocaleString()}</p>
        </div>
      </div>
    `;
  });
}

/* ── RESET TO TODAY ─────────────────────── */
window.resetToToday = function () {
  selectedDateKey = null;
  updateCapacity();
  document.querySelectorAll(".day").forEach(d => d.classList.remove("selected"));
  document.getElementById("bookingDetails").innerHTML =
    `<p class="det-empty">Select a date to view reservations.</p>`;
};

/* ── STATUS UPDATES ─────────────────────── */
async function updateStatus(id, status) {
  try {
    await fetch("admin_bookings.php?action=update_status", {
      method:  "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body:    `id=${id}&status=${status}`
    });
    fetchOrders(); // refresh everything including capacity
  } catch (error) {
    console.error(error);
  }
}

window.approveBooking  = id => updateStatus(id, "Approved");
window.completeBooking = id => updateStatus(id, "Completed");
window.cancelBooking   = id => { if (confirm("Cancel this booking?")) updateStatus(id, "Cancelled"); };

/* ── RESERVATION TABLE ──────────────────── */
function loadReservationList() {
  const tableBody   = document.getElementById("reservationTableBody");
  tableBody.innerHTML = "";

  const searchValue = document.getElementById("searchClient")?.value.toLowerCase() || "";
  const filterValue = document.getElementById("statusFilter")?.value || "all";

  let count = 1;
  orders.forEach(order => {
    if (order.name && !order.name.toLowerCase().includes(searchValue)) return;
    if (filterValue !== "all" && order.status !== filterValue) return;

    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${count++}</td>
      <td>${order.name}</td>
      <td>${order.phone}</td>
      <td>${order.occasion}</td>
      <td>${order.guests} pax</td>
      <td>₱${Number(order.amount).toLocaleString()}</td>
      <td>${order.payment_method}</td>
      <td><span class="status-badge ${order.status.toLowerCase()}">${order.status}</span></td>
      <td>${new Date(order.booking_datetime).toLocaleString()}</td>
    `;
    tableBody.appendChild(row);
  });

  if (count === 1) {
    tableBody.innerHTML = `
      <tr>
        <td colspan="9" style="text-align:center;padding:24px;color:#999;">
          No reservations found.
        </td>
      </tr>
    `;
  }
}

/* ── SET CAPACITY LIMIT ─────────────────── */
window.setNewLimit = async function () {
  const input    = prompt("Enter new maximum guests per day:", dbCapacity);
  if (input === null) return;
  const newLimit = parseInt(input);
  if (isNaN(newLimit) || newLimit < 1) {
    alert("Please enter a valid number greater than 0.");
    return;
  }

  try {
    const res  = await fetch("admin_bookings.php?action=set_capacity", {
      method:  "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body:    `limit=${newLimit}`
    });
    const json = await res.json();
    if (json.success) {
      dbCapacity = newLimit;
      updateCapacity(); // immediately reflect new limit
    } else {
      alert("Failed to save limit. Please try again.");
    }
  } catch (err) {
    console.error("setNewLimit error:", err);
    alert("Network error. Please try again.");
  }
};

/* ── CALENDAR NAV ───────────────────────── */
document.getElementById("prevMonth").addEventListener("click", () => { nav--; renderCalendar(); });
document.getElementById("nextMonth").addEventListener("click", () => { nav++; renderCalendar(); });

/* ── SEARCH / FILTER ────────────────────── */
document.getElementById("searchClient")?.addEventListener("input", loadReservationList);
document.getElementById("statusFilter")?.addEventListener("change", loadReservationList);

/* ── INIT + AUTO-REFRESH every 5 seconds ── */
fetchOrders();
setInterval(fetchOrders, 5000);
</script>

</body>
</html>