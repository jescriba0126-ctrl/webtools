// dito yung para sa log in page 
const form = document.getElementById('loginForm');
const createBtn = document.querySelector('.btn-secondary');

// dito nangyayare pag nag susubmit ng log in
form.addEventListener('submit', function(event) {
  event.preventDefault(); // prevent page reload

  // dito kinukuha yung log in information 
  const username = document.getElementById('username').value.trim();
  const password = document.getElementById('password').value.trim();
  const loginType = document.getElementById('loginType').value;

  // dito mo makikita kung admin yung mag lolog in
  if (loginType === "admin") {
    if (username === "admin" && password === "123") {
      window.open("admin.html", "_self"); // mapupunta sa admin page 
    } else {
      alert("Invalid admin credentials!");
    }
  } 
  else if (loginType === "customer") {
    if (username === "customer1" && password === "123") {
<<<<<<< HEAD
      window.open("book.html", "_self"); // redirect to customer home page
=======
      window.open("book.html", "_self"); // mapupunta sa customer page
>>>>>>> e35a2d3fa3f6a43f86f3b892d529d72fdcb4158c
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