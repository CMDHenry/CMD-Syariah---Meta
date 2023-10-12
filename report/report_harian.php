<?php
include_once("report.php");

function filter_request(){
	global $showTgl,$showCab,$showWil;
	
	$showTgl='t';
	//$showCab='t';
	$showWil='t';
}



function excel_content(){
	global $periode_awal,$periode_akhir,$fk_customer,$bulan,$tahun,$tgl,$fk_cabang,$fk_wilayah;
	
	
	
	if($fk_wilayah != ''){
		$lwhere1.=" and fk_wilayah = '".$fk_wilayah."' ";
	}

	if ($lwhere!="") $lwhere=" where ".$lwhere;	
	//echo $query;
	$judul_atas[0]="New Outlet (dibawah 6 Bulan) ";
	$judul_atas[1]="Existing Outlet";
	$lwhere_cab[0]=" and umur_cabang<6";
	$lwhere_cab[1]=" and umur_cabang>=6";
	
	
	for($a=0;$a<2;$a++){
		echo $judul_atas[$a];
		echo 	
		'<table width="800" border="1">
	
		 <tr>
			<td rowspan="2">NO</td>
			<td rowspan="2">INITIAL</td>
			<td rowspan="2">Outlet</td>
			<td rowspan="2">Net Growth</td>
			<td colspan="2">Pencapaian Target</td>		
		 </tr>
		 <tr>
			<td >%Booking</td>
			<td >%OS</td>
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
		
		$arr["sales"]["tgl_awal"]=$fom;	
		$arr["sales"]["tgl_akhir"]=$tgl;
		$arr["sales"]["tgl"]=$tgl;
		$arr["sales"]["bulan"]=$bulan;
		$arr["sales"]["tahun"]=$tahun;
		
		$arr["os"]["tgl_awal"]=$fom;	
		$arr["os"]["tgl_akhir"]=$tgl;
		$arr["os"]["tgl"]=$tgl;
		$arr["os"]["bulan"]=$bulan;
		$arr["os"]["tahun"]=$tahun;
		
		$ltarget="";
		$ljoin="";
				
		foreach($arr as $temp=>$arr1){		
			if($temp=="sales"){
				$query_target="
					select count(fk_cabang) as jumlah_".$temp.",sum(nilai_".$temp.") as nilai_".$temp.", tblinventory.fk_cabang as fk_cabang_".$temp." from (
						select saldo_pokok as nilai_".$temp.",fk_sbg as no_sbg from 
						".query_booking($arr1["tgl_awal"],$arr1["tgl_akhir"])."
					)as tblar
					inner join tblinventory on fk_sbg = no_sbg
					group by fk_cabang				
				";
			}else if($temp=="os"){
				$query_target="
					select count(fk_cabang) as jumlah_".$temp.",sum(nilai_".$temp.") as nilai_".$temp.", tblinventory.fk_cabang as fk_cabang_".$temp." from (
						select sum(pokok_jt)as  nilai_".$temp.",fk_sbg as no_sbg from data_fa.tblangsuran
						".where_os_tblangsuran($arr1["tgl_akhir"])."
						group by fk_sbg		
					)as tblar
					inner join tblinventory on fk_sbg = no_sbg
					".where_os_tblinventory($arr1["tgl_akhir"])."
					group by fk_cabang
				";
			}
			$ltarget.="
			left join (
				select fk_cabang_".$temp.",jumlah_".$temp.",nilai_".$temp."/target_".$temp." * 100 as persen_".$temp."
				from ( 
					".$query_target."
				) as tblar
				left join (			
					select fk_cabang as fk_cabang_target_".$temp.",target_".$temp." from tbltarget 
					left join tbltarget_detail on kd_target=fk_target where tahun='".$arr1["tahun"]."' and bulan='".$arr1["bulan"]."' 
				) as tbltarget on fk_cabang_target_".$temp."= fk_cabang_".$temp."
			) as tbltarget_".$temp." on fk_cabang_".$temp."=kd_cabang		
			";
		}	
		
		$linventory ="inner join tblinventory on tblinventory.fk_sbg=tblangsuran.fk_sbg";
		
		$ljoin.="
			left join (
				select sum(saldo_pokok) as booking,fk_cabang as fk_cabang_book from
				".query_booking($fom,$tgl)."
				".$linventory."
				group by fk_cabang
			)as tblbook on kd_cabang=fk_cabang_book
			left join (
				select sum(pokok_jt) as pelunasan,fk_cabang as fk_cabang_lunas from 
				".query_pelunasan($fom,$tgl)."
				".$linventory."
				group by fk_cabang
			)as tbllunas on kd_cabang=fk_cabang_lunas
		";
	
		
		$query_cabang="
		select kd_cabang,initial_cabang,nm_cabang from (
			select *,EXTRACT(YEAR FROM age) * 12 + EXTRACT(MONTH FROM age) AS umur_cabang from(
				select * , age(TIMESTAMP  '".today_db."', tgl_input_cabang)as age from tblcabang
			)as tblcabang
		 ) as tblcabang
		where cabang_active='t' and kd_cabang!='999' ".$lwhere1."
		".$lwhere_cab[$a]."
		";
		//showquery($query_cabang);
		$max=pg_num_rows(pg_query($query_cabang));
		$query = "
		select * from (select case when booking is null then 0 else booking end- case when pelunasan is null then 0 else pelunasan end as net_growth ,* from (
				select * from (".$query_cabang.") as tblcab ".$lwhere."
		)as tblcabang
		".$ltarget."
		".$ljoin."
		) as tblmain
		order by net_growth desc
		";
		//showquery($query);
		$lrs = pg_query($query);
		$no=1;
		while($lrow=pg_fetch_array($lrs)){		
			if($lrow["net_growth"]<0){
				$color="Red";
			}else $color="White";
			
			echo '
				<tr bgcolor="'.$color.'">
					<td>'.$no.'</td>
					<td>'.$lrow["initial_cabang"].'</td>
					<td>'.$lrow["nm_cabang"].'</td>
			';
			echo '						
				<td style="text-align:right">'.round($lrow["net_growth"]).'</td>
			';	
			
			foreach($arr as $temp=>$arr1){
				//echo $temp."aaa";
				echo '						
					<td style="text-align:right">&nbsp;'.number_format($lrow["persen_".$temp]).'</td>
				';	
				
			}
	
			echo ' </tr>';
			$no++;
	
		}
			echo '		
		</table><br>';
	}
}


