<?
function cek_error_module(){
	global $j_action,$strmsg;
	
	$tipe='cicilan';
	
	if($_SESSION["jenis_user"]!='HO'){
	  $strmsg="Menu ini hanya bisa diakses oleh HO.<br>";  
	  $j_action= "lInputClose=getObjInputClose();lInputClose.close()";
 	}
	if(pg_num_rows(pg_query("select * from data_fa.tblpembayaran_cicilan where fk_sbg='".$_REQUEST["fk_sbg"]."' and status_pembayaran ='Batal' and no_kwitansi ='".$_REQUEST["id_edit"]."'"))){
		$strmsg.="No Pembayaran ini sudah pernah dibatalkan sekali.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
	}	
	
	$angsuran_ke=$_REQUEST["angsuran_ke"];
	if(pg_num_rows(pg_query("select * from data_fa.tblpembayaran_cicilan where fk_sbg='".$_REQUEST["fk_sbg"]."' and status_pembayaran is null and angsuran_ke >'".$angsuran_ke."'"))){
		$strmsg.="Tidak bisa dibatalkan. Ada angsuran diatas angsuran ke -".$angsuran_ke." yang belum dibatalkan <br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
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
	
	
/*	//JURNAL BALIK GL AUTO	
	$query="select * from data_accounting.tblgl_auto
	left join tblcoa on fk_coa_d = coa or fk_coa_c=coa
	left join tbljenis_transaksi on used_for=kd_jenis_transaksi
	where fk_owner = '".$fk_sbg."' and reference_transaksi='".$no_kwitansi."'
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
		
		$arrPost[$account.$i]  = array('type'=>$type,'value'=>$total,'account'=>$account,'reference'=>$lrow["reference_transaksi"]);	
		$i++;
		
	}	
*/	
	$type_owner='PEMBAYARAN CICILAN';
	$fk_owner=$fk_sbg;
	$arrPost = gl_balik($fk_owner,$type_owner,$no_kwitansi);
	if(count($arrPost)=='0'){
		$l_success=0;
	}

	//cek_balance_array_post($arrPost);
	if(!posting('BATAL PEMBAYARAN CICILAN',$fk_sbg,today_db,$arrPost,$fk_cabang,'00'))$l_success=0;
		
	//$l_success=0;
		
	
	$type="denda";
	$lwhere="fk_sbg='".$fk_sbg."'";
	$nilai_denda=str_replace(',','',$_REQUEST['total_denda_lalu']);
	if(strstr($nilai_denda,"(")){
		$nilai_denda=str_replace('(','-',$nilai_denda);
		$nilai_denda=str_replace(')','',$nilai_denda);
	}
	
	if(!pg_query(insert_log("data_fa.tbl".$type."",$lwhere,'UB'))) $l_success=0;
	if(!pg_query("update data_fa.tbl".$type." SET saldo_".$type." ='".$nilai_denda."' where ".$lwhere."")) $l_success=0;
	//showquery("update data_fa.tbl".$type." SET saldo_".$type." ='".$nilai_denda."' where ".$lwhere."");
	if(!pg_query(insert_log("data_fa.tbl".$type."",$lwhere,'UA'))) $l_success=0;	
	
		
	$query="select * From data_gadai.tblhistory_sbg
	where fk_sbg='".$fk_sbg."' and referensi='".$no_kwitansi."'
	and transaksi='Titipan'
	";
	$lrs=pg_query($query);
	//showquery($query);
	$lrow=pg_fetch_array($lrs);
	
	$nilai_titipan=round($lrow["nilai_bayar"]);
	$type="titipan";		
	$lwhere="fk_sbg='".$fk_sbg."'";
	if(!pg_query(insert_log("data_fa.tbl".$type."",$lwhere,'UB'))) $l_success=0;
	//showquery("update data_fa.tbl".$type." SET saldo_".$type." ='".$nilai_bayar."' where ".$lwhere."");
	if(!pg_query("update data_fa.tbl".$type." SET saldo_".$type." =saldo_".$type."-'".$nilai_titipan."' where ".$lwhere."")) $l_success=0;
	if(!pg_query(insert_log("data_fa.tbl".$type."",$lwhere,'UA'))) $l_success=0;


	if(!pg_query(insert_history_sbg($fk_sbg,0,0,'-',$no_kwitansi,0,'t'))) $l_success=0;
	
	//showquery(insert_history_sbg($fk_sbg,0,0,'-',$no_kwitansi,0,'t'));
	
}	

?>

