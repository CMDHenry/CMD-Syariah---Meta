<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/module.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/file.inc.php';
require 'requires/numeric.inc.php';

$id_edit=trim($_REQUEST["id_view"]);
$id_menu=trim($_REQUEST["id_menu"]);

$fk_module=get_rec("skeleton.tblmodule","pk_id","fk_menu='".$id_menu."'");
$fk_menu_union=get_rec("skeleton.tblmodule","fk_menu","fk_menu_union='".$id_menu."'");;
$tgl_closing = trim($_REQUEST["tgl_closing"]);
if($tgl_closing)$tgl_closing=convert_date_english($tgl_closing);
else $tgl_closing=date("m/d/Y");
//echo $tgl_closing.'123';

/*$tgl_closing = trim($_REQUEST["tgl_closing"]);
if($tgl_closing)$tgl_closing=convert_date_english($tgl_closing);
else $tgl_closing=date("m/d/Y",strtotime('+1 day',strtotime(today_db)));
*/
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
    <tr>
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
	global $id_edit,$tgl_closing;
	//echo $id_edit;
	$larr=explode('.',$id_edit);
	//print_r($larr);
	$query="select * from data_gadai.tblclosing_harian where no_closing = '".$id_edit."'";
	//showquery($query);
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	$tgl_closing=date("m/d/Y",strtotime($lrow["tgl_closing"]));
	
?>
	<table cellpadding="0" cellspacing="1" border="0" width="100%">
		<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5"width="20%" class="fontColor">Tanggal Closing</td>
					<td style="padding:0 5 0 5"width="30%"><?=convert_date_indonesia($tgl_closing)?></td>
				  	<td class="fontColor" style="padding:0 5 0 5">&nbsp;</td>
				  	<td class="fontColor" style="padding:0 5 0 5">&nbsp;</td>
		</tr>
    </table>
      
    <table cellpadding="0" cellspacing="1" border="0" width="100%">
    	<tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
        	<td colspan="2" align="center">Detail barang belum penerimaan</td>
      
        </tr>
        <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
        	<td align="center">NO SBG </td>
            <td align="center">Kode Cabang</td>         
        </tr>
<?	
	$query1="select * from data_gadai.tblclosing_harian_detail
				left join tblcabang on kd_cabang = fk_cabang
				left join tblinventory on fk_sbg=no_ref
			where keterangan = 'Detail barang belum penerimaan' and fk_closing = '".$id_edit."'";
	//showquery($query1);
	$lrs1=pg_query($query1);
	while($lrow=pg_fetch_array($lrs1)){	
?>
         <tr style="padding:0 5 0 5" height="20" bgcolor='#efefef'>
         	<td style="padding:0 5 0 5" class="" align="center"><?=$lrow["fk_sbg"]?></td>
			<td style="padding:0 5 0 5" class="" align="center"><?=$lrow["fk_cabang"]?></td>
         </tr>
      
<?		
	}
?>


		<tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
			<td colspan="2" align="center">Detail transaksi yang gantung</td>
		</tr>
        <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
        	<td align="center">NO SBG </td>
            <td align="center">Kode Cabang</td>
        </tr>
<?	
	$query2="select * from data_gadai.tblclosing_harian_detail
				left join tblcabang on kd_cabang = fk_cabang
				left join tblinventory on fk_sbg=no_ref
			where keterangan = 'Detail transaksi yang gantung' and fk_closing = '".$id_edit."'";
	//showquery($query2);
	$lrs2=pg_query($query2);
	while($lrow=pg_fetch_array($lrs2)){	
?>
         <tr style="padding:0 5 0 5" height="20" bgcolor='#efefef'>
         	<td style="padding:0 5 0 5" class="" align="center"><?=$lrow["no_ref"]?></td>
			<td style="padding:0 5 0 5" class="" align="center"><?=$lrow["kd_cabang"]?></td>
         </tr>
<?		
	}
	
?>

		<tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
			<td colspan="2" align="center">Detail cabang belum close cashier</td>
		</tr>
        <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
        	<td colspan="2" align="center">Kode Cabang </td>
        </tr>
<?	
	$query3="select * from data_gadai.tblclosing_harian_detail
				left join tblcabang on kd_cabang = fk_cabang
				left join tblinventory on fk_sbg=no_ref
			where keterangan = 'Detail cabang belum close cashier' and fk_closing = '".$id_edit."'";
	//showquery($query3);
	$lrs3=pg_query($query3);

	while($lrow=pg_fetch_array($lrs3)){	
?>
         <tr style="padding:0 5 0 5" height="20" bgcolor='#efefef'>
         	<td colspan="2" style="padding:0 5 0 5" class="" align="center"><?=$lrow["kd_cabang"]?></td>
         </tr>
    
     
<?	
	}
?>
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