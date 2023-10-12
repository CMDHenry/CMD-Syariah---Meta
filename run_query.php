<?
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/numeric.inc.php';
require 'requires/validate.inc.php';
require 'requires/timestamp.inc.php';
require 'requires/input.inc.php';
require 'requires/cek_error.inc.php';
require 'requires/module.inc.php';
require 'requires/db_utility.inc.php';
require 'classes/select.class.php';
require 'requires/file.inc.php';
require 'requires/accounting_utility.inc.php';

$l_success=1;
pg_query("BEGIN");

$query="
select addm_addb,fk_owner,* from data_fa.tblangsuran 
left join data_gadai.tblproduk_cicilan on tblproduk_cicilan.no_sbg=tblangsuran.fk_sbg
left join (
	select fk_cabang,fk_sbg as fk_sbg1 from tblinventory 
)as tblinventory on fk_sbg=fk_sbg1
left join (
	select fk_owner,tr_date,reference_transaksi from data_accounting.tblgl_auto 
	where type_owner='ALOKASI PEMBAYARAN CICILAN' and fk_coa_c like '%1191000001' and reference_transaksi like '%BYR%'
)as tblgl on fk_owner=fk_sbg and tr_date=tblangsuran.tgl_jatuh_tempo
where fk_sbg like '1%' and tgl_bayar=tblangsuran.tgl_jatuh_tempo
and case when addm_addb='B' then angsuran_ke>0 else angsuran_ke>1 end and no_kwitansi is not null
and fk_owner is null and no_kwitansi not in(select no_pelunasan_cicilan from data_fa.tblpelunasan_cicilan)
and bunga_jt-akrual1>0
and fk_sbg='1222021800021'
";	
$lrs=pg_query($query);
while($lrow=pg_fetch_array($lrs)){

	if(!pg_query("update data_accounting.tblgl_auto set total=".($lrow["bunga_jt"]-$lrow["akrual1"])." where fk_coa_c like '%4114000002%' and fk_owner='".$lrow["fk_sbg"]."' and reference_transaksi='".$lrow["no_kwitansi"]."'")) $l_success=0;
	//1191000001
	showquery("update data_accounting.tblgl_auto set total=".($lrow["bunga_jt"]-$lrow["akrual1"])." where fk_coa_c like '%4114000002%' and fk_owner='".$lrow["fk_sbg"]."' and reference_transaksi='".$lrow["no_kwitansi"]."'");
	
	if(!pg_query("insert into data_accounting.tblgl_auto select nextserial('tblgl_auto.no_bukti'::text)	,tr_date,type_owner,fk_owner,reference_type,reference_transaksi,fk_currency,description,fk_coa_d,'".$lrow["fk_cabang"].".1191000001',valas,rate,'".$lrow["akrual1"]."',is_approved,tgl_approval,
	approved_by,keterangan_approval,fk_customer,fk_supplier,fk_cabang,fk_jenis_cabang from data_accounting.tblgl_auto where  fk_coa_c like '%4114000002%' and fk_owner='".$lrow["fk_sbg"]."' and reference_transaksi='".$lrow["no_kwitansi"]."' ")) $l_success=0;
	showquery("insert into data_accounting.tblgl_auto select nextserial('tblgl_auto.no_bukti'::text)	,tr_date,type_owner,fk_owner,reference_type,reference_transaksi,fk_currency,description,fk_coa_d,'".$lrow["fk_cabang"].".1191000001',valas,rate,'".$lrow["akrual1"]."',is_approved,tgl_approval,
	approved_by,keterangan_approval,fk_customer,fk_supplier,fk_cabang,fk_jenis_cabang from data_accounting.tblgl_auto where  fk_coa_c like '%4114000002%' and fk_owner='".$lrow["fk_sbg"]."' and reference_transaksi='".$lrow["no_kwitansi"]."' ");

}
//$l_success=0;
if ($l_success==1){
	pg_query("COMMIT");
}else{
	pg_query("ROLLBACK");
}


?>
