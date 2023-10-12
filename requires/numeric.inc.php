<?
function check_type($p_type,$p_str){
	switch($p_type){
		case 'integer' :
			if(!is_numeric($p_str)){
				$errmsg.="is not a numeric digit";
			}else{
				if((float)$p_str!=0){
					$temp=floor((float)$p_str)/(float)$p_str;
					if($temp!=1) $errmsg.="is not an integer";
				}
			}
			return $errmsg;
		break;
		case 'float' : 
			if(!is_numeric($p_str)){
				$errmsg.="is not a numeric digit";
			}
			return $errmsg;
		break;
	}
}

function convert_money($p_currency,$p_int,$p_point=0,$p_sign=false){	
	if($p_int!=""){
		if($p_int >= 0 && $p_currency)	return $p_currency." ".number_format($p_int,$p_point);
		else if($p_int >= 0 && !$p_currency) return number_format($p_int,$p_point);
		else{
			if($p_sign)return $p_currency." -".number_format(($p_int*-1),$p_point);
			else return $p_currency." (".number_format(($p_int*-1),$p_point).")";
		}
	} else return 0;
}

function create_input_number($p_value,$p_kd_field,$p_nm_field,$p_on_keyup="",$p_attrib="",$p_width="",$p_decimal=0,$p_is_submit=false,$p_is_confirm=false){
	if ($p_is_confirm==true){
		$l_js="onChange=\"fChangeTextWC('".$p_nm_field."','Ganti ".$p_nm_field."',".$p_next_kd_field.")\"";
	} else {
		if ($p_is_submit==true){
			$l_js.="onChange=\"fChangeText('Ganti ".$p_nm_field."',".$p_next_kd_field.")\"";
		}
	}
	//echo "aaa".convert_money("",$p_value,$p_decimal);
	//echo "bb".$p_decimal;
	$lreturn="<input type=\"hidden\" name=\"".$p_kd_field."\" value=\"".(($p_value=='')?0:$p_value)."\"/><input type=\"text\" name=\"".$p_kd_field."_mask\" value=\"".convert_money("",$p_value,$p_decimal)."\" class='groove_text_numeric'".(($p_width)?" size=\"".$p_width."\"":"")." ".$l_js." onKeyUp=\"fFormatNumberKeyUp(event,this,document.form1.".$p_kd_field.");".$p_on_keyup."\" onkeydown=\"return fFormatNumberKeyDown(event,this,document.form1.".$p_kd_field.")\"".(($p_attrib)?$p_attrib:" style=\"width:".$p_width."%\" ")." onfocus=\"this.select()\">".chr(13);
	echo $lreturn;
}

?>