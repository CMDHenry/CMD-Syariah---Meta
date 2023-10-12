<?php
require 'requires/config.inc.php';
require 'requires/general.inc.php';
require 'requires/numeric.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/file.inc.php';

$no_image = trim($_REQUEST["no_image"]);
$no_id = trim($_REQUEST["no_id"]);
if(!@rename($upload_path_pic.$no_image.".png", $upload_path_pic.$no_id.".png")){		
	echo 'SUKSES';
}else{
	echo $upload_path_pic.$no_image.".png<br>";	
	echo $upload_path_pic.$no_id.".png";
	
}
	
?>
