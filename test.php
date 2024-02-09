<?php

$csvfile="/home/mathis/Downloads/Untitled spreadsheet - Sheet4.csv";


$filehandle = fopen($csvfile, 'r');
if ($filehandle !== false) {
    
    $csvdata = [];

    while (($row = fgetcsv($filehandle)) !== false) {
        $csvdata[] = $row;
    }

    fclose($filehandle);
    /*
    $c = 1;
    while ($csvdata[7][$c] !== null) {
        echo $csvdata[7][$c];
        $c += 1;
    }

    $playerstartinventory = $csvdata[0][1];
    $playerstartitems = explode('/', $playerstartinventory);
    foreach ($playerstartitems as $itemname) {
        $itemname = tokenize($itemname);
        $item = new Item();
    }
    */
    















} else {
    echo "Error opening CSV file.";
}

function tokenize($str) {
    $str = trim($str);
    $str = preg_replace('/\s+/', ' ', $str);
    $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
    $str = strtolower($str);
    return $str;
}

?>
