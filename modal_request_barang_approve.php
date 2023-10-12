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
require 'classes/smtp.class.php';
require 'classes/excel.class.php';
require 'requires/stok_utility.inc.php';

if($_SESSION["jenis_user"]!='HO'){
   $strmsg="Menu ini hanya bisa diakses oleh HO.<br>";  
   $j_action= "lInputClose=getObjInputClose();lInputClose.close()";
  }

$id_menu=trim($_REQUEST["id_menu"]);
get_data_menu($id_menu);	

$kd_barang = trim($_REQUEST['kd_barang']);
$nm_barang = $_REQUEST['nm_barang'];
$fk_jenis_barang = $_REQUEST['fk_jenis_barang'];
//echo $fk_jenis_barang;
$tahun_produksi = $_REQUEST['tahun_produksi'];
$fk_tipe = $_REQUEST['fk_tipe'];
$harga = round($_REQUEST['harga']);
$nm_tipe=$_REQUEST['nm_tipe'];
$nm_merek=$_REQUEST['nm_merek'];
$kd_request = $_REQUEST['kd_request'];
$fk_grade=$_REQUEST['kd_grade'];

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
    <link href="css/text.css.php" rel="stylesheet" type="text/css">
	<link rel="stylesheet" title="Style CSS" href="js/cwcalendar.css.php" type="text/css" media="all" />
</head>
<script language='javascript' src="js/alert.js.php"></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src='js/calendar.js.php'></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src='js/validate.js.php'></script>
<!--<script language='javascript' src="js/table_v2.js.php"></script>
-->
<script language='javascript'>

function fDownload(){
	document.form1.status.value='Save';
	document.form1.submit();
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")){
		lInputClose=getObjInputClose();lInputClose.close()
	}
}
function fGetReq(){
	fGetCustomNC(false,'request_barang','kd_request','Ganti Lokasi',document.form1.kd_request,document.form1.kd_request,document.form1.kd_request)
}

function fGetReqData(){
	
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataReqState
	lSentText="table=(select * from data_gadai.tblrequest_barang left join tbltipe on fk_tipe=kd_tipe left join tblmerek on fk_merek=kd_merek)as tbl&field=(kd_barang||'¿'||nm_barang||'¿'||fk_jenis_barang||'¿'||harga||'¿'||kd_tipe||'¿'||nm_tipe||'¿'||nm_merek||'¿'||fk_grade)&key=kd_request&value="+document.form1.kd_request.value
	lObjLoad.open("POST","ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}
function fGetDataReqState(){	
	if (this.readyState == 4){
		//confirm(this.responseText)
		if (this.status==200 && this.responseText!="") {
			lTemp=this.responseText.split('¿');
			//confirm(lTemp[2])
			//document.getElementById('divkd_barang').innerHTML=
			document.form1.kd_barang.value=lTemp[0]
			//document.getElementById('divnm_barang').innerHTML=
			document.form1.nm_barang.value=lTemp[1]
			document.form1.fk_jenis_barang.value=lTemp[2]
			document.form1.harga.value=lTemp[3]
			document.form1.harga_mask.value=number_format(lTemp[3])
			document.form1.fk_tipe.value=lTemp[4]
			document.getElementById('divnm_tipe').innerHTML=document.form1.nm_tipe.value=lTemp[5]
			document.getElementById('divnm_merek').innerHTML=document.form1.nm_merek.value=lTemp[6]
			document.form1.fk_grade.value=lTemp[7]
		} else {
			
		}
	}
}
function fCheckSubmit(){
	
	if (document.form1.status.value=='Save') {
		return true
	}
	else return false
}


function fLoad(){
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	} elseif($j_action){
		echo $j_action;
	}else{
		echo "document.form1.kd_request.focus();";
	}
?>
}
</script>
<body onLoad="fLoad()" bgcolor="#fafafa">
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="form1" id="form1" enctype="multipart/form-data" onSubmit="return fCheckSubmit()">
<input type="hidden" name="status">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="tipe" value="Diskon">

<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<tr><td class="judul_menu" align="center">APPROVAL BARANG</td></tr>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
                <tr bgcolor="efefef">
                    <td style="padding:0 5 0 5" width="20%">No Request</td>
                    <td style="padding:0 5 0 5" width="30%"><input name="kd_request" type="text" class='groove_text ' size="20"  onChange="fGetReqData()" value="<?=$kd_request?>">&nbsp;<img src="images/search.gif" style="border:0px" align="absmiddle" onClick="fGetReq()"></td>
                    <td style="padding:0 5 0 5" width="20%"><!--Kode Barang--></td>
                    <td style="padding:0 5 0 5" width="30%"><!--<input type="hidden" name="kd_barang" class='groove_text' value="<?=$kd_barang?>"><span id="divkd_barang"><?=$kd_barang?></span>--></td>
                </tr>
           
                <tr bgcolor="efefef">
                    <td style="padding:0 5 0 5" width="20%">Kode Barang</td>
                    <td style="padding:0 5 0 5"width="30%" ><input type="text" name="kd_barang" value="<?=$kd_barang?>"></td>
                    <td style="padding:0 5 0 5" width="20%">Nama Barang</td>
                    <td style="padding:0 5 0 5"width="30%" ><input type="text" name="nm_barang" value="<?=$nm_barang?>"></td>
                </tr>
                       
                <tr bgcolor="efefef">
                    <td style="padding:0 5 0 5" width="20%">Jenis Barang</td>
                    <td style="padding:0 5 0 5" width="30%"><?=create_list_jenis()?></td>
                    
                    <td style="padding:0 5 0 5" width="20%"><!--Tahun--></td>
                    <td style="padding:0 5 0 5" width="30%"><!--<input type="text" name="tahun_produksi" value="<?=$tahun_produksi?>">--></td>
                </tr>
                
                <tr bgcolor="efefef">
                    <td style="padding:0 5 0 5" width="20%">Kode Tipe</td>
                    <td style="padding:0 5 0 5" width="30%"><input name="fk_tipe" type="text" class='groove_text ' size="20" onChange="fGetTipeData()" value="<?=$fk_tipe?>">&nbsp;<img src="images/search.gif" style="border:0px" align="absmiddle" onClick="fGetTipe()"></td>
                    
                    <td style="padding:0 5 0 5" width="20%">Tipe</td>
                    <td style="padding:0 5 0 5" width="30%"><span id="divnm_tipe"><?=convert_html($nm_tipe)?></span><input type="hidden" name="nm_tipe" value="<?=$nm_tipe?>"></td>
                </tr>
                <tr bgcolor="efefef">
                    <td style="padding:0 5 0 5" width="20%">Merek</td>
                    <td style="padding:0 5 0 5" width="30%"><span id="divnm_merek"><?=convert_html($nm_merek)?></span><input type="hidden" name="nm_merek" value="<?=$nm_merek?>"></td>
                    
                    <td style="padding:0 5 0 5" width="20%"><!--Harga--></td>
                    <td style="padding:0 5 0 5" width="30%"><!--<? create_input_number($harga,"harga","harga","",'','',2)?>--></td>
                </tr>            
<!--                <tr bgcolor="efefef">
                    <td style="padding:0 5 0 5" width="20%">Grade</td>
                    <td style="padding:0 5 0 5" width="30%"><?=create_list_grade()?></td>
                    
                    <td style="padding:0 5 0 5" width="20%"></td>
                    <td style="padding:0 5 0 5" width="30%"></td>
                </tr>                
-->                
              </table>

<!-- end content begin -->
	  	</td>
    </tr>
</table>

<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" align="center">
	<tr height="20">
    	<td height="25" align="center" bgcolor="#D0E4FF">
            <input class="groove_button" type='button' name="btnGenerate" value='Approve' onClick="fDownload()">

		</td>
	</tr>
</table>
</form>
</body>
</html>
<?
//}

function get_data(){
	global $id_edit,$strmsg,$j_action,$no_cif,$fk_sbg,$nm_customer;

}

function create_list_jenis(){
    global $fk_jenis_barang;
    $l_list_obj = new select("select * from tbljenis_barang where kd_jenis_barang!=0","nm_jenis_barang","kd_jenis_barang","fk_jenis_barang");
    $l_list_obj->add_item("-- Pilih ---",'',0);
	$l_list_obj->set_default_value($fk_jenis_barang);
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:12px;' onKeyUp='fNextFocus(event,document.getElementById(\"login\"))'");
}

function create_list_grade(){
    global $fk_grade;
	
    $l_list_obj = new select("select * from tblgrade_brg","kd_grade","kd_grade","fk_grade");
    $l_list_obj->add_item("-- Pilih ---",'',0);
	$l_list_obj->set_default_value($fk_grade);
	
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:12px;' onKeyUp='fNextFocus(event,document.getElementById(\"login\"))'");
}

function save_data(){
	global $strmsg, $j_action,$kd_request,$kd_barang,$nm_barang,$fk_jenis_barang,$tahun_produksi,$fk_tipe,$harga,$fk_grade;
	$l_success=1;
	pg_query("BEGIN");

	if(!pg_query("insert into data_gadai.tblrequest_barang_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from data_gadai.tblrequest_barang where kd_request='".$kd_request."'")) $l_success=0;
		
	if(!pg_query("
	update data_gadai.tblrequest_barang set 
	status_data='Approve',
	kd_barang='".$kd_barang."',
	nm_barang='".$nm_barang."',
	fk_jenis_barang='".$fk_jenis_barang."',
	fk_tipe='".$fk_tipe."',
	harga='".$harga."',
	fk_grade='".$fk_grade."'
	where kd_request='".$kd_request."'
	")) $l_success=0;


	if(!pg_query("insert into data_gadai.tblrequest_barang_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from data_gadai.tblrequest_barang where kd_request='".$kd_request."'")) $l_success=0;	
	
	
	$lrow=pg_fetch_array(pg_query("select * from data_gadai.tblrequest_barang where kd_request='".$kd_request."'"));
	
	if(!pg_query("
		insert into tblbarang (
			kd_barang,nm_barang,fk_jenis_barang,
			fk_tipe,tahun_produksi,fk_grade
		)values(
			'".convert_sql($lrow["kd_barang"])."','".convert_sql($lrow["nm_barang"])."',
			'".convert_sql($lrow["fk_jenis_barang"])."',
			'".convert_sql($lrow["fk_tipe"])."',
			'".convert_sql($lrow["tahun_produksi"])."','".convert_sql($lrow["fk_grade"])."'
		)
	"));
	
	
	$lrow["rate"]=100;
	
	$lrs_wilayah=pg_query("select * from tblwilayah where wilayah_active='t'");
	while($lrow_wilayah=pg_fetch_array($lrs_wilayah)){
		if(!pg_num_rows(pg_query("select * from tblbarang_detail where fk_barang  ='".convert_sql($lrow["kd_barang"])."' and fk_wilayah  ='".convert_sql($lrow_wilayah["kd_wilayah"])."'"))){
			if(!pg_query("insert into tblbarang_detail (fk_barang,harga,fk_wilayah,rate) values ('".$lrow["kd_barang"]."','".$lrow["harga"]."','".$lrow_wilayah["kd_wilayah"]."','".$lrow["rate"]."')"))$l_success=0;
		}
	}					
	
		
	//$l_success=0;
	if ($l_success==1){
		$strmsg="Data Berhasil ter-Approve.<br>";
		$j_action="lInputClose=getObjInputClose();lInputClose.close();";
		pg_query("COMMIT");
	}else{
		$strmsg="Error :<br>Data Gagal Approve.<br>";
		pg_query("ROLLBACK");
	}


}

function cek_error(){
	global $strmsg,$j_action,$kd_request,$kd_barang,$nm_barang,$fk_jenis_barang,$tahun_produksi,$fk_tipe,$harga;
	if($kd_request == '') {
		$strmsg.="No Request Harus Diisi.<br>";
	}else if(pg_num_rows(pg_query("select * from tblbarang where kd_barang='".$kd_barang."' "))){
		$strmsg.="Kode Barang sudah ada.<br>";
	}		
	if($strmsg)$strmsg="Error :<br>".$strmsg;
}

?>