<?php
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/timestamp.inc.php';
require '../classes/ezpdf.class.php';
require '../requires/convert.inc.php';
require '../requires/numeric.inc.php';

$fk_partner_dealer = $_REQUEST['fk_partner_dealer'];
$id_edit= $_REQUEST['id_edit'];


$query="
select * from (
	select case when tgl_terima is null and tgl_batal is null then 'Ya' end as outs_terima
	,case 
	when fk_cabang_kirim='000' and penerima='HO' then 'Bank' 
	when fk_cabang_kirim='000' and penerima!='HO' then 'HO' 
	when fk_cabang_kirim!='000' then 'Cabang' 
	end as pengirim,
	* from data_fa.tblmutasi_bpkb
	left join tblcabang on fk_cabang_terima=kd_cabang
	left join(
		select kd_cabang as kd_cabang1,nm_kota,jenis_cabang as jenis_cabang_kirim from tblcabang
		left join tblkelurahan on fk_kelurahan=kd_kelurahan
		left join tblkecamatan on fk_kecamatan=kd_kecamatan
		left join tblkota on fk_kota=kd_kota
	)as tblcabang1 on fk_cabang_kirim=kd_cabang1
	left join(
		select kd_cabang as kd_cabang2,jenis_cabang from tblcabang
	)as tblcabang2 on fk_cabang_terima=kd_cabang2	
	left join (
		select kd_partner,nm_partner as nm_bank,alamat as alamat_bank from tblpartner
	)as tblbank on fk_partner=kd_partner	
	where no_mutasi='".$id_edit."'
) as tblmutasi_bpkb
";	
//showquery($query);
$lrow=pg_fetch_array(pg_query($query));			
$jenis_mutasi=strtolower($lrow["pengirim"]."_ke_".$lrow["penerima"]);
$tgl_kirim=date("d/m/Y",strtotime($lrow["tgl_kirim"]));
if($jenis_mutasi=='bank_ke_ho'){
	$jenis_mutasi='ho_ke_bank';//cetakannya sama
}
//echo $jenis_mutasi;
$pdf = new Cezpdf('A4','');  

$pdf->ez['topMargin'] = $heigth-$y_table;
$pdf->ez['bottomMargin']=130;
$pdf->ez['rightMargin']=30;
//$all = $pdf->openObject();
//$pdf->saveState();
//$pdf->ezImage('../print/logo.jpeg','','180','','left','');


$fontsize= 8;
$pdf->selectFont('fonts/Times');

$x1=15;
$y=820;

$x2=120;
$x3=145;

//echo $lrow["jenis_cabang"];
if($lrow["jenis_cabang"]=='Cabang')$nm_jabatan='KACAB';
if($lrow["jenis_cabang"]=='Pos')$nm_jabatan='KAPOS';
$nm_kacab_kapos=get_karyawan_by_jabatan($nm_jabatan,$lrow["fk_cabang_terima"]);


$query1=$query."
left join (
	select(case when fk_permohonan is null then no_srt_mts else fk_permohonan end) as surat, fk_sbg,no_rangka,no_mesin,nm_customer,nm_bpkb,no_polisi,no_bpkb,fk_mutasi,tblmutasi_bpkb_detail.keterangan,no_srt_mts,fkt_kw,fk_partner_dealer,nm_bpkb from data_fa.tblmutasi_bpkb_detail
	left join data_fa.tblmutasi_bpkb on no_mutasi=fk_mutasi
	left join data_gadai.tbltaksir_umum on fk_sbg=no_sbg_ar
	left join tblcustomer on fk_cif=no_cif
) as tblmutasi_bpkb_detail on fk_mutasi=no_mutasi
left join(
	select fk_sbg as fk_sbg1,saldo_denda from data_fa.tbldenda	
)as tbldenda on fk_sbg=fk_sbg1
left join(
	select fk_sbg as fk_sbg2,saldo_pinjaman from viewang_ke	
)as viewang_ke on fk_sbg=fk_sbg2
left join (
	select kd_partner as kd_dealer,nm_partner as nm_dealer from tblpartner
)as tbldealer on fk_partner_dealer=kd_dealer	
left join(
	select distinct on(fk_sbg)fk_sbg as fk_sbg_funding, no_funding,tgl_funding, tgl_unpledging,no_batch as no_batch_funding,nm_partner as nm_bank_funding from data_fa.tblfunding
	left join data_fa.tblfunding_detail on fk_funding=no_funding	
	left join tblpartner on fk_partner =kd_partner	
)as tbl on fk_sbg=fk_sbg_funding
left join(
	select no_permohonan,fk_sbg as fk_sbg3,sisa_denda,sisa_angsuran from data_fa.tblpermohonan_bpkb
	left join data_fa.tblpermohonan_bpkb_detail on fk_permohonan =no_permohonan
)as tblpermohonan on fk_permohonan=no_permohonan and fk_sbg=fk_sbg3
";
$total_data=pg_num_rows(pg_query($query1));	

$pdf->addText($x1, $y, $fontsize+2,'No SP : '.$id_edit);
$y-=22;
//echo $lrow["pengirim"];
if($jenis_mutasi=='cabang_ke_ho'){
	$pdf->addText($x1, $y, $fontsize,'Harap diterima '.$total_data.' buah BPKB dengan perincian sebagai berikut :');
}
if($jenis_mutasi=='ho_ke_cabang'){
	$pdf->addText($x1, $y, $fontsize,'Kepada Yth,');
	$y-=10;
	$pdf->addText($x1, $y, $fontsize,$lrow["nm_perusahaan"].' Cab '.$lrow["nm_cabang"]);
	$y-=10;
	$pdf->addText($x1, $y, $fontsize,$lrow["alamat"]);
	$y-=10;
	$pdf->addText($x1, $y, $fontsize,'Up, '.$nm_kacab_kapos);
	$y-=20;
	$pdf->addText($x1, $y, $fontsize,'Harap diterima '.$total_data.' buah BPKB dengan perincian sebagai berikut :');	
}
if($jenis_mutasi=='ho_ke_bank' ){
	$pdf->addText($x1, $y, $fontsize,'Kepada Yth,');
	$y-=10;
	$pdf->addText($x1, $y, $fontsize,'Pimpinan '.$lrow["nm_bank"]);
	$y-=10;
	$pdf->addText($x1, $y, $fontsize,'di '.$lrow["alamat_bank"]);
	$y-=20;
	$pdf->addText($x1, $y, $fontsize,'Perihal : Penyerahan BPKB');
	$y-=20;
	$pdf->addText($x1, $y, $fontsize,'Dengan hormat,');
	$pdf->ezSetY($y);		
	$y-=20;
	if($lrow["pengirim"]=='Bank'){
	$pdf->ezText('        Dengan ini kami dari perusahaan '.$lrow["nm_perusahaan"].' menerima '.$total_data.' ('.convert_terbilang($total_data).' ) buah BPKB, Faktur Penjualan, dan Kwitansi Kosong dengan data-data sebagai berikut :',$fontsize,array('justification'=>'full','left'=>5));
	}else{
	$pdf->ezText('        Dengan ini kami dari perusahaan '.$lrow["nm_perusahaan"].' menyerahkan '.$total_data.' ('.convert_terbilang($total_data).' ) buah BPKB, Faktur Penjualan, dan Kwitansi Kosong dengan data-data sebagai berikut :',$fontsize,array('justification'=>'full','left'=>5));
	}
}

$y-=10;
$pdf->ezSetY($y);
$i=1;
//showquery($query1);
$lrs1=pg_query($query1);
while($lrow1 = pg_fetch_array($lrs1)){
	//echo 'abb';
	if(pg_num_rows(pg_query("select * from data_gadai.tbllelang where fk_sbg='".$lrow1["fk_sbg"]."' and status_data='Approve'"))){
		$lrow1["saldo_denda"]=0;//jual denda ga bayar
	}
	if($jenis_mutasi=='ho_ke_cabang'){
		$data[$i]['no'] = $i;
		if($lrow1["nm_bpkb"]==$lrow1["nm_customer"]){
			$data[$i]['nama'] =  $lrow1["nm_customer"];
		} else{
			$data[$i]['nama'] =  $lrow1["nm_customer"] . ' / ' . $lrow1["nm_bpkb"];
		}
		$data[$i]['no_kontrak'] = $lrow1["fk_sbg"];
		$data[$i]['no_rangka_engine'] =  $lrow1["no_rangka"]." / ".$lrow1["no_mesin"];
		$data[$i]['no_polisi'] =  $lrow1["no_polisi"];
		$data[$i]['no_bpkb'] =  $lrow1["no_bpkb"];
		$data[$i]['ket'] = $lrow1["keterangan"];
		$data[$i]['sisa_ang'] =  convert_money("",($lrow1["saldo_pinjaman"]));
		$data[$i]['sisa_denda'] =  convert_money("",($lrow1["saldo_denda"]));
		$data[$i]['fkt_kw'] = (($lrow1["fkt_kw"]=="YA")?"V":"");
		$data[$i]['surat'] = $lrow1["surat"];
	}
	if($jenis_mutasi=='cabang_ke_ho'){
		$data[$i]['no'] = $i;
		if($lrow1["nm_bpkb"]==$lrow1["nm_customer"]){
			$data[$i]['nama'] =  $lrow1["nm_customer"];
		} else{
			$data[$i]['nama'] =  $lrow1["nm_customer"] . ' / ' . $lrow1["nm_bpkb"];
		};
		$data[$i]['no_kontrak'] = $lrow1["fk_sbg"];
		$data[$i]['no_rangka'] = $lrow1["no_rangka"];
		$data[$i]['no_mesin'] = $lrow1["no_mesin"];
		$data[$i]['no_polisi'] =  $lrow1["no_polisi"];
		$data[$i]['tgl_terima_bpkb'] =  date("d-m-Y",strtotime($lrow1["tgl_terima_bpkb"]));
		$data[$i]['no_batch_funding'] = $lrow1["no_batch_funding"];
		$data[$i]['nm_dealer'] = $lrow1["nm_dealer"];
	}
	
	if($jenis_mutasi=='ho_ke_bank'){
		$data[$i]['no'] = $i;
		if($lrow1["nm_bpkb"]==$lrow1["nm_customer"]){
			$data[$i]['nama'] =  $lrow1["nm_customer"];
		} else{
			$data[$i]['nama'] =  $lrow1["nm_customer"] . ' / ' . $lrow1["nm_bpkb"];
		}
		$data[$i]['no_kontrak'] = $lrow1["fk_sbg"];
		$data[$i]['no_rangka_engine'] =  $lrow1["no_rangka"]." / ".$lrow1["no_mesin"];
		$data[$i]['no_polisi'] =  $lrow1["no_polisi"];
		$data[$i]['no_bpkb'] =  $lrow1["no_bpkb"];
	}	
	
	$i++;
}

$lining['showHeadings'] = 1;
$lining['shaded'] = 0;
$lining['showLines'] =1 ;
$lining['xPos'] = 296;
$lining['fontSize'] = 7.5;
$lining['colGap'] = 2;

if($jenis_mutasi=='ho_ke_cabang'){
	$judul['no'] = 'No.';
	$judul['nama'] = 'Nama';
	$judul['no_kontrak'] = 'Nomor Kontrak';
	$judul['no_rangka_engine'] = 'Chasis/Engine';
	$judul['no_polisi'] = 'No Polisi';
	$judul['no_bpkb'] = 'No BPKB';
	$judul['fkt_kw'] = 'FKT/
	KW';
	$judul['sisa_ang'] = 'Sisa Angs';
	$judul['sisa_denda'] = 'Sisa Denda';
	$judul['surat'] = 'NO.SRT MTS';
	
	$lining['cols']['no']['justification'] = 'left';
	$lining['cols']['nama']['justification'] = 'left';
	$lining['cols']['no_kontrak']['justification'] = 'left';
	$lining['cols']['no_rangka_engine']['justification'] = 'left';
	$lining['cols']['no_polisi']['justification'] = 'left';
	$lining['cols']['no_bpkb']['justification'] = 'left';
	$lining['cols']['ket']['justification'] = 'left';
	$lining['cols']['sisa_ang']['justification'] = 'right';
	$lining['cols']['sisa_denda']['justification'] = 'right';
	
	$lining['cols']['no']['heading_justification'] = 'left';
	$lining['cols']['nama']['heading_justification'] = 'left';
	$lining['cols']['no_kontrak']['heading_justification'] = 'left';
	$lining['cols']['no_rangka_engine']['heading_justification'] = 'left';
	$lining['cols']['no_polisi']['heading_justification'] = 'left';
	$lining['cols']['no_bpkb']['heading_justification'] = 'left';
	$lining['cols']['ket']['heading_justification'] = 'left';
	
	$size['no'] = '18';
	$size['nama'] = '72';
	$size['no_kontrak'] = '65';
	$size['no_rangka_engine'] = '100';
	$size['no_polisi'] = '50';
	$size['no_bpkb'] = '55';
	$size['ket'] = '70';
	$size['sisa_ang'] = '50';
	$size['sisa_denda'] = '40';
	$size['surat'] = '90';
	
}
if($jenis_mutasi=='cabang_ke_ho'){
	$judul['no'] = 'No.';
	$judul['nama'] = 'Nama';
	$judul['no_kontrak'] = 'Nomor Kontrak';
	$judul['no_rangka'] = 'Chasis';
	$judul['no_mesin'] = 'Engine';	
	$judul['no_polisi'] = 'No Polisi';
	$judul['tgl_terima_bpkb'] = 'Tgl Terima';
	$judul['no_batch_funding'] = 'No Limpah';
	$judul['nm_dealer'] = 'Dealer';
	
	$lining['showHeadings'] = 1;
	$lining['shaded'] = 0;
	$lining['showLines'] =1 ;
	$lining['xPos'] = 296;
	$lining['fontSize'] = 7.5;
	$lining['colGap'] = 2;
	
	$size['no'] = '16';
	$size['nama'] = '95';
	$size['no_kontrak'] = '63';
	$size['no_rangka'] = '85';
	$size['no_mesin'] = '60';
	$size['no_polisi'] = '48';
	$size['tgl_terima'] = '45';
	$size['no_batch_funding'] = '55';
	$size['nm_dealer'] = '95';

}

if($jenis_mutasi=='ho_ke_bank'){
	$judul['no'] = 'No.';
	$judul['nama'] = 'Nama';
	$judul['no_kontrak'] = 'Nomor Kontrak';
	$judul['no_rangka_engine'] = 'Chasis/Engine';
	$judul['no_polisi'] = 'No Polisi';
	$judul['no_bpkb'] = 'No BPKB';
	
	$size['no'] = '18';
	$size['nama'] = '180';
	$size['no_kontrak'] = '65';
	$size['no_rangka_engine'] = '200';
	$size['no_polisi'] = '45';
	$size['no_bpkb'] = '55';
	
}
//print_r($data);
$pdf->ezTable($data,$judul,'',$lining,$size);

//$pdf->restoreState();
//$pdf->closeObject();
//$pdf->addObject($all,'all');

$y=$pdf->y;
if($jenis_mutasi=='ho_ke_bank'){
	$y-=20;
	$pdf->addText($x1, $y, $fontsize,'Demikian kami sampaikan agar dapat diketahui. Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.');	
}
if($jenis_mutasi=='cabang_ke_ho'){
	$y-=20;
	$pdf->addText($x1, $y, $fontsize,'NB : BPKB yang dikirimkan sudah diperiksa oleh penanggung jawab BPKB di cabang dengan baik.');	
	$y-=10;
	$pdf->addText($x1, $y, $fontsize,'        Apabila dikemudian hari ada perbedaan maka itu akan menjadi tanggung jawab Cabang sendiri');	
	
	$y-=20;
	$pdf->addText($x1, $y, $fontsize,'Demikian kami sampaikan agar dapat diketahui. Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.');	
	
}

$y-=20;
$pdf->addText($x1, $y, $fontsize,$lrow["nm_kota"].", ".$tgl_kirim);
$y-=10;
$x2=170;
$x3=330;
$x4=490;

$user=get_rec("tbluser left join tblkaryawan on npk=fk_karyawan","nm_depan","username='".$_SERVER['username']."'");

if($jenis_mutasi=='ho_ke_cabang'){

	$pdf->addText($x1, $y, $fontsize,'Dibuat oleh:');
	$pdf->addText($x2, $y, $fontsize,'Diperiksa oleh:');
	$pdf->addText($x3, $y, $fontsize,'Diketahui oleh:');
	$pdf->addText($x4, $y, $fontsize,'Diterima oleh:');
	$y-=70;
	$nm_jabatan1='Adm. BPKB';	
	$nm_jabatan2='ACC';
	$nm_jabatan3='SPV. Finance';
	$nm_jabatan4='KASIR';	
	
	//$nm_karyawan1=get_karyawan_by_jabatan($nm_jabatan1,$lrow["fk_cabang_kirim"]);
	$nm_karyawan1=$user;
	$nm_karyawan2=get_karyawan_by_jabatan($nm_jabatan2,$lrow["fk_cabang_terima"]);
	$nm_karyawan3=get_karyawan_by_jabatan($nm_jabatan3,$lrow["fk_cabang_kirim"]);
	$nm_karyawan4=get_karyawan_by_jabatan($nm_jabatan4,$lrow["fk_cabang_terima"]);
	
	if(!$nm_karyawan1)$nm_karyawan1='                      ';
	if(!$nm_karyawan2)$nm_karyawan2='                      ';
	if(!$nm_karyawan3)$nm_karyawan3='                      ';
	if(!$nm_karyawan4)$nm_karyawan4='                      ';
	
	$pdf->addText($x1, $y, $fontsize-1,'('.$nm_karyawan1.')');//adm bpkb
	$pdf->addText($x2, $y, $fontsize-1,'('.$nm_karyawan2.')');// acc
	$pdf->addText($x3, $y, $fontsize-1,'('.$nm_karyawan3.')');//spv fin
	$pdf->addText($x4, $y, $fontsize-1,'('.$nm_karyawan4.')');//kasir/adh

	$y-=30;
	$pdf->addText($x1, $y, $fontsize,'NB.');
	$y-=20;
	$pdf->addText($x1, $y, $fontsize,'1. Harap dibuat laporan stock BPKB tiap bulan.');
	$y-=10;
	$pdf->addText($x1, $y, $fontsize,'2. Sisa denda dan angsuran dibuat berdasarkan tanggal SP BPKB.');
	$y-=10;
	$pdf->addText($x1+10, $y, $fontsize,'Harap disesuaikan ketika penyerahan BPKB ke Customer dan penyerahan BPKB akan menjadi tanggung');
	$y-=10;
	$pdf->addText($x1, $y, $fontsize,'3. Tanda terima yang berwarna merah harap dikirimkan ke Cabang '.$lrow["nm_cabang"].' setelah ditandatangani ');
	$y-=10;
	$pdf->addText($x1, $y, $fontsize,'4. Untuk angsuran dan denda yang belum lunas, jika dalam waktu 2 minggu customer tidak membayar dan tidak mengambil bpkb tersebut');
	$y-=10;
	$pdf->addText($x1+10, $y, $fontsize,'maka bpkb tersebut wajib dikembalikan ke Cabang '.$lrow["nm_cabang"].'');
	$y-=10;
	$pdf->addText($x1, $y, $fontsize,'5. Jika angsuran dan denda tidak sesuai dengan yang diprogram harap konfirmasi kembali ke Cabang '.$lrow["nm_cabang"].' ');
	//end content

}

if($jenis_mutasi=='ho_ke_bank'){
	$y-=20;
	if($lrow["pengirim"]=='Bank'){
	$pdf->addText($x2-50, $y, $fontsize,'Yang Menyerahkan');
	}else{
	$pdf->addText($x2-50, $y, $fontsize,'Yang Menerima');
	}
	$pdf->addText($x3+50, $y, $fontsize,'Hormat kami');
	
	$y-=60;
	$pdf->addText($x2-50, $y, $fontsize,$lrow["nm_bank"]);
	$pdf->addText($x3+50, $y, $fontsize,$lrow["nm_perusahaan"]);
}

if($jenis_mutasi=='cabang_ke_ho'){
	$pdf->addText($x1, $y, $fontsize,'Dibuat oleh:');
	$pdf->addText($x2, $y, $fontsize,'Diperiksa oleh:');
	$pdf->addText($x3, $y, $fontsize,'Diketahui oleh:');
	$pdf->addText($x4, $y, $fontsize,'Diterima oleh:');
	
	$y-=70;
	$nm_jabatan1='KASIR';	
	$nm_jabatan2='ADH';

	if($lrow["jenis_cabang_kirim"]=='Cabang'){
		$nm_jabatan3='KACAB';	
	}else{
		$nm_jabatan3='KAPOS';
	}

	//echo $lrow["fk_cabang_kirim"].' '.$lrow["jenis_cabang"].' '.$nm_jabatan3;
	$nm_jabatan4='Adm. BPKB';	
	
	//$nm_karyawan1=get_karyawan_by_jabatan($nm_jabatan1,$lrow["fk_cabang_kirim"]);
	$nm_karyawan1=$user;
	$nm_karyawan2=get_karyawan_by_jabatan($nm_jabatan2,$lrow["fk_cabang_kirim"]);
	$nm_karyawan3=get_karyawan_by_jabatan($nm_jabatan3,$lrow["fk_cabang_kirim"]);
	$nm_karyawan4=get_karyawan_by_jabatan($nm_jabatan4,$lrow["fk_cabang_terima"]);
	
	if(!$nm_karyawan1)$nm_karyawan1='                      ';
	if(!$nm_karyawan2)$nm_karyawan2='                      ';
	if(!$nm_karyawan3)$nm_karyawan3='                      ';
	if(!$nm_karyawan4)$nm_karyawan4='                      ';
	
	$pdf->addText($x1, $y, $fontsize-1,'('.$nm_karyawan1.')');//adm bpkb
	$pdf->addText($x2, $y, $fontsize-1,'('.$nm_karyawan2.')');// acc
	$pdf->addText($x3, $y, $fontsize-1,'('.$nm_karyawan3.')');//spv fin
	$pdf->addText($x4, $y, $fontsize-1,'('.$nm_karyawan4.')');//kasir/adh	

}
$pdf->ezStream();   

?>