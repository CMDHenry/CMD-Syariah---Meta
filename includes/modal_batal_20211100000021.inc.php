<?
function cek_error_module(){
	global $j_action,$strmsg;
		
	if($_SESSION["jenis_user"]!='HO'){
	  $strmsg="Menu ini hanya bisa diakses oleh HO.<br>";  
	  $j_action= "lInputClose=getObjInputClose();lInputClose.close()";
 	}
	if(pg_num_rows(pg_query("select * from data_fa.tblpembayaran_bpkb where fk_sbg='".$_REQUEST["fk_sbg"]."' and status_pembayaran ='Batal' and no_kwitansi ='".$_REQUEST["id_edit"]."'"))){
		$strmsg.="No Pembayaran ini sudah pernah dibatalkan sekali.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
	}	
	


}

function save_additional(){
	global $l_success;
	
	
	//SALDO BANK DIBALIKIN 
	$lrs=pg_query("select * from data_fa.tblhistory_bank
	where id_tr = '".$_REQUEST["fk_sbg"]."' and no_referensi='".$_REQUEST["id_edit"]."'
    order by pk_id");
	
	while($lrow=pg_fetch_array($lrs)){
	  if($lrow["nominal_masuk"]>0){
			if(!pg_query(update_saldo_bank($lrow["fk_bank"],$lrow["fk_cabang"],0,$lrow["nominal_masuk"],"Batal ".$lrow["keterangan"],$_REQUEST["fk_sbg"],$_REQUEST["id_edit"])))$l_success=0;		
		}
		else if($lrow["nominal_keluar"]>0){
			if(!pg_query(update_saldo_bank($lrow["fk_bank"],$lrow["fk_cabang"],$lrow["nominal_keluar"],0,"Batal ".$lrow["keterangan"],$_REQUEST["fk_sbg"],$_REQUEST["id_edit"])))$l_success=0;	
		}
		$fk_cabang=$lrow["fk_cabang"];
	}
	
	
	//JURNAL BALIK GL AUTO	
	$query="select * from data_accounting.tblgl_auto
	left join tblcoa on fk_coa_d = coa or fk_coa_c=coa
	left join tbljenis_transaksi on used_for=kd_jenis_transaksi
	where fk_owner = '".$_REQUEST["id_edit"]."'
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
		
		$type_owner=$lrow["type_owner"];
	}	
	//cek_balance_array_post($arrPost);
	if(!posting('BATAL '.$type_owner,$_REQUEST["id_edit"],$lrow["tr_date"],$arrPost,$fk_cabang,'00'))$l_success=0;
		
	//$l_success=0;
	
}	
?>