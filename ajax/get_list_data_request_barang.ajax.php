<?
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/convert.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/referer_check.inc.php';
require '../classes/recordset_ajax.class.php';
require '../requires/numeric.inc.php';


if (!mRefererCheck("modal_request_barang_get.php") || !isset($_SESSION['id'])){
//	echo "err:1000";
} else{
	$page=$_REQUEST["page"];
	$strorderby=$_REQUEST["strorderby"];
	if($strorderby=="") $strorderby="kd_part";
	$strordertype=$_REQUEST["strordertype"];
	if($strordertype=="") $strordertype="asc";
	$kd_barang=$_REQUEST["kd_barang"];
	$nm_barang=$_REQUEST["nm_barang"];
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
						<td width="200"><a href="#" onClick="forder('kd_request')">Kode Request</a><? fascdesc("kd_request")?></td>                       
						<td width="200"><a href="#" onClick="forder('kd_barang')">Kode Barang</a><? fascdesc("kd_barang")?></td>
                        <td width="200"><a href="#" onClick="forder('nm_barang')">Nama Barang</a><? fascdesc("nm_barang")?></td>
                        <td width="200"><a href="#" onClick="forder('fk_jenis_barang')">Jenis Barang</a><? fascdesc("fk_jenis_barang")?></td>
                        <td width="200"><a href="#" onClick="forder('fk_tipe')">Kode Tipe</a><? fascdesc("fk_tipe")?></td>
                        <td width="200"><a href="#" onClick="forder('nm_tipe')">Nama Tipe</a><? fascdesc("nm_tipe")?></td>
                        <td width="200"><a href="#" onClick="forder('nm_merek')">Merek</a><? fascdesc("nm_merek")?></td>
                        <td width="200"><a href="#" onClick="forder('harga')">Harga</a><? fascdesc("harga")?></td>
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
	global $recordset,$strorderby,$strordertype,$page,$kd_barang,$nm_barang,$tipe;
	
	if ($kd_barang!=""){
		$lwhere.=" upper(kd_barang) like upper('%".convert_sql($kd_barang)."%')";
	}
	if ($nm_barang!=""){
		if($lwhere!="") $lwhere.=" and";
		$lwhere.=" upper(nm_barang) like upper('%".convert_sql($nm_barang)."%')";
	}

	if ($lwhere!="") $lwhere=" where".$lwhere;
	
	$lquote="
	select * from(
		select * from data_gadai.tblrequest_barang
		left join tbljenis_barang on fk_jenis_barang=kd_jenis_barang
		left join (
			select kd_tipe,nm_tipe,nm_merek from tbltipe
			left join tblmerek on fk_merek=kd_merek
		)as tbltipe on fk_tipe=kd_tipe
		where status_data='Need Approval'
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
			<td align="center"><input type="radio" name="r_id" class="groove_checkbox" value="<?=$lrow["kd_request"]?>" onClick="fChecked(this)" ondblclick="fChecked(this);fPilih()"></td>
            <td style="padding:0 5 0 5"><?=$lrow["kd_request"]?></td>
			<td style="padding:0 5 0 5"><?=$lrow["kd_barang"]?></td>
			<td style="padding:0 5 0 5"><?=$lrow["nm_barang"]?></td>
			<td style="padding:0 5 0 5"><?=$lrow["nm_jenis_barang"]?></td>
			<td style="padding:0 5 0 5"><?=$lrow["fk_tipe"]?></td>
			<td style="padding:0 5 0 5"><?=$lrow["nm_tipe"]?></td>
			<td style="padding:0 5 0 5"><?=$lrow["nm_merek"]?></td>
			<td style="padding:0 5 0 5"><?=convert_money("",$lrow["harga"])?></td>
            
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