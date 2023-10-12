<?
require 'requires/config.inc.php';
require 'requires/general.inc.php';
require 'requires/numeric.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/accounting_utility.inc.php';
require 'requires/stok_utility.inc.php';
require 'requires/validate.inc.php';
require 'classes/smtp.class.php';

$l_success=1;

$id_edit=$_REQUEST["id_edit"]='44101230600359';

$query="select * from tblinventory left join tblcustomer on fk_cif=no_cif where fk_sbg ='".$id_edit."'";
$lrs=pg_query($query);
$penerima=$lrow["email"];

if($_SERVER['HTTP_HOST']=="116.90.163.21"){
	$ldirPath = "/www/erp/website/file/temp/"; //live	
	$fileFolder = "/www/bin/php -f  \"/www/erp/website/print/"; //live
}
if($_SERVER['HTTP_HOST']=="meta-server"){
	$ldirPath =$_SERVER['DOCUMENT_ROOT']."/capella/website/file/temp/"; //META
	$fileFolder = "c:/php/php.exe -f \"".$_SERVER['DOCUMENT_ROOT']."/capella/website/print/"; //META
}

$fileSourceName = "print_kontrak.php";
$pathFolder=$fileFolder.$fileSourceName;

$fileName=$id_edit.".pdf";
exec($pathFolder."\" 1 KONTRAK ".$id_edit." > \"".$ldirPath.$fileName."\"");
//echo $pathFolder."\" 1 KONTRAK ".$id_edit." > \"".$ldirPath.$fileName."\"";

$_sender = "info@meta-technology.com";//sender smtp
$_pwd	 = "metatech";
$_smtp 	 = "mail.meta-technology.com";

$title ='Kontrak '.$id_edit;
$body='Berikut Draft Kontrak';

$pengirim='danny.horia@meta-technology.com';
$penerima='danny.horia@meta-technology.com';
//$penerima="m.fahmi.riyadi@cmd.co.id";

$lmail = new SMTP;
$lmail->delivery('relay');
$lmail->from($pengirim);
$lmail->addto($penerima);
//$lmail->attachfile($ldirPath.$fileName,$fileName,'','attachment','base64',true);

$lmail->html($body);
$lmail->relay($_smtp,$_sender,$_pwd);
if($lmail->send($title)){
	$strmsg= 'Email Sent';
}else{
	$strmsg= 'Email Failed';
}

echo "<script>
alert('".$strmsg."')
window.close();

</script>";


?>

