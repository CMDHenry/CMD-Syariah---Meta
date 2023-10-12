<?

//ADVANCE EXTERNAL
function save_additional(){
	global $l_success;
	$fk_cabang=$_REQUEST["fk_cabang"];
	
	$fk_bank=$_REQUEST["fk_bank"];
	$fk_bank_ho=$_REQUEST["fk_bank_ho"];
	$fk_cabang=$_REQUEST["fk_cabang"];
	$fk_cabang_ho=$_REQUEST["fk_cabang_ho"];
	$total=str_replace(',','',$_REQUEST['total']);
	$no_voucher=$_REQUEST["no_voucher"];
	$keterangan=convert_sql($_REQUEST["keterangan"]);
	$selisih=str_replace(',','',$_REQUEST['total'])-str_replace(',','',$_REQUEST['total_detail']);


	$account 			= get_coa_bank($fk_bank,$fk_cabang);
	$account_ho 		= get_coa_bank($fk_bank_ho,$fk_cabang_ho);
	$rpkc				= get_coa_cabang($fk_cabang,($fk_cabang_ho!=cabang_ho?$fk_cabang_ho:NULL));

	$fk_coa_bdd=$fk_cabang.'.'.$_REQUEST["fk_coa_bdd"];

	$lrs=pg_query("select * from data_fa.tbladvance_detail where fk_voucher = '".$no_voucher."'");
	$i=0;
	while($lrow=pg_fetch_array($lrs)){
		$arrPost["coa".$i]	= array('type'=>'d','value'=>$lrow["nominal"],'account'=>$fk_cabang.'.'.$lrow["fk_coa"],'reference'=>$keterangan);
		$i++;
	}
	
	//$account_selisih=$fk_cabang.'.5511000999';
	//,'account'=>$account_selisih
	if($selisih>0){
		$arrPost["rpkp"]	= array('type'=>'d','value'=>$selisih,'reference'=>$keterangan);
		$arrPost["bank"]	= array('type'=>'c','value'=>$selisih,'reference'=>$keterangan,'account'=>$account);
		$arrPost["biaya_operasional_lainnya"]	= array('type'=>'d','value'=>$selisih,'reference'=>$keterangan);
	}else if($selisih<0){
		$arrPost["rpkp"]	= array('type'=>'c','value'=>$selisih*-1,'reference'=>$keterangan);
		$arrPost["bank"]	= array('type'=>'d','value'=>$selisih*-1,'reference'=>$keterangan,'account'=>$account);
		$arrPost["biaya_operasional_lainnya"]	= array('type'=>'c','value'=>$selisih*-1,'reference'=>$keterangan);
	}
	$arrPost[$fk_coa_bdd]	= array('type'=>'c','value'=>$total,'account'=>$fk_coa_bdd,'reference'=>$keterangan);
	//cek_balance_array_post($arrPost);
	if(!posting('PENYELESAIAN ADVANCE EKSTERNAL',$no_voucher,today_db,$arrPost,$fk_cabang,'00'))$l_success=0;	

	$nominal_masuk=$nominal_keluar=$nominal_masuk_ho=$nominal_keluar_ho=0;

	
	$arrPost=array();
	if($selisih>0){
		$nominal_keluar=$selisih;
		$nominal_masuk_ho=$selisih;
		
		$arrPost["bank"]		= array('type'=>'d','value'=>$selisih,'reference'=>$keterangan,'account'=>$account_ho);
		$arrPost["rpkc"]		= array('type'=>'c','value'=>$selisih,'reference'=>$keterangan,'account'=>$rpkc);
		
	}
	else if($selisih<0){
		$nominal_keluar_ho=$selisih*-1;
		$nominal_masuk=$selisih*-1;

		$arrPost["rpkc"]		= array('type'=>'d','value'=>$selisih*-1,'reference'=>$keterangan,'account'=>$rpkc);
		$arrPost["bank"]		= array('type'=>'c','value'=>$selisih*-1,'reference'=>$keterangan,'account'=>$account_ho);
	}
	if($selisih!=0){
		//cek_balance_array_post($arrPost);
		if(!posting('PENYELESAIAN ADVANCE EKSTERNAL',$no_voucher,today_db,$arrPost,$fk_cabang_ho,'00'))$l_success=0;	
	}
	
	
	$ket="Penyelesaian Advance Eksternal (".$keterangan.")";
	if($selisih!=0){
		
		
		if(!pg_query(update_saldo_bank($fk_bank,$fk_cabang,$nominal_masuk,$nominal_keluar,$ket,$no_voucher,NULL,$account_ho)))$l_success=0;	
		//showquery(update_saldo_bank($fk_bank,$fk_cabang,$nominal_masuk,$nominal_keluar,$ket,$no_voucher,NULL,$account_ho));
		if($nominal_keluar_ho<=0){
			//$account=NULL;
		}
		
		if(!pg_query(update_saldo_bank($fk_bank_ho,$fk_cabang_ho,$nominal_masuk_ho,$nominal_keluar_ho,$ket,$no_voucher,NULL,$account)))$l_success=0;	
		
	     //showquery(update_saldo_bank($fk_bank_ho,$fk_cabang_ho,$nominal_masuk_ho,$nominal_keluar_ho,$ket,$no_voucher,NULL,$account));
	}


	//$l_success=0;
	
}	

?>

