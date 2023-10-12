<?
require '../requires/session.inc.php';
require '../requires/config.inc.php';
require '../requires/db_utility.inc.php';
require '../requires/general.inc.php';
require '../requires/numeric.inc.php';

$type=stripslashes(trim($_REQUEST["type"]));
$schema=stripslashes(trim($_REQUEST["schema"]));
$table=stripslashes(trim($_REQUEST["table"]));
$field=stripslashes(trim($_REQUEST["field"]))." as data";
$key=stripslashes(trim($_REQUEST["key"]));
$value=stripslashes(trim($_REQUEST["value"]));
$is_date=$_REQUEST["is_date"];
$is_escape=$_REQUEST["is_escape"];
$groupby=stripslashes(trim($_REQUEST["groupby"]));
$limitby=stripslashes(trim($_REQUEST["limitby"]));
$param_value=stripslashes(trim($_REQUEST["param_value"]));
$param1_value=stripslashes(trim($_REQUEST["param1_value"]));
$param2_value=stripslashes(trim($_REQUEST["param2_value"]));
$param3_value=stripslashes(trim($_REQUEST["param3_value"]));
$param4_value=stripslashes(trim($_REQUEST["param4_value"]));

//echo $field."--".$table."--".$key."--".$value;

function min3_month($month){
	//	echo $month;
	if($month>=1 && $month<=3){
		if($month==1)return 10;
		else if ($month==2)return 11;
		else if ($month==3)return 12;
	}
	else {
		
		return ($month-3);
	}	
}
function min1_month($month){
	if($month==1){
		return 12;	
	}
	else {
		return ($month-1);
	}	
}

switch($type){
	case "list" : 
		//if($schema)$table="data_showroom.".$table;
		$query="select ".$field." from ".$table;
		if ($key!="" && $value!="") {
			if ($is_date=="1") $query.=" where ".$key."='#".$value."#'";
			else $query.=" where ".$key."='".convert_sql($value)."'";
		}
		if($limitby!="")$query." limit ".$limitby;
		//echo $query;
		$lrs=pg_query($query);
		while ($row=pg_fetch_array($lrs)){
			echo $row[data];
			echo ",";
		}
	break;
	
	case "multiple_schema":
/*		if($schema){
			$table=str_replace("schema","data_showroom",$table);
			$field=str_replace("schema","data_showroom",$field);
		}
*/		$query="select ".$field." from ".$table;
		
		//showquery ($key);
		if ($key!="" && $value!="") {
			if ($is_date=="1") $query.=" where ".$key."='#".$value."#'";
			//else $query.=" where ".$key."='".$value."'";
		}
		//showquery($query);
		if (
		$groupby!="") $query.=" group by ".$groupby;
		if ($row=pg_fetch_array(pg_query($query)))
			echo $row[data];
		else
			echo "-";
	break;

	case "multiple_schema2":
		if($schema){
			$table=str_replace("schema","data_servis",$table);
			$field=str_replace("schema","data_servis",$field);
		}
		$query="select ".$field." from ".$table;
		if ($key!="" && $value!="") {
			if ($is_date=="1") $query.=" where ".$key."='#".$value."#'";
			else $query.=" where ".$key."='".$value."'";
		}
		if ($groupby!="") $query.=" group by ".$groupby;
		if ($row=pg_fetch_array(pg_query($query)))
			echo $row[data];
		else
			echo "-";
	break;
	
	case "multiple_schema3":
		if($schema){
			$table=str_replace("schema","data_kas",$table);
			$field=str_replace("schema","data_kas",$field);
		}
		$query="select ".$field." from ".$table;
		if ($key!="" && $value!="") {
			if ($is_date=="1") $query.=" where ".$key."='#".$value."#'";
			else $query.=" where ".$key."='".$value."'";
		}
		if ($groupby!="") $query.=" group by ".$groupby;
		if ($row=pg_fetch_array(pg_query($query)))
			echo $row[data];
		else
			echo "-";
	break;
		
	
	default:
		if ($value!="") {
			//if($schema)$table="data_showroom.".$table;
			$query="select ".$field." from ".$table;
			if ($key!="" && $value!="") {
				if ($is_date=="1") $query.=" where ".$key."='#".$value."#'";
				else $query.=" where ".$key."='".$value."'";
			}
			//echo $query;
			//echo $table;
			
			if ($row=pg_fetch_array(pg_query($query)))
				if($is_escape=="1")	echo fullescape($row[data]);
				else echo $row[data];
			else
				echo "";
				
	break;
	}
}

?>
