<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/file.inc.php';
require 'requires/db_utility.inc.php';
require 'classes/excel.class.php';
require 'classes/excel_reader.class.php';
//check_right("10121113");
require 'requires/validate.inc.php';
set_time_limit(0);

$fileUpload = $_FILES["fileUpload"];

if($_REQUEST["status"]=="Save") {
	cek_error();
	if(!$strmsg){
		add_data();
	}
}else{
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

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")) {
		lInputClose=getObjInputClose();lInputClose.close()
	}
}

function fSave(pObj){
	if( pObj.value=='Download' ){
		document.form1.status.value='Download';
		document.form1.submit();
	}else if( pObj.value=='Upload' && cekError() ) {
		document.form1.status.value='Save';
		document.form1.submit();
	}
}

function fLoad(){
	//parent.parent.document.title="Upload Part";
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
				<td align="center" class="judul_menu">UPLOAD BATCH FINTECH(.xls)</td>
			</table>		
		</td>
	</tr>
    <tr>
		<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<tr bgcolor="#efefef">
					<td width="20%" style="padding:0 5 0 5" class="fontColor">Path </td>
					<td width="30%" style="padding:0 5 0 5" colspan="3"><input type="file" name="fileUpload" class="groove_text" accept=".xls"></td>
					<td width="50%" style="padding:0 5 0 5" class="fontColor"  colspan="2">Format => .xls , Header => No SBG, No Batch </td>
				</tr>
			</table>
<!-- end content begin -->
		</td>
    </tr>
	<tr><td height="25" align="center" bgcolor="#D0E4FF" class="border">
		<input type="button" class="groove_button" name="btnsimpan" value="Upload" onClick="fSave(this)">
		&nbsp;<input type="button" class="groove_button" value="Batal" onClick="fBatal()"></td>
	</tr>
</table>
</form>
</body>
</html>
<?
}
//cek error file
function cek_error(){
	global $fileUpload,$strmsg,$j_action;
	
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
			echo "<b>File terupload . . . .<br>Insert/update database<br>----------------------<br></b>";
			$l_success = update_database($path);
	}

}
//=============================================================
//update database
function update_database($file){
	global $fileUpload,$nama_file,$ext_file,$index,$record_gagal,$alasan_reject,$fk_partner;
	//access ke excel
	echo "Open connection to excel<br>";
	
	$array_excel = new Spreadsheet_Excel_Reader($file); //panggil class
	$total_data = $array_excel->rowcount($sheet_index=0) ;//total data
	
	//echo $file;
	echo "Connected to excel<br>----------------------<br>";
	//=====================================================
	$l_success=1;
	pg_query("BEGIN");
	echo "Start insert/update database<br>---------------------------------<br><br>";
	$index=1;
	//echo $total_data;
	
	for($i=2;$i<=$total_data;$i++){
		$lrow=array(
				'fk_sbg'=>$array_excel->val($i,'A'),
				'no_batch'=>$array_excel->val($i,'B'),
			);
/*		$lrow["fk_sbg"]=preg_replace("/\s|&nbsp; /",'',$lrow["fk_sbg"]);
		if(!is_numeric(substr($lrow["fk_sbg"],0,1))){
			$lrow["fk_sbg"]=substr($lrow["fk_sbg"],1);
			//echo "--".$lrow["fk_sbg"].'--';
		}	*/			
		
		//print_r($lrow);
		$index++;
		
		//showquery("select * from data_fa.tblfintech_ar where fk_sbg='".$lrow["fk_sbg"]."' and tgl_verifikasi_ar is null");
		if(!pg_num_rows(pg_query("select * from data_fa.tblfintech_ar where fk_sbg='".$lrow["fk_sbg"]."' and tgl_verifikasi_ar is null"))){
			echo "No SBG ".$lrow["fk_sbg"]." baris ".($i-1)." tidak ada di fintech atau sudah di verifikasi<br>";
		}	
		else{
			$p_table="data_fa.tblfintech_ar";
			$p_where=" fk_sbg='".$lrow["fk_sbg"]."'";
		
			if(!pg_query(insert_log($p_table,$p_where,'UB')))$l_success=0;
			//showquery("update ".$p_table." set no_batch='".$lrow["no_batch"]."', tgl_terima_ar='".today_db."' where ".$p_where);
			if(!pg_query("update ".$p_table." set no_batch='".$lrow["no_batch"]."', tgl_terima_ar='".today_db."' where ".$p_where))$l_success=0;
			if(!pg_query(insert_log($p_table,$p_where,'UA')))$l_success=0;
		}
		
	}
		
	//echo "<br><br>Connection Closed<br>";
	echo "<br>----------------------------------<br>";
	//$l_success=0;
	if ($l_success==1){
		pg_query("COMMIT");
		exit('<br>PENGINPUTAN DATA SUKSES');
	}else{
		pg_query("ROLLBACK");
		exit('<br>PENGINPUTAN DATA GAGAL');
	}
}
//=============================================================
?>
