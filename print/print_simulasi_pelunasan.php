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
left join data_gadai.tbltaksir_umum on no_fatg=fk_fatg
left join(
	select fk_fatg as fk_fatg1, nm_merek,nm_tipe,nilai_awal,diskon,nilai_taksir from data_gadai.tbltaksir_umum_detail
	left join tblbarang on fk_barang=kd_barang
	left join tbltipe on fk_tipe=kd_tipe
	left join tblmodel on fk_model=kd_model
	left join tblmerek on fk_merek=kd_merek
)as tblbarang on fk_fatg=fk_fatg1
left join(
	select fk_sbg as fk_sbg2,count(1)as byk_angs_sdh_jto,sum(bunga_jt)as bunga_sdh_jto from data_fa.tblangsuran
	where (tgl_jatuh_tempo <='".$tgl."' and tgl_bayar is null )and angsuran_ke>0
	group by fk_sbg
)as tblang on fk_sbg=fk_sbg2
left join(
	select fk_sbg as fk_sbg3,count(1)as byk_angs_sdh_byr,sum(bunga_jt)as bunga_sdh_byr from data_fa.tblangsuran
	where (tgl_bayar is not null)and angsuran_ke>0
	group by fk_sbg
)as tblang3 on fk_sbg=fk_sbg3

where fk_sbg = '".$fk_sbg."'";
$l_res=pg_query($query);	
$lrow=pg_fetch_array($l_res);
//showquery($query);
$byk_angs_sdh_jto=$lrow["byk_angs_sdh_jto"];
$bunga_sdh_jto=$lrow["bunga_sdh_jto"];
$byk_angs_sdh_byr=$lrow["byk_angs_sdh_byr"];
$bunga_sdh_byr=$lrow["bunga_sdh_byr"];

pelunasan_cicilan($fk_sbg,$tgl);


$pdf = new Cezpdf('F4');  
$pdf->setLineStyle(1);

$pdf->ez['topMargin'] = (150);
$pdf->ez['bottomMargin']=5;

/*$all = $pdf->openObject();
$pdf->saveState();
*/
//Header
$fontsize=10;
$start_y=910;
$x1 = 21;
$x2 = 100;

$x3 = 308;
$x4 = 378;
$x_right=67;


$pdf->addText($x1,$start_y, $fontsize,$lrow["nm_perusahaan"].'');
$pdf->addText($x1+194,$start_y, $fontsize,'PELUNASAN DIPERCEPAT');
$pdf->addText($x1+374,$start_y, $fontsize,'Tanggal : '.convert_date_indonesia($tgl));

$start_y-=10;

$pdf->addText($x1,$start_y, $fontsize,'No Kontrak');
$pdf->addText($x2,$start_y, $fontsize,': '.$fk_sbg.'');
$pdf->addText($x3,$start_y, $fontsize,'Merek/Tipe');
$pdf->addText($x4,$start_y, $fontsize,': '.$lrow["nm_merek"].'/'.$lrow["nm_tipe"]);

$pdf->ezSetY($start_y+10);
$start_y-=10;

$pdf->addText($x1,$start_y, $fontsize,'Nama');
$pdf->addText($x2,$start_y, $fontsize,': '.$lrow["nm_customer"].'');
$pdf->ezSetY($start_y+10);
$start_y-=10;

$pdf->addText($x1,$start_y, $fontsize,'No. Rangka');
$pdf->addText($x2,$start_y, $fontsize,': '.$lrow["no_rangka"].'');
$pdf->addText($x3,$start_y, $fontsize,'No. Mesin');
$pdf->addText($x4,$start_y, $fontsize,': '.$lrow["no_mesin"].'');

$start_y-=10;

$pdf->line($x1,$start_y,555,$start_y);
$start_y+=10;


/*$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');
*/
$pdf->ezSetY($start_y);

//$tgl_sistem='2021-02-09';
$byk_angs_blm_jto=$lrow['tenor']-$byk_angs_sdh_jto-$byk_angs_sdh_byr;
$i=0;
$total_angs_blm_jto=$lrow["angsuran_bulan"]*$byk_angs_blm_jto;
$data[$i]['data1'] = 'Sisa Angsuran yg blm jto :';	
$data[$i]['data2'] =  $byk_angs_blm_jto.' Bulan x';	
$data[$i]['nilai1'] = convert_money("",$lrow["angsuran_bulan"]).' =';	
$data[$i]['nilai2'] = '';
$data[$i]['nilai3'] = convert_money("",$total_angs_blm_jto).'';
$i++;

$data[$i]['data1'] = '';	
$data[$i]['data2'] = '';	
$data[$i]['nilai1'] ='';	
$data[$i]['nilai2'] = '';
$data[$i]['nilai3'] = '';
$i++;

$data[$i]['data1'] = 'Bunga selama pembiayaan  :';	
$data[$i]['data2'] =  $lrow['tenor'].' Bulan =';	
$data[$i]['nilai1'] = '';	
$data[$i]['nilai2'] = convert_money("",$lrow["total_bunga"]).'';
$data[$i]['nilai3'] = '';
$i++;

$data[$i]['data1'] = 'Bunga yang sudah berjalan: ';	
$data[$i]['data2'] =  $byk_angs_sdh_jto+$byk_angs_sdh_byr.' Bulan =';	
$data[$i]['nilai1'] = '';	
$data[$i]['nilai2'] = convert_money("",$bunga_sdh_jto+$bunga_sdh_byr).'';
$data[$i]['nilai3'] = '';
$i++;

$data[$i]['data1'] = '';	
$data[$i]['data2'] = '';	
$data[$i]['nilai1'] ='';	
$data[$i]['nilai2'] = '=========(-)';
$data[$i]['nilai3'] = '';
$i++;

$bunga_blm_jto=$lrow["total_bunga"]-$bunga_sdh_jto-$bunga_sdh_byr;;
$data[$i]['data1'] = 'Pendapatan bunga yang ditangguhkan:';	
$data[$i]['data2'] = '';	
$data[$i]['nilai1'] ='';	
$data[$i]['nilai2'] = '';
$data[$i]['nilai3'] = convert_money("",$bunga_blm_jto);
$i++;

$data[$i]['data1'] = '';	
$data[$i]['data2'] = '';	
$data[$i]['nilai1'] ='';	
$data[$i]['nilai2'] = '';
$data[$i]['nilai3'] = '=========(-)';
$i++;

$sisa_pokok=$total_angs_blm_jto-$bunga_blm_jto;
$data[$i]['data1'] = 'Sisa pokok per '.date("d M Y",strtotime($tgl));	
$data[$i]['data2'] = '';	
$data[$i]['nilai1'] ='';	
$data[$i]['nilai2'] = '';
$data[$i]['nilai3'] = convert_money("",$sisa_pokok);
$i++;

$data[$i]['data1'] = '';	
$data[$i]['data2'] = '';	
$data[$i]['nilai1'] ='';	
$data[$i]['nilai2'] = '';
$data[$i]['nilai3'] = '';
$i++;

$pdf->addText($x1+4,$start_y-135, 9,'Bunga dari '.date("d/m/Y",strtotime($tgl_jatuh_tempo_lalu)).' s/d '.convert_date_indonesia($tgl).' :('.$selisih_hari.'/360) x '.convert_money("",$sisa_pokok).' x '.$rate_flat.'%');

$data[$i]['data1'] = '';	
$data[$i]['data2'] = '';	
$data[$i]['nilai1'] ='';	
$data[$i]['nilai2'] = '';
$data[$i]['nilai3'] = convert_money("",$bunga_berjalan);
$i++;

$data[$i]['data1'] = '';	
$data[$i]['data2'] = '';	
$data[$i]['nilai1'] ='';	
$data[$i]['nilai2'] = '';
$data[$i]['nilai3'] = '';
$i++;

$data[$i]['data1'] = 'Administrasi Pelunasan:';	
$data[$i]['data2'] = $rate_pinalti.' % x';	
$data[$i]['nilai1'] = convert_money("",$sisa_pokok).' =';	
$data[$i]['nilai2'] = convert_money("",$pinalti);
$data[$i]['nilai3'] = '';
$i++;

$tunggakan_ang=$lrow["angsuran_bulan"]*$byk_angs_sdh_jto;
$data[$i]['data1'] = 'Tunggakan Angsuran:';	
$data[$i]['data2'] = $byk_angs_sdh_jto.' bulan x';	
$data[$i]['nilai1'] = convert_money("",$lrow["angsuran_bulan"]).' =';	
$data[$i]['nilai2'] = convert_money("",$tunggakan_ang);
$data[$i]['nilai3'] = '';
$i++;

$data[$i]['data1'] = 'Sisa Tawidh yang belum dibayar:';	
$data[$i]['data2'] = '';	
$data[$i]['nilai1'] = '';	
$data[$i]['nilai2'] = convert_money("",$nilai_bayar_denda);
$data[$i]['nilai3'] = '';
$i++;

$data[$i]['data1'] = '';	
$data[$i]['data2'] = '';	
$data[$i]['nilai1'] = '';	
$data[$i]['nilai2'] = '=========(+)';
$data[$i]['nilai3'] = '';
$i++;

$total_lain2=$pinalti+$tunggakan_ang+$nilai_bayar_denda;
$data[$i]['data1'] = 'Total adm, tunggakan, sisa denda:';	
$data[$i]['data2'] = '';	
$data[$i]['nilai1'] = '';	
$data[$i]['nilai2'] = '';
$data[$i]['nilai3'] = convert_money("",$total_lain2);
$i++;

$data[$i]['data1'] = '';	
$data[$i]['data2'] = '';	
$data[$i]['nilai1'] = '';	
$data[$i]['nilai2'] = '';
$data[$i]['nilai3'] = '=========(+)';
$i++;

$total_pelunasan=$sisa_pokok+$bunga_berjalan+$total_lain2;
$data[$i]['data1'] = 'Total Pelunasan Dipercepat:  ';	
$data[$i]['data2'] = '';	
$data[$i]['nilai1'] = '';	
$data[$i]['nilai2'] = '';
$data[$i]['nilai3'] = convert_money("",$total_pelunasan);
$i++;

$data[$i]['data1'] = 'yang masih harus dibayar';	
$data[$i]['data2'] = '';	
$data[$i]['nilai1'] = '';	
$data[$i]['nilai2'] = '';
$data[$i]['nilai3'] = '-------------';
$i++;

$data[$i]['data1'] = '';	
$data[$i]['data2'] = '';	
$data[$i]['nilai1'] = '';	
$data[$i]['nilai2'] = '';
$data[$i]['nilai3'] = '';
$i++;

$data[$i]['data1'] = '';	
$data[$i]['data2'] = '';	
$data[$i]['nilai1'] = '';	
$data[$i]['nilai2'] = '';
$data[$i]['nilai3'] = '';
$i++;

$data[$i]['data1'] = 'Dibuat oleh,';	
$data[$i]['data2'] = '';	
$data[$i]['nilai1'] = 'Diperiksa oleh,';	
$data[$i]['nilai2'] = '';
$data[$i]['nilai3'] = 'Disetujui oleh,';
$i++;

$data[$i]['data1'] = '';	
$data[$i]['data2'] = '';	
$data[$i]['nilai1'] = '';	
$data[$i]['nilai2'] = '';
$data[$i]['nilai3'] = '';
$i++;

$data[$i]['data1'] = '';	
$data[$i]['data2'] = '';	
$data[$i]['nilai1'] = '';	
$data[$i]['nilai2'] = '';
$data[$i]['nilai3'] = '';
$i++;

$data[$i]['data1'] = '';	
$data[$i]['data2'] = '';	
$data[$i]['nilai1'] = '';	
$data[$i]['nilai2'] = '';
$data[$i]['nilai3'] = '';
$i++;

$data[$i]['data1'] = '(             )';	
$data[$i]['data2'] = '';	
$data[$i]['nilai1'] = '(             )';	
$data[$i]['nilai2'] = '';
$data[$i]['nilai3'] = '(             )';
$i++;

$judul['data1'] = '';
$judul['data2'] = '';

$judul['nilai1'] = '';
$judul['nilai2'] = '';
$judul['nilai3'] = '';

$lining['showHeadings'] = 1;
$lining['shaded'] = 0;
$lining['showLines'] = 0;
$lining['xPos'] = 280;
$lining['fontSize'] = 9;
$lining['rowGap'] = 1.5;
$lining['colGap'] = 1.5;


$lining['cols']['nilai1']['justification'] = 'right';
$lining['cols']['nilai2']['justification'] = 'right';
$lining['cols']['nilai3']['justification'] = 'right';	
$lining['cols']['data2']['justification'] = 'right';
$size['data1'] = '200';
$size['data2'] = '60';

$size['nilai1'] = '85';
$size['nilai2'] = '85';
$size['nilai3'] = '85';
//$pdf->line(10,240,585,240);
$pdf->ezTable($data,$judul,'',$lining,$size);

$start_y-=390;

$pdf->addText($x1,$start_y, $fontsize,'User');
$pdf->addText($x2,$start_y, $fontsize,': '.$_SESSION["username"].'');

$pdf->ezStream();

?>