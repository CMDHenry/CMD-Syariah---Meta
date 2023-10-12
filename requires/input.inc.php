<?
function generate_input_number($p_value,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_attrib=array(),$p_decimal=0){
	$lreturn="<input type=\"hidden\" name=\"".$p_kd_field."\" value=\"".$p_value."\"/><input type=\"text\" name=\"".$p_kd_field."_mask\" value=\"".convert_money("",$p_value,$p_decimal)."\" class='groove_text_numeric' ".(($p_arr_attrib["size"])?" size=\"".$p_arr_attrib["size"]."\"":"")." onKeyUp=\"fFormatNumberKeyUp(event,this,document.form1.".$p_kd_field.",'".$p_decimal."');fNextFocus(document.form1.".$p_next_kd_field.");".$p_arr_attrib["onkeyup"]."\" onKeyDown=\"return fFormatNumberKeyDown(event,this,document.form1.".$p_kd_field.");".$p_arr_attrib["onkeydown"]."\" onFocus=\"this.select();".$p_arr_attrib["onfocus"]."\" onChange=\"fFormatNumberKeyUp(event,this,document.form1.".$p_kd_field.",'".$p_decimal."');".$p_arr_attrib["onchange"]."\" onLoad=\"".$p_arr_attrib["onLoad"]."\">".chr(13);
	//".generate_attrib($p_arr_attrib,array("onkeyup","onkeydown","onchange","onfocus"))."
	return $lreturn;
}

function generate_input_date($p_value,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_attrib=array()){
	$lreturn="<input type=\"text\" name=\"".$p_kd_field."\" value=\"".$p_value."\" class=\"groove_text\" onKeyPress=\"if(event.keyCode==4) img_".$p_kd_field.".click();".$p_arr_attrib["onkeypress"]."\" onKeyUp=\"fNextFocus(document.form1.".$p_next_kd_field.");".$p_arr_attrib["onkeyup"]."\" ".generate_attrib($p_arr_attrib,array("onkeypress","onkeyup"))." size=\"10\">&nbsp;<img src=\"images/btn_extend.gif\" name=\"img_".$p_kd_field."\" onClick=\"fPopCalendar(document.form1.".$p_kd_field.",function(){".$p_arr_attrib["onchange"]."})\">".chr(13);
	//echo $p_arr_attrib["onkeyup"];
	return $lreturn;
}


function generate_input_get($p_value,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_fk_module,$p_arr_attrib=array(),$p_get_name="",$p_get_data_name=""){
	$lreturn="<input type=\"text\" id=\"".$p_kd_field."\" name=\"".$p_kd_field."\" value=\"".$p_value."\" class='groove_text' onKeyPress=\"if(event.keyCode==4) img_".$p_kd_field.".click();".$p_arr_attrib["onkeypress"]."\" onKeyUp=\"fNextFocus(document.form1.".$p_next_kd_field.")\" onchange=\"".$p_get_data_name."(".$p_kd_field.")\" ".$p_arr_attrib["onkeyup"].generate_attrib($p_arr_attrib,array("onkeypress","onkeyup")).">&nbsp;<img src=\"images/search.gif\" id=\"img_".$p_kd_field."\" onclick=\"".$p_get_name."(".(($p_is_submit=="t")?"true":"false").",'".$p_fk_module."','".$p_kd_field."','Ganti ".$p_nm_field."','document.form1.".$p_kd_field."','document.form1.".$p_next_kd_field."')\" style=\"border:0px\" align=\"absmiddle\">".chr(13);
	
	return $lreturn;
}

function generate_list_manual($p_value,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_list_text,$p_list_value,$p_arr_attrib=array(),$p_is_multiple){
	if($p_is_multiple=="f" ||$p_is_multiple==""){
		$lreturn="<select id=\"".$p_kd_field."\" name=\"".$p_kd_field."\" class=\"groove_text\" onKeyUp=\"fNextFocus(document.form1.".$p_next_kd_field.");".$p_arr_attrib["onkeyup"]."\"".generate_attrib($p_arr_attrib,array("onkeyup"))."><option value=\"\">-- ".$p_nm_field." --</option>";
		$l_arr_text=split(",",$p_list_text);
		$l_arr_value=split(",",$p_list_value);
		for ($l_index=0;$l_index<count($l_arr_text);$l_index++){
			$lreturn.="<option value=\"".$l_arr_value[$l_index]."\" ".(($p_value==$l_arr_value[$l_index])?"selected":"").">".$l_arr_text[$l_index]."</option>";
			//echo $l_index.".".$l_arr_value[$l_index];
		}
		//echo "aa".$p_value."<br>";
		$lreturn.="</select>";
	}
	else if($p_is_multiple=="t"){
		$lreturn="<select multiple name=\"".$p_kd_field."\" class=\"groove_text\" onKeyUp=\"fNextFocus(document.form1.".$p_next_kd_field.");".$p_arr_attrib["onkeyup"]."\"".generate_attrib($p_arr_attrib,array("onkeyup"))."><option value=\"\">-- ".$p_nm_field." --</option>";
		$l_arr_text=split(",",$p_list_text);
		$l_arr_value=split(",",$p_list_value);
		$val=explode(",",$p_value);
		for ($l_index=0;$l_index<count($l_arr_text);$l_index++){	
			$lreturn.="<option value=\"".$l_arr_value[$l_index]."\" ".((in_array($l_arr_value[$l_index],$val))?"selected":"").">".$l_arr_text[$l_index]."</option>";
		}	
		$lreturn.="</select>";
	}
	
	return $lreturn;
}

function generate_list_db($p_value,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_list_sql,$p_list_value,$p_list_text,$p_arr_attrib=array(),$p_is_multiple,$p_is_readonly='f',$p_session=NULL){
	if($p_is_multiple=="f" || $p_is_multiple==""){
		$lreturn="<select style='width:100%;max-width:70%;' id=\"".$p_kd_field."\" name=\"".$p_kd_field."\" class=\"groove_text\" onKeyUp=\"fNextFocus(document.form1.".$p_next_kd_field.");".$p_arr_attrib["onkeyup"]."\"".generate_attrib($p_arr_attrib,array("onkeyup"))."><option value=\"\">-- ".$p_nm_field." --</option>";
	}else if($p_is_multiple=="t"){
		$lreturn="<select multiple name=\"".$p_kd_field."\" class=\"groove_text\" onKeyUp=\"fNextFocus(document.form1.".$p_next_kd_field.");".$p_arr_attrib["onkeyup"]."\"".generate_attrib($p_arr_attrib,array("onkeyup"))."><option value=\"\">-- ".$p_nm_field." --</option>";
	}	
	eval("\$list_sql = \"$p_list_sql\";");
	$val=explode(",",$p_value);
/*	if($p_session){
		if($_SESSION["kd_cabang"]){
			if ($lwhere!="") $lwhere.=" and ";
			$lwhere.=" fk_cabang = '".$_SESSION["kd_cabang"]."' ";
		}
	}
	if ($lwhere!="") $lwhere=" where ".$lwhere;
*/
	$lrs=pg_query($list_sql." ".$lwhere);
	
	while ($lrow=pg_fetch_array($lrs)){
		$lreturn.="<option value=\"".$lrow[$p_list_value]."\" ".((in_array($lrow[$p_list_value],$val))?"selected":"").">".$lrow[$p_list_text]."</option>";
	}
	$lreturn.="</select>";
	return $lreturn;
}

function generate_input_text($p_value,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_attrib=array()){
	//echo $p_next_kd_field;
	$lreturn="<input type=\"text\" id=\"".$p_kd_field."\" name=\"".$p_kd_field."\" value=\"".$p_value."\" class='groove_text' onKeyUp=\"fNextFocus(document.form1.".$p_next_kd_field.");".$p_arr_attrib["onkeyup"]."\" ".generate_attrib($p_arr_attrib,array("onkeyup")).">".chr(13);
	//echo "aaa";
	return $lreturn;
}

function generate_input_password($p_value,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_attrib=array()){
	$lreturn="<input type=\"password\" name=\"".$p_kd_field."\" value=\"".$p_value."\" class='groove_text' onKeyUp=\"fNextFocus(document.form1.".$p_next_kd_field.");".$p_arr_attrib["onkeyup"]."\" ".generate_attrib($p_arr_attrib,array("onkeyup")).">".chr(13);
	return $lreturn;
}

function generate_input_memo($p_value,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_attrib=array()){
	$lreturn="<textarea name=\"".$p_kd_field."\" class='groove_text' rows='2' onKeyUp=\"fNextFocus(document.form1.".$p_next_kd_field.");".$p_arr_attrib["onkeyup"]."\"".generate_attrib($p_arr_attrib,array("onkeyup")).">".$p_value."</textarea>".chr(13);
	return $lreturn;
}

function generate_input_checkbox($p_value,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_attrib=array()){
	$lreturn="<input type=\"checkbox\" id=\"check_".$p_kd_field."\" name=\"".$p_kd_field."\" value='t' class='groove_checkbox' onKeyUp=\"fNextFocus(document.form1.".$p_next_kd_field.");".$p_arr_attrib["onkeyup"]."\"".generate_attrib($p_arr_attrib,array("onkeyup"))."".(($p_value=="t")?" checked":"").">".chr(13);
	return $lreturn;
}

function generate_input_checkbox_list($p_value,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_attrib=array(),$p_value_checkbox){
	$lreturn="<input type=\"checkbox\" name=\"".$p_kd_field."\" id=\"".$p_value_checkbox."\" value=\"".$p_value_checkbox."\" class='groove_checkbox' onKeyUp=\"fNextFocus(document.form1.".$p_next_kd_field.");".$p_arr_attrib["onkeyup"]."\"".generate_attrib($p_arr_attrib,array("onkeyup"))."".((strstr($p_value,$p_value_checkbox)!="")?" checked":"").">".chr(13);
	//echo $lreturn;
	return $lreturn;
}

function generate_input_range_date($p_value1,$p_value2,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_attrib=array()){
		$l_return="<input type=\"text\" id=\"".$p_kd_field."1\" name=\"".$p_kd_field."1\" class=\"groove_text\" value=\"".$p_value1."\" onKeyUp=\"fNextFocus(event,document.form1.".$p_kd_field."2);".$p_arr_attrib["onkeyup"]."\"".generate_attrib($p_arr_attrib,array("onkeyup"))."".(($p_value=="t")?" checked":"")." size=\"10\">&nbsp;<img src=\"images/btn_extend.gif\" width=\"13\" height=\"12\" onClick=\"fPopCalendar(document.form1.".$p_kd_field."1)\"> - <input type=\"text\" id=\"".$p_kd_field."2\" name=\"".$p_kd_field."2\" class=\"groove_text\" value=\"".$p_value2."\" onKeyUp=\"fNextFocus(event,document.form1.".$p_next_kd_field.");".$p_arr_attrib["onkeyup"]."\"".generate_attrib($p_arr_attrib,array("onkeyup"))."".(($p_value=="t")?" checked":"")." size=\"10\">&nbsp;<img src=\"images/btn_extend.gif\" width=\"13\" height=\"12\" onClick=\"fPopCalendar(document.form1.".$p_kd_field."2)\">";
	return $l_return;
}

function generate_input_range_value($p_value1,$p_value2,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_attrib=array()){
	$l_return="<input type=\"text\" name=\"".$p_kd_field."1\" onKeyPress=\"return alphaNumOnly(event,'numeric')\" class=\"groove_text_numeric\" value=\"".convert_html($p_value1)."\" onKeyUp=\"fNextFocus(event,document.form1.".$p_kd_field."2)".$p_arr_attrib["onkeyup"]."\" width=\"".$p_width."\"".generate_attrib($p_arr_attrib,array("onkeyup"))."> - <input type=\"text\" name=\"".$p_kd_field."2\" onKeyPress=\"return alphaNumOnly(event,'numeric')\" class=\"groove_text_numeric\" value=\"".convert_html($p_value2)."\" onKeyUp=\"fNextFocus(event,document.form1.".$p_next_kd_field.");".$p_arr_attrib["onkeyup"]."\"".generate_attrib($p_arr_attrib,array("onkeyup")).">";	
	return $l_return;
}

function generate_input_file($p_value,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_attrib=array()){
	$lreturn="<input type=\"file\" name=\"".$p_kd_field."\" value=\"".convert_html($p_value)."\" class='groove_text' onKeyUp=\"fNextFocus(document.form1.".$p_next_kd_field.");".$p_arr_attrib["onkeyup"]."\" ".generate_attrib($p_arr_attrib,array("onkeyup")).">".chr(13);
	if ($p_value!="") { ?> 
          <img src="images/upload/<?=$p_value?>" width="100px" height="100px">
	<? } 	
	//echo $p_kd_field;
	//showquery($lreturn);
	return $lreturn;
}





function generate_input($p_value_type,$p_data_type,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_attrib=array(),$p_arr_param=array(),$p_list_type="",$p_get_name="",$p_get_data_name="",$p_read_only="",$p_default_value="",$p_is_multiple="",$id_menu="",$p_is_numeric="",$p_type=NULL,$p_default_value_type="",$p_session=NULL){

	global $id_menu;
	
	if($p_type!='add' &&$p_default_value!=NULL &&$p_read_only=='t' ){
		$p_default_value=NULL;
		$p_data_type="readonly";	
		$p_value_type='reference';
	}
	if($p_default_value!=NULL &&$p_read_only=='t'){
		if($_REQUEST[$p_kd_field] && $_REQUEST[$p_kd_field]!=$p_default_value &&$p_default_value_type!="php"){
			$p_default_value=$_REQUEST[$p_kd_field];			
		}
		//print_r($p_arr_attrib);	
		
		$l_return="<input type=\"hidden\" name=\"".$p_kd_field."\" class='groove_text' value=\"".($p_default_value_type=="php"?eval("return $p_default_value;"):$p_default_value)."\"> <span id=\"div".$p_kd_field."\" ".generate_attrib_style($p_arr_attrib,array("onkeyup")).">".convert_html(($p_is_numeric=='t'?convert_money("",($p_default_value_type=="php"?eval("return $p_default_value;"):$p_default_value),2):($p_default_value_type=="php"?eval("return $p_default_value;"):$p_default_value)))."</span>";
	
	}/*else if($p_data_type=="readonly" && strstr($p_default_value,'date')!=""){
		echo eval("return $p_default_value;");
		//echo "123";
	}*/
	else if($p_value_type=='input' || $p_data_type=='others'){
		if($p_data_type=='text'){
			$l_return=generate_input_text($_REQUEST[$p_kd_field],$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_attrib);
			//print_r($p_arr_attrib);
			//echo $_REQUEST[$p_kd_field].'aa';
		} 
		if($p_data_type=='password'){
			$l_return=generate_input_password($_REQUEST[$p_kd_field],$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_attrib);
			//echo $_REQUEST[$p_kd_field];
		} 
		if($p_data_type=='file'){
			$l_return=generate_input_file($_REQUEST[$p_kd_field],$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_attrib);
			//echo $_REQUEST[$p_kd_field];
		}
		else if($p_data_type=='bigserial' || $p_data_type=='numeric'){			
			$l_return=generate_input_number(($_REQUEST[$p_kd_field])?$_REQUEST[$p_kd_field]:0,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_attrib,$p_arr_param["decimal"]);
		}else if($p_data_type=='date' || $p_data_type=='timestamp'){
			$l_return=generate_input_date($_REQUEST[$p_kd_field]?$_REQUEST[$p_kd_field]:eval("return $p_default_value;"),$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_attrib);
		}else if($p_data_type=='range_date'){
			$l_return=generate_input_range_date($_REQUEST[$p_kd_field."1"],$_REQUEST[$p_kd_field."2"],$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_attrib);
		}else if($p_data_type=='range_value'){
			//echo $_REQUEST[$p_kd_field."1"]." ".$p_arr_attrib."<br>";
			//echo $_REQUEST[$p_kd_field."2"]." ".$p_nm_field."<br>";
			$l_return=generate_input_range_value($_REQUEST[$p_kd_field."1"],$_REQUEST[$p_kd_field."2"],$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_attrib);
		}else if($p_data_type=='checkbox'){
			$l_return=generate_input_checkbox($_REQUEST[$p_kd_field],$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_attrib);
		}else if($p_data_type=='checkbox_list'){
			if($_REQUEST["pstatus"]=='edit'){
				$kd_field=str_replace("[]","",$p_kd_field);
				if(is_array($_REQUEST[$kd_field])){
					foreach($_REQUEST[$kd_field] as $arr_value){
						$request_value_checkbox_list.=$arr_value.",";	
					}
				}
			}else{	
				$kd_field=str_replace("[]","",$p_kd_field);
				if(is_array($_REQUEST[$kd_field])){
					foreach($_REQUEST[$kd_field] as $arr_value){
						$request_value_checkbox_list.=$arr_value.",";	
					}
				}
			}
			$text_checkbox=split(',',$p_arr_param["checkbox_manual_text"]);
			$value_checkbox=split(',',$p_arr_param["checkbox_manual_value"]);
						
			for($i=0;$i<count($text_checkbox);$i++){
				$l_return.=generate_input_checkbox_list($request_value_checkbox_list,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_attrib,$value_checkbox[$i])." ".$text_checkbox[$i];	
			}
		}else if($p_data_type=='list'){
			
			if($p_list_type=='list_db'){	
				if($p_is_multiple=="f" || $p_is_multiple==""){
					$l_return=generate_list_db($_REQUEST[$p_kd_field],$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_param["list_sql"],$p_arr_param["list_value"],$p_arr_param["list_text"],$p_arr_attrib,$p_is_multiple,$p_read_only,$p_session);
				} else{
					$kd_field=str_replace("[]","",$p_kd_field);
					if(is_array($_REQUEST[$kd_field])){
						foreach($_REQUEST[$kd_field] as $arr_value){
							$request_value_multiple_combobox.=$arr_value.",";	
						}
					}
					
					for($i = 0; $i < count($p_kd_field); $i++){
						$l_return=generate_list_db($request_value_multiple_combobox,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_param["list_sql"],$p_arr_param["list_value"],$p_arr_param["list_text"],$p_arr_attrib,$p_is_multiple,$p_read_only);
					}
					
				}
			}else if($p_list_type=='list_manual'){
				if($p_is_multiple=="f" || $p_is_multiple==""){
					$l_return=generate_list_manual($_REQUEST[$p_kd_field],$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_param["list_text"],$p_arr_param["list_value"],$p_arr_attrib,$p_is_multiple);
				}else if($p_is_multiple=="t"){
					$kd_field=str_replace("[]","",$p_kd_field);
					if(is_array($_REQUEST[$kd_field])){
						foreach($_REQUEST[$kd_field] as $arr_value){
							$request_value_multiple_combobox_manual.=$arr_value.",";	
						}
					}
					else {
						$request_value_multiple_combobox_manual.=$_REQUEST[$kd_field].",";						
					}
					$text_list_manual=split(',',$p_arr_param["list_text"]);
					$value_list_manual=split(',',$p_arr_param["list_value"]);
					for($i = 0; $i < count($text_list_manual); $i++){
						$l_return=generate_list_manual($request_value_multiple_combobox_manual,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_param["list_text"],$p_arr_param["list_value"],$p_arr_attrib,$p_is_multiple);
						//echo $_REQUEST[$p_kd_field];
						//echo $request_value_multiple_combobox_manual;
					}					
				}
				//echo $_REQUEST[$p_kd_field]."-".$p_kd_field."-".$p_nm_field."-".$p_next_kd_field."-".$p_arr_param["fk_module"]."-".$p_arr_attrib; 
			}else if($p_list_type=='get'){
				//echo $p_arr_param["fk_module"]."oo";
				$l_return=generate_input_get($_REQUEST[$p_kd_field],$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_param["fk_module"],$p_arr_attrib,$p_get_name,$p_get_data_name);
			}		
		}else if($p_data_type=='text_area'){
			$l_return=generate_input_memo($_REQUEST[$p_kd_field],$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_attrib);
		}else if($p_data_type=='others'){
			
			//echo "333";
			//$l_return=generate_input_memo($_REQUEST[$p_kd_field],$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_attrib);
			if(file_exists($_SERVER['DOCUMENT_ROOT']."/SINAR SENTOSA/website/includes/modal_input_".$id_menu.".inc.php")){
				include $_SERVER['DOCUMENT_ROOT']."/SINAR SENTOSA/website/includes/modal_input_".$id_menu.".inc.php";
				//echo $_REQUEST[$p_kd_field."2"];
				$l_return=generate_input_others($id_menu,$_REQUEST[$p_kd_field."1"],$_REQUEST[$p_kd_field."2"],$_REQUEST[$p_kd_field."3"],$p_kd_field,$p_nm_field,$p_next_kd_field,$p_arr_attrib);
				//$l_return=
			}
			else {
				//echo "456";
				echo $_SERVER['DOCUMENT_ROOT']."/SINAR SENTOSA/website/includes/modal_input_".$id_menu.".inc.php";
			}
		}
	}else if($p_value_type=='reference' && $p_data_type!='others'){
		if($p_data_type=='readonly' || $p_read_only=="t"){	
		    //print_r($p_arr_attrib);
			
		  // $_REQUEST[$p_kd_field]=str_replace("<br>"," - ",$_REQUEST[$p_kd_field]);	
			$l_return="<input type=\"hidden\" name=\"".$p_kd_field."\" class='groove_text' value=\"".$_REQUEST[$p_kd_field]."\"> <span id=\"div".$p_kd_field."\" ".generate_attrib_style($p_arr_attrib,array("onkeyup")).">".convert_html(($p_is_numeric=='t'?convert_money("",$_REQUEST[$p_kd_field],2):$_REQUEST[$p_kd_field]))."</span>";	
		}else{			
			$l_return="<input type=\"text\" name=\"".$p_kd_field."\" class='groove_text' value=\"".$_REQUEST[$p_kd_field]."\" onKeyUp=\"fNextFocus(event,document.form1.".$p_next_kd_field."\" ".$p_arr_attrib["onchange"]."\"".generate_attrib_style($p_arr_attrib,array("onkeyup","onkeydown","onchange","onfocus")).">";
		}
	}
	return $l_return;
}

function generate_attrib($p_arr_attrib,$p_arr_exception){
	if(is_array($p_arr_attrib)){
		foreach($p_arr_attrib as $l_key=>$l_value) {
			if (!in_array($l_key,$p_arr_exception)){
				if($l_value=="-")$l_return.=" ".$l_key;
				else $l_return.=" ".$l_key."=\"".$l_value."\"";
				
			}
		}
	}
	//echo $l_return;
	return $l_return;
}


function generate_attrib_style($p_arr_attrib,$p_arr_exception){
	if(is_array($p_arr_attrib)){
		foreach($p_arr_attrib as $l_key=>$l_value) {
			//echo $l_key;
			if (!in_array($l_key,$p_arr_exception))$l_return.=" ".$l_key.":".$l_value.";";
		}
	}
	$l_return="style='".$l_return."'";
	//echo $l_return;
	return $l_return;
}

?>