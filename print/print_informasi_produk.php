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
$template = $_REQUEST['flag'];
	
$query="
select *,nm_partner as nm_dealer,tblpartner.alamat as alamat_dealer,tblcustomer.npwp as npwp_cust,tblcabang.no_telp as telp_cabang from data_gadai.tblproduk_cicilan 
left join data_gadai.tbltaksir_umum on fk_fatg=no_fatg
left join tblcabang on kd_cabang=fk_cabang
left join tblcustomer on fk_cif=no_cif
left join tblpartner on fk_partner_dealer=kd_partner
left join (
	select fk_barang,fk_fatg as fk_fatg_detail,nm_jenis_barang from data_gadai.tbltaksir_umum_detail
	left join(select no_fatg,nm_jenis_barang from viewkendaraan) as tbl on no_fatg=fk_fatg
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
$agunan_motor=get_rec("tblproduk","nominal_denda_keterlambatan","kd_produk='20'");
$agunan_mobil=get_rec("tblproduk","nominal_denda_keterlambatan","kd_produk='40'");
$kacab=kacab($lrow["fk_cabang"],$lrow["tgl_pengajuan"]);
$lrow["nm_kacab"]=$kacab["nm_depan"];
$lrow["alamat_kacab"]=$kacab["alamat"];

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

$pdf = new Cezpdf('A4');  
$pdf->setLineStyle(1);

$fontsize= 9;

if($template=='t'){
$pdf->selectFont('fonts/Times-Roman');
$fontsize= 9;	
$x3=68;
$x3_isi=$x3+172;
$x4=368;
$x4_isi=$x4+68;

	
$y=$pdf->y;
$y-=121;
$y-=10;

$pdf->addText($x3_isi-5, $y, $fontsize,' '.$lrow["jumlah_unit"]);
$pdf->addText($x4_isi, $y, $fontsize,' '.$lrow["warna"]);
$y-=10;
$pdf->addText($x3_isi-5, $y, $fontsize,' '.$lrow["nm_merek"]);
$pdf->addText($x4_isi, $y, $fontsize,' '.$lrow["no_bpkb"]);
$y-=10;
$pdf->addText($x3_isi-5, $y, $fontsize,' '.$lrow["nm_tipe"]);
$pdf->addText($x4_isi, $y, $fontsize,' '.$lrow["no_mesin"]);
$y-=10;
$pdf->addText($x3_isi-5, $y, $fontsize,' '.$lrow["tahun"]);
$pdf->addText($x4_isi, $y, $fontsize,' '.$lrow["no_rangka"]);
$y-=10;
$pdf->addText($x3_isi-5, $y, $fontsize,' '.$lrow["status_barang"]);
$pdf->addText($x4_isi, $y, $fontsize,' '.$lrow["no_polisi"]);

$y-=13;
$y-=9;

$x3_isi+=5;

$pdf->addText($x3_isi, $y, $fontsize,' '.convert_money("",$lrow["total_nilai_pinjaman"]));
$y-=10;
$persen_dp=($lrow["nilai_dp"]/$lrow["total_nilai_pinjaman"]*100);
$pdf->addText($x3_isi, $y, $fontsize,' '.convert_money("",$lrow["nilai_dp"]));
$y-=10;
$pdf->addText($x3_isi, $y, $fontsize,' '.convert_money("",$lrow["biaya_admin"]));
$y-=10;
$pdf->addText($x3_isi, $y, $fontsize,' '.convert_money("",$lrow["biaya_notaris"]));
$y-=10;
$pdf->addText($x3_isi, $y, $fontsize,' '.convert_money("",$lrow["biaya_provisi"]));
$y-=10;

$pdf->addText($x3_isi, $y, $fontsize,' '.jenis_asuransi($lrow));
$y-=10;
$pdf->addText($x3_isi, $y, $fontsize,' '.convert_money("",$lrow["nilai_asuransi"]));
$y-=10;
$pdf->addText($x3_isi, $y, $fontsize,' '.convert_money("",$lrow["nilai_asr_jiwa"]+$lrow["tjh_3"]+$lrow["pa_penumpang"]+$lrow["pa_supir"]+$lrow["biaya_polis"]));

$y-=21;

$pdf->addText($x3_isi, $y, $fontsize,' '.convert_money("",$lrow["pokok_hutang"]));
$y-=10;

$pdf->addText($x3_isi, $y, $fontsize,' '.convert_money("",$lrow["biaya_penyimpanan"]));
$y-=10;
$pdf->addText($x3_isi, $y, $fontsize,' '.convert_money("",$lrow["total_hutang"]));

$y-=21;
$pdf->addText($x3_isi, $y, $fontsize,' '.$lrow["lama_pinjaman"]);
$y-=11;
$pdf->addText($x3_isi-10, $y, $fontsize,' '.$tgl_pengajuan.'        '.$tgl_jt_indo);
$y-=10;
$pdf->addText($x3_isi, $y, $fontsize,' '.convert_money("",$lrow["angsuran_bulan"]));

$pdf->ezNewPage();

$pdf->selectFont('fonts/Times-Roman');
$y_pasal=840;
$pdf->ezSetY($y_pasal);
$fontsize= 9;	
$x1 = 74;
$x3=68;
$x3_isi=$x3+172;
$x4=368;
$x4_isi=$x4+68;

	
$y=$pdf->y;
$y-=500;
$pdf->addText($x1+270,$y, $fontsize,$lrow['nm_cabang']);
$pdf->addText($x1+370,$y, $fontsize,$tgl_pengajuan);
//$today_indo

$y-=62;
$pdf->addText($x1+326,$y, $fontsize-1, $lrow["nm_customer"] );

}

else{
$y_table=810;
$heigth=$pdf->ez['pageHeight'];
$width=$pdf->ez['pageWidth'];
$pdf->ez['topMargin'] = $heigth-$y_table;
$pdf->ez['bottomMargin']=10;
$pdf->ez['leftMargin']=20;
$pdf->ez['rightMargin']=40;

$all = $pdf->openObject();
$pdf->saveState();
//$pdf->ezImage('../print/OJK_Logo.png','300','60','','center','1');
//$pdf->addPngFromFile('../print/OJK_Logo.png', 30, 15,60,25);
$pdf->addPngFromFile('../print/logo_2.png', 35, 5,30,25);
$pdf->selectFont('fonts/Times-Roman');
//$pdf->addText(100, 25, 6,'"PERJANJIAN INI TELAH DISESUAIKAN DENGAN KETENTUAN PERATURAN PERUNDANG-UNDANGAN TERMASUK KETENTUAN PERATURAN OTORITAS JASA KEUANGAN"');
$pdf->addText(75, 15, 9,'PT. Capella Multidana Berizin dan Diawasi oleh Otoritas Jasa Keuangan');

$pdf->selectFont('fonts/Times-Roman');

$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');



//Header
$pdf->y=830;
//echo $pdf->y;
$pdf->selectFont('fonts/Times-Bold');
$pdf->ezText('RINGKASAN INFORMASI PRODUK/LAYANAN PEMBIAYAAN SYARIAH MENGGUNAKAN AKAD MURABAHAH',9,array('justification' =>'center'));
$pdf->ezText($lrow["nm_perusahaan"].' UNIT USAHA SYARIAH',9,array('justification' =>'center'));$y=$pdf->y;
$y-=10;
$pdf->line(10,$y,585,$y);
$y-=3;
$pdf->line(10,$y,585,$y);
$y-=5;

$pdf->selectFont('fonts/Times-Roman');
$pdf->y=$y;
$pdf->ezText('Berdasarkan permohonan fasilitas pembiyaan murabahah dengan pembayaran secara angsuran yang bapak/ibu ajukan, maka dengan ini kami beritahukan ringkasan produk/layanan sebagai informasi awal sebelum penandatangan perjanjian pembiayaan ini.',$fontsize,array('justification'=>'full','left'=>5));

$x1=25;
$x2=$x1+20;

$y=$pdf->y;
$y-=20;
$pdf->selectFont('fonts/Times-Bold');
$pdf->addText($x1, $y, $fontsize,'1.');
$pdf->addText($x2, $y, $fontsize,'Infomasi Produk');
$pdf->selectFont('fonts/Times-Roman');
$y-=12;
$pdf->addText($x2, $y, $fontsize,'Berikut ini merupakan rincian mengenai biaya produk/layanan yang diberikan kepada konsumen :');

$y+=12;
$pdf->y=$y;


$x3=53;
$x3_isi=$x3+160;
$x4=350;
$x4_isi=$x4+80;

$x_rinci=$x1+20;

$y-=25;
$pdf->addText($x_rinci, $y, $fontsize,'a. Objek Pembiayaan');
$y-=12;
$pdf->addText($x3, $y, $fontsize,'-	 Jumlah Unit');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["jumlah_unit"]);
$pdf->addText($x4, $y, $fontsize,'-  Warna');
$pdf->addText($x4_isi, $y, $fontsize,': '.$lrow["warna"]);
$y-=12;
$pdf->addText($x3, $y, $fontsize,'-	 Merk');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["nm_merek"]);
$pdf->addText($x4, $y, $fontsize,'-  Nomor BPKB');
$pdf->addText($x4_isi, $y, $fontsize,': '.$lrow["no_bpkb"]);
$y-=12;
$pdf->addText($x3, $y, $fontsize,'-	 Type');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["nm_tipe"]);
$pdf->addText($x4, $y, $fontsize,'-  Nomor Mesin');
$pdf->addText($x4_isi, $y, $fontsize,': '.$lrow["no_mesin"]);
$y-=12;
$pdf->addText($x3, $y, $fontsize,'-	 Tahun');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["tahun"]);
$pdf->addText($x4, $y, $fontsize,'-  Nomor Rangka');
$pdf->addText($x4_isi, $y, $fontsize,': '.$lrow["no_rangka"]);
$y-=12;
$pdf->addText($x3, $y, $fontsize,'-	 Kondisi');
$pdf->addText($x3_isi, $y, $fontsize,': '.$lrow["status_barang"]);
$pdf->addText($x4, $y, $fontsize,'-  Nomor Polisi');
$pdf->addText($x4_isi, $y, $fontsize,': '.$lrow["no_polisi"]);

$y-=15;
$pdf->addText($x_rinci, $y, $fontsize,'b. Informasi Pembiayaan');

$y-=12;

$pdf->addText($x3, $y, $fontsize,'- Harga Beli Murabahah');
$pdf->addText($x3_isi, $y, $fontsize,': '.convert_money("Rp",$lrow["total_nilai_pinjaman"]));
$y-=12;
$persen_dp=($lrow["nilai_dp"]/$lrow["total_nilai_pinjaman"]*100);
$pdf->addText($x3, $y, $fontsize,'- Uang Muka '.convert_money("",$persen_dp,2).'%');
$pdf->addText($x3_isi, $y, $fontsize,': '.convert_money("Rp",$lrow["nilai_dp"]));
$y-=12;
$pdf->addText($x3, $y, $fontsize,'- Biaya Administrasi & Survey');
$pdf->addText($x3_isi, $y, $fontsize,': '.convert_money("Rp",$lrow["biaya_admin"]));
$y-=12;
$pdf->addText($x3, $y, $fontsize,'- Biaya Notaris & Jaminan Fidusia');
$pdf->addText($x3_isi, $y, $fontsize,': '.convert_money("Rp",$lrow["biaya_notaris"]));
$y-=12;
$pdf->addText($x3, $y, $fontsize,'- Biaya Provisi');
$pdf->addText($x3_isi, $y, $fontsize,': '.convert_money("Rp",$lrow["biaya_provisi"]));
$y-=12;
$pdf->addText($x3, $y, $fontsize,'- Jenis Asuransi Syariah');

$pdf->addText($x3_isi, $y, $fontsize,': '.jenis_asuransi($lrow));
$y-=12;
$pdf->addText($x3, $y, $fontsize,'- Biaya Asuransi Kendaraan');
$pdf->addText($x3_isi, $y, $fontsize,': '.convert_money("Rp",$lrow["nilai_asuransi"]));
$y-=12;
$pdf->addText($x3, $y, $fontsize,'- Biaya Asuransi Lain');
$pdf->addText($x3_isi, $y, $fontsize,': '.convert_money("Rp",$lrow["nilai_asr_jiwa"]+$lrow["tjh_3"]+$lrow["pa_penumpang"]+$lrow["pa_supir"]+$lrow["biaya_polis"]));

$y-=15;
$pdf->addText($x_rinci, $y, $fontsize,'c. Rincian Fasilitas Pembiayaan');

$y-=12;
$pdf->addText($x3, $y, $fontsize,'- Harga Jual Murabahah');
$pdf->addText($x3_isi, $y, $fontsize,': '.convert_money("Rp",$lrow["pokok_hutang"]));
$y-=12;

$pdf->addText($x3, $y, $fontsize,'- Margin');
$pdf->addText($x3_isi, $y, $fontsize,': '.convert_money("Rp",$lrow["biaya_penyimpanan"]));
$y-=12;
$pdf->addText($x3, $y, $fontsize,'- Total Kewajiban');
$pdf->addText($x3_isi, $y, $fontsize,': '.convert_money("Rp",$lrow["total_hutang"]));

$y-=15;
$pdf->addText($x_rinci, $y, $fontsize,'d. Jangka Waktu(Tenor) Pengembalian Kewajiban Utang');
$y-=12;
$pdf->addText($x3, $y, $fontsize,'- Jangka Waktu(Tenor)');
$pdf->addText($x3_isi+40, $y, $fontsize,': '.$lrow["lama_pinjaman"]);
$y-=12;
$pdf->addText($x3, $y, $fontsize,'- Jadwal Pembayaran Angsuran');
$pdf->addText($x3_isi+40, $y, $fontsize,': '.$tgl_pengajuan.'-'.$tgl_jt_indo);
$y-=12;
$pdf->addText($x3, $y, $fontsize,'- Besarnya Angsuran Perbulan');
$pdf->addText($x3_isi+40, $y, $fontsize,': '.convert_money("Rp",$lrow["angsuran_bulan"]));

$y-=20;

$pdf->selectFont('fonts/Times-Bold');
$pdf->addText($x1, $y, $fontsize,'2.');
$pdf->addText($x2, $y, $fontsize,'Tempat Pembayaran Angsuran');
$pdf->selectFont('fonts/Times-Roman');
$y-=12;
$pdf->addText($x2, $y, $fontsize,'Alternatif tempat pembayaran angsuran bagi Bapak/Ibu/Sdr/Sdri :');

$y+=12;
$pdf->y=$y;


$x3=53;
$x3_isi=$x3+160;
$x4=350;
$x4_isi=$x4+80;

$x_rinci=$x1+20;

$y-=25;
$pdf->addText($x_rinci, $y, $fontsize,'a. Kantor Cabang PT Capella Multidana');

$y-=12;
$pdf->addText($x_rinci, $y, $fontsize,'b. Virtual Account Bank yang telah bekerjasama dengan PT. Capella Multidana');

$y-=12;
$pdf->addText($x_rinci, $y, $fontsize,'c. Website cmd.co.id');

$y-=12;
$pdf->addText($x_rinci, $y, $fontsize,'d. Kantor Posindonesia');

$y-=12;
$pdf->addText($x_rinci, $y, $fontsize,'e. On.line Payment (Indomaret dan Alfa Grup)');

$y-=12;
$pdf->addText($x_rinci, $y, $fontsize,'f. Tenaga Penagih PT. Capella Multidana');

$y-=20;
$pdf->selectFont('fonts/Times-Bold');
$pdf->addText($x1, $y, $fontsize,'3.');
$pdf->addText($x2, $y, $fontsize,'Batas Akhir Pembayaran (Jatuh Tempo)');
$pdf->selectFont('fonts/Times-Roman');

$arr=array(
1=>'Tanggal jatuh tempo (sesuai deugan Akad) adalah tanggal yang ditetapkan dan disepakai sebagai batas pembayaran terakhir angsuran setiap bulannya.',
2=>'Apabila jatuh tempo pembayaran angsuran jatuh pada hari libur, maka pembayaran dilakukan pada hari kerja sebelumnya.',
);
$pdf->y=$y;
$y-=12;
for($i=1;$i<=count($arr);$i++){
	$pdf->ezText($arr[$i],$fontsize,array('justification'=>'full','left'=>$x_rinci-20));
	$y-=12;
}

$y-=15;
$pdf->selectFont('fonts/Times-Bold');
$pdf->addText($x1, $y, $fontsize,'4.');
$pdf->addText($x2, $y, $fontsize,'Sanksi (Ta’zir) dan GantiRugi (Ta’widh)');
$pdf->selectFont('fonts/Times-Roman');

$arr=array(
1=>"Sanksi (Ta’zir) adalah kewajiban yang muncul akibat keterlambatan pembayaran angsuran oleh Penerima Fasilitas Pembiayaan sebesar ".convert_money("Rp",($agunan_motor)).",- (".convert_terbilang($agunan_motor)." Rupiah ) untuk Agunan Sepeda Motor dan ".convert_money("Rp",($agunan_mobil)).",- (".convert_terbilang($agunan_mobil)." Rupiah ) untuk Agunan Mobil, Tanah dan Bangunan per keterlambatan setiap pembayaran angsuran yang digunakan untuk kegiatan sosial. Ganti Rugi (Ta’widh) adalah ganti rugi atas kerugian yang dialami PT. Capella Multidana yang muncul dikemudian hari akibat keterlambatan pembayaran angsuran oleh Penerima Fasilitas Pembiayaan.",
);
$pdf->y=$y;
$y-=12;
for($i=1;$i<=count($arr);$i++){
	$pdf->ezText($arr[$i],$fontsize,array('justification'=>'full','left'=>$x_rinci-20));
	$y-=30;
}

$y-=25;
$pdf->selectFont('fonts/Times-Bold');
$pdf->addText($x1, $y, $fontsize,'5.');
$pdf->addText($x2, $y, $fontsize,'Risiko :');
$pdf->selectFont('fonts/Times-Roman');

$x1_pasal=26;
$x2_pasal=40;

$arr_denda=explode(".",$lrow["rate_denda_ganti_rugi"]);
//print_r($arr_denda);
$arr=array(
1=>'Penerima Fasilitas Pembiayaan tidak akan memiliki kendaraan apabila tidak melunasi pembiayaan kendaraan.',
2=>'Risiko hanya akan terjadi apabila bebitur lalai dalam melaksanakan kewajiban/wanprestasi/cidera janji.',
3=>'Keterlamabatan pembayaran angsuran tidak dibenarkanl ebihdari 7 (tujuh) hari dari tanggal jatuh tempo dan jika hal ini tidak dipenuhi, maka Penerima Fasilitas Pembiayaan dengan sukarela menyerahkan Barang kepada PT. Capella Multidana atau PT. Capella Multidana berhak melakukan penanganan/penggudangan terhadap objek pembiayaan.',
4=>'Penerima Fasilitas Pembiayaan  yang menunggak pembayaran angsuran akan tercatat pada Sistem Layanan Informasi Keuangan (SLIK) Otoritas Jasa Keuangan (OJK) sehingga akan mempengaruhi reputasi Penerima Fasilitas Pembiayaan  sendiri.',
5=>'Akad ini juga dapat berakhir secara sepihak dan Penerima Fasilitas Pembiayaan wajib melunasi secara seketika dan sekaligus seluruh utang kepada PT. Capella Multidana apabila terjadi keaadaan sebagai berikut :',

);
$pdf->y=$y;
$y=$pdf->y;
$alphabet = range('a', 'z');
for($i=1;$i<=5;$i++){
	$pdf->y=$y-2;
	$pdf->ezText($alphabet[$i-1].'.',$fontsize,array('justification'=>'full','left'=>$x1_pasal));
	$pdf->y=$y-2;
	$pdf->ezText($arr[$i],$fontsize,array('justification'=>'full','left'=>$x2_pasal));
	$y=$pdf->y;
}

$arr=array(
1=>'Penerima Fasilitas Pembiayaan Wanprestasi/cedera janji/lalai dalam melaksanakan kewajibannya dan dalam hal ini Penerima Fasilitas Pembiayaan tidak mengindahkan semua Surat Peringatan yang sudah diberikan oleh Pemberi Fasilitas Pembiayaan kepadanya.',
2=>'Penerima Fasilitas Pembiayaan menunjukan sikap tidak kooperatif yang dapat mengancam keselamatan fisik, psykis serta tindakan yang dapat menimbulkan kerugian material maupun immaterial bagi Penerima Fasilitas Pembiayaan atau wakilnya selama proses pelaksanaan AKAD.',
3=>'Harta kekayaan Penerima Fasilitas Pembiayaan  disita baik sebagian maupun seluruhnya atau menjadi objek suatu perkara yang menurut pendapat Penerima Fasilitas Pembiayaan sendiri dapat mempengaruhi kemampuan Penerima Fasilitas Pembiayaan  dalam melaksanakan seluruh kewajibannya.',
4=>'Penerima Fasilitas Pembiayaan  meninggal dunia atau sakit berkelanjutan atau cacat tetap yang menurut pendapat Pemberi fasiilitas pembiayaan bahwa Penerima Fasilitas Pembiayaan tidak akan mampu untuk menyelesaikan kewajiban-kewajibannya dalam AKAD , kecuali ada penerima dan/atau penerus hak/ahli warisnya yang dengan persetujuan tertulis dari Pemberi Fasilitas Pembiayaan ,sanggup dan bersedia untuk memenuhi seluruh kewajiban Penerima Fasilitas Pembiayaan berdasarkan ketentuan dalam AKAD dan mengikuti ketentuan pengalihan kewajiban yang ditetapkan Pemberi Fasilitas Pembiayaan.',
5=>'Penerima Fasilitas Pembiayaan berada dibawah pengampuan (under curatele gesteld) atau karena sebab apapun Penerima Fasilitas Pembiayaan tidak cakap atau tidak berhak atau tidak berwenang lagi untuk melakukan pengurusan, atau pemilikan atas dan terhadap kekayaannya, baik sebagian atau seluruhnya.',
6=>'Penerima Fasilitas Pembiayaan mengajukan permohonan kepailitan atau penundaan pembayaran kewajiban hutangnya (surseance van betaling) dengan adanya putusan pengadilan niaga atau Penerima Fasilitas Pembiayaan dinyatakan pailit atau suatu permohonan kepailitan diajukan terhadap Penerima Fasilitas Pembiayaan atas permintaan pihak manapun.',
7=>'BARANG dijual, dialihkan , dijaminkan ( digadaikan) , dipinjamkan , disewakan/rental atau  menyerahkan penguasaan atau penggunaan atas BARANG tersebut kepada Pihak Ketiga dengan jalan apapun juga tanpa mendapat persetujuan secara tertulis terlebih dahulu dari Pemberi Fasilitas Pembiayaan . Pelanggaran atas ketentuan ini dapat dikenakan Pasal 372 dan Pasal 378 Kitab Undang-Undang Hukum Pidana jo. Pasal 36 Undang-Undang No. 42 Tahun 1999 tentang Fidusia.',
8=>'BARANG digunakan untuk hal-hal selain yang diperkenankan oleh Prinsip Syariah.',
9=>'Penerima Fasilitas Pembiayaan dan/atau Objek Jaminan terlibat dalam suatu perkara pidana atau perdata dan karenanya menurut pendapat PIHAK PERTAMA sendiri Penerima Fasilitas Pembiayaan tidak akan mampu untuk menyelesaikan kewajiban-kewajibanya dalam AKAD.',
10=>'Penerima Fasilitas Pembiayaan lalai atau wanprestasi atas fasilitas pembiayaan lainnya yang diberikan oleh Pemberi Fasilitas Pembiayaan.',
11=>'Penerima Fasilitas Pembiayaan terbukti memberikan keterangan, data, informasi, surat pernyataan atau dokumen-dokumen yang tidak benar, palsu dan tidak sah dalam rangka atau selama Pemberian Fasilitas pembiayaan ini.',
);
$pdf->y=$y;
$y=$pdf->y;
for($i=1;$i<=count($arr);$i++){
	$pdf->y=$y-2;
	$pdf->ezText('-',$fontsize,array('justification'=>'full','left'=>$x2_pasal));
	$pdf->y=$y-2;
	$pdf->ezText($arr[$i],$fontsize,array('justification'=>'full','left'=>$x2_pasal+20));
	$y=$pdf->y;
	if($y<100){
		$pdf->ezNewPage();		
		$pdf->y=$y+735;
		$y=$pdf->y;
	}	
	
}
$y-=25;
$pdf->selectFont('fonts/Times-Bold');
$pdf->addText($x1, $y, $fontsize,'6.');
$pdf->addText($x2, $y, $fontsize,'Syarat dan Ketentuan');
$pdf->selectFont('fonts/Times-Roman');

$arr=array(
1=>'Penerima Fasilitas Pembiayaan menjamin bahwa Barang yang diberikan fasilitas pembiayaan kepadanya hanya dipergunakan/dimamfaatkan untuk kegiatan yang memenuhi Prinsip Syariah.',
2=>'Debitur harus menyerahkan data identitas pribadi seperti :
- Foto Copy KTP
- Foto Copy Kartu Keluarga
- Foto Copy Surat Nikah
- Foto Copy rekening Tabungan
- Slip gaji dan keterangan penghasilan
- Dan dokumen maupun suratl ainnya yang dianggap perlu oleh Pemberi Fasilitas Pembiayaan.',
3=>'Penerima Fasilitas Pembiayaan telah mengisi form aplikasi dan melampirkan semua dokumen/data yang dibutuhkan.',
4=>'Penerima Fasilitas Pembiayaan telah melakukan pembayaran uang muka, biaya survey, biaya provisi, biaya asuransi dan biaya pengurusan sertifikat jaminan fidusia kepada Penerima Fasilitas Pembiayaan.',
5=>'Penerima Fasilitas Pembiayaan telah menandatangani semua dokumen-dokumen perjanjian pembiayaan ini.',
6=>'Angsuran wajib dibayar sebelum tanggal jatuh tempo pembayaran angsuran dan apabila jatuh tempo pembayaran angsuran jatuh pada harilibur, maka pembayaran dilakukan pada hari kerja sebelumnya.',
7=>'Penerima Fasilitas Pembiayaan memberikan persetujuan dan hak kepada Pemberi Fasilitas Pembiayaan untuk melakukan kegiatan survey, meliputi pengambilan gambar rumah atau kantor atau tempat usaha Penerima Fasilitas Pembiayaan  dan meminta keterangan atau informasi atau referensi mengenai Penerima Fasilitas Pembiayaan dari pihak terkait disekitar rumah atau kantor atau tempat usaha Penerima Fasilitas Pembiayaan maupun sumber manapun yang dipandang perlu dan dengan tata cara yang ditentukan oleh Pemberi Fasilitas Pembiayaan sesuai dengan ketentuan peraturan perundangan yang berlaku untuk keperluan analisa pemberian Fasilitas Pembiayaan Murabahah maupun untuk pembaharuan data Penerima Fasilitas Pembiayaan.',
8=>'Penerima Fasilitas Pembiayaan  membayar Harga Jual Murabahah ditambah Margin beserta kewajiban lain yang disepakati dalam AKAD Murabahah seperti tetapi tidak terbatas produk asuransi yang selanjutnya secara keseluruhan akan disebut  TOTAL KEWAJIBAN kepada Pemberi Fasilitas Pembiayaan dalam jangka waktu tertentu yang disepakati oleh para pihak berdasarkan AKAD Murabahah . Seluruh biaya yang timbul dalam pelaksanaan AKAD Murabahah meliputi biaya administrasi, pajak, bea materai, biaya survey, biaya perubahan atas AKAD,  biaya pengecekan Buku Pemilik Kendaraan Bermotor (BPKB) / Surat Tanda Nomor Kendaraan  (STNK), biaya fotokopi BPKB (atas permintaan Pemberi Fasilitas Pembiayaan) maupun biaya-biaya lain terkait pemberian Fasilitas Pembiayaan Murabahah serta biaya penghapusan jaminan (jika ada) yang ditetapkan Pemberi Fasilitas Pembiayaan dikemudian hari. Segala biaya sebagaimana tersebut diatas merupakan beban yang harus dibayar seluruhnya oleh Penerima Fasilitas Pembiayaan sampai AKAD murabahah berakhir.',
9=>'Penerima Fasilitas Pembiayaan  menyerahkan semua dokumen asli yang berkaitan dengan kepemilikan kendaraan termasuk Buku Pemilikan Kendaraan Bermotor (BPKB) kepada Pemberi Fasilitas Pembiayaan dan baru diserahkan kepada Penerima Fasilitas Pembiayaan apabila Penerima Fasilitas Pembiayaan  telah melaksanakan seluruh kewajibannya (pelunasan seluruhnya).',
10=>'Bilamana timbul perbedaan pendapat atau perselisihan atau sengketa diantara Pemberi Fasilitas Pembiayaan dan Penerima Fasilitas Pembiayaan sehubungan dengan Akad Muranahah ini atau pelaksanaannya, maka hal tersebut akan diselesaikan musyawarah untuk mencapai kata mufakat oleh Pemberi Fasilitas Pembiayaan dan Penerima Fasilitas Pembiayaan.Apabila penyelesaian perselisihan melalui musyawarah dan mufakat tidak tercapai, maka Pemberi Fasilitas Pembiayaan dan Penerima Fasilitas Pembiayaan sepakat untuk memilih penyelesaian sengketa di Lembaga Alternatif Penyelesaian Sengketa (LAPS) atau di kantor panitera Pengadilan Agama ditempat Pemberi Fasilitas Pembiayaan berada.',
);
$pdf->y=$y;
$y=$pdf->y;
$alphabet = range('a', 'z');
for($i=1;$i<=10;$i++){
	$pdf->y=$y-2;
	$pdf->ezText($alphabet[$i-1].'.',$fontsize,array('justification'=>'full','left'=>$x1_pasal));
	$pdf->y=$y-2;
	$pdf->ezText($arr[$i],$fontsize,array('justification'=>'full','left'=>$x2_pasal));
	$y=$pdf->y;
	if($y<100){
		$pdf->ezSetY($y_pasal);
		$y=$y_pasal+820;
	}

}
$y-=25;
$pdf->selectFont('fonts/Times-Bold');
$pdf->addText($x1, $y, $fontsize,'7.');
$pdf->addText($x2, $y, $fontsize,'Asuransi');
$pdf->selectFont('fonts/Times-Roman');

$arr=array(
1=>'Untuk menjaga dan melindungi Barang/Objekpembiayaan terhadap resiko  kerusakan, kehilangan, kebakaran ataupun bahaya-bahaya yang lainnya, maka Penerima Fasilitas Pembiayaan wajib mengansuransikan Barang tersebut pada perusahaan asuransi.',
2=>'Dalam hal terjadi kerusakan dan/atau kerugian karena suatu peristiwa yang dijamin oleh Polis Asuransi, maka Penerima Fasilitas Pembiayaan berhak menerima pembayaran ganti rugi yang kemudian disebut dengan Harga Pertanggungan. Adapun harga pertanggungan untuk Barang/ObjekPembiayaan berupa Mobil dan Sepeda motor nilainya sebesar yang tertera dalam polis asuransi.',
3=>'Setelah diterimanya pembayaran klaim asuransi, maka Pemberi Fasilitas Pembiayaan berhak secara langsung mengkompensasikan pembayaran klaim asuransi untuk seluruh utang dan kewajiban Penerima fasilitas Pembiayaan berdasarkan AKAD pembiayaan Murabahah.',
4=>'Bilamana terjadi kerusakan, kehilangan atau risiko lainnya pada Barang tersebut, maka Penerima Fasilitas Pembiayaan  harus segera melaporkannya kepada Pemberi Faslitas Pembiayaan atau perusahaan asuransi dalam tenggang waktu 3 x 24 jam setelah kejadian.',
);
$pdf->y=$y;
$y=$pdf->y;
$alphabet = range('a', 'z');
for($i=1;$i<=4;$i++){
	$pdf->y=$y-2;
	$pdf->ezText($alphabet[$i-1].'.',$fontsize,array('justification'=>'full','left'=>$x1_pasal));
	$pdf->y=$y-2;
	$pdf->ezText($arr[$i],$fontsize,array('justification'=>'full','left'=>$x2_pasal));
	$y=$pdf->y;
	if($y<100){
		$pdf->ezNewPage();		
		$pdf->ezSetY($y_pasal);
		$y=$y_pasal;
	}

}

$y-=25;
$pdf->selectFont('fonts/Times-Bold');
$pdf->addText($x1, $y, $fontsize,'8.');
$pdf->addText($x2, $y, $fontsize,'Pengambilan BPKB');
$pdf->selectFont('fonts/Times-Roman');

$arr=array(
1=>'BPKB diambil dikantor PT. Capella Multidana (tempat pengajuan pembelian) setelah seluruh kewajiban Penerima Fasilitas Pembiayaan lunas.',
2=>'Syarat pengambilan BPKB :
- Membawa Kartu Indentitas (KTP) asli
- Kwitansi pembayaran angsuran terakhir
- Membawa Surat Pengantar Dari Direksi (khususAngkutan).',
3=>'Jadwal pengambilan BPKB selambat-lambatnya 14 ( empat belas ) hari setelah angsuran terakhir dibayarkan.',
4=>'Apabila  BPKB belum diambil oleh Penerima Fasilitas Pembiayan setelah 14 ( empat belas) hari , maka Penerima Fasilitas dikenakan biaya penyimpanan BPKB yang besarnya ditetapkan Pemberi Fasilitas Pembiayaan.',

);
$pdf->y=$y;
$y=$pdf->y;
$alphabet = range('a', 'z');
for($i=1;$i<=4;$i++){
	$pdf->y=$y-2;
	$pdf->ezText($alphabet[$i-1].'.',$fontsize,array('justification'=>'full','left'=>$x1_pasal));
	$pdf->y=$y-2;
	$pdf->ezText($arr[$i],$fontsize,array('justification'=>'full','left'=>$x2_pasal));
	$y=$pdf->y;
}

$y-=25;
$pdf->selectFont('fonts/Times-Bold');
$pdf->addText($x1, $y, $fontsize,'9.');
$pdf->addText($x2, $y, $fontsize,'Layanan dan Pengaduan Konsumen');
$pdf->selectFont('fonts/Times-Roman');

$arr=array(
1=>'Untuk keterangan lebih lanjut mengenai fasilitas pembiayaa, debitur/calon debitur dapat langsung mengunjungi kantor '.$lrow["nm_perusahaan"].' terdekat atau Customer Service di nomor telepon '.$lrow["telp_cabang"].'.',
2=>'Ringkasan Informasi Produk / Layanan Pembiayaan Syariah dengan menggunakan Akad Murabahah ini merupakan pengantar yang bertujuan memberikan informasi produk dan layanan kepada Calon Penerima Fasilitas Pembiayaan sebelum menandatangani Perjanjian Pembiayaan dengan Akad Murabahah sebagaimana di amanatkan peraturan perundang-undangan yang berlaku, Ringkasan Informasi Produk / Layanan Pembiayaan Syariah dengan menggunakan Akad Murabahah ini merupakan satu kesatuan yang tidak terpisahkan dengan dengan Perjanjian Pembiayaan dengan menggunakan Akad Murabahah.',
3=>'Setelah membaca dan mendapatkan penjelasan secukupnya, maka Calon Penerima Fasilitas Pembiayaan dengan ini menyatakan telah mengerti serta memahami isi Ringkasan Informasi Produk / Layanan Pembiayaan Syariah dengan menggunakan Akad Murabahah ini yang kemudian menandatanganinya pada tempat dan tanggal seperti yang tertera di bawah ini.',

);
$pdf->y=$y;
$y=$pdf->y;
for($i=1;$i<=count($arr);$i++){
	$pdf->y=$y-2;
	$pdf->ezText($arr[$i],$fontsize,array('justification'=>'full','left'=>$x1_pasal));
	$y=$pdf->y;
}

$y-=80;
$pdf->addText($x1+295,$y, $fontsize,$lrow['nm_cabang'].', '.$tgl_pengajuan);
$y-=30;


$y-=55;
$pdf->addText($x1+295, $y, $fontsize,'('.$lrow["nm_customer"].')');
}

//end content
$pdf->ezStream();   

?>