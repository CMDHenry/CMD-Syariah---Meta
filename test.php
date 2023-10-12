<?
require 'requires/config.inc.php';
require 'requires/general.inc.php';
require 'requires/numeric.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/accounting_utility.inc.php';
require 'requires/stok_utility.inc.php';
require 'requires/timestamp.inc.php';
require 'classes/smtp.class.php';
pg_query('begin');
$l_success=1;
echo round(4.0905,3).'<br>';
echo number_format(4.0905,3).'<br>';
echo convert_money("",4.0905,3).'<br>';
echo convert_money("",round(4.0905,3),3).'<br>';
?>

