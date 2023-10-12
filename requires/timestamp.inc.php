<?
	function GetAge($Birthdate){//range thn 1971-2038
		$age = floor((time() - strtotime($Birthdate))/(60*60*24*365.2425));	
		return $age;
	}

	function getMonthName($p_number,$p_type=2){
		if ($p_type==1)
			$l_arr_name = array("January","February","March","April","May","June","July","August","September","October","November","December");
		else if($p_type==2)
			$l_arr_name = array("Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember");
		else if($p_type==3)
			$l_arr_name = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
		return $l_arr_name[$p_number-1];
	}
	
	function getDayName($p_number){
		$l_arr_name = array("Senin","Selasa","Rabu","Kamis","Jumat","Sabtu","Minggu");
		return $l_arr_name[$p_number-1];
	}	

	//function getDate($pBulan){
	//	return date("j",strtotime)
	//}
	
    function validate_date($str){ //mm:dd//yy
		$l_return=1;
		$str=str_replace("-","/",$str);
        if(preg_match("/^(\d{1,2}\/\d{1,2}\/(\d{2}|\d{4})|\d{2}-\d{2}-(\d{2}|\d{4}))$/",$str)){
            $lstrvaluedate = strtotime($str);
            $larrdate = split("/",$str);
            if (count($larrdate)<3){
                $larrdate = split("/",$str);
            }
			//if ($larrdate[2]<1971 || $larrdate[2]>2038) $l_return=0;
            if (!checkdate($larrdate[0],$larrdate[1],$larrdate[2])) $l_return=0;
        }else $l_return=0;

		return $l_return;
    }

	function validate_time($str){
		$larrtime= split(":",$str);
		if($larrtime[2]=="") $larrtime[2]="00";
		if(!preg_match("/^(\d{1,2}:\d{1,2}:\d{1,2})$/",$larrtime[0].":".$larrtime[1].":".$larrtime[2])){
			return false;
		}else{
			if($larrtime[0]>=24 || $larrtime[0]<0) return false;
			if($larrtime[1]>=60 || $larrtime[1]<0) return false;
			if($larrtime[2]>=60 || $larrtime[2]<0) return false;
		}
		return true;
	}

//	function validate_email($str){
//		$str = strtolower($str);
//		if(ereg("^([^[:space:]]+)@(.+)\.(ad|ae|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|cr|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|fi|fj|fk|fm|fo|fr|fx|ga|gb|gov|gd|ge|gf|gh|gi|gl|gm|gn|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|mv|mw|mx|my|mz|na|nato|nc|ne|net|biz|info|nf|ng|ni|nl|no|np|nr|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)$",$str)){
//			return 1;
//		} else {
//			return 0;
//		}
//	}
?>