
  // ================= ELEMENTS =================
  const ordersTable = document.querySelector("#ordersTable tbody");
  const historyTable = document.querySelector("#historyTable tbody");

  const liveTab = document.getElementById("liveTab");
  const historyTab = document.getElementById("historyTab");
  const liveOrders = document.getElementById("liveOrders");
  const orderHistory = document.getElementById("orderHistory");

  // ================= GLOBAL STATE =================
  window.orders = JSON.parse(localStorage.getItem("orders")) || [];
  window.history = JSON.parse(localStorage.getItem("orderHistory")) || [];


  // ================= MASTER RENDER =================
  function renderAll(){
      renderOrders();
      renderHistory();
  }


  // ================= DASHBOARD =================
  function updateDashboard(stats){

      document.getElementById("totalBookings").textContent = stats.total;
      document.getElementById("pendingBookings").textContent = stats.pending;
      document.getElementById("approvedBookings").textContent = stats.approved;
      document.getElementById("completedBookings").textContent = stats.completed;
      document.getElementById("totalRevenue").textContent = stats.revenue;

      document.getElementById("pendingFlow").textContent = stats.pending;
      document.getElementById("approvedFlow").textContent = stats.approved;
      document.getElementById("completedFlow").textContent = stats.completed;
  }


  // ================= LIVE ORDERS =================
  function renderOrders(){

      ordersTable.innerHTML = "";

      let stats = {
          total: 0,
          pending: 0,
          approved: 0,
          completed: 0,
          cancelled: 0,
          revenue: 0
      };

      let search = (document.getElementById("searchOrder")?.value || "").toLowerCase();
      let filter = document.getElementById("filterStatus")?.value || "all";

      if(window.orders.length === 0){
          ordersTable.innerHTML = `
              <tr><td colspan="8" class="empty">No live orders yet.</td></tr>
          `;
          updateDashboard(stats);
          return;
      }

      window.orders.forEach((o,i)=>{

          if(!o.name.toLowerCase().includes(search)) return;
          if(filter !== "all" && o.status !== filter) return;

          stats.total++;

          if(o.status === "Pending") stats.pending++;
          if(o.status === "Approved") stats.approved++;
          if(o.status === "Completed"){
              stats.completed++;
              stats.revenue += Number(o.amount || 0);
          }
          if(o.status === "Cancelled") stats.cancelled++;

          const tr = document.createElement("tr");

          tr.innerHTML = `
              <td>${o.ticket || "—"}</td>
              <td>${o.name || "—"}</td>
              <td>${o.contact || "—"}</td>
              <td>${o.service || "—"}</td>
              <td>${o.guests || 0}</td>
              <td>₱${o.amount || 0}</td>

              <td>
                  <span class="status ${o.status ? o.status.toLowerCase() : 'pending'}">
                      ${o.status || "Pending"}
                  </span>
              </td>

              <td>
                  <button class="btn approve" onclick="updateStatus(${i},'Approved')">Approve</button>
                  <button class="btn completed" onclick="updateStatus(${i},'Completed')">Done</button>
                  <button class="btn cancel" onclick="cancelOrder(${i})">Cancel</button>
                  <button class="btn delete" onclick="deleteOrder(${i})">Cancel Order</button>
              </td>
          `;

          ordersTable.appendChild(tr);
      });

      updateDashboard(stats);
  }


  // ================= HISTORY =================
  function renderHistory(){

      historyTable.innerHTML = "";

      if(window.history.length === 0){
          historyTable.innerHTML = `
              <tr><td colspan="8" class="empty">No completed orders yet.</td></tr>
          `;
          return;
      }

      window.history.forEach((o,i)=>{

          const tr = document.createElement("tr");

          tr.innerHTML = `
              <td>${o.ticket || "—"}</td>
              <td>${o.name || "—"}</td>
              <td>${o.contact || "—"}</td>
              <td>${o.service || "—"}</td>
              <td>${o.guests || 0}</td>
              <td>₱${o.amount || 0}</td>

              <td><span class="status completed">Completed</span></td>

              <td>
                  <button class="btn delete" onclick="deleteHistory(${i})">Delete</button>
              </td>
          `;

          historyTable.appendChild(tr);
      });
  }


  // ================= ACTIONS =================

  // APPROVE / COMPLETE
  function updateStatus(index, newStatus){

      window.orders[index].status = newStatus;

      if(newStatus === "Completed"){
          window.history.push(window.orders[index]);
          window.orders.splice(index,1);
      }

      localStorage.setItem("orders", JSON.stringify(window.orders));
      localStorage.setItem("orderHistory", JSON.stringify(window.history));

      renderAll();
  }


  // CANCEL ORDER
  function cancelOrder(index){

      window.orders[index].status = "Cancelled";

      localStorage.setItem("orders", JSON.stringify(window.orders));

      renderAll();
  }


  // DELETE LIVE ORDER
  function deleteOrder(index){

      if(confirm("Delete this order permanently?")){

          window.orders.splice(index,1);

          localStorage.setItem("orders", JSON.stringify(window.orders));

          renderAll();
      }
  }


  // DELETE HISTORY
  function deleteHistory(index){

      if(confirm("Delete this record?")){

          window.history.splice(index,1);

          localStorage.setItem("orderHistory", JSON.stringify(window.history));

          renderAll();
      }
  }


  // ================= TABS =================
  liveTab.onclick = () => {
      liveTab.classList.add("active");
      historyTab.classList.remove("active");
      liveOrders.style.display = "block";
      orderHistory.style.display = "none";
  };

  historyTab.onclick = () => {
      historyTab.classList.add("active");
      liveTab.classList.remove("active");
      liveOrders.style.display = "none";
      orderHistory.style.display = "block";
  };


  // ================= SEARCH + FILTER =================
  document.getElementById("searchOrder")?.addEventListener("input", renderOrders);
  document.getElementById("filterStatus")?.addEventListener("change", renderOrders);


  // ================= INIT =================
  renderAll();

  // ================= STATE =================
let orders = JSON.parse(localStorage.getItem("orders")) || [];

// ================= ELEMENTS =================
const ordersTable = document.querySelector("#ordersTable tbody");

// ================= SAVE =================
function save(){
    localStorage.setItem("orders", JSON.stringify(orders));
    renderAll();
}

// ================= MAIN =================
function renderAll(){
    renderOrders();
    updateDashboard();
}

// ================= DASHBOARD =================
function updateDashboard(){

    let stats = {
        total: orders.length,
        pending: 0,
        approved: 0,
        completed: 0,
        revenue: 0
    };

    orders.forEach(o => {
        if(o.status === "Pending") stats.pending++;
        if(o.status === "Approved") stats.approved++;
        if(o.status === "Completed"){
            stats.completed++;
            stats.revenue += Number(o.amount || 0);
        }
    });

    // ================= TOP STATS =================
    document.getElementById("stats_totalBookings").textContent = stats.total;
    document.getElementById("stats_pendingBookings").textContent = stats.pending;
    document.getElementById("stats_completedBookings").textContent = stats.completed;
    document.getElementById("stats_totalRevenue").textContent = stats.revenue;

    // ================= OVERVIEW =================
    document.getElementById("ov_totalBookings").textContent = stats.total;
    document.getElementById("ov_pendingBookings").textContent = stats.pending;
    document.getElementById("ov_approvedBookings").textContent = stats.approved;
    document.getElementById("ov_completedBookings").textContent = stats.completed;
    document.getElementById("ov_totalRevenue").textContent = stats.revenue;

    // ================= FLOW =================
    document.getElementById("ov_pendingFlow").textContent = stats.pending;
    document.getElementById("ov_approvedFlow").textContent = stats.approved;
    document.getElementById("ov_completedFlow").textContent = stats.completed;

    // ================= CAPACITY =================
    const booked = orders.reduce((sum,o)=> sum + Number(o.guests || 0),0);
    const max = 100;

    document.getElementById("currentBooked").textContent = booked;
    document.getElementById("slotsAvailable").textContent = max - booked;

    document.getElementById("capacityFill").style.width =
        Math.min((booked / max) * 100, 100) + "%";
}

// ================= RENDER TABLE =================
function renderOrders(){

    ordersTable.innerHTML = "";

    if(orders.length === 0){
        ordersTable.innerHTML = `
            <tr><td colspan="8">No orders found</td></tr>
        `;
        return;
    }

    orders.forEach((o,i)=>{

        const tr = document.createElement("tr");

        tr.innerHTML = `
            <td>${i+1}</td>
            <td>${o.name}</td>
            <td>${o.contact}</td>
            <td>${o.service}</td>
            <td>${o.guests || 0}</td>
            <td>₱${o.amount || 0}</td>

            <td>
                <span class="status ${o.status?.toLowerCase()}">
                    ${o.status || "Pending"}
                </span>
            </td>

            <td>
                <button class="btn approve" onclick="approve(${i})">Approve</button>
                <button class="btn done" onclick="complete(${i})">Done</button>
                <button class="btn cancel" onclick="cancel(${i})">Cancel</button>
                <button class="btn delete" onclick="remove(${i})">Delete</button>
            </td>
        `;

        ordersTable.appendChild(tr);
    });
}

// ================= ACTIONS =================
function approve(i){
    orders[i].status = "Approved";
    save();
}

function complete(i){
    orders[i].status = "Completed";
    save();
}

function cancel(i){
    orders[i].status = "Cancelled";
    save();
}

function remove(i){
    if(confirm("Delete this order?")){
        orders.splice(i,1);
        save();
    }
}

// ================= SEARCH =================
document.getElementById("searchOrder").addEventListener("input", renderOrders);
document.getElementById("filterStatus").addEventListener("change", renderOrders);

// ================= INIT =================
renderAll();

// ================= STARTUP LOADER CLEANUP =================
window.addEventListener('load', function() {
    // Wait 2.3 seconds (1.5s delay + 0.8s fade out time) to match the CSS
    setTimeout(function() {
        const loader = document.getElementById('startup-loader');
        if (loader) {
            // Completely removes the loader so you can click the dashboard
            loader.style.display = 'none'; 
        }
    }, 2300); 
});