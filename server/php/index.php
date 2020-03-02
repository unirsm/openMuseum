<?php
/**
 * NOTICE OF LICENSE
 * Attribution-NonCommercial 4.0 International (CC BY-NC 4.0) the license. Disclaimer.
 *
 * You do not have to comply with the license
 * for elements of the material in the public domain
 * or where your use is permitted by an applicable exception or limitation.
 * No warranties are given. The license may not give you all of the permissions necessary for your intended use.
 * For example, other rights such as publicity, privacy, or moral rights may limit how you use the material.
 *
 *  @author    Michele Zannoni
 *  @copyright 2019-2020 Michele Zannoni
 *  @license   https://creativecommons.org/licenses/by-nc/4.0/legalcode
 */

error_reporting(E_ALL);ini_set('display_errors', 1);

require_once("config.php");

#config
if($debug==TRUE) {error_reporting(E_ALL);ini_set('display_errors', 1);}
$version = "pre Alpha.001a";
$font_CSS="https://fonts.googleapis.com/css?family=Exo:300,400,700";
$jquery = "https://code.jquery.com/jquery-3.3.1.min.js";
$jqueryUI = "https://code.jquery.com/ui/1.12.1/jquery-ui.js";
$jqueryUI_CSS = "http://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css";
$htmlEditor = "https://cdn.ckeditor.com/4.11.1/basic/ckeditor.js";
$htmlEditor_script = "<script>editors.push(CKEDITOR.replace( 'name' ));</script>";//CKeditor standard anzichè basic
$chosen = "https://harvesthq.github.io/chosen/chosen.jquery.js";
$chosen_CSS = "https://harvesthq.github.io/chosen/chosen.css";
$qrcode_generator ="https://chart.apis.google.com/chart?cht=qr&chs=512x512&chl=";
$upload_folder="upload";
$media_folder="media";
$JPG_compression = 80;
$metadata_template = '{"description":"","author":"","source":""}';
ini_set("memory_limit", "50M");


###########################################
###########################################
###########################################

#system variable
$db= NULL;
//$editor = "http://".$_SERVER['HTTP_HOST'].$_SERVER["PHP_SELF"];
$editor = $path."index.php";
//echo $editor;exit;
$l = "<hr>";    //log
$h = "";    //>header>
$b = "";    //<body>
$f ="";     //<footer>
$errore = "";





pre_action();
$h.= user();

if (!empty($_GET["n"]) && isset($_SESSION['username'])) {action_get($_GET["n"]);}
if (!empty($_GET["a"]) && isset($_SESSION['username'])) {action_get($_GET["a"]);}
if (!empty($_POST)) {$b.= action_post($_POST["action"]);}

if (empty($_GET["a"]) && empty($_POST) && isset($_SESSION['username']) ) {

    l("IP: ".get_client_ip()."| Timestamp server: ".time()."| Timestamp client: ".local_time());

    $b.= make_tile($tile);
    if ($mediaGallery == true) {$b.= make_upload();}
}

####################################################
#    function
####################################################

function pre_action() { // initial actions

    global $version, $editor;
    l("Version: ".$version." on ".$editor);

    db_connect();
    session_start();

}

function db_connect() {
    global $db_server, $db_user, $db_pass, $db_database,$db;$db = mysqli_connect($db_server, $db_user, $db_pass, $db_database);mysqli_query($db, "SET NAMES 'utf8'");if (mysqli_connect_errno()) {  l("Connect failed: ".mysqli_connect_error());} else {l("Connect to the database");return 1;}
}

function action_get($action) { //GET "a" list action

    l("GET Action: ".$action);$out = "";
    switch($action) {
        case "logout":logout();break;
        case "edit":edit("progetto");break;
        case "delete":delete_record();break;
        case "new":insert_record();break;

    } return $out;
}

function action_post($action) { //POST list action
    global $media_folder,$upload_folder;
    l("POST Action: ".$action);$out = "";
    switch($action) {
        case "login":login();break;
        case "edit_record":record_save();break;
        case "upload":upload_file();break;
        case "erase_file":erase_file($_POST["file"]);break;
        case "move_file":move_file($_POST["file"],$media_folder,$upload_folder);break;
        case "update_metadata": update_metadata();
        break;

    } return $out;
}

function read_dir($dir) {
   $result = array();
   $cdir = scandir($dir);
   foreach ($cdir as $key => $value) {
      if (!in_array($value,array(".",".."))) {
         if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
            $result[$value] = read_dir($dir . DIRECTORY_SEPARATOR . $value);
         } else {
            $result[] = $value;
         }
      }
   }
   return $result;
}

function make_upload() {

    global $editor,$upload_folder;
    $inputBT = "<input type='file' name='files[]' id='files' class='inputfile inputfiles' data-multiple-caption='{count} files selected' multiple /><label for='files' class='add'>+</label>";
    $form='<form id="uploader" action="'.$editor.'" method="post" enctype="multipart/form-data">'.$inputBT.'
    <input id="submitfiles"type="submit" value="upload"><input type="hidden" name="action" value="upload">   </form><div id="mediagallery">';
    $o = '<div class="responsive"><h2>Media Gallery</h2>'.$form.'<hr />';

    $path = dirname(__FILE__)."/".$upload_folder;
    l($path);
    $d = read_dir($path);
    //print_r($d);
    foreach($d as $key => $v) {
        $est = strtolower(pathinfo($v,PATHINFO_EXTENSION));
        $info = explode(".",$v);
        $json = str_replace($est,"json",$v);
        $json = str_replace(".ico",".metadata",$json);
        $original_name = $info[0];
        switch ($est) {
            case "jpg":
            case "jpeg":
            case "png":
            case "gif":
                if($info[2] == "ico") {$o.="<div id='$v' class='icona'><img alt='$original_name' class='iconemedia' src='$upload_folder/$v' id='ico_$v'><a href='$v' class='iconemedia_erase' id=''>x</a><span class='dida'>$original_name</span><a id='$upload_folder/$json'  class='info iconemedia_variation' href='#'>i</a></div>";}
            break;
        }
    }
    $o.="</div></div>";
    return $o;
}

function upload_file() {

    global $editor, $upload_folder, $metadata_template;
    $countfiles = count($_FILES['files']['name']);
    // Looping all files
    for($i=0;$i<$countfiles;$i++){
        $filename = $_FILES['files']['name'][$i];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $name = str_replace(".".$ext,"", $filename);
        $name = str_replace(".","", $name);
        $name.=".".date('YmdHis',time());

        if ($ext =="jpeg") {$ext = "jpg";}
        $newname = $name.'.master.'.$ext;
        $newname_ico = $name.'.ico.'.$ext;
        $newname_json = $name.'.metadata'.'.json';
        // Upload file

        switch ($ext) {
            case "jpg":
            case "gif":
            case "png":
                move_uploaded_file($_FILES['files']['tmp_name'][$i],$upload_folder.'/'.$newname);
                write_metadata($upload_folder.'/'.$newname_json,$metadata_template);
                img_resample ($upload_folder.'/'.$newname, $upload_folder.'/'.$newname_ico, 80, 80,"crop");
                break;
        }
    }

    header("Location: ".$editor);

}

function update_metadata() {

  global $upload_folder;

  $fileJson = $_POST["file"];
  unset($_POST["action"]);
  unset($_POST["file"]);
  write_metadata($fileJson,json_encode($_POST));
  echo 1;
  exit;

}

function write_metadata($f,$m) {

    $myfile = fopen($f, "w") or die("Unable to open file!");
    fwrite($myfile, $m);
    fclose($myfile);

}

function insert_record() { //create new record

    global $level,$db,$tile,$editor;
    $tb = k($_GET["tb"]);
    $IP =get_client_ip();
    $DATA=date('y-n-j H:i:s');
    $OWNER=$_SESSION['username'];
    $HISTORY = $DATA." ".$OWNER." ".$IP;
    foreach ($tile as $v) {
        l( "|".$v[5]."==".$level);
        if($v[5]=="" || $v[5]==$level) {
            if($tb==$v[1]) {
                $val_f = $v[2];
                foreach (explode(",",$v[2]) as $fc) {
                  if(isset($_GET[$fc])) {$val_f = str_replace($fc,k($_GET[$fc]),$val_f);}
                }
                $field = str_replace("','",",",$v[2]);
                $fielddata = str_replace(",","','",$val_f);
                if ($v[1] == "users") {
                  $sql= "INSERT  INTO users (login,password,livello,HISTORY,IP,DATA,OWNER) VALUES ('users', '3c44d89e6c907596493881f77e5850f5','editor','$HISTORY','$IP','$DATA','$OWNER')";
                } else {

                  $sql = "INSERT INTO ".$v[1]."  ($field,HISTORY,IP,DATA,OWNER) VALUES ('$fielddata','$HISTORY','$IP','$DATA','$OWNER')";
                }
                mysqli_query($db, $sql) or die(mysqli_error($db));

                if ($v[1] == "users") {
                  $id= mysqli_insert_id($db);
                  $sqlupdate = "UPDATE users SET login='user$id' WHERE id=$id";
                  mysqli_query($db, $sqlupdate) or die(mysqli_error($db));
                }

                header("Location: ".$editor);
                //return ($id);
            }
        }
    }
}

function delete_record() {
    global $level,$db,$tile,$editor;
    $tb = k($_GET["tb"]);
    $id = k($_GET["id"]);

    foreach ($tile as $v) {
        l( "|".$v[5]."==".$level);
        if($v[5]=="" || $v[5]==$level) {
            if($tb==$v[1]) {
                $sql = "DELETE FROM ".$v[1]." WHERE id=".$id;
                mysqli_query($db, $sql) or die(mysqli_error($db));
                //header("Location: ".$editor);
                echo "1";
                exit();
            }
        }
    }
}

function make_tile($t) {
    $o = "";
    global $level,$editor,$path,$media_folder;

    foreach ($t as $v) {
        //$b.= '<div class="responsive"><h2></h2>'.read_table("users","nome,cognome, abilitato","nome ASC").'</div>';
        l( "|".$v[5]."==".$level);
        if($v[5]=="" || $v[5]==$level) {
          if (@$v[6]!="") {
            $ico = '<img class="icona_tile" src="icon/'.@$v[6].'">';
          } else {
            $ico = "";
          }
            switch ($v[3]) {
              case "include_file": $o.= '<div class="responsive"><h2>'.$ico.$v[0].'</h2>';require_once($v[4]);$o.= '</div>';break;
              default: $o.= '<div class="responsive"><h2>'.$ico.$v[0].'</h2><a class="add" href="'.$editor.'?a=new&tb='.$v[1].'">+</a>'.read_table($v[1],$v[2],$v[3],$v[4]).'</div>';

            }
        }
    }
    return $o;
}

// read db table
function read_table($table,$label,$order,$where) {

    global $db,$level,$editor;
    $del = "";
    $o ="<hr />"; $f = explode(",",$label);
    if (isset($order)) {$order = "ORDER BY $order ";} else {$order ="";}
    if ($level!="adm") {
        if ($where!=""){
            $where = "$where AND HISTORY LIKE '% ".$_SESSION['username']." %'";

        } else {
            $where = "HISTORY LIKE '% ".$_SESSION['username']." %'";
        }

    }
    if ($where!="") {$where = "WHERE $where ";} else {$where ="";}
    $sql = "SELECT id, $label FROM $table  $where $order";
    $rs = mysqli_query($db, $sql) or exit(mysqli_error());
    if (mysqli_connect_errno()) {  l("reading table process failed: ".mysqli_connect_error());} else {l("read table ".$table);}
    //$row = mysqli_num_rows($rs);

    while ($r = mysqli_fetch_assoc($rs)) {
        $o .="<div class='line_container'>";
        $o.= "<span id ='".$r["id"]."&tb=$table"."' class='record'>".$r["id"]." <strong>";
        foreach($f as &$v) {
           $o.= $r[$v]." ";
        }
        if ($level=="adm") {$del = "<a class='delete' id='".$r["id"]."' href='#' data='$editor?a=delete&tb=$table&id=".$r["id"]."'>x</a>";}
        $o.= "</strong></span>$del<hr />";
        $o .="</div>";

    }
    return $o;
}



// Function to get the client IP address
function get_client_ip() {
    $ipaddress = '';if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

// Function to get the timestamp of the client in javascript
function local_time() {
    return "<script>document.write( Math.floor(Date.now() / 1000));</script>";
}

function record_save() {

    global $db,$media_folder,$upload_folder,$level;
    $tb =k($_POST["form"]);
    $sqltb = "SHOW FULL COLUMNS FROM ".$tb." WHERE Comment != ''";
    $rs = mysqli_query($db, $sqltb) or exit(mysqli_error());
    $field_type = array();
    while ($r = mysqli_fetch_assoc($rs)) {
        $t = explode(",",$r["Comment"]);
        $type = $t[0];
        $para = str_replace($t[0].",".@$t[1].",","",$r["Comment"]);
        $para = json_decode($para,true);
        $para["type"] = @$t[1];
        $field_type[$r["Field"]] = $para;
    }

    $e = array();
    //print_r($_POST);
    foreach($_POST as $key => $v) {

        if($key!="action"){
            if (!isset($sql)){
                $sql ="update ".k($v)." set ";

            } else {
                switch ($key) {
                    case "id": $where = " WHERE id=".k($v); $idr = k($v); break;
                    case "IP": array_push($e, k($key)." = '".get_client_ip()."'");break;
                    case "DATA": array_push($e, k($key)." = '".date('y-n-j H:i:s')."'");break;
                    case "OWNER": array_push($e, k($key)." = '".k($_SESSION['username'])."'");break;
                    case "HISTORY": if($v!=""){$s=",";}else{$s="";}array_push($e, k($key)." = '".k($v).$s.k(date('Y-m-d H:i:s')." ".$_SESSION['username']." ".get_client_ip())."'");break;
                    default:
                    if (@$field_type[$key]["type"]=="file") {
                        $f = explode(",",k($v));
                        $sqlimma = "SELECT id,$key FROM ".k($_POST["form"])." WHERE id = $idr";
                        $ri = mysqli_query($db, $sqlimma) or exit(mysqli_error());
                        $ric = mysqli_fetch_assoc($ri);
                        $old = explode(",",$ric[$key]);
                        $file_delete = array_diff($old,$f);

						            //check file to cancel
                        foreach($file_delete as $file) {move_file($file,$media_folder,$upload_folder);}

                        foreach($f as $file) {

                            if(!file_exists("./".$media_folder."/".$file)) {

								                //create derivate file
                                $newname = str_replace(".ico",".".$tb.$key,$file);
                                $of = "./".$upload_folder.'/'.str_replace(".ico",".master",$file);
                                $df = "./".$media_folder.'/'.$newname;
                                $wf = $field_type[$key]["width"];
                                $hf = $field_type[$key]["height"];
                                $tf = $field_type[$key]["mode"];
                                //echo "\n".$of."\n".$df."\n".$wf."\n".$hf."\n".$tf."\n";

                                img_resample ($of, $df, $wf, $hf,$tf);

                                move_file($file,$upload_folder,$media_folder);
                            }
                        }
                        if (@$v[0]==","){$v[0]="";}
                    }

                    if ($field_type[$key]!="") {
                      array_push($e, k($key)." = '".k($v)."'");
                    }
                }
            }
        }
    }

    $sql = $sql.implode(",", $e).$where;

    $sqlp = "SELECT id,HISTORY FROM ".k($_POST["form"])." WHERE id = $idr AND HISTORY LIKE '% ".$_SESSION['username']." %'";
    $rsp = mysqli_query($db,$sqlp);
    //echo $sql;
    if (mysqli_num_rows($rsp)==1 || $level=="adm") {$rs = mysqli_query($db,$sql);echo $rs;} else {echo "no permission";}

    exit;

}


function k($data) {
    global $db;
    if(is_array($data)) {
        $data = implode(",",$data);
    }
    $data = (!get_magic_quotes_gpc()) ? addslashes($data) : $data;
    //$data = ($data != "") ? "'" . $data . "'" : "NULL";
    //$data = html_entity_decode($data);
    return $data;
}

function login() {
    l("login starts");


    global $db,$editor,$errore,$h;

	// indica il gruppo a cui l'utente deve appartenere per accedere alla pagina.
	if (isset($_POST['grouprequired']))
		$grouprequired = $_POST['grouprequired'];
	else if (isset($_GET['grouprequired']))
		$grouprequired = $_GET['grouprequired'];
	else
		$grouprequired = '';


	if (isset($_POST['login']) && isset($_POST['pwd']) ){

		$login = mysqli_escape_string($db, $_POST['login']);
		$password = mysqli_escape_string($db, $_POST['pwd']);
        $sql = "SELECT login, password, nome, cognome, gruppo, livello, DATE_FORMAT(ultimo, '%d/%m/%Y %T') AS ultimo_2, accessi, (unix_timestamp(now())-unix_timestamp(ultimo))/3600 as ore FROM users WHERE login=\"$login\" AND password=MD5(\"$password\") AND STATO='Y'";
		$rs = mysqli_query($db,$sql);

	if (mysqli_num_rows($rs)==1) {
        l("login user ".$login);
        $row = mysqli_fetch_assoc($rs);
		if (($grouprequired!='' && ($row['livello'] == $grouprequired || $row['livello']=='adm')) || $grouprequired=='') {
			$_SESSION['username'] = $row['login'];
			$_SESSION['gruppo'] = $row['livello'];
			$_SESSION['ultimo_accesso'] = $row['ultimo_2'];
			$_SESSION['realname'] = $row['nome']." ".$row['cognome'];
      $_SESSION['iniziali'] = $row['nome'][0]." ".$row['cognome'][0];
			$_SESSION['accessi'] = $row['accessi'];
    	$_SESSION['attempt_accessi'] = $row['attempt_accessi'];
			$_SESSION['macrogruppi'] = $row['gruppo'];
			l("Session Var created");
			$ore = $row['ore'];
			// aggiorna il numero di accessi e la data di ultima connessione per l'utente attuale.
			mysqli_query($db, 'UPDATE users SET  attempt_accessi=0, '.(($ore>=6 || $ore==NULL)?'accessi=accessi+1, ':'')."ultimo=now() WHERE login='$login'") or exit(mysqli_error());
			if ($ore>=6 || $ore==NULL){
				if (isset($_SESSION['accessi'])){//aggiunto da quà per vedere di togliere l'errore
					$_SESSION['accessi']++;
				}
			}
			// reidirizza la pagina a quella richiesta visto che il login è avvenuto con successo
			header("Location: $editor");exit;
			} else {

				$errore = 'Spiacente, l\'utente indicato non ha l\'autorizzazione ad accedere alla pagina indicata!';
                l("login fault auth user ".$login." \n");
			}
		} else {
            if (mysqli_error($db)) {
                $errore = mysqli_error($db);
                l("login fault error:");
            } else {
                // da creare qui limitazione sui tentativi di accesso utente
                mysqli_query($db, "UPDATE users SET attempt_accessi=attempt_accessi+1 WHERE login='$login'") or exit(mysqli_error());
                $sql2 = "UPDATE users SET abilitato='no' WHERE attempt_accessi > 9 AND login='$login'";
                mysqli_query($db, $sql2);//or exit(mysqli_error());
                $errore = 'Nome utente o password errati! Dopo 10 tentavi consecutivi errati l\'account viene bloccato';
                l("login fault auth user ".$login." \n");
                //$h.= user();
			}
        }
	}
    l($errore);
    echo "<div class='error'>$errore</div>";
    //$h.= user();
}


#funcion logout
function logout() { 	//remove the local session

    session_destroy();
    header("Location: index.php");
    exit();

}

function user() {

    global $errore, $level,$editor;

    l("Crea area utente");
    $logo = "";


    //$out.= '<div id="logo">'.$logo.'</div><br /><br />';
    if(isset($_SESSION['username'])) {
        $level = $_SESSION['gruppo'];
        $out = '<div id="avatar">'.$_SESSION['iniziali'].'</div>';
        $out.='<div id="user_area">';
        $out.='<strong>'.$_SESSION['realname'].'</strong><i>('.$level.')</i><div><a href="?a=logout" id="logout">logout</a></div>';
	} else {
        $out = '<div id="login" class="">';
		$out.= '
        	<form method="post" action="'.$editor.'">
            	<label for="label4" class="titoli">user: </label>
            	<input type="text" name="login" id="label4" tabindex="1" class="testo" />
            	<label for="pwd" class="titoli">password: </label>
            	<input type="password" name="pwd" id="pwd" tabindex="2" class="testo" width="200" />
            	<div class="error">'.$errore.'</div>
            	<input name="submit" type="submit" tabindex="3" value="login" class="testo" />
            	<input type="hidden" name="action" value="login">
            </form>
        ';
	}
    $out.= '</div><div id="version">OpenMuseum 1.1a</div>';
    return $out;
}


function jumpMenu($tendina,$valore,$label) {

    //global $jcfg;

    if (count($label) > 2) {
        $j = "<select id=\"$tendina\" name=\"$tendina\" onchange=\"rsv();\"  >";
        $j.= "<option value=\"\" >select</option>";

        foreach ($label as $key => $value) {

        if ($valore == $key) {$stato = "selected"; } else {$stato = "";}
        $j.= "<option value=\"$key\" $stato >$value</option>";
        }

        $j.= "</select>";

    } else {
        $j ="<span class=\"DTradio\">";
        foreach ($label as $key => $value) {

            if ($valore == $key) {$stato = "checked=\"checked\" "; } else {$stato = "";}
            $j.= "<label><input type=\"radio\" value=\"$key\" name=\"$tendina\" $stato />$value</label>";
        }
        $j.= "</span>";
    }


    return $j;
}

function link_table($tendina,$valore,$l) {

  // link_table {"tb":"citta","value":"id","field1":"denominazione","field2":"provincia","where":"STATO='Y'","order": "provincia ASC"}


    global $db,$tile;
    $table = k($l['tb']);

    //search icon
    $ico = "";
    foreach ($tile as $v) {

      if ($v[1]==$table && @$v[6]!=""){

        $ico = '<img class="icona_tile_editor" src="icon/'.@$v[6].'">';}}

    $where = "";//"WHERE ".k($l['where']);
    $f1 = k($l['field1']);
    $f2 = k(@$l['field2']);
    $order = "ORDER BY ".k($l['order']);
    $sqlid = "SELECT * FROM $table $where $order";
	  $rsid = mysqli_query($db,$sqlid);
    $valore = explode(",",$valore);

        $j = "<select class='chosen-select' onchange='if($(this).val()==\"\"){ $(this).val(\"NULL\") };' data-placeholder='select $table' multiple id=\"$tendina\" name=\"$tendina"."[]"."\" onchange=\"\" >";
        $j.= "<option value=\"NULL\" >nothing</option>";

        while ($r = mysqli_fetch_assoc($rsid)) {
            if (in_array($r['id'],$valore)) {$stato = "selected"; } else {$stato = "";}
            $j.= '<option value="'.$r['id'].'" '.$stato.' >'.$r[$f1]." ".@$r[$f2].'</option>';
        }
        $j.= "</select>";

    $out[0] = $j;
    $out[1] = $ico;
    return $out;
}

function edit(){

    global $db,$htmlEditor_script,$upload_folder,$path,$media_folder,$level,$qrcode_generator;
    $table = mysqli_escape_string($db, $_GET['tb']);
    $id = mysqli_escape_string($db, $_GET['id']);

    $sqlp = "SELECT id,HISTORY FROM $table WHERE id = $id AND HISTORY LIKE '% ".$_SESSION['username']." %'";
    $rsp = mysqli_query($db,$sqlp);
    if (mysqli_num_rows($rsp)==0 && $level!="adm") {echo "no permission";exit;}


    $sqlid = "SELECT * FROM $table WHERE id=".$_GET['id'];
	$rsid = mysqli_query($db,$sqlid);
    $rowid = mysqli_fetch_assoc($rsid);

    $sql = "SHOW FULL COLUMNS FROM $table WHERE Comment != ''";
    $o= "<form id='form_edit'><input type='hidden' name='form' value='$table'>";

    $rs = mysqli_query($db, $sql) or exit(mysqli_error());
    if (mysqli_connect_errno()) {  l("reading table process failed: ".mysqli_connect_error());} else {l("read table ".$table);}

    $editorHTML="";
    $script = "";

    while ($r = mysqli_fetch_assoc($rs)) {

        $t = explode(",",$r["Comment"]);

        if($t[0] != ""){

           $eti= $t[0];

           $te = "";
           $tp = explode("(",str_replace(")","",$r["Type"]));
           $campo = $r["Field"];
           $v = $rowid[$campo];
           l("$campo type:".$tp[0]." value:".@$tp[1]);

           if (@$t[1]=="noedit") {$tp[0] = "noedit";}
           if (@$t[1]=="link") {$tp[0] = "link";}

            if (@$t[1]=="select") {
                $tp[0] = "select";
                $label = str_replace($t[0].",select,","",$r["Comment"]);
                $label = json_decode($label,true);
            }

            if (@$t[1]=="table") {
                $tp[0] = "table";
                $label = str_replace($t[0].",table,","",$r["Comment"]);
                $label = json_decode($label,true);
            }

            if (@$t[1]=="file") {
                $tp[0] = "file";
                $para = str_replace($t[0].",file,","",$r["Comment"]);
                $para = json_decode($para,true);
                if(@$para["mode"]!="") {$mode = $para["mode"];}
            }

            $datepicker = '<script>$( function() {$( "***" ).datepicker({dateFormat: "yy-mm-dd",altField: "***_alt",altFormat: "dd MM yy",showOn: "both",buttonText: "Choose"});} );</script>';
            $checkbox = '<script>$( function() {$(".radioBT" ).checkboxradio({icon: false});});</script>';
            $eti = "<strong>".strtolower($eti)."</strong><br /><br />";

            switch ($tp[0]) {

                case "noedit":        $te = "$eti <input readonly name=\"$campo\" class=\"noedit\" value=\"$v\">";break; #campo non editabile

                case "enum":          if ($v=="Y") {$statoN=""; $statoY="checked=\"checked\""; } else {$statoN="checked=\"checked\""; $statoY="";}
                                      $te = "$eti <label for=\"Y_$campo\">Yes</label>
                                                  <input type=\"radio\" class=\"radioBT\" name=\"$campo\" $statoY id=\"Y_$campo\" value=\"Y\" />
                                                  <label for=\"N_$campo\">No</label>
                                                  <input type=\"radio\" class=\"radioBT\" name=\"$campo\" $statoN id=\"N_$campo\" value=\"N\" />
                                                  "; break;

                //case "M":           $te = caricaMultiCheckbox($campo,$campoTabella,@$row_DTB[$campo]); $c .= cl($eti,$te,""); break; # checkbox multiple

                case "varchar":       $te = "$eti <input type=\"text\" maxlength=\"$tp[1]\" name=\"$campo\" value=\"$v\" />";break; #campo di testo

                case "int":           $te = "$eti <input type=\"text\" name=\"$campo\" value=\"$v\" />"; break; #campo INT

                case "select":        $te = "$eti ".jumpMenu($campo,$v,$label); break; #menu a tendina

                //case "V":           $te = caricaVocabolario($campo,$campoTabella,$v); $c .= cl($eti,$te,""); break; #menu a tendina

                case "text":
                case "tinytext":
                                        $te = $eti."<textarea name=\"$campo\" id=\"$campo\">$v</textarea>";
                                        if(@$t[1] == "html"){
                                            $te .= str_replace("name",$campo,$htmlEditor_script);
                                            //$editorHTML.="$('$campo').value = editor.getData();";
                                        }
                                        break; #campo testo normale TEXTAREA

                case "link":          $qrcode = $qrcode_generator.$v;$te = "$eti <div class='line'><input class='lineinput' type=\"text\" name=\"$campo\" value=\"$v\" /><a href='$v' class='linkbutton' target='_blank'>Open Link</a><a href='$qrcode' target='_blank'><img class='linkqrcode' src='".$qrcode."'></a></div>";break;


                case "date":          if($v!="") {$v = date_format(new DateTime($v), 'd F Y');} $te = "$eti <input type=\"hidden\" name=\"$campo\" id=\"cal_$campo\" class=\"date\" type=\"data\" class=\"data\" value=\"".$v."\"><input readonly class=\"inputDate\" id=\"cal_$campo"."_alt"."\" value=\"$v\">"; $te.=str_replace("***","#cal_".$campo,$datepicker);break; #campo data
                case "datetime":      $te = "$eti <input name=\"$campo\" id=\"cal_$campo\" class=\"\" type=\"datetime\" value=\"".$v."\">"; break; #campo datetime

                case "table":         $ft = link_table($campo,$v,$label); $te = $ft[1]."$eti ".$ft[0];$script = '<script src="https://harvesthq.github.io/chosen/docsupport/init.js" type="text/javascript" charset="utf-8"></script>';break;

                case "file":
                                    $te="$eti <div class='images_comtainer'>";
                                    $te.= media_view($v,$campo,$mode);
                                    if($mode!="noedit") {$te.= "<a href='#' id='$campo' class='add_imma'>+</a>";}
                                    $te.="</div><input type=\"hidden\" id=\"INPUT_$campo\" name=\"$campo\" value=\"$v\" "." /><br>";
                                    break;

                default:            $te = "$eti <input type=\"text\" maxlength=\"$tp[1]\" name=\"$campo\" value=\"$v\" />";break;
            }
            $o.= $te;
            $o.= "<br /><br />";

        }
        $o.= $checkbox;
        $o.= $script;
    }

    //$o="<div class=\"responsive\">$o</div>";
    $o.="<script>endLoad();</script>";
    $o.='<input type="hidden" name="action" value="edit_record"></form>';
    echo $o;
    exit;
}


function media_view($v,$campo,$modo) {
    global $media_folder;
    $v= explode(",",$v);

    $o="";
    foreach($v as $f) {

        if($f!="") {

            $est = strtolower(pathinfo($f,PATHINFO_EXTENSION));
            $j = str_replace($est,"json",$f);
            $j = str_replace("ico","metadata",$j);

            $o.= "<div id='$f' ' class='icona icona_field'><a href='#' class='browser'><img alt='' class='iconemedia ' src='$media_folder/$f' id='ico_$f'></a>";
            if ($modo!="noedit") {$o.= "<a id='$media_folder/$j'  class='info' href='#'>i</a><a href='$f' class='iconemedia_erase' ref='$campo'>x</a>";}
            $o.= "</div>";
        }
    }
    return $o;
}

#function files
function move_file($f,$o,$d){

    $dir = read_dir($o);
    $i = explode(".",$f);
    $n = $i[0];
    $e = $i[2];



    foreach($dir as $key => $v) {
        $info = explode(".",$v);
        $mame = $info[0];
        if ($info[0]==$n && $info[1]==$i[1]) {
            switch ($info[2]) {

                case "ico":
                case "master":
                case "metadata":

                //echo $f.$v.$f.$v."<br>";
                //echo "move:"."./".$o."/".$v."<br>";
                rename("./".$o."/".$v, "./".$d."/".$v);
                chmod("./".$d."/".$v, 0755);
                break;

                default:
                //echo "erase:"."./".$o."/".$v."<br>";
                unlink("./".$o."/".$v);
                //erase
            }
        }
    }
    //exit;
}

function erase_file($f){

    global $upload_folder;
    $i = explode(".",$f);
    unlink("./".$upload_folder."/".$i[0].".".$i[1].".master.".$i[3]);
    unlink("./".$upload_folder."/".$i[0].".".$i[1].".metadata.json");
    unlink("./".$upload_folder."/".$i[0].".".$i[1].".ico.".$i[3]);
    exit;
}

function l($v) {global $l; $l.=$v."\n<hr>";} //write the log

?>

<!DOCTYPE html>
<html>
<head>
	<title><?php echo $name; ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />

    <link href="<?php echo $font_CSS; ?>" rel="stylesheet">
    <link href="<?php echo $jqueryUI_CSS; ?>" rel="stylesheet">
    <link href="<?php echo $chosen_CSS; ?>" rel="stylesheet">

    <script>
        var editors = [];

    </script>
    <style type="text/css">

        html, body {margin: 20px 0 0 0;border: 0;padding: 0;font-family: 'Exo', sans-serif;background:#EEEEEE;font-size: 12px;color: #333333;}
		    body {text-align: center;} a {text-decoration: none;color: #000066;}

        #version {margin-top: 10px; color:#999;}
        #debug {text-align: left;background: #000; color: #fff;overflow: scroll;}
        #debig pre {float: left;white-space: pre-wrap;font-style: italic;}
		    #logo {width: 100px;height: 100px;display: block;}
        #login {width: 260px;margin: 0 auto;text-align: left;border: solid #000066 1px;border-radius: 9px;padding: 30px;}
        #pwd, #label4 {width: 250px;display: block;margin-top: 5px;}
        #user_area {display: none;width: 260px;height: 30px;border-radius: 9px 24px 24px 9px;margin: 0 auto;text-align: left;border: solid #000066 1px;padding: 10px; position: absolute; right:  6px; top: 6px;z-index: 1000;background: #fff;}
        #user_area strong {text-transform: uppercase;}
        #user_area div {bottom: 6px; position: absolute; font-size: 12px; }
        #avatar {cursor: pointer;width: 40px;height: 40px;line-height: 40px;border-radius: 20px; margin: 0 auto;text-align: center;font-size: 15px;text-transform: uppercase;background: #fff;color: #000066;border: solid #000066 1px;padding: 0; position: absolute; right:  11px; top: 11px;z-index: 1001;}
        #work_area {text-align: left; margin: 18px 0px 0px 0px;position: absolute;width: 100%;padding: 0;}
        #overlay {position: fixed;background: rgba(0,0,0,.5);z-index: 100;top: 0px;left: 0;right: 0; bottom: 0;float: left;display: none;}
        #edit {background: #fff; width: 70%;height: 70%;margin: 0 auto;text-align: left;margin-top: 30px;max-width: 900px; overflow: scroll;padding: 24px;}
        #edit strong {position:relative; font-size: 14px;}
        #edit input {font-size: 14px;width:97%; border:1px solid #ccc; padding: 9px; height: 12px;border-radius: 6px;}
        #edit textarea {font-size: 14px;width:97%; border:1px solid #ccc; padding: 9px; height: 12px;border-radius: 6px; height: 80px;}
        #edit select {font-size: 14px;width:100%; border:1px solid #ccc; padding: 9px; height: 32px;border-radius: 6px; }
        #edit button {font-size: 14px;width:100px; border:1px solid #ccc; padding: 0px; height: 32px;border-radius: 6px;line-height: 16px;}
        #edit_bar {background: #ccc; width: 70%;height: 30px;margin: 0 auto;text-align: left;margin-top: 0px;max-width: 900px;padding: 12px 24px 24px 24px;}
        #edit_bar button {cursor:pointer;background-color: #000; width: 100px;color:#fff;border: #ccc;border-radius:6px;padding: .4em;font-size: 20px;margin-right:10px;text-transform: capitalize;}
        #edit_bar #cancel {background-color: #777;}

        #dialog_error input {font-size: 14px;width:97%; border:1px solid #ccc; padding: 9px; height: 12px;border-radius: 6px;}
        #dialog_error label {font-size: 14px;float: left;padding: 8px;font-weight: bold;}
        #dialog_error input#add {padding-left: 130px;width: 110px;float: left;}
        #dialog_error button {background: #ccc; color:#333;left:0;position: absolute;font-size: 14px;width:100px; border:1px solid #ccc; padding: 0px; height: 32px;border-radius: 6px;line-height: 16px;float: left;box-sizing: border-box;text-align: center;width: 120px;}
        #dialog_error button.removeFieldJson {width: 22px;height: 22px;left: none;position: relative;float: right;top: -28px;border-radius: 19px;font-size: 11px;}

        .line_container {position:relative;}
        .inputDate, .data, .datetime {width:20% !important;position: relative;left: -10px;}
        .corpo {margin-left:auto;margin-right:auto;width:100%;position: absolute;top: 25%;}
        .titoli {font-size: 12px;font-weight: bold;color: #000066;}
        .error { margin: 1em 0; color: #CC0000; }
        .noedit {background: #eee;color:#777;}
        .noedit {cursor: pointer;}
		    .responsive{position: relative; float:left;box-sizing: border-box;width: 420px; height: 453px;background: #fff;display:inline-block;margin: 0px;padding: 9px;border: 6px solid #EEEEEE;border-radius: 15px;overflow: auto;}
        .responsive strong {position: absolute; left: 30px;}
        .responsive h2 {margin-top: 0;margin-bottom: 10px;}
        .record {cursor: pointer; float: left;width:100%;height: 20px;}
        .images_comtainer {width: 100%;float: left;margin-bottom: 18px;}
        .upper {z-index: 4000; width: 400px;height: 400px;display: none;}
        .upper .icona {width: 20%;height: 20%; position: relative;float: left;margin: 0;}
        .upper .iconemedia_erase{display: none;}
        .linkbutton {background: #ccc; color:#333;left:0;position: absolute;font-size: 14px;width:100px; border:1px solid #ccc; padding: 0px; height: 32px;border-radius: 6px;line-height: 16px;float: left;padding: 8px;box-sizing: border-box;text-align: center;width: 120px;}
        .linkqrcode {width:30px;height: 30px;right: 2px;top: 1px;position: absolute;}
        .line {position: relative;margin-bottom: 10px;}
        .lineinput{padding: 15px 10px 15px 140px !important;position: absolute;box-sizing: border-box;  width:100% !important;}

        .icona {width: 120px;height: 120px; position: relative;float: left;margin: 0;}
        .icona_field {width: 120px !important;height: 120px !important;}
        .icona {width: 20%;height: 20%;}
        .icona_tile {width: 50px;height: 50px; margin-right: 10px; display:inline-block; vertical-align:middle; position: relative;}
        .icona_tile_editor {width: 25px;height: 25px; margin-right: 6px; display:inline-block; vertical-align:middle; position: relative;}
        .iconemedia {width: 100%;margin: 0;border: 1px #666 solid;box-sizing: border-box;border-radius: 10px;margin-right: 10px !important;}
        .iconemedia_erase {width: 14px;height: 14px;float: left;text-align: center;line-height: 12px;position: absolute;background: #333333;border-radius: 8px;right: 2px;top:2px;border: 1px solid #ddd;color:#ddd;}
        .iconemedia_erase:hover {background: #ccc; color: #333; border: 1px solid #333;}
        .dida {overflow: hidden;background: rgba(0, 0, 0, 0.47); color:#fff; font-size: 9px;position: absolute;bottom: 4px;left:0;width: 100%;padding: 2px 2px 2px 6px;box-sizing: border-box;border-radius: 0px 0px 10px 10px;}
        .info {width: 14px;height: 14px;float: left;text-align: center;line-height: 15px;font-size: 9px;position: absolute;background: #333333;border-radius: 8px;right: 2px;bottom:2px;border: 1px solid #ddd;color:#ddd;}
        .iconemedia_variation{bottom:6px;}
        .add_imma{font-size: 80px;width: 120px;height: 120px;float: left;text-align: center;box-sizing: border-box;line-height: 122px;border-radius: 8px;color:#fff;background: #ddd;}
        /* tablet pro */ @media screen and (max-width: 1366px) {.responsive{width: 33.3%;}}
        /* tablet */ @media screen and (max-width: 1024px) {.responsive{width: 50%;}}
        /* smartphone */ @media screen and (max-width: 768px) {.responsive{width:100%;}}

        .ui-state-active {background-color: #ccc; color:#000;border: #ccc;}
        .ui-state-default {background-color: #ccc; color:#000;border: none;}
        .ui-state-active:hover {background-color: #ccc; color:#000;border: #ccc;}
        .ui-button:hover {background-color: #000; color:#fff;border: #ccc;}
        .ui-button {border-radius:6px;padding: 0.8em;}

        .noScroll {overflow: hidden;}
        .chosen-choices {border-radius:6px;padding: .4em;min-height: 32px;}
        .chosen-choices input {margin: 5px 0 !important;}

         #submitfiles{display: none;}
         #files{display: none;}
         .add{cursor: pointer;font-size: 20px;width: 20px;height: 20px;float: right;position: absolute;top: 10px;right: 10px;text-align: center;line-height: 21.5px;border-radius: 6px;color:#000;border: 1px solid #000;}
         .delete{cursor: pointer;font-size: 12px;width: 17px;height: 17px;position: absolute;top: -2px;right: 0px;text-align: center;line-height: 16px;border-radius: 9px;color:#000;border: 1px solid #000;margin-bottom: 1px;}
         .online {color: #63B588;}
	</style>
    <script src="<?php echo $jquery; ?>"></script>
    <script src="<?php echo $jqueryUI; ?>"></script>
    <script src="<?php echo $htmlEditor; ?>"></script>
    <script src="<?php echo $chosen; ?>" type="text/javascript" charset="utf-8"></script>

</head>
<body>
<header><?php echo $h; ?></header>
<div id="work_area">
    <?php echo $b; ?>
    <!--<div class="responsive">contenuto 1</div>-->
    <?php if($debug == TRUE) { ?><div class="responsive" id="debug"><h2>Debug</h2><pre><?php echo $l; ?></pre></div><?php } ?>

</div>
<footer><?php echo $f; ?></footer>

<div id="dialog_error" class="upper" title="Error"></div>

<script type="text/javascript">

    $(document).ready(function(){
        $(document).click(function(){ $("#user_area").hide();});
        $("#avatar").bind("click",function (event){$("#user_area").toggle();event.stopImmediatePropagation();});
        $("#user_area").bind("click",function (event){event.stopImmediatePropagation();});
        $(".record").bind("click",function (event){
        $("body").addClass("noScroll");$("#overlay").show();
        $( "#edit" ).load( "<?php echo $editor; ?>?a=edit&id="+($(this).attr('id'))), function( response) {alert(response);}});

        $(".delete").click(function(event){
          event.stopImmediatePropagation();
          id = $(this).attr('id');
          let del = confirm("Do you want delete the record "+id+" ?");
          if( del == true) {
            $.get( $(this).attr('data'), function( data ) {
              location.reload();
            });
          };
        });

        $("#cancel").click(function(){
            $("#overlay").hide();
            $( "#edit" ).html("");
            editors=[];
            $("body").removeClass("noScroll");
            location.reload();
        });
        $("#edit").click(function(event){event.stopImmediatePropagation();});

        $("#save").click(function(){
            for (i = 0; i < editors.length; i++) {editors[i].updateElement();}

            //$.post( "<?php echo $editor; ?>", { action: "move_file", file: div },function(data) {if(data!=""){$( "#dialog_error" ).html(data);$( "#dialog_error" ).dialog({modal: true,buttons: {Ok: function() {$( this ).dialog( "close" );}}});}});

            var serializedData = $('#form_edit').serialize();
            //serializedData = serializedData.replace(".",'');

            $.post("<?php echo $editor; ?>", serializedData, function(data) {
                if(data==1) {
                    editors=[];
                    $("#overlay").hide();
                    $("#edit").html("");
                    $("body").removeClass("noScroll");
                    location.reload();
                } else {
                    alert("Save problem:Error "+data);
                }
            });
        });

        $("#files").bind("change",function (event){$("#uploader").submit();});
        $(".iconemedia_erase").bind("click",function (event){
            event.preventDefault();
            event.stopImmediatePropagation();
            div = $(this).attr('href');

            $.post( "<?php echo $editor; ?>", { action: "erase_file", file: div },function(data) {if(data!=""){
                $( "#dialog_error" ).html(data);

                $( "#dialog_error" ).dialog({modal: true,buttons: {Ok: function() {$( this ).dialog( "close" );}}});

                }});
            $(this).parent().hide();
        });


        endLoad();
    });


    function endLoad(){


      $(".info").bind("click",function (event){

      id = $(this).attr("id");
      //n = id.split(".");
      //n = n[0]+"."+n[1]+".master."
        $.getJSON( id+"?"+Date.now(), function( data ) {

          f = "<form id='metadata1'><input name='action' type='hidden' value='update_metadata' ><input name='file' type='hidden' value='"+id+"' >";
           var items = [];

           $.each( data, function( key, val ) {
            f += "<div class='line'><label for='"+key+"'>"+key+"</label><input name='" + key + "' value='" + val + "'><button class='removeFieldJson'>X</button></div>";
           });

          f += "</form><br /><br /><div class='line'><button id='addfield'>Add new field</button><input placeholder='insert label' id='add' name='add'></div>";

          $( "#dialog_error" ).html(f);
          $("#addfield").bind("click",function (event){
            key = $("#add").val();
            if(key!="") {
              $( "#metadata1" ).append("<div class='line'><label for='"+key+"'>"+key+"</label><input name='" + key + "' value=''><button class='removeFieldJson'>X</button></div>");

              $(".removeFieldJson").bind("click",function (event){
                $(this).parent().remove();
                event.stopImmediatePropagation();
              });
            } else {
              alert("You need to insert a label for the new metadata.")
            }
          });

          $(".removeFieldJson").bind("click",function (event){
            $(this).parent().remove();
            event.stopImmediatePropagation();
          });


          $( "#dialog_error" ).dialog({title: "Metadata Editor",height: 500,width: 500,modal: true,buttons: {

            save: function() {
              $.post("<?php echo $editor; ?>", $("#metadata1").serialize(), function(data) {
                  if(data==1) {
                      $( "#dialog_error" ).dialog( "close" );
                  } else {
                      alert("Save problem:Error "+data);
                  }
              });

            },
            cancel: function() {
              $( this ).dialog( "close" );
            }
          }});

         });



      });

        $(".browser").bind("click",function (event){

            div = $(this).attr('href');
            //console.log("images: "+div);

        });

        $(".iconemedia").bind("click",function (event){
            event.preventDefault();
            event.stopImmediatePropagation();

        });



        $(".add_imma").bind("click",function (event){
            event.preventDefault();
            event.stopImmediatePropagation();
            field = $(this).attr("id")
            $( "#dialog_error" ).html($("#mediagallery").html());
            $( "#dialog_error" ).dialog({title: "Media Manager",maxHeight: 388,width: 480,modal: true,buttons: {close: function() {$( this ).dialog( "close" );}}});
            var images = $("#INPUT_"+field).val().split(",");
            $(".upper .icona").bind("click",function (event){
                var f=$(this).attr('id');
                document.getElementById(f).remove();
                images.push(f);
                n = f.split(".");
                name = n[0]+"."+n[1]+".metadata.json";
                newIMG = "<div id='"+field+"'  class='icona icona_field'> <a href='#' class='browser'><img alt='' class='iconemedia'  src='<?php echo $upload_folder;?>/"+f+"' id='ico_"+f+"'><a id='<?php echo $upload_folder;?>/"+name+"' class='info' href='#'>i</a></a> <a href='"+f+"' class='iconemedia_erase' ref='"+field+"'>x</a> </div>";;
                $("#"+field).before(newIMG);
                $("#INPUT_"+field).val(images.join());
                $("#dialog_error").dialog( "close");
                endLoad();
            });
        });

        $(".iconemedia_erase").bind("click",function (event){
            event.preventDefault();
            event.stopImmediatePropagation();
            field = $(this).attr('ref');
            div = $(this).attr('href');
            var images = $("#INPUT_"+field).val().split(",");
            $.each(images, function( i, l ){if(div==l) {images.splice(i,1);}});
            $(this).parent().hide();
            $("#INPUT_"+field).val(images.join());
        });
    }
</script>
</body>
<div id="overlay">
    <div id="edit">&nbsp;</div>
    <div id='edit_bar'>
        <button id='save'>save</button>
        <button id='cancel'>cancel</button>
    </div>";
</div>
</html>

<!--
SHOW FULL COLUMNS FROM progetto;


ALTER TABLE progetto MODIFY id INT(11) COMMENT 'id,definition';

ALTER TABLE progetto COMMENT = 'commenti,tabella,prova';

-->

<?php
##########################################
##### Service function
##########################################

// GD Function
function img_resample ($image_path, $destinazione, $MAX_WIDTH, $MAX_HEIGHT, $modo){




  # Load image
  $img = null;
  //$ext = strtolower(end(explode('.', $image_path)));
  $ext = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));

  if ($ext == 'jpg' || $ext == 'jpeg') {
    $img = imagecreatefromjpeg($image_path);
  } else if ($ext == 'png') {
    $img = @imagecreatefrompng($image_path);
  # Only if your version of GD includes GIF support
  } else if ($ext == 'gif') {
    $img = @imagecreatefrompng($image_path);
  }

  # If an image was successfully loaded, test the image for size
  if (!$img) { return false; }

  # Get image size and scale ratio
  $width = imagesx($img);
  $height = imagesy($img);
  $scale = min($MAX_WIDTH/$width, $MAX_HEIGHT/$height);
  //echo "\n:".$width."x".$height."\n";

  # If the image is larger than the max shrink it
  if ($scale < 1) {

    switch($modo){

      case "fit":
        $new_width = floor($scale*$width);
        $new_height = floor($scale*$height);
        # Create a new temporary image
        $tmp_img = imagecreatetruecolor($new_width, $new_height);

        # Copy and resize old image into new image
        #imagecopyresized($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagecopyresampled($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagedestroy($img);
        $img = $tmp_img;
        break;

      case "fitw":
        //echo "\nscale:".$scale."\n";
        $scale = $MAX_WIDTH/$width;
        $new_width = floor($scale*$width);
        $new_height = floor($scale*$height);
        # Create a new temporary image
        $tmp_img = imagecreatetruecolor($new_width, $new_height);

        # Copy and resize old image into new image
        imagecopyresampled($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagedestroy($img);
        $img = $tmp_img;
        break;

      case "fith":
        $scale = $MAX_HEIGHT/$height;
        $new_width = floor($scale*$width);
        $new_height = floor($scale*$height);
        # Create a new temporary image
        $tmp_img = imagecreatetruecolor($new_width, $new_height);

        # Copy and resize old image into new image
        imagecopyresampled($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagedestroy($img);
        $img = $tmp_img;
        break;

      case "crop":
        $scale = min($width/$MAX_WIDTH, $height/$MAX_HEIGHT);
        $new_width = floor($width/$scale);
        $new_height = floor($height/$scale);
        # Create a new temporary image
        $tmp_img = imagecreatetruecolor($new_width, $new_height);
        # Copy and resize old image into new image
        imagecopyresampled($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        # Create a new temporary image
        $crop_img = imagecreatetruecolor($MAX_WIDTH, $MAX_HEIGHT);
        $delta_x= floor(($new_width-$MAX_WIDTH)/2);
        $delta_y= floor(($new_height-$MAX_HEIGHT)/2);
        imagecopyresampled($crop_img, $tmp_img, 0, 0, $delta_x, $delta_y, $MAX_WIDTH, $MAX_HEIGHT, $MAX_WIDTH, $MAX_HEIGHT);
        imagedestroy($img);
        $img = $crop_img;
        break;

    }
    // registro la nuova immagine
    global $JPG_compression;
    imagejpeg($img, $destinazione, $JPG_compression);
    //echo $destinazione;

	} else {// se l'immagine è più piccola del formato, copiala senza ridimensionarla
    if($image_path != $destinazione) {
      if (!copy($image_path, $destinazione)) {
        echo "Copy of $destinazione faulted ...\n";
      }
    }
  }

  //contrast only of the smallest images
  $dimensione = getimagesize ($destinazione);
  $w = $dimensione[0];
  $h = $dimensione[1];

  if($w < 210 AND $h < 210) {
    $fattore = ($w < 105 && $h < 105) ? .5 : .6;
    contrasta($destinazione, $fattore);
  }

}


function filtri($tipo, $destinazione , $colore , $valore1, $valore2 ) {
  // i filtri non sono applicabili con GD
  return false;
}
#################################################


#################################################
//constrast images
function contrasta($filename, $fattore_sharp){
  list($width, $height) = getimagesize($filename);
  $img = imagecreatefromjpeg($filename);
  $pix=array();

  //get all color values off the image
  for($hc=0; $hc<$height; ++$hc){
     for($wc=0; $wc<$width; ++$wc){
         $rgb = ImageColorAt($img, $wc, $hc);
         $pix[$hc][$wc][0]= $rgb >> 16;
         $pix[$hc][$wc][1]= $rgb >> 8 & 255;
         $pix[$hc][$wc][2]= $rgb & 255;
     }
  }

  //sharpen with upper and left pixels
  $height--; $width--;
  for($hc=1; $hc<$height; ++$hc){
     $r5=$pix[$hc][0][0];
     $g5=$pix[$hc][0][1];
     $b5=$pix[$hc][0][2];
     $hcc=$hc-1;
     for($wc=1; $wc<$width; ++$wc){
         $r=-($pix[$hcc][$wc][0]);
         $g=-($pix[$hcc][$wc][1]);
         $b=-($pix[$hcc][$wc][2]);

         $r-=$r5+$r5; $g-=$g5+$g5; $b-=$b5+$b5;

         $r5=$pix[$hc][$wc][0];
         $g5=$pix[$hc][$wc][1];
         $b5=$pix[$hc][$wc][2];

         $r+=$r5*5; $g+=$g5*5; $b+=$b5*5;

         $r*=.5; $g*=.5; $b*=.5;

        //here the value of 0.75 is like 75% of sharpening effect
        //Change if you need it to 0.01 to 1.00 or so
        //Zero would be NO effect
        //1.00 would be somewhat grainy
        //$fattore_sharp=.65;

         $r=(($r-$r5)*$fattore_sharp)+$r5;
         $g=(($g-$g5)*$fattore_sharp)+$g5;
         $b=(($b-$b5)*$fattore_sharp)+$b5;

         if ($r<0) $r=0; elseif ($r>255) $r=255;
         if ($g<0) $g=0; elseif ($g>255) $g=255;
         if ($b<0) $b=0; elseif ($b>255) $b=255;
         imagesetpixel($img,$wc,$hc,($r << 16)|($g << 8)|$b);
     }
  }

  //save pic
  imageinterlace($img,1);

  imagejpeg($img,$filename,90);
  imagedestroy($img);
}



?>
