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
	$fk_fatg=$_REQUEST["fk_fatg"];
	if($fk_fatg!=""){
		$query="
			select fk_cif from viewtaksir where no_fatg='".$fk_fatg."'
		";
		$lrs=pg_query($query);
		$lrow=pg_fetch_array($lrs);
		$fk_cif=$lrow["fk_cif"];	
		//echo "select * from tblinventory where fk_cif='".$fk_cif."'";		
		if(!pg_num_rows(pg_query("select * from tblinventory where fk_cif='".$fk_cif."' and status!='Batal'"))){
			$status_aplikasi='New Order';		
		}else {
			if(pg_num_rows(pg_query("select * from tblinventory where fk_cif='".$fk_cif."' and status_sbg='Liv'"))){
				$status_aplikasi='Repeat Order';	
			}else{
				$status_aplikasi='Repeat Order';	
			}
		}		
	}
			
	echo $status_aplikasi.',';
	
}

