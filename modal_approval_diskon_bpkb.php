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
require 'requires/stok_utility.inc.php';

if($_SESSION["jenis_user"]!='HO'){
   $strmsg="Menu ini hanya bisa diakses oleh HO.<br>";  
   $j_action= "lInputClose=getObjInputClose();lInputClose.close()";
  }

$periode_awal = convert_date_english($_REQUEST["periode_awal"]);
$periode_akhir= convert_date_english($_REQUEST["periode_akhir"]);
$id_menu=trim($_REQUEST["id_menu"]);
get_data_menu($id_menu);	

//$id_edit = $_REQUEST['id_edit'];
//echo $id_edit;

$fk_sbg = $_REQUEST['fk_sbg'];
$nm_customer =  $_REQUEST['nm_customer'];
$no_cif = $_REQUEST['fk_cif'];
$diskon = $_REQUEST['diskon'];

if($_REQUEST["status"]=="Save") {
		cek_error();
	if(!$strmsg){
		save_data();
	}

}
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
	document.form1.status.value='Save';
	document.form1.submit();
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")){
		lInputClose=getObjInputClose();lInputClose.close()
	}
}
function fGetSbg(){
	fGetCustomNC(false,'sbg','fk_sbg','Ganti Lokasi',document.form1.fk_sbg,document.form1.fk_sbg,document.form1.tipe)
}

function fGetSbgData(){
	
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataSbgState
	lSentText="table=(select fk_sbg,nm_customer,diskon,fk_cif from tblinventory left join (select fk_sbg as fk_sbg1, diskon from data_fa.tbldiskon_bpkb) as tblmain on fk_sbg=fk_sbg1 left join tblcustomer on no_cif=fk_cif)as tbl&field=(nm_customer||'¿'||fk_cif||'¿'||diskon)&key=fk_sbg&value="+document.form1.fk_sbg.value
	lObjLoad.open("POST","ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}
function fGetDataSbgState(){	
	if (this.readyState == 4){
		//confirm(this.responseText)
		if (this.status==200 && this.responseText!="") {
			lTemp=this.responseText.split('¿');
			//confirm(lTemp[2])
			document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value=lTemp[0]
			document.getElementById('divfk_cif').innerHTML=document.form1.fk_cif.value=lTemp[1]
			document.form1.diskon.value=lTemp[2]
			document.getElementById('divdiskon').innerHTML=number_format(lTemp[2])
		} else {
			document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value="-"
			document.getElementById('divfk_cif').innerHTML=document.form1.fk_cif.value="-"
			document.getElementById('divdiskon').innerHTML=document.form1.diskon.value="-"
			
		}
	}
}
function fCheckSubmit(){
	
	if (document.form1.status.value=='Save') {
		return true
	}
	else return false
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
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="form1" id="form1" enctype="multipart/form-data" onSubmit="return fCheckSubmit()">
<input type="hidden" name="status">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="tipe" value="Diskon_BPKB">

<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<tr><td class="judul_menu" align="center">APPROVAL DISKON</td></tr>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
                        <tr bgcolor="efefef">
                            <td style="padding:0 5 0 5" width="20%">No KOntrak</td>
                            <td style="padding:0 5 0 5" width="30%"><input name="fk_sbg" type="text" class='groove_text ' size="20" id="fk_sbg"  onChange="fGetSbgData()" value="<?=$fk_sbg?>">&nbsp;<img src="images/search.gif" style="border:0px" align="absmiddle" onClick="fGetSbg()"></td>
                            <td style="padding:0 5 0 5" width="20%">No CIF</td>
            				<td style="padding:0 5 0 5" width="30%"><input type="hidden" name="fk_cif" class='groove_text' value="<?=$no_cif?>"><span id="divfk_cif"><?=$no_cif?></span></td>
                        </tr>
                        
                        <tr bgcolor="efefef">
                            <td style="padding:0 5 0 5" width="20%">Nama Customer</td>
                            <td style="padding:0 5 0 5" width="30%"><input type="hidden" name="nm_customer" class='groove_text' value="<?=$nm_customer?>"><span id="divnm_customer"><?=$nm_customer?></span></td>
                            
                            <td style="padding:0 5 0 5" width="20%">Diskon</td>
            				<td style="padding:0 5 0 5" width="30%"><input type="hidden" name="diskon" class='groove_text' value="<?=$diskon?>"><span id="divdiskon"><?=$diskon?></span></td>
                        </tr>
            </table>

<!-- end content begin -->
	  	</td>
    </tr>
</table>

<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" align="center">
	<tr height="20">
    	<td height="25" align="center" bgcolor="#D0E4FF">
            <input class="groove_button" type='button' name="btnGenerate" value='Approve' onClick="fDownload()">

		</td>
	</tr>
</table>
</form>
</body>
</html>
<?
//}

function get_data(){
	global $id_edit,$strmsg,$j_action,$no_cif,$fk_sbg,$nm_customer;

}

function save_data(){
	global $strmsg, $j_action,$id_edit,$fk_sbg,$diskon;
	$l_success=1;
	pg_query("BEGIN");

	if(!pg_query("insert into data_fa.tbldiskon_bpkb_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from data_fa.tbldiskon_bpkb where fk_sbg='".$fk_sbg."'")) $l_success=0;
		
	if(!pg_query("
	update data_fa.tbldiskon_bpkb set 
	is_approval='t'
	where fk_sbg='".$fk_sbg."'
	")) $l_success=0;

	if(!pg_query("insert into data_fa.tbldiskon_bpkb_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from data_fa.tbldiskon_bpkb where fk_sbg='".$fk_sbg."'")) $l_success=0;	
	
	
	//$l_success=0;
	if ($l_success==1){
		$strmsg="Data Berhasil ter-simpan.<br>";
		$j_action="lInputClose=getObjInputClose();lInputClose.close();";
		pg_query("COMMIT");
	}else{
		$strmsg="Error :<br>Data Gagal ter-simpan.<br>";
		pg_query("ROLLBACK");
	}


}

function cek_error(){
	global $strmsg,$j_action,$periode_awal,$periode_akhir2,$fk_sbg,$diskon;
	if($diskon <=0) {
		$strmsg.="Diskon Masih Kosong.<br>";
	}
	if($fk_sbg == '') {
		$strmsg.="No Kontrak Harus Diisi.<br>";
	}
	//showquery("select * from tblcustomer where no_cif='".$no_cif."' ");
	else if(!pg_num_rows(pg_query("select * from viewkontrak where no_sbg='".$fk_sbg."' "))){
		$strmsg.="No Kontrak ".$fk_sbg.", tidak ada.<br>";
		$j_action="document.form1.no_sbg.focus()";
	}	
	
	else if(pg_num_rows(pg_query("select * from tblinventory where fk_sbg='".$fk_sbg."' and tgl_lunas is not null"))){
		//$strmsg.="No SBG ".$fk_sbg.", sudah pelunasan.<br>";
		//$j_action="document.form1.no_sbg.focus()";		
	}
	
	if($strmsg)$strmsg="Error :<br>".$strmsg;
}

?>