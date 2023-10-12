<?php

include_once("modal_approve_custom.php");

function cek_error_module(){
	global $j_action,$strmsg;
	
	$no_voucher=$_REQUEST["no_voucher"];

	$p_table="data_fa.tbladvance";
	$p_where=" no_voucher='".$no_voucher."'";
	
	if(pg_num_rows(pg_query("select * from ".$p_table." where ".$p_where." and is_approval_pengajuan='t'"))){
		$strmsg.="No Voucher ini sudah di-approve pengajuan.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
	}	
	
	if(pg_num_rows(pg_query("select * from ".$p_table." where ".$p_where." and status_data='Batal'"))){
		$strmsg.="No Voucher ini sudah di-batalkan.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
	}	
	
}

function save_data(){
	global $j_action,$strmsg,$id_menu,$kd_module,$id_edit,$l_success;
	$l_success=1;
	pg_query("BEGIN");
	
	$fk_bank=$_REQUEST["fk_bank"];
	$fk_bank_ho=$_REQUEST["fk_bank_ho"];
	$fk_cabang=$_REQUEST["fk_cabang"];
	$fk_cabang_ho=$_REQUEST["fk_cabang_ho"];
	$total=str_replace(',','',$_REQUEST['total']);
	$no_voucher=$_REQUEST["no_voucher"];
	$keterangan=convert_sql($_REQUEST["keterangan"]);
	$fk_coa_bdd=$_REQUEST["fk_coa_bdd"];
	
	$p_table="data_fa.tbladvance";
	$p_where=" no_voucher='".$no_voucher."'";
	
	if(!pg_query(insert_log($p_table,$p_where,'UB')))$l_success=0;
	if(!pg_query("update ".$p_table." set is_approval_pengajuan='t', tgl_approve_pengajuan ='".today_db."' where ".$p_where))$l_success=0;	
	if(!pg_query(insert_log($p_table,$p_where,'UA')))$l_success=0;
	//showquery("update ".$p_table." set is_approval_pengajuan='t', tgl_approve_pengajuan ='".today_db."' where ".$p_where);
	
	$account	 			= get_coa_bank($fk_bank,$fk_cabang);
	$account_ho 			= get_coa_bank($fk_bank_ho,$fk_cabang_ho);
	$rpkc					= get_coa_cabang($fk_cabang,($fk_cabang_ho!=cabang_ho?$fk_cabang_ho:NULL));			
		
	$arrPost = array();
	$arrPost["rpkc"]      	= array('type'=>'d','value'=>$total,'reference'=>$keterangan,'account'=>$rpkc);	
	$arrPost[$account_ho]	= array('type'=>'c','value'=>$total,'reference'=>$keterangan,'account'=>$account_ho);	
	//$fk_cabang_ho
	if(!posting('ADVANCE EKSTERNAL',$no_voucher,today_db,$arrPost,$fk_cabang_ho,'00'))$l_success=0;	
	
	$arrPost = array();
	$arrPost["bank_masuk"]	= array('type'=>'d','value'=>$total,'reference'=>$keterangan,'account'=>$account);
	$arrPost["rpkp"]		= array('type'=>'c','value'=>$total,'reference'=>$keterangan);
	//$fk_cabang
	//cek_balance_array_post($arrPost);
	if(!posting('ADVANCE EKSTERNAL',$no_voucher,today_db,$arrPost,$fk_cabang,'00'))$l_success=0;	
	
	$ket="Advance Eksternal (".$keterangan.")";
	if(!pg_query(update_saldo_bank($fk_bank,$fk_cabang,$total,0,$ket,$no_voucher,NULL,$account_ho)))$l_success=0;	
	if(!pg_query(update_saldo_bank($fk_bank_ho,$fk_cabang_ho,0,$total,$ket,$no_voucher,NULL,$account)))$l_success=0;
	//showquery(update_saldo_bank($fk_bank_ho,$fk_cabang_ho,0,$total,$ket,$no_voucher));	
		
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