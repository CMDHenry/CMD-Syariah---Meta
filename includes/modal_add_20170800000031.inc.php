<?
function query_additional($pk_id=NULL){
	global $l_success;
		
	$fk_cabang=$_REQUEST["fk_cabang"];
	$tbl="data_gadai.tblproduk_gadai";
	is_approval($fk_cabang,$_REQUEST["final_approval"],$tbl,$pk_id);
	if(!pg_query("update ".$tbl." set fk_user_input='".$_SESSION["username"]."' where no_sbg='".$pk_id."'")) $l_success=0;
	//$l_success=0;
}

function cek_error_module(){
	global $strmsg;

	$fk_produk=$_REQUEST["fk_produk"];
	$query="select * from tblproduk left join tblrate on kd_rate=fk_rate where kd_produk='".$fk_produk."'";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	
	$rate_flat_dirut=$lrow["rate_flat_dirut"];
	$rate_flat_direktur=$lrow["rate_flat_direktur"];
	$rate_flat=$_REQUEST["rate_flat"];
	$total_nilai_pinjaman=$_REQUEST["total_nilai_pinjaman"];
	$biaya_penyimpanan=$_REQUEST["biaya_penyimpanan"];
	$lama_pinjaman=$_REQUEST["lama_pinjaman"];
	$jumlah_hari=$lrow["jumlah_hari"];
	$uang_pinjaman_direktur=$lrow["uang_pinjaman_direktur"];
	$uang_pinjaman_dirut=$lrow["uang_pinjaman_dirut"];
	
	if($_REQUEST["approval_rate_flat"]=='Dirut' && $rate_flat>=$rate_flat_direktur){
		$strmsg.="Approval Rate Flat salah ,silakan ketik ulang rate flat agar tidak ke Dirut.<br>"; 
	}
	//echo $total_nilai_pinjaman.'<='.$uang_pinjaman_direktur;
	if($_REQUEST["approval_uang_pinjaman"]=='Dirut' && $total_nilai_pinjaman<=$uang_pinjaman_direktur){
		$strmsg.="Approval Uang Pinjaman salah ,silakan ketik ulang Uang Pinjaman agar tidak ke Dirut.<br>"; 
	}
	$bunga=round(($total_nilai_pinjaman*$lama_pinjaman*$rate_flat/100*(30/$jumlah_hari)));
	if($bunga!=$biaya_penyimpanan){
		$strmsg.="Biaya Penyimpanan salah ,silakan ketik ulang Rate.<br>"; 
	}
}


?>

