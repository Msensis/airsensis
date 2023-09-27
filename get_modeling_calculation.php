<?php 
	require_once 'helper.php';
	
	// check for parameters
	if(!isset($_REQUEST['station']) || !isset($_REQUEST['metric']) || !isset($_REQUEST['date_from']) || !isset($_REQUEST['date_to']) || !isset($_REQUEST['function'])){
		echo json_encode(['status' => 'fail', 'error_msg' => $msg_missing_params]);
		exit();
	}
	
	// check user authorization on the station
	if(!hasFullAccess($_REQUEST['station'])){
		echo json_encode(['status' => 'fail', 'error_msg' => $msg_station_auth_error]);
		exit();
	}
	
	$station = getStation($_REQUEST['station']);
	$metric = getMetric($_REQUEST['metric']);
	$date_from = new DateTime($_REQUEST['date_from']);
	$date_to = new DateTime($_REQUEST['date_to']);
	$date_to->setTime(23,59,59);

	//$from_date = $date_from;
	//$to_date = new DateTime($_REQUEST['date_to'] . ' + 1 day');
	
	$category = getMetricsCategory($metric->category);
	
	// ***get values from db ******
	$stationId=$station->id;
	$pipelineFindData =
	['Station ID' => "$stationId",
			'timestamp' => [ '$gte' =>  new MongoDB\BSON\UTCDateTime($date_from->getTimestamp()*1000 ) , '$lte' => new MongoDB\BSON\UTCDateTime($date_to->getTimestamp()*1000 )]
	]
	;
	$matchingStations = $mongo->nano->find($pipelineFindData);	
	$valuesArray=array();	
	$matchingStationsArray = iterator_to_array($matchingStations);
	$metricFieldName = $metric->field_name;
	
	$data = array();
	$datay = array();

	foreach ( $matchingStationsArray as $result ) {
		//print_r("matchingStation");
		
		if(isset($result->$metricFieldName)){
			
			$dt1=(string)$result->timestamp;
			$date2 =  new MongoDB\BSON\UTCDateTime($dt1) ;
			$date3= $date2->toDateTime();
			
			$value = $result->$metricFieldName;
			$value = (float)$value;
			
			$row = ['timestamp' => $date3->format("Y-m-d H:i:s") , 'value' => $value ];
			
			// TODO is this approach correct?
			//for functions that require metric y, only use data from entries were both metric and metric y values are present
			if(isset($_REQUEST['metric_y'])){
				
				$metricY = getMetric($_REQUEST['metric_y']);
				$metricYFieldName = $metricY->field_name;
				if(isset($result->$metricYFieldName)){
					$valueY = $result->$metricYFieldName;
					$valueY = (float)$valueY;
					
					array_push($data, $value);
					array_push($datay, $valueY);
				}
				
			}else{
				array_push($data, $value);
			}
			
		}
	}
	
	
	//end of getting values from db
	
	//$data = [0.88,0.88,0.9,0.9,0.68,0.68,0.81,0.81,0.93,0.93];
	
	$function = $_REQUEST['function'];
	switch ($function) {
    case 'percentile':
        if(!isset($_REQUEST['p'])){
			echo json_encode(['status' => 'fail', 'error_msg' => $msg_missing_params]);
			exit();
		}
		$p = $_REQUEST['p'];
		$result = percentile($data,$p);
        break;
    case 'correlation':
        if(!isset($_REQUEST['metric_y'])){
			echo json_encode(['status' => 'fail', 'error_msg' => $msg_missing_params]);
			exit();
		}
		
		// ***** toDo get values from db ******
		//$datay = getDataY($mongo);
		//$data = [0.55,0.59995,660.57];
		//$datay = [0.55,0.55,660.57];
		//print_r($data);
		//print_r("Y");
		//print_r($datay);
		
		/* $data[0] = 37;
		$data[1] = 40;
		$data[2] = 30;
		$datay[0] = 60;
		$datay[1] = 60;
		$datay[2] = 61;
		 */
		 //$data[0] = 59.3;
		//$data[1] = 61.2;
		//$data[2] = 56.8;
		//$data[3] = 97.55;
		
		//$datay[0] = 565.82;
		//$datay[1] = 54.568;
		//$datay[2] = 84.22;
		//$datay[3] = 483.55; 

//		$result = stats_stat_correlation($data,$datay);

		//http://php.net/manual/en/function.stats-stat-correlation.php
		//php's correlation function is only for integers, with continued numbers. so it should not work?
		//$result = correlation($data,$datay);
		
						
		$result = correlation($data,$datay);
		//print_r('result ');
		//print_r($result);
		//print_r($result);
        break;
    case 'covariance':
        if(!isset($_REQUEST['metric_y'])){
			echo json_encode(['status' => 'fail', 'error_msg' => $msg_missing_params]);
			exit();
		}
		
		// ***** toDo get values from db ******
		
		//$datay = [0.55,-990.55,660.57];
//		$result = stats_covariance($data, $datay);
		/* $datay[0] = 60;
		$datay[1] = 60;
		$datay[2] = 61; */
		$result = covariance($data, $datay);
        break;
    case 'forecast':
        if(!isset($_REQUEST['metric_y']) || !isset($_REQUEST['x'])){
			echo json_encode(['status' => 'fail', 'error_msg' => $msg_missing_params]);
			exit();
		}
		
		// ***** toDo get values from db ******
		
		//$datay = [0.55,0.55,0.57,0.57,0.43,0.43,0.51,0.51,0.58,0.58];
		$x = $_REQUEST['x'];
		
		if (count(array_unique($data)) === 1){
			
			echo json_encode(['status' => 'fail', 'error_msg' => 'Cannot predict forecast for Y because all existing X values are the same']);
			exit();
		}
		
		$result = forecast($data,$datay,$x);
        break;
    default:
		echo json_encode(['status' => 'fail', 'error_msg' => $msg_unknown_function.' '.$function]);
		exit();
	}
	
	echo json_encode(['status' => 'success','result' => $result]);
?>
