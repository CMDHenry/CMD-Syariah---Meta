<?
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/convert.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/referer_check.inc.php';
require '../classes/recordset_ajax.class.php';

if (!mRefererCheck("list_select.php") || !isset($_SESSION['id'])){
//	echo "err:1000";
} else{
	$module=$_REQUEST["module"];
	$pk_id_module=$_REQUEST["pk_id_module"];
$select_list=$_REQUEST["select_list"];
	if ($select_list){
		del_data($pk_id_module);
	}
	
	$row=pg_fetch_array(pg_query("select * from skeleton.tblmenu where pk_id='".$module."'"));
	$field=pg_field_name(pg_query($row["list_sql"]),0);
	$page=$_REQUEST["page"];
	$strorderby=$_REQUEST["strorderby"];
	if($strorderby=="") $strorderby=$field;
	$strordertype=$_REQUEST["strordertype"];
	if($strordertype=="") $strordertype="asc";

	include_once("includes/list_data_select.inc.php");
}

function del_data($pk_id_module){
	global $select_list,$pk_id_module,$module;
	pg_query("BEGIN");

	$l_success=1;
	
	$row_pk_id=pg_fetch_array(pg_query("select * from skeleton.tblmodule_fields inner join skeleton.tbldb_table_detail 
on fk_db_table=save_to_table and tbldb_table_detail.kd_field=save_to_field where fk_module='".$pk_id_module."' and is_pk='t'"));	
	if(!pg_query("delete from ".$row_pk_id["save_to_table"]." where ".$row_pk_id["save_to_field"]." in (".stripcslashes($select_list).")")) $l_success=0;
	$l_success=0;
	if($l_success==1){
		pg_query("COMMIT");
		echo "<split>Delete berhasil<split>";
	} else {
		pg_query("ROLLBACK");
		echo "<split>Error :<br>Delete gagal, data sudah pernah digunakan<split>";
		//echo "delete from ".$row_pk_id["save_to_table"]." where ".$row_pk_id["save_to_field"]." in (".stripcslashes($select_list).")"
;
	}
}
