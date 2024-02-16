<?php
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/timestamp.inc.php';
require '../classes/ezpdf.class.php';
require '../requires/convert.inc.php';
require '../requires/numeric.inc.php';

$tgl = convert_date_english($_REQUEST['tgl']);
$fk_cabang = $_REQUEST['fk_cabang'];

$query="
select * from tblcabang where kd_cabang='".$fk_cabang."'
";				
$lrow=pg_fetch_array(pg_query($query));
//showquery($query);

$pdf = new Cezpdf('A4','');  
$pdf->ez['topMargin'] = $heigth-$y_table;
$pdf->ez['bottomMargin']=130;

$all = $pdf->openObject();
$pdf->saveState();

$fontsize= 9;
$pdf->selectFont('fonts/Times');

$x1=40;
$y=780;
$x2=420;
$x3=420;

$pdf->ezImage('../print/logo.jpeg','','180','','left','');
$pdf->addText($x1+35, $y+3, $fontsize,'Cab. '.$lrow["nm_cabang"]);

$pdf->ezSetY($y);

$bulan=date("m",strtotime($tgl));
$tahun=date("Y",strtotime($tgl));
if($bulan==1){
	$l_year=$tahun-1;
	$l_month=12;
}else{
	$l_year=$tahun;
	$l_month=$bulan-1;
}

$fk_coa_01=get_rec("tblcabang_detail_bank","fk_coa","fk_cabang='".$fk_cabang."' and fk_bank='01'");
$fk_coa_02=get_rec("tblcabang_detail_bank","fk_coa","fk_cabang='".$fk_cabang."' and fk_bank='02'");

if($fk_coa_01){
	$query_01="
	select sum(balance_cash+balance_bank+balance_memorial+balance_gl_auto) as saldo_awal from data_accounting.tblsaldo_coa
	where tr_month=".$l_month." and tr_year=".$l_year." and fk_coa like '".$fk_cabang.'%'.$fk_coa_01."' ";
	$lrow_saldo_01=pg_fetch_array(pg_query($query_01));
	//showquery($query_01);
	$saldo_awal+=$lrow_saldo_01["saldo_awal"];
	$saldo_awal_01=$lrow_saldo_01["saldo_awal"];
}

if($fk_coa_02){
	$lrow_saldo_02=pg_fetch_array(pg_query("
	select sum(balance_cash+balance_bank+balance_memorial+balance_gl_auto) as saldo_awal from data_accounting.tblsaldo_coa
	where tr_month=".$l_month." and tr_year=".$l_year." and fk_coa like '".$fk_cabang.'%'.$fk_coa_02."' "));
	$saldo_awal+=$lrow_saldo_02["saldo_awal"];
	$saldo_awal_02=$lrow_saldo_02["saldo_awal"];
}

//echo $saldo_awal.'<br>';
$periode_awal=$tgl;
$total_01=get_saldo_coa_harian($fk_coa_01);
$total_02=get_saldo_coa_harian($fk_coa_02);

$saldo_awal+=$total_01+$total_02;
$saldo_akhir=$saldo_awal;


$query="
select 
sum(case when cara_bayar='Cash' and ket='ANG' and kategori='R2' then nilai_bayar end ) as tunai_motor,
sum(case when cara_bayar='Cash' and ket='ANG' and kategori='R4' then nilai_bayar end) as tunai_mobil,
sum(case when cara_bayar='Collector' and ket='ANG' and kategori='R2' then nilai_bayar end) as col_motor,
sum(case when cara_bayar='Collector' and ket='ANG' and kategori='R4' then nilai_bayar end) as col_mobil,
sum(case when ket='DEN' and kategori='R2' then nilai_bayar end )as denda_motor,
sum(case when ket='DEN' and kategori='R4' then nilai_bayar end )as denda_mobil,
sum(case when cara_bayar not in('Cash','Collector') then nilai_bayar end ) as non_tunai
from (
	select * from(
		select *,case when fk_cabang_input is not null then fk_cabang_input else fk_cabang end as fk_cabang_data from(
			select fk_cabang_input,'ANG' as ket,nilai_bayar_angsuran as nilai_bayar,cara_bayar,fk_sbg,no_kwitansi from data_fa.tblpembayaran_cicilan
			where nilai_bayar_angsuran > 0 and tgl_batal is null and tgl_input='".$tgl."'
			union all
			select fk_cabang_input,'DEN' as ket,nilai_bayar_denda+nilai_bayar_denda2 as nilai_bayar,cara_bayar,fk_sbg,no_kwitansi from data_fa.tblpembayaran_cicilan
			where nilai_bayar_denda+nilai_bayar_denda2 > 0 and tgl_batal is null and tgl_input='".$tgl."'
			union all
			select fk_cabang_input,'ANG' as ket,sisa_angsuran+sisa_pokok+bunga_berjalan+(total_pembayaran-total_pelunasan)-diskon_pelunasan as nilai_bayar,cara_bayar,fk_sbg,no_kwitansi from data_fa.tblpelunasan_cicilan
			where sisa_angsuran > 0 and tgl_batal is null and tgl_bayar='".$tgl."'
			union all
			select fk_cabang_input,'DEN' as ket,nilai_bayar_denda+nilai_bayar_denda2-diskon_pelunasan as nilai_bayar,cara_bayar,fk_sbg,no_kwitansi from data_fa.tblpelunasan_cicilan
			where nilai_bayar_denda+nilai_bayar_denda2 > 0 and tgl_batal is null and tgl_bayar='".$tgl."'
		)as tblmain1
		inner join (
			select tblinventory.fk_sbg as fk_sbg_inventory,kategori,fk_cabang from tblinventory 
			left join data_gadai.tblproduk_cicilan on fk_sbg=no_sbg
			left join viewkendaraan on fk_fatg=no_fatg
		) as tblinventory on fk_sbg_inventory=fk_sbg		
	)as tblmain1 where fk_cabang_data='".$fk_cabang."'
) as tblmain";
$lrow_penerimaan=pg_fetch_array(pg_query($query));
//showquery($query);
$angsuran_tunai=$lrow_penerimaan["tunai_motor"]+$lrow_penerimaan["tunai_mobil"];
$collector=$lrow_penerimaan["col_motor"]+$lrow_penerimaan["col_mobil"];
$denda=$lrow_penerimaan["denda_motor"]+$lrow_penerimaan["denda_mobil"];
$non_tunai=$lrow_penerimaan["non_tunai"];// khusus di testing. harusnya di live ga ada ini
$total_penerimaan+=($angsuran_tunai+$collector+$denda+$non_tunai);
						
$queyy_tagih="
select sum(biaya_tagih) as total_tagih from data_fa.tblpembayaran_cicilan
where biaya_tagih > 0 and tgl_batal is null and tgl_input='".$tgl."' and fk_cabang_input='".$fk_cabang."'		
";
$lrow_tagih=pg_fetch_array(pg_query($queyy_tagih));
$total_tagih=$lrow_tagih["total_tagih"];
$total_penerimaan+=$total_tagih;

$query_jual="
select sum(case when jenis_transaksi='Jual Credit' then nilai_dp end) as nilai_dp,sum(case when jenis_transaksi='Jual Cash' or jenis_transaksi='Lelang' then  angka_lelang end) as angka_penjualan from data_gadai.tbllelang 
left join tblinventory on tbllelang.fk_sbg=tblinventory.fk_sbg
where tgl_batal is null and fk_cabang_input='".$fk_cabang."' and tgl_lelang between '".$tgl." 00:00:00' and '".$tgl." 23:59:59' and tgl_approve is not null and status_data!='Batal'";
$lrow_jual=pg_fetch_array(pg_query($query_jual));
$query_pelunasan="select NULL,no_kwitansi,fk_sbg,'PEL' as ket,sisa_pokok+bunga_berjalan+pinalti+sisa_angsuran as nilai_bayar,cara_bayar,diskon_pelunasan as disc_denda,tgl_bayar,0 as nilai_bayar_denda, 0 as nilai_bayar_denda2,'','',fk_bank,'1',tgl_bayar from data_fa.tblpelunasan_cicilan
where (sisa_angsuran > 0 or sisa_pokok > 0) and tgl_batal is null and fk_cabang_input='".$fk_cabang."' and tgl_bayar between '".$tgl." 00:00:00' and '".$tgl." 23:59:59'";
$lrow_pelunasan=pg_fetch_array(pg_query($query_pelunasan));
$penjualan=$lrow_jual["nilai_dp"]+$lrow_jual["angka_penjualan"];
$total_penerimaan+=$penjualan+$lrow_pelunasan["nilai_bayar"];

$query_bpkb=pg_fetch_array(pg_query("
select * from data_fa.tblpembayaran_bpkb 
left join tblinventory on data_fa.tblpembayaran_bpkb.fk_sbg=tblinventory.fk_sbg 
where fk_cabang='".$fk_cabang."' AND tgl_bayar between '".$tgl." 00:00:00' and '".$tgl." 23:59:59' AND tgl_batal is null"));
$nilai_bayar_bpkb=$query_bpkb["nilai_bayar"];

$lrow_kas1=pg_fetch_array(pg_query("
select 
sum(case when fk_coa in('4301000') then nominal end) as total_giro,
sum(case when fk_coa not in('4207011','4207022','4301000','aaaa') then nominal end) as total_lain 
from data_fa.tblrekon_bank
left join data_fa.tblrekon_bank_detail on fk_voucher=no_voucher
where fk_cabang='".$fk_cabang."' and tgl_voucher between '".$tgl." 00:00:00' and '".$tgl." 23:59:59'
and type_tr='Masuk'	and fk_bank in ('01','02')  and tgl_approve is not null  and tgl_batal is null
"));

$total_giro=$lrow_kas1["total_giro"];
$total_lain=$lrow_kas1["total_lain"];
//$total_kredit=$lrow_kas1["over_kredit"];
//$slik_ojk=$lrow_kasl["pembayaran_slik_ojk"];	

$query_tebus="
select sum(biaya_tarik+biaya_gudang+biaya_lainnya) as total_tebus from data_fa.tblpembayaran_tebus
left join tblinventory on tblinventory.fk_sbg=tblpembayaran_tebus.fk_sbg
where tgl_batal is null and tgl_bayar='".$tgl."' and fk_cabang='".$fk_cabang."'		
";
//showquery($query_tebus);
$lrow_tebus=pg_fetch_array(pg_query($query_tebus));
$total_tebus=$lrow_tebus["total_tebus"];

$query_tambah_dp="
select sum(total_pembayaran) as total_tambah_dp from data_fa.tblpenambahan_dp
left join tblinventory on tblinventory.fk_sbg=tblpenambahan_dp.fk_sbg
where tgl_batal is null and tgl_bayar='".$tgl."' and fk_cabang='".$fk_cabang."'		
";
//showquery($query_tebus);
$lrow_tambah_dp=pg_fetch_array(pg_query($query_tambah_dp));
$total_tambah_dp=$lrow_tambah_dp["total_tambah_dp"];


$lrow_kas1=pg_fetch_array(pg_query("
select sum(nominal)as total_lain from data_fa.tblmutasi_bank
left join data_fa.tblmutasi_bank_detail on fk_voucher=no_voucher
where tblmutasi_bank_detail.fk_cabang='".$fk_cabang."' and tgl_voucher between '".$tgl." 00:00:00' and '".$tgl." 23:59:59' and jenis_mutasi='0' and fk_bank_masuk in ('01','02')  and tgl_approve is not null and tgl_batal is null
"));//mutasi dari HO
$total_lain+=$lrow_kas1["total_lain"];	

$lrow_kas1a=pg_fetch_array(pg_query("
select sum(total) as total_bank,fk_bank_masuk as fk_bank from data_fa.tblmutasi_bank 
where jenis_mutasi=3 and fk_cabang='".$fk_cabang."' and tgl_voucher between '".$tgl." 00:00:00' and '".$tgl." 23:59:59' and fk_bank_keluar not in ('01','02') and fk_bank_masuk in ('01','02') and tgl_batal is null
group by fk_bank_masuk
"));			
$total_lain+=$lrow_kas1a["total_bank"];	
$kas_kecil+=$lrow_kas1a["total_bank"];	

$total_penerimaan+=($total_giro+$total_lain+$total_kredit+$slik_ojk+$nilai_bayar_bpkb+$total_tebus);


$saldo_akhir+=$total_penerimaan;
$i = 1;
$data[$i]['0'] = '1.';
$data[$i]['1'] = 'Saldo Awal Kas';
$data[$i]['2'] = number_format($saldo_awal);	
$i++;
$data[$i]['0'] = '';
$data[$i]['1'] = '';
$data[$i]['2'] = '';
$i++;

$data[$i]['0'] = '2.';
$data[$i]['1'] = 'Penerimaan / Penambahan :';
$data[$i]['2'] = number_format($total_penerimaan);	
$i++;
$data[$i]['0'] = '-';
$data[$i]['1'] = 'Angsuran Bayar Kantor';
$data[$i]['2'] = number_format($angsuran_tunai);	
$i++;
$data[$i]['0'] = '';
$data[$i]['1'] = 'Motor';
$data[$i]['2'] = number_format($lrow_penerimaan['tunai_motor']);	
$i++;
$data[$i]['0'] = '';
$data[$i]['1'] = 'Mobil';
$data[$i]['2'] = number_format($lrow_penerimaan['tunai_mobil']);	
$i++;
$data[$i]['0'] = '-';
$data[$i]['1'] = 'Collector';
$data[$i]['2'] = number_format($collector);	
$i++;
$data[$i]['0'] = '';
$data[$i]['1'] = 'Motor';
$data[$i]['2'] = number_format($lrow_penerimaan['col_motor']);	
$i++;
$data[$i]['0'] = '';
$data[$i]['1'] = 'Mobil';
$data[$i]['2'] = number_format($lrow_penerimaan['col_mobil']);	
$i++;
$data[$i]['0'] = '-';
$data[$i]['1'] = 'Denda';
$data[$i]['2'] = number_format($denda);	
$i++;
$data[$i]['0'] = '';
$data[$i]['1'] = 'Motor';
$data[$i]['2'] = number_format($lrow_penerimaan['denda_motor']);	
$i++;
$data[$i]['0'] = '';
$data[$i]['1'] = 'Mobil';
$data[$i]['2'] = number_format($lrow_penerimaan['denda_mobil']);	
$i++;
$data[$i]['0'] = '-';
$data[$i]['1'] = 'Biaya Penyimpanan BPKB';
$data[$i]['2'] = number_format($nilai_bayar_bpkb);	
$i++;
$data[$i]['0'] = '-';
$data[$i]['1'] = 'Penjualan Ex Tarikan';
$data[$i]['2'] = number_format($penjualan);	
$i++;
$data[$i]['0'] = '';
$data[$i]['1'] = 'Down Payment';
$data[$i]['2'] = number_format($lrow_jual["nilai_dp"]);	
$i++;
$data[$i]['0'] = '';
$data[$i]['1'] = 'Pelunasan';
$data[$i]['2'] = number_format($lrow_jual['angka_penjualan']+$lrow_pelunasan["nilai_bayar"]);	
$i++;
/*$data[$i]['0'] = '-';
$data[$i]['1'] = 'Penggantian Giro Tolak dengan Tunai';
$data[$i]['2'] = number_format($total_giro);	
$i++;*/
$data[$i]['0'] = '-';
$data[$i]['1'] = 'Laporan Penerimaan by Tagih Kolektor';
$data[$i]['2'] = number_format($total_tagih);	
$i++;
$data[$i]['0'] = '-';
$data[$i]['1'] = 'Penambahan DP';
$data[$i]['2'] = number_format($total_tambah_dp);	
$i++;
$data[$i]['0'] = '-';
$data[$i]['1'] = 'Pembayaran Tebus';
$data[$i]['2'] = number_format($total_tebus);	
$i++;
$data[$i]['0'] = '-';
$data[$i]['1'] = 'Lain-lain';
$data[$i]['2'] = number_format($total_lain);	
$i++;
$data[$i]['0'] = '-';
$data[$i]['1'] = 'Non Tunai';
$data[$i]['2'] = number_format($non_tunai);	
$i++;


$data[$i]['0'] = '';
$data[$i]['1'] = '';
$data[$i]['2'] = '';	

//PENGELUARAN

$lrow_kas1=pg_fetch_array(pg_query("
select 
sum(nominal) as total_lain 
from data_fa.tblrekon_bank
left join data_fa.tblrekon_bank_detail on fk_voucher=no_voucher
where fk_cabang='".$fk_cabang."' and tgl_voucher between '".$tgl." 00:00:00' and '".$tgl." 23:59:59'
and type_tr='Keluar' and fk_bank in ('01','02')  and tgl_approve is not null  and tgl_batal is null
"));
$total_pengeluaran_lain=$lrow_kas1["total_lain"];

$query_petty_cash="
select sum(total)as total_petty_cash from data_fa.tblpetty_cash
where fk_cabang='".$fk_cabang."' and tgl_voucher between '".$tgl." 00:00:00' and '".$tgl." 23:59:59' and tgl_approve is not null  and tgl_batal is null
";
$lrow_kas2=pg_fetch_array(pg_query($query_petty_cash));
$kasbon=$lrow_kas2["total_petty_cash"];

$query_bpb="
select total from data_fa.tblbatch_payment
inner join(
	select sum(nilai_bayar)as total,referensi from data_gadai.tblhistory_sbg 
	where tgl_batal is null and transaksi like 'Pembayaran%' group by referensi
)as tbl on referensi = no_batch
where fk_cabang='".$fk_cabang."' and tgl_batch between '".$tgl." 00:00:00' and '".$tgl." 23:59:59'
";
$lrow_kas2=pg_fetch_array(pg_query($query_bpb));
//showquery($query_bpb);
$kasbon+=$lrow_kas2["total"];

$lrow_kas2=pg_fetch_array(pg_query("
select sum(case when fk_coa in('C') then nominal end) as total1,sum(case when fk_coa in('D') then nominal end) as total2 from data_fa.tblrekon_bank
left join data_fa.tblrekon_bank_detail on fk_voucher=no_voucher
where fk_cabang='".$fk_cabang."' and tgl_voucher between '".$tgl." 00:00:00' and '".$tgl." 23:59:59'
and type_tr='Keluar' and fk_bank in ('01','02') and tgl_approve is not null  and tgl_batal is null
and fk_coa not in('C','D')
"));
$kliring=$lrow_kas1["total1"];

$query_bank="
select * from tblbank 
left join tblcabang_detail_bank on fk_bank=kd_bank
where fk_cabang='".cabang_ho."' and kd_bank not in('01','02','99')
and kd_bank not like '8%'
";				
//showquery($query_bank);
$lrs_bank= pg_query($query_bank);
while($lrow_bank=pg_fetch_array($lrs_bank)){		
	$arr_bank[$lrow_bank["fk_bank"]]['nm_bank']=$lrow_bank['nm_bank'];
}

$query_bank="
select sum(total) as total_bank,fk_bank_masuk as fk_bank,nm_bank from data_fa.tblmutasi_bank left join tblbank on fk_bank_masuk=kd_bank
where jenis_mutasi=2 and fk_cabang_keluar='".$fk_cabang."' and tgl_voucher between '".$tgl." 00:00:00' and '".$tgl." 23:59:59' and fk_bank_keluar in ('01','02') and tgl_approve is not null and tgl_batal is null
group by fk_bank_masuk,nm_bank
			";				
//showquery($query_bank);
$lrs_bank= pg_query($query_bank);
while($lrow_bank=pg_fetch_array($lrs_bank)){		
	$total_bank+=$lrow_bank['total_bank'];
	$arr_bank[$lrow_bank["fk_bank"]]['total']+=$lrow_bank['total_bank'];
}

$query_bank="
select sum(total) as total_bank,fk_bank_masuk as fk_bank,nm_bank from data_fa.tblmutasi_bank left join tblbank on fk_bank_masuk=kd_bank
where jenis_mutasi=3 and fk_cabang_keluar='".$fk_cabang."' and tgl_voucher between '".$tgl." 00:00:00' and '".$tgl." 23:59:59' and fk_bank_keluar in ('01','02') and fk_bank_masuk not in ('01','02') and tgl_approve is not null and tgl_batal is null
group by fk_bank_masuk,nm_bank";				
//showquery($query_bank);
$lrs_bank= pg_query($query_bank);
while($lrow_bank=pg_fetch_array($lrs_bank)){		
	$total_bank+=$lrow_bank['total_bank'];
	$arr_bank[$lrow_bank["fk_bank"]]['total']+=$lrow_bank['total_bank'];
}

$total_pengeluaran=$total_bank+$kliring+$kasbon+$total_pengeluaran_lain;
//END PENGELUARAN
$saldo_akhir-=$total_pengeluaran;

$i++;
$data[$i]['0'] = '3.';
$data[$i]['1'] = 'Pengeluaran /Pengurangan';
$data[$i]['2'] = number_format($total_pengeluaran);	
$i++;
$data[$i]['0'] = '-';
$data[$i]['1'] = 'Setoran ke Bank';
$data[$i]['2'] = number_format($total_bank);	
if(count($arr_bank)>0){
	foreach($arr_bank as $fk_bank =>$isi){
		$i++;
		$data[$i]['0'] = '';
		$data[$i]['1'] = '* '.$isi["nm_bank"];
		$data[$i]['2'] = number_format($isi['total']);	
	}
}

$i++;
/*$data[$i]['0'] = '-';
$data[$i]['1'] = 'Kliring';
$data[$i]['2'] = number_format($kliring);	
$i++;*/
$data[$i]['0'] = '-';
$data[$i]['1'] = 'KAS BON';
$data[$i]['2'] = number_format($kasbon);	

$i++;
$data[$i]['0'] = '-';
$data[$i]['1'] = 'Lain-lain';
$data[$i]['2'] = number_format($total_pengeluaran_lain);	

$i++;
$data[$i]['0'] = '';
$data[$i]['1'] = '';
$data[$i]['2'] = '';	


$tgl1=date('m/d/Y',strtotime($tgl))." 23:59:59";
$periode_awal=$tgl1;
//echo $tgl1;
$total_01=get_saldo_coa_harian($fk_coa_01);//ambil saldo sampai hari ini ;
$total_02=get_saldo_coa_harian($fk_coa_02);

$saldo_akhir_01=$saldo_awal_01+$total_01;
$saldo_akhir_02=$saldo_awal_02+$total_02;
$saldo_akhir_cash=$saldo_akhir_01+$saldo_akhir_02;

$query_lkh="
select * from data_fa.tbllkh_detail
left join data_fa.tbllkh on fk_lkh=no_lkh
where fk_cabang='".$fk_cabang."' and tgl_lkh between '".$tgl." 00:00:00' and '".$tgl." 23:59:59' 
";	
$lrs_lkh= pg_query($query_lkh);
while($lrow_lkh=pg_fetch_array($lrs_lkh)){	
	$pecahan=$lrow_lkh["pecahan"];
	if($lrow_lkh['jenis']=='Kertas'){
		$kertas[$pecahan]+=$lrow_lkh['jumlah'];
	}else if($lrow_lkh['jenis']=='Logam'){
		$logam[$pecahan]+=$lrow_lkh['jumlah'];
	}
	$total[$pecahan]+=$lrow_lkh['jumlah']*$pecahan;	
	$totallkh+=$lrow_lkh['jumlah']*$pecahan;	
}			



$i++;
$data[$i]['0'] = '4.';
$data[$i]['1'] = 'Saldo Akhir Kas';
$data[$i]['2'] = number_format($saldo_akhir);	
$i++;
$data[$i]['0'] = '';
$data[$i]['1'] = 'Perincian Kas:';
$data[$i]['2'] = '';	
$i++;

$saldo_akhir_cash=$saldo_akhir-$kasbon_ditangguhkan-$kas_kecil-$bg_mundur-$bg_total;
$data[$i]['0'] = '-';
$data[$i]['1'] = 'Kas Tunai';
$data[$i]['2'] = number_format($saldo_akhir_cash);
$i++;
$data[$i]['0'] = '-';
$data[$i]['1'] = 'Kas Bon di tangguhkan';
$data[$i]['2'] = number_format($kasbon_ditangguhkan);	
$i++;
$data[$i]['0'] = '-';
$data[$i]['1'] = 'Kas Kecil/Petty Cash';
$data[$i]['2'] = number_format($kas_kecil);	
$i++;
$data[$i]['0'] = '-';
$data[$i]['1'] = 'BG Mundur';
$data[$i]['2'] = number_format($bg_mundur);	
$i++;
$data[$i]['0'] = '-';
$data[$i]['1'] = 'BG Tolak';
$data[$i]['2'] = number_format($bg_tolak);	
$i++;
$data[$i]['0'] = '';
$data[$i]['1'] = 'Total selisih';
$data[$i]['2'] = number_format($totallkh-$saldo_akhir);	


$judul['0'] = '';
$judul['1'] = 'POSISI KAS';
$judul['2'] = 'Tanggal '.convert_date_indonesia($tgl);

$lining['showHeadings'] = 1;
$lining['shaded'] = 0;
$lining['showLines'] =1 ;
$lining['xPos'] = 180;

$lining['cols']['1']['heading_justification'] = 'left';
$lining['cols']['catatan']['heading_justification'] = 'left';
$lining['cols']['nominal']['heading_justification'] = 'right';

$lining['cols']['fk_coa']['justification'] = 'left';
$lining['cols']['2']['justification'] = 'right';

$size['fk_coa'] = '100';
$size['catatan'] = '300';
$size['nominal'] = '100';

//print_r($data);
$pdf->ezTable($data,$judul,'',$lining,$size);

//TABLE UANG
$y=$pdf->y;
$pdf->ezSetY($y+190);
//showquery($query_lkh);
//print_r($kertas);
$i=1;
$arr_pecahan=array(100000,50000,20000,10000,5000,2000,1000,500,200,100);
foreach($arr_pecahan as $pecahan){
	$i++;
	$data2[$i]['0'] = $kertas[$pecahan];
	$data2[$i]['1'] = $logam[$pecahan];
	$data2[$i]['2'] = number_format($pecahan);
	$data2[$i]['3'] = number_format($total[$pecahan]);	
}
$i++;
$data2[$i]['0'] = '';
$data2[$i]['0'] = '';
$data2[$i]['2'] = 'Total';
$data2[$i]['3'] = number_format($totallkh);	

$lining2['showHeadings'] = 1;
$lining2['shaded'] = 0;
$lining2['showLines'] =2 ;
$lining2['xPos'] = 450;

$lining2['cols']['0']['justification'] = 'right';
$lining2['cols']['1']['justification'] = 'right';
$lining2['cols']['2']['justification'] = 'right';
$lining2['cols']['3']['justification'] = 'right';
$judul2['0'] = 'LBR';
$judul2['1'] = 'LGM';
$judul2['2'] = 'NOMINAL';
$judul2['3'] = 'JUMLAH';

$pdf->ezTable($data2,$judul2,'',$lining2,$size2);
//END TABLE UANG

$y-=40;
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
	
	if($lrow["jenis_cabang"]=='Pos'){
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


$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');
	
//end content
$options['Content-Disposition']='LKH_'.$fk_cabang.'_'.date("Ymd",strtotime($tgl)).'.pdf';//nama file

$pdf->ezStream($options);   


function get_saldo_coa_harian($fk_coa=NULL){
	global $bulan,$tahun,$fk_cabang,$nm_cabang,$periode_awal,$periode_akhir,$strisi,$strisi_cabang;

	if($periode_awal=='')$periode_awal=today_db;
	if($periode_akhir=='')$periode_akhir=today_db;
	if($fk_cabang != ''){
		$lwhere.=" and fk_coa like '".$fk_cabang."%' ";
		$lwhere1.=" and fk_cabang = '".$fk_cabang."' ";
	}		
	
	if($fk_coa){
		$row_coa=pg_fetch_array(pg_query("
		select transaction_type as tr_type,fk_currency,type_saldo,coa,tblcoa.description,nm_perusahaan,alamat,nm_cabang from (
			select * from tblcoa where coa like '%".convert_sql($fk_coa)."%'
		) as tblcoa
		inner join tblhead_account on tblhead_account.code=tblcoa.fk_head_account
		left join tblcabang on fk_cabang = kd_cabang
		"));
	};
	
	$start_day=date('m',strtotime($periode_awal)).'/01/'.date('Y',strtotime($periode_awal))." 00:00:00";
	$end_day=date('m/d/Y',strtotime('-1 second',strtotime($periode_awal)))." 23:59:59";
	
	$source_schema='data_accounting';

	$query="
		select * from(
			select * from (
				select no_bukti,reference_transaksi,tr_date,description, 
					null, 'Auto'::text as type_of_source,cast(fk_owner as text), 
					case when fk_coa_d is not null then total end as total_debit, 
					case when fk_coa_c is not null then total end as total_credit, 
					rate, type_owner, fk_owner
				from (
					select * from ".$source_schema.".tblgl_auto
					where (fk_coa_d like '%".convert_sql($fk_coa)."%' or fk_coa_c like '%".convert_sql($fk_coa)."%') and tr_date>='#".$start_day."#' and tr_date<='#".$end_day."#' ".$lwhere1."
				) as tblgl_auto
				left join tblcustomer on tblcustomer.no_cif=tblgl_auto.fk_customer 
			)as tblmain
			where total_debit <> 0 or total_credit <> 0
		)as tblmain2
		order by tr_date,no_bukti
	
	";
	// showquery($query);
	$lrs=pg_query($query);
	
	$i=1;
	while ($row=pg_fetch_array($lrs)){
		if($row["total_debit"]!=0 || $row["total_credit"]!=0){
		
			if ($row_coa["tr_type"]=="D") {
				$saldo+=($row["total_debit"]-$row["total_credit"]);
			} else {
				$saldo+=($row["total_credit"]-$row["total_debit"]);
			}
			
			$i++;
		}
	}
	return $saldo;
}

?>