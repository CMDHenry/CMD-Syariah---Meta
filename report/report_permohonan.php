<?php

include_once("report.php");

function filter_request(){
	global $jenis_produk,$showCab,$showPeriode,$no_cif;
	$showCab='t';
	$showPeriode='t';
}




function create_filter(){
	global $status_permohonan;
?>

                
 
<?	
}

function excel_content(){
	global $tgl,$fk_cabang,$nm_cabang,$periode_awal,$periode_akhir;
	if($periode_awal != '' && $periode_akhir != ''){
		$lwhere.=" tgl_taksir >='".$periode_awal."' and tgl_taksir <='".$periode_akhir."'";
	}	
	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cabang = '".$fk_cabang."' ";
	}
	
	
	if ($lwhere!="") $lwhere=" where ".$lwhere;
	
	
	$query = "
		select case when status_approval is null then 'Waiting' else status_approval end as status_permohonan,* from(
			select * from data_gadai.tbltaksir_umum
			--where no_fatg='0104.2000229'
		)as tbltaksir_umum
		left join tblcustomer on no_cif=fk_cif
		left join tblkelurahan on kd_kelurahan=fk_kelurahan_tinggal
		left join tblkecamatan on kd_kecamatan=fk_kecamatan
		left join tblkota on kd_kota=fk_kota	
		
		left join viewkendaraan on tbltaksir_umum.no_fatg=viewkendaraan.no_fatg
		left join tblpartner on tbltaksir_umum.fk_partner_dealer=kd_partner
		
		left join data_gadai.tbltaksir_umum_detail_slik on fk_fatg=tbltaksir_umum.no_fatg
		left join(
			select distinct on(fk_fatg)fk_fatg as fk_fatg1,status_approval from data_gadai.tblproduk_cicilan
			order by fk_fatg,no_sbg desc
		)as tblcicilan on fk_fatg1=tbltaksir_umum.no_fatg
		".$lwhere."
	";
	//showquery($query);
	echo 	
	'<table border="1">
		<tr>
			<td align="center" rowspan="2">Kode Cabang</td>
			<td align="center" rowspan="2">No Permohonan</td>
			<td align="center" rowspan="2">Tgl Permohonan</td>
			
			<td align="center" rowspan="2">No CIF</td>
			<td align="center" rowspan="2">Nama Customer</td>
			<td align="center" rowspan="2">No ID/KTP</td>
			<td align="center" rowspan="2">Alamat Tinggal</td>
			<td align="center" rowspan="2">Kelurahan</td>
			<td align="center" rowspan="2">Kecamatan</td>
			<td align="center" rowspan="2">Kota</td>			
			<td align="center" rowspan="2">No HP</td>
			
			<td align="center" rowspan="2">Jenis</td>
			<td align="center" rowspan="2">Merek</td>									
			<td align="center" rowspan="2">Tipe</td>
			<td align="center" rowspan="2">Nama Dealer</td>
			<td align="center" rowspan="2">NO Rangka</td>
			<td align="center" rowspan="2">NO Mesin</td>
			
			<td align="center" rowspan="2">Kondisi</td>
			<td align="center" rowspan="2">Kesimpulan</td>
			<td align="center" rowspan="2">Keterangan/Catatan</td>
			<td align="center" rowspan="2">Character</td>
			<td align="center" rowspan="2">Capital</td>
			<td align="center" rowspan="2">Sumber Dana</td>
			<td align="center" rowspan="2">Alasan</td>
			<td align="center" rowspan="2">Capacity</td>
			<td align="center" rowspan="2">Credit Score CA</td>
			<td align="center" rowspan="2">Credit Score Surveyor</td>
			<td align="center" colspan="9">SLIK</td>
			<td align="center" rowspan="2">Status Permohonan</td>
		</tr>
		  
		<tr>
		   <td align="center">No</td>
		   <td align="center">LJK/Bank</td>
		   <td align="center">Tgl Awal</td>
		   <td align="center">Tgl Akhir</td>
		   <td align="center">Plafon Awal</td>
		   <td align="center">Angs</td>
		   <td align="center">Kualitas</td>
		   <td align="center">Baki Debet</td>
		   <td align="center">Jaminan</td>
		</tr>
	';
	$lrs = pg_query($query);
	$no=1;
	
	while($lrow=pg_fetch_array($lrs)){
		
		
		
		echo '<tr>';
		if($temp!=$lrow["no_fatg"]){
			$no=1;
			$temp=$lrow["no_fatg"];
			echo '
			
				<td valign="top">'.$lrow["fk_cabang"].'</td>
				<td valign="top">'.$lrow["no_fatg"].'</td>
				<td valign="top">'.($lrow["tgl_taksir"]==""?"":date("d/m/Y",strtotime($lrow["tgl_taksir"]))).'</td>
								
				<td valign="top">'.$lrow["no_cif"].'</td>
				<td valign="top">'.$lrow["nm_customer"].'</td>
				<td valign="top">&nbsp;'.$lrow["no_id"].'</td>
				<td valign="top">'.$lrow["alamat_tinggal"].'</td>
				<td valign="top">'.$lrow["nm_kelurahan"].'</td>
				<td valign="top">'.$lrow["nm_kecamatan"].'</td>
				<td valign="top">'.$lrow["nm_kota"].'</td>
				<td valign="top">&nbsp;'.$lrow["no_hp"].'</td>
				
				<td valign="top">'.$lrow["nm_jenis_barang"].'</td>
				<td valign="top">'.$lrow["nm_merek"].'</td>				
				<td valign="top">'.$lrow["nm_tipe"].'</td>
				<td valign="top">'.$lrow["nm_partner"].'</td>
				
				<td valign="top">'.$lrow["no_rangka"].'</td>
				<td valign="top">'.$lrow["no_mesin"].'</td>				

				<td valign="top">'.$lrow["kondisi"].'</td>
				<td valign="top">'.$lrow["kesimpulan"].'</td>				
				<td valign="top">'.$lrow["keterangan"].'</td>
				<td valign="top">'.$lrow["karakter"].'</td>
				<td valign="top">'.$lrow["capital"].'</td>
				<td valign="top">'.$lrow["sumber_dana"].'</td>
				<td valign="top">'.$lrow["alasan"].'</td>
				<td valign="top">'.$lrow["capacity"].'</td>
				<td valign="top">'.$lrow["credit_score_ca"].'</td>
				<td valign="top">'.$lrow["credit_score_surveyor"].'</td>
			';
		}else{
			for($i=0;$i<27;$i++){
				echo '
				<td valign="top"></td>			
				';
			}
			$no++;
		}
		
		echo '
				<td valign="top">'.$no.'</td>											
				<td valign="top">'.$lrow["ljk_bank"].'</td>							
				<td valign="top">'.($lrow["tgl_awal"]==""?"":date("d/m/Y",strtotime($lrow["tgl_awal"]))).'</td>
				<td valign="top">'.($lrow["tgl_akhir"]==""?"":date("d/m/Y",strtotime($lrow["tgl_akhir"]))).'</td>
				<td valign="top">'.$lrow["plafon_awal"].'</td>							
				<td valign="top">'.$lrow["angs"].'</td>							
				<td valign="top">'.$lrow["kualitas"].'</td>							
				<td valign="top">'.$lrow["baki_debet"].'</td>		
				<td valign="top">'.$lrow["jaminan"].'</td>							
				<td valign="top">'.$lrow["status_permohonan"].'</td>									
			</tr>
		';	
		
	
	}
	echo '</table>';
}


