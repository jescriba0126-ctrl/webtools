<?php
ob_start();
session_start();
include 'connect.php';


// ================= SIGN UP =================
if(isset($_POST['signUp'])){

    $firstName       = mysqli_real_escape_string($conn, $_POST['firstName']);
    $lastName        = mysqli_real_escape_string($conn, $_POST['lastName']);
    $gender          = mysqli_real_escape_string($conn, $_POST['gender']);
    $email           = mysqli_real_escape_string($conn, $_POST['email']);
    $password        = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // CHECK PASSWORD MATCH
    if($password !== $confirmPassword){
        die("Passwords do not match!");
    }

    // ENCRYPT PASSWORD
    $password = md5($password);

    // CHECK EMAIL
    $checkEmail = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($checkEmail);

    if($result->num_rows > 0){
        die("Email Already Exists!");
    }

    // INSERT USER
    $insertQuery = "
        INSERT INTO users
        (firstName, lastName, gender, email, password, role)
        VALUES
        ('$firstName', '$lastName', '$gender', '$email', '$password', 'user')
    ";

    if($conn->query($insertQuery) == TRUE){

        header("Location: http://localhost/webtools-main/HTML/login.html");
        exit();

    } else {

        die("Error: " . $conn->error);

    }
}



// ================= SIGN IN =================
if(isset($_POST['signIn'])){

    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = md5($_POST['password']);

    $sql = "
        SELECT * FROM users
        WHERE email='$email'
        AND password='$password'
    ";

    $result = $conn->query($sql);

    // LOGIN SUCCESS
    if($result->num_rows > 0){

        $row = $result->fetch_assoc();

        // STORE SESSION
        $_SESSION['email']     = $row['email'];
        $_SESSION['firstName'] = $row['firstName'];
        $_SESSION['role']      = $row['role'];

        // ================= ADMIN =================
        if($row['role'] == 'admin'){

            header("Location: http://localhost/webtools-main/PHP/admin.php");
            exit();

        }

        // ================= USER =================
        else{

            header("Location: http://localhost/webtools-main/PHP/profile.php");
            exit();

        }

    }

    // LOGIN FAILED
    else{

        die("Incorrect Email or Password!");

    }
}
?>