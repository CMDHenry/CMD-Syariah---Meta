<?

//PENCAIRAN AGENT FEE

function save_additional(){
	global $l_success,$no_voucher;
	//$fk_cabang=$_REQUEST["fk_cabang"];
	$fk_sbg=$_REQUEST["fk_sbg"];
	$fk_cabang=get_rec("tblinventory","fk_cabang","fk_sbg='".$fk_sbg."'");
	$fk_bank_keluar=$_REQUEST["fk_bank"];
	$no_voucher=$_REQUEST["no_voucher"];
	
	$total=round(str_replace(',','',$_REQUEST["total"]));
	if(!pg_query(update_saldo_bank($fk_bank_keluar,$fk_cabang,0,$total,"Pencairan Agent Fee",$no_voucher)))$l_success=0;	
	//showquery(update_saldo_bank($fk_bank_keluar,$fk_cabang,0,$total,"Pencairan Agent Fee",$no_voucher));
	$account_keluar 		= get_coa_bank($fk_bank_keluar,$fk_cabang);
	
	$arrPost["biaya_fee_agent"]		= array('type'=>'d','value'=>$total,'reference'=>$fk_sbg);
	$arrPost["bank_keluar"]		= array('type'=>'c','value'=>$total,'account'=>$account_keluar,'reference'=>$fk_sbg);
	//print_r($arrPost);
	//cek_balance_array_post($arrPost);
	if(!posting('PENCAIRAN AGENT FEE',$no_voucher,today_db,$arrPost,$fk_cabang,'00'))$l_success=0;
	//echo $l_success;	
	//$l_success=0;
}

?>

