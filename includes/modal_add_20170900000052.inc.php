<?
function cek_error_module(){
	global $strmsg,$l_success;
	$l_arr_row = split(chr(191),$_REQUEST["strisi_taksir_umum"]);
	for ($i=0; $i<count($l_arr_row)-1; $i++){
		$l_arr_col=split(chr(187),$l_arr_row[$i]);
		if($l_arr_col[4]==""||$l_arr_col[4]==NULL){
			//$strmsg.="@Detail line-".($i+1).": No Seri tidak boleh kosong.<br>";	
		}
	}	
	
	if($_REQUEST["status_barang"]=='Baru' && $_REQUEST["no_mesin"]){
		
		if(pg_num_rows(pg_query("select * from data_gadai.tbltaksir_umum where no_mesin='".$_REQUEST["no_mesin"]."' and status_barang='Baru' and status_taksir!='Batal'"))){
			$strmsg.="No Mesin sudah terdaftar untuk unit baru.<br>";
		}
	}
	
	if($_REQUEST["status_barang"]=='Baru' && $_REQUEST["no_rangka"]){
		if(pg_num_rows(pg_query("select * from data_gadai.tbltaksir_umum where no_rangka='".$_REQUEST["no_rangka"]."' and status_barang='Baru' and status_taksir!='Batal'"))){
			$strmsg.="No Rangka sudah terdaftar untuk unit baru.<br>";
		}
	}	
}


function save_additional(){


}
?>

