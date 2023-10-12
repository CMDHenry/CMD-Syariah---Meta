<?
function convert_money_view($p_currency,$p_int,$p_point=0,$p_sign=false){
	if($p_int!=""){
		if($p_int >= 0)	return $p_currency." ".number_format($p_int,$p_point);
		else{
			if($p_sign)return $p_currency." -".number_format(($p_int*-1),$p_point);
			else return $p_currency." (".number_format(($p_int*-1),$p_point).")";
		}
	} else return 0;
}
function convert_html($p_str,$p_type="",$p_length=0){
	$l_temp = htmlspecialchars($p_str);
	if ($p_type=="text"){
		$l_temp = str_replace("<br>", "<br>", $l_temp);
		if ($p_length){
			$l_length_word = 0;
			for($i=0;$i<strlen($l_temp);$i++){
				if($l_temp[$i]==" ")
					$l_length_word = 0;
				else if($l_temp[$i].$l_temp[$i+1].$l_temp[$i+2].$l_temp[$i+3]=="<br>"){
					$l_length_word = 0;
					$i = $i+3;
				}else {
					$l_length_word++;
					if ($l_length_word>$p_length){
						$l_temp=substr($l_temp,0,$i)."<br>".substr($l_temp,$i,strlen($p_str));
						$i = $i+3;
						$l_length_word = 0;
					}
				}
			}
		}
	}
	return stripslashes($l_temp);
}

function convert_sql($p_str){
	return stripcslashes(str_replace("'","''",$p_str));
}

function convert_dob($p_dob,$p_sign='/'){
	if($p_dob!=""){
		list($y,$m,$d) = explode('-',substr($p_dob,0,10));
		return $m.$p_sign.$d.$p_sign.$y;
	}
}

function fputcsv($filePointer,$listArray,$delimiter=",",$enclosure='"'){
	// Write a line to a file
	// $filePointer = the file resource to write to
	// $dataArray = the data to write out
	// $delimeter = the field separator
	
	// Build the string
	$string = "";
	
	// No leading delimiter
	foreach($listArray as $dataArray){
		$writeDelimiter = FALSE;
		foreach($dataArray as $dataElement){
			// Replaces a double quote with two double quotes
			$dataElement=str_replace("\"", "\"\"", $dataElement);
			
			// Adds a delimiter before each field (except the first)
			if($writeDelimiter) $string .= $delimiter;
			
			// Encloses each field with $enclosure and adds it to the string
			$string .= $enclosure . $dataElement . $enclosure;
			
			// Delimiters are used every time except the first.
			$writeDelimiter = TRUE;
		} // end foreach
	
		// Append new line
		$string .= "\n";
	} // end foreach
	
	// Write the string to the file
	fwrite($filePointer,$string);
}

function format_data($p_value,$p_type,$p_format=""){
	
	switch($p_type) {
		case "date":
			$date=split(" ",$p_value);
			$date1=split("-",$date[0]);			
			if((int)($date1[0])<1970||(int)($date1[0])>2099){ 
				$date2=$date1[2]."/".$date1[1]."/".$date1[0]; 
				$l_return=$date2;
			}else{  
				$l_return=date($p_format,strtotime($p_value));
			}
			
			break;
		case "checkbox_list":
			$l_return=split(",",$p_value);
			break;
		case "text" :
			$l_return=$p_value;
			break;
		case "text_area" :
			$l_return=$p_value;
			break;
		case "readonly" :
			$l_return=$p_value;
			break;
		default:
			if(strstr($p_value,",")){
				//$l_return=split(",",$p_value);
				$l_return=$p_value;
			
			}
			else $l_return=$p_value;
			//echo $p_value;
			//$l_return=$p_value;
			break;
	}
	return $l_return;
}

function format_data_view($p_value,$p_type,$p_format=""){
	//echo $p_type."--";
	switch($p_type) {
		case "date":
			$date=split(" ",$p_value);
			$date1=split("-",$date[0]);			
			if((int)($date1[0])<1970 || (int) ($date1[0])>=2100){ 
				$date2=$date1[2]."/".$date1[1]."/".$date1[0]; 
				$l_return=$date2;
			}else {  
				$l_return=date($p_format,strtotime($p_value));
			}
			
			break;
		case "checkbox_list":
			$l_return=split(",",$p_value);
			break;
		case "numeric":
			$l_return=convert_money_view(" ",$p_value,2);
			break;
		case "checkbox":
			//if(!$p_value) $p_value="f";
			//$l_return=$p_value;
			$l_return="<img src=\"images/".(($p_value=="t")?"true":"false").".gif\"";
			break;
		//case "list_db" :
		
		//break;	
		default:
			//$l_return=split(",",$p_value);
			$l_return=$p_value;
			$p_value;
			break;
	}
	//echo $p_value;
	//echo $l_return;
	return $l_return;
}


function do_link($p_id,$p_modal,$p_property='dialogwidth:825px;dialogheight:405px'){
		?>
        <a href="#" class="blue" onclick="show_modal('modal_<?=convert_html($p_modal)?>.php?pstatus=edit&id_edit='+escape('<?=convert_html($p_id)?>'),'<?=$p_property?>')"><?=convert_html($p_id)?></a>
		<?
	}
function convert_data($pData,$pField){
	$lField=split('_',$pField);
	if($lField[0]=='tgl' && $pData!=""){
		return date('m/d/Y',strtotime($pData));
	}elseif(!check_type(float,$pData) && $lField[1] != 'pos' && $lField[0] != 'kd'){
		return convert_money("",$pData);
	}else return $pData;
}

function romanic_number($integer, $upcase = true) 
{ 
    $table = array('M'=>1000, 'CM'=>900, 'D'=>500, 'CD'=>400, 'C'=>100, 'XC'=>90, 'L'=>50, 'XL'=>40, 'X'=>10, 'IX'=>9, 'V'=>5, 'IV'=>4, 'I'=>1); 
    $return = ''; 
    while($integer > 0) 
    { 
        foreach($table as $rom=>$arb) 
        { 
            if($integer >= $arb) 
            { 
                $integer -= $arb; 
                $return .= $rom; 
                break; 
            } 
        } 
    } 
    return $return; 
} 


function sort_array_uniq(&$p_arr1,&$p_arr2,&$p_arr3,&$p_arr4,&$p_arr5){
	$l_counter1=0;
	while($l_counter1<=count($p_arr1)-2){
		$l_counter2=0;
		while($l_counter2<=count($p_arr1)-2 - $l_counter1){
			if ($p_arr1[$l_counter2] > $p_arr1[$l_counter2+1]){
				$temp = $p_arr1[$l_counter2];
				$p_arr1[$l_counter2] = $p_arr1[$l_counter2+1];
				$p_arr1[$l_counter2+1] = $temp;
				
				$temp=$p_arr2[$l_counter2];
				$p_arr2[$l_counter2]=$p_arr2[$l_counter2+1];
				$p_arr2[$l_counter2+1]=$temp;
				
				$temp=$p_arr3[$l_counter2];
				$p_arr3[$l_counter2]=$p_arr3[$l_counter2+1];
				$p_arr3[$l_counter2+1]=$temp;
				
				$temp=$l_arr_mau_distribusi[$l_counter2];
				$l_arr_mau_distribusi[$l_counter2]=$l_arr_mau_distribusi[$l_counter2+1];
				$l_arr_mau_distribusi[$l_counter2+1]=$temp;
				
				$temp=$p_arr4[$l_counter2];
				$p_arr4[$l_counter2]=$p_arr4[$l_counter2+1];
				$p_arr4[$l_counter2+1]=$temp;
				
				if ($p_arr5) {
					$temp=$p_arr5[$l_counter2];
					$p_arr5[$l_counter2]=$p_arr5[$l_counter2+1];
					$p_arr5[$l_counter2+1]=$temp;
				}
			}
			$l_counter2++;
		}
		$l_counter1++;
	}		
}


?>
