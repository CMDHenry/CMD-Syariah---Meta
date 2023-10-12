<?
//require 'requires/compress.inc.php';
require 'requires/config.inc.php';
require 'classes/select.class.php';
//require 'requires/ahm_utility.inc.php';
//echo $_SERVER['REMOTE_ADDR'];
//echo session_id();
//$_SESSION["is_mac"] = is_mac_ok();
//if(!$_SESSION["is_mac"])header("location:error_access.php");
//if(strtotime(date("m/d/Y")) > strtotime('05/31/2012'))header("location:error_access.php");
$row_setting=pg_fetch_array(pg_query("select * from skeleton.tblsetting_database"))
?>
<html>
<head>
	<title>.: <?=$row_setting["application"]?> :.</title>
    <link href="text.css.php" rel="stylesheet" type="text/css">
    <style>
	
	
	div.absolute1 {
		position: absolute;
		width: 1345px;
		height: 720px; 
/*		background-image:url(images/upload/gadai_mas.jpg);
*/		background-size: 1345px 720px; 
	}
	
	div.absolute2 {
		position: absolute;
		top: 380px;
		left: 25px;
		width: 400px;
		height: 270px;
		
	}
	
	div.absolute {
		position: absolute;
		top: 380px;
		right: 25px;
		width: 400px;
		height: 270px;
		
	}
	div.copyright {
		position: absolute;
		top: 480px;
		right: 250px;
		font-size:9px
	}
</style>
    
</head>
<script language='javascript' src="js/alert.js.php"></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src="js/object_function.js.php"></script>
<script language='javascript' src="js/coockie.js.php"></script>
<script language="javascript">
var gLogin=0

function getLastLogin(pObj){
	lUser = pObj.value.toLowerCase()
}

function fLogin(){

	if (gLogin==0 && document.form1.user.disabled!="disabled" && document.form1.password.disabled!="disabled"){
		gLogin=1;
		document.form1.user.disabled="disabled"
		document.form1.password.disabled="disabled"
		document.getElementById("divLogin").style.display="none"
		document.getElementById("divLoding").style.display="block"
		
//lSentText='user='+escape(document.form1.user.value)+'&password='+escape(document.form1.password.value)+'&cabang='+escape(document.form1.cabang.value);
		lSentText='user='+escape(document.form1.user.value)+'&password='+escape(document.form1.password.value);
		lObjLoad = getHTTPObject()
		lObjLoad.onreadystatechange=fLoginState	
		lObjLoad.open("POST","ajax/login.ajax.php",true);
		lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
		lObjLoad.setRequestHeader("Content-Length",lSentText.length)
		lObjLoad.setRequestHeader("Connection","close")
		lObjLoad.send(lSentText);
	}
}

function fLoginState(){
	
	if (this.readyState == 4){
		if (this.status==200 && this.responseText!=""){
			lArrMsg=this.responseText.split(":")
			//alert (this.responseText)
			if (lArrMsg[0]=="err"){
				if (lArrMsg[1]=="1000"){
					top.location="index.php"					
				} else if(lArrMsg[1]=="1001"){
					//confirm(lArrMsg[2])
					document.form1.user.disabled=""
					document.form1.password.disabled=""
					document.getElementById("divLogin").style.display="block"
					document.getElementById("divLoding").style.display="none"
					alert("Error:<br>"+lArrMsg[2],function(){gLogin=0;});
					//alert("Error:<br>Username/Password Salah, atau User tidak aktif, atau sudah ada user yang login",function(){gLogin=0;});
					document.form1.password.value='';
				}else if(lArrMsg[1]=="1002"){
					//confirm(lArrMsg[2])
					document.form1.user.disabled=""
					document.form1.password.disabled=""
					document.getElementById("divLogin").style.display="block"
					document.getElementById("divLoding").style.display="none"
					alert("Error:<br>"+lArrMsg[2],function(){gLogin=0;});
					setTimeout(function () {
						top.location='modal_change_password.php?username='+escape(document.form1.user.value)+'&flag='+escape('true');
					}, 1500);
					document.form1.password.value='';
					
				} 
			} else if(lArrMsg[0]=="msg"){
				if (lArrMsg[1]=="success"){
					top.location="home.php"		
				} 				
			}
		} else {
			document.form1.user.disabled=""
			document.form1.password.disabled=""
			document.getElementById("divLogin").style.display="block"
			document.getElementById("divLoding").style.display="none"
			document.form1.password.value='';
			alert("Login Gagal<br><br>Error:<br>"+this.responseText,function(){gLogin=0;});
		}
	}
}

function fFormKeyPress(ev){
	if(ev.keyCode==13)
		fLogin();
}

function fLoad(){
	document.form1.user.focus();
	if(top != self){
		window.top.location = 'index.php'
	}
}
</script>
<body bgcolor="#EEEEEE" onLoad="fLoad()">
<div class="absolute1">
<table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
	<tr>
    	<!--<img src="images/product_image.jpg" usemap="#meta">-->
		<td align="center" valign="middle"><!--<img src="images/gadaimulia_member.png" width="400" height="300">--></td>
        <map name="meta"><area shape="rect" coords="10,400,180,330" href="http://www.meta-technology.com" target="_blank" alt="Meta-Technology" /></map>
	</tr>
    <tr>
    	<td height="230" align="left" valign="left">
            <div class="absolute2">
			<table cellspacing=""="0" cellpadding="0" border="0">            	
				<!--<tr>
					<td colspan="3" align="right" style="padding:5 5 0 5"><img src="images/login.jpg"></td>
				</tr>-->
				<tr>
                	<td align="left" valign="left"><!--<img src="images/regist_ojk.png" width="300" height="150">--></td>
				</tr>
			</table>
            </div>
			
		</td>
		<td height="230" align="right" valign="top">
			<form name="form1" onKeyPress="fFormKeyPress(event)">
            <div class="absolute">
			<table cellspacing=""="0" cellpadding="0" border="0">
				<!--<tr>
					<td colspan="3" align="right" style="padding:5 5 0 5"><img src="images/login.jpg"></td>
				</tr>-->
				<tr>
					<td align="right" style="padding:5 5 5 5;font-size:14px">
						Username&nbsp;<br>
						<input type="text" name="user" style="text-align:right;font-size:14px" onKeyUp="getLastLogin(this)"><br><br>
						Password&nbsp;<br>
						<input type="password" name="password" style="text-align:right;font-size:14px"><br><br>
<!--                        Cabang&nbsp;<br>
-->						<? //echo $_SERVER['REMOTE_ADDR'];//create_list_cabang()?>
					</td>
					<td width="5"><img src="images/login_border.jpg"></td>
					<td width="130" align="center" valign="middle"><div id="divLoding" style="display:none;">Loading ....&nbsp;&nbsp;</div><div id="divLogin"><a href="#" onClick="fLogin()"><img src="images/go.jpg" border="0"></a></div></td>
				</tr>
			</table>
            </div>
			</form>
		</td>
	</tr>
</table>
</div>
</body>
</html>
<?
function create_list_cabang(){
    global $cabang;
	//foreach($GLOBALS['__ARR_CABANG__'] as $_cabang => $_arr_cabang)$list_cabang .= "'".$_cabang."',";
	//$list_cabang = substr($list_cabang,0,strlen($list_cabang)-1);
	
    $l_list_obj = new select("select kd_cabang,nm_cabang from (select * from tblcabang order by nm_cabang)as tblcabang","nm_cabang","kd_cabang","cabang");
    $l_list_obj->set_default_value($cabang);
    $l_list_obj->add_item("-- Cabang ---",'',0);
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:14px;' onKeyUp='fNextFocus(event,document.getElementById(\"login\"))'");
}
?>