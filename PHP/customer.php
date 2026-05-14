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
<title>Cubiertos Customers Dashboard</title>

<link rel="stylesheet" href="../CSS/adminsamp.css">
<link rel="stylesheet" href="../CSS/customer.css">
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
        <li class="active"><a href="customer.php"><span></span>Customers</a></li> <li><a href="report.php"><span></span>Reports</a></li>
    </ul>
</div>

<header id="adminHeader">
    <div class="logo">
        <h1><span>Customers</span> Dashboard</h1>
    </div>
    <nav>
        <a href="logout.php" class="btn logout">Logout</a> </nav>
</header>

<!-- MAIN -->
<main class="dashboard-container">

    <!-- CUSTOMER STATS -->
    <section class="stats-grid">

        <div class="stat-card">
            <h3>Total Customers</h3>
            <p id="totalCustomers">0</p>
        </div>

        <div class="stat-card">
            <h3>VIP Customers</h3>
            <p id="vipCustomers">0</p>
        </div>

        <div class="stat-card">
            <h3>Repeat Customers</h3>
            <p id="repeatCustomers">0</p>
        </div>

        <div class="stat-card">
            <h3>Top Customer</h3>
            <p id="topCustomer">N/A</p>
        </div>

    </section>

    <!-- CUSTOMER INSIGHTS -->
    <section class="customer-insights">

        <div class="insight-card">

            <h2> Customer Loyalty Insights</h2>

            <div class="insight-grid">

                <div class="mini-card">
                    <span>Highest Spending</span>
                    <h3 id="highestSpender">N/A</h3>
                </div>

                <div class="mini-card">
                    <span>Most Bookings</span>
                    <h3 id="mostBookings">N/A</h3>
                </div>

                <div class="mini-card">
                    <span>Average Guests</span>
                    <h3 id="averageGuests">0</h3>
                </div>

            </div>

        </div>

    </section>

    <!-- CUSTOMER TABLE -->
    <section class="customer-table-section">

        <div class="table-header">

            <h2> Customer Records</h2>

            <div class="table-controls">

                <input
                    type="text"
                    id="searchCustomer"
                    placeholder="Search customer..."
                >

                <select id="filterCustomer">
                    <option value="all">All Customers</option>
                    <option value="vip">VIP Customers</option>
                    <option value="repeat">Repeat Customers</option>
                </select>

            </div>

        </div>

        <div class="table-wrapper">

            <table class="customer-table">

                <thead>

                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Total Bookings</th>
                        <th>Total Guests</th>
                        <th>Total Spending</th>
                        <th>Customer Type</th>
                        <th>Latest Booking</th>
                    </tr>

                </thead>

                <tbody id="customerTableBody"></tbody>

            </table>

        </div>

    </section>

</main>

<script>function loadCustomers(){

    const orders =
        JSON.parse(
            localStorage.getItem("orders")
        ) || [];

    const tableBody =
        document.getElementById(
            "customerTableBody"
        );

    tableBody.innerHTML = "";

    const customers = {};

    orders.forEach(order => {

        const key = order.contact;

        if(!customers[key]){

            customers[key] = {

                name: order.name,
                contact: order.contact,
                bookings: 0,
                guests: 0,
                spending: 0,
                latest: order.datetime
            };
        }

        customers[key].bookings++;
        customers[key].guests +=
            Number(order.guests || 0);

        customers[key].spending +=
            Number(order.amount || 0);

        customers[key].latest =
            order.datetime;
    });

    const customerArray =
        Object.values(customers);

    let vipCount = 0;
    let repeatCount = 0;

    let highestSpender = "";
    let highestAmount = 0;

    let mostBookings = "";
    let bookingMax = 0;

    let totalGuests = 0;

    const search =
        document
        .getElementById("searchCustomer")
        .value
        .toLowerCase();

    const filter =
        document
        .getElementById("filterCustomer")
        .value;

    customerArray.forEach((customer,index)=>{

        totalGuests += customer.guests;

        let type = "normal";

        if(customer.spending >= 10000){

            type = "vip";
            vipCount++;
        }

        if(customer.bookings >= 2){

            repeatCount++;
            type = "repeat";
        }

        if(customer.spending > highestAmount){

            highestAmount =
                customer.spending;

            highestSpender =
                customer.name;
        }

        if(customer.bookings > bookingMax){

            bookingMax =
                customer.bookings;

            mostBookings =
                customer.name;
        }

        if(
            customer.name
            .toLowerCase()
            .includes(search) === false
        ) return;

        if(filter === "vip" &&
            type !== "vip") return;

        if(filter === "repeat" &&
            type !== "repeat") return;

        const row =
            document.createElement("tr");

        row.innerHTML = `
            <td>${index + 1}</td>

            <td>${customer.name}</td>

            <td>${customer.contact}</td>

            <td>${customer.bookings}</td>

            <td>${customer.guests}</td>

            <td>
                ₱${customer.spending
                    .toLocaleString()}
            </td>

            <td>
                <span class="
                    customer-type
                    ${type}
                ">
                    ${type.toUpperCase()}
                </span>
            </td>

            <td>
                ${new Date(
                    customer.latest
                ).toLocaleString()}
            </td>
        `;

        tableBody.appendChild(row);
    });

    document
    .getElementById("totalCustomers")
    .textContent =
        customerArray.length;

    document
    .getElementById("vipCustomers")
    .textContent =
        vipCount;

    document
    .getElementById("repeatCustomers")
    .textContent =
        repeatCount;

    document
    .getElementById("topCustomer")
    .textContent =
        highestSpender || "N/A";

    document
    .getElementById("highestSpender")
    .textContent =
        highestSpender || "N/A";

    document
    .getElementById("mostBookings")
    .textContent =
        mostBookings || "N/A";

    document
    .getElementById("averageGuests")
    .textContent =
        customerArray.length > 0
        ? Math.round(
            totalGuests /
            customerArray.length
        )
        : 0;
}

loadCustomers();

setInterval(loadCustomers,3000);

window.addEventListener(
    "storage",
    loadCustomers
);

document
.getElementById("searchCustomer")
.addEventListener(
    "input",
    loadCustomers
);

document
.getElementById("filterCustomer")
.addEventListener(
    "change",
    loadCustomers
);</script>

</body>
</html>