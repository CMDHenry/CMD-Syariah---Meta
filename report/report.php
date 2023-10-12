<?php
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/numeric.inc.php';
require '../requires/timestamp.inc.php';
require '../requires/db_utility.inc.php';
require '../classes/select.class.php';
require '../requires/file.inc.php';
require '../requires/accounting_utility.inc.php';
require '../classes/excel.class.php';
require '../requires/module.inc.php';
require '../requires/input.inc.php';
//require '../classes/report_excel.class.php';
$report=$_REQUEST["report"];
$status=$_REQUEST["status"];
$kategori=$_REQUEST["kategori"];

if($_REQUEST["tahun"]==NULL)$tahun=date('Y',strtotime(today_db));
else $tahun=$_REQUEST["tahun"];
if($_REQUEST["bulan"]==NULL)$bulan=date('n',strtotime(today_db));
else $bulan=$_REQUEST["bulan"];

$fk_cabang=($_REQUEST["fk_cabang"]);
$nm_cabang=($_REQUEST["nm_cabang"]);

$fk_wilayah=($_REQUEST["fk_wilayah"]);
$nm_wilayah=($_REQUEST["nm_wilayah"]);

$is_lapor=trim($_REQUEST["is_lapor"]);
if($is_lapor=="")$is_lapor='f';

if(!$_REQUEST["periode_awal"])$periode_awal = convert_date_english(today);
else $periode_awal = convert_date_english($_REQUEST["periode_awal"]);
if(!$_REQUEST["periode_akhir"])$periode_akhir=convert_date_english(today);
else $periode_akhir =  convert_date_english($_REQUEST["periode_akhir"]);

if(!$_REQUEST["tgl"])$tgl=convert_date_english(today);
else $tgl=convert_date_english($_REQUEST["tgl"]);

$fk_partner_dealer=($_REQUEST["fk_partner_dealer"]);
$nm_dealer=($_REQUEST["nm_dealer"]);
$kd_jenis_barang=($_REQUEST["kd_jenis_barang"]);
filter_request();

if($_REQUEST["status"]) {
	if (function_exists('cek_error'))cek_error();
	//cek_error();
	if(!$strmsg){
		download(str_replace(" ","_",$report));
	}
}

if($_REQUEST["status"]!="Download" ){
?>
<html>
<head>
	<title>.: <?=$_SESSION["application"]?> :.</title>
    <link href="../css/text.css.php" rel="stylesheet" type="text/css">
	<link rel="stylesheet" title="Style CSS" href="../js/cwcalendar.css.php" type="text/css" media="all" />
</head>
<script language='javascript' src="../js/alert.js.php"></script>
<script language='javascript' src="../js/ajax.js.php"></script>
<script language='javascript' src='../js/calendar.js.php'></script>
<script language='javascript' src="../js/openwindow.js.php"></script>
<script language='javascript' src='../js/object_function.js.php'></script>
<script language='javascript' src='../js/validate.js.php'></script>
<script language='javascript' src="../js/table_v2_report.js.php"></script>

<script language='javascript'>

<? if (function_exists('fGet'))fGet();//func javacript addtional?>

<? 
	
	echo '
	function fGetCabang(){
		fGetNC(false,"20170900000010","fk_cabang","Ganti CABANG",document.form1.fk_cabang,document.form1.fk_cabang)
	}
	
	function fGetCabangData(){
		lObjLoad = getHTTPObject()
		lObjLoad.onreadystatechange=fGetDataCabangState
		lSentText="table= tblcabang&field=(nm_cabang)&key=kd_cabang&value="+document.form1.fk_cabang.value
		lObjLoad.open("POST","../ajax/get_data.php",true);
		lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
		lObjLoad.setRequestHeader("Content-Length",lSentText.length)
		lObjLoad.setRequestHeader("Connection","close")
		lObjLoad.send(lSentText);
	}
	
	function fGetDataCabangState(){
		if (this.readyState == 4){
			//confirm(this.responseText)
			if (this.status==200 && this.responseText!="") {
				lTemp=this.responseText.split("¿");
				document.getElementById("divNmCabang").innerHTML=document.form1.nm_cabang.value=lTemp[0]
			} else {
				document.getElementById("divNmCabang").innerHTML=document.form1.nm_cabang.value="-"
			}
		}
	}
	';
	
	
	echo '
	function fGetWilayah(){
		fGetNC(false,"20171100000062","fk_wilayah","Ganti Wilayah",document.form1.fk_wilayah,document.form1.fk_wilayah)
	}
	
	function fGetWilayahData(){
		lObjLoad = getHTTPObject()
		lObjLoad.onreadystatechange=fGetDataWilayahState
		lSentText="table= tblwilayah&field=(nm_wilayah)&key=kd_wilayah&value="+document.form1.fk_wilayah.value
		lObjLoad.open("POST","../ajax/get_data.php",true);
		lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
		lObjLoad.setRequestHeader("Content-Length",lSentText.length)
		lObjLoad.setRequestHeader("Connection","close")
		lObjLoad.send(lSentText);
	}
	
	function fGetDataWilayahState(){
		if (this.readyState == 4){
			//confirm(this.responseText)
			if (this.status==200 && this.responseText!="") {
				lTemp=this.responseText.split("¿");
				document.getElementById("divNmWilayah").innerHTML=document.form1.nm_wilayah.value=lTemp[0]
			} else {
				document.getElementById("divNmWilayah").innerHTML=document.form1.nm_wilayah.value="-"
			}
		}
	}
	';

	echo '
	function fGetDealer(){
		fGetNC(false,"20170900000044","fk_partner_dealer","Ganti Wilayah",document.form1.fk_partner_dealer,document.form1.fk_tipe_partner_dealer,document.form1.fk_tipe_partner_dealer,"","","","fk_tipe_partner")
		
	}
	
	function fGetDealerData(){
		lObjLoad = getHTTPObject()
		lObjLoad.onreadystatechange=fGetDataDealerState
		lSentText="table= tblpartner&field=(nm_partner)&key=kd_partner&value="+document.form1.fk_partner_dealer.value
		lObjLoad.open("POST","../ajax/get_data.php",true);
		lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
		lObjLoad.setRequestHeader("Content-Length",lSentText.length)
		lObjLoad.setRequestHeader("Connection","close")
		lObjLoad.send(lSentText);
	}
	
	function fGetDataDealerState(){
		if (this.readyState == 4){
			//confirm(this.responseText)
			if (this.status==200 && this.responseText!="") {
				lTemp=this.responseText.split("¿");
				document.getElementById("divNmDealer").innerHTML=document.form1.nm_dealer.value=lTemp[0]
			} else {
			}
		}
	}
	
	function fGetKaryawan(){
		fGetNC(false,"20170800000027","fk_karyawan","Ganti Kota",document.form1.fk_karyawan,document.form1.fk_karyawan,document.form1.fk_karyawan,"","20170800000084","","")
		if (document.form1.fk_karyawan.value !="")fGetKaryawanData()
	}
		
	function fGetKaryawanData(){
		lObjLoad = getHTTPObject()
		lObjLoad.onreadystatechange=fGetDataKaryawanState
		lSentText="table= (select nm_depan as nm_karyawan,* from tblkaryawan) as tblkaryawan&field=(nm_karyawan)&key=npk&value="+document.form1.fk_karyawan.value
		lObjLoad.open("POST","../ajax/get_data.php",true);
		lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
		lObjLoad.setRequestHeader("Content-Length",lSentText.length)
		lObjLoad.setRequestHeader("Connection","close")
		lObjLoad.send(lSentText);
	}
	function fGetDataKaryawanState(){
		
		if (this.readyState == 4){
			if (this.status==200 && this.responseText!="") {
				lTemp=this.responseText.split("¿");
				document.getElementById("divnm_karyawan").innerHTML=document.form1.nm_karyawan.value=lTemp[0]
			} else {
				document.getElementById("divnm_karyawan").innerHTML=document.form1.nm_karyawan.value="-"
			}
		}
	}
	';

?>

function fSave(pObj){
	if(document.getElementById('strisi')){
		document.form1.strisi.value=table_detil.getIsi();
	}
	
	if(document.getElementById('strisi_cabang')){
		document.form1.strisi_cabang.value=table_detil_cabang.getIsi();
	}

	document.form1.status.value=pObj.value;
	document.form1.submit();
}

function fLoad(){
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	} elseif($j_action){
		echo $j_action;
	}
?>
}
</script>
<body onLoad="fLoad()" bgcolor="#fafafa">
<form name="form1" action="<?=$_SERVER['../report/PHP_SELF']?>" method="post">
<input type="hidden" name="status" value="<?=$status?>">
<input type="hidden" name="report" value="<?=$report?>">
<input type="hidden" name="showPeriode" value="<?=$showPeriode?>">

<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<tr><td class="judul_menu" align="center"><?=$report?></td></tr>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<? create_date() //default filter by periode?>  
                <? if (function_exists('create_filter'))create_filter();//create filter selain default yg periode?>              	
			</table>
<!-- end content begin -->
	  	</td>
    </tr>
</table>

<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" align="center">
	<tr height="20">
    	<td height="25" align="center" bgcolor="#D0E4FF">
         <input class="groove_button" type='button' name="btngenerate" value='Download' onClick="fSave(this)">
<? if($report!='LKH'){?>
		 <input class="groove_button" type='button' name="btngenerate" value='View' onClick="fSave(this)">
<? } ?>           
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?
}

function create_date(){
	global	
	$showPeriode,$periode_awal,$periode_akhir,$nm_periode,
	$showTgl,$tgl,
	$showBln,$bulan,$tahun,
	$showCab,$fk_cabang,$nm_cabang,
	$showWil,$fk_wilayah,$nm_wilayah,$is_lapor,$p,
	$showDlr,$fk_partner_dealer,$nm_dealer,
	$showJenisBarang,$kd_jenis_barang,$showKategori,$kategori
	;


	if($showPeriode=='t'){
?>	
        <tr bgcolor="efefef">
            <td width="20%" style="padding:0 5 0 5" class="">Periode <?=$nm_periode?></td>
            <td width="80%" style="padding:0 5 0 5" colspan="3">
                            <input type="text" name="periode_awal" value="<?=convert_date_indonesia($periode_awal)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_awal.click();" size="10" >&nbsp;<img src="../images/btn_extend.gif" name="img_periode_awal" onClick="fPopCalendar(document.form1.periode_awal,document.form1.periode_awal)"> -                               
                            <input type="text" name="periode_akhir" value="<?=convert_date_indonesia($periode_akhir)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_akhir.click();" size="10">&nbsp;<img src="../images/btn_extend.gif" name="img_periode_akhir" onClick="fPopCalendar(document.form1.periode_akhir,document.form1.periode_akhir)">                                
            </td>
        </tr>               
<?	
	}
	
	if($showTgl=='t'){
?>	

        <tr bgcolor="efefef">
            <td width="20%" style="padding:0 5 0 5" class="">Tanggal</td>
            <td width="80%" style="padding:0 5 0 5" colspan="3">
                    <input type="text" name="tgl" value="<?=convert_date_indonesia($tgl)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_tgl.click();" size="10" >&nbsp;<img src="../images/btn_extend.gif" name="img_tgl" onClick="fPopCalendar(document.form1.tgl,document.form1.tgl)">                               
            </td>
        </tr>               

<?	
	}
	
	if($showBln=='t'){
?>	

        <tr bgcolor="efefef">
              <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef">Periode</td>
              <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                <select name="bulan" class="groove_text" id="bulan" onChange="">
                    <option value="01" <?=(($bulan=="01")?"selected":"")?>>01</option>
                    <option value="02" <?=(($bulan=="02")?"selected":"")?>>02</option>
                    <option value="03" <?=(($bulan=="03")?"selected":"")?>>03</option>
                    <option value="04" <?=(($bulan=="04")?"selected":"")?>>04</option>
                    <option value="05" <?=(($bulan=="05")?"selected":"")?>>05</option>
                    <option value="06" <?=(($bulan=="06")?"selected":"")?>>06</option>
                    <option value="07" <?=(($bulan=="07")?"selected":"")?>>07</option>
                    <option value="08" <?=(($bulan=="08")?"selected":"")?>>08</option>
                    <option value="09" <?=(($bulan=="09")?"selected":"")?>>09</option>
                    <option value="10" <?=(($bulan=="10")?"selected":"")?>>10</option>
                    <option value="11" <?=(($bulan=="11")?"selected":"")?>>11</option>
                    <option value="12" <?=(($bulan=="12")?"selected":"")?>>12</option>
                </select>
                <input type="text" name="tahun" class="groove_text" size="4" id="tahun" value="<?=$tahun?>" onChange="">
               
              </td>
              <td width="20%" style="padding:0 5 0 5"></td>
              <td width="30%" style="padding:0 5 0 5"></td>
         </tr> 	

<?	
	}
	
	if($showCab=='t'){
		if($_SESSION["jenis_user"]=='HO'||$_SESSION["jenis_user"]=='Wilayah'){
?>	

        <tr bgcolor="efefef">
              <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef">Kode Cabang</td>
              <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                    <input name="fk_cabang" type="text" onKeyPress="if(event.keyCode==4) img_fk_cabang.click();" onKeyUp="fNextFocus(event,document.form1.periode_awal)"  value="<?=$fk_cabang?>" onChange="fGetCabangData()">&nbsp;<img src="../images/search.gif" id="img_fk_cabang" onClick="fGetCabang()" style="border:0px" align="absmiddle">
              </td>
              <td width="20%" style="padding:0 5 0 5">Nama Cabang</td>
              <td width="30%" style="padding:0 5 0 5">
                    <input type="hidden" name="nm_cabang" value="<?=convert_html($nm_cabang)?>" class="groove_text" style="width:90%" > <span id="divNmCabang"><?=convert_html($nm_cabang)?></span>
               </td>
        </tr>

<?		}else{
			$fk_cabang=$_SESSION["kd_cabang"];
			$nm_cabang=$_SESSION["nm_cabang"];
	?>
			<tr bgcolor="efefef">
				  <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef">Kode Cabang</td>
				  <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
						 <input type="hidden" name="fk_cabang" value="<?=$fk_cabang?>" class="groove_text" style="width:90%" > <?=$fk_cabang?>
				  </td>
				  <td width="20%" style="padding:0 5 0 5">Nama Cabang</td>
				  <td width="30%" style="padding:0 5 0 5">
						<input type="hidden" name="nm_cabang" value="<?=convert_html($nm_cabang)?>" class="groove_text" style="width:90%" > <span id="divNmCabang"><?=convert_html($nm_cabang)?></span>
				   </td>
			</tr>

<?	
		}

	}
	
	if($showWil=='t'){
		if($_SESSION["jenis_user"]=='HO'||$_SESSION["jenis_user"]=='Wilayah'){
?>	

        <tr bgcolor="efefef">
              <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef">Kode Wilayah</td>
              <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                    <input name="fk_wilayah" type="text" onKeyPress="if(event.keyCode==4) img_fk_wilayah.click();" onKeyUp="fNextFocus(event,document.form1.periode_awal)"  value="<?=$fk_wilayah?>" onChange="fGetWilayahData()">&nbsp;<img src="../images/search.gif" id="img_fk_wilayah" onClick="fGetWilayah()" style="border:0px" align="absmiddle">
              </td>
              <td width="20%" style="padding:0 5 0 5">Nama Wilayah</td>
              <td width="30%" style="padding:0 5 0 5">
                    <input type="hidden" name="nm_wilayah" value="<?=convert_html($nm_wilayah)?>" class="groove_text" style="width:90%" > <span id="divNmWilayah"><?=convert_html($nm_wilayah)?></span>
               </td>
        </tr>

<?		}
	}
	

	if($showDlr=='t'){
?>	
		<input type="hidden" name="fk_tipe_partner_dealer" value="DEALER">
        <tr bgcolor="efefef">
              <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef">Kode Dealer</td>
              <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                    <input name="fk_partner_dealer" type="text" onKeyPress="if(event.keyCode==4) img_fk_partner_dealer.click();" onKeyUp="fNextFocus(event,document.form1.periode_awal)"  value="<?=$fk_partner_dealer?>" onChange="fGetDealerData()">&nbsp;<img src="../images/search.gif" id="img_fk_partner_dealer" onClick="fGetDealer()" style="border:0px" align="absmiddle">
              </td>
              <td width="20%" style="padding:0 5 0 5">Nama Dealer</td>
              <td width="30%" style="padding:0 5 0 5">
                    <input type="hidden" name="nm_dealer" value="<?=convert_html($nm_dealer)?>" class="groove_text" style="width:90%" > <span id="divNmDealer"><?=convert_html($nm_dealer)?></span>
               </td>
        </tr>

<?		
	}
	
	if($showJenisBarang=='t'){
?>	
        <tr bgcolor="efefef">
              <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef">Jenis Barang</td>
              <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                    <?=create_list_jenis_barang()?>
              </td>
              <td width="20%" style="padding:0 5 0 5"></td>
              <td width="30%" style="padding:0 5 0 5">
              </td>
        </tr>

<?		
	}
	
	if($showKategori=='t'){
?>	
         <tr bgcolor="efefef">
              <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef">Kategori</td>
              <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                <select name="kategori" class="groove_text" id="kategori" onChange="">
                	<option value="">-- Pilih --</option>
                    <option value="R2" <?=(($kategori=="R2")?"selected":"")?>>R2</option>
                    <option value="R4" <?=(($kategori=="R4")?"selected":"")?>>R4</option>
                </select>               
              </td>
              <td width="20%" style="padding:0 5 0 5"></td>
              <td width="30%" style="padding:0 5 0 5"></td>
         </tr>

<?		
	}
	
	if($p=='Y'){
?>	
         <tr bgcolor="efefef">
              <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef">Lapor OJK</td>
              <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                <input type="checkbox" id="is_lapor" name="is_lapor" class="groove_checkbox" value="t" <?=(($is_lapor=="t")?"checked":"")?>>               
              </td>
              <td width="20%" style="padding:0 5 0 5"></td>
              <td width="30%" style="padding:0 5 0 5"></td>
         </tr>

<?		
	}
	
}

function create_header(){
	global $report,
	$showPeriode,$periode_awal,$periode_akhir,$nm_periode,
	$showTgl,$tgl,
	$showBln,$bulan,$tahun,
	$showCab,$fk_cabang,$nm_cabang,
	$showWil,$fk_wilayah,$nm_wilayah,
	$judulTgl

	;
	//header default
	//nm_periode bisa diisi di filter request kalo mau ada namany
	
	echo  
	'<table border="0">
			<tr>
				<td valign="top" colspan="13">REPORT '.$report.'</td>            
			</tr>';
	if($showPeriode=='t'){
		echo'		
			<tr>
				<td valign="top" colspan="13">Periode '.strtoupper($nm_periode).': '.convert_date_indonesia($periode_awal).'-'.convert_date_indonesia($periode_akhir).'</td>            
			</tr>';			     
	}
	
	if($showTgl=='t'){
		if($judulTgl==2){
		echo'		
			<tr>
				<td valign="top" colspan="13">Tanggal : '.convert_date_indonesia(date("m/01/Y",strtotime($tgl))).' - '.convert_date_indonesia($tgl).'</td>            
			</tr>';			     
		}else{
		echo'		
			<tr>
				<td valign="top" colspan="13">Tanggal : '.convert_date_indonesia($tgl).'</td>            
			</tr>';			     
		}
	}
	
	if($showBln=='t'){
		echo'		
			<tr>
				<td valign="top" colspan="13">Periode '.$bulan.' - '.$tahun.'</td>            
			</tr>';			     
	}
		
	if($showCab=='t'){
		echo'		
			<tr>
				<td valign="top" colspan="13">Cabang : '.$fk_cabang.' - '.$nm_cabang.'</td>            
			</tr>';			     
	}
	
	if($showWil=='t' &&$_SESSION["jenis_user"]=='HO'){
		echo'		
			<tr>
				<td valign="top" colspan="13">Wilayah : '.$fk_wilayah.' - '.$nm_wilayah.'</td>            
			</tr>';			     
	}
	
	echo '		     
			<td valign="top" colspan="13"></td>  
	 </table>
	 ';
	
}

function create_list_jenis_barang(){
	global $kd_jenis_barang;
	$l_list_obj = new select("select * from tbljenis_barang order by nm_jenis_barang","nm_jenis_barang","kd_jenis_barang","kd_jenis_barang");
	$l_list_obj->set_default_value($kd_jenis_barang);
	$l_list_obj->add_item("--- Jenis Barang ---",'',0);
	$l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;' onKeyUp='fNextFocus(event,document.form1.nm_jenis_barang)'");
}

function download($report){
	global $status;
	if($status!='View'){
		$nm_file="REPORT_".$report.".xls";
		header("Content-type: application/vnd.ms-excel");		
		header("Content-Disposition: attachment; filename=".$nm_file);
/*		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="sample.csv"');
*/
		header("Pragma: no-cache");
		header("Expires: 0");
	}
	
	create_header();	//default header
	if (function_exists('create_header_additional'))create_header_additional();	//header tambahan 
	excel_content(); //isi data

}

function generate_report_acc($bulan,$tahun,$fk_cabang,$kd_coa,$is_perbandingan='f',$fk_wilayah,$is_summary='f'){
		
	$tgl=$bulan.'/01/'.$tahun;
	//$bulan_lalu=date('n', strtotime('-1 second',strtotime($tgl)));
	$bulan_lalu=date('n',strtotime(get_last_month($tgl,1)));
	$tahun_lalu=date('Y',strtotime(get_last_month($tgl,1)));
	
	if($fk_cabang)$lwhere=" kd_cabang in (".get_cabang_terkait($fk_cabang).")";
	
	if($fk_wilayah){
		if($lwhere)$lwhere .= ' and ';
		$lwhere .= " fk_wilayah = '".convert_sql($fk_wilayah)."' ";
	}
	
	if($lwhere)$lwhere = " where ".$lwhere;
	
	$all_cabang='99999';
	$arr[$all_cabang]["initial"]=$all_cabang;	
	
	$index[0]='MTD';
	$index[1]='YTD';
	if($is_perbandingan=='t'){
		$index[2]='MTD-1';
	}
	$query_cab = "
		select * from tblcabang
		".$lwhere."
		order by kd_cabang
	";
	//showquery($query_cab);
	$lrs_cab= pg_query($query_cab);
	while($lrow_cab=pg_fetch_array($lrs_cab)){
		$kd_cabang=$lrow_cab["kd_cabang"];
		
		if($kd_coa=='1|2|3'){//ganti
			pg_query("begin");
			$tgl_live = get_rec("tblsetting","tgl_live");
			$bulan_acc=get_rec("tblsetting","bulan_accounting");
			$tahun_acc=get_rec("tblsetting","tahun_accounting");
			$tgl_acc=$bulan_acc.'/01/'.$tahun_acc;
			//&& strtotime($bulan.'/01/'.$tahun) >= strtotime($tgl_acc)
			if (strtotime($bulan.'/01/'.$tahun) <= strtotime(today_db)){
				if (strtotime($bulan.'/01/'.$tahun) >= strtotime($tgl_acc)){
					if(strtotime($bulan.'/01/'.$tahun) >= strtotime($tgl_live)   ){
						//echo $bulan.'='.$tahun;
						if( calculate_lb($bulan.'/01/'.$tahun,$kd_cabang) ) pg_query("commit") ;							
					}else pg_query("rollback");		
				}
			}
		}
		
		
		$initial=$lrow_cab["initial_cabang"];
		$arr[$kd_cabang]["initial"]=$initial;	
		$nm_cabang=$lrow_cab["nm_cabang"];
		$arr[$kd_cabang]["nm_cabang"]=$nm_cabang;	
		//ganti
		$query = "
			select 
			case when substr(coa,1,1)='1' then 'Aktiva' 
				 when substr(coa,1,1)in('2','3') then 'Passiva'
				 when substr(coa,1,1)='4' then 'Pendapatan'
				 when substr(coa,1,1)='5' then 'Biaya'
			end	as tipe,
			fk_head_account as head_coa,* from tbltemplate_coa		
			where coa similar to '(".$kd_coa.")%' -- and coa not like '%000'
			order by coa asc
		";
		$lrs = pg_query($query);
		//showquery($query);
		while($lrow=pg_fetch_array($lrs)){
			
			$coa_natural=$lrow['coa'];
			$coa_cab=$kd_cabang.'.'.$coa_natural;
			
			$lbalance['MTD'] = get_balance('','',$kd_cabang,$bulan,$tahun,'',$coa_cab);		
			//echo $lbalance['MTD'].'<br>';
			$lbalance['MTD-1']=get_balance('','',$kd_cabang,$bulan_lalu,$tahun_lalu,'',$coa_cab);	
			//echo $lbalance['MTD-1'].'aa<br>';
			if($kd_coa=='1|2|3'){
				$lbalance['YTD']=$lbalance['MTD'];
				$lbalance['MTD']=$lbalance['YTD']-$lbalance['MTD-1'];				
			}elseif($kd_coa=='4|5'){
				$lbalance['YTD']=0;
				for($i=1;$i<$bulan;$i++){
					$lbalance['YTD'] +=get_balance('','',$kd_cabang,$i,$tahun,'',$coa_cab);
					$lbalance['MTD-1']=$lbalance['YTD'];
				}
				$lbalance['YTD']  +=$lbalance['MTD'];
			}			
			
			if(($lbalance['YTD'] && $lbalance['YTD']!=0)||$lbalance['MTD'] && $lbalance['MTD']!=0){
				$head_coa=$lrow["head_coa"];
				$desc[$coa_natural]=$lrow['description'];
				$arr_head[$coa_natural]=$head_coa;	
				
				for($j=0;$j<count($index);$j++){
					$arr_saldo[$head_coa][$coa_natural][$kd_cabang][$index[$j]]=$lbalance[$index[$j]];												
					$total_head[$head_coa][$kd_cabang][$index[$j]]+=$lbalance[$index[$j]];	
					$total[$lrow['tipe']][$kd_cabang][$index[$j]]+=$lbalance[$index[$j]];
					
					$arr_saldo[$head_coa][$coa_natural][$all_cabang][$index[$j]]+=$lbalance[$index[$j]];											
					$total_head[$head_coa][$all_cabang][$index[$j]]+=$lbalance[$index[$j]];
					$total[$lrow['tipe']][$all_cabang][$index[$j]]+=$lbalance[$index[$j]];
				
				}							
			}
		}	
	}
	//print_r($arr);
	ksort($arr);
	echo 	
	'<table border="1">
	     <tr>
			<td rowspan="2">COA</td>
			<td rowspan="2">DESCRIPTION</td>
	';
	foreach($arr as $kd_cabang=>$arr2){
		$initial=$arr2['initial'];
		if($kd_cabang==$all_cabang){
			$kd_cabang='Grand Total';
			$initial="";
		}
		echo '						
			<td colspan="'.count($index).'" align="center">'.$kd_cabang.' '.$arr2['nm_cabang'].'</td>
		';					
	}
	
	echo '<tr>';
	
	foreach($arr as $kd_cabang=>$arr2){
		for($j=0;$j<count($index);$j++){

			echo '						
				<td align="center">'.$index[$j].'</td>
			';		
		}
	}
	
	echo '</tr>';
		

	//print_r($arr_saldo);
	
	if(count($arr_saldo)>0){
		ksort($arr_saldo);
		foreach($arr_saldo as $head_coa=>$arr_coa){
			//$desc_head=get_rec("tbltemplate_coa","description","coa='".$head_coa."'");
			$desc_head=get_rec("tblhead_account","description","code='".$head_coa."'");

			echo 	
			'
				<tr>
					<td><b>'.$head_coa.'</b></td>
					<td><b>'.$desc_head.'</b></td>
			';					
			foreach($arr as $kd_cabang=>$arr2){
				for($j=0;$j<count($index);$j++){				
					echo '						
						<td align="right"><b>'.convert_money("",$total_head[$head_coa][$kd_cabang][$index[$j]]).'</b></td>						
					';			
				}
			}
			echo '</tr>';	
			
			ksort($arr_coa);
			if($is_summary=='f'){
				
				foreach($arr_coa as $coa=>$saldo){
					echo 	
					'
						<tr>
							<td>'.$coa.'</td>
							<td>'.$desc[$coa].'</td>				
					';
					foreach($arr as $kd_cabang=>$arr2){
						for($j=0;$j<count($index);$j++){				
							echo '						
								<td align="right">'.convert_money("",$saldo[$kd_cabang][$index[$j]]).'</td>
							';	
						}
					}					
					echo '</tr>';
				}
			}
		}
	}
	
	if($kd_coa=='1|2|3'){
		$data[0]='Aktiva';
		$data[1]='Passiva';
										
	}else if($kd_coa=='4|5'){
		$data[0]='Pendapatan';
		$data[1]='Biaya';
		$data[2]='Laba(Rugi)';		
		foreach($arr as $kd_cabang=>$arr2){
			for($j=0;$j<count($index);$j++){	
				$total[$data[2]][$kd_cabang][$index[$j]]=$total[$data[0]][$kd_cabang][$index[$j]]-$total[$data[1]][$kd_cabang][$index[$j]];	
			}
				
		}
	}
	
	for($i=0;$i<count($data);$i++){
		echo 	
		'	<tr>
				<td colspan="2"><b>Total '.$data[$i].'</b></td>
		';		
		
		foreach($arr as $kd_cabang=>$arr2){
			for($j=0;$j<count($index);$j++){	
				echo '						
					<td align="right"><b>'.convert_money("",$total[$data[$i]][$kd_cabang][$index[$j]]).'</b></td>
				';					
			}
		}
		
	}
	
	
	return $table;
}

function convert_to_csv($data){
	$data=trim(preg_replace('!\s+!', ' ', $data));
	$data=str_replace('> <',"><",$data);
	$data=str_replace('<table border="1">',"",$data);	
	$data=str_replace('<td align="center">',"",$data);
	$data=str_replace('<td align="right">',"",$data);
	$data=str_replace('<td>',"",$data);
	$data=str_replace('&nbsp;',"",$data);
	$data=str_replace('<br>',"",$data);	
	$data=str_replace('</td>',"|",$data);
	$data=str_replace('<tr>',"",$data);
	$data=str_replace('</tr>',"\r\n",$data);	
	$data=str_replace('</table>',"",$data);	
	$data=str_replace(';',".",$data);	
	return $data;

}

