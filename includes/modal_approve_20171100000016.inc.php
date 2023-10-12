<?
//MUTASI BANK HO - CABANG

function save_additional(){
	global $l_success,$strmsg;
	//echo $_REQUEST["jenis_mutasi"];
	$no_voucher=$_REQUEST["no_voucher"];
	$keterangan=$_REQUEST["keterangan"];
	$lrs=pg_query("select * from data_fa.tblmutasi_bank_detail 
	where fk_voucher = '".$no_voucher."' ");
	$i=0;
	
	$rpkp	= get_coa_cabang(get_rec("tblsetting","fk_cabang_ho"));
	$tgl_voucher=convert_date_english($_REQUEST["tgl_voucher"]);
	
	while($lrow=pg_fetch_array($lrs)){
		$total=$lrow["nominal"];
		if($_REQUEST["jenis_mutasi"]=="Dropping"){
			$fk_bank_masuk=$_REQUEST["fk_bank_masuk"];
			$fk_cabang_masuk=$lrow["fk_cabang"];
			$fk_bank_keluar=$_REQUEST["fk_bank_keluar"];
			$fk_cabang_keluar=$_REQUEST["fk_cabang_ho"];
			$ket="Mutasi Bank -Dropping ".$fk_cabang_masuk;	
			$rpkc= get_coa_cabang($fk_cabang_masuk);
					
		}elseif($_REQUEST["jenis_mutasi"]=="Refund"){
			$fk_bank_keluar=$_REQUEST["fk_bank_keluar"];
			$fk_cabang_keluar=$lrow["fk_cabang"];
			$fk_bank_masuk=$_REQUEST["fk_bank_masuk"];
			$fk_cabang_masuk=$_REQUEST["fk_cabang_ho"];
			$ket="Mutasi Bank -Refund ".$fk_cabang_keluar;
			$rpkc= get_coa_cabang($fk_cabang_keluar);
		}
						
		$account_masuk 				= get_coa_bank($fk_bank_masuk,$fk_cabang_masuk);
		$account_keluar 			= get_coa_bank($fk_bank_keluar,$fk_cabang_keluar);
		
		if(!pg_num_rows(pg_query("select * from tblcabang_detail_bank where fk_bank='".$fk_bank_masuk."' and fk_cabang='".$fk_cabang_masuk."'"))){
			$strmsg.="Bank ".$fk_bank_masuk." tidak ada pada cabang ".$fk_cabang_masuk.".<br>";  
		}
		
		if(!pg_num_rows(pg_query("select * from tblcabang_detail_bank where fk_bank='".$fk_bank_keluar."' and fk_cabang='".$fk_cabang_keluar."'"))){
			$strmsg.="Bank ".$fk_bank_keluar." tidak ada pada cabang ".$fk_cabang_keluar.".<br>";  
		}		
		
		if($_REQUEST["jenis_mutasi"]=="Dropping"){
			$arrPost = array();
			$arrPost["bank_masuk".$i]	= array('type'=>'d','value'=>$total,'account'=>$account_masuk,'reference'=>$keterangan);
			$arrPost["rpkp"]			= array('type'=>'c','value'=>$total,'reference'=>$keterangan);
			if(!posting('MUTASI BANK HO - CABANG (DROPPING)',$no_voucher,$tgl_voucher,$arrPost,$fk_cabang_masuk,'00'))$l_success=0;

			$arrPost = array();
			$arrPost["rpkc".$i]			= array('type'=>'d','value'=>$total,'account'=>$rpkc,'reference'=>$keterangan);
			$arrPost["bank_keluar".$i]	= array('type'=>'c','value'=>$total,'account'=>$account_keluar,'reference'=>$keterangan);						
			if(!posting('MUTASI BANK HO - CABANG (DROPPING)',$no_voucher,$tgl_voucher,$arrPost,$fk_cabang_keluar,'00'))$l_success=0;

		}elseif($_REQUEST["jenis_mutasi"]=="Refund"){
			$arrPost = array();
			$arrPost["rpkp"]			= array('type'=>'d','value'=>$total,'reference'=>$keterangan);
			$arrPost["bank_keluar".$i]	= array('type'=>'c','value'=>$total,'account'=>$account_keluar,'reference'=>$keterangan);
			if(!posting('MUTASI BANK HO - CABANG (REFUND)',$no_voucher,$tgl_voucher,$arrPost,$fk_cabang_keluar,'00'))$l_success=0;
			
			$arrPost = array();
			$arrPost["bank_masuk".$i]	= array('type'=>'d','value'=>$total,'account'=>$account_masuk,'reference'=>$keterangan);		
			$arrPost["rpkc".$i]			= array('type'=>'c','value'=>$total,'account'=>$rpkc,'reference'=>$keterangan);
			if(!posting('MUTASI BANK HO - CABANG (REFUND)',$no_voucher,$tgl_voucher,$arrPost,$fk_cabang_masuk,'00'))$l_success=0;
			
		}
		
		
		if(!pg_query(update_saldo_bank($fk_bank_masuk,$fk_cabang_masuk,$total,0,$ket,$no_voucher,NULL,$account_keluar)))$l_success=0;
		if(!pg_query(update_saldo_bank($fk_bank_keluar,$fk_cabang_keluar,0,$total,$ket,$no_voucher,NULL,$account_masuk)))$l_success=0;

		$i++;
		
	}
	//cek_balance_array_post($arrPost);

	//$l_success=0;
}

?>

