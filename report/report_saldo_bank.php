<?php
include_once("report.php");

function filter_request(){
	global $showTgl,$showCab,$showPeriode;		

	//$showTgl='t';
	$showPeriode='t';
	$showCab='t';
	
}


function excel_content(){
	global $periode_awal,$periode_akhir,$tgl,$fk_cabang;
	if($tgl!= ''){
		$lwhere.=" tgl between '".$periode_awal." 00:00:00' and '".$periode_akhir." 23:59:59'";
	}
	
	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cabang = '".$fk_cabang."' ";
	}
	

	if ($lwhere!="") $lwhere=" where ".$lwhere;
	
	//echo $query;
	//<td align="center">NAMA CABANG</td>			

	echo 	
	'<table border="1">
	     <tr>
			<td align="center">KODE CABANG</td>		 
			<td align="center">KODE BANK</td>
			<td align="center">NAMA BANK</td>
			<td align="center">SALDO AWAL</td>
			<td align="center">MASUK</td>
			<td align="center">KELUAR</td>			
			<td align="center">SALDO AKHIR</td>						
		  </tr>
	';
	//
	$no=1;
	$query = "
		select fk_bank,nm_bank,fk_cabang,nm_cabang,sum(case when tgl='".$periode_awal."' then awal end)as awal,sum(masuk)as masuk,sum(keluar)as keluar,sum(case when tgl='".$periode_akhir."' then akhir end)as akhir from data_fa.tblsaldo_bank
		left join tblbank on kd_bank=fk_bank
		left join tblcabang on kd_cabang=fk_cabang
		".$lwhere." 
		group by fk_cabang,fk_bank,nm_bank,nm_cabang
		order by fk_cabang,fk_bank,nm_bank,nm_cabang";
	//showquery($query);
	$lrs = pg_query($query);
	while($lrow=pg_fetch_array($lrs)){		
		if($temp!=$lrow["fk_cabang"]){
			$temp=$lrow["fk_cabang"];
			/*echo '
				<tr>
					<td colspan="8" align="left">&nbsp;'.$lrow["fk_cabang"].'-'.$lrow["nm_cabang"].'</td>	
				</tr>				
			';*/			
		}
		//	<td align="center">&nbsp;'.$lrow["nm_cabang"].'</td>
		
		echo '
			<tr>
				<td align="center">&nbsp;'.$lrow["fk_cabang"].'</td>			
				<td align="center">&nbsp;'.$lrow["fk_bank"].'</td>
				<td align="center">&nbsp;'.$lrow["nm_bank"].'</td>
			 	<td valign="top" align="right">'.number_format($lrow["awal"]).'</td>
				<td valign="top" align="right">'.number_format($lrow["masuk"]).'</td>
				<td valign="top" align="right">'.number_format($lrow["keluar"]).'</td>
				<td valign="top" align="right">'.number_format($lrow["akhir"]).'</td>
			</tr>
		';	

	}
	
	$no++;

	echo '</table>';
}

