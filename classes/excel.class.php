<?php
// simple excel writer

class XLS {

	var $fileName;
	var $xlsData="";
	
	function XLS($fileName="Report Excel.xls"){
		$this->fileName = $fileName;
		$this->xlsSOF();
	}
	
	function xlsSOF() {
		$this->xlsData .= pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
		return;
	}
	
	function xlsEOF() {
		$this->xlsData .= pack("ss", 0x0A, 0x00);
		return;
	}
	
	function xlsWriteNumber($Row, $Col, $Value) {
		$this->xlsData .= pack("sssss", 0x203, 14, $Row, $Col, 0x0);
		$this->xlsData .= pack("d", $Value);
		return;
	}
	
	function xlsWriteLabel($Row, $Col, $Value ) {
		$L = strlen($Value);
		$this->xlsData .= pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
		$this->xlsData .= $Value;
		return;
	}
	//nulis output ke file

	function xlsOutput($upload_path, $folder ){
		$this->xlsEOF();
		$ourFileName = $upload_path."/".$folder."/".$this->fileName.".xls";
		//echo $ourFileName;
		$ourFileHandle = fopen($ourFileName, 'w') or die("can't open file");
		fwrite($ourFileHandle, $this->xlsData);
		fclose($ourFileHandle);
	}	
}
?>