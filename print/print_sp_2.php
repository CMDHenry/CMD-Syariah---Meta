<?php
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/timestamp.inc.php';
require '../classes/ezpdf.class.php';
require '../requires/convert.inc.php';
require '../requires/numeric.inc.php';

$id_edit = $_REQUEST['id_edit'];


$lrow=pg_fetch_array(pg_query("select * from tblinventory 
							left join(select no_cif, nm_customer from tblcustomer
						) as tblcustomer on no_cif = fk_cif where fk_sbg='".$id_edit."' "));

//showquery("select * from tblinventory where fk_sbg='".$id_edit."'");
//$lrs=pg_query($query);
//$lrow=pg_fetch_array($lrs);
$nm_customer=$lrow["nm_customer"];
$fk_cif=$lrow["fk_cif"];
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

$y_table=680;
$heigth=$pdf->ez['pageHeight'];
$width=$pdf->ez['pageWidth'];
$pdf->ez['topMargin'] = $heigth-$y_table;
$pdf->ez['bottomMargin']=130;

$all = $pdf->openObject();
$pdf->saveState();

$fontsize= 12;

//Header

$pdf->ezText('SURAT PERINGATAN SP 2',20,array('justification' =>'center'));
$y=$pdf->y;
$y-=60;


$pdf->ezText("",12);
$pdf->ezText("",12);
$pdf->ezText("",12);

$pdf->ezText("Dengan Hormat",12);
$pdf->ezText('Bapak / Ibu '.$nm_customer,12);
$pdf->ezText("",12);
$pdf->ezText("",12);
$pdf->ezText("",12);
$pdf->ezText("",12);

$pdf->ezText("Sesuai dengan Surat Bukti Gadai :",12);
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

$y-=10;



$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');


$y-=20;
$pdf->addText(10, $y, 11.5,'Menurut catatan kami per tanggal '.$tgl_jt.', gadai Bapak / Ibu telah jatuh tempo dan');
$y-=12;
$pdf->addText(10, $y, 11.5,'belum dilakukan pelunasan.');
$y-=19;
$pdf->addText(10, $y, 11.5,'Oleh karena itu, kami mohon agar bapak/ibu segera melakukan pelunasan beserta denda');
$y-=12;
$pdf->addText(10, $y, 11.5,'dan biaya simpan lainnya dalam waktu 7 (tujuh) hari setelah tanggal surat ini.
');
$y-=19;
$pdf->addText(10, $y, 11.5,'Apabila sampai batas waktu yang ditentukan, ternyata Bapak / Ibu belum juga melaku');
$y-=12;
$pdf->addText(10, $y, 11.5,'kan pelunasan, maka dengan sangat menyesal kami akan melakukan lelang terhadap ba');
$y-=12;
$pdf->addText(10, $y, 11.5,'rang jaminan Bapak / Ibu');




$y-=14;
$pdf->addText(10, $y, 11.5,'Jakarta, '.today.',');

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