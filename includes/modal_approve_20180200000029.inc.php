<?
//MUTASI BANK CABANG - HO
function save_additional(){
	global $l_success;
	
	$no_voucher=$_REQUEST["no_voucher"];
	$keterangan=$_REQUEST["keterangan"];
	
	$total=str_replace(',','',$_REQUEST["total"]);
	$fk_bank_masuk=$_REQUEST["fk_bank_masuk"];
	$fk_bank_keluar=$_REQUEST["fk_bank_keluar"];
	$fk_cabang_masuk=$_REQUEST["fk_cabang_ho"];
	$fk_cabang_keluar=$_REQUEST["fk_cabang_keluar"];
	$ket="Mutasi Bank Cabang - HO";		
	
	$tgl_voucher=convert_date_english($_REQUEST["tgl_voucher"]);
	
	//POSTING	
	$account_masuk 			= get_coa_bank($fk_bank_masuk,$fk_cabang_masuk);
	$account_keluar 		= get_coa_bank($fk_bank_keluar,$fk_cabang_keluar);
		
	$rpkc					= get_coa_cabang($fk_cabang_keluar);
	
	$arrPost = array();
	$arrPost["bank_masuk"]	= array('type'=>'d','value'=>$total,'reference'=>$keterangan,'account'=>$account_masuk);
	$arrPost["rpkc"]		= array('type'=>'c','value'=>$total,'reference'=>$keterangan,'account'=>$rpkc);
	if(!posting('MUTASI BANK CABANG - HO',$no_voucher,$tgl_voucher,$arrPost,$fk_cabang_masuk,'00'))$l_success=0;

	$arrPost = array();
	$arrPost["rpkp"]		= array('type'=>'d','value'=>$total,'reference'=>$keterangan);
	$arrPost["bank_keluar"]	= array('type'=>'c','value'=>$total,'reference'=>$keterangan,'account'=>$account_keluar);
	//echo $tgl_voucher;
	//cek_balance_array_post($arrPost);
	if(!posting('MUTASI BANK CABANG - HO',$no_voucher,$tgl_voucher,$arrPost,$fk_cabang_keluar,'00'))$l_success=0;
	
	
	//UPDATE SALDO
	if(!pg_query(update_saldo_bank($fk_bank_masuk,$fk_cabang_masuk,$total,0,$ket." ".$fk_cabang_keluar,$no_voucher,NULL,$account_keluar)))$l_success=0;
	if(!pg_query(update_saldo_bank($fk_bank_keluar,$fk_cabang_keluar,0,$total,$ket,$no_voucher,NULL,$account_masuk)))$l_success=0;
	
	//$l_success=0;
}	

?>

