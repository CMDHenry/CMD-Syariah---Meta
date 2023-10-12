<?

function html_additional(){
	
	echo '<input type="hidden" name="tipe" value="BPKB">';
	echo '<input type="hidden" name="tgl_sistem" value="'.today.'">';
}
function cek_error_module(){
	global $j_action,$strmsg;
	
	$fk_sbg=$_REQUEST["fk_sbg"];
	$tgl_bayar=convert_date_english($_REQUEST['tgl_bayar']);
	
	$query="
	select date_part('day',('".$tgl_bayar."'-tgl_lunas))::numeric as ovd_bpkb,tgl_lunas,fk_cabang from(
		select * from tblinventory 
		where fk_sbg ='".$fk_sbg."'
	)as tblsbg	
	";
	//showquery($query);
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	
	$diskon=$_REQUEST["diskon"];
	
	if(pg_num_rows(pg_query("select * from data_fa.tblpembayaran_bpkb where fk_sbg='".$fk_sbg."' and tgl_batal is null"))){
		$strmsg.="BPKB sudah dibayar<br>";
	}
	
	if($diskon>0 ){
		if(!pg_num_rows(pg_query("select * from data_fa.tbldiskon_bpkb where fk_sbg='".$fk_sbg."' and is_approval='t'"))){
			if(!pg_num_rows(pg_query("select * from data_fa.tbldiskon_bpkb where fk_sbg='".$fk_sbg."' "))){
				//pg_query("BEGIN");
				pg_query("insert into data_fa.tbldiskon_bpkb(fk_sbg,diskon) values ('".$fk_sbg."','".$diskon."')");
				$lwhere="fk_sbg='".$fk_sbg."'";
				pg_query(insert_log("data_fa.tbldiskon_bpkb",$lwhere,'IA'));
				//pg_query("COMMIT");
				
				$strmsg.="Diskon harus diapprove oleh HO terlebih dahulu<br>";
			}else{
				
				$diskon_approval=get_rec("data_fa.tbldiskon_bpkb","diskon","fk_sbg='".$fk_sbg."'");
				$is_approval=get_rec("data_fa.tbldiskon_bpkb","is_approval","fk_sbg='".$fk_sbg."'");
				if($is_approval=='f'){
					$strmsg.="Diskon harus diapprove oleh HO terlebih dahulu<br>";
				}
				if($diskon_approval && $diskon_approval!=$diskon){
					$strmsg.="Diskon harus diapprove ulang jika akan diganti<br>";
					
					$lwhere="fk_sbg='".$fk_sbg."'";
					//pg_query("BEGIN");
					pg_query(insert_log("data_fa.tbldiskon_bpkb",$lwhere,'UB'));
					pg_query("update data_fa.tbldiskon_bpkb SET diskon='".$diskon."', is_approval = 'f' where ".$lwhere."") ;
					pg_query(insert_log("data_fa.tbldiskon_bpkb",$lwhere,'UA'));
					//pg_query("COMMIT");
	
				}
			}
				
		}

	}
		
	$tgl_lunas=$lrow["tgl_lunas"];
	$fk_cabang=$lrow["fk_cabang"];
	$ovd_bpkb=$lrow["ovd_bpkb"];

	if(!$tgl_lunas){
		$strmsg.="Kontrak ini belum lunas <br>";	
	}elseif($ovd_bpkb<=0){
		$strmsg.="Kontrak ini belum perlu bayar BPKB <br>";	
	}

	$nilai_tagihan=str_replace(',','',$_REQUEST['nilai_tagihan']);
	if($diskon>$nilai_tagihan){
		$strmsg.="Diskon lebih besar dari tagihan <br>";	
	}
	
}

function query_additional($pk_id){
	global $no_kwitansi;	
	$no_kwitansi=$pk_id;
}

function save_additional(){
	global $l_success,$no_kwitansi;
	
	$fk_sbg=$_REQUEST["fk_sbg"];
	$nilai_bayar=str_replace(',','',$_REQUEST['nilai_bayar']);
	$fk_bank=$_REQUEST["fk_bank"];
	$fk_cabang=$_REQUEST["fk_cabang"];
	$tgl_bayar=convert_date_english($_REQUEST['tgl_bayar']);
	
	$lrow=pg_fetch_array(pg_query("
	select * from tblinventory 
	left join (select ang_ke ,fk_sbg as fk_sbg1 from viewang_ke)as tbl on fk_sbg1=fk_sbg 
	left join (
		select fk_fatg as no_fatg,no_sbg from data_gadai.tblproduk_cicilan
	)as tbl1 on no_sbg=fk_sbg
	left join (
		select no_fatg as no_fatg1,no_mesin,no_rangka, kategori,nm_tipe,fk_jenis_barang,kategori from viewkendaraan
	)as view on no_fatg=no_fatg1
	where fk_sbg='".$fk_sbg."'"));
	$kategori=$lrow["kategori"];	
	$kategori=strtolower($kategori);
	
	$transaksi='DENDA BPKB';
	
	$coa_bank = get_coa_bank($fk_bank,$fk_cabang);
	$arrPost = array();
	$arrPost["bank"]		= array('type'=>'d','value'=>$nilai_bayar,'account'=>$coa_bank,'reference'=>$keterangan);
	$arrPost["pend_bpkb_".$kategori]	= array('type'=>'c','value'=>$nilai_bayar,'reference'=>$keterangan);
	//cek_balance_array_post($arrPost);
	if(!posting($transaksi,$no_kwitansi,$tgl_bayar,$arrPost,$fk_cabang,'00'))$l_success=0;
	
	//showquery(update_saldo_bank($fk_bank,$fk_cabang,$nilai_bayar,0,$transaksi,$fk_sbg,$no_kwitansi)));
	if(!pg_query(update_saldo_bank($fk_bank,$fk_cabang,$nilai_bayar,0,$transaksi,$fk_sbg,$no_kwitansi)))$l_success=0;
	
	//showquery(insert_history_sbg($fk_sbg,$nilai_bayar,0,$transaksi,$no_kwitansi));
	if(!pg_query(insert_history_sbg($fk_sbg,$nilai_bayar,0,$transaksi,$no_kwitansi))) $l_success=0;
	

}

?>

