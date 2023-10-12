<?php
	include_once('./classes/row.class.php');
    class table {
		var $rows;
		var $row_count;
        var $attributes;

        function table($p_attributes=""){
			$this->rows = array();
			$this->row_count = 0;
			$this->attributes = $p_attributes;
        }

        function html($p_str_form){
            echo "<table ";
			echo $this->attributes;
			echo " >";
            foreach($this->rows as $row){
                $row->html($p_str_form);
            }
            echo "</table>";
        }
		
		function add_row($p_obj_row,$p_row_num = ""){
			if ($p_row_num==""){
				$p_row_num = $this->row_count;
				$this->row_count += 1;
			}
			$this->rows[$p_row_num] = $p_obj_row;			
		}

    }
?>