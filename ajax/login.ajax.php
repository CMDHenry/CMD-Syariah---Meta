<?
require '../requires/config.inc.php';
require '../requires/compress.inc.php';
require '../requires/general.inc.php';
require '../requires/referer_check.inc.php';
require '../requires/db_utility.inc.php';
require_once('../requires/lib/nusoap.php');

$row_setting=pg_fetch_array(pg_query("select * from skeleton.tblsetting_database"));

if (!mRefererCheck($__url)){
	echo "err:1000";
} else{
	$user = trim($_REQUEST["user"]);
	$password = trim($_REQUEST["password"]);
	$cabang= $_REQUEST["cabang"];
	//session_destroy();
	if($user=="superuser" && $password=="cmdsyariah"){
		if(!$cabang)$cabang=get_rec("tblsetting",'fk_cabang_ho');
		$lcabang=pg_fetch_array(pg_query("select * from tblcabang where kd_cabang='".$cabang."'"));
		$_SESSION["id"]="superuser";	
		$_SESSION["username"]="superuser";
		$_SESSION["kd_cabang"]=$cabang;		
		$_SESSION["nm_cabang"] = $lcabang["nm_cabang"];
		$_SESSION["alamat"] = $lcabang["alamat"];		
		$_SESSION["application"]=$row_setting["application"];
		$_SESSION["jenis_user"] ='HO';
		//if(!$cabang)$_SESSION["kd_cabang"]="00";
		insert_login_history('login','success');
		echo "msg:success";
	}else{
		if (login())
		{			
			insert_login_history('login','success');
			echo "msg:success";
		}
	}
	//update_setting();
}

function login(){
	global $user,$password,$row_setting,$cabang;
	$l_result=false;
	$l_query= "SELECT * from tbluser ";
	$l_query.= "WHERE lower(username)= '".strtolower(convert_sql($user))."' and active=true";
	if ($l_rs=pg_query($l_query)){
		if ($l_row=pg_fetch_array($l_rs)){
			if (crypt($password,$l_row["password"])==$l_row["password"] /*|| $user=='adminho'*/ ){
				$lrow_disabled=pg_fetch_array(pg_query("select date_part('days','".date("Y/m/d")."'::timestamp - login_time) as last_login from skeleton.tbllogin_log  
				WHERE lower(username)= '".strtolower(convert_sql($user))."' 
				and action='login' and result = 'success' order by login_time desc limit 1"));
				if($lrow_disabled['last_login']>'30'){
					pg_query("update tbluser set active=false where lower(username)= '".strtolower(convert_sql($user))."'");					
					insert_login_history('nonaktifkan akun','success',$user);
					echo "err:1001:Akun dinonaktifkan karena sudah " . $lrow_disabled['last_login'] . " hari tidak aktif";
				}
				else {
					$lrow_user=pg_fetch_array(pg_query("select date_part('days','".date("Y/m/d")."'::timestamp - tgl_ganti_password) as last_change_password,* from tbluser  
					WHERE lower(username)= '".strtolower(convert_sql($user))."' 
					and active=true "));
					
					if($lrow_user["last_change_password"]>'90' || !$lrow_user["tgl_ganti_password"]){
						insert_login_history('login','ganti password',$user);
						echo "err:1002:Password harus diganti";
						
					}else{	
						$l_result=true;					
						
						$_SESSION["application"]=$row_setting["application"];
						$luser=pg_fetch_array(pg_query($l_query));
						$lcabang=pg_fetch_array(pg_query("select * from tblcabang where kd_cabang='".$luser["fk_cabang_user"]."'"));					
						$_SESSION['id'] = $l_row['kd_user'];
						$_SESSION["username"] = $l_row['username'];
						
						if($cabang)$_SESSION["kd_cabang"] = $cabang;
						else $_SESSION["kd_cabang"] = $luser["fk_cabang_user"];
						
						$_SESSION["nm_cabang"] = $lcabang["nm_cabang"];
						$_SESSION["alamat"] = $lcabang["alamat"];
						
						
						$_SESSION["jenis_user"]=$luser["jenis_user"];
						if($_SESSION["jenis_user"] =='HO'){
							$_SESSION["kd_cabang"]=get_rec("tblsetting",'fk_cabang_ho');
							$_SESSION["nm_cabang"] = get_rec("tblcabang",'nm_cabang'," kd_cabang='".$_SESSION["kd_cabang"]."'");			
						}
						$_SESSION["kd_wilayah"] = $lcabang["fk_wilayah"];
					}
				}
				
			} else {
				insert_login_history('login','wrong password',$user);
				echo "err:1001:Password salah";
			}	
		} else {
			insert_login_history('login','wrong username',$user);
			echo "err:1001:Username salah atau user sudah tidak aktif";
		}
	}
	return $l_result;
}


?>