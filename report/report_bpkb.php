<?php

include_once("report.php");

function filter_request(){
	global $showPeriode,$showCab,$is_outs_terima,$nm_periode,$periode_awal0,$periode_akhir0,$periode_awal1,$periode_akhir1,$showDlr,$jenis_report,$periode_awal2,$periode_akhir2,$posisi_bpkb,$periode_awal3,$periode_akhir3,$periode_awal,$periode_akhir;
	//$showPeriode='t';
	$showCab='t';
	$showDlr='t';
	
	$is_outs_terima=trim($_REQUEST["is_outs_terima"]);
	if($is_outs_terima==""){
		$is_outs_terima="f";
	}
	$jenis_report=trim($_REQUEST["jenis_report"]);
	$posisi_bpkb=trim($_REQUEST["posisi_bpkb"]);
	
	
	$periode_awal = convert_date_english($_REQUEST["periode_awal"]);
	$periode_akhir =  convert_date_english($_REQUEST["periode_akhir"]);	
	$periode_awal0 = convert_date_english($_REQUEST["periode_awal0"]);
	$periode_akhir0 =  convert_date_english($_REQUEST["periode_akhir0"]);	
	
	
	
	$periode_awal1 = convert_date_english($_REQUEST["periode_awal1"]);
	$periode_akhir1 =  convert_date_english($_REQUEST["periode_akhir1"]);
	$periode_awal2 = convert_date_english($_REQUEST["periode_awal2"]);
	$periode_akhir2 =  convert_date_english($_REQUEST["periode_akhir2"]);
	$periode_awal3 = convert_date_english($_REQUEST["periode_awal3"]);
	$periode_akhir3 =  convert_date_english($_REQUEST["periode_akhir3"]);
}
function create_filter(){
	global $periode_awal,$periode_akhir,$periode_awal1,$periode_akhir1,$periode_awal2,$periode_akhir2,$jenis_report,$posisi_bpkb,$periode_awal3,$periode_akhir3;
?>

    <tr bgcolor="efefef">
        <td width="20%" style="padding:0 5 0 5" class="">Periode Batch</td>
        <td width="80%" style="padding:0 5 0 5" colspan="3">
            <input type="text" name="periode_awal" value="<?=convert_date_indonesia($periode_awal)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_awal.click();" size="10" >&nbsp;<img src="../images/btn_extend.gif" name="img_periode_awal" onClick="fPopCalendar(document.form1.periode_awal,document.form1.periode_awal)"> -                               
            <input type="text" name="periode_akhir" value="<?=convert_date_indonesia($periode_akhir)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_akhir.click();" size="10">&nbsp;<img src="../images/btn_extend.gif" name="img_periode_akhir" onClick="fPopCalendar(document.form1.periode_akhir,document.form1.periode_akhir)">                                
        </td>
    </tr>               
    <!--<tr bgcolor="efefef">
        <td width="20%" style="padding:0 5 0 5" class="">Periode Kontrak</td>
        <td width="80%" style="padding:0 5 0 5" colspan="3">
            <input type="text" name="periode_awal0" value="<?=convert_date_indonesia($periode_awal0)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_awal0.click();" size="10" >&nbsp;<img src="../images/btn_extend.gif" name="img_periode_awal0" onClick="fPopCalendar(document.form1.periode_awal0,document.form1.periode_awal0)"> -                               
            <input type="text" name="periode_akhir0" value="<?=convert_date_indonesia($periode_akhir0)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_akhir0.click();" size="10">&nbsp;<img src="../images/btn_extend.gif" name="img_periode_akhir0" onClick="fPopCalendar(document.form1.periode_akhir0,document.form1.periode_akhir0)">                                
        </td>
    </tr> -->              

   <tr bgcolor="efefef">
        <td style="padding:0 5 0 5" width="20%">Posisi BPKB( STOK )</td>
        <td style="padding:0 5 0 5" width="30%">
        <select name="posisi_bpkb">
	       <option value=""<?= (($posisi_bpkb=='')?"selected":"") ?>>-</option>
           <option value="Cabang"<?= (($posisi_bpkb=='Cabang')?"selected":"") ?>>Cabang</option>
           <option value="HO"<?= (($posisi_bpkb=='HO')?"selected":"") ?>>HO</option>
           <option value="Bank"<?= (($posisi_bpkb=='Bank')?"selected":"") ?>>Bank</option> 
           <option value="Customer"<?= (($posisi_bpkb=='Customer')?"selected":"") ?>>Customer</option>          
        </select>
        </td>
        <td style="padding:0 5 0 5" width="20%"></td>
        <td style="padding:0 5 0 5" width="30%"></td>
     </tr>



   <tr bgcolor="efefef">
        <td style="padding:0 5 0 5" width="20%">Jenis Report</td>
        <td style="padding:0 5 0 5" width="30%">
        <select name="jenis_report">
	       <option value=""<?= (($jenis_report=='')?"selected":"") ?>>-</option>
           <option value="1"<?= (($jenis_report=='1')?"selected":"") ?>>Belum Terima dari Dealer</option>
           <option value="2"<?= (($jenis_report=='2')?"selected":"") ?>>Lunas tapi belum diserahkan ke Cust</option>
           <option value="3"<?= (($jenis_report=='3')?"selected":"") ?>>Sudah Mau Lunas</option>
           <option value="4"<?= (($jenis_report=='4')?"selected":"") ?>>Unit Ditarik</option>
           <option value="5"<?= (($jenis_report=='5')?"selected":"") ?>>JTO Belum Terima dari Dealer</option>           
           <option value="6"<?= (($jenis_report=='6')?"selected":"") ?>>Sudah Lunas 60 hari tapi belum diambil Cust</option>
           <option value="7"<?= (($jenis_report=='7')?"selected":"") ?>>Dikembalikan ke Dealer</option>
           <option value="8"<?= (($jenis_report=='8')?"selected":"") ?>>BPKB sudah diterima dari dealer tapi belum kirim ke HO</option>
        </select>
        </td>
        <td style="padding:0 5 0 5" width="20%"></td>
        <td style="padding:0 5 0 5" width="30%"></td>
   </tr>

    <tr bgcolor="efefef">
        <td width="20%" style="padding:0 5 0 5" class="">Periode Terima dr Dealer</td>
        <td width="80%" style="padding:0 5 0 5" colspan="3">
            <input type="text" name="periode_awal1" value="<?=convert_date_indonesia($periode_awal1)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_awal1.click();" size="10" >&nbsp;<img src="../images/btn_extend.gif" name="img_periode_awal1" onClick="fPopCalendar(document.form1.periode_awal1,document.form1.periode_awal1)"> -                               
            <input type="text" name="periode_akhir1" value="<?=convert_date_indonesia($periode_akhir1)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_akhir1.click();" size="10">&nbsp;<img src="../images/btn_extend.gif" name="img_periode_akhir1" onClick="fPopCalendar(document.form1.periode_akhir1,document.form1.periode_akhir1)">                                
        </td>
    </tr>               

    
    <tr bgcolor="efefef">
        <td width="20%" style="padding:0 5 0 5" class="">Periode Penyerahan BPKB Ke Cust </td>
        <td width="80%" style="padding:0 5 0 5" colspan="3">
            <input type="text" name="periode_awal2" value="<?=convert_date_indonesia($periode_awal2)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_awal2.click();" size="10" >&nbsp;<img src="../images/btn_extend.gif" name="img_periode_awal2" onClick="fPopCalendar(document.form1.periode_awal2,document.form1.periode_awal2)"> -                               
            <input type="text" name="periode_akhir2" value="<?=convert_date_indonesia($periode_akhir2)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_akhir2.click();" size="10">&nbsp;<img src="../images/btn_extend.gif" name="img_periode_akhir2" onClick="fPopCalendar(document.form1.periode_akhir2,document.form1.periode_akhir2)">                                
        </td>
    </tr>               
    
    <tr bgcolor="efefef">
        <td width="20%" style="padding:0 5 0 5" class="">Periode Cabut Limpah </td>
        <td width="80%" style="padding:0 5 0 5" colspan="3">
            <input type="text" name="periode_awal3" value="<?=convert_date_indonesia($periode_awal3)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_awal3.click();" size="10" >&nbsp;<img src="../images/btn_extend.gif" name="img_periode_awal3" onClick="fPopCalendar(document.form1.periode_awal3,document.form1.periode_awal3)"> -                               
            <input type="text" name="periode_akhir3" value="<?=convert_date_indonesia($periode_akhir3)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_akhir3.click();" size="10">&nbsp;<img src="../images/btn_extend.gif" name="img_periode_akhir3" onClick="fPopCalendar(document.form1.periode_akhir3,document.form1.periode_akhir3)">                                
        </td>
    </tr>               
    

<?
}

function excel_content(){
	global $periode_awal,$periode_akhir,$periode_awal0,$periode_akhir0,$periode_awal1,$periode_akhir1,$periode_awal2,$periode_akhir2,$fk_cabang,$fk_partner_dealer,$jenis_report,$posisi_bpkb,$periode_awal3,$periode_akhir3;
	//echo $periode_awal1;
	if($jenis_report!=''){
		$periode_awal1= $periode_akhir1=$periode_awal2= $periode_akhir2=$periode_awal3= $periode_akhir3="";
	}
	
	if($jenis_report == '1'){	
		if ($lwhere!="") $lwhere.=" and ";			
		$lwhere.=" tgl_terima_bpkb is null and status_barang='Baru' ";
	}	
	//sisa_angs=1 or 
	$ovd_lunas_bpkb=get_rec("tblsetting","ovd_lunas_bpkb","tgl_sistem is not null");
	
	if($jenis_report == '2'){	
		if ($lwhere!="") $lwhere.=" and ";			
		$lwhere.=" posisi_bpkb='Cabang' and tgl_serah_terima_bpkb is null and (sisa_angs is null)";
	}
	
	if($jenis_report == '3'){	
		if ($lwhere!="") $lwhere.=" and ";			
		$lwhere.=" (sisa_angs =1 or sisa_angs =2)";
	}
	
	if($jenis_report == '4'){	
		if ($lwhere!="") $lwhere.=" and ";			
		$lwhere.=" (status='Tarik' and tgl_lunas is null)";
	}
	
	if($jenis_report == '5'){	
		if ($lwhere!="") $lwhere.=" and ";			
		$lwhere.=" (status_ovd_terima='YA')";
	}
	
	if($jenis_report == '6'){	
		if ($lwhere!="") $lwhere.=" and ";			
		$lwhere.=" posisi_bpkb in('Cabang') and tgl_lunas is not null and ('".today_db."'-tgl_lunas)>'60 days' ";
	}	
	
	if($jenis_report == '7'){	
		if ($lwhere!="") $lwhere.=" and ";			
		$lwhere.=" tgl_pengembalian_ke_dealer is not null ";
	}	
	
	if($jenis_report == '8'){	
		if ($lwhere!="") $lwhere.=" and ";			
		$lwhere.=" posisi_bpkb in('Cabang') and tgl_terima_di_ho is null";
	}	
		
	if($posisi_bpkb !=''){	
		if ($lwhere!="") $lwhere.=" and ";			
		$lwhere.=" posisi_bpkb='".$posisi_bpkb."'";
		$periode_awal1= $periode_akhir1=$periode_awal2= $periode_akhir2=$periode_awal3= $periode_akhir3="";
	}
	
	if($periode_awal != '' && $periode_akhir != ''){
		if ($lwhere!="") $lwhere.=" and ";			
		$lwhere.=" tgl_cair between '".$periode_awal." 00:00:00' and '".$periode_akhir." 23:59:59'";
	}	
	
	if($periode_awal0 != '' && $periode_akhir0 != ''){
		if ($lwhere!="") $lwhere.=" and ";			
		$lwhere.=" tgl_pengajuan between '".$periode_awal0." 00:00:00' and '".$periode_akhir0." 23:59:59'";
	}	


	
	if($periode_awal1 != '' && $periode_akhir1 != ''){
		if ($lwhere!="") $lwhere.=" and ";			
		$lwhere.=" tgl_terima_bpkb between '".$periode_awal1." 00:00:00' and '".$periode_akhir1." 23:59:59'";
	}
	
	if($periode_awal2 != '' && $periode_akhir2 != ''){
		if ($lwhere!="") $lwhere.=" and ";			
		$lwhere.=" tgl_serah_terima_bpkb between '".$periode_awal2." 00:00:00' and '".$periode_akhir2." 23:59:59'";
	}	
	
	if($periode_awal3 != '' && $periode_akhir3 != ''){
		if ($lwhere!="") $lwhere.=" and ";			
		$lwhere.=" tgl_cabut_limpah between '".$periode_awal3." 00:00:00' and '".$periode_akhir3." 23:59:59'";
	}	
	
	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cabang_bpkb = '".$fk_cabang."' ";
	}
	
	if($fk_partner_dealer != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_partner_dealer = '".$fk_partner_dealer."' ";
	}
	
	if ($lwhere!="") $lwhere=" where ".$lwhere;
	else {
		$lwhere.=" where posisi_bpkb !='Customer' or posisi_bpkb is null ";
	}
	
	$query = "
select * from(
	select 
	case when jml_hari_ovd_terima>round(ovd_terima_bpkb) then 'YA' end as status_ovd_terima,
	case when jml_hari_ovd_lunas>".$ovd_lunas_bpkb." then 'YA' end as status_ovd_lunas,
	* from(
		select 
		case 
		when posisi_bpkb is null then 0 
		when posisi_bpkb ='Cabang' then 1
		when posisi_bpkb ='HO' then 2
		when posisi_bpkb ='Bank' then 3
		when posisi_bpkb ='Customer' then 4
		end as urutan,
		case when tgl_terima_bpkb is null then date_part('day',('".today_db."'-tgl_cair))::numeric else 0 end as jml_hari_ovd_terima,
		case when tgl_serah_terima_bpkb is null then date_part('day',('".today_db."'-tgl_lunas))::numeric else 0 end as jml_hari_ovd_lunas,* from (
			select fk_cif,fk_sbg,fk_cabang,tgl_cair,tgl_lunas,status from tblinventory where tgl_cair is not null and status!='Batal') as tblinv	
		left join (select nm_customer,no_cif,alamat_ktp from tblcustomer) as tblcust on no_cif=fk_cif 
		left join (
			select * from data_gadai.tbltaksir_umum
		)as tblkredit on no_sbg_ar=fk_sbg
		left join(
			select tgl_pengajuan,no_sbg from data_gadai.tblproduk_cicilan 
		)as tblcicilan on no_sbg_ar=no_sbg
		left join (select kd_partner,nm_partner, ovd_terima_bpkb from tblpartner)as tblpartner on fk_partner_dealer=kd_partner		
		left join(
			select count(1)as sisa_angs,fk_sbg as fk_sbg1 from data_fa.tblangsuran where tgl_bayar is null
			group by fk_sbg
		)as tblang on fk_sbg=fk_sbg1
		left join(
			select distinct on(fk_sbg)tgl_kirim as tgl_kirim_ke_ho,fk_sbg as fk_sbg_mutasi,tgl_terima as tgl_terima_di_ho from data_fa.tblmutasi_bpkb 
			left join data_fa.tblmutasi_bpkb_detail on no_mutasi=fk_mutasi
			where tgl_batal is null and penerima='HO'  
		)as tblmutasi on fk_sbg=fk_sbg_mutasi
		
	)as tblmain	 	
)as tblmain ".$lwhere."
order by urutan,tgl_cair
";

	//showquery($query);
	echo 	
	'<table border="1">
	     <tr>
			<td align="center" rowspan="2">NO</td>
			<td align="center" rowspan="2">POSISI BPKB</td>
					 
		 	<td align="center" rowspan="2">NO KONTRAK</td>
			<td align="center" rowspan="2">TGL BATCHING</td>			
			<td align="center" rowspan="2">TGL KONTRAK</td>			
			
			
			<td align="center" rowspan="2">NAMA DEBITUR</td>
			<td align="center" colspan="6">DATA KENDARAAN</td>
			<td align="center" rowspan="2">DEALER</td>
			<td align="center" rowspan="2">TGL TERIMA dr DEALER</td>
			
			<td align="center" rowspan="2">TGL LUNAS</td>							
			<td align="center" rowspan="2">TGL PENYERAHAN BPKB </td>
			
			<td align="center" rowspan="2">TGL LIMPAH KE BANK</td>
			<td align="center" rowspan="2">TGL CABUT LIMPAH</td>
			<td align="center" rowspan="2">KETERANGAN</td>
		  </tr>
	     <tr>
			<td align="center">NO RANGKA</td>					 			
			<td align="center">NO MESIN</td>			
			<td align="center">NO POLISI</td>
			<td align="center">NAMA BPKB</td>		
			<td align="center">NO BPKB</td>
			<td align="center">TGL BPKB</td>		
		  </tr>
		  
	';
	$lrs = pg_query($query);
	$no=1;
	
	while($lrow=pg_fetch_array($lrs)){
		//echo $jenis_report;
		$keterangan='';
		if($lrow["tgl_terima_di_ho"]!=null){
			$keterangan="Sudah Diterima HO";
		}else{
			$keterangan=($lrow["tgl_kirim_ke_ho"]?"Sedang Dikirim":"Belum Dikirim");
		}
		
		if($lrow["tgl_terima_bpkb"]!=""){
			$num_tgl_terima = "1";
		}else{
			$num_tgl_terima = "0";
		}
		
		$total_tgl_terima += $num_tgl_terima;
		
		echo '
			<tr>
				<td valign="top">'.$no.'</td>
				<td valign="top">'.$lrow["posisi_bpkb"].'</td>			
				<td valign="top">&nbsp;'.$lrow["fk_sbg"].'</td>
				<td valign="top">'.($lrow["tgl_cair"]==""?"":date("d/m/Y",strtotime($lrow["tgl_cair"]))).'</td>	
				<td valign="top">'.($lrow["tgl_pengajuan"]==""?"":date("d/m/Y",strtotime($lrow["tgl_pengajuan"]))).'</td>								
											
				<td valign="top">'.$lrow["nm_customer"].'</td>
				<td valign="top">'.$lrow["no_rangka"].'</td>
				<td valign="top">'.$lrow["no_mesin"].'</td>
				<td valign="top">'.$lrow["no_polisi"].'</td>
				<td valign="top">'.$lrow["nm_bpkb"].'</td>				
				<td valign="top">'.$lrow["no_bpkb"].'</td>
				<td valign="top">'.($lrow["tgl_bpkb"]==""?"":date("d/m/Y",strtotime($lrow["tgl_bpkb"]))).'</td>
				<td valign="top">'.$lrow["nm_partner"].'</td>				
				<td valign="top">'.($lrow["tgl_terima_bpkb"]==""?"":date("d/m/Y",strtotime($lrow["tgl_terima_bpkb"]))).'</td>				
				
				<td valign="top">'.($lrow["tgl_lunas"]==""?"":date("d/m/Y",strtotime($lrow["tgl_lunas"]))).'</td>																					
				<td valign="top">'.($lrow["tgl_serah_terima_bpkb"]==""?"":date("d/m/Y",strtotime($lrow["tgl_serah_terima_bpkb"]))).'</td>
				<td valign="top">'.($lrow["tgl_limpah_ke_bank"]==""?"":date("d/m/Y",strtotime($lrow["tgl_limpah_ke_bank"]))).'</td>				
				<td valign="top">'.($lrow["tgl_cabut_limpah"]==""?"":date("d/m/Y",strtotime($lrow["tgl_cabut_limpah"]))).'</td>
				<td valign="top">'.$keterangan.'</td>	
							
			</tr>
		';	
		$no++;

	}

	echo '</table>';
	
	
	if($fk_cabang){
		$jenis_cabang=get_rec("tblcabang","jenis_cabang","kd_cabang='".$fk_cabang."'");
		echo 	
		'<table border="1">
			 <tr><td colspan="3" align="center" >Tanggal '.date("d/m/Y H:i:s").'</td></tr>
			 <tr>
				<td colspan="3" align="center" >Dibuat oleh</td>
				<td colspan="3" align="center" >Diperiksa Oleh</td>
				<td colspan="3" align="center"">Diketahui Oleh</td>
			 </tr>	
			<tr></tr>
			<tr></tr>
			<tr></tr>
			 
			<tr>
				<td colspan="3" align="center">'.$_SESSION['username'].'</td>
				<td colspan="3" align="center">'.get_karyawan_by_jabatan('Administration Head',$fk_cabang).'</td>
				<td colspan="3" align="center">'.get_karyawan_by_jabatan(($jenis_cabang=='Pos'?"Kepala Pos":"Kepala Cabang"),$fk_cabang).'</td>
			 </tr>	
		</table>	 
		';		
		
	}
}



