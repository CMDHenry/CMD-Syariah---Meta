<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/numeric.inc.php';
require 'requires/timestamp.inc.php';
require 'requires/db_utility.inc.php';
require 'classes/select.class.php';
require 'requires/file.inc.php';
require 'requires/accounting_utility.inc.php';
require 'classes/smtp.class.php';
require 'classes/excel.class.php';

$periode_awal = convert_date_english($_REQUEST["periode_awal"]);
$periode_akhir= convert_date_english($_REQUEST["periode_akhir"]);

$id_edit = $_REQUEST['id_edit'];
$fk_blanko = $_REQUEST['fk_blanko'];

if($_REQUEST["status"]=="Download") {
	cek_error();
	if(!$strmsg){
		download();
	}

}
if ($_REQUEST["pstatus"]=="edit") get_data();

//if($_REQUEST["status"]!="Download"){
?>
<html>
<head>
	<title>.: <?=$_SESSION["application"]?> :.</title>
    <link href="css/text.css.php" rel="stylesheet" type="text/css">
	<link rel="stylesheet" title="Style CSS" href="js/cwcalendar.css.php" type="text/css" media="all" />
</head>
<script language='javascript' src="js/alert.js.php"></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src='js/calendar.js.php'></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src='js/validate.js.php'></script>
<!--<script language='javascript' src="js/table_v2.js.php"></script>
-->
<script language='javascript'>

function fDownload(){
	document.form1.status.value='Download';
	document.form1.submit();
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")){
		lInputClose=getObjInputClose();lInputClose.close()
	}
}
//function fGetBlanko(){
	//fGetNC(false,'20171100000013','fk_blanko','Ganti Tipe Kendaraan',document.form1.fk_blanko,document.form1.fk_blanko,'','','tipe_active')
//}
function fGetBlanko(){
	fGetNC(false,'20171100000013','fk_blanko','Ganti Provinsi',document.form1.fk_blanko,document.form1.fk_blanko,'','','20171200000200')
}
function fGetItemKendaraan(){
	fGetNC(false,'20150400000046','fk_tipe_kendaraan','Ganti Item Kendaraan',document.form1.fk_item,document.form1.fk_item,'','','item_active')
}
function fLoad(){
	//parent.parent.document.title="TTBJ";
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	} elseif($j_action){
		echo $j_action;
	}else{
		echo "document.form1.periode_awal.focus();";
	}
?>
}
</script>
<body onLoad="fLoad()" bgcolor="#fafafa">
<form name="form1" action="<?=$_SERVER['PHP_SELF']?>" method="post">
<input type="hidden" name="status">
<input type="hidden" name="pstatus">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<tr><td class="judul_menu" align="center">CETAK BLANKO</td></tr>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
            <? //if(get_rec("data_gadai.tblproduk_gadai","fk_blanko","no_sbg='".$id_edit."'")==NULL){ ?>	
                        <tr bgcolor="efefef">
                            <td style="padding:0 5 0 5" width="20%">No Blanko</td>
                            <td style="padding:0 5 0 5" width="80%"><input name="fk_blanko" type="text" class='groove_text ' size="20" id="fk_blanko" value="<?=$fk_blanko?>">&nbsp;<img src="images/search.gif" style="border:0px" align="absmiddle" onClick="fGetBlanko()"></td>
                        </tr>
                        
            <? //} else { ?>
            			<!--<tr bgcolor="efefef">
                            <td style="padding:0 5 0 5" width="20%">No Blanko</td>
                            <td style="padding:0 5 0 5" width="80%"><input type="hidden" name="fk_blanko" value="<?=$fk_blanko?>" class='groove_text' onKeyUp="" ><?=get_rec("data_gadai.tblproduk_gadai","fk_blanko","no_sbg='".$id_edit."'")?></td>
                        </tr>-->
            <? //} ?>
            			
            </table>

<!-- end content begin -->
	  	</td>
    </tr>
</table>

<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" align="center">
	<tr height="20">
    	<td height="25" align="center" bgcolor="#D0E4FF">
            <input class="groove_button" type='button' name="btngenerate" value='Cetak' onClick="fDownload()">

		</td>
	</tr>
</table>
</form>
</body>
</html>
<?
//}

function get_data(){
	global $id_edit,$fk_blanko;
	
	if(pg_num_rows(pg_query("select * from data_gadai.tblproduk_gadai where no_sbg='".$id_edit."' "))){
	$l_pk="sbg";
	$tbl="tblproduk_gadai";
	$tgl_jt="tgl_jatuh_tempo";
}else{
	$l_pk="sbg";
	$tbl="tblproduk_cicilan";
	$tgl_jt="tgl_jatuh_tempo_satu";
}

	$fk_blanko=get_rec("data_gadai.".$tbl."","fk_blanko","no_sbg='".$id_edit."'");
	
}

function download(){
	global $id_edit,$upload_path,$strmsg,$status_pengiriman_ssu,$fk_item,$fk_tipe_kendaraan,$ekspedisi,$fk_blanko;
	$l_success=1;
	pg_query("BEGIN");
	
	if(pg_num_rows(pg_query("select * from data_gadai.tblproduk_gadai where no_sbg='".$id_edit."' "))){
	$l_pk="sbg";
	$tbl="tblproduk_gadai";
	$tgl_jt="tgl_jatuh_tempo";
}else{
	$l_pk="sbg";
	$tbl="tblproduk_cicilan";
	$tgl_jt="tgl_jatuh_tempo_satu";
}

	
	//if(get_rec("data_gadai.tblproduk_gadai","fk_blanko","no_sbg='".$id_edit."'")==NULL){
		if(!pg_query("insert into data_gadai.".$tbl."_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from data_gadai.".$tbl." where no_".$l_pk." = '".$id_edit."' "))$l_success=0;
		
		if(!pg_query("update data_gadai.".$tbl." set fk_blanko='".$fk_blanko."' where no_".$l_pk."='".$id_edit."'")) $l_success=0;
		//showquery("update data_gadai.".$tbl." set fk_blanko='".$fk_blanko."' where no_".$l_pk."='".$id_edit."'");
		
		if(!pg_query("insert into data_gadai.".$tbl."_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from data_gadai.".$tbl." where no_".$l_pk." = '".$id_edit."' "))$l_success=0;
	//}
	
	//Kosongin FK SBG nya dulu//
	
	$fk_blanko_lama=get_rec("data_gadai.tblblanko","kd_blanko","fk_sbg='".$id_edit."' ");
	if(!pg_query("insert into data_gadai.tblblanko_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from data_gadai.tblblanko where kd_blanko = '".$fk_blanko_lama."' "))$l_success=0;
		
		if(!pg_query("update data_gadai.tblblanko set fk_sbg=NULL where kd_blanko='".$fk_blanko_lama."'")) $l_success=0;
		//showquery("update data_gadai.tblblanko set fk_sbg=NULL where kd_blanko='".$fk_blanko."'");
		
		if(!pg_query("insert into data_gadai.tblblanko_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from data_gadai.tblblanko where kd_blanko = '".$fk_blanko_lama."' "))$l_success=0;

	
	//Baru Update dengan blanko yang mau diisi no SBG lama	
		
		if(!pg_query("insert into data_gadai.tblblanko_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from data_gadai.tblblanko where kd_blanko = '".$fk_blanko."' "))$l_success=0;
		
		if(!pg_query("update data_gadai.tblblanko set fk_sbg='".$id_edit."' where kd_blanko='".$fk_blanko."'")) $l_success=0;
		//showquery("update data_gadai.tblblanko set fk_sbg='".$id_edit."' where kd_blanko='".$fk_blanko."'");
		
		if(!pg_query("insert into data_gadai.tblblanko_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from data_gadai.tblblanko where kd_blanko = '".$fk_blanko."' "))$l_success=0;
		
	

	//$l_success=0;
	if ($l_success==1){
		//$strmsg="Data saved.<br>";
		//$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		header("location:print/print_kontrak.php?id_edit=".$id_edit."");
		pg_query("COMMIT");	
	}else{
		$strmsg="Error :<br>Save failed.<br>";
		pg_query("ROLLBACK");
	}
	
	
	
}
function cek_error(){
	global $strmsg,$j_action,$fk_blanko,$id_edit;

	if($fk_blanko == '') {
		$strmsg.="No Blanko Harus Diisi.<br>";
	}
	
		else if(!pg_num_rows(pg_query("select * from data_gadai.tblblanko where kd_blanko='".$fk_blanko."' "))){
			$strmsg.="No Blanko ".$fk_blanko.", tidak ada.<br>";
			$j_action="document.form1.fk_blanko.focus()";
	}
		
		else if($fk_blanko!=get_rec("data_gadai.tblblanko","kd_blanko","fk_sbg='".$id_edit."' ")){
			
	if(!pg_num_rows(pg_query("select * from data_gadai.tblblanko where kd_blanko='".$fk_blanko."' and fk_sbg is NULL"))){
		$strmsg.="No Blanko ".$fk_blanko.", sudah dipakai di No SBG ".$id_edit." .<br>";
		$j_action="document.form1.fk_blanko.focus()";
		
		}
	}
	
	//showquery("select * from data_gadai.tblblanko where fk_sbg='".$id_edit."' ");
	
	if($strmsg)$strmsg="Error :<br>".$strmsg;
	
}

?>