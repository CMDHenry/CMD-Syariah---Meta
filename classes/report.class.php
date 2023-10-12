<?
include_once('ezpdf.class.php');

class report extends Cezpdf{

	function report($paper='a4',$orientation='portrait',$fontSize=10,$margin=array(30,30,30,30)){

		$this->Cezpdf($paper,$orientation,$fontSize,$margin);
	}
	
	function ezPrvtTableColumnHeadings($cols,$pos,$maxWidth,$height,$gap,$size,&$y,$options=array()){
	  // uses ezText to add the text, and returns the height taken by the largest heading
	
	  $mx=0;
	  foreach($cols as $colName=>$colHeading){
		$this->ezSetY($y);
		if (isset($options[$colName]) && isset($options[$colName]['justification'])){
		  $justification = $options[$colName]['justification'];
		} else {
		  $justification = 'left';
		}
		//print_r($pos);		
		$this->ezText($colHeading,$size,array('aleft'=> $pos[$colName],'aright'=>($maxWidth[$colName]+$pos[$colName]),'justification'=>$justification));
		$dy=$y-$this->y;
		if ($dy>$mx){
		  $mx=$dy;
		}
	  }

	  $y=$y-$height;
	  $y -= $gap;
	  return $mx;
	}
	// ------------------------------------------------------------------------------
	
	function ezPrvtTableHeadingsDrawLines($pos,$gap,$x0,$x1,$y0,$y1,$y2,$col){
		$x0=1000;
		$x1=0;
		$this->setStrokeColor($col[0],$col[1],$col[2]);
		foreach($pos as $x){
			//$this->ezText(($x-$gap/2).",".$y0.",".($x-$gap/2).",".$y1);
			$this->line($x-$gap/2,$y0,$x-$gap/2,$y1);
			if ($x>$x1){ $x1=$x; };
			if ($x<$x0){ $x0=$x; };
		}
		$this->line($x0-$gap/2,$y0,$x1-$gap/2,$y0);
	}
	
	// ------------------------------------------------------------------------------

}
?>