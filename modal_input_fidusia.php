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

$id_menu=trim($_REQUEST["id_menu"]);
get_data_menu($id_menu);	
$kd_menu_button=trim($_REQUEST["kd_menu_button"]);
$lrs=pg_query("select * from skeleton.tblmenu where kd_menu='".$kd_menu_button."'");	
$lrow=pg_fetch_array($lrs);
$nama_button=$lrow["nama_menu"];
get_data_module();

$keterangan=trim($_REQUEST["keterangan"]);
$no_batch=($_REQUEST["no_batch"]);
$fk_bank=($_REQUEST["fk_bank"]);
$fk_partner_notaris=($_REQUEST["fk_partner_notaris"]);
$no_rek=get_rec("tblpartner","no_rek","kd_partner='".$fk_partner_dealer."'");

$total=($_REQUEST["total"]);
$strmenu=($_REQUEST["strmenu"]);

$jenis_pembayaran=($_REQUEST["jenis_pembayaran"]);
$fk_cabang=($_REQUEST["fk_cabang"]);

$fk_sbg=($_REQUEST["fk_sbg"]);

if(!$_REQUEST["periode_awal"])$periode_awal = convert_date_english(today);
else $periode_awal = convert_date_english($_REQUEST["periode_awal"]);
if(!$_REQUEST["periode_akhir"])$periode_akhir=convert_date_english(today);
else $periode_akhir =  convert_date_english($_REQUEST["periode_akhir"]);


if($fk_partner_notaris != ''){
	if ($lwhere!="") $lwhere.=" and ";
	$lwhere.=" fk_partner_notaris = '".$fk_partner_notaris."' ";
}

if($fk_cabang != ''){
	if ($lwhere!="") $lwhere.=" and ";
	$lwhere.=" fk_cabang = '".$fk_cabang."' ";
}

if($fk_sbg != ''){
	if ($lwhere!="") $lwhere.=" and ";
	$lwhere.=" no_sbg like '%".$fk_sbg."%' ";
}


if($periode_awal != '' && $periode_akhir != ''){
	if ($lwhere!="") $lwhere.=" and ";
	$lwhere.=" tgl_cair between '".$periode_awal." 00:00:00' and '".$periode_akhir." 23:59:59'";
}

if ($lwhere!="") $lwhere=" and ".$lwhere;
else $lwhere=" and false".$lwhere;

$query=" 
select * from data_gadai.tblproduk_cicilan 
inner join(
select no_fatg,fk_barang,fk_cif,fk_cabang,no_mesin,no_rangka,fk_partner_dealer from data_gadai.tbltaksir_umum 
left join data_gadai.tbltaksir_umum_detail on fk_fatg=no_fatg
)as tbl on no_fatg=fk_fatg
left join tblbarang on kd_barang=fk_barang	
left join tblcustomer on no_cif=fk_cif
left join tblcabang on fk_cabang=kd_cabang
left join tblpartner on fk_partner_notaris=kd_partner
where status_approval ='Approve' and tgl_cair is not null and fk_partner_notaris is not null
--and (no_sertifikat_fidusia is null or tgl_sertifikat_fidusia is null or jam_sertifikat_fidusia is null or no_akta_fidusia is null or tgl_akta_fidusia is null)
".$lwhere." 
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

function fModal(pType,pID,pModule,pModal){
	switch (pType){
		case "view":
			show_modal('modal_view.php?id_menu=20170900000073&pstatus=view&id_view='+pID,'status:no;help:no;dialogwidth:900px;dialogheight:545px;')
		break;
	}


}
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

function formating(pNumber){
	pNumber=pNumber.replace(",","")
	//confirm(parseFloat(pNumber).toFixed(0).replace(/\d(?=(\d{3})+\.)/g, '$&,'))
	pNumber=parseFloat(pNumber).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')
	pNumber=pNumber.replace(".00","")
	//confirm(pNumber)
	return pNumber
}

function fLoad(){
	
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	}else{
		//echo "document.form1.btnsimpan.focus();";
	}
?>
}
</script>
<body onLoad="fLoad();document.form1.autocomplete='off'" bgcolor="#fafafa">
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="form1" enctype="multipart/form-data">
<input type="hidden" name="status">
<input type="hidden" name="strisi" value="<?=$strisi?>">
<input type="hidden" name="kd_menu_button" value="<?=$kd_menu_button?>">
<input type="hidden" name="id_menu" value="<?=$id_menu?>">
<input type="hidden" name="total" value="<?=$total?>">
<input type="hidden" name="strmenu" value="<?=$strmenu?>">


<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<tr><td class="judul_menu" align="center"><?=strtoupper($nama_button)?></td></tr>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border" id="tdContent">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">     
            <tr> 
                <td style="padding:0 5 0 5"width="10%" class="fontColor">Cabang</td>
                <td style="padding:0 5 0 5"width="40%">
                <? create_list_cabang();?>
                </td>
            </tr>        
            <tr> 
                <td style="padding:0 5 0 5"width="10%" class="fontColor">Notaris</td>
                <td style="padding:0 5 0 5"width="40%">
                <? create_list_notaris();?>
                </td>
            </tr>        
            <tr> 
                <td style="padding:0 5 0 5"width="10%" class="fontColor">Kontrak</td>
                <td style="padding:0 5 0 5"width="40%">
              	<input name="fk_sbg" type="text" class='groove_text ' size="20" id="fk_sbg"  onChange="document.form1.submit()" value="<?=$fk_sbg?>">
                </td>
            </tr>       
            <tr>
                <td width="10%" style="padding:0 5 0 5" class="fontColor">Periode </td>
                <td width="40%" style="padding:0 5 0 5" >
                    <input type="text" name="periode_awal" value="<?=convert_date_indonesia($periode_awal)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_awal.click();" size="10" onChange="document.form1.submit()" >&nbsp;<img src="images/btn_extend.gif" name="img_periode_awal" onClick="fPopCalendar(document.form1.periode_awal,function(){document.form1.submit()})"> -                               
                    <input type="text" name="periode_akhir" value="<?=convert_date_indonesia($periode_akhir)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_akhir.click();" size="10" onChange="document.form1.submit()">&nbsp;<img src="images/btn_extend.gif" name="img_periode_akhir" onClick="fPopCalendar(document.form1.periode_akhir,function(){document.form1.submit()})">                                
                </td>
            </tr>               
             
		  	</table>
		<? view_data()?>
       
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
	global $id_edit,$query,$fk_partner_notaris,$strmenu,$fk_cabang,$query,$total;

	if ($lwhere!="") $lwhere=" and ".$lwhere;
	
	$query=$query."  
	".$lwhere." order by no_sbg
	";	
	$lrs=pg_query($query);
	$arr=array('no_sertifikat_fidusia','tgl_sertifikat_fidusia','jam_sertifikat_fidusia','no_akta_fidusia','tgl_akta_fidusia','is_hapus')
?>
    <table cellpadding="0" cellspacing="1" border="0" width="100%">
        <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
        	<td align="center" width="80">No Kontrak</td>    
            <td align="center" width="60">Tgl Kontrak</td>                             
            <td align="center">Nama Customer</td>
            <td align="center">Alamat</td>
            <td align="center" width="200">No Sertifikat Jaminan Fidusia</td>  
            <td align="center" width="100">Tgl Sertifikat (dd/mm/yyyy)</td>  
            <td align="center" width="80">Jam Sertifikat (hh:mm:ss)</td>  
        	<td align="center" width="40">No Akta</td>
        	<td align="center" width="100">Tgl Akta <br>(dd/mm/yyyy)</td>   
        	<td align="center">Nama Notaris</td>            
        	<td align="center">Hapus?<br>(isi Y)</td>
        </tr>
<?	

	while($lrow=pg_fetch_array($lrs)){	
		$fk_cabang=$lrow["fk_cabang"];
		$nominal=$lrow["pokok_hutang"];
		
		foreach($arr as $kolom){
			if(strstr($kolom,'tgl')){
				$lrow[$kolom]=($lrow[$kolom]?date("d/m/Y",strtotime($lrow[$kolom])):"");
			}
			$value[$kolom]=($_REQUEST[$kolom][$lrow["no_sbg"]]?$_REQUEST[$kolom][$lrow["no_sbg"]]:$lrow[$kolom]);
		}
?>                  		
         <tr style="padding:0 5 0 5" height="20" bgcolor='#efefef'>
<!--            <td style="padding:0 5 0 5" class="" align="left"><a href="#" class="blue" onClick="fModal('view','<?=$lrow["no_sbg"]?>','<?=$id_menu?>')"><?=$lrow["no_sbg"]?></a></td>
-->         			
			<td style="padding:0 5 0 5" class="" align="left" ><?=$lrow["no_sbg"]?></a></td>   
			<td style="padding:0 5 0 5" class="" align="left" ><?=($lrow["tgl_pengajuan"]==""?"":date("d/m/Y",strtotime($lrow["tgl_pengajuan"])))?></td>                           
			<td style="padding:0 5 0 5" class="" align="left"><?=$lrow["nm_customer"]?></td>
            <td style="padding:0 5 0 5" class="" align="left"><?=$lrow["alamat_ktp"]?></td>
			<td style="padding:0 5 0 5"align="center" ><input type="text" name="no_sertifikat_fidusia[<?=$lrow["no_sbg"]?>]" value="<?=$value['no_sertifikat_fidusia']?>" size="28"> </td>
			<td style="padding:0 5 0 5"align="center"><input type="text" name="tgl_sertifikat_fidusia[<?=$lrow["no_sbg"]?>]" value="<?=$value['tgl_sertifikat_fidusia']?>" maxlength="10" size="7"> </td>
			<td style="padding:0 5 0 5"align="center"><input type="text" name="jam_sertifikat_fidusia[<?=$lrow["no_sbg"]?>]" value="<?=$value['jam_sertifikat_fidusia']?>" maxlength="8" size="5"> </td>
			<td style="padding:0 5 0 5"align="center" ><input type="text" name="no_akta_fidusia[<?=$lrow["no_sbg"]?>]" value="<?=$value['no_akta_fidusia']?>" size="3" > </td>
			<td style="padding:0 5 0 5"align="center" ><input type="text" name="tgl_akta_fidusia[<?=$lrow["no_sbg"]?>]" value="<?=$value['tgl_akta_fidusia']?>" maxlength="10" size="7"> </td>
            <td style="padding:0 5 0 5" class="" align="left"><?=$lrow["nm_partner"]?></td>   
			<td style="padding:0 5 0 5" align="center"><input type="text" name="is_hapus[<?=$lrow["no_sbg"]?>]" value="<?=$value['is_hapus']?>" size='1' ></td>                 
           </tr>                  
<?		
	}
	
?>

<!--		<tr tyle="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
            <td style="padding:0 5 0 5" align="center" colspan="7">Total</td>
            <td style="padding:0 5 0 5" align="right"><span id="divSelectCount"><?=convert_money("",$total)?></span></td>
            <td style="padding:0 5 0 5" align="center" ></td>
            onkeyup="this.value=formating(this.value)"
        </tr>
-->    </table>

<?	
	
}

function cek_error(){
	global $strmsg,$j_action,$strisi,$strmenu,$jenis_pembayaran,$total,$query,$no_batch,$fk_cabang,$fk_bank;

/*	//echo $total;
	if($total=="" || $total=="0"){
		$strmsg.='Tidak ada yang dipilih<br>';
		//if(!$j_action) $j_action="document.form1.fk_kelurahan.focus()";
	}
	
	$l_arr_row = split(',',$strmenu);
	for ($i=0; $i<count($l_arr_row)-1; $i++){
		$query1=$query." where no_sbg='".$l_arr_row[$i]."'";
		$lrs=pg_query($query1);
		$lrow=pg_fetch_array($lrs);
		if(!$lrow["no_mesin"]){
			$strmsg.='No Kontrak '.$l_arr_row[$i].' , No Mesin masih kosong<br>';
		}
		if(!$lrow["no_rangka"]){
			$strmsg.='No Kontrak '.$l_arr_row[$i].' , No Rangka masih kosong<br>';
		}
		
	}
*/	

	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}


function save_data(){
	global $strmsg,$j_action,$strisi,$jenis_pembayaran,$strmenu,$keterangan,$fk_bank,$fk_cabang,$fk_jenis_cabang,$query,$no_batch;

	$l_success=1;
	pg_query("BEGIN");
	
	$lrs=pg_query($query);
	while($lrow=pg_fetch_array($lrs)){	
		$no_sertifikat_fidusia=$_REQUEST["no_sertifikat_fidusia"][$lrow["no_sbg"]];
		$tgl_sertifikat_fidusia=$_REQUEST["tgl_sertifikat_fidusia"][$lrow["no_sbg"]];
		$jam_sertifikat_fidusia=$_REQUEST["jam_sertifikat_fidusia"][$lrow["no_sbg"]];
		$no_akta_fidusia=$_REQUEST["no_akta_fidusia"][$lrow["no_sbg"]];
		$tgl_akta_fidusia=$_REQUEST["tgl_akta_fidusia"][$lrow["no_sbg"]];
		$is_hapus=$_REQUEST["is_hapus"][$lrow["no_sbg"]];
		
		if($tgl_sertifikat_fidusia){
			if(!validate_date(convert_date_english($tgl_sertifikat_fidusia))){
				$strmsg.=$lrow["no_sbg"]." : Format Tgl Sertifikat salah.<br>";
				$l_success=0;
			}
		}
		if($tgl_akta_fidusia){
			if(!validate_date(convert_date_english($tgl_akta_fidusia))){
				$strmsg.=$lrow["no_sbg"]." : Format Tgl Akta salah.<br>";
				$l_success=0;
			}
		}
		if($jam_sertifikat_fidusia){
			// /^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/  jam menit
			if(!preg_match('#^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$#', $jam_sertifikat_fidusia)){
				$strmsg.=$lrow["no_sbg"]." : Format Jam Akta salah.<br>";
				$l_success=0;
			}
		}
		
		if($l_success==1){
			if(!pg_query(insert_log("data_gadai.tblproduk_cicilan","no_sbg = '".$lrow["no_sbg"]."'",'UB')))$l_success=0;	
			if($is_hapus=='Y'){
				if(!pg_query("
				update data_gadai.tblproduk_cicilan set 
					no_sertifikat_fidusia=NULL ,
					tgl_sertifikat_fidusia=NULL ,
					jam_sertifikat_fidusia=NULL ,
					no_akta_fidusia=NULL ,
					tgl_akta_fidusia=NULL
				where no_sbg='".$lrow["no_sbg"]."'")) $l_success=0;
			}else{
				if(!pg_query("
				update data_gadai.tblproduk_cicilan set 
					no_sertifikat_fidusia=".(($no_sertifikat_fidusia=="")?"no_sertifikat_fidusia":"'".$no_sertifikat_fidusia."'")." ,
					tgl_sertifikat_fidusia=".(($tgl_sertifikat_fidusia=="")?"tgl_sertifikat_fidusia":"'".convert_date_english($tgl_sertifikat_fidusia)."'")." ,
					jam_sertifikat_fidusia=".(($jam_sertifikat_fidusia=="")?"jam_sertifikat_fidusia":"'".$jam_sertifikat_fidusia."'")." ,
					no_akta_fidusia=".(($no_akta_fidusia=="")?"no_akta_fidusia":"'".$no_akta_fidusia."'")." ,
					tgl_akta_fidusia=".(($tgl_akta_fidusia=="")?"tgl_akta_fidusia":"'".convert_date_english($tgl_akta_fidusia)."'")."
				where no_sbg='".$lrow["no_sbg"]."'")) $l_success=0;
			}
			//kalau inputan kosong ambil data lama biar ga hilang recordnya
			
						
						
			if(!pg_query(insert_log("data_gadai.tblproduk_cicilan","no_sbg = '".$lrow["no_sbg"]."'",'UA')))$l_success=0;		
		}
	}
	
	//$l_success=0;	
	if($l_success==1) {
		$strmsg = "Data saved<br>";
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
	
    $l_list_obj = new select("select * from tblcabang_detail_bank left join tblbank on fk_bank=kd_bank left join (select description, coa from tbltemplate_coa)as tblcoa on fk_coa=coa where fk_cabang='".$_SESSION["kd_cabang"]."' and fk_bank not in('01','03')","description","fk_bank","fk_bank");
    $l_list_obj->add_item("-- Bank ---",'',0);
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:12px;' onKeyUp='fNextFocus(event,document.getElementById(\"login\"))'");
}


function create_list_notaris(){
    global $fk_partner_notaris;
	
    $l_list_obj = new select("select * from tblpartner where fk_tipe_partner='NOTARIS' and partner_active ='t' order by nm_partner","nm_partner","kd_partner","fk_partner_notaris");
    $l_list_obj->add_item("-- Notaris ---",'',0);
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:12px;' onKeyUp='fNextFocus(event,document.getElementById(\"login\"))'","form1","document.form1.submit()");
}

function create_list_partner(){
    global $fk_partner_dealer;
	
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
		

	

