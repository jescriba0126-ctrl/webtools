// ================= GLOBAL STATE =================
let orders = JSON.parse(localStorage.getItem("orders")) || [];
const ordersTable = document.querySelector("#ordersTable tbody");

// ================= MASTER RENDER FUNCTION =================
function loadOrders() {
    if (!ordersTable) return;
    ordersTable.innerHTML = "";

    let totalRevenue = 0;
    let pending = 0;
    let approved = 0;
    let completed = 0;
    let totalGuests = 0;

    const searchInput = document.getElementById("searchOrder");
    const filterInput = document.getElementById("filterStatus");

    const searchValue = searchInput ? searchInput.value.toLowerCase() : "";
    const filterValue = filterInput ? filterInput.value : "all";

    orders.forEach((order, index) => {
        // 1. Filter out orders that don't match the search or dropdown
        if (order.name && !order.name.toLowerCase().includes(searchValue)) return;
        if (filterValue !== "all" && order.status !== filterValue) return;

        // 2. Count the statuses
        if (order.status === "Pending") pending++;
        if (order.status === "Approved") approved++;
        if (order.status === "Completed") {
            completed++;
            totalRevenue += Number(order.amount || 0);
        }
        
        //ONLY count guests who are still waiting or currently eating
        if (order.status === "Pending" || order.status === "Approved") {
            totalGuests += Number(order.guests || 0);
        }
        // 3. Build the Table Row
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${order.name || "—"}</td>
            <td>${order.contact || "—"}</td>
            <td>${order.service || "—"}</td>
            <td>${order.guests || 0}</td>
            <td>₱${order.amount || 0}</td>
            <td>
                <span class="status ${order.status ? order.status.toLowerCase() : 'pending'}">
                    ${order.status || "Pending"}
                </span>
            </td>
            <td>
                <button class="btn approve" onclick="approveOrder(${index})">Approve</button>
                <button class="btn completed" onclick="completeOrder(${index})">Done</button>
                <button class="btn delete" onclick="deleteOrder(${index})">Delete</button>
            </td>
        `;
        ordersTable.appendChild(row);
    });

    // ================= UPDATE ALL DASHBOARD NUMBERS =================
    
    // Total Bookings
    const statsTotal = document.getElementById("stats_totalBookings");
    const ovTotal = document.getElementById("ov_totalBookings");
    if (statsTotal) statsTotal.textContent = orders.length;
    if (ovTotal) ovTotal.textContent = orders.length;

    // Pending
    const statsPend = document.getElementById("stats_pendingBookings");
    const ovPend = document.getElementById("ov_pendingBookings");
    const flowPend = document.getElementById("ov_pendingFlow");
    if (statsPend) statsPend.textContent = pending;
    if (ovPend) ovPend.textContent = pending;
    if (flowPend) flowPend.textContent = pending;

    // Approved
    const ovApp = document.getElementById("ov_approvedBookings");
    const flowApp = document.getElementById("ov_approvedFlow");
    if (ovApp) ovApp.textContent = approved;
    if (flowApp) flowApp.textContent = approved;

    // Completed
    const statsComp = document.getElementById("stats_completedBookings");
    const ovComp = document.getElementById("ov_completedBookings");
    const flowComp = document.getElementById("ov_completedFlow");
    if (statsComp) statsComp.textContent = completed;
    if (ovComp) ovComp.textContent = completed;
    if (flowComp) flowComp.textContent = completed;

    // Total Revenue
    const statsRev = document.getElementById("stats_totalRevenue");
    const ovRev = document.getElementById("ov_totalRevenue");
    if (statsRev) statsRev.textContent = totalRevenue;
    if (ovRev) ovRev.textContent = totalRevenue;

    // ================= UPDATE CAPACITY BAR =================
    let maxCap = parseInt(localStorage.getItem("dailyCapacity")) || 100;

    const maxCapEl = document.getElementById("maxCapacity");
    if (maxCapEl) maxCapEl.textContent = maxCap;

    const curBookedEl = document.getElementById("currentBooked");
    if (curBookedEl) curBookedEl.textContent = totalGuests;

    const availEl = document.getElementById("slotsAvailable");
    if (availEl) availEl.textContent = maxCap - totalGuests;

    const fillEl = document.getElementById("capacityFill");
    if (fillEl) {
        let percentage = (totalGuests / maxCap) * 100;
        if (percentage > 100) percentage = 100;
        fillEl.style.width = percentage + "%";
    }
}

// ================= BUTTON ACTIONS =================

window.approveOrder = function(index) {
    orders[index].status = "Approved";
    localStorage.setItem("orders", JSON.stringify(orders));
    loadOrders();
};

window.completeOrder = function(index) {
    orders[index].status = "Completed";
    localStorage.setItem("orders", JSON.stringify(orders));
    loadOrders();
};

window.deleteOrder = function(index) {
    if (confirm("Are you sure you want to delete this booking?")) {
        orders.splice(index, 1);
        localStorage.setItem("orders", JSON.stringify(orders));
        loadOrders();
    }
};

window.setNewLimit = function() {
    let currentLimit = parseInt(localStorage.getItem("dailyCapacity")) || 100;
    let input = prompt("Enter new maximum guests per day:", currentLimit);

    if (input !== null) {
        let newLimit = parseInt(input);
        if (!isNaN(newLimit) && newLimit > 0) {
            localStorage.setItem("dailyCapacity", newLimit);
            loadOrders(); 
        } else {
            alert("Please enter a valid number greater than 0.");
        }
    }
};

// ================= EVENT LISTENERS =================
const searchOrder = document.getElementById("searchOrder");
const filterStatus = document.getElementById("filterStatus");

if (searchOrder) searchOrder.addEventListener("input", loadOrders);
if (filterStatus) filterStatus.addEventListener("change", loadOrders);

// ================= INIT & LOADER =================
loadOrders();

window.addEventListener('load', function() {
    setTimeout(function() {
        const loader = document.getElementById('startup-loader');
        if (loader) loader.style.display = 'none'; 
    }, 2300); 
});

// ================= AUTO REFRESH (REAL-TIME) =================

// 1. Instantly update the dashboard if a booking is made in another tab
window.addEventListener('storage', function() {
    // Re-fetch the freshest data from the browser's memory
    orders = JSON.parse(localStorage.getItem("orders")) || [];
    // Redraw the dashboard
    loadOrders();
});

// 2. Silently check for new data every 5 seconds (5000 milliseconds)
setInterval(function() {
    orders = JSON.parse(localStorage.getItem("orders")) || [];
    loadOrders();
}, 5000);

// ================= CALENDAR =================
let nav = 0; // Tracks which month we are looking at

function renderCalendar() {
    const calendarGrid = document.getElementById('calendarGrid');
    const monthDisplay = document.getElementById('monthDisplay');
    
    if (!calendarGrid || !monthDisplay) return;

    const dt = new Date();
    
    // Adjust the month based on the Next/Prev buttons
    if (nav !== 0) {
        dt.setMonth(new Date().getMonth() + nav);
    }

    const month = dt.getMonth();
    const year = dt.getFullYear();

    const firstDayOfMonth = new Date(year, month, 1);
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const paddingDays = firstDayOfMonth.getDay();

    monthDisplay.textContent = dt.toLocaleDateString('en-us', { month: 'long', year: 'numeric' });
    calendarGrid.innerHTML = '';

    // 1. Group all orders by their specific date
    const ordersByDate = {};
    orders.forEach(order => {
        if (order.datetime) {
            const orderDate = new Date(order.datetime);
            // Create a unique key for the dictionary like "2026-4-8" (Year-Month-Day)
            const dateKey = `${orderDate.getFullYear()}-${orderDate.getMonth()}-${orderDate.getDate()}`;
            
            if (!ordersByDate[dateKey]) {
                ordersByDate[dateKey] = [];
            }
            ordersByDate[dateKey].push(order);
        }
    });

    // 2. Create the calendar squares
    for(let i = 1; i <= paddingDays + daysInMonth; i++) {
        const daySquare = document.createElement('div');
        
        if (i > paddingDays) {
            const dayNumber = i - paddingDays;
            daySquare.textContent = dayNumber;
            
            // Highlight Today
            if (nav === 0 && dayNumber === new Date().getDate()) {
                daySquare.classList.add('active-day');
            }

            // 3. Check if this specific day has any bookings
            const currentSquareKey = `${year}-${month}-${dayNumber}`;
            const daysBookings = ordersByDate[currentSquareKey];

            if (daysBookings && daysBookings.length > 0) {
                // Add the orange dot and text (matching your reference image!)
                const indicator = document.createElement('div');
                indicator.style.fontSize = '0.75rem';
                indicator.style.color = '#bc6c25'; // Your theme's orange
                indicator.style.fontWeight = '600';
                indicator.style.marginTop = '6px';
                indicator.textContent = `● ${daysBookings.length} Booking${daysBookings.length > 1 ? 's' : ''}`;
                daySquare.appendChild(indicator);

                // Add a nice border so the date stands out
                daySquare.style.border = '2px solid rgba(188, 108, 37, 0.3)';
                daySquare.style.cursor = 'pointer';

                // 4. Add the click event to show customer details
                daySquare.addEventListener('click', () => {
                    let details = `📅 Reservations for ${monthDisplay.textContent.split(' ')[0]} ${dayNumber}, ${year}:\n\n`;
                    
                    daysBookings.forEach((b, index) => {
                        // Extract just the time (e.g., "6:30 PM")
                        const timeStr = new Date(b.datetime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                        
                        details += `${index + 1}. ${b.name} @ ${timeStr}\n`;
                        details += `   Contact: ${b.contact}\n`;
                        details += `   Service: ${b.service} (${b.guests} guests)\n`;
                        details += `   Status: ${b.status}\n\n`;
                    });
                    
                    alert(details);
                });
            }
        }
        
        calendarGrid.appendChild(daySquare);
    }
    
}   
// Connect the Previous and Next buttons for the calendar
document.getElementById('prevMonth')?.addEventListener('click', () => {
    nav--;
    renderCalendar();
});

document.getElementById('nextMonth')?.addEventListener('click', () => {
    nav++;
    renderCalendar();
});

// Run the calendar once so it shows up immediately
renderCalendar();