<?
include_once('ezpdf.class.php');

class report extends Cezpdf{

	function report($paper='a4',$orientation='portrait',$fontSize=10,$margin=array(30,30,30,30)){
		$this->Cezpdf($paper,$orientation,$fontSize,$margin);
	}

	function ezTextHeader($text,$size=0,$options=''){
	  // this will add a string of text to the document, starting at the current drawing
	  // position.
	  // it will wrap to keep within the margins, including optional offsets from the left
	  // and the right, if $size is not specified, then it will be the last one used, or
	  // the default value (12 I think).
	  // the text will go to the start of the next line when a return code "\n" is found.
	  // possible options are:
	  // 'left'=> number, gap to leave from the left margin
	  // 'right'=> number, gap to leave from the right margin
	  // 'aleft'=> number, absolute left position (overrides 'left')
	  // 'aright'=> number, absolute right position (overrides 'right')
	  // 'justification' => 'left','right','center','centre','full'
	  // 'new_page' => 0,1
	
	  // only set one of the next two items (leading overrides spacing)
	  // 'leading' => number, defines the total height taken by the line, independent of the font height.
	  // 'spacing' => a real number, though usually set to one of 1, 1.5, 2 (line spacing as used in word processing)
	
	  if (isset($options['new_page'])){
		$new_page=$options['new_page'];
	  } else {
		$new_page = 1;
	  }
	
	  if (isset($options['aleft'])){
		$left=$options['aleft'];
	  } else {
		$left = $this->ez['leftMargin'] + (isset($options['left'])?$options['left']:0);
	  }
	  if (isset($options['aright'])){
		$right=$options['aright'];
	  } else {
		$right = $this->ez['pageWidth'] - $this->ez['rightMargin'] - (isset($options['right'])?$options['right']:0);
	  }
	  if ($size<=0){
		$size = $this->ez['fontSize'];
	  } else {
		$this->ez['fontSize']=$size;
	  }
	  
	  if (isset($options['justification'])){
		$just = $options['justification'];
	  } else {
		$just = 'left';
	  }
	  
	  // modifications to give leading and spacing based on those given by Craig Heydenburg 1/1/02
	  if (isset($options['leading'])) { ## use leading instead of spacing
		$height = $options['leading'];
	  } else if (isset($options['spacing'])) {
		$height = $this->getFontHeight($size) * $options['spacing'];
	  } else {
		$height = $this->getFontHeight($size);
	  }
	
	  $lines = explode("\n",$text);
	  //$this->y=$this->y-$height;
	  $yIndex = $this->y - 15;
	  foreach ($lines as $line){
		$start=1;
		while (strlen($line) || $start){
		  $start=0;
		  if ($new_page && $this->y < $this->ez['bottomMargin']){
			$this->ezNewPage();
		  }
		  $line=$this->addTextWrap($left,$yIndex,$right-$left,$size,$line,$just);
		}
	  }
	  return $this->y;
	}

// ------------------------------------------------------------------------------
	function ezPrvtTableColumnHeadings($cols,$pos,$maxWidth,$height,$gap,$size,&$y,$options=array()){
	  // uses ezText to add the text, and returns the height taken by the largest heading
	  	$flag="";
		$startPos = "";
		$endPos = "";
		$this->ezSetY($y);

		//print_r($pos);

		foreach($cols as $colName=>$colHeading){
			list($nm_colom,$tipe) = explode(" ",$colHeading,2);
			
			if($nm_colom=="Actual")$this->ezTextHeader("Actual",$size,array('aleft'=> $pos['actualN'],'aright'=>($pos['n1N']),'justification'=>'center'));
			elseif($nm_colom=="n-1")$this->ezTextHeader("n-1",$size,array('aleft'=> $pos['n1N'],'aright'=>($pos['n2N']),'justification'=>'center'));
			if($nm_colom=="n-2")$this->ezTextHeader("n-2",$size,array('aleft'=> $pos['n2N'],'aright'=>($pos['n3N']),'justification'=>'center'));
			if($nm_colom=="n-3")$this->ezTextHeader("n-3",$size,array('aleft'=> $pos['n3N'],'aright'=>($pos['_end_']),'justification'=>'center'));
		}

		$dy=$y-$this->y;
		if ($dy>$mx){
		  $mx=$dy;
		}
		
		$y=$y-$height;
		$y -= $gap;

		$mx=0;
		foreach($cols as $colName=>$colHeading){
			$this->ezSetY($y);
			if (isset($options[$colName]) && isset($options[$colName]['header_justification'])){
				$justification = $options[$colName]['header_justification'];
			} else {
				$justification = 'center';
			}
			//print_r($pos);	
			list($nm_colom,$tipe) = explode(" ",$colHeading,2);	
			if($tipe=="N" || $tipe=="%")$heading = $tipe;
			else $heading = $colHeading;

			$this->ezText($heading,$size,array('aleft'=> $pos[$colName],'aright'=>($maxWidth[$colName]+$pos[$colName]),'justification'=>$justification));
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
		$y0_1 = $y0 - (($y0 - $y1)/2);
		$y0_1 += 0;
		$startLine="";
		//print_r($pos);
		foreach($pos as $name=>$x){
			//if($startLine=="" && $name != "area" && $name != "cabang" && $name != "status")$startLine = $name;
			if( $name == "cabang" || $name == "actualN" || $name == "n1N" || $name == "n2N" || $name == "n3N" )$y0_1+=15;
			//echo ($x-$gap/2).",".$y0_1.",".($x-$gap/2).",".$y1."<BR>";
			$this->line($x-$gap/2,$y0_1,$x-$gap/2,$y1);
			if ($x>$x1){ $x1=$x; };
			if ($x<$x0){ $x0=$x; };
			if( $name == "cabang" || $name == "actualN" || $name == "n1N" || $name == "n2N" || $name == "n3N" )$y0_1-=15;
		}
		$x=$pos['_end_'];
		$this->line($x-$gap/2,$y0,$x-$gap/2,$y1);

		$this->line($pos[actualN]-$gap/2,$y0_1,$pos["_end_"]-$gap/2,$y0_1);
		$this->line($x0-$gap/2,$y0,$x1-$gap/2,$y0);
	}
	// ------------------------------------------------------------------------------
}
?>
