<?
//REKON BANK

function cek_error_module(){
	global $strmsg;
	$no_voucher=$_REQUEST["no_voucher"];	
	$lrs=pg_query("select * from data_fa.tblrekon_bank_detail 
	where fk_voucher = '".$no_voucher."' ");
	
	while($lrow=pg_fetch_array($lrs)){
		//showquery("select * from tblcabang_detail_bank where fk_coa='".$lrow["fk_coa"]."'");
		//kunci khusus bank ga boleh input di detail
		if(pg_num_rows(pg_query("select * from tblcabang_detail_bank where fk_coa='".$lrow["fk_coa"]."'  and fk_bank <'80'"))){
			$strmsg.="Detail tidak bisa diisi akun bank, untuk mutasi antar bank bisa lewat menu mutasi<br>";	
		}
		if($lrow["no_kontrak"] && !pg_num_rows(pg_query("select * from tblinventory where fk_sbg='".$lrow["no_kontrak"]."'")))  {
			$strmsg.="Kontrak tidak ada<br>";	
		}
	}

}


function save_additional(){
	global $l_success;
	
	$fk_cabang=$_REQUEST["fk_cabang"];	
	$fk_bank=$_REQUEST["fk_bank"];
	$no_voucher=$_REQUEST["no_voucher"];	
	$keterangan=$_REQUEST["keterangan"];
	$tgl_voucher=convert_date_english($_REQUEST["tgl_voucher"]);
	
	$lrs=pg_query("select * from data_fa.tblrekon_bank_detail 
	where fk_voucher = '".$no_voucher."' ");
	$i=0;
	
	$account			= get_coa_bank($fk_bank,$fk_cabang);

	$arrPost = array();
	while($lrow=pg_fetch_array($lrs)){
		//TYPE TR INPUTAN MASUK KELUAR TAPI DIGANTI TYPE TR D/C
		$total=$lrow["nominal"];
		$catatan=$lrow["catatan"];
		$ket="".convert_sql($catatan)."";
		if($lrow["type_tr"]=='Masuk'){
			$lrow["type_tr"]='c';
			$type_bank='d';
			if(!pg_query(update_saldo_bank($fk_bank,$fk_cabang,$total,0,$ket,$no_voucher)))$l_success=0;	
			$arrPost["bank".$i]		= array('type'=>$type_bank,'value'=>$total,'account'=>$account,'reference'=>$keterangan,'reference_type'=>'DETAIL','keterangan'=>$catatan);
		}
		elseif($lrow["type_tr"]=='Keluar'){
			$lrow["type_tr"]='d';	
			$type_bank='c';	
			if(!pg_query(update_saldo_bank($fk_bank,$fk_cabang,0,$total,$ket,$no_voucher)))$l_success=0;
			$arrPost["bank".$i]		= array('type'=>$type_bank,'value'=>$total,'account'=>$account,'reference'=>$keterangan,'reference_type'=>'DETAIL','keterangan'=>$catatan);		
		}
		
		$arrPost[$lrow["fk_coa"].$i]= array('type'=>$lrow["type_tr"],'value'=>$total,'account'=>$fk_cabang.'.'.$lrow["fk_coa"],'reference'=>$keterangan,'reference_type'=>'DETAIL','keterangan'=>$catatan);
				
		$i++;	
	}	
	
	//cek_balance_array_post($arrPost);
	if(!posting('REKON BANK',$no_voucher,$tgl_voucher,$arrPost,$fk_cabang,'00'))$l_success=0;	
	
	//$l_success=0;
}

?>

