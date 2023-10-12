	<?
	create_recordset()
	?>
	<table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
		<tr height="20">
			<td>
				<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border">
					<tr bgcolor="#D0E4FF">
						<td>
						<? //if(check_right('1010101210',false)){ ?>
<!--						&nbsp;<input type="button" value=" Upload " class="groove_button" onClick="fModal('upload')">
-->						<?	//}	?>
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
				<div id="divContentInner" style='width:100%;height:100%;overflow:auto'>
				<table cellpadding="0" cellspacing="1" border="0" width="100%">
					<tr bgcolor="#c8c8c8" class="header" height="15">
                        <td width="150" align="center"><a href="#" onClick="forder('fk_owner')">No Transaksi</a><? fascdesc("fk_owner")?></td>
                        <td width="150" align="center"><a href="#" onClick="forder('tr_date')">Tanggal</a><? fascdesc("tr_date")?></td>
                        <td width="150" align="center"><a href="#" onClick="forder('type_owner')">Tipe</a><? fascdesc("type_owner")?></td>
                        <td width="150" align="center"><a href="#" onClick="forder('nm_customer')">Nama Customer</a><? fascdesc("nm_customer")?></td>
                        <td width="150" align="center"><a href="#" onClick="forder('reference_transaksi')">Reference Transaksi</a><? fascdesc("reference_transaksi")?></td>
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
			<? //if(check_right('1010101212',false)){ ?>
<!--				&nbsp;&nbsp;&nbsp;<input type="button" name="btndelete" value="Delete" class="groove_button" onClick="fDelete()">
-->			<?	//}	?>
			</td>
		</tr>
		<tr height="20"><td>&nbsp;</td></tr>
	</table>
<?
function create_recordset(){
	global $recordset,$strorderby,$strordertype,$page,$fk_owner,$bulan,$tahun,$type_owner,$fk_cabang,$reference_transaksi,$tr_date;
	if ($fk_owner!=""){
		$lwhere.=" upper(fk_owner) like upper('%".convert_sql($fk_owner)."%')";
		$lwhere1.=" upper(fk_sbg) like upper('%".convert_sql($fk_owner)."%')";
	}
	if ($type_owner!=""){
		if ($lwhere!="") $lwhere.="and";
		$lwhere.=" upper(type_owner) like upper('%".convert_sql($type_owner)."%')";
	}
	
	if ($fk_cabang!=""){
		if ($lwhere!="") $lwhere.="and";
		$lwhere.="(fk_coa_d like '".convert_sql($fk_cabang)."%' or fk_coa_c like '".convert_sql($fk_cabang)."%')";
	}
	
	if ($reference_transaksi!=""){
		if ($lwhere!="") $lwhere.="and";
		$lwhere.=" upper(reference_transaksi) like upper('%".convert_sql($reference_transaksi)."%')";
	}
		
	if ($tr_date!=""){
		$lwhere.=" (tr_date) = ('".convert_date_english($tr_date)."')";
	}

	
	if ($lwhere!="") $lwhere=" and ".$lwhere;
	if ($lwhere1!="") $lwhere1=" where ".$lwhere1;

/*
pk_id
left join (
			select max(no_bukti) as pk_id,fk_owner as fk_owner1,type_owner as type_owner1,tr_date as tr_date1,case when reference_transaksi is null then'-' else reference_transaksi end as reference_transaksi1 from data_accounting.tblgl_auto					
			group by fk_owner,type_owner,tr_date,reference_transaksi 
		)as tblgl on fk_owner=fk_owner1 and type_owner=type_owner1 and tr_date=tr_date1  and case when reference_transaksi is null then'-' else reference_transaksi end =reference_transaksi1

*/
	$lquote="
	select * from(
	SELECT DISTINCT ON (fk_owner,type_owner,tr_date,reference_transaksi)fk_owner,type_owner,tr_date,reference_transaksi,fk_sbg,nm_customer FROM (
		select * from(
			select fk_owner,type_owner,tr_date,reference_transaksi from data_accounting.tblgl_auto 
			where total!=0
			".$lwhere." 
		)as tblgl_auto
	)as tblgl_auto
	left join (
		select fk_sbg,nm_customer from tblinventory left join tblcustomer on no_cif=fk_cif
		".$lwhere1." 
	) as tblinventory on fk_sbg=fk_owner
)as tblgl_auto	
	order by  ".$strorderby." ".$strordertype." 	
	";
	//if($lwhere){
	//showquery($lquote);		 
		$recordset = new recordset("",$lquote,$page,20);
	//}
}

function create_list_data(){
	global $recordset;
	$lIndex=0;
if($recordset){
	$lrs = $recordset->get_recordset();
	while ($lrow=pg_fetch_array($lrs)){
		$ref=$lrow["reference_transaksi"];
		if(strstr($ref,'+')||strstr($ref,'&')){
			$ref=NULL;
		}
?>
		<tr bgcolor='#e0e0e0' onmouseover="fTRColor(this,'over')" onmouseout="fTRColor(this,'out')">
        	<td style="padding:0 5 0 5" width="200"><a href="#" class="blue" onClick="fModal('detail','<?=convert_html($lrow["fk_owner"])?>','<?=convert_html($lrow["type_owner"])?>','<?=convert_html($ref)?>','<?=convert_html($lrow["tr_date"])?>')"><?=convert_html($lrow["fk_owner"])?></a></td>
			<td style="padding:0 5 0 5" align="center"><?=date("d/m/Y",strtotime(convert_html($lrow["tr_date"])))?></td>
			<td style="padding:0 5 0 5"><?=convert_html($lrow["type_owner"])?></td>
            <td style="padding:0 5 0 5"><?=convert_html($lrow["nm_customer"])?></td>
            <td style="padding:0 5 0 5"><?=convert_html(preg_replace('/[^A-Za-z0-9-.;,()&" \/-]/', '',$lrow["reference_transaksi"]))?></td>				
</td>
		</td>														
		</tr>
<?
	
		$lIndex+=1;
	}
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