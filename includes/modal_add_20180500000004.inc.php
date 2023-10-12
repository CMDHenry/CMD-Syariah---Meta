<?
//ADVANCE INTERNAL TRF

function query_additional($no_voucher){
	global $l_success,$strmsg;

	$fk_bank_ho=$_REQUEST["fk_bank_ho"];
	$fk_cabang=$_REQUEST["fk_cabang"];
	$fk_cabang_ho=$_REQUEST["fk_cabang_ho"];
	$total=$_REQUEST["total"];
	$keterangan=convert_sql($_REQUEST["keterangan"]);
	
	$account					= get_coa_bank($fk_bank_ho,$fk_cabang_ho);
	$fk_coa_bdd					= $fk_cabang.'.'.$_REQUEST["fk_coa_bdd"];
	
	$arrPost[$fk_coa_bdd]		= array('type'=>'d','value'=>$total,'account'=>$fk_coa_bdd,'reference'=>$keterangan);
	if($fk_cabang_ho!=$fk_cabang){
		$arrPost["rpkp"]		= array('type'=>'c','value'=>$total,'reference'=>$keterangan);
		//cek_balance_array_post($arrPost);
		if(!posting('ADVANCE TRANSFER',$no_voucher,today_db,$arrPost,$fk_cabang,'00'))$l_success=0;
		
		$arrPost = array();
		$rpkc					= get_coa_cabang($fk_cabang,($fk_cabang_ho!=cabang_ho?$fk_cabang_ho:NULL));
		$arrPost["rpkc"]      	= array('type'=>'d','value'=>$total,'account'=>$rpkc,'reference'=>$keterangan);
	}
	$arrPost[$account]			= array('type'=>'c','value'=>$total,'account'=>$account,'reference'=>$keterangan);	
	//cek_balance_array_post($arrPost);
	if(!posting('ADVANCE TRANSFER',$no_voucher,today_db,$arrPost,$fk_cabang_ho,'00'))$l_success=0;	
	
	
	$ket="Advance Transfer (".$keterangan.")";
	if(!pg_query(update_saldo_bank($fk_bank_ho,$fk_cabang_ho,0,$total,$ket,$no_voucher)))$l_success=0;	
	
	
	if(is_pt=='t'){
		if($fk_cabang_ho!=get_cabang_pt($fk_cabang)){
			$strmsg.="Cabang berasal dari Wilayah berbeda<br>";		
			$l_success=0;
		}
	}
	
	//$l_success=0;
	
}	

?>

