<?php
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/numeric.inc.php';
require '../classes/select.class.php';

$fk_bank=($_REQUEST["fk_bank"]);
$fk_cabang=($_REQUEST["fk_cabang"]);
$fk_cabang2=($_REQUEST["fk_cabang2"]);
if(!$_REQUEST["periode_awal"])$periode_awal=convert_date_english(today);
else $periode_awal = convert_date_english($_REQUEST["periode_awal"]);
if(!$_REQUEST["periode_akhir"])$periode_akhir=convert_date_english(today);
else $periode_akhir =  convert_date_english($_REQUEST["periode_akhir"]);

$jenis_tgl=$_REQUEST["jenis_tgl"];

if($_REQUEST["status"]=="Save") {
	cek_error();
	if(!$strmsg){
		save_data();
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
	window.open('../print/print_penerimaan.php?periode_awal='+document.form1.periode_awal.value+'&periode_akhir='+document.form1.periode_akhir.value+'&fk_cabang='+document.form1.fk_cabang.value+'&fk_bank='+document.form1.fk_bank.value+'&jenis_tgl='+document.form1.jenis_tgl.value+'&fk_cabang2='+document.form1.fk_cabang2.value)
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


function fGetCabang2(){
	fGetNC(false,"20170900000010","fk_cabang","Ganti CABANG",document.form1.fk_cabang2,document.form1.fk_cabang2)
}

function fGetCabangData2(){
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataCabangState2
	lSentText="table= tblcabang&field=(nm_cabang)&key=kd_cabang&value="+document.form1.fk_cabang2.value
	lObjLoad.open("POST","../ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}

function fGetDataCabangState2(){
	if (this.readyState == 4){
		//confirm(this.responseText)
		if (this.status==200 && this.responseText!="") {
			lTemp=this.responseText.split("¿");
			document.getElementById("divNmCabang2").innerHTML=document.form1.nm_cabang2.value=lTemp[0]
		} else {
			document.getElementById("divNmCabang2").innerHTML=document.form1.nm_cabang2.value="-"
		}
	}
}

function fLoad(){
<?
	//echo "document.form1.tgl.focus();";
?>
}
</script>
<body onLoad="fLoad();document.form1.autocomplete='off'" bgcolor="#fafafa">
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="form1" enctype="multipart/form-data">
<input type="hidden" name="status">
<input type="hidden" name="id_menu" value="<?=$id_menu?>">

<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
                <tr><td class="judul_menu" align="center">PENERIMAAN</td></tr>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border" id="tdContent">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">     
            <tr bgcolor="efefef">
                <td width="20%" style="padding:0 5 0 5" class="">Periode</td>
                <td width="80%" style="padding:0 5 0 5" colspan="3">
                <input type="text" name="periode_awal" value="<?=convert_date_indonesia($periode_awal)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_awal.click();" size="10" >&nbsp;<img src="../images/btn_extend.gif" name="img_periode_awal" onClick="fPopCalendar(document.form1.periode_awal,document.form1.periode_awal)"> -                               
                <input type="text" name="periode_akhir" value="<?=convert_date_indonesia($periode_akhir)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_akhir.click();" size="10">&nbsp;<img src="../images/btn_extend.gif" name="img_periode_akhir" onClick="fPopCalendar(document.form1.periode_akhir,document.form1.periode_akhir)">                                
                </td>
            </tr>  
            <? if($_SESSION["jenis_user"]=='HO'){?>             
            <tr bgcolor="efefef">
              <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef">Kode Cabang</td>
              <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
              <input name="fk_cabang" type="text" onKeyPress="if(event.keyCode==4) img_fk_cabang.click();" onKeyUp="fNextFocus(event,document.form1.periode_awal)"  value="<?=$fk_cabang?>" onChange="fGetCabangData()">&nbsp;<img src="../images/search.gif" id="img_fk_cabang" onClick="fGetCabang()" style="border:0px" align="absmiddle">
              </td>
              <td width="20%" style="padding:0 5 0 5">Nama Cabang</td>
              <td width="30%" style="padding:0 5 0 5">
              <input type="hidden" name="nm_cabang" value="<?=convert_html($nm_cabang)?>" class="groove_text" style="width:90%" > <span id="divNmCabang"><?=convert_html($nm_cabang)?></span>
               </td>
            </tr>
            <input type="hidden" name="fk_cabang2" value="<?=$fk_cabang2?>" class="groove_text" style="width:90%" >
            <!--<tr bgcolor="efefef">
              <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef">Kode Cabang Kontrak</td>
              <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
              <input name="fk_cabang2" type="text" onKeyPress="if(event.keyCode==4) img_fk_cabang2.click();" onKeyUp="fNextFocus(event,document.form1.periode_awal)"  value="<?=$fk_cabang2?>" onChange="fGetCabangData()">&nbsp;<img src="../images/search.gif" id="img_fk_cabang2" onClick="fGetCabang()" style="border:0px" align="absmiddle">
              </td>
              <td width="20%" style="padding:0 5 0 5">Nama Cabang</td>
              <td width="30%" style="padding:0 5 0 5">
              <input type="hidden" name="nm_cabang2" value="<?=convert_html($nm_cabang2)?>" class="groove_text" style="width:90%" > <span id="divNmCabang"><?=convert_html($nm_cabang2)?></span>
               </td>
            </tr>-->
            <? }else{ 
			$fk_cabang2=$_SESSION["kd_cabang"];
			$nm_cabang2=$_SESSION["nm_cabang"];
            ?>
			<tr bgcolor="efefef">
				  <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef">Kode Cabang</td>
				  <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
						<input type="hidden" name="fk_cabang2" value="<?=$fk_cabang2?>" class="groove_text" style="width:90%" > <?=$fk_cabang2?>
				  </td>
				  <td width="20%" style="padding:0 5 0 5">Nama Cabang</td>
				  <td width="30%" style="padding:0 5 0 5">
						<input type="hidden" name="nm_cabang2" value="<?=convert_html($nm_cabang2)?>" class="groove_text" style="width:90%" > <span id="divNmCabang2"><?=convert_html($nm_cabang2)?></span>
				   </td>
			</tr>
            <input type="hidden" name="fk_cabang" value="<?=$fk_cabang?>" class="groove_text" style="width:90%" > 
            
            <? }?>
        	<tr bgcolor="efefef">
              <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef">Kode Bank</td>
              <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
 			  <? create_list_bank();?>
              </td>
              <td width="20%" style="padding:0 5 0 5"></td>
              <td width="30%" style="padding:0 5 0 5"></td>
            </tr>
           <tr bgcolor="efefef">
                <td style="padding:0 5 0 5" width="20%">Jenis TGL</td>
                <td style="padding:0 5 0 5" width="30%">
                <select name="jenis_tgl">
                   <option value="tgl_input"<?= (($jenis_tgl=='tgl_input')?"selected":"") ?>>Tgl Input</option>
                   <option value="tgl_bayar"<?= (($tgl_bayar=='tgl_bayar')?"selected":"") ?>>Tgl Bayar</option>
                </select>
                </td>
                <td style="padding:0 5 0 5" width="20%"></td>
                <td style="padding:0 5 0 5" width="30%"></td>
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
	global $strmsg,$j_action,$tgl,$fk_cabang,$fk_bank;
	print_r($arr_nominal);
	$l_success=1;
	pg_query("BEGIN");
	
	//$l_success=0;
	if($l_success==1) {
		//$strmsg = "Data saved. Batch :".$no_batch."<br>";
		header("location:report_penerimaan_pdf.php?fk_cabang=".$fk_cabang."&tgl=".$tgl."&fk_bank=".$fk_bank);
		pg_query("COMMIT");
	} else {
		$strmsg .= "Error :<br>Data save failed.<br>";
	    pg_query("ROLLBACK");
	}	
}	


function create_list_bank(){
    global $fk_bank,$fk_cabang_bank;
	//showquery("select * from tblcabang_detail_bank left join tblbank on fk_bank=kd_bank left join (select description, coa from tbltemplate_coa)as tblcoa on fk_coa=coa where fk_cabang='".$fk_cabang_bank."'  and fk_bank<=90");
    $l_list_obj = new select("select distinct on(fk_bank)* from tblcabang_detail_bank left join tblbank on fk_bank=kd_bank left join (select description, coa from tbltemplate_coa)as tblcoa on fk_coa=coa ","nm_bank","fk_bank","fk_bank");
    $l_list_obj->add_item("-- Pilih ---",'',0);
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:12px;' onKeyUp='fNextFocus(event,document.getElementById(\"login\"))'");
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
		

	

