<?php
	require_once 'helper.php';
	require_once 'include/PHPExcel.php';
	
	// check for parameters
	if(!isset($_REQUEST['station']) || !isset($_REQUEST['metric']) || !isset($_REQUEST['date_from']) || !isset($_REQUEST['date_to'])){
		echo $msg_missing_params;
		exit();
	}
	
	// check user authorization on the station
	if(!hasFullAccess($_REQUEST['station'])){
		echo $msg_station_auth_error;
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

	// ***** toDo get values from db ******
	
	// generate random values for testing
	/* $interval = DateInterval::createFromDateString('1 hour');
	$period = new DatePeriod($from_date, $interval, $to_date);
	$data = array();  
	
	foreach ( $period as $dt ) {
		$row = ['timestamp' => $dt->format("d-m-Y H:i:s"), 'value' => rand($metric->min, $metric->max)];
		array_push($data, $row);
	}*/
	
	$stationId=$station->id;
	
	$pipelineFindData =
	['Station ID' => "$stationId",
			'timestamp' => [ '$gte' =>  new MongoDB\BSON\UTCDateTime($date_from->getTimestamp()*1000 ) , '$lte' => new MongoDB\BSON\UTCDateTime($date_to->getTimestamp()*1000 )]
	]
	;
	
	$options = ['sort' => ['timestamp' => 1]];
	
	//$matchingStations = $mongo->nano->find($pipelineFindData, ['xxx'=>true]);
	$matchingStations = $mongo->nano->find($pipelineFindData, $options);
	
	$matchingStationsArray = iterator_to_array($matchingStations);
	
	$metricFieldName = $metric->label;

	$data = array();

	
	foreach ( $matchingStationsArray as $result ) {
		
		if(isset($result->$metricFieldName)){
			
			$date1=(string)$result->timestamp;
			$date2 =  new MongoDB\BSON\UTCDateTime($date1);
			$date3= $date2->toDateTime();
			
			$value = $result->$metricFieldName;
			$value = (float)$value;
			
			$row = ['timestamp' => $date3->format("Y-m-d H:i:s") , 'value' => $value, 'status' => $result->status ];
						
			array_push($data, $row);
		}
	}
	
	
	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();

	// Set document properties
	$objPHPExcel->getProperties()->setCreator($site_name)
								 ->setLastModifiedBy($site_name)
								 ->setTitle($site_name . " Data Export")
								 ->setSubject($site_name . " Data Export")
								 ->setDescription("Data export document from " . $site_name)
								 ->setKeywords("nanomonitor data export")
								 ->setCategory($site_name . " Data Export File");

	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);

	// set auto width to columns
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);

	// Add column names
	$objPHPExcel->getActiveSheet()
				->setCellValue('A1', 'Timestamp')
				->setCellValue('B1', $metric->label . ' ('.$metric->unit.')')
				->setCellValue('C1', 'Status');

	// Add row data
	for($i=0;$i<count($data);$i++){
		$objPHPExcel->getActiveSheet()
					->setCellValue('A'.($i+2), $data[$i]['timestamp'])
					->setCellValue('B'.($i+2), $data[$i]['value'])
					->setCellValue('C'.($i+2), $data[$i]['status']);
	}

	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle($metric->label);

	// Redirect output to a client’s web browser (Excel5)
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="'. $site_name . '_Data_Export.xls"');
	header('Cache-Control: max-age=0');
	// If you're serving to IE 9, then the following may be needed
	header('Cache-Control: max-age=1');

	// If you're serving to IE over SSL, then the following may be needed
	header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
	header ('Pragma: public'); // HTTP/1.0

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
	exit;
