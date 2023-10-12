<?php
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/timestamp.inc.php';
require '../classes/ezpdf.class.php';
require '../requires/convert.inc.php';
require '../requires/numeric.inc.php';	

$id_edit = $_REQUEST['id_edit'];


$query="
select * from (
	select tblcustomer.npwp as npwp_cust,tblcabang.alamat as alamat_cabang,fk_sbg as no_sbg,* from data_gadai.tbltaksir_umum 
	left join tblcustomer on no_cif = fk_cif
	left join(
		select fk_sbg,tgl_cair from tblinventory
	)as tblinventory on fk_sbg=no_sbg_ar
	inner join (
		select warna,nm_tipe,no_fatg as no_fatg1,nm_merek,nm_jenis_barang from viewkendaraan
	)as tbldetail on no_fatg=no_fatg1
	left join(
		select tgl_bayar,no_sbg from data_gadai.tblproduk_cicilan
		left join data_fa.tblangsuran on fk_sbg=no_sbg and lama_pinjaman=angsuran_ke
	)as tblangs_akhir on fk_sbg=no_sbg
	left join tblcabang on kd_cabang=fk_cabang
	where no_fatg='".$id_edit."' 
	order by tgl_cair
 ) as tblmain
";	

//showquery($query);
$lrow=pg_fetch_array(pg_query($query));			

$fk_cabang=$lrow['fk_cabang'];


$query_serial="select nextserial_cabang('CUST':: text,'".$fk_cabang."')";
$lrow_serial=pg_fetch_array(pg_query($query_serial));
$no_surat=$lrow_serial["nextserial_cabang"];	

if(!pg_query("insert into data_fa.tblsurat(no_surat,tgl_surat,fk_sbg,jenis)values('".$no_surat."','#".date("Y/m/d H:i:s")."#','".$lrow["fk_sbg"]."','Pemberitahuan BPKB')")) $l_success=0;					
if(!pg_query("insert into data_fa.tblsurat_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from data_fa.tblsurat where no_surat='".$no_surat."'")) $l_success=0;

$tgl_bayar=($lrow["tgl_bayar"]?date("d M Y",strtotime($lrow["tgl_bayar"])):"");
$ovd_bpkb=get_rec("tblsetting","ovd_lunas_bpkb","pk_id is not null");
$biaya_bpkb=get_rec("tblsetting","biaya_bpkb","pk_id is not null");
$max_biaya_bpkb=get_rec("tblsetting","max_biaya_bpkb","pk_id is not null");

$pdf = new Cezpdf('A4','');  

$pdf->ez['topMargin'] = $heigth-$y_table;
$pdf->ez['bottomMargin']=130;
$pdf->ez['rightMargin']=30;
//$all = $pdf->openObject();
//$pdf->saveState();

$fontsize= 10;
$pdf->selectFont('fonts/Times');

$x1=33;
$y=800;
$z=300;

$x2=120;
$x3=145;


$pdf->ezSetY($y+20);
$pdf->ezImage('../print/logo.jpeg','','180','','left','');
$y-=12;
$pdf->addText(200, $y, $fontsize+5,'');
$y-=10;
$pdf->addText($x1, $y, $fontsize-2,$lrow["alamat_cabang"]);
$y-=8;
$pdf->addText($x1, $y, $fontsize-2,$lrow["nm_cabang"]);
$y-=8;
$pdf->addText($x1, $y, $fontsize-2,'No. Telp : '.$lrow["no_telp"]);
$y-=8;

$pdf->line($x1,$y,555,$y);
//$y-=6;
$y-=22;
$pdf->addText($x1, $y, $fontsize,'Nomor : '.$no_surat);
$y-=12;
$pdf->addText($x1, $y, $fontsize,'Lamp : 1(satu) lembar');
$y-=12;
$pdf->addText($x1, $y, $fontsize,'Hal : Pemberitahuan terkait agunan BPKB');
$y-=18;

$pdf->addText($x1, $y, $fontsize,'Kepada.');
$y-=12;
$pdf->addText($x1, $y, $fontsize,'Yth. Bapak/Ibu/Sdr/i : '.$lrow["nm_customer"]);
$y-=12;
$pdf->addText($x1, $y, $fontsize,''.$lrow["alamat_tinggal"]);
$y-=12;
$pdf->addText($x1, $y, $fontsize,'Di Tempat.');
$y-=20;

$pdf->addText($x1, $y, $fontsize,'Dengan hormat,');

$pdf->y=$y;
$pdf->ezText('Sebelumnya kami ucapkan terima kasih yang sebesar-besarnya atas kepercayaan dan kesetiaan Bapak/Ibu/Sdr/i yang telah menjadi debitur '.$lrow["nm_perusahaan"].'.',$fontsize,array('justification'=>'full','left'=>23));
$y-=35;

$pdf->y=$y;
$pdf->ezText('Sehubungan adanya pembayaran angsuran terakhir yang Bapak/Ibu/Sdr/i lakukan pada tanggal '.$tgl_bayar.',atas pembiayaan 1(satu) unit kendaraan dengan data sbb:',$fontsize,array('justification'=>'full','left'=>23));
$y-=45;

$pdf->addText($x1, $y, $fontsize,'No Perjanjian ');
$pdf->addText($x1+80, $y, $fontsize,': '.$lrow["fk_sbg"]);
$y-=12;

$pdf->addText($x1, $y, $fontsize,'No Rangka ');
$pdf->addText($x1+80, $y, $fontsize,': '.$lrow["no_rangka"]);
$y-=12;

$pdf->addText($x1, $y, $fontsize,'No Mesin ');
$pdf->addText($x1+80, $y, $fontsize,': '.$lrow["no_mesin"]);
$y-=12;

$pdf->addText($x1, $y, $fontsize,'No Polisi ');
$pdf->addText($x1+80, $y, $fontsize,': '.$lrow["no_polisi"]);
$y-=12;

$pdf->y=$y;
$pdf->ezText('Dengan ini kami sampaikan apabila ada kewajiban lain berupa denda keterlambatan selama masa kredit, maka Bapak/Ibu/Sdr/i wajib melunasinya terlebih dahulu sebelum mengambil BPKB kendaraan yang menjadi agunan di kantor kami.',$fontsize,array('justification'=>'full','left'=>23));
$y-=40;

$pdf->y=$y;
$pdf->ezText('Untuk BPKB yang menjadi agunan tersebut harus diambil dalam jangka waktu '.$ovd_bpkb.' ('.convert_terbilang($ovd_bpkb).') hari terhitung dari tanggal surat pemberitahuan ini, apabila pengambilan melewati '.$ovd_bpkb.' ('.convert_terbilang($ovd_bpkb).') hari, maka akan dikenakan biaya penyimpanan agunan sebesar Rp. '.convert_money("",$biaya_bpkb).'  setiap bulannya yang terhitung sejak tanggal surat pemberitahuan ini',$fontsize,array('justification'=>'full','left'=>23));
$y-=55;

$pdf->addText($x1, $y, $fontsize,'Demikian pemberitahuan ini kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terima kasih');
$y-=22;

$pdf->addText($x1, $y, $fontsize,''.$lrow["nm_cabang"].', '.date('d M Y '));
$y-=12;
$pdf->addText($x1, $y, $fontsize,'Hormat Kami,');
$y-=12;

$y-=70;
$pdf->addText($x1, $y, $fontsize,'<b>'.$lrow["nm_perusahaan"].'</b>');
$y-=10;
//$pdf->selectFont('fonts/Times');

//UNTUK MEMBUAT HALAMAN BARU
$pdf->ezNewPage();
$pdf->selectFont('fonts/Times');

//Paragraph A
$y=$pdf->y-80;
$pdf->addText($x1+175,$y, 12,'<b>SYARAT PENGAMBILAN BPKB</b>');
$y-=40;
$pdf->addText($x1, $y, 12,'A. Debitur Langsung : ');
$pdf->y=$y;
$x1_pasal=37;
$x2_pasal=47;

$arr=array(
1=>'Piutang dan denda sudah LUNAS seluruhnya.',
2=>'Pengambilan BPKB WAJIB dilakukan oleh debitur langsung (perorangan) / Pengurus sesuai akta terakhir yang masih berlaku (perusahaan).',
3=>'Membawa E-KTP / SIM asli dan fotocopy E-KTP / SIM 1 (satu) lembar. Jika tidak ada E-KTP WAJIB membawa surat keterangan asli dan fotocopy dari Lurah (KADES) / DISDUKCAPIL setempat. ',
4=>'Untuk mobil angkutan, debitur WAJIB melampirkan surat pengantar pengambilan BPKB dari gabungan / koperasi yang ASLI disertai E-KTP / SIM / SUKET ASLI dan fotocopy 1 (satu) lembar.',
);
$pdf->y=$y;
$y=$pdf->y;
for($i=1;$i<=count($arr);$i++){
	$pdf->ezText($i.'.',$fontsize,array('justification'=>'full','left'=>$x1_pasal));
	$pdf->y=$y;
	$pdf->ezText($arr[$i],$fontsize,array('justification'=>'full','left'=>$x2_pasal));
	$y=$pdf->y;
}

//Paragraph B
$y=$pdf->y-25;
$pdf->addText($x1, $y, 12,'B.Dengan Menggunakan Surat Kuasa : ');
$pdf->y=$y;
$x1_pasal=35;
$x2_pasal=47;

$arr=array(
1=>'Piutang dan denda sudah LUNAS seluruhnya.',
2=>'Apabila Penerima Kuasa BERADA dalam satu Kartu Keluarga yang sama :
Menyerahkan surat kuasa ASLI yang ditandatangani diatas materai Rp.10.000 serta  membawa E-KTP / SIM  asli pemberi dan penerima kuasa beserta Kartu Keluarga.',
3=>'Apabila Penerima Kuasa TIDAK berada dalam satu Kartu Keluarga yang sama : 
		a. Menyerahkan Salinan Surat Kuasa Notariil ASLI dan penerima Kuasa WAJIB membawa E-KTP / SIM asli serta fotocopy dan pemberi kuasa cukup fotocopy E-KTP / SIM; atau
		b. Menyerahkan Surat Kuasa ASLI bermaterai Rp 10.000 maka surat tersebut WAJIB diketahui oleh Lurah / KADES setempat dan membawa E-KTP / SIM asli pemberi dan penerima kuasa. ',
);

$pdf->y=$y;
$y=$pdf->y;
for($i=1;$i<=count($arr);$i++){
	$pdf->ezText($i.'.',$fontsize,array('justification'=>'full','left'=>$x1_pasal));
	$pdf->y=$y;
	$pdf->ezText($arr[$i],$fontsize,array('justification'=>'full','left'=>$x2_pasal));
	$y=$pdf->y;
}

//Paragraph C
$y=$pdf->y-25;
$pdf->addText($x1, $y, 12,'C.Debitur Yang Telah Meninggal Dunia : ');
$y=$pdf->y-40;
$pdf->addText($x1+13,$y, $fontsize,'Pengambilan BPKB dapat diambil oleh ahli waris dengan persyaratan sebagai berikut :');
$pdf->y=$y;
$x1_pasal=35;
$x2_pasal=47;

$arr=array(
1=>'Piutang dan denda sudah LUNAS seluruhnya.',
2=>'Menyerahkan Akta Kematian / Surat Keterangan Kematian yang dilegalisir oleh Lurah (KADES) / Camat setempat. ',
3=>'Menyerahkan Surat Keterangan ahli waris dari Lurah (KADES) setempat / Notaris yang dilegalisir.  ',
4=>'Menyerahkan Surat Kuasa ahli waris ASLI bermaterai Rp. 10.000 yang diketahui Lurah (KADES) yang dikuasakan kepada salah satu ahli waris untuk pengambilan BPKB dengan membawa E-KTP / SIM asli penerima Kuasa, serta menyerahkan fotocopy E-KTP / SIM seluruh ahli waris masing masing 1 (satu) lembar yang namanya disebutkan dalam Surat Kuasa.',
);

$pdf->y=$y;
$y=$pdf->y;
for($i=1;$i<=count($arr);$i++){
	$pdf->ezText($i.'.',$fontsize,array('justification'=>'full','left'=>$x1_pasal));
	$pdf->y=$y;
	$pdf->ezText($arr[$i],$fontsize,array('justification'=>'full','left'=>$x2_pasal));
	$y=$pdf->y;
}

//Paragraph D
$y=$pdf->y-25;
$pdf->addText($x1, $y, 12,'D.Jangka Waktu Pengambilan BPKB : ');
$pdf->y=$y;
$pdf->ezText('Debitur WAJIB mengambil BPKB paling lambat '.$ovd_bpkb.' ('.convert_terbilang($ovd_bpkb).') hari setelah diterimanya Surat Pemberitahuan Pengambilan BPKB.',$fontsize,array('justification'=>'full','left'=>37));
$y-=45;


//Paragraph E
$y=$pdf->y-25;
$pdf->addText($x1, $y, 12,'E.Biaya Administrasi Penyimpanan BPKB : ');
$pdf->y=$y;
$x1_pasal=35;
$x2_pasal=47;

$arr=array(
1=>'Atas pengambilan BPKB sebagaimana dimaksud pada point D yang dilakukan setelah tanggal jatuh tempo pengambilan BPKB, dikenai biaya administrasi berupa penyimpanan BPKB Rp. '.convert_money("",$biaya_bpkb).' (seratus ribu rupiah) per bulan yang dihitung mulai dari berakhirnya batas waktu penyampaian surat pemberitahuan pengambilan BPKB sampai dengan tanggal pengambilan BPKB, dan bagian dari bulan dihitung penuh 1 (satu) bulan.',
2=>'Maksimal biaya administrasi penyimpanan BPKB yang dapat dikenakan kepada debitur yang tidak mengambil BPKB adalah senilai Rp. '.convert_money("",$max_biaya_bpkb).' (lima ratus ribu rupiah).',
);

$pdf->y=$y;
$y=$pdf->y;
for($i=1;$i<=count($arr);$i++){
	$pdf->ezText($i.'.',$fontsize,array('justification'=>'full','left'=>$x1_pasal));
	$pdf->y=$y;
	$pdf->ezText($arr[$i],$fontsize,array('justification'=>'full','left'=>$x2_pasal));
	$y=$pdf->y;
}


////Penutup Paragraph
//$y=$pdf->y-25;
//$pdf->y=$y;
//$pdf->ezText('Demikian Surat Edaran ini kami beritahukan untuk dapat diketahui dan dilaksanakan oleh Kantor Cabang dan KSKC mulai tanggal 01 Agustus 2019. Atas perhatian dan kerjasamanya kami ucapkan terima kasih.Debitur WAJIB mengambil BPKB paling lambat 60 (enam puluh) hari setelah diterimanya Surat Pemberitahuan Pengambilan BPKB.',$fontsize,array('justification'=>'full','left'=>23));
//$y-=45;
//
//$y=$pdf->y-40;
//$pdf->addText($x1, $y, $fontsize,''.$lrow["nm_cabang"].', '.date('d M Y '));
//$y-=12;
//$pdf->addText($x1, $y, $fontsize,'Hormat Kami,');
//$y-=12;
//
//
//$y-=70;
//$pdf->addText($x1, $y, $fontsize,'<u>ARIEF PRAWIRA</u>');
//$y=$pdf->y-15;
//$y-=132;
//$pdf->addText($x1, $y, $fontsize,'Direktur Utama');
//$y=$pdf->y-15;
//$y-=145;
//$pdf->addText($x1, $y, $fontsize,'<b>'.$lrow["nm_perusahaan"].'</b>');
//$y-=10;

//end content
$pdf->ezStream();   

?>