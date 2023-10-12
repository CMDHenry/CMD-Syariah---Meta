<?php
include_once("report.php");

function filter_request(){
	global $showTgl,$tgl,$showCab,$showBln,$jenis;
	$jenis=($_REQUEST["jenis"]);
	$nm_periode="";
	$showBln='t';
	//$showTgl='t';
	$showCab='t';
}


function create_filter(){
	global $jenis;
?>

       
   <tr bgcolor="efefef">
        <td style="padding:0 5 0 5" width="20%">Jenis</td>
        <td style="padding:0 5 0 5" width="30%">
        <select id='jenis' name="jenis">
                <option value="Pelunasan"<?= (($jenis=='Pelunasan')?"selected":"") ?>>Pelunasan</option>
                <option value="Booking"<?=(($jenis=='Booking')?"selected":"") ?>>Booking</option>
             </select>
        </td>
        <td style="padding:0 5 0 5" width="20%"></td>
        <td style="padding:0 5 0 5" width="30%"></td>
     </tr>
                
 
<?	
}

function excel_content(){
	global $periode_awal,$periode_akhir,$fk_customer,$bulan,$tahun,$fk_cabang,$jenis;
	
	//$bulan=date('m',strtotime($tgl));
	//$tahun=date('Y',strtotime($tgl));	
	
	$fom=$bulan.'/01/'.$tahun;
	$eom=$bulan.'/'.date('t',strtotime($fom)).'/'.$tahun;

	/*if($tgl != '' ){
		$lwhere.=" (tgl_lunas > '".$tgl."' or tgl_lunas is null) and tgl_cair <='".$tgl."'";
		$tgl_data=$tgl;
	}*/
	
	if($periode_awal != '' && $periode_akhir != ''){
		if($jenis=="Pelunasan"){
			$lwhere.=" tgl_lunas between '".$fom." 00:00:00' and '".$eom." 23:59:59'";
		} else {
			$lwhere.=" tgl_cair between '".$fom." 00:00:00' and '".$eom." 23:59:59'";
		}
	}
	
	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" kd_cabang = '".$fk_cabang."' ";
	}
	
	if ($lwhere2!="") $lwhere2=" where ".$lwhere2;

	if ($lwhere!="") $lwhere=" where ".$lwhere;
	
	if($jenis=="Pelunasan"){
	$query = "
		select nm_produk,nm_cabang,hari,sum(pokok_awal) as amount,count(1) as noa from (
	select extract (day from tgl_lunas) as hari,round(rate*(360/jumlah_hari),2) as rate_pa,* from( 
			select * from (
				select nm_produk,nm_cabang,date_part('day',(tgl_lunas-tgl_jt))::numeric as overdue,tgl_lunas,tgl_cair as tgl_booking, status_sbg, fk_sbg as no_sbg1,jumlah_hari,fk_cabang,fk_cif,fk_produk from tblinventory
				left join (
					select kd_produk,nm_produk,jenis_produk,jumlah_hari from tblproduk
					left join tblrate on fk_rate=kd_rate
				) as tblproduk on kd_produk=fk_produk
				left join tblcabang on fk_cabang=kd_cabang
				".$lwhere."
			) as tblinventory
			where tgl_lunas is not null
		) as tblsbg					
		left join viewsbg on no_sbg1=fk_sbg1
		left join viewkontrak on no_sbg1=no_sbg
		) as tblmain
		group by nm_produk,nm_cabang,hari,pokok_awal
		order by nm_cabang
	";
	} else {
	
	$query = "
	select nm_produk,nm_cabang,hari,sum(pokok_awal) as amount,count(1) as noa from (
		select extract (day from tgl_booking) as hari,status_aplikasi, round(rate*(360/jumlah_hari),2) as rate_pa,* from( 
			select * from (
				select  nm_produk,nm_cabang,date_part('day',('".today_db."'-tgl_jt))::numeric as ovd,tgl_lunas, tgl_cair,tgl_cair as tgl_booking, status_sbg, fk_sbg as no_sbg1,fk_cabang,fk_cif,kd_produk,fk_produk,jumlah_hari,jenis_produk from tblinventory
				left join (
					select kd_produk,nm_produk,jenis_produk,jumlah_hari from tblproduk
					left join tblrate on fk_rate=kd_rate
				) as tblproduk on kd_produk=fk_produk
				left join tblcabang on fk_cabang=kd_cabang
				".$lwhere."
			) as tblinventory
			where tgl_cair is not null 
		) as tblsbg
		left join viewsbg on no_sbg1=fk_sbg1
		left join viewkontrak on no_sbg=no_sbg1
		) as tblmain
		group by nm_produk,nm_cabang,hari,pokok_awal
		order by nm_cabang asc
	";
		
	}
	//echo $jenis."aaa";
	//showquery($query);
	
	$lrs = pg_query($query);
	while($lrow=pg_fetch_array($lrs)){
		$kd_cabang=$lrow["kd_cabang"];
		$nm_cabang=$lrow["nm_cabang"];
		$kd_produk=$lrow["kd_produk"];
		$nm_produk=$lrow["nm_produk"];
		$arr_nm_produk[$kd_produk]=$nm_produk;
		
		$arr[$nm_cabang][$nm_produk]["noa"][$lrow["hari"]]+=$lrow["noa"];
		$arr[$nm_cabang][$nm_produk]["amount"][$lrow["hari"]]+=$lrow["amount"];	
		
		$subtotal[$nm_cabang]["noa"][$lrow["hari"]]+=$lrow["noa"];
		$subtotal[$nm_cabang]["amount"][$lrow["hari"]]+=$lrow["amount"];
		
		$subtotal_kanan[$nm_cabang][$nm_produk]["noa"]+=$lrow["noa"];
		$subtotal_kanan[$nm_cabang][$nm_produk]["amount"]+=$lrow["amount"];	
		
		$subtotal_kanan_bawah[$nm_cabang]["noa"]+=$lrow["noa"];
		$subtotal_kanan_bawah[$nm_cabang]["amount"]+=$lrow["amount"];
		
		$arr["NASIONAL"][$nm_produk]["noa"][$lrow["hari"]]+=$lrow["noa"];
		$arr["NASIONAL"][$nm_produk]["amount"][$lrow["hari"]]+=$lrow["amount"];	
		
		$subtotal["NASIONAL"]["noa"][$lrow["hari"]]+=$lrow["noa"];
		$subtotal["NASIONAL"]["amount"][$lrow["hari"]]+=$lrow["amount"];
		
		$subtotal_kanan["NASIONAL"][$nm_produk]["noa"]+=$lrow["noa"];
		$subtotal_kanan["NASIONAL"][$nm_produk]["amount"]+=$lrow["amount"];	
		
		$subtotal_kanan_bawah["NASIONAL"]["noa"]+=$lrow["noa"];
		$subtotal_kanan_bawah["NASIONAL"]["amount"]+=$lrow["amount"];		
	}
	
	$v = $arr['NASIONAL'];
	unset($arr['NASIONAL']);
	if(!$fk_cabang){
		$arr['NASIONAL'] = $v;
	}	
	//print_r($arr);
	$tanggal = date('t',strtotime($fom));
	echo 	
	'<table border="1">
	    <tr>
			<td align="center">Nama Cabang</td>		 	
			<td align="center">Produk</td>
			<td align="center">Detail</td>
			
	';
		for ($i=1;$i<=$tanggal;$i++){
			echo '<td align="center">'.$i.'</td> ';
		}
	echo '
			<td align="center">Total</td>			
		</tr>	
	';
	

	
	$no=1;
	if(count($arr)>0){
		
		foreach($arr as $nm_cabang=>$arr1){
			$i=0;
			//echo count($arr1);
			foreach($arr1 as $nm_produk=>$arr2){
				$rowspan = count($arr1)*2;
				//echo $rowspan."tes";
				echo '	
				 <tr>	
				 ';
				if($i==0){
					echo '<td align="center" rowspan="'.$rowspan.'">'.$nm_cabang.'</td>';				 
				}
				echo '	
					<td align="left" rowspan="2">'.$nm_produk.'</td>	
					<td align="right">AMOUNT</td>
				';
				for ($i=1;$i<=$tanggal;$i++){
				echo '
					<td align="right">'.$arr2["amount"][$i].'</td>
					';
				}
				echo '
					<td align="right">'.$subtotal_kanan[$nm_cabang][$nm_produk]['amount'].'</td>
				</tr>
				<tr>				
					<td align="right">NOA</td>
				';
				for ($i=1;$i<=$tanggal;$i++){
				echo '
					<td align="right">'.$arr2["noa"][$i].'</td>		
																		
				 		
				';
				}
				echo '
					<td align="right">'.$subtotal_kanan[$nm_cabang][$nm_produk]['noa'].'</td>	
				</tr>
				';
				$i++;
			}
			echo '	
			<tr>	
				<td align="center" rowspan="2" colspan="2">Total '.$nm_cabang.'</td>
				<td align="right">AMOUNT</td>
			';
			for ($i=1;$i<=$tanggal;$i++){
			echo '
				<td align="right">'.$subtotal[$nm_cabang]['amount'][$i].'</td>
			';
			}
			echo '
				<td align="right">'.$subtotal_kanan_bawah[$nm_cabang]['amount'].'</td>
			</tr>
			<tr>
				<td align="right">NOA</td>
			';
			for ($i=1;$i<=$tanggal;$i++){
			echo '	
				<td align="right">'.$subtotal[$nm_cabang]['noa'][$i].'</td>
			';
			}
			echo '
				<td align="right">'.$subtotal_kanan_bawah[$nm_cabang]['noa'].'</td>
			</tr>
			';
			

		}
		
	}
	echo '
		</table>';
}


