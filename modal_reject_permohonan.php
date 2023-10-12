<?php

include_once("modal_approve_custom.php");

function save_data(){
	global $j_action,$strmsg,$id_menu,$kd_module,$id_edit, $upload_path,$kd_tabs,$kd_tabs2,$kd_tabs3,$upload_path_website_pic,$hasil;
	$l_success=1;
		
	pg_query("BEGIN");
	
	if(!pg_query("insert into data_gadai.tbltaksir_umum_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from data_gadai.tbltaksir_umum where no_fatg='".$_REQUEST['no_fatg']."'")) $l_success=0;	
	
	if(!pg_query("update data_gadai.tbltaksir_umum set status_taksir='Reject',status_approval_taksir='Batal' where no_fatg='".$_REQUEST['no_fatg']."'"))$l_success=0;
	//showquery("update data_gadai.tbltaksir_umum set status_taksir='Reject',status_approval_taksir='Batal' where no_fatg='".$_REQUEST['no_fatg']."'");
				 
	//log begin
	if(!pg_query("insert into data_gadai.tbltaksir_umum_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from data_gadai.tbltaksir_umum where no_fatg='".$_REQUEST['no_fatg']."'")) $l_success=0;
	//end log
	
	
	//update customer
	if(!pg_query("insert into tblcustomer_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from tblcustomer where no_cif='".$_REQUEST['fk_cif']."'")) $l_success=0;	
	
	if(!pg_query("update tblcustomer set status_debitur='Reject' where no_cif='".$_REQUEST['fk_cif']."'"))$l_success=0;
	//showquery("update tblcustomer set status_debitur='Reject' where no_cif='".$_REQUEST['fk_cif']."'");
				 
	//log begin
	if(!pg_query("insert into tblcustomer_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from tblcustomer where no_cif='".$_REQUEST['fk_cif']."'")) $l_success=0;

	 
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

function html_additional1(){
	global $id_edit;
?>	

	<tr bgcolor="efefef">   
		<td width="20%" style="padding:0 5 0 5" valign="top" >Cek</td>
        <td width="30%" style="padding:0 5 0 5" valign="top" >
		<textarea name="<?=$alasan_reject?>" rows="2" cols="80"  onKeyUp="fNextFocus(event, document.form1.btnSubmit)"></textarea>
		<td width="20%" style="padding:0 5 0 5" valign="top" ></td>
        <td width="30%" style="padding:0 5 0 5" valign="top" >        
</td>
	</tr>
<?
}



?>