<?

function html_additional(){
	
	echo '<input type="hidden" name="tipe" value="Cicilan">';
	echo '<input type="hidden" name="tgl_sistem" value="'.today.'">';
}

function cek_error_module(){
	global $j_action,$strmsg;
	$fk_sbg=$_REQUEST["fk_sbg"];
	$query="
	select tgl_lunas,jenis_produk,fk_cabang,fk_produk,tenor from(
		select * from tblinventory 
		where fk_sbg ='".$_REQUEST["fk_sbg"]."'
	)as tblsbg	
	left join tblproduk on fk_produk=kd_produk
	left join(
		select lama_pinjaman as tenor,no_sbg from data_gadai.tblproduk_cicilan
	)as tblproduk_cicilan on fk_sbg = no_sbg
	";
	//showquery($query);
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	
	$tgl_lunas=$lrow["tgl_lunas"];
	$jenis_produk=$lrow["jenis_produk"];
	$fk_cabang=$lrow["fk_cabang"];
	$fk_produk=$lrow["fk_produk"];
	$tenor=$lrow["tenor"];

	if($tgl_lunas){
		$strmsg.="Kontrak ini sudah lunas <br>";	
	}/*elseif($jenis_produk!='1'){
		$strmsg.="Kontrak ini bukan jenis Cicilan <br>";	
	}*//*elseif($fk_cabang!=$_SESSION["kd_cabang"] && $_SESSION["username"]!='superuser'){
		$strmsg.="Kontrak ini berasal dari cabang lain <br>";	
	}*/
	
	$diskon_pelunasan=$_REQUEST["diskon_pelunasan"];
	if($diskon_pelunasan>0 ){
	
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
		
	
/*	$query="
	select * from viewang_ke
	where fk_sbg='".$fk_sbg."' 
	";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);	
	$saldo_pinjaman=$lrow["saldo_pinjaman"];
	$ang_ke=$lrow["ang_ke"];
*/
	
	$tgl_bayar=convert_date_english($_REQUEST['tgl_bayar']);
	//echo $tgl_bayar;
	$lrs=(pg_query("select sum(pokok_jt)as sisa_pokok,sum(nilai_angsuran)as sisa_angsuran from data_fa.tblangsuran
where fk_sbg='".$fk_sbg."' and tgl_bayar is null and tgl_jatuh_tempo >'".$tgl_bayar."'"));
	$lrow2=pg_fetch_array($lrs);		
	$sisa_pokok=round($lrow2["sisa_pokok"]);

	$sisa_pokok_input=str_replace(',','',$_REQUEST['sisa_pokok']);
	//echo $saldo_pokok;
	if($sisa_pokok!=$sisa_pokok_input){
		//$strmsg.="Nilai Sisa pokok tidak sesuai dengan yang diinput<br>";	
	}

	$query="
	select * from tblproduk_detail_tenor
	where fk_produk='".$fk_produk."' and tenor='".$tenor."'
	";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);						

	$minimal_dipercepat=$lrow["minimal_dipercepat"];
	
	if($minimal_dipercepat!=0){
		if($ang_ke<$minimal_dipercepat){
			//$strmsg.="Pelunasan tidak bisa dipercepat. Minimal pembayaran ".$minimal_dipercepat." angsuran<br>";	
		}
	}

}

function query_additional($pk_id){
	global $no_pelunasan;	
	$no_pelunasan=$pk_id;
}
function save_additional(){
	global $l_success,$no_pelunasan;
	
	$fk_sbg=$_REQUEST["fk_sbg"];
	$tgl_bayar=convert_date_english($_REQUEST['tgl_bayar']);
	
	if(!pg_query(storing($fk_sbg,NULL,'Lunas','Pelunasan',$tgl_bayar)))$l_success=0;
	//showquery(storing($fk_sbg,NULL,'Lunas','Pelunasan'));

	$fk_cabang=get_rec("tblinventory","fk_cabang","fk_sbg='".$fk_sbg."'");
	$fk_bank=$_REQUEST["fk_bank"];
	$fk_cabang_input=$_REQUEST['fk_cabang_input'];
	
	$account = get_coa_bank($fk_bank,$fk_cabang_input);

	//POSTING	
	$total_pelunasan=str_replace(',','',$_REQUEST['total_pelunasan']);
	$total_pembayaran=str_replace(',','',$_REQUEST['total_pembayaran']);
	$titipan_angsuran=str_replace(',','',$_REQUEST['titipan_angsuran']);
	$diskon_pelunasan=str_replace(',','',$_REQUEST['diskon_pelunasan']);
	$pembulatan=$total_pembayaran-$total_pelunasan;
	$bunga_berjalan=str_replace(',','',$_REQUEST['bunga_berjalan']);
	$nilai_bayar_denda=str_replace(',','',$_REQUEST['nilai_bayar_denda']);
	$nilai_bayar_denda2=str_replace(',','',$_REQUEST['nilai_bayar_denda2']);
	$total_denda=$nilai_bayar_denda+$nilai_bayar_denda2;
	$pinalti=str_replace(',','',$_REQUEST['pinalti']);
	$sisa_pokok=str_replace(',','',$_REQUEST['sisa_pokok']);
	$sisa_angsuran=str_replace(',','',$_REQUEST['sisa_angsuran']);
	//UPDATE SALDO BANK	
	if(!pg_query(update_saldo_bank($fk_bank,$fk_cabang,$total_pembayaran,0,"Pelunasan",$fk_sbg,$no_pelunasan)))$l_success=0;
	
	$query="
	select * from (
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
	
	$fom=date('Y-m',strtotime($tgl_bayar)).'-01 00:00:00';
	$query_sisa="
	select saldo_pinjaman as ar_cicilan from data_fa.tblangsuran 
	where fk_sbg='".$fk_sbg."' and tgl_bayar is not null
	order by angsuran_ke desc
	";
	$lrs_sisa=pg_query($query_sisa);
	$lrow_sisa=pg_fetch_array($lrs_sisa);	
	$ar_cicilan=$lrow_sisa["ar_cicilan"];	
	
/*	$query1="
	select sum(bunga_jt)as total_bunga from data_fa.tblangsuran 
	where fk_sbg='".$fk_sbg."'  and tgl_bayar is not null
	and tgl_jatuh_tempo <'".$fom."'
	";
	showquery($query1);
	$lrs1=pg_query($query1);
	$lrow1=pg_fetch_array($lrs1);		
	$total_bunga=$lrow1["total_bunga"];		
	
	$query1="
	select sum(akrual1+akrual2)as total_akrual from data_fa.tblangsuran 
	where fk_sbg='".$fk_sbg."'
	and tgl_jatuh_tempo <'".$fom."'
	";
	showquery($query1);
	$lrs1=pg_query($query1);
	$lrow1=pg_fetch_array($lrs1);		
	$total_akrual=$lrow1["total_akrual"];		*/
	
	$query_akrual="
	select sum(akrual1+akrual2)as saldo_akrual from data_fa.tblangsuran 
	where fk_sbg='".$fk_sbg."' and tgl_jatuh_tempo >='".$fom."'
	";
	//showquery($query_akrual);
	$lrs_akrual=pg_query($query_akrual);
	$lrow_akrual=pg_fetch_array($lrs_akrual);	
	$saldo_akrual=round($lrow_akrual["saldo_akrual"]);	
	//$bunga_jt=round($lrow_akrual["bunga_jt"]);	
	
	//$sisa_bunga=$bunga_berjalan-($total_akrual-$total_bunga);	//pend bunga sisa yang belum diakui di akru
	$sisa_bunga=$ar_cicilan-$sisa_angsuran-$sisa_pokok-$saldo_akrual-$bunga_berjalan;
	//echo $ar_cicilan."-".$sisa_angsuran."-".$sisa_pokok."-".$saldo_akrual;
	
	
	
	$arrPost = array();			
	$arrPost["bank"]				= array('type'=>'d','value'=>$total_pembayaran,'account'=>$account,'reference'=>$no_pelunasan);
	if($fk_cabang!=$fk_cabang_input){
		$rpkc 	  = get_coa_cabang($fk_cabang,$fk_cabang_input);
		$arrPost["rpkc"]			= array('type'=>'c','value'=>$total_pembayaran,'account'=>$rpkc,'reference'=>$no_pelunasan);
		//cek_balance_array_post($arrPost);
		if(!posting('PEMBAYARAN CICILAN',$fk_sbg,$tgl_bayar,$arrPost,$fk_cabang_input,'00'))$l_success=0;
			
		if($fk_cabang_input==cabang_ho){
			
		$arrPost = array();
		$arrPost["rpkp"]			= array('type'=>'d','value'=>$total_pembayaran,'reference'=>$no_pelunasan);		
		
		}else{
			
		$rpkc_keluar				= get_coa_cabang($fk_cabang_input,cabang_ho);
		$rpkc_masuk					= get_coa_cabang($fk_cabang,cabang_ho);	
			
		$arrPost = array();
		$arrPost["rpkc_masuk"]		= array('type'=>'d','value'=>$total_pembayaran,'reference'=>$no_pelunasan,'account'=>$rpkc_masuk);	
		$arrPost["rpkc_keluar"]		= array('type'=>'c','value'=>$total_pembayaran,'reference'=>$no_pelunasan,'account'=>$rpkc_keluar);
		//cek_balance_array_post($arrPost);
		if(!posting('PEMBAYARAN CICILAN',$fk_sbg,$tgl_bayar,$arrPost,cabang_ho,'00'))$l_success=0;		
		
		$arrPost = array();
		$arrPost["rpkp"]			= array('type'=>'d','value'=>$total_pembayaran,'reference'=>$no_pelunasan);					

		}
	}
	
	$arrPost["piutang_pembiayaan"]				= array('type'=>'c','value'=>$ar_cicilan,'reference'=>$no_pelunasan);
	
	$arrPost["pend_bunga_yad"]	        		= array('type'=>'d','value'=>$saldo_akrual,'reference'=>$no_pelunasan);
	
	$arrPost["pend_bunga_".$kategori]	        = array('type'=>'c','value'=>$sisa_bunga*-1,'reference'=>$no_pelunasan);
	//_".$kategori
	
	if($diskon_pelunasan>0){
		$coa_pend=$fk_cabang.'.'.get_rec("tbltemplate_coa","coa","used_for='pend_bunga_".$kategori."'");
		$arrPost["pend_bunga1"]					= array('type'=>'d','value'=>$diskon_pelunasan,'account'=>$coa_pend,'reference'=>$no_pelunasan);
		//_".$kategori
	}
		
	
	if($nilai_bayar_denda>0){
		$arrPost["pend_denda_".$kategori]		= array('type'=>'c','value'=>$nilai_bayar_denda,'reference'=>$no_pelunasan);
	}
	if($nilai_bayar_denda2>0){
		$arrPost["utang_denda"]					= array('type'=>'c','value'=>$nilai_bayar_denda2,'reference'=>$no_pelunasan);
	}

	if($pinalti>0){
		$arrPost["pend_admin_".$kategori]		= array('type'=>'c','value'=>$pinalti,'reference'=>$no_pelunasan);
	}
	
	$pembulatan=($total_pelunasan-$total_pembayaran)*-1;
	if($pembulatan>0){
		$arrPost["pend_lain2"]	   				= array('type'=>'c','value'=>$pembulatan,'reference'=>$no_pelunasan);
	}
	
	//echo $tgl_bayar;
	//cek_balance_array_post($arrPost);
	if(!posting('PELUNASAN CICILAN',$fk_sbg,$tgl_bayar,$arrPost,$fk_cabang,'00'))$l_success=0;
	if($l_success==0){
		cek_balance_array_post($arrPost);
	}
		
	$referensi=$no_pelunasan;
	$ang_ke=get_rec("data_fa.tblangsuran","angsuran_ke","fk_sbg='".$fk_sbg."' and tgl_bayar is null","angsuran_ke asc");
	if(!$ang_ke)$ang_ke=get_rec("data_fa.tblangsuran","angsuran_ke","fk_sbg='".$fk_sbg."'","angsuran_ke desc");
	//update ke saldo denda
	$type="denda";
	$lwhere="fk_sbg='".$fk_sbg."'";		
	$total_denda_lalu=round(get_rec("data_fa.tbl".$type."","saldo_".$type."",$lwhere));

	if(!pg_query(insert_log("data_fa.tbl".$type."",$lwhere,'UB'))) $l_success=0;
	if(!pg_query("update data_fa.tbl".$type." SET saldo_".$type." =0 where ".$lwhere."")) $l_success=0;
	if(!pg_query(insert_log("data_fa.tbl".$type."",$lwhere,'UA'))) $l_success=0;

	
	if($_REQUEST["denda_keterlambatan"]>0){
		$transaksi='Denda Keterlambatan';
		if(!pg_query(insert_history_sbg($fk_sbg,$_REQUEST["denda_keterlambatan"],$ang_ke,$transaksi,$referensi))) $l_success=0;
	}
	if($_REQUEST["denda_ganti_rugi"]>0){
		$transaksi='Denda Ganti Rugi';
		if(!pg_query(insert_history_sbg($fk_sbg,$_REQUEST["denda_ganti_rugi"],$ang_ke,$transaksi,$referensi))) $l_success=0;
	}
	
	if($_REQUEST["nilai_bayar_denda"]>0){
		$transaksi='Bayar Denda Ganti Rugi';
		if(!pg_query(insert_history_sbg($fk_sbg,$_REQUEST["nilai_bayar_denda"],$ang_ke,$transaksi,$referensi))) $l_success=0;
		//showquery(insert_history_sbg($fk_sbg,$_REQUEST["nilai_bayar_denda"],$ang_ke,$transaksi,$referensi));
	}
	if($_REQUEST["nilai_bayar_denda2"]>0){
		$transaksi='Bayar Denda Keterlambatan';
		if(!pg_query(insert_history_sbg($fk_sbg,$_REQUEST["nilai_bayar_denda2"],$ang_ke,$transaksi,$referensi))) $l_success=0;
	}
	
	//showquery(insert_history_sbg($fk_sbg,$denda-$total_denda_lalu,$ang_ke,$transaksi,$referensi));

	$transaksi='Angsuran';
	if(!pg_query(insert_history_sbg($fk_sbg,$total_pembayaran,$ang_ke,$transaksi,$referensi,$total_denda))) $l_success=0;
	//echo $no_pelunasan;

	$lwhere="fk_sbg='".$fk_sbg."' and tgl_bayar is null";
	if(!pg_query(insert_log("data_fa.tblangsuran",$lwhere,'UB'))) $l_success=0;
	if(!pg_query("update data_fa.tblangsuran SET tgl_bayar='".$tgl_bayar."', no_kwitansi ='".$no_pelunasan."'  where ".$lwhere."")) $l_success=0;
	//showquery("update data_fa.tblangsuran SET tgl_bayar='".today_db."', no_kwitansi ='".$no_pelunasan."'  where ".$lwhere."");
	if(!pg_query(insert_log("data_fa.tblangsuran",$lwhere,'UA'))) $l_success=0;

	//echo $l_success;
	//showquery(insert_history_sbg($fk_sbg,$total_pembayaran-$denda,$ang_ke,$transaksi,$referensi,$denda));

	//KALO PERPANJANGAN
	if($_REQUEST["perpanjangan_ke"]>0){
		if(!pg_query("insert into data_fa.tblpelunasan_cicilan(fk_sbg,total_pelunasan,titipan_angsuran,total_pembayaran,tgl_bayar,fk_bank_pelunasan,tgl_jatuh_tempo_akhir,total_denda_lalu,total_denda_kini,denda_keterlambatan,denda_ganti_rugi,total_pelunasan,diskon_pelunasan,is_perpanjangan,nilai_bayar_denda,nilai_bayar_denda2) 
		values('".$fk_sbg."','".$_REQUEST["total_pelunasan"]."','".round($_REQUEST["titipan_angsuran"])."','".$_REQUEST["total_pembayaran"]."','".$tgl_bayar."','".$_REQUEST["fk_bank_pelunasan"]."','".convert_date_english($_REQUEST["tgl_jatuh_tempo_akhir"])."','".$_REQUEST["total_denda_lalu"]."','".$_REQUEST["total_denda_kini"]."','".$_REQUEST["denda_keterlambatan"]."','".$_REQUEST["denda_ganti_rugi"]."','".$_REQUEST["total_pelunasan"]."','0','t','".$_REQUEST["nilai_bayar_denda"]."','".$_REQUEST["nilai_bayar_denda2"]."')")) $l_success=0;	
	}
	//$l_success=0;
	
	
}
//	if($titipan_angsuran>0){
//		$arrPost["kl_titipan_angsuran"]			= array('type'=>'d','value'=>$titipan_angsuran,'reference'=>$no_pelunasan);
//	}
//	if($akrual>0){
//		$arrPost["accrual_pendapatan_bunga"]	= array('type'=>'c','value'=>$akrual,'reference'=>$no_pelunasan);
//	}

?>