<?php
include_once("report.php");

function filter_request(){
	global $showBln,$showCab,$showWil;

	$showBln='t';
	$showCab='t';
	$showWil='t';
}



function excel_content(){
	global $bulan,$tahun,$fk_cabang,$fk_wilayah;
	
	
	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cabang = '".$fk_cabang."' ";
	}
	
	if($fk_wilayah != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_wilayah = '".$fk_wilayah."' ";
	}
	
	if ($lwhere!="") $lwhere=" where ".$lwhere;
	//echo $query;
	echo 	
	'<table width="800" border="1">
		  <tr>
		  		<td></td>
				<td colspan="2">CURRENT</td>
				<td colspan="2">>0 Hari</td>
				<td colspan="2">ART</td>
				<td colspan="2">TOTAL</td>
		  </tr>
	';	
		
	
	$bln_ini=$tahun.'-'.$bulan;
	$fom=$tahun.'-'.$bulan.'-01';
	$eom=$tahun.'-'.$bulan.'-'.date('t',strtotime($fom));
	$jumlah_hari=date('t',strtotime($fom));
	$linventory ="inner join tblinventory on tblinventory.fk_sbg=tblangsuran.fk_sbg";
	for ($i=1;$i<$jumlah_hari;$i++){
		
		$tgl=$tahun.'-'.$bulan.'-'.$i;
		//echo $tgl.'><';
		//echo today_db.'<br>';
		if(strtotime(today_db)>=strtotime($tgl)){
			$query="
				select 
					count(case when ovd<=0 then 1 end )as noa_current,
					sum(case when ovd<=0 then pokok_jt end) as os_current ,
					count(case when (ovd between 1 and 30 and jenis_produk=0) or (ovd >0 and jenis_produk=1)  then 1 end)as noa_ovd,
					sum(case when (ovd between 1 and 30 and jenis_produk=0) or (ovd >0 and jenis_produk=1) then pokok_jt end) as os_ovd ,
					count(case when ovd > 30 and jenis_produk=0 then 1 end)as noa_art,
					sum(case when ovd > 30 and jenis_produk=0 then pokok_jt end) as os_art ,
					count(1)as noa_total,
					sum(pokok_jt) as os_total 
					from( select * from (
						select date_part('day',('".$tgl."'-tgl_jt))::numeric as ovd,* from(				
							select fk_sbg ,sum(pokok_jt)as pokok_jt  from data_fa.tblangsuran
							".where_os_tblangsuran($tgl)."
							group by fk_sbg
						)as tblangsuran
						$linventory
						left join tblproduk on kd_produk=fk_produk
						left join (select kd_cabang,fk_wilayah from tblcabang) as tblcabang on kd_cabang=fk_cabang
						".where_os_tblinventory($tgl)." ) as tbl1 ".$lwhere."
				)as tblmain
			";
			$lrs = pg_query($query);
			$lrow = pg_fetch_array($lrs);
			
			//showquery($query);
			echo '
			<tr>
				<td align="right">'.$i.'</td>
				<td align="right">'.$lrow['noa_current'].'</td>
				<td align="right">'.$lrow['os_current'].'</td>
				<td align="right">'.$lrow['noa_ovd'].'</td>
				<td align="right">'.$lrow['os_ovd'].'</td>
				<td align="right">'.$lrow['noa_art'].'</td>
				<td align="right">'.$lrow['os_art'].'</td>
				<td align="right">'.$lrow['noa_total'].'</td>
				<td align="right">'.$lrow['os_total'].'</td>
			</tr>
			';
	
			$no++;
		}
	}
	
	/*	echo '
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
		';*/
		
		echo '	 		
	</table>';
}
