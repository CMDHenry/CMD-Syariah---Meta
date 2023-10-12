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
	
	pelunasan_gadai($fk_sbg);
	
	echo $fk_cif.'¿'; 
	echo $nm_customer.'¿';
	echo $tgl_cair.'¿';
	echo $lama_pinjaman.'¿';
	echo $lama_pelunasan.'¿';
	echo $lama_jasa_simpan.'¿';
	echo $rate_flat.'¿';
	echo $nilai_pinjaman.'¿';
	echo $nilai_penyimpanan.'¿';
	echo $biaya_denda.'¿';
	echo $biaya_penjualan.'¿';
	echo $titipan;
	
	
}
