<?php

error_reporting(E_ALL);ini_set('display_errors', 1);

#database Mysql
$db_server ="localhost";
$db_user = "default";
$db_pass = 'd4Gg4?t9';
$db_database ="giano";
$db= NULL;


db_connect();

if (isset($_GET["d"])) {$debug = false;} else {$debug = true;}
if (isset($_GET["m"])) {$mus = k($_GET["m"]);} else {exit;}
if (isset($_GET["t"])) {$t = k($_GET["t"]);} else {exit;}

switch($t) {
  case "rfid":$tb="musei";$tb2="tagrfid";$where=" id= $mus"; $order = "id ASC ";$field = "id,tag_uid";$name="rfid";break;
  case "multimedia":$tb="musei";$tb2="multimedia";$where=" id= $mus"; $order = "id ASC ";$field = "id,tipo_media,descrizione_media,path_media,titolo_media";$name="multimedia";break;
  case "bt_speaker";$tb="musei";$tb2="bt_speaker";$where=" id= $mus"; $order = "id ASC ";$field = "id,bt_mac,bt_descrizione";$name="bt_speaker";break;
  case "neopixelspot";$tb="musei";$tb2="neopixelspot";$where=" id= $mus"; $order = "id ASC ";$field = "id,description,ip_indirizzo,colorRGBW";$name="neopixelspot";break;
  case "tradfri";$tb="musei";$tb2="tradfri";$where=" id= $mus"; $order = "id ASC ";$field = "id,ip_gateway,id_bulbo,bulbo_descrizione";$name="tradfri";break;
  case "interaction";$tb="musei";$tb2="tagrfid";$where=" id= $mus"; $order = "id ASC ";$field = "id,tag_uid";$name="interaction";break;

  default: exit;
}

switch($t) {
  case "interaction": $risposta = getdataInteractio($tb,$where,$order,$field,$tb2,$name);break;
  default: $risposta = getdata($tb,$where,$order,$field,$tb2,$name);
}

############################# INVIA RISPOSTA IN JSON #############################

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');
echo json_encode($risposta);

function db_connect() {global $db_server, $db_user, $db_pass, $db_database,$db;$db = mysqli_connect($db_server, $db_user, $db_pass, $db_database);mysqli_query($db, "SET NAMES 'utf8'");if (mysqli_connect_errno()) {  l("Connect failed: ".mysqli_connect_error());} else {l("Connect to the database");return 1;}}
function l($v) {global $l; if (!isset($l)){$l="";}$l.=$v."\n<hr>";} //write the log
function k($data) {global $db;if(is_array($data)) {$data = implode(",",$data);}$data = trim($data);$data = addslashes($data);$data = htmlspecialchars($data);return $data;}
function getdata($tb,$where,$order,$field,$tb2,$name){

    global $db,$slide,$media,$mus;
    $o = array();
    $sql = "SELECT id,$tb2 FROM $tb WHERE $where ORDER BY id";
    $rs = mysqli_query($db, $sql) or exit(mysqli_error());
    $o["id_casamuseo"] = intval($mus);
    while ($r = mysqli_fetch_assoc($rs)) {

      $c = $r[$tb2];
      if($c!="") {$where = "id=".str_replace(","," OR id=",$c);} else {$where = "id=0";}
      $sqlt = "SELECT $field FROM $tb2 WHERE $where ORDER BY id";
      $rst = mysqli_query($db, $sqlt) or exit(mysqli_error());

      $i = 0;

      while ($rt = mysqli_fetch_assoc($rst)) {

        $o[$name][$i] = $rt;
        $o[$name][$i]["id"] = intval($o[$name][$i]["id"]);
        $i++;
      }

    }
    $out = array();array_push($out,$o);

    return $out;
}

function getdataInteractio($tb,$where,$order,$field,$tb2,$name){

    global $db,$slide,$media,$mus;
    $o = array();

    $o["id_casamuseo"] = intval($mus);

    //array_push($o,$i);

    $sql = "SELECT id,$tb2 FROM $tb WHERE $where ORDER BY id";
    $rs = mysqli_query($db, $sql) or exit(mysqli_error());
    $r = mysqli_fetch_assoc($rs);

    $c = $r[$tb2];
    if($c!="") {$where = "id=".str_replace(","," OR id=",$c);} else {$where = "id=0";}
    $sqlt = "SELECT * FROM $tb2 WHERE $where ORDER BY id";
    $rst = mysqli_query($db, $sqlt) or exit(mysqli_error());

    $ob = array();
    while ($rt = mysqli_fetch_assoc($rst)) {
      $a = array();
      //$a = $rt;
      $a["id_rfid"] = intval($rt["id"]);

      $n = 1;
      $ixd =array();
      $d =array();
      $obj = array("bt_speaker","multimedia","neopixelspot","tradfri");
      foreach ($obj as $key => $v) {

        if($rt[$v]!="") {$where = "id=".str_replace(","," OR id=",$rt[$v]);} else {$where = "id=0";}
        $sqlti = "SELECT * FROM $v WHERE $where ORDER BY id";
        $rsti = mysqli_query($db, $sqlti) or exit(mysqli_error());

        while ($rti = mysqli_fetch_assoc($rsti)) {

          $d["id"] =  $n;
          $d["type"] = $v;
          $d["id_ix"] =  intval($rti["id"]);
          array_push($ixd,$d);
          $n++;
        }

      }
      $a["object"] = $ixd;

      array_push($ob,$a);
    }
    $o["interaction"] = $ob;

    $out = array();array_push($out,$o);
    return $out;

}
?>
