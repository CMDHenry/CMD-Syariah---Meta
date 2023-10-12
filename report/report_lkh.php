<?php
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/numeric.inc.php';


$fk_cabang=($_REQUEST["fk_cabang"]);
if(!$_REQUEST["tgl"])$tgl=convert_date_english(today);
else $tgl=convert_date_english($_REQUEST["tgl"]);

if($_SESSION['jenis_user']=='Cabang'){
	$fk_cabang=$_SESSION['kd_cabang'];	
	$nm_cabang=get_rec("tblcabang","nm_cabang","kd_cabang='".$fk_cabang."'");
}

if($_REQUEST["status"]=="Save") {
	cek_error();
	if(!$strmsg){
		//save_data();
	}
}

?>
<html>
<head>
	<title>.: <?=$_SESSION["application"]?> :.</title>
    <link href="../css/text.css.php" rel="stylesheet" type="text/css">
	<link rel="stylesheet" title="Style CSS" href="../js/cwcalendar.css.php" type="text/css" media="all" />
</head>
<script language='javascript' src="../js/alert.js.php"></script>
<script language='javascript' src="../js/ajax.js.php"></script>
<script language='javascript' src='../js/calendar.js.php'></script>
<script language='javascript' src="../js/openwindow.js.php"></script>
<script language='javascript' src='../js/object_function.js.php'></script>
<script language='javascript' src='../js/validate.js.php'></script>
<script language='javascript'>
function cekError(){
	var lAlerttxt="";
	var lFocuscursor="";

	if(lAlerttxt!=""){
		alert("Error : <br>"+lAlerttxt,function(){eval(lFocuscursor+'.focus()')});
		return false
	} else return true;
}


function fSave(){
	window.open('../print/print_lkh.php?tgl='+document.form1.tgl.value+'&fk_cabang='+document.form1.fk_cabang.value)
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")) {
		lInputClose=getObjInputClose();lInputClose.close()
	}
}
function fGetCabang(){
	fGetNC(false,"20170900000010","fk_cabang","Ganti CABANG",document.form1.fk_cabang,document.form1.fk_cabang)
}

function fGetCabangData(){
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataCabangState
	lSentText="table= tblcabang&field=(nm_cabang)&key=kd_cabang&value="+document.form1.fk_cabang.value
	lObjLoad.open("POST","../ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}

function fGetDataCabangState(){
	if (this.readyState == 4){
		//confirm(this.responseText)
		if (this.status==200 && this.responseText!="") {
			lTemp=this.responseText.split("¿");
			document.getElementById("divNmCabang").innerHTML=document.form1.nm_cabang.value=lTemp[0]
		} else {
			document.getElementById("divNmCabang").innerHTML=document.form1.nm_cabang.value="-"
		}
	}
}

function fLoad(){
	
<?

	echo "document.form1.tgl.focus();";
?>
}
</script>
<body onLoad="fLoad();document.form1.autocomplete='off'" bgcolor="#fafafa">
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="form1" enctype="multipart/form-data">
<input type="hidden" name="status">
<input type="hidden" name="strisi" value="<?=$strisi?>">
<input type="hidden" name="hidden_focus">
<input type="hidden" name="id_menu" value="<?=$id_menu?>">
<input type="hidden" name="total" value="<?=$total?>">
<input type="hidden" name="strmenu" value="<?=$strmenu?>">

<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
                <tr><td class="judul_menu" align="center">LKH</td></tr>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border" id="tdContent">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">     
            
            <tr bgcolor="efefef">
                <td width="20%" style="padding:0 5 0 5" class="">Tanggal</td>
                <td width="80%" style="padding:0 5 0 5" colspan="3">
                        <input type="text" name="tgl" value="<?=convert_date_indonesia($tgl)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_tgl.click();" size="10" >&nbsp;<img src="../images/btn_extend.gif" name="img_tgl" onClick="fPopCalendar(document.form1.tgl,document.form1.tgl)">                               
                </td>
            </tr>               

        <tr bgcolor="efefef">
              <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef">Kode Cabang</td>
              <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                    <input name="fk_cabang" type="text" onKeyPress="if(event.keyCode==4) img_fk_cabang.click();" onKeyUp="fNextFocus(event,document.form1.periode_awal)"  value="<?=$fk_cabang?>" onChange="fGetCabangData()" <?=($_SESSION['jenis_user']=='Cabang'?"disabled":"")?>>&nbsp;
					<? if($_SESSION['jenis_user']!='Cabang'){?><img src="../images/search.gif" id="img_fk_cabang" onClick="fGetCabang()" style="border:0px" align="absmiddle"><? } ?>
              </td>
              <td width="20%" style="padding:0 5 0 5">Nama Cabang</td>
              <td width="30%" style="padding:0 5 0 5">
                    <input type="hidden" name="nm_cabang" value="<?=convert_html($nm_cabang)?>" class="groove_text" style="width:90%" > <span id="divNmCabang"><?=convert_html($nm_cabang)?></span>
               </td>
        </tr>
		  	</table>
        </td>
    </tr>
    <tr height="20">
        <td height="25" align="center" bgcolor="#D0E4FF" class="border">
            <input class="groove_button" name="btnsimpan" type='button' value='Cetak' onClick="fSave()">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?



function cek_error(){
	global $strmsg,$j_action,$strisi,$tr_date,$jenis_pembayaran,$total,$fk_partner_dealer,$no_batch,$fk_cabang,$fk_bank,$fk_partner_notaris;
	
	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}


function save_data(){
	global $strmsg,$j_action,$tgl,$fk_cabang,$fk_partner_dealer;
	print_r($arr_nominal);
	$l_success=1;
	pg_query("BEGIN");
	
	//$l_success=0;
	if($l_success==1) {
		//$strmsg = "Data saved. Batch :".$no_batch."<br>";
		pg_query("COMMIT");
	} else {
		$strmsg .= "Error :<br>Data save failed.<br>";
	    pg_query("ROLLBACK");
	}	
}	



function get_data_module(){
	global $kd_module,$j_action,$nm_tabs;
	
	//query untuk memunculkan tab 
	$lrow_tab_switch=pg_fetch_array(pg_query("select * from skeleton.tblmodule_tabs where fk_module='".$kd_module."' order by no_urut_tabs limit 1"));
	$nm_tabs=$lrow_tab_switch["kd_tabs"];
	$lrow_first_field=pg_fetch_array(pg_query("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' order by no_urut_add limit 1"));
	$j_action="document.form1.".$lrow_first_field["kd_field"].".focus();";
}
?>
		

	

