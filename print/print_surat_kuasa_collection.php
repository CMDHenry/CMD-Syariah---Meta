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

$fk_sbg = $_REQUEST['id_edit'];
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
left join(
	select count(1)as tunggakan,fk_sbg as fk_sbg1 from data_fa.tblangsuran where tgl_bayar is null and tgl_jatuh_tempo<'".today_db."'
	group by fk_sbg
)as tblang on fk_sbg=fk_sbg1
left join(
	select nm_depan as nm_head_collection,fk_cabang_karyawan from tblkaryawan 
	left join tbljabatan on fk_jabatan=kd_jabatan
	where nm_jabatan='KAPOS'
)as tblkaryawan on fk_cabang_karyawan=kd_cabang
where fk_sbg = '".$fk_sbg."'";
$l_res=pg_query($query);	
$lrow=pg_fetch_array($l_res);
//showquery($query);

$fk_cabang=$lrow["fk_cabang"];


$query1="
select nm_depan as nm_collector,fk_cabang_karyawan from tblkaryawan 
left join tbljabatan on fk_jabatan=kd_jabatan
where nm_jabatan='Kolektor' and fk_cabang_karyawan = '".$fk_cabang."'";
$l_res1=pg_query($query1);	
$no=1;
while($lrow1=pg_fetch_array($l_res1)){
	$col[$i]=$lrow["nm_depan"];
	$i++;
}

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
$fontsize=11;
$y=890;
$x1 = 71;
$x2 = 130;

$x3 = 308;
$x4 = 378;
$x_right=67;

$pdf->selectFont('fonts/Helvetica');

$lrs_ho=pg_fetch_array(pg_query("select * from tblcabang where kd_cabang='".cabang_ho."'"));
$pdf->addText($x1+180,$y, $fontsize+3,'<b>SURAT KUASA<b>');
$pdf->addText($x1+180,$y, $fontsize,'<b>_________________<b>');

$y-=30;
$pdf->addText($x1,$y, $fontsize,'Yang bertanda tangan dibawah ini:');

/*$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');
*/

$y-=15;

$pdf->addText($x1+30,$y, $fontsize,'Nama ');
$pdf->addText($x1+100,$y, $fontsize,': '.$lrow["nm_head_collection"]);
$y-=15;
$pdf->addText($x1+30,$y, $fontsize,'Jabatan ');
$pdf->addText($x1+100,$y, $fontsize,': Collection Dept. Head');
$y-=15;
$tgl_cair_indo=date("d",strtotime($lrow['tgl_cair'])).' '.getMonthName(date("m",strtotime($lrow['tgl_cair'])),2).' '.date("Y",strtotime($lrow['tgl_cair']));


$pdf->y=$y;
$pdf->ezText('Dalam hal ini bertindak untuk dan atas nama '.$lrow["nm_perusahaan"].'. Berdasarkan Surat Kuasa yang diberikan Debitor kepada '.$lrow["nm_perusahaan"].' selaku Kreditor dengan Hak Substitusi untuk melakukan eksekusi terhadap objek jaminan fidusia sesuai Perjanjian Pembiayaan Konsumen dan Penyerahan Hak Milik Secara Fiducia No. '.$lrow['no_sertifikat_fidusia'].' bila ternyata menurut pendapat Kreditor bahwa Debitor telah melalaikan kewajiban dan/atau tidak mentaati syarat dan ketentuan sebagaimana diatur dalam Perjanjian dimaksud, dari pihak manapun di lokasi manapun juga, tanpa terkecuali',$fontsize,array('justification'=>'full','left'=>11));
$y-=120;
$pdf->y=$y;

$pdf->addText($x1,$y, $fontsize,'Maka dengan ini menerangkan memberi kuasa penuh kepada:');
$y-=15;
$pdf->addText($x1+30,$y, $fontsize,'Nama ');
$pdf->addText($x1+100,$y, $fontsize,'1. '.$col['1']);
$y-=15;
$pdf->addText($x1+100,$y, $fontsize,'2. '.$col['2']);
$y-=15;
$pdf->addText($x1+100,$y, $fontsize,'3. '.$col['3']);
$y-=15;
$pdf->addText($x1,$y, $fontsize,'<i>Baik bersama – sama maupun masing-masing atau sendiri</i>');
$y-=15;
$pdf->addText($x1,$y, $fontsize,'Untuk melaksanakan penarikan atas 1 (satu) unit kendaraan bermotor dengan data sebagai berikut ');
$y-=25;

$pdf->selectFont('fonts/Helvetica-Bold');

$pdf->addText($x1+30,$y, $fontsize,'No Polisi ');
$pdf->addText($x1+100,$y, $fontsize,': '.$lrow["no_polisi"]);
$y-=15;
$pdf->addText($x1+30,$y, $fontsize,'No Chasis ');
$pdf->addText($x1+100,$y, $fontsize,': '.$lrow["no_rangka"]);
$y-=15;
$pdf->addText($x1+30,$y, $fontsize,'No Engine ');
$pdf->addText($x1+100,$y, $fontsize,': '.$lrow["no_mesin"]);
$y-=15;
$pdf->addText($x1+30,$y, $fontsize,'Merk/Type ');
$pdf->addText($x1+100,$y, $fontsize,': '.$lrow["nm_merek"].' / '.$lrow['nm_tipe']);
$y-=15;
$pdf->addText($x1+30,$y, $fontsize,'Tahun ');
$pdf->addText($x1+100,$y, $fontsize,': '.$lrow["tahun"]);
$y-=15;
$pdf->addText($x1+30,$y, $fontsize,'Atas Nama ');
$pdf->addText($x1+100,$y, $fontsize,': '.$lrow["nm_customer"]);
$y-=15;
$pdf->addText($x1+30,$y, $fontsize,'Alamat ');
$pdf->addText($x1+100,$y, $fontsize-1,': '.$lrow["alamat_ktp"]);
$pdf->selectFont('fonts/Helvetica');
$y-=25;
$pdf->addText($x1,$y, $fontsize,'Yang mana kendaraan tersebut di atas telah menunggak:');
$y-=15;
$pdf->addText($x1+30,$y, $fontsize,'Selama ');
$pdf->addText($x1+150,$y, $fontsize,': '.$lrow['tunggakan'].' bulan');
$y-=15;
$pdf->addText($x1+30,$y, $fontsize,'Angsuran per bulan ');
$pdf->addText($x1+150,$y, $fontsize,': '.convert_money("Rp",$lrow["angsuran_bulan"]));
$y-=15;
$total_tunggakan=$lrow['tunggakan']*$lrow["angsuran_bulan"];
$pdf->addText($x1+30,$y, $fontsize,'Sejumlah ');
$pdf->addText($x1+150,$y, $fontsize,': '.$lrow['tunggakan'].' X '.convert_money("Rp",$lrow["angsuran_bulan"]).' = '.convert_money("Rp",$total_tunggakan));
$y-=15;
$pdf->addText($x1+150,$y, $fontsize,'('.convert_terbilang($total_tunggakan).' )');
$y-=10;
$pdf->y=$y;
$pdf->ezText('dan belum termasuk denda. Diperkirakan kendaraan tersebut berada di '.$lrow["nm_cabang"].' dan sekitarnya.',$fontsize,array('justification'=>'full','left'=>11));
$y-=40;

$pdf->y=$y;
$pdf->ezText('Penerima Kuasa diberi hak untuk membuat tanda-terima dan menandatangani semua surat-surat yang diperlukan dalam dan berhubungan dalam rangka pemberian surat kuasa ini. Setelah diadakan penarikan/penerimaan kendaraan tersebut selanjutnya diserahkan / disimpan di gudang '.$lrow["nm_perusahaan"],$fontsize,array('justification'=>'full','left'=>11));
$y-=70;

$pdf->addText($x1,$y, $fontsize,'Demikian Surat Kuasa ini diperbuat dengan sebenarnya agar dapat dipergunakan seperlunya.');
$y-=25;

$pdf->addText($x1+300,$y, $fontsize,$lrow['nm_cabang'].','.date("d").' '.getMonthName(date("m"),2).' '.date("Y"));
$y-=30;
$pdf->addText($x1,$y, $fontsize,'PENERIMA KUASA,');
$pdf->addText($x1+300,$y, $fontsize,'PEMBERI KUASA,');
$y-=15;
$pdf->addText($x1,$y, $fontsize,'1. '.$col['1']);
$y-=15;
$pdf->addText($x1,$y, $fontsize,'2. '.$col['2']);
$y-=15;
$pdf->addText($x1,$y, $fontsize,'3. '.$col['3']);
$y-=25;

$pdf->addText($x1+300,$y, $fontsize,$lrow["nm_head_collection"]);

//$pdf->addText($x1,$y, $fontsize,'_________________');
//$pdf->addText($x1+300,$y, $fontsize,'_________________');

/*$y-=90;
$pdf->addText($x1+80,$y, 7,'Perjanjian Ini Telah Disesuaikan dengan Ketentuan Peraturan Perundang-Undangan Termasuk Ketentuan');
$y-=20;
$pdf->addText($x1+180,$y, 7,'Otoritas Jasa Keuangan');
$pdf->y=$y+65;
$pdf->ezImage('../print/OJK_Logo.png','30','60','','left','');
*/
$pdf->ezStream();

?>