	<?
	create_recordset()
	?>
	<table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
		<tr height="20">
			<td>
				<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border">
					<tr bgcolor="#D0E4FF">
						<td>
							<? create_button_non_item()?>
<!--                            <input type="button" name="btndelete" value="Delete" class="groove_button" onClick="fDelete()">
-->						</td>
                       
						<td align="right">
                      		
							<? if($recordset->page>1) {?>
							<a href="#" onClick="fGoPage(1)"><<</a>
							&nbsp;&nbsp;<a href="#" onClick="fPage(-1)"><</a>&nbsp;&nbsp;
							<? }?>
							<?=$recordset->page?> of <?=$recordset->total_page?>&nbsp;&nbsp;
							<? if($recordset->page<$recordset->total_page) {?>
							<a href="#" onClick="fPage(+1)">></a>
							&nbsp;&nbsp;<a href="#" onClick="fGoPage(<?=$recordset->total_page?>)">>></a>&nbsp;&nbsp;
							<? }?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td bgcolor="#f8f8f8" class="border">
				<div id="divContentInner" style='width:100%;height:100%;overflow:auto'>
				<table cellpadding="0" cellspacing="1" border="0" width="100%">
					<tr bgcolor="#c8c8c8" class="header" height="15">
                        
						<?=create_list_title()?>
						<td width="50"> <input type="checkbox" name="select_list" id="myCheck" class="groove_checkbox" value="<?=(($select_list=="t")?"t":"f")?>" <?=(($select_list=="t")?"checked":"")?> onclick="fcheckall(this)"></td>
					</tr>
					<?
						create_list_data();
					?>
				</table>
				</div>
			</td>
		</tr>
		<tr height="20">
			<td bgcolor="#D0E4FF" class="border"  align="right" style="padding:0 20 0 5">
				&nbsp;&nbsp;&nbsp;
                Total: <span id="divSelectCount"><?=convert_html($select_count)?></span>
			</td>
		</tr>
		<tr height="20"><td>&nbsp;</td></tr>
	</table>
<?
function fascdesc($p_order){
	global $strorderby,$strordertype;
	if($strorderby==$p_order){
		if($strordertype=="asc") echo "<img src='images/asc.gif'>";
		else echo "<img src='images/desc.gif'>";
	}
}

function create_recordset(){
	global $module,$pk_id_module,$recordset,$strorderby,$strordertype,$page;
	
	$lwhere="";
	$lrs=pg_query("select * from skeleton.tblmodule_fields where is_search='t' and fk_module='".$pk_id_module."' order by no_urut_add");
	while ($lrow=pg_fetch_array($lrs)) {
		if($lrow["fk_data_type"]=='range_value'){
			if ($_REQUEST[$lrow["kd_field"]."1"]!="" && $_REQUEST[$lrow["kd_field"]."2"]=="") {
				if ($lwhere!="") $lwhere.=" and";
				$lwhere.=" (".$lrow["save_to_field"]." <= '".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]."1"]))."' and";
				$lwhere.=" ".$lrow["save_to_field_2"]." >= '".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]."1"]))."')";
			} else if ($_REQUEST[$lrow["kd_field"]."2"]!="" && $_REQUEST[$lrow["kd_field"]."1"]=="") {
				if ($lwhere!="") $lwhere.=" and";
				$lwhere.=" (".$lrow["save_to_field"]." <= '".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]."2"]))."' and";
				$lwhere.=" ".$lrow["save_to_field_2"]." >= '".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]."2"]))."')";
			} else if($_REQUEST[$lrow["kd_field"]."1"]!="" && $_REQUEST[$lrow["kd_field"]."2"]!=""){
				if ($lwhere!="") $lwhere.=" and";
				$lwhere.=" (".$lrow["save_to_field"]." >= ".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]."1"]))." and";
				$lwhere.=" ".$lrow["save_to_field"]." <= ".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]."2"]))." or";
				$lwhere.=" ".$lrow["save_to_field_2"]." <= ".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]."2"]))." and";
				$lwhere.=" ".$lrow["save_to_field_2"]." >= ".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]."1"]))." or";
				$lwhere.=" ".$lrow["save_to_field"]." <= ".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]."1"]))." and";
				$lwhere.=" ".$lrow["save_to_field_2"]." >= ".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]."2"])).")";
			}
		} else if($lrow["fk_data_type"]=='range_date'){
				if ($_REQUEST[$lrow["kd_field"]."1"]!="" && $_REQUEST[$lrow["kd_field"]."2"]=="") {
				if ($lwhere!="") $lwhere.=" and";
				$lwhere.=" (".$lrow["save_to_field"]." <= '".convert_sql(strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]."1"])))."' and";
				$lwhere.=" ".$lrow["save_to_field_2"]." >= '".convert_sql(strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]."1"])))."')";
			} else if ($_REQUEST[$lrow["kd_field"]."2"]!="" && $_REQUEST[$lrow["kd_field"]."1"]=="") {
				if ($lwhere!="") $lwhere.=" and";
				$lwhere.=" (".$lrow["save_to_field"]." <= '".convert_sql(strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]."2"])))."' and";
				$lwhere.=" ".$lrow["save_to_field_2"]." >= '".convert_sql(strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]."2"])))."')";
			} else if($_REQUEST[$lrow["kd_field"]."1"]!="" && $_REQUEST[$lrow["kd_field"]."2"]!=""){
				if ($lwhere!="") $lwhere.=" and";
				$lwhere.=" (".$lrow["save_to_field"]." >= '".convert_sql(strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]."1"])))."' and";
				$lwhere.=" ".$lrow["save_to_field"]." <= '".convert_sql(strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]."2"])))."' or";
				$lwhere.=" ".$lrow["save_to_field_2"]." <= '".convert_sql(strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]."2"])))."' and";
				$lwhere.=" ".$lrow["save_to_field_2"]." >= '".convert_sql(strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]."1"])))."' or";
				$lwhere.=" ".$lrow["save_to_field"]." <= '".convert_sql(strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]."1"])))."' and";
				$lwhere.=" ".$lrow["save_to_field_2"]." >= '".convert_sql(strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]."2"])))."')";
			}
		} else if($lrow["fk_data_type"]=='date'){
			if($_REQUEST[$lrow["kd_field"]]!=""){
				$date_search=explode("/",strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]])));
				$date_search_use=$date_search[2]."-".$date_search[0]."-".$date_search[1];
				if ($lwhere!="") $lwhere.=" and";
				$lwhere.=" upper(".$lrow["kd_field"].") like upper('%".convert_sql($date_search_use)."%')";
			}
		}else{
			if ($_REQUEST[$lrow["kd_field"]]!="") {
				if ($lwhere!="") $lwhere.=" and";
				$lwhere.=" upper(".$lrow["kd_field"].") like upper('%".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]]))."%')";
			}
		}
	}	
	if ($lwhere!="") $lwhere=" where ".$lwhere;
	
	//showquery("select * from skeleton.tblmenu where kd_menu='".$module."'");
	$lrow=pg_fetch_array(pg_query("select * from skeleton.tblmenu where pk_id='".$module."'"));
	$lquote="select * from (
				".$lrow["list_sql"]."
			) as tblmain
			".$lwhere." order by ".$strorderby." ".$strordertype;	
	//showquery($lquote);	
	$recordset = new recordset("",$lquote,$page,20);
	
}

function create_list_title(){
	global $pk_id_module;
	
	$lrs=pg_query("select * from skeleton.tblmodule_fields where fk_module='".$pk_id_module."' and is_view='t' order by no_urut_edit");
	//showquery("select * from skeleton.tblmodule_fields where fk_module='".$pk_id_module."' and is_view='t' order by no_urut_edit");
	while ($lrow=pg_fetch_array($lrs)) {
?>
		<td><a href="#" onClick="fOrder('<?=$lrow["save_to_field"]?>')"><?=$lrow["nm_field"]?></a><? fascdesc($lrow["save_to_field"])?></td>
<?
	}
}

function create_list_data(){
	global $recordset,$module,$pk_id_module,$p_id;
	echo $fk_kelurahan;
	$l_index=0;
	$lrow_field_pk=pg_fetch_array(pg_query("select * from (
		select * from skeleton.tblmodule_fields where fk_module='".$pk_id_module."'
	) as tblmodule_field
	inner join (
		select * from skeleton.tbldb_table_detail where is_pk='t'
	) as tbldb_table_detail on fk_db_table=save_to_table and tbldb_table_detail.kd_field=save_to_field"));
	$lrs = $recordset->get_recordset();
	//showquery($lrs);
	while ($lrow=pg_fetch_array($lrs)){
?>
		<tr bgcolor='#e0e0e0' onmouseover="fTRColor(this,'over')" onmouseout="fTRColor(this,'out')">
		
<?
		$lrs_field=pg_query("select * from skeleton.tblmodule_fields where fk_module='".$pk_id_module."' and is_view='t' order by no_urut_edit");
	//	echo ("abc");
		//showquery("select * from skeleton.tblmodule_fields where fk_module='".$pk_id_module."' and is_view='t' order by no_urut_edit");
		
		while ($lrow_field=pg_fetch_array($lrs_field)) {
?>	
				
				<? if ($lrow_field["kd_field"]==$lrow_field_pk["kd_field"]) {?>
            		<td style="padding:0 5 0 5">
                    	<a href="#" class="blue" onClick="fModal('view','<?=$lrow[$lrow_field["save_to_field"]]?>')"><?=$lrow[$lrow_field["save_to_field"]]?></a>
                    </td>
              	<? } else if($lrow_field["fk_data_type"]=="date" || $lrow_field["fk_data_type"]=="timestamp"){ ?>
					<td style="padding:0 5 0 5" align="center">
<?=(($lrow[$lrow_field["save_to_field"]]=="" || $lrow[$lrow_field["save_to_field"]]==NULL)?"":date("d/m/Y",strtotime($lrow[$lrow_field["save_to_field"]])))?>
                    </td>	
				<? } else if($lrow_field["fk_data_type"]=="checkbox"){?>	
                	<td style="padding:0 5 0 5" align="center">
                    	<img src="./images/<?=(($lrow[$lrow_field["save_to_field"]]=='t')?"true":"false")?>.gif">
					</td>
                    
                <? } else if($lrow_field["save_to_field"]=="" && $lrow_field["fk_data_type"]=="readonly"){?>	
                	<td style="padding:0 5 0 5" align="center">
                    <?
					$fk_id = explode("=", $lrow_field["reference_expression"]);
					$value_view = get_rec("".$lrow_field["reference_table_name"]."","".$lrow_field["reference_field_name"]."","$fk_id[0]='".$lrow["".$fk_id[1].""]."'");
						//showquery ("select ".$lrow_field["reference_field_name"]." from ".$lrow_field["reference_table_name"]." where $fk_id[0]=".$lrow["".$fk_id[1].""]."");
					echo $value_view;
					?>
					</td>
                <? } else if($lrow_field["fk_data_type"]=="range_value"){ ?>
					<td style="padding:0 5 0 5" align="center">
						<?=$lrow[$lrow_field["save_to_field"]]?> - <?=$lrow[$lrow_field["save_to_field_2"]]?>
                    </td>	
                <? } else if($lrow_field["fk_data_type"]=="range_date"){ ?>
					<td style="padding:0 5 0 5" align="center">
						<?=date("d/m/Y",strtotime($lrow[$lrow_field["save_to_field"]]))?> - <?=date("d/m/Y",strtotime($lrow[$lrow_field["save_to_field_2"]]))?>
                    </td>	
                <? } else if($lrow_field["fk_data_type"]=="list" && $lrow_field["type_list"]=="list_db"){?>	
                	<td style="padding:0 5 0 5" align="center">
                    	 <?
						 $get_list_db=explode(" ", $lrow_field["list_sql"]);
						 $value_list_db = get_rec("".$get_list_db[3]."","".$lrow_field["list_field_text"]."","".$lrow_field["list_field_value"]."='".$lrow[$lrow_field["save_to_field"]]."'");
						 //showquery("select ".$lrow_field["list_field_text"]." from ".$get_list_db[3]." where ".$lrow_field["list_field_value"]."='".$lrow[$lrow_field["save_to_field"]]."'");
						 echo $value_list_db;
						 ?>
					</td>
             	<?	}else {?>
                    <td style="padding:0 5 0 5">
                		<?=$lrow[$lrow_field["save_to_field"]]?>
                    </td>
                <? }?>
			</td>
            
<?
		}
?>
        <td align="center"><input type="checkbox" name="select_list" class="groove_checkbox" value="<?=$lrow[$lrow_field_pk["kd_field"]]?>" onclick="fCount()"></td>														
		</tr>
<?
		$l_index+=1;
	}
}

function create_button_non_item(){
	global $module;
	
	$lrs=pg_query("select * from skeleton.tblmenu where fk_parent='".$module."' and is_action=true and type_action='module_action' and per_item='f'");	
	while ($lrow=pg_fetch_array($lrs)) {
		//if(check_right($lrow["kd_menu"],false)){
?>
&nbsp;<input type="button" value=" <?=$lrow["nama_menu"]?> " class="groove_button" onClick="fModal('<?=$lrow["kd_menu"]?>')">
<?	
		//}	
	}
}

function create_button_item($p_pk_id){
	global $module;
	
	$lrs=pg_query("select * from skeleton.tblmenu where fk_parent='".$module."' and is_action=true and type_action='module_action' and per_item='t'");	
	while ($lrow=pg_fetch_array($lrs)) {
		//if(check_right($lrow["kd_menu"],false)){
?>
&nbsp;<input type="button" value=" <?=$lrow["nama_menu"]?> " class="groove_button" onClick="fModal('<?=$lrow["kd_menu"]?>','<?=$p_pk_id?>')">
<?	
		//}	
	}
}
?>