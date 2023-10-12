<?php

include_once("modal_approve_custom.php");

function cek_error_module(){
	global $strmsg,$j_action;
	$tgl_unpleding=$_REQUEST["tgl_unpleding"];
	if(!$tgl_unpleding){
		$strmsg.="Tgl Harus diisi <br>";	
	}

}


function request_additional(){
	global $tgl_data;
	$tgl_unpleding=$_REQUEST["tgl_unpleding"];

}
function html_additional(){
	global $nama_menu,$tgl_data;
	$tgl_unpleding=$_REQUEST["tgl_unpleding"];
?>
    <tr bgcolor="efefef">
        <td class="fontColor" style="padding:0 5 0 5" width="20%">Tgl Unpledging</td>
        <td style="padding:0 5 0 5" width="30%">
            <input type="text" value="<?=convert_date_indonesia($tgl_unpledging)?>" name="tgl_unpledging" maxlength="10" size="8" onKeyUp="fNextFocus(event,document.form1.tgl_unpledging)" onChange="fNextFocus(event,document.form1.tgl_unpledging)">&nbsp;<img src="images/btn_extend.gif" width="13" height="12" onClick="fPopCalendar(document.form1.tgl_unpledging,function(){})">
        </td>
        <td style="padding:0 5 0 5" width="20%"></td>
        <td style="padding:0 5 0 5" width="30%">
        </td>
    </tr>
<?
}

function save_data(){
	global $j_action,$strmsg,$id_menu,$kd_module,$id_edit, $upload_path,$kd_tabs,$kd_tabs2,$kd_tabs3,$upload_path_website_pic;
	$l_success=1;
	
	pg_query("BEGIN");
		
	$no_funding=$_REQUEST["no_funding"];
	$p_table="data_fa.tblfunding";
	$p_where=" no_funding='".$no_funding."'";
	$tgl_unpledging=convert_date_english($_REQUEST["tgl_unpledging"]);
	
	if(!pg_query(insert_log($p_table,$p_where,'UB')))$l_success=0;
	if(!pg_query("update data_fa.tblfunding set status_funding='Unpledging' where ".$p_where))$l_success=0;
	if(!pg_query(insert_log($p_table,$p_where,'UA')))$l_success=0;
	
	$lrs=pg_query("select * from data_fa.tblfunding_detail 
	where fk_funding = '".$no_funding."' ");
		
	$p_table2="data_fa.tblfunding_detail";
	$p_where2=" fk_funding='".$no_funding."'";

	while($lrow=pg_fetch_array($lrs)){					
		if(!pg_query("update data_fa.tblfunding_detail set tgl_unpledging='".$tgl_unpledging."' where ".$p_where2))$l_success=0;	
		//showquery("update data_fa.tblfunding_detail set tgl_unpledging='".$tgl_unpledging."' where ".$p_where2);
	}
	
	$l_id_log_ia=get_last_id("data_fa.tblfunding_log","pk_id_log");
	if(!pg_query("insert into data_fa.tblfunding_detail_log select *,'".$l_id_log_ia."' from data_fa.tblfunding_detail  where fk_funding='".$no_funding."'")) $l_success=0;
	
	//$l_success=0;
	if ($l_success==1){
		$lrs_kosong=pg_query("select * from skeleton.tblmodule inner join skeleton.tblmodule_fields on skeleton.tblmodule.pk_id=fk_module where fk_menu='".$id_menu."' and is_edit is true");
		while($lrow_kosong=pg_fetch_array($lrs_kosong)){
			$_REQUEST[$lrow_kosong["kd_field"]]="";
			$_REQUEST[str_replace("[]","",$lrow_kosong["kd_field"]."1")]="";
			$_REQUEST[str_replace("[]","",$lrow_kosong["kd_field"]."2")]="";
		}
		
		$lrs_detail_kosong=pg_query("select * from skeleton.tblmodule_detail where fk_module='".$kd_module."'");
		//showquery("select * from skeleton.tblmodule_detail where fk_module='".$kd_module."'");
		while($lrow_detail_kosong=pg_fetch_array($lrs_detail_kosong)){
			$_REQUEST["strisi_".$lrow_detail_kosong["kd_module_detail"]]="";	
		}	
		$strisi1="";
		$strmsg=$nm_menu." Tersimpan.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		pg_query("COMMIT");
	}else{
		$strmsg="Error :<br>".$nm_menu." Gagal Tersimpan.<br>";
		pg_query("ROLLBACK");
	}
}


?>