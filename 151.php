<?php

$country = array(
    "Philippines"=>"Manila",
    "Bahrain"=>"Manama",
    "Belgium"=>"Brussels",
    "Brunei"=>"Bandar Seri Begawan",
    "Finland"=>"Helsinki",
    "France"=>"Paris",
    "Cambodia"=>"Phnom Penh",
    "Canada"=>"Ottawa",
    "Germany"=>"Berlin",
    "China"=>"Beijing",
    "Ireland"=>"Dublin",
    "Netherlands"=>"Amsterdam",
    "India"=>"New Delhi",
    "Spain"=>"Madrid",
    "Sweden"=>"Stockholm",
    "United Kingdom"=>"London",
    "Indonesia"=>"Jakarta",
    "Japan"=>"Tokyo",
    "Czech Republic"=>"Prague",
    "South Korea"=>"Seoul",
    "Hungary"=>"Budapest",
    "Latvia"=>"Riga",
    "Malaysia"=>"Kuala Lumpur",
    "Nigeria"=>"Abuja",
    "Vietnam"=>"Hanoi"
);

echo "<h2>a) Ascending Order of Country</h2>";
ksort($country);
foreach($country as $c => $cap){
    echo $c . " - " . $cap . "<br>";
}

echo "<h2>b) Ascending Order of Capital</h2>";
asort($country);
foreach($country as $c => $cap){
    echo $c . " - " . $cap . "<br>";
}

echo "<h2>c) Descending Order of Country</h2>";
krsort($country);
foreach($country as $c => $cap){
    echo $c . " - " . $cap . "<br>";
}

echo "<h2>d) Descending Order of Capital</h2>";
arsort($country);
foreach($country as $c => $cap){
    echo $c . " - " . $cap . "<br>";
}

?>