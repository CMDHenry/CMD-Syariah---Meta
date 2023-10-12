<?
// LELANG
function cek_error_module(){
	global $strmsg,$j_action,$fk_cabang;
	$fk_cabang=$_REQUEST["fk_cabang_input"];
	$cabang=$_REQUEST["fk_cabang"];
	
/*	if($fk_cabang!==$cabang){
		$strmsg.='Cabang kontrak tidak sama dengan cabang input<br>';
	}
	
*/	$query="select * from data_fa.tblangsuran where tgl_bayar is not null and fk_sbg='".$_REQUEST["fk_sbg"]."'order by angsuran_ke desc";
	$lrow=pg_fetch_array(pg_query($query));
	$tgl_bayar_terakhir=$lrow["tgl_bayar"];
	if(strtotime(convert_date_english($_REQUEST['tgl_lelang']))<strtotime($tgl_bayar_terakhir)){
		$strmsg.="Tanggal yang diinput harus setelah angsuran terakhir.<br>";		
	}
	
}

function html_additional(){
	
	echo '<input type="hidden" name="tgl_sistem" value="'.today.'">';
	

}


function save_additional(){
	global $l_success,$biaya_penjualan,$biaya_denda,$denda,$pinalti,$titipan_angsuran,$bunga_berjalan,$sisa_pokok,$lama_pinjaman,$lama_pelunasan,$nilai_penyimpanan,$titipan,$total_denda_kini,$total_denda_lalu,$nilai_bayar_denda,$denda_keterlambatan,$denda_ganti_rugi,$sisa_angsuran;
	
	$fk_sbg=$_REQUEST["fk_sbg"];
	$no_kwitansi=$_REQUEST["no_kwitansi"];	
	$nilai_dp=$_REQUEST["nilai_dp"];	
	$fk_bank=$_REQUEST["fk_bank"];	
	$jenis_transaksi=$_REQUEST["jenis_transaksi"];	
	$fk_cabang=$_REQUEST["fk_cabang_input"];
	$cabang=$_REQUEST["fk_cabang"];
	$tgl_lelang=date("Y-m-d",strtotime(convert_date_english($_REQUEST["tgl_lelang"])));	
	
	
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
	//showquery($query);
	$fk_cif=$lrow["fk_cif"];
	$kategori=$lrow["kategori"];
	$kategori=strtolower($kategori);
	if($nilai_dp>0){
		$ket='DP';
		$coa_bank = get_coa_bank($fk_bank,$fk_cabang);
		$arrPost = array();
		$arrPost["bank"]					= array('type'=>'d','value'=>$nilai_dp,'reference'=>$no_kwitansi,'account'=>$coa_bank);
		$arrPost["uang_muka_".$kategori]	= array('type'=>'c','value'=>$nilai_dp,'reference'=>$no_kwitansi);
		//cek_balance_array_post($arrPost);
		//if(!posting($ket,$fk_sbg,$tgl_lelang,$arrPost,$fk_cabang,'00'))$l_success=0;
		
		//if(!pg_query(update_saldo_bank($fk_bank,$fk_cabang,$nilai_dp,0,$ket,$fk_sbg,$no_kwitansi)))$l_success=0;	
		//showquery(update_saldo_bank($fk_bank,$fk_cabang,$nilai_dp,0,$ket,$fk_sbg,$no_kwitansi));
	
	}
	//echo $l_success;
	
}

?>

