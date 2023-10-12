<?php
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/timestamp.inc.php';
require '../classes/ezpdf.class.php';
require '../requires/convert.inc.php';
require '../requires/numeric.inc.php';

$fk_partner_dealer = $_REQUEST['fk_partner_dealer'];
$no_surat = $_REQUEST['no_surat'];
$fk_cabang = $_REQUEST['fk_cabang'];
$query="
select * from (
	select date_part('day',('".date("m/d/Y")."'-tgl_cair))::numeric as jml_hari,nm_partner as nm_dealer,tbldealer.alamat as alamat_dealer,tblcustomer.npwp as npwp_cust,tblcabang.alamat as alamat_cabang,fk_sbg as no_sbg,* from data_gadai.tbltaksir_umum 
	left join tblcustomer on no_cif = fk_cif
	left join(
		select fk_sbg,tgl_cair from tblinventory
	)as tblinventory on fk_sbg=no_sbg_ar
	left join (
		select kd_partner,nm_partner,alamat,ovd_terima_bpkb from tblpartner
	)as tbldealer on fk_partner_dealer=kd_partner
	inner join (
		select warna,nm_tipe,no_fatg as no_fatg1,nm_merek,nm_jenis_barang from viewkendaraan
	)as tbldetail on no_fatg=no_fatg1
	left join tblcabang on kd_cabang=fk_cabang
	where tgl_cair is not null and tgl_terima_bpkb is null and fk_partner_dealer = '".$fk_partner_dealer."' 
	and fk_cabang='".$fk_cabang."'
	order by tgl_cair
 ) as tblmain
";	

$total_data=pg_num_rows(pg_query($query." where jml_hari>ovd_terima_bpkb "));			
$lrow=pg_fetch_array(pg_query($query));			
//showquery($query);

$ovd_terima_bpkb=$lrow["ovd_terima_bpkb"];
$bulan = round($ovd_terima_bpkb)/30; 
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

$x2=120;
$x3=145;


$query_cabang="
select * from tblcabang where kd_cabang='".$_SESSION["kd_cabang"]."'
";	
$lrow_cabang=pg_fetch_array(pg_query($query_cabang));			


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
$pdf->addText($x1, $y, $fontsize,'No. '.$no_surat);
$y-=18;
$pdf->addText($x1, $y, $fontsize,'Kepada Yth.');
$y-=12;
$pdf->addText($x1, $y, $fontsize,''.$lrow["nm_partner"]);
$y-=12;

$pdf->addText($x1, $y, $fontsize,''.$lrow["alamat_dealer"]);
$y-=20;
$pdf->addText($x1, $y, $fontsize,'Perihal');
$pdf->addText($x2, $y, $fontsize,': ');

$pdf->addText($x2+20, $y, $fontsize,'<b>Konfirmasi BPKB belum diterima</b>');
$pdf->addText($x2+20, $y-4, $fontsize,'<b>____________________________</b>');
$y-=20;
$pdf->addText($x1, $y, $fontsize,'Dengan hormat ,');

$y-=15;
$pdf->y=$y;
$pdf->ezText('Sehubungan dengan adanya Penjualan Unit-unit Kendaraan Bermotor dari <b>Dealer '.$lrow["nm_partner"].'</b> yang pembiayaannya melalui <b>'.$lrow["nm_perusahaan"].'</b> dalam bentuk kredit sehingga <b>BPKB</b> sesuai Surat Pernyataan Dealer bahwa <b>BPKB</b> akan diserahkan paling lambat <b>'.$bulan.' BULAN('.round($ovd_terima_bpkb).' hari)</b>.',$fontsize,array('justification'=>'full','left'=>23));

$y-=45;
$pdf->y=$y;
$pdf->ezText('Dan berdasarkan catatan kami Surat Pernyataan <b>BPKB</b> yang telah jatuh tempo, <b>BPKB</b>nya belum kami terima sampai dengan <b>'.tgl_indo(date('Y-m-d')).'</b> ada sebanyak <b>'.$total_data.' ('.convert_terbilang($total_data).' )</b> buku <b>BPKB</b> dengan rincian sebagai berikut.',$fontsize,array('justification'=>'full','left'=>23));

$y-=35;
$pdf->y=$y;
$pdf->ezText('Jika setelah surat ini diterima dan terdapat perbedaan atas jumlah <b>BPKB</b> yang belum diserahkan maka kami mohon dapat diberikan konfirmasi atas perbedaan tersebut selambat-lambatnya 1 (satu) minggu dari sejak tanggal diterimanya Surat ini. Dan diharapkan Pihak Dealer dapat menyampaikan kendala sebab <b>BPKB</b> belum selesai pengurusannya segera mungkin. ',$fontsize,array('justification'=>'full','left'=>23));

$y-=55;
$pdf->y=$y;
$pdf->ezText('Demikian surat konfirmasi ini disampaikan untuk dapat diketahui dan ditindaklanjuti. Dan untuk perhatian dan kerjasama baiknya kami ucapkan terimakasih. ',$fontsize,array('justification'=>'full','left'=>23));

$y-=50;
$pdf->addText($x1, $y, $fontsize,''.$lrow["nm_cabang"].', '.date('d ').getMonthName(date("m")).date(' Y'));
$y-=12;
$pdf->addText($x1, $y, $fontsize,'Hormat Kami,');
$y-=12;
$pdf->addText($x1, $y, $fontsize,'<b>'.$lrow["nm_perusahaan"].'</b>');

if($_SESSION["kd_cabang"]=='000'){
	$nm_jabatan1='Finance Dept. Head';	
	$nm_jabatan2='SPV. Finance';	
}else{
	if($lrow["jenis_cabang"]=='Cabang'){
		$nm_jabatan1='KACAB';	
	}else{
		$nm_jabatan1='KAPOS';
	}
	$nm_jabatan2='ADH';
}
/*if($lrow_cabang["jenis_cabang"]=='Pos'){
	$cc1='Kapos';
}else {
	$cc1='Kacab';
}*/

$nm_karyawan1=get_karyawan_by_jabatan($nm_jabatan1,$_SESSION["kd_cabang"]);
$nm_karyawan2=get_karyawan_by_jabatan($nm_jabatan2,$_SESSION["kd_cabang"]);

if(!$nm_karyawan1)$nm_karyawan1='_______________';
else $garis1=underline($nm_karyawan1);
if(!$nm_karyawan2)$nm_karyawan2='_______________';
else $garis2=underline($nm_karyawan2);

$y-=70;
$pdf->addText($x1, $y, $fontsize,'('.$nm_karyawan1.')');
$pdf->addText($x2+75, $y, $fontsize,'('.$nm_karyawan2.')');


$pdf->addText($x1, $y-2, $fontsize,$garis1);
$pdf->addText($x2+75, $y-2, $fontsize,$garis2);

$y-=12;

$pdf->addText($x1, $y, $fontsize,'<b>'.$nm_jabatan1.'</b>');	
$pdf->addText($x2+75, $y, $fontsize,'<b>'.$nm_jabatan2.'</b>');
$y-=40;
$pdf->addText($x1, $y, $fontsize,'CC:');
$y-=12;
$pdf->addText($x1, $y, $fontsize,'- Arsip');
$y-=12;
$pdf->addText($x1, $y, $fontsize,'- Kacab CMD');
$y-=12;	

if($_SESSION["kd_cabang"]=='000'){
	$pdf->addText($x1, $y, $fontsize,'- Finance Dept. Head');
	$y-=12;	

}else{
}

//$pdf->selectFont('fonts/Times');

$pdf->ezNewPage();
$y=800;
$y-=10;



$query1=$query." where jml_hari <= ".round($ovd_terima_bpkb)."";
$i=1;
//showquery($query1);
$lrs1=pg_query($query1);
while($lrow1 = pg_fetch_array($lrs1)){
	$data[$i]['no'] = $i;
	$data[$i]['nm_bpkb'] =  $lrow1["nm_bpkb"];
	$data[$i]['no_kontrak'] = $lrow1["no_sbg"];
	$data[$i]['no_rangka'] =  $lrow1["no_rangka"];
	$data[$i]['no_mesin'] =  $lrow1["no_mesin"];
	$data[$i]['no_polisi'] =  $lrow1["no_polisi"];
	$data[$i]['tgl_kontrak'] = ($lrow1["tgl_cair"]?date("d M Y",strtotime($lrow1["tgl_cair"])):"");
	$data[$i]['jml_hari'] =  $lrow1["jml_hari"];
	
	$i++;
}

$query2.=$query."where jml_hari > ".$ovd_terima_bpkb."";	
$judul['no'] = 'No.';
$judul['nm_bpkb'] = 'Nama BPKB';
$judul['no_kontrak'] = 'Nomor Konrak';
$judul['no_rangka'] = 'No Rangka';
$judul['no_mesin'] = 'No Mesin';
$judul['no_polisi'] = 'No Polisi';
$judul['tgl_kontrak'] = 'Tgl Kontrak';
$judul['jml_hari'] = 'Jlh Hari';

$lining['showHeadings'] = 1;
$lining['shaded'] = 0;
$lining['showLines'] =1 ;
$lining['xPos'] = 288;
$lining['fontSize'] = 8;

$lining['cols']['no']['justification'] = 'left';
$lining['cols']['nm_bpkb']['justification'] = 'left';
$lining['cols']['no_kontrak']['justification'] = 'left';
$lining['cols']['no_rangka']['justification'] = 'left';
$lining['cols']['no_mesin']['justification'] = 'left';
$lining['cols']['no_polisi']['justification'] = 'left';
$lining['cols']['tgl_kontrak']['justification'] = 'left';
$lining['cols']['jml_hari']['justification'] = 'left';

$lining['cols']['no']['heading_justification'] = 'left';
$lining['cols']['nm_bpkb']['heading_justification'] = 'left';
$lining['cols']['no_kontrak']['heading_justification'] = 'left';
$lining['cols']['no_rangka']['heading_justification'] = 'left';
$lining['cols']['no_mesin']['heading_justification'] = 'left';
$lining['cols']['no_polisi']['heading_justification'] = 'left';
$lining['cols']['tgl_kontrak']['heading_justification'] = 'left';
$lining['cols']['jml_hari']['heading_justification'] = 'left';



$size['no'] = '30';
$size['nm_bpkb'] = '100';
$size['no_kontrak'] = '80';
$size['no_rangka'] = '100';
$size['no_mesin'] = '80';
$size['no_polisi'] = '70';
$size['tgl_kontrak'] = '60';
$size['jml_hari'] = '30';

//print_r($data);
if($i>1){
$pdf->addText($x1-20, $y, $fontsize,'Daftar BPKB dengan jumlah hari KURANG dari '.round($ovd_terima_bpkb).' hari');
$pdf->ezSetY($y);
$pdf->ezTable($data,$judul,'',$lining,$size);
}


$y=$pdf->y;
$y=$y-20;


$query2=$query." where jml_hari > ".round($ovd_terima_bpkb)."";	
$data=array();
$i=1;
//showquery($query2);
$lrs2=pg_query($query2);
while($lrow2 = pg_fetch_array($lrs2)){
	$data[$i]['no'] = $i;
	$data[$i]['nm_bpkb'] =  $lrow2["nm_bpkb"];
	$data[$i]['no_kontrak'] = $lrow2["no_sbg"];
	$data[$i]['no_rangka'] =  $lrow2["no_rangka"];
	$data[$i]['no_mesin'] =  $lrow2["no_mesin"];
	$data[$i]['no_polisi'] =  $lrow2["no_polisi"];
	$data[$i]['tgl_kontrak'] = ($lrow2["tgl_cair"]?date("d M Y",strtotime($lrow2["tgl_cair"])):"");
	$data[$i]['jml_hari'] =  $lrow2["jml_hari"];
	
	$i++;
}

$judul['no'] = 'No.';
$judul['nm_bpkb'] = 'Nama BPKB';
$judul['no_kontrak'] = 'Nomor Konrak';
$judul['no_rangka'] = 'No Rangka';
$judul['no_mesin'] = 'No Mesin';
$judul['no_polisi'] = 'No Polisi';
$judul['tgl_kontrak'] = 'Tgl Kontrak';
$judul['jml_hari'] = 'JLH  Hari';

$lining['showHeadings'] = 1;
$lining['shaded'] = 0;
$lining['showLines'] =1 ;
$lining['xPos'] = 288;
$lining['fontSize'] = 8;

$lining['cols']['no']['justification'] = 'left';
$lining['cols']['nm_bpkb']['justification'] = 'left';
$lining['cols']['no_kontrak']['justification'] = 'left';
$lining['cols']['no_rangka']['justification'] = 'left';
$lining['cols']['no_mesin']['justification'] = 'left';
$lining['cols']['no_polisi']['justification'] = 'left';
$lining['cols']['tgl_kontrak']['justification'] = 'left';
$lining['cols']['jml_hari']['justification'] = 'left';

$lining['cols']['no']['heading_justification'] = 'left';
$lining['cols']['nm_bpkb']['heading_justification'] = 'left';
$lining['cols']['no_kontrak']['heading_justification'] = 'left';
$lining['cols']['no_rangka']['heading_justification'] = 'left';
$lining['cols']['no_mesin']['heading_justification'] = 'left';
$lining['cols']['no_polisi']['heading_justification'] = 'left';
$lining['cols']['tgl_kontrak']['heading_justification'] = 'left';
$lining['cols']['jml_hari']['heading_justification'] = 'left';

$size['no'] = '30';
$size['nm_bpkb'] = '100';
$size['no_kontrak'] = '80';
$size['no_rangka'] = '100';
$size['no_mesin'] = '80';
$size['no_polisi'] = '70';
$size['tgl_kontrak'] = '60';
$size['jml_hari'] = '30';

//print_r($data);
if($i>1){
	$pdf->addText($x1-20, $y, $fontsize,'Daftar BPKB dengan jumlah hari LEBIH dari '.round($ovd_terima_bpkb).' hari');
	$pdf->ezSetY($y);
	$pdf->ezTable($data,$judul,'',$lining,$size);
}
//$pdf->restoreState();
//$pdf->closeObject();
//$pdf->addObject($all,'all');
	
//end content
$pdf->ezStream();
  


function underline($nama){
	$count=strlen($nama);
	for($i=1;$i<=$count;$i++){
		$data.='_';
	}
	$data.='___';
	return $data;
}

function tgl_indo($tanggal){
	$bulan = array (
		1 =>   'Januari',
		'Februari',
		'Maret',
		'April',
		'Mei',
		'Juni',
		'Juli',
		'Agustus',
		'September',
		'Oktober',
		'November',
		'Desember'
	);
	$pecahkan = explode('-', $tanggal);
	
	// variabel pecahkan 0 = tanggal
	// variabel pecahkan 1 = bulan
	// variabel pecahkan 2 = tahun
 
	return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}
?>