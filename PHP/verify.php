<?php
session_start();
include 'connect.php';

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);

    // Check if a matching verification token exists
    $query = "SELECT * FROM users WHERE verification_token = '$token' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user_data = mysqli_fetch_assoc($result);

        // Clean up parameters to switch verification flag settings to active
        $update_query = "UPDATE users SET is_verified = 1, verification_token = NULL WHERE Id = '{$user_data['Id']}'";

        if (mysqli_query($conn, $update_query)) {
            header("Location: ../HTML/login.html?success=" . urlencode("Email verified successfully! You can now log in."));
            exit();
        } else {
            header("Location: ../HTML/login.html?error=" . urlencode("An error occurred during verification. Please try again."));
            exit();
        }
    } else {
        header("Location: ../HTML/login.html?error=" . urlencode("Invalid or expired verification link."));
        exit();
    }
} else {
    header("Location: ../HTML/login.html?error=" . urlencode("No validation token was provided."));
    exit();
}
?>