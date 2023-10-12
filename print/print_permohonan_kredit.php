<?php

//error_reporting (E_ALL);  // remove this from Production Environment
require('pdfb/pdfb.php'); // Must include this
require '../requires/db_utility.inc.php';
require '../requires/general.inc.php';
require '../requires/timestamp.inc.php';
require '../requires/numeric.inc.php';
include_once("../requires/connection_db.inc.php");	
include_once("../requires/session.inc.php");	

class PDF extends PDFB
{
  
}
	$no_cif = $_REQUEST['no_cif'];
	$no_fatg = $_REQUEST['id_edit'];
	
	$pdf = new PDF("p", "pt", "A4");
	$pdf->AddPage();

	$query="
	select * from(
		select no_fatg as no_fatg_awal,fk_cabang as fk_cabang_awal,fk_cif as no_cif_awal from data_gadai.tblfatg
		where no_fatg='".$no_fatg ."'
	)as tblfatg
	left join (
		select fk_cif,fk_cabang as fk_cabang_fatg,nm_partner as nm_dealer,no_fatg as no_fatg1,nm_bpkb,kondisi_unit from data_gadai.tbltaksir_umum
		left join tblpartner on fk_partner_dealer=kd_partner
	)as tblmain on no_fatg_awal=no_fatg1
	left join viewkendaraan on no_fatg=no_fatg1
	left join(
		select * from data_gadai.tblproduk_cicilan
		where status_approval!='Batal'
	)as tblcicilan on no_fatg1=fk_fatg
	left join(
		select * from tblcustomer
		left join tblkelurahan on kd_kelurahan=fk_kelurahan_tinggal
		left join tblkecamatan on kd_kecamatan=fk_kecamatan
		left join tblkota on kd_kota=fk_kota	
		left join tblpekerjaan on kd_pekerjaan=fk_pekerjaan
		left join tblbidang_usaha on kd_bidang_usaha=fk_bidang_usaha
		left join tblstatus_rumah on kd_status_rumah=fk_status_rumah
	) as tblcust on no_cif_awal=no_cif
	left join tblcabang on fk_cabang_awal=kd_cabang
	";
	//echo $query;
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
		
	$date=split(" ",$lrow["tgl_lahir"]);
    $date1=split("-",$date[0]);
	if($lrow["tgl_lahir"]){
		$tgl_lahir=$date1[2]."/".$date1[1]."/".$date1[0];
	}else $tgl_lahir="";	
	
	$date=split(" ",$lrow["tgl_lahir_pasangan"]);
    $date1=split("-",$date[0]);
	if($lrow["tgl_lahir_pasangan"]){
		$tgl_lahir_pasangan=$date1[2]."/".$date1[1]."/".$date1[0];
	}else $tgl_lahir_pasangan="";
	
	$sts_nikah['1']='Menikah';
	$sts_nikah['2']='Belum Menikah';
	$sts_nikah['3']='Pisah/Cerai';
	$lrow["status_pernikahan"]=$sts_nikah[$lrow["status_pernikahan"]];
	$lrow["jenis_asuransi"]=jenis_asuransi($lrow);	
	
	if($lrow["tgl_cair"]){
		$tgl_cair_indo=date("d",strtotime($lrow['tgl_cair'])).' '.getMonthName(date("m",strtotime($lrow['tgl_cair'])),2).' '.date("Y",strtotime($lrow['tgl_cair']));
		$tgl_jt_indo=date("d",strtotime($lrow['tgl_jatuh_tempo'])).' '.getMonthName(date("m",strtotime($lrow['tgl_jatuh_tempo'])),2).' '.date("Y",strtotime($lrow['tgl_jatuh_tempo']));
	}
		
	
	$x=40;
	$y=15;
		
	$x1=$x+10;
	$x2=130;
	$x3=315;
	$x4=400;
			
	$pdf->Image('logo.jpeg',190,15,180);	
	$pdf->setY($y);
	
	$pdf->Cell(540,745,"",1);
	
	$pdf->SetFont("Arial", "B", 10);	
	$y+=55;	
	
	$pdf->Text(205, $y,'PERMOHONAN PEMBIAYAAN KREDIT');
	$y+=5;
	$pdf->line(30,$y,565,$y);
	
	$y+=18;
	
	$pdf->SetFont("Arial", "B", 10);	
	$pdf->Text($x,$y, 'No Permohonan');
	$pdf->Text($x2,$y, ': '.$no_fatg);
	$pdf->Text($x3,$y, 'Cabang');
	$pdf->Text($x4,$y, ': '.$lrow["nm_cabang"]);	
	$y+=22;
	
	$pdf->SetFont("Arial", "BU", 10);	
	$pdf->Text($x,$y, 'DATA PEMOHON');		
	$y+=12;
	
	$pdf->SetFont("Arial", "", 8);
	$pdf->Text($x,$y, 'Nama Debitur');
	$pdf->Text($x2,$y, ': '.$lrow["nm_customer"]);
	$pdf->Text($x3,$y, 'Tgl Lahir');
	$pdf->Text($x4,$y, ': '.$tgl_lahir);	
	$y+=12;
	
	$pdf->Text($x,$y, 'No KTP');
	$pdf->Text($x2,$y, ': '.$lrow["no_id"]);	
	$pdf->Text($x3,$y, 'No CIF');
	$pdf->Text($x4,$y, ': '.$lrow["no_cif"]);	
	$y+=12;		
	
	$pdf->Text($x,$y, 'Alamat KTP');
	$pdf->Text($x2,$y, ': '.$lrow["alamat_ktp"]);	
	$y+=12;	
	
	$pdf->Text($x,$y, 'Alamat Tinggal');
	$pdf->Text($x2,$y, ': '.$lrow["alamat_tinggal"]);
	$y+=12;
	
	$pdf->Text($x,$y, 'Kelurahan');
	$pdf->Text($x2,$y, ': '.$lrow["nm_kelurahan"]);
	$y+=12;
	$pdf->Text($x,$y, 'Kecamatan');
	$pdf->Text($x2,$y, ': '.$lrow["nm_kecamatan"]);
	$y+=12;
	$pdf->Text($x,$y, 'Kabupaten/Kota');
	$pdf->Text($x2,$y, ': '.$lrow["nm_kota"]);
	$y+=12;
	
	$pdf->Text($x,$y, 'No Hp');
	$pdf->Text($x2,$y, ': '.$lrow["no_hp"]);
	$pdf->Text($x3,$y, 'Nomor KK');
	$pdf->Text($x4,$y, ': '.$lrow["no_kk"]);
	$y+=12;

	$pdf->Text($x,$y, 'Status Nikah');
	$pdf->Text($x2,$y, ': '.$lrow["status_pernikahan"]);
	$pdf->Text($x3,$y, 'Nama Ibu');
	$pdf->Text($x4,$y, ': '.$lrow["nm_ibu"]);
	$y+=12;	
	
	$pdf->Text($x,$y, 'Status Tempat Tinggal');
	$pdf->Text($x2,$y, ': '.$lrow["nm_status_rumah"]);
	$pdf->Text($x3,$y, 'Jumlah Tanggungan');
	$pdf->Text($x4,$y, ': '.$lrow["jumlah_tanggungan"]);
	$y+=22;

	$pdf->SetFont("Arial", "BU", 10);	
	$pdf->Text($x,$y, 'DATA PEKERJAAN');
	$y+=12;	
	$pdf->SetFont("Arial", "", 8);	
	
	$pdf->Text($x,$y, 'Pekerjaan');
	$pdf->Text($x2,$y, ': '.$lrow["nm_pekerjaan"]);
	$y+=12;
	
	$pdf->Text($x,$y, 'Nama Perusahaan');
	$pdf->Text($x2,$y, ': '.$lrow["nm_tempat_kerja"]);
	$pdf->Text($x3,$y, 'Lama Bekerja');
	$pdf->Text($x4,$y, ': '.$lrow["lama_bekerja"].' (Tahun)');
	
	$y+=12;

	$pdf->Text($x,$y, 'Alamat Perusahaan');
	$pdf->Text($x2,$y, ': '.$lrow["alamat_bekerja"]);
	$y+=12;
	
	$pdf->Text($x,$y, 'Bidang Usaha');
	$pdf->Text($x2,$y, ': '.$lrow["nm_bidang_usaha"]);
	$pdf->Text($x3,$y, 'Jabatan');
	$pdf->Text($x4,$y, ': '.$lrow["jabatan"]);
	
	$y+=12;	
	
	$pdf->Text($x,$y, 'Penghasilan');
	$pdf->Text($x2,$y, ': '.convert_money("",$lrow["penghasilan"]));
	$pdf->Text($x3,$y, 'Penghasilan Lain');
	$pdf->Text($x4,$y, ': '.convert_money("",$lrow["penghasilan_lain"]));
	$y+=22;

					
	$pdf->SetFont("Arial", "BU", 10);	
	$pdf->Text($x,$y, 'DATA PASANGAN');		
	$y+=12;	
	$pdf->SetFont("Arial", "", 8);
		
	$pdf->Text($x,$y, 'Nama Pasangan');
	$pdf->Text($x2,$y, ': '.$lrow["nm_pasangan"]);
	$pdf->Text($x3,$y, 'Tgl Lahir Pasangan');
	$pdf->Text($x4,$y, ': '.$tgl_lahir_pasangan);		
	$y+=12;
	
	$pdf->Text($x,$y, 'No KTP Pasangan');
	$pdf->Text($x2,$y, ': '.$lrow["no_ktp_pasangan"]);	
	$pdf->Text($x3,$y, 'No Hp Pasangan');
	$pdf->Text($x4,$y, ': '.$lrow["no_hp_pasangan"]);	
	$y+=12;		
	
	$pdf->Text($x,$y, 'Alamat Pasangan');
	$pdf->Text($x2,$y, ': '.$lrow["alamat_pasangan"]);	
	$y+=12;		
	
	$pdf->Text($x,$y, 'Penghasilan Pasangan');
	$pdf->Text($x2,$y, ': '.convert_money("",$lrow["penghasilan_pasangan"]));	
	$y+=22;
	
	$pdf->SetFont("Arial", "BU", 10);	
	$pdf->Text($x,$y, 'DATA KENDARAAN');		
	$y+=12;	
	$pdf->SetFont("Arial", "", 8);
		
	$pdf->Text($x,$y, 'Dealer');
	$pdf->Text($x2,$y, ': '.$lrow["nm_dealer"]);
	$y+=12;
	
	$pdf->Text($x,$y, 'Merk/Tipe');
	$pdf->Text($x2,$y, ': '.$lrow["nm_merek"].' / '.$lrow["nm_tipe"]);	
	$pdf->Text($x3,$y, 'Jenis');
	$pdf->Text($x4,$y, ': '.$lrow["nm_jenis_barang"]);	
	$y+=12;		
	
	$pdf->Text($x,$y, 'Tahun/Warna');
	$pdf->Text($x2,$y, ': '.$lrow["tahun"].' / '.$lrow["warna"]);	
	$pdf->Text($x3,$y, 'No Polisi');
	$pdf->Text($x4,$y, ': '.$lrow["no_polisi"]);	
	$y+=12;		
	
	$pdf->Text($x,$y, 'No Rangka');
	$pdf->Text($x2,$y, ': '.$lrow["no_rangka"]);
	$pdf->Text($x3,$y, 'No Mesin');
	$pdf->Text($x4,$y, ': '.$lrow["no_mesin"]);	
	$y+=12;	
		
	$pdf->Text($x,$y, 'Kondisi');
	$pdf->Text($x2,$y, ': '.$lrow["kondisi_unit"]);
	$pdf->Text($x3,$y, 'Nama BPKB');
	$pdf->Text($x4,$y, ': '.$lrow["nm_bpkb"]);
	$y+=22;

	$pdf->SetFont("Arial", "BU", 10);	
	$pdf->Text($x,$y, 'RINCIAN KREDIT ');		
	$y+=12;	
	$pdf->SetFont("Arial", "", 8);
	
	$pdf->Text($x,$y, 'No Kontrak');
	$pdf->Text($x2,$y, ': '.$lrow["no_sbg"]);
	$pdf->Text($x3,$y, 'Tgl Pengajuan');
	$pdf->Text($x4,$y, ': '.date("d/m/Y",strtotime($lrow["tgl_pengajuan"])));	
	
	$y+=12;	
		
	$pdf->Text($x,$y, 'Harga OTR');
	$pdf->Text($x2,$y, ': '.align_right(convert_money("",$lrow["total_nilai_pinjaman"])));	
	$y+=12;
	$pdf->Text($x,$y, 'Nilai DP');
	$pdf->Text($x2,$y, ': '.align_right(convert_money("",$lrow["nilai_dp"])));	
	$y+=12;
	$pdf->Text($x,$y, 'Biaya Admin');
	$pdf->Text($x2,$y, ': '.align_right(convert_money("",$lrow["biaya_admin"])));	
	$y+=12;	
	$pdf->Text($x,$y, 'Pelunasan ke dealer');
	$pdf->Text($x2,$y, ': '.align_right(convert_money("",$lrow["nilai_ap_customer"])));	
	$y+=22;
	
	$pdf->Text($x,$y, 'Pokok Hutang');
	$pdf->Text($x2,$y, ': '.align_right(convert_money("",$lrow["pokok_hutang"])));	
	$y+=12;
	$pdf->Text($x,$y, 'Margin');
	$pdf->Text($x2,$y, ': '.align_right(convert_money("",$lrow["biaya_penyimpanan"])));		
	$pdf->Text($x3,$y, 'Rate Flat');
	$pdf->Text($x4,$y, ': '.convert_money("",$lrow["rate_flat"],2).' %');	
	$y+=12;
	$pdf->Text($x,$y, 'Total Hutang');
	$pdf->Text($x2,$y, ': '.align_right(convert_money("",$lrow["total_hutang"])));	
	$y+=12;
	
	$pdf->Text($x,$y, 'Angsuran/Bln');
	$pdf->Text($x2,$y, ': '.align_right(convert_money("",$lrow["angsuran_bulan"])));	
	$pdf->Text($x3,$y, 'Tenor');
	$pdf->Text($x4,$y, ': '.$lrow["lama_pinjaman"]);		
	$y+=12;
	$pdf->Text($x,$y, 'Total Asuransi');
	$pdf->Text($x2,$y, ': '.align_right(convert_money("",$lrow["nilai_asuransi"])));	
	$pdf->Text($x3,$y, 'Jenis Asuransi');
	$pdf->Text($x4,$y, ': '.$lrow["jenis_asuransi"]);	
	$y+=12;
	$pdf->Text($x,$y, 'Biaya Polis');
	$pdf->Text($x2,$y, ': '.align_right(convert_money("",$lrow["biaya_polis"])));	
	$y+=12;
	$pdf->Text($x,$y, 'TJH 3');
	$pdf->Text($x2,$y, ': '.align_right(convert_money("",$lrow["tjh_3"])));	
	$y+=12;
	$pdf->Text($x,$y, 'PA Supir');
	$pdf->Text($x2,$y, ': '.align_right(convert_money("",$lrow["pa_supir"])));	
	$y+=12;
	$pdf->Text($x,$y, 'PA Penumpang');
	$pdf->Text($x2,$y, ': '.align_right(convert_money("",$lrow["pa_penumpang"])));	
	$y+=22;

	$pdf->Output();
	
function align_right($text){
	global $pdf,$x2,$y;
	$pdf->setY($y-2);
	$pdf->Cell('171',0,$text,0,0,'R');
}

?>