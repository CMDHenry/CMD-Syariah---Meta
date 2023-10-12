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

$template = $_REQUEST['flag'];

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
$tgl_pengajuan=date("d",strtotime($lrow['tgl_pengajuan'])).' '.getMonthName(date("m",strtotime($lrow['tgl_pengajuan'])),2).' '.date("Y",strtotime($lrow['tgl_pengajuan']));
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
$tgl_pengajuan=date("d",strtotime($lrow['tgl_pengajuan'])).' '.getMonthName(date("m",strtotime($lrow['tgl_pengajuan'])),2).' '.date("Y",strtotime($lrow['tgl_pengajuan']));
$fk_cabang=$lrow["fk_cabang"];
$kacab=kacab($lrow["fk_cabang"],$lrow["tgl_pengajuan"]);
$lrow["nm_kacab"]=$kacab["nm_depan"];
$lrow["alamat_kacab"]=$kacab["alamat"];

$pdf = new Cezpdf('A4');  
$pdf->setLineStyle(1);

$pdf->ez['topMargin'] = (150);
$pdf->ez['bottomMargin']=5;
$pdf->ez['leftMargin']=40;
$pdf->ez['rightMargin']=60;
/*$all = $pdf->openObject();
$pdf->saveState();
*/
//Header
$fontsize=9;
$y=800;
$x1 = 36;
$x2 = 130;

$x3 = 308;
$x4 = 378;
$x_right=67;

$pdf->selectFont('fonts/Times-Roman');

$lrs_ho=pg_fetch_array(pg_query("select * from tblcabang where kd_cabang='".cabang_ho."'"));

if($template=='t'){
$y-=62;
$x5='180';
$pdf->addText($x5,$y, $fontsize,$lrow["nm_penjamin"]);
$y-=18;
$pdf->addText($x5,$y, $fontsize,$lrow['alamat_penjamin']);
$y-=28;
$pdf->addText($x5,$y, $fontsize,"");	

$y-=55;
$pdf->addText($x4+100,$y, $fontsize,$lrow["nm_cabang"]);

$y-=29;
$pdf->addText($x2+120,$y, $fontsize,$fk_sbg);
$pdf->addText($x4+10,$y, $fontsize,$tgl_pengajuan);	

$y-=247;
$pdf->addText($x1+50,$y, $fontsize,$lrow['nm_cabang']);
$pdf->addText($x2+40,$y, $fontsize,$tgl_pengajuan);
$y-=125;
$pdf->addText($x1+50,$y, $fontsize,$nm_pihak1);
$pdf->addText($x1+380,$y, $fontsize,$lrow["nm_penjamin"]);

	
}

else{
$pdf->addText($x1+180,$y, $fontsize,'<b>SURAT PERNYATAAN JAMINAN</b>');
$pdf->addText($x1+180,$y, $fontsize,'<b>_______________________________</b>');
$y-=40;
$pdf->addText($x1,$y, $fontsize,'Saya yang bertanda tangan dibawah ini:');

/*$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');
*/

$pdf->ezSetY($y);

//$tgl_sistem='2021-02-09';
$i=0;

$data[$i]['data1'] = 'Nama ';	
$data[$i]['data2'] =  ': '.strtoupper($lrow['nm_penjamin']);	
$i++;

$data[$i]['data1'] = 'Alamat ';	
$data[$i]['data2'] =  ': '.strtoupper($lrow['alamat_penjamin']);	
$i++;


$data[$i]['data1'] = 'Pekerjaan';	
$data[$i]['data2'] =  ': ';	
$i++;


$judul['data1'] = '';
$judul['data2'] = '';

$lining['showHeadings'] = 0;
$lining['shaded'] = 0;
$lining['showLines'] = 0;
$lining['xPos'] = 270;
$lining['fontSize'] = 9;
$lining['rowGap'] = 1.5;
$lining['colGap'] = 1.5;

$lining['cols']['data1']['justification'] = 'left';
$lining['cols']['data2']['justification'] = 'left';
$size['data1'] = '65';
$size['data2'] = '350';

//$pdf->line(10,240,585,240);
$pdf->ezTable($data,$judul,'',$lining,$size);

$y=$pdf->y-25;
$pdf->addText($x1,$y, $fontsize,'Dengan ini menyatakan :');

$pdf->y=$y;

$x1_pasal=26;
$x2_pasal=40;

$arr=array(
1=>'Bahwa yang bertandatangan mengetahui benar bahwa yang selanjutnya disebut Debitur, benar-benar dan dengan sah berutang kepada '.$lrow["nm_perusahaan"].' berkedudukan di '.$lrow["nm_cabang"].' yang selanjutnya disebut Kreditur, seperti yang tercantum dalam perjanjian pembiayaan dengan pembayaran secara angsuran dengan nomor '.$fk_sbg.' tertanggal '.$tgl_pengajuan.'.',
2=>'Bahwa untuk menjamin kembalinya seluruh utang Debitur kepada Kreditur, berdasarkan perjanjian tersebut di atas, maka yang bertanda tangan selanjutnya disebut juga sebagai PENJAMIN, dengan ini berjanji serta mengikatkan diri untuk membayar kepada Kreditur dengan segera dan secara sekaligus seluruh jumlah uang yang terutang atas permintaan dari Debitur tersebut.',
3=>'Penjamin dengan ini secara tegas menyatakan melepaskan hak-hak yang diberikan oleh seorang penjamin berdasarkan Undang – Undang, terutama:',
);
$pdf->y=$y;
$y=$pdf->y;
for($i=1;$i<=count($arr);$i++){
	$pdf->ezText('-',$fontsize,array('justification'=>'full','left'=>$x1_pasal));
	$pdf->y=$y;
	$pdf->ezText($arr[$i],$fontsize,array('justification'=>'full','left'=>$x2_pasal));
	$y=$pdf->y;
}

$arr=array(
1=>'Hak penjamin untuk memohon kepada Kreditur bahwa harta kekayaan Debitur terlebih dahulu harus dipergunakan untuk pembayaran kembali seluruh jumlah yang terutang oleh Debitur kepada Kreditur',
2=>'Hak memohon kepada Debitur untuk membagi – bagi utang Debitur yang dijamin oleh Penjamin, diantara penjamin lainnya.',
3=>'Hak-hak yang membebaskan seorang penjamin dari tanggung jawab dan tanggungan sebagaimana termaktub dalam pasal 1430, 1847, 1848 dan 1849 Kitab Undang – Undang Hukum Perdata Indonesia.',
);
$pdf->y=$y;
$y=$pdf->y;
$alpha=array("a","b","c");
for($i=1;$i<=count($arr);$i++){
	$pdf->ezText($alpha[$i-1].'.',$fontsize,array('justification'=>'full','left'=>$x1_pasal+14));
	$pdf->y=$y;
	$pdf->ezText($arr[$i],$fontsize,array('justification'=>'full','left'=>$x2_pasal+16));
	$y=$pdf->y;
}

$pdf->ezText('Demikian Surat Pernyataan Jaminan ini dibuat untuk dipergunakan sebagaimana mestinya, dan dalam keadaan sadar, sehat jasmani dan rohani serta tidak mendapatkan paksaan dari pihak manapun juga.',$fontsize,array('justification'=>'full','left'=>11));
$y-=70;

$pdf->addText($x1,$y, $fontsize,$lrow['nm_cabang'].','.$tgl_pengajuan);
$y-=30;
$pdf->addText($x1,$y, $fontsize,'Menyetujui,');
$y-=15;
$pdf->addText($x1,$y, $fontsize,$lrow["nm_perusahaan"]);
$pdf->addText($x1+350,$y, $fontsize,'Yang bertanda tangan,');
$y-=70;
$pdf->addText($x1,$y, $fontsize,'('.$nm_pihak1.')');
$pdf->addText($x1+350,$y, $fontsize,'('.$lrow["nm_penjamin"].')');
$y-=12;
//$pdf->addText($x1+350,$y, $fontsize,'Penjamin');

$y-=90;
$pdf->addPngFromFile('../print/logo_2.png', 35, 5,30,25);
$pdf->addText(75, 9, $fontsize,'PT. Capella Multidana Berizin dan Diawasi oleh Otoritas Jasa Keuangan');
}
$pdf->ezStream();

?>
