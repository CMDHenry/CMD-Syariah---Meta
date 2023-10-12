<?
require '../requires/config.inc.php';
require '../requires/authorization.inc.php';
require '../requires/general.inc.php';
require '../requires/convert.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/numeric.inc.php';
require '../requires/referer_check.inc.php';
require '../classes/recordset_ajax.class.php';

if (!isset($_SESSION['id']) ){
	//echo "err:1000 ";
} else{
	
	$fk_fatg=$_REQUEST["fk_fatg"];
	$nilai_ap_customer=$_REQUEST["nilai_ap_customer"];
	$total_nilai_pinjaman=$_REQUEST["total_nilai_pinjaman"];
	
	$total_taksir=$_REQUEST["total_taksir"];	
	if($total_taksir<=0)$ltv=0;
	else $ltv=($total_nilai_pinjaman/$total_taksir*100);
	$ltv=round($ltv,2);
	//echo $ltv.'----';
	
	$akumulasi_os_pokok=$_REQUEST["akumulasi_os_pokok"]+$total_nilai_pinjaman;
	$rate_flat=$_REQUEST["rate_flat"];
	$fk_produk=$_REQUEST["fk_produk"];
	$fk_cabang=$_REQUEST["fk_cabang"];
	
	$query="select * from tblcabang where kd_cabang='".$fk_cabang."'";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	
	switch($lrow["jenis_cabang"]){
		case "Cabang" : 
			$start="1";
			//$start="1";		
		break;
		case "Unit" : 
			$start="1";	
			//$start="1";	
		break;
		case "Pos" : 
			$start="1";	
		break;
	}
	$max=$start;//default awal , misal cabang maka mulai dari bm, unit mulai dari unit,pos mulai dari pos
	//echo $query;
	
	$query="select * from tbljenjang_approval order by no asc";
	$lrs=pg_query($query);
	while($lrow=pg_fetch_array($lrs)){
		$index[$lrow["no"]]=$lrow["no"];
	}
	$query="select * from tblproduk where kd_produk='".$fk_produk."'";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	
	for($j=0;$j<=3;$j++){
		$max_approval[$j]=$index[$start];//default awal tiap syarat 
	}	
	
	//syarat approval	
	//$approval[0]="ltv_";
	$approval[0]="pinjaman_";
	//$approval[2]="rate_flat_";
	//$approval[3]="one_obligor_";
	
	//nilai dari tiap syarat yg diambil
	//$nilai[0]=$ltv;
	$nilai[0]=$total_nilai_pinjaman;
	//$nilai[2]=$rate_flat;
	//$nilai[3]=$akumulasi_os_pokok;
		
	//echo $rate_flat.',';
	
	for($j=0;$j<=3;$j++){//looping tiap syarat
		for($i=$start;$i<=count($index)-1;$i++){ //looping bykny approval
			$field=$approval[$j].strtolower($index[$i]); //ambil batas approval dari grade		
			
			if($approval[$j]=="rate_flat_"){//kl rate bandingin ke bawah
				if($nilai[$j]<$lrow[$field]){  //bandingkan dgn batas di grade
					if($max<$i+1){ //utk ambil approval terbesar
						$max=$i+1; //utk simpan approval terbesar;	
					}
					$max_approval[$j]=$index[$i+1];//utk tau setiap syarat approval ada di approval mana
				}
			}else{
				if($nilai[$j]>$lrow[$field]){  //bandingkan dgn batas di grade
					if($max<$i+1){ //utk ambil approval terbesar
						$max=$i+1; //utk simpan approval terbesar;	
					}
					$max_approval[$j]=$index[$i+1];//utk tau setiap syarat approval ada di approval mana
				}
			}
			
		}	
	
	}
	for($j=0;$j<=3;$j++){//detail dari tiap syarat approval
		$nm_approval=strtoupper(str_replace("_"," ",$approval[$j]));
		$detail_approval[$j]=$max_approval[$j]."";
	}
	//echo $max;
	$final_approval=$index[$max]; //utk lempar siapa yg final approval ke tampilan dpn;

	$fk_cif=get_rec("viewtaksir","fk_cif","no_fatg='".$fk_fatg."'");	
	$count=pg_num_rows(pg_query("select * from tblinventory where fk_cif='".$fk_cif."' and status_sbg='Liv'"));
	if($count>0){
		$final_approval=2;	
	}
	if($count>1){
		$final_approval=3;	
	}
	
	echo $final_approval.',';
	for($j=0;$j<=3;$j++){
		//echo $detail_approval;
		//echo $detail_approval[$j].',';
	}
	
	echo $count.',';
}
