<?
//header("Cache-Control: ");
//header("Pragma: ");
set_time_limit(0);
require 'numeric.inc.php';

function hitung_gp_unit_optional($harga_jual_unit,$dpp,$bunga_promes,$optional_non_ppn){
	return $harga_jual_unit - $dpp - $bunga_promes - $optional_non_ppn;
}

function hitung_dpp($tgl_faktur,$harga_cod,$ex_ppn_bm){
	if(strtotime($tgl_faktur) < strtotime("01/01/2001")){
		return ($harga_cod / 1.1) - $ex_ppn_bm;
	}else{
		return ($harga_cod - $ex_ppn_bm) / 1.1;
	}
}

function hitung_hpp_unit($dpp,$bunga_promes,$ex_ppn_bm){
	$hasil = ($dpp * 1.1) + $bunga_promes;
	(date("Y")>2000)?$hasil = $hasil + 0: $hasil = $hasil + $ex_ppn_bm;
	return $hasil;
}

function hitung_penjualan_unit_bersih($harga_jual_unit,$potongan_penjualan){
	return $harga_jual_unit - $potongan_penjualan;
}

function hitung_harga_jual_ofr($harga_jual_kendaraan,$harga_jual_surat,$ex_ppn_bm,$optional){
	$hasil = $harga_jual_kendaraan - $harga_jual_surat - $optional;
	(date("Y")>2000)?$hasil = $hasil - 0: $hasil = $hasil - $ex_ppn_bm;
	return $hasil;
}

function hitung_harga_beli_ofr($harga_cod,$ex_ppn_bm){
	$hasil = $harga_cod;
	(date("Y")>2000)?$hasil = $hasil - 0: $hasil = $hasil - $ex_ppn_bm;
	return $hasil;
}

function hitung_harga_ofr_bersih($harga_beli_ofr,$subsidi_atpm){
	return $harga_beli_ofr - $subsidi_atpm;
}

function hitung_total_hpp($harga_beli_ofr_bersih,$bunga_promes){
	return $harga_beli_ofr_bersih + $bunga_promes;
}

function hitung_gp_surat($harga_jual_surat,$harga_beli_surat){
	return $harga_jual_surat - $harga_beli_surat;
}

function hitung_gp_unit_plus_bbn($harga_jual_ofr_bersih,$harga_beli_ofr_bersih,$bunga_promes,$gp_surat){
	return $harga_jual_ofr_bersih - $harga_beli_ofr_bersih - $bunga_promes + $gp_surat;
}

function hitung_gp_net($gp_unit_plus_bbn,$pendapatan_lain2,$total_biaya_penjualan){
	return $gp_unit_plus_bbn + $pendapatan_lain2 - $total_biaya_penjualan;
}

function hitung_total_pendapatan($other_income,$other_expense,$asco_vip){
	$hasil = $other_income - $other_expense - $asco_vip;
	return $hasil;
}
function hitung_total_biaya_penjualan($insentif,$ongkos_kirim,$komisi,$pdi){
	$hasil = $insentif + $ongkos_kirim + $komisi + $pdi;
	return $hasil;
}

function hitung_laba_rugi_unit_optional($total_unit_optional,$total_hpp){
	return $total_unit_optional - $total_hpp;
}

function hitung_penjualan_unit($harga_jual_unit,$potongan_penjualan,$harga_jual_surat,$ex_ppn_bm){
	$hasil = $harga_jual_unit - $potongan_penjualan - $harga_jual_surat;
	(date("Y")>2000)?$hasil = $hasil - 0: $hasil = $hasil - $ex_ppn_bm;
	$hasil = $hasil / 1.1;
	return $hasil;
}

function hitung_penjualan_unit_optional($penjualan_unit,$optional){
	$hasil = $penjualan_unit + $optional;
	return $hasil;
}

function convert_header($pField,$pTotalRow,$pNumber=true){
echo "
<tr align='center'>";
if($pNumber)echo "<td>NO</td>";
	for($x=0;$x < $pTotalRow;$x++){
		$lField=split('_',$pField[$x]);
		$txt="";
		switch($lField[0]){
			case "kd":
				for($i=1;$i < count($lField);$i++){
					$txt.=strtoupper($lField[$i])." ";
				}
				echo "<td>KODE ".$txt."</td>";
			break;
			case "tgl":
				for($i=1;$i < count($lField);$i++){
					$txt.=strtoupper($lField[$i])." ";
				}
				echo "<td>TANGGAL ".$txt."</td>";
			break;
			case "nm":
				for($i=1;$i < count($lField);$i++){
					$txt.=strtoupper($lField[$i])." ";
				}
				echo "<td>NAMA ".$txt."</td>";
			break;
			default:
				for($i=0;$i < count($lField);$i++){
					$txt.=strtoupper($lField[$i])." ";
				}
				echo "<td>".$txt."</td>";
			break;
		}
	}
echo "</tr>";
}

function convert_header_simple($p){
	$lField=split('_',$p);
	$txt="";
	switch($lField[0]){
		case "kd":
			for($i=1;$i < count($lField);$i++){
				$txt.=strtoupper($lField[$i])." ";
			}
			return "KODE ".$txt."";
		break;
		case "tgl":
			for($i=1;$i < count($lField);$i++){
				$txt.=strtoupper($lField[$i])." ";
			}
			return "TANGGAL ".$txt."";
		break;
		case "nm":
			for($i=1;$i < count($lField);$i++){
				$txt.=strtoupper($lField[$i])." ";
			}
			return "NAMA ".$txt."";
		break;
		default:
			for($i=0;$i < count($lField);$i++){
				$txt.=strtoupper($lField[$i])." ";
			}
			return $txt;
		break;
	}
}

function pastMonth($pDate,$pMonth){ //pDate => today, pMonth => brp bulan mundur
	$lArr = array(0=>$pDate);

	$current_d = date('d',strtotime($pDate));
	$current_m = date('m',strtotime($pDate));
	$current_y = date('Y',strtotime($pDate));
	
	if($current_d<=26){
		for($i=1;$i<=$pMonth;$i++){
			$lArr[$i] = date('m/d/Y',strtotime('-'.$i.' month',strtotime($pDate)));
		}
	}else{
		for($i=1;$i<=$pMonth;$i++){
			if($current_m <= $i){
				$m = $current_m - $i;
				$m += 12;
				$y = $current_y - 1;
			}else {
				$m = $current_m - $i;
				$y = $current_y;
			}
			$d = date("d", strtotime('-1 second',strtotime('+1 month',strtotime($m.'/01/'.$y))));
			if($current_d < $d)$d = $current_d;
			$lArr[$i] = $m.'/'.$d.'/'.$y;
		}
	}
	return $lArr;
}

function convert_data($pData,$pField){
	$lField=split('_',$pField);
	if($lField[0]=='tgl' && $pData!=""){
		return date('d/m/Y',strtotime($pData));
	}elseif(!check_type(float,$pData)){
		return convert_money("",$pData,2);
	}else return $pData;
}

function fCreateHeader($pTotalRow,$pCabang = NULL){
	global $report,$lPeriode,$pTanggal;
	
	$lrow_header = pg_fetch_array(pg_query("
				select nm_perusahaan,alamat,nm_area,(nm_merek||' '||nm_cabang)as nm_cabang from tblcabang 
				inner join tblarea on fk_area = kd_area
				inner join tblmerek on fk_merek = kd_merek
				where kd_cabang = '".(($pCabang)?$pCabang:$_SESSION['ho_kd_cabang'])."'"
				));

	$lreport = str_replace("_"," ",$report);
	
	$lreport= " ".strtoupper($lreport);
	echo "
		<table>
			<tr>
				<td colspan='3'>".$lrow_header["nm_perusahaan"]."</td>
	";
	for($i=0;$i < $pTotalRow-3;$i++){
		echo "<td></td>";
	}
	echo "
				<td><!--Tanggal :&nbsp;".date("d/m/Y")."--></td>
			</tr>
			<tr>
				<td colspan='3'>".$lrow_header["alamat"]."</td>
	";
	for($i=0;$i < $pTotalRow-3;$i++){
		echo "	<td></td>";
	}
	echo "
				<td><!--Jam : &nbsp;".date("H:i:s")."--></td>
			</tr>
			<tr>
				<td colspan='3'>".$lrow_header["nm_area"]."</td>
	";
	for($i=0;$i < $pTotalRow-3;$i++){
		echo "	<td></td>";
	}
	echo "
				<td><!--Cabang : &nbsp;".$lrow_header["nm_cabang"]."--></td>
			</tr>
			<tr>
				<td colspan='".$pTotalRow."'>LAPORAN ".$lreport." </td>
			</tr>";
	if($lPeriode){
	echo"
			<tr>
				<td colspan='".$pTotalRow."'>PER TANGGAL ".convert_date_indonesia($lPeriode[0])." s/d ".convert_date_indonesia($lPeriode[1])." </td>
			</tr>";
	} else if ($pTanggal) {
	echo"
			<tr>
				<td colspan='".$pTotalRow."'>TANGGAL ".$pTanggal." </td>
			</tr>";
	}
	echo "
			<tr><td></td></tr>
		</table>
		";
}
?>
