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
	select count(1)as tunggakan,fk_sbg as fk_sbg1,max(angsuran_ke)as max,min(angsuran_ke)as min,max(tgl_jatuh_tempo)as max_jt,min(tgl_jatuh_tempo)as min_jt,sum(nilai_angsuran)as total_tunggakan from data_fa.tblangsuran where tgl_bayar is null and tgl_jatuh_tempo<'".today_db."'
	group by fk_sbg
)as tblang on fk_sbg=fk_sbg1
left join tblpekerjaan on fk_pekerjaan=kd_pekerjaan
where fk_sbg = '".$fk_sbg."'";
$l_res=pg_query($query);	
$lrow=pg_fetch_array($l_res);
//showquery($query);
$tgl_cair_indo=date("d",strtotime($lrow['tgl_cair'])).' '.getMonthName(date("m",strtotime($lrow['tgl_cair'])),2).' '.date("Y",strtotime($lrow['tgl_cair']));
$max_jt=date("d",strtotime($lrow['max_jt'])).' '.getMonthName(date("m",strtotime($lrow['max_jt'])),2).' '.date("Y",strtotime($lrow['max_jt']));
$min_jt=date("d",strtotime($lrow['min_jt'])).' '.getMonthName(date("m",strtotime($lrow['min_jt'])),2).' '.date("Y",strtotime($lrow['min_jt']));

$fk_cabang=$lrow["fk_cabang"];
$kacab=kacab($lrow["fk_cabang"],$lrow["tgl_pengajuan"]);
$lrow["nm_kacab"]=$kacab["nm_depan"];
$lrow["alamat_kacab"]=$kacab["alamat"];

$pdf = new Cezpdf('F4');  
$pdf->setLineStyle(1);

$pdf->ez['topMargin'] = (150);
$pdf->ez['bottomMargin']=5;
$pdf->ez['leftMargin']=25;
$pdf->ez['rightMargin']=40;
/*$all = $pdf->openObject();
$pdf->saveState();
*/
//Header
$fontsize=11;
$y=890;
$x1 = 36;
$x2 = 130;

$x3 = 308;
$x4 = 378;
$x_right=67;

$pdf->selectFont('fonts/Helvetica');

$lrs_ho=pg_fetch_array(pg_query("select * from tblcabang where kd_cabang='".cabang_ho."'"));
/*$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');
*/


$pdf->addText($x1,$y, $fontsize,'Hal : <b>SOMASI</b>');
//$pdf->addText($x1+160,$y, $fontsize,'<b>____________________________</b>');
$y-=20;

$pdf->addText($x1,$y, $fontsize,'Kepada Yth.');
$y-=12;
$pdf->addText($x1,$y, $fontsize,'Bapak/Ibu/Sdr/Sdri:');
$y-=12;
$pdf->addText($x1,$y, $fontsize,$lrow["nm_customer"]);
$y-=12;
$pdf->addText($x1,$y, $fontsize,$lrow["alamat_tinggal"]);
$y-=12;
$pdf->addText($x1,$y, $fontsize,'Di Tempat');
$y-=30;
$pdf->addText($x1,$y, $fontsize,'Dengan hormat,');
$y-=10;
$pdf->y=$y;
$pdf->ezText('Sehubungan dengan perjanjian yang Bapak/Ibu/Sdr/Sdri tanda tangani atas fasilitas pembiayaan 1 (satu) unit kendaraan dengan No.Polisi : '.$lrow["no_polisi"].' sebagaimana Perjanjian Pembiayaan Dengan Pembayaran Secara Angsuran Nomor : '.$fk_sbg.', tanggal '.$tgl_cair_indo.', maka terkait hal tersebut bersama ini kami beritahukan kepada Bapak/Ibu/Sdr/Sdri hal-hal sebagai berikut :',$fontsize,array('justification'=>'full','left'=>11));

$y-=60;
$x1_pasal=14;
$x2_pasal=40;

$arr=array(
1=>'Bahwa sesuai dengan perjanjian pembiayaan di atas, sebagai Debitur (Bapak/Ibu/Sdr/Sdri) berkewajiban untuk mematuhi dan melaksanakan seluruh isi perjanjian yang telah disepakati bersama tersebut.',
2=>'Bahwa pada kenyataannya, Bapak/Ibu/Sdr/Sdri telah melalaikan isi perjanjian tersebut dengan tidak melaksanakan kewajiban untuk pembayaran  angsuran/cicilan kendaraan tersebut di atas selama '.$lrow["tunggakan"].' bulan terhitung pada jatuh tempo tanggal '.$min_jt.' s.d '.$max_jt.'  dengan total angsuran yang tertunggak sebesar '.convert_money("",$lrow["total_tunggakan"]).'. ('.convert_terbilang($lrow["total_tunggakan"]).') belum termasuk denda yang timbul karena keterlambatan pembayaran angsuran.',
3=>'Bahwa berdasarkan kondisi tersebut di atas, kami minta kepada Bapak/Ibu/Sdr/Sdri untuk segera melakukan pembayaran angsuran yang tertunggak tersebut paling lambat 7 (Tujuh) hari dari tanggal surat somasi ini untuk menghindari sanksi denda keterlambatan yang semakin besar serta terhindar dari catatan pada SLIK (Sistem Layanan Informasi Keuangan) OJK dengan kualitas pembayaran kurang baik yang nantinya akan menghambat transaksi keuangan serta mempengaruhi kredibilitas (nama baik) Bapak/Ibu/Sdr/Sdri di Bank maupun Lembaga Keuangan Non Bank (LKNB)',
4=>'Bahwa jika batas waktu pembayaran tunggakan angsuran yang telah kami berikan sebagaimana poin (3) di atas belum juga dilakukan pembayaran maka  kami menghimbau Bapak/Ibu/Sdr/Sdri agar menyerahkan unit kendaraan yang  dibiayai tersebut kepada '.$lrow["nm_perusahaan"].' guna menghindari tindakan hukum sesuai dengan perjanjian pembiayaan ataupun upaya lainnya sesuai ketentuan hukum yang berlaku.',

);

$pdf->y=$y;
$y=$pdf->y;
for($i=1;$i<=count($arr);$i++){
	$pdf->ezText($i.'.',$fontsize,array('justification'=>'full','left'=>$x1_pasal));
	$pdf->y=$y;
	$pdf->ezText($arr[$i],$fontsize,array('justification'=>'full','left'=>$x2_pasal));
	$y=$pdf->y;
}

$y-=10;
$pdf->y=$y;
$pdf->ezText('Demikian pemberitahuan ini disampaikan atas kerjasamanya diucapkan terima kasih.',$fontsize,array('justification'=>'full','left'=>11));
$y-=70;

$pdf->addText($x1,$y, $fontsize,$lrow['nm_cabang'].','.date("d").' '.getMonthName(date("m"),2).' '.date("Y"));
$y-=30;
$pdf->addText($x1,$y, $fontsize,'Hormat Kami,');
$y-=15;
$pdf->addText($x1,$y, $fontsize,$lrow["nm_perusahaan"]);
$y-=35;
$y-=50;
$nm1=get_karyawan_by_jabatan('KAPOS',$fk_cabang);

$pdf->addText($x1,$y, $fontsize,$nm1.'');
$pdf->addText($x1,$y, $fontsize,'__________________');
$y-=12;
$pdf->addText($x1,$y, $fontsize,'Collection Dept. Head');

$pdf->ezStream();

?>
