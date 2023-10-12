<?php
require 'requires/config.inc.php';
require 'requires/general.inc.php';
require 'requires/authorization.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/numeric.inc.php';
require 'requires/referer_check.inc.php';
require 'classes/select.class.php';
require 'requires/accounting_utility.inc.php';

$module=$_REQUEST["module"];//kd_menu/fk_menu
get_data_menu($id_menu);
get_data_module();
	

set_time_limit(0);
$fk_cabang=trim($_REQUEST["fk_cabang"]);
//$bulan=trim($_REQUEST["bulan"]);
//$tahun=trim($_REQUEST["tahun"]);

$tahun=get_rec("tblsetting","tahun_accounting");
$bulan=get_rec("tblsetting","bulan_accounting");
$tgl=convert_date_english($_REQUEST["tgl"]);

if($_REQUEST["status"]=="Save") {
	//print_r($_REQUEST);
	//pg_query("BEGIN");
	recount ($bulan,$tahun,$lcoa);
	//if(!$strmsg){
		//add_data();
	//}else pg_query("ROLLBACK");
}

?>
<html>
<head>
	<title>.: <?=__PROJECT_TITLE__?> :.</title>
    <link href="css/text.css.php" rel="stylesheet" type="text/css">
	<link href="css/menu.css.php" rel="stylesheet" type="text/css">
	<link href="js/cwcalendar.css.php" rel="stylesheet" type="text/css">
    
</head>
<script language='javascript' src="js/alert.js.php"></script>
<script language='javascript' src="js/dd_menu.js.php"></script>
<script language='javascript' src="js/misc.js.php"></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src="js/object_function.js.php"></script>
<script language='javascript' src='js/calendar.js.php'></script>

<script language='javascript'>
var strOrderBy=""
function cekError(){
	var lAlerttxt="";
	var lFocuscursor="";
	var bulan = document.form1.bulan.value;
	var tahun = document.form1.tahun.value;
	
	if (bulan=="--Pilih Bulan--"){
		lAlerttxt+='Bulan Kosong<br>';
		if(lFocuscursor=="")lFocuscursor="document.form1.bulan";
	}
	
	if (document.form1.tahun.value==""){
		lAlerttxt+='Tahun Kosong<br>';
		if(lFocuscursor=="")lFocuscursor="document.form1.tahun";
	}
	
	if (document.form1.tahun.value=="2013"){
		lAlerttxt+='Tahun Salah<br>';
		if(lFocuscursor=="")lFocuscursor="document.form1.tahun";
	}
	
	if (document.form1.bulan.value>="11" && document.form1.tahun.value<="2014"){
		lAlerttxt+='Bulan 1,tahun 2014 Tidak dapat di recount<br>';
		if(lFocuscursor=="")lFocuscursor="document.form1.tahun";
	}
	
	if(lAlerttxt!=""){
		alert("Error : <br>"+lAlerttxt,function (){eval(lFocuscursor+".focus()")});
		return false
	} else return true;
}

function fSave(){
	document.form1.status.value='Save';
	document.form1.submit();

}

function fLoad(){
	//parent.parent.document.title="User";
<?
	
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	}else if($j_action){
		echo $j_action;
	}
	
?>
}
</script>
<body bgcolor="#fafafa" onLoad="fLoad()" onResize="fLoad()">
<form name="form1" action="recount_bank.php" method="post" autocomplete="off">
<input type="hidden" name="status">
<input type="hidden" name="module" value="<?=$module?>">
<input type="hidden" name="_show" value="<?=convert_html($_show)?>">
<table cellpadding="0" cellspacing="0" border="0" width="100%" height="88%">
	<tr background="images/submenu_background.jpg" height="37">
		<td width="20"></td>
		<td class="selectMenu" colspan="2"><?=strtoupper($row_menu["root_menu"])?></td>
	</tr>
	<tr height="20"><td width="20"></td><td align="center" bgcolor="#D0E4FF" class="border">&nbsp;</td><td width="20"></td></tr>
    <tr>
		<td width="20"></td>
		<td class="border" valign="top">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%" style="border-bottom:1px solid #aaafff">
				<tr bgcolor="efefef">
					<td width="20%" style="padding:0 5 0 5" class="fontColor">Cabang</td>
			        <td width="80%" style="padding:0 5 0 5">
                    <? create_list_cabang(); ?>
<!--                    <input type="hidden" name="bulan" class="groove_text" value="<?=$bulan?>" size="4"><?=$bulan?> 
					- <input type="hidden" name="tahun" class="groove_text" value="<?=$tahun?>" size="4"><?=$tahun?>
-->					</td>
				</tr>
                <tr style="padding:0 5 0 5" bgcolor='#efefef'>
                    <td style="padding:0 5 0 5"width="20%" class="fontColor">Tanggal</td>
                    <td style="padding:0 5 0 5"width="80%"><input type="text" value="<?=convert_date_indonesia($tgl)?>" name="tgl" maxlength="10" size="8" onKeyUp="fNextFocus(event,document.form1.tgl)" onChange="fNextFocus(event,document.form1.tgl)">&nbsp;<img src="images/btn_extend.gif" width="13" height="12" onClick="fPopCalendar(document.form1.tgl,function(){document.form1.tgl.focus()})"></td>
                </tr>
                
				<tr bgcolor="efefef">
					<td width="20%"></td>
			        <td width="80%" style="padding:0 5 0 5"><input type="button" name="retrieve" value="Recount" onClick="fSave()"></td>
					<!--img src="images/historis.jpg" onClick="fGenerateData(document.form1._query,document.form1._join)"-->
				</tr>
			</table>
			<div id="divDataTable" style="height:400px;width:800px;overflow:auto"><?=$recount_msg;?></div>
<!-- end content begin -->
    	</td>
		<td width="20"></td>
    </tr>
	<tr height="20"><td width="20"></td><td align="center" bgcolor="#D0E4FF" class="border">&nbsp;</td><td width="20"></td></tr>
</table>
</form>
</body>
</html>
<?
function create_list_cabang(){
	global $fk_cabang;
	$l_list_obj = new select("select kd_cabang, nm_cabang from (select * from tblcabang)as tblcabang order by kd_cabang","kd_cabang","kd_cabang","fk_cabang");
	$l_list_obj->set_default_value($fk_cabang);
	$l_list_obj->add_item("--- All ---",'all',0);
	//$l_list_obj->add_item("Head Office",'head_office',1);
	$l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;'",'form1','');
}

function recount ($p_month,$p_year,$p_coa){
	global $strmsg,$db,$password,$recount_msg,$fk_cabang,$j_action,$tgl;
	
	$p_tgl=$tgl;
	$l_success=1;
	pg_query("BEGIN");
	if($fk_cabang != ''){
		//if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" and fk_cabang = '".$fk_cabang."' ";
	}
	
	
	$p_table="data_fa.tblsaldo_bank";
	
	$lquery = "
	select * from ".$p_table."
	where tgl = '".$p_tgl."' 
	".$lwhere."
	";
	//showquery($lquery);
	
	$yesterday=date("Y-m-d",strtotime('-1 day',strtotime($p_tgl)));
	//echo $yesterday;
	$rs = pg_query($lquery);

	while($row = pg_fetch_array($rs)){
		$masuk=$row["masuk"];
		$keluar=$row["keluar"];
		
		$query="
		select * from(
			select * from ".$p_table."
			where tgl = '".$yesterday."' and fk_bank='".$row['fk_bank']."' and fk_cabang='".$row['fk_cabang']."'		
		)as tblsaldo
		full join (
			select sum(nominal_masuk) as masuk, sum(nominal_keluar)as keluar,fk_bank as fk_bank1,fk_cabang as fk_cabang1 from data_fa.tblhistory_bank 
			where tgl_tr between '".$p_tgl." 00:00:00' and '".$p_tgl." 23:59:59' and fk_bank='".$row['fk_bank']."' and fk_cabang='".$row['fk_cabang']."'	
			group by fk_bank,fk_cabang	
		)as tblhistory on fk_bank=fk_bank1 and fk_cabang=fk_cabang1
		";
		//showquery($query);
		if(pg_num_rows(pg_query($query))){
			$lrs = pg_query($query);
			$lrow_saldo = pg_fetch_array($lrs);
			$akhir=$lrow_saldo["akhir"];
			$masuk=($lrow_saldo["masuk"]?$lrow_saldo["masuk"]:0);
			$keluar=($lrow_saldo["keluar"]?$lrow_saldo["keluar"]:0);		
			
			$p_where="tgl = '".$p_tgl."' and fk_bank='".$row['fk_bank']."' and fk_cabang='".$row['fk_cabang']."'";
			
			$query_saldo.=insert_log($p_table,$p_where,'UB');
			$query_saldo.= "update ".$p_table." set 
			awal=".$akhir.",
			masuk=".$masuk.",
			keluar=".$keluar.",
			akhir=".$akhir."+".$masuk."-".$keluar."
			where ".$p_where." ;"
			;			
			
			$query_saldo.=insert_log($p_table,$p_where,'UB');
			
			if(!pg_query($query_saldo))$l_success=0;
			//showquery($query_saldo);
		}
	}
	
	//$l_success=0;
	if ($l_success==1){
		$strmsg="Recounting Sukses<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		$recount_msg='';
		pg_query("COMMIT");
	}else{
		$strmsg="Error :<br>Recounting Gagal.<br>";
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
	$j_action="document.form1.tgl.focus();";
}

?>