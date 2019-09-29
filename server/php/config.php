<?php
#system
$secret ="83sa43$&478s235dsd9833423sda3325989";
$name = "Area Gestione nodi museo";
$path = "http://192.168.1.1/";
$debug = false;
$mediaGallery = false;

#database Mysql
$db_server ="localhost";
$db_user = "default";
$db_pass = 'password';
$db_database ="nome_db";

#tiles configuration
//$tile[0]=array("description",table,"indice1,indice2","id ASC","where,"owner","icon"); //users
$tile[0]=array("Musei","musei","name","id ASC","","","museum.svg");
$tile[1]=array("Devices","device","ip_reader,description","include_file","ccm.php","","reader.svg");
$tile[2]=array("Tag RIFD","tagrfid","tag_uid,description","id DESC","","","rfid.svg");
$tile[3]=array("File multimediali","multimedia","descrizione_media","descrizione_media ASC","","","media.svg");
$tile[4]=array("Dispositivi audio","bt_speaker","bt_descrizione","bt_descrizione ASC","","","audio.svg");
$tile[5]=array("Spot Led","neopixelspot","description","description ASC","","","spot.svg");
$tile[6]=array("Users","users","login,livello","livello ASC,login ASC","","adm");



?>
