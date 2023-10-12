<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/numeric.inc.php';
require 'requires/db_utility.inc.php';
require 'classes/select.class.php';

$id_edit=$_REQUEST["id_edit"];
//check_right("DL10141010");

$kd_generate_blanko=trim($_REQUEST["kd_generate_blanko"]);
$fk_jenis_blanko=trim($_REQUEST["fk_jenis_blanko"]);
$nm_jenis_blanko=trim($_REQUEST["nm_jenis_blanko"]);
$fk_cabang=trim($_REQUEST["fk_cabang"]);
$nm_cabang=trim($_REQUEST["nm_cabang"]);
$nm_area=trim($_REQUEST["nm_area"]);
$nm_merek=trim($_REQUEST["nm_merek"]);
$fk_merek=trim($_REQUEST["fk_merek"]);
$dari_nomor=trim($_REQUEST["dari_nomor"]);
$sampai_nomor=trim($_REQUEST["sampai_nomor"]);
$all_cabang = stripslashes(trim($_REQUEST["all_cabang"]));
if($all_cabang=="")get_data();
$kd_blanko=trim($_REQUEST["kd_blanko"]);

//echo $kd_blanko."aa";

if($_REQUEST["status"]=="Save") {
	cek_error();
	if(!$strmsg){
		if ($id_edit) edit_data();
		else add_data();
	}
}
if($_REQUEST["pstatus"]){
	get_data();
}
?>
<html>
<head>
	<title>.: ASCO MSIS :.</title>
    <link href="css/text.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src='js/alert.js.php'></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src="js/validate.js.php"></script>
<script language='javascript'>
//function fGetJenisBlanko(){
//	fGetNC(false,'jenis_blanko','kd_jenis_blanko','Ganti Jenis Blanko',document.form1.fk_jenis_blanko,document.form1.dari_nomor)
//	if(document.form1.fk_jenis_blanko!="")fGetJenisBlankoData()
//}

//function fGetJenisBlankoData(){
//	document.getElementById("divNmJenisBlanko").innerHTML=""
//	lObjLoad = getHTTPObject()
//	lObjLoad.onreadystatechange=fGetDataJenisBlankoState
//	lSentText='table=tbljenis_blanko&field=nm_jenis_blanko&key=kd_jenis_blanko&value='+document.form1.fk_jenis_blanko.value
//	lObjLoad.open("POST","ajax/get_data.php",true);
//	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
//	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
//	lObjLoad.setRequestHeader("Connection","close")
//	lObjLoad.send(lSentText);
//}

//function fGetDataJenisBlankoState(){
//	if (this.readyState == 4){
//		if (this.status==200 && this.responseText!="") {
//			document.getElementById("divNmJenisBlanko").innerHTML=this.responseText
//			document.form1.nm_jenis_blanko.value=this.responseText
//		} else {
//			document.getElementById("divNmJenisBlanko").innerHTML="-"
//			document.form1.nm_jenis_blanko.value="-"
//		}
//	}
//}

function cekError(){
	var lAlerttxt="";
	var lFocuscursor="";
	
	//if (document.form1.fk_jenis_blanko.value==""){
//		lAlerttxt+='Kode Jenis Blanko Kosong<br>';
//		if(lFocuscursor==""){lFocuscursor="document.form1.fk_jenis_blanko";}
//	}
	if (document.form1.fk_cabang.value==""){
		lAlerttxt+='Kode Cabang Kosong<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.fk_cabang";}
	}
	if (document.form1.dari_nomor.value==""){
		lAlerttxt+='Dari Nomor Kosong<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.dari_nomor";}
	}
	else if(check_type('angka',document.form1.dari_nomor)){
		lAlerttxt+='Dari Nomor Salah<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.dari_nomor";}
	}
	if (document.form1.sampai_nomor.value==""){
		lAlerttxt+='Sampai Nomor Kosong<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.sampai_nomor";}
	}
	else if(check_type('angka',document.form1.sampai_nomor)){
		lAlerttxt+='Sampai Nomor Salah<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.sampai_nomor";}
	}
	else if(parseInt(document.form1.sampai_nomor.value) < parseInt(document.form1.dari_nomor.value)){
		lAlerttxt+='Sampai Nomor Harus Lebih Besar<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.sampai_nomor";}
	}
	if(lAlerttxt!=""){
		alert("Error : <br>"+lAlerttxt,function(){eval(lFocuscursor+'.focus()')});
		return false
	} else return true;
}
function fSave(){
	//alert('test')
	if (cekError()) {
		document.form1.status.value='Save';
		document.form1.submit();
	}
}
function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")) {
		lInputClose=getObjInputClose();lInputClose.close()
	}
}
function fLoad(){
	//parent.parent.document.title="Generate Blanko";
<?
	if ($strmsg){
		echo "alert('".$strmsg."');";
	}else if($j_action){
		echo $j_action;
	}else{
		//echo "document.form1.fk_jenis_blanko.focus();";
		if ($fk_cabang=="")$fk_cabang=$_SESSION["kd_cabang"];
			$lrow=pg_fetch_array(pg_query("select * from tblcabang where kd_cabang='".$_SESSION["kd_cabang"]."'"));
			$nm_cabang=$lrow["nm_cabang"];
			//$nm_area=$lrow["nm_area"];
			//$nm_merek=$lrow["nm_merek"];
			//$fk_merek=$lrow["fk_merek"];
	}
?>
}
</script>
<body onLoad="fLoad();document.form1.autocomplete='off'" bgcolor="#fafafa">
<form action="modal_generate_blanko.php" method="post" name="form1">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="status">
<input type="hidden" name="all_cabang" value="<?=$all_cabang?>">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<td align="center" class="judul_menu">GENERATE BLANKO</td>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
			  	<!--<tr bgcolor="efefef">
					<td width="20%" style="padding:0 5 0 5" class="fontColor">Kode Jenis Blanko</td>
					<td width="30%" style="padding:0 5 0 5"><input name="fk_jenis_blanko" type="text" class='groove_text' size="30"  value="<?=convert_html($fk_jenis_blanko)?>" onKeyUp="fNextFocus(event,document.form1.dari_nomor)" onChange="fGetJenisBlankoData()">&nbsp;<img src="images/search.gif" onClick="fGetJenisBlanko()" style="border:0px" align="absmiddle"></td>
					<td style="padding:0 5 0 5">Nama Blanko </td>
					<td width="30%" style="padding:0 5 0 5"><input type="hidden" name="nm_jenis_blanko" value="<?=convert_html($nm_jenis_blanko)?>"><span id="divNmJenisBlanko"><?=convert_html($nm_jenis_blanko)?></span></td>
			 	</tr>-->
                <tr bgcolor="efefef">
                        <td width="20%" style="padding:0 5 0 5" class="fontColor">Kode Blanko</td>
                        
                        <td style="padding:0 5 0 5"><input name="kd_blanko" type="text" class="groove_text_numeric" size="30" onKeyUp="fNextFocus(event,document.form1.fk_cabang)" value="<?=convert_html($kd_blanko)?>" maxlength="6"></td>
                        
                        <td width="20%" style="padding:0 5 0 5" class="fontColor"></td>
                        <td width="20%" style="padding:0 5 0 5" class="fontColor"></td>
                       
                 </tr> 
			  	<tr bgcolor="efefef">
			  		<td width="20%" style="padding:0 5 0 5">Kode Cabang</td>
				  	<td width="30%" style="padding:0 5 0 5"><input name="fk_cabang" type="hidden" class='groove_text' size="30"  value="<?=convert_html($fk_cabang)?>"><?=convert_html($fk_cabang)?></td>
					<td style="padding:0 5 0 5">Nama Cabang</td>
					<td width="30%" style="padding:0 5 0 5"><input type="hidden" name="nm_cabang" value="<?=convert_html($nm_cabang)?>"><span id="divNmcabang"><?=convert_html($nm_cabang)?></span></td>
			  	<!--<tr bgcolor="efefef">
					<td style="padding:0 5 0 5">Area</td>
				 	<td width="30%" style="padding:0 5 0 5"><input type="hidden" name="nm_area" value="<?=convert_html($nm_area)?>"><span id="divNmarea"><?=convert_html($nm_area)?></span></td>
				 	<td style="padding:0 5 0 5">Merek</td>
				  	<td width="30%" style="padding:0 5 0 5"><input type="hidden" name="fk_merek" value="<?=convert_html($fk_merek)?>"><input type="hidden" name="nm_merek" value="<?=convert_html($nm_merek)?>"><span id="divNmmerek"><?=convert_html($nm_merek)?></span></td>
			  	</tr>-->
			  	<tr bgcolor="efefef">
					<td style="padding:0 5 0 5" class="fontColor">Dari Nomor</td>
					<td style="padding:0 5 0 5"><input name="dari_nomor" type="text" class="groove_text_numeric" size="30" onKeyUp="fNextFocus(event,document.form1.sampai_nomor)" value="<?=convert_html($dari_nomor)?>" maxlength="6"></td>
					<td style="padding:0 5 0 5" class="fontColor">Sampai Nomor</td>
					<td style="padding:0 5 0 5"><input type="text" name="sampai_nomor" class='groove_text_numeric' size="30" onKeyUp="fNextFocus(event,document.form1.btnsimpan)" value="<?=convert_html($sampai_nomor)?>" maxlength="6"></td>
			  	</tr>
			</table>
<!-- end content begin -->
		</td>
    </tr>
	<tr height="20"><td height="25" align="center" bgcolor="#D0E4FF" class="border"><input name="btnsimpan" type="button" class="groove_button" onClick="fSave()" value="Simpan">&nbsp;<input type="button" class="groove_button" value="Batal" onClick="fBatal()"></td>
	</tr>
</table>
</form>
</body>
</html>
<?
function cek_error(){
	global $id_edit,$strmsg,$j_action,$fk_jenis_blanko,$fk_cabang,$fk_merek,$dari_nomor,$sampai_nomor,$nm_jenis_blanko,$all_cabang,$init_cabang,$resource_blanko,$arr_blanko,$kd_blanko;

	if($fk_cabang==""){
		$strmsg.="Kode Cabang Kosong.<br>";
		if(!$j_action) $j_action="document.form1.fk_cabang.focus()";
	} elseif (!pg_num_rows(pg_query("select * from tblcabang where kd_cabang='".$fk_cabang."' for update"))){
		$strmsg.="Kode Cabang belum terdaftar.<br>";
		if(!$j_action) $j_action="document.form1.fk_cabang.focus()";
	}else {
		$lrow = pg_fetch_array(pg_query("select initial_cabang from tblcabang where kd_cabang = '".$fk_cabang."' for update"));
		$init_cabang = $lrow["initial_cabang"];
	}

	if($dari_nomor==""){
		$strmsg.="Dari Nomor Kosong.<br>";
		if(!$j_action) $j_action="document.form1.dari_nomor.focus()";
	}
	
	if($kd_blanko==""){
		$strmsg.="Kode Blanko Kosong.<br>";
		if(!$j_action) $j_action="document.form1.kd_blanko.focus()";
	}
	
	
	if(strstr($kd_blanko," ")){
		$strmsg.="Kode Blanko Tidak Boleh Menggunakan Spasi.<br>";
		if(!$j_action) $j_action="document.form1.kd_blanko.focus()";
	}

	if($sampai_nomor==""){
		$strmsg.="Sampai Nomor Kosong.<br>";
		if(!$j_action) $j_action="document.form1.sampai_nomor.focus()";
	}
	elseif($sampai_nomor < $dari_nomor){
		$strmsg.="Sampai Nomor Salah.<br>";
		if(!$j_action) $j_action="document.form1.sampai_nomor.focus()";
	}else{
		//$resource_blanko = pg_query("select * from data_gadai.tblblanko where  kd_blanko >= '".$_SESSION['kd_cabang'].'-'.str_pad($dari_nomor,6,"0",STR_PAD_LEFT)."' and kd_blanko <= '".$_SESSION['kd_cabang'].'-'.str_pad( ($sampai_nomor+1) ,6,"0",STR_PAD_LEFT)."' and fk_cabang in (".$all_cabang.") for update");
		
		$resource_blanko = pg_query("select * from data_gadai.tblblanko where  kd_blanko >= '".$kd_blanko.'-'.str_pad($dari_nomor,6,"0",STR_PAD_LEFT)."' and kd_blanko <= '".$kd_blanko.'-'.str_pad( ($sampai_nomor) ,6,"0",STR_PAD_LEFT)."'  for update");
		
		//+1 buat sampai nomor
		//and fk_cabang in (".$all_cabang.")

		//showquery("select * from data_gadai.tblblanko where  kd_blanko >= '".$kd_blanko.'-'.str_pad($dari_nomor,6,"0",STR_PAD_LEFT)."' and kd_blanko <= '".$kd_blanko.'-'.str_pad( ($sampai_nomor) ,6,"0",STR_PAD_LEFT)."'  for update");
		
		while($row_blanko = pg_fetch_array($resource_blanko)){
			$arr_blanko[substr($row_blanko['kd_blanko'],3,6)] = substr($row_blanko['kd_blanko'],3,6);
		}

		if(pg_num_rows($resource_blanko) > 0){
			$strmsg.="Nomor Blanko dari ".$dari_nomor." sampai ".$sampai_nomor." sudah Terdaftar.<br>";
			if(!$j_action) $j_action="document.form1.sampai_nomor.focus()";
		}
	}

	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}

function add_data(){
	global $j_action,$strmsg,$kd,$strmenu,$fk_jenis_blanko,$fk_cabang,$dari_nomor,$sampai_nomor,$nm_cabang,$nm_area,$nm_merek,$nm_jenis_blanko,$fk_merek,$init_cabang,$all_cabang,$arr_blanko,$kd_blanko;
	
	$l_success=1;
	pg_query("BEGIN");
		
	//generate blanko by postgres
	//if(!pg_query("select data_showroom.generate_blanko('".$fk_jenis_blanko."','".$dari_nomor."','".$sampai_nomor."','".$fk_cabang."')")) $l_success=0;
	//end========================

	//generate blanko by php
	
	//echo $kd_blanko."aa";
	$j=0;
	if(count($arr_blanko) == 0){
		//showquery("select data_showroom.generate_blanko('".$fk_jenis_blanko."','".$dari_nomor."','".$sampai_nomor."','".$fk_cabang."','".$_SESSION['id']."','".$_SESSION['username']."','".date('Y-m-d H:i:s')."')");
		if(!pg_query("select data_gadai.generate_blanko('".$fk_jenis_blanko."','".$dari_nomor."','".$sampai_nomor."','".$fk_cabang."','".$_SESSION['id']."','".$_SESSION['username']."','".date('Y-m-d H:i:s')."','".$kd_blanko."')")) $l_success=0;
		$j = $sampai_nomor - $dari_nomor + 1;
		
	}
	//end========================

	//log begin	
	if(!pg_query("insert into data_gadai.tblgenerate_blanko_log (dari_nomor,sampai_nomor,log_action_userid,log_action_username,log_action_date,log_action_mode) values ('".convert_sql($dari_nomor)."','".convert_sql($sampai_nomor)."','".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA')")) $l_success=0;
	//end log]
	
	//$l_success=0;
	if ($l_success==1){
		$dari_nomor="";
		$sampai_nomor="";
		$strmsg=$j." Blanko Berhasil di-Generate.<br>";
		pg_query("COMMIT");
	}else{
		$strmsg="Error :<br>".$j." Blanko Gagal di-Generate.<br>";
		pg_query("ROLLBACK");
	}
}

function get_data(){	
	global $all_cabang;

	$query = "
				select kd_cabang from tblcabang where kd_cabang = '".$_SESSION["kd_cabang"]."' order by kd_cabang
			";
	
	$all_cabang="";
	$lrs = pg_query($query);
	while($lrow = pg_fetch_array($lrs)){
		$all_cabang.="'".$lrow["kd_cabang"]."',";
	}
	
	$all_cabang = substr($all_cabang,0,(strlen($all_cabang)-1));
	$all_cabang = "'".$_SESSION['kd_cabang']."'"; //di STM per cabang nomor blanko nya
}
?>
