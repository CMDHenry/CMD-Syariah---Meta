<?
function get_additional(){
	global $laci_awal;
	$fk_brankas=$_REQUEST["kd_brankas"];
	$query="select * from tblbrankas_detail where fk_brankas ='".$fk_brankas."' order by laci";
	$lrs=pg_query($query);
	$i=0;
	while($lrow=pg_fetch_array($lrs)){
		$i++;
	}
	$laci_awal=$i;
}
	
function save_additional(){
	global $l_success,$strmsg,$laci_awal;
		
	$kd_brankas=$fk_brankas=$_REQUEST["kd_brankas"];
	$query="select * from tblbrankas_detail where fk_brankas ='".$fk_brankas."' order by laci";
	$lrs=pg_query($query);
	$laci_akhir=0;
		
	
	//DELETE LAJUR
	$p_table="tbllajur";
	$p_where="fk_laci in (select kd_laci from tbllaci where fk_brankas='".$kd_brankas."')";
	//showquery("select * from ".$p_table." where ".$p_where." and qty_on_hand>0");
	if(!pg_query(insert_log($p_table,$p_where,'DB')))$l_success=0;
	if(!pg_query("delete from ".$p_table." where ".$p_where))$l_success=0;

	//DELETE LACI
	$p_table="tbllaci";
	$p_where="fk_brankas='".$kd_brankas."'";
	if(!pg_query(insert_log($p_table,$p_where,'DB')))$l_success=0;
	if(!pg_query("delete from ".$p_table." where ".$p_where))$l_success=0;

	while($lrow=pg_fetch_array($lrs)){
		
		$i=$lrow["laci"];
		$digit_laci=str_pad($i,2,"0",STR_PAD_LEFT);
		$nm_laci="LC ".$digit_laci;
		$kd_laci=$fk_brankas.".".$digit_laci;
		if(!pg_query("
		insert into tbllaci 
		(kd_laci,nm_laci,fk_brankas)
		values
		('".$kd_laci."','".$nm_laci."','".$fk_brankas."')
		")) $l_success=0;	

		if(!pg_query("insert into tbllaci_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from tbllaci where kd_laci='".$kd_laci."' ")) $l_success=0;

		$jumlah_lajur=$lrow["jumlah_lajur"];
		for($j=1;$j<=$jumlah_lajur;$j++){	
			$digit_lajur=str_pad($j,2,"0",STR_PAD_LEFT);
			$nm_lajur="LJ ".$digit_laci.".".$digit_lajur;
			$kd_lajur=$kd_laci.".".$digit_lajur;
			if(!pg_query("
			insert into tbllajur 
			(kd_lajur,nm_lajur,fk_laci)
			values
			('".$kd_lajur."','".$nm_lajur."','".$kd_laci."')
			")) $l_success=0;	
		
			if(!pg_query("insert into tbllajur_log select *,'".$_SESSION["id"]."','".$_SESSION["username"]."','#".date("Y/m/d H:i:s")."#','IA' from tbllajur where kd_lajur='".$kd_lajur."' ")) $l_success=0;
		}
		$laci_akhir++;
	}
	if(!recount_onhand($kd_brankas))$l_success=0;
	
/*	if($laci_akhir!=$laci_awal){
		$strmsg="Laci tidak sama.<br>";
	}
*/	//$l_success=0;
}

?>

