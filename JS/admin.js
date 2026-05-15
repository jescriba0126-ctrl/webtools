// ================= GLOBAL STATE =================
let orders = [];

const ordersTable = document.querySelector("#ordersTable tbody");

// ================= FETCH BOOKINGS FROM DATABASE =================
async function fetchOrders() {
  try {
    const response = await fetch("admin_bookings.php?action=list");

    const data = await response.json();

    if (data.success) {
      orders = data.bookings;

      loadOrders();
    } else {
      console.error("Failed to fetch bookings");
    }
  } catch (error) {
    console.error("Fetch error:", error);
  }
}

// ================= LOAD ORDERS =================
function loadOrders() {
  if (!ordersTable) return;

  ordersTable.innerHTML = "";

  let totalRevenue = 0;
  let pending = 0;
  let approved = 0;
  let completed = 0;
  let cancelled = 0;
  let totalGuests = 0;

  const searchValue =
    document.getElementById("searchOrder")?.value.toLowerCase() || "";

  const filterValue = document.getElementById("filterStatus")?.value || "all";

  let displayIndex = 0;

  orders.forEach((order) => {
    // SEARCH — skip if name doesn't match
    if (order.name && !order.name.toLowerCase().includes(searchValue)) return;

    // ── COUNT STATS (always, before any display filter) ──────────
    if (order.status === "Pending") pending++;
    if (order.status === "Approved") approved++;
    if (order.status === "Completed") {
      completed++;
      totalRevenue += Number(order.amount || 0);
    }
    if (order.status === "Cancelled") cancelled++;

    // TODAY GUESTS
    if (order.status === "Pending" || order.status === "Approved") {
      if (order.booking_datetime) {
        const today = new Date();
        const bookingDate = new Date(order.booking_datetime);

        if (today.toDateString() === bookingDate.toDateString()) {
          totalGuests += Number(order.guests || 0);
        }
      }
    }

    // ── DISPLAY FILTER (applied after stats are counted) ──────────

    // If a specific status is selected, only show that status
    if (filterValue !== "all" && order.status !== filterValue) return;

    // Hide completed from main table UNLESS user explicitly selects "Completed"
    if (order.status === "Completed" && filterValue !== "Completed") return;

    // ── BUILD TABLE ROW ───────────────────────────────────────────
    displayIndex++;

    const row = document.createElement("tr");

    row.innerHTML = `
      <td>${displayIndex}</td>

      <td>${order.name || "—"}</td>

      <td>${order.phone || "—"}<br>${order.email || ""}</td>

      <td>${order.occasion || "—"}</td>

      <td>${order.guests || 0} pax</td>

      <td>₱${Number(order.amount || 0).toLocaleString()}</td>

      <td>${order.payment_method || "—"}</td>

      <td>
        <span class="status ${order.status.toLowerCase()}">
          ${order.status}
        </span>
      </td>

      <td>
        <button
          class="btn approve"
          onclick="approveOrder(${order.id})"
        >
          Approve Booking
        </button>

        <button
          class="btn completed"
          onclick="completeOrder(${order.id})"
        >
          Complete Booking
        </button>

        <button
          class="btn cancel"
          onclick="cancelOrder(${order.id})"
        >
          Cancel Booking
        </button>
      </td>
    `;

    ordersTable.appendChild(row);
  });

  // ── Show empty message if no rows rendered ────────────────────
  if (displayIndex === 0) {
    const emptyRow = document.createElement("tr");
    emptyRow.innerHTML = `
      <td colspan="9" style="text-align:center; padding: 24px; color: #999;">
        ${
          filterValue === "Completed"
            ? "No completed bookings found."
            : "No active bookings found."
        }
      </td>
    `;
    ordersTable.appendChild(emptyRow);
  }

  // ================= DASHBOARD STATS =================

  document.getElementById("ov_totalBookings").textContent = orders.length;
  document.getElementById("ov_pendingBookings").textContent = pending;
  document.getElementById("ov_approvedBookings").textContent = approved;
  document.getElementById("ov_completedBookings").textContent = completed;
  document.getElementById("ov_cancelledBookings").textContent = cancelled;
  document.getElementById("ov_totalRevenue").textContent =
    totalRevenue.toLocaleString();

  document.getElementById("ov_pendingFlow").textContent = pending;
  document.getElementById("ov_approvedFlow").textContent = approved;
  document.getElementById("ov_completedFlow").textContent = completed;

  // ================= CAPACITY =================

  let maxCap = parseInt(localStorage.getItem("dailyCapacity")) || 100;

  document.getElementById("maxCapacity").textContent = maxCap;
  document.getElementById("currentBooked").textContent = totalGuests;
  document.getElementById("slotsAvailable").textContent = maxCap - totalGuests;

  const fillEl = document.getElementById("capacityFill");

  let percentage = (totalGuests / maxCap) * 100;

  if (percentage > 100) percentage = 100;

  fillEl.style.width = percentage + "%";
}

// ================= UPDATE STATUS =================
async function updateStatus(id, status) {
  try {
    await fetch("admin_bookings.php?action=update_status", {
      method: "POST",

      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },

      body: `id=${id}&status=${status}`,
    });

    fetchOrders();
  } catch (error) {
    console.error(error);
  }
}

// ================= BUTTON FUNCTIONS =================
window.approveOrder = function (id) {
  updateStatus(id, "Approved");
};

window.completeOrder = function (id) {
  updateStatus(id, "Completed");
};

window.cancelOrder = function (id) {
  if (confirm("Cancel this booking?")) {
    updateStatus(id, "Cancelled");
  }
};

// ================= SEARCH/FILTER =================
document.getElementById("searchOrder")?.addEventListener("input", loadOrders);

document.getElementById("filterStatus")?.addEventListener("change", loadOrders);

// ================= CAPACITY =================
window.setNewLimit = function () {
  let currentLimit = parseInt(localStorage.getItem("dailyCapacity")) || 100;

  let input = prompt("Enter new maximum guests per day:", currentLimit);

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
  const calendarGrid = document.getElementById("calendarGrid");

  const monthDisplay = document.getElementById("monthDisplay");

  if (!calendarGrid || !monthDisplay) return;

  const dt = new Date();

  if (nav !== 0) {
    dt.setMonth(new Date().getMonth() + nav);
  }

  const month = dt.getMonth();
  const year = dt.getFullYear();

  const firstDayOfMonth = new Date(year, month, 1);

  const daysInMonth = new Date(year, month + 1, 0).getDate();

  const paddingDays = firstDayOfMonth.getDay();

  monthDisplay.textContent = dt.toLocaleDateString("en-us", {
    month: "long",
    year: "numeric",
  });

  calendarGrid.innerHTML = "";

  const ordersByDate = {};

  orders.forEach((order) => {
    if (order.booking_datetime) {
      const orderDate = new Date(order.booking_datetime);

      const dateKey = `${orderDate.getFullYear()}-${orderDate.getMonth()}-${orderDate.getDate()}`;

      if (!ordersByDate[dateKey]) {
        ordersByDate[dateKey] = [];
      }

      ordersByDate[dateKey].push(order);
    }
  });

  for (let i = 1; i <= paddingDays + daysInMonth; i++) {
    const daySquare = document.createElement("div");

    if (i > paddingDays) {
      const dayNumber = i - paddingDays;

      daySquare.textContent = dayNumber;

      const currentSquareKey = `${year}-${month}-${dayNumber}`;

      const daysBookings = ordersByDate[currentSquareKey];

      if (daysBookings && daysBookings.length > 0) {
        const indicator = document.createElement("div");

        indicator.textContent = `● ${daysBookings.length}`;

        indicator.style.fontSize = "12px";
        indicator.style.marginTop = "5px";

        daySquare.appendChild(indicator);
      }
    }

    calendarGrid.appendChild(daySquare);
  }
}

// ================= START =================
fetchOrders();

// ================= LOADER (RUN ONCE PER SESSION) =================
window.addEventListener("load", function () {
  const loader = document.getElementById("startup-loader");

  if (loader) {
    if (!sessionStorage.getItem("hasSeenLoader")) {
      loader.style.display = "flex";

      sessionStorage.setItem("hasSeenLoader", "true");

      setTimeout(function () {
        loader.style.display = "none";
      }, 2000);
    } else {
      loader.style.display = "none";
    }
  }
});

// ================= SPECIAL NOTES =================
async function loadSpecialNotes() {
  const container = document.getElementById("notesContainer");

  const search = document.getElementById("searchNotes").value.toLowerCase();

  try {
    const response = await fetch("fetch_notes.php");

    const notes = await response.json();

    container.innerHTML = "";

    if (notes.length === 0) {
      container.innerHTML = `<p class="empty-note">No special notes found.</p>`;
      return;
    }

    let hasResult = false;

    notes.forEach((note) => {
      const customer = (note.name || "").toLowerCase();

      const message = (note.special_notes || "").toLowerCase();

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
        </div>
      `;
    });

    if (!hasResult) {
      container.innerHTML = `<p class="empty-note">No matching notes found.</p>`;
    }
  } catch (error) {
    console.log(error);
  }
}

// SEARCH NOTES
document
  .getElementById("searchNotes")
  .addEventListener("input", loadSpecialNotes);

// LOAD NOTES
loadSpecialNotes();

setInterval(loadSpecialNotes, 3000);
