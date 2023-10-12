<?
require '../requires/config.inc.php';
require '../requires/compress.inc.php';
require '../requires/referer_check.inc.php';

if (!mRefererCheck("head.php")){
	echo "err:1000";
} else{
	pg_query("
	update skeleton.tblsession set
	logout_date='".date("m/d/Y H:i:s")."'
	where username='".$_SESSION["username"]."'
	");
	
	if (insert_login_history('logout','success')){
		$_SESSION["id"]=null;
		$_SESSION["username"]=null;
		$_SESSION["kd_karyawan"]=null;
		$_SESSION["kd_cabang"]=null;
		$_SESSION["nm_cabang"]=null;
		$_SESSION["schema"]=null;
		$_SESSION["jenis_user"]=null;
		echo "msg:success";
	} else {
		echo "err:1001";
	}
}
?>
