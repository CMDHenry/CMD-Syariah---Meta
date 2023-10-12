<?
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/convert.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/numeric.inc.php';
require '../requires/referer_check.inc.php';
require '../classes/recordset_ajax.class.php';

if (!isset($_SESSION['id']) ){
	//echo "err:1000 ";
} else{	
	//print_r($_REQUEST);
	$fk_barang=$_REQUEST["value"];
	$fk_wilayah=get_rec("tblcabang","fk_wilayah","kd_cabang='".$_SESSION['kd_cabang']."'");
	
	$rate=get_rec("tblbarang_detail","rate","fk_barang='".$fk_barang."' and fk_wilayah='".$fk_wilayah."'");
	$harga_awal=get_rec("tblbarang_detail","harga","fk_barang='".$fk_barang."' and fk_wilayah='".$fk_wilayah."'");	
	$harga = $harga_awal * $rate / 100 ;
	
	$query = "
	select * from tblbarang
	left join tbltipe on fk_tipe=kd_tipe
	left join tblmerek on fk_merek=kd_merek
	where kd_barang='".$fk_barang."'
	";	
	//showquery($query);
	$lrs = pg_query($query);
	$lrow=pg_fetch_array($lrs);		
	
	echo $lrow["nm_barang"].'|';
	echo $lrow["nm_merek"].'|';
	echo $lrow["nm_tipe"].'|';
	//echo $lrow["nm_model"].'|';
	echo round($lrow["tahun_produksi"]).'|';
	echo round($harga).'|';
	echo round($harga);
}
