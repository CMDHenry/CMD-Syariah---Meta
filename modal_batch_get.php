<?
require './requires/config.inc.php';
require './requires/authorization.inc.php';
require './requires/compress.inc.php';


$p_id=$_REQUEST["p_id"];

?>
<html>
<head>
	<title>.: <?=$_SESSION["application"]?> :.</title>
    <link href="text.css.php" rel="stylesheet" type="text/css">
	<link href="menu.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" title="Style CSS" href="js/cwcalendar.css.php" type="text/css" media="all" />
</head>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src='js/calendar.js.php'></script>
<script language='javascript' src="js/dd_menu.js.php"></script>
<script language='javascript' src="js/misc.js.php"></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language="javascript">
var strOrderBy=""
var strOrderType=""
var intPage=1



function fPage(pNumber){
	intPage=intPage+pNumber
	with(document.form1){
		fGetData('page='+intPage+'&strorderby='+strOrderBy+'&strordertype='+strOrderType+'&no_batch='+no_batch.value)
	}
}

function fGoPage(pNumber){
	intPage=pNumber
	with(document.form1){
		fGetData('page='+intPage+'&strorderby='+strOrderBy+'&strordertype='+strOrderType+'&no_batch='+no_batch.value)
	}
}

function forder(j_type){
	strOrderBy=j_type;
	if(strOrderType=='asc') strOrderType='desc';
	else strOrderType='asc';
	with(document.form1){
		fGetData('strorderby='+strOrderBy+'&strordertype='+strOrderType+'&no_batch='+no_batch.value)
	}
}

function fGetData(pParam){
	document.getElementById("divContent").innerHTML=" <img src='images/bar_3dots.gif'>"
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataState
	lObjLoad.open("POST","ajax/get_list_data_batch.ajax.php",true);
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
				getRadio()
			}
		} else {
			document.getElementById("divContent").innerHTML="Load Failed."
		}
	}
}

function fView(){
	with(document.form1){
		fGetData('strorderby='+strOrderBy+'&strordertype='+strOrderType+'&no_batch='+no_batch.value)
	}
}

function fChecked(pObj){
	if (pObj.checked){
		document.form1.p_id.value=pObj.value
	} else {
		document.form1.p_id.value=""
	}
}

function fPilih(){
	if(document.form1.p_id.value=="") alert('Anda Belum Memilih Data!');
	else{
		if (document.form1.p_id.value) {window.objectReturn.value=document.form1.p_id.value;if(window.objectReturn.onchange)window.objectReturn.onchange()}
		window.close();
	}
}

function fLoad(){
	if (strOrderBy=="")	strOrderBy="tgl_batch";
	if (strOrderType=="") strOrderType="desc";
	with (document.form1) {
		fGetData('strorderby='+strOrderBy+'&strordertype='+strOrderType+'&no_batch='+no_batch.value)
	}
}
</script>
<body bgcolor="#f3f3f3" onLoad="fLoad()">
<form name="form1" style="margin:0 0 0 0">
<input type="hidden" name="p_id" value="<?=$p_id?>">
<input type="hidden" name="tipe" value="<?=$tipe?>">
<table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
	<tr>
		<td width="20"></td>
		<td valign="top">
			<table cellpadding="0" cellspacing="1" border="0" width="100%" class="border">
				<tr bgcolor="e0e0e0">
					<td width="50%" style="padding:0 5 0 5">No Batch</td>
                    <td width="50%" style="padding:0 5 0 5"></td>
				</tr>
				<tr bgcolor="#efefef">
					<td width="50%" style="padding:0 5 0 5">
						<input type="text" name="no_batch" style="width:200">
					</td>
                    <td width="50%" style="padding:0 5 0 5">
					</td>
				</tr>
				
				<tr bgcolor="#e0e0e0">
					<td style="padding:0 5 0 5" colspan="2"><input type="button" class="groove_button" value="View" onClick="fView()"></td>
				</tr>
			</table>
			<br>
			<div id="divContent" style="height:480" align="center"></div>
		</td>
		<td width="20"></td>
	</tr>
</table>
</form>
</body>
</html>
