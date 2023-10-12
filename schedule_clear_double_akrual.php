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

$today_db=date("Y-m-d",strtotime(get_rec("tblsetting",'tgl_sistem')));	
$fom=date("Y-m",strtotime($today_db)).'-01';
$eom_before=date("Y-m-d",strtotime('-1 second',strtotime($fom)));

if(!pg_query("
delete from data_accounting.tblgl_auto where type_owner='AKRUAL AKHIR BULAN' and tr_date like '%".$eom_before."%'
and fk_owner in(
	select fk_owner from(
	select sum(case when fk_coa_d is not null then total end)as debit, sum(case when fk_coa_c is not null then total end)as credit,fk_owner,type_owner,tr_date,reference_transaksi
	from data_accounting.tblgl_auto where tr_date like '%".$eom_before."%' and type_owner='AKRUAL AKHIR BULAN'
	group by fk_owner,type_owner,tr_date,reference_transaksi
	)as tblmain
	where (debit!=credit or credit is null or debit is null) 
)
")) $l_success=0;		
showquery("
delete from data_accounting.tblgl_auto where type_owner='AKRUAL AKHIR BULAN' and tr_date like '%".$eom_before."%'
and fk_owner in(
	select fk_owner from(
	select sum(case when fk_coa_d is not null then total end)as debit, sum(case when fk_coa_c is not null then total end)as credit,fk_owner,type_owner,tr_date,reference_transaksi
	from data_accounting.tblgl_auto where tr_date like '%".$eom_before."%' and type_owner='AKRUAL AKHIR BULAN'
	group by fk_owner,type_owner,tr_date,reference_transaksi
	)as tblmain
	where (debit!=credit or credit is null or debit is null) 
)
");



$query="
select * from(
	select count(fk_owner) as qty,fk_owner from data_accounting.tblgl_auto 
	where tr_date like '%".$eom_before."%' and type_owner='AKRUAL AKHIR BULAN'
	group by fk_owner
)as tblmain
where qty>2
";
$lrs=pg_query($query);
while($lrow=pg_fetch_array($lrs)){
	$query_del="
	select * from data_accounting.tblgl_auto
	where tr_date like '%".$eom_before."%' and type_owner='AKRUAL AKHIR BULAN' 
	and fk_owner='".$lrow["fk_owner"]."'
	order by no_bukti desc limit 2	
	";
	$lrs_del=pg_query($query_del);
	while($lrow_del=pg_fetch_array($lrs_del)){
	
		if(!pg_query("
		delete from data_accounting.tblgl_auto where type_owner='AKRUAL AKHIR BULAN' and tr_date like '%".$eom_before."%'
		and fk_owner='".$lrow_del["fk_owner"]."' and no_bukti='".$lrow_del["no_bukti"]."'
		")) $l_success=0;		
	
/*		showquery("select * from data_accounting.tblgl_auto where type_owner='AKRUAL AKHIR BULAN' and tr_date like '%".$eom_before."%'
		and fk_owner='".$lrow_del["fk_owner"]."' and no_bukti='".$lrow_del["no_bukti"]."'");
*/	}

}
?>
