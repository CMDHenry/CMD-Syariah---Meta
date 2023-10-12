<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/numeric.inc.php';
require 'requires/validate.inc.php';
require 'requires/timestamp.inc.php';
require 'requires/input.inc.php';
require 'requires/cek_error.inc.php';
require 'requires/module.inc.php';
require 'requires/db_utility.inc.php';
require 'classes/select.class.php';
require 'requires/file.inc.php';
require 'requires/accounting_utility.inc.php';
require 'requires/stok_utility.inc.php';

$id_edit=trim($_REQUEST["id_edit"]);
$id_menu=trim($_REQUEST["id_menu"]);
$pstatus=trim($_REQUEST["pstatus"]);

$fk_bank_ho=trim($_REQUEST["fk_bank_ho"]);
$nm_bank_ho=($_REQUEST["nm_bank_ho"]);
$fk_bank_pencairan_ap=($_REQUEST["fk_bank_pencairan_ap"]);

//echo $p_status;
//if(file_exists($path_site."includes/modal_approve_".$id_menu.".inc.php"))
//	include $path_site."includes/modal_approve_".$id_menu.".inc.php";

//echo $id_edit;
get_data_menu($id_menu);

if(strstr(strtoupper($nm_menu),'GADAI')){
	include $path_site."includes/modal_add_20170800000053.inc.php";
}elseif(strstr(strtoupper($nm_menu),'CICILAN')){
	include $path_site."includes/modal_add_20170800000054.inc.php";
}

$lrs_status=pg_fetch_array(pg_query("select * from skeleton.tblmodule_approval where fk_module='".$kd_module."'"));
//showquery("select * from skeleton.tblmodule_approval where fk_module='".$kd_module."'");
$status_approval=trim($_REQUEST[$lrs_status["save_field_status"]]);
$alasan_status=trim($_REQUEST[$lrs_status["save_field_alasan"]]);
$alasan_batal=trim($_REQUEST[$lrs_status["save_field_alasan_batal"]]);

get_data_module();

if($_REQUEST["pstatus"]){
	get_data();
	get_data_header();
}

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
	<link href="js/cwcalendar.css.php" rel="stylesheet" type="text/css">
    <link href="text.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src='js/alert.js.php'></script>
<script language='javascript' src='js/calendar.js.php'></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src="js/table_v2.js.php"></script>
<script language='javascript' src="js/input_format_number.js.php"></script>
<script language='javascript' src="js/tab.js.php?kd_module=<?=$kd_module?>&id_menu=<?=$id_menu?>"></script>
<script language='javascript'>
<?
_module_create_js();
?>

function fGetBankHO(){
	fGetNC(false,'20171100000051','fk_bank_ho','Ganti Kota',document.form1.fk_bank_ho,document.form1.fk_bank_ho,'','')
	if (document.form1.fk_bank_ho.value !="")fGetBankHOData()
}

function fGetBankHOData(){
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataBankHOState
	lSentText="table=tblbank&field=(nm_bank)&key=kd_bank&value="+document.form1.fk_bank_ho.value
	//confirm(lSentText)
	lObjLoad.open("POST","ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}
function fGetDataBankHOState(){
	if (this.readyState == 4){
		//confirm(this.responseText);
		if (this.status==200 && this.responseText!="") {
			lTemp=this.responseText.split('¿');
			document.getElementById('divnm_bank_ho').innerHTML=document.form1.nm_bank_ho.value=lTemp[0]
		} else {
			document.getElementById('divnm_bank_ho').innerHTML=document.form1.nm_bank_ho.value="-"
		}
	}
}

function fSave(){
	lCanSubmit=true
	document.form1.status.value='Save';
	if (lCanSubmit) document.form1.submit();
}

function fBatal(){

	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")) {
		lInputClose=getObjInputClose();lInputClose.close()
	}
	
}



function fSubmit(){
	document.form1.submit();
	//document.getElementById('reference').style.display='block'
	//create_list_data_reference_field_name(pObj.value)
}

function fLoad(){
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	}else if($j_action){
		echo $j_action;
	}
?>
	//fGetBtkData()
}
</script>
<body onLoad="fLoad()" bgcolor="#fafafa">
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="form1" enctype="multipart/form-data">
<input type="hidden" name="id_menu" value="<?=$id_menu?>">
<input type="hidden" name="nm_menu" value="<?=$nm_menu?>">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="status">
<input type="hidden" name="<?=$lrs_status["save_field_status"]?>">
<input type="hidden" name="pstatus" value="<?=$pstatus?>">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<tr><td class="judul_menu" align="center"><?=strtoupper($nm_menu)?></td></tr>
			</table>
		</td>
	</tr>
    <table cellpadding="0" cellspacing="0" border="0" width="100%">
     <tr>
      	<td class="border">
<!-- content begin -->
			<? _module_create_header_approval("edit",false)?>
	  	</td>
	</tr>
    </table>
<? if($_REQUEST["pembayaran"]=='Transfer HO'){
	$no_rekening=get_rec("viewtaksir left join tblcustomer on no_cif=fk_cif","no_rekening","no_fatg = '".$_REQUEST["fk_fatg"]."' ");
	?>    
	<table cellpadding="0" cellspacing="1" border="0" width="100%">
        <tr bgcolor="efefef">
              <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef" class="fontColor">Kode Bank HO</td>
              <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                     <input name="fk_bank_ho" type="text" onKeyPress="if(event.keyCode==4) img_fk_bank_ho.click();"  value="<?=$fk_bank_ho?>" onChange="fGetBankHOData()">&nbsp;<img src="images/search.gif" id="img_fk_bank_ho" onClick="fGetBankHO()" style="border:0px" align="absmiddle">
                        </td>
              <td width="20%" style="padding:0 5 0 5">Nama Bank HO</td>
              <td width="30%" style="padding:0 5 0 5">
                      <input type="hidden" name="nm_bank_ho" value="<?=convert_html($nm_bank_ho)?>" class="groove_text" style="width:90%" > <span id="divnm_bank_ho"><?=convert_html($nm_bank_ho)?></span></td>
         </tr>
         <tr bgcolor="efefef">
              <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef">No Rekening</td>
              <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                     <input type="hidden" name="no_rekening" class='groove_text' value="<?=$no_rekening?>"><?=$no_rekening?></span>
                        </td>
              <td width="20%" style="padding:0 5 0 5"></td>
              <td width="30%" style="padding:0 5 0 5">
                      </td>
         </tr>               
    </table>	
<? }else{
	//$fk_bank_pencairan_ap=get_rec("tblsetting","fk_bank_pencairan_ap");
	//$nm_bank_pencairan_ap=get_rec("tblbank","nm_bank","kd_bank='".$fk_bank_pencairan_ap."'");
	
	?>	
	<table cellpadding="0" cellspacing="1" border="0" width="100%">
        <tr bgcolor="efefef">
              <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef" >Kode Bank</td>
              <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                     <!-- <input type="hidden" name="fk_bank_pencairan_ap" value="<?=convert_html($fk_bank_pencairan_ap)?>" class="groove_text" style="width:90%" > <span id="divfk_bank_pencairan_ap"><?=convert_html($fk_bank_pencairan_ap)?></span>-->
              <? create_list_bank();?>       
              </td>
             
              <td width="20%" style="padding:0 5 0 5"><!--Nama Bank Pencairan--></td>
              <td width="30%" style="padding:0 5 0 5">
                     <!-- <input type="hidden" name="nm_bank_pencairan_ap" value="<?=convert_html($nm_bank_pencairan_ap)?>" class="groove_text" style="width:90%" > <span id="divnm_bank_pencairan_ap"><?=convert_html($nm_bank_pencairan_ap)?></span>-->		</td>
         </tr>
	 </table>	

<? }?>
<? if($_REQUEST["perpanjangan_ke"]>0){?>    

    <table cellpadding="0" cellspacing="1" border="0" width="100%">
    <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
        <td colspan="4" align="center">PELUNASAN</td>
    </tr>
    
    <tr>
      	<td class="border" colspan='4'>
<!-- content begin -->
        <? 
		$fk_sbg=get_rec("viewtaksir","no_sbg_lama","no_fatg='".$_REQUEST["fk_fatg"]."'");
		if(strstr(strtoupper($nm_menu),'GADAI')){		
			pelunasan_gadai($fk_sbg);
		
			$fk_bank_pelunasan = get_rec("tblsetting","fk_bank_pelunasan");
			$nm_bank_pelunasan = get_rec("tblbank","nm_bank","kd_bank='".$fk_bank_pelunasan."' ");
			
			$_REQUEST["fk_sbg"]=$fk_sbg;
			$_REQUEST["fk_cif"]=$fk_cif;			
			$_REQUEST["lama_pinjaman"]=$lama_pinjaman;
			$_REQUEST["lama_pelunasan"]=$lama_pelunasan;
			$_REQUEST["tgl_cair"]=date("d/m/Y",strtotime($lrow["tgl_cair"]));
			$_REQUEST["tgl_jatuh_tempo_akhir"]=$tgl_jatuh_tempo_akhir;
			$_REQUEST["lama_jasa_simpan"]=$lama_jasa_simpan;
			$_REQUEST["rate_flat"]=$rate_flat;
			$_REQUEST["nilai_penyimpanan"]=$nilai_penyimpanan;
			$_REQUEST["nilai_pinjaman"]=$nilai_pinjaman;
			$_REQUEST["biaya_denda"]=$biaya_denda;
			$_REQUEST["biaya_penjualan"]=$biaya_penjualan;
			$_REQUEST["titipan"]=$titipan;
			
			$total_pembayaran = $nilai_pinjaman + $nilai_penyimpanan + $biaya_penjualan + $biaya_denda -$titipan;	
			$_REQUEST["total_pembayaran"]=$total_pembayaran;
			
			$nilai_bayar=pembulatan_pelunasan($total_pembayaran);	
			
			$_REQUEST["nilai_bayar"]=$nilai_bayar;
			$_REQUEST["fk_bank_pelunasan"]=$fk_bank_pelunasan;
			$_REQUEST["nm_bank_pelunasan"]=$nm_bank_pelunasan;
			
			$lrs_field=pg_query("select * from skeleton.tblmodule_fields where fk_module='20170800000017' and (is_add='t') 
			and kd_field not in('nm_customer','diskon_pelunasan','tgl_bayar')
			order by no_urut_add asc");
			_module_create_header_content($lrs_field,"edit");
			
		?>
        	
           <? }elseif(strstr(strtoupper($nm_menu),'CICILAN')){
			   
				pelunasan_cicilan($fk_sbg);
				$fk_bank_pelunasan = get_rec("tblsetting","fk_bank_pelunasan");
				$nm_bank_pelunasan = get_rec("tblbank","nm_bank","kd_bank='".$fk_bank_pelunasan."' ");
				
				$_REQUEST["fk_sbg"]=$fk_sbg;
				$_REQUEST["fk_cif"]=$fk_cif;
				$_REQUEST["sisa_angsuran"]=$sisa_angsuran;
				$_REQUEST["tgl_cair"]=date("d/m/Y",strtotime($tgl_cair));
				$_REQUEST["tgl_jatuh_tempo_akhir"]=date("d/m/Y",strtotime($tgl_jatuh_tempo_akhir));
				$_REQUEST["total_denda_lalu"]=$total_denda_lalu;
				$_REQUEST["total_denda_kini"]=$total_denda_kini;
				$_REQUEST["denda_keterlambatan"]=$denda_keterlambatan;
				$_REQUEST["denda_ganti_rugi"]=$denda_ganti_rugi;
				$_REQUEST["nilai_bayar_denda"]=$nilai_bayar_denda;
				//$_REQUEST["titipan_angsuran"]=$titipan_angsuran;
				
				$total_pelunasan=$sisa_angsuran+$nilai_bayar_denda;
				$_REQUEST["total_pelunasan"]=$total_pelunasan;
				
				$total_pembayaran=$total_pelunasan-$titipan_angsuran;
				$_REQUEST["total_pembayaran"]=$total_pembayaran;
				
				$nilai_bayar=pembulatan_pelunasan($total_pembayaran);	
				$_REQUEST["nilai_bayar"]=$nilai_bayar;
				$_REQUEST["fk_bank_pelunasan"]=$fk_bank_pelunasan;
				$_REQUEST["nm_bank_pelunasan"]=$nm_bank_pelunasan;
								
				$lrs_field=pg_query("select * from skeleton.tblmodule_fields where fk_module='20170900000001' and (is_add='t') 
				and kd_field not in('nm_customer','diskon_pelunasan','tgl_bayar')
				order by no_urut_add asc");
				_module_create_header_content($lrs_field,"edit");
			   ?>                
           
           <? }
		   
		 $selisih=str_replace(',','',$_REQUEST["nilai_ap_customer"])-str_replace(',','',$_REQUEST["nilai_bayar"]);
		 if($selisih<0){
			$selisih=$selisih*-1; 
		 }
		   ?>
        </table>
        <table cellpadding="0" cellspacing="1" border="0" width="100%">
            <tr bgcolor="efefef">
                <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef" ><?=($selisih>0?"Dibayar":"Diterima")?> oleh Customer</td>
                <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                      <input type="hidden" name="selisih" value="<?=convert_html($selisih)?>" class="groove_text" style="width:90%" > <span id="divselisih"><?=convert_money("",$selisih)?></span></td>
                </td>
                <td width="20%" style="padding:0 5 0 5"></td>
                <td width="30%" style="padding:0 5 0 5">
             </tr>
         </table>	
             
	  	</td>
	</tr>
 <? }?>	       

          </table>
<!-- end content begin -->
	  	</td>
    </tr>
</table>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" align="center">
	<tr height="20">
    	<td height="25" align="center" bgcolor="#D0E4FF">
            <input class="groove_button" type='button' name="btnsubmit" value='Simpan & Cetak Kwitansi' onClick="fSave()">
            <!--<input type="button" class="groove_button" value="Simpan & Print"  onclick="fSave('SaveNPrint')">-->
            &nbsp;<input class="groove_button" type="button" value="Batal" onClick="fBatal()">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?

function create_list_bank(){
    //global $fk_bank_pencairan_ap;
	
    $l_list_obj = new select("select * from tblcabang_detail_bank left join tblbank on fk_bank=kd_bank left join (select description, coa from tbltemplate_coa)as tblcoa on fk_coa=coa where fk_cabang='".get_cabang_pt($_REQUEST["fk_cabang"])."' and fk_bank not in('01','02')","description","fk_bank","fk_bank_pencairan_ap");
	
    $l_list_obj->add_item("-- Bank ---",'',0);
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:12px;' onKeyUp='fNextFocus(event,document.getElementById(\"login\"))'");
}

function cek_error(){
	global $strmsg,$j_action,$id_menu,$kd_module,$id_edit,$alasan_status,$alasan_batal,$pstatus,$nm_menu,$fk_bank_pencairan_ap;
/*	if (function_exists('cek_error_module')) {
		//cek_error_module();
		//echo "Cek Error Module functions are available.<br />\n";
		$strmsg.=cek_error_module();
	}
*/	if($pstatus=="approve"){
		if($alasan_status=="" || $alasan_status==null){
			$strmsg.="Keterangan Approve Harus Diisi.<br>";
			if(!$j_action) $j_action="document.form1.alasan_status.focus()";	
		} else $j_action="";
	}
	if($pstatus=="batal"){
		if($alasan_batal=="" || $alasan_batal==null){
			$strmsg.="Keterangan Batal Harus Diisi.<br>";
			if(!$j_action) $j_action="document.form1.alasan_batal.focus()";	
		} else $j_action="";
	}
	
	if(strstr(strtoupper($nm_menu),'GADAI')){
		$lrs_set_status["save_to_table"]="data_gadai.tblproduk_gadai";
	}
	elseif(strstr(strtoupper($nm_menu),'CICILAN')){
		$lrs_set_status["save_to_table"]="data_gadai.tblproduk_cicilan";
	}
	
	$l_table_pk = "no_sbg";	
	if(pg_num_rows(pg_query("select * from ".$lrs_set_status["save_to_table"]." where (fk_bank_pencairan_ap is not null)and ".$l_table_pk." ='".$id_edit."' for update"))){
		$strmsg.="Transaksi sudah di-cairkan oleh user lain.<br>";
		$j_action="lInputClose=getObjInputClose();lInputClose.close();";
	}elseif(pg_num_rows(pg_query("select * from ".$lrs_set_status["save_to_table"]." where (status_data ='Batal')and ".$l_table_pk." ='".$id_edit."' for update"))){
		$strmsg.="Transaksi sudah di-batalkan oleh user lain.<br>";
		$j_action="lInputClose=getObjInputClose();lInputClose.close();";
	}
	if($_REQUEST["perpanjangan_ke"]>0){
		if(pg_num_rows(pg_query("select * from tblinventory where fk_sbg='".($_REQUEST['fk_sbg'])."' and tgl_lunas is not null"))){
			$strmsg.="Sudah dilakukan pelunasan di menu Pelunasan , tidak bisa dilakukan perpanjangan.<br>";		
		}
		else if(!pg_num_rows(pg_query("select fk_sbg from tblinventory where upper(fk_sbg) = '".($_REQUEST['fk_sbg'])."' and status='Diserahkan' "))){

		}
	}
	
	if($_REQUEST["pembayaran"]=='Transfer HO'){
		$fk_cabang =  $_REQUEST["fk_cabang"];
		if(is_pt=='t')$cabang_ho=get_cabang_pt($fk_cabang);
		else $cabang_ho=cabang_ho;
		if($_REQUEST["fk_bank_ho"]==NULL || $_REQUEST["fk_bank_ho"]==' '){
			$strmsg.="Kode Bank HO Belum Diisi.<br>";
		} else if (!pg_num_rows(pg_query("select * from tblcabang_detail_bank where upper(fk_bank) = '".strtoupper($_REQUEST['fk_bank_ho'])."' and upper(fk_cabang) = '".$cabang_ho."' "))){
			$strmsg.="Kode Bank Tidak Ada di Cabang HO yang bersangkutan.<br>";
		}
	}
	if($_REQUEST["fk_bank_ho"]==NULL || $_REQUEST["fk_bank_ho"]==' '){
		
	}
	
	$fk_cabang =  $_REQUEST["fk_cabang"];
	$fk_lajur=get_lokasi_storing($fk_cabang);
	
	if(!$fk_bank_pencairan_ap){
		$strmsg.="Kode Bank Belum Diisi.<br>";		
	}
	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}

function save_data(){
	global $j_action,$strmsg,$id_menu,$kd_module,$id_edit, $upload_path,$kd_tabs,$kd_tabs2,$kd_tabs3,$alasan_status,$status_approval,$alasan_batal,$pstatus,$fk_bank_pencairan_ap,$nm_menu,$l_success;
	$l_success=1;
	pg_query("BEGIN");	
	
	//KALO PERPANJANGAN AMBIL INCLUDES SI PELUNASAN
	if($_REQUEST["perpanjangan_ke"]>0){
		if (function_exists('save_additional')) {
			//echo "Approve Additional functions are available.<br />\n";
			save_additional();
		}
	}
	
	$l_table_pk = "no_sbg";
	
	$fk_cabang =  $_REQUEST["fk_cabang"];
	
	if($_REQUEST["pembayaran"]=='Transfer'){
		$fk_bank=$_REQUEST["fk_bank_ho"];
		if(is_pt=='t')$fk_cabang_bank=get_cabang_pt($fk_cabang);
		else $fk_cabang_bank=cabang_ho;
		//echo $fk_cabang_bank;
	}else {
		$fk_bank=$fk_bank_pencairan_ap;
		$fk_cabang_bank=get_cabang_pt($fk_cabang);
		$fk_cabang_ho=get_cabang_pt($fk_cabang);
	}
	
	if(strstr(strtoupper($nm_menu), strtoupper('Cicilan'))&& $_REQUEST["addm_addb"]=='M'){
		//ECHO $_REQUEST["addm_addb"];
		$nilai_pembayaran=str_replace(',','',$_REQUEST["angsuran_bulan"]);
		$nominal_keluar-=$nilai_pembayaran;
	}	
	$transaksi='Pencairan';
	$total=str_replace(',','',$_REQUEST['nilai_ap_customer']);
	
	$rpkc 	  = get_coa_cabang($fk_cabang,$fk_cabang_ho);
	$coa_bank = get_coa_bank($fk_bank,$fk_cabang_bank);
	$arrPost = array();
	$arrPost["bank"]			= array('type'=>'c','value'=>$total,'account'=>$coa_bank,'reference'=>$keterangan);
	$arrPost["rpkc"]			= array('type'=>'d','value'=>$total,'account'=>$rpkc,'reference'=>$keterangan);
	//cek_balance_array_post($arrPost);
	if(!posting(strtoupper($transaksi),$id_edit,today_db,$arrPost,$fk_cabang_ho,'00'))$l_success=0;
	
	$arrPost = array();
	$arrPost["rpkp"]			= array('type'=>'c','value'=>$total,'reference'=>$keterangan);		
	$arrPost["utang_usaha"]		= array('type'=>'d','value'=>$total,'reference'=>$keterangan);
	//cek_balance_array_post($arrPost);
	if(!posting(strtoupper($transaksi),$id_edit,today_db,$arrPost,$fk_cabang,'00'))$l_success=0;
	
	
	if(!pg_query(update_saldo_bank($fk_bank,$fk_cabang_bank,0,$total,$transaksi,$id_edit)))$l_success=0;
	//showquery(update_saldo_bank($fk_bank,$fk_cabang_bank,0,$nilai_ap_customer,"Pencairan",$id_edit));

	$referensi='';
	$ang_ke=0;
	if(!pg_query(insert_history_sbg($id_edit,$total,$ang_ke,$transaksi,$referensi))) $l_success=0;
	//showquery(insert_history_sbg($id_edit,$nilai_ap_customer,$ang_ke,$transaksi,$referensi));


	if(!pg_query(insert_log("data_gadai.tblproduk_cicilan","no_sbg = '".$id_edit."'",'UB')))$l_success=0;		
	if(!pg_query("update data_gadai.tblproduk_cicilan set fk_bank_pencairan_ap='".$fk_bank."',tgl_pencairan_datun='".today_db."' where no_sbg='".$id_edit."'")) $l_success=0;
	if(!pg_query(insert_log("data_gadai.tblproduk_cicilan","no_sbg = '".$id_edit."'",'UA')))$l_success=0;		

	//echo $l_success;
	$l_success=0;
	if ($l_success==1){
		$alasan_status="";
		$alasan_batal="";
		$strmsg=$nm_menu." Tersimpan.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		pg_query("COMMIT");
		$j_action="window.location='print/print_kwitansi_pencairan.php?pstatus=edit&id_edit=".$id_edit."'";
	}else{
		$strmsg.="Error :<br>".$nm_menu." Gagal Tersimpan.<br>";		
		pg_query("ROLLBACK");
	}
}

function get_data(){
	global $kd_module,$id_edit,$alasan_status,$status_approval,$pstatus;
	

}

function get_data_header(){
	global $id_edit,$kd_module,$id_menu;

	$lrs_tabel=pg_query("select kd_field,save_to_table,save_to_field,save_to_field_2,fk_data_type,type_list,is_multiple,is_numeric from skeleton.tblmodule_fields
						where fk_module='".$kd_module."'
						order by no_urut_edit, save_to_table,save_to_field");
/*	showquery("select kd_field,save_to_table,save_to_field,save_to_field_2,fk_data_type,type_list,is_multiple from skeleton.tblmodule_fields
						where fk_module='".$kd_module."'
						order by no_urut_edit, save_to_table,save_to_field");
*/						
	while($lrow_table=pg_fetch_array($lrs_tabel)){
		if($lrow_table["save_to_table"]=="") {
			$lrow_table["save_to_table"]=$save_table_old;
		}
		
		$l_arr_table[$lrow_table["save_to_table"]][$lrow_table["kd_field"]]["save_to_field"]=$lrow_table["save_to_field"].(($lrow_table["save_to_field_2"])?",".$lrow_table["save_to_field_2"]:"");
		$l_arr_table[$lrow_table["save_to_table"]][$lrow_table["kd_field"]]["fk_data_type"]=(($lrow_table["fk_data_type"]=="list")?$lrow_table["type_list"]:$lrow_table["fk_data_type"]);
		$l_arr_table[$lrow_table["save_to_table"]][$lrow_table["kd_field"]]["is_multiple"]=$lrow_table["is_multiple"];
		$l_arr_table[$lrow_table["save_to_table"]][$lrow_table["kd_field"]]["is_numeric"]=$lrow_table["is_numeric"];
		
		$save_table_old=$lrow_table["save_to_table"];
	}
	
	$lrs_tabel=pg_query("select distinct save_to_table from skeleton.tblmodule_fields
						where fk_module='".$kd_module."'");
	while($lrow_table=pg_fetch_array($lrs_tabel)){
		$lrs_relation=pg_query("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' and value_type='reference' and fk_data_type='readonly'");
		while ($lrow_relation=pg_fetch_array($lrs_relation)) {
			if ($lrow_relation["reference_table_name"]=="sql_query") {
				$lrow_query=pg_fetch_array(pg_query("select * from skeleton.tblsql_query where kd_sql_query='".$lrow_relation["sql_query"]."'"));
				$l_table=$lrow_query["sql_query"];
			}  else {
				$l_table=$lrow_relation["reference_table_name"];
			}
			$l_arr_relation[$lrow_table["save_to_table"]][$l_table]["field"].=$lrow_relation["reference_field_name"].",";
			$l_arr_relation[$lrow_table["save_to_table"]][$l_table]["expression"]=$lrow_relation["reference_expression"];
		}
	}
	
	$lrs_menu=pg_query("select list_sql from skeleton.tblmenu
				where pk_id='".$id_menu."'
				");
	$lrow_menu=pg_fetch_array($lrs_menu);
	$list_sql=$lrow_menu["list_sql"];

	foreach ($l_arr_table as $l_table=>$l_arr_field){
		if (trim($l_table)) {
			$l_pk=get_rec("skeleton.tbldb_table_detail","kd_field","fk_db_table='".$l_table."' and is_pk=true");
			$l_arr_query[$l_table]="select * from ".$l_table;
			$l_counter_relation=1;
			if (is_array($l_arr_relation)) {
				foreach ($l_arr_relation[$l_table] as $l_join_table=>$l_arr_content) {
					$l_arr_query[$l_table].=" left join ".$l_join_table." on ".$l_arr_content["expression"];
					$l_counter_relation++;
				}
			}
			
			$l_arr_query[$l_table]= "select * from ( ".$list_sql." )as tblmain";// modif baru
			$l_arr_query[$l_table].= " where ".$l_pk."='".convert_sql($id_edit)."'";
			$l_arr_query[$l_table]=str_replace("[","(",$l_arr_query[$l_table]);
			$l_arr_query[$l_table]=str_replace("]",")",$l_arr_query[$l_table]);
			$lrow=pg_fetch_array(pg_query($l_arr_query[$l_table]));
			//showquery($l_arr_query[$l_table]);
			
			foreach ($l_arr_field as $l_kd_field=>$l_save_to_field) {
				if(strstr($l_save_to_field["save_to_field"],",")){
					$save_to_field=explode(",",$l_save_to_field["save_to_field"]);
					$l_field=$save_to_field[0];
				}else{
					$l_field=(($l_save_to_field["save_to_field"]=="")?$l_kd_field:$l_save_to_field["save_to_field"]);
				}
				$l_kd_field=str_replace("[]","",$l_kd_field);				
				if ($l_save_to_field["fk_data_type"]=="list_db" && $l_save_to_field["is_multiple"]=="f") {
					
					$lrow_field=pg_fetch_array(pg_query("select * from skeleton.tblmodule_fields where fk_module='".convert_sql($kd_module)."' and kd_field='".convert_sql($l_kd_field)."'"));
					if(strpos ($lrow_field["list_sql"],"where")>=1){
						if ($lrow_reference=pg_fetch_array(pg_query($lrow_field["list_sql"]." and ".$lrow_field["list_field_value"]."='".$lrow[$l_field]."'"))){
							$_REQUEST[$l_kd_field]=$lrow_reference[$lrow_field["list_field_text"]];
						}

					} else{
						if ($lrow_reference=pg_fetch_array(pg_query($lrow_field["list_sql"]." where ".$lrow_field["list_field_value"]."='".$lrow[$l_field]."'"))){
							$_REQUEST[$l_kd_field]=$lrow_reference[$lrow_field["list_field_text"]];
						}
					}
				} elseif ($l_save_to_field["fk_data_type"]=="list_manual" && $l_save_to_field["is_multiple"]=="f") {
					$lrow_field=pg_fetch_array(pg_query("select * from skeleton.tblmodule_fields where fk_module='".convert_sql($kd_module)."' and kd_field='".convert_sql($l_kd_field)."'"));
					//showquery("select * from skeleton.tblmodule_fields where fk_module='".convert_sql($kd_module)."' and kd_field='".convert_sql($l_kd_field)."'");
					$l_arr_list_manual_value=split(",",$lrow_field["list_manual_value"]);
					//print_r($l_arr_list_manual_value);
					
					$l_arr_list_manual_text=split(",",$lrow_field["list_manual_text"]);
					for ($l_index=0;$l_index<count($l_arr_list_manual_value);$l_index++){
						$l_arr_list[$l_arr_list_manual_value[$l_index]]=$l_arr_list_manual_text[$l_index];
					}
					$_REQUEST[$l_kd_field]=$l_arr_list[$lrow[$l_field]];
					
				} else if ($l_save_to_field["fk_data_type"]=="range_date"){
					$lvalue=explode(",",$l_save_to_field["save_to_field"]);	
					$_REQUEST[$l_kd_field]=format_data_view($lrow[$lvalue[0]],"date","d/m/Y")." - ".format_data_view($lrow[$lvalue[1]],"date","d/m/Y");
				} else if($l_save_to_field["fk_data_type"]=="range_value"){ 
					$lrange_value=explode(",",$l_save_to_field["save_to_field"]);
					$_REQUEST[$l_kd_field]=format_data_view($lrow[$lrange_value[0]],"d/m/Y")." - ".format_data_view($lrow[$lrange_value[1]],"d/m/Y");	
				} else {
					//echo $l_kd_field.'-'.$l_save_to_field["fk_data_type"].'<br>';
					if($l_save_to_field["is_numeric"]=="t")$l_save_to_field["fk_data_type"]="numeric";
					if ($lrow[$l_field]) $_REQUEST[$l_kd_field]=format_data_view($lrow[$l_field],$l_save_to_field["fk_data_type"],"d/m/Y");
					else if($lrow[$l_field]=="0") $_REQUEST[$l_kd_field]=format_data_view($lrow[$l_field],$l_save_to_field["fk_data_type"],"d/m/Y");	
				}
			}
		}
	}
}

function get_data_module(){
	global $kd_module,$j_action,$nm_tabs;
	
	//query untuk memunculkan tab 
	$lrow_tab_switch=pg_fetch_array(pg_query("select * from skeleton.tblmodule_tabs where fk_module='".$kd_module."' order by no_urut_tabs limit 1"));
	$nm_tabs=$lrow_tab_switch["kd_tabs"];
	//$lrow_first_field=pg_fetch_array(pg_query("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' order by no_urut_edit limit 1"));
	//$j_action="document.form1.".$lrow_first_field["kd_field"].".focus();";
	
	//$j_action="document.form1.".get_rec("skeleton.tblmodule_approval","save_field_alasan","fk_module='".$kd_module."'").".focus();";
	//showquery("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' order by no_urut_edit limit 1");
	//echo $lrow_first_field["kd_field"];
}
?>