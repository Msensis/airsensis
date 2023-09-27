<?php
ini_set ('display_errors',1);

require_once 'session.php';
require_once 'dbconnect.php';
require_once 'messages.php';

function log_user_action($user, $action, $affected_user = null){
	global $mysql;
	
	if (!($stmt = $mysql->prepare("insert into user_action(user,action,affected_user) values(?,?,?)"))) {
		throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
	}
	if (!$stmt->bind_param("iss", $user, $action, $affected_user)) {
		throw new Exception("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	if (!$stmt->execute()) {
		throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	
	// update last visit date
	if ($action == UserAction::Login){
		if (!($stmt = $mysql->prepare("update user set last_visit=now() where id=?"))) {
			throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
		}
		if (!$stmt->bind_param("i", $user)) {
			throw new Exception("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
		}
		if (!$stmt->execute()) {
			throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
		}
	}

	$stmt->close();
}

function hasFullAccess($station_id){
	global $mysql, $uid, $urole;
	
	// public users
	if(!isset($uid)){
		return false;
	}
	
	// Admins and Stakeholders
	if(($urole == 1) || ($urole == 2)){
		return true;
	}
	
	// Data Providers
	if (!($stmt = $mysql->prepare("select *
								    from user_station
									where user_id=? and station_id=?"))) {
		throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
	}
	if (!$stmt->bind_param("is", $uid, $station_id)) {
		throw new Exception("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	if (!$stmt->execute()) {
		throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	
	$stmt->store_result();
	
	if ($stmt->num_rows > 0){
		return true;
	}
	return false;
}

function dms2dd($dms){
	// get values from string
	$dms_arr = explode(':', $dms);
	$deg = $dms_arr[0];
	$min = $dms_arr[1];
	$sec_arr = explode(' ', $dms_arr[2]);
	$sec = $sec_arr[0];
	$dir = $sec_arr[1];

    // Converting DMS ( Degrees / minutes / seconds ) to decimal format
    $dd = $deg+((($min*60)+($sec))/3600);
	if ($dir == 'S' || $dir == 'W'){
		$dd = -1 * $dd;
	}
	
	return $dd;
}

function getUsers(){
	global $mysql;
	if (!($stmt = $mysql->prepare("select u.id as id, 
										  u.name as name, 
										  username, 
										  email, 
										  r.name as role, 
										  date_format(last_visit,'%d/%m/%Y %H:%i:%s') as last_visit 
									from user u inner join role r on u.role=r.id 
									order by name"))) {
		throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
	}
	if (!$stmt->execute()) {
		throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	
	return $stmt->get_result();
}

function getUser($id){
	global $mysql;
	if (!($stmt = $mysql->prepare("select name, 
										  username, 
										  email, 
										  role
								    from user
									where id=?"))) {
		throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
	}
	if (!$stmt->bind_param("i", $id)) {
		throw new Exception("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	if (!$stmt->execute()) {
		throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
	}

	return $stmt->get_result()->fetch_object();
}

function getRoles(){
	global $mysql;
	if (!($stmt = $mysql->prepare("select id, name 
								   from role 
								   order by name"))) {
		throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
	}
	if (!$stmt->execute()) {
		throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
	}

	return $stmt->get_result();
}

function getStations(){
	global $mysql, $uid, $urole;

	// public users
	if(!isset($uid)){
		return false;
	}
	
	// Admins and Stakeholders
	if ($urole==1 || $urole==2){
		if (!($stmt = $mysql->prepare("select *
										from station
										order by id"))) {
			throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
		}
		if (!$stmt->execute()) {
			throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
		}
	}
	// Data Providers
	elseif ($urole == 3){
		if (!($stmt = $mysql->prepare("select s.*
										from station s inner join user_station us on s.id = us.station_id
										where user_id=?
										order by s.id"))) {
			throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
		}
		if (!$stmt->bind_param("i", $uid)) {
			throw new Exception("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
		}
		if (!$stmt->execute()) {
			throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
		}
	}

	return $stmt->get_result();
}    

function getStation($id){
	global $mysql;
	if (!($stmt = $mysql->prepare("select *
								    from station
									where id=?"))) {
		throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
	}
	if (!$stmt->bind_param("i", $id)) {
		throw new Exception("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	if (!$stmt->execute()) {
		throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	
	return $stmt->get_result()->fetch_object();
}    

function getTimezone($station_id){
	global $mysql;
	if (!($stmt = $mysql->prepare("select timezone
								    from station
									where id=?"))) {
		throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
	}
	if (!$stmt->bind_param("s", $station_id)) {
		throw new Exception("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	if (!$stmt->execute()) {
		throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	
	return $stmt->get_result()->fetch_object()->timezone;
}    


function getStationLoc($id){
	global $mysql;
	if (!($stmt = $mysql->prepare("select location AS loc
								    from station
									where id=?"))) {
		throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
	}
	if (!$stmt->bind_param("i", $id)) {
		throw new Exception("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	if (!$stmt->execute()) {
		throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	
	return $stmt->get_result()->fetch_object();
}    


function getUserStationIds($id){
	global $mysql;
	if (!($stmt = $mysql->prepare("select station_id as id
								    from user_station
									where user_id=?
									order by station_id"))) {
		throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
	}
	if (!$stmt->bind_param("i", $id)) {
		throw new Exception("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	if (!$stmt->execute()) {
		throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
	}

	$rows = $stmt->get_result();
	$stmt->close();
	
	$station_ids = array();
	foreach ($rows as $row){
		array_push($station_ids, $row['id']);
	}
	
	return $station_ids;
}

function existStation($id){
	global $mysql;
	if (!($stmt = $mysql->prepare("select count(id) as tt
								    from station
									where id=?"))) {
		throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
	}
	if (!$stmt->bind_param("i", $id)) {
		throw new Exception("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	if (!$stmt->execute()) {
		throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
	}

	$row = $stmt->get_result()->fetch_object();
	$stmt->close();
	
	if($row->tt == 1)
		return true;
	
	return false;
}
function existName($id){
	global $mysql;
	if (!($stmt = $mysql->prepare("select location 
								    from station
									where id=?"))) {
		throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
	}
		
	if (!$stmt->bind_param("i", $id)) {
		throw new Exception("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	if (!$stmt->execute()) {
		throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	
	return $stmt->get_result()->fetch_object();
}
	
	
function getMetricsCategories(){
	global $mysql;
	if (!($stmt = $mysql->prepare("select *
								    from metrics_category 
									order by name"))) {
		throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
	}
	if (!$stmt->execute()) {
		throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	
	return $stmt->get_result();
}

function getMetricsCategory($id){
	global $mysql;
	if (!($stmt = $mysql->prepare("select *
								    from metrics_category
									where id=?"))) {
		throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
	}
	if (!$stmt->bind_param("i", $id)) {
		throw new Exception("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	if (!$stmt->execute()) {
		throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	
	return $stmt->get_result()->fetch_object();
}    
    

function getCategoryMetrics($id){
	global $mysql;
	if (!($stmt = $mysql->prepare("select *
								    from metrics
									where category=?
									order by label"))) {
		throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
	}
	if (!$stmt->bind_param("i", $id)) {
		throw new Exception("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	if (!$stmt->execute()) {
		throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	
	return $stmt->get_result();
}    

function getMetrics(){
	global $mysql;
	if (!($stmt = $mysql->prepare("select 	m.id as id,
											label,
											field_name,
											c.name as category,
											unit,
											min,
											max
								    from metrics m inner join metrics_category c on m.category=c.id
									order by c.name, label"))) {
		throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
	}
	if (!$stmt->execute()) {
		throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	
	return $stmt->get_result();
}    

function getMetric($id){
	global $mysql;
	if (!($stmt = $mysql->prepare("select *
								    from metrics
									where id=?"))) {
		throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
	}
	if (!$stmt->bind_param("i", $id)) {
		throw new Exception("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	if (!$stmt->execute()) {
		throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	
	return $stmt->get_result()->fetch_object();
} 

/* STATTISTICAL FUNCTIONS */
function avg($data){
	return (array_sum($data) / count($data));
}

function percentile($arr, $p){
    //$p = $p*0.01;
	//sort($arr);
    //return $arr[ceil($p * count($arr)) - 1];
    
    return stats_stat_percentile($arr, $p);
}
   
function correlation($arr_x, $arr_y){
	
	if (count(array_unique($arr_x)) === 1){
		
		return 0;
	}
	
	if (count(array_unique($arr_y)) === 1){
		
		return 0;
	}
   
	$result = stats_stat_correlation($arr_x,$arr_y);
	
	return $result;
}
function covariance($arr_x, $arr_y){

	$length = count($arr_x);
	$result = stats_covariance($arr_x,$arr_y);
	
	//multiply the result with n/n-1 as Kostas suggested for the correct covariance function
	$result = $result * $length / ($length -1);
	
	return $result;
}
function forecast($x, $y, $xk){
	
	//Calculate Beta
	$length = count($x);
	$meanX = array_sum($x) / $length;
	$meanY = array_sum($y) / $length;
	
	$meanDeviationX=0;
	$meanDeviationY=0;
	$sumMeanDeviationXProductY=0;
	$sumMeanDeviationXPower=0;
	
	for($i=0;$i<$length;$i++)
	{
		$meanDeviationX=$x[$i]-$meanX;
		$meanDeviationY=$y[$i]-$meanY;
		$sumMeanDeviationXProductY=$sumMeanDeviationXProductY+($meanDeviationX*$meanDeviationY);
		$sumMeanDeviationXPower=$sumMeanDeviationXPower + pow($meanDeviationX,2);
	}
	
	$b = $sumMeanDeviationXProductY / $sumMeanDeviationXPower;
	
	//Calculate Alpha
	$a = $meanY - $b * $meanX;
	
	//calculate y
	$yk = $a + $b * $xk;
	
	return $yk;
}

?>