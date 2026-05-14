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
            <a href="admin.php">
                <span></span>
                Dashboard
            </a>
        </li>

        <li>
            <a href="Revenue.php"> <span></span>
                Revenue
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

<div id="startup-loader" style="display: none;">
    <div class="loader-content">
        <h1 class="loader-title">Cubiertos <span>Food Hub</span></h1>
        <p class="loader-text">Loading Dashboard...</p>
        <div class="spinner"></div>
    </div>
</div>


<!-- ================= CUSTOMER SPECIAL NOTES ================= -->

<section class="card special-notes-card">

    <div class="notes-header">

        <h2>Customer Special Notes</h2>

        <input
            type="text"
            id="searchNotes"
            placeholder="Search notes..."
        >

    </div>

    <div id="notesContainer" class="notes-grid">

        <p class="empty-note">
            No special notes available.
        </p>

    </div>

</section>


<script src="../JS/admin.js"></script>

</body>
</html>