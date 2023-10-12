<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/numeric.inc.php';
require 'requires/db_utility.inc.php';

$id_edit=$_REQUEST["id_edit"];
//check_right("10131013");

$nm_level=$_REQUEST["nm_level"];
$strmenu=$_REQUEST["strmenu"];

if($_REQUEST["status"]=="Save") {
	cek_error();
	if(!$strmsg){
		save_data();
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
</head>
<script language='javascript' src="js/alert.js.php"></script>
<script language='javascript' src='js/openwindow.js.php'></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language="javascript">
function fconcat(){
	document.form1.strmenu.value='';
	if (document.form1.strcek.length) {
		for(var i=0;i<document.form1.strcek.length;i++){
			if(document.form1.strcek[i].checked==true){
				document.form1.strmenu.value+=document.form1.strcek[i].value+',';
			}
		}
	} else {
		if(document.form1.strcek.checked==true){
			document.form1.strmenu.value+=document.form1.strcek.value;
		}
	}
}

function cekError(){
	return true;
}

function fcheckall(p_value){
	if (document.form1.strcek.length) {
		for(var i=0;i<document.form1.strcek.length;i++){
			document.form1.strcek[i].checked=p_value;
		}
	} else {
		document.form1.strcek.checked=p_value;
	}
}

function fSave(){
	//alert('test')
	if (cekError()) {
		document.form1.status.value='Save';
		document.form1.submit();
	}
}

function fLoad(){
	//parent.parent.document.title="User";
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	} elseif($j_action){
		echo $j_action;
	}else{
		echo "document.form1.nm_level.focus();";
	}
?>
}
</script>
<body onLoad="fLoad()" bgcolor="#fafafa">
<form action="modal_level_apply.php" method="post" name="form1">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="strmenu" value="<?=$strmenu?>">
<input type="hidden" name="status">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<td align="center" bgcolor="#D0E4FF" class="judul_menu">&nbsp;</td>
			</table>
		</td>
	</tr>
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<tr bgcolor="#efefef">
					<td style="padding:0 5 0 5" width="20%"><font color="#990000">Kode Level</font></td>
					<td style="padding:0 5 0 5" width="30%"><?=convert_html($id_edit)?></td>
					<td style="padding:0 5 0 5" width="20%"><font color="#990000">Nama Level</font></td>
					<td style="padding:0 5 0 5" width="30%"><input type="hidden" name="nm_level" value="<?=convert_html($nm_level)?>"><?=convert_html($nm_level)?></td>
				</tr>
			</table>
			<div style="background:#c8c8c8;padding:0 5 0 5;border-bottom:solid 1px #FFFFFF">
				<input type="button" name="btnSellectAll" value="Select All" onClick="fcheckall(true)"> <input type="button" name="btnUnsellectAll" value="Unselect All" onClick="fcheckall(false)">
			</div>
			<div style='width:790px;height:80;overflow:auto;'>
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr bgcolor="#efefef">
					<td><? create_list_apply()?></td>
				</tr>
			</table>
			</div>
		</td>
	</tr>
	<tr>
		<td height="25" align="center" bgcolor="#D0E4FF" class="border">
			<input type="submit" value="Save" class="groove_button" onClick="fconcat();fSave()">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?
function cek_error(){
	global $id_edit,$strmsg,$j_action,$strmenu;

	if ($strmenu=="") {
		$strmsg.="Tidak pilihan user yang akan di apply.<br>";
	}
	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}

function create_list_apply(){
	global $id_edit;
	$lrs=pg_query("select * from tbluser where fk_level='".$id_edit."'");
	while ($lrow=pg_fetch_array($lrs)) {
		echo "<input type='checkbox' name='strcek' value='".$lrow['kd_user']."' style='border=0;background=none'>&nbsp;".convert_html($lrow["username"]);
	}
}

function get_data(){
	global $id_edit,$nm_level;

	$lrow=pg_fetch_array(pg_query("select * from tbllevel where kd_level='".$id_edit."'"));
	$nm_level=$lrow["name"];
}

function save_data(){
	global $id_edit,$j_action,$strmsg,$strmenu;

	$l_success=1;
	pg_query("BEGIN");
	
	$l_strmenu=str_replace(",","','",$strmenu);
	
	//log begin
	if(!pg_query("insert into tbluser_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from tbluser where kd_user in ('".$l_strmenu."')")) $l_success=0;
	
	$l_id_log_ub=get_last_id("tbluser_log","pk_id_log");

	if(!pg_query("insert into tbluser_menu_log select *,'".$l_id_log_ub."' from tbluser_menu where fk_user in ('".$l_strmenu."')")) $l_success=0;
	//end log
	
	if(!pg_query("delete from tbluser_menu where fk_user in ('".$l_strmenu."')")) $l_success=0;
	$l_arr_user=split(",",$strmenu);
	if (count($l_arr_user)>2) {
		foreach ($l_arr_user as $l_user) {
			if(!pg_query("insert into tbluser_menu (fk_user,menu_id) select '".$l_user."',menu_id from tbllevel_menu where fk_level='".convert_sql($id_edit)."'")) $l_success=0;
		}
	} else {
		$strmenu = str_replace(",","",$strmenu);
		if(!pg_query("insert into tbluser_menu (fk_user,menu_id) select '".convert_sql($strmenu)."',menu_id from tbllevel_menu where fk_level='".convert_sql($id_edit)."'")) $l_success=0;
	}

	//log begin
	if(!pg_query("insert into tbluser_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from tbluser where kd_user in ('".$l_strmenu."')")) $l_success=0;

	$l_id_log_ua=get_last_id("tbluser_log","pk_id_log");

	if(!pg_query("insert into tbluser_menu_log select *,'".$l_id_log_ua."' from tbluser_menu where fk_user in ('".$l_strmenu."')")) $l_success=0;
	//end log

	if ($l_success==1){
		$strmsg="Settingan pada level telah berhasil di apply ke user.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		pg_query("COMMIT");
	}else{
		$strmsg="Error :<br>Settingan pada level tidak berhasil di apply ke user.<br>";
		pg_query("ROLLBACK");
	}
}
?>
