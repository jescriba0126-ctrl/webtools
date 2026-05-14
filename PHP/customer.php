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
        <li><a href="revenue.php"><span></span>Revenue</a></li>
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

<script>

// ================= GLOBAL =================

let customersData = [];

// ================= FETCH BOOKINGS =================

async function fetchCustomers(){

    try{

        const response =
            await fetch(
                "admin_bookings.php?action=list"
            );

        const data =
            await response.json();

        if(data.success){

            customersData =
                data.bookings || [];

            loadCustomers();

        }

    }catch(error){

        console.error(error);

    }

}

// ================= LOAD CUSTOMERS =================

function loadCustomers(){

    const tableBody =
        document.getElementById(
            "customerTableBody"
        );

    if(!tableBody) return;

    tableBody.innerHTML = "";

    const customers = {};

    // ================= BUILD CUSTOMER DATA =================

    customersData.forEach(order => {

        const key =
            order.phone ||
            order.email ||
            order.name;

        if(!customers[key]){

            customers[key] = {

                name:
                    order.name || "N/A",

                contact:
                    `
                    ${order.phone || ""}
                    <br>
                    ${order.email || ""}
                    `,

                bookings:0,

                guests:0,

                spending:0,

                latest:
                    order.booking_datetime

            };

        }

        customers[key].bookings++;

        customers[key].guests +=
            Number(order.guests || 0);

        customers[key].spending +=
            Number(order.amount || 0);

        customers[key].latest =
            order.booking_datetime;

    });

    // ================= CONVERT TO ARRAY =================

    const customerArray =
        Object.values(customers);

    // ================= STATS =================

    let vipCount = 0;
    let repeatCount = 0;

    let highestSpender = "N/A";
    let highestAmount = 0;

    let mostBookings = "N/A";
    let bookingMax = 0;

    let totalGuests = 0;

    // ================= SEARCH + FILTER =================

    const search =
        document
        .getElementById(
            "searchCustomer"
        )
        ?.value
        .toLowerCase() || "";

    const filter =
        document
        .getElementById(
            "filterCustomer"
        )
        ?.value || "all";

    // ================= LOOP CUSTOMERS =================

    customerArray.forEach((customer,index)=>{

        totalGuests += customer.guests;

        let type = "normal";

        // VIP
        if(customer.spending >= 10000){

            type = "vip";

            vipCount++;

        }

        // REPEAT
        if(customer.bookings >= 2){

            repeatCount++;

            if(type !== "vip"){

                type = "repeat";

            }

        }

        // HIGHEST SPENDER
        if(customer.spending > highestAmount){

            highestAmount =
                customer.spending;

            highestSpender =
                customer.name;

        }

        // MOST BOOKINGS
        if(customer.bookings > bookingMax){

            bookingMax =
                customer.bookings;

            mostBookings =
                customer.name;

        }

        // SEARCH
        if(
            customer.name
            .toLowerCase()
            .includes(search) === false
        ){
            return;
        }

        // FILTER
        if(
            filter === "vip" &&
            type !== "vip"
        ){
            return;
        }

        if(
            filter === "repeat" &&
            type !== "repeat"
        ){
            return;
        }

        // ================= TABLE ROW =================

        const row =
            document.createElement("tr");

        row.innerHTML = `

            <td>${index + 1}</td>

            <td>${customer.name}</td>

            <td>${customer.contact}</td>

            <td>${customer.bookings}</td>

            <td>${customer.guests}</td>

            <td>
                ₱${customer.spending.toLocaleString()}
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

                ${
                    customer.latest
                    ? new Date(
                        customer.latest
                    ).toLocaleString()
                    : "N/A"
                }

            </td>

        `;

        tableBody.appendChild(row);

    });

    // ================= UPDATE DASHBOARD =================

    document.getElementById(
        "totalCustomers"
    ).textContent =
        customerArray.length;

    document.getElementById(
        "vipCustomers"
    ).textContent =
        vipCount;

    document.getElementById(
        "repeatCustomers"
    ).textContent =
        repeatCount;

    document.getElementById(
        "topCustomer"
    ).textContent =
        highestSpender;

    document.getElementById(
        "highestSpender"
    ).textContent =
        highestSpender;

    document.getElementById(
        "mostBookings"
    ).textContent =
        mostBookings;

    document.getElementById(
        "averageGuests"
    ).textContent =

        customerArray.length > 0

        ? Math.round(
            totalGuests /
            customerArray.length
        )

        : 0;

}

// ================= REALTIME =================

fetchCustomers();

setInterval(fetchCustomers,3000);

// ================= SEARCH =================

document
.getElementById("searchCustomer")
?.addEventListener(
    "input",
    loadCustomers
);

// ================= FILTER =================

document
.getElementById("filterCustomer")
?.addEventListener(
    "change",
    loadCustomers
);

</script>

</body>
</html>