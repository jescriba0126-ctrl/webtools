<?php
$names = array("Raffy", "Era", "Rommel", "Janice", "Joms");
echo "</BR>Using foreach loop: \n</BR>"; 
foreach ($names as $val) {
    echo "-".$val."</BR>";
}

$countnames = count ($names);
echo "</BR>Array name_list has Scountnames elements\n</BR>"; 
echo "</BR>Using for 1oop: \n</BR>";
$x = 0;
for ($x; $x < $countnames; $x++){
echo "-" .$names [$x]. "</BR>";
}





$married = array(
    "chris" => "Era",
    "Jeff" => "Lilibeth",
    "Rocky" => "Joms",
    "Daniel" => "Katryn",
    "Enrique" => "Liza"
);

echo "</br>Using foreach loop: </BR>";

foreach ($married as $val => $val_value) {
    echo $val . " and " . $val_value . " are married</BR>";
}

echo "</BR>Using for loop: </BR>";

$keys = array_keys($married);
$round = count($married) - 1;

for ($i = $round; $i >= 0; $i--) {
    echo $keys[$i] . ' & ' . $married[$keys[$i]] . "</BR>";
}

?>