<?php

include_once("modal_approve_custom.php");

function save_data(){
	global $j_action,$strmsg,$id_menu,$kd_module,$id_edit, $upload_path,$kd_tabs,$kd_tabs2,$kd_tabs3,$upload_path_website_pic;
	$l_success=1;
	
	pg_query("BEGIN");
	
	$kd_brankas=$_REQUEST["kd_brankas"];
	
	//DELETE LAJUR
	$p_table="tbllajur";
	$p_where="fk_laci in (select kd_laci from tbllaci where fk_brankas='".$kd_brankas."')";
	//showquery("select * from ".$p_table." where ".$p_where." and qty_on_hand>0");
	
	if(pg_num_rows(pg_query("select * from ".$p_table." where ".$p_where." and qty_on_hand>0"))){
		$strmsg="Brankas masih ada isinya.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		$l_success=0;
	}

	if(!pg_query(insert_log($p_table,$p_where,'DB')))$l_success=0;
	if(!pg_query("delete from ".$p_table." where ".$p_where))$l_success=0;
	
	//DELETE LACI
	$p_table="tbllaci";
	$p_where="fk_brankas='".$kd_brankas."'";
	if(!pg_query(insert_log($p_table,$p_where,'DB')))$l_success=0;
	if(!pg_query("delete from ".$p_table." where ".$p_where))$l_success=0;
	
	//DELETE BRANKAS
	$p_table="tblbrankas";
	$p_where="kd_brankas='".$kd_brankas."'";
	if(!pg_query(insert_log($p_table,$p_where,'DB')))$l_success=0;
	if(!pg_query("delete from ".$p_table." where ".$p_where))$l_success=0;
	
	//DELETE BRANKAS DETAIL
	$pk_id_log=get_last_id($p_table."_log","pk_id_log");
	$p_where="fk_brankas='".$kd_brankas."'";	
	
	if(!pg_query("insert into ".$p_table."_detail_log select *,'".$pk_id_log."' from ".$p_table."_detail where ".$p_where." ")) $l_success=0;
	
	if(!pg_query("delete from ".$p_table."_detail where ".$p_where))$l_success=0;	
	
	//$l_success=0;
	if ($l_success==1){
		$lrs_kosong=pg_query("select * from skeleton.tblmodule inner join skeleton.tblmodule_fields on skeleton.tblmodule.pk_id=fk_module where fk_menu='".$id_menu."' and is_edit is true");
		while($lrow_kosong=pg_fetch_array($lrs_kosong)){
			$_REQUEST[$lrow_kosong["kd_field"]]="";
			$_REQUEST[str_replace("[]","",$lrow_kosong["kd_field"]."1")]="";
			$_REQUEST[str_replace("[]","",$lrow_kosong["kd_field"]."2")]="";
		}
		
		$lrs_detail_kosong=pg_query("select * from skeleton.tblmodule_detail where fk_module='".$kd_module."'");
		//showquery("select * from skeleton.tblmodule_detail where fk_module='".$kd_module."'");
		while($lrow_detail_kosong=pg_fetch_array($lrs_detail_kosong)){
			$_REQUEST["strisi_".$lrow_detail_kosong["kd_module_detail"]]="";	
		}	
		$strisi1="";
		$strmsg=$nm_menu." Tersimpan.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		pg_query("COMMIT");
	}else{
		$strmsg.="Error :<br>".$nm_menu." Gagal Tersimpan.<br>";
		pg_query("ROLLBACK");
	}
}


?>