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
$pField = $_REQUEST["pField"];
$pPeriode = $_REQUEST["pPeriode"];
$pOrder = $_REQUEST["pOrder"];
$pGroupBy = $_REQUEST["pGroupBy"];
$tipe = str_replace('tipe,','',$pGroupBy);
$view = $_REQUEST["view"];

if($view==1){
	//header("Content-type: application/vnd.ms-excel");
//	header("Content-Disposition: attachment; filename=report_".$report."_".date("d/m/Y_H:i:s").".".$pType);
//	header("Pragma: no-cache");
//	header("Expires: 0");
}else{
	header("Content-type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=report_".$report."_".date("d/m/Y_H:i:s").".xls");
	header("Pragma: no-cache");
	header("Expires: 0");
}

?>


<table border="1">
  <tr>
  	<th scope="col">No</th>
    <th scope="col">Kode Karyawan</th>
    <th scope="col">Nama Karyawan</th>
    <th scope="col">Kode Jabatan</th>
    <th scope="col">Nama Jabatan</th>
    <th scope="col">No HP</th>
    <th scope="col">Email</th>
    <th scope="col">Alamat</th>
    <th scope="col">Kode Cabang</th>
    <th scope="col">Nama Cabang</th>
    <th scope="col">Jenis Kelamin</th>
    <th scope="col">Pendidikan</th>
    <th scope="col">Status Karyawan</th>
    <th scope="col">Active</th>
  </tr>
  
<?

$query ="
	select * from tblkaryawan   
	left join tblcabang on fk_cabang_karyawan=kd_cabang
	left join tbljabatan on fk_jabatan=kd_jabatan
	left join tblpendidikan on fk_pendidikan =kd_pendidikan
	
";

//showquery($query);
$no = 0;
$lrs = pg_query($query);
while($lrow = pg_fetch_array($lrs)){
	$no++;
	if($lrow["tgl_masuk"]==""){
		$tgl_masuk="";
	}else{
		$tgl_masuk=date('d/m/Y',strtotime($lrow["tgl_masuk"]));
	}
	if($lrow["karyawan_active"]=="t"){
		$karyawan_active='Aktif';
	}else{
		$karyawan_active='Tidak Aktif';
	}
	//<td align='left' >".$tgl_masuk."</th>

	echo "
		  <tr>
			<td align='left'>".$no."</th>
			<td align='left' >".$lrow["npk"]."</th>
			<td align='left' >".$lrow["nm_depan"]."</th>
			<td align='left' >".$lrow["fk_jabatan"]."</th>
			<td align='left' >".$lrow["nm_jabatan"]."</th>
			<td align='left' >&nbsp;".$lrow["no_hp"]."</th>
			<td align='left' >&nbsp;".$lrow["email"]."</th>
			<td align='left' >".$lrow["alamat"]."</th>
			<td align='left' >".$lrow["fk_cabang_karyawan"]."</th>
			<td align='left' >".$lrow["nm_cabang"]."</th>
			<td align='left' >".$lrow["jenis_kelain"]."</th>
			<td align='left' >".$lrow["nm_pendidikan"]."</th>
			<td align='left' >".$lrow["status_karyawan"]."</th>			
			<td align='left' >".$lrow["karyawan_active"]."</th>
		  </tr>";
}
?>
</table>
<p>&nbsp;</p>
