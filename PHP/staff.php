<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'staff'){
    header("Location: register.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Cubiertos Staff Dashboard</title>

<link rel="stylesheet" href="../CSS/adminsamp.css">
<link rel="icon" type="image/jpg" href="/IMAGES/logo.jpg">
<link rel="stylesheet" href="../CSS/staff.css">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">


</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="sidebar-logo">
        <img src="../IMAGES/logo.jpg" alt="">
        <h2>Cubiertos</h2>
    </div>

    <ul class="sidebar-menu">

        <li class="active">
            <a href="#">
                <span>📊</span>
                Dashboard
            </a>
        </li>

        <li>
            <a href="#">
                <span>📅</span>
                Reservations
            </a>
        </li>

        <li>
            <a href="#">
                <span>🍽️</span>
                Orders
            </a>
        </li>

        <li>
            <a href="#">
                <span>👥</span>
                Customers
            </a>
        </li>

        <li>
            <a href="#">
                <span>✅</span>
                Task Board
            </a>
        </li>

    </ul>

</div>

<!-- HEADER -->
<header id="adminHeader">

    <div class="logo">
        <h1><span>Staff</span> Dashboard</h1>
    </div>

    <nav>
        <div class="staff-badge">
            Staff Account
        </div>

        <a href="main.html">Home</a>
        <a href="logout.php" class="btn logout">Logout</a>
    </nav>

</header>

<!-- MAIN -->
<main class="dashboard-container">

<!-- OVERVIEW -->
<section class="dashboard-overview">

    <div class="overview-title">
        <h2>Staff Dashboard Overview</h2>
        <p>Manage daily reservations and customer assistance</p>
    </div>

    <div class="overview-grid">

        <div class="overview-card total">
            <h3>Today's Reservations</h3>
            <p id="totalReservations">0</p>
            <span class="tag">Daily Bookings</span>
        </div>

        <div class="overview-card pending">
            <h3>Pending</h3>
            <p id="pendingReservations">0</p>
            <span class="tag">Awaiting Confirmation</span>
        </div>

        <div class="overview-card approved">
            <h3>Approved</h3>
            <p id="approvedReservations">0</p>
            <span class="tag">Confirmed Guests</span>
        </div>

        <div class="overview-card completed">
            <h3>Completed</h3>
            <p id="completedReservations">0</p>
            <span class="tag">Finished Events</span>
        </div>

    </div>

</section>

<!-- NOTICE -->
<div class="notice-box">
    <h3>Staff Permissions</h3>

    <p>
        Staff accounts can manage bookings, update reservation statuses,
        assist customers, and monitor daily schedules.
        Only administrators can delete records, manage reports,
        edit system settings, and access revenue analytics.
    </p>
</div>

<!-- QUICK ACTIONS -->
<section class="quick-actions">

    <div class="quick-card">
        <h3>Check Reservations</h3>

        <p>
            Review customer bookings and reservation schedules.
        </p>

        <button>
            Open Reservations
        </button>
    </div>

    <div class="quick-card">
        <h3>Customer Assistance</h3>

        <p>
            Help guests with inquiries and booking concerns.
        </p>

        <button>
            View Customers
        </button>
    </div>

    <div class="quick-card">
        <h3>Daily Tasks</h3>

        <p>
            Track staff tasks and event preparation progress.
        </p>

        <button>
            Open Tasks
        </button>
    </div>

</section>

<!-- STATUS + CALENDAR -->
<section class="admin-status-bar">

    <div class="combined-status-card">

        <!-- CALENDAR -->
        <div class="status-section">

            <h2 class="section-title">
                Reservation Calendar
            </h2>

            <div class="calendar-box">

                <div class="calendar-header">
                    <button id="prevMonth">&lt;</button>
                    <span id="monthDisplay"></span>
                    <button id="nextMonth">&gt;</button>
                </div>

                <div class="calendar-days">
                    <span>Su</span>
                    <span>Mo</span>
                    <span>Tu</span>
                    <span>We</span>
                    <span>Th</span>
                    <span>Fr</span>
                    <span>Sa</span>
                </div>

                <div id="calendarGrid" class="calendar-grid"></div>

            </div>

        </div>

        <div class="vertical-divider"></div>

        <!-- DAILY STATUS -->
        <div class="status-section">

            <h2 class="section-title">
                Daily Reservation Status
            </h2>

            <p class="label-text">
                Reservation Capacity
            </p>

            <p class="capacity-value">
                <span id="reservedCount">45</span> Guests
            </p>

            <div class="progress-container">
                <div id="capacityFill" class="progress-fill"></div>
            </div>

            <div class="capacity-footer">
                <p>Reserved: <strong>45</strong></p>
                <p>Remaining: <strong>55</strong></p>
            </div>

        </div>

    </div>

</section>

<!-- TABLE -->
<section class="card">

    <div class="table-controls">

        <div class="search-box">
            <input type="text" id="searchReservation"
            placeholder="Search customer...">
        </div>

        <div class="filter-box">
            <select id="filterStatus">
                <option value="all">All Status</option>
                <option value="Pending">Pending</option>
                <option value="Approved">Approved</option>
                <option value="Completed">Completed</option>
            </select>
        </div>

    </div>

    <table>

        <thead>
            <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Contact</th>
                <th>Service</th>
                <th>Guests</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>

            <tr>
                <td>001</td>
                <td>Juan Dela Cruz</td>
                <td>09123456789</td>
                <td>Birthday Event</td>
                <td>50</td>

                <td>
                    <span class="status pending">
                        Pending
                    </span>
                </td>

                <td>
                    <button class="btn approve">
                        Approve
                    </button>

                    <button class="btn completed">
                        Complete
                    </button>
                </td>
            </tr>

            <tr>
                <td>002</td>
                <td>Maria Santos</td>
                <td>09998887777</td>
                <td>Wedding Reception</td>
                <td>120</td>

                <td>
                    <span class="status approved">
                        Approved
                    </span>
                </td>

                <td>
                    <button class="btn completed">
                        Complete
                    </button>
                </td>
            </tr>

        </tbody>

    </table>

</section>

</main>

<!-- LOADER -->
<div id="startup-loader">

    <div class="loader-content">

        <h1 class="loader-title">
            Cubiertos <span>Food Hub</span>
        </h1>

        <p class="loader-text">
            Loading Staff Dashboard...
        </p>

        <div class="spinner"></div>

    </div>

</div>

<script>

/* SAMPLE COUNTS */

document.getElementById("totalReservations").innerText = 26;
document.getElementById("pendingReservations").innerText = 5;
document.getElementById("approvedReservations").innerText = 15;
document.getElementById("completedReservations").innerText = 6;

/* CAPACITY BAR */

const fill = document.getElementById("capacityFill");
fill.style.width = "45%";

/* CALENDAR */

const monthDisplay = document.getElementById("monthDisplay");
const calendarGrid = document.getElementById("calendarGrid");

let currentDate = new Date();

function renderCalendar() {

    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    const firstDay = new Date(year, month, 1).getDay();
    const totalDays = new Date(year, month + 1, 0).getDate();

    monthDisplay.innerText =
        currentDate.toLocaleString('default', {
            month:'long',
            year:'numeric'
        });

    calendarGrid.innerHTML = "";

    for(let i = 0; i < firstDay; i++) {
        const blank = document.createElement("div");
        calendarGrid.appendChild(blank);
    }

    for(let day = 1; day <= totalDays; day++) {

        const dayBox = document.createElement("div");

        dayBox.innerText = day;

        if(
            day === new Date().getDate() &&
            month === new Date().getMonth() &&
            year === new Date().getFullYear()
        ){
            dayBox.classList.add("active-day");
        }

        calendarGrid.appendChild(dayBox);
    }
}

document.getElementById("prevMonth").onclick = () => {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar();
};

document.getElementById("nextMonth").onclick = () => {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar();
};

renderCalendar();

</script>

</body>
</html>