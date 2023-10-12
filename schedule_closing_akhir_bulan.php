<?
require_once 'requires/config.inc.php';
require_once 'requires/general.inc.php';
require_once 'requires/numeric.inc.php';
require_once 'requires/validate.inc.php';
require_once 'requires/timestamp.inc.php';
require_once 'requires/db_utility.inc.php';
require_once 'requires/file.inc.php';
require_once 'requires/stok_utility.inc.php';
require_once 'requires/accounting_utility.inc.php';
set_time_limit(0);
$l_success=1;

//pg_query("begin");

$fk_sbg=$_REQUEST["fk_sbg"];
if($fk_sbg != ''){
	$lwhere1.="and fk_sbg = '".$fk_sbg."' ";
}

$bulan=$_REQUEST["bulan"];
$tahun=$_REQUEST["tahun"];

if($bulan!="" && $tahun!=""){
	$tgl_sistem=$bulan.'/01/'.$tahun;
	$tgl_sistem=get_next_month($tgl_sistem,1);
	$today_db=date("Y-m-t",strtotime($tgl_sistem));
}else{
	$today_db=date("Y-m-d",strtotime(get_rec("tblsetting",'tgl_sistem')));
}
$fom=date("Y-m",strtotime($today_db)).'-01';

$eom_before=date("Y-m-d",strtotime('-1 second',strtotime($fom)));
$last_month=date("Y-m",strtotime($eom_before));

if(pg_num_rows(pg_query("select * from data_gadai.tblclosing_harian where tgl_closing like '%".$eom_before."%'"))){

	// AKRUAL AKHIR BULAN
	$query="
	select * from(
		select fk_sbg,fk_cabang,jenis_produk,
		akrual1+akrual2 as akrual_akhir_bulan  
			from(
			select * from data_fa.tblangsuran			
			left join (
				select distinct fk_owner,total from data_accounting.tblgl_auto 
				where type_owner='AKRUAL AKHIR BULAN' and tr_date like '".$eom_before."%' 	
			)as tblgl_auto on fk_owner=fk_sbg and akrual1+akrual2=total
			where tgl_jatuh_tempo like '".$last_month."%' and fk_owner is null 
			".$lwhere1."
		)as tblangsuran
		inner join (
			select fk_sbg as fk_sbg1,fk_cif,jenis_produk,fk_cabang from tblinventory 
			left join tblproduk on kd_produk=fk_produk 
			where tgl_cair<'".$eom_before." 23:59:59' and (tgl_lunas >'".$eom_before." 23:59:59' or tgl_lunas is null)
		)as tblinventory on fk_sbg=fk_sbg1
	)as tblmain 	
	left join (
		select no_fatg as no_fatg1,kategori,no_sbg from data_gadai.tblproduk_cicilan 
		left join viewkendaraan on fk_fatg=no_fatg
	)as view on no_sbg=fk_sbg
	left join(
		select distinct on (fk_sbg)fk_sbg as fk_sbg_tarik,tgl_data as tgl_tarik from data_gadai.tblhistory_sbg
		where transaksi='Tarik'	and tgl_data<='".$eom_before." 23:59:59' and tgl_batal is null
	)as tarik on no_sbg=fk_sbg_tarik			
	left join(
		select distinct on (fk_sbg)fk_sbg as fk_sbg_tebus,tgl_data as tgl_tebus from data_gadai.tblhistory_sbg
		where transaksi='Tebus'	and tgl_data<='".$eom_before." 23:59:59' and tgl_batal is null
	)as tebus on no_sbg=fk_sbg_tebus	
				
	where akrual_akhir_bulan!=0	
	--and (tgl_tarik is null or (tgl_tebus is not null and tgl_tarik>tgl_tebus))
	--limit 10
	--limit 500
	";

	$lrs=pg_query($query);
	showquery($query);
	while($lrow=pg_fetch_array($lrs)){
		$kategori=strtolower($lrow["kategori"]);
		$akrual_akhir_bulan=$lrow["akrual_akhir_bulan"];
		$fk_cabang=$lrow["fk_cabang"];
		$posting=true;
		//echo $kategori;
		if($lrow["tgl_tarik"]==NULL || ($lrow["tgl_tebus"]!=NULL && $lrow["tgl_tebus"]>$lrow["tgl_tarik"])){//tarik gk usa akru
			//showquery("select fk_owner,total from data_accounting.tblgl_auto where type_owner='AKRUAL AKHIR BULAN' and tr_date like '".$eom_before."%' and fk_owner ='".$lrow["fk_sbg"]."' and total=".$akrual_akhir_bulan."");
			if(!pg_num_rows(pg_query("select fk_owner,total from data_accounting.tblgl_auto where type_owner='AKRUAL AKHIR BULAN' and tr_date like '".$eom_before."%' and fk_owner ='".$lrow["fk_sbg"]."' and total=".$akrual_akhir_bulan.""))){
				
				$arrPost = array();				
				$arrPost["pend_bunga_yad"]				= array('type'=>'d','value'=>$akrual_akhir_bulan);
				$arrPost["pend_bunga_".$kategori]		= array('type'=>'c','value'=>$akrual_akhir_bulan);				
				//cek_balance_array_post($arrPost);
				if($posting==true){
					if(!posting('AKRUAL AKHIR BULAN',$lrow["fk_sbg"],$eom_before,$arrPost,$fk_cabang,'00'))$l_success=0;	
				}
			}
		}
	}	
}

//pg_query("commit");


?>
