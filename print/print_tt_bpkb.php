<?php
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/timestamp.inc.php';
require '../classes/ezpdf.class.php';
require '../requires/convert.inc.php';
require '../requires/numeric.inc.php';

$id_edit = $_REQUEST['id_edit'];

			
$query="
select *,nm_partner as nm_dealer,tblpartner.alamat as alamat_dealer,tblcustomer.npwp as npwp_cust,tblcabang.alamat as alamat_cabang from data_gadai.tblproduk_cicilan 
left join data_gadai.tbltaksir_umum on fk_fatg=no_fatg
left join tblcabang on kd_cabang=fk_cabang
left join tblcustomer on fk_cif=no_cif
left join tblpartner on fk_partner_dealer=kd_partner
left join (
	select * from viewkendaraan
)as tbldetail on tbldetail.no_fatg=fk_fatg
left join(
	select fk_sbg as fk_sbg1,nm_penerima from data_gadai.tbllelang where status_data='Approve' 
)as tbllelang on no_sbg=fk_sbg1
where fk_fatg='".$id_edit."' and tgl_cair is not null
";				
$lrow=pg_fetch_array(pg_query($query));
//showquery($query);
//echo $lrow["fk_karyawan_kacab"];

$pdf = new Cezpdf('A4','');  

$pdf->ez['topMargin'] = $heigth-$y_table;
$pdf->ez['bottomMargin']=130;

$all = $pdf->openObject();
$pdf->saveState();

$fontsize= 9;
$pdf->selectFont('fonts/Times');

$x1=33;
$y=800;

$x2=120;
$x3=145;


if(!$lrow["no_tt_bpkb"]){
	$query_serial="select nextserial_cabang('TTBPKB':: text,'".$lrow["kd_cabang"]."')";
	$lrow_serial=pg_fetch_array(pg_query($query_serial));
	$no_tt_bpkb=$lrow_serial["nextserial_cabang"];	
	
	$tbl='data_gadai.tbltaksir_umum';
	if(!pg_query("insert into ".$tbl."_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from ".$tbl." where no_fatg = '".$id_edit."' "))$l_success=0;	
	if(!pg_query("update ".$tbl." set no_tt_bpkb='".$no_tt_bpkb."' where no_fatg='".$id_edit."'")) $l_success=0;
	
	//showquery("update ".$tbl." set no_tt_bpkb='".$no_tt_bpkb."',tgl_serah_terima_bpkb='".today_db."' where no_fatg='".$id_edit."'");
		
	if(!pg_query("insert into ".$tbl."_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from ".$tbl." where no_fatg = '".$id_edit."' "))$l_success=0;

}else{
	$no_tt_bpkb=$lrow["no_tt_bpkb"];	
}

$pdf->line($x1-5,$y+22,555,$y+22);

$pdf->ezSetY($y+20);
$pdf->ezImage('../print/logo.jpeg','','180','','left','');
$y-=12;
$pdf->addText(200, $y, $fontsize+5,'TANDA TERIMA BPKB/FAKTUR');
$y-=10;
$pdf->addText($x1, $y, $fontsize-2,$lrow["alamat_cabang"]);
$y-=8;
$pdf->addText($x1, $y, $fontsize-2,$lrow["nm_cabang"]);
$y-=8;
$pdf->addText($x1, $y, $fontsize-2,'No. Telp : '.$lrow["no_telp"]);
$pdf->addText(420, $y, $fontsize,'No. '.$no_tt_bpkb);
$y-=8;

$pdf->line($x1-5,$y,555,$y);
$y-=12;
//$pdf->addText(260, $y, $fontsize,$lrow["no_sbg"]);
//$y-=6;
//$pdf->line($x1,$y,555,$y);
//$y-=12;
$pdf->addText($x1, $y, $fontsize+1,'TELAH DITERIMA 1 (SATU) BUAH BPKB DAN SURAT-SURAT DENGAN DATA SEBAGAI BERIKUT:');
$y-=18;

$pdf->addText($x1, $y, $fontsize,'No Kontrak');
$pdf->addText($x2, $y, $fontsize,': ');
$pdf->addText($x3, $y, $fontsize,''.$lrow["no_sbg"]);
$y-=15;
$pdf->addText($x1, $y, $fontsize,'Nama');
$pdf->addText($x2, $y, $fontsize,': ');
$pdf->addText($x3, $y, $fontsize,''.$lrow["nm_customer"]);
$y-=15;
$pdf->addText($x1, $y, $fontsize,'Alamat');
$pdf->addText($x2, $y, $fontsize,': ');
$pdf->addText($x3, $y, $fontsize,''.$lrow["alamat_ktp"]);
$y-=15;
$pdf->addText($x1, $y, $fontsize,'No. Polisi');
$pdf->addText($x2, $y, $fontsize,': ');
$pdf->addText($x3, $y, $fontsize,''.$lrow["no_polisi"]);
$y-=15;
$pdf->addText($x1, $y, $fontsize,'Merek');
$pdf->addText($x2, $y, $fontsize,': ');
$pdf->addText($x3, $y, $fontsize,''.$lrow["nm_merek"]);
$y-=15;
$pdf->addText($x1, $y, $fontsize,'Type');
$pdf->addText($x2, $y, $fontsize,': ');
$pdf->addText($x3, $y, $fontsize,''.$lrow["nm_tipe"]);
$y-=15;
$pdf->addText($x1, $y, $fontsize,'Chassis');
$pdf->addText($x2, $y, $fontsize,': ');
$pdf->addText($x3, $y, $fontsize,''.$lrow["no_rangka"]);
$y-=15;
$pdf->addText($x1, $y, $fontsize,'Engine');
$pdf->addText($x2, $y, $fontsize,': ');
$pdf->addText($x3, $y, $fontsize,''.$lrow["no_mesin"]);
$y-=15;
$pdf->addText($x1, $y, $fontsize,'Warna');
$pdf->addText($x2, $y, $fontsize,': ');
$pdf->addText($x3, $y, $fontsize,''.$lrow["warna"]);
$y-=15;
$pdf->addText($x1, $y, $fontsize,'Tahun');
$pdf->addText($x2, $y, $fontsize,': ');
$pdf->addText($x3, $y, $fontsize,''.$lrow["tahun"]);
$y-=15;
$pdf->addText($x1, $y, $fontsize,'Tgl Penyerahan');
$pdf->addText($x2, $y, $fontsize,': ');
$pdf->addText($x3, $y, $fontsize,''.($lrow["tgl_serah_terima_bpkb"]?date("d M Y",strtotime($lrow["tgl_serah_terima_bpkb"])):""));


//$pdf->selectFont('fonts/Times-Italic');
//$pdf->selectFont('fonts/Times');

$y-=22;
$pdf->ezSetY($y);

$i=1;
$data[$i]['no'] = $i;
$data[$i]['keterangan'] = 'BPKB';	
$data[$i]['no_surat'] = $lrow["no_bpkb"];
$data[$i]['tgl_surat'] = ($lrow["tgl_bpkb"]?date("d M Y",strtotime($lrow["tgl_bpkb"])):"");
$data[$i]['keterangan2'] = ($lrow["is_bpkb"]=='t'?"Ada":"Tidak Ada");

$i++;
$data[$i]['no'] = $i;
$data[$i]['keterangan'] = 'FAKTUR';	
$data[$i]['no_surat'] = $lrow["no_faktur"];
$data[$i]['tgl_surat'] = ($lrow["tgl_faktur"]?date("d M Y",strtotime($lrow["tgl_faktur"])):"");
$data[$i]['keterangan2'] = ($lrow["is_faktur"]=='t'?"Ada":"Tidak Ada");	
$i++;

$data[$i]['no'] = $i;
$data[$i]['keterangan'] = 'KWITANSI BERMATERAI DAN KOSONG NAMA BPKB';
$data[$i]['keterangan2'] = ($lrow["is_kwitansi_bpkb"]=='t'?"Ada":"Tidak Ada");		
$i++;

$data[$i]['no'] = $i;
$data[$i]['keterangan'] = 'FC KTP NAMA BPKB';	
$data[$i]['keterangan2'] = ($lrow["is_fc_ktp"]=='t'?"Ada":"Tidak Ada");
$i++;

$data[$i]['no'] = $i;
$data[$i]['keterangan'] = 'SERTIFIKAT NIK';	
$data[$i]['no_surat'] = $lrow["no_rangka"];
$data[$i]['keterangan2'] = ($lrow["is_sertifikat_nik"]=='t'?"Ada":"Tidak Ada");	
$i++;



$judul['no'] = 'No.';
$judul['keterangan'] = 'Keterangan';
$judul['no_surat'] = 'Nomor Surat';
$judul['tgl_surat'] = 'Tgl Surat';
$judul['keterangan2'] = 'Keterangan';



$lining['showHeadings'] = 1;
$lining['shaded'] = 0;
$lining['showLines'] =1 ;
$lining['xPos'] = 288;
$lining['fontSize'] = 8;

$lining['cols']['no']['justification'] = 'left';
$lining['cols']['keterangan']['justification'] = 'left';
$lining['cols']['no_surat']['justification'] = 'left';
$lining['cols']['tgl_surat']['justification'] = 'left';
$lining['cols']['no']['heading_justification'] = 'left';
$lining['cols']['keterangan']['heading_justification'] = 'left';
$lining['cols']['no_surat']['heading_justification'] = 'left';
$lining['cols']['tgl_surat']['heading_justification'] = 'left';



$size['no'] = '30';
$size['keterangan'] = '210';
$size['no_surat'] = '160';
$size['tgl_surat'] = '60';


//print_r($data);
$pdf->ezTable($data,$judul,'',$lining,$size);

$y=$pdf->y;
$y-=20;
$y-=20;

$bpkb_diperiksa=$lrow["bpkb_diperiksa"];
$bpkb_diserahkan=$lrow["bpkb_diserahkan"];

$pdf->addText($x1+7, $y, $fontsize,'Diperiksa oleh');
$pdf->addText($x1+205, $y, $fontsize,'Diserahkan oleh');
$pdf->addText($x1+410, $y, $fontsize,'Diterima oleh');
$y-=80;

$nm_karyawan1=get_karyawan_by_jabatan(strtoupper($bpkb_diperiksa),$_SESSION["kd_cabang"]);
$nm_karyawan2=get_karyawan_by_jabatan(strtoupper($bpkb_diserahkan),$_SESSION["kd_cabang"]);
if($lrow["nm_surat_kuasa"]){
	$penerima=$lrow["nm_surat_kuasa"];
}else{
	$penerima=$lrow["nm_customer"];
	if($lrow["nm_penerima"])$penerima=$lrow["nm_penerima"];
}


$pdf->addText($x1+7, $y, $fontsize,'('.$nm_karyawan1.')');
$pdf->addText($x1+205, $y, $fontsize,'('.$nm_karyawan2.')');
$pdf->addText($x1+410, $y, $fontsize,'('.$penerima.')');
$y-=10;

//217
//225
$l=strlen($bpkb_diserahkan);
$pdf->addText($x1+7, $y, $fontsize,$bpkb_diperiksa);
$pdf->addText($x1+205, $y, $fontsize,$bpkb_diserahkan);
$pdf->addText($x1+410, $y, $fontsize,'Customer');


$y-=10;

$pdf->line($x1-5,822,$x1-5,$y);
$pdf->line(555,822,555,$y);
$pdf->line($x1-5,$y,555,$y);


$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');
	
//end content
$pdf->ezStream();   

?>