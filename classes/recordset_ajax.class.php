<?php
    class recordset{
        var $page;
        var $page_size;
        var $total_page;
        var $query;
        var $name;

        function recordset($p_name,$p_query,$p_page,$p_page_size=0){
            $this->name = $p_name;
            $this->query =$p_query;
            $this->page_size = $p_page_size;
			$this->total_row=pg_num_rows(pg_query($this->query));
            if($this->page_size && $p_query ){
                $this->total_page=ceil(pg_num_rows(pg_query($this->query))/$this->page_size);
				//showquery($this->query);
			}
            else
                $this->total_page=1;
            $this->page = $p_page;
            if (!$this->page){
                $this->page=1;
            } elseif ($this->page>$this->total_page){
                $this->page=$this->total_page;
			}
			if ($this->page < 1){
                $this->page=1;
			} 
        }
        function get_recordset(){
            if ($this->page_size)
                $l_query = $this->query . " limit " . $this->page_size . " offset ". ($this->page-1)*$this->page_size ."";
            else
                $l_query = $this->query;
			if ($this->query)
				$l_rs = pg_query($l_query);
				//showquery($l_query);
            return $l_rs;
        }
    }
?>