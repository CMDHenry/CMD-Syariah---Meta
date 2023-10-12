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
	$query="select * from tblinventory where fk_sbg='".$fk_sbg."'";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	$kd_lajur=get_lokasi_storing($lrow["fk_cabang"]);
	
	
	$query="select * from tbllajur left join tbllaci on fk_laci=kd_laci where kd_lajur='".$kd_lajur."' ";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	$kd_laci=$lrow["kd_laci"];
	$kd_brankas=$lrow["fk_brankas"];
	
	echo $kd_lajur.',';
	echo $kd_laci.',';
	echo $kd_brankas;
}
