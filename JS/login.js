// SHOW/HIDE PASSWORD
function togglePassword(inputId, icon) {
  const inp = document.getElementById(inputId);
  const isHidden = inp.type === "password";
  inp.type = isHidden ? "text" : "password";
  icon.className = isHidden ? "bx bxs-lock-open-alt" : "bx bxs-lock-alt";
}

const container = document.querySelector(".container");
const registerBtn = document.querySelector(".register-btn");
const loginBtn = document.querySelector(".login-btn");

// TOGGLE ANIMATION
if (registerBtn) {
  registerBtn.addEventListener("click", () => {
    container.classList.add("active");
  });
}

if (loginBtn) {
  loginBtn.addEventListener("click", () => {
    container.classList.remove("active");
  });
}

// LOGIN SYSTEM
const form = document.getElementById("loginForm");

form.addEventListener("submit", function (event) {
  event.preventDefault();

  const username = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value.trim();
  const loginType = document.getElementById("loginType").value;

  if (loginType === "admin") {
    if (username === "admin" && password === "123") {
      window.location.href = "admin.html";
    } else {
      alert("Invalid admin credentials!");
    }
  } else if (loginType === "customer") {
    if (username === "customer1" && password === "123") {
      window.location.href = "book.html";
    } else {
      alert("Invalid customer credentials!");
    }
  } else {
    alert("Please select login type!");
  }
});
