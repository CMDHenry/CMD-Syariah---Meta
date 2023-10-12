<?
function cek_error_field($p_data_type,$p_kd_field,$p_nm_field,$p_require,$p_condition_require="",&$p_strmsg,&$p_j_action,$p_tab="",$p_no_space='f',$p_check_numeric='f'){
	//echo $p_data_type."<br>";
	//echo $p_kd_field."<br>";
	///echo $_REQUEST[$p_kd_field]."<br>";
	//echo strstr($p_data_type,"Range");
	if (strstr($p_data_type,"range")) {
		//echo "abc";
		//echo strstr($p_data_type,"Range");
		$l_value1=$_REQUEST[$p_kd_field."1"];
		$l_value2=$_REQUEST[$p_kd_field."2"];
		if ($p_condition_require) {
			eval("\$l_result_condition_require =".$p_condition_require.";");
			//echo
			if ($l_result_condition_require) {
				if($l_value1=="" && $p_require=='t'){
					$p_strmsg.=$p_nm_field.' Awal Kosong<br>';
					if(!$p_j_action) {
						$p_j_action=(($p_tab)?"fSwitch('div".$p_tab."',document.getElementById('a_tab_".$p_tab."'));":"");						
						$p_j_action.="document.form1.".$p_kd_field."1.focus()";
					}
				}
				if($l_value2=="" && $p_require=='t'){
					$p_strmsg.=$p_nm_field.' Akhir Kosong<br>';
					if(!$p_j_action) {
						$p_j_action=(($p_tab)?"fSwitch('div".$p_tab."',document.getElementById('a_tab_".$p_tab."'));":"");						
						$p_j_action.="document.form1.".$p_kd_field."2.focus()";
					}
				}
			}
		} else {
			if($l_value1=="" && $p_require=='t'){
				$p_strmsg.=$p_nm_field.' Awal Kosong<br>';
				if(!$p_j_action) {
					$p_j_action=(($p_tab)?"fSwitch('div".$p_tab."',document.getElementById('a_tab_".$p_tab."'));":"");						
					$p_j_action.="document.form1.".$p_kd_field."1.focus()";
				}
			}
			if($l_value2=="" && $p_require=='t'){
				$p_strmsg.=$p_nm_field.' Akhir Kosong<br>';
				if(!$p_j_action) {
					$p_j_action=(($p_tab)?"fSwitch('div".$p_tab."',document.getElementById('a_tab_".$p_tab."'));":"");						
					$p_j_action.="document.form1.".$p_kd_field."2.focus()";
				}
			}
		}
		if ($l_value1!="" && $l_value2!="") {
			switch ($p_data_type){
				case "range_date":
					if (!validate_date(convert_date_english($l_value1))) {
						$p_strmsg.=$p_nm_field.' Awal Harus Tanggal<br>';
						if(!$p_j_action) {
							$p_j_action=(($p_tab)?"fSwitch('div".$p_tab."',document.getElementById('a_tab_".$p_tab."'));":"");						
							$p_j_action.="document.form1.".$p_kd_field."1.focus()";
						}
					}
					if (!validate_date(convert_date_english($l_value2))) {
						$p_strmsg.=$p_nm_field.' Akhir Harus Tanggal<br>';
						if(!$p_j_action) {
							$p_j_action=(($p_tab)?"fSwitch('div".$p_tab."',document.getElementById('a_tab_".$p_tab."'));":"");						
							$p_j_action.="document.form1.".$p_kd_field."2.focus()";
						}
					}
					//echo $p_strmsg;
					//echo strtotime($l_value1).">".strtotime($l_value2);
					if (!$p_strmsg) {
						//echo $l_value1." ".$l_value2."<br>";
						//echo date("Y/m/d",$l_value1).">".date("Y/m/d",$l_value2);
						 $tgl_1=substr($l_value1,6,4).substr($l_value1,3,2).substr($l_value1,0,2);
						 $tgl_2=substr($l_value2,6,4).substr($l_value2,3,2).substr($l_value2,0,2);
						// echo $tgl_1.">".$tgl_2;
						if ($tgl_1>$tgl_2) {
							//echo "aaaa";
							//echo $tgl_1.">".$tgl_2;
							$p_strmsg.=$p_nm_field.' Akhir Harus Lebih Besar Dari Awal<br>';
							if(!$p_j_action) {
								$p_j_action=(($p_tab)?"fSwitch('div".$p_tab."',document.getElementById('a_tab_".$p_tab."'));":"");						
								$p_j_action.="document.form1.".$p_kd_field."1.focus()";
							}
						}
					}
					break;
				case "range_value":
					if (!$p_strmsg) {
						if($l_value1>$l_value2){
							$p_strmsg.=$p_nm_field.' Akhir Harus Lebih Besar Dari Awal<br>';
						}
						if(!$p_j_action) {
							$p_j_action=(($p_tab)?"fSwitch('div".$p_tab."',document.getElementById('a_tab_".$p_tab."'));":"");						
							$p_j_action.="document.form1.".$p_kd_field."1.focus()";
						}
					}
					break;
			}
		}
	}else if($p_data_type=="others"){ 
		$l_value1=$_REQUEST[$p_kd_field."1"];
		$l_value2=$_REQUEST[$p_kd_field."2"];
		$l_value3=$_REQUEST[$p_kd_field."3"];
		if ($p_condition_require) {
			eval("\$l_result_condition_require =".$p_condition_require.";");
			if ($l_result_condition_require) {
				if($l_value1=="" && $p_require=='t'){
					$p_strmsg.=$p_nm_field.' Head Kosong<br>';
					if(!$p_j_action) {
						$p_j_action=(($p_tab)?"fSwitch('div".$p_tab."',document.getElementById('a_tab_".$p_tab."'));":"");						
						$p_j_action.="document.form1.".$p_kd_field."1.focus()";
					}
				}
				if($l_value2=="" && $p_require=='t'){
					$p_strmsg.=$p_nm_field.' Sub Kosong<br>';
					if(!$p_j_action) {
						$p_j_action=(($p_tab)?"fSwitch('div".$p_tab."',document.getElementById('a_tab_".$p_tab."'));":"");						
						$p_j_action.="document.form1.".$p_kd_field."2.focus()";
					}
				}
				if($l_value3=="" && $p_require=='t'){
					$p_strmsg.=$p_nm_field.' Detail Kosong<br>';
					if(!$p_j_action) {
						$p_j_action=(($p_tab)?"fSwitch('div".$p_tab."',document.getElementById('a_tab_".$p_tab."'));":"");						
						$p_j_action.="document.form1.".$p_kd_field."3.focus()";
					}
				}
			}
			}else{
				if($l_value1=="" && $p_require=='t'){
					$p_strmsg.=$p_nm_field.' Head Kosong<br>';
					if(!$p_j_action) {
						$p_j_action=(($p_tab)?"fSwitch('div".$p_tab."',document.getElementById('a_tab_".$p_tab."'));":"");						
						$p_j_action.="document.form1.".$p_kd_field."1.focus()";
					}
				}
				if($l_value2=="" && $p_require=='t'){
					$p_strmsg.=$p_nm_field.' Sub Kosong<br>';
					if(!$p_j_action) {
						$p_j_action=(($p_tab)?"fSwitch('div".$p_tab."',document.getElementById('a_tab_".$p_tab."'));":"");						
						$p_j_action.="document.form1.".$p_kd_field."2.focus()";
					}
				}
				if($l_value3=="" && $p_require=='t'){
					$p_strmsg.=$p_nm_field.' Detail Kosong<br>';
					if(!$p_j_action) {
						$p_j_action=(($p_tab)?"fSwitch('div".$p_tab."',document.getElementById('a_tab_".$p_tab."'));":"");						
						$p_j_action.="document.form1.".$p_kd_field."3.focus()";
					}
				}
		}
	}else {
		$p_kd_field=str_replace("[]","",$p_kd_field);
		if($p_data_type=="file") $l_value = $_FILES[$p_kd_field]['name'];
		else $l_value=trim($_REQUEST[$p_kd_field]);
		if ($p_condition_require) {
			eval("\$l_result_condition_require =".$p_condition_require.";");
			if ($l_result_condition_require) {
				echo $l_value;
				if($l_value=="" && $p_require=='t'){
					
					$p_strmsg.=$p_nm_field.' Kosong<br>';
					
					if(!$p_j_action) {
						$p_j_action=(($p_tab)?"fSwitch('div".$p_tab."',document.getElementById('a_tab_".$p_tab."'));":"");						
						$p_j_action.="document.form1.".$p_kd_field.".focus()";
					}
				}
			}
		} else {
			if($l_value=="" && $p_require=='t'){
				//cek error require di skeleton
				$p_strmsg.=$p_nm_field.' Kosong<br>';
				if(!$p_j_action) {
					$p_j_action=(($p_tab)?"fSwitch('div".$p_tab."',document.getElementById('a_tab_".$p_tab."'));":"");						
					$p_j_action.="document.form1.".$p_kd_field.".focus()";
				}
			}
		}
		if ($p_no_space=='t' && preg_match('/\s/',$l_value)) {
				$p_strmsg.=$p_nm_field.' Tidak Boleh Ada Spasi<br>';
				if(!$p_j_action) {
					$p_j_action=(($p_tab)?"fSwitch('div".$p_tab."',document.getElementById('a_tab_".$p_tab."'));":"");						
					$p_j_action.="document.form1.".$p_kd_field.".focus()";
				}
		}
		if ($p_check_numeric=='t' && !is_numeric($l_value) && $l_value!="") {
				$p_strmsg.=$p_nm_field.' Harus Angka<br>';
				if(!$p_j_action) {
					$p_j_action=(($p_tab)?"fSwitch('div".$p_tab."',document.getElementById('a_tab_".$p_tab."'));":"");						
					$p_j_action.="document.form1.".$p_kd_field.".focus()";
				}
		}		
		if ($l_value!="") {
			switch ($p_data_type){
				case "numeric":
					if (!is_numeric($l_value)) {
						$p_strmsg.=$p_nm_field.' Harus Angka<br>';
						if(!$p_j_action) {
							$p_j_action=(($p_tab)?"fSwitch('div".$p_tab."',document.getElementById('a_tab_".$p_tab."'));":"");						
							$p_j_action.="document.form1.".$p_kd_field.".focus()";
						}
					}
					break;
				case "date":
					if (!validate_date(convert_date_english($l_value))) {
						$p_strmsg.=$p_nm_field.' Harus Tanggal<br>';
						if(!$p_j_action) {
							$p_j_action=(($p_tab)?"fSwitch('div".$p_tab."',document.getElementById('a_tab_".$p_tab."'));":"");						
							$p_j_action.="document.form1.".$p_kd_field.".focus()";
						}
					}
					break;
			}
			
		}
	}
}

function cek_error_field_detail($p_value,$p_data_type,$p_nm_field,$p_require,$p_condition_require="",&$p_strmsg,&$p_j_action,$p_preifx_err_msg=""){
	if ($p_condition_require) {
		eval("\$l_result_condition_require =".$p_condition_require.";");
		if ($l_result_condition_require) {
			if($p_value=="" && $p_require=='t'){
				$p_strmsg.=$p_preifx_err_msg.$p_nm_field.' Kosong<br>';
				if(!$p_j_action) $p_j_action="document.form1.".$p_kd_field.".focus()";
			}
		}
	} else {
		if($p_value=="" && $p_require=='t'){
			$p_strmsg.=$p_preifx_err_msg.$p_nm_field.' Kosong<br>';
			if(!$p_j_action) $p_j_action="document.form1.".$p_kd_field.".focus()";
		}
	}
	if ($p_value!="") {
		//echo "aaa".$p_value.'sdf';
		switch ($p_data_type){
			
			case "numeric":
				if (!is_numeric($p_value)) {
					$p_strmsg.=$p_preifx_err_msg.$p_nm_field.' Harus Angka<br>';
					if(!$p_j_action) $p_j_action="document.form1.".$p_kd_field.".focus()";						
				}
				break;
			case "date":
				if (!validate_date($p_value)) {
					$p_strmsg.=$p_preifx_err_msg.$p_nm_field.' Harus Tanggal<br>';
					if(!$p_j_action) $p_j_action="document.form1.".$p_kd_field.".focus()";
				}
				break;
		}
		
	}
}
?>