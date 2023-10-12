<?
//Input titipan
function cek_error_module(){
	global $strmsg;
			
	$no_rekon=$_REQUEST["no_rekon"];
	$lrs = pg_query("select * from data_fa.tblrekon_fintech_detail where fk_rekon='".$no_rekon."'");
		
	while ($lrow=pg_fetch_array($lrs)){
		$fk_sbg=$lrow["fk_sbg"];
		if(pg_num_rows(pg_query("select * from data_fa.tblfintech_ar where fk_sbg='".$fk_sbg."' "))){
			$strmsg.=" SBG ".$fk_sbg." sudah pernah dihit <br>";
		}				

		
	}
	

}

function save_additional(){
	global $l_success;	
	
	$no_rekon=$_REQUEST["no_rekon"];
	$lrs = pg_query("select * from data_fa.tblrekon_fintech_detail where fk_rekon='".$no_rekon."'");
	//showquery("select * from data_fa.tblrekon_fintech_detail where fk_rekon='".$no_rekon."'");
	while ($lrow=pg_fetch_array($lrs)){
		$fk_sbg=$lrow["fk_sbg"];
		$rate_fintech=$lrow["rate_fintech"]/12;
		$mitra=$lrow["mitra"];
		$l_success=hit_fintech($fk_sbg,$rate_fintech,$mitra);
	}
	//$l_success=0;
}




function hit_fintech($fk_sbg,$rate_fintech,$mitra=NULL){
	
	$l_success=1;
	$query="select nextserial_transaksi('FIN-AR':: text)";
	$lrow=pg_fetch_array(pg_query($query));
	$no_fintech=$lrow["nextserial_transaksi"];
								
	$query="
	select * from(
		select * from viewkontrak 
		left join (select fk_sbg, tgl_cair from tblinventory)as tblinventory on fk_sbg=no_sbg
		where no_sbg='".$fk_sbg."'
	)as tblsbg
	left join viewtaksir on fk_fatg=no_fatg
	";
	//showquery($query);
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	$fk_cabang=$lrow["fk_cabang"];
	$tgl_cair=$lrow["tgl_cair"];
	if(!$tgl_cair)$tgl_cair=get_rec("data_gadai.tblproduk_gadai","tgl_pengajuan","no_sbg='".$fk_sbg."'");
	if(!$tgl_cair)$tgl_cair=today_db;
	$fk_produk=$lrow["fk_produk"];
	//VA
	//$tgl_cair=today_db;
	$fk_bank_ho=20;
	$piutang_gadai=$lrow["total_nilai_pinjaman"];
	$piutang_jasa_penyimpanan=$lrow["biaya_penyimpanan"];
	$lama_pinjaman=$lrow["lama_pinjaman"];
	$lama_pinjaman_full=$lama_pinjaman*30;
	
	$jasa_simpan_fintech=round((($rate_fintech)*$piutang_gadai*$lama_pinjaman)/100);
	//echo $piutang_jasa_penyimpanan.'='.$jasa_simpan_fintech;
	$jasa_simpan_sisa=$piutang_jasa_penyimpanan-$jasa_simpan_fintech;
	//".(($mitra=="")?"NULL":"'".$mitra."'")."
	if(!pg_query("insert into data_fa.tblfintech_ar(no_fintech,tgl_ar,rate_fintech,jasa_simpan_fintech,fk_sbg,nominal,mitra) values ('".$no_fintech."','".$tgl_cair."','".$rate_fintech."','".$jasa_simpan_fintech."','".$fk_sbg."','".$piutang_gadai."',".(($mitra=="")?"NULL":"'".$mitra."'").")"));
	if(!pg_query("update data_fa.tblfintech_ar set tgl_terima_ar='".$tgl_cair."',tgl_verifikasi_ar='".$tgl_cair."' where no_fintech='".$no_fintech."' ")) $l_success=0;
	
	//showquery("insert into data_fa.tblfintech_ar(no_fintech,tgl_ar,rate_fintech,jasa_simpan_fintech,fk_sbg,nominal) values ('".$no_fintech."','".$tgl_cair."','".$rate_fintech."','".$jasa_simpan_fintech."','".$fk_sbg."','".$piutang_gadai."')");
	if(!pg_query("insert into data_fa.tblfintech_ar_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from data_fa.tblfintech_ar where no_fintech='".$no_fintech."'")) $l_success=0;
		
	$fk_cabang_ho 						= (is_pt=='t'?get_cabang_pt($fk_cabang):cabang_ho);					
	$rpkc								= get_coa_cabang($fk_cabang,(is_pt=='t'?$fk_cabang_ho:NULL));	
					
	if(!pg_num_rows(pg_query("select * from tblinventory where fk_sbg='".$fk_sbg."' and status_sbg='Exp'"))){
							
		$arrPost = array();
		$arrPost['virtual_account']			= array('type'=>'d','value'=>$piutang_gadai,'reference'=>$no_fintech);
		$arrPost[$rpkc]						= array('type'=>'c','value'=>$piutang_gadai,'reference'=>$no_fintech,'account'=>$rpkc);
		if(!posting('OBL PENDANAAN',$fk_sbg,$tgl_cair,$arrPost,$fk_cabang_ho,'00'))$l_success=0;			
		
		$arrPost = array();
		//$arrPost["piutang_fintech"]		        = array('type'=>'d','value'=>$piutang_gadai,'reference'=>$no_fintech);
		$arrPost["rpkp"]		      			= array('type'=>'d','value'=>$piutang_gadai,'reference'=>$no_fintech);
		$arrPost["pendapatan_jasa_simpan_yad"]	= array('type'=>'d','value'=>$piutang_jasa_penyimpanan,'reference'=>$no_fintech);
			
		$arrPost["piutang_gadai"]		        = array('type'=>'c','value'=>$piutang_gadai,'reference'=>$no_fintech);
		$arrPost["piutang_jasa_penyimpanan"]	= array('type'=>'c','value'=>$piutang_jasa_penyimpanan,'reference'=>$no_fintech);
		//cek_balance_array_post($arrPost);
		if(!posting('OBL PENDANAAN',$fk_sbg,$tgl_cair,$arrPost,$fk_cabang,'00'))$l_success=0;	
		
		$arrPost = array();
		$arrPost["piutang_jasa_penyimpanan"]	= array('type'=>'d','value'=>$jasa_simpan_sisa,'reference'=>$no_fintech);
		$arrPost["pendapatan_jasa_simpan_yad"]	= array('type'=>'c','value'=>$jasa_simpan_sisa,'reference'=>$no_fintech);
		//cek_balance_array_post($arrPost);
		if(!posting('OBL PENDANAAN',$fk_sbg,$tgl_cair,$arrPost,$fk_cabang,'00'))$l_success=0;	

		//VA		
		if(!pg_query(update_saldo_bank($fk_bank_ho,$fk_cabang_ho,$piutang_gadai,0,'PENDANAAN',$fk_sbg)))$l_success=0;	
	
	}

	//UPDATE ANGSURAN
	$rate_flat=$lrow["rate_flat"];

	$lwhere="fk_sbg='".$fk_sbg."'";
	$tbl="data_fa.tblangsuran";
	if(!pg_query(insert_log($tbl,$lwhere,'UB'))) $l_success=0;
	if(!pg_query("update ".$tbl." 
	SET 
		akrual_sbl_diambil_fintech=akrual1,
		akrual_fintech=round(akrual1*(".$rate_fintech."/".$rate_flat.")),
		akrual1=akrual1-round((akrual1*(".$rate_fintech."/".$rate_flat.")))
	where ".$lwhere."")) $l_success=0;
/*		showquery("update data_fa.tblangsuran SET 
			akrual_sbl_diambil_fintech=akrual1,
			akrual_fintech=round(akrual1*(".$rate_fintech."/".$rate_flat.")),
			akrual1=akrual1-round((akrual1*(".$rate_fintech."/".$rate_flat.")))
		where ".$lwhere."");
*/		if(!pg_query(insert_log($tbl,$lwhere,'UA'))) $l_success=0;	
			
	$no_fintech_ar=$no_fintech;

	//kl lunas sblum di hit		
	if(pg_num_rows(pg_query("select * from tblinventory where fk_sbg='".$fk_sbg."' and status_sbg='Exp'"))){
		$query="select nextserial_transaksi('FIN-AP':: text)";
		$lrow=pg_fetch_array(pg_query($query));
		$no_fintech=$lrow["nextserial_transaksi"];
		
		$nilai_pinjaman=$piutang_gadai;			
		$perhitungan_jasa_simpan=get_rec("tblproduk","perhitungan_jasa_simpan","kd_produk='".$fk_produk."'");
		$jumlah_hari=$lama_pinjaman_full;
		
		
		$query="
		select * from data_fa.tblpelunasan_gadai where fk_sbg='".$fk_sbg."' and status_pelunasan is null
		";
	
		$lrs=pg_query($query);
		$lrow=pg_fetch_array($lrs);
		$lama_jasa_simpan=$lrow["lama_jasa_simpan"];
		$tgl_bayar=$lrow["tgl_bayar"];
		$nilai_penyimpanan=$lrow["nilai_penyimpanan"];
		//sbg batal
		if(pg_num_rows(pg_query("select * from tblinventory where fk_sbg='".$fk_sbg."' and status='Batal'"))){
			$tgl_bayar=$tgl_cair;
			$query_cair="select * from data_gadai.tblproduk_gadai left join tblproduk on fk_produk=kd_produk where no_sbg='".$fk_sbg."'";
			$lrow_cair=pg_fetch_array(pg_query($query_cair));
			$lama_jasa_simpan=$lrow_cair["minimal_jasa_simpan"];
			$nilai_penyimpanan=round($nilai_pinjaman*($rate_flat/100)*ceil($lama_jasa_simpan/$perhitungan_jasa_simpan)/($jumlah_hari/$perhitungan_jasa_simpan));
		}		
		
		if($perhitungan_jasa_simpan>0){					
			$bunga_fintech=round($jasa_simpan_fintech*ceil($lama_jasa_simpan/$perhitungan_jasa_simpan)/($jumlah_hari/$perhitungan_jasa_simpan));
		}
					
		$kl_lainnya_fintech=$nilai_pinjaman+$bunga_fintech;
		
		
		//showquery("insert into data_fa.tblfintech_ap(no_fintech,fk_sbg,tgl_ap,bunga_fintech,jenis,nominal) values ('".$no_fintech."','".$fk_sbg."','".$tgl_bayar."',".$bunga_fintech.",'Pelunasan',".$kl_lainnya_fintech.")");
		if(!pg_query("insert into data_fa.tblfintech_ap(no_fintech,fk_sbg,tgl_ap,bunga_fintech,jenis,nominal) values ('".$no_fintech."','".$fk_sbg."','".$tgl_bayar."',".$bunga_fintech.",'Pelunasan',".$kl_lainnya_fintech.")"));
		if(!pg_query("update data_fa.tblfintech_ap set tagihan_fintech=".$kl_lainnya_fintech.",tgl_bayar_ap='".$tgl_bayar."' where no_fintech='".$no_fintech."' ")) $l_success=0;					
		
		if(!pg_query("insert into data_fa.tblfintech_ap_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from data_fa.tblfintech_ap where no_fintech='".$no_fintech."'")) $l_success=0;
		
		$pendapatan_pengelolaan_fintech=$nilai_penyimpanan-$bunga_fintech;
		
		$arrPost = array();
		$arrPost["virtual_account"]		        = array('type'=>'d','value'=>$nilai_pinjaman,'reference'=>$no_fintech_ar);						
		$arrPost['kl_pendanaan_lainnya']		= array('type'=>'c','value'=>$nilai_pinjaman,'reference'=>$no_fintech_ar);
					
		//cek_balance_array_post($arrPost);
		if(!posting('OBL PENDANAAN',$fk_sbg,$tgl_cair,$arrPost,$fk_cabang_ho,'00'))$l_success=0;								
		
		//VA
		if(!pg_query(update_saldo_bank($fk_bank_ho,$fk_cabang_ho,$nilai_pinjaman,0,'PENDANAAN',$fk_sbg)))$l_success=0;	
		
								
		$arrPost = array();
		$arrPost["pendapatan_jasa_penyimpanan"]	= array('type'=>'d','value'=>$bunga_fintech,'reference'=>$no_fintech_ar);
		$arrPost["rpkp"]						= array('type'=>'c','value'=>$bunga_fintech,'reference'=>$no_fintech_ar);
		
		if(!posting('OBL PELUNASAN PENDANAAN',$fk_sbg,$tgl_cair,$arrPost,$fk_cabang,'00'))$l_success=0;			
		
		$arrPost = array();
		$arrPost[$rpkc]							= array('type'=>'d','value'=>$bunga_fintech,'reference'=>$no_fintech_ar,'account'=>$rpkc);			
		$arrPost['kl_pendanaan_lainnya']		= array('type'=>'d','value'=>$nilai_pinjaman,'reference'=>$no_fintech_ar);		
		$arrPost["virtual_account"]		        = array('type'=>'c','value'=>$kl_lainnya_fintech,'reference'=>$no_fintech_ar);	
	
		//cek_balance_array_post($arrPost);
		if(!posting('OBL PELUNASAN PENDANAAN',$fk_sbg,$tgl_cair,$arrPost,$fk_cabang_ho,'00'))$l_success=0;		
		
		//VA
		if(!pg_query(update_saldo_bank($fk_bank_ho,$fk_cabang_ho,0,$kl_lainnya_fintech,'PELUNASAN',$fk_sbg)))$l_success=0;	
			

	}
	
	
	return $l_success;
}
?>

