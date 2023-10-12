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
	
	$fk_jenis_barang=$_REQUEST["fk_jenis_barang"];
	
	$strisi="";
	$l_res=pg_query("
	select * from (
		select * from tblkondisi_barang where fk_jenis_barang='".$fk_jenis_barang."' --and active='t'
	)as tblmain
	left join tblkondisi_barang_detail on fk_kondisi_barang=kd_kondisi_barang where persen != '100' OR persen is null
	order by aspek 
	");

	$no=0;	
	while($lrow=pg_fetch_array($l_res)){
		$strisi.=$lrow["kondisi_barang"].chr(187)."".chr(187).$lrow["aspek"].chr(191);
		$no++;
	}
		
	echo fullescape($strisi).',';

}
