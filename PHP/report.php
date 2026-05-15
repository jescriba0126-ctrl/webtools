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
        <li><a href="revenue.php"><span></span>Revenue</a></li>
        <li><a href="calendar.php"><span></span>Calendar</a></li>
        <li><a href="customer.php"><span></span>Customers</a></li>
        <li class="active"><a href="report.php"><span></span>Reports</a></li> </ul>
</div>

<header id="adminHeader">
    <div class="logo">
        <h1><span>Reports</span> Dashboard</h1>
    </div>
    <nav>
        <a href="logout.php" class="btn logout">Logout</a> </nav>
</header>

<!-- MAIN -->
<main class="dashboard-container">

    <!-- TOP STATS s-->
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

<script>

async function loadReports(){

    try{

        const response =
            await fetch(
                "admin_bookings.php?action=list"
            );

        const data =
            await response.json();

        if(!data.success){
            console.log("Failed to load reports");
            return;
        }

        const orders = data.bookings;

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

        let totalBookings = 0;

        orders.forEach((order,index)=>{

            if(
                order.name &&
                !order.name
                .toLowerCase()
                .includes(search)
            ) return;

            if(
                filter !== "all" &&
                order.status !== filter
            ) return;

            totalBookings++;

            guests += Number(order.guests || 0);

            // ONLY COMPLETED COUNTS AS REVENUE
            if(order.status === "Completed"){
                completed++;
                revenue += Number(order.amount || 0);
            }

            if(order.status === "Pending"){
                pending++;
            }

            if(order.status === "Approved"){
                approved++;
            }

            if(order.status === "Cancelled"){
                cancelled++;
            }

            // POPULAR SERVICE
            if(order.occasion){

                services[order.occasion] =
                    (services[order.occasion] || 0) + 1;
            }

            const row =
                document.createElement("tr");

            row.innerHTML = `

                <td>${index + 1}</td>

                <td>${order.name || "—"}</td>

                <td>${order.phone || "—"}</td>

                <td>${order.occasion || "—"}</td>

                <td>${order.guests || 0}</td>

                <td>
                    ₱${Number(order.amount || 0)
                        .toLocaleString()}
                </td>

                <td>
                    ${order.payment_method || "—"}
                </td>

                <td>
                    <span class="status ${order.status.toLowerCase()}">
                        ${order.status}
                    </span>
                </td>

                <td>
                    ${new Date(order.booking_datetime)
                        .toLocaleString()}
                </td>

            `;

            tableBody.appendChild(row);

        });

        // ================= POPULAR SERVICE =================

        let popular = "N/A";

        let max = 0;

        for(let service in services){

            if(services[service] > max){

                max = services[service];

                popular = service;
            }
        }

        // ================= DASHBOARD =================

        document.getElementById(
            "totalRevenue"
        ).textContent =
            revenue.toLocaleString();

        document.getElementById(
            "totalBookings"
        ).textContent =
            totalBookings;

        document.getElementById(
            "totalGuests"
        ).textContent =
            guests;

        document.getElementById(
            "completedBookings"
        ).textContent =
            completed;

        document.getElementById(
            "pendingCount"
        ).textContent =
            pending;

        document.getElementById(
            "approvedCount"
        ).textContent =
            approved;

        document.getElementById(
            "cancelledCount"
        ).textContent =
            cancelled;

        document.getElementById(
            "popularService"
        ).textContent =
            popular;

    }
    catch(error){

        console.log(
            "Report fetch error:",
            error
        );
    }
}

// ================= START =================

loadReports();

// AUTO REFRESH REALTIME
setInterval(loadReports, 2000);

// SEARCH
document
.getElementById("searchReport")
.addEventListener(
    "input",
    loadReports
);

// FILTER
document
.getElementById("filterReport")
.addEventListener(
    "change",
    loadReports
);

</script>

</body>
</html>
