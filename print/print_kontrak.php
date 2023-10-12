<?php
if($_REQUEST["id_edit"]!=""){
	$prefix_url="../";
}else{
	if($_SERVER['HTTP_HOST']=="meta-server"){
		$prefix_url="D:/Development/Web Project/capella/website"; // BUAT META
	}else{
		if(strstr(dirname(__FILE__),"website")){
			$prefix_url="/www/erp/website/"; //BUAT DIATAS
		}else{
			$prefix_url="/www/erp/testing/"; //BUAT DIATAS
		}
	}
}

require $prefix_url.'/requires/config.inc.php';
require $prefix_url.'/requires/authorization.inc.php';
require $prefix_url.'/requires/general.inc.php';
require $prefix_url.'/requires/db_utility.inc.php';
require $prefix_url.'/requires/timestamp.inc.php';
require $prefix_url.'/classes/ezpdf.class.php';
require $prefix_url.'/requires/convert.inc.php';
require $prefix_url.'/requires/numeric.inc.php';

if($argv[3]){
	$id_edit=$argv[3];
}else{
	$id_edit = $_REQUEST['fk_sbg'];
}

if(!pg_query("insert into tblprint_log values ('".convert_sql($id_edit)."','KONTRAK','".$_SESSION['id']."','".$_SESSION['username']."','".date('m/d/Y H:i:s')."')"))exit();
		
	
$query="
select *,nm_partner as nm_dealer,tblpartner.alamat as alamat_dealer,tblcustomer.npwp as npwp_cust from data_gadai.tblproduk_cicilan 
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
where no_sbg='".$id_edit."'
";				
$lrow=pg_fetch_array(pg_query($query));
//showquery($query);

$template=$_REQUEST['flag'];
//$template='t';

$agunan_motor=get_rec("tblproduk","nominal_denda_keterlambatan","kd_produk='20'");
$agunan_mobil=get_rec("tblproduk","nominal_denda_keterlambatan","kd_produk='40'");

if(!$lrow['tgl_cair'])$lrow['tgl_cair']=$lrow['tgl_pengajuan'];
$tgl_cair_indo=date("d",strtotime($lrow['tgl_cair'])).' '.getMonthName(date("m",strtotime($lrow['tgl_cair'])),2).' '.date("Y",strtotime($lrow['tgl_cair']));
$tgl_jt_indo=date("d",strtotime($lrow['tgl_jatuh_tempo'])).' '.getMonthName(date("m",strtotime($lrow['tgl_jatuh_tempo'])),2).' '.date("Y",strtotime($lrow['tgl_jatuh_tempo']));
$tgl_pengajuan=date("d",strtotime($lrow['tgl_pengajuan'])).' '.getMonthName(date("m",strtotime($lrow['tgl_pengajuan'])),2).' '.date("Y",strtotime($lrow['tgl_pengajuan']));
if($lrow["jenis_customer"]=='0'){
	$nm_pihak_lain=$lrow["nm_pasangan"];
}elseif($lrow["jenis_customer"]=='1'){
	$nm_pihak_lain=$lrow["nm_pemilik"];
}
if(!$nm_pihak_lain)$nm_pihak_lain='-';

$lrow["jumlah_unit"]='1';

/*if($template=='t'){
	$jabatan_pihak1='';
	$nm_pihak1='';	
	$alamat_pihak1='';
	$tgl_jt_indo=$tgl_cair_indo=$nm_pihak_lain='____________________';
	$id_edit='_________________________________';
	$lrow=array();
	$lrow["nm_dealer"]=$lrow["alamat_dealer"]='__________________________________';
	$lrow["lama_pinjaman"]='      ';
	$lrow["fk_produk"]='20';
	$lrow['nm_cabang']='____________________';
}else*/if($lrow["jenis_cabang"]=='Cabang'){
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


$pdf = new Cezpdf('A4');  
$pdf->setLineStyle(1);

$y_table=680;
$heigth=$pdf->ez['pageHeight'];
$width=$pdf->ez['pageWidth'];
$pdf->ez['topMargin'] = $heigth-$y_table;
$pdf->ez['bottomMargin']=10;
$pdf->ez['leftMargin']=30;
$pdf->ez['rightMargin']=50;

if($template=='t'){
/*	$pdf->ez['bottomMargin']=0;
	$pdf->ez['leftMargin']=0;
	$pdf->ez['rightMargin']=0;
	$pdf->ezImage('kontrak1.png', 10, 0, 0);	
	$all = $pdf->openObject();
	
$pdf->saveState();
$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');*/

$fontsize= 9;
//Header

$x1=30;
$x2=$x1+125;

$y-=80;
$pdf->y=839;
//echo $pdf->y;
$y=$pdf->y;

$pdf->y=$y;

$x1=38;
$x2=$x1+128;
$x3=$x2+95;
$x4=$x3+205;

$y=$pdf->y;
$y-=83;
$pdf->selectFont('fonts/Times-Bold');
$pdf->addText($x2+70, $y, $fontsize+2,$id_edit);
$pdf->selectFont('fonts/Times-Roman');
$y-=24;
$pdf->addText($x1+71, $y, $fontsize,getDayName(date("w",strtotime($lrow['tgl_pengajuan']))));
$pdf->addText($x2+11, $y, $fontsize,date("d",strtotime($lrow['tgl_pengajuan'])));
$pdf->addText($x2+58, $y, $fontsize,getMonthName(date("m",strtotime($lrow['tgl_pengajuan'])),2));
$pdf->addText($x2+132, $y, $fontsize,date("Y",strtotime($lrow['tgl_pengajuan'])));

$y-=22;
$pdf->addText($x2+25, $y, $fontsize,$nm_pihak1);
$y-=10;
$pdf->addText($x2+25, $y, $fontsize,$jabatan_pihak1);
$y-=10;
$pdf->addText($x2+25, $y, $fontsize,$alamat_pihak1);

$y-=72;
$pdf->addText($x2+25, $y, $fontsize,$lrow["nm_customer"]);
$y-=10;
$pdf->addText($x2+25, $y, $fontsize,$lrow["no_id"]);
$y-=10;
$pdf->addText($x2+25, $y, $fontsize,$lrow["nm_pekerjaan"]);
$y-=10;
$pdf->addText($x2+25, $y, $fontsize,$lrow["alamat_ktp"]);
$y-=10;
$pdf->addText($x2+25, $y, $fontsize,$lrow["npwp_cust"]);

$y-=18;
$pdf->addText($x1+42, $y, $fontsize,$nm_pihak_lain);
$y-=73;
$pdf->addText($x1+49, $y, $fontsize,$lrow["nm_dealer"]);
$pdf->addText($x3+76, $y, $fontsize-2,$lrow["alamat_dealer"]);

$y-=33;
$pdf->addText($x3-15, $y, $fontsize,$lrow["jumlah_unit"]);
$pdf->addText($x4-20, $y, $fontsize,$lrow["warna"]);
$y-=10.5;
$pdf->addText($x3-15, $y, $fontsize,$lrow["nm_merek"]);
$pdf->addText($x4-20, $y, $fontsize,$lrow["no_bpkb"]);
$y-=10.5;
$pdf->addText($x3-15, $y, $fontsize,$lrow["nm_tipe"]);
$pdf->addText($x4-20, $y, $fontsize,$lrow["no_mesin"]);
$y-=10.5;
$pdf->addText($x3-15, $y, $fontsize,$lrow["tahun"]);
$pdf->addText($x4-20, $y, $fontsize,$lrow["no_rangka"]);
$y-=10.5;
$pdf->addText($x3-15, $y, $fontsize,$lrow["kondisi_unit"]);
$pdf->addText($x4-20, $y, $fontsize,$lrow["no_polisi"]);

$lrow["total_nilai_pinjaman"]=convert_money("Rp",$lrow["total_nilai_pinjaman"]);
$lrow["nilai_dp"]=convert_money("Rp",$lrow["nilai_dp"]);
$lrow["biaya_admin"]=convert_money("Rp",$lrow["biaya_admin"]);
$lrow["biaya_notaris"]=convert_money("Rp",$lrow["biaya_notaris"]);
$lrow["biaya_provisi"]=convert_money("Rp",$lrow["biaya_provisi"]);
$lrow["nilai_asuransi"]=convert_money("Rp",$lrow["nilai_asuransi"]);
$lrow["nilai_asuransi_lain"]=convert_money("Rp",$lrow["nilai_asr_jiwa"]+$lrow["tjh_3"]+$lrow["pa_penumpang"]+$lrow["pa_supir"]+$lrow["biaya_polis"]);
$lrow["pokok_hutang"]=convert_money("Rp",$lrow["pokok_hutang"]);
$lrow["biaya_penyimpanan"]=convert_money("Rp",$lrow["biaya_penyimpanan"]);
$lrow["total_hutang"]=convert_money("Rp",$lrow["total_hutang"]);
$lrow["angsuran_bulan"]=convert_money("Rp",$lrow["angsuran_bulan"]);

$x3=$x3-15;
$y-=20;
$pdf->addText($x3, $y, $fontsize,$lrow["total_nilai_pinjaman"]);
$y-=10;
$pdf->addText($x3, $y, $fontsize,$lrow["nilai_dp"]);
$y-=10;
$pdf->addText($x3, $y, $fontsize,$lrow["biaya_admin"]);
$y-=10;
$pdf->addText($x3, $y, $fontsize,$lrow["biaya_notaris"]);
$y-=10;
$pdf->addText($x3, $y, $fontsize,$lrow["biaya_provisi"]);
$y-=10;
$pdf->addText($x3, $y, $fontsize,$lrow["jenis_pembiayaan"]);
$y-=10;
$pdf->addText($x3, $y, $fontsize,$lrow["nilai_asuransi"]);
$y-=10;
$pdf->addText($x3, $y, $fontsize,$lrow["nilai_asuransi_lain"]);

$y-=23;
$pdf->addText($x3, $y, $fontsize,$lrow["pokok_hutang"]);
$y-=10;
$pdf->addText($x3, $y, $fontsize,$lrow["biaya_penyimpanan"]);
$y-=10;
$pdf->addText($x3, $y, $fontsize,$lrow["total_hutang"]);

$y-=21;
$pdf->addText($x3, $y, $fontsize,$lrow["lama_pinjaman"].' Bulan');
$y-=10;
$pdf->addText($x3, $y, $fontsize,$tgl_pengajuan.' s/d '.$tgl_jt_indo);
$y-=10;
$pdf->addText($x3, $y, $fontsize,$lrow["angsuran_bulan"]);

$y-=115;
$pdf->addText($x1+150,$y, $fontsize,$lrow['nm_cabang']);
$pdf->addText($x1+258,$y, $fontsize,$tgl_pengajuan);

$y-=48;
$pdf->addText($x1+40,$y, $fontsize,$nm_pihak1);
$pdf->addText($x1+206,$y, $fontsize,$lrow["nm_customer"]);
$pdf->addText($x2+260,$y, $fontsize,$nm_pihak_lain);

$pdf->ezNewPage();

$pdf->selectFont('fonts/Times-Roman');

$y_pasal=840;
$pdf->ezSetY($y_pasal);


$fontsize= 9;
//Header
$pdf->y=840;

$x1=30;
$x2=$x1+125;

$y-=80;
//echo $pdf->y;
$y=$pdf->y;

$pdf->y=$y;

$x1=30;
$x2=$x1+125;
$x3=$x2+88;
$x4=$x3+199;

$y=$pdf->y;
$y-=92;
$pdf->addText($x3+100, $y, $fontsize,$id_edit);
$y-=10;
$pdf->addText($x1+70, $y, $fontsize,$tgl_pengajuan);
$pdf->ezNewPage();

$pdf->selectFont('fonts/Times-Roman');

$y_pasal=840;
$pdf->ezSetY($y_pasal);


$fontsize= 9;
//Header
$pdf->y=840;

$x1=30;
$x2=$x1+125;

$y-=80;
//echo $pdf->y;
$y=$pdf->y;

$pdf->y=$y;

$x1=30;
$x2=$x1+125;
$x3=$x2+88;
$x4=$x3+199;

$y=$pdf->y;
$y-=690;
$pdf->addText($x2+76,$y, $fontsize,$lrow['nm_cabang']);
$pdf->addText($x2+160,$y, $fontsize,$tgl_pengajuan);
$y-=101;
$pdf->addText($x2-60, $y, $fontsize,$lrow["nm_customer"]);
$pdf->addText($x2+189, $y, $fontsize,$nm_pihak_lain);
}///end template

else{
$all = $pdf->openObject();
$pdf->saveState();
//$pdf->ezImage('../print/logo_2.png','300','60','','center','1');
//$pdf->addPngFromFile('../print/logo_2.png', 25, 5,30,25);
$pdf->selectFont('fonts/Times-Roman');
$pdf->addText(75, 10, 9,'PT. Capella Multidana Berizin dan Diawasi oleh Otoritas Jasa Keuangan');
$pdf->selectFont('fonts/Times-Roman');

$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');


$fontsize= 9;

//Header
$pdf->y=840;
//echo $pdf->y;
$pdf->selectFont('fonts/Times-BoldItalic');
$pdf->ezText('BISMILLAHIRRAHMANIRRAHIM',12,array('justification' =>'center'));
$pdf->selectFont('fonts/Times-Roman');
$pdf->ezText('"Dengan Menyebut Nama ALLAH Yang Maha Pengasih dan Penyayang"',8,array('justification' =>'center'));
$pdf->selectFont('fonts/Times-Bold');
$pdf->ezText($lrow["nm_perusahaan"].' UNIT USAHA SYARIAH',12,array('justification' =>'center'));
$pdf->ezText('PERJANJIAN PEMBIAYAAN SYARIAH DENGAN AKAD MURABAHAH',10,array('justification' =>'center'));
$pdf->ezText('NOMOR '.$id_edit,14,array('justification' =>'center'));
$y=$pdf->y;
$y-=9;
$pdf->line(25,$y,565,$y);
$y-=2;
$pdf->line(25,$y,565,$y);
$y-=2;

$pdf->selectFont('fonts/Times-Roman');
$pdf->y=$y;
$pdf->ezText('Pada hari ini tanggal '.$tgl_pengajuan.' telah dibuat dan disepakati Perjanjian Pembiayaan Syariah dengan Akad Murabahah (selanjutnya disebut "PERJANJIAN") antara :',$fontsize,array('justification'=>'full','left'=>5));

$x1=30;
$x2=$x1+125;

$y=$pdf->y;
$y-=12;
$pdf->addText($x1, $y, $fontsize,'1. Nama');
$pdf->addText($x2, $y, $fontsize,': '.$nm_pihak1);
$y-=12;
$pdf->addText($x1, $y, $fontsize,'   Jabatan');
$pdf->addText($x2, $y, $fontsize,': '.$jabatan_pihak1);
$y-=12;
$pdf->addText($x1, $y, $fontsize,'   Alamat');
$pdf->addText($x2, $y, $fontsize,': '.$alamat_pihak1);
$y-=2;
$pdf->y=$y;
$pdf->ezText('Dalam hal ini bertindak dalam jabatannya selaku kuasa dari dan karenanya untuk dan atas nama '. $lrow["nm_perusahaan"].'. Selanjutnya secara sendiri-sendiri maupun bersama-sama disebut sebagai PIHAK PERTAMA.
',$fontsize,array('justification'=>'full','left'=>5));

/*if($template=='t'){
	$tgl_jt_indo=$tgl_pengajuan='                         ';
}*/
$y=$pdf->y;
$y-=3;
$pdf->addText($x1, $y, $fontsize,'2. Nama Lengkap');
$pdf->addText($x2, $y, $fontsize,': '.$lrow["nm_customer"]);
$y-=12;
$pdf->addText($x1, $y, $fontsize,'    Nomor KTP');
$pdf->addText($x2, $y, $fontsize,': '.$lrow["no_id"]);
$y-=12;
$pdf->addText($x1, $y, $fontsize,'    Pekerjaan/Jabatan');
$pdf->addText($x2, $y, $fontsize,': '.$lrow["nm_pekerjaan"]);
$y-=12;
$pdf->addText($x1, $y, $fontsize,'    Alamat');
$pdf->addText($x2, $y, $fontsize,': '.$lrow["alamat_ktp"]);;
$y-=12;
$pdf->addText($x1, $y, $fontsize,'    Nomor NPWP');
$pdf->addText($x2, $y, $fontsize,': '.$lrow["npwp_cust"]);
$y-=2;
$pdf->y=$y;
$pdf->ezText('Dalam melakukan PERJANJIAN ini telah mendapat persetujuan dari Suami / Istri / Komisaris yaitu '.$nm_pihak_lain.' yang turut hadir dan menandatangani PERJANJIAN ini, bertindak atas nama diri sendiri dan / atau bertindak dalam jabatannya yang selanjutnya disebut PIHAK KEDUA',$fontsize,array('justification'=>'full','left'=>5));

$y-=35;
$pdf->y=$y;
$pdf->ezText('Bahwa PIHAK KEDUA telah mengajukan permohonan fasilitas pembiayaan syariah dengan Akad Murabahah kepada PIHAK PERTAMA untuk membeli kendaraan bermotor dan selanjutnya PIHAK PERTAMA telah menyetujui permohonan fasilitas tersebut dengan Perjanjian Pembiayaan menggunakan Akad Murabahah.',$fontsize,array('justification'=>'full','left'=>5));

$y-=45;
$pdf->addText($x1, $y, $fontsize,'1.');
$y+=11;
$pdf->y=$y;
$pdf->ezText('Atas permohonan dari PIHAK KEDUA, PIHAK PERTAMA bersedia membeli Kendaraan Bermotor dari Dealer '.$lrow["nm_dealer"].' yang beralamat di '.$lrow["alamat_dealer"].' yang selanjutnya disebut sebagai "PENJUAL", dengan rincian sebagai berikut :
',$fontsize,array('justification'=>'full','left'=>25));

$x3=53;
$x3_isi=$x3+160;
$x4=350;
$x4_isi=$x4+80;

$x_rinci=$x1+20;

$y-=45;
$pdf->addText($x_rinci, $y, $fontsize,'a. Objek Pembiayaan');
$y-=12;
$pdf->addText($x3, $y, $fontsize,'-	 Jumlah Unit');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["jumlah_unit"]);
$pdf->addText($x4, $y, $fontsize,'-	 Warna');
$pdf->addText($x4_isi, $y, $fontsize,': '.$lrow["warna"]);
$y-=12;
$pdf->addText($x3, $y, $fontsize,'-	 Merk');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["nm_merek"]);
$pdf->addText($x4, $y, $fontsize,'-	 Nomor BPKB');
$pdf->addText($x4_isi, $y, $fontsize,': '.$lrow["no_bpkb"]);
$y-=12;
$pdf->addText($x3, $y, $fontsize,'-	 Jenis / Type');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["nm_tipe"]);
$pdf->addText($x4, $y, $fontsize,'-	 Nomor Mesin');
$pdf->addText($x4_isi, $y, $fontsize,': '.$lrow["no_mesin"]);
$y-=12;
$pdf->addText($x3, $y, $fontsize,'-	 Tahun');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["tahun"]);
$pdf->addText($x4, $y, $fontsize,'-	 Nomor Rangka');
$pdf->addText($x4_isi, $y, $fontsize,': '.$lrow["no_rangka"]);
$y-=12;
$pdf->addText($x3, $y, $fontsize,'-	 Kondisi');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["kondisi_unit"]);
$pdf->addText($x4, $y, $fontsize,'-	 Nomor Polisi');
$pdf->addText($x4_isi, $y, $fontsize,': '.$lrow["no_polisi"]);
$y-=12;

$pdf->addText($x_rinci, $y, $fontsize,'b. Informasi Pembiayaan');
$lrow["total_nilai_pinjaman"]=convert_money("Rp",$lrow["total_nilai_pinjaman"]);
$lrow["nilai_dp"]=convert_money("Rp",$lrow["nilai_dp"]);
$lrow["biaya_admin"]=convert_money("Rp",$lrow["biaya_admin"]);
$lrow["biaya_notaris"]=convert_money("Rp",$lrow["biaya_notaris"]);
$lrow["biaya_provisi"]=convert_money("Rp",$lrow["biaya_provisi"]);
$lrow["nilai_asuransi"]=convert_money("Rp",$lrow["nilai_asuransi"]);
$lrow["nilai_asuransi_lain"]=convert_money("Rp",$lrow["nilai_asr_jiwa"]+$lrow["tjh_3"]+$lrow["pa_penumpang"]+$lrow["pa_supir"]+$lrow["biaya_polis"]);
$lrow["pokok_hutang"]=convert_money("Rp",$lrow["pokok_hutang"]);
$lrow["biaya_penyimpanan"]=convert_money("Rp",$lrow["biaya_penyimpanan"]);
$lrow["total_hutang"]=convert_money("Rp",$lrow["total_hutang"]);
$lrow["angsuran_bulan"]=convert_money("Rp",$lrow["angsuran_bulan"]);

/*if($template=='t'){
	$lrow["total_nilai_pinjaman"]=$lrow["nilai_dp"]=$lrow["biaya_admin"]=$lrow["biaya_notaris"]=$lrow["biaya_provisi"]=$lrow["nilai_asuransi"]=$lrow["total_nilai_pinjaman"]=$lrow["nilai_asuransi_lain"]=$lrow["pokok_hutang"]=$lrow["biaya_penyimpanan"]=$lrow["total_hutang"]=$lrow["angsuran_bulan"]='';
	$tgl_pengajuan='____________________';
}
*/


$y-=12;
$pdf->addText($x3, $y, $fontsize,'-  Harga Beli Murabahah');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["total_nilai_pinjaman"]);
$y-=12;
$pdf->addText($x3, $y, $fontsize,'-  Uang Muka');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["nilai_dp"]);
$y-=12;
$pdf->addText($x3, $y, $fontsize,'-  Biaya Administrasi dan Survey');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["biaya_admin"]);
$y-=12;
$pdf->addText($x3, $y, $fontsize,'-  Biaya Notaris & Jaminan Fidusia');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["biaya_notaris"]);
$y-=12;
$pdf->addText($x3, $y, $fontsize,'-  Biaya Provisi');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["biaya_provisi"]);
$y-=12;
$pdf->addText($x3, $y, $fontsize,'-  Jenis Asuransi Syariah');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["jenis_pembiayaan"]);
$y-=12;
$pdf->addText($x3, $y, $fontsize,'-  Biaya Asuransi Kendaraan');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["nilai_asuransi"]);
$y-=12;
$pdf->addText($x3, $y, $fontsize,'-  Biaya Asuransi Lain');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["nilai_asuransi_lain"]);
$y-=12;
$pdf->addText($x_rinci, $y, $fontsize,'c. Rincian Fasilitas Pembiayaan');
$y-=12;
$pdf->addText($x3, $y, $fontsize,'-  Harga Jual Murabahah');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["pokok_hutang"]);
$y-=12;
$pdf->addText($x3, $y, $fontsize,'-  Margin');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["biaya_penyimpanan"]);
$y-=12;
$pdf->addText($x3, $y, $fontsize,'-  Total Kewajiban');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["total_hutang"]);
$y-=12;

$pdf->addText($x_rinci, $y, $fontsize,'d. Jangka Waktu (Tenor) Pengembalian Kewajiban Hutang');
$y-=12;
$pdf->addText($x3, $y, $fontsize,'-  Jangka Waktu (Tenor)');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["lama_pinjaman"].' Bulan');
$y-=12;
$pdf->addText($x3, $y, $fontsize,'-  Jadwal Pembayaran Angsuran');
$pdf->addText($x3_isi, $y, $fontsize,': '.$tgl_pengajuan.' s/d '.$tgl_jt_indo);
$y-=12;
$pdf->addText($x3, $y, $fontsize,'-  Besarnya Angsuran Perbulan');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["angsuran_bulan"]);


$y-=15;
$pdf->addText($x1, $y, $fontsize,'2.');
$y+=10;
$pdf->y=$y;
$pdf->ezText('PIHAK PERTAMA dan PIHAK KEDUA sepakat untuk melakukan transaksi jual beli Kendaraan Bermotor dan PIHAK KEDUA telah berjanji dan mengikatkan diri kepada PERJANJIAN ini untuk membayar keseluruhan fasilitas pembiayaan yang diberikan oleh PIHAK PERTAMA.',$fontsize,array('justification'=>'full','left'=>25));

$y-=15;
//$pdf->addText($x1, $y, $fontsize,'3.');
$y+=12;
$pdf->y=$y;
/*$pdf->ezText('Selain kewajiban angsuran sesuai Harga Jual Murabahah yang telah disepakati, PIHAK KEDUA juga berkewajiban membayar biaya-biaya yang timbul dari pelaksanaan Akad. Seluruh jumlah biaya untuk pelaksanaan Akad harus dibayarkan secara sekaligus yang sumber dananya berasal dari PIHAK KEDUA.',$fontsize,array('justification'=>'full','left'=>25));
*/

$y-=30;
$pdf->y=$y;
$pdf->ezText('Akad ini mulai berlaku dan mengikat sejak ditandatangani oleh kedua belah pihak dan berakhirnya pada saat kewajiban PIHAK KEDUA telah selesai dipenuhi seluruhnya. Kedua belah pihak telah sepakat dan setuju untuk mematuhi seluruh Ketentuan dan Syarat-syarat Akad sebagaimana tercantum pada halaman berikut Akad ini dan merupakan satu kesatuan yang tidak terpisahkan dari akad ini.',$fontsize,array('justification'=>'full','left'=>5));

$y-=35;
$pdf->y=$y;
$pdf->ezText('Demikian Akad ini dibuat dan dinyatakan secara sah serta mengikat bagi para pihak. Perjanjian ini dibuat dalam rangkap 2 (dua) yang 
masing-masing mempunyai kedudukan hukum yang sama.',$fontsize,array('justification'=>'full','left'=>5));
$y-=35;
$pdf->addText($x1+185,$y, $fontsize,$lrow['nm_cabang'].', '.$tgl_pengajuan);

$y-=15;
$pdf->addText($x1+20, $y, $fontsize,'PIHAK PERTAMA');
$pdf->addText($x1+225, $y, $fontsize,'PIHAK KEDUA');
$pdf->addText($x1+445, $y, $fontsize,'MENYETUJUI');

$y-=35;
$pdf->addText($x1, $y, $fontsize,'________________________');
$pdf->addText($x1+205, $y, $fontsize,'________________________');
$pdf->addText($x1+415, $y, $fontsize,'________________________');

$y-=32;
$pdf->addText($x1, $y, $fontsize,'___________');
$pdf->addText($x1+10, $y-12, 9,'(SAKSI I)');
$pdf->addText($x1+65, $y, $fontsize,'___________');
$pdf->addText($x1+72, $y-12, 9,'(SAKSI II)');
$pdf->addText($x1+165, $y, $fontsize,'______________________');
$pdf->addText($x1+290, $y, $fontsize,'______________________');
$pdf->addText($x1+166, $y-12, 9,'(PENJAMIN)');

$y-=30;
//$pdf->addText(70, $y, 6,'PERJANJIAN INI TELAH DISESUAIKAN DENGAN KETENTUAN PERATURAN PERUNDANG-UNDANGAN TERMASUK KETENTUAN PERATURAN OTORITAS JASA KEUANGAN');

//$pdf->setStrokeColor(0,0,0);

//$pdf->restoreState();
//$pdf->closeObject();
//$pdf->addObject($all,'all');

$pdf->ezNewPage();

$pdf->selectFont('fonts/Times-Bold');

$y_pasal=810;
$pdf->ezSetY($y_pasal);

$pdf->ezText($lrow["nm_perusahaan"].' UNIT USAHA SYARIAH',14,array('justification' =>'center'));
$pdf->ezText('KETENTUAN DAN SYARAT-SYARAT AKAD MURABAHAH',14,array('justification' =>'center'));
$y=$pdf->y;
$y-=12;
$pdf->line(10,$y,585,$y);
$y-=3;
$pdf->line(10,$y,585,$y);
$y-=5;
$pdf->y=$y;
$pdf->selectFont('fonts/Times-Roman');

$pdf->ezText('Dengan ditandatanganinya Akad Murabahah oleh PIHAK PERTAMA dan PIHAK KEDUA, maka para pihak telah sepakat dan setuju bahwa Ketentuan dan Syarat-Syarat Akad Murabahah ini merupakan satu kesatuan utuh yang tidak terpisahkan  dengan Perjanjian Pembiayaan Syariah Dengan Akad Murabahah Nomor : '.$id_edit.', tanggal '.$tgl_pengajuan.'.',$fontsize,array('justification'=>'full','left'=>5));

$y-=45;
$pdf->y=$y;
$pdf->selectFont('fonts/Times-Bold');
$pdf->ezText('PASAL 1',$fontsize,array('justification' =>'center'));
$pdf->ezText('SYARAT-SYARAT UMUM',$fontsize,array('justification' =>'center'));
$pdf->selectFont('fonts/Times-Roman');
$y-=30;
$pdf->y=$y;

$pdf->ezText('PIHAK PERTAMA dan PIHAK KEDUA dengan ini menyetujui segala ketentuan-ketentuan yang diuraikan dalam perjanjian, perjanjian tambahan, surat pernyataan serta surat-surat lainnya yang merupakan bagian penting dan tidak dapat dipisahkan dari perjanjian ini. Semua surat-surat yang terkait dengan Akad Murabahah ini bersifat tetap dan tidak dapat ditarik kembali dengan dasar atau alasan apapun juga, serta tidak berakhir karena sebab-sebab yang tercantum di dalam Pasal 1813, 1814, 1816 Kitab Undang-Undang Hukum Perdata.',$fontsize,array('justification'=>'full','left'=>5));

$y-=65;
$pdf->y=$y;
$pdf->selectFont('fonts/Times-Bold');
$pdf->ezText('PASAL 2',$fontsize,array('justification' =>'center'));
$pdf->ezText('DEFINISI',$fontsize,array('justification' =>'center'));
$pdf->selectFont('fonts/Times-Roman');
$y-=30;
$pdf->y=$y;

$pdf->ezText('Berikut merupakan penjelasan dari istilah-istilah yang digunakan dalam Perjanjian pembiayaan Murabahah sebagai berikut :',$fontsize,array('justification'=>'full','left'=>5));
$y=$pdf->y;

$x1_pasal=15;
$x2_pasal=38;

$pasal=array(
1=>'PIHAK PERTAMA adalah PT.Capella Multidana yang selanjutnya disebut sebagai Pemberi Fasilitas Pembiayaan dengan 
Akad Murabahah / Penerima Hak Milik secara Fidusia.',
2=>'PIHAK KEDUA adalah Penerima Fasilitas Pembiayaan dengan Akad Murabahah / Pemberi Hak milik secara fidusia',
3=>'JAMINAN FIDUSIA adalah pengalihan hak kepemilikan suatu benda atas dasar kepercayaan dengan ketentuan bahwa Barang yang hak kepemilikannya dialihkan tersebut tetap dalam penguasaan pemilik Barang.',
4=>'AKAD adalah perjanjian tertulis tentang fasilitas Pembiayaan Murabahah yang dibuat oleh PIHAK PERTAMA  dan PIHAK KEDUA yang memuat ketentuan-ketentuan dan syarat- syarat yang disepakati bersama sesuai dengan ketentuan dan perundang-undangan.',
5=>'PEMBIAYAAN MURABAHAH adalah pembiayaan atas jual beli suatu barang dengan menegaskan harga perolehan (HARGA JUAL MURABAHAH) kepada PIHAK KEDUA, dan PIHAK KEDUA membayarnya dengan harga lebih (MARGIN) sebagai laba sesuai dengan kesepakatan para pihak.',
6=>'BARANG adalah obyek pembiayaan berupa benda yang dibeli oleh PIHAK KEDUA dengan menggunakan fasilitas Pembiayaan Murabahah.',
7=>'PENJUAL adalah pihak ketiga yang menyediakan BARANG yang dibutuhkan oleh PIHAK KEDUA.',
8=>'HARGA BELI MURABAHAH adalah sejumlah uang yang harus dibayarkan oleh PIHAK PERTAMA kepada PENJUAL untuk membiayai pembelian BARANG atas permintaan PIHAK KEDUA yang disetujui oleh PIHAK PERTAMA ditambah biaya-biaya lain yang timbul yang dikeluarkan oleh PIHAK PERTAMA',
9=>'TOTAL KEWAJIBAN adalah HARGA JUAL MURABAHAH ditambah MARGIN beserta biaya-biaya lain yang timbul dalam pelaksanaan AKAD ini.',
10=>'MARGIN KEUNTUNGAN adalah jumlah uang yang wajib dibayar PIHAK KEDUA kepada PIHAK PERTAMA sebagai imbalan atas Pembiayaan yang diberikan oleh PIHAK KEDUA yang merupakan selisih antara Harga Jual dan Harga Beli.',
11=>'UANG MUKA adalah sejumlah uang yang besarnya  ditetapkan oleh PIHAK PERTAMA dan disetujui oleh PIHAK KEDUA yang harus dibayarkan terlebih dahulu oleh PIHAK KEDUA kepada PIHAK PERTAMA sebagai salah satu syarat yang harus dipenuhi untuk memperoleh Pembiayaan Murabahah dari PIHAK PERTAMA.',
12=>'ANGSURAN adalah hutang yang harus dibayar secara berkala oleh PIHAK KEDUA kepada PIHAK PERTAMA sesuai dengan yang ditentukan dalam Akad ini.',
13=>' TUNGGAKAN adalah PIUTANG MURABAHAH yang telah jatuh tempo, tetapi belum dibayar oleh PIHAK KEDUA kepada PIHAK PERTAMA.',
14=>'JAMINAN adalah sesuatu benda yang dijadikan jaminan baik yang bersifat materil maupun immaterial untuk mendukung keyakinan PIHAK PERTAMA atas kemampuan dan kesanggupan PIHAK KEDUA untuk melunasi seluruh kewajibannya kepada PIHAK PERTAMA sesuai Akad ini.',
15=>'TA’WIDH (Ganti Rugi) adalah ganti rugi atas kerugian yang dialami PIHAK PERTAMA yang dikenakan kepada PIHAK KEDUA akibat perbuatan dan/atau tindakan lalai/wanprestasi oleh PIHAK KEDUA dalam memenuhi kewajibannya dengan nominal sebagaimana yang tercantum dalam perjanjian ini.',
16=>'TA’ZIR (Sanksi Keterlambatan) adalah sejumlah uang yang dikenakan kepada PIHAK KEDUA akibat kelalaian dalam memenuhi kewajibannya yang digunakan untuk kegiatan sosial.',
);

for($i=1;$i<=14;$i++){
	$pdf->ezText('2.'.$i,$fontsize,array('justification'=>'full','left'=>$x1_pasal));
	$pdf->y=$y;
	$pdf->ezText($pasal[$i],$fontsize,array('justification'=>'full','left'=>$x2_pasal));
	$y=$pdf->y;

}


$y-=15;
$pdf->y=$y;
$pdf->selectFont('fonts/Times-Bold');
$pdf->ezText('PASAL 3',$fontsize,array('justification' =>'center'));
$pdf->ezText('SYARAT PEMBERIAN FASILITAS',$fontsize,array('justification' =>'center'));
$pdf->selectFont('fonts/Times-Roman');
$y-=30;
$pdf->y=$y;

$pdf->ezText('PIHAK PERTAMA dan PIHAK KEDUA sepakat bahwa perjanjian ini dilaksanakan dengan syarat-syarat sebagai berikut:',$fontsize,array('justification'=>'full','left'=>5));
$y=$pdf->y;

$pasal=array();
$pasal=array(
1=>'PIHAK KEDUA menyatakan dan menjamin bahwa BARANG yang diberikan fasilitas pembiayaan kepadanya hanya dipergunakan/dimamfaatkan untuk kegiatan yang memenuhi Prinsip Syariah.',
2=>'PIHAK KEDUA wajib menyerahkan baik langsung maupun melalui penjual seluruh dokumen yang disyaratkan oleh PIHAK PERTAMA termasuk tetapi tidak terbatas pada dokumen bukti diri PIHAK KEDUA melainkan surat lainnya yang berkaitan dengan Akad ini yang keabsahannya dijamin oleh PIHAK KEDUA.',
3=>'Seluruh kelengkapan dan keaslian dokumen yang merupakan persyaratan pembiayaan tersebut yang telah diserahkan PIHAK KEDUA kepada PIHAK PERTAMA menjadi milik PIHAK PERTAMA yang tidak perlu dikembalikan kepada PIHAK KEDUA. Oleh karenannya PIHAK KEDUA memberikan persetujuan kepada PIHAK PERTAMA untuk mempergunakan dokumen persyaratan pembiayaan, data transaksi atau data PIHAK KEDUA lainya yang ada atau tercatat pada pembukuan PIHAK PERTAMA untuk kepentingan bisnis PIHAK PERTAMA dan/atau Grup Capella sehubungan dengan kegiatan usaha PIHAK PERTAMA termasuk namun tidak terbatas untuk diberikan kepada pihak ketiga yang memiliki kerjasama dengan PIHAK PERTAMA dalam rangka melaksanakan kegiatan usaha PIHAK PERTAMA.',
4=>'Terkait dengan poin 3.3 di atas, dengan ini PIHAK KEDUA memberikan persetujuan untuk dihubungi melalui sarana komunikasi pribadi oleh PIHAK PERTAMA dan/atau pihak lain yang memiliki kerjasama dengan PIHAK PERTAMA termasuk namun tidak terbatas dalam rangka pengalihan piutang, penagihan kewajiban PIHAK KEDUA, penawaran produk dan/atau layanan, serta untuk memenuhi kewajiban yang ditetapkan oleh peraturan perundangan, termasuk tetapi tidak terbatas pada pemenuhan kewajiban pelaporan Pusat Pelaporan dan Analisis Transaksi Keuangan (PPATK) sebagaimana diatur dalam Undang-Undang Anti Pencucian Uang dan/atau Undang-Undang terkait lainnya.',
5=>'PIHAK KEDUA menyatakan memberikan persetujuan dan hak kepada PIHAK PERTAMA untuk melakukan kegiatan survey, meliputi pengambilan gambar rumah atau kantor atau tempat usaha PIHAK KEDUA dan meminta keterangan atau informasi atau referensi mengenai PIHAK KEDUA dari pihak terkait disekitar rumah atau kantor atau tempat usaha PIHAK KEDUA maupun sumber manapun yang dipandang perlu dan dengan tata cara yang ditentukan oleh PIHAK PERTAMA sesuai dengan ketentuan peraturan perundangan yang berlaku untuk keperluan analisa pemberian Fasilitas Pembiayaan Murabahah maupun untuk pembaharuan data PIHAK KEDUA.',
6=>'PIHAK KEDUA wajib menyetorkan uang muka pembelian dan atau biaya-biaya yang disyaratkan oleh PIHAK PERTAMA.',
7=>'PIHAK KEDUA wajib menandatangani Akad serta surat-surat lainnya yang tidak terpisahkan dan sejak ditandatanganinya Akad dan telah diterimanya Barang oleh PIHAK KEDUA, maka risiko atas Barang tersebut sepenuhnya menjadi tanggung jawab PIHAK KEDUA dan selanjutnya membebaskanPIHAK PERTAMA dari segala tuntutan dan atau ganti rugi dalam bentuk apapun atas risiko tersebut.',
8=>'PIHAK KEDUA membayar Harga Jual Murabahah yaitu Harga Beli Murabahah ditambah Margin Murabahah beserta kewajiban lain yang disepakati dalam PERJANJIAN ini seperti tetapi tidak terbatas produk asuransi yang selanjutnya secara keseluruhan akan disebut  Total  Kewajiban kepada PIHAK PERTAMA dalam jangka waktu tertentu yang disepakati oleh para pihak berdasarkan PERJANJIAN ini. Seluruh biaya yang timbul dalam pelaksanaan PERJANJIAN ini meliputi biaya administrasi, pajak, bea materai, biaya survey, biaya perubahan atas PERJANJIAN, biaya pengecekan Buku Pemilik Kendaraan Bermotor (BPKB) / Surat Tanda Nomor Kendaraan  (STNK), biaya fotokopi BPKB (atas permintaan PIHAK KEDUA) maupun biaya-biaya lain terkait pemberian Fasilitas Pembiayaan Murabahah ini serta biaya penghapusan jaminan (jika ada) yang ditetapkan PIHAK PERTAMA dikemudian hari. Segala biaya sebagaimana tersebut diatas merupakan beban dan harus dibayar seluruhnya oleh PIHAK KEDUA sampai PERJANJIAN ini berakhir.',
9=>'PIHAK KEDUA menyerahkan semua dokumen asli yang berkaitan dengan kepemilikan kendaraan termasuk Buku Pemilikan Kendaraan Bermotor (BPKB) kepada PIHAK PERTAMA dan baru diserahkan kepada PIHAK KEDUA apabila PIHAK KEDUA telah melaksanakan seluruh kewajibannya (pelunasan seluruhnya).',
10=>'PIHAK KEDUA bersedia dan tidak keberatan apabila pembiayaan Murabahah ini dilaporkan PIHAK PERTAMA kepada Sistem Layanan Informasi Keuangan (SLIK) Otoritas Jasa Keuangan (OJK) dengan berpedoman pada ketentuan yang berlaku terkait hal tersebut.',
);

for($i=1;$i<=10;$i++){
	$pdf->ezText('3.'.$i,$fontsize,array('justification'=>'full','left'=>$x1_pasal));
	$pdf->y=$y;
	$pdf->ezText($pasal[$i],$fontsize,array('justification'=>'full','left'=>$x2_pasal));
	$y=$pdf->y;
	//echo $y.' $i<br>';
	if($y<100){
		$pdf->ezNewPage();		
		$pdf->ezSetY($y_pasal);
		$y=$y_pasal;
	}
}


$y-=15;
$pdf->y=$y;
$pdf->selectFont('fonts/Times-Bold');
$pdf->ezText('PASAL 4',$fontsize,array('justification' =>'center'));
$pdf->ezText('HAK DAN KEWAJIBAN PIHAK KEDUA',$fontsize,array('justification' =>'center'));
$pdf->selectFont('fonts/Times-Roman');
$y-=30;
$pdf->y=$y;


$pdf->ezText('Selama berlakunya PERJANJIAN ini, maka PIHAK KEDUA mempunyai Hak dan Kewajiban sebagai berikut :',$fontsize,array('justification'=>'full','left'=>5));
$y=$pdf->y;

$pasal=array();
$pasal=array(
1=>'PIHAK KEDUA mempunyai hak untuk menerima fasilitas pembiayaan yang sudah disetujui oleh PIHAK PERTAMA.',
2=>'PIHAK KEDUA berhak atas jaminan keamanan atas BPKB yang diserahkan kepada PIHAK PERTAMA.',
3=>'PIHAK KEDUA berhak atas fasilitas informasi pembiayaan yang akurat, jujur, jelas serta pelayanan dan penyelesaian penanganan pengaduan.',
4=>'PIHAK KEDUA wajib membayar angsuran secara teratur dan tepat waktu sesuai jatuh tempo dan tidak ada suatu alasan apapun bagi PIHAK KEDUA untuk menunda atau tidak melaksanakan pembayaran angsuran walaupun PIHAK KEDUA sedang dalam proses Klaim Asuransi.',
5=>'Apabila BARANG dijadikan barang bukti oleh Pihak Berwajib (Kepolisian, Kejaksaan, Pengadilan) karena tersangkut perbuatan Pidana dan BARANG tersebut dirampas oleh Negara sehubungan adanya Putusan Hakim yang berkekuatan hukum tepap (Incraht van gewijsde), maka seluruh kewajiban PIHAK KEDUA kepada PIHAK PERTAMA tetap melakat sebagaimana PERJANJIAN ini dan PIHAK KEDUA wajib melunasi seluruh kewajibannya kepada PIHAK PERTAMA hingga lunas. ',
6=>"PIHAK KEDUA berkewajiban membayar sanksi keterlambatan (ta’zir) kepada PIHAK PERTAMA sebesar ".convert_money("Rp",($agunan_motor)).",- (".convert_terbilang($agunan_motor)." Rupiah ) untuk Agunan Sepeda Motor dan ".convert_money("Rp",($agunan_mobil)).",- (".convert_terbilang($agunan_mobil)." Rupiah ) untuk Agunan Mobil, Tanah dan Bangunan per keterlambatan setiap pembayaran angsuran sesuai dengan nominal yang telah disepakati.",
7=>'PIHAK KEDUA berkewajiban membayar ganti rugi (ta’widh) kepada PIHAK PERTAMA atas kerugian yang dialami PIHAK PERTAMA yang muncul dikemudian hari akibat keterlambatan pembayaran angsuran oleh PIHAK KEDUA dari biaya rill yang termasuk namun tidak terbatas pada biaya komunikasi, biaya surat menyurat, biaya perjalanan, biaya jasa konsultan hukum, biaya notariat, biaya perpajakan, biaya lembur dan kerja ekstra, biaya penagihan, dan biaya penanganan BARANG yang dilakukan oleh PIHAK PERTAMA atau pihak ketiga yang ditunjuk oleh PIHAK PERTAMA, dan akan diberitahukan kepada PIHAK KEDUA melalui media yang telah ditentukan dan dibayarkan secara penuh dan sekaligus.',
8=>'PIHAK PERTAMA akan memberikan peringatan/teguran kepada PIHAK KEDUA secara tertulis, termasuk dengan melalui ponsel (handphone) dan/atau melalui electronic mail (e-mail) setelah 3 (tiga) hari sejak PIHAK KEDUA cidera janji (wanprestasi) dalam memenuhi kewajibannya berdasarkan Perjanjian ini.',
9=>'Setiap BARANG yang dilakukan penanganan/penggudangan oleh PIHAK PERTAMA, maka segala biaya yang muncul adalah menjadi tanggung jawab PIHAK KEDUA.',
10=>'PIHAK KEDUA dilarang untuk meminjamkan, menyewakan, mengalihkan, menjaminkan, atau menyerahkan penguasaan atau penggunaan atas BARANG tersebut kepada Pihak Ketiga dengan jalan apapun juga. Pelanggaran atas ketentuan ini dapat dikenakan Pasal 372 dan Pasal 378 Kitab Undang-Undang Hukum Pidana jo. Pasal 36 Undang-Undang No. 42 Tahun 1999 tentang Fidusia.',
11=>'PIHAK KEDUA wajib mengansuransikan BARANG terhadap bahaya-bahaya termasuk tapi tidak terbatas pada kecelakaan/kehilangan dengan premi yang dibayar oleh PIHAK KEDUA dan PIHAK KEDUA wajib menanggung seluruh biaya pengurusan berkas-berkas klaim asuransi yang timbul dikemudian hari.',
12=>'PIHAK KEDUA berkewajiban memelihara dan mengurus BARANG sebaik-baiknya dan melakukan segala pemeliharaan BARANG sesuai ketentuan pabrikan dan melakukan perbaikan atas biaya sendiri bila terjadi kerusakan mesin atau body, dan PIHAK KEDUA tidak diperkenankan mengganti mesin, suku cadang, body atau part lainnya diluar atau yang tidak sesuai dengan peruntukan sebagaimana standar pabrikan.',
13=>'PIHAK KEDUA wajib untuk melakukan perpanjangan STNK dan membayar pajak BARANG sesuai ketentuan yang berlaku selama masa pembiayaan. Apabila karena alasan apapun PIHAK KEDUA tidak dapat atau belum membayar atau memperpanjang STNK sesuai batas waktu yang ditentukan oleh ketentuan yang berlaku maka PIHAK PERTAMA berhak untuk melalui kuasanya melakukan perpanjangan STNK dengan biaya yang akan dibebankan kepada PIHAK KEDUA. Dan PIHAK KEDUA wajib menyerahkan seluruh dokumen syarat-syarat perpanjangan STNK tersebut kepada PIHAK PERTAMA guna kepentingan perpanjangan tersebut. ',
14=>'PIHAK KEDUA wajib memberikan kuasa kepada PIHAK PERTAMA untuk mewakili, membuat dan menandatangani akta penyerahan hak milik secara Fidusia atas nama PIHAK KEDUA kepada PIHAK PERTAMA dihadapan pejabat yang berwenang, sehubungan dengan BARANG pembiayaan. ',
15=>'Apabila PIHAK KEDUA tidak melunasi kewajiban pembayarannya kepada PIHAK PERTAMA, atau tidak memenuhi kewajibannya berdasarkan PERJANJIAN ini kepada PIHAK PERTAMA, maka PIHAK KEDUA memberi kuasa dengan hak substitusi kepada PIHAK PERTAMA untuk melakukan tindakan lain yang diperlukan termasuk mengambil, menarik, memelihara, menyewakan, mengagunkan/menjaminkan, membalik namakan, memindahkan atau menguasai kembali dan menjual BARANG dengan harga dan syarat-syarat serta ketentuan yang dianggap baik oleh PIHAK PERTAMA. ',
16=>'PIHAK KEDUA wajib memberitahukan secara tertulis kepada PIHAK PERTAMA mengenai adanya perubahan alamat dan data-data yang berhubungan dengan AKAD ini. ',
17=>'Bila penjualan BARANG yang ditarik maupun diserahkan secara sukarela oleh PIHAK KEDUA yang harganya dianggap baik oleh PIHAK PERTAMA masih belum menutupi seluruh utangnya maka PIHAK KEDUA wajib membayar selisih kekurangan utangnya kepada PIHAK PERTAMA. ',
18=>'Apabila PIHAK KEDUA menunggak pembayaran angsuran maka PIHAK PERTAMA akan mencatat dan melaporkan pada Sistem Layanan Informasi Keuangan (SLIK) Otoritas Jasa Keuangan (OJK) sehingga akan mempengaruhi reputasi PIHAK KEDUA sendiri. ',
);

for($i=1;$i<=18;$i++){
	$pdf->ezText('4.'.$i,$fontsize,array('justification'=>'full','left'=>$x1_pasal));
	$pdf->y=$y;
	$pdf->ezText($pasal[$i],$fontsize,array('justification'=>'full','left'=>$x2_pasal));
	$y=$pdf->y;
	//echo $y.' $i<br>';
	if($y<100){
		$pdf->ezNewPage();		
		$pdf->ezSetY($y_pasal);
		$y=$y_pasal;
	}
}


$y-=15;
$pdf->y=$y;
$pdf->selectFont('fonts/Times-Bold');
$pdf->ezText('PASAL 5',$fontsize,array('justification' =>'center'));
$pdf->ezText('HAK DAN KEWAJIBAN PIHAK PERTAMA',$fontsize,array('justification' =>'center'));
$pdf->selectFont('fonts/Times-Roman');
$y-=30;
$pdf->y=$y;

$pdf->ezText('Selama berlakunya PERJANJIAN ini, maka PIHAK PERTAMA mempunyai Hak dan Kewajiban sebagai berikut :',$fontsize,array('justification'=>'full','left'=>5));
$y=$pdf->y;

$pasal=array();
$pasal=array(
1=>'PIHAK PERTAMA berhak menerima serta menahan Faktur dan Bukti Pemilikan Kendaraan Bermotor (BPKB) sampai PIHAK KEDUA melunasi seluruh hutangnya.',
2=>'PIHAK PERTAMA atau wakilnya berhak untuk setiap waktu memasuki tempat dimana BARANG tersebut berada untuk memeriksa keadaan dan juga berhak melakukan suatu perbuatan untuk mempertahankan BARANG tersebut jika PIHAK KEDUA lalai melakukan kewajibannya.',
3=>'PIHAK PERTAMA atau wakilnya berhak secara langsung mengambil BARANG tersebut dari PIHAK KEDUA atau pihak lain yang menguasai BARANG tersebut tanpa harus adanya pemberitahuan terlebih dahulu apabila menurut PIHAK PERTAMA adanya tindakan maupun perbuatan terhadap BARANG tersebut yang tidak sesuai dengan tujuan maupun peruntukan fasilitas pembiayaan yang diberikan PIHAK PERTAMA kepada PIHAK KEDUA yang dapat merugikan PIHAK PERTAMA.',
4=>'PIHAK PERTAMA berhak dengan pertolongan alat-alat negara yang berwenang mengambil atau menyita BARANG pembiayaan tersebut. Untuk keperluan eksekusi/penjualan, satu dan lain atas biaya dan segalanya dibebankan kepada PIHAK KEDUA.',
5=>"PIHAK PERTAMA pada waktu menggunakan haknya berdasarkan AKAD ini dan/atau perjanjian lainnya yang dibuat oleh PIHAK KEDUA dan PIHAK PERTAMA, dapat melakukan penagihan kepada PIHAK KEDUA berdasarkan perhitungan PIHAK PERTAMA, baik yang berupa kewajiban, sanksi keterlambatan (ta'zir), ganti rugi (ta'widh), biaya penarikan/pelelangan/penjualan, honorarium pengacara/kuasa dan/atau biaya-biaya atau jumlah kewajiban lainnya. Biaya-biaya yang timbul akibat tindakan PIHAK PERTAMA tersebut wajib ditanggung/dibayar oleh PIHAK KEDUA.",
6=>"Apabila PIHAK KEDUA dinyatakan pailit oleh putusan pengadilan, maka PIHAK PERTAMA akan mendapat kedudukan sebagai Pihak Separatis dimana PIHAK PERTAMA berhak untuk menjual secara lelang BARANG/Objek jaminan fidusia.",
7=>'PIHAK PERTAMA mempunyai hak apabila dalam jangka waktu 1 (satu) minggu sejak diambil alihnya dan/atau ditariknya BARANG pembiayaan tersebut, PIHAK KEDUA tidak memenuhi kewajibannya, maka untuk selanjutnya akan menjualnya dengan cara, harga dan syarat-syarat yang dianggap baik oleh PIHAK PERTAMA baik penjualan yang dilakukan secara bawah tangan, pelelangan umum maupun mengalihkan BARANG tersebut kepada pihak lain.',
8=>'Setelah dilakukan penjualan melalui pelelangan umum, penjualan dibawah tangan atau pengalihan barang yang diambil-alih PIHAK PERTAMA maupun yang diserahkan PIHAK KEDUA secara sukarela dengan tujuan untuk menutupi seluruh sisa kewajiban PIHAK KEDUA, maka PIHAK PERTAMA wajib memberitahukan kepada PIHAK KEDUA mengenai hasil penjualan tersebut secara tertulis dan PIHAK PERTAMA wajib mengembalikan uang kelebihan jika ada kelebihan dari hasil penjualan. Demikian juga halnya bila nilai pelelangan, penjualan atau pengalihan BARANG tersebut tidak menutupi seluruh sisa kewajiban PIHAK KEDUA, maka PIHAK KEDUA wajib membayar kekurangan tersebut.',
9=>'Dalam hal PIHAK KEDUA tidak melakukan pembayaran untuk menutupi seluruh sisa kewajiban kepada PIHAK PERTAMA sebagaimana dalam <b>Pasal 5 ayat (5.8)<b> diatas, maka PIHAK PERTAMA dapat melakukan upaya penagihan maupun upaya lainnya termasuk melakukan tuntutan keperdataan kepada PIHAK KEDUA hingga seluruh kewajiban PIHAK KEDUA selesai kepada PIHAK PERTAMA. ',
10=>'PIHAK PERTAMA berkewajiban menyerahkan kembali Faktur dan Bukti Pemilikan Kendaraan Bermotor (BPKB) apabila PIHAK KEDUA sudah melunasi seluruh kewajibannya.',
11=>'PIHAK PERTAMA dan pihak lain (yang terkait dalam pelaksanaan perjanjian ini) wajib menyimpan dan menjaga keamanan data dan/atau informasi mengenai PIHAK KEDUA sehingga terjamin kerahasian dan keamanannya.',
12=>'Apabila terjadi tindakan moneter dan/atau kebijakan lainnya oleh Pemerintah Republik Indonesia yang berakibat langsung maupun tidak langsung pada PERJANJIAN ini, maka PIHAK PERTAMA berhak menyesuaikan syarat dan ketentuan dalam PERJANJIAN ini sebagaimana akan diberitahukan secara tertulis kepada PIHAK KEDUA.',
13=>'PIHAK PERTAMA wajib memberitahukan kepada PIHAK KEDUA apabila dikemudian hari terdapat peraturan baru, tambahan, lanjutan dan/atau perubahan yang dibuat secara sepihak oleh PIHAK PERTAMA dalam masa pemanfaatan fasilitas pembiayaan ini.',
14=>'PIHAK PERTAMA berhak memberikan teguran secara lisan maupun tulisan kepada PIHAK KEDUA bila PIHAK KEDUA Wanprestasi (ingkar janji/lalai) dalam melakukan pembayaran angsuran yang tertunggak dengan syarat dan ketentuan yang berlaku.',
15=>'Setelah PIHAK PERTAMA melaksanakan ketentuan <b>Pasal 5 ayat (5.14)<b> di atas namun PIHAK KEDUA tidak juga melakukan pembayaran angsuran yang tertunggak dan selanjutnya tidak juga menyerahkan BARANG yang merupakan Objek Jaminan Pembiayaan sebagaimana <b>Pasal 4 ayat (4.7)<b> di atas, maka PIHAK PERTAMA berhak melakukan eksekusi BARANG tersebut sebagaimana Surat Kuasa eksekusi yang diberikan PIHAK KEDUA kepada PIHAK PERTAMA dalam perjanjian ini.',
16=>'PIHAK PERTAMA dapat melakukan kerjasama dengan pihak lain untuk melakukan fungsi dan tugas penagihan angsuran yang tertunggak dan/atau eksekusi terhadap BARANG.',
17=>'PIHAK PERTAMA atau wakilnya berhak melakukan penagihan angsuran yang akan dan telah jatuh tempo kepada PIHAK KEDUA dengan biaya penagihan yang ditentukan oleh PIHAK PERTAMA serta berhak menagih pembayaran Ta’zir dan Ta’widh akibat kelalaian yang dilakukan oleh PIHAK KEDUA.',

);

for($i=1;$i<=17;$i++){
	$pdf->ezText('5.'.$i,$fontsize,array('justification'=>'full','left'=>$x1_pasal));
	$pdf->y=$y;
	$pdf->ezText($pasal[$i],$fontsize,array('justification'=>'full','left'=>$x2_pasal));
	$y=$pdf->y;
	//echo $y.' $i<br>';
	if($y<120){
		$pdf->ezNewPage();		
		$pdf->ezSetY($y_pasal);
		$y=$y_pasal;
	}
}


$y-=15;
$pdf->y=$y;
$pdf->selectFont('fonts/Times-Bold');
$pdf->ezText('PASAL 6',$fontsize,array('justification' =>'center'));
$pdf->ezText('PEMBAYARAN ANGSURAN',$fontsize,array('justification' =>'center'));
$pdf->selectFont('fonts/Times-Roman');
$y-=30;
$pdf->y=$y;

$pasal=array();
$pasal=array(
1=>'PIHAK KEDUA tidak dapat menggunakan alasan apapun juga termasuk tidak terbatas pada permasalahan keuangan, hilangnya BARANG, keadaan memaksa /force majeure, tidak dan/atau belum dibayarkannya klaim dari pihak asuransi, jatuh tempo pembayaran yang jatuh bukan pada hari kerja dan/atau alasan maupun peristiwa apapun lainnya yang terjadi pada PIHAK KEDUA untuk menunda pembayaran angsuran tersebut. Jika hari jatuh tempo tersebut jatuh bukan pada hari kerja, maka pembayaran harus dilakukan pada hari kerja sebelum waktu jatuh tempo atau jika tanggal jatuh tempo tersebut jatuh pada hari kerja dalam satu bulan kalender dimana dalam bulan kalender tersebut tidak terdapat tanggal yang mempunyai nomor yang sama dengan tanggal jatuh tempo, maka waktu jatuh tempo akan jatuh pada hari kerja terakhir dalam bulan kalender dimaksud. Lewatnya waktu jatuh tempo satu pembayaran angsuran pun dari tanggal yang telah ditetapkan dalam PERJANJIAN sudah merupakan bukti yang sempurna mengenai kelalaian PIHAK KEDUA untuk memenuhi kewajibannya menurut PERJANJIAN ini tanpa diperlukan adanya teguran, somasi dari PIHAK PERTAMA atau juru sita pengadilan atau pihak lain yang ditunjuk PIHAK PERTAMA.',
2=>'Setiap pembayaran angsuran oleh PIHAK KEDUA kepada PIHAK PERTAMA akan diterima, dipergunakan dan dibukukan oleh PIHAK PERTAMA. Setiap pembukuan dan/atau pencatatan mengenai pembayaran kewajiban PIHAK KEDUA yang dibuat oleh PIHAK PERTAMA, merupakan bukti transaksi yang sah dan mengikat PIHAK KEDUA.',
3=>'Semua pembayaran kewajiban PIHAK KEDUA kepada PIHAK PERTAMA bisa dilakukakan dengan beberapa cara antara lain:
a.	 Melakukan pembayaran angsuran dikantor PIHAK PERTAMA beserta cabangnya. Pembayaran dapat dilakukan secara tunai ataupun menggunakan cek atau bilyet giro yang dibuat atas nama PIHAK KEDUA itu sendiri.
b.	 Melakukan pembayaran angsuran dengan menggunakan website resmi cmd.co.id.
c.	 Melakukan pembayaran angsuran dengan menggunakan nomor Virtual Account Bank yang telah bekerjasama dengan PIHAK PERTAMA.
d.	 Melakukan pembayaran angsuran di Kantor POS Indonesia manapun diseluruh Indonesia.
e.	 Melakukan pembayaran angsuran melalui Tenaga Penagih yang telah ditugaskan dengan memastikan bahwa PIHAK KEDUA menerima kwitansi pembayaran resmi dari PIHAK PERTAMA.
Atas setiap pembayaran kewajiban PIHAK KEDUA akan dikenakan biaya administrasi sebesar yang akan ditetapkan pihak Bank atau lembaga lain dalam hal pembayaran dilakukan melalui Bank atau lembaga lain yang di tunjuk oleh PIHAK PERTAMA, dan apabila pembayaran dilakukan di Kantor Cabang/Perwakilan PIHAK PERTAMA atau melalui Tenaga Penagih yang ditugaskan oleh PIHAK PERTAMA maka besarnya biaya administrasi pembayaran akan ditetapkan oleh PIHAK PERTAMA.',
4=>"Dalam hal PIHAK PERTAMA yang diwakili oleh bagian kolektor tagih terlambat dalam mengambil angsuran pembiayaan BARANG kepada PIHAK KEDUA, Maka sanksi keterlambatan (ta'zir) dan ganti rugi (ta'widh) tetap diberlakukan dan dibebankan kepada PIHAK KEDUA.",
5=>"Untuk setiap hari keterlambatan pembayaran angsuran, maka PIHAK KEDUA dikenakan asksi keterlambatan (Ta’zir) dan ganti kerugian (Ta’widh) seperti yang tercantum dalam Pasal 4 ayat (4.6) diatas, yang mana berhak ditagih secara sekaligus dan seketika tanpa didahului teguran oleh PIHAK PERTAMA kepada PIHAK KEDUA.",
);

for($i=1;$i<=5;$i++){
	$pdf->ezText('6.'.$i,$fontsize,array('justification'=>'full','left'=>$x1_pasal));
	$pdf->y=$y;
	$pdf->ezText($pasal[$i],$fontsize,array('justification'=>'full','left'=>$x2_pasal));
	$y=$pdf->y;
	//echo $y.' $i<br>';
	if($y<100){
		$pdf->ezNewPage();		
		$pdf->ezSetY($y_pasal);
		$y=$y_pasal;
	}
}


$y-=15;
$pdf->y=$y;
$pdf->selectFont('fonts/Times-Bold');
$pdf->ezText('PASAL 7',$fontsize,array('justification' =>'center'));
$pdf->ezText('JAMINAN',$fontsize,array('justification' =>'center'));
$pdf->selectFont('fonts/Times-Roman');
$y-=30;
$pdf->y=$y;

$pdf->ezText('Demi menjamin terbayarnya seluruh jumlah kewajibannya, PIHAK KEDUA dengan ini menjamin PIHAK PERTAMA bahwa:',$fontsize,array('justification'=>'full','left'=>5));
$y=$pdf->y;


$pasal=array();
$pasal=array(
1=>'PIHAK KEDUA akan melaksanakan Akad ini dengan jujur, itikad baik dan penuh tanggungjawab.',
2=>'PIHAK KEDUA wajib menyerahkan hak atas BARANG berupa surat-surat kendaraan kepada PIHAK PERTAMA selaku perusahaan pembiayaan dan surat-surat dapat diambil kembali bila PIHAK KEDUA telah menyelesaikan seluruh kewajibannya kepada PIHAK PERTAMA.',
3=>'Demi terjaminnya keamanan barang berupa surat-surat kendaraan yang diserahkan PIHAK KEDUA kepada PIHAK PERTAMA sebagai jaminan pembayaran angsuran, maka PIHAK KEDUA menyerahkan hak penuh kepada PIHAK PERTAMA untuk menyimpannya dimana saja tanpa terkecuali.',
4=>'Untuk menjaga keamanan serta kenyamanan dalam pengambilan surat-surat kendaraan sebagaimana Pasal 7 ayat (7.1) diatas, PIHAK KEDUA wajib mematuhi seluruh syarat dan ketentuan dalam pengambilan surat-surat kendaraan yang ditetapkan oleh PIHAK PERTAMA.',
5=>"Bila seluruh kewajiban PIHAK KEDUA telah terselesaikan kepada PIHAK PERTAMA namun PIHAK KEDUA belum mengambil surat-surat kendaraan dari PIHAK PERTAMA, maka PIHAK KEDUA dikenakan biaya yang besarnya ditetapkan PIHAK PERTAMA.",

);

for($i=1;$i<=5;$i++){
	$pdf->ezText('7.'.$i,$fontsize,array('justification'=>'full','left'=>$x1_pasal));
	$pdf->y=$y;
	$pdf->ezText($pasal[$i],$fontsize,array('justification'=>'full','left'=>$x2_pasal));
	$y=$pdf->y;
	//echo $y.' $i<br>';
	if($y<100){
		$pdf->ezNewPage();		
		$pdf->ezSetY($y_pasal);
		$y=$y_pasal;
	}
}

$y-=5;
$pdf->ezNewPage();	
$pdf->ezSetY($y_pasal);
$y=$y_pasal;
$pdf->y=$y;
$pdf->selectFont('fonts/Times-Bold');
$pdf->ezText('PASAL 8',$fontsize,array('justification' =>'center'));
$pdf->ezText('PELUNASAN YANG DIPERCEPAT',$fontsize,array('justification' =>'center'));
$pdf->selectFont('fonts/Times-Roman');
$y-=30;
$pdf->y=$y;

$pasal=array();
$pasal=array(
1=>'Atas persetujuan PIHAK PERTAMA, PIHAK KEDUA dapat melunasi baik seluruh ataupun sebagian kewajibannya kepada PIHAK PERTAMA diluar ketentuan mengenai jangka waktu pengembalian kewajiban yang telah disepakati Para Pihak.',
2=>'Apabila PIHAK KEDUA ingin melunasi kewajibannya sebelum waktunya, maka PIHAK KEDUA wajib menyampaikan pemberitahuan secara tertulis kepada PIHAK PERTAMA maksimal 30 hari sebelum tanggal jatuh tempo pembayaran berikutnya dan karenanya PIHAK KEDUA wajib membayar Sisa Kewajiban, Sanksi Keterlambatan, Ganti Rugi, Biaya Administrasi pelunasan dipercepat dan Biaya Asuransi tidak dapat ditarik kembali, adapun syarat dan ketentuan pelunasan seluruh sisa kewajiban sebagai berikut :',
);

for($i=1;$i<=2;$i++){
	$pdf->ezText('8.'.$i,$fontsize,array('justification'=>'full','left'=>$x1_pasal));
	$pdf->y=$y;
	$pdf->ezText($pasal[$i],$fontsize,array('justification'=>'full','left'=>$x2_pasal));
	$y=$pdf->y;
	//echo $y.' $i<br>';
	if($y<100){
		$pdf->ezNewPage();		
		$pdf->ezSetY($y_pasal);
		$y=$y_pasal;
	}
}

$query_produk=pg_query("select * from tblproduk_detail_tenor where fk_produk='".$lrow["fk_produk"]."'");
while($lrow_produk=pg_fetch_array($query_produk)){

	$data[$i]['data1'] = $lrow_produk["tenor"].' Bulan';	
	$data[$i]['data2'] =  $lrow_produk["minimal_dipercepat"].' Bulan';	
	$i++;

}
$judul['data1'] = 'Jumlah Tenor Pembiayaan';
$judul['data2'] = 'Telah Melaksanakan Pembayaran Angsuran Sebanyak';

$lining['showHeadings'] = 1;
$lining['shaded'] = 0;
$lining['showLines'] = 1;
$lining['xPos'] = 300;
$lining['fontSize'] = 9;
$lining['rowGap'] = 1.5;
$lining['colGap'] = 1.5;

$lining['cols']['data1']['justification'] = 'center';
$lining['cols']['data2']['justification'] = 'center';
$size['data1'] = '190';
$size['data2'] = '270';

$pdf->y=$y-15;
$y-=150;
$pdf->ezTable($data,$judul,'',$lining,$size);
$pdf->y=$y;
$pdf->selectFont('fonts/Times-Bold');
$pdf->ezText('PASAL 9',$fontsize,array('justification' =>'center'));
$pdf->ezText('ASURANSI',$fontsize,array('justification' =>'center'));
$pdf->selectFont('fonts/Times-Roman');
$y-=25;
$pdf->y=$y;


$pasal=array();
$pasal=array(
1=>'PIHAK KEDUA berkewajiban mengasuransikan BARANG terhadap bahaya-bahaya termasuk tapi tidak terbatas pada kecelakaan/kehilangan dengan premi yang dibayar oleh PIHAK KEDUA dan PIHAK KEDUA wajib menanggung seluruh biaya kelengkapan berkas-berkas klaim asuransi yang timbul dikemudian hari. PIHAK KEDUA mengalihkan hak (mencedeer) kepada dan diterima oleh PIHAK PERTAMA segala hak atas asuransi BARANG serta memberi kuasa kepada PIHAK PERTAMA untuk melakukan pengurusan dan penerimaan klaim asuransi, dan setelah diterimanya pembayaran klaim asuransi, maka PIHAK PERTAMA berhak secara langsung mengkompensasikannya pembayaran klaim asuransi dengan seluruh kewajiban PIHAK KEDUA berdasarkan PERJANJIAN.',
2=>'PIHAK KEDUA wajib mengasuransikan BARANG pada Perusahaan Asuransi yang ditunjuk oleh PIHAK PERTAMA.',
3=>'Untuk keperluan Asuransi Kendaraan, PIHAK KEDUA dengan ini memberikan Kuasa/wewenang kepada PIHAK PERTAMA untuk mewakili PIHAK KEDUA menandatangani Akad Asuransi dengan Perusahaan Asuransi.',
4=>'Bilamana terjadi kerusakan, kehilangan atau risiko lainnya pada BARANG tersebut, maka PIHAK KEDUA harus segera melaporkannya kepada PIHAK PERTAMA atau perusahaan asuransi dalam tenggang waktu 3 x 24 jam setelah kejadian.',
5=>'Dalam hal terjadi kegagalan mendapatkan ganti rugi dari pihak asuransi, maka PIHAK KEDUA berjanji tidak menggunakan alasan tersebut sebagai tangkisan untuk tidak melaksanakan atau menunda kewajiban pembayaran angsurannya kepada PIHAK PERTAMA.',
6=>'Apabila ada klaim asuransi terkait kendaraan yang dibiayai dan ternyata klaim asuransi tersebut ditolak oleh perusahaan asuransi berdasarkan ketentuan yang berlaku, maka seluruh kewajiban PIHAK KEDUA kepada PIHAK PERTAMA dalam Perjaanjian ini tetap melekat dan seluruh kewajiban PIHAK KEDUA kepada PIHAK PERTAMA wajib dibayar hingga lunas.',
7=>'Sehubungan pembayaran premi asuransi didahulukan oleh PIHAK PERTAMA kepada Perusahaan Asuransi dalam melindungi BARANG atau Objek pembiayaan, yang mana pembayaran premi asuransi tersebut diakumulasikan ke dalam pembayaran angsuran PIHAK KEDUA setiap bulannya. Bila PIHAK KEDUA lalai dalam melaksanakan kewajibannya untuk membayar angsuran setiap bulannya dalam waktu tertentu, maka PIHAK PERTAMA berhak tanpa persetujuan PIHAK KEDUA untuk tidak melanjutkan perlindungan asuransi BARANG atau Objek pembiayaan ke Perusahaan Asuransi sehingga bila timbul resiko kerugian dan/atau kerusakan maka beban resiko yang timbul menjadi tanggung jawab PIHAK KEDUA sepenuhnya.',
8=>'Selama Akad perjanjian ini berlaku, PIHAK KEDUA tidak berhak untuk membatalkan asuransi terhadap BARANG tersebut',
);


for($i=1;$i<=8;$i++){
	$pdf->ezText('9.'.$i,$fontsize,array('justification'=>'full','left'=>$x1_pasal));
	$pdf->y=$y;
	$pdf->ezText($pasal[$i],$fontsize,array('justification'=>'full','left'=>$x2_pasal));
	$y=$pdf->y;
	//echo $y.' $i<br>';
	if($y<100){
		$pdf->ezNewPage();		
		$pdf->ezSetY($y_pasal);
		$y=$y_pasal;
	}
}


$y-=15;
$pdf->y=$y;
$pdf->selectFont('fonts/Times-Bold');
$pdf->ezText('PASAL 10',$fontsize,array('justification' =>'center'));
$pdf->ezText('BERAKHIRNYA PERJANJIAN',$fontsize,array('justification' =>'center'));
$pdf->selectFont('fonts/Times-Roman');
$y-=30;
$pdf->y=$y;

$pasal=array();
$pasal=array(
1=>'PERJANJIAN akad ini mulai berlaku dan mengikat sejak tanggal ditandatanganinya PERJANJIAN ini oleh kedua belah pihak dan berakhir sampai PIHAK KEDUA telah menyelesaikan seluruh kewajibannya.',
2=>'PIHAK KEDUA dinyatakan wanprestasi dan PERJANJIAN ini dapat berakhir secara sepihak dan oleh karenanya wajib melunasi seluruh kewajibannya dengan sekaligus atau menyerahkan BARANG kepada PIHAK PERTAMA dan PIHAK PERTAMA berhak menagih pelunasan seluruh kewajiban dengan seketika dan sekaligus dari PIHAK KEDUA atau meminta penyerahan BARANG dari PIHAK KEDUA berdasarkan prosedur penanganan pembayaran kewajiban yang ditetapkan PIHAK PERTAMA, tanpa memerlukan pemberitahuan, teguran atau tagihan dari PIHAK PERTAMA atau juru sita Pengadilan atau pihak lain yang ditunjuk PIHAK PERTAMA, apabila terjadi salah satu hal atau lebih peristiwa sebagai berikut :',

);

for($i=1;$i<=2;$i++){
	$pdf->ezText('10.'.$i,$fontsize,array('justification'=>'full','left'=>$x1_pasal));
	$pdf->y=$y;
	$pdf->ezText($pasal[$i],$fontsize,array('justification'=>'full','left'=>$x2_pasal));
	$y=$pdf->y;
	//echo $y.' $i<br>';
	if($y<100){
		$pdf->ezNewPage();		
		$pdf->ezSetY($y_pasal);
		$y=$y_pasal;
	}
}

$pasal=array(
1=>'PIHAK KEDUA Wanprestasi/cedera janji/lalai dalam melaksanakan kewajibannya dan dalam hal ini PIHAK KEDUA tidak mengindahkan semua Surat Peringatan yang sudah diberikan oleh PIHAK PERTAMA kepadanya.',
2=>'PIHAK KEDUA menunjukan sikap tidak kooperatif yang dapat mengancam keselamatan fisik, psykis serta tindakan yang dapat menimbulkan kerugian material maupun immaterial bagi PIHAK PERTAMA atau wakilnya selama proses pelaksanaan AKAD ini.',
3=>'Harta kekayaan PIHAK KEDUA, disita baik sebagian maupun seluruhnya atau menjadi objek suatu perkara yang menurut pendapat PIHAK PERTAMA sendiri dapat mempengaruhi kemampuan PIHAK KEDUA untuk membayar kembali kewajibannya dalam PERJANJIAN ini.',
4=>'PIHAK KEDUA meninggal dunia atau sakit berkelanjutan atau cacat tetap yang menurut pendapat PIHAK PERTAMA bahwa PIHAK KEDUA tidak akan mampu untuk menyelesaikan kewajiban-kewajibannya dalam AKAD ini, kecuali ada penerima dan/atau penerus hak/ahli warisnya yang dengan persetujuan tertulis dari PIHAK PERTAMA, sanggup dan bersedia untuk memenuhi seluruh kewajiban PIHAK KEDUA berdasarkan ketentuan dalam AKAD ini dan mengikuti ketentuan pengalihan kewajiban yang ditetapkan PIHAK PERTAMA.',
5=>'PIHAK KEDUA berada dibawah pengampuan (under curatele gesteld) atau karena sebab apapun PIHAK KEDUA tidak cakap atau tidak berhak atau tidak berwenang lagi untuk melakukan pengurusan, atau pemilikan atas dan terhadap kekayaannya, baik sebagian atau seluruhnya.',
6=>'PIHAK KEDUA mengajukan permohonan kepailitan atau penundaan pembayaran kewajiban hutangnya (surseance van betaling) dengan adanya putusan hakim niaga atau PIHAK KEDUA dinyatakan pailit atau suatu permohonan kepailitan diajukan terhadap PIHAK KEDUA atas permintaan pihak manapun.',
7=>'BARANG dipindah tangankan atau dijaminkan kepada pihak ketiga tanpa mendapat persetujuan secara tertulis terlebih dahulu dari PIHAK PERTAMA.',
8=>'BARANG digunakan untuk hal-hal selain yang diperkenankan oleh Prinsip Syariah.',
9=>'PIHAK KEDUA dan/atau Objek Jaminan terlibat dalam suatu perkara pidana atau perdata dan karenanya menurut pendapat PIHAK PERTAMA sendiri PIHAK KEDUA tidak akan mampu untuk menyelesaikan kewajiban-kewajibanya dalam PERJANJIAN ini.',
10=>'PIHAK KEDUA lalai atau wanprestasi atas fasilitas pembiayaan lainnya yang diberikan oleh PIHAK PERTAMA.',
11=>'PIHAK KEDUA terbukti memberikan keterangan, data, informasi, surat pernyataan atau dokumen-dokumen yang tidak benar, palsu dan tidak sah dalam rangka atau selama pemberian fasilitas pembiayaan ini. ',
);
$alphabet = range('a', 'z');
for($i=1;$i<=11;$i++){
	$pdf->ezText($alphabet[$i-1].'.',$fontsize,array('justification'=>'full','left'=>38));
	$pdf->y=$y;
	$pdf->ezText($pasal[$i],$fontsize,array('justification'=>'full','left'=>50));
	$y=$pdf->y;
	//echo $y.' $i<br>';
	if($y<115){
		$pdf->ezNewPage();		
		$pdf->ezSetY($y_pasal);
		$y=$y_pasal;
	}
}

$y-=15;
$pdf->y=$y;
$pdf->selectFont('fonts/Times-Bold');
$pdf->ezText('PASAL 11',$fontsize,array('justification' =>'center'));
$pdf->ezText('KETENTUAN LAINNYA',$fontsize,array('justification' =>'center'));
$pdf->selectFont('fonts/Times-Roman');
$y-=30;
$pdf->y=$y;


$pasal=array();
$pasal=array(
1=>'Setiap korespondensi/pemberitahuan yang berhubungan dengan Akad ini akan disampaikan melalui alamat yang tercantum dalam Akad ini. PIHAK KEDUA wajib menginformasikan secara tertulis apabila terdapat perubahan alamat rumah/tempat bekerja yang dilengkapi dengan surat pendukung.',
2=>'Semua dan setiap wewenang dan Kausa yang diberikan oleh PIHAK KEDUA kepada PIHAK PERTAMA berdasarkan Akad ini merupakan bagian penting dan tidak dapat dipisahkan dari Akad ini, karena tanpa adanya kuasa-kuasa itu Akad ini tidak akan dibuat, oleh karena itu kuasa-kuasa tersebut tidak dapat ditarik kembali maupun dibatalkan oleh sebab-sebab apapun termasuk yang tercantum dalam Pasal 1813 KUHPerdata, PIHAK KEDUA dengan ini melepaskan ketentuan yang termasuk dalam Pasal 1814 dan 1816 KUHPerdata.',
3=>'Mengenai Akad ini, PIHAK KEDUA melepaskan ketentuan Pasal 1266 KUHPerdata, tentang Tata Cara Mengakhiri Sesuatu Akad sehingga pelaksanaan ketentuan dalam Akad ini tidak perlu melalui Putusan Pengadilan.',
4=>'Segala sesuatu yang belum diatur dalam Akad ini akan diatur kemudian dalam Akad lain yang merupakan Akad tambahan dan satu kesatuan yang tidak terpisahkan dari Akad ini.',
5=>'Semua lampiran merupakan satu kesatuan dan bagian yang tidak terpisahkan dari Akad ini.',
6=>'Terhadap Akad  ini berlaku hukum yang berlaku di Negara Republik Indonesia.',
);

for($i=1;$i<=6;$i++){
	$pdf->ezText('11.'.$i,$fontsize,array('justification'=>'full','left'=>$x1_pasal));
	$pdf->y=$y;
	$pdf->ezText($pasal[$i],$fontsize,array('justification'=>'full','left'=>$x2_pasal));
	$y=$pdf->y;
	//echo $y.' $i<br>';
	if($y<100){
		$pdf->ezNewPage();		
		$pdf->ezSetY($y_pasal);
		$y=$y_pasal;
	}
}

$y-=15;
$pdf->y=$y;
$pdf->selectFont('fonts/Times-Bold');
$pdf->ezText('PASAL 12',$fontsize,array('justification' =>'center'));
$pdf->ezText('PENYELESAIAN PERSELISIHAN',$fontsize,array('justification' =>'center'));
$pdf->selectFont('fonts/Times-Roman');
$y-=30;
$pdf->y=$y;

$pasal=array();
$pasal=array(
1=>'Bilamana timbul perbedaan pendapat atau perselisihan atau sengketa diantara PIHAK PERTAMA dan PIHAK KEDUA sehubungan dengan PERJANJIAN ini atau pelaksanaannya, maka hal tersebut akan diselesaikan musyawarah untuk mencapai kata mufakat oleh kedua belah pihak.',
2=>'Apabila penyelesaian perselisihan melalui musyawarah dan mufakat tidak tercapai, maka kedua belah pihak sepakat untuk memilih penyelesaian sengketa di Lembaga Alternatif Penyelesaian Sengketa (LAPS) atau Badan Mediasi Pembiayaan Pergadaian Indonesia (BMPPI) yang berwenang atau di kantor panitera Pengadilan Agama ditempat PIHAK PERTAMA berada.',
);

for($i=1;$i<=2;$i++){
	$pdf->ezText('12.'.$i,$fontsize,array('justification'=>'full','left'=>$x1_pasal));
	$pdf->y=$y;
	$pdf->ezText($pasal[$i],$fontsize,array('justification'=>'full','left'=>$x2_pasal));
	$y=$pdf->y;
	//echo $y.' $i<br>';
	if($y<100){
		$pdf->ezNewPage();		
		$pdf->ezSetY($y_pasal);
		$y=$y_pasal;
	}
}


$y-=15;
$pdf->y=$y;
$pdf->selectFont('fonts/Times-Bold');
$pdf->ezText('PASAL 13',$fontsize,array('justification' =>'center'));
$pdf->ezText('PENUTUP',$fontsize,array('justification' =>'center'));
$pdf->selectFont('fonts/Times-Roman');
$y-=30;
$pdf->y=$y;

$pasal=array();
$pasal=array(
1=>'Uraian pasal demi pasal Akad ini, telah dibaca, dimengerti dan dipahami serta disetujui oleh PIHAK KEDUA dan PIHAK PERTAMA.',
2=>'Segala sesuatu yang belum diatur atau perubahan dalam Akad ini akan di atur dalam surat- menyurat berdasarkan kesepakatan bersama antara PIHAK PERTAMA dan PIHAK KEDUA  yang merupakan bagian yang tidak terpisahkan dari Akad ini.',
3=>'Ketentuan dan Syarat-syarat dari Perjanjian ini dapat berubah sesuai dengan Peraturan Perundang-Undangan yang berlaku.',

);

for($i=1;$i<=3;$i++){
	$pdf->ezText('13.'.$i,$fontsize,array('justification'=>'full','left'=>$x1_pasal));
	$pdf->y=$y;
	$pdf->ezText($pasal[$i],$fontsize,array('justification'=>'full','left'=>$x2_pasal));
	$y=$pdf->y;
	//echo $y.' $i<br>';
	if($y<100){
		$pdf->ezNewPage();		
		$pdf->ezSetY($y_pasal);
		$y=$y_pasal;
	}
}

$y-=40;
$pdf->addText($x1+185,$y, $fontsize,$lrow['nm_cabang'].' , '.$tgl_pengajuan);
$y-=30;

$pdf->addText($x1+85, $y, $fontsize,'PIHAK KEDUA');
$pdf->addText($x1+295, $y, $fontsize,'MENYETUJUI : Suami / Istri/ Komisaris');

$y-=55;
$pdf->addText($x1+55, $y, $fontsize,'_______________________');
$pdf->addText($x1+295, $y, $fontsize,'________________________________');

}
//end content
if($template!='t')$filename=$id_edit.'-'.$lrow["nm_customer"].'.pdf';
else $filename='template.pdf';
$options=array();//untuk rename nama file
$options['Content-Disposition']=$filename;

$pdf->ezStream($options);   

?>