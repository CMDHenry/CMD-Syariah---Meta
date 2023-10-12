<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/db_utility.inc.php';
require './requires/numeric.inc.php';
require './requires/text.inc.php';
require './requires/timestamp.inc.php';
require 'classes/select.class.php';

$id_edit=trim($_REQUEST["id_edit"]);
if(!$id_edit)

//check_right("HO10141010");
//else check_right("HO10141011");

$code=trim($_REQUEST["code"]);
$code1=trim($_REQUEST["code1"]);
$is_laba_rugi=trim($_REQUEST["is_laba_rugi"]);
if ($is_laba_rugi=="") $is_laba_rugi="f";
$is_neraca=trim($_REQUEST["is_neraca"]);
if ($is_neraca=="") $is_neraca="f";
$description=trim($_REQUEST["description"]);
$type_saldo=trim($_REQUEST["type_saldo"]);

if($_REQUEST["status"]=="Save") {
	cek_error();
	if(!$strmsg){
		if ($id_edit) edit_data();
		else save_data();
	}
}
if($_REQUEST["pstatus"]){
	get_data();
}
?>
<html>
<head>
	<title>.: SUKA FAJAR :.</title>
	<link href="css/cwcalendar.css.php" rel="stylesheet" type="text/css">
    <link href="css/text.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src="js/alert.js.php"></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src="js/validate.js.php"></script>
<script language='javascript'>

function cekError(){
	var lAlerttxt="";
	var lFocuscursor="";
	if (document.form1.code.value==""){
		lAlerttxt+='Code Kosong<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.code";}
	}
	if (document.form1.type_saldo.value=="--Pilih Saldo Type--"){
		lAlerttxt+='Pilih Saldo Type<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.type_saldo";}
	}

	if(lAlerttxt!=""){
		alert("Error : <br>"+lAlerttxt,function(){eval(lFocuscursor+'.focus()')});
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

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")) {
		lInputClose=getObjInputClose();lInputClose.close()
	}
}

function fLoad(){
	//parent.parent.document.title="Head Account";
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function () {'.$j_action.'});';
	}
	else if($j_action){
		echo $j_action;
	}else{
		if ($id_edit=="") echo "document.form1.code.focus();";
		else echo "document.form1.description.focus();";
	}
?>
}
</script>
<body onLoad="fLoad();document.form1.autocomplete='off'" bgcolor="#fafafa">
<form action="modal_head_account.php" method="post" name="form1">
<input type="hidden" name="status">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<td class="judul_menu" align="center">HEAD ACCOUNT</td>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border" style="border-bottom:none">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
            	<?
				if($id_edit!=""){
				?>
                <tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5"width="20%" class="fontColor">Code</td>
					<td  style="padding:0 5 0 5"width="30%" colspan="3"><input type="hidden" name="code" value="<?=convert_html($code)?>" size="12" onKeyUp="fNextFocus(event,document.form1.is_laba_rugi)"><?=convert_html($code)?></td>
				</tr>
                <? } else { ?>
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5"width="20%" class="fontColor">Code</td>
					<td  style="padding:0 5 0 5"width="30%" colspan="3">
                    <input type="text" name="code" value="<?=convert_html($code)?>" size="5" maxlength="5" onKeyUp="fNextFocus(event,document.form1.is_laba_rugi)">
                    
<!--                    <input type="text" name="code1" value="<?=convert_html($code1)?>" size="1" maxlength="2" onKeyUp="fNextFocus(event,document.form1.is_laba_rugi)">
-->                    </td>
				</tr>
                <? } ?>
<!--				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5"width="20%">Laba/Rugi Detail</td>
					<td  style="padding:0 5 0 5"width="30%" colspan="3"><input type="checkbox" name="is_laba_rugi" class="groove_text" value="t" <?=(($is_laba_rugi=="t")?"checked disable":"")?> onKeyUp="fNextFocus(event,document.form1.is_neraca)"></td>
				</tr>
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5"width="20%">Neraca Detail</td>
					<td  style="padding:0 5 0 5"width="30%" colspan="3">
					<input type="checkbox" name="is_neraca" class="groove_text" value="t" <?=(($is_neraca=="t")?"checked disable":"")?> onKeyUp="fNextFocus(event,document.form1.description)"></td>
				</tr>
-->				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5"width="20%">Description</td>
					<td  style="padding:0 5 0 5"width="30%" colspan="3"><input type="text" name="description" class="groove_text" size="97" value="<?=convert_html($description)?>" onKeyUp="fNextFocus(event,document.form1.type_saldo)"></td>
				</tr>
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5"width="20%" class="fontColor">Saldo Type</td>
					<td  style="padding:0 5 0 5"width="30%" colspan="3"><select name="type_saldo" class="groove_text" onKeyUp="fNextFocus(event,document.form1.btnsimpan)">
                    <option>--Pilih Saldo Type--</option>
					<option value="Rollover" <?=(($type_saldo=="Rollover")?"selected":"")?>>Rollover</option>
					<option value="Non Rollover" <?=(($type_saldo=="Non Rollover")?"selected":"")?>>Non Rollover</option></select>
					</td>
				</tr>
            </table>
		</td>
	</tr>
	<tr height="20"><td height="25" align="center" bgcolor="#D0E4FF" class="border">
		<input class="groove_button" name="btnsimpan" type='button' value='Simpan' onClick="fSave()">
		&nbsp;<input class="groove_button" type="button" value="Batal" onClick="fBatal()"></td>
	</tr>
</table>
</form>
</body>
</html>
<?
function cek_error(){
	global $strmsg,$j_action,$code,$code1,$is_laba_rugi,$is_neraca,$description,$type_saldo,$id_edit;
	
	if($id_edit==""){
		//|| $code1==''
		if($code=="" ){
			$strmsg.="Code Kosong.<br>";
			if(!$j_action) $j_action="document.form1.code.focus()";
		}elseif(strlen($code)!=5 || check_type(integer,$code)){
			$strmsg.="Code Harus 5 Digit.<br>";
			if(!$j_action) $j_action="document.form1.code.focus()";
		}elseif(pg_num_rows(pg_query("select * from tblhead_account where code='".$code."' for update"))){
			$strmsg.="Code Sudah Terdaftar.<br>";
			if(!$j_action) $j_action="document.form1.code.focus()";
		}
	}else if($id_edit!=""){
		if(pg_num_rows(pg_query("select * from tblhead_account where code='".$id_edit."' and code<>'".$id_edit."' for update"))){
			$strmsg.="Code Sudah Terdaftar.<br>";
			if(!$j_action) $j_action="document.form1.code.focus()";
		}	
	}

	if($type_saldo=="--Pilih Saldo Type--"){
		$strmsg.="Pilih Type Kosong.<br>";
		if(!$j_action) $j_action="document.form1.type_saldo.focus()";
	}

	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}
function edit_data(){
	global $strmsg,$j_action,$code,$is_laba_rugi,$is_neraca,$description,$type_saldo,$id_edit;

	$l_success=1;
	pg_query("BEGIN");
	
	//log begin
	if(!pg_query("insert into tblhead_account_log select *,'".$_SESSION["ho_id"]."','".$_SESSION["ho_username"]."','#".date("Y/m/d H:i:s")."#','UB' from tblhead_account where code='".$id_edit."'")) $l_success=0;
	//end log
	
	if(!pg_query("update tblhead_account set is_laba_rugi='".convert_sql($is_laba_rugi)."',is_neraca='".convert_sql($is_neraca)."',description='".convert_sql($description)."',type_saldo='".convert_sql($type_saldo)."' where code='".$id_edit."'")) $l_success=0;
	//log begin
	if(!pg_query("insert into tblhead_account_log select *,'".$_SESSION["ho_id"]."','".$_SESSION["ho_username"]."','#".date("Y/m/d H:i:s")."#','UA' from tblhead_account where code='".$id_edit."'")) $l_success=0;
	//end log

	if ($l_success==1){
		$strmsg="Head Account Tersimpan.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		pg_query("COMMIT");
	}else{
		$strmsg="Error :<br>Head Account Gagal Tersimpan.<br>";
		pg_query("ROLLBACK");
	}
}
function save_data(){
	global $strmsg,$j_action,$code,$code1,$is_laba_rugi,$is_neraca,$description,$type_saldo;

	$l_success=1;
	pg_query("BEGIN");

	if(!pg_query("insert into tblhead_account (code,is_laba_rugi,is_neraca,description,type_saldo) values('".convert_sql($code.".".$code1)."','".convert_sql($is_laba_rugi)."','".convert_sql($is_neraca)."','".convert_sql($description)."','".convert_sql($type_saldo)."')")) $l_success=0;
	//showquery("insert into tblhead_account (code,is_laba_rugi,is_neraca,description,type_saldo) values('".convert_sql($code.".".$code1)."','".convert_sql($is_laba_rugi)."','".convert_sql($is_neraca)."','".convert_sql($description)."','".convert_sql($type_saldo)."')");
	$lid_head_account = $code.".".$code1;

	//log begin
	if(!pg_query("insert into tblhead_account_log select *,'".$_SESSION["ho_id"]."','".$_SESSION["ho_username"]."','#".date("Y/m/d H:i:s")."#','IA' from tblhead_account where code='".$lid_head_account."'")) $l_success=0;
	//end log
	//$l_success=0;
	if($l_success==1) {
		$strmsg = "Head Account Tersimpan.<br>";
		$code="";
		$code1="";
		$is_laba_rugi="";
		$is_neraca="";
		$description="";
		$type_saldo="";
		pg_query("COMMIT");
	} else {
		$strmsg = "Error:<br>Head Account Gagal Tersimpan.<br>";
		pg_query("ROLLBACK");
	}
}

function get_data(){
	global $strmsg,$j_action,$code,$is_laba_rugi,$is_neraca,$description,$type_saldo,$id_edit;
	
	$lrow=pg_fetch_array(pg_query("select * from tblhead_account where code='".$id_edit."'"));

	$code=$lrow["code"];
	$is_laba_rugi=$lrow["is_laba_rugi"];
	$is_neraca=$lrow["is_neraca"];
	$description=$lrow["description"];
	$type_saldo=$lrow["type_saldo"];
	
}
?>
