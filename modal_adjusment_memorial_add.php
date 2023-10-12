<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/numeric.inc.php';
require 'requires/accounting_utility.inc.php';
require 'requires/timestamp.inc.php';

$j_action=$_REQUEST["hidden_focus"];

$strisi=trim($_REQUEST["strisi"]);
$tr_date = trim($_REQUEST["tr_date"]);
if($tr_date)$tr_date = convert_date_english($tr_date);
else $tr_date = convert_date_english(today);
$description = trim($_REQUEST["description"]);
$total_debit=trim($_REQUEST["total_debit"]);
$total_credit=trim($_REQUEST["total_credit"]);
$fk_cabang=$_REQUEST["fk_cabang"];
$nm_cabang=$_REQUEST["nm_cabang"];
$fk_jenis_cabang=$_REQUEST["fk_jenis_cabang"];

if($_REQUEST["status"]=="Save") {
	cek_error();
	if(!$strmsg){
		save_data();
	}
}

?>
<html>
<head>
	<title>.: SUKA FAJAR :.</title>
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

function fGetCabang(){
	fGetNC(false,'20170900000010','fk_cabang','Ganti Item Kendaraan',document.form1.fk_cabang,document.form1.fk_cabang)
}

function fGetCabangData(){
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataCabangState
	lSentText="table= tblcabang&field=(nm_cabang)&key=kd_cabang&value="+document.form1.fk_cabang.value
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
			document.getElementById('divNmCabang').innerHTML=document.form1.nm_cabang.value=lTemp[0]
		} else {
		document.getElementById('divNmCabang').innerHTML=document.form1.nm_cabang.value="-"
		}
	}
}


function cekError(){
	var lAlerttxt="";
	var lFocuscursor="";
	if (document.form1.tr_date.value==""){
		lAlerttxt+='Date is require<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.tr_date";}
	}
	if (document.form1.strisi.value==""){
		lAlerttxt+='Adjust Memorial is Empty<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.description";}
	}
	if(lAlerttxt!=""){
		alert("Error : <br>"+lAlerttxt,function(){eval(lFocuscursor+'.focus()')});
		return false
	} else return true;
}

function fSave(){
	document.form1.strisi.value=table1.getIsi();
	//if (cekError()) {
		document.form1.status.value='Save';
		if (document.form1.strisi.value!='false') document.form1.submit();
	//}
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")) {
		lInputClose=getObjInputClose();lInputClose.close()
	}
}


function fLoad(){
	//grantotal(Table_Detil);
<?
	if ($_REQUEST["success"]==1){
		echo "alert('Adjustment Memorial saved');";
	}
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	}elseif($j_action){
		echo $j_action;
	}else{
		echo "document.form1.tr_date.focus();";
	}
?>
}
</script>
<body onLoad="fLoad();document.form1.autocomplete='off'" bgcolor="#fafafa">
<form action="modal_adjusment_memorial_add.php" method="post" name="form1">
<input type="hidden" name="status">
<input type="hidden" name="strisi" value="<?=$strisi?>">
<input type="hidden" name="hidden_focus">
<input type="hidden" name="fk_jenis_cabang" value="<?=$fk_jenis_cabang?>">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<td class="judul_menu" align="center">ADJUSMENT MEMORIAL</td>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border" id="tdContent">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5"width="20%" class="fontColor">Date</td>
					<td style="padding:0 5 0 5"width="30%"><input type="text" value="<?=convert_date_indonesia($tr_date)?>" name="tr_date" maxlength="10" size="8" onKeyUp="fNextFocus(event,document.form1.description)" onChange="fNextFocus(event,document.form1.description)">&nbsp;<img src="images/btn_extend.gif" width="13" height="12" onClick="fPopCalendar(document.form1.tr_date,function(){document.form1.description.focus()})"></td>
				  	<td class="fontColor" style="padding:0 5 0 5">&nbsp;</td>
				  	<td class="fontColor" style="padding:0 5 0 5">&nbsp;</td>
				</tr>
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%" class="fontColor">Description</td>
					<td style="padding:0 5 0 5" width="30%" colspan="3"><input type="text" name="description" size="120" value="<?=$description?>" onKeyUp="fNextFocus(event,document.form1.btnsimpan)"></td>
				</tr>
                
                <tr style="padding:0 5 0 5" bgcolor="efefef">
          			<td width="20%" style="padding:0 5 0 5" bgcolor="#efefef">Kode Cabang</td>
         			<td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                        <input name="fk_cabang" type="text" onKeyPress="if(event.keyCode==4) img_fk_cabang.click();" value="<?=$fk_cabang?>" onChange="fGetCabangData()">&nbsp;<img src="images/search.gif" id="img_fk_cabang" onClick="fGetCabang()" style="border:0px" align="absmiddle">
                    </td>
          			<td width="20%" style="padding:0 5 0 5">Nama Cabang</td>
			        <td width="30%" style="padding:0 5 0 5">
          				<input type="hidden" name="nm_cabang" value="<?=convert_html($nm_cabang)?>" class="groove_text" style="width:90%" > <span id="divNmCabang"><?=convert_html($nm_cabang)?></span></td>
				</tr>
		  	</table>
			<script>
                arrFieldTable_Detil={
					0:{'name':'coa','caption':'COA','type':'get','source':'20171000000034','size':'10','is_required':'t','table_db':'tbltemplate_coa','table_db_inner_join':'',				'field_key':'coa','field_get':'description','referer' : '1','id_detail_field' : '20180400000003'},
					1:{'name':'description','caption':'Description','type':'readonly','size':'10','is_required':'t','is_readonly':'t'},
					2:{'name':'referensi','caption':'Referensi','type':'text','size':'10','is_required':'f'
//						'OtherErrorCheck':function (pObj){
//							lReturn="";
//							if (lReturn=="") {
//								lCurrentValue=pObj.value
//								if(lCurrentValue!=""){
//									lTable=pObj.parentNode.parentNode.parentNode.parentNode
//									lCurrentRow=pObj.parentNode.parentNode
//									if(lCurrentRow.cells[0].children[0])lCurrentRef=lCurrentRow.cells[0].children[0].value
//									for (lIndex=1;lIndex<(lTable.rows.length-1);lIndex++) {
//										if (lIndex!=lCurrentRow.rowIndex) {
//											lValue=lTable.rows[lIndex].cells[2].innerHTML
//											if (lValue==lCurrentValue) {
//												if(lTable.rows[lIndex].cells[0].innerHTML){
//													lRef = lTable.rows[lIndex].cells[0].innerHTML
//													if(lCurrentRef == lRef) lReturn="Sudah Ada"
//												}else lReturn="Sudah Ada"
//											}
//										}
//									}
//								}
//							}
//							return lReturn
//						}
					},
                    3:{'name':'type','caption':'Type','type':'list_manual','size':'15','is_required':'t','field_get':'C,D','field_key':'C,D',
                        'OtherErrorCheck':function (pObj){
                            lReturn="";
                            if(pObj.selectedIndex==0){
                                lReturn="Harus Dipilih"
                            }
                            return lReturn
                        }
                    },
                    4:{'name':'nominal','caption':'Nominal','type':'numeric','size':'15','is_required':'t'},
//2:{'name':'account','caption':'Account','type':'get_other','source':'chart_of_account','size':'18','is_required':'t','table_db':'tblcoa','field_key':'coa','field_get':'coa'},
                    5:{'name':'keterangan','caption':'Keterangan','type':'text','size':'15','is_required':'f'},					
                };
                table1=new table();
                table1.init("<strong>DETAIL</strong>","Table_Detil",arrFieldTable_Detil,document.getElementById("tdContent"),{'border':'0','cellpadding':'0','cellspacing':'1','width':'100%','align':'center'});
                table1.setIsi(document.form1.strisi.value);
            </script>
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
                <tr align="center">
                    <td width="20%" bgcolor="#c8c8c8">Total Debit (Rp)</td>
                    <td width="30%" bgcolor="#c8c8c8" align="right"><input type='hidden' name="total_debit" value="<?=$total_debit?>">
                        <span id="div_total_debit"><?=convert_money("",$total_debit,2)?></span></td>
                    <td width="20%" bgcolor="#c8c8c8">Total Credit (Rp)</td>
                    <td width="20%" bgcolor="#c8c8c8" align="right">
                        <input type='hidden' name="total_credit" value="<?=$total_credit?>">
                        <span id="div_total_credit"><?=convert_money("",$total_credit,2)?></span>
                    </td>
                </tr>
			</table>
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
function cek_error(){
	global $strmsg,$j_action,$strisi,$tr_date,$total_debit,$total_credit,$description,$fk_cabang,$fk_jenis_cabang;

//	$lrs = pg_fetch_array(pg_query("select back_date_transaksi from tblcabang where kd_cabang = '".$fk_cabang."'"));
//	$l_back_date = $lrs["back_date_transaksi"];
	//$lrs = pg_fetch_array(pg_query("select back_date_transaksi_memorial from tblcabang where kd_cabang = '".$fk_cabang."'"));
//	$l_back_date = $lrs["back_date_transaksi_memorial"];
//	if ($l_back_date) $tgl_back_date = date("n/d/Y",strtotime(date("n/d/Y") . "-".$l_back_date." days"));
//	else $tgl_back_date = date("n/d/Y");
	
	if($tr_date==""){
		$strmsg.="Tanggal Harus Diisi.<br>";
		if(!$j_action) $j_action="document.form1.tr_date.focus()";
	} elseif(validate_date($tr_date)==false){
		$strmsg.="Format Tanggal Salah.<br>";
		if(!$j_action) $j_action="document.form1.tr_date.focus()";
	}
	//elseif(strtotime($tr_date) < strtotime($tgl_back_date)){
//		$strmsg.="Tanggal Lebih Kecil daripada Date is smaller than back date allowed.<br>";
//		if(!$j_action) $j_action="document.form1.tr_date.focus()";		
//	}
	 elseif(strtotime($tr_date) > strtotime(today_db)){
		$strmsg.="Tanggal Melebihi Hari Ini<br>";
		if(!$j_action) $j_action="document.form1.tr_date.focus()";
	}
	//else {
	//	if( ! cek_periode_jurnal($tr_date,'memorial',$fk_cabang) ){
	//		$strmsg.="Tanggal transaksi diluar periode.<br>";
	//		if(!$j_action) $j_action="document.form1.tr_date.focus()";
	//	}
	//}
	
	
	if(!cek_periode_accounting(date("Y",strtotime($tr_date)),date("n",strtotime($tr_date)))){
		//$strmsg.="Tanggal yang diinput, tidak sesuai dengan Periode Accounting.<br>";
		if(!$j_action) $j_action="document.form1.tr_date.focus()";
	}

	$tgl_live = get_rec("tblsetting","tgl_live");
	if(strtotime($tr_date) < strtotime($tgl_live)){
		$strmsg.="Tanggal yang diinput, tidak bisa dibawah ".date("d/m/Y",strtotime($tgl_live)).".<br>";
		if(!$j_action) $j_action="document.form1.tr_date.focus()";
	}


	if($description == ""){
		$strmsg.="Description Kosong.<br>";
		if(!$j_action) $j_action="document.form1.description.focus()";
	}

	$l_arr_row = split(chr(191),$strisi);
	if(count($l_arr_row)-1<1) $strmsg.="Detail Kosong.<br>";
	else {
		$l_index_array=1;
		$l_arr_item=array();
		$l_counter_ar_ap=0; // hnya 1 transaksi AR yg diperbolehkan
		$l_counter_pajak=0; // buat ngecek account pajak
		$l_supplier = '';
		$l_total_row = count($l_arr_row)-1;
		for ($i=0; $i<$l_total_row; $i++){
			$l_arr_col=split(chr(187),$l_arr_row[$i]);
			//showquery("select * from tblcoa where coa = '".$l_arr_col[0]."' ");
			// and (type_of_coa<>'Inventory' or type_of_coa is null)
			// dibuka dulu akun persediaan nya, nanti klo udah sama dg rincian baru tutup lagi
			if($l_arr_col[0]=="") $strmsg.="@Detail line ".($i+1)." : Account Kosong.<br>";
			elseif(!$lrow_coa = pg_fetch_array(pg_query("select * from tbltemplate_coa where coa = '".$l_arr_col[0]."' "))) $strmsg.="@Detail line ".($i+1)." : COA Salah.<br>";
			else{
				//print_r($lrow_coa);echo "<br>";
				$l_kd_jenis_transaksi = $lrow_coa['kd_jenis_transaksi'];
				$l_table_db = $lrow_coa['table_db'];
				$l_field_key = $lrow_coa['field_key'];
				$l_jenis_account = $lrow_coa['jenis_account'];

				if($l_jenis_account=='' && $l_table_db!='')$strmsg.="@Detail line ".($i+1)." : Detail not found, Please Call Your Administrator.<br>";

				if($l_jenis_account=='ar' || $l_jenis_account=='ap')$l_counter_ar_ap++;
				if($jenis_pajak!='')if($jenis_pajak==$l_kd_jenis_transaksi)$l_counter_pajak++;

				if($l_arr_col[3] == $lrow_coa["transaction_type"])$l_operator = 1;
				elseif($l_arr_col[3] != $lrow_coa["transaction_type"])$l_operator = -1;
			}
			
			if($l_table_db!= "" && $l_arr_col[2]!=""){ // buka dulu
				if(!$lrow_ap_ar = pg_fetch_array(pg_query("select * from ".$l_table_db." where ".$l_field_key." = '".convert_sql($l_arr_col[2])."' and bulan = '".date('n')."' and tahun = '".date('Y')."'" ))) $strmsg.="@Detail line ".($i+1)." : Reference Salah.<br>";
				//elseif($l_jenis_account == 'ap'){ // klo hutang2an baru cek klo AR mah terima2 aja
				elseif($l_jenis_account != ''){
					//hitung saldo nya jgn smpe minus atau > dari jumlah transaksi
					$l_result = ($lrow_ap_ar['saldo'] + ( $l_arr_col[4] * $l_operator ));
					//echo $l_result;
					if( $l_result < 0 || $l_result > $lrow_ap_ar['jumlah_transaksi'] )$strmsg.="@Detail line ".($i+1)." : Nominal Melebihi Nilai Tagihan.<br>";
					
					if($l_supplier=='')$l_supplier = $lrow_ap_ar['fk_partner'].$lrow_ap_ar['fk_customer'];
					elseif($l_supplier != ($lrow_ap_ar['fk_partner'].$lrow_ap_ar['fk_customer']) )$strmsg.="@Detail line ".($i+1)." : Tagihan diterima dari Customer atau Supplier yang berbeda.<br>";
				}
			}

			if($l_arr_col[3]=="") $strmsg.="@Detail line ".($i+1)." : Type Kosong.<br>";

			if($l_arr_col[4]=="") $strmsg.="@Detail line ".($i+1)." : Nominal Kosong.<br>";
			elseif(check_type('float',$l_arr_col[4])) $strmsg.="@Detail line ".($i+1)." : Nominal Salah.<br>";
			
		}
	}

	if (round($total_debit,2)!=round($total_credit,2)) {
		$strmsg.="Transaksi tidak balance.<br>";
	}
	//$strmsg.=1;
	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}

function save_data(){
	global $strmsg,$j_action,$strisi,$tr_date,$tr_date_convert,$description,$total_debit,$fk_cabang,$fk_jenis_cabang;

	$l_success=1;
	pg_query("BEGIN");
	$query="select nextserial_transaksi('ADJ':: text)";
	$lrow=pg_fetch_array(pg_query($query));
	$l_no_bukti=$lrow["nextserial_transaksi"];
	
	if(!pg_query("insert into data_accounting.tbladjust_memorial (tr_date,description,total,fk_cabang,divisi,no_bukti) values('".$tr_date."',".(($description)?"'".convert_sql($description)."'":"NULL").",".(($total_debit=="")?"NULL":"'".$total_debit."'").",'".$fk_cabang."','".$fk_jenis_cabang."','".$l_no_bukti."')")) $l_success=0;
	//showquery("insert into data_accounting.tbladjust_memorial (tr_date,description,total,fk_cabang,divisi,no_bukti) values('".$tr_date."',".(($description)?"'".convert_sql($description)."'":"NULL").",".(($total_debit=="")?"NULL":"'".$total_debit."'").",'".$fk_cabang."','".$fk_jenis_cabang."','".$l_no_bukti."')");
	//$l_no_bukti=get_last_id("data_accounting.tbladjust_memorial","no_bukti");

	//$l_can_recalculate=false;
	$l_arr_row = split(chr(191),$strisi);
	for ($i=0; $i<count($l_arr_row)-1; $i++){
		$l_arr_col=split(chr(187),$l_arr_row[$i]);
		$l_total=($l_arr_col[4]);
		$lquery = "
			insert into data_accounting.tbladjust_memorial_detail(
				fk_adjust_memorial,fk_account,reference_transaksi,type_tr,
				dokumen,value,description,rate,total,fk_cabang_detail
			) values(
				'".$l_no_bukti."','".convert_sql($l_arr_col[0])."',
				".(($l_arr_col[2]=="")?"NULL":"'".$l_arr_col[2]."'").",'".$l_arr_col[3]."',
				".(($l_arr_col[6]=="")?"NULL":"'".$l_arr_col[6]."'").",
				".(($l_arr_col[4]=="")?"0":"'".$l_arr_col[4]."'").",
				".(($l_arr_col[5]=="")?"NULL":"'".convert_sql(str_replace('"', ".",$l_arr_col[5]))."'").",
				1,".(($l_arr_col[4]=="")?"0":"'".$l_arr_col[4]."'").",
				'".convert_sql($fk_cabang)."'
			)
		";
		//showquery($lquery);
		if(!pg_query($lquery)) $l_success=0;
		
//		if (!update_saldo_coa("memorial",$l_arr_col[2],$l_arr_col[1],$l_total,$tr_date)) $l_success=0;
		/*//--------------------------------------------------------accounting-------------------------------------------------------------
		$row_account = pg_fetch_array(pg_query("SELECT * FROM tblcoa WHERE coa = '".$l_arr_col[2]."'"));
		$arrPost[$row_account['used_for']]	= array('type'=>strtolower($l_arr_col[1]),'value'=>$l_total,'account'=>$l_arr_col[2]);
		
		//end define
		if(!posting('ADJUSTMENT MEMORIAL',$l_no_bukti,date("n/d/Y H:i:s"),$arrPost,$row_account["fk_cabang"],$row_account["fk_jenis_cabang"]))$l_success=0;
		//end posting*///posting pindah saat approve
	}
		//-------------------------------------------------------------------------------------------------------------------------------
	//log begin
	if(!pg_query("insert into data_accounting.tbladjust_memorial_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from data_accounting.tbladjust_memorial where no_bukti='".$l_no_bukti."'")) $l_success=0;
	$l_id_log=get_last_id("data_accounting.tbladjust_memorial_log","pk_id_log");
	//end log
	if(!pg_query("insert into data_accounting.tbladjust_memorial_detail_log select *,'".$l_id_log."' from data_accounting.tbladjust_memorial_detail where fk_adjust_memorial='".$l_no_bukti."'")) $l_success=0;
	//end log

	//$l_success=0;
	if($l_success==1) {
		$strmsg = "Adjust Memorial saved.<br>";
		$strisi="";
		$j_action="lInputClose=getObjInputClose();lInputClose.close()";
		$tr_date=date('m/d/Y');
		$description="";
		pg_query("COMMIT");
	} else {
		$strmsg = "Error :<br>Adjust Memorial save failed.<br>";
	    pg_query("ROLLBACK");
	}
}
?>