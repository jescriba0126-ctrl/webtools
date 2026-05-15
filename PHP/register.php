<?php
ob_start();
session_start();
include 'connect.php';

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

    $insertQuery = "INSERT INTO users (firstName, lastName, gender, email, password, role)
                    VALUES ('$firstName', '$lastName', '$gender', '$email', '$hashedPassword', 'user')";

    if (mysqli_query($conn, $insertQuery)) {
        header("Location: ../HTML/login.html?success=Account+created!+Please+log+in.");
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

        session_regenerate_id(true);

        $_SESSION['id']             = $row['Id'];
        $_SESSION['email']          = $row['email'];
        $_SESSION['firstName']      = $row['firstName'];
        $_SESSION['role']           = $row['role'];
        $_SESSION['just_logged_in'] = true;

        if ($row['role'] === 'admin') {
            header("Location: admin.php");
            exit();
        }

        header("Location: homepage.php");
        exit();

    } else {
        header("Location: ../HTML/login.html?error=Incorrect+email+or+password");
        exit();
    }
}
?>