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

$fk_sbg = $_REQUEST['fk_sbg'];
if(!$_REQUEST['tgl'])$tgl=convert_date_indonesia(today);
else $tgl=$_REQUEST['tgl'];

if($_REQUEST["status"]) {
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

function fDownload(pStatus){
	
	l_obj_function=function (){
		with(document.form1){
		}
	}	
	if(document.form1.fk_sbg.value!='' && document.form1.nm_customer.value!='-'){
		//document.form1.status.value=pStatus;
		//document.form1.submit();	
		show_modal('print/print_'+pStatus+'.php?fk_sbg='+document.form1.fk_sbg.value+'&tgl='+escape(document.form1.tgl.value),'dialogwidth:825px;dialogheight:545px;',l_obj_function)
	}
		
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
	lSentText="table=(select fk_sbg,fk_cif,nm_customer from tblinventory left join tblcustomer on no_cif=fk_cif where tgl_cair is not null) as tblmain&field=(nm_customer||'¿'||fk_cif)&key=fk_sbg&value="+document.form1.fk_sbg.value
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
		} else {
			document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value="-"
			document.getElementById('divno_cif').innerHTML=document.form1.no_cif.value="-"
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
<input type="hidden" name="tipe" value="Cicilan">

<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<tr><td class="judul_menu" align="center">CETAK HISTORIS ANGSURAN</td></tr>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
                        <tr bgcolor="efefef">
                            <td style="padding:0 5 0 5" width="20%">No Kontrak</td>
                            <td style="padding:0 5 0 5" width="30%"><input name="fk_sbg" type="text" class='groove_text ' size="20" id="fk_sbg"  onChange="fGetSbgData()" value="<?=$fk_sbg?>">&nbsp;<img src="images/search.gif" style="border:0px" align="absmiddle" onClick="fGetSbg()"></td>
                            <td style="padding:0 5 0 5" width="20%">No CIF</td>
            				<td style="padding:0 5 0 5" width="30%"><input type="hidden" name="no_cif" class='groove_text' value="<?=$no_cif?>"><span id="divno_cif"><?=$no_cif?></span></td>
                        </tr>
                        
                        <tr bgcolor="efefef">
                            <td style="padding:0 5 0 5" width="20%">Nama Customer</td>
                            <td style="padding:0 5 0 5" width="30%"><input type="hidden" name="nm_customer" class='groove_text' value="<?=$nm_customer?>"><span id="divnm_customer"><?=$nm_customer?></span></td>
                            
                            <td style="padding:0 5 0 5" width="20%">Tanggal</td>
            				<td style="padding:0 5 0 5" width="30%">                        
                            <input type="text" name="tgl" value="<?=convert_date_indonesia($tgl)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_tgl.click();" size="10" >&nbsp;<img src="images/btn_extend.gif" name="img_tgl" onClick="fPopCalendar(document.form1.tgl,document.form1.tgl)">                               
							</td>
                        </tr>
            </table>

<!-- end content begin -->
	  	</td>
    </tr>
</table>

<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" align="center">
	<tr height="20">
    	<td height="25" align="center" bgcolor="#D0E4FF">
            <input class="groove_button" type='button' name="btngenerate" value='Kartu Piutang' onClick="fDownload('kartu_piutang')">
            <input class="groove_button" type='button' name="btngenerate" value='Jadwal Amortisasi' onClick="fDownload('jadwal_amortisasi')">
            <input class="groove_button" type='button' name="btngenerate" value='Historis Denda' onClick="fDownload('historis_denda')">
            <input class="groove_button" type='button' name="btngenerate" value='Simulasi Pelunasan' onClick="fDownload('simulasi_pelunasan')">
            
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?
//}

function get_data(){
	global $id_edit,$strmsg,$j_action,$no_cif,$fk_sbg,$nm_customer;

	$lrow=pg_fetch_array(pg_query("select no_sbg,fk_cif,nm_customer from viewkontrak left join viewtaksir on no_fatg=fk_fatg left join tblcustomer on no_cif=fk_cif  where no_sbg='".$fk_sbg."'"));

	$no_cif=$lrow["fk_cif"];
	$nm_customer=$lrow["nm_customer"];
	
}
function download(){
	global $id_edit,$upload_path,$strmsg,$no_cif,$tgl,$fk_sbg,$j_action;
	$l_success=1;
	pg_query("BEGIN");	
	//$l_success=0;
	if ($l_success==1){
		//$strmsg="Data saved.<br>";
		//$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		header("location:print/print_".$_REQUEST["status"].".php?fk_sbg=".$fk_sbg."&tgl=".convert_date_english($tgl));
		pg_query("COMMIT");	
	}else{
		$strmsg="Error :<br>Save failed.<br>";
		pg_query("ROLLBACK");
	}
	
	
	
}
function cek_error(){
	global $strmsg,$j_action,$periode_awal,$periode_akhir2,$fk_sbg;
	
	if($strmsg)$strmsg="Error :<br>".$strmsg;
}

?>