<?php
include_once("report.php");

function filter_request(){
	global $fk_sbg,$nm_customer;
	$fk_sbg=($_REQUEST["fk_sbg"]);
	$nm_customer=($_REQUEST["nm_customer"]);
	$showPeriode='f';
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
	lSentText="table=(select fk_sbg,nm_customer from tblinventory left join tblcustomer on no_cif=fk_cif)as tbl&field=(nm_customer)&key=fk_sbg&value="+document.form1.fk_sbg.value
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
          <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef">No SBG</td>
          <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                        <input name="fk_sbg" type="text" onKeyPress="if(event.keyCode==4) img_fk_sbg.click();" onKeyUp="fNextFocus(event,document.form1.periode_awal)"  value="<?=$fk_sbg?>" onChange="fGetBtkData()">&nbsp;<img src="../images/search.gif" id="img_fk_sbg" onClick="fGetBtk()" style="border:0px" align="absmiddle">
                    </td>
           <td style="padding:0 5 0 5" width="20%">Nama Customer</td>
            <td style="padding:0 5 0 5" width="30%"><input type="hidden" name="nm_customer" class='groove_text' value="<?=$nm_customer?>"><span id="divnm_customer"><?=$nm_customer?></span></td>
	  </tr>                   
<?	
}

function excel_content(){
	global $periode_awal,$periode_akhir,$periode_awal1,$periode_akhir1,$fk_sbg,$nm_cabang,$fk_cif,$nm_customer;
	
	if($fk_sbg != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_sbg = '".$fk_sbg."' ";
	}
		
	if ($lwhere!="") $lwhere=" where ".$lwhere;
	

	$query = "
	select * from data_gadai.tblhistory_sbg
	".$lwhere."
	order by fk_sbg,no asc
	";
//showquery($query);
	echo 	
	'<table border="1">
	  
		   <tr>
		 	<td align="center">No</td>
			<td align="center">No SBG</td>
			<td align="center">Tanggal Bayar</td>
			<td align="center">Nilai Bayar</td>
			<td align="center">Angsuran Ke</td>
			<td align="center">Denda</td>
			<td align="center">Transaksi</td>	
			<td align="center">Referensi</td>
		  </tr>
		  
	';
	$lrs = pg_query($query);
	$no=1;

	while($lrow=pg_fetch_array($lrs)){

		echo '
		
			<tr>
				<td valign="top" align="right">'.$lrow['no'].'</td>
				<td valign="top">&nbsp;'.$lrow["fk_sbg"].'</td>
				<td valign="top">'.($lrow["tgl_bayar"]==""?"":date("d/m/Y",strtotime($lrow["tgl_bayar"]))).'</td>
				<td valign="top" align="right">'.round($lrow["nilai_bayar"]).'</td>
				<td valign="top" align="right">'.$lrow["ang_ke"].'</td>
				<td valign="top" align="right">'.round($lrow["denda"]).'</td>
				<td valign="top" align="right">'.$lrow["transaksi"].'</td>
				<td valign="top" align="right">'.$lrow["referensi"].'</td>
			</tr>
			
		';	
		$no++;
	
	}
	echo '</table>';
}


