<?
require './requires/config.inc.php';
require './requires/authorization.inc.php';
require './requires/compress.inc.php';


$p_id=$_REQUEST["p_id"];
$no_voucher=trim($_REQUEST["no_voucher"]);
//$nm_customer=trim($_REQUEST["nm_customer"]);

$tipe=trim($_REQUEST["fk_sbg"]);

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
		fGetData('page='+intPage+'&strorderby='+strOrderBy+'&strordertype='+strOrderType+'&no_voucher='+no_voucher.value+'&tgl_voucher='+tgl_voucher.value)
	}
}

function fGoPage(pNumber){
	intPage=pNumber
	with(document.form1){
		fGetData('page='+intPage+'&strorderby='+strOrderBy+'&strordertype='+strOrderType+'&no_voucher='+no_voucher.value+'&tgl_voucher='+tgl_voucher.value)
	}
}

function forder(j_type){
	strOrderBy=j_type;
	if(strOrderType=='asc') strOrderType='desc';
	else strOrderType='asc';
	with(document.form1){
		fGetData('strorderby='+strOrderBy+'&strordertype='+strOrderType+'&no_voucher='+no_voucher.value+'&tgl_voucher='+tgl_voucher.value)
	}
}

function fGetData(pParam){
	document.getElementById("divContent").innerHTML=" <img src='images/bar_3dots.gif'>"
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataState
	lObjLoad.open("POST","ajax/get_list_data_correction.ajax.php",true);
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
		fGetData('strorderby='+strOrderBy+'&strordertype='+strOrderType+'&no_voucher='+no_voucher.value+'&tgl_voucher='+tgl_voucher.value)
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
	//parent.parent.document.title="Kelurahan";
	if (strOrderBy=="")	strOrderBy="no_voucher";
	if (strOrderType=="") strOrderType="asc";
	with (document.form1) {
		fGetData('strorderby='+strOrderBy+'&strordertype='+strOrderType+'&no_voucher='+no_voucher.value+'&tgl_voucher='+tgl_voucher.value)
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
					<td width="50%" style="padding:0 5 0 5">No Voucher</td>
                    <td width="50%" style="padding:0 5 0 5">Tanggal Voucher</td>
				</tr>
				<tr bgcolor="#efefef">
					<td width="50%" style="padding:0 5 0 5">
						<input type="text" name="no_voucher" style="width:200">
					</td>
                    <td width="50%" style="padding:0 5 0 5">
						<input type="text" name="tgl_voucher" value="" class="groove_text" onKeyPress="if(event.keyCode==4) img_tgl_voucher.click();" size="10">&nbsp;<img src="images/btn_extend.gif" name="img_tgl_voucher" onClick="fPopCalendar(document.form1.tgl_voucher,document.form1.tgl_voucher)">
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
