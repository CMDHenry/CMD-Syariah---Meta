<?php
	include_once('./classes/cell.class.php');
    class row{
        var $cells;
		var $cell_count;
        var $attributes;

        function row($p_attributes=""){
            $this->cells = array();
			$this->cell_count = 0;
			$this->attributes=$p_attributes;
        }
		
		function set_cell_value($p_index,$p_value="",$p_attributes=""){
			$this->cells[$p_index]->value = $p_value;
		}
		
		function add_cell($p_obj_cell,$p_cell_num = ""){
			if ($p_cell_num==""){
				$p_cell_num = $this->cell_count;
				$this->cell_count += 1;
			}
			$this->cells[$p_cell_num] = $p_obj_cell;			
		}

        function html(){
            echo "<tr ".$this->attributes.">";
            foreach ($this->cells as $cell){
                $cell->html();
            }
            echo "</tr>";
        }
    }

    class header_table extends row{
		var $name;
		var $sorting;
		var $type;

        function header_table($p_name="unknown",$p_attributes="",$p_int_col){
			$this->name = $p_name;
			$this->row($p_attributes);
            for ($l_index=0;$l_index<$p_int_col;$l_index++){
				$l_cell = new cell();
				$this->add_cell($l_cell);
            }
			$this->sorting_field = $p_str_name_sort;
			$this->type_field = $p_str_name_type;
            $this->sorting = $_REQUEST["header_table_". $this->name ."_sort"];
            $this->type = $_REQUEST["header_table_". $this->name ."_type"];
        }
		
		function get_cell_length(){
			return count($this->cells);
		}

		function get_sort(){
            return $this->sorting;
		}

		function get_type(){
            return $this->type;
		}

        function html($p_str_form){
            echo "<input type='hidden' name='header_table_". $this->name ."_sort' value='".$this->sorting."'>";
            echo "<input type='hidden' name='header_table_". $this->name ."_type' value='".$this->type."'>";
            echo "<script language='javascript'>";
            echo "function f_header_table_". $this->name  ."_sort(p_sort){";
            echo "if (document.$p_str_form.header_table_". $this->name ."_sort.value==p_sort && document.$p_str_form.header_table_". $this->name ."_type.value=='desc'){document.$p_str_form.header_table_". $this->name ."_type.value='asc'}else {document.$p_str_form.header_table_". $this->name ."_type.value='desc'}";
            echo "document.$p_str_form.header_table_". $this->name ."_sort.value=p_sort;";
            echo "document.$p_str_form.submit()";
            echo "}";
            echo "</script>";
            echo "<tr ".$this->attributes.">";
            foreach ($this->cells as $cell){
                $cell->attributes .= " onclick=\"f_header_table_". $this->name  ."_sort('$cell->value')\" style='cursor:hand;'";
                if ($cell->value==$l_str_sort){
                    $cell->value .= " <img src='images/" . $l_str_type . ".gif'>";
                }
                $cell->html();
            }
            echo "</tr>";
        }
    }

    class header_table_input extends header_table{

        function header_table_input($p_name="unknown",$p_attributes="",$p_int_col){
			$this->header_table($p_name,$p_attributes,$p_int_col);
        }

        function html($p_str_form){
            echo "<input type='hidden' name='header_table_input_". $this->name ."_sort' value='".$this->sorting."'>";
            echo "<input type='hidden' name='header_table_input_". $this->name ."_type' value='".$this->type."'>";
            echo "<script language='javascript'>";
            echo "function f_header_table_input_". $this->name  ."_sort(p_sort){";
            echo "if (document.$p_str_form.header_table_input_". $this->name ."_sort.value==p_sort && document.$p_str_form.header_table_input_". $this->name ."_type.value=='desc'){document.$p_str_form.header_table_input_". $this->name ."_type.value='asc'}else {document.$p_str_form.header_table_input_". $this->name ."_type.value='desc'}";
            echo "document.$p_str_form.header_table_input_". $this->name ."_sort.value=p_sort;";
            echo "document.$p_str_form.submit()";
            echo "}";
            echo "</script>";
            echo "<tr ".$this->attributes.">";
            foreach ($this->cells as $cell){
                $cell->attributes .= " onclick=\"f_header_table_input_". $this->name  ."_sort('$cell->value')\" style='cursor:hand;'";
                if ($cell->value==$l_str_sort){
                    $cell->value .= " <img src='images/" . $l_str_type . ".gif'>";
                }
                $cell->html();
            }
			echo "<td></td>";
            echo "</tr>";
        }
    }
?>