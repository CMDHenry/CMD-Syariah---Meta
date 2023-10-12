<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/file.inc.php';
require 'requires/db_utility.inc.php';
require 'classes/excel.class.php';
require 'classes/excel_reader.class.php';
//check_right("10121113");
require 'requires/validate.inc.php';
require 'classes/select.class.php';
set_time_limit(0);

$fileUpload = $_FILES["fileUpload"];

$fk_cabang_kirim=($_REQUEST["fk_cabang_kirim"]);
$fk_cabang_terima=($_REQUEST["fk_cabang_terima"]);
$penerima=($_REQUEST["penerima"]);
$fk_partner=($_REQUEST["fk_partner"]);
$fk_permohonan=($_REQUEST["fk_permohonan"]);

if($_REQUEST["status"]=="Save") {
	cek_error();
	if(!$strmsg){
		//echo $fk_partner;
		add_data();
	}
}elseif($_REQUEST["status"]=="Download"){
	create_excel();
}elseif($_REQUEST["status"]=="Save2"){
	cek_error2();
	if(!$strmsg){
		add_data2();
	}
}
?>
<html>
<head>
	<title>.: <?=$_SESSION["application"]?> :.</title>
    <link href="text.css.php" rel="stylesheet" type="text/css">
<head>
</head>
<script language='javascript' src="js/alert.js.php"></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src='js/calendar.js.php'></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src='js/validate.js.php'></script>
<script language='javascript'>
function cekError(){
	var lAlerttxt="";
	var lFocuscursor="";

	with(document.form1){
		if(fileUpload.value==""){
			lAlerttxt+='Path Kosong<br>';
			if(lFocuscursor==""){lFocuscursor="document.form1.fileUpload";}
		}
	}
	if(lAlerttxt!=""){
		alert("Error : <br>"+lAlerttxt,function(){eval(lFocuscursor+'.focus()')});
		return false
	} else return true;
}

function fGetPartner(){
	fGetNC(false,'20170900000044','fk_partner','Ganti Lokasi',document.form1.fk_partner,document.form1.fk_partner,document.form1.fk_tipe_partner,"","","","fk_tipe_partner")
    if (document.form1.fk_partner.value !="")fGetPartnerData()	
}

function fGetPartnerData(){
	
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataPartnerState
	lSentText="table=(select kd_partner, nm_partner from tblpartner) as tblmain&field=(nm_partner)&key=kd_partner&value="+document.form1.fk_partner.value
	lObjLoad.open("POST","ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}
function fGetDataPartnerState(){	
	if (this.readyState == 4){
		//confirm(this.responseText)
		if (this.status==200 && this.responseText!="") {
			lTemp=this.responseText.split('¿');
			document.getElementById('divnm_partner').innerHTML=document.form1.nm_partner.value=lTemp[0]
		} else {
			document.getElementById('divnm_partner').innerHTML=document.form1.nm_partner.value="-"
		}
	}
}

function fGetCabang(){
	fGetNC(false,"20170900000010","fk_cabang_terima","Ganti CABANG",document.form1.fk_cabang_terima,document.form1.fk_cabang_terima)
}

function fGetCabangData(){
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataCabangState
	lSentText="table= tblcabang&field=(nm_cabang)&key=kd_cabang&value="+document.form1.fk_cabang_terima.value
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
			lTemp=this.responseText.split("¿");
			document.getElementById("divNmCabang").innerHTML=document.form1.nm_cabang.value=lTemp[0]
		} else {
			document.getElementById("divNmCabang").innerHTML=document.form1.nm_cabang.value="-"
		}
	}
}


function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")) {
		lInputClose=getObjInputClose();lInputClose.close()
	}
}

function fSave2(pObj){
	document.form1.status.value='Save2';
	document.form1.submit();
}

function fSave(pObj){
	if( pObj.value=='Download Template' ){
		document.form1.status.value='Download';
		document.form1.submit();
	}else if( pObj.value=='Upload' && cekError() ) {
		document.form1.status.value='Save';
		document.form1.submit();
	}
}


function fChange(){
	if(document.form1.penerima.value=='Bank' || document.form1.penerima.value=='HO'){
		document.form1.fk_cabang_terima.value='<?=cabang_ho?>'
		document.form1.nm_cabang.value='HO'
	}else if(document.form1.penerima.value=='Cabang'  && document.form1.fk_cabang_terima.value=='<?=cabang_ho?>' ){
		document.form1.fk_cabang_terima.value=''
		document.form1.nm_cabang.value=''
	}
}
function fLoad(){
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	}else if($j_action){
		echo $j_action;
	}else{
		echo "document.form1.fileUpload.focus();";
	}
?>
}
</script>
<body onLoad="fLoad()" bgcolor="#fafafa">
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="form1" enctype="multipart/form-data">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="status">
<input type="hidden" name="fk_tipe_partner" value="FUNDING">
<? if($_SESSION["jenis_user"]=='HO'){?>
<input type="hidden" name="fk_cabang_kirim" value="<?=cabang_ho?>">    
<? }else{?>      
<input type="hidden" name="fk_cabang_kirim" value="<?=$_SESSION["kd_cabang"]?>">					
<? }?>

<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<td align="center" class="judul_menu">UPLOAD MUTASI BPKB(.xls)</td>
			</table>		
		</td>
	</tr>
    <tr>
		<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
<!--             <tr bgcolor="#efefef"> 
                <td style="padding:0 5 0 5"width="20%" class="fontColor">Cabang Pengirim</td>
                <td style="padding:0 5 0 5"width="30%">
                 </td> 
				<td width="20%" style="padding:0 5 0 5"></td>
                <td width="30%" style="padding:0 5 0 5"></td>                   
            </tr>            
-->            <tr bgcolor="#efefef"> 
                <td style="padding:0 5 0 5"width="20%" class="fontColor">Penerima</td>
                <td style="padding:0 5 0 5"width="30%">
                <? if($_SESSION["jenis_user"]=='HO'){?>
                <select name="penerima" onChange="fChange()" >
                    <option value="" <?=(($penerima == '')?'selected':'') ?>>--Pilih--</option>
                    <option value="Cabang"<?= (($penerima=='Cabang')?"selected":"") ?>>Cabang</option>
                    <option value="HO"<?= (($penerima=='HO')?"selected":"") ?>>HO</option>                    
                 	<option value="Bank"<?= (($penerima=='Bank')?"selected":"") ?>>Bank</option>
                </select>
                <? }else{?>                 
                <input type="hidden" name="penerima" value="HO">HO
                <? }?>
                </td>
                <td style="padding:0 5 0 5"width="20%" class="fontColor"></td>
                <td style="padding:0 5 0 5"width="30%">                
            </tr>
            
             <tr bgcolor="#efefef"> 
                <td style="padding:0 5 0 5"width="20%" class="fontColor">Cabang Penerima</td>
                <td style="padding:0 5 0 5"width="30%">
                <? if($_SESSION["jenis_user"]=='HO'){?>
				<input name="fk_cabang_terima" type="text" onKeyPress="if(event.keyCode==4) img_fk_cabang.click();"  value="<?=$fk_cabang_terima?>" onChange="fGetCabangData()">&nbsp;<img src="images/search.gif" id="img_fk_cabang" onClick="fGetCabang()" style="border:0px" align="absmiddle">                                  
				<? }else{?>      
                <input type="hidden" name="fk_cabang_terima" value="<?=cabang_ho?>"><?=cabang_ho?>						
				<? }?>
                 </td> 
				<td width="20%" style="padding:0 5 0 5">Nama Cabang</td>
                <td width="30%" style="padding:0 5 0 5">
                <? if($_SESSION["jenis_user"]=='HO'){?>
                    <input type="hidden" name="nm_cabang" value="<?=convert_html($nm_cabang)?>" class="groove_text" style="width:90%" > <span id="divNmCabang"><?=convert_html($nm_cabang)?></span>
                <? }else{?>  
                    <input type="hidden" name="nm_cabang" value="HO">HO		
                <? }?>
                </td>                   
            </tr>        
            <tr bgcolor="efefef">
                <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef">Kode Bank(Jika mutasi dengan bank)</td>
                <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                    <input name="fk_partner" type="text" onKeyPress="if(event.keyCode==4) img_fk_partner.click();" onKeyUp="fNextFocus(event,document.form1.periode_awal)"  value="<?=$fk_partner?>" onChange="fGetPartnerData()">&nbsp;<img src="images/search.gif" id="img_fk_partner" onClick="fGetPartner()" style="border:0px" align="absmiddle">
                </td>
                <td style="padding:0 5 0 5" width="20%">Nama Partner</td>
                <td style="padding:0 5 0 5" width="30%"><input type="hidden" name="nm_partner" class='groove_text' value="<?=$nm_partner?>"><span id="divnm_partner"><?=$nm_partner?></span></td>
	  		</tr> 
            <tr bgcolor="#efefef">
                <td width="20%" style="padding:0 5 0 5" class="fontColor">Path</td>
                <td width="80%" style="padding:0 5 0 5" colspan="3"><input type="file" name="fileUpload" class="groove_text" accept=".xls" value="aaa" ></td>
            </tr>
			</table>
<!-- end content begin -->
		</td>
    </tr>
	<tr>
    	<td height="25" align="center" bgcolor="#D0E4FF" class="border">
		<input type="button" class="groove_button" name="btnsimpan" value="Upload" onClick="fSave(this)">
        &nbsp;<input type="button" class="groove_button" value="Download Template" onClick="fSave(this)">
	</tr>
    <? if($_SESSION["jenis_user"]=='HO'){?>
    <table cellpadding="0" cellspacing="1" border="0" width="100%">
            <tr bgcolor='#efefef'> 
            <td style="padding:0 5 0 5"width="20%" class="fontColor">No Permohonan Mutasi</td>
            <td style="padding:0 5 0 5"width="30%" >
            <? create_list_permohonan();?>
            </td>
            <td style="padding:0 5 0 5"width="20%" class="fontColor"></td>
            <td style="padding:0 5 0 5"width="30%">
        </tr>
	</table>
   	<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
    <tr><td height="25" align="center" bgcolor="#D0E4FF" class="border">
        <input type="button" class="groove_button" name="btncreate" value="Create" onClick="fSave2(this)">
    </tr>
    </table>
    <? }?>
</table>
</form>
</body>
</html>
<?

//cek error file
function cek_error(){
	global $fileUpload,$strmsg,$j_action,$penerima,$fk_cabang_terima,$fk_partner;
	
	if($fileUpload['name']==""){
		$strmsg.="File Upload Error";
		if(!$j_action) $j_action="document.form1.fileUpload.focus()";
	}
	if($penerima==""){
		$strmsg.="Penerima kosong";
		if(!$j_action) $j_action="document.form1.penerima.focus()";
	}	
	
	if($fk_cabang_terima==""){
		$strmsg.="Cabang Penerima Kosong";
		if(!$j_action) $j_action="document.form1.fk_cabang_terima.focus()";
	}	
	
	if($fk_partner=="" && $penerima=="Bank"){
		$strmsg.="Bank kosong";
		if(!$j_action) $j_action="document.form1.fk_cabang_terima.focus()";
	}	
	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}

function cek_error2(){
	global $fileUpload,$strmsg,$j_action,$fk_permohonan;

	if($fk_permohonan==""){
		$strmsg.="No Permohonan Kosong";
		if(!$j_action) $j_action="document.form1.fk_permohonan.focus()";
	}
	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}
//=============================================================
//upload excel ke server
function add_data(){
	global $fileUpload,$strmsg,$j_action,$upload_path;

	$l_success=1;
	$path=$upload_path.'/temp/'.$fileUpload["name"];
	if($fileUpload['name']!=""){
		if(file_exists($path))unlink($path);
			$l_success = move_uploaded_file($_FILES["fileUpload"]["tmp_name"],$path);
			echo "<b>File terupload . . . .<br></b>";
			$l_success = update_database($path);
	}

}

function add_data2(){
	global $fileUpload,$strmsg,$j_action,$fk_permohonan;
	
	$l_success=1;
	pg_query("BEGIN");
	
	$query=("select * from data_fa.tblpermohonan_bpkb where no_permohonan = '".$fk_permohonan."' ");
	//showquery($query);
	$lrs=pg_query($query);
	$lrow = pg_fetch_array($lrs);
	
	
	$query_serial="select nextserial_cabang('MBPKB':: text,'".$lrow['fk_cabang']."')";
	$lrow_serial=pg_fetch_array(pg_query($query_serial));
	$no_mutasi=$lrow_serial["nextserial_cabang"];	
	
	$tgl_mutasi=date("m/d/Y");
		
	if(!pg_query("insert into data_fa.tblmutasi_bpkb(no_mutasi,tgl_kirim,fk_cabang_kirim,penerima,fk_cabang_terima,fk_permohonan)values('".$no_mutasi."','".$tgl_mutasi."','".cabang_ho."','Cabang','".$lrow['fk_cabang']."',".(($fk_permohonan=="")?"NULL":"'".$fk_permohonan."'").")")) $l_success=0;					
			
	//showquery("insert into data_fa.tblmutasi_bpkb(no_mutasi,tgl_kirim,fk_cabang_kirim,penerima,fk_cabang_terima,fk_permohonan)values('".$no_mutasi."','".today_db."','".cabang_ho."','Cabang','".$lrow['fk_cabang']."',".(($fk_permohonan=="")?"NULL":"'".$fk_permohonan."'").")");
	if(!pg_query("insert into data_fa.tblmutasi_bpkb_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from data_fa.tblmutasi_bpkb where no_mutasi='".$no_mutasi."'")) $l_success=0;

	$query=("select * from data_fa.tblpermohonan_bpkb_detail where fk_permohonan = '".$fk_permohonan."' ");
	$lrs=pg_query($query);
	//showquery($query);
	while($lrow = pg_fetch_array($lrs)){
	//for($i=2;$i<=$total_data;$i++){
		if($lrow["fk_sbg"]){
			
			$lrow_f=pg_fetch_array(pg_query("select * from data_gadai.tbltaksir_umum where no_sbg_ar='".$lrow["fk_sbg"]."' "));
			$posisi_bpkb=$lrow_f['posisi_bpkb'];
			//echo $posisi_bpkb.' = '.$penerima;
			//showquery("select * from data_fa.tblmutasi_bpkb_detail left join data_fa.tblmutasi_bpkb on no_mutasi=fk_mutasi where fk_sbg='".$lrow["fk_sbg"]."' and tgl_batal is null and tgl_terima is null");
							
			$index++;
			if(!pg_num_rows(pg_query("select * from tblinventory where fk_sbg='".$lrow["fk_sbg"]."' "))){
				echo "No Kontrak ".$lrow["fk_sbg"]." baris ".($i-1)." tidak ada/belum aktif <br>";
				$l_success=0;
			}//elseif(!pg_num_rows(pg_query("select * from data_gadai.tbltaksir_umum where no_sbg_ar='".$lrow["fk_sbg"]."' and fk_cabang_bpkb='".$fk_cabang_terima."'"))&& $penerima=='Cabang'){
				//echo "No Kontrak ".$lrow["fk_sbg"]." baris ".($i-1)." salah cabang <br>";
				//$l_success=0;
			//}
			elseif(pg_num_rows(pg_query("select * from data_fa.tblmutasi_bpkb_detail left join data_fa.tblmutasi_bpkb on no_mutasi=fk_mutasi where fk_sbg='".$lrow["fk_sbg"]."' and tgl_batal is null and tgl_terima is null"))){
				//showquery("select * from data_fa.tblmutasi_bpkb_detail left join data_fa.tblmutasi_bpkb on no_mutasi=fk_mutasi where fk_sbg='".$lrow["fk_sbg"]."' and tgl_batal is null and tgl_terima is null");
				echo "BPKB No Kontrak ".$lrow["fk_sbg"]." baris ".($i-1)." belum diterima di tujuan <br>";
				$l_success=0;
			}elseif(($posisi_bpkb!='Cabang' && $posisi_bpkb!='Bank') && $penerima=='HO'){
				echo "Posisi BPKB No Kontrak ".$lrow["fk_sbg"]." baris ".($i-1)." bukan Cabang/Bank  <br>";
				$l_success=0;
			}elseif($posisi_bpkb!='HO' && $penerima=='Cabang'){
				echo "Posisi BPKB No Kontrak ".$lrow["fk_sbg"]." baris ".($i-1)." bukan di HO  <br>";
				$l_success=0;
			}elseif($posisi_bpkb!='HO' && $penerima=='Bank'){
				echo "Posisi BPKB No Kontrak ".$lrow["fk_sbg"]." baris ".($i-1)." bukan di HO  <br>";
				$l_success=0;
			}elseif($penerima=='Bank' && !pg_num_rows(pg_query("select * from data_fa.tblfunding left join data_fa.tblfunding_detail on no_funding=fk_funding where fk_sbg='".$lrow["fk_sbg"]."' and fk_partner='".$fk_partner."' and tgl_unpledging is null"))){
				//showquery("select * from data_fa.tblfunding left join data_fa.tblfunding_detail on no_funding=fk_funding where fk_sbg='".$lrow["fk_sbg"]."' and fk_partner='".$fk_partner."' and tgl_unpledging is null");
				echo "Bank No Kontrak ".$lrow["fk_sbg"]." baris ".($i-1)." tidak sesuai dengan funding <br>";
				$l_success=0;
			}else{					
				if(!pg_query("insert into data_fa.tblmutasi_bpkb_detail(fk_sbg,fk_mutasi,keterangan,posisi_awal,fkt_kw,no_srt_mts) values('".$lrow["fk_sbg"]."','".$no_mutasi."','".$lrow["keterangan"]."',".(($posisi_bpkb=="")?"NULL":"'".$posisi_bpkb."'").",".(($lrow["fkt_kw"]=="")?"NULL":"'".$lrow["fkt_kw"]."'").",".(($lrow["no_srt_mts"]=="")?"NULL":"'".$lrow["no_srt_mts"]."'").")")) $l_success=0;		
				//showquery("insert into data_fa.tblmutasi_bpkb_detail(fk_sbg,fk_mutasi,keterangan,posisi_awal,fkt_kw,no_srt_mts) values('".$lrow["fk_sbg"]."','".$no_mutasi."','".$lrow["keterangan"]."',".(($posisi_bpkb="")?"NULL":"'".$posisi_bpkb."'").",".(($lrow["fkt_kw"]=="")?"NULL":"'".$lrow["fkt_kw"]."'").",".(($lrow["no_srt_mts"]=="")?"NULL":"'".$lrow["no_srt_mts"]."'").")");
	
			}		
		}
	}
		
	$l_id_log_ia=get_last_id("data_fa.tblmutasi_bpkb_log","pk_id_log");
	if(!pg_query("insert into data_fa.tblmutasi_bpkb_detail_log select *,'".$l_id_log_ia."' from data_fa.tblmutasi_bpkb_detail where fk_mutasi='".$no_mutasi."'")) $l_success=0;
	//$l_success=0;
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
//=============================================================
//update database
function update_database($file){
	global $fileUpload,$fk_cabang_terima,$ext_file,$index,$fk_cabang_kirim,$penerima,$fk_partner;
	//access ke excel
	
	$array_excel = new Spreadsheet_Excel_Reader($file); //panggil class
	$total_data = $array_excel->rowcount($sheet_index=0) ;//total data
	$l_success=1;
	pg_query("BEGIN");
	echo "<font size='-1'>Start insert/update database<br>---------------------------------<br><br>";
	$index=1;
	$query_serial="select nextserial_cabang('MBPKB':: text,'".$fk_cabang_kirim."')";
	$lrow_serial=pg_fetch_array(pg_query($query_serial));
	$no_mutasi=$lrow_serial["nextserial_cabang"];	
		
	if(!pg_query("insert into data_fa.tblmutasi_bpkb(no_mutasi,tgl_kirim,fk_cabang_kirim,penerima,fk_cabang_terima,fk_partner)values('".$no_mutasi."','".today_db."','".$fk_cabang_kirim."','".$penerima."','".$fk_cabang_terima."',".(($fk_partner=="")?"NULL":"'".$fk_partner."'").")")) $l_success=0;					
			
	//showquery("insert into data_fa.tblmutasi_bpkb(no_mutasi,tgl_kirim,fk_cabang_kirim,penerima,fk_cabang_terima)values('".$no_mutasi."','".today_db."','".$fk_cabang_kirim."','".$penerima."','".$fk_cabang_terima."')");
	if(!pg_query("insert into data_fa.tblmutasi_bpkb_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from data_fa.tblmutasi_bpkb where no_mutasi='".$no_mutasi."'")) $l_success=0;

	for($i=2;$i<=$total_data;$i++){
		$lrow=array(
				'fk_sbg'=>$array_excel->val($i,'A'),
				'no_srt_mts'=>$array_excel->val($i,'B'),
				'fkt_kw'=>$array_excel->val($i,'C'),
			);
		$lrow["fk_sbg"]=preg_replace('/[^A-Za-z0-9-.; \-]/', '', $lrow["fk_sbg"]);
		$lrow["keterangan"]=preg_replace('/[^A-Za-z0-9-.; \-]/', '', $lrow["keterangan"]);
		$lrow["fkt_kw"]=preg_replace('/[^A-Za-z0-9-.; \-]/', '', $lrow["fkt_kw"]);
		$lrow["no_srt_mts"]=preg_replace('/[^A-Za-z0-9-.; \-]/', '', $lrow["no_srt_mts"]);
		
		if(!is_numeric(substr($lrow["fk_sbg"],0,1))){
			$lrow["fk_sbg"]=substr($lrow["fk_sbg"],1);
			//echo "--".$lrow["fk_sbg"].'--';
		}		
		if($lrow["fk_sbg"]){
			//print_r($lrow);
			
			if($lrow["no_srt_mts"]==""){
				//echo "No Kontrak ".$lrow["fk_sbg"]." baris ".($i-1)." No Srt Mutasi kosong<br>";
				//$l_success=0;
			}
			//echo $lrow["fkt_kw"];
			if($lrow["fkt_kw"]!="" && $lrow["fkt_kw"]!='YA'){
				echo "No Kontrak ".$lrow["fk_sbg"]." baris ".($i-1)." FKT/KW harus diisi YA jika centang<br>";
				$l_success=0;
			}
			
			$lrow_f=pg_fetch_array(pg_query("select * from data_gadai.tbltaksir_umum where no_sbg_ar='".$lrow["fk_sbg"]."' "));
			$posisi_bpkb=$lrow_f['posisi_bpkb'];
			//echo $posisi_bpkb.' = '.$penerima;
			//showquery("select * from data_fa.tblmutasi_bpkb_detail left join data_fa.tblmutasi_bpkb on no_mutasi=fk_mutasi where fk_sbg='".$lrow["fk_sbg"]."' and tgl_batal is null and tgl_terima is null");
							
			$index++;
			if(!pg_num_rows(pg_query("select * from tblinventory where fk_sbg='".$lrow["fk_sbg"]."' "))){
				echo "No Kontrak ".$lrow["fk_sbg"]." baris ".($i-1)." tidak ada/belum aktif <br>";
				$l_success=0;
			}//elseif(!pg_num_rows(pg_query("select * from data_gadai.tbltaksir_umum where no_sbg_ar='".$lrow["fk_sbg"]."' and fk_cabang_bpkb='".$fk_cabang_terima."'"))&& $penerima=='Cabang'){
				//echo "No Kontrak ".$lrow["fk_sbg"]." baris ".($i-1)." salah cabang <br>";
				//$l_success=0;
			//}
			elseif(pg_num_rows(pg_query("select * from data_fa.tblmutasi_bpkb_detail left join data_fa.tblmutasi_bpkb on no_mutasi=fk_mutasi where fk_sbg='".$lrow["fk_sbg"]."' and tgl_batal is null and tgl_terima is null"))){
				//showquery("select * from data_fa.tblmutasi_bpkb_detail left join data_fa.tblmutasi_bpkb on no_mutasi=fk_mutasi where fk_sbg='".$lrow["fk_sbg"]."' and tgl_batal is null and tgl_terima is null");
				echo "BPKB No Kontrak ".$lrow["fk_sbg"]." baris ".($i-1)." belum diterima di tujuan <br>";
				$l_success=0;
			}elseif(($posisi_bpkb!='Cabang' && $posisi_bpkb!='Bank') && $penerima=='HO'){
				echo "Posisi BPKB No Kontrak ".$lrow["fk_sbg"]." baris ".($i-1)." bukan Cabang/Bank  <br>";
				$l_success=0;
			}elseif($posisi_bpkb!='HO' && $penerima=='Cabang'){
				echo "Posisi BPKB No Kontrak ".$lrow["fk_sbg"]." baris ".($i-1)." bukan di HO  <br>";
				$l_success=0;
			}elseif($posisi_bpkb!='HO' && $penerima=='Bank'){
				echo "Posisi BPKB No Kontrak ".$lrow["fk_sbg"]." baris ".($i-1)." bukan di HO  <br>";
				$l_success=0;
			}elseif($penerima=='Bank' && !pg_num_rows(pg_query("select * from data_fa.tblfunding left join data_fa.tblfunding_detail on no_funding=fk_funding where fk_sbg='".$lrow["fk_sbg"]."' and fk_partner='".$fk_partner."' and tgl_unpledging is null"))){
				//showquery("select * from data_fa.tblfunding left join data_fa.tblfunding_detail on no_funding=fk_funding where fk_sbg='".$lrow["fk_sbg"]."' and fk_partner='".$fk_partner."' and tgl_unpledging is null");
				echo "Bank No Kontrak ".$lrow["fk_sbg"]." baris ".($i-1)." tidak sesuai dengan funding <br>";
				$l_success=0;
			}else{					
				if(!pg_query("insert into data_fa.tblmutasi_bpkb_detail(fk_sbg,fk_mutasi,keterangan,posisi_awal,fkt_kw,no_srt_mts) values('".$lrow["fk_sbg"]."','".$no_mutasi."','".$lrow["keterangan"]."',".(($posisi_bpkb=="")?"NULL":"'".$posisi_bpkb."'").",".(($lrow["fkt_kw"]=="")?"NULL":"'".$lrow["fkt_kw"]."'").",".(($lrow["no_srt_mts"]=="")?"NULL":"'".$lrow["no_srt_mts"]."'").")")) $l_success=0;		
				showquery("insert into data_fa.tblmutasi_bpkb_detail(fk_sbg,fk_mutasi,keterangan,posisi_awal,fkt_kw,no_srt_mts) values('".$lrow["fk_sbg"]."','".$no_mutasi."','".$lrow["keterangan"]."',".(($posisi_bpkb="")?"NULL":"'".$posisi_bpkb."'").",".(($lrow["fkt_kw"]=="")?"NULL":"'".$lrow["fkt_kw"]."'").",".(($lrow["no_srt_mts"]=="")?"NULL":"'".$lrow["no_srt_mts"]."'").")");
	
			}		
		}
	}
		
	$l_id_log_ia=get_last_id("data_fa.tblmutasi_bpkb_log","pk_id_log");
	if(!pg_query("insert into data_fa.tblmutasi_bpkb_detail_log select *,'".$l_id_log_ia."' from data_fa.tblmutasi_bpkb_detail where fk_mutasi='".$no_mutasi."'")) $l_success=0;
	
	//echo "<br><br>Connection Closed<br>";
	echo "<br>----------------------------------<br>";
	//$l_success=0;
	if ($l_success==1){
		pg_query("COMMIT");
		echo('<br>DATA SUKSES NO: '.$no_mutasi);
	}else{
		pg_query("ROLLBACK");
		echo('<br>DATA GAGAL');
	}
}
//=============================================================
function create_excel(){
	global $upload_path,$penerima;
	$xls = new XLS("bpkb");

	//header excel
	$xls->xlsWriteLabel(0,0,"No.Kontrak");	
	if($penerima=='Cabang'){
	$xls->xlsWriteLabel(0,1,"No Srt Mts");
	//$xls->xlsWriteLabel(0,2,"FKT/KW (isi YA jika centang)");
	}
	//===============================================
	
	$xls->xlsOutput($upload_path,"temp");
	echo "Klik untuk men-download<br>";
	echo "<a href='file/temp/bpkb.xls'>BPKB</a><br>";
}

function create_list_permohonan(){
    global $fk_permohonan;
	
    $l_list_obj = new select("select * from data_fa.tblpermohonan_bpkb where no_permohonan not in (select fk_permohonan from data_fa.tblmutasi_bpkb where fk_permohonan is NOT NULL)","no_permohonan","no_permohonan","fk_permohonan");
    $l_list_obj->add_item("-- Pilih ---",'',0);
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:12px;' onKeyUp='fNextFocus(event,document.getElementById(\"login\"))'","form1","document.form1.submit()");
}
?>

