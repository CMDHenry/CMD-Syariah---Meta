<?php

if($_REQUEST["id_edit"]!=""){
	$prefix_url="../";
}else{
	if($_SERVER['HTTP_HOST']=="meta-server"){
		$prefix_url="D:/Development/Web Project/capella/website"; // BUAT META
	}else{
		$prefix_url="/www/erp/website/"; //BUAT DIATAS
	}
}

require $prefix_url.'/requires/config.inc.php';
require $prefix_url.'/requires/authorization.inc.php';
require $prefix_url.'/requires/general.inc.php';
require $prefix_url.'/requires/db_utility.inc.php';
require $prefix_url.'/requires/timestamp.inc.php';
require $prefix_url.'/requires/convert.inc.php';
require $prefix_url.'/requires/numeric.inc.php';
require $prefix_url.'/classes/ezpdf.class.php';
$id_edit = $_REQUEST['id_edit'];

				
$lrow=pg_fetch_array(pg_query("select * from tblinventory 
								left join(select no_cif, nm_customer,alamat_tinggal from tblcustomer
							)as tblcustomer 
							on no_cif = fk_cif where fk_sbg='".$id_edit."' "));

//showquery("select * from tblinventory 
//							left join(select no_cif, nm_customer from tblcustomer
//						) as tblcustomer on no_cif = fk_cif where fk_sbg='".$id_edit."' ");


//$lrs=pg_query($query);
//$lrow=pg_fetch_array($lrs);
if(!$_REQUEST["tgl"])$tgl=convert_date_english(today);
	else $tgl=convert_date_english($_REQUEST["tgl"]);
$nm_customer=$lrow["nm_customer"];
//<em></em>echo $nm_customer.'nama';
$fk_cif=$lrow["no_cif"];
$tgl_jt=date("d/m/Y",strtotime($lrow["tgl_jt"]));
$tgl_cair=date("d/m/Y",strtotime($lrow["tgl_cair"]));
$biaya_admin=$lrow["biaya_admin"];
$nilai_dp=$lrow["nilai_dp"];
$nilai_ap_customer=$lrow["nilai_ap_customer"];
$nilai_jaminan=$lrow["total_nilai_jaminan"];
$addm_addb=$lrow["addm_addb"];
$angsuran_bulan=$lrow["angsuran_bulan"];

$pdf = new Cezpdf('A4');  
$pdf->setLineStyle(1);
$pdf->selectFont('fonts/Helvetica');

$y_table=680;
$heigth=$pdf->ez['pageHeight'];
$width=$pdf->ez['pageWidth'];
$pdf->ez['topMargin'] = $heigth-$y_table;
$pdf->ez['bottomMargin']=130;

$all = $pdf->openObject();
$pdf->saveState();

$fontsize= 12;

//Header

$pdf->ezText('SURAT PERINGATAN',20,array('justification' =>'center'));
$y=$pdf->y;
$y-=60;



$pdf->ezText("",12);
$pdf->ezText("",12);
$pdf->ezText("",12);

$pdf->ezText("Dengan Hormat",12);
$pdf->ezText('Bapak / Ibu ',12);
$pdf->ezText($nm_customer,12);
$pdf->ezText($lrow["alamat_tinggal"],12);
$pdf->ezText("Di tempat",12);
$pdf->ezText("",12);


$pdf->ezText("Sesuai dengan Kontrak :",12);

$y=$pdf->y;
$y-=10;

$pdf->setStrokeColor(0,0,0);


$y-=15;
$x1=10;
$x2=110;
$x3=125;


$pdf->addText($x1, $y, $fontsize,'Nomor');
$pdf->addText($x2, $y, $fontsize,':');
$pdf->addText($x3, $y, $fontsize,$id_edit);

$y-=17;
$pdf->addText($x1, $y, $fontsize,'CIF');
$pdf->addText($x2, $y, $fontsize,':');
$pdf->addText($x3, $y, $fontsize,$fk_cif);
$y-=17;
$pdf->addText($x1, $y, $fontsize,'Tanggal');
$pdf->addText($x2, $y, $fontsize,':');
$pdf->addText($x3, $y, $fontsize,$tgl_cair);

$y-=15;



$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');


$y-=20;
$pdf->addText(10, $y, 11.5,'Menurut catatan kami per tanggal '.$tgl_jt.', pinjaman Bapak/Ibu telah jatuh tempo  ');
$y-=12;
$pdf->addText(10, $y, 11.5,'dan belum dilakukan pembayaran.');
$y-=19;
$pdf->addText(10, $y, 11.5,'Barangkali Bapak / Ibu sedang sibuk atau bepergian sehingga pembayaran tersebut ');
$y-=12;
$pdf->addText(10, $y, 11.5,'tertunda, perlu diketahui bahwa dengan tertundanya pembayaran tersebut akan ');
$y-=12;
$pdf->addText(10, $y, 11.5,'mengakibatkan bertambahnya biaya simpan dan admin.');
$y-=19;
$pdf->addText(10, $y, 11.5,'Namun demikian, bisa terjadi pada saat surat ini diterima, bapak/ibu telah ');
$y-=12;
$pdf->addText(10, $y, 11.5,'melakukan pembayaran, untuk itu kami mohon maaf.');
$y-=19;
$pdf->addText(10, $y, 11.5,'Atas perhatian Bapak / Ibu, kami mengucapkan terimakasih.');

$y-=14;
$pdf->addText(10, $y, 11.5,' '.date("d M Y").',');

$y-=110;
$pdf->addText(455, $y, 11.5,'Sekian,');


$y-=60;
$pdf->addText(447, $y, 11.5,'Terima Kasih');


//content
$pdf->ezSetY($y_table);
//echo $y_table;
	
//end content
$pdf->ezStream();   

?>