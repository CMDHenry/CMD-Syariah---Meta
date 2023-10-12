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
	$cara_bayar=$_REQUEST["cara_bayar"];	
	$fk_sbg=$_REQUEST["fk_sbg"];		
	
	//$fk_bank='04';
	if($cara_bayar=='Giro/Cek'){
		$no_giro=$_REQUEST["no_giro"];	
		$query="select * from tblgiro where no_giro='".$no_giro."'";
		$lrs=pg_query($query);
		$lrow=pg_fetch_array($lrs);
		$fk_bank=$lrow["fk_partner_bank"];
	}
	
	$nominal_biaya_tagih=0;
	if($cara_bayar=='Collector'){
		if(!pg_num_rows(pg_query("select * from data_fa.tblpembayaran_cicilan where fk_sbg='".$fk_sbg."' and tgl_batal is null"))){
	 		$nominal_biaya_tagih=get_rec ("tblinventory left join tblproduk on fk_produk=kd_produk","nominal_biaya_tagih","fk_sbg='".$fk_sbg."'");
		}
	}
	
	echo $fk_bank.'¿';
	echo $nominal_biaya_tagih.'¿';
}
