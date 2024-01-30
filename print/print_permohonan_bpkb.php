<?php
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/timestamp.inc.php';
require '../classes/ezpdf.class.php';
require '../requires/convert.inc.php';
require '../requires/numeric.inc.php';

$id_edit= $_REQUEST['id_edit'];


$query="
select * from data_fa.tblpermohonan_bpkb left join data_fa.tblpermohonan_bpkb_detail on fk_permohonan=no_permohonan
left join(select nm_customer,nm_bpkb,no_sbg_ar,fk_cif,no_polisi from data_gadai.tbltaksir_umum
left join tblcustomer on no_cif=fk_cif
)as tbltaksir on fk_sbg = no_sbg_ar
left join tblcabang on fk_cabang=kd_cabang
left join(
	select status as status_kontrak,fk_sbg as fk_sbg1 from tblinventory
)as tblinv on fk_sbg1=no_sbg_ar
where no_permohonan='".$id_edit."'
";	
//showquery($query);
$lrow=pg_fetch_array(pg_query($query));			
$nm_cabang=$lrow["nm_cabang"];

$pdf = new Cezpdf('A4','');  
$pdf->ez['topMargin'] = $heigth-$y_table;
$pdf->ez['bottomMargin']=130;
$pdf->ez['rightMargin']=30;
//$all = $pdf->openObject();
//$pdf->saveState();
//$pdf->ezImage('../print/logo.jpeg','','180','','left','');


$fontsize= 8;
$pdf->selectFont('fonts/Times');

$x1=15;
$y=820;

$x2=120;
$x3=145;
$x4=470;

if($lrow["jenis_cabang"]=='Cabang')$nm_jabatan='KACAB';
if($lrow["jenis_cabang"]=='Pos')$nm_jabatan='KAPOS';
$total_data=pg_num_rows(pg_query($query));	
$nm_up=get_karyawan_by_jabatan('Adm. BPKB','000');


$pdf->addText($x1, $y, $fontsize,'No SP : '.$id_edit);
$pdf->addText($x4, $y, $fontsize,$lrow["nm_cabang"].date(' d M Y '));
$y-=11;
$pdf->addText($x1, $y, $fontsize,'Kepada : ');
$y-=11;
$pdf->addText($x1, $y, $fontsize,$lrow["nm_perusahaan"].' HO');
$y-=11;
$pdf->addText($x1, $y, $fontsize,'UP '.$nm_up.'/ BAGIAN BPKB ');
$y-=22;
$pdf->addText($x1, $y, $fontsize,'Perihal : Permohonan BPKB Asli');
$y-=22;
$pdf->addText($x1, $y, $fontsize,'Berikut Kami Sampaikan Daftar Permohonan BPKB : ');
$y-=2;

$pdf->ezSetY($y);
$i=1;
//showquery($query);
//echo $lrow["keterangan"];

$lrs=pg_query($query);
while($lrow = pg_fetch_array($lrs)){
	if($lrow["status_kontrak"]=='Jual Cash' ||$lrow["status_kontrak"]=='Jual Credit' ||$lrow["status_kontrak"]=='Lelang'){
		$lrow["sisa_denda"]=0;
	}
	$data[$i]['no'] = $i;
	if($lrow["nm_bpkb"]==$lrow["nm_customer"]){
		$data[$i]['nama'] =  $lrow["nm_customer"];
	} else{
		$data[$i]['nama'] =  $lrow["nm_customer"] . ' / ' . $lrow["nm_bpkb"];;
	}
	$data[$i]['no_kontrak'] = $lrow["fk_sbg"];
	$data[$i]['no_polisi'] =  $lrow["no_polisi"];
	$data[$i]['sisa_ang'] =  convert_money("",($lrow["sisa_angsuran"]));
	$data[$i]['dis_denda'] =  convert_money("",($lrow["diskon_denda"]));
	$data[$i]['sisa_denda'] =  convert_money("",($lrow["sisa_denda"]));
	$data[$i]['posisi_bpkb'] =  $lrow["posisi_bpkb"];
	$data[$i]['ket'] = $lrow["keterangan"];
	
		
	$i++;
}

$lining['showHeadings'] = 1;
$lining['shaded'] = 0;
$lining['showLines'] =1 ;
$lining['xPos'] = 299;
$lining['fontSize'] = 7.5;
$lining['colGap'] = 2;

	$judul['no'] = 'NO';
	$judul['nama'] = 'NAMA';
	$judul['no_kontrak'] = 'CONTRACT NO';
	$judul['no_polisi'] = 'NO POLISI';
	$judul['sisa_ang'] = 'SISA ANGSURAN';
	$judul['dis_denda'] = 'DISKON DENDA';
	$judul['sisa_denda'] = 'SISA DENDA';
	$judul['posisi_bpkb'] = 'POSISI BPKB';
	$judul['ket'] = 'KETERANGAN';
	
	$lining['cols']['no']['justification'] = 'center';
	$lining['cols']['nama']['justification'] = 'left';
	$lining['cols']['no_kontrak']['justification'] = 'center';
	$lining['cols']['no_polisi']['justification'] = 'center';
	$lining['cols']['posisi_bpkb']['justification'] = 'center';
	$lining['cols']['ket']['justification'] = 'left';
	$lining['cols']['sisa_ang']['justification'] = 'right';
	$lining['cols']['dis_denda']['justification'] = 'right';
	$lining['cols']['sisa_denda']['justification'] = 'right';
	
	$lining['cols']['no']['heading_justification'] = 'center';
	$lining['cols']['nama']['heading_justification'] = 'center';
	$lining['cols']['no_kontrak']['heading_justification'] = 'center';
	$lining['cols']['no_rangka_engine']['heading_justification'] = 'center';
	$lining['cols']['no_polisi']['heading_justification'] = 'center';
	$lining['cols']['no_bpkb']['heading_justification'] = 'center';
	$lining['cols']['sisa_ang']['heading_justification'] = 'center';
	$lining['cols']['dis_denda']['heading_justification'] = 'center';
	$lining['cols']['sisa_denda']['heading_justification'] = 'center';
	
	$size['no'] = '20';
	$size['nama'] = '125';
	$size['no_kontrak'] = '65';
	$size['no_polisi'] = '50';
	$size['posisi_bpkb'] = '40';
	$size['sisa_ang'] = '50';
	$size['dis_denda'] = '40';
	$size['sisa_denda'] = '40';
	$size['ket'] = '135';
	
//print_r($data);
$pdf->ezTable($data,$judul,'',$lining,$size);

//$pdf->restoreState();
//$pdf->closeObject();
//$pdf->addObject($all,'all');

$y=$pdf->y;

$y-=20;
$x2=130;
$x3=260;
$x4=380;
$x5=500;

$lrow=pg_fetch_array(pg_query($query));
$user=get_rec("tbluser left join tblkaryawan on npk=fk_karyawan","nm_depan","username='".$_SERVER['username']."'");

$pdf->addText($x1, $y, $fontsize,'Dengan ini menyatakan bahwa sisa angsuran dan sisa denda akan dibayarkan pada saat pengambilan BPKB di '.$nm_cabang.'.');	
$y-=10;
$pdf->addText($x1, $y, $fontsize,'Jika BPKB tidak diambil dalam jangka waktu 2 bulan, maka akan dibebankan biaya penitipan BPKB sesuai dengan aturan yang berlaku. .');
$y-=20;
$pdf->addText($x1, $y, $fontsize,'Atas kerjasamanya kami ucapkan terimakasih.');
//end content

$y-=20;
$pdf->addText($x1, $y, $fontsize,'Dibuat oleh:');
$pdf->addText($x2, $y, $fontsize,'Diperiksa oleh:');
$pdf->addText($x3, $y, $fontsize,'Diketahui oleh:');
$pdf->addText($x4, $y, $fontsize,'Diterima oleh:');
$pdf->addText($x5, $y, $fontsize,'Diproses oleh:');

$y-=70;

//echo $lrow["fk_cabang_kirim"].' '.$lrow["jenis_cabang"].' '.$nm_jabatan3;
$nm_jabatan4='Adm. BPKB';
if($lrow["jenis_cabang"]=='Cabang'){
	$nm_jabatan3='KACAB';	
}else{
	$nm_jabatan3='KAPOS';
}	

//$nm_karyawan1=get_karyawan_by_jabatan($nm_jabatan1,$lrow["fk_cabang_kirim"]);
$nm_karyawan1=$_SESSION['username'];
$nm_karyawan2=get_karyawan_by_jabatan('ADH',$lrow["fk_cabang"]);
$nm_karyawan3=get_karyawan_by_jabatan($nm_jabatan3,$lrow["fk_cabang"]);
$nm_karyawan4=get_karyawan_by_jabatan($nm_jabatan4,$lrow["fk_cabang_terima"]);
$nm_karyawan5=$nm_up;

if(!$nm_karyawan1)$nm_karyawan1='                      ';
if(!$nm_karyawan2)$nm_karyawan2='                      ';
if(!$nm_karyawan3)$nm_karyawan3='                      ';
if(!$nm_karyawan4)$nm_karyawan4='                      ';
if(!$nm_karyawan5)$nm_karyawan5='                      ';

$pdf->addText($x1, $y, $fontsize-1,'('.$nm_karyawan1.')');//adm bpkb
$pdf->addText($x2, $y, $fontsize-1,'('.$nm_karyawan2.')');// acc
$pdf->addText($x3, $y, $fontsize-1,'('.$nm_karyawan3.')');//spv fin
$pdf->addText($x4, $y, $fontsize-1,'(BAG. A/R HO)');//kasir/adh	
$pdf->addText($x5, $y, $fontsize-1,'('.$nm_karyawan5.')');//kasir/adh	

$pdf->ezStream();   

?>