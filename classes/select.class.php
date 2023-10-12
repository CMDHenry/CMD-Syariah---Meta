<?php
    //require /requeires/config.inc.php
    class select_item{
        var $name;
        var $key;

        //constructor
        function select_item($p_name,$p_key){
           $this->name = $p_name;
           $this->key = $p_key;
        }
    }

    class select{
        var $items;
        var $selected_items;
        var $select_name;
        var $is_multiple;
        var $value;

        //constructor
        function select($p_query,$p_field_show,$p_field_index,$p_select_name,$p_multiple=false){
            $this->select_name = $p_select_name;
            $this->is_multiple = $p_multiple;
            $this->items = array();
			//showquery($p_query);
            $res = @pg_query($p_query);
            $index = 0;
            while ($r = @pg_fetch_array($res)){
                $this->items[$index] = new select_item($r[$p_field_show],$r[$p_field_index]);
                $index += 1;
            }
            $this->value=$_REQUEST["select_".$this->select_name."_value"];
            $this->selected_items = array();
            $index= 0;
            foreach($this->items as $item){
                if (strpos(",".$this->value.",",",".$item->key.",")){
                    $this->selected_items[$index] = $item;
                    $index+=1;
                }
            }
        }

        function add_item($p_name,$p_key,$p_index){
            if ($p_index>count($this->items)) die("<br><font color=red>out of range</font>");
            for($l_index=count($this->items);$l_index>=0;$l_index--){
                if ($l_index == $p_index ){
                    $this->items[$l_index] = new select_item($p_name,$p_key);
                }else if($l_index > $p_index){
                    $this->items[$l_index] = $this->items[$l_index-1];
                }
            }
        }

        function set_default_value($p_default_value){
            $this->value = $p_default_value;
            $this->selected_items = array();
            $l_index= 0;
            foreach($this->items as $item){
                if (strpos(",".$this->value.",",",".$item->key.",")){
                    $this->selected_items[$l_index] = $item;
                    $l_index++;
                }
            }
        }

        function html($p_property="",$p_form="form1",$p_onchange=""){
?>
            <script language="javascript">
            function f_select_<?=$this->select_name?>_concat(pObjList){
                lStrValue = ""
                for(var i=0;i<pObjList.options.length;i++){
                    if (pObjList.options[i].selected){
                        lStrValue += pObjList.options[i].value + ","
                    }
                }
                if (lStrValue){
                    lStrValue = lStrValue.substr(0,lStrValue.length-1)
                }
                document.<?=$p_form?>.select_<?=$this->select_name?>_value.value=lStrValue
            }
            </script>
            <input type="hidden" name="select_<?=$this->select_name?>_value" value="<?=$this->value?>">
<?
            echo "<select name='$this->select_name' onchange='f_select_".$this->select_name."_concat(this);".$p_onchange."' $p_property ".(($this->is_multiple)?"multiple":"").">";
            foreach($this->items as $item){
                if (strpos(",".$this->value.",",",".$item->key.",")!==false){
                    echo "<option value='".$item->key."' selected>". $item->name ."</option>";
                }else{
                    echo "<option value='".$item->key."'>". $item->name ."</option>";
                }
            }
            echo "</select>";
        }
        function get_html($p_property="",$p_form="form1",$p_onchange=""){
            $l_return="<script language='javascript'>
            function f_select_".$this->select_name."_concat(pObjList){
                lStrValue =''
               	for(var i=0;i<pObjList.options.length;i++){
                    if (pObjList.options[i].selected){
                        lStrValue += pObjList.options[i].value + ','
                    }
                }
                if (lStrValue){
                    lStrValue = lStrValue.substr(0,lStrValue.length-1)
                }
                document".$p_form."select_".$this->select_name."_value.value=lStrValue
            }
            </script>
            <input type='hidden' name='select_".$this->select_name."_value' value='".$this->value."'>
            <select name='$this->select_name' 
			onchange='".(($this->is_multiple)?"f_select_".$this->select_name."_concat(this);".$p_onchange:$p_onchange)."' 
			".$p_property." ".(($this->is_multiple)?"multiple":"").">";
            foreach($this->items as $item){
                if (strpos(','.$this->value.',',','.$item->key.',')!==false){
                    $l_return.="<option value='".$item->key."' selected>". $item->name ."</option>";
                }else{
                    $l_return.="<option value='".$item->key."'>". $item->name ."</option>";
                }
            }
            $l_return.="</select>";
			return $l_return;
        }
    }
?>