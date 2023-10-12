<?

//CLOSE CASHIER

function save_additional(){
	global $l_success;
	//leasing ga ad open-close

	
	$fk_cabang=$_REQUEST["fk_cabang"];
	$fk_bank_masuk=$_REQUEST["fk_bank_masuk"];
	$fk_bank_keluar=$_REQUEST["fk_bank_keluar"];
	
	$no_voucher=$_REQUEST["no_voucher"];
	$total=round(str_replace(',','',$_REQUEST["total"]));
//	if(!pg_query(update_saldo_bank($fk_bank_keluar,$fk_cabang,0,$total,"Close Cashier",$no_voucher)))$l_success=0;	
//	if(!pg_query(update_saldo_bank($fk_bank_masuk,$fk_cabang,$total,0,"Close Cashier",$no_voucher)))$l_success=0;
//	
//	
//	$account_masuk 			= get_coa_bank($fk_bank_masuk,$fk_cabang);
//	$account_keluar 		= get_coa_bank($fk_bank_keluar,$fk_cabang);
//	
//	$arrPost["bank_masuk"]		= array('type'=>'d','value'=>$total,'account'=>$account_masuk);
//	$arrPost["bank_keluar"]		= array('type'=>'c','value'=>$total,'account'=>$account_keluar);
//	//print_r($arrPost);
//	//cek_balance_array_post($arrPost);
//	if(!posting('CLOSE CASHIER',$no_voucher,today_db,$arrPost,$fk_cabang,'00'))$l_success=0;
//	
//	$tbl="tblcabang";
//	$lwhere="kd_cabang='".$fk_cabang."'";
//	if(!pg_query(insert_log($tbl,$lwhere,'UB'))) $l_success=0;
//	if(!pg_query("update ".$tbl." SET status_kasir='Close' where ".$lwhere."")) $l_success=0;
//	if(!pg_query(insert_log($tbl,$lwhere,'UA'))) $l_success=0;
	
	//$l_success=0;
}

function cek_error_module(){
	global $strmsg;
	$fk_cabang=$_REQUEST["fk_cabang"];
	if(pg_num_rows(pg_query("select * from tblinventory where status='Belum Terima' and fk_cabang='".$fk_cabang."'"))){
		$strmsg.="Masih ada SBG yang belum dimasukkan ke lajur <br>";			
	}
}

?>

