<?
//MUTASI BANK CABANG - CABANG
function save_additional(){
	global $l_success,$strmsg;
	
	$no_voucher=$_REQUEST["no_voucher"];
	$total=str_replace(',','',$_REQUEST["total"]);
	$keterangan=$_REQUEST["keterangan"];
	
	$fk_bank_masuk=$_REQUEST["fk_bank_masuk"];
	$fk_bank_keluar=$_REQUEST["fk_bank_keluar"];
	$fk_cabang_masuk=$_REQUEST["fk_cabang_masuk"];
	$fk_cabang_keluar=$_REQUEST["fk_cabang_keluar"];
	$fk_cabang_ho=(is_pt=='t'?get_cabang_pt($fk_cabang_keluar):cabang_ho);
	$fk_cabang_ho1=(is_pt=='t'?get_cabang_pt($fk_cabang_masuk):cabang_ho);
	
	if($fk_cabang_ho!=$fk_cabang_ho1){
		$strmsg.="Cabang berasal dari Wilayah berbeda<br>";		
		$l_success=0;
	}

	//POSTING
	$account_masuk 				= get_coa_bank($fk_bank_masuk,$fk_cabang_masuk);
	$account_keluar 			= get_coa_bank($fk_bank_keluar,$fk_cabang_keluar);

	$rpkc_keluar				= get_coa_cabang($fk_cabang_keluar,($fk_cabang_ho!=cabang_ho?$fk_cabang_ho:NULL));
	$rpkc_masuk					= get_coa_cabang($fk_cabang_masuk,($fk_cabang_ho!=cabang_ho?$fk_cabang_ho:NULL));	
	
	$arrPost = array();
	$arrPost["bank_masuk"]		= array('type'=>'d','value'=>$total,'reference'=>$keterangan,'account'=>$account_masuk);
	$arrPost["rpkp"]			= array('type'=>'c','value'=>$total,'reference'=>$keterangan);
	if(!posting('MUTASI BANK CABANG - CABANG',$no_voucher,today_db,$arrPost,$fk_cabang_masuk,'00'))$l_success=0;
	
	$arrPost = array();
	$arrPost["rpkc_masuk"]		= array('type'=>'d','value'=>$total,'reference'=>$keterangan,'account'=>$rpkc_masuk);	
	$arrPost["rpkc_keluar"]		= array('type'=>'c','value'=>$total,'reference'=>$keterangan,'account'=>$rpkc_keluar);
	//cek_balance_array_post($arrPost);
	if(!posting('MUTASI BANK CABANG - CABANG',$no_voucher,today_db,$arrPost,$fk_cabang_ho,'00'))$l_success=0;

	$arrPost = array();
	$arrPost["rpkp"]			= array('type'=>'d','value'=>$total,'reference'=>$keterangan);		
	$arrPost["bank_keluar"]		= array('type'=>'c','value'=>$total,'reference'=>$keterangan,'account'=>$account_keluar);
	
	
	if(!posting('MUTASI BANK CABANG - CABANG',$no_voucher,today_db,$arrPost,$fk_cabang_keluar,'00'))$l_success=0;
	
	$ket="Mutasi Bank Cabang - Cabang";		
	//UPDATE SALDO
	if(!pg_query(update_saldo_bank($fk_bank_masuk,$fk_cabang_masuk,$total,0,$ket,$no_voucher,NULL,$account_keluar)))$l_success=0;
	if(!pg_query(update_saldo_bank($fk_bank_keluar,$fk_cabang_keluar,0,$total,$ket,$no_voucher,NULL,$account_masuk)))$l_success=0;
	
	//$l_success=0;
	
}	

?>

