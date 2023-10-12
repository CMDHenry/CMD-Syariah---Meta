<?php
	function convert_terbilang($p_number){
		if ($p_number==0) $l_result="Nol";
		else {
			$l_index=0;
			$l_format=number_format($p_number);
			$l_seg_number=split(",",$l_format);
			$l_prefix_ribuan=array(""," Ribu"," Juta"," Milyar"," Triliun"," Biliun");
			$l_result="";
			for ($l_index=0;$l_index<count($l_seg_number);$l_index++){
				$l_ratusan=convert_ratusan($l_seg_number[$l_index]);
				if ($l_ratusan!="") {
					$l_result.=$l_ratusan;
					$l_result.=$l_prefix_ribuan[count($l_seg_number)-($l_index+1)];
				}
				
				if($l_index==0 && $p_number<2000){
					$l_result = str_replace("Satu Ribu","Seribu",$l_result);
				}
			}
		}
		$l_result = str_replace("Satu Ratus","Seratus",$l_result);
		return $l_result;
		//$l_result=str_replace("Satu Ribu","Seribu",$l_result);
		//return str_replace("Satu Ratus","Seratus",$l_result);
	}
	
	function convert_ratusan($p_number){
		if ($p_number>0){
			$l_prefix_ratusan=array(""," Puluh"," Ratus");
			$l_angka=array(""," Satu"," Dua"," Tiga"," Empat"," Lima"," Enam"," Tujuh"," Delapan"," Sembilan"," Sepuluh"," Sebelas"," Dua Belas"," Tiga Belas"," Empat Belas"," Lima Belas"," Enam Belas","Tujuh Belas"," Delapan Belas"," Sembilan_Belas");
			$l_result="";
			$l_index=0;
			while ($p_number!=""){
				$p_number=number_format($p_number);
				$l_length=strlen($p_number);
				$l_num=substr($p_number,0,1);
				if ($l_length==2) {
					if ($l_num<2) {
						$l_num=substr($p_number,0,$l_length);
						$l_result.=$l_angka[$l_num];
						break;
					} else {
						$l_result.=$l_angka[$l_num];
					}
				} else {
					$l_result.=$l_angka[$l_num];
				}
				$l_result.=$l_prefix_ratusan[$l_length-1];
				$p_number=substr($p_number,1,$l_length);
				$l_index++;
			}
		}
		return $l_result;
	}
	
	function convert_terbilang_eng($p_number){
		if ($p_number==0) $l_result="Nol";
		else {
			$l_index=0;
			$l_format=number_format($p_number);
			$l_seg_number=split(",",$l_format);
			$l_prefix_ribuan=array(""," Thousand"," Million"," Billion"," Trillion");
			$l_result="";
			for ($l_index=0;$l_index<count($l_seg_number);$l_index++){
				$l_ratusan=convert_ratusan_eng($l_seg_number[$l_index]);
				if ($l_ratusan!="") {
					$l_result.=$l_ratusan;
					$l_result.=$l_prefix_ribuan[count($l_seg_number)-($l_index+1)];
				}
			}
		}
		$l_arr_search=array('Eightty','Twoty');
		$l_arr_replace=array('Eighty','Twenty');		
		return str_replace($l_arr_search,$l_arr_replace,$l_result);
	}
	
	function convert_ratusan_eng($p_number){
		if ($p_number>0){
			$l_prefix_ratusan=array("","ty"," Hundred");
			$l_angka=array(""," One"," Two"," Three"," Four"," Five"," Six"," Seven"," Eight"," Nine"," Ten"," Eleven"," Twelve"," Thriteen"," Fourteen"," Fifteen"," Sixteen","Seventeen"," Eighteen"," Nineteen");
			$l_result="";
			$l_index=0;
			while ($p_number!=""){
				$p_number=number_format($p_number);
				$l_length=strlen($p_number);
				$l_num=substr($p_number,0,1);
				if ($l_length==2) {
					if ($l_num<2) {
						$l_num=substr($p_number,0,$l_length);
						$l_result.=$l_angka[$l_num];
						break;
					} else {
						$l_result.=$l_angka[$l_num];
					}
				} else {
					$l_result.=$l_angka[$l_num];
				}
				$l_result.=$l_prefix_ratusan[$l_length-1];
				$p_number=substr($p_number,1,$l_length);
				$l_index++;
			}
		}
		return $l_result;
	}
?>