<?

function html_additional(){

	if($_REQUEST["fk_voucher"]){
?>
    <table cellpadding="0" cellspacing="1" border="0" width="100%">
       <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
            <td align="center">Kode Bank</td>
            <td align="center">Kode Cabang</td>
            <td align="center">Keterangan</td>
            <td align="center">Nominal Masuk</td>
            <td align="center">Nominal Keluar</td>            
        </tr>        

<?		
		$lrs=pg_query("select * from data_fa.tblhistory_bank
		where id_tr = '".$_REQUEST["fk_voucher"]."' and keterangan !='CORRECTION' order by pk_id");
		while($lrow=pg_fetch_array($lrs)){
?>			
		<tr bgcolor="efefef">
            <td align="center" style="padding:0 5 0 5"><?=$lrow["fk_bank"]?></td>
            <td align="center" style="padding:0 5 0 5"><?=$lrow["fk_cabang"]?></td>
            <td align="center" style="padding:0 5 0 5"><?=$lrow["keterangan"]?></td>
            <td align="center" style="padding:0 5 0 5"><?=convert_money("",$lrow["nominal_masuk"])?></td>
            <td align="center" style="padding:0 5 0 5"><?=convert_money("",$lrow["nominal_keluar"])?></td>      
         </tr>
<?
		}
		
	}

	
}
//CORRECTION
function query_additional($no_correction){
	global $l_success;
	$lrs=pg_query("select * from data_fa.tblhistory_bank
	where id_tr = '".$_REQUEST["fk_voucher"]."' and keterangan !='CORRECTION' order by pk_id");
	$keterangan=$_REQUEST["keterangan"];
	$ket="Correction";
	while($lrow=pg_fetch_array($lrs)){
		
		if($lrow["nominal_masuk"]>0){
			if(!pg_query(update_saldo_bank($lrow["fk_bank"],$lrow["fk_cabang"],0,$lrow["nominal_masuk"],$ket,$lrow["id_tr"],
			$no_correction)))$l_success=0;		
			//showquery(update_saldo_bank($lrow["fk_bank"],$lrow["fk_cabang"],0,$lrow["nominal_masuk"],"Correction",$lrow["id_tr"],
//			$no_correction));
		}
		else if($lrow["nominal_keluar"]>0){
			if(!pg_query(update_saldo_bank($lrow["fk_bank"],$lrow["fk_cabang"],$lrow["nominal_keluar"],0,$ket,$lrow["id_tr"],
			$no_correction)))$l_success=0;	
			//showquery(update_saldo_bank($lrow["fk_bank"],$lrow["fk_bank"],$lrow["nominal_keluar"],0,"Correction",$no_correction));
		}
	}
	
	$lrs_h=pg_query("select distinct type_owner from data_accounting.tblgl_auto
	where fk_owner = '".$_REQUEST["fk_voucher"]."' and type_owner not in('CORRECTION')
	");
	while($lrow_h=pg_fetch_array($lrs_h)){
	
		$arrPost=array();
		$fk_cabang="";
		$lrs=pg_query("
			select * from data_accounting.tblgl_auto
			where fk_owner = '".$_REQUEST["fk_voucher"]."' and type_owner not in('CORRECTION') and type_owner='".$lrow_h["type_owner"]."'
			order by fk_cabang,no_bukti
		");
		$i=0;
		while($lrow=pg_fetch_array($lrs)){

			$total=$lrow["total"];
			if($lrow["fk_coa_d"]!=""){
				$account=$lrow["fk_coa_d"];
				$type='c';				
			}
			elseif($lrow["fk_coa_c"]!=""){
				$account=$lrow["fk_coa_c"];
				$type='d';
			}
			
			$coa=explode('.',$account);
			//$fk_cabang=$coa[0];
			$fk_cabang=$lrow["fk_cabang"];
			
			if($i!=0&&$temp!=$fk_cabang){
				//cek_balance_array_post($arrPost);
				//if(!posting('CORRECTION',$_REQUEST["fk_voucher"],today_db,$arrPost,$temp,'00'))$l_success=0;
				$arrPost=array();		
				$temp=$fk_cabang;	
			}elseif($i==0){
				$temp=$fk_cabang;
			}			
			$arrPost[$account.$i]		= array('type'=>$type,'value'=>$total,'account'=>$account,'reference'=>$keterangan);		
			$i++;
			
			$type_owner=$lrow["type_owner"];
			$fk_owner=$_REQUEST["fk_voucher"];
			$reference_transaksi=$_REQUEST["reference_transaksi"];
			
		}
		//cek_balance_array_post($arrPost);
		//if(!posting('CORRECTION',$_REQUEST["fk_voucher"],today_db,$arrPost,$temp,'00'))$l_success=0;
		
		//echo $l_success;
	}
	$arrPost = gl_balik($fk_owner,$type_owner,$reference_transaksi);
	if(!posting('BATAL '.$type_owner,$fk_owner,today_db,$arrPost,$fk_cabang,'00'))$l_success=0;
	
	
	$no_voucher=$fk_owner;
	$lwhere=" no_voucher='".$no_voucher."'";
	if(strstr($type_owner,'MUTASI BANK')){
		$tbl="data_fa.tblmutasi_bank";
	}
	if(strstr($type_owner,'REKON BANK')){
		$tbl="data_fa.tblrekon_bank";
	}
	if(strstr($type_owner,'PETTY CASH')){
		$tbl="data_fa.tblpetty_cash";
	}
	if(strstr($type_owner,'PAYMENT REQUEST')){
		$tbl="data_fa.tblpayment_request";
	}
	if(!pg_query(insert_log($tbl,$lwhere,'UB'))) $l_success=0;
	if(!pg_query("update ".$tbl." SET tgl_batal='".today_db."',status_data = 'Batal',alasan='".$keterangan."' where ".$lwhere."")) $l_success=0;
	//showquery(insert_log($tbl,$lwhere,'UB'));
	if(!pg_query(insert_log($tbl,$lwhere,'UA'))) $l_success=0;
	
	//$l_success=0;
}

?>

