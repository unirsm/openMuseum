<?php
$o.='<a class="add" href="'.$editor.'?a=new&tb=device">+</a>';

$o.='
<style>
.device_editor {display: none;}
.device_editor input {cursor:pointer;position:relative;color:#999;text-align:center;margin-left:3px; width:46%;height:30px;margin-bottom:2px; background: #fff;border: 1px solid #999; border-radius: 6px;padding: 0.3em;}
.device_editor button{cursor:pointer;position:relative;color:#999;text-align:center;margin-left:3px; width:46%;height:30px;background: #fff;border: 1px solid #999; border-radius: 6px;padding: 0.3em;margin-bottom:2px;}
.device_editor select{cursor:pointer;position:relative;color:#999;text-align:left;margin-left:3px; width:46%;height:30px; background: #fff;border: 1px solid #999; border-radius: 6px;padding: 0.3em;}
.device_editor form {}
.statusbar {position:relative;color:#63B588;text-align:left;margin:3px;}
.doppio {width:93% !important}
.edit_device{display:none;background:#fff;cursor: pointer;font-size: 12px;width: 43px;height: 43px;position: absolute;top: 1.5px;right: 80px;text-align: center;line-height: 42px;border-radius: 5px;color:#000;border: 1px solid #000;margin-bottom: 1px;}


</style>

';

//$tile[1]=array("Devices","device","ip_reader,description","include_file","ccm.php","adm","reader.svg");
//$tile[2]=array("Tag RIFD","tagrfid","tag_uid,description","id ASC","","","rfid.svg");
$o.= read_Reader("device","ip_reader,description","ip_reader ASC","");

// read db table
function read_Reader($table,$label,$order,$where) {

    global $db,$level,$editor,$qrcode_generator;
    $del = "";
    $o ="";
    $o .="<hr />"; $f = explode(",",$label);
    if (isset($order)) {$order = "ORDER BY $order ";} else {$order ="";}
    if ($level!="adm") {
        if ($where!=""){
            $where = "$where AND HISTORY LIKE '% ".$_SESSION['username']." %'";

        } else {
            $where = "HISTORY LIKE '% ".$_SESSION['username']." %'";
        }

    }
    if ($where!="") {$where = "WHERE $where ";} else {$where ="";}
    $sql = "SELECT * FROM $table  $where $order";
    $rs = mysqli_query($db, $sql) or exit(mysqli_error());
    if (mysqli_connect_errno()) {  l("reading table process failed: ".mysqli_connect_error());} else {l("read table ".$table);}
    //$row = mysqli_num_rows($rs);
    $idList = array();
    $pass_device = array();

    while ($r = mysqli_fetch_assoc($rs)) {
        $idl = $r["id"]."&tb=".$table;
        $o .="<div class='line_container'>";
        $o.= "<span style='height:55px;' id ='$idl' class='record ".str_replace(".","_",$r["ip_reader"])."'>";
        $o.= "<strong style='left:10px'><img class='icona_tile' src='icon/reader01.png'>";

        foreach($f as &$v) {
           $o.= $r[$v]." ";
        }
        $o.= "";
        $right = "-6px";
        $nameclass = str_replace(".","_",$r["ip_reader"]);

        if ($level=="adm") {$right = "20px"; $del = "<a class='delete' id='".$r["id"]."' href='#' data='$editor?a=delete&tb=$table&id=".$r["id"]."'>x</a>";}
        $id_editor = "edit_".$nameclass;
        $edit = "<a onclick='$(\"#$id_editor\").toggle();' class='edit_device' id='bt_device_".$r["id"]."' href='#' data=''>config</a>";
        $editor_device ="<span class='device_editor' id='$id_editor'></span>";
        $o.="</strong></span>$edit $editor_device $del<hr />";
        $qrcode = $qrcode_generator."http://".$r["ip_reader"].":1880/ui";
        $o .= "<a href='$qrcode' target='_blank'><img style='width:60px;height:60px;position:absolute; right:$right;top:-6px;' src='".$qrcode."'></a>";
        $o .="</div>";

        $idList[$r["id"]] = $r["ip_reader"];
        $pass_device[$r["id"]] = $r["password"];
    }
    $o.='
    <script type="text/javascript">
            var ws;

            function wsConnect(device,key,nameclass,pass,j_bt,id_device) {
                console.log("connect",device);
                ws = new WebSocket(device);
                ws.onmessage = function(msg) {
                    var line = "";  // or uncomment this to overwrite the existing message
                    var data = msg.data;
                    //console.log(data);
                    // build the output from the topic and payload parts of the object
                    line += "<button class=\'doppio\' onclick=\"window.open(\'?a=new&tb=tagrfid&description=Nuovo TAG RIFD&tag_uid="+data+"\',\'_self\');\">New RFID tag: "+data+"</button>";
                    // replace the messages div with the new "line"
                    document.getElementById(\'messages_\'+nameclass).innerHTML = line;
                    //ws.send(JSON.stringify({data:data}));
                }
                ws.onopen = function() {
                    // update the status div with the connection status

                    console.log(\'status_\'+nameclass);
                    //ws.send("Open for data");
                    console.log("connected: "+nameclass);
                    showDeviceConnect(key,nameclass,pass,j_bt,id_device);
                    document.getElementById(\'status_\'+nameclass).innerHTML = "Device connected";

                }
                ws.onclose = function() {
                    // update the status div with the connection status
                    document.getElementById(\'status_\'+nameclass).innerHTML = "not connected";
                    // in case of lost connection tries to reconnect every 3 secs
                    setTimeout(wsConnect,3000);
                }
            }

            function doit(m) {
                if (ws) { ws.send(m); }
            }';

            //create Bluetooth device list
            $sqlbt= "SELECT * FROM bt_speaker WHERE HISTORY like '%".$_SESSION['username']."%'";
            $rbtq = mysqli_query($db,$sqlbt);
            $j_bt = "<select data-placeholder=\"select Bluetooth device\"  id=\"bt\" name=\"bt\" >";
            $j_bt.= "<option value=\"\" >seleziona...</option>";
            while ($rbt = mysqli_fetch_assoc($rbtq)) {
              $j_bt.= '<option value="'.$rbt['bt_mac'].'" >'.$rbt['bt_mac']." ".@$rbt['bt_descrizione'].'</option>';
            }
            $j_bt.= "</select>";

            //create RFID TAG list
            $sqlRFID= "SELECT * FROM tagrfid WHERE HISTORY like '%".$_SESSION['username']."%'";
            $rRFIDq = mysqli_query($db,$sqlRFID);
            $j_RFID = "<select data-placeholder=\"select RFID TAG\"  id=\"rfid\" name=\"rfid\" >";
            $j_RFID.= "<option value=\"\" >seleziona...</option>";
            while ($rRFID = mysqli_fetch_assoc($rRFIDq)) {
              $j_RFID.= '<option value="'.$rRFID['tag_uid'].'" >'.$rRFID['tag_uid']." ".@$rRFID['description'].'</option>';
            }
            $j_RFID.= "</select>";

      $o.="function showDeviceConnect(key,nameclass,pass,id_device) {
              ind ='http://'+nameclass.replace(/_/g,'.')+':1880';
              msg1 = 'La procedura di connessione del device bluetooth è iniziata, assicurarsi che il device sia acceso e in modalità di pairing e attendere i segnali acustici dalla cassa.';
              msg2 = 'Il profilo della cassa Bluetooth verrà impostato su A2DP.';
              msg3 = 'I file di configurazione del device verranno aggiornati dal server.';
              msg4 = 'Un suono è stato emesso dal device.';
              msg5 = 'Una luce blu è stata accesa sul device.';
              msg6 = 'Comando eseguito sul device.';

              $(\"#bt_device_\"+key).show();
              var str = '<form id=\"bt_'+nameclass+'\" class=\"form_device\" method=\"post\">';
              str +=    '<input type=\"hidden\" value=\"'+pass+'\" name=\"password\">';
              str +=    '<input onclick=\"event.preventDefault();event.stopImmediatePropagation();sendForm(\''+ind+'\'+\'/bt\',\'bt_\'+\''+nameclass+'\',msg1);\" type=\"submit\" value=\"Pair bluetooth device\">';
              str +=    '$j_bt';
              str +=    '</form>';

              str +=    '<form id=\"A2DP_'+nameclass+'\" method=\"post\">';
              str +=    '<input type=\"hidden\" value=\"'+pass+'\" name=\"password\">';
              str +=    '<input class=\"doppio\" onclick=\"event.preventDefault();event.stopImmediatePropagation();sendForm(\''+ind+'\'+\'/A2DP\',\'A2DP_\'+\''+nameclass+'\',msg2);\" type=\"submit\" value=\"A2DP Profile force\">';
              str +=    '</form>';

              str +=    '<form id=\"rfid_'+nameclass+'\" class=\"form_device\" method=\"post\">';
              str +=    '<input type=\"hidden\" value=\"'+pass+'\" name=\"password\">';
              str +=    '<input onclick=\"event.preventDefault();event.stopImmediatePropagation();sendForm(\''+ind+'\'+\'/rfid\',\'rfid_\'+\''+nameclass+'\',msg6);\" type=\"submit\" value=\"TEST TAG RFID\">';
              str +=    '$j_RFID';
              str +=    '</form>';

              str +=    '<form id=\"test_'+nameclass+'\" method=\"post\">';
              str +=    '<input type=\"hidden\" value=\"'+pass+'\" name=\"password\">';
              str +=    '<input  class=\"doppio\" onclick=\"event.preventDefault();event.stopImmediatePropagation();sendForm(\''+ind+'\'+\'/test\',\'test_\'+\''+nameclass+'\',msg4);\" type=\"submit\" value=\"Play test sound\">';
              str +=    '</form>';

              str +=    '<form id=\"stop_'+nameclass+'\" method=\"post\">';
              str +=    '<input type=\"hidden\" value=\"'+pass+'\" name=\"password\">';
              str +=    '<input class=\"doppio\" onclick=\"event.preventDefault();event.stopImmediatePropagation();sendForm(\''+ind+'\'+\'/stop\',\'stop_\'+\''+nameclass+'\',msg6);\" type=\"submit\" value=\"Button STOP\">';
              str +=    '</form>';

              str +=    '<form id=\"blue_'+nameclass+'\" method=\"post\">';
              str +=    '<input type=\"hidden\" value=\"'+pass+'\" name=\"password\">';
              str +=    '<input class=\"doppio\" onclick=\"event.preventDefault();event.stopImmediatePropagation();sendForm(\''+ind+'\'+\'/blue\',\'blue_\'+\''+nameclass+'\',msg5);\" type=\"submit\" value=\"Identifica il device\">';
              str +=    '</form>';

              str +=    '<button onclick=\"window.open(\''+ind+'\',\'_blank\');\">Open Red-Node Interface</button>';
              str +=    '<button onclick=\"window.open(\''+ind+'\'+\'/ui\',\'_blank\');\">Open Red-Node UI</button>';

              str +=    '<form id=\"update_'+nameclass+'\" method=\"post\">';
              str +=    '<input type=\"hidden\" value=\"'+pass+'\" name=\"password\">';
              str +=    '<input  class=\"doppio\" onclick=\"event.preventDefault();event.stopImmediatePropagation();sendForm(\''+ind+'\'+\'/update\',\'update_\'+\''+nameclass+'\',msg3);\" type=\"submit\" value=\"Update device configuration\">';
              str +=    '</form>';

              str +=    '<div id=\"messages_'+nameclass+'\"></div>';
              str +=    '<div class=\"statusbar\"><span id=\"status_'+nameclass+'\">unknown</span></div>';


              $( \"#edit_\"+nameclass ).html( str );
              document.getElementById(id_device).classList.add(\"online\");

            };";

    $o.= '</script>';

    $o.= "<script type=\"text/javascript\">";
    foreach($idList as $key => $ip_r) {
        $id_device = $key."&tb=device";
        $pass = $pass_device[$key];
        $class = str_replace(".","_",$ip_r);
        $ind = "http://$ip_r:1880";
        $indWS = "ws://$ip_r:1880";
        $o.="wsConnect('$indWS/ws/utility','$key','$class','$pass','$id_device');\n";

    }

    $o.="function sendForm(urlform,formID,msg){
          $.post(urlform, $(\"#\"+formID).serialize(), function(data) {
              alert(msg);
          });
        };

        ";

    $o.="</script>";



    return $o;
}
?>
