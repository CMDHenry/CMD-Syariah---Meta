<?php
include_once("report.php");

function filter_request(){
	global $showBln,$showCab,$showTgl;
	$showCab='t';
	//$showBln='t';
	$showTgl='t';
	
	$flag_inc_0=trim($_REQUEST["flag_inc_0"]);
	if($flag_inc_0==""){
		$flag_inc_0="f";
	}
	
}
function create_filter(){
	global $flag_inc_0;
?>

	<tr bgcolor="efefef">
        <td style="padding:0 5 0 5" width="20%">Inc 0</td>
        <td style="padding:0 5 0 5" width="30%">
        <input type="checkbox" name="flag_inc_0" value="t" <?=(($flag_inc_0=="t")?"checked":"")?> >
        </td>
        <td style="padding:0 5 0 5" width="20%"></td>
        <td style="padding:0 5 0 5" width="30%"></td>
     </tr>

<?

}


function excel_content(){
	global $bulan,$tahun,$fk_cabang,$nm_cabang,$tgl,$flag_inc_0;	
	
	$flag_inc_0=trim($_REQUEST["flag_inc_0"]); //flag inc zero	
	
	$bulan=date('m',strtotime($tgl));
	$tahun=date('Y',strtotime($tgl));
	$tr_month=trim($bulan);
	$tr_year=trim($tahun);
	$time_month=strtotime($tr_month.'/01/'.$tr_year);
	
	$source_schema='data_accounting';
	$time_month_before_lastday=strtotime(date("n/d/Y",$time_month)." -1 days");
	$time_month_before=strtotime(date("n/d/Y",$time_month)."-1 month");
	$time_month_lastday=strtotime(date("n/d/Y",$time_month)."+1 month -1 days");
	$EOM = date("m/d/Y", strtotime('-1 second',strtotime('+1 month',strtotime($tr_month.'/01/'.$tr_year))));
	
	$start_day=date("n/d/Y",$time_month)." 00:00:00";
	//$end_day=date("n/d/Y",$time_month_lastday)." 23:59:59";
	//$start_day=$tgl." 00:00:00";
	$end_day=$tgl." 23:59:59";
	$source_schema='data_accounting';
	
	$l_month_saldo_awal=date("n",$time_month_before);
	$l_year_saldo_awal=date("Y",$time_month_before);
	$l_schema_saldo_awal="data_".$l_year_saldo_awal;

	
	if($fk_cabang){
		if($lwhere)$lwhere .= ' and ';
		$lwhere = " fk_cabang = '".convert_sql($fk_cabang)."' ";
		$lwhere2 = " kd_cabang = '".convert_sql($fk_cabang)."' ";
	}
	if($lwhere)$lwhere = " where ".$lwhere;
	if($lwhere2)$lwhere2 = " where ".$lwhere2;
	
	$query_cabang='select * from tblcabang '.$lwhere2.' order by kd_cabang';
	$lrs_cabang=pg_query($query_cabang);
	while($lrow_cabang=pg_fetch_array($lrs_cabang)){
		$arr_cabang[$lrow_cabang["kd_cabang"]]=$lrow_cabang["nm_cabang"];
	}
		
	if($cashlow=='t'){
		$lwhere1.=" where (coa like '%1100000' or coa like '%1112000')";
	}
	
	$length=strlen(cabang_ho)+2;	
	
	//case when type_saldo = 'Rollover' then saldo_awal else 0 end as 
	$lquery = "
	select * from(
		select sum(case when coa similar to '(1|2|3)%' then saldo_awal else saldo_awal1 end)as saldo_awal,sum(debit) as debit,sum(credit) as credit,coa,tr_type,used_for,type_saldo,fk_cabang from(
			select 
				substring(coa from ".$length.") as coa,tblcoa.description,fk_currency,saldo_awal,saldo_awal1,
				debit,credit,transaction_type as tr_type, used_for,case when substr(coa,1,1)='1' then 'Aktiva' 
					 when substr(coa,1,1)in('2','3') then 'Passiva'
					 when substr(coa,1,1)='4' then 'Pendapatan'
					 when substr(coa,1,1)='5' then 'Biaya'
				end	as tipe,type_saldo,fk_cabang
			from (
				select * from tblcoa ".$lwhere." $lwhere1
			)as tblcoa
			left join tblhead_account on tblcoa.fk_head_account=tblhead_account.code 
			left join (
				select * from(
					select fk_coa,(balance_cash+balance_bank+balance_memorial+balance_gl_auto) as saldo_awal  from
					data_accounting.tblsaldo_coa 
					where tr_month=".$l_month_saldo_awal." and tr_year=".$l_year_saldo_awal."
				)as tblsaldo1
				right join 	(
					select fk_coa as fk_coa1,sum(balance_cash+balance_bank+balance_memorial+balance_gl_auto) as saldo_awal1 from 
					data_accounting.tblsaldo_coa 
					where tr_month<=".$l_month_saldo_awal." and tr_year=".$l_year_saldo_awal."
					group by fk_coa
				) as tblsaldo2 on fk_coa=fk_coa1
			)as tblsaldo_all on tblcoa.coa=fk_coa or tblcoa.coa=fk_coa1
			left join (
				select 
					fk_coa,sum(debit) as debit,sum(credit) as credit
				from (
					select 'gl_auto'||no_bukti,no_bukti,case when fk_coa_d is not null then fk_coa_d else fk_coa_c end as fk_coa,
						case when fk_coa_d is not null then total end as debit,
						case when fk_coa_c is not null then total end as credit
						from ".$source_schema.".tblgl_auto 
					where tr_date>='#".$start_day."#' and tr_date<='#".$end_day."#'
					--and type_owner is not null and fk_owner is not null
				) as tblmain
				group by fk_coa
			) as tbltransaksi on tblcoa.coa=tbltransaksi.fk_coa			
		)as tblmain		
		group by coa,tr_type,used_for,type_saldo,fk_cabang
	)as tblmain2
	left join (select description ,coa as kd_coa from tbltemplate_coa)as tbltemplate_coa on kd_coa=coa
	order by coa
	";
	//showquery($lquery);
	
	$lrs=pg_query($lquery);
	$i=0;
	$data_olahan = array();
	$grand_total = array();
	while ($row=pg_fetch_array($lrs)){
		$row["decimal"] = 0;
		
		if($tr_month == 1 && $row["type_saldo"]!="Rollover"){
			$row["saldo_awal"] =0;
		}
		if($row['used_for'] == "laba_rugi_tahun_berjalan"){
		}elseif($row["saldo_awal"] != 0 || $row["debit"] != 0 || $row["credit"] != 0 || $l_saldo != 0 || $flag_inc_0=="t" ){
			$cabang=$row["fk_cabang"];
			$i=$row["coa"];		
			$data_olahan[$i]["coa"]=$row["coa"];
			
			$data_olahan[$i]["description"]=$row["description"];
			$data_olahan[$i]["type_tr"]=$row["tr_type"];
			
			$data_olahan[$i]["saldo_awal"]=$row["saldo_awal"];
			$grand_total["awal"]+=$row["saldo_awal"];
	
			$data_olahan[$i]["transaksi_debit"]=$row["debit"];
			$grand_total["debit"]+=$row["debit"];
			$data_olahan[$i]["transaksi_kredit"]=$row["credit"];
			$grand_total["kredit"]+=$row["credit"];
	
			if ($row["tr_type"]=="D") $l_saldo=$row["debit"]-$row["credit"];
			else $l_saldo=$row["credit"]-$row["debit"];
			$l_saldo=$row["saldo_awal"]+$l_saldo;
						
			$l_total=$l_saldo;
			$data_olahan[$i]["saldo_akhir"]=$l_total;
			$grand_total["akhir"]+=$l_total;
			
			//$i++;
			$l_saldo=0;//tambahan spy ga muncul yg lain2
						
			$total[$i]["saldo_awal"][$cabang]=$row["saldo_awal"];
			$total[$i]["transaksi_debit"][$cabang]=$row["debit"];
			$total[$i]["transaksi_kredit"][$cabang]=$row["credit"];
			$total[$i]["saldo_akhir"][$cabang]=$l_total;
			
			//$total['Total']["saldo_awal"][$cabang]=$row["saldo_awal"];
			$total['Total']["transaksi_debit"][$cabang]+=$row["debit"];
			$total['Total']["transaksi_kredit"][$cabang]+=$row["credit"];
			
			$total_akhir[$i]["saldo_akhir"]+=$l_total;
			$total_akhir['Total']["saldo_akhir"]+=$l_total;
		}
	}
	
	$i='Total';
	$data_olahan[$i]["coa"]="Total";
	$data_olahan[$i]["saldo_awal"]=$grand_total["awal"];
	$data_olahan[$i]["transaksi_debit"]=$grand_total["debit"];
	$data_olahan[$i]["transaksi_kredit"]=$grand_total["kredit"];
	$data_olahan[$i]["saldo_akhir"]=$grand_total["akhir"];
	
	echo "<table border=1>";
	echo "<tr>";
	echo "<td>COA</td>";
	echo "<td>Description</td>";
	echo "<td>Tipe</td>";
	echo "<td>Grand Total</td>";	
	foreach($arr_cabang as $kd_cabang =>$nm_cabang){
	echo "<td>Saldo Awal ".$kd_cabang."</td>";
	echo "<td>Debit ".$kd_cabang."</td>";
	echo "<td>Kredit ".$kd_cabang."</td>";
	echo "<td>Saldo Akhir ".$kd_cabang."</td>";
	}
	
	echo "</tr>";
/*	for($j=0;$j<=$i;$j++){
		echo "<tr>";
		echo "<td>".$data_olahan[$j]["coa"]."</td>";
		echo "<td>".$data_olahan[$j]["description"]."</td>";
		echo "<td>".strtoupper($data_olahan[$j]["type_tr"])."</td>";
		echo "<td align='right'>".convert_money("",$data_olahan[$j]["saldo_awal"])."</td>";
		echo "<td align='right'>".convert_money("",$data_olahan[$j]["transaksi_debit"])."</td>";
		echo "<td align='right'>".convert_money("",$data_olahan[$j]["transaksi_kredit"])."</td>";
		echo "<td align='right'>".convert_money("",$data_olahan[$j]["saldo_akhir"])."</td>";
		echo "</tr>";
	}
*/
	//print_r($data_olahan);
	foreach($data_olahan as $index=>$temp){
		echo "<tr>";
		echo "<td>".$temp["coa"]."</td>";
		echo "<td>".$temp["description"]."</td>";
		echo "<td>".strtoupper($temp["type_tr"])."</td>";
		echo "<td align='right'>".convert_money("",$total_akhir[$temp["coa"]]["saldo_akhir"])."</td>";
		
		foreach($arr_cabang as $kd_cabang =>$nm_cabang){
		echo "<td align='right'>".convert_money("",$total[$temp["coa"]]["saldo_awal"][$kd_cabang])."</td>";
		echo "<td align='right'>".convert_money("",$total[$temp["coa"]]["transaksi_debit"][$kd_cabang])."</td>";
		echo "<td align='right'>".convert_money("",$total[$temp["coa"]]["transaksi_kredit"][$kd_cabang])."</td>";
		echo "<td align='right'>".convert_money("",$total[$temp["coa"]]["saldo_akhir"][$kd_cabang])."</td>";
		}

		echo "</tr>";
	}


	echo "</table>";
}

