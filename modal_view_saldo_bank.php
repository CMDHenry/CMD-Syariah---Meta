<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/module.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/file.inc.php';
require 'requires/numeric.inc.php';

$id_edit=trim($_REQUEST["id_edit"]);
$id_menu=trim($_REQUEST["id_menu"]);

$fk_module=get_rec("skeleton.tblmodule","pk_id","fk_menu='".$id_menu."'");
$fk_menu_union=get_rec("skeleton.tblmodule","fk_menu","fk_menu_union='".$id_menu."'");;

get_data_menu($id_menu);
get_data_module();

if($_REQUEST["pstatus"]){
	get_data();
}
?>
<html>
<head>
	<title>.: <?=$_SESSION["application"]?> :.</title>
	<link href="js/cwcalendar.css.php" rel="stylesheet" type="text/css">
    <link href="text.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src="js/tab.js.php?kd_module=<?=$kd_module?>&id_menu=<?=$id_menu?>"></script>
<script language='javascript' src="js/input_format_number.js.php"></script>
<script language='javascript'>
function fModal(pType,pID,pModule){
	switch (pType){
		case "view":
		show_modal('modal_view.php?id_menu='+pModule+'&pstatus=view&id_view='+pID,'status:no;help:no;dialogwidth:900px;dialogheight:545px;')
		break;
	}

}
function fLoad(){
	if('<?=get_rec("skeleton.tblmodule","is_tab","fk_menu='".$id_menu."'")?>'=='t'){
		fSwitchView('div<?=$nm_tabs?>',document.getElementById('menuBar').children[0])	
	}
	//parent.parent.document.title="Karyawan";
}
</script>
<body onLoad="fLoad()" bgcolor="#fafafa">
<form action="modal_view.php" method="post" name="form1">
<input type="hidden" name="id_menu" value="<?=$id_menu?>">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
            	<tr><td class="judul_menu" align="center"><?=strtoupper($nm_menu)?></td></tr>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border">
<!-- content begin -->
	  	</td>
	</tr>
      	<td class="border">
        <? view_data()?>
<!-- end content begin -->
	  	</td>
    </tr>
</table>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" align="center">
	<tr height="20">
    	<td align="center" bgcolor="#D0E4FF">&nbsp;</td>
	</tr>
    
    
    
</table>
</form>
</body>
</html>
<?
function view_data(){
	global $id_edit;
	//echo $id_edit;
	$larr=explode('.',$id_edit);
	//print_r($larr);
	$query="select * from data_fa.tblhistory_bank
	where fk_bank='".$larr[0]."' and fk_cabang ='".$larr[1]."' and tgl_tr like '%".$larr[2]."%'
	order by pk_id desc
	";
	//showquery($query);
	$lrs=pg_query($query);
?>
    <table cellpadding="0" cellspacing="1" border="0" width="100%">
        <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
        	<td align="center" width="25%">ID Transaksi </td>
            <td align="center" width="50%">Transaksi </td>
            <td align="center" width="12.5%">Masuk</td>
            <td align="center" width="12.5%">Keluar</td>
        </tr>
<?	

	while($lrow=pg_fetch_array($lrs)){	
?>
         <tr style="padding:0 5 0 5" height="20" bgcolor='#efefef'>
         	<td style="padding:0 5 0 5" class="" align="center"><?=$lrow["id_tr"]?></td>
			<td style="padding:0 5 0 5" class="" align="center"><?=$lrow["keterangan"]?> <?=$lrow["fk_sbg"]?></td>
			<td style="padding:0 5 0 5" class="" align="right"><?=convert_money("",$lrow["nominal_masuk"],0)?></td>	
			<td style="padding:0 5 0 5" class="" align="right"><?=convert_money("",$lrow["nominal_keluar"],0)?></td>	
         </tr>
         
<?		
		$nominal_masuk=$lrow["nominal_masuk"];
		$nominal_keluar=$lrow["nominal_keluar"];
		
		$total_masuk+=$nominal_masuk;
		$total_keluar+=$nominal_keluar;
	}
	
	
?>

		<tr tyle="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
            <td width="25%" style="padding:0 5 0 5" align="center">Total</td>
            <td width="50%" style="padding:0 5 0 5" valign="top"></td>
            <td width='12.5%' style="padding:0 5 0 5" align="right"><?=convert_money("",$total_masuk,0)?></td>
            <td width="12.5%" style="padding:0 5 0 5" align="right"><?=convert_money("",$total_keluar,0)?></td>
        </tr>
    </table>

<?	
	
}
function get_data(){
	global $id_view,$kd_module,$id_menu;
	

}

function get_data_module(){
	global $kd_module,$j_action,$nm_tabs;
	
	//query untuk memunculkan tab 
	$lrow_tab_switch=pg_fetch_array(pg_query("select * from skeleton.tblmodule_tabs where fk_module='".$kd_module."' order by no_urut_tabs limit 1"));
	$nm_tabs=$lrow_tab_switch["kd_tabs"];
	$lrow_first_field=pg_fetch_array(pg_query("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' order by no_urut_add limit 1"));
	$j_action="document.form1.".$lrow_first_field["kd_field"].".focus();";
}