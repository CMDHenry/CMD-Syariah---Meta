<?php
require 'requires/config.inc.php';
require 'requires/session.inc.php';
//require 'requires/authorization.inc.php';
require 'requires/file.inc.php';

$file=$_REQUEST["file"];
$name=$_REQUEST["name"];

$file=$upload_path_pic.$file;
$ext=substr($file,strrpos($file,"."));

header("Cache-Control: ");
header("Pragma: ");
header("Content-Type:application/octet-stream");
header("Content-Length: ".filesize($file));
switch (strtolower($ext)){
	case ".jpg" or ".gif" or ".pdf" or ".doc" or ".png":
		header("Content-Disposition: inline; filename=".$name);
		break;
	default:
		header("Content-Disposition: attachment; filename=".$name);
}
if (file_exists($file)){
	readfile($file);
}
?>