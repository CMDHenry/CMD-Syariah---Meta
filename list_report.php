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
	<link rel="stylesheet" title="Style CSS" href="js/cwcalendar.css.php" type="text/css" media="all" />

</head>
<script language='javascript' src="js/alert.js.php"></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src="js/dd_menu.js.php"></script>
<script language='javascript' src="js/misc.js.php"></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src="js/calendar.js.php"></script>
<script language="javascript">
function fModal(pType,pName){
	switch (pType){
		default:
		 show_modal('report/'+pType+'?report='+encodeURI(pName),'status:no;help:no;dialogWidth:1000px;dialogHeight:500px')
		break;
	}
}

function fLoad(){

}
</script>
<body bgcolor="#f3f3f3" onLoad="fLoad()">
<?
include_once("includes/menu.inc.php");

?>
<form name="form1" action="<?=$_SERVER['PHP_SELF']?>" style="margin:0 0 0 0">
<input type="hidden" name="module" style="width:200" value="">
<table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
	<tr background="images/submenu_background.jpg" height="37">
		<td width="20"></td>
		<td class="selectMenu" colspan="2"><?=strtoupper($row_menu["root_menu"])?></td>
	</tr>
	<tr valign="top">
		<td width="20"></td>
		<td>
        	<div id="divContent" style="overflow:auto; height:500">
			<table cellpadding="0" cellspacing="1" border="0" width="100%" class="border">                       
                    <tr bgcolor='#C8C8C8' class="judul">
                        <td colspan="2">Report</td>
                    </tr>  
                   	<? create_report()?>                        
             </table>
             </div>   
         </td>
      </tr>
</table>
</form>
</body>
</html>

<?

function create_report(){
	global $module;

	$lrs=pg_query("
	select * from (
		select * from skeleton.tblmenu 
		where fk_parent='".$module."' and is_hidden!='f'
	)as tblmain order by kd_menu
	");
/*	showquery("
	select * from (
		select * from skeleton.tblmenu 
		where fk_parent='".$module."' and is_hidden!='f'
	)as tblmain order by kd_menu
	");
*/	while($lrow=pg_fetch_array($lrs)){	
?>		
	 <table cellpadding="0" cellspacing="1" border="0" width="100%" class="border">
			<tr bgcolor="e0e0e0" onMouseOver="fTRColor(this,'over')" onMouseOut="fTRColor(this,'out')">
				<td style="padding:0 5 0 5" width="30%" ><?=strtoupper($lrow["nama_menu"])?></td>
<!--                <td style="padding:0 5 0 5" width="20%" align="center"><?=strtoupper($lrow["caption_module_other"])?></td>
-->				<td style="padding:0 5 0 5" align="right">&nbsp;
<?                				
				if(check_right($lrow["kd_menu"],false)){
?>					
					<input type="button" value=" Download " class="groove_button" onClick="fModal('<?=$lrow["nm_file_module_other"]?>','<?=strtoupper($lrow["nama_menu"])?>')">
<?                    
				}
?>				
				</td>
			</tr>
      </table>      
<?
	}
	
?>
	 <table cellpadding="0" cellspacing="1" border="0" width="100%" class="border">
			<tr bgcolor="e0e0e0" onMouseOver="fTRColor(this,'over')" onMouseOut="fTRColor(this,'out')">
				<td style="padding:0 5 0 5" width="30%" ></td>
				<td style="padding:0 5 0 5" align="right">&nbsp;
				</td>
			</tr>
      </table>      

<?	
	
	
}


function get_data_module(){
	global $module,$row_menu,$parent_root,$pk_id_module;
	
	$module=$_REQUEST["module"];	//kd_menu/fk_menu
	$row_menu=pg_fetch_array(pg_query("select * from skeleton.tblmenu where pk_id='".$module."' "));
	
	if (!check_right($row_menu["kd_menu"])){
		header("location:error_access.php");
	}
	
	$parent_root=get_rec("skeleton.tblmenu","pk_id","kd_menu='".substr($row_menu["kd_menu"],0,2)."'"); 
	$pk_id_module=get_rec("skeleton.tblmodule","pk_id","fk_menu='".$module."'"); //pk_id tblmodule/fk_module_fields
}

?>