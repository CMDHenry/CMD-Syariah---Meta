<?php
require 'requires/config.inc.php';
$source=trim($_REQUEST["source"]);

if($source!='email'){
	require 'requires/authorization.inc.php';
}
require 'requires/general.inc.php';
require 'requires/numeric.inc.php';
require 'requires/validate.inc.php';
require 'requires/timestamp.inc.php';
require 'requires/input.inc.php';
require 'requires/cek_error.inc.php';
require 'requires/module.inc.php';
require 'requires/db_utility.inc.php';
require 'classes/select.class.php';
require 'requires/file.inc.php';
require 'requires/accounting_utility.inc.php';
require 'requires/stok_utility.inc.php';
require 'classes/smtp.class.php';

//echo $source;
$id_edit=trim($_REQUEST["id_edit"]);
$id_menu=trim($_REQUEST["id_menu"]);
$kd_menu_button=trim($_REQUEST["kd_menu_button"]);

$pstatus=trim($_REQUEST["pstatus"]);
$alasan_approve=($_REQUEST["alasan_approve"]);
$cek=$_REQUEST["cek"];
$no_bast=get_rec("data_gadai.tblproduk_cicilan","no_bast","no_sbg='".$id_edit."'");
if ($cek=="") $cek="f";
//echo $p_status;

$lrs=pg_query("select * from skeleton.tblmenu where kd_menu='".$kd_menu_button."'");	
$lrow=pg_fetch_array($lrs);
$nama_menu=$lrow["nama_menu"];
$larr=split("Approve ",$lrow["nama_menu"]);
$approval=$larr[1];
//buat tau dia approval ke brp 

if(file_exists($path_site."includes/modal_approve_".$id_menu.".inc.php"))
	include $path_site."includes/modal_approve_".$id_menu.".inc.php";

//echo $id_edit;
get_data_menu($id_menu);

$lrs_status=pg_fetch_array(pg_query("select * from skeleton.tblmodule_approval where fk_module='".$kd_module."'"));
//showquery("select * from skeleton.tblmodule_approval where fk_module='".$kd_module."'");
$status_approval=($_REQUEST["status_approval"]);
$alasan_status=trim($_REQUEST[$lrs_status["save_field_alasan"]]);
$alasan_batal=trim($_REQUEST[$lrs_status["save_field_alasan_batal"]]);

get_data_module();



if($_REQUEST["pstatus"]){
	get_data();
	get_data_header();
}

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
	<link href="js/cwcalendar.css.php" rel="stylesheet" type="text/css">
    <link href="text.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src='js/alert.js.php'></script>
<script language='javascript' src='js/calendar.js.php'></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src="js/table_v2.js.php"></script>
<script language='javascript' src="js/input_format_number.js.php"></script>
<script language='javascript' src="js/tab.js.php?kd_module=<?=$kd_module?>&id_menu=<?=$id_menu?>"></script>
<script language='javascript'>
<?
_module_create_js();
?>
function fSave(status_approval){
	lCanSubmit=true
	document.form1.status.value='Save';
	document.form1.status_approval.value=status_approval;
	<? //_module_create_get_isi()?>
	if (lCanSubmit) document.form1.submit();
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")) {
		lInputClose=getObjInputClose();lInputClose.close()
	}
}

function fModal_view(pType,pID,pID2,pID3){

	switch (pType){
		case "view_nasabah":
			show_modal('modal_view.php?pstatus=view&id_view='+pID+'&id_menu='+escape(pID2)+'&kd_menu_button='+escape(pID3),'dialogwidth:900px;dialogheight:545px')
		break;
		case "view_taksir":
			show_modal('modal_view.php?pstatus=view&id_view='+pID+'&id_menu='+escape(pID2)+'&kd_menu_button='+escape(pID3),'dialogwidth:900px;dialogheight:545px')
		break;
		
	}
}


function fSubmit(){
	document.form1.submit();
	//document.getElementById('reference').style.display='block'
	//create_list_data_reference_field_name(pObj.value)
}

function fLoad(){
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	}else if($j_action){
		echo $j_action;
	}else{
		echo "document.form1.cek.focus();";
		echo "document.form1.alasan_approve.focus();";
	}
?>
}
</script>
<body onLoad="fLoad()" bgcolor="#fafafa">
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="form1" enctype="multipart/form-data">
<input type="hidden" name="id_menu" value="<?=$id_menu?>">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="status">
<input type="hidden" name="status_approval">
<input type="hidden" name="pstatus" value="<?=$pstatus?>">
<input type="hidden" name="kd_menu_button" value="<?=$kd_menu_button?>">
<input type="hidden" name="approval" value="<?=$approval?>">
<input type="hidden" name="source" value="<?=$source?>">

<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<tr><td class="judul_menu" align="center"><?=strtoupper($nm_menu)?></td></tr>
			</table>
		</td>
	</tr>
    <table cellpadding="0" cellspacing="0" border="0" width="100%">
     <tr>
      	<td class="border">
<!-- content begin -->
			<? _module_create_header_approval("edit",false)?>
	  	</td>
	</tr>
    <tr>
      	<td class="border">
			<? /*if(get_rec("skeleton.tblmodule","is_tab","fk_menu='".$id_menu."'")!=t)*/ _module_create_detail_view('false')?>
<!-- end content begin -->
	  	
        </td>
    </tr>
    </table>
    <tr>
      	<td class="border">
<!-- content begin -->
		<table cellpadding="0" cellspacing="1" border="0" width="100%">
       		<tr style="padding:0 5 0 5" bgcolor="efefef">
                <td style="padding:0 5 0 5" class="fontColor" width="20%">No Bast</td>
                <td style="padding:0 5 0 5" width="30%"><input type="hidden" value="<?=$no_bast?>" name="no_bast" readonly><?=$no_bast?></td>
             </tr>
       		 <tr bgcolor="efefef">            
               <td style="padding:0 5 0 5" class="fontColor" colspan="2">Kontrak sudah di tanda tangan debitur &nbsp;&nbsp;<input type="checkbox" name="cek" class="groove_checkbox" onClick="" value="t" onKeyUp="fNextFocus(event, document.form1.alasan_approve)" <?=(($cek=="t")?"checked":"")?>></td>
             </tr>
            <tr  style="padding:0 5 0 5" bgcolor="efefef">            
                <td width="20%" class="fontColor" style="padding:0 5 0 5" valign="top">Alasan Approve/Reject</td>
                <td width="80%" style="padding:0 5 0 5">
                    <textarea name="alasan_approve" rows="1" cols="80"  onKeyUp="fNextFocus(event, document.form1.btnSubmit)"></textarea>
                </td>
             </tr>
            <tr bgcolor="efefef">          
            <? 
			if(pg_num_rows(pg_query("select * from data_gadai.tbltaksir where no_fatg='".$_REQUEST["fk_fatg"]."' "))){
				$tbl="data_gadai.tbltaksir";
				$id_menu_taksir='20170900000051';
			}
			else {
				$tbl="data_gadai.tbltaksir_umum";
				$id_menu_taksir='20170900000052';
			}
			
			//history($tbl);
			
			$fk_cif=get_rec("viewtaksir","fk_cif","no_fatg='".$_REQUEST["fk_fatg"]."'");
			
			?>          
             </tr>
          </table>
          
       	<table cellpadding="0" cellspacing="0" border="0" width="100%">
            <tr>
                <td class="border" bgcolor='#efefef' align="center">
					<? 	
					$pic=get_rec($tbl,"pic_brg","no_fatg ='".$_REQUEST[fk_fatg]."'");	
					if($pic!=""){	
                    ?>        
            		<img src="file_read.php?file=<?=$pic?>&name=<?=$pic?>" width="300" height="150"/>
                    <?
					}else{
					?>
                    <img src="images/customer_default.png" width="100" height="100"/>
                    <?
					}
					?>
                </td>
            </tr>
        
        </table>
          
<!-- end content begin -->
	  	</td>
    </tr>
</table>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" align="center">
	<tr height="20">
    	<td height="25" align="center" bgcolor="#D0E4FF">
        <? 
		//echo $nama_menu;
		if(!strstr(strtoupper($nama_menu),'BATAL')){?>
            <input class="groove_button" type='button' name="btnapprove" value='Approve' onClick="fWait();fSave('Approve')">
            <input class="groove_button" type='button' name="btnreject" value='Revisi' onClick="fWait();fSave('Revisi')">
        <? }?>            
            &nbsp;<input class="groove_button" type="button" value="Batal" onClick="fWait();fSave('Batal')">  
            <input class="groove_button" type='button' name="btnview" value='View Data Customer' onClick="fModal_view('view_nasabah','<?=$fk_cif?>','20170800000001','10101011');">      
            &nbsp;<input class="groove_button" type='button' name="btnview" value='View Data Permohonan' onClick="fModal_view('view_taksir','<?=$_REQUEST["fk_fatg"]?>','<?=$id_menu_taksir?>','10101011');">        
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?
function history($tbl){
	global $id_edit,$kd_module;
	
	$query=	"		
	select * from(
		select * from ".$tbl."_detail
		where fk_fatg='".$_REQUEST["fk_fatg"]."' 
	)as tbl
	";
	//showquery($query);
	$l_res=pg_query($query);
?>
    <table cellpadding="0" cellspacing="1" border="0" width="100%">
        <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
            <td align="center" colspan="10" class="judul_menu">DATA KENDARAAN</td>
        </tr>
       <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
            <td align="center">No</td>       
            <td align="center">Kode Barang</td>
            <td align="center">Keterangan Barang</td>
<!--            <td align="center">Karat</td>
            <td align="center">Berat Kotor</td>
            <td align="center">Berat Bersih</td>
-->            <td align="center">Nilai Kendaraan</td>
            
        </tr>        

<?
	$no=1;
	while($lrow=pg_fetch_array($l_res)){		
		
	?>	
        <tr style="padding:0 5 0 5" height="20" bgcolor='#efefef'>
            <td style="padding:0 5 0 5" ><?=$no?></td>        
            <td style="padding:0 5 0 5" ><?=$lrow['fk_barang']?></td>
            <td style="padding:0 5 0 5" ><?=$lrow['keterangan_barang']?></td>
<!--            <td style="padding:0 5 0 5" ><?=$lrow['karat']?></td>
            <td style="padding:0 5 0 5" ><?=$lrow['berat_kotor']?></td>
            <td style="padding:0 5 0 5" ><?=$lrow['berat_bersih']?></td>
-->            <td style="padding:0 5 0 5" align="right"><?=convert_money('',$lrow['nilai_taksir'])?></td>
        </tr>
	<?	
		$no++;
	}
?>
	</table>	
<?	
}
function cek_error(){
	global $strmsg,$j_action,$id_menu,$kd_module,$id_edit,$alasan_status,$nama_menu,$alasan_batal,$status_approval,$pstatus,$nm_menu,$approval,$alasan_approve,$cek,$no_bast;
	if (function_exists('cek_error_module')) {
		//cek_error_module();
		//echo "Cek Error Module functions are available.<br />\n";
		$strmsg.=cek_error_module();
	}
	
/*	if($pstatus=="approve"){
		if($alasan_status=="" || $alasan_status==null){
			$strmsg.="Keterangan Approve Harus Diisi.<br>";
			if(!$j_action) $j_action="document.form1.alasan_status.focus()";	
		} else $j_action="";
	}
	if($pstatus=="batal"){
		if($alasan_batal=="" || $alasan_batal==null){
			$strmsg.="Keterangan Batal Harus Diisi.<br>";
			if(!$j_action) $j_action="document.form1.alasan_batal.focus()";	
		} else $j_action="";
	}
	
*/	
	if($status_approval=="Approve"){
		if($cek=="" || $cek=="f" || $cek==null){
			$strmsg.="Ceklis belum di centang.<br>";
			if(!$j_action) $j_action="document.form1.cek.focus()";	
		}
		if(!$no_bast){
			$strmsg.="No Bast Belum di Input.<br>";
			if(!$j_action) $j_action="document.form1.no_bast.focus()";	
		}
	}
	if($alasan_approve=="" || $alasan_approve==null){
		$strmsg.="Keterangan Harus Diisi.<br>";
		if(!$j_action) $j_action="document.form1.alasan_approve.focus()";	
	}
	if($nm_menu=="Approval Gadai"){
		$lrs_set_status["save_to_table"]="data_gadai.tblproduk_gadai";
	}
	elseif($nm_menu=="Approval Cicilan"){
		$lrs_set_status["save_to_table"]="data_gadai.tblproduk_cicilan";
	}
	
	$l_table_pk = "no_sbg";
	//showquery("select * from skeleton.tblmenu where kd_menu='".$kd_menu_button."'");
	
	if($nama_menu!="Batal Approval"){
		if(pg_num_rows(pg_query("select * from ".$lrs_set_status["save_to_table"]." where ( status_approval='Approve')and ".$l_table_pk." ='".$id_edit."' for update"))){
			$strmsg.="Transaksi sudah di-approve oleh user lain.<br>";
			$j_action="lInputClose=getObjInputClose();lInputClose.close();";
		}elseif(pg_num_rows(pg_query("select * from ".$lrs_set_status["save_to_table"]." where (status_approval='Batal')and ".$l_table_pk." ='".$id_edit."' for update"))){
			$strmsg.="Transaksi sudah di-batal oleh user lain.<br>";
			$j_action="lInputClose=getObjInputClose();lInputClose.close();";
		}elseif(pg_num_rows(pg_query("select * from ".$lrs_set_status["save_to_table"]." where (tgl_approval_".$approval." is not null)and ".$l_table_pk." ='".$id_edit."' for update"))){
			$strmsg.="Transaksi sudah di-approve/batal/revisi oleh user lain.<br>";
			$j_action="lInputClose=getObjInputClose();lInputClose.close();";
		}
	}
	
	
	//showquery("select * from ".$lrs_set_status["save_to_table"]." where tgl_approval_".$approval." is not null and ".$l_table_pk." ='".$id_edit."' for update");
	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}

function save_data(){
	global $j_action,$strmsg,$id_menu,$kd_module,$id_edit, $upload_path,$kd_tabs,$kd_tabs2,$kd_tabs3,$alasan_status,$status_approval,$alasan_batal,$pstatus,$alasan_approve,$nm_menu,$approval,$l_success,$source,$nama_menu,$cek;
	$l_success=1;
	pg_query("BEGIN");
	//echo "aaa".$pstatus;
	if($nm_menu=="Approval Gadai"){
		$lrs_set_status["save_to_table"]="data_gadai.tblproduk_gadai";
	}
	elseif($nm_menu=="Approval Cicilan"){
		$lrs_set_status["save_to_table"]="data_gadai.tblproduk_cicilan";
	}
	$l_table_pk = "no_sbg";
	
	if(!pg_query(insert_log($lrs_set_status["save_to_table"],"".$l_table_pk." = '".$id_edit."'",'UB')))$l_success=0;
	
	$query="select * from tbljenjang_approval order by no asc";
	$lrs=pg_query($query);
	$i=0;
	while($lrow=pg_fetch_array($lrs)){
		$approval_batal.="tgl_approval_".strtolower($lrow["no"])."=NULL,
    ";		
		if(strtolower($lrow["no"])==strtolower($approval)){
			$index=($lrow["no"]);
		}
	}
	
	if($nama_menu!='Batal Approval'){
		$query="select * from tbljenjang_approval where no<".$index." order by no asc";
		$lrs=pg_query($query);
		$i=0;
		while($lrow=pg_fetch_array($lrs)){
			$is_approval=get_rec($lrs_set_status["save_to_table"],"is_approval_".strtolower($lrow["no"]),"no_sbg='".$id_edit."'");
			$tgl_approval=get_rec($lrs_set_status["save_to_table"],"tgl_approval_".strtolower($lrow["no"]),"no_sbg='".$id_edit."'");
			if($tgl_approval==NULL&&$is_approval=='t'){
				$strmsg.="Transaksi belum di-approve oleh ".$lrow["approval"]." .<br>";
				$j_action="lInputClose=getObjInputClose();lInputClose.close();";
			}
		}	
	}
	
	if($status_approval=='Approve'){
		
		if(pg_num_rows(pg_query("select * from ".$lrs_set_status["save_to_table"]." where (tgl_approval_".$approval." is not null)and ".$l_table_pk." ='".$id_edit."' for update"))){
			$strmsg.="Transaksi sudah di-approve.<br>";
			$j_action="lInputClose=getObjInputClose();lInputClose.close();";
			$l_success=0;
		}
		
		if(strtoupper($approval)!=strtoupper($_REQUEST['final_approval'])){
			$status='Need Approval';
		} else {
			$status='Approve';
		}
				
		if(!pg_query("
		update ".$lrs_set_status["save_to_table"]." set 
		tgl_approval_".$approval."='".today_db." ".date("H:i:s")."',
		fk_user_approval_".$approval."='".$_SESSION["username"]."',
		status_approval='".$status."',
		alasan_approve_".$approval."='".$alasan_approve."',
		is_ttd_kontrak='".$cek."'
		where ".$l_table_pk."='".$id_edit."'")) $l_success=0;	
		if(strtoupper($approval)!='KAPOS' && strtoupper($approval)!='KAUNIT'){
			if(strtoupper($approval)!=strtoupper($_REQUEST['final_approval'])){
				//send_email();
			}			
		}
	}

	else if($status_approval=='Revisi'){
		if(!pg_query("
		update ".$lrs_set_status["save_to_table"]." set 
		status_data='Need Approval',
		alasan=null,tgl_approve=null,
		tgl_approval_".$approval."='".today_db." ".date("H:i:s")."',
		fk_user_approval_".$approval."='".$_SESSION["username"]."',	
		alasan_approve_".$approval."='".$alasan_approve."'
		where ".$l_table_pk."='".$id_edit."'")) $l_success=0;	
	}


	else if($status_approval=='Batal'){
		if($nama_menu=='Batal Approval'){
			if(!pg_query("
			update ".$lrs_set_status["save_to_table"]." set 
			".$approval_batal."
			alasan=null,status_approval='Need Approval',status_data='Need Approval'
			,tgl_batal='".today_db." ".date("H:i:s")."'
			where ".$l_table_pk."='".$id_edit."'")) $l_success=0;		
/*			showquery("
			update ".$lrs_set_status["save_to_table"]." set 
			".$approval_batal."
			alasan=null,status_approval='Need Approval'
			,tgl_batal='".today_db." ".date("H:i:s")."'
			where ".$l_table_pk."='".$id_edit."'");	
*/		}else{
			if(!pg_query("
			update ".$lrs_set_status["save_to_table"]." set 
			alasan=null,tgl_approve=null,
			tgl_approval_".$approval."='".today_db." ".date("H:i:s")."',
			fk_user_approval_".$approval."='".$_SESSION["username"]."',		
			alasan_approve_".$approval."='".$alasan_approve."' ,	
			status_approval='Batal'
			where ".$l_table_pk."='".$id_edit."'")) $l_success=0;			
		}
	}
	
	
	
	if(!pg_query(insert_log($lrs_set_status["save_to_table"],"".$l_table_pk." = '".$id_edit."'",'UA')))$l_success=0;	
	if (function_exists('save_additional')) {
		//echo "Approve Additional functions are available.<br />\n";
		save_additional();

	}
	//echo $l_success;
	
	//$l_success=0;
	if ($l_success==1){
		$alasan_status="";
		$alasan_batal="";
		$cek="f";
		$strmsg=$nm_menu." Tersimpan.<br>";
		if($source=='email'){
			$j_action= "window.close()";
		}else{
			$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		}
		pg_query("COMMIT");
	}else{
		$strmsg.="Error :<br>".$nm_menu." Gagal Tersimpan.<br>";
		pg_query("ROLLBACK");
	}
}
function get_host_name(){
	if($_SERVER['DOCUMENT_ROOT']=="D:/Development/Web Project"){
		return "meta-server/gadai/website/";
	}else{
		return "202.78.195.205:81/website/";
	}
}

function send_email($attach=NULL){
	//setting email info
	global $approval,$kd_menu_button,$nm_menu;
	
	$lkaryawan="
	select * from(
		select * from tblkaryawan 
		left join tbljabatan on kd_jabatan=fk_jabatan
		where karyawan_active is true
	)as tblmain
	";
	//$jabatan
	switch(strtolower($approval)){
		/*case "kapos" : 
			$fk_karyawan=get_rec("tblcabang left join (select kd_cabang as fk_cabang,fk_karyawan_kacab as fk_karyawan1 from tblcabang)as tblhead_unit on fk_cabang=head_unit","fk_karyawan1","kd_cabang='".$_REQUEST["fk_cabang"]."'");
			$lwhere=(" where npk = '".$fk_karyawan."'");
		break;
		case "kaunit" : 
			$fk_karyawan=get_rec("tblcabang left join (select kd_cabang as fk_cabang,fk_karyawan_kacab as fk_karyawan1 from tblcabang)as tblhead_cabang on fk_cabang=head_cabang","fk_karyawan1","kd_cabang='".$_REQUEST["fk_cabang"]."'");
			//echo $fk_karyawan;
			$lwhere=(" where npk = '".$fk_karyawan."'");		
		break;*/
		case "kacab" : 
			$fk_karyawan=get_rec("tblcabang left join tblarea on fk_area=kd_area","fk_karyawan_kaarea","kd_cabang='".$_REQUEST["fk_cabang"]."'");
			$lwhere=(" where npk = '".$fk_karyawan."'");
		break;
		case "kaarea" : 
			$fk_karyawan=get_rec("tblcabang left join tblwilayah on fk_wilayah=kd_wilayah","fk_karyawan_kawil","kd_cabang='".$_REQUEST["fk_cabang"]."'");
			$lwhere=(" where npk = '".$fk_karyawan."'");

		break;
		case "kawil" : 
			$lwhere=(" where upper(nm_jabatan) = upper('DIREKTUR')");
		break;
		case "direktur" : 
			$lwhere=(" where upper(nm_jabatan) like upper('%DIREKTUR UTAMA%')");
		break;

	}
	
	
	
	$lrs=pg_query($lkaryawan.$lwhere);
	//showquery($lkaryawan.$lwhere);
	if($nm_menu=="Approval Gadai"){
		$id_menu="20170800000049";
	}elseif($nm_menu=="Approval Cicilan"){
		$id_menu="20170900000073";
	}
	
	$param="id_menu=".$id_menu."&source=email&pstatus=edit&kd_menu_button=".($kd_menu_button+1)."&id_edit=".$_REQUEST['no_sbg'];	

	while($lrow=pg_fetch_array($lrs)){
		
/*		$_sender    = "info@meta-technology.com";
		$_pwd		= "metatech";
		$_smtp 		= "mail.meta-technology.com";
*/		
		$_sender    = "admin@masagung.co.id";
		$_pwd		= "admin12345";
		$_smtp 		= "mail.serbamulia.co.id";
		
		$title 		= "Request Approval untuk No. SBG ".$_REQUEST['no_sbg'];
		//end setting email
		
		$l_success = 1;		
		$email=$lrow["email"];
		$nm_jabatan=$lrow["nm_jabatan"];
		$nama=$lrow["nm_depan"]." ".$lrow["nm_belakang"];
		
		$l_penerima = $email;
		$exec_time = date('m/d/Y H:i:s'); // bwt nama file
		$file_time = date('YmdHis',strtotime($exec_time));
		
		$myFile = "log/email_log/kirim_req_trial_".$file_time.".log"; 
		$fh = fopen($myFile, 'a') or die("can't open file");
	
		$lmail = new SMTP;
		$lmail->delivery('relay');
		$lmail->from($_sender);		
		$lmail->addto($l_penerima?$l_penerima:$_smtp);
		$lmail->addcc('dwi.hardiyanto@serbamulia.co.id');
		//$lmail->addbcc('dwi.hardiyanto@serbamulia.co.id');
		
		//nulis message pertama utk customer
		$message = "
		
		Kepada Yth, Bpk/Ibu <br> 		
		".$nama."<br> 
		
		Mohon untuk SBG ".$_REQUEST['no_sbg']." berikut ini agar dapat di-approve <br><br>	
		";
		
		$message.="<a href='http://".get_host_name()."modal_approve_kontrak.php?".($param)."'>Link</a>";
		
		$message.="
		<br>
		<br>
		Atas perhatiannya, kami ucapkan terima kasih.
		<br>
		<br>
		
		Hormat Kami, 
		<br>
		<br>
		------------------------------<br>
		PT ".$_SESSION["application"]."<br>
		<br><br>
		
		Email ini di-create sistem tanggal ".date("d/m/Y")." jam ".date("H:i:s")."
		<br>
		";	

		//$lmail->attachfile($attach,$file_name,'autodetect','attachment','base64',true);
		
		//echo $message;
		$lmail->html($message);
		$lmail->relay($_smtp,$_sender,$_pwd);
		$log = date("d/m/Y H:i:s")." sent to ".$l_penerima." contains ".$message."\r\n".$l_exec."\r\n";
		//kirim email
		if($lmail->send($title)){
			
			fwrite($fh,$log);
	
		}else $l_success = 0;
		//end kirim email ke customer
		if($l_success=='0'){
			echo 'EMAIL GAGAL TERKIRIM';			
		}
		
	}
	if($l_success)$l_msg = "berhasil";
	else $l_msg = "gagal";
	return "Pesan ".$l_msg." dikirim<br><br><small><a href='#' onClick='window.close()'>Kembali</a></small>";
}
function get_data(){
	global $kd_module,$id_edit,$alasan_status,$status_approval,$pstatus;
	
}

function get_data_header(){
	global $id_edit,$kd_module,$id_menu;

	$lrs_tabel=pg_query("select kd_field,save_to_table,save_to_field,save_to_field_2,fk_data_type,type_list,is_multiple,is_numeric from skeleton.tblmodule_fields
						where fk_module='".$kd_module."'
						order by no_urut_edit, save_to_table,save_to_field");
/*	showquery("select kd_field,save_to_table,save_to_field,save_to_field_2,fk_data_type,type_list,is_multiple from skeleton.tblmodule_fields
						where fk_module='".$kd_module."'
						order by no_urut_edit, save_to_table,save_to_field");
*/						
	while($lrow_table=pg_fetch_array($lrs_tabel)){
		if($lrow_table["save_to_table"]=="") {
			$lrow_table["save_to_table"]=$save_table_old;
		}
		
		$l_arr_table[$lrow_table["save_to_table"]][$lrow_table["kd_field"]]["save_to_field"]=$lrow_table["save_to_field"].(($lrow_table["save_to_field_2"])?",".$lrow_table["save_to_field_2"]:"");
		$l_arr_table[$lrow_table["save_to_table"]][$lrow_table["kd_field"]]["fk_data_type"]=(($lrow_table["fk_data_type"]=="list")?$lrow_table["type_list"]:$lrow_table["fk_data_type"]);
		$l_arr_table[$lrow_table["save_to_table"]][$lrow_table["kd_field"]]["is_multiple"]=$lrow_table["is_multiple"];
		$l_arr_table[$lrow_table["save_to_table"]][$lrow_table["kd_field"]]["is_numeric"]=$lrow_table["is_numeric"];
		
		$save_table_old=$lrow_table["save_to_table"];
	}
	
	$lrs_tabel=pg_query("select distinct save_to_table from skeleton.tblmodule_fields
						where fk_module='".$kd_module."'");
	while($lrow_table=pg_fetch_array($lrs_tabel)){
		$lrs_relation=pg_query("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' and value_type='reference' and fk_data_type='readonly'");
		while ($lrow_relation=pg_fetch_array($lrs_relation)) {
			if ($lrow_relation["reference_table_name"]=="sql_query") {
				$lrow_query=pg_fetch_array(pg_query("select * from skeleton.tblsql_query where kd_sql_query='".$lrow_relation["sql_query"]."'"));
				$l_table=$lrow_query["sql_query"];
			}  else {
				$l_table=$lrow_relation["reference_table_name"];
			}
			$l_arr_relation[$lrow_table["save_to_table"]][$l_table]["field"].=$lrow_relation["reference_field_name"].",";
			$l_arr_relation[$lrow_table["save_to_table"]][$l_table]["expression"]=$lrow_relation["reference_expression"];
		}
	}
	
	$lrs_menu=pg_query("select list_sql from skeleton.tblmenu
				where pk_id='".$id_menu."'
				");
	$lrow_menu=pg_fetch_array($lrs_menu);
	$list_sql=$lrow_menu["list_sql"];

	foreach ($l_arr_table as $l_table=>$l_arr_field){
		if (trim($l_table)) {
			$l_pk=get_rec("skeleton.tbldb_table_detail","kd_field","fk_db_table='".$l_table."' and is_pk=true");
			$l_arr_query[$l_table]="select * from ".$l_table;
			$l_counter_relation=1;
			if (is_array($l_arr_relation)) {
				foreach ($l_arr_relation[$l_table] as $l_join_table=>$l_arr_content) {
					$l_arr_query[$l_table].=" left join ".$l_join_table." on ".$l_arr_content["expression"];
					$l_counter_relation++;
				}
			}
			
			$l_arr_query[$l_table]= "select * from ( ".$list_sql." )as tblmain";// modif baru
			$l_arr_query[$l_table].= " where ".$l_pk."='".convert_sql($id_edit)."'";
			$l_arr_query[$l_table]=str_replace("[","(",$l_arr_query[$l_table]);
			$l_arr_query[$l_table]=str_replace("]",")",$l_arr_query[$l_table]);
			$lrow=pg_fetch_array(pg_query($l_arr_query[$l_table]));
			//showquery($l_arr_query[$l_table]);
			
			foreach ($l_arr_field as $l_kd_field=>$l_save_to_field) {
				if(strstr($l_save_to_field["save_to_field"],",")){
					$save_to_field=explode(",",$l_save_to_field["save_to_field"]);
					$l_field=$save_to_field[0];
				}else{
					$l_field=(($l_save_to_field["save_to_field"]=="")?$l_kd_field:$l_save_to_field["save_to_field"]);
				}
				$l_kd_field=str_replace("[]","",$l_kd_field);	
					
				if ($l_save_to_field["fk_data_type"]=="list_db" && $l_save_to_field["is_multiple"]=="f") {
					
					$lrow_field=pg_fetch_array(pg_query("select * from skeleton.tblmodule_fields where fk_module='".convert_sql($kd_module)."' and kd_field='".convert_sql($l_kd_field)."'"));
					if(strpos ($lrow_field["list_sql"],"where")>=1){
						if ($lrow_reference=pg_fetch_array(pg_query($lrow_field["list_sql"]." and ".$lrow_field["list_field_value"]."='".$lrow[$l_field]."'"))){
							$_REQUEST[$l_kd_field]=$lrow_reference[$lrow_field["list_field_text"]];
						}

					} else{
						if ($lrow_reference=pg_fetch_array(pg_query($lrow_field["list_sql"]." where ".$lrow_field["list_field_value"]."='".$lrow[$l_field]."'"))){
							$_REQUEST[$l_kd_field]=$lrow_reference[$lrow_field["list_field_text"]];
						}
					}
				} elseif ($l_save_to_field["fk_data_type"]=="list_manual" && $l_save_to_field["is_multiple"]=="f") {
					$lrow_field=pg_fetch_array(pg_query("select * from skeleton.tblmodule_fields where fk_module='".convert_sql($kd_module)."' and kd_field='".convert_sql($l_kd_field)."'"));
					//showquery("select * from skeleton.tblmodule_fields where fk_module='".convert_sql($kd_module)."' and kd_field='".convert_sql($l_kd_field)."'");
					$l_arr_list_manual_value=split(",",$lrow_field["list_manual_value"]);
					//print_r($l_arr_list_manual_value);
					
					$l_arr_list_manual_text=split(",",$lrow_field["list_manual_text"]);
					for ($l_index=0;$l_index<count($l_arr_list_manual_value);$l_index++){
						$l_arr_list[$l_arr_list_manual_value[$l_index]]=$l_arr_list_manual_text[$l_index];
					}
					$_REQUEST[$l_kd_field]=$l_arr_list[$lrow[$l_field]];
					
				} else if ($l_save_to_field["fk_data_type"]=="range_date"){
					$lvalue=explode(",",$l_save_to_field["save_to_field"]);	
					$_REQUEST[$l_kd_field]=format_data_view($lrow[$lvalue[0]],"date","d/m/Y")." - ".format_data_view($lrow[$lvalue[1]],"date","d/m/Y");
				} else if($l_save_to_field["fk_data_type"]=="range_value"){ 
					$lrange_value=explode(",",$l_save_to_field["save_to_field"]);
					$_REQUEST[$l_kd_field]=format_data_view($lrow[$lrange_value[0]],"d/m/Y")." - ".format_data_view($lrow[$lrange_value[1]],"d/m/Y");	
				} else {
					//echo $lrow[$l_field]." ".$l_field.'<br>';		
					if($l_save_to_field["is_numeric"]=="t")$l_save_to_field["fk_data_type"]="numeric";
					if ($lrow[$l_field] ||$lrow[$l_field]==0) $_REQUEST[$l_kd_field]=format_data_view($lrow[$l_field],$l_save_to_field["fk_data_type"],"d/m/Y");//ikutin di semua approve
					
				}
				//echo $_REQUEST["fk_jenis_barang"].'dd';
			}
		}
	}
}

function get_data_module(){
	global $kd_module,$j_action,$nm_tabs;
	
	//query untuk memunculkan tab 
	$lrow_tab_switch=pg_fetch_array(pg_query("select * from skeleton.tblmodule_tabs where fk_module='".$kd_module."' order by no_urut_tabs limit 1"));
	$nm_tabs=$lrow_tab_switch["kd_tabs"];
	//$lrow_first_field=pg_fetch_array(pg_query("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' order by no_urut_edit limit 1"));
	//$j_action="document.form1.".$lrow_first_field["kd_field"].".focus();";
	
	//$j_action="document.form1.".get_rec("skeleton.tblmodule_approval","save_field_alasan","fk_module='".$kd_module."'").".focus();";
	//showquery("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' order by no_urut_edit limit 1");
	//echo $lrow_first_field["kd_field"];
}
?>