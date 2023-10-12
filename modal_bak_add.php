<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/numeric.inc.php';
require 'requires/accounting_utility.inc.php';
require 'requires/timestamp.inc.php';

$j_action=$_REQUEST["hidden_focus"];
$id_menu=trim($_REQUEST["id_menu"]);

get_data_menu($id_menu);

$strisi=trim($_REQUEST["strisi"]);
$total=trim($_REQUEST["total"]);
$keterangan=trim($_REQUEST["keterangan"]);

$tahun=get_rec("tblsetting","tahun_accounting");
$bulan=get_rec("tblsetting","bulan_accounting");

if($_REQUEST["status"]=="Save") {
	$arr=get_bak();
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

<input type="hidden" name="strisi" value="<?=$strisi?>">
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

function count_bak($saldo_akhir){
	$persen_bak=get_rec("tblsetting","rate_bak");
	//mas 365, sam 360//
	$bak=round($saldo_akhir*($persen_bak/100)/360);
	return $bak;

}
function get_bak(){	
	global $id_edit,$bulan,$tahun,$arr,$total;
	
	$fom=$tahun.'-'.$bulan.'-01';
	$lmonth=date("m",strtotime('-1 day',strtotime($fom)));
	$lyear=date("Y",strtotime('-1 day',strtotime($fom)));
	$jumlah_hari=date('t',strtotime($fom));
	//$jumlah_hari=5;
	
	$query1="
	select sum(masuk)as masuk,sum(keluar)as keluar,tgl,fk_cabang from(
		select 
		case when fk_coa_d is not null then total end as keluar, 
		case when fk_coa_c is not null then total end as masuk,
		fk_cabang,
		extract(day from tr_date) as tgl
		from data_accounting.tblgl_auto
		where extract(month from tr_date)=".$bulan." and extract(year from tr_date)=".$tahun."		
		and (fk_coa_d like '%1971000999' or fk_coa_c like '%1971000999')
		--and fk_cabang='".$lrow["kd_cabang"]."' 
	)as tblgl_auto 
	group by tgl,fk_cabang
	";
	//showquery($query1);
	$lrs1=pg_query($query1);
	$masuk=array();
	$keluar=array();
	while($lrow1=pg_fetch_array($lrs1)){
		$masuk[$lrow1["tgl"]][$lrow1["fk_cabang"]]=$lrow1["masuk"];
		$keluar[$lrow1["tgl"]][$lrow1["fk_cabang"]]=$lrow1["keluar"];		
	}
	
	
	$query="
	select * from tblcabang
	left join (
		select * from data_accounting.tblsaldo_coa 
		left join tblcoa on coa=fk_coa
		where tr_month=".$lmonth." and tr_year=".$lyear." and fk_coa like '%1971000999'		
	)as tblsaldo_coa on fk_cabang=kd_cabang
	where cabang_active='t' and kd_cabang!='999' and kd_cabang not in (select kd_cabang from tblcabang inner join tblwilayah on fk_cabang_wilayah=kd_cabang)
	--and kd_cabang='198'
	order by kd_cabang asc
	";
	//showquery($query);
	$lrs=pg_query($query);
	$arr=array();
	$subtotal=array();
	$total=0;
	while($lrow=pg_fetch_array($lrs)){	
		$saldo_awal=$saldo_akhir=$lrow["balance_gl_auto"]*-1;	

		for($i=1;$i<=$jumlah_hari;$i++){
			$tgl=$i;
			$arr[$lrow["kd_cabang"]][$tgl]['saldo_awal']=$saldo_awal;		
			$arr[$lrow["kd_cabang"]][$tgl]['masuk']=$masuk[$tgl][$lrow["kd_cabang"]];
			$arr[$lrow["kd_cabang"]][$tgl]['keluar']=$keluar[$tgl][$lrow["kd_cabang"]];
			$saldo_akhir+=($masuk[$tgl][$lrow["kd_cabang"]]-$keluar[$tgl][$lrow["kd_cabang"]]);	
			
			$arr[$lrow["kd_cabang"]][$tgl]['saldo_akhir']=$saldo_akhir;
			$bak=count_bak($saldo_akhir);
			$arr[$lrow["kd_cabang"]][$tgl]['nominal']=$bak;
			
			$saldo_awal=$saldo_akhir;
			$total+=$bak;
			$subtotal[$lrow["kd_cabang"]]+=$bak;
		
		}
	}	
	
	return $arr;
}
function view_data(){
	global $id_edit,$query,$lmonth,$lyear,$bulan,$tahun,$arr,$total;
	//echo $id_edit;
	$arr=get_bak();
?>
    <table cellpadding="0" cellspacing="1" border="0" width="100%">
    	<tr tyle="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
			<td colspan="7" align="center">Detail</td>
		</tr>

        <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
        	<td align="center">Kode Cabang </td>
        	<td align="center">Tgl </td>            
        	<td align="center">Saldo Awal </td>
            <td align="center">Saldo Masuk </td>
            <td align="center">Saldo Keluar</td>
            <td align="center">Saldo Akhir</td>
            <td align="center">BAK </td>
        </tr>
<?	
	//print_r($arr);
	if(count($arr)>0){
		foreach($arr as $kd_cabang=>$arr1){
			foreach($arr1 as $tgl=>$arr2){
				//print_r($arr2);
	?>
			<tr style="padding:0 5 0 5" height="20" bgcolor='#efefef'>
				<td style="padding:0 5 0 5" class="" align="center"><?=$kd_cabang?></td>        
				<td style="padding:0 5 0 5" class="" align="center"><?=$tgl?></td>
				<td style="padding:0 5 0 5" class="" align="right"><?=convert_money("",$arr2["saldo_awal"],0)?></td>	
				<td style="padding:0 5 0 5" class="" align="right"><?=convert_money("",$arr2["masuk"],0)?></td>	
				<td style="padding:0 5 0 5" class="" align="right"><?=convert_money("",$arr2["keluar"],0)?></td>	
				<td style="padding:0 5 0 5" class="" align="right"><?=convert_money("",$arr2["saldo_akhir"],0)?></td>	
				<td style="padding:0 5 0 5" class="" align="right"><?=convert_money("",$arr2["nominal"],0)?></td>	
			</tr>
			 
	<?		
				$subtotal[$kd_cabang]+=$arr2["nominal"];
				$total_pt[get_cabang_pt($kd_cabang)]+=$arr2["nominal"];
			}
	?>			
		<tr tyle="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
            <td style="padding:0 5 0 5" align="center" >Sub Total <?=$kd_cabang?></td>
			<td style="padding:0 5 0 5" align="center"  colspan="5" ></td>            
            <td style="padding:0 5 0 5" align="right"><?=convert_money("",$subtotal[$kd_cabang],0)?></td>
        </tr>        
	<?		
		}
	}
?>
		<tr tyle="padding:0 5 0 5" height="20" bgcolor='#efefef' >
            <td style="padding:0 5 0 5" align="center" ></td>
			<td style="padding:0 5 0 5" align="center" colspan="5" ></td>            
            <td style="padding:0 5 0 5" align="right"></td>
        </tr>
<?
	
	if(is_pt=='t'){
		foreach($total_pt as $pt=>$nilai){
?>  
      
		<tr tyle="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
			<td style="padding:0 5 0 5" align="center" >Total <?=get_rec("tblcabang","nm_cabang"," kd_cabang='".$pt."'")?></td>
			<td style="padding:0 5 0 5" align="center"  colspan="5" ></td>            
			<td style="padding:0 5 0 5" align="right"><?=convert_money("",$nilai,0)?></td>
		</tr>
<?			
		}
	}

?>


        <input type="hidden" name="total" value="<?=$total?>">
		<tr tyle="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
            <td style="padding:0 5 0 5" align="center" >Grand Total</td>
			<td style="padding:0 5 0 5" align="center"  colspan="5" ></td>            
            <td style="padding:0 5 0 5" align="right"><?=convert_money("",$total,0)?></td>
        </tr>
    </table>

<?	
	
}

function cek_error($arr){
	global $strmsg,$j_action,$strisi,$keterangan,$bulan,$tahun;
	
	if(pg_num_rows(pg_query("select * from data_accounting.tblgl_auto where extract(month from tr_date)=".$bulan." and extract(year from tr_date)=".$tahun." and type_owner like 'BUNGA ANTAR KANTOR%'"))){
		$strmsg.="BAK bulan '".$bulan."' pada '".$tahun."' sudah pernah dibuat.<br>";
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
	
	$query="select nextserial_transaksi('BAK':: text)";
	$lrow=pg_fetch_array(pg_query($query));
	$l_kd_voucher=$lrow["nextserial_transaksi"];
	
	if(!pg_query("insert into data_fa.tblbunga_antar_kantor(kd_voucher,tgl_voucher,total,bulan,tahun,keterangan)values('".$l_kd_voucher."','".today_db."','".$total."','".$bulan."','".$tahun."','".$keterangan."')")) $l_success=0;
	//showquery("insert into data_fa.tblbunga_antar_kantor(kd_voucher,tgl_voucher,total,bulan,tahun)values('".$l_kd_voucher."','".today_db."','".$total."','".$bulan."','".$tahun."')");
	if(!pg_query("insert into data_fa.tblbunga_antar_kantor_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from data_fa.tblbunga_antar_kantor where kd_voucher='".$l_kd_voucher."' ")) $l_success=0;
				
	$l_id_log=get_last_id("data_fa.tblbunga_antar_kantor_log","pk_id_log");
	//print_r($arr);
	foreach($arr as $kd_cabang=>$arr1){
		$fk_cabang=$kd_cabang;
		foreach($arr1 as $tgl=>$arr2){
			$lquery = "
				insert into data_fa.tblbunga_antar_kantor_detail(fk_voucher,tgl,fk_cabang,saldo_awal,masuk,keluar,saldo_akhir,nominal) values(
					'".$l_kd_voucher."',
					'".convert_sql($bulan.'/'.$tgl.'/'.$tahun)."',
					'".convert_sql($kd_cabang)."',
					".(($arr2['saldo_awal']=="")?"0":"'".round($arr2['saldo_awal'])."'").",
					".(($arr2['masuk']=="")?"0":"'".round($arr2['masuk'])."'").",
					".(($arr2['keluar']=="")?"0":"'".round($arr2['keluar'])."'").",
					".(($arr2['saldo_akhir']=="")?"0":"'".round($arr2['saldo_akhir'])."'").",
					".(($arr2['nominal']=="")?"0":"'".round($arr2['nominal'])."'")."
				)
			";
			//showquery($lquery);
			if(!pg_query($lquery)) $l_success=0;
			$saldo_akhir=$arr2['saldo_akhir'];
			$bak=count_bak($saldo_akhir);
			//echo $bak.'sdfsdfdsf';
			$subtotal[$fk_cabang]+=$bak;
			//echo $subtotal[$fk_cabang];			
		}
		
		if($subtotal[$fk_cabang]>0){
			$arrPost = array();				
			$arrPost["pendapatan_bak"]		        = array('type'=>'d','value'=>$subtotal[$fk_cabang],'reference'=>$keterangan);
			$arrPost["rpkp"]	= array('type'=>'c','value'=>$subtotal[$fk_cabang],'reference'=>$keterangan);	
			//cek_balance_array_post($arrPost);
			if(!posting('BUNGA ANTAR KANTOR (CABANG)',$l_kd_voucher,$eom,$arrPost,$fk_cabang,'00'))$l_success=0;
			
			
			$arrPost = array();	
			$fk_cabang_ho				= (is_pt=='t'?get_cabang_pt($fk_cabang):cabang_ho);		
			$rpkc						= get_coa_cabang($fk_cabang,(is_pt=='t'?$fk_cabang_ho:NULL));						
			$arrPost["rpkc"]      		= array('type'=>'d','value'=>$subtotal[$fk_cabang],'account'=>$rpkc,'reference'=>$keterangan);
			$arrPost["pendapatan_bak"]	= array('type'=>'c','value'=>$subtotal[$fk_cabang],'reference'=>$keterangan);
			//cek_balance_array_post($arrPost);
			if(!posting('BUNGA ANTAR KANTOR (HO)',$l_kd_voucher,$eom,$arrPost,$fk_cabang_ho,'00'))$l_success=0;			
		}
	}
	
	//end log
	if(!pg_query("insert into data_fa.tblbunga_antar_kantor_detail_log select *,'".$l_id_log."' from data_fa.tblbunga_antar_kantor_detail where fk_voucher='".$l_kd_voucher."'")) $l_success=0;
	
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