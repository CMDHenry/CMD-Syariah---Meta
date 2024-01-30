<?
$username="cmd";
$password="cmdsyariah";
$hostname="116.90.163.21";
$database="capella";
$port="5432";

$str_connect = (($hostname != "") ? "host=$hostname " : "") . (($port!="")?"port=$port ":"") ."dbname=$database user=$username password=$password";
// echo $str_connect;
$db = pg_connect($str_connect) or die("Could not connect to the postgreSQL server!");
?>