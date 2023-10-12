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
$template = $_REQUEST['flag'];

$tgl = convert_date_english($_REQUEST['tgl']);
//echo $tgl;
$query="
select *,nm_partner as nm_dealer,tblpartner.alamat as alamat_dealer,tblcustomer.npwp as npwp_cust,tblcabang.alamat as alamat_cabang from data_gadai.tblproduk_cicilan 
left join data_gadai.tbltaksir_umum on fk_fatg=no_fatg
left join tblcabang on kd_cabang=fk_cabang
left join tblcustomer on fk_cif=no_cif
left join tblpartner on fk_partner_dealer=kd_partner
left join (
	select fk_barang,fk_fatg as fk_fatg_detail from data_gadai.tbltaksir_umum_detail
)as tbldetail on fk_fatg_detail=no_fatg
		left join tblbarang on fk_barang=kd_barang
		left join tbltipe on fk_tipe=kd_tipe
		left join tblmodel on fk_model=kd_model
		left join tblmerek on fk_merek=kd_merek
left join tblwarna on fk_warna=kd_warna
left join tblpekerjaan on fk_pekerjaan=kd_pekerjaan
left join tblproduk on fk_produk=kd_produk
where no_sbg='".$fk_sbg."'";
$l_res=pg_query($query);	
$lrow=pg_fetch_array($l_res);
//showquery($query);
if($lrow["kategori"]=='R4'){
	$pdf = new Cezpdf('A4');  
	$y=820;
}else{
	$pdf = new Cezpdf('A4');  
	$y=820;
}
$pdf->setLineStyle(1);

$pdf->ez['topMargin'] = (150);
$pdf->ez['bottomMargin']=5;
$pdf->ez['leftMargin']=55;
$pdf->ez['rightMargin']=60;
/*$all = $pdf->openObject();
$pdf->saveState();
*/
//Header
$fontsize=9;
$x1 = 66;
$x2 = 110;

$x3 = 308;
$x4 = 378;
$x_right=67;

$pdf->selectFont('fonts/Times-Roman');
$lrs_ho=pg_fetch_array(pg_query("select * from tblcabang where kd_cabang='".cabang_ho."'"));


if($template=='t'){
$x5=365;
$y-=35;
if($lrow["kategori"]=='R2'){
	$pdf->addText($x2+18,$y, $fontsize,' '.$lrs_ho['nm_perusahaan']);
	$y-=10;
	$pdf->addText($x2+18,$y, $fontsize,' '.$lrs_ho['alamat']);
	
	$y-=40;
	$pdf->addText($x2+70,$y, $fontsize,date("d M Y",strtotime(($lrow['tgl_pengajuan']))));
	$pdf->addText($x3+10,$y, $fontsize,$fk_sbg);
	
	$y-=59;
	$pdf->addText($x5,$y, $fontsize,' '.$lrow["nm_merek"]);
	$y-=13;
	$pdf->addText($x5,$y, $fontsize,' '.$lrow["nm_tipe"]);
	$y-=13;
	$pdf->addText($x5,$y, $fontsize,' '.$lrow["warna"]);
	$y-=13;
	$pdf->addText($x5,$y, $fontsize,' '.$lrow["tahun"]);
	$y-=12;
	$pdf->addText($x5,$y, $fontsize,' '.$lrow["no_rangka"]);
	$y-=12;
	$pdf->addText($x5,$y, $fontsize,' '.$lrow["status_barang"]);
	$y-=11;
	$pdf->addText($x2+5,$y, $fontsize,' '.$lrow["nm_partner"]);
}elseif($lrow["kategori"]=='R4'){
	$y-=11;
	$pdf->addText($x2+18,$y, $fontsize,' '.$lrs_ho['nm_perusahaan']);
	$y-=10;
	$pdf->addText($x2+18,$y, $fontsize,' '.$lrs_ho['alamat']);
	
	$y-=40;
	$pdf->addText($x2+110,$y, $fontsize,date("d M Y",strtotime(($lrow['tgl_pengajuan']))));
	$pdf->addText($x3+50,$y, $fontsize,$fk_sbg);
	
	$y-=60;
	$x5+=5;
	$pdf->addText($x5,$y, $fontsize,' '.$lrow["nm_merek"]);
	$y-=13;
	$pdf->addText($x5,$y, $fontsize,' '.$lrow["nm_tipe"]);
	$y-=13;
	$pdf->addText($x5,$y, $fontsize,' '.$lrow["warna"]);
	$y-=13;
	$pdf->addText($x5,$y, $fontsize,' '.$lrow["tahun"]);
	$y-=12;
	$pdf->addText($x5,$y, $fontsize,' '.$lrow["no_rangka"]);
	$y-=12;
	$pdf->addText($x5,$y, $fontsize,' '.$lrow["status_barang"]);
	$y-=11;
	$pdf->addText($x2+5,$y, $fontsize,' '.$lrow["nm_partner"]);	
}


}

else{


$pdf->addText($x1+150,$y, $fontsize,'<b>BERITA ACARA SERAH TERIMA</b>');
$pdf->addText($x1+150,$y, $fontsize,'<b>________________________________</b>');
$y-=30;
$pdf->addText($x1,$y, $fontsize,'Kepada');
$pdf->addText($x2,$y, $fontsize,': '.$lrs_ho['nm_perusahaan']);
$y-=10;

$pdf->addText($x1,$y, $fontsize,'Alamat');
$pdf->addText($x2,$y, $fontsize,': '.$lrs_ho['alamat']);
$y-=5;

$pdf->y=$y;
$pdf->ezText('Pada hari ini kami mengaku menerima dalam keadaan baik, kendaraan yang dibiayai seperti tersebut dibawah ini yang merupakan pokok dari Perjanjian Pembiayaan dengan Pembayaran Secara Angsuran yang ditandatangani antara saudara dengan kami pada tanggal '.date("d M Y",strtotime(($lrow['tgl_pengajuan']))).' dengan nomor akad '.$fk_sbg.'.',$fontsize,array('justification'=>'full','left'=>11));
$y-=32;

$pdf->y=$y;
$pdf->ezText('Dan selanjutnya kami mengaku bahwa semua syarat dan ketentuan dari Perjanjian Pembiayaan dengan Pembayaran Secara Angsuran tersebut mulai berlaku pada tanggal ini.',$fontsize,array('justification'=>'full','left'=>11));
$y-=32;
/*$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');
*/
$pdf->selectFont('fonts/Times-Bold');
$pdf->addText($x1+174,$y, $fontsize,'<b>SPESIFIKASI</b>');
$pdf->selectFont('fonts/Times-Roman');

$pdf->ezSetY($y-5);

//$tgl_sistem='2021-02-09';
$i=0;
$data[$i]['data1'] = 'Uraian Kendaraan :';	
$data[$i]['data2'] =  'Merek : '.$lrow["nm_merek"];	
$i++;

$data[$i]['data1'] = '';	
$data[$i]['data2'] =  'Jenis : '.$lrow["nm_tipe"];	
$i++;

$data[$i]['data1'] = '';	
$data[$i]['data2'] =  'Warna : '.$lrow["warna"];	
$i++;

$data[$i]['data1'] = '';	
$data[$i]['data2'] =  'Tahun Pembuatan : '.$lrow["tahun"];	
$i++;

$data[$i]['data1'] = '';	
$data[$i]['data2'] =  'No Rangka : '.$lrow["no_rangka"];	
$i++;

$data[$i]['data1'] = '';	
$data[$i]['data2'] =  'Keadaan : '.$lrow["status_barang"];		
$i++;

$data[$i]['data1'] = 'Penjual : '.$lrow["nm_partner"];	
$data[$i]['data2'] =  '';	
$i++;

$data[$i]['data1'] = 'Penempatan Kendaraan : ';	
$data[$i]['data2'] =  '';	
$i++;


$judul['data1'] = '';
$judul['data2'] = '';

$lining['showHeadings'] = 0;
$lining['shaded'] = 0;
$lining['showLines'] = 1;
$lining['xPos'] = 300;
$lining['fontSize'] = 9;
$lining['rowGap'] = 1;
$lining['colGap'] = 1.5;

$lining['cols']['data1']['justification'] = 'left';
$lining['cols']['data2']['justification'] = 'left';
$size['data1'] = '235';
$size['data2'] = '235';

//$pdf->line(10,240,585,240);
$pdf->ezTable($data,$judul,'',$lining,$size);


$y=$pdf->y-15;
$pdf->selectFont('fonts/Times-Bold');
$pdf->addText($x1+144,$y, $fontsize,'<b>Perlengkapan Standard & Tambahan</b>');
$pdf->selectFont('fonts/Times-Roman');

$data=array();
if($lrow["kategori"]=='R4'){
	$data[$i]['data1'] = '[   ] Radio Tape';	
	$data[$i]['data2'] = '[   ] Vleg Racing';		
	$i++;
	
	$data[$i]['data1'] = '[   ] A/C';	
	$data[$i]['data2'] = '[   ] Central Lock';		
	$i++;
	
	$data[$i]['data1'] = '[   ] Dongkrak & Handle';	
	$data[$i]['data2'] = '[   ] Lighter';		
	$i++;
	
	$data[$i]['data1'] = '[   ] Tools Set';	
	$data[$i]['data2'] = '[   ] Lain-Lain';		
	$i++;
	
	$data[$i]['data1'] = '[   ] Buku Petunjuk';	
	$data[$i]['data2'] = '';		
	$i++;
	
	$data[$i]['data1'] = '[   ] Buku Service';	
	$data[$i]['data2'] = '';		
	$i++;
}

if($lrow["kategori"]=='R2'){
	$arr_detail1=array('Spedometer','Batok Spedometer','Lampu Depan','Tutup Batok Atas','Handle Klos/Kloping','Handle Rem',
	'Kaca Spion Kiri','Kaca Spion Kanan','Saklar Kiri','Saklar Kanan','Kunci Kontak','Lampu Sign Kanan Depan',
	'Lampu Sign Kiri Depan','Ban + Lingkar Depan','Gigi Kilometer','Tali Kilometer','Kepala Cakram',
	'Piringan Cakram','Sayap Depan','Shock Breaker Depan','Sebeng Kiri','Sebang Kanan','Sebang Tengah',
	'Tangki Minyak','Tutup Tangki Minyak','Minyak Dalam Tangki','Saring Hawa','Radiator','Pendingin /Vakum',
	'As Overan Gigi','Pedal Overan Gigi','As Engkol','Kick Stater','Stelan Timing','Tutup Timing','Tutup Klep',
	'Block');
	$arr_detail2=array('Deksel','Bak Magnet','Bak Kopling','Karbulator','Leher Karbulator','Desiklos','Busi',
	'Kepala Busi','Tempat Duduk','Swirn Arm','Shock Breaker Belakang','Tutup Rantai','Tutup Gigi Tarik Depan',
	'Tutup Baterai','Baterai','Cagak Tengah','Cagak Samping','Knalpot','Pijakan Besi Kaki Depan','Ban + Lingkar Belakang',
	'Body Cover Kanan','Body Cover Kiri','Less Body','Tutup Cover Body Belakang','Tiang Gawang','Lampu Belakang',
	'Lampu Sign Kanan Belakang','Lampu Sign Kiri Belakang','Sayap Belakang','Rem Belakang','Toolset','STNK',
	'Buku Speksi (Betor)','Plat Speksi (Betor)','Roda Samping (Betor)','Bak Kayu (Betor)','Seksi (Betor)');
	
	for($j=0;$j<=35;$j++){	
		$data[$i]['data1'] = '[   ] '.$arr_detail1[$j];	
		$data[$i]['data2'] = '[   ] '.$arr_detail2[$j];		
		$i++;
	}
}
$lining['showLines'] = 2;

$pdf->y=$y-10;
$pdf->ezTable($data,$judul,'',$lining,$size);

$y=$pdf->y-15;
$pdf->addText($x1,$y, $fontsize,'<b>SEBAGAI BUKTI DARI YANG TERSEBUT DIATAS</b> kami telah membuat Tanda Terima ini.');


$data=array();
$data[$i]['data1'] = 'Yang Menyerahkan';	
$data[$i]['data2'] = 'Yang Menerima';		
$i++;

$data[$i]['data1'] = '';	
$data[$i]['data2'] = '';		
$i++;
$data[$i]['data1'] = '';	
$data[$i]['data2'] = '';		
$i++;

$data[$i]['data1'] = '(_____________________________)';	
$data[$i]['data2'] = '(_____________________________)';		
$i++;

$data[$i]['data1'] = 'Nama Jelas T.Tangan & Stempel Dealer';	
$data[$i]['data2'] = 'Nama Jelas, T. Tangan';		
$i++;

$lining['cols']['data1']['justification'] = 'center';
$lining['cols']['data2']['justification'] = 'center';

$lining['showLines'] = 0;

$pdf->y=$y-10;
$pdf->ezTable($data,$judul,'',$lining,$size);

$y=$pdf->y-70;
//$pdf->addText($x1+80,$y, 7,'Perjanjian Ini Telah Disesuaikan dengan Ketentuan Peraturan Perundang-Undangan Termasuk Ketentuan');
//$y-=10;
//$pdf->addText($x1+180,$y, 7,'Otoritas Jasa Keuangan');
//$pdf->y=$y+55;
//$pdf->ezImage('../print/OJK_Logo.png','30','60','','left','');
//$pdf->ezImage('../print/logo_2.png','30','60','','left','');
$pdf->addPngFromFile('../print/logo_2.png', 35, 5,30,25);
$pdf->selectFont('fonts/Times-Roman');
//$pdf->addText(100, 25, 6,'"PERJANJIAN INI TELAH DISESUAIKAN DENGAN KETENTUAN PERATURAN PERUNDANG-UNDANGAN TERMASUK KETENTUAN PERATURAN OTORITAS JASA KEUANGAN"');
$pdf->addText(75, 15, 9,'PT. Capella Multidana Berizin dan Diawasi oleh Otoritas Jasa Keuangan');

}

$pdf->ezStream();

?>