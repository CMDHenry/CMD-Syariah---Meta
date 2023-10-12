<?php

include_once("report.php");

function filter_request(){
	global $periode_awal,$periode_akhir,$showPeriode,$showKategori,$showCab,$periode_awal1,$periode_akhir1;
	//$showPeriode='t';
	$showCab='t';
	$showKategori='t';
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
	global $periode_awal,$periode_akhir,$periode_awal1,$periode_akhir1,$fk_cabang,$kd_jenis_barang,$periode_awal1,$periode_akhir1,$kategori;
	if($periode_awal1 != '' && $periode_akhir1 != ''){
		$lwhere.=" tgl_pengajuan between '".$periode_awal1." 00:00:00' and '".$periode_akhir1." 23:59:59'";
	}
	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cabang = '".$fk_cabang."' ";
	}
	if($kategori != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" kategori = '".$kategori."' ";
	}
	
	if ($lwhere!="") $lwhere=" where ".$lwhere;
	
	$query = "
	select * from (
		select * from data_gadai.tblproduk_cicilan
		inner join (
			select fk_cif,fk_sbg,fk_cabang,tgl_cair as tgl_cair1 from tblinventory
			--where fk_sbg in('42101230700463','a','b')
		) as tblinv on tblinv.fk_sbg=tblproduk_cicilan.no_sbg
		inner join tblcabang on fk_cabang=kd_cabang
		inner join viewkendaraan on fk_fatg=no_fatg
		left join (
			select nm_customer,no_cif,alamat_ktp from tblcustomer
		) as tblcust on no_cif=fk_cif 
		left join tblpartner on fk_partner_asuransi=kd_partner
		left join (
			select kd_jenis_barang from tbljenis_barang
		)as tbljenis on kd_jenis_barang=fk_jenis_barang
		left join(
			select referensi as no_batch,fk_sbg as fk_sbg_batch,tgl_batch from data_gadai.tblhistory_sbg
			left join(
				select distinct on(fk_owner)fk_owner,tr_date as tgl_batch from data_accounting.tblgl_auto
				where type_owner='AR'
			)as tbl on fk_owner=referensi 
			where transaksi='AR' and tgl_batal is null
		)as batch on no_sbg=fk_sbg_batch
		left join(
			select status_barang,no_fatg as no_fatg1 from viewtaksir
		)as viewtaksir on no_fatg1=fk_fatg
		where fk_partner_asuransi is not null and tgl_cair is not null
	) as tblmain ".$lwhere." --and status_barang='Datun'
	order by tgl_batch,no_batch
	";

	//showquery($query);
	echo 	
	'<table border="1">
	     <tr>
		 	<td align="center" rowspan="2">No</td>
		 	<td align="center" rowspan="2">Nama Perusahaan</td>
			<td align="center" rowspan="2">No Kontrak</td>
			<td align="center" rowspan="2">No Batch</td>
			
			<td align="center" rowspan="2">No CIF</td>			
			<td align="center" rowspan="2">No Polis</td>
			<td align="center" rowspan="2">Nama Tertanggung</td>
			<td align="center" rowspan="2">Alamat</td>
			<td align="center" rowspan="2">No Polisi</td>
			<td align="center" rowspan="2">Merk</td>
			<td align="center" rowspan="2">Jenis Kendaraan</td>
			<td align="center" rowspan="2">Tipe</td>
			<td align="center" rowspan="2">No Rangka</td>
			<td align="center" rowspan="2">No Mesin</td>
			<td align="center" rowspan="2">Warna</td>
			<td align="center" rowspan="2">Tahun</td>
			<td align="center" rowspan="2">Awal Pertang</td>
			<td align="center" rowspan="2">Akhir Pertang</td>
			<td align="center" rowspan="2">Tenor</td>
			<td align="center" rowspan="2">TSI</td>
			<td align="center" rowspan="2">TJH</td>
			<td align="center" rowspan="2">PA Penumpang</td>
			<td align="center" rowspan="2">PA Pengemudi</td>
			<td align="center" rowspan="2">Asuransi</td>
			<td align="center" rowspan="2">Rate</td>			
			<td align="center" colspan="5">Nominal Asuransi</td>						
			<td align="center" colspan="5">Nominal Tagihan</td>						
			<td align="center" colspan="5">Nominal sudah dibayar</td>			
		 </tr>
	     <tr>
		 	<td align="center">Tahun 1</td>
		 	<td align="center">Tahun 2</td>
		 	<td align="center">Tahun 3</td>
		 	<td align="center">Tahun 4</td>
		 	<td align="center">Tahun 5</td>		 
		 	<td align="center">Tahun 1</td>
		 	<td align="center">Tahun 2</td>
		 	<td align="center">Tahun 3</td>
		 	<td align="center">Tahun 4</td>
		 	<td align="center">Tahun 5</td>
		 	<td align="center">Tahun 1</td>
		 	<td align="center">Tahun 2</td>
		 	<td align="center">Tahun 3</td>
		 	<td align="center">Tahun 4</td>
		 	<td align="center">Tahun 5</td>
		 </tr>
		  
	';
	$lrs = pg_query($query);
	$no=1;
	
	while($lrow=pg_fetch_array($lrs)){
		$lrow["jenis_asuransi"]=jenis_asuransi($lrow);	
		
		if($lrow["kategori"]=='R2' || $lrow['status_barang']=='Datun'){
			$tsi=$lrow["pokok_hutang"];
			$tsi=$tsi/1000;
			$tsi=ceil($tsi)*1000;			
		}elseif($lrow["kategori"]=='R4'){
			$tsi=$lrow["total_nilai_pinjaman"];
		}
		
		$nominal_bayar=array();
		$query_byr="
			select sum(nilai_bayar)as nominal_bayar,fk_sbg as fk_sbg1,transaksi from data_gadai.tblhistory_sbg
			where transaksi like 'Pembayaran Asuransi%' and tgl_batal is null and fk_sbg='".$lrow["fk_sbg"]."'
			group by fk_sbg,transaksi
		";
		$lrs_byr = pg_query($query_byr);
		while($lrow_byr=  pg_fetch_array($lrs_byr)){
			$lrow_byr["transaksi"]=str_replace("Pembayaran Asuransi ","",$lrow_byr["transaksi"]);
			$nominal_bayar[$lrow_byr["transaksi"]]=$lrow_byr["nominal_bayar"];
		}
		
		$utang=array();
		$tagihan=array();
		$tenor=ceil($lrow["lama_pinjaman"]/12);	
		for($i=1;$i<=$tenor;$i++){
			$nominal=calc_asuransi($lrow["no_sbg"],$i,'t');						
			//echo $nominal.'<br>';
			if($nominal>0){				
				$utang[$i]=$nominal;
				$total['utang'][$i]+=$nominal;
			
				$asuransi=calc_asuransi_nett($lrow["no_sbg"],$nominal);
				$nominal=$asuransi['nominal'];
				$tagihan[$i]=$nominal;
				$total['tagihan'][$i]+=$nominal;
			}
			//if($nominal_bayar[$i]>0)$tagihan[$i]=$nominal_bayar[$i];
		}
		
		echo '
			<tr>
				<td valign="top">&nbsp;'.$no.'</td>
				<td valign="top">&nbsp;'.$lrow["nm_perusahaan"].'</td>
				<td valign="top">&nbsp;'.$lrow["no_sbg"].'</td>
				<td valign="top">&nbsp;'.$lrow["no_batch"].'</td>			
				<td valign="top">&nbsp;'.$lrow["fk_cif"].'</td>				
				<td valign="top">&nbsp;'.$lrow["no_polis"].'</td>
				<td valign="top">&nbsp;'.$lrow["nm_customer"].'</td>
				<td valign="top">&nbsp;'.$lrow["alamat_ktp"].'</td>
				<td valign="top">&nbsp;'.$lrow["no_polisi"].'</td>
				<td valign="top">&nbsp;'.$lrow["nm_merek"].'</td>
				<td valign="top">&nbsp;'.$lrow["nm_jenis_barang"].'</td>
				<td valign="top">&nbsp;'.$lrow["nm_tipe"].'</td>
				<td valign="top">&nbsp;'.$lrow["no_rangka"].'</td>
				<td valign="top">&nbsp;'.$lrow["no_mesin"].'</td>
				<td valign="top">&nbsp;'.$lrow["warna"].'</td>
				<td valign="top">&nbsp;'.$lrow["tahun"].'</td>
				<td valign="top">'.($lrow["tgl_cair"]==""?"":date("d/m/Y",strtotime($lrow["tgl_cair"]))).'</td>
				<td valign="top">'.($lrow["tgl_jatuh_tempo"]==""?"":date("d/m/Y",strtotime($lrow["tgl_jatuh_tempo"]))).'</td>
				<td valign="top">'.$lrow["lama_pinjaman"].'</td>
				<td valign="top">'.convert_money("",$tsi,0).'</td>
				<td valign="top">'.convert_money("",$lrow["tjh_3"],0).'</td>
				<td valign="top">'.convert_money("",$lrow["pa_penumpang"],0).'</td>
				<td valign="top">'.convert_money("",$lrow["pa_supir"],0).'</td>
				<td valign="top">'.$lrow["jenis_asuransi"]." ".$lrow["nm_partner"].'</td>
				
				<td valign="top">'.convert_money("",$lrow["rate_asuransi"],3).'</td>				
				
				<td valign="top">'.convert_money("",$utang[1],0).'</td>
				<td valign="top">'.convert_money("",$utang[2],0).'</td>
				<td valign="top">'.convert_money("",$utang[3],0).'</td>
				<td valign="top">'.convert_money("",$utang[4],0).'</td>
				<td valign="top">'.convert_money("",$utang[5],0).'</td>				
				
				<td valign="top">'.convert_money("",$tagihan[1],0).'</td>
				<td valign="top">'.convert_money("",$tagihan[2],0).'</td>
				<td valign="top">'.convert_money("",$tagihan[3],0).'</td>
				<td valign="top">'.convert_money("",$tagihan[4],0).'</td>
				<td valign="top">'.convert_money("",$tagihan[5],0).'</td>
				
				<td valign="top">'.convert_money("",$nominal_bayar[1],0).'</td>
				<td valign="top">'.convert_money("",$nominal_bayar[2],0).'</td>
				<td valign="top">'.convert_money("",$nominal_bayar[3],0).'</td>
				<td valign="top">'.convert_money("",$nominal_bayar[4],0).'</td>
				<td valign="top">'.convert_money("",$nominal_bayar[5],0).'</td>
				
			</tr>
		';	
		$no++;	
	}
	
	echo '
		<tr>
			<td colspan="25"></td>
	';
	for($i=1;$i<=5;$i++){		
	echo'	
			<td valign="top">'.convert_money("",$total['utang'][$i],0).'</td>
	';
	}
	for($i=1;$i<=5;$i++){		
	echo'	
			<td valign="top">'.convert_money("",$total['tagihan'][$i],0).'</td>
	';
	}
	
	echo '
		</tr>
	';

	echo '</table>';
}



