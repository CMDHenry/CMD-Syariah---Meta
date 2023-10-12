<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/file.inc.php';
require 'requires/numeric.inc.php';
require 'requires/db_utility.inc.php';
require 'classes/select.class.php';

$id_edit=$_REQUEST["id_edit"];


$fileUpload = $_FILES["fileUpload"];


$id = $_SESSION['id'];
if($_REQUEST["status"]=="Session") {
	update_session();

}
if($_REQUEST["status"]=="Save") {
	cek_error();
	if(!$strmsg){
		add_data();
	}
}

//if($_REQUEST["pstatus"]){
//	get_data();
//}
?>
<html>
<head>
	<title>.: <?=$_SESSION["application"]?> :.</title>
    <link href="text.css.php" rel="stylesheet" type="text/css">
<head>
</head>
<script language='javascript' src='js/alert.js.php'></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src='js/calendar.js.php'></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src="js/validate.js.php"></script>
<script language='javascript'>
function fChangeMode(pObj){
	document.getElementById('divPassword').style.display = 'none'
	document.getElementById('divPicture').style.display = 'none'
	document.getElementById('divCabang').style.display = 'none'

	if(pObj.value=='password') document.getElementById('divPassword').style.display = 'block'
	else if(pObj.value=='picture') document.getElementById('divPicture').style.display = 'block'
	else if(pObj.value == 'move') document.getElementById('divCabang').style.display = 'block'

	document.form1.old_password.value=""
	document.form1.new_password.value=""
	document.form1.confirm_new_password.value=""
	document.form1.fileUpload.value=""
}


function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")) {
		lInputClose=getObjInputClose();lInputClose.close()
	}
}

function fSave(){
	document.form1.status.value='Save';
	document.form1.submit();
}
function fSession(){
	document.form1.status.value='Session';
	document.form1.submit();
}

function fLoad(){
	//parent.parent.document.title="USER PROFILE";
	//fChangeMode(document.form1.changeMode);
<?	
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	}else if($j_action){
		echo $j_action;
	}
?>
}
</script>
<body onLoad="fLoad()" bgcolor="#fafafa">
<form action="modal_browse_foto.php" method="post" name="form1" enctype="multipart/form-data">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="status">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<td align="center" class="judul_menu">BROWSE</td>
			</table>		
		</td>
	</tr>
    <tr>
		<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
 
			</table>

			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<tr bgcolor="#efefef">
					<td width="20%" style="padding:0 5 0 5" class="fontColor">Foto</td>
					<td width="80%" style="padding:0 5 0 5" colspan="3"><input type="file" name="fileUpload" class="groove_text"></td>
				</tr>             
                
			</table>


<!-- end content begin -->
		</td>
    </tr>
	<tr><td height="25" align="center" bgcolor="#D0E4FF" class="border">
		<input type="button" class="groove_button" name="btnsimpan" value="Simpan" onClick="fSave()">
		&nbsp;<input type="button" class="groove_button" value="Batal" onClick="fBatal()">           
    </td>
	</tr>
</table>
</form>
</body>
</html>
<?


function cek_error(){
	global $old_password,$new_password,$confirm_new_password,$strmsg,$j_action,$username,$id,$fileUpload,$id_edit,$changeMode,$cabang;
	
	if($fileUpload['name']!=""){
		if(cek_file("fileUpload",'picture')>0){
			$strmsg.="File Upload Error, Silahkan memilih File Lain<br>";
			if(!$j_action) $j_action="document.form1.fileUpload.focus()";
		}elseif($fileUpload['size'] > 500000){
			//$strmsg.="Ukuran file terlalu besar, Silahkan memilih File Lain<br>";
			if(!$j_action) $j_action="document.form1.fileUpload.focus()";
		}
	}

	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}

function add_data(){
	global $old_password,$new_password,$confirm_new_password,$strmsg,$j_action,$username,$id,$upload_path_website_pic,$id_edit,$fileUpload,$changeMode,$cabang,$wreturn,$upload_path_pic;

	$l_success=1;
	pg_query("BEGIN");
	$strmsg="Data Terupdate.<br>";
	
		//$id_edit=$larr[1];
		//echo $larr[2];
		$imgName=$id_edit.".png";
		
		$tbl="data_gadai.tbltaksir";
		$field_pic="pic_brg";
		$pk_id="no_fatg";	
		
		if($field_pic=="pic_id"){
			$no_id=get_rec("tblcustomer","no_id","".$pk_id." ='".$id_edit."'");
			$imgName=$no_id.".png";
		}
		if($tbl=="data_gadai.tbltaksir"){
			if(pg_num_rows(pg_query("select * from data_gadai.tbltaksir_umum where ".$pk_id."='".$id_edit."' "))){
				$tbl="data_gadai.tbltaksir_umum";
			}
		}
		//echo $imgName."aaa<br>";
		//echo $upload_path_pic."bbb";
		
		if(!pg_query("insert into ".$tbl."_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from ".$tbl." where ".$pk_id."='".$id_edit."' "))$l_success=0;
		
		if(!pg_query("update ".$tbl." set ".$field_pic." ='".$imgName."' where ".$pk_id."='".$id_edit."'")) $l_success=0;
		//echo "update ".$tbl." set ".$field_pic." ='".$imgName."' where ".$pk_id."='".$id_edit."'";
		
		if(!pg_query("insert into ".$tbl."_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from ".$tbl." where ".$pk_id."='".$id_edit."' "))$l_success=0;

		$l_success = move_uploaded_file($_FILES["fileUpload"]["tmp_name"],$upload_path_pic.$imgName);
		
	//$l_success = 0;
	if ($l_success==1){
		$j_action.="lInputClose=getObjInputClose();lInputClose.close();";
		pg_query("COMMIT");
	}else{
		$strmsg="Error :<br>Profile Gagal Terupdate.<br>";
		pg_query("ROLLBACK");
	}
}
function update_session(){
	global $fk_cabang,$j_action,$jenis_user;
	
	$_SESSION["kd_cabang"]=$fk_cabang;
	$_SESSION["jenis_user"]=$jenis_user;
	
	$j_action.="lInputClose=getObjInputClose();lInputClose.close();";

}

?>
