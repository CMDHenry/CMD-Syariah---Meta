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

$fk_partner=($_REQUEST["fk_partner"]);
$nm_partner=($_REQUEST["nm_partner"]);

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

function fGetPartner(){
	fGetNC(false,'20170900000044','fk_partner','Ganti Lokasi',document.form1.fk_partner,document.form1.fk_partner)
    if (document.form1.fk_partner.value !="")fGetPartnerData()	
}

function fGetPartnerData(){
	
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataPartnerState
	lSentText="table=(select kd_partner, nm_partner from tblpartner) as tblmain&field=(nm_partner)&key=kd_partner&value="+document.form1.fk_partner.value
	lObjLoad.open("POST","ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}
function fGetDataPartnerState(){	
	if (this.readyState == 4){
		//confirm(this.responseText)
		if (this.status==200 && this.responseText!="") {
			lTemp=this.responseText.split('¿');
			document.getElementById('divnm_partner').innerHTML=document.form1.nm_partner.value=lTemp[0]
		} else {
			document.getElementById('divnm_partner').innerHTML=document.form1.nm_partner.value="-"
		}
	}
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
				<td align="center" class="judul_menu">UPLOAD BUDGET(.xls)</td>
			</table>		
		</td>
	</tr>
    <tr>
		<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<tr bgcolor="#efefef">
					<td width="20%" style="padding:0 5 0 5" class="fontColor">Path</td>
					<td width="80%" style="padding:0 5 0 5" colspan="3"><input type="file" name="fileUpload" class="groove_text" accept=".xls"></td>
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
				'bulan'=>$array_excel->val($i,'A'),
				'tahun'=>$array_excel->val($i,'B'),
				'fk_cabang'=>$array_excel->val($i,'C'),
				'target_os'=>$array_excel->val($i,'D'),
				'target_sales'=>$array_excel->val($i,'E'),
			);
			
			//echo 'tedst';
		if($i==2){	
		
			$kd_target = $lrow["bulan"].'-'.$lrow["tahun"];
		
			if (pg_num_rows(pg_query("select * from tbltarget where bulan = '".$lrow["bulan"]."' and  tahun = '".$lrow["tahun"]."'"))){
				echo "Data Budget Periode ".($kd_target)." sudah di-upload<br>";$l_success=0;
			} 
			else if(!is_numeric($lrow["target_os"])){
				echo "Format Target OS Harus Angka<br>";$l_success=0;
			}
			else if(!is_numeric($lrow["target_os"])){
				echo "Format Target Sales Harus Angka<br>";$l_success=0;
			}
			else {	
				if(!pg_query("insert into tbltarget(kd_target,bulan,tahun) values('".$kd_target."','".$lrow["bulan"]."','".$lrow["tahun"]."')")) $l_success=0;	
			
			//log
				if(!pg_query("insert into tbltarget_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from tbltarget where kd_target='".$kd_target."'")) $l_success=0;
			//end log
				$l_id_log_ia=get_last_id("tbltarget_log","pk_id_log");
			}
		}
	

		if (pg_num_rows(pg_query("select * from tbltarget_detail where fk_cabang = '".$lrow["fk_cabang"]."' and  fk_target = '".$kd_target."'"))){
				echo "Data Budget Periode'".($kd_target)."' dengan Kode Cabang '".($lrow["fk_cabang"])."' sudah ada<br>";$l_success=0;
		} 
		else {
			if(!pg_query("insert into tbltarget_detail(fk_target,fk_cabang,target_os,target_sales) 
			values('".$kd_target."','".$lrow["fk_cabang"]."','".$lrow["target_os"]."','".$lrow["target_sales"]."')")) $l_success=0;
		}
			if(!is_numeric($lrow["target_os"]))
			echo "Format Target OS Harus Angka<br>";
			
			if(!is_numeric($lrow["target_sales"]))
			echo "Format Target Sales Harus Angka<br>";

		$index++;
		
	}
		
	//log begin
	$l_id_log_ia=get_last_id("tbltarget_log","pk_id_log");
	if(!pg_query("insert into tbltarget_detail_log select *,'".$l_id_log_ia."' from tbltarget_detail where fk_target='".$kd_target."'")) $l_success=0;
	//end log	


	//end log
	
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
