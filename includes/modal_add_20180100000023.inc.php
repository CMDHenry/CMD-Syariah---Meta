<?
//MUTASI BANK INTERNAL CABANG

function query_additional($no_voucher){
	global $l_success;
	$fk_cabang=$_REQUEST["fk_cabang"];
	$fk_bank_masuk=$_REQUEST["fk_bank_masuk"];
	$fk_bank_keluar=$_REQUEST["fk_bank_keluar"];
	$keterangan=$_REQUEST["keterangan"];
	$tgl_voucher=convert_date_english($_REQUEST["tgl_voucher"]);
	
	
	$tbl="data_fa.tblmutasi_bank";
	$lwhere="no_voucher='".$no_voucher."'";
	if(!pg_query(insert_log($tbl,$lwhere,'UB'))) $l_success=0;
	if(!pg_query("update ".$tbl." SET status_data='Approve' where ".$lwhere."")) $l_success=0;
	if(!pg_query(insert_log($tbl,$lwhere,'UA'))) $l_success=0;
		
	//POSTING		
	$total=str_replace(',','',$_REQUEST['total']);
	
	$account_masuk 				= get_coa_bank($fk_bank_masuk,$fk_cabang);
	$account_keluar 			= get_coa_bank($fk_bank_keluar,$fk_cabang);
	
	$arrPost["bank_masuk"]		= array('type'=>'d','value'=>$total,'account'=>$account_masuk,'reference'=>$keterangan);
	$arrPost["bank_keluar"]		= array('type'=>'c','value'=>$total,'account'=>$account_keluar,'reference'=>$keterangan);
	
	//cek_balance_array_post($arrPost);
	if(!posting('MUTASI BANK INTERNAL CABANG',$no_voucher,$tgl_voucher,$arrPost,$fk_cabang,'00'))$l_success=0;
	
	$ket="Mutasi Bank Internal Cabang";
	if(!pg_query(update_saldo_bank($fk_bank_masuk,$fk_cabang,$total,0,$ket,$no_voucher,NULL,$account_keluar)))$l_success=0;
	if(!pg_query(update_saldo_bank($fk_bank_keluar,$fk_cabang,0,$total,$ket,$no_voucher,NULL,$account_masuk)))$l_success=0;
	
	//$l_success=0;
}	

?>

