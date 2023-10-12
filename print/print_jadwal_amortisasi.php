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


$l_res=pg_query("
select * from tblinventory  
left join tblcustomer on fk_cif = no_cif 
left join (
	select * from data_gadai.tblproduk_cicilan
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
left join tblwarna on fk_warna=kd_warna
left join tblpartner on kd_partner=fk_partner_dealer
left join (
	select kd_produk, rate_denda_ganti_rugi*30 as denda from tblproduk
)as produk on tblinventory.fk_produk=kd_produk

where fk_sbg = '".$fk_sbg."'");	

$lrow=pg_fetch_array($l_res);
$rate_eff=(flat_eff($lrow["rate_flat"],$lrow["lama_pinjaman"],$lrow["addm_addb"]));


$pdf = new Cezpdf('F4');  
$pdf->setLineStyle(1);

$pdf->ez['topMargin'] = (150);
$pdf->ez['bottomMargin']=5;

$all = $pdf->openObject();
$pdf->saveState();

//Header
$fontsize=10;
$start_y=910;
$x1 = 21;
$x2 = 120;

$x3 = 338;
$x4 = 418;
$x_right=67;


$pdf->addText($x1,$start_y, $fontsize,$lrow["nm_perusahaan"].'');
$pdf->addText($x1+274,$start_y, $fontsize,'JADWAL AMORTISASI PEMBIAYAAN KONSUMEN');
$start_y-=10;

$pdf->addText($x1,$start_y, $fontsize,'No Kontrak');
$pdf->addText($x2,$start_y, $fontsize,': '.$fk_sbg.'');
$pdf->addText($x3,$start_y, $fontsize,'Nilai Aktiva');
$pdf->addText($x4,$start_y, $fontsize,': ');
$pdf->ezSetY($start_y+10);
$pdf->ezText(convert_money("",$lrow["total_nilai_pinjaman"]),$fontsize,array('justification'=>'right','right'=>$x_right));
$start_y-=10;

$pdf->addText($x1,$start_y, $fontsize,'Konsumen');
$pdf->addText($x2,$start_y, $fontsize,': '.$lrow["nm_customer"].'');
$pdf->addText($x3,$start_y, $fontsize,'Adm & Lainnya');
$pdf->addText($x4,$start_y, $fontsize,': ');
$pdf->ezSetY($start_y+10);
$pdf->ezText(convert_money("",$lrow["nilai_asuransi"]+$lrow["biaya_admin"]),$fontsize,array('justification'=>'right','right'=>$x_right));
$start_y-=10;

$pdf->addText($x1,$start_y, $fontsize,'Alamat');
$pdf->addText($x2,$start_y, $fontsize,': ' .$lrow["alamat_tinggal"].'');
$start_y-=10;

$pdf->addText($x1,$start_y, $fontsize,'Merek');
$pdf->addText($x2,$start_y, $fontsize,': '.$lrow["nm_merek"].'');
$pdf->addText($x3,$start_y, $fontsize,'Uang Muka');
$pdf->addText($x4,$start_y, $fontsize,': ');
$pdf->ezSetY($start_y+10);
$pdf->ezText(convert_money("",$lrow["nilai_dp"]),$fontsize,array('justification'=>'right','right'=>$x_right));
$start_y-=10;

$pdf->addText($x1,$start_y, $fontsize,'Tipe');
$pdf->addText($x2,$start_y, $fontsize,': '.$lrow["nm_tipe"].'');
$pdf->addText($x3,$start_y, $fontsize,'SB Flat /thn');
$pdf->addText($x4,$start_y, $fontsize,': ');
$pdf->ezSetY($start_y+10);
$pdf->ezText(substr($lrow["rate_flat"],0,10),$fontsize,array('justification'=>'right','right'=>$x_right));
$start_y-=10;

$pdf->addText($x1,$start_y, $fontsize,'No. Rangka');
$pdf->addText($x2,$start_y, $fontsize,': '.$lrow["no_rangka"].'');
$pdf->addText($x3,$start_y, $fontsize,'SB Eff /thn');
$pdf->addText($x4,$start_y, $fontsize,': ');
$pdf->ezSetY($start_y+10);
$pdf->ezText(substr($rate_eff,0,10),$fontsize,array('justification'=>'right','right'=>$x_right));
$start_y-=10;

$pdf->addText($x1,$start_y, $fontsize,'No. Mesin');
$pdf->addText($x2,$start_y, $fontsize,': '.$lrow["no_mesin"].'');
$pdf->addText($x3,$start_y, $fontsize,'SB Eff /bln');
$pdf->addText($x4,$start_y, $fontsize,': ');
$pdf->ezSetY($start_y+10);
$pdf->ezText(substr($rate_eff/12,0,9),$fontsize,array('justification'=>'right','right'=>$x_right));
$start_y-=10;

$pdf->addText($x1,$start_y, $fontsize,'Tenor');
$pdf->addText($x2,$start_y, $fontsize,': '.$lrow["lama_pinjaman"].'');
$pdf->addText($x3,$start_y, $fontsize,'SB Denda /bln');
$pdf->addText($x4,$start_y, $fontsize,': ');
$pdf->ezSetY($start_y+10);
$pdf->ezText(substr($lrow["denda"],0,10),$fontsize,array('justification'=>'right','right'=>67));
$start_y-=10;

$pdf->addText($x1,$start_y, $fontsize,'Pembayaran');
$pdf->addText($x2,$start_y, $fontsize,': '.$lrow["addm_addb"].'');
$pdf->addText($x3,$start_y, $fontsize,'Angsuran/Bln');
$pdf->addText($x4,$start_y, $fontsize,': ');
$pdf->ezSetY($start_y+10);
$pdf->ezText(convert_money("",$lrow["angsuran_bulan"]),$fontsize,array('justification'=>'right','right'=>$x_right));
$start_y-=10;

$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');

$pdf->ezSetY($start_y);

$l_res1=pg_query("
	select * from data_fa.tblangsuran	
	where fk_sbg = '".$fk_sbg."' order by angsuran_ke
");	
		
$i = 1;
while($lrow1=pg_fetch_array($l_res1)){
	$angsuran_ke = $lrow1['angsuran_ke'];
	$tgl_jatuh_tempo = date('d/m/Y',strtotime($lrow1['tgl_jatuh_tempo']));
	$tgl_bayar = ($lrow1["tgl_bayar"]==""?"":date("d/m/Y",strtotime($lrow1["tgl_bayar"])));
	
	$data[$i]['angsuran_ke'] = $angsuran_ke;
	$data[$i]['tgl_jatuh_tempo'] = $tgl_jatuh_tempo;	
	$data[$i]['bunga_jt'] = convert_money("",$lrow1["bunga_jt"]);
	$data[$i]['pokok_jt'] = convert_money("",$lrow1["pokok_jt"]);	
	$data[$i]['saldo_pokok'] = convert_money("",$lrow1["saldo_pokok"]);
	$data[$i]['saldo_bunga'] = convert_money("",$lrow1["saldo_bunga"]);	
	$data[$i]['saldo_pinjaman'] = convert_money("",$lrow1["saldo_pinjaman"]);	
	$i++;
	$total_pokok+=$lrow1["pokok_jt"];
	$total_bunga+=$lrow1["bunga_jt"];
}

	$data[$i]['tgl_jatuh_tempo'] = 'Total';	
	$data[$i]['bunga_jt'] = convert_money("",$total_pokok);
	$data[$i]['pokok_jt'] = convert_money("",$total_bunga);	
	$i++;


$judul['angsuran_ke'] = 'NO. ';
$judul['tgl_jatuh_tempo'] = 'Tgl Jt Tempo';
$judul['bunga_jt'] = 'Margin Jt';
$judul['pokok_jt'] = 'Pokok Jt';
$judul['saldo_pokok'] = 'Hutang Pokok';
$judul['saldo_bunga'] = 'Pendapatan Belum Diakui';
$judul['saldo_pinjaman'] = 'Piutang Kontrak';

$lining['showHeadings'] = 1;
$lining['shaded'] = 0;
$lining['showLines'] = 1;
$lining['xPos'] = 270;
$lining['fontSize'] = 9;
$lining['rowGap'] = 1.5;

$lining['cols']['bunga_jt']['justification'] = 'right';
$lining['cols']['pokok_jt']['justification'] = 'right';
$lining['cols']['tgl_bayar']['justification'] = 'center';	
$lining['cols']['saldo_pokok']['justification'] = 'right';
$lining['cols']['saldo_bunga']['justification'] = 'right';
$lining['cols']['saldo_pinjaman']['justification'] = 'right';
$size['angsuran_ke'] = '30';
$size['tgl_jatuh_tempo'] = '65';
$size['bunga_jt'] = '80';
$size['pokok_jt'] = '80';
$size['saldo_pokok'] = '80';
$size['saldo_bunga'] = '80';
$size['saldo_pinjaman'] = '80';
//$pdf->line(10,240,585,240);

$pdf->ezTable($data,$judul,'',$lining,$size);

$start_y=$pdf->y;
$start_y-=20;
$pdf->addText($x1,$start_y, $fontsize,'PrintTime');
$pdf->addText($x2,$start_y, $fontsize,': '.date("d/m/Y H:i"));

$start_y=$pdf->y;
$start_y-=30;
$pdf->addText($x1,$start_y, $fontsize,'User');
$pdf->addText($x2,$start_y, $fontsize,': '.$_SESSION['username'].'');

$pdf->ezStream();

?>