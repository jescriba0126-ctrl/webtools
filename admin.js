
// dito nangyayare yung pag kuha ng elements galing sa forms sa booking page
const ordersTable = document.querySelector("#ordersTable tbody");
const historyTable = document.querySelector("#historyTable tbody");
const liveTab = document.getElementById("liveTab");
const historyTab = document.getElementById("historyTab");
const liveOrders = document.getElementById("liveOrders");
const orderHistory = document.getElementById("orderHistory");

// function neto para mag load galing sa local storage
let orders = JSON.parse(localStorage.getItem("orders")) || [];
let history = JSON.parse(localStorage.getItem("orderHistory")) || [];

// dito naka display yung live orders 
function renderOrders() {
  ordersTable.innerHTML = "";
  if (orders.length === 0) {
    ordersTable.innerHTML = `<tr><td colspan="7" class="empty">No live orders yet.</td></tr>`;
    return;
  }

  orders.forEach((o, i) => {
    const tr = document.createElement("tr");
    const service = o.service || "—";
    const amount = o.amount ? `₱${parseFloat(o.amount).toLocaleString()}` : "₱0";


    // dito nangyayare yung add orders sa admin
    tr.innerHTML = `
      <td>${o.ticket || "—"}</td>
      <td>${o.name || "—"}</td>
      <td>${o.contact || "—"}</td>
      <td>${service}</td>
      <td>${amount}</td>
      <td><span class="status ${o.status ? o.status.toLowerCase().replace(' ', '') : 'pending'}">${o.status || "Pending"}</span></td>
      <td>
        <button class="btn in-progress" onclick="updateStatus(${i}, 'In Progress')">In Progress</button>
        <button class="btn completed" onclick="updateStatus(${i}, 'Completed')">Complete</button>
        <button class="btn delete" onclick="deleteOrder(${i})">Delete</button>
      </td>
    `;
    ordersTable.appendChild(tr);
  });
}

// dito para sa history ng mga appointments or records
function renderHistory() {
  historyTable.innerHTML = "";
  if (history.length === 0) {
    historyTable.innerHTML = `<tr><td colspan="7" class="empty">No completed orders yet.</td></tr>`;
    return;
  }

  history.forEach((o, i) => {
    const service = o.service || "—";
    const amount = o.amount ? `₱${parseFloat(o.amount).toLocaleString()}` : "₱0";

    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${o.ticket || "—"}</td>
      <td>${o.name || "—"}</td>
      <td>${o.contact || "—"}</td>
      <td>${service}</td>
      <td>${amount}</td>
      <td><span class="status completed">Completed</span></td>
      <td><button class="btn delete" onclick="deleteHistory(${i})">Delete</button></td>
    `;
    historyTable.appendChild(tr);
  });
}


// dito yung button para sa complete orders
function updateStatus(index, newStatus) {
  orders[index].status = newStatus;

  if (newStatus === "Completed") {
    history.push(orders[index]);
    orders.splice(index, 1);
  }

  // dito nangyayare yung save sa local storage
  localStorage.setItem("orders", JSON.stringify(orders));
  localStorage.setItem("orderHistory", JSON.stringify(history));

  renderOrders();
  renderHistory();
}


// dito nangyayare yung delete orders
function deleteOrder(index) {
  if (confirm("Delete this live order?")) {
    orders.splice(index, 1);
    localStorage.setItem("orders", JSON.stringify(orders));
    renderOrders();
  }
}


function deleteHistory(index) {
  if (confirm("Delete this completed order?")) {
    history.splice(index, 1);
    localStorage.setItem("orderHistory", JSON.stringify(history));
    renderHistory();
  }
}



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



// dito yung log out section
function logout() {
  localStorage.removeItem("loggedInUser");
  alert("You have been logged out.");
  window.location.href = "login.html";
}

renderOrders();
renderHistory();
