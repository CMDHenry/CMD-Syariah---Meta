<?
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/numeric.inc.php';
require '../requires/validate.inc.php';
require '../requires/timestamp.inc.php';
require '../requires/input.inc.php';
require '../requires/cek_error.inc.php';
require '../requires/module.inc.php';
require '../requires/db_utility.inc.php';
require '../classes/select.class.php';
require '../requires/file.inc.php';


$kd_module=$_REQUEST["kd_module"];
$id_menu=$_REQUEST["id_menu"];
$kd_tabs=$_REQUEST["kd_tabs"];
$kd_tabs2=$_REQUEST["kd_tabs2"];
$kd_tabs3=$_REQUEST["kd_tabs3"];
$nm_tabs=get_rec("skeleton.tblmodule_tabs","kd_tabs","fk_module='".$kd_module."'","no_urut_tabs limit 1");

?>
function fSwitch(p_id,pObj){
	_table = pObj.parentNode
	element = _table.children.length
	//clear all
    //confirm("aaa")
	for(i=0;i<element;i++){
		_table.children[i].style.backgroundColor = "e0e0e0"
		_table.children[i].style.color = "000000"
	}
<?
$lrs_tab=pg_query("select * from skeleton.tblmodule_tabs left join skeleton.tblmodule_tabs_detail on skeleton.tblmodule_tabs.pk_id=skeleton.tblmodule_tabs_detail.fk_module_tabs where fk_module='".$kd_module."' order by no_urut_tabs");

while($lrow_tab=pg_fetch_array($lrs_tab)){
?>
            document.getElementById('div<?=$lrow_tab["kd_tabs"]?>').style.display = "none"
            //confirm('div<?=$lrow_tab["kd_tabs"]?>='+document.getElementById('div<?=$lrow_tab["kd_tabs"]?>').style.display)
<? 
}
?>
	//selected
    <?
	$lrs_tab_main=pg_query("select * from skeleton.tblmodule_tabs left join skeleton.tblmodule_tabs_detail on skeleton.tblmodule_tabs.pk_id=skeleton.tblmodule_tabs_detail.fk_module_tabs where fk_module='".$kd_module."' and no_urut_tabs='0' order by no_urut_tabs");
	//showquery("select * from skeleton.tblmodule_tabs left join skeleton.tblmodule_tabs_detail on skeleton.tblmodule_tabs.pk_id=skeleton.tblmodule_tabs_detail.fk_module_tabs where fk_module='".$kd_module."' and no_urut_tabs='0' order by no_urut_tabs");
	//untuk yang di tab utama
	while($lrow_tab_main=pg_fetch_array($lrs_tab_main)){
		if($lrow_tab_main["tab_detail"]!=""){
	?>
    		if(p_id=="div<?=$lrow_tab_main["kd_tabs"]?>"){
    			document.getElementById('Table_<?=$lrow_tab_main["tab_detail"]?>').style.display="inline-table"
            }else{ 
            	document.getElementById('Table_<?=$lrow_tab_main["tab_detail"]?>').style.display="none"
            }
    <?   }
	} 	?>
	document.getElementById(p_id).style.display = "inline"
	
    <? $lrs_new_tab=pg_query("select * from skeleton.tblmodule_tabs left join skeleton.tblmodule_tabs_detail on skeleton.tblmodule_tabs.pk_id=skeleton.tblmodule_tabs_detail.fk_module_tabs where fk_module='".$kd_module."' and no_urut_tabs!='0' order by no_urut_tabs");
	//untuk yang di tab selain tab utama
		while($lrow_new_tab=pg_fetch_array($lrs_new_tab)){
			if($lrow_new_tab["tab_detail"]!=""){
	?>
                if(p_id=="div<?=$lrow_new_tab["kd_tabs"]?>"){
                    document.getElementById('Table_<?=$lrow_new_tab["tab_detail"]?>').style.display="inline-table"				
                 } else {
                    document.getElementById('Table_<?=$lrow_new_tab["tab_detail"]?>').style.display="none"        
                 }     
       <? 	}
	   } 	?>
	pObj.style.backgroundColor = "000000"
	pObj.style.color = "ffffff"
	//========
}

function fSwitchView(p_id,pObj){
	_table = pObj.parentNode
	element = _table.children.length

	//clear all
	for(i=0;i<element;i++){
		_table.children[i].style.backgroundColor = "e0e0e0"
		_table.children[i].style.color = "000000"
	}
<?
$lrs_tab=pg_query("select * from skeleton.tblmodule_tabs left join skeleton.tblmodule_tabs_detail on skeleton.tblmodule_tabs.pk_id=skeleton.tblmodule_tabs_detail.fk_module_tabs where fk_module='".$kd_module."' order by no_urut_tabs");
//showquery("select * from skeleton.tblmodule_tabs left join skeleton.tblmodule_tabs_detail on skeleton.tblmodule_tabs.pk_id=skeleton.tblmodule_tabs_detail.fk_module_tabs where fk_module='".$kd_module."' order by no_urut_tabs");
//$lrs_tab=pg_query("select * from skeleton.tblmodule_tabs where fk_module='".$kd_module."' order by no_urut_tabs");
/*showquery("select * from skeleton.tblmodule_tabs where fk_module='".$kd_module."' order by no_urut_tabs");*/

while($lrow_tab=pg_fetch_array($lrs_tab)){
?>			
            document.getElementById('div<?=$lrow_tab["kd_tabs"]?>').style.display = "none"
<? 
}
?>
	document.getElementById(p_id).style.display = "inline"
	pObj.style.backgroundColor = "000000"
	pObj.style.color = "ffffff"
	//========
}

function fShowHide(pObj,pKdField){
	div = document.getElementById('menuBar');   
    //confirm(document.getElementById('menuBar').children[0])     
    //fSwitch('div<?=$nm_tabs?>',document.getElementById('menuBar').children[0])
<?
    $lrs_tab_del=pg_query("select * from skeleton.tblmodule_tabs where skeleton.tblmodule_tabs.fk_module='".$kd_module."' and no_urut_tabs !=0 order by no_urut_tabs");
	while($lrow_tab_del=pg_fetch_array($lrs_tab_del)){
?>
            document.getElementById('a_tab_<?=$lrow_tab_del["kd_tabs"]?>').style.display = "none"
            document.getElementById('separator_<?=$lrow_tab_del["kd_tabs"]?>').style.display = "none"   
<?
	}
    pg_result_seek($lrs_tab_del,0);
	while($lrow_tab_del=pg_fetch_array($lrs_tab_del)){
		$lrow_field=pg_fetch_array(pg_query("select * from skeleton.tblmodule_fields where pk_id='".convert_sql($lrow_tab_del["pk_id_module_fields"])."'"));
		if($lrow_field["is_multiple"]=="t"){
?>
			//confirm(document.getElementsByName("<?=$lrow_field["kd_field"]?>")[0].options.length)
			for (x=1;x<document.getElementsByName("<?=$lrow_field["kd_field"]?>")[0].options.length;x++) {
                //confirm(document.getElementsByName("<?=$lrow_field["kd_field"]?>")[x]+"==<?=$lrow_tab_del["kd_tabs"]?>")
                //confirm(document.getElementsByName("<?=$lrow_field["kd_field"]?>")[0].options[x].selected+"/"+document.getElementsByName("<?=$lrow_field["kd_field"]?>")[0].options[x].value+"==<?=$lrow_tab_del["reference_value"]?>")
                if (document.getElementsByName("<?=$lrow_field["kd_field"]?>")[0].options[x].selected && document.getElementsByName("<?=$lrow_field["kd_field"]?>")[0].options[x].value=="<?=$lrow_tab_del["reference_value"]?>") {
                    document.getElementById('a_tab_<?=$lrow_tab_del["kd_tabs"]?>').style.display = "inline"
                    document.getElementById('separator_<?=$lrow_tab_del["kd_tabs"]?>').style.display = "inline"   
                }
			}
<?
 		} else {
?>
			//confirm(document.getElementsByName("<?=$lrow_field["kd_field"]?>")[0]+"==<?=$lrow_tab_del["kd_tabs"]?>")
			//confirm(document.getElementsByName("<?=$lrow_field["kd_field"]?>")[0].value+"==<?=$lrow_tab_del["reference_value"]?>")
            if (document.getElementsByName("<?=$lrow_field["kd_field"]?>")[0].value=="<?=$lrow_tab_del["reference_value"]?>") {
                document.getElementById('a_tab_<?=$lrow_tab_del["kd_tabs"]?>').style.display = "inline"
                document.getElementById('separator_<?=$lrow_tab_del["kd_tabs"]?>').style.display = "inline"   
            }
<?
		}
	}
?>
}
