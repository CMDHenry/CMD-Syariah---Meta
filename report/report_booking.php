<?php

include_once("report.php");


function filter_request(){
	global $no_batch,$showCab,$showPeriode,$showWil,$showDlr,$periode_awal1,$periode_akhir1,$periode_awal2,$periode_akhir2,$fk_karyawan,$periode_awal3,$periode_akhir3,$kd_produk,$is_ap_dealer;
	
	$showCab='t';
	//$showPeriode='t';
	$showWil='t';
	$showDlr='t';
	$periode_awal1 = convert_date_english($_REQUEST["periode_awal1"]);
	$periode_akhir1 = convert_date_english($_REQUEST["periode_akhir1"]);
	$periode_awal2 = convert_date_english($_REQUEST["periode_awal2"]);
	$periode_akhir2 = convert_date_english($_REQUEST["periode_akhir2"]);
	$periode_awal3 = convert_date_english($_REQUEST["periode_awal3"]);
	$periode_akhir3 = convert_date_english($_REQUEST["periode_akhir3"]);
	$no_batch=($_REQUEST["no_batch"]);
	$fk_karyawan=($_REQUEST["fk_karyawan"]);
	$kd_produk=($_REQUEST["kd_produk"]);
	
	$showCMO='t';
	
	$is_ap_dealer=trim($_REQUEST["is_ap_dealer"]);
	if($is_ap_dealer==""){
		$is_ap_dealer="f";
	}
	
}

function fGet(){
?>

	function fGetBatch(){
		fGetCustomNC(false,"batch","no_batch","Ganti CABANG",document.form1.no_batch,document.form1.no_batch)
	}
	

<?	
	
}


function create_filter(){
	global $no_batch,$periode_awal1,$periode_akhir1,$periode_awal2,$periode_akhir2,$fk_karyawan,$periode_awal3,$periode_akhir3,$cek,$kd_produk,$is_ap_dealer;
?>

       
    <tr bgcolor="efefef">
        <td width="20%" style="padding:0 5 0 5" class="">Periode Batch Kontrak</td>
        <td width="30%" style="padding:0 5 0 5">
            <input type="text" name="periode_awal1" value="<?=convert_date_indonesia($periode_awal1)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_awal1.click();" size="10" >&nbsp;<img src="../images/btn_extend.gif" name="img_periode_awal1" onClick="fPopCalendar(document.form1.periode_awal1,document.form1.periode_awal1)"> -                               
            <input type="text" name="periode_akhir1" value="<?=convert_date_indonesia($periode_akhir1)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_akhir1.click();" size="10">&nbsp;<img src="../images/btn_extend.gif" name="img_periode_akhir1" onClick="fPopCalendar(document.form1.periode_akhir1,document.form1.periode_akhir1)">                                
        </td>
        <td style="padding:0 5 0 5" width="20%">No Batch</td>
        <td style="padding:0 5 0 5" width="30%">                    
        <input name="no_batch" type="text" onKeyPress="if(event.keyCode==4) img_no_batch.click();" onKeyUp="fNextFocus(event,document.form1.periode_awal)"  value="<?=$no_batch?>" >&nbsp;<img src="../images/search.gif" id="img_no_batch" onClick="fGetBatch()" style="border:0px" align="absmiddle">
</td>
        
    </tr>               

    
    <tr bgcolor="efefef">
        <td width="20%" style="padding:0 5 0 5" class="">Periode Pengajuan </td>
        <td width="30%" style="padding:0 5 0 5" >
            <input type="text" name="periode_awal2" value="<?=convert_date_indonesia($periode_awal2)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_awal2.click();" size="10" >&nbsp;<img src="../images/btn_extend.gif" name="img_periode_awal2" onClick="fPopCalendar(document.form1.periode_awal2,document.form1.periode_awal2)"> -                               
            <input type="text" name="periode_akhir2" value="<?=convert_date_indonesia($periode_akhir2)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_akhir2.click();" size="10">&nbsp;<img src="../images/btn_extend.gif" name="img_periode_akhir2" onClick="fPopCalendar(document.form1.periode_akhir2,document.form1.periode_akhir2)">                                
        </td>
   		<td style="padding:0 5 0 5" width="20%">Kode Produk</td>
        <td style="padding:0 5 0 5" width="30%">
         <? create_list_produk();?>
        </td>
</td>
        
    </tr>  

    <tr bgcolor="efefef">
          <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef">Kode CMO</td>
          <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                <input name="fk_karyawan" type="text" onKeyPress="if(event.keyCode==4) img_fk_karyawan.click();" onKeyUp="fNextFocus(event,document.form1.periode_awal)"  value="<?=$fk_karyawan?>" onChange="fGetKaryawanData()">&nbsp;<img src="../images/search.gif" id="img_fk_karyawan" onClick="fGetKaryawan()" style="border:0px" align="absmiddle">
          </td>
          <td width="20%" style="padding:0 5 0 5">Nama</td>
          <td width="30%" style="padding:0 5 0 5">
                <input type="hidden" name="nm_karyawan" value="<?=convert_html($nm_karyawan)?>" class="groove_text" style="width:90%" > <span id="divnm_karyawan"><?=convert_html($nm_karyawan)?></span>
           </td>
    </tr>
    <tr bgcolor="efefef">
        <td width="20%" style="padding:0 5 0 5" class="">Periode Pelunasan ke Dealer </td>
        <td width="30%" style="padding:0 5 0 5">
            <input type="text" name="periode_awal3" value="<?=convert_date_indonesia($periode_awal3)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_awal3.click();" size="10" >&nbsp;<img src="../images/btn_extend.gif" name="img_periode_awal3" onClick="fPopCalendar(document.form1.periode_awal3,document.form1.periode_awal3)"> -                               
            <input type="text" name="periode_akhir3" value="<?=convert_date_indonesia($periode_akhir3)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_akhir3.click();" size="10">&nbsp;<img src="../images/btn_extend.gif" name="img_periode_akhir3" onClick="fPopCalendar(document.form1.periode_akhir3,document.form1.periode_akhir3)">                                
        </td>
        <td style="padding:0 5 0 5" width="20%">AP Dealer</td>
        <td style="padding:0 5 0 5" width="30%">       
        <input type="checkbox" name="is_ap_dealer" value="t" <?=(($is_ap_dealer=="t")?"checked":"")?> >                    
		</td>
    </tr>
    
    
<?	
}

function create_list_produk(){
	//showquery("select * from tblcabang_detail_bank left join tblbank on fk_bank=kd_bank left join (select description, coa from tbltemplate_coa)as tblcoa on fk_coa=coa where fk_cabang='".$fk_cabang_bank."'  and fk_bank<=90");
    $l_list_obj = new select("select kd_produk,nm_produk,jenis_produk from tblproduk","nm_produk","kd_produk","kd_produk");
    $l_list_obj->add_item("-- Pilih ---",'',0);
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:12px;' onKeyUp='fNextFocus(event,document.getElementById(\"login\"))'");
}


function excel_content(){
	global $periode_awal1,$periode_akhir1,$fk_cabang,$nm_cabang,$no_batch,$fk_wilayah,$fk_partner_dealer,$periode_awal2,$periode_akhir2,$cek,$fk_karyawan,$periode_awal3,$periode_akhir3,$kd_produk,$is_ap_dealer;
	if($periode_awal1 != '' && $periode_akhir1 != ''){
		$lwhere.=" tgl_cair between '".$periode_awal1." 00:00:00' and '".$periode_akhir1." 23:59:59'";
	}
	
	if($periode_awal2 != '' && $periode_akhir2 != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" tgl_pengajuan between '".$periode_awal2." 00:00:00' and '".$periode_akhir2." 23:59:59'";
	}
	
	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cabang = '".$fk_cabang."' ";
	}
	if($kd_produk != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_produk = '".$kd_produk."' ";
	}
	
	if($no_batch != ''){
		if ($lwhere1!="") $lwhere1.=" and ";
		$lwhere1.=" no_batch = '".$no_batch."' ";
	}
	
	if($fk_wilayah != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_wilayah = '".$fk_wilayah."' ";
	}
	
	if($fk_partner_dealer != ''){
		if ($lwhere1!="") $lwhere1.=" and ";
		$lwhere1.=" fk_partner_dealer = '".$fk_partner_dealer."' ";
	}
	
	if($fk_karyawan != ''){
		if ($lwhere1!="") $lwhere1.=" and ";
		$lwhere1.=" fk_karyawan_cmo = '".$fk_karyawan."' ";
	}
	
	if($periode_awal3 != '' && $periode_akhir3 != ''){
		if ($lwhere1!="") $lwhere1.=" and ";
		
		if($is_ap_dealer=='t'){
			$lwhere1.=" tgl_pelunasan_dealer is null or tgl_pelunasan_dealer > '".$periode_akhir3." 23:59:59'";
			if ($lwhere!="") $lwhere.=" and ";
			$lwhere.=" tgl_pengajuan <='".$periode_akhir3." 23:59:59'";
			
		}else{
			$lwhere1.=" tgl_pelunasan_dealer between '".$periode_awal3." 00:00:00' and '".$periode_akhir3." 23:59:59'";
		}
	}
	
	
	if ($lwhere!="") $lwhere=" where ".$lwhere;
	if ($lwhere1!="") $lwhere1=" where ".$lwhere1;
	
	
	$query = "
		select * from( 
			select * from(
				select * from data_gadai.tblproduk_cicilan
				where status_approval!='Batal' --and no_sbg in('20102230700793','20102230700775','20102230700774')
				--limit 1
			)as tblcicila
			left join(
				select fk_cabang,no_fatg as fk_fatg1, fk_cif,no_bpkb,bpkb_diserahkan,tgl_bpkb,tgl_terima_bpkb,tgl_cabut_limpah,no_limpah_ke_bank,tgl_limpah_ke_bank ,tgl_serah_terima_bpkb,posisi_bpkb,no_faktur,tgl_faktur,nm_bpkb,status_barang,no_tt_bpkb,no_polisi, fk_karyawan_cmo ,kondisi_unit from data_gadai.tbltaksir_umum				
			) as tblinventory on fk_fatg1=fk_fatg
			left join (
				select kd_produk,nm_produk,jenis_produk from tblproduk
				left join tblrate on fk_rate=kd_rate
			) as tblproduk on kd_produk=fk_produk
			left join tblcabang on fk_cabang=kd_cabang
			".$lwhere."
			--where tgl_cair is not null 
		) as tblsbg
		left join tblcustomer on no_cif=fk_cif 
		left join tblkelurahan on kd_kelurahan=fk_kelurahan_tinggal
		left join tblkecamatan on kd_kecamatan=fk_kecamatan
		left join tblkota on kd_kota=fk_kota	
		left join tblpekerjaan on kd_pekerjaan=fk_pekerjaan
		left join(
			select * from viewkendaraan
			left join tblpartner on fk_partner_dealer=kd_partner
		)as tbltaksir on no_fatg=fk_fatg
		left join (
			select referensi as no_batch,fk_sbg as fk_sbg1,tgl_bayar as tgl_batch from data_gadai.tblhistory_sbg
			where transaksi='AR' and tgl_batal is null
		)as tbl on no_sbg=fk_sbg1
		left join(
			select distinct on (fk_sbg)fk_sbg as fk_sbg_tarik,tgl_data as tgl_tarik,fk_user as fk_user_tarik from data_gadai.tblhistory_sbg
			where transaksi='Tarik'	and tgl_batal is null
			order by fk_sbg,tgl_data desc
		)as tarik on no_sbg=fk_sbg_tarik	
		left join(
			select distinct on (fk_sbg)fk_sbg as fk_sbg_tebus,tgl_data as tgl_tebus from data_gadai.tblhistory_sbg
			where transaksi='Tebus' and tgl_batal is null
			order by fk_sbg,tgl_data
		)as tebus on no_sbg=fk_sbg_tebus
		left join(
			select distinct on (no_sbg_ar)no_sbg_ar as no_sbg_log,log_action_date as tgl_edit,log_action_username as user_edit from data_gadai.tbltaksir_umum_log
			where log_action_mode='UA'
			order by no_sbg_ar,log_action_date desc
		)as tltarik on no_sbg=no_sbg_log
		left join(
			select distinct on(fk_sbg)nm_partner as nm_funding,no_funding,fk_sbg as fk_sbg_funding from data_fa.tblfunding
			left join data_fa.tblfunding_detail on no_funding=fk_funding
			left join tblpartner on fk_partner =kd_partner	
		)as tblfunding on no_sbg = fk_sbg_funding
		left join (
			select referensi as no_kwitansi_pelunasan,fk_sbg as fk_sbg2,tgl_data as tgl_pelunasan_dealer from data_gadai.tblhistory_sbg
			where transaksi in('Pembayaran Unit','Pencairan Datun') and tgl_batal is null
		)as tbl2 on no_sbg=fk_sbg2
		left join(
			SELECT npk, nm_depan as nm_cmo FROM tblkaryawan
		)AS tblkaryawan ON npk=fk_karyawan_cmo
		left join(
			select distinct on (no_transaksi)no_transaksi,log_action_date ,log_action_username
			from tblprint_log
			where jenis_transaksi='KONTRAK'
			order by no_transaksi,log_action_date desc
		)as tblprint on no_transaksi=no_sbg
		".$lwhere1."
		order by tgl_pengajuan asc
	";
	//showquery($query);
	
	$arr_tac=array('Dealer','Kacab','SPV','Sales');
	
	
	echo 	
	'<table border="1">
	     <tr>
		 	<td align="center" rowspan="2">No</td>
			<td align="center" rowspan="2">No Kontrak</td>
			<td align="center" rowspan="2">Tgl Pengajuan</td>
			<td align="center" rowspan="2">Tgl Kirim Kontrak</td>		
			
					
		 	<td align="center" rowspan="2">Produk</td>
			<td align="center" rowspan="2">Nama Produk</td>
			<td align="center" rowspan="2">Kd Cabang</td>
			<td align="center" rowspan="2">Nama Cabang</td>
			<td align="center" rowspan="2">No CIF</td>
			<td align="center" rowspan="2">Nama Customer</td>
			<td align="center" rowspan="2">No ID/KTP</td>
			<td align="center" rowspan="2">Jenis Kelamin</td>		
			<td align="center" rowspan="2">Status</td>
			<td align="center" rowspan="2">Pekerjaan</td>							
			<td align="center" rowspan="2">Alamat Tinggal</td>
			<td align="center" rowspan="2">Kelurahan</td>
			<td align="center" rowspan="2">Kecamatan</td>
			<td align="center" rowspan="2">Kota</td>
			<td align="center" rowspan="2">No HP</td>
			
			<td align="center" rowspan="2">Jenis</td>
			<td align="center" rowspan="2">Merek</td>									
			<td align="center" rowspan="2">Tipe</td>
			<td align="center" rowspan="2">Warna</td>
			<td align="center" rowspan="2">No Mesin</td>
			<td align="center" rowspan="2">No Rangka</td>
			<td align="center" rowspan="2">Tahun Kend</td>
			
			<td align="center" rowspan="2">Kondisi</td>							
			
			
			<td align="center" rowspan="2">Nama Dealer</td>
			<td align="center" rowspan="2">No Batch</td>
			<td align="center" rowspan="2">Tgl Batching</td>
			<td align="center" rowspan="2">Tenor</td>		

			<td align="center" rowspan="2">Harga OTR</td>
			<td align="center" rowspan="2">Nilai DP</td>
			<td align="center" rowspan="2">Pokok Hutang</td>
			<td align="center" rowspan="2">Rate Flat</td>
			<td align="center" rowspan="2">Rate Eff</td>
			<td align="center" rowspan="2">Margin(Bunga)</td>	
			<td align="center" rowspan="2">Total Hutang</td>				
			<td align="center" rowspan="2">Angsuran</td>		
			<td align="center" rowspan="2">Tgl Jatuh Tempo</td>			
						
			<td align="center" rowspan="2">By Admin</td>			
			<td align="center" rowspan="2">ADDB/M</td>	
			<td align="center" rowspan="2">Nilai Bayar</td>	
			<td align="center" rowspan="2">Tgl Pelunasan ke Dealer</td>	
			<td align="center" rowspan="2">No BPB ke Dealer</td>
			
			<td align="center" colspan="6">NPWP</td>
			<td align="center" colspan="5">Nominal Tac</td>
			<td align="center" colspan="18">Nominal Tagihan Tac</td>	
			
			<td align="center" rowspan="2">TTD Kontrak</td>
			
			<td align="center" rowspan="2">Jenis Pembiayaan</td>	
			<td align="center" rowspan="2">Skema Pembiayaan</td>		
			<td align="center" rowspan="2">CMO</td>		
			<td align="center" rowspan="2">Tgl Cetak Kontrak</td>		
			<td align="center" rowspan="2">User Cetak</td>
			
			<td align="center" rowspan="2">No Polisi</td>					
	
			<td align="center" rowspan="2">Nama BPKB</td>	
			
			<td align="center" rowspan="2">User Edit</td>
			<td align="center" rowspan="2">Tgl Edit</td>
		  </tr>
		  
		  <tr>
		  	<td align="center">Nama</td>		  
		  	<td align="center">Kacab</td>
		  	<td align="center">Nama</td>		  			
		 	<td align="center">SPV</td>
		  	<td align="center">Nama</td>		 			
		 	<td align="center">Sales</td>			
		';	
		foreach($arr_tac as $jenis_tac){
			echo '
			<td align="center">'.$jenis_tac.'</td>			 		
			';	
		}
		echo '<td align="center">Lain</td>	';
		foreach($arr_tac as $jenis_tac){
			echo '
		 	<td align="center">DPP</td>
		 	<td align="center">PPH</td>
		 	<td align="center">'.$jenis_tac.'</td>			
			<td align="center">BPB</td> 
			';
		}
		echo '<td align="center">Lain</td>
		<td align="center">BPB</td> 	';
		
		echo' </tr>';
		
/*			<td align="center" rowspan="2">Biaya Survey</td>		
			<td align="center" rowspan="2">Biaya Asuransi Syariah</td>		
			<td align="center" rowspan="2">Biaya Penjaminan Syariah</td>		
			<td align="center" rowspan="2">Biaya Pembebanan Agunan</td>		
			<td align="center" rowspan="2">Biaya Adm Sales</td>		
			<td align="center" rowspan="2">Biaya Lain</td>	
*/		
	$lrs = pg_query($query);
	$no=1;	
	
	$jns_kelamin['L']='Laki-laki';
	$jns_kelamin['P']='Perempuan';
	$jns_kelamin['B']='Badan Usaha';
	$jns_kelamin['M']='Masyarakat';
	$sts_nikah['1']='Menikah';
	$sts_nikah['2']='Belum Menikah';
	$sts_nikah['3']='Pisah/Cerai';
	
	while($lrow=pg_fetch_array($lrs)){
		$lrow["jenis_kelamin"]=$jns_kelamin[$lrow["jenis_kelamin"]];
		$lrow["status_pernikahan"]=$sts_nikah[$lrow["status_pernikahan"]];
		
		$lrow["jenis_asuransi"]=jenis_asuransi($lrow);		
		
		if($lrow["is_ttd_kontrak"]=='t')$lrow["is_ttd_kontrak"]='Ya';
		else $lrow["is_ttd_kontrak"]='Tidak';
				
		foreach($arr_tac as $jenis_tac){
			$tac=calc_tac($lrow["no_sbg"],$jenis_tac);
			$tagihan[$jenis_tac]=$tac['nominal'];
			$dpp[$jenis_tac]=$tac['dpp'];
			if($jenis_tac=='Dealer')$pph[$jenis_tac]=$tac['pph23'];
			else $pph[$jenis_tac]=$tac['pph21'];
			$npwp[$jenis_tac]=$tac['npwp'];
			$nama[$jenis_tac]=$tac['nama'];
		}
		$tac=calc_tac($lrow["no_sbg"],'Lain');
		$tagihan['Lain']=$tac['nominal'];

		$nominal_bayar=array();
		$bpb=array();
		$query_byr="
			select sum(nilai_bayar)as nominal_bayar,fk_sbg as fk_sbg1,transaksi,referensi from data_gadai.tblhistory_sbg
			where transaksi like 'Pembayaran TAC%' and tgl_batal is null and fk_sbg='".$lrow["no_sbg"]."'
			group by fk_sbg,transaksi,referensi
		";
		//showquery($query_byr);
		$lrs_byr = pg_query($query_byr);
		while($lrow_byr=  pg_fetch_array($lrs_byr)){
			$jenis_tac=str_replace("Pembayaran TAC ","",$lrow_byr["transaksi"]);
			$nominal_bayar[$jenis_tac]=$lrow_byr["nominal_bayar"];
			if($nominal_bayar[$jenis_tac]>0)$tagihan[$jenis_tac]=$nominal_bayar[$jenis_tac];
			$bpb[$jenis_tac]=$lrow_byr["referensi"];
		}
		
		$lrow["rate_eff"]=(flat_eff($lrow['rate_flat'],$lrow['lama_pinjaman'],$lrow['addm_addb']));
		
		if($lrow["tgl_pengiriman_kontrak"]!="")$num_tgl_pengiriman = '1';
		else $num_tgl_pengiriman = '0';		
		$jumlah_tgl_pengiriman +=  $num_tgl_pengiriman;
		
		echo '
			<tr>
				<td valign="top">'.$no.'</td>
				<td valign="top">&nbsp;'.$lrow["no_sbg"].'</td>		
				<td valign="top">'.($lrow["tgl_pengajuan"]==""?"":date("d/m/Y",strtotime($lrow["tgl_pengajuan"]))).'</td>
				<td valign="top">'.($lrow["tgl_pengiriman_kontrak"]==""?"":date("d/m/Y",strtotime($lrow["tgl_pengiriman_kontrak"]))).'</td>
				<td valign="top">'.$lrow["kd_produk"].'</td>
				<td valign="top">'.$lrow["nm_produk"].'</td>
				<td valign="top">'.$lrow["fk_cabang"].'</td>
				<td valign="top">'.$lrow["nm_cabang"].'</td>
				<td valign="top">'.$lrow["no_cif"].'</td>
				<td valign="top">'.$lrow["nm_customer"].'</td>
				<td valign="top">&nbsp;'.$lrow["no_id"].'</td>
				<td valign="top">'.$lrow["jenis_kelamin"].'</td>
				<td valign="top">'.$lrow["status_pernikahan"].'</td>
				<td valign="top">'.$lrow["nm_pekerjaan"].'</td>						
				<td valign="top">'.$lrow["alamat_tinggal"].'</td>
				<td valign="top">'.$lrow["nm_kelurahan"].'</td>
				<td valign="top">'.$lrow["nm_kecamatan"].'</td>
				<td valign="top">'.$lrow["nm_kota"].'</td>
				<td valign="top">&nbsp;'.$lrow["no_hp"].'</td>
				
				<td valign="top">'.$lrow["nm_jenis_barang"].'</td>
				<td valign="top">'.$lrow["nm_merek"].'</td>				
				<td valign="top">'.$lrow["nm_tipe"].'</td>
				<td valign="top">'.$lrow["warna"].'</td>
				<td valign="top">'.$lrow["no_mesin"].'</td>
				<td valign="top">'.$lrow["no_rangka"].'</td>
				<td valign="top">'.$lrow["tahun"].'</td>				
				<td valign="top">'.$lrow["kondisi_unit"].'</td>															
				<td valign="top">'.$lrow["nm_partner"].'</td>
				
				
				<td valign="top">'.$lrow["no_batch"].'</td>
				<td valign="top">'.($lrow["tgl_cair"]==""?"":date("d/m/Y",strtotime($lrow["tgl_cair"]))).'</td>
				<td valign="top">'.$lrow["lama_pinjaman"].'</td>	
				
				<td valign="top">'.convert_money("",$lrow["total_nilai_pinjaman"]).'</td>
				<td valign="top">'.convert_money("",$lrow["nilai_dp"]).'</td>
				<td valign="top">'.convert_money("",$lrow["pokok_hutang"]).'</td>				
				<td valign="top">'.$lrow['rate_flat'].'</td>
				<td valign="top">'.$lrow['rate_eff'].'</td>
				<td valign="top">'.convert_money("",$lrow["biaya_penyimpanan"]).'</td>	
				<td valign="top">'.convert_money("",$lrow["total_hutang"]).'</td>							
				<td valign="top">'.convert_money("",$lrow["angsuran_bulan"]).'</td>		
				<td valign="top">'.($lrow["tgl_jatuh_tempo"]==""?"":date("d/m/Y",strtotime($lrow["tgl_jatuh_tempo"]))).'</td>
								
				<td valign="top">'.convert_money("",$lrow["biaya_admin"]).'</td>		
				<td valign="top">'.$lrow["addm_addb"].'</td>				
				<td valign="top">'.convert_money("",$lrow["nilai_ap_customer"]).'</td>	
				<td valign="top">'.($lrow["tgl_pelunasan_dealer"]==""?"":date("d/m/Y",strtotime($lrow["tgl_pelunasan_dealer"]))).'</td>
				<td valign="top">'.$lrow["no_kwitansi_pelunasan"].'</td>
				<td valign="top">'.$nama['Kacab'].'</td>					
				<td valign="top">'.$npwp['Kacab'].'</td>	
				<td valign="top">'.$nama['SPV'].'</td>	
				<td valign="top">'.$npwp['SPV'].'</td>	
				<td valign="top">'.$nama['Sales'].'</td>	
				<td valign="top">'.$npwp['Sales'].'</td>	
				
				<td valign="top">'.convert_money("",$lrow["insentif_dealer"]).'</td>	
				<td valign="top">'.convert_money("",$lrow["insentif_kacab"]).'</td>	
				<td valign="top">'.convert_money("",$lrow["insentif_spv"]).'</td>	
				<td valign="top">'.convert_money("",$lrow["insentif_sales"]).'</td>	
				<td valign="top">'.convert_money("",$lrow["insentif_lain"]).'</td>	
			';
			foreach($arr_tac as $jenis_tac){
			echo'
				<td valign="top">'.convert_money("",$dpp[$jenis_tac]).'</td>	
				<td valign="top">'.convert_money("",$pph[$jenis_tac],2).'</td>				
				<td valign="top">'.convert_money("",$tagihan[$jenis_tac],2).'</td>	
				<td valign="top">'.$bpb[$jenis_tac].'</td>	
			';
			}
			echo'
				<td valign="top">'.convert_money("",$tagihan['Lain']).'</td>	
				<td valign="top">'.$bpb['Lain'].'</td>	
				<td valign="top">'.$lrow["is_ttd_kontrak"].'</td>
							
				<td valign="top">'.$lrow["jenis_pembiayaan"].'</td>				
				<td valign="top">'.$lrow["skema_pembiayaan"].'</td>			
				<td valign="top">'.$lrow["nm_cmo"].'</td>				
				<td valign="top">'.$lrow["log_action_date"].'</td>				
				<td valign="top">'.$lrow["log_action_username"].'</td>
				
				<td valign="top">'.$lrow["no_polisi"].'</td>
				<td valign="top">'.$lrow["nm_bpkb"].'</td>					
								
				<td valign="top">'.$lrow["user_edit"].'</td>				
				<td valign="top">'.$lrow["tgl_edit"].'</td>		
			</tr>
		';	
		
		/*
				<td valign="top">'.$lrow["biaya_survey"].'</td>				
				<td valign="top">'.$lrow["biaya_asuransi_syariah"].'</td>				
				<td valign="top">'.$lrow["biaya_penjaminan_syariah"].'</td>				
				<td valign="top">'.$lrow["biaya_pembebanan_agunan"].'</td>				
				<td valign="top">'.$lrow["biaya_adm_sales"].'</td>				
				<td valign="top">'.$lrow["biaya_lain"].'</td>	
		*/		
		$no++;
		$total['total_nilai_pinjaman']+=$lrow["total_nilai_pinjaman"];
		
		$total['nilai_dp']+=$lrow["nilai_dp"];
		$total['pokok_hutang']+=$lrow["pokok_hutang"];
		$total['biaya_penyimpanan']+=$lrow["biaya_penyimpanan"];
		$total['total_hutang']+=$lrow["total_hutang"];
		$total['nilai_ap_customer']+=$lrow["nilai_ap_customer"];
		
		
		$total['insentif_dealer']+=$lrow["insentif_dealer"];
		$total['insentif_kacab']+=$lrow["insentif_kacab"];
		$total['insentif_spv']+=$lrow["insentif_spv"];
		$total['insentif_sales']+=$lrow["insentif_sales"];
		$total['insentif_lain']+=$lrow["insentif_lain"];
		
		$total['Dealer']+=$tagihan['Dealer'];
		$total['Kacab']+=$tagihan['Kacab'];
		$total['SPV']+=$tagihan['SPV'];
		$total['Sales']+=$tagihan['Sales'];
		$total['Lain']+=$tagihan['Lain'];
	
	}
	echo '
		<tr>
			<td align="center"></td>
			<td align="center"><b>Grand Total</b></td>			
			<td align="center"></td>
			<td align="right">'.number_format($jumlah_tgl_pengiriman).'</td>
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"></td>		
			<td align="center"></td>
			<td align="center"></td>							
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"></td>									
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"></td>
			
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"></td>	
			<td align="right"><b>'.number_format($total['total_nilai_pinjaman']).'</b></td>
			<td align="right"><b>'.number_format($total['nilai_dp']).'</b></td>
			<td align="right"><b>'.number_format($total['pokok_hutang']).'</b></td>
			<td align="center"></td>
			<td align="center"></td>	
			<td align="right"><b>'.number_format($total['biaya_penyimpanan']).'</b></td>	
			<td align="right"><b>'.number_format($total['total_hutang']).'</b></td>				
			<td align="center"></td>		
			<td align="center"></td>			
			<td align="center"></td>
			<td align="center"></td>
			<td align="right"><b>'.number_format($total['nilai_ap_customer']).'</b></td>				
			<td align="center"></td>
			<td align="center"></td>			
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"></td>
			
			<td align="center"></td>
			<td align="center"></td>
			<td align="center"></td>
			
			<td align="right"><b>'.number_format($total['insentif_dealer']).'</b></td>
			<td align="right"><b>'.number_format($total['insentif_kacab']).'</b></td>
			<td align="right"><b>'.number_format($total['insentif_spv']).'</b></td>
			<td align="right"><b>'.number_format($total['insentif_sales']).'</b></td>
			<td align="right"><b>'.number_format($total['insentif_lain']).'</b></td>	
			';
			foreach($arr_tac as $jenis_tac){
			echo'	
			<td align="center"></td>		
			<td align="center"></td>						
			<td align="right"><b>'.number_format($total[$jenis_tac],2).'</b></td>	
			<td align="center"></td>	
			';
			}
			echo'
			<td align="right"><b>'.number_format($total['Lain']).'</b></td>	
			<td align="center"></td>				
			<td align="center"></td>		
			<td align="center"></td>		
			<td align="center"></td>		
			<td align="center"></td>		
			<td align="center"></td>
			<td align="center"></td>		
			<td align="center"></td>	
			<td align="center"></td>	
			<td align="center"></td>	
			<td align="center"></td>
		</tr>
	';	
	echo '</table>';
}



