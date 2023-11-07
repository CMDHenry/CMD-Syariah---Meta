<?php
require 'requires/config.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/accounting_utility.inc.php';
require 'includes/auth.php';

header("Content-type: application/json");

$_SESSION['id']=$_SESSION['username']='Online';
$_SESSION["jenis_user"]='HO';

if($auth=='t'){
	get_data();
}

function get_data(){
    global $strmsg;
	$l_success=1;
	$tgl=date("Y-m-d");
    $input=file_get_contents('php://input');

	$query="
    SELECT
        tblinventory.tgl_cair,
        tblinventory.fk_sbg as no_kontrak,
        tblinventory.fk_cabang as kode_cabang,
        tblinventory.fk_cif as no_customer,
    --    tblinventory.tgl_jt,
        tblcustomer.nm_customer as nama_customer,
        tblcustomer.fk_kelurahan_tinggal as kode_kelurahan,
        tblkelurahan.nm_kelurahan as nama_kelurahan,
        tblkecamatan.kd_kecamatan as kode_kecamatan,
        tblkecamatan.nm_kecamatan as nama_kecamatan,
        tblkota.kd_kota as kode_kota,
        tblkota.nm_kota as nama_kota,
        viewang_ke.nilai_angsuran as nominal_angsuran,
        viewang_ke.ang_ke as angsuran_ke,
        viewang_ke.top as tgl_jto
    FROM tblinventory
    LEFT JOIN tblcustomer ON tblinventory.fk_cif = tblcustomer.no_cif
    LEFT JOIN viewkontrak ON tblinventory.fk_sbg = viewkontrak.no_sbg
    LEFT JOIN viewang_ke ON viewang_ke.fk_sbg = tblinventory.fk_sbg
    LEFT JOIN tblkelurahan ON tblcustomer.fk_kelurahan_tinggal = tblkelurahan.kd_kelurahan
    LEFT JOIN tblkecamatan ON kd_kecamatan = fk_kecamatan
    LEFT JOIN tblkota ON kd_kota = fk_kota
    WHERE tblinventory.tgl_cair IS NOT NULL
        AND tblinventory.status_sbg = 'Liv'
        AND viewang_ke.top < '".$tgl."'";
	//showquery($query1);
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	
	if ($l_success==1) {
		$outp .= '{';
		$outp .= '"tgl_cair":"'  . $lrow["tgl_cair"] . '",';
		$outp .= '"no_kontrak":"'  . $lrow["no_kontrak"] . '",';
		$outp .= '"kode_cabang":"'  . $lrow["kode_cabang"] . '",';
		$outp .= '"no_customer":"'  . $lrow["no_customer"] . '",';		
		$outp .= '"nama_customer":"'  . $lrow["nama_customer"] . '",';				
		$outp .= '"kode_kelurahan":"'  . $lrow["kode_kelurahan"] . '",';		
		$outp .= '"nama_kelurahan":"'  . $lrow["nama_kelurahan"] . '",';		
		$outp .= '"kode_kecamatan":"'  . $lrow["kode_kecamatan"] . '",';		
		$outp .= '"nama_kecamatan":"'  . $lrow["nama_kecamatan"] . '",';		
		$outp .= '"kode_kota":'  . $lrow["kode_kota"] . ',';
		$outp .= '"nama_kota":"'  . $lrow["nama_kota"] . '",';				
		$outp .= '"nominal_angsuran":"'  . $lrow["nominal_angsuran"] . '",';				
		$outp .= '"angsuran_ke":"'  . $lrow["angsuran_ke"] . '",';				
		$outp .= '"tgl_jto":"'  . $lrow["tgl_jto"] . '",';		
		
		$outp=rtrim($outp,',');
		$outp .= '}';
	} else {
		$strmsg=str_replace("<br>"," ,",$strmsg);
		$strmsg=trim($strmsg,',');
		$outp  = '';
		$outp .= '{"message": "'.$strmsg.' .Silakan hubungi cabang terdekat"}';
	}	
	echo $outp;
	//echo date("H:i:s").'<br>';

	if(!pg_query("insert into tblapi_log(id,log_date,success,source,input,message,trx_id) values ('".$fk_sbg."','".date("Y/m/d H:i:s")."','".$l_success."','".$_SERVER['PHP_SELF']."','".$input."','".$strmsg."','".$_REQUEST["trx_id"]."')"))$l_success=0;
}


?>