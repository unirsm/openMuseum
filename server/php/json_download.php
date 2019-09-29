<?php

$ini =  "http://lab.z14.it/giano/json.php";
$id =$_GET["id"];
error_reporting(E_ALL);ini_set('display_errors', 1);

$files = array("rfid","multimedia","bt_speaker","neopixelspot","tradfri","interaction");

foreach ($files as &$v) {

    $text = file_get_contents($ini.'?m='.$id.'&t='.$v);
    $file = fopen("json/".$v.".json", "w");
    echo "save file "."json/".$v.".json<br>";
    fwrite($file, $text);
    fclose($file);
}

?>
