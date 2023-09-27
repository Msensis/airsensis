<?php

require_once 'helper.php';
require_once 'helper_pecs.php';
require_once 'System.php';

// check for parameters
if(!isset($_REQUEST['station']) || !isset($_REQUEST['metric']) || !isset($_REQUEST['date_from']) || !isset($_REQUEST['date_to'])){
	echo json_encode(['status' => 'fail', 'error_msg' => $msg_missing_params]);
	exit();
}

// check user authorization on the station
if(!hasFullAccess($_REQUEST['station'])){
	echo json_encode(['status' => 'fail', 'error_msg' => $msg_station_auth_error]);
	exit();
}

$station = getStation($_REQUEST['station']);
$stationId=$station->id;
$metric = getMetric($_REQUEST['metric']);
$timezone=getTimezone($stationId);
$date_from_ = new DateTime($_REQUEST['date_from']);
$date_from=$date_from_->sub(new DateInterval('P1D'));
//this must change in winter time. Prodromos wanted to always be +0200
//even in summer hour
$offset = $_REQUEST['browser_offset']+60;
$time=24+($offset/60);
$date_from->setTime($time,0,0);
$date_to = new DateTime($_REQUEST['date_to']);
$date_to->setTime(23,59,59);
//$br_offset=intval($_REQUEST['browser_offset'])*60000*(-1);
$br_offset=0;
//echo $date_to->format('Y-m-d H:i:s') . "\n";

//$date_from->setTime(00,00,00);
//$date_from=new DateTime($date_from);

//$from_date = $date_from;
//$to_date = new DateTime($_REQUEST['date_to'] . ' + 1 day');

$category = getMetricsCategory($metric->category);


$chartdata['validated'] = array();
$chartdata['non validated'] = array();




//print_r($station->id);




//find location timezone

//$stationLan=(string)$station->latitude;
//$stationLng=(string)$station->longitude;

//convert to normal lang and
//$latitude = explode(":", $stationLan);
//$longitude = explode(":", $stationLng);

//latitude
//$latitude_degree=$latitude[0];
//$latitude_min=$latitude[1];
//$latitude_sec_arr=explode(" ", $latitude[2]);
//$latitude_secs=$latitude_sec_arr[0];

//$lat=intval($latitude_degree)+((intval($latitude_min*60)+intval($latitude_secs))/3600);

//longitude
//$longitude_degree=$longitude[0];
//$longitude_min=$longitude[1];
//$longitude_sec_arr=explode(" ", $longitude[2]);
//$longitude_secs=$longitude_sec_arr[0];

//$long=intval($longitude_degree)+((intval($longitude_min*60)+intval($longitude_secs))/3600);  


//$tz_id=getTimezone($lat,$long);
//min max calculation using mongo query. not needed anymore since we get all data anyway so we calculate them from there.
/* $pipeline = [
		['$match' =>
				['Station ID' => "$stationId",
						'timestamp' => [ '$gte' =>  new MongoDB\BSON\UTCDateTime($date_from->getTimestamp()*1000 ) , '$lte' => new MongoDB\BSON\UTCDateTime($date_to->getTimestamp()*1000 )]
				]
		],
		['$group' =>
				['_id'=> '$Station ID',
						'fmin' => ['$min' => $test],
						'fmax' => ['$max' => $test]
				]
		]
		
		
];


$results = $mongo->nano->aggregate($pipeline);

$resultsArray = iterator_to_array($results);
//print_r("hello");
foreach ( $resultsArray as $result ) {
	$min	 = $result->fmin;
	$max	 = $result->fmax;
	// we need number returned from json, not string
	$min = (float)$min;
	$max = (float)$max;

} */





$pipelineFindData = 
['Station ID' => (string)$stationId,
		'timestamp' => [ '$gte' =>  new MongoDB\BSON\UTCDateTime($date_from->getTimestamp()*1000.0+$br_offset) , '$lte' => new MongoDB\BSON\UTCDateTime($date_to->getTimestamp()*1000.0 +$br_offset)]
]
; 





$options = ['sort' => ['timestamp' => 1]];

//$matchingStations = $mongo->nano->find($pipelineFindData, ['xxx'=>true]);
$matchingStations = $mongo->nano->find($pipelineFindData, $options);






$valuesArray=array();


//$matchingStationsArray = iterator_to_array($matchingStations);


//var_dump($matchingStationsArray);

/* $point = array(
		'timestamp'  => '01/03/2018',
		'value' => 0.88

);
$point2 = array(
		'timestamp'  => '01/03/2018',
		'value' => 0.58

);
$fakeData = [$point, $point2];
$chartdata = $fakeData;
$validated = array(
		'validated'  => $mariosData,
		'non validated' => []
); */


//here fails
$metricFieldName = $metric->label;

$tambientFieldName = $metric->label;




$allValues = array();

//Needed for pecs calculation
$temperatureField = 'T ambient';
$caField = 'CA';
$diameterField = 'Diameter';
$numberOfValues = 0;
$diameterSum = 0;

$dt=0;
$previousDateInSeconds=0;
$dateInSeconds=0;

$caValues = array();
$temperatureValues = array();
$avgDiameterValues = array();
$dtValues = array();

foreach ( $matchingStations as $result ) {

	//data for chart and for statistics tab
	if(isset($result->$metricFieldName)){
		$date1=(string)$result->timestamp;
		$date2 =  new MongoDB\BSON\UTCDateTime($date1);
		$date3= $date2->toDateTime();
		$date3->setTimezone(new DateTimeZone($timezone));
		$date4=$date3->format("Y-m-d H:i:s");
		
		$value = $result->$metricFieldName;
		$value = (float)$value;
	
		$row = ['timestamp' => $date4 , 'value' => $value ];
		
		
		if($result->status == 'validated'){
			array_push($chartdata['validated'], $row);
		}else{
			array_push($chartdata['non validated'], $row);
		}
		
		array_push($allValues, $value);
	}

	//data for PECs (i.e Risk tab). Use only database entries that contain values for CA, T and Diameter 
	if(isset($result->$caField) && isset($result->$temperatureField) && isset($result->$diameterField)){

		$numberOfValues = $numberOfValues + 1;
		$diameterSum = $diameterSum + $result->$diameterField;
		$avgDiameter = $diameterSum / $numberOfValues;
		
		$caValues[$numberOfValues-1] = $result->$caField;
		$temperatureValues[$numberOfValues-1] = $result->$temperatureField;
		$avgDiameterValues[$numberOfValues-1] = $avgDiameter;
		
		/*NOTE: for n dates, we will have (n-1) dt intervals. 
		Therefore we must start calculating the intervals after having the second value*/
		if($numberOfValues==1){
			$previousDateInSeconds = (int)((String)($result->timestamp))/1000;
		}else if($numberOfValues>=2){
			$dtValues[$numberOfValues-2]=10;
			/*Uncomment these lines instead of the above line if you want to use real dt instead of 10 seconds:
			$dateInSeconds = (int)((String)($result->timestamp))/1000;
			$dtValues[$numberOfValues-2]=$dateInSeconds-$previousDateInSeconds;
			$previousDateInSeconds = $dateInSeconds;
			*/
		}
		
	}
}


/* 
$dtValues[4]=100000;
$dtValues[3]=4500;
$dtValues[8]=34000; */

//values for statistics tab
if (empty($allValues)) {
	$min = 0;
	$max = 0;
	$avg = 0;
	$var = 0;
	$svar =0;
	
}else{
	$min = min($allValues);
	$max = max($allValues);
	$avg = avg($allValues);
	//$avg=array_sum($allValues) / count($allValues);
	$var = 0;
	$svar =0;
	if(count($allValues)>1){
		$var = stats_variance($allValues, true);
		$svar = stats_standard_deviation($allValues, true);
	}
}

//values for PECs (Risk tab)
if (count($caValues)<2) {

	$peca = 0;
	$pecw = 0;
	$pecs = 0;
	
}else{

	$V33 = avg($caValues);
	$peca = $V33;
	
	$V29andV30Values = calculateV29andV30Values($caValues, $temperatureValues, $avgDiameterValues );
	//var_dump($V29andV30Values['V30']);
	
	$V31 = calculateIntegration($V29andV30Values['V29'], $dtValues);
	$V32 = calculateIntegration($V29andV30Values['V30'], $dtValues);	
	//print_r('--V31--');
	//print_r($V31);
	//print_r('--V32--');
	//print_r($V32);
	
	$pecw = calculatePecw($V31, $V32, $V33);
	//print_r('----');
	//print_r($pecw);
	
	$pecs = calculatePecs($V31, $V32, $V33);	
	//print_r('----');
	//print_r($pecs);
}


$station = getStation($_REQUEST['station']);
echo json_encode(['status' => 'success',
        'offset'=>$time,
		'station' => $station,
		'metric' => $metric,
		'date_from' => $date_from->format('d/m/Y'),
		'date_to' => $date_to->format('d/m/Y'),
		'date_from_full' => $date_from->format('Y-m-d H:i:s'),
		'date_to_full' => $date_to->format('Y-m-d H:i:s'),
		'data' => $chartdata,
		'min' => $min,
		'max' => $max,
		'avg' => $avg,
		'var' => $var,
		'sdev' => $svar,
		'peca' => $peca,
		'pecw' => $pecw,
		'matchingStations' => $matchingStations,
		'pecs' => $pecs]);
		
/* function getTimezone($lat,$lng)
{
	
	// get the API response for the timezone
	$timezoneAPI = "http://api.timezonedb.com/v2.1/get-time-zone?key=UNVAYPK3ZVDN&format=json&by=position&lat={$lat}&lng={$lng}";
	$curl = curl_init($timezoneAPI);
	//curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$curl_response = curl_exec($curl);
	curl_close($curl);
	$decoded = json_decode($curl_response);

	
	return $decoded->zoneName;
	
}   */
?>
