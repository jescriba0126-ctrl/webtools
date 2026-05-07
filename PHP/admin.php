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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

<header id="adminHeader">

    <div class="logo">
        <img src="../IMAGES/logo.jpg" alt="">
        <h1><span>Admin</span> Dashboard</h1>
    </div>

    <nav>
        <a href="main.html">Home</a>
        <a href="logout.php" class="btn logout">Logout</a>
    </nav>

</header>

<main class="dashboard-container">

    <!-- STATS -->
    <section class="stats-grid">

        <div class="stat-card">
            <h3>Total Bookings</h3>
            <p id="totalBookings">0</p>
        </div>

        <div class="stat-card">
            <h3>Pending</h3>
            <p id="pendingBookings">0</p>
        </div>

        <div class="stat-card">
            <h3>Completed</h3>
            <p id="completedBookings">0</p>
        </div>

        <div class="stat-card">
            <h3>Total Revenue</h3>
            <p>₱<span id="totalRevenue">0</span></p>
        </div>

    </section>

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
        <i>🔍</i>
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
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody></tbody>

        </table>

    </section>

    <section class="dashboard-overview">

    <div class="overview-title">
        <h2>Booking Dashboard Overview</h2>
        <p>Real-time monitoring of all reservation statuses</p>
    </div>

    <div class="overview-grid">

        <!-- TOTAL -->
        <div class="overview-card total">
            <h3>Total Bookings</h3>
            <p id="totalBookings">0</p>
            <span class="tag">All Reservations</span>
        </div>

        <!-- PENDING -->
        <div class="overview-card pending">
            <h3>Pending</h3>
            <p id="pendingBookings">0</p>
            <span class="tag">Waiting Approval</span>
        </div>

        <!-- APPROVED -->
        <div class="overview-card approved">
            <h3>Approved</h3>
            <p id="approvedBookings">0</p>
            <span class="tag">Confirmed Orders</span>
        </div>

        <!-- COMPLETED -->
        <div class="overview-card completed">
            <h3>Completed</h3>
            <p id="completedBookings">0</p>
            <span class="tag">Finished Events</span>
        </div>

        <!-- REVENUE -->
        <div class="overview-card revenue">
            <h3>Total Revenue</h3>
            <p>₱<span id="totalRevenue">0</span></p>
            <span class="tag">Earned Income</span>
        </div>

        <!-- ACTIVE STATUS FLOW -->
        <div class="overview-card flow">
            <h3>Active Booking Flow</h3>
            <div class="status-flow">

                <div class="flow-step">
                    <span id="pendingFlow">0</span>
                    <small>Pending</small>
                </div>

                <div class="arrow">→</div>

                <div class="flow-step">
                    <span id="approvedFlow">0</span>
                    <small>Approved</small>
                </div>

                <div class="arrow">→</div>

                <div class="flow-step">
                    <span id="completedFlow">0</span>
                    <small>Completed</small>
                </div>
  
            </div>
        </div>

    </div>

</section>
    </div>

</section>

</main>



<script>

const ordersTable = document.querySelector("#ordersTable tbody");

function loadOrders(){

    const orders = JSON.parse(localStorage.getItem("orders")) || [];

    ordersTable.innerHTML = "";

    let totalRevenue = 0;
    let pending = 0;
    let completed = 0;
    let totalGuests = 0;

    const searchValue =
        document.getElementById("searchOrder")
        .value
        .toLowerCase();

    const filterValue =
        document.getElementById("filterStatus")
        .value;

    orders.forEach((order,index)=>{

        if(
            !order.name.toLowerCase().includes(searchValue)
        ){
            return;
        }

        if(
            filterValue !== "all" &&
            order.status !== filterValue
        ){
            return;
        }

        if(order.status === "Pending") pending++;

        if(order.status === "Completed"){
            completed++;
            totalRevenue += Number(order.amount);
        }

        totalGuests += Number(order.guests || 0);

        const row = document.createElement("tr");

        row.innerHTML = `
            <td>${index+1}</td>
            <td>${order.name}</td>
            <td>${order.contact}</td>
            <td>${order.service}</td>
            <td>${order.guests}</td>
            <td>₱${order.amount}</td>

            <td>
                <span class="status ${order.status.toLowerCase()}">
                    ${order.status}
                </span>
            </td>

            <td>

                <button class="btn approve"
                onclick="approveOrder(${index})">
                Approve
                </button>

                <button class="btn completed"
                onclick="completeOrder(${index})">
                Done
                </button>

                <button class="btn delete"
                onclick="deleteOrder(${index})">
                Delete
                </button>

            </td>
        `;

        ordersTable.appendChild(row);

    });

    document.getElementById("totalBookings").textContent =
        orders.length;

    document.getElementById("pendingBookings").textContent =
        pending;

    document.getElementById("completedBookings").textContent =
        completed;

    document.getElementById("totalRevenue").textContent =
        totalRevenue;

    document.getElementById("currentBooked").textContent =
        totalGuests;

    document.getElementById("slotsAvailable").textContent =
        100 - totalGuests;

    const percentage = (totalGuests / 100) * 100;

    document.getElementById("capacityFill").style.width =
        percentage + "%";
}

function approveOrder(index){

    let orders =
        JSON.parse(localStorage.getItem("orders")) || [];

    orders[index].status = "Approved";

    localStorage.setItem("orders", JSON.stringify(orders));

    loadOrders();
}

function completeOrder(index){

    let orders =
        JSON.parse(localStorage.getItem("orders")) || [];

    orders[index].status = "Completed";

    localStorage.setItem("orders", JSON.stringify(orders));

    loadOrders();
}

function deleteOrder(index){

    let orders =
        JSON.parse(localStorage.getItem("orders")) || [];

    orders.splice(index,1);

    localStorage.setItem("orders", JSON.stringify(orders));

    loadOrders();
}

document
.getElementById("searchOrder")
.addEventListener("input", loadOrders);

document
.getElementById("filterStatus")
.addEventListener("change", loadOrders);

loadOrders();

</script>

<script src="JS/admin.js"></script>

</body>
</html>