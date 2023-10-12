<?
//ADVANCE INTERNAL

function cek_error_module(){
	global $j_action,$strmsg;
	
	$no_voucher=$_REQUEST["no_voucher"];

	$p_table="data_fa.tbladvance";
	$p_where=" no_voucher='".$no_voucher."'";
	
	if(pg_num_rows(pg_query("select * from ".$p_table." where ".$p_where." and status_data='Approve'"))){
		$strmsg.="No Voucher ini sudah di-approve.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
	}	
}


function save_additional(){
	global $l_success;
	
	$no_voucher=$_REQUEST["no_voucher"];
	$fk_cabang=$_REQUEST["fk_cabang"];	
	$fk_bank=$_REQUEST["fk_bank"];
	$keterangan=convert_sql($_REQUEST["keterangan"]);
	$tbl="data_fa.tbladvance";
	$lwhere="no_voucher='".$no_voucher."'";
	
	if(!pg_query(insert_log($tbl,$lwhere,'UB'))) $l_success=0;
	if(!pg_query("update ".$tbl." SET status_data='Approve', tgl_approve='".today_db." ".date("H:i:s")."' where ".$lwhere."")) $l_success=0;
	//showquery("update ".$tbl." SET status_data='Approve', tgl_approve='".today_db."' where ".$lwhere."");
	if(!pg_query(insert_log($tbl,$lwhere,'UA'))) $l_success=0;

	$lrs=pg_query("select * from ".$tbl."_detail where fk_voucher = '".$no_voucher."'");
	$i=0;
	while($lrow=pg_fetch_array($lrs)){		
		$arrPost["coa".$i]= array('type'=>'d','value'=>$lrow["nominal"],'account'=>$fk_cabang.'.'.$lrow["fk_coa"],'reference'=>$keterangan);
		$i++;
	}
	
	$nominal_masuk=0;
	$nominal_keluar=0;
	
	$selisih=$_REQUEST['selisih'];
	$selisih=str_replace(',','',$selisih);
	$selisih=str_replace('(','',$selisih);
	$selisih=str_replace(')','',$selisih);
	$selisih=round($selisih);
	
	$account						= get_coa_bank($fk_bank,$fk_cabang);
	
	if($selisih>0){
		$nominal_masuk=$selisih;
		$arrPost["bank_masuk"]			= array('type'=>'d','value'=>$selisih,'account'=>$account,'reference'=>$keterangan);
	}
	else if($selisih<0){
		$nominal_keluar=$selisih*-1;
		$arrPost["bank_keluar"]			= array('type'=>'c','value'=>$selisih*-1,'account'=>$account,'reference'=>$keterangan);
	}
		
	if($selisih!=0){
		$ket="Penyelesaian Advance Internal (".$keterangan.")";
		if(!pg_query(update_saldo_bank($fk_bank,$fk_cabang,$nominal_masuk,$nominal_keluar,$ket,$no_voucher)))$l_success=0;	
	}
	
	$total=$_REQUEST["total"];
	$fk_coa_bdd=$fk_cabang.'.'.$_REQUEST["fk_coa_bdd"];
	
	$arrPost[$fk_coa_bdd]			= array('type'=>'c','value'=>$total,'account'=>$fk_coa_bdd,'reference'=>$keterangan);
	//cek_balance_array_post($arrPost);
	//$fk_cabang
	if(!posting('PENYELESAIAN ADVANCE INTERNAL',$no_voucher,today_db,$arrPost,$fk_cabang,'00'))$l_success=0;	
	
	//$l_success=0;
	
}	

?>

