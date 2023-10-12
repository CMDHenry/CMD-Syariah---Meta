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
left join tblpekerjaan on fk_pekerjaan=kd_pekerjaan
where fk_sbg = '".$fk_sbg."'";
$l_res=pg_query($query);	
$lrow=pg_fetch_array($l_res);
//showquery($query);
$tgl_cair_indo=date("d",strtotime($lrow['tgl_cair'])).' '.getMonthName(date("m",strtotime($lrow['tgl_cair'])),2).' '.date("Y",strtotime($lrow['tgl_cair']));

if($lrow["jenis_cabang"]=='Cabang'){
	$jabatan_pihak1='KEPALA CABANG';
	$kacab=kacab($lrow["fk_cabang"],$lrow["tgl_pengajuan"]);
	$nm_pihak1=$kacab["nm_depan"];	
	$alamat_pihak1=$kacab["alamat"];
}elseif($lrow["jenis_cabang"]=='Pos'){
	$jabatan_pihak1='Pimpinan Unit Usaha Syariah';	
	//$jabatan_pihak1='Pimpinan UUS';	
	$nm_pihak1=get_rec("tblkaryawan left join tbljabatan on fk_jabatan =kd_jabatan","nm_depan","nm_jabatan='".$jabatan_pihak1."'");
	$alamat_pihak1=get_rec("tblkaryawan left join tbljabatan on fk_jabatan =kd_jabatan","alamat","nm_jabatan='".$jabatan_pihak1."'");
}
$fk_cabang=$lrow["fk_cabang"];
$pdf = new Cezpdf('F4');  
$pdf->setLineStyle(1);

$pdf->ez['topMargin'] = (150);
$pdf->ez['bottomMargin']=5;
$pdf->ez['leftMargin']=60;
$pdf->ez['rightMargin']=65;
/*$all = $pdf->openObject();
$pdf->saveState();
*/
//Header
$fontsize=10;
$y=890;
$x1 = 69;
$x2 = 130;

$x3 = 308;
$x4 = 378;
$x_right=67;

$pdf->selectFont('fonts/Helvetica');

$lrs_ho=pg_fetch_array(pg_query("select * from tblcabang where kd_cabang='".cabang_ho."'"));


$pdf->addText($x1+100,$y, $fontsize,'<b>KESEPAKATAN BERSAMA TERKAIT WANPRESTASI</b>');
$pdf->addText($x1+100,$y-15, $fontsize,'<b>DAN PENYERAHAN OBJEK JAMINAN PEMBIAYAAN</b>');

$y-=40;
$pdf->addText($x1,$y, $fontsize,'Yang bertanda tangan di bawah ini :');
$pdf->addText($x1,$y-24, $fontsize,'1.');
$pdf->addText($x1,$y-129, $fontsize,'2.');

$pdf->ezSetY($y-60);
$pdf->ezText('Dalam hal ini bertindak dalam jabatannya selaku kuasa dari dan karenanya untuk dan atas nama '.$lrow['nm_perusahaan'].', berkedudukan di '.$lrow['nm_cabang'].', selanjutnya secara disebut sebagai KREDITUR.',$fontsize,array('justification'=>'full','left'=>28));
/*$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');
*/
$kacab=kacab($lrow["fk_cabang"],$lrow["tgl_cair"]);
$lrow["nm_kacab"]=$kacab["nm_depan"];
$alamat_kacab=$kacab["alamat"];


$pdf->ezSetY($y-10);

//$tgl_sistem='2021-02-09';
$i=0;

$data[$i]['data1'] = 'Nama ';	
$data[$i]['data2'] =  ': '.$nm_pihak1;	
$i++;

$data[$i]['data1'] = 'Jabatan ';	
$data[$i]['data2'] =  ': Kepala Cabang';	
$i++;

$data[$i]['data1'] = 'Alamat ';	
$data[$i]['data2'] =  ': '.$alamat_pihak1;	
$i++;

$data[$i]['data1'] = ' ';	
$data[$i]['data2'] = ' ';	
$i++;
$data[$i]['data1'] = ' ';	
$data[$i]['data2'] = ' ';	
$i++;
$data[$i]['data1'] = ' ';	
$data[$i]['data2'] = ' ';	
$i++;
$data[$i]['data1'] = ' ';	
$data[$i]['data2'] = ' ';	
$i++;

$data[$i]['data1'] = 'Nama ';	
$data[$i]['data2'] =  ': '.strtoupper($lrow['nm_customer']);	
$i++;

$data[$i]['data1'] = 'Pekerjaan Jabatan ';	
$data[$i]['data2'] =  ': '.$lrow["nm_pekerjaan"];	
$i++;

$data[$i]['data1'] = 'Alamat ';	
$data[$i]['data2'] =  ': '.strtoupper($lrow['alamat_tinggal']);	
$i++;


$judul['data1'] = '';
$judul['data2'] = '';

$lining['showHeadings'] = 0;
$lining['shaded'] = 0;
$lining['showLines'] = 0;
$lining['xPos'] = 310;
$lining['fontSize'] = 10;
$lining['rowGap'] = 1;
$lining['colGap'] = 1.5;

$lining['cols']['data1']['justification'] = 'left';
$lining['cols']['data2']['justification'] = 'left';
$size['data1'] = '100';
$size['data2'] = '345';

//$pdf->line(10,240,585,240);
$pdf->ezTable($data,$judul,'',$lining,$size);

if($lrow['nm_pasangan']==NULL){
	$lrow["nm_pasangan"]='______________';
}
$pdf->ezText('Dalam melakukan tindakan hukum ini telah mendapat persetujuan dari Suami / Istri / Komisaris yaitu '.$lrow["nm_pasangan"].' yang turut hadir dan menandatangani Perjanjian ini, bertindak atas nama diri sendiri dan/atau bertindak dalam jabatannya yang selanjutnya disebut DEBITUR.',$fontsize,array('justification'=>'full','left'=>28));

$y=$pdf->y-10;
$pdf->y=$y;

$pdf->ezText('Bahwa Kreditur dan Debitur dengan ini membuat suatu Kesepakatan Bersama terkait Wanprestasi dan kesepakatan ini satu kesatuan yang tidak terpisahkan dengan Perjanjian Investasi Dengan Pembayaran Secara Angsuran Nomor: '.$fk_sbg.' Tanggal: '.$tgl_cair_indo.', yang isinya adalah sebagai berikut :',$fontsize,array('justification'=>'full','left'=>8));
$y-=50;
$pdf->y=$y;

$pdf->ezText('Bahwa antara Kreditur dan Debitur menyepakati bila Debitur cedera janji / ingkar janji / lalai dalam melakukan kewajibannya kepada Kreditur lebih dari 7 (tujuh) hari waktu berjalan yang selanjutnya disebut Wanprestasi.',$fontsize,array('justification'=>'full','left'=>34));
$pdf->addText($x1,$y-14, $fontsize,'1.');
$y-=40;
$pdf->y=$y;

$pdf->ezText('Bahwa bila Debitur telah wanprestasi sebagaimana poin (1) di atas, maka Debitur menyerahkan
Barang / Objek Jaminan Pembiayaan kepada Kreditur dalam keadaan baik dan lengkap.',$fontsize,array('justification'=>'full','left'=>34));
$pdf->addText($x1,$y-14, $fontsize,'2.');
$y-=30;
$pdf->y=$y;

$pdf->ezText('Bahwa bila Debitur tidak menjalankan sebagaimana poin (2) di atas, bersama ini Kreditur dapat diberikan hak untuk melakukan teguran secara tertulis.',$fontsize,array('justification'=>'full','left'=>34));
$pdf->addText($x1,$y-14, $fontsize,'3.');
$y-=30;
$pdf->y=$y;

$pdf->ezText('Bahwa bila Debitur tidak juga mengindahkan poin (3) di atas dan setelah diberikan teguran tertulis yang secara undang-undang dianggap patut, maka Kreditur diberikan hak penuh untuk melakukan eksekusi terhadap Barang/Objek Jaminan Pembiayaan kapan dan dimana saja sebagaimana Surat Kuasa yang diberikan Debitur kepada Kreditur dalam perjanjian ini.',$fontsize,array('justification'=>'full','left'=>34));
$pdf->addText($x1,$y-14, $fontsize,'4.');
$y-=53;
$pdf->y=$y;

$pdf->ezText('Bahwa bila Debitur tidak juga mengindahkan poin (3) di atas dan setelah diberikan teguran tertulis yang secara undang-undang dianggap patut, maka Kreditur diberikan hak penuh untuk melakukan eksekusi terhadap Barang/Objek Jaminan Pembiayaan kapan dan dimana saja sebagaimana Surat Kuasa yang diberikan Debitur kepada Kreditur dalam perjanjian ini.',$fontsize,array('justification'=>'full','left'=>34));
$pdf->addText($x1,$y-14, $fontsize,'5.');
$y-=50;
$pdf->y=$y;

$pdf->ezText('Demikian Kesepakatan Bersama Mengenai Wanprestasi ini dibuat dengan sebenarnya tanpa ada paksaan dari pihak manapun.',$fontsize,array('justification'=>'full','left'=>11));
$y-=50;

$today_indo=date("d").' '.getMonthName(date("m"),2).' '.date("Y");
$pdf->addText($x1,$y, $fontsize,$lrow['nm_cabang'].', '.$today_indo);
$y-=30;

$pdf->addText($x1,$y, $fontsize,'PIHAK KREDITUR');
$pdf->addText($x1+160,$y, $fontsize,'PIHAK DEBITUR');
$pdf->addText($x1+320,$y, $fontsize-2,'MENYETUJUI : Suami / Istri/ Komisaris');

$y-=70;
$pdf->addText($x1,$y, $fontsize-1,$nm_pihak1);
$pdf->addText($x1+160,$y, $fontsize-1,$lrow["nm_customer"]);
$pdf->addText($x1+340,$y, $fontsize-1,$lrow["nm_pasangan"]);
$y-=90;
$pdf->addPngFromFile('../print/logo_2.png', 35, 5,30,25);
$pdf->selectFont('fonts/Times-Roman');
//$pdf->addText(100, 25, 6,'"PERJANJIAN INI TELAH DISESUAIKAN DENGAN KETENTUAN PERATURAN PERUNDANG-UNDANGAN TERMASUK KETENTUAN PERATURAN OTORITAS JASA KEUANGAN"');
$pdf->addText(75, 15, 9,'PT. Capella Multidana Berizin dan Diawasi oleh Otoritas Jasa Keuangan');
$pdf->selectFont('fonts/Times-Roman');

$pdf->ezStream();

?>