<?php

include_once("report.php");

function filter_request(){
	global $jenis_produk,$showCab,$showTgl,$kolek,$nm_customer,$no_id,$judulTgl,$jenis_tgl,$is_tarik,$is_aktif;
	$jenis_produk=($_REQUEST["jenis_produk"]);
	$kolek=($_REQUEST["kolek"]);
	$nm_customer=($_REQUEST["nm_customer"]);
	$no_id=($_REQUEST["no_id"]);

	
	$showCab='t';
	$showTgl='t';
	$judulTgl='2';
	$jenis_tgl=($_REQUEST["jenis_tgl"]);
	
	$is_tarik=trim($_REQUEST["is_tarik"]);
	if($is_tarik==""){
		$is_tarik="f";
	}
	$is_aktif=trim($_REQUEST["is_aktif"]);
	if($is_aktif==""){
		$is_aktif="f";
	}
	
}


function fGet(){
?>

function fGetKTPData(){
   lObjLoad = getHTTPObject()
   lObjLoad.onreadystatechange=fGetDataKTPState
   lSentText='&no_id='+document.form1.no_id.value
   lObjLoad.open("POST","../ajax/get_data_ktp.php",true);
   lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
   lObjLoad.setRequestHeader("Content-Length",lSentText.length)
   lObjLoad.setRequestHeader("Connection","close")
   lObjLoad.send(lSentText);
}

function fGetDataKTPState(){
   if (this.readyState == 4){
      if (this.status==200 && this.responseText!="") {
         lTemp=this.responseText.split(',');	
		 document.form1.no_cif.value=lTemp[0]
		 document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value=lTemp[1]
      } else {
		 lAlerttxt="No ID tidak ada"
		 alert("Error : <br>"+lAlerttxt)
		 document.form1.no_cif.value="-"
		 document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value="-"
      }
   }
   
}

function fGetCIFData(){
   lObjLoad = getHTTPObject()
   lObjLoad.onreadystatechange=fGetDataCIFState
   lSentText="table=tblcustomer&field=(no_id||'¿'||nm_customer)&key=no_cif&value="+document.form1.no_cif.value
   lObjLoad.open("POST","../ajax/get_data.php",true);
   lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
   lObjLoad.setRequestHeader("Content-Length",lSentText.length)
   lObjLoad.setRequestHeader("Connection","close")
   lObjLoad.send(lSentText);
}

function fGetDataCIFState(){
  
   if (this.readyState == 4){
      if (this.status==200 && this.responseText!="") {
         lTemp=this.responseText.split('¿');	
		 document.form1.no_id.value=lTemp[0]
		 document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value=lTemp[1]
      } else {
		 document.form1.no_id.value="-"
		 document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value="-"
      }
   }
   
}
<?	
	
}
function create_list_kolektabilitas(){
	global $kolek;
	$l_list_obj = new select("select * from tblsetting_cadangan","kolektabilitas","kolektabilitas","kolek");
	$l_list_obj->set_default_value($kolek);
	$l_list_obj->add_item("--- Pilih ---",'',0);
	$l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;' onKeyUp='fNextFocus(event,document.form1.kolek)'");
}


function create_filter(){
	global $jenis_produk,$kolek,$no_id,$nm_customer,$is_tarik,$jenis_tgl,$is_aktif;
?>
   <tr bgcolor="efefef">
        <td style="padding:0 5 0 5" width="20%">Kolektabilitas</td>
        <td style="padding:0 5 0 5" width="30%">
        <?=create_list_kolektabilitas()?>
        </td>
        <td style="padding:0 5 0 5" width="20%"></td>
        <td style="padding:0 5 0 5" width="30%"></td>
        
     </tr>
   <? if($_SESSION['username']=='superuser'){//untuk ceking ?>
   <tr bgcolor="efefef">
        <td style="padding:0 5 0 5" width="20%">Aktif</td>
        <td style="padding:0 5 0 5" width="30%"><input type="checkbox" name="is_aktif" value="t" <?=(($is_aktif=="t")?"checked":"")?> >
</td>
        <td style="padding:0 5 0 5" width="20%">Tarik</td>
        <td style="padding:0 5 0 5" width="30%">
        <input type="checkbox" name="is_tarik" value="t" <?=(($is_tarik=="t")?"checked":"")?> >
        </td>
     </tr>
	<? }?>
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

<?	
}




function excel_content(){
	global $tgl,$fk_cabang,$nm_cabang,$jenis_produk,$kolek,$is_tarik,$jenis_tgl,$is_aktif;
	
	if(!$jenis_tgl)$jenis_tgl='tgl_batch';
	if($tgl != '' ){
		$lwhere.=" (tgl_lunas > '".$tgl." 23:59:59' or tgl_lunas is null) and ".$jenis_tgl." <='".$tgl." 23:59:59'";
		$tgl_data=$tgl;
	}
	if($no_cif != ''){
		$lwhere="";
		//if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cif = '".$no_cif."' ";
	}
		
	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cabang = '".$fk_cabang."' ";
	}
	
	if($jenis_produk != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" jenis_produk = '".$jenis_produk."' ";
	}
		
	if($fk_karyawan != ''){
		if ($lwhere1!="") $lwhere1.=" and ";
		$lwhere1.=" fk_karyawan_cmo = '".$fk_karyawan."' ";
	}
	
	$tahun_bulan=date("Y-m",strtotime($tgl));
	$fom=date("Y-m-01",strtotime($tgl));
	$eom=date('Y-m-t',strtotime($tgl))." 23:59:59";
	
	if ($lwhere!="") $lwhere=" where ".$lwhere;
	if ($lwhere1!="") $lwhere1=" where ".$lwhere1;

	$query = "
		select * from( 
			select * from (
				select * from(
					select tgl_lunas,status_sbg, tgl_cair, status_sbg, fk_sbg as no_sbg1,fk_cabang,fk_cif,tgl_jt,case when tgl_batch is null then tgl_cair else tgl_batch end as tgl_batch,no_batch from tblinventory
					left join (
						select referensi as no_batch,fk_sbg as fk_sbg_batch,tgl_batch from data_gadai.tblhistory_sbg
						left join(
							select distinct on(fk_owner)fk_owner,tr_date as tgl_batch from data_accounting.tblgl_auto
							where type_owner='AR'
						)as tbl on fk_owner=referensi 
						where transaksi='AR' and tgl_batal is null
					)as tbl on fk_sbg=fk_sbg_batch
				)as tblmain				
				".$lwhere." --and fk_sbg in('20102230300217','','')
			) as tblinventory
			left join (select kd_cabang, nm_cabang,fk_wilayah, fk_area from tblcabang) as tblcab on kd_cabang=fk_cabang
			where tgl_cair is not null 
		) as tblsbg		
		left join (
			select distinct on (fk_sbg)fk_sbg as fk_sbg_angsuran,tgl_jatuh_tempo as tgl_jt_berjalan,nilai_angsuran from data_fa.tblangsuran
			where (tgl_bayar > '".$tgl."' or tgl_bayar is null) and angsuran_ke>0
			order by fk_sbg,angsuran_ke
		)tblangsuran1 on no_sbg1=fk_sbg_angsuran
		left join viewsbg on no_sbg1=fk_sbg1
		left join (
			select sum(pokok_jt) as saldo_pokok, sum(bunga_jt) as saldo_bunga,sum(nilai_angsuran) as saldo_pinjaman, fk_sbg_os from(
				select bunga_jt, pokok_jt as pokok_jt,nilai_angsuran, fk_sbg as fk_sbg_os,tgl_jatuh_tempo from data_fa.tblangsuran
				".where_os_tblangsuran($tgl)."						
			)as tblar			
			group by fk_sbg_os
		)as tblos on no_sbg1=fk_sbg_os
		left join (
			select sum(nilai_angsuran) as saldo_blm_jto, fk_sbg as fk_sbg_os2 from data_fa.tblangsuran
			where tgl_jatuh_tempo >'".$tgl." 23:59:59'							
			group by fk_sbg
		)as tblos2 on no_sbg1=fk_sbg_os2
		left join tblwilayah on kd_wilayah=fk_wilayah
		left join viewkontrak on no_sbg=no_sbg1
		left join (select npk, fk_jabatan, nm_depan||' '||case when nm_belakang is not null then nm_belakang else '' end as nm_karyawan from tblkaryawan) as tblkaryawan on npk=fk_karyawan_sales		
		left join(
			select nm_produk,skema_pembiayaan,jenis_pembiayaan,no_sbg as no_sbg_appr,addm_addb,tgl_jatuh_tempo, nilai_dp,tgl_wo,total_hutang as total_ar,rate as rate_jaminan,tgl_pengiriman_kontrak,tgl_pengajuan from data_gadai.tblproduk_cicilan			
			left join tblmetode_perhitungan_jaminan on fk_metode=kd_metode
			left join tblproduk on fk_produk=kd_produk
		)as tblappr on no_sbg1 = no_sbg_appr
		left join(
			select * from viewkendaraan
			left join tblpartner on fk_partner_dealer=kd_partner
		)as tbltaksir on no_fatg=fk_fatg
		left join (
			select no_sbg_ar,fk_karyawan_cmo, nm_depan||' '||case when nm_belakang is not null then nm_belakang else '' end as nm_karyawan_cmo,fk_tujuan_transaksi,nm_tujuan_transaksi from data_gadai.tbltaksir_umum
			left join tblkaryawan on fk_karyawan_cmo=npk
			left join tbltujuan_transaksi on fk_tujuan_transaksi=kd_tujuan_transaksi
		) as tbltaksir_umum on no_sbg1=no_sbg_ar
		left join(
			select fk_sbg as fk_sbg_pertama, tgl_jatuh_tempo as tgl_jt1 from data_fa.tblangsuran 		
			where angsuran_ke=1
		)as tblbayar1 on no_sbg=fk_sbg_pertama			
		left join(
			select distinct on(fk_sbg)fk_sbg as fk_sbg_bayar, tgl_bayar as tgl_bayar_terakhir,nilai_angsuran as nominal_bayar_terakhir,no_kwitansi as no_kwitansi_terakhir,nm_bank,cara_bayar from data_fa.tblangsuran 
			left join (select fk_sbg as fk_sbg1,nm_bank,cara_bayar from data_fa.tblpembayaran_cicilan left join tblbank on fk_bank=kd_bank)as tbl on fk_sbg1=fk_sbg
			where (tgl_bayar is not null and tgl_bayar<='".$tgl."')
			and angsuran_ke>0 order by fk_sbg,angsuran_ke desc 
		)as tblbayar on no_sbg=fk_sbg_bayar	
			
		left join(
			select distinct on (fk_sbg)fk_sbg as fk_sbg_tarik,tgl_data as tgl_tarik from data_gadai.tblhistory_sbg
			where transaksi='Tarik'	and tgl_data<='".$tgl." 23:59:59' and tgl_batal is null
		)as tarik on no_sbg=fk_sbg_tarik					
		left join(
			select distinct on (fk_sbg)fk_sbg as fk_sbg_tebus,tgl_data as tgl_tebus from data_gadai.tblhistory_sbg
			where transaksi='Tebus'	and tgl_data<='".$tgl." 23:59:59' and tgl_batal is null
		)as tebus on no_sbg=fk_sbg_tebus	
				
		left join(
			select sum(pokok_jt)as pokok_c,sum(bunga_jt)as bunga_c,fk_sbg as fk_sbg_lunas from data_fa.tblangsuran 
			where (tgl_bayar>='".$fom."' and tgl_bayar<='".$tgl."')
			and angsuran_ke>0 group by fk_sbg_lunas
		)as lunas on no_sbg=fk_sbg_lunas	
		
		left join (
			select sum(nilai_bayar_denda)as total_denda,sum(nilai_bayar_denda2)as total_denda2,sum(biaya_tagih)as total_tagih,fk_sbg as fk_sbg_denda from data_fa.tblpembayaran_cicilan
			where (tgl_bayar>='".$fom."' and tgl_bayar<='".$tgl."')and tgl_batal is null
			group by fk_sbg_denda 
		)as tblpembayaran on no_sbg=fk_sbg_denda
		left join (
			select sum(akrual1+akrual2) as saldo_akrual,
			fk_sbg as fk_sbg_akru from data_fa.tblangsuran 
			where tgl_jatuh_tempo >='".$eom."' 	
			--where tgl_jatuh_tempo >='".$fom."' 	
			group by fk_sbg
		) as akrual on fk_sbg_akru=no_sbg		
		
		left join tblcustomer on no_cif=fk_cif 
		left join (
			select * from tblkelurahan 
			left join tblkecamatan on kd_kecamatan=fk_kecamatan
			left join tblkota on kd_kota=fk_kota
		) as tblkelurahan on kd_kelurahan=fk_kelurahan_ktp
		left join (
			select kd_kelurahan as kd_kelurahan_domisili,nm_kelurahan as nm_kelurahan_domisili,nm_kecamatan as nm_kecamatan_domisili,nm_kota as nm_kota_domisili from tblkelurahan 
			left join tblkecamatan on kd_kecamatan=fk_kecamatan
			left join tblkota on kd_kota=fk_kota
		) as tblkelurahan_domisili on kd_kelurahan_domisili=fk_kelurahan_tinggal
		left join tblpekerjaan on kd_pekerjaan=fk_pekerjaan
		
		".$lwhere1." 
		order by tgl_batch,no_batch asc-- limit 50
	";
	
	
	echo 	
	'<table border="1">
	     <tr>
		 	<td align="center" rowspan="2">NO</td>
			<td align="center" rowspan="2">NAMA DEBITUR</td>	
			<td align="center" rowspan="2">NO KTP</td>			
			<td align="center" rowspan="2">NO KONTRAK</td>			
			<td align="center" rowspan="2">NAMA PRODUK</td>			
			<td align="center" rowspan="2">SKEMA PEMBIAYAAN</td>			
			<td align="center" rowspan="2">JENIS PEMBIAYAAN</td>
			<td align="center" rowspan="2">TGL PENGAJUAN</td>
			<td align="center" rowspan="2">OTR</td>				
			<td align="center" rowspan="2">DOWNPAYMENT</td>
			<td align="center" rowspan="2">RATE FLAT%</td>	
			<td align="center" rowspan="2">RATE EFF%</td>										
			<td align="center" rowspan="2">SALDO AWAL</td>
			<td align="center" rowspan="2">DEBET POKOK</td>
			<td align="center" rowspan="2">DEBET BUNGA</td>
			<td align="center" rowspan="2">KREDIT POKOK</td>						
			<td align="center" rowspan="2">KREDIT BUNGA</td>
			<td align="center" rowspan="2">SALDO AKHIR POKOK</td>			
			<td align="center" rowspan="2">SALDO AKHIR BUNGA</td>
			<td align="center" rowspan="2">SALDO AKHIR TOTAL</td>
			<td align="center" rowspan="2">SALDO AKRUAL</td>		
			<td align="center" rowspan="2">SALDO PROPORSIONAL</td>									
			<td align="center" rowspan="2">TAZIR</td>
			<td align="center" rowspan="2">BY TAGIH</td>			
			<td align="center" rowspan="2">YANG BELUM JTO</td>
			<td align="center" rowspan="2">01-30 HARI</td>
			<td align="center" rowspan="2">31-60 HARI</td>
			<td align="center" rowspan="2"> > 60 HARI</td>
			<td align="center" rowspan="2">KETERANGAN BAYAR VIA</td>
			<td align="center" rowspan="2">CARA BAYAR</td>
			<td align="center" rowspan="2">ACCOUNT</td>			
			<td align="center" rowspan="2">STATUS</td>												
			<td align="center" colspan="3">STATUS PIUTANG</td>
			<td align="center" rowspan="2">DEALER</td>
			<td align="center" rowspan="2">NO.HP</td>
			<td align="center" rowspan="2">PEKERJAAN</td>								
			<td align="center" colspan="4">ALAMAT KTP</td>
			<td align="center" colspan="4">ALAMAT DOMISILI</td>
			<td align="center" rowspan="2">ANGSURAN</td>
			<td align="center" rowspan="2">ANGSURAN KE</td>
			<td align="center" rowspan="2">TENOR</td>
			<td align="center" rowspan="2">JATUH TEMPO PERTAMA</td>
			<td align="center" rowspan="2">JATUH TEMPO SAAT INI</td>
			<td align="center" rowspan="2">TGL TERAKHIR BAYAR</td>
			<td align="center" rowspan="2">END JTO</td>		
			<td align="center" rowspan="2">KOLEKTABILITAS</td>
			<td align="center" rowspan="2">KETERLAMBATAN</td>
			<td align="center" rowspan="2">TUJUAN TRANSAKSI</td>
			<td align="center" rowspan="2">NO BATCH</td>	
			<td align="center" rowspan="2">TGL BATCHING</td>						
			<td align="center" rowspan="2">TGL KIRIM KONTRAK</td>						
			<td align="center" rowspan="2">CMO/SURVEYOR</td>
			<td align="center" rowspan="2">SALES DEALER</td>
		  </tr>
		  <tr>
			<td align="center">STATUS</td>
			<td align="center">BULAN</td>
			<td align="center">TAHUN</td>
			<td align="center">ALAMAT KTP</td>								
			<td align="center">KELURAHAN</td>			
			<td align="center">KECAMATAN</td>
			<td align="center">KABUPATEN</td>
			<td align="center">ALAMAT KTP</td>								
			<td align="center">KELURAHAN</td>			
			<td align="center">KECAMATAN</td>
			<td align="center">KABUPATEN</td>
		  </tr>
	';
	// showquery($query);
	$lrs = pg_query($query);
	$no=1;
	//$is_tarik='t';
	//$is_aktif='t';
	while($lrow=pg_fetch_array($lrs)){	
		//echo date("h:i:s").'a<br>';
		$view='t';
		$fk_sbg=$no_sbg=$lrow["no_sbg1"];		
		
		//CARI PER OVD
		$i=1;
		$ovd=array();
		$ovd_data=0;
		$lrow["ang_ke"]=-1;
		
		if($lrow["tgl_jt_berjalan"]){
		$query_ovd="
		select * from(
			select date_part('days','".$tgl."'-tgl_jatuh_tempo)as ovd,* from data_fa.tblangsuran 
			where (tgl_bayar is null or (tgl_bayar is not null and tgl_bayar>='".$tgl."')) and fk_sbg='".$no_sbg."' 
			and angsuran_ke>0
			and tgl_jatuh_tempo>='".$lrow["tgl_jt_berjalan"]."'
			order by angsuran_ke 				
		)as tblmain		
		";
		//showquery($query_ovd);
		$lrs_ovd=pg_query($query_ovd);
		while($lrow_ovd=pg_fetch_array($lrs_ovd)){			
			if($i==1){
				$lrow["ang_ke"]=$lrow_ovd["angsuran_ke"]-1;
			}
						
			if($lrow_ovd[ovd]>0 && $lrow_ovd[ovd]<=30){
				$ovd['1']['ar']+=$lrow_ovd["nilai_angsuran"];
				$ovd['1']['od']=$lrow_ovd["ovd"];
				$ovd['1']['pokok']+=$lrow_ovd["pokok_jt"];
				$ovd['1']['bunga']+=$lrow_ovd["bunga_jt"];
			}
			if($lrow["saldo_pinjaman"]-$lrow["saldo_blm_jto"]==$lrow_ovd["nilai_angsuran"]){
				$ovd['1']['ar']=$lrow_ovd["nilai_angsuran"];
				$ovd['1']['od']=$lrow_ovd["ovd"];
				$ovd['1']['pokok']=$lrow_ovd["pokok_jt"];
				$ovd['1']['bunga']=$lrow_ovd["bunga_jt"];
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
				$lrow["total_tunggakan"]+=$lrow_ovd["nilai_angsuran"];		
				if(!$ovd_data)$ovd_data=$lrow_ovd[ovd];	
			}		
			
			$i++;
		}
		}
		
		if($lrow["ang_ke"]<0)$lrow["ang_ke"]=$lrow["tenor"];
		
		$lrow["pokok_d"]=0;
		$lrow["bunga_d"]=0;
		$lrow['saldo_awal']=($lrow["pokok_c"]+$lrow["bunga_c"])+$lrow["saldo_pinjaman"];
		
		$tahun_bulan_cair=date("Y-m",strtotime($lrow["tgl_cair"]));
		if($tahun_bulan==$tahun_bulan_cair){//hanya untuk bulan pencairan
			$lrow['saldo_awal']=0;
			$lrow["pokok_d"]=$lrow["pokok_awal"];
			$lrow["bunga_d"]=$lrow["bunga_awal"];
			if($lrow["addm_addb"]=='M'){
				$lrow["pokok_d"]-=$lrow['nilai_angsuran'];		
				$lrow["pokok_c"]-=$lrow['nilai_angsuran'];	
			}
			//$lrow["bunga_d"]=$lrow["pokok_d"]=$lrow["pokok_c"]=0;//cek penerimaan
		}		
		
		$lrow["bunga_d"]+=$lrow['total_denda'];
		$lrow["bunga_c"]+=$lrow['total_denda'];
				
		$query_cad='select * from tblsetting_cadangan';
		$lrs_cad=pg_query($query_cad);
		while($lrow_cad=pg_fetch_array($lrs_cad)){
			
			if($ovd_data>=$lrow_cad["ovd_awal"]&& $ovd_data<=$lrow_cad["ovd_akhir"]){
				$kolektabilitas=$lrow_cad['kolektabilitas'];	
			}	
			if($kolek != '')$view='f';
		}
				
		if($lrow["tgl_wo"] && strtotime($lrow["tgl_wo"])<=strtotime($tgl)){
			$lrow["status_piutang"]='WO';
			$lrow["bulan_wo"]=date("M",strtotime($lrow["tgl_wo"]));
			$lrow["tahun_wo"]=date("Y",strtotime($lrow["tgl_wo"]));
			$kolektabilitas='WO';
		}else{
			$lrow["status_piutang"]='Piutang Aktif';
		}		
				
		
		if($lrow["fk_sbg_tarik"]){
			if(!$lrow["fk_sbg_tebus"] || $lrow["tgl_tarik"]>$lrow["tgl_tebus"]){
				$lrow["status"]='TARIK';
				$ovd_data=strtotime($lrow["tgl_tarik"])-strtotime($lrow["tgl_jt_berjalan"]);
				$ovd_data=$ovd_data/(60*60*24);
				if($ovd_data<0)$ovd_data=0;
				$kolektabilitas='Tarik';
				
				$fom_tarik=date("Y-m-01",strtotime($lrow["tgl_tarik"]));
				
				$query_akru="
				select sum(akrual1+akrual2) as saldo_akrual,
				fk_sbg as fk_sbg_akru from data_fa.tblangsuran 
				where tgl_jatuh_tempo >='".$fom_tarik."' and fk_sbg='".$lrow["no_sbg1"]."' group by fk_sbg
				";
				//showquery($query_akru);
				$lrs_akru=pg_query($query_akru);
				$lrow_akru=pg_fetch_array($lrs_akru);
				$lrow["saldo_akrual"]=$lrow_akru["saldo_akrual"];

				if($is_aktif=='t')$view='f';
			}elseif($lrow["fk_sbg_tebus"]){
				$lrow["status"]='TEBUS';		
				if($is_tarik=='t')$view='f';
			}
		}else{
			$lrow["status"]='NORMAL';
			if($is_tarik=='t')$view='f';
		}
		
		if($kolek != ''){//filter kolek
			if($kolek==$kolektabilitas)$view='t';							
		}
				
		//SUMMARY
		if($kolektabilitas!='Tarik' && $kolektabilitas!='WO'){
			$sum['ALL']["saldo_pokok"]+=$lrow["saldo_pokok"];
			$sum['ALL']["saldo_bunga"]+=$lrow["saldo_bunga"];
			$sum['ALL']["qty"]+=1;
			$sum['ALL']["total"]+=($lrow["saldo_pokok"]+$lrow["saldo_bunga"]);
		}
		$sum[$kolektabilitas]["saldo_pokok"]+=$lrow["saldo_pokok"];
		$sum[$kolektabilitas]["saldo_bunga"]+=$lrow["saldo_bunga"];
		$sum[$kolektabilitas]["qty"]+=1;
		$sum[$kolektabilitas]["total"]+=($lrow["saldo_pokok"]+$lrow["saldo_bunga"]);
		
		
		$lrow["rate_eff"]=(flat_eff($lrow['rate'],$lrow['lama_pinjaman'],$lrow['addm_addb']));;
		
		$lrow["saldo_proporsional"]=$lrow["saldo_pinjaman"]-$lrow["saldo_akrual"];

		if($lrow["saldo_blm_jto"] < 0 && $lrow["saldo_pinjaman"] == 0){
			$saldo_blm_jto = 0;
		} else {
			$saldo_blm_jto = $lrow["saldo_blm_jto"];
		}
		
		if($view=='t'){
		
		echo '
			<tr>
				<td valign="top">'.$no.'</td>
				<td valign="top">'.$lrow["nm_customer"].'</td>		
				<td valign="top">'.$lrow["no_id"].'</td>
				<td valign="top">&nbsp;'.$lrow["no_sbg1"].'</td>
				<td valign="top">&nbsp;'.$lrow["nm_produk"].'</td>
				<td valign="top">&nbsp;'.$lrow["skema_pembiayaan"].'</td>
				<td valign="top">&nbsp;'.$lrow["jenis_pembiayaan"].'</td>
				<td valign="top">'.($lrow["tgl_pengajuan"]==""?"":date("d/m/Y",strtotime($lrow["tgl_pengajuan"]))).'</td>	
				<td valign="top">'.number_format($lrow['total_nilai_pinjaman'],2).'</td>
				<td valign="top">'.number_format($lrow['nilai_dp'],2).'</td>
				<td valign="top">'.number_format($lrow['rate'],2).'</td>	
				<td valign="top">'.number_format($lrow['rate_eff'],2).'</td>											
				<td valign="top">'.number_format($lrow['saldo_awal'],2).'</td>				
				<td valign="top">'.number_format($lrow["pokok_d"]).'</td>
				<td valign="top">'.number_format($lrow["bunga_d"]).'</td>
				<td valign="top">'.number_format($lrow["pokok_c"]).'</td>
				<td valign="top">'.number_format($lrow["bunga_c"]).'</td>
				<td valign="top">'.number_format($lrow["saldo_pokok"]).'</td>
				<td valign="top">'.number_format($lrow["saldo_bunga"]).'</td>
				<td valign="top">'.number_format($lrow["saldo_pinjaman"]).'</td>
				<td valign="top">'.number_format($lrow["saldo_akrual"]).'</td>
				<td valign="top">'.number_format($lrow["saldo_proporsional"]).'</td>
				<td valign="top">'.number_format($lrow["total_denda2"]).'</td>				
				<td valign="top">'.number_format($lrow["total_tagih"]).'</td>								
				<td valign="top">'.number_format($saldo_blm_jto).'</td>
				<td valign="top">'.number_format($ovd['1']['ar']).'</td>
				<td valign="top">'.number_format($ovd['2']['ar']).'</td>
				<td valign="top">'.number_format($ovd['3']['ar']).'</td>
				<td valign="top">'.($lrow["no_kwitansi_terakhir"]==""?"Account Receivable":$lrow["no_kwitansi_terakhir"]).'</td>
				<td valign="top">'.$lrow["cara_bayar"].'</td>							
				<td valign="top">'.$lrow["nm_bank"].'</td>				
				<td valign="top">'.$lrow["status"].'</td>				
				<td valign="top">'.$lrow["status_piutang"].'</td>
				<td valign="top">'.$lrow["bulan_wo"].'</td>
				<td valign="top">'.$lrow["tahun_wo"].'</td>
				<td valign="top">'.$lrow["nm_partner"].'</td>
				<td valign="top">&nbsp;'.$lrow["no_hp"].'</td>
				<td valign="top">'.$lrow["nm_pekerjaan"].'</td>
				<td valign="top">'.$lrow["alamat_ktp"].'</td>
				<td valign="top">'.$lrow["nm_kelurahan"].'</td>
				<td valign="top">'.$lrow["nm_kecamatan"].'</td>
				<td valign="top">'.$lrow["nm_kota"].'</td>
				<td valign="top">'.$lrow["alamat_tinggal"].'</td>
				<td valign="top">'.$lrow["nm_kelurahan_domisili"].'</td>
				<td valign="top">'.$lrow["nm_kecamatan_domisili"].'</td>
				<td valign="top">'.$lrow["nm_kota_domisili"].'</td>
				<td valign="top">'.number_format($lrow['nilai_angsuran'],2).'</td>
				<td valign="top" align="right">'.$lrow["ang_ke"].'</td>
				<td valign="top" align="right">'.$lrow["tenor"].'</td>
				<td valign="top">'.($lrow["tgl_jt1"]==""?"":date("d/m/Y",strtotime($lrow["tgl_jt1"]))).'</td>	
				<td valign="top">'.($lrow["tgl_jt_berjalan"]==""?"":date("d/m/Y",strtotime($lrow["tgl_jt_berjalan"]))).'</td>
				<td valign="top">'.($lrow["tgl_bayar_terakhir"]==""?"":date("d/m/Y",strtotime($lrow["tgl_bayar_terakhir"]))).'</td>
				<td valign="top">'.($lrow["tgl_jt"]==""?"":date("d/m/Y",strtotime($lrow["tgl_jt"]))).'</td>
				
				<td valign="top">'.$kolektabilitas.'</td>
				<td valign="top">'.$ovd_data.'</td>
				<td valign="top">'.$lrow["nm_tujuan_transaksi"].'</td>
				<td valign="top">'.$lrow["no_batch"].'</td>		
				<td valign="top">'.($lrow["tgl_batch"]==""?"":date("d/m/Y",strtotime($lrow["tgl_batch"]))).'</td>	
				<td valign="top">'.($lrow["tgl_pengiriman_kontrak"]==""?"":date("d/m/Y",strtotime($lrow["tgl_pengiriman_kontrak"]))).'</td>		
				<td valign="top">'.$lrow["nm_karyawan_cmo"].'</td>
				<td valign="top">'.$lrow["nm_karyawan"].'</td>
				
							
			</tr>
		';	
		$no++;
		$total['pokok_d']+=$lrow["pokok_d"];
		$total['bunga_d']+=$lrow["bunga_d"];
		$total['pokok_c']+=$lrow["pokok_c"];
		$total['bunga_c']+=$lrow["bunga_c"];
		$total['saldo_pokok']+=$lrow["saldo_pokok"];
		$total['saldo_bunga']+=$lrow["saldo_bunga"];
		$total['saldo_pinjaman']+=$lrow["saldo_pinjaman"];
		$total['saldo_akrual']+=$lrow["saldo_akrual"];
		$total['saldo_proporsional']+=$lrow["saldo_proporsional"];
		
		$total['saldo_blm_jto']+=($saldo_blm_jto);
		$total['saldo_awal']+=$lrow["saldo_awal"];
		$total['qty']+=1;
		$total['total_denda2']+=$lrow["total_denda2"];
		$total['total_tagih']+=$lrow["total_tagih"];
		
		}
		//echo date("h:i:s").'b<br>';
	}
	//echo $total['pokok_d'];
	echo '
		<tr>
			<td valign="top"><b>Grand Total</b></td>
			<td valign="top"></td>				
			<td valign="top"></td>			
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"><b>'.number_format($total['saldo_awal']).'</b></td>
			<td valign="top"><b>'.number_format($total['pokok_d']).'</b></td>
			<td valign="top"><b>'.number_format($total['bunga_d']).'</b></td>
			<td valign="top"><b>'.number_format($total["pokok_c"]).'</b></td>
			<td valign="top"><b>'.number_format($total["bunga_c"]).'</b></td>
			<td valign="top"><b>'.number_format($total["saldo_pokok"]).'</b></td>
			<td valign="top"><b>'.number_format($total["saldo_bunga"]).'</b></td>
			<td valign="top"><b>'.number_format($total["saldo_pinjaman"]).'</b></td>
			<td valign="top"><b>'.number_format($total["saldo_akrual"]).'</b></td>	
			<td valign="top"><b>'.number_format($total["saldo_proporsional"]).'</b></td>
				
			<td valign="top"><b>'.number_format($total["total_denda2"]).'</b></td>	
			<td valign="top"><b>'.number_format($total["total_tagih"]).'</b></td>			
			<td valign="top"><b>'.number_format(($total["saldo_blm_jto"])).'</b></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>	
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			
		</tr>
	';		
				
	echo '</table>';
	
	echo '<br>';
	echo '<br>';
	echo '<br>';
	
	//print_r($sum);
	
	echo '<table border="1">
			<tr>
				<td valign="top"></td>
				<td valign="top"></td>				
				<td valign="top">Unit</td>
				<td valign="top">O/S</td>
				<td valign="top">Bunga</td>
				<td valign="top">Total</td>
				<td valign="top"></td>
			</tr>	
	';	
	
	$persen_tarik=round($sum['Tarik']["saldo_pokok"]/($sum['ALL']["saldo_pokok"]+$sum['Tarik']["saldo_pokok"])*100,2);
	echo '
			<tr>
				<td valign="top"></td>			
				<td valign="top">Tarik</td>				
				<td valign="top" align="right">'.$sum['Tarik']["qty"].'</td>
				<td valign="top" align="right">'.number_format($sum['Tarik']["saldo_pokok"]).'</td>
				<td valign="top" align="right">'.number_format($sum['Tarik']["saldo_bunga"]).'</td>
				<td valign="top" align="right">'.number_format($sum['Tarik']["total"]).'</td>
				<td valign="top" align="right">'.number_format($persen_tarik,2).'</td>
			</tr>
			<tr>
				<td valign="top"></td>			
				<td valign="top">WO</td>				
				<td valign="top" align="right">'.$sum['WO']["qty"].'</td>
				<td valign="top" align="right">'.number_format($sum['WO']["saldo_pokok"]).'</td>
				<td valign="top" align="right">'.number_format($sum['WO']["saldo_bunga"]).'</td>
				<td valign="top" align="right">'.number_format($sum['WO']["total"]).'</td>
				<td valign="top"></td>
			</tr>
			
			<tr>
				<td valign="top"></td>			
				<td valign="top"></td>				
				<td valign="top"></td>
				<td valign="top"></td>
				<td valign="top"></td>
				<td valign="top"></td>
				<td valign="top">NPF GROSS</td>
			</tr>	
	';		
		
	$total['total']+=$sum[$kolektabilitas]["total"];
			
	
	$query_cad='select * from tblsetting_cadangan';
	$lrs_cad=pg_query($query_cad);	
	while($lrow_cad=pg_fetch_array($lrs_cad)){
		$kolektabilitas=$lrow_cad['kolektabilitas'];
		$j++;
		
		$tr_style='black';
		
		//echo $j.'<br>';
		if($total['saldo_pinjaman']>0){
			$persen_npf=round($sum[$kolektabilitas]["saldo_pokok"]/($sum['ALL']["saldo_pokok"])*100,2);
		}else $persen_npf=0;
		if($j>=3){
			$tr_style='red';
			$npf_gross+=$persen_npf;
		}
		
		echo '
			<tr style="color:'.$tr_style.'">
				<td valign="top">&nbsp;'.$lrow_cad["ovd_awal"].'-'.$lrow_cad["ovd_akhir"].'</td>			
				<td valign="top">'.$lrow_cad["kolektabilitas"].'</td>				
				<td valign="top" align="right">'.number_format($sum[$kolektabilitas]["qty"]).'</td>
				<td valign="top" align="right">'.number_format($sum[$kolektabilitas]["saldo_pokok"]).'</td>
				<td valign="top" align="right">'.number_format($sum[$kolektabilitas]["saldo_bunga"]).'</td>
				<td valign="top" align="right">'.number_format($sum[$kolektabilitas]["total"]).'</td>	
				<td valign="top" align="right">'.number_format($persen_npf,2).'</td>	
			</tr>	
		';
		if($j>=3){
			$os_kurang_lancar+=$sum[$kolektabilitas]["saldo_pokok"];
		}
	}
	
	echo'
			<tr>
				<td valign="top"></td>			
				<td valign="top">Total Aktif</td>				
				<td valign="top" align="right">'.number_format($sum['ALL']["qty"]).'</td>
				<td valign="top" align="right">'.number_format($sum['ALL']["saldo_pokok"]).'</td>
				<td valign="top" align="right">'.number_format($sum['ALL']["saldo_bunga"]).'</td>
				<td valign="top" align="right">'.number_format($sum['ALL']["total"]).'</td>	
				<td valign="top" align="right">'.number_format($npf_gross,2).'</td>
			</tr>';
	echo '</table>';
	
	
}

function get_user($username){
	$nm_user=get_rec("tbluser left join tblkaryawan on fk_karyawan=npk","nm_depan","username='".$username."'");
	
	return $nm_user;
}


