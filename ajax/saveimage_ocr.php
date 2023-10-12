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
		if($_SERVER['HTTP_HOST']=="sopiga.gadaimulia.co.id:444" || $_SERVER['HTTP_HOST']=="meta-server"){
			$url="http://103.84.192.27:82/";
		}
				
		$url="http://103.84.192.27:82/";
	
		$no_image=date("YmdHis").$_SESSION["kd_cabang"];												
		$imgName=$no_image.".png";											
				
		//create img dummy
		if(file_exists($upload_path_pic.$imgName))unlink($upload_path_pic.$imgName);
		
		$fp = fopen($upload_path_pic.$imgName, 'wb');		
		fwrite($fp, $unencodedData);
		fclose($fp);
		
		//send image dummy ke ocr
		$url_image=$url.'api/ocr_post.php?no_image='.$no_image.'&fk_cabang='.$_SESSION["kd_cabang"];	
		if(dirname(__FILE__)=='/www/erp/gm/ajax'){
			$url_image=$url.'api/ocr_post_gm.php?no_image='.$no_image.'&fk_cabang='.$_SESSION["kd_cabang"];	
		}

		
		$data=file_get_contents($url_image);					
			
		echo $data;
	}

}


