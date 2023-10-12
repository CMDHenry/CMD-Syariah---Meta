<?
//MTS BLANKO
function cek_error_module(){
	global $strmsg,$j_action;
	
	$fk_cabang_asal=$_REQUEST["fk_cabang_asal"];
	$fk_cabang_tujuan=$_REQUEST["fk_cabang_tujuan"];
	
	$kd_blanko=$_REQUEST["fk_inisial_blanko"];
	$dari_nomor=$_REQUEST["dari_nomor"];
	$sampai_nomor=$_REQUEST["sampai_nomor"];
	
	$lwhere="kd_blanko >= '".$kd_blanko.'-'.str_pad($dari_nomor,6,"0",STR_PAD_LEFT)."' and kd_blanko <= '".$kd_blanko.'-'.str_pad( ($sampai_nomor+1) ,6,"0",STR_PAD_LEFT)."' and fk_sbg is null and fk_cabang='".$fk_cabang_asal."'";
	$tbl="data_gadai.tblblanko";
	//showquery("select * from ".$tbl."  where ".$lwhere);
	if(!pg_num_rows(pg_query("select * from ".$tbl."  where ".$lwhere))){
		$strmsg.="Tidak ada blanko yang bisa dimutasi.<br>";
		$j_action="lInputClose=getObjInputClose();lInputClose.close();";
	
	}
	
}

function save_additional(){
	global $l_success;
	$fk_cabang_asal=$_REQUEST["fk_cabang_asal"];
	$fk_cabang_tujuan=$_REQUEST["fk_cabang_tujuan"];
	
	$kd_blanko=$_REQUEST["fk_inisial_blanko"];
	$dari_nomor=$_REQUEST["dari_nomor"];
	$sampai_nomor=$_REQUEST["sampai_nomor"];
	
	
	$lwhere="kd_blanko >= '".$kd_blanko.'-'.str_pad($dari_nomor,6,"0",STR_PAD_LEFT)."' and kd_blanko <= '".$kd_blanko.'-'.str_pad( ($sampai_nomor+1) ,6,"0",STR_PAD_LEFT)."'";
	$tbl="data_gadai.tblblanko";
	if(!pg_query(insert_log($tbl,$lwhere,'UB')))$l_success=0;
	if(!pg_query("update ".$tbl." SET fk_cabang='".$fk_cabang_tujuan."' where ".$lwhere."  and fk_sbg is null and fk_cabang='".$fk_cabang_asal."'"))$l_success=0;
	//showquery("update ".$tbl." SET fk_cabang='".$fk_cabang_tujuan."' where ".$lwhere."");
	if(!pg_query(insert_log($tbl,$lwhere,'UA')))$l_success=0;

		
	//$l_success=0;
}

?>

