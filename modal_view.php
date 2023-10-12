<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/module.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/file.inc.php';
require 'requires/input.inc.php';
require 'requires/numeric.inc.php';


$id_view=trim($_REQUEST["id_view"]);
$id_menu=trim($_REQUEST["id_menu"]);

$fk_module=get_rec("skeleton.tblmodule","pk_id","fk_menu='".$id_menu."'");
$fk_menu_union=get_rec("skeleton.tblmodule","fk_menu","fk_menu_union='".$id_menu."'");;

if($fk_menu_union!="" ||$fk_menu_union!=NULL){
	$lrs_tabel=pg_query("
		select 							
		kd_field,save_to_table,save_to_field,save_to_field_2,
		fk_data_type,type_list,is_multiple,is_numeric 
		from skeleton.tblmodule_fields
		where fk_module='".$fk_module."'
		order by no_urut_edit, save_to_table,save_to_field");
	$lrow_table=pg_fetch_array($lrs_tabel);
	$l_table=$lrow_table["save_to_table"];
	$l_pk=get_rec("skeleton.tbldb_table_detail","kd_field","fk_db_table='".$l_table."' and is_pk=true");
	
	$lrs_menu=pg_query("select list_sql from skeleton.tblmenu
			where pk_id='".$id_menu."'
			");
	$lrow_menu=pg_fetch_array($lrs_menu);
	$list_sql=$lrow_menu["list_sql"];
	
	if(!pg_num_rows(pg_query($list_sql." where ".$l_pk."='".$id_view."'"))){
		$id_menu=$fk_menu_union;	
	}

}
get_data_menu($id_menu);
get_data_module();

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
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src="js/tab.js.php?kd_module=<?=$kd_module?>&id_menu=<?=$id_menu?>"></script>
<script language='javascript' src="js/input_format_number.js.php"></script>
<script language='javascript'>
function fModal(pType,pID,pModule){
	switch (pType){
		case "view":
		show_modal('modal_view.php?id_menu='+pModule+'&pstatus=view&id_view='+pID,'status:no;help:no;dialogwidth:900px;dialogheight:545px;')
		break;
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
		case "view_detail_payment":
			show_modal('modal_view_payment.php?pstatus=view&id_edit='+pID,'dialogwidth:900px;dialogheight:545px')
		break;
	}
}


function fLoad(){
	if('<?=get_rec("skeleton.tblmodule","is_tab","fk_menu='".$id_menu."'")?>'=='t'){
		fSwitchView('div<?=$nm_tabs?>',document.getElementById('menuBar').children[0])	
	}
	//parent.parent.document.title="Karyawan";
}
</script>
<body onLoad="fLoad()" bgcolor="#fafafa">
<form action="modal_view.php" method="post" name="form1">
<input type="hidden" name="id_menu" value="<?=$id_menu?>">
<input type="hidden" name="id_view" value="<?=$id_view?>">
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
			<? _module_create_header_view("edit",false)?>
	  	</td>
	</tr>
    <tr>
      	<td class="border">
			<? $fk_module=get_rec("skeleton.tblmodule","pk_id","fk_menu='".$id_menu."'");
			if(!pg_num_rows(pg_query("select * from skeleton.tblmodule_fields where fk_module='".$fk_module."' and fk_data_type='table_detail'"))){			
			_module_create_detail_view('true');			
			}?>
	  	</td>
    </tr>
<? if($nm_menu=="Customer"){//KHUSUS MUNCULIN GAMBAR DI CUSTOMER ?>    
    <tr>
      	<td class="border" bgcolor='#efefef' align="center">
<? 	
		$pic=get_rec("tblcustomer","pic_cust","no_cif ='".$id_view."'");
		$pic1=get_rec("tblcustomer","pic_id","no_cif ='".$id_view."'");
		
		if($pic=="" || $pic=="NULL"){
?>        
		 <img src="images/customer_default.png" width="100" height="100"/>
        <img src="images/customer_default.png" width="100" height="100"/>
<? 
		}else{
?>
        <img src="file_read.php?file=<?=$pic?>&name=<?=$pic?>" width="300" height="150"/>
        <img src="file_read.php?file=<?=$pic1?>&name=<?=$pic1?>" width="300" height="150"/>
	  	</td>
    </tr>

<? 		}
	}else if($nm_menu=="Inventory"){ ?>
  	<tr>
      	<td class="border" bgcolor='#efefef' align="center">
<? 		
		$no_fatg=get_rec("viewkontrak","fk_fatg","no_sbg='".$id_view."' ");
		if(pg_num_rows(pg_query("select * from data_gadai.tbltaksir where no_fatg='".$no_fatg."' ")))$tbl="data_gadai.tbltaksir";
		else $tbl="data_gadai.tbltaksir_umum";
		$pic=get_rec($tbl,"pic_brg","no_fatg ='".$no_fatg."'");
?>
		<img src="file_read.php?file=<?=$pic?>&name=<?=$pic?>" width="300" height="150"/>
        </td>
    </tr>
<? }else if($nm_menu=='Approval Gadai'||$nm_menu=='Approval Cicilan'){ ?>    
    <tr bgcolor="efefef">          
    <? 
    if(pg_num_rows(pg_query("select * from data_gadai.tbltaksir where no_fatg='".$_REQUEST["fk_fatg"]."' ")))$tbl="data_gadai.tbltaksir";
    else $tbl="data_gadai.tbltaksir_umum";
    
   // history($tbl);
		

    ?>          
    </tr>
    
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

<? }?>

</table>
<!-- end content begin -->

<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" align="center">
	<tr height="20">
    	<td align="center" bgcolor="#D0E4FF">  
<?
		if(strstr($nm_menu,'Cicilan') && !strstr($nm_menu,'Pembayaran') && !strstr($nm_menu,'Pelunasan')&& !strstr($nm_menu,'Tagihan') ){
		$tbl="data_gadai.tbltaksir_umum";
		$id_menu_taksir='20170900000052';

?>           
        &nbsp;<input class="groove_button" type='button' name="btnview" value='View Data Permohonan' onClick="fModal_view('view_taksir','<?=$_REQUEST["fk_fatg"]?>','<?=$id_menu_taksir?>','10101011');">      
<?
		}
?>
<?		
		if(strstr(strtoupper($nm_menu),'HUTANG')){

?>           
        &nbsp;<input class="groove_button" type='button' name="btnview" value='View Detail' onClick="fModal_view('view_detail_payment','<?=$_REQUEST["no_batch"]?>');">      
<?
		}
?>
        
          
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
            <td align="center" colspan="10" class="judul_menu">DATA PENAKSIRAN</td>
        </tr>
       <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
            <td align="center">No</td>       
            <td align="center">Kode Barang</td>
            <td align="center">Keterangan Barang</td>
            <td align="center">Karat</td>
            <td align="center">Berat Kotor</td>
            <td align="center">Berat Bersih</td>
            <td align="center">Nilai Taksir</td>            
        </tr>        

<?
	$no=1;
	while($lrow=pg_fetch_array($l_res)){		
		
	?>	
        <tr style="padding:0 5 0 5" height="20" bgcolor='#efefef'>
            <td style="padding:0 5 0 5" ><?=$no?></td>        
            <td style="padding:0 5 0 5" ><?=$lrow['fk_barang']?></td>
            <td style="padding:0 5 0 5" ><?=$lrow['keterangan_barang']?></td>
            <td style="padding:0 5 0 5" ><?=$lrow['karat']?></td>
            <td style="padding:0 5 0 5" ><?=$lrow['berat_kotor']?></td>
            <td style="padding:0 5 0 5" ><?=$lrow['berat_bersih']?></td>
            <td style="padding:0 5 0 5" align="right"><?=convert_money('',$lrow['nilai_taksir'])?></td>
        </tr>
	<?	
		$no++;
	}
?>
	</table>	
<?	
}
function get_data(){
	global $id_view,$kd_module,$id_menu;
	$lrs_tabel=pg_query("select 	
						kd_field,save_to_table,save_to_field,save_to_field_2,fk_data_type,type_list,is_multiple,is_numeric from skeleton.tblmodule_fields
						where fk_module='".$kd_module."'
						order by no_urut_edit, save_to_table,save_to_field");
	while($lrow_table=pg_fetch_array($lrs_tabel)){
		if($lrow_table["save_to_table"]=="") {
			$lrow_table["save_to_table"]=$save_table_old;
		}
		$l_arr_table[$lrow_table["save_to_table"]][$lrow_table["kd_field"]]["save_to_field"]=$lrow_table["save_to_field"].(($lrow_table["save_to_field_2"])?",".$lrow_table["save_to_field_2"]:"");
		$l_arr_table[$lrow_table["save_to_table"]][$lrow_table["kd_field"]]["fk_data_type"]=(($lrow_table["fk_data_type"]=="list")?$lrow_table["type_list"]:$lrow_table["fk_data_type"]);
		$l_arr_table[$lrow_table["save_to_table"]][$lrow_table["kd_field"]]["is_multiple"]=$lrow_table["is_multiple"];
		$save_table_old=$lrow_table["save_to_table"];
		$l_arr_table[$lrow_table["save_to_table"]][$lrow_table["kd_field"]]["is_numeric"]=$lrow_table["is_numeric"];

	}
	//print_r($l_arr_table);
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

			$l_arr_query[$l_table].= " where ".$l_pk."='".convert_sql($id_view)."'";
			$l_arr_query[$l_table]=str_replace("[","(",$l_arr_query[$l_table]);
		 	$l_arr_query[$l_table]=str_replace("]",")",$l_arr_query[$l_table]);
			//showquery($l_arr_query[$l_table]);
			$lrow=pg_fetch_array(pg_query($l_arr_query[$l_table]));
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
							if ($lrow_reference=pg_fetch_array(pg_query("select * from (".$lrow_field["list_sql"].")as tbl where ".$lrow_field["list_field_value"]."='".$lrow[$l_field]."'"))){
		
								$_REQUEST[$l_kd_field]=$lrow_reference[$lrow_field["list_field_text"]];
							}
						}
					}
				} elseif ($l_save_to_field["fk_data_type"]=="list_manual" && $l_save_to_field["is_multiple"]=="f") {
					$lrow_field=pg_fetch_array(pg_query("select * from skeleton.tblmodule_fields where fk_module='".convert_sql($kd_module)."' and kd_field='".convert_sql($l_kd_field)."'"));
				//	showquery("select * from skeleton.tblmodule_fields where fk_module='".convert_sql($kd_module)."' and kd_field='".convert_sql($l_kd_field)."'");
					$l_arr_list_manual_value=split(",",$lrow_field["list_manual_value"]);
					//print_r($l_arr_list_manual_value);
					//echo $lrow_field["list_manual_value"].$l_save_to_field["save_to_field"];
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
					if($l_save_to_field["is_numeric"]=="t")$l_save_to_field["fk_data_type"]="numeric";
					if ($lrow[$l_field]) $_REQUEST[$l_kd_field]=format_data_view($lrow[$l_field],$l_save_to_field["fk_data_type"],"d/m/Y");
					//$l_kd_field;
					else if($lrow[$l_field]=="0") $_REQUEST[$l_kd_field]=format_data_view($lrow[$l_field],$l_save_to_field["fk_data_type"],"d/m/Y");
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
	$lrow_first_field=pg_fetch_array(pg_query("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' order by no_urut_add limit 1"));
	$j_action="document.form1.".$lrow_first_field["kd_field"].".focus();";
}