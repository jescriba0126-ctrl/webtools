<?php
ob_start();
session_start();
include 'connect.php';
include 'Mailer.php'; // 1. Added mail helper include

// ================= SIGN UP =================
if (isset($_POST['signUp'])) {

    $firstName       = $_POST['firstName'];
    $lastName        = $_POST['lastName'];
    $gender          = $_POST['gender'];
    $email           = $_POST['email'];
    $password        = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    if (empty($password) || empty($confirmPassword)) {
        header("Location: ../HTML/login.html?error=Please+fill+all+password+fields&panel=register");
        exit();
    }

    if ($password !== $confirmPassword) {
        header("Location: ../HTML/login.html?error=Passwords+do+not+match&panel=register");
        exit();
    }

    // CHECK EMAIL EXISTS
    $checkResult = mysqli_query($conn, "SELECT Id FROM users WHERE email = '$email'");

    if (mysqli_num_rows($checkResult) > 0) {
        header("Location: ../HTML/login.html?error=Email+already+exists&panel=register");
        exit();
    }

    // MD5 — matches existing database hashes
    $hashedPassword = md5($password);

    // 2. GENERATE COMPLIANT SECURITY TOKENS AND UPDATE STRUCTURAL INSERT VALUES
    $token = bin2hex(random_bytes(50));
    
    $insertQuery = "INSERT INTO users (firstName, lastName, gender, email, password, role, is_verified, verification_token)
                    VALUES ('$firstName', '$lastName', '$gender', '$email', '$hashedPassword', 'user', 0, '$token')";

    if (mysqli_query($conn, $insertQuery)) {
        // Dispatch the email notification string immediately following database persistence
        sendVerificationEmail($email, $firstName, $token);
        
        header("Location: ../HTML/login.html?success=" . urlencode("Account created! Check your email to verify your registration."));
        exit();
    } else {
        header("Location: ../HTML/login.html?error=Registration+failed.+Try+again.&panel=register");
        exit();
    }
}

// ================= SIGN IN =================
if (isset($_POST['signIn'])) {

    $email          = $_POST['email'];
    $password       = $_POST['password'];
    $hashedPassword = md5($password);

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' AND password = '$hashedPassword'");

    if (mysqli_num_rows($result) > 0) {

        $row = mysqli_fetch_assoc($result);

        // 3. SECURE PASSIVE RESTRICTION BLOCKING UNVERIFIED ENTRIES
        if ((int)$row['is_verified'] === 0) {
            header("Location: ../HTML/login.html?error=" . urlencode("Please verify your email address before signing in."));
            exit();
        }

        session_regenerate_id(true);

        $_SESSION['id']             = $row['Id'];
        $_SESSION['email']          = $row['email'];
        $_SESSION['firstName']      = $row['firstName'];
        $_SESSION['role']           = $row['role'];
        $_SESSION['just_logged_in'] = true;

        if ($row['role'] === 'admin') {
            header("Location: admin.php");
            exit();
        } else {
            header("Location: ../HTML/menu.html"); 
            exit();
        }
    } else {
        header("Location: ../HTML/login.html?error=Incorrect+Email+or+Password");
        exit();
    }
}
?>