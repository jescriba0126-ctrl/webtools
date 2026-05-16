
<?php
$city = array("Makati", "Taguig", "Manila");

echo "</BR>First City: " . $city[0] . "\n";
$city[1] = "Pasig";
echo "</BR>Modified array: "; print_r($city);
$city[] = "Mandaluyong";
echo "</BR>Array after adding Mandaluyong: "; 
print_r($city);
unset ($city[2]);
echo "</BR>Array after removing Manila: "; 
print_r($city);
echo "</BR>Looping through the array: \n";
foreach ($city as $cities) {
echo $cities . "\n";
}

sort($city);
echo "</BR>Sorted array: "; print_r($city);
if (in_array("Makati", $city)) {
echo "</BR>Makati exist\n";
} else {
echo "</BR>Makati does not exist\n";
}
$length = count ($city);
echo "</BR>Length: " . $length . "\n";
?>
