<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/file.inc.php';
require 'requires/db_utility.inc.php';
require 'classes/excel.class.php';
require 'classes/excel_reader.class.php';
require_once('classes/parsecsv.lib.php');
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
}elseif($_REQUEST["status"]=="Download"){
	create_csv();
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
				<td align="center" class="judul_menu">UPLOAD(.csv)</td>
			</table>		
		</td>
	</tr>
    <tr>
		<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<tr bgcolor="#efefef">
					<td width="20%" style="padding:0 5 0 5" class="fontColor">Path</td>
					<td width="80%" style="padding:0 5 0 5" colspan="3"><input type="file" name="fileUpload" class="groove_text" accept=".csv"></td>
				</tr>
			</table>
<!-- end content begin -->
		</td>
    </tr>
	<tr><td height="25" align="center" bgcolor="#D0E4FF" class="border">
		<input type="button" class="groove_button" name="btnsimpan" value="Upload" onClick="fSave(this)">
        &nbsp;<input type="button" class="groove_button" value="Download" onClick="fSave(this)">
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
	//access ke excel
//	echo "open connection to excel<br>";
//	$l_db_access=odbc_connect("DBQ=".$file.";Driver={Microsoft Excel Driver (*.xls)};","","");
//	echo "connected to excel<br>----------------------<br>";
//	$lrs = odbc_exec($l_db_access,"select distinct * from [part$]");
	//=====================================================
	global $flag;
	$flag=1;
	$luploadMsg='';

	$l_csv = new parseCSV();
	$l_csv->auto($file);
	$ldata = $l_csv->data;
	//print_r($ldata);
	if(count($ldata)<1){
		$luploadMsg='no data';
		return false;
	}
	$l_success=1;
	pg_query("BEGIN");
	echo "Start insert/update database<br>";
	$index=1;
	foreach($ldata as $lindex => $row){
		
		$lrow=array(
			'npk'=>$row[0],
			'nm_depan'=>$row[1],
			'alamat'=>$row[2],
			'no_hp'=>$row[3],
			'fk_jabatan'=>$row[4],
			'karyawan_active'=>$row[5],
		);
		$lrow["npk"]=trim($lrow["npk"]);
		$lrow["no_hp"]=ltrim($lrow["no_hp"],"'");
		
		if($index>1){
			
			if(!pg_num_rows(pg_query("select * from tbljabatan where kd_jabatan = '".convert_sql($lrow["fk_jabatan"])."'"))){
				$l_success=0;
				echo "Baris ".$index." ".' GAGAL PADA '.$lrow["fk_jabatan"]. '. KODE JABATAN TIDAK ADA PADA MASTER<br>';
			}			

			if(pg_num_rows(pg_query("select * from tblkaryawan where npk  ='".convert_sql($lrow["npk"])."'"))){
				// UPDATE BARANG 
				if(!pg_query("insert into tblkaryawan_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from tblkaryawan where npk='".$lrow["npk"]."'")) $l_success=0;
				if(!pg_query("update tblkaryawan set nm_depan='".$lrow["nm_depan"]."',alamat='".$lrow["alamat"]."',no_hp='".$lrow["no_hp"]."',fk_jabatan='".$lrow["fk_jabatan"]."',karyawan_active='".$lrow["karyawan_active"]."'  where npk='".($lrow["npk"])."'")) $l_success=0;					
				//showquery("update tblkaryawan set nm_depan='".$lrow["nm_depan"]."',alamat='".$lrow["alamat"]."',no_hp='".$lrow["no_hp"]."',fk_jabatan='".$lrow["fk_jabatan"]."',karyawan_active='".$lrow["karyawan_active"]."'  where npk='".($lrow["npk"])."'");					
				if(!pg_query("insert into tblkaryawan_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from tblkaryawan where npk='".$lrow["npk"]."'")) $l_success=0;										
				
			}else{
				//BARANG BARU									
				
				if($lrow["npk"]==""|| $lrow["nm_depan"]==""){
					$l_success=0;
					echo "Baris ".$index." KODE/NAMA KARYAWAN KOSONG<br>";	
				}					
				
				$flag=0;
				if($l_success==1){
					if(!pg_query("
						insert into tblkaryawan (
							npk,nm_depan,alamat,
							no_hp,fk_jabatan,karyawan_active
						)values(
							'".convert_sql($lrow["npk"])."','".convert_sql($lrow["nm_depan"])."',
							'".convert_sql($lrow["alamat"])."',
							'".convert_sql($lrow["no_hp"])."',
							'".convert_sql($lrow["fk_jabatan"])."',
							'".convert_sql($lrow["karyawan_active"])."'
						)
					")){
							$l_success=0;
							echo "Baris ".$index.' GAGAL PADA '.$lrow["npk"]."<br>";
						}else{
							$flag=1;
							if(!pg_query("insert into tblkaryawan_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from tblkaryawan where npk='".($lrow["npk"])."'"))$l_success=0;
	
					}						
				}
			}								
		}
		$index++;
	}
	
	echo "Finished insert/update database<br>----------------------<br>";
	echo "Connection Closed<br>";

	//$l_success = 0;
	if ($l_success==1){
		pg_query("COMMIT");
		exit('SUKSES BESAR');
	}else{
		pg_query("ROLLBACK");
		exit('GAGAL TOTAL');
	}
}

function create_csv(){	
	$fileName="datakaryawan.csv";
	$text='NPK;NAMA;ALAMAT;NOHP;KDJABATAN;ACTIVE';
	$text.="\r\n";
	
	$lrs = pg_query("select * from tblkaryawan left join tbljabatan on fk_jabatan=kd_jabatan");
	while($lrow = pg_fetch_array($lrs)){
		$text.=$lrow["npk"].";".$lrow["nm_depan"].";".$lrow["alamat"].";'".$lrow["no_hp"].";".$lrow["fk_jabatan"].";".$lrow["karyawan_active"];
		$text.="\r\n";
	}
	$text=trim($text);
	header("Cache-Control: ");
	header("Pragma: ");
	header("Content-Type:application/octet-stream");
	header("Content-Length: ".strlen($text));
	header("Content-Disposition: inline; filename=".$fileName."");
	exit($text);	
	
}

//=============================================================

?>
