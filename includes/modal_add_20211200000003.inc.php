<?

function html_additional(){
	
	echo '<input type="hidden" name="tipe" value="Tebus">';
	echo '<input type="hidden" name="tgl_sistem" value="'.today.'">';
	

}
function cek_error_module(){
	global $j_action,$strmsg;
	
	$fk_sbg=$_REQUEST["fk_sbg"];
	$query="
	select tgl_lunas,fk_cabang,status from(
		select * from tblinventory 
		where fk_sbg ='".$fk_sbg."'
	)as tblsbg	
	";
	//showquery($query);
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
			
	$tgl_lunas=$lrow["tgl_lunas"];
	$fk_cabang=$lrow["fk_cabang"];
	
	$status=$lrow["status"];

	if($tgl_lunas){
		$strmsg.="Kontrak ini sudah lunas <br>";	
	}
	
	if($status!='Tarik'){
		$strmsg.="Kontrak ini belum ditarik <br>";	
	}
	
}

function query_additional($pk_id){
	global $no_kwitansi;	
	$no_kwitansi=$pk_id;
}

function save_additional(){
	global $l_success,$no_kwitansi;
	
	$fk_sbg=$_REQUEST["fk_sbg"];
	$total_pembayaran=str_replace(',','',$_REQUEST['total_pembayaran']);
	$fk_bank=$_REQUEST["fk_bank"];
	$fk_cabang=$_REQUEST["fk_cabang"];
	$fk_cabang_bayar=$_REQUEST["fk_cabang_bayar"];
	$tgl_bayar=convert_date_english($_REQUEST['tgl_bayar']);
	
	$lrow=pg_fetch_array(pg_query("
	select * from tblinventory 
	left join (select ang_ke ,fk_sbg as fk_sbg1 from viewang_ke)as tbl on fk_sbg1=fk_sbg 
	left join (
		select fk_fatg as no_fatg,no_sbg from data_gadai.tblproduk_cicilan
	)as tbl1 on no_sbg=fk_sbg
	left join (
		select no_fatg as no_fatg1,no_mesin,no_rangka, kategori,nm_tipe,fk_jenis_barang,kategori from viewkendaraan
	)as view on no_fatg=no_fatg1
	left join(
		select distinct on (fk_sbg)fk_sbg as fk_sbg_tarik,tgl_data as tgl_tarik from data_gadai.tblhistory_sbg
		where transaksi='Tarik'	and tgl_data<'".$tgl_bayar." 23:59:59' and tgl_batal is null
	)as tarik on fk_sbg=fk_sbg_tarik					
	
	where fk_sbg='".$fk_sbg."'"));
	$status_inv=$lrow["status"];
	$ang_ke=$lrow["ang_ke"];	
	$kategori=$lrow["kategori"];	
	$kategori=strtolower($kategori);
	
	$transaksi='PEMBAYARAN TEBUS';	
	
	$coa_bank = get_coa_bank($fk_bank,$fk_cabang_bayar);
	$arrPost = array();
	$arrPostCabangBayar = array();
	$arrPostCabangBayar["bank"]					= array('type'=>'d','value'=>$total_pembayaran,'account'=>$coa_bank);
	$arrPostCabangBayar["pend_admin_".$kategori]	= array('type'=>'c','value'=>$total_pembayaran);
	
	$query_sisa="
	select saldo_pinjaman as ar_cicilan,saldo_bunga from data_fa.tblangsuran 
	where fk_sbg='".$fk_sbg."' and tgl_bayar is not null
	order by angsuran_ke desc
	";
	$lrs_sisa=pg_query($query_sisa);
	$lrow_sisa=pg_fetch_array($lrs_sisa);	
	$ar_cicilan=$lrow_sisa["ar_cicilan"];	
	
	$fom_tarik=date("Y-m-01",strtotime($lrow["tgl_tarik"]));
	$query_akru="
	select sum(akrual1+akrual2) as saldo_akrual,
	fk_sbg as fk_sbg_akru from data_fa.tblangsuran 
	where tgl_jatuh_tempo >='".$fom_tarik."' and fk_sbg='".$fk_sbg."' group by fk_sbg
	";
	//showquery($query_akru);
	$lrs_akru=pg_query($query_akru);
	$lrow_akru=pg_fetch_array($lrs_akru);
	$sisa_yad=$lrow_akru["saldo_akrual"];
	
	$arrPost["piutang_pembiayaan"]		= array('type'=>'d','value'=>$ar_cicilan);
	$arrPost["jaminan_dikuasai_kembali"]= array('type'=>'c','value'=>$ar_cicilan-$sisa_yad);
	$arrPost["pend_bunga_yad"]		    = array('type'=>'c','value'=>$sisa_yad);
	
	//cek_balance_array_post($arrPost);	
	foreach($arrPost as $index=>$temp){
		$arrPost[$index]['reference'] =$no_kwitansi;//tambah keterangan disemua arrpost
	}
	
	if(!posting($transaksi,$fk_sbg,$tgl_bayar,$arrPostCabangBayar,$fk_cabang_bayar,'00'))$l_success=0;
	if(!posting($transaksi,$fk_sbg,$tgl_bayar,$arrPost,$fk_cabang,'00'))$l_success=0;

	$fom_tarik=date("Y-m-01",strtotime($lrow["tgl_tarik"]));
	$fom_tebus=date("Y-m-01",strtotime($tgl_bayar));
	$selisih_bulan=(strtotime($fom_tebus)-strtotime($fom_tarik))/(60*60*24*30);
	
	if($selisih_bulan>0){
		$eom_tebus=date("Y-m-01",strtotime($tgl_bayar));
				
		$query_akru="
		select sum(akrual1+akrual2) as saldo_akrual,
		fk_sbg as fk_sbg_akru from data_fa.tblangsuran 
		where tgl_jatuh_tempo >='".$fom_tarik."'  and tgl_jatuh_tempo <'".$fom_tebus."'
		and fk_sbg='".$fk_sbg."' group by fk_sbg
		";
		//showquery($query_akru);
		$lrs_akru=pg_query($query_akru);
		$lrow_akru=pg_fetch_array($lrs_akru);
		$akrual_akhir_bulan=$lrow_akru["saldo_akrual"];
		
		$arrPost=array();
		$arrPost["pend_bunga_yad"]				= array('type'=>'d','value'=>$akrual_akhir_bulan);
		$arrPost["pend_bunga_".$kategori]		= array('type'=>'c','value'=>$akrual_akhir_bulan);
		foreach($arrPost as $index=>$temp){
			$arrPost[$index]['reference'] =$no_kwitansi;//tambah keterangan disemua arrpost
		}
		cek_balance_array_post($arrPost);	
		if(!posting($transaksi,$fk_sbg,$tgl_bayar,$arrPost,$fk_cabang,'00'))$l_success=0;
		if(!posting($transaksi,$fk_sbg,$tgl_bayar,$arrPostCabangBayar,$fk_cabang_bayar,'00'))$l_success=0;
	}
	
	
	if(!pg_query(update_saldo_bank($fk_bank,$fk_cabang,$total_pembayaran,0,$transaksi,$fk_sbg,$no_kwitansi)))$l_success=0;
	//showquery(update_saldo_bank($fk_bank,$fk_cabang,$total_pembayaran,0,$transaksi,$fk_sbg,$no_kwitansi));
	
	$p_arr=array(
		'tgl_data'=>$tgl_bayar,
		'fk_sbg'=>$fk_sbg,
		'referensi'=>$no_kwitansi,
		'ang_ke'=>$ang_ke,
		'transaksi'=>'Tebus',
	);
	
	//showquery(insert_history($p_arr));	
	if(!pg_query(insert_history($p_arr))) $l_success=0;			
	
	
	if(!pg_query(storing($fk_sbg,'-','Terima','Tebus')))$l_success=0;
	//showquery(storing($fk_sbg,'-','Terima','Tebus'));
	
}

?>

