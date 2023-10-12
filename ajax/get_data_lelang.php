<?
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/convert.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/numeric.inc.php';
require '../requires/referer_check.inc.php';
require '../classes/recordset_ajax.class.php';
require '../requires/stok_utility.inc.php';


if (!isset($_SESSION['id']) ){
	//echo "err:1000 ";
} else{
	
	$fk_sbg=$_REQUEST["fk_sbg"];
	$jenis_transaksi=$_REQUEST["jenis_transaksi"];
	if(!$_REQUEST["tgl_bayar"]){
		$tgl_bayar=today_db;
	}else $tgl_bayar=convert_date_english($_REQUEST["tgl_bayar"]);
	
	
	if(pg_num_rows(pg_query("select * from tblinventory left join tblproduk on fk_produk=kd_produk where fk_sbg='".$fk_sbg."' and jenis_produk=0"))){
		pelunasan_gadai($fk_sbg);
		$total_bayar=round($nilai_pinjaman)+round($nilai_penyimpanan)+round($biaya_denda)+round($biaya_penjualan)-round($titipan);
	}elseif(pg_num_rows(pg_query("select * from tblinventory left join tblproduk on fk_produk=kd_produk where fk_sbg='".$fk_sbg."' and jenis_produk=1"))){
		pelunasan_cicilan($fk_sbg,$tgl_bayar);
	    $total_bayar=round($sisa_pokok)+round($bunga_berjalan)+round($denda)+round($pinalti)-round($titipan_angsuran);
		//$total_bayar=round($sisa_pelunasan);
		$query="select * from tblinventory left join tblcustomer on fk_cif=no_cif where fk_sbg='".$fk_sbg."' ";
		$lrs=pg_query($query);
		$lrow=pg_fetch_array($lrs);
		$nm_customer=$lrow["nm_customer"];
	}
	
	echo $total_bayar.'¿';
	echo $fk_cabang.'¿';
	echo $nm_customer.'¿';
	
}
