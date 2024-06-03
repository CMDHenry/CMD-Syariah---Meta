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
	function RightAlignText($x, $y, $text, $width)
    {
        // Get the string width
        $stringWidth = $this->GetStringWidth($text);
        
        // Calculate the position
        $positionX = $x + ($width - $stringWidth);

        // Set the position and print the text
        $this->Text($positionX, $y, $text);
    }

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
	
	$nm_cmo=get_rec("tblkaryawan","nm_depan","npk='".$lrow["fk_karyawan_cmo"]."'");
	$nm_sales=get_rec("tblkaryawan_dealer","nm_karyawan","nik='".$lrow["fk_karyawan_sales"]."'");
	$nm_spv=get_rec("tblkaryawan_dealer","nm_karyawan","nik='".$lrow["fk_karyawan_spv"]."'");
	$nm_kacab=get_rec("tblkaryawan_dealer","nm_karyawan","nik='".$lrow["fk_karyawan_kacab"]."'");
	$tgl_jto_pertama = get_rec("data_fa.tblangsuran","tgl_jatuh_tempo","fk_sbg='".$lrow["no_sbg"]."' and angsuran_ke ='1'");
	$jto_pertama = $tgl_jto_pertama ? date("d",strtotime($tgl_jto_pertama)).' '.getMonthName(date("m",strtotime($tgl_jto_pertama)),2).' '.date("Y",strtotime($tgl_jto_pertama)) : '';
	$tgl_pengajuan=date("d",strtotime($lrow['tgl_pengajuan'])).' '.getMonthName(date("m",strtotime($lrow['tgl_pengajuan'])),2).' '.date("Y",strtotime($lrow['tgl_pengajuan']));
	switch($lrow['fk_partner_asuransi']){
		case 'RLC':
			$nm_perusahaan_asuransi = ' RELIANCE';
			break;
	}

	$x=20;
	$y=15;
	$width = 40;
		
	$pdf->setY($y);
	// $pdf->Cell(568,700,"",1);
	// $pdf->SetTextColor(255, 0, 0);
	$pdf->SetFont("Arial", "B", 22);
	$pdf->Text($x+155,$y+35, $lrow["nm_perusahaan"]);
	$pdf->Image('logo.jpeg',15,30,150);

	$pdf->SetTextColor(0,0,0);
	$y+=65;
	$pdf->Line($x-5,$y-20,568,$y-20);
	$pdf->Line($x-5,$y-18,568,$y-18);
	
	$pdf->SetFont("Arial", "", 10);
	
	$pdf->Text($x,$y, ' Nomor PO : '.$id_edit);
	$pdf->RightAlignText($x+450, $y, $lrow["nm_cabang"].', '. $tgl_pengajuan, 100);
	// $pdf->Text($x+390,$y, $lrow["nm_cabang"].', '. $tgl_pengajuan);
	$y+=15;
	
	if($lrow['status_barang'] == 'Baru'){
		$pdf->Text($x,$y, ' Kepada Yth :');
		$pdf->SetFont("Arial", "B", 9);
		$y+=10;
		$pdf->Text($x,$y, ' '.$lrow["nm_partner"]); 
		$y+=10;
		$pdf->Text($x,$y, ' '.$lrow["alamat_dealer"]);
		$y+=10;
		$pdf->Text($x,$y, ' '.$lrow["nm_cabang"]);
		$pdf->SetFont("Arial", "BU", 11);
		$y+=20;
		$pdf->Text($x,$y, ' Perihal : Persetujuan Fasilitas Pembiayaan');
	} else{
		$pdf->SetFont("Arial", "BU", 11);
		$pdf->Text($x,$y, ' Perihal : Persetujuan Fasilitas Pembiayaan');
		$y+=50;
	}
	
	$y+=15;
	$pdf->SetFont("Arial", "", 9);
	$pdf->Text($x,$y, ' Dengan Hormat,');
	$y+=10;
	$pdf->Text($x,$y, ' Dengan ini kami sampaikan bahwa kami telah menyetujui permohonan fasilitas pembiayaan dengan rincian sebagai berikut :');

	$pdf->Rect($x,$y+9,568,58,'');
	$y+=20;
	$pdf->SetFont("Arial", "BU", 9);
	$pdf->Text($x,$y, ' DATA DEBITUR ');	
	$pdf->SetFont("Arial", "", 9);

	$bullet = chr(149); // ASCII code for bullet
	$y+=10;
	$x1=$x+30;
	$x2=$x1+100;
	$x3=$x2+10;	
	$x4=$x3+150;
	$x5=$x4+70;
	$x6=$x5+10;
	$pdf->Text($x1,$y, ' ' . $bullet . ' Nama ');
	$pdf->Text($x2+100,$y, ' : ');
	$pdf->Text($x3+100,$y, $lrow["nm_customer"]);

	$y+=10;
	$pdf->Text($x1,$y, ' ' . $bullet . ' No KTP ');
	$pdf->Text($x2+100,$y, ' : ');
	$pdf->Text($x3+100,$y, $lrow["no_id"]);
	
	$alamat=strtoupper($lrow["alamat_ktp"]);
	if(strlen($alamat) > 60){
		$alamat = wordwrap($alamat,60,"<split>");
		$tmp_alamat = split("<split>",$alamat);
	} else $tmp_alamat[0] = $alamat;
		
	$y+=10;
	$pdf->Text($x1,$y, ' ' . $bullet . ' Alamat ');
	$pdf->Text($x2+100,$y, ' : ');
	$pdf->Text($x3+100,$y, $tmp_alamat[0]);
	$y+=10;
	$pdf->Text($x3+100,$y, $tmp_alamat[1]);	

	if ($lrow['status_barang'] == 'Datun') {
		$pdf->Rect($x,$y+25,568,55,'');
	} else {
		$pdf->Rect($x,$y+25,568,50,'');
	}
	$y+=35;
	$pdf->SetFont("Arial", "BU", 9);
	if ($lrow['status_barang'] == 'Datun') {
		$pdf->Text($x, $y, ' OBJEK AGUNAN ');
	} else {
		$pdf->Text($x, $y, ' OBJEK PEMBIAYAAN ');
	}
	$pdf->SetFont("Arial", "", 9);
	
	
	if ($lrow['status_barang'] == 'Datun') {
		$y+=10;
		$pdf->Text($x1,$y, ' ' . $bullet . ' Harga Unit ');
		$pdf->Text($x2,$y, ' : ');
		$pdf->Text($x3,$y, convert_money('',$lrow["total_nilai_pinjaman"],0));
		$pdf->Text($x4,$y, ' ' . $bullet . ' No Rangka ');
		$pdf->Text($x5,$y, ' : ');
		$pdf->Text($x6,$y, $lrow["no_rangka"]);
		
		$y+=10;
		$pdf->Text($x1,$y, ' ' . $bullet . ' Merek ');
		$pdf->Text($x2,$y, ' : ');
		$pdf->Text($x3,$y, $lrow["nm_merek"]);
		$pdf->Text($x4,$y, ' ' . $bullet . ' No Mesin ');
		$pdf->Text($x5,$y, ' : ');
		$pdf->Text($x6,$y, $lrow["no_mesin"]);
			
		$y+=10;
		$pdf->Text($x1,$y, ' ' . $bullet . ' Type ');
		$pdf->Text($x2,$y, ' : ');
		$pdf->Text($x3,$y, $lrow["nm_tipe"]);
		$pdf->Text($x4,$y, ' ' . $bullet . ' Tahun ');
		$pdf->Text($x5,$y, ' : ');
		$pdf->Text($x6,$y, $lrow["tahun"]);

		$y+=10;
		$pdf->Text($x1,$y, ' ' . $bullet . ' Nama STNK & BPKB ');
		$pdf->Text($x2,$y, ' : ');
		$pdf->Text($x3,$y, $lrow["nm_bpkb"]);
		$pdf->Text($x4,$y, ' ' . $bullet . ' Warna ');
		$pdf->Text($x5,$y, ' : ');
		$pdf->Text($x6,$y, $lrow["warna"]);
	} else {
		$y+=10;
		$pdf->Text($x1,$y, ' ' . $bullet . ' Merk ');
		$pdf->Text($x2,$y, ' : ');
		$pdf->Text($x3,$y, $lrow["nm_merek"]);
		$pdf->Text($x4,$y, ' ' . $bullet . ' No Rangka ');
		$pdf->Text($x5,$y, ' : ');
		$pdf->Text($x6,$y, $lrow["no_rangka"]);
		
		$y+=10;
		$pdf->Text($x1,$y, ' ' . $bullet . ' Type ');
		$pdf->Text($x2,$y, ' : ');
		$pdf->Text($x3,$y, $lrow["nm_tipe"]);
		$pdf->Text($x4,$y, ' ' . $bullet . ' No Mesin ');
		$pdf->Text($x5,$y, ' : ');
		$pdf->Text($x6,$y, $lrow["no_mesin"]);
			
		$y+=10;
		$pdf->Text($x1,$y, ' ' . $bullet . ' Nama STNK & BPKB ');
		$pdf->Text($x2,$y, ' : ');
		$pdf->Text($x3,$y, $lrow["nm_bpkb"]);
		$pdf->Text($x4,$y, ' ' . $bullet . ' Tahun / Warna ');
		$pdf->Text($x5,$y, ' : ');
		$pdf->Text($x6,$y, $lrow["tahun"].' / '.$lrow["warna"]);
	}
	
	if ($lrow['status_barang'] == 'Bekas'){
		$pdf->Rect($x,$y+20,568,75,'');
	} else {
		$pdf->Rect($x,$y+20,568,50,'');
	}
	$y+=30;
	$pdf->SetFont("Arial", "BU", 9);
	if ($lrow['status_barang'] == 'Baru') {
		$pdf->Text($x,$y, ' RINCIAN PELUNASAN KE DEALER / SHOWROOM ');
	} else {
		$pdf->Text($x,$y, ' RINCIAN PINJAMAN ');
	}
	$pdf->SetFont("Arial", "", 9);
	
	if ($lrow['status_barang'] == 'Baru') {
		$y+=12;
		$pdf->Text($x1,$y, ' ' . $bullet . ' Harga Unit ');
		$pdf->Text($x2+100,$y, ' : ');
		$pdf->RightAlignText($x3+100, $y, convert_money('',$lrow["total_nilai_pinjaman"],0), 70);
		
		$y+=10;
		$total_dp=$lrow["nilai_dp"];
		if($lrow["kategori"]=='R4'){
			if($lrow["addm_addb"]=='M')$total_dp+=($lrow["angsuran_bulan"]);
		}
		$total_dp+=($lrow["biaya_admin"]+$lrow["biaya_polis"]);
		$pdf->Text($x1,$y, ' ' . $bullet . ' TDP ');
		$pdf->Text($x2+100,$y, ' : ');
		$pdf->Text($x3+100,$y, '______________ -');
		$pdf->RightAlignText($x3+100, $y, convert_money('',$total_dp,0), 70);
		
		$y+=12;
		$pdf->Text($x1,$y, ' ' . $bullet . ' Pelunasan ke Dealer / Showroom ');
		$pdf->Text($x2+100,$y, ' : ');
		$pdf->SetFont("Arial", "B", 9);
		$pdf->Text($x3+100,$y, ' ______________ ');
		$pdf->RightAlignText($x3+100, $y, convert_money('',$lrow["nilai_ap_customer"],0), 70);
	} elseif($lrow['status_barang'] == 'Datun') {
		$y+=12;
		$pdf->Text($x1,$y, ' ' . $bullet . ' Nominal Pinjaman ');
		$pdf->Text($x2+100,$y, ' : ');
		$pdf->RightAlignText($x3+100, $y, convert_money('',$lrow["nilai_ap_customer"],0), 70);
		
		$y+=10;
		$pdf->Text($x1,$y, ' ' . $bullet . ' Pinjaman Asuransi + Admin ');
		$pdf->Text($x2+100,$y, ' : ');
		$pdf->Text($x3+100,$y, '______________ +');
		$pdf->RightAlignText($x3+100, $y, convert_money('',$lrow['biaya_admin']+$lrow['nilai_asuransi'],0), 70);
		
		$y+=12;
		$pdf->Text($x1,$y, ' ' . $bullet . ' Total Pinjaman ');
		$pdf->Text($x2+100,$y, ' : ');
		$pdf->SetFont("Arial", "B", 9);
		$pdf->Text($x3+100,$y, ' ______________ ');
		$pdf->RightAlignText($x3+100, $y, convert_money('',$lrow["nilai_ap_customer"]+$lrow['biaya_admin']+$lrow['nilai_asuransi'],0), 70);
	} else {
		$y+=12;
		$pdf->Text($x1,$y, ' ' . $bullet . ' Harga Unit ');
		$pdf->Text($x2+100,$y, ' : ');
		$pdf->RightAlignText($x3+100, $y, convert_money('',$lrow["total_nilai_pinjaman"],0), 70);
		
		$y+=10;
		$total_dp=$lrow["nilai_dp"];
		if($lrow["kategori"]=='R4'){
			if($lrow["addm_addb"]=='M')$total_dp+=($lrow["angsuran_bulan"]);
		}
		$total_dp+=($lrow["biaya_admin"]+$lrow["biaya_polis"]);
		$pdf->Text($x1,$y, ' ' . $bullet . ' DP ');
		$pdf->Text($x2+100,$y, ' : ');
		$pdf->Text($x3+100,$y, '______________ -');
		$pdf->RightAlignText($x3+100, $y, convert_money('',$total_dp,0), 70);

		$y+=12;
		$pdf->Text($x1,$y, ' ' . $bullet . ' Pinjaman ');
		$pdf->Text($x2+100,$y, ' : ');
		$pdf->RightAlignText($x3+100, $y, convert_money('',$lrow["total_nilai_pinjaman"],0), 70);

		$y+=10;
		$pdf->Text($x1,$y, ' ' . $bullet . ' Premi Asuransi ');
		$pdf->Text($x2+100,$y, ' : ');
		$pdf->Text($x3+100,$y, '______________ +');
		$pdf->RightAlignText($x3+100, $y, convert_money('',$total_dp,0), 70);
		
		$y+=12;
		$pdf->Text($x1,$y, ' ' . $bullet . ' Total Pinjaman ');
		$pdf->Text($x2+100,$y, ' : ');
		$pdf->SetFont("Arial", "B", 9);
		$pdf->Text($x3+100,$y, ' ______________ ');
		$pdf->RightAlignText($x3+100, $y, convert_money('',$lrow["nilai_ap_customer"],0), 70);
	}
	
	$pdf->SetFont("Arial", "", 9);
	if ($lrow['status_barang'] == 'Bekas'){
		$pdf->Rect($x,$y+20,568,90,'');
	} else {
		$pdf->Rect($x,$y+20,568,155,'');
	}
	
	$y+=30;
	$pdf->SetFont("Arial", "BU", 9);
	if ($lrow['status_barang'] == 'Baru') {
		$pdf->Text($x,$y, ' SYARAT PENCAIRAN FASILITAS PEMBIAYAAN ');
		$pdf->SetFont("Arial", "", 9);
		$y+=10;
		$pdf->Text($x,$y, ' ' . $bullet . ' Dokumen Perjanjian Pembiayaan telah ditanda tangani oleh Debitur. ');
		$y+=10;
		$pdf->Text($x,$y, ' ' . $bullet . ' Semua persyaratan dan dokumen dari Debitur telah lengkap dan valid. ');
		$y+=10;
		$pdf->Text($x,$y, ' ' . $bullet . ' Surat - surat kendaraan harus dicek keabsahannya (untuk kendaraan bekas) ');
		$y+=10;
		$pdf->Text($x,$y, ' ' . $bullet . ' Penagihan wajib melampirkan');
		$y+=10;
		$pdf->Text($x+10,$y, ' 1. Fotocopy Berita Acara Serah Terima Kendaraan ');
		$y+=10;
		$pdf->Text($x+10,$y, ' 2. Gesek Nomor Rangka & Mesin ');
		$y+=10;
		$pdf->Text($x+10,$y, ' 3. Fotocopy Kwitansi DP ');
		$y+=10;
		$pdf->Text($x+10,$y, ' 4. Asli Kwitansi Pelunasan ');
		$y+=10;
		$pdf->Text($x+10,$y, ' 5. Surat Pernyataan BPKB / Surat Pernyataan Bersama ');
	} elseif($lrow['status_barang'] == 'Datun') {
		$pdf->Text($x,$y, ' SYARAT PENCAIRAN FASILITAS PEMBIAYAAN ');
		$pdf->SetFont("Arial", "", 9);
		$y+=10;
		$pdf->Text($x,$y, ' ' . $bullet . ' Dokumen Perjanjian Pembiayaan telah ditanda tangani oleh Debitur. ');
		$y+=10;
		$pdf->Text($x,$y, ' ' . $bullet . ' Semua persyaratan dan dokumen dari Debitur telah lengkap dan valid. ');
		$y+=10;
		$pdf->Text($x,$y, ' ' . $bullet . ' Surat - surat kendaraan harus dicek keabsahannya ');
		$y+=10;
		$pdf->Text($x,$y, ' ' . $bullet . ' Debitur wajib melampirkan dokumen sebagai berikut:');
		$y+=10;
		$pdf->Text($x+10,$y, ' 1. Asli BPKB ');
		$y+=10;
		$pdf->Text($x+10,$y, ' 2. Asli Faktur BPKB ');
		$y+=10;
		$pdf->Text($x+10,$y, ' 3. Kwitansi Kosong 3 (tiga) Lembar (1 (satu) lembar bermaterai & tanda tangan a/n BPKB) ');
		$y+=10;
		$pdf->Text($x+10,$y, ' 4. Fotocopy KTP a/n BPKB ');
		$y+=10;
		$pdf->Text($x+10,$y, ' 5. Nomor Rekening Penerima Dana (Debitur / keluarga yang ditunjuk debitur) ');
	} else {
		$pdf->Text($x,$y, ' SYARAT SERAH TERIMA KENDARAAN ');
		$pdf->SetFont("Arial", "", 9);
		$y+=10;
		$pdf->Text($x,$y, ' ' . $bullet . ' Dokumen Perjanjian Pembiayaan telah ditanda tangani oleh Debitur. ');
		$y+=10;
		$pdf->Text($x,$y, ' ' . $bullet . ' Semua persyaratan dan dokumen dari Debitur telah lengkap dan valid. ');
		$y+=10;
		$pdf->Text($x,$y, ' ' . $bullet . ' Melampirkan kwitansi pembayaran Uang Muka yang diterima oleh kasir PT Capella Multidana ');
	}

	$y+=15;
	$pdf->SetFont("Arial", "BU", 9);
	$pdf->Text($x,$y, ' KETENTUAN PERSETUJUAN FASILITAS PEMBIAYAAN ');
	$pdf->SetFont("Arial", "", 8);
	$y+=10;
	$pdf->Text($x,$y, ' Surat Persetujuan Pembiayaan ini berlaku selama 30 (tiga puluh) hari sejak tanggal dikeluarkan. Sebelum seluruh Dokumen Perjanjian Pembiayaan dan ');
	$y+=10;
	$pdf->Text($x,$y, ' Berita Acara Serah Terima Kendaraan ditanda-tangani oleh Debitur serta belum diterima oleh PT. Capella Multidana maka pembiayaan ini belum mengikat ');
	$y+=10;
	$pdf->Text($x,$y, ' PT. Capella Multidana. ');

	if ($lrow['status_barang'] == 'Datun') {
		$pdf->Rect($x,$y+20,568,80,'');
	} elseif($lrow['status_barang'] == 'Baru') {
		$pdf->Rect($x,$y+20,568,120,'');
	} else{
		$pdf->Rect($x,$y+20,568,100,'');
	}
	$y+=30;
	$pdf->SetFont("Arial", "BU", 9);
	if ($lrow['status_barang'] == 'Datun'){
		$pdf->Text($x,$y, ' CATATAN ');
		$pdf->SetFont("Arial", "", 9);
		$y+=12;
		$pdf->Text($x,$y, ' Angsuran / Bulan ');
		$pdf->Text($x+100,$y, ' : ');
		$pdf->Text($x+110,$y, convert_money('Rp. ',$lrow["angsuran_bulan"],0));
		$pdf->Text($x3+150,$y, ' CMO ');
		$pdf->Text($x3+250,$y, ' : ');
		$pdf->Text($x3+260,$y, $nm_cmo);
		$y+=10;
		$pdf->Text($x,$y, ' Tanggal JTO Ke-I ');
		$pdf->Text($x+100,$y, ' : ');
		$pdf->Text($x+110,$y, $jto_pertama);
		$pdf->Text($x3+150,$y, ' Salesman ');
		$pdf->Text($x3+250,$y, ' : ');
		$pdf->Text($x3+260,$y, $nm_sales);
		$y+=10;
		$pdf->Text($x,$y, ' Tenor Kredit ');
		$pdf->Text($x+100,$y, ' : ');
		$pdf->Text($x+110,$y, $lrow['lama_pinjaman']);
		$pdf->Text($x3+150,$y, ' SPV ');
		$pdf->Text($x3+250,$y, ' : ');
		$pdf->Text($x3+260,$y, $nm_spv);
		$y+=10;
		$pdf->Text($x,$y, ' Jenis Pembayaran ');
		$pdf->Text($x+100,$y, ' : ');
		$pdf->Text($x+110,$y, strtoupper($lrow['skema_pembiayaan']));
		$pdf->Text($x3+150,$y, ' Kacab ');
		$pdf->Text($x3+250,$y, ' : ');
		$pdf->Text($x3+260,$y, $nm_kacab);
		$y+=10;
		$lrow["jenis_asuransi"] = '';
		if ($lrow['all_risk_dari_tahun']) {
			$lrow["jenis_asuransi"] .= $lrow['all_risk_dari_tahun'];
			if ($lrow['all_risk_sampai_tahun'] != $lrow['all_risk_dari_tahun']) {
				$lrow["jenis_asuransi"] .= ' - ' . $lrow['all_risk_sampai_tahun'];
			}
			$lrow["jenis_asuransi"] .= ' ALL RISK';
		}

		if ($lrow['tlo_dari_tahun']) {
			if ($lrow["jenis_asuransi"]) {
				$lrow["jenis_asuransi"] .= ' + ';
			}
			$lrow["jenis_asuransi"] .= $lrow['tlo_dari_tahun'];
			if ($lrow['tlo_sampai_tahun'] != $lrow['tlo_dari_tahun']) {
				$lrow["jenis_asuransi"] .= ' - ' . $lrow['tlo_sampai_tahun'];
			}
			$lrow["jenis_asuransi"] .= ' TLO';
		}
		$pdf->Text($x,$y, ' Jenis Asuransi ');
		$pdf->Text($x+100,$y, ' : ');
		$pdf->Text($x+110,$y, $lrow['jenis_asuransi']. $nm_perusahaan_asuransi);
		$pdf->Text($x3+150,$y, ' Program Kredit ');
		$pdf->Text($x3+250,$y, ' : ');
		// $pdf->Text($x3+260,$y, 'REGULER');
		$y+=10;
		$pembiayaan = 'ADDB';
		if($lrow["addm_addb"]=='M')$pembiayaan='ADDM';
		$pdf->Text($x,$y, ' Jenis Pembiayaan ');
		$pdf->Text($x+100,$y, ' : ');
		$pdf->Text($x+110,$y, $pembiayaan);
	} elseif($lrow['status_barang'] == 'Baru'){
		$pdf->Text($x,$y, ' RINCIAN TDP ');
		$pdf->Text($x3+150,$y, ' CATATAN ');
		$pdf->SetFont("Arial", "", 9);
		$y+=12;
		$pdf->Text($x,$y, ' DP Murni (' . ceil($lrow['nilai_dp']/$lrow['total_nilai_pinjaman']*100) . '%)');
		$pdf->Text($x+100,$y, ' : ');
		$pdf->RightAlignText($x+110, $y, convert_money('',$lrow['nilai_dp'],0), 65);
		$pdf->Text($x3+150,$y, ' Program Kredit ');
		$pdf->Text($x3+250,$y, ' : ');
		// $pdf->Text($x3+260,$y, 'REGULER ');
		$y+=10;
		$angsuran = 0;
		if($lrow["addm_addb"]=='M')$angsuran+=($lrow["angsuran_bulan"]);
		$pdf->Text($x,$y, ' Angsuran / Bulan ');
		$pdf->Text($x+100,$y, ' : ');
		$pdf->RightAlignText($x+110, $y, convert_money('',$angsuran,0), 65);
		$pdf->Text($x3+150,$y, ' Tanggal JTO Ke-I ');
		$pdf->Text($x3+250,$y, ' : ');
		$pdf->Text($x3+260,$y, $jto_pertama);
		$y+=10;
		$pdf->Text($x,$y, ' Biaya Admin ');
		$pdf->Text($x+100,$y, ' : ');
		$pdf->RightAlignText($x+110, $y, convert_money('',$lrow['biaya_admin'],0), 65);
		$pdf->Text($x3+150,$y, ' Tenor Kredit ');
		$pdf->Text($x3+250,$y, ' : ');
		$pdf->Text($x3+260,$y, $lrow['lama_pinjaman']);
		$y+=10;
		$pdf->Text($x,$y, ' Asuransi ');
		$pdf->Text($x+100,$y, ' : ');
		$pdf->RightAlignText($x+110, $y, convert_money('',$lrow['biaya_polis'],0), 65);
		$pdf->Text($x3+150,$y, ' Jenis Pembayaran ');
		$pdf->Text($x3+250,$y, ' : ');
		$pdf->Text($x3+260,$y, strtoupper($lrow['skema_pembiayaan']));
		$y+=10;
		$lrow["jenis_asuransi"] = '';
		if ($lrow['all_risk_dari_tahun']) {
			$lrow["jenis_asuransi"] .= $lrow['all_risk_dari_tahun'];
			if ($lrow['all_risk_sampai_tahun'] != $lrow['all_risk_dari_tahun']) {
				$lrow["jenis_asuransi"] .= ' - ' . $lrow['all_risk_sampai_tahun'];
			}
			$lrow["jenis_asuransi"] .= ' ALL RISK';
		}

		if ($lrow['tlo_dari_tahun']) {
			if ($lrow["jenis_asuransi"]) {
				$lrow["jenis_asuransi"] .= ' + ';
			}
			$lrow["jenis_asuransi"] .= $lrow['tlo_dari_tahun'];
			if ($lrow['tlo_sampai_tahun'] != $lrow['tlo_dari_tahun']) {
				$lrow["jenis_asuransi"] .= ' - ' . $lrow['tlo_sampai_tahun'];
			}
			$lrow["jenis_asuransi"] .= ' TLO';
		}
		$pdf->Text($x,$y, ' Provisi ');
		$pdf->Text($x+100,$y, ' : ');
		$pdf->Text($x+110,$y, '______________ +');
		$pdf->RightAlignText($x+110, $y, convert_money('',$lrow['biaya_provisi'],0), 65);
		$pdf->Text($x3+150,$y, ' Jenis Asuransi ');
		$pdf->Text($x3+250,$y, ' : ');
		$pdf->Text($x3+260,$y, $lrow['jenis_asuransi']. $nm_perusahaan_asuransi);
		$y+=10;
		$pembiayaan = 'ADDB';
		if($lrow["addm_addb"]=='M')$pembiayaan='ADDM';
		$pdf->Text($x,$y, ' Total ');
		$pdf->Text($x+100,$y, ' : ');
		$pdf->Text($x+110,$y, '______________ ');	
		$pdf->SetFont("Arial", "B", 9);
		$pdf->RightAlignText($x+110, $y, convert_money('',$total_dp,0), 65);
		$pdf->RightAlignText($x+110, $y, '_____________ ', 65);
		$pdf->SetFont("Arial", "", 9);
		$pdf->Text($x3+150,$y, ' Jenis Pembiayaan ');
		$pdf->Text($x3+250,$y, ' : ');
		$pdf->Text($x3+260,$y, $pembiayaan);
		$y+=10;
		$pdf->Text($x3+150,$y, ' CMO ');
		$pdf->Text($x3+250,$y, ' : ');
		$pdf->Text($x3+260,$y, $nm_cmo);
		$y+=10;
		$pdf->Text($x3+150,$y, ' Salesman ');
		$pdf->Text($x3+250,$y, ' : ');
		$pdf->Text($x3+260,$y, $nm_sales);
		$y+=10;
		$pdf->Text($x3+150,$y, ' SPV Sales ');
		$pdf->Text($x3+250,$y, ' : ');
		$pdf->Text($x3+260,$y, $nm_spv);
		$y+=10;
		$pdf->Text($x3+150,$y, ' Kacab Sales ');
		$pdf->Text($x3+250,$y, ' : ');
		$pdf->Text($x3+260,$y, $nm_kacab);
	} else {
		$pdf->Text($x,$y, ' RINCIAN TDP ');
		$pdf->Text($x3+150,$y, ' CATATAN ');
		$pdf->SetFont("Arial", "", 9);
		$y+=12;
		$pdf->Text($x,$y, ' DP Murni (' . ceil($lrow['nilai_dp']/$lrow['total_nilai_pinjaman']*100) . '%)');
		$pdf->Text($x+100,$y, ' : ');
		// $pdf->RightAlignText($x+110, $y, convert_money('',$total_dp['nilai_dp'],0), 65);
		$pdf->RightAlignText($x+110, $y, convert_money('',$total_dp,0), 65);
		$pdf->Text($x3+150,$y, ' Program Kredit ');
		$pdf->Text($x3+250,$y, ' : ');
		// $pdf->Text($x3+260,$y, 'REGULER ');
		$y+=10;
		$angsuran = 0;
		if($lrow["addm_addb"]=='M')$angsuran+=($lrow["angsuran_bulan"]);
		$pdf->Text($x,$y, ' Angsuran / Bulan ');
		$pdf->Text($x+100,$y, ' : ');
		// $pdf->RightAlignText($x+110, $y, convert_money('',$angsuran,0), 65);
		$pdf->RightAlignText($x+110, $y, '0', 65);
		$pdf->Text($x3+150,$y, ' Tanggal JTO Ke-I ');
		$pdf->Text($x3+250,$y, ' : ');
		$pdf->Text($x3+260,$y, $jto_pertama);
		$y+=10;
		$pdf->Text($x,$y, ' Biaya Admin ');
		$pdf->Text($x+100,$y, ' : ');
		// $pdf->RightAlignText($x+110, $y, convert_money('',$lrow['biaya_admin'],0), 65);
		$pdf->RightAlignText($x+110, $y, convert_money('','0',0), 65);
		$pdf->Text($x3+150,$y, ' Tenor Kredit ');
		$pdf->Text($x3+250,$y, ' : ');
		$pdf->Text($x3+260,$y, $lrow['lama_pinjaman']);
		$y+=10;
		$pdf->Text($x,$y, ' Asuransi ');
		$pdf->Text($x+100,$y, ' : ');
		// $pdf->RightAlignText($x+110, $y, convert_money('',$lrow['biaya_polis'],0), 65);
		$pdf->RightAlignText($x+110, $y, convert_money('','0',0), 65);
		$pdf->Text($x3+150,$y, ' Jenis Pembayaran ');
		$pdf->Text($x3+250,$y, ' : ');
		$pdf->Text($x3+260,$y, strtoupper($lrow['skema_pembiayaan']));
		$y+=10;
		$lrow["jenis_asuransi"] = '';
		if ($lrow['all_risk_dari_tahun']) {
			$lrow["jenis_asuransi"] .= $lrow['all_risk_dari_tahun'];
			if ($lrow['all_risk_sampai_tahun'] != $lrow['all_risk_dari_tahun']) {
				$lrow["jenis_asuransi"] .= ' - ' . $lrow['all_risk_sampai_tahun'];
			}
			$lrow["jenis_asuransi"] .= ' ALL RISK';
		}

		if ($lrow['tlo_dari_tahun']) {
			if ($lrow["jenis_asuransi"]) {
				$lrow["jenis_asuransi"] .= ' + ';
			}
			$lrow["jenis_asuransi"] .= $lrow['tlo_dari_tahun'];
			if ($lrow['tlo_sampai_tahun'] != $lrow['tlo_dari_tahun']) {
				$lrow["jenis_asuransi"] .= ' - ' . $lrow['tlo_sampai_tahun'];
			}
			$lrow["jenis_asuransi"] .= ' TLO';
		}
		$pdf->Text($x,$y, ' Provisi ');
		$pdf->Text($x+100,$y, ' : ');
		$pdf->Text($x+110,$y, '______________ +');
		// $pdf->RightAlignText($x+110, $y, convert_money('',$lrow['biaya_provisi'],0), 65);
		$pdf->RightAlignText($x+110, $y, convert_money('','0',0), 65);
		$pdf->Text($x3+150,$y, ' Jenis Asuransi ');
		$pdf->Text($x3+250,$y, ' : ');
		$pdf->Text($x3+260,$y, $lrow['jenis_asuransi']. $nm_perusahaan_asuransi);
		$y+=10;
		$pembiayaan = 'ADDB';
		if($lrow["addm_addb"]=='M')$pembiayaan='ADDM';
		$pdf->Text($x,$y, ' Total ');
		$pdf->Text($x+100,$y, ' : ');
		$pdf->Text($x+110,$y, '______________ ');	
		$pdf->SetFont("Arial", "B", 9);
		$pdf->RightAlignText($x+110, $y, convert_money('',$total_dp,0), 65);
		$pdf->RightAlignText($x+110, $y, '_____________ ', 65);
		$pdf->SetFont("Arial", "", 9);
		$pdf->Text($x3+150,$y, ' Jenis Pembiayaan ');
		$pdf->Text($x3+250,$y, ' : ');
		$pdf->Text($x3+260,$y, $pembiayaan);
		$y+=10;
		$pdf->Text($x3+150,$y, ' CMO ');
		$pdf->Text($x3+250,$y, ' : ');
		$pdf->Text($x3+260,$y, $nm_cmo);
		$y+=10;
		$pdf->Text($x3+150,$y, ' Salesman ');
		$pdf->Text($x3+250,$y, ' : ');
		$pdf->Text($x3+260,$y, $nm_sales);
	} 
	
	$y+=25;
	$pdf->Text($x,$y, ' Demikian disampaikan atas perhatian dan kerjasamanya, kami ucapkan Terima Kasih ');
	$y+=20;
	if($lrow['status_barang'] == 'Baru'){
		$pdf->Text($x+50,$y, ' Supplier, '); $pdf->Text($x+255,$y, ' Debitur '); $pdf->Text($x+433,$y, ' Hormat Kami, ');
	} else {
		$pdf->Text($x+30,$y, ' Pasangan / Penjamin '); $pdf->Text($x+255,$y, ' Debitur '); $pdf->Text($x+433,$y, ' Hormat Kami, ');
	}
	$y+=60;
	$pdf->Text($x+30,$y, '(_________________)'); $pdf->Text($x+230,$y, '(_________________)'); $pdf->Text($x+420,$y, '(_________________)');	
	$y+=15;
	$pdf->SetFont("Arial", "B", 9);
	if($lrow['status_barang'] == 'Baru'){
		$supplier=strtoupper($lrow["nm_partner"]);
	} else {
		if($lrow['status_pernikahan'] == '1'){
			$supplier=strtoupper($lrow["nm_pasangan"]);
		} else {
			$supplier=strtoupper($lrow["nm_penjamin"]);
		}
	}
	if(strlen($supplier) > 30){
		$supplier = wordwrap($supplier,30,"<split>");
		$tmp_supplier = split("<split>",$supplier);
	} else $tmp_supplier[0] = $supplier;
	$debitur=strtoupper($lrow["nm_customer"]);
	if(strlen($debitur) > 30){
		$debitur = wordwrap($debitur,30,"<split>");
		$tmp_debitur = split("<split>",$debitur);
	} else $tmp_debitur[0] = $debitur;
	// $pdf->Text($x+30,$y, $lrow["nm_partner"]);
	$pdf->Text($x+30,$y, $tmp_supplier[0]);	
	// $pdf->Text($x+235,$y, $lrow['nm_customer']);
	$pdf->Text($x+235,$y, $tmp_debitur[0]);
	$pdf->Text($x+420,$y, strtoupper($lrow["nm_perusahaan"]));
	$y+=10;	
	$pdf->Text($x+30,$y, $tmp_supplier[1]);
	$pdf->Text($x+235,$y, $tmp_debitur[1]);					
	
	// $y=610;

	// $y+=10;
	// $pdf->Line($x-5,$y-20,568,$y-20);
	// $pdf->Text($x,$y, ' Angsuran /Bulan ');
	// $pdf->Text($x+100,$y, ' : '.convert_money("",$lrow["angsuran_bulan"]));
	
	//$nm_karyawan_ca=get_rec("tblkaryawan","nm_depan","npk='".$lrow["fk_karyawan_ca"]."'");
	//$pdf->Text($x3,$y, ' CA ');
	//$pdf->Text($x3+50,$y, ' : '.$nm_karyawan_ca);
	// $y+=10;
	// $pdf->Text($x,$y, ' Lama Angsuran ');
	// $pdf->Text($x+100,$y, ' : '.$lrow["lama_pinjaman"]);
	
	//$nm_karyawan_survey=get_rec("tblkaryawan","nm_depan","npk='".$lrow["fk_karyawan_survey"]."'");
	//$pdf->Text($x3,$y, ' Survey ');
	//$pdf->Text($x3+50,$y, ' : '.$nm_karyawan_survey);
	
	//$y+=10;
	//$pdf->Text($x,$y, ' Jenis Pembayaran ');
	//$pdf->Text($x+100,$y, ' : '.$lrow["jenis_pembayaran"]);
	
	//$nm_karyawan_kredit=get_rec("tblkaryawan","nm_depan","npk='".$lrow["fk_karyawan_kredit"]."'");
	//$pdf->Text($x3,$y, ' Kredit ');
	//$pdf->Text($x3+50,$y, ' : '.$nm_karyawan_kredit);
	
	//$y+=10;
	//$pdf->Text($x,$y, ' Jenis Asuransi ');
	//$pdf->Text($x+100,$y, ' : '.$lrow["jenis_asuransi"]);	
	
	// $y+=10;
	// $pdf->Text($x,$y, ' Sales ');
	// $pdf->Text($x+100,$y, ' : '.$nm_karyawan_sales);
	
	// $y+=10;
	// $pdf->Text($x,$y, ' Keterangan ');
	// $pdf->Text($x+100,$y, ' : '.$lrow["keterangan_dealer"]);
	
	//$nm_karyawan_komite=get_rec("tblkaryawan","nm_depan","npk='".$lrow["fk_karyawan_komite"]."'");
	//$pdf->Text($x3,$y, ' Komite ');
	//$pdf->Text($x3+50,$y, ' : '.$nm_karyawan_komite);

	$pdf->Output();
?>