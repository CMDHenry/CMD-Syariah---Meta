<?php
include_once("report.php");

function filter_request(){
	global $showBln,$showCab,$showTgl,$showPeriode,$fk_coa;
	//$showCab='t';
	//$showBln='t';
	//$showTgl='t';
	$showPeriode='t';
	
	$fk_coa=($_REQUEST["fk_coa"]);
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



<?

}
function create_filter(){
	global $fk_coa;
?>

<!--     <tr bgcolor="efefef">
          <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef">COA Lawan Transaksi</td>
          <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
                        <input name="fk_coa" type="text" onKeyPress="if(event.keyCode==4) img_fk_cabang.click();" onKeyUp="fNextFocus(event,document.form1.periode_awal)"  value="<?=$fk_coa?>" onChange="fGetCoaData()">&nbsp;<img src="../images/search.gif" id="img_fk_cabang" onClick="fGetCoa()" style="border:0px" align="absmiddle">
                    </td>
          <td width="20%" style="padding:0 5 0 5">Description</td>
          <td width="30%" style="padding:0 5 0 5">
          				<input type="hidden" name="description" value="<?=convert_html($description)?>" class="groove_text" style="width:90%" > <span id="divDescription"><?=convert_html($description)?></span></td>
	  </tr>   
 --> 
<?

}


function excel_content(){
	global $bulan,$tahun,$fk_cabang,$periode_akhir,$tgl,$periode_awal,$fk_coa;	
	
	if($fk_coa != ''){
		$lwhere.=" and fk_owner||type_owner||tr_date in (select fk_owner||type_owner||tr_date from data_accounting.tblgl_auto where fk_coa_d like '%".$fk_coa."' or fk_coa_c like '%".$fk_coa."') ";
		$lwhere2.=" where coa not like '%.".$fk_coa."' and coa not like '%.312%'";
	}
	
	//if($lwhere)$lwhere = " and ".$lwhere;
	
	echo "<table border=1>";
	echo "<tr>";
	echo "<td>No Transaksi</td>";	
	echo "<td>Tipe</td>";	
	echo "<td>Tgl(English)</td>";	
	echo "<td>COA</td>";
	echo "<td>Nama COA</td>";
	echo "<td>Keterangan</td>";	
	echo "<td>Debit</td>";
	echo "<td>Kredit</td>";
	echo "<td>Head Account</td>";		
	echo "</tr>";
	
	$lquery = "
	select DISTINCT ON (fk_owner,type_owner,tr_date,reference_transaksi)fk_owner,type_owner,tr_date,reference_transaksi from(
		select * from data_accounting.tblgl_auto 
		where tr_date >='".$periode_awal." 00:00:00' and tr_date <='".$periode_akhir." 23:59:59'
		".$lwhere."
		order by tr_date,no_bukti
	)as tblmain
	";
	//showquery($lquery);
	
	$lrs=pg_query($lquery);
	$i=0;
	$data_olahan = array();
	$grand_total = array();
	while ($row=pg_fetch_array($lrs)){
		$row["reference_transaksi"]=str_replace("'","''",$row["reference_transaksi"]);

		$query_detail="
		select *,
		case when fk_coa_d is not null then total end as debit,
		case when fk_coa_c is not null then total end as credit 
		from (
			select * from data_accounting.tblgl_auto 
			where total!=0 and type_owner not in('LABA RUGI BULAN BERJALAN')and fk_owner='".$row["fk_owner"]."' and type_owner='".$row["type_owner"]."' and tr_date='".$row["tr_date"]."' ".($row["reference_transaksi"]?"and reference_transaksi='".$row["reference_transaksi"]."'":"")."
			order by no_bukti asc 
		) as tblgl_auto
		inner join (select coa, description as desc_coa,fk_head_account from tblcoa ".$lwhere2.") as tblcoa on fk_coa_d=coa or fk_coa_c=coa
		";
		$lrs_detail=pg_query($query_detail);		
		//showquery($query_detail);
		while($lrow_detail=pg_fetch_array($lrs_detail)){				
			echo "<tr>";
			echo "<td>".$lrow_detail["fk_owner"]."</td>";
			echo "<td>".$lrow_detail["type_owner"]."</td>";
			echo "<td>".date("m/d/Y",strtotime($lrow_detail["tr_date"]))."</td>";
			echo "<td>&nbsp;".$lrow_detail["coa"]."</td>";
			echo "<td>".$lrow_detail["desc_coa"]."</td>";
			echo "<td>".$lrow_detail["reference_transaksi"]."</td>";
			echo "<td align='right'>".convert_money("",$lrow_detail["debit"])."</td>";
			echo "<td align='right'>".convert_money("",$lrow_detail["credit"])."</td>";
			echo "<td>".$lrow_detail["fk_head_account"]."</td>";
			
			echo "</tr>";
		}
	}

		
	echo "</table>";
}

