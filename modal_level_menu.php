<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/numeric.inc.php';
require 'requires/db_utility.inc.php';
require 'classes/select.class.php';

$id_edit=$_REQUEST["id_edit"];
//if ($id_edit=="") check_right("10131010");
//else check_right("10131011");

$nm_level=trim($_REQUEST["nm_level"]);
$strmenu=$_REQUEST["strmenu"];

if($_REQUEST["status"]=="Save") {
	if ($id_edit) edit_data();
	//else add_data();
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
<script language='javascript'>
function fcheck(pID,j_object){
	for(var i=0;i<document.form1.strcek.length;i++){
		if(document.form1.strcek[i].value.substr(0,j_object.value.length)==pID){
			document.form1.strcek[i].checked=j_object.checked;
		}
	}
	if(j_object.checked==true){
		fcheckup_true(j_object);
	}/*else{
		fcheckup_false(j_object);
	}*/
}

function fcheckup_true(j_object){
	l_parent_value = j_object.value.substr(0,j_object.value.length-2)
	for(var i=0;i<document.form1.strcek.length;i++){
		if(document.form1.strcek[i].value==l_parent_value){
			document.form1.strcek[i].checked=true;
			fcheckup_true(document.form1.strcek[i]);
			break;
		}
	}
}
function fcheckup_false(j_object){
	parent_object = "";
	sama=true;
	l_parent_value = j_object.value.substr(0,j_object.value.length-2)
	for(var i=0;i<document.form1.strcek.length;i++){
		if(document.form1.strcek[i].value==l_parent_value){
			parent_object=document.form1.strcek[i];
		}
		if(document.form1.strcek[i].value.substr(0,document.form1.strcek[i].value.length-2)==l_parent_value){
			if(document.form1.strcek[i].checked!=j_object.checked){
				sama=false;
				break;
			}
		}
	}
	if(sama==true){
		if(parent_object!="") parent_object.checked=false;
		l_parent_value = l_parent_value.substr(0,l_parent_value.length-2);
		if (l_parent_value!="")
			fcheckup_false(parent_object);
	}
}

function fcheckall(p_value){
	for(var i=0;i<document.form1.strcek.length;i++){
		document.form1.strcek[i].checked=p_value;
	}
}

function fconcat(){
	document.form1.strmenu.value='';
	for(var i=0;i<document.form1.strcek.length;i++){
		x=document.form1.strcek[i].value.split('-');
		if(document.form1.strcek[i].value!='all'){
			if(document.form1.strcek[i].checked==true){
				document.form1.strmenu.value+=x[0]+',';
			}
		}
	}
}

function cekError(){
	var lAlerttxt="";
	var lFocusbox=false;
	var lFocuscursor="";

	if (document.form1.nm_level.value==""){
		lAlerttxt+='Nama Level Kosong<br>';
		if(lFocusbox==false){lFocuscursor=document.form1.nm_level;lFocusbox=true;}
	}
	if(lAlerttxt!=""){
		alert("Error : <br>"+lAlerttxt);
		lFocuscursor.focus();
		return false
	} else return true;
}

function fSave(){
	//alert('test')
	if (cekError()) {
		document.form1.status.value='Save';
		document.form1.submit();
	}
}

function fLoad(){
	//parent.parent.document.title="Level";
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
<form action="modal_level_menu.php" method="post" name="form1">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="strmenu" value="<?=$strmenu?>">
<input type="hidden" name="status">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<tr><td class="judul_menu" align="center">LEVEL</td></tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<?
				if ($id_edit!="") {
				?>
				<tr bgcolor="#efefef">
					<td style="padding:0 5 0 5" width="150">Kode Level</td>
					<td style="padding:0 5 0 5"><?=$id_edit?></td>
				</tr>
				<?
				}
				?>
				<tr bgcolor="#efefef">
					<td style="padding:0 5 0 5" width="150"><font color="#990000">Nama Level</font></td>
					<td style="padding:0 5 0 5"><input type="hidden" name="nm_level" size="90" value="<?=convert_html($nm_level)?>" onKeyUp="#"><?=$nm_level?></td>
				</tr>
            </table>
			<div style="background:#efefef;padding:0 5 0 5;border-bottom:solid 1px #FFFFFF">Menu Setting &nbsp;&nbsp;&nbsp;<input type="button" name="btnSellectAll" value="Select All" onClick="fcheckall(true)"> <input type="button" name="btnUnsellectAll" value="Unselect All" onClick="fcheckall(false)"></div>
			<div style='width:100%;height:465;overflow:auto;'>
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr bgcolor="#efefef">
					<td><? create_menu()?></td>
				</tr>
			</table>
			</div>
		</td>
	</tr>
	<tr>
		<td height="25" bgcolor="#D0E4FF" class="border">
			<input type='button' name="btnsubmit" value='Save' onClick="fconcat();fSave()">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?
function create_menu(){
	get_child("null",0,2);
}

function get_child($f_id,$p_space,$p_length){
	global $strmenu,$id_edit;
	
	if($f_id=="null"){
		$lstrsql = "SELECT * FROM skeleton.tblmenu where char_length(kd_menu)=2 and is_hidden is TRUE order by kd_menu";
	}else{
		$lstrsql = "SELECT * FROM skeleton.tblmenu where char_length(kd_menu)=".$p_length." and kd_menu like '".$f_id."%' and is_hidden is TRUE order by kd_menu";
	}
    $l_rs = pg_query($lstrsql);
    while ($lrow = pg_fetch_array($l_rs)){
		for ($l_index=1;$l_index<=$p_space;$l_index++){
			echo "&nbsp;";
		}
			if(pg_fetch_array(pg_query("select * from tbllevel_menu where fk_level='".$id_edit."' and menu_id='".$lrow["kd_menu"]."'"))){
				echo "<input type='checkbox' name='strcek' value='".$lrow['kd_menu']."' style='border=0;background=none' onclick=fcheck('$lrow[kd_menu]',this) checked>";
			}else{
				echo "<input type='checkbox' name='strcek' value='".$lrow['kd_menu']."' style='border=0;background=none' onclick=fcheck('$lrow[kd_menu]',this)>";
			}
		echo $lrow["nama_menu"]."<br>";
		get_child($lrow['kd_menu'],$p_space + 5,$p_length+2);
    }
}

function edit_data(){
	global $id_edit,$j_action,$strmsg,$nm_level,$strmenu;

	$l_success=1;
	pg_query("BEGIN");
	
	//log begin
	if(!pg_query("insert into tbllevel_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from tbllevel where kd_level='".$id_edit."'")) $l_success=0;
	
	$l_id_log_ub=get_last_id("tbllevel_log","pk_id_log");

	if(!pg_query("insert into tbllevel_menu_log select *,'".$l_id_log_ub."' from tbllevel_menu where fk_level='".$id_edit."'")) $l_success=0;
	//end log
	
	//log begin
	if(!pg_query("delete from tbllevel_menu where fk_level='".$id_edit."'")) $l_success=0;
	
	$arrmenu=split(",",$strmenu);
	for($i=0;$i<count($arrmenu)-1;$i++){
		if(!pg_affected_rows(pg_query("insert into tbllevel_menu (menu_id,fk_level) values('".$arrmenu[$i]."','".$id_edit."')"))) $l_success=0;
	}
	
	if(!pg_query("insert into tbllevel_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from tbllevel where kd_level='".$id_edit."'")) $l_success=0;

	$l_id_log_ua=get_last_id("tbllevel_log","pk_id_log");

	if(!pg_query("insert into tbllevel_menu_log select *,'".$l_id_log_ua."' from tbllevel_menu where fk_level='".$id_edit."'")) $l_success=0;
	//end log

	if ($l_success==1){
		$strmsg="Level Tersimpan.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		pg_query("COMMIT");
	}else{
		$strmsg="Error :<br>Level Gagal Tersimpan.<br>";
		pg_query("ROLLBACK");
	}
}
/*
function add_data(){
	global $j_action,$strmsg,$nm_level,$strmenu,$investasi,$marketing,$perbaikan;

	$l_success=1;
	pg_query("BEGIN");

	$investasi=$marketing=$perbaikan=5;
	
	if(!pg_query("insert into tbllevel(nm_level,investasi,marketing,perbaikan) values('".convert_sql($nm_level)."','".convert_sql($investasi)."','".convert_sql($marketing)."','".convert_sql($perbaikan)."')")) $l_success=0;

	$lid_level=get_last_id("tbllevel","kd_level");

	$arrmenu=split(",",$strmenu);
	for($i=0;$i<count($arrmenu)-1;$i++){
		if(!pg_affected_rows(pg_query("insert into tbllevel_menu (menu_id,fk_level) values('".$arrmenu[$i]."','".$lid_level."')"))) $l_success=0;
	}

	
	//log begin
	if(!pg_query("insert into tbllevel_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from tbllevel where kd_level='".$lid_level."'")) $l_success=0;
	
	$l_id_log_ub=get_last_id("tbllevel_log","pk_id_log");

	if(!pg_query("insert into tbllevel_menu_log select *,'".$l_id_log_ub."' from tbllevel_menu where fk_level='".$lid_level."'")) $l_success=0;
	//end log
	
	$cabang=pg_query("select * from tblcabang");
	while($lrow_cabang=pg_fetch_array($cabang)){
		//workflow_investasi
		if(!pg_affected_rows(pg_query("insert into tblworkflow_investasi (fk_level,fk_cabang) values('".$lid_level."','".$lrow_cabang["kd_cabang"]."')"))) $l_success=0;
		
		//log begin
		if(!pg_query("insert into tblworkflow_investasi_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from tblworkflow_investasi where fk_level='".$lid_level."' and fk_cabang='".$lrow_cabang["kd_cabang"]."'")) $l_success=0;
		//log end
		
		//workflow_marketing
		if(!pg_affected_rows(pg_query("insert into tblworkflow_marketing (fk_level,fk_cabang) values('".$lid_level."','".$lrow_cabang["kd_cabang"]."')"))) $l_success=0;
		//log begin
		if(!pg_query("insert into tblworkflow_marketing_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from tblworkflow_marketing where fk_level='".$lid_level."' and fk_cabang='".$lrow_cabang["kd_cabang"]."'")) $l_success=0;
		//log end
		
		//workflow_perbaikan
		if(!pg_affected_rows(pg_query("insert into tblworkflow_perbaikan (fk_level,fk_cabang) values('".$lid_level."','".$lrow_cabang["kd_cabang"]."')"))) $l_success=0;
		//log begin
		if(!pg_query("insert into tblworkflow_perbaikan_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from tblworkflow_perbaikan where fk_level='".$lid_level."' and fk_cabang='".$lrow_cabang["kd_cabang"]."'")) $l_success=0;
		//log_end
	}
	
	if ($l_success==1){
		$nm_level="";
		$strmsg="Level Tersimpan.<br>";
		pg_query("COMMIT");
	}else{
		$strmsg="Error :<br>Level Gagal Tersimpan.<br>";
		pg_query("ROLLBACK");
	}
}
*/
function get_data(){
	global $id_edit,$nm_level;

	$lrow=pg_fetch_array(pg_query("select * from tbllevel where kd_level='".$id_edit."'"));
	$nm_level=$lrow["name"];
}
?>