<?
if($_SESSION["id"]!='Online'){
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/convert.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/numeric.inc.php';
require '../requires/referer_check.inc.php';
require '../classes/recordset_ajax.class.php';
require '../requires/stok_utility.inc.php';
}

if (!isset($_SESSION['id']) ){
	//echo "err:1000 ";
} else{
	
	$fk_sbg=$_REQUEST["fk_sbg"];	

	if(!$_REQUEST["tgl_bayar"]){
		$tgl_bayar=today_db;
	}else $tgl_bayar=convert_date_english($_REQUEST["tgl_bayar"]);
	
//	$fk_sbg="20104230300121";
//	$tgl_bayar='04/30/2023';	
	
	$query="
	select * from(
		select * from(
			select fk_sbg as no_sbg,fk_cif,fk_produk,tgl_cair,fk_cabang from tblinventory 
			left join tblproduk on fk_produk=kd_produk
			where fk_sbg='".$fk_sbg."' and jenis_produk=1 and status_sbg='Liv'
		)as tblcicilan		
		left join viewsbg on fk_sbg1=no_sbg			
		left join (select fk_sbg as fk_sbg3 ,saldo_titipan as titipan from data_fa.tbltitipan)as tbltitipan on fk_sbg3=no_sbg
		left join (select fk_sbg as fk_sbg4 ,saldo_denda as total_denda_lalu from data_fa.tbldenda)as tbldenda on fk_sbg4=no_sbg
		left join (
			select kd_produk,nominal_denda_keterlambatan,rate_denda_ganti_rugi,nominal_biaya_tagih from tblproduk
		)as tblproduk on fk_produk=kd_produk
		left join (
			select nm_customer , no_cif from tblcustomer
		)as tblcustomer on no_cif = fk_cif
	)as tblproduk_cicilan
	";	
	//echo $query;
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	
	$query1="select * from viewang_ke where fk_sbg='".$fk_sbg."'";
	$lrs1=pg_query($query1);
	$lrow1=pg_fetch_array($lrs1);
	$lrow["saldo_pinjaman"]	=$lrow1["saldo_pinjaman"];
	$lrow["ang_ke"]	=$lrow1["ang_ke"];
	$lrow["top"]	=$lrow1["top"];
	$lrow["nilai_angsuran"]	=$lrow1["nilai_angsuran"];
	
	$fk_cif=$lrow["fk_cif"];
	$fk_cabang=$lrow["fk_cabang"];
	$nm_customer=$lrow["nm_customer"];
	$tgl_cair=$lrow["tgl_cair"];
	$saldo_pinjaman=$lrow["saldo_pinjaman"]	;	
	$ang_ke=$lrow["ang_ke"];
	$total_denda_lalu=($lrow["total_denda_lalu"]==""?0:$lrow["total_denda_lalu"]);		
	$titipan=($lrow["titipan"]==""?0:$lrow["titipan"]);		
	$rate_denda=$lrow["rate_denda"];
	$rate_pinalti=$lrow["rate_pinalti"];
	$total_denda_kini=0;
	$tenor=$lrow["tenor"];
	$masa_tenggang_denda=($lrow["masa_tenggang_denda"]);
 	$nilai_angsuran=$lrow["nilai_angsuran"];
	
	$tgl_jatuh_tempo=$lrow["top"];		
	
	$overdue=(strtotime($tgl_bayar)-strtotime($tgl_jatuh_tempo))/ (60 * 60 * 24);     
	if($overdue<0)$overdue=0;
	if(!$tgl_jatuh_tempo)$overdue=0;
	
	if($overdue>0){
		$now=date("Y-m-d",strtotime('-1 day',strtotime($tgl_bayar)));
		
		while(strtotime($now)>=strtotime($tgl_jatuh_tempo)){		
			$hari=date('l',strtotime($now));
			if((pg_num_rows(pg_query("select * from tblhari_libur where tgl_libur= '".$now."'"))|| $hari=='Sunday')){
				$libur++;
				//break;
			}
			$now=date("Y-m-d",strtotime('-1 day',strtotime($now)));
			//break;
		}
	}
	//echo $libur.'aa<br>';
	if($libur==$overdue)$overdue=0;//kalau jmlh hari libur+minggu = overdue. denda di nolkan
			
	if($overdue>0){
		$denda_keterlambatan+=$lrow["nominal_denda_keterlambatan"];
		$denda_ganti_rugi+=round($lrow["rate_denda_ganti_rugi"]*$nilai_angsuran/100)*$overdue;
	}
	$total_denda_kini=$denda_keterlambatan+$denda_ganti_rugi;
	
	if($ang_ke==NULL)$ang_ke=$tenor;// kalau sdh abis angs dan mau bayar denda
	if($_SESSION["id"]!='Online'){//php 5
	echo $fk_cif.'¿';
	echo $nm_customer.'¿';
	echo $tgl_cair.'¿';
	echo $saldo_pinjaman.'¿';
	echo $tgl_jatuh_tempo.'¿';
	echo $nilai_angsuran.'¿';
	echo $ang_ke.'¿';
	echo $total_denda_lalu.'¿';
	echo $total_denda_kini.'¿';
	echo $titipan.'¿';
	echo $tenor.'¿';
	echo $fk_cabang.'¿';
	echo $overdue.'¿';
	echo $denda_keterlambatan.'¿';
	echo $denda_ganti_rugi.'¿';
	echo $denda_ganti_rugi.'¿';
	}
	
}
