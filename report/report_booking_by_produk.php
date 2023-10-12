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

	if($tgl!= ''){
		$lwhere.=" tgl_cair between '".$fom." 00:00:00' and '".$tgl." 23:59:59'";
	}
	
	if($fk_cabang != ''){
		if ($lwhere2!="") $lwhere2.=" and ";
		$lwhere2.=" kd_cabang = '".$fk_cabang."' ";
	}
	
	if ($lwhere2!="") $lwhere2=" where ".$lwhere2;

	if ($lwhere!="") $lwhere=" where ".$lwhere;
	
	//echo $query;
	$bulan=date('n',strtotime($tgl));
	$tahun=date('Y',strtotime($tgl));	
	
	$query = "
		select * from(
			select kd_cabang,initial_cabang from tblcabang ".$lwhere2."
		)as tblcabang
		inner join(
			select * from(
				select pokok_awal as amount,fk_sbg1 as no_sbg from viewsbg
				".$lwhere."
			)as tblar
			inner join tblinventory on fk_sbg=no_sbg			
		)as tblinventory  on fk_cabang=kd_cabang	
		left join (
			select kd_produk,nm_produk from tblproduk 
		)as tblproduk on kd_produk=fk_produk
		order by kd_cabang";
	//showquery($query);
	$lrs = pg_query($query);
	while($lrow=pg_fetch_array($lrs)){
		$kd_cabang=$lrow["kd_cabang"];
		$initial_cabang=$lrow["initial_cabang"];
		$kd_produk=$lrow["kd_produk"];
		$nm_produk=$lrow["nm_produk"];
		$arr_nm_produk[$kd_produk]=$nm_produk;
		$arr_initial_cabang[$kd_cabang]=$initial_cabang;
		$arr[$kd_cabang][$kd_produk]["noa"]+=1;
		$arr[$kd_cabang][$kd_produk]["amount"]+=$lrow["amount"];	
		$total[$kd_produk]["noa"]+=1;
		$total[$kd_produk]["amount"]+=$lrow["amount"];	
	}
	//print_r($arr);
	$no=1;
	if(count($arr)>0){
		echo 	
		'<table border="1">
			 <tr>
				<td align="center" rowspan="2">CAB</td>
		';
		foreach($arr_nm_produk as $kd_produk=>$nm_produk){
			echo '		
				<td align="center" colspan="2">'.$nm_produk.'</td>			
			';
		}
		echo '</tr> 
			  <tr>';
		foreach($arr_nm_produk as $kd_produk=>$nm_produk){
			echo '
				<td align="center">NOA</td>
				<td align="center">AMT</td>													
			';
		}
		echo '</tr>';
		foreach($arr_initial_cabang as $kd_cabang=>$initial_cabang){
			
			echo '
				<tr>
					<td align="center">&nbsp;'.$initial_cabang.'</td>
			';		
			foreach($arr_nm_produk as $kd_produk=>$nm_produk){
				echo '
					<td align="right">'.$arr[$kd_cabang][$kd_produk]["noa"].'</td>
					<td align="right">'.$arr[$kd_cabang][$kd_produk]["amount"].'</td>
				';
			}
			echo '
				</tr>			
			';	
		}
		echo '
			<tr>
				<td colspan="9">&nbsp;</td>
			</tr>
			<tr>
					<td align="center">Total</td>';	
		foreach($arr_nm_produk as $kd_produk=>$nm_produk){
			echo '
					<td align="right">'.$total[$kd_produk]["noa"].'</td>
					<td align="right">'.$total[$kd_produk]["amount"].'</td>						
			';
		}
		
		$no++;
	
		echo '
			</tr>	
		</table>';
	}
}


