<?
function create_input_timestamp($p_value,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_is_submit){
	if ($p_is_submit=="t") $l_js="onChange=\"fChangeText('Ganti ".$p_nm_field."',".$p_next_kd_field.")\"";
	$lreturn="<input type=\"text\" size=\"2\" maxlength=\"2\" name=\"".$p_kd_field."\" value=\"".$p_value."\"".$l_js." onKeyUp=\"fNextFocus(document.form1.".$p_next_kd_field.")\">".getMonthName($_SESSION["month"],1)."&nbsp;".$_SESSION["year"].chr(13);
	return $lreturn;
}

function create_input_date($p_value,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_is_submit,$p_width){
	if ($p_is_submit=="t") $l_js="onChange=\"fChangeText('Ganti ".$p_nm_field."',".$p_next_kd_field.")\"";
	$lreturn="<input type=\"text\" name=\"".$p_kd_field."\" value=\"".$p_value."\" class='groove_text' size=\"".$p_width."\"".$l_js." onKeyUp=\"fNextFocus(document.form1.".$p_next_kd_field.")\" onKeyPress=\"if(event.keyCode==4) img_".$p_kd_field.".click()\">&nbsp;<img src=\"images/btn_extend.gif\" name=\"img_".$p_kd_field."\" onClick=\"fPopCalendar(document.form1.".$p_kd_field.",document.form1.".$p_kd_field.")\">".chr(13);
	return $lreturn;
}

function create_input_get($p_value,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_fk_module,$p_is_submit,$p_is_confirm,$p_width){
	if ($p_is_confirm=="t"){
		$l_js2="onclick=\"fGetWC(".(($p_is_submit=="t")?"true":"false").",'".$p_fk_module."','".$p_nm_field."','".$p_kd_field."','Ganti ".$p_nm_field."',".$p_kd_field.",".$p_next_kd_field.")\"";
		$l_js="onChange=\"fChangeTextWC('".$p_nm_field."','Ganti ".$p_nm_field."',".$p_next_kd_field.")\"";
	} else {
		$l_js2="onclick=\"fGetNC(".(($p_is_submit=="t")?"true":"false").",'".$p_fk_module."','".$p_kd_field."','Ganti ".$p_nm_field."',".$p_kd_field.",".$p_next_kd_field.")\"";
		if ($p_is_submit=="t"){
			$l_js.="onChange=\"fChangeText('Ganti ".$p_nm_field."',".$p_next_kd_field.")\"";
		}
	}
	$lreturn="<input type=\"text\" name=\"".$p_kd_field."\" value=\"".$p_value."\" class='groove_text' size=\"".$p_width."\" ".$l_js." onKeyPress=\"if(event.keyCode==4) img_".$p_kd_field.".click()\" onKeyUp=\"fNextFocus(document.form1.".$p_next_kd_field.")\">&nbsp;<img src=\"images/search.gif\" id=\"img_".$p_kd_field."\" ".$l_js2." style=\"border:0px\" align=\"absmiddle\">".chr(13);
	return $lreturn;
}

function create_list_manual($p_value,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_list_text,$p_list_value,$p_width){
	if ($p_is_submit=="t") $l_js="onChange=\"fChangeText('Ganti ".$p_nm_field."',".$p_next_kd_field.")\"";
	
	$lreturn="<select name=\"".$p_kd_field."\" class=\"groove_text\"".$l_submit." onKeyUp=\"fNextFocus(document.form1.".$p_next_kd_field.")\" width=\"".$p_width."\" ".$l_js."><option value=\"\">-- ".$p_nm_field." --</option>";
	$l_arr_text=split(",",$p_list_text);
	$l_arr_value=split(",",$p_list_value);
	for ($l_index;$l_index<count($l_arr_text);$l_index++){
		$lreturn.="<option value=\"".$l_arr_value[$l_index]."\" ".(($p_value==$l_arr_value[$l_index])?"selected":"").">".$l_arr_text[$l_index]."</option>";
	}
	$lreturn.="</select>";
	return $lreturn;
}

function create_list_db($p_value,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_list_sql,$p_list_value,$p_list_text,$p_is_submit,$p_width){
	if ($p_is_submit=="t") $l_js="onChange=\"fChangeText('Ganti ".$p_nm_field."',".$p_next_kd_field.")\"";

	$lreturn="<select name=\"".$p_kd_field."\" class=\"groove_text\"".$l_submit." onKeyUp=\"fNextFocus(document.form1.".$p_next_kd_field.")\" width=\"".$p_width."\" ".$l_js."><option value=\"\">-- ".$p_nm_field." --</option>";
	$lrs=pg_query($p_list_sql);
	while ($lrow=pg_fetch_array($lrs)){
		$lreturn.="<option value=\"".$lrow[$p_list_value]."\" ".(($p_value==$lrow[$p_list_value])?"selected":"").">".$lrow[$p_list_text]."</option>";
	}
	$lreturn.="</select>";
	return $lreturn;
}

function create_input($p_value,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_is_submit,$p_width){
	if ($p_is_submit=="t") $l_js="\"fChangeText('Ganti ".$p_nm_field."',".$p_next_kd_field.")\"";
	else $l_js="";
	$lreturn="<input type=\"text\" name=\"".$p_kd_field."\" value=\"".$p_value."\" class='groove_text' size=\"".$p_width."\"".$l_js." onKeyUp=\"fNextFocus(document.form1.".$p_next_kd_field.")\">".chr(13);
	return $lreturn;
}

function create_input_memo($p_value,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_is_submit,$p_width){
	if ($p_is_submit=="t") $l_js="\"fChangeText('Ganti ".$p_nm_field."',".$p_next_kd_field.")\"";
	else $l_js="";
	$lreturn="<textarea name=\"".$p_kd_field."\" class='groove_text' rows='5' cols=\"".$p_width."\"".$l_js." onKeyUp=\"fNextFocus(document.form1.".$p_next_kd_field.")\">".$p_value."</textarea>".chr(13);
	return $lreturn;
}

function create_input_checkbox($p_value,$p_kd_field,$p_nm_field,$p_next_kd_field,$p_is_submit,$p_width){
	if ($p_is_submit=="t") $l_js="\"fChangeText('Ganti ".$p_nm_field."',".$p_next_kd_field.")\"";
	else $l_js="";
	$lreturn="<input type=\"checkbox\" name=\"".$p_kd_field."\" value='t' class='groove_checkbox' size=\"".$p_width."\"".$l_js." onKeyUp=\"fNextFocus(document.form1.".$p_next_kd_field.")\" ".(($p_value=="t")?"checked":"").">".chr(13);
	return $lreturn;
}
?>