<?php
include_once("report.php");

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
	global $bulan,$tahun,$fk_cabang,$periode_akhir,$tgl,$periode_awal;	
	
	echo "<table border=1>";
	echo "<tr>";
	echo "<td>Tanggal</td>";	
	echo "<td>No Voucher</td>";
	echo "<td>Keterangan</td>";	
	echo "<td>Bank Sumber</td>";
	echo "<td>Nama Partner</td>";
	echo "<td>Bank Tujuan Transfer</td>";
	echo "<td>No Rekening Tujuan Transfer</td>";
	echo "<td>Nama Rekening Tujuan Transfer</td>";
	echo "<td>Nominal</td>";		
	echo "</tr>";

	$query = "
			SELECT
				CASE
					WHEN fk_bank IN ('01','02') THEN 'KAS'
					ELSE 'BANK'
				END AS jenis,
				*,
				alamat
			FROM
				(
					SELECT
						no_voucher,
						total,
						keterangan,
						fk_bank,
						fk_cabang,
						tgl_voucher,
						'' AS fk_cek,
						NULL::timestamp AS tgl_jatuh_tempo,
						'' AS transaksi,
						'' AS bulan,
						'' AS tahun
					FROM
						data_fa.tblpetty_cash
					UNION
					SELECT
						no_voucher,
						total,
						keterangan,
						fk_bank_keluar,
						fk_cabang,
						tgl_voucher,
						'',
						NULL,
						'',
						'',
						''
					FROM
						data_fa.tblmutasi_bank
					UNION
					SELECT
						no_voucher,
						total*-1,
						keterangan,
						fk_bank,
						fk_cabang,
						tgl_voucher,
						fk_cek,
						tgl_jatuh_tempo,
						'',
						'',
						''
					FROM
						data_fa.tblrekon_bank
					UNION
					SELECT
						no_batch,
						total,
						nm_partner,
						fk_bank,
						'',
						tgl_batch,
						'',
						NULL,
						transaksi,
						bulan,
						tahun
					FROM
						data_fa.tblbatch_payment
					LEFT JOIN
						(
							SELECT
								kd_partner,
								nm_partner
							FROM
								tblpartner
							UNION ALL
							SELECT
								no_cif,
								nm_customer
							FROM
								tblcustomer
						) AS tblpartner ON kd_partner=fk_partner
					LEFT JOIN
						(
							SELECT
								referensi,
								SUM(nilai_bayar) AS total,
								transaksi
							FROM
								data_gadai.tblhistory_sbg
							WHERE
								tgl_batal IS NULL
							GROUP BY
								referensi,
								transaksi
						) AS tblhistori ON no_batch=referensi
					UNION
					SELECT
						no_voucher,
						total,
						nm_partner,
						fk_bank_ho,
						fk_cabang_ho,
						tgl_voucher,
						NULL,
						NULL,
						'',
						'',
						''
					FROM
						data_fa.tblpayment_request
					LEFT JOIN
						(
							SELECT
								kd_partner,
								nm_partner
							FROM
								tblpartner
						) AS tblpartner ON kd_partner=fk_partner
				) AS tblmain
			LEFT JOIN
				tblbank ON fk_bank = kd_bank
			LEFT JOIN
				tblcabang ON fk_cabang = kd_cabang
			WHERE
				(no_voucher like '%PMR%' or no_voucher like 'BPB%' or no_voucher like '%MB%')
				AND nm_bank like 'BANK%'
				AND tgl_voucher >= '" . $periode_awal . " 00:00:00'
				AND tgl_voucher <= '" . $periode_akhir . " 23:59:59'
			ORDER BY
				tgl_voucher, no_voucher ASC
		";
	$lrow=pg_query($query);

	while ($row = pg_fetch_array($lrow)) {
		// Determine the query based on the transaction type
		if (strpos($row["no_voucher"], "BPB") !== false) {
			$keterangan = $row["transaksi"];
			$queryNoRek = "SELECT kd_partner, nm_partner, nm_bank, nm_rekening, no_rek, nm_bank_tac, nm_rekening_tac, no_rek_tac, nm_bank_tac_lain, nm_rekening_tac_lain, no_rek_tac_lain 
						   FROM data_fa.tblbatch_payment
						   LEFT JOIN tblpartner ON kd_partner = fk_partner
						   WHERE no_batch = '".$row["no_voucher"]."'";
		} else {
			if(strpos($row["no_voucher"], "MB") !== false){
				$queryNoRek = "SELECT 
								'PT Capella Multidana' as nm_partner,
								CASE 
									WHEN POSITION('(' IN bk_masuk.nm_bank) > 0 THEN 
										SUBSTRING(bk_masuk.nm_bank FROM 1 FOR POSITION('(' IN bk_masuk.nm_bank) - 1)
									ELSE 
										bk_masuk.nm_bank 
								END AS nm_bank,
								CASE 
									WHEN POSITION('(' IN bk_masuk.nm_bank) > 0 THEN 
										REPLACE(SUBSTRING(bk_masuk.nm_bank FROM POSITION('(' IN bk_masuk.nm_bank) + 1 FOR POSITION(')' IN bk_masuk.nm_bank) - POSITION('(' IN bk_masuk.nm_bank) - 1), ' ', '')
									ELSE 
										NULL -- or whatever default value you want to use when the pattern is not found
								END AS no_rek,
								'PT Capella Multidana' as nm_rekening
							FROM 
								data_fa.tblmutasi_bank mb
							LEFT JOIN 
								tblbank bk_keluar ON mb.fk_bank_keluar = bk_keluar.kd_bank
							LEFT JOIN 
								tblbank bk_masuk ON mb.fk_bank_masuk = bk_masuk.kd_bank
							WHERE 
								mb.no_voucher = '".$row["no_voucher"]."'";
			} else{
				$queryNoRek = "SELECT kd_partner, nm_partner, nm_bank, nm_rekening, no_rek, nm_bank_tac, nm_rekening_tac, no_rek_tac 
				FROM data_fa.tblpayment_request
				LEFT JOIN tblpartner ON kd_partner = fk_partner
				WHERE no_voucher = '".$row["no_voucher"]."'";
			}			
			$queryKeterangan = "SELECT reference_transaksi 
								FROM data_accounting.tblgl_auto 
								WHERE fk_owner = '".$row["no_voucher"]."'";
			$lrowKeterangan = pg_fetch_array(pg_query($queryKeterangan));
			$keterangan = $lrowKeterangan["reference_transaksi"] == null ? "BATAL" : $lrowKeterangan["reference_transaksi"];
		}
		// Fetch data for account details
		$lrowNoRek = pg_fetch_array(pg_query($queryNoRek));
	
		// Output table row
		echo "<tr>";
		echo "<td>".date("d/m/Y", strtotime($row["tgl_voucher"]))."</td>";
		echo "<td>".$row["no_voucher"]."</td>";
		echo "<td>".$keterangan."</td>";
		echo "<td>".$row["nm_bank"]."</td>";
		echo "<td>".$lrowNoRek["nm_partner"]."</td>";
		// Output account details based on transaction type
		switch ($row["transaksi"]) {
			case 'Pembayaran TAC Dealer':
				echo "<td>".$lrowNoRek["nm_bank_tac"]."</td>";
				echo "<td>".$lrowNoRek["no_rek_tac"]."</td>";
				echo "<td>".$lrowNoRek["nm_rekening_tac"]."</td>";
				break;
			case 'Pembayaran TAC Lain':
				echo "<td>".$lrowNoRek["nm_bank_tac_lain"]."</td>";
				echo "<td>".$lrowNoRek["no_rek_tac_lain"]."</td>";
				echo "<td>".$lrowNoRek["nm_rekening_tac_lain"]."</td>";
				break;
			case 'Pembayaran TAC SPV':
			case 'Pembayaran TAC Sales':
				$queryKaryawanDealer = "select fk_karyawan_".strtolower(substr($row["transaksi"], 15)).", nm_karyawan, tblkaryawan_dealer.no_rekening, tblkaryawan_dealer.nm_bank, tblkaryawan_dealer.nm_rekening from data_gadai.tbltaksir_umum left join data_gadai.tblhistory_sbg on fk_sbg = no_sbg_ar left join tblkaryawan_dealer on nik = fk_karyawan_".strtolower(substr($row["transaksi"], 15))." where referensi = '".$row["no_voucher"]."'";
				$lrowKaryawanDealer = pg_fetch_array(pg_query($queryKaryawanDealer));
				echo "<td>".$lrowKaryawanDealer["nm_bank"]."</td>";
				echo "<td>".$lrowKaryawanDealer["no_rekening"]."</td>";
				echo "<td>".$lrowKaryawanDealer["nm_rekening"]."</td>";
				break;
			case 'Pembayaran TAC Kacab':
$queryKaryawanDealer = "select nm_karyawan, no_rekening, nm_bank, nm_rekening FROM tblkaryawan_dealer WHERE fk_dealer = '".$lrowNoRek["kd_partner"]."' AND fk_jabatan = 'KACAB' AND karyawan_dealer_active is true";
				$lrowKaryawanDealer = pg_fetch_array(pg_query($queryKaryawanDealer));
				echo "<td>".$lrowKaryawanDealer["nm_bank"]."</td>";
				echo "<td>".$lrowKaryawanDealer["no_rekening"]."</td>";
				echo "<td>".$lrowKaryawanDealer["nm_rekening"]."</td>";
				break;
			case 'Pencairan Datun':
$queryKaryawanDealer = "select fk_cif, nm_customer, no_rekening, nm_bank, nm_rekening from data_gadai.tbltaksir_umum
				left join data_gadai.tblhistory_sbg on fk_sbg = no_sbg_ar
				left join tblcustomer on no_cif = fk_cif
				where referensi = '".$row["no_voucher"]."'";
				$lrowKaryawanDealer = pg_fetch_array(pg_query($queryKaryawanDealer));
				echo "<td>".$lrowKaryawanDealer["nm_bank"]."</td>";
				echo "<td>".$lrowKaryawanDealer["no_rekening"]."</td>";
				echo "<td>".$lrowKaryawanDealer["nm_rekening"]."</td>";
				break;
			default:
				echo "<td>".$lrowNoRek["nm_bank"]."</td>";
				echo "<td>".$lrowNoRek["no_rek"]."</td>";
				echo "<td>".$lrowNoRek["nm_rekening"]."</td>";
		}
		echo "<td align='right'>" . ($keterangan == null ? 0 : convert_money("",$row["total"])) . "</td>";
		echo "</tr>";
	}
		
	echo "</table>";
}