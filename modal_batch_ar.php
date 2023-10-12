<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/numeric.inc.php';
require 'requires/accounting_utility.inc.php';
require 'requires/timestamp.inc.php';
require 'requires/stok_utility.inc.php';

require 'classes/select.class.php';

$j_action=$_REQUEST["hidden_focus"];
$id_menu=trim($_REQUEST["id_menu"]);

$fk_module=get_rec("skeleton.tblmodule","pk_id","fk_menu='".$id_menu."'");
$fk_menu_union=get_rec("skeleton.tblmodule","fk_menu","fk_menu_union='".$id_menu."'");;

get_data_menu($id_menu);
get_data_module();

$keterangan=trim($_REQUEST["keterangan"]);
$no_batch=($_REQUEST["no_batch"]);
$fk_bank=($_REQUEST["fk_bank"]);
$fk_partner_dealer=($_REQUEST["fk_partner_dealer"]);
$no_rek=get_rec("tblpartner","no_rek","kd_partner='".$fk_partner_dealer."'");

$total=($_REQUEST["total"]);
$strmenu=($_REQUEST["strmenu"]);

$jenis_pembayaran=($_REQUEST["jenis_pembayaran"]);
$fk_cabang=($_REQUEST["fk_cabang"]);

$jenis_ar=($_REQUEST["jenis_ar"]);

if(!$_REQUEST["periode_awal"])$periode_awal = convert_date_english(today);
else $periode_awal = convert_date_english($_REQUEST["periode_awal"]);
if(!$_REQUEST["periode_akhir"])$periode_akhir=convert_date_english(today);
else $periode_akhir =  convert_date_english($_REQUEST["periode_akhir"]);

$is_backdate=trim($_REQUEST["is_backdate"]);
if($is_backdate==""){
	$is_backdate="f";
}
	$query=" 
	select * from data_gadai.tblproduk_cicilan 
	inner join(
		select no_fatg,fk_barang,fk_cif,fk_cabang,no_mesin,no_rangka,
		fk_partner_dealer,no_sbg_lama,status_fatg,status_barang from data_gadai.tbltaksir_umum 
		left join data_gadai.tbltaksir_umum_detail on fk_fatg=no_fatg
	)as tbl on no_fatg=fk_fatg
	left join tblbarang on kd_barang=fk_barang	
	left join tblcustomer on no_cif=fk_cif
	left join tblcabang on fk_cabang=kd_cabang
	left join tblpartner on fk_partner_dealer=kd_partner
	left join (
		select no_fatg as no_fatg1, kategori,fk_jenis_barang from viewkendaraan
	)as view on fk_fatg=no_fatg1	
	";
//showquery($query);


if($_REQUEST["status"]=="Save") {
	cek_error();
	if(!$strmsg){
		save_data();
	}
}
?>
<html>
<head>
	<title>.: <?=$_SESSION["application"]?> :.</title>
	<link href="css/cwcalendar.css.php" rel="stylesheet" type="text/css">
    <link href="css/text.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src="js/alert.js.php"></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src="js/calendar.js.php"></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src='js/input_format_number.js.php'></script>
<!--<script language='javascript' src="js/table_v2.js.php"></script>-->
<script language='javascript' src="js/table_adjustment_memorial.js.php"></script>
<script language='javascript' src="js/validate.js.php"></script>
<script language='javascript'>

function fModal(pType,pID,pModule,pModal){
	switch (pType){
		case "view":
			show_modal('modal_view.php?id_menu=20170900000073&pstatus=view&id_view='+pID,'status:no;help:no;dialogwidth:900px;dialogheight:545px;')
		break;
	}


}
function cekError(){
	var lAlerttxt="";
	var lFocuscursor="";

	if(lAlerttxt!=""){
		alert("Error : <br>"+lAlerttxt,function(){eval(lFocuscursor+'.focus()')});
		return false
	} else return true;
}


function fSave(){
	fCount()
	//alert('test')
	//if (cekError()) {
		document.form1.status.value='Save';
		document.form1.submit();
	//}
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")) {
		lInputClose=getObjInputClose();lInputClose.close()
	}
}

function fCount(pFlag='true'){
	var index;
	index =0;
	//confirm(pFlag)
	document.form1.strmenu.value='';
	lObjCount=document.form1.strcek
	var len = document.form1.strcek.length;
	if(len == undefined) len = 1;
	//confirm(lObjCount[0].checked)
	//confirm(lObjCount[1].checked)
	if(len==1){
		if (lObjCount.checked){
			index+=parseFloat(lObjCount.value)
			document.form1.strmenu.value=lObjCount.id+',';
		}
	}else{
		
		for (j=0;j<len;j++){			
			if (lObjCount[j].checked) {
				index+=parseFloat(lObjCount[j].value)
				document.form1.strmenu.value+=lObjCount[j].id+',';
			}
		}	
	}
	document.getElementById('divSelectCount').innerHTML=number_format(index);
	document.form1.total.value=index
	//var r=confirm("Total Data yang Dipilih : "+index);
	
}

function fLoad(){
	
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	}elseif($j_action){
		echo $j_action;
	}else{
		echo "document.form1.btnsimpan.focus();";
	}
?>
}
</script>
<body onLoad="fLoad();document.form1.autocomplete='off'" bgcolor="#fafafa">
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="form1" enctype="multipart/form-data">
<input type="hidden" name="status">
<input type="hidden" name="strisi" value="<?=$strisi?>">
<input type="hidden" name="hidden_focus">
<input type="hidden" name="id_menu" value="<?=$id_menu?>">
<input type="hidden" name="total" value="<?=$total?>">
<input type="hidden" name="strmenu" value="<?=$strmenu?>">

<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
                <tr><td class="judul_menu" align="center"><?=strtoupper($nm_menu)?></td></tr>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border" id="tdContent">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">     
            <tr bgcolor="efefef">
                <td width="20%" style="padding:0 5 0 5" class="">Periode </td>
                <td width="80%" style="padding:0 5 0 5" colspan="3">
                    <input type="text" name="periode_awal" value="<?=convert_date_indonesia($periode_awal)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_awal.click();" size="10" >&nbsp;<img src="images/btn_extend.gif" name="img_periode_awal" onClick="fPopCalendar(document.form1.periode_awal,function(){document.form1.submit()})"> -                               
                    <input type="text" name="periode_akhir" value="<?=convert_date_indonesia($periode_akhir)?>" class="groove_text" onKeyPress="if(event.keyCode==4) img_periode_akhir.click();" size="10">&nbsp;<img src="images/btn_extend.gif" name="img_periode_akhir" onClick="fPopCalendar(document.form1.periode_akhir,function(){document.form1.submit()})">                                
                </td>
            </tr>               
            <tr bgcolor="efefef"> 
                <td style="padding:0 5 0 5"width="10%" class="fontColor">Cabang</td>
                <td style="padding:0 5 0 5"width="40%" colspan="3">
                <? create_list_cabang();?>
                </td>
            </tr>   
            <tr bgcolor="efefef"> 
                <td style="padding:0 5 0 5"width="10%" class="fontColor">Jenis AR</td>
                <td style="padding:0 5 0 5"width="40%" colspan="3">
                <select name="jenis_ar" onChange="document.form1.submit()">
                    <option value="Reguler"<?= (($jenis_ar=='Reguler')?"selected":"") ?>>Reguler</option>
                 	<option value="Tambah DP"<?= (($jenis_ar=='Tambah DP')?"selected":"") ?>>Tambah DP</option>
                </select>
                </td>
            </tr>
            
            <tr bgcolor="efefef"> 
                <td style="padding:0 5 0 5"width="10%" class="fontColor">Dealer</td>
                <td style="padding:0 5 0 5"width="40%">
                <? create_list_dealer();?>
                </td>
            </tr> 
            <tr bgcolor="efefef">
                <td style="padding:0 5 0 5" width="10%">Backdate</td>
                <td style="padding:0 5 0 5" width="40%">
                <input type="checkbox" name="is_backdate" value="t" <?=(($is_backdate=="t")?"checked":"")?> >
                </td>
             </tr>
                   
            
		  	</table>
		<? view_data()?>
       
        </td>
    </tr>
    <tr height="20">
        <td height="25" align="center" bgcolor="#D0E4FF" class="border">
            <input class="groove_button" name="btnsimpan" type='button' value='Simpan' onClick="fSave()">
            &nbsp;<input class="groove_button" type="button" value="Batal" onClick="fBatal()"></td>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?

function view_data(){
	global $id_edit,$query,$fk_partner_dealer,$strmenu,$fk_cabang,$query,$total,$jenis_ar,$periode_awal,$periode_akhir;
	
	//if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cabang = '".$fk_cabang."' ";
	//}
	
	if($periode_awal != '' && $periode_akhir != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" tgl_pengajuan between '".$periode_awal." 00:00:00' and '".$periode_akhir." 23:59:59'";
	}
	
	if($jenis_ar == 'Tambah DP'){		
		$lwhere1.=" and no_sbg in(select fk_sbg_dp from data_fa.tblpenambahan_dp where tgl_batal is null and fk_sbg is null ) ";
	}else {
		$lwhere1.=" and (tgl_cair is null  --or no_sbg in('20101210400811','a','b')
		--or no_sbg in(select fk_sbg from data_gadai.tblhistory_sbg where transaksi='AR' and referensi='2104.AR.0000771')
		)";
	}
	if($fk_partner_dealer != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_partner_dealer = '".$fk_partner_dealer."' ";		
	}
	
	
	if ($lwhere!="") $lwhere=" and ".$lwhere;

	$query1=$query." 
	where (status_approval ='Approve' --and  no_sbg='40101210100087'
	".$lwhere1." )
	".$lwhere."  order by tgl_pengajuan,no_sbg
	--limit 2
	";
	
	//showquery($query1);
	
	$lrs=pg_query($query1);
	
?>
    <table cellpadding="0" cellspacing="1" border="0" width="100%">
        <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
    	    <td align="center">Cabang</td>
	        <td align="center">Dealer</td>
        	<td align="center">No Kontrak</td>
        	<td align="center">Tgl Pengajuan Kontrak</td>            
            <td align="center">Nama Customer</td>
            <td align="center">Nama Kendaraan</td>  
            <td align="center">No Mesin</td>  
            <td align="center">No Rangka</td>  
        	<td align="center">Nominal</td>
            <td align="center">Ganti Tgl J.T</td>
        	<td align="center"></td>
        </tr>
<?	

	while($lrow=pg_fetch_array($lrs)){	
		$fk_cabang=$lrow["fk_cabang"];
		$nominal=$lrow["pokok_hutang"];
		
		$kolom='tgl';
		$value[$kolom]=($_REQUEST[$kolom][$lrow["no_sbg"]]?$_REQUEST[$kolom][$lrow["no_sbg"]]:$lrow[$kolom]);
?>                  

		
         <tr style="padding:0 5 0 5" height="20" bgcolor='#efefef'>
         	<td style="padding:0 5 0 5" class="" align="left" width="100"><?=$lrow["nm_cabang"]?></td>
            <td style="padding:0 5 0 5" class="" align="left" width="150"><?=$lrow["nm_partner"]?></td>         
            <td style="padding:0 5 0 5" class="" align="left"><!--<a href="#" class="blue" onClick="fModal('view','<?=$lrow["no_sbg"]?>','<?=$id_menu?>')"><?=$lrow["no_sbg"]?></a>--><?=$lrow["no_sbg"]?></td>
            <td style="padding:0 5 0 5" class="" align="left"><?=date("d/m/Y",strtotime($lrow["tgl_pengajuan"]))?></td>
            <td style="padding:0 5 0 5" class="" align="left"><?=$lrow["nm_customer"]?></td>
            <td style="padding:0 5 0 5" class="" align="left"><?=$lrow["nm_barang"]?></td>
            <td style="padding:0 5 0 5" class="" align="left"><?=$lrow["no_mesin"]?></td>
            <td style="padding:0 5 0 5" class="" align="left"><?=$lrow["no_rangka"]?></td>
			<td style="padding:0 5 0 5" class="" align="right"><?=convert_money("",$nominal,0)?></td>	
			<td style="padding:0 5 0 5"align="center" ><input type="text" name="tgl[<?=$lrow["no_sbg"]?>]" value="<?=$value['tgl']?>" maxlength="2" size="1"> </td>            
			<td style="padding:0 5 0 5"align="center"><input type="checkbox" name="strcek" id=<?=$lrow["no_sbg"]?> value="<?=$nominal?>" <?=((strstr($strmenu,$lrow["no_sbg"]))?"checked":"")?>  onclick="fCount()"></td>
            	
         </tr>                  
<?		
	}
	
?>

		<tr tyle="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
            <td style="padding:0 5 0 5" align="center" colspan="8">Total</td>
            <td style="padding:0 5 0 5" align="right"><span id="divSelectCount"><?=convert_money("",$total)?></span></td>
            <td style="padding:0 5 0 5" align="center" ></td>
            <td style="padding:0 5 0 5" align="center" ></td>
        </tr>
    </table>

<?	
	
}

function cek_error(){
	global $strmsg,$j_action,$strisi,$strmenu,$jenis_pembayaran,$total,$query,$no_batch,$fk_cabang,$fk_bank,$is_backdate;

	//echo $total;
	if($total=="" || $total=="0"){
		$strmsg.='Tidak ada yang dipilih<br>';
		//if(!$j_action) $j_action="document.form1.fk_kelurahan.focus()";
	}
	
	$l_arr_row = split(',',$strmenu);
	for ($i=0; $i<count($l_arr_row)-1; $i++){
		$query1=$query." where no_sbg='".$l_arr_row[$i]."'";
		$lrs=pg_query($query1);
		$lrow=pg_fetch_array($lrs);
		if(!$lrow["no_mesin"]){
			$strmsg.='No Kontrak '.$l_arr_row[$i].' , No Mesin masih kosong<br>';
		}
		if(!$lrow["no_rangka"]){
			$strmsg.='No Kontrak '.$l_arr_row[$i].' , No Rangka masih kosong<br>';
		}
		
		$tgl_undur='';
		$tgl_pengajuan=$lrow["tgl_pengajuan"];
		if($_REQUEST["tgl"][$lrow["no_sbg"]]){
			$tgl_undur=date("m/",strtotime($tgl_pengajuan)).$_REQUEST["tgl"][$lrow["no_sbg"]].date("/Y",strtotime($tgl_pengajuan));	
			if(!validate_date($tgl_undur)){
				$strmsg.='Tgl Pengunduran jatuh tempo Kontrak '.$l_arr_row[$i].' salah<br>';
			}	
		}
		if(date("Ym",strtotime($lrow["tgl_pengajuan"]))!= date("Ym",strtotime(today_db)) && $is_backdate=='f'){
			$strmsg.='Tgl Kontrak '.$l_arr_row[$i].' berbeda bulan dengan tanggal sistem<br>';
		}

	}
	
	if($fk_cabang=="" ){
		$strmsg.='Cabang belum dipilih<br>';
	}	

	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}


function save_data(){
	global $strmsg,$j_action,$strisi,$jenis_pembayaran,$strmenu,$jenis_ar,$fk_bank,$fk_cabang,$fk_jenis_cabang,$query,$no_batch;

	$l_success=1;
	pg_query("BEGIN");
	
	$query_serial="select nextserial_transaksi('AR':: text)";
	$lrow_serial=pg_fetch_array(pg_query($query_serial));
	$no_batch=$lrow_serial["nextserial_transaksi"];	

	$total=0;
	$l_arr_row = split(',',$strmenu);
	//print_r($l_arr_row);
	for ($i=0; $i<count($l_arr_row)-1; $i++){
		if($jenis_ar == 'Tambah DP'){	
			tambah_dp($l_arr_row[$i]);
		}			
		
		$query1=$query." where no_sbg='".$l_arr_row[$i]."'";
		$lrs=pg_query($query1);
		$lrow=pg_fetch_array($lrs);
				
		$id_edit=$l_arr_row[$i];				
		
		$fk_cabang=$lrow["fk_cabang"];	
		$addm_addb=$lrow["addm_addb"];	
		$lama_pinjaman=$lrow["lama_pinjaman"];	
		$fk_cif=$lrow["fk_cif"];	
		$fk_produk=$lrow["fk_produk"];		
		$pokok=$lrow["total_hutang"];			
		
		//$tgl_pengajuan=today_db;
		$tgl_pengajuan=$lrow["tgl_pengajuan"];
		
		$tgl_undur='';
		if($_REQUEST["tgl"][$lrow["no_sbg"]]){
			$tgl_undur=date("m/",strtotime($tgl_pengajuan)).$_REQUEST["tgl"][$lrow["no_sbg"]].date("/Y",strtotime($tgl_pengajuan));	
			$tgl_pengajuan=	$tgl_undur;
		}
				
		
		angsuran($id_edit,$tgl_pengajuan);
		if($addm_addb=='M'){
			$tgl_jatuh_tempo=(get_next_month(($tgl_pengajuan),($lama_pinjaman-1)));
		}elseif($addm_addb=='B'){
			$tgl_jatuh_tempo=(get_next_month(($tgl_pengajuan),($lama_pinjaman)));
		}
		
		if(!pg_query(storing($id_edit,'-','Terima','AR',$tgl_pengajuan,'t',$fk_cabang,$fk_cif,$fk_produk,$tgl_jatuh_tempo)))$l_success=0;
		//showquery(storing($id_edit,'-','Terima','AR',$tgl_pengajuan,'t',$fk_cabang,$fk_cif,$fk_produk,$tgl_jatuh_tempo));
		
		if(!pg_query(insert_log("data_gadai.tblproduk_cicilan","no_sbg = '".$id_edit."'",'UB')))$l_success=0;		
		if(!pg_query("update data_gadai.tblproduk_cicilan set tgl_cair='".$tgl_pengajuan."',tgl_jatuh_tempo='".$tgl_jatuh_tempo."' where no_sbg='".$id_edit."'")) $l_success=0;
		if(!pg_query(insert_log("data_gadai.tblproduk_cicilan","no_sbg = '".$id_edit."'",'UA')))$l_success=0;		
		
		if(!pg_query(insert_history_sbg($id_edit,$pokok,'0','AR',$no_batch))) $l_success=0;								
		
		if(!pg_query(insert_log("data_gadai.tbltaksir_umum","no_fatg = '".$lrow["no_fatg"]."'",'UB')))$l_success=0;		
		if(!pg_query("update data_gadai.tbltaksir_umum set no_sbg_ar='".$id_edit."',fk_cabang_bpkb='".$fk_cabang."' where no_fatg='".$lrow["no_fatg"]."'")) $l_success=0;
		if(!pg_query(insert_log("data_gadai.tbltaksir_umum","no_fatg = '".$lrow["no_fatg"]."'",'UA')))$l_success=0;		
						
		//HITUNG						
		$total_bunga+=$lrow["biaya_penyimpanan"];
		$total_piutang+=$lrow["total_hutang"];
		if($addm_addb=='M'){
			$total_piutang-=$lrow["angsuran_bulan"];
		}
		
		$total_provisi+=$lrow["biaya_provisi"];
		
		if($lrow["kategori"]=='R2'){
			$admin['R2']+=$lrow["biaya_admin"];
			$admin['R2']+=$lrow["biaya_polis"];//polis jadi pend admin
		}
		if($lrow["kategori"]=='R4'){			
			$admin['R4']+=($lrow["biaya_adm_sales"]+$lrow["biaya_admin"]);
			$admin['R4']+=$lrow["biaya_polis"];//polis jadi pend admin
		}
		
		if($lrow["status_barang"]=='Baru' ){	
			$dealer[$lrow["fk_partner_dealer"]]+=$lrow["nilai_ap_customer"];			
		}elseif($lrow["status_barang"]=='Bekas'){			
			$uang_muka[$lrow["kategori"]]+=$lrow["nilai_ap_customer"];
		}elseif($lrow["status_barang"]=='Datun'){	
			$utang_usaha+=$lrow["nilai_ap_customer"];			
		}
		
		if($lrow["fk_partner_asuransi"]){	
			$asr_lain=$lrow["tjh_3"]+$lrow["pa_penumpang"]+$lrow["pa_supir"];//asr lain ud di calc masuk
			$asr_customer=($lrow["nilai_asuransi"]+$asr_lain);

			//+$lrow["biaya_polis"] //polis jadi pend admin
			//echo $asr_customer.'<br>';
			$tenor=ceil($lama_pinjaman/12);
			$total_asr_partner=0;
			for($j=1;$j<=$tenor;$j++){
				$asr_partner=calc_asuransi($lrow["no_sbg"],$j);	
				if($j==1){
					$asuransi[$lrow["fk_partner_asuransi"]][$lrow["kategori"]]+=($asr_partner);
				}
				else {
					$asuransi['ARO'][$lrow["kategori"]]+=($asr_partner);	
				}
				$total_asr_partner+=($asr_partner);
			}			
			$selisih=$asr_customer-$total_asr_partner;
			//$diskon_asuransi=$selisih;
			$diskon_asuransi=0;
			$asuransi['ARO'][$lrow["kategori"]]+=($selisih);	
						
			$total_asr_jiwa+=$lrow["nilai_asr_jiwa"];		
			$jiwa[$lrow["fk_partner_asuransi"]]+=($lrow["nilai_asr_jiwa"]);
		}
		
	}
	
	$reference='PEMBIAYAAN BATCH '.$no_batch;
	$arrPost = array();
	$arrPost["piutang_pembiayaan"]	= array('type'=>'d','value'=>$total_piutang);
	$arrPost["pend_bunga_yad"]		= array('type'=>'c','value'=>$total_bunga);
	
	if($utang_usaha>0){
		$arrPost["utang_usaha"]		= array('type'=>'c','value'=>$utang_usaha);	
	}
	
	$i=1;
	if(count($dealer)>0){
		foreach($dealer as $kd_partner =>$total){
			$coa_dealer=$fk_cabang.'.'.get_rec("tblpartner","fk_coa_dealer","kd_partner='".$kd_partner."'");			
			$arrPost["utang_dealer".$i]		= array('type'=>'c','value'=>$total,'account'=>$coa_dealer);
			$i++;
		}
	}
	
	$i=1;
	if(count($asuransi)>0){
		foreach($asuransi as $kd_partner =>$temp){
			foreach($temp as $kategori =>$total){
				$kategori=strtolower($kategori);
				$coa_asr=$fk_cabang.'.'.get_rec("tblpartner","fk_coa_asr_".$kategori,"kd_partner='".$kd_partner."'");				
				if($kd_partner=='ARO'){
					$arrPost["utang_aro_".$kategori]	= array('type'=>'c','value'=>$total);
				}else{
					$arrPost["utang_asr".$i]			= array('type'=>'c','value'=>$total,'account'=>$coa_asr);
				}
				$i++;
			}
		}
		//if($diskon_asuransi>0){
			$arrPost["diskon_asuransi"]		= array('type'=>'c','value'=>$diskon_asuransi);
		//}
	}
	
	if($total_asr_jiwa>0){
		foreach($jiwa as $kd_partner =>$asr_jiwa){
			$coa_asr_jiwa=$fk_cabang.'.'.get_rec("tblpartner","fk_coa_asr_jiwa","kd_partner='".$kd_partner."'");				
			$arrPost["utang_asr_jiwa"]	= array('type'=>'c','value'=>$asr_jiwa,'account'=>$coa_asr_jiwa);	
		}
	}				
			
	if(count($uang_muka)>0){
		foreach($uang_muka as $kategori =>$total){
			$kategori=strtolower($kategori);
			$arrPost["uang_muka_".$kategori]	= array('type'=>'c','value'=>$total);	
		}
	}
	
	if(count($admin)>0){
		foreach($admin as $kategori =>$total){
			$kategori=strtolower($kategori);
			$arrPost["pend_admin_".$kategori]	= array('type'=>'c','value'=>$total);
		}
	}	
	
	if($total_provisi>0){
		$arrPost["pend_provisi"]				= array('type'=>'c','value'=>$total_provisi);	
	}
	
	foreach($arrPost as $index=>$temp){
		$arrPost[$index]['reference'] =$reference;//tambah keterangan disemua arrpost
	}
	//cek_balance_array_post($arrPost);
	//echo $tgl_pengajuan;
	if(!posting('AR',$no_batch,$tgl_pengajuan,$arrPost,$fk_cabang,'00'))$l_success=0;		
	
	if($l_success==0)cek_balance_array_post($arrPost);
	
	//echo $l_success;
	//$l_success=0;
	if($l_success==1) {				
		$strmsg = "Data saved. Batch :".$no_batch."<br>";
		$j_action="lInputClose=getObjInputClose();lInputClose.close()";
		//pg_query("ROLLBACK");
		pg_query("COMMIT");
	} else {
		$strmsg .= "Error :<br>Data save failed.<br>";
	    pg_query("ROLLBACK");
	}	
}	

function create_list_bank(){
    global $fk_bank;
	
    $l_list_obj = new select("select * from tblcabang_detail_bank left join tblbank on fk_bank=kd_bank left join (select description, coa from tbltemplate_coa)as tblcoa on fk_coa=coa where fk_cabang='".$_SESSION["kd_cabang"]."' and fk_bank not in('01','03')","description","fk_bank","fk_bank");
    $l_list_obj->add_item("-- Bank ---",'',0);
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:12px;' onKeyUp='fNextFocus(event,document.getElementById(\"login\"))'");
}

function create_list_dealer(){
    global $fk_partner_dealer;
	
    $l_list_obj = new select("select * from tblpartner where fk_tipe_partner='DEALER' and partner_active ='t' order by nm_partner","nm_partner","kd_partner","fk_partner_dealer");
    $l_list_obj->add_item("-- Dealer ---",'',0);
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:12px;' onKeyUp='fNextFocus(event,document.getElementById(\"login\"))'","form1","document.form1.submit()");
}


function create_list_cabang(){
    global $fk_cabang;
	
    $l_list_obj = new select("select * from tblcabang where cabang_active ='t' order by kd_cabang","nm_cabang","kd_cabang","fk_cabang");
    $l_list_obj->add_item("-- Cabang ---",'',0);
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:12px;' onKeyUp='fNextFocus(event,document.getElementById(\"login\"))'","form1","document.form1.submit()");
}

function get_data_module(){
	global $kd_module,$j_action,$nm_tabs;
	
	//query untuk memunculkan tab 
	$lrow_tab_switch=pg_fetch_array(pg_query("select * from skeleton.tblmodule_tabs where fk_module='".$kd_module."' order by no_urut_tabs limit 1"));
	$nm_tabs=$lrow_tab_switch["kd_tabs"];
	$lrow_first_field=pg_fetch_array(pg_query("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' order by no_urut_add limit 1"));
	$j_action="document.form1.".$lrow_first_field["kd_field"].".focus();";
}
?>
		

	

