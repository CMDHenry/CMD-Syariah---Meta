<?

function html_additional(){
	
	echo '<input type="hidden" name="tipe" value="Cicilan">';
	echo '<input type="hidden" name="tgl_sistem" value="'.today.'">';

	echo '<input type="hidden" name="ang_ke_awal" value="">';
}

function cek_error_module(){
	global $j_action,$strmsg;
	
	$fk_sbg=$_REQUEST["fk_sbg"];
	$tgl_bayar=convert_date_english($_REQUEST['tgl_bayar']);
	$tgl_input=convert_date_english($_REQUEST['tgl_input']);	
	
	$angsuran_ke=$_REQUEST["angsuran_ke"];
	$overdue=($_REQUEST['overdue']);
	$cara_bayar=$_REQUEST["cara_bayar"];
	$arr_cara_bayar_cabang=array('Cash','Collector','Giro/Cek');
	
	// Hapus blokir 90 hari, SOP dari management (tim UUS) tanggal 22 September 2023
	// $ovd_blokir=get_rec("tblsetting","ovd_blokir","ovd_blokir>0");	
	// if($overdue>$ovd_blokir && !in_array($cara_bayar,$arr_cara_bayar_cabang)){//ini untuk pembayaran online
	// 	if(!pg_num_rows(pg_query("select * from data_fa.tblapproval_buka_blokir where fk_sbg='".$fk_sbg."' and tgl_buka between '".date("m/d/Y 00:00:00")."' and '".date("m/d/Y 23:59:59")."'"))){
	// 		$strmsg.="Tagihan melebihi batas OD ".$ovd_blokir." hari<br>";	
	// 	}
	// }
	
	
	$query="
	select tgl_lunas,jenis_produk,fk_cabang,jmlh_tunggakan,tgl_bayar_terakhir,status from(
		select * from tblinventory 
		where fk_sbg ='".$fk_sbg."'
	)as tblsbg	
	left join tblproduk on fk_produk=kd_produk
	left join(
		select count(1)as jmlh_tunggakan , fk_sbg as fk_sbg1 from data_fa.tblangsuran
		where tgl_jatuh_tempo<='".$tgl_bayar."' and tgl_bayar is null
		group by fk_sbg
	)as tblangs on fk_sbg=fk_sbg1
	left join(
		select distinct on(fk_sbg)fk_sbg as fk_sbg_bayar, tgl_bayar as tgl_bayar_terakhir,nilai_angsuran as nominal_bayar_terakhir,no_kwitansi as no_kwitansi_terakhir from data_fa.tblangsuran 
		where angsuran_ke>0 and angsuran_ke<'".$_REQUEST["angsuran_ke"]."'
		order by fk_sbg,angsuran_ke desc 
	)as tblbayar on fk_sbg=fk_sbg_bayar	
	";
	//showquery($query);
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	
	$tgl_lunas=$lrow["tgl_lunas"];
	$jenis_produk=$lrow["jenis_produk"];
	$fk_cabang=$lrow["fk_cabang"];
	$jmlh_tunggakan=$lrow["jmlh_tunggakan"];
	$tgl_bayar_terakhir=date("m/d/Y",strtotime($lrow["tgl_bayar_terakhir"]));
	$status=$lrow["status"];

	if($tgl_lunas){
		$strmsg.="Kontrak ini sudah lunas <br>";	
	}/*elseif($fk_cabang!=$_SESSION["kd_cabang"]&& $_SESSION["jenis_user"]!='HO'){
		$strmsg.="Kontrak ini berasal dari cabang lain <br>";	
	}*/elseif($status=='Tarik'){
		$strmsg.="Kontrak ini sedang ditarik<br>";	
	}
	
	if($angsuran_ke>1 && strtotime($tgl_bayar)<strtotime($tgl_bayar_terakhir)){
		$strmsg.="Tgl Bayar tidak boleh lebih kecil dari angsuran sebelumnya<br>";	
	}
						
	if($_REQUEST["nilai_bayar_angsuran"]>0 &&($_REQUEST["nilai_bayar_angsuran"]!=$_REQUEST["nilai_angsuran"])){
		$strmsg.="Nilai Bayar Angsuran Salah<br>";
	}
	
	if($_REQUEST["nilai_bayar_angsuran"] % $_REQUEST["nilai_angsuran"] !=0){
		$strmsg.="Nilai Bayar Angsuran Tidak Sama<br>";
	}elseif($_REQUEST["nilai_angsuran"]>0){ //kalau masih ada sisa angsuran
		$jmlh_angs=$_REQUEST["nilai_bayar_angsuran"]/$_REQUEST["nilai_angsuran"];	

		if(pg_num_rows(pg_query("select * from data_fa.tblpembayaran_cicilan where fk_sbg='".$fk_sbg."' and angsuran_ke>='".$angsuran_ke."' and angsuran_ke<='".($angsuran_ke+($jmlh_angs-1))."' and tgl_batal is null"))){
			$strmsg.="Angsuran ke-".$angsuran_ke." sudah diinput<br>";
		}
		
		if($jmlh_tunggakan==3){
			$minimal=3;
		}elseif($jmlh_tunggakan==4 || $jmlh_tunggakan==5){
			$minimal=2;
		}elseif($jmlh_tunggakan>=6){
			$minimal=3;
		}
		
		$sisa_angsuran=$jmlh_tunggakan-$jmlh_angs;
		if($sisa_angsuran>$minimal && $minimal){
			//$strmsg.="Sisa Angsuran Tunggakan minimal ".$minimal."<br>";
		}
	}
	
	
/*	if(strtotime(convert_date_english($_REQUEST['tgl_bayar']))<strtotime(today_db) && $_SESSION["jenis_user"]!='HO'){
		//$strmsg.="Tgl Bayar tidak bisa mundur dari tgl sistem kecuali user HO <br>";	
	}
*/	
	if(strtotime($tgl_bayar)>strtotime(date("m/d/Y"))){
		$strmsg.="Tgl Bayar lebih besar dari hari ini<br>";	
	}
	
	if(!in_array($cara_bayar,$arr_cara_bayar_cabang) && $strmsg){
		$strmsg.="Silakan hubungi cabang terdekat<br>";	
	}
	
	if($cara_bayar!='Collector' &&  $_REQUEST["biaya_tagih"]>0){
		$strmsg.="Biaya Tagih tidak perlu diisi<br>";
	}
	
	if($cara_bayar!='Collector' && $tgl_input!=$tgl_bayar){
		$strmsg.="Tgl input harus sama dgn tgl bayar jika bukan Collector<br>";	
	}
	
	$batas_jam='14:00';
	if($cara_bayar=='Cash'){
		if(date("H:i")>$batas_jam){
			//$strmsg.="Untuk Cash tidak bisa dibayar setelah jam ".$batas_jam."<br>";	
		}
		if(date("m/d/Y")!=$tgl_bayar){
			//$strmsg.="Untuk Cash, Tgl Bayar harus sama dengan hari ini<br>";	
		}
	}

}

function query_additional($pk_id){
	global $no_pelunasan;	
	$no_pelunasan=$pk_id;
}

function save_additional(){
	global $l_success,$no_pelunasan,$strmsg;
	//php 5
	$fk_sbg=$_REQUEST["fk_sbg"];
	$fk_cabang=$_REQUEST["fk_cabang"];
	$fk_bank=$_REQUEST["fk_bank"];
	if(!$no_pelunasan)$no_pelunasan=$_REQUEST["no_kwitansi"];
	$referensi=$no_pelunasan;
	$tgl_bayar=convert_date_english($_REQUEST['tgl_bayar']);
	$tgl_input=convert_date_english($_REQUEST['tgl_input']);
	
	$saldo_titipan=$_REQUEST["nilai_bayar_angsuran"]+$_REQUEST["titipan"];
	
	$nilai_bayar_angsuran=$_REQUEST["nilai_bayar_angsuran"];
	$nilai_bayar_denda=$_REQUEST["nilai_bayar_denda"];
	$nilai_bayar_denda2=$_REQUEST["nilai_bayar_denda2"];	
	$biaya_tagih=$_REQUEST["biaya_tagih"];
	$total_pembayaran=$nilai_bayar_angsuran+$nilai_bayar_denda+$nilai_bayar_denda2+$biaya_tagih;	
	
	$angsuran_ke=$_REQUEST["angsuran_ke"];
	$total_denda=$nilai_bayar_denda+$nilai_bayar_denda2;
	
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
	
	$query_1="select * from data_fa.tblangsuran where fk_sbg='".$fk_sbg."' 
	and tgl_bayar is null and angsuran_ke='".$angsuran_ke."'
	order by angsuran_ke asc";
	//showquery($query_1);
	$lrs_1=pg_query($query_1);
	$nilai_pembayaran=0;
	while($lrow_1=pg_fetch_array($lrs_1)){
		
		//cek kl ud jt lsg update ke ang ybs jadi sudah dibayar
		$tgl_jatuh_tempo=date("Y-m-d",strtotime($lrow_1["tgl_jatuh_tempo"]));
		
		$nilai_angsuran=$lrow_1["nilai_angsuran"];	
		$nilai_bunga=$lrow_1["bunga_jt"];	
		$akrual1=$lrow_1["akrual1"];	
		//$tgl_bayar='2021-02-01';
		if($saldo_titipan>=$nilai_angsuran){
			$saldo_titipan-=$nilai_angsuran;
			$nilai_pembayaran+=$nilai_angsuran;
		
			$lwhere="fk_sbg='".$lrow_1["fk_sbg"]."' and angsuran_ke='".$lrow_1["angsuran_ke"]."'";		
			
			if(!pg_query(insert_log("data_fa.tblangsuran",$lwhere,'UB'))) $l_success=0;
			if(!pg_query("update data_fa.tblangsuran SET tgl_bayar='".$tgl_bayar."',no_kwitansi ='".$no_pelunasan."' where ".$lwhere."")) $l_success=0;	
			//showquery("update data_fa.tblangsuran SET tgl_bayar='".$tgl_bayar."',no_kwitansi ='".$no_pelunasan."' where ".$lwhere."");
			if(!pg_query(insert_log("data_fa.tblangsuran",$lwhere,'UA'))) $l_success=0;		
			
			$ang_ke=$lrow_1["angsuran_ke"];															
			$transaksi='Angsuran';
			if(!pg_query(insert_history_sbg($fk_sbg,$nilai_angsuran,$ang_ke,$transaksi,$referensi,$total_denda))) $l_success=0;			
			//showquery(insert_history_sbg($fk_sbg,$nilai_angsuran,$ang_ke,$transaksi,$referensi,$total_denda));
			
		}
	}
	//echo $akrual;
	//end		

	
	//BEGIN DENDA	
	//if($nilai_bayar_angsuran>0){
	$sisa_denda=($_REQUEST["total_denda_lalu"]+$_REQUEST["denda_ganti_rugi"])-($nilai_bayar_denda);	
	//SALDO DENDA DITAMBAH KALAU BAYAR ANGSURAN SAJA. KALAU BAYAR DENDA AJ GA
	$type="denda";
	$lwhere="fk_sbg='".$fk_sbg."'";
	if(!pg_num_rows(pg_query("select * from data_fa.tbl".$type." where ".$lwhere." for update"))){
		if(!pg_query("insert into data_fa.tbl".$type."(fk_sbg,saldo_".$type.") values('".$fk_sbg."','".$sisa_denda."')")) $l_success=0;
		if(!pg_query(insert_log("data_fa.tbl".$type."",$lwhere,'IA'))) $l_success=0;
		
	}else{
		if(!pg_query(insert_log("data_fa.tbl".$type."",$lwhere,'UB'))) $l_success=0;
		if(!pg_query("update data_fa.tbl".$type." SET saldo_".$type." ='".$sisa_denda."' where ".$lwhere."")) $l_success=0;
		//showquery("update data_fa.tbl".$type." SET saldo_".$type." ='".$sisa_denda."' where ".$lwhere."");
		if(!pg_query(insert_log("data_fa.tbl".$type."",$lwhere,'UA'))) $l_success=0;
	}	

	
	if($_REQUEST["denda_ganti_rugi"]>0){
		$transaksi='Denda Ganti Rugi';
		if(!pg_query(insert_history_sbg($fk_sbg,$_REQUEST["denda_ganti_rugi"],$angsuran_ke,$transaksi,$referensi))) $l_success=0;
	}
//	if($_REQUEST["denda_keterlambatan"]>0){
//		$transaksi='Denda Keterlambatan';
//		if(!pg_query(insert_history_sbg($fk_sbg,$_REQUEST["denda_keterlambatan"],$angsuran_ke,$transaksi,$referensi))) $l_success=0;
//	}
		
	//END DENDA
	//}
	
	if($nilai_bayar_denda>0){
		$transaksi='Bayar Denda Ganti Rugi';
		if(!pg_query(insert_history_sbg($fk_sbg,$nilai_bayar_denda,$angsuran_ke,$transaksi,$referensi))) $l_success=0;
	}	
	
	if($nilai_bayar_denda2>0){
		$transaksi='Bayar Denda Keterlambatan';
		if(!pg_query(insert_history_sbg($fk_sbg,$nilai_bayar_denda2,$angsuran_ke,$transaksi,$referensi))) $l_success=0;
	}	

		
	//POSTING
	$fk_cabang_input=$_REQUEST['fk_cabang_input'];
	$account = get_coa_bank($fk_bank,$fk_cabang_input);
	if($tgl_input!=$tgl_bayar){
		$account=$fk_cabang_input.'.'.get_rec("tbltemplate_coa","coa","used_for='piutang_kolektor'");//karena kolektor bisa setor uangnya besoknya
	}
	
	$coa_antara=$fk_cabang_input.'1180000';
	if($total_pembayaran>0){
				
		$arrPost = array();	
		
		$arrPost["bank"]		      		= array('type'=>'d','value'=>$total_pembayaran,'reference'=>$no_pelunasan,'account'=>$account);
		
		if($fk_cabang!=$fk_cabang_input){
			$rpkc 	  = get_coa_cabang($fk_cabang,$fk_cabang_input);
			$arrPost["rpkc"]				= array('type'=>'c','value'=>$total_pembayaran,'account'=>$rpkc,'reference'=>$no_pelunasan);
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
		
		$arrPost["piutang_pembiayaan"]		= array('type'=>'c','value'=>$nilai_pembayaran,'reference'=>$no_pelunasan);		

		if($nilai_bayar_denda>0){
			$arrPost["pend_denda_".$kategori]= array('type'=>'c','value'=>$nilai_bayar_denda,'reference'=>$no_pelunasan);
		}	
		if($nilai_bayar_denda2>0){
			$arrPost["utang_denda"]			= array('type'=>'c','value'=>$nilai_bayar_denda2,'reference'=>$no_pelunasan);
		}	
		
		if($biaya_tagih>0){
			$arrPost["pend_tagih_".$kategori]= array('type'=>'c','value'=>$biaya_tagih,'reference'=>$no_pelunasan);
		}	
		
		//cek_balance_array_post($arrPost);
		if(!posting('PEMBAYARAN CICILAN',$fk_sbg,$tgl_bayar,$arrPost,$fk_cabang,'00'))$l_success=0;		
		
		
		if($tgl_input!=$tgl_bayar){
			$arrPost = array();
			$account2 = get_coa_bank($fk_bank,$fk_cabang_input);
			$arrPost["bank2"]		      		= array('type'=>'d','value'=>$total_pembayaran,'reference'=>$no_pelunasan,'account'=>$account2);
			$arrPost["bank"]		      		= array('type'=>'c','value'=>$total_pembayaran,'reference'=>$no_pelunasan,'account'=>$account);			
			//cek_balance_array_post($arrPost);
			if(!posting('PEMBAYARAN CICILAN',$fk_sbg,$tgl_input,$arrPost,$fk_cabang_input,'00'))$l_success=0;		
		}
	}	
	//echo $l_success;
	if(!pg_query(update_saldo_bank($fk_bank,$fk_cabang_input,$total_pembayaran,0,"Pembayaran",$fk_sbg,$no_pelunasan)))$l_success=0;
			
	//echo $l_success;
	//$l_success=0;
}

?>

