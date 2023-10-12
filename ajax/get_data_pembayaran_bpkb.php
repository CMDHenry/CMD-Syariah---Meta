<?
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/convert.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/numeric.inc.php';
require '../requires/referer_check.inc.php';
require '../classes/recordset_ajax.class.php';
require '../requires/stok_utility.inc.php';


if (!isset($_SESSION['id']) ){
	//echo "err:1000 ";
} else{
	
	$fk_sbg=$_REQUEST["fk_sbg"];
	if(!$_REQUEST["tgl_bayar"]){
		$tgl_bayar=today_db;
	}else $tgl_bayar=convert_date_english($_REQUEST["tgl_bayar"]);
	
	
	$query="
	select * from(
		select * from(
			select date_part('day',('".$tgl_bayar."'-tgl_lunas))::numeric as ovd_bpkb,fk_sbg as no_sbg,fk_cif,tgl_cair,fk_cabang,tgl_lunas from tblinventory 
			left join (select no_sbg_ar,tgl_serah_terima_bpkb from data_gadai.tbltaksir_umum)as tblumum on no_sbg_ar=fk_sbg
			where fk_sbg='".$fk_sbg."' and tgl_lunas is not null and tgl_serah_terima_bpkb is null
		)as tblcicilan
		left join (
			select nm_customer , no_cif from tblcustomer
		)as tblcustomer on no_cif = fk_cif
		 
	)as tblproduk_cicilan
	";	
	//echo $query;
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	
	$fk_cif=$lrow["fk_cif"];
	$fk_cabang=$lrow["fk_cabang"];
	$nm_customer=$lrow["nm_customer"];
	$tgl_lunas=$lrow["tgl_lunas"];
	$ovd_bpkb=$lrow["ovd_bpkb"];

	$nilai_tagihan=0;
	$lrow_s=pg_fetch_array(pg_query("select * from tblsetting"));
	$ovd_lunas_bpkb=$ovd_bpkb-$lrow_s["ovd_lunas_bpkb"];
	if($ovd_lunas_bpkb>0){		
		$nilai_tagihan=ceil($ovd_lunas_bpkb/30)*$lrow_s["biaya_bpkb"];
		if($nilai_tagihan>$lrow_s["max_biaya_bpkb"]){
			$nilai_tagihan=$lrow_s["max_biaya_bpkb"];
		}
	}
	
	
	echo $nm_customer.'¿';
	echo $fk_cabang.'¿';
	echo $tgl_lunas.'¿';
	echo $ovd_bpkb.'¿';
	echo $nilai_tagihan.'¿';
	
}
