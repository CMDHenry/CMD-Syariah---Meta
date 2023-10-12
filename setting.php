<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/db_utility.inc.php';
require 'classes/recordset.class.php';
//require 'requires/text.inc.php';
require 'requires/numeric.inc.php';
get_data_module();
?>
<html>
<head>
    <title>.:<?=$_SESSION["application"]?> :.</title>
    <link href="js/cwcalendar.css.php" rel="stylesheet" type="text/css">
    <link href="text.css.php" rel="stylesheet" type="text/css">
	<link href="menu.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src="js/alert.js.php"></script>
<script type="text/JavaScript" src="js/misc.js.php"></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src="js/dd_menu.js.php"></script>
<script language='javascript' src='js/openwindow.js.php'></script>
<script language='javascript'>
var strOrderBy=""
var strOrderType=""
var intPage=1

function fModal(pType,pID){
	l_obj_function=function(){
		document.form1.submit()
	}
	switch (pType){	
	<?=_module_generate_modal($pk_id_module)?>
	}
	if(pType=="edit"){
		show_modal('modal_edit.php?id_menu=<?=$module?>&pstatus=edit&id_edit='+pID,'status:no;help:no;dialogWidth:800px;dialogHeight:300px',l_obj_function)
	}
}

function fView(){
	with(document.form1){
		fGetData('page='+intPage+'&strorderby='+strOrderBy+'&strordertype='+strOrderType)
	}
}

function fResize(){
	document.getElementById("divContent").style.height=window.innerHeight-150
	//document.getElementById("divContent2").style.height=window.innerHeight-200
}

</script>
<body bgcolor="#f3f3f3" onResize="fResize()">
<?
include_once("includes/menu.inc.php");
?>
<form name="form1" method="post">
<input type="hidden" name="order_by" value="<?=$order_by?>">
<input type="hidden" name="order_type" value="<?=$order_type?>">
<table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
	<tr background="images/submenu_background.jpg" height="37">
		<td width="20"></td>
		<td class="selectMenu" colspan="2"><?=strtoupper($row_menu["root_menu"])?></td>
	</tr>
	<tr>
		<td width="20"></td>
		<td valign="top">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" height="100px">
                <tr height="20">
                    <td>
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" class="border">
                            <tr bgcolor="#D0E4FF">
                                <td>&nbsp;
                                
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#f8f8f8" class="border">
                        <div  id="divContent" style='width:100%;height:100%;overflow:auto'>
                        <?
                           
							$lrow=pg_fetch_array(pg_query("select * from skeleton.tblmenu where pk_id='".$module."'"));
							$lrs=pg_query("select * from (
										".$lrow["list_sql"]."
									) as tblmain");
						?>
                        <table cellpadding="0" cellspacing="1" border="0" width="100%">
                            <tr bgcolor="#c8c8c8" class="header" height="15">
                                <td colspan="2"><span style="padding:0 5 0 5"><strong>Settings</strong></span></td>
                            </tr>
                            
                            <? 
							while($lrow=pg_fetch_array($lrs)){
							$lrs_nm_field=pg_query("select * from skeleton.tblmodule_fields 
							inner join skeleton.tblmodule on skeleton.tblmodule_fields.fk_module=skeleton.tblmodule.pk_id where fk_menu='".$module."' and is_view=true order by no_urut_edit" );
/*							showquery("select * from skeleton.tblmodule_fields 
							inner join skeleton.tblmodule on skeleton.tblmodule_fields.fk_module=skeleton.tblmodule.pk_id where fk_menu='".$module."' and is_view=true order by no_urut_edit");
*/								while($lrow_nm_field=pg_fetch_array($lrs_nm_field)){									
								
                        	?>
                            <tr bgcolor="#e0e0e0" height="15">
                            	
                                <td width="30%" style="padding:0 5 0 5"><?=$lrow_nm_field["nm_field"]?></td>
                                <? if($lrow_nm_field["fk_data_type"]=="numeric"){?>
                                <td style="padding:0 5 0 5"><?=convert_money("",$lrow[$lrow_nm_field["save_to_field"]],0)?></td>
                                <? }else if($lrow_nm_field["fk_data_type"]=="date"){?>
                                <td style="padding:0 5 0 5"><?=date("d/m/Y",strtotime($lrow[$lrow_nm_field["save_to_field"]]))?></td>                       
                                <? } else if($lrow_nm_field["fk_data_type"]=="checkbox"){?>	
                                <td style="padding:0 5 0 5" >
                                    <img src="./images/<?=(($lrow[$lrow_nm_field["save_to_field"]]=='t')?"true":"false")?>.gif">
                                </td>
                                <? }else{?>
                                <td style="padding:0 5 0 5"><?=$lrow[$lrow_nm_field["save_to_field"]]?></td>
                                <? }?>
                            </tr>
                            <? }
							} ?>
                        </table>
                        </div>
                    </td>
                </tr>
                <tr height="25">
                    <td bgcolor="#D0E4FF" class="border"> 
                    <? 		$lrs_field_pk_id=pg_query("select * from skeleton.tblmodule_fields inner join 
							skeleton.tblmodule on skeleton.tblmodule_fields.fk_module=skeleton.tblmodule.pk_id  
							inner join (
									select * from skeleton.tbldb_table_detail where is_pk='t'
								) as tbldb_table_detail on fk_db_table=save_to_table and tbldb_table_detail.kd_field=save_to_field
							where fk_menu='".$module."' order by no_urut_edit");
							
							
							$lrow_field_pk_id=pg_fetch_array($lrs_field_pk_id);
							$lrow=pg_fetch_array(pg_query("select * from skeleton.tblmenu where pk_id='".$module."'"));
							$lrs=pg_query("select * from (
										".$lrow["list_sql"]."
									) as tblmain");
							$lrow_pk_id=pg_fetch_array($lrs)
							
					?>
                  
                        <input type="button" value=" Edit " class="groove_button" onClick="fModal('edit','<?=$lrow_pk_id[$lrow_field_pk_id["save_to_field"]]?>')">
						 <? create_button_non_item()?>                        
                    </td>
                </tr>
            </table>
		</td>
		<td width="20"></td>
	</tr>
</table>
</form>
</body>
</html>

<?
function get_data_module(){
	global $module,$row_menu,$parent_root,$pk_id_module;
	
	$module=$_REQUEST["module"];	//kd_menu/fk_menu
	$row_menu=pg_fetch_array(pg_query("select * from skeleton.tblmenu where pk_id='".$module."'"));
	if (!check_right($row_menu["kd_menu"])){
		header("location:error_access.php");
	}
	$parent_root=get_rec("skeleton.tblmenu","pk_id","kd_menu='".substr($row_menu["kd_menu"],0,2)."'"); //Menu Tab Master
	$pk_id_module=get_rec("skeleton.tblmodule","pk_id","fk_menu='".$module."'"); //pk_id tblmodule/fk_module_fields
}

function _module_generate_modal(){
	global $module,$pk_id_module;

	$lrs=pg_query("select * from skeleton.tblmenu where fk_parent='".$module."' and is_action=true and type_action='module_action' and per_item='f'");	
	while ($lrow=pg_fetch_array($lrs)) {
		$lrow_menu=pg_fetch_array(pg_query("select * from skeleton.tblmenu where fk_parent='".$module."' and type_action='module_action' and jenis_module='other' and kd_menu='".$lrow["kd_menu"]."'"));
	//showquery("select * from skeleton.tblmenu where fk_parent='".$module."' and type_action='module_action' and jenis_module='other' and kd_menu='".$lrow["kd_menu"]."'");
		$l_arr_jenis_module=array("add"=>"modal_add.php","edit"=>"modal_edit.php","view"=>"modal_view.php","batal"=>"modal_approve.php","approve"=>"modal_approve.php","other"=>$lrow_menu["nm_file_module_other"]);
		
		$l_arr_jenis_module_param["edit"]="&pstatus=edit&id_edit='+pID+'";
		$l_arr_jenis_module_param["view"]="&pstatus=view&id_view='+pID+'";
		$l_arr_jenis_module_param["batal"]="&pstatus=batal&id_edit='+pID+'";
		$l_arr_jenis_module_param["approve"]="&pstatus=approve&id_edit='+pID+'";
		//$l_arr_jenis_module_param["other"]="&pstatus=edit&id_edit='+pID+'";
		//$l_arr_jenis_module_param["other"]="&pstatus=edit&id_edit='+pID+'";
?>
		case "<?=$lrow["kd_menu"]?>":
			show_modal('<?=$l_arr_jenis_module[$lrow["jenis_module"]]?>?id_menu=<?=$module.$l_arr_jenis_module_param[$lrow["jenis_module"]]?>&kd_menu_button=<?=$lrow["kd_menu"]?>','status:no;help:no;dialogwidth:900px;dialogheight:575px;',l_obj_function)
			break;
<?		
	}

	$lrs=pg_query("select * from skeleton.tblmenu where fk_parent='".$module."' and is_action=true and type_action='module_action' and per_item='t'");	
	while ($lrow=pg_fetch_array($lrs)) {
		$lrow_menu=pg_fetch_array(pg_query("select * from skeleton.tblmenu where fk_parent='".$module."' and type_action='module_action' and jenis_module='other' and kd_menu='".$lrow["kd_menu"]."'"));
	//showquery("select * from skeleton.tblmenu where fk_parent='".$module."' and type_action='module_action' and jenis_module='other'");
		$l_arr_jenis_module=array("add"=>"modal_add.php","edit"=>"modal_edit.php","view"=>"modal_view.php","batal"=>"modal_approve.php","approve"=>"modal_approve.php","other"=>$lrow_menu["nm_file_module_other"]);
		$l_arr_jenis_module_param["edit"]="&pstatus=edit&id_edit='+pID+'";
		$l_arr_jenis_module_param["view"]="&pstatus=view&id_edit='+pID+'";
		$l_arr_jenis_module_param["batal"]="&pstatus=batal&id_edit='+pID+'";
		$l_arr_jenis_module_param["approve"]="&pstatus=approve&id_view='+pID+'";
		$l_arr_jenis_module_param["other"]="&pstatus=edit&id_edit='+pID+'";
?>
		case "<?=$lrow["kd_menu"]?>":
			show_modal('<?=$l_arr_jenis_module[$lrow["jenis_module"]]?>?id_menu=<?=$module?>&pstatus=<?=($lrow["jenis_module"]!="other")?$lrow["jenis_module"]:"edit"?>&kd_menu_button=<?=$lrow["kd_menu"]?>&<?=($lrow["jenis_module"]!="view")?"id_edit":"id_view"?>='+pID,'status:no;help:no;dialogwidth:900px;dialogheight:575px;',l_obj_function)
			break;
<?		
	}
}

function _module_generate_search(){
	global $pk_id_module;

	$l_counter=1;
	
	$l_arr_param["list"]["list_db"]["list_sql"]="list_sql";
	$l_arr_param["list"]["list_db"]["list_value"]="list_field_value";
	$l_arr_param["list"]["list_db"]["list_text"]="list_field_text";
	$l_arr_param["list"]["list_manual"]["list_text"]="list_manual_text";
	$l_arr_param["list"]["list_manual"]["list_value"]="list_manual_value";
	$l_arr_param["list"]["get"]["fk_module"]="fk_get_module";
	
	$lrs=pg_query("select * from skeleton.tblmodule_fields_attribute where fk_module_fields='".$pk_id_module."'");
	while ($lrow=pg_fetch_array($lrs)) {
		$l_arr_attrib[$lrow["nm_attribute"]]=$lrow["value"];
	}
	
	
	$l_arr_add_param["decimal"]=0;
	
	$lrs=pg_query("select * from skeleton.tblmodule_fields where is_search='t' and fk_module='".$pk_id_module."' order by no_urut_edit");
	//showquery("select * from skeleton.tblmodule_fields where is_search='t' and fk_module='".$pk_id_module."' order by no_urut_edit");
	while ($lrow=pg_fetch_array($lrs)) {
		if(is_array($l_arr_param[$lrow["fk_data_type"]][$lrow["type_list"]])){
			foreach($l_arr_param[$lrow["fk_data_type"]][$lrow["type_list"]] as $l_key=>$l_value) {
				$l_arr_add_param[$l_key]=$lrow[$l_value];
			}
		}
		//echo $l_counter;
		$_REQUEST["view_kd_field"][$l_counter]=$lrow["kd_field"];
		if (($l_counter%2)!=0) {
?>
                <tr>
                    <td width="20%" style="padding:0 5 0 5" bgcolor="e0e0e0"><?=$lrow["nm_field"]?></td>
                    <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                    	<?=generate_input($lrow["value_type"],$lrow["fk_data_type"],$lrow["kd_field"],$lrow["nm_field"],"",$l_arr_attrib,$l_arr_add_param,$lrow["type_list"],$lrow["fgetnamesearch"],$lrow["fgetdatanamesearch"],"","",$lrow["is_multiple"])?>
                    </td>
<?
		} else {
?>
                    <td width="20%" style="padding:0 5 0 5" bgcolor="e0e0e0"><?=$lrow["nm_field"]?></td>
                    <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                        <?=generate_input($lrow["value_type"],$lrow["fk_data_type"],$lrow["kd_field"],$lrow["nm_field"],"",$l_arr_attrib,$l_arr_add_param,$lrow["type_list"],$lrow["fgetnamesearch"],$lrow["fgetdatanamesearch"],"","",$lrow["is_multiple"])?>
                    </td>
                </tr>
<?
		}
		$l_counter++;
	}
	if (($l_counter%2)==0) {
?>
                    <td width="20%" style="padding:0 5 0 5" bgcolor="e0e0e0"></td>
                    <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef"></td>
                </tr>
<?
	}
}

function _module_generate_url_seacrch_param() {
	global $pk_id_module;
	$l_param="";

	$lrs=pg_query("select * from skeleton.tblmodule_fields where is_search='t' and fk_module='".$pk_id_module."' order by no_urut_edit");
	//showquery("select * from skeleton.tblmodule_fields where is_search='t' and fk_module='".$pk_id_module."' order by no_urut_edit");
	while ($lrow=pg_fetch_array($lrs)) {
		if($lrow["fk_data_type"]=='range_value' || $lrow["fk_data_type"]=='range_date'){
			$l_param.="+'&".$lrow["kd_field"]."1"."='+document.form1.".$lrow["kd_field"]."1".".value";
			$l_param.="+'&".$lrow["kd_field"]."2"."='+document.form1.".$lrow["kd_field"]."2".".value";			
		} else{
			$kd_field=str_replace("[]","",$lrow["kd_field"]);
			//$kd_field=$lrow["kd_field"];
			$l_param.="+'&".$kd_field."='+document.form1.".$kd_field.".value";
		}
	}
	return $l_param;
}
function create_button_non_item(){
	global $module;

	$lrs=pg_query("select * from skeleton.tblmenu where fk_parent='".$module."' and is_action=true and type_action='module_action' and per_item='f'");	
	while ($lrow=pg_fetch_array($lrs)) {
		if(check_right($lrow["kd_menu"],false)){
?>
&nbsp;<input type="button" value=" <?=$lrow["nama_menu"]?> " class="groove_button" onClick="fModal('<?=$lrow["kd_menu"]?>')">
<?	
		}	
	}
}

?>