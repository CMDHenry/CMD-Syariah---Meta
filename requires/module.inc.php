<?
function _module_generate_detail_query($p_id,$p_table,$p_arr_relation){
	$l_parent=get_rec("skeleton.tbldb_table","parent_table","is_table_detail is true and kd_table='".$p_table."'");
	$l_fk_field=get_rec("skeleton.tbldb_table_detail","kd_field","is_foreign_key is true and fk_db_table='".$p_table."' and foreign_table='".$l_parent."'");  //aneh ambil foreign tablenya lbh baik darimana?
	//echo $p_id;
	//echo  $p_inner_join.'sss';
	$l_temp=explode(".",$p_table);
	$l_return="select * from (
		select * from ".$p_table." where ".$l_fk_field."='".convert_sql($p_id)."'
	) as ".$l_temp[1];
	
	//print_r($p_arr_relation);
	if($p_arr_relation){
		foreach ($p_arr_relation as $l_table_join=>$l_arr_join_field) {
			//print_r($l_arr_join_field);
			$l=0;
			foreach ($l_arr_join_field as $l_table=>$l_arr_join_value) {
				
				$l_arr_join_value["get_field_value"]= explode("||\'|\'||",htmlspecialchars($l_arr_join_value["get_field_value"][0]));
				if (strstr($l_table,".")) {
				    //echo $l_table;
					$l_temp2=explode(".",$l_table);
					//echo $l_arr_join_value["is_join_default"];
					if($l_arr_join_value["is_join_default"]!='f'){
						$l_return.=" left join (select ".$l_arr_join_value["get_field_key"].",".join(",",$l_arr_join_value["get_field_value"])." from ".$l_table." ) as ".$l_temp2[1]." on ".$l_arr_join_value["get_field_key"]."=".$l_temp[1].".".$l_arr_join_value["get_foreign_key"];
					}
				} else {
						
					//echo "cc".$l_arr_join_value["inner_join"];
					$l_return.=" left join (select ".$l_arr_join_value["get_field_key"].",".join(",",$l_arr_join_value["get_field_value"])." from ".$l_table." ) as tblmain".$l." on tblmain".$l.".".$l_arr_join_value["get_field_key"]."=".$l_temp[1].".".$l_arr_join_value["get_foreign_key"];
					
					//echo $l_return;

					$l++;
					//$l_return.=" left join (select ".$l_arr_join_value["get_field_key"].",".join(",",$l_arr_join_value["get_field_value"])." from ".$l_table." ".$l_arr_join_value["inner_join"].") as ".$l_table." on ".$l_arr_join_value["get_field_key"]."=".$l_temp[1].".".$l_arr_join_value["get_foreign_key"];
					//$l_return.=" inner join (select kd_item,desc_tipe_cust,warna from tblitem_kendaraan inner join tbltipe_kendaraan on tblitem_kendaraan.fk_tipe_kendaraan=tbltipe_kendaraan.kd_tipe_kendaraan inner join tblwarna on fk_warna=kd_warna) as ".$l_table." on ".$l_arr_join_value["get_field_key"]."=".$l_arr_join_value["get_foreign_key"];
				}
			}
		}
	}
	//showquery($l_return);
	return $l_return;
}

function _module_create_header($p_type){
	global $id_menu,$kd_module,$nm_tabs;
	//if($p_type=="add" || $p_type=="edit"){
		if(get_rec("skeleton.tblmodule","is_tab","fk_menu='".$id_menu."'")=='t'){			
			//echo get_rec("skeleton.tblmodule","is_tab","fk_menu='".$id_menu."'");
	?>
				<table cellpadding="0" cellspacing="1" border="0" width="100%">
					<tr>
						<td colspan="4" bgcolor='#e0e0e0'>
							<table cellpadding="0" cellspacing="0" border="0" width="100%">
								<tr>
									<td class="switch_menu" id="menuBar">                             
										<a href="#" onClick="fSwitch('div<?=$nm_tabs?>',this)">&nbsp;&nbsp;&nbsp;<?=$nm_tabs?>&nbsp;&nbsp;&nbsp;</a> |
										<? _module_create_menu_tab($p_type) ?>
										<!--<span id="tab"> &nbsp;</span>-->	
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr height="1"><td colspan="4" bgcolor='#aaafff'></td></tr>
				</table>
	<?
			$lrs_tab_tampil=pg_query("select * from skeleton.tblmodule_tabs left join skeleton.tblmodule_tabs_detail 
	on skeleton.tblmodule_tabs.pk_id=skeleton.tblmodule_tabs_detail.fk_module_tabs where fk_module='".$kd_module."' order by no_urut_tabs");
			//showquery("select * from skeleton.tblmodule_tabs left join skeleton.tblmodule_tabs_detail 
	//on skeleton.tblmodule_tabs.pk_id=skeleton.tblmodule_tabs_detail.fk_module_tabs where fk_module='".$kd_module."' order by no_urut_tabs");
			while($lrow_tampil_tab=pg_fetch_array($lrs_tab_tampil)){
	?>
				<div id="div<?=$lrow_tampil_tab["kd_tabs"]?>" style='display:none;'>
				<table cellpadding="0" cellspacing="1" border="0" width="100%">
	<?
				//showquery("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' and no_urut_".$p_type.">=".$lrow_tampil_tab["no_begin_".$p_type]." and no_urut_".$p_type."<=".$lrow_tampil_tab["no_end_".$p_type]." order by no_urut_".$p_type." asc");
				if($lrow_tampil_tab["tab_detail"]==""){
					$lrs_field=pg_query("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' and no_urut_".$p_type.">=".$lrow_tampil_tab["no_begin_".$p_type]." and no_urut_".$p_type."<=".$lrow_tampil_tab["no_end_".$p_type]." order by no_urut_".$p_type." asc");
					//showquery("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' and no_urut_".$p_type.">=".$lrow_tampil_tab["no_begin_".$p_type]." and no_urut_".$p_type."<=".$lrow_tampil_tab["no_end_".$p_type]." order by no_urut_".$p_type." asc");
					_module_create_header_content($lrs_field,$p_type,$lrow_tampil_tab["no_begin_".$p_type]);
				}else{
					if($lrow_tampil_tab["no_begin_".$p_type]!=""){ //untuk yang di tab nya ada header dan table detail
						
						$lrs_field=pg_query("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' and no_urut_".$p_type.">=".$lrow_tampil_tab["no_begin_".$p_type]." and no_urut_".$p_type."<=".$lrow_tampil_tab["no_end_".$p_type]." order by no_urut_".$p_type." asc");
						if(!$div || $div!=1){
						//showquery("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' and no_urut_".$p_type.">=".$lrow_tampil_tab["no_begin_".$p_type]." and no_urut_".$p_type."<=".$lrow_tampil_tab["no_end_".$p_type]." order by no_urut_".$p_type." asc");
							_module_create_header_content($lrs_field,$p_type,$lrow_tampil_tab["no_begin_".$p_type]);
							_module_create_detail(none);
						}
						$div=1;
						//echo $div;
					} else _module_create_detail(none); //untuk yang di tab nya hanya ada table detail saja
				}
				
	?>     
				</table>
				</div>
	<? 
			}
		//}
	}else {
?>
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
<?
		$lrs_field=pg_query("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' and is_".$p_type."='t' order by no_urut_".$p_type." asc");
		//showquery("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' and is_".$p_type." order by no_urut_".$p_type." asc");
		_module_create_header_content($lrs_field,$p_type);
?>	
            </table>
<?
	}
}

function _module_create_header_content($p_rs_field,$p_type,$p_no_urut_awal=0){
	$l_arr_param["list"]["list_db"]["list_sql"]="list_sql";
	$l_arr_param["list"]["list_db"]["list_value"]="list_field_value";
	$l_arr_param["list"]["list_db"]["list_text"]="list_field_text";
	$l_arr_param["list"]["list_manual"]["list_text"]="list_manual_text";
	$l_arr_param["list"]["list_manual"]["list_value"]="list_manual_value";
	$l_arr_param["checkbox_list"][""]["checkbox_manual_text"]="checkbox_manual_text";
	$l_arr_param["checkbox_list"][""]["checkbox_manual_value"]="checkbox_manual_value";
	$l_arr_param["list"]["get"]["fk_module"]="fk_get_module";
	
	$l_arr_param_value["decimal"]=0;
	$l_old_no_urut=$p_no_urut_awal;
	while ($lrow_field=pg_fetch_array($p_rs_field)) {		
		if($lrow_field["is_numeric"]=='t')$lrow_field["fk_data_type"]="numeric";
		$l_arr_param_value=array();
		if (is_array($l_arr_param[$lrow_field["fk_data_type"]][$lrow_field["type_list"]])) {
			foreach($l_arr_param[$lrow_field["fk_data_type"]][$lrow_field["type_list"]] as $l_key=>$l_value) {
				$l_arr_param_value[$l_key]=$lrow_field[$l_value];
			}
		}
		$lrs_field_attribute=pg_query("select * from skeleton.tblmodule_fields_attribute where fk_module_fields='".$lrow_field["pk_id"]."'");
		//showquery("select * from skeleton.tblmodule_fields_attribute where fk_module_fields='".$lrow_field["pk_id"]."'");
		$align_right="";
		while($lrow_field_attribute=pg_fetch_array($lrs_field_attribute)){
			$l_arr_attribute_value[$lrow_field["pk_id"]][$lrow_field_attribute["nm_attribute"]]=$lrow_field_attribute["value"];
		
/*			if($lrow_field_attribute["nm_attribute"]=='align' && $lrow_field_attribute["value"]=='right' && $lrow_field["fk_data_type"]=="numeric"){
				$align_right="align='right'";
			}
*/		}
		
		for(;$l_old_no_urut<$lrow_field["no_urut_".$p_type];$l_old_no_urut++) {
			if ($l_old_no_urut%2==0) {
?>
                <tr bgcolor="#efefef">
                	<td width="20%" style="padding:0 5 0 5"></td>
                	<td width="30%" style="padding:0 5 0 5"></td>
<?				
			} else {
?>
                	<td width="20%" style="padding:0 5 0 5"></td>
                	<td width="30%" style="padding:0 5 0 5"></td>
				</tr>
<?
			}
		}
		if ($lrow_field["is_colspan"]=='t') $l_old_no_urut=$lrow_field["no_urut_".$p_type]+2;
		else $l_old_no_urut=$lrow_field["no_urut_".$p_type]+1;
		if($lrow_field["fk_data_type"]=="subtitle"){
?>
                <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
                     <td align="center" colspan="4">&nbsp;<?=$lrow_field["nm_field"]?></td> 
                </tr>                       			
<?
		}else if($lrow_field["fk_data_type"]=="table_detail"){
			$lrs_table_detail=pg_query("select * from skeleton.tblmodule_detail where fk_module='".$lrow_field["fk_module"]."' and kd_module_detail='".$lrow_field["fk_table_detail"]."'");
			//showquery("select * from skeleton.tblmodule_detail where fk_module='".$lrow_field["fk_module"]."'");
			while($lrow_table_detail=pg_fetch_array($lrs_table_detail)){
			?>
            	<tr bgcolor="#efefef">
                	<td colspan="4">
                    	<div id=div<?=$lrow_table_detail["kd_module_detail"]?>></div>
                    </td>
                </tr>
            <?
			}
		}else {
			if($lrow_field["no_urut_".$p_type]%2==0){
				//<?=($lrow_field["is_numeric"]=='t'?" align='right'":"")
				// biar ke kanan kalau angka
?>
                <tr bgcolor="#efefef">
					<td width="20%" style="padding:0 5 0 5"<?=((($lrow_field["is_require"]=='t')&&$lrow_field["is_read_only_".$p_type]=='f')?" class='fontColor'":"")?> valign="top"><?=$lrow_field["nm_field"]?></td>
					<td<?=(($lrow_field["is_colspan"]=='t')?" colspan='3'":"")?><?=(($lrow_field["is_colspan"]=='t')?" width='80%'":" width='30%'")?> style="padding:0 5 0 5" valign="top" <?=$align_right?>><?=(($lrow_field["is_read_only_".$p_type]=='t' &&$lrow_field["default_value_input"]==NULL)?generate_input("reference","readonly",$lrow_field["kd_field"],$lrow_field["nm_field"],$lrow_field["next_kd_field_".$p_type],$l_arr_attribute_value[$lrow_field["pk_id"]],$l_arr_param_value,$lrow_field["type_list"],$lrow_field["fgetname"],$lrow_field["fgetdataname"],"",$lrow_field["default_value_input"],NULL,NULL,$lrow_field["is_numeric"],NULL,$lrow_field["value_type"]):generate_input($lrow_field["value_type"],$lrow_field["fk_data_type"],$lrow_field["kd_field"],$lrow_field["nm_field"],$lrow_field["next_kd_field_".$p_type],$l_arr_attribute_value[$lrow_field["pk_id"]],$l_arr_param_value,$lrow_field["type_list"],$lrow_field["fgetname"],$lrow_field["fgetdataname"],$lrow_field["is_read_only_".$p_type],$lrow_field["default_value_input"],$lrow_field["is_multiple"],"",$lrow_field["is_numeric"],$p_type,$lrow_field["value_type"],$lrow_field["field_fk_dealer"]))?></td>
<?
				if ($lrow_field["is_colspan"]=='t') {
?>
				</tr>
<?
				}
			}else{

?>             
					<td width="20%" style="padding:0 5 0 5"<?=(($lrow_field["is_require"]=='t' && $lrow_field["condition_require"]=="" && $lrow_field["is_read_only_".$p_type]=='f')?" class='fontColor'":"")?> valign="top"><?=$lrow_field["nm_field"]?></td>
					<td width="30%" style="padding:0 5 0 5" valign="top" <?=$align_right?>><?=(($lrow_field["is_read_only_".$p_type]=='t' &&$lrow_field["default_value_input"]==NULL)?generate_input("reference","readonly",$lrow_field["kd_field"],$lrow_field["nm_field"],$lrow_field["next_kd_field_".$p_type],$l_arr_attribute_value[$lrow_field["pk_id"]],$l_arr_param_value,$lrow_field["type_list"],$lrow_field["fgetname"],$lrow_field["fgetdataname"],NULL,NULL,NULL,NULL,$lrow_field["is_numeric"],NULL,$lrow_field["value_type"]):generate_input($lrow_field["value_type"],$lrow_field["fk_data_type"],$lrow_field["kd_field"],$lrow_field["nm_field"],$lrow_field["next_kd_field_".$p_type],$l_arr_attribute_value[$lrow_field["pk_id"]],$l_arr_param_value,$lrow_field["type_list"],$lrow_field["fgetname"],$lrow_field["fgetdataname"],$lrow_field["is_read_only_".$p_type],$lrow_field["default_value_input"],$lrow_field["is_multiple"],"",$lrow_field["is_numeric"],$p_type,$lrow_field["value_type"],$lrow_field["field_fk_dealer"]))?></td>
				</tr>
<?
			}
		}
	}
	if ($l_old_no_urut%2!=0) {
?>
                	<td width="20%" style="padding:0 5 0 5"></td>
                	<td width="30%" style="padding:0 5 0 5"></td>
				</tr>
<?
	}
}

function _module_create_detail($display,$menu='is_add'){
	global $id_menu;
	//query untuk memunculkan tabel detail
	$lrs_detail=pg_query("select * from skeleton.tblmodule
						inner join skeleton.tblmodule_detail on skeleton.tblmodule.pk_id=fk_module
						where fk_menu='".$id_menu."' and is_detail is true
						and ".$menu." ='t'
						order by no_urut_detail asc,skeleton.tblmodule_detail.pk_id");//order by no_urut_detail
						
	while($lrow_detail=pg_fetch_array($lrs_detail)){
		$lrs_detail_fields=pg_query("select * from skeleton.tblmodule_detail_fields where fk_module_detail='".$lrow_detail["pk_id"]."' order by no_urut");
?>
				<input type="hidden" name="strisi_<?=$lrow_detail["kd_module_detail"]?>" value="<?=$_REQUEST["strisi_".$lrow_detail["kd_module_detail"]]?>">
				<script>
                    arrFieldTable_<?=$lrow_detail["kd_module_detail"]?>={
<?
		$lrs_detail_fields=pg_query("select * from skeleton.tblmodule_detail_fields where fk_module_detail='".$lrow_detail["pk_id"]."' order by no_urut");
		
		$index=0;
		$count_db=0;
		while($lrow_detail_fields=pg_fetch_array($lrs_detail_fields)){
			$i=0;
			if($lrow_detail_fields["type_list"]=='list_db'){
				$lrs_db=pg_query($lrow_detail_fields["list_sql"]);	
				$count_baris=pg_num_rows($lrs_db);
				while($lrow_db=pg_fetch_array($lrs_db)){
					if($i!=$count_baris){
						$list_database[$index].=$lrow_db[$lrow_detail_fields["list_field_value"]].",";
						$list_database_text[$index].=$lrow_db[$lrow_detail_fields["list_field_text"]].",";
					} else {
						$list_database[$index].=$lrow_db[$lrow_detail_fields["list_field_value"]];
						$list_database_text[$index].=$lrow_db[$lrow_detail_fields["list_field_text"]];
					}
					$i++;			
				}	
			}	
				
?>
						<?
						$count_db_detil=0;
						?>
						<?=$index?>:{'name':'<?=$lrow_detail_fields["kd_field"]?>','caption':'<?=$lrow_detail_fields["nm_field"]?>',
						<?=(($lrow_detail_fields["fk_data_type"]=='list')?"'type':'".$lrow_detail_fields["type_list"]."',":"'type':'".$lrow_detail_fields["fk_data_type"]."',")?>
						<?=(($lrow_detail_fields["fk_get_module"])?"'source':'".$lrow_detail_fields["fk_get_module"]."',":"")?>
						'size':'<?=$lrow_detail_fields["input_width"]?>',
						'id_detail_field':'<?=$lrow_detail_fields["pk_id"]?>',
						'is_required':'<?=$lrow_detail_fields["is_require"]?>',
						'is_numeric':'<?=$lrow_detail_fields["is_numeric"]?>',
						'table_db':'<?=$lrow_detail_fields["get_table_db"]?>',						
						'table_db_inner_join':'<?=$lrow_detail_fields["inner_join"]?>',						
						<?=(($lrow_detail_fields["type_list"]=='get' || $lrow_detail_fields["type_list"]=='get_custom')?"'field_key':'".$lrow_detail_fields["get_field_key"]."',":(($lrow_detail_fields["type_list"]=='list_manual')?"'field_key':'".$lrow_detail_fields["list_manual_value"]."',":"'field_key':'".$list_database[$index]."',"))?>
						<?=(($lrow_detail_fields["type_list"]=='get' || $lrow_detail_fields["type_list"]=='get_custom')?"'field_get':'".$lrow_detail_fields["get_field_value"]."',":(($lrow_detail_fields["type_list"]=='list_manual')?"'field_get':'".$lrow_detail_fields["list_manual_text"]."',":"'field_get':'".$list_database_text[$index]."',"))?>
						
						<?=(($lrow_detail_fields["data_reference"])?"'referer':'".$lrow_detail_fields["data_reference"]."'":"")?>
							<? if($lrow_detail_fields["is_unique"]=='t' || $lrow_detail_fields["is_pk"]=='t'){?>
							,'OtherErrorCheck':function (pObj){
								lReturn="";
								if (lReturn=="") {
									lTable=pObj.parentNode.parentNode.parentNode.parentNode
									lTableCurrentRow=pObj.parentNode.parentNode
									for (lIndex=1;lIndex<(lTable.rows.length-1);lIndex++) {
										if (lIndex!=lTableCurrentRow.rowIndex) {
											lComp=lTable.rows[lIndex].cells[0].innerHTML
											lValue=pObj.value
											if (lComp==lValue) lReturn="Sudah Ada"
										}
									}
								}
								return lReturn
							}
							<? }else if($lrow_detail_fields["fk_data_type"]=="date" || $lrow_detail_fields["fk_data_type"]=="timestamp"){?>
								'OtherErrorCheck':function (pObj){
									lReturn="";
									if(pObj.value!=""){
										if(!pObj.value.match(/^\d{1,2}\/\d{1,2}\/\d{4}$/)){
											lReturn="Salah"
										} else {
											var tglNormal = new Array(31,28,31,30,31,30,31,31,30,31,30,31);
											var tglLeap = new Array(31,29,31,30,31,30,31,31,30,31,30,31);
											var tgl=pObj.value.split("/");
											if(tgl[2]%4==0){
												if(tgl[1]>12) lReturn="Salah"
												if(tgl[0]>tglLeap[tgl[1]-1]) lReturn="Salah"
											} else{
												if(tgl[1]>12) lReturn="Salah"
												if(tgl[0]>tglNormal[tgl[1]-1]) lReturn="Salah"
											}
										}
									}
									return lReturn
								}
							<? }?>
						},
						
<?			
			$index++;
		}// while tbldetail_fields
?>
                    };
					table_<?=$lrow_detail["kd_module_detail"]?>=new table();
					table_<?=$lrow_detail["kd_module_detail"]?>.init("<strong><?=$lrow_detail["nm_module_detail"]?></strong>","Table_<?=$lrow_detail["kd_module_detail"]?>",arrFieldTable_<?=$lrow_detail["kd_module_detail"]?>,document.form1,{'id':'Table_<?=$lrow_detail["kd_module_detail"]?>','style':'display:<?=($display)?$display:none?>','border':'0','cellpadding':'1','cellspacing':'1','width':'100%','class':'border','align':'center'});
					table_<?=$lrow_detail["kd_module_detail"]?>.setIsi(document.form1.strisi_<?=$lrow_detail["kd_module_detail"]?>.value);
					
					<?
					if($lrow_detail["dynamic_table_detail"]=="t"){
					?>
						document.getElementById("div<?=$lrow_detail["kd_module_detail"]?>").appendChild(table_<?=$lrow_detail["kd_module_detail"]?>.table)
					<?	
					}
					?>
                </script>
<?
	}//while berapa banyak tabel detail	
}

function _module_create_get_isi($menu='is_add'){
	global $id_menu;
	//query untuk memunculkan tabel detail
	$lrs_detail=pg_query("select * from skeleton.tblmodule
						inner join skeleton.tblmodule_detail on skeleton.tblmodule.pk_id=fk_module
						where fk_menu='".$id_menu."' and is_detail is true and ".$menu." ='t'
						order by skeleton.tblmodule_detail.pk_id");
	while($lrow_detail=pg_fetch_array($lrs_detail)){
?>
	document.form1.strisi_<?=$lrow_detail["kd_module_detail"]?>.value=table_<?=$lrow_detail["kd_module_detail"]?>.getIsi();
	if(document.form1.strisi_<?=$lrow_detail["kd_module_detail"]?>.value=='false') lCanSubmit=false
<?
	}
}
function _module_create_get_table(){
	global $id_menu;
	$lrs_detail=pg_query("select * from skeleton.tblmodule
						inner join skeleton.tblmodule_detail on skeleton.tblmodule.pk_id=fk_module
						where fk_menu='".$id_menu."' and is_detail is true
						order by skeleton.tblmodule_detail.pk_id");
	while($lrow_detail=pg_fetch_array($lrs_detail)){
?>"Table_<?=$lrow_detail["kd_module_detail"]?>"
<?
	}
}

function _module_create_menu_tab_view($p_type){
	global $id_menu;
	
	$lrs_tab=pg_query("select * from skeleton.tblmodule 
							inner join skeleton.tblmodule_fields on skeleton.tblmodule.pk_id=skeleton.tblmodule_fields.fk_module 
							inner join skeleton.tblmodule_tabs on skeleton.tblmodule.pk_id=skeleton.tblmodule_tabs.fk_module and skeleton.tblmodule_tabs.pk_id_module_fields=skeleton.tblmodule_fields.pk_id 
							inner join skeleton.tblmodule_fields_attribute on fk_module_fields=skeleton.tblmodule_fields.pk_id 
							where fk_menu='".$id_menu."' and is_".$p_type." is true and value like '%fShowHide(this%' and no_urut_tabs!=0
							order by no_urut_".$p_type.",no_urut_tabs");
							
	/*showquery("select * from skeleton.tblmodule 
							inner join skeleton.tblmodule_fields on skeleton.tblmodule.pk_id=skeleton.tblmodule_fields.fk_module 
							inner join skeleton.tblmodule_tabs on skeleton.tblmodule.pk_id=skeleton.tblmodule_tabs.fk_module and skeleton.tblmodule_tabs.pk_id_module_fields=skeleton.tblmodule_fields.pk_id 
							inner join skeleton.tblmodule_fields_attribute on fk_module_fields=skeleton.tblmodule_fields.pk_id 
							where fk_menu='".$id_menu."' and is_".$p_type." is true and value like '%fShowHide(this%' and no_urut_tabs!=0
							order by no_urut_".$p_type.",no_urut_tabs");*/
							
	while($lrow_tab=pg_fetch_array($lrs_tab)){
		if ($lrow_tab["pk_id_module_fields"]) {
			if (($lrow_tab["fk_data_type"]=="checkbox_list") && is_array($_REQUEST[str_replace("[]","",$lrow_tab["kd_field"])])){
				//print_r ($_REQUEST[str_replace("[]","",$lrow_tab["kd_field"])]);
			 	$l_display=((in_array($lrow_tab["kd_tabs"],$_REQUEST[str_replace("[]","",$lrow_tab["kd_field"])]))?"inline":"none");
			 }else if($lrow_tab["fk_data_type"]=="list" and $lrow_tab["is_multiple"]=="t"){
				 $list=explode(",",strtoupper($_REQUEST[str_replace("[]","",$lrow_tab["kd_field"])]));
				 $l_display=((in_array($lrow_tab["kd_tabs"],$list))?"inline":"none");
			 }else{
				
				$l_display=((strtoupper($_REQUEST[$lrow_tab["kd_field"]])==strtoupper($lrow_tab["kd_tabs"]))?"inline":"none");
			}
			//echo $l_display;
		} else {
			$l_display="none";
		}
		
?>
                                <a href="#" onClick="fSwitchView('div<?=$lrow_tab["kd_tabs"]?>',this)" id="a_tab_<?=$lrow_tab["kd_tabs"]?>" style="display:<?=$l_display?>">&nbsp;&nbsp;&nbsp;<?=$lrow_tab["kd_tabs"]?>&nbsp;&nbsp;&nbsp;</a> <span id="separator_<?=$lrow_tab["kd_tabs"]?>" style="display:<?=$l_display?>">|</span>
<?
	}
}

function _module_create_menu_tab($p_type){
	global $id_menu;
	if($p_type=="approve" || $p_type=="batal") $p_type="edit";
	
	$lrs_tab=pg_query("select * from skeleton.tblmodule 
							inner join skeleton.tblmodule_fields on skeleton.tblmodule.pk_id=skeleton.tblmodule_fields.fk_module 
							inner join skeleton.tblmodule_tabs on skeleton.tblmodule.pk_id=skeleton.tblmodule_tabs.fk_module and skeleton.tblmodule_tabs.pk_id_module_fields=skeleton.tblmodule_fields.pk_id 
							inner join skeleton.tblmodule_fields_attribute on fk_module_fields=skeleton.tblmodule_fields.pk_id 
							where fk_menu='".$id_menu."' and is_".$p_type." is true and value like '%fShowHide(this%' and no_urut_tabs!=0
							order by no_urut_".$p_type.",no_urut_tabs");
							
	/*showquery("select * from skeleton.tblmodule 
							inner join skeleton.tblmodule_fields on skeleton.tblmodule.pk_id=skeleton.tblmodule_fields.fk_module 
							inner join skeleton.tblmodule_tabs on skeleton.tblmodule.pk_id=skeleton.tblmodule_tabs.fk_module and skeleton.tblmodule_tabs.pk_id_module_fields=skeleton.tblmodule_fields.pk_id 
							inner join skeleton.tblmodule_fields_attribute on fk_module_fields=skeleton.tblmodule_fields.pk_id 
							where fk_menu='".$id_menu."' and is_".$p_type." is true and value like '%fShowHide(this%' and no_urut_tabs!=0
							order by no_urut_".$p_type.",no_urut_tabs");*/
							
	while($lrow_tab=pg_fetch_array($lrs_tab)){
		if ($lrow_tab["pk_id_module_fields"]) {
			if ($lrow_tab["fk_data_type"]=="checkbox_list" && is_array($_REQUEST[str_replace("[]","",$lrow_tab["kd_field"])])){
			 	$l_display=((in_array($lrow_tab["kd_tabs"],$_REQUEST[str_replace("[]","",$lrow_tab["kd_field"])]))?"inline":"none");
			 } else {
				if(is_array($_REQUEST[str_replace("[]","",$lrow_tab["kd_field"])])){ 
					$l_display=((in_array(strtolower($lrow_tab["kd_tabs"]),$_REQUEST[str_replace("[]","",$lrow_tab["kd_field"])]))?"inline":"none");
				} else {
					$l_display=(strtoupper($_REQUEST[str_replace("[]","",$lrow_tab["kd_field"])])==strtoupper($lrow_tab["kd_tabs"]))?"inline":"none";
				}
			}
		} else {
			$l_display="none";
		}
		
?>
                                <a href="#" onClick="fSwitch('div<?=$lrow_tab["kd_tabs"]?>',this)" id="a_tab_<?=$lrow_tab["kd_tabs"]?>" style="display:<?=$l_display?>">&nbsp;&nbsp;&nbsp;<?=$lrow_tab["kd_tabs"]?>&nbsp;&nbsp;&nbsp;</a> <span id="separator_<?=$lrow_tab["kd_tabs"]?>" style="display:<?=$l_display?>">|</span>
<?
	}
}

function _module_create_menu_tab_load($p_type){
	global $id_menu;
	if($p_type=="approve" || $p_type=="batal") $p_type="edit";
	$i=0;
	$lrs_tab=pg_query("select * from skeleton.tblmodule 
							inner join skeleton.tblmodule_fields on skeleton.tblmodule.pk_id=skeleton.tblmodule_fields.fk_module 
							inner join skeleton.tblmodule_tabs on skeleton.tblmodule.pk_id=skeleton.tblmodule_tabs.fk_module and skeleton.tblmodule_tabs.pk_id_module_fields=skeleton.tblmodule_fields.pk_id 
							inner join skeleton.tblmodule_fields_attribute on fk_module_fields=skeleton.tblmodule_fields.pk_id 
							where fk_menu='".$id_menu."' and is_".$p_type." is true and value like '%fShowHide(this%' and no_urut_tabs!=0
							order by no_urut_".$p_type.",no_urut_tabs");
	
	while($lrow_tab=pg_fetch_array($lrs_tab)){
		if(strstr($lrow_tab["condition_value"],",")!="" && $lrow_tab["is_multiple"] =="f"){	
			//echo "bbb";
			$condition_value=split(',',$lrow_tab["condition_value"]);
			//$kd_field=str_replace("[]","",$lrow_tab["kd_field"]);
			$kd_field=$lrow_tab["kd_field"];
?>
        	if(document.form1.<?=$kd_field?>.value=='<?=$condition_value[$i]?>') {
        		fShowHide(document.form1.<?=$kd_field?>)
            
			}
<?
			$i++;
			//showquery ($lrow_tab["kd_field"]);
		}else{
			if($lrow_tab["fk_data_type"]=="checkbox_list" || $lrow_tab["is_multiple"]=="t"){
				//echo "aaa";
				$kd_field=str_replace("[]","",$lrow_tab["kd_field"]);
				if(is_array($_REQUEST[$kd_field])){
					foreach($_REQUEST[$kd_field] as $value){
						$value_checkbox_list.=$value.",";
					}
				}
				if($value_checkbox_list!=""){
					if(strstr($value_checkbox_list,$lrow_tab["condition_value"])!=""){	//Jika value nya sesuai maka munculkan tab
?>		
        			fShowHide("","")
<?
					}
				}
			}else{
				
?>				
        		if(document.form1.<?=$lrow_tab["kd_field"]?>.value=='<?=$lrow_tab["condition_value"]?>'){	
            		fShowHide(document.form1.<?=$lrow_tab["kd_field"]?>)
        		}
<?
			}
			$i=0;
		}
	}
}

function _module_create_js(){
	global $kd_module;
	
	$lrs=pg_query("select * from skeleton.tblmodule_fields where fk_module='".convert_sql($kd_module)."' and fjavascript||fgetdataname<>''");
	//showquery("select * from skeleton.tblmodule_fields where fk_module='".convert_sql($kd_module)."' and fjavascript||fgetdataname<>''");
	
	while ($lrow=pg_fetch_array($lrs)) {
		echo $lrow["fjavascript"]."\n";
		echo $lrow["fgetdataname"]."\n";
	}
}


function _module_create_js_search(){
	global $pk_id_module;
	
	$lrs=pg_query("select * from skeleton.tblmodule_fields where fk_module='".convert_sql($pk_id_module)."' and fjavascriptsearch||fgetdatanamesearch<>''");
	//showquery("select * from skeleton.tblmodule_fields where fk_module='".convert_sql($kd_module)."' and fjavascriptsearch||fgetdatanamesearch<>''");
	while ($lrow=pg_fetch_array($lrs)) {
		echo $lrow["fjavascriptsearch"]."\n";
		echo $lrow["fgetdatanamesearch"]."\n";
	}
}

function _module_create_header_view(){
	global $id_menu,$kd_module,$nm_tabs;
	
	if(get_rec("skeleton.tblmodule","is_tab","fk_menu='".$id_menu."'")=='t'){
?>
            <table cellpadding="0" cellspacing="1" border="0" width="100%">
				<tr>
					<td colspan="4" bgcolor='#e0e0e0'>
                    	<table cellpadding="0" cellspacing="0" border="0" width="100%">
							<td class="switch_menu" id="menuBar">
                            	<a href="#" onClick="fSwitchView('div<?=$nm_tabs?>',this)">&nbsp;&nbsp;&nbsp;<?=$nm_tabs?>&nbsp;&nbsp;&nbsp;</a> |
                            	<? _module_create_menu_tab_view("edit") ?>
								<!--<span id="tab"> &nbsp;</span>-->	
							</td>
						</table>
                    </td>
				</tr>
				<tr height="1"><td colspan="4" bgcolor='#aaafff'></td></tr>
			</table>
<?
		$lrs_tab_tampil=pg_query("select * from skeleton.tblmodule_tabs left join skeleton.tblmodule_tabs_detail 
on skeleton.tblmodule_tabs.pk_id=skeleton.tblmodule_tabs_detail.fk_module_tabs where fk_module='".$kd_module."' order by no_urut_tabs");
		while($lrow_tampil_tab=pg_fetch_array($lrs_tab_tampil)){
						
?>
            <div id="div<?=$lrow_tampil_tab["kd_tabs"]?>" style='display:none;'>
            <table cellpadding="0" cellspacing="1" border="0" width="100%">
<?			

			if($lrow_tampil_tab["tab_detail"]==""){
				$lrs_field=pg_query("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' and no_urut_edit>=".$lrow_tampil_tab["no_begin_edit"]." and no_urut_edit<=".$lrow_tampil_tab["no_end_edit"]." order by no_urut_edit asc");
				_module_create_header_content_view($lrs_field,$lrow_tampil_tab["no_begin_edit"]);
			} else{
				if($lrow_tampil_tab["no_begin_edit"]!="")
				$lrs_field=pg_query("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' and no_urut_edit>=".$lrow_tampil_tab["no_begin_edit"]." and no_urut_edit<=".$lrow_tampil_tab["no_end_edit"]." order by no_urut_edit asc");
				_module_create_header_content_view($lrs_field,$lrow_tampil_tab["no_begin_edit"]);
			}
			
?>     
            </table>
            </div>

<? 
		}
	} else {
?>
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
<?
		$lrs_field=pg_query("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' and (is_edit='t' or is_add='t') order by no_urut_edit asc");
		//showquery("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' order by no_urut_edit asc");
		_module_create_header_content_view($lrs_field);
		
?>	
                </table>
                </div>
<?
	}
}

function _module_create_header_approval(){
	global $id_menu,$kd_module,$nm_tabs;
	
	?>
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
<?
		$lrs_field=pg_query("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' and (is_edit='t' or is_add='t') order by no_urut_edit asc");
		//showquery("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' order by no_urut_edit asc");
		_module_create_header_content_view($lrs_field);
		
?>	
                </table>
                </div>
<?
}

function _module_create_header_content_view($p_rs_field,$p_no_urut_awal=0){
	$l_old_no_urut=$p_no_urut_awal;
	$detail_flag=0;
	while ($lrow_field=pg_fetch_array($p_rs_field)) {
		
		$lrs_field_attribute=pg_query("select * from skeleton.tblmodule_fields_attribute where fk_module_fields='".$lrow_field["pk_id"]."'");
		//showquery("select * from skeleton.tblmodule_fields_attribute where fk_module_fields='".$lrow_field["pk_id"]."'");
		while($lrow_field_attribute=pg_fetch_array($lrs_field_attribute)){
			$l_arr_attribute_value[$lrow_field["pk_id"]][$lrow_field_attribute["nm_attribute"]]=$lrow_field_attribute["value"];
		}
		//print_r($l_arr_attribute_value);
		$style=(generate_attrib($l_arr_attribute_value[$lrow_field["pk_id"]],array("onkeypress","onkeyup")));
		$style=str_replace('"','',$style);
		$style=str_replace('=',':',$style);
		
		$l_kd_field=$lrow_field["kd_field"];
		$l_kd_field=str_replace("[]","",$l_kd_field);				
		for(;$l_old_no_urut<$lrow_field["no_urut_edit"];$l_old_no_urut++) {
			if ($l_old_no_urut%2==0) {
?>
                <tr bgcolor="#efefef">
                	<td width="20%" style="padding:0 5 0 5"></td>
                	<td width="30%" style="padding:0 5 0 5"></td>
<?				
			} else {
?>
                	<td width="20%" style="padding:0 5 0 5"></td>
                	<td width="30%" style="padding:0 5 0 5"></td>
				</tr>
<?
			}
		}
		if ($lrow_field["is_colspan"]=='t') $l_old_no_urut=$lrow_field["no_urut_edit"]+2;
		else $l_old_no_urut=$lrow_field["no_urut_edit"]+1;
		
		//echo $lrow_field["fk_table_detail"].'='.$detail_flag.'<br>';
		if($lrow_field["fk_data_type"]=="subtitle"){
?>
                <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
                     <td align="center" colspan="4">&nbsp;<?=$lrow_field["nm_field"]?></td> 
                </tr>                       			
<?
		}else if($lrow_field["fk_data_type"]=="table_detail" ){	
			?> </table><? _module_create_detail_view('true','',$lrow_field["fk_table_detail"]); $detail_flag=1;?> <table cellpadding="0" cellspacing="1" border="0" width="100%"> <?	
		}
		
		else {
			if($lrow_field["no_urut_edit"]%2==0){
				//echo $lrow_field["is_link_view"];
?>
                <tr bgcolor="#efefef">
					<td width="20%" style="padding:0 5 0 5" valign="top"><?=$lrow_field["nm_field"]?></td>
					<? if($lrow_field["is_link_view"]=='t' && !strstr($lrow_field["link_view_reference"],'php') ){	
  ?>     				<td <?=(($lrow_field["is_colspan"]=='t')?" colspan='3'":"")?> style="padding:0 5 0 5"<?=(($lrow_field["is_colspan"]=='t')?" width='80%'":" width=30%")?> valign="top"><a href="#" class="blue" onClick="fModal('view','<?=$_REQUEST[$l_kd_field]?>','<?=$lrow_field["link_view_reference"]?>')"><?=$_REQUEST[$l_kd_field]?></a></td>
                     <? }elseif ($lrow_field["fk_data_type"]=="numeric"||$lrow_field["is_numeric"]=="t"){ ?>
                     	<td <?=(($lrow_field["is_colspan"]=='t')?" colspan='3'":"")?> style="padding:0 5 0 5;<?=$style?>"<?=(($lrow_field["is_colspan"]=='t')?" width='80%'":" width=30%")?> valign="top" align="right"><?=((is_array($_REQUEST[$l_kd_field]))?join(",",$_REQUEST[$l_kd_field]):$_REQUEST[$l_kd_field])?></td>
                     <? }else if($lrow_field["fk_data_type"]=="file") {?>
                    		<td width="30%" style="padding:0 5 0 5" valign="top">
                            <? if($_REQUEST[$l_kd_field]!=""){?>
                        		<img src="images/upload/<?=$_REQUEST[$l_kd_field]?>" width="100px" height="100px"/>
                             <? } ?>&nbsp;
                             </td>
                    <? } else {?>
					<td <?=(($lrow_field["is_colspan"]=='t')?" colspan='3'":"")?> style="padding:0 5 0 5;<?=$style?>"<?=(($lrow_field["is_colspan"]=='t')?" width='80%'":" width=30%")?> valign="top"><?=((is_array($_REQUEST[$l_kd_field]))?join(",",$_REQUEST[$l_kd_field]):$_REQUEST[$l_kd_field])?></td>
                    <? } ?>
<?
				if ($lrow_field["is_colspan"]=='t') {
?>
				</tr>
<?
				}
			}else{
?>             		<? //echo $lrow_field["fk_data_type"]."<br>"?>
					<td width="20%" style="padding:0 5 0 5" valign="top"><?=$lrow_field["nm_field"]?></td>
					<? if($lrow_field["is_link_view"]=='t'){	
  ?>     				<td <?=(($lrow_field["is_colspan"]=='t')?" colspan='3'":"")?> style="padding:0 5 0 5"<?=(($lrow_field["is_colspan"]=='t')?" width='80%'":" width=30%")?> valign="top"><a href="#" class="blue" onClick="fModal('view','<?=$_REQUEST[$l_kd_field]?>','<?=$lrow_field["link_view_reference"]?>')"><?=$_REQUEST[$l_kd_field]?></a></td>
                     <? } else if ($lrow_field["fk_data_type"]=="numeric" ||$lrow_field["is_numeric"]=="t"){ ?>
					<td width="30%" align="right" style="padding:0 5 0 5;<?=$style?>" valign="top"><?=((is_array($_REQUEST[$l_kd_field]))?join(",",$_REQUEST[$l_kd_field]):$_REQUEST[$l_kd_field])?></td>
                    <? }else if($lrow_field["fk_data_type"]=="file") {?>
                    <td width="30%" style="padding:0 5 0 5" valign="top">
						<? if($_REQUEST[$l_kd_field]!=""){?>
                        <img src="images/upload/<?=$_REQUEST[$l_kd_field]?>" width="100px" height="100px"/>
                         <? } ?>&nbsp;
                    </td>
                    <? }else { ?>
                     <td width="30%" style="padding:0 5 0 5;<?=$style?>" valign="top"><?=((is_array($_REQUEST[$l_kd_field]))?join(",",$_REQUEST[$l_kd_field]):$_REQUEST[$l_kd_field])?></td>
                    <? } ?>
				</tr>
<?
			}
		}
	}
	if ($l_old_no_urut%2!=0) {
?>
                	<td width="20%" style="padding:0 5 0 5"></td>
                	<td width="30%" style="padding:0 5 0 5"></td>
				</tr>
<?
	//_module_create_detail_view();
	}
}


function _module_create_detail_view($dynamic_table,$menu='is_add',$kd_module_detail=""){
	global $id_menu,$id_view,$id_edit;
	//echo "13";
	//query untuk memunculkan tabel detail
	$lrs_detail=pg_query("
	select * from skeleton.tblmodule
	inner join skeleton.tblmodule_detail on skeleton.tblmodule.pk_id=fk_module
	where fk_menu='".$id_menu."' and is_detail is true and dynamic_table_detail is ".$dynamic_table." and (is_add='t' or is_edit='t') ".(($kd_module_detail=="")?"":"and kd_module_detail='".convert_sql($kd_module_detail)."'")."
	order by skeleton.tblmodule_detail.pk_id");
/*	showquery("
	select * from skeleton.tblmodule
	inner join skeleton.tblmodule_detail on skeleton.tblmodule.pk_id=fk_module
	where fk_menu='".$id_menu."' and is_detail is true and dynamic_table_detail is ".$dynamic_table." and (is_add='t' or is_edit='t') ".(($kd_module_detail=="")?"":"and kd_module_detail='".convert_sql($kd_module_detail)."'")."
	order by skeleton.tblmodule_detail.pk_id");
*/	
	while($lrow_detail=pg_fetch_array($lrs_detail)){
		$lrs_tabel=pg_query("select kd_field,nm_field,no_urut,save_to_table,save_to_field,fk_data_type,get_table_db,get_field_key,get_field_value,inner_join ,is_numeric,is_join_default,type_list,list_field_text,list_manual_value,list_manual_text from skeleton.tblmodule_detail_fields
							where fk_module_detail='".$lrow_detail["pk_id"]."'
							order by no_urut");		
/*		showquery("select kd_field,nm_field,no_urut,save_to_table,save_to_field,fk_data_type,get_table_db,get_field_key,get_field_value,inner_join ,is_numeric from skeleton.tblmodule_detail_fields
							where fk_module_detail='".$lrow_detail["pk_id"]."'
							order by no_urut");
*/						
?>
            <table cellpadding="0" cellspacing="1" border="0" width="100%">
				<tr bgcolor="#c8c8c8">
                	<td align="center" style="padding: 0px 5px;" colspan="<?=pg_num_rows($lrs_tabel)?>"><strong><?=$lrow_detail["nm_module_detail"]?></strong></td>
                </tr>
            	<tr bgcolor="#c8c8c8">
<?		
		$l_arr_detail=array();
		while($lrow_table=pg_fetch_array($lrs_tabel)){
			if($lrow_table["type_list"]=="list_db")$lrow_table["save_to_field"]=$lrow_table["list_field_text"];
?>				
			
					<td align="center" style="padding: 0px 5px;"><?=$lrow_table["nm_field"]?></td>
<?			
			if ($lrow_table["fk_data_type"]!="readonly" ||$lrow_table["save_to_field"]!="" ) {
					$l_arr_detail[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["no_urut"]]["save_to_field"]=$lrow_table["save_to_field"];
					$l_arr_detail[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["no_urut"]]["fk_data_type"]=$lrow_table["fk_data_type"];
					$l_arr_detail[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["no_urut"]]["is_numeric"]=$lrow_table["is_numeric"];
					$save_to_table_default=$lrow_table["save_to_table"];
			} else {
				//showquery("select * from skeleton.tblmodule_detail_fields where fk_module_detail='".$lrow_detail["pk_id"]."' and ','||data_reference||',' like '%".",".$lrow_table["no_urut"].","."%'");
				if ($lrow_reference=pg_fetch_array(pg_query("select * from skeleton.tblmodule_detail_fields where fk_module_detail='".$lrow_detail["pk_id"]."' and ','||data_reference||',' like '%".",".$lrow_table["no_urut"].","."%'"))) {
					$l_arr_detail[$lrow_detail["kd_module_detail"]][$lrow_reference["save_to_table"]][$lrow_table["no_urut"]]["save_to_field"]=$lrow_table["kd_field"];
					$l_arr_detail[$lrow_detail["kd_module_detail"]][$lrow_reference["save_to_table"]][$lrow_table["no_urut"]]["fk_data_type"]=$lrow_table["fk_data_type"];
//echo $lrow_table["is_numeric"];
					$l_arr_detail[$lrow_detail["kd_module_detail"]][$lrow_reference["save_to_table"]][$lrow_table["no_urut"]]["is_numeric"]=$lrow_table["is_numeric"];
				}else{
					$l_arr_detail[$lrow_detail["kd_module_detail"]][$save_to_table_default][$lrow_table["no_urut"]]["save_to_field"]=$lrow_table["kd_field"];
					$l_arr_detail[$lrow_detail["kd_module_detail"]][$save_to_table_default][$lrow_table["no_urut"]]["fk_data_type"]=$lrow_table["fk_data_type"];
					$l_arr_detail[$lrow_detail["kd_module_detail"]][$save_to_table_default][$lrow_table["no_urut"]]["is_numeric"]=$lrow_table["is_numeric"];
					$l_arr_detail_max[$lrow_detail["kd_module_detail"]]=$lrow_table["no_urut"];	
				}
			}
			if ($lrow_table["inner_join"]!="") {
				//echo $lrow_table["save_to_table"].'23432';
				$l_inner_join[$lrow_table["save_to_table"]]=$lrow_table["inner_join"];
			}
			
			//print_r($l_arr_detail);
			if ($lrow_table["get_table_db"]) {
				$l_arr_relation[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["get_table_db"]]["get_field_key"]=$lrow_table["get_field_key"];
				$l_arr_relation[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["get_table_db"]]["get_foreign_key"]=$lrow_table["save_to_field"];
				$l_arr_relation[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["get_table_db"]]["inner_join"]=$lrow_table["inner_join"];
				$l_arr_relation[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["get_table_db"]]["is_join_default"]=$lrow_table["is_join_default"];
				
				//$larr=explode("||\'|\'||",htmlspecialchars($lrow_table["get_field_value"]));
				$larr=split(",",htmlspecialchars($lrow_table["get_field_value"]));;
				foreach ($larr as $l_index=>$l_value) {
					if (is_array($l_arr_relation[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["get_table_db"]]["get_field_value"])) {
						if (!in_array($l_value,$l_arr_relation[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["get_table_db"]]["get_field_value"])) {
							$l_arr_relation[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["get_table_db"]]["get_field_value"][count($l_arr_relation[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["get_table_db"]]["get_field_value"])]=$l_value;
						}
					} else {
						$l_arr_relation[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["get_table_db"]]["get_field_value"][count($l_arr_relation[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]]["get_field_value"])]=$l_value;
					}
				}
				//print_r($l_arr_relation);
			}
			$l_arr_detail_max[$lrow_detail["kd_module_detail"]]=$lrow_table["no_urut"];
		}
?>
            	</tr>
<?
	if ($l_arr_detail) {
		//print_r($l_arr_detail);
		foreach ($l_arr_detail as $l_kd_module_detail=>$l_arr_table){
			foreach ($l_arr_table as $l_table=>$l_arr_field){
				//print_r($l_arr_field);
				//showquery(_module_generate_detail_query(($id_edit)?$id_edit:$id_view,$l_table,$l_arr_relation[$l_kd_module_detail])." ".$l_inner_join[$l_table]);
				$lrs=pg_query(_module_generate_detail_query(($id_edit)?$id_edit:$id_view,$l_table,$l_arr_relation[$l_kd_module_detail])." ".$l_inner_join[$l_table]);		
					
				while ($lrow=pg_fetch_array($lrs)) {
?>
					<tr bgcolor="#e0e0e0">
<?					
					
					for($l_no_urut=0;$l_no_urut<=$l_arr_detail_max[$l_kd_module_detail];$l_no_urut++) {
						if ($lrow[$l_arr_field[$l_no_urut]["save_to_field"]] || $l_arr_field[$l_no_urut]["save_to_field"] ){
							$no_urut++;
						}
					}
					
					for($l_no_urut=0;$l_no_urut<=$l_arr_detail_max[$l_kd_module_detail];$l_no_urut++) {
						if($l_arr_field[$l_no_urut]["is_numeric"]=='t')$l_arr_field[$l_no_urut]["fk_data_type"]='numeric';				
						//echo $lrow[$l_arr_field[$l_no_urut]["save_to_field"]];		
						//echo $l_arr_field[$l_no_urut]["is_numeric"];
						//print_r ($lrow[$l_arr_field[$l_no_urut]["save_to_field"]]);
						//echo $lrow[$l_arr_field[$l_no_urut]["save_to_field"]];
						//echo $l_no_urut;
						//if ($lrow[$l_arr_field[$l_no_urut]["save_to_field"]]!=NULL){
?>
						<td style="padding: 0px 5px;" <?=(($l_arr_field[$l_no_urut]["fk_data_type"]=='numeric')?" align='right'":"")?>><?=(($lrow[$l_arr_field[$l_no_urut]["save_to_field"]]!=NULL)?format_data_view($lrow[$l_arr_field[$l_no_urut]["save_to_field"]],$l_arr_field[$l_no_urut]["fk_data_type"],"d/m/Y"):"")?></td>
<?
						//}
					}
?>
					</tr>
<?
			 	 }
			  }
		   }
		}	
	}
}

?>