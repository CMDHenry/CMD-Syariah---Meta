<?php

include_once("modal_approve_custom.php");


function save_data(){
	global $j_action,$strmsg,$id_menu,$kd_module,$id_edit,$l_success;
	$l_success=1;
	pg_query("BEGIN");
	
	$fk_sbg_new=update_new();	
	$no_kwitansi=$_REQUEST["no_kwitansi"];
	$fk_sbg=$_REQUEST["fk_sbg_dp"];
	$fk_cabang=$_REQUEST["fk_cabang"];
	$fk_fatg_old=$_REQUEST["fk_fatg"];
	$jenis=$_REQUEST["jenis"];	
	
	$lrow=pg_fetch_array(pg_query("select * from data_gadai.tbltaksir_umum where no_fatg='".$fk_fatg_old."'"));
	//showquery("select * from data_gadai.tbltaksir_umum where no_fatg='".$fk_fatg."'");						
	$lrow_old=pg_fetch_array(pg_query("select * from data_gadai.tbltaksir_umum where no_sbg_ar='".$fk_sbg."'"));
	
	if($jenis=='Ganti CIF'){
		//echo $lrow["no_mesin"]."!=".$lrow_old["no_mesin"];
		if($lrow["no_mesin"]!=$lrow_old["no_mesin"]){
			$strmsg.="No Mesin Permohonan Pengganti harus sama dengan No Mesin di kontrak ini .<br>";
		}
		if($_REQUEST["nilai_bayar_denda"]>0){
			$strmsg.="Ganti CIF harus lunas denda dulu .<br>";
		}
		
	}
	
	$fk_bank=$_REQUEST['fk_bank'];
	$total_pembayaran=str_replace(',','',$_REQUEST['total_pembayaran']);
	$tgl_bayar=convert_date_english($_REQUEST['tgl_bayar']);
	
	$query="
	select * from (
		select * from data_gadai.tblproduk_cicilan where no_sbg='".$fk_sbg."'	
	) as viewsaldo_ar
	left join tblinventory on no_sbg=fk_sbg 
	left join (
		select no_fatg as no_fatg1,kategori,fk_jenis_barang from viewkendaraan
	)as view on fk_fatg=no_fatg1		
	";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);	
	$kategori=$lrow["kategori"];
	$kategori=strtolower($kategori);
	
	if($total_pembayaran>0){
		$coa_bank = get_coa_bank($fk_bank,$fk_cabang);
		$arrPost = array();
		$arrPost["bank"]				= array('type'=>'d','value'=>$total_pembayaran,'reference'=>$no_kwitansi,'account'=>$coa_bank);
		$arrPost["piutang_pembiayaan"]	= array('type'=>'c','value'=>$total_pembayaran,'reference'=>$no_kwitansi);
		//cek_balance_array_post($arrPost);
		if(!posting('Tambah DP',$fk_sbg,$tgl_bayar,$arrPost,$fk_cabang,'00'))$l_success=0;
			
		if(!pg_query(update_saldo_bank($fk_bank,$fk_cabang,$total_pembayaran,0,"Tambah DP",$fk_sbg,$no_kwitansi)))$l_success=0;
		//showquery(update_saldo_bank($fk_bank,$fk_cabang,$total_pembayaran,0,"Tambah DP",$fk_sbg,$no_kwitansi));	
	}
	
	if($jenis=='Ganti CIF'){//buat selisih sisa akrual yang lama sama baru
		$fom=date("m/01/Y",strtotime($tgl_bayar));
		$akru_new=str_replace(',','',$_REQUEST['biaya_penyimpanan']);
		//echo $akru_new;
		$query_akru="select sum(akrual1+akrual2)as sisa_akru from data_fa.tblangsuran where fk_sbg='".$fk_sbg."' and tgl_jatuh_tempo>='".$fom."'";
		$lrow_akru=pg_fetch_array(pg_query($query_akru));
		$akru_old=$lrow_akru['sisa_akru'];
		//echo $akru_old;
		$selisih_akru=$akru_old-$akru_new;
		
		$arrPost = array();
		$arrPost["pend_bunga_yad"]			= array('type'=>'d','value'=>$selisih_akru,'reference'=>$no_kwitansi);
		$arrPost["pend_bunga_".$kategori]	= array('type'=>'c','value'=>$selisih_akru,'reference'=>$no_kwitansi);
		//cek_balance_array_post($arrPost);
		if(!posting('Tambah DP',$fk_sbg,$tgl_bayar,$arrPost,$fk_cabang,'00'))$l_success=0;
	}
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

function update_new(){
	$arr["fk_sbg"]=$_REQUEST["fk_sbg_dp"];
	$arr["jenis"]=$_REQUEST["jenis"];
	$arr["no_kwitansi"]=$_REQUEST["no_kwitansi"];
	
	$lrow=pg_fetch_array(pg_query("select * from data_gadai.tblproduk_cicilan left join data_gadai.tbltaksir_umum on fk_fatg=no_fatg where no_sbg='".$arr["fk_sbg"]."'"));
	$arr["addm_addb"]=$lrow["addm_addb"];
	$arr["fk_fatg"]=$lrow["fk_fatg"];
	$arr["fk_produk"]=$lrow["fk_produk"];
	$arr["fk_cif"]=$lrow["fk_cif"];
	$arr["fk_cabang"]=$lrow["fk_cabang"];		
	$arr["tgl_jt"]=date("m/d/Y",strtotime($lrow["tgl_jt"]));
	
	//$tgl_pengajuan=get_rec("data_fa.tblangsuran","tgl_jatuh_tempo","fk_sbg='".$fk_sbg."' and angsuran_ke='".$ang_ke."'","angsuran_ke asc");
	$arr["tgl_pengajuan"]=convert_date_english($_REQUEST['tgl_bayar']);;
	
	if(!pg_query(insert_history_sbg($fk_sbg,0,'0',$jenis,$no_kwitansi))) $l_success=0;	
	
	//$arr["jenis"]='Tambah DP';
	if($arr["jenis"]=='Ganti CIF'){
		$arr["no_fatg_new"]=$_REQUEST["fk_fatg"];		
		$lrow=pg_fetch_array(pg_query("select nextserial_kontrak('SBG', '".$arr["fk_cabang"]."','".$arr["fk_produk"]."')"));
		$arr["no_sbg_new"]=$lrow["nextserial_kontrak"];
		//echo $arr["no_sbg_new"];
		$arr["fk_cif_new"]=get_rec("data_gadai.tbltaksir_umum","fk_cif","no_fatg='".$arr["no_fatg_new"]."'");
								
		update_fatg($arr);	
		update_inventory($arr);		
		new_sbg($arr);	
		update_angs($arr);								
				
	}else{
		$arr["no_fatg_new"]=$arr["fk_fatg"].'J';			
		$arr["no_sbg_new"]=$arr["fk_sbg"].'J';
		
		update_fatg($arr);		
		update_inventory($arr);		
		new_sbg($arr);		
		update_angs($arr);
		
/*		if($tgl_jatuh_tempo!=$tgl_jt){
			echo("Tgl Jatuh Tempo baru ".$tgl_jatuh_tempo." tidak sama dengan yang lama ".$tgl_jt);
			pg_query("rollback");
			exit();
		}
*/	
		$ang_ke=get_rec("data_fa.tblangsuran","angsuran_ke","fk_sbg='".$fk_sbg."' and tgl_bayar is null","angsuran_ke asc");

		$referensi=$no_kwitansi;
		if($_REQUEST["denda_keterlambatan"]>0){
			$transaksi='Denda Keterlambatan';
			if(!pg_query(insert_history_sbg($fk_sbg,$_REQUEST["denda_keterlambatan"],$ang_ke,$transaksi,$referensi))) $l_success=0;
		}
		if($_REQUEST["denda_ganti_rugi"]>0){
			$transaksi='Denda Ganti Rugi';
			if(!pg_query(insert_history_sbg($fk_sbg,$_REQUEST["denda_ganti_rugi"],$ang_ke,$transaksi,$referensi))) $l_success=0;
		}
		if($_REQUEST["nilai_bayar_denda"]>0){
			$transaksi='Bayar Denda';
			if(!pg_query(insert_history_sbg($fk_sbg,$_REQUEST["nilai_bayar_denda"],$ang_ke,$transaksi,$referensi))) $l_success=0;
		}			
	}
		
	if(!pg_query("update data_fa.tblpenambahan_dp set fk_sbg='".$arr["no_sbg_new"]."' where no_kwitansi='".$arr["no_kwitansi"]."'")) $l_success=0;
	return $arr["no_sbg_new"];
	
}

function update_fatg($arr){
	$jenis=$arr["jenis"];
	$fk_fatg=$arr["fk_fatg"];	
	$fk_sbg=$arr["fk_sbg"];
	$no_fatg_new=$arr["no_fatg_new"];
	$no_sbg_new=$arr["no_sbg_new"];
	$tgl_pengajuan=$arr["tgl_pengajuan"];
	
	if($jenis=='Tambah DP'){
		$query_table=pg_query("
		   SELECT *
		   FROM information_schema.columns
		   WHERE table_schema = 'data_gadai' AND table_name  = 'tbltaksir_umum' and column_name !='no_fatg'
		   order by ordinal_position
			 ;
		");	
		$column_names='';
		while($lrow_table=pg_fetch_array($query_table)){
			$column_names.=",".$lrow_table["column_name"];
		}	
		
		if(!pg_query("insert into data_gadai.tbltaksir_umum select '".$no_fatg_new."' ".$column_names." from data_gadai.tbltaksir_umum where no_fatg='".$fk_fatg."'")) $l_success=0;	//insert select taksir		
		//showquery("insert into data_gadai.tbltaksir_umum select '".$no_fatg_new."' ".$column_names." from data_gadai.tbltaksir_umum where no_fatg='".$fk_fatg."'");
		$no_sbg_ar=$fk_sbg;
		$no_sbg_lama=$no_sbg_new;
		$no_fatg=$fk_fatg;
	}else{
		$no_sbg_ar=$no_sbg_new;
		$no_sbg_lama=$fk_sbg;
		$no_fatg=$no_fatg_new;		
	}
	
	$query="update data_gadai.tbltaksir_umum set tgl_taksir='".$tgl_pengajuan."',no_sbg_ar='".$no_sbg_ar."',status_fatg='".$jenis."',no_sbg_lama='".$no_sbg_lama."' where no_fatg='".$no_fatg."'";
	if(!pg_query($query)) $l_success=0;	
	//showquery($query);	
}


function update_inventory($arr){
	$jenis=$arr["jenis"];
	$fk_fatg=$arr["fk_fatg"];	
	$fk_sbg=$arr["fk_sbg"];
	$no_fatg_new=$arr["no_fatg_new"];
	$no_sbg_new=$arr["no_sbg_new"];
	$tgl_pengajuan=$arr["tgl_pengajuan"];
	$fk_produk=$arr["fk_produk"];
	$tgl_jt=$arr["tgl_jt"];
	$fk_cif=$arr["fk_cif"];
	$fk_cif_new=$arr["fk_cif_new"];
	$fk_cabang=$arr["fk_cabang"];
	
	if($jenis=='Tambah DP'){
		if(!pg_query("insert into tblinventory_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','DB' from tblinventory where fk_sbg='".$fk_sbg."' ")) $l_success=0;	
		if(!pg_query("update tblinventory set fk_sbg='".$no_sbg_new."' where fk_sbg='".$fk_sbg."'")) $l_success=0;
		$no_sbg_lunas=$no_sbg_new;
		$no_sbg_cair=$fk_sbg;
		$fk_cif_insert=$fk_cif;
	}else{
		$no_sbg_lunas=$fk_sbg;
		$no_sbg_cair=$no_sbg_new;
		$fk_cif_insert=$fk_cif_new;
	}
		
	if(!pg_query(storing($no_sbg_lunas,NULL,'Lunas',$jenis,$tgl_pengajuan)))$l_success=0;	
	//showquery(storing($no_sbg_lunas,NULL,'Lunas',$jenis,$tgl_pengajuan));	
	if(!pg_query(storing($no_sbg_cair,'-','Terima','AR',$tgl_pengajuan,'t',$fk_cabang,$fk_cif_insert,$fk_produk,$tgl_jt)))$l_success=0;	
	//showquery(storing($no_sbg_cair,'-','Terima','AR',$tgl_pengajuan,'t',$fk_cabang,$fk_cif_insert,$fk_produk,$tgl_jt));	
}


function new_sbg($arr){
	$jenis=$arr["jenis"];
	$fk_fatg=$arr["fk_fatg"];	
	$fk_sbg=$arr["fk_sbg"];
	$no_fatg_new=$arr["no_fatg_new"];
	$no_sbg_new=$arr["no_sbg_new"];
	$tgl_pengajuan=$arr["tgl_pengajuan"];
	$fk_produk=$arr["fk_produk"];	
	$addm_addb=$arr["addm_addb"];	
	
	//update cicilan
	$query_table=pg_query("
	   SELECT *
	   FROM information_schema.columns
	   WHERE table_schema = 'data_gadai' AND table_name  = 'tblproduk_cicilan' and column_name !='no_sbg'
	   order by ordinal_position
	");	
	$column_names='';
	while($lrow_table=pg_fetch_array($query_table)){
		$column_names.=",".$lrow_table["column_name"];
	}	
	
	if(!pg_query("insert into data_gadai.tblproduk_cicilan select '".$no_sbg_new."' ".$column_names." from data_gadai.tblproduk_cicilan where no_sbg='".$fk_sbg."'")) $l_success=0;
	//showquery("insert into ".$tbl." select '".$no_sbg_new."' ".$column_names." from ".$tbl." where no_sbg='".$fk_sbg."'");
	if(!pg_query("update data_gadai.tblproduk_cicilan set fk_fatg='".$no_fatg_new."' where no_sbg='".$no_sbg_new."'")) $l_success=0;	
	
	$sisa_pokok=str_replace(',','',$_REQUEST['sisa_pokok']);
	$nilai_dp=str_replace(',','',$_REQUEST['nilai_dp']);
	$lama_pinjaman=str_replace(',','',$_REQUEST['lama_pinjaman']);
	$pokok_hutang=str_replace(',','',$_REQUEST['pokok_hutang']);
	$biaya_penyimpanan=str_replace(',','',$_REQUEST['biaya_penyimpanan']);
	$total_hutang=str_replace(',','',$_REQUEST['total_hutang']);
	$angsuran_bulan=str_replace(',','',$_REQUEST['angsuran_bulan']);
	$biaya_admin=str_replace(',','',$_REQUEST['biaya_admin']);	

	if($addm_addb=='M'){
		$tgl_jatuh_tempo=(get_next_month(($tgl_pengajuan),($lama_pinjaman-1)));
	}elseif($addm_addb=='B'){
		$tgl_jatuh_tempo=(get_next_month(($tgl_pengajuan),($lama_pinjaman)));
	}	
	$rate_flat=$biaya_penyimpanan/($lama_pinjaman*(30/360))/$pokok_hutang*100;	
	//echo $rate_flat;
	
	$query="update data_gadai.tblproduk_cicilan set 
	tgl_pengajuan='".$tgl_pengajuan."',tgl_cair='".$tgl_pengajuan."',tgl_jatuh_tempo='".$tgl_jatuh_tempo."' ,
	total_nilai_pinjaman='".$sisa_pokok."',
	nilai_dp='".$nilai_dp."',
	lama_pinjaman='".$lama_pinjaman."',
	pokok_hutang='".$pokok_hutang."',
	biaya_penyimpanan='".$biaya_penyimpanan."',
	total_hutang='".$total_hutang."',
	angsuran_bulan='".$angsuran_bulan."',
	biaya_admin_awal='".$biaya_admin."',
	diskon_admin='0',
	biaya_admin='".$biaya_admin."',
	rate_flat='".$rate_flat."',
	rate_flat_input=round(".$rate_flat.",3)
	where no_sbg='".$no_sbg_new."'";
	if(!pg_query($query)) $l_success=0;		
	//showquery($query);
	//end 		
}

function update_angs($arr){
	$jenis=$arr["jenis"];
	$fk_fatg=$arr["fk_fatg"];	
	$fk_sbg=$arr["fk_sbg"];
	$no_fatg_new=$arr["no_fatg_new"];
	$no_sbg_new=$arr["no_sbg_new"];
	$tgl_pengajuan=$arr["tgl_pengajuan"];
	$no_kwitansi=$arr["no_kwitansi"];	

	if(!pg_query("insert into data_fa.tblangsuran_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from data_fa.tblangsuran where fk_sbg='".$fk_sbg."' ")) $l_success=0;			

	if($jenis=='Tambah DP'){
		if(!pg_query("update data_fa.tblpembayaran_cicilan set fk_sbg='".$no_sbg_new."' where fk_sbg='".$fk_sbg."'")) $l_success		=0;		
		$no_sbg_insert=$fk_sbg;
	}else{
		if(!pg_query("update data_fa.tblangsuran set tgl_bayar='".$tgl_pengajuan."', no_kwitansi ='".$no_kwitansi."' where fk_sbg='".$fk_sbg."' and tgl_bayar is null")) $l_success=0;
		//showquery("update data_fa.tblangsuran set tgl_bayar='".$tgl_pengajuan."', no_kwitansi ='".$no_kwitansi."' where fk_sbg='".$fk_sbg."' and tgl_bayar is null");
		if(!pg_query("insert into data_fa.tblangsuran_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from data_fa.tblangsuran where fk_sbg='".$fk_sbg."' ")) $l_success=0;	
		$no_sbg_insert=$no_sbg_new;
	}

	angsuran($no_sbg_insert,$tgl_pengajuan);	
}
?>