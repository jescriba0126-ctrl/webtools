<?php

include("connect.php");

header("Content-Type: application/json");

$sql = "
    SELECT
        id,
        name,
        occasion,
        guests,
        special_notes,
        booking_datetime,
        status
    FROM bookings
    ORDER BY booking_datetime DESC
";

$result = mysqli_query($conn, $sql);

$notes = [];

while($row = mysqli_fetch_assoc($result)){
    $notes[] = $row;
}

echo json_encode($notes);

?>