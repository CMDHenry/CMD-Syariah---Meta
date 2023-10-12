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

$no_funding=($_REQUEST["no_funding"]);
//$nm_partner=($_REQUEST["nm_partner"]);

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

function fGetFunding(){
	fGetNC(false,'20171100000045','no_funding','Ganti Lokasi',document.form1.no_funding,document.form1.no_funding)
    //if (document.form1.no_funding.value !="")fGetFundingData()	
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
				<td align="center" class="judul_menu">UPLOAD UNPLEDGING(.xls)</td>
			</table>		
		</td>
	</tr>
    <tr>
		<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
            	<tr bgcolor="efefef">
          			<td width="20%" style="padding:0 5 0 5" bgcolor="#efefef" class="fontColor">No Funding</td>
          			<td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                        <input name="no_funding" type="text" onKeyPress="if(event.keyCode==4) img_no_funding.click();" onKeyUp="fNextFocus(event,document.form1.periode_awal)"  value="<?=$no_funding?>">&nbsp;<img src="images/search.gif" id="img_no_funding" onClick="fGetFunding()" style="border:0px" align="absmiddle">
                    </td>
            			<td style="padding:0 5 0 5" width="20%"></td>
            			<td style="padding:0 5 0 5" width="30%"></td>
	  </tr> 
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
	global $fileUpload,$nama_file,$ext_file,$index,$record_gagal,$alasan_reject,$no_funding;
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
				//'tgl_cair'=>$array_excel->val($i,'B'),
				//'tgl_jt'=>$array_excel->val($i,'C'),
		);
			
		$lrow["fk_sbg"]=preg_replace("/\s|&nbsp; /",'',$lrow["fk_sbg"]);
		if(!is_numeric(substr($lrow["fk_sbg"],0,1))){
			$lrow["fk_sbg"]=substr($lrow["fk_sbg"],1);
			//echo "--".$lrow["fk_sbg"].'--';
		}				
	
		
		if(!pg_num_rows(pg_query("select * from tblinventory where fk_sbg='".$lrow["fk_sbg"]."' "))){
			echo "No Kontrak baris ".($i-1)." tidak ada<br>";$l_success=0;
		}
	
		$p_table2="data_fa.tblfunding_detail";
		$p_where2=" fk_funding='".$no_funding."' and fk_sbg='".$lrow["fk_sbg"]."' ";

		$lrs=pg_query("select * from ".$p_table2."
		where ".$p_where2." ");
				
		while($lrow1=pg_fetch_array($lrs)){						
			if(!pg_query("update data_fa.tblfunding_detail set tgl_unpledging='".today_db."' where ".$p_where2))$l_success=0;
			//showquery("update data_fa.tblfunding_detail set tgl_unpledging='".today_db."' where ".$p_where2);		
		}
		
	}
	
	if(!pg_num_rows(pg_query("select * from data_fa.tblfunding_detail where tgl_unpledging is null and fk_funding='".$no_funding."' "))){
		$p_table="data_fa.tblfunding";
		$p_where=" no_funding='".$no_funding."'";
	
		if(!pg_query(insert_log($p_table,$p_where,'UB')))$l_success=0;
		
		if(!pg_query("update data_fa.tblfunding set status_funding='Unpledging' where ".$p_where))$l_success=0;
		//showquery("update data_fa.tblfunding set status_funding='Unpledging' where ".$p_where);
		
		if(!pg_query(insert_log($p_table,$p_where,'UA')))$l_success=0;
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
?>
