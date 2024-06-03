<?php
include_once("modal_edit_custom.php");
function cek_error_module(){
	global $strmsg,$j_action,$nama_menu;
	$tgl_data=$_REQUEST["tgl_data"];
	if(!$tgl_data && $nama_menu=='Tarik'){
		$strmsg.="Tgl Harus diisi <br>";	
	}
	if(date("Ym",strtotime(convert_date_english($tgl_data))) < date("Ym",strtotime(today_db))){
		$strmsg.="Tgl Tarik tidak bisa backdate <br>";	
		//biar akrual ga selisih di neraca
	} 
	

}
function request_additional(){
	global $tgl_data;
	$tgl_data=convert_date_english($_REQUEST["tgl_data"]);

}
function html_additional(){
	global $nama_menu,$tgl_data;
	$tgl_tarik=$_REQUEST["tgl_tarik"];
	if($nama_menu=='Tarik'){
?>
    <tr bgcolor="efefef">
        <td class="fontColor" style="padding:0 5 0 5" width="20%">Tgl Tarik</td>
        <td style="padding:0 5 0 5" width="30%">
            <input type="text" value="<?=convert_date_indonesia($tgl_data)?>" name="tgl_data" maxlength="10" size="8" onKeyUp="fNextFocus(event,document.form1.description)" onChange="fNextFocus(event,document.form1.description)">&nbsp;<img src="images/btn_extend.gif" width="13" height="12" onClick="fPopCalendar(document.form1.tgl_data,function(){})">
        </td>
        <td style="padding:0 5 0 5" width="20%"></td>
        <td style="padding:0 5 0 5" width="30%">
        </td>
    </tr>
<?
	}else{
?>
    <tr bgcolor="efefef">
        <td style="padding:0 5 0 5" width="20%">Tgl Tarik</td>
        <td style="padding:0 5 0 5" width="30%">
            <input type="hidden" value="<?=($tgl_tarik)?>" name="tgl_tarik" maxlength="10" size="8" ><?=($tgl_tarik)?>
        </td>
        <td style="padding:0 5 0 5" width="20%"></td>
        <td style="padding:0 5 0 5" width="30%">
        </td>
    </tr>


<?		
	}
}
function save_data(){
	global $j_action,$strmsg,$id_menu,$kd_module,$id_edit,$upload_path,$kd_tabs,$kd_tabs2,$kd_tabs3,$upload_path_website_pic,$nama_menu;
	$l_success=1;
	pg_query("BEGIN");
	
	$tgl_data=convert_date_english($_REQUEST["tgl_data"]);
	$tgl_tarik=$tgl_data;
	if($nama_menu!="Tarik"){
		$tgl_tarik=convert_date_english($_REQUEST["tgl_tarik"]);
	}
	$lrow=pg_fetch_array(pg_query("
	select * from tblinventory 
	left join (select ang_ke ,fk_sbg as fk_sbg1 from viewang_ke)as tbl on fk_sbg1=fk_sbg 
	left join(
		select distinct on (fk_sbg)fk_sbg as fk_sbg_tarik,tgl_data as tgl_tarik_old from data_gadai.tblhistory_sbg
		where transaksi='Tarik'	and tgl_data<'".$tgl_tarik." 23:59:00' and tgl_batal is null
	)as tarik on fk_sbg=fk_sbg_tarik					
	where fk_sbg='".$id_edit."'"));
	$status_inv=$lrow["status"];
	$ang_ke=$lrow["ang_ke"];	
	$fk_cabang=$lrow["fk_cabang"];	
	$fk_sbg=$id_edit;
	$tgl_tarik_old=$lrow["tgl_tarik_old"];	
	
	//$tgl_data='2022-01-01';
	if($status_inv=='Terima'){
		if(!pg_query(storing($id_edit,'-','Tarik','Tarik')))$l_success=0;
		
		$query_serial="select nextserial_cabang('TARIK':: text,'".$_SESSION["kd_cabang"]."')";
		$lrow_serial=pg_fetch_array(pg_query($query_serial));
		$referensi=$lrow_serial["nextserial_cabang"];	
			
		$p_arr=array(
			'tgl_data'=>$tgl_data,
			'fk_sbg'=>$id_edit,
			'referensi'=>$referensi,
			'ang_ke'=>$ang_ke,
			'transaksi'=>'Tarik',
		);
		
		//showquery(insert_history($p_arr));	
		if(!pg_query(insert_history($p_arr))) $l_success=0;			
		
		$query="
		select * from (
			select biaya_penyimpanan as total_bunga from data_gadai.tblproduk_cicilan where no_sbg='".$fk_sbg."'	
		) as viewsaldo_ar
		";
		$lrs=pg_query($query);
		$lrow=pg_fetch_array($lrs);	
		$total_bunga=$lrow["total_bunga"];		

		$query_sisa="
		select saldo_pinjaman as ar_cicilan,saldo_bunga, saldo_pokok from data_fa.tblangsuran 
		where fk_sbg='".$fk_sbg."' and tgl_bayar is not null
		order by angsuran_ke desc
		";
		$lrs_sisa=pg_query($query_sisa);
		$lrow_sisa=pg_fetch_array($lrs_sisa);	
		$ar_cicilan=$lrow_sisa["ar_cicilan"];	
		
		$fom=date("m/01/Y",strtotime($tgl_tarik));
		if($tgl_tarik_old)$eom_old=date("m/t/Y",strtotime($tgl_tarik_old));
		
		$query_akrual="
		select sum(akrual1+akrual2)as saldo_akrual from data_fa.tblangsuran 
		where fk_sbg='".$fk_sbg."' and tgl_jatuh_tempo <'".$fom."' ".($eom_old?"and tgl_jatuh_tempo>'".$eom_old."'":"")."
		";
		//showquery($query_akrual);
		$lrs_akrual=pg_query($query_akrual);
		$lrow_akrual=pg_fetch_array($lrs_akrual);	
		$saldo_akrual=$lrow_akrual["saldo_akrual"];		
		
		$sisa_yad=$total_bunga-$saldo_akrual;
		
		$arrPost = array();	
		$arrPost["piutang_pembiayaan"]				= array('type'=>'c','value'=>$ar_cicilan);
		// Ambil saldo proporsional
		// $arrPost["jaminan_dikuasai_kembali"]		= array('type'=>'d','value'=>$ar_cicilan-$sisa_yad);
		// $arrPost["pend_bunga_yad"]		        	= array('type'=>'d','value'=>$sisa_yad);
		// Ambil saldo pokok bunga		
		$arrPost["jaminan_dikuasai_kembali"]		= array('type'=>'d','value'=>$lrow_sisa["saldo_pokok"]);
		$arrPost["pend_bunga_yad"]		        	= array('type'=>'d','value'=>$lrow_sisa["saldo_bunga"]);
		
		foreach($arrPost as $index=>$temp){
			$arrPost[$index]['reference'] =$referensi;//tambah keterangan disemua arrpost
		}
		//cek_balance_array_post($arrPost);
		if(!posting('TARIK',$fk_sbg,$tgl_data,$arrPost,$fk_cabang,'00'))$l_success=0;				
		
	}elseif($status_inv=='Tarik'){
		if(!pg_query(storing($id_edit,'-','Terima','Batal Tarik')))$l_success=0;
		
		$lrow_tarik=pg_fetch_array(pg_query("select referensi,tgl_data from data_gadai.tblhistory_sbg where fk_sbg='".$id_edit."' and transaksi='Tarik' and tgl_batal is null order by pk_id desc,tgl_data desc "));
		$referensi=$lrow_tarik["referensi"];	
		$tgl_data=date("m/d/Y",strtotime($lrow_tarik["tgl_data"]));					
		
		if(!pg_query("
		update data_gadai.tblhistory_sbg set 
		tgl_batal='".today_db." ".date("H:i:s")."',fk_user_batal='".$_SESSION["username"]."' 			
		where fk_sbg='".$id_edit."'  and referensi='".$referensi."'
		")) $l_success=0;		
		
/*		showquery("
		update data_gadai.tblhistory_sbg set 
		tgl_batal='".today_db." ".date("H:i:s")."',fk_user_batal='".$_SESSION["username"]."' 			
		where fk_sbg='".$id_edit."'  and referensi='".$referensi."'
		");
*/	
		$type_owner=strtoupper('TARIK');
		$fk_owner=$fk_sbg;
		
		$arrPost = gl_balik($fk_owner,$type_owner,$referensi);
		if(count($arrPost)=='0'){
			$l_success=0;
		}
		//cek_balance_array_post($arrPost);
		if(!posting('BATAL '.$type_owner,$fk_owner,$tgl_data,$arrPost,$fk_cabang,'00'))$l_success=0;

	}							
	//echo $l_success;
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