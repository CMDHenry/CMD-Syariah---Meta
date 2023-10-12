<?php
function mRefererCheck($pStr,$pAutoExit=false){	
	$lResult=false;
	//echo $pStr;
	$lArr=split("\|",$pStr);
	for ($lIndex=0;$lIndex<count($lArr);$lIndex++){
		if (strchr(strtolower($_SERVER['HTTP_REFERER']),$lArr[$lIndex])!="")
			$lResult=true;
	}
	if(!$lResult && $pAutoExit)header("location:error_access.php");
	return $lResult;
}
?>