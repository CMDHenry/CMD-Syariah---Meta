<?php

include_once("report.php");

function filter_request(){
	global $jenis_report,$showCab,$showTgl,$no_cif,$nm_customer,$no_id,$is_tgl_jatuh_tempo;
	$jenis_report=($_REQUEST["jenis_report"]);
	$no_cif=($_REQUEST["no_cif"]);
	$nm_customer=($_REQUEST["nm_customer"]);
	$no_id=($_REQUEST["no_id"]);

	
	$showCab='t';
	$showTgl='t';
	
	
	$is_tgl_jatuh_tempo=trim($_REQUEST["is_tgl_jatuh_tempo"]);
	if($is_tgl_jatuh_tempo==""){
		$is_tgl_jatuh_tempo="f";
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


function create_filter(){
	global $jenis_report,$no_cif,$no_id,$nm_customer,$is_tgl_jatuh_tempo;
?>
       
   <tr bgcolor="efefef">
        <td style="padding:0 5 0 5" width="20%">Jenis Report</td>
        <td style="padding:0 5 0 5" width="30%">
        <select id='jenis_report' name="jenis_report">
                <option value="Tarik"<?= (($jenis_report=='Tarik')?"selected":"") ?>>Tarik</option>
                <option value="Tebus"<?=(($jenis_report=='Tebus')?"selected":"") ?>>Tebus</option>
             </select>
        </td>
         <td style="padding:0 5 0 5" width="20%"></td>
         <td style="padding:0 5 0 5" width="30%"></td>
<!--        <td style="padding:0 5 0 5" width="20%">Jatuh Tempo</td>
        <td style="padding:0 5 0 5" width="30%">
        <input type="checkbox" name="is_tgl_jatuh_tempo" value="t" <?=(($is_tgl_jatuh_tempo=="t")?"checked":"")?> >
        </td>
     </tr>
   

<?	
}




function excel_content(){
	global $tgl,$fk_cabang,$nm_cabang,$jenis_report,$no_cif,$is_tgl_jatuh_tempo;
	if($tgl=='')$tgl=today_db;
	
	if($tgl != '' ){
		$lwhere.=" (tgl_lunas > '".$tgl."' or tgl_lunas is null) and tgl_cair <='".$tgl."'";
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
	
	if($is_tgl_jatuh_tempo=='t'){
		//$lwhere1.="ovd > 0 ";
		$lwhere1.=" where tgl_jt_akhir between '".$tgl." 00:00:00' and '".$tgl." 23:59:59'";
		$tgl_data=today_db;
	}
	if($jenis_report!=''){
		$lwhere_jenis.="
		inner join(
			select distinct on (fk_sbg)fk_sbg as fk_sbg2,fk_user as fk_user2,transaksi,tgl_data as tgl_transaksi from data_gadai.tblhistory_sbg
			where transaksi='".$jenis_report."'	and tgl_data<='".$tgl." 23:59:59' and tgl_batal is null
			order by fk_sbg,tgl_data
		)as tbl1 on no_sbg=fk_sbg2
		";
	}
	if($jenis_report=='Tarik'){
		if ($lwhere1!="") $lwhere.=" and ";
		$lwhere1.=" fk_sbg_tebus is null or (tgl_tarik>tgl_tebus) ";
	}
	
	if($jenis_report=='Tebus'){
		if ($lwhere1!="") $lwhere.=" and ";
		$lwhere1.=" tgl_tebus >='".date("m/01/Y",strtotime($tgl))." 00:00:00' and tgl_tebus <='".$tgl." 23:59:59'";
	}
	
	
	if ($lwhere1!="") $lwhere1=" where ".$lwhere1;
	
	if ($lwhere!="") $lwhere=" where ".$lwhere;


	$query = "
		select tgl_tarik,tgl_tebus,date_part('day',(tgl_tarik-tgl_jt_berjalan))::numeric as ovd_tarik,extract(month from tgl_booking)||'/'||extract(year from tgl_booking)as periode, round(rate*(360/jumlah_hari),2) as rate_pa,tgl_booking+ interval '120 days' as tgl_buyback,* from( 
			select * from (
				select tgl_lunas,status_sbg, tgl_cair,tgl_cair as tgl_booking, status_sbg, fk_sbg as no_sbg1,fk_cabang,fk_cif,kd_produk,fk_produk,jumlah_hari,nm_produk,tgl_jt,jenis_produk from tblinventory
				left join (
					select kd_produk,nm_produk,jenis_produk,jumlah_hari from tblproduk
					left join tblrate on fk_rate=kd_rate
				) as tblproduk on kd_produk=fk_produk
				".$lwhere." --and fk_sbg in('001153071801076','001152051800329')
			) as tblinventory
			left join (select kd_cabang, nm_cabang,fk_wilayah, fk_area from tblcabang) as tblcab on kd_cabang=fk_cabang
			where tgl_cair is not null 
		) as tblsbg		
		left join (
			select distinct on (fk_sbg)angsuran_ke as ang_ke,fk_sbg as fk_sbg_angsuran,tgl_jatuh_tempo as tgl_jt_berjalan,nilai_angsuran from data_fa.tblangsuran
			where (tgl_bayar > '".$tgl."' or tgl_bayar is null) and angsuran_ke>0
			order by fk_sbg,angsuran_ke
		)tblangsuran1 on no_sbg1=fk_sbg_angsuran
		left join viewsbg on no_sbg1=fk_sbg1
		left join (
			select sum(saldo_os) as saldo_os, sum(saldo_bunga) as saldo_bunga, fk_sbg_os from(
				select bunga_jt as saldo_bunga, pokok_jt as saldo_os,fk_sbg as fk_sbg_os from data_fa.tblangsuran
				".where_os_tblangsuran($tgl)."						
			)as tblar
			inner join tblinventory on fk_sbg = fk_sbg_os
			".where_os_tblinventory($tgl)."
			group by fk_sbg_os
		)as tblos on no_sbg1=fk_sbg_os
		left join tblcustomer on no_cif=fk_cif 
		left join tblwilayah on kd_wilayah=fk_wilayah
		left join viewkontrak on no_sbg=no_sbg1
		left join(
			select no_sbg as no_sbg_appr,addm_addb,tgl_jatuh_tempo, nilai_dp,tgl_wo,total_hutang as total_ar,status_stnk,masa_berlaku_pajak,fk_kolektor ,nm_karyawan as nm_kolektor from data_gadai.tblproduk_cicilan
			left join (select npk, fk_jabatan, nm_depan||' '||case when nm_belakang is not null then nm_belakang else '' end as nm_karyawan from tblkaryawan) as tblkaryawan on npk=fk_kolektor					
		)as tblappr on no_sbg1 = no_sbg_appr
		left join(
			select * from viewkendaraan
			left join tblpartner on fk_partner_dealer=kd_partner
		)as tbltaksir on no_fatg=fk_fatg
		left join (
			select no_sbg_ar, nm_depan||' '||case when nm_belakang is not null then nm_belakang else '' end as nm_cmo from data_gadai.tbltaksir_umum
			left join tblkaryawan on fk_karyawan_cmo=npk
		) as tbltaksir_umum on no_sbg1=no_sbg_ar
		left join(
			select distinct on(fk_sbg)fk_sbg as fk_sbg_bayar, tgl_bayar as tgl_bayar_terakhir,nilai_angsuran as nominal_bayar_terakhir,no_kwitansi as no_kwitansi_terakhir from data_fa.tblangsuran 
			where (tgl_bayar is not null and tgl_bayar<='".$tgl."')
			and angsuran_ke>0 order by fk_sbg,angsuran_ke desc 
		)as tblbayar on no_sbg=fk_sbg_bayar		
		left join(
			select distinct on (tblhistory_sbg.fk_sbg)tblhistory_sbg.fk_sbg as fk_sbg_tarik,tgl_data as tgl_tarik,fk_user as fk_user_tarik,biaya_tarik,biaya_gudang,biaya_lainnya from data_gadai.tblhistory_sbg
			left join data_fa.tblpembayaran_tebus on tblpembayaran_tebus.fk_sbg=tblhistory_sbg.fk_sbg
			where transaksi='Tarik'	and tgl_data<='".$tgl." 23:59:59' and tblhistory_sbg.tgl_batal is null
			order by tblhistory_sbg.fk_sbg,tgl_data desc
		)as tarik on no_sbg=fk_sbg_tarik	
		left join(
			select distinct on (tblhistory_sbg.fk_sbg)tblhistory_sbg.fk_sbg as fk_sbg_tebus,tgl_data as tgl_tebus from data_gadai.tblhistory_sbg
			where transaksi='Tebus'	and tgl_data<='".$tgl." 23:59:59' and tblhistory_sbg.tgl_batal is null
			order by tblhistory_sbg.fk_sbg,tgl_data desc
		)as tebus on no_sbg=fk_sbg_tebus		
			
		".$lwhere_jenis."
		".$lwhere1."
		order by tgl_booking asc
	";	
	//showquery($query);
	
	echo 	
	'<table border="1">
	     <tr>
		 	<td align="center" rowspan="2">NO</td>
			<td align="center" rowspan="2">NAMA DEBITUR</td>			
			<td align="center" rowspan="2">NO KONTRAK</td>
	';
	if($jenis_report=="Tarik"){
		echo '
			<td align="center" rowspan="2">SALDO PIUTANG</td>
			<td align="center" rowspan="2">PIUTANG YANG BELUM JATUH TEMPO</td>
		';
	}
	echo '		
			<td align="center" rowspan="2">SALDO AKHIR POKOK/OS</td>
	';
	if($jenis_report=="Tarik"){
		echo '
			<td align="center" colspan="6">AR OVERDUE</td>
		';
	}
	echo'
			<td align="center" rowspan="2">OVERDUE SAAT DITARIK</td>
			<td align="center" rowspan="2">TOTAL TUNGGAKAN</td>
			<td align="center" rowspan="2">ANGSURAN</td>
			<td align="center" rowspan="2">ANGSURAN KE </td>
			<td align="center" rowspan="2">TENOR</td>			
			<td align="center" rowspan="2">TGL JTO ANSGURAN BAYAR</td>												
			<td align="center" rowspan="2">TANGGAL TARIK</td>
			<td align="center" rowspan="2">DEALER</td>			
			<td align="center" colspan="9">DATA KENDARAAN</td>
			<td align="center" rowspan="2">INISIAL KOLEKTOR</td>
			<td align="center" rowspan="2">CMO</td>			
			';
			
	if($jenis_report=="Tebus"){
	echo'
			<td align="center" colspan="5">KETERANGAN TEBUS</td>
	';
	}

	echo '		
		  </tr>
		  <tr>
		  
	';
	if($jenis_report=="Tarik"){
		echo '
			<td align="center">OD (01-30)</td>
			<td align="center">ANGSURAN (01-30)</td>
			<td align="center">OD (31-60)</td>
			<td align="center">ANGSURAN (31-60)</td>								
			<td align="center">OD > 60</td>			
			<td align="center">ANGSURAN > 60</td>
	
		';
	}
	echo '
			<td align="center">KATEGORY</td>
			<td align="center">TYPE</td>
			<td align="center">PLAT</td>
			<td align="center">NO RANGKA</td>								
			<td align="center">NO MESIN</td>			
			<td align="center">TAHUN RAKITAN</td>
			<td align="center">WARNA</td>
			<td align="center">STATUS STNK</td>
			<td align="center">MASA BERLAKU PAJAK</td>
	';
			
	if($jenis_report=="Tebus"){
	echo '
			<td align="center">STATUS</td>								
			<td align="center">TANGGAL</td>		
			<td align="center">By Tarik</td>			
			<td align="center">By Gudang</td>			
			<td align="center">By Lainnya</td>							
	';
	}
	echo '
		  </tr>
	';
	$lrs = pg_query($query);
	$no=1;
	
	$jns_kelamin['L']='Laki-laki';
	$jns_kelamin['P']='Perempuan';
	$jns_kelamin['B']='Badan Usaha';
	$jns_kelamin['M']='Masyarakat';
	$sts_nikah['M']='Menikah';
	$sts_nikah['B']='Belum Menikah';
	$sts_nikah['P']='Pisah/Cerai';
	
	
	while($lrow=pg_fetch_array($lrs)){		
		$tgl_lahir=split(" ",$lrow["tgl_lahir"]);
       	$tgl_lahir1=split("-",$tgl_lahir[0]);
		$tgl_lahir2=$tgl_lahir1[2]."/".$tgl_lahir1[1]."/".$tgl_lahir1[0];

		$fk_sbg=$lrow["no_sbg1"];

		$lrow["jenis_kelamin"]=$jns_kelamin[$lrow["jenis_kelamin"]];
		$lrow["status_pernikahan"]=$sts_nikah[$lrow["status_pernikahan"]];
		
		$no_sbg=$lrow["no_sbg"];
		$query_ovd="
		select * from(
			select date_part('days','".$tgl."'-tgl_jatuh_tempo)as ovd,* from data_fa.tblangsuran 
			where tgl_bayar is null and fk_sbg='".$no_sbg."' order by angsuran_ke desc 
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
			$lrow["saldo_pokok"]+=$lrow_ovd["pokok_jt"];
			$lrow["saldo_bunga"]+=$lrow_ovd["bunga_jt"];
			$lrow["saldo_pinjaman"]+=$lrow_ovd["nilai_angsuran"];
			$i++;
		}
			
		echo '
			<tr>
				<td valign="top">'.$no.'</td>
				<td valign="top">'.$lrow["nm_customer"].'</td>				
				<td valign="top">'.$lrow["no_sbg1"].'</td>
				
		';		
		if($jenis_report=="Tarik"){
			echo '
				<td valign="top">'.number_format($lrow["saldo_pinjaman"]).'</td>
				<td valign="top">'.number_format(($lrow["total_ar"]-$lrow["total_tunggakan"])).'</td>
			';
		}
		echo '
				<td valign="top">'.number_format($lrow["saldo_pokok"]).'</td>
		';
		if($jenis_report=="Tarik"){
			echo '
				<td valign="top">'.number_format($ovd['1']['od']).'</td>
				<td valign="top">'.number_format($ovd['1']['ar']).'</td>
				<td valign="top">'.number_format($ovd['2']['od']).'</td>
				<td valign="top">'.number_format($ovd['2']['ar']).'</td>
				<td valign="top">'.number_format($ovd['3']['od']).'</td>
				<td valign="top">'.number_format($ovd['3']['ar']).'</td>
			';
		}
		echo'
				<td valign="top" align="right">'.$lrow["ovd_tarik"].'</td>
				<td valign="top">'.number_format($lrow['total_tunggakan'],2).'</td>
				<td valign="top">'.number_format($lrow['nilai_angsuran'],2).'</td>
				<td valign="top" align="right">'.$lrow["ang_ke"].'</td>
				<td valign="top" align="right">'.$lrow["tenor"].'</td>
				<td valign="top">'.($lrow["tgl_jt_berjalan"]==""?"":date("d/m/Y",strtotime($lrow["tgl_jt_berjalan"]))).'</td>				
				<td valign="top">'.($lrow["tgl_tarik"]==""?"":date("d/m/Y",strtotime($lrow["tgl_tarik"]))).'</td>
				<td valign="top">'.$lrow["nm_partner"].'</td>
				<td valign="top">'.$lrow["nm_jenis_barang"].'</td>
				<td valign="top">'.$lrow["nm_tipe"].'</td>
				<td valign="top">'.$lrow["no_polisi"].'</td>
				<td valign="top">'.$lrow["no_rangka"].'</td>
				<td valign="top">'.$lrow["no_mesin"].'</td>
				<td valign="top">'.$lrow["tahun"].'</td>
				<td valign="top">'.$lrow["warna"].'</td>
				<td valign="top">'.$lrow["status_stnk"].'</td>
				<td valign="top">'.($lrow["masa_berlaku_pajak"]==""?"":date("d/m/Y",strtotime($lrow["masa_berlaku_pajak"]))).'</td>
				<td valign="top">'.$lrow["nm_kolektor"].'</td>
				<td valign="top">'.$lrow["nm_cmo"].'</td>';
		if($jenis_report=="Tebus"){		
		echo '
				<td valign="top">'.$lrow["transaksi"].'</td>
				<td valign="top">'.($lrow["tgl_transaksi"]==""?"":date("d/m/Y",strtotime($lrow["tgl_transaksi"]))).'</td>
				<td valign="top">'.number_format($lrow['biaya_tarik'],2).'</td>
				<td valign="top">'.number_format($lrow['biaya_gudang'],2).'</td>
				<td valign="top">'.number_format($lrow['biaya_lainnya'],2).'</td>
			';
		}
		echo '	
			</tr>
		';	
		$no++;
		$total_saldo+=$lrow["saldo_os"];
	
	}
	
				
	echo '</table>';
	
	
}

function get_user($username){
	$nm_user=get_rec("tbluser left join tblkaryawan on fk_karyawan=npk","nm_depan","username='".$username."'");
	
	return $nm_user;
}


