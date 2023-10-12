<?php
include_once("report.php");

function filter_request(){
	global $showBln,$tgl,$showCab,$jenis_produk;
	$nm_periode="";
	$showBln='t';
	//$showCab='t';
}




function excel_content(){
	global $bulan,$tahun,$fk_customer,$tgl,$fk_cabang,$jenis_produk;

	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_cabang = '".$fk_cabang."' ";
	}
	
	
	if ($lwhere!="") $lwhere=" where ".$lwhere;
	
	$hari=date('t',strtotime($bulan.'/01/'.$tahun));	
	$fom=$bulan.'/01/'.$tahun;
	$eom=$bulan.'/'.$hari.'/'.$tahun;
	$ptgl=" tr_date>='#".$fom."#' and tr_date<='#".$eom."#' ";	
	
	$last_month=get_last_month($fom,1);
	//echo $last_month;
	$l_month=date('n',strtotime($last_month));
	$l_year=date('Y',strtotime($last_month));	
	
	$query = "
	select sum(case when fk_sbg_funding is not null then pokok_hutang end) as funding,sum(case when tgl_wo is not null then pokok_hutang end) as wo from(
		select * from tblinventory
		left join tblcustomer on no_cif=fk_cif
		".$lwhere." --and fk_sbg='20101210100015'
	)as tblmain
	left join data_gadai.tblproduk_cicilan on fk_sbg=no_sbg
	
	left join(
		select kd_cabang,nm_cabang from tblcabang
	)as tblcabang on fk_cabang=kd_cabang
	
	left join(
		select distinct on(fk_sbg)nm_partner,fk_sbg as fk_sbg_funding from data_fa.tblfunding
		left join data_fa.tblfunding_detail on no_funding=fk_funding
		left join tblpartner on fk_partner =kd_partner
		where tgl_unpledging is null or ( tgl_unpledging is not null and tgl_unpledging>='".$tgl."')
	)as tblfunding on no_sbg = fk_sbg_funding	
			
	";
	//showquery($query);
	$lrs = pg_query($query);	
	$lrow=pg_fetch_array($lrs);
	
	$array = array(
		1 => array('no' => '1','name' => 'Fasilitas Pendanaan yang Belum Ditarik'
		,'data'=>'sub1'),
		2 => array('no' => '2','name' => 'Fasilitas Pendanaan yang Belum Ditarik dari Dalam Negeri'
		,'data'=>'sub2_in'),
		3 => array('no' => '3','name' => 'Fasilitas Pendanaan yang Belum Ditarik dari Bank Syariah'
		,'data'=>'sub3_in','coa'=>array('2210000')),
		4 => array('no' => '4','name' => 'Fasilitas Pendanaan yang Belum Ditarik dari Lembaga jasa Keuangan Nonbank Syariah'
		,'data'=>'sub3_in'),
		5 => array('no' => '5','name' => 'Fasilitas Pendanaan yang Belum Ditarik dari Dalam Negeri Lainnya'
		,'data'=>'sub3_in'),
		6 => array('no' => '6','name' => 'Fasilitas Pendanaan yang Belum Ditarik dari Luar Negeri'
		,'data'=>'sub2_in'),
		7 => array('no' => '7','name' => 'Fasilitas Pendanaan yang Belum Ditarik dari Bank Syariah'
		,'data'=>'sub3_in'),
		8 => array('no' => '8','name' => 'Fasilitas Pendanaan yang Belum Ditarik dari Lembaga jasa Keuangan Nonbank Syariah'
		,'data'=>'sub3_in'),
		9 => array('no' => '9','name' => 'Fasilitas Pendanaan yang Belum Ditarik dari Luar Negeri Lainnya'
		,'data'=>'sub3_in'),
		10 => array('no' => '10','name' => 'Fasilitas Pembiayaan kepada Konsumen yang belum ditarik'
		,'data'=>'sub1'),
		11 => array('no' => '11','name' => 'Penerbitan Surat Sanggup Bayar dengan Prinsip Syariah'
		,'data'=>'sub1'),
		12 => array('no' => '12','name' => 'Pendanaan Surat Sanggup Bayar Dalam Negeri'
		,'data'=>'sub2_in'),
		13 => array('no' => '13','name' => 'Pendanaan Surat Sanggup Bayar Luar Negeri'
		,'data'=>'sub2_in'),
		14 => array('no' => '14','name' => 'Nilai Penyaluran pembiayaan Bersama Porsi Pihak Ketiga'
		,'data'=>'sub1'),
		15 => array('no' => '15','name' => 'Penerusan Pembiayaan (Channeling) dengan Akad Wakalah Bil Ujrah'
		,'data'=>'sub2_in'),
		16 => array('no' => '16','name' => 'Penyaluran Kredit dalam Rangka Pembiayaan Bersama (Joint Financing)'
		,'data'=>'sub2_in'),
		17 => array('no' => '17','name' => 'Instrumen Derivatif untuk Lindung Nilai Syariah'
		,'data'=>'sub1'),
		18 => array('no' => '18','name' => 'Posisi Spot untuk Lindung Nilai'
		,'data'=>'sub2_in'),
		19 => array('no' => '19','name' => 'Posisi Forward Agreement'
		,'data'=>'sub2_in'),
		20 => array('no' => '20','name' => 'Rekening Administratif Lainnya'
		,'data'=>'sub1'),
		21 => array('no' => '21','name' => 'Piutang Pembiayaan yang Dihapus Buku'
		,'data'=>'sub2_in','coa'=>array('1142000')),
		22 => array('no' => '22','name' => 'Piutang Pembiayaan Hapus Buku yang Berhasil Ditagih'
		,'data'=>'sub2_in','coa'=>array('zz')),
		23 => array('no' => '23','name' => 'Piutang Pembiayaan yang Dihapus Tagih'
		,'data'=>'sub2_in','coa'=>array('zz')),
		24 => array('no' => '24','name' => 'Pembiayaan Alihan dengan Pengelolaan Penagihan'
		,'data'=>'sub2_in'),
		25 => array('no' => '25','name' => 'Total Rekening Administratif'
		,'data'=>'sub1'),
	);
	//
	$coa_in_out=array("zz");
	
	
	for($i=$hari;$i<=$hari;$i++){
		$tgl=$tahun.'-'.$bulan.'-'.$i;
		//$fom=$tgl;
		$a=0;
		foreach($array as $index=>$data){
			if($data['coa']){
				//print_r($data);
				//echo $data['data'].'<br>';
				$jenis="";
				if(strstr($data['data'],'in'))$jenis='in';
				else $jenis='out';								
				
				$lwhere2='';
				if($data['no']>20){
					$lwhere2=" and reference_transaksi like 'WO%'";
				}
				
				foreach($data['coa'] as $coa){
					$fk_head_account='';
					if(strlen($coa)=='5')$fk_head_account=$coa;
					//echo substr($coa,0,1).'<br>';	
					//echo $coa.'<br>';
					$kd_coa= substr($coa,0,1);
					$query_ledger="
select 
sum(case when fk_coa_d is not null then total end) as total_debit, 
sum(case when fk_coa_c is not null then total end) as total_credit,
coa,tr_type
from data_accounting.tblgl_auto
inner join (
	select coa,description,transaction_type as tr_type,fk_head_account from tblcoa
	".$lwhere." 
)as tblcoa on case when fk_coa_d is not null then fk_coa_d else fk_coa_c end =coa
where ".$ptgl."
and ".($fk_head_account?"fk_head_account='".$fk_head_account."'":"(fk_coa_d like '%".$coa."' or fk_coa_c like '%".$coa."')")." 						
and fk_owner||type_owner||tr_date in (
	select fk_owner||type_owner||tr_date from data_accounting.tblgl_auto 
	inner join (
		select coa,description,transaction_type as tr_type,fk_head_account from tblcoa
		".$lwhere_head." --and fk_cabang='000'
	)as tblcoa on case when fk_coa_d is not null then fk_coa_d else fk_coa_c end =coa
) 
".$lwhere2."
--and coa like '%2510000%'
group by coa,tr_type
					";
					//showquery($query_ledger);
					$lrs_leger=pg_query($query_ledger);
					while ($lrow_ledger=pg_fetch_array($lrs_leger)){
						$saldo=0;
						if ($jenis=='out') {
							$saldo=($lrow_ledger["total_debit"]-$lrow_ledger["total_credit"]);
							if(in_array($coa,$coa_in_out)){
								$saldo=($lrow_ledger["total_debit"]);								
							}
						} else {
							$saldo=($lrow_ledger["total_credit"]-$lrow_ledger["total_debit"]);
							if(in_array($coa,$coa_in_out)){
								$saldo=($lrow_ledger["total_credit"]);
								//echo $lrow_ledger["total_credit"].'sdf<br>';
							}
						}
												
						$code=$data['no'];
						//echo $jenis.'<br>';
						$total[$code]+=$saldo;;
						//if($code=='62')echo $total[$code].'<br>';
						//echo $code.'-'.$saldo.'<br>';
					}
				}
			}
			$a++;
		}
	}
	
	$total["1"]=$total["2"]=$total["3"];
	
	$total["20"]=$total["21"]+$total["22"]+$total["23"];
	$total["25"]=$total["20"]+$total["1"];
				
	
	//print_r($total);
	//$bgcolor['sub1']='#FFCC00';
	//$bgcolor['sub2_in']='#33CCFF';
	//$bgcolor['sub2_out']='#FFCCCC';
	//$bgcolor['sub3']='#CCCCCC';	
	$tab['sub2_in']=$tab['sub2_out']='&nbsp;&nbsp;&nbsp;';
	$tab['sub3_in']=$tab['sub3_out']='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	$tab['detail_in']=$tab['detail_out']='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';	
	
	//$fontcolor['detail_in']=$fontcolor['sub2_in']=$fontcolor['sub3_in']='style="color:blue"';
	//$fontcolor['detail_out']=$fontcolor['sub2_out']=$fontcolor['sub3_out']='style="color:red"';
	
	echo 	
	'<table border="1" >				
	';
	echo '
		<tr >
			<td>Pos</td>	
			<td>Code</td>									
		';
		echo '<td align="right">Total</td>';
		echo '
	</tr>';
	
	foreach($array as $index=>$temp){
		$code=$temp['no'];
		
		$nominal=$total[$code];
		//if(strstr($temp['data'],'out'))$nominal=$total[$code]*-1;
		
		echo '
		<tr bgcolor="'.$bgcolor[$temp['data']].'" '.$fontcolor[$temp['data']].'>
			<td>'.$tab[$temp['data']].$temp['name'].'</td>	
			<td>'.($temp['no']?$temp['no']:$code).'</td>			
		';
		echo '<td align="right">'.number_format($nominal).'</td>';
		echo '
		</tr>'
		;
	}
	echo '<tr bgcolor="#66CCFF"><tr>';
	echo '</table>';
}


	
/*

22 => array('no' => '','name' => '','flag_sum'=>'0'),
if($data['+']){
	$total['105']+=$saldo_awal;
}elseif($data['-']){
	$total['105']-=$saldo_awal;
}

$tambah=explode(",",$data['+']);
foreach($tambah as $row){
	$total[$row]+=$saldo;
}

//echo $data['-'];
$kurang=explode(",",$data['-']);
foreach($kurang as $row){							
	$total[$row]-=$saldo;
}
						
*/		

