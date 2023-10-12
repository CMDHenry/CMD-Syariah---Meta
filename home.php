<?
require 'requires/session.inc.php';
require 'requires/authorization.inc.php';

?>
<!--<meta http-equiv="refresh" content="10" /> auto refresh-->
<html>
<head>
    <title>.: <?=$_SESSION['application']?>:.</title>
    <link href="css/text.css.php" rel="stylesheet" type="text/css">
	<link href="css/menu.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src="js/coockie.js.php"></script>
<body style="margin:0 0 0 0;" <? if(strtolower($_SESSION['username'])!='superuser'){ echo "onLoad=\"SetCookie('".strtolower($_SESSION['username'])."',1)\""; } ?>>
<table cellpadding="0" cellspacing="0" border="0" width="100%" height="99%">
  <tr height="53">
    	<td colspan="3">
    		<iframe name="head" src="head.php" width="100%" height="53" frameborder="0" scrolling="no"></iframe>
        </td>
    </tr>
    <tr>
    	<td>
    		<iframe name="content" src="blank.php" width="100%" height="100%" frameborder="0" scrolling="no"></iframe>
        </td>
    	<td width="10">
    		<iframe name="control_left_panel" src="control.php" width="100%" height="100%" frameborder="0" scrolling="no"></iframe>
        </td>
    	<td width="183" id="tdLeftPanel">
    		<iframe name="left_panel" id="left_panel" src="left_panel.php" width="100%" height="100%" frameborder="0" scrolling="no"></iframe>
        </td>
    </tr>
</table>
</body>
</html>