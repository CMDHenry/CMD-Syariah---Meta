<?
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';

$parent=$_REQUEST["parent"];
?>
<html>
<head>
	<title>.: <?=$_SESSION["application"]?> :.</title>
    <link href="text.css.php" rel="stylesheet" type="text/css">
    <link href="menu.css.php" rel="stylesheet" type="text/css">
    
</head>
<script language='javascript' src="js/misc.js.php"></script>
<script language='javascript'>
	var iTimer;	
	function fMouseOver(pIndex,pObj) {
		var l_var2 = document.getElementById('subMenu');
		parent.frames["submenu_content"].menu.st(pIndex,true,(pObj.offsetLeft-l_var2.scrollLeft)+3)
	}
	
	function fMouseOut(pMenu,pIndex){
		if (typeof(pIndex)!="undefined")parent.frames["submenu_content"].menu.st(pIndex);
	}
	
	function fLoad(){
		subMenu.style.width=document.body.offsetWidth-40
	}
	
	function fResetting(){
		subMenu.style.width=document.body.offsetWidth-40
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
<body onLoad="fLoad()" onResize="fResetting()">
<table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%" style="border-bottom:solid 1px #8f8f8f">
	<tr bgcolor="#EEEEEE" valign="bottom" height="39">
		<td width="20" align="right" onMouseOver="fscroll('left')" onMouseOut="fscrollout()"><img src="images/left.gif" width="6" height="7">&nbsp;&nbsp;
		</td>
		<td>
		<div id="subMenu" style="overflow:hidden">
			<table cellpadding="0" cellspacing="0" border="0">
				<tr height="22" align="center">
					<td width="15"></td>
					<td width="1" background="images/blue_button_border.gif"></td>
                    <? create_submenu()?>
					<td nowrap="nowrap" width="1" background="images/blue_button_border.gif"></td>
			</tr>
			</table>
            </div>
            
		</td>
        <td width="20" align="right" onMouseOver="fscroll('right')" onMouseOut="fscrollout()">
			<img src="images/right.gif" width="6" height="7">&nbsp;&nbsp;
		</td>
	</tr>
</table>
</body>
</html>
<?
function create_submenu(){
	global $parent;
	
	$lrs_submenu=pg_query("select * from skeleton.tblmenu where fk_parent='".$parent."' and is_hidden='t' order by kd_menu");
	$row_count_submenu=array();
	while($row_submenu=pg_fetch_array($lrs_submenu)){
		if (check_right($row_submenu["kd_menu"],false)) {
			//echo $row_count["kd_menu"];
			$row_count_submenu[$parent]+=1;
		}
	}	
	
	$l_count_menu=0;
	$count_baris_data=1;
	$lrs=pg_query("select * from skeleton.tblmenu where fk_parent='".$parent."' and is_hidden='t' order by kd_menu");
	$baris_data=pg_num_rows($lrs);
	
	//showquery("select * from skeleton.tblmenu where fk_parent='".$parent."' order by kd_menu");
	while ($lrow=pg_fetch_array($lrs)){
		
		if (check_right($lrow["kd_menu"],false)) {			
			if ($lrow["is_action"]=="t") {
				if ($lrow["jenis_module"]=="list") $l_link="list.php";
				elseif ($lrow["jenis_module"]=="list_select") $l_link="list_select.php";
				elseif ($lrow["jenis_module"]=="add") $l_link="modal_add.php";
				elseif ($lrow["jenis_module"]=="edit") $l_link="modal_edit.php";
				elseif ($lrow["jenis_module"]=="report") $l_link="list_report.php";
				elseif ($lrow["jenis_module"]=="other") $l_link=$lrow["nm_file_module_other"];
				
	?>
						<td width="84" class="submenu"><nobr>&nbsp;<a href="<?=$l_link?>?module=<?=$lrow["pk_id"]?>" target="submenu_content"><?=strtoupper($lrow["nama_menu"])?></a>&nbsp;<nobr></td>
						<?
							if($count_baris_data==$baris_data){
						?>
							<td nowrap="nowrap" background="images/blue_button_border.gif"></td>
						<? 
							}else{
						?>	     
							<td width="5" class="separator">&nbsp;</td>
						<? }?> 
	<?
			} else {
				$l_link="submenu_content.php";		
					
	?>
						<td width="90" class="submenu" id="img_<?=$lrow["kd_menu"]?>" onMouseOver="fMouseOver(<?=$l_count_menu?>,this)" onMouseOut="fMouseOut('<?=$lrow["kd_menu"]?>',<?=$l_count_menu?>)"><nobr>&nbsp;<?=strtoupper($lrow["nama_menu"])?>&nbsp;<nobr></td>
					<?
						//echo $lrow["nama_menu"]."=".$count_baris_data;
						if($count_baris_data==$baris_data){
					?>
						<td nowrap="nowrap" background="images/blue_button_border.gif"></td>
					<? 
						}else{
							if($count_baris_data!=$row_count_submenu[$parent]){
					?>	     
						<td width="5" class="separator">&nbsp;</td>
					<? 
							}
						}?>   
	<?	
				
			}
			$count_baris_data++;
			//showquery("select * from skeleton.tblmenu where (counter =1 and is_hidden='t' and kd_menu like '".$lrow["kd_menu"]."%')");
			$lrs_row_count=pg_query("select * from skeleton.tblmenu 
			where (counter =1 and is_hidden='t' and kd_menu like '".$lrow["kd_menu"]."%')");
			$row_count_count_menu=0;
			while($row_count=pg_fetch_array($lrs_row_count)){
				if (check_right($row_count["kd_menu"],false)) {
					//echo $row_count["kd_menu"];
					$row_count_count_menu+=1;
				}
			}
			$l_count_menu+=$row_count_count_menu;
			
		}
		
	}
}
?>