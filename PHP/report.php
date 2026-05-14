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
<title>Cubiertos Reports Dashboard</title>

<link rel="stylesheet" href="../CSS/adminsamp.css">
<link rel="stylesheet" href="../CSS/report.css">
<link rel="icon" type="image/jpg" href="/IMAGES/logo.jpg">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        <li><a href="calendar.php"><span></span>Calendar</a></li>
        <li><a href="customer.php"><span></span>Customers</a></li>
        <li class="active"><a href="report.php"><span></span>Reports</a></li> </ul>
</div>

<header id="adminHeader">
    <div class="logo">
        <h1><span>Reports</span> Dashboard</h1>
    </div>
    <nav>
        <a href="main.html">Home</a>
        <a href="logout.php" class="btn logout">Logout</a> </nav>
</header>

<!-- MAIN -->
<main class="dashboard-container">

    <!-- TOP STATS -->
    <section class="stats-grid">

        <div class="stat-card revenue">
            <h3>Total Revenue</h3>
            <p>₱<span id="totalRevenue">0</span></p>
        </div>

        <div class="stat-card bookings">
            <h3>Total Bookings</h3>
            <p id="totalBookings">0</p>
        </div>

        <div class="stat-card guests">
            <h3>Total Guests</h3>
            <p id="totalGuests">0</p>
        </div>

        <div class="stat-card completed">
            <h3>Completed Events</h3>
            <p id="completedBookings">0</p>
        </div>

    </section>

    <!-- REPORT SUMMARY -->
    <section class="report-summary">

        <div class="summary-card">
            <h2>Booking Insights</h2>

            <div class="summary-grid">

                <div class="mini-card">
                    <span>Pending</span>
                    <h3 id="pendingCount">0</h3>
                </div>

                <div class="mini-card">
                    <span>Approved</span>
                    <h3 id="approvedCount">0</h3>
                </div>

                <div class="mini-card">
                    <span>Cancelled</span>
                    <h3 id="cancelledCount">0</h3>
                </div>

                <div class="mini-card">
                    <span>Most Popular Service</span>
                    <h3 id="popularService">N/A</h3>
                </div>

            </div>

        </div>

    </section>

    <!-- REPORT LOGS -->
    <section class="logs-section">

        <div class="logs-header">
            <h2>Reservation Report Logs</h2>

            <div class="log-controls">

                <input
                    type="text"
                    id="searchReport"
                    placeholder="Search customer..."
                >

                <select id="filterReport">
                    <option value="all">All Status</option>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>

            </div>

        </div>

        <div class="table-wrapper">

            <table class="report-table">

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

                <tbody id="reportTableBody"></tbody>

            </table>

        </div>

    </section>

</main>

<script>javascript
function loadReports(){

    const orders =
        JSON.parse(
            localStorage.getItem("orders")
        ) || [];

    const tableBody =
        document.getElementById(
            "reportTableBody"
        );

    tableBody.innerHTML = "";

    let revenue = 0;
    let guests = 0;
    let completed = 0;
    let pending = 0;
    let approved = 0;
    let cancelled = 0;

    let services = {};

    const search =
        document.getElementById(
            "searchReport"
        ).value.toLowerCase();

    const filter =
        document.getElementById(
            "filterReport"
        ).value;

    orders.forEach((order,index)=>{

        if(order.name &&
            !order.name
            .toLowerCase()
            .includes(search)) return;

        if(filter !== "all" &&
            order.status !== filter) return;

        revenue += Number(order.amount || 0);
        guests += Number(order.guests || 0);

        if(order.status === "Completed") completed++;
        if(order.status === "Pending") pending++;
        if(order.status === "Approved") approved++;
        if(order.status === "Cancelled") cancelled++;

        if(order.service){
            services[order.service] =
                (services[order.service] || 0) + 1;
        }

        const row = document.createElement("tr");

        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${order.name || "—"}</td>
            <td>${order.contact || "—"}</td>
            <td>${order.service || "—"}</td>
            <td>${order.guests || 0}</td>
            <td>₱${Number(order.amount || 0).toLocaleString()}</td>
            <td>${order.payment || "—"}</td>
            <td>
                <span class="status ${order.status.toLowerCase()}">
                    ${order.status}
                </span>
            </td>
            <td>
                ${new Date(order.datetime)
                    .toLocaleString()}
            </td>
        `;

        tableBody.appendChild(row);
    });

    let popular = "N/A";
    let max = 0;

    for(let service in services){

        if(services[service] > max){
            max = services[service];
            popular = service;
        }
    }

    document.getElementById("totalRevenue")
        .textContent = revenue.toLocaleString();

    document.getElementById("totalBookings")
        .textContent = orders.length;

    document.getElementById("totalGuests")
        .textContent = guests;

    document.getElementById("completedBookings")
        .textContent = completed;

    document.getElementById("pendingCount")
        .textContent = pending;

    document.getElementById("approvedCount")
        .textContent = approved;

    document.getElementById("cancelledCount")
        .textContent = cancelled;

    document.getElementById("popularService")
        .textContent = popular;
}

loadReports();

setInterval(loadReports,3000);

window.addEventListener(
    "storage",
    loadReports
);

document
.getElementById("searchReport")
.addEventListener(
    "input",
    loadReports
);

document
.getElementById("filterReport")
.addEventListener(
    "change",
    loadReports
);</script>

</body>
</html>
