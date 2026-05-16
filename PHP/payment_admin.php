<?php
// =====================================================
//  payment_admin.php  —  ALL-IN-ONE
//  Place in: PHP/payment_admin.php
// =====================================================

session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: register.php");
    exit();
}

include("connect.php");

// ── AUTO-MIGRATE: Add GCash columns if not yet existing ──
try {
    $pdo->exec("
        ALTER TABLE bookings
            ADD COLUMN IF NOT EXISTS gcash_name       VARCHAR(200)  DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS gcash_number     VARCHAR(20)   DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS gcash_reference  VARCHAR(50)   DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS proof_path       VARCHAR(500)  DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS payment_status   VARCHAR(30)   DEFAULT 'Pending',
            ADD COLUMN IF NOT EXISTS ticket_number    VARCHAR(50)   DEFAULT NULL
    ");
} catch (PDOException $e) {
    // Non-fatal — columns may already exist
}

// =====================================================
//  AJAX ACTIONS — return JSON and exit
// =====================================================
$action = $_GET['action'] ?? '';

// ── LIST all GCash payments ───────────────────────────
if ($action === 'list') {
    header("Content-Type: application/json");
    try {
        $stmt = $pdo->prepare("
            SELECT
                id, name, email, phone, occasion, guests, package, amount,
                booking_datetime, payment_method, payment_status,
                gcash_name, gcash_number, gcash_reference,
                proof_path, ticket_number,
                status AS booking_status,
                special_notes, created_at
            FROM bookings
            WHERE payment_method = 'GCash'
            ORDER BY
                CASE WHEN payment_status = 'Pending'   THEN 0
                     WHEN payment_status = 'Confirmed' THEN 1
                     ELSE 2 END,
                created_at DESC
        ");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "payments" => $rows]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
    exit();
}

// ── CONFIRM a payment ─────────────────────────────────
if ($action === 'confirm' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Content-Type: application/json");
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(["success" => false, "message" => "Invalid ID."]);
        exit();
    }
    try {
        $pdo->prepare("
            UPDATE bookings SET payment_status = 'Confirmed'
            WHERE id = ? AND payment_method = 'GCash'
        ")->execute([$id]);

        $pdo->prepare("
            UPDATE bookings SET status = 'Approved'
            WHERE id = ? AND status = 'Pending'
        ")->execute([$id]);

        echo json_encode(["success" => true, "message" => "Payment confirmed."]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cubiertos — GCash Payments</title>
  <link rel="stylesheet" href="../CSS/adminsamp.css">
  <link rel="icon" type="image/jpg" href="/IMAGES/logo.jpg">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">

<style>
.pay-stats-row {
  display: flex;
  gap: 16px;
  margin-bottom: 24px;
  flex-wrap: wrap;
}
.pay-stat-card {
  display: flex;
  align-items: center;
  gap: 16px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--r-lg);
  padding: 18px 24px;
  flex: 1;
  min-width: 200px;
  box-shadow: var(--shadow-sm);
  transition: transform 0.2s, box-shadow 0.2s;
}
.pay-stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
.pay-stat-icon { font-size: 2rem; line-height: 1; }
.pay-stat-info span {
  display: block;
  font-size: 1.8rem;
  font-weight: 700;
  color: var(--brand);
  line-height: 1;
}
.pay-stat-info p {
  font-size: 0.78rem;
  color: var(--text-3);
  margin-top: 4px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.pay-filter-bar {
  display: flex;
  gap: 12px;
  margin-bottom: 24px;
  flex-wrap: wrap;
}
.pay-filter-bar input {
  flex: 1;
  min-width: 240px;
  padding: 11px 16px;
  border: 1px solid var(--border);
  border-radius: var(--r-md);
  background: var(--surface);
  font-family: 'DM Sans', sans-serif;
  font-size: 0.875rem;
  color: var(--text-1);
  outline: none;
  transition: border-color 0.2s;
}
.pay-filter-bar input:focus { border-color: var(--brand); }
.pay-filter-bar select {
  appearance: none;
  padding: 11px 42px 11px 16px;
  border: 1px solid var(--border);
  border-radius: var(--r-md);
  background: var(--surface) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23bc6c25' stroke-width='1.8' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat right 14px center;
  font-family: 'DM Sans', sans-serif;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-1);
  cursor: pointer;
  outline: none;
  min-width: 200px;
  transition: border-color 0.2s;
}
.pay-filter-bar select:focus { border-color: var(--brand); }

.payment-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 20px;
}
.pay-empty {
  grid-column: 1 / -1;
  text-align: center;
  padding: 48px 24px;
  color: var(--text-3);
  font-size: 0.95rem;
}

.pay-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--r-xl);
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  transition: transform 0.22s, box-shadow 0.22s;
  display: flex;
  flex-direction: column;
}
.pay-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
.pay-card.confirmed-card { border-color: rgba(39,174,96,0.25); }

.pay-card-accent { height: 4px; background: linear-gradient(90deg, var(--brand), #e07b2a); }
.pay-card-accent.confirmed { background: linear-gradient(90deg, #27ae60, #2ecc71); }

.pay-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 18px 20px 12px;
  border-bottom: 1px solid var(--border);
  gap: 10px;
}
.pay-card-name { font-size: 1rem; font-weight: 700; color: var(--text-1); margin: 0 0 3px 0; }
.pay-card-ticket { font-size: 0.72rem; color: var(--text-3); font-weight: 500; letter-spacing: 0.3px; }

.pay-status-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 12px;
  border-radius: 99px;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.4px;
  text-transform: uppercase;
  white-space: nowrap;
  flex-shrink: 0;
}
.pay-status-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.pay-status-badge.pending   { background: rgba(230,126,34,0.12); color: var(--pending-c); }
.pay-status-badge.confirmed { background: rgba(39,174,96,0.12);  color: var(--completed-c); }

.pay-card-body {
  padding: 16px 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  flex: 1;
}
.pay-info-row { display: flex; justify-content: space-between; align-items: center; font-size: 0.84rem; gap: 8px; }
.pay-info-label { color: var(--text-3); font-weight: 500; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.4px; white-space: nowrap; flex-shrink: 0; }
.pay-info-value { color: var(--text-1); font-weight: 600; text-align: right; word-break: break-word; }
.pay-info-value.ref {
  font-family: 'Courier New', monospace;
  font-size: 0.82rem;
  background: var(--surface-2);
  padding: 2px 8px;
  border-radius: 6px;
  color: var(--brand-dk);
  letter-spacing: 0.5px;
}
.pay-divider { height: 1px; background: var(--border); margin: 4px 0; }

.pay-proof-area { padding: 0 20px 16px; }
.pay-proof-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-3); font-weight: 600; margin-bottom: 8px; }
.pay-proof-thumb {
  width: 100%; height: 160px; object-fit: cover;
  border-radius: var(--r-md); border: 1px solid var(--border);
  cursor: zoom-in; display: block;
  transition: opacity 0.2s, transform 0.2s;
}
.pay-proof-thumb:hover { opacity: 0.88; transform: scale(1.01); }
.pay-no-proof {
  display: flex; align-items: center; justify-content: center;
  height: 90px; background: var(--surface-2);
  border-radius: var(--r-md); border: 1px dashed var(--border);
  color: var(--text-3); font-size: 0.82rem;
}

.pay-card-actions { display: flex; gap: 8px; padding: 14px 20px 18px; border-top: 1px solid var(--border); }
.pay-btn {
  flex: 1; padding: 10px 14px; border: none;
  border-radius: var(--r-md); font-family: 'DM Sans', sans-serif;
  font-size: 0.8rem; font-weight: 700; cursor: pointer;
  letter-spacing: 0.3px; transition: opacity 0.15s, transform 0.15s;
}
.pay-btn:hover:not(:disabled)  { opacity: 0.85; transform: translateY(-1px); }
.pay-btn:active:not(:disabled) { transform: scale(0.97); }
.pay-btn:disabled  { opacity: 0.38; cursor: not-allowed; transform: none !important; }
.pay-btn.confirm   { background: var(--brand); color: #fff; }
.pay-btn.view-proof { background: var(--surface-2); color: var(--text-2); border: 1px solid var(--border); }

.proof-modal { position: fixed; inset: 0; z-index: 9000; display: flex; align-items: center; justify-content: center; }
.proof-modal-backdrop { position: absolute; inset: 0; background: rgba(0,0,0,0.72); backdrop-filter: blur(4px); }
.proof-modal-box {
  position: relative; background: var(--surface); border-radius: var(--r-xl);
  padding: 28px 28px 24px; max-width: 600px; width: 92%; max-height: 88vh;
  overflow-y: auto; box-shadow: 0 24px 60px rgba(0,0,0,0.35);
  animation: modalIn 0.25s ease-out; z-index: 1;
}
@keyframes modalIn {
  from { opacity: 0; transform: scale(0.92) translateY(20px); }
  to   { opacity: 1; transform: scale(1) translateY(0); }
}
.proof-modal-box h3 { font-family: 'Syne', sans-serif; font-size: 1.1rem; font-weight: 700; color: var(--text-1); margin-bottom: 16px; padding-right: 36px; }
.proof-modal-box img { width: 100%; border-radius: var(--r-md); border: 1px solid var(--border); display: block; }
.proof-close-btn {
  position: absolute; top: 18px; right: 18px;
  background: var(--surface-2); border: none; border-radius: 50%;
  width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
  font-size: 0.9rem; cursor: pointer; color: var(--text-2);
  transition: background 0.2s, color 0.2s;
}
.proof-close-btn:hover { background: var(--brand); color: #fff; }

.pay-toast {
  position: fixed; bottom: 28px; right: 28px;
  background: var(--forest); color: #fff;
  padding: 14px 20px; border-radius: var(--r-md);
  font-size: 0.875rem; font-weight: 500;
  z-index: 9999; box-shadow: var(--shadow-lg);
  animation: toastIn 0.3s ease-out; max-width: 320px;
}
.pay-toast.success { border-left: 4px solid #27ae60; }
.pay-toast.error   { border-left: 4px solid #e74c3c; }
@keyframes toastIn {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}

@media (max-width: 700px) {
  .payment-grid  { grid-template-columns: 1fr; }
  .pay-stats-row { flex-direction: column; }
}
</style>
</head>
<body>

<aside class="sidebar">
  <div class="sidebar-logo">
    <img src="../IMAGES/logo.jpg" alt="Cubiertos logo">
    <h2>Cubiertos</h2>
  </div>
  <ul class="sidebar-menu">
    <li><a href="admin.php"><span class="nav-icon">⊞</span><span class="nav-label">Dashboard</span></a></li>
    <li><a href="revenue.php"><span class="nav-icon">₱</span><span class="nav-label">Revenue</span></a></li>
    <li><a href="calendar.php"><span class="nav-icon">◫</span><span class="nav-label">Calendar</span></a></li>
    <li><a href="customer.php"><span class="nav-icon">◎</span><span class="nav-label">Customers</span></a></li>
    <li class="active"><a href="payment_admin.php"><span class="nav-icon">📲</span><span class="nav-label">Payments</span></a></li>
    <li><a href="report.php"><span class="nav-icon">▤</span><span class="nav-label">Reports</span></a></li>
  </ul>
  <div class="sidebar-footer">
    <p>Cubiertos Food Hub &copy; 2025</p>
  </div>
</aside>

<header id="adminHeader">
  <div class="logo">
    <h1><span>GCash</span> Payment Verification</h1>
  </div>
  <nav>
    <a href="admin.php">Dashboard</a>
    <a href="logout.php" class="btn logout">Logout</a>
  </nav>
</header>

<main class="dashboard-container">

  <div class="pay-stats-row">
    <div class="pay-stat-card">
      <div class="pay-stat-icon">⏳</div>
      <div class="pay-stat-info">
        <span id="stat_pending">0</span>
        <p>Pending GCash</p>
      </div>
    </div>
    <div class="pay-stat-card">
      <div class="pay-stat-icon">✅</div>
      <div class="pay-stat-info">
        <span id="stat_confirmed">0</span>
        <p>Confirmed Payments</p>
      </div>
    </div>
    <div class="pay-stat-card">
      <div class="pay-stat-icon">💰</div>
      <div class="pay-stat-info">
        <span id="stat_total">₱0</span>
        <p>Total GCash Revenue</p>
      </div>
    </div>
  </div>

  <div class="pay-filter-bar">
    <input type="text" id="paySearch" placeholder="🔍  Search by name or reference number…">
    <select id="payFilter">
      <option value="Pending">Pending Verification</option>
      <option value="Confirmed">Confirmed</option>
      <option value="all">All GCash Payments</option>
    </select>
  </div>

  <!-- Static empty message — never removed from DOM -->
  <p class="pay-empty" id="payEmpty" style="display:none;">No payments found.</p>

  <div id="paymentGrid" class="payment-grid"></div>

</main>

<div id="proofModal" class="proof-modal" style="display:none;">
  <div class="proof-modal-backdrop" onclick="closeModal()"></div>
  <div class="proof-modal-box">
    <button class="proof-close-btn" onclick="closeModal()">✕</button>
    <h3 id="modalTitle">Payment Screenshot</h3>
    <img id="modalImg" src="" alt="Payment Proof">
  </div>
</div>

<script>

let allPayments   = [];
let autoRefreshId = null;

// ── ESCAPE HTML ───────────────────────────────────────
function esc(str) {
  if (str === null || str === undefined) return "";
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
}

// ── FETCH from server ─────────────────────────────────
// Does NOT call renderCards() directly — only updates data + stats,
// then re-applies the current filter so search/dropdown state is preserved.
async function fetchPayments() {
  try {
    const res  = await fetch("payment_admin.php?action=list");
    const data = await res.json();
    if (data.success) {
      allPayments = data.payments;
      renderStats();
      renderCards(); // safe — reads live values from inputs
    }
  } catch (err) {
    // Silent fail on background refresh — don't disrupt the UI
  }
}

// ── STATS ─────────────────────────────────────────────
function renderStats() {
  const pending   = allPayments.filter(p => p.payment_status === "Pending").length;
  const confirmed = allPayments.filter(p => p.payment_status === "Confirmed").length;
  const total     = allPayments
    .filter(p => p.payment_status === "Confirmed")
    .reduce((sum, p) => sum + Number(p.amount || 0), 0);

  document.getElementById("stat_pending").textContent   = pending;
  document.getElementById("stat_confirmed").textContent = confirmed;
  document.getElementById("stat_total").textContent     = "₱" + total.toLocaleString();
}

// ── RENDER CARDS ──────────────────────────────────────
// Reads search + filter live from the DOM so it always reflects
// what the user has currently typed/selected.
function renderCards() {
  const grid      = document.getElementById("paymentGrid");
  const emptyMsg  = document.getElementById("payEmpty");
  const searchVal = document.getElementById("paySearch").value.toLowerCase().trim();
  const filterVal = document.getElementById("payFilter").value;

  // Filter the master list
  const filtered = allPayments.filter(p => {
    // Status filter
    if (filterVal !== "all" && p.payment_status !== filterVal) return false;
    // Search filter
    if (searchVal) {
      const name = (p.name            || "").toLowerCase();
      const ref  = (p.gcash_reference || "").toLowerCase();
      const tick = (p.ticket_number   || "").toLowerCase();
      if (!name.includes(searchVal) && !ref.includes(searchVal) && !tick.includes(searchVal)) return false;
    }
    return true;
  });

  // Clear only the cards grid, NOT the emptyMsg (it lives outside the grid)
  grid.innerHTML = "";

  if (filtered.length === 0) {
    emptyMsg.style.display = "block";
    emptyMsg.textContent   = allPayments.length === 0
      ? "No GCash payments recorded yet."
      : "No payments match your current filter.";
  } else {
    emptyMsg.style.display = "none";
    filtered.forEach(p => grid.appendChild(buildCard(p)));
  }
}

// ── BUILD CARD ─────────────────────────────────────────
function buildCard(p) {
  const isConfirmed = p.payment_status === "Confirmed";

  const card = document.createElement("div");
  card.className  = "pay-card" + (isConfirmed ? " confirmed-card" : "");
  card.dataset.id = p.id;

  const fmtDate = raw => {
    if (!raw) return "—";
    return new Date(raw).toLocaleDateString("en-PH", {
      year: "numeric", month: "short", day: "numeric",
      hour: "2-digit", minute: "2-digit"
    });
  };

  // Proof image — use encoded src to avoid XSS in onclick
  let proofHTML;
  if (p.proof_path) {
    const safeSrc  = esc(p.proof_path);
    const safeName = esc(p.name);
    proofHTML = `
      <div class="pay-proof-area">
        <p class="pay-proof-label">Payment Screenshot</p>
        <img class="pay-proof-thumb"
             src="${safeSrc}"
             alt="Payment proof"
             data-src="${safeSrc}"
             data-name="${safeName}"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
        <div class="pay-no-proof" style="display:none;">Image not found on server</div>
      </div>`;
  } else {
    proofHTML = `
      <div class="pay-proof-area">
        <p class="pay-proof-label">Payment Screenshot</p>
        <div class="pay-no-proof">No screenshot uploaded</div>
      </div>`;
  }

  const confirmBtn = isConfirmed
    ? `<button class="pay-btn confirm" disabled>✓ Confirmed</button>`
    : `<button class="pay-btn confirm" data-id="${p.id}">Confirm Payment</button>`;

  const proofBtn = p.proof_path
    ? `<button class="pay-btn view-proof" data-src="${esc(p.proof_path)}" data-name="${esc(p.name)}">🔍 View Proof</button>`
    : `<button class="pay-btn view-proof" disabled>No Proof</button>`;

  card.innerHTML = `
    <div class="pay-card-accent ${isConfirmed ? 'confirmed' : ''}"></div>
    <div class="pay-card-header">
      <div>
        <h3 class="pay-card-name">${esc(p.name || "—")}</h3>
        <span class="pay-card-ticket">Ticket #${esc(p.ticket_number || String(p.id))}</span>
      </div>
      <span class="pay-status-badge ${isConfirmed ? 'confirmed' : 'pending'}">
        ${isConfirmed ? "Confirmed" : "Pending"}
      </span>
    </div>
    <div class="pay-card-body">
      <div class="pay-info-row">
        <span class="pay-info-label">Email</span>
        <span class="pay-info-value">${esc(p.email || "—")}</span>
      </div>
      <div class="pay-info-row">
        <span class="pay-info-label">Phone</span>
        <span class="pay-info-value">${esc(p.phone || "—")}</span>
      </div>
      <div class="pay-divider"></div>
      <div class="pay-info-row">
        <span class="pay-info-label">GCash Name</span>
        <span class="pay-info-value">${esc(p.gcash_name || "—")}</span>
      </div>
      <div class="pay-info-row">
        <span class="pay-info-label">GCash Number</span>
        <span class="pay-info-value">${esc(p.gcash_number || "—")}</span>
      </div>
      <div class="pay-info-row">
        <span class="pay-info-label">Reference #</span>
        <span class="pay-info-value ref">${esc(p.gcash_reference || "—")}</span>
      </div>
      <div class="pay-divider"></div>
      <div class="pay-info-row">
        <span class="pay-info-label">Occasion</span>
        <span class="pay-info-value">${esc(p.occasion || "—")}</span>
      </div>
      <div class="pay-info-row">
        <span class="pay-info-label">Guests</span>
        <span class="pay-info-value">${esc(p.guests || "—")} pax</span>
      </div>
      <div class="pay-info-row">
        <span class="pay-info-label">Package</span>
        <span class="pay-info-value">${esc(p.package || "—")}</span>
      </div>
      <div class="pay-info-row">
        <span class="pay-info-label">Amount</span>
        <span class="pay-info-value" style="color:var(--brand);font-weight:700;">
          ₱${Number(p.amount || 0).toLocaleString()}
        </span>
      </div>
      <div class="pay-info-row">
        <span class="pay-info-label">Event Date</span>
        <span class="pay-info-value">${fmtDate(p.booking_datetime)}</span>
      </div>
      <div class="pay-info-row">
        <span class="pay-info-label">Booking Status</span>
        <span class="pay-info-value">${esc(p.booking_status || "Pending")}</span>
      </div>
      <div class="pay-info-row">
        <span class="pay-info-label">Submitted</span>
        <span class="pay-info-value">${fmtDate(p.created_at)}</span>
      </div>
    </div>
    ${proofHTML}
    <div class="pay-card-actions">
      ${confirmBtn}
      ${proofBtn}
    </div>
  `;

  return card;
}

// ── EVENT DELEGATION — confirm + view proof buttons ───
// Using delegation on the grid means we never lose listeners
// when cards are re-rendered by the auto-refresh.
document.getElementById("paymentGrid").addEventListener("click", function(e) {
  const btn = e.target.closest("button");
  if (!btn) return;

  // Confirm payment
  if (btn.classList.contains("confirm") && btn.dataset.id) {
    confirmPayment(parseInt(btn.dataset.id, 10));
    return;
  }

  // View proof
  if (btn.classList.contains("view-proof") && btn.dataset.src) {
    openModal(btn.dataset.src, btn.dataset.name);
    return;
  }

  // Click on proof thumbnail
  const thumb = e.target.closest(".pay-proof-thumb");
  if (thumb && thumb.dataset.src) {
    openModal(thumb.dataset.src, thumb.dataset.name);
  }
});

// ── CONFIRM PAYMENT ───────────────────────────────────
async function confirmPayment(id) {
  if (!confirm("Confirm this GCash payment? The booking will also be approved.")) return;
  try {
    const res  = await fetch("payment_admin.php?action=confirm", {
      method:  "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body:    `id=${id}`
    });
    const data = await res.json();
    if (data.success) {
      showToast("✅ Payment confirmed and booking approved!", "success");
      fetchPayments();
    } else {
      showToast("❌ " + (data.message || "Failed to confirm."), "error");
    }
  } catch (err) {
    showToast("❌ Network error. Try again.", "error");
  }
}

// ── MODAL ─────────────────────────────────────────────
function openModal(src, name) {
  document.getElementById("modalImg").src           = src;
  document.getElementById("modalTitle").textContent = (name || "Customer") + " — Payment Proof";
  document.getElementById("proofModal").style.display = "flex";
}

function closeModal() {
  document.getElementById("proofModal").style.display = "none";
  document.getElementById("modalImg").src = "";
}

window.closeModal = closeModal;
document.addEventListener("keydown", e => { if (e.key === "Escape") closeModal(); });

// ── SEARCH + FILTER — live, no debounce needed ────────
document.getElementById("paySearch").addEventListener("input",  renderCards);
document.getElementById("payFilter").addEventListener("change", renderCards);

// ── TOAST ─────────────────────────────────────────────
function showToast(msg, type = "success") {
  const t = document.createElement("div");
  t.className   = `pay-toast ${type}`;
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 3800);
}

// ── INIT ──────────────────────────────────────────────
fetchPayments();

// Auto-refresh every 60s (increased from 30s to reduce flicker).
// Does NOT reset search/filter — renderCards() reads inputs live.
autoRefreshId = setInterval(fetchPayments, 60000);

</script>
</body>
</html>