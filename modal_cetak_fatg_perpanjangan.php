<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/numeric.inc.php';
require 'requires/timestamp.inc.php';
require 'requires/db_utility.inc.php';
require 'classes/select.class.php';
require 'requires/file.inc.php';
require 'requires/accounting_utility.inc.php';
require 'classes/smtp.class.php';
require 'classes/excel.class.php';
require 'requires/stok_utility.inc.php';


$periode_awal = convert_date_english($_REQUEST["periode_awal"]);
$periode_akhir= convert_date_english($_REQUEST["periode_akhir"]);
$id_menu=trim($_REQUEST["id_menu"]);
get_data_menu($id_menu);	

$id_edit = $_REQUEST['id_edit'];
$no_cif_new = trim($_REQUEST['no_cif_new']);
//echo $id_edit;
if($id_edit){
	$fk_sbg = get_rec("viewtaksir","no_sbg_lama","no_fatg='".$id_edit."'");
	get_data();
}
else {
	$fk_sbg = $_REQUEST['fk_sbg'];
	$nm_customer =  $_REQUEST['nm_customer'];
	$no_cif = $_REQUEST['no_cif'];
	$fk_produk = $_REQUEST['fk_produk'];
}
if($_REQUEST["status"]=="Download") {
	if(!$id_edit){
		cek_error();
	}
	if(!$strmsg){
		download();
	}

}
//if($_REQUEST["status"]!="Download"){
?>
<html>
<head>
	<title>.: <?=$_SESSION["application"]?> :.</title>
    <link href="css/text.css.php" rel="stylesheet" type="text/css">
	<link rel="stylesheet" title="Style CSS" href="js/cwcalendar.css.php" type="text/css" media="all" />
</head>
<script language='javascript' src="js/alert.js.php"></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src='js/calendar.js.php'></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src='js/validate.js.php'></script>
<!--<script language='javascript' src="js/table_v2.js.php"></script>
-->
<script language='javascript'>

function fDownload(){
	document.form1.status.value='Download';
	document.form1.submit();
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")){
		lInputClose=getObjInputClose();lInputClose.close()
	}
}
function fGetSbg(){
	fGetCustomNC(false,'sbg','fk_sbg','Ganti Lokasi',document.form1.fk_sbg,document.form1.fk_sbg,document.form1.tipe)
}

function fGetSbgData(){
	
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataSbgState
	lSentText="table=(select fk_produk,no_sbg,fk_cif,nm_customer from viewkontrak left join viewtaksir on no_fatg=fk_fatg left join tblcustomer on no_cif=fk_cif) as tblmain&field=(nm_customer||'¿'||fk_cif||'¿'||fk_produk)&key=no_sbg&value="+document.form1.fk_sbg.value
	lObjLoad.open("POST","ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}
function fGetDataSbgState(){	
	if (this.readyState == 4){
		//confirm(this.responseText)
		if (this.status==200 && this.responseText!="") {
			lTemp=this.responseText.split('¿');
			document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value=lTemp[0]
			document.getElementById('divno_cif').innerHTML=document.form1.no_cif.value=lTemp[1]
			document.form1.fk_produk.value=lTemp[2]
		} else {
			document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value="-"
			document.getElementById('divno_cif').innerHTML=document.form1.no_cif.value="-"
		}
	}
}

function fGetCIFData(){
   lObjLoad = getHTTPObject()
   lObjLoad.onreadystatechange=fGetDataCIFState
   lSentText="table=tblcustomer&field=(no_id||'¿'||nm_customer)&key=no_cif&value="+document.form1.no_cif_new.value
   lObjLoad.open("POST","ajax/get_data.php",true);
   lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
   lObjLoad.setRequestHeader("Content-Length",lSentText.length)
   lObjLoad.setRequestHeader("Connection","close")
   lObjLoad.send(lSentText);
}

function fGetDataCIFState(){
  
   if (this.readyState == 4){
      if (this.status==200 && this.responseText!="") {
         lTemp=this.responseText.split('¿');	
		 //document.form1.no_id.value=lTemp[0]
		 document.getElementById('divnm_customer_new').innerHTML=document.form1.nm_customer_new.value=lTemp[1]
      } else {
		 //lAlerttxt="No CIF tidak ada"
		 // alert("Error : <br>"+lAlerttxt)
		 //document.form1.no_id.value="-"
		 //document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value="-"
      }
   }
   
}
function fCheckSubmit(){
	
	if (document.form1.status.value=='Save') {
		return true
	}
	else return false
}


function fLoad(){
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	} elseif($j_action){
		echo $j_action;
	}else{
		echo "document.form1.fk_sbg.focus();";
	}
?>
}
</script>
<body onLoad="fLoad()" bgcolor="#fafafa">
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="form1" id="form1" enctype="multipart/form-data" onSubmit="return fCheckSubmit()">
<input type="hidden" name="status">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="tipe" value="Perpanjangan">
<input type="hidden" name="fk_produk" value="<?=$fk_produk?>">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<tr><td class="judul_menu" align="center">PERPANJANGAN</td></tr>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
                        <tr bgcolor="efefef">
                            <td style="padding:0 5 0 5" width="20%">No Kontrak</td>
                            <? if($id_edit!=""){?>
                            <td style="padding:0 5 0 5" width="30%"><input type="hidden" name="fk_sbg" value="<?=$fk_sbg?>" class="groove_text"><?=$fk_sbg?></td>
                            <? } else { ?>
                            <td style="padding:0 5 0 5" width="30%"><input name="fk_sbg" type="text" class='groove_text ' size="20" id="fk_sbg"  onChange="fGetSbgData()" value="<?=$fk_sbg?>">&nbsp;<img src="images/search.gif" style="border:0px" align="absmiddle" onClick="fGetSbg()"></td>
                            <? } ?>
                            <td style="padding:0 5 0 5" width="20%"></td>
            				<td style="padding:0 5 0 5" width="30%"></td>
                        </tr>
                        
                        <tr bgcolor="efefef">
                            <td style="padding:0 5 0 5" width="20%">No CIF </td>
            				<td style="padding:0 5 0 5" width="30%"><input type="hidden" name="no_cif" class='groove_text' value="<?=$no_cif?>" onChange="fGetCIFData()"><span id="divno_cif"><?=$no_cif?></span></td>
                            <td style="padding:0 5 0 5" width="20%">Nama Customer</td>
                            <td style="padding:0 5 0 5" width="30%"><input type="hidden" name="nm_customer" class='groove_text' value="<?=$nm_customer?>"><span id="divnm_customer"><?=$nm_customer?></span></td>
                        </tr>
                       
<!--                       	<tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
                        	<td colspan="4">&nbsp;</td>
						</tr>
                        <? if($id_edit==""){?>
                        <tr bgcolor="efefef">
                            <td style="padding:0 5 0 5" width="20%">No CIF Pengganti</td>
            				<td style="padding:0 5 0 5" width="30%"><input type="text" name="no_cif_new" class='groove_text' value="<?=$no_cif_new?>" onChange="fGetCIFData()"></td>
                            <td style="padding:0 5 0 5" width="20%">Nama Customer Pengganti</td>
                            <td style="padding:0 5 0 5" width="30%"><input type="hidden" name="nm_customer_new" class='groove_text' value="<?=$nm_customer_new?>"><span id="divnm_customer_new"><?=$nm_customer_new?></span></td>
                        </tr>                        
                       <? } else{?>
                        <tr bgcolor="efefef">
                            <td style="padding:0 5 0 5" width="20%">No CIF Pengganti</td>
            				<td style="padding:0 5 0 5" width="30%"><input type="hidden" name="no_cif_new" class='groove_text' value="<?=$no_cif_new?>" onChange="fGetCIFData()"><?=$no_cif_new?></td>
                            <td style="padding:0 5 0 5" width="20%">Nama Customer Pengganti</td>
                            <td style="padding:0 5 0 5" width="30%"><input type="hidden" name="nm_customer_new" class='groove_text' value="<?=$nm_customer_new?>"><span id="divnm_customer_new"><?=$nm_customer_new?></span></td>
                        </tr>                        
                       
                        <? } ?>
-->                       
            </table>
<input name="no_id" type="hidden" class='groove_text ' size="20" id="no_id" value="<?=$no_id?>" onChange="fGetKTPData()">
<!-- end content begin -->
	  	</td>
    </tr>
</table>

<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" align="center">
	<tr height="20">
    	<td height="25" align="center" bgcolor="#D0E4FF">
            <input class="groove_button" type='button' name="btngenerate" value='Cetak' onClick="fDownload()">

		</td>
	</tr>
</table>
</form>
</body>
</html>
<?
//}

function get_data(){
	global $id_edit,$strmsg,$j_action,$no_cif,$fk_sbg,$nm_customer,$no_cif_new,$nm_customer_new,$id_edit;

	$lrow=pg_fetch_array(pg_query("select fk_produk,no_sbg,fk_cif,nm_customer from viewkontrak left join viewtaksir on no_fatg=fk_fatg left join tblcustomer on no_cif=fk_cif  where no_sbg='".$fk_sbg."'"));
	//echo $lrow["fk_cif"];
	$no_cif=$lrow["fk_cif"];
	$nm_customer=$lrow["nm_customer"];
	$fk_produk=$lrow["nm_customer"];
	
	$lrow=pg_fetch_array(pg_query("select fk_cif ,nm_customer from viewtaksir left join tblcustomer on no_cif=fk_cif  where no_fatg='".$id_edit."'"));
	$no_cif_new=$lrow["fk_cif"];
	$nm_customer_new=$lrow["nm_customer"];

	
}
function download(){
	global $id_edit,$upload_path,$strmsg,$no_cif,$no_fatg,$fk_sbg,$no_cif_new;
	$l_success=1;
	pg_query("BEGIN");	
	
	
	if($id_edit==""){
		$no_fatg=perpanjangan($fk_sbg,$no_cif);
	} else {
		$no_fatg=$id_edit;	
	}
	//echo $no_cif_view;
	//$l_success=0;
	if ($l_success==1){
		//$strmsg="Data saved.<br>";
		//$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		header("location:print/print_survey.php?no_cif=".$no_cif."&no_fatg=".$no_fatg."&id_edit=".$no_fatg."");
		pg_query("COMMIT");	
	}else{
		$strmsg="Error :<br>Save failed.<br>";
		pg_query("ROLLBACK");
	}
	
	
	
}
function cek_error(){
	global $strmsg,$j_action,$no_cif_new,$periode_akhir2,$fk_sbg,$fk_produk;
		
	if($fk_sbg == '') {
		$strmsg.="No Kontrak Harus Diisi.<br>";
	}
	else if(!pg_num_rows(pg_query("select * from viewkontrak where no_sbg='".$fk_sbg."' "))){
		$strmsg.="No Kontrak ".$fk_sbg.", tidak ada.<br>";
		$j_action="document.form1.no_sbg.focus()";
	}		
	else if(!pg_num_rows(pg_query("select * from tblinventory where fk_sbg='".$fk_sbg."' and tgl_cair is not null"))){
		$strmsg.="No Kontrak ".$fk_sbg.", belum pencairan.<br>";
		$j_action="document.form1.no_sbg.focus()";		
	}
	
	else if(pg_num_rows(pg_query("select * from tblinventory where fk_sbg='".$fk_sbg."' and tgl_lunas is not null"))){
		$strmsg.="No Kontrak ".$fk_sbg.", sudah pelunasan.<br>";
		$j_action="document.form1.no_sbg.focus()";		
	}
		
	else if(pg_num_rows(pg_query("select * from viewtaksir left join viewkontrak on fk_fatg=no_fatg where no_sbg_lama='".$fk_sbg."' and no_sbg is not null and status_data='Approve'"))){
		$strmsg.="No Kontrak ".$fk_sbg.", sudah Perpanjangan dan dibuat Kontrak baru.<br>";
		$j_action="document.form1.no_sbg.focus()";
	
	}
	
	else if(!pg_num_rows(pg_query("select * from tblproduk where kd_produk='".$fk_produk."' and is_perpanjangan='t'"))){
		$strmsg.="Produk ".$fk_produk." tidak bisa diperpanjang.<br>";
		$j_action="document.form1.no_sbg.focus()";
	
	}
	
	
/*	if($no_cif_new == '') {
		$strmsg.="Customer Baru Harus Diisi.<br>";
	}elseif(!pg_num_rows(pg_query("select * from tblcustomer where no_cif='".$no_cif_new."' "))){
		$strmsg.="No CIF Customer Baru Tidak ada.<br>";
		$j_action="document.form1.no_cif_new.focus()";
	}
*/	
	if($strmsg)$strmsg="Error :<br>".$strmsg;
}

?>