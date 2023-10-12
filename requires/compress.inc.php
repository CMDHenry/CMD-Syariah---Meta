<?
header("Content-Encoding: gzip");
function gztrim ($d) {return gzencode($d,9);}
ob_start('gztrim');
?>