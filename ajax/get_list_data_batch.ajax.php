<?
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/convert.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/referer_check.inc.php';
require '../classes/recordset_ajax.class.php';
require '../requires/numeric.inc.php';


if (!mRefererCheck("modal_batch_get.php") || !isset($_SESSION['id'])){
//	echo "err:1000";
} else{
	$page=$_REQUEST["page"];
	$strorderby=$_REQUEST["strorderby"];
	if($strorderby=="") $strorderby="kd_part";
	$strordertype=$_REQUEST["strordertype"];
	if($strordertype=="") $strordertype="asc";
	$no_batch=$_REQUEST["no_batch"];
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
						<td width="200"><a href="#" onClick="forder('no_batch')">No Batch</a><? fascdesc("no_batch")?></td>                       
						<td width="200"><a href="#" onClick="forder('tgl_batch')">Tgl Batch</a><? fascdesc("tgl_batch")?></td>
                        <td width="200"><a href="#" onClick="forder('fk_cabang')">Kode Cabang</a><? fascdesc("fk_cabang")?></td>
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
	global $recordset,$strorderby,$strordertype,$page,$no_batch,$nm_barang,$tipe;
	
	if ($no_batch!=""){
		$lwhere.=" upper(no_batch) like upper('%".convert_sql($no_batch)."%')";
	}
	if ($nm_barang!=""){
		if($lwhere!="") $lwhere.=" and";
		$lwhere.=" upper(nm_barang) like upper('%".convert_sql($nm_barang)."%')";
	}

	if ($lwhere!="") $lwhere=" where".$lwhere;
	
	$lquote="
	select * from(
		select distinct on(referensi)referensi as no_batch,date_trunc('day',tgl_bayar)as tgl_batch,fk_cabang from data_gadai.tblhistory_sbg
		left join (select fk_cabang,fk_sbg as fk_sbg2 from tblinventory)as tblmain on fk_sbg=fk_sbg2
		where transaksi='AR' and tgl_batal is null
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
			<td align="center"><input type="radio" name="r_id" class="groove_checkbox" value="<?=$lrow["no_batch"]?>" onClick="fChecked(this)" ondblclick="fChecked(this);fPilih()"></td>
            <td style="padding:0 5 0 5"><?=$lrow["no_batch"]?></td>
            <td style="padding:0 5 0 5"><?=date("d/m/Y",strtotime($lrow["tgl_batch"]))?></td>
			<td style="padding:0 5 0 5"><?=$lrow["fk_cabang"]?></td>
            
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