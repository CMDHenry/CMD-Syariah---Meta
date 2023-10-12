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
	
	$l_res=pg_query("select * from tblcabang where cabang_active='true' and kd_cabang !='".cabang_ho."' order by kd_cabang asc");

	$no=0;	
	while($lrow=pg_fetch_array($l_res)){
		$strisi.=$lrow["kd_cabang"].chr(187).$lrow["nm_cabang"].chr(191);
		$no++;
	}
	echo fullescape($strisi).',';

}
