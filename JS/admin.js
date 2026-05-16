// ================= GLOBAL STATE =================
let orders         = [];
let upcomingEvents = []; // server-pre-filtered upcoming events (next 7 days, Pending/Approved)
let notificationHistory = [];

const ordersTable = document.querySelector("#ordersTable tbody");

// ================= FETCH BOOKINGS FROM DATABASE =================
async function fetchOrders() {
  try {
    const response = await fetch("admin_bookings.php?action=list");
    const data = await response.json();

    if (data.success) {
      orders         = data.bookings       || [];
      // Use server-pre-filtered upcoming events when available (PHP does the heavy lifting)
      // Falls back to all orders so client-side filter still works
      upcomingEvents = data.upcomingEvents || orders;

      loadOrders();
      renderUpcomingEvents();
      checkEventNotifications();
    } else {
      console.error("Failed to fetch bookings:", data);
      const container = document.getElementById("upcomingEventsContainer");
      if (container) {
        container.innerHTML = `
          <div class="events-empty">
            <div class="events-empty-icon">⚠️</div>
            <p>Could not load bookings.<br>Check that admin_bookings.php is reachable.</p>
          </div>`;
      }
    }
  } catch (error) {
    console.error("Fetch error:", error);
    const container = document.getElementById("upcomingEventsContainer");
    if (container) {
      container.innerHTML = `
        <div class="events-empty">
          <div class="events-empty-icon">⚠️</div>
          <p>Connection error:<br>${error.message}</p>
        </div>`;
    }
  }
}

// ================= LOAD ORDERS =================
function loadOrders() {
  if (!ordersTable) return;

  ordersTable.innerHTML = "";

  let totalRevenue = 0;
  let pending      = 0;
  let approved     = 0;
  let completed    = 0;
  let cancelled    = 0;
  let totalGuests  = 0;

  const searchValue = document.getElementById("searchOrder")?.value.toLowerCase() || "";
  const filterValue = document.getElementById("filterStatus")?.value || "all";

  let displayIndex = 0;

  orders.forEach((order) => {
    if (order.name && !order.name.toLowerCase().includes(searchValue)) return;

    if (order.status === "Pending")   pending++;
    if (order.status === "Approved")  approved++;
    if (order.status === "Completed") {
      completed++;
      totalRevenue += Number(order.amount || 0);
    }
    if (order.status === "Cancelled") cancelled++;

    if (order.status === "Pending" || order.status === "Approved") {
      if (order.booking_datetime) {
        const today       = new Date();
        const bookingDate = parseBookingDate(order.booking_datetime);
        if (bookingDate && today.toDateString() === bookingDate.toDateString()) {
          totalGuests += Number(order.guests || 0);
        }
      }
    }

    if (filterValue !== "all" && order.status !== filterValue) return;
    if (order.status === "Completed" && filterValue !== "Completed") return;

    let actionButtons = "";

    if (order.status === "Pending") {
      actionButtons = `
        <button class="btn-action btn-approve"
                onclick="approveOrder(${order.id})"
                title="Approve this booking">
          ✔ Approve
        </button>
        <button class="btn-action btn-complete btn-disabled"
                disabled
                title="You must approve the booking before completing it">
          🔒 Complete
        </button>
        <button class="btn-action btn-cancel"
                onclick="cancelOrder(${order.id})"
                title="Cancel this booking">
          ✖ Cancel
        </button>`;

    } else if (order.status === "Approved") {
      actionButtons = `
        <button class="btn-action btn-complete"
                onclick="completeOrder(${order.id})"
                title="Mark this booking as completed">
          ✔ Complete
        </button>
        <button class="btn-action btn-cancel"
                onclick="cancelOrder(${order.id})"
                title="Cancel this booking">
          ✖ Cancel
        </button>`;

    } else {
      actionButtons = `<span class="btn-action btn-done">—</span>`;
    }

    displayIndex++;

    const row = document.createElement("tr");

    row.innerHTML = `
      <td>${displayIndex}</td>
      <td>${order.name || "—"}</td>
      <td>${order.phone || "—"}<br><small>${order.email || ""}</small></td>
      <td>${order.occasion || "—"}</td>
      <td>${order.guests || 0} pax</td>
      <td>₱${Number(order.amount || 0).toLocaleString()}</td>
      <td>${order.payment_method || "—"}</td>
      <td>
        <span class="status ${order.status.toLowerCase()}">
          ${order.status}
        </span>
      </td>
      <td class="action-cell">${actionButtons}</td>
    `;

    ordersTable.appendChild(row);
  });

  if (displayIndex === 0) {
    const emptyRow = document.createElement("tr");
    emptyRow.innerHTML = `
      <td colspan="9" style="text-align:center; padding:24px; color:#999;">
        ${filterValue === "Completed"
          ? "No completed bookings found."
          : "No active bookings found."}
      </td>`;
    ordersTable.appendChild(emptyRow);
  }

  // ================= DASHBOARD STATS =================
  // Guard every getElementById with ?. so missing elements never crash
  const _el = (id) => document.getElementById(id);
  if (_el("ov_totalBookings"))    _el("ov_totalBookings").textContent    = orders.length;
  if (_el("ov_pendingBookings"))  _el("ov_pendingBookings").textContent  = pending;
  if (_el("ov_approvedBookings")) _el("ov_approvedBookings").textContent = approved;
  if (_el("ov_completedBookings"))_el("ov_completedBookings").textContent= completed;
  if (_el("ov_cancelledBookings"))_el("ov_cancelledBookings").textContent= cancelled;
  if (_el("ov_totalRevenue"))     _el("ov_totalRevenue").textContent     = totalRevenue.toLocaleString();

  if (_el("ov_pendingFlow"))   _el("ov_pendingFlow").textContent   = pending;
  if (_el("ov_approvedFlow"))  _el("ov_approvedFlow").textContent  = approved;
  if (_el("ov_completedFlow")) _el("ov_completedFlow").textContent = completed;

  // ================= CAPACITY =================
  let maxCap = parseInt(localStorage.getItem("dailyCapacity")) || 100;
  if (_el("maxCapacity"))    _el("maxCapacity").textContent    = maxCap;
  if (_el("currentBooked"))  _el("currentBooked").textContent  = totalGuests;
  if (_el("slotsAvailable")) _el("slotsAvailable").textContent = maxCap - totalGuests;

  const fillEl = _el("capacityFill");
  if (fillEl) {
    let percentage = (totalGuests / maxCap) * 100;
    if (percentage > 100) percentage = 100;
    fillEl.style.width = percentage + "%";
  }
}


// ================= UPDATE STATUS =================
async function updateStatus(id, status) {
  try {
    await fetch("admin_bookings.php?action=update_status", {
      method:  "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body:    `id=${id}&status=${status}`,
    });
    fetchOrders();
  } catch (error) {
    console.error(error);
  }
}

// ================= BUTTON FUNCTIONS =================
window.approveOrder = function (id) {
  if (!confirm("Approve this booking?")) return;
  updateStatus(id, "Approved");
};

window.completeOrder = function (id) {
  const order = orders.find(o => String(o.id) === String(id));
  if (!order) return;

  if (order.status !== "Approved") {
    showActionToast(
      "⚠️ This booking must be Approved before it can be Completed.",
      "warning"
    );
    return;
  }

  if (!confirm("Mark this booking as Completed?")) return;
  updateStatus(id, "Completed");
};

window.cancelOrder = function (id) {
  if (!confirm("Cancel this booking?")) return;
  updateStatus(id, "Cancelled");
};

// ================= TOAST NOTIFICATION =================
function showActionToast(msg, type = "info") {
  document.querySelectorAll(".action-toast").forEach(t => t.remove());

  const toast = document.createElement("div");
  toast.className   = "action-toast action-toast--" + type;
  toast.textContent = msg;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 4000);
}

// ================= SEARCH/FILTER =================
document.getElementById("searchOrder")?.addEventListener("input",  loadOrders);
document.getElementById("filterStatus")?.addEventListener("change", loadOrders);

// ================= CAPACITY =================
window.setNewLimit = function () {
  let currentLimit = parseInt(localStorage.getItem("dailyCapacity")) || 100;
  let input        = prompt("Enter new maximum guests per day:", currentLimit);
  if (input !== null) {
    let newLimit = parseInt(input);
    if (!isNaN(newLimit) && newLimit > 0) {
      localStorage.setItem("dailyCapacity", newLimit);
      loadOrders();
    }
  }
};

// ================= CALENDAR =================
let nav = 0;

function renderCalendar() {
  const calendarGrid  = document.getElementById("calendarGrid");
  const monthDisplay  = document.getElementById("monthDisplay");
  if (!calendarGrid || !monthDisplay) return;

  const dt = new Date();
  if (nav !== 0) dt.setMonth(new Date().getMonth() + nav);

  const month            = dt.getMonth();
  const year             = dt.getFullYear();
  const firstDayOfMonth  = new Date(year, month, 1);
  const daysInMonth      = new Date(year, month + 1, 0).getDate();
  const paddingDays      = firstDayOfMonth.getDay();

  monthDisplay.textContent = dt.toLocaleDateString("en-us", {
    month: "long",
    year:  "numeric",
  });

  calendarGrid.innerHTML = "";

  const ordersByDate = {};
  orders.forEach((order) => {
    if (order.booking_datetime) {
      const orderDate  = parseBookingDate(order.booking_datetime);
      if (!orderDate) return;
      const dateKey    = `${orderDate.getFullYear()}-${orderDate.getMonth()}-${orderDate.getDate()}`;
      if (!ordersByDate[dateKey]) ordersByDate[dateKey] = [];
      ordersByDate[dateKey].push(order);
    }
  });

  for (let i = 1; i <= paddingDays + daysInMonth; i++) {
    const daySquare = document.createElement("div");
    if (i > paddingDays) {
      const dayNumber         = i - paddingDays;
      daySquare.textContent   = dayNumber;
      const currentSquareKey  = `${year}-${month}-${dayNumber}`;
      const daysBookings      = ordersByDate[currentSquareKey];
      if (daysBookings && daysBookings.length > 0) {
        const indicator       = document.createElement("div");
        indicator.textContent = `● ${daysBookings.length}`;
        indicator.style.fontSize  = "12px";
        indicator.style.marginTop = "5px";
        daySquare.appendChild(indicator);
      }
    }
    calendarGrid.appendChild(daySquare);
  }
}

// ================= HELPERS =================

// Fix MySQL "YYYY-MM-DD HH:MM:SS" → JS-safe "YYYY-MM-DDTHH:MM:SS"
// Without this, new Date("2025-06-01 18:00:00") returns Invalid Date in Safari/Firefox
function parseBookingDate(str) {
  if (!str) return null;
  const fixed = str.replace(" ", "T"); // "2025-06-01 18:00:00" → "2025-06-01T18:00:00"
  const dt = new Date(fixed);
  return isNaN(dt.getTime()) ? null : dt;
}

// ================= UPCOMING EVENTS PANEL =================

function renderUpcomingEvents() {
  const container = document.getElementById("upcomingEventsContainer");
  if (!container) return;

  const now = new Date();

  // Use the server-pre-filtered list (Pending/Approved, ±3 to +7 days)
  // Then sort by booking_datetime ascending
  const upcoming = upcomingEvents
    .filter(o => {
      const dt = parseBookingDate(o.booking_datetime);
      if (!dt) return false;
      if (o.status === "Completed" || o.status === "Cancelled") return false;
      return true; // server already filtered date range
    })
    .sort((a, b) => parseBookingDate(a.booking_datetime) - parseBookingDate(b.booking_datetime));

  // Update badge
  const badge = document.getElementById("upcomingBadge");
  if (badge) {
    badge.textContent = upcoming.length;
    badge.style.display = upcoming.length > 0 ? "flex" : "none";
  }

  container.innerHTML = "";

  if (upcoming.length === 0) {
    container.innerHTML = `
      <div class="events-empty">
        <div class="events-empty-icon">📅</div>
        <p>No upcoming events in the next 7 days</p>
      </div>`;
    return;
  }

  upcoming.forEach(order => {
    const dt        = parseBookingDate(order.booking_datetime);
    const diffMs    = dt - now;
    const diffMins  = Math.round(diffMs / 60000);
    const diffHours = Math.round(diffMs / 3600000);

    let timeLabel  = "";
    let timeClass  = "";
    let eventState = "upcoming"; // upcoming | starting-soon | ongoing | past

    if (diffMins < 0) {
      // Event is in the past (within today's window)
      timeLabel  = `Started ${Math.abs(diffMins)} min ago`;
      timeClass  = "time-past";
      eventState = "ongoing";
    } else if (diffMins <= 30) {
      timeLabel  = diffMins <= 5 ? "Starting now!" : `In ${diffMins} min`;
      timeClass  = "time-imminent";
      eventState = "starting-soon";
    } else if (diffHours < 24) {
      timeLabel  = `In ${diffHours}h ${diffMins % 60}m`;
      timeClass  = "time-today";
      eventState = "today";
    } else {
      const days = Math.ceil(diffMs / (1000 * 60 * 60 * 24));
      timeLabel  = `In ${days} day${days > 1 ? "s" : ""}`;
      timeClass  = "time-future";
      eventState = "upcoming";
    }

    const formattedDate = dt.toLocaleDateString("en-PH", {
      weekday: "short", month: "short", day: "numeric"
    });
    const formattedTime = dt.toLocaleTimeString("en-PH", {
      hour: "2-digit", minute: "2-digit"
    });

    const card = document.createElement("div");
    card.className = `event-card event-state-${eventState}`;
    card.innerHTML = `
      <div class="event-card-header">
        <div class="event-time-badge ${timeClass}">${timeLabel}</div>
        <span class="event-status-dot status-dot-${order.status.toLowerCase()}"></span>
      </div>
      <div class="event-card-body">
        <div class="event-name">${order.name || "—"}</div>
        <div class="event-occasion">🎉 ${order.occasion || "General Event"}</div>
        <div class="event-meta">
          <span>👥 ${order.guests || 0} guests</span>
          <span>₱${Number(order.amount || 0).toLocaleString()}</span>
        </div>
        <div class="event-datetime">
          <span class="event-date-str">📆 ${formattedDate}</span>
          <span class="event-time-str">🕐 ${formattedTime}</span>
        </div>
      </div>
      <div class="event-card-footer">
        <span class="event-booking-status ${order.status.toLowerCase()}">${order.status}</span>
        ${order.status === "Pending" ? `<button class="evt-btn evt-approve" onclick="approveOrder(${order.id})">Approve</button>` : ""}
        ${order.status === "Approved" ? `<button class="evt-btn evt-complete" onclick="completeOrder(${order.id})">Complete</button>` : ""}
      </div>
    `;
    container.appendChild(card);
  });
}

// ================= EVENT NOTIFICATIONS =================

function checkEventNotifications() {
  const now = new Date();

  orders.forEach(order => {
    if (!order.booking_datetime) return;
    if (order.status === "Cancelled") return;

    const dt       = parseBookingDate(order.booking_datetime);
    if (!dt) return;
    const diffMins = (dt - now) / 60000;
    const key30    = `notif-30-${order.id}`;
    const key0     = `notif-0-${order.id}`;
    const keyDone  = `notif-done-${order.id}`;

    // 30 minutes before
    if (diffMins > 0 && diffMins <= 30 && !notificationHistory.includes(key30)) {
      notificationHistory.push(key30);
      addNotification({
        type:    "warning",
        icon:    "⏰",
        title:   "Event Starting Soon",
        message: `${order.name}'s ${order.occasion || "booking"} starts in ~${Math.round(diffMins)} min`,
        orderId: order.id,
      });
    }

    // Event time reached (within 5 min window)
    if (diffMins >= -5 && diffMins <= 5 && !notificationHistory.includes(key0)) {
      notificationHistory.push(key0);
      addNotification({
        type:    "info",
        icon:    "🎉",
        title:   "Event Starting Now",
        message: `${order.name}'s ${order.occasion || "event"} is beginning now!`,
        orderId: order.id,
      });
    }

    // Event ended (60 min after start, and still Approved)
    if (diffMins <= -60 && order.status === "Approved" && !notificationHistory.includes(keyDone)) {
      notificationHistory.push(keyDone);
      addNotification({
        type:    "success",
        icon:    "✅",
        title:   "Event Likely Finished",
        message: `${order.name}'s event may have ended. Mark as Completed?`,
        orderId: order.id,
        action:  { label: "Mark Complete", fn: () => completeOrder(order.id) },
      });
    }
  });
}

function addNotification({ type, icon, title, message, orderId, action }) {
  const panel = document.getElementById("notifList");
  if (!panel) return;

  // Remove "no notifications" placeholder
  const placeholder = panel.querySelector(".notif-placeholder");
  if (placeholder) placeholder.remove();

  // Update bell badge
  updateNotifBadge(1);

  const item = document.createElement("div");
  item.className = `notif-item notif-${type}`;
  item.dataset.orderId = orderId;
  item.innerHTML = `
    <div class="notif-icon">${icon}</div>
    <div class="notif-body">
      <div class="notif-title">${title}</div>
      <div class="notif-msg">${message}</div>
      <div class="notif-time">${new Date().toLocaleTimeString("en-PH", { hour: "2-digit", minute: "2-digit" })}</div>
      ${action ? `<button class="notif-action-btn" onclick="${action.fn.toString().replace(/"/g, "'")}">${action.label}</button>` : ""}
    </div>
    <button class="notif-dismiss" onclick="dismissNotif(this)" title="Dismiss">✕</button>
  `;

  // Action button workaround (closures don't survive innerHTML)
  if (action) {
    const btn = item.querySelector(".notif-action-btn");
    if (btn) btn.addEventListener("click", action.fn);
  }

  panel.prepend(item);

  // Also show a floating toast
  showEventToast({ type, icon, title, message });
}

let notifCount = 0;
function updateNotifBadge(delta) {
  notifCount = Math.max(0, notifCount + delta);
  const badge = document.getElementById("notifBadge");
  if (!badge) return;
  badge.textContent = notifCount;
  badge.style.display = notifCount > 0 ? "flex" : "none";
}

window.dismissNotif = function(btn) {
  const item = btn.closest(".notif-item");
  if (item) {
    item.style.animation = "notifFadeOut 0.3s ease forwards";
    setTimeout(() => {
      item.remove();
      updateNotifBadge(-1);
      const panel = document.getElementById("notifList");
      if (panel && panel.children.length === 0) {
        panel.innerHTML = `<div class="notif-placeholder">No new notifications</div>`;
      }
    }, 300);
  }
};

window.clearAllNotifications = function() {
  const panel = document.getElementById("notifList");
  if (!panel) return;
  panel.innerHTML = `<div class="notif-placeholder">No new notifications</div>`;
  notifCount = 0;
  updateNotifBadge(0);
};

function showEventToast({ type, icon, title, message }) {
  const toast = document.createElement("div");
  toast.className = `event-toast event-toast--${type}`;
  toast.innerHTML = `
    <div class="et-icon">${icon}</div>
    <div class="et-content">
      <strong>${title}</strong>
      <span>${message}</span>
    </div>
    <button onclick="this.parentElement.remove()">✕</button>
  `;
  document.body.appendChild(toast);
  setTimeout(() => {
    toast.style.animation = "toastSlideOut 0.4s ease forwards";
    setTimeout(() => toast.remove(), 400);
  }, 6000);
}

// Toggle notification panel
window.toggleNotifPanel = function() {
  const panel = document.getElementById("notifDropdown");
  if (!panel) return;
  panel.classList.toggle("open");
};

// Toggle upcoming events panel
window.toggleEventsPanel = function() {
  const panel = document.getElementById("eventsPanel");
  if (!panel) return;
  panel.classList.toggle("collapsed");
};

// Close panels when clicking outside
document.addEventListener("click", function(e) {
  const dropdown = document.getElementById("notifDropdown");
  const bell     = document.getElementById("notifBell");
  if (dropdown && !dropdown.contains(e.target) && bell && !bell.contains(e.target)) {
    dropdown.classList.remove("open");
  }
});

// ================= START =================
fetchOrders();
// Re-check notifications every 60 seconds
setInterval(() => {
  fetchOrders();
}, 60000);

// ================= LOADER (RUN ONCE PER SESSION) =================
window.addEventListener("load", function () {
  const loader = document.getElementById("startup-loader");
  if (loader) {
    if (!sessionStorage.getItem("hasSeenLoader")) {
      loader.style.display = "flex";
      sessionStorage.setItem("hasSeenLoader", "true");
      setTimeout(() => { loader.style.display = "none"; }, 2000);
    } else {
      loader.style.display = "none";
    }
  }
});

// ================= SPECIAL NOTES =================
async function loadSpecialNotes() {
  const container = document.getElementById("notesContainer");
  const search    = document.getElementById("searchNotes").value.toLowerCase();

  try {
    const response = await fetch("fetch_notes.php");
    const notes    = await response.json();

    container.innerHTML = "";

    if (notes.length === 0) {
      container.innerHTML = `<p class="empty-note">No special notes found.</p>`;
      return;
    }

    let hasResult = false;

    notes.forEach((note) => {
      const customer = (note.name          || "").toLowerCase();
      const message  = (note.special_notes || "").toLowerCase();
      if (!customer.includes(search) && !message.includes(search)) return;

      hasResult = true;
      container.innerHTML += `
        <div class="note-box">
          <div class="note-top">
            <h3>${note.name}</h3>
            <span>${new Date(note.booking_datetime).toLocaleString()}</span>
          </div>
          <div class="note-body">
            <p><strong>Occasion:</strong> ${note.occasion || "—"}</p>
            <p><strong>Guests:</strong> ${note.guests || 0}</p>
            <p><strong>Status:</strong> ${note.status || "Pending"}</p>
            <div class="special-message">
              ${note.special_notes || "No special note"}
            </div>
          </div>
        </div>`;
    });

    if (!hasResult) {
      container.innerHTML = `<p class="empty-note">No matching notes found.</p>`;
    }
  } catch (error) {
    console.log(error);
  }
}

document.getElementById("searchNotes")?.addEventListener("input", loadSpecialNotes);
loadSpecialNotes();
setInterval(loadSpecialNotes, 30000);