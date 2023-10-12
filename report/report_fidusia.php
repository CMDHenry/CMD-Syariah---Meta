<?php

include_once("report.php");

function filter_request(){
	global $periode_awal,$periode_akhir,$showPeriode,$showCab,$periode_awal1,$periode_akhir1;
	//$showPeriode='t';
	$showCab='t';
	$periode_awal1 = convert_date_english($_REQUEST["periode_awal1"]);
	$periode_akhir1 =  convert_date_english($_REQUEST["periode_akhir1"]);
	

}

function create_filter(){
	global $is_outs,$periode_awal1,$periode_akhir1;
?>

    <tr bgcolor="efefef">
        <td width="20%" style="padding:0 5 0 5" class="">Periode </td>
        <td width="80%" style="padding:0 5 0 5" colspan="3">
                        <input type="text" name="periode_awal1" value="<?=convert_date_indonesia($periode_awal1)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_awal1.click();" size="10" >&nbsp;<img src="../images/btn_extend.gif" name="img_periode_awal1" onClick="fPopCalendar(document.form1.periode_awal1,document.form1.periode_awal1)"> -                               
                        <input type="text" name="periode_akhir1" value="<?=convert_date_indonesia($periode_akhir1)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_akhir1.click();" size="10">&nbsp;<img src="../images/btn_extend.gif" name="img_periode_akhir1" onClick="fPopCalendar(document.form1.periode_akhir1,document.form1.periode_akhir1)">                                
        </td>
    </tr>               


<?
}
function excel_content(){
	global $periode_awal,$periode_akhir,$periode_awal1,$periode_akhir1,$fk_cabang,$periode_awal1,$periode_akhir1;
	if($periode_awal1 != '' && $periode_akhir1 != ''){
		$lwhere.=" tgl_pengajuan between '".$periode_awal1." 00:00:00' and '".$periode_akhir1." 23:59:59'";
	}
	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cabang = '".$fk_cabang."' ";
	}
	
	if ($lwhere!="") $lwhere=" where ".$lwhere;
	
	$query = "
	select * from (
		select *,extract(day from tgl_sertifikat_fidusia - tgl_pengajuan)as jml_hari from data_gadai.tblproduk_cicilan
		inner join (select fk_cif,fk_sbg,fk_cabang,tgl_cair as tgl_cair1 from tblinventory where status!='Batal') as tblinv on tblinv.fk_sbg=tblproduk_cicilan.no_sbg
		left join (select nm_customer,no_cif,alamat_ktp from tblcustomer) as tblcust on no_cif=fk_cif 
		left join tblpartner on fk_partner_notaris=kd_partner
		left join(
			select kategori,no_fatg from viewkendaraan
			left join tblpartner on fk_partner_dealer=kd_partner
		)as tbltaksir on no_fatg=fk_fatg		
		left join (
			select referensi as no_kwitansi_pelunasan,fk_sbg as fk_sbg2,tgl_data as tgl_pelunasan_dealer,nilai_bayar from data_gadai.tblhistory_sbg
			where transaksi='Pembayaran Fidusia' and tgl_batal is null
		)as tbl2 on no_sbg=fk_sbg2
		
		where fk_partner_notaris is not null --and no_sbg='40101230200016'
	) as tblmain ".$lwhere."  
	--and kategori='R2'
	order by tgl_cair
	";
	//showquery($query);
	
	echo 	
	'<table border="1">
	     <tr>
		 	<td align="center">NO AKAD</td>
			<td align="center">TGL AKAD</td>
			<td align="center">NAMA DEBITUR</td>
			<td align="center">ALAMAT DEBITUR</td>
			<td align="center">NO SERTIFIKAT JAMINAN FIDUSIA</td>
			<td align="center">TANGGAL SERTIFIKAT JAMINAN FIDUSIA</td>
			<td align="center">JAM SERTIFIKAT JAMINAN FIDUSIA</td>
			
			<td align="center">NO AKTA</td>
			<td align="center">TANGGAL AKTA</td>
			<td align="center">NAMA NOTARIS </td>
			<td align="center">JUMLAH HARI </td>
			<td align="center">BIAYA </td>
			<td align="center">JASA </td>
			<td align="center">PPH </td>
			<td align="center">TOTAL </td>
			<td align="center">NO BPB </td>
			<td align="center">NILAI DIBAYAR </td>
		  </tr>
	';
	$lrs = pg_query($query);
	$no=1;
	while($lrow=pg_fetch_array($lrs)){
		
		$tahun=date("Y",strtotime($lrow["tgl_cair1"]));
		$bulan=date("m",strtotime($lrow["tgl_cair1"]));
		
		$nominal=$lrow["biaya_notaris"];	
		$fk_partner=$lrow["fk_partner_notaris"];			
		$total_hutang=$lrow["total_hutang"];			
	
		//echo $nominal;
		$arr=calc_fidusia($nominal,$fk_partner,$bulan,$tahun,$total_hutang);	
		//print_r($arr);
		$lrow["biaya"]=$arr["biaya"];
		$lrow["jasa"]=$arr["jasa"];
		$lrow["pph21"]=$arr["pph21"];				
		$lrow["total"]=$arr["nominal"];				
		
		echo '
			<tr>
				<td valign="top">&nbsp;'.$lrow["no_sbg"].'</td>
				<td valign="top">'.($lrow["tgl_pengajuan"]==""?"":date("d/m/Y",strtotime($lrow["tgl_pengajuan"]))).'</td>
				<td valign="top">'.$lrow["nm_customer"].'</td>
				<td valign="top">'.$lrow["alamat_ktp"].'</td>
				<td valign="top">'.$lrow["no_sertifikat_fidusia"].'</td>
				<td valign="top">'.($lrow["tgl_sertifikat_fidusia"]==""?"":date("d/m/Y",strtotime($lrow["tgl_sertifikat_fidusia"]))).'</td>
				<td valign="top">'.$lrow["jam_sertifikat_fidusia"].'</td>
				<td valign="top">'.$lrow["no_akta_fidusia"].'</td>
				<td valign="top">'.($lrow["tgl_akta_fidusia"]==""?"":date("d/m/Y",strtotime($lrow["tgl_akta_fidusia"]))).'</td>
				<td valign="top">'.$lrow["nm_partner"].'</td>
				<td valign="top">'.$lrow["jml_hari"].'</td>
				
				<td valign="top">'.convert_money("",$lrow["biaya"]).'</td>
				<td valign="top">'.convert_money("",$lrow["jasa"]).'</td>
				<td valign="top">'.convert_money("",$lrow["pph21"]).'</td>
				<td valign="top">'.convert_money("",$lrow["total"]).'</td>
				<td valign="top">'.$lrow["no_kwitansi_pelunasan"].'</td>
				<td valign="top">'.convert_money("",$lrow["nilai_bayar"]).'</td>
			</tr>
		';	
		$no++;
	
	}
	echo '</table>';
}



