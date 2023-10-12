<?
//ADVANCE INTERNAL TRF

function cek_error_module(){
	global $j_action,$strmsg;
	
	$no_voucher=$_REQUEST["no_voucher"];

	$p_table="data_fa.tbladvance";
	$p_where=" no_voucher='".$no_voucher."'";
	
	if(pg_num_rows(pg_query("select * from ".$p_table." where ".$p_where." and status_data='Approve'"))){
		$strmsg.="No Voucher ini sudah di-approve.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
	}
	
	if($_REQUEST["strisi_advance_detail"]==NULL){
		$strmsg.="Detail COA Harus diisi.<br>";	
	}	
}


function save_additional(){
	global $l_success;
	
	$no_voucher=$_REQUEST["no_voucher"];
	$fk_cabang=$_REQUEST["fk_cabang"];	
	$fk_cabang_ho=$_REQUEST["fk_cabang_ho"];
	$fk_bank_ho=$_REQUEST["fk_bank_ho"];
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
	
	if($fk_cabang_ho!=$fk_cabang){
		if($selisih>0){
			$arrPost["rpkp"]	= array('type'=>'d','value'=>$selisih,'reference'=>$keterangan);
		}else if($selisih<0){
			$arrPost["rpkp"]	= array('type'=>'c','value'=>$selisih*-1,'reference'=>$keterangan);
		}
		
	}
	$total=$_REQUEST["total"];
	$fk_coa_bdd=$fk_cabang.'.'.$_REQUEST["fk_coa_bdd"];
	
	$arrPost[$fk_coa_bdd]			= array('type'=>'c','value'=>$total,'account'=>$fk_coa_bdd,'reference'=>$keterangan);

	if($fk_cabang_ho!=$fk_cabang){
		//cek_balance_array_post($arrPost);
		if(!posting('PENYELESAIAN ADVANCE TRANSFER',$no_voucher,today_db,$arrPost,$fk_cabang,'00'))$l_success=0;	
		$arrPost=array();
	}

	$account						= get_coa_bank($fk_bank_ho,$fk_cabang_ho);
	$rpkc							= get_coa_cabang($fk_cabang,($fk_cabang_ho!=cabang_ho?$fk_cabang_ho:NULL));
	
	if($selisih>0){
		$nominal_masuk=$selisih;
		$arrPost["bank_masuk"]			= array('type'=>'d','value'=>$selisih,'account'=>$account,'reference'=>$keterangan);
		if($fk_cabang_ho!=$fk_cabang){
			$arrPost["rpkc"]			= array('type'=>'c','value'=>$selisih,'reference'=>$keterangan,'account'=>$rpkc);
		}
	}
	else if($selisih<0){
		$nominal_keluar=$selisih*-1;
		$arrPost["bank_keluar"]			= array('type'=>'c','value'=>$selisih*-1,'account'=>$account,'reference'=>$keterangan);
		if($fk_cabang_ho!=$fk_cabang){
			$arrPost["rpkc"]			= array('type'=>'d','value'=>$selisih*-1,'reference'=>$keterangan,'account'=>$rpkc);
		}
	}
	//cek_balance_array_post($arrPost);
	if(!posting('PENYELESAIAN ADVANCE TRANSFER',$no_voucher,today_db,$arrPost,$fk_cabang_ho,'00'))$l_success=0;	
	if($selisih!=0){		
	
		$ket="Penyelesaian Advance Transfer (".$keterangan.")";
		if(!pg_query(update_saldo_bank($fk_bank_ho,$fk_cabang_ho,$nominal_masuk,$nominal_keluar,$ket,$no_voucher)))$l_success=0;	
	}

	
	//$l_success=0;
	
}	

?>

