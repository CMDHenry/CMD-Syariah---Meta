<?
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/compress.inc.php';
require 'requires/input.inc.php';
require 'requires/module.inc.php';
require 'requires/general.inc.php';
require 'requires/db_utility.inc.php';

get_data_module();
?>
<html>
<head>
	<title>.: <?=$_SESSION["application"]?> :.</title>
    <link href="js/cwcalendar.css.php" rel="stylesheet" type="text/css">
    <link href="text.css.php" rel="stylesheet" type="text/css">
	<link href="menu.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src="js/alert.js.php"></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src="js/dd_menu.js.php"></script>
<script language='javascript' src="js/misc.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src="js/input_format_number.js.php"></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src="js/calendar.js.php"></script>
<script language="javascript">
<?
_module_create_js_search();
?>

var strOrderBy=""
var strOrderType=""
var intPage=1


function fModal(pType,pID){
	l_obj_function=function (){
		with(document.form1){
			fGetData('module=<?=$module?>&pk_id_module=<?=$pk_id_module?>&page='+intPage+'&strorderby='+strOrderBy+'&strordertype='+strOrderType<?=_module_generate_url_seacrch_param()?>)
		}
	}
	
	switch (pType){
	<?=_module_generate_modal($pk_id_module)?>
		case "view":
			show_modal('modal_view.php?id_menu=<?=$module?>&pstatus=view&id_view='+pID,'status:no;help:no;dialogwidth:900px;dialogheight:545px;',l_obj_function)
			break;
		case "view_pic":
			show_modal('modal_list_pic_view.php?id_menu=<?=$module?>&images_name='+pID,'dialogwidth:800px;dialogheight:400px;',l_obj_function)
			break;
	}
}

function fPage(pNumber){
	intPage=intPage+pNumber
	with(document.form1){
		fGetData('module=<?=$module?>&pk_id_module=<?=$pk_id_module?>&page='+intPage+'&strorderby='+strOrderBy+'&strordertype='+strOrderType<?=_module_generate_url_seacrch_param()?>)
	}
}

function fGoPage(pNumber){
	intPage=pNumber
	with(document.form1){
		fGetData('module=<?=$module?>&pk_id_module=<?=$pk_id_module?>&page='+intPage+'&strorderby='+strOrderBy+'&strordertype='+strOrderType<?=_module_generate_url_seacrch_param()?>)
	}
}

function fOrder(pType){
	strOrderBy=pType;
	if(strOrderType=='asc') strOrderType='desc';
	else strOrderType='asc';
	with(document.form1){
		fGetData('module=<?=$module?>&pk_id_module=<?=$pk_id_module?>&page='+intPage+'&strorderby='+strOrderBy+'&strordertype='+strOrderType<?=_module_generate_url_seacrch_param()?>)
	}
}

function fGetData(pParam){
	document.getElementById("divContent").innerHTML="<img src='images/bar_3dots.gif'></img>"
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataState
	lObjLoad.open("POST","ajax/get_list_data_select.ajax.php",true);
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
		fGetData('module=<?=$module?>&pk_id_module=<?=$pk_id_module?>&page='+intPage+'&strorderby='+strOrderBy+'&strordertype='+strOrderType<?=_module_generate_url_seacrch_param()?>)
	}
}

function fDelete(){
	if (confirm('Apakah Anda Yakin Ingin Menghapus Data Yang Dipilih ?')){
		lObjDel=document.form1.select_list
		lStrDel='';
		if (!lObjDel.length){
			if (lObjDel.checked) lStrDel="'"+lObjDel.value+"'";
		} else {
			for (i=0;i<lObjDel.length;i++){
				if (lObjDel[i].checked) lStrDel=lStrDel+"'"+lObjDel[i].value+"',";
			}
			if (lStrDel){
				lStrDel=lStrDel.substring(0,lStrDel.length-1)
			}
		}
		if (lStrDel){
			lSentText='strorderby='+strOrderBy+'&strordertype='+strOrderType+'&module='+<?=$module?>+'&pk_id_module=<?=$pk_id_module?>&select_list='+lStrDel
			document.getElementById("divContent").innerHTML=" <img src='images/bar_3dots.gif'>"
			lObjLoad = getHTTPObject()
			lObjLoad.onreadystatechange=fDeleteState	
			lObjLoad.open("POST","ajax/del_data_select_list.ajax.php",true);
			lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
			lObjLoad.setRequestHeader("Content-Length",lSentText.length)
			lObjLoad.setRequestHeader("Connection","close")
			lObjLoad.send(lSentText);
		}
	}
}

function fDeleteState(){
	if (this.readyState == 4){
		//alert(this.responseText)
		if (this.responseText!=""){
			if (this.responseText=="err:1000"){
				top.location="index.php"									
			} else {
				lArrData=this.responseText.split("<split>")
				if (lArrData[1])alert(lArrData[1])
				document.getElementById("divContent").innerHTML=lArrData[2]
			}
		}
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

function fCount(pFlag='true'){
	var index;
	index =0;
	//confirm(pFlag)
	lObjCount=document.form1.select_list
	for (j=1;j<lObjCount.length;j++){
		if (lObjCount[j].checked) index ++;
	}	
	document.getElementById('divSelectCount').innerHTML=index;
	//var r=confirm("Total Data yang Dipilih : "+index);
	
}

function fcheckall(pValue,pIsi){
	 var x = document.getElementById("myCheck").checked;
	//confirm  (p_value)
	//if(x==true){
		for(var i=0;i<document.form1.select_list.length;i++){
			document.form1.select_list[i].checked=x;
		}
	//}else 
	fCount('false')
}

function fLoad(){
	window.onresize =fResize;
	if (strOrderBy=="")	strOrderBy="<?=get_rec("skeleton.tblmodule_fields","save_to_field","fk_module='".$pk_id_module."'","no_urut_add")?>";
	if (strOrderType=="") strOrderType="asc";
	fGetData('module=<?=$module?>&pk_id_module=<?=$pk_id_module?>&strorderby='+strOrderBy+'&strordertype='+strOrderType)
}
</script>
<body bgcolor="#f3f3f3" onLoad="fLoad()">
<?
include_once("includes/menu.inc.php");
?>
<form name="form1" style="margin:0 0 0 0">
<input type="hidden" name="module" value="<?=$module?>">
<input type="hidden" name="pk_id_module" value="<?=$pk_id_module?>">
<table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
	<tr background="images/submenu_background.jpg" height="37">
		<td width="20"></td>
		<td class="selectMenu" colspan="2"><?=strtoupper($row_menu["root_menu"])?></td>
	</tr>
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
							<?=_module_generate_search()?>
                        </table>
                    </td>
				</tr>
                <tr id="trSearchButton" style="display:none">
                    <td>
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" class="border">
                            <tr bgcolor="#D0E4FF">
                                <td style="padding:0 5 0 5"><input type="button" name="btnView" class="groove_button" value="View" onClick="fView()"></td>
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
function get_data_module(){
	global $module,$row_menu,$parent_root,$pk_id_module;
	
	$module=$_REQUEST["module"];	//kd_menu/fk_menu
	$row_menu=pg_fetch_array(pg_query("select * from skeleton.tblmenu where pk_id='".$module."'"));
	/*
	if (!check_right("10101312")){
		header("location:error_access.php");
	}
	*/ 
	//$parent_root='20140200000037';
	$parent_root=get_rec("skeleton.tblmenu","pk_id","kd_menu='".substr($row_menu["kd_menu"],0,2)."'"); //Menu Tab Master
	$pk_id_module=get_rec("skeleton.tblmodule","pk_id","fk_menu='".$module."'"); //pk_id tblmodule/fk_module_fields
}

function _module_generate_modal(){
	global $module,$pk_id_moduile;

	$l_arr_jenis_module=array("add"=>"modal_add.php","edit"=>"modal_edit.php","view"=>"modal_view.php","batal"=>"modal_batal.php","approve"=>"modal_approve.php");
	$l_arr_jenis_module_param["edit"]="&pstatus=edit&id_edit='+pID+'";
	$l_arr_jenis_module_param["view"]="&pstatus=view&id_view='+pID+'";
	$l_arr_jenis_module_param["batal"]="&pstatus=batal&id_edit='+pID+'";
	$l_arr_jenis_module_param["approve"]="&pstatus=approve&id_edit='+pID+'";

	$lrs=pg_query("select * from skeleton.tblmenu where fk_parent='".$module."' and is_action=true and type_action='module_action' and per_item='f'");	
	while ($lrow=pg_fetch_array($lrs)) {
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
			show_modal('<?=$l_arr_jenis_module[$lrow["jenis_module"]]?>?id_menu=<?=$module?>&pstatus=<?=($lrow["jenis_module"]=="view")?"view":"edit"?>&<?=($lrow["jenis_module"]=="view")?"id_view":"id_edit"?>='+pID,'status:no;help:no;dialogwidth:900px;dialogheight:575px;',l_obj_function)
			break;
<?		
	}
}

function _module_generate_search(){
	global $pk_id_module;

	$l_counter=1;
	
	$l_arr_param["list"]["list_db"]["list_sql"]="list_sql";
	$l_arr_param["list"]["list_db"]["list_value"]="list_field_value";
	$l_arr_param["list"]["list_db"]["list_text"]="list_field_text";
	$l_arr_param["list"]["list_manual"]["list_text"]="list_manual_text";
	$l_arr_param["list"]["list_manual"]["list_value"]="list_manual_value";
	$l_arr_param["list"]["get"]["fk_module"]="fk_get_module";
	
	$lrs=pg_query("select * from skeleton.tblmodule_fields_attribute where fk_module_fields='".$pk_id_module."'");
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
		//echo $l_counter;
		$_REQUEST["view_kd_field"][$l_counter]=$lrow["kd_field"];
		if (($l_counter%2)!=0) {
?>
                <tr>
                    <td width="20%" style="padding:0 5 0 5" bgcolor="e0e0e0"><?=$lrow["nm_field"]?></td>
                    <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                    	<?=generate_input($lrow["value_type"],$lrow["fk_data_type"],$lrow["kd_field"],$lrow["nm_field"],"",$l_arr_attrib,$l_arr_add_param,$lrow["type_list"],$lrow["fgetnamesearch"],$lrow["fgetdatanamesearch"],"","",$lrow["is_multiple"])?>
                    </td>
<?
		} else {
?>
                    <td width="20%" style="padding:0 5 0 5" bgcolor="e0e0e0"><?=$lrow["nm_field"]?></td>
                    <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                        <?=generate_input($lrow["value_type"],$lrow["fk_data_type"],$lrow["kd_field"],$lrow["nm_field"],"",$l_arr_attrib,$l_arr_add_param,$lrow["type_list"],$lrow["fgetnamesearch"],$lrow["fgetdatanamesearch"],"","",$lrow["is_multiple"])?>
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

function _module_generate_url_seacrch_param() {
	global $pk_id_module;
	$l_param="";

	$lrs=pg_query("select * from skeleton.tblmodule_fields where is_search='t' and fk_module='".$pk_id_module."' order by no_urut_edit");
	//showquery("select * from skeleton.tblmodule_fields where is_search='t' and fk_module='".$pk_id_module."' order by no_urut_edit");
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