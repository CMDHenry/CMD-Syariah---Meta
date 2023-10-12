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
			if($i==7 && $row==0);
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
		
			select tblcustomer.nm_customer as customer_cetak,* from data_fa.tblinput_titipan
			left join tblinventory on tblinventory.fk_sbg=data_fa.tblinput_titipan.fk_sbg
			left join tblcustomer on fk_cif = no_cif
			where no_voucher='".$id_edit."'
		
		
	";
	//showquery($query);
	//echo $query;
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	
	$fk_sbg=$lrow["fk_sbg"];
	$nm_penerima=$lrow["customer_cetak"];
	$angka_lelang=$lrow["total"];
	
	$tgl_lelang=date("d/m/Y",strtotime($lrow["tgl_voucher"]));
	//echo $tgl_lelang.'tgl';


		
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
	$x_angka=180;
			

//for($i=0;$i<2;$i++){		
	
	$y+=32;
	
	$pdf->Image('logo gadai mas.jpg',90,20,100);	
	$y+=75;
		
	$pdf->SetFont("Arial", "", 14);
	
	//$nm_perusahaan='GADAI MULIA DKI';
/*	$pad=(45-strlen($nm_perusahaan))/2;
	$spasi3="";
	for($z=0;$z<$pad;$z++){
		$spasi3.=" ";
	}	
	$pdf->Text(24,$y,$spasi3.$nm_perusahaan.$spasi3);
*/	
	$y+=20;
	
	$pdf->SetFont("Arial", "", 10);
	$pdf->Text(32, $y,'BUKTI TRANSAKSI UANG TITIPAN NASABAH');
	$y+=15;
	$pdf->Text(32, $y,'====================================');
	$y+=20;
	
	$pdf->SetFont("Arial", "", 11);
	
/*	$pdf->Text($x,$y, ' No SBG');
	$pdf->Text($x2,$y, ' :');
	$pdf->Text($x3,$y, ''.$fk_sbg);
	$y+=
	12;
*/	

	$pdf->Text($x,$y, ' No SBG');
	$pdf->Text($x2,$y, ' :');
	$pdf->Text($x3,$y, ''.strtoupper($fk_sbg));
	$y+=12;
	
	$pdf->Text($x,$y, ' Nama Nasabah');
	$pdf->Text($x2,$y, ' :');
	$pdf->Text($x3,$y, ''.strtoupper($nm_penerima));
	$y+=12;
	
	$pdf->Text($x,$y, ' Tanggal Bayar');
	$pdf->Text($x2,$y, ' :');
	$pdf->Text($x3,$y, ''.$tgl_lelang);
	$y+=12;
	

	$pdf->Text($x,$y, ' Nominal Bayar');
	$pdf->Text($x2,$y, ' :');
	$pdf->Text($x3,$y, 'Rp');
	$pdf->Text($x_angka,$y, ''.convert_money("",$angka_lelang));
	$y+=12;


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
	
	$pdf->Text(80, $y,'Tanda Tangan Nasabah');
	$y+=14;
	
	$pdf->Text(100, $y,'Terima Kasih');
	$y+=30;
	
	$pdf->Text(80, $y,'Nama Kasir');
	$pdf->Text(135, $y,'   : '.$nm_depan);
	$y+=15;
	
	$pdf->Text(75, $y,' '.$tgl_terkini.' '.date("H:i:s"));
	

		



$pdf->Output();

?>