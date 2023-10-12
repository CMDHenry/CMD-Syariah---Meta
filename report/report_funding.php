<?php

include_once("report.php");

function filter_request(){
	global $periode_awal,$periode_akhir,$showPeriode,$showCab,$no_batch,$showTgl,$tgl;
	$showTgl='t';
	//$showCab='t';
	$no_batch=$_REQUEST["no_batch"];
}

function create_filter(){
	global $no_batch;
?>

    <tr bgcolor="efefef">
        <td style="padding:0 5 0 5" width="20%">No Batch</td>
        <td style="padding:0 5 0 5" width="30%"><input name="no_batch" type="text" class='groove_text ' size="20" value="<?=$no_batch?>" ></td>
        
        <td style="padding:0 5 0 5" width="20%"></td>
        <td style="padding:0 5 0 5" width="30%"></td>
    </tr>

<?

}


function excel_content(){
	global $periode_awal,$periode_akhir,$fk_cabang,$no_batch,$produk,$hostname,$database,$port,$username,$tgl;
	
	if($tgl != '' ){
		$lwhere.=" (tgl_lunas > '".$tgl."' or tgl_lunas is null) and tblinventory.tgl_cair <='".$tgl."'";
	}
	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cabang = '".$fk_cabang."' ";
	}
	
	if($no_batch != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" no_batch = '".$no_batch."' ";
	}
	
	
	if ($lwhere!="") $lwhere=" where ".$lwhere;
	
	
	echo 	
	'<table border="1">
	     <tr>
	     	<td align="center">NO</td>
		 	<td align="center">Bank</td>			
		 	<td align="center">No. Kontrak</td>
			<td align="center">Cabang</td>						
			<td align="center">Nama Customer</td>
			<td align="center">Awal Fasilitas</td>
			<td align="center">Jatuh Tempo</td>
			<td align="center">Tenor Awal</td>
			<td align="center">Sisa Tenor</td>		
			<td align="center">Saldo Awal</td>	
			<td align="center">Debet Pokok</td>
			<td align="center">Debet Bunga</td>
			<td align="center">Kredit Pokok</td>						
			<td align="center">Kredit Bunga</td>
			<td align="center">Saldo Akhir Pokok</td>			
			<td align="center">Saldo Akhir Bunga</td>
			<td align="center">Saldo Akhir Total</td>
			<td align="center">Yang Belum Jatuh Tempo</td>
			<td align="center">Tunggakan 01-30 Hari</td>
			<td align="center">Tunggakan 31-60 Hari</td>
			<td align="center">Tunggakan > 60 Hari</td>
			<td align="center">Overdue</td>
			<td align="center">No Batch</td>
			
		  </tr>
	';
	
	
	$query = "
	select case when status_sbg='Liv' then 'BELUM LUNAS' else 'LUNAS' end as ket,*,date_part('day',('".today_db."'-tgl_jt))::numeric as ovd,pinjam_awal as total_ar from (
		select * from tblinventory
		left join(select no_sbg,posisi_bpkb from data_gadai.tblproduk_cicilan left join data_gadai.tbltaksir_umum on fk_fatg=no_fatg)as tblsbg on fk_sbg=no_sbg
	)as tblinventory
	left join (select nm_customer,no_cif from tblcustomer) as tblcust on no_cif=fk_cif 
	inner join(
		select distinct on(fk_sbg)nm_partner,fk_sbg as fk_sbg_funding,no_batch,tgl_funding,tgl_bpkb_kirim,tgl_bpkb_kembali from data_fa.tblfunding
		left join data_fa.tblfunding_detail on no_funding=fk_funding
		left join tblpartner on fk_partner =kd_partner
		where tgl_unpledging is null
	)as tblfunding on tblinventory.fk_sbg = fk_sbg_funding	
	left join viewang_ke on tblinventory.fk_sbg =viewang_ke.fk_sbg			
	left join viewsbg on tblinventory.fk_sbg =fk_sbg1	
	left join (
		select kd_cabang,nm_cabang,nm_wilayah from tblcabang
		left join tblwilayah on fk_wilayah=kd_wilayah
	)as tblcabang on fk_cabang=kd_cabang
	left join(
		select count(1)as sisa_tenor,fk_sbg as fk_sbg_bayar from data_fa.tblangsuran 
		where (tgl_bayar is null or tgl_bayar>'".$tgl."')		
		group by fk_sbg
	)as tblbayar on no_sbg=fk_sbg_bayar		
	left join(
		select sum(pokok_jt)as pokok_c,sum(bunga_jt)as bunga_c,fk_sbg as fk_sbg_lunas from data_fa.tblangsuran 
		where (tgl_bayar is not null and tgl_bayar like '".$tahun_bulan."%' )
		and angsuran_ke>0 group by fk_sbg_lunas
	)as lunas on no_sbg=fk_sbg_lunas		
	
	
	".$lwhere." ";


	$no=1;
	//showquery($query);	
	$tahun_bulan=date("Y-m",strtotime($tgl));
	$lrs = pg_query($query);
	while($lrow=pg_fetch_array($lrs)){
		
		$no_sbg=$lrow["no_sbg"];
		$query_ovd="
		select * from(
			select date_part('days','".$tgl."'-tgl_jatuh_tempo)as ovd,* from data_fa.tblangsuran 
			where tgl_bayar is null and fk_sbg='".$no_sbg."' order by angsuran_ke desc 
		)as tblmain		
		";
		//showquery($query_ovd);
		$lrs_ovd=pg_query($query_ovd);
		$i=1;
		$ovd=array();
		while($lrow_ovd=pg_fetch_array($lrs_ovd)){
			$lrow["nilai_angsuran"]=$lrow_ovd["nilai_angsuran"];
			$lrow["tgl_jt2"]=$lrow_ovd["tgl_jatuh_tempo"];
			$lrow["ang_ke"]=$lrow_ovd["angsuran_ke"];
						
			if($lrow_ovd[ovd]>0 && $lrow_ovd[ovd]<=30){
				$ovd['1']['ar']+=$lrow_ovd["nilai_angsuran"];
				$ovd['1']['od']=$lrow_ovd["ovd"];
				$ovd['1']['pokok']+=$lrow_ovd["pokok_jt"];
				$ovd['1']['bunga']+=$lrow_ovd["bunga_jt"];
			}
			if($lrow_ovd[ovd]>30 && $lrow_ovd[ovd]<=60){
				$ovd['2']['ar']+=$lrow_ovd["nilai_angsuran"];
				$ovd['2']['od']=$lrow_ovd["ovd"];	
				$ovd['2']['pokok']+=$lrow_ovd["pokok_jt"];
				$ovd['2']['bunga']+=$lrow_ovd["bunga_jt"];							
			}
			if($lrow_ovd[ovd]>60){
				$ovd['3']['ar']+=$lrow_ovd["nilai_angsuran"];
				$ovd['3']['od']=$lrow_ovd["ovd"];		
				$ovd['3']['pokok']+=$lrow_ovd["pokok_jt"];
				$ovd['3']['bunga']+=$lrow_ovd["bunga_jt"];
						
			}
			
			if($lrow_ovd[ovd]>0){
				$lrow["ovd_akhir"]=$lrow_ovd["ovd"];	
				$lrow["total_tunggakan"]+=$lrow_ovd["nilai_angsuran"];
				
			}
			$lrow["saldo_pokok"]+=$lrow_ovd["pokok_jt"];
			$lrow["saldo_bunga"]+=$lrow_ovd["bunga_jt"];
			$lrow["saldo_pinjaman"]+=$lrow_ovd["nilai_angsuran"];
			$i++;
		}
		
		$lrow["pokok_d"]=0;
		$lrow["bunga_d"]=0;
		$tahun_bulan_cair=date("Y-m",strtotime($lrow["tgl_cair"]));
		if($tahun_bulan==$tahun_bulan_cair){//hanya untuk bulan pencairan
			$lrow["pokok_d"]=$lrow["pokok_awal"];
			$lrow["bunga_d"]=$lrow["bunga_awal"];
			if($lrow["addm_addb"]=='M'){
				$lrow["pokok_d"]-=$lrow['nilai_angsuran'];				
			}
		}
		if($lrow["addm_addb"]=='M'){
			$lrow["pokok_c"]-=$lrow['nilai_angsuran'];
		}		
			
		if($lrow["ovd"]<0)$lrow["ovd"]=0;
		echo '
			<tr>
				<td valign="top">&nbsp;'.$no.'</td>
				<td valign="top">'.$lrow["nm_partner"].'</td>				
				
				<td valign="top">&nbsp;'.$lrow["fk_sbg1"].'</td>
				<td valign="top">'.$lrow["nm_cabang"].'</td>				
				<td valign="top">'.$lrow["nm_customer"].'</td>
				<td valign="top">'.($lrow["tgl_cair"]==""?"":date("d/m/Y",strtotime($lrow["tgl_cair"]))).'</td>
				<td valign="top">'.($lrow["tgl_jt"]==""?"":date("d/m/Y",strtotime($lrow["tgl_jt"]))).'</td>
				<td valign="top" align="right">'.$lrow["tenor"].'</td>
				<td valign="top" align="right">'.$lrow["sisa_tenor"].'</td>				
				<td valign="top" align="right">'.number_format($lrow["pokok_awal"]).'</td>				
				<td valign="top">'.number_format($lrow["pokok_d"]).'</td>
				<td valign="top">'.number_format($lrow["bunga_d"]).'</td>
				<td valign="top">'.number_format($lrow["pokok_c"]).'</td>
				<td valign="top">'.number_format($lrow["bunga_c"]).'</td>
				<td valign="top">'.number_format($lrow["saldo_pokok"]).'</td>
				<td valign="top">'.number_format($lrow["saldo_bunga"]).'</td>
				<td valign="top">'.number_format($lrow["saldo_pinjaman"]).'</td>
				<td valign="top">'.number_format(($lrow["total_ar"]-$lrow["total_tunggakan"])).'</td>
				<td valign="top">'.number_format($ovd['1']['ar']).'</td>
				<td valign="top">'.number_format($ovd['2']['ar']).'</td>
				<td valign="top">'.number_format($ovd['3']['ar']).'</td>
				<td valign="top" align="right">'.$lrow["ovd"].'</td>
				<td valign="top" >'.$lrow["no_batch"].'</td>
			</tr>
		';	
		$no++;
	
	}
	echo '</table>';
}


