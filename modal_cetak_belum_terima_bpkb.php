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
get_data_module();

$keterangan=trim($_REQUEST["keterangan"]);
$no_batch=($_REQUEST["no_batch"]);

if($_SESSION["jenis_user"]!='HO'){
	$fk_cabang=$_SESSION["kd_cabang"];
}else{
	$fk_cabang=($_REQUEST["fk_cabang"]);
}
$fk_partner_dealer=($_REQUEST["fk_partner_dealer"]);
$fk_partner_notaris=($_REQUEST["fk_partner_notaris"]);

$no_rek=get_rec("tblpartner","no_rek","kd_partner='".$fk_partner_dealer."'");
$no_rek1=get_rec("tblpartner","no_rek","kd_partner='".$fk_partner_notaris."'");


$total=($_REQUEST["total"]);
$strmenu=($_REQUEST["strmenu"]);

$jenis_pembayaran=($_REQUEST["jenis_pembayaran"]);


$query=" 
select  date_part('day',('".date("m/d/Y")."'-tgl_cair))::numeric as jml_hari,case when tgl_serah_terima_bpkb is not null then '0' else ovd_lunas_bpkb end as ovd_bpkb,* from(
select no_fatg,no_mesin,no_rangka,fk_cif,no_polisi,fk_partner_dealer,no_sbg_ar,fk_cabang,tgl_serah_terima_bpkb,tgl_terima_bpkb ,nm_bpkb from data_gadai.tbltaksir_umum 
)as tbltaksir
left join tblcustomer on no_cif = fk_cif
left join (
	select kd_partner,nm_partner from tblpartner
)as tbldealer on fk_partner_dealer=kd_partner
inner join (
	select warna,nm_tipe,no_fatg as no_fatg1,nm_merek,nm_jenis_barang from viewkendaraan
)as tbldetail on no_fatg=no_fatg1
inner join(
	select fk_sbg as no_sbg,tgl_cair,status_sbg,tgl_lunas,date_part('day',((select tgl_sistem from tblsetting)-tgl_lunas))::numeric -(select ovd_lunas_bpkb from tblsetting) as ovd_lunas_bpkb from tblinventory 		
	left join (
		select fk_sbg as fk_sbg1,no_kwitansi,overdue from data_fa.tblpembayaran_bpkb where tgl_bayar is null
	)as tbl on fk_sbg=fk_sbg1
)as tblinv on no_sbg=no_sbg_ar
left join tblcabang on fk_cabang=kd_cabang
";
//showquery($query);


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
<input type="hidden" name="total" value="<?=$total?>">
<input type="hidden" name="strmenu" value="<?=$strmenu?>">

<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
                <tr><td class="judul_menu" align="center"><?=strtoupper($nm_menu)?></td></tr>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border" id="tdContent">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">     
           
            <tr bgcolor="efefef"> 
                <td style="padding:0 5 0 5"width="10%" class="fontColor">Partner</td>
                <td style="padding:0 5 0 5"width="40%">
                <? create_list_dealer();?>
                </td>
            </tr>
            <? if($_SESSION["jenis_user"]=='HO'){?>
            <tr bgcolor="efefef"> 
                <td style="padding:0 5 0 5"width="10%" class="fontColor">Cabang</td>
                <td style="padding:0 5 0 5"width="40%" colspan="3">
                <? create_list_cabang();?>
                </td>
            </tr>   
            <? }?>
            
            <tr> 
		  	</table>
		<? 
		//if($fk_partner_dealer)
		view_data();?>
       
        </td>
    </tr>
    <tr height="20">
        <td height="25" align="center" bgcolor="#D0E4FF" class="border">
            <input class="groove_button" name="btnsimpan" type='button' value='Cetak' onClick="fSave()">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?

function view_data(){
	global $id_edit,$query,$fk_partner_dealer,$strmenu,$no_batch,$jenis_pembayaran,$total,$fk_partner_notaris,$arr_nominal,$fk_cabang;
	
	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cabang = '".$fk_cabang."' ";
	}
	
	if($fk_partner_dealer != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_partner_dealer = '".$fk_partner_dealer."' ";
	}/*else{
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_partner_dealer is not null ";
	}*/

	if ($lwhere!="") $lwhere=" and ".$lwhere;
	
	$query1=$query." 
	where tgl_cair is not null and tgl_terima_bpkb is null 
	".$lwhere." order by fk_cabang,tgl_cair
	
	";	
	//showquery($query1);	
	$lrs=pg_query($query1);
	
?>

    <table cellpadding="0" cellspacing="1" border="0" width="100%">
        <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
        	<td align="center">No</td>
            <td align="center">Cabang</td>
            <td align="center">Nama BPKB</td>
        	<td align="center">No Kontrak</td>
            <td align="center">No Rangka</td>
            <td align="center">No Mesin</td>  
            <td align="center">No Polisi</td>  
        	<td align="center">Tanggal Kontrak</td>
        	<td align="center">JLH Hari</td>
            <td align="center">Dealer</td>
        </tr>
<?	
	$no=1;
	while($lrow=pg_fetch_array($lrs)){	
		$fk_cabang=$lrow["fk_cabang"];
		$nm_partner=get_rec("tblpartner","nm_partner","kd_partner='".$fk_partner."'");
		
?>                  

		<input type="hidden" name="nominal[<?=$lrow["no_sbg"]?>]" value="<?=$nominal?>">
         <tr style="padding:0 5 0 5" height="20" bgcolor='#efefef'>
            <td style="padding:0 5 0 5" class="" align="left"><?=$no?></td>
            <td style="padding:0 5 0 5" class="" align="left"><?=$lrow["nm_cabang"]?></td>            
            <td style="padding:0 5 0 5" class="" align="left" width="100"><?=$lrow["nm_bpkb"]?></td>
            <td style="padding:0 5 0 5" class="" align="left"><?=$lrow["no_sbg"]?></td>
            <td style="padding:0 5 0 5" class="" align="left"><?=$lrow["no_rangka"]?></td>
            <td style="padding:0 5 0 5" class="" align="left"><?=$lrow["no_mesin"]?></td>
            <td style="padding:0 5 0 5" class="" align="left"><?=$lrow["no_polisi"]?></td>
			<td style="padding:0 5 0 5" class="" align="left"><?= date('d/m/Y',strtotime($lrow["tgl_cair"]))?></td>
            <td style="padding:0 5 0 5" class="" align="left"><?=$lrow["jml_hari"].' Hari'?></td></td>
            <td style="padding:0 5 0 5" class="" align="left"><?=$lrow["nm_partner"]?></td>
            
         </tr>                  
<?		$no++;
	}
	
?>

    </table>

<?	
	
}

function cek_error(){
	global $strmsg,$j_action,$strisi,$tr_date,$jenis_pembayaran,$total,$fk_partner_dealer,$no_batch,$fk_cabang,$fk_bank,$fk_partner_notaris;
	if($fk_partner_dealer=="" ){
		$strmsg.='Dealer harus dipilih <br>';
		if(!$j_action) $j_action="document.form1.fk_partner_dealer.focus()";
	}


	
	if($fk_cabang=="" ){
		if($_SESSION["jenis_user"]=='HO'){
			$strmsg.='Cabang harus dipilih <br>';
		}else{
			$strmsg.='Session Cabang habis. Silakan login ulang <br>';
		}
	}
	
	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}


function save_data(){
	global $strmsg,$j_action,$strisi,$jenis_pembayaran,$strmenu,$keterangan,$fk_bank,$fk_cabang,$fk_jenis_cabang,$query,$no_batch,$arr_nominal,$fk_partner_dealer;
	print_r($arr_nominal);
	$l_success=1;
	pg_query("BEGIN");
	
	$query_serial="select nextserial_cabang('DLR':: text,'".$fk_cabang."')";
	$lrow_serial=pg_fetch_array(pg_query($query_serial));
	$no_surat=$lrow_serial["nextserial_cabang"];	
	
	if(!pg_query("insert into data_fa.tblsurat(no_surat,tgl_surat,fk_partner_dealer,jenis)values('".$no_surat."','#".date("Y/m/d H:i:s")."#','".$fk_partner_dealer."','Belum Terima BPKB')")) $l_success=0;					
	if(!pg_query("insert into data_fa.tblsurat_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from data_fa.tblsurat where no_surat='".$no_surat."'")) $l_success=0;
	//showquery("insert into data_fa.tblbelum_terima_bpkb(no_surat,tgl_surat,fk_partner_dealer)values('".$no_surat."','#".date("Y/m/d H:i:s")."#','".$fk_partner_dealer."'");
	
	//$l_success=0;
	if($l_success==1) {
		//$strmsg = "Data saved. Batch :".$no_batch."<br>";
		header("location:print/print_belum_terima_bpkb.php?fk_partner_dealer=".$fk_partner_dealer."&fk_cabang=".$fk_cabang."&no_surat=".$no_surat);
		pg_query("COMMIT");
	} else {
		$strmsg .= "Error :<br>Data save failed.<br>";
	    pg_query("ROLLBACK");
	}	
}	


function create_list_dealer(){
    global $fk_partner_dealer;
	//and fk_cabang='".$_SESSION["kd_cabang"]."'
	
	if($_SESSION["jenis_user"]!='HO'){
		$lcabang="and fk_cabang='".$_SESSION["kd_cabang"]."'";
	}
	
    $l_list_obj = new select("select * from tblpartner where fk_tipe_partner='DEALER' and partner_active ='t' and kd_partner in (select fk_partner_dealer from data_gadai.tbltaksir_umum where fk_partner_dealer is not null and tgl_terima_bpkb is null and no_sbg_ar is not null ".$lcabang.") order by nm_partner","nm_partner","kd_partner","fk_partner_dealer");
    $l_list_obj->add_item("-- Dealer ---",'',0);
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
		

	

