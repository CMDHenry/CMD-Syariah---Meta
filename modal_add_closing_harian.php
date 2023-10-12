<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/module.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/file.inc.php';
require 'requires/numeric.inc.php';
require 'requires/accounting_utility.inc.php';
require 'requires/stok_utility.inc.php';
require_once 'closing.php';

$id_edit=trim($_REQUEST["id_edit"]);
$id_menu=trim($_REQUEST["id_menu"]);
$fk_module=get_rec("skeleton.tblmodule","pk_id","fk_menu='".$id_menu."'");
$fk_menu_union=get_rec("skeleton.tblmodule","fk_menu","fk_menu_union='".$id_menu."'");;
$tgl_akhir_closing = trim($_REQUEST["tgl_akhir_closing"]);
if($tgl_akhir_closing)$tgl_akhir_closing=convert_date_english($tgl_akhir_closing);
else $tgl_akhir_closing=date("m/d/Y",strtotime('+1 day',strtotime(today_db)));
//echo $upload_path;
get_data_menu($id_menu);
get_data_module();

if($_REQUEST["status"]=="Save") {
	cek_error();
	if(!$strmsg){
		save_data();
	}
}

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
<script language='javascript' src="js/alert.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src="js/tab.js.php?kd_module=<?=$kd_module?>&id_menu=<?=$id_menu?>"></script>
<script language='javascript' src="js/input_format_number.js.php"></script>
<script language='javascript' src='js/calendar.js.php'></script>
<script language='javascript' src="js/validate.js.php"></script>
<script language='javascript'>


function cekError(){
	return true;
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")) {
		lInputClose=getObjInputClose();lInputClose.close()
	}
}

function fSave(){
	//if (cekError()) {
		document.form1.status.value='Save';
		document.form1.submit();
	//}
}

function fLoad(){
	//if('</?=get_rec("skeleton.tblmodule","is_tab","fk_menu='".$id_menu."'")?>'=='t'){
//		fSwitchView('div<?=$nm_tabs?>',document.getElementById('menuBar').children[0])	
//	}
<?	
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	}else if($j_action){
		echo $j_action;
	}
?>
}


</script>
<body onLoad="fLoad()" bgcolor="#fafafa">
<form action="modal_add_closing_harian.php" method="post" name="form1">
<input type="hidden" name="id_menu" value="<?=$id_menu?>">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="status">
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
	<table cellpadding="0" cellspacing="1" border="0" width="100%">
		<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5"width="20%" class="fontColor">Closing ke Tgl</td>
					<td style="padding:0 5 0 5"width="30%"><input type="text" value="<?=convert_date_indonesia($tgl_akhir_closing)?>" name="tgl_akhir_closing" maxlength="10" size="8" onKeyUp="fNextFocus(event,document.form1.tgl_akhir_closing)" onChange="fNextFocus(event,document.form1.tgl_akhir_closing)">&nbsp;<img src="images/btn_extend.gif" width="13" height="12" onClick="fPopCalendar(document.form1.tgl_akhir_closing,function(){document.form1.tgl_akhir_closing.focus()})"></td>
				  	<td class="fontColor" style="padding:0 5 0 5">&nbsp;</td>
				  	<td class="fontColor" style="padding:0 5 0 5">&nbsp;</td>
		</tr>
    </table>
        
        <? //view_data()?>
<!-- end content begin -->
	  	</td>
    </tr>
</table>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" align="center">
	
    <tr>
		<td height="25" align="center" bgcolor="#D0E4FF" class="border">
		  <input class="groove_button" type='button' name="btnsubmit" value='Simpan' onClick="fWait();fSave()">		
            &nbsp;<input class="groove_button" type="button" value="Batal" onClick="fBatal()">
		</td>
	</tr>
    
</table>
</form>
</body>
</html>
<?
function cek_error(){
	global $id_edit,$strmsg,$j_action,$tgl_akhir_closing;

	if (pg_num_rows(pg_query("select * from data_gadai.tblclosing_harian where tgl_closing like '%".today_db."%'"))){
		$strmsg.="Closing harian sudah dilakukan.<br>";
	} 
	
	if (strtotime($tgl_akhir_closing) < strtotime(today_db)){
		$strmsg.="Tanggal Akhir Closing tidak boleh lebih kecil dari tanggal Sistem.<br>";
	} 
	
	if (strtotime($tgl_akhir_closing) > strtotime(date("m/d/Y"))){
		$strmsg.="Tanggal Akhir Closing tidak boleh lebih besar dari tgl real<br>";
	} 
	//showquery("select * from data_gadai.tblclosing_harian where tgl_closing  like '%".today_db."%'");
	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}

function view_data(){
	global $id_edit,$tgl_akhir_closing;
	
?>
	<input type="hidden" name="no" value="<?=$no?>">
      
    <table cellpadding="0" cellspacing="1" border="0" width="100%">
    	<tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
        	<td colspan="2" align="center">Detail barang belum diterima /tidak jadi pelunasan</td>
      
        </tr>
        <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
        	<td align="center">NO SBG </td>
            <td align="center">Kode Cabang</td>         
        </tr>
<?	
	$query1="select * from tblinventory where status in('Belum Terima','Pengajuan Pelunasan','Diserahkan')";
	//showquery($query1);
	$lrs1=pg_query($query1);
	$no=0;
	while($lrow=pg_fetch_array($lrs1)){	
?>
         <tr style="padding:0 5 0 5" height="20" bgcolor='#efefef'>
         	<td style="padding:0 5 0 5" class="" align="center"><?=$lrow["fk_sbg"]?></td>
			<td style="padding:0 5 0 5" class="" align="center"><?=$lrow["fk_cabang"]?></td>
         </tr>
      
<?		
		$no++;
	}
?>


		<tr tyle="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
			<td colspan="2" align="center">Detail transaksi yang gantung</td>
		</tr>
        <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
        	<td align="center">NO SBG </td>
            <td align="center">Kode Cabang</td>
        </tr>
<?	
	$query2="select * from viewkontrak 
				left join (
					select *, fk_cabang as fk_cabang_viewtaksir from viewtaksir 
				)as viewtaksir on no_fatg = fk_fatg
				left join tblinventory on fk_sbg=no_sbg
			where (status_approval ='Need Approval' or (status_approval ='Approve' and tgl_cair is null)) and status_data='Approve'";
	//showquery($query2);
	$lrs2=pg_query($query2);
	while($lrow=pg_fetch_array($lrs2)){	
?>
         <tr style="padding:0 5 0 5" height="20" bgcolor='#efefef'>
         	<td style="padding:0 5 0 5" class="" align="center"><?=$lrow["no_sbg"]?></td>
			<td style="padding:0 5 0 5" class="" align="center"><?=$lrow["fk_cabang_viewtaksir"]?></td>
         </tr>
<?		
		$no++;
	}
	
?>

		<tr tyle="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
			<td colspan="2" align="center">Detail cabang belum close cashier</td>
		</tr>
        <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
        	<td colspan="2" align="center">Kode Cabang </td>
        </tr>
<?	
	$query3="
	select * from tblcabang
	where status_kasir !='Close'
	order by kd_cabang
	";
	//showquery($query3);
	$lrs3=pg_query($query3);

	while($lrow=pg_fetch_array($lrs3)){	
?>
         <tr style="padding:0 5 0 5" height="20" bgcolor='#efefef'>
         	<td style="padding:0 5 0 5" class="" align="center" colspan="2"><?=$lrow["kd_cabang"]?></td>
         </tr>
    
    
<?	

		$no++;
	}
	
?>

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


function save_data(){
	global $j_action,$strmsg,$tgl_akhir_closing,$l_success,$wreturn;
	$tbl="tblsetting";
	
	$lwhere="is_closing_harian='f'";
	if(!pg_query(insert_log($tbl,$lwhere,'UB')));
	if(!pg_query("update ".$tbl." SET is_closing_harian='t' where ".$lwhere."")) $l_success=0;	
	if(!pg_query(insert_log($tbl,$lwhere,'UA')));	
	
	$l_success=1;
	
	pg_query("BEGIN");
	
	//echo $tgl_akhir_closing;
	if (function_exists('closing')) {	
		$jumlah_hari=strtotime($tgl_akhir_closing)-strtotime(today_db);
		$jumlah_hari=$jumlah_hari/(60*60*24);
		//echo $jumlah_hari;
		while($jumlah_hari>0){				
			if(!closing()){
				$l_success=0;				
			}
			$jumlah_hari--;
		}		
	}
	$lwhere="is_closing_harian='t'";
	if(!pg_query(insert_log($tbl,$lwhere,'UB')));
	if(!pg_query("update ".$tbl." SET is_closing_harian='f' where ".$lwhere."")) $l_success=0;	
	if(!pg_query(insert_log($tbl,$lwhere,'UA')));	
	
	//echo $l_success;
	//$l_success=0;
	if ($l_success==1){
		$strmsg="Data Tersimpan.<br>";
		//$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		$j_action= "top.location.reload()";
		pg_query("COMMIT");
	}else{
		$strmsg="Error :<br>Data save failed.<br>";
		pg_query("ROLLBACK");		
		if(!pg_query(insert_log($tbl,$lwhere,'UB')));
		if(!pg_query("update ".$tbl." SET is_closing_harian='f' where ".$lwhere."")) $l_success=0;	
		if(!pg_query(insert_log($tbl,$lwhere,'UA')));	
		
	}
}
