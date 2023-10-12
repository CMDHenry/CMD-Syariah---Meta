<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/accounting_utility.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/numeric.inc.php';
require 'requires/text.inc.php';
require 'requires/timestamp.inc.php';
require 'classes/select.class.php';

//if (!check_right("HO10141110,HO10141111")){
//	header("location:error_access.php");
//}

$id_edit			= trim($_REQUEST["id_edit"]);

$fk_cabang			= trim($_REQUEST["fk_cabang"]);
$fk_merek			= trim($_REQUEST["fk_merek"]);
$coa				= trim($_REQUEST["coa"]);
$fk_head_account	= trim($_REQUEST["fk_head_account"]);
$kd_accounting 		= trim($_REQUEST["kd_accounting"]);
$detail_account		= trim($_REQUEST["detail_account"]);
$detail_account2	= trim($_REQUEST["detail_account2"]);
$description		= trim($_REQUEST["description"]);
$additional_desc	= trim($_REQUEST["additional_desc"]);
$transaction_type	= trim($_REQUEST["transaction_type"]);
$currency			= trim($_REQUEST["currency"]);
$type_of_coa		= trim($_REQUEST["type_of_coa"]);
//if($id_edit)$begin_balance = get_current_balance($id_edit);
//else{
$begin_balance	= round($_REQUEST["begin_balance"]);
//if($begin_balance)$begin_balance=convert_money_english($begin_balance);
//}
$used_for			= trim($_REQUEST["used_for"]);
$radio_used_for		= trim($_REQUEST["radio_used_for"]);
$rk_dg_cabang		= trim($_REQUEST["rk_dg_cabang"]);
$account_merek 		= trim($_REQUEST["account_merek"]);
$account_jenis_cabang = trim($_REQUEST["account_jenis_cabang"]);
$account_cabang 		= trim($_REQUEST["account_cabang"]);

$is_petty_cash=trim($_REQUEST["is_petty_cash"]);
if($is_petty_cash==""){
	$is_petty_cash="f";
}

$is_rekon_bank=trim($_REQUEST["is_rekon_bank"]);
if($is_rekon_bank==""){
	$is_rekon_bank="f";
}

$is_payment_request=trim($_REQUEST["is_payment_request"]);
if($is_payment_request==""){
	$is_payment_request="f";
}

$is_active=trim($_REQUEST["is_active"]);
if($is_active==""){
	$is_active="f";
}
//echo $account_cabang.'ooo';

if($_REQUEST["status"]=="Save") {
	cek_error();
	if(!$strmsg){
		if ($id_edit) edit_data();
		else add_data();
	}
}
if($_REQUEST["pstatus"]){
	get_data();
}
//get_data_account();
?>
<html>
<head>
	<title>.: SUKA FAJAR :.</title>
	<link href="css/cwcalendar.css.php" rel="stylesheet" type="text/css">
    <link href="css/text.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src="js/alert.js.php"></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src="js/validate.js.php"></script>
<script language='javascript' src='js/input_format_number.js.php'></script>
<script language='javascript'>

function cekError(){
	var lAlerttxt="";
	var lFocuscursor="";
	if(document.form1.id_edit.value==""){
	//	if (document.form1.fk_head_account.value==""){
//			lAlerttxt+='Pilih Head Account<br>';
//			if(lFocuscursor==""){lFocuscursor="document.form1.fk_head_account";}
//		}
		//if (document.form1.detail_account.value==""){
//			lAlerttxt+='Chart of Account Kosong<br>';
//			if(lFocuscursor==""){lFocuscursor="document.form1.detail_account";}
//		}
	}
	if (document.form1.transaction_type.value==""){
		lAlerttxt+='Transaction type Kosong<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.transaction_type";}
	}
	//if (document.form1.begin_balance.value==""){
//		lAlerttxt+='Begin Balance Kosong<br>';
//		if(lFocuscursor==""){lFocuscursor="document.form1.begin_balance";}
//	}
	
//	flag = true
//	if(document.form1.radio_used_for.length > 0){
//		for(i=0;i < document.form1.radio_used_for.length;i++)if(document.form1.radio_used_for[i].checked==true){flag = false; break;}
//		if(flag){
//			lAlerttxt+='Used For belum dipilih<br>';
//			if(lFocuscursor==""){lFocuscursor="document.form1.used_for";}
//		}
//	}
	if(lAlerttxt!=""){
		alert("Error : <br>"+lAlerttxt,function(){eval(lFocuscursor+'.focus()')});
		return false
	} else return true;
}

function fSave(){
	if (cekError()) {
		document.form1.status.value='Save';
		document.form1.submit();
	}
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")) {
		lInputClose=getObjInputClose();lInputClose.close()
	}
}

function fReset(){
	document.form1.used_for.value = ''
	if(document.form1.radio_used_for.length > 0)for(i=0;i < document.form1.radio_used_for.length;i++)document.form1.radio_used_for[i].checked=false
	document.form1.rk_dg_cabang.value = ''

}

function fPilih(pObj){
	document.form1.used_for.value = pObj.value
	document.form1.rk_dg_cabang.value = ''
}

function fChange(pObj){
	document.form1.rk_dg_cabang.value = pObj.value
}

function fLoad(){
	//parent.parent.document.title="Chart of Account";
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function () {'.$j_action.'});';
	}
	else if($j_action){
		echo $j_action;
	}else{
		if($id_edit){
			echo "document.form1.description.focus();";
		}else{
			echo "document.form1.description.focus();";
		}
	}
?>
}
</script>
<body onLoad="fLoad();document.form1.autocomplete='off'" bgcolor="#fafafa">
<form action="modal_chart_of_account.php" method="post" name="form1">
<input type="hidden" name="status">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="used_for" value="<?=$used_for?>">
<input type="hidden" name="rk_dg_cabang" value="<?=$rk_dg_cabang?>">
<input type="hidden" name="fk_cabang" value="<?=$fk_cabang?>">
<input type="hidden" name="fk_merek" value="<?=$fk_merek?>">
<input type="hidden" name="account_merek" value="<?=convert_html($account_merek)?>">
<!--<input type="hidden" name="account_cabang" value="<?=convert_html($account_cabang)?>">
--><table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<td class="judul_menu" align="center">CHART OF ACCOUNT</td>
			</table>
		</td>
	</tr>
					
 <!--                   .
                    <? //convert_html($account_cabang)?>
                    <?=create_list_cabang()?>
                    .
                    <?=create_list_jenis_cabang()?>
                    .
                    <input type="text" name="detail_account" size="2" class="groove_text" value="<?=$detail_account?>" onKeyUp="fNextFocus(event,document.form1.detail_account2)" maxlength="2">
                    <!--&nbsp;.&nbsp;<input type="text="detail_account2" size="2" class="groove_text" value="<?=$detail_account2?>" onKeyUp="fNextFocus(event,document.form1.description)" maxlength="2"-->  
    <tr>
      	<td class="border" style="border-bottom:none">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%" class="fontColor">Chart Of Account</td>
                    <? if($id_edit!=""){?>
					<td style="padding:0 5 0 5" colspan="3" width="80%"><input type="hidden" name="coa" value="<?=$coa?>" class="groove_text"><?=$coa?> </td>
                    <?
					   }else{
					?>
					<td style="padding:0 5 0 5" colspan="3" width="80%">
						<!--<?=create_list_head_account()?>&nbsp;--><input type="text" name="coa" value="<?=$coa?>" class="groove_text">
                         Head Account &nbsp;<?=create_list_head_account()?>
                    </td>
                   	<?
					   }
					?>
				</tr>
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%">Description</td>
					<td style="padding:0 5 0 5" width="30%" colspan="3"><input type="text" style="width:97%" name="description" class="groove_text" value="<?=convert_html($description)?>" onKeyUp="fNextFocus(event,document.form1.additional_desc)"></td>
				</tr>
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%">Additional Desc</td>
					<td style="padding:0 5 0 5" width="30%" colspan="3"><input type="text" style="width:97%" name="additional_desc" class="groove_text" value="<?=convert_html($additional_desc)?>" onKeyUp="fNextFocus(event,document.form1.transaction_type)"></td>
				</tr>
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%" class="fontColor">Transaction Type</td>
					<td style="padding:0 5 0 5" width="30%" colspan="3">
                   
					<select name="transaction_type" onKeyUp="fNextFocus(event,document.form1.type_of_coa)"  class="groove_text">
						<option value="">--Transaction Type--</option>
						<option value="C" <?=(($transaction_type=="C")?"selected":"")?>>C</option>
						<option value="D"<?=(($transaction_type=="D")?"selected":"")?>>D</option>
					</select>
                   
                    </td>
				</tr>
               <? if($id_edit!=""){?>
               <tr bgcolor="efefef">
                    <td style="padding:0 5 0 5" width="20%">Active</td>
                    <td style="padding:0 5 0 5" width="30%">
                    <input type="checkbox" name="is_active" value="t" <?=(($is_active=="t")?"checked":"")?> >
                    </td>
                    <td style="padding:0 5 0 5" width="20%"></td>
                    <td style="padding:0 5 0 5" width="30%"></td>
                 </tr>
				<? }?>                
<!--				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%">Currency</td>
					<td style="padding:0 5 0 5" width="30%" colspan="3">
                    	<input type="hidden" name="currency" class="groove_text">Rp</td>
				</tr>
-->				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%">Type of COA</td>
					<td style="padding:0 5 0 5" width="30%" colspan="3">
                    
                    <!--<input type="hidden" name="type_of_coa" value="<?=$type_of_coa?>"><?=$type_of_coa?>-->
                    
                   	<!--<select name="type_of_coa" onKeyUp="fNextFocus(event,document.form1.begin_balance_mask)"  class="groove_text">
                        <option value="">--Type of COA--</option>-->
                        <!--<option value="Cash" <?=(($type_of_coa=="Cash")?"selected":"")?>>Cash</option>
                        <option value="Bank" <?=(($type_of_coa=="Bank")?"selected":"")?>>Bank</option>
                        <option value="Inventory" <?=(($type_of_coa=="Inventory")?"selected":"")?>>Inventory</option>-->
                        <!--<option value="Petty Cash" <?=(($type_of_coa=="Petty Cash")?"selected":"")?>>Petty Cash</option>
                        <option value="Rekon Bank" <?=(($type_of_coa=="Rekon Bank")?"selected":"")?>>Rekon Bank</option>
                    </select>-->
                    <input type="checkbox" name="is_petty_cash" class="groove_text" value="t" <?=(($is_petty_cash=="t")?"checked disable":"")?> onKeyUp="fNextFocus(event,document.form1.is_rekon_bank)"> Petty Cash
                    <input type="checkbox" name="is_rekon_bank" class="groove_text" value="t" <?=(($is_rekon_bank=="t")?"checked disable":"")?> onKeyUp="fNextFocus(event,document.form1.is_payment_request)"> Rekon Bank
                    <input type="checkbox" name="is_payment_request" class="groove_text" value="t" <?=(($is_payment_request=="t")?"checked disable":"")?> onKeyUp="fNextFocus(event,document.form1.description)"> Payment Request
                    </td>
				</tr>
<!--				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%" class="<?=(($id_edit)?"":"fontColor")?>"><?=(($id_edit)?"Current":"Begin")?> Balance</td>
					<td style="padding:0 5 0 5" width="30%" colspan="3">
                    <? if($id_edit == ""){
						 create_input_number($begin_balance,"begin_balance","Begin Balance","fNextFocus(event,document.form1.btnsimpan);",'','',2);
					}else{
					?>
                    	<input type="<?=(($id_edit)?"hidden":"text")?>" style="width:97%" name="begin_balance" onChange="this.value = number_format(this.value,true)" onKeyUp="fNextFocus(event,document.form1.btnsimpan)" class="groove_text_numeric" value="<?=convert_money('',$begin_balance,2)?>" ><span style="display:<?=(($id_edit)?'inline':'none')?>"><?=convert_money('',$begin_balance,2)?></span>
                    <?
					}
					?>
                    </td>
				</tr>
                <tr bgcolor="aaafff"><td colspan="4" height="1"></td></tr>
				<tr style="padding:0 5 0 5" class="header" bgcolor="e0e0e0">
					<td style="padding:0 5 0 5" colspan="4">Digunakan Untuk</td>
				</tr>
-->		  	</table>
            <!--<div style="overflow:scroll;height:200px">
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
                <? //create_list_jenis_transaksi() ?>
			</table>
			</div>-->
		</td>
	</tr>
</table>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" align="center">
	<tr height="20"><td height="25" align="center" bgcolor="#D0E4FF">
		<input class="groove_button" name="btnsimpan" type='button' value='Simpan' onClick="fSave()">
		&nbsp;<input class="groove_button" type="button" value="Batal" onClick="fBatal()"></td>
	</tr>
</table>
</form>
</body>
</html>
<?
function create_list_jenis_transaksi(){
	global $id_edit,$used_for,$fk_cabang,$fk_jenis_cabang;

	$lrs = pg_query("select distinct on (jenis_transaksi,fk_cabang) * from (select * from tbljenis_transaksi where is_hidden is false and is_active is true)as tbljenis_transaksi left join (select * from tbltemplate_coa where fk_cabang = '".convert_sql($fk_cabang)."' and fk_jenis_cabang = '".convert_sql($fk_jenis_cabang)."')as tbltemplate_coa on used_for = kd_jenis_transaksi order by jenis_transaksi,fk_cabang");

	$lindex=0;
	echo '<tr bgcolor="efefef"><td colspan=4 style="padding:0 5 0 5"><a href="#" class="blue" onClick="fReset()">Reset</a></td></tr>';
	while($lrow = pg_fetch_array($lrs)){
		if($lindex%4==0 || $lindex==0)echo '<tr bgcolor="efefef">'; // row open

		echo '<td width="15"><input type="radio" name="radio_used_for" value="'.$lrow['kd_jenis_transaksi'].'" class="groove_checkbox" '.(($used_for==$lrow['kd_jenis_transaksi'])?"checked":"").' '.(($lrow['coa'] && $lrow['rk_dg_cabang']=='')?"disabled":"").' onclick="fPilih(this)"></td>';
		echo '<td width="48%" style="padding:0 5 0 5">'.$lrow['jenis_transaksi'].'&nbsp;&nbsp;';

		if($lrow['get_list_cabang'])create_list_cabang($lrow['get_list_cabang'],$lrow['kd_jenis_transaksi']);
		
		echo '</td>';
		$lindex+=2;
		if($lindex%4==0)echo '</tr>'; // row close
	}
	if($lindex%4!=0)echo'<td colspan="2"></td></tr>'; // kalau ganjil bikin row close
}
/*
function create_list_cabang($ptype,$pname){
	global $rk_dg_cabang,$fk_cabang;
    $l_list_obj = new select("
		select 
			kd_cabang,nm_cabang||' ('||jenis_cabang||') '||case when rk_dg_cabang is null 
			then '' else ' - '||coa end as nm_cabang 
		from (
			select 
				kd_cabang||'".chr(187)."'||kd_jenis_cabang as kd_cabang,kd_cabang as kd_cabang_coa,
				nm_cabang,kd_jenis_cabang,jenis_cabang
			from tblcabang,tbljenis_cabang where is_aktif is true 
			".(($ptype=='all')?"":"and is_area = ".(($ptype=='dealer')?"TRUE":"FALSE")."")." 
		) as tblcabang 
		left join (
			select * from tblcoa  --distinct on (fk_cabang) 
			where used_for = '".strtolower($pname)."' 
			and fk_cabang = '".convert_sql($fk_cabang)."'
		)as tblcoa on tblcoa.rk_dg_cabang = tblcabang.kd_cabang_coa 
		and tblcoa.rk_dg_divisi = tblcabang.kd_jenis_cabang
		order by kd_cabang
	","nm_cabang","kd_cabang",$pname);

    $l_list_obj->set_default_value($rk_dg_cabang);
    $l_list_obj->add_item("-- Pilih --",'',0);
    $l_list_obj->html("class='groove_text' style='width:225px;background-color:#ffffff;border-color:#999999;'","form1","fChange(this)");
}*/
function create_list_cabang(){
	global $rk_dg_cabang,$fk_cabang,$account_cabang;
    $l_list_obj = new select("
		select * from tblcabang
		where kd_accounting is not null
		order by kd_cabang
	","kd_accounting","kd_accounting","account_cabang");

    //$l_list_obj->set_default_value($rk_dg_cabang);
    $l_list_obj->add_item("-- Pilih --",'',0);
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;'","form1","fChange(this)");
}
function create_list_jenis_cabang(){
	global $account_jenis_cabang;
    $l_list_obj = new select("select *,kd_accounting||' - '||jenis_cabang as nama from tbljenis_cabang order by kd_accounting","nama","kd_accounting","account_jenis_cabang");
    $l_list_obj->set_default_value($account_jenis_cabang);
    $l_list_obj->add_item("-- Divisi --",'',0);
    $l_list_obj->html("class='groove_text' style='width:120px;background-color:#ffffff;border-color:#999999;' onKeyUp='fNextFocus(event,document.form1.detail_account)'");
}

function create_list_head_account(){
	global $fk_head_account;
    $l_list_obj = new select("select *,code||' - '||description as nama from tblhead_account order by code","nama","code","fk_head_account");
    $l_list_obj->set_default_value($fk_head_account);
    $l_list_obj->add_item("-- Head Account --",'',0);
    $l_list_obj->html("class='groove_text' style='width:120px;background-color:#ffffff;border-color:#999999;' onKeyUp='fNextFocus(event,document.form1.detail_account)'");
}

function cek_error(){
	global $strmsg,$j_action,$fk_head_account,$fk_cabang,$detail_account,$kd_accounting,$transaction_type,$begin_balance,$id_edit,$fk_jenis_cabang,$coa,$used_for,$rk_dg_cabang,$detail_account2,$account_merek,$account_jenis_cabang,$account_cabang,$description;

	$kd_accounting = $account_cabang.".".$account_jenis_cabang;
	//echo $kd_accounting;
	if($id_edit==""){
		//$coa='';
		if($fk_head_account==""){
			$strmsg.="Pilih Head Account.<br>";
			if(!$j_action) $j_action="document.form1.fk_head_account.focus()";
		}
		if($coa==""){
			$strmsg.="Pilih COA.<br>";
			if(!$j_action) $j_action="document.form1.coa.focus()";
		}
		
	/*		
		elseif (!pg_num_rows(pg_query("select * from tblhead_account where code='".$fk_head_account."' for update"))) {
			$strmsg.="Pilih Head Account Belum Terdaftar.<br>";
			if(!$j_action) $j_action="document.form1.fk_head_account.focus()";
		}else $coa = $fk_head_account.'.'.$kd_accounting;

		if($detail_account==""){
			$strmsg.="Chart of Account Kosong.<br>";
			if(!$j_action) $j_action="document.form1.detail_account.focus()";
		}else $coa = $coa.'.'.$detail_account;*/

		if(pg_num_rows(pg_query("select * from tbltemplate_coa where coa='".convert_sql($coa)."' for update")) > 0){
			$strmsg.="Chart of Account Sudah Terdaftar.<br>";
			if(!$j_action) $j_action="document.form1.fk_head_account.focus()";
		}
		
	}
	if(strlen($coa)!='7'){
		$strmsg.="Chart of Account harus 7 digit.<br>";
	}
	
	if($used_for){
		if(!$lrow = pg_fetch_array(pg_query("select * from tbljenis_transaksi where kd_jenis_transaksi = '".convert_sql($used_for)."' for update"))){
			$strmsg.="Jenis Transaksi tidak terdaftar.<br>";
			if(!$j_action) $j_action="document.form1.used_for.focus()";
		}elseif($lrow['get_list_cabang'] != '' && $rk_dg_cabang == ''){
			$strmsg.="Pilih Cabang.<br>";
			if(!$j_action) $j_action="document.form1.rk_dg_cabang.focus()";
		} 
		//else $description = $lrow['jenis_transaksi'].get_rec("tblcabang","nm_cabang","kd_cabang = '".convert_sql($fk_cabang)."'");
		$l_tmp = explode(chr(187),$rk_dg_cabang);

		if(pg_num_rows(pg_query("select * from tbltemplate_coa where coa <> '".convert_sql($coa)."' and used_for = '".convert_sql($used_for)."' ".(($rk_dg_cabang=='')?"":" and rk_dg_cabang = '".convert_sql($l_tmp[0])."' and rk_dg_divisi = '".convert_sql($l_tmp[1])."'")." and fk_jenis_cabang = '".convert_sql($fk_jenis_cabang)."' for update")) > 0){
			$strmsg.="Transaksi yg sama sudah digunakan pada account yg lain.<br>";
			if(!$j_action) $j_action="document.form1.transaction_type.focus()";
		}
	}else $rk_dg_cabang='';

	if($transaction_type==""){
		$strmsg.="Transaction Type Kosong.<br>";
		if(!$j_action) $j_action="document.form1.transaction_type.focus()";
	}

/*	if($begin_balance==""){
		$strmsg.="Begin Balance Kosong.<br>";
		if(!$j_action) $j_action="document.form1.begin_balance.focus()";
	}else if(check_type("float",$begin_balance)){
		$strmsg.="Begin Balance Salah.<br>";
		if(!$j_action) $j_action="document.form1.begin_balance.focus()";
	}
*/	//echo $coa;
	if ($strmsg) $strmsg="Error :<br>".$strmsg;
}

function edit_data(){
	global $strmsg,$j_action,$description,$additional_desc,$transaction_type,$type_of_coa,$begin_balance,$id_edit,$used_for,$rk_dg_cabang,$fk_cabang,$is_petty_cash,$is_rekon_bank,$is_payment_request,$is_active;

	$l_success=1;
	pg_query("BEGIN");
	$type_of_coa=NULL;
	if($is_petty_cash=='t'){
		$type_of_coa='Petty Cash,';
	}
	if($is_rekon_bank=='t'){
		$type_of_coa.='Rekon Bank,';
	}
	if($is_payment_request=='t'){
		$type_of_coa.='Payment Request,';
	}
	//log begin
	if(!pg_query("insert into tbltemplate_coa_log select *,'".$_SESSION["ho_id"]."','".$_SESSION["ho_username"]."','#".date("Y/m/d H:i:s")."#','UB' from tbltemplate_coa where coa='".$id_edit."'")) $l_success=0;
	//end log
	//pecah karena isi nya itu > 01>>S, dsb
	if($rk_dg_cabang)$l_tmp = explode(chr(187),$rk_dg_cabang);
	
	if(!pg_query("
		update tbltemplate_coa set 
			description=".(($description)?"'".convert_sql($description)."'":"null").",
			additional_desc=".(($additional_desc)?"'".convert_sql($additional_desc)."'":"null").",
			transaction_type='".convert_sql($transaction_type)."',
			type_of_coa=".(($type_of_coa)?"'".convert_sql($type_of_coa)."'":"null").", 
			rk_dg_cabang = ".(($rk_dg_cabang=='')?"NULL":"'".convert_sql($l_tmp[0])."'").", 
			rk_dg_divisi = ".(($rk_dg_cabang=='')?"NULL":"'".convert_sql($l_tmp[1])."'").",
			is_active='".convert_sql($is_active)."'
			--,used_for = ".(($used_for=='')?"NULL":"'".convert_sql($used_for)."'")." 
		where coa='".$id_edit."'
	")) $l_success=0;
/*	showquery("
		update tbltemplate_coa set 
			description=".(($description)?"'".convert_sql($description)."'":"null").",
			additional_desc=".(($additional_desc)?"'".convert_sql($additional_desc)."'":"null").",
			transaction_type='".convert_sql($transaction_type)."',
			type_of_coa=".(($type_of_coa)?"'".convert_sql($type_of_coa)."'":"null").", 
			rk_dg_cabang = ".(($rk_dg_cabang=='')?"NULL":"'".convert_sql($l_tmp[0])."'").", 
			rk_dg_divisi = ".(($rk_dg_cabang=='')?"NULL":"'".convert_sql($l_tmp[1])."'").",
			is_active='".convert_sql($is_active)."'
			--,used_for = ".(($used_for=='')?"NULL":"'".convert_sql($used_for)."'")." 
		where coa='".$id_edit."'
	");
*/	//log begin
	if(!pg_query("insert into tbltemplate_coa_log select *,'".$_SESSION["ho_id"]."','".$_SESSION["ho_username"]."','#".date("Y/m/d H:i:s")."#','UA' from tbltemplate_coa where coa='".$id_edit."'")) $l_success=0;
	//end log
	
	if(!pg_query("
		update tblcoa set 
			description=".(($description)?"'".convert_sql($description)."'":"null").",
			transaction_type='".convert_sql($transaction_type)."'
		where coa like '%".$id_edit."'
	")) $l_success=0;
	
/*	showquery("
		update tblcoa set 
			description=".(($description)?"'".convert_sql($description)."'":"null").",
			transaction_type='".convert_sql($transaction_type)."'
		where coa like '%".$id_edit."'");
*/	
	//$l_success=0;
	if ($l_success==1){
		$strmsg="Chart of Account Tersimpan.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		pg_query("COMMIT");
	}else{
		$strmsg="Error :<br>Chart of Account Gagal Tersimpan.<br>";
		pg_query("ROLLBACK");
	}
}


function add_data(){
	global $strmsg,$j_action,$fk_head_account,$fk_cabang,$detail_account,$description,$additional_desc,$transaction_type,$type_of_coa,$begin_balance,$fk_jenis_cabang,$account_jenis_cabang,$coa,$used_for,$rk_dg_cabang,$fk_cabang,$detail_account2,$is_petty_cash,$is_rekon_bank,$is_payment_request;
	
	$l_success=1;
	pg_query("BEGIN");
	$type_of_coa=NULL;
	if($is_petty_cash=='t'){
		$type_of_coa='Petty Cash,';
	}
	if($is_rekon_bank=='t'){
		$type_of_coa.='Rekon Bank,';
	}
	if($is_payment_request=='t'){
		$type_of_coa.='Payment Request,';
	}
	//pecah karena isi nya itu > 01>>S, dsb
	if($rk_dg_cabang)$l_tmp = explode(chr(187),$rk_dg_cabang);
//	$row_cabang = pg_fetch_array(pg_query("select fk_merek,fk_jenis_cabang from tblcabang where kd_cabang = '".$fk_cabang."'"));
	//$kd_jenis_cabang = get_rec("tbljenis_cabang","kd_jenis_cabang","kd_accounting='".$account_jenis_cabang."'");
	$kd_jenis_cabang= substr($coa,-2);
	//echo $kd_jenis_cabang;
	$fk_cabang='000';
	
	//$fk_head_account=substr($coa,0,3);
	//$fk_head_account=substr($coa,0,2).'00';
	if(!pg_query("
		insert into tbltemplate_coa (
			coa,fk_head_account,fk_jenis_cabang,fk_cabang,description,additional_desc,
			transaction_type,type_of_coa,begin_balance,used_for,rk_dg_cabang,rk_dg_divisi,fk_merek
		) values(
			'".convert_sql($coa)."','".convert_sql($fk_head_account)."','".convert_sql($kd_jenis_cabang)."',
			'".convert_sql($fk_cabang)."',".(($description)?"'".convert_sql($description)."'":"null").",
			".(($additional_desc)?"'".convert_sql($additional_desc)."'":"null").",
			'".convert_sql($transaction_type)."',".(($type_of_coa)?"'".convert_sql($type_of_coa)."'":"null").",
			'".convert_sql($begin_balance)."',
			".(($used_for)?"'".convert_sql($used_for)."'":"null").",
			".(($rk_dg_cabang=='')?"NULL":"'".convert_sql($l_tmp[0])."'").", 
			".(($rk_dg_cabang=='')?"NULL":"'".convert_sql($l_tmp[1])."'").", 
			'".convert_sql($row_cabang["fk_merek"])."'
		)
	")) $l_success=0;
/*	showquery("
		insert into tbltemplate_coa (
			coa,fk_head_account,fk_jenis_cabang,fk_cabang,description,additional_desc,
			transaction_type,type_of_coa,begin_balance,used_for,rk_dg_cabang,rk_dg_divisi,fk_merek
		) values(
			'".convert_sql($coa)."','".convert_sql($fk_head_account)."','".convert_sql($kd_jenis_cabang)."',
			'".convert_sql($fk_cabang)."',".(($description)?"'".convert_sql($description)."'":"null").",
			".(($additional_desc)?"'".convert_sql($additional_desc)."'":"null").",
			'".convert_sql($transaction_type)."',".(($type_of_coa)?"'".convert_sql($type_of_coa)."'":"null").",
			'".convert_sql($begin_balance)."',
			".(($used_for)?"'".convert_sql($used_for)."'":"null").",
			".(($rk_dg_cabang=='')?"NULL":"'".convert_sql($l_tmp[0])."'").", 
			".(($rk_dg_cabang=='')?"NULL":"'".convert_sql($l_tmp[1])."'").", 
			'".convert_sql($row_cabang["fk_merek"])."'
		)
	");
*/	$lid_coa = $coa;
	$LM = date('m/d/Y',strtotime('-1 second',strtotime(date('m').'/01/'.date('Y'))));
	//$l_success=create_saldo_coa($lid_coa,$LM,true,$begin_balance,'gl_auto');

	//log begin
	if(!pg_query("insert into tbltemplate_coa_log select *,'".$_SESSION["ho_id"]."','".$_SESSION["ho_username"]."','#".date("Y/m/d H:i:s")."#','IA' from tbltemplate_coa where coa='".$lid_coa."'")) $l_success=0;
	//end log
	//$l_success=0;
	if($l_success==1) {
		$strmsg = "Chart of Account Tersimpan.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";		
		$account_jenis_cabang='';
		$fk_head_account="";
		$fk_jenis_cabang="";
		$detail_account="";
		$detail_account2="";
		$detail_account_3="";
		$detail_account_4="";
		$description="";
		$additional_desc="";
		$transaction_type="";
		$type_of_coa="";
		$begin_balance="";
		$used_for='';
		$rk_dg_cabang='';
		pg_query("COMMIT");
	} else {
		$strmsg = "Error:<br>Chart of Account Gagal Tersimpan.<br>";
		pg_query("ROLLBACK");
	}
}

function get_data(){
	global $strmsg,$j_action,$coa,$description,$additional_desc,$transaction_type,$fk_cabang,
		   $type_of_coa,$begin_balance,$id_edit,$used_for,$rk_dg_cabang,$is_petty_cash,$is_rekon_bank,$is_payment_request,$is_active;

	$lrow=pg_fetch_array(pg_query("select * from tbltemplate_coa where coa = '".$id_edit."'"));
	
	$coa = $lrow["coa"];
	$description = $lrow["description"];
	$additional_desc = $lrow["additional_desc"];
	$transaction_type = $lrow["transaction_type"];
	$type_of_coa = $lrow["type_of_coa"];
	$begin_balance = $lrow["begin_balance"];
	$fk_cabang = $lrow["fk_cabang"];
	$used_for = $lrow["used_for"];
	$rk_dg_cabang = $lrow["rk_dg_cabang"].chr(187).$lrow["rk_dg_divisi"];
	$is_active = $lrow["is_active"];
	if(strstr($type_of_coa,'Petty Cash')){
		$is_petty_cash='t';
	}
	if(strstr($type_of_coa,'Rekon Bank')){
		$is_rekon_bank='t';
	}
	if(strstr($type_of_coa,'Payment Request')){
		$is_payment_request='t';
	}
}

function get_current_balance($pCoa){
	$lrow = pg_fetch_array(pg_query("select * from data_accounting.tblbalance_coa where fk_coa = '".convert_sql($pCoa)."' and tr_month = '".date('n')."' and tr_year = '".date('Y')."'"));
	
	return $lrow['balance_cash'] + $lrow['balance_bank'] + $lrow['balance_memorial'] + $lrow['balance_gl_auto'];
}
function get_data_account(){
	global $fk_merek,$fk_cabang,$account_merek,$account_jenis_cabang,$account_cabang;
	$lrow_account = pg_fetch_array(pg_query("select 
	--fk_merek,--tblmerek.kd_accounting as account_merek,
	 tblcabang.kd_accounting as account_cabang from tblcabang 
	--left join tblmerek on fk_merek = kd_merek 
	--left join tbljenis_cabang on kd_jenis_cabang = fk_jenis_cabang 
	where kd_cabang = '".$fk_cabang."'"));
	$fk_merek = $lrow_account["fk_merek"];
	$account_merek = $lrow_account["account_merek"];
	//$account_jenis_cabang = $lrow_account["account_jenis_cabang"]; //pindah pake select 
	$account_cabang = $lrow_account["account_cabang"];
	//echo $account_cabang;
}
?>
