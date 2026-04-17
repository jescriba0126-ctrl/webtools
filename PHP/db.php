<?php
$conn = new mysqli("localhost", "root", "", "cubiertos");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>