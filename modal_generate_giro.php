<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/numeric.inc.php';
require 'requires/db_utility.inc.php';
require 'classes/select.class.php';

/*$id_edit=$_REQUEST["id_edit"];
check_right("DL12151210");
*/
$tgl_generate=trim($_REQUEST["tgl_generate"]);
$kd_giro=trim($_REQUEST["kd_giro"]);
$fk_partner_bank=trim($_REQUEST["fk_partner_bank"]);
$nm_partner=trim($_REQUEST["nm_partner"]);
$dari_nomor=trim($_REQUEST["dari_nomor"]);
$sampai_nomor=trim($_REQUEST["sampai_nomor"]);
$nominal=trim($_REQUEST["nominal"]);


if($_REQUEST["status"]=="Save") {
	pg_query("BEGIN");
	cek_error();
	if(!$strmsg){
		if ($id_edit) edit_data();
		else add_data();
	}else pg_query("ROLLBACK");
}
if($_REQUEST["pstatus"]){
	//get_data();
}
?>
<html>
<head>
	<title>.: <?=__PROJECT_TITLE__?> :.</title>
    <link href="css/text.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src='js/alert.js.php'></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src="js/validate.js.php"></script>
<script language='javascript' src="js/input_format_number.js.php"></script>

<script language='javascript'>
function fGetBank(){
	fGetNC(false,'20171100000051','no_rek','Ganti Bank',document.form1.fk_partner_bank,document.form1.fk_partner_bank)
	if (document.form1.fk_partner_bank.value !="")fGetBankData()
	
}
function fGetBankData(){
	document.getElementById("divPartner").innerHTML=""
	document.form1.nm_partner_bank.value='';
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetBankState
	lSentText="table=(select * from tblbank)as tblmain&field=nm_bank&key=kd_bank&value="+document.form1.fk_partner_bank.value
	lObjLoad.open("POST","ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}
function fGetBankState(){
	if (this.readyState == 4){
		if (this.status==200 && this.responseText!="") {
			document.getElementById("divPartner").innerHTML=this.responseText
			document.form1.nm_partner_bank.value=this.responseText;
		} else {
			document.getElementById("divPartner").innerHTML="-"
			document.form1.nm_partner_bank.value='';
		}
	}
}

function cekError(){
	var lAlerttxt="";
	var lFocuscursor="";
	
	if (document.form1.kd_giro.value==""){
		lAlerttxt+='Kode Giro Kosong<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.kd_giro";}
	}
	
	if (document.form1.fk_partner_bank.value==""){
		lAlerttxt+='Kode Bank Kosong<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.fk_partner_bank";}
	}
	
	if(document.form1.dari_nomor.value==""){
		lAlerttxt+='Dari Nomor Kosong<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.dari_nomor";}
	}else if(check_type('angka',document.form1.dari_nomor)){
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
		alert("Error : <br>"+lAlerttxt,function (){eval(lFocuscursor+".focus()")});
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
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	}
	if($j_action){
		echo $j_action;
	}else{
		echo "document.form1.kd_giro.focus();";
		if($tgl_generate=="")$tgl_generate=date("n/d/Y");
	}
?>
}
</script>
<body onLoad="fLoad();document.form1.autocomplete='off'" bgcolor="#fafafa">
<form action="modal_generate_giro.php" method="post" name="form1">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="status">
<input type="hidden" name="all_cabang" value="<?=$all_cabang?>">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<td align="center" class="judul_menu">GENERATE GIRO</td>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
            	<tr bgcolor="efefef">
			  		<td width="20%" style="padding:0 5 0 5">Tanggal Generate</td>
				  	<td width="30%" style="padding:0 5 0 5"><input name="tgl_generate" type="hidden" class='groove_text' size="30"  value="<?=convert_date_indonesia($tgl_generate)?>"><?=convert_date_indonesia($tgl_generate)?></td>
					<td style="padding:0 5 0 5" class="fontColor">Kode Giro</td>
					<td width="30%" style="padding:0 5 0 5"><input type="text" name="kd_giro" value="<?=convert_html($kd_giro)?>" size="3" maxlength="3"></td>
			  	</tr>
                <tr bgcolor="efefef">
			  		<td width="20%" style="padding:0 5 0 5" class="fontColor">Kode Bank</td>
				  	<td width="30%" style="padding:0 5 0 5"><input name="fk_partner_bank" type="text" class='groove_text' value="<?=convert_html($fk_partner_bank)?>" onChange="fGetBankData()">&nbsp;<img src="images/search.gif" style="border:0px" align="absmiddle" onClick="fGetBank()"></td>
					<td style="padding:0 5 0 5">Nama Bank</td>
					<td width="30%" style="padding:0 5 0 5"><input type="hidden" name="nm_partner_bank" value="<?=convert_html($nm_partner_bank)?>"><span id="divPartner"><?=convert_html($nm_partner_bank)?></span></td>
			  	</tr>
			  	<tr bgcolor="efefef">
					<td style="padding:0 5 0 5" class="fontColor">Dari Nomor</td>
					<td style="padding:0 5 0 5"><input name="dari_nomor" type="text" class="groove_text_numeric" onKeyUp="fNextFocus(event,document.form1.sampai_nomor)" value="<?=convert_html($dari_nomor)?>" maxlength="6"></td>
					<td style="padding:0 5 0 5" class="fontColor">Sampai Nomor</td>
					<td style="padding:0 5 0 5"><input type="text" name="sampai_nomor" class='groove_text_numeric'  onKeyUp="fNextFocus(event,document.form1.btnsimpan)" value="<?=convert_html($sampai_nomor)?>" maxlength="6"></td>
			  	</tr>
			  	<tr bgcolor="efefef">
					<td style="padding:0 5 0 5" class="fontColor">Nominal</td>
					<td style="padding:0 5 0 5"><? create_input_number($nominal,"nominal","","fNextFocus(event,document.form1.nominal);")?></td>
					<td style="padding:0 5 0 5" class="fontColor"></td>
					<td style="padding:0 5 0 5"></td>
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
	global $id_edit,$strmsg,$j_action,$tgl_generate,$kd_giro,$fk_partner_bank,$nm_partner,$dari_nomor,$sampai_nomor,$arr_blanko,$nominal;

	if($kd_giro==""){
		$strmsg.="Kode Giro Kosong.<br>";
		if(!$j_action) $j_action="document.form1.kd_giro.focus()";
	} 
	
	if($nominal=="0"){
		$strmsg.="Nominal Kosong.<br>";
		if(!$j_action) $j_action="document.form1.nominal.focus()";
	} 
	
	if($fk_partner_bank==""){
		$strmsg.="Kode Bank Kosong.<br>";
		if(!$j_action) $j_action="document.form1.fk_partner_bank.focus()";
	} elseif (!pg_num_rows(pg_query("select * from tblbank where kd_bank='".$fk_partner_bank."' for update"))){
		$strmsg.="Kode Bank belum terdaftar.<br>";
		if(!$j_action) $j_action="document.form1.fk_cabang.focus()";
	}

	if($dari_nomor==""){
		$strmsg.="Dari Nomor Kosong.<br>";
		if(!$j_action) $j_action="document.form1.dari_nomor.focus()";
	}

	if($sampai_nomor==""){
		$strmsg.="Sampai Nomor Kosong.<br>";
		if(!$j_action) $j_action="document.form1.sampai_nomor.focus()";
	}elseif($sampai_nomor < $dari_nomor){
		$strmsg.="Sampai Nomor Salah.<br>";
		if(!$j_action) $j_action="document.form1.sampai_nomor.focus()";
	}else{
		$resource_blanko = pg_query("select * from tblgiro where  no_giro >= '".$kd_giro."".str_pad($dari_nomor,6,"0",STR_PAD_LEFT)."' and no_giro <= '".$kd_giro."".str_pad( ($sampai_nomor+1) ,6,"0",STR_PAD_LEFT)."' and fk_cabang='".$_SESSION["kd_cabang"]."' for update");				
		
		while($row_blanko = pg_fetch_array($resource_blanko)){
			$arr_blanko[substr($row_blanko['no_giro'],2,6)] = substr($row_blanko['no_giro'],2,6);
		}
		
		//print_r ($arr_blanko);
/*		if(pg_num_rows($resource_blanko) > 0){
			$strmsg.="Nomor Blanko dari ".$dari_nomor." sampai ".$sampai_nomor." sudah Terdaftar.<br>";
			if(!$j_action) $j_action="document.form1.sampai_nomor.focus()";
		}
*/	}
	
	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}

function add_data(){
	global $id_edit,$strmsg,$j_action,$tgl_generate,$kd_giro,$fk_partner_bank,$nm_partner,$dari_nomor,$sampai_nomor,$arr_blanko,$nominal;
	
	$l_success=1;
	pg_query("BEGIN");	

	//generate blanko by php
	$j=0;
	
	if(count($arr_blanko)==0)$arr_blanko=array();
	
	for($i = $dari_nomor; $i <= $sampai_nomor; $i++){
		$temp = str_pad($i,6,"0",STR_PAD_LEFT);

		if(!in_array($temp,$arr_blanko)){

			if(!pg_query("
					insert into tblgiro (
						no_giro,fk_partner_bank,kd_giro,fk_cabang,
						tgl_create,nominal
					) values(
						'".$kd_giro.$temp."','".convert_sql($fk_partner_bank)."','".$kd_giro."',
						'".$_SESSION["kd_cabang"]."','".date('Y-m-d H:i:s')."','".$nominal."'
					);
					insert into tblgiro_log 
					select *, '".$_SESSION['id']."','".$_SESSION['username']."','".date('Y-m-d H:i:s')."','IA'
					from tblgiro
					where no_giro = '".$kd_giro.$temp."'
					and fk_cabang = '".$_SESSION["kd_cabang"]."';
			"))$l_success = 0;
			else $j++;
		}
		
	}
	
	if($j <= 0){
		$strmsg="Error :<br>".$j." Giro di-Generate.<br>";
		pg_query("ROLLBACK");
	}
	
	
	//end========================

	//log begin	
	//if(!pg_query("insert into tblgenerate_giro_log (fk_jenis_blanko,dari_nomor,sampai_nomor,log_action_userid,log_action_username,log_action_date,log_action_mode) values ('GIRO','".convert_sql($dari_nomor)."','".convert_sql($sampai_nomor)."','".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA')")) $l_success=0;
	//end log]
	//$l_success=0;
	if ($l_success==1){
		$kd_giro=$fk_partner_bank=$nm_partner=$dari_nomor=$sampai_nomor=$nominal="";

		$strmsg=$j." Giro Berhasil di-Generate.<br>";
		pg_query("COMMIT");
	}else{
		$strmsg="Error :<br>".$j." Giro Gagal di-Generate.<br>";
		pg_query("ROLLBACK");
	}
}

function get_data(){	
	global $all_cabang;

	$query = "
		select kd_cabang from tblcabang where fk_area in (
			select fk_area from tblcabang 
			inner join tblarea on fk_area = kd_area
			inner join tblkota on fk_kota = kd_kota
			where kd_cabang = '".$_SESSION["kd_cabang"]."'
		) order by kd_cabang
	";
	
	$all_cabang="";
	$lrs = pg_query($query);
	while($lrow = pg_fetch_array($lrs)){
		$all_cabang.="'".$lrow["kd_cabang"]."',";
	}
	
	$all_cabang = substr($all_cabang,0,(strlen($all_cabang)-1));
}
?>
