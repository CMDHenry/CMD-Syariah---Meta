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
$id_edit = $_REQUEST["id_edit"];

$tahun=($_REQUEST["tahun"]);
if(!$tahun)$tahun=date('Y',strtotime(today_db));
$bulan=($_REQUEST["bulan"]);
if(!$bulan)$bulan=date('m',strtotime(today_db));

if($view==1){
	//header("Content-type: application/vnd.ms-excel");
//	header("Content-Disposition: attachment; filename=report_".$report."_".date("d/m/Y_H:i:s").".".$pType);
//	header("Pragma: no-cache");
//	header("Expires: 0");
}else{
	header("Content-type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=print_".$report."_".date("d/m/Y_H:i:s").".xls");
	header("Pragma: no-cache");
	header("Expires: 0");
}
$query ="
	select * from data_fa.tblbatch_payment
	left join data_gadai.tblhistory_sbg on no_batch=referensi
	left join (select kd_partner from tblpartner)as tbpartner on kd_partner=fk_partner
	where referensi = '".$id_edit."'
";

//showquery($query);
$no = 0;
$lrs = pg_query($query);
$lrow = pg_fetch_array($lrs);

$tgl_bayar = date("d/m/Y", strtotime($lrow["tgl_batch"]));
$partner = $lrow["kd_partner"];
$rate_ppn = $lrow["rate_ppn"];
?>

DAFTAR PEMBY.PREMI ASURANSI</br>
KE <?=$partner?><br>
TGL. <?=$tgl_bayar?><br>
<br>

<table border="1">
  <tr>
  	<th scope="col" rowspan="2">NO</th>
    <th scope="col" rowspan="2">NAMA CUSTOMER</th>
    <th scope="col" rowspan="2">NO KONTRAK</th>
    <th scope="col" rowspan="2">TAHUN KE <?=$tahun_ke?></th>
    <th scope="col" colspan="3">KOMISI</th>
    <th scope="col" rowspan="2">PREMIUM (ASURANSI)</th>
    <th scope="col"rowspan="2">Premi Yg byr ke Asuransi</th>
  </tr>
	<tr>
    	<th scope="col">DPP</th>
        <th scope="col">PPN <?=($rate_ppn*100)?>%</th>
        <th scope="col">PPH 23</th>
    </tr>
  
<?

$query ="
	select tgl_bayar,transaksi,nm_customer,referensi as no_batch,fk_sbg from data_gadai.tblhistory_sbg
		left join
		(select fk_cif,fk_sbg as sbg from tblinventory)as tblinven on sbg=fk_sbg
		left join
		(select nm_customer,no_cif from tblcustomer)as tblkrywn on no_cif=fk_cif
	where referensi = '".$id_edit."' and tgl_batal is null 
";

//showquery($query);
$no = 0;
$lrs = pg_query($query);
			
while($lrow = pg_fetch_array($lrs)){
	$no++;
	$transaksi = $lrow["transaksi"];
	$tahun_ke= substr($transaksi,"20");
	$nominal=calc_asuransi($lrow["fk_sbg"],$tahun_ke);	
	$utang=$nominal;	
	
	$asuransi=calc_asuransi_nett($lrow["fk_sbg"],$nominal);
	$komisi=$asuransi['komisi'];
	$ppn=$asuransi['ppn'];
	$pph23=$asuransi['pph23'];
	$nominal=$asuransi['nominal'];

	echo "
		  <tr>
			<td align='left'>".$no."</th>
			<td align='left' >".$lrow["nm_customer"]."</th>
			<td align='left' >&nbsp;".$lrow["fk_sbg"]."</th>
			<td align='right' >".$tahun_ke."</th>
			<td align='right' >".number_format($komisi)."</th>
			<td align='right' >".number_format($ppn)."</th>
			<td align='right' >".number_format($pph23)."</th>
			<td align='right' >&nbsp;".number_format($utang)."</th>
			<td align='right' >&nbsp;".number_format($nominal)."</th>
		  </tr>";
		  
		$total['utang']+=$utang;
		$total['komisi']+=$komisi;
		$total['ppn']+=$ppn;
		$total['pph23']+=$pph23;
		$total['nominal']+=$nominal;
}
	echo '
		<tr>
			<td align="center">TOTAL</td>
			<td align="center"></td>
			<td align="center"></td>

			<td align="right"></td>
			<td align="right">'.number_format($total['komisi']).'</td>
			<td align="right">'.number_format($total['ppn']).'</td>
			<td align="right">'.number_format($total['pph23']).'</td>
			<td align="right">'.number_format($total['utang']).'</td>	
			<td align="right">'.number_format($total['nominal']).'</td>					
						
		</tr>
	';
?>
</table>
<br>
<table border="1">
  <tr>
    <td align="center" rowspan="2">Tanda Tangan</td>
    <td></td>
    <td>Diverifikasi Oleh,</td>
    <td>Diketahui Oleh,</td>
    <td>Disetujui Oleh,</td>
    <td></td>
  </tr>
  
<?
	echo "
		<tr>
			<td align='left'></td>
			<td align='center'></td>
			<td align='center'></td>
			<td align='center'></td>
			<td align='center'></td>
		</tr>
		<tr>
			<td align='left'>Nama</td>
			<td align='center'></td>
			<td align='center'></td>
			<td align='center'></td>
			<td align='center'></td>
			<td align='center'></td>
		</tr>
		<tr>
			<td align='left'>Jabatan</td>
			<td align='center'></td>
			<td align='center'></td>
			<td align='center'></td>
			<td align='center'></td>
			<td align='center'></td>
		</tr>
		<tr>
			<td align='left'>Tanggal</td>
			<td align='center'></td>
			<td align='center'></td>
			<td align='center'></td>
			<td align='center'></td>
			<td align='center'></td>
		</tr>
		";
?>
</table>
<p>&nbsp;</p>
