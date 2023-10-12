<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/numeric.inc.php';
require 'requires/accounting_utility.inc.php';
require 'requires/timestamp.inc.php';

$id_edit=$_REQUEST["id_edit"];
$tr_date=$_REQUEST["tr_date"];
if($tr_date)$tr_date=convert_date_english($tr_date);
$status = $_REQUEST["status"];
$keterangan = $_REQUEST["keterangan"];
$fk_cabang=$_REQUEST["fk_cabang"];
$nm_cabang=$_REQUEST["nm_cabang"];
$fk_jenis_cabang=$_REQUEST["fk_jenis_cabang"];

get_data();

//echo $tr_date."aa";

if ($status=="Simpan") {
	cek_error();
	if (!$strmsg){
		cancel();
	}
}
?>
<html>
<head>
	<title>.: SUKA FAJAR :.</title>
    <link href="css/text.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src="js/alert.js.php"></script>
<script>
function fLoad(){
	//parent.parent.document.title='adjust_memorial'
	<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function(){eval("'.$j_action.'")});';
	}elseif($j_action){
		echo $j_action;
	}else{
		echo "document.form1.keterangan.focus();";
	}
	?>
}
function fAction(pStatus,pID){
	if (pStatus=="detail_coa") {
		show_modal('modal_chart_of_account_detail.php?pstatus=detail&id_edit='+pID,'dialogwidth:825px;dialogHeight:345px');
	}
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")) {
		lInputClose=getObjInputClose();lInputClose.close()
	}
}

</script>
<body bgcolor="#fafafa" onLoad="fLoad();document.form1.autocomplete='off'"> 
<form action="modal_adjustment_memorial_batal.php" method="post" name="form1" onSubmit="return false">
<input type="hidden" name="status">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="hidden_focus">
<input type="hidden" name="fk_cabang" value="<?=$fk_cabang?>">
<input type="hidden" name="fk_jenis_cabang" value="<?=$fk_jenis_cabang?>">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<td class="judul_menu" align="center">ADJUSTMENT MEMORIAL</td>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border" style="border-bottom:none">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5"width="20%">No. Bukti</td>
					<td  style="padding:0 5 0 5"width="30%">
                    <?=$no_bukti?>
                    </td>
					<td style="padding:0 5 0 5"width="20%">Date</td>
					<td  style="padding:0 5 0 5"width="30%">
                    <input type="hidden" value="<?=convert_date_indonesia($tr_date)?>" name="tr_date"><?=convert_date_indonesia($tr_date)?>
                    </td>
				</tr>
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5"width="20%" >Description</td>
					<td  style="padding:0 5 0 5"width="30%" colspan="3"><?=$description?></td>
				</tr>
                
                <tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5"width="20%">Kode Cabang</td>
					<td  style="padding:0 5 0 5"width="30%">
                    <?=$fk_cabang?>
                    </td>
                    
                    <td style="padding:0 5 0 5"width="20%">Nama Cabang</td>
					<td  style="padding:0 5 0 5"width="30%">
                    <?=$nm_cabang?>
                    </td>
                </tr>
			</table>
			<table cellspacing="1" cellpadding="1" border="0"  width="100%">
				<tr bgcolor="#c8c8c8" align="center">
					<td>Account</td>
                    <td>Description</td>
                    <td>Reference</td>
					<td>Type</td>                   
                    <td>Total</td>
					<td>Keterangan</td>
					<td style="padding:0 5 0 5">Kode Cabang</td>                    
                    
				</tr>
				<?
				$total_debit=0;
				$total_credit=0;
				if ($type_tr=="Debit") $total_debit=$total;
				elseif ($type_tr="Credit")$total_credit=$total;
				$lrs=pg_query("select fk_account,total,type_tr,data_accounting.tbladjust_memorial_detail.description, tbltemplate_coa.description as description_coa,value,rate,total,reference_transaksi,fk_cabang_detail from data_accounting.tbladjust_memorial_detail left join tbltemplate_coa on fk_account = coa where fk_adjust_memorial='".$id_edit."'");
				while($lrow=pg_fetch_array($lrs)){
					if ($lrow["type_tr"]=="D") $total_debit+=$lrow["total"];
					if ($lrow["type_tr"]=="C") $total_credit+=$lrow["total"];
				?>
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td><?=$lrow["fk_account"]?></td>
                    <td><?=convert_html($lrow["description_coa"])?></td>
                    <td><?=convert_html($lrow["reference_transaksi"])?></td>
                    <td><?=$lrow["type_tr"]?></td>
                    <td align="right"><?=convert_money("",$lrow["total"],2)?></td>
                    <td><?=convert_html($lrow["description"])?></td>
					<td><?=convert_html($lrow["fk_cabang_detail"])?></td>      
				</tr>
				<?
				}
				?>
			</table>
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%" bgcolor="#c8c8c8">Total Debit (Rp)</td>
					<td style="padding:0 5 0 5" width="30%" bgcolor="#c8c8c8" align="right">
						<?=convert_money("Rp ",$total_debit,2)?>
					</td>
					<td style="padding:0 5 0 5" width="20%" bgcolor="#c8c8c8">Total Credit (Rp)</td>
					<td style="padding:0 5 0 5" width="30%" bgcolor="#c8c8c8" align="right">
						<?=convert_money("Rp ",$total_credit,2)?>
					</td>
				</tr>
			</table>
            <table  cellpadding="0" cellspacing="1" border="0" width="100%">
            	<tr height="20" style="padding:0 5 0 5" bgcolor='#efefef'>
                	<td width="20%" class="fontColor" style="padding:0 5 0 5">Keterangan</td>
               	  <td style="padding:0 5 0 5"><input type="text" class="groove_text" name="keterangan" value="<?=$keterangan?>" size="125" onKeyUp="fNextFocus(event,document.form1.btnsimpan)"></td>
              	</tr>
            </table>
            <table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" align="center">
                <tr height="20">
                	<td height="25" colspan="3" align="center" bgcolor="#D0E4FF">
                        <input class="groove_button" name="btnsimpan" type='button' value='Simpan' onClick="document.form1.status.value='Simpan';submit()">
                        <input class="groove_button" name="btnBatal" type='button' value='Batal' onClick="fBatal()">
                    <td>
                </tr>
            </table>
         </td>
    </tr>
</table>
</form>
</body>
</html>
<?
function cek_error(){
	global $strmsg,$j_action,$id_edit,$status,$keterangan,$tr_date,$fk_cabang;
	
/*	$lrs = pg_fetch_array(pg_query("select back_date_transaksi_memorial from tblcabang where kd_cabang = '".$fk_cabang."'"));
	$l_back_date = $lrs["back_date_transaksi_memorial"];
	if ($l_back_date) $tgl_back_date = date("n/d/Y",strtotime(date("n/d/Y") . "-".$l_back_date." days"));
	else $tgl_back_date = date("n/d/Y");
*/	
	if($keterangan==""){
		$strmsg.="Keterangan Kosong.<br>";
	}
	$lrow=pg_fetch_array(pg_query("select * from data_accounting.tbladjust_memorial where no_bukti='".$id_edit."' for update"));
	if($lrow['tgl_batal']!=''){
		$strmsg="Error :<br>Transaksi Sudah Dibatalkan!<br>";
		$j_action="lInputClose=getObjInputClose();lInputClose.close()";
	}
	if($tr_date==''){
		$strmsg.="Tanggal kosong.<br>";
		if(!$j_action) $j_action="document.form1.tr_date.focus()";
	}elseif($lrow['status']=='Approved'){
		if(strtotime($tr_date) < strtotime($tgl_back_date)){
			//$strmsg.="Date is smaller than back date allowed.<br>";
			if(!$j_action) $j_action="document.form1.tr_date.focus()";		
		}
	//	if( ! cek_periode_jurnal($tr_date,'memorial',$fk_cabang) ){
	//		$strmsg.="Tanggal transaksi diluar periode.<br>";
	//		if(!$j_action) $j_action="document.form1.tr_date.focus()";
	//	}
	}

	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}

function cancel(){
	global $strmsg,$j_action,$id_edit,$account,$total,$amount,$type_tr,$status,$keterangan,$tr_date;

	$l_success=1;
	pg_query("BEGIN");

	//log begin
	if(!pg_query("insert into data_accounting.tbladjust_memorial_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from data_accounting.tbladjust_memorial where no_bukti='".$id_edit."'")) $l_success=0;
	
	$l_id_log_ub=get_last_id("data_accounting.tbladjust_memorial_log","pk_id_log");
	//end log

	//get status approval
	$lrow_adjust_memorial = pg_fetch_array(pg_query("select * from data_accounting.tbladjust_memorial where no_bukti='".$id_edit."' for update"));
	$tr_date = $lrow_adjust_memorial['tr_date'];
	$l_status_transaksi = $lrow_adjust_memorial['status'];
	//end status approval

	if (!pg_query("update data_accounting.tbladjust_memorial set status='Batal',tgl_batal='".date('m/d/Y H:i:s')."',keterangan_batal='".convert_sql($keterangan)."',fk_user_approval = '".$_SESSION["kd_karyawan"]."' where no_bukti='".$id_edit."'")) $l_success=0;
	else {
		if($l_status_transaksi=='Approved'){ // klo dr approved maka perlu di normalisasi
			//log begin
			if(!pg_query("insert into data_accounting.tbladjust_memorial_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from data_accounting.tbladjust_memorial where no_bukti='".$id_edit."'")) $l_success=0;
			
			$l_id_log_ua=get_last_id("data_accounting.tbladjust_memorial_log","pk_id_log");
			if(!pg_query("insert into data_accounting.tbladjust_memorial_detail_log select *,'".$l_id_log_ua."' from data_accounting.tbladjust_memorial_detail where fk_adjust_memorial='".$id_edit."'")) $l_success=0;
			//end log

			$lrs_detail=pg_query("select * from data_accounting.tbladjust_memorial_detail inner join tbltemplate_coa on fk_account = coa left join tbljenis_transaksi on used_for = kd_jenis_transaksi where fk_adjust_memorial='".convert_sql($id_edit)."'");
			while ($lrow_detail=pg_fetch_array($lrs_detail)){
	
				//klo ap atau ar maka update saldo di tbl ap ar
/*				if($lrow_detail['jenis_account'] == 'ar' || $lrow_detail['jenis_account'] == 'ap'){
					//func ap ato ar
					$l_update_function = 'update_saldo_'.$lrow_detail['jenis_account'];
				
					//query2tambahan ada di db utility
					$l_arr_update = query_tambahan($_SERVER['PHP_SELF'],$tr_date,$lrow_detail,'batal');
					if(is_array($l_arr_update)){
						foreach($l_arr_update as $l_arr_update_field => $l_arr_update_value){
							$l_arr_update[$l_arr_update_field] = $l_arr_update_value * $lrow_detail["total"];
						}
					}
	
					//do update saldo ap/ar
					//print_r($l_arr_update);
					if($lrow_detail['reference_transaksi'])if(!$l_update_function($lrow_detail['reference_transaksi'],$lrow_detail['used_for'],$tr_date,$l_arr_update,$_SESSION['kd_cabang']))$l_success=0;
				}
*/				//echo $l_success;
				//if (!update_saldo_coa("memorial",$lrow_detail["fk_account"],$lrow_detail["type_tr"],($lrow_detail["total"]*-1),$tr_date)) $l_success=0;
				
			}
			$type_owner='ADJUSTMENT MEMORIAL';
			$fk_owner=$id_edit;
			//echo $l_success;
			$arrPost = gl_balik($fk_owner,$type_owner,$description);
			//cek_balance_array_post($arrPost);
			if(!posting('BATAL '.$type_owner,$fk_owner,today_db,$arrPost,$fk_cabang,'00'))$l_success=0;
			//echo $l_success;
		}//end if lstatus approve
	}

	//if ($l_success==1) $current_status="Approved";
//	$l_success=0;
	if($l_success==1) {
		$strmsg = "Adjustment Memorial Cancelled.<br>";
		$j_action="lInputClose=getObjInputClose();lInputClose.close();";
		pg_query("COMMIT");
	} else {
		$strmsg = "Error :<br>Adjustment Memorial cancellation failed.<br>";
		pg_query("ROLLBACK");
	}
}

function get_data(){
	global $id_edit,$strmsg,$j_action,$no_bukti,$tr_date,$description,$fk_cabang,$nm_cabang;

	$lrow=pg_fetch_array(pg_query("select * from data_accounting.tbladjust_memorial left join (select kd_cabang, nm_cabang from tblcabang) as tblcabang on kd_cabang=fk_cabang where no_bukti='".$id_edit."'"));
	$no_bukti=$lrow["no_bukti"];
	$tr_date=(($lrow["tr_date"]!="")?date('m/d/Y',strtotime($lrow["tr_date"])):"");
	$description=$lrow["description"];
	$fk_cabang=$lrow["fk_cabang"];
	$nm_cabang=$lrow["nm_cabang"];
	if($lrow['tgl_batal']!=''){
		$strmsg="Error :<br>Transaksi Sudah Dibatalkan!<br>";
		$j_action="lInputClose=getObjInputClose();lInputClose.close()";
	}
	if($lrow['status']=='Approved'){
		//$strmsg="Error :<br>Transaksi Sudah Diapprove!<br>";
		//$j_action="lInputClose=getObjInputClose();lInputClose.close()";
	}
	
}

?>