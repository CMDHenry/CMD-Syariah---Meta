<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/module.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/file.inc.php';
require 'requires/numeric.inc.php';
require 'requires/accounting_utility.inc.php';

$j_action=$_REQUEST["hidden_focus"];
$id_menu=trim($_REQUEST["id_menu"]);

get_data_menu($id_menu);

$strisi=trim($_REQUEST["strisi"]);
$total=trim($_REQUEST["total"]);
$keterangan=trim($_REQUEST["keterangan"]);

$tahun=get_rec("tblsetting","tahun_accounting");
$bulan=get_rec("tblsetting","bulan_accounting");

if($_REQUEST["status"]=="Save") {
	$arr=get_data_view();
	cek_error($arr);
	if(!$strmsg){
		save_data($arr);
	}
}

?>
<html>
<head>
	<title>.: <?=$_SESSION["application"]?> :.</title>
	<link href="css/cwcalendar.css.php" rel="stylesheet" type="text/css">
    <link href="css/text.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src="js/alert.js.php"></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src="js/calendar.js.php"></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src='js/input_format_number.js.php'></script>
<!--<script language='javascript' src="js/table_v2.js.php"></script>-->
<script language='javascript' src="js/table_adjustment_memorial.js.php"></script>
<script language='javascript' src="js/validate.js.php"></script>
<script language='javascript'>

function fSave(){
	document.form1.status.value='Save';
	document.form1.submit();
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")) {
		lInputClose=getObjInputClose();lInputClose.close()
	}
}


function fLoad(){
	//grantotal(Table_Detil);
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	}elseif($j_action){
		echo $j_action;
	}else{
		echo "document.form1.keterangan.focus();";
	}
?>
}
</script>
<body onLoad="fLoad();document.form1.autocomplete='off'" bgcolor="#fafafa">
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="form1" enctype="multipart/form-data">
<input type="hidden" name="status">
<input type="hidden" name="id_menu" value="<?=$id_menu?>">
<input type="hidden" name="hidden_focus">

<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<td class="judul_menu" align="center"><?=strtoupper($nm_menu)?></td>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border" id="tdContent">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5"width="10%" class="fontColor">Periode Accounting</td>
					<td style="padding:0 5 0 5"width="40%"><input type="hidden" name="bulan" class="groove_text" value="<?=$bulan?>" size="4"><?=$bulan?> 
					- <input type="hidden" name="tahun" class="groove_text" value="<?=$tahun?>" size="4"><?=$tahun?>
                </tr>
                
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="10%" class="fontColor">Keterangan</td>
					<td style="padding:0 5 0 5" width="40%"><input name="keterangan" type="text" class='groove_text ' size="80" value="<?=$keterangan?>"></td>
                </tr>
          
		  	</table>
		<? view_data();
		?>
        </td>
    </tr>
    <tr height="20">
        <td height="25" align="center" bgcolor="#D0E4FF" class="border">
            <input class="groove_button" name="btnsimpan" type='button' value='Simpan' onClick="fSave()">
            &nbsp;<input class="groove_button" type="button" value="Batal" onClick="fBatal()"></td>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?

function get_data_view(){	
	global $id_edit,$bulan,$tahun,$arr,$total;
		
	$fom=$p_date=$tahun.'-'.$bulan.'-01';
	$eom=$tahun.'-'.$bulan.'-'.date('t',strtotime($fom));
	$last_month=date("Y-m-d",strtotime('-1 second',strtotime($fom)));
	$bulan_lalu=date('m',strtotime($last_month));
	$tahun_lalu=date('Y',strtotime($last_month));
	//echo $last_month;
	$query="
		select jenis_produk,sum(case when fk_sbg_fintech is null then booking else 0 end) as total_pokok, tblinventory.fk_cabang from (
			select sum(pokok_jt)as  booking,fk_sbg as no_sbg from data_fa.tblangsuran
			".where_os_tblangsuran($eom)."
			group by fk_sbg		
		)as tblar
		inner join tblinventory on fk_sbg = no_sbg
		left join (			
				select 'FINTECH' as fintech,tblfintech_ar.fk_sbg as fk_sbg_fintech,tgl_ar,tgl_ap from data_fa.tblfintech_ar		
				left join data_fa.tblfintech_ap on tblfintech_ar.fk_sbg=tblfintech_ap.fk_sbg
				where tgl_ar <='".$eom."' and (tgl_ap is null or tgl_ap>'".$eom."')
		)as tblfintech on no_sbg = fk_sbg_fintech
		left join tblproduk on fk_produk=kd_produk
		".where_os_tblinventory($eom)." 
		group by fk_cabang,jenis_produk		
	";
	//showquery($query);
	$lrs=pg_query($query);
	$arr=array();
	$subtotal=array();
	$total=0;
	$rate_piutang_ragu=get_rec("tblsetting","rate_piutang_ragu");
	while($lrow=pg_fetch_array($lrs)){	
		$fk_cabang=$lrow["fk_cabang"];
		$total_cadangan=round($lrow["total_pokok"]*$rate_piutang_ragu/100);
	
		if($lrow["jenis_produk"]=='0')$cadangan_piutang="cadangan_piutang_ragu_ragu";
		elseif($lrow["jenis_produk"]=='1')$cadangan_piutang="cadangan_piutang_tak_tertagih";
	
		$lbalance = get_balance($cadangan_piutang,'',$fk_cabang,$bulan_lalu,$tahun_lalu)*-1;
		
		if($lbalance>$total_cadangan)$nominal=0;
		else $nominal=$total_cadangan-$lbalance;

		$arr[$lrow["fk_cabang"]][$cadangan_piutang]['saldo_awal']=$lbalance;		
		$arr[$lrow["fk_cabang"]][$cadangan_piutang]['total_os']=$lrow["total_pokok"];
		$arr[$lrow["fk_cabang"]][$cadangan_piutang]['total_cadangan']=$total_cadangan;		
		$arr[$lrow["fk_cabang"]][$cadangan_piutang]['nominal']=round($nominal);

		$total+=round($nominal);
	}	
	
	return $arr;
}
function view_data(){
	global $id_edit,$query,$lmonth,$lyear,$bulan,$tahun,$arr,$total;
	//echo $id_edit;
	$arr=get_data_view();
?>
    <table cellpadding="0" cellspacing="1" border="0" width="100%">
    	<tr tyle="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
			<td colspan="6" align="center">Detail</td>
		</tr>

        <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
        	<td align="center">Kode Cabang </td>
			<td align="center">COA </td>          
            <td align="center">Total OS </td>
            <td align="center">Total Cadangan </td>              
        	<td align="center">Saldo Cadangan </td>
            <td align="center">Nilai Jurnal</td>
        </tr>
<?	
	//print_r($arr);
	if(count($arr)>0){
		ksort($arr);
		foreach($arr as $kd_cabang=>$arr1){
			foreach($arr1 as $cadangan_piutang=>$arr2){
			//print_r($arr2);
			$desc=get_rec("tbltemplate_coa","description","used_for='".$cadangan_piutang."'");
			$coa=get_rec("tbltemplate_coa","coa","used_for='".$cadangan_piutang."'");			
	?>
			<tr style="padding:0 5 0 5" height="20" bgcolor='#efefef'>
				<td style="padding:0 5 0 5" class="" align="center"><?=$kd_cabang?></td>      
				<td style="padding:0 5 0 5" class="" align="center"><?=$desc?></td>     
				<td style="padding:0 5 0 5" class="" align="right"><?=convert_money("",$arr2["total_os"],0)?></td>	
				<td style="padding:0 5 0 5" class="" align="right"><?=convert_money("",$arr2["total_cadangan"],0)?></td>	
				<td style="padding:0 5 0 5" class="" align="right"><?=convert_money("",$arr2["saldo_awal"],0)?></td>	
				<td style="padding:0 5 0 5" class="" align="right"><?=convert_money("",$arr2["nominal"],0)?></td>	
			</tr>
			 
	<?		
				$subtotal[$kd_cabang]+=$arr1["nominal"];
			}
		
		}
	}
?>
        <input type="hidden" name="total" value="<?=$total?>">
		<tr tyle="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
            <td style="padding:0 5 0 5" align="center" >Grand Total</td>
			<td style="padding:0 5 0 5" align="center"  colspan="4" ></td>            
            <td style="padding:0 5 0 5" align="right"><?=convert_money("",$total,0)?></td>
        </tr>
    </table>

<?	
	
}

function cek_error($arr){
	global $strmsg,$j_action,$strisi,$keterangan,$bulan,$tahun;
	
	//showquery("select * from data_accounting.tblgl_auto where extract(month from tr_date)=".$bulan." and extract(year from tr_date)=".$tahun." and type_owner='PENCADANGAN PIUTANG'");
	if(pg_num_rows(pg_query("select * from data_accounting.tblgl_auto where extract(month from tr_date)=".$bulan." and extract(year from tr_date)=".$tahun." and type_owner='PENCADANGAN PIUTANG'"))){
		$strmsg.="Cadangan Piutang bulan '".$bulan."' pada '".$tahun."' sudah pernah dibuat.<br>";
	}
	
	$tr_date=$tahun.'-'.$bulan.'-01';
	if(!cek_periode_accounting(date("Y",strtotime($tr_date)),date("n",strtotime($tr_date)))){
		$strmsg.="Periode yang diinput, tidak sesuai dengan periode Accounting <br>";
		if(!$j_action) $j_action="document.form1.tr_date.focus()";
	}
	
	if(count($arr)==0){
		$strmsg.="Detail Kosong.<br>";
	}
	if($keterangan==""){
		$strmsg.="Keterangan Kosong.<br>";
	}
	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}

function save_data($arr){
	global $strmsg,$j_action,$strisi,$bulan,$tahun,$total,$keterangan;

	$l_success=1;
	pg_query("BEGIN");
	
	$fom=$bulan.'/01/'.$tahun;
	$eom=date("Y-m-t",strtotime($fom));
	
	$query="select nextserial_transaksi('CAD':: text)";
	$lrow=pg_fetch_array(pg_query($query));
	$l_kd_voucher=$lrow["nextserial_transaksi"];
	
	if(!pg_query("insert into data_fa.tblcadangan_piutang(kd_voucher,tgl_voucher,total,bulan,tahun,keterangan)values('".$l_kd_voucher."','".today_db."','".$total."','".$bulan."','".$tahun."','".$keterangan."')")) $l_success=0;
	//showquery("insert into data_fa.tblcadangan_piutang(kd_voucher,tgl_voucher,total,bulan,tahun)values('".$l_kd_voucher."','".today_db."','".$total."','".$bulan."','".$tahun."')");
	if(!pg_query("insert into data_fa.tblcadangan_piutang_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from data_fa.tblcadangan_piutang where kd_voucher='".$l_kd_voucher."' ")) $l_success=0;
				
	$l_id_log=get_last_id("data_fa.tblcadangan_piutang_log","pk_id_log");
	//print_r($arr);
	foreach($arr as $kd_cabang=>$arr1){
		$fk_cabang=$kd_cabang;
		foreach($arr1 as $cadangan_piutang=>$arr2){
			if($arr2['nominal']>0){
				$lquery = "
					insert into data_fa.tblcadangan_piutang_detail(fk_voucher,fk_cabang,saldo_awal,total_os,total_cadangan,nominal,fk_used_for) values(
						'".$l_kd_voucher."',
						'".convert_sql($kd_cabang)."',
						".(($arr2['saldo_awal']=="")?"0":"'".round($arr2['saldo_awal'])."'").",
						".(($arr2['total_os']=="")?"0":"'".round($arr2['total_os'])."'").",
						".(($arr2['total_cadangan']=="")?"0":"'".round($arr2['total_cadangan'])."'").",
						".(($arr2['nominal']=="")?"0":"'".round($arr2['nominal'])."'").",
						'".convert_sql($cadangan_piutang)."'
					)
				";
				//showquery($lquery);
				if(!pg_query($lquery)) $l_success=0;
				
				$arrPost = array();		
				$arrPost["biaya_cadangan_piutang"]		= array('type'=>'d','value'=>$arr2['nominal']);
				$arrPost[$cadangan_piutang]				= array('type'=>'c','value'=>$arr2['nominal']);
				//cek_balance_array_post($arrPost);
				//echo $EOM;
				if(!posting('PENCADANGAN PIUTANG',date("Ymd",strtotime($eom)),$eom,$arrPost,$fk_cabang,'00'))$l_success=0;	
			}
		}

	}
	
	//end log
	if(!pg_query("insert into data_fa.tblcadangan_piutang_detail_log select *,'".$l_id_log."' from data_fa.tblcadangan_piutang_detail where fk_voucher='".$l_kd_voucher."'")) $l_success=0;
	
	//$l_success=0;
	if($l_success==1) {
		$strmsg = "Data saved.<br>";
		$strisi="";
		$j_action="lInputClose=getObjInputClose();lInputClose.close()";
		$tr_date=date('m/d/Y');
		$description="";
		pg_query("COMMIT");
	} else {
		$strmsg = "Error :<br>Data save failed.<br>";
	    pg_query("ROLLBACK");
	}
}




?>