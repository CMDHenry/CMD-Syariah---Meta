<?php
include_once("report.php");

function filter_request(){
	global $showCab,$showBln,$is_perbandingan,$showWil,$is_summary;

	$showCab='t';
	$showBln='t';
	$showWil='t';
	
	$is_perbandingan=trim($_REQUEST["is_perbandingan"]);
	if($is_perbandingan==""){
		$is_perbandingan="f";
	}
	
	$is_summary=trim($_REQUEST["is_summary"]);
	if($is_summary==""){
		$is_summary="f";
	}
	
	
}
function create_filter(){
	global $is_perbandingan,$is_summary;
?>

    <tr bgcolor="efefef">
        <td width="20%" style="padding:0 5 0 5" bgcolor="#efefef"><!--Perbandingan--></td>
        <td width="30%" style="padding:0 5 0 5" bgcolor="#efefef">
<!--            <input type="checkbox" name="is_perbandingan" value="t" <?=(($is_perbandingan=="t")?"checked":"")?> >                    
-->        </td>
        <td style="padding:0 5 0 5" width="20%">Summary</td>
        <td style="padding:0 5 0 5" width="30%"> 
        <input type="checkbox" name="is_summary" value="t" <?=(($is_summary=="t")?"checked":"")?> >                    
</td>
    </tr>   

<?
}
function excel_content(){
	global $bulan,$tahun,$fk_cabang,$is_perbandingan,$fk_wilayah,$is_summary;
	
	$kd_coa='4|5';
	
	generate_report_acc($bulan,$tahun,$fk_cabang,$kd_coa,$is_perbandingan,$fk_wilayah,$is_summary);

}


