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

$id_edit = $_REQUEST['fk_sbg'];
$template=$_REQUEST['flag'];
$tgl = convert_date_english($_REQUEST['tgl']);
//echo $tgl;
$query="
select * from  (
	select lama_pinjaman as tenor,biaya_penyimpanan as total_bunga,no_sbg as fk_sbg,nm_bpkb,no_fatg as no_fatg1,* from data_gadai.tblproduk_cicilan
	left join data_gadai.tbltaksir_umum on no_fatg=fk_fatg
	left join tblpartner on fk_partner_asuransi=kd_partner
)as tblproduk_cicilan  
left join tblcustomer on fk_cif = no_cif 
left join tblcabang on fk_cabang=kd_cabang
left join(
	select no_fatg as no_fatg2 ,* from viewkendaraan
	left join tblpartner on fk_partner_dealer=kd_partner
)as tblbarang on no_fatg2=no_fatg1
left join tblpekerjaan on fk_pekerjaan=kd_pekerjaan
where fk_sbg = '".$id_edit."'";
$l_res=pg_query($query);	
$lrow=pg_fetch_array($l_res);
//showquery($query);
$query_produk="select * from tblproduk_detail_tenor where fk_produk='".$lrow["fk_produk"]."'";
$l_p=pg_query($query_produk);	
$lrow_produk=pg_fetch_array($l_p);

$tgl_cair_indo=date("d",strtotime($lrow['tgl_cair'])).' '.getMonthName(date("m",strtotime($lrow['tgl_cair'])),2).' '.date("Y",strtotime($lrow['tgl_cair']));
$fk_cabang=$lrow["fk_cabang"];
$tgl_pengajuan=date("d",strtotime($lrow['tgl_pengajuan'])).' '.getMonthName(date("m",strtotime($lrow['tgl_pengajuan'])),2).' '.date("Y",strtotime($lrow['tgl_pengajuan']));
$pdf = new Cezpdf('A4'); 
//$pdf->ezImage('permohonan.png',0, 0, 0);
$pdf->setLineStyle(1);

$pdf->ez['topMargin'] = (150);
$pdf->ez['bottomMargin']=5;
$pdf->ez['leftMargin']=75;
$pdf->ez['rightMargin']=80;
/*$all = $pdf->openObject();
$pdf->saveState();
*/
if($template=='t'){
$fontsize=9;
$y=800;
$x1 = 67;
$x2 = 157;

$x3 = 293;
$x4 = 363;
$x_right=76;

$pdf->selectFont('fonts/Times-Roman');

$lrs_ho=pg_fetch_array(pg_query("select * from tblcabang where kd_cabang='".cabang_ho."'"));
$kacab=kacab($lrow["fk_cabang"],$lrow["tgl_cair"]);
$lrow["nm_kacab"]=$kacab["nm_depan"];
$alamat_kacab=$kacab["alamat"];

$y-=125;
$x2+=12;
$pdf->addText($x2,$y, $fontsize,$lrow["nm_customer"]);
$y-=13;

$pdf->addText($x2,$y, $fontsize,$lrow["alamat_ktp"]);
$y-=21;
$pdf->addText($x2,$y, $fontsize,$lrow["no_id"]);

$y-=57;
$pdf->addText($x2, $y, $fontsize,$lrow["nm_merek"]);
$y-=12;
$pdf->addText($x2, $y, $fontsize,$lrow["nm_tipe"]);
$y-=11;
$pdf->addText($x2, $y, $fontsize,$lrow["no_rangka"]);
$y-=12;
$pdf->addText($x2, $y, $fontsize,$lrow["no_mesin"]);
$y-=12;
$pdf->addText($x2, $y, $fontsize,$lrow["tahun"]);
$y-=11;
$pdf->addText($x2, $y, $fontsize,$lrow["warna"]);
$y-=11;
$pdf->addText($x2, $y, $fontsize,$lrow["lama_pinjaman"]);

$y-=37;
$pdf->addText($x1, $y, $fontsize,$lrow["nm_partner"]);
$pdf->addText($x4, $y, $fontsize,$tgl_pengajuan);
$y-=77;
$pdf->addText($x1,$y, $fontsize,$lrow['nm_cabang']);
$pdf->addText($x1+146,$y, $fontsize,$tgl_pengajuan);
$y-=105;
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

$pdf->addText($x1+105,$y, $fontsize+3,'<b>SURAT PERNYATAAN DAN PERMOHONAN<b>');
$y-=40;
$pdf->addText($x1,$y, $fontsize,'Kepada Yth');

$y-=12;
$pdf->addText($x1,$y, $fontsize,'Pimpinan '.$lrow["nm_perusahaan"]);

$y-=28;
$pdf->addText($x1,$y, $fontsize,'Dengan Hormat, ');

$y-=12;
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

$y=$pdf->y-5;
$pdf->y=$y;
$pdf->ezText('Dengan ini menyatakan benar telah membeli 1 (satu) unit kendaraan secara kredit melalui PT. Capella Multidana dengan data berikut :',$fontsize,array('justification'=>'full','left'=>-8));
$x1_head = 81;
$x2_rinci = 147;

$y-=25;
$pdf->addText($x1_head, $y-12, $fontsize,'Merk');
$pdf->addText($x2_rinci, $y-12, $fontsize,': '.$lrow["nm_merek"]);
$y-=12;
$pdf->addText($x1_head, $y-12, $fontsize,'Type');
$pdf->addText($x2_rinci, $y-12, $fontsize,': '.$lrow["nm_tipe"]);
$y-=12;
$pdf->addText($x1_head, $y-12, $fontsize,'No Rangka');
$pdf->addText($x2_rinci, $y-12, $fontsize,': '.$lrow["no_rangka"]);
$y-=12;
$pdf->addText($x1_head, $y-12, $fontsize,'No Mesin');
$pdf->addText($x2_rinci, $y-12, $fontsize,': '.$lrow["no_mesin"]);
$y-=12;
$pdf->addText($x1_head, $y-12, $fontsize,'Tahun');
$pdf->addText($x2_rinci, $y-12, $fontsize,': '.$lrow["tahun"]);
$y-=12;
$pdf->addText($x1_head, $y-12, $fontsize,'Warna');
$pdf->addText($x2_rinci, $y-12, $fontsize,': '.$lrow["warna"]);
$y-=12;
$pdf->addText($x1_head, $y-12, $fontsize,'Tenor Kredit');
$pdf->addText($x2_rinci, $y-12, $fontsize,': '.$lrow["lama_pinjaman"].' Bulan');

$y-=20;
$pdf->y=$y;
$arr=array(
1=>"Dan unit telah saya terima dengan baik yang dibuktikan dengan Berita Acara SerahTerima (BAST) dari Dealer ".$lrow["nm_partner"]." pada tanggal ".$tgl_pengajuan." dan saya mohon kepada PT. Capella Multidana untuk mengasuransikan unit tersebut melalui perusahaan asuransi yang dianggap baik oleh PT. Capella Multidana selama tenor kredit terhitung sejak tanggal serah terima yang telah disebutkan di atas.",
);
$pdf->y=$y;
$y-=15;
for($i=1;$i<=count($arr);$i++){
	$pdf->ezText($arr[$i],$fontsize,array('justification'=>'full','left'=>-8));
	$y-=30;
}

$arr=array(
1=>"Demikianlah surat permohonan ini saya buat untuk dapat dipergr.rnakan seperlunya. Atas bantuan dan perhatian yang diberikan saya ucapkan terima kasih.",
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