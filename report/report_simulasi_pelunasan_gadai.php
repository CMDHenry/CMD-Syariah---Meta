<?php
include_once("report.php");
function filter_request(){
	global $showTgl,$tgl,$fk_sbg,$nm_customer;
	$fk_sbg=($_REQUEST["fk_sbg"]);
	$nm_customer=($_REQUEST["nm_customer"]);
	
	$showTgl='t';
	//$showBln='t';
}



function fGet(){
?>

function fGetBtk(){
	fGetCustomNC(false,'sbg','fk_sbg','Ganti SBG',document.form1.fk_sbg,document.form1.fk_sbg,document.form1.tipe,'','20170900000002')
    if (document.form1.fk_sbg.value !="")fGetBtkData()	
}

function fGetBtkData(){
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataBtkState
	lSentText="table=(select fk_sbg,nm_customer from tblinventory left join tblcustomer on no_cif=fk_cif)as tbl&field=(nm_customer)&key=fk_sbg&value="+document.form1.fk_sbg.value
	lObjLoad.open("POST","../ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}
function fGetDataBtkState(){	
	if (this.readyState == 4){
		//confirm(this.responseText)
		if (this.status==200 && this.responseText!="") {
			lTemp=this.responseText.split('¿');
			document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value=lTemp[0]
		} else {
			document.getElementById('divnm_customer').innerHTML=document.form1.nm_customer.value="-"
		}
	}
}


<?

}

function create_filter(){
	global $periode_awal1,$periode_akhir1,$fk_sbg,$fk_cif,$nm_customer;
	echo '<input type="hidden" name="tipe" value="Gadai">';	
?>
	<tr bgcolor="efefef">
          <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef">No SBG</td>
          <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                        <input name="fk_sbg" type="text" onKeyPress="if(event.keyCode==4) img_fk_sbg.click();" onKeyUp="fNextFocus(event,document.form1.periode_awal)"  value="<?=$fk_sbg?>" onChange="fGetBtkData()">&nbsp;<img src="../images/search.gif" id="img_fk_sbg" onClick="fGetBtk()" style="border:0px" align="absmiddle">
                    </td>
           <td style="padding:0 5 0 5" width="20%">Nama Customer</td>
            <td style="padding:0 5 0 5" width="30%"><input type="hidden" name="nm_customer" class='groove_text' value="<?=$nm_customer?>"><span id="divnm_customer"><?=$nm_customer?></span></td>
	  </tr>                   
<?	
}

function excel_content(){
	global $fk_cif,$nm_customer,$tgl_cair,$lama_pinjaman,$lama_pelunasan,$lama_jasa_simpan,$rate_flat,$nilai_pinjaman,$nilai_penyimpanan,$biaya_denda,$biaya_penjualan,$fk_sbg,$tgl;

?>
    <table cellpadding="0" cellspacing="1" border="0" width="100%">
    <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
        <td colspan="4" align="center">PELUNASAN</td>
    </tr>
    
    <tr>
      	<td class="border" colspan='4'>


<?
	//echo $fk_sbg;
	pelunasan_gadai($fk_sbg,$tgl);

	$fk_bank_pelunasan = get_rec("tblsetting","fk_bank_pelunasan");
	$nm_bank_pelunasan = get_rec("tblbank","nm_bank","kd_bank='".$fk_bank_pelunasan."' ");
	
	$_REQUEST["fk_sbg"]=$fk_sbg;
	$_REQUEST["fk_cif"]=$fk_cif;			
	$_REQUEST["lama_pinjaman"]=$lama_pinjaman;
	$_REQUEST["lama_pelunasan"]=$lama_pelunasan;
	$_REQUEST["tgl_cair"]=date("d/m/Y",strtotime($tgl_cair));
	$_REQUEST["tgl_jatuh_tempo_akhir"]=$tgl_jatuh_tempo_akhir;
	$_REQUEST["lama_jasa_simpan"]=$lama_jasa_simpan;
	$_REQUEST["rate_flat"]=$rate_flat;
	$_REQUEST["nilai_penyimpanan"]=$nilai_penyimpanan;
	$_REQUEST["nilai_pinjaman"]=$nilai_pinjaman;
	$_REQUEST["biaya_denda"]=$biaya_denda;
	$_REQUEST["biaya_penjualan"]=$biaya_penjualan;
	
	$total_pembayaran = $nilai_pinjaman + $nilai_penyimpanan + $biaya_penjualan + $biaya_denda;	
	$_REQUEST["total_pembayaran"]=$total_pembayaran;
	
	$nilai_bayar=pembulatan_pelunasan($total_pembayaran);	
	
	$_REQUEST["nilai_bayar"]=$nilai_bayar;
	$_REQUEST["fk_bank_pelunasan"]=$fk_bank_pelunasan;
	$_REQUEST["nm_bank_pelunasan"]=$nm_bank_pelunasan;
	
	$lrs_field=pg_query("select * from skeleton.tblmodule_fields where fk_module='20170800000017' and (is_add='t') 
	and kd_field not in('nm_customer','diskon_pelunasan','tgl_bayar','fk_sbg')
	order by no_urut_add asc");
	_module_create_header_content($lrs_field,"edit");


?>
	 </table>
<?
}


