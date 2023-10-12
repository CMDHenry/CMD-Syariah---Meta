<?
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/convert.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/file.inc.php';
require '../requires/numeric.inc.php';
require '../requires/referer_check.inc.php';
require '../classes/recordset_ajax.class.php';

if (!isset($_SESSION['id']) ){
	//echo "err:1000 ";
} else{
	//echo $GLOBALS["HTTP_RAW_POST_DATA"];
	if (isset($GLOBALS["HTTP_RAW_POST_DATA"])) {
		$larr=split('&',$GLOBALS["HTTP_RAW_POST_DATA"]);
		$imageData = $GLOBALS['HTTP_RAW_POST_DATA'];
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);
		$unencodedData = base64_decode($filteredData);
		//echo $unencodedData;
		$id_edit=$larr[1];
		//echo $larr[2];
		$imgName=$id_edit.".png";
		
		$tbl=$larr[2];
		$field_pic=$larr[3];
		$pk_id=$larr[4];	
		
		if($field_pic=="pic_id"){
			$no_id=get_rec("tblcustomer","no_id","".$pk_id." ='".$id_edit."'");
			$imgName=$no_id.".png";
		}
		if($tbl=="data_gadai.tbltaksir"){
			if(pg_num_rows(pg_query("select * from data_gadai.tbltaksir_umum where ".$pk_id."='".$id_edit."' "))){
				$tbl="data_gadai.tbltaksir_umum";
			}
		}
		
		
		if(!pg_query("insert into ".$tbl."_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UB' from ".$tbl." where ".$pk_id."='".$id_edit."' "))$l_success=0;
		
		if(!pg_query("update ".$tbl." set ".$field_pic." ='".$imgName."' where ".$pk_id."='".$id_edit."'")) $l_success=0;
		//echo "update ".$tbl." set ".$field_pic." ='".$imgName."' where ".$pk_id."='".$id_edit."'";
		
		if(!pg_query("insert into ".$tbl."_log select *, '".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','UA' from ".$tbl." where ".$pk_id."='".$id_edit."' "))$l_success=0;
		
		$fp = fopen($upload_path_pic.$imgName, 'wb');
		
		fwrite($fp, $unencodedData);
		fclose($fp);
	}

}


