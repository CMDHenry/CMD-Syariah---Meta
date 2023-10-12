<?php

include_once("report.php");

function filter_request(){
	global $showCab,$showPeriode;
	$showCab='t';
	$showPeriode='t';

}


function excel_content(){
	global $fk_cabang,$periode_awal,$periode_akhir;

	if($periode_awal != '' && $periode_akhir != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" tgl_input_customer between '".$periode_awal." 00:00:00' and '".$periode_akhir." 23:59:59'";
	}
	if($fk_cabang != ''){
		//if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" and fk_cabang_asal = '".$fk_cabang."' ";
	}
	
	if ($lwhere!="") $lwhere=" where ".$lwhere;
	
	
	$query = "
	select * from tblcustomer
	left join (
	select kd_kelurahan,nm_kelurahan,nm_kecamatan,nm_kota,nm_provinsi,kd_pos from (
		select * from tblkelurahan 
		where kd_kelurahan in (select fk_kelurahan_ktp from tblcustomer)
	)as tblkelurahan
	left join tblkecamatan on fk_kecamatan=kd_kecamatan
	left join tblkota on fk_kota=kd_kota
	left join tblprovinsi on fk_provinsi =kd_provinsi
	) as tblarea on fk_kelurahan_ktp=kd_kelurahan
	left join (
	select kd_kelurahan as kd_kelurahan1, nm_kelurahan as nm_kelurahan1,nm_kecamatan as nm_kecamatan1,nm_kota as nm_kota1,nm_provinsi as nm_provinsi1, kd_pos as kd_pos1 from (
		select * from tblkelurahan  where kd_kelurahan in (select fk_kelurahan_tinggal from tblcustomer)
	) as tblkelurahan
	left join tblkecamatan on fk_kecamatan=kd_kecamatan
	left join tblkota on fk_kota=kd_kota
	left join tblprovinsi on fk_provinsi =kd_provinsi
	) as tblarea1 on fk_kelurahan_tinggal=kd_kelurahan1
	left join (
	select kd_kelurahan as kd_kelurahan2, nm_kelurahan as nm_kelurahan2,nm_kecamatan as nm_kecamatan2,nm_kota as nm_kota2,nm_provinsi as nm_provinsi2, kd_pos as kd_pos2 from (
		select * from tblkelurahan  where kd_kelurahan in (select fk_kelurahan_badan_usaha from tblcustomer)
	) as tblkelurahan
	left join tblkecamatan on fk_kecamatan=kd_kecamatan
	left join tblkota on fk_kota=kd_kota
	left join tblprovinsi on fk_provinsi =kd_provinsi
	) as tblarea2 on fk_kelurahan_badan_usaha=kd_kelurahan2
	left join (
		select kd_pekerjaan,nm_pekerjaan from tblpekerjaan
	) as tblpekerjaan on fk_pekerjaan=kd_pekerjaan
	left join (
		select kd_pekerjaan as kd_pekerjaan_pasangan,nm_pekerjaan as nm_pekerjaan_pasangan from tblpekerjaan
	) as tblpekerjaan_pasangan on fk_pekerjaan_pasangan=kd_pekerjaan_pasangan
	left join (
		select kd_bidang_usaha,nm_bidang_usaha from tblbidang_usaha
	) as tblbidang_usaha on fk_bidang_usaha=kd_bidang_usaha
	left join (
		select kd_agama,nm_agama from tblagama
	) as tblagama on fk_agama=kd_agama
	left join (
		select kd_pendidikan,nm_pendidikan from tblpendidikan
	) as pendidikan on fk_pendidikan=kd_pendidikan
	left join (
		select kd_status_rumah,nm_status_rumah from tblstatus_rumah
	) as tblstatus_rumah on fk_status_rumah=kd_status_rumah
	".$lwhere."
	";
//showquery($query);
		
	echo 	
	'<table border="1">
	  
		   <tr>
		    <td align="center">No ID</td>
			<td align="center">Nama Customer</td>
			<td align="center">Cabang Asal</td>
			<td align="center">Tempat Lahir</td>
			<td align="center">Tgl Lahir</td>
			<td align="center">Jenis Kelamin</td>
			<td align="center">Jenis Customer</td>
			<td align="center">No CIF</td>
			<td align="center">Alamat KTP</td>
			<td align="center">Kelurahan KTP</td>
			<td align="center">Kecamatan KTP</td>
			<td align="center">Kota KTP</td>	
			<td align="center">Provinsi KTP</td>
			<td align="center">Kode Pos KTP</td>
			<td align="center">Alamat Tinggal</td>
			<td align="center">Kelurahan Tinggal</td>
			<td align="center">Kecamatan Tinggal</td>
			<td align="center">Kota Tinggal</td>	
			<td align="center">Provinsi Tinggal</td>
			<td align="center">Kode Pos Tinggal</td>
			<td align="center">No HP</td>
			<td align="center">No WA</td>
			<td align="center">Nama Ibu</td>
			<td align="center">NPWP</td>
			<td align="center">No KK</td>
			<td align="center">Email</td>
			<td align="center">Status Nikah</td>
			<td align="center">Jumlah Tanggungan</td>
			<td align="center">Status Rumah</td>
			<td align="center">Lama Tinggal (Tahun)</td>
			<td align="center">Pekerjaan</td>
			<td align="center">Lama Bekerja (Tahun)</td>
			<td align="center">Nama Tempat Kerja</td>
			<td align="center">Alamat Kerja</td>
			<td align="center">Jabatan</td>			
			<td align="center">Bidang Usaha</td>
			<td align="center">Penghasilan</td>
			<td align="center">Penghasilan Lain</td>
			<td align="center">Nama Pasangan</td>
			<td align="center">No HP Pasangan</td>
			<td align="center">No KTP Pasangan</td>
			<td align="center">Alamat Pasangan</td>
			<td align="center">Tanggal Lahir Pasangan</td>
			<td align="center">Pekerjaan Pasangan</td>
			<td align="center">Penghasilan Pasangan</td>
			<td align="center">Nama Perusahaan Pasangan</td>
			<td align="center">Alamat Perusahaan Pasangan</td>
			<td align="center">No ID Badan Usaha</td>
			<td align="center">Nama</td>
			<td align="center">NPWP</td>
			<td align="center">No Akta Pendirian</td>
			<td align="center">Tgl Akta Pendirian</td>
			<td align="center">Telp</td>
			<td align="center">No HP</td>
			<td align="center">Email</td>
			<td align="center">Alamat</td>
			<td align="center">Kelurahan</td>
			<td align="center">Kota</td>
			<td align="center">Kode Pos</td>
			<td align="center">Nama Pemilik</td>
			<td align="center">No ID Pemillik</td>
			<td align="center">Total Aset</td>
			<td align="center">No Akta Terakhir</td>
			<td align="center">Tanggal Akta Terakhir</td>
			<td align="center">Tanggal Input Customer</td>
			<td align="center">Agama</td>
			<td align="center">Pendidikan</td>
			<td align="center">No ID Penjamin</td>
			<td align="center">Nama Penjamin</td>

		  </tr>
		  
	';
	$lrs = pg_query($query);
	$no=1;

	while($lrow=pg_fetch_array($lrs)){
	
		$tgl_lahir=split(" ",$lrow["tgl_lahir"]);
       	$tgl_lahir1=split("-",$tgl_lahir[0]);
		$tgl_lahir2=$tgl_lahir1[2]."/".$tgl_lahir1[1]."/".$tgl_lahir1[0];
		
		$tgl_lahir_pasangan=split(" ",$lrow["tgl_lahir_pasangan"]);
       	$tgl_lahir_pasangan1=split("-",$tgl_lahir_pasangan[0]);
		$tgl_lahir_pasangan2=$tgl_lahir_pasangan1[2]."/".$tgl_lahir_pasangan1[1]."/".$tgl_lahir_pasangan1[0];
		
		if($lrow["jenis_kelamin"]=='L'){
			$jenis_kelamin='Laki - laki';
		}
		else if($lrow["jenis_kelamin"]=='P'){
			$jenis_kelamin='Perempuan';
		}
		else if($lrow["jenis_kelamin"]=='B'){
			$jenis_kelamin='Badan Usaha';
		}
		else if($lrow["jenis_kelamin"]=='M'){
			$jenis_kelamin='Masyarakat';
		}
		else $jenis_kelamin='';

		
		if($lrow["status_pernikahan"]=='1'){
			$status_pernikahan='Menikah';
		}
		else if($lrow["status_pernikahan"]=='2'){
			$status_pernikahan='Belum Menikah';
		}
		else if($lrow["status_pernikahan"]=='3'){
			$status_pernikahan='Pisah/Cerai';
		}
		else $status_pernikahan='';
		
		
		if($lrow["jenis_customer"]=='0'){
			$jenis_customer='Individu';
		}
		else if($lrow["jenis_customer"]=='1'){
			$jenis_customer='Badan Usaha';
		}
		else $jenis_customer='';
		
		echo '
		
			<tr>
				<td valign="top">&nbsp;'.$lrow["no_id"].'</td>
				<td valign="top">&nbsp;'.$lrow["nm_customer"].'</td>
				<td valign="top" >'.$lrow["fk_cabang_asal"].'</td>
				<td valign="top" >'.$lrow["tempat_lahir"].'</td>
				<td valign="top" >&nbsp;'.$tgl_lahir2.'</td>
				<td valign="top" >'.$jenis_kelamin.'</td>
				<td valign="top" >'.$jenis_customer.'</td>
				<td valign="top" >'.$lrow["no_cif"].'</td>
				<td valign="top" >'.$lrow["alamat_ktp"].'</td>
				<td valign="top" >'.$lrow["nm_kelurahan"].'</td>
				<td valign="top" >'.$lrow["nm_kecamatan"].'</td>
				<td valign="top" >'.$lrow["nm_kota"].'</td>
				<td valign="top" >'.$lrow["nm_provinsi"].'</td>
				<td valign="top" >'.$lrow["kd_pos"].'</td>
				<td valign="top" >'.$lrow["alamat_tinggal"].'</td>
				<td valign="top" >'.$lrow["nm_kelurahan1"].'</td>
				<td valign="top" >'.$lrow["nm_kecamatan1"].'</td>
				<td valign="top" >'.$lrow["nm_kota1"].'</td>
				<td valign="top" >'.$lrow["nm_provinsi1"].'</td>
				<td valign="top" >'.$lrow["kd_pos1"].'</td>
				<td valign="top" >&nbsp;'.$lrow["no_hp"].'</td>
				<td valign="top" >&nbsp;'.$lrow["no_wa"].'</td>
				<td valign="top" >'.$lrow["nm_ibu"].'</td>
				<td valign="top" >&nbsp;'.$lrow["npwp"].'</td>
				<td valign="top" >&nbsp;'.$lrow["no_kk"].'</td>
				<td valign="top" >'.$lrow["email"].'</td>
				<td valign="top" >'.$status_pernikahan.'</td>
				<td valign="top" >'.$lrow["jumlah_tanggungan"].'</td>
				<td valign="top" >'.$lrow["nm_status_rumah"].'</td>
				<td valign="top" >'.$lrow["lama_tinggal"].'</td>
				<td valign="top" >'.$lrow["nm_pekerjaan"].'</td>
				<td valign="top" >'.$lrow["lama_bekerja"].'</td>
				<td valign="top" >'.$lrow["nm_tempat_kerja"].'</td>
				<td valign="top" >'.$lrow["alamat_bekerja"].'</td>
				<td valign="top" >'.$lrow["jabatan"].'</td>				
				<td valign="top" >'.$lrow["nm_bidang_usaha"].'</td>
				<td valign="top" >'.$lrow["penghasilan"].'</td>
				<td valign="top" >'.$lrow["penghasilan_lain"].'</td>
				<td valign="top" >'.$lrow["nm_pasangan"].'</td>
				<td valign="top" >&nbsp;'.$lrow["no_hp_pasangan"].'</td>
				<td valign="top" >&nbsp;'.$lrow["no_ktp_pasangan"].'</td>
				<td valign="top" >'.$lrow["alamat_pasangan"].'</td>
				<td valign="top" >&nbsp;'.$tgl_lahir_pasangan2.'</td>
				<td valign="top" >'.$lrow["nm_pekerjaan_pasangan"].'</td>
				<td valign="top" >'.$lrow["penghasilan_pasangan"].'</td>
				<td valign="top" >'.$lrow["nm_perusahaan_pasangan"].'</td>
				<td valign="top" >'.$lrow["alamat_perusahaan_pasangan"].'</td>
				<td valign="top" >&nbsp;'.$lrow["no_id_badan_usaha"].'</td>
				<td valign="top" >'.$lrow["nm_badan_usaha"].'</td>
				<td valign="top" >&nbsp;'.$lrow["npwp_badan_usaha"].'</td>
				<td valign="top" >&nbsp;'.$lrow["no_akta_pendirian"].'</td>
				<td valign="top" >&nbsp;'.($lrow["tgl_akta_pendirian"]==""?"":date("d/m/Y",strtotime($lrow["tgl_akta_pendirian"]))).'</td>
				<td valign="top" >'.$lrow["telp_badan_usaha"].'</td>
				<td valign="top" >&nbsp;'.$lrow["no_hp_badan_usaha"].'</td>
				<td valign="top" >'.$lrow["email_badan_usaha"].'</td>
				<td valign="top" >'.$lrow["alamat_badan_usaha"].'</td>
				<td valign="top" >'.$lrow["nm_kelurahan2"].'</td>
				<td valign="top" >'.$lrow["nm_kota2"].'</td>
				<td valign="top" >'.$lrow["kd_pos2"].'</td>
				<td valign="top" >'.$lrow["nm_pemilik"].'</td>
				<td valign="top" >&nbsp;'.$lrow["no_id_pemilik"].'</td>
				<td valign="top" >'.$lrow["total_aset"].'</td>
				<td valign="top" >&nbsp;'.$lrow["no_akta_terakhir"].'</td>
				<td valign="top" >&nbsp;'.($lrow["tgl_akta_terakhir"]==""?"":date("d/m/Y",strtotime($lrow["tgl_akta_terakhir"]))).'</td>
				<td valign="top" >&nbsp;'.($lrow["tgl_input_customer"]==""?"":date("d/m/Y",strtotime($lrow["tgl_input_customer"]))).'</td>
				<td valign="top" >'.$lrow["nm_agama"].'</td>
				<td valign="top" >'.$lrow["nm_pendidikan"].'</td>
				<td valign="top" >&nbsp;'.$lrow["no_id_penjamin"].'</td>
				<td valign="top" >'.$lrow["nm_penjamin"].'</td>
			</tr>
		';	
		$no++;
	
	}
	echo '</table>';
}


