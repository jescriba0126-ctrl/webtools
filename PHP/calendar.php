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
<title>Booking Calendar</title>

<link rel="stylesheet" href="../CSS/adminsamp.css">
<link rel="stylesheet" href="../CSS/calendar.css">
<link rel="icon" type="image/jpg" href="/IMAGES/logo.jpg">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
<div class="sidebar">
    <div class="sidebar-logo">
        <img src="../IMAGES/logo.jpg" alt="">
        <h2>Cubiertos</h2>
    </div>
    <ul class="sidebar-menu">
        <li><a href="admin.php"><span></span>Dashboard</a></li>
        <li><a href="appointments.php"><span></span>Appointments</a></li>
        <li><a href="venues.php"><span></span>Venues</a></li>
        <li class="active"><a href="calendar.php"><span></span>Calendar</a></li> <li><a href="customer.php"><span></span>Customers</a></li>
        <li><a href="report.php"><span></span>Reports</a></li>
    </ul>
</div>

<header id="adminHeader">
    <div class="logo">
        <h1><span>Calendar</span> Dashboard</h1> </div>
    <nav>
        <a href="admin.php">Dashboard</a>
        <a href="logout.php" class="btn logout">Logout</a> </nav>
</header>

<!-- MAIN -->
<main class="dashboard-container">

    <!-- TOP STATS -->
    <section class="calendar-stats">

        <div class="stat-box">
            <h3>Total Reservations</h3>
            <p id="totalReservations">0</p>
        </div>

        <div class="stat-box">
            <h3>Today's Bookings</h3>
            <p id="todayBookings">0</p>
        </div>

        <div class="stat-box">
            <h3>Pending</h3>
            <p id="pendingBookings">0</p>
        </div>

        <div class="stat-box">
            <h3>Approved</h3>
            <p id="approvedBookings">0</p>
        </div>

    </section>

    <!-- ================= CLIENT RESERVATION LIST ================= -->
<section class="reservation-list-section">

    <div class="list-header">
        <h2> Client Reservation List</h2>

        <div class="list-controls">

            <input
                type="text"
                id="searchClient"
                placeholder="Search client..."
            >

            <select id="statusFilter">
                <option value="all">All Status</option>
                <option value="Pending">Pending</option>
                <option value="Approved">Approved</option>
                <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option>
            </select>

        </div>
    </div>

    <div class="reservation-table-wrapper">

        <table class="reservation-table">

            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Service</th>
                    <th>Guests</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>

            <tbody id="reservationTableBody"></tbody>

        </table>

    </div>

</section>

    <div class="calendar-wrapper">

        <div class="calendar-left-column">
            
            <div class="calendar-card">

                <div class="calendar-header">
                    <button id="prevMonth">&lt;</button>
                    <h2 id="monthDisplay"></h2>
                    <button id="nextMonth">&gt;</button>
                </div>

                <div class="calendar-days">
                    <span>Sun</span>
                    <span>Mon</span>
                    <span>Tue</span>
                    <span>Wed</span>
                    <span>Thu</span>
                    <span>Fri</span>
                    <span>Sat</span>
                </div>

                <div id="calendarGrid" class="calendar-grid"></div>

            </div>

            <div class="capacity-card" style="background: white; border-radius: 25px; padding: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); margin-top: 30px;">
                
                <div class="capacity-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 class="section-title" style="font-size: 1.2rem; color: #283618;">Daily Capacity</h2>
                    <button class="btn-set-limit" onclick="setNewLimit()" style="border: none; background: #bc6c25; color: white; padding: 8px 16px; border-radius: 20px; cursor: pointer; font-weight: 500;">Set Limit</button>
                </div>

                <div class="capacity-body">
                    <p class="label-text" style="color: #777; margin-bottom: 5px;">Maximum Guests</p>
                    
                    <p class="capacity-value" style="font-size: 2rem; font-weight: 700; color: #283618; margin-bottom: 15px;">
                        <span id="maxCapacity">100</span> / Day
                    </p>

                    <div class="progress-container" style="width: 100%; height: 12px; background: #ececec; border-radius: 20px; overflow: hidden; margin-bottom: 15px;">
                        <div id="capacityFill" class="progress-fill" style="width: 0%; height: 100%; background: #bc6c25; border-radius: 20px; transition: 0.4s ease;"></div>
                    </div>

                    <div class="capacity-footer" style="display: flex; justify-content: space-between; font-size: 0.9rem; color: #555;">
                        <p>Booked: <strong id="currentBooked" style="color: #bc6c25;">0</strong></p>
                        <p>Available: <strong id="slotsAvailable">100</strong></p>
                    </div>

                </div>
            </div>

        </div>

        <div class="details-card">

            <div class="details-top">

                <h2>Client Reservations</h2>

                <input
                    type="text"
                    id="searchClient"
                    placeholder="Search client..."
                >

            </div>

            <div id="bookingDetails">

                <p class="empty">
                    Select a date to view reservations.
                </p>

            </div>

        </div>

    </div>

</main>

<script>

let nav = 0;

function renderCalendar() {

    const calendarGrid =
        document.getElementById("calendarGrid");

    const monthDisplay =
        document.getElementById("monthDisplay");

    let orders =
        JSON.parse(localStorage.getItem("orders")) || [];

    const dt = new Date();

    if(nav !== 0){
        dt.setMonth(new Date().getMonth() + nav);
    }

    const month = dt.getMonth();
    const year = dt.getFullYear();

    const daysInMonth =
        new Date(year, month + 1, 0).getDate();

    const firstDay =
        new Date(year, month, 1);

    const paddingDays =
        firstDay.getDay();

    monthDisplay.innerText =
        dt.toLocaleDateString("en-us", {
            month: "long",
            year: "numeric"
        });

    calendarGrid.innerHTML = "";

    let ordersByDate = {};

    orders.forEach(order => {

        if(order.datetime){

            const date =
                new Date(order.datetime);

            const key =
                `${date.getFullYear()}-${date.getMonth()}-${date.getDate()}`;

            if(!ordersByDate[key]){
                ordersByDate[key] = [];
            }

            ordersByDate[key].push(order);
        }

    });

    for(let i = 1; i <= paddingDays + daysInMonth; i++){

        const daySquare =
            document.createElement("div");

        if(i > paddingDays){

            const dayNumber =
                i - paddingDays;

            daySquare.classList.add("day");

            daySquare.innerHTML =
                `<span>${dayNumber}</span>`;

            const key =
                `${year}-${month}-${dayNumber}`;

            const bookings =
                ordersByDate[key];

            if(bookings){

    daySquare.classList.add("has-booking");

    // TOTAL BOOKINGS
    const badge =
        document.createElement("small");

    badge.innerText =
        bookings.length + " booking";

    daySquare.appendChild(badge);

    // CLICK EVENT
    daySquare.addEventListener("click", () => {

        showBookings(bookings);

    });

}

            const today = new Date();

            if(
                dayNumber === today.getDate() &&
                month === today.getMonth() &&
                year === today.getFullYear()
            ){
                daySquare.classList.add("today");
            }

        }

        calendarGrid.appendChild(daySquare);
        const allOrders =
    JSON.parse(localStorage.getItem("orders")) || [];

document.getElementById("totalReservations")
.innerText = allOrders.length;

document.getElementById("pendingBookings")
.innerText =
    allOrders.filter(o => o.status === "Pending").length;

document.getElementById("approvedBookings")
.innerText =
    allOrders.filter(o => o.status === "Approved").length;

const today = new Date();

const todayKey =
`${today.getFullYear()}-${today.getMonth()}-${today.getDate()}`;

document.getElementById("todayBookings")
.innerText =
    allOrders.filter(order => {

        if(!order.datetime) return false;

        const d =
            new Date(order.datetime);

        const key =
`${d.getFullYear()}-${d.getMonth()}-${d.getDate()}`;

        return key === todayKey;

    }).length;


    }

}

function showBookings(bookings){

    const container =
        document.getElementById("bookingDetails");

    container.innerHTML = "";

    bookings.forEach((order,index) => {

        container.innerHTML += `

        <div class="booking-card">

            <div class="booking-top">

                <h3>${order.name}</h3>

                <span class="status ${order.status.toLowerCase()}">
                    ${order.status}
                </span>

            </div>

            <div class="booking-grid">

                <p><b>📞 Contact:</b><br>${order.contact}</p>

                <p><b>🎉 Service:</b><br>${order.service}</p>

                <p><b>👥 Guests:</b><br>${order.guests}</p>

                <p><b>💳 Payment:</b><br>${order.payment}</p>

                <p><b>💰 Amount:</b><br>₱${Number(order.amount).toLocaleString()}</p>

                <p><b>📅 Schedule:</b><br>
                ${new Date(order.datetime).toLocaleString()}
                </p>

            </div>

            <div class="booking-actions">

                <button onclick="approveBooking(${index})">
                    Approve
                </button>

                <button onclick="completeBooking(${index})">
                    Complete
                </button>

                <button class="danger"
                    onclick="cancelBooking(${index})">
                    Cancel
                </button>

            </div>

        </div>

        `;
    });
}

document.getElementById("prevMonth")
.addEventListener("click", () => {

    nav--;

    renderCalendar();

});

document.getElementById("nextMonth")
.addEventListener("click", () => {

    nav++;

    renderCalendar();

});

window.addEventListener("storage", renderCalendar);

setInterval(renderCalendar, 3000);

renderCalendar();

function approveBooking(index){

    let orders =
        JSON.parse(localStorage.getItem("orders")) || [];

    orders[index].status = "Approved";

    localStorage.setItem(
        "orders",
        JSON.stringify(orders)
    );

    renderCalendar();
}

function completeBooking(index){

    let orders =
        JSON.parse(localStorage.getItem("orders")) || [];

    orders[index].status = "Completed";

    localStorage.setItem(
        "orders",
        JSON.stringify(orders)
    );

    renderCalendar();
}

function cancelBooking(index){

    let orders =
        JSON.parse(localStorage.getItem("orders")) || [];

    orders[index].status = "Cancelled";

    localStorage.setItem(
        "orders",
        JSON.stringify(orders)
    );

    renderCalendar();

}

// ================= CLIENT RESERVATION LIST =================

function loadReservationList() {

    const tableBody =
        document.getElementById(
            "reservationTableBody"
        );

    if (!tableBody) return;

    tableBody.innerHTML = "";

    let orders =
        JSON.parse(
            localStorage.getItem("orders")
        ) || [];

    const searchValue =
        document.getElementById("searchClient")
        ?.value
        .toLowerCase() || "";

    const filterValue =
        document.getElementById("statusFilter")
        ?.value || "all";

    orders.forEach((order, index) => {

        if (
            order.name &&
            !order.name
                .toLowerCase()
                .includes(searchValue)
        ) return;

        if (
            filterValue !== "all" &&
            order.status !== filterValue
        ) return;

        const row =
            document.createElement("tr");

        const bookingDate =
            order.datetime
            ? new Date(order.datetime)
                .toLocaleString()
            : "—";

        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${order.name || "—"}</td>
            <td>${order.contact || "—"}</td>
            <td>${order.service || "—"}</td>
            <td>${order.guests || 0}</td>
            <td>₱${Number(order.amount || 0).toLocaleString()}</td>
            <td>${order.payment || "—"}</td>
            <td>
                <span class="status-badge ${order.status.toLowerCase()}">
                    ${order.status}
                </span>
            </td>
            <td>${bookingDate}</td>
        `;

        tableBody.appendChild(row);
    });
}

document
.getElementById("searchClient")
?.addEventListener(
    "input",
    loadReservationList
);

document
.getElementById("statusFilter")
?.addEventListener(
    "change",
    loadReservationList
);

window.addEventListener(
    "storage",
    loadReservationList
);

setInterval(
    loadReservationList,
    3000
);

loadReservationList();

// ================= CAPACITY LOGIC =================
function updateCapacity() {
    let maxCap = parseInt(localStorage.getItem("dailyCapacity")) || 100;
    let allOrders = JSON.parse(localStorage.getItem("orders")) || [];
    let totalGuests = 0;

    const today = new Date();
    const todayKey = `${today.getFullYear()}-${today.getMonth()}-${today.getDate()}`;

    // Calculate guests ONLY for today's Pending or Approved bookings
    allOrders.forEach(order => {
        if ((order.status === "Pending" || order.status === "Approved") && order.datetime) {
            const d = new Date(order.datetime);
            const key = `${d.getFullYear()}-${d.getMonth()}-${d.getDate()}`;

            if (key === todayKey) {
                totalGuests += Number(order.guests) || 0;
            }
        }
    });

    // Update Text Elements
    document.getElementById("maxCapacity").textContent = maxCap;
    document.getElementById("currentBooked").textContent = totalGuests;
    document.getElementById("slotsAvailable").textContent = Math.max(0, maxCap - totalGuests);

    // Update Progress Bar
    const fillEl = document.getElementById("capacityFill");
    if (fillEl) {
        let percentage = (totalGuests / maxCap) * 100;
        if (percentage > 100) percentage = 100;
        fillEl.style.width = percentage + "%";
    }
}

// Function triggered by the "Set Limit" button
window.setNewLimit = function () {
    let currentLimit = parseInt(localStorage.getItem("dailyCapacity")) || 100;
    let input = prompt("Enter new maximum guests per day:", currentLimit);

    if (input !== null) {
        let newLimit = parseInt(input);
        if (!isNaN(newLimit) && newLimit > 0) {
            localStorage.setItem("dailyCapacity", newLimit);
            updateCapacity(); // Refresh UI instantly
        }
    }
};

// Run immediately and every 3 seconds
updateCapacity();
setInterval(updateCapacity, 3000);
window.addEventListener("storage", updateCapacity);

</script>

</body>
</html>