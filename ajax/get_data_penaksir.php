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
	
	$fk_cabang=$_REQUEST["fk_cabang"];
	$total_taksir=$_REQUEST["total_taksir"];
	$query="select * from tblcabang left join tbllimit_approval on kd_limit=fk_limit where kd_cabang='".$fk_cabang."'";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	//echo $query;
	$index[1]="1";
	$index[2]="2";
	$index[3]="3";
	//if($lrow["jenis_cabang"]=="Pos"){
		$index[4]="4";
		$index[5]="4";
	//}else{
		//$index[4]="3";
	//}
	
	$max=1;
	$final_approval=$index[$max];
	for($i=1;$i<=count($index)-1;$i++){
		$field="limit_".strtolower($index[$i]);
		if($total_taksir>$lrow[$field]){
			$final_approval=$index[$i+1]; //utk lempar final approval ke tampilan dpn;
			$max=$i; //utk looping dari yg approval terbesar;	
			//echo $lrow[$field].'<br>';
		}
	}	
	//echo $max;
	//$final_approval='1';
	echo $final_approval;
}
