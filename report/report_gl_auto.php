<?php
include_once("report.php");
require '../requires/db_utility.inc.php';

function filter_request(){
	global $showBln,$showCab,$showTgl,$showPeriode,$fk_coa;
	//$showCab='t';
	//$showBln='t';
	//$showTgl='t';
	$showPeriode='t';
	
	$fk_coa=($_REQUEST["fk_coa"]);
}


function fGet(){
?>


function fGetCoa(){
	fGetNC(false,'20171000000034','fk_coa','Ganti Item Kendaraan',document.form1.fk_coa,document.form1.fk_coa,'','','','')
}

function fGetCoaData(){
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataCoaState
	lSentText="table= tbltemplate_coa&field=(description)&key=coa&value="+document.form1.fk_coa.value
	lObjLoad.open("POST","../ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}

function fGetDataCoaState(){
	if (this.readyState == 4){
		//confirm(this.responseText)
		if (this.status==200 && this.responseText!="") {
			lTemp=this.responseText.split('¿');
			document.getElementById('divDescription').innerHTML=document.form1.description.value=lTemp[0]
		} else {
		document.getElementById('divDescription').innerHTML=document.form1.description.value="-"
		}
	}
}



<?

}
function create_filter(){
	global $fk_coa;
?>

<!--     <tr bgcolor="efefef">
          <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef">COA Lawan Transaksi</td>
          <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                        <input name="fk_coa" type="text" onKeyPress="if(event.keyCode==4) img_fk_cabang.click();" onKeyUp="fNextFocus(event,document.form1.periode_awal)"  value="<?=$fk_coa?>" onChange="fGetCoaData()">&nbsp;<img src="../images/search.gif" id="img_fk_cabang" onClick="fGetCoa()" style="border:0px" align="absmiddle">
                    </td>
          <td width="20%" style="padding:0 5 0 5">Description</td>
          <td width="30%" style="padding:0 5 0 5">
          				<input type="hidden" name="description" value="<?=convert_html($description)?>" class="groove_text" style="width:90%" > <span id="divDescription"><?=convert_html($description)?></span></td>
	  </tr>   
 --> 
<?

}


function excel_content(){
	global $bulan,$tahun,$fk_cabang,$periode_akhir,$tgl,$periode_awal,$fk_coa;	
	
	if($fk_coa != ''){
		$lwhere.=" and fk_owner||type_owner||tr_date in (select fk_owner||type_owner||tr_date from data_accounting.tblgl_auto where fk_coa_d like '%".$fk_coa."' or fk_coa_c like '%".$fk_coa."') ";
		$lwhere2.=" where coa not like '%.".$fk_coa."' and coa not like '%.312%'";
	}
	
	//if($lwhere)$lwhere = " and ".$lwhere;
	
	echo "<table border=1>";
	echo "<tr>";
	echo "<td>No Transaksi</td>";	
	echo "<td>Tipe</td>";	
	echo "<td>Tgl(English)</td>";
	echo "<td>Catatan</td>";	
	echo "<td>COA</td>";
	echo "<td>Nama COA</td>";
	echo "<td>Keterangan</td>";	
	echo "<td>Debit</td>";
	echo "<td>Kredit</td>";
	echo "<td>Head Account</td>";		
	echo "</tr>";
	
	$lquery = "
	select DISTINCT ON (fk_owner,type_owner,tr_date,reference_transaksi)fk_owner,type_owner,tr_date,reference_transaksi from(
		select * from data_accounting.tblgl_auto 
		where tr_date >='".$periode_awal." 00:00:00' and tr_date <='".$periode_akhir." 23:59:59'
		".$lwhere."
		order by tr_date,no_bukti
	)as tblmain
	";
	//showquery($lquery);
	
	$lrs=pg_query($lquery);
	$i=0;
	$data_olahan = array();
	$grand_total = array();
	while ($row=pg_fetch_array($lrs)){
		$row["reference_transaksi"]=str_replace("'","''",$row["reference_transaksi"]);

		$query_detail="
		select *,
		case when fk_coa_d is not null then total end as debit,
		case when fk_coa_c is not null then total end as credit 
		from (
			select * from data_accounting.tblgl_auto 
			where total!=0 and type_owner not in('LABA RUGI BULAN BERJALAN')and fk_owner='".$row["fk_owner"]."' and type_owner='".$row["type_owner"]."' and tr_date='".$row["tr_date"]."' ".($row["reference_transaksi"]?"and reference_transaksi='".$row["reference_transaksi"]."'":"")."
			order by no_bukti asc 
		) as tblgl_auto
		inner join (select coa, description as desc_coa,fk_head_account from tblcoa ".$lwhere2.") as tblcoa on fk_coa_d=coa or fk_coa_c=coa
		";
		$lrs_detail=pg_query($query_detail);
		//showquery($query_detail);
		while($lrow_detail=pg_fetch_array($lrs_detail)){
			$nm_pembeli = get_rec("data_gadai.tbllelang","nm_penerima","fk_sbg = '".$lrow_detail["fk_owner"]."'");
			$nm_kontrak = pg_fetch_array(pg_query("select nm_customer from tblcustomer left join data_gadai.tbltaksir_umum on tblcustomer.no_cif = data_gadai.tbltaksir_umum.fk_cif where no_sbg_ar = '".$lrow_detail["fk_owner"]."'"));
			switch($lrow_detail["type_owner"]){
				case 'ADJUSTMENT MEMORIAL' : 
					$catatan = get_rec("data_accounting.tbladjust_memorial","description","no_bukti = '".$lrow_detail["fk_owner"]."'");
					break;
				case 'AKRUAL AKHIR BULAN' : 
				case 'AR' :
				case 'REKON BANK' :
					$catatan = $lrow_detail["description"];
					break;
				case 'BATAL ADJUSTMENT MEMORIAL' : 
					$catatan_temp = get_rec("data_accounting.tbladjust_memorial","description","no_bukti = '".$lrow_detail["fk_owner"]."'");
					$catatan = 'BATAL ' . $catatan_temp;
					break;
				case 'BATAL AR' : 
					$catatan = 'BATAL ' . $lrow_detail["description"];
					break;
				case 'BATAL MUTASI BANK CABANG - HO' : 
				case 'BATAL MUTASI BANK INTERNAL CABANG' : 
					$catatan_temp = get_rec("data_fa.tblmutasi_bank","keterangan","no_voucher = '".$lrow_detail["fk_owner"]."'");
					$catatan = 'BATAL ' . $catatan_temp;
					break;
				case 'BATAL PAYMENT' : 
				case 'BATAL PAYMENT REQUEST (HO)' : 
					$catatan = 'BATAL ' . $lrow_detail["reference_transaksi"];
					break;
				case 'BATAL PEMBAYARAN CICILAN' : 
					$catatan_temp = pg_fetch_array(pg_query("select cara_bayar, angsuran_ke from data_fa.tblpembayaran_cicilan where no_kwitansi = '".$lrow_detail["reference_transaksi"]."'"));
					$catatan = 'BATAL PEMBAYARAN ANGSURAN KE ' . $catatan_temp['angsuran_ke'] . ' VIA '. strtoupper($catatan_temp['cara_bayar']);
					break;
				case 'BATAL PETTY CASH' : 
					$catatan_temp = pg_fetch_array(pg_query("select alasan, keterangan from data_fa.tblpetty_cash where no_voucher = '".$lrow_detail["fk_owner"]."'"));
					$catatan = 'BATAL DENGAN ALASAN ' . strtoupper($catatan_temp['alasan']) . ' UNTUK TRANSAKSI  '. strtoupper($catatan_temp['keterangan']);
					break;
				case 'BATAL REKON BANK' : 
					$catatan_temp = get_rec("data_fa.tblrekon_bank","alasan","no_voucher = '".$lrow_detail["fk_owner"]."'");
					$catatan = 'BATAL DENGAN ALASAN ' . strtoupper($catatan_temp);
					break;
				case 'BATAL TARIK' : 
					$catatan = 'BATAL TARIK NO KONTRAK ' . $lrow_detail["fk_owner"];
					break;
				case 'BIAYA FIDUSIA' : 
					$cabang = get_rec("tblcabang","nm_cabang","kd_cabang = '".$lrow_detail["fk_cabang"]."'");
					$catatan = 'BIAYA FIDUSIA CABANG ' . $cabang;
					break;
				case 'JUAL CASH' : 
				case 'JUAL CREDIT' :
				case 'LELANG' :
					$catatan = 'PENJUALAN UNIT NO KONTRAK ' . $lrow_detail["fk_owner"] . ' ATAS NAMA '. $nm_kontrak['nm_customer'] .' KEPADA ' . $nm_pembeli;
					break;
				case 'MUTASI BANK CABANG - HO' : 
				case 'MUTASI BANK INTERNAL CABANG' : 
					$catatan = get_rec("data_fa.tblmutasi_bank","keterangan","no_voucher = '".$lrow_detail["fk_owner"]."'");
					break;
				case 'PAYMENT' : 
				case 'PAYMENT REQUEST (HO)' : 
				case 'PENCAIRAN' :
					$catatan = $lrow_detail["reference_transaksi"];
					break;
				case 'PELUNASAN CICILAN' :
					$catatan = 'PELUNASAN CICILAN NO KONTRAK ' . $lrow_detail["fk_owner"] . ' ATAS NAMA '. $nm_kontrak['nm_customer'];
					break;
				case 'PEMBAYARAN CICILAN' : 
					$catatan_temp = pg_fetch_array(pg_query("select cara_bayar, angsuran_ke from data_fa.tblpembayaran_cicilan where no_kwitansi = '".$lrow_detail["reference_transaksi"]."'"));
					$catatan = 'PEMBAYARAN ANGSURAN KE ' . $catatan_temp['angsuran_ke'] . ' VIA '. strtoupper($catatan_temp['cara_bayar']);
					break;
				case 'PEMBAYARAN TEBUS' :
					$catatan = 'PEMBAYARAN TEBUS NO KONTRAK ' . $lrow_detail["fk_owner"] . ' ATAS NAMA '. $nm_kontrak['nm_customer'];
					break;
				case 'PETTY CASH' : 
					$catatan = get_rec("data_fa.tblpetty_cash_detail","catatan","fk_voucher = '".$lrow_detail["fk_owner"]."'");
					break;
				case 'TARIK' : 
					$catatan = 'TARIK NO KONTRAK ' . $lrow_detail["fk_owner"];
					break;
			}
			echo "<tr>";
			echo "<td>".$lrow_detail["fk_owner"]."</td>";
			echo "<td>".$lrow_detail["type_owner"]."</td>";
			echo "<td>".date("m/d/Y",strtotime($lrow_detail["tr_date"]))."</td>";
			echo "<td>".$catatan."</td>";
			echo "<td>&nbsp;".$lrow_detail["coa"]."</td>";
			echo "<td>".$lrow_detail["desc_coa"]."</td>";
			echo "<td>".$lrow_detail["reference_transaksi"]."</td>";
			echo "<td align='right'>".convert_money("",$lrow_detail["debit"],2)."</td>";
			echo "<td align='right'>".convert_money("",$lrow_detail["credit"],2)."</td>";
			echo "<td>".$lrow_detail["fk_head_account"]."</td>";
			
			echo "</tr>";
		}
	}

		
	echo "</table>";
}

