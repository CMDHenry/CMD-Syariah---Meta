<?php

include_once("report.php");

function filter_request(){
	global $jenis_report,$showCab,$showTgl,$no_cif,$nm_customer,$no_id,$showPeriode;
	$showPeriode='t';
	$showCab='t';
	//$showTgl='t';
	$jenis_report=trim($_REQUEST["jenis_report"]);
	
}

function create_filter(){
	global $jenis_report,$no_cif,$no_id,$nm_customer,$is_tgl_jatuh_tempo;
?>
   <tr bgcolor="efefef">
        <td style="padding:0 5 0 5" width="20%">Jenis report</td>
        <td style="padding:0 5 0 5" width="30%">
        <select name="jenis_report">
	       <option value=""<?= (($jenis_report=='')?"selected":"") ?>>-</option>
           <option value="D01"<?= (($jenis_report=='D01')?"selected":"") ?>>D01</option>
           <option value="D02"<?= (($jenis_report=='D02')?"selected":"") ?>>D02</option>           
           <option value="A01"<?= (($jenis_report=='A01')?"selected":"") ?>>A01</option>
           <option value="F01"<?= (($jenis_report=='F01')?"selected":"") ?>>F01</option>
           <option value="P01"<?= (($jenis_report=='P01')?"selected":"") ?>>P01</option>
           
        </select>
        </td>
        <td style="padding:0 5 0 5" width="20%"></td>
        <td style="padding:0 5 0 5" width="30%"></td>
     </tr>
<?	
}




function excel_content(){
	global $fk_cabang,$nm_cabang,$jenis_report,$periode_awal,$periode_akhir;
	
	if($periode_awal != '' &&  $periode_akhir != ''){
		//$lwhere.=" (tgl_lunas > '".$tgl."' or tgl_lunas is null) and tgl_cair <='".$tgl."'";
		$lwhere.=" tgl_cair between '".$periode_awal." 00:00:00' and '".$periode_akhir." 23:59:59'";
		$tgl=$periode_akhir;
	}
	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cabang = '".$fk_cabang."' ";
	}
	
	if($jenis_report =='D01'){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" jenis_customer = '0' or ( jenis_customer = '1' and tgl_input_customer <='2023-08-31')";
	}elseif($jenis_report =='D02'){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" jenis_customer = '1' and tgl_input_customer >'2023-08-31' ";//difilter karena yang awal sudah terlapor di D01
	}
		
	$Ym=date("Ym",strtotime($tgl));
	$mM=date("m",strtotime($tgl));
	$Yy=date("Y",strtotime($tgl));
	if ($lwhere!="") $lwhere=" where ".$lwhere;
	$Y_m=date("Y-m",strtotime($tgl));

	$query = "
	select * from(
		select replace(no_cif, '.', '')as no_cif_replace,* from tblinventory
		left join tblcustomer on no_cif=fk_cif
		left join tblkelurahan on fk_kelurahan_ktp=kd_kelurahan
		left join tblkecamatan on fk_kecamatan=kd_kecamatan
		left join tblkota on fk_kota=kd_kota		
		left join(
			select nm_kelurahan as nm_kelurahan_badan, nm_kecamatan as nm_kecamatan_badan, kd_kota_slik as kd_kota_slik_badan,kd_pos as kd_pos_badan,kd_kelurahan as kd_kelurahan_badan from tblkelurahan 
			left join tblkecamatan on fk_kecamatan=kd_kecamatan
			left join tblkota on fk_kota=kd_kota		
		)as tblkelurahan_badan on fk_kelurahan_badan_usaha =kd_kelurahan_badan
		".$lwhere." --and fk_sbg='20101230700464'
	)as tblmain
	left join data_gadai.tblproduk_cicilan on fk_sbg=no_sbg
	left join (select kd_produk,rate_denda_ganti_rugi from tblproduk)as tblproduk on tblproduk_cicilan.fk_produk=kd_produk
	left join(
		select distinct on(fk_sbg)fk_sbg as fk_sbg_bayar, tgl_bayar as tgl_bayar_terakhir,nilai_angsuran as nominal_bayar_terakhir,tgl_jatuh_tempo as tgl_jt_bayar_terakhir,angsuran_ke as ang_ke_akhir from data_fa.tblangsuran 
		where (tgl_bayar is not null and tgl_bayar<='".$tgl."')
		and angsuran_ke>0 order by fk_sbg,angsuran_ke desc 
	)as tblbayar on no_sbg=fk_sbg_bayar		
	
	left join(
		select distinct on(fk_sbg)fk_sbg as fk_sbg_od,date_part('day','".$tgl."' -tgl_jatuh_tempo)as ovd from data_fa.tblangsuran 
		where (tgl_bayar is null or(tgl_bayar is not null and tgl_bayar>'".$tgl."'))
		and angsuran_ke>0 order by fk_sbg,angsuran_ke asc 
	)as tblod on no_sbg=fk_sbg_od			
	
	left join (
		select kd_partner as kd_asuransi, nm_partner as nm_asuransi from tblpartner
	)as tblasuransi on fk_partner_asuransi=kd_asuransi	
	
	left join(
		select viewkendaraan.no_rangka,viewkendaraan.no_fatg,nm_tipe,kategori,status_barang,viewkendaraan.fk_partner_dealer,fk_tujuan_transaksi,no_bpkb,perpanjangan_ke,tgl_bpkb from viewkendaraan 		
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
	
	left join (
		select fk_sbg as fk_sbg_denda,sum(nilai_bayar_denda+nilai_bayar_denda2)as denda_bayar from data_fa.tblpembayaran_cicilan where tgl_batal is null and tgl_bayar like '".$Y_m."%'
		group by fk_sbg
	)as tbldenda on no_sbg=fk_sbg_denda
	left join(
		select distinct on (fk_sbg)fk_sbg as fk_sbg_macet,tgl_bayar as tgl_macet from data_fa.tblangsuran
		where tgl_bayar is not null
		order by fk_sbg,tgl_bayar desc
	)as tarik on no_sbg=fk_sbg_macet
	left join (
		select fk_sbg as sbg_cadangan,nilai_agunan from data_fa.tblcadangan_piutang_detail
		left join data_fa.tblcadangan_piutang on kd_voucher=fk_voucher
		where bulan='".$mM."' and tahun='".$Yy."'
	)as tblcadangan on no_sbg=sbg_cadangan
	left join(
		select fk_sbg as fk_sbg_od1,sum(pokok_jt)as tunggakan_pokok,sum(bunga_jt)as tunggakan_bunga,count(1)as byk_tunggakan from data_fa.tblangsuran 
		where (tgl_bayar is null or(tgl_bayar is not null and tgl_bayar>'".$tgl."'))
		and '".$tgl."' >tgl_jatuh_tempo
		and angsuran_ke>0 
		group by fk_sbg
	)as tblod1 on no_sbg=fk_sbg_od1	
	
	left join(
		select distinct on (fk_sbg)(saldo_pinjaman)as saldo_pinjaman_mtd ,fk_sbg as fk_sbg_angs from data_fa.tblangsuran where (tgl_bayar is null or(tgl_bayar is not null and tgl_bayar>'".$tgl."'))
		order by fk_sbg, angsuran_ke asc
	)as angs on no_sbg=fk_sbg_angs
	left join(
		select fk_sbg as fk_sbg_pokok,sum(pokok_jt)as saldo_pokok from data_fa.tblangsuran 
		where (tgl_bayar is null or(tgl_bayar is not null and tgl_bayar>'".$tgl."'))
		and angsuran_ke>0 
		group by fk_sbg
	)as tblpokok on no_sbg=fk_sbg_pokok	
			
	";
	
	$lrs = pg_query($query);
	//showquery($query);
	
	if($jenis_report=='D01'){
		echo 	
		'<table border="1">
			 <tr>
				<td align="center">Flag</td>
				<td align="center">NOMOR CIF</td>	
				<td align="center">JENIS IDENTITAS</td>									
				<td align="center">NOMOR IDENTITAS</td>
				<td align="center">NAMA KTP</td>
				<td align="center">NAMA LENGKAP</td>
				<td align="center">KODE PENDIDIKAN</td>
				<td align="center">JENIS KELAMIN</td>
				<td align="center">TEMPAT LAHIR</td>
				<td align="center">TANGGAL LAHIR</td>										
				<td align="center">NPWP</td>
				
				<td align="center">ALAMAT</td>			
				<td align="center">KELURAHAN</td>			
				<td align="center">KECAMATAN</td>
				<td align="center">KODE KAB</td>
				<td align="center">KODE POS</td>
				
				<td align="center">NO TELP</td>
				<td align="center">NO HP</td>
				<td align="center">EMAIL</td>			
				<td align="center">KODE NEGARA</td>
				<td align="center">KODE PEKERJAAN</td>									
				<td align="center">TEMPAT KERJA</td>
				<td align="center">KODE BID USAHA</td>
				<td align="center">ALAMAT BKRJA</td>
				<td align="center">PENGHASILAN</td>
				<td align="center">KODE SUMBER</td>								
				
				<td align="center">JUMLAH TANGGUNGGAN</td>
				<td align="center">KODE HUB PELAPOR</td>			
				<td align="center">KODE GOL DEBITUR</td>
				<td align="center">Status</td>
				<td align="center">NO KTP PASANGAN</td>
				<td align="center">NAMA PASANGAN</td>
				<td align="center">TANGGAL LAHIR PASANGAN</td>
				<td align="center">PERJANJIANN PISAH HARTA</td>
				<td align="center">MELANGGGAR BMPK</td>
				<td align="center">MELAMPAU MPK</td>
				<td align="center">NAMA IBU</td>
				<td align="center">KODE CAB</td>
				<td align="center">OPERASI DATA</td>			

			  </tr>
		';
		$no=1;
		
		while($lrow=pg_fetch_array($lrs)){		
			$lrow["jenis_id"]='1';
			
			if($lrow["is_terkait"]=='t'){
				$lrow["keterkaitan"]='T9';
			}else{
				$lrow["keterkaitan"]='N';
			}
			
			$Ym_tgl_input_customer=date("Ym",strtotime($lrow["tgl_input_customer"]));
			
			$Ym_log_action_date=get_rec("tblcustomer_log","log_action_date","no_cif='".$lrow["fk_cif"]."' and log_action_mode='UB' and log_action_date like '%".$Ym."%'","pk_id_log desc");
			
			if($Ym_tgl_input_customer==$Ym){//cek apakah sama periode report sama dengan tgl input customer 
				$lrow["operasi_data"]='C';
			}elseif($Ym_log_action_date){
				$lrow["operasi_data"]='U';
			}else{
				$lrow["operasi_data"]='N';
			}
			
			$lrow["nm_kecamatan"]=str_replace('(','',$lrow["nm_kecamatan"]);
			$lrow["nm_kecamatan"]=str_replace(')','',$lrow["nm_kecamatan"]);			
			
			echo '
				<tr>
					<td valign="top">D</td>
					<td valign="top">'.$lrow["no_cif_replace"].'</td>
					<td valign="top">'.$lrow["jenis_id"].'</td>
					<td valign="top">&nbsp;'.$lrow["no_id"].'</td>				
					<td valign="top">'.$lrow["nm_customer"].'</td>
					<td valign="top">'.$lrow["nm_customer"].'</td>
					<td valign="top">'.$lrow["fk_pendidikan"].'</td>
					<td valign="top">'.$lrow["jenis_kelamin"].'</td>
					<td valign="top">'.$lrow["tempat_lahir"].'</td>
					<td valign="top">'.($lrow["tgl_lahir"]==""?"":date("Ymd",strtotime($lrow["tgl_lahir"]))).'</td>								
					<td valign="top">&nbsp;'.$lrow["npwp"].'</td>
					
					<td valign="top">'.$lrow["alamat_ktp"].'</td>
					<td valign="top">'.$lrow["nm_kelurahan"].'</td>						
					<td valign="top">'.$lrow["nm_kecamatan"].'</td>
					<td valign="top">'.$lrow["kd_kota_slik"].'</td>
					<td valign="top">'.$lrow["kd_pos"].'</td>
					<td valign="top">'.($lrow["telepon"]==""?"0":$lrow["telepon"]).'</td>
					<td valign="top">&nbsp;'.$lrow["no_hp"].'</td>
					<td valign="top">'.$lrow["email"].'</td>
					<td valign="top">ID</td>
					<td valign="top">&nbsp;'.$lrow["fk_pekerjaan"].'</td>
					<td valign="top">'.$lrow["nm_tempat_kerja"].'</td>
					<td valign="top">&nbsp;'.$lrow["fk_bidang_usaha"].'</td>
					<td valign="top">'.$lrow["alamat_bekerja"].'</td>
					<td valign="top">'.$lrow["penghasilan"].'</td>
					<td valign="top">'.$lrow["sumber_penghasilan"].'</td>
					<td valign="top">'.$lrow["jumlah_tanggungan"].'</td>
					<td valign="top">'.$lrow["keterkaitan"].'</td>
					<td valign="top">S14</td>				
					<td valign="top">'.$lrow["status_pernikahan"].'</td>
					<td valign="top">&nbsp;'.$lrow["no_ktp_pasangan"].'</td>
					<td valign="top">'.$lrow["nm_pasangan"].'</td>
					<td valign="top">'.($lrow["tgl_lahir_pasangan"]==""?"":date("Ymd",strtotime($lrow["tgl_lahir_pasangan"]))).'</td>
					<td valign="top">T</td>				
					<td valign="top">T</td>				
					<td valign="top">T</td>				
					
					<td valign="top">'.$lrow["nm_ibu"].'</td>
					<td valign="top">000</td>				
					<td valign="top">'.$lrow["operasi_data"].'</td>
				</tr>
			';	
			$no++;
		}
	}
	
	
	if($jenis_report=='D02'){
		echo 	
		'<table border="1">
			 <tr>
				<td align="center">Flag</td>
				<td align="center">NOMOR CIF</td>	
				<td align="center">NOMOR IDENTITAS</td>
				<td align="center">NAMA BADAN USAHA</td>
				<td align="center">KODE BADAN USAHA</td>
				<td align="center">TEMPAT PENDIRIAN</td>
				<td align="center">NOMOR AKTA PENDIRIAN</td>	
				<td align="center">TANGGAL AKTA PENDIRIAN</td>										
				<td align="center">NOMOR AKTA TERAKHIR</td>	
				<td align="center">TANGGAL AKTA TERAKHIR</td>										
				<td align="center">NO TELP</td>
				<td align="center">NO HP</td>
				<td align="center">EMAIL</td>		
				
				<td align="center">ALAMAT</td>			
				<td align="center">KELURAHAN</td>			
				<td align="center">KECAMATAN</td>
				<td align="center">KODE KAB</td>
				<td align="center">KODE POS</td>
					
				<td align="center">KODE NEGARA</td>
				<td align="center">KODE BIDANG USAHA</td>									

				<td align="center">KODE HUB PELAPOR</td>			
				<td align="center">MELANGGGAR BMPK</td>
				<td align="center">MELAMPAU MPK</td>
				<td align="center">GO PUBLIC</td>
				<td align="center">KODE GOL DEBITUR</td>									
				<td align="center">PERINGKAT/RATING DEBITUR</td>									
				<td align="center">LEMBAGA PEMERINGKAT RATING</td>									
				<td align="center">TANGGAL PEMERINGKAT</td>		
				<td align="center">NAMA GROUP DEBITUR</td>									
											
				<td align="center">KODE CAB</td>
				<td align="center">OPERASI DATA</td>			

			  </tr>
		';
		$no=1;
		
		while($lrow=pg_fetch_array($lrs)){		
			$lrow["jenis_id"]='1';
			
			if($lrow["is_terkait"]=='t'){
				$lrow["keterkaitan"]='T9';
			}else{
				$lrow["keterkaitan"]='N';
			}
			
			$Ym_tgl_input_customer=date("Ym",strtotime($lrow["tgl_input_customer"]));
			
			$Ym_log_action_date=get_rec("tblcustomer_log","log_action_date","no_cif='".$lrow["fk_cif"]."' and log_action_mode='UB' and log_action_date like '%".$Ym."%'","pk_id_log desc");
			
			if($Ym_tgl_input_customer==$Ym){//cek apakah sama periode report sama dengan tgl input customer 
				$lrow["operasi_data"]='C';
			}elseif($Ym_log_action_date){
				$lrow["operasi_data"]='U';
			}else{
				$lrow["operasi_data"]='N';
			}
			
			$lrow["nm_kecamatan"]=str_replace('(','',$lrow["nm_kecamatan"]);
			$lrow["nm_kecamatan"]=str_replace(')','',$lrow["nm_kecamatan"]);		
			
			$inisial_badan=substr($lrow["nm_badan_usaha"],0,2);
			if($inisial_badan=='PT')$lrow["bentuk_badan_usaha"]='18';
			elseif($inisial_badan=='CV')$lrow["bentuk_badan_usaha"]='02';
			
			echo '
				<tr>
					<td valign="top">D</td>
					<td valign="top">'.$lrow["no_cif_replace"].'</td>
					<td valign="top">&nbsp;'.$lrow["npwp_badan_usaha"].'</td>				
					<td valign="top">'.$lrow["nm_badan_usaha"].'</td>
					<td valign="top">'.$lrow["bentuk_badan_usaha"].'</td>
					<td valign="top">'.$lrow["tempat_pendirian"].'</td>
					<td valign="top">'.$lrow["no_akta_pendirian"].'</td>
					<td valign="top">'.($lrow["tgl_akta_pendirian"]==""?"":date("Ymd",strtotime($lrow["tgl_akta_pendirian"]))).'</td>
					<td valign="top">'.$lrow["no_akta_terakhir"].'</td>
					<td valign="top">'.($lrow["tgl_akta_terakhir"]==""?"":date("Ymd",strtotime($lrow["tgl_akta_terakhir"]))).'</td>
					<td valign="top">'.($lrow["telp_badan_usaha"]==""?"0":$lrow["telp_badan_usaha"]).'</td>
					<td valign="top">&nbsp;'.$lrow["no_hp_badan_usaha"].'</td>
					<td valign="top">'.$lrow["email_badan_usaha"].'</td>
					
					<td valign="top">'.$lrow["alamat_badan_usaha"].'</td>
					<td valign="top">'.$lrow["nm_kelurahan_badan"].'</td>						
					<td valign="top">'.$lrow["nm_kecamatan_badan"].'</td>
					<td valign="top">'.$lrow["kd_kota_slik_badan"].'</td>
					<td valign="top">'.$lrow["kd_pos_badan"].'</td>
					<td valign="top">ID</td>
					<td valign="top">&nbsp;'.$lrow["fk_bidang_usaha"].'</td>
					<td valign="top">'.$lrow["keterkaitan"].'</td>					
					<td valign="top">T</td>				
					<td valign="top">T</td>				
					<td valign="top">T</td>	
					<td valign="top">S14</td>										
					<td valign="top"></td>				
					<td valign="top"></td>				
					<td valign="top"></td>				
					<td valign="top"></td>				
					<td valign="top">000</td>				
					<td valign="top">'.$lrow["operasi_data"].'</td>
				</tr>
			';	
			$no++;
		}
	}	
	if($jenis_report=='A01'){
		echo 	
		'<table border="1">
			 <tr>
				<td align="center">FLAG</td>
				<td align="center">NOMOR AGUNAN</td>	
				<td align="center">NOREK FASILITAS</td>									
				<td align="center">CIF</td>
				<td align="center">KODE JENIS SEGMEN</td>
				<td align="center">KODE STATUS AGUNAN</td>
				<td align="center">KODE JENIS AGUNAN</td>
				<td align="center">PERINGKAT AGUNAN</td>
				<td align="center">KODE LEMBAGA PEMERINGKAT</td>
				<td align="center">KODE JENIS PENGIKAT</td>			
							
				<td align="center">TANGGAL SERTIFIKAT FIDUSIA</td>
				<td align="center">NAMA PEMILIK AGUNAN</td>			
				<td align="center">BUKTI KEPEMILIKAN</td>			
				<td align="center">ALAMAT AGUNAN</td>
				<td align="center">KODE KAB</td>
				<td align="center">NILAI AGUNAN</td>
				
				<td align="center">NILAI AGUNAN PELAPOR</td>
				<td align="center">TANGGAL PENILAI AGUNAN</td>
				<td align="center">NILAI AGUNAN INDEPD</td>			
				<td align="center">NAMA PENILAI INDEPD</td>
				<td align="center">TGL PENILAI INDEPD</td>									
				<td align="center">STATUS PARIPASU</td>
				<td align="center">%PARIPASU</td>
				<td align="center">STST KREDIT JOIN</td>
				<td align="center">DIASURANSIKAN</td>
				<td align="center">KETERANGAN</td>								
				
				<td align="center">KODE KANTOR CABANG</td>
				<td align="center">OPERASI DATA</td>			
			  </tr>
		';
		$no=1;
		//A01
		while($lrow=pg_fetch_array($lrs)){		
			$lrow["jenis_id"]='1';
			
			$Ym_tgl_cair=date("Ym",strtotime($lrow["tgl_cair"]));
			
			if($Ym_tgl_cair==$Ym){//cek apakah sama periode report sama dengan tgl input customer 
				$lrow["operasi_data"]='C';
			}else{
				$lrow["operasi_data"]='U';
			}
			$lrow["is_asuransi"]='Y';
			$lrow["jenis_pengikatan"]='03';
			$lrow["jenis_agunan"]='AN020203';			
			
			if($lrow["no_bpkb"] && (strtotime($lrow["tgl_bpkb"]<=strtotime($tgl)))){
				$lrow["kd_jenis_agunan"]='1';//bpkb sudah ada
				$lrow["no_bpkb"]='BPKB NO '.$lrow["no_bpkb"];
			}else{
				$lrow["kd_jenis_agunan"]='2';//belum ada
			}
						
			$lrow["no_agunan"]=$lrow["fk_cabang"].$lrow["fk_sbg"];
			echo '
				<tr>
					<td valign="top">D</td>
					<td valign="top">&nbsp;'.$lrow["no_agunan"].'</td>
					<td valign="top">&nbsp;'.$lrow["fk_sbg"].'</td>
					<td valign="top">'.$lrow["no_cif_replace"].'</td>				
					<td valign="top">F01</td>
					<td valign="top">'.$lrow["kd_jenis_agunan"].'</td>
					<td valign="top">'.$lrow["jenis_agunan"].'</td>
					<td valign="top"></td>
					<td valign="top"></td>
					<td valign="top">'.$lrow["jenis_pengikatan"].'</td>
					<td valign="top">'.($lrow["tgl_cair"]==""?"":date("Ymd",strtotime($lrow["tgl_cair"]))).'</td>
					<td valign="top">'.$lrow["nm_customer"].'</td>
					<td valign="top">'.$lrow["no_bpkb"].'</td>
					
					<td valign="top">'.$lrow["alamat_ktp"].'</td>						
					<td valign="top">'.$lrow["kd_kota_slik"].'</td>
					<td valign="top">'.$lrow["nilai_agunan"].'</td>
					<td valign="top">'.$lrow["nilai_agunan"].'</td>
					<td valign="top">'.($tgl==""?"":date("Ymd",strtotime($tgl))).'</td>
					<td valign="top"></td>
					<td valign="top"></td>
					<td valign="top"></td>
					<td valign="top">T</td>
					<td valign="top"></td>
					<td valign="top">T</td>
					<td valign="top">'.$lrow["is_asuransi"].'</td>
					<td valign="top"></td>
					<td valign="top">000</td>
					<td valign="top">'.$lrow["operasi_data"].'</td>			
	
				</tr>
			';	
			$no++;
		}
	}	
				
				
	if($jenis_report=='F01'){
		echo 	
		'<table border="1">
			 <tr>
				<td align="center">FLAG</td>
				<td align="center">NO REKENING</td>	
				<td align="center">NO CIF</td>									
				<td align="center">KODE SIFAT KREDIT</td>
				<td align="center">KODE JENIS</td>
				<td align="center">KODE AKAD KREDIT</td>
				<td align="center">NOMOR AKAD</td>
				<td align="center">TANGGAL AKAD</td>
				<td align="center">NOMOR AKAD AKHIR</td>
				<td align="center">TANGGAL AKAD AKHIR</td>			
							
				<td align="center">BARU / PERPANJANG</td>
				<td align="center">TANGGAL AWAL KREDIT</td>			
				<td align="center">TANGGAL MULAI</td>			
				<td align="center">TANGGAL JATUH TEMPO</td>
				<td align="center">KODE KATEGORI DEB</td>
				<td align="center">KODE JENIS PENGGUNAAN</td>
				
				<td align="center">KODE ORIENTASI PENGGUNAAN</td>
				<td align="center">KODE SEKTOR EKONOMI</td>
				<td align="center">KODE KOTA/KAB</td>			
				<td align="center">NILAI PROYEK</td>
				<td align="center">KODE VALUTA</td>									
				<td align="center">SUKU BUNGA</td>
				<td align="center">JENIS SUKU</td>
				<td align="center">KREDIT PROGRAM</td>
				<td align="center">ASAL KREDIT</td>
				<td align="center">SUMBER DANA</td>								
				
				<td align="center">PLAFON AWAL</td>
				<td align="center">PLAFON</td>			
				<td align="center">REALISASI BULAN BERJALAN</td>
				<td align="center">DENDA</td>
				<td align="center">BAKI DEBET</td>
				<td align="center">NILAI DALAM MATA UANG ASAL</td>
				<td align="center">KODE KUALITAS KREDIT</td>
				<td align="center">TGL MACET</td>
				<td align="center">KODE SEBAB MACET</td>
				<td align="center">TUNGGAKAN POKOK</td>
				<td align="center">TUNGGAKAN BUNGA</td>
				<td align="center">JLH HARI TUNGGAK</td>
				<td align="center">FREKUENSI TUNGGAKAN</td>
				<td align="center">FREKUENSI RESTRUKTUR</td>
				<td align="center">TANGGAL RESTRUKTURISASI AWAL</td>
				<td align="center">TANGGAL RESTRUKTURISASI AKHIR</td>
				<td align="center">KODE CARA REST</td>
				<td align="center">KODE KONDISI</td>
				<td align="center">TANGGAL KONDISI</td>
				<td align="center">KETERANGAN</td>
				<td align="center">KODE KANTOR CAB</td>
				<td align="center">OPERASI DATA</td>
				
			  </tr>
		';
		$no=1;
		//FO1
		while($lrow=pg_fetch_array($lrs)){		
			$lrow["jenis_id"]='1';
		
			$query_slik="select * from data_gadai.tbltaksir_umum_detail_slik where fk_fatg='".$lrow["no_fatg"]."' ";
			$lrs_slik=pg_query($query_slik);
			while($lrow_slik=pg_fetch_array($lrs_slik)){
				$lrow["baki_debet"]+=$lrow_slik["baki_debet"];
			}
						
			if($lrow["ovd"]<0)$lrow["ovd"]=0;
			$query_cad='select * from tblsetting_cadangan';
			$lrs_cad=pg_query($query_cad);
			while($lrow_cad=pg_fetch_array($lrs_cad)){
				if($lrow["ovd"]>=$lrow_cad["ovd_awal"]&& $lrow["ovd"]<=$lrow_cad["ovd_akhir"]){
					$lrow["kualitas_kredit"]=$lrow_cad['kd_slik'];		
				}			
			}
			
			
			$lrow["akad_kredit"]='070';
			$lrow["jenis_suku_bunga"]='3';
			$lrow["sifat_kredit"]='9';
			if($lrow["fk_kategori_ukb"]=='e3830'){
				$lrow["jenis_kredit"]='P03';
				$juta=1000000;
				if($lrow["penghasilan"]>=0&& $lrow["penghasilan"]<=(300*$juta)){
					$lrow["kategori_debitur"]='UM';
				}elseif($lrow["penghasilan"]>(300*$juta)&& $lrow["penghasilan"]<=(2500*$juta)){
					$lrow["kategori_debitur"]='UK';
				}elseif($lrow["penghasilan"]>(2500*$juta)&& $lrow["penghasilan"]<=(50000*$juta)){
					$lrow["kategori_debitur"]='UT';
				}
				
			}else{
				$lrow["jenis_kredit"]='P04';
				$lrow["kategori_debitur"]='NU';
			}
						
			$lrow["sumber_dana"]='002';
			
			//kondisi ada if
			$lrow["kondisi"]='00';
			
			$Ym_tgl_cair=date("Ym",strtotime($lrow["tgl_cair"]));
			
			if($Ym_tgl_cair==$Ym){//cek apakah sama periode report sama dengan tgl input customer 
				$lrow["operasi_data"]='C';
			}else{
				$lrow["operasi_data"]='U';
			}
			
			$lrow["kd_orientasi"]='10';
			
			if($lrow["baki_debet"]=='0'){
				$lrow["baki_debet"]='';
			}else{
				$lrow["baki_debet"];
			}
			
			if($lrow["kategori"]=='R4'){
				$lrow["bidang_usaha_dibiayai"]='451000';
			}elseif($lrow["kategori"]=='R2'){
				$lrow["bidang_usaha_dibiayai"]='454001';
			}
			
			$lrow["plafon_awal"]=$lrow["total_nilai_pinjaman"]-$lrow["nilai_dp"];			
			$lrow["plafon"]=$lrow["saldo_pinjaman_mtd"];
			$lrow["baki_debet"]=$lrow["saldo_pokok"];
			
			if($lrow["kualitas_kredit"]=='5'){
				$sebab_macet='99';
				$tgl_macet=($lrow["tgl_macet"]==""?"":date("Ymd",strtotime($lrow["tgl_macet"])));
			}else{
				$tgl_macet='';
				$sebab_macet='';
			}
			
			if($lrow["ovd"]>0){
				$lrow["denda"]=round($lrow["rate_denda_ganti_rugi"]*$lrow["angsuran_bulan"]/100)*$lrow["ovd"];
			}else{
				$lrow["denda"]=0;
			}
			
			echo '
				<tr>
					<td valign="top">D</td>
					<td valign="top">&nbsp;'.$lrow["fk_sbg"].'</td>
					<td valign="top">'.$lrow["no_cif_replace"].'</td>
					<td valign="top">'.$lrow["sifat_kredit"].'</td>
					<td valign="top">'.$lrow["jenis_kredit"].'</td>		
					<td valign="top">'.$lrow["akad_kredit"].'</td>	
					<td valign="top">&nbsp;'.$lrow["fk_sbg"].'</td>
					<td valign="top">'.($lrow["tgl_cair"]==""?"":date("Ymd",strtotime($lrow["tgl_cair"]))).'</td>
					<td valign="top">&nbsp;'.$lrow["fk_sbg"].'</td>
					<td valign="top">'.($lrow["tgl_cair"]==""?"":date("Ymd",strtotime($lrow["tgl_cair"]))).'</td>
					<td valign="top">'.$lrow["perpanjangan_ke"].'</td>
					<td valign="top">'.($lrow["tgl_cair"]==""?"":date("Ymd",strtotime($lrow["tgl_cair"]))).'</td>
					<td valign="top">'.($lrow["tgl_cair"]==""?"":date("Ymd",strtotime($lrow["tgl_cair"]))).'</td>
					<td valign="top">'.($lrow["tgl_jatuh_tempo"]==""?"":date("Ymd",strtotime($lrow["tgl_jatuh_tempo"]))).'</td>
					<td valign="top">'.$lrow["kategori_debitur"].'</td>		
					<td valign="top">'.$lrow["kd_tujuan_transaksi_slik"].'</td>		
					<td valign="top">'.$lrow["kd_orientasi"].'</td>	
					<td valign="top">&nbsp;'.$lrow["bidang_usaha_dibiayai"].'</td>		
					<td valign="top">'.$lrow["kd_kota_slik"].'</td>
					<td valign="top"></td>		
					<td valign="top">IDR</td>	
					<td valign="top">'.str_replace(".",",",number_format($lrow['rate_flat'],2)).'</td>	
					<td valign="top">'.$lrow["jenis_suku_bunga"].'</td>	
					<td valign="top">10</td>									
					<td valign="top"></td>	
					<td valign="top">'.$lrow["sumber_dana"].'</td>	
					<td valign="top">'.$lrow["plafon_awal"].'</td>	
					<td valign="top">'.$lrow["plafon"].'</td>	
					<td valign="top">0</td>					
					<td valign="top">'.round($lrow["denda"]).'</td>				
					<td valign="top">'.$lrow["baki_debet"].'</td>
					<td valign="top"></td>
					<td valign="top">'.$lrow["kualitas_kredit"].'</td>
					<td valign="top">'.$tgl_macet.'</td>
					<td valign="top">'.$sebab_macet.'</td>
					<td valign="top">'.round($lrow["tunggakan_pokok"]).'</td>								
					<td valign="top">'.round($lrow["tunggakan_bunga"]).'</td>
					<td valign="top">'.round($lrow["ovd"]<=0?"":$lrow["ovd"]).'</td>
					<td valign="top">'.round($lrow["byk_tunggakan"]).'</td>
					<td valign="top">0</td>					
					<td valign="top"></td>					
					<td valign="top"></td>			
					<td valign="top"></td>			
					<td valign="top">'.$lrow["kondisi"].'</td>
					<td valign="top"></td>			
					<td valign="top"></td>		
					<td valign="top">000</td>
					<td valign="top">'.$lrow["operasi_data"].'</td>		
		
				</tr>
			';	
			$no++;
		}
	}
	
	if($jenis_report=='P01'){
		echo 	
		'<table border="1">
			 <tr>
				<td align="center">FLAG</td>
				<td align="center">NOMOR IDENTITAS PENJAMIN</td>	
				<td align="center">NOREK FASILITAS</td>									
				<td align="center">CIF</td>
				<td align="center">KODE JENIS SEGMEN</td>
				<td align="center">KODE JENIS IDENTITAS PENJAMIN</td>
				<td align="center">NAMA PENJAMIN</td>
				<td align="center">NAMA LENGKAP</td>
				<td align="center">KODE GOL PENJAMIN</td>			
				<td align="center">ALAMAT PENJAMIN</td>
				<td align="center">PERSENTASE DIJAMIN</td>			
				<td align="center">KETERANGAN</td>			
				
				<td align="center">KODE KANTOR CABANG</td>
				<td align="center">OPERASI DATA</td>			
			  </tr>
		';
		$no=1;
		//P01
		while($lrow=pg_fetch_array($lrs)){	
			if($lrow["status_pernikahan"]=='2'){
				$Ym_tgl_input_customer=date("Ym",strtotime($lrow["tgl_input_customer"]));
				
				$Ym_log_action_date=get_rec("tblcustomer_log","log_action_date","no_cif='".$lrow["fk_cif"]."' and log_action_mode='UB' and log_action_date like '%".$Ym."%'","pk_id_log desc");
				
				if($Ym_tgl_input_customer==$Ym){//cek apakah sama periode report sama dengan tgl input customer 
					$lrow["operasi_data"]='C';
				}elseif($Ym_log_action_date){
					$lrow["operasi_data"]='U';
				}else{
					$lrow["operasi_data"]='N';
				}
				
				echo '
					<tr>
						<td valign="top">D</td>
						<td valign="top">&nbsp;'.$lrow["no_id_penjamin"].'</td>
						<td valign="top">&nbsp;'.$lrow["fk_sbg"].'</td>
						<td valign="top">'.$lrow["no_cif_replace"].'</td>				
						<td valign="top">F01</td>
						<td valign="top">1</td>				
						<td valign="top">'.$lrow["nm_penjamin"].'</td>
						<td valign="top">'.$lrow["nm_penjamin"].'</td>					
						<td valign="top">S14</td>
						<td valign="top">'.$lrow["alamat_penjamin"].'</td>
						<td valign="top"></td>
						<td valign="top"></td>					
						<td valign="top">000</td>
						<td valign="top">'.$lrow["operasi_data"].'</td>			
		
					</tr>
				';	
				$no++;
			}
		}
	}		
	echo '</table>';
	
	
}

function get_user($username){
	$nm_user=get_rec("tbluser left join tblkaryawan on fk_karyawan=npk","nm_depan","username='".$username."'");
	
	return $nm_user;
}


