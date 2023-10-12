<?

function html_additional(){
	
	echo '<input type="hidden" name="tipe" value="Gadai">';

}
function cek_error_module(){
	global $j_action,$strmsg;
	$fk_sbg=$_REQUEST["fk_sbg"];
	$query="
	select lama_simpan,masa_tenggang,tgl_lunas,jenis_produk,fk_cabang from(
		select * from tblinventory 
		where fk_sbg ='".$fk_sbg."'
	)as tblsbg	
	left join tblproduk on fk_produk=kd_produk
	left join(
		select min(dari)as masa_tenggang,fk_produk as fk_produk1 from tblproduk_detail_masa_tenggang group by fk_produk
	) as tbldetail on fk_produk1=kd_produk
	";
	//showquery($query);
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	$lama_simpan=$lrow["lama_simpan"];
	$masa_tenggang=$lrow["masa_tenggang"];
	
	$diskon_pelunasan=$_REQUEST["diskon_pelunasan"];
	$lama_pelunasan=$_REQUEST["lama_pelunasan"];
	if($diskon_pelunasan>0 ){
		if($lama_pelunasan<($lama_simpan*30)){
			//$strmsg.="Diskon tidak boleh diisi jika belum melewati overdue<br>";
		}
		if(!pg_num_rows(pg_query("select * from data_fa.tbldiskon_pelunasan where fk_sbg='".$fk_sbg."' and is_approval='t'"))){
			if(!pg_num_rows(pg_query("select * from data_fa.tbldiskon_pelunasan where fk_sbg='".$fk_sbg."' "))){
				//pg_query("BEGIN");
				pg_query("insert into data_fa.tbldiskon_pelunasan(fk_sbg,diskon) values ('".$fk_sbg."','".$diskon_pelunasan."')");
				$lwhere="fk_sbg='".$fk_sbg."'";
				pg_query(insert_log("data_fa.tbldiskon_pelunasan",$lwhere,'IA'));
				//pg_query("COMMIT");
				
				$strmsg.="Diskon harus diapprove oleh HO terlebih dahulu<br>";
			}else{
				
				$diskon_approval=get_rec("data_fa.tbldiskon_pelunasan","diskon","fk_sbg='".$fk_sbg."'");
				$is_approval=get_rec("data_fa.tbldiskon_pelunasan","is_approval","fk_sbg='".$fk_sbg."'");
				if($is_approval=='f'){
					$strmsg.="Diskon harus diapprove oleh HO terlebih dahulu<br>";
				}
				if($diskon_approval && $diskon_approval!=$diskon_pelunasan){
					$strmsg.="Diskon harus diapprove ulang jika akan diganti<br>";
					
					$lwhere="fk_sbg='".$fk_sbg."'";
					//pg_query("BEGIN");
					pg_query(insert_log("data_fa.tbldiskon_pelunasan",$lwhere,'UB'));
					pg_query("update data_fa.tbldiskon_pelunasan SET diskon='".$diskon_pelunasan."', is_approval = 'f' where ".$lwhere."") ;
					pg_query(insert_log("data_fa.tbldiskon_pelunasan",$lwhere,'UA'));
					//pg_query("COMMIT");
	
				}
			}
				
		}

	}
		

	//if(pg_num_rows(pg_query("select * from data_fa.tblfintech_ap where fk_sbg ='".$_REQUEST["fk_sbg"]."'"))){
		//$strmsg.="SBG ini sudah dibayarkan ke fintech <br>";
		
	//}
	$tgl_lunas=$lrow["tgl_lunas"];
	$jenis_produk=$lrow["jenis_produk"];
	$fk_cabang=$lrow["fk_cabang"];

	if($tgl_lunas){
		$strmsg.="SBG ini sudah lunas <br>";	
	}elseif($jenis_produk!='0'){
		$strmsg.="SBG ini bukan jenis Gadai <br>";	
	}elseif($fk_cabang!=$_SESSION["kd_cabang"]){
		$strmsg.="SBG ini berasal dari cabang lain <br>";	
	}


	$total_pembayaran=str_replace(',','',$_REQUEST['total_pembayaran']);
	$nilai_penyimpanan=str_replace(',','',$_REQUEST['nilai_penyimpanan']);
	$nilai_pinjaman=str_replace(',','',$_REQUEST['nilai_pinjaman']);
	$biaya_penjualan=str_replace(',','',$_REQUEST['biaya_penjualan']);
	$biaya_denda=str_replace(',','',$_REQUEST['biaya_denda']);
	$nilai_bayar=str_replace(',','',$_REQUEST['nilai_bayar']);
	$diskon_pelunasan=str_replace(',','',$_REQUEST['diskon_pelunasan']);
	$titipan=str_replace(',','',$_REQUEST['titipan']);	
	
	//echo $nilai_penyimpanan.'-'.$nilai_pinjaman;
	$query="
	select * from viewsbg
	where fk_sbg1='".$fk_sbg."' 
	";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);	
	$pokok_awal=$lrow["pokok_awal"];
	
	if($pokok_awal!=$nilai_pinjaman){
		$strmsg.="Nilai Pinjaman tidak sesuai, silakan input ulang No. SBG <br>";	
	}
	elseif(($pokok_awal+$nilai_penyimpanan+$biaya_denda+$biaya_penjualan-$diskon_pelunasan-$titipan)!=$total_pembayaran){
		$strmsg.="Nilai Total Pembayaran tidak sesuai, silakan input ulang No. SBG <br>";	
	}
	
}

function query_additional($pk_id){
	global $no_pelunasan;	
	$no_pelunasan=$pk_id;
}
function save_additional(){
	global $l_success,$no_pelunasan;
	
	$fk_sbg=$_REQUEST["fk_sbg"];
	$overdue=get_rec("viewjatuh_tempo","overdue","fk_sbg='".$fk_sbg."'");

	//SAM
	if(!pg_query(storing($fk_sbg,NULL,'Pengajuan Pelunasan','Pelunasan')))$l_success=0;
	
	$total_pembayaran=str_replace(',','',$_REQUEST['total_pembayaran']);
	$nilai_penyimpanan=str_replace(',','',$_REQUEST['nilai_penyimpanan']);
	$nilai_pinjaman=str_replace(',','',$_REQUEST['nilai_pinjaman']);
	$biaya_penjualan=str_replace(',','',$_REQUEST['biaya_penjualan']);
	$biaya_denda=str_replace(',','',$_REQUEST['biaya_denda']);
	$nilai_bayar=str_replace(',','',$_REQUEST['nilai_bayar']);
	$diskon_pelunasan=str_replace(',','',$_REQUEST['diskon_pelunasan']);
	$titipan=str_replace(',','',$_REQUEST['titipan']);

    $query="
	select saldo_bunga as piutang_jasa_penyimpanan,* from viewang_ke
	where fk_sbg='".$fk_sbg."' 
	 ";	 
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);	
	$piutang_jasa_penyimpanan=$lrow["piutang_jasa_penyimpanan"];
	
	$fk_cabang=get_rec("tblinventory","fk_cabang","fk_sbg='".$fk_sbg."'");	
	$fk_cabang_ho=(is_pt=='t'?get_cabang_pt($fk_cabang):cabang_ho);

	
	$fk_bank=$_REQUEST["fk_bank_pelunasan"];
	$account = get_coa_bank($fk_bank,$fk_cabang);
	
	$fom=date("Y",strtotime(today_db)).'-'.date("m",strtotime(today_db)).'-01';
	
	$query_akrual="
	select sum(akrual1)as accrual_income from data_fa.tblangsuran 
	where fk_sbg='".$fk_sbg."' and tgl_jatuh_tempo <'".$fom." 00:00:00'
	";
	//showquery($query_akrual);
	$lrs_akrual=pg_query($query_akrual);
	$lrow_akrual=pg_fetch_array($lrs_akrual);
	$accrual_income=$lrow_akrual["accrual_income"];	
	
	//CEK UD FINTECH 
	$fintech=false;
	if(pg_num_rows(pg_query("select * from data_fa.tblfintech_ar where fk_sbg='".$fk_sbg."'"))&&!pg_num_rows(pg_query("select * from data_fa.tblfintech_ap where fk_sbg='".$fk_sbg."' "))){
		$fintech=true;
	}
	$jasa_simpan_fintech=get_rec("data_fa.tblfintech_ar",'jasa_simpan_fintech',"fk_sbg='".$fk_sbg."'");
	$jasa_simpan_sisa=$piutang_jasa_penyimpanan-$jasa_simpan_fintech;
	
	//$fintech=true;	
	

	//CEK UD PINDAH KE PIUTANG TUNGGU 
	$query1="select max(ke) as masa_tenggang from tblproduk_detail_masa_tenggang
	left join tblproduk on kd_produk=fk_produk
	where jenis_produk=0";
	
	$lrs1=pg_query($query1);
	
	while($lrow1=pg_fetch_array($lrs1)){
		$masa_tenggang=$lrow1["masa_tenggang"];
	}
	$lama_pinjaman_full=$_REQUEST["lama_pinjaman"]*30;
	$lama_pelunasan=$_REQUEST["lama_pelunasan"];
	
	/////===================JURNAL=========================
	if($fintech!=true){
		//------P O S T I N G YANG BUKAN FINTECH and YANG FINTECH SDH JT---------
		
		$pendapatan_jasa_penyimpanan=$nilai_penyimpanan-$accrual_income;
		$accrual='accrual_jasa_simpan';
		if(pg_num_rows(pg_query("select * from data_fa.tblfintech_ap where fk_sbg='".$fk_sbg."' "))){
			$piutang_jasa_penyimpanan-=$jasa_simpan_fintech;
			$accrual='accrual_pendapatan_fintech';
			//$accrual_income=$piutang_jasa_penyimpanan;
			//$pendapatan_jasa_penyimpanan=0;
			$pendapatan_jasa_penyimpanan=$piutang_jasa_penyimpanan-$accrual_income;
		}
		
		
		$arrPost = array();
		$arrPost["bank"]		        			= array('type'=>'d','value'=>$nilai_bayar,'account'=>$account,'reference'=>$no_pelunasan);
		$arrPost["pendapatan_jasa_simpan_yad"]		= array('type'=>'d','value'=>$piutang_jasa_penyimpanan,'reference'=>$no_pelunasan);
		if($diskon_pelunasan>0){
			
			$total_biaya=$biaya_denda+$biaya_penjualan;
			$tblcoa='tbltemplate_coa';
			$pendapatan_admin_penjualan=$fk_cabang.'.'.get_rec($tblcoa,"coa","used_for='pendapatan_admin_penjualan'");
			$pendapatan_admin_pemeliharaan=$fk_cabang.'.'.get_rec($tblcoa,"coa","used_for='pendapatan_admin_pemeliharaan'");
			
			if($diskon_pelunasan>$total_biaya){
				$kerugian_pelunasan=$diskon_pelunasan-($total_biaya);
				$arrPost["kerugian_pelunasan"]   	= array('type'=>'d','value'=>$kerugian_pelunasan,'reference'=>$no_pelunasan);
				$arrPost["pendapatan_admin_penjualan_d"]   = array('type'=>'d','value'=>$biaya_penjualan,'reference'=>$no_pelunasan,'account'=>$pendapatan_admin_penjualan);				
				$arrPost["pendapatan_admin_pemeliharaan_d"]= array('type'=>'d','value'=>$biaya_denda,'reference'=>$no_pelunasan,'account'=>$pendapatan_admin_pemeliharaan);

			}else{
				
				$sisa=$diskon_pelunasan;
				if($biaya_penjualan>0){		
					if($diskon_pelunasan>$biaya_penjualan)$nilai_diskon=$biaya_penjualan;
					else $nilai_diskon=$sisa;
					$arrPost["pendapatan_admin_penjualan_d"]   = array('type'=>'d','value'=>$nilai_diskon,'reference'=>$no_pelunasan,'account'=>$pendapatan_admin_penjualan);
					$sisa-=$biaya_penjualan;
				}
				if($biaya_denda>0&&$sisa>0){
					$arrPost["pendapatan_admin_pemeliharaan_d"]= array('type'=>'d','value'=>$sisa,'reference'=>$no_pelunasan,'account'=>$pendapatan_admin_pemeliharaan);
				}
			}			
		}
		
		//NEW
		if($titipan>0){
			$arrPost["kl_titipan_gadai"]							= array('type'=>'d','value'=>$titipan,'reference'=>$no_pelunasan);		
			if(!pg_query(update_saldo_titipan($fk_sbg,($titipan*-1))))$l_success=0;
		}
		
		
		$pembulatan=($total_pembayaran-$nilai_bayar)*-1;
		if($pembulatan>0){
			$arrPost["pembulatan"]	   					= array('type'=>'c','value'=>$pembulatan,'reference'=>$no_pelunasan);
		}
		

		//if($lama_pelunasan>($lama_pinjaman_full+$masa_tenggang)){
		$masa_tenggang=30;//dikunci
		if($overdue>$masa_tenggang){
			$used_for_piutang="piutang_tunggu";
		}
		else{
			$used_for_piutang="piutang_gadai";
		}
		$arrPost[$used_for_piutang]		        	= array('type'=>'c','value'=>$nilai_pinjaman,'fk_supplier'=>$_REQUEST["fk_cif"],'reference'=>$no_pelunasan);
		$arrPost["piutang_jasa_penyimpanan"]		= array('type'=>'c','value'=>$piutang_jasa_penyimpanan,'fk_supplier'=>$_REQUEST["fk_cif"],'reference'=>$no_pelunasan);
		$arrPost[$accrual]							= array('type'=>'c','value'=>$accrual_income,'reference'=>$no_pelunasan);
		$arrPost["pendapatan_jasa_penyimpanan"]		= array('type'=>'c','value'=>$pendapatan_jasa_penyimpanan,'reference'=>$no_pelunasan);	
		if($biaya_denda>0){
			$arrPost["pendapatan_admin_pemeliharaan"]= array('type'=>'c','value'=>$biaya_denda,'reference'=>$no_pelunasan);
		}
		
		if($biaya_penjualan>0){
			$arrPost["pendapatan_admin_penjualan"]   = array('type'=>'c','value'=>$biaya_penjualan,'reference'=>$no_pelunasan);
		}
		
		if($jasa_simpan_fintech>0){
			$arrPost["tl_bunga_nasabah_fintech"]	 = array('type'=>'c','value'=>$jasa_simpan_fintech,'reference'=>$no_pelunasan);
		}		
		
		//cek_balance_array_post($arrPost);
		if(!posting('PELUNASAN GADAI',$fk_sbg,today_db,$arrPost,$fk_cabang,'00'))$l_success=0;	
		
		//UPDATE SALDO AR	
//		$l_arr_update["saldo"]=($nilai_pinjaman)*-1;
//		$l_arr_update["jumlah_alokasi"]=($nilai_pinjaman);
//		$l_arr_update["jumlah_approve"]=($nilai_pinjaman);
//		if(!update_saldo_ar($fk_sbg,$used_for_piutang,today_db,$l_arr_update,$fk_cabang))$l_success=0;				
//	
//		$l_arr_update["saldo"]=($piutang_jasa_penyimpanan)*-1;
//		$l_arr_update["jumlah_alokasi"]=($piutang_jasa_penyimpanan);
//		$l_arr_update["jumlah_approve"]=($piutang_jasa_penyimpanan);
//		if(!update_saldo_ar($fk_sbg,'piutang_jasa_penyimpanan',today_db,$l_arr_update,$fk_cabang))$l_success=0;	
		
	}
	
	else{
		//POSTING FINTECH SBLUM JATUH TEMPO				
		
		$fk_supplier="F01";
		$rpkc				= get_coa_cabang($fk_cabang,(is_pt=='t'?$fk_cabang_ho:NULL));
		
		$jumlah_hari=$lama_pinjaman_full;
		$lama_jasa_simpan=$_REQUEST["lama_jasa_simpan"];
		$fk_produk=get_rec("tblinventory","fk_produk","fk_sbg='".$fk_sbg."'");	
		$perhitungan_jasa_simpan=get_rec("tblproduk","perhitungan_jasa_simpan","kd_produk='".$fk_produk."'");				
		
		if($perhitungan_jasa_simpan>0){	
			//echo $perhitungan_jasa_simpan; 	
			$bunga_fintech=round($jasa_simpan_fintech*ceil($lama_jasa_simpan/$perhitungan_jasa_simpan)/($jumlah_hari/$perhitungan_jasa_simpan));
			
			$pendapatan_jasa_pengelolaan_fintech=$nilai_penyimpanan-$bunga_fintech-$accrual_income;
		}
				
		$kl_lainnya_fintech=$nilai_pinjaman+$bunga_fintech;
		
		$arrPost = array();
		$arrPost["bank"]		        					= array('type'=>'d','value'=>$nilai_bayar,'account'=>$account,'reference'=>$no_pelunasan);
		//NEW
		if($titipan>0){
			$arrPost["kl_titipan_gadai"]				= array('type'=>'d','value'=>$titipan,'reference'=>$no_pelunasan);	
			if(!pg_query(update_saldo_titipan($fk_sbg,($titipan*-1))))$l_success=0;	
		}
		
		$pembulatan=($total_pembayaran-$nilai_bayar)*-1;
		if($pembulatan>0){
			$arrPost["pembulatan"]	   						= array('type'=>'c','value'=>$pembulatan,'reference'=>$no_pelunasan);
		}
		
		$arrPost["accrual_pendapatan_fintech"]				= array('type'=>'c','value'=>$accrual_income,'reference'=>$no_pelunasan);
		$arrPost["rpkp"]									= array('type'=>'c','value'=>$kl_lainnya_fintech,'reference'=>$no_pelunasan);
		if($diskon_pelunasan>0) {
			//$pendapatan_jasa_pengelolaan_fintech-= $diskon_pelunasan;
			$arrPost["kerugian_pelunasan"]   			= array('type'=>'d','value'=>$diskon_pelunasan,'reference'=>$no_pelunasan);
		}
		
		$arrPost["pendapatan_jasa_pengelolaan_fintech"]		= array('type'=>'c','value'=>$pendapatan_jasa_pengelolaan_fintech,'reference'=>$no_pelunasan);
		$arrPost["pendapatan_jasa_simpan_yad"]			= array('type'=>'d','value'=>$jasa_simpan_sisa,'reference'=>$no_pelunasan);
		$arrPost["piutang_jasa_penyimpanan"]			= array('type'=>'c','value'=>$jasa_simpan_sisa,'reference'=>$no_pelunasan);
		//cek_balance_array_post($arrPost);
		if(!posting('PELUNASAN GADAI',$fk_sbg,today_db,$arrPost,$fk_cabang,'00'))$l_success=0;	
		
		$arrPost = array();
		$arrPost[$rpkc]					= array('type'=>'d','value'=>$kl_lainnya_fintech,'reference'=>$no_pelunasan,'account'=>$rpkc);
		$arrPost["virtual_account"]		= array('type'=>'c','value'=>$kl_lainnya_fintech,'reference'=>$no_pelunasan);						
		
		//cek_balance_array_post($arrPost);
		if(!posting('PELUNASAN GADAI',$fk_sbg,today_db,$arrPost,$fk_cabang_ho,'00'))$l_success=0;	
		
		//VA
		$fk_bank_ho	= 20;
		if(!pg_query(update_saldo_bank($fk_bank_ho,$fk_cabang_ho,0,$kl_lainnya_fintech,'PELUNASAN',$fk_sbg)))$l_success=0;											
								
		//INSERT FINTECH AP
		$query="select nextserial_transaksi('FIN-AP':: text)";
		$lrow=pg_fetch_array(pg_query($query));
		$no_fintech=$lrow["nextserial_transaksi"];
		
		if(!pg_query("insert into data_fa.tblfintech_ap(no_fintech,fk_sbg,tgl_ap,bunga_fintech,jenis,nominal) values ('".$no_fintech."','".$fk_sbg."','".today_db."',".$bunga_fintech.",'Pelunasan',".$kl_lainnya_fintech.")"));
		
		//VA
		if(!pg_query("update data_fa.tblfintech_ap set tagihan_fintech=".$kl_lainnya_fintech.",tgl_bayar_ap='".today_db."' where no_fintech='".$no_fintech."' ")) $l_success=0;							
	
		if(!pg_query("insert into data_fa.tblfintech_ap_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from data_fa.tblfintech_ap where no_fintech='".$no_fintech."'")) $l_success=0;

		//END INSERT
		
	}
	
	/////===================END JURNAL=========================
		
		
	//UPDATE SALDO BANK
	if(!pg_query(update_saldo_bank($fk_bank,$fk_cabang,$nilai_bayar,0,"Pelunasan",$fk_sbg,$no_pelunasan)))$l_success=0;
	
	//UPDATE ANGSURAN
	$lwhere="fk_sbg='".$fk_sbg."' and tgl_bayar is null";
	if(!pg_query(insert_log("data_fa.tblangsuran",$lwhere,'UB'))) $l_success=0;
	if(!pg_query("update data_fa.tblangsuran SET tgl_bayar='".today_db."',no_kwitansi = '".$no_pelunasan."' where ".$lwhere."")) $l_success=0;
	if(!pg_query(insert_log("data_fa.tblangsuran",$lwhere,'UA'))) $l_success=0;
	
	
	$transaksi='Angsuran';
	$referensi=$no_pelunasan;
	$ang_ke=$_REQUEST["lama_pinjaman"];
	if(!pg_query(insert_history_sbg($fk_sbg,$nilai_bayar,$ang_ke,$transaksi,$referensi))) $l_success=0;
	//showquery(insert_history_sbg($fk_sbg,$nilai_bayar,$ang_ke,$transaksi,$referensi));
	
	//====KALO PERPANJANGAN====
	if($_REQUEST["perpanjangan_ke"]>0){
		if(!pg_query("insert into data_fa.tblpelunasan_gadai(fk_sbg,biaya_denda,total_pembayaran,tgl_bayar,nilai_bayar,lama_pelunasan,fk_bank_pelunasan,nilai_penyimpanan,biaya_penjualan,lama_jasa_simpan,diskon_pelunasan,is_perpanjangan,titipan) 
		values('".$fk_sbg."','".$_REQUEST["biaya_denda"]."','".$_REQUEST["total_pembayaran"]."','".today_db."','".$_REQUEST["nilai_bayar"]."','".$_REQUEST["lama_pelunasan"]."','".$_REQUEST["fk_bank_pelunasan"]."','".$_REQUEST["nilai_penyimpanan"]."','".$_REQUEST["biaya_penjualan"]."','".$_REQUEST["lama_jasa_simpan"]."',0,'t','".$_REQUEST["titipan"]."')")) $l_success=0;	
	}
	//echo $l_success;
	//$l_success=0;
}

?>

