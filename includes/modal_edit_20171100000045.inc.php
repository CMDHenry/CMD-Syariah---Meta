<?

function cek_error_module(){
	global $strmsg,$l_success;
	
	$id_edit= $_REQUEST["id_edit"];
	$l_arr_row = split(chr(191),$_REQUEST["strisi_funding"]);
	$arr=array();
	for ($i=0; $i<count($l_arr_row)-1; $i++){
		$l_arr_col=split(chr(187),$l_arr_row[$i]);
		if(in_array($l_arr_col["0"],$arr)){
			$strmsg.="Data ".$l_arr_col["0"]." sudah ada <br>";		
		}
		$arr[$l_arr_col["0"]]=$l_arr_col["0"];
		$lrow_f=pg_fetch_array(pg_query("select * from data_fa.tblfunding_detail where fk_sbg='".$l_arr_col["0"]."' and tgl_unpledging is null and fk_funding!='".$id_edit."'"));

		if($lrow_f["fk_sbg"]){
			$strmsg.="Data ".$l_arr_col["0"]." sudah difunding di ".$lrow_f["fk_funding"]."<br>";		
		}
		
	}

}

?>

