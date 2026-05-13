<?php

$host="localhost";
$user="root";
$pass="";
$db="login";
$conn=new mysqli($host,$user,$pass,$db);
if($conn->connect_error){
    echo "Failed to connect DB".$conn->connect_error;
}

// connect.php  — place this in your PHP/ folder (or wherever your other files live)
// Adjust host / dbname / user / pass to match your local setup
 
$host   = 'localhost';
$dbname = 'login';          // your existing database
$user   = 'root';           // your phpMyAdmin username
$pass   = '';               // your phpMyAdmin password (blank on XAMPP default)
 
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'DB connection failed: ' . $e->getMessage()]));
}

?>