<?php

include_once("report.php");

function filter_request(){
	global $jenis_produk,$showCab,$showTgl,$no_cif,$nm_customer,$no_id,$is_tgl_jatuh_tempo;
	$jenis_produk=($_REQUEST["jenis_produk"]);
	$no_cif=($_REQUEST["no_cif"]);
	$nm_customer=($_REQUEST["nm_customer"]);
	$no_id=($_REQUEST["no_id"]);

	
	$showCab='t';
	$showTgl='t';
	

}



function create_filter(){
	global $jenis_produk,$no_cif,$no_id,$nm_customer,$is_tgl_jatuh_tempo;
?>
       
<!--   <tr bgcolor="efefef">
        <td style="padding:0 5 0 5" width="20%">Jenis Produk</td>
        <td style="padding:0 5 0 5" width="30%">
        <select id='jenis_produk' name="jenis_produk">
                <option value=""   <?=(($jenis_produk == '')?'selected':'') ?>>--Pilih--</option>
                <option value="0"<?= (($jenis_produk=='0')?"selected":"") ?>>Gadai</option>
                <option value="1"<?=(($jenis_produk=='1')?"selected":"") ?>>Cicilan</option>
             </select>
        </td>
        <td style="padding:0 5 0 5" width="20%">Jatuh Tempo</td>
        <td style="padding:0 5 0 5" width="30%">
        <input type="checkbox" name="is_tgl_jatuh_tempo" value="t" <?=(($is_tgl_jatuh_tempo=="t")?"checked":"")?> >
        </td>
     </tr>
    <tr bgcolor="efefef">
        <td style="padding:0 5 0 5" width="20%">No ID</td>
        <td style="padding:0 5 0 5" width="30%"><input name="no_id" type="text" class='groove_text ' size="20" id="no_id" value="<?=$no_id?>" onChange="fGetKTPData()"></td>
        
        <td style="padding:0 5 0 5" width="20%">No CIF</td>
        <td style="padding:0 5 0 5" width="30%"><input name="no_cif" type="text" class='groove_text ' size="20" id="no_cif" value="<?=$no_cif?>" onChange="fGetCIFData()"></td>
    </tr>
    
    <tr bgcolor="efefef">
        <td style="padding:0 5 0 5" width="20%">Nama Customer</td>
        <td style="padding:0 5 0 5" width="30%"><input name="nm_customer" type="hidden" class='groove_text ' size="20" id="nm_customer" value="<?=$nm_customer?>"><span id="divnm_customer"><?=$nm_customer?></span></td>
        <td style="padding:0 5 0 5" width="20%"></td>
        <td style="padding:0 5 0 5" width="30%"></td>
    </tr>-->

	<!--<tr bgcolor="efefef">
        <td style="padding:0 5 0 5" width="20%">Jatuh Tempo</td>
        <td style="padding:0 5 0 5" width="30%">
        <input type="checkbox" name="is_tgl_jatuh_tempo" value="t" <?=(($is_tgl_jatuh_tempo=="t")?"checked":"")?> >
        </td>
        <td style="padding:0 5 0 5" width="20%"></td>
        <td style="padding:0 5 0 5" width="30%"></td>
     </tr>-->


<?	
}




function excel_content(){
	global $tgl,$fk_cabang,$nm_cabang,$jenis_produk,$no_cif,$is_tgl_jatuh_tempo;
	

	if($tgl != '' ){
		$lwhere.=" (tgl_lunas > '".$tgl."' or tgl_lunas is null) and tgl_cair <='".$tgl."'";
		$tgl_data=$tgl;
	}
		
	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cabang = '".$fk_cabang."' ";
	}
	
	if($jenis_produk != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" jenis_produk = '".$jenis_produk."' ";
	}
	
	if ($lwhere!="") $lwhere=" where ".$lwhere;

	$query = "
		select * from( 
			select * from (
				select tgl_lunas,status_sbg, tgl_cair,tgl_cair as tgl_booking, status_sbg, fk_sbg as no_sbg1,fk_cabang,fk_cif,kd_produk,fk_produk,jumlah_hari,nm_produk,tgl_jt,jenis_produk from tblinventory
				left join (
					select kd_produk,nm_produk,jenis_produk,jumlah_hari from tblproduk
					left join tblrate on fk_rate=kd_rate
				) as tblproduk on kd_produk=fk_produk
				".$lwhere." -- and fk_sbg in('20101230300136','-')
			) as tblinventory
			left join (select kd_cabang, nm_cabang,fk_wilayah, fk_area from tblcabang) as tblcab on kd_cabang=fk_cabang
			where tgl_cair is not null 
		) as tblsbg		
		left join tblcustomer on no_cif=fk_cif 
		left join tblwilayah on kd_wilayah=fk_wilayah
		left join tblarea on kd_area=fk_area		
		left join viewkontrak on no_sbg=no_sbg1
		left join (
			select kd_kelurahan as kd_kelurahan_tinggal,nm_kelurahan as nm_kelurahan_tinggal,nm_kecamatan as nm_kecamatan_tinggal,nm_kota as nm_kota_tinggal from tblkelurahan 
			left join tblkecamatan on kd_kecamatan=fk_kecamatan
			left join tblkota on kd_kota=fk_kota
		) as tblwilayah_tinggal on kd_kelurahan_tinggal=fk_kelurahan_tinggal
		left join (
			select kd_kelurahan as kd_kelurahan_ktp,nm_kelurahan as nm_kelurahan_ktp,nm_kecamatan as nm_kecamatan_ktp,nm_kota as nm_kota_ktp from tblkelurahan 
			left join tblkecamatan on kd_kecamatan=fk_kecamatan
			left join tblkota on kd_kota=fk_kota
		) as tblwilayah_ktp on kd_kelurahan_ktp=fk_kelurahan_ktp	
		left join(
			select no_sbg as no_sbg_appr,addm_addb,tgl_jatuh_tempo as tgl_jt_akhir,total_hutang as total_ar,tgl_wo from data_gadai.tblproduk_cicilan			
		)as tblappr on no_sbg1 = no_sbg_appr
		left join(
			select * from viewkendaraan
			left join tblpartner on fk_partner_dealer=kd_partner
		)as tbltaksir on no_fatg=fk_fatg
		left join data_gadai.tbltaksir_umum on tbltaksir_umum.no_fatg=fk_fatg
		left join (
			select npk as npk_cmo,case when nm_belakang is null then nm_depan else nm_depan||' '||nm_belakang end as nm_karyawan_cmo from tblkaryawan
		) as tblkaryawan_cmo on fk_karyawan_cmo=npk_cmo
		left join (
			select nik as nik_dealer,nm_karyawan as nm_karyawan_dealer from tblkaryawan_dealer
		) as tblkaryawan_dealer on tbltaksir_umum.fk_karyawan_sales=nik_dealer
		--left join viewang_ke on viewang_ke.fk_sbg=no_sbg
		left join(
			select distinct on(fk_sbg)fk_sbg as fk_sbg_bayar, tgl_bayar as tgl_bayar_terakhir,nilai_angsuran as nominal_bayar_terakhir from data_fa.tblangsuran 
			where (tgl_bayar is not null and tgl_bayar<='".$tgl."')
			and angsuran_ke>0 order by fk_sbg,angsuran_ke desc 
		)as tblbayar on no_sbg=fk_sbg_bayar		
		left join(
			select fk_sbg as fk_sbg1, tgl_jatuh_tempo as tgl_jt1 from data_fa.tblangsuran 		
			where angsuran_ke=1
		)as tblbayar1 on no_sbg=fk_sbg1			
		inner join(
			select distinct on(fk_sbg)fk_sbg as fk_sbg_od from data_fa.tblangsuran 
			where (tgl_bayar is null or(tgl_bayar is not null and tgl_bayar>'".$tgl."'))
			and '".$tgl."' >tgl_jatuh_tempo
			and angsuran_ke>0 order by fk_sbg,angsuran_ke desc 
		)as tblod on no_sbg=fk_sbg_od			
		left join(
			select distinct on (fk_sbg)fk_sbg as fk_sbg_tarik,tgl_data as tgl_tarik from data_gadai.tblhistory_sbg
			where transaksi='Tarik'	and tgl_data<='".$tgl." 23:59:59' and tgl_batal is null
		)as tarik on no_sbg=fk_sbg_tarik
		left join(
			select distinct on (fk_sbg)fk_sbg as fk_sbg_tebus,tgl_data as tgl_tebus from data_gadai.tblhistory_sbg
			where transaksi='Tebus'	and tgl_data<='".$tgl." 23:59:59' and tgl_batal is null
		)as tebus on no_sbg=fk_sbg_tebus							
		".$lwhere1."
		order by tgl_booking asc --limit 1
	";
	
	//showquery($query);
	echo 	
	'<table border="1">
	     <tr>
		 	<td align="center" rowspan="2">No</td>
		 	<td align="center" rowspan="2">Nama Debitur</td>
			<td align="center" rowspan="2">No Akad</td>			
			<td align="center" rowspan="2">NO HP</td>
			<td align="center" rowspan="2">TGL JTO Pertama</td>
			<td align="center" rowspan="2">TGL JTO</td>
			<td align="center" rowspan="2">TGL Terakhir Bayar</td>
			<td align="center" rowspan="2">Nominal Pembayaran Terakhir</td>
			<td align="center" rowspan="2">NO POLISI</td>						
			<td align="center" rowspan="2">NO RANGKA</td>
			<td align="center" rowspan="2">NO MESIN</td>				
			<td align="center" rowspan="2">Katagori</td>
			<td align="center" rowspan="2">TYPE</td>
			<td align="center" rowspan="2">DEALER</td>
			<td align="center" rowspan="2">Sales Dealer</td>
			<td align="center" rowspan="2">CMO/Surveyor</td>
			<td align="center" rowspan="2">Angsuran Ke</td>
			<td align="center" rowspan="2">Tenor</td>			
			<td align="center" rowspan="2">Nominal Angsuran</td>
			
			<td align="center" colspan="6">AR Ovedue</td>
			<td align="center" rowspan="2">OverDue</td>
			<td align="center" rowspan="2">Total Tunggakan</td>
			<td align="center" colspan="2">Debet Pokok dan Bunga (01-30)</td>
			<td align="center" colspan="2">Debet Pokok dan Bunga (31-60)</td>
			<td align="center" colspan="2">Debet Pokok dan Bunga > 60</td>
			<td align="center" rowspan="2">Receivables</td>
			<td align="center" rowspan="2">Not Yet Due</td>
			<td align="center" rowspan="2">Sisa Pokok</td>
			<td align="center" colspan="3">Status Piutang</td>
			<td align="center" colspan="4">Alamat KTP</td>
			<td align="center" colspan="4">Alamat Domisili</td>
		  </tr>
		  <tr>
			<td align="center">OD (01-30)</td>					
			<td align="center">Angsuran (01-30)</td>
			<td align="center">OD (31-60)</td>
			<td align="center">Angsuran (31-60)</td> 
			<td align="center">OD > 60</td>
			<td align="center">Angsuran > 60</td>  
			
			<td align="center">Pokok</td>
			<td align="center">Bunga</td>
			<td align="center">Pokok</td>
			<td align="center">Bunga</td>
            <td align="center">Pokok</td>
			<td align="center">Bunga</td> 
			<td align="center">Status</td>
            <td align="center">Bulan WO</td>
			<td align="center">Tahun WO</td>
			
			<td align="center">ALAMAT KTP</td>
			<td align="center">KELURAHAN</td>
			<td align="center">KECAMATAN</td>
			<td align="center">KABUPATEN</td>
			
			<td align="center">ALAMAT DOMISILI</td>
			<td align="center">KELURAHAN</td>
			<td align="center">KECAMATAN</td>
			<td align="center">KABUPATEN</td>   	
		  </tr>
	';
	$lrs = pg_query($query);
	$no=1;
	
	
	while($lrow=pg_fetch_array($lrs)){		

		$no_sbg=$lrow["no_sbg"];
		$ovd=array();
		
		$query_ovd="
		select * from(
			select date_part('days','".$tgl."'-tgl_jatuh_tempo)as ovd,* from data_fa.tblangsuran 
			where (tgl_bayar is null or (tgl_bayar is not null and tgl_bayar>'".$tgl."'))
			and angsuran_ke>0
			and fk_sbg='".$no_sbg."' order by angsuran_ke desc 
		)as tblmain		
		";
		//showquery($query_ovd);
		$lrs_ovd=pg_query($query_ovd);
		$i=1;
		while($lrow_ovd=pg_fetch_array($lrs_ovd)){
			$lrow["nilai_angsuran"]=$lrow_ovd["nilai_angsuran"];
			$lrow["tgl_jt2"]=$lrow_ovd["tgl_jatuh_tempo"];
			$lrow["ang_ke"]=$lrow_ovd["angsuran_ke"];
			
			if($lrow_ovd[ovd]>0 && $lrow_ovd[ovd]<=30){
				$ovd['1']['ar']+=$lrow_ovd["nilai_angsuran"];
				$ovd['1']['od']=$lrow_ovd["ovd"];
				$ovd['1']['pokok']+=$lrow_ovd["pokok_jt"];
				$ovd['1']['bunga']+=$lrow_ovd["bunga_jt"];
			}
			if($lrow_ovd[ovd]>30 && $lrow_ovd[ovd]<=60){
				$ovd['2']['ar']+=$lrow_ovd["nilai_angsuran"];
				$ovd['2']['od']=$lrow_ovd["ovd"];	
				$ovd['2']['pokok']+=$lrow_ovd["pokok_jt"];
				$ovd['2']['bunga']+=$lrow_ovd["bunga_jt"];							
			}
			if($lrow_ovd[ovd]>60){
				$ovd['3']['ar']+=$lrow_ovd["nilai_angsuran"];
				$ovd['3']['od']=$lrow_ovd["ovd"];		
				$ovd['3']['pokok']+=$lrow_ovd["pokok_jt"];
				$ovd['3']['bunga']+=$lrow_ovd["bunga_jt"];
						
			}
			
			if($lrow_ovd[ovd]>0){
				$lrow["ovd_akhir"]=$lrow_ovd["ovd"];	
				$lrow["total_tunggakan"]+=$lrow_ovd["nilai_angsuran"];
				
			}
			$lrow["sisa_pokok"]+=$lrow_ovd["pokok_jt"];
			$i++;
		}
		//print_r($ovd);
		
		if($lrow["tgl_wo"]){
			$lrow["status_piutang"]='WO';
			$lrow["bulan_wo"]=date("M",strtotime($lrow["tgl_wo"]));
			$lrow["tahun_wo"]=date("Y",strtotime($lrow["tgl_wo"]));
		}else{
			$lrow["status_piutang"]='Piutang Aktif';
		}
		
		if($lrow["fk_sbg_tarik"]){
			if(!$lrow["fk_sbg_tebus"] || $lrow["tgl_tarik"]>$lrow["tgl_tebus"]){
				$lrow["status"]='TARIK';
			}elseif($lrow["fk_sbg_tebus"]){
				$lrow["status"]='TEBUS';	
			}
		}else{
			$lrow["status"]='NORMAL';
		}
		
		if($lrow["status"]=='TEBUS' || $lrow["status"]=='NORMAL'){	
		echo '
			<tr>
				<td valign="top">'.$no.'</td>
				<td valign="top">'.$lrow["nm_customer"].'</td>
				<td valign="top">&nbsp;'.$lrow["no_sbg"].'</td>				
				<td valign="top">&nbsp;'.$lrow["no_hp"].'</td>
				<td valign="top">'.($lrow["tgl_jt1"]==""?"":date("d/m/Y",strtotime($lrow["tgl_jt1"]))).'</td>				
				<td valign="top">'.($lrow["tgl_jt2"]==""?"":date("d/m/Y",strtotime($lrow["tgl_jt2"]))).'</td>
				<td valign="top">'.($lrow["tgl_bayar_terakhir"]==""?"":date("d/m/Y",strtotime($lrow["tgl_bayar_terakhir"]))).'</td>
						
				<td valign="top">'.number_format($lrow["nominal_bayar_terakhir"]).'</td>
				<td valign="top">'.$lrow["no_polisi"].'</td>
				<td valign="top">'.$lrow["no_rangka"].'</td>
				<td valign="top">'.$lrow["no_mesin"].'</td>
				<td valign="top">'.$lrow["nm_jenis_barang"].'</td>
				<td valign="top">'.$lrow["nm_tipe"].'</td>
				<td valign="top">'.$lrow["nm_partner"].'</td>	
				<td valign="top">'.$lrow["nm_karyawan_dealer"].'</td>
				<td valign="top">'.$lrow["nm_karyawan_cmo"].'</td>
				<td valign="top">'.$lrow["ang_ke"].'</td>
				<td valign="top">'.$lrow["lama_pinjaman"].'</td>				
				<td valign="top">'.number_format($lrow["nilai_angsuran"]).'</td>


				<td valign="top">'.$ovd['1']['od'].'</td>
				<td valign="top">'.number_format($ovd['1']['ar']).'</td>
				<td valign="top">'.$ovd['2']['od'].'</td>
				<td valign="top">'.number_format($ovd['2']['ar']).'</td>
				<td valign="top">'.$ovd['3']['od'].'</td>
				<td valign="top">'.number_format($ovd['3']['ar']).'</td>
				
				<td valign="top">'.$lrow["ovd_akhir"].'</td>
				<td valign="top">'.number_format($lrow["total_tunggakan"]).'</td>
				
				<td valign="top">'.number_format($ovd['1']['pokok']).'</td>
				<td valign="top">'.number_format($ovd['1']['bunga']).'</td>
				<td valign="top">'.number_format($ovd['2']['pokok']).'</td>
				<td valign="top">'.number_format($ovd['2']['bunga']).'</td>
				<td valign="top">'.number_format($ovd['3']['pokok']).'</td>
				<td valign="top">'.number_format($ovd['3']['bunga']).'</td>
				<td valign="top">'.number_format($lrow["total_ar"]).'</td>
				<td valign="top">'.number_format(($lrow["total_ar"]-$lrow["total_tunggakan"])).'</td>
				<td valign="top">'.number_format($lrow["sisa_pokok"]).'</td>
				<td valign="top">'.$lrow["status_piutang"].'</td>
				<td valign="top">'.$lrow["bulan_wo"].'</td>
				<td valign="top">'.$lrow["tahun_wo"].'</td>
				<td valign="top">'.$lrow["alamat_ktp"].'</td>
				<td valign="top">'.$lrow["nm_kelurahan_ktp"].'</td>
				<td valign="top">'.$lrow["nm_kecamatan_ktp"].'</td>
				<td valign="top">'.$lrow["nm_kota_ktp"].'</td>
				<td valign="top">'.$lrow["alamat_tinggal"].'</td>
				<td valign="top">'.$lrow["nm_kelurahan_tinggal"].'</td>
				<td valign="top">'.$lrow["nm_kecamatan_tinggal"].'</td>
				<td valign="top">'.$lrow["nm_kota_tinggal"].'</td>
				
			</tr>
		';	
		$no++;
		$total_saldo+=$lrow["saldo_os"];
		$total['1']['ar']+=$ovd['1']['ar'];
		$total['2']['ar']+=$ovd['2']['ar'];
		$total['3']['ar']+=$ovd['3']['ar'];
		$total['total_tunggakan']+=$lrow['total_tunggakan'];
		$total['1']['pokok']+=$ovd['1']['pokok'];
		$total['2']['pokok']+=$ovd['2']['pokok'];
		$total['3']['pokok']+=$ovd['3']['pokok'];
		$total['1']['bunga']+=$ovd['1']['bunga'];
		$total['2']['bunga']+=$ovd['2']['bunga'];
		$total['3']['bunga']+=$ovd['3']['bunga'];
		$total['total_ar']+=$lrow['total_ar'];
		$total['sisa_pokok']+=$lrow['sisa_pokok'];
		}
	}
	echo '
		<tr>
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
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td valign="top">'.number_format($total['1']['ar']).'</td>
			<td></td>
			<td valign="top">'.number_format($total['2']['ar']).'</td>
			<<td></td>
			<td valign="top">'.number_format($total['3']['ar']).'</td>
			
			<td></td>
			<td valign="top">'.number_format($total["total_tunggakan"]).'</td>
			
			<td valign="top">'.number_format($total['1']['pokok']).'</td>
			<td valign="top">'.number_format($total['1']['bunga']).'</td>
			<td valign="top">'.number_format($total['2']['pokok']).'</td>
			<td valign="top">'.number_format($total['2']['bunga']).'</td>
			<td valign="top">'.number_format($total['3']['pokok']).'</td>
			<td valign="top">'.number_format($total['3']['bunga']).'</td>
			<td valign="top">'.number_format($total["total_ar"]).'</td>
			<td valign="top">'.number_format(($total["total_ar"]-$total["total_tunggakan"])).'</td>
			<td valign="top">'.number_format($total["sisa_pokok"]).'</td>
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
		</tr>
	';					
	echo '</table>';
	
	
}

function get_user($username){
	$nm_user=get_rec("tbluser left join tblkaryawan on fk_karyawan=npk","nm_depan","username='".$username."'");
	
	return $nm_user;
}


