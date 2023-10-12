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

$id_edit = $_REQUEST['id_edit'];
$id_menu=trim($_REQUEST["id_menu"]);

$kd_produk = $_REQUEST['kd_produk'];
get_data_menu($id_menu);

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

function fSave(){
	document.form1.status.value='Save';
	document.form1.submit();
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")){
		lInputClose=getObjInputClose();lInputClose.close()
	}
}


function fGetProduk(){
	fGetNC(false,'20170800000001','kd_produk','Ganti Provinsi',document.form1.kd_produk,document.form1.kd_produk,'','','20170800000001')
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
<input type="hidden" name="id_menu"  value="<?=$id_menu?>">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<tr><td class="judul_menu" align="center"><?=strtoupper($nm_menu)?></td></tr>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
                <tr bgcolor="efefef">
                    <td style="padding:0 5 0 5" width="20%" class="fontColor">Kode Produk</td>
                    <td style="padding:0 5 0 5"width="30%" ><input type="text" name="kd_produk" value="<?=$kd_produk?>"></td>
                    <td style="padding:0 5 0 5" width="20%">Kode Produk Asal</td>
                    <td style="padding:0 5 0 5"width="30%" ><input type="hidden" name="id_edit" value="<?=$id_edit?>"><?=$id_edit?></td>
                    
                </tr>
            </table>

<!-- end content begin -->
	  	</td>
    </tr>
</table>

<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" align="center">
	<tr height="20">
    	<td height="25" align="center" bgcolor="#D0E4FF">
            <input class="groove_button" type='button' name="btngenerate" value='Copy' onClick="fSave()">

		</td>
	</tr>
</table>
</form>
</body>
</html>
<?
//}
function save_data(){
	global $id_edit,$strmsg,$kd_produk,$j_action;
	$l_success=1;
	
	$query_table=pg_query("
	   SELECT *
	   FROM information_schema.columns
	   WHERE table_schema = 'public' AND table_name   = 'tblproduk' and column_name !='kd_produk'
	   order by ordinal_position
		 ;
	");	
	while($lrow_table=pg_fetch_array($query_table)){
		$column_names.=",".$lrow_table["column_name"];
	}
	//echo $column_names;
	
	
	pg_query("BEGIN");
		
	if(!pg_query("insert into tblproduk select '".$kd_produk."' ".$column_names." from tblproduk where kd_produk='".$id_edit."'")) $l_success=0;
	
	//showquery("insert into tblproduk select '".$kd_produk."' ".$column_names." from tblproduk where kd_produk='".$id_edit."'");
	
	
	if(!pg_query("insert into tblproduk_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from tblproduk where kd_produk='".$kd_produk."'")) $l_success=0;
	$l_id_log_ia=get_last_id("tblproduk_log","pk_id_log");

/*	$lrs=(pg_query("select * from tblproduk_detail where fk_produk='".$id_edit."'"));
	while($lrow=pg_fetch_array($lrs)){
		if(!pg_query("insert into tblproduk_detail (fk_cabang,fk_produk)values ( '".$lrow["fk_cabang"]."' , '".$kd_produk."' )")) 
		$l_success=0;
	}
	
	if(!pg_query("insert into tblproduk_detail_log select *,'".$l_id_log_ia."' from tblproduk_detail where fk_produk='".$kd_produk."'")) $l_success=0;
*/


/*	$lrs=(pg_query("select * from tblproduk_detail_area where fk_produk='".$id_edit."'"));	
	while($lrow=pg_fetch_array($lrs)){
		if(!pg_query("insert into tblproduk_detail_area (fk_area,fk_produk) values ('".$lrow["fk_area"]."' , '".$kd_produk."' )")) $l_success=0;	
	}
	if(!pg_query("insert into tblproduk_detail_area_log select *,'".$l_id_log_ia."' from tblproduk_detail_area  where fk_produk='".$kd_produk."'")) $l_success=0;
*/	
	
	$lrs=(pg_query("select * from tblproduk_detail_biaya_admin where fk_produk='".$id_edit."'"));		
	while($lrow=pg_fetch_array($lrs)){
  		if(!pg_query("insert into tblproduk_detail_biaya_admin (fk_produk,dari,ke,nilai,kategori)values ('".$kd_produk."','".$lrow["dari"]."' ,'".$lrow["ke"]."' ,'".$lrow["nilai"]."','".$lrow["kategori"]."')")) $l_success=0;
	}
	if(!pg_query("insert into tblproduk_detail_biaya_admin_log select *,'".$l_id_log_ia."' from tblproduk_detail_biaya_admin  where fk_produk='".$kd_produk."'")) $l_success=0;
	

/*	$lrs=(pg_query("select * from tblproduk_detail_grade where fk_produk='".$id_edit."'"));
	while($lrow=pg_fetch_array($lrs)){
		if(!pg_query("insert into tblproduk_detail_grade (fk_produk,fk_grade)values ('".$kd_produk."','".$lrow["fk_grade"]."' )")) $l_success=0;
	}
	if(!pg_query("insert into tblproduk_detail_grade_log select *,'".$l_id_log_ia."' from tblproduk_detail_grade where fk_produk='".$kd_produk."'")) $l_success=0;
*/	

/*	$lrs=(pg_query("select * from tblproduk_detail_masa_tenggang where fk_produk='".$id_edit."'"));	
	while($lrow=pg_fetch_array($lrs)){
		if(!pg_query("insert into tblproduk_detail_masa_tenggang (fk_produk,dari,ke,persen)values ('".$kd_produk."','".$lrow["dari"]."','".$lrow["ke"]."','".$lrow["persen"]."')")) $l_success=0;
	}
	if(!pg_query("insert into tblproduk_detail_masa_tenggang_log select *,'".$l_id_log_ia."' from tblproduk_detail_masa_tenggang  where fk_produk='".$kd_produk."'")) $l_success=0;
	
	$lrs=(pg_query("select * from tblproduk_detail_masa_tunggu where fk_produk='".$id_edit."'"));	
	while($lrow=pg_fetch_array($lrs)){	
		if(!pg_query("insert into tblproduk_detail_masa_tunggu (fk_produk,dari,ke,persen)values ('".$kd_produk."','".$lrow["dari"]."','".$lrow["ke"]."','".$lrow["persen"]."')")) $l_success=0;
	}
	if(!pg_query("insert into tblproduk_detail_masa_tunggu_log select *,'".$l_id_log_ia."' from tblproduk_detail_masa_tunggu  where fk_produk='".$kd_produk."'")) $l_success=0;
*/	
	$lrs=(pg_query("select * from tblproduk_detail_tenor where fk_produk='".$id_edit."'"));	
	while($lrow=pg_fetch_array($lrs)){	
		if(!pg_query("insert into tblproduk_detail_tenor (fk_produk,tenor,minimal_dipercepat)values ('".$kd_produk."','".$lrow["tenor"]."','".$lrow["minimal_dipercepat"]."')")) $l_success=0;
	}
	if(!pg_query("insert into tblproduk_detail_tenor_log select *,'".$l_id_log_ia."' from tblproduk_detail_tenor  where fk_produk='".$kd_produk."'")) $l_success=0;
	
	/*
	$lrs=(pg_query("select * from tblproduk_detail_wilayah where fk_produk='".$id_edit."'"));	
	while($lrow=pg_fetch_array($lrs)){	
		if(!pg_query("insert into tblproduk_detail_wilayah (fk_produk,tenor)values ('".$kd_produk."','".$lrow["wilayah"]."')")) $l_success=0;
	}
	if(!pg_query("insert into tblproduk_detail_wilayah_log select *,'".$l_id_log_ia."' from tblproduk_detail_wilayah  where fk_produk='".$kd_produk."'")) $l_success=0;
	*/
	
	
	$lrs=(pg_query("select * from tblproduk_detail_asuransi where fk_produk='".$id_edit."'"));	
	while($lrow=pg_fetch_array($lrs)){	
		if(!pg_query("insert into tblproduk_detail_asuransi (fk_produk,jenis_asuransi,fk_jenis_barang,dari,ke,persentase)values ('".$kd_produk."','".$lrow["jenis_asuransi"]."','".$lrow["fk_jenis_barang"]."','".$lrow["dari"]."','".$lrow["ke"]."','".$lrow["persentase"]."')")) $l_success=0;
	}
	if(!pg_query("insert into tblproduk_detail_asuransi_log select *,'".$l_id_log_ia."' from tblproduk_detail_asuransi  where fk_produk='".$kd_produk."'")) $l_success=0;
	
	$lrs=(pg_query("select * from tblproduk_detail_asuransi_jiwa where fk_produk='".$id_edit."'"));	
	while($lrow=pg_fetch_array($lrs)){	
		if(!pg_query("insert into tblproduk_detail_asuransi_jiwa (fk_produk,tahun_ke,persentase)values ('".$kd_produk."','".$lrow["tahun_ke"]."','".$lrow["persentase"]."')")) $l_success=0;
	}
	if(!pg_query("insert into tblproduk_detail_asuransi_jiwa_log select *,'".$l_id_log_ia."' from tblproduk_detail_asuransi_jiwa where fk_produk='".$kd_produk."'")) $l_success=0;
	
	$lrs=(pg_query("select * from tblproduk_detail_nilai_pertangungan where fk_produk='".$id_edit."'"));	
	while($lrow=pg_fetch_array($lrs)){	
		if(!pg_query("insert into tblproduk_detail_nilai_pertangungan (fk_produk,tahun_ke,persentase,kategori)values ('".$kd_produk."','".$lrow["tahun_ke"]."','".$lrow["persentase"]."','".$lrow["kategori"]."')")) $l_success=0;
	}
	if(!pg_query("insert into tblproduk_detail_nilai_pertangungan_log select *,'".$l_id_log_ia."' from tblproduk_detail_nilai_pertangungan where fk_produk='".$kd_produk."'")) $l_success=0;
	
	//$l_success=0;
	if ($l_success==1){
		$strmsg="Data saved.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		pg_query("COMMIT");	
	}else{
		$strmsg="Error :<br>Save failed.<br>";
		pg_query("ROLLBACK");
	}
	
	
}
function cek_error(){
	global $strmsg,$j_action,$periode_awal,$periode_akhir2,$kd_produk;
	if($kd_produk==""){
			$strmsg.="Kode Produk Kosong.<br>";
			if(!$j_action) $j_action="document.form1.kd_produk.focus()";
	} 
	
	else if (pg_num_rows(pg_query("select * from tblproduk where upper(kd_produk)='". strtoupper($kd_produk)."'"))){
			$strmsg.="Kode Sudah Terdaftar.<br>"; 
			if(!$j_action) $j_action="document.form1.kd_produk.focus()";
			//showquery("select * from tblproduk where upper(kd_produk)='". strtoupper($kd_produk)."'");
	}
	
	
	
	if($strmsg)$strmsg="Error :<br>".$strmsg;
}

?>