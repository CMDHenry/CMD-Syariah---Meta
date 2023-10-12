<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/accounting_utility.inc.php';
require 'requires/numeric.inc.php';
require 'requires/timestamp.inc.php';


$j_action=$_REQUEST["hidden_focus"];
$id_edit=$_REQUEST["id_edit"];

$no_bukti=trim($_REQUEST["no_bukti"]);
$tgl_cair = convert_date_english(trim($_REQUEST["tgl_cair"]));
$nm_customer = trim($_REQUEST["nm_customer"]);
$tgl_pengajuan = convert_date_english(trim($_REQUEST["tgl_pengajuan"]));
$tgl_pengiriman_kontrak = convert_date_english(trim($_REQUEST["tgl_pengiriman_kontrak"]));
$catatan_pengiriman_kontrak=trim($_REQUEST["catatan_pengiriman_kontrak"]);
$status_stnk=trim($_REQUEST["status_stnk"]);
$masa_berlaku_pajak=convert_date_english(trim($_REQUEST["masa_berlaku_pajak"]));
$keterangan_tarik=trim($_REQUEST["keterangan_tarik"]);
$no_bast=trim($_REQUEST["no_bast"]);
$nm_kolektor=trim($_REQUEST["nm_kolektor"]);
$fk_kolektor=trim($_REQUEST["fk_kolektor"]);

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

function fGetKaryawan(){
	//fGetNC(false,"20170800000027","fk_kolektor","Ganti Kota",document.form1.fk_kolektor,document.form1.fk_kolektor,document.form1.nm_jabatan,"","20170800000084","","nm_jabatan")
	fGetNC(false,"20170800000027","fk_kolektor","Ganti Kota",document.form1.fk_kolektor,document.form1.fk_kolektor,"","","","","")
	if (document.form1.fk_kolektor.value !="")fGetKaryawanData()
}
	
function fGetKaryawanData(){
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataKaryawanState
	lSentText="table= (select nm_depan as nm_kolektor,* from tblkaryawan) as tblkaryawan&field=(nm_kolektor)&key=npk&value="+document.form1.fk_kolektor.value
	lObjLoad.open("POST","ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}
function fGetDataKaryawanState(){
	
	if (this.readyState == 4){
		if (this.status==200 && this.responseText!="") {
			lTemp=this.responseText.split("¿");
			document.getElementById("divnm_kolektor").innerHTML=document.form1.nm_kolektor.value=lTemp[0]
		} else {
			document.getElementById("divnm_kolektor").innerHTML=document.form1.nm_kolektor.value="-"
		}
	}
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
<input type="hidden" name="nm_jabatan" value="Kolektor">
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
					<td style="padding:0 5 0 5" width="20%">&nbsp;</td>
                    <td style="padding:0 5 0 5" width="20%">&nbsp;</td>
					
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
                <tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td  class="fontColor" style="padding:0 5 0 5">Status STNK</td>
				  	<td  class="fontColor" style="padding:0 5 0 5">
                    <select name="status_stnk" class="groove_text" onKeyUp="fNextFocus(event,document.form1.status_stnk)" onChange="fCalc(this)">
                        <option value="">--Status STNK--</option>
                        <option value="Ada" <?=(($status_stnk=="Ada")?"selected":"");?>>Ada</option>
                        <option value="Tidak" <?=(($status_stnk=="Tidak")?"selected":"");?>>Tidak</option>
                    </select></td>
					<td class="fontColor" style="padding:0 5 0 5" width="20%">Masa Berlaku Pajak</td>
					<td  class="fontColor" style="padding:0 5 0 5" width="30%"><input name="masa_berlaku_pajak" type="text"  class='groove_text' size="12" value="<?=convert_date_indonesia($masa_berlaku_pajak)?>" maxlength="10" onKeyUp="fNextFocus(event,document.form1.masa_berlaku_pajak)">
					&nbsp;<img src="images/btn_extend.gif" width="13" height="12" onClick="fPopCalendar(document.form1.masa_berlaku_pajak,function(){document.form1.masa_berlaku_pajak.focus()})"></td>
				</tr>
                <tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td width="20%" style="padding:0 5 0 5" class="fontColor">No BAST</td>
				  	<td width="30%" style="padding:0 5 0 5"><input type="text" name="no_bast" class='groove_text' style="width:90%" value="<?=convert_html($no_bast)?>" onKeyUp="fNextFocus(event,document.form1.no_bast)"></td>
					<td width="20%" style="padding:0 5 0 5" class="fontColor"></td>
				  	<td width="30%" style="padding:0 5 0 5"></td>
				</tr>
                
                <tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td width="20%" style="padding:0 5 0 5" class="fontColor">Kode Karyawan</td>
				  	<td width="30%" style="padding:0 5 0 5"><input name="fk_kolektor" type="text" onKeyPress="if(event.keyCode==4) img_fk_kolektor.click();" onKeyUp="fNextFocus()"  value="<?=$fk_kolektor?>" onChange="fGetKaryawanData()">&nbsp;<img src="images/search.gif" id="img_fk_kolektor" onClick="fGetKaryawan()" style="border:0px" align="absmiddle">
</td>
					<td width="20%" style="padding:0 5 0 5" class="fontColor">Nama Kolektor</td>
				  	<td width="30%" style="padding:0 5 0 5"><input name="nm_kolektor" type="hidden" class='groove_text ' size="20" id="nm_kolektor" value="<?=$nm_kolektor?>"><span id="divnm_kolektor"><?=$nm_kolektor?></span></td>
				</tr>
                
                <tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td valign="top" class="fontColor" style="padding:0 5 0 5" rowspan="2">Keterangan Tarik</td>
				  	<td style="padding:0 5 0 5" rowspan="2" colspan="7"><textarea name="keterangan_tarik" id="keterangan_tarik" rows="3" cols="15" style="width:90%" class="groove_text" onKeyUp="fNextFocus(event,document.form1.keterangan_tarik);"><?=convert_html($keterangan_tarik)?></textarea></td>
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
function cek_error(){
	global $strmsg,$j_action,$strisi,$fk_cif,$fk_fatg,$fk_fatg_old,$id_edit,$fk_cabang,$masa_berlaku_pajak,$status_stnk,$keterangan_tarik,$no_bast,$nm_kolektor;

	if($status_stnk==""){
		$strmsg.="Pilih Status STNK.<br>";
		if(!$j_action) $j_action="document.form1.status_stnk.focus()";
	}
	
	if($masa_berlaku_pajak==""){
			$strmsg.="Masa Berlaku Pajak Kosong.<br>";
			if(!$j_action) $j_action="document.form1.masa_berlaku_pajak.focus()";
	}
	
	if($keterangan_tarik==""){
			$strmsg.="Keterangan Tarik Kosong.<br>";
			if(!$j_action) $j_action="document.form1.keterangan_tarik.focus()";
	}
	
	if($no_bast==""){
		$strmsg.="No BAST Kosong.<br>";
		if(!$j_action) $j_action="document.form1.no_bast.focus()";
	}
	
	if($nm_kolektor==""){
		$strmsg.="Nama Kolektor Kosong.<br>";
		if(!$j_action) $j_action="document.form1.nm_kolektor.focus()";
	}
	
	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}

function save_data(){
	global $strmsg,$j_action,$id_edit,$strisi,$tgl_pengiriman_kontrak,$fk_cif,$tgl_pengajuan,$fk_kolektor,$masa_berlaku_pajak,$status_stnk,$keterangan_tarik,$no_bast,$nm_kolektor,$fk_kolektor;

	$l_success=1;
	pg_query("BEGIN");


	//log begin
	if(!pg_query("insert into data_gadai.tblproduk_cicilan_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from data_gadai.tblproduk_cicilan where no_sbg='".$id_edit."'")) $l_success=0;
	//end log

	if(!pg_query("update data_gadai.tblproduk_cicilan set status_stnk=".(($status_stnk=="")?"null":"'".convert_sql($status_stnk)."'").",masa_berlaku_pajak=".(($masa_berlaku_pajak=="")?"null":"'".convert_sql($masa_berlaku_pajak)."'").",keterangan_tarik=".(($keterangan_tarik=="")?"null":"'".convert_sql($keterangan_tarik)."'").",no_bast=".(($no_bast=="")?"null":"'".convert_sql($no_bast)."'").",fk_kolektor=".(($fk_kolektor=="")?"null":"'".convert_sql($fk_kolektor)."'")." where no_sbg='".$id_edit."'")) $l_success=0;
	
	//showquery("update data_gadai.tblproduk_cicilan set status_stnk=".(($status_stnk=="")?"null":"'".convert_sql($status_stnk)."'").",masa_berlaku_pajak=".(($masa_berlaku_pajak=="")?"null":"'".convert_sql($masa_berlaku_pajak)."'").",keterangan_tarik=".(($keterangan_tarik=="")?"null":"'".convert_sql($keterangan_tarik)."'").",no_bast=".(($no_bast=="")?"null":"'".convert_sql($no_bast)."'").",fk_kolektor=".(($fk_kolektor=="")?"null":"'".convert_sql($fk_kolektor)."'")." where no_sbg='".$id_edit."'");
	
	//log begin
	if(!pg_query("insert into data_gadai.tblproduk_cicilan_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from data_gadai.tblproduk_cicilan where no_sbg='".$id_edit."'")) $l_success=0;
	//end log

	//$l_success = 0;
	if($l_success==1) {
		$strmsg = "Data Saved.<br>";
		$j_action="lInputClose=getObjInputClose();lInputClose.close();";
		$no_bukti="";
		$strisi="";
		$tr_date="";
		$description="";
		pg_query("COMMIT");
	} else {
		$strmsg.= "Error :<br> Save Failed.<br>";
		pg_query("ROLLBACK");
	}
}


function get_data(){
	global $id_edit,$strmsg,$j_action,$strisi,$no_sbg,$tgl_cair,$overdue,$nm_customer,$tgl_pengiriman_kontrak,$fk_cif,$fk_kolektor,$no_mesin,$no_rangka,$tgl_pengajuan,$keterangan_tarik,$masa_berlaku_pajak,$status_stnk,$no_bast,$nm_kolektor;

	$lrow=pg_fetch_array(pg_query("
	select no_sbg,tgl_cair,nm_customer,tgl_wo,fk_cif,fk_fatg ,tgl_pengajuan,no_mesin,no_rangka,tgl_pengajuan,tgl_pengiriman_kontrak,catatan_pengiriman_kontrak,keterangan_tarik,masa_berlaku_pajak,status_stnk,no_bast,fk_kolektor from data_gadai.tblproduk_cicilan
	left join viewkendaraan on fk_fatg=viewkendaraan.no_fatg
	left join(
		select fk_cif,no_fatg as no_fatg1,nm_customer from data_gadai.tbltaksir_umum
		left join tblcustomer on no_cif=fk_cif
	)as tbltaksir_umum	on no_fatg=no_fatg1
	left join(
		select npk ,nm_depan as nm_kolektor from tblkaryawan
	)as tblkaryawan on npk=fk_kolektor
	where no_sbg='".$id_edit."'"));
	 
	$no_sbg=$lrow["no_sbg"];
	$tgl_cair=(($lrow["tgl_cair"]!="")?date('m/d/Y',strtotime($lrow["tgl_cair"])):"");
	$tgl_pengajuan=(($lrow["tgl_pengajuan"]!="")?date('m/d/Y',strtotime($lrow["tgl_pengajuan"])):"");
	$overdue=$lrow["overdue"];
	$nm_customer=$lrow["nm_customer"];
	$tgl_pengiriman_kontrak=(($lrow["tgl_pengiriman_kontrak"]!="")?date('m/d/Y',strtotime($lrow["tgl_pengiriman_kontrak"])):"");
	$fk_cif=$lrow["fk_cif"];
	$catatan_pengiriman_kontrak=$lrow["catatan_pengiriman_kontrak"];
	$keterangan_tarik=$lrow["keterangan_tarik"];
	$masa_berlaku_pajak=(($lrow["masa_berlaku_pajak"]!="")?date('m/d/Y',strtotime($lrow["masa_berlaku_pajak"])):"");
	$status_stnk=$lrow["status_stnk"];
	$no_bast=$lrow["no_bast"];
	$nm_kolektor=$lrow["nm_kolektor"];
	$fk_kolektor=$lrow["fk_kolektor"];
	
	$no_mesin=$lrow["no_mesin"];
	$no_rangka=$lrow["no_rangka"];
}

?>