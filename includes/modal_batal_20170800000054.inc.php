<?
function cek_error_module(){
	global $j_action,$strmsg;
	
	$tipe='cicilan';
	
	if($_SESSION["jenis_user"]!='HO'){
	  $strmsg="Menu ini hanya bisa diakses oleh HO.<br>";  
	  $j_action= "lInputClose=getObjInputClose();lInputClose.close()";
 	}
	
	if(pg_num_rows(pg_query("select * from data_fa.tblpelunasan_".$tipe." where fk_sbg='".$_REQUEST["fk_sbg"]."' and status_pelunasan ='Batal' and no_kwitansi!='".$_REQUEST["id_edit"]."'"))){
		///$strmsg.="No SBG ini sudah pernah dibatalkan pelunasan sekali.<br>";
		//$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
	}	
	
	if(pg_num_rows(pg_query("select * from data_gadai.tbltaksir_umum where no_sbg_ar='".$_REQUEST["fk_sbg"]."' and tgl_serah_terima_bpkb is not null"))){
		$strmsg.="No Kontrak ini ,BPKBnya harus dikembalikan dahulu oleh cabang.<br>";
		//$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
	}		
}

function save_additional(){
	global $l_success;
	
	//ANGSURAN DIBALIKIN
	$fk_sbg=$_REQUEST["fk_sbg"];
	$no_kwitansi=$_REQUEST["id_edit"];

	$lwhere="fk_sbg='".$fk_sbg."' and no_kwitansi='".$no_kwitansi."'";
	if(!pg_query(insert_log("data_fa.tblangsuran",$lwhere,'UB'))) $l_success=0;
	if(!pg_query("update data_fa.tblangsuran SET tgl_bayar=NULL,no_kwitansi = NULL where ".$lwhere."")) $l_success=0;
	//showquery("update data_fa.tblangsuran SET tgl_bayar=NULL,no_kwitansi = NULL where ".$lwhere."");
	if(!pg_query(insert_log("data_fa.tblangsuran",$lwhere,'UA'))) $l_success=0;

	$fk_cabang=get_rec("tblinventory","fk_cabang","fk_sbg='".$fk_sbg."'");

	//SALDO BANK DIBALIKIN 
	$lrs=pg_query("select * from data_fa.tblhistory_bank
	where id_tr = '".$fk_sbg."' and no_referensi='".$no_kwitansi."'
    order by pk_id");
	
	while($lrow=pg_fetch_array($lrs)){
	  if($lrow["nominal_masuk"]>0){
			if(!pg_query(update_saldo_bank($lrow["fk_bank"],$lrow["fk_cabang"],0,$lrow["nominal_masuk"],"Batal ".$lrow["keterangan"],$fk_sbg,$no_kwitansi)))$l_success=0;		
		}
		else if($lrow["nominal_keluar"]>0){
			if(!pg_query(update_saldo_bank($lrow["fk_bank"],$lrow["fk_cabang"],$lrow["nominal_keluar"],0,"Batal ".$lrow["keterangan"],$fk_sbg,$no_kwitansi)))$l_success=0;	
		}
		$fk_cabang=$lrow["fk_cabang"];
	}
	
	
	//JURNAL BALIK GL AUTO	
	$query="select * from data_accounting.tblgl_auto
	left join tblcoa on fk_coa_d = coa or fk_coa_c=coa
	left join tbljenis_transaksi on used_for=kd_jenis_transaksi
	where fk_owner = '".$fk_sbg."' and type_owner in('PELUNASAN CICILAN')
	order by no_bukti";
	$lrs=pg_query($query);
	//showquery($query);
	$i=0;
/*	while($lrow=pg_fetch_array($lrs)){
		$total=$lrow["total"];
		if($lrow["fk_coa_d"]!=""){
			   $account=$lrow["fk_coa_d"];
			   $type='c';
		}
		elseif($lrow["fk_coa_c"]!=""){
			   $account=$lrow["fk_coa_c"];
			   $type='d';
		 }
		
		$arrPost[$account.$i]  = array('type'=>$type,'value'=>$total,'account'=>$account,'reference'=>$lrow["reference_transaksi"]);	
		$i++;
		
	}	
*/	
	$type_owner='PELUNASAN CICILAN';
	$fk_owner=$fk_sbg;
	$arrPost = gl_balik($fk_owner,$type_owner,$no_kwitansi);
	if(count($arrPost)=='0'){
		$l_success=0;
	}

	//cek_balance_array_post($arrPost);
	if(!posting('BATAL PELUNASAN CICILAN',$fk_sbg,today_db,$arrPost,$fk_cabang,'00'))$l_success=0;
	
	//MASUKIN ULANG KE STOK
	$fk_cabang=get_rec("tblinventory","fk_cabang","fk_sbg='".$fk_sbg."'");
	$kd_lajur=get_lokasi_storing($fk_cabang);	
	$kd_lajur='-';
	if(!pg_query(storing($fk_sbg,$kd_lajur,'Terima','Batal Pelunasan')))$l_success=0;	
	//showquery(storing($fk_sbg,$kd_lajur,'Terima','Batal Pelunasan'));
	//$l_success=0;
	
	if(!pg_query(insert_history_sbg($fk_sbg,0,0,'-',$no_kwitansi,0,'t'))) $l_success=0;
	//showquery(insert_history_sbg($fk_sbg,0,0,'-',$no_kwitansi,0,'t'));

}	

?>

