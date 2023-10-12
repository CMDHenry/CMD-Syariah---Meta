<?
// LELANG
function cek_error_module(){
	global $strmsg,$j_action;
	
	//echo $_REQUEST['tgl_lelang'];
	if($_REQUEST['tgl_lelang']!=today){
		//$strmsg.="Approve Lelang tidak bisa beda hari dengan penginputan lelang, silakan dibatalkan dulu.<br>";
		//$j_action="lInputClose=getObjInputClose();lInputClose.close();";
	}	
	$fk_sbg=$_REQUEST["fk_sbg"];
		
	$jenis_transaksi=$_REQUEST["jenis_transaksi"];
	if($jenis_transaksi=='Jual Cash'){
		if(!pg_num_rows(pg_query("select * from tblinventory where fk_sbg='".$fk_sbg."' and status='Tarik'"))){
			$strmsg.="Kontrak belum ditarik.<br>";
		}
	}
}




function save_additional(){
	global $l_success,$biaya_penjualan,$biaya_denda,$denda,$pinalti,$titipan_angsuran,$bunga_berjalan,$sisa_pokok,$lama_pinjaman,$lama_pelunasan,$nilai_penyimpanan,$titipan,$total_denda_kini,$total_denda_lalu,$nilai_bayar_denda,$denda_keterlambatan,$denda_ganti_rugi,$sisa_angsuran;
	
	$fk_sbg=$_REQUEST["fk_sbg"];
	$no_lelang=$_REQUEST["no_kwitansi"];	
	$tgl_lelang=date("Y-m-d",strtotime(convert_date_english($_REQUEST["tgl_lelang"])));	
	$jenis_transaksi=$_REQUEST["jenis_transaksi"];
	$rate_ppn=$_REQUEST["rate_ppn"];

	$query="
	select * from (
		select pokok_awal as piutang_gadai,bunga_awal as piutang_jasa_penyimpanan,fk_sbg1 as no_sbg from viewsbg where fk_sbg1='".$fk_sbg."'	
	) as viewsaldo_ar
	left join tblinventory on no_sbg=fk_sbg 
	left join (select ang_ke ,fk_sbg as fk_sbg1 from viewang_ke)as tbl on fk_sbg1=fk_sbg
	";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);	
	//showquery($query);
	
	$fk_cif=$lrow["fk_cif"];	
	$ang_ke=$lrow["ang_ke"];	
	$angka_lelang=str_replace(',','',$_REQUEST['angka_lelang']);
	$angka_pelunasan=str_replace(',','',$_REQUEST['angka_pelunasan']);
	$nilai_dp=str_replace(',','',$_REQUEST['nilai_dp']);
	$nilai_claim=str_replace(',','',$_REQUEST['nilai_claim']);
	$nilai_selisih=$angka_lelang-$angka_pelunasan;
	$fk_cabang=$lrow["fk_cabang"];
		
	$fk_cabang_input=$_REQUEST["fk_cabang_input"];	
	$fk_bank=$_REQUEST["fk_bank"];	
	$account = get_coa_bank($fk_bank,$fk_cabang_input);	

	if(pg_num_rows(pg_query("select * from tblinventory left join tblproduk on fk_produk=kd_produk where fk_sbg='".$fk_sbg."' and jenis_produk=0"))){
/*		pelunasan_gadai($fk_sbg,$tgl_lelang);	
	
		$piutang_gadai=$lrow["piutang_gadai"];
		$piutang_jasa_penyimpanan=$lrow["piutang_jasa_penyimpanan"];
		//$nilai_penyimpanan=$lrow["piutang_jasa_penyimpanan"];
		
		$pembulatan= $angka_pelunasan-($piutang_gadai+$piutang_jasa_penyimpanan+$biaya_penjualan+$biaya_denda-$titipan);		
		$fom=date("Y",strtotime(today_db)).'-'.date("m",strtotime(today_db)).'-01';
		$query_akrual="
		select sum(akrual1)as accrual_income from data_fa.tblangsuran 
		where fk_sbg='".$fk_sbg."' and tgl_jatuh_tempo <'".$fom." 00:00:00'
		";
		//showquery($query_akrual);
		$lrs_akrual=pg_query($query_akrual);
		$lrow_akrual=pg_fetch_array($lrs_akrual);
		$accrual_income=$lrow_akrual["accrual_income"];
		//echo $accrual_income;
		$pendapatan_jasa_penyimpanan=$nilai_penyimpanan-$accrual_income;
		$accrual='accrual_jasa_simpan';
		if(pg_num_rows(pg_query("select * from data_fa.tblfintech_ap where fk_sbg='".$fk_sbg."'"))){
			$jasa_simpan_fintech=get_rec("data_fa.tblfintech_ar","jasa_simpan_fintech","fk_sbg='".$fk_sbg."'");
			$piutang_jasa_penyimpanan-=$jasa_simpan_fintech;
			$accrual='accrual_pendapatan_fintech';
			$accrual_income=$piutang_jasa_penyimpanan;
			$pendapatan_jasa_penyimpanan=0;
		}
		
		
		$arrPost = array();
		$arrPost["bank"]							= array('type'=>'d','value'=>$angka_lelang,'account'=>$account,'reference'=>$no_lelang);
		$arrPost["pendapatan_jasa_simpan_yad"]		= array('type'=>'d','value'=>$piutang_jasa_penyimpanan,'reference'=>$no_lelang);
		if($nilai_selisih>0){
			$arrPost["kl_kelebihan_lelang"]			= array('type'=>'c','value'=>$nilai_selisih,'reference'=>$no_lelang);
		} elseif($nilai_selisih<0) {
			$arrPost["biaya_rugi_lelang_bj"]		= array('type'=>'d','value'=>$nilai_selisih*-1,'reference'=>$no_lelang);
		}
		
		if($titipan>0){
			$arrPost["kl_titipan_gadai"]			= array('type'=>'d','value'=>$titipan,'reference'=>$no_lelang);		
			if(!pg_query(update_saldo_titipan($fk_sbg,($titipan*-1))))$l_success=0;
		}			
		
		//kunci 30
		$masa_tenggang=30;
		
		$lama_pinjaman_full=$lama_pinjaman*30;
		
		if($lama_pelunasan>($lama_pinjaman_full+$masa_tenggang)){
			$used_for_piutang="piutang_tunggu";
		}
		else{
			$used_for_piutang="piutang_gadai";
		}
		$arrPost[$used_for_piutang]					= array('type'=>'c','value'=>$piutang_gadai,'fk_supplier'=>$fk_cif,'reference'=>$no_lelang);
		
		$arrPost["piutang_jasa_penyimpanan"]		= array('type'=>'c','value'=>$piutang_jasa_penyimpanan,'fk_supplier'=>$fk_cif,'reference'=>$no_lelang);
	
		$arrPost[$accrual]							= array('type'=>'c','value'=>$accrual_income,'reference'=>$no_lelang);
		$arrPost["pendapatan_jasa_penyimpanan"]		= array('type'=>'c','value'=>$pendapatan_jasa_penyimpanan,'reference'=>$no_lelang);	

		if($biaya_penjualan>0){
			$arrPost["pendapatan_admin_penjualan"]		= array('type'=>'c','value'=>$biaya_penjualan,'reference'=>$no_lelang);
		}
		if($biaya_denda>0){
			$arrPost["pendapatan_admin_pemeliharaan"]	= array('type'=>'c','value'=>$biaya_denda,'reference'=>$no_lelang); 
		}
		if($jasa_simpan_fintech>0){
			$arrPost["tl_bunga_nasabah_fintech"]		= array('type'=>'c','value'=>$jasa_simpan_fintech,'reference'=>$no_lelang);
		}	
		if($pembulatan>0){
			$arrPost["pembulatan"]	   					= array('type'=>'c','value'=>$pembulatan,'reference'=>$no_lelang);
		}
		
		//cek_balance_array_post($arrPost);
		if(!posting('LELANG',$fk_sbg,today_db,$arrPost,$fk_cabang,'00'))$l_success=0;
		
		
		if(!pg_query("insert into data_fa.tblpelunasan_lelang (fk_lelang,biaya_bunga,biaya_denda,biaya_penjualan,titipan)values
		(".$no_lelang.",".(($nilai_penyimpanan=="")?"0":"'".$nilai_penyimpanan."'").",".(($biaya_denda=="")?"0":"'".$biaya_denda."'").",".(($biaya_penjualan=="")?"0":"'".$biaya_penjualan."'").",".(($titipan=="")?"0":"'".$titipan."'").")"))$l_success=0;		
		
		*/
	
	}elseif(pg_num_rows(pg_query("select * from tblinventory left join tblproduk on fk_produk=kd_produk where fk_sbg='".$fk_sbg."' and jenis_produk=1"))){
		
		//start cicicilan
		pelunasan_cicilan($fk_sbg,$tgl_lelang);	
		
		$query="
		select *,tblinventory.status as status_kontrak,biaya_penyimpanan as total_bunga from (
			select * from data_gadai.tblproduk_cicilan where no_sbg='".$fk_sbg."'	
		) as viewsaldo_ar
		left join tblinventory on no_sbg=fk_sbg 
		left join (
			select no_fatg as no_fatg1,kategori,fk_jenis_barang from viewkendaraan
		)as view on fk_fatg=no_fatg1		
		";
		$lrs=pg_query($query);
		$lrow=pg_fetch_array($lrs);	
		$kategori=$lrow["kategori"];		
		$kategori=strtolower($kategori);
		$status_kontrak=($lrow["status_kontrak"]);
		$total_bunga=$lrow["total_bunga"];		
		
		$query_sisa="
		select saldo_pinjaman as ar_cicilan,saldo_bunga from data_fa.tblangsuran 
		where fk_sbg='".$fk_sbg."' and tgl_bayar is not null
		order by angsuran_ke desc
		";
		$lrs_sisa=pg_query($query_sisa);
		$lrow_sisa=pg_fetch_array($lrs_sisa);	
		$ar_cicilan=$lrow_sisa["ar_cicilan"];	
		$saldo_bunga=$lrow_sisa["saldo_bunga"];		
		
		$lrow_tarik=pg_fetch_array(pg_query("select referensi,tgl_data from data_gadai.tblhistory_sbg where fk_sbg='".$fk_sbg."' and transaksi='Tarik' and tgl_batal is null order by pk_id desc,tgl_data desc "));
		$tgl_tarik=date("m/d/Y",strtotime($lrow_tarik["tgl_data"]));
		if($status_kontrak=='Tarik'){
			$fom=date("m/01/Y",strtotime($tgl_tarik));
		}else{
			$fom=date("m/01/Y",strtotime($tgl_lelang));
		}
		
		$query_akrual="
		select sum(akrual1+akrual2)as saldo_akrual from data_fa.tblangsuran 
		where fk_sbg='".$fk_sbg."' and tgl_jatuh_tempo <'".($fom)."'
		";
		//showquery($query_akrual);
		$lrs_akrual=pg_query($query_akrual);
		$lrow_akrual=pg_fetch_array($lrs_akrual);	
		$saldo_akrual=$lrow_akrual["saldo_akrual"];		
		$sisa_yad=$total_bunga-$saldo_akrual;
				
		//$pembulatan= $angka_pelunasan-($sisa_pokok+$bunga_berjalan+$denda+$pinalti-$titipan_angsuran);
		//echo $sisa_pokok." ".$bunga_berjalan." ".$denda." ".$pinalti." ".$titipan_angsuran;
		
		$arrPost = array();		
		$rpkc 	  = get_coa_cabang($fk_cabang,$fk_cabang_input);

		if($jenis_transaksi=='Lelang' ||$jenis_transaksi=='Jual Cash' ){
			$dpp=round($angka_lelang/(1+$rate_ppn));
			$ppn=$angka_lelang-$dpp;			
			$selisih=$ar_cicilan-$sisa_yad;
			
			$cad_piutang=$angka_lelang-$selisih-$ppn;
			
			$arrPost["bank"]							= array('type'=>'d','value'=>$angka_lelang,'account'=>$account);
			if($fk_cabang!=$fk_cabang_input){
				$arrPost["rpkc"]						= array('type'=>'c','value'=>$angka_lelang,'account'=>$rpkc);
				
				foreach($arrPost as $index=>$temp){
					$arrPost[$index]['reference'] =$no_lelang;//tambah keterangan disemua arrpost
				}
				//cek_balance_array_post($arrPost);
				if(!posting(strtoupper($jenis_transaksi),$fk_sbg,$tgl_lelang,$arrPost,$fk_cabang_input,'00'))$l_success=0;
				
				$arrPost = array();
				$arrPost["rpkp"]						= array('type'=>'d','value'=>$angka_lelang);		
			}
			
			$arrPost["jaminan_dikuasai_kembali"]		= array('type'=>'c','value'=>$selisih);
			//$arrPost["pend_bunga_yad"]		        = array('type'=>'d','value'=>$saldo_akrual);
			if($cad_piutang>0){
				$arrPost["titipan_gor"]		    		= array('type'=>'c','value'=>$cad_piutang);
			}else{		
				$cad_piutang=$cad_piutang*-1;
				$arrPost["cadangan_dikuasai_kembali"]   = array('type'=>'d','value'=>$cad_piutang);
			}
			
			$arrPost["ppn_keluaran"]					= array('type'=>'c','value'=>$ppn);
		}
		
		if($jenis_transaksi=='Jual Credit'){
			$dpp=round($angka_lelang/(1+$rate_ppn));
			$ppn=$angka_lelang-$dpp;			
			$selisih=$ar_cicilan-$sisa_yad;
			
			$cad_piutang=$selisih+$ppn-$angka_lelang;
			//echo $nilai_dp.'-'.$selisih.'-'.$ppn;
			$arrPost["bank"]							= array('type'=>'d','value'=>$nilai_dp,'account'=>$account);
			if($fk_cabang!=$fk_cabang_input){
				$arrPost["rpkc"]						= array('type'=>'c','value'=>$nilai_dp,'account'=>$rpkc);
				//cek_balance_array_post($arrPost);
				foreach($arrPost as $index=>$temp){
					$arrPost[$index]['reference'] =$no_lelang;//tambah keterangan disemua arrpost
				}
				
				if(!posting(strtoupper($jenis_transaksi),$fk_sbg,$tgl_lelang,$arrPost,$fk_cabang_input,'00'))$l_success=0;
				
				$arrPost = array();
				$arrPost["rpkp"]						= array('type'=>'d','value'=>$nilai_dp);		
				
			}						
			$arrPost["uang_muka_".$kategori]		    = array('type'=>'d','value'=>$angka_lelang-$nilai_dp);
			$arrPost["jaminan_dikuasai_kembali"]		= array('type'=>'c','value'=>$selisih);
			//$arrPost["pend_bunga_yad"]				= array('type'=>'d','value'=>$saldo_akrual);
			$arrPost["cadangan_dikuasai_kembali"]		= array('type'=>'d','value'=>$cad_piutang);
			$arrPost["ppn_keluaran"]					= array('type'=>'c','value'=>$ppn);
		}
		
		if($jenis_transaksi=='Claim Asuransi'){
			$sisa_bunga=$saldo_akrual+$bunga_berjalan-$saldo_bunga;
			$arrPost["bank"]							= array('type'=>'d','value'=>$nilai_claim,'account'=>$account);
			if($fk_cabang!=$fk_cabang_input){
				$arrPost["rpkc"]						= array('type'=>'c','value'=>$nilai_claim,'account'=>$rpkc);
				foreach($arrPost as $index=>$temp){
					$arrPost[$index]['reference'] =$no_lelang;//tambah keterangan disemua arrpost
				}				
				//cek_balance_array_post($arrPost);
				if(!posting(strtoupper($jenis_transaksi),$fk_sbg,$tgl_lelang,$arrPost,$fk_cabang_input,'00'))$l_success=0;
				
				$arrPost = array();
				$arrPost["rpkp"]						= array('type'=>'d','value'=>$nilai_claim);		
			}
			
			$arrPost["piutang_pembiayaan"]				= array('type'=>'c','value'=>$ar_cicilan);
			
			$arrPost["pend_bunga_yad"]		        	= array('type'=>'d','value'=>$saldo_akrual);
			$arrPost["pend_bunga_".$kategori]		    = array('type'=>'c','value'=>$sisa_bunga);
			//_".$kategori
			if($pinalti>0){
			$arrPost["pend_admin_".$kategori]		    = array('type'=>'c','value'=>$pinalti);
			}
			if($nilai_bayar_denda>0){
			$arrPost["pend_denda_".$kategori]		     = array('type'=>'c','value'=>$nilai_bayar_denda);
			}
			$nilai_selisih=$nilai_claim-$angka_pelunasan;
			if($nilai_selisih!=0){
				$arrPost["pend_lain2"]		        	= array('type'=>'c','value'=>$nilai_selisih);
			}
		}
		
		foreach($arrPost as $index=>$temp){
			$arrPost[$index]['reference'] =$no_lelang;//tambah keterangan disemua arrpost
		}

		//cek_balance_array_post($arrPost);
		if(!posting(strtoupper($jenis_transaksi),$fk_sbg,$tgl_lelang,$arrPost,$fk_cabang,'00'))$l_success=0;
						
	}	
	//echo $l_success;
	
	$nilai_bayar=0;
	$ket=$jenis_transaksi;
	if($jenis_transaksi=='Lelang' ||$jenis_transaksi=='Jual Cash' ){
		$nilai_bayar=$angka_lelang;		
	}
	if($jenis_transaksi=='Claim Asuransi'){
		$nilai_bayar=$nilai_claim;		
	}
	
	if($nilai_bayar>0){
		if(!pg_query(update_saldo_bank($fk_bank,$fk_cabang_input,$nilai_bayar,0,$ket,$fk_sbg,$no_lelang)))$l_success=0;		
		//showquery(update_saldo_bank($fk_bank,$fk_cabang,$nilai_bayar,0,$ket,$fk_sbg,$no_lelang));
	}
	
	//UPDATE ANGSURAN
	$lwhere="fk_sbg='".$fk_sbg."' and tgl_bayar is null";
	if(!pg_query(insert_log("data_fa.tblangsuran",$lwhere,'UB'))) $l_success=0;
	if(!pg_query("update data_fa.tblangsuran SET tgl_bayar='".$tgl_lelang."',no_kwitansi = '".$no_lelang."' where ".$lwhere."")) $l_success=0;	
	
	//showquery("update data_fa.tblangsuran SET tgl_bayar='".today_db."',no_kwitansi = '".$no_lelang."' where ".$lwhere."");
	if(!pg_query(insert_log("data_fa.tblangsuran",$lwhere,'UA'))) $l_success=0;
	
	if(!pg_query("update data_gadai.tbllelang SET 
	selisih=".($selisih?$selisih:0).",
	ppn=".($ppn?$ppn:0)."
	where no_kwitansi='".$no_lelang."'
	")) $l_success=0;
/*	showquery("update data_gadai.tbllelang SET 
	selisih=".($selisih?$selisih:0).",
	ppn=".($ppn?$ppn:0)."
	where no_kwitansi='".$no_lelang."'
	");
*/	
	if($jenis_transaksi=='Claim Asuransi'){
		if(!pg_query("update data_gadai.tbllelang SET 
		total_denda_kini='".$total_denda_kini."',
		total_denda_lalu='".$total_denda_lalu."',
		denda_keterlambatan='".$denda_keterlambatan."',
		denda_ganti_rugi='".$denda_ganti_rugi."',
		nilai_bayar_denda='".$nilai_bayar_denda."',
		sisa_pokok='".$sisa_pokok."',
		bunga_berjalan='".$bunga_berjalan."',		
		pinalti='".$pinalti."',
		sisa_angsuran='".$sisa_angsuran."'
		where no_kwitansi='".$no_lelang."'")) $l_success=0;
	}
	
	
		
	if(!pg_query(storing($fk_sbg,'-',$jenis_transaksi,'Jual',$tgl_lelang)))$l_success=0;
	//showquery(storing($fk_sbg,'-',$jenis_transaksi,'Jual',$tgl_lelang));
	
	//$lrow=pg_fetch_array(pg_query("select * from tblinventory left join (select ang_ke ,fk_sbg as fk_sbg1 from viewang_ke)as tbl on fk_sbg1=fk_sbg where fk_sbg='".$fk_sbg."'"));
	//$ang_ke=$lrow["ang_ke"];	
	$p_arr=array(
		'tgl_data'=>$tgl_lelang,
		'fk_sbg'=>$fk_sbg,
		'referensi'=>$no_lelang,
		'ang_ke'=>$ang_ke,
		'transaksi'=>$ket,
	);
	
	//showquery(insert_history($p_arr));	
	if(!pg_query(insert_history($p_arr))) $l_success=0;			
	
	//if(!pg_query(insert_history_sbg($fk_sbg,'0',$ang_ke,$ket,$no_lelang))) $l_success=0;		
	//showquery(insert_history_sbg($fk_sbg,'0',$ang_ke,$ket,$no_lelang));		

			
	//echo $l_success;
	//$l_success=0;
	
}

/*

		$fom=date('Y-m',strtotime($tgl_lelang)).'-01 00:00:00';
		$query_akrual="
		select sum(akrual2)as akrual from data_fa.tblangsuran 
		left join (
				select tgl_bayar as tgl_bayar_akru2 ,fk_sbg as fk_sbg_akru2,angsuran_ke-1 as angsuran_ke_akru2 from data_fa.tblangsuran
		)as tblakru2 on fk_sbg=fk_sbg_akru2 and angsuran_ke=angsuran_ke_akru2
		where fk_sbg='".$fk_sbg."' and tgl_jatuh_tempo <'".$fom."' and tgl_bayar_akru2 is null 	
		";
		//showquery($query_akrual);
		$lrs_akrual=pg_query($query_akrual);
		$lrow_akrual=pg_fetch_array($lrs_akrual);	
		$akrual+=round($lrow_akrual["akrual"]);				

*/

?>

