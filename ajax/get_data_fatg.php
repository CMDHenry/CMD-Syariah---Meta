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
	$fk_fatg=$_REQUEST["fk_fatg"];
	$tipe=$_REQUEST["tipe"];
	
	$query="
	select * from(
		select fk_cabang,case when total_hutang is null then '0' else total_hutang end as akumulasi_os_pokok,fk_jenis_barang,no_fatg,nm_customer,total_taksir,perpanjangan_ke
		,case when nilai_ap_customer is null then '0' else nilai_ap_customer end as nilai_ap_lama,case when fk_karyawan_sales1 is null then '' else fk_karyawan_sales1 end as fk_karyawan_sales,
		status_barang
		,case when asal_aplikasi is null then '' else asal_aplikasi end as asal_aplikasi
		,case when no_sbg is null then '-' else no_sbg end as no_sbg
		,case when fk_produk_lama is null then '' else fk_produk_lama end as fk_produk_lama from 
		(select * from viewtaksir)as tbltaksir 
		left join tblcustomer on no_cif = fk_cif 
		left join (
			select fk_cif as fk_customer,sum(saldo_pokok)as total_hutang from viewang_ke
			left join tblinventory on viewang_ke.fk_sbg=tblinventory.fk_sbg
			group by fk_cif
		)as tblos on fk_customer=fk_cif 
		left join tblcabang on kd_cabang=fk_cabang 
		left join (
			select nilai_ap_customer,'-' :: text as fk_karyawan_sales1,asal_aplikasi,no_sbg,fk_produk as fk_produk_lama from data_gadai.tblproduk_".$tipe."
		)as tblviewkontrak on no_sbg=no_sbg_lama
		--left join tblpartner on fk_partner_bank=kd_partner
	) as tblmain 	
	where no_fatg='".$fk_fatg."' ";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	
	$nm_customer=$lrow["nm_customer"];	
	$total_taksir=$lrow["total_taksir"];	
	$akumulasi_os_pokok=$lrow["akumulasi_os_pokok"];	
	$fk_cabang=$lrow["fk_cabang"];	
	$perpanjangan_ke=$lrow["perpanjangan_ke"];	
	$nilai_ap_lama=$lrow["nilai_ap_lama"];	
	$fk_karyawan_sales=$lrow["fk_karyawan_sales"];	
	$status_barang=$lrow["status_barang"];	
	$asal_aplikasi=$lrow["asal_aplikasi"];	
	$no_sbg=$lrow["no_sbg"];	
	$fk_produk_lama=$lrow["fk_produk_lama"];	
	$no_rekening=$lrow["no_rekening"];	
	$fk_partner_bank=$lrow["fk_partner_bank"];	
	$nm_partner=$lrow["nm_partner"];	
	
	$kategori=get_rec("viewkendaraan","kategori","no_fatg='".$fk_fatg."'");
	
	echo $nm_customer.'¿';
	echo $total_taksir.'¿';
	echo $akumulasi_os_pokok.'¿';//
	echo $fk_cabang.'¿';
	echo $perpanjangan_ke.'¿';
	echo '0'.'¿';
	echo $fk_karyawan_sales.'¿';
	echo $status_barang.'¿';
	echo $kategori.'¿';
	echo $no_sbg.'¿';
	echo $fk_produk_lama.'¿';
	echo '0'.'¿';
	
}

function nilai_pelunasan($fk_sbg,$tipe){
	global $nilai_pinjaman, $nilai_penyimpanan, $biaya_penjualan, $biaya_denda,$sisa_pokok,$bunga_berjalan,$denda,$pinalti;	
	if($tipe=='gadai'){
		pelunasan_gadai($fk_sbg);
		
		$total_pembayaran = $nilai_pinjaman + $nilai_penyimpanan + $biaya_penjualan + $biaya_denda;	
	}elseif($tipe=='cicilan'){
		pelunasan_cicilan($fk_sbg);
		
		$total_pelunasan=$sisa_pokok+$bunga_berjalan+$denda+$pinalti;
		$total_pembayaran=$total_pelunasan-$titipan_angsuran;
	}
	$nilai_bayar=pembulatan_pelunasan($total_pembayaran);	
	return $nilai_bayar;
}
