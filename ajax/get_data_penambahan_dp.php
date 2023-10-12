<?
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/convert.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/numeric.inc.php';
require '../requires/referer_check.inc.php';
require '../classes/recordset_ajax.class.php';
require '../requires/stok_utility.inc.php';


if (!isset($_SESSION['id']) ){
	//echo "err:1000 ";
} else{
	
	$fk_sbg=$_REQUEST["fk_sbg"];
	//$fk_sbg='2010121100002';
	$nilai_dp=$_REQUEST["nilai_dp_baru"];
	//$nilai_dp=30000000;
	$lama_pinjaman_input=$_REQUEST["lama_pinjaman"];
	
	$jenis=$_REQUEST["jenis"];

	if(!$tgl_sistem){
		$tgl_sistem=date("Y-m-d",strtotime(get_rec("tblsetting",'tgl_sistem')));
	}	
	$tgl_bayar=$tgl_sistem;
	
	$query="
	select * from(
		select fk_sbg as no_sbg,fk_cif,fk_produk,tgl_cair,fk_cabang,nominal_denda_keterlambatan,rate_denda_ganti_rugi,rate_pinalti from tblinventory 
		left join tblproduk on fk_produk=kd_produk
		where fk_sbg='".$fk_sbg."' and jenis_produk=1 --and status_sbg='Liv'
	)as tblproduk_cicilan	
	left join tblcustomer on no_cif=fk_cif
	left join (
		select no_sbg as no_sbg1 ,rate_flat from viewkontrak 
	)as tblsbg on no_sbg=no_sbg1
	left join(
		select rate_flat_input, no_sbg as no_sbg2 from data_gadai.tblproduk_cicilan
	)as tblcicilan on no_sbg=no_sbg2
	left join (select fk_sbg as fk_sbg3 ,saldo_titipan as titipan from data_fa.tbltitipan)as tbltitipan on fk_sbg3=no_sbg
	left join (select fk_sbg as fk_sbg4 ,saldo_denda as total_denda_lalu from data_fa.tbldenda)as tbldenda on fk_sbg4=no_sbg

	";
	//showquery($query);
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	
	$fk_cif=$lrow["fk_cif"];
	$fk_cabang=$lrow["fk_cabang"];
	$nm_customer=$lrow["nm_customer"];
	$tgl_cair=$lrow["tgl_cair"];
	$total_denda_lalu=($lrow["total_denda_lalu"]==""?0:round($lrow["total_denda_lalu"]));		
	$rate_denda=$lrow["rate_denda"];
	$rate_pinalti=$lrow["rate_pinalti"];
	$rate_flat=($lrow["rate_flat"]);
	$rate_flat_input=($lrow["rate_flat_input"]);
	//$rate_flat_input=9.5;
	if($total_denda_lalu<0)$total_denda_lalu=0;
			
if($fk_cabang){		
	
	$lrs=(pg_query("select sum(pokok_jt)as sisa_pokok,sum(bunga_jt)as sisa_bunga,count(1)as sisa_tenor from data_fa.tblangsuran
where fk_sbg='".$fk_sbg."' and tgl_jatuh_tempo >'".$tgl_bayar."' 
	-- and tgl_bayar is null"));
	$lrow2=pg_fetch_array($lrs);		
	$sisa_pokok=round($lrow2["sisa_pokok"]);
	$sisa_bunga=round($lrow2["sisa_bunga"]);
	//$sisa_pokok=136239897;
	
	$lrs=(pg_query("select sum(pokok_jt)as sisa_pokok,count(1)as sisa_tenor from data_fa.tblangsuran
where fk_sbg='".$fk_sbg."' and tgl_jatuh_tempo >'".$tgl_bayar."'"));
	$lrow2=pg_fetch_array($lrs);			
	//$lama_pinjaman=round($lrow2["sisa_tenor"])-1;
	$lama_pinjaman=$lama_pinjaman_input;
	//$lama_pinjaman=40;		
	
	$lrs=(pg_query("select sum(bunga_jt)as akrual,sum(nilai_angsuran)as sisa_angsuran from data_fa.tblangsuran
where fk_sbg='".$fk_sbg."' and tgl_jatuh_tempo <='".$tgl_bayar."' 
	--and tgl_bayar is null"));
	$lrow2=pg_fetch_array($lrs);		
	$sisa_angsuran=round($lrow2["sisa_angsuran"]);
	
	//denda
	$denda_keterlambatan=0;
	$denda_ganti_rugi=0;
	$total_denda_kini=0;
	
	$lrs=(pg_query("select tgl_jatuh_tempo,nilai_angsuran from data_fa.tblangsuran where fk_sbg='".$fk_sbg."' 
	--and tgl_bayar is null 
	order by angsuran_ke asc "));

	$i=0;
	while($lrow1=pg_fetch_array($lrs)){
		$tgl_jatuh_tempo=$lrow1["tgl_jatuh_tempo"];
		$nilai_angsuran=$lrow1["nilai_angsuran"];
		
		$overdue=(strtotime($tgl_bayar)-strtotime($tgl_jatuh_tempo))/ (60 * 60 * 24);       	
		if($overdue<0)$overdue=0;
		
		if($overdue>0){
			$denda_keterlambatan+=$lrow["nominal_denda_keterlambatan"];
			$denda_ganti_rugi+=round($lrow["rate_denda_ganti_rugi"]*$nilai_angsuran/100)*$overdue;
		}		
	}
	
	$total_denda_kini=$denda_keterlambatan+$denda_ganti_rugi;
	$nilai_bayar_denda=$total_denda_lalu+$total_denda_kini;	
	
	$biaya_admin=round($nilai_dp*$rate_pinalti/100);
		
	//hitung angsuran 
	$pokok_hutang=$sisa_pokok;
	$pokok_hutang+=$biaya_admin;	
	$pokok_hutang-=$nilai_dp;		

	$jumlah_hari='360';
	
	if($lama_pinjaman>0){
		//$biaya_penyimpanan=round(($pokok_hutang*$lama_pinjaman*$rate_flat_input/100*(30/$jumlah_hari)));		
		$biaya_penyimpanan=$sisa_bunga;
			
		$total_hutang=$pokok_hutang+$biaya_penyimpanan;	
		$angsuran=round($total_hutang/$lama_pinjaman);
		
		//echo substr($angsuran,-3,3).',';
		if(substr($angsuran,-3,3)<=500 && substr($angsuran,-3,3)>0){
			if(substr($angsuran,-3,3)!=500){
				$angsuran=$angsuran/1000;
				$angsuran=(round($angsuran)*1000)+500;
			}
		}else {
			$angsuran=$angsuran/1000;
			$angsuran=ceil($angsuran)*1000;			
		}				
		
		//cari rate flat buat angsuran bulat lalu hitung ulang
		$total_angsuran=$angsuran*$lama_pinjaman;
		$total_bunga_bulat=$total_angsuran-$pokok_hutang;
		$rate_flat=$total_bunga_bulat/($lama_pinjaman*(30/$jumlah_hari))/$pokok_hutang*100;		
		$rate_flat=str_replace(",","",convert_money("",$rate_flat,8));		
		$biaya_penyimpanan=$total_bunga_bulat;
		$total_hutang=$pokok_hutang+$biaya_penyimpanan;		
	}
	
}
	
	
	echo $nm_customer.'¿';
	echo $fk_cabang.'¿';
	echo $sisa_pokok.'¿';	
	echo $biaya_admin.'¿';	
	echo $nilai_dp.'¿';	
	echo $pokok_hutang.'¿';	
	echo $biaya_penyimpanan.'¿';	
	echo $total_hutang.'¿';	
	echo $angsuran.'¿';	
	echo $rate_flat.'¿';	
	echo $sisa_angsuran.'¿';	
	echo $total_denda_lalu.'¿';	
	echo $denda_keterlambatan.'¿';	
	echo $denda_ganti_rugi.'¿';	
	echo $total_denda_kini.'¿';	
	echo $nilai_bayar_denda.'¿';	
	echo $lama_pinjaman.'¿';	
	
}
