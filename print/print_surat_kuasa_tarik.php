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

$fk_sbg = $_REQUEST['fk_sbg'];
$template = $_REQUEST['flag'];
$tgl = convert_date_english($_REQUEST['tgl']);
//echo $tgl;
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
where no_sbg='".$fk_sbg."'
";
$l_res=pg_query($query);	
$lrow=pg_fetch_array($l_res);
//showquery($query);
$tgl_cair_indo=date("d",strtotime($lrow['tgl_cair'])).' '.getMonthName(date("m",strtotime($lrow['tgl_cair'])),2).' '.date("Y",strtotime($lrow['tgl_cair']));
$tgl_pengajuan=date("d",strtotime($lrow['tgl_pengajuan'])).' '.getMonthName(date("m",strtotime($lrow['tgl_pengajuan'])),2).' '.date("Y",strtotime($lrow['tgl_pengajuan']));

$fk_cabang=$lrow["fk_cabang"];
$kacab=kacab($lrow["fk_cabang"],$lrow["tgl_cair"]);
$lrow["nm_kacab"]=$kacab["nm_depan"];
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
$tgl_pengajuan=date("d",strtotime($lrow['tgl_pengajuan'])).' '.getMonthName(date("m",strtotime($lrow['tgl_pengajuan'])),2).' '.date("Y",strtotime($lrow['tgl_pengajuan']));

$pdf = new Cezpdf('A4');  
$pdf->setLineStyle(1);

$pdf->ez['topMargin'] = (150);
$pdf->ez['bottomMargin']=5;
$pdf->ez['leftMargin']=60;
$pdf->ez['rightMargin']=65;
/*$all = $pdf->openObject();
$pdf->saveState();
*/
//Header
$fontsize=9;
$y=800;
$x1 = 69;
$x2 = 130;

$x3 = 308;
$x4 = 378;
$x_right=67;

$pdf->selectFont('fonts/Times-Roman');

if($template=='t'){
if($lrow["nm_bpkb"]==$lrow["nm_customer"]){
	$y-=40;
	$pdf->addText($x2+20, $y, $fontsize,$lrow["nm_customer"]);
	$y-=18;
	$pdf->addText($x2+20, $y, $fontsize,$lrow["jabatan"]);
	$y-=18;
	$pdf->addText($x2+20, $y, $fontsize,$lrow["alamat_ktp"]);
	$y-=18;
	$pdf->addText($x2+20, $y, $fontsize,$lrow["no_id"]);
}else{
	if($lrow["jenis_customer"]=='0'){
		$y-=40;
		$pdf->addText($x2+20, $y, $fontsize,$lrow["nm_bpkb"]);
		$y-=18;
		$pdf->addText($x2+20, $y, $fontsize,' ');
		$y-=18;
		$pdf->addText($x2+20, $y, $fontsize,$lrow["alamat_bpkb"]);
		$y-=18;
		$pdf->addText($x2+20, $y, $fontsize,$lrow["no_ktp_bpkb"]);
	}elseif($lrow["jenis_customer"]=='1'){
		$nm_pihak_lain=$lrow["nm_pemilik"];
		$y-=40;
		$pdf->addText($x2+20, $y, $fontsize,$lrow["nm_customer"]);
		$y-=18;
		$pdf->addText($x2+20, $y, $fontsize,$lrow["jabatan"]);
		$y-=18;
		$pdf->addText($x2+20, $y, $fontsize,$lrow["alamat_tinggal"]);
		$y-=18;
		$pdf->addText($x2+20, $y, $fontsize,$lrow["no_id"]);
	}
	
}
$y-=42;
$pdf->addText($x4+77, $y, $fontsize,$fk_sbg);
$y-=11;
$pdf->addText($x2+22, $y, $fontsize,$tgl_pengajuan);

$y-=386;
$pdf->addText($x3-105, $y, $fontsize,$lrow['nm_cabang']);
$pdf->addText($x3+20, $y, $fontsize,$tgl_pengajuan);

$y-=109;
$pdf->addText($x3-150, $y, $fontsize,$nm_pihak1);
if(substr($lrow["nm_bpkb"],0,6)==substr($lrow["nm_customer"],0,6)){
$pdf->addText($x3+40, $y, $fontsize,$lrow["nm_customer"]);
}else{
	if($lrow["jenis_customer"]=='0'){
		$pdf->addText($x3+40, $y, $fontsize,$lrow["nm_bpkb"]);
	}elseif($lrow["jenis_customer"]=='1'){
		$pdf->addText($x3+40, $y, $fontsize,$lrow["nm_pemilik"]);
	}
}

}else
{

$lrs_ho=pg_fetch_array(pg_query("select * from tblcabang where kd_cabang='".cabang_ho."'"));


$pdf->addText($x1+180,$y, $fontsize+5,'<b>SURAT KUASA<b>');
$pdf->addText($x1+180,$y, $fontsize,'<b>______________________<b>');

$y-=30;
$pdf->addText($x1,$y, $fontsize,'Saya yang bertanda tangan dibawah ini:');

/*$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');
*/

$pdf->ezSetY($y-15);

//$tgl_sistem='2021-02-09';
$i=0;
if($lrow["nm_bpkb"]==$lrow["nm_customer"]){
	$data[$i]['data1'] = 'Nama ';	
	$data[$i]['data2'] =  ': '.strtoupper($lrow['nm_customer']);	
	$i++;
	
	$data[$i]['data1'] = 'Jabatan ';	
	$data[$i]['data2'] =  ': '.strtoupper($lrow['jabatan']);	
	$i++;
	
	$data[$i]['data1'] = 'Alamat ';	
	$data[$i]['data2'] =  ': '.strtoupper($lrow['alamat_ktp']);	
	$i++;
	
	$data[$i]['data1'] = 'No KTP ';	
	$data[$i]['data2'] =  ': '.$lrow["no_id"];	
	$i++;
}else{
	if($lrow["jenis_customer"]=='0'){
		$data[$i]['data1'] = 'Nama ';	
		$data[$i]['data2'] =  ': '.strtoupper($lrow['nm_bpkb']);	
		$i++;
		
		$data[$i]['data1'] = 'Jabatan ';	
		$data[$i]['data2'] =  ': ';	
		$i++;
		
		$data[$i]['data1'] = 'Alamat ';	
		$data[$i]['data2'] =  ': '.strtoupper($lrow['alamat_bpkb']);	
		$i++;
		
		$data[$i]['data1'] = 'No KTP ';	
		$data[$i]['data2'] =  ': '.strtoupper($lrow['no_ktp_bpkb']);	
		$i++;
	}elseif($lrow["jenis_customer"]=='1'){
		$data[$i]['data1'] = 'Nama ';	
		$data[$i]['data2'] =  ': '.strtoupper($lrow['nm_pemilik']);	
		$i++;
		
		$data[$i]['data1'] = 'Jabatan ';	
		$data[$i]['data2'] =  ': '.strtoupper($lrow['jabatan']);	
		$i++;
		
		$data[$i]['data1'] = 'Alamat ';	
		$data[$i]['data2'] =  ': '.strtoupper($lrow['alamat_tinggal']);	
		$i++;
		
		$data[$i]['data1'] = 'No KTP ';	
		$data[$i]['data2'] =  ': '.$lrow["no_id"];
	}
}

$judul['data1'] = '';
$judul['data2'] = '';

$lining['showHeadings'] = 0;
$lining['shaded'] = 0;
$lining['showLines'] = 0;
$lining['xPos'] = 300;
$lining['fontSize'] = 9;
$lining['rowGap'] = 1.5;
$lining['colGap'] = 1.5;

$lining['cols']['data1']['justification'] = 'left';
$lining['cols']['data2']['justification'] = 'left';
$size['data1'] = '65';
$size['data2'] = '370';

//$pdf->line(10,240,585,240);
$pdf->ezTable($data,$judul,'',$lining,$size);

$y=$pdf->y-10;
$pdf->y=$y;
$pdf->ezText('Dalam hal ini bertindak untuk dan atas nama diri sendiri yang selanjutnya disebut sebagai PEMBERI KUASA.',$fontsize,array('justification'=>'full','left'=>11));
$y-=20;
$pdf->y=$y;

$pdf->ezText('Berkenaan dengan perjanjian pembiayaan Multiguna Dengan Pembayaran Secara Angsuran Nomor '.$fk_sbg.' tanggal : '.$tgl_pengajuan.', dengan ini saya selaku PEMBERI KUASA Barang yang dibiayai, menyatakan dengan ini menunju dan memberikan Kuasa penuh kepada '.$lrow['nm_perusahaan'].' selaku Pemberi Fasilitas Pembiayaan , untuk :',$fontsize,array('justification'=>'full','left'=>11));
$y-=45;
$pdf->y=$y;

$pdf->ezText('Melakukan eksekusi/menerima BARANG yang merupakan objek jaminan Pembiayaan/ Objek Jaminan fiducia sesuai dengan perjanjian Pembiayaan Multiguna Dengan Pembayaran Secara Angsuran bila ternyata menurut pendapat kreditur bahwa saya telah wanprestasi/cedera janji/lalai dalam menjalankan kewajiban dan/atau mentaati syarat dan ketentuan sebagai mana diataur dalam perjanjian dimaksud, dari pihak manapun dan dilokasi manapun juga tanpa terkecuali.',$fontsize,array('justification'=>'full','left'=>30));
$pdf->addText($x1,$y-12, $fontsize,'-');
$y-=44;
$pdf->y=$y;

$pdf->ezText('Memasuki tempat tinggal atas nama Pemberi Kuasa ataupun tempat dimana BARANG tersebut berada.',$fontsize,array('justification'=>'full','left'=>30));
$pdf->addText($x1,$y-12, $fontsize,'-');
$y-=15;
$pdf->y=$y;

$pdf->ezText('Menjual sendiri BARANG yang dibiayai kepada calon pembeli dengan harga yang dianggap baik oleh Penerima Kuasa atau mengalihkan hak atas BARANG kepada pihak manapun juga termasuk kepada si Penerima Kuasa, menghadap dimana perlu, memberi keterangan-keterangan, membuat dan suruh membuat serta menandatangani surat-surat untuk keperluan balik nama, surat pemblokiran STNK dan BPKB tanda terima pembayaran (kwitansi) serta klaim Asuransi Kendaraan dan sebagainya (khusus untuk BARANG yang dibiayai adalah kendaraan).',$fontsize,array('justification'=>'full','left'=>30));
$pdf->addText($x1,$y-12, $fontsize,'-');
$y-=55;
$pdf->y=$y;

$pdf->ezText('Mewakili Pemberi Kuasa di depan Pengadilan, pejabat instansi pemerintah yang berwenang maupun pihak-pihak lainnya untuk hal-hal yang berhubungan dengan kepemilikan, pengambil-alihan maupun penjualan BARANG.',$fontsize,array('justification'=>'full','left'=>30));
$pdf->addText($x1,$y-12, $fontsize,'-');
$y-=30;
$pdf->y=$y;


$pdf->ezText('Kuasa ini diberikan dengan Hak Substitusi dan merupakan bagian yang tidak terpisahkan dari perjanjian Pembiayaan Multiguna Dengan Pembayaran Secara Angsuran.',$fontsize,array('justification'=>'full','left'=>11));
$y-=70;
$pdf->ezText('Kuasa ini tidak dapat dibatalkan atau menjadi batal oleh karena apapun, serta tidak berakhir oleh sebab-sebab yang diatur dalam pasal 1813, 1814 dan 1816 kitab Undang-Undang Hukum Perdata.',$fontsize,array('justification'=>'full','left'=>11));
$y-=60;
$pdf->ezText('Pemberi Kuasa menyetujui bahwa dalam hal Penerimaan Kuasa menjalankan haknya yang timbul berdasarkan kuasa ini maka pemberi kuasa membebaskan Penerima Kuasa dan/atau PIHAK KETIGA yang ditunjuk oleh Penerima Kuasa dari segala tuntutan hokum baik secara perdata maupun pidana.',$fontsize,array('justification'=>'full','left'=>11));

$y+=50;
$pdf->y=$y;
$pdf->ezText('Demikian surat kuasa ini dibuat untuk dipergunakan sebagaimana mestinya, dan dalam keadaan sadar, sehat jasmani dan rohani serta tidak mendapat paksaan dari pihak manapun juga.',$fontsize,array('justification'=>'full','left'=>11));
$y-=70;


$pdf->addText($x1+185,$y, $fontsize,$lrow['nm_cabang'].','.$tgl_pengajuan);
$y-=30;
$pdf->addText($x1+45,$y, $fontsize,'PENERIMA KUASA,');
$pdf->addText($x1+360,$y, $fontsize,'PEMBERI KUASA,');

$y-=70;

$pdf->addText($x1+45,$y, $fontsize,'('.$nm_pihak1.')');
if($lrow["nm_bpkb"]==$lrow["nm_customer"]){
$pdf->addText($x1+360,$y, $fontsize,'('.$lrow["nm_customer"].')');
}else{
	if($lrow["jenis_customer"]=='0'){
		$pdf->addText($x1+360,$y, $fontsize,'('.$lrow["nm_bpkb"].')');
	}elseif($lrow["jenis_customer"]=='1'){
		$pdf->addText($x1+360,$y, $fontsize,'('.$lrow["nm_pemilik"].')');
	}
}

//$pdf->addText($x1,$y, $fontsize,'_________________');
//$pdf->addText($x1+300,$y, $fontsize,'_________________');

$y-=90;
//$pdf->ezImage('../print/logo_2.png','30','60','','left','');
$pdf->addPngFromFile('../print/logo_2.png', 35, 5,30,25);
//$pdf->addText(100, 25, 6,'"PERJANJIAN INI TELAH DISESUAIKAN DENGAN KETENTUAN PERATURAN PERUNDANG-UNDANGAN TERMASUK KETENTUAN PERATURAN OTORITAS JASA KEUANGAN"');
$pdf->addText(75, 15, 9,'PT. Capella Multidana Berizin dan Diawasi oleh Otoritas Jasa Keuangan');
}

$pdf->ezStream();

?>