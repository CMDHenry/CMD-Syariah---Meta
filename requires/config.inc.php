<?
//error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

include_once("session.inc.php");
include_once("connection_db.inc.php");



$lsetting=pg_fetch_array(pg_query("select * from tblsetting"));

define(today,date("d/m/Y",strtotime($lsetting["tgl_sistem"])));
define(today_db,date("Y-m-d",strtotime($lsetting["tgl_sistem"])));
define(cabang_ho,$lsetting["fk_cabang_ho"]);
define(tgl_accounting,$lsetting["tahun_accounting"].'-'.$lsetting["bulan_accounting"].'-01');
$lcabang_wilayah=pg_fetch_array(pg_query("select fk_cabang_wilayah from tblcabang left join tblwilayah on fk_wilayah=kd_wilayah where kd_cabang='".$_SESSION["kd_cabang"]."'"));
define(cabang_wilayah,$lcabang_wilayah["fk_cabang_wilayah"]);
define(is_pt,$lsetting["is_pt"]);

//echo today_db;
function convert_date_english($pStr,$p_sign='/'){//dari indonesia ke inggris
	if($pStr!="" && $pStr!="-"){
		list($d,$m,$y) = explode('/',substr($pStr,0,10));
		return $m.$p_sign.$d.$p_sign.$y;
	}else return $pStr;
}

function convert_date_indonesia($pStr,$p_sign='/'){//dari inggris ke indonesia
	if($pStr!="" && $pStr!="-"){
		list($m,$d,$y) = explode('/',substr($pStr,0,10));
		return $d.$p_sign.$m.$p_sign.$y;
	}else return $pStr;
}

function showquery($squery){
	echo "<textarea cols=100 rows=5>".$squery."</textarea>";
}

function insert_login_history($p_action,$p_result,$p_id=""){
	if ($p_id=="") $p_id=$_SESSION['id'];

	return pg_query("insert into skeleton.tbllogin_log (action,result,userid,username,session_id,login_ip) values('".$p_action."','".$p_result."','".$p_id."','".$_SESSION["username"]."','". session_id() ."','". $_SERVER['REMOTE_ADDR'] ."')");
}

function is_mac_ok(){
	exec("ipconfig/all",$l_result);
	if (array_find("Physical Address. . . . . . . . . : 00-13-8F-2D-11-99",$l_result)==false) $l_return=false;
	else $l_return=true;

	return $l_return;
}

function array_find($p_needle,$p_haystack){
	$i=0;
    for ($j=0;$j<count($p_haystack);$j++) {
		if($l_text=strstr($p_haystack[$j],$p_needle)) {
			$l_arr_return[$i]=$l_text;
			$i++;
		}
    } 
	if ($l_arr_return) return $l_arr_return;
    else return false; 
}

$__url="website/";
?>