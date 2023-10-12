<?php
	function do_link($pID,$pModal,$pProperty='dialogwidth:825px;dialogheight:405px'){
?>
        <a href="#" class="blue" onclick="show_modal('modal_<?=convert_html($pModal)?>.php?id_menu=20140200000252&pstatus=view&id_view=<?=convert_html($pID)?>','<?=$pProperty?>')"><?=convert_html($pID)?></a>
<?
	}
	
	function do_link_chasis($pID,$pModal,$pMesin,$pChasis,$pPolisi,$pProperty='dialogwidth:825px;dialogheight:405px'){
?>
	 <a href="#" class="blue" onclick="show_modal('modal_<?=convert_html($pModal)?>.php?pstatus=edit&no_mesin=<?=convert_html($pMesin)?>&no_chasis=<?=convert_html($pChasis)?>&no_polisi=<?=convert_html($pPolisi)?>','<?=$pProperty?>')"><?=convert_html($pID)?></a>
<?	
	}
?>