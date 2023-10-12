<?
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: Fri, 1 Jan 2010 00:00:00 GMT");
include_once("connection_db.inc.php");

$GLOBALS['__ARR_CABANG__'] = array("120");
if(!in_array($_SESSION["kd_cabang"],$GLOBALS['__ARR_CABANG__'])){
	//header("location:index.php");
}
$GLOBALS['__LOCK_MENU__'] = array("1110",'1111','1112');//menu yang mau dilock
$GLOBALS['__LOCK_CABANG__'] = array("");//untuk lock menu dicabang
if(pg_num_rows(pg_query("select * from tblsetting where is_closing_harian='t'"))&& $_SESSION["username"]!='superuser'){
	header("location:index.php");
	exit();
}

if ($_SESSION["id"]=="" ){
	if(strstr($_SERVER['PHP_SELF'],"/report"))header("location:../index.php");
	else header("location:index.php");
}

if($_SESSION["username"]!='superuser'){
	//check_session();
}
function check_session(){
	
	$lrs_session=(pg_query("select * from skeleton.tblsession where lower(username)= '".strtolower($_SESSION["username"])."'"));
	$lrow_session=pg_fetch_array($lrs_session);
	
	$to_time = strtotime(date("Y-m-d H:i:s"));
	$from_time = strtotime($lrow_session["last_access_date"]);
	$selisih_menit=round(($to_time - $from_time) / 60);
	//echo $selisih_menit;
	if($selisih_menit>30 &&	!pg_num_rows(pg_query("select * from skeleton.tblsession where lower(username)= '".strtolower(($_SESSION["username"]))."' and logout_date is null"))){
		pg_query("
		update skeleton.tblsession set
		logout_date='".date("m/d/Y H:i:s")."'
		where username='".$_SESSION["username"]."'
		");

		$_SESSION["id"]=null;
		$_SESSION["username"]=null;
		$_SESSION["kd_karyawan"]=null;
		$_SESSION["kd_cabang"]=null;
		$_SESSION["nm_cabang"]=null;
		$_SESSION["schema"]=null;
		$_SESSION["jenis_user"]=null;
		//header("location:index.php");
	}else {	
		pg_query("
		update skeleton.tblsession set
		last_access_date='".date("m/d/Y H:i:s")."'
		where username='".$_SESSION["username"]."'
		");
	}

}
function check_right($p_menu,$p_autoexit=true){
	//return true;
	$id=$_SESSION["id"];
	//print_r($GLOBALS['__LOCK_CABANG__']);
	
	if($id=="superuser"){
		return true;
	}else{
		if(in_array($_SESSION["kd_cabang"],$GLOBALS['__LOCK_CABANG__']) && in_array($p_menu,$GLOBALS['__LOCK_MENU__']))return false;
		else{	
			$lrow=pg_fetch_array(pg_query("select * from tbluser_menu where fk_user='".$id."' and menu_id in ('".str_replace(",","','",$p_menu)."')"));
			if($lrow){
				return true;
			}else{
				if($p_autoexit==true) header("location:error_access.php");
				return false;
			}
		}
	}

}

function check_right_approve($p_type,$p_tingkat="",$p_level="",$p_cabang=""){
	//$l_return=false;
	$l_return=true;

	if (pg_num_rows(pg_query("select * from tblworkflow_".$p_type." where fk_level='".$p_level."' and fk_cabang='".$p_cabang."' and (fk_karyawan_approval_".$p_tingkat."='".$_SESSION["kd_karyawan"]."' or fk_karyawan_delegate_".$p_tingkat."='".$_SESSION["kd_karyawan"]."')"))) {
		$l_return=true;
	}else $l_return=false;
	
	return $l_return;
}
?>