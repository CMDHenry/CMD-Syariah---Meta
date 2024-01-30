<?php

include_once("report.php");

function filter_request(){
	global $is_eoy,$showCab,$showTgl;
	$showCab='t';
	$showTgl='t';
	
	$is_eoy=trim($_REQUEST["is_eoy"]);
	if($is_eoy==""){
		$is_eoy="f";
	}
	
}

function create_filter(){
	global $is_eoy;
?>

   <tr bgcolor="efefef">
        <td style="padding:0 5 0 5" width="20%">End Of Year</td>
        <td style="padding:0 5 0 5" width="30%">
        <input type="checkbox" name="is_eoy" value="t" <?=(($is_eoy=="t")?"checked":"")?> >
        </td>
        <td style="padding:0 5 0 5" width="20%"></td>
        <td style="padding:0 5 0 5" width="30%"></td>
        
     </tr>

<?	
}

function excel_content(){
	global $tgl,$fk_cabang,$nm_cabang,$is_eoy;
	
	if($is_eoy=='t'){//buat tarik 1 tahun
		$year=date('Y',strtotime($tgl));
		$tgl='12/31/'.$year;
	}
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
	
	$current_month=date('Y-m',strtotime($tgl));
	$fom=date('Y-m',strtotime($tgl)).'-01 00:00:00';
	$eom=date('Y-m-t',strtotime($tgl))." 23:59:59";
	
	$next_fom=date('Ymd',strtotime('+1 day',strtotime($eom)));
	$next_eom=date('Y-m-t',strtotime($next_fom))." 23:59:59";
	//echo $next_fom;
	$last_eom=date('Ymd',strtotime('-1 day',strtotime($fom)));
	$eom_before=date('Y-m-t',strtotime('-1 day',strtotime($fom)));

	$last_fom=date('Y-m',strtotime($last_eom)).'-01 00:00:00';
	//echo $last_eom;
	$last_month=date('Y-m',strtotime($last_eom));
	$month=date('M',strtotime($tgl));
	
	
	if($is_eoy=='t'){
		$current_month=$year;
		$month=	$year;
		$fom=$year.'-01-01 00:00:00';
		$eom=$tgl." 23:59:59";
		$eom_before = date('Y-m-t',strtotime('-1 day',strtotime($fom)));
	}
	
	//echo $last_month;
	$query = "
		select * from( 
			select * from (
				select  date_part('day',('".$tgl."'-tgl_jt))::numeric as ovd, tgl_lunas, tgl_cair, status_sbg, fk_sbg as no_sbg1,fk_cabang,fk_cif,fk_produk,kd_produk,jumlah_hari,jenis_produk from tblinventory
				left join (
					select kd_produk,nm_produk,jenis_produk,jumlah_hari from tblproduk
					left join tblrate on fk_rate=kd_rate
				) as tblproduk on kd_produk=fk_produk
				".$lwhere." --and fk_sbg in('20101210100002')
			) as tblinventory
		) as tblsbg
		
		left join viewkontrak on no_sbg=no_sbg1
		left join (
			select nm_customer,no_cif from tblcustomer
		)as tblcustomer on no_cif=fk_cif 
		
		left join (
			select sum(akrual1+akrual2) as saldo_awal_akrual,
			fk_sbg as fk_sbg2 from data_fa.tblangsuran 
			where tgl_jatuh_tempo <='".$eom_before."' 	
			group by fk_sbg
		) as tblangsuran2 on fk_sbg2=no_sbg			
		left join (
			select sum(akrual1+akrual2) as akrual_bln_ini,
			fk_sbg as fk_sbg3 from data_fa.tblangsuran 
			left join (select addm_addb, tgl_wo, no_sbg from data_gadai.tblproduk_cicilan)as tblsbg on fk_sbg=no_sbg
			where tgl_jatuh_tempo like '".$current_month."%' and tgl_wo is null
			group by fk_sbg
		) as tblangsuran3 on fk_sbg3=no_sbg		
		left join (
			select sum(akrual1+akrual2) as saldo_awal_bunga,
			fk_sbg as fk_sbg4 from data_fa.tblangsuran 
			where tgl_jatuh_tempo >='".$eom_before."' 	
			group by fk_sbg
		) as tblangsuran1 on fk_sbg4=no_sbg			
		left join (
			select sum(akrual1+akrual2) as saldo_bunga,
			fk_sbg as fk_sbg5 from data_fa.tblangsuran 
			left join (select addm_addb, tgl_wo, no_sbg from data_gadai.tblproduk_cicilan)as tblsbg on fk_sbg=no_sbg
			where tgl_jatuh_tempo >='".$eom."' and tgl_wo is null
			group by fk_sbg
		) as tblangsuran5 on fk_sbg5=no_sbg		
			
		left join (
			select referensi as no_batch,fk_sbg as fk_sbg_ar,tgl_bayar as tgl_batch from data_gadai.tblhistory_sbg
			where transaksi='AR' and tgl_batal is null
		)as tbl on no_sbg=fk_sbg_ar
		--where no_sbg not in(
		--	select fk_owner from data_accounting.tblgl_auto 
		--	where type_owner='AKRUAL AKHIR BULAN' and fk_coa_d is not null 			
		--	and tr_date like '".$current_month."%'  
		--)
		order by no_batch 
	";

	//showquery($query);

	echo 	
	'<table border="1">
	     <tr>
			<td align="center" rowspan="2">Kode Cabang</td>
			<td align="center" rowspan="2">No Kontrak</td>
			<td align="center" rowspan="2">Nama Customer</td>
			<td align="center" rowspan="2">No Batch</td>
			<td align="center" rowspan="2">Due Date</td>
			<td align="center" colspan="2">Beginning '.$month.'</td>		
			<td align="center" rowspan="2">Acru '.$month.'</td>							
			<td align="center" colspan="2">Ending '.$month.'</td>
		 </tr>
	     <tr>
			<td align="center">ULI</td>
			<td align="center">LI</td>
			<td align="center">ULI</td>
			<td align="center">LI</td>
			
		 </tr>		 
		';
		echo '
		 
	';
	$lrs = pg_query($query);
	$no=1;
	while($lrow=pg_fetch_array($lrs)){
		//$lrow["saldo_awal_bunga"]=$lrow["saldo_bunga"]-$lrow["akrual_bln_ini"];
		$lrow["saldo_akrual"]=$lrow["saldo_awal_akrual"]+$lrow["akrual_bln_ini"];
		
		echo '
			<tr>
				<td valign="top">'.$lrow["fk_cabang"].'</td>
				<td valign="top">&nbsp;'.$lrow["no_sbg1"].'</td>
				<td valign="top">'.$lrow["nm_customer"].'</td>
				<td valign="top">'.$lrow["no_batch"].'</td>
				<td valign="top">'.($lrow["tgl_cair"]==""?"":date("d",strtotime($lrow["tgl_cair"]))).'</td>
				<td valign="top" align="right">'.number_format($lrow["saldo_awal_bunga"]).'</td>
				<td valign="top" align="right">'.number_format($lrow["saldo_awal_akrual"]).'</td>
				<td valign="top" align="right">'.number_format($lrow["akrual_bln_ini"]).'</td>	
				<td valign="top" align="right">'.number_format($lrow["saldo_bunga"]).'</td>							
				<td valign="top" align="right">'.number_format($lrow["saldo_akrual"]).'</td>
		';
				
		$no++;
		$total["saldo_awal_bunga"]+=$lrow["saldo_awal_bunga"];
		$total["saldo_awal_akrual"]+=$lrow["saldo_awal_akrual"];
		
		$total["akrual_bln_ini"]+=$lrow["akrual_bln_ini"];
		$total["saldo_bunga"]+=$lrow["saldo_bunga"];
		$total["saldo_akrual"]+=$lrow["saldo_akrual"];
	}
	//$selish=$total["saldo_akrual"]-$total['saldo_bunga'];
		echo '
			<tr>
				<td valign="top">Total</td>
				<td valign="top"></td>
				<td valign="top"></td>
				<td valign="top"></td>
				<td valign="top">'.number_format($selish).'</td>
				<td valign="top" align="right">'.number_format($total["saldo_awal_bunga"]).'</td>							
				<td valign="top" align="right">'.number_format($total['saldo_awal_akrual']).'</td>
				<td valign="top" align="right">'.number_format($total["akrual_bln_ini"]).'</td>	
				<td valign="top" align="right">'.number_format($total["saldo_bunga"]).'</td>							
				<td valign="top" align="right">'.number_format($total['saldo_akrual']).'</td>
		';
	
	echo '</table>';
}

/* left join viewsbg on no_sbg1=fk_sbg1		
		left join (
			select sum(saldo_os) as saldo_os,  fk_sbg_os from(
				select bunga_jt as saldo_bunga, pokok_jt as saldo_os,fk_sbg as fk_sbg_os from data_fa.tblangsuran
				".where_os_tblangsuran($tgl)."						
			)as tblar
			inner join tblinventory on fk_sbg = fk_sbg_os
			".where_os_tblinventory($tgl)."
			group by fk_sbg_os
		)as tblos on no_sbg1=fk_sbg_os


*/

