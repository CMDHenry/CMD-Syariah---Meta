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
	select kd_produk,nominal_denda_keterlambatan,rate_denda_ganti_rugi from tblproduk
)as tblproduk on tblinventory.fk_produk=kd_produk
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

//$tgl_sistem=today_db;
$tgl_sistem=date("m/d/Y");
//$tgl_sistem='2024-05-01';

$pdf->addText($x1,$start_y, $fontsize,$lrow["nm_perusahaan"].'');
$pdf->addText($x1+274,$start_y, $fontsize,'Rincian Denda s/d '.date("d M Y",strtotime($tgl_sistem)));
$start_y-=10;

$pdf->addText($x1,$start_y, $fontsize,'No Kontrak');
$pdf->addText($x2,$start_y, $fontsize,': '.$fk_sbg.'');
$pdf->ezSetY($start_y+10);
$start_y-=10;

$pdf->addText($x1,$start_y, $fontsize,'Konsumen');
$pdf->addText($x2,$start_y, $fontsize,': '.$lrow["nm_customer"].'');
$pdf->ezSetY($start_y+10);
$start_y-=10;


$pdf->addText($x1,$start_y, $fontsize,'No. Rangka');
$pdf->addText($x2,$start_y, $fontsize,': '.$lrow["no_rangka"].'');
$pdf->ezSetY($start_y+10);
$start_y-=10;

$pdf->addText($x1,$start_y, $fontsize,'No. Mesin');
$pdf->addText($x2,$start_y, $fontsize,': '.$lrow["no_mesin"].'');
$pdf->ezSetY($start_y+10);
//$pdf->ezText(substr($lrow["rate_flat"],0,10),$fontsize,array('justification'=>'right','right'=>100));
$start_y-=20;


$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');

$pdf->ezSetY($start_y);

//$tgl_sistem='2021-02-09';
//'Denda Keterlambatan',
//,'Bayar Denda Keterlambatan'
$query="
	select * from data_fa.tblangsuran	
	left join(
		select sum(nilai_bayar)as denda,fk_sbg as fk_sbg1,referensi from data_gadai.tblhistory_sbg
		where transaksi in('Denda Ganti Rugi')
		group by fk_sbg,referensi
	)as tbldenda on fk_sbg=fk_sbg1 and referensi=no_kwitansi
	left join(
		select (nilai_bayar)as denda_bayar,fk_sbg as fk_sbg2,referensi as ref2,tgl_bayar_denda,ang_ke from data_gadai.tblhistory_sbg
		left join (
			select fk_sbg as fk_sbg1 ,tgl_bayar as tgl_bayar_denda,no_kwitansi from data_fa.tblpembayaran_cicilan
		)as tbl on fk_sbg=fk_sbg and referensi=no_kwitansi
		where transaksi in('Bayar Denda Ganti Rugi') and tgl_batal is null
	)as tbldenda2 on fk_sbg=fk_sbg2 and ang_ke=angsuran_ke	
	where fk_sbg = '".$fk_sbg."' and angsuran_ke >0
	--and tgl_jatuh_tempo<'".$tgl_sistem."' 
	--and tgl_bayar is not null
	order by angsuran_ke
";
$l_res1=pg_query($query);	
//showquery($query);
$i = 1;
while($lrow1=pg_fetch_array($l_res1)){
	$angsuran_ke = $lrow1['angsuran_ke'];
	$nilai_angsuran = $lrow1['nilai_angsuran'];
	$tgl_jatuh_tempo = date('d M Y',strtotime($lrow1['tgl_jatuh_tempo']));
	$tgl_bayar = ($lrow1["tgl_bayar"]==""?"":date("d M Y",strtotime($lrow1["tgl_bayar"])));
	$tgl_bayar_denda = ($lrow1["tgl_bayar_denda"]==""?"":date("d M Y",strtotime($lrow1["tgl_bayar_denda"])));
	$denda=0;
	$denda_keterlambatan=0;
	$denda_ganti_rugi=0;
	if($tgl_bayar)$tgl_hitung=$tgl_bayar;
	else $tgl_hitung=$tgl_sistem;
	

	//if($tgl_bayar){
		//$denda =$lrow1["denda"];
	//}else{
		$overdue=(strtotime($tgl_hitung)-strtotime($lrow1['tgl_jatuh_tempo']))/ (60 * 60 * 24);     
		if($overdue<0)$overdue=0;
		$libur=0;
		if($overdue>0){						
			$now=date("Y-m-d",strtotime('-1 day',strtotime($tgl_sistem)));
			
			while(strtotime($now)>=strtotime($tgl_jatuh_tempo)){		
				$hari=date('l',strtotime($now));
				if((pg_num_rows(pg_query("select * from tblhari_libur where tgl_libur= '".$now."'"))|| $hari=='Sunday')){
					$libur++;
					//break;
				}
				$now=date("Y-m-d",strtotime('-1 day',strtotime($now)));
				//break;
			}
			if($libur==$overdue)$overdue=0;
			
			//$denda_keterlambatan=$lrow["nominal_denda_keterlambatan"];
			$denda_ganti_rugi=round($lrow["rate_denda_ganti_rugi"]*$nilai_angsuran/100)*$overdue;
			$denda=$denda_keterlambatan+$denda_ganti_rugi;
		}
	
	//}
	
	if(pg_num_rows(pg_query("select * from data_gadai.tbllelang where no_kwitansi='".$lrow1["no_kwitansi"]."' and status_data='Approve'"))){
		$lrow1["denda_bayar"]=$sisa+$denda;//jual denda ga bayar
	}	
	
	$sisa+=($denda-$lrow1["denda_bayar"]);
	$data[$i]['no'] = $i;
	$data[$i]['angsuran_ke'] = $angsuran_ke;
	$data[$i]['tgl_jatuh_tempo'] = $tgl_jatuh_tempo;	
	$data[$i]['tgl_bayar'] = $tgl_bayar;
	$data[$i]['denda'] = convert_money("",$denda);	
	$data[$i]['tgl_bayar_denda'] = $tgl_bayar_denda;
	$data[$i]['denda_bayar'] = convert_money("",$lrow1["denda_bayar"]);
	$data[$i]['sisa'] = convert_money("",$sisa);	
	$i++;
	$total_denda+=$denda;
	$total_denda_bayar+=$lrow1["denda_bayar"];
}

	$data[$i]['tgl_jatuh_tempo'] = 'Total';	
	$data[$i]['denda'] = convert_money("",$total_denda);
	$data[$i]['denda_bayar'] = convert_money("",$total_denda_bayar);	
	$i++;

$judul['angsuran_ke'] = 'Ang Ke. ';
$judul['tgl_jatuh_tempo'] = 'Tgl Jto';
$judul['tgl_bayar'] = 'Tgl Bayar Angs';
$judul['denda'] = 'Jumlah';
$judul['denda_bayar'] = 'Bayar';
$judul['tgl_bayar_denda'] = 'Tgl Bayar Denda';
$judul['sisa'] = 'Sisa';

$lining['showHeadings'] = 1;
$lining['shaded'] = 0;
$lining['showLines'] = 1;
$lining['xPos'] = 260;
$lining['fontSize'] = 9;
$lining['rowGap'] = 1.5;

$lining['cols']['denda']['justification'] = 'right';
$lining['cols']['denda_bayar']['justification'] = 'right';
$lining['cols']['sisa']['justification'] = 'right';	
$size['angsuran_ke'] = '30';
$size['tgl_jatuh_tempo'] = '70';
$size['tgl_bayar'] = '70';
$size['denda'] = '80';
$size['tgl_bayar_denda'] = '70';
$size['denda_bayar'] = '80';
$size['sisa'] = '80';
//$pdf->line(10,240,585,240);
$pdf->ezTable($data,$judul,'',$lining,$size);
$start_y=$pdf->y;
$start_y-=20;
$pdf->addText($x1,$start_y, $fontsize,'User');
$pdf->addText($x2,$start_y, $fontsize,': '.$_SESSION["username"].'');

$pdf->ezStream();

?>