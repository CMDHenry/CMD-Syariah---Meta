<?php

include_once("report.php");

function filter_request(){
	global $jenis_produk,$showCab,$showTgl,$no_cif,$nm_customer,$no_id,$is_tgl_jatuh_tempo,$is_lapor,$p;
	
	$showCab='t';
	$showTgl='t';
	$p='Y';
	
}

function create_filter(){
	global $jenis_produk,$no_cif,$no_id,$nm_customer,$is_tgl_jatuh_tempo;
?>
      
<?	
}

function excel_content(){
	global $tgl,$fk_cabang,$nm_cabang,$jenis_produk,$no_cif,$is_tgl_jatuh_tempo,$is_lapor;
	
	if($tgl != '' ){
		$lwhere.=" (tgl_lunas > '".$tgl."' or tgl_lunas is null) and tgl_cair <='".$tgl."'";
	}
	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cabang = '".$fk_cabang."' ";
	}
		
	if ($lwhere!="") $lwhere=" where ".$lwhere;

	$bulan=date("m",strtotime($tgl));
	$tahun=date("Y",strtotime($tgl));
	$query = "
	select * from(
		select * from tblinventory
		left join tblcustomer on no_cif=fk_cif
		".$lwhere."
		--and fk_sbg='41101210100373'
	)as tblmain
	left join data_gadai.tblproduk_cicilan on fk_sbg=no_sbg
	left join(
		select distinct on(fk_sbg)fk_sbg as fk_sbg_bayar, tgl_bayar as tgl_bayar_terakhir,nilai_angsuran as nominal_bayar_terakhir,tgl_jatuh_tempo as tgl_jt_bayar_terakhir,angsuran_ke as ang_ke_akhir from data_fa.tblangsuran 
		where (tgl_bayar is not null and tgl_bayar<='".$tgl."')
		and angsuran_ke>0 order by fk_sbg,angsuran_ke desc 
	)as tblbayar on no_sbg=fk_sbg_bayar		
	
	left join(
		select distinct on(fk_sbg)fk_sbg as fk_sbg_od,date_part('day','".$tgl."' -tgl_jatuh_tempo)as ovd from data_fa.tblangsuran 
		where (tgl_bayar is null or(tgl_bayar is not null and tgl_bayar>'".$tgl."'))
		and angsuran_ke>0 order by fk_sbg,angsuran_ke desc 
	)as tblod on no_sbg=fk_sbg_od			
	left join (
		select sum(pokok_jt) as saldo_pokok, sum(bunga_jt) as saldo_bunga,sum(nilai_angsuran) as saldo_pinjaman, fk_sbg_os from(
			select bunga_jt, pokok_jt as pokok_jt,nilai_angsuran, fk_sbg as fk_sbg_os from data_fa.tblangsuran
			".where_os_tblangsuran($tgl)."						
		)as tblar
		inner join tblinventory on fk_sbg = fk_sbg_os
		".where_os_tblinventory($tgl)."
		group by fk_sbg_os
	)as tblos on no_sbg=fk_sbg_os
	left join (
		select kd_partner as kd_asuransi, nm_partner as nm_asuransi from tblpartner
	)as tblasuransi on fk_partner_asuransi=kd_asuransi	
	
	left join(
		select viewkendaraan.no_fatg,nm_tipe,kategori,status_barang,viewkendaraan.fk_partner_dealer,
		fk_tujuan_transaksi,posisi_bpkb,viewkendaraan.no_rangka,no_bpkb from viewkendaraan 		
		left join data_gadai.tbltaksir_umum on viewkendaraan.no_fatg=tbltaksir_umum.no_fatg
	)as tbltaksir on fk_fatg=no_fatg
	
	left join (
		select kd_partner as kd_dealer, nm_partner as nm_dealer from tblpartner
	)as tbldealer on fk_partner_dealer=kd_dealer	
	
	left join tbltujuan_transaksi on fk_tujuan_transaksi=kd_tujuan_transaksi
	left join tblbidang_usaha on fk_bidang_usaha=kd_bidang_usaha
	left join tblpekerjaan on fk_pekerjaan=kd_pekerjaan
	
	left join(
		select kd_cabang,nm_cabang,kd_kota_ojk from tblcabang
		left join tblkelurahan on fk_kelurahan=kd_kelurahan
		left join tblkecamatan on fk_kecamatan=kd_kecamatan
		left join tblkota on fk_kota=kd_kota
	)as tblcabang on fk_cabang=kd_cabang
	left join(
		select fk_sbg as fk_sbg_cad,nilai_agunan from data_fa.tblcadangan_piutang 
		left join data_fa.tblcadangan_piutang_detail on kd_voucher=fk_voucher
		where bulan='".$bulan."' and tahun='".$tahun."'
	)as tblcad on fk_sbg=fk_sbg_cad
	left join(
		select fk_sbg as fk_sbg_cad2,sum(total_cadangan)as total_cadangan from data_fa.tblcadangan_piutang 
		left join data_fa.tblcadangan_piutang_detail on kd_voucher=fk_voucher
		where tgl_voucher<='".$tgl."'
		group by fk_sbg
	)as tblcad2 on fk_sbg=fk_sbg_cad2
	
	left join(
		select distinct on (fk_sbg)fk_sbg as fk_sbg_tarik,tgl_data as tgl_tarik from data_gadai.tblhistory_sbg
		where transaksi='Tarik'	and tgl_data<='".$tgl." 23:59:59' and tgl_batal is null
	)as tarik on no_sbg=fk_sbg_tarik					
	left join(
		select distinct on (fk_sbg)fk_sbg as fk_sbg_tebus,tgl_data as tgl_tebus from data_gadai.tblhistory_sbg
		where transaksi='Tebus'	and tgl_data<='".$tgl." 23:59:59' and tgl_batal is null
	)as tebus on no_sbg=fk_sbg_tebus	
	
	";
	
	//showquery($query);
	//SILARAS
	$data.= 	
	'<table border="1">
	     <tr>
		 	<td align="center">Nomor Konsumen</td>
			<td align="center">Nama Konsumen</td>			
			<td align="center">Nama Kelompok Konsumen</td>
			<td align="center">Kategori Usaha Konsumen</td>
			<td align="center">Kategori Usaha Keuangan Berkelanjutan</td>
			<td align="center">Golongan Konsumen</td>
			<td align="center">Status Keterkaitan</td>
			<td align="center">Sektor Ekonomi Lapangan Usaha</td>
			<td align="center">Lokasi Kabupaten/Kota Proyek</td>			
						
			<td align="center">Nomor Kontrak</td>
			<td align="center">Jenis Pembiayaan</td>			
			<td align="center">Skema Pembiayaan</td>			
			<td align="center">Tujuan Pembiayaan</td>
			<td align="center">Tanggal Mulai Pembiayaan</td>
			<td align="center">Tanggal Jatuh Tempo Pembiayaan</td>
			
			<td align="center">Nilai Awal Pembiayaan</td>
			<td align="center">Tagihan Piutang Pembiayaan Bruto dalam Mata Uang Asal</td>
			<td align="center">Tagihan Piutang Pembiayaan Bruto dalam Ekuivalen Rupiah</td>			
			<td align="center">Tagihan Piutang Pembiayaan Pokok dalam Mata Uang Asal </td>
			<td align="center">Tagihan Piutang Pembiayaan Pokok dalam Ekuivalen Rupiah</td>									
			<td align="center">Porsi Perusahaan Pada Pembiayaan Bersama</td>
			<td align="center">Jenis Mata Uang</td>
			<td align="center">Simpanan Jaminan/Uang Muka</td>
			<td align="center">Pihak Lawan Kerjasama Pembiayaan Bersama (Joint Financing)</td>
			<td align="center">Biaya Insentif Akuisisi Pembiayaan kepada Pihak Ketiga</td>			
			<td align="center">% TAC</td>
			<td align="center">Batas Maksimal TAC</td>
			<td align="center">% Max TAC</td>								
			
			<td align="center">Jenis Bagi Hasil</td>
			<td align="center">Nilai Margin/Imbal Jasa</td>			
			<td align="center">Tingkat Bagi Hasil</td>
			<td align="center">Margin yang Ditangguhkan dalam Mata Uang Asal</td>
			<td align="center">Margin yang Ditangguhkan dalam Ekuivalen Rupiah</td>
			<td align="center">Pendapatan Administrasi</td>
			<td align="center">Pendapatan Provisi</td>
			<td align="center">Kategori Piutang</td>
			<td align="center">Kualitas</td>
			<td align="center">Tanggal Jatuh Tempo Pembayaran Terakhir</td>
			<td align="center">Tanggal Pembayaran Angsuran Terakhir</td>
			<td align="center">Angsuran Ke-</td>
						
			<td align="center">Nilai Pembayaran Angsuran Terakhir</td>
			<td align="center">Jenis Barang dan Jasa yang Dibiayai</td>			
			<td align="center">Nilai Barang/Jasa yang Dibiayai</td>
			<td align="center">Metode Cadangan Kerugian Penurunan Nilai</td>
			
			<td align="center">Nilai CKPN Aset Baik</td>
			<td align="center">Nilai CKPN Aset Kurang Baik</td>
			<td align="center">Nilai CKPN Aset Tidak Baik</td>
			<td align="center">Proporsi Penjaminan Kredit Syariah</td>		
			
			<td align="center">Nama Perusahaan Asuransi</td>				
			<td align="center">Jangka Waktu Asuransi Syariah (Bulan)</td>
			<td align="center">Kontribusi oleh Konsumen</td>
			<td align="center">Diskon Kontribusi</td>
			
			<td align="center">Nomor Identitas Agunan</td>
			<td align="center">Jenis Agunan</td>
			<td align="center">Nilai Agunan</td>
			<td align="center">Jenis Pengikatan Agunan</td>
			<td align="center">Nomor Sertifikat Kepemilikan Agunan</td>
			<td align="center">Nomor Sertifikat Pengikatan Agunan</td>
			<td align="center">Tanggal Sertifikat Pengikatan Agunan</td>
			<td align="center">Posisi Penyimpanan Sertifikat Agunan</td>
			
		  </tr>
	';
	$lrs = pg_query($query);
	$no=1;
	
	$jenis_pembiayaan['Jual Beli']='e25';
	$jenis_pembiayaan['Investasi']='e30';
	$jenis_pembiayaan['Usaha']='e36';
	
	$skema_pembiayaan['Murabahah']='e26';
	$skema_pembiayaan['Mudharabah']='e31';
	$skema_pembiayaan['Istishna']='e28';
	$skema_pembiayaan['Salam']='e27';
	$skema_pembiayaan['Musyarakah']='e32';
	$skema_pembiayaan['Mudharabah musytarakah']='e33';
	$skema_pembiayaan['Musyarakah mutanaqishoh']='e34';
	$skema_pembiayaan['Ijarah']='e37';
	$skema_pembiayaan['Ijarah muntahiya bi tamlik']='e38';
	$skema_pembiayaan['Hawalah atau Hawalah bil ujrah']='e40';
	$skema_pembiayaan['Wakalah atau Wakalah bil ujrah']='e42';
	$skema_pembiayaan['Kafalah atau Kafalah bil ujrah']='e44';
	$skema_pembiayaan['Qardh']='e46';
	
	while($lrow=pg_fetch_array($lrs)){		
		
		if($lrow['all_risk_dari_tahun']){
			if($lrow['all_risk_dari_tahun']==$lrow['all_risk_sampai_tahun']){
				$lrow["lama_asuransi"]=$lrow['all_risk_dari_tahun'];
			}else{
				$lrow["lama_asuransi"]=$lrow['all_risk_sampai_tahun'];
			}						
		}		
		if($lrow['tlo_dari_tahun']){
			$lrow["lama_asuransi"]=$lrow['tlo_sampai_tahun'];
		}		
		
		if($lrow["kondisi_unit"]=='Baru'){
			if($lrow["kategori"]=='R2'){
				$lrow["jenis_barang"]='e36';
			}
			if($lrow["kategori"]=='R4'){
				$lrow["jenis_barang"]='e38';
			}
		}
		if($lrow["kondisi_unit"]=='Bekas'){
			if($lrow["kategori"]=='R2'){
				$lrow["jenis_barang"]='e37';
			}
			if($lrow["kategori"]=='R4'){
				$lrow["jenis_barang"]='e39';
			}
		}
		
		if($lrow["ovd"]<0)$lrow["ovd"]=0;
		$query_cad='select * from tblsetting_cadangan';
		$lrs_cad=pg_query($query_cad);
		while($lrow_cad=pg_fetch_array($lrs_cad)){
			if($lrow["ovd"]>=$lrow_cad["ovd_awal"]&& $lrow["ovd"]<=$lrow_cad["ovd_akhir"]){
				$lrow["kualitas"]=$lrow_cad['kd_ojk'];		
				$lrow["status_aset"]=$lrow_cad['status_aset'];					
			}			
		}
		
		$lrow["aset ".$lrow["status_aset"]]=$lrow["total_cadangan"];
				
		
		if($lrow["penghasilan"]>=0&& $lrow["penghasilan"]<=(30*$juta)){
			$lrow["kategori_usaha"]='e17';
		}elseif($lrow["penghasilan"]>(30*$juta)&& $lrow["penghasilan"]<=(100*$juta)){
			$lrow["kategori_usaha"]='e18';
		}elseif($lrow["penghasilan"]>(100*$juta)&& $lrow["penghasilan"]<=(200*$juta)){
			$lrow["kategori_usaha"]='e19';
		}elseif($lrow["penghasilan"]>(200*$juta)){
			$lrow["kategori_usaha"]='e16';
		}
		
		if($lrow["is_terkait"]=='t'){
			$lrow["keterkaitan"]='e2';
		}else{
			$lrow["keterkaitan"]='e3';
		}
		
		$lrow["jenis_pembiayaan"]=$jenis_pembiayaan[$lrow["jenis_pembiayaan"]];
		$lrow["skema_pembiayaan"]=$skema_pembiayaan[$lrow["skema_pembiayaan"]];
		
		if($lrow["status_barang"]=='Baru'){
			if($lrow["kategori"]=='R2'){
				$lrow["jenis_agunan"]='e36';
			}
			if($lrow["kategori"]=='R4'){
				$lrow["jenis_agunan"]='e38';
			}
		}
		
		if($lrow["status_barang"]=='Bekas' || $lrow["status_barang"]=='Datun'){
			if($lrow["kategori"]=='R2'){
				$lrow["jenis_agunan"]='e37';
			}
			if($lrow["kategori"]=='R4'){
				$lrow["jenis_agunan"]='e39';
			}
		}
		$lrow["jenis_bagi_hasil"]='e49';
		$lrow["kategori_piutang"]='e69';
		
		
		$query_mutasi="
			select * from data_fa.tblmutasi_bpkb 
			left join data_fa.tblmutasi_bpkb_detail on no_mutasi=fk_mutasi
			where tgl_batal is null and tgl_terima is not null and tgl_terima<='".$tgl." 23:59:59'
			and fk_sbg='".$lrow["fk_sbg"]."' order by tgl_terima desc
		";
		//showquery($query_mutasi);		
		$lrow_mutasi=pg_fetch_array(pg_query($query_mutasi));
		
		$lrow["posisi_bpkb"]=$lrow_mutasi["penerima"];		
		if($lrow["posisi_bpkb"]=='Cabang'){			
			$lrow["posisi_penyimpanan"]='e32';
		}elseif($lrow["posisi_bpkb"]=='HO'){			
			$lrow["posisi_penyimpanan"]='e34';
		}elseif($lrow["posisi_bpkb"]=='Bank'){			
			$lrow["posisi_penyimpanan"]='e42';
		}else $lrow["posisi_penyimpanan"]='';
		
		$posisi_penyimpanan=$lrow["posisi_penyimpanan"];
		if($posisi_penyimpanan!=""){
			if($is_lapor=="" || $is_lapor=="f"){
				$posisi_penyimpanan=$lrow["posisi_penyimpanan"];
			}else{
				$posisi_penyimpanan="AK:".$lrow["posisi_penyimpanan"];
			}
		}
		
		$lrow["metode_cadangan"]='e62';
		$max_tac=get_rec('tblsetting','rate_max_tac',"is_pt='t'");
		//$max_tac=17.5;
		$total_insentif=$lrow["insentif_dealer"]+$lrow["insentif_kacab"]+$lrow["insentif_spv"]+$lrow["insentif_sales"]+$lrow["insentif_lain"];
		$rate_tac=$total_insentif/$lrow['biaya_penyimpanan']*100;
		
		$total_max_tac=$max_tac*$lrow['biaya_penyimpanan']/100;

		if($total_insentif<=$total_max_tac){
			$total_max_tac='';
			$max_tac='';
		}
		
		$lrow['kontribusi_konsumen']=$lrow["nilai_asuransi"];
		$lrow['diskon_kontribusi_konsumen']=$lrow["nilai_asuransi"]*0.25;
		
		
		if($lrow["fk_sbg_tarik"]){
			if(!$lrow["fk_sbg_tebus"] || $lrow["tgl_tarik"]>$lrow["tgl_tebus"]){
				$lrow["status"]='TARIK';
			}elseif($lrow["fk_sbg_tebus"]){
				$lrow["status"]='TEBUS';		
			}
		}
		if($lrow["status"]!='TARIK'){
		$data.= '
			<tr>
				<td>'.$lrow["fk_cif"].'</td>
				<td>'.$lrow["nm_customer"].'</td>				
				<td>'.(($is_lapor=="" || $is_lapor=="f")?"e3594":"EN:e3594").'</td>
				<td>'.(($is_lapor=="" || $is_lapor=="f")?$lrow["kategori_usaha"]:"EN:".$lrow["kategori_usaha"]).'</td>
				<td>'.(($is_lapor=="" || $is_lapor=="f")?$lrow["fk_kategori_ukb"]:"EN:".$lrow["fk_kategori_ukb"]).'</td>
				<td>'.(($is_lapor=="" || $is_lapor=="f")?$lrow["kd_pekerjaan_ojk"]:"EN:".$lrow["kd_pekerjaan_ojk"]).'</td>
				<td>'.(($is_lapor=="" || $is_lapor=="f")?$lrow["keterkaitan"]:"KT:".$lrow["keterkaitan"]).'</td>
				<td>'.(($is_lapor=="" || $is_lapor=="f")?$lrow["kd_bidang_usaha_ojk"]:"SE:".$lrow["kd_bidang_usaha_ojk"]).'</td>
				<td>'.(($is_lapor=="" || $is_lapor=="f")?$lrow["kd_kota_ojk"]:"LO:".$lrow["kd_kota_ojk"]).'</td>
							
				<td>'.$lrow["fk_sbg"].'</td>
				<td>'.(($is_lapor=="" || $is_lapor=="f")?$lrow["jenis_pembiayaan"]:"JK:".$lrow["jenis_pembiayaan"]).'</td>
				<td>'.(($is_lapor=="" || $is_lapor=="f")?$lrow["skema_pembiayaan"]:"JK:".$lrow["skema_pembiayaan"]).'</td>
				<td>'.(($is_lapor=="" || $is_lapor=="f")?$lrow["kd_tujuan_transaksi_ojk"]:"JK:".$lrow["kd_tujuan_transaksi_ojk"]).'</td>
				<td>'.($lrow["tgl_cair"]==""?"":date("Y-m-d",strtotime($lrow["tgl_cair"]))).'</td>
				<td>'.($lrow["tgl_jatuh_tempo"]==""?"":"".date("Y-m-d",strtotime($lrow["tgl_jatuh_tempo"]))).'</td>
				<td>'.number_format($lrow['total_hutang']).'</td>
				<td>0</td>
				<td>'.number_format($lrow['saldo_pinjaman']).'</td>
				<td>0</td>
				<td>'.number_format($lrow['saldo_pokok']).'</td>
				<td>'.number_format($lrow['porsi_perusahaan']).'</td>
				<td>'.(($is_lapor=="" || $is_lapor=="f")?"IDR":"MU:IDR").'</td>
				<td>'.number_format($lrow['nilai_dp']).'</td>
				<td>'.number_format($lrow['jf']).'</td>
				<td>'.number_format($total_insentif).'</td>
				<td>'.number_format($rate_tac,2).'</td>
				<td>'.number_format($total_max_tac).'</td>
				<td>'.number_format($max_tac,2).'</td>
				
				<td>'.(($is_lapor=="" || $is_lapor=="f")?$lrow["jenis_bagi_hasil"]:"JK:".$lrow["jenis_bagi_hasil"]).'</td>
				<td>'.number_format($lrow['biaya_penyimpanan']).'</td>
				<td>'.number_format($lrow['rate_flat'],2).'</td>
				<td>0</td>
				<td>'.number_format($lrow['saldo_bunga']).'</td>
				<td>'.number_format($lrow['biaya_admin']).'</td>
				<td>'.number_format($lrow['biaya_provisi']).'</td>
				<td>'.(($is_lapor=="" || $is_lapor=="f")?$lrow["kategori_piutang"]:"JK:".$lrow["kategori_piutang"]).'</td>
				<td>'.(($is_lapor=="" || $is_lapor=="f")?$lrow["kualitas"]:"SF:".$lrow["kualitas"]).'</td>
				<td>'.($lrow["tgl_jt_bayar_terakhir"]==""?"":"".date("Y-m-d",strtotime($lrow["tgl_jt_bayar_terakhir"]))).'</td>
				<td>'.($lrow["tgl_bayar_terakhir"]==""?"":"".date("Y-m-d",strtotime($lrow["tgl_bayar_terakhir"]))).'</td>
				<td>'.$lrow["ang_ke_akhir"].'</td>
				<td>'.number_format($lrow['nominal_bayar_terakhir']).'</td>
				<td>'.(($is_lapor=="" || $is_lapor=="f")?$lrow["jenis_agunan"]:"BJ:".$lrow["jenis_agunan"]).'</td>
				<td>'.number_format($lrow['total_hutang']).'</td>	
				<td>'.(($is_lapor=="" || $is_lapor=="f")?$lrow["metode_cadangan"]:"JK:".$lrow["metode_cadangan"]).'</td>			
				<td>'.number_format($lrow['aset Baik']).'</td>
				<td>'.number_format($lrow['aset Kurang Baik']).'</td>
				<td>'.number_format($lrow['aset Tidak Baik']).'</td>
				<td>0%</td>
				<td>'.$lrow["nm_asuransi"].'</td>
				<td>'.($lrow["lama_asuransi"]*12).'</td>
				<td>'.number_format($lrow['kontribusi_konsumen']).'</td>
				<td>'.number_format($lrow['diskon_kontribusi_konsumen']).'</td>
				
				<td>'.$lrow["no_bpkb"].'</td>
				<td>'.(($is_lapor=="" || $is_lapor=="f")?$lrow["jenis_agunan"]:"BJ:".$lrow["jenis_agunan"]).'</td>	
				<td>'.number_format($lrow['nilai_agunan']).'</td>	
				<td>'.(($is_lapor=="" || $is_lapor=="f")?"e35":"AK:e35").'</td>				
				<td>'.$lrow["no_akta_fidusia"].'</td>
				<td>'.$lrow["no_sertifikat_fidusia"].'</td>
				<td>'.($lrow["tgl_sertifikat_fidusia"]==""?"":"".date("Y-m-d",strtotime($lrow["tgl_sertifikat_fidusia"]))).'</td>
				<td>'.$posisi_penyimpanan.'</td>
			</tr>
		';
		
		$total['total_hutang']+=$lrow["total_hutang"];
		$total['saldo_pinjaman']+=$lrow["saldo_pinjaman"];
		$total['saldo_pokok']+=$lrow["saldo_pokok"];
		$total['saldo_bunga']+=$lrow["saldo_bunga"];
		$total['nilai_dp']+=$lrow["nilai_dp"];
		$grand_total_insentif+=$total_insentif;
		$grand_total_max+=($max_tac*$lrow['biaya_penyimpanan'])/100;
		$total['biaya_penyimpanan']+=$lrow["biaya_penyimpanan"];
		$total['biaya_admin']+=$lrow["biaya_admin"];
		$total['biaya_provisi']+=$lrow["biaya_provisi"];
		$total['aset Baik']+=$lrow["aset Baik"];
		$total['aset Kurang Baik']+=$lrow["aset Kurang Baik"];
		$total['aset Tidak Baik']+=$lrow["aset Tidak Baik"];
		$total['nilai_agunan']+=$lrow["nilai_agunan"];
		}
	}
		$data.= '
			<tr>
				<td>Total</td>
				<td></td>				
				<td></td>				
				<td></td>				
				<td></td>				
				<td></td>				
				<td></td>				
				<td></td>				
				<td></td>				
				<td></td>				
				<td></td>				
				<td></td>				
				<td></td>				
				<td></td>				
				<td></td>				
				<td>'.number_format($total['total_hutang']).'</td>
				<td>0</td>
				<td>'.number_format($total['saldo_pinjaman']).'</td>
				<td>0</td>
				<td>'.number_format($total['saldo_pokok']).'</td>
				<td>'.number_format($total['porsi_perusahaan']).'</td>
				<td></td>				
				<td>'.number_format($total['nilai_dp']).'</td>
				<td></td>				
				<td>'.number_format($grand_total_insentif).'</td>
				<td></td>
				<td></td>
				<td></td>		
				<td></td>										
				<td>'.number_format($total['biaya_penyimpanan']).'</td>
				<td></td>
				<td>0</td>
				<td>'.number_format($total['saldo_bunga']).'</td>
				<td>'.number_format($total['biaya_admin']).'</td>
				<td>'.number_format($total['biaya_provisi']).'</td>
				<td></td>				
				<td></td>				
				<td></td>				
				<td></td>				
				<td></td>				
				<td></td>
				<td></td>				
				<td>'.number_format($total['total_hutang']).'</td>				
				<td></td>				
				<td>'.number_format($total['aset Baik']).'</td>
				<td>'.number_format($total['aset Kurang Baik']).'</td>
				<td>'.number_format($total['aset Tidak Baik']).'</td>
				<td></td>				
				<td></td>				
				<td></td>				
				<td></td>				
				<td></td>				
				
				<td></td>				
				<td></td>				
				<td>'.number_format($total['nilai_agunan']).'</td>				
				<td></td>				
				<td></td>				
				<td></td>				
				<td></td>				
				<td></td>				
			</tr>
		';					
	$data.= '</table>';
	//echo convert_to_csv($data);	
	
	echo $data;
	
	
}

function get_user($username){
	$nm_user=get_rec("tbluser left join tblkaryawan on fk_karyawan=npk","nm_depan","username='".$username."'");
	
	return $nm_user;
}


