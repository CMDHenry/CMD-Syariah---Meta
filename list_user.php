<?
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/compress.inc.php';
require 'requires/input.inc.php';
require 'requires/module.inc.php';
require 'requires/general.inc.php';
require 'requires/db_utility.inc.php';
get_data_module();

$module = $_REQUEST['module'];

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
<script language="javascript">
var strOrderBy=""
var strOrderType=""
var intPage=1

function fModal(pType,pID){
	l_obj_function=function (){
		with(document.form1){
			fGetData('module=<?=$module?>&pk_id_module=<?=$pk_id_module?>&page='+intPage+'&strorderby='+strOrderBy+'&strordertype='+strOrderType)
		}
	}
	
	switch (pType){
		case "new":
			show_modal('modal_user.php?id_menu=<?=$module?>&pstatus=view','status:no;help:no;dialogwidth:900px;dialogheight:545px;',l_obj_function)
			break;
		case "edit":
			show_modal('modal_user.php?id_menu=<?=$module?>&pstatus=view&id_edit='+pID,'status:no;help:no;dialogwidth:900px;dialogheight:545px;',l_obj_function)
			break;
		case "menu":
			show_modal('modal_user_menu.php?id_menu=<?=$module?>&pstatus=view&id_edit='+pID,'status:no;help:no;dialogwidth:900px;dialogheight:545px;',l_obj_function)
			break;	
	}
}

function fView(){
	with(document.form1){
		fGetData('page='+intPage+'&strorderby='+strOrderBy+'&strordertype='+strOrderType+'&username='+document.form1.username.value+'&nm_karyawan='+document.form1.nm_karyawan.value+'&module='+<?=$module?>)
	}
}

function fPage(pNumber){
	intPage=intPage+pNumber
	with(document.form1){
		fGetData('page='+intPage+'&strorderby='+strOrderBy+'&strordertype='+strOrderType+'&username='+document.form1.username.value+'&nm_karyawan='+document.form1.nm_karyawan.value+'&module='+<?=$module?>)
	}
}

function fGoPage(pNumber){
	intPage=pNumber
	with(document.form1){
		fGetData('page='+intPage+'&strorderby='+strOrderBy+'&strordertype='+strOrderType+'&username='+document.form1.username.value+'&nm_karyawan='+document.form1.nm_karyawan.value+'&module='+<?=$module?>)
	}
}

function forder(j_type){
	strOrderBy=j_type;
	if(strOrderType=='asc') strOrderType='desc';
	else strOrderType='asc';
	with(document.form1){
		fGetData('page='+intPage+'&strorderby='+strOrderBy+'&strordertype='+strOrderType+'&username='+document.form1.username.value+'&nm_karyawan='+document.form1.nm_karyawan.value+'&module='+<?=$module?>)
	}
}

function fGetData(pParam){
	document.getElementById("divContent").innerHTML="<img src='images/bar_3dots.gif'></img>"
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataState
	lObjLoad.open("POST","ajax/get_list_data_user.ajax.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",pParam.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(pParam);
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

function fGetDataState(){
	if (this.readyState == 4){
		if (this.status==200 && this.responseText!="") {
			if (this.responseText=="err:1000"){
				top.location="index.php"									
			} else {
				document.getElementById("divContent").innerHTML=this.responseText
			}
		} else {
			document.getElementById("divContent").innerHTML="Load Failed."
		}
	}
	document.getElementById("divContentInner").style.height=window.innerHeight-150-(document.getElementById("tableSearch").offsetHeight-17)
}
function fLoad(){
	if (strOrderBy=="")	strOrderBy="username";
	if (strOrderType=="") strOrderType="asc";
		fGetData('strorderby='+strOrderBy+'&strordertype='+strOrderType+'&module='+<?=$module?>)
}
</script>
<body bgcolor="#f3f3f3" onLoad="fLoad()">
<?
include_once("includes/menu.inc.php");
?>
<form name="form1" action="list_user.php" style="margin:0 0 0 0">
<input type="hidden" name="module" style="width:200" value="">
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
							<tr>
                                <td width="10%" style="padding:0 5 0 5" bgcolor="e0e0e0">Username</td>
                                <td width="40%" style="padding:0 5 0 5" bgcolor="#efefef">
                                    <input type="text" name="username" style="width:200">
            
                                </td>
                                <td width="10%" style="padding:0 5 0 5" bgcolor="e0e0e0">Nama Karyawan</td>
                                <td width="40%" style="padding:0 5 0 5" bgcolor="#efefef">
                                    <input type="text" name="nm_karyawan" style="width:200">
                                </td>
                            </tr>
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
	//showquery("select * from skeleton.tblmenu where pk_id='".$module."'");
	if (!check_right($row_menu["kd_menu"])){
		header("location:error_access.php");
	}
	echo $module;
	$parent_root=get_rec("skeleton.tblmenu","pk_id","kd_menu='".substr($row_menu["kd_menu"],0,2)."'"); //Menu Tab Master
	$pk_id_module=get_rec("skeleton.tblmodule","pk_id","fk_menu='".$module."'"); //pk_id tblmodule/fk_module_fields
}

?>