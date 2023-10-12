<?

function save_additional(){
	global $l_success;
		
	$fk_brankas=$_REQUEST["kd_brankas"];
	$query="select * from tblbrankas_detail where fk_brankas ='".$fk_brankas."' order by laci";
	$lrs=pg_query($query);
	
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

	}
	
	//$l_success=0;
}

?>

