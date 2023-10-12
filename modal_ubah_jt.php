<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/accounting_utility.inc.php';
require 'requires/numeric.inc.php';
require 'requires/timestamp.inc.php';

if($_SESSION["jenis_user"]!='HO'){
   $strmsg="Menu ini hanya bisa diakses oleh HO.<br>";  
   $j_action= "lInputClose=getObjInputClose();lInputClose.close()";
 }
  
$id_menu=trim($_REQUEST["id_menu"]);
get_data_menu($id_menu);	
$kd_menu_button=trim($_REQUEST["kd_menu_button"]);
$lrs=pg_query("select * from skeleton.tblmenu where kd_menu='".$kd_menu_button."'");	
$lrow=pg_fetch_array($lrs);
$nama_button=$lrow["nama_menu"];

$tanggal=$_REQUEST["tanggal"];
$bulan=$_REQUEST["bulan"];
if(!$bulan)$bulan='0';
$fk_sbg=$_REQUEST["fk_sbg"];
$nm_customer=$_REQUEST["nm_customer"];
$fk_sbg=$_REQUEST["fk_sbg"];

$no_cif=$_REQUEST["no_cif"];


if($_REQUEST["status"]=="Save") {
	cek_error();
	if(!$strmsg){
		save_data();
	}
}
if($_REQUEST["pstatus"]=="edit"){
	get_data();
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

function fGetSbg(){
	fGetCustomNC(false,'sbg','fk_sbg','Ganti Lokasi',document.form1.fk_sbg,document.form1.fk_sbg,document.form1.tipe)
}

function fGetSbgData(){
	
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataSbgState
	lSentText="table=(select fk_sbg,nm_customer,fk_cif from tblinventory left join tblcustomer on no_cif=fk_cif)as tbl&field=(nm_customer||'¿'||fk_cif)&key=fk_sbg&value="+document.form1.fk_sbg.value
	lObjLoad.open("POST","ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}
function fGetDataSbgState(){	
	if (this.readyState == 4){
		//confirm(this.responseText)
		if (this.status==200 && this.responseText!="") {
			lTemp=this.responseText.split('¿');
			//confirm(lTemp[2])
			document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value=lTemp[0]
			document.getElementById('divfk_cif').innerHTML=document.form1.fk_cif.value=lTemp[1]
		} else {
			document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value="-"
			document.getElementById('divfk_cif').innerHTML=document.form1.fk_cif.value="-"
			
		}
	}
}

function fSave(){
	///document.form1.strisi.value=table1.getIsi();
	
	if (confirm("Apakah anda yakin ?")) {
		document.form1.status.value='Save';
		//if (document.form1.strisi.value!='false') document.form1.submit();
		document.form1.submit();
	}
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")) {
		lInputClose=getObjInputClose();lInputClose.close()
	}
}


function fLoad(){
<?
	if ($strmsg){
		echo "alert('".$strmsg."',function(){".$j_action."});";
	}elseif($j_action){
		echo $j_action;
	}else{
		echo "document.form1.id_edit.focus();";
	}
?>
}
</script>
<body onLoad="fLoad();document.form1.autocomplete='off'" bgcolor="#fafafa">
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="form1" id="form1" enctype="multipart/form-data" onSubmit="return fCheckSubmit()">
<input type="hidden" name="status">
<input type="hidden" name="tipe" value="Cicilan">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="kd_menu_button" value="<?=$kd_menu_button?>">
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
                <tr bgcolor="efefef">
                    <td style="padding:0 5 0 5" width="20%" class="fontColor">No Kontrak</td>
                    <td style="padding:0 5 0 5" width="30%"><input name="fk_sbg" type="text" class='groove_text ' size="20" id="fk_sbg"  onChange="fGetSbgData()" value="<?=$fk_sbg?>">&nbsp;<img src="images/search.gif" style="border:0px" align="absmiddle" onClick="fGetSbg()"></td>
                    <td style="padding:0 5 0 5" width="20%">No CIF</td>
                    <td style="padding:0 5 0 5" width="30%"><input type="hidden" name="no_cif" class='groove_text' value="<?=$no_cif?>"><span id="divfk_cif"><?=$no_cif?></span></td>
                </tr>
                
                <tr bgcolor="efefef">
                    <td style="padding:0 5 0 5" width="20%">Nama Customer</td>
                    <td style="padding:0 5 0 5" width="30%"><input type="hidden" name="nm_customer" class='groove_text' value="<?=$nm_customer?>"><span id="divnm_customer"><?=$nm_customer?></span></td>
                    
                    <td style="padding:0 5 0 5" width="20%" class="fontColor"></td>
                    <td style="padding:0 5 0 5" width="30%"></td>
                </tr>
                
                <tr bgcolor="efefef">
                
                    <td style="padding:0 5 0 5" width="20%" class="fontColor">Jumlah bulan mundur</td>
                    <td style="padding:0 5 0 5" width="30%"><input type="text" name="bulan" class='groove_text' value="<?=$bulan?>" size="2"  maxlength="3"></td>
                    <td style="padding:0 5 0 5" width="20%" class="fontColor">Tanggal ganti</td>
                    <td style="padding:0 5 0 5" width="30%"><input type="text" name="tanggal" class='groove_text' value="<?=$tanggal?>" size="2"  maxlength="2"></td>
                    
                </tr>
            </table>

			
        </td>
    </tr>
    <tr height="20">
        <td height="25" align="center" bgcolor="#D0E4FF" class="border">
            <input class="groove_button" name="btnsimpan" type='button' value='Simpan' onClick="fSave()">
            &nbsp;<input class="groove_button" type="button" value="Batal" onClick="fBatal()">
       </td>
	</tr>
</table>
</form>
</body>
</html>
<?
function cek_error(){
	global $strmsg,$j_action,$strisi,$fk_sbg,$tanggal,$bulan;
	
	if($tanggal == ""){
		//$strmsg.="Tanggal Kosong.<br>";//dibolehin untuk hanya maju bulan
	}elseif(!is_numeric($tanggal)){
		$strmsg.="Tanggal bukan angka.<br>";
	}
	
	if($bulan == ""){
		$strmsg.="Bulan Kosong.<br>";
	}elseif(!is_numeric($bulan)){
		$strmsg.="Bulan bukan angka.<br>";
	}/*elseif($bulan<0){
		$strmsg.="Bulan salah.<br>";
	}*///dibolehin mundur karena cmd sering salah maju jto
		
	if(!pg_num_rows(pg_query("select * from tblinventory where fk_sbg='".$fk_sbg."' and tgl_lunas is null"))){
		$strmsg.="No Kontrak ".$fk_sbg.", tidak ada atau sudah pelunasan.<br>";
		$j_action="document.form1.no_sbg.focus()";		
	}	
	
	//showquery("select * from data_fa.tblangsuran where fk_sbg='".$fk_sbg."' and '".today_db."'>(date_trunc('month', tgl_jatuh_tempo::date) + interval '1 month' - interval '1 day')::date and angsuran_ke>0");
	
	//if(pg_num_rows(pg_query("select * from data_fa.tblangsuran where fk_sbg='".$fk_sbg."' and tgl_ganti_jatuh_tempo is not null"))){// ga perlu kunci karena cmd sering salah ganti jto
	if(round($bulan)!=0){
		if(pg_num_rows(pg_query("select * from data_fa.tblangsuran where fk_sbg='".$fk_sbg."' and '".today_db."'>(date_trunc('month', tgl_jatuh_tempo::date) + interval '1 month' - interval '1 day')::date and angsuran_ke>0"))){ //boleh ganti 2x kalau bulan beda
			if($_SESSION['username']!='superuser'){
			//showquery("select * from data_fa.tblangsuran where fk_sbg='".$fk_sbg."' and '".today_db."'>(date_trunc('month', tgl_jatuh_tempo::date) + interval '1 month' - interval '1 day')::date and angsuran_ke>0");
			$strmsg.="No Kontrak ".$fk_sbg.",sudah melewati batal akrual.<br>";//harus dibuka manual dan info ke accounting karena bisa buat selisih akrual neraca
			$j_action="document.form1.no_sbg.focus()";		
			}
		}
	//}		
	}

	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}

function save_data(){
	global $strmsg,$j_action,$id_edit,$fk_sbg,$tanggal,$bulan;

	$l_success=1;
	pg_query("BEGIN");
	
	$query1="select * from data_gadai.tblproduk_cicilan where no_sbg='".$fk_sbg."'";
	$lrs=pg_query($query1);
	$lrow=pg_fetch_array($lrs);
	
	$addm_addb=$lrow["addm_addb"];
	$lama_pinjaman=$lrow["lama_pinjaman"];
	$tgl_pengajuan=$lrow["tgl_pengajuan"];
	$tgl_undur=date("m/",strtotime($tgl_pengajuan)).$tanggal.date("/Y",strtotime($tgl_pengajuan));
	$tgl_pengajuan=	$tgl_undur;
	
	$eom=date("m/t/Y",strtotime('-1 month',strtotime(today_db)));
	
	if(!pg_query("insert into data_fa.tblangsuran_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from data_fa.tblangsuran where fk_sbg='".$fk_sbg."'")) $l_success=0;

	$update="tgl_jatuh_tempo_old=tgl_jatuh_tempo,";
/*	showquery("
	update data_fa.tblangsuran set 
	".$update."		
	tgl_jatuh_tempo=tgl_jatuh_tempo+ interval '1 month' * ".$bulan.",
	tgl_ganti_jatuh_tempo='".today_db."'
	where fk_sbg='".$fk_sbg."' and tgl_bayar is null 
	");
*/	
	if($bulan){
		if(!pg_query("
		update data_fa.tblangsuran set 
		".$update."		
		tgl_jatuh_tempo=tgl_jatuh_tempo+ interval '1 month' * ".$bulan.",
		tgl_ganti_jatuh_tempo='".today_db."'
		where fk_sbg='".$fk_sbg."' and tgl_bayar is null 
		")) $l_success=0;		
		$update="";
	}
	
	if($tanggal){
		if(!pg_query("
		update data_fa.tblangsuran set 
		".$update."			
		tgl_jatuh_tempo=(extract(month from tgl_jatuh_tempo)||'/'||case when (extract(month from tgl_jatuh_tempo))='2' and ".$tanggal."  in('29','30','31') then '28' else ".$tanggal." end||'/'||extract(year from tgl_jatuh_tempo))::timestamp,
		tgl_ganti_jatuh_tempo='".today_db."'
		where fk_sbg='".$fk_sbg."' and case when tgl_bayar is not null then false else true end
		")) $l_success=0;
		//and tgl_jatuh_tempo>'".$eom." 23:59:59' and case when angsuran_ke=0 then true when tgl_bayar is not null then false else true end
/*		showquery("
		update data_fa.tblangsuran set 
		".$update."			
		tgl_jatuh_tempo=(extract(month from tgl_jatuh_tempo)||'/'||case when (extract(month from tgl_jatuh_tempo))='2' and ".$tanggal."  in('29','30','31') then '28' else ".$tanggal." end||'/'||extract(year from tgl_jatuh_tempo))::timestamp,
		tgl_ganti_jatuh_tempo='".today_db."'
		where fk_sbg='".$fk_sbg."' and case when tgl_bayar is not null then false else true end
		");
*/		
	}
	
	if(pg_num_rows(pg_query("
	select count(1),fk_sbg,gabung from(
		select extract(month from tgl_jatuh_tempo)||extract(year from tgl_jatuh_tempo)as gabung, fk_sbg from data_fa.tblangsuran where angsuran_ke>0 and fk_sbg='".$fk_sbg."'
	)as tbl group by fk_sbg,gabung having count(1)>1"))){
		$strmsg.= "Ubah JT salah. Silakan cek jadwal angsuran terlebih dahulu<br>";
		$l_success=0;
	}
	
	/*
	select count(1),fk_sbg,gabung from(
select extract(month from tgl_jatuh_tempo)||extract(year from tgl_jatuh_tempo)as gabung, fk_sbg from data_fa.tblangsuran 
where angsuran_ke>0
)as tbl
group by fk_sbg,gabung
having count(1)>1
	*/

	if(!pg_query("insert into data_fa.tblangsuran_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from data_fa.tblangsuran where fk_sbg='".$fk_sbg."'")) $l_success=0;

	$lrow=pg_fetch_array(pg_query("select * from data_fa.tblangsuran where fk_sbg='".$fk_sbg."' order by angsuran_ke desc limit 1"));
	$tgl_jatuh_tempo=$lrow["tgl_jatuh_tempo"];
	//echo $tgl_jatuh_tempo;
	
	if(!pg_query("update data_gadai.tblproduk_cicilan set tgl_jatuh_tempo='".$tgl_jatuh_tempo."' where no_sbg='".$fk_sbg."'")) $l_success=0;
	if(!pg_query(insert_log("tblinventory","fk_sbg = '".$fk_sbg."'",'UB')))$l_success=0;	
	if(!pg_query("update tblinventory set tgl_jt = '".$tgl_jatuh_tempo."' where fk_sbg='".$fk_sbg."'")) $l_success=0;
	if(!pg_query(insert_log("tblinventory","fk_sbg = '".$fk_sbg."'",'UA')))$l_success=0;

	//$l_success = 0;
	if($l_success==1) {
		$strmsg = "Data Saved.<br>";
		$j_action="lInputClose=getObjInputClose();lInputClose.close();";
		$no_bukti="";
		$strisi="";
		$tr_date="";
		$description="";
		pg_query("COMMIT");
	} else {
		$strmsg.= "Error :<br> Save Failed.<br>";
		pg_query("ROLLBACK");
	}
}


function get_data(){
	global $id_edit,$strmsg,$j_action,$strisi,$no_sbg,$tgl_cair,$overdue,$nm_customer,$tgl_wo,$fk_cif,$fk_fatg;

	$lrow=pg_fetch_array(pg_query("
	select no_sbg,tgl_cair,nm_customer,date_part('day',(now()-tgl_jt))::numeric as overdue,tgl_wo,fk_cif,fk_fatg from data_gadai.tblproduk_cicilan
	inner join(
		select tgl_jt,nm_customer,fk_sbg as fk_sbg_inventory,fk_cif from tblinventory
		left join tblcustomer on fk_cif=no_cif
	) as tblinventory on fk_sbg_inventory=no_sbg
	 where no_sbg='".$id_edit."'"));
	$no_sbg=$lrow["no_sbg"];
	$tgl_cair=(($lrow["tgl_cair"]!="")?date('m/d/Y',strtotime($lrow["tgl_cair"])):"");
	$overdue=$lrow["overdue"];
	$nm_customer=$lrow["nm_customer"];
	$tgl_wo=(($lrow["tgl_wo"]!="")?date('m/d/Y',strtotime($lrow["tgl_wo"])):"");
	$fk_cif=$lrow["fk_cif"];
	$fk_fatg=$lrow["fk_fatg"];
}

?>