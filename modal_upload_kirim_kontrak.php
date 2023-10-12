<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/file.inc.php';
require 'requires/db_utility.inc.php';
require 'classes/excel.class.php';
require 'classes/excel_reader.class.php';
require 'requires/validate.inc.php';
require 'requires/timestamp.inc.php';
set_time_limit(0);

$fileUpload = $_FILES["fileUpload"];

$fk_partner=($_REQUEST["fk_partner"]);

if($_REQUEST["status"]=="Save") {
	cek_error();
	if(!$strmsg){
		//echo $fk_partner;
		add_data();
	}
}elseif($_REQUEST["status"]=="Download"){
	create_excel();
}
?>
<html>
<head>
	<title>.: <?=$_SESSION["application"]?> :.</title>
    <link href="text.css.php" rel="stylesheet" type="text/css">
<head>
</head>
<script language='javascript' src="js/alert.js.php"></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src='js/calendar.js.php'></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src='js/validate.js.php'></script>
<script language='javascript'>
function cekError(){
	var lAlerttxt="";
	var lFocuscursor="";

	with(document.form1){
		if(fileUpload.value==""){
			lAlerttxt+='Path Kosong<br>';
			if(lFocuscursor==""){lFocuscursor="document.form1.fileUpload";}
		}
	}
	if(lAlerttxt!=""){
		alert("Error : <br>"+lAlerttxt,function(){eval(lFocuscursor+'.focus()')});
		return false
	} else return true;
}


function fGetCabang(){
	fGetNC(false,"20170900000010","fk_cabang_terima","Ganti CABANG",document.form1.fk_cabang_terima,document.form1.fk_cabang_terima)
}

function fGetCabangData(){
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataCabangState
	lSentText="table= tblcabang&field=(nm_cabang)&key=kd_cabang&value="+document.form1.fk_cabang_terima.value
	lObjLoad.open("POST","ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}

function fGetDataCabangState(){
	if (this.readyState == 4){
		//confirm(this.responseText)
		if (this.status==200 && this.responseText!="") {
			lTemp=this.responseText.split("¿");
			document.getElementById("divNmCabang").innerHTML=document.form1.nm_cabang.value=lTemp[0]
		} else {
			document.getElementById("divNmCabang").innerHTML=document.form1.nm_cabang.value="-"
		}
	}
}


function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")) {
		lInputClose=getObjInputClose();lInputClose.close()
	}
}

function fSave(pObj){
	if( pObj.value=='Download Template' ){
		document.form1.status.value='Download';
		document.form1.submit();
	}else if( pObj.value=='Upload' && cekError() ) {
		document.form1.status.value='Save';
		document.form1.submit();
	}
}


function fLoad(){
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	}else if($j_action){
		echo $j_action;
	}else{
		echo "document.form1.fileUpload.focus();";
	}
?>
}
</script>
<body onLoad="fLoad()" bgcolor="#fafafa">
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="form1" enctype="multipart/form-data">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="status">

<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<td align="center" class="judul_menu">UPLOAD TGL KIRIM KONTRAK(.xls)</td>
			</table>		
		</td>
	</tr>
    <tr>
		<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">            
            <tr bgcolor="#efefef">
                <td width="20%" style="padding:0 5 0 5" class="fontColor">Path</td>
                <td width="80%" style="padding:0 5 0 5" colspan="3"><input type="file" name="fileUpload" class="groove_text" accept=".xls" ></td>
            </tr>
			</table>
<!-- end content begin -->
		</td>
    </tr>
	<tr><td height="25" align="center" bgcolor="#D0E4FF" class="border">
		<input type="button" class="groove_button" name="btnsimpan" value="Upload" onClick="fSave(this)">
        &nbsp;<input type="button" class="groove_button" value="Download Template" onClick="fSave(this)">
	</tr>
</table>
</form>
</body>
</html>
<?

//cek error file
function cek_error(){
	global $fileUpload,$strmsg,$j_action,$penerima,$fk_cabang_terima,$fk_partner;
	
	if($fileUpload['name']==""){
		$strmsg.="File Upload Error";
		if(!$j_action) $j_action="document.form1.fileUpload.focus()";
	}
	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}
//=============================================================
//upload excel ke server
function add_data(){
	global $fileUpload,$strmsg,$j_action,$upload_path;

	$l_success=1;
	$path=$upload_path.'/temp/'.$fileUpload["name"];
	if($fileUpload['name']!=""){
		if(file_exists($path))unlink($path);
			$l_success = move_uploaded_file($_FILES["fileUpload"]["tmp_name"],$path);
			echo "<b>File terupload . . . .<br></b>";
			$l_success = update_database($path);
	}

}
//=============================================================
//update database
function update_database($file){
	global $fileUpload,$fk_cabang_terima,$ext_file,$index,$fk_cabang_kirim,$penerima,$fk_partner;
	//access ke excel
	
	$array_excel = new Spreadsheet_Excel_Reader($file); //panggil class
	$total_data = $array_excel->rowcount($sheet_index=0) ;//total data
	$l_success=1;
	pg_query("BEGIN");
	echo "<font size='-1'>Start insert/update database<br>---------------------------------<br><br>";
	$index=1;

	for($i=2;$i<=$total_data;$i++){
		$lrow=array(
				'fk_sbg'=>$array_excel->val($i,'A'),
				'tgl_pengiriman_kontrak'=>$array_excel->val($i,'B'),
			);
		$lrow["fk_sbg"]=preg_replace('/[^A-Za-z0-9-.; \-]/', '', $lrow["fk_sbg"]);
		
		
		if($lrow["tgl_pengiriman_kontrak"]){
			if(!validate_date(convert_date_english($lrow["tgl_pengiriman_kontrak"]))){
				echo "Format Tgl ".$lrow["fk_sbg"]."  harus d/m/Y <br>";
				$l_success=0;
			}
		}
		
		if(!pg_query("update data_gadai.tblproduk_cicilan set tgl_pengiriman_kontrak=".(($lrow["tgl_pengiriman_kontrak"]=="")?"null":"'".convert_date_english($lrow["tgl_pengiriman_kontrak"])."'")." where no_sbg='".$lrow["fk_sbg"]."'")) $l_success=0;
		
		//showquery("update data_gadai.tblproduk_cicilan set tgl_pengiriman_kontrak=".(($lrow["tgl_pengiriman_kontrak"]=="")?"null":"'".convert_sql($lrow["tgl_pengiriman_kontrak"])."'")." where no_sbg='".$lrow["fk_sbg"]."'");
		
		//log begin
		if(!pg_query("insert into data_gadai.tblproduk_cicilan_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from data_gadai.tblproduk_cicilan where no_sbg='".$lrow["fk_sbg"]."'")) $l_success=0;
		//end log
		
	}

	
	//echo "<br><br>Connection Closed<br>";
	echo "<br>----------------------------------<br>";
	//$l_success=0;
	if ($l_success==1){
		pg_query("COMMIT");
		echo('<br>DATA SUKSES');
	}else{
		pg_query("ROLLBACK");
		echo('<br>DATA GAGAL');
	}
}
//=============================================================
function create_excel(){
	global $upload_path,$penerima;
	$xls = new XLS("tgl_kirim_kontrak");

	//header excel
	$xls->xlsWriteLabel(0,0,"No.Kontrak");	
	$xls->xlsWriteLabel(0,1,"Tanggal Kirim");	
	
	//===============================================
	
	$xls->xlsOutput($upload_path,"temp");
	echo "Klik untuk men-download<br>";
	echo "<a href='file/temp/tgl_kirim_kontrak.xls'>TANGGAL KIRIM KONTRAK</a><br>";
}
?>

