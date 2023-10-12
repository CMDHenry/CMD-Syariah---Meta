<?php

include_once("report.php");

function filter_request(){
	global $showCab,$showTgl;
	$showCab='t';
	$showTgl='t';

}


function excel_content(){
	global $fk_cabang,$tgl;


	if($fk_cabang != ''){
		//if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cabang = '".$fk_cabang."' ";
	}
	
	if ($lwhere!="") $lwhere=" where ".$lwhere;
	
	
	$tgl_pilih=date("Y-m-d",strtotime($tgl));
	$bulan=date("m",strtotime($tgl_pilih));
	$tahun=date("Y",strtotime($tgl_pilih));
	
	$last_month=date("Y-m-d",strtotime(get_last_month($tgl,1)));
	//echo $last_month;
	$bulan_lalu=date('m',strtotime($last_month));
	$tahun_lalu=date('Y',strtotime($last_month));	

	$fom=$tahun.'-'.$bulan.'-01';
	$last_fom=$tahun_lalu.'-'.$bulan_lalu.'-01';
	$kemarin=date("Y-m-d",strtotime('-1 second',strtotime($tgl)));
	$kemarin_bln_lalu=date("Y-m-d",strtotime('-1 second',strtotime($last_month)));
	
	$arr["bln_ini"]["tgl_awal"]=$fom;	
	$arr["bln_ini"]["tgl_akhir"]=$tgl_pilih;
	$arr["bln_ini"]["kemarin"]=$kemarin;
	
	$arr["bln_ini"]["tgl"]=$tgl_pilih;
	$arr["bln_ini"]["bulan"]=$bulan;
	$arr["bln_ini"]["tahun"]=$tahun;
	
	$arr["bln_lalu"]["tgl_awal"]=$last_fom;
	$arr["bln_lalu"]["tgl_akhir"]=$last_month;
	$arr["bln_lalu"]["kemarin"]=$kemarin_bln_lalu;
	
	$arr["bln_lalu"]["tgl"]=$last_month;
	$arr["bln_lalu"]["bulan"]=$bulan_lalu;
	$arr["bln_lalu"]["tahun"]=$tahun_lalu;
	
	
	$linventory="
	inner join (
		select fk_sbg,jenis_produk,fk_cabang,tgl_lunas,tgl_cair from tblinventory 
		inner join tblproduk on kd_produk=fk_produk				
	) as tblinventory
	";
	
	$data=array("OS Kemarin","Booking Hari ini","Pelunasan Hari ini","OS Hari ini","Booking MTD","Pelunasan MTD");
	
	foreach($arr as $tipe =>$arr1){


		$query="
			select sum(pokok_jt) as booking,jenis_produk,fk_sbg from(
				select * from(
					select fk_sbg as no_sbg  ,sum(pokok_jt)as pokok_jt  from data_fa.tblangsuran
					".where_os_tblangsuran($arr1["kemarin"])."
					group by fk_sbg
				)as tblangsuran
				".$linventory."on fk_sbg=no_sbg
				".$lwhere."	
			)as tblmain
			".where_os_tblinventory($arr1["kemarin"])."		
			group by jenis_produk,fk_sbg
		";		
		//showquery($query);
		$lrs=pg_query($query);
		while($lrow=pg_fetch_array($lrs)){
			$jml[$tipe][$lrow["jenis_produk"]]["OS Kemarin"]+=1;
			$amt[$tipe][$lrow["jenis_produk"]]["OS Kemarin"]+=$lrow["booking"];
		}
		//echo query_booking($arr1["tgl"],$arr1["tgl"]);
		$query="
			select * from(
				select *,1 as booking,0 as pelunasan from(
					select fk_sbg as no_sbg,saldo_pokok as nominal_keluar,0 as nominal_masuk from 
					".query_booking($arr1["tgl"],$arr1["tgl"])."
				)as tblbooking	
				union 
				select *,0,1 from(
					select fk_sbg as no_sbg,0,sum(pokok_jt)as pokok_jt from
					".query_pelunasan($arr1["tgl"],$arr1["tgl"])."
					group by no_sbg
				)tblpelunasan
			) as tblhari_ini
			".$linventory." on fk_sbg = no_sbg 
			".$lwhere."
		";

		//showquery($query);
		$lrs=pg_query($query);
		while($lrow=pg_fetch_array($lrs)){
			
			if($lrow["jenis_produk"]=='1' && !strstr($lrow["tgl_lunas"],$arr1["tgl"])){
				$lrow["pelunasan"]=0;
			}
			$jml[$tipe][$lrow["jenis_produk"]]["Booking Hari ini"]+=$lrow["booking"];
			$jml[$tipe][$lrow["jenis_produk"]]["Pelunasan Hari ini"]+=$lrow["pelunasan"];
			
			$amt[$tipe][$lrow["jenis_produk"]]["Booking Hari ini"]+=$lrow["nominal_keluar"];
			$amt[$tipe][$lrow["jenis_produk"]]["Pelunasan Hari ini"]+=$lrow["nominal_masuk"];
		}
		
		$mtd=$arr1["tahun"]."-".$arr1["bulan"];
		$query="
			select * from(
				select *,1 as booking,0 as pelunasan from(
					select fk_sbg as no_sbg,saldo_pokok as nominal_keluar,0 as nominal_masuk from 
					".query_booking($arr1["tgl_awal"],$arr1["tgl_akhir"])."
				)as tblbooking	
				union 
				select *,0,1 from(
					select fk_sbg as no_sbg,0,sum(pokok_jt)as pokok_jt from
					".query_pelunasan($arr1["tgl_awal"],$arr1["tgl_akhir"])."
					group by no_sbg
				)tblpelunasan
			)as tblmtd
			".$linventory." on fk_sbg = no_sbg 
			".$lwhere."
		";		

		//showquery($query);
		$lrs=pg_query($query);
		while($lrow=pg_fetch_array($lrs)){
			if($lrow["jenis_produk"]=='1' && !strstr($lrow["tgl_lunas"],$mtd)){
				//echo $mtd.'=='.$lrow["tgl_lunas"];
				$lrow["pelunasan"]=0;
			}
			
			$jml[$tipe][$lrow["jenis_produk"]]["Booking MTD"]+=$lrow["booking"];
			$jml[$tipe][$lrow["jenis_produk"]]["Pelunasan MTD"]+=$lrow["pelunasan"];
			
			$amt[$tipe][$lrow["jenis_produk"]]["Booking MTD"]+=$lrow["nominal_keluar"];
			$amt[$tipe][$lrow["jenis_produk"]]["Pelunasan MTD"]+=$lrow["nominal_masuk"];
		}
		
		for($i=0;$i<2;$i++){
			$jml[$tipe][$i]["OS Hari ini"]+=($jml[$tipe][$i]["OS Kemarin"]+$jml[$tipe][$i]["Booking Hari ini"]-$jml[$tipe][$i]["Pelunasan Hari ini"]);
			$amt[$tipe][$i]["OS Hari ini"]+=($amt[$tipe][$i]["OS Kemarin"]+$amt[$tipe][$i]["Booking Hari ini"]-$amt[$tipe][$i]["Pelunasan Hari ini"]);
		}	
	}
	
	//echo $query;
	echo 	
	'<table border="0" style="border-top:solid;border-bottom:solid">
		<tr>
			<td>&nbsp;</td>
			<td rowspan="3" >&nbsp;</td>
			<td colspan="4" align="center" style="border-bottom:solid;">POSISI SAMPAI HARI INI</td>
			<td colspan="4" align="center" style="border-bottom:solid;">TGL SAMA BULAN SEBELUMNYA</td>
		</tr>
		<tr>
			<td rowspan="2" style="border-bottom:solid;">Keterangan</td>
			<td colspan="2" align="center" >KONSINYASI</td>
			<td colspan="2" align="center" >CICILAN</td>
			<td colspan="2" align="center" >KONSINYASI</td>
			<td colspan="2" align="center" >CICILAN</td>
		</tr>
		<tr>
		';
		for($i=0;$i<4;$i++){
		  echo '
				<td align="right" style="border-bottom:solid;">NOA</td>
				<td align="right" style="border-bottom:solid;">AMT</td>
		  ';
		}
		echo 
		'</tr>';
		
		foreach($data as $arr_data =>$judul){		   
			echo '	
				<tr>
					<td>'.$judul.'</td>
					<td>:</td>
			';
			foreach($arr as $tipe =>$arr1){
			   for($i=0;$i<2;$i++){
				   echo '
						<td align="right">'.round($jml[$tipe][$i][$judul]).'</td>
						<td align="right">'.round($amt[$tipe][$i][$judul]).'</td>
					';
			   }
			
			}
			echo '
				</tr>';
		}
	echo '
		</table>
	';
}


