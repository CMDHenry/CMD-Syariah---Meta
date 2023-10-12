<?php

/*

+-----------------------------------------------------------------+
|     Created by Chirag Mehta - http://chir.ag/projects/pdfb      |
|-----------------------------------------------------------------|
|                      For PDFB Library                           |
+-----------------------------------------------------------------+

*/

error_reporting (E_ALL);  // remove this from Production Environment
require('pdfb/pdfb.php'); // Must include this
require '../requires/db_utility.inc.php';
require '../requires/general.inc.php';
require '../requires/timestamp.inc.php';
require '../requires/numeric.inc.php';
include_once("../requires/connection_db.inc.php");	
include_once("../requires/session.inc.php");	

  // Recommended way to use PDFB Library
  // - create your own PDF class
  // - instantiate it wherever necessary
  // - you can create multiple classes extending from PDFB
  //   for each different report

class PDF extends PDFB
{
	function ImprovedTable($header, $data,$x,$y)
	{
	// Column widths
	//$w = array(40, 35, 40, 45);
	// Header
	/*    for($i=0;$i<count($header);$i++)
	$this->Cell($w[$i],7,$header[$i],1,0,'C');
	$this->Ln();*/
	// Data
		$this->setX($x);
		$this->setY($y-6);
		$i=1;
		foreach($data as $row)
		{
			if(($i==7||$i==8) && $row==0);
			else{
 			$this->Cell(220,6,($row),0,12,'R');
			$this->Ln();
				}
		$i++;
		}
	// Closing line
	//$this->Cell(array_sum($w),0,'','T');
	} 
}


// Create a PDF object and set up the properties

	$pdf = new PDF("p", "pt", "kwitansi");
	$header = array('');
	
	$pdf->AddPage();
	
	$id_edit = $_REQUEST['id_edit'];

	$query="
		select * from(
			select * from data_fa.tblpelunasan_gadai
			where no_pelunasan_gadai='".$id_edit."'
		)as tblpelunasan_gadai
		left join (
			select fk_sbg as no_sbg,fk_cif,fk_rate,tgl_cair,fk_produk,fk_cabang,tgl_jt from tblinventory 
			left join tblproduk on fk_produk=kd_produk			
		)as tblproduk_gadai	on no_sbg=fk_sbg	
		left join (
			select fk_sbg as fk_sbg2,saldo_pokok as total_nilai_pinjaman,rate from data_fa.tblangsuran
			where angsuran_ke=0
		)as tblangsuran on fk_sbg2=no_sbg
		left join (
			select max(angsuran_ke)as lama_pinjaman,fk_sbg as fk_sbg1,max(tgl_jatuh_tempo)as tgl_jatuh_tempo from data_fa.tblangsuran 		
			group by fk_sbg
		)as tblangsuran1 on fk_sbg1=no_sbg	
		left join tblrate on kd_rate=fk_rate
		left join tblcustomer on no_cif = fk_cif
		left join tblproduk on fk_produk=kd_produk
		left join tblcabang on kd_cabang=fk_cabang
		
	";
	//showquery($query);
	//echo $query;
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	
	$no_cif=$lrow["no_cif"];
	//$no_fatg=$lrow["no_fatg"];
	$no_sbg=$lrow["no_sbg"];
	$fk_cabang=$lrow["fk_cabang"];
	$nm_cabang=$lrow["nm_cabang"];
	$nm_perusahaan=$lrow["nm_perusahaan"];	
	$tgl_cair=date("d/m/Y",strtotime($lrow["tgl_cair"]));	
	$tgl_jatuh_tempo=date("d/m/Y",strtotime($lrow["tgl_jt"]));
	//echo $tbl."a";
	//$total_taksir=$lrow["total_taksir"];
	$total_nilai_pinjaman=$lrow["total_nilai_pinjaman"];
	$biaya_denda=$lrow["biaya_denda"];
	$nilai_penyimpanan=$lrow["nilai_penyimpanan"];
	$biaya_penjualan=$lrow["biaya_penjualan"];
	$total_pembayaran=$lrow["total_pembayaran"];
	//$nilai_ap_customer=$lrow["nilai_ap_customer"];
	$nilai_bayar=$lrow["nilai_bayar"];
	$titipan=$lrow["titipan"];
	//$no_id=$lrow["no_id"];
	$nm_customer=$lrow["nm_customer"];
	$lama_pelunasan=$lrow["lama_pelunasan"];
	$lama_jasa_simpan=$lrow["lama_jasa_simpan"];
	$diskon_pelunasan=$lrow["diskon_pelunasan"];
	$tgl_bayar=date("d/m/Y",strtotime($lrow["tgl_bayar"]));

		
	$x=30;
	$y=5;
	$w=210;
	$h=40;
	
	
	$x1=$x+10;
	$x2=150;
	$x3=160;
	$x4=300;
	$x5=400;
	$x6=450;
			

//for($i=0;$i<2;$i++){		
	
	$y+=12;
	
	$pdf->Image('logo gadai mas.jpg',90,1,100);	
	$y+=80;
		
	//$pdf->SetFont("Arial", "", 16);
//	
//	$pad=(25-strlen($nm_perusahaan))/2;
//		$spasi3="";
//		for($z=0;$z<$pad;$z++){
//		$spasi3.=" ";
//		}
//	$pdf->Text(24,$y,$spasi3.$nm_perusahaan.$spasi3);
	$y+=30;
	
	$pdf->SetFont("Arial", "", 14);
	$pdf->Text(32, $y,'BUKTI TRANSAKSI PELUNASAN');
	$y+=15;
	$pdf->Text(32, $y,'===========================');
	$y+=25;
	
	$pdf->SetFont("Arial", "", 11);
	$pdf->Text($x,$y, ' Kode Cabang');
	$pdf->Text($x2,$y, ' :');
	$pdf->Text($x3,$y, ''.$fk_cabang);
	$y+=12;
	
	$pdf->Text($x,$y, ' Nama Cabang');
	$pdf->Text($x2,$y, ' :');
	$pdf->Text($x3,$y, ''.$nm_cabang);
	$y+=20;
	
	$pdf->Text($x,$y, ' No SBG');
	$pdf->Text($x2,$y, ' :');
	$pdf->Text($x3,$y, ''.$no_sbg);
	$y+=12;
	
	$pdf->Text($x,$y, ' Nama');
	$pdf->Text(70,$y, ' : '.$nm_customer);
	$y+=20;
	
	$pdf->Text($x,$y, ' Tanggal Cair');
	$pdf->Text($x2,$y, ' :');
	$pdf->Text($x3,$y, ''.$tgl_cair);
	$y+=12;
	
	
	$pdf->Text($x,$y, ' Tanggal Jatuh Tempo');
	$pdf->Text($x2,$y, ' :');
	$pdf->Text($x3,$y, ''.$tgl_jatuh_tempo);
	
	$y+=12;
	
	
	$data=array($lama_pelunasan." Hari",
	$lama_jasa_simpan." Hari",
	convert_money("",$total_nilai_pinjaman),
	convert_money("",$nilai_penyimpanan),
	convert_money("",$biaya_denda),
	convert_money("",$biaya_penjualan),
	convert_money("",$diskon_pelunasan),
	convert_money("",$titipan),
	convert_money("",$total_pembayaran),
	convert_money("",$nilai_bayar)
	);
	
	//convert_money("",$total_taksir),
	
	$pdf->ImprovedTable($header,$data,$x4,$y);
	
	$pdf->Text($x,$y, ' Jumlah Hari Real');
	$pdf->Text($x2,$y, ' : ');
	$y+=12;
	
	$pdf->Text($x,$y, ' Jml Hari Jasa Simpan');
	$pdf->Text($x2,$y, ' : ');
	$y+=12;
	
/*	$pdf->Text($x,$y, ' Nilai Taksir');
	$pdf->Text($x2,$y, ' :  Rp');
	$y+=12;
*/	
	$pdf->Text($x,$y, ' Nilai Uang Pinjaman');
	$pdf->Text($x2,$y, ' :  Rp');
	$y+=12;
	
	$pdf->Text($x,$y, ' Sewa Modal');
	$pdf->Text($x2,$y, ' :  Rp');
	$y+=12;
	
	$pdf->Text($x,$y, ' Denda');
	$pdf->Text($x2,$y, ' :  Rp');
	$y+=12;
	
	$pdf->Text($x,$y, ' Biaya Penjualan');
	$pdf->Text($x2,$y, ' :  Rp');
	$y+=12;
	
	if($diskon_pelunasan>0){
	$pdf->Text($x,$y, ' Diskon');
	$pdf->Text($x2,$y, ' :  Rp');
	$y+=12;
	}
	
	if($titipan>0){
		$pdf->Text($x,$y, ' Titipan');
		$pdf->Text($x2,$y, ' :  Rp');
		$y+=12;
	}	
	
	
	$pdf->Text($x,$y, ' Total Kewajiban Bayar');
	$pdf->Text($x2,$y, ' :  Rp');
	$y+=12;
	
	$pdf->Text($x,$y, ' Pembulatan');
	$pdf->Text($x2,$y, ' :  Rp');
	$y+=12;

	$pdf->Text($x,$y, ' Tanggal Pembayaran');
	$pdf->Text($x2,$y, ' : '.$tgl_bayar);
	//$y+=75;

	//$pdf->Text(80, $y,'Tanda Tangan Kasir');
	$y+=55;

	$pdf->Text(75, $y,'_____________________');
	$y+=15;
		
	$query="
		select * from tbluser
		left join tblkaryawan on npk=fk_karyawan
		where username = '".$_SESSION['username']."'
	";
	//showquery($query);
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	
	$tgl_terkini=date("d/m/Y",strtotime(get_rec("tblsetting","tgl_sistem")));
	$nm_depan=$lrow["nm_depan"];
	$nm_belakang=$lrow["nm_belakang"];
	
	$pdf->Text(80, $y,'Tanda Tangan Kasir');
	$y+=13;
	
	$pdf->Text(100, $y,'Terima Kasih');
	$y+=13;
	
	$pdf->Text(80, $y,'Nama Kasir');
	$pdf->Text(135, $y,'   : '.$nm_depan);
	$y+=15;
	
	$pdf->Text(75, $y,' '.$tgl_terkini.' '.date("H:i:s"));
		
//}
		



$pdf->Output();

?>