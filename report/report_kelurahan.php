<?php
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/numeric.inc.php';
require '../requires/timestamp.inc.php';
require '../requires/db_utility.inc.php';
require '../classes/select.class.php';
require '../requires/file.inc.php';


header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=KELURAHAN.xls");
header("Pragma: no-cache");
header("Expires: 0");


if($fk_cabang != ''){
	if ($lwhere!="") $lwhere.=" and ";
	$lwhere.=" fk_cabang = '".$fk_cabang."' ";
}

if ($lwhere!="") $lwhere=" where ".$lwhere;

$query = "
	select * from (
		select * from tblkelurahan
		left join tblkecamatan on fk_kecamatan =kd_kecamatan
		left join tblkota on fk_kota =kd_kota
		left join tblprovinsi on fk_provinsi=kd_provinsi
	) as tblmain 
".$lwhere." 
";
	//showquery($query);
?>

<table border="1">
            <tr>
                <td align="center">Kode Kelurahan</td>
                <td align="center">Nama Kelurahan</td>
                <td align="center">Kode Pos</td>
                <td align="center">Kode Kecamatan</td>
                <td align="center">Nama Kecamatan</td>
                <td align="center">Kode Kota</td>
                <td align="center">Nama Kota</td>
                <td align="center">Kode Provinsi</td>
                <td align="center">Nama Provinsi</td>
                
                
            </tr>
    	
<?
	$lrs = pg_query($query);
	$no=1;
		while($lrow = pg_fetch_array($lrs)){
?>
			<tr>
				<td valign="top"><?=$lrow["kd_kelurahan"]?></td>
                <td valign="top"><?=$lrow["nm_kelurahan"]?></td>
                <td valign="top"><?=$lrow["kd_pos"]?></td>
				<td valign="top"><?=$lrow["kd_kecamatan"]?></td>
                <td valign="top"><?=$lrow["nm_kecamatan"]?></td>
				<td valign="top"><?=$lrow["kd_kota"]?></td>
                <td valign="top"><?=$lrow["nm_kota"]?></td>
				<td valign="top"><?=$lrow["kd_provinsi"]?></td>
                <td valign="top"><?=$lrow["nm_provinsi"]?></td>
			</tr>
				
<?			
			$no++;
		}
	
?>
</table>

<?
