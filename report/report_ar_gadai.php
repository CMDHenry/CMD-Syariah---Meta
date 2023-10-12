<?php
include_once("report.php");

function filter_request(){
	global $showTgl,$tgl,$showCab,$jenis_produk;
	$jenis_produk=($_REQUEST["jenis_produk"]);
	$nm_periode="";
	$showTgl='t';
	$showCab='t';
}


function create_filter(){
	global $jenis_produk;
?>

       
   <tr bgcolor="efefef">
        <td style="padding:0 5 0 5" width="20%">Jenis Produk</td>
        <td style="padding:0 5 0 5" width="30%">
        <select id='jenis_produk' name="jenis_produk">
<!--            <option value=""   <?=(($jenis_produk == '')?'selected':'') ?>>--Pilih--</option>
-->             <option value="0"<?= (($jenis_produk=='0')?"selected":"") ?>>Gadai</option>
                <option value="1"<?=(($jenis_produk=='1')?"selected":"") ?>>Cicilan</option>
             </select>
        </td>
        <td style="padding:0 5 0 5" width="20%"></td>
        <td style="padding:0 5 0 5" width="30%"></td>
     </tr>
                
 
<?	
}


function excel_content(){
	global $periode_awal,$periode_akhir,$fk_customer,$tgl,$fk_cabang,$jenis_produk;

	//if ($lwhere!="") $lwhere=" where ".$lwhere;
	
	$bulan=date('m',strtotime($tgl));
	$tahun=date('Y',strtotime($tgl));	
	
	$fom=$bulan.'/01/'.$tahun;

/*	if($tgl!= ''){
		$lwhere.=" tgl_cair < '".$tgl." 23:59:59'";
	}
*/	
	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cabang = '".$fk_cabang."' ";
	}
	
	if($jenis_produk != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" jenis_produk = '".$jenis_produk."' ";
	}

	if ($lwhere!="") $lwhere=" and ".$lwhere;
	
	//echo $query;
	$bulan=date('n',strtotime($tgl));
	$tahun=date('Y',strtotime($tgl));		
	echo 	
	'<table width="1000" border="1">
		  <tr>
		  		<td rowspan="2">KODE CAB</td>
				<td rowspan="2">CAB</td>
				<td colspan="3">CURRENT</td>
				<td colspan="2">OVD 1-15 Hr</td>
				<td colspan="2">OVD 16-30 Hr</td>
				<td colspan="2">OVD 31-60 Hr</td>
				<td colspan="2">OVD &gt; 60 Hr</td>
				<td colspan="2">TOTAL AR</td>
		  </tr>
		  <tr>
				<td>NOA</td>
				<td>AMT</td>
				<td>%</td>
				<td>NOA</td>
				<td>AMT</td>
				<td>NOA</td>
				<td>AMT</td>
				<td>NOA</td>
				<td>AMT</td>
				<td>NOA</td>
				<td>AMT</td>
				<td>NOA</td>
				<td>AMT</td>
		  </tr>
	';
	//echo get_last_month(convert_date_english(today),2);
	$bulan=date('n',strtotime($tgl));
	$tahun=date('Y',strtotime($tgl));	
	
	if($jenis_produk=='0'){
		$lquery="left join (
					select max(tgl_jatuh_tempo)as tgl_jatuh_tempo,fk_sbg as fk_sbg2 from data_fa.tblangsuran
					group by fk_sbg
				)as tblangsuran on fk_sbg2=fk_sbg							
				left join (
					select sum(saldo_os) as saldo_os, sum(saldo_bunga) as saldo_bunga, fk_sbg_os from(
						select bunga_jt as saldo_bunga, pokok_jt as saldo_os,fk_sbg as fk_sbg_os from data_fa.tblangsuran
						".where_os_tblangsuran($tgl)."						
					)as tblar
					inner join tblinventory on fk_sbg = fk_sbg_os
					".where_os_tblinventory($tgl)."
					group by fk_sbg_os
				)as tblos on fk_sbg=fk_sbg_os"	
			;
	}elseif($jenis_produk=='1'){
		$lquery="						
				left join (
					select min(angsuran_ke)as ang_ke,fk_sbg as fk_sbg_os from data_fa.tblangsuran
					".where_os_tblangsuran($tgl)."		
					group by fk_sbg				
				)as tblos on fk_sbg=fk_sbg_os
				left join (
					select (tgl_jatuh_tempo)as tgl_jatuh_tempo,fk_sbg as fk_sbg2,nilai_angsuran as saldo_os,angsuran_ke from data_fa.tblangsuran										
				)as tblangsuran on fk_sbg2=fk_sbg and angsuran_ke=ang_ke
			"	
			;
	}
	
	$query="
	select kd_cabang,
		initial_cabang,
		sum(case when due <=0 then saldo end )as \"current\",
		count(case when due <=0 then 1 end )as \"jml_current\",
		sum(case when due >0 and due <=15 then saldo end )as \"0-15\",
		count(case when due >0 and due <=15 then 1 end )as \"jml_0-15\",
		sum(case when due >=16 and due <=30 then saldo end )as \"16-30\",
		count(case when due >=16 and due <=30 then 1 end )as \"jml_16-30\",
		sum(case when due >=31 and due <=60 then saldo end )as \"31-60\",
		count(case when due >=31 and due <=60 then 1 end )as \"jml_31-60\",
		sum(case when due >60 then saldo end )as \">60\",
		count(case when due >60 then 1 end )as \"jml_>60\",
		sum(saldo)as total_due,
		count(1)as jml_total_due
	from(
			select kd_cabang,initial_cabang,saldo_os as saldo,jenis_produk,date_part('day',('".$tgl."'-tgl_jatuh_tempo))::numeric as due from(
				select * from (
					select fk_sbg,jenis_produk,fk_cabang,tgl_jt from tblinventory
					left join tblproduk on fk_produk = kd_produk 
					where tgl_cair<'".$tgl." 23:59:59' and (tgl_lunas >'".$tgl." 23:59:59' or tgl_lunas is null)
					".$lwhere."
				)as tblinventory
				".$lquery."
				left join (
					select kd_cabang,nm_cabang,initial_cabang from tblcabang 
				)as tblcabang on kd_cabang=fk_cabang 						
			)as tblar 
	)as tblmain 
	group by kd_cabang,initial_cabang
	order by kd_cabang";
	//showquery($query);
	$lrs = pg_query($query);
	$no=1;
	while($lrow=pg_fetch_array($lrs)){
		
		$persen=round($lrow["current"]/$lrow["total_due"], 2) * 100;
	
		$jml_current += $lrow["jml_current"];
		$current += $lrow["current"];
		$jml_total_due += $lrow["jml_total_due"];
		$total_due += $lrow["total_due"];
		
		$OVD1 += $lrow["0-15"];
		$JML_OVD1 += $lrow["jml_0-15"];
		$OVD2 += $lrow["16-30"];
		$JML_OVD2 += $lrow["jml_16-30"];
		$OVD3 += $lrow["31-60"];
		$JML_OVD3 += $lrow["jml_31-60"];
		$OVD4 += $lrow[">60"];
		$JML_OVD4 += $lrow["jml_>60"];
		
		$persen_current =round($current/$total_due, 2) * 100;
		
		echo '
			<tr>
				<td>'.$lrow["kd_cabang"].'</td>
				<td>'.$lrow["initial_cabang"].'</td>
				<td style="text-align:right">'.round($lrow["jml_current"], 2).'</td>
				
				<td style="text-align:right">'.round($lrow["current"],2).'</td>
				<td style="text-align:right">'.$persen.'</td>
				<td style="text-align:right">'.round($lrow["jml_0-15"], 2).'</td>
				<td style="text-align:right">'.round($lrow["0-15"], 2).'</td>
				<td style="text-align:right">'.round($lrow["jml_16-30"], 2).'</td>
				<td style="text-align:right">'.round($lrow["16-30"], 2).'</td>
				<td style="text-align:right">'.round($lrow["jml_31-60"], 2).'</td>
				<td style="text-align:right">'.round($lrow["31-60"],2).'</td>
				<td style="text-align:right">'.round($lrow["jml_>60"],2).'</td>
				<td style="text-align:right">'.round($lrow[">60"],2).'</td>
				<td style="text-align:right">'.round($lrow["jml_total_due"],2).'</td>
				<td style="text-align:right">'.round($lrow["total_due"],2).'</td>
			</tr>
		';	

	}
	
	$no++;

	echo '
		<tr>
			<td colspan="15">&nbsp;</td>
 		</tr>
  		 <tr>
				<td colspan="2">TOT</td>
				<td style="text-align:right">'.$jml_current.'</td>
				<td style="text-align:right">'.$current.'</td>
				<td style="text-align:right">'.$persen_current.'</td>
				<td style="text-align:right">'.$JML_OVD1.'</td>
				<td style="text-align:right">'.$OVD1.'</td>
				<td style="text-align:right">'.$JML_OVD2.'</td>
				<td style="text-align:right">'.$OVD2.'</td>
				<td style="text-align:right">'.$JML_OVD3.'</td>
				<td style="text-align:right">'.$OVD3.'</td>
				<td style="text-align:right">'.$JML_OVD4.'</td>
				<td style="text-align:right">'.$OVD4.'</td>
				<td style="text-align:right">'.$jml_total_due.'</td>
				<td style="text-align:right">'.$total_due.'</td>
 		 </tr>
	</table>';
}

