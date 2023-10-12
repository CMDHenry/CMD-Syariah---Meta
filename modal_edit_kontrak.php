<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/accounting_utility.inc.php';
require 'requires/stok_utility.inc.php';
require 'requires/numeric.inc.php';
require 'requires/timestamp.inc.php';
require 'classes/select.class.php';
$j_action=$_REQUEST["hidden_focus"];
$id_edit=$_REQUEST["id_edit"];

$strisi=trim($_REQUEST["strisi"]);
$tgl_cair = convert_date_english(trim($_REQUEST["tgl_cair"]));
$nm_customer = trim($_REQUEST["nm_customer"]);
$overdue=trim($_REQUEST["overdue"]);
$tgl_wo = convert_date_english(trim($_REQUEST["tgl_wo"]));
$nm_tipe=trim($_REQUEST["nm_tipe"]);
$fk_cif=trim($_REQUEST["fk_cif"]);
$fk_fatg=trim($_REQUEST["fk_fatg"]);
$fk_fatg_old=get_rec("data_gadai.tblproduk_cicilan","fk_fatg","no_sbg='".$id_edit."'");

$fk_metode=trim($_REQUEST["fk_metode"]);
$is_ganti_kontrak=trim($_REQUEST["is_ganti_kontrak"]);
if($is_ganti_kontrak==""){
	$is_ganti_kontrak="f";
}

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

function fGetReferensiTaksir(){
	fGetNC(false,'20170900000052','fk_fatg','Ganti Kota',document.form1.fk_fatg,document.form1.fk_fatg,'','','20171200000083')
	if (document.form1.fk_fatg.value !="")fGetCIFData()
}

function fGetCIF(){
	fGetNC(false,'20170800000001','fk_cif','Ganti Lokasi',document.form1.fk_cif,document.form1.fk_cif)
}

function fGetCIFData(){
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataCIFState
	lSentText="table=(select * from data_gadai.tbltaksir_umum left join tblcustomer on fk_cif=no_cif)as tbl&field=(nm_customer||'¿'||fk_cif)&key=no_fatg&value="+document.form1.fk_fatg.value
	lObjLoad.open("POST","ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}
function fGetDataCIFState(){	
	if (this.readyState == 4){
		//confirm(this.responseText)
		if (this.status==200 && this.responseText!="") {
			lTemp=this.responseText.split('¿');
			//confirm(lTemp[2])
			document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value=lTemp[0]
			document.getElementById('divfk_cif').innerHTML=document.form1.fk_cif.value=lTemp[1]
		} else {
			document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value=""
			document.getElementById('divfk_cif').innerHTML=document.form1.fk_cif.value=""
		}
	}
}
function fSave(){
	if (confirm("Apakah anda yakin ingin menyimpan data? Data tidak bisa dikembalikan lagi")) {
		document.form1.status.value='Save';
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
<form action="modal_edit_kontrak.php" method="post" name="form1">
<input type="hidden" name="status">
<input type="hidden" name="strisi" value="<?=$strisi?>">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="fk_fatg_old" value="<?=$fk_fatg_old?>">
<input type="hidden" name="hidden_focus">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<td class="judul_menu" align="center">EDIT KONTRAK</td>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border" id="tdContent">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%">No. Kontrak</td>
					<td style="padding:0 5 0 5" width="30%"><input type="hidden" value="<?=$id_edit?>" name="id_edit"><?=$id_edit?></td>
					<td style="padding:0 5 0 5" width="20%">Tanggal Kontrak</td>
					<td style="padding:0 5 0 5" width="30%"><input type="hidden" value="<?=convert_date_indonesia($tgl_cair)?>" name="tgl_cair"><?=convert_date_indonesia($tgl_cair)?></td>
				</tr>
                
				<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" width="20%">Tipe</td>
					<td style="padding:0 5 0 5" width="30%"><input type="hidden" value="<?=$nm_tipe?>" name="nm_tipe"><?=$nm_tipe?></td>
					<td style="padding:0 5 0 5" width="20%"><!--Metode Perhitungan Jaminan--></td>
					<td style="padding:0 5 0 5" width="30%"><!--<?=create_list_metode();?>--></td>
                    
				</tr>
                
              	<tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td style="padding:0 5 0 5" >Tanggal WO (Hapus Buku)</td>
					<td style="padding:0 5 0 5" class="style1"><input name="tgl_wo" type="text"  class='groove_text' id="tgl_wo" size="12" value="<?=convert_date_indonesia($tgl_wo)?>" maxlength="10" onKeyUp="fNextFocus(event,document.form1.tgl_wo)">
					&nbsp;<img src="images/btn_extend.gif" width="13" height="12" onClick="fPopCalendar(document.form1.tgl_wo,function(){document.form1.tgl_wo.focus()})"></td>
                    <td style="padding:0 5 0 5" width="20%">Overdue</td>
					<td style="padding:0 5 0 5" width="30%"><input type="hidden" value="<?=$overdue?>" name="overdue"><?=$overdue?></td>
             	</tr>
                
                <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
                	 <td align="center" colspan="4">OVER KREDIT ( GANTI CIF ) ( KONTRAK SAMA )</td>
                </tr>
<!--                <tr bgcolor="efefef">
                    <td style="padding:0 5 0 5" width="20%">Ganti Kontrak?</td>
                    <td style="padding:0 5 0 5" width="30%"><input type="checkbox" name="is_ganti_kontrak" value="t" <?=(($is_ganti_kontrak=="t")?"checked":"")?> >
                    </td>
                    <td style="padding:0 5 0 5" width="20%"></td>
                    <td style="padding:0 5 0 5" width="30%"></td>
                </tr>
-->                <tr bgcolor="efefef">
                    <td style="padding:0 5 0 5" width="20%">No Permohonan</td>
                    <td style="padding:0 5 0 5" width="30%">
                    <input name="fk_fatg" type="text" class='groove_text' onChange="fGetCIFData()" value="<?=$fk_fatg?>">&nbsp;<img src="images/search.gif" style="border:0px" align="absmiddle" onClick="fGetReferensiTaksir()">
                    </td>
                    <td style="padding:0 5 0 5" width="20%"></td>
                    <td style="padding:0 5 0 5" width="30%"></td>
                </tr>
                <tr bgcolor="efefef">
                    <td style="padding:0 5 0 5" width="20%">No CIF</td>
                    <td style="padding:0 5 0 5" width="30%">
                    <input name="fk_cif" type="hidden" class='groove_text' value="<?=$fk_cif?>"><span id="divfk_cif"><?=$fk_cif?></span>
                    </td>
                    <td style="padding:0 5 0 5" width="20%">Nama Customer</td>
                    <td style="padding:0 5 0 5" width="30%"><input type="hidden" value="<?=$nm_customer?>" name="nm_customer"><span id="divnm_customer"><?=$nm_customer?></span></td>
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
function create_list_metode(){
    global $fk_metode;
    $l_list_obj = new select("select * from (select nm_metode||'-'||rate||'%' as nama_metode,* from tblmetode_perhitungan_jaminan)as tbl","nama_metode","kd_metode","fk_metode");	
	$l_list_obj->set_default_value($fk_metode);
	$l_list_obj->add_item("-- Pilih ---",'',0);		
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:12px;' onKeyUp='fNextFocus(event,document.getElementById(\"login\"))'","form1","");
}


function cek_error(){
	global $strmsg,$j_action,$strisi,$fk_cif,$fk_fatg,$fk_fatg_old,$id_edit,$description,$fk_cabang;
	
	if($fk_cif == ""){
		$strmsg.="CIF Kosong.<br>";
	}
	
	if($fk_fatg!=$fk_fatg_old){
		if(pg_num_rows(pg_query("select * from viewtaksir left join viewkontrak on fk_fatg=no_fatg where no_sbg is not null and no_fatg='".$fk_fatg."' and status_data!='Batal'"))){
			//showquery("select * from viewtaksir left join viewkontrak on fk_fatg=no_fatg where no_sbg is not null and no_fatg!='".$fk_fatg."'");
			$strmsg.="No Permohonan ".$fk_fatg.", sudah untuk kontrak lain.<br>";
			$j_action="document.form1.no_sbg.focus()";
		}
		
		$lrow=pg_fetch_array(pg_query("select * from data_gadai.tbltaksir_umum where no_fatg='".$fk_fatg."'"));						
		$lrow_old=pg_fetch_array(pg_query("select * from data_gadai.tbltaksir_umum where no_fatg='".$fk_fatg_old."'"));
		
		if($lrow["no_mesin"]!=$lrow_old["no_mesin"]){
			$strmsg.="No Mesin Permohonan Pengganti harus sama dengan No Mesin di kontrak ini .<br>";
		}
		$jumlah_angs_lunas=pg_num_rows(pg_query("select * from data_fa.tblangsuran where fk_sbg='".$id_edit."' and tgl_bayar is not null and angsuran_ke>0"));
		
		if($jumlah_angs_lunas<12){
			$strmsg.="Ganti debitur minimal lunas 12 angsuran.<br>";
		}

		if(pg_num_rows(pg_query("select * from data_fa.tbldenda where fk_sbg='".$id_edit."' and saldo_denda>0"))){
			$strmsg.="Ganti debitur todak boleh ada sisa denda.<br>";
		}
	}
	
	
	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}

function save_data(){
	global $strmsg,$j_action,$id_edit,$strisi,$tgl_wo,$fk_cif,$fk_fatg_old,$fk_fatg,$fk_metode;
	$l_success=1;
	pg_query("BEGIN");
	
	if($tgl_wo){
		if(!pg_query("insert into data_gadai.tblproduk_cicilan_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from data_gadai.tblproduk_cicilan where no_sbg='".$id_edit."'")) $l_success=0;
		if(!pg_query("update data_gadai.tblproduk_cicilan set tgl_wo=".(($tgl_wo=="")?"null":"'".convert_sql($tgl_wo)."'")." where no_sbg='".$id_edit."'")) $l_success=0;
		if(!pg_query("insert into data_gadai.tblproduk_cicilan_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from data_gadai.tblproduk_cicilan where no_sbg='".$id_edit."'")) $l_success=0;
	}
	
	$lrow=pg_fetch_array(pg_query("select * from tblinventory where fk_sbg='".$id_edit."'"));
	
	$fk_cif_old=$lrow["fk_cif"];
	$fk_cif_old1=$lrow["fk_cif_old1"];
	$fk_cif_old2=$lrow["fk_cif_old2"];
	
	if($fk_fatg_old!=$fk_fatg){
		$lrow_old=pg_fetch_array(pg_query("select * from data_gadai.tbltaksir_umum where no_fatg='".$fk_fatg_old."'"));
		
		if(!$fk_cif_old1){
			$lupdate=",fk_cif_old1='".$fk_cif_old."',tgl_ganti_cif1='".today_db." ".date("H:i:s")."'";
		}else if(!$fk_cif_old2){
			$lupdate=",fk_cif_old2='".$fk_cif_old."',tgl_ganti_cif2='".today_db." ".date("H:i:s")."'";
		}else{
			$strmsg = "CIF sudah diganti 2x<br>";
			$l_success=0;	
		}
		
		//update cif ke old
		$where1="fk_sbg='".$id_edit."'";
		if(!pg_query(insert_log("tblinventory",$where1,'UB')))$l_success=0;		
		if(!pg_query("update tblinventory set fk_cif='".$fk_cif."'  $lupdate where fk_sbg='".$id_edit."'")) $l_success=0;
		//showquery("update tblinventory set fk_cif='".$fk_cif."'  $lupdate where fk_sbg='".$id_edit."'");
		if(!pg_query(insert_log("tblinventory",$where1,'UA')))$l_success=0;		
		
		//update fatg baru
		$where1="no_fatg='".$fk_fatg."'";
		if(!pg_query(insert_log("data_gadai.tbltaksir_umum",$where1,'UB')))$l_success=0;		
/*		showquery("update data_gadai.tbltaksir_umum set no_sbg_ar='".$id_edit."'
		,tgl_terima_bpkb=".(($lrow_old["tgl_terima_bpkb"]=="")?"NULL":"'".$lrow_old["tgl_terima_bpkb"]."'")."
		,no_polisi=".(($lrow_old["no_polisi"]=="")?"NULL":"'".$lrow_old["no_polisi"]."'")."
		,no_bpkb=".(($lrow_old["no_bpkb"]=="")?"NULL":"'".$lrow_old["no_bpkb"]."'")."
		,no_faktur=".(($lrow_old["no_faktur"]=="")?"NULL":"'".$lrow_old["no_faktur"]."'")."
		,tgl_faktur=".(($lrow_old["tgl_faktur"]=="")?"NULL":"'".$lrow_old["tgl_faktur"]."'")."
		,posisi_bpkb=".(($lrow_old["posisi_bpkb"]=="")?"NULL":"'".$lrow_old["posisi_bpkb"]."'")."
		where no_fatg='".$fk_fatg."'");
*/		if(!pg_query("update data_gadai.tbltaksir_umum set no_sbg_ar='".$id_edit."'
		,tgl_terima_bpkb=".(($lrow_old["tgl_terima_bpkb"]=="")?"NULL":"'".$lrow_old["tgl_terima_bpkb"]."'")."
		,no_polisi=".(($lrow_old["no_polisi"]=="")?"NULL":"'".$lrow_old["no_polisi"]."'")."
		,no_bpkb=".(($lrow_old["no_bpkb"]=="")?"NULL":"'".$lrow_old["no_bpkb"]."'")."
		,no_faktur=".(($lrow_old["no_faktur"]=="")?"NULL":"'".$lrow_old["no_faktur"]."'")."
		,tgl_faktur=".(($lrow_old["tgl_faktur"]=="")?"NULL":"'".$lrow_old["tgl_faktur"]."'")."
		,posisi_bpkb=".(($lrow_old["posisi_bpkb"]=="")?"NULL":"'".$lrow_old["posisi_bpkb"]."'")."
		where no_fatg='".$fk_fatg."'")) $l_success=0;
		if(!pg_query(insert_log("data_gadai.tbltaksir_umum",$where1,'UA')))$l_success=0;		
		
		//ganti fatg lama 
		$where1="no_fatg='".$fk_fatg_old."'";
		if(!pg_query(insert_log("data_gadai.tbltaksir_umum",$where1,'UB')))$l_success=0;		
		if(!pg_query("update data_gadai.tbltaksir_umum set no_sbg_ar =null,no_sbg_lama='".$id_edit."'
		where no_fatg='".$fk_fatg_old."'")) $l_success=0;
		//showquery("update data_gadai.tbltaksir_umum set no_sbg_ar =null,no_sbg_lama='".$id_edit."' where no_fatg='".$fk_fatg_old."'");
		if(!pg_query(insert_log("data_gadai.tbltaksir_umum",$where1,'UA')))$l_success=0;		
		
		//update ke produk
		$where1="no_sbg='".$id_edit."'";
		if(!pg_query(insert_log("data_gadai.tblproduk_cicilan",$where1,'UB')))$l_success=0;		
		//showquery("update data_gadai.tblproduk_cicilan set fk_fatg='".$fk_fatg."' where no_sbg='".$id_edit."'");
		if(!pg_query("update data_gadai.tblproduk_cicilan set fk_fatg='".$fk_fatg."' where no_sbg='".$id_edit."'")) $l_success=0;
		if(!pg_query(insert_log("data_gadai.tblproduk_cicilan",$where1,'UA')))$l_success=0;		
	}
	//copy_sbg($id_edit,$fk_fatg);
	//$l_success = 0;
	if($l_success==1) {
		$strmsg = "Data Saved.<br>";
		$j_action="lInputClose=getObjInputClose();lInputClose.close();";
		pg_query("COMMIT");
	} else {
		$strmsg.= "Error :<br> Save Failed.<br>";
		pg_query("ROLLBACK");
	}
}

function copy_sbg($no_sbg,$no_fatg){
	
	$lrow=pg_fetch_array(pg_query("select * from tblinventory where fk_sbg='".$no_sbg."'"));
	$fk_cabang=$lrow["fk_cabang"];
	$fk_produk=$lrow["fk_produk"];	
	$fk_cif=$lrow["fk_cif"];	
	
	$lrow=pg_fetch_array(pg_query("select * from data_gadai.tblproduk_cicilan where no_sbg='".$no_sbg."'"));
	$tgl_pengajuan=$lrow["tgl_pengajuan"];
	$tgl_jatuh_tempo=$lrow["tgl_jatuh_tempo"];
	
	if(!pg_query(storing($no_sbg,NULL,'Lunas','Ganti CIF')))$l_success=0;//insert inventory baru
	
	$lrow=pg_fetch_array(pg_query("select nextserial_kontrak('SBG', '".$fk_cabang."','".$fk_produk."')"));
	$no_sbg_new=$lrow["nextserial_kontrak"];
	
	if(!pg_query(storing($no_sbg_new,'-','Terima','Ganti CIF',$tgl_pengajuan,'t',$fk_cabang,$fk_cif,$fk_produk,$tgl_jatuh_tempo)))$l_success=0;		
	
	if(!pg_query("update data_gadai.tbltaksir_umum set status_fatg='Ganti CIF',no_sbg_lama='".$no_sbg."' where no_fatg='".$no_fatg."'")) $l_success=0;	
	if(!pg_query("insert into data_gadai.tbltaksir_umum_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from data_gadai.tbltaksir_umum where no_fatg='".$no_fatg."'")) $l_success=0;
	
	//update cicilan
	$query_table=pg_query("
	   SELECT *
	   FROM information_schema.columns
	   WHERE table_schema = 'data_gadai' AND table_name  = 'tblproduk_cicilan' and column_name !='no_sbg'
	   order by ordinal_position
	");	
	$column_names='';
	while($lrow_table=pg_fetch_array($query_table)){
		$column_names.=",".$lrow_table["column_name"];
	}	

	if(!pg_query("insert into data_gadai.tblproduk_cicilan select '".$no_sbg_new."' ".$column_names." from data_gadai.tblproduk_cicilan where no_sbg='".$no_sbg."'")) $l_success=0;	
	//showquery("insert into ".$tbl." select '".$no_sbg_new."' ".$column_names." from ".$tbl." where no_sbg='".$fk_sbg."'");
	if(!pg_query("update data_gadai.tblproduk_cicilan set fk_fatg='".$no_fatg_new."' where no_sbg='".$no_sbg_new."'")) $l_success=0;		
	if(!pg_query("insert into data_gadai.tblproduk_cicilan_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from data_gadai.tblproduk_cicilan where no_sbg='".$no_sbg_new."'")) $l_success=0;	
	
	$query_table=pg_query("
	   SELECT *
	   FROM information_schema.columns
	   WHERE table_schema = 'data_fa' AND table_name  = 'tblangsuran' and column_name not in('fk_sbg','pk_id')
	   order by ordinal_position
	");	
	$column_names='';
	while($lrow_table=pg_fetch_array($query_table)){
		$column_names.=",".$lrow_table["column_name"];
	}	
		
	$no_kwitansi='';
	if(!pg_query("insert into data_fa.tblangsuran select nextserial('tblangsuran.pk_id'::text),'".$no_sbg_new."' ".$column_names." from data_fa.tblangsuran where fk_sbg='".$no_sbg."'")) $l_success=0;
		
	if(!pg_query("insert into data_fa.tblangsuran_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from data_fa.tblangsuran where fk_sbg='".$no_sbg_new."'")) $l_success=0;	
	
	if(!pg_query(insert_history_sbg($no_sbg_new,0,'0','AR Tambah DP',$no_kwitansi))) $l_success=0;		
	showquery(insert_history_sbg($no_sbg_new,0,'0','AR Tambah DP',$no_kwitansi));
}


function get_data(){
	global $id_edit,$strmsg,$j_action,$strisi,$no_sbg,$tgl_cair,$overdue,$nm_customer,$tgl_wo,$fk_cif,$fk_fatg,$fk_metode,$nm_tipe;

	$lrow=pg_fetch_array(pg_query("
	select no_sbg,tgl_cair,nm_customer,date_part('day',(now()-tgl_jt))::numeric as overdue,tgl_wo,fk_cif,fk_fatg,fk_metode ,nm_tipe from data_gadai.tblproduk_cicilan
	inner join(
		select tgl_jt,nm_customer,fk_sbg as fk_sbg_inventory,fk_cif from tblinventory
		left join tblcustomer on fk_cif=no_cif
	) as tblinventory on fk_sbg_inventory=no_sbg
	left join (
		select no_mesin,no_rangka, kategori,nm_tipe,fk_jenis_barang,no_sbg_ar1 from viewkendaraan
	)as view on no_sbg=no_sbg_ar1
	 where no_sbg='".$id_edit."'"));
	//echo $lrow["nm_tipe"];
	$no_sbg=$lrow["no_sbg"];
	$tgl_cair=(($lrow["tgl_cair"]!="")?date('m/d/Y',strtotime($lrow["tgl_cair"])):"");
	$overdue=$lrow["overdue"];
	if($overdue<0)$overdue=0;
	$nm_customer=$lrow["nm_customer"];
	$tgl_wo=(($lrow["tgl_wo"]!="")?date('m/d/Y',strtotime($lrow["tgl_wo"])):"");
	$fk_cif=$lrow["fk_cif"];
	$fk_fatg=$lrow["fk_fatg"];
	$fk_metode=$lrow["fk_metode"];
	$nm_tipe=$lrow["nm_tipe"];
}


/*	
	$lrow_kontrak_old=pg_fetch_array(pg_query("select * from data_gadai.tblproduk_cicilan where no_sbg='".$id_edit."'"));
	$tgl_wo_old=$lrow_kontrak_old['tgl_wo'];
	
	if($tgl_wo && !$tgl_wo_old){		
		$p_arr=array(
		'tgl_data'=>$tgl_wo,
		'fk_sbg'=>$id_edit,
		'referensi'=>NULL,
		'ang_ke'=>'0',
		'transaksi'=>'WO',
		'nilai_bayar'=>0,
		);
		//showquery(insert_history($p_arr));	
		if(!pg_query(insert_history($p_arr))) $l_success=0;					
	}
	
	if($tgl_wo_old && !$tgl_wo){
		$p_arr=array(
		'tgl_data'=>today_db,
		'fk_sbg'=>$id_edit,
		'referensi'=>NULL,
		'ang_ke'=>'0',
		'transaksi'=>'Batal WO',
		'nilai_bayar'=>0,
		);
		//showquery(insert_history($p_arr));	
		if(!pg_query(insert_history($p_arr))) $l_success=0;					
	}
*/
	

?>