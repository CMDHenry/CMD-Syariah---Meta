<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/numeric.inc.php';
require 'requires/db_utility.inc.php';
require 'classes/select.class.php';

$id_edit=$_REQUEST["id_edit"];
/*if ($id_edit=="") check_right("HO1010131310");
else check_right("HO1010131311");
*/
$kd_group_karyawan=trim($_REQUEST["kd_group_karyawan"]);
$nm_group_karyawan=trim($_REQUEST["nm_group_karyawan"]);
$nik=trim($_REQUEST["nik"]);
$nm_cabang=trim($_REQUEST["nm_cabang"]);
$fk_cabang=trim($_REQUEST["fk_cabang"]);
$nm_depan=trim($_REQUEST["nm_depan"]);
$strisi=trim($_REQUEST["strisi"]);

if($_REQUEST["status_action"]=="Save") {
	cek_error();
	if(!$strmsg){
		if ($id_edit) edit_data();
		else add_data();
	}
}

if($_REQUEST["pstatus"]){
	get_data();
}
?>
<html>
<head>
	<title>.: JUJUR JAYA :.</title>
    <link href="css/text.css.php" rel="stylesheet" type="text/css">
	<link href="css/cwcalendar.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src="js/alert.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src="js/table_v2.js.php"></script>
<script language='javascript' src="js/table_group.js.php"></script>
<script language='javascript' src='js/calendar.js.php'></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<!--<script language='javascript' src="js/cek_group.js.php"></script>
--><script language='javascript' src='js/validate.js.php'></script>
<script language='javascript'>
var tanggal=/^\d{1,2}\/\d{1,2}\/\d{4}$/g;

function fGetCabang(){
	fGetNC(false,'20170900000044','kd_partner','Ganti Cabang',document.form1.fk_cabang,document.form1.strisi)
	if (document.form1.nik.value !="")fGetCabangData()
}

function fGetCabangData(){
	document.getElementById("divNmCabang").innerHTML=""
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataCabangState
	lSentText="table=tblpartner&field=nm_partner&key=kd_partner&value="+document.form1.fk_cabang.value
	lObjLoad.open("POST","ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}

function fGetDataCabangState(){
	if (this.readyState == 4){
		if (this.status==200 && this.responseText!="") {
			document.getElementById("divNmCabang").innerHTML=this.responseText
			document.form1.nm_cabang.value=this.responseText
		} else {
			document.getElementById("divNmCabang").innerHTML="-"
			document.form1.nm_depan.value=""
		}
	}
}

function fGetKaryawan(){
	fGetNC(false,'20210900000017','nik','Ganti Karyawan',document.form1.nik,document.form1.strisi)
	if (document.form1.nik.value !="")fGetKaryawanData()	
}

function fGetKaryawanData(){
	document.getElementById("divNmKaryawan").innerHTML=""
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fGetDataKaryawanState
	lSentText="table=tblkaryawan_dealer&field=(nm_karyawan)&key=nik&value="+document.form1.nik.value
	lObjLoad.open("POST","ajax/get_data.php",true);
	lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
}

function fGetDataKaryawanState(){
	if (this.readyState == 4){
		if (this.status==200 && this.responseText!="") {
			ltext = convert_data(this.responseText)
			document.getElementById("divNmKaryawan").innerHTML=stripquote(ltext[0])+" "+stripquote(ltext[1])
			document.form1.nm_depan.value=stripquote(ltext[0])+" "+stripquote(ltext[1])
		} else {
			document.getElementById("divNmKaryawan").innerHTML="-"
			document.form1.nm_depan.value="-"
		}
	}
}

function fBatal(){
	if (confirm("Apakah anda yakin ingin membatalkan penginputan data ?")) {
		lInputClose=getObjInputClose();lInputClose.close()
	}
}

function fSave(){
	//if (cekError()) {
		document.form1.status_action.value='Save';
		document.form1.strisi.value=table1.getIsi();
		if(document.form1.strisi.value!="false")document.form1.submit();
	//}
}

function fLoad(){
	table1.setIsi(document.form1.strisi.value);
	//parent.parent.document.title="Group";
<?
	if ($strmsg){
		echo 'alert("'.$strmsg.'",function (){'.$j_action.'});';
	}
	else if($j_action){
		echo $j_action;
	}else{
		echo "document.form1.nm_group_karyawan.focus();";
	}
?>
}
</script>
<body onLoad="fLoad();document.form1.autocomplete='off'" bgcolor="#fafafa">
<form name="form1" action="modal_group.php" method="post">
<input type="hidden" name="id_edit" value="<?=$id_edit?>">
<input type="hidden" name="strisi" value="<?=$strisi?>">
<input type="hidden" name="status_action">
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<td align="center" class="judul_menu">TEAM MARKETING</td>
			</table>		
		</td>
	</tr>
    <tr>
      <td class="border">
<!-- content begin -->
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<?
				if ($id_edit!="") {
				?>
				<tr bgcolor="efefef">
					<td style="padding:0 5 0 5" width="20%" class="fontColor">Kode Team Marketing </td>
					<td style="padding:0 5 0 5" width="30%" ><?=convert_html($id_edit)?></td>
					<td width="20%" style="padding:0 5 0 5">&nbsp;</td>
					<td width="30%" style="padding:0 5 0 5">&nbsp;</td>
				</tr>
				<?
				}else{
				?>
                <tr bgcolor="efefef">
                <td style="padding:0 5 0 5" width="20%" class="fontColor">Kode Team Marketing </td>
					<td style="padding:0 5 0 5" width="30%" ><input name="kd_group_karyawan" type="text" class='groove_text' size="30" value="<?=convert_html($kd_group_karyawan)?>" onKeyUp="fNextFocus(event,document.form1.nik)" ></td>
					<td width="20%" style="padding:0 5 0 5">&nbsp;</td>
					<td width="30%" style="padding:0 5 0 5">&nbsp;</td>
                </tr>
                <? }?>
				<tr bgcolor="efefef">
					<td width="20%" style="padding:0 5 0 5" class="fontColor">Nama Team Marketing </td>
				    <td width="30%" style="padding:0 5 0 5"><input name="nm_group_karyawan" type="text" class='groove_text' size="30" value="<?=convert_html($nm_group_karyawan)?>" onKeyUp="fNextFocus(event,document.form1.nik)" ></td>
				    <td width="20%"></td>
				    <td width="30%"></td>
				</tr>
				<tr bgcolor="efefef">
					<td style="padding:0 5 0 5" class="fontColor">Kode SPV </td>
					<td style="padding:0 5 0 5">
						<input id="nik" name="nik" type="text" class='groove_text' size="30" value="<?=convert_html($nik)?>" onKeyUp="fNextFocus(event,document.form1.fk_cabang)" onChange="fGetKaryawanData()">&nbsp;<img src="images/search.gif" id="img_karyawan"  onClick="fGetKaryawan()" style="border:0px" align="absmiddle">
					</td>
					<td style="padding:0 5 0 5">Nama SPV </td>
					<td style="padding:0 5 0 5"><input type="hidden" id="nm_depan" name="nm_depan" value="<?=convert_html($nm_depan)?>"><span id="divNmKaryawan"><?=convert_html($nm_depan)?></span></td>
				</tr>
<!--				<tr bgcolor="efefef">
					<td style="padding:0 5 0 5" class="fontColor">Kode Dealer</td>
					<td style="padding:0 5 0 5">
						<input name="fk_cabang" type="text" class='groove_text' size="30" value="<?=convert_html($fk_cabang)?>" onKeyUp="fNextFocus(event,document.form1.btnsimpan)" onChange="fGetCabangData()">&nbsp;<img src="images/search.gif" id="img_cabang" onClick="fGetCabang()" style="border:0px" align="absmiddle">
					</td>
					<td style="padding:0 5 0 5">Nama Dealer</td>
					<td style="padding:0 5 0 5"><input type="hidden" name="nm_cabang" value="<?=convert_html($nm_cabang)?>"><span id="divNmCabang"><?=convert_html($nm_cabang)?></span></td>
				</tr>
-->			</table>
<script>
	arrFieldTeam_Marketing={
		0:{'name':'nik','caption':'NIK','type':'get','source':'20210900000017','size':'15','is_required':'t','table_db':'tblkaryawan_dealer','field_key':'nik','field_get':'nm_karyawan','referer':1},
		1:{'name':'nama','caption':'Nama Karyawan/Team Marketing ','type':'readonly','size':'22'},
		2:{'name':'jabatan','caption':'Jabatan','type':'readonly','size':'22'},
		3:{'name':'tgl_pindah','caption':'Tanggal Aktif','type':'date','size':'10',
			'OtherErrorCheck':function (pObj){
				lReturn="";
				if(!pObj.value.match(/^\d{1,2}\/\d{1,2}\/\d{4}$/g)){
					lReturn="Salah"
				} else {
					var tglNormal = new Array(31,28,31,30,31,30,31,31,30,31,30,31);
					var tglLeap = new Array(31,29,31,30,31,30,31,31,30,31,30,31);
					var tgl=pObj.value.split("/");
					if(tgl[2]%4==0){
						if(tgl[1]>12) lReturn="Salah"
						if(tgl[0]>tglLeap[tgl[1]-1]) lReturn="Salah"
					} else{
						if(tgl[1]>12) lReturn="Salah"
						if(tgl[0]>tglNormal[tgl[1]-1]) lReturn="Salah"
					}
				}
				return lReturn
			}
		},
		4:{'name':'tgl_nonaktif','caption':'Tanggal NonAktif','type':'date','size':'10',
			'OtherErrorCheck':function (pObj){
				lReturn="";
				if(pObj.value!=""){
					flag=1
					if(!pObj.value.match(/^\d{1,2}\/\d{1,2}\/\d{4}$/)){
						flag=0
						lReturn="Salah"
					} else {
						var tglNormal = new Array(31,28,31,30,31,30,31,31,30,31,30,31);
						var tglLeap = new Array(31,29,31,30,31,30,31,31,30,31,30,31);
						var tgl=pObj.value.split("/");
						if(tgl[2]%4==0){
							if(tgl[1]>12){
								flag=0;
								lReturn="Salah";
							}
							if(tgl[0]>tglLeap[tgl[1]-1]){
								flag=0;
								lReturn="Salah";
							}
						} else{
							if(tgl[1]>12){
								flag=0;
								lReturn="Salah";
							}
							if(tgl[0]>tglNormal[tgl[1]-1]){
								flag=0;
								lReturn="Salah";
							}
						}
					}
					return lReturn
				}
			}
		}
	};
	table1=new table();
	table1.init("Team_Marketing",arrFieldTeam_Marketing,document.form1,{'border':'0','cellpadding':'1','cellspacing':'1','width':'100%','class':'border','align':'center'});
</script>
<!-- end content begin -->	  
		</td>
    </tr>
</table>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="border" style="border-top:none" align="center">
	<tr height="20"><td height="25" align="center" bgcolor="#D0E4FF">
		<input class="groove_button" type="button" value="Simpan" onClick="fSave()" name="btnsimpan">
		&nbsp;<input type="button"  class="groove_button" value="Batal" onClick="fBatal()"></td>
	</tr>
</table>
</form>
</body>
</html>
<?
function cek_error(){
	global $id_edit,$strmsg,$j_action,$nm_group_karyawan,$nik,$nm_depan,$strisi,$fk_cabang,$nm_cabang;

	if($nm_group_karyawan==""){
		$strmsg.="Nama Team Marketing Kosong.<br>";
		if(!$j_action) $j_action="document.form1.nm_group_karyawan.focus()";
	} elseif ($id_edit=="") {
		if (pg_num_rows(pg_query("select * from tblgroup_karyawan where upper(nm_group_karyawan)='". strtoupper($nm_group_karyawan)."' for update"))){
			$strmsg.="Team Marketing sudah ada.<br>";
			if(!$j_action) $j_action="document.form1.nm_group_karyawan.focus()";
		}
	} else {
		if (pg_num_rows(pg_query("select * from tblgroup_karyawan where upper(nm_group_karyawan)='". strtoupper($nm_group_karyawan)."' and kd_group_karyawan<>'".$id_edit."' for update"))){
			$strmsg.="Team Marketing sudah ada.<br>";
			if(!$j_action) $j_action="document.form1.nm_group_karyawan.focus()";
		}
	}
	
	if($nik==""){
		$strmsg.="Kode Kepala Team Marketing Kosong.<br>";
		if(!$j_action) $j_action="document.form1.nik.focus()";
	}elseif (!pg_num_rows(pg_query("select * from tblkaryawan_dealer where nik='".$nik."' for update"))){
		$strmsg.="Kode Kepala Team Marketing belum terdaftar.<br>";
		if(!$j_action) $j_action="document.form1.nik.focus()";
	}elseif($id_edit==""){
		if(pg_num_rows(pg_query("
			select * from tblgroup_karyawan where fk_karyawan = '".$nik."' and fk_cabang = '".$fk_cabang."' for update
		"))){
			$strmsg.="Kepala Team Marketing sudah terdaftar pada Team lain <br>";
			if(!$j_action) $j_action="document.form1.nik.focus()";
		}
	}else{
		if(pg_num_rows(pg_query("
			select * from tblgroup_karyawan where fk_karyawan = '".$nik."' and kd_group_karyawan <> '".$id_edit."' 
			and fk_cabang = '".$fk_cabang."' for update
		"))){
			$strmsg.="Kepala Team Marketing sudah terdaftar pada Team lain<br>";
			if(!$j_action) $j_action="document.form1.nik.focus()";
		}		
	}
	
/*	if($fk_cabang == ""){
		$strmsg.="Kode Cabang Kosong.<br>";
		if(!$j_action) $j_action="document.form1.fk_cabang.focus()";
	}elseif(!pg_num_rows(pg_query("select * from tblpartner where kd_partner = '".$fk_cabang."' for update"))){
		$strmsg.="Kode Cabang tidak Terdaftar.<br>";
		if(!$j_action) $j_action="document.form1.fk_cabang.focus()";
	}
*/	
	$l_arr_row = split(chr(191),$strisi);
	if($strisi==""){
		$strmsg.="Anggota Team Marketing Kosong.<br>";
		if(!$j_action) $j_action="document.form1.strisi.focus()";
	}else if(count($l_arr_row)>0) {
		for ($i=0; $i<count($l_arr_row)-1; $i++){
			$l_arr_col=split("»",$l_arr_row[$i]);
			//if($l_arr_col[0]==$nik) $strmsg.="@Detail ke-".($i+1)." Kode Karyawan sama dengan Kode Kepala Team<br>";
			//else{
				if(pg_num_rows(pg_query("
					select * from (
						select * from tblgroup_karyawan where fk_cabang = '".convert_sql($fk_cabang)."'
						".(($id_edit!="")?" and kd_group_karyawan <> '".convert_sql($id_edit)."' ": "")."
					)as tblhead
					inner join (
						select * from tblgroup_karyawan_detail where fk_karyawan = '".convert_sql($l_arr_col[0])."'
						and tgl_nonaktif is null
					)as tbldetail on fk_group_karyawan = kd_group_karyawan for update
				")) > 0 && $l_arr_col[4]==""){
					$strmsg.="@Detail ke-".($i+1)." Kode Karyawan sudah terdaftar dan aktif pada Group Lain <br>";
				}
				
				if($l_arr_col[4]!=""){
					if(strtotime($l_arr_col[4]) < strtotime($l_arr_col[3])) $strmsg.='@Detail ke-'.($i+1).' Tanggal Nonaktif lebih kecil dari tanggal aktif<br>';
				}
			//}
			
			if($i > 0){
				for($j = 0; $j < $i; $j++){
					$l_arr_col2=split(chr(187),$l_arr_row[$j]);
					
					//$l_tgl_aktif=strtotime($l_arr_col2[3]);
					//if($l_arr_col2[4])$l_tgl_nonaktif=strtotime($l_arr_col2[4]);
					//else $l_tgl_nonaktif='';
					
					//$l_current_aktif=strtotime($l_arr_col[3]);
					//if($l_arr_col[4])$l_current_nonaktif=strtotime($l_arr_col[4]);
					//else $l_current_nonaktif='';
					if($l_arr_col[0] === $l_arr_col2[0]){	
						$strmsg.="@Detail ke-".($i+1)." Karyawan yang sama sudah terdaftar<br>";
					}
					
				}
			}
		}
	}

	if ($strmsg) $strmsg="Error:<br>".$strmsg;
}

function edit_data(){
	global $id_edit,$j_action,$strmsg,$nm_group_karyawan,$nik,$nm_depan,$strisi,$strmenu,$fk_cabang,$nm_cabang;

	$l_success=1;
	pg_query("BEGIN");
	if(!$fk_cabang)$fk_cabang='-';
	//log begin
	if(!pg_query("insert into tblgroup_karyawan_log select *,'".$_SESSION["ho_id"]."','".$_SESSION["ho_username"]."','#".date("Y/m/d H:i:s")."#','UB' from tblgroup_karyawan where kd_group_karyawan='".$id_edit."'")) $l_success=0;
	
	$l_id_log_ub=get_last_id("tblgroup_karyawan_log","pk_id_log");

	if(!pg_query("insert into tblgroup_karyawan_detail_log select *,'".$l_id_log_ub."' from tblgroup_karyawan_detail where fk_group_karyawan='".$id_edit."'")) $l_success=0;
	//end log
	
	if(!pg_query("update tblgroup_karyawan set nm_group_karyawan='".convert_sql($nm_group_karyawan)."',fk_karyawan='".convert_sql($nik)."', fk_cabang = '".convert_sql($fk_cabang)."' where kd_group_karyawan='".convert_sql($id_edit)."'")) $l_success=0;
	
	if(!pg_query("delete from tblgroup_karyawan_detail  where fk_group_karyawan='".convert_sql($id_edit)."'")) $l_success=0;
	else {
		$l_arr_row = split("¿",$strisi);
		for ($i=0; $i<count($l_arr_row)-1; $i++){
			$l_arr_col=split("»",$l_arr_row[$i]);
			$lrow_karyawan=pg_fetch_array(pg_query("select * from tblkaryawan_dealer where nik='".convert_sql($l_arr_col[0])."'"));
			if(!pg_query("insert into  tblgroup_karyawan_detail (fk_group_karyawan,type_detail,fk_karyawan,fk_group,fk_jabatan,tgl_pindah,tgl_nonaktif) values ('".convert_sql($id_edit)."','Karyawan','".convert_sql($l_arr_col[0])."',null,'".convert_sql($lrow_karyawan["fk_jabatan"])."','#".convert_sql($l_arr_col[3])."',".(($l_arr_col[4]=="")?"NULL":"'".convert_sql($l_arr_col[4])."'").") ")) $l_success=0;
		}
	}

	//log begin
	if(!pg_query("insert into tblgroup_karyawan_log select *,'".$_SESSION["ho_id"]."','".$_SESSION["ho_username"]."','#".date("Y/m/d H:i:s")."#','UA' from tblgroup_karyawan where kd_group_karyawan='".$id_edit."'")) $l_success=0;
	
	$l_id_log_ua=get_last_id("tblgroup_karyawan_log","pk_id_log");

	if(!pg_query("insert into tblgroup_karyawan_detail_log select *,'".$l_id_log_ua."' from tblgroup_karyawan_detail where fk_group_karyawan='".$id_edit."'")) $l_success=0;
	//end log

	if ($l_success==1){
		$strmsg="Team Marketing  Tersimpan.<br>";
		$j_action= "lInputClose=getObjInputClose();lInputClose.close()";
		pg_query("COMMIT");
	}else{
		$strmsg="Error :<br>Team Marketing  Gagal Tersimpan.<br>";
		pg_query("ROLLBACK");
	}
}

function add_data(){
	global $j_action,$strmsg,$nm_group_karyawan,$nik,$nm_depan,$strisi,$fk_cabang,$nm_cabang,$kd_group_karyawan,$kd_group_karyawan;
	
	$l_success=1;
	pg_query("BEGIN");
	if(!$fk_cabang)$fk_cabang='-';
	
	if(!pg_query("insert into tblgroup_karyawan(kd_group_karyawan,nm_group_karyawan,fk_karyawan,fk_cabang) values('".convert_sql($kd_group_karyawan)."','".convert_sql($nm_group_karyawan)."','".convert_sql($nik)."','".convert_sql($fk_cabang)."')")) $l_success=0;
	
	$lid_group_karyawan=get_last_id("tblgroup_karyawan","kd_group_karyawan");
	
	$l_arr_row = split("¿",$strisi);
	for ($i=0; $i<count($l_arr_row)-1; $i++){
		$l_arr_row = split("¿",$strisi);
		for ($i=0; $i<count($l_arr_row)-1; $i++){
			$l_arr_col=split("»",$l_arr_row[$i]);
			$lrow_karyawan=pg_fetch_array(pg_query("select * from tblkaryawan_dealer where nik='".convert_sql($l_arr_col[0])."'"));
			if(!pg_query("insert into  tblgroup_karyawan_detail (fk_group_karyawan,type_detail,fk_karyawan,fk_group,fk_jabatan,tgl_pindah,tgl_nonaktif) values ('".convert_sql($lid_group_karyawan)."','Karyawan','".convert_sql($l_arr_col[0])."',null,'".convert_sql($lrow_karyawan["fk_jabatan"])."','#".convert_sql($l_arr_col[3])."',".(($l_arr_col[4]=="")?"NULL":"'".convert_sql($l_arr_col[4])."'").") ")) $l_success=0;
		}
	}
	//log begin
	if(!pg_query("insert into tblgroup_karyawan_log select *,'".$_SESSION["ho_id"]."','".$_SESSION["ho_username"]."','#".date("Y/m/d H:i:s")."#','IA' from tblgroup_karyawan  where kd_group_karyawan='".$lid_group_karyawan."'")) $l_success=0;
	$l_id_log_ia=get_last_id("tblgroup_karyawan_log","pk_id_log");
	if(!pg_query("insert into tblgroup_karyawan_detail_log select *,'".$l_id_log_ia."' from tblgroup_karyawan_detail where fk_group_karyawan='".$lid_group_karyawan."'")) $l_success=0;
	//end log
	//$l_success=0;
	if ($l_success==1){
		$nm_group_karyawan="";
		$nik="";
		$nm_depan="";
		$fk_cabang="";
		$nm_cabang="";
		$strisi="";
		$strmsg="Team Marketing Tersimpan.<br>";
		pg_query("COMMIT");
	}else{
		$strmsg="Error :<br>Team Marketing Gagal Tersimpan.<br>";
		pg_query("ROLLBACK");
	}
}

function get_data(){
	global $id_edit,$nm_group_karyawan,$nik,$nm_depan,$strisi,$fk_cabang,$nm_cabang;
	
	$lrow = pg_fetch_array(pg_query("
				select * from (
					select * from tblgroup_karyawan where kd_group_karyawan='".$id_edit."'
				)as tblgroup_karyawan
				left join (
					select nik,nm_karyawan from tblkaryawan_dealer
				)as tblkaryawan_dealer on tblgroup_karyawan.fk_karyawan=tblkaryawan_dealer.nik  
				left join tblpartner on fk_cabang = kd_partner
			"));

	$fk_cabang=$lrow["fk_cabang"];
	$nm_cabang=$lrow["nm_partner"];  
	$nm_group_karyawan=$lrow["nm_group_karyawan"];
	$nik=$lrow["fk_karyawan"];
	$nm_depan=$lrow["nm_depan"];
	$nm_depan.=" ".$lrow["nm_belakang"];
	$lrow["nm_gelar"]==""?$nm_depan:$nm_depan.=", ".$lrow["nm_gelar"];
	$nm_depan=$lrow["nm_karyawan"];
	
	$lrs=pg_query("
		select type_detail,tblgroup_karyawan_detail.fk_karyawan,fk_group,
		nm_karyawan,nm_group_karyawan,
		nm_jabatan,tgl_pindah,tgl_nonaktif from (
			select * from tblgroup_karyawan_detail where fk_group_karyawan='".$id_edit."'
		) as tblgroup_karyawan_detail
		left join tblkaryawan_dealer on tblkaryawan_dealer.nik=tblgroup_karyawan_detail.fk_karyawan
		left join tblgroup_karyawan on tblgroup_karyawan.kd_group_karyawan=tblgroup_karyawan_detail.fk_group
		left join tbljabatan on tbljabatan.kd_jabatan=tblkaryawan_dealer.fk_jabatan
	");

	while($lrow=pg_fetch_array($lrs)){
		$strisi.=$lrow["fk_karyawan"]."»".convert_html($lrow["nm_karyawan"])."»".convert_html($lrow["nm_jabatan"])."»".date("m/d/Y",strtotime($lrow["tgl_pindah"]))."»".(($lrow["tgl_nonaktif"])==""?"":date("m/d/Y",strtotime($lrow["tgl_nonaktif"])))."¿";
	}
}
?>
