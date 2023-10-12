<?
function query_additional(){
}

function save_additional(){
	global $l_success;
	//BPKB
	$no_mutasi=$_REQUEST["no_mutasi"];
	$lrs=pg_query("select * from data_fa.tblmutasi_bpkb_detail
	where fk_mutasi = '".$no_mutasi."' ");
	
	$tgl_kirim=convert_date_english($_REQUEST["tgl_kirim"]);
	$fk_cabang_terima=$_REQUEST["fk_cabang_terima"];
	$fk_cabang_kirim=$_REQUEST["fk_cabang_kirim"];
	$penerima=	$_REQUEST["penerima"];
	$tgl_terima=convert_date_english($_REQUEST["tgl_terima"]);
	$pengirim=	$_REQUEST["pengirim"];
	while($lrow=pg_fetch_array($lrs)){
		
		$no_sbg=$lrow['fk_sbg'];
		$tbl="data_gadai.tbltaksir_umum";
		$lwhere="no_sbg_ar='".$no_sbg."'";
		
		if($penerima=='Cabang'){
			$update = " ,fk_cabang_bpkb='".$fk_cabang_terima."'";
		}
		
		if($penerima=='HO' && $pengirim=='Bank'){
			$update = " ,no_cabut_limpah='".$no_mutasi."',tgl_cabut_limpah='".$tgl_kirim."'";			
			
			$fk_funding=get_rec("data_fa.tblfunding_detail","fk_funding","fk_sbg='".$no_sbg."' and tgl_unpledging is null");

			if(!pg_query("update data_fa.tblfunding_detail set tgl_unpledging='".$tgl_kirim."' where fk_sbg='".$no_sbg."' and tgl_unpledging is null and fk_funding='".$fk_funding."'"))$l_success=0;
			//showquery("update data_fa.tblfunding_detail set tgl_unpledging='".$tgl_kirim."' where fk_sbg='".$no_sbg."' and tgl_unpledging is null and fk_funding='".$fk_funding."'");
						
			$arr[$fk_funding]=$fk_funding;
		}
		if($penerima=='Bank' && $pengirim=='HO'){
			$update = " ,no_limpah_ke_bank='".$no_mutasi."',tgl_limpah_ke_bank='".$tgl_kirim."'";			
		}
		
		if(!pg_query(insert_log($tbl,$lwhere,'UB'))) $l_success=0;
		if(!pg_query("update ".$tbl." SET posisi_bpkb='".$penerima."' ".$update." where ".$lwhere."")) $l_success=0;
		//showquery("update ".$tbl." SET posisi_bpkb='".$penerima."' ".$update." where ".$lwhere."");
		if(!pg_query(insert_log($tbl,$lwhere,'UA'))) $l_success=0;

	}
	if(count($arr)>0){
		foreach($arr as $no_funding){
			if(!pg_num_rows(pg_query("select * from data_fa.tblfunding_detail where tgl_unpledging is null and fk_funding='".$no_funding."' "))){
				$p_table="data_fa.tblfunding";
				$p_where=" no_funding='".$no_funding."'";
			
				if(!pg_query(insert_log($p_table,$p_where,'UB')))$l_success=0;
				
				if(!pg_query("update data_fa.tblfunding set status_funding='Unpledging' where ".$p_where))$l_success=0;
				//showquery("update data_fa.tblfunding set status_funding='Unpledging' where ".$p_where);
				
				if(!pg_query(insert_log($p_table,$p_where,'UA')))$l_success=0;
			}
		}
	}
	
	$no_mutasi=$_REQUEST["no_mutasi"];
	$tgl_terima=date("m/d/Y");
	
	if(!pg_query(insert_log("data_fa.tblmutasi_bpkb","no_mutasi='".$no_mutasi."'",'UB'))) $l_success=0;
	
	if(!pg_query("update data_fa.tblmutasi_bpkb set tgl_terima='".$tgl_terima."' where no_mutasi='".$no_mutasi."'"))$l_success=0;
	
	if(!pg_query(insert_log("data_fa.tblmutasi_bpkb","no_mutasi='".$no_mutasi."'",'UA'))) $l_success=0;

	//showquery(insert_log("data_fa.tblmutasi_bpkb","no_mutasi='".$no_mutasi."'",'UA'));
	
}

?>

