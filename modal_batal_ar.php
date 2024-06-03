<?php

include_once("modal_approve_custom.php");

function get_additional(){
	global $pstatus,$j_action,$strmsg;
	$pstatus='batal';
	if($_SESSION["jenis_user"]!='HO'){
		$strmsg="Error :<br> Menu ini hanya bisa diakses oleh HO.<br>";		
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
	}
	elseif(!pg_num_rows(pg_query("select * from tblinventory where fk_sbg='".$_REQUEST["no_sbg"]."' and status ='Terima'"))){
		//$strmsg.="Error :<br>Status SBG ".$_REQUEST["no_sbg"]." belum diterima / sudah pengajuan pelunasan.<br>";
		//$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
	}	
	
	if(pg_num_rows(pg_query("select * from data_fa.tblpembayaran_cicilan where fk_sbg='".$_REQUEST["no_sbg"]."' and tgl_batal is null"))){
		//$strmsg.="Error :<br>Transaksi sudah dilakukan pembayaran angsuran<br>";
		//$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
	}	
	
	if(pg_num_rows(pg_query("select * from data_gadai.tblhistory_sbg where fk_sbg='".$_REQUEST["no_sbg"]."' and transaksi like 'Pembayaran Unit%' and tgl_batal is null"))){
		//untuk pembayaran lain2nya gpp nyangkut. -25 ags 2023 diminta pak fahmi karena ada cabang salah input tenor kontrak
		$strmsg.="Error :<br>Transaksi sudah dibayar ke partner terkait<br>";
		//$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
	}	
	
}

function save_data(){
	global $j_action,$strmsg,$id_menu,$kd_module,$id_edit, $upload_path,$kd_tabs,$kd_tabs2,$kd_tabs3,$upload_path_website_pic,$l_success;
	$l_success=1;
	
	pg_query("BEGIN");	
	
	//BARANG DIBALIKIN TAPI HARUS DITERIMA DULU
	$fk_lajur=get_rec("tblinventory","fk_lajur","fk_sbg='".$_REQUEST["no_sbg"]."'");
	if(!pg_query(storing($_REQUEST["no_sbg"],$fk_lajur,'Batal','Batal Pencairan')))$l_success=0;	
	
	$fk_cabang = $_REQUEST["fk_cabang"];
	$fk_sbg = $_REQUEST["no_sbg"];
	
	
	$query=" 
	select * from data_gadai.tblproduk_cicilan 
	inner join(
		select no_fatg,fk_barang,fk_cif,fk_cabang,no_mesin,no_rangka,
		fk_partner_dealer,no_sbg_lama,status_fatg,status_barang from data_gadai.tbltaksir_umum 
		left join data_gadai.tbltaksir_umum_detail on fk_fatg=no_fatg
	)as tbl on no_fatg=fk_fatg
	left join tblbarang on kd_barang=fk_barang	
	left join tblcustomer on no_cif=fk_cif
	left join tblcabang on fk_cabang=kd_cabang
	left join tblpartner on fk_partner_dealer=kd_partner
	left join (
		select no_fatg as no_fatg1, kategori,fk_jenis_barang from viewkendaraan
	)as view on fk_fatg=no_fatg1	
	left join (
		select referensi as no_batch,fk_sbg as fk_sbg_batch,tgl_batch from data_gadai.tblhistory_sbg
		left join(
			select distinct on(fk_owner)fk_owner,tr_date as tgl_batch from data_accounting.tblgl_auto
			where type_owner='AR'
		)as tbl on fk_owner=referensi 
		where transaksi='AR' and tgl_batal is null
	)as tbl1 on no_sbg=fk_sbg_batch				
	";
	
	$query1=$query." where no_sbg='".$fk_sbg."'";
	$lrs=pg_query($query1);
	$lrow=pg_fetch_array($lrs);
	
	$tgl_cair=$lrow["tgl_cair"];
	$tgl_batch=date("m/d/Y",strtotime($lrow["tgl_batch"]));
	
	$fk_cabang=$lrow["fk_cabang"];	
	$addm_addb=$lrow["addm_addb"];	
	$lama_pinjaman=$lrow["lama_pinjaman"];	
	$fk_cif=$lrow["fk_cif"];	
	$fk_produk=$lrow["fk_produk"];		
	$pokok=$lrow["total_hutang"];			
	
					
	$total_piutang+=$lrow["total_hutang"];
	if($addm_addb=='M'){
		$total_piutang-=$lrow["angsuran_bulan"];
	}

	
	if($lrow["kategori"]=='R2'){
		$admin['R2']+=$lrow["biaya_admin"];
	}
	if($lrow["kategori"]=='R4'){			
		$admin['R4']+=($lrow["biaya_adm_sales"]+$lrow["biaya_admin"]);
	}
	
	if($lrow["status_barang"]=='Baru' ){	
		$dealer[$lrow["fk_partner_dealer"]]+=$lrow["nilai_ap_customer"];			
	}elseif($lrow["status_barang"]=='Bekas'){			
		$utang_usaha+=+$lrow["nilai_ap_customer"];
	}elseif($lrow["status_barang"]=='Datun'){	
		$utang_usaha+=$lrow["nilai_ap_customer"];			
	}
				
	
	if($lrow["fk_partner_asuransi"]){	
		//calc_asuransi($no_sbg,$tahun_ke)
		$asr_customer=($lrow["nilai_asuransi"]+$lrow["tjh_3"]+$lrow["pa_penumpang"]+$lrow["pa_supir"]+$lrow["biaya_polis"]);
		//echo $asr_customer.'<br>';
		$tenor=ceil($lama_pinjaman/12);
		$total_asr_partner=0;
		for($j=1;$j<=$tenor;$j++){
			$asr_partner=calc_asuransi($lrow["no_sbg"],$j);	
			if($j==1){
				$asuransi[$lrow["fk_partner_asuransi"]][$lrow["kategori"]]+=($asr_partner);
			}
			else {
				$asuransi['ARO'][$lrow["kategori"]]+=($asr_partner);	
			}
			$total_asr_partner+=($asr_partner);
		}			
		$selisih=$asr_customer-$total_asr_partner;
		$asuransi['ARO'][$lrow["kategori"]]+=($selisih);	
					
		$total_asr_jiwa+=$lrow["nilai_asr_jiwa"];		
		$jiwa[$lrow["fk_partner_asuransi"]]+=($lrow["nilai_asr_jiwa"]);
		
	}
				
	$total_bunga+=$lrow["biaya_penyimpanan"];
	
	
	
	$reference='PEMBIAYAAN KONTRAK '.$fk_sbg;
	//'reference'=>$reference
	$arrPost = array();
	$arrPost["piutang_pembiayaan"]	= array('type'=>'d','value'=>$total_piutang);
	$arrPost["pend_bunga_yad"]		= array('type'=>'c','value'=>$total_bunga);
	
	if($utang_usaha>0){
		$arrPost["utang_usaha"]		= array('type'=>'c','value'=>$utang_usaha);	
	}
	
	$i=1;
	if(count($dealer)>0){
		foreach($dealer as $kd_partner =>$total){
			$coa_dealer=$fk_cabang.'.'.get_rec("tblpartner","fk_coa_dealer","kd_partner='".$kd_partner."'");			
			$arrPost["utang_dealer".$i]		= array('type'=>'c','value'=>$total,'account'=>$coa_dealer);
			$i++;
		}
	}
	
	
	$i=1;
	if(count($asuransi)>0){
		foreach($asuransi as $kd_partner =>$temp){
			foreach($temp as $kategori =>$total){
				$kategori=strtolower($kategori);
				$coa_asr=$fk_cabang.'.'.get_rec("tblpartner","fk_coa_asr_".$kategori,"kd_partner='".$kd_partner."'");				
				if($kd_partner=='ARO'){
					$arrPost["utang_aro_".$kategori]	= array('type'=>'c','value'=>$total);
				}else{
					$arrPost["utang_asr".$i]			= array('type'=>'c','value'=>$total,'account'=>$coa_asr);
				}
				$i++;
			}
		}
	}
	
	
	if($total_asr_jiwa>0){
		foreach($jiwa as $kd_partner =>$asr_jiwa){
			$coa_asr_jiwa=$fk_cabang.'.'.get_rec("tblpartner","fk_coa_asr_jiwa","kd_partner='".$kd_partner."'");				
			$arrPost["utang_asr_jiwa"]	= array('type'=>'c','value'=>$asr_jiwa,'account'=>$coa_asr_jiwa);	
		}
	}				
			
	if($uang_muka>0){
		$arrPost["uang_muka"]		= array('type'=>'c','value'=>$uang_muka);	
	}
	
	if(count($admin)>0){
		foreach($admin as $kategori =>$total){
			$kategori=strtolower($kategori);
			$arrPost["pend_admin_".$kategori]		= array('type'=>'c','value'=>$total);
		}
	}	
	
	foreach($arrPost as $index=>$temp){
		$arrPost[$index]['reference'] =$reference;
		//untuk balik batal
		if($arrPost[$index]['type']=='d') $arrPost[$index]['type']='c';
		elseif($arrPost[$index]['type']=='c') $arrPost[$index]['type']='d';
	}

	// cek_balance_array_post($arrPost);
	//echo $tgl_batch;
	//if(count($arrPost)>0){
		if(!posting('BATAL AR',$fk_sbg,$tgl_batch,$arrPost,$fk_cabang,'00'))$l_success=0;
	//}
	
	if(pg_num_rows(pg_query("select * from data_gadai.tblproduk_gadai where no_sbg='".$_REQUEST["no_sbg"]."' "))){
		$tbl="data_gadai.tblproduk_gadai";
	}else{
		$tbl="data_gadai.tblproduk_cicilan";
	}

	//SBG DIBATALIN
	$lwhere="no_sbg='".$_REQUEST["no_sbg"]."'";
	if(!pg_query(insert_log($tbl,$lwhere,'UB')))$l_success=0;	
	if(!pg_query("update ".$tbl." set status_data='Batal',status_approval='Batal',tgl_cair=NULL,tgl_batal_pencairan='".today_db."',alasan_batal_pencairan='".$_REQUEST["alasan_batal"]."' where ".$lwhere.""))$l_success=0; 

	if(!pg_query(insert_log($tbl,$lwhere,'UA')))$l_success=0;
	
	if(!pg_query(insert_log("data_gadai.tbltaksir_umum","no_fatg = '".$lrow["no_fatg"]."'",'UB')))$l_success=0;		
	if(!pg_query("update data_gadai.tbltaksir_umum set no_sbg_ar=NULL where no_fatg='".$lrow["no_fatg"]."'")) $l_success=0;
	if(!pg_query(insert_log("data_gadai.tbltaksir_umum","no_fatg = '".$lrow["no_fatg"]."'",'UA')))$l_success=0;		
	//echo $l_success;
	//$l_success=0;
	
	//if(!pg_query("delete from data_accounting.tblgl_auto where type_owner='AKRUAL AKHIR BULAN' and fk_owner ='".$lrow["no_sbg"]."'")) $l_success=0;

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