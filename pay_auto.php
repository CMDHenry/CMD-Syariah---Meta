<?php
require 'requires/config.inc.php';
require 'requires/general.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/accounting_utility.inc.php';
//require 'includes/auth.php';

$_SESSION['id']=$_SESSION['username']='Online';
$_SESSION["jenis_user"]='HO';


$query="select * from tblapi_log where message='Internal Server Error' and log_date >='2024-01-01' and trx_id not in(select trx_id from data_fa.tblpembayaran_cicilan where trx_id is not null) limit 5 ";
$lrs=pg_query($query);
while($lrow=pg_fetch_array($lrs)){
	$input=$lrow["input"];
	$tgl_bayar=date("m/d/Y",strtotime($lrow["log_date"]));
//if($auth=='t'){
	get_data($input,$tgl_bayar);
//}

}

function get_data($input,$tgl_bayar){
	global $no_pelunasan,$strmsg;
	$l_success=1;
	
	///$input=file_get_contents('php://input');
/*	$input=	'{
	"id_pel": "20102210501416", 
	"trx_id": "'.date("Ymd.His").'", 
	"trx_location": "ESPAY", 
	"tawidh_amount": 1, 
	"email_phone_number": "-",
	"bank_id":"04" 
	}';
*/	$data=json_decode($input, true);
	//print_r($data);
	//echo $input;
	
	$no_transaksi=$data["id_pel"];
	$arr_cara_bayar=array(
	'MT001'=>'Indomaret','MSTA'=>'Indomaret',
	'MT005'=>'Alfamart','MT006'=>'Alfamart',
	'MT012'=>'Tokopedia',
	'SBCA'=>'Webpay BCA','SCIM'=>'Webpay CIMB','SBSI'=>'Webpay BSI','SBRI'=>'Webpay BRI','SMAN'=>'Webpay MANDIRI','SBNI'=>'Webpay BNI','SPMT'=>'Webpay PERMATA','SQRI'=>'Webpay QRIS','SOVO'=>'Webpay OVO',
	'MCOL'=>'Mobile Collection','SPOS'=>'Pos','TRF'=>'Transfer'
	);
	$cara_bayar=$arr_cara_bayar[$data["trx_location"]];	
	
/*	$arr_webpay=array(
	'SBCA'=>'81','SCIM'=>'82','SBSI'=>'83','SBRI'=>'84','SMAN'=>'85','SBNI'=>'86','SPMT'=>'87','SQRI'=>'88','SOVO'=>'89'
	);	
*/
	if(!$cara_bayar){
		$strmsg.="Cara bayar tidak ada<br>";
		$l_success=0;
	}
	$_REQUEST["trx_id"]=$data["trx_id"];	
	
	//$tgl_bayar=date("m/d/Y");	
	//$tgl_bayar=date("m/d/Y",strtotime('2022-01-20'));	//testing
	//$tgl_input=date("m/d/Y");	
	$tgl_input=$tgl_bayar;
	
	$fk_sbg=get_rec("data_gadai.tbltaksir_umum","no_sbg_ar","no_polisi='".$no_transaksi."' or no_sbg_ar='".$no_transaksi."'");
			
	if(!$fk_sbg){
		$strmsg.="Data tidak ada<br>";
		$l_success=0;
	}else{
		
		$_REQUEST["fk_sbg"]=$fk_sbg;	
		$_REQUEST["tgl_bayar"]=convert_date_indonesia($tgl_bayar);		
		$_REQUEST["tgl_input"]=convert_date_indonesia(($tgl_input));					
				
		require "includes/get_data_pembayaran_cicilan.php";
		
		$nilai_bayar_angsuran=$nilai_angsuran;
		if($cara_bayar=='Indomaret' ||$cara_bayar=='Alfamart' ||$cara_bayar=='Tokopedia'){
			$fk_bank='99';//piutang mst
		}elseif(strstr($cara_bayar,'Webpay')){
			$fk_bank='80';
		}else $fk_bank='';
		$_REQUEST["angsuran_ke"]=$ang_ke;	
		$_REQUEST["nilai_angsuran"]=$nilai_angsuran;		
		$_REQUEST["nilai_bayar_angsuran"]=$nilai_bayar_angsuran;	
		$_REQUEST['overdue']=$overdue;
		$_REQUEST["cara_bayar"]=$cara_bayar;	
		
		$_REQUEST["fk_cabang"]=$fk_cabang;	
		$_REQUEST["fk_cabang_input"]=$fk_cabang_input=cabang_ho;
		$_REQUEST["fk_bank"]=$fk_bank;
		$_REQUEST["saldo_pinjaman"]=$saldo_pinjaman;
		
		$_REQUEST["total_denda_lalu"]=round($total_denda_lalu);
		$_REQUEST["total_denda_kini"]=round($total_denda_kini);
		$_REQUEST["denda_ganti_rugi"]=round($denda_ganti_rugi);
		$_REQUEST["denda_keterlambatan"]=round($denda_keterlambatan);				
				
		$nilai_bayar_denda=round($data["tawidh_amount"]);//open payment boleh isi 0
		//$nilai_bayar_denda=$denda_ganti_rugi+$total_denda_lalu;	//close payment
		if($nilai_bayar_denda>0){
			$nilai_bayar_denda2=$denda_keterlambatan;
		}
		$_REQUEST["nilai_bayar_denda"]=round($nilai_bayar_denda);
		$_REQUEST["nilai_bayar_denda2"]=round($nilai_bayar_denda2);
		//$_REQUEST["biaya_tagih"]=$biaya_tagih;	//kalau nanti masuk biaya tagih
		
		$total_pembayaran=$nilai_bayar_angsuran+$nilai_bayar_denda+$nilai_bayar_denda2;
		$_REQUEST["total_pembayaran"]=$total_pembayaran;
		//print_r($_REQUEST);		
		
		include_once "includes/modal_add_20170800000056.inc.php";
		cek_error_module();
		
		$total_denda=$_REQUEST["total_denda_lalu"] + $_REQUEST["total_denda_kini"];
		if(($_REQUEST["nilai_bayar_denda"] > $total_denda) && $total_denda>0) {
			$strmsg.="Tawidh Amount lebih besar dari tagihan<br>";
			$l_success=0;
		}
		
		if(pg_num_rows(pg_query("select * from data_fa.tblpembayaran_cicilan where trx_id='".$_REQUEST["trx_id"]."'"))){
			echo "select * from data_fa.tblpembayaran_cicilan where trx_id='".$_REQUEST["trx_id"]."'";
			$strmsg.="Trx Id sudah ada<br>";
			$l_success=0;
		}
		
		if(pg_num_rows(pg_query("select * from tblapi_log where trx_id='".$_REQUEST["trx_id"]."' and source like '%cancel%'"))){
			$strmsg.="Trx Id sudah ada cancel<br>";
			$l_success=0;
		}		
		
		if(!pg_num_rows(pg_query("select * from tblbank where kd_bank='".$fk_bank."'"))){
			$strmsg.="Bank ID tidak ada <br>";
			$l_success=0;
		}
		
		if($strmsg)$l_success=0;
	}

	if($l_success==1){
		$_REQUEST["tgl_input"]=convert_date_indonesia(($tgl_input));
		$_REQUEST["tgl_bayar"]=convert_date_indonesia(($tgl_bayar));
		$l_success=save_data();	
	}
	//$l_success=0;
	// Cicilan Ke-'  . $ang_ke . 
	if ($l_success==1) {
		$outp .= '{';
		$outp .= '"trx_id":"'  . $_REQUEST["trx_id"] . '",';
		$outp .= '"description":"",';
		$outp .= '"amount":"'  . $total_pembayaran . '",';
		$outp .= '"admin_fee":" 0 "';		
		$outp .= '}';
	} else {
		if(!$strmsg)$strmsg='Internal Server Error';		
		$strmsg=str_replace("<br>"," ,",$strmsg);
		$strmsg=trim($strmsg,',');
		$outp  = '';
		$outp .= '{"message": "'.$strmsg.'"}';
	}	
	echo $outp;
	
	if(!pg_query("insert into tblapi_log(id,log_date,success,source,input,message,trx_id) values ('".$fk_sbg."','".date("Y/m/d H:i:s")."','".$l_success."','".$_SERVER['PHP_SELF']."','".$input."','".$strmsg."','".$_REQUEST["trx_id"]."')"))$l_success=0;
	
}


function save_data(){
	global $l_success;
	pg_query("BEGIN");
	$l_success=1;

	$query="select nextserial_cabang('A':: text,'".$_REQUEST["fk_cabang"]."')";
	$lrow=pg_fetch_array(pg_query($query));
	$no_kwitansi=$lrow["nextserial_cabang"];
	
	$_REQUEST["no_kwitansi"]=$no_kwitansi;	
		
	$insert="insert into data_fa.tblpembayaran_cicilan (angsuran_ke,cara_bayar,denda_ganti_rugi,denda_keterlambatan,fk_bank,fk_cabang_input,fk_giro,fk_sbg,nilai_bayar_angsuran,nilai_bayar_denda,nilai_bayar_denda2,no_kwitansi,overdue,saldo_pinjaman,tgl_bayar,tgl_input,total_denda_kini,total_denda_lalu,total_pembayaran,trx_id) values ('".$_REQUEST["angsuran_ke"]."','".$_REQUEST["cara_bayar"]."','".$_REQUEST["denda_ganti_rugi"]."','".$_REQUEST["denda_keterlambatan"]."','".$_REQUEST["fk_bank"]."','".$_REQUEST["fk_cabang_input"]."',null,'".$_REQUEST["fk_sbg"]."','".$_REQUEST["nilai_bayar_angsuran"]."','".$_REQUEST["nilai_bayar_denda"]."','".$_REQUEST["nilai_bayar_denda2"]."','".$_REQUEST["no_kwitansi"]."','".$_REQUEST["overdue"]."','".$_REQUEST["saldo_pinjaman"]."','".convert_date_english($_REQUEST["tgl_bayar"])."','".convert_date_english($_REQUEST["tgl_input"])."','".$_REQUEST["total_denda_kini"]."','".$_REQUEST["total_denda_lalu"]."','".$_REQUEST["total_pembayaran"]."','".$_REQUEST["trx_id"]."');";
	if(!pg_query($insert));
	//showquery($insert);
	
	if(!pg_query("insert into data_fa.tblpembayaran_cicilan_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from data_fa.tblpembayaran_cicilan where no_kwitansi='".$no_kwitansi."' "));
	
	
	save_additional();
	//$l_success=0;
	if ($l_success==1) {
		pg_query("COMMIT");
		//pg_query("ROLLBACK");
		return 1;
	} else {
		pg_query("ROLLBACK");
		return 0;
	}	
	
}



?>