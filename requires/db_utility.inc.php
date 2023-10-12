<?
function generate_log($p_table,$p_id,$p_status,$p_field_pk,$p_status_detail='f'){
	$l_success=1;
	if($p_status_detail=='f'){
		if (!pg_query("insert into ".$p_table."_log (select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','".$p_status."' from ".$p_table." where ".$p_field_pk."='".$p_id."')")) $l_success=0;
		//=showquery("insert into ".$p_table."_log (select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','".$p_status."' from ".$p_table." where ".$p_field_pk."='".$p_id."')");
	}
	//echo $p_status_detail;
	
	if($p_status_detail=='t'){
		$l_id_log=get_last_id($p_table."_log","pk_id_log");

		$lrs_detail=pg_query("select * from skeleton.tbldb_table where parent_table='".$p_table."'");
		//showquery("select * from skeleton.tbldb_table where parent_table='".$p_table."'");
		while ($lrow_detail=pg_fetch_array($lrs_detail)) {
			$p_fk_detail= get_rec("skeleton.tbldb_table_detail","kd_field","is_foreign_key is true and foreign_table='".$p_table."' and foreign_field='".$p_field_pk."'  and fk_db_table='".$lrow_detail["kd_table"]."'");
			//echo $fk_detail;
			if (!pg_query("insert into ".$lrow_detail["kd_table"]."_log select *,'".$l_id_log."' from ".$lrow_detail["kd_table"]." where ".$p_fk_detail."='".$p_id."'")) $l_success=0;
			//showquery("insert into ".$lrow_detail["kd_table"]."_log select *,'".$l_id_log."' from ".$lrow_detail["kd_table"]." where ".$p_fk_detail."='".$p_id."'");
			//if (!pg_query("insert into ".$lrow_detail["kd_table"]."_log select *,'".$l_id_log."' from ".$lrow_detail["kd_table"]." where ".get_rec("skeleton.tbldb_table_detail","kd_field","is_foreign_key is true and foreign_table='".$p_table."' and foreign_field='".$p_field_pk."'  and fk_db_table='".$lrow_detail["kd_table"]."'")."='".$p_id."'")) $l_success=0;
		}
	}
	return $l_success;
}
function insert_log($p_table,$p_where,$p_status,$p_detail='f'){
	$lquery="insert into ".$p_table."_log (select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','".$p_status."' from ".$p_table." where ".$p_where.");";	
	return $lquery;

}
function fullescape($pstr){
	$lreturn = '';
	for ($i=0;$i<strlen($pstr);$i++){
		$hex = dechex(ord($pstr[$i]));
		if ($hex=='')
			$lreturn = $lreturn.urlencode($pstr[$i]);
		else
			$lreturn = $lreturn .'%'.((strlen($hex)==1) ? ('0'.strtoupper($hex)):(strtoupper($hex)));
	}
	$lreturn = str_replace('+','%20',$lreturn);
	$lreturn = str_replace('_','%5F',$lreturn);
	$lreturn = str_replace('.','%2E',$lreturn);
	$lreturn = str_replace('-','%2D',$lreturn);
	return $lreturn;
}

function get_data_menu($p_id){
	global $kd_menu,$nm_menu,$detail,$session_schema,$generate_get,$kd_module,$strmsg,$j_action;
	if ($lrow=pg_fetch_array(pg_query("select kd_menu,nama_menu,skeleton.tblmodule.pk_id as pk_id_module,is_detail,is_generate_get from skeleton.tblmodule inner join skeleton.tblmenu on skeleton.tblmenu.pk_id=fk_menu where skeleton.tblmenu.pk_id='".$p_id."'"))){
		$kd_menu=$lrow["kd_menu"];
		$nm_menu=$lrow["nama_menu"];
		$kd_module=$lrow["pk_id_module"];
		$detail=$lrow["is_detail"];
		//$session_schema=$lrow["is_session_schema"];
		$generate_get=$lrow["is_generate_get"];
	} else {
		$kd_module="";
		$nm_module="";
		$detail="f";
		$session_schema="f";
		$generate_get="f";
	}
	if($nm_menu=='Open Cashier'){
		$query_barang_belum_simpan="select * from tblinventory where status in('Belum Terima','Pengajuan Pelunasan') and fk_cabang='".$_SESSION["kd_cabang"]."'";
		//showquery("select * from tblinventory where status in('Belum Terima') and fk_cabang='".$_SESSION["kd_cabang"]."'");			
		$lrs_barang_belum_simpan=pg_query($query_barang_belum_simpan);
		while($lrow=pg_fetch_array($lrs_barang_belum_simpan)){
			echo '<font size="2"><b>' .$lrow["fk_sbg"].' belum distoring atau belum diserahkan <b><br>';
		}
	}

	if($_SESSION["jenis_user"]!='HO'&&$_SESSION["jenis_user"]!='Wilayah' &&$_SESSION["username"]!='superuser'){
		$array_menu=array('121110','121119','131011');
		if (!in_array($kd_menu,$array_menu)) {
			if($_SESSION["kd_cabang"]){
				$status_kasir=get_rec("tblcabang","status_kasir","kd_cabang='".$_SESSION["kd_cabang"]."'");
				if($status_kasir!='Open'){
					$strmsg.="Cabang belum di-open.<br>";
					$j_action="lInputClose=getObjInputClose();lInputClose.close();";
				}
			}			
			
			if($strmsg)$strmsg="Error :<br>".$strmsg;
		}
	}

}


function kd_menu($nama_menu){	
	global $module;
	return get_rec("skeleton.tblmenu","kd_menu","fk_parent='".$module."' and nama_menu='".$nama_menu."'");
}

function get_rec($ptable,$pdata,$pexpression="",$poderby=""){
	$lsql = "select ".$pdata." from ".$ptable;
	if ($pexpression!=""){
		$lsql .= " where ".$pexpression;
	}
	if ($poderby!=""){
		$lsql .= " order by ".$poderby;
	}
	
	$parray=$pdata;
	$parray=explode('.',$pdata);
	if(count($parray)>1)$pdata=$parray[1];
	//echo($lsql);
	
	if ($lrow=pg_fetch_array(pg_query($lsql))){	
		 return $lrow[$pdata];
	}
}

function get_last_id($ptable,$pdata){
	return get_rec($ptable,$pdata,"",$pdata." desc");
}

function hitung_hari_kerja($pTglDari,$pTglSampai){
	$temp=$pTglDari;
	$selisih_hari_kerja=0;
	
	if($pTglDari && $pTglSampai){
		while($temp<$pTglSampai){
			$temp = date("m/d/Y",strtotime($temp. "+ 1 Days"));
			if(date("D",strtotime($temp))!="Sun" && date("D",strtotime($temp))!="Sat" && pg_num_rows(pg_query("select * from tblhari_libur where tgl_hari_libur!='".$temp."' for update"))){
				$selisih_hari_kerja++;	
			}
		}
	}
	return $selisih_hari_kerja;
}

function hitung_hari($pTglDari,$pTglSampai){
	$temp=$pTglDari;
	$selisih_hari_kerja=0;
	//echo "aaa".$temp."<br>";
	//echo "bb".$pTglSampai."<br>";
	if($pTglDari && $pTglSampai){
		//echo "tes".$temp."s".$pTglSampai;
		while($temp<$pTglSampai){
			//echo "masuk";
			$temp = date("m/d/Y",strtotime($temp. "+ 1 Days"));
			$selisih_hari_kerja++;	
		}
	}
	
	return $selisih_hari_kerja;
}

function get_last_day($pBulan,$pTahun){
	$get_last_day=date("j",strtotime(date("Y-m-t", strtotime($pBulan."/15/".$pTahun))));
	
	return $get_last_day;
}
function query_tambahan($p_modal,$p_tanggal,$p_arr_data,$p_flag=NULL){
	$tmp = explode('/',$p_modal);
	$p_modal = $tmp[count($tmp)-1];
	$l_success=1;
	// default = transaksi normal
	//$l_return_arr = array('jumlah_alokasi'=>1,'jumlah_approve'=>1,'saldo'=>-1); 
	$l_return_arr = array('jumlah_approve'=>1,'saldo'=>-1); 
	//klo ternyata transaksi dg tipe akun sama maka saldo bertambah
	if($p_arr_data['type_tr'] == $p_arr_data['transaction_type']){
		$l_return_arr = array('jumlah_approve'=>-1,'saldo'=>1); 
		//$l_return_arr = array('jumlah_alokasi'=>-1,'jumlah_approve'=>-1,'saldo'=>1); 

	}

	//klo batal maka kebalikan dari default
	//echo $p_flag;
	if($p_flag=='batal'){
		$l_return_arr = array('jumlah_alokasi'=>1);
		foreach($l_return_arr as $l_return_arr_field => $l_return_arr_value){
			$l_return_arr[$l_return_arr_field] = $l_return_arr_value * -1;
		}
	}
	if($p_flag=='batal_approve'){
		$l_return_arr = array('jumlah_alokasi'=>1,'jumlah_approve'=>1,'saldo'=>-1);
		foreach($l_return_arr as $l_return_arr_field => $l_return_arr_value){
			$l_return_arr[$l_return_arr_field] = $l_return_arr_value * -1;
		}
	}
	//print_r($l_return_arr);
	if($l_success)return $l_return_arr;
	else return false;
}

function fBulat($angka) {
	$koma="";
	$koma_2="";
    $l_angka=explode(".",$angka);
	
	$koma=substr($l_angka[1],0,2);
	
	//echo "koma :".$koma."<br>";
	//if($koma=="01" || $koma=="02" || $koma=="03" || $koma=="04" || $koma=="05" || $koma=="06" || $koma=="07" || $koma=="08" || $koma=="09") $koma=1;
	//echo $koma;
	
	$koma_2=substr($l_angka[1],4,1);
	//echo $koma_2."uu";
	//echo "koma 2:".$koma_2."<br>";
	
	if($koma_2 >=50){
		$koma=$koma+1;
	}else{
		$koma=$koma;	
	}
	//echo "koma".$koma_2."<br>";
	//echo $koma."cc";
	$koma=substr($koma,0,2);
	//echo "koma :".$koma;
	//echo "Angka : ".$l_angka[0];
	if($koma=="")$koma=0;
	//echo "oo".$koma;
	if($koma>=50){
		$angka=$l_angka[0]+1;
	}else{
	
		$angka=$l_angka[0];
	}
	
    return $angka;
}

function is_approval($fk_cabang,$final_approval,$tbl,$pk_id){
		
	$query="select * from tbljenjang_approval order by no asc";
	$lrs=pg_query($query);
	$i=0;
	while($lrow=pg_fetch_array($lrs)){
		$index[$lrow["no"]]=$lrow["no"];
		$approval_batal.="tgl_approval_".strtolower($lrow["no"])."=NULL ,fk_user_approval_".strtolower($lrow["no"])."=NULL,alasan_approve_".strtolower($lrow["no"])."=NULL ,is_approval_".strtolower($lrow["no"])."='f' ,";
	}
	
	$approval_batal="".rtrim($approval_batal,',');

	if(!pg_query("update ".$tbl." set 
	".$approval_batal."
	where no_sbg='".$pk_id."'")) $l_success=0;

	
	$query="select * from tblcabang where kd_cabang='".$fk_cabang."'";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	
	//echo $query;
	if($lrow["jenis_cabang"]=="Cabang"){
		$start="1";	
		//$start="1";	
	}else if($lrow["jenis_cabang"]=="Unit"){
		$start="1";	
		//$start="1";	
	}else if($lrow["jenis_cabang"]=="Pos"){
		$start="1";	
		//if(!$lrow["head_unit"])$approval_unit='f';
	}	
	
	for($i=$start;$i<=count($index);$i++){
/*		if($i==2){
			if($approval_unit!='f')$update.="is_approval_".strtolower($index[$i])."='t',";
		}else $update.="is_approval_".strtolower($index[$i])."='t',";
*/		
		$update.="is_approval_".strtolower($index[$i])."='t',";
		if(strtolower($final_approval)==strtolower($index[$i]))break;
	}
	$update=rtrim($update,",");
	
	if(!pg_query("update ".$tbl." set 
	".$update.",status_data='Approve'
	where no_sbg='".$pk_id."'")) $l_success=0;
	
/*	showquery("update ".$tbl." set 
	".$update.",status_data='Approve' 
	where no_sbg='".$pk_id."'");
*/}



function get_last_month($p_date,$p_num){
	//date format mm/dd/yyyy
	//num =byk bulan ke blkg
	
	$day=date("d",strtotime($p_date));	
	$day_last=date("d",strtotime('-'.$p_num.' month',strtotime($p_date)));	
	//echo $day_last;
	if($day!=$day_last){	
		$date = date("m/d/Y", strtotime('-1 second',strtotime('-'.($p_num-1).' month',strtotime(date('m',strtotime($p_date)).'/01/'.date('Y',strtotime($p_date))))));	
	}else{
		$date=date("m/d/Y",strtotime('-'.$p_num.' month',strtotime($p_date)));	
	}
	return $date;
	
}
function get_next_month($p_date,$p_num){
	//date format mm/dd/yyyy
	//num =byk bulan ke dpn
	
	$day=date("d",strtotime($p_date));	
	$day_next=date("d",strtotime('+'.$p_num.' month',strtotime($p_date)));	
	//echo $day;
	if($day!=$day_next){			
		$date = date("m/d/Y", strtotime('-1 second',strtotime('+'.($p_num+1).' month',strtotime(date('m',strtotime($p_date)).'/01/'.date('Y',strtotime($p_date))))));	
	}else{
		$date=date("m/d/Y",strtotime('+'.$p_num.' month',strtotime($p_date)));	
	}
	return $date;
	
}


function RATE($nper, $pmt, $pv, $fv = 0.0, $type = 0, $guess = 0.1) {//rate flat -> eff
	
	define('FINANCIAL_MAX_ITERATIONS', 128);
	define('FINANCIAL_PRECISION', 1.0e-08);

    $rate = $guess;
    if (abs($rate) < FINANCIAL_PRECISION) {
        $y = $pv * (1 + $nper * $rate) + $pmt * (1 + $rate * $type) * $nper + $fv;
    } else {
        $f = exp($nper * log(1 + $rate));
        $y = $pv * $f + $pmt * (1 / $rate + $type) * ($f - 1) + $fv;
    }
    $y0 = $pv + $pmt * $nper + $fv;
    $y1 = $pv * $f + $pmt * (1 / $rate + $type) * ($f - 1) + $fv;
    $i = $x0 = 0.0;
    $x1 = $rate;
    while ((abs($y0 - $y1) > FINANCIAL_PRECISION) && ($i < FINANCIAL_MAX_ITERATIONS)) {
        $rate = ($y1 * $x0 - $y0 * $x1) / ($y1 - $y0);
        $x0 = $x1;
        $x1 = $rate;
        if (abs($rate) < FINANCIAL_PRECISION) {
            $y = $pv * (1 + $nper * $rate) + $pmt * (1 + $rate * $type) * $nper + $fv;
        } else {
            $f = exp($nper * log(1 + $rate));
            $y = $pv * $f + $pmt * (1 / $rate + $type) * ($f - 1) + $fv;
        }
        $y0 = $y1;
        $y1 = $y;
        ++$i;
    }
    return $rate*1200;
}


function flat_eff($FLAT,$TENOR,$ADDM){

	/*
Include-ID  : flat_eff.i
Description : Include program untuk konversi dari bunga flat ke effektife
Created by  : ks on Aug 11, 94
Rumus dasar :
Parameter   : o input
		{1} - Bunga flat
		{2} - Tenor dalam bulan
		{3} - M bila Addm
		      B bila Addb
	      o Output
		{4} - Bunga Effektif
*/

//def var xKURUNG1    as decimal format "-999.999999999".
//def var xINT0       as decimal format "-999.999999999".
//def var xINT1       as decimal format "-999.999999999".
//def var xPEMBILANG  as decimal format "-999.999999999".
//def var xPENYEBUT   as decimal format "-999.999999999".
//def var xN          as integer.
//def var xY          as integer.
//def var xJ          as integer.
//def var xTENOR      as integer.
//def var xSALDO_FLAT as integer.
//def var xPH         as decimal.
//def var xPH0        as decimal.
//def var xFLAT       as decimal format "-999.999999999".
//def var xADDM       as char.

/* Parameter input */
//$FLAT  = $input1;
//$TENOR = $input2;
//$ADDM  = $input3;
/* ----------------*/

$PH = 10000;   /* konstata */

$N = $TENOR;
/* Hitung saldo awal dengan bunga flat */
$SALDO_FLAT = $PH * ( 1 + $FLAT * $TENOR / 1200);

if($ADDM== "M"){/* bila ADDM */
  $PH0        = $PH - ( $SALDO_FLAT / $TENOR );
  $SALDO_FLAT = $SALDO_FLAT - ( $SALDO_FLAT / $TENOR );
  $N          = $TENOR - 1;
} else {
   /* bila ADDB */
  $PH0 = $PH;
}

$INT0 = 0.01 * $N / ( 1 + ( $FLAT * $N * 0.01));
$Y    = - $N;
for($J=1;$J<=150;$J++){

  $KURUNG1   = 1 - pow (( 1 + $INT0 ), $Y);
  $PEMBILANG = $INT0 / $KURUNG1 - $SALDO_FLAT / ( $PH0 * $N );
  $PENYEBUT  = $INT0 * $N * pow (( 1 + $INT0) , ( $Y - 1 ));
  $PENYEBUT  = ( $PENYEBUT + $KURUNG1 ) / pow ( $KURUNG1, 2);
  $INT1      = $INT0 - ( $PEMBILANG / $PENYEBUT );
  $INT0 = $INT1;
}
return (($INT1*100))*12;
//return $INT1 * 1200;

}
function angsuran($id_edit,$tgl_pengajuan=NULL){
	global $nm_menu,$l_success;
	
	$table.="<table>";
	$table.= "
	<tr>
		<td>No</td>	
		<td>TOP</td>				
		<td>Nilai Angsuran</td>				
		<td>Pokok Jt</td>				
		<td>Bunga Jt</td>				
		<td>Saldo Pokok</td>		
		<td>Saldo Bunga</td>				
		<td>Saldo Pinjaman</td>				
		<td>Akrual 1</td>				
		<td>Akrual 2</td>				
		<td>Tgl Bayar</td>				
	</tr>
	";	
	
	$l_table_pk="no_sbg";
	if(pg_num_rows(pg_query("select * from data_gadai.tblproduk_gadai where ".$l_table_pk."='".$id_edit."' "))){		
		$jenis='gadai';
	}else{		
		$jenis='cicilan';
	}
	
	$l_tbl="data_gadai.tblproduk_".$jenis;		
	$query="select * from ".$l_tbl." 
	left join tblproduk on kd_produk=fk_produk
	left join tblrate on kd_rate=fk_rate
	where ".$l_table_pk." ='".$id_edit."'";
	//showquery($query);
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
		
	$lama_pinjaman=$lrow["lama_pinjaman"];	
		
	$nilai_bunga=$saldo_bunga=$pendapatan_bunga=round($lrow["biaya_penyimpanan"]);
			
	if($jenis=='cicilan'){
		$saldo_pokok=$lrow["pokok_hutang"];		
		$total_hutang=$lrow["total_hutang"];		
		$saldo_pinjaman=$nilai_ar=($total_hutang);
		$angsuran_bulan=$lrow["angsuran_bulan"];
		$total_pokok=$lrow["pokok_hutang"];		
	}elseif($jenis=='gadai'){
		$saldo_pokok=$lrow["total_nilai_pinjaman"];		
		$saldo_pinjaman=$nilai_ar=($saldo_pokok+$saldo_bunga);
		$angsuran_bulan=round($saldo_pinjaman/$lrow["lama_pinjaman"]);
		//echo $angsuran_bulan;
	}
	$jumlah_hari=$lrow["jumlah_hari"];
	$rate=$lrow["rate_flat"];
	$rate_flat=$lrow["rate_flat"]*(30/$jumlah_hari);// rateny harus yg rate 1 bulan		
	$bunga_bulanan=round($lrow["biaya_penyimpanan"]/$lrow["lama_pinjaman"]);	
	$fk_produk=$lrow["fk_produk"];	
	$perhitungan_jasa_simpan=$lrow["perhitungan_jasa_simpan"];	
	
	//khusus cicilan
	$addm_addb=$lrow["addm_addb"];
	//$addm_addb='B';
	//echo $rate_flat;
	$flat_eff=flat_eff($rate_flat*12,$lama_pinjaman,$addm_addb);
	//$flat_eff=flat_eff(25.31648518,27,'B');
	//$angsuran_bulan= 2464494.50 ;
	//$flat_eff2=rate($lama_pinjaman,$angsuran_bulan,$saldo_pokok*-1);
	//echo $flat_eff.'<br>';
		
	
	$total_angsuran=0;
	$ang_ke=0;
	
	
	$tgl_sistem=convert_date_english(today);
	if($tgl_pengajuan){
		$tgl_sistem=$tgl_pengajuan;
	}
		
	//if($addm_addb=='M')$tgl_sistem=get_last_month($tgl_sistem,1);
	$akrual1=$akrual2=0;
	//$flat_ef=42.16998313;
	//echo $flat_eff;
	for($i=$lama_pinjaman;$i>=0;$i--){
		
		/*-----------
			buat ngecek apakah tgl di bulan tsb ada di bulan selanjutnya
			misal 31 jan -> 31 feb tidak ada 		
		*/
		$top[$ang_ke]=get_next_month($tgl_sistem,$ang_ke);
		//if($addm_addb=='M' && $ang_ke==0)$top[$ang_ke]=get_next_month($top[$ang_ke],1);
		if($addm_addb=='M'){			
			if($ang_ke==0 || $ang_ke==1)$top[$ang_ke]=$tgl_sistem;
			else $top[$ang_ke]=get_next_month($tgl_sistem,$ang_ke-1);
		}
		
		//------------		
			
		if($ang_ke==0){
			$nilai_angsuran=0;	
			$pokok_jt=0;
			$bunga_jt=0;
		}else {
			$nilai_angsuran=$angsuran_bulan;	
		}
		
		if($jenis=='cicilan'){
			
			if($ang_ke>0){
				//hitung bunga_jt dikali pakai rate eff bkn flat
				if($ang_ke==1&&$addm_addb=='M'){
					$bunga_jt=0;
				}
				else $bunga_jt=round($flat_eff*$saldo_pokok/1200);
				//echo $ang_ke.'-'.$bunga_jt.'<br>';
				if($ang_ke==$lama_pinjaman){
					//hitung sisa angsuran dan bunga jt agar pas sesuai total semuanya bulat
					$nilai_angsuran=($nilai_ar-$total_angsuran);
					$bunga_jt=($pendapatan_bunga-$total_bunga);
				}
			}
			$total_bunga+=$bunga_jt;
		}elseif($jenis=='gadai'){
			
			if($ang_ke>0){
				//hitung_bunga_jt
				$selisih_jt=(strtotime($top[$ang_ke])-strtotime($top[$ang_ke-1]))/ (60 * 60 * 24);
				if($ang_ke==$lama_pinjaman){
					//hitung sisa bunga_jt
					$nilai_angsuran=($nilai_ar-$total_angsuran);
					$bunga_jt=$nilai_bunga-$total_bunga_jt;
				}else{
					$bunga_jt=round($selisih_jt/30*$bunga_bulanan);
				}
				$total_bunga_jt+=$bunga_jt;				
			}
		}
		$total_angsuran+=$nilai_angsuran;
		//hitung brp hari lg sampai akhir bulan
		$eom=date("m/t/Y",strtotime($top[$ang_ke]));		//lama
		$selisih_hari=(strtotime($eom)-strtotime($top[$ang_ke]))/ (60 * 60 * 24);
		
		//$selisih_hari=30-(date("d",strtotime($top[$ang_ke]))-1);
		//echo $selisih_hari.'<br>';		

		$pokok_jt=$nilai_angsuran-$bunga_jt;
		$saldo_bunga-=$bunga_jt;
		$saldo_pokok-=$pokok_jt;						
		$saldo_pinjaman-=$nilai_angsuran;		
		if($saldo_bunga<0){
			//echo $bunga_jt;	
		}
		
		if($jenis=='cicilan'){			
			//akrual2 pas di akhir bulan dihitung selisih brp hari dgn jt	
			//akrual1 = bunga_jt- akrual 2 ang_ke sblumny
			if($ang_ke==0&&$addm_addb=='M'){
				$bunga_akrual2=0;
			}else{				
				//$bunga_akrual2=round($flat_eff*$saldo_pokok/1200);
				$bunga_akrual2=round($flat_eff*$saldo_pokok/1200);
				//if($ang_ke==1)echo $bunga_akrual2;
			}
						
			//echo $ang_ke.'-'.$bunga_akrual2.'<br>';
			if($ang_ke==0){
				$akrual1=0;
			}else{
				$akrual1=$bunga_jt-$akrual2;
			}			
			
			$total_hari=date('t',strtotime($top[$ang_ke])); //lama pny gadai. leasing mngkin pakai ini
			//$akrual2=round(($selisih_hari/$total_hari)*$bunga_akrual2);
			//$total_hari=30;			
			//$akrual2=(round($saldo_pokok*$flat_eff/100/372)*$selisih_hari);
			$akrual2=(round($saldo_pokok*$flat_eff/100/($total_hari*12))*$selisih_hari);
			
			if($addm_addb=='M' && $ang_ke==0)$akrual2=0;
			//echo $selisih_hari.'-'.$total_hari."<br>";
			if($ang_ke==$lama_pinjaman-1){										
				$bunga_jt_sisa=($pendapatan_bunga-$total_bunga);
				$akrual2=round($selisih_hari/$total_hari*$bunga_jt_sisa);				
			}elseif($ang_ke==$lama_pinjaman){
				$akrual2=0;//sam							
			}
			
						
		}elseif($jenis=='gadai'){
			//akrual1 pas akhir bulan dihitung selisih brp hari dgn jt
			if($ang_ke==0){
				$bunga_hitung=$bunga_bulanan/(30/$perhitungan_jasa_simpan);
				$hari_hitung=$selisih_hari/$perhitungan_jasa_simpan;
				$akrual1=round($bunga_hitung*ceil($hari_hitung));
			}elseif($ang_ke==$lama_pinjaman){
				$akrual1=$nilai_bunga-$total_akrual1;				
			}else{
				$akrual1=$bunga_bulanan;
			}
			$total_akrual1+=$akrual1;
			$akrual2=0;
			
		}
		if($ang_ke==0)$tgl_bayar=$top[$ang_ke];
		elseif($ang_ke==1 && $addm_addb=='M')$tgl_bayar=$top[$ang_ke];
		else $tgl_bayar="";
		
		$query_ang_ke="
		insert into data_fa.tblangsuran(
			fk_sbg,angsuran_ke,tgl_jatuh_tempo,
			nilai_angsuran,pokok_jt,bunga_jt,
			saldo_pokok,saldo_bunga,saldo_pinjaman,
			akrual1,akrual2,tgl_bayar,rate
		) values(
		'".$id_edit."','".$ang_ke."','".$top[$ang_ke]."',
		".($nilai_angsuran).",".($pokok_jt).",".($bunga_jt).",
		".($saldo_pokok).",".($saldo_bunga).",".($saldo_pinjaman).",
		".($akrual1).",".($akrual2).",".(($tgl_bayar=="")?"NULL":"'".convert_sql($tgl_bayar)."'").",".($rate)."
		)";
		
		
		
		$table.= "
		<tr>
			<td>".$ang_ke."</td>	
			<td>".$top[$ang_ke]."</td>				
			<td align='right'>".convert_money("",$nilai_angsuran)."</td>				
			<td align='right'>".convert_money("",$pokok_jt)."</td>				
			<td align='right'>".convert_money("",$bunga_jt)."</td>				
			<td align='right'>".convert_money("",$saldo_pokok)."</td>		
			<td align='right'>".convert_money("",$saldo_bunga)."</td>				
			<td align='right'>".convert_money("",$saldo_pinjaman)."</td>				
			<td align='right'>".convert_money("",$akrual1)."</td>				
			<td align='right'>".convert_money("",$akrual2)."</td>				
			<td>".$tgl_bayar."</td>				
			<td align='right'>".convert_money("",($akrual1+$akrual2))."</td>	
		</tr>
		";				
		
		//showquery($query_ang_ke);
		if(!pg_query($query_ang_ke))$l_success=0;
		$ang_ke++;
	}
	
	if(!pg_query("insert into data_fa.tblangsuran_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from data_fa.tblangsuran where fk_sbg='".$id_edit."'")) $l_success=0;
	$table.= "</table>";
	echo $table;
	//$l_success=0;
	//if($jenis=='cicilan')$l_success=0;
}


function perpanjangan($fk_sbg,$no_cif=NULL,$status_fatg='Perpanjangan'){
	global $l_success,$upload_path_pic;
	
	//insert ke tblfatg
	$query_fatg="
		select * from(
			select * from viewkontrak where no_sbg='".$fk_sbg."'
		)as tblmain
		left join viewtaksir on fk_fatg=no_fatg
	";
	$lrs_fatg=pg_query($query_fatg);
	$lrow_fatg=pg_fetch_array($lrs_fatg);
	$no_fatg_old=$lrow_fatg["fk_fatg"];
	$fk_cabang=$lrow_fatg["fk_cabang"];
	
	$query="select nextserial_fatg('FATG':: text, '".$fk_cabang."')";
	$lrow=pg_fetch_array(pg_query($query));
	$no_fatg=$lrow["nextserial_fatg"];
	//showquery("select no_fatg from data_gadai.tbltaksir where no_fatg='".$no_fatg_old."' "); 
	if(pg_num_rows(pg_query("select no_fatg from data_gadai.tbltaksir where no_fatg='".$no_fatg_old."' "))){
		$tbl="data_gadai.tbltaksir";
	}
	else {
		$tbl="data_gadai.tbltaksir_umum";
	}
	
	$query="select * from ".$tbl." where no_fatg='".$no_fatg_old."'";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	
	//KOLOM HEADER
	$column="jumlah_barang,keterangan,fk_cabang,fk_jenis_barang,no_mesin,no_rangka,fk_warna,tahun,no_polisi,no_bpkb,nm_bpkb,tgl_bpkb,no_faktur,tgl_faktur,posisi_bpkb,tgl_terima_bpkb,alamat_bpkb,fk_cabang_bpkb";
	//$column="fk_cif,jumlah_barang,keterangan,final_penaksir,fk_cabang,fk_jenis_barang,credit_score_ca,credit_score_surveyor,fk_karyawan_ca,fk_karyawan_cmo,fk_karyawan_sales,	fk_karyawan_survey,fk_partner_dealer,hasil_interview,info_lingkungan,kesimpulan,kondisi,no_mesin,no_rangka,fk_warna,tahun,no_polisi,nm_stnk_bpkb,fk_karyawan_spv,karakter,capacity,capital,fk_tujuan_transaksi,sumber_dana,alasan";	
	
	$arr=split(',',$column);
	for($i=0;$i<count($arr);$i++){
		$column_convert.="".(($lrow[$arr[$i]]=="")?"NULL":"'".$lrow[$arr[$i]]."'").",";	
	}
	$column_convert=rtrim($column_convert,',');
		
	//INSERT HEADER
	$lquery="insert into ".$tbl."
	(no_fatg,tgl_taksir,status_fatg,no_sbg_lama,perpanjangan_ke,fk_cif,status_barang,".$column." )
	values		
	('".$no_fatg."','".today_db."','".$status_fatg."','".$fk_sbg."','".($lrow["perpanjangan_ke"]+1)."','".$no_cif."','Bekas',".$column_convert." );";
	//showquery($lquery);
	if(!pg_query($lquery))$l_success=0;	
	
	$lquery="";
	//INSERT DETAIL
	$query="select * from ".$tbl."_detail
	left join tblbarang on fk_barang=kd_barang
	where fk_fatg='".$no_fatg_old."'";
	$lrs=pg_query($query);
	
	//KOLOM DETAIL
	$column="fk_barang,keterangan_barang,nilai_awal,diskon,no_seri";
	
	$arr=split(',',$column);

	while($lrow=pg_fetch_array($lrs)){
		$column_convert="";
		for($i=0;$i<count($arr);$i++){
			$column_convert.="'".convert_sql($lrow[$arr[$i]])."',";			
		}
		$column_convert=rtrim($column_convert,',');
		$nilai_taksir=$lrow["nilai_taksir"];
		
		$total_taksir+=$nilai_taksir;
		$lquery.="
		insert into ".$tbl."_detail(fk_fatg,nilai_taksir,".$column." )
		values		
		('".$no_fatg."','".$nilai_taksir."',".$column_convert." );";		
	}
	$lquery.="update ".$tbl." set total_taksir='".$total_taksir."' where no_fatg='".$no_fatg."';";
	//showquery($lquery);
	if(!pg_query($lquery))$l_success=0;	
	
	if (!generate_log($tbl,$no_fatg,"IA","no_fatg")) $l_success=0;
	if (!generate_log($tbl,$no_fatg,"detail","no_fatg",'t')) $l_success=0;
		
	return $no_fatg;
	//showquery($lquery);	
	
}

function pelunasan_gadai($fk_sbg,$tgl_sistem=NULL){
	global $fk_cif,$nm_customer,$tgl_cair,$lama_pinjaman,$lama_pelunasan,$lama_jasa_simpan,$rate_flat,$nilai_pinjaman,$nilai_penyimpanan,$biaya_denda,$biaya_penjualan,$titipan;
	if(!$tgl_sistem){
		$tgl_sistem=date("Y-m-d",strtotime(get_rec("tblsetting",'tgl_sistem')));
	}
	
	$query="
	select 
	(extract(epoch from ('".$tgl_sistem."')-tgl_cair)/86400)+1 as lama_pelunasan	
	,total_nilai_pinjaman as nilai_pinjaman,rate as rate_flat
	,* from(
		select fk_sbg as no_sbg,fk_cif,fk_rate,tgl_cair,fk_produk,tgl_jt as tgl_jatuh_tempo from tblinventory 
		left join tblproduk on fk_produk=kd_produk
		where fk_sbg='".$fk_sbg."' and jenis_produk=0 and status_sbg='Liv'
	)as tblproduk_gadai
	left join (
		select fk_sbg,saldo_bunga as nilai_penyimpanan,saldo_pokok as total_nilai_pinjaman,rate from data_fa.tblangsuran
		where angsuran_ke=0
	)as tblangsuran on fk_sbg=no_sbg
	left join (
		select max(angsuran_ke)as lama_pinjaman,fk_sbg as fk_sbg1 from data_fa.tblangsuran 		
		group by fk_sbg
	)as tblangsuran1 on fk_sbg1=no_sbg	
	left join tblrate on kd_rate=fk_rate
	left join tblcustomer on no_cif = fk_cif
	left join tblproduk on fk_produk=kd_produk
	";
	//showquery($query);
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	
	$fk_cif=$lrow["fk_cif"];
	$nm_customer=$lrow["nm_customer"];
	$tgl_cair=$lrow["tgl_cair"];
	$lama_pinjaman=$lrow["lama_pinjaman"];
	$rate_flat=$lrow["rate_flat"];
	$jumlah_hari=$lrow["jumlah_hari"];
	$lama_pelunasan=round($lrow["lama_pelunasan"]);
	$nilai_pinjaman=$lrow["nilai_pinjaman"];
	$nilai_ap_customer=$lrow["nilai_ap_customer"];
	$fk_produk=$lrow["fk_produk"];
	$total_lama_pinjaman=$lama_pinjaman*30;
	$overdue=$lrow["lama_pelunasan"]-$lama_pinjaman*30;
	if($overdue<0)$overdue=0;

	$nilai_penyimpanan=$lrow["nilai_penyimpanan"];	
	$perhitungan_jasa_simpan=$lrow["perhitungan_jasa_simpan"];	
	$minimal_jasa_simpan=$lrow["minimal_jasa_simpan"];	
	if($lama_pelunasan<$minimal_jasa_simpan){
		$lama_jasa_simpan=$minimal_jasa_simpan;
	}
	elseif($total_lama_pinjaman<$lama_pelunasan)$lama_jasa_simpan=$total_lama_pinjaman;
	else $lama_jasa_simpan=$lama_pelunasan;
	if($nilai_penyimpanan==0){
		$lama_jasa_simpan=0;
	}
	elseif($perhitungan_jasa_simpan>0 ){		
		$nilai_penyimpanan=round($nilai_pinjaman*($rate_flat/100)*ceil($lama_jasa_simpan/$perhitungan_jasa_simpan)/($jumlah_hari/$perhitungan_jasa_simpan));
		$lama_jasa_simpan=ceil($lama_jasa_simpan/$perhitungan_jasa_simpan)*$perhitungan_jasa_simpan;
	}	
	

	$biaya_denda = 0;
	$biaya_penjualan = 0;
	$tgl_jatuh_tempo=date("Ymd",strtotime($lrow["tgl_jatuh_tempo"]));

	if($overdue>0){	
	
		//buat libur				
		$i=$tgl_jatuh_tempo;
		$no=1;
		while(strtotime($i)<=strtotime($tgl_sistem)){						
			
			//if($no==1){
				$hari=date('l',strtotime($i));
				//echo "select * from tblhari_libur where '".$i."' between periode_satu and periode_dua<br>";
				if(pg_num_rows(pg_query("select * from tblhari_libur where '".$i."' between periode_satu and periode_dua and libur_active ='t'"))){
					$libur ++;				
					//echo $i.'<br>';
				}elseif($hari=='Sunday' && $no==1){
					$libur++;
					$no++;
				}
			//}
			$i=date("Y-m-d",strtotime('+ 1 day',strtotime($i)));
			
		}
		//echo $libur.'<br>';
		//$libur=0;
		if($libur>$overdue)$overdue=0;
		else $overdue=$overdue-$libur;
	
		$lama_pelunasan=$lama_pelunasan-$libur;
		//end libur

		//echo $overdue.'=';
		$query="
		select * from tblproduk_detail_masa_tenggang
		where fk_produk='".$fk_produk."'
		order by dari asc
		";
		$lrs=pg_query($query);
		$i=1;
		while($lrow=pg_fetch_array($lrs)){
			$dari=$lrow["dari"];
			$ke=$lrow["ke"];
			$persen=$lrow["persen"];
			if($overdue>=$dari && $overdue<=$ke){
				$biaya_denda+=$nilai_pinjaman*$persen/100;
			}
		}
		if($overdue>$ke)$biaya_denda+=$nilai_pinjaman*$persen/100;	
		$biaya_denda=round($biaya_denda);
		$query="
		select * from tblproduk_detail_masa_tunggu
		where fk_produk='".$fk_produk."'
		order by dari asc
		";
		$lrs=pg_query($query);
		$i=1;
		while($lrow=pg_fetch_array($lrs)){
			$dari=$lrow["dari"];
			$ke=$lrow["ke"];
			$persen=$lrow["persen"];
			if($overdue>=$dari && $overdue<=$ke){
				$biaya_penjualan+=$nilai_pinjaman*$persen/100;
			}
		}
		if($overdue>$ke)$biaya_penjualan+=$nilai_pinjaman*$persen/100;	
		$biaya_penjualan=round($biaya_penjualan);
	}
	$titipan=round(get_rec("data_fa.tbltitipan","saldo_titipan","fk_sbg='".$fk_sbg."'"));

}


function pelunasan_cicilan($fk_sbg,$tgl_sistem=NULL){
	global $nm_customer,$sisa_angsuran,$tgl_cair,$total_denda_lalu,$tgl_jatuh_tempo_akhir,$total_denda_kini,$denda_keterlambatan,$denda_ganti_rugi,$fk_cif,$nilai_bayar_denda,$total_pelunasan,$fk_cabang,$sisa_pokok,$bunga_berjalan,$pinalti,$sisa_pelunasan,$tgl_jatuh_tempo_lalu,$selisih_hari,$rate_pinalti,$rate_flat,$byk_angs_tunggakan;
	//$tgl_sistem='2021-02-22';
	if(!$tgl_sistem){
		$tgl_sistem=date("Y-m-d",strtotime(get_rec("tblsetting",'tgl_sistem')));
	}	
	$tgl_bayar=$tgl_sistem;
	
	$query="
	select * from(
		select fk_sbg as no_sbg,fk_cif,fk_produk,tgl_cair,fk_cabang from tblinventory 
		left join tblproduk on fk_produk=kd_produk
		where fk_sbg='".$fk_sbg."' and jenis_produk=1 and status_sbg='Liv'
	)as tblproduk_cicilan	
	left join tblcustomer on no_cif=fk_cif
	left join (
		select no_sbg as no_sbg1 ,rate_flat from viewkontrak 
	)as tblsbg on no_sbg=no_sbg1
	left join (
		select max(tgl_jatuh_tempo)as tgl_jatuh_tempo_akhir,max(angsuran_ke)as ang_ke_akhir,fk_sbg as fk_sbg2 from data_fa.tblangsuran
		group by fk_sbg
	)as tblangsuran1 on fk_sbg2=no_sbg
	left join (select fk_sbg as fk_sbg3 ,saldo_titipan as titipan from data_fa.tbltitipan)as tbltitipan on fk_sbg3=no_sbg
	left join (select fk_sbg as fk_sbg4 ,saldo_denda as total_denda_lalu from data_fa.tbldenda)as tbldenda on fk_sbg4=no_sbg
	left join (
		select kd_produk,nominal_denda_keterlambatan,rate_denda_ganti_rugi,rate_pinalti from tblproduk
	)as tblproduk on fk_produk=kd_produk

	";
	//showquery($query);
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	
	$fk_cif=$lrow["fk_cif"];
	$fk_cabang=$lrow["fk_cabang"];
	$nm_customer=$lrow["nm_customer"];
	$tgl_cair=$lrow["tgl_cair"];
	$total_denda_lalu=($lrow["total_denda_lalu"]==""?0:round($lrow["total_denda_lalu"]));		
	$tgl_jatuh_tempo_akhir=$lrow["tgl_jatuh_tempo_akhir"];
	$titipan_angsuran=($lrow["titipan"]==""?0:$lrow["titipan"]);	
	$rate_denda=$lrow["rate_denda"];
	$rate_pinalti=$lrow["rate_pinalti"];
	$rate_flat=($lrow["rate_flat"]);
			
	$fom=date("Y-m-01",strtotime($tgl_bayar));
	
	$lrs=(pg_query("select sum(pokok_jt)as sisa_pokok from data_fa.tblangsuran
where fk_sbg='".$fk_sbg."' and tgl_bayar is null and tgl_jatuh_tempo >'".$fom."'"));
	$lrow2=pg_fetch_array($lrs);		
	$sisa_pokok=round($lrow2["sisa_pokok"]);
		
	$lrs=(pg_query("select sum(bunga_jt)as akrual,sum(nilai_angsuran)as sisa_angsuran,count(1)as byk_angs_tunggakan from data_fa.tblangsuran where fk_sbg='".$fk_sbg."' and tgl_bayar is null and tgl_jatuh_tempo <='".$fom."'"));
	$lrow2=pg_fetch_array($lrs);		
	$sisa_angsuran=round($lrow2["sisa_angsuran"]);
	$byk_angs_tunggakan=round($lrow2["byk_angs_tunggakan"]);
	
	//untuk penjualan/lelang
	$lrs=(pg_query("select sum(nilai_angsuran)as sisa_pelunasan from data_fa.tblangsuran
where fk_sbg='".$fk_sbg."' and tgl_bayar is null "));
	$lrow2=pg_fetch_array($lrs);		
	$sisa_pelunasan=round($lrow2["sisa_pelunasan"]);
	
	
	//denda
	$denda_keterlambatan=0;
	$denda_ganti_rugi=0;
	$total_denda_kini=0;
	
	$lrs=(pg_query("select tgl_jatuh_tempo,nilai_angsuran from data_fa.tblangsuran where fk_sbg='".$fk_sbg."' and tgl_bayar is null order by angsuran_ke asc "));

	$i=0;
	while($lrow1=pg_fetch_array($lrs)){
		$tgl_jatuh_tempo=$lrow1["tgl_jatuh_tempo"];
		$nilai_angsuran=$lrow1["nilai_angsuran"];
		
		$overdue=(strtotime($tgl_bayar)-strtotime($tgl_jatuh_tempo))/ (60 * 60 * 24);       	
		if($overdue<0)$overdue=0;
		
		if($overdue>0){
			$denda_keterlambatan+=$lrow["nominal_denda_keterlambatan"];
			$denda_ganti_rugi+=round($lrow["rate_denda_ganti_rugi"]*$nilai_angsuran/100)*$overdue;
			//echo $overdue;
		}		
	}
	
	$total_denda_kini=$denda_keterlambatan+$denda_ganti_rugi;
	//echo $total_denda_lalu."+".$total_denda_kini;
	$nilai_bayar_denda=$total_denda_lalu+$total_denda_kini;
	
	//bunga berjalan harian
	$bunga_berjalan=0;
	$lrs=(pg_query("select tgl_jatuh_tempo from data_fa.tblangsuran where fk_sbg='".$fk_sbg."' and tgl_jatuh_tempo <='".$tgl_bayar."'  order by angsuran_ke desc"));
	$lrow=pg_fetch_array($lrs);
	$tgl_jatuh_tempo_lalu=$lrow["tgl_jatuh_tempo"];
		
	$selisih_hari=(strtotime($tgl_bayar)-strtotime($tgl_jatuh_tempo_lalu))/ (60 * 60 * 24);
	$bunga_harian=round($sisa_pokok*$selisih_hari/360*$rate_flat/100);	
	$bunga_berjalan=$bunga_harian;
	
	$pinalti=0;
	$pinalti=round($sisa_pokok*$rate_pinalti/100);
		
}

function pembulatan_pelunasan($total_pembayaran){
	
	$pembulatan_baru=substr(round($total_pembayaran),-2,2);
	if($pembulatan_baru!=00){
		$nilai_bayar=$total_pembayaran/100;
		$nilai_bayar=ceil($nilai_bayar)*100;
	}else $nilai_bayar=$total_pembayaran;	

	
/*	$pembulatan_baru=substr(round($total_pembayaran),-3,3);
	if($pembulatan_baru!=000 && $pembulatan_baru!=500){
		if($pembulatan_baru<=500){			
			$nilai_bayar=round($total_pembayaran/1000);
			$nilai_bayar=($nilai_bayar*1000)+500;
		} else {
			$nilai_bayar=$total_pembayaran/1000;
			$nilai_bayar=ceil($nilai_bayar)*1000;
		}
	}else $nilai_bayar=$total_pembayaran;	
*/	

	return $nilai_bayar;
	
}

function get_sbg_awal($fk_sbg,$perpanjangan_ke){
	$no_sbg=$fk_sbg;
	for($i=$perpanjangan_ke;$i>1;$i--){
		$query="
		select * from(
			select * from(
				select no_sbg,fk_fatg from viewkontrak
				where no_sbg='".$no_sbg."' 
			)as tblsbg
			left join viewtaksir on fk_fatg=no_fatg			
		)as tblmain		
		";
		//echo $query;
		$lrs=pg_query($query);
		$lrow=pg_fetch_array($lrs);
		$no_sbg=$lrow["no_sbg_lama"];
	}
	return $no_sbg;
	
}


function insert_history_sbg($fk_sbg,$nilai_bayar,$ang_ke,$transaksi,$referensi,$denda=0,$is_batal='f'){
	
	$no=get_rec("data_gadai.tblhistory_sbg","no","fk_sbg='".$fk_sbg."'","no::numeric desc");
	if($no==NULL)$no=0;
	else $no=$no+1;
	$today_db=date("m/d/Y",strtotime(get_rec("tblsetting","tgl_sistem")));
	$tgl_bayar=$today_db." ".date("H:i:s");
	
	if($is_batal=='t'){
		$query="
			update data_gadai.tblhistory_sbg set 
			tgl_batal='".$tgl_bayar."' ,fk_user_batal='".$_SESSION['username']."'		
			where referensi='".$referensi."' and fk_sbg='".$fk_sbg."'";
	}else{
		$query="
			insert into data_gadai.tblhistory_sbg
			(no,fk_sbg,nilai_bayar,ang_ke,transaksi,referensi,denda,tgl_bayar,fk_user)
			values
			(".$no.",'".$fk_sbg."',".$nilai_bayar.",".$ang_ke.",'".$transaksi."','".$referensi."',".$denda.",'".$tgl_bayar."','".$_SESSION['username']."')
		";
	}
	//echo $query;
	return $query;
	
}

function get_cabang_terkait($fk_cabang){
	
	$query="
		select * from tblcabang 
		where kd_cabang ='".$fk_cabang."'
		or head_unit='".$fk_cabang."'
		or head_cabang='".$fk_cabang."'
	";
	$lrs=pg_query($query);
	while($lrow=pg_fetch_array($lrs)){
		$lcabang.="'".$lrow["kd_cabang"]."',";					
		
	}
	$lcabang=rtrim($lcabang,',');
	return $lcabang;
	
}
function query_booking($periode_awal,$periode_akhir){
	
	$query="
	(
		select * from data_fa.tblangsuran
		where angsuran_ke=0 and tgl_jatuh_tempo between '".$periode_awal." 00:00:00' and '".$periode_akhir." 00:00:00'
		and fk_sbg not in(select fk_sbg from tblinventory where status='Batal')
	)as tblangsuran
	";
	return $query;
}

function query_pelunasan($periode_awal,$periode_akhir){
	
	$query="
	(	
		select * from data_fa.tblangsuran
		where angsuran_ke!=0 and tgl_bayar between '".$periode_awal." 00:00:00' and '".$periode_akhir." 00:00:00'
	)as tblangsuran
	";
	return $query;
}

function where_os_tblangsuran($tgl){
	$where="
		where angsuran_ke!=0 and (tgl_bayar > '".$tgl."' or tgl_bayar is null)
	";
	return $where;
	
}

function where_os_tblinventory($tgl){
	$where="
		where tgl_cair <= '".$tgl."' and (tgl_lunas > '".$tgl."' or tgl_lunas is null)
	";
	return $where;
	
}

function get_cabang_pt($fk_cabang){
	return get_rec("(select * from tblcabang left join tblwilayah on fk_wilayah=kd_wilayah)as tblmain","fk_cabang_wilayah","kd_cabang='".$fk_cabang."'");
}

function get_list_cabang_pt($fk_cabang){
	
	$query="
		select * from tblcabang 
		left join tblwilayah on fk_wilayah=kd_wilayah
		where fk_cabang_wilayah ='".$fk_cabang."'
		order by kd_cabang desc	";
	$lrs=pg_query($query);
	while($lrow=pg_fetch_array($lrs)){
		$lcabang.="'".$lrow["kd_cabang"]."',";					
	}
	$lcabang=rtrim($lcabang,',');
	return $lcabang;
	
}


function list_non_cabang(){
	
	$query="
		select * from tblcabang inner join tblwilayah on fk_cabang_wilayah=kd_cabang
		order by kd_cabang asc	";
	$lrs=pg_query($query);
	while($lrow=pg_fetch_array($lrs)){
		$lcabang.="'".$lrow["kd_cabang"]."',";					
	}
	$lcabang.="'999'";
	$lcabang=rtrim($lcabang,',');
	return $lcabang;
	
}

function update_saldo_titipan($fk_sbg,$total){
	
	$type="titipan";		
	$lwhere="fk_sbg='".$fk_sbg."'";
	
	if(!pg_num_rows(pg_query("select * from data_fa.tbl".$type." where ".$lwhere." for update"))){		
		$query.="insert into data_fa.tbl".$type."(fk_sbg,saldo_".$type.") values('".$fk_sbg."',".$total.");";
		$query.=insert_log("data_fa.tbl".$type."",$lwhere,'IA');
	}else{
		$query.=insert_log("data_fa.tbl".$type."",$lwhere,'UB');
		$query.="update data_fa.tbl".$type." SET saldo_".$type." =saldo_".$type."+".$total." where ".$lwhere.";";		
		$query.=insert_log("data_fa.tbl".$type."",$lwhere,'UA');		
	}	
	//showquery("select * from data_fa.tbl".$type." where ".$lwhere." for update");
	//showquery($query);
	return $query;
}
function kacab($cabang,$tgl){
	
/*	if(!$tgl)$tgl=date("m/d/Y");
	$lrow=pg_fetch_array(pg_query("
	select * from tblkaryawan 
	inner join tblkaryawan_history_jabatan on fk_karyawan=npk
	left join tbljabatan on fk_jabatan=kd_jabatan
	where (tgl_non_active is null or '".$tgl."' between tgl_active and tgl_non_active)
	and (nm_jabatan='KACAB' or kd_jabatan='KACAB' or nm_jabatan='KAPOS' or kd_jabatan='KAPOS') and fk_cabang_karyawan='".$cabang."'
	"));

*/	

	$lrow=pg_fetch_array(pg_query("
	select * from(
	select kd_cabang,fk_karyawan_kacab from tblcabang 
	)as tblcabang
	left join tblkaryawan on fk_karyawan_kacab=npk
	where kd_cabang='".$cabang."'
	"));
	return $lrow;
	
}

function print_qrcode($codeContents){
	if($_SERVER['DOCUMENT_ROOT']=="D:/Development/Web Project"){
		//$filename="http://192.168.4.10/gadai/test.php";
		$filename="http://192.168.4.10/gadai/qr_code.php?codeContents=".urlencode($codeContents)."";
		//$filename="http://116.90.163.21:81/api/qr_code.php?codeContents=".$id_edit."";
	}else{
		$filename="http://localhost:81/api/qr_code.php?codeContents=".urlencode($codeContents)."";
	}
	return $filename;
}

function tambah_dp($fk_sbg){
	
	$lrow=pg_fetch_array(pg_query("select * from data_gadai.tblproduk_cicilan where no_sbg='".$fk_sbg."'"));
	$addm_addb=$lrow["addm_addb"];
	$fk_fatg=$lrow["fk_fatg"];
	
	$ang_ke=get_rec("data_fa.tblangsuran","angsuran_ke","fk_sbg='".$fk_sbg."' and tgl_bayar is null","angsuran_ke asc");
	$tgl_pengajuan=get_rec("data_fa.tblangsuran","tgl_jatuh_tempo","fk_sbg='".$fk_sbg."' and tgl_jatuh_tempo >'".today_db."'","angsuran_ke asc");
		
	$no_fatg_new=$fk_fatg.'J';			
	$no_sbg_new=$fk_sbg.'J';	
	
	//update inventory
	if(!pg_query("insert into tblinventory_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','DB' from tblinventory where fk_sbg='".$fk_sbg."' ")) $l_success=0;	
	if(!pg_query("update tblinventory set fk_sbg='".$no_sbg_new."' where fk_sbg='".$fk_sbg."'")) $l_success=0;
		
	if(!pg_query(storing($no_sbg_new,NULL,'Lunas','Pelunasan')))$l_success=0;//insert inventory baru
	//showquery(storing($fk_sbg,NULL,'Lunas','Pelunasan'));
	
	//if(!pg_query("update data_gadai.tblhistory_sbg set fk_sbg='".$no_sbg_new."' where fk_sbg='".$fk_sbg."'")) $l_success=0;		
	
	if(!pg_query("update data_fa.tblpembayaran_cicilan set fk_sbg='".$no_sbg_new."' where fk_sbg='".$fk_sbg."'")) $l_success=0;		
	//showquery("update data_fa.tblpembayaran_cicilan set fk_sbg='".$no_sbg_new."' where fk_sbg='".$fk_sbg."'");
		
	$lrow=pg_fetch_array(pg_query("select * from tblinventory where fk_sbg='".$no_sbg_new."'"));
	$tgl_cair=$lrow["tgl_cair"];
	$fk_produk=$lrow["fk_produk"];
	$fk_cif=$lrow["fk_cif"];
	$fk_cabang=$lrow["fk_cabang"];		
	$tgl_jt=date("m/d/Y",strtotime($lrow["tgl_jt"]));	
	//end			
	
	
	//update taksir
	
	$query_table=pg_query("
	   SELECT *
	   FROM information_schema.columns
	   WHERE table_schema = 'data_gadai' AND table_name  = 'tbltaksir_umum' and column_name !='no_fatg'
	   order by ordinal_position
		 ;
	");	
	$column_names='';
	while($lrow_table=pg_fetch_array($query_table)){
		$column_names.=",".$lrow_table["column_name"];
	}	
	
	if(!pg_query("insert into data_gadai.tbltaksir_umum select '".$no_fatg_new."' ".$column_names." from data_gadai.tbltaksir_umum where no_fatg='".$fk_fatg."'")) $l_success=0;	
	
	//showquery("insert into data_gadai.tbltaksir_umum select '".$no_fatg_new."' ".$column_names." from data_gadai.tbltaksir_umum where no_fatg='".$fk_fatg."'");
	if(!pg_query("update data_gadai.tbltaksir_umum set tgl_taksir='".today_db."',no_sbg_ar='".$no_sbg_new."' where no_fatg='".$no_fatg_new."'")) $l_success=0;	

	if(!pg_query("update data_gadai.tbltaksir_umum set status_fatg='Tambah DP',no_sbg_lama='".$no_sbg_new."' where no_fatg='".$fk_fatg."'")) $l_success=0;	
	//showquery("update data_gadai.tbltaksir_umum set status_fatg='Tambah DP',no_sbg_lama='".$no_sbg_new."' where no_fatg='".$fk_fatg."'");	
	
	//update cicilan
	$query_table=pg_query("
	   SELECT *
	   FROM information_schema.columns
	   WHERE table_schema = 'data_gadai' AND table_name  = 'tblproduk_cicilan' and column_name !='no_sbg'
	   order by ordinal_position
		 ;
	");	
	$column_names='';
	while($lrow_table=pg_fetch_array($query_table)){
		$column_names.=",".$lrow_table["column_name"];
	}	
	
	if(!pg_query("insert into data_gadai.tblproduk_cicilan select '".$no_sbg_new."' ".$column_names." from data_gadai.tblproduk_cicilan where no_sbg='".$fk_sbg."'")) $l_success=0;
	//showquery("insert into ".$tbl." select '".$no_sbg_new."' ".$column_names." from ".$tbl." where no_sbg='".$fk_sbg."'");
	if(!pg_query("update data_gadai.tblproduk_cicilan set fk_fatg='".$no_fatg_new."' where no_sbg='".$no_sbg_new."'")) $l_success=0;	
	//end 			
	
	
	$lrow=pg_fetch_array(pg_query("select * from data_fa.tblpenambahan_dp where fk_sbg_dp='".$fk_sbg."' and tgl_batal is null"));
	$no_kwitansi=$lrow['no_kwitansi'];
	
	$sisa_pokok=$lrow['sisa_pokok'];
	$nilai_dp=$lrow['nilai_dp'];
	$lama_pinjaman=$lrow['lama_pinjaman'];
	$pokok_hutang=$lrow['pokok_hutang'];
	$biaya_penyimpanan=$lrow['biaya_penyimpanan'];
	$total_hutang=$lrow['total_hutang'];
	$angsuran_bulan=$lrow['angsuran_bulan'];
	$biaya_admin=$lrow['biaya_admin'];
	
	if(!pg_query("update data_gadai.tblproduk_cicilan set 
	tgl_pengajuan='".today_db."',
	total_nilai_pinjaman='".$sisa_pokok."',
	nilai_dp='".$nilai_dp."',
	lama_pinjaman='".$lama_pinjaman."',
	pokok_hutang='".$pokok_hutang."',
	biaya_penyimpanan='".$biaya_penyimpanan."',
	total_hutang='".$total_hutang."',
	angsuran_bulan='".$angsuran_bulan."',
	biaya_admin_awal='".$biaya_admin."',
	diskon_admin='0',
	biaya_admin='".$biaya_admin."'
	where no_sbg='".$fk_sbg."'")) $l_success=0;			
	
/*	showquery("update data_gadai.tblproduk_cicilan set 
	tgl_pengajuan='".today_db."',
	total_nilai_pinjaman='".$sisa_pokok."',
	nilai_dp='".$nilai_dp."',
	lama_pinjaman='".$lama_pinjaman."',
	pokok_hutang='".$pokok_hutang."',
	biaya_penyimpanan='".$biaya_penyimpanan."',
	total_hutang='".$total_hutang."',
	angsuran_bulan='".$angsuran_bulan."',
	biaya_admin_awal='".$biaya_admin."',
	diskon_admin='0',
	biaya_admin='".$biaya_admin."'
	where no_sbg='".$fk_sbg."'");*/
	//end 
	
							
	///update hitungan 
	if(!pg_query("insert into data_fa.tblangsuran_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','DB' from data_fa.tblangsuran where fk_sbg='".$fk_sbg."' ")) $l_success=0;	
	if(!pg_query("update data_fa.tblangsuran set tgl_bayar='".today_db."', no_kwitansi ='".$no_kwitansi."' where fk_sbg='".$fk_sbg."' and tgl_bayar is null")) $l_success=0;
	
	if(!pg_query("update data_fa.tblangsuran set fk_sbg='".$no_sbg_new."' where fk_sbg='".$fk_sbg."'")) $l_success=0;	
	//showquery("update data_fa.tblangsuran set fk_sbg='".$no_sbg_new."' where fk_sbg='".$fk_sbg."'");													
	
	$referensi=$no_kwitansi;
	if($_REQUEST["denda_keterlambatan"]>0){
		$transaksi='Denda Keterlambatan';
		if(!pg_query(insert_history_sbg($fk_sbg,$_REQUEST["denda_keterlambatan"],$ang_ke,$transaksi,$referensi))) $l_success=0;
	}
	
	if($_REQUEST["denda_ganti_rugi"]>0){
		$transaksi='Denda Ganti Rugi';
		if(!pg_query(insert_history_sbg($fk_sbg,$_REQUEST["denda_ganti_rugi"],$ang_ke,$transaksi,$referensi))) $l_success=0;
	}
	
	if($_REQUEST["nilai_bayar_denda"]>0){
		$transaksi='Bayar Denda';
		if(!pg_query(insert_history_sbg($fk_sbg,$_REQUEST["nilai_bayar_denda"],$ang_ke,$transaksi,$referensi))) $l_success=0;
	}
		
	if(!pg_query("update data_fa.tblpenambahan_dp set fk_sbg='".$no_sbg_new."' where no_kwitansi='".$no_kwitansi."'")) $l_success=0;	
	
}

function scoring($no_fatg){
	
	//$no_fatg='104.2100043';
	
	$query="select extract(year from age(tgl_lahir))as umur,* from data_gadai.tbltaksir_umum left join tblcustomer on fk_cif =no_cif 
	left join(
		select count(1)as pembiayaan_lain,fk_fatg as fk_fatg1 from data_gadai.tbltaksir_umum_detail_slik
		group by fk_fatg
	)as tblslik on no_fatg=fk_fatg1
	left join (
		select fk_fatg,nilai_dp/total_nilai_pinjaman*100 as persen_dp,angsuran_bulan,lama_pinjaman as tenor from data_gadai.tblproduk_cicilan
		where status_approval!='Batal'
	)as tblcicilan on fk_fatg=no_fatg
	where no_fatg='".$no_fatg."'";
	//showquery($query);
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	$jenis_pekerjaan=$lrow["jenis_pekerjaan"];
	$lama_bekerja=$lrow["lama_bekerja"];
	$fk_status_rumah=$lrow["fk_status_rumah"];
	$lama_tinggal=$lrow["lama_tinggal"];
	$umur=$lrow["umur"];
	$persen_dp=$lrow["persen_dp"];	
	$penghasilan=$lrow["penghasilan"]/12;
	$pembiayaan_lain=round($lrow["pembiayaan_lain"]);
	$rasio_ang=($lrow["angsuran_bulan"]/$penghasilan)*100;
	$tenor=$lrow["tenor"];

	$score=0;
	
	$query="select * from tblcredit_scoring_pekerjaan where pekerjaan='".$jenis_pekerjaan."'";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	$score+=$lrow["nilai"];
	//echo 'pekerjaan '.$lrow["nilai"].'<br>';
	
	$query="select * from tblcredit_scoring_lama_bekerja where '".$lama_bekerja."' between dari and ke";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	$score+=$lrow["nilai"];
	//echo 'lama kerja '.$lrow["nilai"].'<br>';	
	
	$query="select * from tblcredit_scoring_status_rumah where fk_status_rumah='".$fk_status_rumah."'";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	$score+=$lrow["nilai"];
	//echo 'rumah '.$lrow["nilai"].'<br>';		
	
	$query="select * from tblcredit_scoring_lama_tinggal where '".$lama_tinggal."' between dari and ke";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	$score+=$lrow["nilai"];
	//echo 'lama tinggal '.$lrow["nilai"].'<br>';			
	
	$query="select * from tblcredit_scoring_umur where '".$umur."' between dari and ke";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	$score+=$lrow["nilai"];	
	//echo 'umur '.$lrow["nilai"].'<br>';		
	
	$query="select * from tblcredit_scoring_dp where '".$persen_dp."' between dari and ke";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	$score+=$lrow["nilai"];
	//echo 'dp '.$lrow["nilai"].'<br>';			
	
	$query="select * from tblcredit_scoring_penghasilan where '".$penghasilan."' between dari and ke";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	$score+=$lrow["nilai"];
	//echo 'penghasilan '.$lrow["nilai"].'<br>';			
	
	$query="select * from tblcredit_scoring_pembiayaan_lain where '".$pembiayaan_lain."' between dari and ke";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	$score+=$lrow["nilai"];	
	//echo 'lain '.$lrow["nilai"].'<br>';			
	
	$query="select * from tblcredit_scoring_rasio_angsuran where '".$rasio_ang."' between dari and ke";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	$score+=$lrow["nilai"];	
	//echo 'ang '.$lrow["nilai"].'<br>';			
	
	$query="select * from tblcredit_scoring_tenor where '".$tenor."' between dari and ke";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	$score+=$lrow["nilai"];	
	//echo 'tenor '.$lrow["nilai"].'<br>';
	
	return $score;		
}

function calc_asuransi($no_sbg,$tahun_ke,$is_pertahun='f'){
	//is pertahun untuk datun, karena rumus datun dikontrak totalan tapi untuk bayar pecah per tahun
	// update 15 ags 23
	$query="select * from data_gadai.tblproduk_cicilan 
	left join (
		select no_fatg as no_fatg1,no_mesin,no_rangka, kategori,nm_tipe,fk_jenis_barang,tahun as tahun_unit from viewkendaraan
	)as view on fk_fatg=no_fatg1 
	left join(
		select status_barang,no_fatg from viewtaksir
	)as viewtaksir on no_fatg=fk_fatg
	left join(select kd_produk,nm_produk from tblproduk) as tblproduk on fk_produk=kd_produk
	where no_sbg='".$no_sbg."'";
	$lrow=pg_fetch_array(pg_query($query));
	
	$kategori=$lrow["kategori"];		
	$fk_produk=$lrow["fk_produk"];	
	$fk_jenis_barang=$lrow["fk_jenis_barang"];	
	$pokok_hutang=$lrow["pokok_hutang"] ;
	$biaya_polis=$lrow["biaya_polis"] ;
	$rate_full=$lrow["rate_asuransi"] ;
	$nilai_asuransi=$lrow["nilai_asuransi"] ;
	$status_barang=$lrow["status_barang"] ;
		
	if($lrow["kategori"]=='R2'){
		$tsi=$lrow["pokok_hutang"];
		$tsi=$tsi/1000;
		$tsi=ceil($tsi)*1000;		
	}elseif($lrow["kategori"]=='R4'){
		$tsi=$lrow["total_nilai_pinjaman"];
	}
		
	$all_risk_dari_tahun=$lrow["all_risk_dari_tahun"];	
	$all_risk_sampai_tahun=$lrow["all_risk_sampai_tahun"];	
	$tlo_dari_tahun=$lrow["tlo_dari_tahun"];	
	$tlo_sampai_tahun=$lrow["tlo_sampai_tahun"];				
	
	$tjh_3=$lrow["tjh_3"];	
	$pa_supir=$lrow["pa_supir"];	
	$pa_penumpang=$lrow["pa_penumpang"];	
	$total_lain=$tjh_3+$pa_supir+$pa_penumpang;
	
	$lama_pinjaman=$lrow["lama_pinjaman"];		
	if($lama_pinjaman>=	$tahun_ke*12){
		$sisa_bulan=12;
	}else $sisa_bulan=12-($tahun_ke*12-$lama_pinjaman);		
	$tenor=ceil($lama_pinjaman/12);		
		
	if(strstr(strtoupper($lrow["nm_produk"]),"KONVERSI"))$is_konversi='t';	
	if($is_konversi=='t'){//konversi tambah tenor hanya tahun 1 
		if($tahun_ke>1)return 0;
		else return $lrow["nilai_asuransi"];
	}
	
	if($kategori=='R2'){
		$lasr=" and tenor='0'";//itung pertahun pakai rate rumus default
	}
	
	if($status_barang=='Datun'){
		if(!$all_risk_sampai_tahun && $tlo_sampai_tahun){
			if($is_pertahun!='t'){
				$lasr=" and tenor='".$lama_pinjaman."'";//DATUN TLO cukup ambil sekali saja sesuai tenor		
			}else $lasr=" and tenor='12'";			
			$tsi=$lrow["pokok_hutang"]-$lrow["biaya_polis"];
		}else{
			$lasr=" and tenor='12'"; //klo kombinasi ambil per tenor 12	
		}				
	}		
	
	if($tahun_ke>=$all_risk_dari_tahun && $tahun_ke<=$all_risk_sampai_tahun){
		$jenis_asuransi='All Risk';
	}elseif($tahun_ke>=$tlo_dari_tahun && $tahun_ke<=$tlo_sampai_tahun){
		$jenis_asuransi='TLO';
	}
		
	$query2="
	select * from tblproduk_detail_nilai_pertangungan
	where fk_produk='".$fk_produk."' and tahun_ke ='".$tahun_ke."'  order by tahun_ke";
	$lrs2=pg_query($query2);
	$lrow2=pg_fetch_array($lrs2);
	//showquery($query2);
	$persen_nilai_barang=$lrow2["persentase"];								
	$pinjaman_asr=($tsi*$persen_nilai_barang/100);	
	
	$lwhere="fk_produk='".$fk_produk."' and ".$pinjaman_asr." between dari and ke and fk_jenis_barang='".$fk_jenis_barang."'";
			
	$persen_asr=get_rec("tblproduk_detail_asuransi","persentase"," ".$lwhere." and jenis_asuransi='".$jenis_asuransi."' ".$lasr."");			
	//showquery("select * from tblproduk_detail_asuransi where fk_produk='".$fk_produk."' and ".$pinjaman_asr." between dari and ke and fk_jenis_barang='".$fk_jenis_barang."' and jenis_asuransi='".$jenis_asuransi."' ".$lasr."");

	if($status_barang=='Bekas')$sisa_bulan=12;	

	if($kategori=='R2'){	
		$desimal=2;
	}else {
		$desimal=3;
		$persen_asr=$persen_asr*($sisa_bulan/12);
	}
	
	if($kategori=='R4' && $status_barang!='Datun'){		
		$rate_asuransi=$persen_asr;
		$nominal=round($pinjaman_asr*$rate_asuransi/100);
		$nm_jenis_barang=get_rec("tbljenis_barang","nm_jenis_barang","kd_jenis_barang='".$fk_jenis_barang."'");
		if($nm_jenis_barang=='TRUCK'){
			$nominal+=($total_lain/$tenor);//dibagi per tenor untuk truck
		}else{		
			if($tahun_ke==1){
				$nominal+=$total_lain;		
			}
		}
		
	}else{
		$rate_asuransi=$persen_asr;
		$nominal=($pinjaman_asr*$rate_asuransi*($sisa_bulan/12)/100);		
		$nominal=round($nominal);
	}
	//echo 'TAHUN KE '.$tahun_ke.' ='.$pinjaman_asr.'*'.$rate_asuransi.' / '.$sisa_bulan.'<br>';
	
	if($status_barang=='Datun' && $is_pertahun!='t'){
		//$persen_asr=$persen_selisih;
		$rate_asuransi=str_replace(",","",convert_money("",$persen_asr,$desimal));		
		$nominal=round($tsi*$rate_asuransi/100);		
		//echo $tsi.'*'.$rate_asuransi.'<br>';
	}
		
	//echo 'TAHUN KE '.$tahun_ke.' ='.$nominal.'*'.$rate_asuransi.'<br>';
	return round($nominal);			
}


function get_karyawan_by_jabatan($nm_jabatan,$fk_cabang){
	$query="
	select nm_depan,fk_cabang_karyawan from tblkaryawan 
	left join tbljabatan on fk_jabatan=kd_jabatan
	where upper(nm_jabatan)=upper('".$nm_jabatan."') and fk_cabang_karyawan = '".$fk_cabang."' and karyawan_active='t'
	union
	select nm_depan,fk_cabang_detail from tblkaryawan 
	left join tblkaryawan_history_jabatan on fk_karyawan=npk 
	left join tbljabatan on fk_jabatan=kd_jabatan
	where upper(nm_jabatan)=upper('".$nm_jabatan."') and fk_cabang_detail = '".$fk_cabang."' and karyawan_active='t'
	and tgl_non_active is null
	";
	$l_res=pg_query($query);	
	$lrow=pg_fetch_array($l_res);
	
	//showquery($query);
	return $lrow["nm_depan"];
}

function calc_asuransi_nett($no_sbg,$nominal){
	$lrs_setting=(pg_query("select * from tblsetting"));
	$lsetting=pg_fetch_array($lrs_setting);	
	//echo $nominal.'<br>';
	$arr=array();
				
	$rate_komisi_asr=$lsetting["rate_komisi_asr"];
	$komisi=round($nominal*$rate_komisi_asr);
	$arr['komisi']=$komisi;
	//echo $komisi.'<br>';
	
	$rate_ppn=$lsetting["rate_ppn"];
	
	$dpp=$komisi;
	$ppn=round($dpp*$rate_ppn);
	$arr['ppn']=$ppn;
	//echo $ppn.'<br>';
	
	$rate_pph23=$lsetting["rate_pph23"];
	$pph23=round($dpp*$rate_pph23);		
	$arr['pph23']=$pph23;
	//echo $pph23.'<br>';
	
	$nominal=$nominal-$dpp-$ppn+$pph23;	
	$arr['nominal']=$nominal;
	return $arr;
	
}


function calc_fidusia($nominal,$fk_partner,$bulan,$tahun,$nilai=0){
	
	$query="
	select * from tblfidusia_detail
	left join tblfidusia on kd_fidusia =fk_fidusia
	where fk_partner='".$fk_partner."' and ".$nilai." between dari and ke ";
	//showquery($query);
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	$arr["biaya"]=$lrow["biaya"];		
	$arr["jasa"]=$lrow["jasa"];
	
	$lrs_setting=(pg_query("select * from tblsetting"));
	$lsetting=pg_fetch_array($lrs_setting);		
		
	$fom=$tahun.'-'.$bulan.'-01 00:00:00';
	$eom=$tahun.'-'.$bulan.'-'.date('t',strtotime($fom)).' 23:59:59';
	$foy=$tahun.'-01-01 00:00:00';
	
	if($bulan==1)$total_berjalan=0;
	else{
		$query1="select sum(jasa)as total_berjalan from data_gadai.tblproduk_cicilan 
		left join(
			select * from tblfidusia_detail
			left join tblfidusia on kd_fidusia =fk_fidusia
		)as fidusia on fk_partner_notaris=fk_partner and total_hutang between dari and ke
		inner join (
			select referensi as no_kwitansi_pelunasan,fk_sbg as fk_sbg2,tgl_data as tgl_pelunasan_dealer,nilai_bayar from data_gadai.tblhistory_sbg
			where transaksi='Pembayaran Fidusia' and tgl_batal is null
			and tgl_data>='".$foy."' and tgl_data <='".$fom."'
		)as tbl2 on no_sbg=fk_sbg2		
		";
		//showquery($query1); 
		$lrs1=pg_query($query1);
		$lrow1=pg_fetch_array($lrs1);
		//echo $lrow1["total_berjalan"];
		$total_berjalan= $lrow1["total_berjalan"];
	}//cari nilai yang sudah dibayarkan
	$rate_bawah_pph21=$lsetting["rate_bawah_pph21"];
	$batas_pph21=$lsetting["batas_pph21"];
	$rate_atas_pph21=$lsetting["rate_atas_pph21"];
		
	if($total_berjalan>$batas_pph21){
		$rate_pph21=$rate_atas_pph21;
	}else{
		$rate_pph21=$rate_bawah_pph21;
	}

	$pph21=round($arr["jasa"]*$rate_pph21);		
	$arr['pph21']=$pph21;
	
	$utang=$nominal;
	$arr['utang']=$utang;
	
	$nominal=$nominal-$pph21;		
	$arr['nominal']=$nominal;
	return $arr;
	
}


function calc_tac($no_sbg,$jenis_tac){
	
	$lrs_setting=(pg_query("select * from tblsetting"));
	$lsetting=pg_fetch_array($lrs_setting);			
	
	$query="select * from data_gadai.tblproduk_cicilan 
	left join (
		select no_fatg as no_fatg1,no_mesin,no_rangka, kategori,nm_tipe,fk_jenis_barang from viewkendaraan
	)as view on fk_fatg=no_fatg1 
	left join(
		select status_barang,no_fatg,fk_karyawan_sales,fk_karyawan_spv,fk_partner_dealer from data_gadai.tbltaksir_umum
	)as viewtaksir on no_fatg=fk_fatg
	where no_sbg='".$no_sbg."'";
	//showquery($query);
	$lrow=pg_fetch_array(pg_query($query));	
	//echo $lrow["fk_karyawan_sales"];
	$fk_partner=$lrow["fk_partner_dealer"];
	$kategori=$lrow["kategori"];	
		
	switch($jenis_tac){
		case "Dealer" : 
			$nominal=$lrow["insentif_dealer"];	
			$no_npwp='-';
			if(pg_num_rows(pg_query("select * from tblpartner where kd_partner='".$fk_partner."' and is_pph23_dealer='t'"))){
				$kategori='R2';//flag khusus rumus ikut R2 walaupun kontrak R4
			}
		break;
		case "Kacab" : 
			$nominal=$lrow["insentif_kacab"];	
			$lrow1=pg_fetch_array(pg_query("select * from tblkaryawan_dealer left join tbljabatan on fk_jabatan=kd_jabatan where fk_dealer='".$fk_partner."' and upper(nm_jabatan)='KEPALA CABANG'"));
			$no_npwp=$lrow1["no_npwp"];
			$nama=$lrow1["nm_karyawan"];
		break;
		case "SPV" : 
			$nominal=$lrow["insentif_spv"];	
			$lrow1=pg_fetch_array(pg_query("select * from tblkaryawan_dealer left join tbljabatan on fk_jabatan=kd_jabatan where nik='".$lrow["fk_karyawan_spv"]."'"));
			$no_npwp=$lrow1["no_npwp"];
			$nama=$lrow1["nm_karyawan"];
		break;
		case "Sales" : 
			$nominal=$lrow["insentif_sales"];
			$lrow1=pg_fetch_array(pg_query("select * from tblkaryawan_dealer left join tbljabatan on fk_jabatan=kd_jabatan where nik='".$lrow["fk_karyawan_sales"]."'"));
			$no_npwp=$lrow1["no_npwp"];
			$nama=$lrow1["nm_karyawan"];
		break;
		case "Lain" : 
			$nominal=$lrow["insentif_lain"];
			$no_npwp=NULL;
		break;
	}
	
	$rate_pph21_npwp=$lsetting['rate_pph21_npwp'];
	$rate_pph21_non_npwp=$lsetting['rate_pph21_non_npwp'];
	$rate_pph23=$lsetting['rate_pph23'];
	$rate_ppn=$lsetting['rate_ppn'];
			
	if($no_npwp==''||$no_npwp==NULL){
		$rate_pph21=$rate_pph21_non_npwp;	
	}else $rate_pph21=$rate_pph21_npwp;			
				
	$dpp=round($nominal/(1+$rate_ppn));			
	$beban=$nominal;
	
	if($jenis_tac=='Dealer'){	
		$pph23=round($dpp*$rate_pph23,2);		
	}elseif($jenis_tac=='Lain'){		
		//no pph
	}else{
		$pph21=round($nominal*$rate_pph21);		
	}	
	
	if($kategori=='R2'){
		$nominal=$nominal-$pph21-$pph23;	
	}else{
		if($jenis_tac!='Dealer'){			
			$beban_up=round($beban/(1-$rate_pph21),2);	
			$pph21=$beban_up-$nominal;
			$komisi=($pph21+$pph23);
		}else{
			$pph23=0;
			$pph21=0;
		}
		
	}	
//	echo $nominal.'aaa<br>';
	$arr["dpp"]=$dpp;
	$arr["komisi"]=$komisi;
	$arr["pph21"]=$pph21;
	$arr["pph23"]=$pph23;
	$arr["nominal"]=$nominal;
	$arr["beban"]=$beban;
	
	$arr["npwp"]=$no_npwp;
	$arr["nama"]=$nama;
	
	return $arr;
}

function jenis_asuransi($lrow){
	
	if($lrow['all_risk_dari_tahun']){
		if($lrow['all_risk_dari_tahun']==$lrow['all_risk_sampai_tahun']){
			$lrow["jenis_asuransi"]=$lrow['all_risk_dari_tahun'].' AR';
		}else{
			$lrow["jenis_asuransi"]=' '.($lrow['all_risk_dari_tahun'].'-'.$lrow['all_risk_sampai_tahun']).' AR';
		}
	}
	
	if($lrow['tlo_dari_tahun']){
		if($lrow["jenis_asuransi"])$lrow["jenis_asuransi"].='+';
		$lrow["jenis_asuransi"].=($lrow['tlo_dari_tahun'].'-'.$lrow['tlo_sampai_tahun']).' TLO';
	}
	
	return $lrow["jenis_asuransi"];

}

function insert_history($p_arr){
	//$fk_sbg,$nilai_bayar,$ang_ke,$transaksi,$referensi,$denda=0,$is_batal='f'
		
	$tgl_data=$p_arr["tgl_data"];
	$fk_sbg=$p_arr["fk_sbg"];
	$referensi=$p_arr["referensi"];
	$nilai_bayar=$p_arr["nilai_bayar"];
	$ang_ke=$p_arr["ang_ke"];
	$transaksi=$p_arr["transaksi"];
	
	if(!$tgl_data){
		$tgl_data=get_rec("tblsetting","tgl_sistem");
	}
	
	$tgl_bayar=date("m/d/Y",strtotime(get_rec("tblsetting","tgl_sistem"))).date('  H:i:s');
	$no=get_rec("data_gadai.tblhistory_sbg","no","fk_sbg='".$fk_sbg."'","no::numeric desc");
	if($no==NULL)$no=0;
	else $no=$no+1;
	
	if($is_batal=='t'){
		$query="
			update data_gadai.tblhistory_sbg set 
			tgl_batal='".$tgl_bayar."' ,fk_user_batal='".$_SESSION['username']."'		
			where referensi='".$referensi."' and fk_sbg='".$fk_sbg."'";
	}else{
		$query="
			insert into data_gadai.tblhistory_sbg
			(no,fk_sbg,nilai_bayar,ang_ke,transaksi,referensi,tgl_bayar,fk_user,tgl_data,denda)
			values
			(".$no.",'".$fk_sbg."',".($nilai_bayar?$nilai_bayar:"0").",".$ang_ke.",'".$transaksi."','".$referensi."','".$tgl_bayar."','".$_SESSION['username']."','".$tgl_data."',0)
		";
	}
	//echo $query;
	return $query;
	
}

function number_format2($data,$ptype=NULL){
	//if(strstr($_SERVER['DOCUMENT_ROOT'],"Web Project"||$_SESSION["username"]=='superuser')){//
//		if(!$data)return NULL;
//		else return convert_money("",$data,2);
//	}
	//else {				
		$data= number_format($data,2);
		$data1=explode(".",$data);
		if(!$data)return NULL;
		elseif($ptype==1) return str_replace(",","",$data1[0]).",".$data1[1];
		else return str_replace(",",".",$data1[0]).",".$data1[1];
	//}
}

function excl(){
	//buat hide approval
	return " and case when kd_field like '%_approval%' and kd_field similar to '%(kapos|kaunit|kawil|dirut)%' then false else true end ";
}
?>