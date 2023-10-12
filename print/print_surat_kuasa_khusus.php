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
select tblcabang.alamat as alamat_cabang,* from tblinventory  
left join tblcustomer on fk_cif = no_cif 
left join (
	select lama_pinjaman as tenor,biaya_penyimpanan as total_bunga,* from data_gadai.tblproduk_cicilan
)as tblproduk_cicilan on tblinventory.fk_sbg=no_sbg
left join tblcabang on fk_cabang=kd_cabang
left join(
	select * from viewkendaraan
	left join tblpartner on fk_partner_dealer=kd_partner
	left join (
		select no_fatg as no_fatg1, status_barang from data_gadai.tbltaksir_umum 
	)as tbl on no_fatg1=no_fatg
)as tblbarang on fk_fatg=no_fatg
where fk_sbg = '".$fk_sbg."'";
$l_res=pg_query($query);	
$lrow=pg_fetch_array($l_res);
//showquery($query);

$fk_cabang=$lrow["fk_cabang"];
$kacab=kacab($lrow["fk_cabang"],$lrow["tgl_cair"]);
$lrow["nm_kacab"]=$kacab["nm_depan"];

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
$fontsize=11;
$y=800;
$x1 = 69;
$x2 = 130;

$x3 = 308;
$x4 = 378;
$x_right=67;

$pdf->selectFont('fonts/Times-Roman');

$lrs_ho=pg_fetch_array(pg_query("select * from tblcabang where kd_cabang='".cabang_ho."'"));


$pdf->addText($x1+180,$y, $fontsize+3,'<b>SURAT KUASA</b>');
$pdf->addText($x1+180,$y, $fontsize,'<b>_________________</b>');

$y-=30;
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
$lining['xPos'] = 300;
$lining['fontSize'] = 11;
$lining['rowGap'] = 1.5;
$lining['colGap'] = 1.5;

$lining['cols']['data1']['justification'] = 'left';
$lining['cols']['data2']['justification'] = 'left';
$size['data1'] = '65';
$size['data2'] = '370';

//$pdf->line(10,240,585,240);
$pdf->ezTable($data,$judul,'',$lining,$size);

$tgl_cair_indo=date("d",strtotime($lrow['tgl_cair'])).' '.getMonthName(date("m",strtotime($lrow['tgl_cair'])),2).' '.date("Y",strtotime($lrow['tgl_cair']));

$y=$pdf->y-10;
$pdf->y=$y;
$pdf->ezText('Dalam hal ini bertindak untuk dan atas nama diri sendiri yang selanjutnya disebut sebagai <b>PEMBERI KUASA</b>.',$fontsize,array('justification'=>'full','left'=>11));
$y-=30;
$pdf->y=$y;

$pdf->ezText('Dengan ini memberikan kuasa dengan hak substitusi kepada <b>'.$lrow["nm_perusahaan"].'</b> berkedudukan di <b>'.$lrow["nm_cabang"].'</b> dan berkantor di '.$lrow["alamat_cabang"].' Selanjutnya disebut <b>PENERIMA KUASA</b>.',$fontsize,array('justification'=>'full','left'=>11));
$y-=70;
$pdf->y=$y;

$pdf->addText($x1+200,$y, $fontsize,'<b>KHUSUS</b>');
$pdf->addText($x1+200,$y, $fontsize,'________');


$pdf->ezText('Untuk dan atas nama <b>PEMBERI KUASA</b> melakukan eksekusi, menerima, memeliahara, menyewakan, mengagunkan/menjaminkan, membaliknamakan, memindahkan atau menguasai kembali dan menjual dengan harga dan syarat-syarat ketentuan-ketentuan yang dianggap baik oleh penerima kuasa kepada siapapun juga termasuk kepada Penerima Kuasa sendiri atas 1 (satu) unit kendaraan bermotor dengan perincian sebagai berikut:',$fontsize,array('justification'=>'full','left'=>11));
$y-=65;
$pdf->y=$y;

$y-=20;
$pdf->addText($x1+30,$y, $fontsize,'-');
$pdf->addText($x1+50,$y, $fontsize,'Merek');
$pdf->addText($x1+170,$y, $fontsize,': '.$lrow['nm_merek']);
$y-=12;
$pdf->addText($x1+30,$y, $fontsize,'-');
$pdf->addText($x1+50,$y, $fontsize,'Jenis');
$pdf->addText($x1+170,$y, $fontsize,': '.$lrow['nm_jenis_barang']);
$y-=12;
$pdf->addText($x1+30,$y, $fontsize,'-');
$pdf->addText($x1+50,$y, $fontsize,'Model/Type');
$pdf->addText($x1+170,$y, $fontsize,': '.$lrow['nm_tipe']);
$y-=12;
$pdf->addText($x1+30,$y, $fontsize,'-');
$pdf->addText($x1+50,$y, $fontsize,'No. Rangka');
$pdf->addText($x1+170,$y, $fontsize,': '.$lrow['no_rangka']);
$y-=12;
$pdf->addText($x1+30,$y, $fontsize,'-');
$pdf->addText($x1+50,$y, $fontsize,'No. Mesin	');
$pdf->addText($x1+170,$y, $fontsize,': '.$lrow['no_mesin']);
$y-=12;
$pdf->addText($x1+30,$y, $fontsize,'-');
$pdf->addText($x1+50,$y, $fontsize,'Keadaan');
$pdf->addText($x1+170,$y, $fontsize,': '.$lrow['status_barang']);
$y-=12;
$pdf->addText($x1+30,$y, $fontsize,'-');
$pdf->addText($x1+50,$y, $fontsize,'Tahun Pembuatan');
$pdf->addText($x1+170,$y, $fontsize,': '.$lrow['tahun']);
$y-=12;
$pdf->addText($x1+30,$y, $fontsize,'-');
$pdf->addText($x1+50,$y, $fontsize,'Warna');
$pdf->addText($x1+170,$y, $fontsize,': '.$lrow['warna']);
$y-=10;
$pdf->y=$y;
$pdf->ezText('Guna keperluan tersebut <b>PENERIMA KUASA</b> berwenang menghadap dimana perlu, antara lain tetapi tidak terbatas pada notaris dan pejabat-pejabat dari instansi yang berwenang untuk memberikan keterangan, membuat dan suruh membuat, menandatangani surat-surat serta memberikan kwitansi tanda terimanya dan melakukan segala tindakan lainnya yang diperlukan tanpa terkecuali agar maksud dan tujuan Surat Kuasa ini dapat dilaksanakan dengan sebaik-baiknya. ',$fontsize,array('justification'=>'full','left'=>11));
$y-=70;
$pdf->ezText('Surat kuasa ini tidak dapat dicabut kembali atau menjadi batal dengan alasan-alasan atau sebab-sebab apapun juga, termasuk sebab-sebab yang tercantum dalam pasal 1813,1814,1816 kitab Undang-Undang Hukum Perdata selama seluruh Hutang (Total Kewajiban) Pemberi Kuasa belum dibayar lunas berdasarkan Akad Murabahah Nomor : '.$fk_sbg.' Tanggal : '.$tgl_cair_indo.'. ',$fontsize,array('justification'=>'full','left'=>11));
$y-=80;
$pdf->y=$y;
$pdf->ezText('Demikian surat kuasa ini dibuat untuk dipergunakan sebagaimana mestinya, dan dalam keadaan sadar, sehat jasmani dan rohani serta tidak mendapat paksaan dari pihak manapun juga.',$fontsize,array('justification'=>'full','left'=>11));
$y-=70;


$pdf->addText($x1,$y, $fontsize,$lrow['nm_cabang'].','.date("d").' '.getMonthName(date("m"),2).' '.date("Y"));
$y-=30;
$pdf->addText($x1,$y, $fontsize,'PENERIMA KUASA,');
$pdf->addText($x1+300,$y, $fontsize,'PEMBERI KUASA,');

$y-=70;

$pdf->addText($x1,$y, $fontsize,$lrow["nm_kacab"]);
$pdf->addText($x1+300,$y, $fontsize,$lrow["nm_customer"]);

//$pdf->addText($x1,$y, $fontsize,'_________________');
//$pdf->addText($x1+300,$y, $fontsize,'_________________');

$y-=90;
$pdf->addPngFromFile('../print/logo_2.png', 35, 5,30,25);
$pdf->selectFont('fonts/Times-Roman');
//$pdf->addText(100, 25, 6,'"PERJANJIAN INI TELAH DISESUAIKAN DENGAN KETENTUAN PERATURAN PERUNDANG-UNDANGAN TERMASUK KETENTUAN PERATURAN OTORITAS JASA KEUANGAN"');
$pdf->addText(75, 15, 9,'PT. Capella Multidana Berizin dan Diawasi oleh Otoritas Jasa Keuangan');
$pdf->selectFont('fonts/Times-Roman');

$pdf->ezStream();

?>