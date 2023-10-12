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

$periode_awal = convert_date_english($_REQUEST["periode_awal"]);
$periode_akhir= convert_date_english($_REQUEST["periode_akhir"]);

$id_menu=trim($_REQUEST["id_menu"]);
get_data_menu($id_menu);	
$kd_menu_button=trim($_REQUEST["kd_menu_button"]);
$lrs=pg_query("select * from skeleton.tblmenu where kd_menu='".$kd_menu_button."'");	
$lrow=pg_fetch_array($lrs);
$nama_button=$lrow["nama_menu"];


$no_id = trim($_REQUEST['no_id']);
$no_cif = trim($_REQUEST['no_cif']);
$nm_customer = $_REQUEST['nm_customer'];
if($_REQUEST["status"]=="Download") {
	cek_error();
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

function fModal(pType){
	lAlerttxt="No CIF tidak ada/kosong"
	
	switch (pType){
		case "new":
		if (confirm("Apakah anda ingin menginput data nasabah baru?")){
			//window.location=('modal_add.php?id_menu='+document.form1.id_menu.value)  
			show_modal('modal_add.php?id_menu='+document.form1.id_menu.value,'dialogwidth:900px;dialogheight:545px;')
		}	
		break;
		case "ocr":
		if (confirm("Apakah anda ingin menginput data nasabah baru?")){
			//window.location=('modal_add.php?id_menu='+document.form1.id_menu.value)  
			show_modal('modal_upload_ocr.php?id_menu='+document.form1.id_menu.value,'dialogwidth:900px;dialogheight:545px;')
		}	
		break;		
		case "upload_foto":
			if(document.form1.no_cif.value==""){
				alert("Error : <br>"+lAlerttxt)
			}else{
				show_modal('modal_upload_foto.php?id_menu='+document.form1.id_menu.value+'&pstatus=edit&kd_menu_button=10101012&id_edit='+document.form1.no_cif.value,'dialogwidth:900px;dialogheight:545px;')
			}
		break;
		case "upload_id":
			if(document.form1.no_cif.value==""){
				alert("Error : <br>"+lAlerttxt)
			}else{
				show_modal('modal_upload_foto.php?id_menu='+document.form1.id_menu.value+'&pstatus=edit&kd_menu_button=10101013&id_edit='+document.form1.no_cif.value,'dialogwidth:900px;dialogheight:545px;')
			}
		break;
	}
}

function fDownload(){
	document.form1.status.value='Download';
	document.form1.submit();
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")){
		lInputClose=getObjInputClose();lInputClose.close()
	}
}
function fGetCustomer(){
	fGetNC(false,'20170800000001','no_cif','Ganti Provinsi',document.form1.no_cif,document.form1.no_cif,'','','20170800000001')
}

function fGetKTPData(){
   lObjLoad = getHTTPObject()
   lObjLoad.onreadystatechange=fGetDataKTPState
   lSentText='&no_id='+document.form1.no_id.value
   lObjLoad.open("POST","ajax/get_data_ktp.php",true);
   lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
   lObjLoad.setRequestHeader("Content-Length",lSentText.length)
   lObjLoad.setRequestHeader("Connection","close")
   lObjLoad.send(lSentText);
}

function fGetDataKTPState(){
   if (this.readyState == 4){
      if (this.status==200 && this.responseText!="") {
         lTemp=this.responseText.split(',');	
		 document.form1.no_cif.value=lTemp[0]
		 document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value=lTemp[1]
      } else {
		 lAlerttxt="No ID tidak ada"
		 alert("Error : <br>"+lAlerttxt)
		 document.form1.no_cif.value="-"
		 document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value="-"
      }
   }
   
}

function fGetCIFData(){
   lObjLoad = getHTTPObject()
   lObjLoad.onreadystatechange=fGetDataCIFState
   lSentText="table=tblcustomer&field=(no_id||'¿'||nm_customer)&key=no_cif&value="+document.form1.no_cif.value
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
		 document.form1.no_id.value=lTemp[0]
		 document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value=lTemp[1]
      } else {
		 //lAlerttxt="No CIF tidak ada"
		// alert("Error : <br>"+lAlerttxt)
		 document.form1.no_id.value="-"
		 document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value="-"
      }
   }
   
}

function fLoad(){
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	} elseif($j_action){
		echo $j_action;
	}else{
		echo "document.form1.no_id.focus();";
	}
?>
}
</script>
<body onLoad="fLoad()" bgcolor="#fafafa">
<form name="form1" action="<?=$_SERVER['PHP_SELF']?>" method="post">
<input type="hidden" name="status">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="id_menu" value="20170800000001">
<input type="hidden" name="kd_menu_button" value="<?=$kd_menu_button?>">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<tr><td class="judul_menu" align="center"><?=strtoupper($nama_button)?></td></tr>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
                <tr bgcolor="efefef">
                    <td style="padding:0 5 0 5" width="20%">No ID</td>
                    <td style="padding:0 5 0 5" width="30%"><input name="no_id" type="text" class='groove_text ' size="20" id="no_id" value="<?=$no_id?>" onChange="fGetKTPData()"></td>
                    
                    <td style="padding:0 5 0 5" width="20%">No CIF</td>
                    <td style="padding:0 5 0 5" width="30%"><input name="no_cif" type="text" class='groove_text ' size="20" id="no_cif" value="<?=$no_cif?>" onChange="fGetCIFData()"></td>
                </tr>
                
                <tr bgcolor="efefef">
                    <td style="padding:0 5 0 5" width="20%">Nama Customer</td>
                    <td style="padding:0 5 0 5" width="30%"><input name="nm_customer" type="hidden" class='groove_text ' size="20" id="nm_customer" value="<?=$nm_customer?>"><span id="divnm_customer"><?=$nm_customer?></span></td>
                    <td style="padding:0 5 0 5" width="20%"></td>
                    <td style="padding:0 5 0 5" width="30%"></td>
                </tr>
                
            </table>

<!-- end content begin -->
	  	</td>
    </tr>
</table>

<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" align="center">
	<tr height="20">
    	<td height="25" align="center" bgcolor="#D0E4FF">
            <input class="groove_button" type='button' name="btngenerate" value='Cetak' onClick="fDownload()">
            <input class="groove_button" type='button' name="btngenerate" value='Nasabah Baru' onClick="fModal('new')">
<!--            <input class="groove_button" type='button' name="btngenerate" value='OCR' onClick="fModal('ocr')">
-->            
            <input class="groove_button" type='button' name="btngenerate" value='Upload ID' onClick="fModal('upload_id')">
<!--            <input class="groove_button" type='button' name="btngenerate" value='Upload FOTO' onClick="fModal('upload_foto')">
-->		</td>
	</tr>
</table>
</form>
</body>
</html>
<?
//}
function download(){
	global $id_edit,$upload_path,$strmsg,$no_cif,$no_fatg;
	$l_success=1;
	pg_query("BEGIN");
	
	$query="select nextserial_fatg('FATG':: text, '".$_SESSION["kd_cabang"]."')";
	$lrow=pg_fetch_array(pg_query($query));
	$no_fatg=$lrow["nextserial_fatg"];	
	
	if(!pg_query("insert into data_gadai.tblfatg (no_fatg,fk_cabang,fk_cif) values('".$no_fatg."','".$_SESSION["kd_cabang"]."','".$no_cif."')")) $l_success=0;
	//showquery("insert into data_gadai.tblfatg (no_fatg,fk_cabang,fk_cif) values('".$no_fatg."','".$_SESSION["kd_cabang"]."','".$no_cif."')");

	if(!pg_query("insert into data_gadai.tblfatg_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from data_gadai.tblfatg where no_fatg='".$no_fatg."'")) $l_success=0;

	//$l_success=0;
	if ($l_success==1){
		//$strmsg="Data saved.<br>";
		//$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		header("location:print/print_permohonan_kredit.php?no_cif=".$no_cif."&id_edit=".$no_fatg."&no_fatg=".$no_fatg);
		//header("location:print/print_survey.php?no_cif=".$no_cif."&no_fatg=".$no_fatg."");
		pg_query("COMMIT");	
	}else{
		$strmsg="Error :<br>Save failed.<br>";
		pg_query("ROLLBACK");
	}
	
	
	
}
function cek_error(){
	global $strmsg,$j_action,$periode_awal,$periode_akhir2,$no_cif;
	
	if($no_cif == '') {
		$strmsg.="No CIF Harus Diisi.<br>";
	}
	else if(!pg_num_rows(pg_query("select * from tblcustomer where no_cif='".$no_cif."' "))){
		$strmsg.="No CIF ".$no_cif.", tidak ada.<br>";
		$j_action="document.form1.no_cif.focus()";
	}
	
	if(pg_num_rows(pg_query("select * from tblcustomer where no_cif='".$no_cif."' and  pic_id is NULL "))){
		$strmsg.="No CIF ".$no_cif.", belum Upload Foto ID.<br>";
		$j_action="document.form1.no_cif.focus()";
	}
	
	
	if($strmsg)$strmsg="Error :<br>".$strmsg;
}

?>