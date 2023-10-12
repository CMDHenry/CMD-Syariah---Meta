<?php
$flag=$_REQUEST["flag"];
require 'requires/config.inc.php';
if($flag!='true'){
	require 'requires/authorization.inc.php';
}require 'requires/general.inc.php';
require 'requires/file.inc.php';
require 'requires/numeric.inc.php';
require 'requires/db_utility.inc.php';
require 'classes/select.class.php';

$id_edit=$_REQUEST["id_edit"];

$cabang = trim($_REQUEST["cabang"]);
$changeMode = trim($_REQUEST["changeMode"]);
$old_password=trim($_REQUEST["old_password"]);
$new_password=trim($_REQUEST["new_password"]);
$confirm_new_password=trim($_REQUEST["confirm_new_password"]);
if($flag!='true'){
	$username = $_SESSION['username'];
}else{
	$username = $_REQUEST['username'];
}
$fileUpload = $_FILES["fileUpload"];
$wreturn="window.parent.returnValue=0;";
if(!$_REQUEST["kd_cabang"])$kd_cabang= $_SESSION['kd_cabang'];
else $kd_cabang = trim($_REQUEST["kd_cabang"]);
if(!$_REQUEST["jenis_user"])$jenis_user= $_SESSION['jenis_user'];
else $jenis_user = trim($_REQUEST["jenis_user"]);

$lrow_username=pg_fetch_array(pg_query("select * from tbluser where upper(username)='".strtoupper($username)."'"));
if($flag!='true'){
	$id = $_SESSION['id'];
}else{
	$id = $lrow_username['kd_user'];
}
if($_REQUEST["status"]=="Session") {
	update_session();

}

if($_REQUEST["status"]=="Ganti Cabang") {
	//echo "aaaa";
	update_cabang();
}

if($_REQUEST["status"]=="Save") {
	cek_error();
	if(!$strmsg){
		add_data();
	}
}

if($_REQUEST["pstatus"]){
	get_data();
}
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

function fChange(){
	document.form1.status.value='Ganti Cabang';
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
<form action="modal_change_password.php" method="post" name="form1" enctype="multipart/form-data">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="flag" value="<?=$flag?>">
<input type="hidden" name="username" value="<?=$username?>">
<input type="hidden" name="status">
<table cellpadding="0" cellspacing="0" border="0" width="794" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<td align="center" class="judul_menu">PROFILE</td>
			</table>		
		</td>
	</tr>
    <tr>
		<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<tr bgcolor="#efefef">
					<td width="20%" style="padding:0 5 0 5">Username</td>
				  	<td width="30%" style="padding:0 5 0 5"><?=$username?></td>
					<td width="20%" style="padding:0 5 0 5">Level</td>
				    <td width="30%" style="padding:0 5 0 5"><?=get_level_name()?></td>
				</tr>
                <tr><td bgcolor="aaafff" height="1" colspan="4"></td></tr>
				<tr bgcolor="#efefef">
					<td width="20%" style="padding:0 5 0 5" class="fontColor">Password Lama</td>
				  	<td width="80%" style="padding:0 5 0 5" colspan="3"><input style="width:90%" type="password" name="old_password" class="groove_text" value="<?=$old_password?>"></td>
				</tr>
				<tr bgcolor="#efefef">
					<td width="20%" style="padding:0 5 0 5" class="fontColor">Password Baru</td>
					<td width="80%" style="padding:0 5 0 5" colspan="3"><input style="width:90%" type="password" name="new_password" class="groove_text" value="<?=$new_password?>"></td>
				</tr>
				<tr bgcolor="#efefef">
					<td width="20%" style="padding:0 5 0 5" class="fontColor">Konfirmasi Password Baru</td>
					<td width="80%" style="padding:0 5 0 5" colspan="3"><input style="width:90%" type="password" name="confirm_new_password" class="groove_text" value="<?=$confirm_new_password?>"></td>
				</tr>
                
                
			</table>

			<table cellpadding="0" cellspacing="1" border="0" width="100%">
<? if($flag!="true"){?>            
				<tr bgcolor="#efefef">
					<td width="20%" style="padding:0 5 0 5" class="fontColor">Foto (max 500Kb)</td>
					<td width="80%" style="padding:0 5 0 5" colspan="3"><input type="file" name="fileUpload" class="groove_text"></td>
				</tr>
<? }?>               
<?  if($_SESSION["jenis_user"]=="HO" || $username=="superuser"){?>                      
                <tr bgcolor="#efefef">
                    <td width="20%" style="padding:0 5 0 5" class="">Cabang</td>
                    <td width="80%" style="padding:0 5 0 5" colspan="3"><?=create_list_cabang_ho()?></td>
                </tr>
<?  } ?> 
<?  if($username=="superuser"){?>                
				<!--<tr bgcolor="#efefef">
					<td width="20%" style="padding:0 5 0 5" class="">Cabang</td>
					<td width="80%" style="padding:0 5 0 5" colspan="3"><input style="width:90%" type="text" name="fk_cabang" class="groove_text" value="<?=$fk_cabang?>"></td>
				</tr>-->
				<tr bgcolor="#efefef">
					<td width="20%" style="padding:0 5 0 5" class="">Jenis User</td>
					<td width="80%" style="padding:0 5 0 5" colspan="3"><input style="width:90%" type="text" name="jenis_user" class="groove_text" value="<?=$jenis_user?>"></td>
				</tr>                
<? }?>                
                
			</table>


<!-- end content begin -->
		</td>
    </tr>
	<tr><td height="25" align="center" bgcolor="#D0E4FF" class="border">
		<input type="button" class="groove_button" name="btnsimpan" value="Simpan" onClick="fSave()">
		&nbsp;<input type="button" class="groove_button" value="Batal" onClick="fBatal()">
<?  if($username=="superuser"){?>                
		<input type="button" class="groove_button" name="btnsimpan" value="Session" onClick="fSession()">
<? }?> 
	<?  if($_SESSION["jenis_user"]=="HO"){?>                
                <input type="button" class="groove_button" name="btnsimpan" value="Ganti Cabang" onClick="fChange()">
        <? }?>                               
    </td>
	</tr>
</table>
</form>
</body>
</html>
<?
function create_list_cabang_ho(){
	global $kd_cabang,$username;	
	
	//if($username=="superuser"){
		$query="select kd_cabang,kd_cabang||' - '||nm_cabang as nm_cabang from tblcabang order by kd_cabang";
	/*}else{
		$query="
			select * from(
				select kd_cabang,nm_cabang from tblcabang 
				inner join tblwilayah on fk_cabang_wilayah=kd_cabang 
				where kd_cabang in (select fk_cabang_user_ho from tbluser_ho_detail where fk_user=".$_SESSION["id"].")
				union
				select kd_cabang,nm_cabang from tblcabang where kd_cabang='".cabang_ho."'
				and (select fk_cabang_user from tbluser where kd_user=".$_SESSION["id"].") =''			
			)as tblmain
			order by kd_cabang
		";
	}*/
	//showquery($query);
	$l_list_obj = new select($query,"nm_cabang","kd_cabang","kd_cabang");
	$l_list_obj->set_default_value($kd_cabang);
	//$l_list_obj->add_item("HO",cabang_ho,0);
	$l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;' onKeyUp='fNextFocus(event,document.form1.fk_cabang)'",'form1','');
}


function get_level_name(){
	$lreturn="";
	if ($lrow_user=pg_fetch_array(pg_query("select * from (select * from tbluser where kd_user='".$_SESSION["id"]."') as tbluser inner join tbllevel on tbllevel.kd_level=tbluser.fk_level"))){
		$lreturn=$lrow_user["name"];
	}
	//showquery("select * from (select * from tbluser where kd_user='".$_SESSION["id"]."') as tbluser inner join tbllevel on tbllevel.kd_level=tbluser.fk_level");
	return $lreturn;
}

function cek_error(){
	global $old_password,$new_password,$confirm_new_password,$strmsg,$j_action,$username,$id,$fileUpload,$id_edit,$changeMode,$cabang,$flag;
	//if($_SESSION['username']=='superuser')return true;
	$lrow=pg_fetch_array(pg_query("select * from tbluser where upper(username) = '".strtoupper($username)."' and kd_user = '".$id."'"));
	//showquery("select * from tbluser where upper(username) = '".strtoupper($username)."' and kd_user = '".$id."'");
	if($old_password == '' && $new_password == '' && $confirm_new_password == '' && $fileUpload['name']==""){
		$strmsg.="Silahkan Ganti Password atau Ganti foto.<br>";
		if(!$j_action) $j_action="document.form1.old_password.focus()";
	}
	if($old_password != '' || $new_password != '' || $confirm_new_password != ''){
		if($old_password==""){
			$strmsg.="Password Lama Kosong.<br>";
			if(!$j_action) $j_action="document.form1.old_password.focus()";
		}elseif(crypt($old_password,$lrow["password"])!=$lrow["password"]){
			$strmsg.="Password Lama Salah.<br>";
			if(!$j_action) $j_action="document.form1.old_password.focus()";
		}
		if($new_password==""){
			$strmsg.="Password Baru Kosong.<br>";
			if(!$j_action) $j_action="document.form1.new_password.focus()";
		}elseif($new_password==$old_password){
			$strmsg.="Password Baru Sama Dengan Password Lama.<br>";
			if(!$j_action) $j_action="document.form1.new_password.focus()";		
		}
		if($confirm_new_password==""){
			$strmsg.="Konfirmasi Password Baru Kosong.<br>";
			if(!$j_action) $j_action="document.form1.confirm_new_password.focus()";
		}elseif($new_password!=$confirm_new_password){
			$strmsg.="Konfirmasi Password Baru berbeda dengan Password Baru.<br>";
			if(!$j_action) $j_action="document.form1.confirm_new_password.focus()";		
		}
	}
	
	if($fileUpload['name']!=""){
		if(cek_file("fileUpload",'picture')>0){
			$strmsg.="File Upload Error, Silahkan memilih File Lain<br>";
			if(!$j_action) $j_action="document.form1.fileUpload.focus()";
		}elseif($fileUpload['size'] > 500000){
			$strmsg.="Ukuran file terlalu besar, Silahkan memilih File Lain<br>";
			if(!$j_action) $j_action="document.form1.fileUpload.focus()";
		}
	}

	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}

function add_data(){
	global $old_password,$new_password,$confirm_new_password,$strmsg,$j_action,$username,$id,$upload_path_website_pic,$id_edit,$fileUpload,$changeMode,$cabang,$wreturn,$flag;

	$l_success=1;
	pg_query("BEGIN");
	$strmsg="Profile Terupdate.<br>";
	if($old_password != '' && $new_password != '' && $confirm_new_password != ''){
		//log begin
		if(!pg_query("insert into tbluser_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from tbluser where kd_user='".$id."'")) $l_success=0;
		//end log
	
		if(!pg_query("update tbluser set password='".convert_sql(crypt($new_password))."',tgl_ganti_password='".date("Y/m/d H:i:s")."' where kd_user='".$id."'")) $l_success=0;
		
		//showquery("update tbluser set password='".convert_sql(crypt($new_password))."',tgl_ganti_password='".date("Y/m/d H:i:s")."' where kd_user='".$id."'");
		
		//log begin
		if(!pg_query("insert into tbluser_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from tbluser where kd_user='".$id."'")) $l_success=0;
		//end log
	}
	if($fileUpload['name']!=""){

		$ext = findexts($_FILES["fileUpload"]["name"]);
		
		$l_success = move_uploaded_file($_FILES["fileUpload"]["tmp_name"],$upload_path_website_pic."profile_pic/".$fileUpload['name']);
		if(!pg_query("update tbluser set pic='".convert_sql($fileUpload['name'])."' where kd_user='".$id."'")) $l_success=0;
		
		$wreturn="window.parent.returnValue=1;";
	}
	//$l_success = 0;
	if ($l_success==1){
		if($flag!="true"){
			$j_action.="lInputClose=getObjInputClose();lInputClose.close();";
		}else{
			$j_action="location='index.php'";
		}
		pg_query("COMMIT");
	}else{
		$strmsg="Error :<br>Profile Gagal Terupdate.<br>";
		pg_query("ROLLBACK");
	}
}

function update_cabang(){
	global $kd_cabang,$j_action,$jenis_user,$kd_wilayah,$kd_area;
	$_SESSION["kd_cabang"]=$kd_cabang;
	$nm_cabang=get_rec("tblcabang","nm_cabang","kd_cabang='".$kd_cabang."'");
	$_SESSION["nm_cabang"]=$nm_cabang;
	
	//if(!pg_num_rows(pg_query("select * from tblwilayah where fk_cabang_wilayah ='".$kd_cabang."'")))$_SESSION["jenis_user"]='Cabang';
	//else $_SESSION["jenis_user"]='HO';
	
	//$_SESSION["jenis_user"]=$jenis_user;
	//$_SESSION["kd_area"]=$kd_area;
	//$_SESSION["kd_wilayah"]=$kd_wilayah;
	$j_action.="lInputClose=getObjInputClose();lInputClose.close();";

}

function update_session(){
	global $kd_cabang,$j_action,$jenis_user;
	
	$_SESSION["kd_cabang"]=$kd_cabang;
	$nm_cabang=get_rec("tblcabang","nm_cabang","kd_cabang='".$kd_cabang."'");
	$_SESSION["nm_cabang"]=$nm_cabang;
	
	$_SESSION["jenis_user"]=$jenis_user;
	
	$j_action.="lInputClose=getObjInputClose();lInputClose.close();";

}

?>
