<?


function cek_error_module(){
	global $j_action,$strmsg,$nama_menu;
	
	$no_sbg=$_REQUEST["no_sbg_ar"];	
	$no_fatg=$_REQUEST["no_fatg"];
	$tbl="data_gadai.tbltaksir_umum";
	$lwhere="no_fatg='".$no_fatg."'";
	
	$query="select * from ".$tbl." where ".$lwhere;
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	$tgl_serah_terima_bpkb_old=$lrow["tgl_serah_terima_bpkb"];	
	$tgl_pengembalian_ke_dealer=$_REQUEST["tgl_pengembalian_ke_dealer"];
		
	$tgl_terima_bpkb=$_REQUEST["tgl_terima_bpkb"];
	$posisi_bpkb=$_REQUEST["posisi_bpkb"];
	
	if(!$tgl_pengembalian_ke_dealer){
		if($posisi_bpkb && !$tgl_terima_bpkb){
			$strmsg.="Tgl Terima BPKB tidak bisa dikosongkan.<br>";
		}
	}
	$tgl_serah_terima_bpkb=$_REQUEST["tgl_serah_terima_bpkb"];		
	if(($posisi_bpkb !='Cabang' && $posisi_bpkb !='Customer' && $posisi_bpkb !='HO')&& $tgl_serah_terima_bpkb){
		$strmsg.="Tgl Serah Terima BPKB tidak bisa diisi jika posisi bukan cabang.<br>";
	}	
			
	$no_kwitansi=get_rec("data_fa.tblpembayaran_bpkb","no_kwitansi","tgl_bayar is not null and fk_sbg='".$no_sbg."'");	
	$ovd_lunas_bpkb=get_rec("tblsetting","ovd_lunas_bpkb","tgl_sistem is not null");
	$tgl_lunas=get_rec("tblinventory","tgl_lunas","fk_sbg='".$no_sbg."'");
	$status_inv=get_rec("tblinventory","status","fk_sbg='".$no_sbg."'");
	//echo $tgl_serah_terima_bpkb.'sdfsdf';
	//$ovd_pengambilan_bpkb=$_REQUEST["ovd_pengambilan_bpkb"];		
	$ovd_bpkb=(strtotime(convert_date_english($tgl_serah_terima_bpkb))-strtotime($tgl_lunas))/(60*60*24);
	//echo $ovd_bpkb.'sdfsdf';
	$ovd_lunas_bpkb=$ovd_bpkb-$ovd_lunas_bpkb;
	if($status_inv=='Jual Cash')$ovd_lunas_bpkb=0;//cash ga perlu biaya simapan
	
	//echo $ovd_lunas_bpkb;
	if($ovd_lunas_bpkb>0 && !$no_kwitansi && $tgl_serah_terima_bpkb && !$tgl_serah_terima_bpkb_old){
		$strmsg.="Tgl Serah Terima BPKB belum bisa diisi sebelum dibayar biaya OVD.<br>";	
	}
	if($tgl_serah_terima_bpkb ){
		if(!$tgl_lunas){
			$strmsg.="Belum Lunas";
		}
		//showquery("select * from tblinventory where fk_cif='".$_REQUEST["fk_cif"]."' and status_sbg='Liv' and fk_sbg!='".$no_sbg."'");
		if(pg_num_rows(pg_query("select * from tblinventory where fk_cif='".$_REQUEST["fk_cif"]."' and status_sbg='Liv' and fk_sbg!='".$no_sbg."'"))){
			//$strmsg.="Masih ada kontrak lain yang belum lunas";
			//ga bisa kasih warning ini karena ada case khusus. 22-07-23
		}
	}
	
	
	if(!$tgl_serah_terima_bpkb &&  $tgl_serah_terima_bpkb_old && $_SESSION["jenis_user"]!='HO'){
		$strmsg.="Batal Serah Terima BPKB hanya bisa oleh user HO.<br>";	
	}
		
	if($tgl_terima_bpkb && $tgl_pengembalian_ke_dealer){
		$strmsg.="Tgl Terima BPKB tidak bisa diisi jika Tgl Pengembalian tidak dikosongkan.<br>";
	}
	
	if($tgl_terima_bpkb){
		if(!$_REQUEST["no_bpkb"]){
			$strmsg.="No BPKB tidak bisa kosong jika Tgl Terima BPKB sudah ada.<br>";
		}
		if(!$_REQUEST["tgl_bpkb"]){
			$strmsg.="Tgl BPKB tidak bisa kosong jika Tgl Terima BPKB sudah ada.<br>";
		}
		if(!$_REQUEST["nm_bpkb"]){
			$strmsg.="Nama BPKB tidak bisa kosong jika Tgl Terima BPKB sudah ada<br>";
		}
	}
	
	
}

function save_additional(){

	$no_fatg=$_REQUEST["no_fatg"];
	
	$tbl="data_gadai.tbltaksir_umum";
	$lwhere="no_fatg='".$no_fatg."'";
		
	//$query="select * from ".$tbl." where ".$lwhere;
	//$lrs=pg_query("select ");
	
	$posisi_bpkb=$_REQUEST["posisi_bpkb"];
	$tgl_terima_bpkb=$_REQUEST["tgl_terima_bpkb"];
	$tgl_pengembalian_ke_dealer=$_REQUEST["tgl_pengembalian_ke_dealer"];
	$tgl_serah_terima_bpkb=$_REQUEST["tgl_serah_terima_bpkb"];	
		
	if((!$posisi_bpkb||$posisi_bpkb=='Revisi') && $tgl_terima_bpkb){
		$posisi_bpkb='Cabang';
		//if(!pg_query(insert_log($tbl,$lwhere,'UB'))) $l_success=0;
		if(!pg_query("update ".$tbl." SET posisi_bpkb='".$posisi_bpkb."' where ".$lwhere."")) $l_success=0;
		//showquery("update ".$tbl." SET posisi_bpkb='".$posisi_bpkb."' where ".$lwhere."");
		if(!pg_query(insert_log($tbl,$lwhere,'UA'))) $l_success=0;
	}
	
	if(($posisi_bpkb =='Cabang' || $posisi_bpkb =='HO') && $tgl_serah_terima_bpkb){
		$posisi_bpkb='Customer';
		//if(!pg_query(insert_log($tbl,$lwhere,'UB'))) $l_success=0;
		if(!pg_query("update ".$tbl." SET posisi_bpkb='".$posisi_bpkb."' where ".$lwhere."")) $l_success=0;
		//showquery("update ".$tbl." SET posisi_bpkb='".$posisi_bpkb."' where ".$lwhere."");
		if(!pg_query(insert_log($tbl,$lwhere,'UA'))) $l_success=0;
	}
		
	if($posisi_bpkb =='Customer' && !$tgl_serah_terima_bpkb){
		$posisi_bpkb='Cabang';
		//if(!pg_query(insert_log($tbl,$lwhere,'UB'))) $l_success=0;
		if(!pg_query("update ".$tbl." SET posisi_bpkb='".$posisi_bpkb."',no_tt_bpkb=NULL where ".$lwhere."")) $l_success=0;
		//showquery("update ".$tbl." SET posisi_bpkb='".$posisi_bpkb."' where ".$lwhere."");
		if(!pg_query(insert_log($tbl,$lwhere,'UA'))) $l_success=0;
	}

	if($tgl_pengembalian_ke_dealer){
		//if(!pg_query(insert_log($tbl,$lwhere,'UB'))) $l_success=0;
		if(!pg_query("update ".$tbl." SET posisi_bpkb='Revisi',tgl_terima_bpkb=NULL where ".$lwhere."")) $l_success=0;
		//showquery("update ".$tbl." SET posisi_bpkb='Revisi',tgl_terima_bpkb=NULL where ".$lwhere."");
		if(!pg_query(insert_log($tbl,$lwhere,'UA'))) $l_success=0;
	}


	if($tgl_terima_bpkb){
		$no_register=$_REQUEST["no_register"];			
		if(!$no_register){
			$fk_cabang=$_REQUEST["fk_cabang"];
			$query_serial="select nextserial_cabang_tahun('BPKB':: text,'".$fk_cabang."')";
			$lrow_serial=pg_fetch_array(pg_query($query_serial));
			$no_register=$lrow_serial["nextserial_cabang_tahun"];	
			//echo $no_register;
			if(!pg_query("update ".$tbl." SET no_register='".$no_register."' where ".$lwhere."")) $l_success=0;
			//showquery("update ".$tbl." SET no_register='".$no_register."' where ".$lwhere."");
			if(!pg_query(insert_log($tbl,$lwhere,'UA'))) $l_success=0;
		}
	}


}


?>

