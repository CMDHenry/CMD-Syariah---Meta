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

$tgl_funding = convert_date_english(trim($_REQUEST["tgl_funding"]));


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
		if(fk_partner.value==""){
			lAlerttxt+='Partner Bank Kosong<br>';
			if(lFocuscursor==""){lFocuscursor="document.form1.fk_partner";}
		}
		if(tgl_funding.value==""){
			lAlerttxt+='Tgl Kosong<br>';
			if(lFocuscursor==""){lFocuscursor="document.form1.tgl_funding";}
		}
		
	}
	if(lAlerttxt!=""){
		alert("Error : <br>"+lAlerttxt,function(){eval(lFocuscursor+'.focus()')});
		return false
	} else return true;
}

function fGetPartner(){
	fGetNC(false,'20170900000044','fk_partner','Ganti Lokasi',document.form1.fk_partner,document.form1.fk_partner,document.form1.fk_tipe_partner,"","","","fk_tipe_partner")
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
<input type="hidden" name="fk_tipe_partner" value="FUNDING">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<td align="center" class="judul_menu">UPLOAD PLEDGING(.xls)</td>
			</table>		
		</td>
	</tr>
    <tr>
		<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
            	<tr bgcolor="efefef">
          			<td width="20%" style="padding:0 5 0 5" bgcolor="#efefef" class="fontColor">Kode Partner</td>
          			<td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                        <input name="fk_partner" type="text" onKeyPress="if(event.keyCode==4) img_fk_partner.click();" onKeyUp="fNextFocus(event,document.form1.periode_awal)"  value="<?=$fk_partner?>" onChange="fGetPartnerData()">&nbsp;<img src="images/search.gif" id="img_fk_partner" onClick="fGetPartner()" style="border:0px" align="absmiddle">
                    </td>
            			<td style="padding:0 5 0 5" width="20%">Nama Partner</td>
            			<td style="padding:0 5 0 5" width="30%"><input type="hidden" name="nm_partner" class='groove_text' value="<?=$nm_partner?>"><span id="divnm_partner"><?=$nm_partner?></span></td>
	  </tr> 
				<tr bgcolor="#efefef">
					<td width="20%" style="padding:0 5 0 5" class="fontColor">Path</td>
					<td width="30%" style="padding:0 5 0 5" ><input type="file" name="fileUpload" class="groove_text" accept=".xls"></td>
					<td style="padding:0 5 0 5"width="20%" class="fontColor">Tgl</td>
					<td style="padding:0 5 0 5"width="30%"><input type="text" value="<?=convert_date_indonesia($tgl_funding)?>" name="tgl_funding" maxlength="10" size="8" onKeyUp="fNextFocus(event,document.form1.description)" onChange="fNextFocus(event,document.form1.description)">&nbsp;<img src="images/btn_extend.gif" width="13" height="12" onClick="fPopCalendar(document.form1.tgl_funding,function(){document.form1.tgl_funding.focus()})"></td>
                   
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
	global $fileUpload,$strmsg,$j_action,$fk_partner,$tgl_funding;
	if($fk_partner==''){
		$strmsg.="Partner Bank kosong";
	}
	if($tgl_funding==''){
		$strmsg.="Tgl Kosong";
	}
	
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
	global $fileUpload,$nama_file,$ext_file,$index,$record_gagal,$tgl_funding,$fk_partner;
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

	$query="select nextserial_transaksi('FND':: text)";
	$lrow=pg_fetch_array(pg_query($query));
	$no_funding=$lrow["nextserial_transaksi"];
	
	if(!pg_query("insert into data_fa.tblfunding(no_funding,tgl_funding,fk_partner) 
	values('".$no_funding."','".$tgl_funding."','".$fk_partner."')")) $l_success=0;	
	//showquery("insert into data_fa.tblfunding(no_funding,tgl_funding,fk_partner) 
	//values('".$no_funding."','".$tgl_funding."','".$fk_partner."')");
	
	if(!pg_query("insert into data_fa.tblfunding_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from data_fa.tblfunding where no_funding='".$no_funding."'")) $l_success=0;
	
	for($i=2;$i<=$total_data;$i++){
		$lrow=array(
				'fk_sbg'=>$array_excel->val($i,'A'),
			);
		$lrow["fk_sbg"]=preg_replace("/\s|&nbsp; /",'',$lrow["fk_sbg"]);
		if(!is_numeric(substr($lrow["fk_sbg"],0,1))){
			$lrow["fk_sbg"]=substr($lrow["fk_sbg"],1);
			//echo "--".$lrow["fk_sbg"].'--';
		}				
		
		//print_r($lrow);
		$lrow_f=pg_fetch_array(pg_query("select * from data_fa.tblfunding_detail where fk_sbg='".$lrow["fk_sbg"]."' and tgl_unpledging is null "));
		
		$index++;
		if(!pg_num_rows(pg_query("select * from tblinventory where fk_sbg='".$lrow["fk_sbg"]."' "))){
			echo "No Kontrak ".$lrow["fk_sbg"]." baris ".($i-1)." tidak ada/belum aktif <br>";
			$l_success=0;
		}elseif($lrow_f["fk_sbg"]){
			echo "No Kontrak ".$lrow["fk_sbg"]." baris ".($i-1)." sudah di funding di ".$lrow_f["fk_funding"]."<br>";
			$l_success=0;
		}
		else{
			if(!pg_query("insert into data_fa.tblfunding_detail(fk_sbg,fk_funding) values('".$lrow["fk_sbg"]."','".$no_funding."')")) $l_success=0;		
		}
		
	}
		
	$l_id_log_ia=get_last_id("data_fa.tblfunding_log","pk_id_log");
	if(!pg_query("insert into data_fa.tblfunding_detail_log select *,'".$l_id_log_ia."' from data_fa.tblfunding_detail where fk_funding='".$no_funding."'")) $l_success=0;
	
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
