<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/accounting_utility.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/numeric.inc.php';
require 'requires/timestamp.inc.php';

$id_edit = trim($_REQUEST["id_view"]);

//check_right("HO111013");

if($_REQUEST["pstatus"]){
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
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src="js/validate.js.php"></script>
<script language='javascript'>

function fGenerateAccounting(pField,pIsCoa){
	bulan = <?=date('n')?>;
    tahun = <?=date('Y')?>;
	
	//div_report.style.height=document.body.offsetHeight-100
    
    link_modal = 'report/modal_report_'+pField+'.php?report='+pField+'&pagesource=cabang'
    if(pIsCoa == "t"){
    	fk_coa = document.getElementById('coa').value
        window.open ( link_modal+='&bulan='+bulan+'&tahun='+tahun+'&fk_coa='+fk_coa,'dialogwidth:20px;dialogheight:20px;dialogleft:'+getCenterX(100)+';dialogtop:'+getCenterY(100)+';' )
    }else{
        window.open ( link_modal+='&bulan='+bulan+'&tahun='+tahun+'&fk_cabang='+pCabang+'&fk_divisi='+pDivisi,'dialogwidth:'+div_report.style.width+'px;dialogheight:'+div_report.style.height+'px;dialogleft:'+getCenterX(100)+';dialogtop:'+getCenterY(100)+';' )
	}
}

function fLoad(){
	//parent.parent.document.title="Chart of Account";
}
</script>
<body onLoad="fLoad();document.form1.autocomplete='off'" bgcolor="#fafafa">
<form action="modal_chart_of_account.php" method="post" name="form1">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<td class="judul_menu" align="center">CHART OF ACCOUNT</td>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border" style="border-bottom:none">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%">Chart Of Account</td>
					<td style="padding:0 5 0 5"><input type="hidden" name="coa" style="width:200" id="coa" value="<?=$coa?>"><?=$coa?>&nbsp;&nbsp;&nbsp;&nbsp; <!--<a href="#" class="abutton" onClick="fGenerateAccounting('ledger','t')">Ledger</a> <div id="div_report"></div>-->
                    </td>
				</tr>
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%">Description</td>
					<td style="padding:0 5 0 5"><?=$description?></td>
				</tr>
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%">Additional Desc</td>
					<td style="padding:0 5 0 5"><?=$additional_desc?></td>
				</tr>
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%">Transaction Type</td>
					<td style="padding:0 5 0 5"><?=$transaction_type?></td>
				</tr>
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%">Head Account</td>
					<td style="padding:0 5 0 5"><?=$fk_head_account?></td>
				</tr>
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%">Type of COA</td>
					<td style="padding:0 5 0 5"><?=$type_of_coa?></td>
				</tr>
<!--				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%">Balance</td>
					<td style="padding:0 5 0 5"><?=convert_money('',$balance,2)?></td>
				</tr>
-->		  	</table>
		</td>
	</tr>
</table>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" align="center">
	<tr height="20">
    <td height="25" align="center" bgcolor="#D0E4FF"></tr>
</table>
</form>
</body>
</html>
<?
function get_data(){
	global $strmsg,$j_action,$coa,$description,$additional_desc,$transaction_type,$type_of_coa,$fk_head_account,$id_edit;

	$lrow=pg_fetch_array(pg_query("select * from tbltemplate_coa where coa = '".convert_sql($id_edit)."'"));
	//showquery("select * from tbltemplate_coa where coa = '".convert_sql($id_edit)."'");
	$coa = $lrow["coa"];
	$description = $lrow["description"];
	$additional_desc = $lrow["additional_desc"];
	$transaction_type = $lrow["transaction_type"];
	$type_of_coa = $lrow["type_of_coa"];
	$fk_head_account = $lrow["fk_head_account"];
	$balance = get_rec("(select *, (balance_gl_auto+balance_bank+balance_memorial+balance_cash)as saldo from data_accounting.tblsaldo_coa)as tblsaldo_coa",'saldo',"fk_coa = '".$id_edit."' and tr_month = ".date('n')." and tr_year = ".date('Y'));
}
?>
