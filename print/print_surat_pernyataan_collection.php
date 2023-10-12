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
	select count(1)as tunggakan,fk_sbg as fk_sbg1,max(angsuran_ke)as max,min(angsuran_ke)as min from data_fa.tblangsuran where tgl_bayar is null and tgl_jatuh_tempo<'".today_db."'
	group by fk_sbg
)as tblang on fk_sbg=fk_sbg1
left join tblpekerjaan on fk_pekerjaan=kd_pekerjaan
where fk_sbg = '".$fk_sbg."'";
$l_res=pg_query($query);	
$lrow=pg_fetch_array($l_res);
//showquery($query);
$tgl_cair_indo=date("d",strtotime($lrow['tgl_cair'])).' '.getMonthName(date("m",strtotime($lrow['tgl_cair'])),2).' '.date("Y",strtotime($lrow['tgl_cair']));

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


$pdf->addText($x1+200,$y, $fontsize,'<b>SURAT PERNYATAAN</b>');
//$pdf->addText($x1+160,$y, $fontsize,'<b>____________________________</b>');
$y-=40;
$pdf->addText($x1,$y, $fontsize,'Saya yang bertanda tangan dibawah ini:');
$y-=15;
$pdf->addText($x1+30,$y, $fontsize,'Nama ');
$pdf->addText($x1+150,$y, $fontsize,': '.$lrow["nm_customer"]);
$y-=15;
$pdf->addText($x1+30,$y, $fontsize,'Tempat / Tgl Lahir ');
$pdf->addText($x1+150,$y, $fontsize,': '.$lrow["tempat_lahir"].' / '.date("d-m-Y",strtotime($lrow["tgl_lahir"])));
$y-=15;
$pdf->addText($x1+30,$y, $fontsize,'Pekerjaan ');
$pdf->addText($x1+150,$y, $fontsize,': '.$lrow["nm_pekerjaan"]);
$y-=15;
$pdf->addText($x1+30,$y, $fontsize,'Alamat ');
$pdf->addText($x1+150,$y, $fontsize,': '.$lrow["alamat_ktp"]);
$y-=15;
$pdf->addText($x1+30,$y, $fontsize,'No. KTP ');
$pdf->addText($x1+150,$y, $fontsize,': '.$lrow["no_id"]);
$y-=25;
$pdf->addText($x1,$y, $fontsize,'Adalah benar konsumen '.$lrow["nm_perusahaan"].' atas pembiayaan kendaraan dengan spesifikasi sbb:');
$y-=15;
$pdf->addText($x1+30,$y, $fontsize,'No Rangka ');
$pdf->addText($x1+150,$y, $fontsize,': '.$lrow["no_rangka"]);
$y-=15;
$pdf->addText($x1+30,$y, $fontsize,'No Mesin ');
$pdf->addText($x1+150,$y, $fontsize,': '.$lrow["no_mesin"]);
$y-=15;
$pdf->addText($x1+30,$y, $fontsize,'Merk/Type ');
$pdf->addText($x1+150,$y, $fontsize,': '.$lrow["nm_merek"].' / '.$lrow['nm_tipe']);
$y-=15;
$pdf->addText($x1+30,$y, $fontsize,'Warna ');
$pdf->addText($x1+150,$y, $fontsize,': '.$lrow["warna"]);
$y-=15;
$pdf->addText($x1+30,$y, $fontsize,'Tahun ');
$pdf->addText($x1+150,$y, $fontsize,': '.$lrow["tahun"]);
$y-=15;
$pdf->addText($x1+30,$y, $fontsize,'No Polisi ');
$pdf->addText($x1+150,$y, $fontsize,': '.$lrow["no_polisi"]);
$y-=25;

$pdf->addText($x1,$y, $fontsize,'Dengan ini menyatakan :');
$pdf->y=$y;

$x1_pasal=26;
$x2_pasal=40;

$arr=array(
1=>'Bahwa benar saya telah menunggak angsuran kendaraan sebagaimana tersebut diatas selama '.$lrow["tunggakan"].' bulan yaitu untuk angsuran ke '.$lrow["min"].' s/d angsuran ke '.$lrow["max"].'',
2=>'Bahwa dengan menunggaknya angsuran kendaraan tersebut, saya mengakui telah cidera janji (wanprestasi) dan akan membayar angsuran yang tertunggak tersebut pada tanggal ……………………… sebanyak  '.$lrow["tunggakan"].' angsuran.',
3=>'Apabila saya tidak melakukan pembayaran angsuran sebagaimana poin (2) di atas, saya bersedia dengan sukarela menyerahkan kendaraan tersebut diatas kepada '.$lrow["nm_perusahaan"].' dan apabila saya tidak mematuhinya maka saya bersedia di laporkan ke pihak yang berwajib.',
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
$pdf->ezText('Demikian surat pernyataan ini saya buat dengan sebenarnya tanpa ada paksaan dari pihak manapun.',$fontsize,array('justification'=>'full','left'=>11));
$y-=70;

$pdf->addText($x1,$y, $fontsize,$lrow['nm_cabang'].','.date("d").' '.getMonthName(date("m"),2).' '.date("Y"));
$y-=30;
$pdf->addText($x1,$y, $fontsize,'Yang membuat pernyataan,');
$y-=35;
$pdf->addText($x1+47,$y, $fontsize-2,'Materai');

$y-=50;
$pdf->addText($x1,$y, $fontsize,'(__________________)');
$y-=12;
$pdf->addText($x1,$y, $fontsize-2,'Nb: wajib tulis tangan konsumen');


$pdf->ezStream();

?>
