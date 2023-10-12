<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/numeric.inc.php';
require 'requires/timestamp.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/file.inc.php';
require 'classes/select.class.php';


$id_edit=$_REQUEST["id_edit"];

$upload_ktp = $_FILES["upload_ktp"];
$upload_surat = $_FILES["upload_surat"];
$upload_lain = $_FILES["upload_lain"];

$file1=$_REQUEST["file1"];
$file2=$_REQUEST["file2"];
$file3=$_REQUEST["file3"];


if($_REQUEST["status"]=="Save") {
	cek_error();
	if(!$strmsg){
		if ($id_edit) edit_data();
		else add_data();
	}
}
//if($_REQUEST["pstatus"]){
	get_data();
//}
?>
<html>
<head>
	<title>.: ASCO MSIS :.</title>
     <link href="css/text.css.php" rel="stylesheet" type="text/css">
	<link href="css/cwcalendar.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src="js/alert.js.php"></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src='js/calendar.js.php'></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src='js/input_format_number.js.php'></script>
<script language='javascript' src='js/validate.js.php'></script>
<script language='javascript'>
function fModal(pType,pID){
	//confirm(pID)
	l_obj_function=function (){
		with(document.form1){
		}
	}
	switch (pType){
		case "upload":
			show_modal('upload/pdf/'+pID,'dialogwidth:825px;dialogheight:545;',l_obj_function)
		break;
	}
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")){
		lInputClose=getObjInputClose();lInputClose.close()
	}
}
function fSave(){
	document.form1.status.value='Save';
	document.form1.submit();
}
function fLoad(){
<?
	
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	} elseif($j_action){
		echo $j_action;
	}else{
		echo "document.form1.btnsimpan.focus();";
	}
?>
}
</script>
<body onLoad="fLoad();document.form1.autocomplete='off'" bgcolor="#fafafa">
<form name="form1" action="modal_upload_dok_pendukung.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="file1" value="<?=$file1?>">
<input type="hidden" name="file2" value="<?=$file2?>">
<input type="hidden" name="file3" value="<?=$file3?>">
<input type="hidden" name="status">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<td align="center" class="judul_menu">UPLOAD TANDA TANGAN</td>
			</table>		
		</td>
	</tr>
    <tr>
		<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
            	<tr bgcolor="#aaafff"><td colspan="4" height="1"></td></tr>
            	<tr bgcolor="efefef">
					<td style="padding:0 5 0 5" width="20%">Upload KTP</td>
					<td style="padding:0 5 0 5" width="30%"><input type="file" name="upload_ktp" value="<?=convert_html($upload_ktp)?>" onKeyUp="fNextFocus(event,document.form1.upload_ktp)" accept=".pdf"></td>
					<td style="padding:0 5 0 5" width="20%">Download</td>
					<td style="padding:0 5 0 5" width="30%"><a href="#" class="blue" onClick="fModal('upload','<?=$file1?>')"><?=convert_html($file1)?></a></td>
                    
                </tr>
            	<tr bgcolor="efefef">
					<td style="padding:0 5 0 5" width="20%">Upload Surat Permohonan</td>
					<td style="padding:0 5 0 5" width="30%"><input type="file" name="upload_surat" value="<?=convert_html($upload_surat)?>" onKeyUp="fNextFocus(event,document.form1.upload_surat)" accept=".pdf"></td>
					<td style="padding:0 5 0 5" width="20%">Download</td>
					<td style="padding:0 5 0 5" width="30%"><a href="#" class="blue" onClick="fModal('upload','<?=$file2?>')"><?=convert_html($file2)?></a></td>
                    
                </tr>
            	<tr bgcolor="efefef">
					<td style="padding:0 5 0 5" width="20%">Upload Lainnya</td>
					<td style="padding:0 5 0 5" width="30%"><input type="file" name="upload_lain" value="<?=convert_html($upload_lain)?>" onKeyUp="fNextFocus(event,document.form1.upload_lain)" accept=".pdf"></td>
					<td style="padding:0 5 0 5" width="20%">Download</td>
					<td style="padding:0 5 0 5" width="30%"><a href="#" class="blue" onClick="fModal('upload','<?=$file3?>')"><?=convert_html($file3)?></a></td>
                    
                </tr>
                
            </table>
<!-- end content begin -->
    	</td>
    </tr>
	<tr><td height="25" align="center" bgcolor="#D0E4FF" class="border">
		<input type="button" class="groove_button" name="btnsimpan" value="Simpan" onClick="fSave()">
		&nbsp;<input type="button" class="groove_button" value="Batal" onClick="fBatal()"></td>
	</tr>
</table>
</form>
</body>
</html>
<?
function cek_error(){
	global $id_edit,$strmsg,$j_action,$fileUpload,$nm_brosur;
		
	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}

function edit_data(){
	global $id_edit,$j_action,$strmsg,$id_edit,$upload_ktp,$upload_surat,$upload_lain,$upload_path_pdf;
	
	$l_success=1;
	pg_query("BEGIN");
	
	//log begin
	if(!pg_query("insert into data_gadai.tblpengaduan_customer_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from data_gadai.tblpengaduan_customer where no_pengaduan='".$id_edit."'")) $l_success=0;
	//end log
	
	$file1="dok1_".$id_edit.".pdf";
	$file2="dok2_".$id_edit.".pdf";
	$file3="dok3_".$id_edit.".pdf";

	$dir=$upload_path_pdf;
	
	if($_FILES["upload_ktp"]["name"]!=''){		
		if(!pg_query("update data_gadai.tblpengaduan_customer set upload_ktp='".convert_sql($file1)."' where no_pengaduan='".$id_edit."'")) $l_success=0;
		$l_success = move_uploaded_file($_FILES["upload_ktp"]["tmp_name"],$dir.$file1);
	}
	
	if($_FILES["upload_surat"]["name"]!=''){		
		if(!pg_query("update data_gadai.tblpengaduan_customer set upload_surat='".convert_sql($file2)."' where no_pengaduan='".$id_edit."'")) $l_success=0;
		$l_success = move_uploaded_file($_FILES["upload_surat"]["tmp_name"],$dir.$file2);
	}
	if($_FILES["upload_lain"]["name"]!=''){		
		if(!pg_query("update data_gadai.tblpengaduan_customer set upload_lain='".convert_sql($file3)."' where no_pengaduan='".$id_edit."'")) $l_success=0;
		$l_success = move_uploaded_file($_FILES["upload_lain"]["tmp_name"],$dir.$file3);
	}
	
	//log begin
	if(!pg_query("insert into data_gadai.tblpengaduan_customer_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from data_gadai.tblpengaduan_customer where no_pengaduan='".$id_edit."'")) $l_success=0;
	//end log
	//$l_success=0;
	if ($l_success==1){
		$strmsg="Upload Tersimpan.<br>";
		//$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		pg_query("COMMIT");
	}else{
		$strmsg="Error :<br>Upload Gagal Tersimpan.<br>";
		pg_query("ROLLBACK");
	}
}

function add_data(){
	global $j_action,$strmsg,$fileUpload,$upload_path,$fk_cabang,$nm_brosur,$is_active;
	
}
function get_data(){
	global $id_edit,$file1,$file2,$file3;
	
	$lrow=pg_fetch_array(pg_query("
	select * from data_gadai.tblpengaduan_customer
	where no_pengaduan='".$id_edit."'"));
	$file1=$lrow["upload_ktp"];
	$file2=$lrow["upload_surat"];
	$file3=$lrow["upload_lain"];
	
}

?>
