<?

//PAYMENT REQUEST
function save_additional(){
	global $l_success;
	$keterangan=convert_sql($_REQUEST["keterangan"]);
	$no_voucher=$_REQUEST["no_voucher"];
	$fk_bank_ho=$_REQUEST["fk_bank_ho"];
	$fk_cabang_ho=$_REQUEST["fk_cabang_ho"];
	$fk_cabang=$_REQUEST["fk_cabang"];
	$total_header=str_replace(',','',$_REQUEST['total']);
	
	$lrs=pg_query("select * from data_fa.tblpayment_request_detail 
	where fk_voucher = '".$no_voucher."' ");
	$i=0;
	
	while($lrow=pg_fetch_array($lrs)){
		//POSTING biaya
		if (!strstr($lrow["fk_coa"],'212100000')){
			$total+=$lrow["nominal"];	
			$arrPost["coa".$i]	= array('type'=>'d','value'=>$lrow["nominal"],'account'=>($fk_cabang_ho!=$fk_cabang?$fk_cabang:$fk_cabang_ho).'.'.$lrow["fk_coa"],'reference'=>$keterangan);			
		}
		$i++;
	}
	$account = get_coa_bank($fk_bank_ho,$fk_cabang_ho);

	if($fk_cabang_ho!=$fk_cabang){
		$arrPost["rpkp"]		= array('type'=>'c','value'=>$total_header,'reference'=>$keterangan);
		//cek_balance_array_post($arrPost);
		if(!posting('PAYMENT REQUEST',$no_voucher,today_db,$arrPost,$fk_cabang,'00'))$l_success=0;
		
		$arrPost = array();
		$rpkc					= get_coa_cabang($fk_cabang,($fk_cabang_ho!=cabang_ho?$fk_cabang_ho:NULL));
		$arrPost["rpkc"]      	= array('type'=>'d','value'=>$total_header,'account'=>$rpkc,'reference'=>$keterangan);
	}

	$lrs=pg_query("select * from data_fa.tblpayment_request_detail 
	where fk_voucher = '".$no_voucher."' ");
	$i=0;
	while($lrow=pg_fetch_array($lrs)){
		//POSTING pajak
		if (strstr($lrow["fk_coa"],'212100000')){
			$total-=$lrow["nominal"];
			$arrPost["pajak".$i]	= array('type'=>'c','value'=>$lrow["nominal"],'account'=>$fk_cabang_ho.'.'.$lrow["fk_coa"],'reference'=>$keterangan);
		}
		$i++;
	}
	$arrPost["bank"]		= array('type'=>'c','value'=>$total,'account'=>$account,'reference'=>$keterangan);
	//cek_balance_array_post($arrPost);
	if(!posting('PAYMENT REQUEST',$no_voucher,today_db,$arrPost,$fk_cabang_ho,'00'))$l_success=0;
	
	$ket="Payment Request (".$keterangan.")";
	if(!pg_query(update_saldo_bank($fk_bank_ho,$fk_cabang_ho,0,$total,$ket,$no_voucher)))$l_success=0;	
	//$l_success=0;
}

function cek_error_module(){
	global $strmsg,$j_action;
	
	if(!pg_num_rows(pg_query("select * from data_fa.tblpayment_request_detail  where fk_voucher='".$_REQUEST["id_edit"]."' "))&&$_REQUEST["pstatus"]=="approve"){
		$strmsg.="Detil Payment Request kosong.<br>";
	}
	
	
	
}
?>

