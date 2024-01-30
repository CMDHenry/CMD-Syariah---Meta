<?php
set_time_limit(0);
$counter=0;
function cek_balance_array_post($p_arr_transaksi){
	// cuman bwt test balance array aja
	foreach($p_arr_transaksi as $l_transaksi => $l_arr){
		if($l_arr['type']=='c')$c += $l_arr['value'];
		if($l_arr['type']=='d')$d += $l_arr['value'];
	}
	print_r($p_arr_transaksi); //jgn di delete
	echo "<br>Credit : ".$c."<br>Debit : ".$d."<br>";
}

function cek_balance_gl_auto($p_type_owner,$p_owner){
	//cek gl auto nya balance gak
	$lquery = "
		select d,c from (
			select 
				trunc(sum (case when fk_coa_d is not null then total else 0 end)) as D,
				trunc(sum (case when fk_coa_c is not null then total else 0 end)) as C
			from data_accounting.tblgl_auto where type_owner = '".convert_sql($p_type_owner)."' 
			and fk_owner ='".convert_sql($p_owner)."' 
			--and description not like 'Persediaan%'
		)as tblgl_auto
		where d <> c
	";//exclude persediaan
	//showquery($lquery);

	//print_r(pg_fetch_array(pg_query($lquery)));
	if(pg_num_rows(pg_query($lquery)) > 0)return 0;
	else return 1;
}

function is_posting_approved($p_type_owner='',$p_owner){
	//ngecek posting GL Auto udah di approve atau blum
	if($p_type_owner==''){
		$p_type_owner = $_SERVER['SCRIPT_NAME'];
		$ltemp = split('/',$p_type_owner);
		$llength = count($ltemp);
		$p_type_owner = $ltemp[$llength-1];
	}
	
	if( pg_num_rows(pg_query("select * from data_accounting.tblgl_auto where fk_owner = '".convert_sql($p_owner)."' and type_owner = '".convert_sql($p_type_owner)."' and is_approved is true")) > 0)return true;
	else return false;

}

function posting($p_type_owner='',$p_owner,$p_tanggal='',&$p_arr_transaksi,$p_cabang,$p_divisi,$p_do_post='posting'){
	/*******************************************************************************
	$p_type_owner 	=> klo dikosongin ngambil nama file, isi aja source data cth : DO Unit, Transaksi Debit Showroom, dll
					=> yg penting jelas
	$p_owner 		=> no transaksi
	$p_arr_transaksi	=> array transaksi
		cth : $p_arr_transaksi['pembelian_kendaraan'] = array('type'=>'d','value'=>$harga_tebus,'reference'=>$no_mesin);
		pembelian_kendaraan => used_for di table coa berdasarkan jenis transaksi
		array isinya :
		account		=> isi nya COA, klo kosong, nanti otomatis nyari sendiri pk get_coa_posting
		reference	=> ini dipake klo bnyk detail 
					=> cth : mau Transaksi Kredit Showroom (header), kembali_tanda_jadi(detail)
					=> reference nya isi 000001/SO/2012, reference_type => yg field get
		reference_type,
		fk_customer => code customer
		fk_supplier => code supplier
		type isinya cuman c/d,
		value ya nilai nya . . . jgn smpe kosong...
		cabang_coa => isi nya cabang pemilik COA default sih ambil dari session
		divisi_coa => isi nya cabang pemilik COA default sih ambil dari parameter yg dilempar $p_divisi
		is_rk => di bikin true klo mau RK, 
			dan harus tau rk siapa dg siapa, 
			cth RK HO dg Dealer Sales, maka rk_from = head_office, rk_to = kode dealer, rk_to_divisi = S
		rk['rk_from']
		rk['rk_to']
		rk['rk_to_divisi']
		do_ap
	$p_do_post 		=> posting atau unposting, posting buat ngisi, unposting buat batal nya
	********************************************************************************/
	
	//init
	if(!__POSTING__)return true;
	$l_success=1;
	
	//print_r ($p_arr_transaksi);
	if($p_type_owner==''){
		$p_type_owner = $_SERVER['SCRIPT_NAME'];
		$ltemp = split('/',$p_type_owner);
		$llength = count($ltemp);
		$p_type_owner = $ltemp[$llength-1];
	}
	//if($p_tanggal=='')$p_tanggal = date('m/d/Y H:i:s');
	$today_db=get_rec("tblsetting",'tgl_sistem');
	if($p_tanggal=='')$p_tanggal = $today_db;
	if($p_cabang=='')$p_cabang = $_SESSION['kd_cabang'];
	
	$p_do_post = strtolower($p_do_post);
	if(!($p_do_post!="posting" || $p_do_post!="unposting")){
		return false;
	}
	//echo $l_scucess;
	//end init

	// kalau unposting, dy ngedelete record based on no transaksi alias fk_owner
	if($p_do_post=='unposting'){
//		if(is_posting_approved($p_type_owner,$p_owner))return false;

//		if(pg_num_rows(pg_query("select * from data_accounting.tblgl_auto where fk_owner = '".convert_sql($p_owner)."' and type_owner = '".convert_sql($p_type_owner)."'"))<=0)return false; // cek owner dan type owner nya bener ada gak
//
//		if(!pg_query("
//			insert into data_accounting.tblgl_auto_log select *,'".$_SESSION['id']."','".$_SESSION['username']."','".date('m/d/Y H:i:s')."','DB','".$_SERVER['SCRIPT_NAME']."' from data_accounting.tblgl_auto where fk_owner = '".convert_sql($p_owner)."' and type_owner = '".convert_sql($p_type_owner)."'
//		"))$l_success=0;
//
//		if(!pg_query("delete from data_accounting.tblgl_auto where fk_owner = '".convert_sql($p_owner)."' and type_owner = '".convert_sql($p_type_owner)."'"))$l_success=0;

		//klo ada yg mw delete posting lanjutin stristr nya
		if( strtolower($p_type_owner) == 'terima unit' || strtolower($p_type_owner) == 'transport unit' || stristr($p_type_owner,'transaksi pengeluaran')!='' || stristr($p_type_owner,'transaksi penerimaan')!='' ){

			$lquery = "
				insert into data_accounting.tblgl_auto_log select *,'".$_SESSION['id']."','".$_SESSION['username']."','".date('m/d/Y H:i:s')."','DB','".$_SERVER['SCRIPT_NAME']."' from data_accounting.tblgl_auto where fk_owner = '".convert_sql($p_owner)."' and type_owner = '".convert_sql($p_type_owner)."';
				delete from data_accounting.tblgl_auto where fk_owner = '".convert_sql($p_owner)."' and type_owner = '".convert_sql($p_type_owner)."';
			";
			if(!pg_query($lquery))$l_success=0;
			//showquery($lquery);
			
			$l_delete_posting = 1;

		}else{
			//klo unposting gak boleh delete
			//$p_tanggal = date('m/d/Y H:i:s');
			$today_db=get_rec("tblsetting",'tgl_sistem');
			$p_tanggal = $today_db;
			$p_type_owner = 'BATAL '.$p_type_owner;
			$l_delete_posting = 0;
		}

	}
	
	//print_r ($p_arr_transaksi);
	foreach($p_arr_transaksi as $l_transaksi => $l_arr){
		// klo divisi coa nya di set maka parameternya pk divisi
		if($l_arr['divisi_coa']!='')$l_divisi=$l_arr['divisi_coa'];
		else $l_divisi = $p_divisi;
		// klo cabang_coa nya di set maka parameternya pk cabang_coa
		if($l_arr['cabang_coa']!='')$l_cabang=$l_arr['cabang_coa'];
		else $l_cabang = $p_cabang;
		
		if($l_arr['tr_date']!='')$p_tanggal=$l_arr['tr_date'];
		
		if(isset($l_arr['account'])){ // klo ada isi maka ngecek account nya bener gak
			$lcoa = cek_coa_posting($l_arr['account'],$l_cabang,$l_divisi);
		}else{ // klo gak ada isi maka dy nyari otomatis sesuai sama jenis transaksi cbg divisi
	
			// klo RK maka harus tau rk siapa dg siapa cth HO -> Dealer, maka rk_from = head_office, rk_to = dealer
			// khusus untuk STM tambah rk_to_divisi >> krn pd coanya spesifik hutang piutang dg cabang/divisi mana
			if($l_arr['is_rk'])$lcoa = get_coa_posting($l_transaksi,$l_arr['rk']['rk_from'],$l_divisi,$l_arr['rk']['rk_to'],$l_arr['rk']['rk_to_divisi']);
			// jalur normal klo cabang coa nya kosong nanti dy ambil dr session
			else $lcoa = get_coa_posting($l_transaksi,$l_cabang,$l_divisi);
		}
		if($l_arr['value']=='')$l_arr['value']=0;
		elseif(!is_numeric($l_arr['value']))$l_arr['value']=0;
		else $l_arr['value']=round($l_arr['value'],2);
		
		$l_divisi=get_rec("tblcoa","fk_jenis_cabang","coa='".$lcoa['coa']."'");//khusus per divisi
		if(is_array($lcoa)){
			//print_r($lcoa);
			// klo unposting maka nominal nya di minus
			if($p_do_post=='unposting'){
				if( $l_delete_posting ){
					$l_arr['value'] = $l_arr['value']*(-1);
				}else{
					if($l_arr['type'] == 'd')$l_arr['type']='c';
					elseif($l_arr['type'] == 'c')$l_arr['type']='d';
				}

				//delete saldo ap
				if(!isset($l_arr['do_ap']) || $l_arr['do_ap'] == true)if(pg_num_rows(pg_query("select * from tbljenis_transaksi where kd_jenis_transaksi = '".$l_transaksi."' and jenis_account = 'ap'")) && stristr($p_type_owner,'pengeluaran kas')=='') $l_success = delete_saldo_ap($p_owner,$l_transaksi,$p_tanggal,$l_arr['value'],$p_cabang);

			}elseif($p_do_post=='posting'){
				//create saldo ap
				if(!isset($l_arr['do_ap']) || $l_arr['do_ap'] == true)if(pg_num_rows(pg_query("select * from tbljenis_transaksi where kd_jenis_transaksi = '".$l_transaksi."' and jenis_account = 'ap'")) && stristr($p_type_owner,'pengeluaran kas')=='') $l_success = create_saldo_ap($p_owner,$l_transaksi,$p_tanggal,$l_arr['value'],$p_cabang,$l_arr['fk_supplier'],$p_divisi,$lcoa['coa'],$l_arr['keterangan']);
				if(!isset($l_arr['do_ar']) || $l_arr['do_ar'] == true)if(pg_num_rows(pg_query("select * from tbljenis_transaksi where kd_jenis_transaksi = '".$l_transaksi."' and jenis_account = 'ar'")) && stristr($p_type_owner,'pengeluaran kas')=='') $l_success = create_saldo_ar($p_owner,$l_transaksi,$p_tanggal,$l_arr['value'],$p_cabang,$l_arr['fk_supplier'],$p_divisi,$lcoa['coa'],$l_arr['keterangan']);

			}
			if(!$l_delete_posting){

				//klo minus posisi nya dibalik trus nilai di bikin positif
				if($l_arr['value']<0){ 
					if($l_arr['type'] == 'd')$l_arr['type']='c';
					elseif($l_arr['type'] == 'c')$l_arr['type']='d';
					$l_arr['value']*=-1;
				}

				$query_log="select nextserial('tblgl_auto.no_bukti'::text)";
				$lrow_log=pg_fetch_array(pg_query($query_log));
				$lid_log=$lrow_log["nextserial"];

				//insert gl auto
				$lquery = "
					insert into data_accounting.tblgl_auto (
						tr_date,type_owner,fk_owner,
						reference_type,reference_transaksi,
						description,fk_coa_".$l_arr['type'].",total,
						fk_customer,fk_supplier,fk_cabang,fk_jenis_cabang,no_bukti
					)values(
						'".$p_tanggal."','".convert_sql($p_type_owner)."','".convert_sql($p_owner)."',
						".(($l_arr['reference_type'])?"'".convert_sql($l_arr['reference_type'])."'":"NULL").",
						".(($l_arr['reference'])?"'".convert_sql($l_arr['reference'])."'":"NULL").",
						'".convert_sql((($l_arr['keterangan'])?$l_arr['keterangan']:$lcoa['description']))."',
						'".convert_sql($lcoa['coa'])."',
						".(($l_arr['value'])?"round(".convert_sql($l_arr['value']).",2)":"0").",
						".(($l_arr['fk_customer'])?"'".convert_sql($l_arr['fk_customer'])."'":"NULL").",
						".(($l_arr['fk_supplier'])?"'".convert_sql($l_arr['fk_supplier'])."'":"NULL").",
						'".$l_cabang."','".$l_divisi."','".$lid_log."'
					)
				";//showquery($lquery);
				if(!pg_query($lquery))$l_success=0;
				//echo $l_success."<br>";
				//$lid_log = get_last_id('data_accounting.tblgl_auto','no_bukti');
				if(!pg_query("
					insert into data_accounting.tblgl_auto_log select *,'".$_SESSION['id']."','".$_SESSION['username']."','".date('m/d/Y H:i:s')."','IA','".$_SERVER['SCRIPT_NAME']."' from data_accounting.tblgl_auto where no_bukti = '".$lid_log."'
				"))$l_success=0;
				
			}

			//update saldo coa
			$l_success = update_saldo_coa('gl_auto',$lcoa['coa'],$l_arr['type'],$l_arr['value'],$p_tanggal);
		}else return 0;

	}
	
	if(!cek_periode_accounting(date("Y",strtotime($p_tanggal)),date("n",strtotime($p_tanggal)))){
		trigger_error('<h3>Tidak boleh backdate karena sudah closing accounting</h3>',E_USER_WARNING);
		return 0;
	}
	
	//cek balance gl auto
	//if($p_do_post!='unposting')
	$l_success = cek_balance_gl_auto($p_type_owner,$p_owner);
	//echo $l_success;
	if($l_success)$p_arr_transaksi = array();
	return $l_success;
}

function update_gl_auto($p_type_owner,$p_owner,$p_tanggal,$p_arr,$p_cabang,$p_divisi){
	$l_success=1;
	//update manual gl auto
	if(!pg_query("
		insert into data_accounting.tblgl_auto_log select *,'".$_SESSION['id']."','".$_SESSION['username']."','".date('m/d/Y H:i:s')."','UB','".$_SERVER['SCRIPT_NAME']."' from data_accounting.tblgl_auto where fk_owner = '".convert_sql($p_owner)."' and type_owner = '".$p_type_owner."'
	"))$l_success=0;

	foreach($p_arr as $l_transaksi => $l_arr){
		$lcoa = get_coa_posting($l_transaksi,$p_cabang,$p_divisi);
		if(is_array($lcoa)){
			//update saldo coa old
			$l_success = update_saldo_coa('gl_auto',$lcoa['coa'],$l_arr['type'],($l_arr['value_old']*-1),$p_tanggal);
			//delete saldo ap
			if(pg_num_rows(pg_query("select * from tbljenis_transaksi where kd_jenis_transaksi = '".$l_transaksi."' and jenis_account = 'ap'")) && stristr($p_type_owner,'transaksi kredit')=='') $l_success = delete_saldo_ap($p_owner,$l_transaksi,$p_tanggal,$l_arr['value'],$p_cabang);

			
			//update account
			$lquery="
				update data_accounting.tblgl_auto set
					total = '".$l_arr['value']."'
				where fk_owner = '".convert_sql($p_owner)."' and type_owner = '".$p_type_owner."'
				and fk_coa_".$l_arr['type']." = '".$lcoa['coa']."'
			";
			//showquery($lquery);
			if(!pg_query($lquery))$l_success=0;
			
			//update saldo coa new
			$l_success = update_saldo_coa('gl_auto',$lcoa['coa'],$l_arr['type'],$l_arr['value'],$p_tanggal);
			//create saldo ap
			if(pg_num_rows(pg_query("select * from tbljenis_transaksi where kd_jenis_transaksi = '".$l_transaksi."' and jenis_account = 'ap'")) && stristr($p_type_owner,'transaksi kredit')=='') $l_success = create_saldo_ap($p_owner,$l_transaksi,$p_tanggal,$l_arr['value'],$p_cabang,$l_arr['fk_supplier'],$p_divisi);

		}else return 0;
	}	

	if(!pg_query("
		insert into data_accounting.tblgl_auto_log select *,'".$_SESSION['id']."','".$_SESSION['username']."','".date('m/d/Y H:i:s')."','UA','".$_SERVER['SCRIPT_NAME']."' from data_accounting.tblgl_auto where fk_owner = '".convert_sql($p_owner)."' and type_owner = '".$p_type_owner."'
	"))$l_success=0;
	
	return $l_success;
}

function get_coa_posting($p_used_for,$p_cabang='',$p_divisi='',$p_rk='',$p_rk_divisi='',$p_show_error=0){
	global $strmsg;
	//return nya array, divisi gak boleh kosong => S showroom, W workshop, P sparepart, D dealer
	if($p_cabang=='')$p_cabang=$_SESSION['kd_cabang'];
	$p_divisi=NULL;
	//pertama nyari ke default
	if($lrow = pg_fetch_array(pg_query("
		select coa,description from (
			select * from tblcoa where used_for = '".convert_sql($p_used_for)."' 
			and fk_cabang = '".convert_sql($p_cabang)."' 
			".(($p_divisi)?" and fk_jenis_cabang = '".convert_sql($p_divisi)."'":"")." 
			".(($p_rk)?" and rk_dg_cabang = '".convert_sql($p_rk)."'":"")." 
			".(($p_rk_divisi)?" and rk_dg_divisi = '".convert_sql($p_rk_divisi)."'":"")."
		)as tblcoa left join tbljenis_transaksi on used_for = kd_jenis_transaksi
	"))){
		return $lrow;
	//nyari ke accounting
	}/*elseif($lrow = pg_fetch_array(pg_query("
		select coa,description from (
			select * from tblcoa where used_for = '".convert_sql($p_used_for)."' 
			and fk_cabang = '".convert_sql($p_cabang)."' and fk_jenis_cabang = 'A' 
			".(($p_rk)?" and rk_dg_cabang = '".convert_sql($p_rk)."'":"")."
			".(($p_rk_divisi)?" and rk_dg_divisi = '".convert_sql($p_rk_divisi)."'":"")."
		)as tblcoa left join tbljenis_transaksi on used_for = kd_jenis_transaksi
	"))){
		return $lrow;
	}*/else{ //else error
		//message
		showquery("
			select coa,description from (
				select * from tblcoa where used_for = '".convert_sql($p_used_for)."' 
				and fk_cabang = '".convert_sql($p_cabang)."' 
				".(($p_divisi)?" and fk_jenis_cabang = '".convert_sql($p_divisi)."'":"")." 
				".(($p_rk)?" and rk_dg_cabang = '".convert_sql($p_rk)."'":"")." 
				".(($p_rk_divisi)?" and rk_dg_divisi = '".convert_sql($p_rk_divisi)."'":"")."
			)as tblcoa left join tbljenis_transaksi on used_for = kd_jenis_transaksi
		");
		
		$l_msg = 'COA belum di define untuk transaksi '.str_replace('_',' ',strtoupper($p_used_for)).' '.(($p_rk)?$p_rk:"").' pada cabang '.$p_cabang.' divisi '.$p_divisi.'<br>';
		if($p_show_error)trigger_error('<h3>'.$l_msg.'</h3>',E_USER_WARNING);
		$strmsg.=$l_msg;
		return 0;
	}
	
}

function cek_coa_posting($p_coa,$p_cabang='',$p_divisi,$p_show_error=0){
	//return nya array, divisi gak boleh kosong => S showroom, W workshop, P sparepart, D dealer
	if($p_cabang=='')$p_cabang=$_SESSION['kd_cabang'];

	if($lrow = pg_fetch_array(pg_query("select coa,jenis_transaksi,description from (select * from tblcoa where coa = '".convert_sql($p_coa)."' and fk_cabang = '".convert_sql($p_cabang)."')as tblcoa left join tbljenis_transaksi on used_for = kd_jenis_transaksi"))){
		// and fk_jenis_cabang = '".convert_sql($p_divisi)."'
		return $lrow;
	}else{
		if(!$p_show_error)trigger_error('<h3>COA '.$p_coa.' not found pada cabang '.$p_cabang.' divisi '.$p_divisi.'</h3>',E_USER_WARNING);
		return false;
	}
	
}

function create_saldo_coa($p_coa,$p_tanggal='',$p_is_new=false,$p_balance='',$p_coa_type=''){
	$m = date('n',strtotime($p_tanggal));
	$y = date('Y',strtotime($p_tanggal));
	$today_db=get_rec("tblsetting",'tgl_sistem');
	if($p_is_new){

		$lgl_auto = $lmemorial = $lcash = $lbank = 0;
		if(strtolower($p_coa_type)=='cash')$lcash = $p_balance;
		elseif(strtolower($p_coa_type)=='bank')$lbank = $p_balance;
		elseif(strtolower($p_coa_type)=='gl_auto')$lgl_auto = $p_balance;
		elseif(strtolower($p_coa_type)=='memorial')$lmemorial = $p_balance;
		//start date nya dari param tgl
		$l_start_date = $m.'/01/'.$y;
		
		//end nya hrs nyari last bln/thn dr record ato klo gak ada bln/thn ini
		if($lrow_end_date = pg_fetch_array(pg_query("select * from data_accounting.tblsaldo_coa where fk_coa = '".$p_coa."' order by ( tr_year::text||case when length(tr_month::text) < 2 then '0'||tr_month::text else tr_month::text end ) asc")))
			$l_end_date = $lrow_end_date['tr_month'].'/01/'.$lrow_end_date['tr_year'];
		else
			$l_end_date = date("m/d/Y 23:59:59", strtotime('-1 second',strtotime('+1 month',strtotime(date('m',strtotime($today_db)).'/01/'.date('Y',strtotime($today_db))))));
				

		while(strtotime($l_start_date) < strtotime($l_end_date)){
			if(!pg_query("
				insert into data_accounting.tblsaldo_coa (
					fk_coa,tr_month,tr_year,balance_cash,balance_bank,
					balance_gl_auto,balance_memorial
				) values (
					'".convert_sql($p_coa)."','".$m."','".$y."',
					'".$lcash."','".$lbank."','".$lgl_auto."','".$lmemorial."'
				);
				insert into data_accounting.tblsaldo_coa_log (
					fk_coa,tr_month,tr_year,balance_cash,balance_bank,
					balance_gl_auto,balance_memorial,
					log_action_userid,log_action_username,log_action_date,
					log_action_mode,log_action_from
				) values (
					'".convert_sql($p_coa)."','".$m."','".$y."',
					'".$lcash."','".$lbank."','".$lgl_auto."','".$lmemorial."',
					'".$_SESSION['id']."','".$_SESSION['username']."',
					'".date('Y-m-d H:i:s')."','IA','".$_SERVER['SCRIPT_NAME']."'
				);
			"))return false;
			
			$m += 1;
			if($m > 12){$y+=1;$m=1;}
			$l_start_date = $m."/01/".$y;
		}

	}else{ //ini mindahin saldo, klo rollover dibawa nilainya
	
		if ($lrow_coa=pg_fetch_array(pg_query("select type_saldo from (select * from tblcoa where coa = '".convert_sql($p_coa)."')as tblcoa left join tblhead_account on fk_head_account = code"))){
	
			$lquery = "select * from data_accounting.tblsaldo_coa where fk_coa = '".$p_coa."' ";
			//and tr_month = '".$m."' and tr_year = '".$y."'
			$lrs = pg_query($lquery." order by tr_year,tr_month");
			//showquery($lquery." order by tr_year,tr_month");
			if(pg_num_rows($lrs) <= 0){ // cek pernah ada kartu stok gak
				if(!pg_query("
					insert into data_accounting.tblsaldo_coa values ('".convert_sql($p_coa)."','".$m."','".$y."');
					insert into data_accounting.tblsaldo_coa_log select *,'".$_SESSION['id']."','".$_SESSION['username']."','#".date("Y/m/d H:i:s")."#','IA','".$_SERVER['PHP_SELF']."' from data_accounting.tblsaldo_coa where fk_coa='".$p_coa."' and tr_month = '".$m."' and tr_year = '".$y."';
				"))return false;
			}else{

				$lrs = pg_query($lquery." and tr_month = '".$m."' and tr_year = '".$y."' ");
				//showquery($lquery." and tr_month = '".$m."' and tr_year = '".$y."' "); 
				if(pg_num_rows($lrs) <= 0){
					$getM = $m-1;
					if($getM <= 0){$getY = $y-1;$getM = 12;}else{$getY=$y;}
					$lrs = pg_query($lquery." and tr_month = '".$getM."' and tr_year = '".$getY."' ");
					
					if(pg_num_rows($lrs) <= 0 ){
						//showquery($lquery." and tr_month = '".$getM."' and tr_year = '".$getY."' "); 
						if(!create_saldo_coa($p_coa,$getM.'/01/'.$getY))return false;
					}
					$lrow = pg_fetch_array(pg_query($lquery." and tr_month = '".$getM."' and tr_year = '".$getY."' "));
					$lquery2 = "insert into data_accounting.tblsaldo_coa values ('".convert_sql($p_coa)."','".$m."','".$y."'";
					//showquery($lquery2);
					if(strtolower($lrow_coa['type_saldo'])=='rollover'){
						$lquery2.=", 
							".(($lrow['saldo_d']=='')?"0":"'".$lrow['saldo_d']."'").", 
							".(($lrow['saldo_c']=='')?"0":"'".$lrow['saldo_c']."'").", 
							".(($lrow['balance_cash']=='')?"0":"'".$lrow['balance_cash']."'").", 
							".(($lrow['balance_bank']=='')?"0":"'".$lrow['balance_bank']."'").", 
							".(($lrow['balance_memorial']=='')?"0":"'".$lrow['balance_memorial']."'").", 
							".(($lrow['balance_gl_auto']=='')?"0":"'".$lrow['balance_gl_auto']."'")."
						";
					}
					$lquery2 .= ");
					insert into data_accounting.tblsaldo_coa_log select *,'".$_SESSION['id']."','".$_SESSION['username']."','#".date("Y/m/d H:i:s")."#','IA','".$_SERVER['PHP_SELF']."' from data_accounting.tblsaldo_coa where fk_coa='".$p_coa."' and tr_month = '".$m."' and tr_year = '".$y."';
					";
					//showquery($lquery2);
					if(!pg_query($lquery2))return false;
		
				}

			}
	
		}else return false;

	}
	
	return true;
}

function update_saldo_coa($p_field,$p_coa,$p_type,$pTotal=0,$p_tanggal='',$p_sumvalue=1,$p_need_recal=1){
	//$p_field => text bisa bank, cash, memorial, gl_auto
	//$p_type => C atau D
	$m = date('n',strtotime($p_tanggal));
	$y = date('Y',strtotime($p_tanggal));
	$pTotal*=1;
	
	$l_success=1;
	
	if ($lrow_coa=pg_fetch_array(pg_query("select transaction_type,type_saldo,fk_merek from (select * from tblcoa inner join tblhead_account on fk_head_account = code where coa = '".convert_sql($p_coa)."')as tblcoa"))){
		if (strtolower($p_type)==strtolower($lrow_coa["transaction_type"])) $l_operator="+";
		else $l_operator="-";

		$lrecord = pg_num_rows(pg_query("select * from data_accounting.tblsaldo_coa where fk_coa = '".$p_coa."' and ( tr_year::text||case when length(tr_month::text) < 2 then '0'||tr_month::text else tr_month::text end ) <= '".date('Ym',strtotime($p_tanggal))."' order by tr_year,tr_month"));
		$l_success = create_saldo_coa($p_coa,$p_tanggal,(($lrecord==0)?true:false));

		if(strtolower($lrow_coa['type_saldo'])=='rollover'){ // klo rollover dy update dr tgl transksi mpe tgl system
			//nti dy update dr tgl transaksi smpai tgl system
			$lrs_saldo_coa = pg_query("select * from data_accounting.tblsaldo_coa where fk_coa = '".$p_coa."' and tr_month >= '".$m."' and tr_year >= '".$y."' order by tr_year,tr_month");
			while($lrow_saldo_coa = pg_fetch_array($lrs_saldo_coa)){//looping dr tgl transaksi smpe abis
				$this_m = $lrow_saldo_coa['tr_month'];
				$this_y = $lrow_saldo_coa['tr_year'];
	
				$lquery = "
				--insert into data_accounting.tblsaldo_coa_log select *,'".$_SESSION['id']."','".$_SESSION['username']."','".date('Y-m-d H:i:s')."','UB','".$_SERVER['SCRIPT_NAME']."' from data_accounting.tblsaldo_coa where fk_coa = '".$p_coa."' and tr_month = '".$this_m."' and tr_year = '".$this_y."';
		
				update data_accounting.tblsaldo_coa set
					saldo_".strtolower($p_type)." = ".(($p_sumvalue)?"saldo_".strtolower($p_type)." + ":"")." (".$pTotal."),
					balance_".strtolower($p_field)." =  ".(($p_sumvalue)?"balance_".strtolower($p_field)." ".$l_operator."":"")." (".$pTotal.")
				where fk_coa = '".$p_coa."' and tr_month = '".$this_m."' and tr_year = '".$this_y."';
		
				--insert into data_accounting.tblsaldo_coa_log select *,'".$_SESSION['id']."','".$_SESSION['username']."','".date('Y-m-d H:i:s')."','UA','".$_SERVER['SCRIPT_NAME']."' from data_accounting.tblsaldo_coa where fk_coa = '".$p_coa."' and tr_month = '".$this_m."' and tr_year = '".$this_y."';
				";//showquery($lquery);
				if(!pg_query($lquery))$l_success=0;
			}
		}else { // klo non rollover ya dy update saldo di tgl transaksi aja
			$lquery = "
			--insert into data_accounting.tblsaldo_coa_log select *,'".$_SESSION['id']."','".$_SESSION['username']."','".date('Y-m-d H:i:s')."','UB','".$_SERVER['SCRIPT_NAME']."' from data_accounting.tblsaldo_coa where fk_coa = '".$p_coa."' and tr_month = '".$m."' and tr_year = '".$y."';
	
			update data_accounting.tblsaldo_coa set
				saldo_".strtolower($p_type)." = ".(($p_sumvalue)?"saldo_".strtolower($p_type)." + ":"")." (".$pTotal."),
				balance_".strtolower($p_field)." =  ".(($p_sumvalue)?"balance_".strtolower($p_field)." ".$l_operator."":"")." (".$pTotal.")
			where fk_coa = '".$p_coa."' and tr_month = '".$m."' and tr_year = '".$y."';
	
			--insert into data_accounting.tblsaldo_coa_log select *,'".$_SESSION['id']."','".$_SESSION['username']."','".date('Y-m-d H:i:s')."','UA','".$_SERVER['SCRIPT_NAME']."' from data_accounting.tblsaldo_coa where fk_coa = '".$p_coa."' and tr_month = '".$m."' and tr_year = '".$y."';
			";
			//showquery($lquery);
			if(!pg_query($lquery))$l_success=0;
		}

	} else $l_success=0;

	//if($p_need_recal && date('Ym',strtotime($p_tanggal)) < date('Ym'))$l_success = calculate_lb($p_tanggal,$lrow_coa['fk_merek']);
	return $l_success;
}

function get_saldo_coa($p_coa,$tr_month,$tr_year,$fk_cabang=NULL){
	$select="balance_cash+balance_bank+balance_memorial+balance_gl_auto";
	$lreturn = pg_fetch_array(pg_query("
		SELECT SUM(balance_cash+balance_bank+balance_memorial+balance_gl_auto) AS total_balance FROM (
			SELECT * FROM data_accounting.tblsaldo_coa where tr_month = '".$tr_month."' and tr_year = '".$tr_year."'	
		)AS tblsaldo_coa
		INNER JOIN (
			SELECT * FROM tblcoa WHERE fk_cabang is not null
			AND fk_cabang in ('".$fk_cabang."') and coa like '%.".$p_coa."%'
		)AS tblcoa ON tblsaldo_coa.fk_coa = tblcoa.coa
	"));
	
	return (($lreturn['total_balance'])?$lreturn['total_balance']:0);
}

function calculate_lb ($p_start_date,$p_cabang='') {
	$l_success=1;
	//$l_end = date('m/d/Y');
	$today_db=get_rec("tblsetting",'tgl_sistem');
	$l_end=($today_db);
	//$l_end=$p_start_date;
	//$lrow_utility = pg_fetch_array(pg_query("select * from tblutility"));
	//$begin = $lrow_utility['begin_month'];
	//$end = $lrow_utility['end_month'];
	$end=12;
	//$lrs = pg_query("select * from tblcabang ".(($p_cabang)?" where kd_cabang = '".$p_cabang."' ":"")." order by kd_cabang");
	if($p_cabang)$l_cabang="where kd_cabang in (".get_cabang_terkait($p_cabang).")";
	$lrs = pg_query("select * from tblcabang ".$l_cabang." order by kd_cabang");
	//showquery("select * from tblcabang ".$l_cabang." order by kd_cabang");
	$c=0;
	while($lrow = pg_fetch_array($lrs)){
		$fk_cabang = (($p_cabang)?$p_cabang:$lrow['kd_cabang']);
		$fk_merek = $lrow['fk_merek'];
		$l_start = $p_start_date;
		//echo $l_start.$l_end;
		while( strtotime($l_start) <= strtotime($l_end) ){
			//echo date("h:i:s").'a<br>';
			$tr_month = date('n',strtotime($l_start));
			$tr_year = date('Y',strtotime($l_start));

			$tgl = $tr_month."/01/".$tr_year;
			$temp_last_month = date('m/d/Y',strtotime('-1 second',strtotime($tgl)));
			$bulan_lalu = date('n',strtotime($temp_last_month));
			$tahun_lalu = date('Y',strtotime($temp_last_month));

			$total_pendapatan=get_saldo_coa('4',$tr_month,$tr_year,$fk_cabang);
			$total_biaya=get_saldo_coa('5',$tr_month,$tr_year,$fk_cabang);		
			$total_laba_rugi = $total_pendapatan - $total_biaya ;		
			//echo date("h:i:s").'b<br>';
			//echo $total_pendapatan ."-". $total_biaya ;
			
			$lcoa_lr_berjalan = get_coa_posting('laba_rugi_tahun_berjalan',$fk_cabang,"00"); // laba rugi tahun ini
			$lcoa_lr_ditahan = get_coa_posting('laba_rugi_ditahan',$fk_cabang,"00"); // laba rugi ditahan akhir tahun lalu
						
			//nilai adjustment
			$lr_berjalan_adjust=get_rec("(select sum(case when fk_coa_d is not null then total*-1 else total end)as total from data_accounting.tblgl_auto where extract(month from tr_date) ='".$tr_month."' and extract(year from tr_date) ='".$tr_year."' and type_owner!='LABA RUGI BULAN BERJALAN' and (fk_coa_d='".$lcoa_lr_berjalan[coa]."' or fk_coa_c='".$lcoa_lr_berjalan[coa]."'))as tblmain","total","total!=0 ");				
		
			//echo $lr_berjalan_adjust.'<br>';			
			
			//$EOM = date("m/d/Y", strtotime('-1 second',strtotime('+1 month',strtotime(date('m',strtotime($l_start)).'/01/'.date('Y',strtotime($l_start))))));
			$EOM = date("m/d/Y", strtotime('-1 second',strtotime('+1 month',strtotime(date('m',strtotime($l_start)).'/01/'.date('Y',strtotime($l_start))))));			
			$lquery_lr_bulanan = "select * from data_accounting.tblgl_auto where fk_coa_c = '".$lcoa_lr_berjalan['coa']."' and description = 'LR Bulanan' and tr_date = '".$EOM."'";
			//showquery("select * from data_accounting.tblgl_auto where fk_coa_c = '".$lcoa_lr_berjalan['coa']."' and description = 'LR Bulanan' and tr_date = '".$EOM."'");
			if(pg_num_rows(pg_query($lquery_lr_bulanan)) > 0 ){//klo ada dibulan ini
				$lrow_lr_bulanan = pg_fetch_array(pg_query($lquery_lr_bulanan));
				if(!pg_query("
					--insert into data_accounting.tblgl_auto_log select *,'".$_SESSION['id']."','".$_SESSION['username']."','".date('Y-m-d H:i:s')."','UB','".$_SERVER['SCRIPT_NAME']."_calculate_lb' from data_accounting.tblgl_auto where fk_coa_c = '".$lcoa_lr_berjalan['coa']."' and description = 'LR Bulanan' and tr_date = '".$EOM."';
					update data_accounting.tblgl_auto set total = '".$total_laba_rugi."' where fk_coa_c = '".$lcoa_lr_berjalan['coa']."' and description = 'LR Bulanan' and tr_date = '".$EOM."';
					--insert into data_accounting.tblgl_auto_log select *,'".$_SESSION['id']."','".$_SESSION['username']."','".date('Y-m-d H:i:s')."','UA','".$_SERVER['SCRIPT_NAME']."_calculate_lb' from data_accounting.tblgl_auto where fk_coa_c = '".$lcoa_lr_berjalan['coa']."' and description = 'LR Bulanan' and tr_date = '".$EOM."';
				"))$l_success=0;

				$l_sum_value=1;
				if(date('n',strtotime($EOM))=='1')$l_saldo_lr_bulan_lalu=0;
				else{
					$l_saldo_lr_bulan_lalu = get_rec("data_accounting.tblsaldo_coa","balance_gl_auto","tr_month = '".$bulan_lalu."' and tr_year = '".$tahun_lalu."' and fk_coa = '".$lcoa_lr_berjalan['coa']."'");
				}

				$l_success=update_saldo_coa('gl_auto',$lcoa_lr_berjalan[coa],'c',$l_saldo_lr_bulan_lalu,$EOM,0,0);

			}else{ // klo gak ada insert
				//echo 'insert gl auto';
				if(!pg_query("
					insert into data_accounting.tblgl_auto (
						tr_date,description,fk_coa_c,total,type_owner,fk_owner
					)values(
						'".$EOM."','LR Bulanan','".convert_sql($lcoa_lr_berjalan['coa'])."',
						'".convert_sql($total_laba_rugi)."','LABA RUGI BULAN BERJALAN','".$EOM."'
					);
					insert into data_accounting.tblgl_auto_log select *,'".$_SESSION['id']."','".$_SESSION['username']."','".date('Y-m-d H:i:s')."','IA','".$_SERVER['SCRIPT_NAME']."_calculate_lb' from data_accounting.tblgl_auto where fk_coa_c = '".$lcoa_lr_berjalan['coa']."' and description = 'LR Bulanan' and tr_date = '".$EOM."';
				"))$l_success=0;				
			}

			//echo date("h:i:s").'c<br>';
			$l_success=update_saldo_coa('gl_auto',$lcoa_lr_berjalan[coa],'c',$total_laba_rugi+$lr_berjalan_adjust,$EOM,1,0);
			$l_success=update_saldo_coa('gl_auto',$lcoa_lr_ditahan[coa],'c',0,$EOM,1,0);
			//echo date("h:i:s").'d<br>';
			$next_m = $tr_month + 1;
			if($next_m > 12){$next_y=$tr_year+1;$next_m=1;}
			else {$next_y = $tr_year;}
			//echo $tr_month;
			//klo tutup buku
			if($tr_month == $end){
				
				//total lr tahun berjalan
				$lrow_total_lr_tahun_berjalan = pg_fetch_array(pg_query("select *,(balance_memorial+balance_gl_auto+balance_cash+balance_bank)as saldo from data_accounting.tblsaldo_coa where fk_coa = '".$lcoa_lr_berjalan['coa']."' and tr_month = '".$tr_month."' and tr_year = '".$tr_year."'"));
				$total_lr_tahun_berjalan = $lrow_total_lr_tahun_berjalan['saldo'];
				
				//laba ditahan
				$lrow_total_lr_ditahan = pg_fetch_array(pg_query("select *,(balance_memorial+balance_gl_auto+balance_cash+balance_bank)as saldo from data_accounting.tblsaldo_coa where fk_coa = '".$lcoa_lr_ditahan['coa']."' and tr_month = '".$tr_month."' and tr_year = '".$tr_year."'"));
				$total_lr_ditahan = $lrow_total_lr_ditahan['saldo'];

				//cek ada gak di saldo
				//showquery("select * from data_accounting.tblsaldo_coa where fk_coa = '".$lcoa_lr_ditahan['coa']."' and tr_month = '".$next_m."' and tr_year = '".$next_y."'");
				if(pg_num_rows(pg_query("select * from data_accounting.tblsaldo_coa where fk_coa = '".$lcoa_lr_ditahan['coa']."' and tr_month = '".$next_m."' and tr_year = '".$next_y."'")) <=0 ){
					//echo 'insert saldo_coa';
					$lquery_tutup_buku = "
						insert into data_accounting.tblsaldo_coa (
							fk_coa,tr_month,tr_year,balance_gl_auto
						) values (
							'".convert_sql($lcoa_lr_ditahan[coa])."','".$next_m."','".$next_y."',
							'".($total_lr_tahun_berjalan+$total_lr_ditahan)."'
						);
						insert into data_accounting.tblsaldo_coa_log (
							fk_coa,tr_month,tr_year,balance_gl_auto,
							log_action_userid,log_action_username,log_action_date,
							log_action_mode,log_action_from
						) values (
							'".convert_sql($lcoa_lr_ditahan[coa])."','".$next_m."','".$next_y."',
							'".($total_lr_tahun_berjalan+$total_lr_ditahan)."',
							'".$_SESSION['id']."','".$_SESSION['username']."',
							'".date('Y-m-d H:i:s')."','IA','".$_SERVER['SCRIPT_NAME']."_calculate_lb'
						);
						insert into data_accounting.tblsaldo_coa (
							fk_coa,tr_month,tr_year
						) values (
							'".convert_sql($lcoa_lr_berjalan[coa])."','".$next_m."','".$next_y."'
						);
						insert into data_accounting.tblsaldo_coa_log (
							fk_coa,tr_month,tr_year,
							log_action_userid,log_action_username,log_action_date,
							log_action_mode,log_action_from
						) values (
							'".convert_sql($lcoa_lr_berjalan[coa])."','".$next_m."','".$next_y."',
							'".$_SESSION['id']."','".$_SESSION['username']."',
							'".date('Y-m-d H:i:s')."','IA','".$_SERVER['SCRIPT_NAME']."_calculate_lb'
						);
					";//showquery($lquery_tutup_buku);
					if(!pg_query($lquery_tutup_buku))$l_success=0;
				}else{
					$l_success=update_saldo_coa('gl_auto',$lcoa_lr_berjalan[coa],'c',0,$next_m.'/01/'.$next_y,0,0);
					$l_success=update_saldo_coa('gl_auto',$lcoa_lr_ditahan[coa],'c',($total_lr_tahun_berjalan+$total_lr_ditahan),$next_m.'/01/'.$next_y,0,0);
				}
			}
		
			$tr_month = $next_m;
			$tr_year = $next_y;
			$l_start = $tr_month."/01/".$tr_year;
			//echo $l_start.'<br>';
			$c++;
			if($c > 500){
				pg_query("rollback");
				echo 'a';
				exit("overloop");
			}
		}
		$c++;
		if($c > 500){
			pg_query("rollback");
			echo 'a';
			exit("overloop");
		}
	}
	//$l_success=0;
	return $l_success;

}

// ********************* FUNCTION UNTUK ACCOUNTING
function get_balance ($p_used_for='',$p_merek='',$p_cabang='',$m,$y,$p_head='',$p_coa='') {
	//if($p_cabang=='')$p_cabang=$_SESSION['kd_cabang'];
	if($p_used_for)$p_used_for = "'".str_replace(",","','",$p_used_for)."'";
	if($p_cabang)$p_cabang = "'".str_replace(",","','",$p_cabang)."'";
	$select="balance_cash+balance_bank+balance_memorial+balance_gl_auto";
	$lreturn = pg_fetch_array(pg_query("
		SELECT SUM(".$select.") AS total_balance FROM (
			SELECT * FROM data_accounting.tblsaldo_coa where tr_month = '".$m."' and tr_year = '".$y."'
			".(($p_coa)?"and fk_coa = '".$p_coa."'":"")."
		)AS tblsaldo_coa
		INNER JOIN (
			SELECT * FROM tblcoa WHERE fk_cabang is not null
			".(($p_used_for)?" AND used_for in (".$p_used_for.") ":"")."
			".(($p_merek)?"and fk_merek = '".$p_merek."'":"")."
			".(($p_head)?"and fk_head_account = '".$p_head."'":"")."
			".(($p_cabang)?" AND fk_cabang in (".$p_cabang.") ":"")."
		)AS tblcoa ON tblsaldo_coa.fk_coa = tblcoa.coa
		--GROUP BY fk_cabang
	"));
	
/*	showquery("
		SELECT SUM(".$select.") AS total_balance FROM (
			SELECT * FROM data_accounting.tblsaldo_coa where tr_month = '".$m."' and tr_year = '".$y."'
			".(($p_coa)?"and fk_coa = '".$p_coa."'":"")."
		)AS tblsaldo_coa
		INNER JOIN (
			SELECT * FROM tblcoa WHERE fk_cabang is not null
			".(($p_used_for)?" AND used_for in (".$p_used_for.") ":"")."
			".(($p_merek)?"and fk_merek = '".$p_merek."'":"")."
			".(($p_head)?"and fk_head_account = '".$p_head."'":"")."
			".(($p_cabang)?" AND fk_cabang in (".$p_cabang.") ":"")."
		)AS tblcoa ON tblsaldo_coa.fk_coa = tblcoa.coa
		--GROUP BY fk_cabang
	");*/
	
	return (($lreturn['total_balance'])?$lreturn['total_balance']:0);
}

function generate_list_head_account_by_group($p_group,$m,$y,$p_merek,$p_cabang,$p_link=0){	

	$lreturn['data'] = array();
	$lreturn['total'] = 0;
	$i = 0;
	$lrs_group=pg_query("select * from tblgroup_account where fk_group='".$p_group."'");
	//showquery("select * from tblgroup_account where fk_group='".$p_group."'");
	while ($lrow_group=pg_fetch_array($lrs_group)){
		$lrs_head=pg_query("select * from tblhead_account where code>='".$lrow_group["begin_range"]."' and code<='".$lrow_group["end_range"]."' and code not like '%0' order by code,description");
		//showquery("select * from tblhead_account where code>='".$lrow_group["begin_range"]."' and code<='".$lrow_group["end_range"]."' order by code,description");
		while ($lrow_head=pg_fetch_array($lrs_head)){
			$lreturn['data'][$i]['keterangan'] = str_pad("",8).$lrow_head['description'];
			$lbalance = get_balance('',$p_merek,$p_cabang,$m,$y,$lrow_head['code']);
			if($p_link)	$lreturn['data'][$i]['total'] = "<c:alink:modal_report_head_account_detail.php?fk_head_account=".$lrow_head["code"]."&bulan=".$m."&tahun=".$y."&fk_merek=".$p_merek.">".$lbalance."</c:alink>";
			else $lreturn['data'][$i]['total'] = $lbalance;
			$lreturn['total'] += $lbalance;
			$i++;
		}
	}
	
	return $lreturn;
}

function generate_list_coa_by_head_account($p_head,$m,$y,$p_merek,$p_link=0){	

	$lreturn['data'] = array();
	$lreturn['total'] = 0;
	$i = 0;
	$lrs_coa=pg_query("select * from tblcoa where fk_head_account = '".convert_sql($p_head)."' order by coa,description");
	while ($lrow_coa=pg_fetch_array($lrs_coa)){
		$lreturn['data'][$i]['keterangan'] = $lrow_coa['description'];
		$lbalance = get_balance('',$p_merek,'',$m,$y,'',$lrow_coa['coa']);

		if($p_link)	$lreturn['data'][$i]['total'] = "<c:alink:modal_report_ledger.php?report=ledger&bulan=".$m."&tahun=".$y."&fk_coa=".$lrow_coa['coa'].">".convert_money('',$lbalance,2)."</c:alink>";
		else $lreturn['data'][$i]['total'] = convert_money('',$lbalance,2);
		
		$lreturn['total'] += $lbalance;
		$i++;
	}
	
	$lreturn['index'] =  $i;
	return $lreturn;
}

//utk create AP
function create_saldo_ap($p_transaksi,$p_jenis,$p_tanggal='',$p_jumlah=0,$p_cabang='',$p_vendor='',$p_divisi='',$p_coa=NULL,$p_keterangan=NULL){
	global $counter;
	if($p_cabang=='')$p_cabang=$_SESSION['kd_cabang'];
	//if($p_tanggal=='')$p_tanggal=date('m/d/Y');
	$today_db=get_rec("tblsetting",'tgl_sistem');

	if($p_tanggal=='')$p_tanggal=$today_db;
	//if($p_cabang=="")return false; //recheck kode cabang
	$bulan = date('n',strtotime($p_tanggal));
	$tahun = date('Y',strtotime($p_tanggal));

	$counter++;
	if($counter >= 1000000){echo 'overloop';break;}
	//klo record barus

	if($p_coa==""){
		$l_coa_array = get_coa_posting($p_jenis,$p_cabang,$p_divisi); // get coa utk simpen di table
		$l_coa = $l_coa_array['coa']; // karena return nya array
		//if($l_coa == '')return false; // in case gak dpt coa nya
	}else $l_coa = $p_coa;

	if(pg_num_rows(pg_query("select * from data_accounting.tblap where no_transaksi = '".$p_transaksi."' and jenis_transaksi = '".$p_jenis."' and detail_transaksi = '".$p_jenis."'")) <= 0){
		$p_eom = date("m/d/Y", strtotime('-1 second',strtotime('+1 month',strtotime(date('m',strtotime($p_tanggal)).'/01/'.date('Y',strtotime($p_tanggal))))));
		$eom = date("m/d/Y", strtotime('-1 second',strtotime('+1 month',strtotime(date('m',strtotime($today_db)).'/01/'.date('Y',strtotime($today_db))))));
		
		while(strtotime($p_eom) <= strtotime($eom)){
		
			$lquery = "
				insert into data_accounting.tblap (
					no_transaksi,tgl_transaksi,jenis_transaksi,
					detail_transaksi,jumlah_transaksi,fk_cabang,
					bulan,tahun,saldo,fk_partner,fk_jenis_cabang,fk_coa,
					keterangan
				)values(
					'".$p_transaksi."','".$p_tanggal."','".$p_jenis."',
					'".$p_jenis."',".(($p_jumlah)?"round('".$p_jumlah."',2)":0).",'".$p_cabang."',
					'".$bulan."','".$tahun."',".(($p_jumlah)?"round('".$p_jumlah."',2)":0).",
					".(($p_vendor)?"'".convert_sql($p_vendor)."'":"null").",
					".(($p_divisi)?"'".convert_sql($p_divisi)."'":"null").",
					".(($l_coa)?"'".convert_sql($l_coa)."'":"null").",
					".(($p_keterangan)?"'".convert_sql($p_keterangan)."'":"null")."
				);
				insert into data_accounting.tblap_log (
					no_transaksi,tgl_transaksi,jenis_transaksi,
					detail_transaksi,jumlah_transaksi,fk_cabang,
					bulan,tahun,saldo,fk_partner,fk_jenis_cabang,fk_coa,
					keterangan,log_action_userid,log_action_username,
					log_action_date,log_action_mode,log_action_from
				)values(
					'".$p_transaksi."','".$p_tanggal."','".$p_jenis."',
					'".$p_jenis."',".(($p_jumlah)?"round('".$p_jumlah."',2)":0).",'".$p_cabang."',
					'".$bulan."','".$tahun."',".(($p_jumlah)?"round('".$p_jumlah."',2)":0).",
					".(($p_vendor)?"'".convert_sql($p_vendor)."'":"null").",
					".(($p_divisi)?"'".convert_sql($p_divisi)."'":"null").",
					".(($l_coa)?"'".convert_sql($l_coa)."'":"null").",
					".(($p_keterangan)?"'".convert_sql($p_keterangan)."'":"null").",
					'".$_SESSION['id']."','".$_SESSION['username']."',
					'".date('m/d/Y H:i:s')."','IA','".$_SERVER['PHP_SELF']."'
				);
			";//showquery($lquery);
			
			$bulan += 1;
			if($bulan > 12){$tahun+=1;$bulan=1;}
			$p_eom = $bulan."/01/".$tahun;

			//echo $lquery;
			if(!pg_query($lquery))$lreturn = 0;
			else $lreturn = 1;

		}
		//echo $lreturn;
		return $lreturn;

	}else{//klo ada record
		
		//cek klo saldo ap utk bulan/tahun bersangkutan sudah ada atau blum klo blum dicari ke bulan/tahun lalu
		if(pg_num_rows(pg_query("select * from data_accounting.tblap where no_transaksi = '".$p_transaksi."' and jenis_transaksi = '".$p_jenis."' and detail_transaksi = '".$p_jenis."' and bulan = '".$bulan."' and tahun = '".$tahun."'")) <= 0){
			
			//ambil bulan/tahun lalu
			$bulan_saldo = $bulan-1;
			if($bulan_saldo<=0){
				$bulan_saldo=12;
				$tahun_saldo = $tahun-1;
			}else $tahun_saldo = $tahun;
		
			//ambil saldo ap bulan/tahun lalu utk create bulan skrg
			if(pg_num_rows(pg_query("select * from data_accounting.tblap where no_transaksi = '".$p_transaksi."' and jenis_transaksi = '".$p_jenis."' and detail_transaksi = '".$p_jenis."' and bulan = '".$bulan_saldo."' and tahun = '".$tahun_saldo."'")) <= 0){
				if(!create_saldo_ap($p_transaksi,$p_jenis,$bulan_saldo.'/01/'.$tahun_saldo,$p_jumlah,$p_cabang))return false;
			}
			
			//klo ketemu maka di insert
			$lrow = pg_fetch_array(pg_query("select * from data_accounting.tblap where no_transaksi = '".$p_transaksi."' and jenis_transaksi = '".$p_jenis."' and detail_transaksi = '".$p_jenis."' and bulan = '".$bulan_saldo."' and tahun = '".$tahun_saldo."'"));
			$lquery = "
				insert into data_accounting.tblap (
					no_transaksi,tgl_transaksi,jenis_transaksi,
					detail_transaksi,jumlah_transaksi,fk_cabang,bulan,tahun,
					jumlah_alokasi,jumlah_approve,saldo,
					fk_partner,fk_jenis_cabang,fk_coa,keterangan
				)values(
					'".$p_transaksi."','".$lrow['tgl_transaksi']."','".$p_jenis."',
					'".$p_jenis."','".$lrow['jumlah_transaksi']."','".$lrow['fk_cabang']."',
					'".$bulan."','".$tahun."','".$lrow['jumlah_alokasi']."',
					'".$lrow['jumlah_approve']."','".$lrow['saldo']."',
					".(($lrow['fk_partner'])?"'".convert_sql($lrow['fk_partner'])."'":"null").",
					".(($lrow['fk_jenis_cabang'])?"'".convert_sql($lrow['fk_jenis_cabang'])."'":"null").",
					".(($lrow['fk_coa'])?"'".$lrow['fk_coa']."'":"null").",
					".(($lrow['keterangan'])?"'".convert_sql($lrow['keterangan'])."'":"null")."
				);
				insert into data_accounting.tblap_log (
					no_transaksi,tgl_transaksi,jenis_transaksi,
					detail_transaksi,jumlah_transaksi,fk_cabang,
					bulan,tahun,jumlah_alokasi,jumlah_approve,saldo,
					fk_partner,fk_jenis_cabang,fk_coa,keterangan,
					log_action_userid,log_action_username,
					log_action_date,log_action_mode,log_action_from
				)values(
					'".$p_transaksi."','".$lrow['tgl_transaksi']."','".$p_jenis."',
					'".$p_jenis."','".$lrow['jumlah_transaksi']."','".$lrow['fk_cabang']."',
					'".$bulan."','".$tahun."','".$lrow['jumlah_alokasi']."',
					'".$lrow['jumlah_approve']."','".$lrow['saldo']."',
					".(($lrow['fk_partner'])?"'".convert_sql($lrow['fk_partner'])."'":"null").",
					".(($lrow['fk_jenis_cabang'])?"'".convert_sql($lrow['fk_jenis_cabang'])."'":"null").",
					".(($lrow['fk_coa'])?"'".$lrow['fk_coa']."'":"null").",
					".(($lrow['keterangan'])?"'".convert_sql($lrow['keterangan'])."'":"null").",
					'".$_SESSION['id']."','".$_SESSION['username']."',
					'".date('m/d/Y H:i:s')."','IA','".$_SERVER['PHP_SELF']."'
				);
			";//showquery($lquery);
			if(!pg_query($lquery))return false;
			else return true;
		}
	}
}
//update saldo payment=======================
function update_saldo_ap($p_transaksi,$p_jenis,$p_tanggal='',$p_arr,$p_cabang=''){
	if(!is_array($p_arr))return false;
	if($p_cabang=='')$p_cabang=$_SESSION['kd_cabang'];
	$bulan = date('n',strtotime($p_tanggal));
	$tahun = date('Y',strtotime($p_tanggal));
	//klo ada transaksi yg beda bulan maka di-create saldo ap nya
	
	if(pg_num_rows(pg_query("select * from data_accounting.tblap where no_transaksi = '".$p_transaksi."' and jenis_transaksi = '".$p_jenis."' and detail_transaksi = '".$p_jenis."' and bulan = '".$bulan."' and tahun = '".$tahun."'")) <= 0) {
		$lrow_jenis_transaksi=pg_fetch_array(pg_query("select * from tbljenis_transaksi where kd_jenis_transaksi='".$p_jenis."'"));
		create_saldo_ap($p_transaksi,$p_jenis,$p_tanggal,0,$p_cabang,"",$lrow_jenis_transaksi["fk_jenis_cabang"]);
	}

	//ambil bulan depan
	$bulan_saldo = $bulan+1;
	if($bulan_saldo>=13){
		$bulan_saldo=1;
		$tahun_saldo = $tahun+1;
	}else $tahun_saldo = $tahun;

	//kalau di bulan depan masih ada di update juga
	if(pg_num_rows(pg_query("select * from data_accounting.tblap where no_transaksi = '".$p_transaksi."' and jenis_transaksi = '".$p_jenis."' and detail_transaksi = '".$p_jenis."' and bulan = '".$bulan_saldo."' and tahun = '".$tahun_saldo."'"))){
		if(!update_saldo_ap($p_transaksi,$p_jenis,$bulan_saldo.'/01/'.$tahun_saldo,$p_arr,$p_cabang))return false;
	}

	$lquery='';
	foreach($p_arr as $l_transaksi => $lValue) $lquery .= $l_transaksi.' = '.$l_transaksi." + ".$lValue." ,";
	$lquery = substr($lquery,0,strlen($lquery)-1);

	//kurangin saldo bulan sekarang
	$lquery_ = "
		insert into data_accounting.tblap_log select *,'".$_SESSION['id']."','".$_SESSION['username']."','".date('Y-m-d H:i:s')."','UB','".$_SERVER['SCRIPT_NAME']."' from data_accounting.tblap where no_transaksi = '".$p_transaksi."' and jenis_transaksi = '".$p_jenis."' and detail_transaksi = '".$p_jenis."' and bulan = '".$bulan."' and tahun = '".$tahun."';

		update data_accounting.tblap set ".$lquery." where no_transaksi = '".$p_transaksi."' and jenis_transaksi = '".$p_jenis."' and detail_transaksi = '".$p_jenis."' and bulan = '".$bulan."' and tahun = '".$tahun."';

		insert into data_accounting.tblap_log select *,'".$_SESSION['id']."','".$_SESSION['username']."','".date('Y-m-d H:i:s')."','UA','".$_SERVER['SCRIPT_NAME']."' from data_accounting.tblap where no_transaksi = '".$p_transaksi."' and jenis_transaksi = '".$p_jenis."' and detail_transaksi = '".$p_jenis."' and bulan = '".$bulan."' and tahun = '".$tahun."';
	";//showquery($lquery_);
	if(!pg_query($lquery_)){
		return false;
	}else return true;
	

}
//================================================
//utk cek payment yg sudah dilakukan
function cek_saldo_ap($p_transaksi,$p_jenis,$p_tanggal='',$p_jumlah=0,$p_cabang='',$p_cek_jumlah='alokasi',$p_cek_by_date=true){
	//return 1 klo gak ketemu
	//return 2 klo jumlah bayar salah(bwt transaksi kredit)
	//return 3 klo udah ada bayar
	if($p_cabang=='')$p_cabang=$_SESSION['kd_cabang'];
	$bulan = date('n',strtotime($p_tanggal));
	$tahun = date('Y',strtotime($p_tanggal));
	$lreturn = 0;
	if($lrow = pg_fetch_array(pg_query("select * from data_accounting.tblap where no_transaksi = '".$p_transaksi."' and jenis_transaksi = '".$p_jenis."' and detail_transaksi = '".$p_jenis."' ".(($p_cek_by_date)?"":" and bulan = '".$bulan."' and tahun = '".$tahun."'")." for update"))){
		if( $lrow['jumlah_transaksi'] < ($lrow['jumlah_'.$p_cek_jumlah]+$p_jumlah) ){//cek lebih bayar
			$lreturn = 2;
		}elseif($lrow['jumlah_'.$p_cek_jumlah] > 0){//klo udah ada pembayaran
			$lreturn = 3;
		}
	}else $lreturn = 1;
	//showquery("select * from data_accounting.tblap where no_transaksi = '".$p_transaksi."' and jenis_transaksi = '".$p_jenis."' and detail_transaksi = '".$p_jenis."' ".(($p_cek_by_date)?"":" and bulan = '".$bulan."' and tahun = '".$tahun."'")." for update");
	return $lreturn;
}
//================================================
//delete
function delete_saldo_ap($p_transaksi,$p_jenis,$p_tanggal='',$p_jumlah=0,$p_cabang=''){
	if($p_cabang=='')$p_cabang=$_SESSION['kd_cabang'];
	$lquery = "
		insert into data_accounting.tblap_log select *,'".$_SESSION['id']."','".$_SESSION['username']."','".date('Y-m-d H:i:s')."','DB','".$_SERVER['SCRIPT_NAME']."' from data_accounting.tblap where no_transaksi = '".$p_transaksi."' and jenis_transaksi = '".$p_jenis."' and detail_transaksi = '".$p_jenis."';
		delete from data_accounting.tblap where no_transaksi = '".$p_transaksi."' and jenis_transaksi = '".$p_jenis."' and detail_transaksi = '".$p_jenis."';
	";
	//showquery($lquery);
	if(!pg_query($lquery)) return false;
	else return true;
}
//================================================
//bikin saldo ar===================================
function create_saldo_ar($p_transaksi,$p_jenis,$p_tanggal='',$p_jumlah=0,$p_cabang='',$p_customer='',$p_divisi='00',$p_coa=NULL,$p_keterangan=NULL){
	global $counter;

	if($p_cabang=='')$p_cabang=$_SESSION['kd_cabang'];
	//if($p_tanggal=='')$p_tanggal=date('m/d/Y');
	$today_db=get_rec("tblsetting",'tgl_sistem');
	if($p_tanggal=='')$p_tanggal=$today_db;
	//echo $p_tanggal;
	$bulan = date('n',strtotime($p_tanggal));
	$tahun = date('Y',strtotime($p_tanggal));

	$counter++;
	if($counter >= 1000000){echo 'overloop';break;}
	if($p_coa==""){
		$l_coa_array = get_coa_posting($p_jenis,$p_cabang,$p_divisi); // get coa utk simpen di table
		$l_coa = $l_coa_array['coa']; // karena return nya array
	}else $l_coa = $p_coa;

	//klo record barus
	//echo "select * from data_accounting.tblar where no_transaksi = '".$p_transaksi."' and jenis_transaksi = '".$p_jenis."' ";
	if(pg_num_rows(pg_query("select * from data_accounting.tblar where no_transaksi = '".$p_transaksi."' and jenis_transaksi = '".$p_jenis."' " )) <= 0){

		$p_eom = date("m/d/Y", strtotime('-1 second',strtotime('+1 month',strtotime(date('m',strtotime($p_tanggal)).'/01/'.date('Y',strtotime($p_tanggal))))));
		$eom = date("m/d/Y", strtotime('-1 second',strtotime('+1 month',strtotime(date('m',strtotime($today_db)).'/01/'.date('Y',strtotime($today_db))))));
		//echo $eom;
		while(strtotime($p_eom) <= strtotime($eom)){
		
			$lquery = "
				insert into data_accounting.tblar (
					no_transaksi,tgl_transaksi,jenis_transaksi,
					jumlah_transaksi,fk_cabang,bulan,tahun,
					saldo,fk_customer,fk_jenis_cabang,fk_coa,keterangan
				)values(
					'".$p_transaksi."','".$p_tanggal."','".$p_jenis."',
					round('".$p_jumlah."',2) , '".$p_cabang."',
					'".$bulan."','".$tahun."', round('".$p_jumlah."',2) ,
					".(($p_customer)?"'".convert_sql($p_customer)."'":"null").",
					".(($p_divisi)?"'".convert_sql($p_divisi)."'":"null").",
					'".$l_coa."',".(($p_keterangan)?"'".convert_sql($p_keterangan)."'":"null")."
				);
				insert into data_accounting.tblar_log (
					no_transaksi,tgl_transaksi,jenis_transaksi,
					jumlah_transaksi,fk_cabang,bulan,tahun,
					saldo,fk_customer,fk_jenis_cabang,fk_coa,keterangan,
					log_action_userid,log_action_username,
					log_action_date,log_action_mode,log_action_from
				)values(
					'".$p_transaksi."','".$p_tanggal."','".$p_jenis."',
					round('".$p_jumlah."',2) , '".$p_cabang."',
					'".$bulan."','".$tahun."', round('".$p_jumlah."',2) ,
					".(($p_customer)?"'".convert_sql($p_customer)."'":"null").",
					".(($p_divisi)?"'".convert_sql($p_divisi)."'":"null").",
					'".$l_coa."',".(($p_keterangan)?"'".convert_sql($p_keterangan)."'":"null").",
					'".$_SESSION['id']."','".$_SESSION['username']."',
					'".date('m/d/Y H:i:s')."','IA','".$_SERVER['PHP_SELF']."'
				);
			";//showquery($lquery);
			
			$bulan += 1;
			if($bulan > 12){$tahun+=1;$bulan=1;}
			$p_eom = $bulan."/01/".$tahun;

			//echo $lquery;
			if(!pg_query($lquery))$lreturn = 0;
			else $lreturn = 1;

		}
		return $lreturn;

	}else{//klo ada record

		//cek klo saldo ar utk bulan/tahun bersangkutan sudah ada atau blum klo blum dicari ke bulan/tahun lalu
		if(pg_num_rows(pg_query("select * from data_accounting.tblar where no_transaksi = '".$p_transaksi."' and jenis_transaksi = '".$p_jenis."'  and bulan = '".$bulan."' and tahun = '".$tahun."'")) <= 0){
			
			//ambil bulan/tahun lalu
			$bulan_saldo = $bulan-1;
			if($bulan_saldo<=0){
				$bulan_saldo=12;
				$tahun_saldo = $tahun-1;
			}else $tahun_saldo = $tahun;
			
			//ambil saldo ar bulan/tahun lalu utk create bulan skrg
			
			if(pg_num_rows(pg_query("select * from data_accounting.tblar where no_transaksi = '".$p_transaksi."' and jenis_transaksi = '".$p_jenis."'  and bulan = '".$bulan_saldo."' and tahun = '".$tahun_saldo."'")) <= 0){
				//echo "select * from data_accounting.tblar where no_transaksi = '".$p_transaksi."' and jenis_transaksi = '".$p_jenis."'  and bulan = '".$bulan_saldo."' and tahun = '".$tahun_saldo."'<br>";
				if(!create_saldo_ar($p_transaksi,$p_jenis,$bulan_saldo.'/01/'.$tahun_saldo,$p_jumlah,$p_cabang))return false;
			}
			
			//klo ketemu maka di insert
			$lrow = pg_fetch_array(pg_query("select * from data_accounting.tblar where no_transaksi = '".$p_transaksi."' and jenis_transaksi = '".$p_jenis."'  and bulan = '".$bulan_saldo."' and tahun = '".$tahun_saldo."'"));
			
			$lquery = "
				insert into data_accounting.tblar (
					no_transaksi,tgl_transaksi,jenis_transaksi,
					jumlah_transaksi,fk_cabang,bulan,tahun,
					jumlah_alokasi,jumlah_approve,saldo,
					fk_customer,fk_jenis_cabang,fk_coa,keterangan
				)values(
					'".$p_transaksi."','".$lrow['tgl_transaksi']."','".$p_jenis."',
					'".$lrow['jumlah_transaksi']."','".$lrow['fk_cabang']."',
					'".$bulan."','".$tahun."','".$lrow['jumlah_alokasi']."',
					'".$lrow['jumlah_approve']."','".$lrow['saldo']."',
					".(($lrow['fk_customer'])?"'".convert_sql($lrow['fk_customer'])."'":"null").",
					".(($lrow['fk_jenis_cabang'])?"'".convert_sql($lrow['fk_jenis_cabang'])."'":"null").",
					".(($lrow['fk_coa'])?"'".$lrow['fk_coa']."'":"null").",
					".(($lrow['keterangan'])?"'".convert_sql($lrow['keterangan'])."'":"null")."
				);
				insert into data_accounting.tblar_log (
					no_transaksi,tgl_transaksi,jenis_transaksi,
					jumlah_transaksi,fk_cabang,
					bulan,tahun,jumlah_alokasi,jumlah_approve,saldo,
					fk_customer,fk_jenis_cabang,fk_coa,keterangan,
					log_action_userid,log_action_username,
					log_action_date,log_action_mode,log_action_from
				)values(
					'".$p_transaksi."','".$lrow['tgl_transaksi']."','".$p_jenis."',
					'".$lrow['jumlah_transaksi']."','".$lrow['fk_cabang']."',
					'".$bulan."','".$tahun."','".$lrow['jumlah_alokasi']."',
					'".$lrow['jumlah_approve']."','".$lrow['saldo']."',
					".(($lrow['fk_customer'])?"'".convert_sql($lrow['fk_customer'])."'":"null").",
					".(($lrow['fk_jenis_cabang'])?"'".convert_sql($lrow['fk_jenis_cabang'])."'":"null").",
					".(($lrow['fk_coa'])?"'".$lrow['fk_coa']."'":"null").",
					".(($lrow['keterangan'])?"'".convert_sql($lrow['keterangan'])."'":"null").",
					'".$_SESSION['id']."','".$_SESSION['username']."',
					'".date('m/d/Y H:i:s')."','IA','".$_SERVER['PHP_SELF']."'
				);
			";//showquery($lquery);
			if(!pg_query($lquery))return false;
			else return true;
		}
	}
}
//=================================================
//update saldo ar=======================
function update_saldo_ar($p_transaksi,$p_jenis,$p_tanggal='',$p_arr,$p_cabang=''){
	if(!is_array($p_arr))return false;
	if($p_cabang=='')$p_cabang=$_SESSION['kd_cabang'];
	$bulan = date('n',strtotime($p_tanggal));
	$tahun = date('Y',strtotime($p_tanggal));

	if(pg_num_rows(pg_query("select * from data_accounting.tblar where no_transaksi = '".$p_transaksi."' and jenis_transaksi = '".$p_jenis."' and bulan = '".$bulan."' and tahun = '".$tahun."'")) <= 0)create_saldo_ar($p_transaksi,$p_jenis,$p_tanggal,0,$p_cabang);

	//ambil bulan depan
	$bulan_saldo = $bulan+1;
	if($bulan_saldo>=13){
		$bulan_saldo=1;
		$tahun_saldo = $tahun+1;
	}else $tahun_saldo = $tahun;

	//kalau di bulan depan masih ada di update juga
	if(pg_num_rows(pg_query("select * from data_accounting.tblar where no_transaksi = '".$p_transaksi."' and jenis_transaksi = '".$p_jenis."' and bulan = '".$bulan_saldo."' and tahun = '".$tahun_saldo."'"))){
		if(!update_saldo_ar($p_transaksi,$p_jenis,$bulan_saldo.'/01/'.$tahun_saldo,$p_arr,$p_cabang))return false;
	}

	$lquery='';
	foreach($p_arr as $l_transaksi => $l_value) $lquery .= $l_transaksi.' = '.$l_transaksi." + round( ".$l_value.",2 ) ,";
	$lquery = substr($lquery,0,strlen($lquery)-1);

	//kurangin saldo bulan sekarang
	$lquery_ = "
		insert into data_accounting.tblar_log select *,'".$_SESSION['id']."','".$_SESSION['username']."','".date('Y-m-d H:i:s')."','UB','".$_SERVER['SCRIPT_NAME']."' from data_accounting.tblar where no_transaksi = '".$p_transaksi."' and jenis_transaksi = '".$p_jenis."' and bulan = '".$bulan."' and tahun = '".$tahun."';

		update data_accounting.tblar set ".$lquery." where no_transaksi = '".$p_transaksi."' and jenis_transaksi = '".$p_jenis."' and bulan = '".$bulan."' and tahun = '".$tahun."';
	
		insert into data_accounting.tblar_log select *,'".$_SESSION['id']."','".$_SESSION['username']."','".date('Y-m-d H:i:s')."','UA','".$_SERVER['SCRIPT_NAME']."' from data_accounting.tblar where no_transaksi = '".$p_transaksi."' and jenis_transaksi = '".$p_jenis."' and bulan = '".$bulan."' and tahun = '".$tahun."';
	";
	//showquery($lquery_);
	if(!pg_query($lquery_)){
		return false;
	}else return true;
	
}

function create_list_head_account_by_group_account($p_group,$p_plus_if="C",$p_merek=NULL,$p_cabang=NULL,$m=NULL,$y=NULL){
	global $fk_merek,$fk_cabang,$tr_month,$tr_year;
	
	if($p_merek=="")$p_merek = $fk_merek;
	if($p_cabang=="")$p_cabang = $fk_cabang;
	if($m=="")$m = $tr_month;
	if($y=="")$y = $tr_year;

	$i=0;
	//showquery("select * from tblgroup_account where fk_group = '".$p_group."' order by fk_group");
	$lrs_group = pg_query("select * from tblgroup_account where fk_group = '".$p_group."' order by fk_group");
	while($lrow_group = pg_fetch_array($lrs_group)){ // get group
		$total_per_group = array();
		$lrs_head_account = pg_query("select * from tblhead_account where code >= '".$lrow_group['begin_range']."' and code <= '".$lrow_group['end_range']."' order by code");
		//showquery("select * from tblhead_account where code >= '".$lrow_group['begin_range']."' and code <= '".$lrow_group['end_range']."' order by code");
		while($lrow_head_account = pg_fetch_array($lrs_head_account)){ // get semua head account per group

//			echo $lrow_head_account['description'].'<br>';
			$data_olahan['data'][$i]["keterangan"]=str_pad("",8).$lrow_head_account['description'];
			//$lrs_jenis_cabang = pg_query("select * from tbljenis_cabang where is_laba_rugi is true order by kd_jenis_cabang");
			$lrs_jenis_cabang = pg_query("select '00' as kd_jenis_cabang,'HO'  as jenis_cabang");
			$total_balance = 0;

			while($lrow_jenis_cabang = pg_fetch_array($lrs_jenis_cabang)){

				$lbalance = get_balance_by_type_transaction(NULL,$p_merek,$p_cabang,$lrow_jenis_cabang['kd_jenis_cabang'],$m,$y,$lrow_head_account['code'],NULL,$p_plus_if);

				$data_olahan['data'][$i][$lrow_jenis_cabang['kd_jenis_cabang']]	= convert_money("",$lbalance,2);
				//"<c:alink:modal_report_head_account_detail.php?fk_head_account=".$lrow_head["code"]."&bulan=".$m."&tahun=".$y."&fk_merek=".$p_merek."&fk_cabang=".$p_cabang.">".convert_money('',$ltotal_balance,2)."</c:alink>";
				
				$total_balance += $lbalance;
				$total_per_group['total'] += $lbalance;
				$total_per_group[$lrow_jenis_cabang['kd_jenis_cabang']] += $lbalance;

			}

			$data_olahan['data'][$i]["total"]= "<c:alink:modal_report_head_account_detail.php?fk_head_account=".$lrow_head_account["code"]."&bulan=".$tr_month."&tahun=".$tr_year."&fk_merek=".$fk_merek."&fk_cabang=".$fk_cabang.">".convert_money('',$total_balance,2)."</c:alink>";
			//convert_money("",$total_balance,2);
//			echo $total_per_group." += ".$total_balance.'<br>';
			$i++;

		}

		$data_olahan['data'][$i]["keterangan"] = "Total ".$p_group;
		$lrs_jenis_cabang = pg_query("select * from tbljenis_cabang where is_laba_rugi is true order by kd_jenis_cabang");

		while($lrow_jenis_cabang = pg_fetch_array($lrs_jenis_cabang)){

			$data_olahan['data'][$i][$lrow_jenis_cabang['kd_jenis_cabang']]	= convert_money("",$total_per_group[$lrow_jenis_cabang['kd_jenis_cabang']],2);
			$data_olahan['total'][$lrow_jenis_cabang['kd_jenis_cabang']] = $total_per_group[$lrow_jenis_cabang['kd_jenis_cabang']];

		}

		$data_olahan['data'][$i]["total"]= convert_money("",$total_per_group['total'],2);
		$data_olahan['total']['total'] = $total_per_group['total'];
//		print_r($data_olahan);
//		echo $total_per_group;

	}
	
	return $data_olahan;
}
//================================================
//FUNGSI GADAI

function get_coa_cabang($p_cabang,$p_wilayah=NULL){	
	$p_table="tblcabang";
	$p_where=" kd_cabang='".$p_cabang."' ";
	$lquery = "select * from ".$p_table." where ".$p_where."";
	//showquery($lquery);
	$lrow = pg_fetch_array(pg_query($lquery));
	$kd_accounting=$lrow["kd_accounting"];	
	$p_cabang_ho=get_rec("tblsetting","fk_cabang_ho");
	
	//Kalau antar wilayah dengan cabang ,parameter p_wilayah harus diisi 
	if($p_wilayah)$p_cabang_ho=$p_wilayah;	
	
	if($p_cabang==$p_cabang_ho)return ($p_cabang.'.'.$kd_accounting);
	else return ($p_cabang_ho.'.'.$kd_accounting);
	//return ($kd_accounting);
}

function get_coa_bank($p_bank,$p_cabang){
	
	$p_table="tblcabang_detail_bank";
	$p_where=" fk_bank='".$p_bank."' and  fk_cabang='".$p_cabang."' ";
	$lquery = "select * from ".$p_table." where ".$p_where."";
	//showquery($lquery);
	$lrow = pg_fetch_array(pg_query($lquery));
	$fk_coa=$lrow["fk_coa"];
	return ($p_cabang.'.'.$fk_coa);
	//return ($fk_coa);
}


function get_saldo_bank($p_bank,$p_cabang,$p_tgl=NULL){
	if(!$p_tgl)$p_tgl=get_rec("tblsetting","tgl_sistem");
	
	$p_table="data_fa.tblsaldo_bank";
	$p_where=" fk_bank='".$p_bank."' and  fk_cabang='".$p_cabang."' and tgl='".$p_tgl."'";
	$lquery = "select * from ".$p_table." where ".$p_where."";
	//showquery($lquery);
	$lrow = pg_fetch_array(pg_query($lquery));
	$nominal=$lrow["akhir"];
	
	return (!$nominal?0:$nominal);
}


function create_saldo_bank($p_bank,$p_cabang,$p_tgl,$nominal=0){
	
	$p_table="data_fa.tblsaldo_bank";
	//$yesterday=date("Y-m-d",strtotime('-1 day',strtotime($p_tgl)));
	//$p_where=" fk_bank='".$p_bank."' and  fk_cabang='".$p_cabang."' and tgl='".$yesterday."'";
	
	$lquery = "insert into ".$p_table." (tgl,fk_bank,fk_cabang,awal,masuk,keluar,akhir) values ('".$p_tgl."','".$p_bank."','".$p_cabang."',".$nominal.",0,0,".$nominal.");";
	$p_where=" fk_bank='".$p_bank."' and  fk_cabang='".$p_cabang."' and tgl='".$p_tgl."'";
	//$lquery.=insert_log($p_table,$p_where,'IA');	
	return $lquery;

}
function update_saldo_bank($p_bank,$p_cabang,$masuk=0,$keluar=0,$p_keterangan=null,$p_id=null,$p_referensi=null,$p_coa=null){
	global $strmsg,$l_success;
	$p_table="data_fa.tblsaldo_bank";
	$lsetting=pg_fetch_array(pg_query("select * from tblsetting"));

	if($p_keterangan=='BUYBACK'){
		$today_db=date("Y-m-d",strtotime($lsetting["tgl_sistem"]));
	}else{
		$today_db=today_db;
	}

	$p_where=" fk_bank='".$p_bank."' and  fk_cabang='".$p_cabang."' and tgl='".$today_db."'";
	
	//showquery("select * from  ".$p_table." where ".$p_where."");
	if(!pg_num_rows(pg_query("select * from  ".$p_table." where ".$p_where.""))){
		if(pg_num_rows(pg_query("select * from tblcabang_detail_bank where fk_bank='".$p_bank."' and  fk_cabang='".$p_cabang."'"))){
			if(!pg_query(create_saldo_bank($p_bank,$p_cabang,$today_db)))$l_success=0;
		}else{
			$l_msg="<b> Bank : ".$p_bank." Cabang : ".$p_cabang." tidak ada</b><br>";
			$strmsg=$l_msg;
		}
		//showquery(create_saldo_bank($p_bank,$p_cabang,today_db));
	}
	
	
	$lquery.=insert_log($p_table,$p_where,'UB');
	
	$akhir=$masuk-$keluar;
	$update.="masuk=masuk+".$masuk.",";
	$update.="keluar=keluar+".$keluar.",";
	$update.="akhir=akhir+".$akhir."";
	$lquery .= "
		update ".$p_table." set 
		".$update."
		where ".$p_where." ;
	";
	
	$lquery.=insert_log($p_table,$p_where,'UA');
	
	$lquery .= "
		insert into data_fa.tblhistory_bank
		(fk_bank,fk_cabang,tgl_tr,keterangan,nominal_masuk,nominal_keluar,id_tr,no_referensi,fk_coa_bank)
		values
		('".$p_bank."','".$p_cabang."','".$today_db." ".date("H:i:s")."','".strtoupper($p_keterangan)."',".$masuk.",".$keluar.",'".$p_id."','".$p_referensi."','".$p_coa."')
		;
	";
	$lquery .= "
		insert into data_fa.tblhistory_bank_log(fk_bank,fk_cabang,tgl_tr,keterangan,nominal_masuk,nominal_keluar,id_tr,no_referensi,fk_coa_bank,log_action_userid,log_action_username,log_action_date,log_action_mode)
		values
		('".$p_bank."','".$p_cabang."','".$today_db." ".date("H:i:s")."','".strtoupper($p_keterangan)."',".$masuk.",".$keluar.",'".$p_id."','".$p_referensi."','".$p_coa."','".$_SESSION['id']."','".$_SESSION['username']."','".date('m/d/Y H:i:s')."','IA')
		;
	";	
	//showquery($lquery);
	

	$is_negatif=get_rec("tblbank","is_negatif","kd_bank='".$p_bank."'");
	$lrow=pg_fetch_array(pg_query("select * from  ".$p_table." where ".$p_where.""));
	
	if($lrow["akhir"]+$akhir<0 && $is_negatif!='t'){
		//$l_success=0;
		//$l_msg="<b>Saldo Bank : ".$p_bank.", Cabang : ".$p_cabang." tidak mencukupi </b><br>";
		//$strmsg.=$l_msg;
	}

	//$l_success=0;
	//echo $strmsg;
	return $lquery;
	
}

function calculate_cadangan_piutang($p_date){
	//pg_query('begin');
	$temp="temp";
	
	$fom=date("Y-m",strtotime($p_date)).'-01';
	$last_month=date("Y-m-d",strtotime('-1 second',strtotime($fom)));
	$bulan=date('m',strtotime($last_month));
	$tahun=date('Y',strtotime($last_month));
	//echo $bulan;
	//echo $tahun;
	
	$query="
		select jenis_produk,sum(booking) as total_pokok, tblinventory.fk_cabang from (
			select sum(pokok_jt)as  booking,fk_sbg as no_sbg from data_fa.tblangsuran
			".where_os_tblangsuran($p_date)."
			group by fk_sbg		
		)as tblar
		inner join tblinventory on fk_sbg = no_sbg
		left join tblproduk on fk_produk=kd_produk
		".where_os_tblinventory($p_date)."
		group by fk_cabang,jenis_produk		
	";
	//showquery($query);
	$lrs=pg_query($query);
	$rate_piutang_ragu=get_rec("tblsetting","rate_piutang_ragu");
	$EOM=$p_date;
	$lquery_bulanan = "delete from data_accounting.tblgl_auto where type_owner = 'PENCADANGAN PIUTANG' and tr_date = '".$EOM."'";
	if(!pg_query($lquery_bulanan))$l_success=0;

	while($lrow=pg_fetch_array($lrs)){
		$fk_cabang=$lrow["fk_cabang"];
		$total_pokok=round($lrow["total_pokok"]*$rate_piutang_ragu/100);
		
		if($lrow["jenis_produk"]=='0')$cadangan_piutang="cadangan_piutang_ragu_ragu";
		elseif($lrow["jenis_produk"]=='1')$cadangan_piutang="cadangan_piutang_tak_tertagih";
		
		$lbalance = get_balance($cadangan_piutang,'',$fk_cabang,$bulan,$tahun)*-1;
		//echo $lbalance;
		if($lbalance>$total_pokok)$total_pokok=0;
		else $total_pokok-=$lbalance;
		//echo $total_pokok;
		
		if($total_pokok>0){

			$arrPost["biaya_cadangan_piutang"]		= array('type'=>'d','value'=>$total_pokok);
			$arrPost[$cadangan_piutang]				= array('type'=>'c','value'=>$total_pokok);
			cek_balance_array_post($arrPost);
			if(!posting('PENCADANGAN PIUTANG',date("Ymd",strtotime($EOM)),$EOM,$arrPost,$fk_cabang,'00'))$l_success=0;	

		}
	}
	
	//pg_query('rollback');
	
}

function change_type_owner($type_owner){
	switch($type_owner){
		case "ALOKASI PEMBAYARAN CICILAN":	
			$l_return='ALOKASI CICILAN';
		break;

		case "BUNGA ANTAR KANTOR (CABANG)":	
			$l_return='BUNGA ANTAR KANTOR';
		break;

		case "BUNGA ANTAR KANTOR (HO)":	
			$l_return='BUNGA ANTAR KANTOR';
		break;

		case "MUTASI BANK CABANG - CABANG":	
			$l_return='MUTASI BANK ANTAR CABANG';
		break;
		
		case "MUTASI BANK HO - CABANG (DROPPING)(CABANG)":	
			$l_return='MUTASI BANK HO - CABANG';
		break;
		
		case "MUTASI BANK HO - CABANG (DROPPING)(HO)":	
			$l_return='MUTASI BANK HO - CABANG';
		break;
		
		case "MUTASI BANK HO - CABANG (REFUND)(CABANG)":	
			$l_return='MUTASI BANK HO - CABANG';
		break;
		
		case "MUTASI BANK HO - CABANG (REFUND)(HO)":	
			$l_return='MUTASI BANK HO - CABANG';
		break;
		
		case "MUTASI BANK INTERNAL CABANG":	
			$l_return='MUTASI BANK INTERNAL';
		break;
		
		case "PENERIMAAN DANA DARI FINTECH":	
			$l_return='PENERIMAAN DARI FINTECH';
		break;
		
		case "PENYELESAIAN ADVANCE EKSTERNAL":	
			$l_return='PENYELESAIAN ADVANCE.EKS';
		break;
		
		case "PENYELESAIAN ADVANCE INTERNAL":	
			$l_return='PENYELESAIAN ADVANCE.INT';
		break;
		
		case "PENYELESAIAN ADVANCE TRANSFER":	
			$l_return='PENYELESAIAN ADVANCE.TRF';
		break;
		
		default :
			$l_return=$type_owner;
		break;
		
		
		
	}
	
	return $l_return;
	
}

function cek_periode_accounting($p_tahun,$p_bulan){
	
	$bulan_accounting = get_rec("tblsetting","bulan_accounting");
	$tahun_accounting = get_rec("tblsetting","tahun_accounting");	
	$tgl_live = get_rec("tblsetting","tgl_live");
	$p_tgl=$p_tahun.'-'.$p_bulan.'-01';
	//echo $p_tahun.$p_bulan. "<".$tahun_accounting.$bulan_accounting;
	if(strtotime($p_tahun.'-'.$p_bulan.'-01') <strtotime($tahun_accounting.'-'.$bulan_accounting.'-01'))return false;
	else if(strtotime($p_tgl) < strtotime($tgl_live))return false;	
	else return true;
}


function gl_balik($fk_owner,$type_owner,$reference_transaksi=NULL){
	//$fk_owner='2101.RKN.0000001';
	//$type_owner='REKON BANK';
	
	if($reference_transaksi != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" reference_transaksi = '".$reference_transaksi."' ";
	}
	
	if ($lwhere!="") $lwhere=" and ".$lwhere;
	
	$query="select * from data_accounting.tblgl_auto
	left join tblcoa on fk_coa_d = coa or fk_coa_c=coa
	left join tbljenis_transaksi on used_for=kd_jenis_transaksi
	where fk_owner = '".$fk_owner."' and type_owner in('".$type_owner."')
	".$lwhere."
	order by no_bukti
	";
	
	$lrs=pg_query($query);
	$query=
	//showquery($query);
	$i=0;
	while($lrow=pg_fetch_array($lrs)){
		$type_owner=$lrow["type_owner"];
		$cabang_coa=$lrow["fk_cabang"];		
		$total=$lrow["total"];
		$reference_transaksi=$lrow["reference_transaksi"];		
		$tr_date=$lrow["tr_date"];		
		if($lrow["fk_coa_d"]!=""){
			$account=$lrow["fk_coa_d"];
			$type='c';
		}
		elseif($lrow["fk_coa_c"]!=""){
			$account=$lrow["fk_coa_c"];
			$type='d';
		}
		
		$arrPost[$account.$i]		= array('type'=>$type,'value'=>$total,'account'=>$account,'cabang_coa'=>$cabang_coa,'reference'=>$reference_transaksi,'tr_date'=>$tr_date);		
		$i++;
		
	}	
	return $arrPost;
}
?>