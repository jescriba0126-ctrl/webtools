<?php
include 'db.php';

$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$occasion = $_POST['occasion'];
$guests = $_POST['guests'];
$datetime = $_POST['datetime'];
$package = $_POST['package'];
$message = $_POST['message'];

$amount = 0;
if ($package == "basic") $amount = 2000;
elseif ($package == "standard") $amount = 5000;
elseif ($package == "premium") $amount = 10000;

$sql = "INSERT INTO appointments 
(name,email,phone,occasion,guests,datetime,package,message,amount)
VALUES 
('$name','$email','$phone','$occasion','$guests','$datetime','$package','$message','$amount')";

if ($conn->query($sql)) {
    echo "<script>alert('Booking Successful!'); window.location='book.html';</script>";
} else {
    echo "Error: " . $conn->error;
}
?>
