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

$fk_cabang=$lrow["fk_cabang"];
$pdf = new Cezpdf('F4');  
$pdf->setLineStyle(1);

$pdf->ez['topMargin'] = (150);
$pdf->ez['bottomMargin']=5;
$pdf->ez['leftMargin']=75;
$pdf->ez['rightMargin']=80;
/*$all = $pdf->openObject();
$pdf->saveState();
*/
//Header
$fontsize=12;
$y=890;
$x1 = 86;
$x2 = 130;

$x3 = 308;
$x4 = 378;
$x_right=67;

$pdf->selectFont('fonts/Helvetica');

$lrs_ho=pg_fetch_array(pg_query("select * from tblcabang where kd_cabang='".cabang_ho."'"));


$pdf->addText($x1+150,$y, $fontsize,'<b>SURAT PERNYATAAN<b>');
$y-=40;
$pdf->addText($x1,$y, $fontsize,'Saya yang bertanda tangan dibawah ini:');

/*$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');
*/

$pdf->ezSetY($y-15);

//$tgl_sistem='2021-02-09';
$i=0;

$data[$i]['data1'] = 'Nama ';	
$data[$i]['data2'] =  ': '.strtoupper($lrow['nm_customer']);	
$i++;

$data[$i]['data1'] = 'Alamat ';	
$data[$i]['data2'] =  ': '.strtoupper($lrow['alamat_tinggal']);	
$i++;

$data[$i]['data1'] = 'No KTP ';	
$data[$i]['data2'] =  ': '.$lrow["no_id"];	
$i++;


$judul['data1'] = '';
$judul['data2'] = '';

$lining['showHeadings'] = 0;
$lining['shaded'] = 0;
$lining['showLines'] = 0;
$lining['xPos'] = 315;
$lining['fontSize'] = 11;
$lining['rowGap'] = 1.5;
$lining['colGap'] = 1.5;

$lining['cols']['data1']['justification'] = 'left';
$lining['cols']['data2']['justification'] = 'left';
$size['data1'] = '65';
$size['data2'] = '350';

//$pdf->line(10,240,585,240);
$pdf->ezTable($data,$judul,'',$lining,$size);

$y=$pdf->y-10;
$pdf->y=$y;
$pdf->ezText('Dengan ini menyatakan setuju dan akan mengikuti aturan PELUNASAN DI PERCEPAT kredit kendaraan bermotor yang di buat oleh PT CAPELLA MULTIDANA dengan ketentuan sebagai berikut:',$fontsize,array('justification'=>'full','left'=>11));

$y-=50;
$pdf->y=$y;

$pdf->ezText('Tenor kredit 12 bulan, pelunasan kredit dapat dilakukan dengan membayar seluruh sisa angsuran yang belum jatuh tempo.',$fontsize,array('justification'=>'full','left'=>50));
$pdf->addText($x1+10,$y-12, $fontsize,'-');
$y-=30;
$pdf->y=$y;

$pdf->ezText('Tenor kredit 18 bulan, pelunasan kredit dapat dilakukan apabila telah melaksanakan pembayaran angsuran minimal 12 bulan dari masa kredit yang diperjanjikan.',$fontsize,array('justification'=>'full','left'=>50));
$pdf->addText($x1+10,$y-12, $fontsize,'-');
$y-=45;
$pdf->y=$y;

$pdf->ezText('Tenor kredit 30 bulan, pelunasan kredit dapat dilakukan apabila telah melaksanakan pembayaran angsuran minimal 15 bulan dari masa kredit yang diperjanjikan.',$fontsize,array('justification'=>'full','left'=>50));
$pdf->addText($x1+10,$y-12, $fontsize,'-');
$y-=45;
$pdf->y=$y;

$pdf->ezText('Tenor kredit 36 bulan, pelunasan kredit dapat dilakukan apabila telah melaksanakan pembayaran angsuran minimal 15 bulan dari masa kredit yang diperjanjikan.',$fontsize,array('justification'=>'full','left'=>50));
$pdf->addText($x1+10,$y-12, $fontsize,'-');
$y-=45;
$pdf->y=$y;

$pdf->ezText('Tenor kredit 42 bulan, pelunasan kredit dapat dilakukan apabila telah melaksanakan pembayaran angsuran minimal 18 bulan dari masa kredit yang diperjanjikan.',$fontsize,array('justification'=>'full','left'=>50));
$pdf->addText($x1+10,$y-12, $fontsize,'-');
$y-=45;
$pdf->y=$y;

$pdf->ezText('Tenor kredit 48 bulan, pelunasan kredit dapat dilakukan apabila telah melaksanakan pembayaran angsuran minimal 18 bulan dari masa kredit yang diperjanjikan',$fontsize,array('justification'=>'full','left'=>50));
$pdf->addText($x1+10,$y-12, $fontsize,'-');
$y-=45;
$pdf->y=$y;

$pdf->ezText('Tenor kredit 60 bulan, pelunasan kredit dapat dilakukan apabila telah melaksanakan pembayaran angsuran minimal 21 bulan dari masa kredit yang diperjanjikan.',$fontsize,array('justification'=>'full','left'=>50));
$pdf->addText($x1+10,$y-12, $fontsize,'-');
$y-=45;
$pdf->y=$y;

$pdf->ezText('Demikianlah surat pernyataan ini saya perbuat dalam keadaan sadar dan tanpa paksaan dari pihak manapun.',$fontsize,array('justification'=>'full','left'=>11));
$y-=70;

$pdf->addText($x1,$y, $fontsize,$lrow['nm_cabang'].','.date("d").' '.getMonthName(date("m"),2).' '.date("Y"));
$y-=15;
$pdf->addText($x1,$y, $fontsize,'Yang Membuat Pernyataan');
$y-=70;

$pdf->addText($x1,$y, $fontsize,'('.$lrow["nm_customer"].')');

$y-=90;
$pdf->addPngFromFile('../print/logo_2.png', 35, 5,30,25);
$pdf->selectFont('fonts/Times-Roman');
//$pdf->addText(100, 25, 6,'"PERJANJIAN INI TELAH DISESUAIKAN DENGAN KETENTUAN PERATURAN PERUNDANG-UNDANGAN TERMASUK KETENTUAN PERATURAN OTORITAS JASA KEUANGAN"');
$pdf->addText(75, 15, 9,'PT. Capella Multidana Berizin dan Diawasi oleh Otoritas Jasa Keuangan');
$pdf->selectFont('fonts/Times-Roman');

$pdf->ezStream();

?>