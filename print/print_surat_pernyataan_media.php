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
$template=$_REQUEST['flag'];
$tgl = convert_date_english($_REQUEST['tgl']);
//echo $tgl;

$query="
select * from  (
	select lama_pinjaman as tenor,biaya_penyimpanan as total_bunga,no_sbg as fk_sbg,nm_bpkb,no_fatg as no_fatg1,* from data_gadai.tblproduk_cicilan
	left join data_gadai.tbltaksir_umum on no_fatg=fk_fatg
)as tblproduk_cicilan  
left join tblcustomer on fk_cif = no_cif 
left join tblcabang on fk_cabang=kd_cabang
left join(
	select * from viewkendaraan
	left join tblpartner on fk_partner_dealer=kd_partner
)as tblbarang on fk_fatg=no_fatg1
where fk_sbg = '".$fk_sbg."'";

$l_res=pg_query($query);	
$lrow=pg_fetch_array($l_res);
//showquery($query);
$tgl_cair_indo=date("d",strtotime($lrow['tgl_cair'])).' '.getMonthName(date("m",strtotime($lrow['tgl_cair'])),2).' '.date("Y",strtotime($lrow['tgl_cair']));
$tgl_pengajuan=date("d",strtotime($lrow['tgl_pengajuan'])).' '.getMonthName(date("m",strtotime($lrow['tgl_pengajuan'])),2).' '.date("Y",strtotime($lrow['tgl_pengajuan']));

$tgl_lahir_indo=date("d",strtotime($lrow['tgl_lahir'])).' '.getMonthName(date("m",strtotime($lrow['tgl_lahir'])),2).' '.date("Y",strtotime($lrow['tgl_lahir']));

$fk_cabang=$lrow["fk_cabang"];
$pdf = new Cezpdf('A4');  
$pdf->setLineStyle(1);

$pdf->ez['topMargin'] = (150);
$pdf->ez['bottomMargin']=5;
$pdf->ez['leftMargin']=60;
$pdf->ez['rightMargin']=65;
/*$all = $pdf->openObject();
$pdf->saveState();
*/
//Header
$fontsize=9;
$y=800;
$x1 = 69;
$x2 = 130;

$x3 = 308;
$x4 = 378;
$x_right=67;

$pdf->selectFont('fonts/Times-Roman');

$lrs_ho=pg_fetch_array(pg_query("select * from tblcabang where kd_cabang='".cabang_ho."'"));


if($template=='t'){
$y-=84;
$x5='170';
$pdf->addText($x5,$y, $fontsize,$lrow["nm_customer"]);
$y-=18;
$pdf->addText($x5,$y, $fontsize,strtoupper($lrow['tempat_lahir'].', '.$tgl_lahir_indo));
$y-=17;
$pdf->addText($x5,$y, $fontsize,strtoupper($lrow['alamat_ktp']));	
$y-=17;
$pdf->addText($x5,$y, $fontsize,$lrow["no_id"]);

$y-=76;
$pdf->addText($x5-20,$y, $fontsize,$fk_sbg);
$pdf->addText($x4+25,$y, $fontsize,$tgl_pengajuan);

$y-=270;
//$pdf->selectFont('fonts/Times-Roman');
$pdf->addText($x1+270,$y, $fontsize,$lrow['nm_cabang']);
$pdf->addText($x1+380,$y, $fontsize,$tgl_pengajuan);

$y-=34;
$y-=58;

$pdf->addText($x1+355,$y, $fontsize,$lrow["nm_customer"]);

}
else{
$pdf->addText($x1+150,$y, $fontsize+3,'<b>SURAT PERNYATAAN<b>');
$pdf->addText($x1+150,$y, $fontsize,'<b>______________________________<b>');

$y-=30;
$pdf->addText($x1,$y, $fontsize,'Bahwa saya yang bertanda tangan dibawah ini:');

/*$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');
*/

$pdf->ezSetY($y-10);

//$tgl_sistem='2021-02-09';
$i=0;

$data[$i]['data1'] = 'Nama ';	
$data[$i]['data2'] =  ': '.strtoupper($lrow['nm_customer']);	
$i++;


$data[$i]['data1'] = 'Tempat/Tgl Lahir ';	
$data[$i]['data2'] =  ': '.strtoupper($lrow['tempat_lahir'].', '.$tgl_lahir_indo);	
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
$lining['xPos'] = 315;
$lining['fontSize'] = 9;
$lining['rowGap'] = 1.5;
$lining['colGap'] = 1.5;

$lining['cols']['data1']['justification'] = 'left';
$lining['cols']['data2']['justification'] = 'left';
$size['data1'] = '95';
$size['data2'] = '360';

//$pdf->line(10,240,585,240);
$pdf->ezTable($data,$judul,'',$lining,$size);
$y=$pdf->y-20;


$pdf->addText($x1,$y, $fontsize,'Dengan ini menyatakan bahwa saya :');
$pdf->addText($x1,$y, $fontsize,'<b>_______________________________<b>');

$y-=15;
$pdf->y=$y;

$pdf->ezText('Saya telah membaca, mengerti, serta menyetujui segala peraturan-peraturan yang tertulis dalam Pembiayaan Syariah Dengan Akad Murabah dengan Nomor '.$fk_sbg.' tanggal '.$tgl_pengajuan,$fontsize,array('justification'=>'full','left'=>45));
$pdf->addText($x1,$y-12, $fontsize,'1.  [   ]');
$y-=30;
$pdf->y=$y;

$pdf->ezText('Tanda tangan saya sering berubah-ubah terkait dengan surat Perjanjian.',$fontsize,array('justification'=>'full','left'=>45));
$pdf->addText($x1,$y-12, $fontsize,'2.  [   ]');
$y-=30;
$pdf->y=$y;

$pdf->ezText('Bahwa pada saat ini saya belum memiliki Nomor Pokok Wajib Pajak (NPWP)',$fontsize,array('justification'=>'full','left'=>45));
$pdf->addText($x1,$y-12, $fontsize,'3.  [   ]');
$y-=30;
$pdf->y=$y;

$pdf->ezText('Saya tidak berkeberatan untuk menyerahkan Barang/Objek Pembiayaan kepada '.$lrow['nm_perusahaan'].' apabila terjadi keterlambatan pembayaran angsuran melebihi 7 (tujuh) hari dari tanggal jatuh tempo pembayaran.',$fontsize,array('justification'=>'full','left'=>45));
$pdf->addText($x1,$y-12, $fontsize,'4.  [   ]');
$y-=40;
$pdf->y=$y;

$pdf->ezText('Saya tidak berkeberatan dan tidak akan menuntut secara Pidana maupun Perdata apabila '.$lrow['nm_perusahaan'].' memuat Berita Panggilan lengkap dengan pasfoto atas diri saya di Mass-Media akibat tunggakan pembayaran angsuran sehubungan dengan Perjanjian Pembiayaan ini. Saya juga bersedia menanggung seluruh biaya yang dikeluarkan '.$lrow['nm_perusahaan'].' atas berita panggilan tersebut.',$fontsize,array('justification'=>'full','left'=>45));
$pdf->addText($x1,$y-12, $fontsize,'5.  [   ]');
$y-=55;
$pdf->y=$y;

$pdf->ezText('Saya memberikan persetujuan kepada '.$lrow['nm_perusahaan'].' dalam menyebarkan Data dan/atau Informasi Pribadi saya kepada PIHAK KETIGA untuk segala hal yang berhubungan dengan Akad Pembiayaan Syariah Dengan Akad Murabahah ini.',$fontsize,array('justification'=>'full','left'=>45));
$pdf->addText($x1,$y-12, $fontsize,'6.  [   ]');
$y-=55;
$pdf->y=$y;

$pdf->ezText('Demikianlah surat pernyataan ini dibuat dengan sebenar-benarnya dalam keadaan sadar, tanpa adanya tekanan ataupun paksaan dari pihak manapun dan ketentuan ini tidak dibatalkan ataupun ditarik kembali secara sepihak.',$fontsize,array('justification'=>'full','left'=>11));
$y-=70;
//$pdf->selectFont('fonts/Times-Roman');
$today_indo=date("d").' '.getMonthName(date("m"),2).' '.date("Y");
$pdf->addText($x1+315,$y, $fontsize,$lrow['nm_cabang'].','.$tgl_pengajuan,array('justification'=>'full','right'=>45));
$y-=20;
$pdf->addText($x1+325,$y, $fontsize,'Yang membuat Pernyataan,',array('justification'=>'full','right'=>45));

$y-=70;

$pdf->addText($x1+333,$y, $fontsize,'('.$lrow["nm_customer"].')');

$y-=90;
//$pdf->ezImage('../print/logo_2.png','30','60','','left','');
$pdf->addPngFromFile('../print/logo_2.png', 35, 5,30,25);
//$pdf->addText(100, 25, 6,'"PERJANJIAN INI TELAH DISESUAIKAN DENGAN KETENTUAN PERATURAN PERUNDANG-UNDANGAN TERMASUK KETENTUAN PERATURAN OTORITAS JASA KEUANGAN"');
$pdf->addText(75, 15, $fontsize,'PT. Capella Multidana Berizin dan Diawasi oleh Otoritas Jasa Keuangan');
}

$pdf->ezStream();

?>