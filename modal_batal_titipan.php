<?php

include_once("modal_approve_custom.php");

function get_additional(){
	global $pstatus,$j_action,$strmsg;
	$pstatus='batal';
	if($_SESSION["jenis_user"]!='HO'){
		$strmsg="Error :<br> Menu ini hanya bisa diakses oleh HO.<br>";		
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
	}	
	
	$fk_sbg=$_REQUEST["fk_sbg"];
	
	if(!pg_num_rows(pg_query("select * from tblinventory where fk_sbg='".$fk_sbg."' and status_sbg='Liv'"))){
		$strmsg.="SBG tidak ada / sudah lunas<br>";
	}		
	
	$total=round(str_replace(',','',$_REQUEST["total"]));
	$saldo_titipan=get_rec("data_fa.tbltitipan","saldo_titipan","fk_sbg='".$fk_sbg."'");	
	if($saldo_titipan<$total){
		$strmsg.="Error :<br> Sisa titipan tidak mencukupi untuk batal<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
	}	
}

function save_data(){
	global $j_action,$strmsg,$id_menu,$kd_module,$id_edit, $upload_path,$kd_tabs,$kd_tabs2,$kd_tabs3,$upload_path_website_pic,$l_success;
	$l_success=1;	
	pg_query("BEGIN");	

	$no_voucher=$_REQUEST["no_voucher"];
	$total=round(str_replace(',','',$_REQUEST["total"]));
	
	$fk_sbg=$_REQUEST["fk_sbg"];
	//
	$fk_bank=$_REQUEST["fk_bank"];
	$fk_cabang=get_rec("tblinventory","fk_cabang","fk_sbg='".$fk_sbg."'");
	$account 						= get_coa_bank($fk_bank,$fk_cabang);

	$arrPost = array();
	$arrPost["bank"]				= array('type'=>'c','value'=>$total,'reference'=>$no_voucher,'account'=>$account);
	$arrPost["kl_titipan_gadai"]	= array('type'=>'d','value'=>$total,'reference'=>$no_voucher);	
	//cek_balance_array_post($arrPost);
	if(!posting('BATAL INPUT TITIPAN',$fk_sbg,today_db,$arrPost,$fk_cabang,'00'))$l_success=0;
	
	$ket="Batal Input Titipan";		
	if(!pg_query(update_saldo_bank($fk_bank,$fk_cabang,0,$total,$ket,$fk_sbg,$no_voucher)))$l_success=0;
	//showquery(update_saldo_titipan($fk_sbg,$total));
	if(!pg_query(update_saldo_titipan($fk_sbg,($total*-1))))$l_success=0;
	
	
	$tbl="data_fa.tblinput_titipan";
	//SBG DIBATALIN
	$lwhere="no_voucher='".$no_voucher."'";
	if(!pg_query(insert_log($tbl,$lwhere,'UB')))$l_success=0;	
	if(!pg_query("update ".$tbl." set status_data='Batal',tgl_batal='".today_db."' where ".$lwhere.""))$l_success=0; 
	if(!pg_query(insert_log($tbl,$lwhere,'UA')))$l_success=0;
	

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