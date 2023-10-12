<?php

include_once("report.php");

function filter_request(){
	global $periode_awal,$periode_akhir,$showPeriode,$showCab;
	$showPeriode='t';
	$showCab='t';
}


function excel_content(){
	global $periode_awal,$periode_akhir,$periode_awal1,$periode_akhir1,$fk_cabang,$nm_cabang,$produk;
	if($periode_awal != '' && $periode_akhir != ''){
		$lwhere.=" tgl_approve between '".$periode_awal." 00:00:00' and '".$periode_akhir." 23:59:59'";
	}
	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cabang = '".$fk_cabang."' ";
	}
	
	if ($lwhere!="") $lwhere=" where ".$lwhere;
	
	$query = "
	select * from (
		select * from data_gadai.tbllelang
		left join (select fk_cif,fk_sbg,fk_cabang,tgl_cair from tblinventory) as tblinv on tblinv.fk_sbg=data_gadai.tbllelang.fk_sbg
		left join (select nm_customer,no_cif from tblcustomer) as tblcust on no_cif=fk_cif 
		where status_data = 'Approve'
	) as tblmain ".$lwhere." ";

	//echo $query;
	echo 	
	'<table border="1">
	     <tr>
		 	<td align="center">Kontrak</td>
			<td align="center">NO CIF</td>
			<td align="center">Kode Cabang</td>
			<td align="center">Nama Customer</td>
			<td align="center">Tanggal AR</td>
			<td align="center">Tanggal Approve Penjualan</td>
			
			<td align="center">Sisa Angsuran</td>
			<td align="center">Denda</td>
			
			<td align="center">Angka Pelunasan</td>
			<td align="center">Angka Penjualan</td>
			<td align="center">Kelebihan/Kekurangan Penjualan</td>
			<td align="center">Nama Penerima</td>
		  </tr>
	';
	$lrs = pg_query($query);
	$no=1;		

	while($lrow=pg_fetch_array($lrs)){
				
		echo '
			<tr>
				<td valign="top">&nbsp;'.$lrow["fk_sbg"].'</td>
				<td valign="top">'.$lrow["fk_cif"].'</td>
				<td valign="top">'.$lrow["fk_cabang"].'</td>
				<td valign="top">'.$lrow["nm_customer"].'</td>
				<td valign="top">'.($lrow["tgl_cair"]==""?"":date("d/m/Y",strtotime($lrow["tgl_cair"]))).'</td>
				<td valign="top">'.($lrow["tgl_approve"]==""?"":date("d/m/Y",strtotime($lrow["tgl_approve"]))).'</td>	
				<td valign="top" align="right">'.$lrow["sisa_angsuran"].'</td>
				<td valign="top" align="right">'.$lrow["nilai_bayar_denda"].'</td>
							
				<td valign="top" align="right">'.$lrow["angka_pelunasan"].'</td>
				<td valign="top" align="right">'.$lrow["angka_lelang"].'</td>
				<td valign="top" align="right">'.$lrow["selisih"].'</td>
				<td valign="top">'.$lrow["nm_penerima"].'</td>
			</tr>
		';	
		$no++;
	
	}
	echo '</table>';
}



