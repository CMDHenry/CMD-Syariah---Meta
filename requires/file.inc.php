<?php
if($_SERVER['DOCUMENT_ROOT']=="D:/Development/Web Project"){
	$url_folder="/capella";
	
}else $url_folder="";
//$url_folder="/sam";
//echo $_SERVER['DOCUMENT_ROOT'];
$upload_path = $_SERVER['DOCUMENT_ROOT'].$url_folder."/website/file";
$upload_path_website_pic = $_SERVER['DOCUMENT_ROOT'].$url_folder."/website/images/upload";
$upload_path_profile_pic = $_SERVER['DOCUMENT_ROOT'].$url_folder."/website/images/profile_pic";	
$path_site = $_SERVER['DOCUMENT_ROOT'].$url_folder."/website/";
$upload_path_pic = $_SERVER['DOCUMENT_ROOT'].$url_folder."/website/upload/img_cust/";
$upload_path_pdf = $_SERVER['DOCUMENT_ROOT'].$url_folder."/website/upload/pdf/";

//echo $upload_path;
$_filesize= ini_get("upload_max_filesize")."b";

function cek_file($filename,$filetype="all"){ //ini bisa digabung dengan picture
	if ($_FILES[$filename]["error"]==4){
		return 1;
		//"File tidak ditemukan<br>"
	}else if ($_FILES[$filename]["error"]==1){
		return 2;
		//"Ukuran file lebih dari batas maksimum<br>"
	}else if($_FILES[$filename]["size"]<=0){
		return 1;
	}else if($filetype!="all" && $filetype!="picture"){
		$ketemu=0;
		$ext=split("\.",$_FILES[$filename]["name"]);
		$ext=$ext[count($ext)-1];
		$arrfiletype=split("/",$filetype);
		for($i=0;$i<count($arrfiletype);$i++){
			if(strtoupper($ext)==strtoupper($arrfiletype[$i])) $ketemu=1;
		}
		if($ketemu===0) return 3;
	}else if($filetype=='picture'){
		$type = $_FILES[$filename]['type'];
		echo $type;
		switch($type){
			case "image/jpeg" : return 0; break;
			case "image/jpg" : return 0; break;
			case "image/gif" : return 0; break;
			default : return 3; break;
		}
	}
	return 0;
}

function cek_name($pname){
	return strrpos($pname,"/")!==FALSE || strrpos($pname,"\\")!==FALSE || strrpos($pname,":")!==FALSE || strrpos($pname,"*")!==FALSE || strrpos($pname,"?")!==FALSE || strrpos($pname,"\"")!==FALSE || strrpos($pname,"<")!==FALSE || strrpos($pname,">")!==FALSE || strrpos($pname,"|")!==FALSE;
}

function findexts ($filename) { 
	$filename = strtolower($filename) ; 
	$exts = split("[/\\.]", $filename) ; 
	$n = count($exts)-1; 
	$exts = $exts[$n]; 
	return $exts; 
}

/*function create_log($nama_file,$ext_file,$index,$record_gagal,$alasan_reject){
	if(!pg_query("
		insert into data_it.tbllog (
		no_file,jenis_file,jmlh_record,jmlh_record_gagal,alasan_reject,status_log,
		log_action_userid,log_action_username,log_action_date,
		log_action_mode,log_action_from)
		values('".convert_sql($nama_file)."','".convert_sql($ext_file)."','".convert_sql(($index))."','".convert_sql($record_gagal)."','".convert_sql($alasan_reject)."','".($record_gagal==0?"Complete":"Not Complete")."',
		'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA','".$_SERVER['SCRIPT_NAME']."'
	)")) $l_success=0;
}*/	
?>