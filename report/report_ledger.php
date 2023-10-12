<?php
include_once("report.php");

function filter_request(){
	global $fk_coa,$description,$showCab,$showPeriode,$strisi,$strisi_cabang,$fk_cabang;
	
	$showPeriode='t';
	//$showBln='t';
	//$showCab='t';
	if($_SESSION["jenis_user"]=='Cabang')$showCab='t';
	$strisi=($_REQUEST["strisi"]);
	$strisi_cabang=($_REQUEST["strisi_cabang"]);
	$fk_coa=($_REQUEST["fk_coa"]);
	$fk_cabang=($_REQUEST["fk_cabang"]);
	$description=($_REQUEST["description"]);
	
	//echo $strisi_cabang."aa";
}


function fGet(){
?>


function fGetCoa(){
	fGetNC(false,'20171000000034','fk_coa','Ganti Item Kendaraan',document.form1.fk_coa,document.form1.fk_coa,'','','','')
}

function fGetCoaData(){
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataCoaState
	lSentText="table= tbltemplate_coa&field=(description)&key=coa&value="+document.form1.fk_coa.value
	lObjLoad.open("POST","../ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}

function fGetDataCoaState(){
	if (this.readyState == 4){
		//confirm(this.responseText)
		if (this.status==200 && this.responseText!="") {
			lTemp=this.responseText.split('¿');
			document.getElementById('divDescription').innerHTML=document.form1.description.value=lTemp[0]
		} else {
		document.getElementById('divDescription').innerHTML=document.form1.description.value="-"
		}
	}
}



function fGetCabang(){
	fGetNC(false,'20170900000010','fk_cabang','Ganti Kendaraan',document.form1.fk_cabang,document.form1.fk_cabang,'','','','')
}

function fGetCabangData(){
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataCabangState
	lSentText="table= tblcabang&field=(nm_cabang)&key=kd_cabang&value="+document.form1.fk_cabang.value
	lObjLoad.open("POST","../ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}

function fGetDataCabangState(){
	if (this.readyState == 4){
		//confirm(this.responseText)
		if (this.status==200 && this.responseText!="") {
			lTemp=this.responseText.split('¿');
			document.getElementById('divNmCabang').innerHTML=document.form1.nm_cabang.value=lTemp[0]
		} else {
		document.getElementById('divNmCabang').innerHTML=document.form1.nm_cabang.value="-"
		}
	}
}


<?

}



function create_filter(){
	global $periode_awal1,$periode_akhir1,$kd_cabang,$nm_cabang,$bulan,$tahun,$fk_coa,$description,$strisi,$strisi_cabang;
?>
     <tr bgcolor="efefef">
          <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef">COA Lawan Transaksi</td>
          <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                        <input name="fk_coa" type="text" onKeyPress="if(event.keyCode==4) img_fk_cabang.click();" onKeyUp="fNextFocus(event,document.form1.periode_awal)"  value="<?=$fk_coa?>" onChange="fGetCoaData()">&nbsp;<img src="../images/search.gif" id="img_fk_cabang" onClick="fGetCoa()" style="border:0px" align="absmiddle">
          </td>
          <td width="20%" style="padding:0 5 0 5">Description</td>
          <td width="30%" style="padding:0 5 0 5">
          				<input type="hidden" name="description" value="<?=convert_html($description)?>" class="groove_text" style="width:90%" > <span id="divDescription"><?=convert_html($description)?></span></td>
	  </tr>   
      
       <input type="hidden" name="strisi" id="strisi" value="<?=convert_html($strisi)?>">
        <script>
            arrFieldTable_detil={
			0:{'name':'coa','caption':'COA','type':'get','source':'20171000000034','size':'10','is_required':'t','table_db':'tbltemplate_coa','table_db_inner_join':'','field_key':'coa','field_get':'description','referer' : '1'},
			1:{'name':'description','caption':'Description','type':'readonly','size':'10','is_required':'t','is_readonly':'t'},
            };
            table_detil=new table();
            table_detil.init("<strong>Detail COA</strong>","Table_detil",arrFieldTable_detil,document.form1,{'id':'Table_detil','style':'display:inline-table','border':'0','cellpadding':'1','cellspacing':'1','width':'100%','class':'border','align':'center'});
            table_detil.setIsi(document.form1.strisi.value);
            document.getElementById("divdetil").appendChild(table_detil.table)
        </script>   
<? 
	if($_SESSION["jenis_user"]=='HO'){
?>        
        
        <input type="hidden" name="strisi_cabang" id="strisi_cabang" value="<?=convert_html($strisi_cabang)?>">
        <script>
            arrFieldTable_detil_cabang={
			0:{'name':'kd_cabang','caption':'Kode Cabang','type':'get','source':'20170900000010','size':'10','is_required':'t','table_db':'tblcabang','table_db_inner_join':'','field_key':'kd_cabang','field_get':'nm_cabang','referer' : '1'},
			1:{'name':'nm_cabang','caption':'Nama Cabang','type':'readonly','size':'10','is_required':'t','is_readonly':'t'},
            };
            table_detil_cabang=new table();
            table_detil_cabang.init("<strong>Detail Cabang</strong>","Table_detil_cabang",arrFieldTable_detil_cabang,document.form1,{'id':'Table_detil_cabang','style':'display:inline-table','border':'0','cellpadding':'1','cellspacing':'1','width':'100%','class':'border','align':'center'});
            table_detil_cabang.setIsi(document.form1.strisi_cabang.value);
            document.getElementById("divdetil_cabang").appendChild(table_detil_cabang.table)
        </script>           
               
<?	
	}
}

function excel_content(){
	global $bulan,$tahun,$fk_cabang,$nm_cabang,$fk_coa,$periode_awal,$periode_akhir,$strisi,$strisi_cabang;
	
	if($strisi!=""){
		$l_arr_row = split(chr(191),$strisi);
		$lwhere2.=" (";
		for ($i=0; $i<count($l_arr_row)-1; $i++){
			$l_arr_col=split(chr(187),$l_arr_row[$i]);
			$lwhere2.=" fk_coa_d like '%".($l_arr_col[0])."%' or  fk_coa_c like '%".($l_arr_col[0])."%'";	
			if($i!=count($l_arr_row)-2)$lwhere2.=" or ";
			$lwhere2a.="or coa like '%".($l_arr_col[0])."'";	
		}
		$lwhere2.=" )";
	}
	
	$lwhere2a=ltrim($lwhere2a,'or');
	if($lwhere2a)$lwhere2a='and ('.$lwhere2a.')';
	//echo $strisi_cabang."aaa";
	
	if($fk_cabang)$strisi_cabang=$fk_cabang.chr(191);
	if($strisi_cabang!=""){
		$l_arr_row = split(chr(191),$strisi_cabang);
		if ($lwhere2!="") $lwhere2.=" and ";
		$lwhere2.=" (";
		//echo count($l_arr_row);
		for ($i=0; $i<count($l_arr_row)-1; $i++){
			$l_arr_col=split(chr(187),$l_arr_row[$i]);
			//echo $i.'<br>';
			$lwhere2.=" fk_coa_d like '".($l_arr_col[0]).".%' or  fk_coa_c like '".($l_arr_col[0]).".%'";	
			if($i!=count($l_arr_row)-2)$lwhere2.=" or ";
			$lwhere2a.=" and coa like '".($l_arr_col[0])."%'";	
		}
		$lwhere2.=" )";

	}
	if ($lwhere2!="") $lwhere2=" and ".$lwhere2;

	$bulan=date('m',strtotime($periode_awal));
	$tahun=date('Y',strtotime($periode_awal));
	
	$source_schema='data_accounting';
	$time_month=strtotime($bulan.'/01/'.$tahun);
	$time_month_before_lastday=strtotime(date("n/d/Y",$time_month)." -1 days");

	if($fk_coa){
		$row_coa=pg_fetch_array(pg_query("
		select transaction_type as tr_type,fk_currency,type_saldo,coa,tblcoa.description,nm_perusahaan,alamat,nm_cabang from (
			select * from tbltemplate_coa where coa like '%".convert_sql($fk_coa)."%' 
		) as tblcoa
		inner join tblhead_account on tblhead_account.code=tblcoa.fk_head_account
		left join tblcabang on fk_cabang = kd_cabang
		"));
		$lwhere1=$lwhere2="";
		$lwhere3=" and fk_owner||type_owner||tr_date in (select fk_owner||type_owner||tr_date from data_accounting.tblgl_auto where fk_coa_d like '%".$fk_coa."' or fk_coa_c like '%".$fk_coa."') ";
		$lwhere4=" and coa not like '%.".$fk_coa."' and coa not like '%.312%'";
		$order_by="order by tr_date,no_bukti";
		
	}

	$time_month_before=strtotime(date("n/d/Y",$time_month)."-1 month");
	$time_month_lastday=strtotime(date("n/d/Y",$time_month)."+1 month -1 days");
	
	$l_month = date('n',$time_month_before);	
	$l_year = date('Y',$time_month_before);	

	$data_olahan = array();
	$data_olahan_footer = array();
		
	$start_day=$periode_awal." 00:00:00";
	$end_day=$periode_akhir." 23:59:59";
	
	$order_by="order by fk_coa_gl,tr_date,no_bukti";
	
	if($fk_coa){
		$order_by="order by tr_date,no_bukti";
	}
	
	$query="
		select * from (
			select distinct on(no_bukti)no_bukti,reference_transaksi,tr_date, 
				null, 'Auto'::text as type_of_source, 
				case when fk_coa_d is not null then total end as total_debit, 
				case when fk_coa_c is not null then total end as total_credit, 
				case when fk_coa_d is not null then fk_coa_d else fk_coa_c end as fk_coa_gl, 		
				rate, type_owner, fk_owner,data_batal,
				case when type_owner like 'BATAL%' or data_batal is not null then 'YA' end as tipe_batal,reference_type,tblgl_auto.description as keterangan
			from (
				select (fk_owner||tr_date||COALESCE(reference_transaksi, ''))as data,
				* from ".$source_schema.".tblgl_auto
				where  tr_date>='#".$start_day."#' and tr_date<='#".$end_day."#' --and fk_owner='2105.ADJ.0000045'
				".$lwhere1." ".$lwhere2." ".$lwhere3."
			) as tblgl_auto
			left join(--BUAT TARIK ADA LAWAN BATALNYA 
				select (fk_owner||tr_date||COALESCE(reference_transaksi, ''))as data_batal 
				from ".$source_schema.".tblgl_auto
				inner join (
					select coa,description,transaction_type as tr_type,fk_head_account from tblcoa
					where true ".$lwhere4."
				)as tblcoa on fk_coa_d=coa or fk_coa_c=coa
				where  tr_date>='#".$start_day."#' and tr_date<='#".$end_day."#' and type_owner like 'BATAL%'
				".$lwhere1." ".$lwhere2." ".$lwhere3."
			)as tblgl_batal on data=data_batal and case when type_owner not like 'BATAL%' then true else false end 
		)as tblmain
		full join (
			select coa,description,transaction_type as tr_type,fk_head_account,fk_cabang as fk_cabang_coa from tblcoa
			where true ".$lwhere4." ".$lwhere2a."
		)as tblcoa on fk_coa_gl=coa
		inner join tblhead_account on tblhead_account.code=tblcoa.fk_head_account
		
		--where total_debit <> 0 or total_credit <> 0		
		".$order_by.";
	";
	$lrs=pg_query($query);
	//showquery($query);
	$i=0;

	while ($row=pg_fetch_array($lrs)){
		$row["fk_coa_gl"]=$row["coa"];
		if(($temp!=$row["fk_coa_gl"] && $fk_coa == '') || $i==0){
			
			$temp_coa=$row["fk_coa_gl"];
			//biar dapat saldo awal coa aslinya			
			if($fk_coa){//kalau coa lawan ambil tipe coanya dari query row coa
				$row["fk_coa_gl"]=$fk_coa;
				$type_saldo=$row_coa["type_saldo"];	
			}else {
				$type_saldo=$row["type_saldo"];	
			}
			
			$query_saldo="
			select sum(balance_cash+balance_bank+balance_memorial+balance_gl_auto) as saldo_awal from data_accounting.tblsaldo_coa
			where tr_year=".$l_year." and fk_coa = '".$row["fk_coa_gl"]."' ".$lwhere."";
			if ($type_saldo=="Rollover") {
				$query_saldo.="  and tr_month=".$l_month;
			} else {
				$query_saldo.="  and tr_month<=".$l_month;
			}		
			
			$row_saldo_awal=pg_fetch_array(pg_query($query_saldo." ".$lwhere));		
			//showquery($query_saldo." ".$lwhere);					
			$saldo=$row_saldo_awal["saldo_awal"];
						
			$data_olahan[$i]["fk_cabang_coa"]=$row["fk_cabang_coa"];
			$coa=explode('.',$row["fk_coa_gl"]);	
			$data_olahan[$i]["fk_coa_natural"]=$coa[1];
			$data_olahan[$i]["description"]="Saldo Awal";
			if(date('d',strtotime($periode_awal))!='01'){
				$saldo+=get_saldo_coa_harian($row["fk_coa_gl"]);
			}
			$data_olahan[$i]["saldo_akhir"]=$saldo;//*$row_rate["rate"]	
			$i++;
			
			
			$row["fk_coa_gl"]=$temp_coa;
			$temp=$row["fk_coa_gl"];
		}
		
		if($row["total_debit"]!=0 || $row["total_credit"]!=0){
			$data_olahan[$i]["tanggal"]=date("d/n/Y",strtotime($row["tr_date"]));
			$data_olahan[$i]["fk_coa_gl"]=$row["fk_coa_gl"];
			
			if($fk_coa!=''){//untuk data coa lawan dibalik
				$debit=$row["total_debit"];
				$credit=$row["total_credit"];
				$row["total_debit"]=$credit;
				$row["total_credit"]=$debit;
				$row["tr_type"]=$row_coa["tr_type"];
			}
			
			if($row["reference_type"]=='DETAIL'){
				$row["reference_transaksi"]=$row["keterangan"];
			}
			
			$coa=explode('.',$row["fk_coa_gl"]);			
			$data_olahan[$i]["fk_cabang_coa"]=$row["fk_cabang_coa"];		
			$data_olahan[$i]["fk_coa_natural"]=$coa[1];
			$coa=$coa[1];
			$nama_coa=get_rec("tbltemplate_coa","description","coa='".$coa."'");

			$data_olahan[$i]["description"]=$nama_coa;
			$data_olahan[$i]["type_owner"]=$row["type_owner"];
			$data_olahan[$i]["type_of_source"]=$row["type_of_source"];
			$data_olahan[$i]["no_bukti"]=$row["no_bukti"];
			$data_olahan[$i]["fk_owner"]=$row["fk_owner"];
			$data_olahan[$i]["transaksi_debit"]=$row["total_debit"];
			$data_olahan[$i]["transaksi_kredit"]=$row["total_credit"];
			
			$data_olahan[$i]["reference_transaksi"]=$row["reference_transaksi"];
		
			$data_olahan_footer[0]["transaksi_debit"]+=$row["total_debit"];
			$data_olahan_footer[0]["transaksi_kredit"]+=$row["total_credit"];
		
			if ($row["tr_type"]=="D") {
				$saldo+=($row["total_debit"]-$row["total_credit"]);
			} else {
				$saldo+=($row["total_credit"]-$row["total_debit"]);
			}
			
			$data_olahan[$i]["saldo_akhir"]=$saldo;
			
			$data_olahan[$i]["tipe_batal"]=$row["tipe_batal"];
			$i++;
		}
	}
	$data_olahan_footer[0]["transaksi_debit"]=$data_olahan_footer[0]["transaksi_debit"];
	$data_olahan_footer[0]["transaksi_kredit"]=$data_olahan_footer[0]["transaksi_kredit"];
		
	if($i >= 1){
		//echo $fk_coa." - ".$row_coa['description'].'<br>';
		echo "<table border=1>";
		echo "<tr>";
		echo "<td>No</td>";
		echo "<td>Tanggal</td>";
		echo "<td>Kode Cabang</td>";			
		echo "<td>COA</td>";		
		echo "<td>Description</td>";
		echo "<td>Reference</td>";
		echo "<td>Batal</td>";	
		echo "<td>Type</td>";
		echo "<td>No Bukti</td>";
		echo "<td>Debit</td>";
		echo "<td>Kredit</td>";
		echo "<td>Saldo</td>";
		//echo "<td>Keterangan</td>";
		echo "</tr>";
		for($j=0;$j<=$i-1;$j++){
			echo "<tr>";
			echo "<td>".(($j>0)?$j:'')."</td>";
			echo "<td>".$data_olahan[$j]["tanggal"]."</td>";
			echo "<td>".$data_olahan[$j]["fk_cabang_coa"]."</td>";			
			echo "<td>".$data_olahan[$j]["fk_coa_natural"]."</td>";
			echo "<td>".$data_olahan[$j]["description"]."</td>";
			echo "<td>".$data_olahan[$j]["reference_transaksi"]."</td>";
			echo "<td>".$data_olahan[$j]["tipe_batal"]."</td>";			
			echo "<td>".$data_olahan[$j]["type_owner"]."</td>";
			echo "<td>".$data_olahan[$j]["fk_owner"]."</td>";
			echo "<td align='right'>".convert_money("",$data_olahan[$j]["transaksi_debit"],2)."</td>";
			echo "<td align='right'>".convert_money("",$data_olahan[$j]["transaksi_kredit"],2)."</td>";
			echo "<td align='right'>".convert_money("",$data_olahan[$j]["saldo_akhir"],2)."</td>";
			//echo "<td>".$data_olahan[$j]["keterangan"]."</td>";
			echo "</tr>";
		}
		echo "<tr>";
		echo "<td></td>";
		echo "<td></td>";
		echo "<td></td>";
		echo "<td></td>";
		echo "<td>".$data_olahan_footer[0]["tanggal"]."</td>";
		echo "<td>".$data_olahan_footer[0]["description"]."</td>";
		echo "<td></td>";
		echo "<td>".$data_olahan_footer[0]["type"]."</td>";
		echo "<td>&nbsp;".$data_olahan_footer[0]["no_bukti"]."</td>";
		echo "<td align='right'>".convert_money("",$data_olahan_footer[0]["transaksi_debit"],2)."</td>";
		echo "<td align='right'>".convert_money("",$data_olahan_footer[0]["transaksi_kredit"],2)."</td>";
		//echo "<td align='right'>".round($data_olahan_footer[0]["saldo_akhir"])."</td>";
		echo "<td></td>";
		echo "</tr>";
		echo "</table>";
	}
	?> 
    	<table></table>
    	<table>
            <tr>
                <td colspan="4" align='center'>Dibuat Oleh</td>
                <td colspan="4" align='center'>Diperiksa Oleh</td>
                <td colspan="4" align='center'>Diketahui Oleh</td>
            </tr>
            <tr></tr>
            <tr></tr>
            <tr></tr>
            <tr>
                <td colspan="4" align='center'><?=get_karyawan_by_jabatan('Kasir','000') ?></td>
                <td colspan="4" align='center'><?=get_karyawan_by_jabatan('Supervisor Finance','000') ?></td>
                <td colspan="4" align='center'><?=get_karyawan_by_jabatan('Manager Finance','000') ?></td>
            </tr>
        </table>
	<?
}


function get_saldo_coa_harian($fk_coa=NULL){
	global $bulan,$tahun,$fk_cabang,$nm_cabang,$periode_awal,$periode_akhir,$strisi,$strisi_cabang;

	
	if($periode_awal=='')$periode_awal=today_db;
	if($periode_akhir=='')$periode_akhir=today_db;
	if($fk_cabang != ''){
		$lwhere.=" and fk_coa like '".$fk_cabang."%' ";
		$lwhere1.=" and fk_cabang = '".$fk_cabang."' ";
	}		
	
	if($fk_coa){
		$row_coa=pg_fetch_array(pg_query("
		select transaction_type as tr_type,fk_currency,type_saldo,coa,tblcoa.description,nm_perusahaan,alamat,nm_cabang from (
			select * from tblcoa where coa like '%".convert_sql($fk_coa)."%'
		) as tblcoa
		inner join tblhead_account on tblhead_account.code=tblcoa.fk_head_account
		left join tblcabang on fk_cabang = kd_cabang
		"));
	}
	
	
	$start_day=date('m',strtotime($periode_awal)).'/01/'.date('Y',strtotime($periode_awal))." 00:00:00";
	$end_day=date('m/d/Y',strtotime('-1 second',strtotime($periode_awal)))." 23:59:59";
	
	$source_schema='data_accounting';

	$query="
		select * from(
			select * from (
				select no_bukti,reference_transaksi,tr_date,description, 
					null, 'Auto'::text as type_of_source,cast(fk_owner as text), 
					case when fk_coa_d is not null then total end as total_debit, 
					case when fk_coa_c is not null then total end as total_credit, 
					rate, type_owner, fk_owner
				from (
					select * from ".$source_schema.".tblgl_auto
					where (fk_coa_d like '%".convert_sql($fk_coa)."%' or fk_coa_c like '%".convert_sql($fk_coa)."%') and tr_date>='#".$start_day."#' and tr_date<='#".$end_day."#' ".$lwhere1."
				) as tblgl_auto
				left join tblcustomer on tblcustomer.no_cif=tblgl_auto.fk_customer 
			)as tblmain
			where total_debit <> 0 or total_credit <> 0
		)as tblmain2
		order by tr_date,no_bukti
	
	";
	$lrs=pg_query($query);
	
	//showquery($query);
	$i=1;
	while ($row=pg_fetch_array($lrs)){
		if($row["total_debit"]!=0 || $row["total_credit"]!=0){
		
			if ($row_coa["tr_type"]=="D") {
				$saldo+=($row["total_debit"]-$row["total_credit"]);
			} else {
				$saldo+=($row["total_credit"]-$row["total_debit"]);
			}
			
			$i++;
		}
	}
	return $saldo;
}






