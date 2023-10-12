<?
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/compress.inc.php';
require 'requires/input.inc.php';
require 'requires/db_utility.inc.php';

$p_id=$_REQUEST["p_id"];
$module=$_REQUEST["module"];	

$row_menu=pg_fetch_array(pg_query("select * from skeleton.tblmenu where pk_id='".$module."'")); //Buat Root Menunya

//showquery("select * from skeleton.tblmenu where pk_id='".$module."'");
$parent_root=get_rec("skeleton.tblmenu","pk_id","kd_menu='".substr($row_menu["kd_menu"],0,2)."'"); //Menu Tab Master
$pk_id_module=get_rec("skeleton.tblmodule","pk_id","fk_menu='".$module."'"); //pk_id tblmodule/fk_module

$id_detail_field=$_REQUEST["id_detail_field"]; //pk_id tblmodule detail_fields
$id_field=$_REQUEST["id_field"];//pk_id tblmodule_fields
$option_name=$_REQUEST["option_name"];//where tambahan sesuai data header
$kode=$_REQUEST["kd_".$module];
//echo $option_name;
//echo $_REQUEST["kode"];
if(!$kode)$kode=$_REQUEST[$_REQUEST["kode"]];

if($option_name=="mutasi_bank_ho_ke_cabang"){
	if($_REQUEST["jenis_mutasi"]==0)$kode=$_REQUEST["fk_bank_masuk"];
	elseif($_REQUEST["jenis_mutasi"]==1)$kode=$_REQUEST["fk_bank_keluar"];
	else $kode='';
	$option_name="bank_cek_ke_cabang";
}

if($option_name=="mutasi_bank_wilayah_ke_cabang"){
	$fk_cabang_wilayah=$_REQUEST["fk_cabang_wilayah"];
	if($_REQUEST["jenis_mutasi"]==7)$kode=$_REQUEST["fk_bank_masuk"].'_'.$fk_cabang_wilayah;
	elseif($_REQUEST["jenis_mutasi"]==8)$kode=$_REQUEST["fk_bank_keluar"].'_'.$fk_cabang_wilayah;
	else $kode='';
}


if($option_name=="fk_jenis_barang_grade"){
	$kode.=$_REQUEST["fk_jenis_barang"].'_'.$_REQUEST["fk_grade"];
}

if($option_name=="fk_produk"){
	$kode.=$_REQUEST["status_barang"].'_'.$_REQUEST["kategori"];
}



$id_detail_field=$_REQUEST["id_detail_field"];
?>
<html>
<head>
	<title>.: <?=$_SESSION["application"]?> :.</title>
    <link href="text.css.php" rel="stylesheet" type="text/css">
	<link href="menu.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src="js/alert.js.php"></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src="js/dd_menu.js.php"></script>
<script language='javascript' src="js/misc.js.php"></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src="js/object_function.js.php"></script>

<script language="javascript">
var strOrderBy=""
var strOrderType=""
var intPage=1


function fModal(pType,pID,pModal){
	l_obj_function=function (){
		with(document.form1){
			fGetData('module=<?=$module?>&p_id=<?=$p_id?>&pk_id_module=<?=$pk_id_module?>&id_field=<?=$id_field?>&id_detail_field=<?=$id_detail_field?>&option_name=<?=$option_name?>&kode=<?=$kode?>&page='+intPage+'&strorderby='+strOrderBy+'&strordertype='+strOrderType<?=generate_url_seacrch_param()?>)
		}
	}
	
	switch (pType){
	<?=generate_modal($pk_id_module)?>
		case "view":
			show_modal('modal_view.php?id_menu=<?=$module?>&pstatus=view&id_view='+pID,'status:no;help:no;dialogwidth:900px;dialogheight:545px;',l_obj_function)
			break;
		case "view_custom":
			show_modal(pModal+'?id_menu=<?=$module?>&pstatus=view&id_view='+pID,'status:no;help:no;dialogwidth:900px;dialogheight:545px;',l_obj_function)
			break;
			
	}
}

function fPage(pNumber){
	intPage=intPage+pNumber
	with(document.form1){
		fGetData('module=<?=$module?>&p_id=<?=$p_id?>&pk_id_module=<?=$pk_id_module?>&id_field=<?=$id_field?>&id_detail_field=<?=$id_detail_field?>&option_name=<?=$option_name?>&kode=<?=$kode?>&page='+intPage+'&strorderby='+strOrderBy+'&strordertype='+strOrderType<?=generate_url_seacrch_param()?>)
	}
}

function fGoPage(pNumber){
	intPage=pNumber
	with(document.form1){
		fGetData('module=<?=$module?>&p_id=<?=$p_id?>&pk_id_module=<?=$pk_id_module?>&id_field=<?=$id_field?>&id_detail_field=<?=$id_detail_field?>&option_name=<?=$option_name?>&kode=<?=$kode?>&page='+intPage+'&strorderby='+strOrderBy+'&strordertype='+strOrderType<?=generate_url_seacrch_param()?>)
	}
}

function fOrder(pType){
	strOrderBy=pType;
	if(strOrderType=='asc') strOrderType='desc';
	else strOrderType='asc';
	with(document.form1){
		fGetData('module=<?=$module?>&p_id=<?=$p_id?>&pk_id_module=<?=$pk_id_module?>&id_field=<?=$id_field?>&id_detail_field=<?=$id_detail_field?>&option_name=<?=$option_name?>&kode=<?=$kode?>&page='+intPage+'&strorderby='+strOrderBy+'&strordertype='+strOrderType<?=generate_url_seacrch_param()?>)
	}
}

function fGetData(pParam){
	document.getElementById("divContent").innerHTML="<img src='images/bar_3dots.gif'></img>"
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataState
	lObjLoad.open("POST","ajax/get_list_data_checkbox.ajax.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",pParam.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(pParam);
}

function fGetDataState(){
	if (this.readyState == 4){
		if (this.status==200 && this.responseText!="") {
			if (this.responseText=="err:1000"){
				top.location="index.php"									
			} else {
				document.getElementById("divContent").innerHTML=this.responseText
				document.getElementById("divContentInner").style.height=window.innerHeight-150-(document.getElementById("tableSearch").offsetHeight-17)
			}
		} else {
			document.getElementById("divContent").innerHTML="Load Failed."
		}
	}
}

function fView(){
	with(document.form1){
		fGetData('module=<?=$module?>&p_id=<?=$p_id?>&pk_id_module=<?=$pk_id_module?>&id_field=<?=$id_field?>&id_detail_field=<?=$id_detail_field?>&option_name=<?=$option_name?>&kode=<?=$kode?>&page='+intPage+'&strorderby='+strOrderBy+'&strordertype='+strOrderType<?=generate_url_seacrch_param()?>)
	}
}

function fOpenWindowSearch(pObj){
	if (pObj.innerHTML=="+ Search"){
		pObj.innerHTML="- Search"
		document.getElementById("trSearchField").style.display="table-row"
		document.getElementById("trSearchButton").style.display="table-row"
	} else {
		pObj.innerHTML="+ Search"
		document.getElementById("trSearchField").style.display="none"
		document.getElementById("trSearchButton").style.display="none"
	}
	if (document.getElementById("divContentInner")) document.getElementById("divContentInner").style.height=window.innerHeight-150-(document.getElementById("tableSearch").offsetHeight-17)
}

function fResize(){
	if (document.getElementById("divContentInner")) document.getElementById("divContentInner").style.height=window.innerHeight-150
}

function fChecked(pObj){
	if (pObj.checked){
		document.form1.p_id.value=pObj.value
		//alert (pObj)
	} else {
		document.form1.p_id.value=""
	}
}

function fPilih(){
	if(document.form1.p_id.value=="") alert('Error:<br>Anda Belum Memilih Data!');
	else{
		if (document.form1.p_id.value) {window.objectReturn.value=document.form1.p_id.value;if(window.objectReturn.onchange)window.objectReturn.onchange()}
		window.close();
	}
}

function fCheck(pObj){
	//if(document.form1.p_id.value!="") 
	alert ("test")	
}

function fLoad(){
	window.onresize =fResize;
	if (strOrderBy=="")	strOrderBy="<?=get_rec("skeleton.tblmodule_fields","save_to_field","fk_module='".$pk_id_module."'","no_urut_edit")?>";
	//if (strOrderType=="") strOrderType="desc";
	if (strOrderType=="") strOrderType="<?=(get_rec("skeleton.tblmenu","order_by_type","pk_id='".$module."'")=="asc"?"asc":"desc")?>";
	//fGetData('module=<?=$module?>&pk_id_module=<?=$pk_id_module?>&strorderby='+strOrderBy+'&strordertype='+strOrderType)

	fGetData('module=<?=$module?>&p_id=<?=$p_id?>&pk_id_module=<?=$pk_id_module?>&id_field=<?=$id_field?>&id_detail_field=<?=$id_detail_field?>&option_name=<?=$option_name?>&kode=<?=$kode?>&strorderby='+strOrderBy+'&strordertype='+strOrderType)
}

</script>
<body bgcolor="#f3f3f3" onLoad="fLoad()">
<?
include_once("includes/menu.inc.php");
?>
<form name="form1" style="margin:0 0 0 0">
<input type="hidden" name="p_id" value="<?=$p_id?>">
<input type="hidden" name="id_field" value="<?=$id_field?>">

<table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
	<tr>
		<td width="20"></td>
		<td valign="top">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" id="tableSearch">
                <tr>
                    <td>
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" class="border">
                            <tr bgcolor="#D0E4FF">
                                <td style="padding:0 5 0 5"><a href="#" onClick="fOpenWindowSearch(this)">+ Search</a></td>
                            </tr>
                        </table>
                    </td>
				</tr>
                <tr id="trSearchField" style="display:none">
                    <td>
                        <table cellpadding="0" cellspacing="1" border="0" width="100%" class="border">
							<?=generate_search()?>
                        </table>
                    </td>
				</tr>
                <tr id="trSearchButton" style="display:none">
                    <td>
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" class="border">
                            <tr bgcolor="#D0E4FF">
                                <td style="padding:0 5 0 5"><input type="button" class="groove_button" value="View" onClick="fView()"></td>
                            </tr>
                        </table>
                    </td>
				</tr>
			</table>
			<br>
			<div id="divContent" style="height:auto" align="center"></div>
		</td>
		<td width="20"></td>
	</tr>
</table>
</form>
</body>
</html>
<?
function generate_modal(){
	global $module,$pk_id_moduile;

	$l_arr_jenis_module=array("add"=>"modal_add.php","edit"=>"modal_edit.php");

	$lrs=pg_query("select * from skeleton.tblmenu where fk_parent='".$module."' and is_action=true and type_action='module_action' and per_item='f'");	
	while ($lrow=pg_fetch_array($lrs)) {
		$lrow_menu=pg_fetch_array(pg_query("select * from skeleton.tblmenu where fk_parent='".$module."' and type_action='module_action' and jenis_module='other' and kd_menu='".$lrow["kd_menu"]."'"));
	//showquery("select * from skeleton.tblmenu where fk_parent='".$module."' and type_action='module_action' and jenis_module='other' and kd_menu='".$lrow["kd_menu"]."'");
		$l_arr_jenis_module=array("add"=>"modal_add.php","edit"=>"modal_edit.php","view"=>"modal_view.php","batal"=>"modal_approve.php","approve"=>"modal_approve.php","other"=>$lrow_menu["nm_file_module_other"]);
		
		$l_arr_jenis_module_param["edit"]="&pstatus=edit&id_edit='+pID+'";
		$l_arr_jenis_module_param["view"]="&pstatus=view&id_view='+pID+'";
		$l_arr_jenis_module_param["batal"]="&pstatus=batal&id_edit='+pID+'";
		$l_arr_jenis_module_param["approve"]="&pstatus=approve&id_edit='+pID+'";
		$l_arr_jenis_module_param["other"]="&pstatus=edit&id_edit='+pID+'";
?>
		case "<?=$lrow["kd_menu"]?>":
			show_modal('<?=$l_arr_jenis_module[$lrow["jenis_module"]]?>?id_menu=<?=$module.$l_arr_jenis_module_param[$lrow["jenis_module"]]?>','status:no;help:no;dialogwidth:900px;dialogheight:575px;',l_obj_function)
			break;
<?		
	}

	$lrs=pg_query("select * from skeleton.tblmenu where fk_parent='".$module."' and is_action=true and type_action='module_action' and per_item='t'");	
	while ($lrow=pg_fetch_array($lrs)) {
?>
		case "<?=$lrow["kd_menu"]?>":
			show_modal('<?=$l_arr_jenis_module[$lrow["jenis_module"]]?>?id_menu=<?=$module?>&pstatus=edit&id_edit='+pID,'status:no;help:no;dialogwidth:900px;dialogheight:575px;',l_obj_function)
			break;
<?		
	}
}

function generate_search(){
	global $pk_id_module;

	$l_counter=1;
	
	$l_arr_param["list"]["list_db"]["list_sql"]="list_sql";
	$l_arr_param["list"]["list_db"]["list_value"]="list_field_value";
	$l_arr_param["list"]["list_db"]["list_text"]="list_field_text";
	$l_arr_param["list"]["list_manual"]["list_text"]="list_manual_text";
	$l_arr_param["list"]["list_manual"]["list_value"]="list_manual_value";
	$l_arr_param["list"]["get"]["fk_module"]="fk_get_module";
	
	$lrs=pg_query("select * from skeleton.tblmodule_fields_attribute where fk_module_fields='".$pk_id_module."'");
	//showquery("select * from skeleton.tblmodule_fields_attribute where fk_module_fields='".$pk_id_module."'");
	while ($lrow=pg_fetch_array($lrs)) {
		$l_arr_attrib[$lrow["nm_attribute"]]=$lrow["value"];
	}
	
	
	$l_arr_add_param["decimal"]=0;
	
	$lrs=pg_query("select * from skeleton.tblmodule_fields where is_search='t' and fk_module='".$pk_id_module."' order by no_urut_edit");
	//showquery("select * from skeleton.tblmodule_fields where is_search='t' and fk_module='".$pk_id_module."' order by no_urut_edit");
	while ($lrow=pg_fetch_array($lrs)) {
		if(is_array($l_arr_param[$lrow["fk_data_type"]][$lrow["type_list"]])){
			foreach($l_arr_param[$lrow["fk_data_type"]][$lrow["type_list"]] as $l_key=>$l_value) {
				$l_arr_add_param[$l_key]=$lrow[$l_value];
			}
		}
		
		$_REQUEST["view_kd_field"][$l_counter]=$lrow["kd_field"];
		
		if($lrow["fk_data_type"]=="list")$lrow["fk_data_type"]="text";// tambahan buat ilangin kaca pembesar
		if($lrow["fk_data_type"]=='readonly') $lrow["fk_data_type"]="text";
		if($lrow["fk_data_type"]=='checkbox_list') {
			$lrow["fk_data_type"]="text";
			
			$lrow["kd_field"]=str_replace("]","",(str_replace("[","",$lrow["kd_field"])));
		}
		if($lrow["value_type"]=='php')$lrow["value_type"]='input';
		//fView()
		
		if (($l_counter%2)!=0) {
?>
                <tr>
                    <td width="20%" style="padding:0 5 0 5" bgcolor="e0e0e0"><?=$lrow["nm_field"]?></td>
                    <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                    	<?=generate_input($lrow["value_type"],$lrow["fk_data_type"],$lrow["kd_field"],$lrow["nm_field"],"",$l_arr_attrib,$l_arr_add_param,$lrow["type_list"])?>
                    </td>
<?
		} else {
					 if($lrow["nm_field"]=='Nama Barang'){
						$l_arr_attrib["onkeyup"]='fView()';
					 }
?>				
				
                    <td width="20%" style="padding:0 5 0 5" bgcolor="e0e0e0"><?=$lrow["nm_field"]?></td>
                    <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                        <?=generate_input($lrow["value_type"],$lrow["fk_data_type"],$lrow["kd_field"],$lrow["nm_field"],$lrow["kd_field"],$l_arr_attrib,$l_arr_add_param,$lrow["type_list"])?>
                    </td>
                </tr>
<?
		}
		$l_counter++;
	}
	if (($l_counter%2)==0) {
?>
                    <td width="20%" style="padding:0 5 0 5" bgcolor="e0e0e0"></td>
                    <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef"></td>
                </tr>
<?
	}
}

function generate_url_seacrch_param() {
	global $pk_id_module;
	$l_param="";

	$lrs=pg_query("select * from skeleton.tblmodule_fields where is_search='t' and fk_module='".$pk_id_module."' order by no_urut_edit");
	while ($lrow=pg_fetch_array($lrs)) {
		if($lrow["fk_data_type"]=='range_value' || $lrow["fk_data_type"]=='range_date'){
			$l_param.="+'&".$lrow["kd_field"]."1"."='+document.form1.".$lrow["kd_field"]."1".".value";
			$l_param.="+'&".$lrow["kd_field"]."2"."='+document.form1.".$lrow["kd_field"]."2".".value";			
		} else{
			$kd_field=str_replace("[]","",$lrow["kd_field"]);
			//$kd_field=$lrow["kd_field"];
			$l_param.="+'&".$kd_field."='+document.form1.".$kd_field.".value";
		}
	}
	return $l_param;
}
?>