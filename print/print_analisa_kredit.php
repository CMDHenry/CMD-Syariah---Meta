<?php

/*

+-----------------------------------------------------------------+
|     Created by Chirag Mehta - http://chir.ag/projects/pdfb      |
|-----------------------------------------------------------------|
|                      For PDFB Library                           |
+-----------------------------------------------------------------+

*/

//error_reporting (E_ALL);  // remove this from Production Environment
require('pdfb/pdfb.php'); // Must include this
require '../requires/db_utility.inc.php';
require '../requires/general.inc.php';
require '../requires/timestamp.inc.php';
require '../requires/numeric.inc.php';
include_once("../requires/connection_db.inc.php");	
include_once("../requires/session.inc.php");	
//include_once("../requires/config.inc.php");

  // Recommended way to use PDFB Library
  // - create your own PDF class
  // - instantiate it wherever necessary
  // - you can create multiple classes extending from PDFB
  //   for each different report
  

class PDF extends PDFB
{
	function BasicTable($header, $data,$x,$y,$option=array())
	{
		
		$this->setY($y-5);

		// Header
		foreach($header as $col){
			
			if($option['w'][$col])$w=$option['w'][$col];
			else $w=60;
			if($option['header_align'][$col])$align=$option['header_align'][$col];
			else $align='';				
			$this->Cell($w,15,$col,1,'',$align,'','',$x);
		}
		$this->Ln();
		// Data
		if(count($data)>0){
		foreach($data as $row)
		{	
			//print_r($row);						
			foreach($row as $col=>$input){
				if($option['w'][$col])$w=$option['w'][$col];
				else $w=60;
				
				if($option['align'][$col])$align=$option['align'][$col];
				else $align='';				
				$this->Cell($w,15,$input,1,'',$align,'','',$x);
			}
			$this->Ln();
		}
		}
	}
}
	$pdf = new PDF("p", "pt", "A4");
	$pdf->AddPage();

	$id_edit=$_REQUEST["fk_sbg"];
	
	$query="
	select case when pembayaran='1' then 'Tunai' else 'Transfer' end as jenis_pembayaran,* from data_gadai.tblproduk_cicilan
	left join data_gadai.tbltaksir_umum on fk_fatg=no_fatg
	left join tblcustomer on fk_cif=no_cif
	left join(
		select * from viewkendaraan
	)as tblbarang on tblbarang.no_fatg=tbltaksir_umum.no_fatg
	left join tblwarna on fk_warna=kd_warna
	inner join tblcabang on fk_cabang=kd_cabang
	left join tblpartner on tbltaksir_umum.fk_partner_dealer=kd_partner
	left join tbltujuan_transaksi on fk_tujuan_transaksi=kd_tujuan_transaksi
	where no_sbg='".$id_edit."'";
	//echo($query);
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	$kacab=kacab($lrow["fk_cabang"],$lrow["tgl_pengajuan"]);
	$nm_kacab=$kacab["nm_depan"];
	$tgl_pengajuan=date("d",strtotime($lrow['tgl_pengajuan'])).' '.getMonthName(date("m",strtotime($lrow['tgl_pengajuan'])),2).' '.date("Y",strtotime($lrow['tgl_pengajuan']));
	
	$fk_cabang=$lrow["fk_cabang"];
	
	$x=40;
	$x1=$x+70;
	$x2=$x1+10;
	$x3=$x2+250;
	$x4=$x3+70;
	$x5=$x4+10;
	$y=15;
		
	$pdf->setY($y);
	$pdf->Cell(540,770,"",1);
		
	$pdf->SetFont("Arial", "B", 11);
	$y+=10;
	
	$pdf->Text($x+60,$y, ' LAPORAN ANALISA PEMBIAYAAN & PERSETUJUAN KOMITE PEMBIAYAAN');
	$y+=20;
	
	$pdf->SetFont("Arial", "B", 9);
	$pdf->Text($x,$y, ' Kepada ');
	$pdf->Text($x1,$y, ' : ');
	$pdf->Text($x2,$y, 'Kepala Cabang '.$nm_kacab);
	$y+=10;
	
	$pdf->Text($x,$y, ' Dari ');
	$pdf->Text($x1,$y, ' : ');
	
	$nm_karyawan_ca=get_rec("tblkaryawan","nm_depan","npk='".$lrow["fk_karyawan_ca"]."'");
	$pdf->Text($x2,$y, 'CA '.$nm_karyawan_ca);
	$y+=10;
	
	$pdf->Text($x,$y, ' Perihal ');
	$pdf->Text($x1,$y, ' : ');
	$pdf->Text($x2,$y, 'Persetujuan Pembiayaan dan Pemesanan Barang ');
	$y+=15;
	
	$pdf->SetFont("Arial", "", 9);
	$pdf->Text($x,$y, ' Dengan ini disampaikan Laporan Analisa Permohonan Pembiayaan dengan data sebagai berikut :');
	$y+=20;
	
	$pdf->SetFont("Arial", "B", 9);
	$pdf->Text($x,$y, ' Debitur / HP ');
	$pdf->Text($x1,$y, ' : ');
	$pdf->Text($x2,$y, $lrow["nm_customer"].' / '.$lrow["no_hp"]);
	
	$pdf->Text($x3,$y, ' OTR ');
	$pdf->Text($x4,$y, ' : ');
	$pdf->Text($x5,$y, convert_money("Rp",$lrow["total_nilai_pinjaman"],0));
	$y+=10;
	
	$pdf->Text($x,$y, ' Alamat KTP ');
	$pdf->Text($x1,$y, ' : ');
	$pdf->Text($x2,$y, $lrow["alamat_ktp"]);	
	$y+=10;
	
	$pdf->Text($x,$y, ' NIK ');
	$pdf->Text($x1,$y, ' : ');
	$pdf->Text($x2,$y, $lrow["no_id"]);
	$pdf->Text($x3,$y, ' TDP ');
	$pdf->Text($x4,$y, ' : ');
	$pdf->Text($x5,$y, convert_money("Rp",$lrow["nilai_dp"],0));	
	$y+=10;
	
	$pdf->Text($x,$y, ' Merk / Type ');
	$pdf->Text($x1,$y, ' : ');
	$pdf->Text($x2,$y, $lrow["nm_merek"].' / '.$lrow["nm_tipe"]);
	$pdf->Text($x3,$y, ' Angsuran ');
	$pdf->Text($x4,$y, ' : ');
	$pdf->Text($x5,$y, convert_money("Rp",$lrow["angsuran_bulan"],0));	
	$y+=10;
	
	$pdf->Text($x,$y, ' Tahun ');
	$pdf->Text($x1,$y, ' : ');
	$pdf->Text($x2,$y, $lrow["tahun"]);
	$pdf->Text($x3,$y, ' Tenor ');
	$pdf->Text($x4,$y, ' : ');
	$pdf->Text($x5,$y, $lrow["lama_pinjaman"]);	
	$y+=10;
	
	$pdf->Text($x,$y, ' Dealer ');
	$pdf->Text($x1,$y, ' : ');
	$pdf->Text($x2,$y, $lrow["nm_partner"]);	
	$pdf->Text($x3,$y, ' CMO ');
	$pdf->Text($x4,$y, ' : ');
	$nm_karyawan_kredit=get_rec("tblkaryawan","nm_depan","npk='".$lrow["fk_karyawan_cmo"]."'");
	$pdf->Text($x5,$y, $nm_karyawan_kredit);				
	$y+=20;
	
	$pdf->Text($x,$y, ' CHARACTER ');
	$pdf->Text($x1,$y, ' : ');
	$pdf->Text($x2,$y, $lrow["karakter"]);	
	$y+=15;
	
	$capacity=$lrow["capacity"];

	if(strlen($capacity) > 120){
		$capacity = wordwrap($capacity,90,"<split>");
		$tmp_capacity = split("<split>",$capacity);
	}else $tmp_capacity[0] = $capacity;	
	$pdf->Text($x,$y, ' CAPACITY ');
	$pdf->SetFont("Arial", "B", 8);
	$pdf->Text($x1,$y, ' : ');
	$pdf->Text($x2,$y, $tmp_capacity[0]);	
	$y+=10;
	
	$pdf->Text($x-10,$y, ' (Sesuai Analisa CA)');
	$pdf->Text($x2,$y, $tmp_capacity[1]);
	$y+=10;	
	$pdf->Text($x2,$y, $tmp_capacity[2]);
	$y+=15;
	
	$pdf->SetFont("Arial", "", 9);
	$pdf->Text($x2,$y, ' Kondisi Keuangan / Penghasilan Bulanan : ');
	
	$x_1=$x2+30;
	$x_2=$x_1+180;
	$x_3=$x_2+10;
	$lrow["penghasilan"]=$lrow["penghasilan"]/12;
	$lrow["penghasilan_pasangan"]=$lrow["penghasilan_pasangan"]/12;
	$lrow["penghasilan_penjamin"]=$lrow["penghasilan_penjamin"]/12;
	$y+=10;
	$pdf->Text($x_1,$y, ' Penghasilan Pemohon ');
	$pdf->Text($x_2,$y, ' : ');
	$pdf->Text($x_3,$y, ' Rp '.convert_money("",$lrow["penghasilan"]+$lrow["penghasilan_lain"]).'');
	$y+=10;
	$pdf->Text($x_1,$y, ' Penghasilan Pasangan ');
	$pdf->Text($x_2,$y, ' : ');
	$pdf->Text($x_3,$y, ' Rp '.convert_money("",$lrow["penghasilan_pasangan"]).'');
	$y+=10;
	$pdf->Text($x_1,$y, ' Penghasilan Penjamin ');
	$pdf->Text($x_2,$y, ' : ');
	$pdf->Text($x_3,$y, ' Rp '.convert_money("",$lrow["penghasilan_penjamin"]).'');
	$y+=10;
	
	$lrow_slik=pg_fetch_array(pg_query("select sum(angs)as total_angsuran_slik from data_gadai.tbltaksir_umum_detail_slik where fk_fatg='".$lrow["no_fatg"]."'"));	
	
	$pdf->Text($x_1,$y, ' Total Angsuran SLIK  ');
	$pdf->Text($x_2,$y, ' : ');
	$pdf->Text($x_3,$y, ' Rp '.convert_money("",$lrow_slik["total_angsuran_slik"]).'');
	$y+=10;
	
	$sisa_penghasilan=$lrow["penghasilan"]+$lrow["penghasilan_pasangan"]+$lrow["penghasilan_lain"]+$lrow["penghasilan_penjamin"]-$lrow_slik["total_angsuran_slik"];
	$pdf->Text($x_1,$y, ' Sisa Penghasilan  ');	
	$pdf->Text($x_2,$y, ' : ');
	$pdf->Text($x_3,$y, ' Rp '.convert_money("",$sisa_penghasilan).'');
	$y+=10;
	$pdf->Text($x_1,$y, ' Angs. '.($lrow["nm_jenis_barang"]).' Yg Diajukan  ');
	$pdf->Text($x_2,$y, ' : ');	
	$pdf->Text($x_3,$y, ' Rp '.convert_money("",$lrow["angsuran_bulan"]).'');
	
	$y+=10;
	$sisa_penghasilan1=$sisa_penghasilan-$lrow["angsuran_bulan"];
	
	$pdf->Text($x2,$y, ' Sisa Penghasilan Dikurangi Angs ');
	$pdf->Text($x_2,$y, ' : ');	
	$pdf->Text($x_3,$y, ' Rp '.convert_money("",$sisa_penghasilan1).'');
	
	$y+=20;
	$capital=$lrow["capital"];
	if(strlen($capital) > 95){
		$capital = wordwrap($capital,95,"<split>");
		$tmp_capital = split("<split>",$capital);
	}else $tmp_capital[0] = $capital;	
	$pdf->SetFont("Arial", "B", 9);
	$pdf->Text($x,$y, ' CAPITAL ');
	$pdf->Text($x1,$y, ' : ');
	$pdf->Text($x2,$y, $tmp_capital[0]);
	$y+=10;
	$pdf->Text($x2,$y, $tmp_capital[1]);
	//$y+=10;
	//$pdf->Text($x2,$y, '  ');
	$y+=10;
	$kondisi=$lrow["kondisi"];
	if(strlen($kondisi) > 120){
		$kondisi = wordwrap($kondisi,90,"<split>");
		$tmp_kondisi = split("<split>",$kondisi);
	}else $tmp_kondisi[0] = $kondisi;	
	
	$pdf->Text($x,$y, ' CONDITION  ');
	$pdf->SetFont("Arial", "B", 8);
	$pdf->Text($x1,$y, ' : ');
	$pdf->Text($x2,$y, $tmp_kondisi[0]);
	$y+=10;
	$pdf->Text($x-5,$y, '(pada saat survey) ');
	$pdf->Text($x2,$y, $tmp_kondisi[1]);
	$pdf->Text($x2,$y,' ');
	$y+=10;
	$pdf->Text($x2,$y, $tmp_kondisi[2]);
	$pdf->Text($x2,$y,' ');
	
	$pdf->SetFont("Arial", "B", 9);
	$y+=20;
	$pdf->Text($x,$y, ' TUJUAN ');
	$pdf->Text($x1,$y, ' : ');
	$pdf->Text($x2,$y, $lrow["nm_tujuan_transaksi"]);

	$y+=10;
	$pdf->Text($x,$y, ' SUMBER DANA ');
	$pdf->Text($x1,$y, ' : ');
	$pdf->Text($x2,$y, $lrow["sumber_dana"]);

	$y+=10;
	$pdf->Text($x,$y, ' COLLATERAL ');
	$pdf->Text($x1,$y, ' : ');
	$pdf->Text($x2,$y, ' BPKB ');
	//$pdf->Text($x5,$y, ' Tahun ');
	$y+=10;
	$pdf->Text($x2,$y, '  ');
	
	$y+=10;
	$persen_dp=(($lrow["nilai_dp"]/$lrow["total_nilai_pinjaman"]*100));
	if($sisa_penghasilan1>0){
		$persen_sisa_penghasilan=($lrow["angsuran_bulan"]/$sisa_penghasilan)*100;
	}else{
		$persen_sisa_penghasilan=0;
	}
	$pdf->Text($x,$y, ' OTHER ');
	$pdf->Text($x1,$y, ' : ');
	$pdf->Text($x2,$y, ' DP : '.convert_money("",$persen_dp,2).' %');
	$pdf->Text($x2+100,$y, ' DSR : '.convert_money("",$persen_sisa_penghasilan,2).' %');
	$y+=10;
	$pdf->Text($x2,$y, ' Cek Internal : '.$lrow["cek_internal"]);	
	$y+=10;
	$pdf->Text($x2,$y, ' Cek Slik : '.$lrow["cek_slik"]);	
	$y+=10;
	$pdf->Text($x2,$y, ' Berikut hasil cek SLIK pemohon dan istri ybs :	 ');			
	$y+=10;	
             				
	$header = array('No', 'LJK/Bank', 'Periode', 'Plafon Awal','Angs','Kualitas','Baki Debet','Jaminan');
	
	
	$query_slik="select * from data_gadai.tbltaksir_umum_detail_slik where fk_fatg='".$lrow["no_fatg"]."' ";
	//echo $query_slik;
	$lrs_slik=pg_query($query_slik);
	$i=1;
	//for($i=1;$i<=4;$i++){
	while($lrow_slik=pg_fetch_array($lrs_slik)){
		$data[$i]['No']=$i;
		$data[$i]['LJK/Bank']=$lrow_slik["ljk_bank"];
		$data[$i]['Periode']=date("m/Y",strtotime($lrow_slik["tgl_awal"]))."-".date("m/Y",strtotime($lrow_slik["tgl_akhir"]));
		$data[$i]['Plafon Awal']=convert_money("",$lrow_slik["plafon_awal"]);
		$data[$i]['Angs']=convert_money("",$lrow_slik["angs"]);
		$data[$i]['Kualitas']=$lrow_slik["kualitas"];
		$data[$i]['Baki Debet']=convert_money("",$lrow_slik["baki_debet"]);
		$data[$i]['Jaminan']=$lrow_slik["jaminan"];
		$i++;
	}
	
	$pdf->SetFont("Arial", "", 8);
	
	$option['w']['No']='20';
	$option['w']['LJK/Bank']='160';			
	$option['w']['Periode']='70';
	$option['w']['Jaminan']='100';
	$option['w']['Kualitas']='40';		
	$option['w']['Plafon Awal']='55';		
	$option['w']['Angs']='55';		
	$option['w']['Baki Debet']='55';		
	$option['w']['Jaminan']='75';		

	
	$option['align']['Plafon Awal']='R';		
	$option['align']['Angs']='R';	
	$option['align']['Baki Debet']='R';		
	$option['align']['Kualitas']='C';		
	
	$option['header_align']['No']='C';
	$option['header_align']['LJK/Bank']='C';			
	$option['header_align']['Periode']='C';
	$option['header_align']['Jaminan']='C';
	$option['header_align']['Kualitas']='C';	
	$option['header_align']['Plafon Awal']='C';		
	$option['header_align']['Angs']='C';	
	$option['header_align']['Baki Debet']='C';		
	$option['header_align']['Jaminan']='C';		
	
			
	$pdf->BasicTable($header,$data,5,$y,$option);	
	$pdf->SetFont("Arial", "B", 9);
	
	$kesimpulan=$lrow["kesimpulan"];
	if(strlen($kesimpulan) > 95){
		$kesimpulan = wordwrap($kesimpulan,95,"<split>");
		$tmp_kesimpulan = split("<split>",$kesimpulan);
	}else $tmp_kesimpulan[0] = $kesimpulan;			
		
	$y=$pdf->y;	
	$y+=10;
	$pdf->Text($x,$y, ' KESIMPULAN ');
	$pdf->Text($x1,$y, ' : ');
	$pdf->Text($x2,$y, $tmp_kesimpulan[0]);
	$y+=10;
	
	$alasan=$lrow["alasan"];	
	if(strlen($alasan) > 100){
		$alasan = wordwrap($alasan,100,"<split>");
		$tmp_alasan = split("<split>",$alasan);
	}else $tmp_alasan[0] = $alasan;		
	$pdf->Text($x,$y, ' ALASAN ');
	$pdf->SetFont("Arial", "B", 8);

	$pdf->Text($x1,$y, ' : ');
	$pdf->Text($x2,$y, $tmp_alasan[0]);
	$y+=10;
	$pdf->Text($x2,$y,  $tmp_alasan[1]);
	$pdf->SetFont("Arial", "B", 9);

	
	$y+=10;
	$keterangan=$lrow["keterangan"];
	if(strlen($keterangan) > 95){
		$keterangan = wordwrap($keterangan,95,"<split>");
		$tmp_keterangan = split("<split>",$keterangan);
	}else $tmp_keterangan[0] = $keterangan;	
	$pdf->Text($x,$y, ' CATATAN ');
	$pdf->Text($x1,$y, ' : ');
	$pdf->Text($x2,$y, $tmp_keterangan[0]);

	$y+=10;
	$pdf->Text($x,$y, ' KOMITE ');
	$pdf->Text($x1,$y, '  ');
	$pdf->Text($x2,$y, $tmp_keterangan[1]);
	
	$y+=20;
	$pdf->Text($x,$y, $lrow["nm_cabang"].", ".$tgl_pengajuan);
	$y+=5;
		
	//$pdf->Line($x,$y,100,$y);
	$pdf->SetFont("Arial", "", 9);
	$y+=15;
	$pdf->Text($x+50,$y, ' Dibuat Oleh '); $pdf->Text($x+400,$y, ' Komite Pembiayaan ');
	
	$y+=50;
	$jabatan2='Supervisor Marketing';
	$nm_karyawan2=get_karyawan_by_jabatan($jabatan2,$fk_cabang);
	if(!$nm_karyawan2)$nm_karyawan2=$nm_kacab;
	
	
	$pdf->setY($y);
	$pdf->Cell(50,15,'('.$nm_karyawan_ca.')',0,'','C','','',$x);
	$pdf->Cell(50,15,'('.$nm_karyawan2.')',0,'','C','','',$x+90);
	$pdf->Cell(50,15,'('.get_karyawan_by_jabatan('Administration Head',$fk_cabang).')',0,'','C','','',$x+170);
	$pdf->Cell(50,15,'('.$nm_kacab.')',0,'','C','','',$x+250);
	
	$y+=13;		
	$pdf->setY($y);
	$pdf->Cell(50,15,'Credit Analyst',0,'','C','','',$x);
	$pdf->Cell(50,15,'SPV Marketing',0,'','C','','',$x+90);
	$pdf->Cell(50,15,'ADH',0,'','C','','',$x+170);
	$pdf->Cell(50,15,($lrow["jenis_cabang"]=='Cabang'?"Kepala Cabang":"Kepala Pos"),0,'','C','','',$x+250);
	

	$pdf->Output();
?>