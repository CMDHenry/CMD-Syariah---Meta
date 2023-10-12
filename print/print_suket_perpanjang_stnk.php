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
where fk_sbg = '".$fk_sbg."'";
$l_res=pg_query($query);	
$lrow=pg_fetch_array($l_res);
//showquery($query);

$fk_cabang=$lrow["fk_cabang"];
$pdf = new Cezpdf('F4');  
$pdf->setLineStyle(1);

$pdf->ez['topMargin'] = (150);
$pdf->ez['bottomMargin']=5;
$pdf->ez['leftMargin']=75;
$pdf->ez['rightMargin']=80;
/*$all = $pdf->openObject();
$pdf->saveState();
*/
//Header
$fontsize=12;
$y=890;
$x1 = 86;
$x2 = 130;

$x3 = 308;
$x4 = 378;
$x_right=67;

$pdf->selectFont('fonts/Times-Roman');

$lrs_ho=pg_fetch_array(pg_query("select * from tblcabang where kd_cabang='".cabang_ho."'"));

$pdf->addText($x1+150,$y, $fontsize,'<b>SURAT KETERANGAN<b>');
$pdf->addText($x1+150,$y, $fontsize,'<b>______________________<b>');
$y-=20;

$query_serial="select nextserial_cabang('STNK':: text,'".$fk_cabang."')";
$lrow_serial=pg_fetch_array(pg_query($query_serial));
$no_surat=$lrow_serial["nextserial_cabang"];	

if(!pg_query("insert into data_fa.tblsurat(no_surat,tgl_surat,fk_partner_dealer,jenis)values('".$no_surat."','#".date("Y/m/d H:i:s")."#','".$fk_partner_dealer."','Suket Perpanjang STNK')")) $l_success=0;					
if(!pg_query("insert into data_fa.tblsurat_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from data_fa.tblsurat where no_surat='".$no_surat."'")) $l_success=0;

$pdf->addText($x1+150,$y, $fontsize,'<b>No. <b>'.$no_surat);
$y-=30;

$pdf->y=$y;
$pdf->ezText('Yang bertanda tangan dibawah ini menerangkan bahwa Buku Pemilik Kendaraan Bermotor (BPKB) asli dengan data :',$fontsize,array('justification'=>'full','left'=>11));
$y-=30;

/*$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');
*/

$pdf->ezSetY($y-5);

//$tgl_sistem='2021-02-09';
$i=0;

$data[$i]['data1'] = 'Nama ';	
$data[$i]['data2'] =  ': '.strtoupper($lrs_ho['nm_perusahaan']);	
$i++;

$data[$i]['data1'] = 'Alamat ';	
$data[$i]['data2'] =  ': '.strtoupper($lrs_ho['alamat']);	
$i++;

$data[$i]['data1'] = 'No Polisi ';	
$data[$i]['data2'] =  ': '.$lrow["no_polisi"];	
$i++;

$data[$i]['data1'] = 'No Rangka ';	
$data[$i]['data2'] =  ': '.$lrow["no_rangka"];	
$i++;

$data[$i]['data1'] = 'No Mesin';	
$data[$i]['data2'] =  ': '.$lrow["no_mesin"];	
$i++;

$data[$i]['data1'] = 'Merk/Type';	
$data[$i]['data2'] =  ': '.$lrow["nm_merek"].'/'.$lrow["nm_tipe"];	
$i++;

$data[$i]['data1'] = 'Tahun ';	
$data[$i]['data2'] =  ': '.$lrow["tahun"];	
$i++;

$judul['data1'] = '';
$judul['data2'] = '';

$lining['showHeadings'] = 0;
$lining['shaded'] = 0;
$lining['showLines'] = 0;
$lining['xPos'] = 300;
$lining['fontSize'] = 11;
$lining['rowGap'] = 1.5;
$lining['colGap'] = 1.5;

$lining['cols']['data1']['justification'] = 'left';
$lining['cols']['data2']['justification'] = 'left';
$size['data1'] = '85';
$size['data2'] = '275';

//$pdf->line(10,240,585,240);
$pdf->ezTable($data,$judul,'',$lining,$size);

$y=$pdf->y-30;

$pdf->addText($x1,$y, $fontsize,'Memang benar dalam status agunan');

$pdf->selectFont('fonts/Times-Bold');
$pdf->addText($x1+177,$y, $fontsize,$lrs_ho['nm_perusahaan'].' - '.$lrow['nm_cabang']);
$pdf->selectFont('fonts/Times-Roman');

$pdf->y=$y-10;

$pdf->ezText('Demikian Surat keterangan ini kami perbuat dengan sebenarnya agar dapat dipergunakan seperlunya.',$fontsize,array('justification'=>'full','left'=>11));

$pdf->y=$y-80;

$pdf->ezText('NB : Surat keterangan ini berlaku 14 hari mulai dari tanggal surat keterangan ini.',$fontsize,array('justification'=>'full','left'=>11));

$y=$pdf->y-60;
$pdf->addText(350,$y, $fontsize,$lrow['nm_cabang'].','.date("d").' '.getMonthName(date("m"),2).' '.date("Y"));
$y-=20;
$pdf->addText(350,$y, $fontsize,'Hormat Kami,');
$y-=80;
$pdf->selectFont('fonts/Times-Bold');
$pdf->addText(350,$y, $fontsize,'Collection Dept. Head');

$pdf->ezStream();

?>