<?
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/convert.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/referer_check.inc.php';
require '../classes/recordset_ajax.class.php';

if (!mRefererCheck("list_user.php") || !isset($_SESSION['id'])){
//	echo "err:1000";
} else{
	$module=$_REQUEST["module"];
	$pk_id_module=$_REQUEST["pk_id_module"];
	$username = $_REQUEST["username"];
	$nm_karyawan = $_REQUEST["nm_karyawan"];
	$page=$_REQUEST["page"];
	$strorderby=$_REQUEST["strorderby"];
	if($strorderby=="") $strorderby="username";
	$strordertype=$_REQUEST["strordertype"];
	if($strordertype=="") $strordertype="asc";
	
	include_once("includes/list_user.inc.php");
}

?>