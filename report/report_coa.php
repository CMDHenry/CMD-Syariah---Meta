<?php
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/numeric.inc.php';
require '../requires/timestamp.inc.php';
require '../requires/db_utility.inc.php';
require '../classes/select.class.php';
require '../requires/file.inc.php';
require '../requires/accounting_utility.inc.php';
require '../classes/excel.class.php';
require '../requires/module.inc.php';
require '../requires/input.inc.php';
$report = $_REQUEST["report"];
$view = $_REQUEST["view"];

if($view==1){
	//header("Content-type: application/vnd.ms-excel");
//	header("Content-Disposition: attachment; filename=report_".$report."_".date("d/m/Y_H:i:s").".".$pType);
//	header("Pragma: no-cache");
//	header("Expires: 0");
}else{
	header("Content-type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=report_coa_".date("d/m/Y_H:i:s").".xls");
	header("Pragma: no-cache");
	header("Expires: 0");
}

?>


<table border="1">
  <tr>
    <th scope="col">No COA</th>
    <th scope="col">Nama</th>
  </tr>
  
<?

$query ="
	select * from tbltemplate_coa order by coa asc
";

//showquery($query);
$no = 0;
$lrs = pg_query($query);
while($lrow = pg_fetch_array($lrs)){
	$no++;

	echo "
		  <tr>
			<td align='left' >".$lrow["coa"]."</th>
			<td align='left' >".$lrow["description"]."</th>
		  </tr>";
}
?>
</table>
<p>&nbsp;</p>
