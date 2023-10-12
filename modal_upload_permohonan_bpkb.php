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
set_time_limit(0);

$fileUpload = $_FILES["fileUpload"];

if($_REQUEST["status"]=="Save") {
	cek_error();
	if(!$strmsg){
		//echo $fk_partner;
		add_data();
	}
}elseif($_REQUEST["status"]=="Download"){
	create_excel();
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

function fSave(pObj){
	if( pObj.value=='Download Template' ){
		document.form1.status.value='Download';
		document.form1.submit();
	}else if( pObj.value=='Upload' && cekError() ) {
		document.form1.status.value='Save';
		document.form1.submit();
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


<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<td align="center" class="judul_menu">UPLOAD PERMOHONAN BPKB(.xls)</td>
			</table>		
		</td>
	</tr>
    <tr>
		<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">                                  
            <tr bgcolor="#efefef">
                <td width="20%" style="padding:0 5 0 5" class="fontColor">Path</td>
                <td width="80%" style="padding:0 5 0 5" colspan="3"><input type="file" name="fileUpload" class="groove_text" accept=".xls" value="aaa" ></td>
            </tr>
			</table>
<!-- end content begin -->
		</td>
    </tr>
	<tr><td height="25" align="center" bgcolor="#D0E4FF" class="border">
		<input type="button" class="groove_button" name="btnsimpan" value="Upload" onClick="fSave(this)">
        &nbsp;<input type="button" class="groove_button" value="Download Template" onClick="fSave(this)">
	</tr>
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
	
	if($_SESSION["kd_cabang"]==""){
		$strmsg.="Cabang Kosong. Silakan login ulang";
		if(!$j_action) $j_action="document.form1.fk_cabang_terima.focus()";
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
	
	$fk_cabang=$_SESSION["kd_cabang"];
	$query_serial="select nextserial_cabang('PBPKB':: text,'".$fk_cabang."')";
	$lrow_serial=pg_fetch_array(pg_query($query_serial));
	$no_permohonan=$lrow_serial["nextserial_cabang"];	
		
	if(!pg_query("insert into data_fa.tblpermohonan_bpkb(no_permohonan,tgl_permohonan,fk_cabang)values('".$no_permohonan."','".today_db."','".$fk_cabang."')")) $l_success=0;					
			
	if(!pg_query("insert into data_fa.tblpermohonan_bpkb_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from data_fa.tblpermohonan_bpkb where no_permohonan='".$no_permohonan."'")) $l_success=0;

	for($i=2;$i<=$total_data;$i++){
		$lrow=array(
			'fk_sbg'=>$array_excel->val($i,'A'),
			//'sisa_angsuran'=>$array_excel->val($i,'B'),
			'diskon_denda'=>$array_excel->val($i,'B'),
			//'sisa_denda'=>$array_excel->val($i,'C'),
			'keterangan'=>$array_excel->val($i,'C'),								
		);
		$lrow["fk_sbg"]=preg_replace('/[^A-Za-z0-9-.; \-]/', '', $lrow["fk_sbg"]);
		$lrow["keterangan"]=preg_replace('/[^A-Za-z0-9-.; \-]/', '', $lrow["keterangan"]);
		
		if($lrow["fk_sbg"]){
			
			$lrow_f=pg_fetch_array(pg_query("
			select * from data_gadai.tbltaksir_umum 
			left join viewang_ke on no_sbg_ar=fk_sbg
			left join (select fk_sbg as fk_sbg4 ,saldo_denda from data_fa.tbldenda)as tbldenda on fk_sbg4=no_sbg_ar
			where no_sbg_ar='".$lrow["fk_sbg"]."' 
			"));
			$posisi_bpkb=$lrow_f['posisi_bpkb'];
			$lrow["sisa_angsuran"]=$lrow_f['saldo_pinjaman'];
			$lrow["sisa_denda"]=$lrow_f['saldo_denda'];
							
			$index++;
			if(!pg_num_rows(pg_query("select * from tblinventory where fk_sbg='".$lrow["fk_sbg"]."' "))){
				echo "No Kontrak ".$lrow["fk_sbg"]." baris ".($i-1)." tidak ada/belum aktif <br>";
				$l_success=0;
			}else{					
				if(!pg_query("insert into data_fa.tblpermohonan_bpkb_detail(fk_sbg,fk_permohonan,keterangan,posisi_bpkb,sisa_angsuran,diskon_denda,sisa_denda) values('".$lrow["fk_sbg"]."','".$no_permohonan."','".$lrow["keterangan"]."',".(($posisi_bpkb=="")?"NULL":"'".$posisi_bpkb."'").",".(($lrow["sisa_angsuran"]=="")?"0":"'".$lrow["sisa_angsuran"]."'").",".(($lrow["diskon_denda"]=="")?"0":"'".$lrow["diskon_denda"]."'").",".(($lrow["sisa_denda"]=="")?"0":"'".$lrow["sisa_denda"]."'").")")) $l_success=0;		
				//showquery("insert into data_fa.tblpermohonan_bpkb_detail(fk_sbg,fk_permohonan,keterangan,posisi_bpkb,sisa_angsuran,diskon_denda,sisa_denda) values('".$lrow["fk_sbg"]."','".$no_permohonan."','".$lrow["keterangan"]."',".(($posisi_bpkb=="")?"NULL":"'".$posisi_bpkb."'").",".(($lrow["sisa_angsuran"]=="")?"0":"'".$lrow["sisa_angsuran"]."'").",".(($lrow["diskon_denda"]=="")?"0":"'".$lrow["diskon_denda"]."'").",".(($lrow["sisa_denda"]=="")?"0":"'".$lrow["sisa_denda"]."'").")");
	
			}		
		}
	}
		
	$l_id_log_ia=get_last_id("data_fa.tblpermohonan_bpkb_log","pk_id_log");
	if(!pg_query("insert into data_fa.tblpermohonan_bpkb_detail_log select *,'".$l_id_log_ia."' from data_fa.tblpermohonan_bpkb_detail where fk_permohonan='".$no_permohonan."'")) $l_success=0;
	
	//echo "<br><br>Connection Closed<br>";
	echo "<br>----------------------------------<br>";
	//$l_success=0;
	if ($l_success==1){
		pg_query("COMMIT");
		echo('<br>DATA SUKSES NO: '.$no_permohonan);
	}else{
		pg_query("ROLLBACK");
		echo('<br>DATA GAGAL');
	}
}
//=============================================================
function create_excel(){
	global $upload_path,$penerima;
	$xls = new XLS("permohonan_bpkb");

	//header excel
	$xls->xlsWriteLabel(0,0,"No.Kontrak");	
	//$xls->xlsWriteLabel(0,1,"Sisa Angsuran");
	$xls->xlsWriteLabel(0,1,"Diskon Denda");
	//$xls->xlsWriteLabel(0,3,"Sisa Denda");
	$xls->xlsWriteLabel(0,2,"Keterangan");
	
	//===============================================
	
	$xls->xlsOutput($upload_path,"temp");
	echo "Klik untuk men-download<br>";
	echo "<a href='file/temp/permohonan_bpkb.xls'>PERMOHONAN-BPKB</a><br>";
}
?>


