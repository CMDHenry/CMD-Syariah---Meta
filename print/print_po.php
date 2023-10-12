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
	$pdf = new PDF("p", "pt", "A4");
	$pdf->AddPage();
	
	$id_edit=$_REQUEST["id_edit"];

	$query="
	select case when pembayaran='1' then 'Tunai' else 'Transfer' end as jenis_pembayaran,*,tblpartner.alamat as alamat_dealer from data_gadai.tblproduk_cicilan
	left join data_gadai.tbltaksir_umum on fk_fatg=no_fatg
	left join tblcustomer on fk_cif=no_cif
	left join(
		select fk_fatg as fk_fatg1, nm_merek,nm_tipe,nilai_awal,diskon,nilai_taksir,nm_jenis_barang,kategori from data_gadai.tbltaksir_umum_detail
		left join tblbarang on fk_barang=kd_barang
		left join tbltipe on fk_tipe=kd_tipe
		left join tblmodel on fk_model=kd_model
		left join tblmerek on fk_merek=kd_merek
		left join tbljenis_barang on fk_jenis_barang =kd_jenis_barang
	)as tblbarang on fk_fatg=fk_fatg1
	left join tblwarna on fk_warna=kd_warna
	inner join tblcabang on fk_cabang=kd_cabang
	left join tblpartner on fk_partner_dealer=kd_partner
	where no_sbg='".$id_edit."'";
	//echo($query);
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	
	$nm_kacab=get_rec("tblkaryawan_dealer","nm_karyawan","fk_dealer='".$lrow["fk_partner_dealer"]."' and fk_jabatan='DLR-001'");

	$x=33;
	$y=25;
		
	$pdf->setY($y);
	$pdf->Cell(540,700,"",1);
	$pdf->SetTextColor(255, 0, 0);
	$pdf->SetFont("Arial", "I", 35);
	//pdf->Text($x+40,$y+35, $lrow["nm_perusahaan"]);
	$pdf->Image('logo.jpeg',30,30,180);	

	$pdf->SetTextColor(0,0,0);
	$y+=65;
	$pdf->Line($x-5,$y-20,568,$y-20);
	
	$pdf->SetFont("Arial", "", 10);
	
	$pdf->Text($x,$y, ' No : '.$id_edit);
	$pdf->Text($x+350,$y, $lrow["nm_cabang"].', '.date("d M Y",strtotime($lrow["tgl_pengajuan"])));
	$y+=20;
	
	$pdf->Text($x,$y, ' Kepada :');
	$pdf->SetFont("Arial", "B", 9);
	$y+=15;
	$pdf->Text($x,$y, ' '.$lrow["nm_partner"]); 
	$pdf->SetFont("Arial", "", 9);
	
	$pdf->SetFont("Arial", "B", 9);
	$y+=10;
	$pdf->Text($x,$y, ' '.$lrow["alamat_dealer"]);
	$pdf->SetFont("Arial", "U", 11);
	$y+=25;
	$pdf->Text($x,$y, ' Perihal : Persetujuan Pembiayaan dan Pemesanan Barang');
	$y+=20;
	$pdf->SetFont("Arial", "", 9);
	$pdf->Text($x,$y, ' Dengan Hormat,');
	$y+=10;
	$pdf->Text($x,$y, ' Dengan ini kami sampaikan bahwa kami telah menyetujui permohonan pembiayaan berikut ini :');
	
	$y+=15;
	$x1=$x+100;
	$x2=$x1+100;
	$x3=$x2+20;
	$pdf->Text($x1,$y, ' Merk / Type ');
	$pdf->Text($x2,$y, ' : ');
	$pdf->Text($x3,$y, $lrow["nm_merek"].' / '.$lrow["nm_tipe"]);
	
	$y+=10;
	$pdf->Text($x1,$y, ' Jenis ');
	$pdf->Text($x2,$y, ' : ');
	$pdf->Text($x3,$y, $lrow["nm_jenis_barang"]);
		
	$y+=10;
	$pdf->Text($x1,$y, ' Tahun / Warna ');
	$pdf->Text($x2,$y, ' : ');
	$pdf->Text($x3,$y, $lrow["tahun"].' / '.$lrow["warna"]);
	
	$y+=10;
	$pdf->Text($x1,$y, ' No. Rangka ');
	$pdf->Text($x2,$y, ' : ');
	$pdf->Text($x3,$y, $lrow["no_rangka"]);
	
	$y+=10;
	$pdf->Text($x1,$y, ' No. Mesin ');
	$pdf->Text($x2,$y, ' : ');
	$pdf->Text($x3,$y, $lrow["no_mesin"]);
	
	$y+=10;
	$pdf->Text($x1,$y, ' No. Polisi ');
	$pdf->Text($x2,$y, ' : ');
	$pdf->Text($x3,$y, $lrow["no_polisi"]);
	
	$y+=10;
	$pdf->Text($x1,$y, ' Nama BPKB ');
	$pdf->Text($x2,$y, ' : ');
	$pdf->Text($x3,$y, $lrow["nm_bpkb"]);
	
	
	$y=290;
	$pdf->Line($x-5,$y-20,568,$y-20);
	$pdf->Text($x,$y, ' Kepada Sdr/I ');

	$y+=20;
	$pdf->Text($x1,$y, ' Nama ');
	$pdf->Text($x2,$y, ' : ');
	$pdf->Text($x3,$y, $lrow["nm_customer"]);
	
	$alamat=$lrow["alamat_ktp"];
	if(strlen($alamat) > 25){
		$alamat = wordwrap($alamat,25,"<split>");
		$tmp_alamat = split("<split>",$alamat);
	} else $tmp_alamat[0] = $alamat;
		
	$y+=10;
	$pdf->Text($x1,$y, ' Alamat ');
	$pdf->Text($x2,$y, ' : ');
	$pdf->Text($x3,$y, $tmp_alamat[0]);
	$y+=10;
	$pdf->Text($x3,$y, $tmp_alamat[1]);
	
	
	$y=360;
	$pdf->Line($x-5,$y-20,568,$y-20);
	$pdf->Text($x,$y, ' Pembiayaan dilakukan oleh '.$lrow["nm_perusahaan"].' sebagai pelunasan dengan perincian sbb: ');
	
	$y+=20;
	$pdf->Text($x1,$y, ' Harga Unit ');
	$pdf->Text($x2,$y, ' : ');
	$pdf->Text($x3,$y, convert_money('',$lrow["nilai_awal"],0));
	
	$y+=10;
	$pdf->Text($x1,$y, ' Diskon Dealer ');
	$pdf->Text($x2,$y, ' : ');
	$pdf->Text($x3,$y, '_________');
	$pdf->Text($x3,$y, convert_money('',$lrow["diskon"],0));
	
	$y+=12;
	$pdf->Text($x1,$y, ' Harga setelah potongan ');
	$pdf->Text($x2,$y, ' : ');
	$pdf->Text($x3,$y, convert_money('',$lrow["nilai_taksir"],0));
	
	$y+=10;
	$total_dp=$lrow["nilai_dp"];
	if($lrow["kategori"]=='R4')$total_dp+=($lrow["biaya_admin"]+$lrow["biaya_polis"]);
	$pdf->Text($x1,$y, ' DP '); 
	$pdf->Text($x2,$y, ' : ');
	$pdf->Text($x3,$y, '_________');
	$pdf->Text($x3,$y, convert_money('',$total_dp,0));
	
	$y+=12;
	$pdf->Text($x1,$y, ' Pelunasan Dealer ');
	$pdf->Text($x2,$y, ' : ');
	$pdf->SetFont("Arial", "B", 9);
	$pdf->Text($x3,$y, convert_money('',$lrow["nilai_taksir"]-$total_dp,0));
	
	$pdf->SetFont("Arial", "", 9);
	
	
	$y=450;
	$pdf->Line($x-5,$y-20,568,$y-20);
	$pdf->Text($x,$y, ' Sebelum Dokumen Perjanjian Pembiayaan dan Berita Acara Serah Terima ditanda-tangani serta diterima oleh  ');
	$y+=10;
	$pdf->Text($x,$y, ' '.$lrow["nm_perusahaan"].', maka pembiayaan ini belum mengikat '.$lrow["nm_perusahaan"].'  ');
	$y+=20;
	$pdf->Text($x,$y, ' Atas perhatian dan kerjasamanya, kami ucapkan terima kasih ');
	
	$y+=20;
	$pdf->Text($x+50,$y, ' Supplier, '); $pdf->Text($x+400,$y, ' Hormat Kami, ');
	$y+=60;
	$pdf->Text($x+30,$y, '(_________________)'); $pdf->Text($x+380,$y, '(_________________)');	
	$y+=15;
	$pdf->Text($x+30,$y, $lrow["nm_partner"]); $pdf->Text($x+375,$y, $lrow["nm_perusahaan"]);						
	
	$y=610;

	$y+=10;
	$pdf->Line($x-5,$y-20,568,$y-20);
	$pdf->Text($x,$y, ' Angsuran /Bulan ');
	$pdf->Text($x+100,$y, ' : '.convert_money("",$lrow["angsuran_bulan"]));
	
	//$nm_karyawan_ca=get_rec("tblkaryawan","nm_depan","npk='".$lrow["fk_karyawan_ca"]."'");
	//$pdf->Text($x3,$y, ' CA ');
	//$pdf->Text($x3+50,$y, ' : '.$nm_karyawan_ca);
	$y+=10;
	$pdf->Text($x,$y, ' Lama Angsuran ');
	$pdf->Text($x+100,$y, ' : '.$lrow["lama_pinjaman"]);
	
	//$nm_karyawan_survey=get_rec("tblkaryawan","nm_depan","npk='".$lrow["fk_karyawan_survey"]."'");
	//$pdf->Text($x3,$y, ' Survey ');
	//$pdf->Text($x3+50,$y, ' : '.$nm_karyawan_survey);
	
	//$y+=10;
	//$pdf->Text($x,$y, ' Jenis Pembayaran ');
	//$pdf->Text($x+100,$y, ' : '.$lrow["jenis_pembayaran"]);
	
	//$nm_karyawan_kredit=get_rec("tblkaryawan","nm_depan","npk='".$lrow["fk_karyawan_kredit"]."'");
	//$pdf->Text($x3,$y, ' Kredit ');
	//$pdf->Text($x3+50,$y, ' : '.$nm_karyawan_kredit);
	
	$lrow["jenis_asuransi"]=' '.($lrow['all_risk_dari_tahun'].'-'.$lrow['all_risk_sampai_tahun']).' AR';
	$lrow["jenis_asuransi"].='+ '.($lrow['tlo_dari_tahun'].'-'.$lrow['tlo_sampai_tahun']).' TLO';
	
	//$y+=10;
	//$pdf->Text($x,$y, ' Jenis Asuransi ');
	//$pdf->Text($x+100,$y, ' : '.$lrow["jenis_asuransi"]);	
	
	$y+=10;
	$nm_karyawan_sales=get_rec("tblkaryawan_dealer","nm_karyawan","nik='".$lrow["fk_karyawan_sales"]."'");
	$pdf->Text($x,$y, ' Sales ');
	$pdf->Text($x+100,$y, ' : '.$nm_karyawan_sales);
	
	$y+=10;
	$pdf->Text($x,$y, ' Keterangan ');
	$pdf->Text($x+100,$y, ' : '.$lrow["keterangan_dealer"]);
	
	//$nm_karyawan_komite=get_rec("tblkaryawan","nm_depan","npk='".$lrow["fk_karyawan_komite"]."'");
	//$pdf->Text($x3,$y, ' Komite ');
	//$pdf->Text($x3+50,$y, ' : '.$nm_karyawan_komite);

	$pdf->Output();
?>