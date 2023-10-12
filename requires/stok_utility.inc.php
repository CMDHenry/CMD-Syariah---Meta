<?
//bikin kartu stok
function create_kartu_stok($item,$type,$m,$y,$stok_array,$p_gudang = ''){
	
	if($type=='part')$table='data_part.tblstock_part';
	if($type=='aksesoris')$table='data_unit.tblstock_aksesoris';
	
	if($p_gudang == '')$p_gudang = $_SESSION['kd_cabang'];
	$pattern = '/^on_|hpp_terakhir|on_hand|intransit|booking|stock_min|stock_max|lokasi|lokasi_2/';
	if (is_array($stok_array)){
		foreach($stok_array as $lindex => $ldata){
			if(preg_match($pattern,$lindex)){
				$lfield .= ",".$lindex;
				$lvalue .= ",".(($ldata)?"'".$ldata."'":"0")."";
			}
		}
	}
	//showquery( "select * from ".$table." where fk_".$type."='".convert_sql($item)."' and bulan='".$m."' and tahun='".$y."'");
	if(!pg_num_rows(pg_query("select * from ".$table." where fk_".$type."='".convert_sql($item)."' and bulan='".$m."' and tahun='".$y."' and fk_gudang='".$p_gudang."'"))){//cek bener gak biar gak duplicate
		$lquery = "
			insert into ".$table." (
				fk_".$type.",bulan,tahun,fk_gudang
				".$lfield."
			)values(
				'".convert_sql($item)."',
				'".$m."','".$y."','".$p_gudang."'
				".$lvalue."
			);
			insert into ".$table."_log (
				log_action_userid,log_action_username,log_action_date,
				log_action_mode,log_action_from,
				fk_".$type.",bulan,tahun,fk_gudang
				".$lfield."
			)values(
				'".$_SESSION['id']."','".$_SESSION['username']."','".date('Y-m-d H:i:s')."',
				'IA','".$_SERVER['SCRIPT_NAME']."',
				'".convert_sql($item)."',
				'".$m."','".$y."','".$p_gudang."'
				".$lvalue."
			);
		";			
		//showquery($lquery);

		if(!pg_query($lquery)){
			//showquery($lquery);
			echo "error saat membuat kartu stok baru untuk : ".$item."".$gudang;
			return false;
		}else return true;
	}else return true;
}

//auto create kartu stok kalau ternyata stok baru atau belum di ada kartu stok di bulan berjalan
function cek_stok($p_item,$p_type,$create_new = true,$p_tanggal='',$p_gudang = ''){
	if($p_type=='part')$table='data_part.tblstock_part';
	if($p_type=='aksesoris')$table='data_unit.tblstock_aksesoris';
	$m = date('n',strtotime($p_tanggal));
	$y = date('Y',strtotime($p_tanggal));
	$p_type = strtolower($p_type);
	if($p_type=='parts')$p_type='part'; //jaga2 klo ada yg lupa
	if($p_gudang == '')$p_gudang = $_SESSION['kd_cabang'];
	
	$lquery="
		select * from ".$table." 
		where fk_".$p_type." = '".convert_sql($p_item)."' and fk_gudang = '".$p_gudang."'
	";
	//showquery($lquery);
	//kalau gak ada lsg bikin baru
	$lrow_stok_bulan_lalu = array();
	if(pg_num_rows(pg_query($lquery)) <= 0){//kalau dy stok baru
		if($create_new){
			//echo 'Stok bulan sebelum tidak ada';
			$flag=false;
			$ltemp = get_stok_workshop($p_item,$p_type,$p_tanggal,$p_gudang);
			//print_r($ltemp);
			$lrow_stok_bulan_lalu['hpp_terakhir'] = $ltemp['hpp_terakhir'];
			//ngecek kartu stok utk gudang sudah di create blum
			//showquery("select * from ".$table." where fk_".$p_type." = '".convert_sql($p_item)."' and fk_gudang = '".$p_gudang."'");
			
			if(pg_num_rows(pg_query("select * from ".$table." where fk_".$p_type." = '".convert_sql($p_item)."' and fk_gudang = '".$p_gudang."'")) <= 0)$flag = create_kartu_stok($p_item,$p_type,$m,$y,$lrow_stok_bulan_lalu,$p_gudang);
			
			return $flag;
		}else return false;
	}elseif(pg_num_rows(pg_query($lquery." and bulan = '".$m."' and tahun = '".$y."'")) <= 0 ){//kalau dy di bulan ini blum ada
		if($create_new){
			$flag=false;
			$lastMonth = $m-1;
			if($lastMonth<=0){
				$lastMonth=12;
				$lastYear=$y-1;
			}else $lastYear=$y;
			//echo 'Stok bulan sebelum ada';
			if(cek_stok($p_item,$p_type,true,$lastMonth.'/01/'.$lastYear,$p_gudang)){
				$lrow_stok_bulan_lalu = pg_fetch_array(pg_query("select * from ".$table." where fk_".$p_type." = '".convert_sql($p_item)."'  and bulan = '".$lastMonth."' and tahun = '".$lastYear."' and fk_gudang = '".$p_gudang."'"));
				$flag = create_kartu_stok($p_item,$p_type,$m,$y,$lrow_stok_bulan_lalu,$p_gudang);
			}

			return $flag;
		}else return false;
	}else return true;

}

//nyari gudang setiap cabang, klo gak ada ya error dah
function cek_gudang(){
	$lquery_gudang = "select * from tblgudang_part --where fk_cabang = '".$_SESSION["kd_cabang"]."' and fk_jenis_cabang = 'W'";

	$l_resource_gudang = pg_query($lquery_gudang);

	if(pg_num_rows($l_resource_gudang) <= 0){// kalau belum disetting gudangnya
		exit('
			<body bgcolor="#f3f3f3">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
				<tr height="37">
					<td width="20"></td>
					<td colspan="2"></td>
				</tr>
				<tr>
					<td width="20"></td>
					<td valign="top"><font color="red" style="font-size:12px">Gudang untuk '.$_SESSION["nm_cabang"].' belum di-setting.<br>Silahkan Hubungi Administrator Anda.</font></td>
					<td width="20"></td>
				</tr>
			</table>
			</body>
		');
	}else return true;
}

//nyari hpp nota kontan
/*function get_hpp_nota_kontan($p_nota,$p_type){
	$lquery = "
		select sum(hpp_".$p_type." * qty) as hpp from data_part.tblinvoice_customer_detail
		inner join data_part.tblinvoice_customer on fk_invoice_customer = no_invoice_customer
		where no_invoice_customer = '".convert_sql($p_nota)."'
	";
	$lrow_hpp = pg_fetch_array(pg_query($lquery));
	if(!$lrow_hpp['hpp'])return 0;
	else return $lrow_hpp['hpp'];
}
*/
function get_hpp($p_item,$p_type,$p_tanggal="",$p_gudang=""){
	//p_item=part,bahan,aksesoris,
	//p_type=kd_part,kd_bahan,kd_aksesoris
	
	if($p_type=='part')$table='data_part.tblstock_part';
	if($p_type=='aksesoris')$table='data_unit.tblstock_aksesoris';

	
	$p_type = strtolower($p_type);
	if($p_type=='parts')$p_type='part'; //jaga2 klo ada yg lupa
	//if($p_gudang=="")$p_gudang = $_SESSION['kd_cabang'];

	$m = date('n',strtotime($p_tanggal));
	$y = date('Y',strtotime($p_tanggal));
	
	$l_resource = pg_query("
					select 
						sum(qty_on_hand) as total_stok,
						sum(on_sales) as total_sales,
						sum(on_koreksi_sales) as total_koreksi_sales,
						--sum(on_iris_out) as total_iris_out,
						--sum(on_demand) as total_demand,
						--sum(terima) as total_terima,
						--sum(keluar) as total_keluar,
						--sum(koreksi_in) as total_koreksi_in,
						--sum(koreksi_out) as total_koreksi_out,
						hpp_terakhir
					from ".$table."
					where fk_".$p_type." = '".$p_item."' and bulan = '".$m."' and tahun = '".$y."' and fk_gudang = '".$p_gudang."'
					group by fk_".$p_type.", bulan = '".$m."', tahun = '".$y."', hpp_terakhir,fk_gudang = '".$p_gudang."'
				 ");
	if(pg_num_rows($l_resource)){
		$lreturn = pg_fetch_array($l_resource);
	}else{
		$lreturn[total_stok] 		= 0;
		$lreturn[total_sales] 		= 0;
		$lreturn[total_koreksi_sales] 	= 0;
		/*$lreturn[total_iris_out] 	= 0;
		$lreturn[total_demand] 		= 0;
		$lreturn[total_terima] 		= 0;
		$lreturn[total_keluar] 		= 0;
		$lreturn[total_koreksi_in] 	= 0;
		$lreturn[total_koreksi_out] = 0;
*/		$lreturn[hpp_terakhir] 		= 0;
	}
	
	return $lreturn;
	
}

//nyari stok akhir di table stok
function get_stok_workshop($p_item,$p_type,$p_tanggal="",$p_gudang="",$p_temp=""){
	//p_tanggal => tanggal transaksi
	//return array(on_hand=>0,on_order=>1,on_iris_in=>0,on_iris_out=>1)
	if($p_type=='part')$table='data_part.tblstock_part';
	if($p_type=='aksesoris')$table='data_unit.tblstock_aksesoris';
	
	$p_type = strtolower($p_type);
	if($p_type=='parts')$p_type='part'; //jaga2 klo ada yg lupa

	if($p_gudang=="")$p_gudang = $_SESSION['kd_cabang'];

	$m = date('n',strtotime($p_tanggal));
	$y = date('Y',strtotime($p_tanggal));

	if($p_type=='part')$qty_db="qty_on_hand,qty_booking,";
	if($p_type=='aksesoris')$qty_db="qty_on_hand,";

	$lquery="
		select 
			".$qty_db."
			hpp_terakhir
		from ".$table." 
		where fk_".$p_type." = '".convert_sql($p_item)."'
		and fk_gudang = '".$p_gudang."'
		and bulan = '".$m."' and tahun = '".$y."'
	";

	$l_resource = pg_query($lquery);
	//showquery($lquery);
	if(pg_num_rows($l_resource) <= 0){
		$lreturn = array(qty_on_hand=>0, hpp_terakhir=>0,qty_booking=>0);
		//$lreturn = create_kartu_stok_bulan_berjalan($p_item,$p_type,$p_tanggal,$p_gudang);
	}else{
		$lreturn = pg_fetch_array($l_resource);
	}

	return $lreturn;

}

//update tblstok
function update_stok_workshop($p_item,$p_type,$p_arr_stok,$p_tanggal="",$p_gudang=""){ 
	//buat arr atok nya, dibikin array jadi bisa update bnyk => array(on_hand=>0,on_order=>1,on_iris_in=>0,on_iris_out=>1)
	//p_tanggal => tanggal 
	if($p_type=='part')$table='data_part.tblstock_part';
	if($p_type=='aksesoris')$table='data_unit.tblstock_aksesoris';
	
	$p_type = strtolower($p_type);
	if($p_type=='parts')$p_type='part'; //jaga2 klo ada yg lupa

	$m = date('n',strtotime($p_tanggal));
	$y = date('Y',strtotime($p_tanggal));
	
	if($p_gudang=="")$p_gudang=$_SESSION['kd_cabang'];

	//klo di bulan depan ada maka update
	//ambil bulan depan
	$m_next = $m+1;
	if($m_next>=13){
		$m_next=1;
		$y_next = $tahun+1;
	}else $y_next = $y;
	//kalau di bulan depan masih ada di update juga
	//showquery("select * from ".$table." where fk_".$p_type." = '".convert_sql($p_item)."' and bulan = '".$m_next."' and tahun = '".$y_next."'");
	if(pg_num_rows(pg_query("select * from ".$table." where fk_".$p_type." = '".convert_sql($p_item)."' and bulan = '".$m_next."' and tahun = '".$y_next."' ;"))){
		if(!update_stok_workshop($p_item,$p_type,$p_arr_stok,$m_next.'/01/'.$y_next,$p_gudang))return false;
	}
	//end update next month

	$lquery_stok = "update ".$table." set ";
	//print_r($p_arr_stok);
	foreach($p_arr_stok as $l_type => $l_qty){
		if($l_type!='hpp_terakhir')$lquery_stok.=" ".$l_type." = ".$l_type." + ".$l_qty.",";
		else $lquery_stok.=" ".$l_type." = ".$l_qty.",";
	}
	//showquery($lquery_stok);
	$lquery_stok = substr($lquery_stok,0,(strlen($lquery_stok)-1));
	$lquery_stok .= " where fk_".$p_type." = '".convert_sql($p_item)."' and bulan = '".$m."' and tahun = '".$y."' and fk_gudang='".$p_gudang."';";
	
	//UB log
	$lquery="insert into ".$table."_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB','".$_SERVER['PHP_SELF']."' from ".$table." where fk_".$p_type." = '".convert_sql($p_item)."' and bulan = '".$m."' and tahun = '".$y."' and fk_gudang='".$p_gudang."' ;";
	//end log
	
	//query stok
	$lquery .= $lquery_stok;
	//showquery($lquery);
	//UA log
	$lquery.="insert into ".$table."_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA','".$_SERVER['PHP_SELF']."' from ".$table." where fk_".$p_type." = '".convert_sql($p_item)."' and bulan = '".$m."' and tahun = '".$y."' and fk_gudang='".$p_gudang."';";
	//end log
	//echo $lquery;
	if(pg_query($lquery)){
		//===tambahan utk cek stok klo minus lsg return false
		//$p_item,$p_type,$p_tanggal="",$p_gudang="",$p_gudang=""
		$l_arr_stok = get_stok_workshop($p_item,$p_type,$p_tanggal,$p_gudang,$p_gudang);
		if($l_arr_stok['qty_on_hand'] == 0){// update hpp jadi 0 klo stok nya 0
			//echo "update data_part.tblstock_".$p_type." set hpp_terakhir = 0 "."where fk_".$p_type." = '".convert_sql($p_item)."' and bulan = '".$m."' and tahun = '".$y."' and fk_gudang='".$p_gudang."';";
			if(!pg_query("update ".$table." set hpp_terakhir = 0 "."where fk_".$p_type." = '".convert_sql($p_item)."' and bulan = '".$m."' and tahun = '".$y."' and fk_gudang='".$p_gudang."';"))return 0; 
			
			
			else return 1;
			
		}else return 1;
		
		if($l_arr_stok['qty_on_hand'] < 0){// update hpp jadi 0 klo stok nya 0
			return 0;
		}
		//---end
		return 1;
	}else{
		return 0;
	}
	
}

//bikin stok gudang/batch===================================
function cek_stok_gudang($p_type_gudang='gudang',$p_item,$p_type,$p_gudang,$p_tanggal='',$p_gudang=''){
	global $counter; //p_type_gudang bisa diganti dg batch atau gudang
	if($p_gudang=='')$p_gudang=$_SESSION['kd_cabang'];
	$bulan = date('n',strtotime($p_tanggal));
	$tahun = date('Y',strtotime($p_tanggal));

	$counter++;
	if($counter >= 1000){echo 'overloop';break;}
	
	//klo record baru
	if(pg_num_rows(pg_query("select * from ".$table."_".$p_type_gudang." where fk_".$p_type." = '".convert_sql($p_item)."'  and fk_".$p_type_gudang." = '".$p_gudang."' " )) <= 0){

		$p_eom = date("m/d/Y", strtotime('-1 second',strtotime('+1 month',strtotime(date('m',strtotime($p_tanggal)).'/01/'.date('Y',strtotime($p_tanggal))))));
		$eom = date("m/d/Y", strtotime('-1 second',strtotime('+1 month',strtotime(date('m').'/01/'.date('Y')))));
		
		while(strtotime($p_eom) <= strtotime($eom)){
		
			$lquery = "
				insert into ".$table."_".$p_type_gudang." (
					fk_".$p_type.", fk_".$p_type_gudang.", bulan, tahun, on_hand
				)values(
					'".$p_item."','".$p_gudang."',
					'".$bulan."','".$tahun."',0
				);
				insert into ".$table."_".$p_type_gudang."_log (
					fk_".$p_type.",  fk_".$p_type_gudang.", bulan, tahun, on_hand,
					log_action_userid,log_action_username,
					log_action_date,log_action_mode,log_action_from
				)values(
					'".$p_item."','".$p_gudang."',
					'".$bulan."','".$tahun."',0,
					'".$_SESSION['id']."','".$_SESSION['username']."',
					'".date('m/d/Y H:i:s')."','IA','".$_SERVER['PHP_SELF']."'
				);
			";
			
			$bulan += 1;
			if($bulan > 12){$tahun+=1;$bulan=1;}
			$p_eom = $bulan."/01/".$tahun;

			//showquery($lquery);
			if(!pg_query($lquery))$lreturn = 0;
			else $lreturn = 1;

		}
		return $lreturn;

	}else{//klo ada record
		
		//cek klo saldo ar utk bulan/tahun bersangkutan sudah ada atau blum klo blum dicari ke bulan/tahun lalu
		if(pg_num_rows(pg_query("select * from ".$table."_".$p_type_gudang." where fk_".$p_type." = '".convert_sql($p_item)."' and fk_".$p_type_gudang." = '".$p_gudang."' and bulan = '".$bulan."' and tahun = '".$tahun."'")) <= 0){
			
			//ambil bulan/tahun lalu
			$bulan_saldo = $bulan-1;
			if($bulan_saldo<=0){
				$bulan_saldo=12;
				$tahun_saldo = $tahun-1;
			}else $tahun_saldo = $tahun;
		
			//ambil saldo ar bulan/tahun lalu utk create bulan skrg
			if(pg_num_rows(pg_query("select * from ".$table."_".$p_type_gudang." where fk_".$p_type." = '".convert_sql($p_item)."' and fk_".$p_type_gudang." = '".$p_gudang."' and bulan = '".$bulan_saldo."' and tahun = '".$tahun_saldo."'")) <= 0){
				if(!cek_stok_gudang($p_type_gudang,$p_item,$p_type,$p_gudang,$p_tanggal,$p_gudang))return false;
			}
			
			//klo ketemu maka di insert
			$lrow = pg_fetch_array(pg_query("select * from ".$table."_".$p_type_gudang." where fk_".$p_type." = '".convert_sql($p_item)."'  and fk_".$p_type_gudang." = '".$p_gudang."' and bulan = '".$bulan_saldo."' and tahun = '".$tahun_saldo."'"));
			$lquery = "
				insert into ".$table."_".$p_type_gudang." (
					fk_".$p_type.", fk_".$p_type_gudang.", bulan, tahun, on_hand
				)values(
					'".$p_item."','".$p_gudang."',
					'".$bulan."','".$tahun."','".$lrow['on_hand']."'
				);
				insert into ".$table."_".$p_type_gudang."_log (
					fk_".$p_type.", fk_".$p_type_gudang.", bulan, tahun, on_hand,
					log_action_userid,log_action_username,
					log_action_date,log_action_mode,log_action_from
				)values(
					'".$p_item."','".$p_gudang."',
					'".$bulan."','".$tahun."','".$lrow['on_hand']."',
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

//update rack/gudang/batch
function update_stok_gudang($p_type_gudang='gudang',$p_item,$p_type,$p_arr_stok,$p_gudang,$p_tanggal="",$p_gudang=""){ 
	//buat arr atok nya, dibikin array jadi bisa update bnyk => array(on_hand=>0,on_order=>1,on_iris_in=>0,on_iris_out=>1)
	//p_tanggal => tanggal 
	//p_type_gudang bisa diganti dg batch atau gudang

	$p_type = strtolower($p_type);
	if($p_type=='parts')$p_type='part'; //jaga2 klo ada yg lupa

	$m = date('n',strtotime($p_tanggal));
	$y = date('Y',strtotime($p_tanggal));
	
	if($p_gudang=="")$p_gudang=$_SESSION['kd_cabang'];

	if(pg_num_rows(pg_query("select * from ".$table."_".$p_type_gudang." where fk_".$p_type." = '".convert_sql($p_item)."' and bulan = '".$m."' and tahun = '".$y."' 
	 and fk_".$p_type_gudang." = '".$p_gudang."'")) <= 0)cek_stok_gudang($p_type_gudang,$p_item,$p_type,$p_gudang,$p_tanggal,$p_gudang);

	//klo di bulan depan ada maka update
	//ambil bulan depan
	$m_next = $m+1;
	if($m_next>=13){
		$m_next=1;
		$y_next = $tahun+1;
	}else $y_next = $y;
	//kalau di bulan depan masih ada di update juga
	if(pg_num_rows(pg_query("select * from ".$table."_".$p_type_gudang." where fk_".$p_type." = '".convert_sql($p_item)."' and bulan = '".$m_next."' and tahun = '".$y_next."' and fk_".$p_type_gudang." = '".$p_gudang."';"))){
		if(!update_stok_gudang($p_type_gudang,$p_item,$p_type,$p_arr_stok,$p_gudang,$m_next.'/01/'.$y_next,$p_gudang,$p_gudang))return false;
	}
	//end update next month

	$lquery_stok = "update ".$table."_".$p_type_gudang." set ";
	
	foreach($p_arr_stok as $l_type => $l_qty){
		if($l_type!='hpp_terakhir')$lquery_stok.=" ".$l_type." = ".$l_type." + ".$l_qty.",";
		else $lquery_stok.=" ".$l_type." = ".$l_qty.",";
	}
	
	$lquery_stok = substr($lquery_stok,0,(strlen($lquery_stok)-1));
	
	$lquery_stok .= " where fk_".$p_type." = '".convert_sql($p_item)."' and bulan = '".$m."' and tahun = '".$y."'  and fk_".$p_type_gudang." = '".$p_gudang."';";
	
	//UB log
	$lquery="
		insert into ".$table."_".$p_type_gudang."_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB','".$_SERVER['PHP_SELF']."' from ".$table."_".$p_type_gudang." where fk_".$p_type." = '".convert_sql($p_item)."' and bulan = '".$m."' and tahun = '".$y."'  and fk_".$p_type_gudang." = '".$p_gudang."';
	";
	//end log
	
	//query stok
	$lquery .= $lquery_stok;
	
	//UA log
	$lquery.="
		insert into ".$table."_".$p_type_gudang."_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA','".$_SERVER['PHP_SELF']."' from ".$table."_".$p_type_gudang." where fk_".$p_type." = '".convert_sql($p_item)."' and bulan = '".$m."' and tahun = '".$y."'  and fk_".$p_type_gudang." = '".$p_gudang."';
	";
	//end log

	//showquery($lquery);
	if(pg_query($lquery)){
		return 1;
	}else{
		return 0;
	}
	
}

//ngitung HPP
function hitung_hpp($p_item,$p_type,$p_arr_stok,$p_tanggal=NULL,$p_gudang){
	//p_arr_stok isinya qty serta harga beli nya, cth array(qty=>10,harga_beli=>95000,total_harga=>950000)
	if(!$p_tanggal)$p_tanggal=date('m/d/Y H:i:s');
	//if(!$p_gudang)$p_gudang=$_SESSION['kd_cabang'];
	
	if(!isset($p_arr_stok)){
		return false;
	}
	//print_r($p_arr_stok);
	if($p_arr_stok[total_harga]!=""){
		
		$l_arr = get_hpp($p_item,$p_type,$p_tanggal,$p_gudang);
		
		$head = ( round($l_arr[hpp_terakhir] * $l_arr[total_stok]) + $p_arr_stok[total_harga] );
		
		$tail = ($l_arr[total_stok] + $p_arr_stok[qty]);
		
	} else {

		$l_arr = get_hpp($p_item,$p_type,$p_tanggal,$p_gudang);
		
		$head = ( round($l_arr[hpp_terakhir] * $l_arr[total_stok]) + ($p_arr_stok[qty] * $p_arr_stok[harga_beli]) );
		
		$tail = ($l_arr[total_stok] + $p_arr_stok[qty]);

	}
	//echo $head."-".$tail.'<br>';
	if($head <= 0 || $tail <= 0) $hpp = 0;
	else $hpp =  $head / $tail ;

	return round($hpp,2);
	
}


function hitung_hpp_sparepart($p_item,$p_arr_stok,$p_tanggal='',$p_cabang='',$p_gudang=''){
	if($p_cabang=='')$p_cabang=$_SESSION['kd_cabang'];
	//	if($p_gudang=='')$p_gudang = get_gudang_sparepart($p_cabang);
	//echo $p_gudang;
	return hitung_hpp($p_item,'part',$p_arr_stok,$p_tanggal,$p_gudang,'data_sparepart');
}



//FUNGSI KHUSUS GADAI

function get_lokasi_storing($p_cabang){
	$lquery="
			select * from tbllajur 
			left join tbllaci on fk_laci=kd_laci
			left join tblbrankas on fk_brankas=kd_brankas
			where fk_cabang='".$p_cabang."' and lajur_active is true and laci_active is true and brankas_active is true
			order by qty_on_hand asc,kd_brankas,nm_laci asc,nm_lajur asc";
	$lrs=pg_query($lquery);
	//showquery($lquery);
	if(!pg_num_rows(pg_query($lquery)))return false;
	$lrow=pg_fetch_array($lrs);
	return $lrow["kd_lajur"];
}



function storing($p_sbg,$p_lajur=NULL,$p_status=NULL,$p_keterangan=NULL,$p_tgl=today_db,$is_new=NULL,$p_cabang=NULL,$p_cust=NULL,$p_produk=NULL,$p_jt=NULL){
	global $strmsg,$l_success;
	
/*	p_status						: status inventory ex : terima, belum terima
	p_keterangan					: asal menu ex : pelunasan, penerimaan
	is_new,p_cabang,p_cust			: untuk brg baru yang belum ada di inventory 
*/	
	//$p_lajur_lama=NULL,
	$l_success=1;
	$p_table="tblinventory";
	$p_where=" fk_sbg='".$p_sbg."' ";	
	
	if(!$is_new){
		$update.=($p_lajur?"fk_lajur='".$p_lajur."',":"");
		$update.=($p_status?"status='".$p_status."',":"");
	
		$update.=($p_status=="Lunas"?"status_sbg='Exp',tgl_lunas='".$p_tgl."',":"");
		$update.=($p_status=="Batal"?"status_sbg='Exp',tgl_cair=NULL,":"");
		
		$update.=($p_keterangan=="Jual"?"status_sbg='Exp',tgl_lunas='".$p_tgl."',":"");// untuk jual cash/credit dll
		
		$update.=($p_keterangan=='Batal Pelunasan'||$p_keterangan=='Batal Jual'?"status_sbg='Liv',tgl_lunas=NULL":"");
		$update=rtrim($update,',');
		
		$lquery.=insert_log($p_table,$p_where,'UB');
		
		$lquery = "
			update ".$p_table." set 
			".$update."
			where ".$p_where." ;
		";
		
		$lquery.=insert_log($p_table,$p_where,'UA');

	}else{
		$lquery = "
			insert into ".$p_table."
			(fk_sbg,fk_cabang,fk_cif,status,fk_produk,tgl_jt,status_sbg,tgl_cair)
			values 
			('".$p_sbg."',".($p_cabang?"'".($p_cabang)."'":"NULL").",".($p_cust?"'".($p_cust)."'":"NULL").",".($p_status?"'".($p_status)."'":"NULL").",".($p_produk?"'".($p_produk)."'":"NULL").",".($p_jt?"'".($p_jt)."'":"NULL").",'Liv','".$p_tgl."')
			;";		
		$lquery.=insert_log($p_table,$p_where,'IA');
			
	}

	
	$lquery.="			
			insert into data_gadai.tblhistory_penyimpanan
			(fk_sbg,fk_lajur,status,keterangan,tgl_transaksi)
			values 
			('".$p_sbg."',".($p_lajur?"'".($p_lajur)."'":"NULL").",".($p_status?"'".($p_status)."'":"NULL").",".($p_keterangan?"'".($p_keterangan)."'":"NULL").",'#".today_db." ".date("H:i:s")."#')
	";
	
	//showquery($lquery);
	return $lquery;

	
	/*		
	$array=array('Penerimaan', 'Pengeluaran', 'Batal Pencairan','Batal Pelunasan','Lelang','Batal Lelang');
	$array_plus=array('Penerimaan', 'Batal Pelunasan','Batal Lelang');
	$array_minus=array('Pengeluaran', 'Lelang', 'Batal Pencairan');
	//echo $p_keterangan;
	if (in_array($p_keterangan, $array)){
		if (in_array($p_keterangan, $array_plus)){
			$nilai=1;
		}else if (in_array($p_keterangan, $array_minus)){
			$nilai=-1;
		}
		//$lquery.=insert_log("tbllajur","kd_lajur='".$p_lajur."'",'UB');

		$lquery .= "
		update tbllajur set 
		qty_on_hand=qty_on_hand+(".$nilai.")
		where kd_lajur='".$p_lajur."';
		";
		
		//$lquery.=insert_log("tbllajur","kd_lajur='".$p_lajur."'",'UA');
	}
	
	if($p_status=='Terima' && $p_lajur==NULL){
		$l_success=0;
		$l_msg="<b>Lajur tidak ada untuk penerimaan</b><br>";
		$strmsg=$l_msg;
	}

	if($p_lajur_lama){
		
		//$lquery.=insert_log("tbllajur","kd_lajur='".$p_lajur."'",'UB');
		$nilai=1;
		$lquery .= "
		update tbllajur set 
		qty_on_hand=qty_on_hand+(".$nilai.")
		where kd_lajur='".$p_lajur."';
		";
		//$lquery.=insert_log("tbllajur","kd_lajur='".$p_lajur."'",'UA');
		
		//$lquery.=insert_log("tbllajur","kd_lajur='".$p_lajur_lama."'",'UB');
		$nilai=-1;
		$lquery .= "
		update tbllajur set 
		qty_on_hand=qty_on_hand+(".$nilai.")
		where kd_lajur='".$p_lajur_lama."';
		";
		//$lquery.=insert_log("tbllajur","kd_lajur='".$p_lajur_lama."'",'UA');
	}	
	
	
	if($p_lajur_lama){
		$lrs=pg_query("select * from tbllajur where kd_lajur='".$p_lajur_lama."'");
		$lrow=pg_fetch_array($lrs);
		if($lrow["qty_on_hand"]<0){
			$l_success=0;
			$l_msg="<b>Onhand lajur ".$p_lajur_lama." lebih kecil dari 0</b><br>";
			$strmsg=$l_msg;
		}
	}elseif($p_lajur){
		$lrs=pg_query("select * from tbllajur where kd_lajur='".$p_lajur."'");
		$lrow=pg_fetch_array($lrs);
		if($lrow["qty_on_hand"]<0){
			$l_success=0;
			$l_msg="<b>Onhand lajur ".$p_lajur." lebih kecil dari 0</b><br>";
			$strmsg=$l_msg;
		}
	}*/
	//$l_success=0;
}


function recount_onhand($p_brankas=NULL){
	if($p_brankas)$lwhere="and fk_brankas='".$p_brankas."'";
	$lquery="
		select count(fk_lajur)as qty_on_hand,fk_lajur from tblinventory 
		left join tbllajur on fk_lajur= kd_lajur
		left join tbllaci on fk_laci = kd_laci
		where status in ('Terima','Pengajuan Pelunasan','Diserahkan')
		".$lwhere."
		group by fk_lajur	
	";
	$lrs=pg_query($lquery);
	//showquery($lquery);
	$p_table="tbllajur";	
	while($lrow=pg_fetch_array($lrs)){
		$p_where=" kd_lajur='".$lrow["fk_lajur"]."'";		
		$lupdate .=insert_log($p_table,$p_where,'UB');		
		$lupdate .= "
		update ".$p_table." set 
		qty_on_hand=".($lrow["qty_on_hand"])."
		where ".$p_where.";
		";
		$lupdate .=insert_log($p_table,$p_where,'UA');
	}		
	
	$lupdate .="
	update tbllajur set qty_on_hand=0 
	where qty_on_hand<0 and kd_lajur not in(select fk_lajur from tblinventory where status_sbg='Liv' and fk_lajur is not null)
	";
	
	//showquery($lupdate);
	if($lupdate){
		if(!pg_query($lupdate))return false;
		else return true;
	}else return true;
}

?>
