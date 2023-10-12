<?
set_time_limit(0);

function recount_coa(){
	global $fh;
	//fwrite($fh,"start recount_coa -- ".date('Ymd_His')."\r\n");
	//$lrs = pg_query("select * from tblcoa");
	
	$today_db=get_rec("tblsetting",'tgl_sistem');
	$yesterday=date("Y-m-d",strtotime('-1 day',strtotime($today_db)));
	$bulan=date('n',strtotime($yesterday));
	$tahun=date('Y',strtotime($yesterday));
	
	$lrs = pg_query("select fk_coa as coa,* from data_accounting.tblsaldo_coa where tr_month=".$bulan." and tr_year=".$tahun."");
	while($lrow = pg_fetch_array($lrs)){
		//if(create_saldo_coa($lrow['coa'],'',false,'',''));
		if(create_saldo_coa($lrow['coa'],$today_db,false,'',''));
		//fwrite($fh,"recount_coa ".$lrow['coa']."\r\n");
	}
	//fwrite($fh,"end recount_coa -- ".date('Ymd_His')."\r\n");
}


function closing_additional(){
	$today_db=get_rec("tblsetting",'tgl_sistem');
	//echo $l_success.'2<br>';
	// EVALUASI OUTLET TAPI BLUM FIX  
	$month=date("m",strtotime($today_db));
	
	$query="
	select * from(
		select * from tblcabang
		where cabang_active='t' and kd_cabang!='999'
	)as tblcabang
	left join (
		select sum(saldo_pokok) as total_os,fk_cabang_sbg from viewang_ke
		left join (select fk_cabang as fk_cabang_sbg,fk_sbg as fk_sbg1 from tblinventory )as tblinventory on fk_sbg1=fk_sbg
		group by fk_cabang_sbg
	)as tblar
	on kd_cabang=fk_cabang_sbg
	";
	$lrs=pg_query($query);
	
	while($lrow=pg_fetch_array($lrs)){
		$querygrade="
		select * from tblgrade 
		where jenis_outlet='".$lrow["jenis_cabang"]."'
		";
		$lrsgrade=pg_query($querygrade);
		
		while($lrowgrade=pg_fetch_array($lrsgrade)){
			if($lrowgrade["evaluasi_outlet"]>12)$lrowgrade["evaluasi_outlet"]=$lrowgrade["evaluasi_outlet"]/12;
			if($month % $lrowgrade["evaluasi_outlet"]==0){

				if($lrowgrade["min_grade_outstanding"]<$lrow["total_os"]){		
					$lwhere="kd_cabang='".$lrow["kd_cabang"]."'";
					if(!pg_query(insert_log("tblcabang",$lwhere,'UB'))) $l_success=0;	
					if(!pg_query("update tblcabang SET fk_grade='".$lrowgrade["fk_grade"]."' where ".$lwhere."")) $l_success=0;	
					//showquery("update tblcabang SET fk_grade='".$lrow["fk_grade"]."' where ".$lwhere."");
					if(!pg_query(insert_log("tblcabang",$lwhere,'UA'))) $l_success=0;	
				}
			}
		}
	}	
	
}

function insert_closing(){
	$l_success=1;	

	//===================================================================
	/*    INSERT CLOSING HARIAN       */
	$today_db=get_rec("tblsetting",'tgl_sistem');
	$fk_closing=date("Ymd",strtotime($today_db));
	if(!pg_query("insert into data_gadai.tblclosing_harian(no_closing,tgl_closing,user_closing) values('".$fk_closing."','".$today_db."','".$_SESSION["username"]."')")) $l_success=0;

	//log begin
	if(!pg_query("insert into data_gadai.tblclosing_harian_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from data_gadai.tblclosing_harian where no_closing='".$fk_closing."'"))$l_success=0;  
	//end log
	
	
	// CASE CABANG BELUM CLOSE CASHIER
	$query_close_cashier="select * from tblcabang where status_kasir!='Close' and false";//buat testing
	//showquery($query_close_cashier);
	$lrs_close_cashier=pg_query($query_close_cashier);
	
	$fk_bank_keluar='04';
	$fk_bank_masuk='01';
	while($lrow=pg_fetch_array($lrs_close_cashier)){
		//if(!pg_query("insert into data_gadai.tblclosing_harian_detail(fk_closing,fk_cabang,keterangan,no_ref) values('".$fk_closing."','".$lrow["kd_cabang"]."','Detail cabang belum close cashier',null)")) $l_success=0;
		
		$tbl="tblcabang";
		$lwhere="kd_cabang='".$lrow["kd_cabang"]."'";
		if(!pg_query(insert_log($tbl,$lwhere,'UB'))) $l_success=0;
		if(!pg_query("update ".$tbl." SET status_kasir='Close' where ".$lwhere."")) $l_success=0;
		if(!pg_query(insert_log($tbl,$lwhere,'UA'))) $l_success=0;

		$total=get_saldo_bank($fk_bank_keluar,$lrow["kd_cabang"]);
		if($total){
			if(!pg_query(update_saldo_bank($fk_bank_keluar,$lrow["kd_cabang"],0,$total,"Closing Harian",$fk_closing)))$l_success=0;	
			if(!pg_query(update_saldo_bank($fk_bank_masuk,$lrow["kd_cabang"],$total,0,"Closing Harian",$fk_closing)))$l_success=0;
			
			$fk_cabang=$lrow["kd_cabang"];
			$account_masuk 			= get_coa_bank($fk_bank_masuk,$fk_cabang);
			$account_keluar 		= get_coa_bank($fk_bank_keluar,$fk_cabang);
					
			$arrPost = array();
			$arrPost["bank_masuk"]		= array('type'=>'d','value'=>$total,'account'=>$account_masuk);
			$arrPost["bank_keluar"]		= array('type'=>'c','value'=>$total,'account'=>$account_keluar);
			//print_r($arrPost);
			//cek_balance_array_post($arrPost);
			if(!posting('CLOSE CASHIER',$fk_closing,$today_db,$arrPost,$fk_cabang,'00'))$l_success=0;
		}
	}
	
/*	// SBG YANG GANTUNG/BLUM CAIR
	$query_transaksi_gantung="
	select * from viewkontrak 
	left join (
		select *, fk_cabang as fk_cabang_viewtaksir from viewtaksir 
	)as viewtaksir on no_fatg = fk_fatg
	left join tblinventory on fk_sbg=no_sbg
	where (status_approval ='Need Approval' or (status_approval ='Approve' and tgl_cair is null)) and status_data='Approve'";
	//showquery($query_transaksi_gantung);
	$lrs_transaksi_gantung=pg_query($query_transaksi_gantung);
	while($lrow=pg_fetch_array($lrs_transaksi_gantung)){
		if(!pg_query("insert into data_gadai.tblclosing_harian_detail(fk_closing,fk_cabang,keterangan,no_ref) values('".$fk_closing."','".$lrow["fk_cabang_viewtaksir"]."','Detail transaksi yang gantung','".$lrow["no_sbg"]."')")) $l_success=0;
		
		if(pg_num_rows(pg_query("select * from data_gadai.tblproduk_gadai where no_sbg='".$lrow["no_sbg"]."' "))){
			$tbl="data_gadai.tblproduk_gadai";
		}else{
			$tbl="data_gadai.tblproduk_cicilan";
		}
	
		$lwhere="no_sbg='".$lrow["no_sbg"]."'";
		if(!pg_query(insert_log($tbl,$lwhere,'UB')))$l_success=0;	
		if(!pg_query("update ".$tbl." set status_data='Batal',tgl_batal='".$today_db."' where ".$lwhere.""))$l_success=0; 
		//showquery("update ".$tbl." set status_data='Batal' where ".$lwhere."");
		if(!pg_query(insert_log($tbl,$lwhere,'UA')))$l_success=0;
		
	}*/
	
	
	//log begin
	//$l_id_log_ia=get_last_id("data_gadai.tblclosing_harian_log","pk_id_log");
	//if(!pg_query("insert into data_gadai.tblclosing_harian_detail_log select *,'".$l_id_log_ia."' from data_gadai.tblclosing_harian_detail where fk_closing='".$fk_closing."'")) $l_success=0;
	//end log	
	//echo $l_success.'3<br>';
	if($l_success==0)return $l_success;	
	else return $fk_closing;
	
}
function closing(){
	$l_success=1;	
	
	$fk_closing=insert_closing();
	if($fk_closing==0)$l_success=0;
	$today_db=date("Y-m-d",strtotime(get_rec("tblsetting",'tgl_sistem')));
	//===================================================================
	
		
	//PEMINDAHAN SALDO
	echo 'PEMINDAHAN SALDO '.$today_db.'<br>';
	$masa_tenggang=30;//dikunci fix 30
	
	$query="
	select * from (
		select * from viewjatuh_tempo where overdue = ".$masa_tenggang." 
	) as tbljt
	left join (
		select fk_sbg as fk_sbg1,saldo_pokok as pokok_awal,saldo_bunga as bunga_awal from data_fa.tblangsuran
		where angsuran_ke=0
	)as tblangsuran on fk_sbg=fk_sbg1	
	inner join (
		select fk_cif,fk_sbg as no_sbg,fk_cabang,jenis_produk from tblinventory 
		left join tblproduk on fk_produk=kd_produk
	)as tblinv on no_sbg=fk_sbg
	where jenis_produk=0;
	" 
	;
	//showquery($query);
	$lrs=pg_query($query);
	while($lrow = pg_fetch_array($lrs)){
		$fk_cabang=$lrow["fk_cabang"];
		$piutang_gadai=$lrow["pokok_awal"];
		
		
		$arrPost = array();		
		$arrPost["piutang_tunggu"]			= array('type'=>'d','value'=>$piutang_gadai,'fk_supplier'=>$lrow["fk_cif"],'reference'=>$fk_closing);
		$arrPost["piutang_gadai"]		    = array('type'=>'c','value'=>$piutang_gadai,'reference'=>$fk_closing);
	
		//cek_balance_array_post($arrPost);
		//if(!posting('PEMINDAHAN SALDO PIUTANG',$lrow["fk_sbg"],$today_db,$arrPost,$fk_cabang,'00'))$l_success=0;
	
	}				
	

	
	//===================================================================
	/*  ALOKASI TITIPAN CICILAN   */	
/*	echo 'ALOKASI TITIPAN '.$today_db.'<br>';
	
	//cek titipan , kalo ada yg jt tempo di hari itu , dilunasin by sistem angsurannya (cicilan)
	$query="select * from data_fa.tbltitipan 
	inner join(
		select fk_sbg as fk_sbg1 from tblinventory 
		left join tblproduk on fk_produk=kd_produk
		where jenis_produk='1'
	)as tblmain
	on fk_sbg =fk_sbg1
	where saldo_titipan>0";
	$lrs=pg_query($query);
	//showquery($query);

	$transaksi='Angsuran';
	$referensi=$fk_closing;

	while($lrow=pg_fetch_array($lrs)){
		$saldo_titipan=$lrow["saldo_titipan"];
		//echo $saldo_titipan;	
		
		//DIALOKASI KE PEMBAYARAN
		$query_1="select * from data_fa.tblangsuran where fk_sbg='".$lrow["fk_sbg"]."' and tgl_bayar is null 
		and tgl_jatuh_tempo<='".$today_db."' 
		order by angsuran_ke asc";
		//showquery($query_1);
		$lrs_1=pg_query($query_1);
		
		$query_inventory=pg_query("select fk_cabang,fk_cif from tblinventory where fk_sbg='".$lrow["fk_sbg"]."'");
		$lrow_inventory=pg_fetch_array($query_inventory);
		$fk_cabang=$lrow_inventory["fk_cabang"];		
		$fk_cif=$lrow_inventory["fk_cif"];		
		//echo $fk_cabang;
		//echo $fk_cif;
		while($lrow_1=pg_fetch_array($lrs_1)){

			if($saldo_titipan>=$lrow_1["nilai_angsuran"]){
				
				$query_2="select * from data_fa.tblangsuran where fk_sbg='".$lrow["fk_sbg"]."' and tgl_bayar is not null 
				and tgl_jatuh_tempo<='".$today_db."' 
				order by angsuran_ke desc";
				//showquery($query_2);
				$lrs_2=pg_query($query_2);
				$lrow_2=pg_fetch_array($lrs_2);
				$akrual2=$lrow_2["akrual2"];
				
				$bunga_jt=$lrow_1["bunga_jt"];
				$nilai_angsuran=$lrow_1["nilai_angsuran"];		
				$akrual1=$lrow_1["akrual1"];		
				$saldo_titipan-=$nilai_angsuran;
				
				$lwhere="fk_sbg='".$lrow["fk_sbg"]."' and angsuran_ke='".$lrow_1["angsuran_ke"]."'";
				
				if(!pg_query(insert_log("data_fa.tblangsuran",$lwhere,'UB')));
				if(!pg_query("update data_fa.tblangsuran SET tgl_bayar='".$today_db."',tgl_stop_accrual=NULL where ".$lwhere."")) $l_success=0;
				//showquery("update data_fa.tblangsuran SET tgl_bayar='".$today_db."' where ".$lwhere."");
				if(!pg_query(insert_log("data_fa.tblangsuran",$lwhere,'UA')));
				
				if(!pg_query(insert_history_sbg($lrow["fk_sbg"],$nilai_angsuran,$lrow_1["angsuran_ke"],$transaksi,$referensi))) $l_success=0;
				
				$arrPost = array();
				$arrPost["kl_titipan_angsuran"]			= array('type'=>'d','value'=>$nilai_angsuran,'reference'=>$fk_closing);
				$arrPost["ar_cicilan"]					= array('type'=>'c','value'=>$nilai_angsuran,'fk_supplier'=>$fk_cif,'reference'=>$fk_closing);
				if(!posting('ALOKASI PEMBAYARAN CICILAN',$lrow["fk_sbg"],$today_db,$arrPost,$fk_cabang,'00'))$l_success=0;	


				if($bunga_jt!=($akrual1+$akrual2)){
					$bunga_jt=$akrual1+$akrual2;
				}
				$arrPost = array();
				$arrPost["ue_cicilan"]					= array('type'=>'d','value'=>$bunga_jt,'reference'=>$fk_closing);				
				$arrPost["accrual_pendapatan_bunga"]	= array('type'=>'c','value'=>$akrual2,'reference'=>$fk_closing);
				$arrPost["pendapatan_bunga_pinjaman"]	= array('type'=>'c','value'=>$akrual1,'reference'=>$fk_closing);
				if(!posting('ALOKASI PEMBAYARAN CICILAN',$lrow["fk_sbg"],$today_db,$arrPost,$fk_cabang,'00'))$l_success=0;	

				$nilai_pembayaran=$lrow_1["nilai_angsuran"];
				
				$fk_sbg=$lrow["fk_sbg"];
				$query2="select tgl_jatuh_tempo as tgl_jt from data_fa.tblangsuran where fk_sbg='".$fk_sbg."' 
				and tgl_bayar is null
				order by angsuran_ke asc";
				//showquery($query2);
				$lrs_2=pg_query($query2);
				$lrow_2=pg_fetch_array($lrs_2);
				$tgl_jt=$lrow_2["tgl_jt"];
				//echo $tgl_jt;
				if($tgl_jt){
					$lwhere="fk_sbg='".$fk_sbg."'";
					if(!pg_query(insert_log("tblinventory",$lwhere,'UB'))) $l_success=0;
					//showquery("update tblinventory SET tgl_jt='".$tgl_jt."'  where ".$lwhere."");
					if(!pg_query("update tblinventory SET tgl_jt='".$tgl_jt."'  where ".$lwhere."")) $l_success=0;		
					if(!pg_query(insert_log("tblinventory",$lwhere,'UA'))) $l_success=0;
				}		

			}
		}		
		//echo $l_success.'4<br>';
		//UPDATE SISA TITIPAN
		$type="titipan";
		$nilai_bayar=$saldo_titipan;
		
		if(!pg_query(insert_log("data_fa.tbl".$type."","fk_sbg='".$lrow["fk_sbg"]."'",'UB')));
		if(!pg_query("update data_fa.tbl".$type." SET saldo_".$type." =".$nilai_bayar." where fk_sbg='".$lrow["fk_sbg"]."'")) $l_success=0;			
		if(!pg_query(insert_log("data_fa.tbl".$type."","fk_sbg='".$lrow["fk_sbg"]."'",'UA')));
		
	}	
*/	
	//$l_success=0;	

	//===================================================================
	/*  AKRU JT CICILAN  */	
/*	echo 'AKRUAL JATUH TEMPO CICILAN '.$today_db.'<br>';
	

	$query="
		select * from(
			select * from data_fa.tblangsuran 
			where tgl_bayar is null and tgl_jatuh_tempo like '%".$today_db."%'
		)as tblangsuran		
		inner join (
			select fk_sbg as no_sbg,jenis_produk,status_sbg,fk_cabang from tblinventory 
			left join tblproduk on fk_produk=kd_produk	
			where jenis_produk=1 and status_sbg='Liv' 	
		)as tblinventory on fk_sbg=no_sbg
	";
	$lrs=pg_query($query);
	
	while($lrow=pg_fetch_array($lrs)){		
		//showquery($query);
		$arrPost = array();
		$akrual_jt=$lrow["akrual1"];			
		$overdue=$lrow["overdue"];			
		//if($overdue<$stop_accrual){
		if(!pg_num_rows(pg_query("select * from data_fa.tblangsuran where tgl_stop_accrual is not null and fk_sbg='".$lrow["fk_sbg"]."'"))){	
			$arrPost["accrual_pendapatan_bunga"]	= array('type'=>'d','value'=>$akrual_jt,'reference'=>$fk_closing);
			$arrPost["pendapatan_bunga_pinjaman"]	= array('type'=>'c','value'=>$akrual_jt,'reference'=>$fk_closing);
			//cek_balance_array_post($arrPost);
			$fk_cabang=$lrow["fk_cabang"];
			if(!posting('AKRUAL JATUH TEMPO',$lrow["fk_sbg"],$today_db,$arrPost,$fk_cabang,'00'))$l_success=0;	
		}else{
			$tgl_stop_accrual=get_rec("data_fa.tblangsuran","tgl_stop_accrual","tgl_stop_accrual is not null and fk_sbg='".$lrow["fk_sbg"]."'");
			$lwhere="fk_sbg='".$lrow["fk_sbg"]."' and angsuran_ke='".$lrow["angsuran_ke"]."' ";
		
			if(!pg_query(insert_log("data_fa.tblangsuran",$lwhere,'UB')));
			if(!pg_query("update data_fa.tblangsuran SET tgl_stop_accrual='".$tgl_stop_accrual."' where ".$lwhere."")) $l_success=0;
			if(!pg_query(insert_log("data_fa.tblangsuran",$lwhere,'UA')));

		}
		//}
	}	
	*/
	//$l_success=0;	


	//======================================================================================================//
	/*  UPDATE AKHIR BULAN    */		
	
	$fom=date("Y-m",strtotime($today_db)).'-01';
	$eom=date("Y-m-t",strtotime($today_db));
	$current_month=date("Y-m",strtotime($today_db));
	$last_month=date("Y-m-d",strtotime('-1 second',strtotime($fom)));
	$bulan=date('m',strtotime($last_month));
	$tahun=date('Y',strtotime($last_month));
	
	//echo today_db.'-'.$eom;
	if($today_db==$eom){
		
		$recount=true;		
	}
	//closing_additional();
	
	//======================================================================================================//
	/*   UPDATE TGL SISTEM KE HARI SELANJUTNYA DAN JUGA INSERT SALDO BANK     */	
	echo 'PINDAH SALDO BANK '.$today_db.'<br>';
	$nextday=date("Y-m-d",strtotime('+1 day',strtotime($today_db)));
	
	//echo $nextday;
	if(!pg_query("update tblsetting set tgl_sistem='".$nextday."' ")) $l_success=0;
	//showquery("update tblsetting set tgl_sistem='".$nextday."' ");

	$p_table="data_fa.tblsaldo_bank";
	$p_where="tgl='".$today_db."'";
	$lquery = "select * from ".$p_table." where ".$p_where." order by fk_cabang,fk_bank";
	$lrs=pg_query($lquery);
	while($lrow = pg_fetch_array($lrs)){
		//showquery(create_saldo_bank($lrow["fk_bank"],$lrow["fk_cabang"],$nextday));
		//echo date("H:i:s").'<br>';
		if(!pg_query(create_saldo_bank($lrow["fk_bank"],$lrow["fk_cabang"],$nextday,$lrow["akhir"])))$l_success=0;
		//echo date("H:i:s").'<br>';
	}
	//showquery($lquery);

	if($recount==true){
		echo 'RECOUNTING AKHIR BULAN '.$today_db.'<br>';
		recount_coa();
	}
	
	//echo $l_success.'abc<br>';
	//$l_success=0;

	return $l_success;
}

?>
