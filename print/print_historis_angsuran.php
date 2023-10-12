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
		select fk_sbg as fk_sbg_ang_ke, ang_ke, saldo_pinjaman as saldo from viewang_ke
	) as viewang_ke on fk_sbg = fk_sbg_ang_ke
	left join (
		select saldo_titipan, fk_sbg as fk_sbg_titipan from data_fa.tbltitipan
	) as tbltitipan on fk_sbg = fk_sbg_titipan
	left join tblcabang on fk_cabang=kd_cabang
	where fk_sbg = '".$fk_sbg."'");	

	while($lrow=pg_fetch_array($l_res)){
		$fk_cif = $lrow['fk_cif'];
		$ang_ke = $lrow['ang_ke'];
		$saldo_titipan = $lrow['saldo_titipan'];
		$saldo = $lrow['saldo'];
		$tgl_do = date('d/m/Y',strtotime($lrow['tgl_do']));
		$nm_cabang = $lrow['nm_cabang'];
		$nm_customer= $lrow['nm_customer'];
	}

$pdf = new Cezpdf('a4');  
$pdf->setLineStyle(1);

$pdf->ez['topMargin'] = (150);
$pdf->ez['bottomMargin']=50;

$all = $pdf->openObject();
$pdf->saveState();

//Header

$pdf->ezSetY(800);
$start_y=826;
$start_y-=15;
$pdf->addText(10,$start_y, 12,$nm_cabang.'');
$pdf->addText(434,$start_y, 12,'Tanggal : '.today_db.'');
$start_y-=15;


$pdf->ezSetY(790);
$pdf->ezText('KARTU PIUTANG',20,array('justification' =>'center'));


$pdf->ezSetY($start_y);

$start_y=750;
$start_y-=15;
$x1 = 10;
$x2 = 130;
$pdf->addText($x1,$start_y, 12,'No CIF');
$pdf->addText($x2,$start_y, 12,': '.$fk_cif.'');


$pdf->addText(328,$start_y, 12,'Nama');
$pdf->addText(368,$start_y, 12,': '.$nm_customer.'');
$start_y-=13;

$pdf->addText($x1,$start_y, 12,'No SBG');
$pdf->addText($x2,$start_y, 12,': '.$fk_sbg.'');
$start_y-=13;

$pdf->addText($x1,$start_y, 12,'Angsuran Ke');
$pdf->addText($x2,$start_y, 12,': '.convert_money("",$ang_ke).'');

$pdf->addText(328,$start_y, 12,'Saldo');
$pdf->addText(368,$start_y, 12,': '.convert_money("",$saldo).'');
$start_y-=13;

//footer
$y = $pdf->ez['bottomMargin'];

$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');

$pdf->ezSetY($start_y);
$start_y-=10;

$l_res1=pg_query("
	select * from data_fa.tblangsuran	
	left join (select fk_sbg as no_sbg, fk_cabang, fk_cif from tblinventory) as tblinventory on no_sbg=fk_sbg
	left join tblcustomer on no_cif = fk_cif 
	left join (
		 select (angsuran_ke+1) as ang_ke1,saldo_pinjaman as sisa_saldo,fk_sbg as fk_sbg2 from data_fa.tblangsuran
	)as tblangsuran2 on fk_sbg2=fk_sbg and ang_ke1=angsuran_ke
	where fk_sbg = '".$fk_sbg."' and angsuran_ke != 0
");	
		
$i = 1;
while($lrow1=pg_fetch_array($l_res1)){
	$sisa_saldo = $lrow1['sisa_saldo'];
	$angsuran_ke = $lrow1['angsuran_ke'];
	$tgl_jatuh_tempo = date('d/m/Y',strtotime($lrow1['tgl_jatuh_tempo']));
	$nilai_angsuran = $lrow1['nilai_angsuran'];
	//$tgl_bayar = date('d/m/Y',strtotime($lrow1['tgl_bayar']));
	$tgl_bayar = ($lrow1["tgl_bayar"]==""?"":date("d/m/Y",strtotime($lrow1["tgl_bayar"])));
	
	$data[$i]['angsuran_ke'] = $angsuran_ke;
	$data[$i]['tgl_jatuh_tempo'] = $tgl_jatuh_tempo;	
	$data[$i]['sisa_saldo'] = convert_money("",$sisa_saldo);
	$data[$i]['nilai_angsuran'] = convert_money("",$nilai_angsuran);	
	$data[$i]['tgl_bayar'] = $tgl_bayar;	
	
		
	$i++;
	
	
}
$judul['angsuran_ke'] = 'Ang. Ke';
$judul['tgl_jatuh_tempo'] = 'Tgl Jt Tempo';
$judul['sisa_saldo'] = 'Saldo';
$judul['nilai_angsuran'] = 'Nilai Angsuran';
$judul['tgl_bayar'] = 'Tgl Bayar';

$lining['showHeadings'] = 1;
$lining['shaded'] = 0;
$lining['showLines'] = 2;
$lining['xPos'] = 295;

$lining['cols']['angsuran_ke']['justification'] = 'center';	
$lining['cols']['tgl_jatuh_tempo']['justification'] = 'center';	
$lining['cols']['sisa_saldo']['justification'] = 'right';
$lining['cols']['nilai_angsuran']['justification'] = 'right';
$lining['cols']['tgl_bayar']['justification'] = 'center';	
	
$size['angsuran_ke'] = '52';
$size['tgl_jatuh_tempo'] = '121';
$size['sisa_saldo'] = '138';
$size['nilai_angsuran'] = '138';
$size['tgl_bayar'] = '121';

//$pdf->line(10,240,585,240);
$pdf->ezTable($data,$judul,'',$lining,$size);


$pdf->ezStream();

?>