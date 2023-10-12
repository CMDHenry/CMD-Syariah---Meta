<?
require_once 'requires/config.inc.php';
//require_once 'requires/authorization.inc.php';
require_once 'requires/general.inc.php';
require_once 'requires/numeric.inc.php';
require_once 'requires/validate.inc.php';
require_once 'requires/timestamp.inc.php';
require_once 'requires/input.inc.php';
require_once 'requires/cek_error.inc.php';
require_once 'requires/module.inc.php';
require_once 'requires/db_utility.inc.php';
require_once 'classes/select.class.php';
require_once 'requires/file.inc.php';
require_once 'requires/stok_utility.inc.php';
require_once 'requires/accounting_utility.inc.php';
set_time_limit(0);
$l_success=1;

pg_query("begin");

$bulan=$_REQUEST["bulan"];
$tahun=$_REQUEST["tahun"];
//$bulan=1;
//$tahun=2022;

if($bulan!="" && $tahun!=""){
	$tgl_sistem=$bulan.'/01/'.$tahun;
	$tgl_sistem=get_next_month($tgl_sistem,1);
	$today_db=date("Y-m-t",strtotime($tgl_sistem));
	$eom=$today_db;
}else{
	$today_db=date("Y-m-d",strtotime(get_rec("tblsetting",'tgl_sistem')));
}

$fom=date("Y-m",strtotime($today_db)).'-01';

$eom_before=date("Y-m-d",strtotime('-1 second',strtotime($fom)));
$last_month=date("Y-m",strtotime($eom_before));

//showquery("select * from data_gadai.tblclosing_harian where tgl_closing like '%".$eom_before."%'");
//&& !pg_num_rows(pg_query("select * from data_accounting.tblgl_auto where tr_date like '%".$eom_before."%' and type_owner='CADANGAN PIUTANG'")
if(pg_num_rows(pg_query("select * from data_gadai.tblclosing_harian where tgl_closing like '%".$eom_before."%'"))){
	
	$bulan=date("n",strtotime($eom_before));
	$tahun=date("Y",strtotime($eom_before));
	
	if(!pg_num_rows(pg_query("select * from data_fa.tblcadangan_piutang where bulan='".$bulan."' and tahun='".$tahun."'"))){
		$query="select nextserial_transaksi('CAD':: text)";
		$lrow=pg_fetch_array(pg_query($query));
		$l_kd_voucher=$lrow["nextserial_transaksi"];
		
		if(!pg_query("insert into data_fa.tblcadangan_piutang(kd_voucher,tgl_voucher,total,bulan,tahun,keterangan)values('".$l_kd_voucher."','".$eom_before."',0,'".$bulan."','".$tahun."','".$keterangan."')")) $l_success=0;
		//showquery("insert into data_fa.tblcadangan_piutang(kd_voucher,tgl_voucher,total,bulan,tahun,keterangan)values('".$l_kd_voucher."','".$eom_before."','".$total."','".$bulan."','".$tahun."','".$keterangan."')");
		if(!pg_query("insert into data_fa.tblcadangan_piutang_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from data_fa.tblcadangan_piutang where kd_voucher='".$l_kd_voucher."' ")) $l_success=0;
					
		$l_id_log=get_last_id("data_fa.tblcadangan_piutang_log","pk_id_log");
	}else{
		$l_kd_voucher=get_rec("data_fa.tblcadangan_piutang","kd_voucher","bulan ='".$bulan."' and tahun='".$tahun."'");
	}
	
	$lquery=" 
	select * from data_gadai.tblproduk_cicilan 
	inner join (
		select fk_sbg,fk_cif,jenis_produk,fk_cabang from tblinventory 
		left join tblproduk on kd_produk=fk_produk 
		where tgl_cair<'".$eom_before." 23:59:59' and (tgl_lunas >'".$eom_before." 23:59:59' or tgl_lunas is null)
	)as inv on fk_sbg=no_sbg		
	left join (
		select no_fatg as no_fatg1,no_mesin,no_rangka, kategori,nm_tipe,fk_jenis_barang from viewkendaraan
	)as view on fk_fatg=no_fatg1	
	left join(
		select distinct on(fk_sbg)fk_sbg as fk_sbg_od,date_part('day','".$eom_before."' -tgl_jatuh_tempo)as ovd,saldo_pokok from data_fa.tblangsuran 
		where (tgl_bayar is null or(tgl_bayar is not null and tgl_bayar>'".$eom_before."'))
		and angsuran_ke>0 order by fk_sbg,angsuran_ke asc 
	)as tblod on no_sbg=fk_sbg_od		
	left join (
		select sum(pokok_jt) as saldo_pokok, sum(bunga_jt) as saldo_bunga,sum(nilai_angsuran) as saldo_pinjaman, fk_sbg_os from(
			select bunga_jt, pokok_jt as pokok_jt,nilai_angsuran, fk_sbg as fk_sbg_os from data_fa.tblangsuran
			".where_os_tblangsuran($eom_before)."						
		)as tblar
		inner join tblinventory on fk_sbg = fk_sbg_os
		".where_os_tblinventory($eom_before)."
		group by fk_sbg_os
	)as tblos on no_sbg=fk_sbg_os
	left join(
		select distinct on (fk_sbg)fk_sbg as fk_sbg_tarik,tgl_data as tgl_tarik from data_gadai.tblhistory_sbg
		where transaksi='Tarik'	and tgl_data<='".$eom_before." 23:59:59' and tgl_batal is null
	)as tarik on no_sbg=fk_sbg_tarik					
	left join(
		select distinct on (fk_sbg)fk_sbg as fk_sbg_tebus,tgl_data as tgl_tebus from data_gadai.tblhistory_sbg
		where transaksi='Tebus'	and tgl_data<='".$eom_before." 23:59:59' and tgl_batal is null
	)as tebus on no_sbg=fk_sbg_tebus	
	
	left join tblmetode_perhitungan_jaminan on fk_metode=kd_metode	
	where true and fk_metode is not null 
	--and no_sbg in('20102230200006','20102230200005','20102230300208')
	order by tgl_cair asc
	--limit 5
	";
	$lrs=pg_query($lquery);
	showquery($lquery);
	
	echo "<table>";

	while($lrow = pg_fetch_array($lrs)){
		$fk_cabang=$lrow["fk_cabang"];
		$kategori=strtolower($lrow["kategori"]);
		$no_sbg=$lrow["no_sbg"];					
		$cabang[$fk_cabang]=$fk_cabang;						
		
		$total_nilai_pinjaman=$lrow["total_nilai_pinjaman"];		
		$persen_jaminan=$lrow["rate"];
		$nilai_agunan=$total_nilai_pinjaman*$persen_jaminan/100;
		//echo $total_nilai_pinjaman.'<br>';
		$saldo_pokok=$lrow["saldo_pokok"];
		$selisih=$saldo_pokok-$nilai_agunan;
		//echo $no_sbg.'=>'.$selisih.'='.$saldo_pokok."-".$nilai_agunan.'<br>';
		
		if($lrow["ovd"]<=0)$lrow["ovd"]=0;
		
		$query_cad="select * from tblsetting_cadangan where ".$lrow["ovd"].">=ovd_awal and ".$lrow["ovd"]."<=ovd_akhir";
		$lrs_cad=pg_query($query_cad);
		$persen_ckpn=0;
		while($lrow_cad=pg_fetch_array($lrs_cad)){
			//echo $lrow["ovd"].'>='.$lrow_cad["ovd_awal"].'<='.$lrow_cad["ovd_akhir"].'<br>';
			//if($lrow["ovd"]>=round($lrow_cad["ovd_awal"])&& $lrow["ovd"]<=round($lrow_ovd["ovd_akhir"])){//aneh ga jalan di php
				$persen_ckpn=$lrow_cad['rate'];		
			//}			
		}
		$total_cadangan=round($selisih*$persen_ckpn/100);
		//echo $total_cadangan.'<br>';
		
		
		if($lrow["fk_sbg_tarik"]){
			if(!$lrow["fk_sbg_tebus"] || $lrow["tgl_tarik"]>$lrow["tgl_tebus"]){
				$lrow["status"]='TARIK';
				$ovd_data=strtotime($lrow["tgl_tarik"])-strtotime($lrow["tgl_jt_berjalan"]);
				$ovd_data=$ovd_data/(60*60*24);
				if($ovd_data<0)$ovd_data=0;
			}elseif($lrow["fk_sbg_tebus"]){
				$lrow["status"]='TEBUS';	
			}
		}
		//echo $lrow["status"].'<br>';
		if($lrow["status"]=='TARIK'){
			$total_cadangan=0;
		}
				
		if($nilai_agunan>0){//karena butuh nilai agunan buat report
			//$cad[$fk_cabang]+=$total_cadangan;
			$fk_owner=$no_sbg;
			$flag_jurnal='f';
			
			$total_jurnal=get_rec("data_accounting.tblgl_auto","total","tr_date like '%".$eom_before."%' and type_owner='CADANGAN PIUTANG' and fk_owner='".$fk_owner."'");
			
			//echo $total_cadangan."!=".$total_jurnal.'=>'.$fk_owner.'<br>';
			if(round($total_jurnal)==0){				
				//$fk_owner='CAD'.date("m/y",strtotime($eom_before));
				$flag_jurnal='t';
			}elseif($total_cadangan!=$total_jurnal){
				$flag_jurnal='t';
				$lquery = "
					delete from data_accounting.tblgl_auto
					where tr_date like '%".$eom_before."%' and type_owner='CADANGAN PIUTANG' and fk_owner='".$fk_owner."'";
				showquery($lquery);
				if(!pg_query($lquery)) $l_success=0;
			}
			
			//$flag_jurnal='t';
			if($flag_jurnal=='t'){
				
				$arrPost = array();		
				$arrPost["cadangan_piutang"]					= array('type'=>'c','value'=>$total_cadangan);
				$arrPost["penyisihan_pembiayaan_".$kategori]	= array('type'=>'d','value'=>$total_cadangan);
				//cek_balance_array_post($arrPost);
				if(!posting('CADANGAN PIUTANG',$fk_owner,$eom_before,$arrPost,$fk_cabang,'00'))$l_success=0;
			
				if(!pg_num_rows(pg_query("select * from data_fa.tblcadangan_piutang_detail where fk_sbg='".$no_sbg."' and fk_voucher='".$l_kd_voucher."'"))){							
					$lquery = "
						insert into data_fa.tblcadangan_piutang_detail(fk_voucher,total_cadangan,fk_sbg,persen_jaminan,persen_ckpn,nilai_agunan) 
						values(
							'".$l_kd_voucher."',
							".(($total_cadangan=="")?"0":"'".round($total_cadangan)."'").",
							'".convert_sql($no_sbg)."',
							".(($persen_jaminan=="")?"0":"'".round($persen_jaminan)."'").",
							".(($persen_ckpn=="")?"0":"'".round($persen_ckpn)."'").",
							".(($nilai_agunan=="")?"0":"'".round($nilai_agunan)."'")."
						)";
					showquery($lquery);
					if(!pg_query($lquery)) $l_success=0;
				}else{
					$lquery = "
					update data_fa.tblcadangan_piutang_detail set 
						total_cadangan='".$total_cadangan."',
						nilai_agunan='".$nilai_agunan."',
						persen_jaminan='".$persen_jaminan."',
						persen_ckpn='".$persen_ckpn."'
					where fk_voucher='".$l_kd_voucher."' and fk_sbg='".$no_sbg."'";
					showquery($lquery);
					if(!pg_query($lquery)) $l_success=0;
					
				}
			}
			
		}
		
	}
	
}
//echo $l_success;
pg_query("commit");
//pg_query("rollback");


?>
	