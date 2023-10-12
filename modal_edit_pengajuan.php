<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/accounting_utility.inc.php';
require 'requires/numeric.inc.php';
require 'requires/timestamp.inc.php';
require 'classes/select.class.php';

$j_action=$_REQUEST["hidden_focus"];
$id_edit=$_REQUEST["id_edit"];

$no_bukti=trim($_REQUEST["no_bukti"]);
$no_bast=trim($_REQUEST["no_bast"]);
$tgl_cair = convert_date_english(trim($_REQUEST["tgl_cair"]));
$nm_customer = trim($_REQUEST["nm_customer"]);
$tgl_pengajuan = convert_date_english(trim($_REQUEST["tgl_pengajuan"]));
$tgl_pengiriman_kontrak = convert_date_english(trim($_REQUEST["tgl_pengiriman_kontrak"]));
$catatan_pengiriman_kontrak=trim($_REQUEST["catatan_pengiriman_kontrak"]);
$skema_pembiayaan=trim($_REQUEST["skema_pembiayaan"]);
$jenis_pembiayaan=trim($_REQUEST["jenis_pembiayaan"]);
$fk_cif=trim($_REQUEST["fk_cif"]);
$no_mesin=trim($_REQUEST["no_mesin"]);
$no_rangka=trim($_REQUEST["no_rangka"]);

if($_REQUEST["status"]=="Save") {
	cek_error();
	if(!$strmsg){
		save_data();
	}
}
if($_REQUEST["pstatus"]=="edit"){
	get_data();
}
?>
<html>
<head>
	<title>.: SUKA FAJAR :.</title>
	<link href="css/cwcalendar.css.php" rel="stylesheet" type="text/css">
    <link href="css/text.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src="js/alert.js.php"></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src="js/calendar.js.php"></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src='js/input_format_number.js.php'></script>
<!--<script language='javascript' src="js/table_v2.js.php"></script>-->
<script language='javascript' src="js/table_adjustment_memorial.js.php"></script>
<script language='javascript' src="js/validate.js.php"></script>
<script language='javascript'>

function fGetReferensiTaksir(){
	fGetNC(false,'20170900000052','fk_fatg','Ganti Kota',document.form1.fk_fatg,document.form1.fk_fatg,'','','20171200000083')
	if (document.form1.fk_fatg.value !="")fGetCIFData()
}


function fGetCIF(){
	fGetNC(false,'20170800000001','fk_cif','Ganti Lokasi',document.form1.fk_cif,document.form1.fk_cif)
}

function fGetCIFData(){
	
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataCIFState
	lSentText="table=(select * from data_gadai.tbltaksir_umum left join tblcustomer on fk_cif=no_cif)as tbl&field=(nm_customer||'¿'||fk_cif)&key=no_fatg&value="+document.form1.fk_fatg.value
	lObjLoad.open("POST","ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}
function fGetDataCIFState(){	
	if (this.readyState == 4){
		//confirm(this.responseText)
		if (this.status==200 && this.responseText!="") {
			lTemp=this.responseText.split('¿');
			//confirm(lTemp[2])
			document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value=lTemp[0]
			document.getElementById('divfk_cif').innerHTML=document.form1.fk_cif.value=lTemp[1]
		} else {
			document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value=""
			document.getElementById('divfk_cif').innerHTML=document.form1.fk_cif.value=""
		}
	}
}

function cekError(){

}

function fSave(){
	///document.form1.strisi.value=table1.getIsi();
	
	//if (cekError()) {
		document.form1.status.value='Save';
		//if (document.form1.strisi.value!='false') document.form1.submit();
		document.form1.submit();
	//}
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")) {
		lInputClose=getObjInputClose();lInputClose.close()
	}
}


function fLoad(){
<?
	if ($strmsg){
		echo "alert('".$strmsg."',function(){".$j_action."});";
	}elseif($j_action){
		echo $j_action;
	}else{
		echo "document.form1.id_edit.focus();";
	}
?>
}
</script>
<body onLoad="fLoad();document.form1.autocomplete='off'" bgcolor="#fafafa">
<form name="form1" action="<?=$_SERVER['PHP_SELF']?>" method="post">
<input type="hidden" name="status">
<input type="hidden" name="tgl_cair" value="<?=$tgl_cair?>">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="hidden_focus">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<td class="judul_menu" align="center">EDIT KONTRAK</td>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border" id="tdContent">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%">No. Kontrak</td>
					<td  style="padding:0 5 0 5" width="30%"><input type="hidden" value="<?=$id_edit?>" name="id_edit"><?=$id_edit?></td>
					<td style="padding:0 5 0 5" width="20%"></td>
					<td  style="padding:0 5 0 5" width="30%"></td>
				</tr>
                
                <tr bgcolor="efefef">
                    <td style="padding:0 5 0 5" width="20%">No CIF</td>
                    <td style="padding:0 5 0 5" width="30%">
                    <input name="fk_cif" type="hidden" class='groove_text' value="<?=$fk_cif?>"><?=$fk_cif?>
                    </td>
                    <td style="padding:0 5 0 5" width="20%">Nama Customer</td>
                    <td style="padding:0 5 0 5" width="30%"><input type="hidden" value="<?=$nm_customer?>" name="nm_customer"><?=$nm_customer?></td>
                </tr>
                <tr bgcolor="efefef">
                    <td style="padding:0 5 0 5" width="20%">No Mesin</td>
                    <td style="padding:0 5 0 5" width="30%">
                    <input name="no_mesin" type="hidden" class='groove_text' value="<?=$no_mesin?>"><?=$no_mesin?>
                    </td>
                    <td style="padding:0 5 0 5" width="20%">No Rangka</td>
                    <td style="padding:0 5 0 5" width="30%"><input type="hidden" value="<?=$no_rangka?>" name="no_rangka"><?=$no_rangka?></td>
                </tr>
                <?
                if($tgl_cair){
				?>                
<!--				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%">Catatan Pengiriman Kontrak</td>
					<td  style="padding:0 5 0 5" width="30%"><input type="text" value="<?=$catatan_pengiriman_kontrak?>" name="catatan_pengiriman_kontrak"></td>
					<td style="padding:0 5 0 5" width="20%">Tanggal Pengiriman Kontrak</td>
					<td  style="padding:0 5 0 5" width="30%"><input name="tgl_pengiriman_kontrak" type="text"  class='groove_text' size="12" value="<?=convert_date_indonesia($tgl_pengiriman_kontrak)?>" maxlength="10" onKeyUp="fNextFocus(event,document.form1.tgl_pengiriman_kontrak)">
					&nbsp;<img src="images/btn_extend.gif" width="13" height="12" onClick="fPopCalendar(document.form1.tgl_pengiriman_kontrak,function(){document.form1.tgl_pengiriman_kontrak.focus()})"></td>
				</tr>
-->             <? }else{ ?>
				<? }?>
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%" class="fontColor">Tanggal Pengajuan</td>
					<td style="padding:0 5 0 5" width="30%"><input name="tgl_pengajuan" type="text"  class='groove_text' id="tgl_pengajuan" size="12" value="<?=convert_date_indonesia($tgl_pengajuan)?>" maxlength="10" onKeyUp="fNextFocus(event,document.form1.tgl_pengajuan)">
					&nbsp;<img src="images/btn_extend.gif" width="13" height="12" onClick="fPopCalendar(document.form1.tgl_pengajuan,function(){document.form1.tgl_pengajuan.focus()})"></td>
					<td style="padding:0 5 0 5" width="20%"></td>
					<td style="padding:0 5 0 5" width="30%"></td>
				</tr>
                
                <tr bgcolor="efefef">
                    <td style="padding:0 5 0 5" width="20%" class="fontColor">Skema Pembiayaan</td>
                    <td style="padding:0 5 0 5" width="30%"><?=create_list_skema_pembiayaan()?></td>
                
                    <td style="padding:0 5 0 5" class="fontColor" width="20%">No Bast</td>
                    <td style="padding:0 5 0 5" width="30%">
                    <input name="no_bast" type="text" class='groove_text' value="<?=$no_bast?>">
                    </td>
                </tr>
                <tr bgcolor="efefef">
                    <td style="padding:0 5 0 5" width="20%" class="fontColor">Jenis Pembiayaan</td>
                    <td style="padding:0 5 0 5" width="30%"><select class='groove_text' name="jenis_pembiayaan"  value="<?=$jenis_pembiayaan?>" >
                    <option value="Jual Beli">Jual Beli</option>
                    <option value="Investasi">Investasi</option>
                    <option value="Jasa">Jasa</option>
                    </select></td>
                
                    <td style="padding:0 5 0 5" class="fontColor" width="20%"></td>
                    <td style="padding:0 5 0 5" width="30%">
                    </td>
                </tr>
                
		  	</table>
			
        </td>
    </tr>
    <tr height="20">
        <td height="25" align="center" bgcolor="#D0E4FF" class="border">
            <input class="groove_button" name="btnsimpan" type='button' value='Simpan' onClick="fSave()">
            &nbsp;<input class="groove_button" type="button" value="Batal" onClick="fBatal()">
       </td>
	</tr>
</table>
</form>
</body>
</html>
<?
function create_list_skema_pembiayaan(){
    global $skema_pembiayaan;
	$column='Murabahah,Mudharabah,Istishna,Salam,Musyarakah,Mudharabah musytarakah,Musyarakah mutanaqishoh,Ijarah,Ijarah muntahiya bi tamlik,Hawalah atau Hawalah bil ujrah,Wakalah atau Wakalah bil ujrah,Kafalah atau Kafalah bil ujrah,Qardh
	';
  	$arr=explode(',',$column);
	$i=0;
    foreach($arr as $name){
		$i++;
		$data.=" select '".$name."' as skema_pembiayaan,".$i."::numeric as index union";
	}
	$data=rtrim($data,"union");
	//echo "select * from (".$data.")as a order by index";
	
    $l_list_obj = new select("select * from (".$data.")as s order by index ","skema_pembiayaan","skema_pembiayaan","skema_pembiayaan");
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:12px;' onKeyUp=''","form1","");
}


function cek_error(){
	global $strmsg,$j_action,$skema_pembiayaan,$fk_cif,$fk_fatg,$fk_fatg_old,$id_edit,$tgl_pengajuan,$fk_cabang,$no_bast,$tgl_pengajuan;
	
	if($tgl_pengajuan == ""){
		$strmsg.="Tgl Kosong.<br>";
	}
	if($no_bast == ""){
		//$strmsg.="No Bast Kosong.<br>";
	}
	if($skema_pembiayaan == ""){
		$strmsg.="Skema Pembiayaan Kosong.<br>";
	}
	
	$tgl_input_customer=get_rec('tblcustomer','tgl_input_customer',"no_cif='".$fk_cif."'");
	if(strtotime($tgl_pengajuan) < strtotime($tgl_input_customer)){
		$strmsg.="Tanggal Pengajuan Lebih Kecil dari Tanggal Input Debitur.<br>";
	}	
	
	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}

function save_data(){
	global $strmsg,$j_action,$id_edit,$skema_pembiayaan,$tgl_pengiriman_kontrak,$fk_cif,$tgl_pengajuan,$catatan_pengiriman_kontrak,$no_bast,$jenis_pembiayaan;
	$l_success=1;
	pg_query("BEGIN");

	if(!pg_query("insert into data_gadai.tblproduk_cicilan_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from data_gadai.tblproduk_cicilan where no_sbg='".$id_edit."'")) $l_success=0;
	//,tgl_pengiriman_kontrak=".(($tgl_pengiriman_kontrak=="")?"null":"'".convert_sql($tgl_pengiriman_kontrak)."'").",catatan_pengiriman_kontrak=".(($catatan_pengiriman_kontrak=="")?"null":"'".convert_sql($catatan_pengiriman_kontrak)."'").",

	if(!pg_query("update data_gadai.tblproduk_cicilan set tgl_pengajuan=".(($tgl_pengajuan=="")?"null":"'".convert_sql($tgl_pengajuan)."'").",no_bast='".convert_sql($no_bast)."',skema_pembiayaan='".convert_sql($skema_pembiayaan)."',jenis_pembiayaan='".convert_sql($jenis_pembiayaan)."' where no_sbg='".$id_edit."'")) $l_success=0;
	//showquery("update data_gadai.tblproduk_cicilan set tgl_pengajuan=".(($tgl_pengajuan=="")?"null":"'".convert_sql($tgl_pengajuan)."'").",no_bast='".convert_sql($no_bast)."',skema_pembiayaan='".convert_sql($skema_pembiayaan)."',jenis_pembiayaan='".convert_sql($jenis_pembiayaan)."' where no_sbg='".$id_edit."'");
	
	if(!pg_query("insert into data_gadai.tblproduk_cicilan_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from data_gadai.tblproduk_cicilan where no_sbg='".$id_edit."'")) $l_success=0;

	//$l_success = 0;
	if($l_success==1) {
		$strmsg = "Data Saved.<br>";
		$j_action="lInputClose=getObjInputClose();lInputClose.close();";
		$no_bukti="";
		$strisi="";
		$no_bast="";
		$tr_date="";
		$description="";
		pg_query("COMMIT");
	} else {
		$strmsg.= "Error :<br> Save Failed.<br>";
		pg_query("ROLLBACK");
	}
}


function get_data(){
	global $id_edit,$strmsg,$j_action,$skema_pembiayaan,$no_sbg,$tgl_cair,$overdue,$nm_customer,$tgl_pengiriman_kontrak,$fk_cif,$catatan_pengiriman_kontrak,$no_mesin,$no_rangka,$tgl_pengajuan,$no_bast,$jenis_pembiayaan;

	$lrow=pg_fetch_array(pg_query("
	select no_sbg,tgl_cair,nm_customer,tgl_wo,fk_cif,fk_fatg ,tgl_pengajuan,no_mesin,no_rangka,tgl_pengajuan,tgl_pengiriman_kontrak,catatan_pengiriman_kontrak from data_gadai.tblproduk_cicilan
	left join viewkendaraan on fk_fatg=viewkendaraan.no_fatg
	left join(
		select fk_cif,no_fatg as no_fatg1,nm_customer from data_gadai.tbltaksir_umum
		left join tblcustomer on no_cif=fk_cif
	)as tbltaksir_umum	on no_fatg=no_fatg1
	 where no_sbg='".$id_edit."'"));
	$no_sbg=$lrow["no_sbg"];
	$tgl_cair=(($lrow["tgl_cair"]!="")?date('m/d/Y',strtotime($lrow["tgl_cair"])):"");
	$tgl_pengajuan=(($lrow["tgl_pengajuan"]!="")?date('m/d/Y',strtotime($lrow["tgl_pengajuan"])):"");
	$overdue=$lrow["overdue"];
	$nm_customer=$lrow["nm_customer"];
	$tgl_pengiriman_kontrak=(($lrow["tgl_pengiriman_kontrak"]!="")?date('m/d/Y',strtotime($lrow["tgl_pengiriman_kontrak"])):"");
	$fk_cif=$lrow["fk_cif"];
	$catatan_pengiriman_kontrak=$lrow["catatan_pengiriman_kontrak"];
	$no_bast=$lrow["no_bast"];
	//$jenis_pembiayaan=$lrow["jenis_pembiayaan"];
	
	$no_mesin=$lrow["no_mesin"];
	$no_rangka=$lrow["no_rangka"];
}

?>