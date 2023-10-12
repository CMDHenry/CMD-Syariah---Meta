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
  
}

// Create a PDF object and set up the properties

	$no_cif = $_REQUEST['no_cif'];
	$no_fatg = $_REQUEST['no_fatg'];
	
	$pdf = new PDF("p", "pt", "A4.5");
	$pdf->AddPage();

	$query="
	select * from 
(select no_cif,no_id,nm_customer,jenis_kelamin, tempat_lahir, tgl_lahir, no_hp, alamat_ktp, alamat_tinggal, status_customer, telepon, no_hp, nm_ibu, nm_pasangan,fk_pekerjaan from tblcustomer) as tblcust
	left join tblpekerjaan on kd_pekerjaan=fk_pekerjaan
	where no_cif='".$no_cif."'
	";
	 //echo $query;
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	
	$no_cif=$lrow["no_cif"];
	//echo $lrow["no_cif"]."bbb";
	$no_id=$lrow["no_id"];
	$nm_customer=strtoupper($lrow["nm_customer"]);
	$jenis_kelamin=$lrow["jenis_kelamin"];
	$tempat_lahir=$lrow["tempat_lahir"];
	//echo $lrow["tgl_lahir"]."aaa";
	
	$date=split(" ",$lrow["tgl_lahir"]);
    $date1=split("-",$date[0]);
	if($lrow["tgl_lahir"]){
		$tgl_lahir=$date1[2]."/".$date1[1]."/".$date1[0];
	}else $tgl_lahir="-";
	$no_hp=$lrow["no_hp"];
	$alamat_ktp=$lrow["alamat_ktp"];
	$alamat_tinggal=$lrow["alamat_tinggal"];
	$status_customer=$lrow["status_customer"];
	$telepon=$lrow["telepon"];
	$no_hp=$lrow["no_hp"];
	$nm_ibu=$lrow["nm_ibu"];
	$nm_pasangan=$lrow["nm_pasangan"];
	$nm_pekerjaan=$lrow["nm_pekerjaan"];
	$no_cif=$lrow["no_cif"];
	
	// Move to the right
	if($status_customer=='1'){
		$status='Menikah';
	} else{
		$status='Belum Menikah';
	}
	
	if($jenis_kelamin=='1'){
		$jk='L';
	} else{
		$jk='P';
	}
	
	$x=40;
	$y=25;
		
	$x1=$x+10;
	$x2=110;
	$x3=250;
	$x4=315;
	$x5=400;
	$x6=450;
			
	$pdf->setY($y);
	$pdf->Cell(540,395,"",1);
	$y+=12;
		
	$pdf->SetFont("Arial", "B", 12);
	
	$pdf->Text(180, $y,'FORMULIR APLIKASI TRANSAKSI GADAI');
	$y+=12;
	
	$pdf->SetFont("Arial", "B", 8);
	
	$pdf->Text($x,$y, ' Nama Nasabah');
	$pdf->Text($x2,$y, ' : '.$nm_customer);
	//$pdf->Text($x3,$y, ' Jenis Kelamin');
	$pdf->Text(370,$y, '['.$jk.']');
	$pdf->Text($x5,$y, ' Status');
	$pdf->Text($x6,$y, ' : '.$status);
	$y+=12;
	
	//echo $lrow["status_customer"]."aaa";
	
	$pdf->Text($x,$y, ' Tempat/Tgl Lahir');
	$pdf->Text($x2,$y, ' : '.$tempat_lahir. ','.$tgl_lahir);
	$pdf->Text($x3,$y, ' No KTP/SIM');
	$pdf->Text($x4,$y, ' : '.$no_id);
	//$pdf->Text($x5,$y, ' Masa Berlaku');
	//$pdf->Text($x6,$y, ' : '.$no_id);
	$y+=12;
	
	$pdf->Text($x,$y, ' Alamat KTP');
	$pdf->Text($x2,$y, ' : '.$alamat_ktp);
	$y+=12;
	
	$pdf->Text($x,$y, ' Alamat Domisili');
	$pdf->Text($x2,$y, ' : '.$alamat_tinggal);
	$y+=12;
	
	$pdf->Text($x,$y, ' No Telp');
	$pdf->Text($x2,$y, ' : '.$telepon);
	$pdf->Text($x3,$y, ' No Hp');
	$pdf->Text($x4,$y, ' : '.$no_hp);
	$y+=12;
	
	$pdf->Text($x,$y, ' Nama Ibu');
	$pdf->Text($x2,$y, ' : '.$nm_ibu);
	$pdf->Text($x3,$y, ' Nama Pasangan');
	$pdf->Text($x4,$y, ' : '.$nm_pasangan);
	$y+=12;
	
	$pdf->Text($x,$y, ' Pekerjaan');
	$pdf->Text($x2,$y, ' : '.$nm_pekerjaan);
	$y+=12;
	
//-----------------------------------------------------//
	$pdf->SetFont("Arial", "BI", 8);
	$pdf->Text(40,$y, '*Diisi oleh petugas');
	$y+=2;
	
	$pdf->SetFont("Arial", "B", 8);
	$pdf->setY($y);
	
	$x=40;
	$pdf->setX($x);

	$pdf->Cell(170,35,"",1);
	$y+=10;
	
	
	$pdf->Text($x,$y, ' No CIF');
	$pdf->Text($x2,$y, ' : '.$no_cif);
	
	$pdf->Text(380,$y, ' Tanggal Taksiran : ');
	$y+=15;
	
	$pdf->Text($x,$y, ' No SBG');
	$pdf->Text($x2,$y, ' : ');
	
	$pdf->SetFont("Arial", "B", 10);
	$pdf->Text(230,$y, ' No FATG');
	$pdf->Text(290,$y, ':  '.$no_fatg);
	$y+=15;
	
	
	$x=230;
	$pdf->SetFont("Arial", "B", 8);
	$pdf->setX($x);

	$pdf->Cell(140,35,"",1);
	
	$x=400;
	$pdf->setX($x);

	//$pdf->Cell(150,59,"",1);
	$y+=5;
		
	//echo strlen($nm_customer);
	$pad=(38-strlen($nm_customer))/2;
	$spasi="";
	for($i=0;$i<$pad;$i++){
		$spasi.=" ";
	}
	
	//$pdf->Text(410,$y,$spasi.$nm_customer.$spasi);
	$y+=1;
	
	//$pdf->Text(410,$y, ' _____________________________');
	$y+=10;
	
	
	$pdf->SetFont("Arial", "BI", 8);
	$pdf->Text(40,$y, '*Dengan ini saya menyetujui dan tunduk pada ketentuan yang berlaku');	
	//$pdf->Text(420,$y, ' Tanda Tangan & Nama Jelas ');
	$y+=15;
	
	$pdf->SetFont("Arial", "B", 8);
	$pdf->Text(40,$y, 'STLE[ Antam / Perhiasan 24K ]');
	$pdf->Text(150,$y, ' : ');
	//$pdf->Text(400,$y, 'Tanggal Taksiran');
	//$pdf->Text(460,$y, ' : ');
	$y+=10;
	
	//-----------------------------------------------------//
	
	$pdf->setY($y);
	
	$x=40;
	$pdf->setX($x);

	$pdf->Cell(510,20,"",1);
	$y+=10;
	
	$pdf->Text(215,$y, ' KETERANGAN TAKSIRAN BARANG JAMINAN');
	$y+=10;
	
	//-----------------------------------------------------//
	
	$pdf->setY($y);
	
	$x=40;
	$pdf->setX($x);

	$pdf->Cell(510,70,"",1);
	$y+=10;
	
	$pdf->Text($x,$y, 'Taksiran 1');
	//$pdf->Text(440,$y, 'Taksiran 2');
	
	$x=440;
	$pdf->setX($x);

	//$pdf->Cell(110,100,"",1);
	$y+=60;
	
	//-----------------------------------------------------//
	
	$pdf->setY($y);
	
	$x=40;
	$pdf->setX($x);

	$pdf->Cell(160,100,"",1);
	$pdf->Cell(180,100,"",1);
	$pdf->Cell(170,100,"",1);
	//$pdf->Cell(110,50,"",1);
	$y+=10;
	
	$pdf->Text(100,$y, 'Penaksir, ');
	$pdf->Text(250,$y, 'Kepala Cabang/Unit, ');
	$pdf->Text(445,$y, 'Pemohon, ');
	//$pdf->Text(450,$y, 'Penaksir Kantor Pusat, ');
	$y+=60;
	
	$pdf->Text(80,$y, 'Ttd & Nama jelas');
	$pdf->Text(250,$y, 'Ttd & Nama jelas');
	$pdf->Text(430,$y, 'Ttd & Nama jelas');
	
	$y+=10;
	
	$pdf->Text(40,$y, 'NT');
	$pdf->Text(55,$y, ':');
	$pdf->Text(65,$y, 'Rp ................................................... ');
	$pdf->Text(210,$y, 'NT');
	$pdf->Text(225,$y, ':');
	$pdf->Text(235,$y, 'Rp .................................................. ');
	//$pdf->Text(400,$y, 'NT');
	//$pdf->Text(415,$y, ':');
	$pdf->Text(400,$y, $spasi.$nm_customer.$spasi);
	//$pdf->Text(450,$y, 'NT');
	//$pdf->Text(465,$y, ':');
	//$pdf->Text(475,$y, 'Rp ............................ ');
	$y+=15;
	
	$pdf->Text(40,$y, 'NP');
	$pdf->Text(55,$y, ':');
	$pdf->Text(65,$y, 'Rp .................................................. ');
	$pdf->Text(210,$y, 'NP');
	$pdf->Text(225,$y, ':');
	$pdf->Text(235,$y, 'Rp .................................................. ');
	//$pdf->Text(400,$y, 'NP');
	//$pdf->Text(415,$y, ':');
	$pdf->Text(380,$y, 'Tanggal .................................................... ');
	//$pdf->Text(450,$y, 'NP');
	//$pdf->Text(465,$y, ':');
	//$pdf->Text(475,$y, 'Rp ............................ ');
	$y+=30;

	$pdf->SetFont('Arial','B',12);

	$pdf->Output();

?>