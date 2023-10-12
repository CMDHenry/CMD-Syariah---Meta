<?
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/numeric.inc.php';
require 'requires/validate.inc.php';
require 'requires/timestamp.inc.php';
require 'requires/input.inc.php';
require 'requires/cek_error.inc.php';
require 'requires/module.inc.php';
require 'requires/db_utility.inc.php';
require 'classes/select.class.php';
require 'requires/file.inc.php';
require 'requires/accounting_utility.inc.php';
require 'requires/stok_utility.inc.php';

$id_menu=trim($_REQUEST["id_menu"]);
$kd_tabs=trim($_REQUEST["kd_tabs"]);
$kd_tabs2=trim($_REQUEST["kd_tabs2"]);
$kd_tabs3=trim($_REQUEST["kd_tabs3"]);

$kd_menu_button=trim($_REQUEST["kd_menu_button"]);
$lrs=pg_query("select * from skeleton.tblmenu where kd_menu='".$kd_menu_button."'");	
$lrow=pg_fetch_array($lrs);
$nama_menu=$lrow['nama_menu'];

//echo today;
//echo $_REQUEST["strisi_taksir_kendaraan"].'sdfsdf';
if(file_exists($path_site."includes/modal_add_".$id_menu.".inc.php")){
	//echo $path_site."includes/modal_add_".$id_menu.".inc.php";
	include $path_site."includes/modal_add_".$id_menu.".inc.php";
}

get_data_menu($id_menu);

get_data_module();

//echo $id_menu;

if($_REQUEST["status"]=="Save") {
	pg_query("BEGIN");
	cek_error();
	if (!$strmsg){
		 save_data();
	}else pg_query("ROLLBACK");
}
?>
<html>
<head>
	<title>.: <?=$_SESSION["application"]?> :.</title>
	<link href="js/cwcalendar.css.php" rel="stylesheet" type="text/css">
    <link href="text.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src='js/alert.js.php'></script>
<script language='javascript' src='js/calendar.js.php'></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src="js/<?=(get_rec("skeleton.tblmodule","use_table_detail","fk_menu='".$id_menu."'"))?get_rec("skeleton.tblmodule","use_table_detail","fk_menu='".$id_menu."'"):"table_v2.js.php"?>"></script>
<script language='javascript' src="js/input_format_number.js.php"></script>
<script language='javascript' src="js/tab.js.php?kd_module=<?=$kd_module?>&id_menu=<?=$id_menu?>"></script>
<script language='javascript'>
<?
_module_create_js();
?>

/*function fcolor(){
	var data="nilai_ap_customer"
	document.getElementById("div"+data).style.color="blue";
}*/
function fSave(){
	lCanSubmit=true
	document.form1.status.value='Save';
	<? 
		if($is_add!='f')_module_create_get_isi()	
	?>
	if (lCanSubmit) document.form1.submit();
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")) {
		lInputClose=getObjInputClose();lInputClose.close()
	}
}
function fSubmit(){
	document.form1.submit();
	//document.getElementById('reference').style.display='block'
	//create_list_data_reference_field_name(pObj.value)
}

function fLoad(){
	<?=get_rec("skeleton.tblmodule","onload_function","fk_menu='".$id_menu."'")?>

	document.form1.kd_tabs.value=''
	if('<?=get_rec("skeleton.tblmodule","is_tab","fk_menu='".$id_menu."'")?>'=='t'){
		fSwitch('div<?=$nm_tabs?>',document.getElementById('menuBar').children[0])	
		//confirm(fSwitch('div<?=$nm_tabs?>',document.getElementById('menuBar').children[0]))
		<? _module_create_menu_tab_load("add")?>
	}
	
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
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="form1" enctype="multipart/form-data">
<input type="hidden" name="id_menu" value="<?=$id_menu?>">
<input type="hidden" name="status">
<input type="hidden" name="kd_tab_on" value="<?=$kd_tab_on?>">
<input type="hidden" name="kd_tabs" value="<?=$kd_tabs?>">
<input type="hidden" name="kd_tabs2" value="<?=$kd_tabs2?>">
<input type="hidden" name="kd_tabs3" value="<?=$kd_tabs3?>">
<input type="hidden" name="nama_menu" value="<?=$nama_menu?>">
<input type="hidden" name="kd_menu_button" value="<?=$kd_menu_button?>">


<?
	if (function_exists('html_additional')) {
		html_additional();
	}

?>
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
			<? _module_create_header("add")?>
           
			<?  //echo get_rec("skeleton.tblmodule","is_tab","fk_menu='".$id_menu."'");
				if(get_rec("skeleton.tblmodule","is_tab","fk_menu='".$id_menu."'")!=t){ 
					
					if($is_add!='f'){
						_module_create_detail("inline-table");
					}
					
				}	
			?>
<!-- end content begin -->
	  	</td>
    </tr>
</table>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" align="center">
	<tr height="20">
    	<td height="25" align="center" bgcolor="#D0E4FF">
            <input class="groove_button" type='button' name="btnsubmit" value='Simpan' onClick="fSave()">
            &nbsp;<input class="groove_button" type="button" value="Batal" onClick="fBatal()">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?
function cek_error(){
	global $strmsg,$j_action,$id_menu,$kd_module,$kd_tabs;
	$j_action="";
	
	$l_is_tab=get_rec("skeleton.tblmodule","is_tab","fk_menu='".$id_menu."'");
	$lrs_field=pg_query("select * from skeleton.tblmodule_fields
		where fk_module='".$kd_module."' and is_add is true --and is_read_only_add='f'
		order by no_urut_add asc");
	while($lrow_field=pg_fetch_array($lrs_field)){
		$lrow_tab=pg_fetch_array(pg_query("select * from skeleton.tblmodule_tabs where fk_module='".$kd_module."' and no_begin_add<='".$lrow_field["no_urut_add"]."' and no_end_add>='".$lrow_field["no_urut_add"]."'"));
		//showquery("select * from skeleton.tblmodule_tabs where fk_module='".$kd_module."' and no_begin_add<='".$lrow_field["no_urut_add"]."' and no_end_add>='".$lrow_field["no_urut_add"]."'");
		cek_error_field($lrow_field["fk_data_type"],$lrow_field["kd_field"],$lrow_field["nm_field"],$lrow_field["is_require"],$lrow_field["condition_require"],$strmsg,$j_action,$lrow_tab["kd_tabs"],$lrow_field["is_no_space"],$lrow_field["is_check_numeric"]);
		//echo $_REQUEST[$lrow_field["kd_field"]];
		if($_REQUEST[$lrow_field["kd_field"]]!=""){	//Cek Error diluar Tab selain kosong
			$lrs_cek_error_other=pg_query("select * from skeleton.tblmodule_fields_cek_error where fk_module_fields='".$lrow_field["pk_id"]."' and (status='Add' or status='Add-Edit')");
			//showquery("select * from skeleton.tblmodule_fields_cek_error where fk_module_fields='".$lrow_field["pk_id"]."' and (status='Add' or status='Add-Edit')");
			while($lrow_cek_error_other=pg_fetch_array($lrs_cek_error_other)){
				//echo $lrow_cek_error_other["condition"];
				eval("\$lresult=".$lrow_cek_error_other["condition"].";");
				if($lresult){ //	$lrow_cek_error_other["condition"] || 
					$strmsg.=$lrow_cek_error_other["pesan_error"].'<br>';
					if(!$j_action) 	{
						$j_action=(($l_is_tab=='t')?"fSwitch('div".$lrow_tab["kd_tabs"]."',document.getElementById('div".$lrow_tab["kd_tabs"]."'));":"");
						$j_action.="document.form1.".$lrow_field["kd_field"].".focus();";
					}
				}
			}
		}
		//}
	}
	
	//cek apakah detail harus diisi atau tidak (diluar tab)
	$lrs_detail=pg_query("select * from skeleton.tblmodule_detail where fk_module='".convert_sql($kd_module)."'");
	while($lrow_detail=pg_fetch_array($lrs_detail)){
		if($lrow_detail["condition_require"]){
			eval("\$l_result_condition_require =".$lrow_detail["condition_require"].";");
			if($_REQUEST["strisi_".$lrow_detail["kd_module_detail"]]=="" && $lrow_detail["is_require"]=='t' && $l_result_condition_require){	//buat $striisi.$i
				$strmsg.=$lrow_detail["nm_module_detail"].' Kosong <br>';
			} else if(($_REQUEST["strisi_".$lrow_detail["kd_module_detail"]]!="" && $lrow_detail["is_require"]=='t' && !$l_result_condition_require)){
				$_REQUEST["strisi_".$lrow_detail["kd_module_detail"]]="";
			} 
		}else if($lrow_detail["condition_require"]==""){
			if($_REQUEST["strisi_".$lrow_detail["kd_module_detail"]]=="" && $lrow_detail["is_require"]=='t'){	//buat $striisi.$i
				$strmsg.=$lrow_detail["nm_module_detail"].' Kosong <br>';
			} 
		}
		//else {
			$l_arr_row = split(chr(191),$_REQUEST["strisi_".$lrow_detail["kd_module_detail"]]);
			if(count($l_arr_row)>0) {
				$l_index_array=1;
				$l_arr_item=array();
				for ($i=0; $i<count($l_arr_row)-1; $i++){	//jumlah baris yang ada didetail
					$l_arr_col=split(chr(187),$l_arr_row[$i]);

					$lrs_detail_field=pg_query("select * from skeleton.tblmodule_detail_fields
						where fk_module_detail='".$lrow_detail["pk_id"]."' and fk_data_type<>'readonly'
						order by no_urut asc");
					while ($lrow_detail_field=pg_fetch_array($lrs_detail_field)){
						cek_error_field_detail($l_arr_col[$lrow_detail_field["no_urut"]],$lrow_detail_field["fk_data_type"],$lrow_detail_field["nm_field"],$lrow_detail_field["is_require"],$lrow_detail_field["condition_require"],$strmsg,$j_action,"@Detail line ".($i+1)." : ");
						
						if($l_arr_col[$lrow_detail_field["no_urut"]]!=""){	//Cek Error diluar Tab selain kosong
							$lrs_cek_error_other=pg_query("select * from skeleton.tblmodule_detail_fields_cek_error where fk_module_fields_detail='".$lrow_detail_field["pk_id"]."' and (status='Add' or status='Add-Edit')");
							while($lrow_cek_error_other=pg_fetch_array($lrs_cek_error_other)){								
								eval("\$lresult =".$lrow_cek_error_other["condition"].";");
								if($lresult){
									$strmsg.="@Detail line ".($i+1)." : ".$lrow_cek_error_other["pesan_error"]."<br>";
								}
							}
						}
					}
				}
			//}
		}
	}
	if (function_exists('cek_error_module')) {
		//cek_error_module();
		//echo "Cek Error Module functions are available.<br />\n";
		$strmsg.=cek_error_module();
	}
	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}



function get_data_module(){
	global $kd_module,$j_action,$nm_tabs,$is_add,$strmsg;
	
	
	$is_add=get_rec("skeleton.tblmodule_detail","is_add","fk_module='".$kd_module."'");
	//query untuk memunculkan tab 
	$nm_tabs=get_rec("skeleton.tblmodule_tabs","kd_tabs","fk_module='".$kd_module."'","no_urut_tabs limit 1");
	$lrow_first_field=pg_fetch_array(pg_query("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' order by no_urut_add limit 1"));
	//showquery("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' order by no_urut_add limit 1");
	$j_action.="document.form1.".$lrow_first_field["kd_field"].".focus();";
	

}
?>