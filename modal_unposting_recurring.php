<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/numeric.inc.php';
require 'requires/accounting_utility.inc.php';
require 'requires/timestamp.inc.php';
require 'classes/select.class.php';

$j_action=$_REQUEST["hidden_focus"];
$id_menu=trim($_REQUEST["id_menu"]);

$fk_module=get_rec("skeleton.tblmodule","pk_id","fk_menu='".$id_menu."'");
$fk_menu_union=get_rec("skeleton.tblmodule","fk_menu","fk_menu_union='".$id_menu."'");;

get_data_menu($id_menu);
get_data_module();

$keterangan=trim($_REQUEST["keterangan"]);
$no_batch=trim($_REQUEST["no_batch"]);
$bulan=trim($_REQUEST["bulan"]);
if(!$bulan)$bulan=get_rec("tblsetting","bulan_accounting");
$tahun=trim($_REQUEST["tahun"]);
if(!$tahun)$tahun=get_rec("tblsetting","tahun_accounting");
$fom=$bulan.'/01/'.$tahun;
$eom=date("Y-m-t",strtotime($fom));

$query=" select * from data_fa.tblrecurring where '".$eom."' >= periode_awal and '".$eom."' <=periode_akhir and (status_data != 'Batal' or status_data is null )";
	
//showquery($query);

$id_edit=trim($_REQUEST["id_view"]);

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
function cekError(){
	var lAlerttxt="";
	var lFocuscursor="";

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
	//grantotal(Table_Detil);
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	}elseif($j_action){
		echo $j_action;
	}else{
		echo "document.form1.no_batch.focus();";
	}
?>
}
</script>
<body onLoad="fLoad();document.form1.autocomplete='off'" bgcolor="#fafafa">
<form action="<?=$_SERVER['PHP_SELF']?>"  method="post" name="form1">
<input type="hidden" name="status">
<input type="hidden" name="strisi" value="<?=$strisi?>">
<input type="hidden" name="hidden_focus">
<input type="hidden" name="lmonth" value="<?=$lmonth?>">
<input type="hidden" name="lyear" value="<?=$lyear?>">
<input type="hidden" name="id_menu" value="<?=$id_menu?>">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<!--<td class="judul_menu" align="center">VERIFIKASI FINTECH AR</td>-->
                <tr><td class="judul_menu" align="center"><?=strtoupper($nm_menu)?></td></tr>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border" id="tdContent">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
            	<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5"width="10%" class="fontColor">Periode Accounting</td>
					<td style="padding:0 5 0 5"width="40%">
                    <input type="hidden" name="bulan" class="groove_text" value="<?=$bulan?>" size="4"><?=$bulan?> 
					- <input type="hidden" name="tahun" class="groove_text" value="<?=$tahun?>" size="4"><?=$tahun?>
                    </td>
                </tr>
                
				<!--<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="10%" class="fontColor">Keterangan</td>
					<td style="padding:0 5 0 5" width="40%">

                     <input name="keterangan" type="text" class='groove_text' id="keterangan" size="42" value="<?=convert_html($keterangan)?>" onKeyUp="fNextFocus(event,document.form1.btnsimpan)">
                     </td>
                </tr>-->
          
		  	</table>
            
				<? 
				if ($bulan&&$tahun!="")
				view_data()
				?>
        </td>
    </tr>
    <tr height="20">
        <td height="25" align="center" bgcolor="#D0E4FF" class="border">
            <input class="groove_button" name="btnsimpan" type='button' value='Simpan' onClick="fSave()">
            &nbsp;<input class="groove_button" type="button" value="Batal" onClick="fBatal()"></td>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?

function view_data(){
	global $id_edit,$query,$lmonth,$lyear,$no_batch,$query;
	
	$lrs=pg_query($query);
	
?>
    <table cellpadding="0" cellspacing="1" border="0" width="100%">
    	<tr tyle="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
			<td colspan="3" align="center">Detail</td>
		</tr>

        <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
        	<td align="center">No Recurring</td>
        	<td align="center">Kode Cabang</td>
            <td align="center">Keterangan</td>
            
        </tr>
<?	

	while($lrow=pg_fetch_array($lrs)){	
?>
         
         
         <tr style="padding:0 5 0 5" height="20" bgcolor='#efefef'>
         	<td style="padding:0 5 0 5" class="" align="center"><?=$lrow["no_recurring"]?></td>
			<td style="padding:0 5 0 5" class="" align="right"><?=$lrow["fk_cabang"]?></td>	
            <td style="padding:0 5 0 5" class="" align="right"><?=$lrow["keterangan"]?></td>		
         </tr>
         
         
<?		
		$nominal=$lrow["nominal"];
		$total_nominal+=$nominal;
	}
	
?>

		<!--<tr tyle="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
            <td width="5%" style="padding:0 5 0 5" align="center" >Total</td>
            <td width="20%" style="padding:0 5 0 5" align="right"><?=convert_money("",$total_nominal,0)?></td>
        </tr>-->
    </table>

<?	
	
}

function cek_error(){
	global $strmsg,$j_action,$strisi,$tr_date,$total_debit,$total_credit,$keterangan,$bulan,$fk_cabang,$fk_jenis_cabang,$bulan,$tahun,$query;

	if (!pg_num_rows(pg_query($query))){
		$strmsg.="Recurring Bulan : ".$bulan.",  Tahun : ".$tahun." tidak ada data.<br>";
		if(!$j_action) $j_action="document.form1.bulan.focus()";
	}
	
	$tr_date=$tahun.'-'.$bulan.'-01';
	if(!cek_periode_accounting(date("Y",strtotime($tr_date)),date("n",strtotime($tr_date)))){
		$strmsg.="Periode yang diinput, tidak sesuai dengan periode Accounting <br>";
		if(!$j_action) $j_action="document.form1.tr_date.focus()";
	}

	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}


function save_data(){
	global $strmsg,$j_action,$strisi,$tr_date,$tr_date_convert,$keterangan,$total_debit,$fk_cabang,$fk_jenis_cabang,$query,$bulan,$tahun;

	$l_success=1;
	pg_query("BEGIN");
		
	$lrs=pg_query($query);
	$query_total="
		select sum(total_debit)as total from(
			".$query."		
		)as tblmain
	";
	//showquery($query_total);
	$lrow_total=pg_fetch_array(pg_query($query_total));
	$total=$lrow_total["total"];
	
	//echo $total.'test';
	
	$tbl="data_fa.tblrecurring_detail";
	
	$fom=$tahun.'-'.$bulan.'-01';
	$eom=$tahun.'-'.$bulan.'-'.date('t',strtotime($fom));
	
	//insert header
	if(!pg_query("insert into data_accounting.tbladjust_memorial (tr_date,description,total) values('".$eom."','RECURRING','".$total."')")) $l_success=0;
	
	//showquery("insert into data_accounting.tbladjust_memorial (tr_date,description,total) values('".$eom."','RECURRING','".$total."')");
	
	$l_no_bukti=get_last_id("data_accounting.tbladjust_memorial","no_bukti");
	
	//log begin
	if(!pg_query("insert into data_accounting.tbladjust_memorial_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from data_accounting.tbladjust_memorial where no_bukti='".$l_no_bukti."'")) $l_success=0;
	$l_id_log=get_last_id("data_accounting.tbladjust_memorial_log","pk_id_log");
	//End Log
	
	
	while($lrow=pg_fetch_array($lrs)){	
		
		$no_recurring=$lrow["no_recurring"];		
		$lwhere="fk_recurring='".$no_recurring."'";		
	
		$query1="select * from ".$tbl." where ".$lwhere." ";
		$lrs1=pg_query($query1);
		//showquery($query1);
		while($lrow1=pg_fetch_array($lrs1)){
			$type_tr=$lrow1["type_tr"];
			$fk_account=$lrow1["fk_account"];
			$total=$lrow1["total"];
			$reference_transaksi=$lrow1["fk_recurring"];
			$fk_cabang=$lrow["fk_cabang"];
			$keterangan=$lrow["keterangan"];
			//insert detail
			$lquery = "insert into data_accounting.tbladjust_memorial_detail(fk_adjust_memorial,type_tr,fk_account,total,reference_transaksi,fk_cabang_detail,description,value) values('".$l_no_bukti."',
					'".$type_tr."','".$fk_account."',
					'".$total."','".$reference_transaksi."','".$fk_cabang."','".$keterangan."','".$total."')";
			//showquery($lquery);
			if(!pg_query($lquery)) $l_success=0;
				
		}
	}
	
	//log begin
	if(!pg_query("insert into data_accounting.tbladjust_memorial_detail_log select *,'".$l_id_log."' from data_accounting.tbladjust_memorial_detail where fk_adjust_memorial='".$l_no_bukti."'")) $l_success=0;
	//end log
	
	//$l_success=0;
	
	if($l_success==1) {
		$strmsg = "Data saved.<br>";
		$strisi="";
		$j_action="lInputClose=getObjInputClose();lInputClose.close()";
		$tr_date=date('m/d/Y');
		$description="";
		pg_query("COMMIT");
	} else {
		$strmsg.= "Error :<br>Data save failed.<br>";
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
		

	
