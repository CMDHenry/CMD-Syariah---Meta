<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/file.inc.php';
require 'requires/db_utility.inc.php';
require 'classes/excel.class.php';
require 'classes/excel_reader.class.php';

//check_right("10121113");

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
<form action="modal_kelurahan_upload.php" method="post" name="form1" enctype="multipart/form-data">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="status">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<td align="center" class="judul_menu">UPLOAD Kelurahan(.xls)</td>
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
	$path=$upload_path.'/kelurahan/'.$fileUpload["name"];
	if($fileUpload['name']!=""){
		if(file_exists($path))unlink($path);
			$l_success = move_uploaded_file($_FILES["fileUpload"]["tmp_name"],$path);
			echo "<b>File terupload . . . .<br>Insert/update database<br>----------------------<br></b>";
			$l_success = update_database($path);
	}

	if ($l_success==1){
		$strmsg="Simpart Terupdate.<br>";
		$j_action.="lInputClose=getObjInputClose();lInputClose.close();";
		pg_query("COMMIT");
	}else{
		$strmsg="Error :<br>Simpart Gagal Terupdate.<br>";
		pg_query("ROLLBACK");
	}
}
//=============================================================
//update database
function update_database($file){
	global $fileUpload,$nama_file,$ext_file,$index,$record_gagal,$alasan_reject,$upload_path;
	//access ke excel
	//echo "Open connection to excel<br>";
	$array_excel = new Spreadsheet_Excel_Reader($file); //panggil class
	$total_data = $array_excel->rowcount($sheet_index=0) ;//total data
	//echo "Connected to excel<br>----------------------<br>";
	//=====================================================
	$l_success=1;
	//echo "Start insert/update database<br>---------------------------------<br><br>";
	$index=1;
	echo $total_data;
	pg_query("BEGIN");	
	//echo date("H:i:s")." 1<br>";
	
	for($i=2;$i<=$total_data;$i++){
		
		$lrow=array(
				'kd_pos'=>$array_excel->val($i,'A'),
				'nm_kelurahan'=>$array_excel->val($i,'B'),
				'nm_kecamatan'=>$array_excel->val($i,'C'),
				'jenis_kota'=>$array_excel->val($i,'D'),
				'nm_kota'=>$array_excel->val($i,'E'),
				'nm_provinsi'=>$array_excel->val($i,'F')
			);
		$lrow["nm_kelurahan"]=str_replace("'", "", $lrow["nm_kelurahan"]);
		$lrow["nm_kecamatan"]=str_replace("'", "", $lrow["nm_kecamatan"]);
		$lrow["nm_kota"]=str_replace("'", "", $lrow["nm_kota"]);
		if($nm_provinsi!=$lrow["nm_provinsi"]){
			//showquery("select * from tblprovinsi where  nm_provinsi='".convert_sql($lrow["nm_provinsi"])."'");
			if(!pg_num_rows(pg_query("select kd_provinsi from tblprovinsi where nm_provinsi='".convert_sql($lrow["nm_provinsi"])."'"))){
				$no_provinsi=pg_num_rows(pg_query("select * from tblprovinsi"));
				$kd_provinsi=str_pad($no_provinsi+1,2,"0",STR_PAD_LEFT);
				$query="
				insert into tblprovinsi(kd_provinsi,nm_provinsi) values('".$kd_provinsi."','".$lrow["nm_provinsi"]."') ";
				//showquery($query);
				
				if(!pg_query($query))$l_success=0;
				
				//if(!pg_query("insert into tblprovinsi_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from tblprovinsi where kd_provinsi='".convert_sql($kd_provinsi)."'")) $l_success=0;
				$nm_provinsi=$lrow["nm_provinsi"];
			}		
		}
		if($nm_kota!=$lrow["nm_kota"]){
		//showquery("select * from tblkota where  nm_kota='".convert_sql($lrow["nm_kota"])."'");
			if(!pg_num_rows(pg_query("select kd_kota from tblkota where  nm_kota='".convert_sql($lrow["nm_kota"])."'"))){
				$no_kota=pg_num_rows(pg_query("select * from tblkota"));
				$kd_kota=str_pad($no_kota+1,3,"0",STR_PAD_LEFT);
				$fk_provinsi=get_rec("tblprovinsi","kd_provinsi","nm_provinsi='".convert_sql($lrow["nm_provinsi"])."'");
				$query="
				insert into tblkota(kd_kota ,nm_kota,fk_provinsi,jenis_kota) values('".$kd_kota."','".$lrow["nm_kota"]."','".$fk_provinsi."','".$lrow["jenis_kota"]."') ";
				//showquery($query);
				if(!pg_query($query))$l_success=0;
				
				//if(!pg_query("insert into tblkota_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from tblkota where kd_kota='".convert_sql($kd_kota)."'")) $l_success=0;
			}
			$nm_kota=$lrow["nm_kota"];
		}
		
		
		if($nm_kecamatan!=$lrow["nm_kecamatan"]){
		//showquery("select * from tblkecamatan where nm_kecamatan='".convert_sql($lrow["nm_kecamatan"])."'");
			if(!pg_num_rows(pg_query("select kd_kecamatan from tblkecamatan left join tblkota on fk_kota=kd_kota where  nm_kecamatan='".convert_sql($lrow["nm_kecamatan"])."' and nm_kota='".convert_sql($lrow["nm_kota"])."' "))){
				$no_kecamatan=pg_num_rows(pg_query("select * from tblkecamatan"));
				$kd_kecamatan=str_pad($no_kecamatan+1,4,"0",STR_PAD_LEFT);
				$fk_kota=get_rec("tblkota","kd_kota","nm_kota='".convert_sql($lrow["nm_kota"])."'");
				$query="
				insert into tblkecamatan(kd_kecamatan,nm_kecamatan,fk_kota) values('".$kd_kecamatan."','".$lrow["nm_kecamatan"]."','".$fk_kota."') ";
				//showquery($query);
				if(!pg_query($query))$l_success=0;
				
				//if(!pg_query("insert into tblkecamatan_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from tblkecamatan where kd_kecamatan='".convert_sql($kd_kecamatan)."'")) $l_success=0;
			}
			$nm_kecamatan=$lrow["nm_kecamatan"];
		}
		if($nm_kelurahan!=$lrow["nm_kelurahan"]){
		showquery("select kd_kelurahan from tblkelurahan left join tblkecamatan on fk_kecamatan=kd_kecamatan left join tblkota on fk_kota=kd_kota left join tblprovinsi on kd_provinsi =fk_provinsi where nm_kelurahan='".convert_sql($lrow["nm_kelurahan"])."' and nm_kecamatan='".convert_sql($lrow["nm_kecamatan"])."' and nm_kota='".convert_sql($lrow["nm_kota"])."'");
			if(!pg_num_rows(pg_query("select kd_kelurahan from tblkelurahan left join tblkecamatan on fk_kecamatan=kd_kecamatan left join tblkota on fk_kota=kd_kota left join tblprovinsi on kd_provinsi =fk_provinsi where nm_kelurahan='".convert_sql($lrow["nm_kelurahan"])."' and nm_kecamatan='".convert_sql($lrow["nm_kecamatan"])."' and nm_kota='".convert_sql($lrow["nm_kota"])."'"))){
				$no_kelurahan=pg_num_rows(pg_query("select * from tblkelurahan"));
				$kd_kelurahan=str_pad($no_kelurahan+1,5,"0",STR_PAD_LEFT);
				$fk_kecamatan=get_rec("tblkecamatan","kd_kecamatan","nm_kecamatan='".convert_sql($lrow["nm_kecamatan"])."'");
				$query="
				insert into tblkelurahan(kd_kelurahan,fk_kecamatan,kd_pos,nm_kelurahan) values('".$kd_kelurahan."','".$fk_kecamatan."','".$lrow["kd_pos"]."','".$lrow["nm_kelurahan"]."') ";
				//showquery($query);
				if(!pg_query($query))$l_success=0;
				
				//if(!pg_query("insert into tblkelurahan_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from tblkelurahan where kd_kelurahan='".convert_sql($kd_kelurahan)."'")) $l_success=0;	
			}
		}
		
		$index++;
	}
	//echo date("H:i:s")." 3<br>";
	//$l_success=0;

	if ($l_success==1){
		pg_query("COMMIT");
		exit('<br>PENGINPUTAN DATA SUKSES');
	}else{
		pg_query("ROLLBACK");
		exit('<br>PENGINPUTAN DATA GAGAL');
	}
	
}
 
?>
