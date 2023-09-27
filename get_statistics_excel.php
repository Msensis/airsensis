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

	// test data
	//$data = [0.88,0.88,0.9,0.9,0.68,0.68,0.81,0.81,0.93,0.93];
	
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
	
	$metricFieldName = $metric->field_name;
	
	$data = array();
	
	
	foreach ( $matchingStationsArray as $result ) {
		
		if(isset($result->$metricFieldName)){
			
			$date1=(string)$result->timestamp;
			$date2 =  new MongoDB\BSON\UTCDateTime($date1);
			$date3= $date2->toDateTime();
			
			$value = $result->$metricFieldName;
			$value = (float)$value;

			array_push($data, $value);
		}
	}
	
	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();

	// Set document properties
	$objPHPExcel->getProperties()->setCreator($site_name)
								 ->setLastModifiedBy($site_name)
								 ->setTitle($site_name . " Statistics Export")
								 ->setSubject($site_name . " Statistics Export")
								 ->setDescription("Statistics export document from " . $site_name)
								 ->setKeywords("nanomonitor statistics export")
								 ->setCategory($site_name . " Statistics Export File");

	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);

	// set auto width to columns
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);

	// Add column names
	$objPHPExcel->getActiveSheet()
				->setCellValue('A1', 'Station ID')
				->setCellValue('B1', 'Location')
				->setCellValue('C1', 'Period')
				->setCellValue('D1', 'Metric')
				->setCellValue('E1', 'Unit')
				->setCellValue('F1', 'Statistic')
				->setCellValue('G1', 'Value');

	// Add row data
	$period = $date_from->format('d/m/Y') . ' - ' . $date_to->format('d/m/Y');
	//add info data
	for($i=2;$i<7;$i++){
		$objPHPExcel->getActiveSheet()
					->setCellValue('A'.$i, $station->id)
					->setCellValue('B'.$i, $station->location)
					->setCellValue('C'.$i, $period)
					->setCellValue('D'.$i, $metric->label)
					->setCellValue('E'.$i, $metric->unit);
	}
	//add statistics
	$objPHPExcel->getActiveSheet()
					->setCellValue('F2', 'Min')
					->setCellValue('G2', min($data))
					->setCellValue('F3', 'Max')
					->setCellValue('G3', max($data))
					->setCellValue('F4', 'Average')
					->setCellValue('G4', avg($data))
					->setCellValue('F5', 'Variance')
					->setCellValue('G5', stats_variance($data, true))
					->setCellValue('F6', 'Standard deviation')
					->setCellValue('G6', stats_standard_deviation($data, true));

	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle($metric->label . ' Statistics');

	// Redirect output to a client’s web browser (Excel5)
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="'. $site_name . '_Statistics_Export.xls"');
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
