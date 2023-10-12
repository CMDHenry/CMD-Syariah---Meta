<?php

include_once("report.php");

function filter_request(){
	global $periode_awal,$periode_akhir,$showPeriode,$showCab,$periode_awal1,$periode_akhir1;
	$showPeriode='t';
	//$showCab='t';
	$periode_awal1 = convert_date_english($_REQUEST["periode_awal1"]);
	$periode_akhir1 =  convert_date_english($_REQUEST["periode_akhir1"]);
	

}

function create_filter(){
	global $is_outs,$periode_awal1,$periode_akhir1;
?>

<!--    <tr bgcolor="efefef">
        <td width="20%" style="padding:0 5 0 5" class="">Periode </td>
        <td width="80%" style="padding:0 5 0 5" colspan="3">
                        <input type="text" name="periode_awal1" value="<?=convert_date_indonesia($periode_awal1)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_awal1.click();" size="10" >&nbsp;<img src="../images/btn_extend.gif" name="img_periode_awal1" onClick="fPopCalendar(document.form1.periode_awal1,document.form1.periode_awal1)"> -                               
                        <input type="text" name="periode_akhir1" value="<?=convert_date_indonesia($periode_akhir1)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_akhir1.click();" size="10">&nbsp;<img src="../images/btn_extend.gif" name="img_periode_akhir1" onClick="fPopCalendar(document.form1.periode_akhir1,document.form1.periode_akhir1)">                                
        </td>
    </tr>               
-->

<?
}
function excel_content(){
	global $periode_awal,$periode_akhir,$periode_awal1,$periode_akhir1,$fk_cabang,$periode_awal1,$periode_akhir1;
	if($periode_awal != '' && $periode_akhir != ''){
		$lwhere.=" tgl_pengaduan between '".$periode_awal." 00:00:00' and '".$periode_akhir." 23:59:59'";
	}
	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cabang = '".$fk_cabang."' ";
	}
	
	if ($lwhere!="") $lwhere=" where ".$lwhere;
	
	$query = "
	select *,extract(day from((case when tgl_selesai is not null then tgl_selesai else current_date end -tgl_pengaduan)))as total_hari from (
		select * from data_gadai.tblpengaduan_customer
		inner join (select fk_cif,fk_sbg,fk_cabang,tgl_cair as tgl_cair1,tgl_jt from tblinventory where status!='Batal') as tblinv on tblinv.fk_sbg=tblpengaduan_customer.fk_sbg
		left join (select nm_customer,no_cif,alamat_ktp from tblcustomer) as tblcust on no_cif=fk_cif 
		left join (
			select * from data_gadai.tbltaksir_umum
			left join(
				select no_fatg as fk_fatg,nm_jenis_barang from viewkendaraan
			)as tblkendaraan on no_fatg=fk_fatg
		)as tbl on tblpengaduan_customer.fk_sbg=no_sbg_ar
	) as tblmain ".$lwhere." ";

	//showquery($query);
	echo 	
	'<table border="1">
	     <tr>
			<td align="center">Nama Debitur</td>		 
		 	<td align="center">No Akad</td>
			<td align="center">Jenis Kendaraan</td>
			<td align="center">Nomor Polisi</td>
			<td align="center">Tgl. JTO</td>
			<td align="center">Jenis Pengaduan</td>
			<td align="center">Tgl. Pengaduan</td>		
			<td align="center">Dokumen Pendukung</td>							
			<td align="center">Deskripsi</td>
			<td align="center">Tindak Lanjut</td>
			<td align="center">Status</td>
			<td align="center">Total Hari</td>			
		  </tr>
	';
	$lrs = pg_query($query);
	$no=1;
	
	while($lrow=pg_fetch_array($lrs)){
		$dokumen='';
		if($lrow["upload_ktp"]){
			$dokumen='KTP ,';
		}
		if($lrow["upload_surat"]){
			$dokumen.='Surat Permohonan ,';
		}
		if($lrow["upload_lain"]){
			$dokumen.='Lainnya';
		}
		
		echo '
			<tr>
				<td valign="top">'.$lrow["nm_customer"].'</td>			
				<td valign="top">&nbsp;'.$lrow["fk_sbg"].'</td>
				<td valign="top">'.$lrow["nm_jenis_barang"].'</td>	
				<td valign="top">'.$lrow["no_polisi"].'</td>							
				<td valign="top">'.($lrow["tgl_jt"]==""?"":date("d/m/Y",strtotime($lrow["tgl_jt"]))).'</td>
				<td valign="top">'.$lrow["jenis_pengaduan"].'</td>							
				<td valign="top">'.($lrow["tgl_pengaduan"]==""?"":date("d/m/Y",strtotime($lrow["tgl_pengaduan"]))).'</td>
				<td valign="top">'.$dokumen.'</td>
				<td valign="top">'.$lrow["deskripsi"].'</td>
				<td valign="top">'.$lrow["tindak_lanjut"].'</td>
				<td valign="top">'.$lrow["status_pengaduan"].'</td>
				<td valign="top">'.$lrow["total_hari"].'</td>
			</tr>
		';	
		$no++;
	
	}
	echo '</table>';
}



