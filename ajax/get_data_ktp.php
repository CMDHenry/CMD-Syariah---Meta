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
	
	$no_id=$_REQUEST["no_id"];	
	$query="select * from tblcustomer where no_id='".$no_id."'";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	if(pg_num_rows(pg_query($query))){
		echo $lrow["no_cif"].',';
		echo $lrow["nm_customer"];
	}
}
