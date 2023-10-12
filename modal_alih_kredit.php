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

$id_menu=trim($_REQUEST["id_menu"]);
get_data_menu($id_menu);	

$id_edit = $_REQUEST['id_edit'];

//echo $id_edit;
if($id_edit){
	$fk_sbg = get_rec("data_gadai.tbllelang","fk_sbg","no_kwitansi='".$id_edit."'");
	$nm_penerima = get_rec("data_gadai.tbllelang","nm_penerima","no_kwitansi='".$id_edit."'");
	$fk_sbg_ar = $_REQUEST['fk_sbg_ar'];
	get_data();
}
else {
	$fk_sbg = $_REQUEST['fk_sbg'];
	$nm_customer =  $_REQUEST['nm_customer'];
	$fk_sbg_ar = $_REQUEST['fk_sbg_ar'];
}
if($_REQUEST["status"]=="Save") {
	cek_error();
	if(!$strmsg){
		if ($id_edit) save_data();
		else save_data();
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

function fSave(){
	document.form1.status.value='Save';
	document.form1.submit();
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")){
		lInputClose=getObjInputClose();lInputClose.close()
	}
}
function fGetSbg(){
	fGetCustomNC(false,'sbg','fk_sbg_ar','Ganti Lokasi',document.form1.fk_sbg_ar,document.form1.fk_sbg_ar,document.form1.tipe)
}

function fGetSbgData(){
	
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataSbgState
	lSentText="table=(select fk_produk,no_sbg,fk_cif,nm_customer from viewkontrak left join viewtaksir on no_fatg=fk_fatg left join tblcustomer on no_cif=fk_cif) as tblmain&field=(nm_customer||'¿'||fk_cif||'¿'||fk_produk)&key=no_sbg&value="+document.form1.fk_sbg_ar.value
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
			document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value=lTemp[0]
			document.getElementById('divno_cif').innerHTML=document.form1.no_cif.value=lTemp[1]
			document.form1.fk_produk.value=lTemp[2]
		} else {
			document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value="-"
			document.getElementById('divno_cif').innerHTML=document.form1.no_cif.value="-"
		}
	}
}

function fGetCIF(){
	fGetNC(false,'20170800000001','no_cif_new','Ganti Lokasi',document.form1.no_cif_new,document.form1.no_cif_new)
}

function fGetCIFData(){
   lObjLoad = getHTTPObject()
   lObjLoad.onreadystatechange=fGetDataCIFState
   lSentText="table=tblcustomer&field=(no_id||'¿'||nm_customer)&key=no_cif&value="+document.form1.no_cif_new.value
   lObjLoad.open("POST","ajax/get_data.php",true);
   lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
   lObjLoad.setRequestHeader("Content-Length",lSentText.length)
   lObjLoad.setRequestHeader("Connection","close")
   lObjLoad.send(lSentText);
}

function fGetDataCIFState(){
  
   if (this.readyState == 4){
      if (this.status==200 && this.responseText!="") {
         lTemp=this.responseText.split('¿');	
		 //document.form1.no_id.value=lTemp[0]
		 document.getElementById('divnm_customer_new').innerHTML=document.form1.nm_customer_new.value=lTemp[1]
      } else {
		 //lAlerttxt="No CIF tidak ada"
		 // alert("Error : <br>"+lAlerttxt)
		 //document.form1.no_id.value="-"
		 //document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value="-"
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
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	} elseif($j_action){
		echo $j_action;
	}else{
		echo "document.form1.fk_sbg.focus();";
	}
?>
}
</script>
<body onLoad="fLoad()" bgcolor="#fafafa">
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="form1" id="form1" enctype="multipart/form-data" onSubmit="return fCheckSubmit()">
<input type="hidden" name="status">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="tipe" value="Perpanjangan">
<input type="hidden" name="fk_produk" value="<?=$fk_produk?>">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<tr><td class="judul_menu" align="center">Alih Jual Kredit</td></tr>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
            			<tr bgcolor="efefef">
                            <td style="padding:0 5 0 5" width="20%">No Kontrak</td>
                            <td style="padding:0 5 0 5" width="30%"><input type="hidden" name="fk_sbg" class='groove_text' value="<?=$fk_sbg?>"><?=$fk_sbg?></td>
                            <td style="padding:0 5 0 5" width="20%">Nama Penerima</td>
                            <td style="padding:0 5 0 5" width="30%"><input type="hidden" name="nm_penerima" class='groove_text' value="<?=$nm_penerima?>"><?=$nm_penerima?></td>
                        </tr>
                        <tr bgcolor="efefef">
                            <td style="padding:0 5 0 5" width="20%">No Kontrak Baru</td>
                            <td style="padding:0 5 0 5" width="30%"><input name="fk_sbg_ar" type="text" class='groove_text ' size="20" id="fk_sbg_ar"  onChange="fGetSbgData()" value="<?=$no_sbg_ar?>">&nbsp;<img src="images/search.gif" style="border:0px" align="absmiddle" onClick="fGetSbg()"></td>
                            <td style="padding:0 5 0 5" width="20%">Nama Customer</td>
                            <td style="padding:0 5 0 5" width="30%"><input type="hidden" name="nm_customer" class='groove_text' value="<?=$nm_customer?>"><span id="divnm_customer"><?=$nm_customer?></span></td>
                        </tr>
                        
                       <!-- <tr bgcolor="efefef">
                           <td style="padding:0 5 0 5" width="20%">No CIF </td>
            				<td style="padding:0 5 0 5" width="30%"><input type="hidden" name="no_cif" class='groove_text' value="<?=$no_cif?>" onChange="fGetCIFData()"><span id="divno_cif"><?=$no_cif?></span></td>
                            
                        </tr>   -->                             
            </table>
<!-- end content begin -->
	  	</td>
    </tr>
</table>

<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" align="center">
	<tr height="20">
    	<td height="25" align="center" bgcolor="#D0E4FF">
            <input class="groove_button" type='button' name="btngenerate" value='Alihkan' onClick="fSave()">

		</td>
	</tr>
</table>
</form>
</body>
</html>
<?
//}

function get_data(){
	global $id_edit,$strmsg,$j_action,$no_cif,$fk_sbg,$nm_customer,$no_cif_new,$nm_customer_new,$id_edit,$no_fatg,$no_sbg_ar,$nm_penerima;

	/*$lrow1=pg_fetch_array(pg_query("select fk_produk,no_sbg,fk_cif,nm_customer from viewkontrak left join viewtaksir on no_fatg=fk_fatg left join tblcustomer on no_cif=fk_cif where no_sbg='".$fk_sbg."'"));
	showquery("select fk_produk,no_sbg,fk_cif,nm_customer from viewkontrak left join viewtaksir on no_fatg=fk_fatg left join tblcustomer on no_cif=fk_cif where no_sbg='".$fk_sbg."'");*/
	$lrow=pg_fetch_array(pg_query("select fk_produk,no_sbg,fk_cif,no_sbg_ar from viewkontrak left join viewtaksir on no_fatg=fk_fatg left join tblcustomer on no_cif=fk_cif left join (select no_sbg_ar from data_gadai.tbltaksir_umum)as tbltaksir on no_sbg_ar=no_sbg where no_sbg_lama='".$fk_sbg."'"));
	//showquery("select fk_produk,no_sbg,fk_cif,nm_customer,no_sbg_ar from viewkontrak left join viewtaksir on no_fatg=fk_fatg left join tblcustomer on no_cif=fk_cif left join (select no_sbg_ar from data_gadai.tbltaksir_umum)as tbltaksir on no_sbg_ar=no_sbg where no_sbg_lama='".$fk_sbg."'");
	//echo $lrow["fk_cif"];
	$no_cif=$lrow["fk_cif"];
	$no_fatg=$lrow["no_fatg"];
	$nm_customer=$lrow["nm_customer"];
	$no_sbg_ar=$lrow["no_sbg_ar"];
	
	
}

function cek_error(){
	global $strmsg,$j_action,$no_cif_new,$periode_akhir2,$fk_sbg,$fk_produk,$fk_sbg_ar,$no_sbg_ar,$nm_customer,$nm_penerima;
	
/*	if($fk_sbg_r == '') {
		$strmsg.="No Kontrak Harus Diisi.<br>";
	}
	*/
	if(!pg_num_rows(pg_query("select * from data_gadai.tbltaksir_umum where no_sbg_ar='".$fk_sbg_ar."'"))){
		$strmsg.="No SBG AR tidak ada.<br>";
		$j_action="document.form1.no_cif.focus()";
	}
	
	if(!pg_num_rows(pg_query("select fk_produk,no_sbg,fk_cif,no_sbg_ar from viewkontrak left join viewtaksir on no_fatg=fk_fatg left join tblcustomer on no_cif=fk_cif left join (select no_sbg_ar from data_gadai.tbltaksir_umum)as tbltaksir on no_sbg_ar=no_sbg where nm_customer='".$nm_penerima."' and no_sbg_ar='".$fk_sbg_ar."'"))){
		$strmsg.="Nama customer harus sesuai dengan nama penerima.<br>";
		$j_action="document.form1.nm_customer.focus()";
	}
	
	if($strmsg)$strmsg="Error :<br>".$strmsg;
}

function save_data(){
	global $j_action,$strmsg,$id_edit,$upload_path,$strmsg,$no_cif,$no_fatg,$fk_sbg,$fk_sbg_ar,$no_cif_new,$no_fatg,$nm_customer;
	
	$l_success=1;
	pg_query("BEGIN");
	
	//log begin	
	if(!pg_query("insert into data_gadai.tbltaksir_umum_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from data_gadai.tbltaksir_umum where no_sbg_ar='".$fk_sbg_ar."'")) $l_success=0;
	//end log
	
	if(!pg_query("update data_gadai.tbltaksir_umum set no_sbg_lama='".$fk_sbg."' where no_sbg_ar='".$fk_sbg_ar."'"))$l_success=0;
	//showquery("update data_gadai.tbltaksir_umum set no_sbg_lama='".$fk_sbg."' where no_sbg_ar='".$fk_sbg_ar."'");
	
	//log begin	
	if(!pg_query("insert into data_gadai.tbltaksir_umum_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from data_gadai.tbltaksir_umum where no_sbg_ar='".$fk_sbg_ar."'")) $l_success=0;
	//end log
	//$l_success=0;
	if ($l_success==1){
		$strmsg=$nm_menu." Tersimpan.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		
		pg_query("COMMIT");
		if($id_menu=="20170900000052"){//untuk taksir umum lsg pindah form
			//$j_action="window.location='modal_add.php?id_menu=20170800000031&kd_menu_button=11111010&fk_fatg_link=".$id_edit."'";
		}	
		
	}else{
		$strmsg.="Error :<br>".$nm_menu." Gagal Tersimpan.<br>";
		pg_query("ROLLBACK");			
	}
}

?>