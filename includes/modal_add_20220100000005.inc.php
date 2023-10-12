<?

function html_additional(){
	
	echo '<input type="hidden" name="tipe" value="Cicilan">';

}

function cek_error_module(){
	global $j_action,$strmsg;
	
	$fk_sbg=$_REQUEST["fk_sbg_dp"];
	$query="
	select tgl_lunas,jenis_produk,fk_cabang,fk_produk,tenor from(
		select * from tblinventory 
		where fk_sbg ='".$fk_sbg."'
	)as tblsbg	
	left join tblproduk on fk_produk=kd_produk
	left join(
		select lama_pinjaman as tenor,no_sbg from data_gadai.tblproduk_cicilan
	)as tblproduk_cicilan on fk_sbg = no_sbg
	";
	//showquery($query);
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	
	$tgl_lunas=$lrow["tgl_lunas"];
	$jenis_produk=$lrow["jenis_produk"];
	$fk_cabang=$lrow["fk_cabang"];
	$fk_produk=$lrow["fk_produk"];
	$tenor=$lrow["tenor"];

	if($tgl_lunas){
		$strmsg.="Kontrak ini sudah lunas <br>";	
	}
	//if(pg_num_rows(pg_query("select * from tblinventory where fk_sbg ='".$fk_sbg."J' "))){
		//$strmsg.="Kontrak ini sudah pernah tambah DP <br>";			
	//}
}

function query_additional($pk_id){
	global $no_kwitansi;	
	$no_kwitansi=$pk_id;
}

/*function save_additional1(){//pindah ke modal_terima_dp
	global $l_success,$no_kwitansi;
	
	$fk_sbg=$_REQUEST["fk_sbg_dp"];
	
	$lrow=pg_fetch_array(pg_query("select * from data_gadai.tblproduk_cicilan where no_sbg='".$fk_sbg."'"));
	$addm_addb=$lrow["addm_addb"];
	$fk_fatg=$lrow["fk_fatg"];
	
	$ang_ke=get_rec("data_fa.tblangsuran","angsuran_ke","fk_sbg='".$fk_sbg."' and tgl_bayar is null","angsuran_ke asc");
	$tgl_pengajuan=get_rec("data_fa.tblangsuran","tgl_jatuh_tempo","fk_sbg='".$fk_sbg."' and angsuran_ke='".$ang_ke."'","angsuran_ke asc");
		
	$no_fatg_new=$fk_fatg.'J';			
	$no_sbg_new=$fk_sbg.'J';	
	
	//update inventory
	if(!pg_query("insert into tblinventory_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','DB' from tblinventory where fk_sbg='".$fk_sbg."' ")) $l_success=0;	
	if(!pg_query("update tblinventory set fk_sbg='".$no_sbg_new."' where fk_sbg='".$fk_sbg."'")) $l_success=0;
		
	if(!pg_query(storing($no_sbg_new,NULL,'Lunas','Pelunasan')))$l_success=0;//insert inventory baru
		
	if(!pg_query("update data_fa.tblpembayaran_cicilan set fk_sbg='".$no_sbg_new."' where fk_sbg='".$fk_sbg."'")) $l_success=0;		
	//showquery("update data_fa.tblpembayaran_cicilan set fk_sbg='".$no_sbg_new."' where fk_sbg='".$fk_sbg."'");
		
	$lrow=pg_fetch_array(pg_query("select * from tblinventory where fk_sbg='".$no_sbg_new."'"));
	$tgl_cair=$lrow["tgl_cair"];
	$fk_produk=$lrow["fk_produk"];
	$fk_cif=$lrow["fk_cif"];
	$fk_cabang=$lrow["fk_cabang"];		
	$tgl_jt=date("m/d/Y",strtotime($lrow["tgl_jt"]));	
	//end			
	
	
	//update taksir
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
	if(!pg_query("update data_gadai.tbltaksir_umum set tgl_taksir='".$tgl_pengajuan."',no_sbg_ar='".$no_sbg_new."' where no_fatg='".$no_fatg_new."'")) $l_success=0;	

	if(!pg_query("update data_gadai.tbltaksir_umum set status_fatg='Tambah DP',no_sbg_lama='".$no_sbg_new."' where no_fatg='".$fk_fatg."'")) $l_success=0;	
	//showquery("update data_gadai.tbltaksir_umum set status_fatg='Tambah DP',no_sbg_lama='".$no_sbg_new."' where no_fatg='".$fk_fatg."'");
	
	
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
	
	//$tgl_pengajuan=date("m/",strtotime(today_db)).date("d",strtotime($tgl_cair)).date("/Y",strtotime(today_db));
	$tgl_pengajuan=date("m/d/Y",strtotime($tgl_pengajuan));
	
	if($addm_addb=='M'){
		$tgl_jatuh_tempo=(get_next_month(($tgl_pengajuan),($lama_pinjaman-1)));
	}elseif($addm_addb=='B'){
		$tgl_jatuh_tempo=(get_next_month(($tgl_pengajuan),($lama_pinjaman)));
	}	
	
	if(!pg_query("update data_gadai.tblproduk_cicilan set 
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
	biaya_admin='".$biaya_admin."'
	where no_sbg='".$fk_sbg."'")) $l_success=0;		
	
	//end 
	
							
	///update hitungan 
	if(!pg_query("insert into data_fa.tblangsuran_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','DB' from data_fa.tblangsuran where fk_sbg='".$fk_sbg."' ")) $l_success=0;	
	if(!pg_query("update data_fa.tblangsuran set tgl_bayar='".$tgl_pengajuan."', no_kwitansi ='".$no_kwitansi."' where fk_sbg='".$fk_sbg."' and tgl_bayar is null")) $l_success=0;
	
	if(!pg_query("update data_fa.tblangsuran set fk_sbg='".$no_sbg_new."' where fk_sbg='".$fk_sbg."'")) $l_success=0;
	//showquery("update data_fa.tblangsuran set fk_sbg='".$no_sbg_new."' where fk_sbg='".$fk_sbg."'");		
	
	angsuran($fk_sbg,$tgl_pengajuan);//insert angsuran baru
	if(!pg_query(storing($fk_sbg,'-','Terima','AR',$tgl_pengajuan,'t',$fk_cabang,$fk_cif,$fk_produk,$tgl_jatuh_tempo)))$l_success=0;
	//showquery(storing($fk_sbg,'-','Terima','AR',NULL,'t',$fk_cabang,$fk_cif,$fk_produk,$tgl_jatuh_tempo,$tgl_pengajuan));		
	
	if($tgl_jatuh_tempo!=$tgl_jt){
		echo("Tgl Jatuh Tempo baru ".$tgl_jatuh_tempo." tidak sama dengan yang lama ".$tgl_jt);
		pg_query("rollback");
		exit();
	}

	if(!pg_query(insert_history_sbg($fk_sbg,$total_hutang,'0','AR Tambah DP',$no_kwitansi,$_REQUEST["nilai_bayar_denda"]))) $l_success=0;		
	
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
		
	if(!pg_query("update data_fa.tblpenambahan_dp set fk_sbg='".$no_sbg_new."' where no_kwitansi='".$no_kwitansi."'")) $l_success=0;
		
	//showquery("update data_fa.tblpenambahan_dp set fk_sbg='".$no_sbg_new."' where no_kwitansi='".$no_kwitansi."'");
	
}*/

?>

