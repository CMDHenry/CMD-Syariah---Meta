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

if($_SESSION["jenis_user"]!='HO'){
   $strmsg="Menu ini hanya bisa diakses oleh HO.<br>";  
   $j_action= "lInputClose=getObjInputClose();lInputClose.close()";
}

$id_menu=trim($_REQUEST["id_menu"]);
get_data_menu($id_menu);	

$bulan = $_REQUEST['bulan'];
$tahun = $_REQUEST['tahun'];

if(!$bulan)$bulan=get_rec("tblsetting","bulan_accounting");
if(!$tahun)$tahun=get_rec("tblsetting","tahun_accounting");

$jurnal = $_REQUEST['jurnal'];

if($_REQUEST["status"]=="Save") {
	//cek_error();
	if(!$strmsg){
		save_data();
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
<script language='javascript' src="js/input_format_number.js.php"></script>

<script language='javascript'>

function fDownload(pValue){
	document.form1.status.value='Save';
	document.form1.jurnal.value='CKPN';
	document.form1.submit();
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")){
		lInputClose=getObjInputClose();lInputClose.close()
	}
}


function fLoad(){
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	} elseif($j_action){
		echo $j_action;
	}else{
		echo "document.form1.jurnal.focus();";
	}
?>
}
</script>
<body onLoad="fLoad()" bgcolor="#fafafa">
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="form1" id="form1" enctype="multipart/form-data" onSubmit="return fCheckSubmit()">
<input type="hidden" name="status">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="jurnal" value="<?=$jurnal?>">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<tr><td class="judul_menu" align="center">AUTO JURNAL AKHIR BULAN</td></tr>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
<!--                <tr bgcolor="efefef">
                      <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef">Periode</td>
                      <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                        <select name="bulan" class="groove_text" id="bulan" onChange="">
                            <option value="01" <?=(($bulan=="01")?"selected":"")?>>01</option>
                            <option value="02" <?=(($bulan=="02")?"selected":"")?>>02</option>
                            <option value="03" <?=(($bulan=="03")?"selected":"")?>>03</option>
                            <option value="04" <?=(($bulan=="04")?"selected":"")?>>04</option>
                            <option value="05" <?=(($bulan=="05")?"selected":"")?>>05</option>
                            <option value="06" <?=(($bulan=="06")?"selected":"")?>>06</option>
                            <option value="07" <?=(($bulan=="07")?"selected":"")?>>07</option>
                            <option value="08" <?=(($bulan=="08")?"selected":"")?>>08</option>
                            <option value="09" <?=(($bulan=="09")?"selected":"")?>>09</option>
                            <option value="10" <?=(($bulan=="10")?"selected":"")?>>10</option>
                            <option value="11" <?=(($bulan=="11")?"selected":"")?>>11</option>
                            <option value="12" <?=(($bulan=="12")?"selected":"")?>>12</option>
                        </select>
                        <input type="text" name="tahun" class="groove_text" size="4" id="tahun" value="<?=$tahun?>" onChange="">
                       
                      </td>
                      <td width="20%" style="padding:0 5 0 5"></td>
                      <td width="30%" style="padding:0 5 0 5"></td>
                 </tr> 	-->
            </table>

<!-- end content begin -->
	  	</td>
    </tr>
</table>

<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" align="center">
	<tr height="20">
    	<td height="25" align="center" bgcolor="#D0E4FF">
<!--            <input class="groove_button" type='button' name="btnGenerate" value='Simpan' onClick="fDownload()">
-->            
			<input class="groove_button" type='button' name="btnGenerate" value='CKPN' onClick="fDownload('ckpn')">
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

}

function save_data(){
	global $strmsg, $j_action,$id_edit,$__url,$bulan,$tahun,$jurnal;
	$l_success=1;
	pg_query("BEGIN");
	
	if($_SERVER['DOCUMENT_ROOT']=="D:/Development/Web Project"){
		$url_folder="/capella";
		//echo $_SERVER['DOCUMENT_ROOT'];
	}else $url_folder="";
	echo $jurnal;
	if($jurnal=='CKPN'){
		include 'auto_cadangan_piutang.php';
	}

	//$l_success=0;
	if ($l_success==1){
		$strmsg="Data berhasil tersimpan.<br>";
		$j_action="lInputClose=getObjInputClose();lInputClose.close();";
		pg_query("COMMIT");		
	}else{
		$strmsg="Error :<br>Data gagal tersimpan.<br>";
		pg_query("ROLLBACK");
	}
}

function cek_error(){
	global $strmsg,$j_action,$periode_awal,$periode_akhir2,$fk_sbg,$nilai;

	if($strmsg)$strmsg="Error :<br>".$strmsg;
}

?>