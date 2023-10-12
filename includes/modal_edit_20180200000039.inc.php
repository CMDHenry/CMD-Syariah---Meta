<?
//ADVANCE EKSTERNAL

function cek_error_module(){
	global $j_action,$strmsg,$nama_menu;
	if($nama_menu=='Penyelesaian'){
		$no_voucher=$_REQUEST["no_voucher"];		
		$tbl="data_fa.tbladvance";
		$lwhere="no_voucher='".$no_voucher."'";
		
		$fk_bank=$_REQUEST["fk_bank"];		
		$fk_bank_old=get_rec($tbl,"fk_bank",$lwhere);
		if($fk_bank!=$fk_bank_old)$strmsg.="Bank tidak boleh di-edit waktu penyelesaian.<br>";
		
		$fk_bank_ho=$_REQUEST["fk_bank_ho"];
		$fk_bank_ho_old=get_rec($tbl,"fk_bank_ho",$lwhere);
		if($fk_bank_ho!=$fk_bank_ho_old)$strmsg.="Bank tidak boleh di-edit waktu penyelesaian.<br>";
	
	}
}


?>

