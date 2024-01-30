<?php
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/timestamp.inc.php';
require '../classes/ezpdf.class.php';
require '../requires/convert.inc.php';
require '../requires/numeric.inc.php';
set_time_limit(0);
$periode_awal = convert_date_english($_REQUEST['periode_awal']);
$periode_akhir = convert_date_english($_REQUEST['periode_akhir']);

$fk_cabang = $_REQUEST['fk_cabang'];
$nm_cabang=get_rec("tblcabang","nm_cabang","kd_cabang='".$fk_cabang."'");
$fk_bank = $_REQUEST['fk_bank'];
if($fk_bank){
	$lwhere=" and fk_bank = '".$fk_bank."'";
}

$fk_cabang2 = $_REQUEST['fk_cabang2'];
if($fk_cabang2)$nm_cabang=get_rec("tblcabang","nm_cabang","kd_cabang='".$fk_cabang2."'");

$pdf = new Cezpdf('A3','');  

$pdf->ez['topMargin'] = 30;
$pdf->ez['bottomMargin']=30;

$all = $pdf->openObject();
$pdf->saveState();

$fontsize= 9;
$pdf->selectFont('fonts/Times');

$x1=30;
$y=$y_atas=1160;

$x2=420;
$x3=420;

$pdf->addText($x1, $y, $fontsize,'LAPORAN PENERIMAAN  '.convert_date_indonesia($periode_awal).' - '.convert_date_indonesia($periode_akhir).' '.$nm_cabang);

$y-=15;

$pdf->ezSetY($y);

if(!$_REQUEST['jenis_tgl']){
	$jenis_tgl='tgl_input';
}else{
	$jenis_tgl=$_REQUEST['jenis_tgl'];
}

$query = "
select * from(
	select *,case when fk_cabang_data is not null then fk_cabang_data else fk_cabang end as fk_cabang_input,tblinventory.fk_cabang as fk_cabang_kontrak from(
		select * from (
			select fk_cabang_input as fk_cabang_data,no_kwitansi,fk_sbg,'ANG' as ket,nilai_bayar_angsuran as nilai_bayar,cara_bayar,0 as disc_denda,tgl_input,0 as nilai_bayar_denda , 0 as nilai_bayar_denda2,no_kwitansi_manual,nm_kolektor,fk_bank,'0' as urutan,tgl_bayar from data_fa.tblpembayaran_cicilan
			where nilai_bayar_angsuran > 0 and tgl_batal is null
			union all
			select fk_cabang_input,no_kwitansi,fk_sbg,'Ta''widh' as ket,nilai_bayar_denda as nilai_bayar,cara_bayar,0 as disc_denda,tgl_input,nilai_bayar_denda,nilai_bayar_denda2,no_kwitansi_manual,nm_kolektor,fk_bank,'1',tgl_bayar  from data_fa.tblpembayaran_cicilan
			where nilai_bayar_denda > 0 and tgl_batal is null
			union all
			select fk_cabang_input,no_kwitansi,fk_sbg,'Ta''zir' as ket,nilai_bayar_denda2 as nilai_bayar,cara_bayar,0 as disc_denda,tgl_input,nilai_bayar_denda,nilai_bayar_denda2,no_kwitansi_manual,nm_kolektor,fk_bank,'1',tgl_bayar   from data_fa.tblpembayaran_cicilan
			where nilai_bayar_denda2 > 0 and tgl_batal is null
			union all
			select NULL,no_kwitansi,fk_sbg,'PEL' as ket,sisa_pokok+bunga_berjalan+pinalti+sisa_angsuran as nilai_bayar,cara_bayar,diskon_pelunasan as disc_denda,tgl_bayar,0 as nilai_bayar_denda, 0 as nilai_bayar_denda2,'','',fk_bank,'1',tgl_bayar   from data_fa.tblpelunasan_cicilan
			where (sisa_angsuran > 0 or sisa_pokok > 0) and tgl_batal is null
			union all
			select fk_cabang_input,no_kwitansi,fk_sbg,'Ta''widh' as ket,nilai_bayar_denda as nilai_bayar,cara_bayar,diskon_pelunasan as disc_denda,tgl_bayar,nilai_bayar_denda,nilai_bayar_denda2,'','',fk_bank,'1',tgl_bayar from data_fa.tblpelunasan_cicilan
			where nilai_bayar_denda > 0 and tgl_batal is null
			union all
			select fk_cabang_input,no_kwitansi,fk_sbg,'Ta''zir' as ket,nilai_bayar_denda2 as nilai_bayar,cara_bayar,diskon_pelunasan as disc_denda,tgl_bayar,nilai_bayar_denda,nilai_bayar_denda2,'','',fk_bank,'1',tgl_bayar from data_fa.tblpelunasan_cicilan
			where nilai_bayar_denda2 > 0 and tgl_batal is null
			union all
			select fk_cabang_input,no_kwitansi,fk_sbg,'PEL' as ket,angka_lelang as nilai_bayar,'Cash' as cara_bayar,0 as disc_denda,tgl_lelang,0 as nilai_bayar_denda,0 as nilai_bayar_denda2,'','',fk_bank,'1',tgl_lelang from data_gadai.tbllelang
			where status_data='Approve' and jenis_transaksi in('Jual Cash','Lelang')
			
			union all
			select fk_cabang_input,no_kwitansi,fk_sbg,'TGH' as ket,biaya_tagih as nilai_bayar,cara_bayar,0 as disc_denda,tgl_input,0 as nilai_bayar_denda , 0 as nilai_bayar_denda2,no_kwitansi_manual,nm_kolektor,fk_bank,'1',tgl_bayar  from data_fa.tblpembayaran_cicilan
			where biaya_tagih > 0 and tgl_batal is null
		) as tblmain			
		where ".$jenis_tgl." between '".$periode_awal." 00:00:00' and '".$periode_akhir." 23:59:59'
		".$lwhere."
	)as tblmain
	inner join (
		select fk_sbg as fk_sbg_inventory,nm_customer,fk_cabang,nm_cabang,initial_cabang,jenis_cabang from tblinventory 
		left join tblcustomer on fk_cif=no_cif
		left join tblcabang on fk_cabang=kd_cabang
	) as tblinventory on fk_sbg_inventory=fk_sbg
	--inner join (
		--select distinct on(fk_sbg,referensi) fk_sbg as fk_sbg_history,fk_user,referensi,tgl_bayar as tgl_input_sistem from data_gadai.tblhistory_sbg
	--) as tblhistory on no_kwitansi=referensi 
	left join(
		select no_kwitansi as no_kwitansi_log,log_action_username as fk_user,log_action_date as tgl_input_sistem from data_fa.tblpembayaran_cicilan_log
		union all
		select no_kwitansi,log_action_username,log_action_date as tgl_input_sistem from data_fa.tblpelunasan_cicilan_log
		union all
		select * from(
		select distinct on (no_kwitansi)no_kwitansi,log_action_username,log_action_date as tgl_input_sistem from data_gadai.tbllelang_log where log_action_mode='IA' order by no_kwitansi,log_action_date desc
		)as tbllelang
	)as tbllog on no_kwitansi_log=no_kwitansi
)as tblmain
where true
".($fk_cabang?" and fk_cabang_input='".$fk_cabang."'":"")."
".($fk_cabang2?" and ((fk_cabang_kontrak='".$fk_cabang2."' and cara_bayar!='Cash')or fk_cabang_input='".$fk_cabang2."')":"")."
order by tgl_bayar,no_kwitansi,urutan
";
// showquery($query);
$lrs = pg_query($query);
$i=1;

while($lrow=pg_fetch_array($lrs)){
	
	if($lrow["cara_bayar"]!='Giro/Cek'){
		$lrow["tunai"]=$lrow["nilai_bayar"];
		$lrow["giro_cek"]=0;
	} elseif($lrow["cara_bayar"]=='Giro/Cek') {
		$lrow["giro_cek"]=$lrow["nilai_bayar"];
		$lrow["tunai"]=0;
	}
	
	$tgl_input=$lrow["tgl_input_sistem"];
	//$tgl_input=get_rec("data_gadai.tblhistory_sbg","tgl_bayar","referensi='".$lrow['no_kwitansi']."'");	
	
	$lrow['subtotal']=$lrow['tunai']+$lrow['giro_cek'];
	$lrow['total']=$lrow['subtotal']-$lrow["disc_denda"];
	//echo $no;
	$data[$i]['no'] = $i;
	$data[$i]['no_kwitansi'] = $lrow['no_kwitansi'];
	$data[$i]['no_kontrak'] = $lrow['fk_sbg'];
	$data[$i]['fk_cabang'] = $lrow['initial_cabang'];
	$data[$i]['nm_customer'] = $lrow['nm_customer'];
	$data[$i]['ket'] = $lrow['ket'];
	$data[$i]['tunai'] = convert_money("",$lrow['tunai']);
	$data[$i]['giro_cek'] =  convert_money("",$lrow['giro_cek']);
	//$data[$i]['subtotal'] =  convert_money("",$lrow['subtotal']);
	$data[$i]['disc_denda'] =  convert_money("",$lrow['disc_denda']);
	$data[$i]['total'] =  convert_money("",$lrow['total']);
	$data[$i]['cara_bayar'] = $lrow['cara_bayar'];	
	$data[$i]['tgl_bayar'] = date("d/m/Y",strtotime($lrow['tgl_bayar']));
	
	$data[$i]['no_kwitansi_manual'] = $lrow['no_kwitansi_manual'];	
	$data[$i]['tgl_input'] = date("d/m/y H:i",strtotime($tgl_input));
	$lrow['fk_user']=substr($lrow['fk_user'],0,11);
	$data[$i]['fk_user'] = $lrow['fk_user'];
	
	$grandtotal['tunai']+=$lrow['tunai'];
	$grandtotal['giro_cek']+=$lrow['giro_cek'];	
	$grandtotal['subtotal']+=$lrow['subtotal'];	
	$grandtotal['disc_denda']+=$lrow['disc_denda'];	
	$grandtotal['total']+=$lrow['total'];	
	
	if($lrow['cara_bayar']=='Collector'){		
		if($lrow['nm_kolektor']=='')$lrow['nm_kolektor']='-';
		$lrow['cara_bayar2']=$lrow['ket'].' '.$lrow['cara_bayar'].' '.$lrow['nm_kolektor'];
		$arr_col[$lrow['cara_bayar2']]+=$lrow["nilai_bayar"];		
	}elseif($lrow['cara_bayar']=='Indomaret' || $lrow['cara_bayar']=='Alfamart' || $lrow['cara_bayar']=='Tokopedia'){		
		if($lrow['ket']!='ANG'){
			$lrow['cara_bayar2']=$lrow['ket'].' '.$lrow['cara_bayar'];
			$arr_col[$lrow['cara_bayar2']]+=$lrow["nilai_bayar"];	
		}
	}
	
	if($lrow['ket']=='ANG'){
		$arr3['Angs via '.$lrow['cara_bayar']]+=$lrow["nilai_bayar"];	
	}
	
	$arr2[$lrow['cara_bayar']]+=$lrow["nilai_bayar"];			
	$arr[$lrow['ket']]+=$lrow["nilai_bayar"];
	
	//$jml_debitur[$lrow['fk_sbg']]=$lrow['fk_sbg'];
	// if($lrow['cara_bayar']=='Indomaret' || $lrow['cara_bayar']=='Alfamart' || $lrow['cara_bayar']=='Cash' || $lrow['cara_bayar']=='Collector' || strpos($lrow['cara_bayar'], 'Webpay') === true || $lrow['cara_bayar']=='Giro/Cek'){	
		$jml_debitur[$lrow['cara_bayar']][$lrow['fk_sbg']]=$lrow['fk_sbg'];
	// }else{
	// 	$jml_debitur[' '][$lrow['fk_sbg']]=$lrow['fk_sbg'];
	// }
	
	$i++;
	$jenis_cabang=$lrow["jenis_cabang"];
}
//print_r($jml_debitur);

$data[$i]['nm_customer'] = 'GRAND TOTAL';
$data[$i]['tunai'] = convert_money("",$grandtotal['tunai']);
$data[$i]['giro_cek'] =  convert_money("",$grandtotal['giro_cek']);
$data[$i]['subtotal'] =  convert_money("",$grandtotal['subtotal']);
$data[$i]['disc_denda'] =  convert_money("",$grandtotal['disc_denda']);
$data[$i]['total'] =  convert_money("",$grandtotal['total']);
$data[$i]['cara_bayar'] = $grandtotal['cara_bayar'];
$i++;

$judul['no'] = 'No';
$judul['no_kwitansi'] = 'No Kwitansi';
$judul['no_kontrak'] = 'No Kontrak';
$judul['fk_cabang'] = 'Cab';
$judul['nm_customer'] = 'Nama Customer';
$judul['ket'] = 'Ket';
$judul['tunai'] = 'Tunai';
$judul['giro_cek'] = 'Cek+Giro';
//$judul['subtotal'] = 'Subtotal';
$judul['disc_denda'] = 'Disc';
$judul['total'] = 'Total';
$judul['cara_bayar'] = 'Cara Bayar';
$judul['tgl_bayar'] = 'Tanggal';
$judul['no_kwitansi_manual'] = 'Kwt. Manual';

$judul['tgl_input'] = 'Waktu Input';
$judul['fk_user'] = 'User';


$xPos=420;
$lining['showHeadings'] = 1;
$lining['shaded'] = 0;
$lining['showLines'] =1 ;
$lining['xPos'] = $xPos;
$lining['fontSize'] =8 ;
$lining['colGap'] = 2 ;

$lining['cols']['tunai']['justification'] = 'right';
$lining['cols']['giro_cek']['justification'] = 'right';
$lining['cols']['subtotal']['justification'] = 'right';
$lining['cols']['disc_denda']['justification'] = 'right';
$lining['cols']['total']['justification'] = 'right';

//$size['no_kwitansi'] = '70';
$size['no'] = '18';
$size['fk_cabang'] = '25';
$size['nm_customer'] = '123';
$size['ket'] = '32';
$size['tunai'] = '45';
$size['giro_cek'] = '45';
$size['subtotal'] = '45';
$size['disc_denda'] = '45';
$size['total'] = '45';
$size['cara_bayar'] = '45';

$size['no_kwitansi_manual'] = '55';
$size['fk_user'] = '69';
//print_r($data);
$pdf->ezTable($data,$judul,'',$lining,$size);

$y=$pdf->y;

/*$data2[$i]['1'] = 'Sub Total Angsuran';
$data2[$i]['2'] =  convert_money("",$arr['ANG']);
$i++;
*/

//print_r($arr3);
if(count($arr3)>0){
	foreach($arr3 as $cara =>$nilai){
		$data2[$i]['1'] = 'Sub Total '.$cara;
		$data2[$i]['2'] =  convert_money("",$nilai);
		$i++;
	}
}

$data2[$i]['1'] = "Sub Total Ta'widh";
$data2[$i]['2'] =  convert_money("",$arr["Ta'widh"]);
$i++;
$data2[$i]['1'] = "Sub Total Ta'zir";
$data2[$i]['2'] =  convert_money("",$arr["Ta'zir"]);
$i++;
$data2[$i]['1'] = "Sub Total Tagih";
$data2[$i]['2'] =  convert_money("",$arr['TGH']);
$i++;

$data2[$i]['1'] = "Sub Total Penjualan/Pelunasan";
$data2[$i]['2'] =  convert_money("",$arr['PEL']);
$i++;
$data2[$i]['1'] = "";
$data2[$i]['2'] = "____________";
$i++;

$data2[$i]['1'] = "Total Penerimaan";
$data2[$i]['2'] =  convert_money("",$grandtotal['total']);
$i++;

if(count($jml_debitur)>0){
	foreach($jml_debitur as $cara =>$nilai){
		$data2[$i]['1'] = 'Jumlah Debitur '.$cara;
		$data2[$i]['2'] =  convert_money("",count($nilai));
		$total_debitur+= (int) $nilai;
		$i++;
	}
}

$data2[$i]['1'] = "Jumlah Debitur";
$data2[$i]['2'] =  $total_debitur;
$i++;

$data2[$i]['1'] = "===================================";
$data2[$i]['2'] = "=======================";
$i++;

if(count($arr2)>0){
	foreach($arr2 as $cara =>$nilai){
		$data2[$i]['1'] = 'Total '.$cara;
		$data2[$i]['2'] =  convert_money("",$nilai);
		$i++;
	}
}

$data2[$i]['1'] = "===================================";
$data2[$i]['2'] = "=======================";
$i++;

if(count($arr_col)>0){
	foreach($arr_col as $cara =>$nilai){
		$data2[$i]['1'] = 'Total '.$cara;
		$data2[$i]['2'] =  convert_money("",$nilai);
		$i++;
	}
}


$judul['1'] = '';
$judul['2'] = '';

$lining2['showHeadings'] = 0;
$lining2['shaded'] = 0;
$lining2['showLines'] =1 ;
$lining2['xPos'] = $xPos-241.7;
$lining2['fontSize'] =8 ;
//$lining['colGap'] = 3 ;

$lining2['cols']['1']['justification'] = 'left';
$lining2['cols']['2']['justification'] = 'right';

$size2['1'] = '177.5';
$size2['2'] = '123';

$pdf->ezTable($data2,$judul2,'',$lining2,$size2);

$y=$pdf->y;
if($y<50){
	$pdf->ezNewPage();
	$y=$y_atas;
}

$y-=20;
$pdf->addText($x1, $y, $fontsize,$nm_cabang.', '.date('d M Y H:i:s'));

$y-=20;
$pdf->addText($x1, $y, $fontsize,'Dibuat oleh');
$pdf->addText($x1+240, $y, $fontsize,'Diperiksa oleh');
$pdf->addText($x1+380, $y, $fontsize,'Diketahui oleh');
$y-=60;

if($fk_cabang==cabang_ho){
	$jab1='KASIR';
	$jab2='FINANCE CHECKER';
	$jab3='Supervisor Finance ';
}
else{
	$jab1='KASIR';
	$jab2='Administration Head';
	$jab3='Kepala Cabang';
	
	if($jenis_cabang=='Pos'){
		$jab3='Kepala Pos';
	}
}

$nm1=get_karyawan_by_jabatan($jab1,$fk_cabang);
$nm2=get_karyawan_by_jabatan($jab2,$fk_cabang);
$nm3=get_karyawan_by_jabatan($jab3,$fk_cabang);


$pdf->addText($x1, $y, $fontsize,$nm1);
$pdf->addText($x1+240, $y, $fontsize,$nm2);
$pdf->addText($x1+380, $y, $fontsize,$nm3);

$pdf->addText($x1, $y, $fontsize,'____________________');
$pdf->addText($x1+240, $y, $fontsize,'____________________');
$pdf->addText($x1+380, $y, $fontsize,'____________________');
$y-=15;
$pdf->addText($x1, $y, $fontsize,$jab1);
$pdf->addText($x1+240, $y, $fontsize,$jab2);
$pdf->addText($x1+380, $y, $fontsize,$jab3);

$pdf->ezSetY($y+230);
//echo $y;
if($y<50)$pdf->ezSetY(350);


$pk_id="LPK_NRA_".date('YmdHis');
if($_SESSION["username"]!='superuser'){
if(!pg_query("insert into tblreport_log (pk_id,tgl_cetak,user_cetak) values ('".convert_sql($pk_id)."','".date("m/d/Y H:i:s")."','".convert_sql($_SESSION["username"])."')"));
}

$codeContents='['.$_SESSION["nm_cabang"].', '.$today.'] - [MadeBy:'.$_SESSION["username"].'] - [TtlTrx:'.($i-1).'] - [TtlAng:'.convert_money("",$arr['ANG']).'] - [TtlDnd:'.convert_money("",$arr["Ta'widh"]+$arr["Ta'zir"]).'] - [Nomor:'.$pk_id."]";

if($_SERVER['DOCUMENT_ROOT']=="D:/Development/Web Project"){
	//$filename="http://192.168.4.10/gadai/test.php";
	$filename="http://192.168.4.10/gadai/qr_code.php?codeContents=".urlencode($codeContents)."";
	//$filename="http://116.90.163.21:81/api/qr_code.php?codeContents=".$id_edit."";
}elseif($_SERVER['DOCUMENT_ROOT']=="/www/erp"){
	$filename="http://116.90.163.21:81/api/qr_code.php?codeContents=".urlencode($codeContents)."";
}else{
	$filename="http://localhost:81/api/qr_code.php?codeContents=".urlencode($codeContents)."";
}
//echo $pdf->y;
$pdf->ezImage($filename,'130','60','','right','');


$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');
	
//end content
$options['Content-Disposition']=$pk_id.'.pdf';//nama file
$pdf->ezStream($options);   

?>