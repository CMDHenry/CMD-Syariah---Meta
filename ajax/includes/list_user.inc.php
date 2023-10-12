	<?
	create_recordset()
	?>
	<table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
		<tr height="20">
			<td>
				<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border">
					<tr bgcolor="#D0E4FF">
						<td>
						<? if(check_right(kd_menu('New'),false)){ ?>
						&nbsp;<input type="button" value=" New " class="groove_button" onClick="fModal('new')">
						<?	}?>
						</td>
						<td align="right">
							<? if($recordset->page>1) {?>
							<a href="#" onClick="fGoPage(1)"><<</a>
							&nbsp;&nbsp;<a href="#" onClick="fPage(-1)"><</a>&nbsp;&nbsp;
							<? }?>
							<?=$recordset->page?> of <?=$recordset->total_page?>&nbsp;&nbsp;
							<? if($recordset->page<$recordset->total_page) {?>
							<a href="#" onClick="fPage(+1)">></a>
							&nbsp;&nbsp;<a href="#" onClick="fGoPage(<?=$recordset->total_page?>)">>></a>&nbsp;&nbsp;
							<? }?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td bgcolor="#f8f8f8" class="border">
				<div style='width:100%;height:290;overflow:auto'>
				<table cellpadding="0" cellspacing="1" border="0" width="100%">
					<tr bgcolor="#c8c8c8" class="header" height="15">
                        <td width="150" align="center"><a href="#" onClick="forder('username')">Username</a><? fascdesc("username")?></td>
                        <td width="150" align="center"><a href="#" onClick="forder('nm_karyawan')">Karyawan</a><? fascdesc("nm_karyawan")?></td>
                        <td width="150" align="center"><a href="#" onClick="forder('active')">Active</a><? fascdesc("active")?></td>
						<td width="35"></td>
					</tr>
					<?
						create_list_data();
					?>
				</table>
				</div>
			</td>
		</tr>
		<tr height="20">
			<td bgcolor="#D0E4FF" class="border">
			<?	
 //if(check_right('1010101212',false)){ ?>
<!--				&nbsp;&nbsp;&nbsp;<input type="button" name="btndelete" value="Delete" class="groove_button" onClick="fDelete()">
-->			<?	//}	?>
			</td>
		</tr>
		<tr height="20"><td>&nbsp;</td></tr>
	</table>
<?
function create_recordset(){
	global $recordset,$strorderby,$strordertype,$page,$username,$nm_karyawan;
	if ($username!=""){
		$lwhere.=" upper(username) like upper('%".convert_sql($username)."%')";
	}
	if ($nm_karyawan!=""){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" upper(nm_karyawan) like upper('%".convert_sql($nm_karyawan)."%')";
	}
	if ($lwhere!="") $lwhere=" where ".$lwhere;

	$lquote="select * from(
				select * from tbluser inner join (
					select npk,(nm_depan||' '|| case when nm_belakang is null then '' else nm_belakang end) as nm_karyawan from tblkaryawan
				)as tblkaryawan on fk_karyawan=npk
			) as tblmain
			 ".$lwhere." order by ".$strorderby." ".$strordertype;
	//showquery($lquote);	 
	$recordset = new recordset("",$lquote,$page,20);
}

function create_list_data(){
	global $recordset;
	$lIndex=0;
	$lrs = $recordset->get_recordset();
	while ($lrow=pg_fetch_array($lrs)){
?>
		<tr bgcolor='#e0e0e0' onmouseover="fTRColor(this,'over')" onmouseout="fTRColor(this,'out')">
			<td style="padding:0 5 0 5"align="center"><?=$lrow["username"]?></td>
            <td style="padding:0 5 0 5"align="center"><?=$lrow["nm_karyawan"]?></td>
			<td style="padding:0 5 0 5" class="" align="center"><img src="./images/<?=(($lrow["active"]=='t')?"true":"false")?>.gif"></td>
            <td align="center">
			<? if(check_right(kd_menu('Edit'),false)){ ?>            
			 	<input type="button" name="btnEdit" class="groove_button" value="Edit" onClick="fModal('edit','<?=$lrow["kd_user"]?>')">&nbsp;
            <?	}?>    
			<? if(check_right(kd_menu('Menu'),false)){ ?>                        
                <input type="button" name="btnMenu" class="groove_button" value="Menu" onClick="fModal('menu','<?=$lrow["kd_user"]?>')">&nbsp;
            <?	}?>    
                
			</td>														
		</tr>
<?
		$lIndex+=1;
	}
}

function fascdesc($p_order){
	global $strorderby,$strordertype;
	if($strorderby==$p_order){
		if($strordertype=="asc") echo "<img src='images/asc.gif'>";
		else echo "<img src='images/desc.gif'>";
	}
}


?>