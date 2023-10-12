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
$tgl_serah_terima_bpkb = convert_date_english(($_REQUEST["tgl_serah_terima_bpkb"]));
$bpkb_diperiksa =($_REQUEST["bpkb_diperiksa"]);
$bpkb_diserahkan =($_REQUEST["bpkb_diserahkan"]);

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
		if(tgl_serah_terima_bpkb.value==""){
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
					<td width="25%" style="padding:0 5 0 5" class="fontColor">Path</td>
					<td width="25%" style="padding:0 5 0 5" colspan="3"><input type="file" name="fileUpload" class="groove_text" accept=".xls"></td>
					<td style="padding:0 5 0 5"width="20%" class="fontColor">Tgl Penyerahan</td>
					<td style="padding:0 5 0 5"width="30%"><input type="text" value="<?=convert_date_indonesia($tgl_serah_terima_bpkb)?>" name="tgl_serah_terima_bpkb" maxlength="10" size="8" onKeyUp="fNextFocus(event,document.form1.description)" onChange="fNextFocus(event,document.form1.description)">&nbsp;<img src="images/btn_extend.gif" width="13" height="12" onClick="fPopCalendar(document.form1.tgl_serah_terima_bpkb,function(){document.form1.tgl_serah_terima_bpkb.focus()})"></td>
                </tr>
                <tr bgcolor="#efefef">
					<td width="25%" class="fontColor" style="padding:0 5 0 5">&nbsp;</td>
					<td  width="25%" class="fontColor" style="padding:0 5 0 5" colspan="3">&nbsp;</td>
                    <td width="25%" class="fontColor" style="padding:0 5 0 5">Diperiksa</td>
					<td  width="25%" class="fontColor" style="padding:0 5 0 5" colspan="3">
                    <select name="bpkb_diperiksa" class="groove_text" onKeyUp="fNextFocus(event,document.form1.bpkb_diperiksa)" onChange="fCalc(this)">
                        <option value="">--Diperiksa--</option>
                        <option value="ADH" <?=(($bpkb_diperiksa=="ADH")?"selected":"");?>>ADH</option>
                        <option value="SPV.Finance" <?=(($bpkb_diperiksa=="SPV.Finance")?"selected":"");?>>SPV.Finance</option>
                    </select></td>
					
                </tr>
                <tr bgcolor="#efefef">
					<td width="25%" class="fontColor" style="padding:0 5 0 5">&nbsp;</td>
					<td  width="25%" class="fontColor" style="padding:0 5 0 5" colspan="3">&nbsp;</td>
                    <td  class="fontColor" style="padding:0 5 0 5">Diserahkan</td>
				  	<td  class="fontColor" style="padding:0 5 0 5">
                    <select name="bpkb_diserahkan" class="groove_text" onKeyUp="fNextFocus(event,document.form1.bpkb_diserahkan)" onChange="fCalc(this)">
                        <option value="">--Diserahkan--</option>
                        <option value="ADM.BPKB" <?=(($bpkb_diserahkan=="ADM.BPKB")?"selected":"");?>>ADM.BPKB</option>
                        <option value="KASIR" <?=(($bpkb_diserahkan=="KASIR")?"selected":"");?>>KASIR</option>
                    </select></td>
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
	global $fileUpload,$strmsg,$j_actio,$tgl_serah_terima_bpkb,$bpkb_diperiksa,$bpkb_diserahkan;
	
	if($fileUpload['name']==""){
		$strmsg.="File Upload Error";
		if(!$j_action) $j_action="document.form1.fileUpload.focus()";
	}
	
	if($bpkb_diperiksa==""){
		$strmsg.="Pilih Pemeriksaan.<br>";
		if(!$j_action) $j_action="document.form1.bpkb_diperiksa.focus()";
	}
	
	if($bpkb_diserahkan==""){
		$strmsg.="Pilih Penyerahan.<br>";
		if(!$j_action) $j_action="document.form1.bpkb_diserahkan.focus()";
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
	global $fileUpload,$nama_file,$ext_file,$index,$record_gagal,$tgl_serah_terima_bpkb,$fk_partner,$bpkb_diperiksa,$bpkb_diserahkan;
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
			);
		$lrow["fk_sbg"]=preg_replace("/[^A-Za-z0-9]/", "",$lrow["fk_sbg"]);
		
		if (!pg_num_rows(pg_query("select * from tblinventory where fk_sbg = '".$lrow["fk_sbg"]."' and status_sbg='Exp'"))){
			echo "No Kontrak salah/belum lunas di ".$lrow['fk_sbg']."<br>";$l_success=0;
		} 
		$tbl="data_gadai.tbltaksir_umum";
		$lwhere="no_sbg_ar='".$lrow['fk_sbg']."'";

		if(!pg_query(insert_log($tbl,$lwhere,'UB'))) $l_success=0;
		if(!pg_query("update ".$tbl." SET tgl_serah_terima_bpkb='".$tgl_serah_terima_bpkb."',bpkb_diperiksa='".$bpkb_diperiksa."',bpkb_diserahkan='".$bpkb_diserahkan."',posisi_bpkb='Customer' where ".$lwhere."")) $l_success=0;
		
		
		//showquery("update ".$tbl." SET tgl_serah_terima_bpkb='".$tgl_serah_terima_bpkb."',bpkb_diperiksa='".$bpkb_diperiksa."',bpkb_diserahkan='".$bpkb_diserahkan."',posisi_bpkb='Customer' where ".$lwhere."");
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
	$xls = new XLS("bpkb_penyerahan");

	//header excel
	$xls->xlsWriteLabel(0,0,"No.Kontrak");	
	//===============================================
	
	$xls->xlsOutput($upload_path,"temp");
	echo "Klik untuk men-download<br>";
	echo "<a href='file/temp/bpkb_penyerahan.xls'>BPKB_PENYERAHAN</a><br>";
}

?>
