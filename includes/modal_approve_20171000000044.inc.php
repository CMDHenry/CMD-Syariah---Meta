<?
/*function cek_error_module(){
	global $strmsg;
	$lrs=pg_query("select * from data_gadai.tblmoving_lokasi_detail 
	left join data_gadai.tblslot on pk_id_barang=pk_id_barang_moving_lokasi
	where fk_moving_lokasi = '".$_REQUEST["kd_moving_lokasi"]."' order by pk_id desc");
	
		
	while($lrow=pg_fetch_array($lrs)){
		$fk_rak=$lrow["fk_rak_awal"];
		$slot=$lrow["slot_awal"];
		
		if(!pg_num_rows(pg_query("select * from data_gadai.tblslot where pk_id_barang='".$lrow["pk_id_barang"]."' and slot='".$slot."' and fk_rak='".$fk_rak."' "))){
			$strmsg.="Barang ".$lrow["pk_id_barang"]." sudah tidak ada di rak.<br>";	
		}
		
		
	}

}*/

function save_additional(){
	global $l_success;
	$lrs=pg_query("select * from data_gadai.tblmutasi_barang_detail 
	where fk_mutasi_barang = '".$_REQUEST["kd_mutasi_barang"]."' ");
	
	while($lrow=pg_fetch_array($lrs)){
		if(!pg_query(storing($lrow["fk_sbg"],$lrow["fk_lajur_baru"],'Terima','Mutasi Barang',$lrow["fk_lajur_lama"],NULL,NULL,NULL)))$l_success=0;
		//showquery(storing($lrow["fk_sbg"],$lrow["fk_lajur_baru"],'Terima','Mutasi Barang',NULL,NULL,NULL,$lrow["fk_lajur_lama"]));
	}
	//$l_success=0;
}

?>

