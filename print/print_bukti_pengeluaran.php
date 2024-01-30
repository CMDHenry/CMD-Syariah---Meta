<?php
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/timestamp.inc.php';
require '../classes/ezpdf.class.php';
require '../requires/convert.inc.php';
require '../requires/numeric.inc.php';


$id_edit = $_REQUEST['id_edit'];//='2102.PMR.0000003';


$query="
select case when fk_bank in('01','02') then 'KAS' else 'BANK' end as jenis,*,alamat from ( 
	select no_voucher,total,keterangan,fk_bank,fk_cabang,tgl_voucher,'' as fk_cek,null::timestamp as tgl_jatuh_tempo,'' as transaksi,'' as bulan,'' as tahun from data_fa.tblpetty_cash
	union
	select no_voucher,total,keterangan,fk_bank_keluar,fk_cabang,tgl_voucher,'',null,'','','' from data_fa.tblmutasi_bank
	union
	select no_voucher,total*-1,keterangan,fk_bank,fk_cabang,tgl_voucher,fk_cek,tgl_jatuh_tempo,'','',''  from data_fa.tblrekon_bank
	union 
	select no_batch,total,nm_partner,fk_bank,'',tgl_batch,'',NULL,transaksi,bulan,tahun from data_fa.tblbatch_payment
	left join(
		select kd_partner,nm_partner from tblpartner
		union all
		select no_cif,nm_customer from tblcustomer
	)as tblpartner on kd_partner=fk_partner
	left join(
		select referensi,sum(nilai_bayar) as total,transaksi from data_gadai.tblhistory_sbg
		where tgl_batal is null 
		group by referensi,transaksi
	)as tblhistori on no_batch=referensi
	union 
	select no_voucher,total,nm_partner,fk_bank_ho,fk_cabang_ho,tgl_voucher,NULL,NULL,'','',''  from data_fa.tblpayment_request
	left join(
		select kd_partner,nm_partner from tblpartner
	)as tblpartner on kd_partner=fk_partner

)as tblmain
left join tblbank on fk_bank =kd_bank
left join tblcabang on fk_cabang=kd_cabang
where no_voucher='".$id_edit."'
";				
$lrow=pg_fetch_array(pg_query($query));
//showquery($query);
//echo $lrow["fk_karyawan_kacab"];
$total=$lrow['total'];

$pdf = new Cezpdf('A4','');  

$pdf->ez['topMargin'] = $heigth-$y_table;
$pdf->ez['bottomMargin']=130;

$all = $pdf->openObject();
$pdf->saveState();

$fontsize= 8;
$pdf->selectFont('fonts/Times');

$x1=40;
$y=810;

$x2=420;
$x3=420;

$pdf->ezImage('../print/logo.jpeg','','180','','left','');
//$pdf->addText(210, $y, $fontsize+2,''.strtoupper($lrow["nm_perusahaan"]));

$pdf->addText($x3, $y, $fontsize,'Tanggal       : '.date("d/m/Y",strtotime($lrow["tgl_voucher"])));
$y-=12;
$pdf->addText($x3, $y, $fontsize,'No Voucher : '.$id_edit);
$y-=12;
$pdf->line($x1,$y,555,$y);
$y-=12;

if($lrow["transaksi"]=='Pencairan Datun'){
	$lrow["keterangan"]=get_rec("data_gadai.tblhistory_sbg left join tblinventory on tblinventory.fk_sbg=tblhistory_sbg.fk_sbg left join tblcustomer on fk_cif=no_cif","nm_customer","referensi='".$id_edit."'");
}

if($total>0){
	$pdf->addText(220, $y, $fontsize+1,'BUKTI PENGELUARAN '.$lrow["jenis"]);
	$y-=18;
	if($lrow["jenis"]=='BANK'){
		$pdf->addText($x1, $y, $fontsize,''.$lrow["nm_bank"]);
		$y-=12;
	}
	$pdf->addText($x1, $y, $fontsize,'Bayar Kepada : '.$lrow["keterangan"]);
	
}else{
	$pdf->addText(220, $y, $fontsize+1,'BUKTI PENERIMAAN '.$lrow["jenis"]);
	$y-=18;
	$pdf->addText($x1, $y, $fontsize,'Diterima Oleh : '.$lrow["keterangan"]);	
}
$y-=12;

$terbilang=($lrow["total"]<0?$lrow["total"]*-1:$lrow["total"]);
$pdf->addText($x1, $y, $fontsize,'Jumlah Uang : '.convert_money("Rp",$terbilang)."   #".convert_terbilang($terbilang)." #");
$y-=12;

//$pdf->selectFont('fonts/Times-Italic');
$pdf->ezSetY($y);

$transaksi=$lrow["transaksi"];
$asr=array();
if($transaksi){

	$query="select * from data_gadai.tblhistory_sbg 
	left join(
		select fk_sbg as no_sbg, nm_customer, nm_produk,status_barang from tblinventory left join tblcustomer on fk_cif=no_cif
		left join tblproduk on fk_produk=kd_produk
	)as tblinventory on no_sbg=fk_sbg
	where referensi='".$id_edit."'
	order by fk_sbg desc
	";
	$l_res1=pg_query($query);	
	while($lrow1=pg_fetch_array($l_res1)){
		
		if(!strstr($transaksi,"Asuransi")){//asuransi notes digabung
			if(strstr(strtoupper($lrow1['nm_produk']),"KONVERSI") && $lrow1["status_barang"]=='Datun'){
				$transaksi='Pelunasan Konversi';
			}
			$lbatch.="
			union all
			select '".$id_edit."','".$lrow1["nilai_bayar"]."','".$transaksi." a/n ".str_replace("'","''",$lrow1["nm_customer"])."','1','-'
			";
		}else{
			$trx=$lrow1["transaksi"];
			$asr[$trx]['qty']+=1;
			$asr[$trx]['nominal']+=$lrow1["nilai_bayar"];
		}
	}
	if(count($asr)>0){
		foreach($asr as $catatan =>$temp){
			$catatan=str_replace("Asuransi","Asuransi Tahun",$catatan);
			$periode='untuk '. getMonthName($lrow["bulan"]).' '.$lrow["tahun"];
;
			$lbatch.="
			union all
			select '".$id_edit."','".$temp["nominal"]."','".$catatan." ".$periode." ".$temp["qty"]." unit','1','-'
			";
		}
	}
	
}

$query="
select * from(
	select fk_voucher,nominal,catatan, pk_id_detail,fk_coa from data_fa.tblpetty_cash_detail
	union all
	select fk_voucher,case when type_tr='Masuk' then nominal else nominal end ,catatan, pk_id_detail,fk_coa from data_fa.tblrekon_bank_detail
	union
	select fk_voucher,nominal,catatan, pk_id_detail,fk_coa from data_fa.tblpayment_request_detail
	".$lbatch."
)as tbl	
where fk_voucher = '".$id_edit."'";
//showquery($query);
$l_res1=pg_query($query);	
$i = 1;
while($lrow1=pg_fetch_array($l_res1)){
	$data[$i]['fk_coa'] = $lrow1["fk_coa"];
	if (strpos($$lrow1["catatan"], "Datun") !== false) {
		// Replace "Datun" with "Akad Murabahah"
		$$lrow1["catatan"] = str_replace("Datun", "Akad Murabahah", $$lrow1["catatan"]);
	}
	$data[$i]['catatan'] = $lrow1["catatan"];	
	$data[$i]['nominal'] = convert_money("",$lrow1["nominal"]);	
	$i++;
}



$judul['fk_coa'] = 'Kode';
$judul['catatan'] = 'Keterangan';
$judul['nominal'] = 'Jumlah';

$lining['showHeadings'] = 1;
$lining['shaded'] = 0;
$lining['showLines'] =4 ;
$lining['xPos'] = 295;
$lining['fontSize'] = $fontsize;

$lining['cols']['fk_coa']['heading_justification'] = 'left';
$lining['cols']['catatan']['heading_justification'] = 'left';
$lining['cols']['nominal']['heading_justification'] = 'right';

$lining['cols']['fk_coa']['justification'] = 'left';
$lining['cols']['catatan']['justification'] = 'left';
$lining['cols']['nominal']['justification'] = 'right';


$size['fk_coa'] = '100';
$size['catatan'] = '300';
$size['nominal'] = '115';

//print_r($data);
$pdf->ezTable($data,$judul,'',$lining,$size);
$y=$pdf->y;
$y-=5;
$pdf->line($x1,$y,555,$y);

$pdf->ezSetY($y);
$data=array();
$lining['showHeadings'] = 0;
$lining['shaded'] = 0;
$lining['showLines'] =0 ;
$lining['xPos'] = 295;


$data[$i]['fk_coa'] = '';
$data[$i]['catatan'] = 'TOTAL';	
$data[$i]['nominal'] = convert_money("",$total);	
$i++;
$pdf->ezTable($data,$judul,'',$lining,$size);



$y-=20;
if($lrow["jenis"]=='BANK' && $lrow["fk_cek"]){
	$pdf->addText($x1, $y, $fontsize,'Cek/Giro : Bank '.$lrow["nm_bank"].' No '.$lrow["fk_cek"].' Tgl/JT '.($lrow["tgl_jatuh_tempo"]?date("d M Y",strtotime($lrow["tgl_jatuh_tempo"])):"").'');
}


$y-=20;
$pdf->addText($x1, $y, $fontsize,'Disetujui oleh');
$pdf->addText($x1+140, $y, $fontsize,'Diperiksa oleh');
$pdf->addText($x1+280, $y, $fontsize,'Dibuat oleh');
$pdf->addText($x1+400, $y, $fontsize,'Diterima oleh');
$y-=40;

$pdf->addText($x1, $y, $fontsize,'(                           )');
$pdf->addText($x1+140, $y, $fontsize,'(                           )');
$pdf->addText($x1+280, $y, $fontsize,'(                           )');
$pdf->addText($x1+400, $y, $fontsize,'(                           )');


$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');
	
//end content
$pdf->ezStream();   



/*$max_i=6;
if($lrow["jenis"]=='Bank')$max_i=4;

while($i<$max_i){
	$data[$i]['nominal'] = '';	
	$i++;
	
	if($i>10000)break;
}*/

/*$data[$i]['nominal'] = convert_money("",$lrow["total"]);
$i++;
*/

?>