<?php
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/timestamp.inc.php';
require '../classes/ezpdf.class.php';
require '../requires/convert.inc.php';
require '../requires/numeric.inc.php';

$id_edit = $_REQUEST['fk_sbg'];
$template=$_REQUEST['flag'];

				
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
//echo $lrow["fk_karyawan_kacab"];
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
$kacab=kacab($lrow["fk_cabang"],$lrow["tgl_pengajuan"]);
$lrow["nm_kacab"]=$kacab["nm_depan"];
$lrow["alamat_kacab"]=$kacab["alamat"];
$tgl_pengajuan=date("d",strtotime($lrow['tgl_pengajuan'])).' '.getMonthName(date("m",strtotime($lrow['tgl_pengajuan'])),2).' '.date("Y",strtotime($lrow['tgl_pengajuan']));

if($lrow["jenis_customer"]=='0'){
	$nm_pihak_lain=$lrow["nm_pasangan"];
}elseif($lrow["jenis_customer"]=='1'){
	$nm_pihak_lain=$lrow["nm_pemilik"];
}

if(!$nm_pihak_lain)$nm_pihak_lain='-';
$pdf = new Cezpdf('A4'); 
//$pdf->ezImage('pernyataan_bersama2.png',0, 0, 0);
$pdf->setLineStyle(1);

$y_table=680;
$heigth=$pdf->ez['pageHeight'];
$width=$pdf->ez['pageWidth'];
$pdf->ez['topMargin'] = $heigth-$y_table;
$pdf->ez['bottomMargin']=10;
$pdf->ez['leftMargin']=20;
$pdf->ez['rightMargin']=30;

if($template=='t'){
$fontsize= 9;

$x1=30;
$x2=$x1+125;

$y-=80;
$pdf->y=855;
//echo $pdf->y;
$y=$pdf->y;

$pdf->y=$y;

$x1=79;
$x2=$x1+122;
$x3=$x2+91;
$x4=$x3+202;
$nm_kacab_dealer=get_rec("tblkaryawan_dealer",'nm_karyawan'," fk_dealer='".$lrow["fk_partner_dealer"]."' and fk_jabatan='DLR-KACAB'");

if(!$nm_kacab_dealer)$nm_kacab_dealer="-";

$pdf->selectFont('fonts/Times-Roman');
$y-=94;
$pdf->addText($x1, $y, $fontsize,$lrow["nm_partner"]);
$y-=19;
$pdf->addText($x1, $y, $fontsize,$lrow["alamat"]);
$y-=19;
$pdf->addText($x2+3, $y, $fontsize,$nm_kacab_dealer);
$pdf->addText($x3+108, $y, $fontsize,"Kepala Cabang");
$y-=19;
$pdf->addText($x2+50, $y, $fontsize,$lrow["nm_partner"]);
if($lrow["jenis_customer"]=='0'){
	$y-=50;
	$pdf->addText($x1, $y, $fontsize,$lrow["nm_customer"]);
	$y-=19;
	$pdf->addText($x1, $y, $fontsize,$lrow["alamat_ktp"]);
	$y-=19;
	$pdf->addText($x2+3, $y, $fontsize,$lrow["nm_customer"]);
	$pdf->addText($x3+108, $y, $fontsize,$lrow["jabatan"]);
	$y-=19;
	$pdf->addText($x2+50, $y, $fontsize,$lrow["nm_customer"]);
}else if($lrow["jenis_customer"]=='1'){
	$y-=50;
	$pdf->addText($x1, $y, $fontsize,$lrow["nm_badan_usaha"]);
	$y-=19;
	$pdf->addText($x1, $y, $fontsize,$lrow["alamat_badan_usaha"]);
	$y-=19;
	$pdf->addText($x2+3, $y, $fontsize,$lrow["nm_customer"]);
	$pdf->addText($x3+108, $y, $fontsize,$lrow["jabatan"]);
	$y-=19;
	$pdf->addText($x2+50, $y, $fontsize,$lrow["nm_badan_usaha"]);
}

$y-=50;
$pdf->addText($x1, $y, $fontsize,$nm_pihak1);
$pdf->addText($x3+22, $y, $fontsize,"Kepala Cabang");
$y-=36;
$pdf->addText($x1, $y, $fontsize,$lrow["alamat"]);
$y-=60;
$pdf->addText($x1, $y, $fontsize,$nm_pihak1);

$y-=79;
$pdf->addText($x1, $y, $fontsize,convert_money("",$lrow["total_nilai_pinjaman"]));
$y-=105;
$pdf->addText($x1, $y, $fontsize,$id_edit);
$pdf->addText($x3+80, $y, $fontsize,$tgl_pengajuan);
$y-=94;
$pdf->addText($x1+25, $y, $fontsize,$lrow["ovd_terima_bpkb"]);
$pdf->addText($x1+73, $y, $fontsize,convert_terbilang($lrow["ovd_terima_bpkb"]));

$pdf->ezNewPage();
$fontsize= 9;

$y-=80;
$pdf->y=842;
//echo $pdf->y;
$y=$pdf->y;

$pdf->y=$y;

$pdf->selectFont('fonts/Times-Roman');

$y-=145;
$pdf->addText($x2-17, $y, $fontsize,$lrow["nm_merek"].'/'.$lrow["nm_tipe"].'/'.$lrow["tahun"]);
$pdf->addText($x3+110, $y, $fontsize,$lrow["status_barang"]);
$y-=12;
$pdf->addText($x2-17, $y, $fontsize,$lrow["no_rangka"]);
$pdf->addText($x3+110, $y, $fontsize,$lrow["nm_bpkb"]);
$y-=12;
$pdf->addText($x2-17, $y, $fontsize,$lrow["no_mesin"]);
$pdf->addText($x3+110, $y, $fontsize,$lrow["no_bpkb"]);

$y-=345;
$pdf->addText($x1+120,$y, $fontsize,$lrow['nm_cabang']);
$pdf->addText($x1+260,$y, $fontsize,$tgl_pengajuan);

$y-=121;
$pdf->addText($x1-2,$y, $fontsize,$nm_pihak1);
$pdf->addText($x1+146,$y, $fontsize,$lrow["nm_customer"]);
$pdf->addText($x2+214,$y, $fontsize,$lrow["nm_dealer"]);
}
	else
{
$all = $pdf->openObject();
$pdf->saveState();
$fontsize= 9;

//Header
$pdf->selectFont('fonts/Times-Roman');
$pdf->ezText('<b>SURAT PERNYATAAN BERSAMA</b>',14,array('justification' =>'center'));
$y=$pdf->y;
$y-=10;

$pdf->y=$y;
$pdf->ezText('Yang bertanda tangan dibawah ini : ',$fontsize,array('justification'=>'left','left'=>5));

$x1=18;
$x2=$x1+120;

$y=$pdf->y;
$y-=20;
$pdf->addText($x1+10, $y, $fontsize,'I.');
$pdf->y=$y+10;
$nm_kacab_dealer=get_rec("tblkaryawan_dealer",'nm_karyawan'," fk_dealer='".$lrow["fk_partner_dealer"]."' and fk_jabatan='DLR-KACAB'");

if(!$nm_kacab_dealer)$nm_kacab_dealer="-";

$pdf->ezText(''.$lrow["nm_partner"].' beralamat di '.$lrow["alamat"].' dalam hal ini diwakili oleh '.$nm_kacab_dealer.' Jabatan Kepala Cabang yang bertindak untuk dan atas nama '.$lrow["nm_partner"].' yang selanjutnya disebut PIHAK PERTAMA',$fontsize,array('justification'=>'left','left'=>20));

$y=$pdf->y;
$y-=20;
$pdf->addText($x1+10, $y, $fontsize,'II.');
$pdf->y=$y+10;

if($lrow["jenis_customer"]=='0'){
	$pdf->ezText(''.$lrow["nm_customer"].' beralamat di '.$lrow["alamat_ktp"].' dalam hal ini diwakili oleh '.$lrow["nm_customer"].' yang bertindak untuk dan atas nama '.$lrow["nm_customer"].' yang selanjutnya disebut PIHAK KEDUA',$fontsize,array('justification'=>'left','left'=>20));
}
else if($lrow["jenis_customer"]=='1'){
	$pdf->ezText(''.$lrow["nm_badan_usaha"].' beralamat di '.$lrow["alamat_badan_usaha"].' dalam hal ini diwakili oleh '.$lrow["nm_customer"].' yang bertindak untuk dan atas nama '.$lrow["nm_badan_usaha"].' yang selanjutnya disebut PIHAK KEDUA',$fontsize,array('justification'=>'left','left'=>20));
}

$y=$pdf->y;
$y-=20;
$pdf->addText($x1+10, $y, $fontsize,'III.');
$pdf->y=$y+10;

$pdf->ezText(''.$nm_pihak1.' Jabatan Kepala Cabang Yang dalam hal ini bertindak dan atas nama '.$lrow["nm_perusahaan"].' yang mempunyai Kantor Pusat '.$lrow["alamat"].' dengan kantor-kantor cabang diwilayah Indonesia yang selanjutnya disebut PIHAK KETIGA. Dalam hal ini bertindak dalam jabatannya selaku kuasa dan atas nama '.$lrow["nm_perusahaan"].' , yang selanjutnya disebut PIHAK KETIGA',$fontsize,array('justification'=>'left','left'=>20));

$y=$pdf->y;

$y-=20;
$x1+=10;
$y=$pdf->y;
$y-=10;

$pdf->y=$y;
$pdf->ezText('PIHAK PERTAMA dan PIHAK KEDUA dengan ini menyatakan sebenarnya bahwa :',$fontsize,array('justification'=>'left','left'=>9));


$y-=30;

$pdf->addText($x1, $y, $fontsize,'1.');
$pdf->y=$y+10;
$pdf->ezText('PIHAK PERTAMA mengakui dan membenarkan telah menjual secara tunai '.convert_money("",$lrow["total_nilai_pinjaman"]).' unit kendaraan bermotor kepada PIHAK KEDUA yang dananya diperoleh dari fasilitas pembiayaan yang diberikan oleh PIHAK KETIGA kepada PIHAK KEDUA',$fontsize,array('justification'=>'left','left'=>20));

$y-=25;
$pdf->addText($x1, $y, $fontsize,'2.');
$pdf->y=$y+10;;
$pdf->ezText('Sesuai dengan fasilitas pembiayaan yang diberikan oleh PIHAK KETIGA kepada PIHAK KEDUA, PIHAK KEDUA telah mengikat diri menyerahkan kepemilikan kendaraan bermotor tersebut kepada PIHAK KETIGA sesuai dengan Perjanjian Pembiayaan Dengan Akad Murabahah dengan Nomor '.$id_edit.' tertanggal '.$tgl_pengajuan,$fontsize,array('justification'=>'left','left'=>20));

$y-=35;
$pdf->addText($x1, $y, $fontsize,'3.');
$pdf->y=$y+10;
$pdf->ezText('Sebagai penjual kendaraan bermotor, PIHAK PERTAMA terikat pada kewajiban sebagai berikut :',$fontsize,array('justification'=>'left','left'=>20));
$y-=12;
$pdf->addText($x1+12, $y, $fontsize,'a.');
$pdf->y=$y+10;
$pdf->ezText('Bagi kendaran baru, pengurusan pembuatan dokumen kendaraan bermotor berupa SURAT TANDA NOMOR KENDARAAN (STNK) harus selesai sepenuhnya selambat-lambatnya dalam waktu 30 (tiga puluh)  hari dan '.$lrow["ovd_terima_bpkb"].' ('.convert_terbilang($lrow["ovd_terima_bpkb"]).') hari untuk Buku Kepemilikan Kendaraan Bermotor  (BPKB), terhitung setelah Berita Acara Serah Terima Kendaraan antara PIHAK PERTAMA dan PIHAK KEDUA dilakukan. Selanjutnya PIHAK PERTAMA menyerahkan BPKB, Faktur dan Fotocopy STNK kepada PIHAK KETIGA',$fontsize,array('justification'=>'left','left'=>30));

$y-=50;
$pdf->addText($x1+12, $y, $fontsize,'b.');
$pdf->y=$y+10;
$pdf->ezText('Bagi kendaraan bekas pakai, PIHAK PERTAMA diwajibkan menyerahkan Fotocopy STNK, Asli Blanko Kwitansi, dan Fotocopy KTP atas nama pemilik kendaraan terakhir beserta BPKB aslinya kepada PIHAK KETIGA pada saat penandatanganan Perjanjian Pembiayaan dengan Akan Murabahah.',$fontsize,array('justification'=>'left','left'=>30));

$y-=37;
$pdf->addText($x1, $y, $fontsize,'4.');
$pdf->y=$y+10;
$pdf->ezText('Apabila PIHAK PERTAMA lalai dalam memenuhi kewajibannya sesuai Surat Pernyataan Bersama butir 3.a., maka PIHAK PERTAMA wajib membayar denda keterlambatan sesuai ketentuan PIHAK KETIGA.',$fontsize,array('justification'=>'left','left'=>20));

$y-=25;
$pdf->addText($x1, $y, $fontsize,'5.');
$pdf->y=$y+10;
$pdf->ezText('Surat Tanda Nomor Kendaraan (STNK) dab Buku Pemilikan Kendaraan Bermotor (BPKB) tersebut telah disetujui bersama oleh PIHAK KEDUA dan PIHAK KETIGA serta telah diketahui oleh PIHAK PERTAMA dengan keterangan sebagai berikut:
',$fontsize,array('justification'=>'left','left'=>20));

$x3=30;
$x3_isi=$x3+100;
$x4=350;
$x4_isi=$x4+85;

$x_rinci=$x1+20;

$y-=25;
$pdf->addText($x3+10, $y, $fontsize,'Merk/Jenis/Tahun ');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["nm_merek"].'/'.$lrow["nm_tipe"].'/'.$lrow["tahun"]);
$pdf->addText($x4+10, $y, $fontsize,'Kondisi');
$pdf->addText($x4_isi, $y, $fontsize,': '.$lrow["status_barang"]);

$y-=15;
$pdf->addText($x3+10, $y, $fontsize,'No Rangka');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["no_rangka"]);
$pdf->addText($x4+10, $y, $fontsize,'BPKB a/n');
$pdf->addText($x4_isi, $y, $fontsize,': '.$lrow["nm_bpkb"]);

$y-=15;
$pdf->addText($x3+10, $y, $fontsize,'No Mesin');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["no_mesin"]);
$pdf->addText($x4+10, $y, $fontsize,'No BPKB');
$pdf->addText($x4_isi, $y, $fontsize,': '.$lrow["no_bpkb"]);

$y-=20;
$pdf->addText($x1, $y, $fontsize,'6.');
$pdf->y=$y+10;
$pdf->ezText('PIHAK PERTAMA atau PIHAK KEDUA wajib menyerahkan kepada PIHAK KETIGA Buku Pemilikan Kendaraan Bermotor (BPKB) asli dan sah yang dikeluarkan oleh instansi yang berwenang 	dan dokumen lain yang tercantum dalam butir 3a dab 3b untuk disimpan oelh PIHAK KETIGA. Buku Pemilikan Kendaraan Bermotor (BPKB) serta dokumen lainnya tersebut akan dikembalikan dalam keadaan yang utuh kepada PIHAK KEDUA setelah PIHAK KEDUA memenuhi segala kewajibannya. PIHAK PERTAMA dan PIHAK KEDUA dengan alasan apapun tidak akan menuntut PIHAK KETIGA atas Buku Pemilikan Kendaraan Bermotor (BPKB) yang telah diterima kembali.',$fontsize,array('justification'=>'left','left'=>20));

$y-=60;
$pdf->addText($x1, $y, $fontsize,'7.');
$pdf->y=$y+10;
$pdf->ezText('PIHAK PERTAMA dan PIHAK KEDUA dengan ini menegaskan bahwa kendaraan dan Buku Pemilikan Kendaraan Bermotor (BPKB) tersebut tidak sedang dalam sengketa/dijaminkan/dialihkan 	kepada pihak lain diluar PARA PIHAK pada perjanjian ini.',$fontsize,array('justification'=>'left','left'=>20));

$y-=25;
$pdf->addText($x1, $y, $fontsize,'8.');
$pdf->y=$y+10;
$pdf->ezText('PIHAK PERTAMA dan PIHAK KEDUA akan mempertanggungjawabkan secara hukum dan menanggung segala resiko yang timbul atas pernyataan tersebut diatas beserta segala akibat hukumnya, dan untuk selanjutnya  PIHAK KETIGA dibebaskan dari segala tindakan dan tuntutan dari pihak manapun.
',$fontsize,array('justification'=>'left','left'=>20));


$y-=30;
$pdf->y=$y;
$pdf->ezText('Demikian Surat Pernyataan Bersama ini dibuat dengan sebenarnya pada hari dan tanggal diatas. Surat Pernyataan Bersama ini tidak dapat diubah/dicabut/dibatalkan tanpa persetujuan tertulis dari PIHAK KETIGA dan berlaku sampai selesainya kewajiban PIHAK PERTAMA kepada PIHAK KETIGA dan lunasnya PIHAK KEDUA kepada PIHAK KETIGA.',$fontsize,array('justification'=>'left','left'=>10));

$y-=55;
$pdf->addText($x1+178,$y, $fontsize,$lrow['nm_cabang'].', '.$tgl_pengajuan);

$y-=16;
$pdf->addText($x1, $y, $fontsize,'Mengetahui');

$y-=25;
$pdf->addText($x1, $y, $fontsize,$lrow["nm_perusahaan"]);
$pdf->addText($x1+445, $y, $fontsize,'PIHAK PERTAMA');
$pdf->addText($x1+205, $y, $fontsize,'PIHAK KEDUA');

$y-=55;
$pdf->addText($x1, $y, $fontsize,'______________');
$pdf->addText($x1+205, $y, $fontsize,'______________');
$pdf->addText($x1+445, $y, $fontsize,'______________');

$y-=12;
$pdf->addText($x1,$y, $fontsize,$nm_pihak1);
$pdf->addText($x1+205,$y, $fontsize,$lrow["nm_customer"]);
$pdf->addText($x2+320,$y, $fontsize,$lrow["nm_dealer"]);
/*$pdf->selectFont('fonts/Times-Italic');

$y-=40;
$pdf->addText(70, $y, 6,'PERJANJIAN INI TELAH DISESUAIKAN DENGAN KETENTUAN PERATURAN PERUNDANG-UNDANGAN TERMASUK KETENTUAN PERATURAN OTORITAS JASA KEUANGAN');

*/

$pdf->setStrokeColor(0,0,0);

$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');

$y-=90;
//$pdf->ezImage('../print/logo_2.png','30','60','','left','');
$pdf->addPngFromFile('../print/logo_2.png', 35, 5,30,25);
//$pdf->addText(100, 25, 6,'"PERJANJIAN INI TELAH DISESUAIKAN DENGAN KETENTUAN PERATURAN PERUNDANG-UNDANGAN TERMASUK KETENTUAN PERATURAN OTORITAS JASA KEUANGAN"');
$pdf->addText(75, 15, 9,'PT. Capella Multidana Berizin dan Diawasi oleh Otoritas Jasa Keuangan');
}
//end content
if($template!='t')$filename=$id_edit.'-'.$lrow["nm_customer"].'.pdf';
else $filename='template.pdf';
$options=array();//untuk rename nama file
$options['Content-Disposition']=$filename;

$pdf->ezStream($options);   

?>