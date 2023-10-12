<?php
    class recordset{
        var $page;
        var $page_size;
        var $total_page;
        var $query;
        var $name;

        function recordset($p_name,$p_query,$p_page_size=0){
            $this->name = $p_name;
            $this->query =$p_query;
            $this->page_size = $p_page_size;
            if($this->page_size)
                $this->total_page=ceil(pg_num_rows(pg_query($this->query))/$this->page_size);
            else
                $this->total_page=1;
            $this->page = $_REQUEST["recordset_" . $this->name . "_page"];
            if (!$this->page){
                $this->page=1;
            }
        }
        function get_recordset(){
            if ($this->page_size)
                $l_query = $this->query . " limit " . $this->page_size . " offset ". ($this->page-1)*$this->page_size ."";
            else
                $l_query = $this->query;
            $l_rs = pg_query($l_query);
			
            return $l_rs;
        }

        function goto_page($p_page){
            $this->page=$p_page;
            if ($this->page > $this->total_page){
                $this->page = $this->total_page;
            }
			if ($this->page < 1){
                $this->page = 1;
            }
        }

        function goto_next(){
            if ($this->page < $this->total_page){
                $this->page+=1;
            }
        }

        function goto_prev(){
            if ($this->page>1){
                $this->page-=1;
            }
        }

        function goto_last(){
            $this->page=$this->total_page;
        }

        function goto_first(){
            $this->page=1;
        }

        function action($p_action,$p_page=""){
            switch ($p_action){
                case "first":
                    $this->goto_first();
                    break;
                case "prev":
                    $this->goto_prev();
                    break;
                case "next":
                    $this->goto_next();
                    break;
                case "last":
                    $this->goto_last();
                    break;
                case "goto":
                    $this->goto_page($p_page);
                    break;
            }
        }

		function getfunction($ptype){
            switch ($ptype){
                case "first":
                    return "fpaging_".$this->name."('first')";
                    break;
                case "prev":
                    return "fpaging_".$this->name."('prev')";
                    break;
                case "next":
                    return "fpaging_".$this->name."('next')";
                    break;
                case "last":
                    return "fpaging_".$this->name."('last')";
                    break;
            }
		}
        function html(){
            echo "<input type='hidden' name='recordset_" .  $this->name . "_page' value='".$this->page."'>";
        }
    }
?>