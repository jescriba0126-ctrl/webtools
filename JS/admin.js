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

  orders.forEach((order, index) => {
    // SEARCH
    if (order.name && !order.name.toLowerCase().includes(searchValue)) return;

    // FILTER
    if (filterValue !== "all" && order.status !== filterValue) return;

    // COUNTS
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

    // TABLE ROW
    const row = document.createElement("tr");

    row.innerHTML = `
            <td>${index + 1}</td>

            <td>${order.name || "—"}</td>

            <td>${order.phone || "—"}<br>${order.email || ""}</td>

            <td>${order.occasion || "—"}</td>

            <td>${order.guests || 0}</td>

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

    ordersTable.appendChild(row);/////////////////////////////////////////////////////////////////////
  });

  // ================= DASHBOARD =================

  document.getElementById("ov_totalBookings").textContent = orders.length;
  document.getElementById("ov_pendingBookings").textContent = pending;
  document.getElementById("ov_approvedBookings").textContent = approved;
  document.getElementById("ov_completedBookings").textContent = completed;
  document.getElementById("ov_cancelledBookings").textContent = cancelled;
  document.getElementById("ov_totalRevenue").textContent = totalRevenue;

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
    // Check if they have NOT seen the loader yet this session
    if (!sessionStorage.getItem("hasSeenLoader")) {
      
      // 1. Show the loader
      loader.style.display = "flex"; // Or "block" depending on your CSS
      
      // 2. Mark that they have seen it
      sessionStorage.setItem("hasSeenLoader", "true");

      // 3. Hide it after 2 seconds
      setTimeout(function () {
        loader.style.display = "none";
      }, 2000);

    } else {
      // If they already saw it, ensure it stays completely hidden
      loader.style.display = "none";
    }
  }
});
