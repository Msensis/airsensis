<?php 
	require_once 'helper.php';
	
	// check for parameters
	if(!isset($_REQUEST['station']) || !isset($_REQUEST['metric']) || !isset($_REQUEST['date_from']) || !isset($_REQUEST['date_to'])){
		echo json_encode(['status' => 'fail', 'error_msg' => 'Missing parameters']);
		exit();
	}
	
	// check user authorization on the station
	if(!hasFullAccess($_REQUEST['station'])){
		echo json_encode(['status' => 'fail', 'error_msg' => 'Not authorized for this station']);
		exit();
	}
	
	$station = getStation($_REQUEST['station']);
	$metric = getMetric($_REQUEST['metric']);
	$date_from = new DateTime($_REQUEST['date_from']);
	$date_to = new DateTime($_REQUEST['date_to']);

	$from_date = $date_from;
	$to_date = new DateTime($_REQUEST['date_to'] . ' + 1 day');
	
	$category = getMetricsCategory($metric->category);
	
	// ***** toDo get values from db ******
	
	// generate random values for testing
/*	$interval = DateInterval::createFromDateString('1 hour');
	$period = new DatePeriod($from_date, $interval, $to_date);
	$data = array();
	foreach ( $period as $dt ) {
		array_push($data, rand($metric->min, $metric->max));
	}
*/	
	$data = [0.88,0.88,0.9,0.9,0.68,0.68,0.81,0.81,0.93,0.93];

	
	echo json_encode(['status' => 'success',
					  'station' => $station,
					  'metric' => $metric,
					  'date_from' => $date_from->format('d/m/Y'),
					  'date_to' => $date_to->format('d/m/Y'),
					  'min' => min($data),
					  'max' => max($data),
					  'avg' => avg($data),
					  'var' => stats_variance($data, true),
					  'sdev' => stats_standard_deviation($data, true)]);
?>