<?
//Input titipan
function cek_error_module(){
	global $strmsg;
	
	$fk_sbg=$_REQUEST["fk_sbg"];
	if(!pg_num_rows(pg_query("select * from tblinventory where fk_sbg='".$fk_sbg."' and status_sbg='Liv'"))){
		$strmsg.="Data tidak ada / sudah lunas<br>";
	}	
		
	$total=round(str_replace(',','',$_REQUEST["total"]));
	if($total<=0){
		$strmsg.="Total Kosong<br>";
	}
	
/*	$saldo_titipan=get_rec("data_fa.tbltitipan","saldo_titipan","fk_sbg='".$fk_sbg."'");	
	$nilai_pokok=get_rec("viewsbg","pokok_awal","fk_sbg1='".$fk_sbg."'");	
	
	if($saldo_titipan+$total>$nilai_pokok){
		$strmsg.="Jumlah titipan ".($saldo_titipan+$total)." melebihi nilai pokok ".$nilai_pokok."<br>";
	}	
*/

}

function save_additional(){
	global $l_success;	
	
	$no_voucher=$_REQUEST["no_voucher"];
	$total=round(str_replace(',','',$_REQUEST["total"]));
	$tgl_bayar=convert_date_english($_REQUEST['tgl_voucher']);
	
	$fk_sbg=$_REQUEST["fk_sbg"];
	$fk_bank=$_REQUEST["fk_bank"];
	
	$fk_cabang=get_rec("tblinventory","fk_cabang","fk_sbg='".$fk_sbg."'");
	$fk_cabang_input=$_REQUEST['fk_cabang_input'];
	
	$account 						= get_coa_bank($fk_bank,$fk_cabang_input);
		
	$arrPost = array();
	$arrPost["bank"]				= array('type'=>'d','value'=>$total,'reference'=>$no_voucher,'account'=>$account);
	
	if($fk_cabang!=$fk_cabang_input){		
		$rpkc 	  = get_coa_cabang($fk_cabang,$fk_cabang_input);
		$arrPost["rpkc"]			= array('type'=>'c','value'=>$total,'reference'=>$no_voucher,'account'=>$rpkc);		
		if(!posting('INPUT TITIPAN',$fk_sbg,$tgl_bayar,$arrPost,$fk_cabang_input,'00'))$l_success=0;
		if($fk_cabang_input==cabang_ho){
			$arrPost = array();
			$arrPost["rpkp"]			= array('type'=>'d','value'=>$total,'reference'=>$no_voucher);		
		}else{
			$rpkc_keluar				= get_coa_cabang($fk_cabang_input,cabang_ho);
			$rpkc_masuk					= get_coa_cabang($fk_cabang,cabang_ho);	
				
			$arrPost = array();
			$arrPost["rpkc_masuk"]		= array('type'=>'d','value'=>$total,'reference'=>$no_voucher,'account'=>$rpkc_masuk);	
			$arrPost["rpkc_keluar"]		= array('type'=>'c','value'=>$total,'reference'=>$no_voucher,'account'=>$rpkc_keluar);
			//cek_balance_array_post($arrPost);
			if(!posting('INPUT TITIPAN',$fk_sbg,$tgl_bayar,$arrPost,cabang_ho,'00'))$l_success=0;		
			
			$arrPost = array();
			$arrPost["rpkp"]			= array('type'=>'d','value'=>$total,'reference'=>$no_voucher);					
		}
	}		
	
	$arrPost["titipan_angsuran"]	= array('type'=>'c','value'=>$total,'reference'=>$no_voucher);	
	//cek_balance_array_post($arrPost);
	if(!posting('INPUT TITIPAN',$fk_sbg,$tgl_bayar,$arrPost,$fk_cabang,'00'))$l_success=0;
	
	$ket="Input Titipan";		
	if(!pg_query(update_saldo_bank($fk_bank,$fk_cabang,$total,0,$ket,$fk_sbg,$no_voucher)))$l_success=0;
	//showquery(update_saldo_titipan($fk_sbg,$total));
	if(!pg_query(update_saldo_titipan($fk_sbg,$total)))$l_success=0;
	//$l_success=0;
}

?>

