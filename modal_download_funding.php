<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/numeric.inc.php';
require 'requires/timestamp.inc.php';
require 'requires/db_utility.inc.php';
require 'classes/select.class.php';
require 'requires/file.inc.php';
require 'requires/accounting_utility.inc.php';
require './classes/smtp.class.php';
require 'classes/excel.class.php';
//require 'classes/select.class.php';;
$strmenu=$_REQUEST["strmenu"];
//echo $strmenu;
$periode_awal = convert_date_english($_REQUEST["periode_awal"]);
$periode_akhir= convert_date_english($_REQUEST["periode_akhir"]);
$fk_cabang=($_REQUEST["fk_cabang"]);
$nm_cabang=($_REQUEST["nm_cabang"]);
$usia_overdue_awal = ($_REQUEST["usia_overdue_awal"]);
$usia_overdue_akhir = ($_REQUEST["usia_overdue_akhir"]);
$nilai_pinjaman_awal = ($_REQUEST["nilai_pinjaman_awal"]);
$nilai_pinjaman_akhir = ($_REQUEST["nilai_pinjaman_akhir"]);

//echo $nilai_pinjaman_awal."aaa";
if($_REQUEST["status"]=="Save") {
	pg_query("BEGIN");
	cek_error();
	if (!$strmsg){
		 save_data();
	}else pg_query("ROLLBACK");
}
if($_REQUEST["status"]!="Save") {

?>

<html>
<head>
	<title>.: <?=$_SESSION["application"]?> :.</title>
    <link href="text.css.php" rel="stylesheet" type="text/css">
	<link rel="stylesheet" title="Style CSS" href="js/cwcalendar.css.php" type="text/css" media="all" />
</head>
<script language='javascript' src="js/alert.js.php"></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src='js/calendar.js.php'></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src='js/validate.js.php'></script>
<script language='javascript' src='js/report.js.php'></script>
<script language='javascript'>

function fSave(){
	document.form1.status.value='Save';
	document.form1.submit();
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")){
		lInputClose=getObjInputClose();lInputClose.close()
	}
}
function fconcat(){
	document.form1.strmenu.value='';
	for(var i=0;i<document.form1.strcek.length;i++){
		x=document.form1.strcek[i].value.split(',');
		if(document.form1.strcek[i].value!='all'){
			if(document.form1.strcek[i].checked==true){
				document.form1.strmenu.value+=x[0]+',';
			}
		}
	}
}

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

function fGetCheck(){
	if(document.form1.strcek.checked==true){
		document.form1.strmenu.value=document.form1.strcek.value+',';
	}
	
}
function fLoad(){
	//parent.parent.document.title="TTBJ";
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	} elseif($j_action){
		echo $j_action;
	}else{
		echo "document.form1.periode_awal.focus();";
	}
?>
}
</script>
<body onLoad="fLoad()" bgcolor="#fafafa">
<form name="form1" action="<?=$_SERVER['PHP_SELF']?>" method="post">
<input type="hidden" name="status">
<input type="hidden" name="strmenu" value="<?=$strmenu?>">

<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<tr><td class="judul_menu" align="center">FUNDING</td></tr>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<!--<tr bgcolor="efefef">
				  <td width="20%" style="padding:0 5 0 5" class="fontColor">Periode</td>
				  <td width="30%" style="padding:0 5 0 5" colspan="3">
  							<input type="text" name="periode_awal" value="<?=convert_date_indonesia($periode_awal)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_awal.click();" size="10">&nbsp;<img src="images/btn_extend.gif" name="img_periode_awal" onClick="fPopCalendar(document.form1.periode_awal,document.form1.periode_awal)"> - 
                            <input type="text" name="periode_akhir" value="<?=convert_date_indonesia($periode_akhir)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_akhir.click();" size="10">&nbsp;<img src="images/btn_extend.gif" name="img_periode_akhir" onClick="fPopCalendar(document.form1.periode_akhir,document.form1.periode_akhir)">  
                  </td>
                  
                  <td width="20%" style="padding:0 5 0 5" class="fontColor"></td>
				  <td width="30%" style="padding:0 5 0 5" colspan="3"></td>
                 </tr>-->
                 
             <!--    <tr bgcolor="efefef"> 
                  <td width="20%" style="padding:0 5 0 5" class="fontColor">Kriteria</td>
				  <td width="30%" style="padding:0 5 0 5" colspan="3"><input type="text" name="nm_partner" class='groove_text' value="<?=$nm_partner?>"><?=$nm_partner?></td>
                  
                  <td width="20%" style="padding:0 5 0 5" class="fontColor"></td>
				  <td width="30%" style="padding:0 5 0 5" colspan="3"></td>
                 </tr>-->
                 
                 <tr bgcolor="efefef">
                 	<td width="20%" style="padding:0 5 0 5" class="fontColor">Usia Overdue</td>
				 	<td width="30%" style="padding:0 5 0 5" colspan="3"><input type="numeric" name="usia_overdue_awal" class='groove_text_numeric' size='10' value="<?=$usia_overdue_awal?>"> - <input type="numeric" name="usia_overdue_akhir" size='10' class='groove_text_numeric' value="<?=$usia_overdue_akhir?>"></td>
                    
                    <td width="20%" style="padding:0 5 0 5" class="fontColor"></td>
				 	<td width="30%" style="padding:0 5 0 5" colspan="3"></td>
                 </tr>
                 
                 <tr bgcolor="efefef">
                 	<td width="20%" style="padding:0 5 0 5" class="fontColor">Nilai Pinjaman</td>
				 	<td width="30%" style="padding:0 5 0 5" colspan="3"><input type="numeric" name="nilai_pinjaman_awal" class='groove_text_numeric' size='10' value="<?=$nilai_pinjaman_awal?>"> - <input type="numeric" name="nilai_pinjaman_akhir" size='10' class='groove_text_numeric' value="<?=$nilai_pinjaman_akhir?>"></td>
                    
                    <td width="20%" style="padding:0 5 0 5" class="fontColor"></td>
				 	<td width="30%" style="padding:0 5 0 5" colspan="3"></td>
                 </tr>
                 
                 <tr bgcolor="efefef">
                 	<td width="20%" style="padding:0 5 0 5" class="fontColor">Kode Cabang</td>
				 	<td width="30%" style="padding:0 5 0 5" colspan="3"><input name="fk_cabang" type="text" onKeyPress="if(event.keyCode==4) img_fk_cabang.click();" onKeyUp="fNextFocus(event,document.form1.periode_awal)"  value="<?=$fk_cabang?>" onChange="fGetCabangData()">&nbsp;<img src="images/search.gif" id="img_fk_cabang" onClick="fGetCabang()" style="border:0px" align="absmiddle"></td>
                    
                    <td width="20%" style="padding:0 5 0 5" class="fontColor">Nama Cabang</td>
				 	<td width="30%" style="padding:0 5 0 5" colspan="3"><input type="hidden" name="nm_cabang" value="<?=convert_html($nm_cabang)?>" class="groove_text" style="width:90%" > <span id="divNmCabang"><?=convert_html($nm_cabang)?></span></td>
                 </tr>
			</table>
<!-- end content begin -->
	  	</td>
    </tr>
</table>

<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" align="center">
	<tr height="20">
    	<td height="25" align="center" bgcolor="#D0E4FF">
            <input class="groove_button" type='button' name="btngenerate" value='Download' onClick="fSave();">
            &nbsp;<input class="groove_button" type="button" value="Batal" onClick="fBatal()">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?
}
function cek_error(){
	global  $j_action,$strmsg,$periode_awal,$periode_akhir,$tipe,$strmenu;
	
	
	if ($strmsg) $strmsg="Error:<br>".$strmsg;
	
}


function save_data(){
global $periode_awal,$periode_akhir,$kd_gudang_part,$strmenu,$usia_overdue_awal,$usia_overdue_akhir,$nilai_pinjaman_awal,$nilai_pinjaman_akhir,$fk_cabang;

header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=FUNDING");
header("Pragma: no-cache");
header("Expires: 0");


	//if($periode_awal != '' && $periode_akhir != ''){
//		$lwhere.=" tgl_cair between '".$periode_awal." 00:00:00' and '".$periode_akhir." 23:59:59'";
//	}
	
	if($usia_overdue_awal >0 && $usia_overdue_akhir >0){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" overdue between ".$usia_overdue_awal." and ".$usia_overdue_akhir." ";
	}
	
	if($nilai_pinjaman_awal >0 && $nilai_pinjaman_akhir >0){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" total_nilai_pinjaman between ".$nilai_pinjaman_awal." and ".$nilai_pinjaman_akhir." ";
	}
	
	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cabang = '".$fk_cabang."' ";
	}
	
	if ($lwhere!="") $lwhere=" where ".$lwhere;

	$query = "
		select * from (
			select tenor,nm_customer,total_taksir,berat_bersih,fk_cabang,fk_sbg,tblinventory.tgl_cair,tgl_jt,date_part('day',('".today_db."'-tgl_jt))::numeric as overdue, total_nilai_pinjaman from tblinventory
			left join viewsbg on fk_sbg=fk_sbg1
			left join viewkontrak on no_sbg=fk_sbg
			left join (
			   select total_taksir,no_fatg,berat_bersih from viewtaksir
			   left join (
					select sum(berat_bersih) as berat_bersih,fk_fatg as fk_fatg2 from viewtaksir_detail group by fk_fatg
				 ) as tbltaksir_detail on no_fatg=fk_fatg2 
			  ) as tbltaksir on no_fatg=fk_fatg
			left join tblcustomer on fk_cif = no_cif
			left join(
					select distinct on(fk_sbg)nm_partner,fk_sbg as fk_sbg_funding from data_fa.tblfunding
					left join data_fa.tblfunding_detail on no_funding=fk_funding
					left join tblpartner on fk_partner =kd_partner
					where tgl_unpledging is null
			)as tblfunding on fk_sbg = fk_sbg_funding	

			where status_sbg='Liv' AND fk_sbg_funding is null
		) as tblmain
	".$lwhere." order by tgl_cair
	";
	//showquery($query);
?>

<table border="1">
            <tr>
                <td align="center">No SBG</td>
                <td align="center">Tanggal Cair</td>
                <td align="center">Tanggal Jatuh Tempo</td>
                <td align="center">Overdue</td>                
                <td align="center">Total Nilai Pinjaman</td>
                <td align="center">Kode Cabang</td>
                <td align="center">Nama Nasabah</td>
                <td align="center">Berat</td>
                <td align="center">Taksiran</td>
                <td align="center">Tenor</td>
                
                
            </tr>
    	
<?
	$lrs = pg_query($query);
	$no=1;
		while($lrow = pg_fetch_array($lrs)){
?>
			<tr>
				<td valign="top">&nbsp;<?=$lrow["fk_sbg"]?></td>
				<td valign="top"><?=date("d/m/Y",strtotime($lrow["tgl_cair"]))?></td>
				<td valign="top"><?=date("d/m/Y",strtotime($lrow["tgl_jt"]))?></td>
                <td valign="top"><?=$lrow["overdue"]?></td>
                <td valign="top"><?=$lrow["total_nilai_pinjaman"]?></td>
                <td valign="top"><?=$lrow["fk_cabang"]?></td>
                <td valign="top"><?=$lrow["nm_customer"]?></td>
                <td valign="top"><?=$lrow["berat_bersih"]?></td>
                <td valign="top"><?=$lrow["total_taksir"]?></td>
                <td valign="top"><?=$lrow["tenor"]?></td>
                
			</tr>
				
<?			
			$no++;
		}
	
?>
</table>

<?
}?>