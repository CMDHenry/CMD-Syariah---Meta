<?php
include_once("report.php");

function filter_request(){
	global $showBln,$showCab;

	$showBln='t';
	//$showCab='t';
}



function excel_content(){
	global $bulan,$tahun;
	
	//echo $query;
	echo 	
	'<table width="800" border="1">
		  <tr>
				<td rowspan="2">NO</td>
				<td rowspan="2">KD</td>
				<td rowspan="2">NAMA CABANG</td>
				<td colspan="3">BOOKING</td>
				<td colspan="3">OS</td>
				<td colspan="2">PELUNASAN</td>
		  </tr>
		  <tr>
				<td>BUDGET</td>
				<td>NOA</td>
				<td>ACTUAL</td>
				<td>BUDGET</td>
				<td>NOA</td>
				<td>ACTUAL</td>
				<td>NOA</td>
				<td>ACTUAL</td>
		  </tr>
	';	
		
	
	$bln_ini=$tahun.'-'.$bulan;
	$fom=$tahun.'-'.$bulan.'-01';
	$eom=$tahun.'-'.$bulan.'-'.date('t',strtotime($fom));
	
	$linventory ="inner join tblinventory on tblinventory.fk_sbg=tblangsuran.fk_sbg";
	$ljoin.="
		left join(
			select count(fk_cabang)as noa_booking,sum(saldo_pokok)as booking,fk_cabang as fk_cabang1 from 
			".query_booking($fom,$eom)."
			$linventory
			group by fk_cabang
		)as tbl1 on kd_cabang=fk_cabang1
		left join(
			select count(fk_cabang)as noa_os,sum(pokok_jt) as os,fk_cabang as fk_cabang2 from(
				select fk_sbg ,sum(pokok_jt)as pokok_jt  from data_fa.tblangsuran
				".where_os_tblangsuran($eom)."
				group by fk_sbg
			)as tblangsuran
			$linventory
			".where_os_tblinventory($eom)."
			group by fk_cabang
		)as tbl2 on kd_cabang=fk_cabang2
		left join(
			select sum(case when jenis_produk=1 and (tgl_lunas not like '%".$bln_ini."%' or tgl_lunas is null) then 0 else 1 end)as noa_pelunasan,sum(pokok_jt)as pelunasan,fk_cabang as fk_cabang3 from(
				select fk_sbg ,sum(pokok_jt)as pokok_jt from 
				".query_pelunasan($fom,$eom)."
				group by fk_sbg
			)as tblangsuran
			$linventory	
			left join tblproduk on kd_produk=fk_produk
			group by fk_cabang
		)as tbl3 on kd_cabang=fk_cabang3
	";
	$ltarget ="			
	left join (			
		select target_os as budget_os,target_sales as budget_booking,fk_cabang as fk_cabang_target from tbltarget 
		left join tbltarget_detail on kd_target=fk_target where tahun='".$tahun."' and bulan='".$bulan."' 
	) as tbltarget on kd_cabang= fk_cabang_target
";

	
	//showquery($ljoin);
	
	$query = "
	select * from (
		select kd_cabang,nm_cabang from tblcabang
		where cabang_active='t' 
	)as tblcabang
	$ljoin
	$ltarget
	order by kd_cabang
	";
	//showquery($query);
	$lrs = pg_query($query);
	$no=1;

	
	while($lrow=pg_fetch_array($lrs)){		
		echo '
		<tr>
			<td>'.$no.'</td>	
			<td>'.$lrow['kd_cabang'].'</td>	
			<td>'.$lrow['nm_cabang'].'</td>
			<td align="right">'.$lrow['budget_booking'].'</td>
			<td align="right">'.$lrow['noa_booking'].'</td>
			<td align="right">'.round($lrow['booking']).'</td>
			<td align="right">'.$lrow['budget_os'].'</td>
			<td align="right">'.$lrow['noa_os'].'</td>
			<td align="right">'.round($lrow['os']).'</td>
			<td align="right">'.$lrow['noa_pelunasan'].'</td>
			<td align="right">'.round($lrow['pelunasan']).'</td>
		</tr>
		';

		$no++;
		
		$jml_budget_booking+=$lrow['budget_booking'];	
		$jml_noa+=$lrow['noa_booking'];
		$jml_booking+=$lrow['booking'];
		$jml_budget_os+=$lrow['budget_os'];
		$jml_noa_os+=$lrow['noa_os'];
		$jml_os+=$lrow['os'];
		$jml_noa_pelunasan+=$lrow['noa_pelunasan'];
		$jml_pelunasan+=$lrow['pelunasan'];

	}
	
		echo '
		<tr>
			<td align="center" colspan="3">Total</td>	
			<td align="right">'.$jml_budget_booking.'</td>	
			<td align="right">'.$jml_noa.'</td>
			<td align="right">'.$jml_booking.'</td>
			<td align="right">'.$jml_budget_os.'</td>
			<td align="right">'.$jml_noa_os.'</td>
			<td align="right">'.$jml_os.'</td>
			<td align="right">'.$jml_noa_pelunasan.'</td>
			<td align="right">'.$jml_pelunasan.'</td>
		</tr>
		';
		
		echo '	 		
	</table>';
}
