<?
function query_additional($pk_id=NULL){
	global $l_success;
		
	//$fk_cabang=$_REQUEST["fk_cabang"];
	$no_pinjaman_bank=$pk_id;
	$tbl="data_fa.tblpinjaman_bank_detail";
	$tgl_awal=$_REQUEST["tgl_awal"];
	$pinjaman_awal=$_REQUEST["pinjaman_awal"];
	$rate_awal=$_REQUEST["rate_awal"];
	$plafon=$_REQUEST["plafon"];
	$bunga_harian=round($rate_awal*$pinjaman_awal/100/360);
	$saldo_plafon=$plafon-$pinjaman_awal;
	//is_approval($fk_cabang,$_REQUEST["final_approval"],$tbl,$pk_id);
	//if(!pg_query("insert into ".$tbl." (fk_pinjaman_bank,saldo_pinjaman,rate_pa,bunga_harian,saldo_plafon,tgl_detail,saldo_pinjaman_awal,saldo_plafon_awal) values('".$no_pinjaman_bank."','".$pinjaman_awal."','".$rate_awal."','".$bunga_harian."','".$saldo_plafon."','".$tgl_awal."','".$pinjaman_awal."','".$saldo_plafon."') ")) $l_success=0;
	
	//showquery("insert into ".$tbl." (fk_pinjaman_bank,saldo_pinjaman,rate_pa,bunga_harian,saldo_plafon,tgl_detail,saldo_pinjaman_awal,saldo_plafon_awal) values('".$no_pinjaman_bank."','".$pinjaman_awal."','".$rate_awal."','".$bunga_harian."','".$saldo_plafon."','".$tgl_awal."','".$pinjaman_awal."','".$saldo_plafon."') ");
	
	//$l_id_log=get_last_id("data_fa.tblpinjaman_bank_log","pk_id_log");
	//end log
	//if(!pg_query("insert into ".$tbl."_log select *,'".$l_id_log."' from ".$tbl." where fk_pinjaman_bank='".$no_pinjaman_bank."'")) $l_success=0;
	//$l_success=0;
}

?>

