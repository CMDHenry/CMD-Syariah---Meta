<?php

include_once("report.php");

function filter_request(){
	global $jenis_produk,$showCab,$showTgl,$no_cif;
	$jenis_produk=($_REQUEST["jenis_produk"]);
	$no_cif=($_REQUEST["no_cif"]);
	$showCab='t';
	$showTgl='t';
}




function create_filter(){
	global $jenis_produk;
?>

       
   <tr bgcolor="efefef">
        <td style="padding:0 5 0 5" width="20%">Jenis Produk</td>
        <td style="padding:0 5 0 5" width="30%">
        <select id='jenis_produk' name="jenis_produk">
                <option value=""   <?=(($jenis_produk == '')?'selected':'') ?>>--Pilih--</option>
                <option value="0"<?= (($jenis_produk=='0')?"selected":"") ?>>Gadai</option>
                <option value="1"<?=(($jenis_produk=='1')?"selected":"") ?>>Cicilan</option>
             </select>
        </td>
        <td style="padding:0 5 0 5" width="20%"></td>
        <td style="padding:0 5 0 5" width="30%"></td>
     </tr>
                
 
<?	
}

function excel_content(){
	global $tgl,$fk_cabang,$nm_cabang,$jenis_produk,$no_cif;
	if($tgl != '' ){
		$lwhere.=" (tgl_lunas > '".$tgl."' or tgl_lunas is null) and tgl_cair <='".$tgl."'";
	}	
	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cabang = '".$fk_cabang."' ";
	}
	
	if($jenis_produk != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" jenis_produk = '".$jenis_produk."' ";
	}
	
	if ($lwhere!="") $lwhere=" where ".$lwhere;
	
	
	$query = "
		select nilai_pinjaman,nm_barang,round(rate*(360/jumlah_hari),2) as rate_pa, 
		case when karat =0 or berat_bersih =0 then 0 else 
			case when status_antam='Berlian' then round(nilai_taksir/jumlah/karat) 
     		else round(nilai_taksir/karat*24/berat_bersih) end
		end	as harga_pasar, * from( 
			select * from (
				select   date_part('day',('".$tgl."'-tgl_jt))::numeric as ovd,tgl_lunas, tgl_cair,tgl_cair as tgl_booking, status_sbg, fk_sbg as no_sbg1,fk_cabang,fk_cif,kd_produk,fk_produk,jumlah_hari from tblinventory
				left join (
					select kd_produk,nm_produk,jenis_produk,jumlah_hari from tblproduk
					left join tblrate on fk_rate=kd_rate
				) as tblproduk on kd_produk=fk_produk
				".$lwhere."
			) as tblinventory
			where tgl_cair is not null 
		) as tblsbg
		left join viewang_ke on no_sbg1=fk_sbg			
		left join viewsbg on no_sbg1=fk_sbg1
		left join tblcustomer on no_cif=fk_cif 
		left join tblkelurahan on kd_kelurahan=fk_kelurahan_tinggal
		left join tblkecamatan on kd_kecamatan=fk_kecamatan
		left join tblkota on kd_kota=fk_kota		
		left join (select fk_sbg as fk_sbg2,overdue from viewjatuh_tempo) as viewjatuh_tempo on no_sbg1=fk_sbg2
		left join viewkontrak on no_sbg=no_sbg1		
		left join (
			select fk_barang,total_taksir,no_fatg,berat_bersih,nilai_taksir,karat,keterangan_barang,jumlah from viewtaksir
			left join (
				select * from viewtaksir_detail 
			) as tbltaksir_detail on no_fatg=fk_fatg 
		) as tbltaksir on no_fatg=fk_fatg
		left join(
			select fk_barang as fk_barang1,nilai_taksir as nilai_taksir1,nilai_pinjaman,fk_sbg as fk_sbg_detail from data_gadai.tblproduk_gadai_detail
			union all
			select fk_barang,nilai_taksir,nilai_pinjaman,fk_sbg from data_gadai.tblproduk_cicilan_detail		
		)as tblsbg_detail on fk_sbg_detail=no_sbg1 and fk_barang1=fk_barang and nilai_taksir1=nilai_taksir
		left join (select npk, fk_jabatan, nm_depan||' '||nm_belakang as nm_karyawan from tblkaryawan) as tblkaryawan on npk=fk_karyawan_sales
		left join tbljabatan on kd_jabatan=fk_jabatan
		left join (
			select kd_barang, nm_barang,status_antam from tblbarang
		)as tblbarang on fk_barang = kd_barang
		order by tgl_booking,no_sbg1	
	";
	//showquery($query);
	echo 	
	'<table border="1">
	     <tr>
		 	<td align="center">Produk</td>
			<td align="center">Kode Cabang</td>
			<td align="center">No SBG</td>
			<td align="center">No CIF</td>
			<td align="center">Nama Customer</td>
			<td align="center">Tanggal Cair</td>
			<td align="center">Tenor</td>
			<td align="center">Ke</td>
			<td align="center">Overdue</td>
			<td align="center">Angsuran</td>
			<td align="center">Rate Flat</td>
			<td align="center">Pokok Awal</td>
			<td align="center">Biaya Admin</td>			
			<td align="center">No</td>			
			<td align="center">Model</td>			
			<td align="center">Taksir</td>
			<td align="center">Karat</td>
			<td align="center">Berat</td>
			<td align="center">Harga Pasar</td>
			<td align="center">Nama Barang</td>
			<td align="center">Nilai Pinjaman</td>
		  </tr>
	';
	$lrs = pg_query($query);
	$no=1;
	
	while($lrow=pg_fetch_array($lrs)){
		echo '<tr>';
		if($temp!=$lrow["no_sbg1"]){
			$no=1;
			$temp=$lrow["no_sbg1"];
			echo '
			
				<td valign="top">'.$lrow["kd_produk"].'</td>
				<td valign="top">'.$lrow["fk_cabang"].'</td>
				<td valign="top">&nbsp;'.$lrow["no_sbg1"].'</td>
				<td valign="top">'.$lrow["no_cif"].'</td>
				<td valign="top">'.$lrow["nm_customer"].'</td>
				<td valign="top">'.($lrow["tgl_booking"]==""?"":date("d/m/Y",strtotime($lrow["tgl_booking"]))).'</td>
				<td valign="top">'.$lrow["tenor"].'</td>
				<td valign="top">'.$lrow["ang_ke"].'</td>
				<td valign="top">'.$lrow["ovd"].'</td>
				<td valign="top" align="right">'.$lrow["angsuran"].'</td>
				<td valign="top">'.$lrow['rate_pa'].'</td>
				<td valign="top" align="right">'.$lrow["pokok_awal"].'</td>
				<td valign="top" align="right">'.$lrow["biaya_admin"].'</td>	
			';
		}else{
			echo '
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			<td valign="top"></td>
			';
			$no++;
		}
		
		echo '
				<td valign="top">'.$no.'</td>											
				<td valign="top">'.$lrow["keterangan_barang"].'</td>							
				<td valign="top" align="right">'.$lrow["nilai_taksir"].'</td>
				<td valign="top" align="right">'.$lrow["karat"].'</td>				
				<td valign="top" align="right">'.$lrow["berat_bersih"].'</td>
				<td valign="top" align="right">'.$lrow["harga_pasar"].'</td>
				<td valign="top">'.$lrow["nm_barang"].'</td>
				<td valign="top" align="right">'.$lrow["nilai_pinjaman"].'</td>
			</tr>
		';	
		
	
	}
	echo '</table>';
}


