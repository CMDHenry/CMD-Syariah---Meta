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
where no_sbg='".$id_edit."'
";				
$lrow=pg_fetch_array(pg_query($query));
//showquery($query);
$kacab=kacab($lrow["fk_cabang"],$lrow["tgl_pengajuan"]);
$lrow["nm_kacab"]=$kacab["nm_depan"];
$lrow["alamat_kacab"]=$kacab["alamat"];
if($lrow["kondisi_unit"]=='Bekas' && substr($id_edit, 0, 2) != "23" && substr($id_edit, 0, 2) != "43"){
	$lrow["nm_bpkb"]=$lrow['nm_customer'];//kalau unit bekas biar jangan ambil nama bpkb pembeli pertama
}

if($lrow["jenis_customer"]=='0'){
	$nm_pihak_lain=$lrow["nm_pasangan"];
}elseif($lrow["jenis_customer"]=='1'){
	$nm_pihak_lain=$lrow["nm_pemilik"];
}

if($lrow["jenis_customer"]=='0'){
	$nm_pihak_lain1=$lrow["nm_bpkb"];
	$alamat_pihak_lain1=$lrow["alamat_bpkb"];
	$ktp_pihak_lain1=$lrow["no_ktp_bpkb"];
}elseif($lrow["jenis_customer"]=='1'){
	$nm_pihak_lain1=$lrow["nm_pemilik"];
	$alamat_pihak_lain1=$lrow["alamat_ktp"];
	$ktp_pihak_lain1=$lrow["no_id"];
	$jabatan_pihak_lain1=$lrow["jabatan"];
}
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
if(!$nm_pihak_lain)$nm_pihak_lain='-';

if(!$lrow['tgl_cair'])$lrow['tgl_cair']=$lrow['tgl_pengajuan'];
$tgl_cair_indo=date("d",strtotime($lrow['tgl_cair'])).' '.getMonthName(date("m",strtotime($lrow['tgl_cair'])),2).' '.date("Y",strtotime($lrow['tgl_cair']));
$tgl_pengajuan=date("d",strtotime($lrow['tgl_pengajuan'])).' '.getMonthName(date("m",strtotime($lrow['tgl_pengajuan'])),2).' '.date("Y",strtotime($lrow['tgl_pengajuan']));


$pdf = new Cezpdf('A4');  
$pdf->setLineStyle(1);

$y_table=680;
$heigth=$pdf->ez['pageHeight'];
$width=$pdf->ez['pageWidth'];
$pdf->ez['topMargin'] = $heigth-$y_table;
$pdf->ez['bottomMargin']=10;

$all = $pdf->openObject();
$pdf->saveState();


//Header
$pdf->selectFont('fonts/Times');

if($template=='t'){
$fontsize= 9;
$x1=65;
$x2=$x1+105;

$y=$pdf->y;

if($lrow["nm_bpkb"]==$lrow["nm_customer"]){
	$y-=67;
	$pdf->addText($x2, $y, $fontsize,$lrow["nm_customer"]);
	$y-=20;
	$pdf->addText($x2, $y, $fontsize,$lrow["jabatan"]);//$lrow["nm_pekerjaan"]
	$y-=20;
	$pdf->addText($x2, $y, $fontsize,$lrow["alamat_ktp"]);
	$y-=20;
	$pdf->addText($x2, $y, $fontsize,$lrow["no_id"]);//$lrow["no_id"]
}else{	
	$y-=67;
	$pdf->addText($x2, $y, $fontsize,$nm_pihak_lain1);
	$y-=20;
	$pdf->addText($x2, $y, $fontsize,$jabatan_pihak_lain1);//$lrow["nm_pekerjaan"]
	$y-=20;
	$pdf->addText($x2, $y, $fontsize,$alamat_pihak_lain1);
	$y-=20;
	$pdf->addText($x2, $y, $fontsize,$ktp_pihak_lain1);//$lrow["no_id"]
}
$y-=60;
$pdf->addText($x2, $y, $fontsize,$nm_pihak1);

$arr_alamat = array();
if(strlen($alamat_pihak1) > 17){
	$temp = wordwrap($alamat_pihak1,70,"<split>");
	$arr_alamat = explode("<split>",$temp);
}else {
	$arr_alamat[0] = ($alamat_pihak1);
}

$y-=20;
$pdf->addText($x2, $y, $fontsize,$arr_alamat[0]);
$y-=20;
$pdf->addText($x2, $y, $fontsize,$arr_alamat[1]);


$y-=92;
$pdf->addText($x2, $y, $fontsize,$id_edit);
$pdf->addText($x2+200, $y, $fontsize,$tgl_pengajuan);
$y-=250;
$pdf->addText($x2+40,$y, $fontsize,$lrow['nm_cabang']);
$pdf->addText($x2+135,$y, $fontsize,$tgl_pengajuan);
$y-=121;
$pdf->addText($x2-38,$y, $fontsize,$nm_pihak1);
if($lrow["nm_bpkb"]==$lrow["nm_customer"]){
	$pdf->addText($x2+189,$y, $fontsize,$lrow["nm_customer"]);
}else{
	$pdf->addText($x2+189,$y, $fontsize,$nm_pihak_lain1);
}
}
else{
$fontsize= 9;
$pdf->ezText('SURAT KUASA',14,array('justification' =>'center'));
$y=$pdf->y;
$y-=10;

$pdf->y=$y;
$pdf->ezText('Yang bertanda tangan di bawah ini :',$fontsize,array('justification'=>'left','left'=>5));

$x1=20;
$x2=$x1+120;

$y=$pdf->y;
if($lrow["nm_bpkb"]==$lrow["nm_customer"]){
$y-=15;
$pdf->addText($x1, $y, $fontsize,'   Nama');
$pdf->addText($x2, $y, $fontsize,': '.$lrow["nm_customer"]);
$y-=15;
$pdf->addText($x1, $y, $fontsize,'   Jabatan');
$pdf->addText($x2, $y, $fontsize,': '.$lrow["jabatan"]);//$lrow["nm_pekerjaan"]
$y-=15;
$pdf->addText($x1, $y, $fontsize,'   Alamat');
$pdf->addText($x2, $y, $fontsize,': '.$lrow["alamat_ktp"]);
$y-=15;
$pdf->addText($x1, $y, $fontsize,'   No. KTP');
$pdf->addText($x2, $y, $fontsize,': '.$lrow["no_id"]);//$lrow["no_id"]
}else{
$y-=15;
$pdf->addText($x1, $y, $fontsize,'   Nama');
$pdf->addText($x2, $y, $fontsize,': '.$nm_pihak_lain1);
$y-=15;
$pdf->addText($x1, $y, $fontsize,'   Jabatan');
$pdf->addText($x2, $y, $fontsize,': '.$jabatan_pihak_lain1);//$lrow["nm_pekerjaan"]
$y-=15;
$pdf->addText($x1, $y, $fontsize,'   Alamat');
$pdf->addText($x2, $y, $fontsize,': '.$alamat_pihak_lain1);
$y-=15;
$pdf->addText($x1, $y, $fontsize,'   No. KTP');
$pdf->addText($x2, $y, $fontsize,': '.$ktp_pihak_lain1);//$lrow["no_id"]
}

$y-=10;

$pdf->y=$y;
$pdf->ezText('Dalam hal ini bertindak untuk atas nama diri sendiri yang selanjutnya disebut sebagai PEMBERI KUASA.
Dengan ini memberikan kuasa dengan hak subsitusi kepada ',$fontsize,array('justification'=>'left','left'=>5));

$y=$pdf->y-15;

$pdf->addText($x1, $y, $fontsize,'    Nama');
$pdf->addText($x2, $y, $fontsize,': '.$nm_pihak1);
$y-=15;
$pdf->addText($x1, $y, $fontsize,'    Alamat');
$pdf->addText($x2, $y, $fontsize,': '.$alamat_pihak1);
$y-=15;

$pdf->y=$y;
$pdf->ezText('....................................................................................KHUSUS.................................................................',$fontsize,array('justification'=>'left','left'=>5));
$y-=20;

$pdf->y=$y;
$pdf->ezText('Untuk mewakili serta bertindak untuk atas nama PEMBERI KUASA dalam hal :',$fontsize,array('justification'=>'left','left'=>5));
$y-=30;

$pdf->addText($x1, $y, $fontsize,'-');
$y+=12;
$pdf->y=$y;
$pdf->ezText('Mengurus dan melaksanakan serta menandatangani pengikatan Akta Jaminan Fidusia di Notaris sehubungan dengan adanya Perjanjian Pembiayaan Multiguna Dengan Pembayaran Secara Angsuran Nomor Akad : '.$id_edit.' Tanggal : '.$tgl_pengajuan.', selanjutnya disebut Perjanjian Pembiayaan.',$fontsize,array('justification'=>'left','left'=>30));
$y-=50;

$pdf->addText($x1, $y, $fontsize,'-');
$y+=12;
$pdf->y=$y;
$pdf->ezText('Menghadap instansi-instansi/pejabat-pejabat lainnya memohon atau memberikan semua keterangan, mengajukan semua surat-surat yang berhubungan dengan pengikatan jaminan fidusia ini, menerima atau melakukan pembayaran, membuat atau menerima kwitansi pembayaran, dan/atau melakukan segala hal upaya/perbuatan yang umumnya dapat dilakukan oleh seorang kuasa/wakil secara hukum guna kepentingan tersebut di atas sesuai dengan ketentuan hukum yang berlaku di Indonesia.',$fontsize,array('justification'=>'left','left'=>30));
$y-=70;

$pdf->addText($x1, $y, $fontsize,'-');
$y+=12;
$pdf->y=$y;
$pdf->ezText('Kuasa ini merupakan bagian yang tidak terpisahkan dari Perjanjian Pembiayaan Multiguna Dengan Pembayaran Secara Angsuran, karena itu tidak dapat ditarik kembali dan juga tidak akan berakhir karena sebab apapun juga, antara lain karena sebab-sebab yang termaktub dalam Pasal 1813, 1814, 1816 Kitab Undang-Undang Hukum Perdata Indonesia.',$fontsize,array('justification'=>'left','left'=>30));
$y-=50;

$pdf->addText($x1, $y, $fontsize,'-');
$y+=12;
$pdf->y=$y;
$pdf->ezText('Semua biaya-biaya, ongkos-ongkos, dan bea materai untuk pembuatan Akta Jaminan Fidusia di Notaris dan pendaftaran Fidusia pada Kantor Pendaftaran Fidusia menjadi tanggungan dan wajib dibayar oleh Pemberi Kuasa.',$fontsize,array('justification'=>'left','left'=>30));
$y-=40;

$pdf->y=$y;
$pdf->ezText('Surat kuasa ini diberikan dengan Hak Melimpahkan (Substitusi) baik sebagian atau seluruhnya yang dikuasakan ini kepada orang lain.',$fontsize,array('justification'=>'left','left'=>5));

$y-=45;
$pdf->addText($x1+135, $y, $fontsize,'__________________,_________________');

$y-=45;
$pdf->addText($x1+135, $y, $fontsize,'PENERIMA KUASA');
$pdf->addText($x1+305, $y, $fontsize,'PEMBERI KUASA');

$y-=35;
//$pdf->line(330,$y+12,385,$y+12);
$pdf->addText($x1+325, $y, $fontsize-2,'Materai');
//$pdf->line(330,$y-15,385,$y-15);


$y-=30;
$pdf->addText($x1+135, $y, $fontsize,'___________________');
$pdf->addText($x1+305, $y, $fontsize,'___________________');
$y-=90;
$pdf->addPngFromFile('../print/logo_2.png', 35, 5,30,25);
$pdf->addText(75, 15, $fontsize,'PT. Capella Multidana Berizin dan Diawasi oleh Otoritas Jasa Keuangan');

}



$pdf->setStrokeColor(0,0,0);

$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');

	
//end content
$pdf->ezStream();   

?>