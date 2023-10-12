<?
//PETTY CASH

function cek_error_module(){
	global $strmsg,$j_action;
	$total=str_replace(',','',$_REQUEST['total']);

	if($total>1000000){
		if($_SESSION['jenis_user']!='HO'){
			$strmsg.="Total lebih dari 1.000.000 harus diapprove oleh HO.<br>";
			//$j_action="lInputClose=getObjInputClose();lInputClose.close();";
		}
		if($_REQUEST['fk_cabang']==cabang_ho){
			$jabatan=get_rec("tbluser left join tblkaryawan on fk_karyawan=npk left join tbljabatan on fk_jabatan=kd_jabatan","nm_jabatan","username='".$_SESSION["username"]."'");
			if(strtoupper($jabatan)!='MANAGER FINANCE' && strtoupper($jabatan)!='MANAGER ACCOUNTING'){
				$strmsg.="Total lebih dari 1.000.000 harus diapprove oleh MANAGER FINANCE/ACCOUNTING.<br>";
				//$j_action="lInputClose=getObjInputClose();lInputClose.close();";
			}
		}		
	}	
	
	$no_voucher=$_REQUEST["no_voucher"];	
	$lrs=pg_query("select * from data_fa.tblpetty_cash_detail 
	where fk_voucher = '".$no_voucher."' ");
	
	while($lrow=pg_fetch_array($lrs)){
		//showquery("select * from tblcabang_detail_bank where fk_coa='".$lrow["fk_coa"]."'");
		if(pg_num_rows(pg_query("select * from tblcabang_detail_bank where fk_coa='".$lrow["fk_coa"]."'"))){
			$strmsg.="Detail tidak bisa diisi akun bank, untuk mutasi antar bank bisa lewat menu mutasi<br>";	
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
	$arrPost = array();

	$lrs=pg_query("select * from data_fa.tblpetty_cash_detail 
	where fk_voucher = '".$no_voucher."' ");
	$i=0;
	while($lrow=pg_fetch_array($lrs)){
		$total+=$lrow["nominal"];	
		$catatan=convert_sql($lrow["catatan"]);
		//echo $catatan;
		//$fk_divisi=get_rec("tbltemplate_coa","fk_jenis_cabang","coa ='".$lrow["fk_coa"]."'");
		//,'divisi_coa'=>$fk_divisi
		//reference_type=> untuk flag keterangan detail
		//keterangan=> untuk isi keterangan detail
		$arrPost["coa".$i]	= array('type'=>'d','value'=>$lrow["nominal"],'account'=>$fk_cabang.'.'.$lrow["fk_coa"],'reference'=>$keterangan,'reference_type'=>'DETAIL','keterangan'=>$catatan);	
		$account 			= get_coa_bank($fk_bank,$fk_cabang);
		$arrPost["bank".$i]	= array('type'=>'c','value'=>$lrow["nominal"],'account'=>$account,'reference'=>$keterangan,'reference_type'=>'DETAIL','keterangan'=>$catatan);
		
		if(!pg_query(update_saldo_bank($fk_bank,$fk_cabang,0,$lrow["nominal"] ,"".$catatan."",$no_voucher)))$l_success=0;
			
		$i++;		
	}	
	
	//cek_balance_array_post($arrPost);
	if(!posting('PETTY CASH',$no_voucher,$tgl_voucher,$arrPost,$fk_cabang,'00'))$l_success=0;	
	//echo $l_success;
		
	//$l_success=0;
}

?>


