<?php
include_once("report.php");

function filter_request(){
	global $showBln,$tgl,$showCab,$showTgl;
	$nm_periode="";
	$showBln='t';
	//$showTgl='t';
}

function excel_content(){
	global $bulan,$tahun,$fk_customer,$tgl,$fk_cabang;
	//$fk_cabang='101';
	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cabang = '".$fk_cabang."' ";
	}
	
	if ($lwhere!="") $lwhere=" and ".$lwhere;
	
	$hari=date('t',strtotime($bulan.'/01/'.$tahun));	
	if($tgl){
		//$hari=date('d',strtotime($tgl));	
		//echo $hari;
	}
	
	$fom=$bulan.'/01/'.$tahun;
	$eom=$bulan.'/'.$hari.'/'.$tahun;
	
	$last_month=get_last_month($fom,1);
	//echo $last_month;
	$l_month=date('n',strtotime($last_month));
	$l_year=date('Y',strtotime($last_month));	
	
	
	if($tgl){
		//$fom=$tgl;
		//$eom=$tgl;
	}

	$array = array(
		1 => array('no' => '1','name' => 'Arus Kas Bersih dari Kegiatan Operasi'
		,'data'=>'sub1'),
		2 => array('no' => '2','name' => 'Arus Kas Masuk dari Kegiatan Operasi'
		,'data'=>'sub2_in'),
		3 => array('no' => '3','name' => 'Arus Kas Masuk dari Pembiayaan Jual Beli Berdasarkan Prinsip Syariah'
		,'data'=>'sub3_in'),
		4 => array('no' => '4','name' => 'Arus Kas Masuk dari Kegiatan Pembiayaan Murabahah'
		,'data'=>'detail_in','coa'=>array('1141000','1142000','1143000','1145000','1150000','1160000','2607000','2608000','2609000','2510000','2630000','27000','41000','42000')),
		5 => array('no' => '5','name' => 'Arus Kas Masuk dari Kegiatan Pembiayaan Salam'
		,'data'=>'detail_in'),
		6 => array('no' => '6','name' => 'Arus Kas Masuk dari Kegiatan Pembiayaan Istishna'
		,'data'=>'detail_in'),
		7 => array('no' => '7','name' => 'Arus Kas Masuk dari Akad Jual Beli Lainnya Berdasarkan Prinsip Syariah'
		,'data'=>'detail_in'),		
		8 => array('no' => '8','name' => 'Arus Kas Masuk dari Pembiayaan Investasi Berdasarkan Prinsip Syariah'
		,'data'=>'sub3_in'),
		9 => array('no' => '9','name' => 'Arus Kas Masuk dari Kegiatan Pembiayaan Mudharabah'
		,'data'=>'detail_in'),
		10 => array('no' => '10','name' => 'Arus Kas Masuk dari Kegiatan Pembiayaan Musyarakah'
		,'data'=>'detail_in'),
		11 => array('no' => '11','name' => 'Arus Kas Masuk dari Kegiatan Pembiayaan Mudharabah musytarakah'
		,'data'=>'detail_in'),
		12 => array('no' => '12','name' => 'Arus Kas Masuk dari Kegiatan Pembiayaan Musyarakah Muntanaqisah'
		,'data'=>'detail_in'),		
		13 => array('no' => '13','name' => 'Arus Kas Masuk dari Akad Investasi Lainnya Berdasarkan Prinsip Syariah'
		,'data'=>'detail_in'),		
		14 => array('no' => '14','name' => 'Arus Kas Masuk dari Kegiatan Pembiayaan Jasa Berdasarkan Prinsip Syariah'
		,'data'=>'sub3_in'),		
		15 => array('no' => '15','name' => 'Arus Kas Masuk dari Kegiatan Pembiayaan Ijarah'
		,'data'=>'detail_in'),
		16 => array('no' => '16','name' => 'Arus Kas Masuk dari Kegiatan Pembiayaan IMBT'
		,'data'=>'detail_in'),
		21 => array('no' => '21','name' => 'Arus Kas Masuk dari Kegiatan Pembiayaan Qardh'
		,'data'=>'detail_in'),		
		22 => array('no' => '22','name' => 'Arus Kas Masuk dari Akad Pembiayaan Jasa Lainnya '
		,'data'=>'detail_in'),		
		
		23 => array('no' => '23','name' => 'Arus Kas Masuk dari Kegiatan Pembiayaan Penerusan dengan akad wakalah bil ujrah'
		,'data'=>'sub3_in'),	
		24 => array('no' => '24','name' => 'Arus Kas Masuk dari Kegiatan Pembiayaan Penerusan (Channeling)'
		,'data'=>'sub3_in'),		
		25 => array('no' => '25','name' => 'Arus Kas Masuk dari Kegiatan Pembiayaan Bersama (Joint Financing)'
		,'data'=>'sub3_in'),			
		26 => array('no' => '26','name' => 'Arus Kas Masuk dari Surat Berharga yang Diperjualbelikan'
		,'data'=>'sub3_in'),			
		27 => array('no' => '27','name' => 'Arus Kas Masuk dari Pendapatan Kegiatan operasi lainnya'
		,'data'=>'sub3_in','coa'=>array('11800','2606000','2610000','43000')),		
			
		28 => array('no' => '28','name' => 'Arus Kas Keluar untuk Kegiatan Operasi'
		,'data'=>'sub2_out'),
		29 => array('no' => '29','name' => 'Arus Kas Keluar untuk Kegiatan Pembiayaan Jual Beli Berdasarkan Prinsip Syariah'
		,'data'=>'sub3_out'),			
		30 => array('no' => '30','name' => 'Arus Kas Keluar untuk Kegiatan Pembiayaan Murabahah'
		,'data'=>'detail_out','coa'=>array('21000','24000')),
		31 => array('no' => '31','name' => 'Arus Kas Keluar untuk Kegiatan Pembiayaan Salam'
		,'data'=>'detail_out'),
		32 => array('no' => '32','name' => 'Arus Kas Keluar untuk Kegiatan Pembiayaan Istishna'
		,'data'=>'detail_out'),		
		33 => array('no' => '33','name' => 'Arus Kas Keluar untuk Kegiatan Pembiayaan Penerusan (Channeling)'
		,'data'=>'sub3_out'),
		34 => array('no' => '34','name' => 'Arus Kas Keluar untuk Kegiatan Pembiayaan Bersama (Joint Financing)'
		,'data'=>'sub3_out'),
		35 => array('no' => '35','name' => 'Arus Kas Keluar untuk Akad Jual Beli Lainnya'
		,'data'=>'sub3_out'),		
		36 => array('no' => '36','name' => 'Arus Kas Keluar untuk Kegiatan Pembiayaan Investasi Berdasarkan Prinsip Syariah'
		,'data'=>'sub3_out'),
		37 => array('no' => '37','name' => 'Arus Kas Keluar untuk Kegiatan Pembiayaan Mudharabah'
		,'data'=>'detail_out'),
		38 => array('no' => '38','name' => 'Arus Kas Keluar untuk Kegiatan Pembiayaan Musyarakah'
		,'data'=>'detail_out'),
		39 => array('no' => '39','name' => 'Arus Kas Keluar untuk Kegiatan Pembiayaan Mudharabah musytarakah'
		,'data'=>'detail_out'),		
		40 => array('no' => '40','name' => 'Arus Kas Keluar untuk Kegiatan Pembiayaan Musyarakah Muntanaqisah'
		,'data'=>'detail_out'),
		41 => array('no' => '41','name' => 'Arus Kas Keluar untuk Akad Investasi Lainnya'
		,'data'=>'detail_out'),
		42 => array('no' => '42','name' => 'Arus Kas Keluar untuk Kegiatan Pembiayaan Jasa Berdasarkan Prinsip Syariah'
		,'data'=>'sub3_out'),
		43 => array('no' => '43','name' => 'Arus Kas Keluar untuk Kegiatan Pembiayaan Ijarah'
		,'data'=>'detail_out'),
		44 => array('no' => '44','name' => 'Arus Kas Keluar untuk Kegiatan Pembiayaan IMBT'
		,'data'=>'detail_out'),
		49 => array('no' => '49','name' => 'Arus Kas Keluar untuk Kegiatan Pembiayaan Qardh'
		,'data'=>'detail_out'),		

		50 => array('no' => '50','name' => 'Arus Kas Keluar untuk Kegiatan Akad Pembiayaan Jasa Lainnya'
		,'data'=>'detail_out'),		
		51 => array('no' => '51','name' => 'Arus Kas Keluar untuk Pembayaran Beban Umum dan Administrasi'
		,'data'=>'sub3_out','coa'=>array('2520000','2620011','2620022','','2601000','2602000','2603000','2900000','51000','52100','53000','54000','55000','56000','57000','58000','59000','5910033')),	
		52 => array('no' => '52','name' => 'Arus Kas Keluar untuk Pembayaran Pajak Penghasilan'
		,'data'=>'sub3_out','coa'=>array('11900','23000')),		
		//'23000'
		53 => array('no' => '53','name' => 'Arus Kas Keluar untuk Perolehan Surat Berharga yang Ditujukan untuk Diperjualbelikan'
		,'data'=>'sub3_out'),		
		54 => array('no' => '54','name' => 'Arus Kas Keluar untuk Pembayaran Kegiatan Operasi Lainnya'
		,'data'=>'sub3_out','coa'=>array('11700','','','1270000','1290000','2602011','2602022','2602033','51100','59800')),			
		
		55 => array('no' => '55','name' => 'Arus Kas Bersih dari Kegiatan Investasi'
		,'data'=>'sub1'),
		56=> array('no' => '56','name' => 'Arus Kas Masuk dari Kegiatan Investasi'
		,'data'=>'sub2_in'),
		57 => array('no' => '57','name' => 'Arus Kas Masuk Atas Pelepasan Anak Perusahaan'
		,'data'=>'sub3_in'),			
		58 => array('no' => '58','name' => 'Arus Kas Masuk dari Penjualan Tanah, Bangunan, dan Peralatan'
		,'data'=>'sub3_in','coa'=>array('1200000','1210000','1220000','1230000','1240000','1250000','1260000')),			
		59 => array('no' => '59','name' => 'Arus Kas Masuk dari Penjualan Surat Berharga yang Tidak Dimaksudkan untuk Diperjualbelikan'
		,'data'=>'sub3_in'),			
		60 => array('no' => '60','name' => 'Arus Kas Masuk dari Deviden'
		,'data'=>'sub3_in'),	
		61 => array('no' => '61','name' => 'Arus Kas Masuk Bagi Hasil dari Kegiatan Investasi'
		,'data'=>'sub3_in'),			
		62 => array('no' => '62','name' => 'Arus Kas Masuk dari Kegiatan Investasi Lainnya'
		,'data'=>'sub3_in'),			
		63=> array('no' => '63','name' => 'Arus Kas Keluar untuk Kegiatan Investasi'
		,'data'=>'sub2_out'),
		64 => array('no' => '64','name' => 'Arus Kas Keluar untuk Perolehan Atas Anak Perusahaan'
		,'data'=>'sub3_out'),			
		65 => array('no' => '65','name' => 'Arus Kas Keluar untuk Pembelian Tanah, Bangunan, dan Peralatan'
		,'data'=>'sub3_out','coa'=>array('1200000','1210000','1220000','1230000')),			
		66 => array('no' => '66','name' => 'Arus Kas Keluar untuk Perolehan Surat Berharga yang Tidak Dimaksudkan Diperjualbelikan'
		,'data'=>'sub3_out'),			
		67 => array('no' => '67','name' => 'Arus Kas Keluar untuk Kegiatan Investasi Lainnya'
		,'data'=>'sub3_out'),			
		
		68 => array('no' => '68','name' => 'Arus Kas Bersih dari Kegiatan Pendanaan'
		,'data'=>'sub1'),		
		69=> array('no' => '69','name' => 'Arus Kas Masuk dari Kegiatan Pendanaan'
		,'data'=>'sub2_in'),
		70 => array('no' => '70','name' => 'Arus Kas Masuk dari Pendanaan Bank Syariah'
		,'data'=>'sub3_in'),
		71 => array('no' => '71','name' => 'Arus Kas Masuk dari Pendanaan Mudharabah dari Bank'
		,'data'=>'detail_in','coa'=>array('2210000')),
		72 => array('no' => '72', 'name' => 'Arus Kas Masuk dari Pendanaan Mudharabah Musytarakah dari Bank'
		,'data'=>'detail_in'),
		73 => array('no' => '73', 'name' => 'Arus Kas Masuk dari Pendanaan Musyarakah dari Bank'
		,'data'=>'detail_in'),
		74 => array('no' => '74', 'name' => 'Arus Kas Masuk dari Pendanaan dengan Akad Lainnya Berdasarkan Prinsip Syariah dari Bank'
		,'data'=>'detail_in'),
		75 => array('no' => '75','name' => 'Arus Kas Masuk dari Pendanaan Non Bank'
		,'data'=>'sub3_in'),
		76 => array('no' => '76', 'name' => 'Arus Kas Masuk dari Pendanaan Mudharabah dari Lembaga Bukan Bank'
		,'data'=>'detail_in'),
		77 => array('no' => '77', 'name' => 'Arus Kas Masuk dari Pendanaan Mudharabah Musytarakah dari Lembaga Bukan Bank'
		,'data'=>'detail_in'),
		78 => array('no' => '78', 'name' => 'Arus Kas Masuk dari Pendanaan Musyarakah dari Lembaga Bukan Bank'
		,'data'=>'detail_in'),
		79 => array('no' => '79', 'name' => 'Arus Kas Masuk dari Pendanaan dengan Akad Lainnya Berdasarkan Prinsip Syariah dari Lembaga Bukan Bank'
		,'data'=>'detail_in'),
		80 => array('no' => '80','name' => 'Arus Kas Masuk dari Pinjaman (Qardh) Subordinasi'
		,'data'=>'sub3_in'),
		81 => array('no' => '81','name' => 'Arus Kas Masuk dari Penerbitan Surat Berharga Syariah'
		,'data'=>'sub3_in'),
		82 => array('no' => '82','name' => 'Arus Kas Masuk dari Pendanaan Sekuritisasi Berdasarkan Prinsip Syariah'
		,'data'=>'sub3_in'),
		83 => array('no' => '83','name' => 'Arus Kas Masuk dari Pendanaan Lainnya Berdasarkan Prinsip Syariah'
		,'data'=>'sub3_in'),
		84 => array('no' => '84','name' => 'Arus Kas Masuk dari Penerbitan Modal Saham'
		,'data'=>'sub3_in'),
		85 => array('no' => '85','name' => 'Arus Kas Masuk dari Setoran Modal Kerja'
		,'data'=>'sub3_in','coa'=>array('2604000','2605000','3100000','3200000','3300000','3400000','3500000','3600000')),
		86 => array('no' => '86','name' => 'Arus Kas Keluar untuk Kegiatan Pendanaan'
		,'data'=>'sub2_out'),
		87 => array('no' => '87','name' => 'Arus Kas Keluar untuk Pembayaran Pendanaan dari Bank'
		,'data'=>'sub3_out'),
		88 => array('no' => '88','name' => 'Arus Kas Keluar untuk Pembayaran Pendanaan Mudharabah dari Bank'
		,'data'=>'detail_out','coa'=>array('2210000')),
		89 => array('no' => '89','name' => 'Arus Kas Keluar untuk Pembayaran Pendanaan Mudharabah Musytarakah dari Bank'
		,'data'=>'detail_out'),
		90 => array('no' => '90','name' => 'Arus Kas Keluar untuk Pembayaran Pendanaan Musyarakah dari Bank'
		,'data'=>'detail_out'),
		91 => array('no' => '91','name' => 'Arus Kas Keluar untuk Pembayaran Pendanaan dengan Akad Lainnya Berdasarkan Prinsip Syariah dari Bank'
		,'data'=>'detail_out'),
		92 => array('no' => '92','name' => 'Arus Kas Keluar untuk Pembayaran Pendanaan Non Bank'
		,'data'=>'sub3_out'),
		93 => array('no' => '93','name' => 'Arus Kas Keluar untuk Pembayaran Pendanaan Mudharabah dari Non Bank'
		,'data'=>'detail_out'),
		94 => array('no' => '94','name' => 'Arus Kas Keluar untuk Pembayaran Pendanaan Mudharabah Musytarakah dari Non Bank'
		,'data'=>'detail_out'),
		95 => array('no' => '95','name' => 'Arus Kas Keluar untuk Pembayaran Pendanaan Musyarakah dari Non Bank'
		,'data'=>'detail_out'),
		96 => array('no' => '96','name' => 'Arus Kas Keluar untuk Pembayaran Pendanaan dengan Akad Lainnya Berdasarkan Prinsip Syariah dari Non Bank'
		,'data'=>'detail_out'),
		97 => array('no' => '97','name' => 'Arus Kas Keluar untuk Pinjaman (Qardh) Subordinasi'
		,'data'=>'sub3_out'),
		98 => array('no' => '98','name' => 'Arus Kas Keluar untuk Penerbitan Surat Berharga Syariah'
		,'data'=>'sub3_out'),
		99 => array('no' => '99','name' => 'Arus Kas Keluar untuk Pendanaan Sekuritisasi Berdasarkan Prinsip Syariah'
		,'data'=>'sub3_out'),
		100 => array('no' => '100','name' => 'Arus Kas Keluar untuk Pendanaan Lainnya Berdasarkan Prinsip Syariah'
		,'data'=>'sub3_out'),
		101 => array('no' => '101','name' => 'Arus Kas Keluar untuk Penarikan Kembali Modal Perusahaan (Treasury Stock)'
		,'data'=>'sub3_out'),
		102 => array('no' => '102','name' => 'Arus Kas Keluar untuk Pembayaran Dividen'
		,'data'=>'sub3_out'),
		103 => array('no' => '103','name' => 'Surplus (Defisit) pada Kas dan Setara Kas Akibat Perubahan Kurs'
		,'data'=>''),
		104 => array('no' => '104','name' => 'Kenaikan (Penurunan) Bersih Kas dan Setara Kas'
		,'data'=>''),
		105 => array('no' => '105','name' => 'Kas dan Setara Kas Pada Awal Periode'
		,'data'=>''),
		106 => array('no' => '106','name' => 'Kas dan Setara Kas Pada Akhir Periode'
		,'data'=>''),	
	);
	//
	//2510000
	$coa_in_out=array("2210000",'1200000','1210000','1220000','1230000');
	
	$lwhere_head="where fk_head_account in('11000','11100','11300')";
	$lwhere_head2="and fk_head_account not in('11000','11100','11300','31200')";
		
for($no=1;$no<=2;$no++){		
	if($no==1){
		$kolom='MTD';
		$month=$l_month;
		$year=$l_year;
		$ptgl=" tr_date>='#".$fom."#' and tr_date<='#".$eom."#' ";
	}
	else {
		//echo $no;
		$kolom='YTD';
		$month=12;
		$year=$tahun-1;
		$tgl_live=get_rec("tblsetting","tgl_live");
		$tahun_live=date("Y",strtotime($tgl_live));
		$bulan_live=date("m",strtotime($tgl_live));					
		
		if($bulan_live!=1){
			$month=$bulan_live-1;
			$year=$tahun_live;
		}
		
		
		$ptgl=" tr_date>='#".$l_year."-01-01#' and tr_date<='#".$eom."#' ";
	}
	
	$query_saldo_awal="
	select sum(balance_cash+balance_bank+balance_memorial+balance_gl_auto) as saldo_awal,
	transaction_type as tr_type from data_accounting.tblsaldo_coa
	inner join (
		select coa,description,transaction_type,fk_head_account from tblcoa
		".$lwhere_head." ".$lwhere."
	)as tblcoa on fk_coa =coa
	";	
	

	$row_saldo_awal=pg_fetch_array(pg_query($query_saldo_awal." and tr_month=".$month." and tr_year=".$year." group by transaction_type"));		
	//showquery($query_saldo_awal." and tr_month=".$month." and tr_year=".$year." group by transaction_type");			
	$saldo_awal=$row_saldo_awal["saldo_awal"];
	$total[$kolom]['105']+=$saldo_awal;
	
	$query_ledger="
select 
sum(case when fk_coa_d is not null then total end) as total_debit, 
sum(case when fk_coa_c is not null then total end) as total_credit,
coa,tr_type,fk_head_account
from data_accounting.tblgl_auto
inner join (
	select coa,description,transaction_type as tr_type,fk_head_account from tblcoa
	where true
	".$lwhere." ".$lwhere_head2."
)as tblcoa on case when fk_coa_d is not null then fk_coa_d else fk_coa_c end =coa
where ".$ptgl."				
and fk_owner||type_owner||tr_date in (
	select fk_owner||type_owner||tr_date from data_accounting.tblgl_auto 
	inner join (
		select coa,description,transaction_type as tr_type,fk_head_account from tblcoa
		".$lwhere_head." --and fk_cabang='000'
	)as tblcoa on case when fk_coa_d is not null then fk_coa_d else fk_coa_c end =coa
	where ".$ptgl."
) 
and fk_owner||' '||coalesce(reference_transaksi, '') not in(
	select fk_owner||' '||coalesce(reference_transaksi, '') from data_accounting.tblgl_auto 
	where type_owner like '%BATAL%' and ".$ptgl."
)
--and coa like '%1183000%'
group by coa,tr_type,fk_head_account
order by fk_head_account,coa
	";	
	$saldo_total=array();
	$saldo_total1=array();
	$lrs_leger=pg_query($query_ledger);
	//showquery($query_ledger);
	while ($lrow_ledger=pg_fetch_array($lrs_leger)){
		$lrow_ledger['coa']=substr($lrow_ledger['coa'],4);
		$saldo_total[$lrow_ledger['coa']]['total_debit']+=($lrow_ledger["total_debit"]);
		$saldo_total[$lrow_ledger['coa']]['total_credit']+=($lrow_ledger["total_credit"]);	
			
		$saldo_total1[$lrow_ledger['fk_head_account']]['total_debit']+=($lrow_ledger["total_debit"]);	
		$saldo_total1[$lrow_ledger['fk_head_account']]['total_credit']+=($lrow_ledger["total_credit"]);	
		
	}

	//print_r($saldo_total);
	$flag=array();
	$nilai=array();
	//$fom=$tgl;
	$a=0;
	foreach($array as $index=>$data){
		if($data['coa']){
			//print_r($data);
			//echo $data['data'].'<br>';
			$jenis="";
			if(strstr($data['data'],'in'))$jenis='in';
			else $jenis='out';								
			
			foreach($data['coa'] as $coa){
				$fk_head_account='';
				if(strlen($coa)=='5')$fk_head_account=$coa;
				//echo substr($coa,0,1).'<br>';	
				//echo $coa.'<br>';
				$kd_coa= substr($coa,0,1);					
				
				if($fk_head_account){
					$flag[$fk_head_account]='t';
					//echo $fk_head_account.'<br>';
					$nilai["total_debit"]=$saldo_total1[$fk_head_account]['total_debit'];
					$nilai["total_credit"]=$saldo_total1[$fk_head_account]['total_credit'];
				}else{
					$flag[$coa]='t';
					if($coa=='1142000'){
						//echo 'ppppp';
					}
					$nilai["total_debit"]=$saldo_total[$coa]['total_debit'];
					$nilai["total_credit"]=$saldo_total[$coa]['total_credit'];
				}
				$saldo=0;
				if ($jenis=='out') {
					$saldo=($nilai["total_debit"]-$nilai["total_credit"]);
					if(in_array($coa,$coa_in_out)){
						$saldo=($nilai["total_debit"]);								
					}
				} else {
					$saldo=($nilai["total_credit"]-$nilai["total_debit"]);
					if(in_array($coa,$coa_in_out)){
						$saldo=($nilai["total_credit"]);
						//echo $lrow_ledger["total_credit"].'sdf<br>';
					}
				}
										
				$code=$data['no'];
				$total[$kolom][$code]+=$saldo;;
			}
		}
		$a++;
	}
	//print_r($total);
	//print_r($flag);
	//showquery($query_ledger);
	$lrs_leger=pg_query($query_ledger);
	while ($lrow_ledger=pg_fetch_array($lrs_leger)){
		$lrow_ledger['coa']=substr($lrow_ledger['coa'],4);
		if($flag[$lrow_ledger['coa']]=='t' || $flag[$lrow_ledger['fk_head_account']]=='t'){
		
		}else{
			echo $lrow_ledger['coa'].'<br>'; 	
			//buat cek coa belum masuk array kategori
		}
	}
	
	
	$total[$kolom][3]=$total[$kolom][4]+$total[$kolom][5]+$total[$kolom][6]+$total[$kolom][7];
	$total[$kolom][8]=$total[$kolom][9]+$total[$kolom][10]+$total[$kolom][11]+$total[$kolom][12]+$total[$kolom][13];
	$total[$kolom][14]=$total[$kolom][15]+$total[$kolom][16]+$total[$kolom][21]+$total[$kolom][22];
	$total[$kolom][29]=$total[$kolom][30]+$total[$kolom][31]+$total[$kolom][32];
	$total[$kolom][36]=$total[$kolom][37]+$total[$kolom][38]+$total[$kolom][39]+$total[$kolom][40]+$total[$kolom][41];
	$total[$kolom][42]=$total[$kolom][43]+$total[$kolom][44]+$total[$kolom][49]+$total[$kolom][50];
	$total[$kolom][56]=$total[$kolom][57]+$total[$kolom][58]+$total[$kolom][59]+$total[$kolom][60]+$total[$kolom][61]+$total[$kolom][62];
	$total[$kolom][63]=$total[$kolom][64]+$total[$kolom][64]+$total[$kolom][65]+$total[$kolom][67];
	$total[$kolom][70]=$total[$kolom][71]+$total[$kolom][72]+$total[$kolom][73]+$total[$kolom][74];
	$total[$kolom][75]=$total[$kolom][76]+$total[$kolom][77]+$total[$kolom][78]+$total[$kolom][79];
	$total[$kolom][87]=$total[$kolom][88]+$total[$kolom][89]+$total[$kolom][90]+$total[$kolom][91];
	$total[$kolom][92]=$total[$kolom][93]+$total[$kolom][94]+$total[$kolom][95]+$total[$kolom][96];
	
	$total[$kolom][2]=$total[$kolom][3]+$total[$kolom][8]+$total[$kolom][14]+$total[$kolom][23]+$total[$kolom][24]+$total[$kolom][25]+$total[$kolom][26]+$total[$kolom][27];
	$total[$kolom][28]=$total[$kolom][29]+$total[$kolom][33]+$total[$kolom][34]+$total[$kolom][36]+$total[$kolom][42]+$total[$kolom][51]+$total[$kolom][52]+$total[$kolom][53]+$total[$kolom][54];
	$total[$kolom][69]=$total[$kolom][70]+$total[$kolom][75]+$total[$kolom][80]+$total[$kolom][81]+$total[$kolom][82]+$total[$kolom][83]+$total[$kolom][84]+$total[$kolom][85];
	$total[$kolom][86]=$total[$kolom][87]+$total[$kolom][92]+$total[$kolom][97]+$total[$kolom][98]+$total[$kolom][99]+$total[$kolom][100]+$total[$kolom][101]+$total[$kolom][102];
	
	$total[$kolom][1]=$total[$kolom][2]-$total[$kolom][28];
	$total[$kolom][55]=$total[$kolom][56]-$total[$kolom][63];
	$total[$kolom][68]=$total[$kolom][69]-$total[$kolom][86];
	$total[$kolom][104]=$total[$kolom][1]+$total[$kolom][55]+$total[$kolom][68]+$total[$kolom][103];
	$total[$kolom][106]=$total[$kolom][104]+$total[$kolom][105];
}
			

	//print_r($total);
	$bgcolor['sub1']='#FFCC00';
	$bgcolor['sub2_in']='#33CCFF';
	$bgcolor['sub2_out']='#FFCCCC';
	//$bgcolor['sub3']='#CCCCCC';	
	$tab['sub2_in']=$tab['sub2_out']='&nbsp;&nbsp;&nbsp;';
	$tab['sub3_in']=$tab['sub3_out']='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	$tab['detail_in']=$tab['detail_out']='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';	
	
	$fontcolor['detail_in']=$fontcolor['sub2_in']=$fontcolor['sub3_in']='style="color:blue"';
	$fontcolor['detail_out']=$fontcolor['sub2_out']=$fontcolor['sub3_out']='style="color:red"';
	
	echo 	
	'<table border="1" >				
	';
	echo '
		<tr bgcolor="#0099FF">
			<td>Metric</td>	
			<td>Code</td>									
		';
		echo '<td align="right">Total</td>';
		echo '<td align="right">YTD</td>';
		echo '
	</tr>';
	
	foreach($array as $index=>$temp){
		$code=$temp['no'];
		
		$nominal=$total['MTD'][$code];
		$nominal_ytd=$total['YTD'][$code];
		//if(strstr($temp['data'],'out'))$nominal=$total[$code]*-1;
		
		//if($nominal!=0){
		echo '
		<tr bgcolor="'.$bgcolor[$temp['data']].'" '.$fontcolor[$temp['data']].'>
			<td>'.$tab[$temp['data']].$temp['name'].'</td>	
			<td>'.($temp['no']?$temp['no']:$code).'</td>			
		';
		echo '<td align="right">'.number_format($nominal).'</td>';
		echo '<td align="right">'.number_format($nominal_ytd).'</td>';
		
		echo '
		</tr>'
		;
		//}
		
	}
	echo '<tr bgcolor="#66CCFF"><tr>';
	echo '</table>';
}


