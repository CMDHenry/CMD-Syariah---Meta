<?
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/file.inc.php';
require 'requires/db_utility.inc.php';

$jenis_cabang = get_rec("tblcabang","jenis_cabang","kd_cabang ='".$_SESSION["kd_cabang"]."'");

?>
<html>
<head>
	<title>.: <?=$_SESSION["application"]?> :.</title>
    <link href="css/text.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src='js/ajax.js.php'></script>
<script language='javascript' src='js/openwindow.js.php'></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language="javascript">
function fLoad(){
	//document.getElementById('img').src = "ajax/get_image.ajax.php?t="+Date();
}

function fClick(){
	l_obj_function = function(){parent.parent.window.location = 'home.php'}
	lreturn = show_modal('modal_change_password.php','dialogwidth:795px;dialogheight:225;',l_obj_function)
	if(lreturn)fLoad()
}
</script>
<body onLoad="fLoad()">
<table cellpadding="0" cellspacing="0" border="0" width="183" height="100%" bgcolor="#e8e8e8">
    <tr height="26">
		<td valign="top"><?//=create_list_memo()?>
        <font style="font-weight:bold">  System Date :</font>
        <font style="font-weight:bold;font-size:12px"><? echo date("d/m/Y",strtotime(get_rec("tblsetting","tgl_sistem")))?> </font><br>
        <? if(get_level_name()=="STAFF ACC") { ?>
        <font style="font-weight:bold;font-size:11px">Periode Acc : <? echo get_rec("tblsetting","bulan_accounting")?> - <? echo get_rec("tblsetting","tahun_accounting")?> </font><br>
        <? } ?>
        </td>
	</tr>
       
	<tr height="26">
		<td><img src="images/head_profile.jpg"></td>
	</tr>
	<tr>
		<td valign="top" align="center" height="100">
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr>
					<td colspan="3" height="10"></td>
				</tr>
				<tr>
					<td width="5"></td>
					<td>
						<table cellpadding="0" cellspacing="0" border="0" width="100%">
							<tr>
								<td width="50" valign="top" style="padding:4 5 0 5;border-color:#F6C;border:2">
                                	<? //echo get_rec("tbluser","pic","kd_user='".$_SESSION["id"]."'"); ?>
                                    <img id="img" src="upload/profile_pic/<?=get_rec("tbluser","pic","kd_user='".$_SESSION["id"]."'")?>" width="50px" height="50px">
                                	<p>
                                    <input type="button" value="Profile" style="width:50px" onClick="fClick()">
                                    </p>
								</td>
                                 <?
								
								?>
								<td style="padding:0 5 0 5" valign="top">
									<font style="font-weight:bold">Login as</font><br>
									<font style="color:#0f0f0f"><?=convert_html($_SESSION["username"])?></font><br>
									<font style="font-weight:bold">User: <?=convert_html($_SESSION["jenis_user"])?></font><br>
                                    <font style="font-weight:bold"><?=convert_html($_SESSION["nm_cabang"])?></font><br>
                                    <font style="font-weight:bold"><?=convert_html($_SESSION["kd_cabang"])?></font><br>
									<font style="color:#0f0f0f"><?//=get_level_name()?></font><br>
                                </td>
							</tr>
						</table>		
					</td>
					<td width="5">
                   
                    
                    </td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td valign="top" height="25"><img src="images/head_information.jpg"></td>
	</tr>   
	<tr>
		<td valign="top" height="25"><!--<img src="images/regist_ojk.png" width="170" height="90">--></td>
	</tr>   
    
	<tr>
    	<td bgcolor="#e8e8e8" valign="top"></td>
	</tr>
</table>
</body>
</html>
<?
function get_level_name(){
	$lreturn="";
	if ($lrow_user=pg_fetch_array(pg_query("select * from (select * from tbluser where kd_user='".$_SESSION["id"]."') as tbluser inner join tbllevel on tbllevel.kd_level=tbluser.fk_level"))){
		$lreturn=$lrow_user["name"];
	
	}
	return $lreturn;
}


function create_list_memo(){
	$lrs=pg_query("select pk_id,no_memo,judul_memo from tblinternal_memo where tgl_akhir_berlaku >= '".date("Y/m/d")." 23:59:59' and tgl_awal_berlaku <= '".date("Y/m/d")." 00:00:00' and tgl_batal is null order by tgl_memo desc");
	while($lrow = pg_fetch_array($lrs)){
		echo '&nbsp;&nbsp;&bull;&nbsp;&nbsp;<a href="#" class="blue" style="font-weight:bold" onClick="show_modal(\'modal_internal_memo_view.php?pstatus=view&id_edit='.convert_html($lrow['pk_id']).'\',\'status:no;help:no;dialogwidth:805px;dialogheight:380px;\')" title="'.convert_html($lrow['judul_memo']).'">'.convert_html(substr($lrow['no_memo'],0,18)).((strlen($lrow['no_memo'])<18)?"":"&hellip;")."</a><br>";
	}
}
?>
