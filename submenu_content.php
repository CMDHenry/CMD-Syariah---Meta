<?
$parent=$_REQUEST["parent"];

if($_REQUEST['next_location'])$src=$_REQUEST['next_location'];
else $src = 'blank.php';
?>
<html>
<head>
	<title>.:HAYATI :.</title>
</head>
<frameset rows="40,*" framespacing="0" border="0" frameborder="0">
	<frame name="submenu" target="submenu_content" src="submenu.php?parent=<?=$parent?>" marginwidth="0" marginheight="0" scrolling="no" noresize>
	<frame name="submenu_content" src="<?=$src?>?parent_root=<?=$parent?>" marginwidth="0" marginheight="0" scrolling="no" noresize>
</frameset>
<noframes>
<body>
This page uses frames, but your browser doesn't support them.
</body>
</noframes>
</html>
