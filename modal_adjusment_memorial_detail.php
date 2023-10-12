<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/db_utility.inc.php';
require './requires/numeric.inc.php';
require './requires/text.inc.php';
require './requires/timestamp.inc.php';
require 'classes/select.class.php';

$id_edit=$_REQUEST["id_view"];
$tr_date = convert_date_english(trim($_REQUEST["tr_date"]));
$fk_cabang=$_REQUEST["fk_cabang"];
$nm_cabang=$_REQUEST["nm_cabang"];
if($tr_date)$tr_date=convert_date_english($tr_date);
if($_REQUEST["pstatus"]=="view"){
	get_data();
}

//echo $tr_date."aa";
?>
<html>
<head>
	<title>.: <?=__PROJECT_TITLE__?> :.</title>
    <link href="css/text.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src="js/openwindow.js.php"></script>
<script>
function fAction(pStatus,pID){
	if (pStatus=="detail_coa") {
		show_modal('modal_chart_of_account_detail.php?pstatus=detail&id_edit='+pID,'dialogwidth:825px;dialogHeight:345px');
	}
}
</script>
<body bgcolor="#fafafa">
<form action="modal_adjusment_memorial_detail.php" method="post" name="form1">
<input type="hidden" name="status">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="hidden_focus">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<td class="judul_menu" align="center">ADJUSMENT MEMORIAL</td>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border" style="border-bottom:none">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5"width="20%">No. Bukti</td>
					<td  style="padding:0 5 0 5"width="30%">
                    <?=$id_edit?>
                    </td>
					<td style="padding:0 5 0 5"width="20%">Date</td>
					<td  style="padding:0 5 0 5"width="30%">
                    <input type="hidden" name="tr_date" value="<?=convert_date_indonesia($tr_date)?>"><?=convert_date_indonesia($tr_date)?></td>
				</tr>
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5"width="20%" >Description</td>
					<td  style="padding:0 5 0 5"width="30%" colspan="3"><?=$description?></td>
				</tr>
                
                <tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5"width="20%">Kode Cabang</td>
					<td  style="padding:0 5 0 5"width="30%">
                    <?=$fk_cabang?>
                    </td>
                    
                    <td style="padding:0 5 0 5"width="20%">Nama Cabang</td>
					<td  style="padding:0 5 0 5"width="30%">
                    <?=$nm_cabang?>
                    </td>
                </tr>
			</table>
			<table cellspacing="1" cellpadding="1" border="0"  width="100%">
				<tr bgcolor="#c8c8c8" align="center">
					<td>Account</td>
                    <td>Nama Account</td>
                    <td>Reference</td>
                    <td>Type</td>
                    <td>Total</td>
                    <td>Keterangan</td>
					<td>Kode Cabang</td>                    
                    
				</tr>
				<?
				$total_debit=0;
				$total_credit=0;
				if ($type_tr=="Debit") $total_debit=$total;
				elseif ($type_tr="Credit")$total_credit=$total;
				$lrs=pg_query("
				select fk_account,total,type_tr,tbladjust_memorial_detail.description, tbltemplate_coa.description as description_coa,value,rate,total,reference_transaksi,fk_cabang_detail from data_accounting.tbladjust_memorial_detail 
				left join tbltemplate_coa on fk_account = coa 
				where fk_adjust_memorial='".$id_edit."'");
				while($lrow=pg_fetch_array($lrs)){
					if ($lrow["type_tr"]=="D") $total_debit+=$lrow["total"];
					if ($lrow["type_tr"]=="C") $total_credit+=$lrow["total"];
				?>
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td><?=$lrow["fk_account"]?></td>
                    <td><?=convert_html($lrow["description_coa"])?></td>
                    <td><?=convert_html($lrow["reference_transaksi"])?></td>
                    <td><?=$lrow["type_tr"]?></td>
                    <td align="right"><?=convert_money("",$lrow["total"],2)?></td>
                    <td><?=convert_html($lrow["description"])?></td>
					<td><?=convert_html($lrow["fk_cabang_detail"])?></td>      
                    
				</tr>
				<?
				}
				?>
			</table>
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%" bgcolor="#c8c8c8">Total Debit (Rp)</td>
					<td style="padding:0 5 0 5" width="30%" bgcolor="#c8c8c8" align="right">
						<?=convert_money("Rp ",$total_debit,2)?>
					</td>
					<td style="padding:0 5 0 5" width="20%" bgcolor="#c8c8c8">Total Credit (Rp)</td>
					<td style="padding:0 5 0 5" width="30%" bgcolor="#c8c8c8" align="right">
						<?=convert_money("Rp ",$total_credit,2)?>
					</td>
				</tr>
			</table>
         </td>
    </tr>
    <tr height="20">
        <td height="25" colspan="3" align="center" class="border" bgcolor="#D0E4FF">&nbsp;<td>
    </tr>
</table>
</form>
</body>
</html>
<?
function get_data(){
	global $id_edit,$no_bukti,$tr_date,$description,$fk_cabang,$nm_cabang;

	$lrow=pg_fetch_array(pg_query("select * from data_accounting.tbladjust_memorial left join (select kd_cabang, nm_cabang from tblcabang) as tblcabang on kd_cabang=fk_cabang where no_bukti='".$id_edit."'"));
	$no_bukti=$lrow["no_bukti"];
	$fk_cabang=$lrow["fk_cabang"];
	$nm_cabang=$lrow["nm_cabang"];
	$tr_date=(($lrow["tr_date"]!="")?date('m/d/Y',strtotime($lrow["tr_date"])):"");
	
	$description=$lrow["description"];
}

?>