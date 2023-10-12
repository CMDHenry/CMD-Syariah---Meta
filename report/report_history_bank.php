<?php
include_once("report.php");

function filter_request(){
	global $showTgl,$showPeriode,$showCab,$fk_bank;
	$nm_periode="";
	$showPeriode='t';
	//$showTgl='t';
	$showCab='t';
	$fk_bank=$_REQUEST["fk_bank"];
}

function fGet(){
?>

function fGetBank(){
fGetNC(false,'20171100000051','fk_bank','Ganti Kota',document.form1.fk_bank,document.form1.fk_bank,'','','20170900000023')
if (document.form1.fk_bank.value !="")fGetBankData()
}

function fGetBankData(){
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataBankState
	lSentText="table=tblbank&field=(nm_bank)&key=kd_bank&value="+document.form1.fk_bank.value
	lObjLoad.open("POST","../ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}
function fGetDataBankState(){
	if (this.readyState == 4){
    	//confirm(this.responseText)
		if (this.status==200 && this.responseText!="") {
			lTemp=this.responseText.split('¿');
			document.getElementById('divnm_bank').innerHTML=document.form1.nm_bank.value=lTemp[0]
		} else {
			document.getElementById('divnm_bank').innerHTML=document.form1.nm_bank.value="-"
		}
	}
}


<?
}
function create_filter(){
	global $periode_awal1,$periode_akhir1,$fk_sbg,$fk_cif,$fk_bank,$nm_bank,$is_data_unit_pos;	
?>
    <tr bgcolor="efefef">
        <td style="padding:0 5 0 5">Bank</td>
        <td style="padding:0 5 0 5"><input name="fk_bank" type="text" value="<?=$fk_bank?>" onKeyUp="" onChange="fGetBankData()">&nbsp;<img src="../images/search.gif" id="img_fk_bank" onClick="fGetBank()" style="border:0px" align="absmiddle"></td>
        <td style="padding:0 5 0 5">Nama Bank</td>
        <td style="padding:0 5 0 5"><input type="hidden" name="nm_bank" class='groove_text' value="<?=$nm_bank?>"><span id="divnm_bank"><?=$nm_bank?></span></td>
    </tr>              
                
                
                     
<?	
}


function excel_content(){
	global $periode_awal,$periode_akhir,$fk_bank,$tgl,$fk_cabang;
	if($periode_awal != '' && $periode_akhir != ''){
		//$lwhere.=" tgl_tr between '".$periode_awal." 00:00:00' and '".$periode_akhir." 23:59:59'";
		$lwhere2.=" tr_date between '".$periode_awal." 00:00:00' and '".$periode_akhir." 23:59:59'";
	}

	if($fk_cabang != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" tblcabang_detail_bank.fk_cabang = '".$fk_cabang."' ";
		
		//if ($lwhere2!="") $lwhere2.=" and ";
		//$lwhere2.=" fk_cabang = '".$fk_cabang."' ";
	}

	if($fk_bank != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" fk_bank = '".$fk_bank."' ";
		
		//if ($lwhere2!="") $lwhere2.=" and ";
		//$lwhere2.=" fk_bank = '".$fk_bank."' ";
	}


	if ($lwhere!="") $lwhere=" where ".$lwhere;
	if ($lwhere2!="") $lwhere2=" where ".$lwhere2;
	/*
			left join (
			select tgl,awal,akhir,fk_cabang as fk_cabang1,fk_bank as fk_bank1 from data_fa.tblsaldo_bank ".$lwhere2."
		) as tblsaldo_bank on fk_cabang1=kd_cabang
		--left join data_fa.tblhistory_bank on fk_cabang=fk_cabang1 and fk_bank=fk_bank1
		
		--left join (
			--select fk_bank as fk_bank1,fk_cabang as fk_cabang1,keterangan,nominal_masuk,nominal_keluar,id_tr,no_referensi from data_fa.tblhistory_bank
		--)as tblhistory_bank on id_tr=fk_owner
		
	*/
	$fom=date('Y-m',strtotime($periode_awal)).'-01 00:00:00';
	$bulan_lalu=date('m',strtotime('-1 day',strtotime($fom)));
	$tahun_lalu=date('Y',strtotime('-1 day',strtotime($fom)));
	//echo $fom;
	$query = "
	SELECT * FROM(
		select * from(
			select kd_cabang from tblcabang
		)as tblcabang
		inner join (
			select description,coa, tblcabang_detail_bank.fk_cabang,nm_bank,fk_bank,tblcabang_detail_bank.fk_cabang||'.'||fk_coa as fk_coa,awal from tblcabang_detail_bank 
			inner join tbltemplate_coa on fk_coa=coa
			left join(
				select fk_coa as fk_coa1,balance_gl_auto as awal from data_accounting.tblsaldo_coa 
				where tr_month='".$bulan_lalu."' and tr_year='".$tahun_lalu."'
			)as tblsaldo_coa on fk_coa1 =tblcabang_detail_bank.fk_cabang||'.'||coa
			left join tblbank on kd_bank=fk_bank
			".$lwhere." 
		)as tblsaldo_coa on fk_cabang=kd_cabang
		left join(
			select fk_owner||type_owner||tr_date||case when reference_transaksi is not null then reference_transaksi else '' end as no_gl,case when fk_coa_d is not null then fk_coa_d else fk_coa_c end as fk_coa1,case when type_owner like '%BATAL%' then 'BATAL' end as is_batal from data_accounting.tblgl_auto 
			".$lwhere2." 
		) as tblgl_auto on fk_coa=fk_coa1
		left join(
			select no_bukti,case when reference_type ='DETAIL' then description else reference_transaksi end as reference_transaksi,tr_date as tgl_tr,
				case when fk_coa_c is not null then total end as nominal_masuk, 
				case when fk_coa_d is not null then total end as nominal_keluar, 
				case when fk_coa_d is not null then fk_coa_d else fk_coa_c end as fk_coa_gl, 		
				type_owner, fk_owner , fk_owner||type_owner||tr_date||case when reference_transaksi is not null then reference_transaksi else '' end as no_gl_lawan from data_accounting.tblgl_auto
			".$lwhere2." 
		)as tbllawan_gl on no_gl=no_gl_lawan
		left join(
			select description as desc_coa,coa as fk_coa2 from tblcoa
		)as tblcoa on fk_coa_gl=fk_coa2
		where fk_coa_gl not like '%.312%' and fk_coa_gl !=fk_coa1
	)AS TBLALL	
	--where type_owner ='PETTY CASH'
	order by fk_cabang,tgl_tr,no_bukti asc, fk_bank asc
	";

	//showquery($query);
	echo 	
	'<table border="1">
	     <tr>
	     	<td align="center">No</td>
			<td align="center">Kode Cabang</td>						
			<td align="center">Tgl Transaksi</td>
			<td align="center">No Transaksi</td>
			<td align="center">Nama Perkiraan</td>
			<td align="center">Referensi/Keterangan</td>
			<td align="center">Batal</td>			
			<td align="center">Masuk</td>			
			<td align="center">Keluar</td>			
				
		  </tr>
	';

	//	<td align="center">Kode Bank</td>
	//	<td align="center">Nama Bank</td>	
	//	<td align="center">Saldo Akhir</td>	
	

	$lrs = pg_query($query);
	$no=1;
	$arr=array();
	while($lrow=pg_fetch_array($lrs)){

		$kd_bank=$lrow["fk_bank"];
		$tgl_tr=$lrow["tgl_tr"];
		$id_tr=$lrow["fk_owner"];
		$keterangan=$lrow["keterangan"];
		$kd_cabang=$lrow["fk_cabang"];
		$no_referensi=$lrow["reference_transaksi"];
		$desc_coa=$lrow["desc_coa"];
		//echo $lrow["reference_transaksi"];
		
		$fk_coa_bank=$lrow["fk_coa_bank"];
		$nm_coa[$fk_coa_bank]=$lrow["description"];
		
		$nominal_masuk=$lrow["nominal_masuk"];
		$nominal_keluar=$lrow["nominal_keluar"];
		$saldo_awal[$kd_cabang][$kd_bank]=$lrow["awal"];
		$saldo_akhir[$kd_cabang][$kd_bank]=$lrow["akhir"];
		
		$nm_bank[$kd_bank]=$lrow["nm_bank"];

		$arr[$kd_cabang][$kd_bank][$no]['no']=$no;
		$arr[$kd_cabang][$kd_bank][$no]['tgl_tr']=$tgl_tr;
		$arr[$kd_cabang][$kd_bank][$no]['id_tr']=$id_tr;
		$arr[$kd_cabang][$kd_bank][$no]['keterangan']=$keterangan;
		$arr[$kd_cabang][$kd_bank][$no]['desc_coa']=$desc_coa;
		
		$arr[$kd_cabang][$kd_bank][$no]['fk_coa_bank']=$fk_coa_bank;
		$arr[$kd_cabang][$kd_bank][$no]['nominal_masuk']=$nominal_masuk;
		$arr[$kd_cabang][$kd_bank][$no]['nominal_keluar']=$nominal_keluar;
		$arr[$kd_cabang][$kd_bank][$no]['saldo_akhir']=$saldo_akhir;
		$arr[$kd_cabang][$kd_bank][$no]['no_referensi']=$no_referensi;
		$arr[$kd_cabang][$kd_bank][$no]['is_batal']=$lrow["is_batal"];
		$no++;
	}
	
	//echo count($arr>0)."aaa";
	
	if(count($arr>0)){ 
		foreach($arr as $kd_cabang=>$arr2){
			foreach($arr2 as $kd_bank=>$no){
				//echo $kd_bank."aaaa";  
				$total_nominal_masuk = 0;
				$total_nominal_keluar = 0;
				
				echo '<tr>
						<td align="left" colspan="9">'.$kd_bank.' '.$nm_bank[$kd_bank].' ('.$kd_cabang.')</td>
				</tr>';
				
/*				echo '<tr>
						<td align="left" colspan="9">Saldo Awal Bulan</td>
						<td align="right">'.convert_money("",$saldo_awal[$kd_cabang][$kd_bank]).'</td>
				</tr>';
*/				
				$hasil=$saldo_awal[$kd_cabang][$kd_bank];
				//print_r($no);
				$i=1;
				foreach($no as $temp=>$lrow){
					
					$total_nominal_masuk+=$lrow['nominal_masuk'];
					$total_nominal_keluar+=$lrow['nominal_keluar'];
					
					$hasil=$hasil+$lrow['nominal_masuk']-$lrow['nominal_keluar'];
					//<td valign="top" align="left">'.$lrow['keterangan'].'</td>							
					//<td valign="top">&nbsp;'.$kd_bank.'</td>
					//<td valign="top">'.$nm_bank[$kd_bank].'</td>

					echo '
						<tr>
							<td valign="top">'.$i.'</td>
							<td valign="top">&nbsp;'.$kd_cabang.'</td>														
							<td valign="top">'.date("d/m/Y",strtotime($lrow["tgl_tr"])).'</td>
							<td valign="top">&nbsp;'.$lrow['id_tr'].'</td>
							<td valign="top">&nbsp;'.$lrow['desc_coa'].'</td>							
							<td valign="top" align="left">'.$lrow['no_referensi'].'</td>
							<td valign="top" align="left">'.$lrow['is_batal'].'</td>
					';
					echo '
							<td valign="top" align="right">'.convert_money("",$lrow['nominal_masuk']).'</td>
							<td valign="top" align="right">'.convert_money("",$lrow['nominal_keluar']).'</td>
							
						</tr>
					';	
					//<td valign="top" align="right">'.convert_money("",$hasil).'</td>
					$i++;
				}
		
				echo '
				 <tr>
					<td align="center" colspan="7">Total</td>		
					<td align="right">'.convert_money("",$total_nominal_masuk).'</td>			
					<td align="right">'.convert_money("",$total_nominal_keluar).'</td>
							
				 </tr>
				';	
				//<td align="right"></td>		
			}
	
		}
	}
	
	echo '</table>';
}


