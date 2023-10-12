<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/file.inc.php';
require 'requires/db_utility.inc.php';
require 'classes/excel.class.php';
require 'classes/excel_reader.class.php';
//check_right("10121113");
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
				<td align="center" class="judul_menu">UPLOAD BARANG(.csv)</td>
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
			'kd_jenis_barang'=>$row[0],
			'nm_jenis_barang'=>$row[1],			
			'kd_merek'=>$row[2],
			'nm_merek'=>$row[3],
			'kd_model'=>$row[0],
			'nm_model'=>$row[1],
			'kd_tipe'=>$row[4],
			'nm_tipe'=>$row[5],
			'kd_barang'=>$row[6],
			'nm_barang'=>$row[7],
			'barang_active'=>$row[10],
		);
		$lrow["kd_jenis_barang"]=trim($lrow["kd_jenis_barang"]);
		$lrow["kd_merek"]=trim($lrow["kd_merek"]);
		$lrow["kd_tipe"]=trim($lrow["kd_tipe"]);
		$lrow["nm_barang"]=trim($lrow["nm_barang"]);
		$lrow["kd_barang"]=trim($lrow["kd_barang"]);
		
		if($index>1){
			
			if(!is_numeric($lrow["harga"])){
				$l_success=0;
				echo "Baris ".$index." HARGA SALAH<br>";	
			}
			
			if($lrow["fk_grade"]==""){
				$l_success=0;
				echo "Baris ".$index." GRADE KOSONG<br>";	
			}elseif(!pg_num_rows(pg_query("select * from tblgrade_brg where kd_grade = '".convert_sql($lrow["fk_grade"])."'"))){
				$l_success=0;
				echo "Baris ".$index." ".' GAGAL PADA '.$lrow["fk_grade"]. '. GRADE TIDAK TERDAFTAR<br>';
			}
			
			
			if(!pg_num_rows(pg_query("select * from tbljenis_barang where kd_jenis_barang = '".convert_sql($lrow["kd_jenis_barang"])."'"))){
				$l_success=0;
				echo "Baris ".$index." ".' GAGAL PADA '.$lrow["fk_jenis_barang"]. '. KODE JENIS BARANG TIDAK ADA PADA MASTER<br>';
			}elseif(!pg_num_rows(pg_query("select * from tblwilayah where kd_wilayah = '".convert_sql($lrow["fk_wilayah"])."'"))){
				$l_success=0;
				echo "Baris ".$index." ".' GAGAL PADA '.$lrow["fk_wilayah"]. '. KODE WILAYAH TIDAK TERDAFTAR<br>';
			}else{
				//showquery("select * from tblbarang where kd_barang  ='".convert_sql($lrow["kd_barang"])."'");
				if(pg_num_rows(pg_query("select * from tblbarang where kd_barang  ='".convert_sql($lrow["kd_barang"])."'"))){
					// UPDATE BARANG 
					if(!pg_query("insert into tblbarang_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from tblbarang where kd_barang='".$lrow["kd_barang"]."'")) $l_success=0;
					if(!pg_query("update tblbarang set nm_barang='".$lrow["nm_barang"]."',barang_active='".$lrow["barang_active"]."'  where kd_barang='".($lrow["kd_barang"])."'")) $l_success=0;										
					if(!pg_query("insert into tblbarang_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from tblbarang where kd_barang='".$lrow["kd_barang"]."'")) $l_success=0;										
					
					if(!pg_query("insert into tbltipe_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from tbltipe where kd_tipe='".$lrow["kd_tipe"]."'")) $l_success=0;
					if(!pg_query("update tbltipe set nm_tipe='".$lrow["nm_tipe"]."'  where kd_tipe='".($lrow["kd_tipe"])."'")) $l_success=0;						
					if(!pg_query("insert into tbltipe_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from tbltipe where kd_tipe='".$lrow["kd_tipe"]."'")) $l_success=0;					
					$flag=1;
				}else{
					//BARANG BARU
					if($lrow["kd_merek"]=="" || $lrow["nm_merek"]==""){
						$l_success=0;
						echo "Baris ".$index." KODE/NAMA MEREK KOSONG<br>";	
					}else{
						if(!pg_num_rows(pg_query("select kd_merek from tblmerek where kd_merek = '".trim(convert_sql($lrow["kd_merek"]))."'"))){
							if(!pg_query("insert into tblmerek (kd_merek,nm_merek) values ('".$lrow["kd_merek"]."','".$lrow["nm_merek"]."')"))$l_success=0;
							if(!pg_query("insert into tblmerek_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from tblmerek where kd_merek='".($lrow["kd_merek"])."'"))$l_success=0;
						}
					}
					
					if($lrow["kd_tipe"]==""|| $lrow["nm_tipe"]==""){
						$l_success=0;
						echo "Baris ".$index." KODE/NAMA TIPE KOSONG<br>";	
					}else{
						if(!pg_num_rows(pg_query("select kd_tipe from tbltipe where kd_tipe = '".trim(convert_sql($lrow["kd_tipe"]))."'"))){
							if(!pg_query("insert into tbltipe (kd_tipe,nm_tipe,fk_merek) values ('".$lrow["kd_tipe"]."','".$lrow["nm_tipe"]."','".$lrow["kd_merek"]."')"))$l_success=0;
							if(!pg_query("insert into tbltipe_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from tbltipe where kd_tipe='".($lrow["kd_tipe"])."'"))$l_success=0;
							
						}
					}
										
					if($lrow["tahun_produksi"]==""){
						$l_success=0;
						echo "Baris ".$index." TAHUN KOSONG<br>";	
					}
					
					if($lrow["kd_barang"]==""|| $lrow["nm_barang"]==""){
						$l_success=0;
						echo "Baris ".$index." KODE/NAMA BARANG KOSONG<br>";	
					}					
					
					$flag=0;
					if($l_success==1){
/*						showquery("
						insert into tblbarang (
							kd_barang,nm_barang,fk_jenis_barang,
							fk_tipe,tahun_produksi
						)values(
							'".convert_sql($lrow["kd_barang"])."','".convert_sql($lrow["nm_barang"])."',
							'".convert_sql($lrow["kd_jenis_barang"])."',
							'".convert_sql($lrow["kd_tipe"])."',
							'".convert_sql($lrow["tahun_produksi"])."'
						)
					");
*/						if(!pg_query("
						insert into tblbarang (
							kd_barang,nm_barang,fk_jenis_barang,
							fk_tipe,tahun_produksi,barang_active,fk_grade
						)values(
							'".convert_sql($lrow["kd_barang"])."','".convert_sql($lrow["nm_barang"])."',
							'".convert_sql($lrow["kd_jenis_barang"])."',
							'".convert_sql($lrow["kd_tipe"])."',
							'".convert_sql($lrow["tahun_produksi"])."',
							'".convert_sql($lrow["barang_active"])."',
							'".convert_sql($lrow["fk_grade"])."'
							
						)
					")){
							$l_success=0;
							echo "Baris ".$index.' GAGAL PADA '.$lrow["kd_barang"]."<br>";
						}else{
							$flag=1;
							if(!pg_query("insert into tblbarang_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from tblbarang where kd_barang='".($lrow["kd_barang"])."'"))$l_success=0;
	
						}						
					}
				}
				
				if($flag==1){
				//INSERT HARGA DETAIL
					if(!pg_num_rows(pg_query("select * from tblbarang_detail where fk_barang  ='".convert_sql($lrow["kd_barang"])."' and fk_wilayah  ='".convert_sql($lrow["fk_wilayah"])."'"))){
						if(!pg_query("insert into tblbarang_detail (fk_barang,harga,fk_wilayah,rate) values ('".$lrow["kd_barang"]."','".$lrow["harga"]."','".$lrow["fk_wilayah"]."','".$lrow["rate"]."')"))$l_success=0;
					}else{									
						if(!pg_query("update tblbarang_detail set harga='".$lrow["harga"]."',rate='".$lrow["rate"]."' where fk_barang='".($lrow["kd_barang"])."' and fk_wilayah  ='".convert_sql($lrow["fk_wilayah"])."'")) $l_success=0;
					}
				}
				
				$l_id_log=get_last_id("tblbarang_log","pk_id_log");
				if(!pg_query("insert into tblbarang_detail_log select *,'".$l_id_log."' from tblbarang_detail where fk_barang='".($lrow["kd_barang"])."'")) $l_success=0;

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
	$fileName="barang.csv";
	$text='KDJENIS;JENIS;KDMEREK;MEREK;KDTIPE;TIPE;KDBARANG;BARANG;TAHUN;HARGA;ACTIVE;KDWILAYAH;GRADE';
	$text.="\r\n";
	
	$lrs = pg_query("select * from tblbarang left join tbltipe on fk_tipe=kd_tipe left join tblmerek on fk_merek=kd_merek left join tblbarang_detail on fk_barang=kd_barang left join tbljenis_barang on fk_jenis_barang=kd_jenis_barang where fk_jenis_barang!=0 and fk_tipe is not null");
	while($lrow = pg_fetch_array($lrs)){
		$text.=$lrow["fk_jenis_barang"].';'.$lrow["nm_jenis_barang"].';'.$lrow["fk_merek"].';'.$lrow["nm_merek"].';'.$lrow["fk_tipe"].';'.$lrow["nm_tipe"].';'.$lrow["kd_barang"].';'.$lrow["nm_barang"].';'.$lrow["tahun_produksi"].';'.$lrow["harga"].';'.$lrow["barang_active"].';'.$lrow["fk_wilayah"].';'.$lrow["fk_grade"];
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
//create excel to download
function create_excel(){
	global $upload_path;
	$xls = new XLS("barang");
	echo "<b>Creating file</b>";

	//header excel
	$xls->xlsWriteLabel(0,0,"kd_barang");
	$xls->xlsWriteLabel(0,1,"nm_barang");
	$xls->xlsWriteLabel(0,2,"fk_jenis_barang");
	$xls->xlsWriteLabel(0,3,"nm_merek");
	$xls->xlsWriteLabel(0,4,"nm_tipe");
	//===============================================
	
	$i=1;
	$lrs = pg_query("select * from tblbarang where fk_jenis_barang=2");
	echo "<br>Writing<br>";
	while($lrow = pg_fetch_array($lrs)){
		//write excel
		$xls->xlsWriteLabel($i,0,$lrow["kd_barang"]);
		$xls->xlsWriteLabel($i,1,$lrow["nm_barang"]);
		$xls->xlsWriteLabel($i,2,$lrow["fk_jenis_barang"]);
		
		$lrow_merek=pg_fetch_array(pg_query("select nm_merek from tblmerek where kd_merek = '".trim(convert_sql($lrow["fk_merek"]))."'"));
		$lrow_tipe=pg_fetch_array(pg_query("select nm_tipe from tbltipe where kd_tipe = '".trim(convert_sql($lrow["fk_tipe"]))."'"));
						
		$xls->xlsWriteLabel($i,3,$lrow_merek["nm_merek"]);
		$xls->xlsWriteLabel($i,4,$lrow_tipe["nm_tipe"]);
		//===============================================
		$i++;
	}

	$xls->xlsOutput($upload_path,"temp");
	echo "File berhasil di-create<br>Klik untuk men-download<br>";
	echo "<a href='file/temp/barang.xls'>BARANG</a><br>";
}
//=============================================================

/*
				$lrs_wilayah=pg_query("select * from tblwilayah where wilayah_active='t'");
				while($lrow_wilayah=pg_fetch_array($lrs_wilayah)){
					if(!pg_num_rows(pg_query("select * from tblbarang_detail where fk_barang  ='".convert_sql($lrow["kd_barang"])."' and fk_wilayah  ='".convert_sql($lrow_wilayah["kd_wilayah"])."'"))){
						if(!pg_query("insert into tblbarang_detail (fk_barang,harga,fk_wilayah,rate) values ('".$lrow["kd_barang"]."','".$lrow["harga"]."','".$lrow_wilayah["kd_wilayah"]."','".$lrow["rate"]."')"))$l_success=0;
					}
				}					
									
				if(!pg_query("update tblbarang_detail set harga='".$lrow["harga"]."',rate='".$lrow["rate"]."' where fk_barang='".($lrow["kd_barang"])."'")) $l_success=0;

*/
?>
