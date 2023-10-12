<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/numeric.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/timestamp.inc.php';
//require 'requires/link.inc.php';


$id_edit=trim($_REQUEST["id_edit"]);
$jenis_transaksi=trim($_REQUEST["jenis_transaksi"]);
$reference_transaksi=trim($_REQUEST["reference_transaksi"]);
$tr_date=($_REQUEST["tr_date"]);
//echo $reference_transaksi;
get_data();
?>
<html>
<head>
    <title>.: <?=__PROJECT_TITLE__?> :.</title>
    <link href="css/text.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src='js/openwindow.js.php'></script>
<script language='javascript'>

function fModal(pType,pID,pID2){
	l_obj_function=function (){
		with(document.form1){
			fGetData('module=<?=$module?>&pk_id_module=<?=$pk_id_module?>&page='+intPage+'&strorderby='+strOrderBy+'&strordertype='+strOrderType+'&fk_owner='+document.form1.fk_owner.value)
		}
	}
	switch (pType){
		case "detail":
			show_modal('modal_gl_auto_detail.php?pstatus=edit&id_edit='+pID+'&jenis_transaksi='+escape(pID2),'dialogwidth:825px;dialogheight:245;',l_obj_function)
		break;
	}
}
function fAction(pStatus,pID,pKategori){
	//alert (pStatus)
	
	// SPAREPART
	if(pStatus == "INVOICE PO KTB" || pStatus == "PO ORDER KTB"){
		show_modal('modal_po_view.php?pstatus=view&id_edit='+pID,'dialogwidth:825px;dialogheight:425px;');
	}else if(pStatus == "PENYERAHAN PART"){
		show_modal('modal_penyerahan_part_view.php?pstatus=edit&id_edit='+pID,'dialogwidth:825px;dialogheight:425px;');
	}}



</script>
<body>
<form action="modal_gl_auto_detail.php" method="post" name="form1">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<td class="judul_menu" align="center">GL AUTO</td>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border" style="border-bottom:none">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%">No. Transaksi</td>
                    <td style="padding:0 5 0 5">
                    <? if($type_owner == "CETAK INVOICE"){ ?>
                        <a href="#" class="blue" onClick="fAction('<?=$type_owner?>','<?=convert_html($lrow["no_kwitansi"])?>','<?=convert_html($lrow["fk_kategori_pembebanan"])?>')"><?=$id_edit?></a>
                    <? }  else if($type_owner == "CATAT JUAL INTERNAL") { 
							$lrow_internal = pg_fetch_array(pg_query("SELECT * FROM data_showroom.tblcatat_jual WHERE no_faktur = '".$id_edit."'"));
					?>
                    	<a href="#" class="blue" onClick="fAction('<?=$type_owner?>','<?=convert_html($lrow_internal["pk_id"])?>','<?=convert_html($type_owner)?>')"><?=$id_edit?></a>
                    <? } else { ?>
                    	<a href="#" class="blue" onClick="fAction('<?=$type_owner?>','<?=convert_html($id_edit)?>','<?=convert_html($type_owner)?>')"><?=$id_edit?></a>
                    <? } ?>
					
                    </td>
                    <td style="padding:0 5 0 5" width="20%">Tgl Transaksi</td>
					<td style="padding:0 5 0 5" width="30%">
						<?=date("d/n/Y",strtotime($tr_date))?>
					</td>

				</tr>
				<tr style="padding:0 5 0 5 " bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%">Jenis Transaksi</td>
					<td style="padding:0 5 0 5" width="30%">
						<?=convert_html($type_owner)?>
					</td>
                   
					<td style="padding:0 5 0 5" width="20%">Nasabah</td>
					<td style="padding:0 5 0 5" width="30%">
						<?=convert_html($nm_customer)?>
					</td>
                </tr>
                
                <tr style="padding:0 5 0 5 " bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%">Reference Transaksi</td>
					<td style="padding:0 5 0 5" width="30%">
						<?=convert_html($reference_transaksi)?>
					</td>
                    
					<td style="padding:0 5 0 5" width="20%"></td>
					<td style="padding:0 5 0 5" width="30%">
					</td>
                </tr>    
            </table>
			<table cellspacing="1" cellpadding="0" border="0"  width="100%" style="border-top:1px solid #aaafff">
				<tr bgcolor="#c8c8c8" class="header">
					<td style="padding:0 5 0 5">COA</td>
					<td style="padding:0 5 0 5">Description</td>
					<td style="padding:0 5 0 5" align="center">D</td>
					<td style="padding:0 5 0 5" align="center">C</td>
				</tr>
				<?
				$total_debit=0;
				$total_credit=0;
				//if ($type_tr=="Debit") $total_debit=$total;
				//elseif ($type_tr="Credit")$total_credit=$total;
				if($reference_transaksi)$lreference="and reference_transaksi like '%".$reference_transaksi."%'";
				$query="
					select no_bukti,fk_coa_d,fk_coa_c,total,case when fk_coa_d is null then 'C'  else 'D'  end  as type_tr from  data_accounting.tblgl_auto where fk_owner='".$id_edit."' and type_owner = '".$jenis_transaksi."'  and tr_date = '".$tr_date."' ".$lreference." and total <> 0 
					order by fk_cabang,no_bukti,type_tr desc
				";
				$lrs=pg_query($query); //yg 0 gak usah muncul
				
				//showquery($query);
				while($lrow=pg_fetch_array($lrs)){
				$lrow=array_map("trim",$lrow);
				//print_r ($lrow);echo "<br>";
				?>
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5"><?=(($lrow["fk_coa_d"]=="")?$lrow["fk_coa_c"]:$lrow["fk_coa_d"])?></td>
                
<!--					<td style="padding:0 5 0 5"><?=(($lrow["fk_coa_d"]=="")?do_link($lrow["fk_coa_c"],'chart_of_account_detail','dialogwidth:825px;dialogheight:445px;'):do_link($lrow["fk_coa_d"],'view','dialogwidth:825px;dialogheight:445px;'))?></td>
-->					

					<td style="padding:0 5 0 5"><?=fcoa((($lrow["fk_coa_d"]!="")?$lrow["fk_coa_d"]:$lrow["fk_coa_c"]))?></td>					
					<td align="right" style="padding-right:5"><?=(($lrow["fk_coa_d"]=="")?0:convert_money("",$lrow["total"],2))?></td>
					<td align="right" style="padding-right:5"><?=(($lrow["fk_coa_c"]=="")?0:convert_money("",$lrow["total"],2))?></td>
				</tr>
                <?
					if ($lrow["fk_coa_d"]!="") $total_debit+=$lrow["total"];
					if ($lrow["fk_coa_c"]!="") $total_credit+=$lrow["total"];
				}
				?>
                <tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="212" bgcolor="#c8c8c8"></td>
                    <td style="padding:0 5 0 5" width="325" bgcolor="#c8c8c8" align="right">Total (Rp)</td>
					<td width="" bgcolor="#c8c8c8" align="right" style="padding-right:5">
						<?=convert_money("",$total_debit,2)?>
					</td>
					<td width="" bgcolor="#c8c8c8" align="right" style="padding-right:5">
						<?=convert_money("",$total_credit,2)?>
					</td>
				</tr>
			</table>
			
            <table cellpadding="0" cellspacing="0" border="0" width="100%" align="center" style="border: 1px #aaafff;border-style: solid none solid none;">
                <tr height="20"><td height="25" align="center" bgcolor="#D0E4FF">&nbsp;</td>
                </tr>
            </table>
         </td>
    </tr>
</table>
</form>
</body>
</html>	
<?
function get_data(){
	global $id_edit,$fk_owner,$tr_date,$type_owner,$fk_customer,$fk_supplier,$fk_coa_d,$fk_coa_c,$nm_customer,$nm_supplier,$jenis_transaksi,$reference_transaksi;
	if($reference_transaksi)$lreference="and reference_transaksi like '%".$reference_transaksi."%'";
	$query="
		select * from (
			select fk_owner,tr_date,type_owner,fk_customer,fk_supplier,reference_transaksi from data_accounting.tblgl_auto where fk_owner='".$id_edit."' and type_owner = '".$jenis_transaksi."' and tr_date = '".$tr_date."' ".$lreference."
		)as tblgl_auto
		left join (select fk_sbg,nm_customer from tblinventory left join tblcustomer on no_cif=fk_cif) as tblinventory on fk_sbg=fk_owner
		order by fk_supplier asc
	";
	$lrow=pg_fetch_array(pg_query($query));
	//showquery($query);
	$fk_owner=$lrow["fk_owner"];
	$tr_date=$lrow["tr_date"];
	$type_owner=$lrow["type_owner"];
	$fk_customer=$lrow["fk_customer"];
	$fk_supplier=$lrow["fk_supplier"];
	$reference_transaksi=$lrow["reference_transaksi"];
	$nm_customer=$lrow["nm_customer"];

	

	$fk_coa_d = $lrow["fk_coa_d"];
	$fk_coa_c = $lrow["fk_coa_c"];
}

function fcoa($coa){
	$coa=explode('.',$coa,2);
	$coa=$coa[1];
	//$coa=substr($coa,4);
	$lrow=pg_fetch_array(pg_query("select description from tbltemplate_coa where coa = '".$coa."'"));
	$description = $lrow['description'];
	return $description;
}

?>