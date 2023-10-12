<?php
    class cell{
        var $value;
        var $attributes;

        function cell($p_value="",$p_attributes=""){
			$this->value = $p_value;
			$this->attributes = $p_attributes;
        }
		
        function html(){
            echo "<td " . $this->attributes . ">";
            echo $this->value;
            echo "</td>";
        }
    }
?>