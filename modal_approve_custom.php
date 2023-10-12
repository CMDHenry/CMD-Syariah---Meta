<?php
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

$id_edit=trim($_REQUEST["id_edit"]);
$id_menu=trim($_REQUEST["id_menu"]);
$id_menu=trim($_REQUEST["id_menu"]);
$pstatus=trim($_REQUEST["pstatus"]);
//echo $p_status;

/*if(file_exists($path_site."includes/modal_approve_".$id_menu.".inc.php"))
	include $path_site."includes/modal_approve_".$id_menu.".inc.php";
*/
//echo $id_edit;
get_data_menu($id_menu);

/*$lrs_status=pg_fetch_array(pg_query("select * from skeleton.tblmodule_approval where fk_module='".$kd_module."'"));
$status_approval=trim($_REQUEST[$lrs_status["save_field_status"]]);
$alasan_status=trim($_REQUEST[$lrs_status["save_field_alasan"]]);
$alasan_batal=trim($_REQUEST[$lrs_status["save_field_alasan_batal"]]);*/

$alasan_approve=trim($_REQUEST["alasan_approve"]);
$alasan_batal=trim($_REQUEST["alasan_batal"]);

get_data_module();

if (function_exists('request_additional')) {
	request_additional();	
}


if($_REQUEST["pstatus"]){
	get_data();
	get_data_header();
	if (function_exists('get_additional')) {
		//echo "Additional functions are available.<br />\n";
		get_additional();
	} 
}

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
<script language='javascript' src="js/table_v2.js.php"></script>
<script language='javascript' src="js/input_format_number.js.php"></script>
<script language='javascript' src="js/tab.js.php?kd_module=<?=$kd_module?>&id_menu=<?=$id_menu?>"></script>
<script language='javascript'>
<?
_module_create_js();
?>
function fSave(){
	lCanSubmit=true
	document.form1.status.value='Save';
	<? //_module_create_get_isi()?>
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
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	}else if($j_action){
		echo $j_action;
	}else{
		if($pstatus=="approve"){
			echo "document.form1.alasan_approve.focus();";
		}elseif($pstatus=="batal"){
			echo "document.form1.alasan_batal.focus();";
		}
	}
?>
}
</script>
<body onLoad="fLoad()" bgcolor="#fafafa">
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="form1" enctype="multipart/form-data">
<input type="hidden" name="id_menu" value="<?=$id_menu?>">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="status">
<input type="hidden" name="<?=$lrs_status["save_field_status"]?>">
<input type="hidden" name="pstatus" value="<?=$pstatus?>">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<tr><td class="judul_menu" align="center"><?=strtoupper($nm_menu)?></td></tr>
			</table>
		</td>
	</tr>
    <table cellpadding="0" cellspacing="0" border="0" width="100%">
     <tr>
      	<td class="border">
<!-- content begin -->
			<? _module_create_header_approval("edit",false)?>
	  	</td>
	</tr>
    <tr>
      	<td class="border">
			<? /*if(get_rec("skeleton.tblmodule","is_tab","fk_menu='".$id_menu."'")!=t)*/ _module_create_detail_view('false')?>
            
			<? $fk_module=get_rec("skeleton.tblmodule","pk_id","fk_menu='".$id_menu."'");
			if(!pg_num_rows(pg_query("select * from skeleton.tblmodule_fields where fk_module='".$fk_module."' and fk_data_type='table_detail'"))){			
			_module_create_detail_view('true');			
			}?>
            
<!-- end content begin -->
	  	</td>
    </tr>
    <tr>
      	<td class="border">
            <table cellpadding="0" cellspacing="1" border="0" width="100%">
			<?
                if (function_exists('html_additional')) {
                    html_additional();
                }
            ?>
			</table>
		</td>
	</tr>
    
    </table>
    <tr>
      	<td class="border">
<!-- content begin -->
		<table cellpadding="0" cellspacing="0" border="0" width="100%">
			<? if($pstatus=="approve"){ ?>
            <tr bgcolor="efefef">            
						<td width="20%" class="fontColor" style="padding:0 5 0 5" valign="top">Alasan Approve/Reject</td>
						<td width="80%" style="padding:0 5 0 5">
							<textarea name="alasan_approve" rows="5" cols="80"  onKeyUp="fNextFocus(event, document.form1.btnSubmit)"></textarea>
						</td>
                        
             </tr>
              <? }else if($pstatus=="batal"){ ?>
              <tr bgcolor="efefef">            
						<td width="20%" class="fontColor" style="padding:0 5 0 5" valign="top">Alasan Batal</td>
						<td width="80%" style="padding:0 5 0 5">
							<textarea name="alasan_batal" rows="5" cols="80"  onKeyUp="fNextFocus(event, document.form1.btnSubmit)"></textarea>
						</td>
                        
             </tr>
             <? } ?>
          </table>
<!-- end content begin -->
	  	</td>
    </tr>
</table>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" align="center">
	<tr height="20">
    	<td height="25" align="center" bgcolor="#D0E4FF">
<? if(strstr(strtoupper($nm_menu),"EMAIL")){ 
?>        
			  <input class="groove_button" type='button' name="btnsubmit" value='Kirim' onClick="fSave()">

<? }else{?>
			  <input class="groove_button" type='button' name="btnsubmit" value='Simpan' onClick="fSave()">
<? }?>              
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?
function cek_error(){
	global $strmsg,$j_action,$id_menu,$kd_module,$id_edit,$alasan_status,$alasan_batal,$pstatus;
	if (function_exists('cek_error_module')) {
		//cek_error_module();
		//echo "Cek Error Module functions are available.<br />\n";
		$strmsg.=cek_error_module();
	}
	if($pstatus=="approve"){
		$status_tr='Approve';
		if($alasan_status=="" || $alasan_status==null){
			$strmsg.="Keterangan Approve Harus Diisi.<br>";
			if(!$j_action) $j_action="document.form1.alasan_status.focus()";	
		} else $j_action="";
	}
	if($pstatus=="batal"){
		$status_tr='Batal';
		if($alasan_batal=="" || $alasan_batal==null){
			$strmsg.="Keterangan Batal Harus Diisi.<br>";
			if(!$j_action) $j_action="document.form1.alasan_batal.focus()";	
		} else $j_action="";
	}
	
	$lrs_set_status=pg_fetch_array(pg_query("select * from skeleton.tblmodule_approval where fk_module='".$kd_module."'"));
	$table_pk = get_rec("skeleton.tbldb_table_detail","kd_field","is_pk=true and fk_db_table='".$lrs_set_status["save_to_table"]."'");
	
	//showquery("select ".$lrs_set_status["save_field_status"]."  from ".$lrs_set_status["save_to_table"]." where $table_pk='".$id_edit."' and ".$lrs_set_status["save_field_status"]." ='".$status_tr."' for update");
	
	
	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}


function get_data(){
	global $kd_module,$id_edit,$alasan_status,$status_approval,$pstatus;
	

}

function get_data_header(){
	global $id_edit,$kd_module,$id_menu;

	$lrs_tabel=pg_query("select kd_field,save_to_table,save_to_field,save_to_field_2,fk_data_type,type_list,is_multiple,is_numeric from skeleton.tblmodule_fields
						where fk_module='".$kd_module."'
						order by no_urut_edit, save_to_table,save_to_field");
/*	showquery("select kd_field,save_to_table,save_to_field,save_to_field_2,fk_data_type,type_list,is_multiple from skeleton.tblmodule_fields
						where fk_module='".$kd_module."'
						order by no_urut_edit, save_to_table,save_to_field");
*/						
	while($lrow_table=pg_fetch_array($lrs_tabel)){
		if($lrow_table["save_to_table"]=="") {
			$lrow_table["save_to_table"]=$save_table_old;
		}
		
		$l_arr_table[$lrow_table["save_to_table"]][$lrow_table["kd_field"]]["save_to_field"]=$lrow_table["save_to_field"].(($lrow_table["save_to_field_2"])?",".$lrow_table["save_to_field_2"]:"");
		$l_arr_table[$lrow_table["save_to_table"]][$lrow_table["kd_field"]]["fk_data_type"]=(($lrow_table["fk_data_type"]=="list")?$lrow_table["type_list"]:$lrow_table["fk_data_type"]);
		$l_arr_table[$lrow_table["save_to_table"]][$lrow_table["kd_field"]]["is_multiple"]=$lrow_table["is_multiple"];
		$l_arr_table[$lrow_table["save_to_table"]][$lrow_table["kd_field"]]["is_numeric"]=$lrow_table["is_numeric"];
		
		$save_table_old=$lrow_table["save_to_table"];
	}
	
	$lrs_tabel=pg_query("select distinct save_to_table from skeleton.tblmodule_fields
						where fk_module='".$kd_module."'");
	while($lrow_table=pg_fetch_array($lrs_tabel)){
		$lrs_relation=pg_query("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' and value_type='reference' and fk_data_type='readonly'");
		while ($lrow_relation=pg_fetch_array($lrs_relation)) {
			if ($lrow_relation["reference_table_name"]=="sql_query") {
				$lrow_query=pg_fetch_array(pg_query("select * from skeleton.tblsql_query where kd_sql_query='".$lrow_relation["sql_query"]."'"));
				$l_table=$lrow_query["sql_query"];
			}  else {
				$l_table=$lrow_relation["reference_table_name"];
			}
			$l_arr_relation[$lrow_table["save_to_table"]][$l_table]["field"].=$lrow_relation["reference_field_name"].",";
			$l_arr_relation[$lrow_table["save_to_table"]][$l_table]["expression"]=$lrow_relation["reference_expression"];
		}
	}
	
	$lrs_menu=pg_query("select list_sql from skeleton.tblmenu
				where pk_id='".$id_menu."'
				");
	$lrow_menu=pg_fetch_array($lrs_menu);
	$list_sql=$lrow_menu["list_sql"];

	foreach ($l_arr_table as $l_table=>$l_arr_field){
		if (trim($l_table)) {
			
			$l_pk=get_rec("skeleton.tbldb_table_detail","kd_field","fk_db_table='".$l_table."' and is_pk=true");
			$l_arr_query[$l_table]="select * from ".$l_table;
			$l_counter_relation=1;
			if (is_array($l_arr_relation)) {
				foreach ($l_arr_relation[$l_table] as $l_join_table=>$l_arr_content) {
					$l_arr_query[$l_table].=" left join ".$l_join_table." on ".$l_arr_content["expression"];
					$l_counter_relation++;
				}
			}
			
			$l_arr_query[$l_table]= "select * from ( ".$list_sql." )as tblmain";// modif baru
			$l_arr_query[$l_table].= " where ".$l_pk."='".convert_sql($id_edit)."'";
			$l_arr_query[$l_table]=str_replace("[","(",$l_arr_query[$l_table]);
			$l_arr_query[$l_table]=str_replace("]",")",$l_arr_query[$l_table]);
			$lrow=pg_fetch_array(pg_query($l_arr_query[$l_table]));
			
			foreach ($l_arr_field as $l_kd_field=>$l_save_to_field) {
				if(strstr($l_save_to_field["save_to_field"],",")){
					$save_to_field=explode(",",$l_save_to_field["save_to_field"]);
					$l_field=$save_to_field[0];
				}else{
					$l_field=(($l_save_to_field["save_to_field"]=="")?$l_kd_field:$l_save_to_field["save_to_field"]);
				}
				$l_kd_field=str_replace("[]","",$l_kd_field);				
				if ($l_save_to_field["fk_data_type"]=="list_db" && $l_save_to_field["is_multiple"]=="f") {
					
					$lrow_field=pg_fetch_array(pg_query("select * from skeleton.tblmodule_fields where fk_module='".convert_sql($kd_module)."' and kd_field='".convert_sql($l_kd_field)."'"));
					if(strpos ($lrow_field["list_sql"],"where")>=1){
						if ($lrow_reference=pg_fetch_array(pg_query($lrow_field["list_sql"]." and ".$lrow_field["list_field_value"]."='".$lrow[$l_field]."'"))){
							$_REQUEST[$l_kd_field]=$lrow_reference[$lrow_field["list_field_text"]];
						}

					} else{
						if ($lrow_reference=pg_fetch_array(pg_query($lrow_field["list_sql"]." where ".$lrow_field["list_field_value"]."='".$lrow[$l_field]."'"))){
							$_REQUEST[$l_kd_field]=$lrow_reference[$lrow_field["list_field_text"]];
						}
					}
				} elseif ($l_save_to_field["fk_data_type"]=="list_manual" && $l_save_to_field["is_multiple"]=="f") {
					$lrow_field=pg_fetch_array(pg_query("select * from skeleton.tblmodule_fields where fk_module='".convert_sql($kd_module)."' and kd_field='".convert_sql($l_kd_field)."'"));
					//showquery("select * from skeleton.tblmodule_fields where fk_module='".convert_sql($kd_module)."' and kd_field='".convert_sql($l_kd_field)."'");
					$l_arr_list_manual_value=split(",",$lrow_field["list_manual_value"]);
					//print_r($l_arr_list_manual_value);
					
					$l_arr_list_manual_text=split(",",$lrow_field["list_manual_text"]);
					for ($l_index=0;$l_index<count($l_arr_list_manual_value);$l_index++){
						$l_arr_list[$l_arr_list_manual_value[$l_index]]=$l_arr_list_manual_text[$l_index];
					}
					$_REQUEST[$l_kd_field]=$l_arr_list[$lrow[$l_field]];
					
				} else if ($l_save_to_field["fk_data_type"]=="range_date"){
					$lvalue=explode(",",$l_save_to_field["save_to_field"]);	
					$_REQUEST[$l_kd_field]=format_data_view($lrow[$lvalue[0]],"date","d/m/Y")." - ".format_data_view($lrow[$lvalue[1]],"date","d/m/Y");
				} else if($l_save_to_field["fk_data_type"]=="range_value"){ 
					$lrange_value=explode(",",$l_save_to_field["save_to_field"]);
					$_REQUEST[$l_kd_field]=format_data_view($lrow[$lrange_value[0]],"d/m/Y")." - ".format_data_view($lrow[$lrange_value[1]],"d/m/Y");	
				} else {
					if($l_save_to_field["is_numeric"]=="t")$l_save_to_field["fk_data_type"]="numeric";
					if ($lrow[$l_field] ||$lrow[$l_field]==0) $_REQUEST[$l_kd_field]=format_data_view($lrow[$l_field],$l_save_to_field["fk_data_type"],"d/m/Y");
					
				}
			}
		}
	}
}

function get_data_module(){
	global $kd_module,$j_action,$nm_tabs;
	
	//query untuk memunculkan tab 
	$lrow_tab_switch=pg_fetch_array(pg_query("select * from skeleton.tblmodule_tabs where fk_module='".$kd_module."' order by no_urut_tabs limit 1"));
	$nm_tabs=$lrow_tab_switch["kd_tabs"];
	//$lrow_first_field=pg_fetch_array(pg_query("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' order by no_urut_edit limit 1"));
	//$j_action="document.form1.".$lrow_first_field["kd_field"].".focus();";
	
	//$j_action="document.form1.".get_rec("skeleton.tblmodule_approval","save_field_alasan","fk_module='".$kd_module."'").".focus();";
	//showquery("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' order by no_urut_edit limit 1");
	//echo $lrow_first_field["kd_field"];
}
?>