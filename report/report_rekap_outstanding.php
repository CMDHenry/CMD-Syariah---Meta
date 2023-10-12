<?php
include_once("report.php");

function filter_request(){
	global $showTgl,$tgl,$showCab;
	
	$nm_periode="";
	$showTgl='t';
	$showCab='t';
}


function excel_content(){
	global $periode_awal,$periode_akhir,$fk_customer,$tgl,$fk_cabang;
	
	$bulan=date('m',strtotime($tgl));
	$tahun=date('Y',strtotime($tgl));	
	
	$fom=$bulan.'/01/'.$tahun;
	$eom=$bulan.'/'.date('t',strtotime($fom)).'/'.$tahun;

	if($tgl != '' ){
		$lwhere.=" (tgl_lunas > '".$tgl."' or tgl_lunas is null) and tgl_cair <='".$tgl."'";
		$tgl_data=$tgl;
	}
	
	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" kd_cabang = '".$fk_cabang."' ";
	}
	
	if ($lwhere2!="") $lwhere2=" where ".$lwhere2;

	if ($lwhere!="") $lwhere=" where ".$lwhere;
	
	
	$query = "
	
	select nm_cabang,nm_produk,count(1)as noa,sum(case when fk_sbg_ang is not null then saldo_os end)as saldo_pokok,sum(case when fk_sbg_ang is  null then saldo_os end)as saldo_art,sum(pokok_awal)as pokok_awal,sum(saldo_os) from(
		select 		
		date_part('day',('".$tgl_data."'-tgl_jt))::numeric as ovd,extract(month from tgl_booking)||'/'||extract(year from tgl_booking)as periode, round(rate*(360/jumlah_hari),2) as rate_pa,tgl_booking+ interval '120 days' as tgl_buyback,* from( 
			select * from (
				select tgl_lunas,status_sbg, tgl_cair,tgl_cair as tgl_booking, status_sbg, fk_sbg as no_sbg1,fk_cabang,fk_cif,nm_produk,kd_produk,jumlah_hari,tgl_jt,jenis_produk from tblinventory
				left join (
					select kd_produk,nm_produk,jenis_produk,jumlah_hari from tblproduk
					left join tblrate on fk_rate=kd_rate
				) as tblproduk on kd_produk=fk_produk
				left join tblcabang on fk_cabang=kd_cabang
				".$lwhere." --and fk_sbg in('001153071801076','001152051800329')
			) as tblinventory
			left join (select kd_cabang, fk_wilayah, fk_area,nm_cabang from tblcabang) as tblcab on kd_cabang=fk_cabang
			where tgl_cair is not null 
		) as tblsbg
		left join (
			select distinct on (fk_sbg)fk_sbg as fk_sbg_ang from data_fa.tblangsuran
			where tgl_jatuh_tempo between '".$fom."' and '".$eom."' and (akrual1>0 or akrual2>0)
		)tblangsuran1 on no_sbg1=fk_sbg_ang
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
		".$lwhere1."
	)as tblmain 
	group by nm_cabang,nm_produk
	order by nm_cabang,nm_produk
		
	";
	
	//showquery($query);
	$lrs = pg_query($query);
	while($lrow=pg_fetch_array($lrs)){
		$kd_cabang=$lrow["kd_cabang"];
		$nm_cabang=$lrow["nm_cabang"];
		$kd_produk=$lrow["kd_produk"];
		$nm_produk=$lrow["nm_produk"];
		$arr_nm_produk[$kd_produk]=$nm_produk;
		
		$arr[$nm_cabang][$nm_produk]["noa"]=$lrow["noa"];
		$arr[$nm_cabang][$nm_produk]["pokok_awal"]=$lrow["pokok_awal"];	
		$arr[$nm_cabang][$nm_produk]["saldo_pokok"]=$lrow["saldo_pokok"];	
		$arr[$nm_cabang][$nm_produk]["saldo_art"]=$lrow["saldo_art"];	
		
		$subtotal[$nm_cabang]["noa"]+=$lrow["noa"];
		$subtotal[$nm_cabang]["pokok_awal"]+=$lrow["pokok_awal"];	
		$subtotal[$nm_cabang]["saldo_pokok"]+=$lrow["saldo_pokok"];	
		$subtotal[$nm_cabang]["saldo_art"]+=$lrow["saldo_art"];	
		
		$arr["NASIONAL"][$nm_produk]["noa"]+=$lrow["noa"];
		$arr["NASIONAL"][$nm_produk]["pokok_awal"]+=$lrow["pokok_awal"];	
		$arr["NASIONAL"][$nm_produk]["saldo_pokok"]+=$lrow["saldo_pokok"];	
		$arr["NASIONAL"][$nm_produk]["saldo_art"]+=$lrow["saldo_art"];	
		
		$subtotal["NASIONAL"]["noa"]+=$lrow["noa"];
		$subtotal["NASIONAL"]["pokok_awal"]+=$lrow["pokok_awal"];	
		$subtotal["NASIONAL"]["saldo_pokok"]+=$lrow["saldo_pokok"];	
		$subtotal["NASIONAL"]["saldo_art"]+=$lrow["saldo_art"];	
		
	}
	
	$v = $arr['NASIONAL'];
	unset($arr['NASIONAL']);
	if(!$fk_cabang){
		$arr['NASIONAL'] = $v;
	}	
	//print_r($arr);
	
	echo 	
	'<table border="1">
	    <tr>		 	
			<td align="center">Cabang</td>
			<td align="center">Produk</td>
			<td align="center">NOA</td>
			<td align="center">Pokok Awal</td>
			<td align="center">Saldo Pokok</td>
			<td align="center">Piutang Tunggu</td>
		</tr>	
	';
	
	
	
	$no=1;
	if(count($arr)>0){
		
		foreach($arr as $nm_cabang=>$arr1){
			$i=0;
			//echo count($arr1);
			foreach($arr1 as $nm_produk=>$arr2){
				echo '	
				 <tr>	
				 ';
				if($i==0){
					echo '<td align="center" rowspan="'.count($arr1).'">'.$nm_cabang.'</td>';				 
				}
				echo '	
					<td align="left">'.$nm_produk.'</td>	
					<td align="right">'.$arr2["noa"].'</td>						
					<td align="right">'.$arr2["pokok_awal"].'</td>						
					<td align="right">'.$arr2["saldo_pokok"].'</td>		
					<td align="right">'.$arr2["saldo_art"].'</td>															
				 </tr>		
				';
				$i++;
			}
			echo '	
			<tr>	
				<td align="center" colspan="2">Total '.$nm_cabang.'</td>	
				<td align="right">'.$subtotal[$nm_cabang]["noa"].'</td>	
				<td align="right">'.$subtotal[$nm_cabang]["pokok_awal"].'</td>	
				<td align="right">'.$subtotal[$nm_cabang]["saldo_pokok"].'</td>	
				<td align="right">'.$subtotal[$nm_cabang]["saldo_art"].'</td>	
				
			</tr>
			';

		}

	}
	echo '
		</table>';
}


