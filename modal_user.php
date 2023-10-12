<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/numeric.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/file.inc.php';
require 'classes/select.class.php';

$id_edit=$_REQUEST["id_edit"];

$username=trim($_REQUEST["username"]);
$password1=trim($_REQUEST["password1"]);
$password2=trim($_REQUEST["password2"]);
$level=$_REQUEST["level"];
$fk_karyawan=$_REQUEST["fk_karyawan"];
$nm_karyawan=$_REQUEST["nm_karyawan"];
$jenis_user=$_REQUEST["jenis_user"];
$kd_wilayah	= $_REQUEST["kd_wilayah"];
$nm_wilayah	= $_REQUEST["nm_wilayah"];
$fk_cabang	= $_REQUEST["fk_cabang"];
$nm_cabang	= $_REQUEST["nm_cabang"];
$is_active=$_REQUEST["is_active"];
if ($is_active=="") $is_active="f";

$pic_profile=trim($_REQUEST["pic_profile"]);
$pic_name = $_FILES['pic_profile']['name'];

if($_REQUEST["status"]=="Save") {
	cek_error();
	if(!$strmsg){
		if ($id_edit) edit_data();
		else add_data();
	}
}

if($_REQUEST["pstatus"]){
	//$id_edit=get_rec("tbluser","kd_user","username='".$id_edit."'");
	get_data();
}
?>
<html>
<head>
	<title>.: <?=$_SESSION["application"]?> :.</title>
    <link href="text.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src='js/ajax.js.php'></script>
<script language='javascript' src='js/alert.js.php'></script>
<script language='javascript' src='js/openwindow.js.php'></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript'>
function fGetKaryawan(){
	fGetNC(false,'20170800000027','npk','Ganti Karyawan',document.form1.fk_karyawan,document.form1.fk_karyawan)
	if (document.form1.fk_karyawan.value !="")fGetKaryawanData()
}

function fGetKaryawanData(){
	document.getElementById("divNmKaryawan").innerHTML=""
	document.form1.nm_karyawan.value=""
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataKaryawanState
	lSentText='table=(select npk,(nm_depan||\' \'|| case when nm_belakang is null then \'\' else nm_belakang end) as nm_karyawan from tblkaryawan)as tblkaryawan&field=nm_karyawan&key=npk&value='+document.form1.fk_karyawan.value
	lObjLoad.open("POST","ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}
function fGetDataKaryawanState(){
	if (this.readyState == 4){
		if (this.status==200 && this.responseText!="") {
			document.getElementById("divNmKaryawan").innerHTML=this.responseText
			document.form1.nm_karyawan.value=this.responseText
		} else {
			document.getElementById("divNmKaryawan").innerHTML="-"
			document.form1.nm_karyawan.value="-"
		}
	}
}


function fGetCabang(){
	fGetNC(false,'20170900000010','kd_cabang','Ganti Karyawan',document.form1.fk_cabang,document.form1.fk_cabang)
	if (document.form1.fk_cabang.value !="")fGetCabangData()
}

function fGetCabangData(){
	document.getElementById("divnm_cabang").innerHTML=""
	document.form1.nm_cabang.value=""
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataCabangState
	lSentText="table=(select * from tblcabang left join tblwilayah on kd_wilayah = fk_wilayah) as tblcabang&field=(nm_cabang||'¿'||kd_wilayah||'¿'||nm_wilayah)&key=kd_cabang&value="+document.form1.fk_cabang.value
	//confirm(lSentText);
	lObjLoad.open("POST","ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}
function fGetDataCabangState(){
	if (this.readyState == 4){
		//confirm(this.responseText)
		if (this.status==200 && this.responseText!="") {
		lTemp=this.responseText.split('¿');
			//document.getElementById("divnm_cabang").innerHTML=this.responseText
			//document.form1.nm_cabang.value=this.responseText
			document.getElementById('divnm_cabang').innerHTML=document.form1.nm_cabang.value=lTemp[0]
			document.getElementById('divkd_wilayah').innerHTML=document.form1.kd_wilayah.value=lTemp[1]
			document.getElementById('divnm_wilayah').innerHTML=document.form1.nm_wilayah.value=lTemp[2]
		} else {
			//document.getElementById("divnm_cabang").innerHTML="-"
			//document.form1.nm_cabang.value="-"
			document.getElementById('divnm_cabang').innerHTML=document.form1.nm_cabang.value="-"
			document.getElementById('divkd_wilayah').innerHTML=document.form1.kd_wilayah.value="-"
			document.getElementById('divnm_wilayah').innerHTML=document.form1.nm_wilayah.value="-"
		}
	}
}



function fSave(){
	//if (cekError()) {
	document.form1.status.value='Save';
	document.form1.submit();
	//}
}

function fLoad(){
	//parent.parent.document.title="User";
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	}elseif($j_action){
		echo $j_action;
	}else{
		echo "document.form1.username.focus();";
	}
?>
}
</script>
<body onLoad="fLoad()" bgcolor="#fafafa">
<form action="modal_user.php" method="post" name="form1" enctype="multipart/form-data">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="status">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<td class="judul_menu" align="center">USER</td>
			</table>
		</td>
	</tr>
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<tr bgcolor="#efefef">
					<td style="padding:0 5 0 5" width="150"><font color="#990000">Username</font></td>
					<td style="padding:0 5 0 5" colspan="3">
					<? if ($id_edit=="") {?>                    
					<input type="text" name="username" size="90" value="<?=convert_html($username)?>" onKeyUp="fNextFocus(event,document.form1.password1)">		
					<? } else {
					?>
						<input type="hidden" name="username" value="<?=convert_html($username)?>"><?=convert_html($username)?>                    
					<?	
					}
					
					?>                    					
					</td>
				</tr>
				<tr bgcolor="#efefef">
					<td style="padding:0 5 0 5" width="150">Password</td>
					<td style="padding:0 5 0 5" colspan="3"><input type="password" name="password1" size="90" onKeyUp="fNextFocus(event,document.form1.password2)"></td>
				</tr>
				<tr bgcolor="#efefef">
					<td style="padding:0 5 0 5" width="150">Confirm Password</td>
					<td style="padding:0 5 0 5" colspan="3"><input type="password" name="password2" size="90" onKeyUp="fNextFocus(event,document.form1.level)"></td>
				</tr>
				<tr bgcolor="#efefef">
					<td style="padding:0 5 0 5" width="150"><font color="#990000">Level</font></td>
					<td style="padding:0 5 0 5" colspan="3">
						<? create_list_level();?>
					</td>
				</tr>
                
				<tr bgcolor="#efefef">
					<td style="padding:0 5 0 5" width="20%"><font color="#990000">NPK</font></td>
					<td style="padding:0 5 0 5" width="30%">
						<? if ($id_edit=="") {?>
						<input type="text" name="fk_karyawan" value="<?=convert_html($fk_karyawan)?>" class='groove_text' size="28" onKeyUp="fNextFocus(event,document.form1.is_active)" onChange="fGetKaryawanData()">&nbsp;<img src="images/search.gif" style="border:0px" align="absmiddle" onClick="fGetKaryawan()">
						<? } else {
						?>
						<input type="hidden" name="fk_karyawan" value="<?=convert_html($fk_karyawan)?>"><?=convert_html($fk_karyawan)?>
						<?
							}?>
					</td>
                    
                    <td style="padding:0 5 0 5" width="20%"><font color="#990000">Nama Karyawan</font></td>
					<td style="padding:0 5 0 5" width="30%">
						<input type="hidden" name="nm_karyawan" value="<?=convert_html($nm_karyawan)?>"><span id="divNmKaryawan"><?=convert_html($nm_karyawan)?></span>
					</td>
				</tr>
                
				<tr bgcolor="#efefef">
					<td style="padding:0 5 0 5" width="150"><font color="#990000">Jenis User</font></td>
					<td style="padding:0 5 0 5" colspan="3">
                    <select name="jenis_user" class="groove_text">
                    	<option value="">--Jenis--</option>
                        <option value="HO" <?=(($jenis_user=='HO')?'Selected':'')?>>HO</option>
                     	<option value="Wilayah" <?=(($jenis_user=='Wilayah')?'Selected':'')?>>Wilayah </option>
                     	<option value="Cabang" <?=(($jenis_user=='Cabang')?'Selected':'')?>>Cabang </option>
                     	<option value="Unit" <?=(($jenis_user=='Unit')?'Selected':'')?>>Unit </option>
                        <option value="Pos" <?=(($jenis_user=='Pos')?'Selected':'')?>>Pos </option>
                     </select>
					</td>
				</tr>
                
                <tr bgcolor="#efefef">
					<td style="padding:0 5 0 5" width="20%">Kode Cabang</td>
					<td width='30%' style="padding:0 5 0 5" valign="top"><input name="fk_cabang" type="text" class='groove_text ' size="20"  value="<?=$fk_cabang?>" onChange="fGetCabangData()">&nbsp;<img src="images/search.gif" style="border:0px" align="absmiddle" onClick="fGetCabang()">
                    </td>
                    <td style="padding:0 5 0 5" width="20%">Nama Cabang</td>
					<td style="padding:0 5 0 5" width="30%">
						<input type="hidden" name="nm_cabang" value="<?=convert_html($nm_cabang)?>"><span id="divnm_cabang"><?=convert_html($nm_cabang)?></span>
					</td>
				</tr>
                
                
                <tr bgcolor="#efefef">
					
                    
                    <td style="padding:0 5 0 5" width="20%">Kode Wilayah</td>
					<td style="padding:0 5 0 5" width="30%">
						<input type="hidden" name="kd_wilayah" value="<?=convert_html($kd_wilayah)?>"><span id="divkd_wilayah"><?=convert_html($kd_wilayah)?></span>
					</td>
                    
                    <td style="padding:0 5 0 5" width="20%">Nama Wilayah</td>
					<td style="padding:0 5 0 5" width="30%">
						<input type="hidden" name="nm_wilayah" value="<?=convert_html($nm_wilayah)?>"><span id="divnm_wilayah"><?=convert_html($nm_wilayah)?></span>
					</td>
				</tr>
                
                <tr bgcolor="#efefef">
					<td style="padding:0 5 0 5" width="150">Picture</td>
					<td style="padding:0 5 0 5" colspan="3">
						<input type="file" size="15" name="pic_profile" value="<?=convert_html($pic_profile)?>">
					</td>
				</tr>
                <? if ($id_edit) {?>
                <tr bgcolor="#efefef">
					<td style="padding:0 5 0 5">Active</td>
					<td style="padding:0 5 0 5" colspan="3"><input type="checkbox" name="is_active" value="t" onKeyUp="fNextFocus(event,document.form1.btnsubmit)" <?=(($is_active=="t")?"checked":"")?>></td>
				</tr>
                <? }?>
			</table>
		</td>
	</tr>
	<tr>
		<td height="25" align="center" bgcolor="#D0E4FF" class="border">
			<input type='button' name="btnsubmit" value='Save' onClick="fSave()">		
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?
function cek_error(){
	global $id_edit,$strmsg,$j_action,$username,$password1,$password2,$level,$fk_karyawan,$is_active,$pic_name,$jenis_user,$pic_profile,$fk_cabang,$fk_wilayah;

	if ($fk_cabang!="") {
		//showquery("select * from tblcabang where kd_cabang='".convert_sql($fk_cabang)."'");
		if (!pg_num_rows(pg_query("select * from tblcabang where kd_cabang='".convert_sql($fk_cabang)."'"))){
			$strmsg.="Cabang Tidak Terdaftar.<br>";
			if(!$j_action) $j_action="document.form1.fk_cabang.focus()";
		}
	}
	
	if($username==""){
		$strmsg.="Username Kosong.<br>";
		if(!$j_action) $j_action="document.form1.kd_user.focus()";
	} elseif ($id_edit=="") {
		if (pg_num_rows(pg_query("select * from tbluser where upper(username)='". strtoupper($username)."'"))){
			$strmsg.="Username sudah terdaftar dalam database.<br>";
			if(!$j_action) $j_action="document.form1.username.focus()";
		}
	} else {
		if (pg_num_rows(pg_query("select * from tbluser where upper(username)='". strtoupper($username)."' and kd_user<>'".$id_edit."'"))){
			$strmsg.="Username sudah terdaftar dalam database.<br>";
			if(!$j_action) $j_action="document.form1.username.focus()";
		}
	}
	if ($id_edit=="") {
		if($password1==""){
			$strmsg.="Password Kosong.<br>";
			if(!$j_action) $j_action="document.form1.password1.focus()";
		}
		if($password2==""){
			$strmsg.="Confirmation Password Kosong.<br>";
			if(!$j_action) $j_action="document.form1.password1.focus()";
		}
		if($password1!="" && $password2!=""){
			if($password1!=$password2){
				$strmsg.="Confirm Password tidak sesuai dengan Password.<br>";
				if(!$j_action) $j_action="document.form1.password1.focus()";
			}
		} 
	} else {
		if($password1!="" || $password2!=""){
			if($password1!=$password2){
				$strmsg.="Confirm Password tidak sesuai dengan Password.<br>";
				if(!$j_action) $j_action="document.form1.password1.focus()";
			}
		} elseif($password1!=$password2){
			$strmsg.="Confirm Password tidak sesuai dengan Password<br>";
			if(!$j_action) $j_action="document.form1.password1.focus()";
		}
	}
	if($level==""){
		$strmsg.="Level Kosong.<br>";
		if(!$j_action) $j_action="document.form1.level.focus()";
	} elseif (!pg_num_rows(pg_query("select * from tbllevel where kd_level='".convert_sql($level)."'"))) {
		$strmsg.="Level tidak ditemukan.<br>";
		if(!$j_action) $j_action="document.form1.level.focus()";
	}
	
	/*if($jenis_user==""){
		$strmsg.="Jenis User Kosong.<br>";
		if(!$j_action) $j_action="document.form1.jenis_user.focus()";
	} elseif (!pg_num_rows(pg_query("select * from tbllevel where kd_level='".convert_sql($level)."'"))) {
		$strmsg.="Level tidak ditemukan.<br>";
		if(!$j_action) $j_action="document.form1.level.focus()";
	}
	*/	
		
	if($jenis_user==""){
		$strmsg.="Jenis User Kosong.<br>";
		if(!$j_action) $j_action="document.form1.jenis_user.focus()";
	
	}
	else{
		if($jenis_user=='HO' && ($fk_cabang!="" || $fk_wilayah!="")){
			$strmsg.="Kode Cabang / Kode Wilayah tidak perlu di isi.<br>";
			if(!$j_action) $j_action="document.form1.jenis_user.focus()";
		}
		if($jenis_user=='Wilayah' &&  ($fk_cabang=="" && $fk_wilayah=="")){
			$strmsg.="Kode Cabang dan Kode Wilayah harus di isi.<br>";
			if(!$j_action) $j_action="document.form1.jenis_user.focus()";
		}
/*		else if($jenis_user=='Wilayah' &&  $fk_cabang!=""){	
			$strmsg.="Kode Cabang tidak perlu di isi.<br>";
			if(!$j_action) $j_action="document.form1.jenis_user.focus()";
		}
*/		if(($jenis_user=='Cabang' || $jenis_user=='Unit' || $jenis_user=='Pos') && ($fk_cabang=="")){
			$strmsg.="Kode Cabang harus di isi.<br>";
			if(!$j_action) $j_action="document.form1.jenis_user.focus()";
		}
		else if(($jenis_user=='Cabang' || $jenis_user=='Unit' || $jenis_user=='Pos')  &&  $fk_wilayah!=""){
			$strmsg.="Kode Wilayah tidak perlu di isi.<br>";
			if(!$j_action) $j_action="document.form1.jenis_user.focus()";
		}
	}
	/*if($jenis_user=="Cabang" && $password2=="Unit"){
		if($jenis_user!=$jenis_user){
			$strmsg.="Confirm Password tidak sesuai dengan Password.<br>";
			if(!$j_action) $j_action="document.form1.jenis_user.focus()";
		}
	} */
	
	
	if($fk_karyawan==""){
		$strmsg.="Karyawan Kosong.<br>";
		if(!$j_action) $j_action="document.form1.karyawan.focus()";
	} elseif (!pg_num_rows(pg_query("select * from tblkaryawan where npk='".convert_sql($fk_karyawan)."'"))) {
		$strmsg.="Karyawan tidak ada dalam database.<br>";
		if(!$j_action) $j_action="document.form1.karyawan.focus()";
	} elseif($id_edit=="") {
		/*if (pg_num_rows(pg_query("select * from tbluser where fk_karyawan='".convert_sql($fk_karyawan)."'"))) {
			$strmsg.="Karyawan sudah di assign ke user lain.<br>";
			if(!$j_action) $j_action="document.form1.karyawan.focus()";
		}*/
	}
	
	if($pic_name=="" && $id_edit==""){
		//$strmsg.="Picture Harus diisi.<br>";
		if(!$j_action) $j_action="document.form1.pic_profile.focus()";
	}
	
	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}


function create_list_level(){
    global $level;
    $l_list_obj = new select("select * from tbllevel where active is true  order by name","name","kd_level","level");
    $l_list_obj->set_default_value($level);
    $l_list_obj->add_item("-- Level ---",'',0);
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;' onKeyUp='fNextFocus(event,document.form1.karyawan)'");
}

function edit_data(){
	global $id_edit,$j_action,$strmsg,$username,$password1,$password2,$level,$fk_karyawan,$is_active,$upload_path_profile_pic,$pic_profile,$pic_name,$nm_karyawan,$jenis_user,$fk_cabang,$kd_wilayah;
	//echo $level;
	$l_success=1;
	pg_query("BEGIN");
	
	if($pic_name) $l_success = move_uploaded_file($_FILES['pic_profile']['tmp_name'], $upload_path_profile_pic."/".$_FILES['pic_profile']['name']);
	//echo $upload_path_profile_pic."/".$_FILES['pic_profile']['name'];
	//log begin
	if(!pg_query("insert into tbluser_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from tbluser where kd_user='".$id_edit."'")) $l_success=0;
	
	$l_id_log_ub=get_last_id("tbluser_log","pk_id_log");
	
	//showquery("insert into tbluser_menu_log select *,'".$l_id_log_ub."' from tbluser_menu where fk_user='".$id_edit."'");
	if(!pg_query("insert into tbluser_menu_log select *,'".$l_id_log_ub."' from tbluser_menu where fk_user='".$id_edit."'")) $l_success=0;
	//end log

	
	if($password1){
		if(!pg_query("update tbluser set username='".convert_sql($username)."',password='".convert_sql(crypt($password1))."',fk_level='".convert_sql($level)."',active='".$is_active."',jenis_user='".$jenis_user."',fk_cabang_user='".$fk_cabang."' where kd_user='".$id_edit."'")) $l_success=0;
	}else{
		if(!pg_query("update tbluser set username='".convert_sql($username)."',fk_level='".convert_sql($level)."',active='".$is_active."',jenis_user='".$jenis_user."',fk_cabang_user='".$fk_cabang."' where kd_user='".$id_edit."'")) $l_success=0;
	}
	
	if($pic_name){
		if(!pg_query("update tbluser set pic='".convert_sql($pic_name)."' where kd_user='".$id_edit."'")) $l_success=0;
	}
	$level_old=get_rec("tbluser","fk_level","kd_user='".$id_edit."'");
	if($level_old!=$level){

		if(!pg_query("delete from tbluser_menu where fk_user='".$id_edit."'")) $l_success=0;
		else {
			$l_res=pg_query("select * from tbllevel_menu where fk_level='".$level."'");
			//showquery("select * from tbllevel_menu where fk_level='".$level."'");
			while($lrow=pg_fetch_array($l_res)){
				if(!pg_query("insert into tbluser_menu(fk_user,menu_id) values('".$id_edit."','".$lrow["menu_id"]."')")) $l_success=0;
				//showquery("insert into tbluser_menu(fk_user,menu_id) values('".$id_edit."','".$lrow["menu_id"]."')");
				
			}
		}
	}
			
	//log begin
	if(!pg_query("insert into tbluser_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from tbluser where kd_user='".$id_edit."'")) $l_success=0;
	
	$l_id_log_ua=get_last_id("tbluser_log","pk_id_log");

	if(!pg_query("insert into tbluser_menu_log select *,'".$l_id_log_ua."' from tbluser_menu where fk_user='".$id_edit."'")) $l_success=0;
	//end log
	
	//$l_success=0;
	if ($l_success==1){
		$strmsg="User saved.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		pg_query("COMMIT");
	}else{
		$strmsg.="Error:<br>User save failed.<br>";
		pg_query("ROLLBACK");
	}
}

function add_data(){
	global $j_action,$strmsg,$username,$password1,$password2,$level,$fk_karyawan,$is_active,$pic_profile,$pic_name,$upload_path_profile_pic,$nm_karyawan,$jenis_user,$fk_cabang,$kd_wilayah;

	$l_success=1;
	pg_query("BEGIN");

	if($pic_name) $l_success = move_uploaded_file($_FILES['pic_profile']['tmp_name'], $upload_path_profile_pic."/".$_FILES['pic_profile']['name']);
		

	if(!pg_query("insert into tbluser(username,password,fk_level,fk_karyawan,pic,jenis_user,fk_cabang_user) values('".convert_sql($username)."','".convert_sql(crypt($password1))."','".$level."','".$fk_karyawan."','".$pic_name."','".$jenis_user."','".$fk_cabang."')")) $l_success=0;

	$l_pk_id_user=get_last_id("tbluser","kd_user");

	$lrs=pg_query("select * from tbllevel_menu where fk_level='".$level."'");
	while($lrow=pg_fetch_array($lrs)){
		//showquery("insert into tbluser_menu(fk_user,menu_id) values('".$l_pk_id_user."','".$lrow["menu_id"]."')");
		if(!pg_query("insert into tbluser_menu(fk_user,menu_id) values('".$l_pk_id_user."','".$lrow["menu_id"]."')")) $l_success=0;
	}

	//log begin
	if(!pg_query("insert into tbluser_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from tbluser where kd_user='".$l_pk_id_user."'")) $l_success=0;
	
	$l_id_log_ub=get_last_id("tbluser_log","pk_id_log");

	//showquery("insert into tbluser_menu_log select *,'".$l_id_log_ub."' from tbluser_menu where fk_user='".$l_pk_id_user."'");
	if(!pg_query("insert into tbluser_menu_log select *,'".$l_id_log_ub."' from tbluser_menu where fk_user='".$l_pk_id_user."'")) $l_success=0;
	//end log

	//$l_success=0;
	if ($l_success==1){
		$username="";
		$password1="";
		$password2="";
		$level="";
		$fk_karyawan="";
		$nm_karyawan="";
		$jenis_user="";
		$is_active="f";
		$strmsg="User saved.<br>";
		pg_query("COMMIT");
	}else{
		$strmsg="Error:<br>User save failed.<br>";
		pg_query("ROLLBACK");
	}
}

function get_data(){
	global $id_edit,$username,$strsaldo_awal,$level,$nm_karyawan,$is_active,$fk_karyawan,$jenis_user,$nm_cabang,$fk_cabang,$kd_wilayah,$nm_wilayah;

	
	$lrow=pg_fetch_array(pg_query("select * from tbluser 
									left join tblcabang on fk_cabang_user = kd_cabang 
									left join tblwilayah on kd_wilayah = fk_wilayah
								  where kd_user='".$id_edit."'"));
	$username=$lrow["username"];
	$level=$lrow["fk_level"];
	//echo $level;
	$fk_karyawan=$lrow["fk_karyawan"];
	$jenis_user=$lrow["jenis_user"];
	$fk_cabang=$lrow["fk_cabang_user"];
	$nm_cabang=$lrow["nm_cabang"];
	$kd_wilayah=$lrow["kd_wilayah"];
	$nm_wilayah=$lrow["nm_wilayah"];
	$lrow_karyawan=pg_fetch_array(pg_query("select (nm_depan||' '|| case when nm_belakang is null then '' else nm_belakang end) as nm_karyawan from tblkaryawan where npk='".$fk_karyawan."'"));
	$nm_karyawan=$lrow_karyawan["nm_karyawan"];
	$is_active=$lrow["active"];

}
?>