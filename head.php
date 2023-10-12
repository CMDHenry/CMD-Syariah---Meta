<?
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/db_utility.inc.php';
?>
<html>
<head>
	<title>.: <?=$_SESSION["application"]?> :.</title>
    <link href="text.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src="js/misc.js.php"></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript'>
var gActiveMenu="";
var gActiveObject="";

function fActiveMenu(pMenu,pObj){
	if (gActiveMenu){
		if (gActiveMenu!=pMenu)
			gActiveObject.src="images/menu_"+gActiveMenu+".jpg";
	}
	gActiveMenu=pMenu;
	gActiveObject=pObj;
}

function fMouseOut(pMenu){
	if (gActiveMenu!=pMenu)
		MM_swapImgRestore()
}

function drawtime(){
	arr_month=Array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
	arr_day=Array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
	odate=new Date();
	c_hour=odate.getHours()
	if(c_hour<10){
		c_hour="0"+c_hour;
		c_am = "AM";
	}else{
		if (c_hour>12){
			c_hour=c_hour-12;
			c_am = "PM";
		} else {
			c_am = "AM";
		}
	}
	c_minute=odate.getMinutes()
	if(c_minute<10){
		c_minute="0"+c_minute;
	}
	c_sec=odate.getSeconds();
	if(c_sec<10){
		c_sec="0"+c_sec;
	}
	document.getElementById("span_time").innerHTML=arr_day[odate.getDay()]+", "+arr_month[odate.getMonth()]+" "+odate.getDate()+" "+odate.getFullYear()+" "+c_hour+":"+c_minute+":"+c_sec+" "+c_am;
	setTimeout(drawtime,1000)
}

function fLogout(){
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fLogoutState	
	lObjLoad.open("GET","ajax/logout.ajax.php",true);
	lObjLoad.send(null);
}

function fLogoutState(){
	if (this.readyState == 4){
		if (this.status==200 && this.responseText!=""){
			lArrMsg=this.responseText.split(":")
			if (lArrMsg[0]=="err"){
				if (lArrMsg[1]=="1000"){
					top.location="index.php"				
				} else if(lArrMsg[1]=="1001"){
					alert("Try to logout again");
				}
			} else if(lArrMsg[0]=="msg"){
				if (lArrMsg[1]=="success"){
					top.location="index.php"					
				} 				
			}
		} else {
			alert("Try to logout again");
		}
	}
}

function fGoHome(){
	window.parent.frames["content"].location='blank.php';
	window.parent.frames["left_panel"].location.reload();
}

function fLoad(){
	document.img_home.src="images/menu_home_over.jpg";
	gActiveMenu="home";
	gActiveObject=document.img_home;
	drawtime();
}

function fscroll(j_direction){
		var l_var = j_direction;
		var l_var2 = document.getElementById('subMenu');
		if(l_var == 'right'){
			iTimer=setInterval(function(){l_var2.scrollLeft += 10;},20);
		}
		else if(l_var == 'left'){
			iTimer=setInterval(function(){l_var2.scrollLeft -= 10;},20);
		}
		//iTimer=setInterval("subMenu.doScroll('"+j_direction+"')", 20);
	}

function fscrollout(){
		clearInterval(iTimer);
	}

</script>
<body onLoad="MM_preloadImages(<?=get_root_image()?>);fLoad()">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr height="17">
		<td background="images/head_background.jpg">
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr>
					<td style="color:#333333;font-weight:600" class="judul">
						&nbsp;&nbsp;&nbsp;&nbsp;Welcome <?=convert_html($_SESSION["username"])?> | Login Time : <?=date("l, M d Y h:i:s A")?> | Current Time : <span id="span_time"></span> 
                        
					</td>
                    
                    <td style="color:#333333;font-weight:600" class="judul" align="right">
        &nbsp;&nbsp;&nbsp;&nbsp;System Date : <span id="divsystem_dates"><? echo date("d/m/Y",strtotime(get_rec("tblsetting","tgl_sistem")))?> </span> &nbsp;
        </td>
                    
					<!--<td style="color:#333333;font-weight:600" class="judul" align="right">Version : 1.00&nbsp;&nbsp;</td>-->
				</tr>
			</table>
		</td>
	</tr>
	<tr height="36">
		<td background="images/menu_background.jpg">
			<table cellpadding="0" cellspacing="0" border="0">
				<tr valign="top">
                <td width="20" align="right" onMouseOver="fscroll('left')" onMouseOut="fscrollout()"><img src="images/left.gif" width="6" height="7">&nbsp;&nbsp;
					</td>
                <? create_menu();?>
					<td width="20">&nbsp;</td>
					<td><img src="images/border.jpg">&nbsp;<img src="images/border.jpg"></td>
					<td width="20">&nbsp;</td>
					<td><a href="#" onClick="fLogout()"><img src="images/menu_logout.jpg" alt="Logout" height="36" border="0" id="img_logout" onClick="fActiveMenu('logout',img_logout)" onMouseOver="MM_swapImage('img_logout','','images/menu_logout_over.jpg',1)" onMouseOut="fMouseOut('logout')"></a></td>
					<td width="20">&nbsp;</td>
					<td><a href="#" onClick="fGoHome()"><img src="images/menu_home.jpg" alt="Home" height="36" border="0" id="img_home" onClick="fActiveMenu('home',img_home)" onMouseOver="MM_swapImage('img_home','','images/menu_home_over.jpg',1)" onMouseOut="fMouseOut('home')"></a></td>
                    
                    <td width="20" align="right" onMouseOver="fscroll('right')" onMouseOut="fscrollout()">
                        <img src="images/right.gif" width="6" height="7">&nbsp;&nbsp;
                    </td>
				</tr>
			</table>
            	
			
		</td>
	</tr>
</table>
</body>
</html>
<?
function get_root_image() {
	$lrs=pg_query("select * from skeleton.tblmenu where fk_parent is null order by kd_menu");
	while ($lrow=pg_fetch_array($lrs)){
		$l_return.="'images/".$lrow["menu_pic_hover"]."',";
	}
	return substr($l_return,0,-1);
}

function create_menu(){
	$lrs=pg_query("select * from skeleton.tblmenu where fk_parent is null order by kd_menu");
	while ($lrow=pg_fetch_array($lrs)){
		if ($lrow["is_action"]=="t") {
			if ($lrow["type_action"]=="list") $l_link="list.php";
			elseif ($lrow["type_action"]=="add") $l_link="modal_add.php";
			elseif ($lrow["type_action"]=="edit") $l_link="modal_edit.php";
			elseif ($lrow["type_action"]=="other") $l_link=$lrow["nm_file_menu_other"];
		} else $l_link="submenu_content.php?parent=".$lrow["pk_id"];
		//echo $lrow["kd_menu"]."aaa";
		if (check_right($lrow["kd_menu"],false)) {
?>
            <td width="10">&nbsp;</td>
            <td><a href="<?=$l_link?>" target="content"><img src="images/<?=$lrow["menu_pic"]?>" alt="<?=$lrow["nama_menu"]?>" name="img_<?=$lrow["kd_menu"]?>" height="36"  border="0" id="img_<?=$lrow["kd_menu"]?>" onClick="fActiveMenu('<?=$lrow["kd_menu"]?>',img_<?=$lrow["kd_menu"]?>)" onMouseOver="MM_swapImage('img_<?=$lrow["kd_menu"]?>','','images/<?=$lrow["menu_pic_hover"]?>',1)" onMouseOut="fMouseOut('<?=$lrow["kd_menu"]?>')"></a></td>
<?
		}
	}
}
?>