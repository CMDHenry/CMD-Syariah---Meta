<?
function cek_error_module(){
	global $strmsg,$l_success;
	
	if($_REQUEST["status_fatg"]=='Perpanjangan'){
		$l_arr_row = split(chr(191),$_REQUEST["strisi_taksir"]);
		for ($i=0; $i<count($l_arr_row)-1; $i++){
			$l_arr_col=split(chr(187),$l_arr_row[$i]);
			$fk_barang[$i]=$l_arr_col[0];
			$jumlah[$i]=$l_arr_col[2];
		}

		$lrs=pg_query("select * from data_gadai.tbltaksir_detail
		where fk_fatg = '".$_REQUEST["no_fatg"]."' ");
		$i=0;
		while($lrow=pg_fetch_array($lrs)){
			if($lrow["fk_barang"]!=$fk_barang[$i]){
				$strmsg.="@Detail line-".($i+1).": Kode Barang tidak boleh diubah jika perpanjangan.<br>";	
			}
			if($lrow["jumlah"]!=$jumlah[$i]){
				$strmsg.="@Detail line-".($i+1).": Jumlah Barang tidak boleh diubah jika perpanjangan.<br>";	
			}
			$i++;
		}	
	}
	
	$lwhere="where fk_fatg='".$_REQUEST["no_fatg"]."' and status_data!='Batal' and status_approval!='Batal' ";
	
	if(pg_num_rows(pg_query("
		select fk_fatg from data_gadai.tblproduk_gadai
		$lwhere
		union all
		select fk_fatg from data_gadai.tblproduk_cicilan
		$lwhere
	"))){
		$strmsg.="FATG sudah dibuat SBG.<br>";	
	}
	//$l_success=0;
}

?>

