<<<<<<< HEAD
const container = document.querySelector('.container');
const registerBtn = document.querySelector('.register-btn');
const loginBtn = document.querySelector('.login-btn');

// TOGGLE ANIMATION
if (registerBtn) {
  registerBtn.addEventListener('click', () => {
    container.classList.add('active');
  });
}

if (loginBtn) {
  loginBtn.addEventListener('click', () => {
    container.classList.remove('active');
  });
}

// LOGIN SYSTEM
const form = document.getElementById('loginForm');

form.addEventListener('submit', function(event) {
  event.preventDefault();

  const username = document.getElementById('username').value.trim();
  const password = document.getElementById('password').value.trim();
  const loginType = document.getElementById('loginType').value;

  if (loginType === "admin") {
    if (username === "admin" && password === "123") {
      window.location.href = "admin.html";
    } else {
      alert("Invalid admin credentials!");
    }
  } 
  else if (loginType === "customer") {
    if (username === "customer1" && password === "123") {
      window.location.href = "book.html";
    } else {
      alert("Invalid customer credentials!");
    }
  } 
  else {
    alert("Please select login type!");
  }
=======
document.addEventListener("DOMContentLoaded", function () {

  const container = document.querySelector('.container');
  const registerBtn = document.querySelector('.register-btn');
  const loginBtn = document.querySelector('.login-btn');

  if (registerBtn) {
    registerBtn.addEventListener('click', () => {
      container.classList.add('active');
    });
  }

  if (loginBtn) {
    loginBtn.addEventListener('click', () => {
      container.classList.remove('active');
    });
  }

  const form = document.getElementById('loginForm');

  form.addEventListener('submit', function(event) {
    event.preventDefault();

    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    const loginType = document.getElementById('loginType').value;

    if (loginType === "admin") {
      if (username === "admin" && password === "123") {
        window.location.href = "admin.html";
      } else {
        alert("Invalid admin credentials!");
      }
    } 
    else if (loginType === "customer") {
      if (username === "customer1" && password === "123") {
        window.location.href = "../HTML/book.html";
      } else {
        alert("Invalid customer credentials!");
      }
    } 
    else {
      alert("Please select login type!");
    }
  });

>>>>>>> 2662f06 (sql)
});