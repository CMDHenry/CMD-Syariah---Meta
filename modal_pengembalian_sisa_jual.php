<?php

include_once("modal_approve_custom.php");

function save_data(){
	global $j_action,$strmsg,$id_menu,$kd_module,$id_edit,$l_success;
	$l_success=1;
	pg_query("BEGIN");
	
	$fk_sbg=$_REQUEST["fk_sbg"];
	$no_lelang=$_REQUEST["no_lelang"];
	
	$p_table="data_gadai.tbllelang";
	$p_where=" no_lelang='".$no_lelang."'";
	
	if(!pg_query(insert_log($p_table,$p_where,'UB')))$l_success=0;
	if(!pg_query("update ".$p_table." set is_pengembalian='t' where ".$p_where))$l_success=0;	
	if(!pg_query(insert_log($p_table,$p_where,'UA')))$l_success=0;
	
	$angka_lelang=str_replace(',','',$_REQUEST['angka_lelang']);
	$angka_pelunasan=str_replace(',','',$_REQUEST['angka_pelunasan']);
	$nilai_selisih=($angka_lelang-$angka_pelunasan);
	$fk_bank = get_rec("tblsetting","fk_bank_pelunasan");
	$fk_cabang=get_rec("tblinventory","fk_cabang","fk_sbg='".$fk_sbg."'");
	$kas = get_coa_bank($fk_bank,$fk_cabang);
		
	$arrPost["kl_kelebihan_lelang"]     = array('type'=>'d','value'=>$nilai_selisih,'reference'=>$no_lelang);	
	$arrPost[$kas]						= array('type'=>'c','value'=>$nilai_selisih,'reference'=>$no_lelang,'account'=>$kas);
	//cek_balance_array_post($arrPost);
	if(!posting('PENGEMBALIAN SISA JUAL',$fk_sbg,today_db,$arrPost,$fk_cabang,'00'))$l_success=0;	
	
	//$arrPost[$kas]				  = array('type'=>'d','value'=>$nilai_selisih*-1,'reference'=>$no_lelang,'account'=>$kas);
	//$arrPost["piutang_nasabah"]     = array('type'=>'c','value'=>$nilai_selisih*-1,'reference'=>$no_lelang);	
	//if(!posting('PENERIMAAN KURANG LELANG DARI NASABAH',$fk_sbg,today_db,$arrPost,$fk_cabang,'00'))$l_success=0;	

	
	$ket="Pengembalian Sisa Jual";
	if(!pg_query(update_saldo_bank($fk_bank,$fk_cabang,0,$nilai_selisih,$ket,$no_lelang)))$l_success=0;	
		
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
		$strmsg.="Error :<br>".$nm_menu." Gagal Tersimpan.<br>";
		pg_query("ROLLBACK");
	}
}


?>