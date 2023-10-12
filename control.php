<html>
<head>
	<title>.: <?=$_SESSION["application"]?> :.</title>
    <link href="css/text.css.php" rel="stylesheet" type="text/css">
</head>
<script language="javascript" src="js/coockie.js.php"></script>
<script language="javascript">
	var lCookieLP = "open";
	function fSwitchControl(){
		if (lCookieLP=="close"){
			top.document.getElementById("left_panel").width="100%";
			top.document.getElementById("tdLeftPanel").width="183";
			document.getElementById("img_control").src="images/left_panel_switch_open.jpg";
			lCookieLP="open";
		} else {
			top.document.getElementById("left_panel").width=0;
			top.document.getElementById("tdLeftPanel").width=0;
			document.getElementById("img_control").src="images/left_panel_switch_close.jpg";
			lCookieLP="close";
		}
	}
</script>
<body>
<table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
	<tr height="100%">
		<td background="images/left_panel_border.jpg" width="10" valign="middle"><a href="#" onClick="fSwitchControl()"><img src="images/left_panel_switch_open.jpg" border="0" id="img_control"></a></td>
	</tr>
</table>
</body>
</html>
