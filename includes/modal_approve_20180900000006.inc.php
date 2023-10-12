<?
//MUTASI BANK WILAYAH - CABANG

function save_additional(){
	global $l_success,$strmsg;
	//echo $_REQUEST["jenis_mutasi"];
	$no_voucher=$_REQUEST["no_voucher"];
	$keterangan=$_REQUEST["keterangan"];
	$lrs=pg_query("select * from data_fa.tblmutasi_bank_detail 
	where fk_voucher = '".$no_voucher."' ");
	$i=0;
	
	$rpkp	= get_coa_cabang(get_rec("tblsetting","fk_cabang_ho"));

	while($lrow=pg_fetch_array($lrs)){
		$total=$lrow["nominal"];
		if($_REQUEST["jenis_mutasi"]=="Dropping"){
			$fk_bank_masuk=$_REQUEST["fk_bank_masuk"];
			$fk_cabang_masuk=$lrow["fk_cabang"];
			$fk_bank_keluar=$_REQUEST["fk_bank_keluar"];
			$fk_cabang_keluar=$_REQUEST["fk_cabang_ho"];
			$ket="Mutasi Bank -Dropping ".$fk_cabang_masuk;	
			$rpkc= get_coa_cabang($fk_cabang_masuk,$fk_cabang_keluar);
					
		}elseif($_REQUEST["jenis_mutasi"]=="Refund"){
			$fk_bank_keluar=$_REQUEST["fk_bank_keluar"];
			$fk_cabang_keluar=$lrow["fk_cabang"];
			$fk_bank_masuk=$_REQUEST["fk_bank_masuk"];
			$fk_cabang_masuk=$_REQUEST["fk_cabang_ho"];
			$ket="Mutasi Bank -Refund ".$fk_cabang_keluar;
			$rpkc= get_coa_cabang($fk_cabang_keluar,$fk_cabang_masuk);
		}
						
		$account_masuk 				= get_coa_bank($fk_bank_masuk,$fk_cabang_masuk);
		$account_keluar 			= get_coa_bank($fk_bank_keluar,$fk_cabang_keluar);
		
		if($_REQUEST["jenis_mutasi"]=="Dropping"){
			$ket1="MUTASI BANK DROPPING WILAYAH - CABANG";
			$arrPost = array();
			$arrPost["bank_masuk".$i]	= array('type'=>'d','value'=>$total,'account'=>$account_masuk,'reference'=>$keterangan);
			$arrPost["rpkp"]			= array('type'=>'c','value'=>$total,'reference'=>$keterangan);
			//cek_balance_array_post($arrPost);
			if(!posting($ket1,$no_voucher,today_db,$arrPost,$fk_cabang_masuk,'00'))$l_success=0;

			$arrPost = array();
			$arrPost["rpkc".$i]			= array('type'=>'d','value'=>$total,'account'=>$rpkc,'reference'=>$keterangan);
			$arrPost["bank_keluar".$i]	= array('type'=>'c','value'=>$total,'account'=>$account_keluar,'reference'=>$keterangan);						
			//cek_balance_array_post($arrPost);
			if(!posting($ket1,$no_voucher,today_db,$arrPost,$fk_cabang_keluar,'00'))$l_success=0;

		}elseif($_REQUEST["jenis_mutasi"]=="Refund"){
			$ket1="MUTASI BANK REFUND WILAYAH - CABANG";
			$arrPost = array();
			$arrPost["rpkp"]			= array('type'=>'d','value'=>$total,'reference'=>$keterangan);
			$arrPost["bank_keluar".$i]	= array('type'=>'c','value'=>$total,'account'=>$account_keluar,'reference'=>$keterangan);
			if(!posting($ket1,$no_voucher,today_db,$arrPost,$fk_cabang_keluar,'00'))$l_success=0;
			
			$arrPost = array();
			$arrPost["bank_masuk".$i]	= array('type'=>'d','value'=>$total,'account'=>$account_masuk,'reference'=>$keterangan);		
			$arrPost["rpkc".$i]			= array('type'=>'c','value'=>$total,'account'=>$rpkc,'reference'=>$keterangan);
			if(!posting($ket1,$no_voucher,today_db,$arrPost,$fk_cabang_masuk,'00'))$l_success=0;
			
		}
		
		
		if(!pg_query(update_saldo_bank($fk_bank_masuk,$fk_cabang_masuk,$total,0,$ket,$no_voucher,NULL,$account_keluar)))$l_success=0;
		if(!pg_query(update_saldo_bank($fk_bank_keluar,$fk_cabang_keluar,0,$total,$ket,$no_voucher,NULL,$account_masuk)))$l_success=0;

		$i++;
	
		
		if(pg_num_rows(pg_query("select * from tblcabang where kd_cabang='".$lrow["fk_cabang"]."' and kd_cabang in (select fk_cabang_wilayah from tblwilayah)"))){
			$strmsg.="".$lrow["fk_cabang"]." adalah Wilayah.<br>";  
			$l_success=0;
		}else if(!pg_num_rows(pg_query("select * from tblcabang where kd_cabang='".$lrow["fk_cabang"]."' and fk_wilayah in (select kd_wilayah from tblwilayah where fk_cabang_wilayah='".$_REQUEST["fk_cabang_ho"]."')"))){
			$strmsg.="".$lrow["fk_cabang"]." bukan termasuk wilayah ".$_REQUEST["fk_cabang_ho"]." .<br>";  
			$l_success=0;
		}				
		
	}
	//cek_balance_array_post($arrPost);

	//$l_success=0;
}

?>

