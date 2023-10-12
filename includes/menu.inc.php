<ul class="menu" id="menu">
<?
//query submenu utility (master,master unit,...)
$rs=pg_query("select * from skeleton.tblmenu where fk_parent='".$parent_root."' and is_action = 'f' and is_hidden='t' order by kd_menu asc");
//showquery("select * from skeleton.tblmenu where fk_parent='".$parent_root."' and is_action = 'f' and is_hidden='t'  order by kd_menu asc");
while($lrow=pg_fetch_array($rs)){
	
	if (check_right($lrow["kd_menu"],false)) {
		
?>	<li name="menu_<?=$lrow["kd_menu"]?>" style="left:0">
		<ul>
        	<? 
			//query menu submenu (karyawan,dealer,vendor,..)
			$rs_child=pg_query("select * from skeleton.tblmenu where fk_parent='".$lrow["pk_id"]."' and is_hidden='t' order by kd_menu asc");
			//echo "select * from skeleton.tblmenu where fk_parent='".$lrow["pk_id"]."' and is_hidden='t' order by kd_menu asc"
			//showquery("select * from skeleton.tblmenu where fk_parent='".$lrow["pk_id"]."' and is_hidden='t' order by kd_menu asc");
			while($lrow_child=pg_fetch_array($rs_child)){
				if (check_right($lrow_child["kd_menu"],false)) {
			?>	
            	<li>
				<?
                    fMenu($lrow_child["pk_id"],$lrow_child["nama_menu"],$lrow_child["jenis_module"],$lrow_child["nm_file_module_other"]);	
                ?>	      
             	</li>
            <?
				}
			}
			?>
          </ul>         	
     </li>                
<? 
	}
}
?>
</ul>

<?
function fMenu($pk_id,$nm_menu,$type_action,$nm_file_module_other){
	
	//query submenu (divisi,jabatan,karyawan)
	$rs_child_child=pg_query("select * from skeleton.tblmenu where fk_parent='".$pk_id."' and (type_action='module' or is_action is false)and is_hidden='t' order by kd_menu asc");
	//showquery("select * from skeleton.tblmenu where fk_parent='".$pk_id."' and (type_action='module' or is_action is false) order by kd_menu asc");
	//$lrow_child=pg_fetch_array($rs_child_child);
	if(pg_num_rows($rs_child_child)){
		//if (!check_right($lrow_child["kd_menu"],false)) {
?>
	<span class="selectMenu" style="padding:5px 7px 7px"><?=strtoupper($nm_menu)?></span>
	<!--<span style="padding-left:30px">--><img align="absmiddle;right" src="images/right.gif" style="padding-right:10px" /><!--</span>-->
	<ul>
		<?
		while($lrow_child_child=pg_fetch_array($rs_child_child)){
			//echo $lrow_child_child["nama_menu"];
			if (check_right($lrow_child_child["kd_menu"],false)) {	
		?>
			<li>
            	<? fMenu($lrow_child_child["pk_id"],$lrow_child_child["nama_menu"],$lrow_child_child["jenis_module"],$lrow_child_child["nm_file_module_other"]);?>
			</li>	
		<?
			}
		}
		?>    
	</ul>
	<? 
		//}
	}else{
		if ($type_action=="list") $l_link="list.php";
		else if ($type_action=="setting") $l_link="setting.php";
		elseif ($type_action=="list_select") $l_link="list_select.php";
		elseif ($type_action=="add") $l_link="modal_add.php";
		elseif ($type_action=="edit") $l_link="modal_edit.php";
		elseif ($type_action=="other") $l_link=$nm_file_module_other;
		//str_replace(" ","_",strtolower($nm_menu)).".php"
		//echo strtoupper($nm_menu);
		//if (!check_right($lrow_child["kd_menu"],false)) {
	?>
    	<? //=$l_link?><!--?module=--> <? //=$pk_id?>
		
		<a href="<?=$l_link?>?module=<?=$pk_id?>" class="selectMenu"><?=strtoupper($nm_menu)?></a>
<?
		//}
	}
}
?>
<script language="javascript">
	var menu=new menu.dd("menu");
	menu.init("menu","menuhover");
</script>
