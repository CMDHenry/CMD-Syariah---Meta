<?php

//error_reporting (E_ALL);  // remove this from Production Environment
require('pdfb/pdfb.php'); // Must include this
require '../requires/db_utility.inc.php';
require '../requires/general.inc.php';
require '../requires/timestamp.inc.php';
require '../requires/numeric.inc.php';
require '../requires/convert.inc.php';

include_once("../requires/connection_db.inc.php");	
include_once("../requires/session.inc.php");	

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
    foreach($data as $row)
    {
        $this->Cell(220,6,($row),0,12,'R');
        $this->Ln();
		
		}
    // Closing line
    //$this->Cell(array_sum($w),0,'','T');
	} 
}


// Create a PDF object and set up the properties

		$pdf = new PDF("p", "pt", "a4");
		$header = array('');
		
		$pdf->AddPage();
		
		$id_edit = $_REQUEST['id_edit'];
		
		if(pg_num_rows(pg_query("select * from data_gadai.tblproduk_gadai where no_sbg='".$id_edit."' "))){
			$l_pk="sbg";
			$tbl="tblproduk_gadai";
		}else{
			$l_pk="sbg";
			$tbl="tblproduk_cicilan";
		}
		
		$query="
		select tblcabang.alamat as alamat_cabang,* from (
			select *,tbllelang.nilai_dp as dp_jual from data_gadai.tbllelang where no_kwitansi='".$id_edit."') as tblpembayaran_cicilan
		left join (
		   select fk_sbg as fk_sbg1,fk_produk,tgl_cair,fk_cabang,fk_cif from tblinventory 
		   left join tblproduk on fk_produk=kd_produk
		)as tblcicilan on fk_sbg=fk_sbg1
		left join data_gadai.tblproduk_cicilan on fk_sbg=no_sbg
		left join (
			select * from viewkendaraan
		)as tbldetail on fk_fatg=no_fatg			
		
		left join (select no_cif,nm_customer,alamat_ktp from tblcustomer)as tblcustomer on no_cif = fk_cif
		left join tblcabang on kd_cabang=tblcicilan.fk_cabang
		";
		//echo($query);
		//echo $query;
		$lrs=pg_query($query);
		$lrow=pg_fetch_array($lrs);
		
		$no_sbg=$lrow["no_sbg"];
		$fk_cabang=$lrow["fk_cabang"];
		$nm_cabang=$lrow["nm_cabang"];
		$tgl_jatuh_tempo=date("d M Y",strtotime($lrow["tgl_jatuh_tempo"]));
		$tgl_bayar=date("d F Y",strtotime($lrow["tgl_bayar"]));
		$tgl_cair=date("d/m/Y",strtotime($lrow["tgl_cair"]));
		$total_nilai_pinjaman=$lrow["total_nilai_pinjaman"];
		$nilai_bayar_denda=$lrow["nilai_bayar_denda"];
		$jenis_transaksi=$lrow["jenis_transaksi"];
		
		$total_pembayaran=$lrow["angka_lelang"];
		if($jenis_transaksi=='Jual Credit'){
			$total_pembayaran=$lrow["dp_jual"];
		}elseif($jenis_transaksi=='Claim Asuransi'){
			$total_pembayaran=$lrow["nilai_claim"];
		}
		$nilai_bayar_angsuran=$lrow["nilai_bayar_angsuran"];
		$titipan=$lrow["titipan"];
		$angsuran_ke=$lrow["angsuran_ke"];
		$nilai_angsuran=$lrow["nilai_angsuran"];
		$nm_perusahaan=$lrow["nm_perusahaan"];
		
		$overdue=(strtotime($lrow["tgl_bayar"])-strtotime($lrow["tgl_jatuh_tempo"]))/(60 * 60 * 24);
		
		if($overdue<0){
			$overdue=0;	
		}
		$nm_customer=$lrow["nm_customer"];						
			
		$y=35;
		$w=210;
		$h=40;
		
		$x1=20;		
		$x2=100;
		$x3=110;
		
		$x4=110;
		$x5=180;
		$x6=190;
		
		$x7=280;
		$x8=320;
		$x9=330;
		
		$x10=400;		
		$x11=460;
		$x12=470;
		
		
		$pdf->Image('logo.jpeg',0,20,180);	
		
		$pdf->setY($y);
		
		$pdf->SetFont("Arial", "", 15);
		$pdf->SetTextColor(255, 0, 0);
		$pdf->SetTextColor(0, 0, 0);
		
		$pdf->SetFont("Arial", "", 9);
		$pdf->Text($x10,$y, 'Nomor PK');
		$pdf->Text($x11,$y, ':');		
		$pdf->Text($x12,$y, ''.$no_sbg);
		$y+=15;	
		$pdf->SetFont("Arial", "I", 9);
		$pdf->SetFont("Arial", "", 9);			
		$pdf->Text($x10,$y, 'ID Customer');
		$pdf->Text($x11,$y, ':');		
		$pdf->Text($x12,$y, ''.$lrow["fk_cif"]);
		$y+=10;		
		$pdf->line(20,$y,555,$y);
		//echo round(str_replace(" ","",microtime(true))*100000000);
		$codeContents='['.$lrow["nm_cabang"].', '.$tgl_bayar.'] - [NoPK:'.$no_sbg.'] - [NoKW:'.$id_edit.'] - [NoPol:'.$lrow["no_polisi"].'] - [Jlh:'.convert_money("Rp",$total_pembayaran).']';
		//echo $codeContents;
		if($_SERVER['DOCUMENT_ROOT']=="D:/Development/Web Project"){
			//$filename="http://192.168.4.10/gadai/test.php";
			$filename="http://192.168.4.10/gadai/qr_code.php?codeContents=".urlencode($codeContents)."";
			//$filename="http://116.90.163.21:81/api/qr_code.php?codeContents=".$id_edit."";
		}else{
			$filename="http://localhost:81/api/qr_code.php?codeContents=".urlencode($codeContents)."";
		}
		$pdf->Image($filename, $x12,$y+5, 80, 80);
		
		$y+=20;						
		
		$pdf->Text($x1,$y, 'Kwitansi Nomor');
		$pdf->Text($x2,$y, ':');
		$pdf->Text($x3,$y, ''.$id_edit);
		$y+=12;
		
		$pdf->Text($x1,$y, 'Terima Dari');
		$pdf->Text($x2,$y, ':');
		$pdf->Text($x3,$y, ''.$lrow['nm_penerima']);
		$y+=12;
		$y+=12;
		
		$pdf->Text($x1,$y, 'Banyaknya Uang');
		$pdf->Text($x2,$y, ':');
		$pdf->Text($x3,$y, '#'.strtolower(convert_terbilang($total_pembayaran)).'#');
		$y+=12;
		
		$pdf->Text($x1,$y, 'Untuk Pembayaran');
		$pdf->Text($x2,$y, ':');		
		$pdf->Text($x4,$y, ($lrow["dp_jual"]>0?"DP":"Penjualan").' 1(Satu) Unit Kendaraan');
		
		$pdf->Text($x7,$y, 'No Polisi');
		$pdf->Text($x8,$y, ':');
		$pdf->Text($x9,$y, ''.$lrow["no_polisi"]);
		$y+=12;

		$pdf->Text($x4,$y, 'No Rangka ');
		$pdf->Text($x5-20,$y, ':');
		$pdf->Text($x6-20,$y, ''.$lrow["no_rangka"]);
		
		$pdf->Text($x7,$y, 'Merek');
		$pdf->Text($x8,$y, ':');
		$pdf->Text($x9,$y, ''.$lrow["nm_merek"]);		
		$y+=12;
			
		$pdf->Text($x4,$y, 'No Mesin ');
		$pdf->Text($x5-20,$y, ':');
		$pdf->Text($x6-20,$y, ''.$lrow["no_mesin"]);
		
		$pdf->Text($x7,$y, 'Tipe');
		$pdf->Text($x8,$y, ':');
		$pdf->Text($x9,$y, ''.$lrow["nm_tipe"]);		
		$y+=12;

		$pdf->Text($x10,$y, $lrow["nm_cabang"].', '.$tgl_bayar);
		$y+=12;
		
		$pdf->Text($x10,$y,'Dibayar Oleh :                 Diterima Oleh :');
		$y+=18;
	
		$pdf->Text($x1,$y,'Jumlah Rp.'.convert_money("",$total_pembayaran));
		$y+=42;
		$y+=16;
		$pdf->Text($x10,$y,'Nama Penyetor :');
		$y+=12;
		$pdf->Text($x10,$y,'No KTP/HP :');
		
		
		$query="
			select * from tbluser
			left join tblkaryawan on npk=fk_karyawan
			where username = '".$_SESSION['username']."'
		";
		//showquery($query);
		$lrs=pg_query($query);
		$lrow=pg_fetch_array($lrs);
				
		$nm_depan=$lrow["nm_depan"];
		
		$y+=12;
								
		//$pdf->BarCode($id_edit,"", 30, $y, 500, 88, 0.4, 0.4, 2, 5, "", "PNG");//harus ada
		
		
		

$pdf->SetFont('Arial','B',12);


$pdf->Output();

?>