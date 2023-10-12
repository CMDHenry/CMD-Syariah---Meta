<?php

include_once("report.php");

function filter_request(){
	global $showCab,$showTgl,$showWil;
	$showCab='t';
	//$showTgl='t';
	$showWil='t';
}


function excel_content(){
	global $fk_wilayah, $fk_cabang,$tgl;

	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cabang = '".$fk_cabang."' ";
	}
	
	if($fk_wilayah != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_wilayah = '".$fk_wilayah."' ";
	}
	if ($lwhere!="") $lwhere=" where ".$lwhere;	
	//if ($lwhere1!="") $lwhere1=" where ".$lwhere1;
	
	
	$query = "
	select count(1)as total,fk_cabang,nm_cabang from (
		select * from data_gadai.tblblanko where kondisi_blanko='Baik' and fk_sbg is null
	) as tblblanko
	left join tblcabang on fk_cabang=kd_cabang
	".$lwhere." ".$lwhere1."
	group by fk_cabang,nm_cabang
	order by fk_cabang,nm_cabang
	";
//showquery($query);
		
	echo 	
	'<table border="1">
	  
		   <tr>
		   	<td align="center">No</td>
		    <td align="center">Kode Cabang</td>
		    <td align="center">Nama Cabang</td>			
			<td align="center">Total Blanko</td>
		  </tr>
		  
	';
	$lrs = pg_query($query);
	$no=1;

	while($lrow=pg_fetch_array($lrs)){
		
		echo '
		
			<tr>
				<td valign="top">&nbsp;'.$no.'</td>
				<td valign="top">&nbsp;'.$lrow["fk_cabang"].'</td>
				<td valign="top">&nbsp;'.$lrow["nm_cabang"].'</td>				
				<td valign="top">'.$lrow["total"].'</td>
			</tr>
		';	
		$no++;
	
	}
	echo '</table>';
}


