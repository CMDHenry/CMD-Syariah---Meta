<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/numeric.inc.php';
require 'requires/validate.inc.php';
require 'requires/timestamp.inc.php';
require 'requires/input.inc.php';
require 'requires/cek_error.inc.php';
require 'requires/module.inc.php';
require 'requires/db_utility.inc.php';
require 'classes/select.class.php';
require 'requires/file.inc.php';



$images_name=trim($_REQUEST["images_name"]);
get_data_menu($id_menu);
?>
<html>
<head>
	<title>.: <?=$_SESSION["application"]?> :.</title>
    <link href="text.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src='js/calendar.js.php'></script>
<script language='javascript' src='js/alert.js.php'></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src='js/validate.js.php'></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript'>

function fLoad(){
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	}
	else if($j_action){
		echo $j_action;
	}else{
		echo "document.form1.kd_menu.focus();";
	}
?>
}
</script>
<body onLoad="fLoad()" bgcolor="#fafafa">
<form name="form1" action="modal_module_pic_view.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="id_menu" value="<?=$id_menu?>">
<input type="hidden" name="status">
<!--<input type="hidden" name="kd_menu" value="<?//=$kd_menu?>">-->
<table cellpadding="0" cellspacing="0" border="0" width="794" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<td align="center" class="judul_menu">PIC <?=strtoupper($nm_menu)?></td>
			</table>		
		</td>
	</tr>
    <tr>
		<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
            	<tr bgcolor="efefef">
					<td style="padding:0 5 0 5"><center><img src="./upload/<?=$images_name?>" width="400px" height="400px"></center></td>
				</tr> 
			</table>
<!-- end content begin -->
    	</td>
    </tr>
	<tr><td height="25" align="center" bgcolor="#D0E4FF" class="border">
    	&nbsp;
		<!--
        <input type="button" class="groove_button" name="btnsimpan" value="Simpan" onClick="fSave()">
		&nbsp;<input type="button"  class="groove_button" value="Batal" onClick="fBatal()">--></td>
	</tr>
</table>
</form>
</body>
</html>
