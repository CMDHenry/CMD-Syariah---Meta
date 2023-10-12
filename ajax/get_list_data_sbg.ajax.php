<?
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/convert.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/referer_check.inc.php';
require '../classes/recordset_ajax.class.php';

if (!mRefererCheck("modal_sbg_get.php") || !isset($_SESSION['id'])){
//	echo "err:1000";
} else{
	$page=$_REQUEST["page"];
	
	$strorderby=$_REQUEST["strorderby"];
	if($strorderby=="") $strorderby='no_sbg';
	$strordertype=$_REQUEST["strordertype"];
	if($strordertype=="") $strordertype="asc";
	
	$no_sbg=$_REQUEST["no_sbg"];
	$nm_customer=$_REQUEST["nm_customer"];
	$tipe=$_REQUEST["tipe"];
	$no_polisi=$_REQUEST["no_polisi"];
	$no_mesin=$_REQUEST["no_mesin"];
	$no_rangka=$_REQUEST["no_rangka"];
	
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
						<td ><a href="#" onClick="forder('no_sbg')">No Kontrak</a><? fascdesc("no_sbg")?></td>
                        <td ><a href="#" onClick="forder('nm_customer')">Nama Customer</a><? fascdesc("nm_customer")?></td>

                        <td ><a href="#" onClick="forder('fk_cif')">CIF</a><? fascdesc("fk_cif")?></td>
                        <td ><a href="#" onClick="forder('no_mesin')">No Mesin</a><? fascdesc("no_mesin")?></td>                		<td ><a href="#" onClick="forder('no_rangka')">No Rangka</a><? fascdesc("no_rangka")?></td>
						<td ><a href="#" onClick="forder('no_polisi')">No Polisi</a><? fascdesc("no_polisi")?></td>
						<td ><a href="#" onClick="forder('alamat_ktp')">Alamat KTP</a><? fascdesc("alamat_ktp")?></td>
                        
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
	global $recordset,$strorderby,$strordertype,$page,$no_sbg,$nm_customer,$tipe,$no_polisi,$no_mesin,$no_rangka;
	if ($no_sbg!=""){
		$lwhere.=" upper(no_sbg) like upper('%".convert_sql($no_sbg)."%')";
	}
	if ($nm_customer!=""){
		if($lwhere!="") $lwhere.=" and";
		$lwhere.=" upper(nm_customer) like upper('%".convert_sql($nm_customer)."%')";
	}
	if ($no_polisi!=""){
		if($lwhere!="") $lwhere.=" and";
		$lwhere.=" upper(no_polisi) like upper('%".convert_sql($no_polisi)."%')";
	}
	if ($no_mesin!=""){
		if($lwhere!="") $lwhere.=" and";
		$lwhere.=" upper(no_mesin) like upper('%".convert_sql($no_mesin)."%')";
	}
	if ($no_rangka!=""){
		if($lwhere!="") $lwhere.=" and";
		$lwhere.=" upper(no_rangka) like upper('%".convert_sql($no_rangka)."%')";
	}
	
	if ($_SESSION["jenis_user"]!="HO" && $tipe!='Cicilan'){
		if($lwhere!="") $lwhere.=" and";
		$lwhere.=" fk_cabang='".$_SESSION["kd_cabang"]."'";
	}
	if ($lwhere!="") $lwhere=" where".$lwhere;
	
	if($tipe=='Perpanjangan'){
		$query="
			select * from(
				select fk_sbg as no_sbg,tblinventory.fk_cif,fk_produk,status,tblinventory.fk_cabang from tblinventory 				
				left join (select no_sbg_lama from viewtaksir)as sbg_lama on fk_sbg=no_sbg_lama				
				where tgl_cair is not null and tgl_lunas is null and no_sbg_lama is null and status='Terima' 				
			)as tblmain			
		";
	}
	
	else {
		//echo $tipe;
		$query="select no_sbg,tbltaksir_umum.fk_cif,tblproduk_cicilan.fk_produk,status_sbg,tbltaksir_umum.fk_cabang from 
				data_gadai.tblproduk_cicilan
				left join tblinventory on fk_sbg=no_sbg
				left join data_gadai.tbltaksir_umum on fk_fatg=no_fatg
				--where tgl_cair is not null
				where status_approval='Approve'
		";		
		if($tipe=='Gadai')$lwhere1.=" where jenis_produk=0 and status_sbg='Liv' ";
		elseif($tipe=='Cicilan')$lwhere1.=" where jenis_produk=1 --and status_sbg='Liv'";
		elseif($tipe=='Diskon'){
			$query="
			select tbldiskon_pelunasan.fk_sbg as no_sbg,fk_cif,fk_produk,status_sbg,fk_cabang from tblinventory 
			inner join (select * from data_fa.tbldiskon_pelunasan where is_approval='f')as tbldiskon_pelunasan on tbldiskon_pelunasan.fk_sbg=tblinventory.fk_sbg
			where tgl_cair is not null and tgl_lunas is null";		
		}
		elseif($tipe=='Agent'){
			$query="
			select tblinventory.fk_sbg as no_sbg,fk_cif,fk_produk,status_sbg,fk_cabang from tblinventory 
			inner join (select no_sbg ,fk_karyawan_sales from viewkontrak where fk_karyawan_sales is not null)as viewkontrak on viewkontrak.no_sbg=tblinventory.fk_Sbg
			where tgl_cair is not null";		
		}	
		elseif($tipe=='BPKB'){
			$query="
			select tblinventory.fk_sbg as no_sbg,fk_cif,fk_produk,status_sbg,fk_cabang from tblinventory 
			left join (select no_sbg_ar,tgl_serah_terima_bpkb from data_gadai.tbltaksir_umum)as tblumum on no_sbg_ar=fk_sbg
			where tgl_lunas is not null and tgl_serah_terima_bpkb is null";		
		}elseif($tipe=='Diskon_BPKB'){
			$query="
			select tbldiskon_pelunasan.fk_sbg as no_sbg,fk_cif,fk_produk,status_sbg,fk_cabang from tblinventory 
			inner join (select * from data_fa.tbldiskon_bpkb where is_approval='f')as tbldiskon_pelunasan on tbldiskon_pelunasan.fk_sbg=tblinventory.fk_sbg
			left join (select no_sbg_ar,tgl_serah_terima_bpkb from data_gadai.tbltaksir_umum)as tblumum on no_sbg_ar=tblinventory.fk_sbg
			where tgl_lunas is not null and tgl_serah_terima_bpkb is null";		
		}elseif($tipe=='Tebus'){
			$query="
			select tblinventory.fk_sbg as no_sbg,fk_cif,fk_produk,status_sbg,fk_cabang from tblinventory 
			where tgl_lunas is null and status='Tarik'";		
		}elseif($tipe=='Blokir'){
			$ovd_blokir=get_rec("tblsetting","ovd_blokir","ovd_blokir>0");
			$query="
			select tblinventory.fk_sbg as no_sbg,fk_cif,fk_produk,status_sbg,fk_cabang from tblinventory 
			inner join (select * from viewjatuh_tempo where overdue >".$ovd_blokir.")as tbl on tbl.fk_sbg=tblinventory.fk_sbg
			";		
		}	
		
	}
	
	$lquote="
	select * from(
		select * from (
			".$query."
		)as tblsbg				
		left join (select no_cif,nm_customer,alamat_ktp from tblcustomer) as tblmain on no_cif=fk_cif
		left join (select kd_produk,nm_produk,jenis_produk from tblproduk) as tblmain2 on kd_produk=fk_produk  	
		left join (select no_sbg_ar,no_mesin,no_rangka,no_polisi from data_gadai.tbltaksir_umum)as tblumum on no_sbg_ar=no_sbg
		left join (select no_sbg as no_sbg1,no_polis from data_gadai.tblproduk_cicilan)as tblcicilan on no_sbg1=no_sbg
		".$lwhere1."							
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
			<td align="center"><input type="radio" name="r_id" class="groove_checkbox" value="<?=$lrow["no_sbg"]?>" onClick="fChecked(this)" ondblclick="fChecked(this);fPilih()"></td>
			<td style="padding:0 5 0 5"><?=$lrow["no_sbg"]?></td>
            <td style="padding:0 5 0 5"><?=$lrow["nm_customer"]?></td>
            <td style="padding:0 5 0 5"><?=$lrow["fk_cif"]?></td>
            <td style="padding:0 5 0 5"><?=$lrow["no_mesin"]?></td>
            <td style="padding:0 5 0 5"><?=$lrow["no_rangka"]?></td>
            <td style="padding:0 5 0 5"><?=$lrow["no_polisi"]?></td>
            <td style="padding:0 5 0 5"><?=$lrow["alamat_ktp"]?></td>
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