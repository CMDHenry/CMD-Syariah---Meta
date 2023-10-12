<?php
include_once("report.php");

function filter_request(){
	global $fk_sbg,$nm_customer;

	$fk_sbg=trim($_REQUEST["fk_sbg"]);
	$nm_customer=($_REQUEST["nm_customer"]);
}


function fGet(){
?>

function fGetBtk(){
	fGetCustomNC(false,'sbg','fk_sbg','Ganti Lokasi',document.form1.fk_sbg,document.form1.fk_sbg)
    if (document.form1.fk_sbg.value !="")fGetBtkData()	
}

function fGetBtkData(){
	
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataBtkState
	lSentText="table=(select no_sbg,nm_customer from viewkontrak left join viewtaksir on fk_fatg =no_fatg left join tblcustomer on no_cif=fk_cif)as tbl&field=(nm_customer)&key=no_sbg&value="+document.form1.fk_sbg.value
	lObjLoad.open("POST","../ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}
function fGetDataBtkState(){	
	if (this.readyState == 4){
		//confirm(this.responseText)
		if (this.status==200 && this.responseText!="") {
			lTemp=this.responseText.split('¿');
			document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value=lTemp[0]
		} else {
			document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value="-"
		}
	}
}


<?

}

function create_header_additional(){
	global $report,$periode_awal,$periode_akhir,$fk_sbg,$fk_cif,$nm_customer;
	
	
}

function create_filter(){
	global $periode_awal1,$periode_akhir1,$fk_sbg,$fk_cif,$nm_customer;
?>

	<tr bgcolor="efefef">
          <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef">No Kontrak</td>
          <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                        <input name="fk_sbg" type="text" onKeyPress="if(event.keyCode==4) img_fk_sbg.click();" onKeyUp="fNextFocus(event,document.form1.periode_awal)"  value="<?=$fk_sbg?>" onChange="fGetBtkData()">&nbsp;<img src="../images/search.gif" id="img_fk_sbg" onClick="fGetBtk()" style="border:0px" align="absmiddle">
                    </td>
           <td style="padding:0 5 0 5" width="20%">Nama Customer</td>
            <td style="padding:0 5 0 5" width="30%"><input type="hidden" name="nm_customer" class='groove_text' value="<?=$nm_customer?>"><span id="divnm_customer"><?=$nm_customer?></span></td>
	  </tr>                   
<?	
}

function excel_content(){
	global $periode_awal,$periode_akhir,$periode_awal1,$periode_akhir1,$fk_sbg,$nm_cabang,$fk_cif,$nm_customer,$fk_sbg;
		
	if($fk_sbg){
		$lwhere.=" fk_sbg = '".$fk_sbg."' ";
		if ($lwhere!="") $lwhere=" where ".$lwhere;
	}
	
	$query = "
	select * from(
	select no_sbg as fk_sbg,lama_pinjaman,rate_flat,addm_addb,'1' as jenis_produk from data_gadai.tblproduk_cicilan
	union
	select no_sbg, lama_pinjaman,rate_flat,'B','0' as jenis_produk from data_gadai.tblproduk_gadai
	)as tblmain
	".$lwhere."	
	";
	$lrs = pg_query($query);
	$lrow=pg_fetch_array($lrs);
	
	$lama_pinjaman=$lrow["lama_pinjaman"];
	$rate_flat=$lrow["rate_flat"];
	$addm_addb=$lrow["addm_addb"];	
	$jenis_produk=$lrow["jenis_produk"];	
	if($jenis_produk==1){
		$note="(Bunga Berjalan)";
	}
	
	if($jenis_produk){
		$rate_eff=(flat_eff($rate_flat,$lama_pinjaman,$addm_addb));
		
		echo 'Rate Flat : '.$rate_flat.'<br>';
		echo 'Rate Eff : '.$rate_eff.'<br>';
	}
	//echo $jenis_produk.'test';
	$query = "
	select * from data_fa.tblangsuran	
	inner join (select fk_sbg as no_sbg, fk_cabang, fk_cif from tblinventory) as tblinventory on no_sbg=fk_sbg
	left join tblcustomer on no_cif = fk_cif
	".$lwhere."
	order by fk_sbg, angsuran_ke asc
	";
	//showquery($query);
	echo 	
	'<table border="1">
	     <tr>
		 	<td align="center">Tanggal Jatuh Tempo</td>
			<td align="center">No Kontrak</td>
			<td align="center">No Urut</td>
			<td align="center">Nilai Angsuran</td>
			<td align="center">Pokok Jatuh Tempo</td>
			<td align="center">Margin Jatuh Tempo</td>
			<td align="center">Saldo Pokok</td>	
			<td align="center">Saldo Margin</td>
			<td align="center">Saldo Pinjam</td>
			<td align="center">Akru</td>	
			<td align="center">Saldo Akru</td>		
			<td align="center">Tanggal Bayar</td>
	
	';
	
	//<td align="center">Akrual 1 '.$note.'</td>	
	//<td align="center">Akrual 2</td>	


			
	echo '</tr>';
		
	$lrs = pg_query($query);
	$no=1;

	while($lrow=pg_fetch_array($lrs)){
		if($no==1)$saldo_akru=$lrow["saldo_bunga"];
		$total_akru=($lrow["akrual1"]+$lrow["akrual2"]);
		$saldo_akru-=$total_akru;
		echo '
			<tr>
				<td valign="top">'.($lrow["tgl_jatuh_tempo"]==""?"":date("d/m/Y",strtotime($lrow["tgl_jatuh_tempo"]))).'</td>
				<td valign="top">&nbsp;'.$lrow["fk_sbg"].'</td>
				<td valign="top" align="right">'.$lrow["angsuran_ke"].'</td>
				<td valign="top" align="right">'.convert_money("",$lrow["nilai_angsuran"]).'</td>
				<td valign="top" align="right">'.convert_money("",$lrow["pokok_jt"]).'</td>
				<td valign="top" align="right">'.convert_money("",$lrow["bunga_jt"]).'</td>
				<td valign="top" align="right">'.convert_money("",$lrow["saldo_pokok"]).'</td>
				<td valign="top" align="right">'.convert_money("",$lrow["saldo_bunga"]).'</td>
				<td valign="top" align="right">'.convert_money("",$lrow["saldo_pinjaman"]).'</td>
				<td valign="top" align="right">'.convert_money("",$lrow["akrual1"]+$lrow["akrual2"]).'</td>
				<td valign="top" align="right">'.convert_money("",$saldo_akru).'</td>				
				<td valign="top">'.($lrow["tgl_bayar"]==""?"":date("d/m/Y",strtotime($lrow["tgl_bayar"]))).'</td>	
		';	
		//<td valign="top" align="right">'.convert_money("",$lrow["akrual2"]).'</td>

		
		echo '</tr>';
			
		$no++;
	
	}
	echo '</table>';
}


