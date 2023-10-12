<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/file.inc.php';
require 'requires/db_utility.inc.php';
require 'classes/excel.class.php';
require 'classes/excel_reader.class.php';
require_once('classes/parsecsv.lib.php');
require 'requires/validate.inc.php';
set_time_limit(0);

$fileUpload = $_FILES["fileUpload"];

if($_REQUEST["status"]=="Save") {
	cek_error();
	if(!$strmsg){
		add_data();
	}
}else{
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
	fGetNC(false,'20170900000044','fk_partner','Ganti Lokasi',document.form1.fk_partner,document.form1.fk_partner)
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

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")) {
		lInputClose=getObjInputClose();lInputClose.close()
	}
}

function fSave(pObj){
	if( pObj.value=='Download' ){
		document.form1.status.value='Download';
		document.form1.submit();
	}else if( pObj.value=='Upload' && cekError() ) {
		document.form1.status.value='Save';
		document.form1.submit();
	}
}

function fLoad(){
	//parent.parent.document.title="Upload Part";
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
				<td align="center" class="judul_menu">UPLOAD MEMORIAL</td>
			</table>		
		</td>
	</tr>
    <tr>
		<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">

				<tr bgcolor="#efefef">
					<td width="20%" style="padding:0 5 0 5" class="fontColor">Path</td>
					<td width="30%" style="padding:0 5 0 5" ><input type="file" name="fileUpload" class="groove_text" accept=".csv" ></td>
					<td width="50%" style="padding:0 5 0 5" class="fontColor" colspan="2">
                    Format: Tgl,Kode Cabang, Tipe, Coa, Nominal, Keterangan, Referensi
                    </td>
                    
				</tr>
			</table>
<!-- end content begin -->
		</td>
    </tr>
	<tr><td height="25" align="center" bgcolor="#D0E4FF" class="border">
		<input type="button" class="groove_button" name="btnsimpan" value="Upload" onClick="fSave(this)">
		&nbsp;<input type="button" class="groove_button" value="Batal" onClick="fBatal()"></td>
	</tr>
</table>

</form>
</body>
</html>
<?
}
//cek error file
function cek_error(){
	global $fileUpload,$strmsg,$j_action;
	
	if($fileUpload['name']==""){
		$strmsg.="File Upload Error";
		if(!$j_action) $j_action="document.form1.fileUpload.focus()";
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
			echo "<b>File terupload . . . .<br>Insert/update database<br>----------------------<br></b>";
			$l_success = update_database($path);
	}

}
//=============================================================
//update database
function update_database($file){
	global $fileUpload,$nama_file,$ext_file,$index,$record_gagal,$alasan_reject,$fk_partner;
	//access ke excel
	echo "Open connection to excel<br>";
	
	//$array_excel = new Spreadsheet_Excel_Reader($file); //panggil class
	//$total_data = $array_excel->rowcount($sheet_index=0) ;//total data
	
	//echo $file;
	echo "Connected to excel<br>----------------------<br>";
	//=====================================================
	$l_success=1;
	pg_query("BEGIN");
	echo "Start insert/update database<br>---------------------------------<br><br>";
	$index=0;
	//echo $total_data;

	$bulan_accounting = get_rec("tblsetting","bulan_accounting");
	$tahun_accounting = get_rec("tblsetting","tahun_accounting");

	$csv = new parseCSV();
	$csv->auto($file);
	
	$query="select nextserial_transaksi('ADJ':: text)";
	$lrow=pg_fetch_array(pg_query($query));
	$l_no_bukti=$lrow["nextserial_transaksi"];
	$no_bukti=$l_no_bukti;		
	//for($i=2;$i<=$total_data;$i++){
	foreach ($csv->data as $key => $row){
		if($index>0){
/*		$lrow=array(
				'tanggal'=>$array_excel->val($i,'A'),
				'fk_cabang'=>$array_excel->val($i,'B'),
				'tipe'=>$array_excel->val($i,'C'),
				'coa'=>$array_excel->val($i,'D'),
				'nominal'=>$array_excel->val($i,'E'),
				'keterangan'=>$array_excel->val($i,'F'),
				'reference_transaksi'=>$array_excel->val($i,'G'),
			);*/
			$lrow=array(
				'tanggal'=>$row[0],
				'fk_cabang'=>$row[1],
				'tipe'=>$row[2],
				'coa'=>$row[3],
				'nominal'=>$row[4],
				'keterangan'=>$row[5],
				'reference_transaksi'=>$row[6],
			);			
		
		$coa = replace_space(trim($lrow["coa"]));
		
		$fk_cabang=$lrow["fk_cabang"];
		$tgl=$lrow["tanggal"];
		//echo $coa;
		$tr_tahun=date("Y",strtotime($tgl));
		$tr_bulan=date("n",strtotime($tgl));

		//if($tr_tahun.$tr_bulan !=$tahun_accounting.$bulan_accounting){
//			$l_success=0;
//			pg_query("ROLLBACK");
//			exit('Tanggal yang diinput, tidak sesuai dengan Tanggal Sistem Accounting.<br>');
//		}
		$tgl_live = get_rec("tblsetting","tgl_live");
		//echo $tgl;
		if(strtotime($tgl) < strtotime($tgl_live)){
			$l_success=0;
			pg_query("ROLLBACK");
			exit('Tgl yang diinput harus diatas '.date("d/m/Y",strtotime($tgl_live)));
		}	
		
		if($index>1){
			if($temp_tgl!=$tgl){
				  $l_success=0;
				  pg_query("ROLLBACK");
				  exit("Transaksi tidak boleh dengan tanggal yang berbeda");
			}
		}
		$temp_tgl=$tgl;
		if($coa!=""){
			if(!pg_num_rows(pg_query("select * from tblcoa where coa like '%".$coa."%' and fk_cabang = '".convert_sql($fk_cabang)."'"))){
				$l_success=0;
				pg_query("ROLLBACK");
				exit("Baris ".$index.' ,coa '.$coa .'tidak terdaftar');
			}

			if($index==1){
				$lrow["tanggal"]=preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $lrow["tanggal"]);
				showquery("insert into data_accounting.tbladjust_memorial (tr_date,description,no_bukti) values('".($lrow["tanggal"])."','UPLOAD MEMORIAL','".$l_no_bukti."')");
				
				if(!pg_query("insert into data_accounting.tbladjust_memorial (tr_date,description,no_bukti) values('".($lrow["tanggal"])."','UPLOAD MEMORIAL','".$l_no_bukti."')")){
					$l_success=0;
					pg_query("ROLLBACK");
					exit($index.' GAGAL PADA '.$lrow["kode"]);
				}//else echo $index." insert new ".$lrow["kode"]." success<br>";
			}
				
			//nanti di bawah baru update total
			if(!pg_query("
				insert into data_accounting.tbladjust_memorial_detail(
				fk_adjust_memorial,fk_account,type_tr,
				value,description,rate,total,fk_cabang_detail,
				reference_transaksi
					) values(
						'".$l_no_bukti."',
						".(($coa=="")?"NULL":"'".$coa."'").",'".$lrow["tipe"]."',
						".(($lrow["nominal"]=="")?"0":"'".$lrow["nominal"]."'").",
						".(($lrow["keterangan"]=="")?"NULL":"'".$lrow["keterangan"]."'").",
						1,".(($lrow["nominal"]=="")?"0":"'".$lrow["nominal"]."'").",'".$lrow["fk_cabang"]."',
						".(($lrow["reference_transaksi"]=="")?"NULL":"'".$lrow["reference_transaksi"]."'")."
					)
			")){
				$l_success=0;
				pg_query("ROLLBACK");
				exit($index.' GAGAL PADA '.$lrow["kode"]);
			}//else echo $index." insert new ".$lrow["kode"]." success<br>";
			
			if ($lrow["tipe"]=='D')$total_debit+=$lrow["nominal"];
			else if ($lrow["tipe"]=='C')$total_credit+=$lrow["nominal"];
			else {
				$l_success=0;
				pg_query("ROLLBACK");
				exit($index.' GAGAL PADA '.$lrow["kode"] ."tipe salah");
			}
			
		}
		}
		$index++;
		
	}
	
	 if (round($total_debit,2)!=round($total_credit,2)) {
		  $l_success=0;
		  pg_query("ROLLBACK");
		  exit("Transaksi tidak balance ".round($total_debit,2).".!=".round($total_credit,2));
		 }
		 		 
	if(!pg_query("
		  update data_accounting.tbladjust_memorial
		  set total='".$total_debit."' where no_bukti='".$l_no_bukti."' ")){
			  $l_success=0;
			  pg_query("ROLLBACK");
			  exit("update salah");
		 	}
		
	
	//echo "<br><br>Connection Closed<br>";
	echo "<br>----------------------------------<br>";
	//$l_success=0;
	if ($l_success==1){
		pg_query("COMMIT");
		exit('<br>PENGINPUTAN DATA SUKSES');
	}else{
		pg_query("ROLLBACK");
		exit('<br>PENGINPUTAN DATA GAGAL');
	}
}
//=============================================================
function replace_space($pIsi){
	return str_replace(' ','',$pIsi);
}
?>
