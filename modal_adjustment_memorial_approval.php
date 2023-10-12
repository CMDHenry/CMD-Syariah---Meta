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
$description = $_REQUEST["description"];
$fk_jenis_cabang = $_REQUEST["fk_jenis_cabang"];
if ($status=="Approve") {
	cek_error();
	if (!$strmsg){
		approve();
	}
} elseif ($status=="Reject") {
	cek_error();
	if (!$strmsg){
		reject();
	}
}

get_data();
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
<form action="modal_adjustment_memorial_approval.php" method="post" name="form1" onSubmit="return false">
<input type="hidden" name="status">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="description" value="<?=$description?>">
<input type="hidden" name="fk_cabang" value="<?=$fk_cabang?>">
<input type="hidden" name="hidden_focus">
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
			<table cellspacing="1" cellpadding="0" border="0"  width="100%">
				<tr bgcolor="#c8c8c8" >
					<td style="padding:0 5 0 5">Account</td>
					<td style="padding:0 5 0 5">Description</td>
					<td style="padding:0 5 0 5">Reference</td>
					<td style="padding:0 5 0 5">Type</td>
					<td style="padding:0 5 0 5">Total</td>
					<td style="padding:0 5 0 5">Keterangan</td>
					<td style="padding:0 5 0 5">Kode Cabang</td>                    
				</tr>
				<?
				$total_debit=0;
				$total_credit=0;
				if ($type_tr=="Debit") $total_debit=$total;
				elseif ($type_tr="Credit")$total_credit=$total;
				$lrs=pg_query("
					select 
						fk_account,data_accounting.tbladjust_memorial_detail.description as keterangan, 
						tbltemplate_coa.description as keterangan_coa,type_tr,total,reference_transaksi,
						used_for,modal_view,fk_cabang_detail
					from data_accounting.tbladjust_memorial_detail 
					left join (
						select coa,description,used_for,modal_view from tbltemplate_coa
						left join tbljenis_transaksi on used_for = kd_jenis_transaksi
					)as tbltemplate_coa on fk_account = coa where fk_adjust_memorial='".$id_edit."'
				");
				while($lrow=pg_fetch_array($lrs)){
					if ($lrow["type_tr"]=="D") $total_debit+=$lrow["total"];
					if ($lrow["type_tr"]=="C") $total_credit+=$lrow["total"];
					//echo $lrow['used_for'].$lrow['modal_view']."<br>";
				?>
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td><?=$lrow["fk_account"]?></td>
					<td><?=convert_html($lrow["keterangan_coa"])?></td>
                    <td><?=convert_html($lrow["reference_transaksi"])?></td>
                    <td><?=convert_html($lrow["type_tr"])?></td>
					<td align="right"><?=convert_money("",$lrow["total"],2)?></td>
					<td><?=convert_html($lrow["keterangan"])?></td>
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
               	  <td style="padding:0 5 0 5"><input type="text" class="groove_text" name="keterangan" value="<?=$keterangan?>" style="width:90%" onKeyUp="fNextFocus(event,document.form1.btnsimpan)"></td>
              	</tr>
            </table>
            <table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" align="center">
                <tr height="20">
                	<td height="25" colspan="3" align="center" bgcolor="#D0E4FF">
                        <input class="groove_button" name="btnApprove" type='button' value='Approve' onClick="document.form1.status.value='Approve';submit()">
                       <!-- &nbsp;<input class="groove_button" name="btnReject" type="button" value="Reject" onClick="document.form1.status.value='Reject';submit()">-->
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
	global $strmsg,$j_action,$id_edit,$status,$keterangan,$tr_date,$fk_cabang,$status;
	
	if (pg_num_rows(pg_query("select * from data_accounting.tbladjust_memorial where status='Approved' and no_bukti='".convert_sql($id_edit)."'  for update"))){
		$strmsg.="Transaksi sudah di-approve oleh user lain.<br>";
		$j_action="lInputClose=getObjInputClose();lInputClose.close();";
	}

	if (!$lrow = pg_fetch_array(pg_query("select * from data_accounting.tbladjust_memorial where status='Need Approval' and no_bukti='".convert_sql($id_edit)."'  for update"))){
		$strmsg.="Memorial is invalid to approve.<br>";
	}else{
		$fk_cabang = $lrow['fk_cabang'];
	}

	if($tr_date==''){
		$strmsg.="Tanggal kosong.<br>";
		if(!$j_action) $j_action="document.form1.tr_date.focus()";
	}//elseif($status=='Approve'){
//		if(strtotime($tr_date) > strtotime(date("n/d/Y"))){
//			$strmsg.="Date is bigger than today.<br>";
//			if(!$j_action) $j_action="document.form1.tr_date.focus()";
//		}
//		//if( ! cek_periode_jurnal($tr_date,'memorial',$fk_cabang) ){
////			$strmsg.="Tanggal transaksi diluar periode.<br>";
////			if(!$j_action) $j_action="document.form1.tr_date.focus()";
////		}
//	}

	
	if(!cek_periode_accounting(date("Y",strtotime($tr_date)),date("n",strtotime($tr_date)))){
		//$strmsg.="Tanggal yang diinput, tidak sesuai dengan Periode Accounting.<br>";
		if(!$j_action) $j_action="document.form1.tr_date.focus()";
	}

	if($keterangan==""){
		$strmsg.="Keterangan is require.<br>";
	}
	
	$lrs_detail=pg_query("select * from data_accounting.tbladjust_memorial_detail inner join tbltemplate_coa on fk_account = coa left join tbljenis_transaksi on used_for = kd_jenis_transaksi where fk_adjust_memorial='".convert_sql($id_edit)."'");
	$l_bulan = date('n');
	$l_tahun = date('Y');
	while ($lrow_detail=pg_fetch_array($lrs_detail)){
		
		$l_operator = 1;
		if($lrow_detail["type_tr"] == $lrow_detail["transaction_type"])$l_operator = 1;
		elseif($lrow_detail["type_tr"] != $lrow_detail["transaction_type"])$l_operator = -1;

		$l_kd_jenis_transaksi = $lrow_detail['kd_jenis_transaksi'];
		$l_table_db = $lrow_detail['table_db'];
		$l_field_key = $lrow_detail['field_key'];
		$l_jenis_account = $lrow_detail['jenis_account'];

/*		if($l_table_db!="" && $lrow_detail['reference_transaksi']!=""){ // buka dulu sementara tadi nya if($l_table_db)
		
			if(!$lrow_ap_ar = pg_fetch_array(pg_query("select * from ".$l_table_db." where ".$l_field_key." = '".convert_sql($lrow_detail['reference_transaksi'])."' and bulan = '".$l_bulan."' and tahun = '".$l_tahun."'" ))) $strmsg.="@Detail line ".($i+1)." : Reference Salah.<br>";
			elseif($l_jenis_account != ''){ // klo hutang2an baru cek klo AR mah terima2 aja
				//hitung saldo nya jgn smpe minus atau > dari jumlah transaksi
				$l_result = ($lrow_ap_ar['saldo'] + ( $lrow_detail['total'] * $l_operator ));
				if( $l_result < 0 || $l_result > $lrow_ap_ar['jumlah_transaksi'] )$strmsg.="@Detail line ".($i+1)." : Nominal Melebihi Nilai Tagihan.<br>";
			}
			
			if(date("Ym",strtotime($lrow_ap_ar['tgl_transaksi'])) > date("Ym",strtotime($tr_date)))$strmsg.="@Detail line ".($i+1)." : Tanggal voucher lebih kecil dari transaksi ".$lrow_detail['reference_transaksi'].".<br>";
			if($fk_cabang != $lrow_ap_ar['fk_cabang'])$strmsg.="@Detail line ".($i+1)." : Transaksi ".$lrow_detail['reference_transaksi']." salah.<br>";
			
		}
*/		
		$i++;
	}
	
	
	$lrs_detail=pg_query("select * from data_accounting.tbladjust_memorial_detail inner join tbltemplate_coa on fk_account = coa left join tbljenis_transaksi on used_for = kd_jenis_transaksi where fk_adjust_memorial='".convert_sql($id_edit)."' order by fk_cabang_detail asc");
	//showquery("select * from data_accounting.tbladjust_memorial_detail inner join tbltemplate_coa on fk_account = coa left join tbljenis_transaksi on used_for = kd_jenis_transaksi where fk_adjust_memorial='".convert_sql($id_edit)."' order by fk_cabang_detail asc");
	$arrPost=array();
	$i=0;
	while ($lrow_detail=pg_fetch_array($lrs_detail)){
		
		if($lrow_detail["fk_cabang_detail"])$fk_cabang=$lrow_detail["fk_cabang_detail"];
		
		if($i>0 && $temp_cabang!=$fk_cabang){
			//cek_balance_array_post($arrPost);	
				
			foreach($arrPost as $l_transaksi => $l_arr){
				if($l_arr['type']=='c')$c += $l_arr['value'];
				if($l_arr['type']=='d')$d += $l_arr['value'];
			}
			
			if(number_format($c,2)!=number_format($d,2)){
				$strmsg.="Transaksi cabang ".$fk_cabang." tidak balance, D / C =".number_format($d,2)." / ".number_format($c,2)." .<br>";
			}
			$d=$c=0;
			$arrPost=array();
		}			
		
		$arrPost[$lrow_detail['fk_account'].$i++]	= array('type'=>strtolower($lrow_detail['type_tr']),'value'=>$lrow_detail['value'],'account'=>$fk_cabang.'.'.$lrow_detail['fk_account'],'reference'=>$description);
		$i++;
		$temp_cabang=$fk_cabang;
		
	}
	foreach($arrPost as $l_transaksi => $l_arr){
		if($l_arr['type']=='c')$c += $l_arr['value'];
		if($l_arr['type']=='d')$d += $l_arr['value'];
	}
	//print_r($arrPost);

	//echo $c.'='.$d."=".$fk_cabang.'<br>';
	if(number_format($c,2)!=number_format($d,2)){
		$strmsg.="Transaksi cabang ".$fk_cabang." tidak balance, D / C =".number_format($d,2)." / ".number_format($c,2)." .<br>";
	}
	if($fk_cabang==""){
		$strmsg.="Cabang kosong .<br>";
	}

	//cek_balance_array_post($arrPost);	
	
	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}

function approve(){
	global $strmsg,$j_action,$id_edit,$account,$total,$amount,$type_tr,$status,$keterangan,$tr_date,$description,$fk_cabang;

	$l_success=1;
	pg_query("BEGIN");

	//log begin
	if(!pg_query("insert into data_accounting.tbladjust_memorial_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from data_accounting.tbladjust_memorial where no_bukti='".$id_edit."'")) $l_success=0;
	
	$l_id_log_ub=get_last_id("data_accounting.tbladjust_memorial_log","pk_id_log");
	//end log
	//$fk_cabang=get_rec("tblsetting","fk_cabang_ho");
	if (!pg_query("update data_accounting.tbladjust_memorial set status='Approved',keterangan=".(($keterangan)?"'".convert_sql($keterangan)."'":"NULL").",fk_user_approval='".$_SESSION["kd_karyawan"]."' where no_bukti='".$id_edit."'")) $l_success=0;
	else {
		//log begin
		if(!pg_query("insert into data_accounting.tbladjust_memorial_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from data_accounting.tbladjust_memorial where no_bukti='".$id_edit."'")) $l_success=0;
		
		$l_id_log_ua=get_last_id("data_accounting.tbladjust_memorial_log","pk_id_log");

		if(!pg_query("insert into data_accounting.tbladjust_memorial_detail_log select *,'".$l_id_log_ua."' from data_accounting.tbladjust_memorial_detail where fk_adjust_memorial='".$id_edit."'")) $l_success=0;
		//end log
		$i=0;
		$lrs_detail=pg_query("select * from data_accounting.tbladjust_memorial_detail inner join tbltemplate_coa on fk_account = coa left join tbljenis_transaksi on used_for = kd_jenis_transaksi where fk_adjust_memorial='".convert_sql($id_edit)."' order by fk_cabang_detail asc");
		//showquery("select * from data_accounting.tbladjust_memorial_detail inner join tbltemplate_coa on fk_account = coa left join tbljenis_transaksi on used_for = kd_jenis_transaksi where fk_adjust_memorial='".convert_sql($id_edit)."' order by fk_cabang_detail asc");
		$arrPost=array();
		while ($lrow_detail=pg_fetch_array($lrs_detail)){
			
			if($lrow_detail["fk_cabang_detail"])$fk_cabang=$lrow_detail["fk_cabang_detail"];
			
			if($i>0 && $temp_cabang!=$fk_cabang){
				//cek_balance_array_post($arrPost);
				if(!posting('ADJUSTMENT MEMORIAL',$id_edit,$tr_date,$arrPost,$temp_cabang,'00'))$l_success=0;
				$arrPost=array();
			}			
			if($lrow_detail['value']!=0){
				$arrPost[$lrow_detail['fk_account'].$i++]	= array('type'=>strtolower($lrow_detail['type_tr']),'value'=>$lrow_detail['value'],'account'=>$fk_cabang.'.'.$lrow_detail['fk_account'],'reference'=>$description);
			}
			$i++;
			$temp_cabang=$fk_cabang;
			
		}		
		//cek_balance_array_post($arrPost);
		if(!posting('ADJUSTMENT MEMORIAL',$id_edit,$tr_date,$arrPost,$fk_cabang,'00'))$l_success=0;

		if ($l_success==1) $current_status="Approved";
	}

	//$l_success=0;
	if($l_success==1) {
		$strmsg = "Adjustment Memorial Approved.<br>";
		$j_action="lInputClose=getObjInputClose();lInputClose.close();";
		pg_query("COMMIT");
	} else {
		$strmsg = "Error :<br>Adjustment Memorial Approve failed.<br>";
		pg_query("ROLLBACK");
	}
}

function reject(){
	global $strmsg,$j_action,$id_edit,$account,$total,$amount,$type_tr,$status,$keterangan,$tr_date;

	$l_success=1;
	pg_query("BEGIN");

	//log begin
	if(!pg_query("insert into data_accounting.tbladjust_memorial_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from data_accounting.tbladjust_memorial where no_bukti='".$id_edit."'")) $l_success=0;
	$l_id_log_ub=get_last_id("data_accounting.tbladjust_memorial_log","pk_id_log");
	if(!pg_query("insert into data_accounting.tbladjust_memorial_detail_log select *,'".$l_id_log_ub."' from data_accounting.tbladjust_memorial_detail where fk_adjust_memorial='".$id_edit."'")) $l_success=0;
	//end log

	if (!pg_query("update data_accounting.tbladjust_memorial set status='Rejected',keterangan=".(($keterangan)?"'".convert_sql($keterangan)."'":"NULL").",fk_user_approval='".$_SESSION["ho_kd_karyawan"]."' where no_bukti='".$id_edit."'")) $l_success=0;

	//log begin
	if(!pg_query("insert into data_accounting.tbladjust_memorial_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from data_accounting.tbladjust_memorial where no_bukti='".$id_edit."'")) $l_success=0;
	
	$l_id_log_ua=get_last_id("data_accounting.tbladjust_memorial_log","pk_id_log");

	if(!pg_query("insert into data_accounting.tbladjust_memorial_detail_log select *,'".$l_id_log_ua."' from data_accounting.tbladjust_memorial_detail where fk_adjust_memorial='".$id_edit."'")) $l_success=0;
	//end log

	//if ($l_success==1) $current_status="Approved";
//$l_success=0;
	if($l_success==1) {
		$strmsg = "Adjustment Memorial Rejected.<br>";
		$j_action="lInputClose=getObjInputClose();lInputClose.close();";
		pg_query("COMMIT");
	} else {
		$strmsg = "Error :<br>Adjustment Memorial Reject failed.<br>";
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
/*	if($lrow['status']=='Approved'){
		$strmsg="Error :<br>Transaksi Sudah Diapprove!<br>";
		$j_action="lInputClose=getObjInputClose();lInputClose.close()";
	}
*/

}
?>