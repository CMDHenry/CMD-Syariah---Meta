<?
require_once 'requires/config.inc.php';
require_once 'requires/authorization.inc.php';
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
/*$bulan=1;
$tahun=2021;
*/

if($bulan!="" && $tahun!=""){
	$tgl_sistem=$bulan.'/01/'.$tahun;
	//$tgl_sistem=get_next_month($tgl_sistem,1);
	$today_db=date("Y-m-t",strtotime($tgl_sistem));
	
}else{
	$today_db=date("Y-m-d",strtotime(get_rec("tblsetting",'tgl_sistem')));
}

$fom=date("Y-m",strtotime($today_db)).'-01';
$eom=date("Y-m-t",strtotime($today_db));

$eom_before=date("Y-m-d",strtotime('-1 second',strtotime($fom)));
$fom_before=date("Y-m-01",strtotime('-1 second',strtotime($fom)));
$last_month=date("Y-m",strtotime($eom_before));

//showquery("select * from data_gadai.tblclosing_harian where tgl_closing like '%".$eom_before."%'");

if(pg_num_rows(pg_query("select * from data_gadai.tblclosing_harian where tgl_closing like '%".$eom_before."%'"))&& !pg_num_rows(pg_query("select * from data_accounting.tblgl_auto where tr_date like '%".$eom_before."%' and type_owner='BIAYA FIDUSIA'"))){
	
	$lquery=" 
	select * from data_gadai.tblproduk_cicilan 
	inner join (
		select fk_sbg,fk_cabang from tblinventory where tgl_cair >='".$fom_before."' and tgl_cair <='".$eom_before."'
	)as inv on fk_sbg=no_sbg		
	left join (
		select no_fatg as no_fatg1,no_mesin,no_rangka, kategori,nm_tipe,fk_jenis_barang from viewkendaraan
	)as view on fk_fatg=no_fatg1	
	where biaya_notaris>0 --limit 100	
	";
	$lrs=pg_query($lquery);
	showquery($lquery);
	
	while($lrow = pg_fetch_array($lrs)){
		$fk_cabang=$lrow["fk_cabang"];
		$kategori=$lrow["kategori"];
		$nominal=$lrow["biaya_notaris"];	
		$fidusia[$fk_cabang][$kategori]+=$nominal;
		$cabang[$fk_cabang]=$fk_cabang;
	}
	
	if(count($cabang)>0){
		foreach($cabang as $fk_cabang ){			
			foreach($fidusia[$fk_cabang] as $kategori =>$total){				
				$fk_owner='FID'.date("m/y",strtotime($eom_before));
				//echo $fk_owner;
				$kategori=strtolower($kategori);
				$arrPost = array();		
				$arrPost["utang_fidusia_".$kategori]		= array('type'=>'c','value'=>$total);
				$arrPost["biaya_adm_legal_".$kategori]	    = array('type'=>'d','value'=>$total);
				cek_balance_array_post($arrPost);
				if(!posting('BIAYA FIDUSIA',$fk_owner,$eom_before,$arrPost,$fk_cabang,'00'))$l_success=0;
				//echo "posting";
			}
		}
	}

}

pg_query("commit");
//pg_query("rollback");


?>
	