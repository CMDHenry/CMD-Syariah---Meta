<?
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/convert.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/referer_check.inc.php';
require '../classes/recordset_ajax.class.php';

if (!mRefererCheck("modal_correction_get.php") || !isset($_SESSION['id'])){
//	echo "err:1000";
} else{
	$page=$_REQUEST["page"];
	$strorderby=$_REQUEST["strorderby"];
	if($strorderby=="") $strorderby="kd_part";
	$strordertype=$_REQUEST["strordertype"];
	if($strordertype=="") $strordertype="asc";
	$no_voucher=$_REQUEST["no_voucher"];
	$tgl_voucher=$_REQUEST["tgl_voucher"];
	$tipe=$_REQUEST["tipe"];
	create_recordset()
	?>
	<table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
		<tr height="20">
			<td>
				<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border">
					<tr bgcolor="#D0E4FF">
						<td>&nbsp;
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
				<div style='width:100%;height:414;overflow:auto'>
				<table cellpadding="0" cellspacing="1" border="0" width="100%">
					<tr bgcolor="#c8c8c8" class="header" height="15">
						<td width="25"></td>
						<td width="200"><a href="#" onClick="forder('no_voucher')">No Voucher</a><? fascdesc("no_voucher")?></td>
                        <td width="200"><a href="#" onClick="forder('tgl_voucher')">Tanggal Voucher</a><? fascdesc("tgl_voucher")?></td>
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
				&nbsp;&nbsp;&nbsp;<input type="button" name="btnpilih" value="Pilih" class="groove_button" onClick="fPilih()">
			</td>
		</tr>
		<tr height="20"><td>&nbsp;</td></tr>
	</table>
<?
}
function create_recordset(){
	global $recordset,$strorderby,$strordertype,$page,$no_voucher,$tgl_voucher,$tipe;
	
	if ($no_voucher!=""){
		$lwhere.=" upper(no_voucher) like upper('%".convert_sql($no_voucher)."%')";
	}

	if($tgl_voucher != '' && $tgl_voucher != ''){
		if($lwhere!="") $lwhere.=" and";
		$lwhere.=" tgl_voucher between '".convert_date_english($tgl_voucher)." 00:00:00' and '".convert_date_english($tgl_voucher)." 23:59:59'";
	}

	if ($lwhere!="") $lwhere=" where".$lwhere;
	
	$lquote="
	select * from(
			select * from viewvoucher
			left join data_fa.tblcorrection on fk_voucher=no_voucher 
			where fk_voucher is null			
	)as tblmain		
			
	".$lwhere." order by ".$strorderby." ".$strordertype;	
	$recordset = new recordset("",$lquote,$page,20);
	//showquery($lquote);
}

function create_list_data(){
	global $recordset;
	$lIndex=0;
	$lrs = $recordset->get_recordset();
	while ($lrow=pg_fetch_array($lrs)){
?>
		<tr bgcolor='#e0e0e0' onmouseover="fTRColor(this,'over')" onmouseout="fTRColor(this,'out')">
			<td align="center"><input type="radio" name="r_id" class="groove_checkbox" value="<?=$lrow["no_voucher"]?>" onClick="fChecked(this)" ondblclick="fChecked(this);fPilih()"></td>
			<td style="padding:0 5 0 5"><?=$lrow["no_voucher"]?></td>
            <td style="padding:0 5 0 5"><?=($lrow["tgl_voucher"]==""?"":date("d/m/Y",strtotime($lrow["tgl_voucher"])))?></td>
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