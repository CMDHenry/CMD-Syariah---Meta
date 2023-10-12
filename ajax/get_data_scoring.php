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
	
	$no_fatg=$_REQUEST["no_fatg"];	
	
	$score=scoring($no_fatg);

	echo $score;
	//echo $kd_laci.',';
}
