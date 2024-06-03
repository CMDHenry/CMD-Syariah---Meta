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

$fk_sbg = trim($_REQUEST['fk_sbg']);

$query="
select *,case 
when status='Terima' and fk_sbg_tebus is null then 'Normal'
when status='Terima' and fk_sbg_tebus is not null then 'Tebus'
when status='Tarik' then 'Tarik'
else status
end as status_inventory
from tblinventory  
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
left join(
	select distinct on (fk_sbg)fk_sbg as fk_sbg_tarik,tgl_data as tgl_tarik from data_gadai.tblhistory_sbg
	where transaksi='Tarik'	and tgl_batal is null
	order by fk_sbg,tgl_data desc
)as tarik1 on fk_sbg=fk_sbg_tarik
left join(
	select distinct on (fk_sbg)fk_sbg as fk_sbg_tebus,tgl_data from data_gadai.tblhistory_sbg
	where transaksi='Tebus'	and tgl_batal is null
	order by fk_sbg,tgl_data desc
)as tarik on fk_sbg=fk_sbg_tebus
left join (
	select distinct on (fk_sbg)tgl_jatuh_tempo as tgl_jt_terakhir,fk_sbg as fk_sbg1 from data_fa.tblangsuran 
	where tgl_bayar is null order by fk_sbg,angsuran_ke asc 
)as tblangsuran on fk_sbg=fk_sbg1
where fk_sbg = '".$fk_sbg."'";
$l_res=pg_query($query);	
//showquery($query);
$lrow=pg_fetch_array($l_res);

$rate_eff=(flat_eff($lrow["rate_flat"],$lrow["lama_pinjaman"],$lrow["addm_addb"]));

$pdf = new Cezpdf('F4','landscape');  
$pdf->setLineStyle(1);

$pdf->ez['topMargin'] = (10);
$pdf->ez['bottomMargin']=10;

/*$all = $pdf->openObject();
$pdf->saveState();
*/
//Header
$fontsize=11;
$start_y=570;
$start_z=450;
$x1 = 10;
$x2 = 100;
$x3=340;
$x4=440;
$x5=670;
$x6=770;

$x_right=70;

$pdf->selectFont('fonts/Times');
$nilai_aset=$lrow["total_nilai_pinjaman"]+$lrow["biaya_admin"]+$lrow["nilai_asuransi"]+$lrow["tjh_3"]+$lrow["pa_penumpang"]+$lrow["pa_supir"]+$lrow["biaya_polis"];

$pdf->addText($x1,$start_y, $fontsize,$lrow["nm_cabang"].'');
$start_y-=15;

$pdf->addText($x1,$start_y, $fontsize,'No. Akad');//(Kontrak)
$pdf->addText($x2,$start_y, $fontsize,': '.$fk_sbg.'');

$pdf->addText($x3,$start_y, $fontsize,'No. Order');
$pdf->addText($x4,$start_y, $fontsize,': '.$lrow["fk_fatg"].'');

$pdf->addText($x5,$start_y, $fontsize,'Nilai Aset');
$pdf->addText($x6,$start_y, $fontsize,': ');
$pdf->ezSetY($start_y+13);
$pdf->ezText(convert_money("",$nilai_aset),$fontsize,array('justification'=>'right','right'=>$x_right));

$start_y-=15;

$pdf->addText($x1,$start_y, $fontsize,'Nama');
$pdf->addText($x2,$start_y, $fontsize,': '.$lrow["nm_customer"].'');

$pdf->addText($x3,$start_y, $fontsize,'No. Kirim');
$pdf->addText($x4,$start_y, $fontsize,': '.$lrow[""].'');

$pdf->addText($x5,$start_y, $fontsize,'Uang Muka');
$pdf->addText($x6,$start_y, $fontsize,': ');
$pdf->ezSetY($start_y+13);
$pdf->ezText(convert_money("",$lrow["nilai_dp"]),$fontsize,array('justification'=>'right','right'=>$x_right));


$start_y-=15;

$pdf->addText($x1,$start_y, $fontsize,'Alamat');
$pdf->addText($x2,$start_y, $fontsize,': '.substr($lrow["alamat_tinggal"],0,32).'');

$pdf->addText($x3,$start_y, $fontsize,'Tgl. Kirim');
$pdf->addText($x4,$start_y, $fontsize,': '.($lrow["tgl_pengiriman_kontrak"]?date("d M Y",strtotime($lrow["tgl_pengiriman_kontrak"])):"").'');

$pdf->addText($x5,$start_y, $fontsize,'Piutang Pokok');
$pdf->addText($x6,$start_y, $fontsize,': ');
$pdf->ezSetY($start_y+13);
$pdf->ezText(convert_money("",$lrow["pokok_hutang"]),$fontsize,array('justification'=>'right','right'=>$x_right));

$start_y-=15;

$pdf->addText($x1,$start_y, $fontsize,'No Polisi');
$pdf->addText($x2,$start_y, $fontsize,': '.$lrow["no_polisi"].'');

$pdf->addText($x3,$start_y, $fontsize,'Tgl. Akad');
$pdf->addText($x4,$start_y, $fontsize,': '.date("d M Y",strtotime($lrow["tgl_cair"])).'');

$pdf->addText($x5,$start_y, $fontsize,'Margin');
$pdf->addText($x6,$start_y, $fontsize,': ');
$pdf->ezSetY($start_y+13);
$pdf->ezText(convert_money("",$lrow["biaya_penyimpanan"]),$fontsize,array('justification'=>'right','right'=>$x_right));

$start_y-=15;

$pdf->addText($x1,$start_y, $fontsize,'Tipe');
$pdf->addText($x2,$start_y, $fontsize,': '.$lrow["nm_tipe"].'');

$pdf->addText($x3,$start_y, $fontsize,'No BPKB');
$pdf->addText($x4,$start_y, $fontsize,': '.$lrow["no_bpkb"].'');

$pdf->addText($x5,$start_y, $fontsize,'S.B. Flat');
$pdf->addText($x6,$start_y, $fontsize,': ');
$pdf->ezSetY($start_y+13);
$pdf->ezText(substr($lrow["rate_flat"],0,10),$fontsize,array('justification'=>'right','right'=>$x_right));


$start_y-=15;

$pdf->addText($x1,$start_y, $fontsize,'No. Rangka');
$pdf->addText($x2,$start_y, $fontsize,': '.$lrow["no_rangka"].'');

$pdf->addText($x3,$start_y, $fontsize,'Terms');
$pdf->addText($x4,$start_y, $fontsize,': '.$lrow["lama_pinjaman"].'');

$pdf->addText($x5,$start_y, $fontsize,'S.B. Eff');
$pdf->addText($x6,$start_y, $fontsize,': ');
$pdf->ezSetY($start_y+13);
$pdf->ezText(substr($rate_eff,0,10),$fontsize,array('justification'=>'right','right'=>$x_right));

$start_y-=15;

$pdf->addText($x1,$start_y, $fontsize,'No. Mesin');
$pdf->addText($x2,$start_y, $fontsize,': '.$lrow["no_mesin"].'');

$pdf->addText($x3,$start_y, $fontsize,'Tgl Tagih');
$pdf->addText($x4,$start_y, $fontsize,': '.($lrow["tgl_jt_terakhir"]?date("d M Y",strtotime($lrow["tgl_jt_terakhir"])):"").'');

$pdf->addText($x5,$start_y, $fontsize,'Angsuran');
$pdf->addText($x6,$start_y, $fontsize,': ');
$pdf->ezSetY($start_y+13);
$pdf->ezText(convert_money("",$lrow["angsuran_bulan"]),$fontsize,array('justification'=>'right','right'=>$x_right));

$start_y-=15;

$pdf->addText($x1,$start_y, $fontsize,'Warna');
$pdf->addText($x2,$start_y, $fontsize,': '.$lrow["warna"].'');

$pdf->addText($x3,$start_y, $fontsize,'Supplier');
$pdf->addText($x4,$start_y, $fontsize,': '.$lrow["nm_partner"].'');

$pdf->addText($x5,$start_y, $fontsize,'Bayar');
$pdf->addText($x6,$start_y, $fontsize,': '.$lrow["addm_addb"].'');


/*
$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');
*/
$start_y-=10;

$pdf->ezSetY($start_y);

$data1[1]['no']  = 'No';
$data1[1]['angsuran_ke'] = 'Ang. Ke';
$data1[1]['tgl_jatuh_tempo'] = 'Tgl JTP';
$data1[1]['tgl_bayar'] = 'Tgl Bayar';
$data1[1]['od'] = 'OD';
$data1[1]['keterangan'] = 'Keterangan';
$data1[1]['no_kwitansi'] = 'No Bukti Bayar';
$data1[1]['denda_keterlambatan'] = "Ta'zir";
$data1[1]['denda_ganti_rugi'] = "Ta'widh";

$size['no']  = 25;
$size['angsuran_ke'] = 35;
$size['tgl_jatuh_tempo'] = 55;
$size['tgl_bayar'] = 55;
$size['od'] = 25;
$size['keterangan'] = 83;
$size['no_kwitansi'] = 80;;
$size['denda_keterlambatan'] = 46;
$size['denda_ganti_rugi'] = 46;

$lining1['showHeadings'] = 0;
$lining1['xPos'] =235;
$lining1['rowGap'] =10;


$pdf->ezTable($data1,$judul1,'',$lining1,$size);


$pdf->ezSetY($start_y);

$data2[1]['debet']  = 'Debet';
$data2[1]['kredit'] = 'Kredit';
$data2[1]['saldo_akhir'] = 'Saldo Akhir';

$size['debet']  = 120;
$size['kredit'] = 120;
$size['saldo_akhir'] = 180;

$lining2['showHeadings'] = 0;
$lining2['xPos'] =670;
//$lining2['rowGap'] =10;

$pdf->ezTable($data2,$judul1,'',$lining2,$size);

$data3[1]['pokok_d'] = "Pokok";
$data3[1]['bunga_d'] = "Margin";
$data3[1]['pokok_c'] = "Pokok";
$data3[1]['bunga_c'] = "Margin";
$data3[1]['pokok_akhir'] = "Pokok";
$data3[1]['bunga_akhir'] = "Margin";
$data3[1]['total_akhir'] = "Total";

$size['pokok_d'] = 60;
$size['bunga_d'] = 60;
$size['pokok_c'] = 60;
$size['bunga_c'] = 60;
$size['pokok_akhir'] = 60;
$size['bunga_akhir'] = 60;
$size['total_akhir'] = 60;

$lining3['showHeadings'] = 0;
$lining3['xPos'] =670;
$lining3['rowGap'] =8;

$pdf->ezTable($data3,$judul1,'',$lining3,$size);



$start_y-=45;

$pdf->ezSetY($start_y);

$query1="
select * from(
	--query ambil angsuran
	select angsuran_ke,tgl_jatuh_tempo,tgl_bayar,no_kwitansi,denda_keterlambatan,denda_ganti_rugi,saldo_pokok,saldo_bunga,pokok_jt,bunga_jt ,case when angsuran_ke =0 then 'Account Receivable' else 'Angs' end as keterangan,'1' as urutan from data_fa.tblangsuran	
	left join (
		select fk_sbg as fk_sbg1,no_kwitansi as no_kwitansi1,denda_keterlambatan,denda_ganti_rugi from data_fa.tblpembayaran_cicilan where tgl_batal is null and nilai_bayar_angsuran>0		
	)as tbl on fk_sbg=fk_sbg1 and no_kwitansi =no_kwitansi1
	where fk_sbg = '".$fk_sbg."' and tgl_bayar is not null and (no_kwitansi1 is not null or angsuran_ke=0 or (angsuran_ke=1 and bunga_jt=0))
		
	union --query denda
	select angsuran_ke,tgl_jatuh_tempo,tgl_bayar_denda,no_kwitansi1,0 as denda_keterlambatan,0 as denda_ganti_rugi,saldo_pokok,saldo_bunga,0,total_bayar_denda,'Denda','2' from data_fa.tblangsuran	
	inner join (
		select fk_sbg as fk_sbg1,no_kwitansi as no_kwitansi1,angsuran_ke as ang_ke,nilai_bayar_denda2+nilai_bayar_denda as total_bayar_denda,tgl_bayar as tgl_bayar_denda from data_fa.tblpembayaran_cicilan where tgl_batal is null and nilai_bayar_denda>0
	)as tbl on fk_sbg=fk_sbg1 and angsuran_ke = ang_ke	
	where fk_sbg = '".$fk_sbg."'
	
	union --query jual+lelang
	select distinct on (fk_sbg)angsuran_ke,tgl_jatuh_tempo,tgl_bayar,no_kwitansi,0,0,saldo_pokok,saldo_bunga,nominal as pokok_jt,0 as bunga_jt ,jenis_transaksi as keterangan,case when jenis_transaksi='Jual Cash' then '4' else '3' end from data_fa.tblangsuran	
	inner join (
		select fk_sbg as fk_sbg1,no_kwitansi as no_kwitansi1,jenis_transaksi,case when angka_lelang>0 then angka_lelang when nilai_claim>0 then nilai_claim end as nominal from data_gadai.tbllelang 
		where tgl_batal is null 
	)as tbl on fk_sbg=fk_sbg1 and no_kwitansi =no_kwitansi1
	where fk_sbg = '".$fk_sbg."' and tgl_bayar is not null 
	
	union --query alih ganti kontrak
	select distinct on (fk_sbg)angsuran_ke,tgl_jatuh_tempo,tgl_bayar,no_kwitansi,0,0,saldo_pokok,saldo_bunga,nominal as pokok_jt,0 as bunga_jt ,jenis_transaksi as keterangan,'4' from data_fa.tblangsuran	
	inner join (
		select fk_sbg_dp as fk_sbg1,no_kwitansi as no_kwitansi1,jenis as jenis_transaksi,total_pembayaran as nominal from data_fa.tblpenambahan_dp
		where tgl_batal is null 
	)as tbl on fk_sbg=fk_sbg1 and no_kwitansi =no_kwitansi1
	where fk_sbg = '".$fk_sbg."' and tgl_bayar is not null 
	
	
	--query pelunasan after jt
	
	union
	select angsuran_ke,tgl_jatuh_tempo,tgl_bayar,no_kwitansi,denda_keterlambatan,denda_ganti_rugi,saldo_pokok,(saldo_bunga-bunga_pelunasan)*-1,pokok_jt,bunga_pelunasan as bunga_jt ,'Pelunasan' as keterangan,'5' as urutan from(
		select tgl_bayar,fk_sbg as fk_sbg,no_kwitansi,nilai_bayar_denda2 as denda_keterlambatan,nilai_bayar_denda as denda_ganti_rugi,pinalti+bunga_berjalan as bunga_pelunasan from data_fa.tblpelunasan_cicilan where tgl_batal is null
	)as tblmain
	inner join(
		select sum(pokok_jt)as pokok_jt,sum(bunga_jt)as bunga_jt,fk_sbg as fk_sbg1,no_kwitansi as no_kwitansi1,0 as angsuran_ke,min(tgl_jatuh_tempo)as tgl_jatuh_tempo,0 as saldo_pokok,sum(bunga_jt)as saldo_bunga from data_fa.tblangsuran 
		where no_kwitansi is not null and tgl_bayar<tgl_jatuh_tempo
		group by fk_sbg,no_kwitansi 
	)as tblangs on fk_sbg =fk_sbg1 and no_kwitansi=no_kwitansi1
	where fk_sbg = '".$fk_sbg."'
	
	--query pelunasan tunggakan
	union
	select angsuran_ke,tgl_jatuh_tempo,tgl_bayar,no_kwitansi,0,0,saldo_pokok,saldo_bunga*-1,pokok_jt,bunga_jt ,'Tunggakan' as keterangan,'6' as urutan from(
		select tgl_bayar,fk_sbg as fk_sbg,no_kwitansi,nilai_bayar_denda2 as denda_keterlambatan,nilai_bayar_denda as denda_ganti_rugi from data_fa.tblpelunasan_cicilan where tgl_batal is null
	)as tblmain
	inner join(
		select sum(pokok_jt)as pokok_jt,sum(bunga_jt)as bunga_jt,fk_sbg as fk_sbg1,no_kwitansi as no_kwitansi1,max(angsuran_ke)as angsuran_ke,min(tgl_jatuh_tempo)as tgl_jatuh_tempo,0 as saldo_pokok,sum(bunga_jt)as saldo_bunga from data_fa.tblangsuran 
		where no_kwitansi is not null and tgl_bayar>tgl_jatuh_tempo
		group by fk_sbg,no_kwitansi 
	)as tblangs on fk_sbg =fk_sbg1 and no_kwitansi=no_kwitansi1
	where fk_sbg = '".$fk_sbg."'
	
	--query pelunasan denda
	union
	select angsuran_ke,NULL,tgl_bayar,no_kwitansi,0,0,0,0,0,denda_keterlambatan+denda_ganti_rugi as bunga_jt ,'Denda' as keterangan,'7' as urutan from(
		select tgl_bayar,fk_sbg as fk_sbg,no_kwitansi,nilai_bayar_denda2 as denda_keterlambatan,nilai_bayar_denda as denda_ganti_rugi from data_fa.tblpelunasan_cicilan where tgl_batal is null
	)as tblmain
	left join(
		select min(angsuran_ke)as angsuran_ke,fk_sbg as fk_sbg1, no_kwitansi as no_kwitansi1 from data_fa.tblangsuran 
		where no_kwitansi is not null 
		group by fk_sbg,no_kwitansi 
	)as tblangs on fk_sbg =fk_sbg1 and no_kwitansi=no_kwitansi1
	where fk_sbg = '".$fk_sbg."'
	
)as tblmain
order by tgl_bayar,urutan,angsuran_ke
";
$l_res1=pg_query($query1);	
//showquery($query1);
		
$i = 1;
while($lrow1=pg_fetch_array($l_res1)){
	$tgl_jatuh_tempo = date('d/m/Y',strtotime($lrow1['tgl_jatuh_tempo']));
	
	$tgl_bayar = ($lrow1["tgl_bayar"]==""?"":date("d/m/Y",strtotime($lrow1["tgl_bayar"])));
	$od=(strtotime($lrow1['tgl_bayar'])-strtotime($lrow1['tgl_jatuh_tempo']))/(60*60*24);
	if($od<=0)$od=0;
	$keterangan=$lrow1["keterangan"];
	if($lrow1["keterangan"]=='Denda' || $lrow1["angsuran_ke"]==0){
		$tgl_jatuh_tempo="";
		$od="";
	}
	
	if($keterangan=='Jual Credit'|| $keterangan=='Ganti CIF'){
		$kontrak_baru=get_rec("data_gadai.tbltaksir_umum left join data_gadai.tblproduk_cicilan on fk_fatg=no_fatg","no_sbg","no_sbg_lama='".$fk_sbg."'");
		//echo $fk_sbg;
		$keterangan='Alih Kredit ke '.$kontrak_baru;
	}
	
	
	$data[$i]['no'] = $i;
	if(!$lrow1["angsuran_ke"])$lrow1["angsuran_ke"]=0;
	$data[$i]['angsuran_ke'] = $lrow1["angsuran_ke"]."/".$lrow["lama_pinjaman"];
	$data[$i]['tgl_jatuh_tempo'] = $tgl_jatuh_tempo;	
	$data[$i]['tgl_bayar'] = $tgl_bayar;	
	$data[$i]['od'] = $od;	
	$data[$i]['keterangan'] = $keterangan;	
	$data[$i]['no_kwitansi'] = $lrow1["no_kwitansi"];	
	$data[$i]['denda_keterlambatan'] = convert_money("",$lrow1["denda_keterlambatan"]);	
	$data[$i]['denda_ganti_rugi'] = convert_money("",$lrow1["denda_ganti_rugi"]);	
	$pokok_d=$bunga_d=$pokok_c=$bunga_c=0;
	
	if($lrow1["keterangan"]=='Account Receivable' ){
		$pokok_d=$lrow1["saldo_pokok"];
		$bunga_d=$lrow1["saldo_bunga"];
	}else{
		$pokok_c=$lrow1["pokok_jt"];
		$bunga_c=$lrow1["bunga_jt"];
		if($lrow1["keterangan"]=='Denda'){//denda
			$bunga_d=$bunga_c;
		}
		if($lrow1["urutan"]=='5'){//5 buat pelunasan
			$bunga_d=$lrow1["saldo_bunga"];
		}
		//echo $pokok_c.'aa<br>';
	}	
	
	
	if($lrow1["urutan"]=='3'){//jual selain cash
		$pokok_d=($total_pokok_c+$pokok_c)-$total_pokok_d;
		$bunga_d=($total_bunga_c+$bunga_c)-$total_bunga_d;
	}
	//echo $pokok_d.'<br>';
	if($lrow1["urutan"]=='4'){//jual cash
		//$bunga_c=($total_pokok_c+$pokok_c)-$total_pokok_d;
		//$pokok_c-=$bunga_c;
		$pokok_d=($total_pokok_c+$pokok_c)-$total_pokok_d;		
		$bunga_d=($total_bunga_c+$bunga_c)-$total_bunga_d;
		
		//echo $total_pokok_d.'='.$pokok_c.'='.$total_pokok_c;
		$flag_denda='t';
	}
	
	
	$pokok_akhir+=($pokok_d-$pokok_c);
	$bunga_akhir+=($bunga_d-$bunga_c);	
	$total_akhir=$pokok_akhir+$bunga_akhir;
	
	$total_pokok_d+=$pokok_d;
	$total_bunga_d+=$bunga_d;
	$total_pokok_c+=$pokok_c;
	$total_bunga_c+=$bunga_c;	
	
	$data[$i]['pokok_d'] = convert_money("",$pokok_d);	
	$data[$i]['bunga_d'] = convert_money("",$bunga_d);
	$data[$i]['pokok_c'] = convert_money("",$pokok_c);
	$data[$i]['bunga_c'] = convert_money("",$bunga_c);
	$data[$i]['pokok_akhir'] = convert_money("",$pokok_akhir);
	$data[$i]['bunga_akhir'] = convert_money("",$bunga_akhir);
	$data[$i]['total_akhir'] = convert_money("",$total_akhir);
	$i++;
	
}
	
$denda=get_rec("data_fa.tbldenda","saldo_denda","fk_sbg='".$fk_sbg."'");
//echo $denda.'a';
if($denda>0 && $flag_denda=='t'){
	$pokok_d=$pokok_c=$denda*-1;
	$bunga_d=$bunga_c=$denda;
	
	$data[$i]['no'] = $i;
	$data[$i]['angsuran_ke'] = $lrow1["angsuran_ke"]."/".$lrow["lama_pinjaman"];
	$data[$i]['tgl_jatuh_tempo'] = $tgl_jatuh_tempo;	
	$data[$i]['tgl_bayar'] = $tgl_bayar;	
	$data[$i]['keterangan'] = 'Diskon Denda';	
	
	$total_pokok_d+=$pokok_d;
	$total_bunga_d+=$bunga_d;
	$total_pokok_c+=$pokok_c;
	$total_bunga_c+=$bunga_c;	
	
	$data[$i]['pokok_d'] = convert_money("",$pokok_d);	
	$data[$i]['bunga_d'] = convert_money("",$bunga_d);
	$data[$i]['pokok_c'] = convert_money("",$pokok_c);
	$data[$i]['bunga_c'] = convert_money("",$bunga_c);
	$data[$i]['pokok_akhir'] = convert_money("",$pokok_akhir);
	$data[$i]['bunga_akhir'] = convert_money("",$bunga_akhir);
	$data[$i]['total_akhir'] = convert_money("",$total_akhir);
	$i++;
}


$data[$i]['keterangan'] = 'T O T A L';	

$data[$i]['pokok_d'] = convert_money("",$total_pokok_d);	
$data[$i]['bunga_d'] = convert_money("",$total_bunga_d);
$data[$i]['pokok_c'] = convert_money("",$total_pokok_c);
$data[$i]['bunga_c'] = convert_money("",$total_bunga_c);
$i++;


$judul['no'] = 'No';
$judul['angsuran_ke'] = 'Ang.';
$judul['tgl_jatuh_tempo'] = 'Tgl Jt Tempo';
$judul['tgl_bayar'] = 'Tgl Bayar';
$judul['od'] = 'OD';
$judul['keterangan'] = 'Keterangan';
$judul['no_kwitansi'] = 'Nomor Bukti Bayar';
$judul['denda_keterlambatan'] = "Ta'zir";
$judul['denda_ganti_rugi'] = "Ta'widh";

$judul['pokok_d'] = "-";
$judul['bunga_d'] = "-";
$judul['pokok_c'] = "-";
$judul['bunga_c'] = "-";
$judul['pokok_akhir'] = "-";
$judul['bunga_akhir'] = "-";
$judul['total_akhir'] = "-";

$lining['showHeadings'] = 0;
$lining['shaded'] = 0;
$lining['showLines'] = 2;
$lining['xPos'] =445;
$lining['fontSize'] =8;

$lining['cols']['denda_keterlambatan']['justification'] = 'right';
$lining['cols']['denda_ganti_rugi']['justification'] = 'right';

$lining['cols']['pokok_d']['justification'] = 'right';
$lining['cols']['bunga_d']['justification'] = 'right';
$lining['cols']['pokok_c']['justification'] = 'right';
$lining['cols']['bunga_c']['justification'] = 'right';
$lining['cols']['pokok_akhir']['justification'] = 'right';
$lining['cols']['bunga_akhir']['justification'] = 'right';
$lining['cols']['total_akhir']['justification'] = 'right';
	
$pdf->ezTable($data,$judul,'',$lining,$size);

$start_y=$pdf->y;
$pdf->addText($x6+45,$start_y-15, $fontsize,'('.$lrow["status_inventory"].')');

$queryFunding="
select * from data_fa.tblfunding_detail
left join data_fa.tblfunding on data_fa.tblfunding.no_funding = data_fa.tblfunding_detail.fk_funding
where fk_sbg='".$fk_sbg."' order by tgl_funding desc
";
$l_res_funding=pg_fetch_array(pg_query($queryFunding));

$start_y=$pdf->y;
$start_y-=40;
$pdf->addText($x1,$start_y, $fontsize,'Keterangan');
if($l_res_funding['tgl_unpledging'] != null || empty($l_res_funding)){
	$pdf->addText($x2,$start_y, $fontsize,': ');
}else{
	$pdf->addText($x2,$start_y, $fontsize,': Funding di '. str_replace("FUNDING-","BANK ",$l_res_funding['fk_partner']) . ' per tanggal ' . date("d F Y",strtotime(substr($l_res_funding['tgl_funding'],0,10))));
}
$start_y-=15;

$l_res=pg_query($queryFunding);
if (!empty($l_res_funding)) {
	$pdf->addText($x1,$start_y, $fontsize,'History Funding');
	$start_y-=15;
    $pdf->addText($x1, $start_y, $fontsize, 'No Funding');	
    $pdf->addText($x1+100, $start_y, $fontsize, ' | Bank Funding ');
    $pdf->addText($x1+320, $start_y, $fontsize, ' | Tanggal Limpah ');
    $pdf->addText($x1+470, $start_y, $fontsize, ' | Tanggal Cabut Limpah');
    $start_y -= 15;

    while ($lrow = pg_fetch_array($l_res)) {
        $tgl_unpledging = $lrow['tgl_unpledging'] ? date("d F Y", strtotime(substr($lrow['tgl_unpledging'], 0, 10))) : "-";
        $pdf->addText($x1, $start_y, $fontsize, $lrow['no_funding']);
        $pdf->addText($x1+100, $start_y, $fontsize, ' | ' . str_replace("FUNDING-", "BANK ", $lrow['fk_partner']));
        $pdf->addText($x1+320, $start_y, $fontsize, ' | ' . date("d F Y", strtotime(substr($lrow['tgl_funding'], 0, 10))));
        $pdf->addText($x1+470, $start_y, $fontsize, ' | ' . $tgl_unpledging);
        $start_y -= 15;
    }
}

$query="
select * from data_gadai.tblhistory_sbg
left join data_gadai.tbllelang on data_gadai.tbllelang.no_kwitansi = data_gadai.tblhistory_sbg.referensi
left join data_gadai.tblproduk_cicilan on data_gadai.tblproduk_cicilan.no_sbg = data_gadai.tblhistory_sbg.fk_sbg
left join tblpartner on data_gadai.tblproduk_cicilan.fk_partner_asuransi = kd_partner
where transaksi in('Tarik','Tebus','Lelang','Jual Cash','Jual Kredit','Claim Asuransi') and data_gadai.tblhistory_sbg.tgl_batal is null
and data_gadai.tblhistory_sbg.fk_sbg='".$fk_sbg."' order by no
";
$l_res=pg_query($query);	
while($lrow=pg_fetch_array($l_res)){
	$pdf->addText($x1,$start_y, $fontsize,''.$lrow['transaksi']);
	if($lrow['transaksi']=='Tarik' || $lrow['transaksi']=='Tebus'){
		$pdf->addText($x1+90,$start_y, $fontsize,' Tgl '.date("d F Y",strtotime($lrow["tgl_data"])));
	} elseif($lrow['transaksi']=='Claim Asuransi') {
		$pdf->addText($x1+90, $start_y, $fontsize,' pada ' . date("d F Y", strtotime($lrow["tgl_lelang"])) . ' dengan no kwitansi ' . $lrow['no_kwitansi'] . ' sebesar ' . convert_money("",$lrow['angka_lelang'] + $lrow['nilai_claim']) . ' ('.$lrow['nm_partner'].')');
	}else {
		$pdf->addText($x1+90, $start_y, $fontsize,' pada ' . date("d F Y", strtotime($lrow["tgl_lelang"])) . ' dengan no kwitansi ' . $lrow['no_kwitansi'] . ' sebesar ' . convert_money("",$lrow['angka_lelang'] + $lrow['nilai_claim']) . ' ('.$lrow['nm_penerima'].')');
	}
	$start_y-=15;
}

$arr=explode(PHP_EOL,$lrow["keterangan_tarik"]);
$count=count($arr);
for($i=0; $i<$count; $i++){
	$pdf->addText($x1,$start_y, $fontsize,''.$arr[$i].'');
	$start_y-=15;
}

$pdf->addText($x1,$start_y, $fontsize,'Biaya');
$pdf->addText($x2,$start_y, $fontsize,': ');
$start_y-=15;

$query="
select * from data_fa.tblrekon_bank left join data_fa.tblrekon_bank_detail on no_voucher=fk_voucher where status_data='Approve' and tgl_batal is null and no_kontrak='".$fk_sbg."' order by tgl_voucher
";
$l_res=pg_query($query);	
while($lrow=pg_fetch_array($l_res)){
	$pdf->addText($x1,$start_y, $fontsize,''.$lrow['catatan'].' : '.convert_money("",$lrow["nominal"]));
	$start_y-=15;
}



$start_y=$pdf->y;
$start_y-=20;
$pdf->addText($x1,$start_y, $fontsize,'PrintTime');
$pdf->addText($x2,$start_y, $fontsize,': '.date("d/m/Y H:i"));

$start_y=$pdf->y;
$start_y-=30;
$pdf->addText($x1,$start_y, $fontsize,'User');
$pdf->addText($x2,$start_y, $fontsize,': '.$_SESSION['username'].'');

$pdf->ezStream();

 
/*$pdfcode = $pdf->ezOutput();
$fp=fopen($_SERVER['DOCUMENT_ROOT'].'/capella/website/print/test.pdf','wb');
fwrite($fp,$pdfcode);
fclose($fp);
*/
?>