<?php
include_once("report.php");

function filter_request(){
	global $showTgl,$showCab,$showWil;
	$showCab='t';
	$showTgl='t';
	$showWil='t';
	
}


function excel_content(){
	global $periode_awal,$periode_akhir,$fk_customer,$bulan,$tahun,$tgl,$fk_cabang,$fk_wilayah;

	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" kd_cabang = '".$fk_cabang."' ";
	}
	
	if($fk_wilayah != ''){
	
		$lwhere1.=" and fk_wilayah = '".$fk_wilayah."' ";
	}
	if ($lwhere!="") $lwhere=" where ".$lwhere;	
	//echo $query;
	echo 	
	'<table border="1">
	     <tr>
			<td align="center" rowspan="2">CAB</td>
			<td align="center" colspan="3">POSISI SAMPAI HARI INI</td>			
			<td align="center" colspan="3">TGL SAMA BULAN SEBELUMNYA</td>
		 </tr>	
		 <tr>		
			<td align="center">BOOKING</td>
			<td align="center">PELUNASAN</td>			
			<td align="center">NET OS</td>	
			<td align="center">BOOKING</td>
			<td align="center">PELUNASAN</td>			
			<td align="center">NET OS</td>														
		  </tr>
	';
	$bulan=date('n',strtotime($tgl));
	$tahun=date('Y',strtotime($tgl));	
	
	$last_month=get_last_month($tgl,1);
	//echo $last_month;
	
	$bulan_lalu=date('n',strtotime($last_month));
	$tahun_lalu=date('Y',strtotime($last_month));	
	
	$fom=$bulan.'/01/'.$tahun;
	$last_fom=$bulan_lalu.'/01/'.$tahun_lalu;
	
    $arr["bln_ini"]["tgl_awal"]=$fom;	
	$arr["bln_ini"]["tgl_akhir"]=$tgl;
	$arr["bln_ini"]["bulan"]=$bulan;
	$arr["bln_ini"]["tahun"]=$tahun;
	
	$arr["bln_lalu"]["tgl_awal"]=$last_fom;
	$arr["bln_lalu"]["tgl_akhir"]=$last_month;	
	$arr["bln_lalu"]["bulan"]=$bulan_lalu;
	$arr["bln_lalu"]["tahun"]=$tahun_lalu;
	
	$linventory ="inner join tblinventory on tblinventory.fk_sbg=tblangsuran.fk_sbg";
	/*
	
			left join(
				select sum(pokok_jt) as os_".$temp.",fk_cabang as fk_cabang_os_".$temp." from(
					select fk_sbg ,sum(pokok_jt)as pokok_jt  from data_fa.tblangsuran
					".where_os_tblangsuran($arr2["tgl_akhir"])."
					group by fk_sbg
				)as tblangsuran
				$linventory
				".where_os_tblinventory($arr2["tgl_akhir"])."
				group by fk_cabang
			)as tblos_".$temp." on kd_cabang=fk_cabang_os_".$temp."
	
	
	*/
	



	foreach($arr as $temp=>$arr2){		
		
		$ljoin.="
		left join (
			select sum(saldo_pokok) as booking_".$temp.",fk_cabang as fk_cabang_book_".$temp." from
			".query_booking($arr2["tgl_awal"],$arr2["tgl_akhir"])."
			$linventory
			group by fk_cabang
		)as tblbook_".$temp." on kd_cabang=fk_cabang_book_".$temp."
		left join (
				select sum(pokok_jt) as pelunasan_".$temp.",fk_cabang as fk_cabang_lunas_".$temp." from 
				".query_pelunasan($arr2["tgl_awal"],$arr2["tgl_akhir"])."
				$linventory
				group by fk_cabang
		)as tbllunas_".$temp." on kd_cabang=fk_cabang_lunas_".$temp."
		
		";
	}	
	$query_cabang="select kd_cabang,initial_cabang from tblcabang where cabang_active='t' and kd_cabang!='999' ".$lwhere1."";
	$max=pg_num_rows(pg_query($query_cabang));
	$query = "
		select * from (select case when booking_bln_ini is null then 0 else booking_bln_ini end as booking,case when pelunasan_bln_ini is null then 0 else pelunasan_bln_ini end as pelunasan,* from (
			select * from (".$query_cabang.") as tblcab ".$lwhere."
		)as tblcabang
	".$ljoin." ) as tblmain
	order by booking-pelunasan desc";
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
				<td align="center">&nbsp;'.$lrow["initial_cabang"].'</td>
		';
		foreach($arr as $temp=>$arr2){
			$lrow["os_".$temp]=$lrow["booking_".$temp]-$lrow["pelunasan_".$temp];
			echo '						
				<td style="text-align:right">'.round($lrow["booking_".$temp], 2).'</td>
				<td style="text-align:right">'.round($lrow["pelunasan_".$temp], 2).'</td>
				<td style="text-align:right">'.round($lrow["os_".$temp], 2).'</td>
			';	
			
			
			$total_booking[$temp] += $lrow["booking_".$temp];
			$total_pelunasan[$temp]+= $lrow["pelunasan_".$temp];
			$total_os[$temp] += $lrow["os_".$temp];
			
		}
		echo ' </tr>';
		$no++;

	}
	
	
	
	
	echo '
		<tr>
			<td align="center">Total</td>
	';
	foreach($arr as $temp=>$arr2){
		echo '		
			<td style="text-align:right">'.$total_booking[$temp].'</td>
			<td style="text-align:right">'.$total_pelunasan[$temp].'</td>
			<td style="text-align:right">'.$total_os[$temp].'</td>
		';
	}
	echo '	
		</tr>
	</table>';
}

