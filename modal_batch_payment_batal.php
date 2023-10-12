<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/numeric.inc.php';
require 'requires/accounting_utility.inc.php';
require 'requires/timestamp.inc.php';
require 'requires/stok_utility.inc.php';

require 'classes/select.class.php';

$j_action=$_REQUEST["hidden_focus"];
$id_menu=trim($_REQUEST["id_menu"]);

$fk_module=get_rec("skeleton.tblmodule","pk_id","fk_menu='".$id_menu."'");
$fk_menu_union=get_rec("skeleton.tblmodule","fk_menu","fk_menu_union='".$id_menu."'");;

get_data_menu($id_menu);
//get_data_module();

$keterangan=trim($_REQUEST["keterangan"]);
$no_batch=($_REQUEST["no_batch"]);

$total=($_REQUEST["total"]);
$strmenu=($_REQUEST["strmenu"]);
$fk_cabang=($_REQUEST["fk_cabang"]);

if ($lwhere!="") $lwhere=" where ".$lwhere;

$jenis_pembayaran=get_rec("data_gadai.tblhistory_sbg","transaksi","referensi='".$no_batch."'");
if($jenis_pembayaran=='Pembayaran Unit'){
	$tujuan='fk_partner_dealer';
}else if($jenis_pembayaran=='Pembayaran Fidusia'){
	$tujuan='fk_partner_notaris';
}else if($jenis_pembayaran=='Pembayaran Asuransi'){
	$tujuan='fk_partner_asuransi';
}else{
	$tujuan='fk_sbg';
}
	
$query=" 
select *,".$tujuan." as fk_partner from (
	select * from data_fa.tblbatch_payment
	inner join(
		select referensi as referensi_history,DATE_TRUNC('day',tgl_bayar) as tgl_bayar_convert,* from data_gadai.tblhistory_sbg 
		where referensi='".$no_batch."' and tgl_batal is null
	) as tblhistory_sbg on no_batch=referensi_history
	left join data_gadai.tblproduk_cicilan on fk_sbg=no_sbg 
	inner join(
		select no_fatg,fk_barang,fk_cif,fk_cabang,no_mesin,no_rangka,fk_partner_dealer,status_fatg from data_gadai.tbltaksir_umum 
		left join data_gadai.tbltaksir_umum_detail on fk_fatg=no_fatg
		--where status_barang='Baru'
	)as tbl on no_fatg=fk_fatg
	left join tblbarang on kd_barang=fk_barang	
	left join tblcustomer on no_cif=fk_cif
	".$lwhere."
) as tbl";
//showquery($query);

$lrs=pg_query($query);
$lrow=pg_fetch_array($lrs);
$fk_partner=$lrow['fk_partner'];
$nm_partner=get_rec("tblpartner","nm_partner","kd_partner='".$fk_partner."'");


if($_REQUEST["status"]=="Save") {
	cek_error();
	if(!$strmsg){
		save_data();
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
function cekError(){
	var lAlerttxt="";
	var lFocuscursor="";

	if(lAlerttxt!=""){
		alert("Error : <br>"+lAlerttxt,function(){eval(lFocuscursor+'.focus()')});
		return false
	} else return true;
}


function fSave(){
	//fCount()
	//alert('test')
	//if (cekError()) {
		document.form1.status.value='Save';
		document.form1.submit();
	//}
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")) {
		lInputClose=getObjInputClose();lInputClose.close()
	}
}

function fCount(pFlag='true'){
	var index;
	index =0;
	//confirm(pFlag)
	document.form1.strmenu.value='';
	lObjCount=document.form1.strcek
	var len = document.form1.strcek.length;
	if(len == undefined) len = 1;
	//confirm(lObjCount[0].checked)
	//confirm(lObjCount[1].checked)
	if(len==1){
		if (lObjCount.checked){
			index+=parseFloat(lObjCount.value)
			document.form1.strmenu.value=lObjCount.id+',';
		}
	}else{
		
		for (j=0;j<len;j++){			
			if (lObjCount[j].checked) {
				index+=parseFloat(lObjCount[j].value)
				document.form1.strmenu.value+=lObjCount[j].id+',';
			}
		}	
	}
	document.getElementById('divSelectCount').innerHTML=number_format(index);
	document.form1.total.value=index
	//var r=confirm("Total Data yang Dipilih : "+index);
	
}

function fLoad(){
	
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	}elseif($j_action){
		echo $j_action;
	}else{
		echo "document.form1.btnsimpan.focus();";
	}
?>
}
</script>
<body onLoad="fLoad();document.form1.autocomplete='off'" bgcolor="#fafafa">
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="form1" enctype="multipart/form-data">
<input type="hidden" name="status">
<input type="hidden" name="strisi" value="<?=$strisi?>">
<input type="hidden" name="hidden_focus">
<input type="hidden" name="id_menu" value="<?=$id_menu?>">
<input type="hidden" name="jenis_pembayaran" value="<?=$jenis_pembayaran?>">

<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
                <tr><td class="judul_menu" align="center">BATAL <?=strtoupper($nm_menu)?></td></tr>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border" id="tdContent">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">     
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%" class="fontColor">No. BPB</td>
					<td style="padding:0 5 0 5" width="30%"><input type="text" value="<?=$no_batch?>" name="no_batch">           			 <input class="groove_button" name="generate" type='button' value='Generate' onClick="document.form1.submit()">
</td>
					<td style="padding:0 5 0 5" width="20%">Nama </td>
					<td  style="padding:0 5 0 5" width="30%"><input type="hidden" name="nm_partner" value="<?=$nm_partner?>"><?=$nm_partner?></td>
				</tr>
     
		  	</table>
		<? 
		if($no_batch)view_data();?>
       <input type="hidden" name="total" value="<?=$total?>">

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

function view_data(){
	global $id_edit,$query,$fk_partner_dealer,$strmenu,$no_batch,$jenis_pembayaran,$total,$fk_partner_notaris,$arr_nominal,$fk_cabang;
	
	//showquery($query1);	
	$lrs=pg_query($query);
	
?>

    <table cellpadding="0" cellspacing="1" border="0" width="100%">
        <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
        	<td align="center">No Kontrak</td>
            <td align="center">Nama Customer</td>
        	<td align="center">Nominal</td>
        </tr>
<?	

	while($lrow=pg_fetch_array($lrs)){	
		$fk_cabang=$lrow["fk_cabang"];
		$nominal=$lrow["nilai_bayar"];
?>                  

         <tr style="padding:0 5 0 5" height="20" bgcolor='#efefef'>

            <td style="padding:0 5 0 5" class="" align="left"><?=$lrow["no_sbg"]?></td>
            <td style="padding:0 5 0 5" class="" align="left"><?=$lrow["nm_customer"]?></td>
			<td style="padding:0 5 0 5" class="" align="right"><?=convert_money("",$nominal,0)?></td>	
            	
         </tr>                  
<?		
		$total+=$lrow["nilai_bayar"];
	}
	
?>

		<tr tyle="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
            <td style="padding:0 5 0 5" align="center" colspan="2">Total</td>
            <td style="padding:0 5 0 5" align="right"><span id="divSelectCount"><?=convert_money("",$total)?></span></td>
        </tr>
    </table>

<?	
	
}

function cek_error(){
	global $strmsg,$j_action,$strisi,$tr_date,$jenis_pembayaran,$total,$fk_partner_dealer,$no_batch,$fk_cabang,$fk_bank,$fk_partner_notaris,$total;

	if($total=="" || $total=="0"){
		$strmsg.='Tidak ada yang data<br>';
	}	
	
	if($no_batch=="" ){
		$strmsg.='No BPB kosong <br>';
		if(!$j_action) $j_action="document.form1.no_batch.focus()";
	}
	
/*	if(!pg_num_rows(pg_query("select * from data_fa.tblbatch_payment where no_batch='".$no_batch."'"))){
		$strmsg.='No BPB Salah <br>';
		if(!$j_action) $j_action="document.form1.no_batch.focus()";
	}
*/
	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}


function save_data(){
	global $strmsg,$j_action,$strisi,$jenis_pembayaran,$strmenu,$keterangan,$fk_bank,$fk_cabang,$fk_jenis_cabang,$query,$no_batch,$arr_nominal,$total;
	//print_r($arr_nominal);
	$l_success=1;
	pg_query("BEGIN");
	
	$lrs=pg_query($query);
	//print_r($l_arr_row);
	$lrow=pg_fetch_array($lrs);
	//showquery($query);
	$fk_bank=$lrow['fk_bank'];
	
	if(!pg_query("
	update data_gadai.tblhistory_sbg set 
	tgl_batal='".today_db." ".date("H:i:s")."',fk_user_batal='".$_SESSION["username"]."' 			
	where referensi='".$no_batch."' 
	")) $l_success=0;
			
	//echo $total.'sdfsdf';
	if(!pg_query(update_saldo_bank($fk_bank,cabang_ho,0,$total,'BATAL '.$jenis_pembayaran,$no_batch)))$l_success=0;
	
	//showquery(update_saldo_bank($fk_bank,cabang_ho,0,$total,'BATAL '.$jenis_pembayaran,$no_batch));
	
	$arrPost=array();
	$fk_owner=$no_batch;
	$owner = get_rec("data_gadai.tblhistory_sbg","transaksi","referensi='".$no_batch."'");
	if($owner=="Pencairan Datun"){
		$type_owner='PENCAIRAN';
	}else{
		$type_owner='PAYMENT';
	}
	$arrPost = gl_balik($fk_owner,$type_owner);
	if(count($arrPost)=='0'){
		$l_success=0;
	}
	
	//cek_balance_array_post($arrPost);
	if(!posting('BATAL '.$type_owner,$fk_owner,today_db,$arrPost,$fk_cabang,'00'))$l_success=0;
	
	
	//$l_success=0;
	if($l_success==1) {
		$strmsg = "Data saved.<br>";
		$j_action="lInputClose=getObjInputClose();lInputClose.close()";
		//pg_query("ROLLBACK");
		pg_query("COMMIT");
	} else {
		$strmsg .= "Error :<br>Data save failed.<br>";
	    pg_query("ROLLBACK");
	}	
}	

function create_list_bank(){
    global $fk_bank;
	
    $l_list_obj = new select("select * from tblcabang_detail_bank left join tblbank on fk_bank=kd_bank left join (select description, coa from tbltemplate_coa)as tblcoa on fk_coa=coa where fk_cabang='".cabang_ho."' and fk_bank not in('01','03')","description","fk_bank","fk_bank");
    $l_list_obj->add_item("-- Bank ---",'',0);
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:12px;' onKeyUp='fNextFocus(event,document.getElementById(\"login\"))'");
}

function create_list_dealer(){
    global $fk_partner_dealer;
	
    $l_list_obj = new select("select * from tblpartner where fk_tipe_partner='DEALER' and partner_active ='t' order by nm_partner","nm_partner","kd_partner","fk_partner_dealer");
    $l_list_obj->add_item("-- Dealer ---",'',0);
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:12px;' onKeyUp='fNextFocus(event,document.getElementById(\"login\"))'","form1","document.form1.submit()");
}

function create_list_notaris(){
    global $fk_partner_notaris;
	
    $l_list_obj = new select("select * from tblpartner where fk_tipe_partner='NOTARIS' and partner_active ='t' order by nm_partner","nm_partner","kd_partner","fk_partner_notaris");
    $l_list_obj->add_item("-- Notaris ---",'',0);
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:12px;' onKeyUp='fNextFocus(event,document.getElementById(\"login\"))'","form1","document.form1.submit()");
}

function create_list_cabang(){
    global $fk_cabang;
	
    $l_list_obj = new select("select * from tblcabang where cabang_active ='t' order by kd_cabang","nm_cabang","kd_cabang","fk_cabang");
    $l_list_obj->add_item("-- Cabang ---",'',0);
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:12px;' onKeyUp='fNextFocus(event,document.getElementById(\"login\"))'","form1","document.form1.submit()");
}
function get_data_module(){
	global $kd_module,$j_action,$nm_tabs;
	
	//query untuk memunculkan tab 
	$lrow_tab_switch=pg_fetch_array(pg_query("select * from skeleton.tblmodule_tabs where fk_module='".$kd_module."' order by no_urut_tabs limit 1"));
	$nm_tabs=$lrow_tab_switch["kd_tabs"];
	$lrow_first_field=pg_fetch_array(pg_query("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' order by no_urut_add limit 1"));
	$j_action="document.form1.".$lrow_first_field["kd_field"].".focus();";
}
?>
		

	

