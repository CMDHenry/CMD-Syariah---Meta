<?
//ADVANCE INTERNAL

function query_additional($no_voucher){
	global $l_success;

	$fk_bank=$_REQUEST["fk_bank"];
	$fk_cabang=$_REQUEST["fk_cabang"];
	$total=$_REQUEST["total"];
	$keterangan=convert_sql($_REQUEST["keterangan"]);
	//$no_voucher=$_REQUEST["no_voucher"];
	
	$account					= get_coa_bank($fk_bank,$fk_cabang);
	$fk_coa_bdd					= $fk_cabang.'.'.$_REQUEST["fk_coa_bdd"];
	
	$arrPost[$fk_coa_bdd]		= array('type'=>'d','value'=>$total,'account'=>$fk_coa_bdd,'reference'=>$keterangan);
	$arrPost[$account]			= array('type'=>'c','value'=>$total,'account'=>$account,'reference'=>$keterangan);	
	//cek_balance_array_post($arrPost);
	if(!posting('ADVANCE INTERNAL',$no_voucher,today_db,$arrPost,$fk_cabang,'00'))$l_success=0;	
	$ket="Advance Internal (".$keterangan.")";
	if(!pg_query(update_saldo_bank($fk_bank,$fk_cabang,0,$total,$ket,$no_voucher)))$l_success=0;	
	//$l_success=0;
	
}	

?>

