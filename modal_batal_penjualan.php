<?php

include_once("modal_approve_custom.php");

function get_additional(){
	global $pstatus,$j_action,$strmsg;
	$pstatus='batal';
	if($_SESSION["jenis_user"]!='HO'){
		$strmsg="Error :<br> Menu ini hanya bisa diakses oleh HO.<br>";		
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
	}
}

function save_data(){
	global $j_action,$strmsg,$id_menu,$kd_module,$id_edit, $upload_path,$kd_tabs,$kd_tabs2,$kd_tabs3,$upload_path_website_pic,$l_success;
	
	$l_success=1;		
	
	pg_query("BEGIN");	
	$no_lelang=	$_REQUEST["no_kwitansi"];
	$fk_sbg=	$_REQUEST["fk_sbg"];
	$jenis_transaksi=	$_REQUEST["jenis_transaksi"];
	$lrow=pg_fetch_array(pg_query("select * from tblinventory left join (select ang_ke ,fk_sbg as fk_sbg1 from viewang_ke)as tbl on fk_sbg1=fk_sbg where fk_sbg='".$fk_sbg."'"));
	$ang_ke=$lrow["ang_ke"];	
	
	//BARANG DIBALIKIN TAPI HARUS DITERIMA DULU
	if(!strstr(strtoupper($jenis_transaksi),'ASURANSI')){
		if(!pg_query(storing($fk_sbg,'-','Tarik','Batal Pelunasan')))$l_success=0;
	}else{
		if(!pg_query(storing($fk_sbg,'-','Terima','Batal Pelunasan')))$l_success=0;
	}
	if(!pg_query(insert_history_sbg($fk_sbg,'0',$ang_ke,'Batal '.$jenis_transaksi,$no_lelang,0,'t'))) $l_success=0;			
	
	//SALDO BANK DIBALIKIN 
	$query="select * from data_fa.tblhistory_bank
	where (no_referensi = '".$no_lelang."') 
	order by pk_id";
	$lrs=pg_query($query);
	//showquery($query);
	while($lrow=pg_fetch_array($lrs)){
	  if($lrow["nominal_masuk"]>0){
			if(!pg_query(update_saldo_bank($lrow["fk_bank"],$lrow["fk_cabang"],0,$lrow["nominal_masuk"],"Batal ".$lrow["keterangan"],$fk_sbg,$_REQUEST["id_edit"])))$l_success=0;		
		}
		else if($lrow["nominal_keluar"]>0){
			if(!pg_query(update_saldo_bank($lrow["fk_bank"],$lrow["fk_cabang"],$lrow["nominal_keluar"],0,"Batal ".$lrow["keterangan"],$fk_sbg,$_REQUEST["id_edit"])))$l_success=0;	
		}
	}
	
	//GL AUTO JURNAL BALIK
/*	$query="select * from data_accounting.tblgl_auto
	left join tblcoa on fk_coa_d = coa or fk_coa_c=coa
	left join tbljenis_transaksi on used_for=kd_jenis_transaksi
	where fk_owner = '".$fk_sbg."' and reference_transaksi='".$no_lelang."'
	order by no_bukti
	";
	
	$lrs=pg_query($query);
	$query=
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
		
		//$arrPost[$account.$i]		= array('type'=>$type,'value'=>$total,'account'=>$account,'fk_supplier'=>($lrow["fk_supplier"]==""?NULL:$lrow["fk_supplier"]),'reference'=>$lrow["reference_transaksi"]);		
		$i++;
	}	
*/	
	$type_owner=strtoupper($jenis_transaksi);
	$fk_owner=$fk_sbg;
	
	$arrPost = gl_balik($fk_owner,$type_owner,$no_lelang);
	if(count($arrPost)=='0'){
		$l_success=0;
	}
	//cek_balance_array_post($arrPost);
	if(!posting('BATAL '.$type_owner,$fk_owner,today_db,$arrPost,$fk_cabang,'00'))$l_success=0;
	
	$tbl="data_gadai.tbllelang";
	//SBG DIBATALIN
	$lwhere="no_kwitansi='".$no_lelang."'";
	if(!pg_query(insert_log($tbl,$lwhere,'UB')))$l_success=0;	
	if(!pg_query("update ".$tbl." set status_data='Batal' where ".$lwhere.""))$l_success=0; 
	if(!pg_query(insert_log($tbl,$lwhere,'UA')))$l_success=0;
	
	$lwhere="fk_sbg='".$fk_sbg."' and tgl_bayar is not null and no_kwitansi='".$no_lelang."'";
	if(!pg_query(insert_log("data_fa.tblangsuran",$lwhere,'UB'))) $l_success=0;
	if(!pg_query("update data_fa.tblangsuran SET tgl_bayar=NULL, no_kwitansi =NULL  where ".$lwhere."")) $l_success=0;
	//showquery("update data_fa.tblangsuran SET tgl_bayar=NULL, no_kwitansi =NULL  where ".$lwhere."");
	if(!pg_query(insert_log("data_fa.tblangsuran",$lwhere,'UA'))) $l_success=0;
	
		
/*	$titipan=round(get_rec("data_fa.tblpelunasan_lelang","titipan","fk_lelang='".$no_lelang."'"));
	if($titipan>0){	
		if(!pg_query(update_saldo_titipan($fk_sbg,($titipan))))$l_success=0;
	}
*/
	//echo $l_success;
	//$l_success=0;
	if ($l_success==1){
		$lrs_kosong=pg_query("select * from skeleton.tblmodule inner join skeleton.tblmodule_fields on skeleton.tblmodule.pk_id=fk_module where fk_menu='".$id_menu."' and is_edit is true");
		while($lrow_kosong=pg_fetch_array($lrs_kosong)){
			$_REQUEST[$lrow_kosong["kd_field"]]="";
			$_REQUEST[str_replace("[]","",$lrow_kosong["kd_field"]."1")]="";
			$_REQUEST[str_replace("[]","",$lrow_kosong["kd_field"]."2")]="";
		}
		
		$lrs_detail_kosong=pg_query("select * from skeleton.tblmodule_detail where fk_module='".$kd_module."'");
		//showquery("select * from skeleton.tblmodule_detail where fk_module='".$kd_module."'");
		while($lrow_detail_kosong=pg_fetch_array($lrs_detail_kosong)){
			$_REQUEST["strisi_".$lrow_detail_kosong["kd_module_detail"]]="";	
		}	
		$strisi1="";
		$strmsg=$nm_menu." Tersimpan.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		pg_query("COMMIT");
	}else{
		$strmsg.="Error :<br>".$nm_menu." Gagal Tersimpan.<br>";
		pg_query("ROLLBACK");
		
	}
}


?>