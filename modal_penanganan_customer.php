<?php

include_once("modal_edit_custom.php");

function save_data(){
	global $j_action,$strmsg,$id_menu,$kd_module,$id_edit, $upload_path,$kd_tabs,$kd_tabs2,$kd_tabs3,$upload_path_website_pic,$hasil,$cara_menghubungi,$fk_kontrak,$tgl_penanganan;
	$l_success=1;
		
	pg_query("BEGIN");
	
	if(!pg_query(" INSERT INTO data_gadai.tblpenanganan_customer (fk_sbg,cara_menghubungi,hasil,tgl_penanganan) values('".$_REQUEST["fk_sbg"]."', '".$_REQUEST["cara_menghubungi"]."','".$_REQUEST["hasil"]."','#".today_db." ".date("H:i:s")."#')			 	"))$l_success=0;
				 
	$lid_penanganan = get_last_id("data_gadai.tblpenanganan_customer","no_penanganan");
	 
	//log begin
	if(!pg_query("insert into data_gadai.tblpenanganan_customer_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from data_gadai.tblpenanganan_customer where no_penanganan='".$lid_penanganan."'")) $l_success=0;
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