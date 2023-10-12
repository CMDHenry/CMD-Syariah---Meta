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
select *,nm_partner as nm_dealer,tblpartner.alamat as alamat_dealer,tblcustomer.npwp as npwp_cust from data_gadai.tblproduk_cicilan 
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
left join tblpekerjaan on fk_pekerjaan=kd_pekerjaan
where no_sbg='".$id_edit."'
";				
$lrow=pg_fetch_array(pg_query($query));
//showquery($query);
//echo $lrow["fk_karyawan_kacab"];

$pdf = new Cezpdf('A4');  

$pdf->ez['topMargin'] = $heigth-$y_table;
$pdf->ez['bottomMargin']=130;

$all = $pdf->openObject();
$pdf->saveState();

$fontsize= 12;
$pdf->selectFont('fonts/Times');

$x1=150;
$y=800;

$x2=420;

//$pdf->addText($x1, $y, $fontsize,$lrow["nm_perusahaan"]);
$y-=50;
$pdf->selectFont('fonts/Times-Italic');
//$pdf->addText($x1, $y, $fontsize,convert_terbilang($lrow["nilai_ap_customer"]));
$pdf->selectFont('fonts/Times');
$y-=25;
//$y-=45;
$pdf->addText($x1, $y, $fontsize,'1 (SATU) UNIT '.strtoupper($lrow["nm_merek"]).' TYPE '.strtoupper($lrow["nm_tipe"]).'');
$y-=20;
$pdf->addText($x1, $y, $fontsize,'DENGAN NO CHASIS '.$lrow["no_rangka"] );
$y-=20;
$pdf->addText($x1, $y, $fontsize,'DAN NO ENGINE '.$lrow["no_mesin"]);
$y-=20;


//$pdf->addText($x2-200, $y, $fontsize,$lrow["nm_cabang"]);
//$pdf->addText($x2, $y, $fontsize,date("d/m/Y"));
//$pdf->addText($x2+100, $y, $fontsize,date("y"));

$y-=70;


//$pdf->addText($x1, $y, $fontsize,convert_money("",$lrow["nilai_ap_customer"]));
$y-=15;


$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');
	
//end content
$pdf->ezStream();   

?>