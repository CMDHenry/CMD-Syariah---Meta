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
	if(!$_REQUEST["tgl_bayar"]){
		$tgl_bayar=today_db;
	}else $tgl_bayar=convert_date_english($_REQUEST["tgl_bayar"]);
	
	pelunasan_cicilan($fk_sbg,$tgl_bayar);
	
	echo $nm_customer.'¿';
	echo $fk_cif.'¿';
	echo $tgl_cair.'¿';
	echo $tgl_jatuh_tempo_akhir.'¿';	
	echo $fk_cabang.'¿';
	echo $total_denda_lalu.'¿';
	echo $total_denda_kini.'¿';
	echo $denda_keterlambatan.'¿';
	echo $denda_ganti_rugi.'¿';
	
	echo $sisa_pokok.'¿';
	echo $bunga_berjalan.'¿';
	echo $pinalti.'¿';
	echo $sisa_angsuran.'¿';
	echo $nilai_bayar_denda.'¿';
	
	
	
}
