<?php

include_once("modal_approve_custom.php");

function cek_error_module(){
	global $j_action,$strmsg;
	
	$no_voucher=$_REQUEST["no_voucher"];

	$p_table="data_fa.tbladvance";
	$p_where=" no_voucher='".$no_voucher."'";
	
	if(pg_num_rows(pg_query("select * from ".$p_table." where ".$p_where." and is_tarik_advance='t'"))){
		$strmsg.="No Voucher ini sudah di-tarik advance.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
	}	
}

function save_data(){
	global $j_action,$strmsg,$id_menu,$kd_module,$id_edit,$l_success;
	$l_success=1;
	pg_query("BEGIN");
	
	$fk_bank=$_REQUEST["fk_bank"];
	$fk_cabang=$_REQUEST["fk_cabang"];
	$total=str_replace(',','',$_REQUEST['total']);
	$no_voucher=$_REQUEST["no_voucher"];
	$keterangan=convert_sql($_REQUEST["keterangan"]);
	$fk_coa_bdd=$fk_cabang.'.'.$_REQUEST["fk_coa_bdd"];
	
	$p_table="data_fa.tbladvance";
	$p_where=" no_voucher='".$no_voucher."'";
	
	if(!pg_query(insert_log($p_table,$p_where,'UB')))$l_success=0;
	if(!pg_query("update ".$p_table." set is_tarik_advance='t', tgl_tarik_advance ='".today_db."' where ".$p_where))$l_success=0;	
	if(!pg_query(insert_log($p_table,$p_where,'UA')))$l_success=0;
	
	$account	 			= get_coa_bank($fk_bank,$fk_cabang);
	$rpkc					= get_coa_cabang($fk_cabang);

	$arrPost["fk_coa_bdd"]	= array('type'=>'d','value'=>$total,'reference'=>$keterangan,'account'=>$fk_coa_bdd);	
	$arrPost["bank_keluar"]	= array('type'=>'c','value'=>$total,'reference'=>$keterangan,'account'=>$account);
	//$fk_cabang
	//cek_balance_array_post($arrPost);
	if(!posting('TARIK ADVANCE EKSTERNAL',$no_voucher,today_db,$arrPost,$fk_cabang,'00'))$l_success=0;	
	
	$ket="Tarik Advance Eksternal (".$keterangan.")";
	if(!pg_query(update_saldo_bank($fk_bank,$fk_cabang,0,$total,$ket,$no_voucher)))$l_success=0;	
		
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