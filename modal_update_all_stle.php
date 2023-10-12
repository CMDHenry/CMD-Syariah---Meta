<?php

include_once("modal_add_custom.php");

function save_data(){
	global $j_action,$strmsg,$id_menu,$kd_module,$id_edit;
	$l_success=1;
		
	pg_query("BEGIN");
	 //log begin
	if(!pg_query("insert into tblcabang_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from tblcabang ")) $l_success=0;
	//end log
	if(!pg_query("
		update tblcabang set 
			harga_stle_antam =".$_REQUEST["harga_stle_antam"].",
			harga_stle_non_antam =".$_REQUEST["harga_stle_non_antam"].",
			harga_stle_perhiasan =".$_REQUEST["harga_stle_perhiasan"].",
			harga_stp =".$_REQUEST["harga_stp"]."
	"))$l_success=0;
				 
	 //log begin
	if(!pg_query("insert into tblcabang_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from tblcabang")) $l_success=0;
	//end log
	 
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
		$strmsg="Error :<br>".$nm_menu." Gagal Tersimpan.<br>";
		pg_query("ROLLBACK");
	}
}


?>