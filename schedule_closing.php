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
require_once 'closing.php';
require_once 'requires/stok_utility.inc.php';
require_once 'requires/accounting_utility.inc.php';
set_time_limit(0);
$l_success=1;

$is_closing_otomatis=get_rec("tblsetting","is_closing_otomatis");

if($is_closing_otomatis=='t' && !pg_num_rows(pg_query("select * from data_gadai.tblclosing_harian where tgl_closing like '%".today_db."%'"))){
	$tbl="tblsetting";
	
	$lwhere="is_closing_harian='f'";
	if(!pg_query(insert_log($tbl,$lwhere,'UB')));
	if(!pg_query("update ".$tbl." SET is_closing_harian='t' where ".$lwhere."")) $l_success=0;	
	if(!pg_query(insert_log($tbl,$lwhere,'UA')));	

	pg_query("begin");
	
	if(!closing())$l_success=0;
	
	$lwhere="is_closing_harian='t'";
	if(!pg_query(insert_log($tbl,$lwhere,'UB')));
	if(!pg_query("update ".$tbl." SET is_closing_harian='f' where ".$lwhere."")) $l_success=0;	
	if(!pg_query(insert_log($tbl,$lwhere,'UA')));	
			
	if ($l_success==1){
		echo 'SUKSES<br>';
		pg_query("commit");
	}else{
		echo 'GAGAL<br>';
		pg_query("rollback");
		if(!pg_query(insert_log($tbl,$lwhere,'UB')));
		if(!pg_query("update ".$tbl." SET is_closing_harian='f' where ".$lwhere."")) $l_success=0;	
		if(!pg_query(insert_log($tbl,$lwhere,'UA')));	
	
	}

}
?>
