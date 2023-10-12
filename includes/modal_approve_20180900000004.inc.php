<?
//MUTASI BANK HO - WILAYAH
function save_additional(){
	global $l_success;
	
	$no_voucher=$_REQUEST["no_voucher"];
	$total=str_replace(',','',$_REQUEST["total"]);
	$keterangan=$_REQUEST["keterangan"];
	
	$fk_cabang_ho=get_rec("tblsetting","fk_cabang_ho");
	$fk_bank_masuk=$_REQUEST["fk_bank_masuk"];
	$fk_bank_keluar=$_REQUEST["fk_bank_keluar"];
	$fk_cabang_masuk=$_REQUEST["fk_cabang_masuk"];
	$fk_cabang_keluar=$_REQUEST["fk_cabang_keluar"];
	$ket="Mutasi Bank Wilayah - HO";			
	
	//POSTING
	$account_masuk 				= get_coa_bank($fk_bank_masuk,$fk_cabang_masuk);
	$account_keluar 			= get_coa_bank($fk_bank_keluar,$fk_cabang_keluar);

	$piutang					= get_coa_cabang($fk_cabang_keluar);	
		
	$arrPost = array();
	$arrPost["bank_masuk"]			= array('type'=>'d','value'=>$total,'reference'=>$keterangan,'account'=>$account_masuk);
	$arrPost["piutang"]				= array('type'=>'c','value'=>$total,'reference'=>$keterangan,'account'=>$piutang);	
	//cek_balance_array_post($arrPost);
	if(!posting(strtoupper($ket),$no_voucher,today_db,$arrPost,$fk_cabang_masuk,'00'))$l_success=0;	
	$arrPost = array();
	$arrPost["hutang_afiliasi_ho"]	= array('type'=>'d','value'=>$total,'reference'=>$keterangan);	
	$arrPost["bank_keluar"]			= array('type'=>'c','value'=>$total,'reference'=>$keterangan,'account'=>$account_keluar);
	//cek_balance_array_post($arrPost);	
	if(!posting(strtoupper($ket),$no_voucher,today_db,$arrPost,$fk_cabang_keluar,'00'))$l_success=0;	
	
	//UPDATE SALDO
	if(!pg_query(update_saldo_bank($fk_bank_masuk,$fk_cabang_masuk,$total,0,$ket,$no_voucher,NULL,$account_keluar)))$l_success=0;
	//showquery(update_saldo_bank($fk_bank_masuk,$fk_cabang_masuk,$total,0,$ket,$no_voucher,NULL,$account_keluar));
	if(!pg_query(update_saldo_bank($fk_bank_keluar,$fk_cabang_keluar,0,$total,$ket,$no_voucher,NULL,$account_masuk)))$l_success=0;
	
	//$l_success=0;
	
}	

?>

