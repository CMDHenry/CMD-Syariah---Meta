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
	
	$grade="";
	$strisi_taksir_umum=$_REQUEST["strisi_taksir_umum"];
	$strisi_detail_kondisi_barang=$_REQUEST["strisi_detail_kondisi_barang"];
	//echo fullescape($strisi_detail_kondisi_barang);
	
	$l_arr_row1 = split('¿',$strisi_detail_kondisi_barang);
	//echo count($l_arr_row1)-1;
	for ($j=0; $j<count($l_arr_row1)-1; $j++){	
		$strisi_detail_kondisi_barang1.=$l_arr_col1[0].chr(187).$l_arr_col1[1].chr(187).$l_arr_col1[2].chr(187).convert_money("",$total_taksir,0).chr(191);
		//echo $l_arr_col1[0];
	}
	
	
		
	$l_arr_row = split('¿',$strisi_taksir_umum);
	for ($i=0; $i<count($l_arr_row)-1; $i++){
		$l_arr_col=split('»',$l_arr_row[$i]);		
		$total_awal = $total_taksir = str_replace(",","",$l_arr_col[6]);
		$strisi_detail_kondisi_barang1="";
		
		$l_arr_row1 = split('¿',$strisi_detail_kondisi_barang);
		//echo count($l_arr_row1)-1;
		for ($j=0; $j<count($l_arr_row1)-1; $j++){			
			$l_arr_col1=split('»',$l_arr_row1[$j]);
			
			//print_r($l_arr_col1);
/*			$query="select * from tblbarang 
			left join tblkondisi_barang on tblbarang.fk_jenis_barang=tblkondisi_barang.fk_jenis_barang 
			left join tblkondisi_barang_detail on fk_kondisi_barang=kd_kondisi_barang 
			where kondisi_barang='".$l_arr_col1[0]."' and kd_barang='".$l_arr_col[0]."' ";
			$lrow=pg_fetch_array(pg_query($query));
*/			
			$cek=$l_arr_col1[1];	
			$persen=$l_arr_col1[2];				
			if($cek=='t'){				
				$pengurangan += ($total_awal * $persen/100);
			}
			
			$total_taksir = $total_awal - $pengurangan;
			
			$strisi_detail_kondisi_barang1.=$l_arr_col1[0].chr(187).$l_arr_col1[1].chr(187).$l_arr_col1[2].chr(187).convert_money("",$total_taksir,0).chr(191);
		}
			
		$strisi_taksir_umum1.=$l_arr_col[0].chr(187).$l_arr_col[1].chr(187).$l_arr_col[2].chr(187).$l_arr_col[3].chr(187).$l_arr_col[4].chr(187).$l_arr_col[5].chr(187).$l_arr_col[6].chr(187).convert_money("",$total_taksir,0).chr(187).$l_arr_col[8].chr(191);
	}	
			
	echo fullescape($strisi_taksir_umum1).',';	
	echo $total_taksir.',';	
	echo fullescape($strisi_detail_kondisi_barang1).',';	
	//echo fullescape($strisi_detail_kondisi_barang).',';	

}
