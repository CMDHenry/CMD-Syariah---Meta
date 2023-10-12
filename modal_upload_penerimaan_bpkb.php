<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/file.inc.php';
require 'requires/db_utility.inc.php';
require 'classes/excel.class.php';
require 'classes/excel_reader.class.php';
require 'requires/timestamp.inc.php';
require 'requires/validate.inc.php';
set_time_limit(0);

$fileUpload = $_FILES["fileUpload"];

$fk_partner=($_REQUEST["fk_partner"]);
$nm_partner=($_REQUEST["nm_partner"]);
$tgl_terima_bpkb = convert_date_english(($_REQUEST["tgl_terima_bpkb"]));

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
	<link href="css/cwcalendar.css.php" rel="stylesheet" type="text/css">
    
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
		if(tgl_terima_bpkb.value==""){
			lAlerttxt+='Tanggal Kosong<br>';
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
				<td align="center" class="judul_menu">UPLOAD(.xls)</td>
			</table>		
		</td>
	</tr>
    <tr>
		<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<tr bgcolor="#efefef">
					<td width="20%" style="padding:0 5 0 5" class="fontColor">Path</td>
					<td width="30%" style="padding:0 5 0 5" colspan="3"><input type="file" name="fileUpload" class="groove_text" accept=".xls"></td>
					<td style="padding:0 5 0 5"width="20%" class="fontColor">Tgl Terima Dari Dealer</td>
					<td style="padding:0 5 0 5"width="30%"><input type="text" value="<?=convert_date_indonesia($tgl_terima_bpkb)?>" name="tgl_terima_bpkb" maxlength="10" size="8" onKeyUp="fNextFocus(event,document.form1.description)" onChange="fNextFocus(event,document.form1.description)">&nbsp;<img src="images/btn_extend.gif" width="13" height="12" onClick="fPopCalendar(document.form1.tgl_terima_bpkb,function(){document.form1.tgl_terima_bpkb.focus()})"></td>                    </tr>
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
	global $fileUpload,$strmsg,$j_actio,$tgl_terima_bpkb;
	
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
	global $fileUpload,$nama_file,$ext_file,$index,$record_gagal,$tgl_terima_bpkb,$fk_partner;
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
				'no_bpkb'=>$array_excel->val($i,'B'),
				'nm_bpkb'=>$array_excel->val($i,'C'),
				'tgl_bpkb'=>$array_excel->val($i,'D'),
			);
			
		$lrow["tgl_bpkb"]=str_replace("/","zz",$lrow["tgl_bpkb"]);
		$lrow["fk_sbg"]=preg_replace("/[^A-Za-z0-9]/", "",$lrow["fk_sbg"]);
		$lrow["no_bpkb"]=preg_replace("/[^A-Za-z0-9]/", "",$lrow["no_bpkb"]);
		$lrow["nm_bpkb"]=preg_replace("/[^A-Za-z0-9]/", "",$lrow["nm_bpkb"]);
		$lrow["tgl_bpkb"]=preg_replace("/[^A-Za-z0-9]/", "",$lrow["tgl_bpkb"]);
		$lrow["tgl_bpkb"]=str_replace("zz","/",$lrow["tgl_bpkb"]);

		
		if (!pg_num_rows(pg_query("select * from tblinventory where fk_sbg = '".$lrow["fk_sbg"]."' "))){
			echo "No Kontrak salah di ".$lrow['fk_sbg']."<br>";$l_success=0;
		} 
		
		if(!$lrow["no_bpkb"]){
			echo "No BPKB kosong ".$lrow['fk_sbg']."<br>";$l_success=0;
		}
		if(!$lrow["nm_bpkb"]){
			echo "Nama BPKB kosong ".$lrow['fk_sbg']."<br>";$l_success=0;
		}
		if(!$lrow["tgl_bpkb"]){
			echo "Tgl BPKB kosong ".$lrow['fk_sbg']."<br>";$l_success=0;
		}elseif(!validate_date($lrow["tgl_bpkb"])){
			echo "Tgl BPKB salah format ".$lrow['fk_sbg']."<br>";$l_success=0;
		}
		
		$tbl="data_gadai.tbltaksir_umum";
		$lwhere="no_sbg_ar='".$lrow['fk_sbg']."'";
		
		if (pg_num_rows(pg_query("select * from ".$tbl." where ".$lwhere." and tgl_terima_bpkb is not null "))){
			echo "No Kontrak sudah terima ".$lrow['fk_sbg']."<br>";$l_success=0;
		} 		
		
		$posisi_bpkb='Cabang';
		if(!pg_query(insert_log($tbl,$lwhere,'UB'))) $l_success=0;
		if(!pg_query("update ".$tbl." SET posisi_bpkb='".$posisi_bpkb."',tgl_terima_bpkb='".$tgl_terima_bpkb."',no_bpkb='".$lrow["no_bpkb"]."' ,nm_bpkb='".$lrow["nm_bpkb"]."',tgl_bpkb='".$lrow["tgl_bpkb"]."' where ".$lwhere."")) $l_success=0;
		//showquery("update ".$tbl." SET posisi_bpkb='".$posisi_bpkb."',tgl_terima_bpkb='".$tgl_terima_bpkb."',no_bpkb='".$lrow["no_bpkb"]."' ,nm_bpkb='".$lrow["nm_bpkb"]."',tgl_bpkb='".$lrow["tgl_bpkb"]."' where ".$lwhere."");
		if(!pg_query(insert_log($tbl,$lwhere,'UA'))) $l_success=0;
		
		$index++;
		
	}
		

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

function create_excel(){
	global $upload_path,$penerima;
	$xls = new XLS("bpkb_penerimaan");

	//header excel
	$xls->xlsWriteLabel(0,0,"No.Kontrak");	
	$xls->xlsWriteLabel(0,1,"NO BPKB");	
	$xls->xlsWriteLabel(0,2,"Nama BPKB");	
	$xls->xlsWriteLabel(0,3,"TGL BPKB (Format MM/DD/YYYY)");	
	
	//===============================================
	
	$xls->xlsOutput($upload_path,"temp");
	echo "Klik untuk men-download<br>";
	echo "<a href='file/temp/bpkb_penerimaan.xls'>BPKB_PENERIMAAN</a><br>";
}

?>
