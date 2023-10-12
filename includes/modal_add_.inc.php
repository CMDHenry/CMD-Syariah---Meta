<?
function save_additional(){
	$fk_kelompok=$_REQUEST["fk_kelompok_part"];
	$kd_part = $_REQUEST["kd_part"];
	//echo "aaa".$kd_lokasi_unit;
	echo "123".$fk_kelompok;
	if($fk_kelompok == 'AHM028'){
		
		if(!pg_query("insert into tbldiskon_oli_regular (tipe_diskon,diskon_range1,diskon_range2,diskon_range3,fk_part_oli)values('Rupiah',0,0,0,'".$kd_part."')")) $l_success=0;
		$pk_id=get_last_id("tbldiskon_oli_regular","kd_diskon_oli_regular");
		if(!pg_query("insert into tbldiskon_oli_regular_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from tbldiskon_oli_regular where kd_diskon_oli_regular='".$pk_id."'")) $l_success=0;
		
		
	}
	
	$lrs_gudang_part=pg_query("select * from tblgudang_part");
	while($lrow_gudang_part=pg_fetch_array($lrs_gudang_part)){
		if(!pg_query("insert into data_part.tblstock_part(fk_part,fk_gudang,qty_booking,qty_on_hand,qty_intransit)values('".convert_sql($kd_part)."','".$lrow_gudang_part["kd_gudang_part"]."','0','0','0')")) $l_success=0;
		if(!pg_query("insert into data_part.tblstock_part_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from data_part.tblstock_part where fk_part='".$kd_part."'")) $l_success=0;
		
		if(!pg_query("insert into tbllokasi_part_detail(fk_lokasi_part,fk_part,qty_maks,qty_available)values('pick_".$lrow_gudang_part["kd_gudang_part"]."','".convert_sql($kd_part)."','0','0')")) $l_success=0;
	}
}

?>
