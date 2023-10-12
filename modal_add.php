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
//echo $path_site."includes/modal_add_".$id_menu.".inc.php";
if(file_exists($path_site."includes/modal_add_".$id_menu.".inc.php")){
	
	include $path_site."includes/modal_add_".$id_menu.".inc.php";
}

get_data_menu($id_menu);

get_data_module();
//echo $id_menu;

if(!$_REQUEST["fk_fatg"]&& $_REQUEST["fk_fatg_link"])$_REQUEST["fk_fatg"]=$_REQUEST["fk_fatg_link"];//umum
$no_image=$_REQUEST["no_image"];//ocr

//echo $_REQUEST["no_id"].'->id';

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

function fModal_view(pType,pID,pID2,pID3){

	switch (pType){
		case "view_angsuran":
			show_modal('modal_view.php?pstatus=view&id_view='+pID+'&id_menu='+escape(pID2)+'&kd_menu_button='+escape(pID3),'dialogwidth:900px;dialogheight:545px')
		break;
		
	}
}


function fConfirm(){
	if (confirm("Apakah anda yakin ingin melakukan pembayaran ?")) {
		fSave()
	}
}

function fSave(){

	lCanSubmit=true	
	document.form1.status.value='Save';
	<? 
		_module_create_get_isi()	
	?>	
	
	if (lCanSubmit) {	
	
		document.form1.submit();
	}
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
function fCheckSubmit(){
	
	if (document.form1.status.value=='Save') {
		return true
	}
	else return false
}
	
function fLoad(){
	if (typeof fIsi === "function") { 
    // safe to use the function
		fIsi();
		//confirm('sdf')
	}
	
	<?=get_rec("skeleton.tblmodule","onload_function","fk_menu='".$id_menu."'")?>
	
	<? if($_REQUEST["fk_fatg_link"]){ //umum otomatis ?>
		fGetReferensiTaksirData()
	<? }?>
	
	<? if($_REQUEST["no_image"]){ //ocr ?>
		fGetKelurahanData()
	<? }?>

	
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
<form action="modal_add.php" method="post" name="form1" id="form1" enctype="multipart/form-data" onSubmit="return fCheckSubmit()">

<!--<form action="modal_add.php" method="post" name="form1" enctype="multipart/form-data">
--><input type="hidden" name="id_menu" value="<?=$id_menu?>">
<input type="hidden" name="status">
<input type="hidden" name="kd_tab_on" value="<?=$kd_tab_on?>">
<input type="hidden" name="kd_tabs" value="<?=$kd_tabs?>">
<input type="hidden" name="kd_tabs2" value="<?=$kd_tabs2?>">
<input type="hidden" name="kd_tabs3" value="<?=$kd_tabs3?>">
<input type="hidden" name="nama_menu" value="<?=$nama_menu?>">
<input type="hidden" name="kd_menu_button" value="<?=$kd_menu_button?>">
<? if ($id_menu=='20170800000001') { ?>
	<input type="hidden" name="no_image" value="<?=$no_image?>">
<? }?>


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
						_module_create_detail("inline-table",'is_add');
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
        
<? if($id_menu=="20180200000046" ){?>        
            <input class="groove_button" type='button' name="btnsubmit1" value='Generate Detail' onClick="document.form1.submit()">
<? }?>  
      
<? if($id_menu=="20170800000053" || $id_menu=="20170800000054"||$id_menu=="20170800000056"){?>        
            <input class="groove_button" type='button' name="btnsubmit" value='Simpan & Cetak Kwitansi' onClick="fConfirm()">
<? }else{?>
            <input class="groove_button" type='button' name="btnsubmit" value='Simpan' onClick="fSave()">
<? }?>     
       
            &nbsp;<input class="groove_button" type="button" value="Batal" onClick="fBatal()">            
<?
		if(strstr($nm_menu,'DP')){
		$id_menu='20170800000032';
?>           
        &nbsp;&nbsp;&nbsp;<input class="groove_button" type='button' name="btnview" value='View Data Angsuran' onClick="fModal_view('view_angsuran',document.form1.fk_sbg_dp.value,'<?=$id_menu?>','10101011');">      
<?
		}
?>       
<?
		if(strstr($nm_menu,'Kredit')){		
?>           
<!--        &nbsp;&nbsp;&nbsp;<input class="groove_button" type='button' name="btnview" value='Credit Scoring' onClick="fGetScoringData();">      
--><?
		}
?>       
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
					//$strmsg.=$_REQUEST[$lrow_field["kd_field"]].' : '.$lrow_cek_error_other["pesan_error"].'<br>';
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
								$lrow_cek_error_other["condition"]=str_replace("&#039;","'",$lrow_cek_error_other["condition"]);							
								eval("\$lresult =".$lrow_cek_error_other["condition"].";");
								if($lresult){
									$strmsg.="@".$lrow_detail["nm_module_detail"]." line ".($i+1)." : ".$lrow_cek_error_other["pesan_error"]."<br>";
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

function save_data(){
	global $j_action,$strmsg,$id_menu,$kd_module, $strisi1,$upload_path,$kd_tabs,$kd_tabs2,$kd_tabs3,$upload_path_website_pic,$is_add,$nama_menu,$l_success,$upload_path_pic;

	$l_success=1;
	
	pg_query("BEGIN");
		
	if($kd_tabs=="" && $kd_tabs2=="" && $kd_tabs3==""){
		$lrs_tabel=pg_query("select *
							from skeleton.tblmodule_fields
							where 
							fk_module='".$kd_module."' and (is_add is true or (is_edit is true and (default_value_input !='' or  (default_value_nextserial!=''and default_value_nextserial is not null)))) 
							and (is_read_only_add is false or (default_value_input !='' and fk_data_type!='readonly') )  and save_to_table !=''
							and is_tab is false order by save_to_table,save_to_field");
	}else {
		$lrs_tabel=pg_query("select * 
							from skeleton.tblmodule_fields inner join skeleton.tblmodule_tabs on 						
							skeleton.tblmodule_fields.fk_module=skeleton.tblmodule_tabs.fk_module 
							and skeleton.tblmodule_fields.no_urut_add between skeleton.tblmodule_tabs.no_begin_add and
							skeleton.tblmodule_tabs.no_end_add where skeleton.tblmodule_fields.fk_module='".$kd_module."' 
							and (is_add is true or (is_edit is true and default_value_input !='')) and is_read_only_add is false
							and (kd_tabs='".$kd_tabs."' OR kd_tabs='".$kd_tabs3."' or kd_tabs='".$kd_tabs2."' or no_urut_tabs='0') and save_to_table !=''
							order by kd_field,save_to_table,save_to_field");

	}						
	$temp_value;
	$count_value=0;	
	$pk_id="";		
	while($lrow_table=pg_fetch_array($lrs_tabel)){
			$l_arr_table[$lrow_table["save_to_table"]][$lrow_table["kd_field"]]=$lrow_table["save_to_field"].(($lrow_table["save_to_field_2"])?",".$lrow_table["save_to_field_2"]:"");
			$data_type[$lrow_table["kd_field"]]=$lrow_table["fk_data_type"];
			$temp_value[$count_value]=$lrow_table["default_value_input"];
			$value_type[$lrow_table["kd_field"]]=$lrow_table["value_type"];
			$is_read_only[$count_value]=$lrow_table["is_read_only_add"];
//			if($lrow_table["fk_data_type"]=="date"){
//				$temp_value[$count_value] = eval("return $temp_value[$count_value];");
//			}
			if($lrow_table["default_value_nextserial"]!=""){
						
				$nextserial_value=split(',',$lrow_table["nextserial_value"]);
				$query_serial="select ".$lrow_table["default_value_nextserial"]."(";
				//print_r($nextserial_value);
				for($i=0;$i<count($nextserial_value);$i++){
					//echo($nextserial);
					$query_serial.=" '".eval("return $nextserial_value[$i];")."'::text ,";					
				}
				$query_serial=rtrim($query_serial,',').")";

				//echo $query_serial;				
				$lserial=pg_fetch_array(pg_query($query_serial));	
				$temp_value[$count_value]=$lserial[$lrow_table["default_value_nextserial"]];
				if(get_rec("skeleton.tbldb_table_detail","kd_field","is_pk=true and kd_field='".$lrow_table["save_to_field"]."'")){
					$pk_id=$lserial[$lrow_table["default_value_nextserial"]];					
				}
						//echo eval("return $p_default_value;")
			}
			$count_value+=1;
	}
	
	$count_value=0;
	foreach ($l_arr_table as $l_table=>$l_arr_field){
		$l_arr_query[$l_table]="insert into ".$l_table." (".join(",",$l_arr_table[$l_table]).") values (";
	}
	//print_r ($l_arr_table);
	foreach ($l_arr_table as $l_table=>$l_arr_field){
		foreach ($l_arr_field as $l_kd_field=>$l_save_to_field) {
					$l_kd_field=str_replace("[]","",$l_kd_field);
					if(($_REQUEST[$l_kd_field]) =="" and $temp_value[$count_value] !=""){
							$_REQUEST[$l_kd_field]= $temp_value[$count_value];
					}
					$_REQUEST[$l_kd_field]=trim($_REQUEST[$l_kd_field]);
					$count_value+=1;
					//print_r ($data_type)."<br>";
					if($data_type[$l_kd_field]=='date'){
						//." ".date("H:i:s")
						$l_arr_query[$l_table].=(($_REQUEST[$l_kd_field])?"'".($temp_value[$count_value]!=""&&$is_read_only[$count_value]=='t'?convert_date_english($_REQUEST[$l_kd_field]):convert_date_english($_REQUEST[$l_kd_field]))."'":"null").",";
					}else if($data_type[$l_kd_field]=='checkbox'){
						$l_arr_query[$l_table].=(($_REQUEST[$l_kd_field])?"'".((is_array($_REQUEST[$l_kd_field]))?join(",",$_REQUEST[$l_kd_field]):$_REQUEST[$l_kd_field])."'":'false').",";
					}else if($data_type[$l_kd_field]=='password'){
						 $l_arr_query[$l_table].=(($_REQUEST[$l_kd_field])?"'".((is_array($_REQUEST[$l_kd_field]))?join(",",$_REQUEST[$l_kd_field]):crypt($_REQUEST[$l_kd_field]))."'":"null").",";
					}else if($data_type[$l_kd_field]=='range_value' || $data_type[$l_kd_field]=='range_date'){
						if($data_type[$l_kd_field]=='range_date'){
							$_REQUEST[$l_kd_field]=convert_date_english($_REQUEST[$l_kd_field."1"])."','".convert_date_english($_REQUEST[$l_kd_field."2"]);
						} else{
							$_REQUEST[$l_kd_field]=$_REQUEST[$l_kd_field."1"]."','".$_REQUEST[$l_kd_field."2"];	
						}
						$l_arr_query[$l_table].=(($_REQUEST[$l_kd_field])?"'".((is_array($_REQUEST[$l_kd_field]))?join(",",$_REQUEST[$l_kd_field]):$_REQUEST[$l_kd_field])."'":"null").",";
					}else if($data_type[$l_kd_field]=='others'){
							//echo "123";
							$_REQUEST[$l_kd_field]=$_REQUEST[$l_kd_field."1"]."-".$_REQUEST[$l_kd_field."2"]."-".$_REQUEST[$l_kd_field."3"];	
							$l_arr_query[$l_table].=(($_REQUEST[$l_kd_field])?"'".((is_array($_REQUEST[$l_kd_field]))?join(",",$_REQUEST[$l_kd_field]):$_REQUEST[$l_kd_field])."'":"null").",";
					}else if($data_type[$l_kd_field]=='file'){
						$count_gbr=0;
						$menu_pic=trim($_REQUEST[$l_kd_field]);
						$pic = $_FILES[$l_kd_field]['name'];
						if($pic) $l_success = move_uploaded_file($_FILES[$l_kd_field]['tmp_name'], $upload_path_website_pic."/".$_FILES[$l_kd_field]['name']);
						$l_arr_query[$l_table].=(($pic)?"'".((is_array($pic))?join(",",$pic):$pic)."'":"null").",";
					}else if($data_type[$l_kd_field]=='list'){
						if($_REQUEST[$l_kd_field]!=""){
						$l_arr_query[$l_table].=(($_REQUEST[$l_kd_field] || $_REQUEST[$l_kd_field]==0)?"'".((is_array($_REQUEST[$l_kd_field]))?join(",",$_REQUEST[$l_kd_field]):$_REQUEST[$l_kd_field])."'":"null").",";
						}else{
							$l_arr_query[$l_table].=(($_REQUEST[$l_kd_field] || $_REQUEST[$l_kd_field]==0)?"null":"null").",";
						}
					} else{
						//echo $_REQUEST[$l_kd_field].'-'.$l_kd_field."<br>";
						//if($value_type[$l_kd_field]=='php')$_REQUEST[$l_kd_field]=eval("return $_REQUEST[$l_kd_field];");
						
						$l_arr_query[$l_table].=(($_REQUEST[$l_kd_field] || $_REQUEST[$l_kd_field]==0)?"'".((is_array($_REQUEST[$l_kd_field]))?join(",",$_REQUEST[$l_kd_field]):$_REQUEST[$l_kd_field])."'":"null").",";

					}
		}
		$l_arr_query[$l_table]=substr($l_arr_query[$l_table],0,-1).");";
	}
	
	foreach ($l_arr_table as $l_table=>$l_arr_field){
		//showquery($l_arr_query[$l_table]);
		if(!pg_query("".$l_arr_query[$l_table]."")) $l_success=0;
		//LOG nya pake Generate Log
		//showquery("select * from skeleton.tbldb_table_detail where fk_db_table='".$l_table."' and is_pk=true");
		$lrow_db_table_detail=pg_fetch_array(pg_query("select * from skeleton.tbldb_table_detail where fk_db_table='".$l_table."' and is_pk=true"));
		//showquery("select * from skeleton.tbldb_table_detail where fk_db_table='".$l_table."' and is_pk=true");
		$l_pk=$lrow_db_table_detail["kd_field"];
		//echo $lrow_db_table_detail["default_value_type"]."aaa";
		if ($lrow_db_table_detail["default_value_type"]=="") {
			$l_arr_id[$l_table]=$_REQUEST[array_search($l_pk,$l_arr_field)];
		}
		else {
			$l_arr_id[$l_table]=get_last_id($l_table,$l_pk);
		}
		
		if ($pk_id!="") $l_arr_id[$l_table]=$pk_id;
		//echo $lrow_db_table_detail["default_value_nextserial"].'sdfsd';
		//showquery(generate_log($l_table,$l_arr_id[$l_table],"IA",$l_pk));
		if (function_exists('query_additional')) {
			query_additional($l_arr_id[$l_table]);
	
		} 		
		if (!generate_log($l_table,$l_arr_id[$l_table],"IA",$l_pk)) $l_success=0;
		$pk_id=$l_arr_id[$l_table];
	}

//strisi -- detail table
	$lrs_detail=pg_query("select * from skeleton.tblmodule_detail where fk_module='".convert_sql($kd_module)."'");
	while ($lrow_detail=pg_fetch_array($lrs_detail)){
		$lrs_tabel=pg_query("select no_urut,save_to_table,save_to_field,is_numeric from skeleton.tblmodule_detail_fields
							where fk_module_detail='".$lrow_detail["pk_id"]."' and (is_read_only is false or  save_to_field !='') 
							order by no_urut");
/*		showquery("select no_urut,save_to_table,save_to_field from skeleton.tblmodule_detail_fields
							where fk_module_detail='".$lrow_detail["pk_id"]."' and (is_read_only is false or ( save_to_field !='')) 
							order by no_urut");
*/		while($lrow_table=pg_fetch_array($lrs_tabel)){
			$l_arr_detail[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["no_urut"]]=$lrow_table["save_to_field"];
			$l_numeric[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["no_urut"]]["is_numeric"]=$lrow_table["is_numeric"];		

		}
	}
	if (is_array($l_arr_detail)) {
		foreach ($l_arr_detail as $l_kd_module_detail=>$l_arr_table){
			//print_r($l_arr_detail);
			foreach ($l_arr_table as $l_table=>$l_arr_field){
				$parent_table=get_rec("skeleton.tbldb_table","parent_table","is_table_detail is true and kd_table='".$l_table."'"); //mengambil parent tabel dari table detail
				$l_fk_field=get_rec("skeleton.tbldb_table_detail","kd_field","is_foreign_key is true and fk_db_table='".$l_table."' and foreign_table='".$parent_table."'");
				//showquery(" select * from skeleton.tbldb_table_detail where is_foreign_key is true and fk_db_table='".$l_table."' and foreign_table='".str_replace("_detail","",$l_table)."'");
				$l_arr_query[$l_kd_module_detail][$l_table]="insert into ".$l_table." (".$l_fk_field.",".join(",",$l_arr_table[$l_table]).") values (";
			}
		}
		foreach ($l_arr_detail as $l_kd_module_detail=>$l_arr_table){
			$l_arr_row = split(chr(191),$_REQUEST["strisi_".$l_kd_module_detail]);
			for ($i=0; $i<count($l_arr_row)-1; $i++){
				$l_arr_col=split(chr(187),$l_arr_row[$i]);
				foreach ($l_arr_table as $l_table=>$l_arr_field){
					$parent_table=get_rec("skeleton.tbldb_table","parent_table","is_table_detail is true and kd_table='".$l_table."'");
					$l_fk_field=get_rec("skeleton.tbldb_table_detail","kd_field","is_foreign_key is true and fk_db_table='".$l_table."' and foreign_table='".$parent_table."'"); //aneh ambil foreign tablenya lbh baik darimana?
					//showquery ("select kd_field from skeleton.tbldb_table_detail where is_foreign_key is true and fk_db_table='".$l_table."' and foreign_table='".$parent_table."'");
					$l_temp=$l_arr_id[$parent_table];
					$l_arr_query[$l_kd_module_detail][$l_table].=(($l_temp)?"'".$l_temp."'":"null").",";
					//if($l_arr_col[3]!="")					
					foreach ($l_arr_field as $l_no_urut=>$l_save_to_field) {	
						//if ($l_arr_col[$l_no_urut]=='0')echo '3'.$l_arr_col[$l_no_urut].'<br>';
						
						if($l_numeric[$l_kd_module_detail][$l_table][$l_no_urut]["is_numeric"]=='t') {
							$l_arr_col[$l_no_urut]  = str_replace(',','',$l_arr_col[$l_no_urut]);
							$l_arr_col[$l_no_urut]  = str_replace('.00','',$l_arr_col[$l_no_urut]);
						}
						$l_arr_query[$l_kd_module_detail][$l_table].=(($l_arr_col[$l_no_urut] || $l_arr_col[$l_no_urut]=='0')?"'".trim(str_replace('"', "'",$l_arr_col[$l_no_urut]))."'":"null").",";	
						//echo $l_arr_query[$l_kd_module_detail][$l_table];

					}
					$l_arr_query[$l_kd_module_detail][$l_table]=substr($l_arr_query[$l_kd_module_detail][$l_table],0,-1).");";
				}
				
				if($l_temp!=""){
					//showquery($l_arr_query[$l_kd_module_detail][$l_table]);
					if(!pg_query($l_arr_query[$l_kd_module_detail][$l_table])) $l_success=0;
					$l_arr_query[$l_kd_module_detail][$l_table]=substr($l_arr_query[$l_kd_module_detail][$l_table],0,0)."insert into ".$l_table." (".$l_fk_field.",".join(",",$l_arr_table[$l_table]).") values (";
				}	
			}
		}
		$l_pk=get_rec("skeleton.tbldb_table_detail","kd_field","fk_db_table='".$parent_table."' and is_pk=true");
		if($is_add!='f')if (!generate_log($parent_table,$pk_id,"detail",$l_pk,'t')) $l_success=0;

	}
	

	if (function_exists('save_additional')) {
		//echo "Save Additional functions are available.<br />\n";
		save_additional();
	} 
	
	//if($id_menu=="20220100000005")$l_success=0;
	//echo $id_menu;
	//echo $nama_menu;
	//echo $_REQUEST["no_id_link"];
	 //if($id_menu=="20170800000056")$l_success=0;
	//if($id_menu=="20170800000054")$l_success=0;
	//echo $l_success;
	//if($_SESSION["username"]=='superuser')$l_success=0;
	$l_success=0;
	if ($l_success==1){
		if($_REQUEST["no_image"]){// untuk insert foto ktp ocr
			//if(!pg_query("update tblcustomer set pic_id ='".$_REQUEST["no_id"].".png' where no_id='".$_REQUEST["no_id"]."'")) $l_success=0;
			if(!@copy($upload_path_pic.$_REQUEST["no_image"].'.png',$upload_path_pic.$_REQUEST["no_id"].".png")){
				
			}
		}
		
		
		$lrs_kosong=pg_query("select * from skeleton.tblmodule inner join skeleton.tblmodule_fields on skeleton.tblmodule.pk_id=fk_module where fk_menu='".$id_menu."' and is_add is true");
		
		while($lrow_kosong=pg_fetch_array($lrs_kosong)){
			$_REQUEST[str_replace("[]","",$lrow_kosong["kd_field"])]="";
			$_REQUEST[str_replace("[]","",$lrow_kosong["kd_field"]."1")]="";
			$_REQUEST[str_replace("[]","",$lrow_kosong["kd_field"]."2")]="";
		}

		$lrs_detail_kosong=pg_query("select * from skeleton.tblmodule_detail where fk_module='".$kd_module."'");
		//showquery("select * from skeleton.tblmodule_detail where fk_module='".$kd_module."'");
		while($lrow_detail_kosong=pg_fetch_array($lrs_detail_kosong)){
			$_REQUEST["strisi_".$lrow_detail_kosong["kd_module_detail"]]="";	
		}
		
		$strisi1="";
		$strmsg=$nm_menu." Tersimpan.<br>";
		//if($nama_menu!='New')$j_action= "lInputClose=getObjInputClose();lInputClose.close()";\
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		pg_query("COMMIT");
		if($id_menu=="20170800000053" || $id_menu=="20170800000054"||$id_menu=="20170800000056"){
			//echo $aaaaa;
			$print=get_rec("skeleton.tblmenu","nm_file_module_other","fk_parent='".$id_menu."' and nama_menu like '%Kwitansi%'");
			$j_action="window.location='".$print."?pstatus=edit&id_edit=".$pk_id."'";
		}
		
		
	}else{
		$strmsg.="Error :<br>".$nm_menu." Gagal Tersimpan.<br>";
		pg_query("ROLLBACK");
	}
	
}

function get_data_module(){
	global $kd_module,$j_action,$nm_tabs,$is_add,$strmsg;
	
	
	//$is_add=get_rec("skeleton.tblmodule_detail","is_add","fk_module='".$kd_module."'");
	//query untuk memunculkan tab 
	$nm_tabs=get_rec("skeleton.tblmodule_tabs","kd_tabs","fk_module='".$kd_module."'","no_urut_tabs limit 1");
	$lrow_first_field=pg_fetch_array(pg_query("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' order by no_urut_add limit 1"));
	//showquery("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' order by no_urut_add limit 1");
	$j_action.="document.form1.".$lrow_first_field["kd_field"].".focus();";
	

}
?>