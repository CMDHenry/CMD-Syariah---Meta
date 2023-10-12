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
	
	$fk_sbg=$_REQUEST["fk_sbg"];	
	$query="
	select * from tblinventory 
	left join (
		select * from data_gadai.tbltaksir_umum
		left join(
			select no_fatg as fk_fatg,nm_jenis_barang from viewkendaraan
		)as tblkendaraan on no_fatg=fk_fatg
	)as tbl on fk_sbg=no_sbg_ar
	left join tblcustomer on no_cif=tblinventory.fk_cif
	where fk_sbg='".$fk_sbg."'";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
		
	if($lrow['fk_sbg']){		
		echo $lrow["no_cif"].',';
		echo $lrow["nm_customer"].',';
		echo $lrow["no_id"].',';		
		echo $lrow["tgl_cair"].',';
		echo $lrow["fk_cabang"].',';
		echo $lrow["no_mesin"].',';
		echo $lrow["no_rangka"].',';
		echo $lrow["no_polisi"].',';
		echo $lrow["nm_jenis_barang"].',';		
		
	}
}
