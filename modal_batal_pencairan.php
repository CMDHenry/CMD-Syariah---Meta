<?php

include_once("modal_approve_custom.php");

function get_additional(){
	global $pstatus,$j_action,$strmsg;
	$pstatus='batal';
	if($_SESSION["jenis_user"]!='HO'){
		$strmsg="Error :<br> Menu ini hanya bisa diakses oleh HO.<br>";		
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
	}

}

function save_data(){
	global $j_action,$strmsg,$id_menu,$kd_module,$id_edit, $upload_path,$kd_tabs,$kd_tabs2,$kd_tabs3,$upload_path_website_pic,$l_success;
	$l_success=1;
	
	pg_query("BEGIN");	
	
	$fk_cabang=	$_REQUEST["fk_cabang"];
	$fk_sbg=$_REQUEST["no_sbg"];
	
	//SALDO BANK DIBALIKIN 
	$lrs=pg_query("select * from data_fa.tblhistory_bank
	where id_tr = '".$_REQUEST["no_sbg"]."' and keterangan='PENCAIRAN' order by pk_id");
	
	while($lrow=pg_fetch_array($lrs)){
	  if($lrow["nominal_masuk"]>0){
			if(!pg_query(update_saldo_bank($lrow["fk_bank"],$lrow["fk_cabang"],0,$lrow["nominal_masuk"],"Batal ".$lrow["keterangan"],$_REQUEST["no_sbg"])))$l_success=0;		
		}
		else if($lrow["nominal_keluar"]>0){
			if(!pg_query(update_saldo_bank($lrow["fk_bank"],$lrow["fk_cabang"],$lrow["nominal_keluar"],0,"Batal ".$lrow["keterangan"],$_REQUEST["no_sbg"])))$l_success=0;	
		}
	}

	$fk_owner=$_REQUEST["no_sbg"];
	$type_owner='PENCAIRAN';
	$arrPost = gl_balik($fk_owner,$type_owner);
	if(count($arrPost)>0){
		//cek_balance_array_post($arrPost);
		if(!posting('BATAL '.$type_owner,$fk_owner,today_db,$arrPost,$fk_cabang,'00'))$l_success=0;
	}
	
	if(!pg_query(insert_log("data_gadai.tblproduk_cicilan","no_sbg = '".$fk_sbg."'",'UB')))$l_success=0;		
	if(!pg_query("update data_gadai.tblproduk_cicilan set tgl_pencairan_datun=NULL where no_sbg='".$fk_sbg."'")) $l_success=0;
	if(!pg_query(insert_log("data_gadai.tblproduk_cicilan","no_sbg = '".$fk_sbg."'",'UA')))$l_success=0;		
	
	
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