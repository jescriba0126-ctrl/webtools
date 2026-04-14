<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Page</title>

    <!-- BOXICONS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <!-- FONT AWESOME (for icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- YOUR CSS -->
    <link rel="stylesheet" href="../CSS/login.css">
</head>

<body>

<div class="container">

    <!-- SIGN IN -->
    <div class="form-box login">
        <form method="post" action="register.php">
            <h1>Sign In</h1>

            <div class="input-box">
                <input type="email" name="email" placeholder="Email" required>
                <i class="fas fa-envelope"></i>
            </div>

            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class="fas fa-lock"></i>
            </div>

            <button type="submit" class="btn" name="signIn">Sign In</button>
        </form>
    </div>

    <!-- SIGN UP -->
    <div class="form-box register">
        <form method="post" action="register.php">
            <h1>Register</h1>

            <div class="input-box">
                <input type="text" name="fName" placeholder="First Name" required>
                <i class="fas fa-user"></i>
            </div>

            <div class="input-box">
                <input type="text" name="lName" placeholder="Last Name" required>
                <i class="fas fa-user"></i>
            </div>

            <div class="input-box">
                <input type="email" name="email" placeholder="Email" required>
                <i class="fas fa-envelope"></i>
            </div>

            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class="fas fa-lock"></i>
            </div>

            <button type="submit" class="btn" name="signUp">Sign Up</button>
        </form>
    </div>

    <!-- TOGGLE PANEL -->
    <div class="toggle-box">

        <div class="toggle-panel toggle-left">
            <h2>Welcome Back!</h2>
            <p>Don't have an account?</p>
            <button class="btn" id="showRegister">Sign Up</button>
        </div>

        <div class="toggle-panel toggle-right">
            <h2>Hello, Friend!</h2>
            <p>Already have an account?</p>
            <button class="btn" id="showLogin">Sign In</button>
        </div>

    </div>

</div>

<!-- SCRIPT -->
<script>
const container = document.querySelector('.container');

document.getElementById('showRegister').onclick = () => {
    container.classList.add('active');
};

document.getElementById('showLogin').onclick = () => {
    container.classList.remove('active');
};
</script>

</body>
</html>