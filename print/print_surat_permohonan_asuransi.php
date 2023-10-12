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

$template=$_REQUEST['flag'];
$fk_sbg = $_REQUEST['fk_sbg'];
$tgl = convert_date_english($_REQUEST['tgl']);
//echo $tgl;
$query="
select * from  (
	select lama_pinjaman as tenor,biaya_penyimpanan as total_bunga,nm_partner as nm_asuransi,no_sbg as fk_sbg,nm_bpkb,no_fatg as no_fatg1,* from data_gadai.tblproduk_cicilan
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
where fk_sbg = '".$fk_sbg."'";
$l_res=pg_query($query);	
$lrow=pg_fetch_array($l_res);
//showquery($query);

$nilai_asr_jiwa=$lrow["nilai_asr_jiwa"];
if(!$lrow['tgl_cair'])$lrow['tgl_cair']=$lrow['tgl_pengajuan'];
$tgl_cair_indo=date("d",strtotime($lrow['tgl_cair'])).' '.getMonthName(date("m",strtotime($lrow['tgl_cair'])),2).' '.date("Y",strtotime($lrow['tgl_cair']));
$tgl_jt_indo=date("d",strtotime($lrow['tgl_jatuh_tempo'])).' '.getMonthName(date("m",strtotime($lrow['tgl_jatuh_tempo'])),2).' '.date("Y",strtotime($lrow['tgl_jatuh_tempo']));
$tgl_pengajuan=date("d",strtotime($lrow['tgl_pengajuan'])).' '.getMonthName(date("m",strtotime($lrow['tgl_pengajuan'])),2).' '.date("Y",strtotime($lrow['tgl_pengajuan']));

$fk_cabang=$lrow["fk_cabang"];
$pdf = new Cezpdf('A4');  
$pdf->setLineStyle(1);

$pdf->ez['topMargin'] = (150);
$pdf->ez['bottomMargin']=5;
$pdf->ez['leftMargin']=60;
$pdf->ez['rightMargin']=65;

if($template=='t'){
//Header
$fontsize=10;
$y=800;
$x1 = 74;
$x2 = 78;

$x3 = 248;
$x4 = 376;
$x_right=67;


/*$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');
*/

$pdf->selectFont('fonts/Times-Roman');

$lrs_ho=pg_fetch_array(pg_query("select * from tblcabang where kd_cabang='".cabang_ho."'"));
$kacab=kacab($lrow["fk_cabang"],$lrow["tgl_cair"]);
$lrow["nm_kacab"]=$kacab["nm_depan"];
$alamat_kacab=$kacab["alamat"];
$fontalamat=$fontsize;
if(strlen($lrow["alamat_ktp"])>50){
	$fontalamat=$fontsize-2;
}

$y-=27;

$y-=17;

$pdf->addText($x3,$y, $fontsize,$lrow["nm_customer"]);
$y-=17;

$pdf->addText($x3,$y, $fontalamat,$lrow["alamat_ktp"]);
$y-=17;
$pdf->addText($x3,$y, $fontsize,$lrow["nm_bpkb"]);
$y-=17;
$pdf->addText($x3,$y, $fontsize,$fk_sbg);
$y-=18;

$y-=15;

$pembayaran_asr1=$pembayaran_asr2="  ";
if($lrow["nilai_asuransi"]>0){
	if($lrow["is_asuransi_tunai"]=='t')$pembayaran_asr1='V';
	else $pembayaran_asr2='V';
}
$pdf->addText($x3-2,$y, $fontsize,$pembayaran_asr1);	
$y-=19;
$pdf->addText($x2,$y, $fontsize,'');
$pdf->addText($x3-2,$y, $fontsize,$pembayaran_asr2);	
$y-=17;

$jns_asuransi1=$jns_asuransi2="  ";
if($lrow["nilai_asuransi"]>0)$jns_asuransi1='V';
if($lrow["nilai_asr_jiwa"]>0)$jns_asuransi2='V';
$pdf->addText($x3-2,$y, $fontsize,$jns_asuransi1);	
$y-=6;
$pdf->addText($x2,$y, $fontsize,'');
$pdf->addText($x3-2,$y, $fontsize,$jns_asuransi2);	
$y-=17;


$jns_tanggung1=$jns_tanggung2=$jns_tanggung3="  ";

if($lrow['all_risk_dari_tahun']){
	$jns_tanggung1='V';
}

if($lrow['tlo_dari_tahun']){
	if($jns_tanggung1=='V'){
		$jns_tanggung1="  ";
		$jns_tanggung3="V";
	}else{
		$jns_tanggung2="V";
	}
}

$y-=15;
$pdf->addText($x2,$y, $fontsize,'');
$pdf->addText($x3-2,$y, $fontsize,$jns_tanggung1);	
$y-=15;
$pdf->addText($x2,$y, $fontsize,'');
$pdf->addText($x3-2,$y, $fontsize,$jns_tanggung2);	
$y-=15;
$pdf->addText($x2,$y, $fontsize,'');
$pdf->addText($x3-2,$y, $fontsize,$jns_tanggung3);	
$y-=15;


$jiwa1=$jiwa2="  ";
if($lrow["nilai_asr_jiwa"]>0)$jiwa1='V';
$pdf->addText($x2,$y, $fontsize,'');
$y-=17;
$pdf->addText($x2,$y, $fontsize,'');
$pdf->addText($x3-2,$y, $fontsize,$jiwa1);	
$y-=17;
$pdf->addText($x2,$y, $fontsize,'');
$pdf->addText($x3-2,$y, $fontsize,$jiwa2);	
$y-=55;

$y-=22;
$pdf->addText($x3,$y, $fontsize,'');	
$y-=17;
$pdf->addText($x3,$y, $fontsize,$lrow["no_polisi"]);	
$y-=17;
$y-=17;
$pdf->addText($x3,$y, $fontsize,$lrow["no_rangka"]);	
$y-=17;
$pdf->addText($x3,$y, $fontsize,$lrow["no_mesin"]);	
$y-=17;
$pdf->addText($x3,$y, $fontsize,$lrow["nm_merek"]);	
$y-=17;
$pdf->addText($x3,$y, $fontsize,$lrow["nm_tipe"]);	
$y-=17;
$pdf->addText($x3,$y, $fontsize,$lrow["tahun"]);	
$y-=17;
$pdf->addText($x3,$y, $fontsize,convert_money("",$lrow["total_nilai_pinjaman"]));	
$y-=17;
$pdf->addText($x3,$y, $fontsize,'');	
$y-=16;
$y-=15;
$pdf->addText($x3,$y, $fontsize,'');	
$y-=15;
if($nilai_asr_jiwa>0){
	$pdf->addText($x3,$y, $fontsize,$lrow["nm_customer"]);	
	$y-=17;
	$pdf->addText($x3,$y, $fontalamat,$lrow["alamat_ktp"]);	
}else{
	$y-=17;
}
$y-=42;

$pdf->addText($x3,$y, $fontsize,$tgl_pengajuan.' - '.$tgl_jt_indo);	
$y-=19;
$pdf->addText($x3,$y, $fontsize,$lrow["nm_asuransi"]);	

$y-=18;
$pdf->addText($x1+307,$y, $fontsize,$lrow['nm_cabang'].'        '.$tgl_pengajuan);
//$today_indo

$y-=92;
$pdf->addText($x1+326,$y, $fontsize-1, $lrow["nm_customer"] );
}else{
/*$all = $pdf->openObject();
$pdf->saveState();
*/
//Header
$fontsize=10;
$y=800;
$x1 = 69;
$x2 = 73;

$x3 = 230;
$x4 = 378;
$x_right=67;


/*$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');
*/

$pdf->selectFont('fonts/Times-Roman');

$lrs_ho=pg_fetch_array(pg_query("select * from tblcabang where kd_cabang='".cabang_ho."'"));
$kacab=kacab($lrow["fk_cabang"],$lrow["tgl_cair"]);
$lrow["nm_kacab"]=$kacab["nm_depan"];
$alamat_kacab=$kacab["alamat"];
$fontalamat=$fontsize;
if(strlen($lrow["alamat_ktp"])>50){
	$fontalamat=$fontsize-2;
}


$pdf->addText($x1+145,$y, $fontsize,'<b>SURAT PERMOHONAN ASURANSI</b>');
$pdf->addText($x1+145,$y, $fontsize,'<b>______________________________</b>');
$y-=30;

$pdf->addText($x1,$y, $fontsize,'I. DATA PERMOHONAN');
$y-=18;

$pdf->addText($x2,$y, $fontsize,'- Nama Lengkap Pemohon');
$pdf->addText($x3,$y, $fontsize,': '.$lrow["nm_customer"]);
$y-=18;
$pdf->addText($x2,$y, $fontsize,'- Alamat');
$pdf->addText($x3,$y, $fontalamat,': '.$lrow["alamat_ktp"]);
$y-=18;
$pdf->addText($x2,$y, $fontsize,'- Nama Pemilik di STNK');
$pdf->addText($x3,$y, $fontsize,': '.$lrow["nm_bpkb"]);
$y-=18;
$pdf->addText($x2,$y, $fontsize,'- Nomor Kontrak');
$pdf->addText($x3,$y, $fontsize,': '.$fk_sbg);
$y-=18;

$pdf->addText($x1,$y, $fontsize,'II. PILIHAN ASURANSI');
$y-=18;

$pembayaran_asr1=$pembayaran_asr2="  ";
if($lrow["nilai_asuransi"]>0){
	if($lrow["is_asuransi_tunai"]=='t')$pembayaran_asr1='V';
	else $pembayaran_asr2='V';
}
$pdf->addText($x2,$y, $fontsize,'- Pembayaran Asuransi');
$pdf->addText($x3,$y, $fontsize,': [ '.$pembayaran_asr1.' ] Tunai/Cash');	
$y-=18;
$pdf->addText($x2,$y, $fontsize,'');
$pdf->addText($x3,$y, $fontsize,': [ '.$pembayaran_asr2.' ] Masuk dalam Pokok Utang');	
$y-=18;

$jns_asuransi1=$jns_asuransi2="  ";
if($lrow["nilai_asuransi"]>0)$jns_asuransi1='V';
if($lrow["nilai_asr_jiwa"]>0)$jns_asuransi2='V';
$pdf->addText($x2,$y, $fontsize,'- Jenis Asuransi');
$pdf->addText($x3,$y, $fontsize,': [ '.$jns_asuransi1.' ] Asuransi Kendaraan Bermotor');	
$y-=18;
$pdf->addText($x2,$y, $fontsize,'');
$pdf->addText($x3,$y, $fontsize,': [ '.$jns_asuransi2.' ] Asuransi Jiwa');	
$y-=18;


$jns_tanggung1=$jns_tanggung2=$jns_tanggung3="  ";

if($lrow['all_risk_dari_tahun']){
	$jns_tanggung1='V';
}

if($lrow['tlo_dari_tahun']){
	if($jns_tanggung1=='V'){
		$jns_tanggung1="  ";
		$jns_tanggung3="V";
	}else{
		$jns_tanggung2="V";
	}
}

$pdf->addText($x2,$y, $fontsize,'- Jenis Pertanggungan');
$pdf->addText($x3,$y, $fontsize,': Untuk Asuransi Kendaraan Bermotor');	
$y-=18;
$pdf->addText($x2,$y, $fontsize,'');
$pdf->addText($x3,$y, $fontsize,': [ '.$jns_tanggung1.' ] All Risk (AR)');	
$y-=18;
$pdf->addText($x2,$y, $fontsize,'');
$pdf->addText($x3,$y, $fontsize,': [ '.$jns_tanggung2.' ] Total Lost Only (TLO)');	
$y-=18;
$pdf->addText($x2,$y, $fontsize,'');
$pdf->addText($x3,$y, $fontsize,': [ '.$jns_tanggung3.' ] Kombinasi');	
$y-=18;


$jiwa1=$jiwa2="  ";
if($lrow["nilai_asr_jiwa"]>0)$jiwa1='V';
$pdf->addText($x2,$y, $fontsize,'');
$pdf->addText($x3,$y, $fontsize,' Untuk Asuransi Jiwa');	
$y-=18;
$pdf->addText($x2,$y, $fontsize,'');
$pdf->addText($x3,$y, $fontsize,': [ '.$jiwa1.' ] Kecelakaan Diri');	
$y-=18;
$pdf->addText($x2,$y, $fontsize,'');
$pdf->addText($x3,$y, $fontsize,': [ '.$jiwa2.' ] Natural Death');	
$y-=18;

$pdf->addText($x1,$y, $fontsize,'III. DATA OBJEK PETANGGUNGAN');
$y-=18;
$pdf->addText($x2,$y, $fontsize,'- Untuk Asuransi Kendaraan Bermotor');
$pdf->addText($x3,$y, $fontsize,'');	
$y-=18;
$pdf->addText($x2+6,$y, $fontsize,'No. Polisi');
$pdf->addText($x3,$y, $fontsize,': '.$lrow["no_polisi"]);	
$y-=18;
$pdf->addText($x2+6,$y, $fontsize,'No. Rangka');
$pdf->addText($x3,$y, $fontsize,': '.$lrow["no_rangka"]);	
$y-=18;
$pdf->addText($x2+6,$y, $fontsize,'No. Mesin');
$pdf->addText($x3,$y, $fontsize,': '.$lrow["no_mesin"]);	
$y-=18;
$pdf->addText($x2+6,$y, $fontsize,'Merk');
$pdf->addText($x3,$y, $fontsize,': '.$lrow["nm_merek"]);	
$y-=18;
$pdf->addText($x2+6,$y, $fontsize,'Type Kendaraan');
$pdf->addText($x3,$y, $fontsize,': '.$lrow["nm_tipe"]);	
$y-=18;
$pdf->addText($x2+6,$y, $fontsize,'Tahun Kendaraan');
$pdf->addText($x3,$y, $fontsize,': '.$lrow["tahun"]);	
$y-=18;
$pdf->addText($x2+6,$y, $fontsize,'Harga Pertanggungan');
$pdf->addText($x3,$y, $fontsize,': '.convert_money("",$lrow["total_nilai_pinjaman"]));	
$y-=18;
$pdf->addText($x2+6,$y, $fontsize,'Keterangan Lainnya');
$pdf->addText($x3,$y, $fontsize,': ');	
$y-=18;
$y-=18;
$pdf->addText($x2,$y, $fontsize,'- Untuk Asuransi Jiwa');
$pdf->addText($x3,$y, $fontsize,'');	
$y-=18;
$pdf->addText($x2+6,$y, $fontsize,'Nama');
if($nilai_asr_jiwa>0){
$pdf->addText($x3,$y, $fontsize,': '.$lrow["nm_customer"]);	
}else{
$pdf->addText($x3,$y, $fontsize,': ');	
}
$y-=18;
$pdf->addText($x2+6,$y, $fontsize,'Alamat');
if($nilai_asr_jiwa>0){
$pdf->addText($x3,$y, $fontalamat,': '.$lrow["alamat_ktp"]);	
}else{
$pdf->addText($x3,$y, $fontsize,': ');	
}
$y-=18;
$y-=18;
$pdf->addText($x1,$y, $fontsize,'IV. Jangka Waktu Pertanggungan');
$pdf->addText($x3,$y, $fontsize,': '.$tgl_pengajuan.' - '.$tgl_jt_indo);	
$y-=18;
$pdf->addText($x1,$y, $fontsize,'V. Nama Perusahaan Asuransi');
$pdf->addText($x3,$y, $fontsize,': '.$lrow["nm_asuransi"]);	

$y-=50;
$today_indo=date("d").' '.getMonthName(date("m"),2).' '.date("Y");
$pdf->addText($x1+320,$y, $fontsize,$lrow['nm_cabang'].', '.$tgl_pengajuan);
$y-=30;

$pdf->addText($x1+320,$y, $fontsize,'Yang Membuat Pernyataan');

$y-=70;
$pdf->addText($x1+320,$y, $fontsize-1,'('.$lrow["nm_customer"].')');
$y-=60;
//$pdf->ezImage('../print/logo_2.png','30','60','','left','');
$pdf->addPngFromFile('../print/logo_2.png', 35, 5,30,25);
$pdf->selectFont('fonts/Times');
//$pdf->addText(100, 25, 6,'"PERJANJIAN INI TELAH DISESUAIKAN DENGAN KETENTUAN PERATURAN PERUNDANG-UNDANGAN TERMASUK KETENTUAN PERATURAN OTORITAS JASA KEUANGAN"');
$pdf->addText(75, 15, 9,'PT. Capella Multidana Berizin dan Diawasi oleh Otoritas Jasa Keuangan');
$pdf->selectFont('fonts/Times');
}
if($template!='t')$filename=$id_edit.'-'.$lrow["nm_customer"].'.pdf';
else $filename='template.pdf';
$options=array();//untuk rename nama file
$options['Content-Disposition']=$filename;

$pdf->ezStream($options);

?>