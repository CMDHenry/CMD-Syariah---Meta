<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/db_utility.inc.php';
require 'requires/numeric.inc.php';
require 'requires/accounting_utility.inc.php';
require 'requires/timestamp.inc.php';
require 'requires/stok_utility.inc.php';
require 'classes/select.class.php';

$id_menu=trim($_REQUEST["id_menu"]);

$fk_module=get_rec("skeleton.tblmodule","pk_id","fk_menu='".$id_menu."'");

get_data_menu($id_menu);
get_data_module();

$keterangan=trim($_REQUEST["keterangan"]);
$no_batch=($_REQUEST["no_batch"]);
$fk_bank=($_REQUEST["fk_bank"]);
$fk_partner_dealer=($_REQUEST["fk_partner_dealer"]);
$fk_partner_notaris=($_REQUEST["fk_partner_notaris"]);
$fk_partner_asuransi=($_REQUEST["fk_partner_asuransi"]);
$fk_karyawan_sales=($_REQUEST["fk_karyawan_sales"]);
$tgl_bayar=convert_date_english($_REQUEST["tgl_bayar"]);
$no_invoice=($_REQUEST["no_invoice"]);
$fk_cabang=($_REQUEST["fk_cabang"]);$fk_cabang=($_REQUEST["fk_cabang"]);
$fk_cabang_bank=$_REQUEST["fk_cabang_bank"];

$total=($_REQUEST["total"]);
$strmenu=($_REQUEST["strmenu"]);
$fk_cabang=($_REQUEST["fk_cabang"]);

$jenis_pembayaran=($_REQUEST["jenis_pembayaran"]);
$lwhere_status="where status_barang='Baru'";
if($jenis_pembayaran=='Pembayaran Unit'){
	$tujuan=$fk_partner_dealer;
}else if($jenis_pembayaran=='Pembayaran Fidusia'){
	$tujuan=$fk_partner_notaris;
	$lwhere_status='';
}else if($jenis_pembayaran=='Pembayaran Asuransi'){
	$tujuan=$fk_partner_asuransi;
	$lwhere_status='';
}else if($jenis_pembayaran=='Pencairan Datun'){
	$lwhere_status="where status_barang='Datun'";
}else if(strstr($jenis_pembayaran,'Pembayaran TAC')){
	$tujuan=$_REQUEST["tujuan"];
	if($fk_partner_dealer)$tujuan=$fk_partner_dealer;
	if($fk_karyawan_sales)$tujuan=$fk_karyawan_sales;
}


$no_rek=get_rec("tblpartner","no_rek","kd_partner='".$tujuan."'");
$transaksi=$jenis_pembayaran;

$tahun=($_REQUEST["tahun"]);
if(!$tahun)$tahun=date('Y',strtotime(today_db));
$bulan=($_REQUEST["bulan"]);
if(!$bulan)$bulan=date('n',strtotime(today_db));

$query=" 
select * from(
select ((".$tahun."-extract(year from tgl_pengajuan))+1)as tahun_ke,* from data_gadai.tblproduk_cicilan 
inner join(
	select no_fatg,fk_barang,fk_cif,fk_cabang,fk_partner_dealer,status_fatg,fk_karyawan_sales from data_gadai.tbltaksir_umum 
	left join data_gadai.tbltaksir_umum_detail on fk_fatg=no_fatg
	".$lwhere_status." 
)as tbl on no_fatg=fk_fatg and case when status_fatg not in('Tambah DP','Ganti CIF') then true else false end 
left join (
	select no_fatg as no_fatg1,no_mesin,no_rangka, kategori,nm_tipe,fk_jenis_barang from viewkendaraan
)as view on no_fatg=no_fatg1
left join tblcustomer on no_cif=fk_cif
left join (
	select fk_sbg as fk_sbg_pay from data_gadai.tblhistory_sbg
	where transaksi='".$transaksi."' and tgl_batal is null
)as tblpayment on fk_sbg_pay=no_sbg
left join (
	select fk_sbg as fk_sbg_ar,referensi as no_batch_ar from data_gadai.tblhistory_sbg
	where transaksi='AR' and tgl_batal is null
)as tblar on fk_sbg_ar=no_sbg
left join(
	select status_barang,no_fatg as no_fatg2 from viewtaksir
)as viewtaksir on no_fatg2=fk_fatg
)as tblmain
";

//showquery($query);

$no_batch_ar=($_REQUEST["no_batch_ar"]);
$kategori=($_REQUEST["kategori"]);


if($_REQUEST["status"]=="Save") {
	cek_error();
	if(!$strmsg){
		save_data();
	}
}

?>
<html>
<head>
	<title>.: <?=$_SESSION["application"]?> :.</title>
	<link href="js/cwcalendar.css.php" rel="stylesheet" type="text/css">
    <link href="css/text.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src="js/alert.js.php"></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src='js/calendar.js.php'></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src='js/input_format_number.js.php'></script>
<!--<script language='javascript' src="js/table_v2.js.php"></script>-->
<script language='javascript' src="js/table_adjustment_memorial.js.php"></script>
<script language='javascript' src="js/validate.js.php"></script>
<script language='javascript'>
function cekError(){
	var lAlerttxt="";
	var lFocuscursor="";

	if(lAlerttxt!=""){
		alert("Error : <br>"+lAlerttxt,function(){eval(lFocuscursor+'.focus()')});
		return false
	} else return true;
}


function fSave(){
	fCount()
	//alert('test')
	//if (cekError()) {
		document.form1.status.value='Save';
		document.form1.submit();
	//}
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")) {
		lInputClose=getObjInputClose();lInputClose.close()
	}
}

function fCount(pFlag='true'){
	var index;
	index =0;
	//confirm(pFlag)
	document.form1.strmenu.value='';
	lObjCount=document.form1.strcek
	var len = document.form1.strcek.length;
	if(len == undefined) len = 1;
	//confirm(lObjCount[0].checked)
	//confirm(lObjCount[1].checked)
	if(len==1){
		if (lObjCount.checked){
			index+=parseFloat(lObjCount.value)
			document.form1.strmenu.value=lObjCount.id+',';
		}
	}else{
		
		for (j=0;j<len;j++){			
			if (lObjCount[j].checked) {
				index+=parseFloat(lObjCount[j].value)
				document.form1.strmenu.value+=lObjCount[j].id+',';
			}
		}	
	}
	document.getElementById('divSelectCount').innerHTML=number_format(index);
	document.form1.total.value=index
	//var r=confirm("Total Data yang Dipilih : "+index);
	
}

function fcheckall(pValue,pIsi){
	
	var x = document.getElementById("myCheck").checked;
	for(var i=0;i<document.form1.strcek.length;i++){
		document.form1.strcek[i].checked=x;
	}
	fCount()
}

function fLoad(){
	
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	}elseif($j_action){
		echo $j_action;
	}else{
		echo "document.form1.btnsimpan.focus();";
	}
?>
}
</script>
<body onLoad="fLoad();document.form1.autocomplete='off'" bgcolor="#fafafa">
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="form1" enctype="multipart/form-data">
<input type="hidden" name="status">
<input type="hidden" name="tujuan" value="<?=$tujuan?>">
<input type="hidden" name="hidden_focus">
<input type="hidden" name="id_menu" value="<?=$id_menu?>">
<input type="hidden" name="total" value="<?=$total?>">
<input type="hidden" name="strmenu" value="<?=$strmenu?>">

<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
                <tr><td class="judul_menu" align="center"><?=strtoupper($nm_menu)?></td></tr>
			</table>
		</td>
	</tr>
    <tr>
      	<td class="border" id="tdContent">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">     
			            
            <tr bgcolor='#efefef'> 
                <td style="padding:0 5 0 5"width="20%" class="fontColor">Jenis Pembayaran</td>
                <td style="padding:0 5 0 5"width="30%" colspan="3">
                <?
				$arr_jenis_pembayaran=array(
				array("value"=>'Pembayaran Unit',"text"=>"Unit"),
				array("value"=>'Pembayaran Fidusia',"text"=>"Fidusia"),
				array("value"=>'Pembayaran Asuransi',"text"=>"Asuransi"),
				array("value"=>'Pencairan Datun',"text"=>"Datun"),
				array("value"=>'Pembayaran TAC Dealer',"text"=>"TAC Dealer"),
				array("value"=>'Pembayaran TAC Kacab',"text"=>"TAC Kacab"),
				array("value"=>'Pembayaran TAC SPV',"text"=>"TAC SPV"),
				array("value"=>'Pembayaran TAC Sales',"text"=>"TAC Sales"),
				array("value"=>'Pembayaran TAC Lain',"text"=>"TAC Lain"),
				);
				?>
                
                
                <select name="jenis_pembayaran" onChange="document.form1.submit()">
                    <option value="" <?=(($jenis_pembayaran == '')?'selected':'') ?>>--Pilih--</option>
					<?
                    foreach ($arr_jenis_pembayaran as $temp){
                    //print_r($temp);
                    ?>
                     <option value="<?=$temp['value']?>" <?= (($jenis_pembayaran==$temp['value'])?"selected":"") ?>><?=$temp["text"]?></option>
                    <?
                    }
                    ?>
                </select>
                </td>
            </tr>
            
            <tr bgcolor='#efefef'> 
                <td style="padding:0 5 0 5"width="20%" class="fontColor">Cabang</td>
                <td style="padding:0 5 0 5"width="30%" colspan="3">
                <? create_list_cabang();?>
                </td>
            </tr>                 

            
            <? if($jenis_pembayaran=='Pembayaran Asuransi' || $jenis_pembayaran=='Pembayaran Fidusia' || strstr($jenis_pembayaran,'Pembayaran TAC') ){?>
            <tr bgcolor='#efefef'> 
                <td style="padding:0 5 0 5"width="20%" class="fontColor">Periode</td>
                <td style="padding:0 5 0 5"width="30%" colspan="3">
                
                <select name="bulan" onChange="document.form1.submit()">
             		<option value="">-- Pilih --</option>
			<? for($i=1;$i<=12;$i++){?>
					<option value="<?=$i?>"<?= (($bulan==$i)?"selected":"") ?>><?=$i?></option>
            <? }?>       
                </select>
                -
                <select name="tahun" onChange="document.form1.submit()">
                	<option value="">-- Pilih --</option>
			<? for($i=-2;$i<2;$i++){ 
					$tahun_pilih=date('Y',strtotime(today_db))+($i);
			?>
					<option value="<?=$tahun_pilih?>"<?= (($tahun==$tahun_pilih)?"selected":"") ?>><?=$tahun_pilih?></option>
            <? }?>       
                </select>
                
                </td>
            </tr>     
            
            <? }?>
            
            <? if($jenis_pembayaran=='Pembayaran Asuransi' || $jenis_pembayaran=='Pembayaran Fidusia' ){?>
            <tr bgcolor='#efefef'> 
                <td style="padding:0 5 0 5"width="20%" class="fontColor">Kategori</td>
                <td style="padding:0 5 0 5"width="30%" colspan="3">
                <select name="kategori" class="groove_text" id="kategori" onChange="document.form1.submit()">
                	<option value="">-- Pilih --</option>
                    <option value="R2" <?=(($kategori=="R2")?"selected":"")?>>R2</option>
                    <option value="R4" <?=(($kategori=="R4")?"selected":"")?>>R4</option>
                </select>               
                </td>
            </tr>     
            
            <? }?>            
            
            <tr bgcolor='#efefef'> 
                <td style="padding:0 5 0 5"width="20%" class="fontColor">Partner</td>
                <td style="padding:0 5 0 5"width="30%" >
                <?
				if($jenis_pembayaran=='Pembayaran Unit'){
					create_list_dealer();
				}
				if($jenis_pembayaran=='Pembayaran Fidusia'){
					create_list_notaris();
				}				
				if($jenis_pembayaran=='Pembayaran Asuransi'){
					create_list_asuransi();
				}
				
				if(strstr($jenis_pembayaran,'TAC') && !(strstr($jenis_pembayaran,'Lain1'))){
					create_list_dealer();	
					if($jenis_pembayaran=='Pembayaran TAC Sales'){
						echo 'Sales : ' ;create_list_sales();
					}	
				}
							
							
				if(strstr($jenis_pembayaran,'Lain1')){
				?>
               	<input type="text" name="tujuan" value="<?=$tujuan?>" (onKeyUp="fNextFocus(event,document.form1.btnsimpan)">
                <?
				}
				?>            
                </td>
                <td style="padding:0 5 0 5"width="20%" class="fontColor">No Rekening</td>
                <td style="padding:0 5 0 5"width="30%">
                <?=$no_rek?>
                </td>                
            </tr>
           
            <tr bgcolor='#efefef'> 
                <td style="padding:0 5 0 5"width="20%" class="fontColor">Kode Bank</td>
                <td style="padding:0 5 0 5"width="30%" >
                <? create_list_bank();?>
                </td>
                <td class="fontColor" style="padding:0 5 0 5" width="20%">Cabang Bayar</td>
                <td style="padding:0 5 0 5" width="30%">
                    <? create_list_cabang_bank();?>
                </td>                
                
            </tr>
            
            <tr bgcolor="efefef">
                <td class="fontColor" style="padding:0 5 0 5" width="20%">Tgl Bayar</td>
                <td style="padding:0 5 0 5" width="30%">
                    <input type="text" value="<?=convert_date_indonesia($tgl_bayar)?>" name="tgl_bayar" maxlength="10" size="8" onKeyUp="fNextFocus(event,document.form1.tgl_bayar)" onChange="fNextFocus(event,document.form1.tgl_bayar)">&nbsp;<img src="images/btn_extend.gif" width="13" height="12" onClick="fPopCalendar(document.form1.tgl_bayar,function(){document.form1.tgl_bayar.focus()})">
                </td>
                <td class="fontColor" style="padding:0 5 0 5" width="20%">No Invoice</td>
                <td style="padding:0 5 0 5" width="30%">
                    <input type="text" name="no_invoice" value="<?=$no_invoice?>" onKeyUp="fNextFocus(event,document.form1.btnsimpan)">
                </td>                
            </tr>
            <tr bgcolor='#efefef'> 
                <td style="padding:0 5 0 5"width="20%" >No Batch AR</td>
                <td style="padding:0 5 0 5"width="30%" colspan="3">
                    <input type="text" name="no_batch_ar" value="<?=$no_batch_ar?>">
				  <input class="groove_button" name="Search" type='button' value='Search' onClick="document.form1.submit()">                    
                </td>
            </tr>
            
		  	</table>
		<? 
		if($jenis_pembayaran)view_data();?>
       
        </td>
    </tr>
    <tr height="20">
        <td height="25" align="center" bgcolor="#D0E4FF" class="border">
            <input class="groove_button" name="btnsimpan" type='button' value='Simpan' onClick="fSave()">
            &nbsp;<input class="groove_button" type="button" value="Batal" onClick="fBatal()"></td>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?

function view_data(){
	global $id_edit,$query,$fk_partner_dealer,$strmenu,$no_batch,$jenis_pembayaran,$total,$fk_partner_notaris,$arr_nominal,$fk_cabang,$tahun,$bulan,$no_batch_ar,$kategori,$fk_karyawan_sales;
	if($fk_cabang != ''){
		$lwhere.=" fk_cabang = '".$fk_cabang."' ";
	}
	
	if($jenis_pembayaran=='Pembayaran Unit'){		
		if($fk_partner_dealer != ''){
			if ($lwhere!="") $lwhere.=" and ";
			$lwhere.=" fk_partner_dealer = '".$fk_partner_dealer."' ";
		}
	}
	
	if($jenis_pembayaran=='Pembayaran Fidusia'){		
		if($fk_partner_notaris != ''){
			if ($lwhere!="") $lwhere.=" and ";
			$lwhere.=" fk_partner_notaris = '".$fk_partner_notaris."' ";
		}	
		if($kategori !='' ){
			if ($lwhere!="") $lwhere.=" and ";
			$lwhere.=" kategori = '".$kategori."' ";
		}
		
		
		if($bulan=='' || $tahun==''){
			$view='f';
		}else{
			if ($lwhere!="") $lwhere.=" and ";
			$fom=$tahun.'-'.$bulan.'-01 00:00:00';
			$eom=$tahun.'-'.$bulan.'-'.date('t',strtotime($fom)).' 23:59:59';
			$lwhere.=" tgl_pengajuan>='".$fom."' and tgl_pengajuan <='".$eom."'";
		}
	}
	
	if($jenis_pembayaran=='Pembayaran Asuransi' ){		
		if($fk_partner_asuransi != ''){
			if ($lwhere!="") $lwhere.=" and ";
			$lwhere.=" fk_partner_asuransi = '".$fk_partner_asuransi."' ";
		}
		
		if($kategori !='' ){
			if ($lwhere!="") $lwhere.=" and ";
			$lwhere.=" kategori = '".$kategori."' ";
		}
				
		//$eoy='12/01/'.$tahun;
		//$lwhere.=" and ('".$eoy."' between tgl_cair and tgl_jatuh_tempo)  ";
		
		if($bulan=='' || $tahun==''){
			$view='f';
		}else{
			if ($lwhere!="") $lwhere.=" and ";
			$lwhere.=" extract(month from tgl_pengajuan) = '".$bulan."' ";
			$lwhere.=" and no_sbg||'Pembayaran Asuransi '||tahun_ke not in(select fk_sbg||transaksi from data_gadai.tblhistory_sbg where tgl_batal is null)";
		}
	}	
	
	if(strstr($jenis_pembayaran,'Pembayaran TAC')){
		if($fk_partner_dealer != ''){
			if ($lwhere!="") $lwhere.=" and ";
			$lwhere.=" fk_partner_dealer = '".$fk_partner_dealer."' ";
			$lwhere2.=" or(status_approval='Approve' and fk_partner_dealer like 'CM-%' ) ";//khusus dealer CM- boleh TAC sblum batching
		}	
		if($fk_karyawan_sales != ''){
			if ($lwhere!="") $lwhere.=" and ";
			$lwhere.=" fk_karyawan_sales = '".$fk_karyawan_sales."' ";
		}		
		
		if($bulan=='' || $tahun==''){
			$view='f';
		}else{
			if ($lwhere!="") $lwhere.=" and ";
			$fom=$tahun.'-'.$bulan.'-01 00:00:00';
			$eom=$tahun.'-'.$bulan.'-'.date('t',strtotime($fom)).' 23:59:59';
			$lwhere.=" tgl_pengajuan>='".$fom."' and tgl_pengajuan <='".$eom."'";
			
		}
	}
	
	if($no_batch_ar != ''){
		if ($lwhere!="") $lwhere.=" and ";
		$lwhere.=" no_batch_ar = '".$no_batch_ar."' ";
	}
	
	
	if ($lwhere!="") $lwhere=" and ".$lwhere;
	
	if($view!='f'){
		
	$query1=$query." 
where status_approval ='Approve' and 
fk_sbg_pay is null and (tgl_cair is not null ".$lwhere2." )
--and no_sbg='40103230200024'
".$lwhere." order by tgl_cair
--limit 100
	";	
	//showquery($query1);	
	$lrs=pg_query($query1);
?>

    <table cellpadding="0" cellspacing="1" border="0" width="100%">
        <tr style="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
            <td align="center">Partner</td>
        	<td align="center">No Kontrak</td>
        	<td align="center">Tgl Kontrak</td>
            
            <td align="center">Nama Customer</td>
            <td align="center">Tipe</td>  
            <td align="center">No Mesin</td>  
            <td align="center">No Rangka</td>  
        	<td align="center">Nominal</td>
        	<td align="center"><input type="checkbox" name="select_list" id="myCheck" class="groove_checkbox"  onclick="fcheckall(this)"></td>
<?			if($jenis_pembayaran=='Pembayaran Asuransi'){?>
			<td align="center">Tahun Ke</td>
<?			}?>      
<?			if($jenis_pembayaran=='Pembayaran Fidusia' ||strstr($jenis_pembayaran,'Pembayaran TAC')){?>
			<!--<td align="center">PPH 21</td>-->
<?			}?>            
      
        </tr>
<?	
	$lrs_setting=(pg_query("select * from tblsetting"));
	$lsetting=pg_fetch_array($lrs_setting);
	$rate_ppn=$lsetting["rate_ppn"];

	while($lrow=pg_fetch_array($lrs)){	
		//$fk_cabang=$lrow["fk_cabang"];
		$data='t';
		$nominal=0;
		$kategori=$lrow["kategori"];
		if($jenis_pembayaran=='Pembayaran Unit'){
			$nominal=$lrow["nilai_ap_customer"];	
			$fk_partner=$lrow["fk_partner_dealer"];
		}elseif($jenis_pembayaran=='Pembayaran Fidusia'){					
			$nominal=$lrow["biaya_notaris"];				
			$fk_partner=$lrow["fk_partner_notaris"];			
			
			$arr=calc_fidusia($nominal,$fk_partner,$bulan,$tahun,$lrow["total_hutang"]);	
			
			$pph21=$arr["pph21"];
			$utang=$arr["utang"];
			$nominal=$arr["nominal"];
		}elseif($jenis_pembayaran=='Pembayaran Asuransi'){
			//echo $lrow["no_sbg"].'<br>';
			$fk_partner=$lrow["fk_partner_asuransi"];
			$tahun_ke=($tahun-date("Y",strtotime($lrow["tgl_pengajuan"])))+1;
			//showquery("select * from data_gadai.tblhistory_sbg where transaksi='".$jenis_pembayaran." ".$tahun_ke."' and tgl_batal is null and fk_sbg='".$lrow["no_sbg"]."'");
			//echo date("H:i:s").'-a<br>';
			if(pg_num_rows(pg_query("select * from data_gadai.tblhistory_sbg where transaksi='".$jenis_pembayaran." ".$tahun_ke."' and tgl_batal is null and fk_sbg='".$lrow["no_sbg"]."'"))){
				$data='f';
			}else{
				$nominal=calc_asuransi($lrow["no_sbg"],$tahun_ke,'t');		
			}
			//echo date("H:i:s").'-b<br>';
			$utang=$nominal;

			$asuransi=calc_asuransi_nett($lrow["no_sbg"],$nominal);
			$komisi=$asuransi['komisi'];
			$ppn=$asuransi['ppn'];
			$pph23=$asuransi['pph23'];
			$nominal=$asuransi['nominal'];
									
		}elseif($jenis_pembayaran=='Pencairan Datun'){
			$fk_partner='DTN';
			$nominal=$lrow["nilai_ap_customer"];	
		}elseif(strstr($jenis_pembayaran,'Pembayaran TAC')){	
			$fk_partner=$lrow["fk_partner_dealer"];
		
			$jenis_tac=str_replace("Pembayaran TAC ","",$jenis_pembayaran);
			$tac=calc_tac($lrow["no_sbg"],$jenis_tac);
			
			$beban=$tac['beban'];
			$pph21=$tac['pph21'];
			$pph23=$tac['pph23'];
			$komisi=$tac['komisi'];			
			$nominal=$tac['nominal'];
		}
		
		if($nominal==0)$data='f';
		
		$nominal=($nominal);
		//echo $nominal;
		$arr_nominal[$lrow["no_sbg"]]=$nominal;
		
		$nm_partner=get_rec("tblpartner","nm_partner","kd_partner='".$fk_partner."'");

		if($data=='t'){
?>                 
			<input type="hidden" name="beban[<?=$lrow["no_sbg"]?>]" value="<?=$beban?>"> 
            <input type="hidden" name="utang[<?=$lrow["no_sbg"]?>]" value="<?=$utang?>"> 
            <input type="hidden" name="pph21[<?=$lrow["no_sbg"]?>]" value="<?=$pph21?>">
            <input type="hidden" name="pph23[<?=$lrow["no_sbg"]?>]" value="<?=$pph23?>">
            <input type="hidden" name="ppn[<?=$lrow["no_sbg"]?>]" value="<?=$ppn?>">
            <input type="hidden" name="komisi[<?=$lrow["no_sbg"]?>]" value="<?=$komisi?>">
            <input type="hidden" name="tahun_ke[<?=$lrow["no_sbg"]?>]" value="<?=$tahun_ke?>">            
            <input type="hidden" name="nominal[<?=$lrow["no_sbg"]?>]" value="<?=$nominal?>">
            <tr style="padding:0 5 0 5" height="20" bgcolor='#efefef'>
                <td style="padding:0 5 0 5" class="" align="left" width="150"><?=$nm_partner?></td>
                <td style="padding:0 5 0 5" class="" align="left"><?=$lrow["no_sbg"]?></td>
                <td style="padding:0 5 0 5" class="" align="left"><?=date("d/m/Y",strtotime($lrow["tgl_pengajuan"]))?></td>
                <td style="padding:0 5 0 5" class="" align="left" width="100"><?=$lrow["nm_customer"]?></td>
                <td style="padding:0 5 0 5" class="" align="left"><?=$lrow["nm_tipe"]?></td>
                <td style="padding:0 5 0 5" class="" align="left"><?=$lrow["no_mesin"]?></td>
                <td style="padding:0 5 0 5" class="" align="left"><?=$lrow["no_rangka"]?></td>
                <td style="padding:0 5 0 5" class="" align="right"><?=convert_money("",$nominal,2)?></td>	
                <td style="padding:0 5 0 5"align="center"><input type="checkbox" name="strcek" id=<?=$lrow["no_sbg"]?> value="<?=$nominal?>" <?=((strstr($strmenu,$lrow["no_sbg"]))?"checked":"")?>  onclick="fCount()"></td>            
    <?		if($jenis_pembayaran=='Pembayaran Asuransi'){?>
                <td style="padding:0 5 0 5" class="" align="right"><?=$tahun_ke?></td>
    <?		}?>
	<?		if($jenis_pembayaran=='Pembayaran Fidusia' ||strstr($jenis_pembayaran,'Pembayaran TAC')){?>
<!--				<td style="padding:0 5 0 5" class="" align="right"><?=convert_money("",$pph21,0)?></td>
-->	<?		}?> 
               
            </tr>                  
    <?	}
	}?>

		<tr tyle="padding:0 5 0 5" height="20" bgcolor='#C8C8C8' class="judul">
            <td style="padding:0 5 0 5" align="center" colspan="7">Total</td>
            <td style="padding:0 5 0 5" align="right">
            <span id="divSelectCount"><?=convert_money("",$total)?></span>
            </td>
            <td style="padding:0 5 0 5" align="center" ></td>
<?		if($jenis_pembayaran=='Pembayaran Asuransi'){?>
			<td style="padding:0 5 0 5" align="center" ></td>
<?		}?>            
        </tr>
    </table>

<?	
	}
}

function cek_error(){
	global $strmsg,$j_action,$l_arr_row,$tr_date,$jenis_pembayaran,$total,$fk_partner_dealer,$no_batch,$fk_cabang,$fk_bank,$fk_partner_notaris,$fk_partner_asuransi,$strmenu,$tgl_bayar,$no_invoice,$tujuan;

	if($jenis_pembayaran=="" ){
		$strmsg.='Jenis Pembayaran harus dipilih <br>';
		if(!$j_action) $j_action="document.form1.jenis_pembayaran.focus()";
	}

	//echo $total;
	if($total=="" || $total=="0"){
		$strmsg.='Tidak ada yang dipilih<br>';
	}
	
	
	if($fk_cabang=="" ){
		if($jenis_pembayaran!='Pembayaran Asuransi'){
			$strmsg.='Cabang harus dipilih <br>';
			if(!$j_action) $j_action="document.form1.fk_cabang.focus()";
		}
	}
	
	if($fk_partner_dealer=="" && $jenis_pembayaran=='Pembayaran Unit'){
		$strmsg.='Partner harus dipilih <br>';
		if(!$j_action) $j_action="document.form1.fk_partner_dealer.focus()";
	}
	
	if($fk_partner_notaris=="" && $jenis_pembayaran=='Pembayaran Fidusia'){
		$strmsg.='Partner harus dipilih <br>';
		if(!$j_action) $j_action="document.form1.fk_partner_notaris.focus()";
	}
	
	if($fk_partner_asuransi=="" && $jenis_pembayaran=='Pembayaran Asuransi'){
		$strmsg.='Partner harus dipilih <br>';
		if(!$j_action) $j_action="document.form1.fk_partner_asuransi.focus()";
	}		
	
	if($tujuan=="" && strstr($jenis_pembayaran,'Pembayaran TAC')){
		$strmsg.='Tujuan harus diisi <br>';
	}
	
	if($fk_bank=="" ){
		$strmsg.='Bank harus dipilih <br>';
		if(!$j_action) $j_action="document.form1.fk_bank.focus()";
	}	
	if($tgl_bayar=="" ){
		$strmsg.='Tgl Bayar harus dipilih <br>';
		if(!$j_action) $j_action="document.form1.tgl_bayar.focus()";
	}
	if($no_invoice=="" ){
		$strmsg.='No Invoice harus diisi <br>';
		if(!$j_action) $j_action="document.form1.no_invoice.focus()";
	}
	
	$l_arr_row = split(',',$strmenu);
	$jml_data=count($l_arr_row)-1;
	if($jenis_pembayaran=='Pencairan Datun' && $jml_data>1){
		$strmsg.='Pencairan Datun hanya bisa 1 per BPB <br>';
	}

	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}


function save_data(){
	global $strmsg,$j_action,$tujuan,$jenis_pembayaran,$strmenu,$keterangan,$fk_bank,$fk_cabang_bank,$query,$no_batch,$arr_nominal,$tahun,$bulan,$tgl_bayar,$no_invoice;
	$l_success=1;
	pg_query("BEGIN");
	
	if(!$_SESSION["kd_cabang"]){
		$l_success=0;
		$strmsg.='Session Habis. silakan login ulang<br>';
	}	
	
	$lrs_setting=(pg_query("select * from tblsetting"));
	$lsetting=pg_fetch_array($lrs_setting);
	$rate_ppn=$lsetting["rate_ppn"];
	
	$type_owner='PAYMENT';
			
	$query_serial="select nextserial_cabang('BPB':: text,'".$_SESSION["kd_cabang"]."')";
	$lrow_serial=pg_fetch_array(pg_query($query_serial));
	$no_batch=$lrow_serial["nextserial_cabang"];	
	//echo $no_batch;	
	
	
	if(!pg_query("insert into data_fa.tblbatch_payment (no_batch,tgl_batch,fk_partner,fk_bank,fk_cabang,rate_ppn,no_invoice,bulan,tahun)values('".$no_batch."','".$tgl_bayar."','".$tujuan."','".$fk_bank."','".$fk_cabang_bank."','".$rate_ppn."','".$no_invoice."',".($bulan==""?"NULL":"'".$bulan."'").",".($tahun==""?"NULL":"'".$tahun."'").")")) $l_success=0;		
	//showquery("insert into data_fa.tblbatch_payment (no_batch,tgl_batch,fk_partner,fk_bank,fk_cabang,rate_ppn,no_invoice,bulan,tahun)values('".$no_batch."','".$tgl_bayar."','".$tujuan."','".$fk_bank."','".$fk_cabang_ho."','".$rate_ppn."','".$no_invoice."',".($bulan==""?"NULL":"'".$bulan."'").",".($tahun==""?"NULL":"'".$tahun."'").")");

	
	$total=0;
	$l_arr_row = split(',',$strmenu);
	//print_r($l_arr_row);
	for ($i=0; $i<count($l_arr_row)-1; $i++){
		$query1=$query." where no_sbg='".$l_arr_row[$i]."'";
		$lrs=pg_query($query1);
		$lrow=pg_fetch_array($lrs);
				
		$id_edit=$l_arr_row[$i];
		$fk_cabang=$lrow["fk_cabang"];	
		$kategori=$lrow["kategori"];
		$kategori=strtolower($kategori);
		
		$nominal=$_REQUEST["nominal"][$lrow["no_sbg"]];
		if(round($nominal)==0){
			$strmsg.='Nominal Tagihan '.$lrow["no_sbg"].' masih 0<br>';
			$l_success=0;
		}
		
		$transaksi=$jenis_pembayaran;
		
		if($jenis_pembayaran=='Pembayaran Asuransi'){
			$tahun_ke=$_REQUEST["tahun_ke"][$lrow["no_sbg"]];
			$transaksi=$transaksi.' '.$tahun_ke;
			if($tahun_ke==1){	
				$asr[$fk_cabang][$kategori]+=$_REQUEST["utang"][$lrow["no_sbg"]];
			}else{
				$aro[$fk_cabang][$kategori]+=$_REQUEST["utang"][$lrow["no_sbg"]];
			}
			$pph23[$fk_cabang]+=$_REQUEST["pph23"][$lrow["no_sbg"]];
			$ppn[$fk_cabang]+=$_REQUEST["ppn"][$lrow["no_sbg"]];
			$komisi[$fk_cabang][$kategori]+=$_REQUEST["komisi"][$lrow["no_sbg"]];
		}
		
		if($jenis_pembayaran=='Pembayaran Fidusia'){
			$fidusia[$kategori]+=$_REQUEST["utang"][$lrow["no_sbg"]];
			$pph21+=$_REQUEST["pph21"][$lrow["no_sbg"]];
		}
		
		if($jenis_pembayaran=='Pencairan Datun'){
			$tujuan=$lrow["fk_cif"];
			$type_owner='PENCAIRAN';
		}
		
		if(strstr($jenis_pembayaran,'Pembayaran TAC')){		
			if(date("n",strtotime($tgl_bayar))==date("n",strtotime($lrow["tgl_cair"]))){
				$is_beban='t';
			}
			$beban[$kategori]+=$_REQUEST["beban"][$lrow["no_sbg"]];		
			
			$komisi[$kategori]+=$_REQUEST["komisi"][$lrow["no_sbg"]];	
			$pph21+=$_REQUEST["pph21"][$lrow["no_sbg"]];	
			$pph23+=$_REQUEST["pph23"][$lrow["no_sbg"]];	
		}		
		
		if(pg_num_rows(pg_query("select * from data_gadai.tblhistory_sbg where transaksi='".$transaksi."' and fk_sbg='".$id_edit."' and tgl_batal is null"))){
			$strmsg.='No '.$transaksi.' '.$id_edit.' sudah masuk<br>';
			$l_success=0;
		}		
		
		$p_arr=array(
			'tgl_data'=>$tgl_bayar,
			'fk_sbg'=>$id_edit,
			'referensi'=>$no_batch,
			'ang_ke'=>'0',
			'transaksi'=>$transaksi,
			'nilai_bayar'=>$nominal,
		);
		//showquery(insert_history($p_arr));	
		if(!pg_query(insert_history($p_arr))) $l_success=0;			
		
		//showquery("select * from data_gadai.tblhistory_sbg where transaksi='".$transaksi."' and fk_sbg='".$id_edit."' and tgl_batal is null");
		
		$total+=$nominal;	
		$total_cabang[$fk_cabang]+=$nominal;
		
		$fk_cabang_ho=(get_cabang_pt($fk_cabang));
		
	}
	
		
	if(!pg_query(update_saldo_bank($fk_bank,$fk_cabang_ho,0,$total,$jenis_pembayaran,$no_batch)))$l_success=0;
	//showquery(update_saldo_bank($fk_bank,cabang_ho,0,$total,$jenis_pembayaran,$no_batch));
	
	$kd_partner	 = $tujuan;	
	$nm_partner  = get_rec("(select kd_partner,nm_partner from tblpartner union select nik,nm_karyawan from tblkaryawan_dealer)as tbl","nm_partner","kd_partner='".$kd_partner."'");
	$coa_bank 	 = get_coa_bank($fk_bank,$fk_cabang_bank);
	
	
	$reference=$jenis_pembayaran.' kepada '.($nm_partner?$nm_partner:$tujuan);
	//echo $reference;
	
	
	if($fk_cabang_bank==cabang_ho){
		$arrPost = array();
		$arrPost["bank"]	= array('type'=>'c','value'=>$total,'account'=>$coa_bank);
		foreach($total_cabang as $fk_cabang =>$total){
			$rpkc 		 = get_coa_cabang($fk_cabang,$fk_cabang_bank);
			$arrPost["rpkc".$fk_cabang]	= array('type'=>'d','value'=>$total,'account'=>$rpkc);
		}
		foreach($arrPost as $index=>$temp){
			$arrPost[$index]['reference'] =$reference;//tambah keterangan disemua arrpost
		}	
		//cek_balance_array_post($arrPost);
		if(!posting($type_owner,$no_batch,$tgl_bayar,$arrPost,$fk_cabang_ho,'00'))$l_success=0;		
	}
		
		
	foreach($total_cabang as $fk_cabang =>$total){
		$arrPost = array();
		if($fk_cabang_bank==cabang_ho){
			$arrPost["rpkp"]	= array('type'=>'c','value'=>$total);	
		}else{
			$arrPost["bank"]	= array('type'=>'c','value'=>$total,'account'=>$coa_bank);
		}
		
		if($jenis_pembayaran=='Pembayaran Unit'){
			$coa_dealer=$fk_cabang.'.'.get_rec("tblpartner","fk_coa_dealer","kd_partner='".$kd_partner."'");
			$arrPost["utang"]	= array('type'=>'d','value'=>$total,'account'=>$coa_dealer);			
		}
		
		if($jenis_pembayaran=='Pembayaran Asuransi'){
			$i=1;
			if(count($asr[$fk_cabang])>0){
				foreach($asr[$fk_cabang] as $kategori =>$total1){
					$coa_asr=$fk_cabang.'.'.get_rec("tblpartner","fk_coa_asr_".$kategori,"kd_partner='".$kd_partner."'");				
					$arrPost["utang_asr".$i] = array('type'=>'d','value'=>$total1,'account'=>$coa_asr);
					$i++;
				}	
			}
			if(count($aro[$fk_cabang])>0){
				foreach($aro[$fk_cabang] as $kategori =>$total1){
					$arrPost["utang_aro_".$kategori]		= array('type'=>'d','value'=>$total1);
				}
			}
			foreach($komisi[$fk_cabang] as $kategori =>$total1){
				$arrPost["pend_komisi_asr_".$kategori]	= array('type'=>'c','value'=>$total1);
			}
			
			$arrPost["pph23"]				= array('type'=>'d','value'=>$pph23[$fk_cabang]);
			$arrPost["ppn_keluaran"]		= array('type'=>'c','value'=>$ppn[$fk_cabang]);
		}
		
		if($jenis_pembayaran=='Pembayaran Fidusia'){
			foreach($fidusia as $kategori =>$total1){
				$arrPost["utang_fidusia_".$kategori]	= array('type'=>'d','value'=>$total1);
				$i++;
			}	
			$arrPost["pph21"]				= array('type'=>'c','value'=>$pph21);
		}
		
		if($jenis_pembayaran=='Pencairan Datun'){
			$arrPost["utang_usaha"]			= array('type'=>'d','value'=>$total);			
		}
		
		if(strstr($jenis_pembayaran,'Pembayaran TAC')){	
			foreach($beban as $kategori =>$total1){								
				// if($kategori =='r2'){
				// 	if($is_beban=='t'){
				// 		$arrPost["beban_insentif_".$kategori]= array('type'=>'d','value'=>$total1);
				// 	}else {
				// 		$arrPost["utang_insentif_".$kategori]= array('type'=>'d','value'=>$total1);	
				// 	}
				// }

				if($jenis_pembayaran == 'Pembayaran TAC Dealer' || $jenis_pembayaran == 'Pembayaran TAC Lain'){
					if($is_beban=='t'){
						$arrPost["beban_insentif_".$kategori]= array('type'=>'d','value'=>$total1);
					}else {
						$arrPost["utang_insentif_".$kategori]= array('type'=>'d','value'=>$total1);	
					}
				}
				
				if($kategori=='r2'){
					if($komisi[$kategori]>0){
						$arrPost["komisi_penj_".$kategori]	 = array('type'=>'d','value'=>$komisi[$kategori]);
					}
				} else{
					if($komisi[$kategori]>0){
						$arrPost["komisi_penj_".$kategori]	 = array('type'=>'d','value'=>$beban[$kategori]);
					}
				}
				$i++;
			}	
			if($pph23>0){
				$arrPost["utang_pph23"]			= array('type'=>'c','value'=>($pph23));
			}
			if($pph21>0){
				$arrPost["pph21"]				= array('type'=>'c','value'=>$pph21);
			}
		}
		
		foreach($arrPost as $index=>$temp){
			$arrPost[$index]['reference'] =$reference;//tambah keterangan disemua arrpost
		}
		
		//echo $type_owner;		
		if(!posting($type_owner,$no_batch,$tgl_bayar,$arrPost,$fk_cabang,'00'))$l_success=0;
		if($l_success==0)cek_balance_array_post($arrPost);
	}
	
	//echo $l_success;
	//$l_success=0;
	if($l_success==1) {
		$strmsg = "Data saved. BPB :".$no_batch."<br>";
		$j_action="lInputClose=getObjInputClose();lInputClose.close()";
		//pg_query("ROLLBACK");
		pg_query("COMMIT");
	} else {
		$strmsg .= "Error :<br>Data save failed.<br>";
	    pg_query("ROLLBACK");
	}	
}	
//if($jenis_pembayaran=='Pembayaran Fidusia'){			
	//if(!pg_query(insert_log("data_gadai.tblproduk_cicilan","no_sbg = '".$id_edit."'",'UB')))$l_success=0;		
	//if(!pg_query("update data_gadai.tblproduk_cicilan set biaya_notaris='".$nominal."' where no_sbg='".$id_edit."'")) $l_success=0;
	//if(!pg_query(insert_log("data_gadai.tblproduk_cicilan","no_sbg = '".$id_edit."'",'UA')))$l_success=0;			
//}

function create_list_bank(){
    global $fk_bank,$fk_cabang_bank;
	//showquery("select * from tblcabang_detail_bank left join tblbank on fk_bank=kd_bank left join (select description, coa from tbltemplate_coa)as tblcoa on fk_coa=coa where fk_cabang='".$fk_cabang_bank."'  and fk_bank<=90");
    $l_list_obj = new select("select * from tblcabang_detail_bank left join tblbank on fk_bank=kd_bank left join (select description, coa from tbltemplate_coa)as tblcoa on fk_coa=coa where fk_cabang='".$fk_cabang_bank."'  and fk_bank<=90","description","fk_bank","fk_bank");
    $l_list_obj->add_item("-- Pilih ---",'',0);
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:12px;' onKeyUp='fNextFocus(event,document.getElementById(\"login\"))'");
}

function create_list_dealer(){
    global $fk_partner_dealer;
	
    $l_list_obj = new select("select * from tblpartner where fk_tipe_partner='DEALER' and partner_active ='t' order by nm_partner","nm_partner","kd_partner","fk_partner_dealer");
    $l_list_obj->add_item("-- Pilih ---",'',0);
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:12px;' onKeyUp='fNextFocus(event,document.getElementById(\"login\"))'","form1","document.form1.submit()");
}

function create_list_notaris(){
    global $fk_partner_notaris;
	
    $l_list_obj = new select("select * from tblpartner where fk_tipe_partner='NOTARIS' and partner_active ='t' order by nm_partner","nm_partner","kd_partner","fk_partner_notaris");
    $l_list_obj->add_item("-- Pilih ---",'',0);
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:12px;' onKeyUp='fNextFocus(event,document.getElementById(\"login\"))'","form1","document.form1.submit()");
}

function create_list_asuransi(){
    global $fk_partner_asuransi;
	
    $l_list_obj = new select("select * from tblpartner where fk_tipe_partner='ASURANSI' and partner_active ='t' order by nm_partner","nm_partner","kd_partner","fk_partner_asuransi");
    $l_list_obj->add_item("-- Pilih ---",'',0);
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:12px;' onKeyUp='fNextFocus(event,document.getElementById(\"login\"))'","form1","document.form1.submit()");
}

function create_list_cabang(){
    global $fk_cabang;
	
    $l_list_obj = new select("select * from tblcabang where cabang_active ='t' and kd_cabang not in('000')order by kd_cabang","nm_cabang","kd_cabang","fk_cabang");
    $l_list_obj->add_item("-- Cabang ---",'',0);
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:12px;' onKeyUp='fNextFocus(event,document.getElementById(\"login\"))'","form1","document.form1.submit()");
}

function create_list_cabang_bank(){
    global $fk_cabang_bank,$fk_cabang;
	
    $l_list_obj = new select("select * from tblcabang where cabang_active ='t' and kd_cabang in('000','".$fk_cabang."')order by kd_cabang","nm_cabang","kd_cabang","fk_cabang_bank");
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:12px;' onKeyUp='fNextFocus(event,document.getElementById(\"login\"))'","form1","document.form1.submit()");
}



function create_list_sales(){
    global $fk_karyawan_sales,$bulan,$tahun,$fk_cabang,$fk_partner_dealer;

//showquery("select distinct on (fk_karyawan_sales)fk_karyawan_sales,nm_karyawan from data_gadai.tbltaksir_umum inner join tblkaryawan_dealer on fk_karyawan_sales=nik inner join tblinventory on fk_sbg=no_sbg_ar where extract(month from tgl_cair)='".$bulan."' and extract(year from tgl_cair)='".$tahun."' ".($fk_cabang?"and tblinventory.fk_cabang='".$fk_cabang."'":"")." ".($fk_partner_dealer?"and fk_partner_dealer='".$fk_partner_dealer."'":"")."");
    $l_list_obj = new select("select distinct on (fk_karyawan_sales)fk_karyawan_sales,nm_karyawan from data_gadai.tbltaksir_umum inner join tblkaryawan_dealer on fk_karyawan_sales=nik inner join tblinventory on fk_sbg=no_sbg_ar where extract(month from tgl_cair)='".$bulan."' and extract(year from tgl_cair)='".$tahun."' ".($fk_cabang?"and tblinventory.fk_cabang='".$fk_cabang."'":"")." ".($fk_partner_dealer?"and fk_partner_dealer='".$fk_partner_dealer."'":"")."","nm_karyawan","fk_karyawan_sales","fk_karyawan_sales");
    $l_list_obj->add_item("-- Pilih ---",'',0);
    $l_list_obj->html("class='groove_text' style='background-color:#ffffff;border-color:#999999;font-size:12px;' onKeyUp='fNextFocus(event,document.getElementById(\"login\"))'","form1","document.form1.submit()");
}


function get_data_module(){
	global $kd_module,$j_action,$nm_tabs;
	
	//query untuk memunculkan tab 
	$lrow_tab_switch=pg_fetch_array(pg_query("select * from skeleton.tblmodule_tabs where fk_module='".$kd_module."' order by no_urut_tabs limit 1"));
	$nm_tabs=$lrow_tab_switch["kd_tabs"];
	$lrow_first_field=pg_fetch_array(pg_query("select * from skeleton.tblmodule_fields where fk_module='".$kd_module."' order by no_urut_add limit 1"));
	$j_action="document.form1.".$lrow_first_field["kd_field"].".focus();";
}


/*

<!--                <option value="Pembayaran Unit"<?= (($jenis_pembayaran=='Pembayaran Unit')?"selected":"") ?>>Unit</option>
                    <option value="Pembayaran Fidusia"<?= (($jenis_pembayaran=='Pembayaran Fidusia')?"selected":"") ?>>Fidusia</option>
                    <option value="Pembayaran Asuransi"<?= (($jenis_pembayaran=='Pembayaran Asuransi')?"selected":"") ?>>Asuransi</option>
                    <option value="Pencairan Datun"<?= (($jenis_pembayaran=='Pencairan Datun')?"selected":"") ?>>Datun</option>
                    <option value="Pembayaran TAC Dealer"<?= (($jenis_pembayaran=='Pembayaran TAC Dealer')?"selected":"") ?>>TAC Dealer</option>
                    <option value="Pembayaran TAC Kacab"<?= (($jenis_pembayaran=='Pembayaran TAC Kacab')?"selected":"") ?>>TAC Kacab</option>
                    <option value="Pembayaran TAC SPV"<?= (($jenis_pembayaran=='Pembayaran TAC SPV')?"selected":"") ?>>TAC SPV</option>
                    <option value="Pembayaran TAC Sales"<?= (($jenis_pembayaran=='Pembayaran TAC Sales')?"selected":"") ?>>TAC Sales</option>
                    <option value="Pembayaran TAC Lain"<?= (($jenis_pembayaran=='Pembayaran TAC Lain')?"selected":"") ?>>TAC Lain</option>-->

*/

?>
		

	

