<?php
require 'requires/config.inc.php';
require 'requires/general.inc.php';
require 'requires/numeric.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/stok_utility.inc.php';
require 'classes/select.class.php';
require 'requires/accounting_utility.inc.php';

//$strisi = trim($_REQUEST["strisi"]);
//

	
	pg_query("insert into tblcoa select  (kd_cabang||'.'||coa)::text as coa,fk_head_account,tbltemplate_coa.fk_jenis_cabang,tblcabang.kd_cabang,
transaction_type,type_of_coa,description,additional_desc,fk_currency,begin_balance,used_for,rk_dg_cabang,is_active,tbltemplate_coa.fk_merek,rk_dg_divisi from (
  select '1'::text as connector, * from tbltemplate_coa
 ) as tbltemplate_coa 
 inner join (
  select '1'::text as connector,tblcabang.kd_accounting as kode_accounting,* from tblcabang
  left join tbljenis_cabang on fk_jenis_cabang = kd_jenis_cabang
 ) as tblcabang on tblcabang.connector=tbltemplate_coa.connector
 where   (kd_cabang||'.'||coa)::text  not in (select coa from tblcoa);");
	
?>
