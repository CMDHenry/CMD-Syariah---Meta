	<?
	create_recordset()
	?>
	<table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
		<tr height="20">
			<td>
				<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border">
					<tr bgcolor="#D0E4FF">
						<td>
                        
						<? 
							create_button_non_item();
						?>
						</td>Total data : 
                        <?=$recordset->total_row?>

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
						<? if(pg_num_rows(pg_query("select * from skeleton.tblmenu where fk_parent='".$module."' and is_action=true and type_action='module_action' and per_item='t' order by kd_menu"))){?>
                        <td width="150">&nbsp;</td>
                        <? } ?>
					</tr>
					<?
						create_list_data();
					?>
				</table>
				</div>
			</td>
		</tr>
		<tr height="20">
			<td bgcolor="#D0E4FF" class="border">&nbsp;        
			<? 
			//$column_total="total_taksir";
			if($column_total){
				//$lrs1 =pg_fetch_array(pg_query("select sum(".$column_total.") as ".$column_total." from (".$lquote.")as tbl1"));
				//echo "Total : ".convert_money("",$lrs1[$column_total],0);
			}
			?>

			</td>
		</tr>
        
		<tr height="20"><td>&nbsp;</td></tr>
	</table>
<?
function fascdesc($p_order){
	global $strorderby,$strordertype;
	if($strorderby==$p_order){
		if($strordertype=="asc") echo "<img src='images/asc.gif'>";
		else echo "<img src='images/desc.gif'>";
	}
}

function create_recordset(){
	global $module,$pk_id_module,$recordset,$strorderby,$strordertype,$page,$lquote,$is_historis;
	
	$lwhere="";
	$lrs=pg_query("select * from skeleton.tblmodule_fields where is_search='t' and fk_module='".$pk_id_module."' order by no_urut_add");
	//showquery("select * from skeleton.tblmodule_fields where is_search='t' and fk_module='".$pk_id_module."' order by no_urut_add");
	while ($lrow=pg_fetch_array($lrs) ) {
		//echo $lrow["fk_data_type"].'<br>';
		if($lrow["kd_field"]=="fk_sbg_detail" && $_REQUEST[$lrow["kd_field"]]!=""){
			//echo $_REQUEST[$lrow["kd_field"]];
			if ($module=="20171000000007") {
				if ($lwhere!="") $lwhere.=" and";
				$lwhere.=" no_batch in (select referensi from data_gadai.tblhistory_sbg where transaksi like 'Pembayaran%' and (fk_sbg) like upper('".convert_sql(($_REQUEST[$lrow["kd_field"]]))."'))";
			}			
		}else{
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
				if($_REQUEST[$lrow["kd_field"]."1"]!="" && $_REQUEST[$lrow["kd_field"]."2"]!=""){
					if ($lwhere!="") $lwhere.=" and";
					$lwhere.=" (".$lrow["save_to_field"]." >= '".convert_sql(strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]."1"])))." 00:00:00' and";
					$lwhere.=" ".$lrow["save_to_field_2"]." <= '".convert_sql(strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]."2"])))." 23:58:59')";
				}
			} else if($lrow["fk_data_type"]=='date'){
				if($_REQUEST[$lrow["kd_field"]]){
					$date_search=explode("/",strtoupper(convert_date_indonesia($_REQUEST[$lrow["kd_field"]])));
					$date_search_use=$date_search[2]."-".$date_search[0]."-".$date_search[1];
					if ($lwhere!="") $lwhere.=" and";
					$lwhere.=" upper(".$lrow["kd_field"].") like upper('%".convert_sql($date_search_use)."%')";
				}
			}else if($lrow["fk_data_type"]=='list'){
				if($lrow["type_list"]=='get'){
					if ($_REQUEST[$lrow["kd_field"]]!="") {
						if ($lwhere!="") $lwhere.=" and";
						$lwhere.=" upper(".$lrow["kd_field"].") like upper('%".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]]))."%')";
					}
				}else{
					if ($_REQUEST[$lrow["kd_field"]]!="") {
						if ($lwhere!="") $lwhere.=" and";
						$lwhere.=" upper(".$lrow["kd_field"].") = ('".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]]))."')";
					}
				}
			}/*else if($lrow["fk_data_type"]=='readonly'){
				echo $lrow["reference_table_name"].' - '.$lrow["reference_field_name"];
				if ($_REQUEST[$lrow["kd_field"]]!="") {
					if ($lwhere!="") $lwhere.=" and";
					$field_kd_data=$reference_expression = explode("=",$lrow["reference_expression"]);
					print_r ($field_kd_data);
					$kd_data=get_rec("".$lrow["reference_table_name"]."","".$field_kd_data[0]."","upper(".($lrow["reference_field_name"]).") like '%".strtoupper($_REQUEST[$lrow["kd_field"]])."%'");
					
					$lwhere.=" upper(".$field_kd_data[1].") = ('".convert_sql(strtoupper($kd_data))."')";
				}
			}*/else{
				// $lrow["kd_field"];
				if($lrow["fk_data_type"]=="checkbox_list"){
					$lrow["kd_field"]=str_replace("]","",(str_replace("[","",$lrow["kd_field"])));
				}
				if ($_REQUEST[$lrow["kd_field"]]!="") {
					//echo $lrow["kd_field"];
					if ($lwhere!="") $lwhere.=" and";
					$lwhere.=" upper(".$lrow["kd_field"].") like upper('%".convert_sql(strtoupper($_REQUEST[$lrow["kd_field"]]))."%')";
				}
			}
		}	
	}
	
	/*$query_wilayah=(pg_query("
	select * from(
		select * from tblwilayah_detail 
		left join tblprovinsi_detail on tblprovinsi_detail.fk_wilayah=tblwilayah_detail.fk_wilayah
		where tblwilayah_detail.fk_wilayah='".$_SESSION["kd_wilayah"]."'
	)as tblwilayah
	inner join tblkota on tblkota.fk_provinsi=tblwilayah.fk_provinsi
	inner join tblkecamatan on fk_kota=kd_kota
	inner join tblkelurahan on fk_kecamatan =kd_kecamatan
	inner join tblcabang on fk_kelurahan=kd_kelurahan
	"));*/
	
	/*if($_SESSION["jenis_user"]=='Area' ){	

		$query_wilayah=(pg_query("
		select * from(
			select * from tblcabang
			where fk_area='".$_SESSION["kd_area"]."'
		)as tblwilayah
		"));
		while($lwilayah=pg_fetch_array($query_wilayah)){
			$cabang.="'".$lwilayah["kd_cabang"]."',";						
		}
		$cabang=rtrim($cabang,',');
	}				
	*/	
	
	$lrow=pg_fetch_array(pg_query("select * from skeleton.tblmenu where pk_id='".$module."'"));	
	//showquery("select * from skeleton.tblmenu where pk_id='".$module."'");
		
	//Filter biar muncul data yang sesuai sistem
	//$is_historis ='f';
	if($lrow["field_tgl_sistem"]!="" && $is_historis!='true'){
		if ($lwhere!="") $lwhere.=" and";
		if($lrow["field_bulan_sistem"]!='t'){
			$lwhere.=" ".$lrow["field_tgl_sistem"]." = (select tgl_sistem from tblsetting)";
		}else{
			$lwhere.=" 
			(
				(			
					extract(year from  ".$lrow["field_tgl_sistem"].") = extract (year from(select tgl_sistem from tblsetting))
					and
					extract(month from  ".$lrow["field_tgl_sistem"].") = extract (month from(select tgl_sistem from tblsetting))								
				)
				or
				status_data='Need Approval'
			)
			";
		}
	}
	
	//case approval penaksir
	if(strstr(strtoupper($lrow["root_menu"]),strtoupper("Approval Taksir 3"))||strstr(strtoupper($lrow["root_menu"]),strtoupper("Approval Taksir 4"))){						
		$query_penaksir=(pg_query("select * from data_gadai.tbltaksir 
		where tgl_taksir =(select tgl_sistem from tblsetting) and
		fk_user_penaksir='".$_SESSION["username"]."' "));						
		//$penaksir='t';			
		while($lpenaksir=pg_fetch_array($query_penaksir)){
			$lwhere_taksir.="'".$lpenaksir["no_fatg"]."',";
		}
		$lwhere_taksir=rtrim($lwhere_taksir,',');
		
		if ($lwhere_taksir!=""){
			$lwhere2=" no_fatg in (".$lwhere_taksir.")";
		}
		//echo $lwhere_taksir;
	}
	
	//case pencairan non tunai utk HO
	//echo strtoupper($lrow["root_menu"]);
	
	if((strstr(strtoupper($lrow["nama_menu"]),"PENCAIRAN GADAI")||strstr(strtoupper($lrow["nama_menu"]),"PENCAIRAN CICILAN")) && $_SESSION["username"]!='superuser'){						
		
/*		if($_SESSION["jenis_user"]!='HO'){
			if ($lwhere!="") $lwhere.=" and";
			$lwhere.=" pembayaran = '1'";
		}else{
			if ($lwhere!="") $lwhere.=" and";
			$lwhere.=" pembayaran = '2'";
		}
*/	}
	if(strstr(strtoupper($lrow["nama_menu"]),"APPROVAL")){		
		$query_user="select * from tbluser left join tbllevel on fk_level =kd_level where username='".$_SESSION["username"]."'";
		$lrs_user=pg_query($query_user);		
		$lrow_user=pg_fetch_array($lrs_user);
		if($lrow_user["name"]=="DIREKSI" || $lrow_user["name"]=="PRESDIR"){
/*			if ($lwhere!="") $lwhere.=" and";
			$lwhere.=" (final_approval in ('Dirut','Direktur') )";
*/		}
	}

	
	//case jenis_user
	if($_SESSION["jenis_user"]!='HO' && ($_SESSION["kd_cabang"]||$_SESSION["kd_wilayah"]) &&$lrow["field_fk_dealer"]!="" ){
				
		if($_SESSION["jenis_user"]=='Pos' ){	
			$cabang="'".$_SESSION["kd_cabang"]."'";
		}
		
		if($_SESSION["jenis_user"]=='Unit' ){	
			if(!strstr(strtoupper($lrow["root_menu"]),"BLANKO")){						
				$query_cabang=(pg_query("select * from tblcabang where head_unit='".$_SESSION["kd_cabang"]."'"));
				while($lcabang=pg_fetch_array($query_cabang)){
					$cabang.="'".$lcabang["kd_cabang"]."',";						
				}
			}
			$cabang.="'".$_SESSION["kd_cabang"]."',";
			$cabang=rtrim($cabang,',');
		
		}
	
		if($_SESSION["jenis_user"]=='Cabang' ){	
			if(!strstr(strtoupper($lrow["root_menu"]),"BLANKO")){						
				$query_cabang=(pg_query("select * from tblcabang where head_cabang='".$_SESSION["kd_cabang"]."'"));
				while($lcabang=pg_fetch_array($query_cabang)){
					$cabang.="'".$lcabang["kd_cabang"]."',";						
				}
			}
			$cabang.="'".$_SESSION["kd_cabang"]."',";
			$cabang=rtrim($cabang,',');
			
		}
		if($_SESSION["jenis_user"]=='Wilayah' ){	

			$query_wilayah=(pg_query("
			select * from(
				select * from tblcabang
				where fk_wilayah='".$_SESSION["kd_wilayah"]."'
			)as tblwilayah
			"));
			while($lwilayah=pg_fetch_array($query_wilayah)){
				$cabang.="'".$lwilayah["kd_cabang"]."',";						
			}
			$cabang=rtrim($cabang,',');
		}
		
		$cabang="'".$_SESSION["kd_cabang"]."'";
		if(!$cabang)$cabang="'".$_SESSION["kd_cabang"]."'";
			
		if ($lwhere2!="") $lwhere2.=" or";
		$lwhere2.=" ".$lrow["field_fk_dealer"]." in (".$cabang.")";

	}
	if((strstr(strtoupper($lrow["nama_menu"]),"MUTASI BPKB"))){		
			
		if($_SESSION["jenis_user"]!='HO' ){	
			if ($lwhere!="") $lwhere.=" and";			
			$lwhere.=" (fk_cabang_terima='".$_SESSION["kd_cabang"]."' or fk_cabang_kirim='".$_SESSION["kd_cabang"]."')  ";
		}
	}
	
	
	if ($lwhere!="") $lwhere=" where ".$lwhere;
	
	if ($lwhere2!=""){
		if ($lwhere!="")$operator="and";
		else $operator="where";
		$lwhere.=$operator. "( ".$lwhere2." )";//khusus approval delegasi di taksir && filter cabang
	}
	
	if($pk_order_by==$strorderby && $lrow["field_order_by"]!=""){
		$order=$lrow["field_order_by"]." ".$strordertype;
	}elseif($lrow["field_order_by"]!="" && $lrow["field_order_by"]!=$strorderby ){
		$order=$strorderby." ".$strordertype;
	}elseif($lrow["field_order_by"]!="" ){
		$order=$lrow["field_order_by"]." ".$strordertype;
	}else{
		$order=$strorderby." ".$strordertype;
	}
	//echo $strorderby;
	
	if(!$lwhere){
		switch(strtoupper($lrow["nama_menu"])){
			case "DAFTAR KONTRAK" : 
				//$lrow["list_sql"].=" limit 20";
			break;
			
			case "KREDIT" : 
				//$lrow["list_sql"].=" limit 20";
			break;
			
			case "CICILAN" : 
				//$lrow["list_sql"].=" limit 20";
			break;
			
			case "APPROVAL CICILAN" : 
				//$lrow["list_sql"].=" limit 20";
			break;			
			
			case "PENCAIRAN CICILAN" : 
				//$lrow["list_sql"].=" limit 20";
			break;
			
			case "AR CICILAN" : 
				//$lrow["list_sql"].=" limit 20";
			break;
			
		}
	}//dibatasin yg keluar. biar by search aj
	

	$lquote="select * from (
				".$lrow["list_sql"]."
			) as tblmain
			".$lwhere." order by ".$order;	
	//showquery($lquote);	

	$pPage=20;
	//echo $lrow["nama_menu"];
	if(strstr(strtoupper($lrow["nama_menu"]),"QUERY SALDO"))$pPage=1000;
	$recordset = new recordset("",$lquote,$page,$pPage);
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
	
	//showquery("select * from skeleton.tblmodule_fields where fk_module='".$pk_id_module."' and is_view='t' order by no_urut_edit");
	
	while ($lrow=pg_fetch_array($lrs)) {
		$lrow["value_type"];
		$lrow["reference_field_name"];
		$reference_expression = explode("=",$lrow["reference_expression"]);
?>
		<td><a href="#" onClick="fOrder('<?=(($lrow["value_type"]=="reference" && $lrow["fk_data_type"]=="readonly")?$lrow["reference_field_name"]:$lrow["save_to_field"])?>')"><?=$lrow["nm_field"]?></a><? fascdesc(($lrow["value_type"]=="reference" && $lrow["fk_data_type"]=="readonly")?$lrow["reference_field_name"]:$lrow["save_to_field"])?></td>
<?
	}
}

function create_list_data(){
	global $recordset,$module,$pk_id_module;

	$l_index=0;

	$lrow_field_pk=pg_fetch_array(pg_query("select * from (
		select * from skeleton.tblmodule_fields where fk_module='".$pk_id_module."'
	) as tblmodule_field
	inner join (
		select * from skeleton.tbldb_table_detail where is_pk='t'
	) as tbldb_table_detail on fk_db_table=save_to_table and tbldb_table_detail.kd_field=save_to_field"));
	
	/*showquery("select * from (
		select * from skeleton.tblmodule_fields where fk_module='".$pk_id_module."'
	) as tblmodule_field
	inner join (
		select * from skeleton.tbldb_table_detail where is_pk='t'
	) as tbldb_table_detail on fk_db_table=save_to_table and tbldb_table_detail.kd_field=save_to_field");*/
	
	$lrs = $recordset->get_recordset();

	while ($lrow=pg_fetch_array($lrs)){
?>
		<tr bgcolor='#e0e0e0' onmouseover="fTRColor(this,'over')" onmouseout="fTRColor(this,'out')">
<?

		$lrs_field=pg_query("
		select * from(
			select *,
			case 
			when no_urut_list is not null then no_urut_list 
			else no_urut_edit end as no_urut from skeleton.tblmodule_fields 
			where fk_module='".$pk_id_module."' and is_view='t' 
		)as tblmain	
		order by no_urut");
		while ($lrow_field=pg_fetch_array($lrs_field)) {
			
			//echo $lrow_field["kd_field"]."==".$lrow_field_pk["kd_field"];
			//echo "1";
			if($lrow_field["is_numeric"]=="t" )$lrow_field["fk_data_type"]="numeric" ;
?>	
				<? if ($lrow_field["kd_field"]==$lrow_field_pk["kd_field"]) {
					//echo $lrow_field["kd_field"]."==".$lrow_field_pk["kd_field"];
					?>
            		<td style="padding:0 5 0 5">
                    	<a href="#" class="blue" onClick="fModal('view','<?=$lrow[$lrow_field["save_to_field"]]?>')"><?=$lrow[$lrow_field["save_to_field"]]?></a>
                    </td>
                    
              	<? } else if(($lrow_field["fk_data_type"]=="date" || $lrow_field["fk_data_type"]=="timestamp") && ($lrow_field["value_type"]=="input" || $lrow_field["value_type"]=="php")){ ?>
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
                
                <? } else if(($lrow_field["fk_data_type"]=="date" || $lrow_field["fk_data_type"]=="timestamp") && $lrow_field["value_type"]=="reference" && $lrow_field["reference_table_name"]=="sql_query"){ ?>
                	<td style="padding:0 5 0 5" align="center">	
                		<?
						$fk_id = explode("=", $lrow_field["reference_expression"]);
						$lrs_field_reference=(pg_query("select sql_query from skeleton.tblsql_query where kd_sql_query='".$lrow_field["sql_query"]."'")); //ambil sql_query dari tabel sql_query
						//showquery("select sql_query from skeleton.tblsql_query where kd_sql_query='".$lrow_field["sql_query"]."'");
						while ($lrow_field_reference=pg_fetch_array($lrs_field_reference)){
							 $fk_test=explode("as tbl",$lrow_field_reference["sql_query"]);
							 $fk_test=str_replace("[","",$fk_test);
							 $fk_test=str_replace("]","",$fk_test);
							 //showquery($fk_test[0]."where ".$fk_id[0]."='".$lrow["".$fk_id[1].""]."'");
							// echo $fk_id[1];
							 $lrs_test=pg_query($fk_test[0]." where ".$fk_id[0]."='".$lrow["".$fk_id[1].""]."'"); //untuk ambil field yang di select pada fungsi SQL QUERY
							 //echo $fk_id[1];
							// showquery($fk_test[0]." where ".$fk_id[0]."='".$lrow["".$fk_id[1].""]."'");
							 while($lrow_test=pg_fetch_array($lrs_test)){
								if($lrow_test[$lrow_field["kd_field"]]){
									
									echo date("d/m/Y",strtotime($lrow_test[$lrow_field["kd_field"]])); //menampilkan value dari foreign id					
								}
							 }
						}
						?>
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
                    	<? if($lrow[$lrow_field["save_to_field"]] && $lrow[$lrow_field["save_to_field_2"]]){ ?>
						<?=date("d/m/Y",strtotime($lrow[$lrow_field["save_to_field"]]))?> - <?=date("d/m/Y",strtotime($lrow[$lrow_field["save_to_field_2"]]))?>
                    	<? } ?>
                    </td>	
                        
				<? } else if($lrow_field["fk_data_type"]=="checkbox"){?>	
                	<td style="padding:0 5 0 5" align="center">
                    	<img src="./images/<?=(($lrow[$lrow_field["save_to_field"]]=='t')?"true":"false")?>.gif">
					</td>
                <? } else if($lrow_field["save_to_field"]=="" && $lrow_field["fk_data_type"]=="readonly" && $lrow_field["reference_table_name"]!="sql_query"){?>	
                	<td style="padding:0 5 0 5" align="center">
                    	 <?
						 $fk_id = explode("=", $lrow_field["reference_expression"]);
						// echo $fk_id[1];
						$value_view = get_rec("".$lrow_field["reference_table_name"]."","".$lrow_field["reference_field_name"]."","$fk_id[0]='".$lrow["".$fk_id[1].""]."'");
						//showquery ("select ".$lrow_field["reference_field_name"]." from ".$lrow_field["reference_table_name"]." where $fk_id[0]='".$lrow["".$fk_id[1].""]."'");
						 //if()
						 if(strstr($value_view,"00:00:00")==true)
							echo date("d/m/Y",strtotime($value_view));
						 else echo $value_view;	
						 ?>
					</td>
                    <?   } else if($lrow_field["save_to_field"]=="" && $lrow_field["fk_data_type"]=="numeric"  && $lrow_field["value_type"]=="reference" && $lrow_field["reference_table_name"]!="sql_query"){//tambahan?>	
                	<td style="padding:0 5 0 5" align="right">
                    	 <?
						 $fk_id = explode("=", $lrow_field["reference_expression"]);
						 //echo $fk_id[1];
						$value_view = get_rec("".$lrow_field["reference_table_name"]."","".$lrow_field["reference_field_name"]."","$fk_id[0]='".$lrow["".$fk_id[1].""]."'");
						//showquery ("select ".$lrow_field["reference_field_name"]." from ".$lrow_field["reference_table_name"]." where $fk_id[0]='".$lrow["".$fk_id[1].""]."'");
						 //if()
						 if(strstr($value_view,"00:00:00")==true)
							echo date("d/m/Y",strtotime($value_view));
						 else echo convert_money("",$value_view,0);	
						 ?>
					</td>
                    
                    <? } else if($lrow_field["save_to_field"]=="" && $lrow_field["fk_data_type"]=="date"  && $lrow_field["value_type"]=="reference" && $lrow_field["reference_table_name"]!="sql_query"){//tambahan?>	
                	<td style="padding:0 5 0 5" align="center">
                    	 <?
						 $fk_id = explode("=", $lrow_field["reference_expression"]);
						 //echo $fk_id[1];
						$value_view = get_rec("".$lrow_field["reference_table_name"]."","".$lrow_field["reference_field_name"]."","$fk_id[0]='".$lrow["".$fk_id[1].""]."'");
						//showquery ("select ".$lrow_field["reference_field_name"]." from ".$lrow_field["reference_table_name"]." where $fk_id[0]='".$lrow["".$fk_id[1].""]."'");
						 //if()
							echo date("d/m/Y",strtotime($value_view));
						 ?>
					</td>
                    <? }else if($lrow_field["save_to_field"]=="" && $lrow_field["fk_data_type"]=="readonly" && $lrow_field["reference_table_name"]=="sql_query"){?>	
                	<td style="padding:0 5 0 5" align="left">
                    	 <?
						$fk_id = explode("=", $lrow_field["reference_expression"]);
						//echo  $lrow_field["reference_expression"];
						//print_r($fk_id);
						$lrs_field_reference=(pg_query("select sql_query from skeleton.tblsql_query where kd_sql_query='".$lrow_field["sql_query"]."'")); //ambil sql_query dari tabel sql_query
						//showquery("select sql_query from skeleton.tblsql_query where kd_sql_query='".$lrow_field["sql_query"]."'");
						while ($lrow_field_reference=pg_fetch_array($lrs_field_reference)){
							 $fk_test=explode("as tbl",$lrow_field_reference["sql_query"]);
							 $fk_test=str_replace("["," ",$fk_test);
							 $fk_test=str_replace("]"," ",$fk_test);
							 //showquery($fk_test[0]."where ".$fk_id[0]."='".$lrow["".$fk_id[1].""]."'");
							 if(substr($fk_id[1],0,1)=="'"){
							 $lrs_test=pg_query($fk_test[0]."where ".$fk_id[0]."=".$fk_id[1].""); //untuk ambil field yang di select pada fungsi SQL QUERY			
					
							 }
							 else{
							 $lrs_test=pg_query($fk_test[0]."where ".$fk_id[0]."='".$lrow["".$fk_id[1].""]."'"); //untuk ambil field yang di select pada fungsi SQL QUERY
							 }
							 //showquery($fk_test[0]."where ".$fk_id[0]."=".$fk_id[1]."");
							 while($lrow_test=pg_fetch_array($lrs_test)){
								echo $lrow_test[$lrow_field["kd_field"]]; //menampilkan value dari foreign id
							 }
						}
						?>
					</td>
                <? } else if($lrow_field["fk_data_type"]=="list" && $lrow_field["type_list"]=="list_db"){?>	
                	<td style="padding:0 5 0 5" align="center">
                    	 <?
						 $get_list_db=explode(" ", $lrow_field["list_sql"]);
						 if($lrow_field["is_multiple"]=="f"){
							 $value_list_db = get_rec("".$get_list_db[3]."","".$lrow_field["list_field_text"]."","".$lrow_field["list_field_value"]."='".$lrow[$lrow_field["save_to_field"]]."'");
							 //showquery("select ".$lrow_field["list_field_text"]." from ".$get_list_db[3]." where ".$lrow_field["list_field_value"]."='".$lrow[$lrow_field["save_to_field"]]."'");
							 echo $value_list_db;
						 }else{
							$kd_field_db=$lrow[$lrow_field["save_to_field"]];
							$kd_field_db=explode(",",$kd_field_db);
							for($i=0;$i<count($kd_field_db);$i++){
								$value_list_db = get_rec("".$get_list_db[3]."","".$lrow_field["list_field_text"]."","".$lrow_field["list_field_value"]."='".$kd_field_db[$i]."'");
								if($i!=(count($kd_field_db)-1))echo $value_list_db.", ";
								else echo $value_list_db;
							}
						 }
						 ?>
					</td>
                <? } else if($lrow_field["fk_data_type"]=="numeric" && $lrow_field["value_type"]=="reference" && $lrow_field["reference_table_name"]=="sql_query"){ ?>
                	<td style="padding:0 5 0 5" align="right">	
                		<?
						$fk_id = explode("=", $lrow_field["reference_expression"]);
						$lrs_field_reference=(pg_query("select sql_query from skeleton.tblsql_query where kd_sql_query='".$lrow_field["sql_query"]."'")); //ambil sql_query dari tabel sql_query
						//showquery("select sql_query from skeleton.tblsql_query where kd_sql_query='".$lrow_field["sql_query"]."'");
						while ($lrow_field_reference=pg_fetch_array($lrs_field_reference)){
							 $fk_test=explode("as tbl",$lrow_field_reference["sql_query"]);
							 $fk_test=str_replace("[","",$fk_test);
							 $fk_test=str_replace("]","",$fk_test);
							 //showquery($fk_test[0]."where ".$fk_id[0]."='".$lrow["".$fk_id[1].""]."'");
							 $lrs_test=pg_query($fk_test[0]."where ".$fk_id[0]."='".$lrow["".$fk_id[1].""]."'"); //untuk ambil field yang di select pada fungsi SQL QUERY
							 //showquery($fk_test[0]."where ".$fk_id[0]."='".$lrow["".$fk_id[1].""]."'");
							 while($lrow_test=pg_fetch_array($lrs_test)){
								echo convert_money("",($lrow_test[$lrow_field["kd_field"]])); //menampilkan value dari foreign id
							 }
						}
						?>
                   	</td>  
               <? }else if($lrow_field["fk_data_type"]=="numeric"){  ?>
                   <td style="padding:0 5 0 5" align="right">
                		<?=convert_money("",$lrow[$lrow_field["save_to_field"]],0)?>
                   </td>
                <? } else if($lrow_field["fk_data_type"]=="list" && $lrow_field["type_list"]=="list_manual"){
					$list_manual_text = explode(",", $lrow_field["list_manual_text"]);
					$list_manual_value = explode(",", $lrow_field["list_manual_value"]);
					$list_view="";
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
             	<?	}else {?>
                    <td style="padding:0 5 0 5" align="center">
                    	<?  if(strstr($lrow[$lrow_field["save_to_field"]],"00:00:00")==true)
								//$lrow[$lrow_field["save_to_field"]]=date("d/m/Y",strtotime($lrow[$lrow_field["save_to_field"]]));
                         ?>
                         <? if($lrow_field["is_link_view"]=="t"){
							// echo $lrow_field["link_view_reference"];
							
								if(strstr($lrow_field["link_view_reference"],'menu_finance')){
									$query_voc="";
									$lwhere_voc="where no_voucher='".$lrow[$lrow_field["save_to_field"]]."'";
									
									$array_voc[0]["tblpetty_cash"]["20171100000014"]='';
									$array_voc[1]["tblmutasi_bank"]["20171100000016"]=' and jenis_mutasi in(0,1)';
									$array_voc[2]["tblmutasi_bank"]["20180200000029"]=' and jenis_mutasi in(2)';
									$array_voc[3]["tblmutasi_bank"]["20180100000023"]=' and jenis_mutasi in(3)';
									$array_voc[4]["tblmutasi_bank"]["20180100000030"]=' and jenis_mutasi in(4)';
									$array_voc[5]["tbladvance"]["20171100000046"]=' and jenis_advance =1';
									$array_voc[6]["tbladvance"]["20180200000039"]=' and jenis_advance =2';
									$array_voc[7]["tblpayment_request"]["20171200000003"]='';
									$array_voc[8]["tblrekon_bank"]["20180200000034"]='';	
									$i=0;
									foreach($array_voc as $no_urut =>$arr_tbl){

										foreach($arr_tbl as $tbl =>$arr_id_menu){
											foreach($arr_id_menu as $id_menu =>$condition){
												$query_voc.="
												select no_voucher,tgl_voucher,'".$id_menu."'as id_menu from data_fa.".$tbl."
												".$lwhere_voc." ".$condition."";
												if($i==count($array_voc)-1)break;
												$query_voc.="union
												";
											}
										}
										$i++;
									}

									//showquery($query_voc);
									$lrs_voc=pg_query($query_voc);
									$lrow_voc=pg_fetch_array($lrs_voc);		
									$id_menu=$lrow_voc["id_menu"];
						 ?>
                          
                    			<a href="#" class="blue" onClick="fModal('view_reference','<?=$lrow[$lrow_field["save_to_field"]]?>','<?=$id_menu?>')"><?=$lrow[$lrow_field["save_to_field"]]?></a>
                                
                                
                         	  <? }							
								elseif(strstr($lrow_field["link_view_reference"],'php')){
						  ?>
                          
                				<a href="#" class="blue" onClick="fModal('view_other','<?=$lrow[$lrow_field["save_to_field"]]?>','<?=$lrow_field["link_view_reference"]?>','<?=$lrow_field["link_view_reference"]?>')"><?=$lrow[$lrow_field["save_to_field"]]?></a>
                                
                                
                         	  <? }else {?>     
                              
                    			<a href="#" class="blue" onClick="fModal('view_reference','<?=$lrow[$lrow_field["save_to_field"]]?>','<?=$lrow_field["link_view_reference"]?>')"><?=$lrow[$lrow_field["save_to_field"]]?></a>
                                
                                
                          	 <? }?>     

                        <? }else{?>
							<?=$lrow[$lrow_field["save_to_field"]]?>
                        <? }?>
                        
                    </td>
                <? }?>
			</td>
<?
		}
?>
			<td align="center">
				<? 
					create_button_item($lrow[$lrow_field_pk["kd_field"]],$lrow);
				?>
			</td>														
		</tr>
<?
		$l_index+=1;
	}
}

function create_button_non_item(){
	global $module;
	//echo "456";
	
	$lrs=pg_query("select * from skeleton.tblmenu where fk_parent='".$module."' and is_action=true and type_action='module_action' and per_item='f' and is_hidden!='f' order by kd_menu");	
	while ($lrow=pg_fetch_array($lrs)) {
		$lresult=NULL;
		if($lrow["condition_module_action"]==""){
			$lresult='t';
		}else{
			eval("\$lresult=".$lrow["condition_module_action"].";");
		}		
		if($lresult){
			if(check_right($lrow["kd_menu"],false)){
	?>
			&nbsp;<input type="button" value="<?=$lrow["nama_menu"]?>" class="groove_button" onClick="fModal('<?=$lrow["kd_menu"]?>')">
	<?	
			}	
		}
		
	}
}

function create_button_item($p_pk_id,$lrow){
	global $module;
	//showquery("select * from skeleton.tblmenu where fk_parent='".$module."' and is_action=true and type_action='module_action' and per_item='t' order by kd_menu");
	
	$lrow[$lrow_field_pk["kd_field"]]=$p_pk_id;
	$lrs_condition_button=pg_query("select * from skeleton.tblmenu where fk_parent='".$module."' and is_action=true and type_action='module_action' and per_item='t' and is_hidden!='f' order by kd_menu");//and condition_module_action is null	
	while ($lrow_condition_button=pg_fetch_array($lrs_condition_button)) {
		$lresult=NULL;
		if($lrow_condition_button["condition_module_action"]==""){
			$lresult='t';				
		}else{
			eval("\$lresult=".$lrow_condition_button["condition_module_action"].";");
		}
		//echo $lresult.$lrow_condition_button["condition_module_action"];
		if($lresult){
			if(check_right($lrow_condition_button["kd_menu"],false)){

	?>				
                <input type="button" value="<?=$lrow_condition_button["nama_menu"]?>" class="groove_button" onClick="fModal('<?=$lrow_condition_button["kd_menu"]?>','<?=$lrow[$lrow_field_pk["kd_field"]]?>')">
	<?
			}

		}
	}
}
?>