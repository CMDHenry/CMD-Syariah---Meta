<?
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/convert.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/referer_check.inc.php';
require '../classes/recordset_ajax.class.php';
require '../requires/numeric.inc.php';

if (!mRefererCheck("modal_get.php") || !isset($_SESSION['id'])){
//	echo "err:1000";
} else{
	$module=$_REQUEST["module"];
	$p_id=$_REQUEST["p_id"];
	$pk_id_module=$_REQUEST["pk_id_module"];
	$row=pg_fetch_array(pg_query("select * from skeleton.tblmenu where pk_id='".convert_sql($module)."'"));
	//showquery("select * from skeleton.tblmenu where pk_id='".convert_sql($module)."'");
	//showquery($row["list_sql"]);
	if($row["list_sql"]!=""){
		//$field=pg_field_name(pg_query($row["list_sql"]),0);
	}
	//showquery($row["list_sql"]);
	$id_field=$_REQUEST["id_field"];
	//echo $id_field;
	$id_detail_field=$_REQUEST["id_detail_field"];
	$option_name=$_REQUEST["option_name"];//where tambahan sesuai data header
	$kode=$_REQUEST["kode"];
	$page=$_REQUEST["page"];
	//echo $option_name.'sdfds';
	if($option_name!="undefined" && $option_name!=NULL){
		if($option_name=="bank_cek_ke_cabang"){						
			$where_add=" kd_cabang in (select fk_cabang from tblcabang_detail_bank where fk_bank='$kode') ";
		}elseif($option_name=="cabang_cek_ke_bank"){						
			$where_add=" kd_bank in (select fk_bank from tblcabang_detail_bank where fk_cabang='$kode') ";
		}elseif($option_name=="cabang_cek_unit_pos"){						
			$where_add=" 
				kd_cabang in 
				(
					select kd_cabang from tblcabang where (head_cabang='$kode' or head_unit='$kode') and kd_cabang!='$kode'
					union 
					select head_cabang from tblcabang where kd_cabang='$kode' and head_cabang not in('$kode','".cabang_ho."')
					union
					select head_unit from tblcabang where kd_cabang='$kode' and head_unit not in('$kode','".cabang_ho."')
				) 
			";			
		}elseif($option_name=="mutasi_bank_wilayah_ke_cabang"){	
			$kode=split('_',$kode);
			$where_add=" kd_cabang in (select fk_cabang from tblcabang_detail_bank where fk_bank='$kode[0]')" ;
			if($kode[1])$where_add.="  and kd_cabang in (".get_list_cabang_pt($kode[1]).")  ";						
		}elseif($option_name=="wilayah_cek_cabang"){						
			$where_add=" kd_cabang in (".get_list_cabang_pt($kode).")";
		}elseif($option_name=="fk_jenis_barang_grade"){
			$kode=explode('_',$kode);
			$where_add=" fk_jenis_barang ='".$kode[0]."' and fk_grade='".$kode[1]."'";
		}elseif($option_name=="fk_produk"){
			$kode=explode('_',$kode);
			$where_add=" status_barang ='".$kode[0]."' and kategori='".$kode[1]."'";
		}
		else $where_add=" upper($option_name)=upper('$kode')";
	}
	
	$strorderby=$_REQUEST["strorderby"];
	if($strorderby=="") $strorderby=get_rec("skeleton.tblmodule_fields","save_to_field","fk_module='".$pk_id_module."'","no_urut_edit");
	//echo "a".$strorderby;
	$strordertype=$_REQUEST["strordertype"];
	if($strordertype=="") $strordertype="asc";
	if($row["list_sql"]!=""){
		create_recordset();
	}

	
	?>
	<table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
		<tr height="20">
			<td>
				<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border">
					<tr bgcolor="#D0E4FF">
						<td>
							<? create_button_non_item()?>
                            <? 
							if($option_name=='fk_jenis_barang' || $option_name=='fk_jenis_barang_grade'){//untuk pengajuan barang baru
							?>
                            &nbsp;<input type="button" value=" New Barang" class="groove_button" onClick="show_modal('modal_request_barang_ho.php?id_menu=<?=$module?>&pstatus=view','status:no;help:no;dialogwidth:900px;dialogheight:545px;','')">
                            <?
							}
							?>
						</td>
						<td align="right">
							<? if($recordset->page>1) {?>
							<a href="#" onClick="fGoPage(1)"><<</a>
							&nbsp;&nbsp;<a href="#" onClick="fPage(-1)"><</a>&nbsp;&nbsp;
							<? }?>
							<?=$recordset->page?> of <?=$recordset->total_page?>&nbsp;&nbsp;
							<? if($recordset->page<$recordset->total_page) {?>
							<a href="#" onClick="fPage(+1)">></a>
							&nbsp;&nbsp;<a href="#" onClick="fGoPage(<?=$recordset->total_page?>)">>></a>&nbsp;&nbsp;
							<? }?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td bgcolor="#f8f8f8" class="border">
				<div id="divContentInner" style='width:100%;height:100%;overflow:auto'>
				<table cellpadding="0" cellspacing="1" border="0" width="100%">
					<tr bgcolor="#c8c8c8" class="header" height="15">
                        <?=create_list_title()?>
						
					</tr>
					<?
						create_list_data();
					?>
				</table>
				</div>
			</td>
		</tr>
        <tr height="20">
			<td bgcolor="#D0E4FF" class="border">
				&nbsp;&nbsp;&nbsp;<input type="button" name="btnpilih" value="Pilih" class="groove_button" onClick="fPilih()">
			</td>
		</tr>
	</table>	
<?	
}
function fascdesc($p_order){
	global $strorderby,$strordertype;
	if($strorderby==$p_order){
		if($strordertype=="asc") echo "<img src='images/asc.gif'>";
		else echo "<img src='images/desc.gif'>";
	}
}

function create_recordset(){
	global $module,$pk_id_module,$recordset,$strorderby,$strordertype,$page,$active_field, $id_field,$id_detail_field,$where_add;
	
	$lwhere="";
	$lrs=pg_query("select * from skeleton.tblmodule_fields where is_search='t' and fk_module='".$pk_id_module."' order by no_urut_add");
	while ($lrow=pg_fetch_array($lrs)) {
		
		if($lrow["fk_data_type"]=="checkbox_list"){
				$lrow["kd_field"]=str_replace("]","",(str_replace("[","",$lrow["kd_field"])));
		}
		
		if($lrow["fk_data_type"]=='range_value'){
			if ($_REQUEST[$lrow["kd_field"]."1"]!="" && $_REQUEST[$lrow["kd_field"]."2"]=="") {
				if ($lwhere!="") $lwhere.=" and";
				$lwhere.=" (".$lrow["save_to_field"]." <= '".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]."1"]))."' and";
				$lwhere.=" ".$lrow["save_to_field_2"]." >= '".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]."1"]))."')";
			} else if ($_REQUEST[$lrow["kd_field"]."2"]!="" && $_REQUEST[$lrow["kd_field"]."1"]=="") {
				if ($lwhere!="") $lwhere.=" and";
				$lwhere.=" (".$lrow["save_to_field"]." <= '".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]."2"]))."' and";
				$lwhere.=" ".$lrow["save_to_field_2"]." >= '".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]."2"]))."')";
			} else if($_REQUEST[$lrow["kd_field"]."1"]!="" && $_REQUEST[$lrow["kd_field"]."2"]!=""){
				if ($lwhere!="") $lwhere.=" and";
				$lwhere.=" (".$lrow["save_to_field"]." >= ".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]."1"]))." and";
				$lwhere.=" ".$lrow["save_to_field"]." <= ".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]."2"]))." or";
				$lwhere.=" ".$lrow["save_to_field_2"]." <= ".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]."2"]))." and";
				$lwhere.=" ".$lrow["save_to_field_2"]." >= ".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]."1"]))." or";
				$lwhere.=" ".$lrow["save_to_field"]." <= ".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]."1"]))." and";
				$lwhere.=" ".$lrow["save_to_field_2"]." >= ".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]."2"])).")";
			}
		} else if($lrow["fk_data_type"]=='range_date'){
				if ($_REQUEST[$lrow["kd_field"]."1"]!="" && $_REQUEST[$lrow["kd_field"]."2"]=="") {
				if ($lwhere!="") $lwhere.=" and";
				$lwhere.=" (".$lrow["save_to_field"]." <= '".convert_sql(strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]."1"])))."' and";
				$lwhere.=" ".$lrow["save_to_field_2"]." >= '".convert_sql(strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]."1"])))."')";
			} else if ($_REQUEST[$lrow["kd_field"]."2"]!="" && $_REQUEST[$lrow["kd_field"]."1"]=="") {
				if ($lwhere!="") $lwhere.=" and";
				$lwhere.=" (".$lrow["save_to_field"]." <= '".convert_sql(strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]."2"])))."' and";
				$lwhere.=" ".$lrow["save_to_field_2"]." >= '".convert_sql(strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]."2"])))."')";
			} else if($_REQUEST[$lrow["kd_field"]."1"]!="" && $_REQUEST[$lrow["kd_field"]."2"]!=""){
				if ($lwhere!="") $lwhere.=" and";
				$lwhere.=" (".$lrow["save_to_field"]." >= '".convert_sql(strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]."1"])))."' and";
				$lwhere.=" ".$lrow["save_to_field"]." <= '".convert_sql(strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]."2"])))."' or";
				$lwhere.=" ".$lrow["save_to_field_2"]." <= '".convert_sql(strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]."2"])))."' and";
				$lwhere.=" ".$lrow["save_to_field_2"]." >= '".convert_sql(strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]."1"])))."' or";
				$lwhere.=" ".$lrow["save_to_field"]." <= '".convert_sql(strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]."1"])))."' and";
				$lwhere.=" ".$lrow["save_to_field_2"]." >= '".convert_sql(strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]."2"])))."')";
			}
		} else if($lrow["fk_data_type"]=='date'){
			if($_REQUEST[$lrow["kd_field"]]){
				$date_search=explode("/",strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]])));
				$date_search_use=$date_search[2]."-".$date_search[0]."-".$date_search[1];
				if ($lwhere!="") $lwhere.=" and";
				$lwhere.=" upper(".$lrow["kd_field"].") like upper('%".convert_sql($date_search_use)."%')";
			}
		}elseif ($lrow["fk_data_type"]=='list'){
			if ($_REQUEST[$lrow["kd_field"]]!="") {
				if ($lwhere!="") $lwhere.=" and";
				$lwhere.=" upper(".$lrow["kd_field"].") = upper('".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]]))."')";
			}
		}else{
			if ($_REQUEST[$lrow["kd_field"]]!="") {
				if ($lwhere!="") $lwhere.=" and";
				$lwhere.=" upper(".$lrow["kd_field"].") like upper('%".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]]))."%')";
			}
		}
	}	
	
	//buat detail filter cabang, ikut dari header
	if($id_detail_field=="20171000000069"){
		 $id_field="20171100000007";
	}
	
	//echo $id_field;
	//filter cabang di header
	if(get_rec("skeleton.tblmodule_fields","field_fk_dealer","pk_id='".$id_field."'")!="" && $_SESSION["kd_cabang"]){
		if ($lwhere!="") $lwhere.=" and";
		$lwhere.=" ".get_rec("skeleton.tblmodule_fields","field_fk_dealer","pk_id='".$id_field."'")." = '".$_SESSION["kd_cabang"]."'";
		//khusus buat LOV produk karena ngeceknya ke produk detail
		if($module=="20171000000004"){
			$fk_area=get_rec("tblcabang","fk_area","kd_cabang='".$_SESSION["kd_cabang"]."'");
			$fk_wilayah=get_rec("tblcabang","fk_wilayah","kd_cabang='".$_SESSION["kd_cabang"]."'");
			$lwhere2.=" and (fk_wilayah='".$fk_wilayah."' or fk_grade='".$fk_grade."' or fk_cabang='".$_SESSION["kd_cabang"]."' or is_all_cabang is true)";
		}
	}
	
	//khusus buat LOV produk karena ngeceknya ke produk detail
	//&& $_SESSION["jenis_user"]!='HO'
	if($module=="20171000000004" ){
		//$fk_area=get_rec("tblcabang","fk_area","kd_cabang='".$_SESSION["kd_cabang"]."'");
//		$fk_wilayah=get_rec("tblcabang","fk_wilayah","kd_cabang='".$_SESSION["kd_cabang"]."'");
//		$lwhere2.=" and (fk_wilayah='".$fk_wilayah."' or fk_grade='".$fk_grade."' or fk_cabang='".$_SESSION["kd_cabang"]."' or is_all_cabang is true)";
		$lwhere2.=" and 
		case when kd_produk in(select fk_produk from tblproduk_detail_wilayah) 
		then kd_produk||'".$_SESSION["kd_cabang"]."' in (select fk_produk||kd_cabang from tblproduk_detail_wilayah left join tblcabang on tblproduk_detail_wilayah.fk_wilayah=tblcabang.fk_wilayah where kd_cabang='".$_SESSION["kd_cabang"]."')
		else true			
		end and
		
		case when kd_produk in(select fk_produk from tblproduk_detail) 
		then kd_produk||'".$_SESSION["kd_cabang"]."' in (select fk_produk||fk_cabang from tblproduk_detail where fk_cabang='".$_SESSION["kd_cabang"]."')
		else true			
		end
		";
	}
	//echo $module. "--" .$_SESSION["jenis_user"];
	// filter tambahan pemisah or
	if(get_rec("skeleton.tblmodule_fields","condition_where","pk_id='".$id_field."'")!="" ){
		if(get_rec("skeleton.tblmodule_fields","condition_type","pk_id='".$id_field."'")=='or'){
			if ($lwhere!="") $lwhere.=" or";
		}else {
			if ($lwhere!="") $lwhere.=" and";	
		}
		
		$lwhere.=" ".get_rec("skeleton.tblmodule_fields","condition_where","pk_id='".$id_field."'");		
	}
	// filter utk get di detail
	//echo $id_detail_field;
	
	//biar coa bisa all
	if($id_detail_field=="20180200000016" && $_SESSION["jenis_user"]=='HO'){
		$id_detail_field="20180400000003";
	}
	
		
	if(get_rec("skeleton.tblmodule_detail_fields","condition_where","pk_id='".$id_detail_field."'")!=""){
		if ($lwhere!="") $lwhere.=" and";
		$lwhere.=" (".get_rec("skeleton.tblmodule_detail_fields","condition_where","pk_id='".$id_detail_field."'").")";		
	}
	

	
	//filter di detail ambil parameter dari header
	if($where_add){
		if ($lwhere!="") $lwhere.=" and";
		$lwhere.=" ".$where_add;		
	}

	if ($lwhere!="") $lwhere=" where ".$lwhere;
	//eval("\$condition_where = \"$condition_where\";");
	
	$lrow=pg_fetch_array(pg_query("select * from skeleton.tblmenu where pk_id='".$module."'"));
	// $condition_where
	
	// list sql manual
	if(get_rec("skeleton.tblmodule_fields","list_sql","pk_id='".$id_field."'")!=""){
		$lrow["list_sql"]=get_rec("skeleton.tblmodule_fields","list_sql","pk_id='".$id_field."'");
	}
	
	$lquote="select * from (
				".$lrow["list_sql"]." 
				".$lwhere2." 
			) as tblmain
			".$lwhere." 
			order by ".$strorderby." ".$strordertype;	
	//showquery($lquote);	
	$recordset = new recordset("",$lquote,$page,20);
	
}
function create_list_title(){
	global $pk_id_module;
	
	$lrs=pg_query("
	select * from(
		select *,
		case 
		when no_urut_list is not null then no_urut_list 
		else no_urut_edit end as no_urut from skeleton.tblmodule_fields 
		where fk_module='".$pk_id_module."' and is_view='t' 
	)as tblmain	
	order by no_urut");
	
	?><td></td>
    <?
	while ($lrow=pg_fetch_array($lrs)) {
?>
		<td><a href="#" onClick="fOrder('<?=$lrow["save_to_field"]?>')"><?=$lrow["nm_field"]?></a><? fascdesc($lrow["save_to_field"])?></td>
<?
	}
}
	
function create_list_data(){
	global $recordset,$module,$pk_id_module,$p_id;
	//echo $fk_kelurahan;
	$l_index=0;
	$lrow_field_pk=pg_fetch_array(pg_query("select * from (
		select * from skeleton.tblmodule_fields where fk_module='".$pk_id_module."'
	) as tblmodule_field
	inner join (
		select * from skeleton.tbldb_table_detail where is_pk='t'
	) as tbldb_table_detail on fk_db_table=save_to_table and tbldb_table_detail.kd_field=save_to_field"));
	if($recordset){
		$lrs = $recordset->get_recordset();
		//showquery($lrs);
		while ($lrow=pg_fetch_array($lrs)){
	?>
	
			<tr bgcolor='#e0e0e0' onmouseover="fTRColor(this,'over')" onmouseout="fTRColor(this,'out')">
			<td align="center"><input type="radio" name="r_id" class="groove_checkbox" value="<?=$lrow[$lrow_field_pk["kd_field"]]?>" onClick="fChecked(this)" ondblclick="fChecked(this);fPilih()" <?=(($p_id==$lrow[$lrow_field_pk["kd_field"]])?"checked":"")?>></td>
	<?
			$lrs_field=pg_query("
			select * from(
				select *,
				case 
				when no_urut_list is not null then no_urut_list 
				else no_urut_edit end as no_urut from skeleton.tblmodule_fields 
				left join (select use_modal_view_name, pk_id as pk_id_module from skeleton.tblmodule)as tblmodule on fk_module=pk_id_module
				where fk_module='".$pk_id_module."' and is_view='t' 
			)as tblmain	
			order by no_urut");
			
			while ($lrow_field=pg_fetch_array($lrs_field)) {
				//echo $lrow_field["kd_field"]."ccc";
				if($lrow_field["is_numeric"]=="t" )$lrow_field["fk_data_type"]="numeric" ;
	?>	
					
					<? if ($lrow_field["kd_field"]==$lrow_field_pk["kd_field"]) {
						 if($lrow_field["use_modal_view_name"]!=''&&$lrow_field["use_modal_view_name"]!=NULL)$view='view_custom';
						 else $view='view';
						?>
						<td style="padding:0 5 0 5">
							<a href="#" class="blue" onClick="fModal('<?=$view?>','<?=$lrow[$lrow_field["save_to_field"]]?>','<?=$lrow_field["use_modal_view_name"]?>')"><?=$lrow[$lrow_field["save_to_field"]]?></a>
						</td>
					<? } else if($lrow_field["fk_data_type"]=="date" || $lrow_field["fk_data_type"]=="timestamp"){ ?>
						<td style="padding:0 5 0 5" align="center">
							<?	
							if($lrow[$lrow_field["save_to_field"]]!=""){
								$date=split(" ",$lrow[$lrow_field["save_to_field"]]);
								$date1=split("-",$date[0]);			
								if((int)($date1[0])<1970 || (int)($date1[0])>2099){ 
									$date2=$date1[2]."/".$date1[1]."/".$date1[0]; 
									$dates=$date2;
								}else{  
									$dates=date("d/m/Y",strtotime($lrow[$lrow_field["save_to_field"]]));
								}
							}
							else $dates="";
							//echo $dates;
							?>
							<?=(($dates=="" || $dates==NULL)?"":$dates)?>
						</td>
						
					<? } else if($lrow_field["fk_data_type"]=="file"){ ?>
						<td style="padding:0 5 0 5" align="center">
							<a href="#" class="blue" onclick="fModal('view_pic','<?=$lrow[$lrow_field["save_to_field"]]?>')"><?=$lrow[$lrow_field["save_to_field"]]?></a>
						</td>    
					<? } else if($lrow_field["fk_data_type"]=="range_value"){ ?>
						<td style="padding:0 5 0 5" align="center">
							<?=$lrow[$lrow_field["save_to_field"]]?> - <?=$lrow[$lrow_field["save_to_field_2"]]?>
						</td>	
					<? } else if($lrow_field["fk_data_type"]=="range_date"){ ?>
						<td style="padding:0 5 0 5" align="center">
							<?=date("d/m/Y",strtotime($lrow[$lrow_field["save_to_field"]]))?> - <?=date("d/m/Y",strtotime($lrow[$lrow_field["save_to_field_2"]]))?>
						</td>
					<? } else if($lrow_field["save_to_field"]=="" && $lrow_field["fk_data_type"]=="readonly" && $lrow_field["reference_table_name"]!="sql_query"){?>	
						<td style="padding:0 5 0 5" align="center">
							 <?
							 $fk_id = explode("=", $lrow_field["reference_expression"]);
							$value_view = get_rec("".$lrow_field["reference_table_name"]."","".$lrow_field["reference_field_name"]."","$fk_id[0]='".$lrow["".$fk_id[1].""]."'");
							 echo $value_view;
							 ?>
						</td>        
					<? } else if($lrow_field["fk_data_type"]=="checkbox"){?>	
						<td style="padding:0 5 0 5" align="center">
							<img src="./images/<?=(($lrow[$lrow_field["save_to_field"]]=='t')?"true":"false")?>.gif">
						</td>
						
					<? } else if($lrow_field["save_to_field"]=="" && $lrow_field["fk_data_type"]=="readonly" && $lrow_field["reference_table_name"]!="sql_query"){?>	
						<td style="padding:0 5 0 5" align="center">
							 <?
							$fk_id = explode("=", $lrow_field["reference_expression"]);
							$value_view = get_rec("".$lrow_field["reference_table_name"]."","".$lrow_field["reference_field_name"]."","$fk_id[0]='".$lrow["".$fk_id[1].""]."'");
							 echo $value_view;
							 ?>
						</td>
						<? } else if($lrow_field["save_to_field"]=="" && $lrow_field["fk_data_type"]=="readonly" && $lrow_field["reference_table_name"]=="sql_query"){?>	
						<td style="padding:0 5 0 5" align="center">
							 <?
							$fk_id = explode("=", $lrow_field["reference_expression"]);
							$lrs_field_reference=(pg_query("select sql_query from skeleton.tblsql_query where kd_sql_query='".$lrow_field["sql_query"]."'")); //ambil sql_query dari tabel sql_query
							while ($lrow_field_reference=pg_fetch_array($lrs_field_reference)){
								 $fk_test=explode("as tbl",$lrow_field_reference["sql_query"]);
								 $fk_test=str_replace("[","",$fk_test);
								 $fk_test=str_replace("]","",$fk_test);
								 //showquery($fk_test[0]."where ".$fk_id[0]."='".$lrow["".$fk_id[1].""]."'");
								 $lrs_test=pg_query($fk_test[0]."where ".$fk_id[0]."='".$lrow["".$fk_id[1].""]."'"); //untuk ambil field yang di select pada fungsi SQL QUERY
								 while($lrow_test=pg_fetch_array($lrs_test)){
									echo $lrow_test[$lrow_field["kd_field"]]; //menampilkan value dari foreign id
								 }
							}
							?>
						</td>
					<? } else if($lrow_field["fk_data_type"]=="range_value"){ ?>
						<td style="padding:0 5 0 5" align="center">
							<?=$lrow[$lrow_field["save_to_field"]]?> - <?=$lrow[$lrow_field["save_to_field_2"]]?>
						</td>	
					<? } else if($lrow_field["fk_data_type"]=="range_date"){ ?>
						<td style="padding:0 5 0 5" align="center">
							<?=date("d/m/Y",strtotime($lrow[$lrow_field["save_to_field"]]))?> - <?=date("d/m/Y",strtotime($lrow[$lrow_field["save_to_field_2"]]))?>
						</td>	
					<? } else if($lrow_field["fk_data_type"]=="list" && $lrow_field["type_list"]=="list_db"){?>	
						<td style="padding:0 5 0 5" align="center">
							 <?
							 $get_list_db=explode(" ", $lrow_field["list_sql"]);
							 $value_list_db = get_rec("".$get_list_db[3]."","".$lrow_field["list_field_text"]."","".$lrow_field["list_field_value"]."='".$lrow[$lrow_field["save_to_field"]]."'");
							 //showquery("select ".$lrow_field["list_field_text"]." from ".$get_list_db[3]." where ".$lrow_field["list_field_value"]."='".$lrow[$lrow_field["save_to_field"]]."'");
							 echo $value_list_db;
							 ?>
						</td>
			<? }else if($lrow_field["fk_data_type"]=="numeric"){  ?>
					   <td style="padding:0 5 0 5" align="right">
							<?=convert_money("",$lrow[$lrow_field["save_to_field"]],0)?>
					   </td>
					<? 
				}else if($lrow_field["fk_data_type"]=="list" && $lrow_field["type_list"]=="list_manual"){
						$list_manual_text = explode(",", $lrow_field["list_manual_text"]);
						$list_manual_value = explode(",", $lrow_field["list_manual_value"]);
						for($i=0;$i<count($list_manual_value);$i++){
							if($lrow[$lrow_field["save_to_field"]]==$list_manual_value[$i]){
								//echo $lrow[$lrow_field["save_to_field"]]."-".$list_manual_value[$i];
								$list_view=$list_manual_text[$i];
							}
						}					
					?>	
					   <td style="padding:0 5 0 5" align="center">
							<?=$list_view?>
					   </td>
				<? }else if($lrow_field["fk_data_type"]=='readonly'){
				if ($_REQUEST[$lrow_field["kd_field"]]!="") {
					if ($lwhere!="") $lwhere.=" and";
					$field_kd_data=$reference_expression = explode("=",$lrow_field["reference_expression"]);
				
					$kd_data=get_rec("".$lrow_field["reference_table_name"]."","".$field_kd_data[0]."","upper(".($lrow_field["reference_field_name"]).") like '%".strtoupper($_REQUEST[$lrow_field["kd_field"]])."%'");
					$lwhere.=" upper(".$field_kd_data[1].") = ('".convert_sql(strtoupper($kd_data))."')";
				}else{		
					?>
						<td style="padding:0 5 0 5">
							<?=$lrow[$lrow_field["save_to_field"]]?>
						</td>
					<? 
				}
			}else {?>
						<td style="padding:0 5 0 5">
							<?=$lrow[$lrow_field["save_to_field"]]?>
						</td>
					<? }
					
					
					?>
				</td>
	<?
			}
	?>
																		
			</tr>
	<?
			$l_index+=1;
		}
	}
}
	
function create_button_non_item(){
	global $module;
	
	$lrs=pg_query("select * from skeleton.tblmenu where fk_parent='".$module."' and is_action=true and type_action='module_action' and per_item='f'");	
	while ($lrow=pg_fetch_array($lrs)) {
		if(check_right($lrow["kd_menu"],false)){
?>
<!--&nbsp;<input type="button" value=" <?=$lrow["nama_menu"]?> " class="groove_button" onClick="fModal('<?=$lrow["kd_menu"]?>')">
--><?	
		}	
	}
}

function create_button_item($p_pk_id){
	global $module;
	
	$lrs=pg_query("select * from skeleton.tblmenu where fk_parent='".$module."' and is_action=true and type_action='module_action' and per_item='t'");	
	while ($lrow=pg_fetch_array($lrs)) {
		//if(check_right($lrow["kd_menu"],false)){
?>
&nbsp;<input type="button" value=" <?=$lrow["nama_menu"]?> " class="groove_button" onClick="fModal('<?=$lrow["kd_menu"]?>','<?=$p_pk_id?>')">
<?	
		//}	
	}
}

?>