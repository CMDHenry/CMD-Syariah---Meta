<?php
class checkbox{
	var $query;
	var $field_key;
	var $field_show;
	var $checkbox_name;
	var $number_of_cells;
	var $value;
	var $reset_button;
	var $is_disable;
	var $array_checkbox = array();
	var $array_checkbox_keterangan = array();

	//constructor
	function checkbox($p_query,$p_field_key,$p_field_show,$p_name,$p_value,$p_disable=0,$p_number_of_cells=4,$p_create_reset_button=0){
		$this->query = $p_query; // query
		$this->field_key = $p_field_key; // yg jadi key utk value checkbox
		$this->field_show = $p_field_show; // text yg akan kluar di sebeleh checkbox
		$this->checkbox_name = $p_name; //nama checkbox
		$this->value = $p_value; //value untuk set default checkbox nya ke centang atau gak
		$this->reset_button = $p_create_reset_button; //kluarin reset button atau gak
		$this->number_of_cells = $p_number_of_cells; // number of cells dalam table harus genap klo gak berantakan
		$this->is_disable = $p_disable; //nama checkbox
	}

	//output
	function html($p_onkeyup="",$p_form="form1",$p_onclick=""){
		$lrs = pg_query($this->query); // query biasa
		$llength = pg_num_rows($lrs)-1; // nyari jumlah data yg kluar
		$lIndex=0; // index
		$lCells=0; // cells
		$lwidth = (200/$this->number_of_cells)-1; //width nya setial cells yg akan nampung text
		$this->setisi(); //set isi ke dalam array berdasarkan this->value
		?>
        <script>
		//ini buat onclick, nti akan ngisi kedalam 1 text hidden yang gunanya untuk nyimpan data sementara
		function f_onclick_checkbox_<?=$this->checkbox_name?> (){
			document.<?=$p_form?>.input_text_<?=$this->checkbox_name?>.value = ''
			for(var i=0;i<document.<?=$p_form?>.<?=$this->checkbox_name?>.length;i++){
				if(document.<?=$p_form?>.<?=$this->checkbox_name?>[i].checked==true){
					document.<?=$p_form?>.input_text_<?=$this->checkbox_name?>.value += document.<?=$p_form?>.<?=$this->checkbox_name?>[i].value+"<?=chr(187)?>";
					if(document.getElementById('input_text_<?=$this->checkbox_name?>'+'_'+document.<?=$p_form?>.<?=$this->checkbox_name?>[i].value)){
						document.<?=$p_form?>.input_text_<?=$this->checkbox_name?>.value += document.getElementById('input_text_<?=$this->checkbox_name?>'+'_'+document.<?=$p_form?>.<?=$this->checkbox_name?>[i].value).value;
					}
					
					document.<?=$p_form?>.input_text_<?=$this->checkbox_name?>.value += "<?=chr(191)?>";
				}else{
					if(document.getElementById('input_text_<?=$this->checkbox_name?>'+'_'+document.<?=$p_form?>.<?=$this->checkbox_name?>[i].value)){
						document.getElementById('input_text_<?=$this->checkbox_name?>'+'_'+document.<?=$p_form?>.<?=$this->checkbox_name?>[i].value).value='';
					}
				}
			}
		}

		function f_reset_checkbox_<?=$this->checkbox_name?>(){
			for(var i=0;i<document.<?=$p_form?>.<?=$this->checkbox_name?>.length;i++){
				document.<?=$p_form?>.<?=$this->checkbox_name?>[i].checked=false;
				document.<?=$p_form?>.<?=$this->checkbox_name?>[i].onclick();
			}
			document.<?=$p_form?>.input_text_<?=$this->checkbox_name?>.value="";
		}
		</script>
        <input type="hidden" name="input_text_<?=$this->checkbox_name?>" value="<?=convert_html($this->value)?>" style="width:99%">
        <table cellpadding="0" cellspacing="1" border="0" width="100%">
        <?
		if($this->reset_button){
		?>
			<tr bgcolor="efefef"><td colspan="<?=$this->number_of_cells?>" style="padding:0 5 0 5"><a href="#" class="blue" onClick="f_reset_checkbox_<?=$this->checkbox_name?>()">Reset</a></td></tr>
		<?
		}
		while($lrow = pg_fetch_array($lrs)){
			if($lCells % $this->number_of_cells==0 || $lCells==0){ // row open
		?>
        	<tr bgcolor="efefef">
		<? 
			}
			//nyari next focus
			if($llength==$lIndex) $l_onkeyup = (($lrow['is_create_input']=='t')?"fNextFocus(event,document.".$p_form.".input_text_".$this->checkbox_name."_".$lrow[$this->field_key].")":$p_onkeyup);
			else $l_onkeyup = "fNextFocus(event,document.".$p_form.".".(($lrow['is_create_input']=='t')?"input_text_".$this->checkbox_name."_".$lrow[$this->field_key]:$this->checkbox_name."[".($lIndex+1)."]").")";
			
		?>
			<td width="15"><input type="checkbox" name="<?=$this->checkbox_name?>" value="<?=$lrow[$this->field_key]?>" class="groove_checkbox" onKeyUp="<?=$l_onkeyup?>" onclick="f_onclick_checkbox_<?=$this->checkbox_name?>(this);" <?=((in_array($lrow[$this->field_key],$this->array_checkbox))?"checked":"")?> <?=(($this->is_disable)?"disabled":"")?> /></td>
			<td width="<?=$lwidth?>%" style="padding:0 5 0 5"><?=$lrow[$this->field_show]?>&nbsp;&nbsp;
		<?
			//kalau dy ada create input -> misalkan pilih lain2 harus input keterangan
			if($lrow['is_create_input']=='t'){
				//nyari next focus
				if($llength==$lIndex) $l_onkeyup = $p_onkeyup;
				else $l_onkeyup = "fNextFocus(event,document.".$p_form.".".(($lrow['is_create_input']=='f')?"input_text_".$lrow[$this->field_key]."_".$this->checkbox_name:$this->checkbox_name."[".($lIndex+1)."]").")";

				//isi text field
				$isi_text_field = '';
				if($_REQUEST['input_text_'.$lrow[$this->field_key].'_'.$this->checkbox_name])$isi_text_field = $_REQUEST['input_text_'.$lrow[$this->field_key].'_'.$this->checkbox_name];
				else $isi_text_field = $this->array_checkbox_keterangan[$lrow[$this->field_key]];
		?>

		<script>
		//nti otomatis kecentang kalau diinpput atau dikosongin textbox nya
		function f_input_text_<?=$lrow[$this->field_key].'_'.$this->checkbox_name?>_keyup (pObj){
			if(pObj.value=='')document.<?=$p_form?>.<?=$this->checkbox_name?>[<?=$lIndex?>].checked=false;
			else document.<?=$p_form?>.<?=$this->checkbox_name?>[<?=$lIndex?>].checked=true;
		}
		</script>

        	<input type="text" class="groove_text" id="input_text_<?=$this->checkbox_name?>_<?=$lrow[$this->field_key]?>" name="input_text_<?=$this->checkbox_name?>_<?=$lrow[$this->field_key]?>" size="<?=$lwidth?>%" value="<?=convert_html($isi_text_field)?>" onkeyup="f_input_text_<?=$lrow[$this->field_key].'_'.$this->checkbox_name?>_keyup (this);f_onclick_checkbox_<?=$this->checkbox_name?>();<?=$l_onkeyup?>" <?=(($this->is_disable)?"disabled":"")?>/>
		<?
			}
		?>
			</td>
		<?
			$lIndex++;
			$lCells+=2;
			if($lCells % $this->number_of_cells==0){ // row close
		?>
				</tr>
		<?
			}
		}
		if($lCells % $this->number_of_cells != 0){// kalau ganjil bikin cell close
		?>
			<td colspan="2"></td></tr>
		<?
		}
		?>
        </table>
		<?
	}

	function setisi(){ //ngeset array isi
		$l_isi = $this->value;
		$l_arr = split(chr(191),$l_isi);
		for($i = 0; $i < count($l_arr);$i++){
			$l_arr2 = split(chr(187),$l_arr[$i]);
			for($j = 0; $j < count($l_arr2); $j++){
				$this->array_checkbox[$i] = $l_arr2[0];
				$this->array_checkbox_keterangan[$l_arr2[0]] = $l_arr2[1];
			}
		}
	}

}
?>