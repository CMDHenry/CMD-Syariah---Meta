<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/db_utility.inc.php';
require './requires/numeric.inc.php';
require './requires/text.inc.php';
require './requires/timestamp.inc.php';
require 'classes/select.class.php';


$id_edit			= trim($_REQUEST["id_view"]);

//check_right("HO101311");

$begin				= trim($_REQUEST["begin"]);
$end		 		= trim($_REQUEST["end"]);
$fk_group			= trim($_REQUEST["fk_group"]);
$used_for			= trim($_REQUEST["used_for"]);
$radio_used_for		= trim($_REQUEST["radio_used_for"]);
$arr_used_for 		= trim($_REQUEST["arr_used_for"]);

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

	if (document.form1.begin.value==""){
		lAlerttxt+='Begin Range Kosong<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.begin";}
	}else if(isNaN(document.form1.begin.value)){
		lAlerttxt+='Begin Range Harus Angka<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.begin";}
	}else if(document.form1.begin.value.length!=3){
		lAlerttxt+='Begin Range Harus Diinput 3 Digit Angka<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.begin";}
	}
	
	if (document.form1.end.value==""){
		lAlerttxt+='End Range Kosong<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.end";}
	}else if(isNaN(document.form1.end.value)){
		lAlerttxt+='End Range Harus Angka<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.end";}
	}else if(document.form1.end.value.length!=3){
		lAlerttxt+='End Range Harus Diinput 3 Digit Angka<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.end";}
	}
	
	if(document.form1.begin.value!="" && document.form1.end.value!=""){
		if(!isNaN(document.form1.begin.value) && !isNaN(document.form1.end.value)){
			if(document.form1.begin.value.length==3 && document.form1.end.value.length==3){
				if(document.form1.end.value < document.form1.begin.value){
					lAlerttxt+='End Range Lebih Kecil dari Begin Range<br>';
					if(lFocuscursor==""){lFocuscursor="document.form1.end";}
				}
			}
		}
	}
	
	if (document.form1.fk_group.value==""){
		lAlerttxt+='Group Kosong<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.fk_group";}
	}
	if(lAlerttxt!=""){
		alert("Error : <br>"+lAlerttxt,function(){eval(lFocuscursor+'.focus()')});
		return false
	} else return true;
}

function fSave(){
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

function fReset(){
	if(document.form1.radio_used_for.length > 0)for(i=0;i < document.form1.radio_used_for.length;i++)document.form1.radio_used_for[i].checked=false
	document.form1.used_for.value = ''
	document.form1.rk_dg_cabang.value = ''
}
function fPilih(pObj){
	if(pObj.checked == true){
		document.form1.arr_used_for.value += pObj.value+",";
		document.form1.used_for.value = pObj.value
		document.form1.rk_dg_cabang.value = ''
	}else if(pObj.checked == false){
		document.form1.arr_used_for.value = document.form1.arr_used_for.value.replace(pObj.value+",","")
	}
	//confirm(document.form1.arr_used_for.value);
}
function fLoad(){
	//parent.parent.document.title="Group Account";
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function(){ '.$j_action.' });';
	}
	else if($j_action){
		echo $j_action;
	}else{
		echo "document.form1.begin.focus();";
	}
?>
}
</script>
<body onLoad="fLoad();document.form1.autocomplete='off'" bgcolor="#fafafa">
<form action="modal_group_account_view.php" method="post" name="form1">
<input type="hidden" name="status">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="used_for" value="<?=$used_for?>">
<input type="hidden" name="rk_dg_cabang" value="<?=$rk_dg_cabang?>">
<input type="hidden" name="arr_used_for" value="<?=$arr_used_for?>">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<td class="judul_menu" align="center">GROUP ACCOUNT</td>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border" style="border-bottom:none">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5"width="20%">Begin</td>
					<td  style="padding:0 5 0 5"width="30%"><input type="hidden" size="20" name="begin" maxlength="3" class="groove_text" value="<?=$begin?>" onKeyUp="fNextFocus(event,document.form1.end)"><?=$begin?></td>
                    <td style="padding:0 5 0 5"width="20%">End</td>
					<td  style="padding:0 5 0 5"width="30%"><input type="hidden" size="20" name="end" maxlength="3" class="groove_text" value="<?=$end?>" onKeyUp="fNextFocus(event,document.form1.fk_group)"><?=$end?></td>
				</tr>
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5"width="20%">Group</td>
					<td  style="padding:0 5 0 5"width="30%" colspan="3">
                    <input type="hidden" name="fk_group" value="<?=$fk_group?>"> <?=$fk_group?>
					</td>
		    </tr>
                <tr style="padding:0 5 0 5" class="header" bgcolor="e0e0e0">
					<td style="padding:0 5 0 5" colspan="4">Digunakan Untuk</td>
				</tr>
		  	</table>
            <div id="divContentJenisTransaksi" style="overflow:scroll;height:200px"><? create_list_jenis_transaksi() ?></div>
		</td>
	</tr>
</table>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" align="center">
	<tr height="20"><td height="25" align="center" bgcolor="#D0E4FF">
	</tr>
</table>
</form>
</body>
</html>
<?
function create_list_jenis_transaksi(){
	global $id_edit,$used_for,$fk_cabang,$fk_jenis_cabang,$arr_used_for,$fk_group;
	
	echo '<table cellpadding="0" cellspacing="1" border="0" width="100%">';
	//====cek jika berasal dari dealer RK dg KTB muncul, dan jika berasal dari cabang maka RK dengan KTB tidak muncul
	
	$lrs = pg_query("select * from tbljenis_transaksi where is_hidden is false and is_active is true order by kd_jenis_transaksi");

	$lindex=0;
	echo '<tr bgcolor="efefef"><td colspan=4 style="padding:0 5 0 5"><a href="#" class="blue" onClick="fReset()">Reset</a></td></tr>';
	while($lrow = pg_fetch_array($lrs)){
		if($lindex%4==0 || $lindex==0)echo '<tr bgcolor="efefef">'; // row open
			if($id_edit ==""){	
				echo '<td width="15"><input type="checkbox" name="radio_used_for" value="'.$lrow['kd_jenis_transaksi'].'" class="groove_checkbox" '.(($lrow['group_account']!='')?"disabled":"").' onclick="fPilih(this)"></td>';
			}else{
				echo '<td width="15"><input type="checkbox" name="radio_used_for" value="'.$lrow['kd_jenis_transaksi'].'" class="groove_checkbox" '.(($fk_group==$lrow['group_account'])?"checked":"").' disabled onclick="fPilih(this)"></td>';
			}
		echo '<td width="48%" style="padding:0 5 0 5">'.$lrow['jenis_transaksi'].'&nbsp;&nbsp;';
		
		echo '</td>';
		$lindex+=2;
		if($lindex%4==0)echo '</tr>'; // row close
	}
	if($lindex%4!=0)echo'<td colspan="2"></td></tr>'; // kalau ganjil bikin row close
	echo '</table>';
}

function create_list_cabang($ptype,$pname){
	global $rk_dg_cabang,$fk_cabang;
    $l_list_obj = new select("select kd_cabang,nm_cabang||case when rk_dg_cabang is null then '' else ' - '||coa end as nm_cabang from (select * from tblcabang where is_aktif is true ".(($ptype=='all')?"":"and is_area = ".(($ptype=='dealer')?"TRUE":"FALSE")."")." ) as tblcabang left join (select distinct on (fk_cabang) * from tblcoa where used_for = '".strtolower($pname)."' and fk_cabang = '".convert_sql($fk_cabang)."')as tblcoa on rk_dg_cabang = kd_cabang order by kd_cabang","nm_cabang","kd_cabang",$pname);
    $l_list_obj->set_default_value($rk_dg_cabang);
    $l_list_obj->add_item("-- Pilih --",'',0);
    $l_list_obj->html("class='groove_text' style='width:150px;background-color:#ffffff;border-color:#999999;'","form1","fChange(this)");
}


function edit_data(){
	global $strmsg,$j_action,$begin,$end,$fk_group,$id_edit,$arr_used_for;

	$l_success=1;
	pg_query("BEGIN");
	
	//log begin
	if(!pg_query("insert into tblgroup_account_log select *,'".$_SESSION["ho_id"]."','".$_SESSION["ho_username"]."','#".date("Y/m/d H:i:s")."#','UB' from tblgroup_account where pk_id='".$id_edit."'")) $l_success=0;
	//end log
	
	if(!pg_query("update tblgroup_account set begin_range='".convert_sql($begin)."',end_range='".convert_sql($end)."',fk_group='".convert_sql($fk_group)."' where pk_id='".$id_edit."'")) $l_success=0;
	//log begin
	if(!pg_query("insert into tblgroup_account_log select *,'".$_SESSION["ho_id"]."','".$_SESSION["ho_username"]."','#".date("Y/m/d H:i:s")."#','UA' from tblgroup_account where pk_id='".$id_edit."'")) $l_success=0;
	//end log

	if ($l_success==1){
		$strmsg="Group Account Tersimpan.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		pg_query("COMMIT");
	}else{
		$strmsg="Error :<br>Group Account Gagal Tersimpan.<br>";
		pg_query("ROLLBACK");
	}
}

function get_data(){
	global $strmsg,$j_action,$begin,$end,$fk_group,$id_edit;
	
	$lrow=pg_fetch_array(pg_query("select * from tblgroup_account 	
	where --pk_id = '".$id_edit."'
	begin_range= '".$id_edit."'
	"));
	$begin = $lrow["begin_range"];
	$end = $lrow["end_range"];
	$fk_group = $lrow["fk_group"];
	
}
?>
