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
	$fk_cabang=$_REQUEST["fk_cabang"];
	$fk_produk=$_REQUEST["fk_produk"];
	
	$query="select * from tblcabang where kd_cabang='".$fk_cabang."'";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	switch($lrow["jenis_cabang"]){
		case "Cabang" : 
			//$default='kacab';
			$default='kapos';
		break;
		case "Unit" : 
			//$default='kaunit';
			$default='kapos';
		break;
		case "Pos" : 
			$default='kapos';
		break;
	}
	
	
	$query="select * from tblproduk where kd_produk='".$fk_produk."'";
	$lrs=pg_query($query);
	$lrow=pg_fetch_array($lrs);
	
	$ltv=$lrow["ltv_".$default];
	
	$strisi="";
	$l_res=pg_query("
	select * from (
		select * from viewtaksir_detail where fk_fatg='".$fk_fatg."'
	)as tblmain
	left join tblbarang on kd_barang=fk_barang
	");

	$no=0;	
	while($lrow=pg_fetch_array($l_res)){
		//$nilai_pinjaman=round($lrow["nilai_taksir"]*$ltv/100);
		$nilai_pinjaman=$lrow["nilai_taksir"];
		$strisi.=$lrow["fk_barang"].chr(187).$lrow["nm_barang"].chr(187).convert_money("",$lrow["nilai_taksir"],2).chr(187).convert_money("",$nilai_pinjaman,2).chr(191);
		$total_pinjaman+=$nilai_pinjaman;
		$no++;
	}
		
	echo fullescape($strisi).',';
	echo $total_pinjaman.',';
}
