<?

//PAYMENT REQUEST dari HO
function save_additional(){
	global $l_success;
	$keterangan=convert_sql($_REQUEST["keterangan"]);
	$no_voucher=$_REQUEST["no_voucher"];
	$fk_bank_ho=$_REQUEST["fk_bank_ho"];
	$fk_cabang_ho=$_REQUEST["fk_cabang_ho"];
	//$fk_cabang=$_REQUEST["fk_cabang"];
	$total_header=str_replace(',','',$_REQUEST['total']);
	$tgl_voucher=convert_date_english($_REQUEST["tgl_voucher"]);
	//$lrs_cab=pg_query("select * from data_fa.tblpayment_request_detail_cabang
	//where fk_voucher = '".$no_voucher."' ");

	//while($lrow_cab=pg_fetch_array($lrs_cab)){
		$total=0;
		$fk_cabang=$lrow_cab["fk_cabang_detail"];
		
		$lrs=pg_query("select * from data_fa.tblpayment_request_detail 
		where fk_voucher = '".$no_voucher."' ");
		$i=0;
		$arrPost2 = array();
		while($lrow=pg_fetch_array($lrs)){
			//POSTING biaya
			$fk_cabang=$lrow["fk_cabang"];
			$total+=$lrow["nominal"];	
			
			if($fk_cabang!=cabang_ho){			
				$arrPost = array();
				$arrPost["coa".$i]		= array('type'=>'d','value'=>$lrow["nominal"],'account'=>($fk_cabang).'.'.$lrow["fk_coa"],'reference'=>$keterangan);				
				$arrPost["rpkp"]		= array('type'=>'c','value'=>$lrow["nominal"],'reference'=>$keterangan);
				//cek_balance_array_post($arrPost);
				if(!posting('PAYMENT REQUEST (HO)',$no_voucher,$tgl_voucher,$arrPost,$fk_cabang,'00'))$l_success=0;
			}

			$rpkc						= ($fk_cabang).'.'.$lrow["fk_coa"];
			if($fk_cabang!=cabang_ho){
				$rpkc					= get_coa_cabang($fk_cabang,($fk_cabang_ho!=cabang_ho?$fk_cabang_ho:NULL));
			}
			$arrPost2["rpkc".$i]    = array('type'=>'d','value'=>$lrow["nominal"],'account'=>$rpkc,'reference'=>$keterangan);		
			
			$i++;
		}						
		
		$account = get_coa_bank($fk_bank_ho,$fk_cabang_ho);		
		$arrPost2["bank"]		= array('type'=>'c','value'=>$total_header,'account'=>$account,'reference'=>$keterangan);		
		//cek_balance_array_post($arrPost2);
		if(!posting('PAYMENT REQUEST (HO)',$no_voucher,$tgl_voucher,$arrPost2,$fk_cabang_ho,'00'))$l_success=0;
		$total_all+=$total;
	//}
	$ket= "Payment Request HO (".$keterangan.")";
	if(!pg_query(update_saldo_bank($fk_bank_ho,$fk_cabang_ho,0,$total_header,$ket,$no_voucher)))$l_success=0;	
	//showquery(update_saldo_bank($fk_bank_ho,$fk_cabang_ho,0,$total_all,$ket,$no_voucher));
	//$l_success=0;
}

function cek_error_module(){
	global $strmsg,$j_action;
	
	if(!pg_num_rows(pg_query("select * from data_fa.tblpayment_request_detail  where fk_voucher='".$_REQUEST["id_edit"]."' "))&&$_REQUEST["pstatus"]=="approve"){
		$strmsg.="Detil Payment Request kosong.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		
	}
	
	if(!pg_num_rows(pg_query("select * from data_fa.tblpayment_request_detail_cabang where fk_voucher='".$_REQUEST["no_voucher"]."'"))&&$_REQUEST["pstatus"]=="approve"){
		//$strmsg.="Detail Cabang kosong.<br>";
		//$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
	}	

	$no_voucher=$_REQUEST["no_voucher"];	
	$lrs=pg_query("select * from data_fa.tblpayment_request_detail 
	where fk_voucher = '".$no_voucher."' ");
	
	while($lrow=pg_fetch_array($lrs)){
		//showquery("select * from tblcabang_detail_bank where fk_coa='".$lrow["fk_coa"]."'");
		if(pg_num_rows(pg_query("select * from tblcabang_detail_bank where fk_coa='".$lrow["fk_coa"]."'"))){
			$strmsg.="Detail tidak bisa diisi akun bank, untuk mutasi antar bank bisa lewat menu mutasi<br>";	
		}
	}

	
	
}
?>

