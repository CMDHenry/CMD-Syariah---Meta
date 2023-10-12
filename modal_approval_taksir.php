<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
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
set_time_limit(0);

$id_edit=trim($_REQUEST["id_edit"]);
$id_menu=trim($_REQUEST["id_menu"]);
$kd_tabs=trim($_REQUEST["kd_tabs"]);
$kd_tabs2=trim($_REQUEST["kd_tabs2"]);
$kd_tabs3=trim($_REQUEST["kd_tabs3"]);
$kd_menu_button=trim($_REQUEST["kd_menu_button"]);

//$nm_menu_approval=get_rec("(select * from skeleton.tblmenu left join (select pk_id as pk_id_parent,nama_menu as nm_menu_approval from skeleton.tblmenu)as tblmenu1 on fk_parent=pk_id_parent)as tblmain","nm_menu_approval"," pk_id='".$id_menu."'");
//$larr=split('Approval Taksir ',$nm_menu_approval);
//$no=$larr[1];
$lrs=pg_query("select * from skeleton.tblmenu where kd_menu='".$kd_menu_button."'");	
$lrow=pg_fetch_array($lrs);
$larr=split("Approve ",$lrow["nama_menu"]);
$approval=$larr[1];
//buat tau dia approval ke brp 
$status_approval=($_REQUEST["status_approval"]);

if(file_exists($path_site."includes/modal_edit_".$id_menu.".inc.php"))
	include $path_site."includes/modal_edit_".$id_menu.".inc.php";
get_data_menu($id_menu);

get_data_module();
if($_REQUEST["status"]=="Save") {

	cek_error();
	if(!$strmsg){
		save_data();
	}
}
 

if($_REQUEST["pstatus"]){
	get_data();
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
<script language='javascript' src="js/<?=(get_rec("skeleton.tblmodule","use_table_detail","fk_menu='".$id_menu."'"))?get_rec("skeleton.tblmodule","use_table_detail","fk_menu='".$id_menu."'"):"table_v2.js.php"?>"></script>
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
	
	<? _module_create_get_isi()?>
	if (lCanSubmit) document.form1.submit();
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")) {
		lInputClose=getObjInputClose();lInputClose.close()
	}
}

function fSubmit(){
	document.form1.submit();
	//document.getElementById('reference').style.display='block'
	//create_list_data_reference_field_name(pObj.value)
}

function fLoad(){
	<?=get_rec("skeleton.tblmodule","onload_function","fk_menu='".$id_menu."'")?>
	
	if('<?=get_rec("skeleton.tblmodule","is_tab","fk_menu='".$id_menu."'")?>'=='t'){
		fSwitch('div<?=$nm_tabs?>',document.getElementById('menuBar').children[0])	
		<? _module_create_menu_tab_load("edit")?>
	}
<?
	echo "document.form1.alasan_approval_".$approval.".focus();";
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	}else if($j_action){
		echo $j_action;
	}else{
		echo "document.form1.alasan_approval_".$approval.".focus();";
	}
?>
}
</script>
<body onLoad="fLoad()" bgcolor="#fafafa">
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="form1" enctype="multipart/form-data">
<input type="hidden" name="id_menu" value="<?=$id_menu?>">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="kd_tabs" value="<?=$kd_tabs?>">
<input type="hidden" name="kd_tabs2" value="<?=$kd_tabs2?>">
<input type="hidden" name="kd_tabs3" value="<?=$kd_tabs3?>">
<input type="hidden" name="kd_menu_button" value="<?=$kd_menu_button?>">
<input type="hidden" name="status">
<input type="hidden" name="approval" value="<?=$approval?>">
<input type="hidden" name="status_approval">


<?
	if (function_exists('html_additional')) {
		html_additional();
	}

?>

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
			<? _module_create_header("edit")?>
			<? 
			if(get_rec("skeleton.tblmodule","is_tab","fk_menu='".$id_menu."'")!=t) _module_create_detail("inline-table");
			?>
<!-- end content begin -->
	  	</td>
    </tr>
    <tr>
      	<td class="border" bgcolor='#efefef' align="center">
<? 	if(pg_num_rows(pg_query("select * from data_gadai.tbltaksir where no_fatg='".$id_edit."' ")))$tbl="data_gadai.tbltaksir";
	else $tbl="data_gadai.tbltaksir_umum";
	$pic=get_rec($tbl,"pic_brg","no_fatg ='".$id_edit."'");
?>        
    <img src="file_read.php?file=<?=$pic?>&name=<?=$pic?>" width="300" height="150"/>
	  	</td>
    </tr>
    <? history($tbl)?>        
</table>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" align="center">
	<tr height="20">
    	<td height="25" align="center" bgcolor="#D0E4FF">
            <input class="groove_button" type='button' name="btnsubmit" value='Approve' onClick="fSave('Approve')">
            &nbsp;<input class="groove_button" type="button" value="Batal" onClick="fSave('Batal')">

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
		select * from ".$tbl."_log
		left join tbluser on kd_user=log_action_userid
		left join tblkaryawan on fk_karyawan=npk
		where no_fatg='".$id_edit."' and log_action_mode in('IA','UA')
		--and status_taksir='Approve'
	)as tbllog
	inner join ".$tbl."_detail_log on fk_detail_log=pk_id_log
	left join tblbarang on kd_barang=fk_barang
	order by log_action_date asc
	";
	//showquery($query);
	$l_res=pg_query($query);
	while($lrow=pg_fetch_array($l_res)){		
		$arr[$lrow["pk_id_log"]][$lrow["fk_barang"]]['karat']=$lrow["karat"];
		$arr[$lrow["pk_id_log"]][$lrow["fk_barang"]]['berat_kotor']=$lrow["berat_kotor"];
		$arr[$lrow["pk_id_log"]][$lrow["fk_barang"]]['berat_bersih']=$lrow["berat_bersih"];
		$arr[$lrow["pk_id_log"]][$lrow["fk_barang"]]['nm_barang']=$lrow["nm_barang"];
		$arr[$lrow["pk_id_log"]][$lrow["fk_barang"]]['fk_barang']=$lrow["fk_barang"];
		$arr[$lrow["pk_id_log"]][$lrow["fk_barang"]]['jumlah']=$lrow["jumlah"];
		$arr[$lrow["pk_id_log"]][$lrow["fk_barang"]]['keterangan_barang']=$lrow["keterangan_barang"];
		$arr[$lrow["pk_id_log"]][$lrow["fk_barang"]]['nilai_taksir']=$lrow["nilai_taksir"];
		
		$user[$lrow["pk_id_log"]]=$lrow["nm_depan"];
	}
?>
    <table cellpadding="0" cellspacing="1" border="0" width="100%">
        <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
            <td align="center" colspan="10" class="judul_menu">HISTORY PENAKSIRAN</td>
        </tr>
       <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
            <td align="center">Kode Brg</td>
            <td align="center">Nama Barang</td>
            <td align="center">Jumlah</td>
            <td align="center">Keterangan Barang</td>
            <td align="center">Karat</td>
            <td align="center">Berat Kotor</td>
            <td align="center">Berat Bersih</td>
            <td align="center">Nilai Taksir</td>
            
        </tr>        

<?
	$no=1;
	
	$arr_change=array("karat","berat_kotor","berat_bersih");
	//while($lrow=pg_fetch_array($l_res)){		
	foreach($arr as $pk_id_log=>$arr2){			
?>
        <tr style="padding:0 5 0 5" height="20" bgcolor='#efefef'>
			<td style="padding:0 5 0 5" colspan="8"><?=$no?> - <?=$user[$pk_id_log]?></td>            
		</tr>
        
<?		
		foreach($arr2 as $fk_barang=>$lrow){		
			for($i=0;$i<count($arr_change);$i++){
				$color[$arr_change[$i]]="";
			}

			if($no==1){
				for($i=0;$i<count($arr_change);$i++){
					$temp[$fk_barang][$arr_change[$i]]=$lrow[$arr_change[$i]];
				}
			}else{
				for($i=0;$i<count($arr_change);$i++){
					if($temp[$fk_barang][$arr_change[$i]]!=$lrow[$arr_change[$i]]){
						$temp[$fk_barang][$arr_change[$i]]=$lrow[$arr_change[$i]];
						$color[$arr_change[$i]]="red";
					}
				}

			}
			
		?>	
			<tr style="padding:0 5 0 5" height="20" bgcolor='#efefef'>
				<td style="padding:0 5 0 5" ><?=$lrow['fk_barang']?></td>
				<td style="padding:0 5 0 5" ><?=$lrow['nm_barang']?></td>
				<td style="padding:0 5 0 5" align="right" ><?=$lrow['jumlah']?></td>
				<td style="padding:0 5 0 5" ><?=$lrow['keterangan_barang']?></td>
				<td style="padding:0 5 0 5;background-color:<?=$color[$arr_change[0]]?>" align="right"><?=$lrow['karat']?></td>
				<td style="padding:0 5 0 5;background-color:<?=$color[$arr_change[1]]?>"" align="right"><?=$lrow['berat_kotor']?></td>
				<td style="padding:0 5 0 5;background-color:<?=$color[$arr_change[2]]?>"" align="right"><?=$lrow['berat_bersih']?></td>
				<td style="padding:0 5 0 5" align="right"><?=convert_money('',$lrow['nilai_taksir'])?></td>
			</tr>
		<?	
			
		}
		?>		
    	
<!--        <tr style="padding:0 5 0 5" height="20" bgcolor='#efefef'>
			<td style="padding:0 5 0 5" colspan="8"></td>            
		</tr>
-->
    <?
		$no++;

	}
?>
	</table>	
<?	
}
function cek_error(){
	global $strmsg,$j_action,$id_menu,$kd_module,$id_edit,$approval;
	$j_action="";

	$l_is_tab=get_rec("skeleton.tblmodule","is_tab","fk_menu='".$id_menu."'");
	$lrs_field=pg_query("select * from skeleton.tblmodule_fields
		where fk_module='".$kd_module."' and is_edit is true --and is_read_only_edit='f'
		order by no_urut_edit asc");
	while($lrow_field=pg_fetch_array($lrs_field)){
			if($lrow_field["save_to_table"]!="") {
				$save_table=$lrow_field["save_to_table"];
			}

			$lrow_tab=pg_fetch_array(pg_query("select * from skeleton.tblmodule_tabs where fk_module='".$kd_module."' and no_begin_edit<='".$lrow_field["no_urut_edit"]."' and no_end_edit>='".$lrow_field["no_urut_edit"]."'"));
			cek_error_field($lrow_field["fk_data_type"],$lrow_field["kd_field"],$lrow_field["nm_field"],$lrow_field["is_require"],$lrow_field["condition_require"],$strmsg,$j_action,$lrow_tab["kd_tabs"]);

			if($_REQUEST[$lrow_field["kd_field"]]!=""){	//Cek Error diluar Tab selain kosong
				$lrs_cek_error_other=pg_query("select * from skeleton.tblmodule_fields_cek_error where fk_module_fields='".$lrow_field["pk_id"]."' and (status='Edit' or status='Add-Edit')");
				
				//showquery("select * from skeleton.tblmodule_fields_cek_error where fk_module_fields='".$lrow_field["pk_id"]."' and (status='Edit' or status='Add-Edit')");
				while($lrow_cek_error_other=pg_fetch_array($lrs_cek_error_other)){
					//echo $lrow_cek_error_other["condition"];
					eval("\$lresult=".$lrow_cek_error_other["condition"].";");
					//echo $lresult."aa";
					if($lresult){
						$strmsg.=$lrow_cek_error_other["pesan_error"].'<br>';
						if(!$j_action) 	{
							$j_action=(($l_is_tab=='t')?"fSwitch('div".$lrow_tab["kd_tabs"]."',document.getElementById('div".$lrow_tab["kd_tabs"]."'));":"");
							$j_action.="document.form1.".$lrow_field["kd_field"].".focus();";
						}
				    }
			   }
		  }
	}
	
	//cek apakah detail harus diisi atau tidak (diluar tab)
	$lrs_detail=pg_query("select * from skeleton.tblmodule_detail where fk_module='".convert_sql($kd_module)."'");
	while($lrow_detail=pg_fetch_array($lrs_detail)){
		if($lrow_detail["condition_require"]){
			eval("\$l_result_condition_require =".$lrow_detail["condition_require"].";");
			if($_REQUEST["strisi_".$lrow_detail["kd_module_detail"]]=="" && $lrow_detail["is_require"]=='t' && $l_result_condition_require){	//buat $striisi.$i
				$strmsg.=$lrow_detail["nm_module_detail"].' Kosong <br>';
			} 
			else if(($_REQUEST["strisi_".$lrow_detail["kd_module_detail"]]!="" && $lrow_detail["is_require"]=='t' && !$l_result_condition_require)){
				$_REQUEST["strisi_".$lrow_detail["kd_module_detail"]]="";
			}
		}else if($lrow_detail["condition_require"]==""){
			if($_REQUEST["strisi_".$lrow_detail["kd_module_detail"]]=="" && $lrow_detail["is_require"]=='t'){	//buat $striisi.$i
				$strmsg.=$lrow_detail["nm_module_detail"].' Kosong <br>';
			} 
			//else{
				
			//}
		}
		
		$l_arr_row = split(chr(191),$_REQUEST["strisi_".$lrow_detail["kd_module_detail"]]);
		if(count($l_arr_row)>0) {
			$l_index_array=1;
			$l_arr_item=array();
			for ($i=0; $i<count($l_arr_row)-1; $i++){	//jumlah baris yang ada didetail
				$l_arr_col=split(chr(187),$l_arr_row[$i]);
				$lrs_detail_field=pg_query("select * from skeleton.tblmodule_detail_fields
					where fk_module_detail='".$lrow_detail["pk_id"]."' and fk_data_type<>'readonly'
					order by no_urut asc");
				while ($lrow_detail_field=pg_fetch_array($lrs_detail_field)){
					cek_error_field_detail($l_arr_col[$lrow_detail_field["no_urut"]],$lrow_detail_field["fk_data_type"],$lrow_detail_field["nm_field"],$lrow_detail_field["is_require"],$lrow_detail_field["condition_require"],$strmsg,$j_action,"@Detail line ".($i+1)." : ");
					
					if($l_arr_col[$lrow_detail_field["no_urut"]]!=""){	//Cek Error diluar Tab selain kosong
						//showquery("select * from skeleton.tblmodule_detail_fields_cek_error where fk_module_fields_detail='".$lrow_detail_field["pk_id"]."' and (status='Edit' or status='Add-Edit')");
						$lrs_cek_error_other=pg_query("select * from skeleton.tblmodule_detail_fields_cek_error where fk_module_fields_detail='".$lrow_detail_field["pk_id"]."' and (status='Edit' or status='Add-Edit')");
						while($lrow_cek_error_other=pg_fetch_array($lrs_cek_error_other)){
							eval("\$lresult =".$lrow_cek_error_other["condition"].";");
							//echo $lresult."dadada";
							if($lresult){
								$strmsg.="@Detail line ".($i+1)." : ".$lrow_cek_error_other["pesan_error"]."<br>";
							}
						}
					}
				}
			}
		}
	}
	
	//tgl_approval_".$approval." is not null or
	if(pg_num_rows(pg_query("select * from ".$save_table." where (status_approval_taksir='Approve') and no_fatg='".$id_edit."' for update"))){
		$strmsg.="Transaksi sudah di-approve oleh user lain.<br>";
		$j_action="lInputClose=getObjInputClose();lInputClose.close();";
	}
	elseif(pg_num_rows(pg_query("select * from ".$save_table." where (status_approval_taksir='Batal') and no_fatg='".$id_edit."' for update"))){
		$strmsg.="Transaksi sudah di-batal oleh user lain.<br>";
		$j_action="lInputClose=getObjInputClose();lInputClose.close();";
	}
	elseif(pg_num_rows(pg_query("select * from ".$save_table." where (tgl_approval_".$approval." is not null) and no_fatg='".$id_edit."' for update"))){
		$strmsg.="Transaksi sudah di-approve/batal oleh user lain.<br>";
		$j_action="lInputClose=getObjInputClose();lInputClose.close();";
	}

	//showquery("select * from data_gadai.tbltaksir where tgl_approval_".$no." is not null and no_fatg='".$id_edit."' for update");
	if (function_exists('cek_error_module')) {
		//cek_error_module();
		//echo "Cek Error Module functions are available.<br />\n";
		$strmsg.=cek_error_module();
	}
	
	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}

function save_data(){
	global $j_action,$strmsg,$id_menu,$kd_module,$id_edit, $upload_path,$kd_tabs,$kd_tabs2,$kd_tabs3,$upload_path_website_pic,$nm_menu,$no,$approval,$status_approval;
	if (function_exists('get_additional')) {
		//echo "Get Additional functions are available.<br />\n";
		get_additional();

	}

	$l_success=1;
	
	pg_query("BEGIN");
	
	if($kd_tabs=="" && $kd_tabs2=="" && $kd_tabs3==""){
/*		showquery("select kd_field,save_to_table,save_to_field,save_to_field_2,fk_data_type,default_value_input,is_tab from skeleton.tblmodule_fields
							where fk_module='".$kd_module."' and (is_edit is true or (is_edit is true and default_value_input !='')) and is_read_only_edit is 
							false and is_tab is false order by save_to_table,save_to_field");
*/		$lrs_tabel=pg_query("select kd_field,save_to_table,save_to_field,save_to_field_2,fk_data_type,default_value_input,is_tab from skeleton.tblmodule_fields
							where fk_module='".$kd_module."' 
							and (is_edit is true or (is_edit is true and default_value_input !='')) 
							and (is_read_only_edit is false or (default_value_input !='' and fk_data_type!='readonly'))
							and is_tab is false order by save_to_table,save_to_field");
	}else{
		$lrs_tabel=pg_query("select * 
							from skeleton.tblmodule_fields inner join skeleton.tblmodule_tabs on 						
							skeleton.tblmodule_fields.fk_module=skeleton.tblmodule_tabs.fk_module 
							and skeleton.tblmodule_fields.no_urut_edit between skeleton.tblmodule_tabs.no_begin_edit and
							skeleton.tblmodule_tabs.no_end_edit where skeleton.tblmodule_fields.fk_module='".$kd_module."' 
							and (is_edit is true or (is_edit is true and default_value_input !='')) and is_read_only_edit is false
							and (kd_tabs='".$kd_tabs."' or kd_tabs='".$kd_tabs3."' or kd_tabs='".$kd_tabs2."' or no_urut_tabs='0')
							order by kd_field,save_to_table,save_to_field");
		/*showquery("select * 
							from skeleton.tblmodule_fields inner join skeleton.tblmodule_tabs on 						
							skeleton.tblmodule_fields.fk_module=skeleton.tblmodule_tabs.fk_module 
							and skeleton.tblmodule_fields.no_urut_edit between skeleton.tblmodule_tabs.no_begin_edit and
							skeleton.tblmodule_tabs.no_end_edit where skeleton.tblmodule_fields.fk_module='".$kd_module."' 
							and (is_edit is true or (is_edit is true and default_value_input !='')) and is_read_only_edit is false
							and (kd_tabs='".$kd_tabs."' or kd_tabs='".$kd_tabs3."' or kd_tabs='".$kd_tabs2."' or no_urut_tabs='0')
							order by kd_field,save_to_table,save_to_field");	*/
	}					
	while($lrow_table=pg_fetch_array($lrs_tabel)){
		$l_arr_table[$lrow_table["save_to_table"]][$lrow_table["kd_field"]]=$lrow_table["save_to_field"].(($lrow_table["save_to_field_2"])?",".$lrow_table["save_to_field_2"]:"");
		$data_type[$lrow_table["kd_field"]]=$lrow_table["fk_data_type"];
		
	}
	if($l_arr_table!=NULL){
		foreach ($l_arr_table as $l_table=>$l_arr_field){	
			$l_pk=get_rec("skeleton.tbldb_table_detail","kd_field","fk_db_table='".$l_table."' and is_pk=true");
			if (!generate_log($l_table,$id_edit,"UB",$l_pk)) $l_success=0;	
			$l_arr_query[$l_table]="update ".$l_table." set";
			foreach ($l_arr_field as $l_kd_field=>$l_save_to_field) {
				$l_kd_field=str_replace("[]","",$l_kd_field);
				if($data_type[$l_kd_field]=='date'){
					$l_arr_query[$l_table].=" ".$l_save_to_field."=".(($_REQUEST[$l_kd_field])?"'".((is_array($_REQUEST[$l_kd_field]))?join(",",$_REQUEST[$l_kd_field]):convert_date_english($_REQUEST[$l_kd_field]))."'":"null").",";
				}else if($data_type[$l_kd_field]=='range_value' || $data_type[$l_kd_field]=='range_date'){
					if($data_type[$l_kd_field]=='range_date'){
						$_REQUEST[$l_kd_field."1"]=convert_date_english($_REQUEST[$l_kd_field."1"]);
						$_REQUEST[$l_kd_field."2"]=convert_date_english($_REQUEST[$l_kd_field."2"]);
						$l_save_to_field=explode(",",$l_save_to_field);
						
						$date_search2=explode("/",strtoupper($_REQUEST[$l_kd_field."2"]));
						$_REQUEST[$l_kd_field."2"]=$date_search2[2]."-".$date_search2[0]."-".$date_search2[1]." 23:59:59";
						
						$l_arr_query[$l_table].=" ".$l_save_to_field[0]."=".(($_REQUEST[$l_kd_field."1"])?"'".((is_array($_REQUEST[$l_kd_field."1"]))?join(",",$_REQUEST[$l_kd_field."1"]):$_REQUEST[$l_kd_field."1"])."'":"null").",";
						$l_arr_query[$l_table].=" ".$l_save_to_field[1]."=".(($_REQUEST[$l_kd_field."2"])?"'".((is_array($_REQUEST[$l_kd_field."2"]))?join(",",$_REQUEST[$l_kd_field."2"]):$_REQUEST[$l_kd_field."2"])."'":"null").",";
					}else{
						$_REQUEST[$l_kd_field."1"]=$_REQUEST[$l_kd_field."1"];
						$_REQUEST[$l_kd_field."2"]=$_REQUEST[$l_kd_field."2"];
						if($_REQUEST[$l_kd_field."1"]==null || $_REQUEST[$l_kd_field."1"]==0) $_REQUEST[$l_kd_field."1"]=0;
						if($_REQUEST[$l_kd_field."2"]==null || $_REQUEST[$l_kd_field."2"]==0) $_REQUEST[$l_kd_field."2"]=0;
						
						$l_save_to_field=explode(",",$l_save_to_field);
						$l_arr_query[$l_table].=" ".$l_save_to_field[0]."=".(($_REQUEST[$l_kd_field."1"])?"'".((is_array($_REQUEST[$l_kd_field."1"]))?join(",",$_REQUEST[$l_kd_field."1"]):$_REQUEST[$l_kd_field."1"])."'":"0").",";
						$l_arr_query[$l_table].=" ".$l_save_to_field[1]."=".(($_REQUEST[$l_kd_field."2"])?"'".((is_array($_REQUEST[$l_kd_field."2"]))?join(",",$_REQUEST[$l_kd_field."2"]):$_REQUEST[$l_kd_field."2"])."'":"0").",";
					}
				}else if($data_type[$l_kd_field]=='password'){
					$l_arr_query[$l_table].=" ".$l_save_to_field."=".(($_REQUEST[$l_kd_field])?"'".((is_array($_REQUEST[$l_kd_field]))?join(",",$_REQUEST[$l_kd_field]):crypt($_REQUEST[$l_kd_field]))."'":"null").",";
				}else if($data_type[$l_kd_field]=='checkbox'){
					$l_arr_query[$l_table].=" ".$l_save_to_field."=".(($_REQUEST[$l_kd_field])?"'".((is_array($_REQUEST[$l_kd_field]))?join(",",$_REQUEST[$l_kd_field]):$_REQUEST[$l_kd_field])."'":'false').",";
				} else if($data_type[$l_kd_field]=='file'){
					$menu_pic=trim($_REQUEST[$l_kd_field]);
					$pic = $_FILES[$l_kd_field]['name'];
					if($pic) {$l_success = move_uploaded_file($_FILES[$l_kd_field]['tmp_name'], $upload_path_website_pic."/".$_FILES[$l_kd_field]['name']);
					$l_arr_query[$l_table].=" ".$l_save_to_field."=".(($pic)?"'".((is_array($pic))?join(",",$pic):$pic)."'":"null").",";
					}
				} else if($data_type[$l_kd_field]=='others'){
					$_REQUEST[$l_kd_field]=$_REQUEST[$l_kd_field."1"]."-".$_REQUEST[$l_kd_field."2"]."-".$_REQUEST[$l_kd_field."3"];
					//$l_save_to_field=explode(",",$l_save_to_field);
					$l_arr_query[$l_table].=" ".$l_save_to_field."=".(($_REQUEST[$l_kd_field])?"'".((is_array($_REQUEST[$l_kd_field]))?join(",",$_REQUEST[$l_kd_field]):$_REQUEST[$l_kd_field])."'":"null").",";
				}else{
					//error kalau data_type numeric dan inputny 0
					//$l_arr_query[$l_table].=" ".$l_save_to_field."=".(($_REQUEST[$l_kd_field] || $_REQUEST[$l_kd_field]==0 )?"'".((is_array($_REQUEST[$l_kd_field]))?join(",",$_REQUEST[$l_kd_field]):$_REQUEST[$l_kd_field])."'":"null").",";
					
					$l_arr_query[$l_table].=" ".$l_save_to_field."=".(($_REQUEST[$l_kd_field]!=NULL)?"'".((is_array($_REQUEST[$l_kd_field]))?join(",",$_REQUEST[$l_kd_field]):$_REQUEST[$l_kd_field])."'":"null").",";
					//echo $data_type[$l_kd_field];
				}
			}
			//showquery($l_arr_query[$l_table]);
			$l_arr_query[$l_table]=substr($l_arr_query[$l_table],0,-1);
	        if($status_approval=='Approve'){
				$final_penaksir_old=get_rec($l_table,"final_penaksir"," ".$l_pk."='".$id_edit."'");
				if(strtoupper($approval)!=strtoupper($final_penaksir_old) ){//kl masi blum sama masi need approval
				//if(strtoupper($approval)!=strtoupper($_REQUEST['final_penaksir'])){
					$status='Need Approval';
				} else {
					if(($final_penaksir_old-$_REQUEST['final_penaksir'])<2){// buat tahan ga boleh turun 2 tingkat
      					$status='Approve';
					}else{
						$l_success=0;
						$strmsg="Jenjang Approval tidak boleh turun lebih dari 1 .<br>";
					}
				}
			} elseif($status_approval=='Batal'){
				$status='Batal';
			}
		
			$l_arr_query[$l_table].=" ,tgl_approval_".$approval."='".today_db." ".date("H:i:s")."',fk_user_approval_".$approval."='".$_SESSION["username"]."',status_approval_taksir='".$status."' ";
			$l_arr_query[$l_table].=" where ".$l_pk."='".$id_edit."'";
			//showquery($l_arr_query[$l_table]);
			if(!pg_query($l_arr_query[$l_table])) $l_success=0;
			if (!generate_log($l_table,$id_edit,"UA",$l_pk)) $l_success=0;
		}
	}
//strisi -- detail table
	$lrs_detail=pg_query("select * from skeleton.tblmodule_detail where fk_module='".convert_sql($kd_module)."'");
	//showquery("select * from skeleton.tblmodule_detail where fk_module='".convert_sql($kd_module)."'");
	while ($lrow_detail=pg_fetch_array($lrs_detail)){
		$lrs_tabel=pg_query("select no_urut,save_to_table,save_to_field,fk_data_type,is_numeric from skeleton.tblmodule_detail_fields
							where fk_module_detail='".$lrow_detail["pk_id"]."' and (is_read_only is false or save_to_field !='')
							order by no_urut");
		/*showquery("select no_urut,save_to_table,save_to_field from skeleton.tblmodule_detail_fields
							where fk_module_detail='".$lrow_detail["pk_id"]."' and is_read_only is false
							order by no_urut");*/
		while($lrow_table=pg_fetch_array($lrs_tabel)){
			$l_arr_detail[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["no_urut"]]=$lrow_table["save_to_field"];	
			$l_numeric[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["no_urut"]]["is_numeric"]=$lrow_table["is_numeric"];		
			//echo $lrow_detail["kd_module_detail"];			//$l_arr_detail[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["save_to_field"]]=$lrow_table["fk_fata_type"];
		}
	}
	//print_r($l_numeric);
	if (is_array($l_arr_detail)) {
		foreach ($l_arr_detail as $l_kd_module_detail=>$l_arr_table){
			if ($_REQUEST["strisi_".$l_kd_module_detail]){
				foreach ($l_arr_table as $l_table=>$l_arr_field){
					$parent_table=get_rec("skeleton.tbldb_table","parent_table","is_table_detail is true and kd_table='".$l_table."'");
					$l_fk_field=get_rec("skeleton.tbldb_table_detail","kd_field","is_foreign_key is true and fk_db_table='".$l_table."' and foreign_table='".$parent_table."'");
					//showquery("delete from ".$l_table." where ".$l_fk_field."='".$id_edit."'");
					if (!pg_query("delete from ".$l_table." where ".$l_fk_field."='".$id_edit."'")) $l_success=0;
					$l_arr_row = split(chr(191),$_REQUEST["strisi_".$l_kd_module_detail]);
					for ($i=0; $i<count($l_arr_row)-1; $i++){
						$l_arr_col=split(chr(187),$l_arr_row[$i]);
						$l_arr_query[$l_kd_module_detail][$l_table].="insert into ".$l_table." (".$l_fk_field.",".join(",",$l_arr_table[$l_table]).") values (";
						$l_arr_query[$l_kd_module_detail][$l_table].=(($id_edit)?"'".$id_edit."'":"null").",";
						foreach ($l_arr_field as $l_no_urut=>$l_save_to_field) {
							//echo $lrow_table["fk_data_type"]."aaa";
							//if($lrow_table["fk_data_type"]=='numeric'){
								
								if($l_numeric[$l_kd_module_detail][$l_table][$l_no_urut]["is_numeric"]=='t') {
									$l_arr_col[$l_no_urut]  = str_replace(',','',$l_arr_col[$l_no_urut]);
									$l_arr_col[$l_no_urut]  = str_replace('.00','',$l_arr_col[$l_no_urut]);
								}
								$l_arr_query[$l_kd_module_detail][$l_table].=(($l_arr_col[$l_no_urut] ||$l_arr_col[$l_no_urut]==0)?"'".$l_arr_col[$l_no_urut]."'":"null").",";
							//}else{
								//$l_arr_query[$l_kd_module_detail][$l_table].=(($l_arr_col[$l_no_urut])?"'".$l_arr_col[$l_no_urut]."'":"null").",";	
							//}
						}
						$l_arr_query[$l_kd_module_detail][$l_table]=substr($l_arr_query[$l_kd_module_detail][$l_table],0,-1).");";
					}				
				}				
			} else{ //buat delete kalo detailnya cuman ada satu
				foreach ($l_arr_detail as $l_kd_module_detail=>$l_arr_table){
					foreach ($l_arr_table as $l_table=>$l_arr_field){
						$parent_table=get_rec("skeleton.tbldb_table","parent_table","is_table_detail is true and kd_table='".$l_table."'");
						$l_fk_field=get_rec("skeleton.tbldb_table_detail","kd_field","is_foreign_key is true and fk_db_table='".$l_table."' and foreign_table='".$parent_table."'");
						//("delete from ".$l_table." where ".$l_fk_field."='".$id_edit."'");
						if (!pg_query("delete from ".$l_table." where ".$l_fk_field."='".$id_edit."'")) $l_success=0;
					}
				}
			}
		}		
		
		foreach ($l_arr_detail as $l_kd_module_detail=>$l_arr_table){
			foreach ($l_arr_table as $l_table=>$l_arr_field){
				
				if ($l_arr_query[$l_kd_module_detail][$l_table]) if(!pg_query($l_arr_query[$l_kd_module_detail][$l_table])) $l_success=0;
				$parent_table=get_rec("skeleton.tbldb_table","parent_table","is_table_detail is true and kd_table='".$l_table."'");
				
			}
		}
		$l_pk=get_rec("skeleton.tbldb_table_detail","kd_field","fk_db_table='".$parent_table."' and is_pk=true");
		if (!generate_log($parent_table,$id_edit,"detail",$l_pk,'t')) $l_success=0;

	}
	
	if (function_exists('save_additional')) {
		//echo "Edit Additional functions are available.<br />\n";
		save_additional();
	} 	
	
	if (function_exists('delete_additional')) {
	//echo "Edit Additional functions are available.<br />\n";
		delete_additional();
	}
	//$l_success=0;
	if ($l_success==1){
		$lrs_kosong=pg_query("select * from skeleton.tblmodule inner join skeleton.tblmodule_fields on skeleton.tblmodule.pk_id=fk_module where fk_menu='".$id_menu."' and is_edit is true");
		while($lrow_kosong=pg_fetch_array($lrs_kosong)){
			$_REQUEST[$lrow_kosong["kd_field"]]="";
			$_REQUEST[str_replace("[]","",$lrow_kosong["kd_field"]."1")]="";
			$_REQUEST[str_replace("[]","",$lrow_kosong["kd_field"]."2")]="";
		}
		$lrs_detail_kosong=pg_query("select * from skeleton.tblmodule_detail where fk_module='".$kd_module."'");
		while($lrow_detail_kosong=pg_fetch_array($lrs_detail_kosong)){
			$_REQUEST["strisi_".$lrow_detail_kosong["kd_module_detail"]]="";	
		}	
		$strisi1="";
		$strmsg=$nm_menu." Tersimpan.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		pg_query("COMMIT");
	}else{
		$strmsg.="Error :<br>".$nm_menu." Gagal Tersimpan.<br>";
		pg_query("ROLLBACK");
	}
}

function get_data(){
	global $kd_module,$id_edit,$id_menu;

	
	$lrs_tabel=pg_query("select no_urut_edit, kd_field,save_to_table,save_to_field,save_to_field_2,fk_data_type,type_list ,is_numeric from skeleton.tblmodule_fields
						where fk_module='".$kd_module."'
						order by no_urut_edit, save_to_table,save_to_field");
	while($lrow_table=pg_fetch_array($lrs_tabel)){
		if($lrow_table["save_to_table"]=="") {
			$lrow_table["save_to_table"]=$save_table_old;
		}
		$l_arr_table[$lrow_table["save_to_table"]][$lrow_table["kd_field"]]["save_to_field"]=$lrow_table["save_to_field"].(($lrow_table["save_to_field_2"])?",".$lrow_table["save_to_field_2"]:"");
		
		$l_arr_table[$lrow_table["save_to_table"]][$lrow_table["kd_field"]]["fk_data_type"]=(($lrow_table["fk_data_type"]=="list")?$lrow_table["type_list"]:$lrow_table["fk_data_type"]);
		$l_arr_table[$lrow_table["save_to_table"]][$lrow_table["kd_field"]]["is_numeric"]=$lrow_table["is_numeric"];
		
		$save_table_old=$lrow_table["save_to_table"];
	}
	$lrs_tabel=pg_query("select distinct save_to_table from skeleton.tblmodule_fields
						where fk_module='".$kd_module."'");
	while($lrow_table=pg_fetch_array($lrs_tabel)){
		$lrs_relation=pg_query("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."'  and value_type='reference' and fk_data_type='readonly'");
		//showquery("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."'  and value_type='reference' and fk_data_type='readonly'");
		
		while ($lrow_relation=pg_fetch_array($lrs_relation)) {
			if ($lrow_relation["reference_table_name"]=="sql_query") {
				$lrow_query=pg_fetch_array(pg_query("select * from skeleton.tblsql_query where kd_sql_query='".$lrow_relation["sql_query"]."'"));
				$l_table=$lrow_query["sql_query"];
			}else {
				$l_table=$lrow_relation["reference_table_name"];
			}
			$l_arr_relation[$lrow_table["save_to_table"]][$l_table]["field"].=$lrow_relation["reference_field_name"].",";
			$l_arr_relation[$lrow_table["save_to_table"]][$l_table]["expression"]=$lrow_relation["reference_expression"];
		}
	}
	//print_r($l_arr_table);
	$lrs_menu=pg_query("select list_sql from skeleton.tblmenu
				where pk_id='".$id_menu."'
				");
	$lrow_menu=pg_fetch_array($lrs_menu);
	$list_sql=$lrow_menu["list_sql"];
	foreach ($l_arr_table as $l_table=>$l_arr_field){
		//echo $l_table.'ccccccccccc';
		if (trim($l_table)) {	
			//showquery("select * from skeleton.tbldb_table_detail where fk_db_table='".$l_table."' and is_pk=true ")	;	
			$l_pk=get_rec("skeleton.tbldb_table_detail","kd_field","fk_db_table='".$l_table."' and is_pk=true");
			$l_arr_query[$l_table]="select * from ".$l_table;
			//echo $l_arr_query[$l_table];
			$l_counter_relation=1;
			if (is_array($l_arr_relation)) {
				foreach ($l_arr_relation[$l_table] as $l_join_table=>$l_arr_content) {	
					$l_arr_query[$l_table].=" left join ".$l_join_table." on ".$l_arr_content["expression"];
					$l_counter_relation++;
				}
			}
			
			//echo '121';
			$l_arr_query[$l_table]= $list_sql;// modif baru
			
			$l_arr_query[$l_table]= "select * from ( ".$list_sql." )as tblmain";// modif baru
			$l_arr_query[$l_table].= " where ".$l_pk."='".convert_sql($id_edit)."'";			
		 	$l_arr_query[$l_table]=str_replace("[","(",$l_arr_query[$l_table]);
		 	$l_arr_query[$l_table]=str_replace("]",")",$l_arr_query[$l_table]);
			$lrow=pg_fetch_array(pg_query($l_arr_query[$l_table]));
			//print_r($l_arr_field);
			//showquery($l_arr_query[$l_table]);
			foreach ($l_arr_field as $l_kd_field=>$l_save_to_field) {				
				if(strstr($l_save_to_field["save_to_field"],",")){
					$save_to_field=explode(",",$l_save_to_field["save_to_field"]);
					$l_field=$save_to_field[0];
				}else{
					$l_field=(($l_save_to_field["save_to_field"]=="")?$l_kd_field:$l_save_to_field["save_to_field"]);
				}
				//echo $lrow["end_of_day"];
				$l_kd_field=str_replace("[]","",$l_kd_field);
				//echo $lrow[$l_field]."<br>";
				if ($lrow[$l_field]!=NULL) {
					if ($l_save_to_field["fk_data_type"]=="range_date"){
						//echo $l_save_to_field["save_to_field"];
						$lvalue=explode(",",$l_save_to_field["save_to_field"]);	
						$_REQUEST[$l_kd_field."1"]=format_data($lrow[$lvalue[0]],"date","d/m/Y");
						$_REQUEST[$l_kd_field."2"]=format_data($lrow[$lvalue[1]],"date","d/m/Y");
					}else if($l_save_to_field["fk_data_type"]=="range_value"){
						$lrange_value=explode(",",$l_save_to_field["save_to_field"]);
						$_REQUEST[$l_kd_field."1"]=format_data($lrow[$lrange_value[0]],"d/m/Y");
						$_REQUEST[$l_kd_field."2"]=format_data($lrow[$lrange_value[1]],"d/m/Y");
						
					}else if($l_save_to_field["fk_data_type"]=="others"){
						$lrange_value=explode("-",$lrow[$l_save_to_field["save_to_field"]]);
						//echo $_REQUEST[$l_kd_field."1"];
						$_REQUEST[$l_kd_field."1"]=format_data($lrange_value[0],"d/m/Y");
						$_REQUEST[$l_kd_field."2"]=format_data($lrange_value[1],"d/m/Y");
						$_REQUEST[$l_kd_field."3"]=format_data($lrange_value[2],"d/m/Y"); 
					}else if($l_save_to_field["fk_data_type"]=="password"){
						$_REQUEST[$l_kd_field]="";
						//echo $l_kd_field."=".$_REQUEST[$l_kd_field]."<br>";
						//echo $lrow[$l_field];
						//echo $lrow[$l_field]." : ".$l_field." : ".$l_save_to_field["fk_data_type"]."<br>";
					}elseif($l_save_to_field["fk_data_type"]=="list_db") {
						$lrow_field=pg_fetch_array(pg_query("select * from skeleton.tblmodule_fields where fk_module='".convert_sql($kd_module)."' and kd_field='".convert_sql($l_kd_field)."'"));
						//showquery("select * from skeleton.tblmodule_fields where fk_module='".convert_sql($kd_module)."' and kd_field='".convert_sql($l_kd_field)."'");
						//showquery($lrow_field["list_sql"]." where ".$lrow_field["list_field_value"]."='".$lrow[$l_field]."'");
						if($lrow_field["list_field_value"]==$lrow_field["list_field_text"]){
							$_REQUEST[$l_kd_field]=$lrow[$l_field];
							//echo "aaa".$lrow[$l_field];						
						}
						else{
							if(strpos ($lrow_field["list_sql"],"where")>=1){
								if ($lrow_reference=pg_fetch_array(pg_query($lrow_field["list_sql"]." and ".$lrow_field["list_field_value"]."='".$lrow[$l_field]."'"))){
									$_REQUEST[$l_kd_field]=$lrow_reference[$lrow_field["list_field_text"]];
								}
		
							} else{
								if ($lrow_reference=pg_fetch_array(pg_query($lrow_field["list_sql"]." where ".$lrow_field["list_field_value"]."='".$lrow[$l_field]."'"))){
			
									$_REQUEST[$l_kd_field]=$lrow_reference[$lrow_field["list_field_text"]];
								}
							}
						}
									
					}else {
						if($l_save_to_field["is_numeric"]=="t")$l_save_to_field["fk_data_type"]="numeric";
						$_REQUEST[$l_kd_field]=format_data($lrow[$l_field],$l_save_to_field["fk_data_type"],"d/m/Y");
						//echo $l_save_to_field["fk_data_type"].'<br>';
					}
				}
			}
		}
	}
	
	$lrs_detail=pg_query("select * from skeleton.tblmodule_detail where fk_module='".convert_sql($kd_module)."'");
	//showquery("select * from skeleton.tblmodule_detail where fk_module='".convert_sql($kd_module)."'");
	while ($lrow_detail=pg_fetch_array($lrs_detail)){
		$lrs_tabel=pg_query("select kd_field,no_urut,save_to_table,save_to_field,fk_data_type,get_table_db,get_field_key,get_field_value,is_numeric,inner_join from skeleton.tblmodule_detail_fields where fk_module_detail='".$lrow_detail["pk_id"]."' order by no_urut");
		//showquery("select kd_field,no_urut,save_to_table,save_to_field,fk_data_type,get_table_db,get_field_key,get_field_value,inner_join from skeleton.tblmodule_detail_fields where fk_module_detail='".$lrow_detail["pk_id"]."' order by no_urut");
		while($lrow_table=pg_fetch_array($lrs_tabel)){
			if ($lrow_table["fk_data_type"]!="readonly" ||$lrow_table["save_to_field"]!="" ) {
					$l_arr_detail[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["no_urut"]]["save_to_field"]=$lrow_table["save_to_field"];
					$l_arr_detail[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["no_urut"]]["fk_data_type"]=$lrow_table["fk_data_type"];
					$save_to_table_default=$lrow_table["save_to_table"];
			} else {
				//showquery("select * from skeleton.tblmodule_detail_fields where fk_module_detail='".$lrow_detail["pk_id"]."' and data_reference='".$lrow_table["no_urut"]."'");
				if ($lrow_reference=pg_fetch_array(pg_query("select * from skeleton.tblmodule_detail_fields where fk_module_detail='".$lrow_detail["pk_id"]."' and data_reference like'%".$lrow_table["no_urut"]."%'"))) {
									
					$l_arr_detail[$lrow_detail["kd_module_detail"]][$lrow_reference["save_to_table"]][$lrow_table["no_urut"]]["save_to_field"]=$lrow_table["kd_field"];
					$l_arr_detail[$lrow_detail["kd_module_detail"]][$lrow_reference["save_to_table"]][$lrow_table["no_urut"]]["fk_data_type"]=$lrow_reference["fk_data_type"];
					$l_arr_detail[$lrow_detail["kd_module_detail"]][$lrow_reference["save_to_table"]][$lrow_table["no_urut"]]["is_numeric"]=$lrow_reference["is_numeric"];
				}else{
					$l_arr_detail[$lrow_detail["kd_module_detail"]][$save_to_table_default][$lrow_table["no_urut"]]["save_to_field"]=$lrow_table["kd_field"];
					$l_arr_detail[$lrow_detail["kd_module_detail"]][$save_to_table_default][$lrow_table["no_urut"]]["fk_data_type"]=$lrow_table["fk_data_type"];
					$l_arr_detail[$lrow_detail["kd_module_detail"]][$save_to_table_default][$lrow_table["no_urut"]]["is_numeric"]=$lrow_table["is_numeric"];
					$l_arr_detail_max[$lrow_detail["kd_module_detail"]]=$lrow_table["no_urut"];	
				}
				
			}
			if ($lrow_table["inner_join"]!="") {
				//echo $lrow_table["save_to_table"].'23432';
				$l_inner_join[$lrow_table["save_to_table"]]=$lrow_table["inner_join"];
			}
			if ($lrow_table["get_table_db"]) {
				$l_arr_relation[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["get_table_db"]]["get_field_key"]=$lrow_table["get_field_key"];
				$l_arr_relation[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["get_table_db"]]["get_foreign_key"]=$lrow_table["save_to_field"];
				$l_arr_relation[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["get_table_db"]]["inner_join"]=$lrow_table["inner_join"];
				$larr=split(",",$lrow_table["get_field_value"]);
				foreach ($larr as $l_index=>$l_value) {
					if (is_array($l_arr_relation[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["get_table_db"]]["get_field_value"])) {
						if (!in_array($l_value,$l_arr_relation[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["get_table_db"]]["get_field_value"])) {
							$l_arr_relation[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["get_table_db"]]["get_field_value"][count($l_arr_relation[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["get_table_db"]]["get_field_value"])]=$l_value;
						}
					}else {
						$l_arr_relation[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]][$lrow_table["get_table_db"]]["get_field_value"][count($l_arr_relation[$lrow_detail["kd_module_detail"]][$lrow_table["save_to_table"]]["get_field_value"])]=$l_value;
					}
				}
			}
			$l_arr_detail_max[$lrow_detail["kd_module_detail"]]=$lrow_table["no_urut"];
		}
	}
	//print_r($l_arr_detail);
	if ($l_arr_detail) {
		foreach ($l_arr_detail as $l_kd_module_detail=>$l_arr_table){
			//print_r($l_arr_table);
			
			foreach ($l_arr_table as $l_table=>$l_arr_field){
				//echo $l_inner_join[$l_table];
				$lrs=pg_query(_module_generate_detail_query($id_edit,$l_table,$l_arr_relation[$l_kd_module_detail])." ".$l_inner_join[$l_table]);
				//showquery(_module_generate_detail_query($id_edit,$l_table,$l_arr_relation[$l_kd_module_detail])." ".$l_inner_join[$l_table]);
				while ($lrow=pg_fetch_array($lrs)) {
					//$no_urut=$l_arr_detail_max[$l_kd_module_detail]+1;
					//echo $no_urut;
					$no_urut_field=0;
					for($l_no_urut=0;$l_no_urut<=$l_arr_detail_max[$l_kd_module_detail];$l_no_urut++) {
						//print_r ($l_arr_field);
						if ($lrow[$l_arr_field[$l_no_urut]["save_to_field"]] || $l_arr_field[$l_no_urut]["save_to_field"]) {
							$no_urut_field++;
						}
					}
					//echo $no_urut_field;
					$no_urut_field+=2;
					for($l_no_urut=0;$l_no_urut<=$l_arr_detail_max[$l_kd_module_detail];$l_no_urut++) {
						//echo $l_no_urut;
						//print_r ($l_arr_field);
						//echo $l_arr_field[$l_no_urut]["save_to_field"]."<br>"; 
						//echo $l_arr_field[$l_no_urut]["fk_data_type"]."<br>";
						if ($lrow[$l_arr_field[$l_no_urut]["save_to_field"]]) {
								//echo $lrow[$l_arr_field[$l_no_urut]["save_to_field"]];
								if($l_arr_field[$l_no_urut]["is_numeric"]=="t")$l_arr_field[$l_no_urut]["fk_data_type"]="numeric";
								$_REQUEST["strisi_".$l_kd_module_detail].=format_data($lrow[$l_arr_field[$l_no_urut]["save_to_field"]],$l_arr_field[$l_no_urut]["fk_data_type"],"m/d/Y").chr(187);
								 //echo $l_no_urut;
						}else if($l_arr_field[$l_no_urut]["save_to_field"]){
								$_REQUEST["strisi_".$l_kd_module_detail].=$lrow[$l_arr_field[$l_no_urut]["save_to_field"]].chr(187);
								 //echo $l_no_urut;
						}else{
							 
							 //echo $l_no_urut;
							 $_REQUEST["strisi_".$l_kd_module_detail].=$lrow[$no_urut_field].chr(187);
							 //echo $_REQUEST["strisi_".$l_kd_module_detail];
							// echo $no_urut_field;
							 //echo $lrow[$no_urut_field];
							 $no_urut_field++;
							
						}
					}
					//echo($_REQUEST["strisi_".$l_kd_module_detail]);
					$_REQUEST["strisi_".$l_kd_module_detail]=substr($_REQUEST["strisi_".$l_kd_module_detail],0,-1).chr(191);
				}
			}
		}
	}
}

function get_data_module(){
	global $kd_module,$j_action,$nm_tabs;
	
	//query untuk memunculkan tab 
	$lrow_tab_switch=pg_fetch_array(pg_query("select * from skeleton.tblmodule_tabs where fk_module='".$kd_module."' order by no_urut_tabs limit 1"));
	$nm_tabs=$lrow_tab_switch["kd_tabs"];
	$lrow_first_field=pg_fetch_array(pg_query("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' order by no_urut_edit limit 1"));
	$j_action.="document.form1.".$lrow_first_field["kd_field"].".focus();";
}
?>