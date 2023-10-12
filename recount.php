<?php
require 'requires/config.inc.php';
require 'requires/general.inc.php';
require 'requires/authorization.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/numeric.inc.php';
require 'requires/referer_check.inc.php';
require 'classes/select.class.php';
require 'requires/accounting_utility.inc.php';

$module=$_REQUEST["module"];//kd_menu/fk_menu
get_data_menu($id_menu);
get_data_module();
	

set_time_limit(0);
//$bulan=trim($_REQUEST["bulan"]);
//$tahun=trim($_REQUEST["tahun"]);
$fk_cabang=$_REQUEST["fk_cabang"];
$fk_wilayah=$_REQUEST["fk_wilayah"];
if(!$tahun)$tahun=get_rec("tblsetting","tahun_accounting");
else $tahun=trim($_REQUEST["tahun"]);
if(!$bulan)$bulan=get_rec("tblsetting","bulan_accounting");
else $bulan=trim($_REQUEST["bulan"]);

if($_REQUEST["status"]=="Save") {
	//print_r($_REQUEST);
	//pg_query("BEGIN");
	recount ($bulan,$tahun,$lcoa);
	//if(!$strmsg){
		//add_data();
	//}else pg_query("ROLLBACK");
}

?>
<html>
<head>
	<title>.: <?=__PROJECT_TITLE__?> :.</title>
    <link href="css/text.css.php" rel="stylesheet" type="text/css">
	<link href="css/menu.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src="js/alert.js.php"></script>
<script language='javascript' src="js/dd_menu.js.php"></script>
<script language='javascript' src="js/misc.js.php"></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src="js/object_function.js.php"></script>
<script language='javascript'>
var strOrderBy=""


function fGetCabang(){
	fGetNC(false,'20170900000010','fk_cabang','Ganti Item Kendaraan',document.form1.fk_cabang,document.form1.fk_cabang)
}

function fGetCabangData(){
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataCabangState
	lSentText="table= tblcabang&field=(kd_cabang)&key=kd_cabang&value="+document.form1.fk_cabang.value
	lObjLoad.open("POST","ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}

function fGetDataCabangState(){
	if (this.readyState == 4){
		//confirm(this.responseText)
		if (this.status==200 && this.responseText!="") {
			lTemp=this.responseText.split('¿');
		   //document.getElementById('divNmCabang').innerHTML=document.form1.nm_cabang.value=lTemp[0]
		} else {
			//document.getElementById('divNmCabang').innerHTML=document.form1.nm_cabang.value="-"
		}
	}
}


function fGetWilayah(){
	fGetNC(false,'20171100000062','fk_wilayah','Ganti Item Kendaraan',document.form1.fk_wilayah,document.form1.fk_wilayah)
}

function fGetWilayahData(){
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataWilayahState
	lSentText="table= tblwilayah&field=(kd_wilayah)&key=kd_wilayah&value="+document.form1.fk_wilayah.value
	lObjLoad.open("POST","ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}

function fGetDataWilayahState(){
	if (this.readyState == 4){
		//confirm(this.responseText)
		if (this.status==200 && this.responseText!="") {
			lTemp=this.responseText.split('¿');
		   //document.getElementById('divNmCabang').innerHTML=document.form1.nm_cabang.value=lTemp[0]
		} else {
			//document.getElementById('divNmCabang').innerHTML=document.form1.nm_cabang.value="-"
		}
	}
}



function fSave(){

	document.form1.status.value='Save';
	document.form1.submit();

}

function fLoad(){
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	} /*else {
		if($id_edit)echo "document.form1.retrieve.focus();";
		else echo "document.form1.retrieve.focus();";
	}*/
?>
}
</script>
<body bgcolor="#fafafa" onLoad="fLoad()" onResize="fLoad()">
<form name="form1" action="recount.php" method="post" autocomplete="off">
<input type="hidden" name="status">
<input type="hidden" name="module" value="<?=$module?>">
<input type="hidden" name="_show" value="<?=convert_html($_show)?>">
<table cellpadding="0" cellspacing="0" border="0" width="100%" height="88%">
<!--	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
            	<tr><td class="judul_menu" align="center"><?=strtoupper($nm_menu)?></td></tr>
			</table>
		</td>
	</tr>
-->	<tr height="20"><td align="center" bgcolor="#D0E4FF" class="border">&nbsp;</td></tr>
    <tr>
		<td class="border" valign="top">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%" style="border-bottom:1px solid #aaafff">
				<tr bgcolor="efefef">
					<td width="20%" style="padding:0 5 0 5" class="fontColor">Bulan dan Tahun</td>
			        <td width="80%" style="padding:0 5 0 5">
                  
                    <input type="text" name="bulan" class="groove_text" value="<?=$bulan?>" size="2">
					- <input type="text" name="tahun" class="groove_text" value="<?=$tahun?>" size="4">
					</td>
				</tr>
  		      <tr bgcolor="efefef">
                	<td width="20%" style="padding:0 5 0 5" bgcolor="efefef" class="fontColor">Kode Cabang (2 digit)</td>
         			<td width="30%" style="padding:0 5 0 5" bgcolor="efefef">
                        <? create_list_cabang(); ?>
                    </td>                
                </tr>                
<!--            <tr bgcolor="efefef">
                	<td width="20%" style="padding:0 5 0 5" bgcolor="efefef" class="fontColor">Kode Cabang</td>
         			<td width="30%" style="padding:0 5 0 5" bgcolor="efefef">
                        <input name="fk_cabang" type="text" onKeyPress="if(event.keyCode==4) img_fk_cabang.click();" value="<?=$fk_cabang?>" onChange="fGetCabangData()">&nbsp;<img src="images/search.gif" id="img_fk_cabang" onClick="fGetCabang()" style="border:0px" align="absmiddle">
                    </td>
                
                </tr>
-->              <!--  <tr bgcolor="efefef">
                	<td width="20%" style="padding:0 5 0 5" bgcolor="efefef" class="fontColor">Kode Wilayah</td>
         			<td width="30%" style="padding:0 5 0 5" bgcolor="efefef">
                        <input name="fk_wilayah" type="text" onKeyPress="if(event.keyCode==4) img_fk_wilayah.click();" value="<?=$fk_wilayah?>" onChange="fGetWilayahData()">&nbsp;<img src="images/search.gif" id="img_fk_wilayah" onClick="fGetWilayah()" style="border:0px" align="absmiddle">
                    </td>
                
                </tr>-->
				<tr bgcolor="efefef">
					<td width="20%"></td>
			        <td width="80%" style="padding:0 5 0 5"><input type="button" name="retrieve" value="Recount" onClick="fWait();fSave()"></td>
					<!--img src="images/historis.jpg" onClick="fGenerateData(document.form1._query,document.form1._join)"-->
				</tr>
			</table>
<!--			<div id="divDataTable" style="height:400px;width:800px;overflow:auto"><?=$recount_msg;?></div>
--><!-- end content begin -->
    	</td>
    </tr>
	<tr height="20"><td align="center" bgcolor="#D0E4FF" class="border">&nbsp;</td></tr>
</table>
</form>
</body>
</html>
<?
function create_list_cabang(){
	global $fk_cabang;
	$l_list_obj = new select("select distinct kd_cabang from (select substring(kd_cabang from 1 for 2)as kd_cabang from tblcabang where cabang_active is true)as tblcabang order by kd_cabang","kd_cabang","kd_cabang","fk_cabang");
	$l_list_obj->set_default_value($fk_cabang);
	//$l_list_obj->add_item("--- Pilih ---",'all',0);
	//$l_list_obj->add_item("Head Office",'head_office',1);
	$l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;'",'form1','fView()');
}

function recount ($p_month,$p_year,$p_coa){
	global $strmsg,$db,$password,$recount_msg,$fk_cabang,$j_action,$fk_wilayah;
	
	$l_success=1;
	//pg_query("BEGIN");
	
	$recount_msg="";
	$this_month_date = $p_month."/01/".$p_year;
	$FOM = date('m/d/Y',strtotime('-1 second',strtotime(date('m',strtotime($this_month_date)).'/02/'.date('Y',strtotime($this_month_date)))));
	$EOM = date("m/d/Y", strtotime('-1 second',strtotime('+1 month',strtotime(date('m',strtotime($this_month_date)).'/01/'.date('Y',strtotime($this_month_date))))));
	$last_month_date = date('m/d/Y',strtotime('-1 second',strtotime($this_month_date)));
	$last_month = date('n',strtotime($last_month_date));
	$last_year = date('Y',strtotime($last_month_date));

	//===================================================================
	/*    INSERT CLOSING BULANAN       */
	
	$today_db=get_rec("tblsetting",'tgl_sistem');
	$query="select nextserial('CLS':: text)";
	$lrow=pg_fetch_array(pg_query($query));
	$l_no_closing=$lrow["nextserial"];
	if(!pg_query("insert into data_gadai.tblclosing_bulanan(no_closing,tgl_closing,user_closing,bulan,tahun,fk_cabang,fk_wilayah) values('".$l_no_closing."','".$today_db."','".$_SESSION["username"]."','".$p_month."','".$p_year."','".$fk_cabang."','".$fk_wilayah."')")) $l_success=0;
	//showquery("insert into data_gadai.tblclosing_bulanan(no_closing,tgl_closing,user_closing,bulan,tahun,fk_cabang) values('".$l_no_closing."','".$today_db."','".$_SESSION["username"]."','".$p_month."','".$p_year."','".$fk_cabang."','".$fk_wilayah."')");
	//log begin
	if(!pg_query("insert into data_gadai.tblclosing_bulanan_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from data_gadai.tblclosing_bulanan where no_closing='".$l_no_closing."'"))$l_success=0;  
	//end log
		
	$tgl_live = get_rec("tblsetting","tgl_live");
	if(strtotime($this_month_date) < strtotime($tgl_live) ){
		$strmsg="Error :<br>Recount Tidak Bisa Dibawah Tgl Live.<br>";
		pg_query("ROLLBACK");
		return false;
		exit("Gagal Tersimpan");
	}
	//if ($fk_cabang=='' && $fk_wilayah==''){
//		$strmsg="Error :<br>Kode Cabang Kosong.<br>";
//		pg_query("ROLLBACK");
//		return false;
//		exit("Gagal Tersimpan");
//	}

	$c=0;
	
	$now = date('Y-m-t', strtotime(today_db));
	if($fk_cabang != ''){
		$lwhere2.=" kd_cabang like '".$fk_cabang."%' ";
		//$lwhere2.=" kd_cabang like '%13%' ";
	}
	
	if ($lwhere2!="") $lwhere2=" where ".$lwhere2;
	
	while(strtotime($this_month_date) < strtotime($now)){
		$p_month = date("n",strtotime($this_month_date));
		$p_year = date("Y",strtotime($this_month_date));
		
		$FOM = date('m/d/Y',strtotime('-1 second',strtotime(date('m',strtotime($this_month_date)).'/02/'.date('Y',strtotime($this_month_date)))));
		$EOM = date("m/d/Y", strtotime('-1 second',strtotime('+1 month',strtotime(date('m',strtotime($this_month_date)).'/01/'.date('Y',strtotime($this_month_date))))));
		$last_month_date = date('m/d/Y',strtotime('-1 second',strtotime($this_month_date)));
		$last_month = date('n',strtotime($last_month_date));
		$last_year = date('Y',strtotime($last_month_date));

		//do recount
		//if($fk_cabang)$l_cabang="where kd_cabang ='".$fk_cabang."'";
		$lquery = "
			select * from (
				select * from public.tblcoa 
				left join public.tblhead_account on fk_head_account = code 
				left join (
					select * from data_accounting.tblsaldo_coa 
					where tr_month = '".$last_month."' and tr_year = '".$last_year."'
				)as tblsaldo on fk_coa = coa
				left join (
					select fk_coa as fk_coa1 from data_accounting.tblsaldo_coa 
					where tr_month = '".$p_month."' and tr_year = '".$p_year."'
				)as tblsaldo1 on fk_coa1 = coa				
				inner join(
					select * from tblcabang
					".$lwhere2."
				)as tblcabang on fk_cabang=kd_cabang
				where ".(($l_where)?$l_where:"true")."
			)as tblcoa
			left join (
				select bulan,tahun,sum(memo) as jurnal, fk_account from (
					select 
						extract(month from tr_date) as bulan, 
						extract(year from tr_date) as tahun, 
						fk_account,count(*) as memo
					from (
						select no_bukti,tr_date,case when fk_coa_c is null then fk_coa_d else fk_coa_c end as fk_account 
						from data_accounting.tblgl_auto
					)as tblgl_auto
					group by fk_account, bulan, tahun
				)as tbljurnal
				where bulan = '".$p_month."' and tahun = '".$p_year."'
				group by fk_account,bulan,tahun
			)as tbljurnal on fk_account = coa
			where (balance_gl_auto !=0 or jurnal!=0 or fk_coa1 is not null) and(used_for!='laba_rugi_ditahan' or used_for is null)
			order by coa
		";
		//showquery($lquery);
	
		$rs = pg_query($lquery);
		while($row = pg_fetch_array($rs)){
			
			$p_coa = str_replace(',',"','",$row['coa']);
			$balance_gl_auto = $row['balance_gl_auto']+0;
			$balance_cash = $row['balance_cash']+0;
			$balance_bank = $row['balance_bank']+0;
			$balance_memorial = $row['balance_memorial']+0;
	
			pg_query("
			--insert into data_accounting.tblsaldo_coa_log 
			--select *,'recounting','recounting',
			--'#".date("Y/m/d H:i:s")."#','DB','".$_SERVER['PHP_SELF']."' 
			--from data_accounting.tblsaldo_coa 
			--where fk_coa='".$p_coa."' and tr_month = '".$p_month."' and tr_year = '".$p_year."';
		
			delete from data_accounting.tblsaldo_coa 
			where tr_month = '".$p_month."' and tr_year = '".$p_year."'
			".(($p_coa)?" and fk_coa in ('".$p_coa."')":"")."
			");

			if($row['jurnal'] == "" || $row['jurnal'] == 0) {
				$lquery = "
					insert into data_accounting.tblsaldo_coa(
						fk_coa,tr_month,tr_year,balance_gl_auto,
						balance_cash,balance_bank,balance_memorial
					) values (
						'".$p_coa."','".$p_month."','".$p_year."',
						".(($row['type_saldo']=='Rollover')?"
							'".$balance_gl_auto."','".$balance_cash."',
							'".$balance_bank."','".$balance_memorial."'
						":"
							'0','0','0','0'
						")."
					);
					--insert into data_accounting.tblsaldo_coa_log 
					--select *,'recounting','recounting',
					--'#".date("Y/m/d H:i:s")."#','IA','".$_SERVER['PHP_SELF']."' 
					--from data_accounting.tblsaldo_coa
					--where fk_coa='".$p_coa."' and tr_month = '".$p_month."' 
					--and tr_year = '".$p_year."';
				";
				//showquery($lquery);
				if(!pg_query($lquery)){$recount_msg.="err<br>\r\n";}
				else $recount_msg.= "tidak ada transaksi >> tr_month = ".$p_month." tr_year = ".$p_year." fk_coa = ".$p_coa."<br>\r\n";
				
			}elseif($row['jurnal'] != 0){ // klo ada transaksi
	
				$lquery = "
				select 
					coa,source,balance_gl_auto,balance_cash,balance_bank,balance_memorial,
					rupiah_debit,rupiah_credit,used_for,transaction_type, type_saldo
				from (
					select * from tblcoa ".(($p_coa)?"where coa in ('".$p_coa."')":"")."
				)as tblcoa left join tblhead_account on fk_head_account = code 
				left join (
					select fk_coa,balance_gl_auto,balance_cash,balance_bank,balance_memorial
					from data_accounting.tblsaldo_coa where tr_month = '".$last_month."' and tr_year = '".$last_year."'
				)as tblsaldo_awal on fk_coa = coa
				left join (
					select 
						source,fk_coa,sum(debit) as debit,sum(credit) as credit,
						sum(rupiah_debit) as rupiah_debit,sum(rupiah_credit) as rupiah_credit 
					from (
						select 'gl_auto'||no_bukti,'gl_auto'::text as source,no_bukti,case when fk_coa_d is not null then fk_coa_d else fk_coa_c end as fk_coa,
							case when fk_coa_d is not null then valas end as debit,
							case when fk_coa_c is not null then valas end as credit,
							case when fk_coa_d is not null then total end as rupiah_debit,
							case when fk_coa_c is not null then total end as rupiah_credit
						from data_accounting.tblgl_auto where --type_owner is not null and fk_owner is not null and 
						tr_date>='#".$FOM." 00:00:00#' and tr_date<='#".$EOM." 23:59:59#'
					) as tblmain
					group by fk_coa,source
				)as tbltransaksi on coa = tbltransaksi.fk_coa order by coa
				";//showquery($lquery);
				$rs_coa = pg_query($lquery);
			
				$coa = '';
				while($row = pg_fetch_array($rs_coa)){
					if($coa != $row['coa']){
						$balance_gl_auto = $row['balance_gl_auto']+0;
						$balance_cash = $row['balance_cash']+0;
						$balance_bank = $row['balance_bank']+0;
						$balance_memorial = $row['balance_memorial']+0;
						$coa = $row['coa'];
						$type_saldo = $row['type_saldo'];
						$lquery = "
							insert into data_accounting.tblsaldo_coa (
								fk_coa,tr_month,tr_year,balance_gl_auto,
								balance_cash,balance_bank,balance_memorial
							) values (
								'".$coa."','".$p_month."','".$p_year."',
								".(($type_saldo=='Rollover')?"
									'".$balance_gl_auto."','".$balance_cash."',
									'".$balance_bank."','".$balance_memorial."'
								":"
									'0','0','0','0'
								")."
							);
							--insert into data_accounting.tblsaldo_coa_log 
							--select *,'recounting','recounting',
							--'#".date("Y/m/d H:i:s")."#','IA','".$_SERVER['PHP_SELF']."' 
							--from data_accounting.tblsaldo_coa 
							--where fk_coa='".$coa."' and tr_month = '".$p_month."' 
							--and tr_year = '".$p_year."';
						";
						if(!pg_query($lquery)){/*showquery($lquery)*/;$recount_msg.="<br>\r\n";}
					}
					if($row['source']!=""){
						if($row['transaction_type']=='D'){
							$lquery = "
								--insert into data_accounting.tblsaldo_coa_log 
								--select *,'recounting','recounting',
								--'#".date("Y/m/d H:i:s")."#','UB','".$_SERVER['PHP_SELF']."' 
								--from data_accounting.tblsaldo_coa 
								--where fk_coa='".$coa."' and tr_month = '".$p_month."' 
								--and tr_year = '".$p_year."';
								
								update data_accounting.tblsaldo_coa set 
									balance_".$row['source']." = balance_".$row['source']." + ".(($row['rupiah_debit'])?$row['rupiah_debit']:0).",
									saldo_d = saldo_d + ".(($row['rupiah_debit'])?$row['rupiah_debit']:0)."
								where fk_coa = '".$coa."' and tr_month = '".$p_month."' and tr_year = '".$p_year."';
								update data_accounting.tblsaldo_coa set 
									balance_".$row['source']." = balance_".$row['source']." - ".(($row['rupiah_credit'])?$row['rupiah_credit']:0).",
									saldo_c = saldo_c + ".(($row['rupiah_credit'])?$row['rupiah_credit']:0)."
								where fk_coa = '".$coa."' and tr_month = '".$p_month."' and tr_year = '".$p_year."';
								
								--insert into data_accounting.tblsaldo_coa_log 
								--select *,'recounting','recounting',
								--'#".date("Y/m/d H:i:s")."#','UA','".$_SERVER['PHP_SELF']."' 
								--from data_accounting.tblsaldo_coa 
								--where fk_coa='".$coa."' and tr_month = '".$p_month."' 
								--and tr_year = '".$p_year."';
							";
							if(pg_query($lquery)){/*showquery($lquery);*/$recount_msg.="recount tr_month '".$p_month."' tr_year '".$p_year."' >> fk_coa='".$coa."'<br>\r\n";}
						}else{
							$lquery = "
								--insert into data_accounting.tblsaldo_coa_log 
								--select *,'recounting','recounting',
								--'#".date("Y/m/d H:i:s")."#','UB','".$_SERVER['PHP_SELF']."' 
								--from data_accounting.tblsaldo_coa 
								--where fk_coa='".$coa."' and tr_month = '".$p_month."' 
								--and tr_year = '".$p_year."';
								
								update data_accounting.tblsaldo_coa set 
									balance_".$row['source']." = balance_".$row['source']." - ".(($row['rupiah_debit'])?$row['rupiah_debit']:0).",
									saldo_d = saldo_d + ".(($row['rupiah_debit'])?$row['rupiah_debit']:0)."
								where fk_coa = '".$coa."' and tr_month = '".$p_month."' and tr_year = '".$p_year."';
								update data_accounting.tblsaldo_coa set 
									balance_".$row['source']." = balance_".$row['source']." + ".(($row['rupiah_credit'])?$row['rupiah_credit']:0).",
									saldo_c = saldo_c + ".(($row['rupiah_credit'])?$row['rupiah_credit']:0)."
								where fk_coa = '".$coa."' and tr_month = '".$p_month."' and tr_year = '".$p_year."';
								
								--insert into data_accounting.tblsaldo_coa_log 
								--select *,'recounting','recounting',
								--'#".date("Y/m/d H:i:s")."#','UA','".$_SERVER['PHP_SELF']."' 
								--from data_accounting.tblsaldo_coa 
								--where fk_coa='".$coa."' and tr_month = '".$p_month."' 
								--and tr_year = '".$p_year."';
							";
							if(pg_query($lquery)){/*showquery($lquery);*/$recount_msg.="recount tr_month '".$p_month."' tr_year '".$p_year."' >> fk_coa='".$coa."'<br>\r\n";}
						}
					}
				}
	
			}
	
		}
	
		//thismondate++
		$this_month_date = date("m/d/Y", strtotime('+1 second',strtotime($EOM." 23:59:59")));
		//$recount_msg.= $this_month_date.'-'.date("m/d/Y")."<br>";
		$c++;
		if($c>=100)exit('overloop');
	}	
		
/*	if($bulan_sistem == 12){
		$bulan_update=1;
		$tahun_update=$tahun_sistem+1;	
	} else {
		$bulan_update=$bulan_sistem+1;
		$tahun_update=$tahun_sistem;
	}
*/
	
	//$l_success=0;
	if ($l_success==1){
		$strmsg="Recounting Sukses<br>";
		//$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		$recount_msg='';
		//pg_query("COMMIT");
	}else{
		$strmsg="Error :<br>Recounting Gagal.<br>";
		//pg_query("ROLLBACK");
		//echo $strmsg;
	}	
}

function get_data_module(){
	global $kd_module,$j_action,$nm_tabs;
	
	//query untuk memunculkan tab 
	$lrow_tab_switch=pg_fetch_array(pg_query("select * from skeleton.tblmodule_tabs where fk_module='".$kd_module."' order by no_urut_tabs limit 1"));
	$nm_tabs=$lrow_tab_switch["kd_tabs"];
	$lrow_first_field=pg_fetch_array(pg_query("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' order by no_urut_add limit 1"));
	//$j_action="document.form1.".$lrow_first_field["kd_field"].".focus();";
}

?>