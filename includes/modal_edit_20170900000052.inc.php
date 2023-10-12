<?
function cek_error_module(){
	global $strmsg,$l_success;
	
	$lwhere="where fk_fatg='".$_REQUEST["no_fatg"]."' and status_data!='Batal' and status_approval!='Batal'";
	
	if(pg_num_rows(pg_query("
		select fk_fatg from data_gadai.tblproduk_gadai
		$lwhere
		union all
		select fk_fatg from data_gadai.tblproduk_cicilan
		$lwhere
	"))){
		//$strmsg.="FATG sudah dibuat SBG.<br>";	
		
		$l_arr_row = split(chr(191),$_REQUEST["strisi_taksir_umum"]);
		for ($i=0; $i<count($l_arr_row)-1; $i++){
			$l_arr_col=split(chr(187),$l_arr_row[$i]);
			$harga[$i]=str_replace(",","",$l_arr_col[6]);
			$barang[$i]=str_replace(",","",$l_arr_col[0]);
		}
		
		$lrs=pg_query("select * from data_gadai.tbltaksir_umum_detail
		where fk_fatg = '".$_REQUEST["no_fatg"]."' ");
		$i=0;
		while($lrow=pg_fetch_array($lrs)){
			//echo $lrow["nilai_taksir"].'=='.$harga[$i];
			if($lrow["nilai_taksir"]!=$harga[$i]){
				$strmsg.="@Detail line-".($i+1).": Harga tidak boleh diubah jika sudah dibuat kontrak.<br>";	
			}
			if($lrow["fk_barang"]!=$barang[$i]){
				//$strmsg.="@Detail line-".($i+1).": Barang tidak boleh diubah jika sudah dibuat kontrak.<br>";	
			}
			
			$i++;
		}			
	}
	
	
	$lrow=pg_fetch_array(pg_query("select * from data_gadai.tbltaksir_umum left join (select * from data_gadai.tblproduk_cicilan where status_data!='Batal' and status_approval!='Batal')as tbl on fk_fatg=no_fatg where no_fatg='".$_REQUEST["no_fatg"]."'"));	//query data sblum update	
	
	//showquery("select * from data_gadai.tbltaksir_umum left join (select * from data_gadai.tblproduk_cicilan where status_data!='Batal' and status_approval!='Batal')as tbl on fk_fatg=no_fatg where no_fatg='".$_REQUEST["no_fatg"]."'");
	
	$lwhere_taksir=" and status_barang='Baru' and status_taksir!='Batal' and no_fatg!='".$_REQUEST["no_fatg"]."'";
	
	if($_REQUEST["status_barang"]=='Baru' && $_REQUEST["no_mesin"] ){
		if(pg_num_rows(pg_query("select * from data_gadai.tbltaksir_umum where no_mesin='".$_REQUEST["no_mesin"]."' ".$lwhere_taksir))){
			$strmsg.="No Mesin sudah terdaftar untuk unit baru.<br>";
		}
	}
	
	//showquery("select * from data_gadai.tbltaksir_umum where no_mesin='".$_REQUEST["no_mesin"]."' ".$lwhere_taksir);
	
	if($_REQUEST["status_barang"]=='Baru' && $_REQUEST["no_rangka"] ){
		if(pg_num_rows(pg_query("select * from data_gadai.tbltaksir_umum where no_rangka='".$_REQUEST["no_rangka"]."' ".$lwhere_taksir))){
			$strmsg.="No Rangka sudah terdaftar untuk unit baru.<br>";
		}
	}	
	
	if($lrow["tgl_cair"] && $_SESSION["jenis_user"]!="HO"){
		if($_REQUEST["no_mesin"]!=$lrow["no_mesin"] && $_REQUEST["no_mesin"]){
			$strmsg.="No Mesin tidak boleh diubah jika sudah AR. Silakan hubungi HO<br>";
		}
		if($_REQUEST["no_rangka"]!=$lrow["no_rangka"] && $_REQUEST["no_rangka"]){
			$strmsg.="No Rangka tidak boleh diubah jika sudah AR. Silakan hubungi HO<br>";
		}
		if($_REQUEST["fk_partner_dealer"]!=$lrow["fk_partner_dealer"] && $_REQUEST["fk_partner_dealer"]){
			$strmsg.="Dealer tidak boleh diubah jika sudah AR. Silakan hubungi HO<br>";
		}		
	}
	
	if($lrow["no_sbg"] && $_REQUEST["status_barang"]!=$lrow["status_barang"]){
		$strmsg.="Status barang tidak boleh diubah jika sudah buat kontrak. Silakan batal kontrak<br>";
	}
	
	//$l_success=0;
	
}

function query_additional(){
	$lrow=pg_fetch_array(pg_query("select * from data_gadai.tbltaksir_umum left join data_gadai.tblproduk_cicilan on fk_fatg=no_fatg where no_fatg='".$_REQUEST["no_fatg"]."' and status_approval!='Batal' "));		
	
	if(!$lrow['tgl_cair']){
		
		$no_fatg=$_REQUEST["no_fatg"];
		$score=scoring($no_fatg);
		//echo $score.'sdf';
		
		$tbl="data_gadai.tbltaksir_umum";
		$lwhere="no_fatg='".$no_fatg."'";
		
		if(!pg_query(insert_log($tbl,$lwhere,'UB'))) $l_success=0;
		if(!pg_query("update ".$tbl." SET credit_score_ca='".$score."',credit_score_surveyor='".$score."' where ".$lwhere."")) $l_success=0;
		if(!pg_query(insert_log($tbl,$lwhere,'UA'))) $l_success=0;
		
		//showquery("update ".$tbl." SET credit_score_ca='".$score."',credit_score_surveyor='".$score."' where ".$lwhere."");					
	}
}

?>

