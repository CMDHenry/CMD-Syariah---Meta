<?
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/convert.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/numeric.inc.php';
require '../requires/referer_check.inc.php';
require '../classes/recordset_ajax.class.php';

if (!mRefererCheck("list.php") || !isset($_SESSION['id'])){
//	echo "err:1000";
} else{
	$module=$_REQUEST["module"];
	$pk_id_module=$_REQUEST["pk_id_module"];
	//echo $pk_id_module;
	$page=$_REQUEST["page"];
	$strorderby=$_REQUEST["strorderby"];
	if($strorderby=="" || $strorderby=="undefined") $strorderby=(get_rec("skeleton.tblmodule_fields","save_to_field","fk_module='".$pk_id_module."' and is_view='t'","no_urut_edit"))?get_rec("skeleton.tblmodule_fields","save_to_field","fk_module='".$pk_id_module."' and is_view='t'","no_urut_edit"):get_rec("skeleton.tblmodule_fields","reference_field_name","fk_module='".$pk_id_module."' and is_view='t'","no_urut_edit");
	//echo get_rec("skeleton.tblmodule_fields","save_to_field","fk_module='".$pk_id_module."'","no_urut_edit");
	$strordertype=$_REQUEST["strordertype"];
	if($strordertype=="" || $strordertype=="undefined") $strordertype="desc";
	
	$is_historis=$_REQUEST["is_historis"];
	include_once("includes/list_data.inc.php");
}