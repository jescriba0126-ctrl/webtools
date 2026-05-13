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

  <title>Cubiertos Admin Dashboard</title>

  <link rel="stylesheet" href="../CSS/adminsamp.css">
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

        <li class="active">
            <a href="#">
                <span></span>
                Dashboard
            </a>
        </li>

        <li>
            <a href="#">
                <span></span>
                Appointments
            </a>
        </li>

        <li>
            <a href="#">
                <span></span>
                Venues
            </a>
        </li>

        <li>
    <a href="calendar.php">
        <span></span>
        Calendar
    </a>
</li>
        <li>
            <a href="customer.php">
                <span></span>
                Customers
            </a>
        </li>

        <li>
            <a href="report.php">
                <span></span>
                Reports
            </a>
        </li>

    </ul>

</div>


<header id="adminHeader">

    <div class="logo">
  
        <h1><span>Admin</span> Dashboard</h1>
    </div>

    <nav>
        <a href="main.html">Home</a>
        <a href="logout.php" class="btn logout">Logout</a>
    </nav>

</header>

<main class="dashboard-container">

    <!-- STATS -->
     <section class="dashboard-overview">

    <div class="overview-title">
        <h2>Booking Dashboard Overview</h2>
        <p>Real-time monitoring of all reservation statuses</p>
    </div>

    <div class="overview-grid">

        <!-- TOTAL -->
        <div class="overview-card total">
            <h3>Total Bookings</h3>
            <p id="ov_totalBookings">0</p> <span class="tag">All Reservations</span>
        </div>

        <!-- PENDING -->
        <div class="overview-card pending">
            <h3>Pending</h3>
            <p id="ov_pendingBookings">0</p>
            <span class="tag">Waiting Approval</span>
        </div>

        <!-- APPROVED -->
        <div class="overview-card approved">
            <h3>Approved</h3>
            <p id="ov_approvedBookings">0</p>
            <span class="tag">Confirmed Orders</span>
        </div>

        <!-- COMPLETED -->
        <div class="overview-card completed">
            <h3>Completed</h3>
            <p id="ov_completedBookings">0</p>
            <span class="tag">Finished Events</span>
        </div>

        <!-- CANCELLED -->
<div class="overview-card cancelled">
    <h3>Cancelled</h3>
    <p id="ov_cancelledBookings">0</p>
    <span class="tag">Cancelled Reservations</span>
</div>

        <!-- REVENUE -->
        <div class="overview-card revenue">
            <h3>Total Revenue</h3>
            <p>₱<span id="ov_totalRevenue">0</span></p>
            <span class="tag">Earned Income</span>
        </div>

         <!-- ACTIVE STATUS FLOW -->
        <div class="overview-card flow">
            <h3>Active Booking Flow</h3>
            <div class="status-flow">

                <div class="flow-step">
                    <span id="ov_pendingFlow">0</span>
                    <small>Pending</small>
                </div>

                <div class="arrow">→</div>

                <div class="flow-step">
                    <span id="ov_approvedFlow">0</span>
                    <small>Approved</small>
                </div>

                <div class="arrow">→</div>

                <div class="flow-step">
                    <span id="ov_completedFlow">0</span>
                    <small>Completed</small>
                </div>
  
            </div>
        </div>

    </div>

    <!-- CAPACITY -->
    <section class="admin-status-bar">

        <div class="combined-status-card">

            <!-- CALENDAR -->
            <div class="status-section">

                <h2 class="section-title">Booking Calendar</h2>

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

            <!-- CAPACITY -->
            <div class="status-section">

                <div class="capacity-header">
                    <h2 class="section-title">Daily Capacity</h2>
                    <button class="btn-set-limit" onclick="setNewLimit()">
                        Set Limit
                    </button>
                </div>

                <div class="capacity-body">

                    <p class="label-text">Maximum Guests</p>

                    <p class="capacity-value">
                        <span id="maxCapacity">100</span> / Day
                    </p>

                    <div class="progress-container">
                        <div id="capacityFill" class="progress-fill"></div>
                    </div>

                    <div class="capacity-footer">
                        <p>Booked: <strong id="currentBooked">0</strong></p>
                        <p>Available: <strong id="slotsAvailable">100</strong></p>
                    </div>

                </div>

            </div>

        </div>

    </section>

    <!-- CONTROLS -->
    <section class="card">

        <div class="table-controls">

    <div class="search-box">
        <input type="text"
        id="searchOrder"
        placeholder="Search customer name...">
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

        <!-- TABLE -->
        <table id="ordersTable">

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
    <th>Action</th>
</tr>
            </thead>

            <tbody></tbody>

        </table>

    </section>


</section>
    </div>

</section>

</main>

<div id="startup-loader">
    <div class="loader-content">
        <h1 class="loader-title">Cubiertos <span>Food Hub</span></h1>
        <p class="loader-text">Loading Dashboard...</p>
        <div class="spinner"></div>
    </div>
</div>



<script src="../JS/admin.js"></script>

</body>
</html>