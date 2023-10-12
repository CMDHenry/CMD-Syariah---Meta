<?
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/convert.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/referer_check.inc.php';
require '../classes/recordset_ajax.class.php';

if (!mRefererCheck("list_gl_auto.php") || !isset($_SESSION['id'])){
//	echo "err:1000";
} else{
	$module=$_REQUEST["module"];
	$pk_id_module=$_REQUEST["pk_id_module"];
	$fk_owner = $_REQUEST["fk_owner"];
	$type_owner = $_REQUEST["type_owner"];
	$fk_cabang = $_REQUEST["fk_cabang"];
	$reference_transaksi = $_REQUEST['reference_transaksi'];
	$tr_date = $_REQUEST["tr_date"];
	$page=$_REQUEST["page"];
	$strorderby=$_REQUEST["strorderby"];
	if($strorderby=="") $strorderby="fk_owner";
	$strordertype=$_REQUEST["strordertype"];
	if($strordertype=="") $strordertype="asc";
	include_once("includes/list_gl_auto.inc.php");
}

?>