// Get the form and Create Account button
const form = document.getElementById('loginForm');
const createBtn = document.querySelector('.btn-secondary');

// When the login form is submitted
form.addEventListener('submit', function(event) {
  event.preventDefault(); // prevent page reload

  // Get the input values
  const username = document.getElementById('username').value.trim();
  const password = document.getElementById('password').value.trim();
  const loginType = document.getElementById('loginType').value;

  // Check login type and credentials
  if (loginType === "admin") {
    if (username === "admin" && password === "123") {
      window.open("admin.html", "_self"); // redirect to admin page
    } else {
      alert("Invalid admin credentials!");
    }
  } 
  else if (loginType === "customer") {
    if (username === "costumer1" && password === "123") {
      window.open("book.html", "_self"); // redirect to customer home page
    } else {
      alert("Invalid customer credentials!");
    }
  } 
  else {
    alert("Please select login type!");
  }
});

// When "Create Account" button is clicked
createBtn.addEventListener('click', function() {
  window.open("register.html", "_self"); // redirect to registration page
});