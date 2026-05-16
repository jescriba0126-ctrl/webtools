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
<title>Cubiertos — Customers Dashboard</title>

<!-- Same stylesheets as admin -->
<link rel="stylesheet" href="../CSS/adminsamp.css">
<link rel="icon" type="image/jpg" href="/IMAGES/logo.jpg">
<!-- adminsamp.css already imports DM Sans + Syne, but include as fallback -->
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">

<style>
/* ============================================================
   CUSTOMER PAGE — extends adminsamp.css
   Uses the SAME variables, fonts, and patterns as admin.php.
   Only adds what customer.php specifically needs.
============================================================ */

/* ---- Stat cards grid (4-up, matches overview-grid style) ---- */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}

/* Re-use .overview-card from adminsamp.css for stat cards */
.stat-card {
    background: var(--surface);
    border-radius: var(--r-lg);
    padding: 22px 24px;
    border: 1px solid var(--border);
    box-shadow: var(--shadow-sm);
    transition: transform 0.2s, box-shadow 0.2s;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 3px;
    border-radius: var(--r-lg) var(--r-lg) 0 0;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
}

/* individual accent colours — mirrors admin card pattern */
.stat-card.c-total     { }
.stat-card.c-total::before    { background: var(--forest); }
.stat-card.c-total    .stat-value { color: var(--forest); }

.stat-card.c-vip::before      { background: var(--pending-c); }
.stat-card.c-vip      .stat-value { color: var(--pending-c); }

.stat-card.c-repeat::before   { background: var(--approved-c); }
.stat-card.c-repeat   .stat-value { color: var(--approved-c); }

.stat-card.c-top::before      { background: var(--completed-c); }
.stat-card.c-top      .stat-value { color: var(--completed-c); }

.stat-card h3 {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--text-3);
    margin-bottom: 10px;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
    word-break: break-word;
}

/* Top Customer name is long — shrink slightly */
#topCustomer { font-size: 1.2rem !important; line-height: 1.25 !important; }

.stat-tag {
    display: inline-block;
    margin-top: 10px;
    font-size: 0.7rem;
    color: var(--text-3);
    background: var(--surface-2);
    padding: 3px 9px;
    border-radius: 99px;
}

/* ---- Insight row (3 mini-cards, same .card shell) ---- */
.insight-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

/* Re-uses .card from adminsamp for the outer wrapper */
.insight-inner {
    /* sits inside a .card — no extra background needed */
}

.mini-card {
    background: var(--surface-2);
    border-radius: var(--r-lg);
    padding: 18px 20px;
    border: 1px solid var(--border);
    border-left: 3px solid var(--brand);
    transition: transform 0.2s, box-shadow 0.2s;
}

.mini-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
    background: var(--surface);
}

.mini-card .mini-label {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--text-3);
    margin-bottom: 8px;
}

.mini-card .mini-value {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-1);
}

/* ---- Table section header ---- */
/* .card already handles the outer shell.
   We only need the header row inside it. */
.table-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px;
    margin-bottom: 24px;
}

.table-top h2 {
    font-family: 'Syne', sans-serif;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-1);
}

/* ---- Customer type badges (extend .status from adminsamp) ---- */
.customer-type {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 11px;
    border-radius: 99px;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.3px;
}

.customer-type::before {
    content: '';
    width: 6px; height: 6px;
    border-radius: 50%;
    background: currentColor;
    flex-shrink: 0;
}

.customer-type.vip    { background: rgba(230,126,34,0.12); color: var(--pending-c); }
.customer-type.repeat { background: rgba(41,128,185,0.12); color: var(--approved-c); }
.customer-type.normal { background: rgba(39,174,96,0.12);  color: var(--completed-c); }

/* ---- Empty state row ---- */
.empty-state-row td {
    padding: 52px 20px;
    text-align: center;
    color: var(--text-3);
    font-size: 0.9rem;
}

/* ---- Responsive ---- */
@media (max-width: 1100px) {
    .stats-grid   { grid-template-columns: repeat(2, 1fr); }
    .insight-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 900px) {
    /* adminsamp.css collapses sidebar at 900px — match that */
    .stats-grid   { grid-template-columns: repeat(2, 1fr); }
    .insight-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 600px) {
    .stats-grid   { grid-template-columns: 1fr 1fr; }
    .insight-grid { grid-template-columns: 1fr; }
}
</style>
</head>

<body>



<!-- ==================== SIDEBAR (identical markup to admin.php) ==================== -->
<aside class="sidebar">

  <div class="sidebar-logo">
    <img src="../IMAGES/logo.jpg" alt="Cubiertos logo">
    <h2>Cubiertos</h2>
  </div>

  <ul class="sidebar-menu">

    <li>
      <a href="admin.php">
        <span class="nav-icon">⊞</span>
        <span class="nav-label">Dashboard</span>
      </a>
    </li>

    <li>
      <a href="revenue.php">
        <span class="nav-icon">₱</span>
        <span class="nav-label">Revenue</span>
      </a>
    </li>

    <li>
      <a href="calendar.php">
        <span class="nav-icon">◫</span>
        <span class="nav-label">Calendar</span>
      </a>
    </li>

    <li class="active">
      <a href="customer.php">
        <span class="nav-icon">◎</span>
        <span class="nav-label">Customers</span>
      </a>
    </li>

    <li>
  <a href="payment_admin.php">
    <span class="nav-icon">📲</span>
    <span class="nav-label">Payments</span></a>
  </li>

    <li>
      <a href="report.php">
        <span class="nav-icon">▤</span>
        <span class="nav-label">Reports</span>
      </a>
    </li>

  </ul>

  <div class="sidebar-footer">
    <p>Cubiertos Food Hub &copy; 2025</p>
  </div>

</aside>


<!-- ==================== HEADER (identical to admin.php) ==================== -->
<header id="adminHeader">

    <div class="logo">
        <h1><span>Customers</span> Dashboard</h1>
    </div>

    <nav>
        <a href="main.html">Home</a>
        <a href="logout.php" class="btn logout">Logout</a>
    </nav>

</header>

<!-- ==================== MAIN ==================== -->
<main class="dashboard-container">

    <!-- Page title — same .overview-title pattern as admin -->
    <div class="overview-title">
        <h2>Customer Overview</h2>
        <p>Track loyalty, spending habits, and booking frequency</p>
    </div>

    <!-- ---- STAT CARDS ---- -->
    <section class="stats-grid">

        <div class="stat-card c-total">
            <h3>Total Customers</h3>
            <p class="stat-value" id="totalCustomers">—</p>
            <span class="stat-tag">All clients</span>
        </div>

        <div class="stat-card c-vip">
            <h3>VIP Customers</h3>
            <p class="stat-value" id="vipCustomers">—</p>
            <span class="stat-tag">Spending ≥ ₱10,000</span>
        </div>

        <div class="stat-card c-repeat">
            <h3>Repeat Customers</h3>
            <p class="stat-value" id="repeatCustomers">—</p>
            <span class="stat-tag">2 or more bookings</span>
        </div>

        <div class="stat-card c-top">
            <h3>Top Customer</h3>
            <p class="stat-value" id="topCustomer">—</p>
            <span class="stat-tag">Highest total spend</span>
        </div>

    </section>

    <!-- ---- LOYALTY INSIGHTS (inside .card shell) ---- -->
    <div class="card" style="margin-bottom:20px;">

        <div class="notes-header" style="margin-bottom:20px;">
            <h2>Customer Loyalty Insights</h2>
        </div>

        <div class="insight-grid">

            <div class="mini-card">
                <div class="mini-label">Highest Spending</div>
                <div class="mini-value" id="highestSpender">—</div>
            </div>

            <div class="mini-card">
                <div class="mini-label">Most Bookings</div>
                <div class="mini-value" id="mostBookings">—</div>
            </div>

            <div class="mini-card">
                <div class="mini-label">Average Guests per Customer</div>
                <div class="mini-value" id="averageGuests">—</div>
            </div>

        </div>

    </div>

    <!-- ---- CUSTOMER TABLE (inside .card shell) ---- -->
    <div class="card">

        <!-- Header row — uses .table-controls + .search-box + .filter-box from adminsamp -->
        <div class="table-top">

            <h2>Customer Records</h2>

            <!-- adminsamp.css already styles .table-controls, .search-box, .filter-box -->
            <div class="table-controls" style="margin-bottom:0;">

                <div class="search-box">
                    <input type="text" id="searchCustomer" placeholder="Search customer...">
                </div>

                <div class="filter-box">
                    <select id="filterCustomer">
                        <option value="all">All Customers</option>
                        <option value="vip">VIP Customers</option>
                        <option value="repeat">Repeat Customers</option>
                    </select>
                </div>

            </div>

        </div>

        <!-- adminsamp.css styles table, thead th, tbody tr, td globally -->
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Total Bookings</th>
                        <th>Total Guests</th>
                        <th>Total Spending</th>
                        <th>Customer Type</th>
                        <th>Latest Booking</th>
                    </tr>
                </thead>
                <tbody id="customerTableBody">
                    <tr class="empty-state-row"><td colspan="8">Loading customers…</td></tr>
                </tbody>
            </table>
        </div>

    </div>
    <!-- /.card -->

</main>

<!-- ==================== SCRIPT ==================== -->
<script>

/* -------------------------------------------------------
   STATE
------------------------------------------------------- */
let allBookings = [];

/* -------------------------------------------------------
   FETCH
------------------------------------------------------- */
async function fetchCustomers() {
    try {
        const res  = await fetch("admin_bookings.php?action=list");
        const data = await res.json();
        if (data.success) {
            allBookings = data.bookings || [];
            renderCustomers();
        }
    } catch (err) {
        console.error("Fetch error:", err);
    }
}

/* -------------------------------------------------------
   BUILD CUSTOMER MAP
   Aggregates raw booking rows by customer name.
   Tracks the MAX (most recent) booking date.
------------------------------------------------------- */
function buildCustomerMap(bookings) {
    const map = {};

    bookings.forEach(order => {
        const key = (order.name || "").trim().toLowerCase();
        if (!key) return;

        if (!map[key]) {
            map[key] = {
                name:     (order.name || "N/A").trim(),
                phone:    order.phone  || "",
                email:    order.email  || "",
                bookings: 0,
                guests:   0,
                spending: 0,
                latest:   null,
            };
        }

        map[key].bookings++;
        map[key].guests   += Number(order.guests || 0);
        map[key].spending += Number(order.amount  || 0);

        const dt = order.booking_datetime ? new Date(order.booking_datetime) : null;
        if (dt && (!map[key].latest || dt > map[key].latest)) {
            map[key].latest = dt;
        }
    });

    return Object.values(map);
}

/* -------------------------------------------------------
   CUSTOMER TYPE (centralised)
------------------------------------------------------- */
function getType(c) {
    if (c.spending >= 10000) return "vip";
    if (c.bookings >= 2)     return "repeat";
    return "normal";
}

/* -------------------------------------------------------
   RENDER
   Pass 1 → global stats (all customers)
   Pass 2 → filtered rows for the table
------------------------------------------------------- */
function renderCustomers() {

    const search = (document.getElementById("searchCustomer")?.value || "").trim().toLowerCase();
    const filter = document.getElementById("filterCustomer")?.value || "all";

    const all = buildCustomerMap(allBookings);

    /* Pass 1 — stats */
    let vipCount    = 0;
    let repeatCount = 0;
    let totalGuests = 0;
    let topSpender  = { name: "N/A", amount: -1 };
    let topBooker   = { name: "N/A", count:  -1 };

    all.forEach(c => {
        const t = getType(c);
        if (t === "vip")    vipCount++;
        if (t === "repeat") repeatCount++;
        totalGuests += c.guests;
        if (c.spending > topSpender.amount) topSpender = { name: c.name, amount: c.spending };
        if (c.bookings > topBooker.count)   topBooker  = { name: c.name, count:  c.bookings };
    });

    setText("totalCustomers",  all.length);
    setText("vipCustomers",    vipCount);
    setText("repeatCustomers", repeatCount);
    setText("topCustomer",     topSpender.name);
    setText("highestSpender",  topSpender.name);
    setText("mostBookings",    topBooker.name);
    setText("averageGuests",   all.length > 0 ? Math.round(totalGuests / all.length) : 0);

    /* Pass 2 — filter + search */
    const filtered = all.filter(c => {
        const t = getType(c);
        if (filter === "vip"    && t !== "vip")    return false;
        if (filter === "repeat" && t !== "repeat") return false;
        if (search && !c.name.toLowerCase().includes(search)) return false;
        return true;
    });

    /* Render rows */
    const tbody = document.getElementById("customerTableBody");
    if (!tbody) return;
    tbody.innerHTML = "";

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr class="empty-state-row"><td colspan="8">No customers match your search.</td></tr>`;
        return;
    }

    filtered.forEach((c, i) => {

        const t = getType(c);

        const contact = [c.phone, c.email]
            .filter(Boolean)
            .join("<br>") || "—";

        // Date formatted same as admin table
        const dateStr = c.latest
            ? c.latest.toLocaleString("en-PH", {
                year: "numeric", month: "short", day: "numeric",
                hour: "2-digit", minute: "2-digit"
              })
            : "N/A";

        // adminsamp.css adds " pax" via td:nth-child(5)::after —
        // guests column is col 5 so that pseudo-element fires automatically.
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${i + 1}</td>
            <td>${c.name}</td>
            <td style="font-size:0.82rem;color:var(--text-2);line-height:1.65;">${contact}</td>
            <td>${c.bookings}</td>
            <td>${c.guests.toLocaleString()}</td>
            <td>&#8369;${c.spending.toLocaleString()}</td>
            <td><span class="customer-type ${t}">${t.toUpperCase()}</span></td>
            <td>${dateStr}</td>
        `;
        tbody.appendChild(row);
    });
}

function setText(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
}

/* Init */
fetchCustomers();
setInterval(fetchCustomers, 5000);

document.getElementById("searchCustomer")?.addEventListener("input",  renderCustomers);
document.getElementById("filterCustomer")?.addEventListener("change", renderCustomers);

</script>
</body>
</html>