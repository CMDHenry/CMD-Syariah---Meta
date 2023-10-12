<?php

include_once("report.php");

function filter_request(){
	global $showPeriode,$showCab,$is_outs_terima,$nm_periode,$periode_awal1,$periode_akhir1,$showDlr,$jenis_report,$periode_awal2,$periode_akhir2,$posisi_bpkb;
	$showPeriode='t';
	$showCab='t';
}
function create_filter(){
	global $periode_awal1,$periode_akhir1,$periode_awal2,$periode_akhir2,$jenis_report,$posisi_bpkb;
?>


<?
}

function excel_content(){
	global $periode_awal,$periode_akhir,$periode_awal1,$periode_akhir1,$periode_awal2,$periode_akhir2,$fk_cabang,$fk_partner_dealer,$jenis_report,$posisi_bpkb;
	//echo $periode_awal1;	
	
	if($periode_awal != '' && $periode_akhir != ''){
		if ($lwhere!="") $lwhere.=" and ";			
		$lwhere.=" tgl_kirim between '".$periode_awal." 00:00:00' and '".$periode_akhir." 23:59:59'";
	}
	
	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cabang_kirim = '".$fk_cabang."' ";
	}
	
	if($fk_partner_dealer != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_partner_dealer = '".$fk_partner_dealer."' ";
	}
		
	
	if ($lwhere!="") $lwhere=" where ".$lwhere;
	
	$query = "
	select * from(
		select * from(
			select * from data_fa.tblmutasi_bpkb 
			left join data_fa.tblmutasi_bpkb_detail on no_mutasi=fk_mutasi
			where tgl_batal is null
		)as tblmutasi
		left join (
			select fk_cif,fk_sbg as fk_sbg1,fk_cabang,tgl_cair,tgl_lunas from tblinventory
		) as tblinv	on fk_sbg=fk_sbg1
		left join (select nm_customer,no_cif,alamat_ktp from tblcustomer) as tblcust on no_cif=fk_cif 
		left join (
			select no_sbg_ar,no_mesin,no_rangka,fk_partner_dealer,no_polisi,no_bpkb,tgl_bpkb,tgl_terima_bpkb,tgl_serah_terima_bpkb,nm_bpkb,no_faktur,tgl_faktur,status_barang,posisi_bpkb from data_gadai.tbltaksir_umum
		)as tblkredit on no_sbg_ar=fk_sbg
		left join (select kd_partner,nm_partner, ovd_terima_bpkb from tblpartner)as tblpartner on fk_partner_dealer=kd_partner		
	)as tblmain	 
	".$lwhere."
	";

//	showquery($query);
	echo 	
	'<table border="1">
	     <tr>
		 	<td align="center">NO KONTRAK</td>
			<td align="center">TGL KONTRAK</td>
			<td align="center">NAMA DEBITUR</td>
			<td align="center">NO MESIN</td>			
			<td align="center">NO RANGKA</td>
			<td align="center">NO POLISI</td>
			<td align="center">NO BPKB</td>
			<td align="center">NAMA BPKB</td>
			
			<td align="center">CABANG PENGIRIM</td>			
			<td align="center">TGL KIRIM</td>	
			<td align="center">NO MUTASI</td>	
			
			<td align="center">PENERIMA</td>		
			<td align="center">CABANG PENERIMA</td>						
			<td align="center">TGL TERIMA</td>		
		  </tr>
	';
	$lrs = pg_query($query);
	$no=1;
	
	while($lrow=pg_fetch_array($lrs)){
		
		echo '
			<tr>
				<td valign="top">&nbsp;'.$lrow["fk_sbg"].'</td>
				<td valign="top">'.($lrow["tgl_cair"]==""?"":date("d/m/Y",strtotime($lrow["tgl_cair"]))).'</td>
				<td valign="top">'.$lrow["nm_customer"].'</td>
				<td valign="top">'.$lrow["no_rangka"].'</td>
				<td valign="top">'.$lrow["no_mesin"].'</td>
				<td valign="top">'.$lrow["no_polisi"].'</td>
				<td valign="top">'.$lrow["no_bpkb"].'</td>
				<td valign="top">'.$lrow["nm_bpkb"].'</td>
								
				<td valign="top">'.$lrow["fk_cabang_kirim"].'</td>				
				<td valign="top">'.($lrow["tgl_kirim"]==""?"":date("d/m/Y",strtotime($lrow["tgl_kirim"]))).'</td>
				<td valign="top">'.$lrow["no_mutasi"].'</td>
				
				<td valign="top">'.$lrow["penerima"].'</td>			
				<td valign="top">'.$lrow["fk_cabang_terima"].'</td>									
				<td valign="top">'.($lrow["tgl_terima"]==""?"":date("d/m/Y",strtotime($lrow["tgl_terima"]))).'</td>
				
			</tr>
		';	
		$no++;
	
	}
	echo '</table>';
}



