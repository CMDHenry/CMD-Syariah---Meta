<?
function cek_error_module(){
	global $j_action,$strmsg;
	
	$tipe='gadai';
	
	if($_SESSION["jenis_user"]!='HO'){
	  $strmsg="Menu ini hanya bisa diakses oleh HO.<br>";  
	  $j_action= "lInputClose=getObjInputClose();lInputClose.close()";
 	}
	
	if(pg_num_rows(pg_query("select * from data_fa.tblpelunasan_".$tipe." where fk_sbg='".$_REQUEST["fk_sbg"]."' and status_pelunasan ='Batal' and no_pelunasan_".$tipe."!='".$_REQUEST["id_edit"]."'"))){
		$strmsg.="No SBG ini sudah pernah dibatalkan pelunasan sekali.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
	}	

	if(pg_num_rows(pg_query("select * from data_fa.tblfintech_ap where fk_sbg ='".$_REQUEST["fk_sbg"]."' --and tgl_bayar_ap is not null"))){
		$strmsg.="Pelunasan SBG yang diambil fintech tidak bisa dibatalkan<br>";
		
	}


}


function save_additional(){
	global $l_success;
		
	//ANGSURAN DIBALIKIN

	$lwhere="fk_sbg='".$_REQUEST["fk_sbg"]."' and no_kwitansi='".$_REQUEST["id_edit"]."'";
	if(!pg_query(insert_log("data_fa.tblangsuran",$lwhere,'UB'))) $l_success=0;
	if(!pg_query("update data_fa.tblangsuran SET tgl_bayar=NULL,no_kwitansi = NULL where ".$lwhere."")) $l_success=0;
	//showquery("update data_fa.tblangsuran SET tgl_bayar=NULL,no_kwitansi = NULL where ".$lwhere."");
	if(!pg_query(insert_log("data_fa.tblangsuran",$lwhere,'UA'))) $l_success=0;


	$fk_cabang=get_rec("tblinventory","fk_cabang","fk_sbg='".$fk_sbg."'");	
		
	
	//SALDO BANK DIBALIKIN 
	$lrs=pg_query("select * from data_fa.tblhistory_bank
	where id_tr = '".$_REQUEST["fk_sbg"]."' and no_referensi='".$_REQUEST["id_edit"]."'
    order by pk_id");
	
	while($lrow=pg_fetch_array($lrs)){
	  if($lrow["nominal_masuk"]>0){
			if(!pg_query(update_saldo_bank($lrow["fk_bank"],$lrow["fk_cabang"],0,$lrow["nominal_masuk"],"Batal ".$lrow["keterangan"],$_REQUEST["fk_sbg"])))$l_success=0;		
		}
		else if($lrow["nominal_keluar"]>0){
			if(!pg_query(update_saldo_bank($lrow["fk_bank"],$lrow["fk_cabang"],$lrow["nominal_keluar"],0,"Batal ".$lrow["keterangan"],$_REQUEST["fk_sbg"])))$l_success=0;	
		}
		$fk_cabang=$lrow["fk_cabang"];
	}
	
	//JURNAL BALIK GL AUTO	
	$query="select * from data_accounting.tblgl_auto
	left join tblcoa on fk_coa_d = coa or fk_coa_c=coa
	left join tbljenis_transaksi on used_for=kd_jenis_transaksi
	where fk_owner = '".$_REQUEST["fk_sbg"]."' and type_owner in('PELUNASAN GADAI','PELUNASAN GADAI-FINTECH')
	order by no_bukti";
	$lrs=pg_query($query);
	//showquery($query);
	$i=0;
	while($lrow=pg_fetch_array($lrs)){
		$total=$lrow["total"];
		if($lrow["fk_coa_d"]!=""){
			   $account=$lrow["fk_coa_d"];
			   $type='c';
		}
		elseif($lrow["fk_coa_c"]!=""){
			   $account=$lrow["fk_coa_c"];
			   $type='d';
		 }
		
		$arrPost[$account.$i]  = array('type'=>$type,'value'=>$total,'account'=>$account,'fk_supplier'=>($lrow["fk_supplier"]==""?NULL:$lrow["fk_supplier"]),'reference'=>$lrow["reference_transaksi"]);
		$i++;
		
		if($lrow["jenis_account"]=='ar'){
			$query1="select * from data_accounting.tbl".$lrow["jenis_account"]." where no_transaksi='".$_REQUEST["fk_sbg"]."'
			and fk_coa='".$lrow["coa"]."' and bulan = ".date('n',strtotime(today_db))." and tahun = ".date('Y',strtotime(today_db))					
			." ";
			//showquery($query1);
			$lrs1=pg_query($query1);
			$lrow1=pg_fetch_array($lrs1);
			$saldo=$lrow1["saldo"];			
			//UPDATE SALDO AR	
			if($saldo>0){
				$l_success=0;
				$strmsg="Pelunasan sudah Dibatalkan.<br>";			
				$j_action= "lInputClose=getObjInputClose();lInputClose.close()";					
			}else{
				$l_arr_update["saldo"]=($lrow["total"]);
				$l_arr_update["jumlah_alokasi"]=($lrow["total"])*-1;
				$l_arr_update["jumlah_approve"]=($lrow["total"])*-1;
				//echo $lrow["total"];
				if(!update_saldo_ar($_REQUEST["fk_sbg"],$lrow["used_for"],today_db,$l_arr_update,$fk_cabang))$l_success=0;
			}
		}

	}	
	//$fk_cabang
	//cek_balance_array_post($arrPost);
	if(!posting('BATAL PELUNASAN GADAI',$_REQUEST["fk_sbg"],today_db,$arrPost,$fk_cabang,'00'))$l_success=0;
	
	//MASUKIN ULANG KE STOK
	$fk_cabang=get_rec("tblinventory","fk_cabang","fk_sbg='".$_REQUEST["fk_sbg"]."'");
	$kd_lajur=get_lokasi_storing($fk_cabang);	
	if(!pg_query(storing($_REQUEST["fk_sbg"],$kd_lajur,'Terima','Batal Pelunasan')))$l_success=0;	
	
	//$l_success=0;
	
}	

?>

