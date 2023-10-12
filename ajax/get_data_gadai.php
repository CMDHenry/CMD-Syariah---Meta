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
	
	$fk_produk=$_REQUEST["fk_produk"];
	$tgl_pengajuan=$_REQUEST["tgl_pengajuan"];
	$total_nilai_pinjaman=round($_REQUEST["total_nilai_pinjaman"]);	//otr 
    $rate_flat_input=$_REQUEST["rate_flat_input"];
	$biaya_admin=$_REQUEST["biaya_admin"];
	$lama_pinjaman=$_REQUEST["lama_pinjaman"];	
	$is_new=$_REQUEST["is_new"];
	$fk_cabang=$_REQUEST["fk_cabang"];
	$addm_addb=$_REQUEST["addm_addb"];
	$rate_asuransi=$_REQUEST["rate_asuransi"];
	$is_asuransi_tunai=$_REQUEST["is_asuransi_tunai"];
	$diskon_admin=$_REQUEST["diskon_admin"];
	$nilai_dp=$_REQUEST["nilai_dp"];
	$tahun_asr_jiwa=$_REQUEST["tahun_asr_jiwa"];
	$all_risk_dari_tahun=$_REQUEST["all_risk_dari_tahun"];
	$all_risk_sampai_tahun=$_REQUEST["all_risk_sampai_tahun"];
	$tlo_dari_tahun=$_REQUEST["tlo_dari_tahun"];
	$tlo_sampai_tahun=$_REQUEST["tlo_sampai_tahun"];
	$biaya_polis=$_REQUEST["biaya_polis"];
	$tjh_3=$_REQUEST["tjh_3"];
	$pa_supir=$_REQUEST["pa_supir"];
	$pa_penumpang=$_REQUEST["pa_penumpang"];
	
	$fk_fatg=$_REQUEST["fk_fatg"];	
	
/*	$fk_produk='43';
	$total_nilai_pinjaman=196100000;
	$nilai_dp=69457330;
	$lama_pinjaman=49;
	$all_risk_dari_tahun='';
	$all_risk_sampai_tahun='';
	$tlo_dari_tahun=1;
	$tlo_sampai_tahun=1;
	$fk_fatg='101.2300744';
*/	
/*	
	$fk_produk='22';
	$total_nilai_pinjaman=14000000;
	$nilai_dp=4000000;
	$lama_pinjaman=12;
	$tlo_dari_tahun=1;
	$tlo_sampai_tahun=1;
	$fk_fatg='104.2300410';	
*/
	
	$biaya_survey=$_REQUEST["biaya_survey"];
	$biaya_asuransi_syariah=$_REQUEST["biaya_asuransi_syariah"];
	$biaya_penjaminan_syariah=$_REQUEST["biaya_penjaminan_syariah"];
	$biaya_pembebanan_agunan=$_REQUEST["biaya_pembebanan_agunan"];
	$biaya_provisi=$_REQUEST["biaya_provisi"];
	$biaya_lain=$_REQUEST["biaya_lain"];
	$biaya_adm_sales=$_REQUEST["biaya_adm_sales"];
	$total_biaya_lain=$biaya_survey+$biaya_asuransi_syariah+$biaya_penjaminan_syariah+$biaya_pembebanan_agunan+$biaya_provisi+$biaya_lain;
	
	$is_admin_tunai=$_REQUEST["is_admin_tunai"];
	
	
	$query_brg="
	select * from (
		select fk_barang,fk_fatg,status_fatg,status_barang,fk_cif,kondisi_unit,tahun from data_gadai.tbltaksir_umum_detail 
		left join data_gadai.tbltaksir_umum on fk_fatg=no_fatg
		where fk_fatg='".$fk_fatg."'
		)as tbltaksir
	left join tblbarang on fk_barang=kd_barang
	left join tbljenis_barang on fk_jenis_barang=kd_jenis_barang
	";
	$lrs_brg=pg_query($query_brg);
	$lrow_brg=pg_fetch_array($lrs_brg);
			
	$fk_jenis_barang=$lrow_brg["fk_jenis_barang"];		
	$kategori=$lrow_brg["kategori"];	
	$status_fatg=$lrow_brg["status_fatg"];	
	$status_barang=$lrow_brg["status_barang"];	
	$kondisi_unit=$lrow_brg["kondisi_unit"];	
	$fk_cif=$lrow_brg["fk_cif"];
	$fk_tipe=$lrow_brg["fk_tipe"];		
	$tahun_unit=$lrow_brg["tahun"];			
	
	$query_tipe="
	select * from tbltipe
    LEFT JOIN tblmodel ON fk_model = kd_model
    LEFT JOIN tblmerek ON fk_merek = kd_merek
	where kd_tipe='".$fk_tipe."'
	";
	$lrs_tipe=pg_query($query_tipe);
	$lrow_tipe=pg_fetch_array($lrs_tipe);
	$is_provisi_pokok=$lrow_tipe["is_provisi_pokok"];			
	
	
	if($is_new=="undefined")$is_new=false;
	$query="
	select * from tblproduk
	left join tblrate on kd_rate=fk_rate
	where kd_produk='".$fk_produk."' ";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	if($is_new==true){
		$query1="select * from tblcabang where kd_cabang='".$fk_cabang."'";
		$lrs1=pg_query($query1);
		$lrow1=pg_fetch_array($lrs1);
		
/*		switch($lrow1["jenis_cabang"]){
			case "Cabang" : 				
				$default='1';
			break;
			case "Unit" : 
				$default='1';
			break;
			case "Pos" : 
				$default='1';
			break;
		}		
*/		
		$rate_flat_input=$lrow["min_rate"];		
	}
	if(strstr(strtoupper($lrow["nm_produk"]),"KONVERSI"))$is_konversi='t';
	
	if($kondisi_unit=='Bekas' && $kategori=='R2'){
		$is_karyawan=get_rec("tblcustomer","is_karyawan","no_cif='".$fk_cif."'");
		$is_karyawan='f';
		if($is_karyawan=='t')$lwhere_admin=" and jenis='KARYAWAN' ";
		else $lwhere_admin=" and jenis='NONKARYAWAN' ";
	}else{
		$lwhere_admin=" and jenis='ALL' ";
	}
	$query1="
	select * from tblproduk_detail_biaya_admin
	where fk_produk='".$fk_produk."' and ".$lama_pinjaman." between dari and ke ".$lwhere_admin;
	$lrs1=pg_query($query1);
	$lrow1=pg_fetch_array($lrs1);
	//echo $query1.'  <br>';
	
	$jenis_admin=$lrow["jenis_admin"];	
	if($jenis_admin=="Persentase")$biaya_admin_awal=round($lrow1["nilai"]*$total_nilai_pinjaman/100);		
	else $biaya_admin_awal=$lrow1["nilai"];
	
	$biaya_admin=$biaya_admin_awal-$diskon_admin;
	
	$jenis_produk=$lrow["jenis_produk"];
	$min_rate=$lrow["min_rate"];
	$jumlah_hari=$lrow["jumlah_hari"];
	
	$tgl_pengajuan=convert_date_english(today);
	
	if($kategori=='R2'){	
		$desimal=2;
	}else $desimal=3;
		
	if($jenis_produk=='1'){	
		
		//hitung asuransi		
		if(!$lama_pinjaman)$lama_pinjaman=12;
		$tenor=ceil($lama_pinjaman/12);		
		if($tenor==0)$tenor=1;
		if($is_konversi=='t')$tenor=$tlo_sampai_tahun;//kalau konversi tenor ikut lama asr
		
		$total_persen_asr=0;		
		for($i=1;$i<=($tenor);$i++){
			$tahun_ke=$i;
			$sisa_bulan=12;
			if($kategori!='R4'){//R4 SELALU FULL tenor
				if($i==$tenor)$sisa_bulan=12-($tenor*12-$lama_pinjaman);
			}
						
			$jenis_asuransi="";
			if($tahun_ke>=$all_risk_dari_tahun && $tahun_ke<=$all_risk_sampai_tahun){
				$jenis_asuransi='All Risk';
			}elseif($tahun_ke>=$tlo_dari_tahun && $tahun_ke<=$tlo_sampai_tahun){
				$jenis_asuransi='TLO';
			}
			
			if($is_konversi=='t'){
				$tahun_ke+=(date("Y",strtotime(today_db))-$tahun_unit);//konversi ambil umur unit
			}
			
			$query2="
			select * from tblproduk_detail_nilai_pertangungan
			where fk_produk='".$fk_produk."' and tahun_ke ='".$tahun_ke."' order by tahun_ke";
			$lrs2=pg_query($query2);
			$lrow2=pg_fetch_array($lrs2);
			//echo $query2.'  <br>';
			$persen_nilai_barang=$lrow2["persentase"];		
			
			$pengali=1;
			if($is_konversi=='t'){
				$pengali=$persen_nilai_barang/100;//kalau konversi, otr disusut
			}
			
			if($status_barang=='Datun' || $kategori=='R2'){
				if(!$all_risk_sampai_tahun && $tlo_sampai_tahun){
					$lasr=" and tenor='".$lama_pinjaman."'";//DATUN R2 TLO cukup ambil sekali saja sesuai tenor						
					$i=$lama_pinjaman; 
					$pinjaman_asr=(($total_nilai_pinjaman-$nilai_dp)*$persen_nilai_barang/100);
				}else{
					$lasr=" and tenor='12'"; //klo kombinasi ambil per tenor 12		
					$pinjaman_asr=($total_nilai_pinjaman*$persen_nilai_barang/100);
					$is_asr_datun_kombinasi='t';
				}				
				$sisa_bulan=12;				
			}else{
				$pinjaman_asr=($total_nilai_pinjaman*$persen_nilai_barang/100);
			}						
			//echo $pinjaman_asr.'aaaa<br>';
			$persen_asr=get_rec("tblproduk_detail_asuransi","persentase"," fk_produk='".$fk_produk."' and ".$pinjaman_asr." between dari and ke and fk_jenis_barang='".$fk_jenis_barang."' and jenis_asuransi='".$jenis_asuransi."' ".$lasr);			
			//echo "fk_produk='".$fk_produk."' and ".$pinjaman_asr." between dari and ke and fk_jenis_barang='".$fk_jenis_barang."' and jenis_asuransi='".$jenis_asuransi."' ".$lasr."<br>";	
			
			if($is_asr_datun_kombinasi=='t'){
				$nilai_asuransi_datun+=round($persen_asr*$pinjaman_asr/100);//rumus datun kombinasi beda sendiri
				//persen nya pakai persen full per tenor 12			
				//echo $pinjaman_asr.'*'.$persen_asr.'='.round($persen_asr*$pinjaman_asr/100).'<br>';
			}			
			
			//echo $persen_asr.'*'.($persen_nilai_barang/100).'%*'.($sisa_bulan/12).'<br>';	
			if($status_barang=='Bekas')$sisa_bulan=12;//kalau bekas persennya full
			if($is_konversi!='t')$persen_asr=$persen_asr*($persen_nilai_barang/100)*($sisa_bulan/12);//kalau konversi ga perlu kali lagi						
			
			$total_persen_asr+=str_replace(",","",convert_money("",$persen_asr,$desimal));						
		}		
			
		
		//$rate_asuransi=$total_persen_asr;
		$rate_asuransi=str_replace(",","",convert_money("",$total_persen_asr,$desimal));				
		//end asuransi				
		
		$nilai_ap_customer=$total_nilai_pinjaman-$nilai_dp;
		//$nilai_ap_customer-=$total_biaya_lain; //biaya lain hanya info
		
		$pokok_hutang=$total_nilai_pinjaman;
		$pokok_hutang-=$nilai_dp;		
						
		if($kategori=='R2'){						
			$pokok_asr_kendaraan=$total_nilai_pinjaman-$nilai_dp+$biaya_polis;
			//if($is_admin_tunai=='false'){// ceklist tunai cuma info saja
			$pokok_asr_kendaraan+=$biaya_admin;
			$rate_pengurang_asr=(100-$rate_asuransi)/100;
			$nilai_asuransi=round($pokok_asr_kendaraan/$rate_pengurang_asr-$pokok_asr_kendaraan);	
			//echo $pokok_asr_kendaraan.'/'.$rate_pengurang_asr.'<br>';	
			
			$pokok_asr_jiwa=$pokok_asr_kendaraan;		
			$pokok_hutang+=$biaya_admin;	
			$pokok_hutang+=$biaya_polis;
		}elseif($kategori=='R4'){						
			$pokok_asr_kendaraan=$total_nilai_pinjaman*$pengali;			
			$nilai_asuransi=round($pokok_asr_kendaraan*$rate_asuransi/100);		
			//echo $total_nilai_pinjaman.'*'.$pengali.'<br>';
			//echo $pokok_asr_kendaraan.'*'.$rate_asuransi.'<br>';
						
			$pokok_asr_jiwa=$total_nilai_pinjaman-$nilai_dp;	
			//if($is_admin_tunai=='true' ){// ga dipakai buat hitung. hanya info
			if($status_barang!='Datun'){
				$nilai_ap_customer-=$biaya_admin;
				$nilai_ap_customer-=($biaya_polis);
			}else{
				$pokok_hutang+=$biaya_admin;		
				$pokok_hutang+=$biaya_polis;
			}
			if($is_provisi_pokok=='t'){//merek tertentu provisi
				$pokok_hutang+=$biaya_provisi;
			}
		}		
		
		if($status_barang=='Datun'){
			$rate_pengurang_asr=(100-$rate_asuransi)/100;
			$pokok_asr_kendaraan=$total_nilai_pinjaman-$nilai_dp+$biaya_admin;
			$nilai_asuransi=round($pokok_asr_kendaraan/$rate_pengurang_asr-$pokok_asr_kendaraan);

			if($is_asr_datun_kombinasi=='t')$nilai_asuransi=$nilai_asuransi_datun;//datun kombi beda cara hitung
		}
		
		$pokok_hutang+=$nilai_asuransi;
		
		//if($is_asuransi_tunai=='true'){// ga dipakai buat hitung. hanya info
			//$nilai_ap_customer-=$nilai_asuransi;
		//}										
		
		//asuransi jiwa
		$query3="
		select * from tblproduk_detail_asuransi_jiwa
		where fk_produk='".$fk_produk."' and tahun_ke <='".$tahun_asr_jiwa."' order by tahun_ke";
		$lrs3=pg_query($query3);
		//echo $query3;
		while($lrow3=pg_fetch_array($lrs3)){
			$total_persen_asr_jiwa+=$lrow3["persentase"];
		}		
		$nilai_asuransi_jiwa=round($pokok_asr_jiwa*$total_persen_asr_jiwa/100);		
		
		if($status_barang=='Datun'){
			$rate_pengurang_asr_jiwa=(100-$total_persen_asr_jiwa)/100;
			$nilai_asuransi_jiwa=round($pokok_asr_kendaraan/$rate_pengurang_asr_jiwa-$pokok_asr_kendaraan);//
		}
		//echo $nilai_asuransi_jiwa.'aa';
		$pokok_hutang+=$nilai_asuransi_jiwa;		
		//end jiwa		
		
		$total_asr_lain=$tjh_3+$pa_supir+$pa_penumpang;		
		$pokok_hutang+=$total_asr_lain;
				
		$biaya_penyimpanan=round(($pokok_hutang*$lama_pinjaman*$rate_flat_input/100*(30/$jumlah_hari)));
		$total_hutang=$pokok_hutang+$biaya_penyimpanan;	
		
		$angsuran=round($total_hutang/$lama_pinjaman);
		$angsuran=$angsuran/1000;
		$angsuran=ceil($angsuran)*1000;			
		//angsuran dibuat bulat		
		
		//lalu hitung ulang untuk cari rate flat dari angsuran bulat 
		$total_angsuran=$angsuran*$lama_pinjaman;
		$total_bunga_bulat=$total_angsuran-$pokok_hutang;
		$rate_flat=$total_bunga_bulat/($lama_pinjaman*(30/$jumlah_hari))/$pokok_hutang*100;		
		$rate_flat=str_replace(",","",convert_money("",$rate_flat,8));		
		$biaya_penyimpanan=$total_bunga_bulat;
		$total_hutang=$pokok_hutang+$biaya_penyimpanan;	
		
		if($addm_addb=='M'){
			$nilai_ap_customer-=$angsuran;
			$nilai_ap_customer-=$biaya_adm_sales;
			$nilai_ap_customer-=$biaya_provisi;
									
			$tgl_jatuh_tempo=convert_date_indonesia(get_next_month(convert_date_english(today),($lama_pinjaman-1)));
		}elseif($addm_addb=='B'){
			$nilai_ap_customer-=$biaya_adm_sales;
						
			$tgl_jatuh_tempo=convert_date_indonesia(get_next_month(convert_date_english(today),$lama_pinjaman));
		}else{
			$tgl_jatuh_tempo_satu="";
		}
				
	}else{
		$biaya_penyimpanan=round(($total_nilai_pinjaman*$lama_pinjaman*$rate_flat/100*(30/$jumlah_hari)));
		$nilai_ap_customer=$total_nilai_pinjaman-$biaya_admin;
		$total_hari=$lama_pinjaman*30-1;
		$tgl_jatuh_tempo=date("d/m/Y",strtotime('+ '.$total_hari.' day',strtotime($tgl_pengajuan)));
	}
	
	$biaya_notaris=0;//hitung fidusia
	$fk_partner_notaris=$_REQUEST["fk_partner_notaris"];
	if($fk_partner_notaris){
		$query4="
		select * from tblfidusia_detail
		left join tblfidusia on kd_fidusia =fk_fidusia
		where fk_partner='".$fk_partner_notaris."' and ".$total_hutang." between dari and ke ";
		$lrs4=pg_query($query4);
		$lrow4=pg_fetch_array($lrs4);
		$biaya_notaris=$lrow4["nilai"];
	}		
	
	echo $biaya_penyimpanan.',';
	echo $biaya_admin.',';
	echo $tgl_jatuh_tempo.',';//
	echo $nilai_ap_customer.',';
	echo $rate_flat.',';
	echo $rate_asuransi.',';
	echo $pokok_hutang.',';
	echo $total_hutang.',';
	echo $nilai_asuransi.',';
	echo $angsuran.',';
	echo $min_rate.',';
	echo $nilai_asuransi_jiwa.',';
	echo $rate_flat_input.',';
	echo $biaya_notaris.',';
	echo $biaya_admin_awal.',';
	echo $abc.',';//ga pake
	echo $min_provisi.',';//ga pake
	echo $bunga_dimuka.',';

	
}
