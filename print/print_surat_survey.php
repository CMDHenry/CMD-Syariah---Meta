<?php
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/timestamp.inc.php';
//require 'requires/report.inc.php';
require '../classes/ezpdf.class.php';
require '../requires/convert.inc.php';
require '../requires/numeric.inc.php';

$template=$_REQUEST['flag'];
if($argv[3]){
	$id_edit=$argv[3];
}else{
	$id_edit = $_REQUEST['fk_sbg'];
}
$fk_sbg = $_REQUEST['fk_sbg'];
$tgl = convert_date_english($_REQUEST['tgl']);
//echo $tgl;
$query="
select * from tblinventory  
left join tblcustomer on fk_cif = no_cif 
left join (
	select lama_pinjaman as tenor,biaya_penyimpanan as total_bunga,* from data_gadai.tblproduk_cicilan
)as tblproduk_cicilan on tblinventory.fk_sbg=no_sbg
left join tblcabang on fk_cabang=kd_cabang
left join(
	select * from viewkendaraan
	left join tblpartner on fk_partner_dealer=kd_partner
)as tblbarang on fk_fatg=no_fatg
where fk_sbg = '".$fk_sbg."'";
$l_res=pg_query($query);	
$lrow=pg_fetch_array($l_res);
//showquery($query);
$query_produk="select * from tblproduk_detail_tenor where fk_produk='".$lrow["fk_produk"]."'";
$l_p=pg_query($query_produk);	
$lrow_produk=pg_fetch_array($l_p);

$tgl_cair_indo=date("d",strtotime($lrow['tgl_cair'])).' '.getMonthName(date("m",strtotime($lrow['tgl_cair'])),2).' '.date("Y",strtotime($lrow['tgl_cair']));
$tgl_pengajuan=date("d",strtotime($lrow['tgl_pengajuan'])).' '.getMonthName(date("m",strtotime($lrow['tgl_pengajuan'])),2).' '.date("Y",strtotime($lrow['tgl_pengajuan']));
$fk_cabang=$lrow["fk_cabang"];
$pdf = new Cezpdf('A4');  
//$pdf->ezImage('survey.png',0, 0, 0);
$pdf->setLineStyle(1);

$pdf->ez['topMargin'] = (150);
$pdf->ez['bottomMargin']=5;
$pdf->ez['leftMargin']=75;
$pdf->ez['rightMargin']=80;
/*$all = $pdf->openObject();
$pdf->saveState();
*/
if($template=='t'){
//Header
$fontsize=9;
$y=800;
$x1 = 67;
$x2 = 140;

$x3 = 273;
$x4 = 437;
$x_right=76;
$pdf->selectFont('fonts/Times-Roman');
$y-=78;

$pdf->addText($x2+4,$y, $fontsize,$lrow["nm_customer"]);
$y-=18;
$pdf->addText($x2+4,$y, $fontsize,$lrow["jabatan"]);
$y-=21;
$pdf->addText($x2+4,$y, $fontsize,$lrow["alamat_ktp"]);
$y-=20;
$pdf->addText($x2+4,$y, $fontsize,$lrow["no_id"]);

$y-=18;
$pdf->addText($x3,$y, $fontsize,$id_edit);
$pdf->addText($x4-14,$y, $fontsize,$tgl_pengajuan);

$y-=298;
$pdf->addText($x1-30,$y, $fontsize,$lrow['nm_cabang']);
$pdf->addText($x2-20,$y, $fontsize,$tgl_pengajuan);
$y-=124;

$pdf->addText($x1,$y, $fontsize,$lrow["nm_customer"]);
}else
{
//Header
$fontsize=9;
$y=800;
$x1 = 67;
$x2 = 111;

$x3 = 293;
$x4 = 363;
$x_right=76;

$pdf->selectFont('fonts/Times-Roman');

$lrs_ho=pg_fetch_array(pg_query("select * from tblcabang where kd_cabang='".cabang_ho."'"));

$pdf->addText($x1+105,$y, $fontsize+3,'<b>SURAT PERNYATAAN DAN PERSETUJUAN<b>');
$y-=40;
$pdf->addText($x1,$y, $fontsize,'Saya yang bertanda tangan di bawah ini : ');
/*$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');
*/

$pdf->ezSetY($y-5);

//$tgl_sistem='2021-02-09';
$i=0;

$data[$i]['data1'] = 'Nama ';	
$data[$i]['data2'] =  ': '.strtoupper($lrow['nm_customer']);	
$i++;

$data[$i]['data1'] = 'Jabatan ';	
$data[$i]['data2'] =  ': '.strtoupper($lrow['jabatan']);	
$i++;

$data[$i]['data1'] = 'Alamat ';	
$data[$i]['data2'] =  ': '.strtoupper($lrow['alamat_ktp']);	
$i++;

$data[$i]['data1'] = 'No KTP ';	
$data[$i]['data2'] =  ': '.$lrow["no_id"];	
$i++;


$judul['data1'] = '';
$judul['data2'] = '';

$lining['showHeadings'] = 0;
$lining['shaded'] = 0;
$lining['showLines'] = 0;
$lining['xPos'] = 287;
$lining['fontSize'] = 9;
$lining['rowGap'] = 1.5;
$lining['colGap'] = 1.5;

$lining['cols']['data1']['justification'] = 'left';
$lining['cols']['data2']['justification'] = 'left';
$size['data1'] = '65';
$size['data2'] = '350';

//$pdf->line(10,240,585,240);
$pdf->ezTable($data,$judul,'',$lining,$size);

$y=$pdf->y-15;
$pdf->addText($x1,$y, $fontsize,'Bahwa terkait Perjanjian Pembiayan Nomor '.$id_edit.' tanggal '.$tgl_pengajuan.' dengan ini menyatakan :');
$y-=12;
$spasi="		";
$pdf->y=$y;
$arr=array();
$arr=array(
1=>'Saya telah diberi masa tenggang waktu selama 2 (dua) hari kerja oleh PT. Capella Multidana setelah saya menandatangani Perjanjian guna mempelajari isi perjanjian serta surat-surat lain yang merupakan satukesatuan yang tidak terpisahkan dengan Perjanjian pembiayaan.',
2=>'Apabila saya membatalkan Perjanjian Pembiayaan selama masa tenggang waktu sebagaimana point (1) diatas, saya akan mengajukan pembatalan tersebut secara tertulis dan bersedia membayar seluruh biaya yang mencakup biaya administrasi dan survey yang dikeluarkan PT. Capella Multidana secara seketika dan sekaligus.',
3=>"Bahwa terkait biaya-biaya yang dikeluarkan PT Capella Multidana dengan adanya pembatalan Perjanjian Pembiayaan yang saya lakukan sebagaimana poin (2) diatas, dengan ini saya bersedia dan menyetujui besar biaya administrasi dan survey pembatalan sbb:
Untuk survey diluar kota kendaraan mobi sebesar : Rp 1.000.000,- (satu juta rupiah).
Untuk survey didalam kota kendaraan mobil sebesar : Rp 500.000,- (lima ratus ribu rupiah).
Untuk survey untuk kendaraan sepeda motor sebesar : Rp 250.000.- (dua ratus lima puluh ribu rupiah).",
4=>'Apabila saya tidak mengajukan pembatalan Pe{anjian Pembiayaan secara tertulis kepada PT Capella Multidana selama tenggang waktu sebagaimana poin (l) diatas, maka saya menyatakan setuju Perjanjian Pembiayaan yang telah saya tandatangani bersama PT Capella Multidana dilanjutkan.',
);

for($i=1;$i<=4;$i++){
	$pdf->ezText($i.'.',$fontsize,array('justification'=>'full','left'=>-8));
	$pdf->y=$y;
	$pdf->ezText($arr[$i],$fontsize,array('justification'=>'full','left'=>11));
	$y=$pdf->y;
}

$y-=12;
$arr=array(
1=>"Demikian surat pernyataan dan persetujuan ini saya buat dengan sebenar-benarnya dalam keadaan sadar, tanpa adanya tekanan ataupun paksaan dari pihak manapun.
",
);
$pdf->y=$y;
$y-=12;
for($i=1;$i<=count($arr);$i++){
	$pdf->ezText($arr[$i],$fontsize,array('justification'=>'full','left'=>-8));
	$y-=30;
}
$pdf->addText($x1,$y, $fontsize,$lrow['nm_cabang'].','.$tgl_pengajuan);
$y-=15;
$pdf->addText($x1,$y, $fontsize,'Yang Membuat Pernyataan');
$y-=70;

$pdf->addText($x1,$y, $fontsize,'('.$lrow["nm_customer"].')');

$y-=90;
$pdf->addPngFromFile('../print/logo_2.png', 25, 5,30,25);
$pdf->selectFont('fonts/Times-Roman');
//$pdf->addText(100, 25, 6,'"PERJANJIAN INI TELAH DISESUAIKAN DENGAN KETENTUAN PERATURAN PERUNDANG-UNDANGAN TERMASUK KETENTUAN PERATURAN OTORITAS JASA KEUANGAN"');
$pdf->addText(75, 10, 9,'PT. Capella Multidana Berizin dan Diawasi oleh Otoritas Jasa Keuangan');
}
if($template!='t')$filename=$id_edit.'-'.$lrow["nm_customer"].'.pdf';
else $filename='template.pdf';
$options=array();//untuk rename nama file
$options['Content-Disposition']=$filename;

$pdf->ezStream($options);

?>