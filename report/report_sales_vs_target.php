<?php
include_once("report.php");

function filter_request(){
	global $showTgl,$showCab,$showWil;
	
	$showTgl='t';
	$showCab='t';
	$showWil='t';
}



function excel_content(){
	global $periode_awal,$periode_akhir,$fk_customer,$bulan,$tahun,$tgl,$fk_cabang,$fk_wilayah;
	
	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" kd_cabang like '%".$fk_cabang."%' ";
	}
	
	if($fk_wilayah != ''){
	
		$lwhere1.=" and fk_wilayah = '".$fk_wilayah."' ";
	}

	if ($lwhere!="") $lwhere=" where ".$lwhere;	
	//echo $query;
	echo 	
	'<table width="800" border="1">
 	 <tr>
		<td rowspan="2">CAB</td>
		<td colspan="4">POSISI SAMPAI HARI INI</td>
		<td colspan="4">TGL SAMA BULAN SEBELUMNYA</td>
  	 </tr>
     <tr>
		<td>NOA</td>
		<td>SALES</td>
		<td>TARGET</td>
		<td>%</td>
		<td>NOA</td>
		<td>SALES</td>
		<td>TARGET</td>
		<td>%</td>
  	  </tr>
	';
	
	$bulan=date('m',strtotime($tgl));
	$tahun=date('Y',strtotime($tgl));	
	
	$last_month=get_last_month($tgl,1);
	//echo $last_month;
	
	$bulan_lalu=date('m',strtotime($last_month));
	$tahun_lalu=date('Y',strtotime($last_month));	
	
	$fom=$bulan.'/01/'.$tahun;
	$last_fom=$bulan_lalu.'/01/'.$tahun_lalu;
	
	$arr["bln_ini"]["tgl_awal"]=$fom;	
	$arr["bln_ini"]["tgl_akhir"]=$tgl;
	$arr["bln_ini"]["tgl"]=$tgl;
	$arr["bln_ini"]["bulan"]=$bulan;
	$arr["bln_ini"]["tahun"]=$tahun;
	
	$arr["bln_lalu"]["tgl_awal"]=$last_fom;
	$arr["bln_lalu"]["tgl_akhir"]=$last_month;
	$arr["bln_lalu"]["tgl"]=$last_month;
	$arr["bln_lalu"]["bulan"]=$bulan_lalu;
	$arr["bln_lalu"]["tahun"]=$tahun_lalu;
	
	foreach($arr as $temp=>$arr1){		
		$ltarget.="
		left join (
			select fk_cabang_".$temp.",jumlah_".$temp.",booking_".$temp.", target_".$temp." 
			from ( 
				select count(fk_cabang) as jumlah_".$temp.",sum(booking_".$temp.") as booking_".$temp.", tblinventory.fk_cabang as fk_cabang_".$temp." from (
					select saldo_pokok as booking_".$temp.",fk_sbg as no_sbg from 
					".query_booking($arr1["tgl_awal"],$arr1["tgl_akhir"])."
				)as tblar
				inner join tblinventory on fk_sbg = no_sbg
				group by fk_cabang
			) as tblar
			left join (			
				select fk_cabang as fk_cabang_target_".$temp.",target_sales as target_".$temp." from tbltarget 
				left join tbltarget_detail on kd_target=fk_target where tahun='".$arr1["tahun"]."' and bulan='".$arr1["bulan"]."' 
			) as tbltarget on fk_cabang_target_".$temp."= fk_cabang_".$temp."
		) as tbltarget_".$temp." on fk_cabang_".$temp."=kd_cabang		
		";
	}	
	
	$query_cabang="select kd_cabang,initial_cabang from tblcabang where cabang_active='t' and kd_cabang!='999' ".$lwhere1."";
	//showquery($query_cabang);
	$max=pg_num_rows(pg_query($query_cabang));
	$query = "
	select case when booking_bln_ini is null then 0 else booking_bln_ini end as sales,* from (
			select * from (".$query_cabang.") as tblcab ".$lwhere."
	)as tblcabang
	".$ltarget."
	order by sales desc";
	//showquery($query);
	$lrs = pg_query($query);
	$no=1;
	while($lrow=pg_fetch_array($lrs)){		
		if($fk_cabang == ''){
			$end=($max-$no+1);
			if($no>=1 && $no<=10){
				$color="Lime";
			}else if($end>=1 && $end<=10){
				$color="Red";
			}else $color="Yellow";
		
		}
		
		echo '
			<tr bgcolor="'.$color.'">
				<td>'.$lrow["initial_cabang"].'</td>
		';
		foreach($arr as $temp=>$arr1){
			if($lrow["target_".$temp]!="" || $lrow["target_".$temp]!=0){
				$persen[$temp]=$lrow["booking_".$temp]/$lrow["target_".$temp] * 100;
			}else{
				$persen[$temp]=0;
			}
			
			echo '						
				<td style="text-align:right">'.round($lrow["jumlah_".$temp]).'</td>
				<td style="text-align:right">'.round($lrow["booking_".$temp]).'</td>
				<td style="text-align:right">'.round($lrow["target_".$temp]).'</td>
				<td style="text-align:right">&nbsp;'.number_format($persen[$temp], 2).'</td>
			';	
			
			
			$total_noa[$temp] += $lrow["jumlah_".$temp];
			$total[$temp]+= $lrow["booking_".$temp];
			$total_target[$temp] += $lrow["target_".$temp];
			$total_persen[$temp] += $persen[$temp];			
			
		}
		echo ' </tr>';
		$no++;

	}
	
	

	echo '
		<tr>
			<td colspan="9">&nbsp;</td>
 		 </tr>
  		<tr>
			<td>TOT</td>
	';
	foreach($arr as $temp=>$arr1){
		echo '		
			<td style="text-align:right">'.$total_noa[$temp].'</td>
			<td style="text-align:right">'.$total[$temp].'</td>
			<td style="text-align:right">'.$total_target[$temp].'</td>
			<td style="text-align:right">&nbsp;'.number_format($total_persen[$temp]/$no,2).'</td>
		';
	}
		echo '	
 		 </tr>
	</table>';
}


