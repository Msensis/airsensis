<?php 
    //deny remote access
/*	if ($_SERVER['SERVER_ADDR'] != $_SERVER['REMOTE_ADDR']){
		echo "Access Denied!";
		exit;
	}
*/



	require_once 'helper.php';
	//=====================================

	
	//=====================================
	$response = array();
	if (!isset($uid)){
		$reponse['status'] = "403";
		echo json_encode($reponse);
		exit;
	}	
	
	ini_set('display_errors', 1);
	ini_set('max_execution_time', 600);
	ini_set('memory_limit', '64M');
	
	// create index
	$key = ['Station ID' => 1, 'timestamp' => -1, ];
	$options = ['unique' => true];
	$mongo->nano->createIndex($key, $options);
	
	$metricsdbtest=getMetrics();
	
	
	$current_date = date('Y-m-d H:i:s');
	$response['name'] = $_FILES['file']['name'];
    $file_name='logs/'."$current_date".'_'.$response['name'].'.log';

	
	// get column names from first row
	$file = fopen($_FILES['file']['tmp_name'], "r");
	//$file = fopen("nanodata_sample_2.csv", "r");
	$key = fgetcsv($file, 1000, ",");
	$numofcols = count($key);
    
    
    //this also needs to be written in $log_file
	$tempError = checkFields($key);
	if(!empty($tempError))
	{
		$response['status'] = 2;
		$response['error'] = $tempError;
		output($response);
	}
	
	$corruptedLines = 0;
	$inserted = 0;
	$updated = 0;
	$failed = 0;
	
	$document = array();
	$documents = array();
	$fileLine = 2;
	$error = '';
	//$myfile = fopen("logs/newfile.txt", "w");
	$errors = '';
 	while ($value = fgetcsv($file, 1000, ",")){
		if (count($value) != $numofcols){
			$failed ++;
			//$errors .= "line ".$fileLine. " error: Data columns do not match with fields columns\r\n";
            
            file_put_contents($file_name,"line ".$fileLine. " error: Data columns do not match with fields columns\r\n",FILE_APPEND);
		}
		else
		{
			$document = array();
			$document['timestamp'] = null;
			
			$document['status'] = 'validated';
			
			for($i=0; $i < $numofcols; $i++){
				if ($key[$i] == "Date"){
					$date_str = $value[$i];
				}elseif ($key[$i] == "Time"){
					$time_str = $value[$i];
				}elseif ($key[$i] == "Comment"){
					$comment_str = $value[$i];
                    $document['Comment'] = $comment_str;
				}elseif ($key[$i] == "Longitude"){
					$Longitude_str = $value[$i];
                    $document['Longitude'] = $Longitude_str;
				}elseif ($key[$i] == "Latitude"){
					$Latitude_str = $value[$i];
                    $document['Latitude'] = $Latitude_str;
				}elseif ($key[$i] == "Location"){
					$document['Location']= $value[$i];
				/* }elseif ($key[$i] == "Station ID"){
					$document['Station ID'] = $value[$i]; */
				}elseif ($key[$i] == "Station ID"){
					if (ctype_digit($value[$i])){
						if (startsWith($value[$i],'0')){
							$station_id_no_pre_zeros = ltrim($value[$i], '0');
							file_put_contents($file_name,"line ".$fileLine. " warning: The Station ID with ".$value[$i]." changed to $station_id_no_pre_zeros. \r\n",FILE_APPEND);
							$document['Station ID'] = $station_id_no_pre_zeros;
						}else{
							$document['Station ID'] = $value[$i];
						}
						
					}
					
				
				}else{
					//==============================test 
					
					$findMetric=false;
					$isInRange=false;
					$testnum=0;
					$metricFieldName;
					
					
								//do not insert in db non numeric strings.
								if(!is_numeric($value[$i]))
								{ 
									//if non numeric string is not "null" add it to error message
									if(strcasecmp("Null", $value[$i]) != 0){
										//$errors .= "line ".$fileLine. " warning: The ".$value[$i]." is not a numeric and will be omitted. \r\n";
                                        file_put_contents($file_name,"line ".$fileLine. " warning: The ".$value[$i]." is not a numeric and will be omitted. \r\n",FILE_APPEND);
                                    }
								}
								else{
									$testnum=floatval($value[$i]);
								
									$metricsdbtest->data_seek(0);
									
									
									while ($metdbtest = $metricsdbtest->fetch_object()) {
											if($metdbtest->label == $key[$i] ){
												$findMetric=true;
												$metricFieldName=$metdbtest->field_name;
												if($testnum >= $metdbtest->min && $testnum <= $metdbtest->max)
												{
													$isInRange=true;
												}

											} 
		
									}//end while
								}//end if-else
				
								if($findMetric && $isInRange)
								{
									$document[$metricFieldName] = $testnum;
								}//$value[$i];}
								else
								{
                                    //$errors .= "line ".$fileLine. " warning: In Metric ".$key[$i]." the value ".$value[$i]." is out of range and will be omitted. \r\n";
                                    file_put_contents($file_name,"line ".$fileLine. " warning: In Metric ".$key[$i]." the value ".$value[$i]." is out of range and will be omitted. \r\n",FILE_APPEND);

                                }									
							
						}

								
			}//endfor
							
					
				//}

			
			if(existStation($document['Station ID']))
			{
				if(getStation($document['Station ID'])->location == $document['Location'])
				{
				 	if(array_key_exists('PM',$document) && array_key_exists('T ambient',$document) && array_key_exists('Diameter',$document)){

				 		//NOTE currently no CA is provided, and we use PM value when inserting CA value
						$document['CA'] = $document['PM'];

					}
		
					try{
					//year must have 4 digits, so the whole date should be 10 chars
					if(strlen($date_str) != 10)
					{
						$failed ++;
						//$errors .= "line ".$fileLine. " error: Date is not valid(".$date_str.")\r\n";
                        file_put_contents($file_name,"line ".$fileLine. " error: Date is not valid(".$date_str.")\r\n",FILE_APPEND);
					}else{
					
						// generate timestamp from date and time fields
						try{
						$dt_str = $date_str . ' ' . $time_str;
						$dt = DateTime::createFromFormat('d/m/Y H:i:s',$dt_str);
						$document['timestamp'] = new MongoDB\BSON\UTCDateTime($dt->getTimestamp()*1000);
						// they requested to add validate status at data from csv files
						}catch(Exception $e){
							file_put_contents($file_name,'here '.$e,FILE_APPEND);
						}
						$existing = $mongo->nano->findOne(array("Station ID" => $document['Station ID'], "timestamp" => $document['timestamp']));
						if($existing) {
							$mongo -> nano -> deleteOne(array("_id" => $existing["_id"]));
						}
						try{
							$insertOneResult = $mongo -> nano -> insertOne($document);
							if($insertOneResult->getInsertedCount() == 0) {
								$failed++;
								if($existing) { 
                                    //$errors .= 'Could not update'; 
                                    file_put_contents($file_name,'Could not update',FILE_APPEND);
                                } else { 
                                    //$errors .= 'Could not insert'; 
                                    file_put_contents($file_name,'Could not insert',FILE_APPEND);
                                }
							} else {
								if($existing) { $updated++; } else { $inserted++; }
							}
						}catch (Exception $e){
							$failed ++;
							if($existing) {
								//$errors .= 'Error updating '.$e;
                                file_put_contents($file_name,'Error updating '.$e,FILE_APPEND);
							} else {
								//$errors .= 'Error inserting '.$e;
                                file_put_contents($file_name,'Error inserting '.$e,FILE_APPEND);
							}
						}
					}

				}catch (\Throwable $e){
					$failed ++;
					//$errors .= "line ".$fileLine. " error: Date/Time is not valid(".$dt_str.")\r\n";
                    file_put_contents($file_name,"line ".$fileLine. " error: Date/Time is not valid(".$dt_str.")\r\n",FILE_APPEND);
				}
					
			}
			else {
						$failed ++;
						//$errors .= "line ".$fileLine. " error: There is not Station with location ".$document['Location']."\r\n";
                        file_put_contents($file_name,"line ".$fileLine. " error: There is not Station with location ".$document['Location']."\r\n",FILE_APPEND);
					}
		}
		else {
						$failed ++;
						//$errors .= "line ".$fileLine. " error : There is not Station with id ".$document['Station ID']."\r\n";
                        file_put_contents($file_name,"line ".$fileLine. " error : There is not Station with id ".$document['Station ID']."\r\n",FILE_APPEND);
				}
		
	
		
		$fileLine++;
		
		}//close the "if" that checked number of columns
		
 	}//closed while here
	fclose($file);

	if($inserted == 0 && $updated == 0 && $failed == 0)
	{
		$response['status'] = 2;
		//$response['errors'] = $errors;
		$response['error'] = 'CSV does not contain any data.';
        output($response);
	}
	
	if($failed == 0)
	{
		$response['status'] = 1;
		$response['inserted'] = $inserted;
		$response['updated'] = $updated;
		//$response['errors'] = $errors;
        file_put_contents($file_name,"Inserted: ".$inserted."   Updated: ".$updated."   Failed: ".$failed."\n",FILE_APPEND);
		$response['log_file'] = $file_name;
        output($response);
	}
	else {
		$response['status'] = 3;
		$response['inserted'] = $inserted;
		$response['updated'] = $updated;
		$response['failed'] = $failed;
		//$response['errors'] = $errors;
        file_put_contents($file_name,"Inserted: ".$inserted."   Updated: ".$updated."   Failed: ".$failed."\n",FILE_APPEND);
		$response['error'] = 'Download analytic report';
		$response['log_file'] = $file_name;
        output($response);
	}
	

	
	function createLogFile($content, $filename)
	{
		$myfile = fopen("logs/$filename.log", "w");
		fwrite($myfile, $content);
		fclose($myfile);
		return "logs/$filename.log";
	}
    function createLogFileSrv($filename)
	{
		$myfile = fopen("logs/$filename.log", "w");
		fwrite($myfile);
	}
	//======================================
	//test function 
	function checkFields($key)
	{
		$errors = '';
		$stationIdField = false;
		$dateField = false;
		$timeField = false;
		$LocationField = false;
		$LatitudeField = false;
		$LongitudeField = false;
		$CommentField = false;
		
		for($i = 0; $i < count($key); $i++) {
			if($key[$i] == "Station ID") {
				$stationIdField = true;
			} else if($key[$i] == "Date") {
				$dateField = true;
			} else if($key[$i] == "Time") {
				$timeField = true;
			} else if($key[$i] == "Location") {
				$LocationField = true;
			} else if($key[$i] == "Latitude") {
				$LatitudeField = true; //Not mandatory yet
			} else if($key[$i] == "Longitude") {
				$LongitudeField = true; //Not mandatory yet
			} else if($key[$i] == "Comment") {
				$CommentField = true; //Not mandatory yet
			} else {
			
				$metricsdb=getMetrics();
				$fieldFound=false;
				
				while ($metdb = $metricsdb->fetch_object()) {
					if($metdb->label == $key[$i] ) {
						$fieldFound=true;
					}				
				}

				if(!$fieldFound) {
					$errors .= 'Field '.$key[$i].' is not a metric';
                    //file_put_contents($log_file,'Field '.$key[$i].' is not a metric',FILE_APPEND);
				}
			}
		}
		
		if($stationIdField == false || $dateField == false || $timeField == false || $LocationField==false)
		{
			$missingFields = '';
			
			if(!$stationIdField)
			{
				$missingFields .= '"Station ID", ';
			}
			if(!$dateField)
			{
				$missingFields .= '"Date", ';
			}
			if(!$timeField)
			{
				$missingFields .= '"Time", ';
			}
			if(!$LocationField)
			{
				$missingFields .= '"Location", ';
			}
			
					
			$errors= 'Missing require field '.rtrim($missingFields,", ");
			//file_put_contents($log_file,'Missing require field '.rtrim($missingFields,", "),FILE_APPEND);
		}
		
		return $errors;
	
	}
	
	//======================================
	
	/*function checkFields($key)
	{
		$errors = '';
		$stationIdField = false;
		$dateField = false;
		$timeField = false;
		$LocationField = false;
		for($i = 0; $i < count($key); $i++)
		{
			if($key[$i] == "Station ID")
			{
				$stationIdField = true;
			}else if($key[$i] == "Date")
			{
				$dateField = true;
			}else if($key[$i] == "Time")
			{
				$timeField = true;
			}else if($key[$i] == "Location")
			{
				$LocationField = true;
			}
			
		}
	
		if($stationIdField == false || $dateField == false || $timeField == false || $LocationField==false)
		{
			$missingFields = '';
			
			if(!$stationIdField)
			{
				$missingFields .= '"Station ID", ';
			}
			if(!$dateField)
			{
				$missingFields .= '"Date", ';
			}
			if(!$timeField)
			{
				$missingFields .= '"Time", ';
			}
			if(!$LocationField)
			{
				$missingFields .= '"Location", ';
			}
			
					
			$errors= 'Missing require field '.rtrim($missingFields,", ");
			
		}
		
		return $errors;
	}*/
	
	
	function output($response)
	{
		echo json_encode($response);
		exit;
	}
	
	
	function startsWith($stationID, $zeros)
	{
		$length = strlen($zeros);
		return (substr($stationID, 0, $length) === $zeros);
	}
	
	// print_r($_FILES);
	// ini_set('display_errors', 1);
	// ini_set('max_execution_time', 600);
	// ini_set('memory_limit', '64M');
// 	
	// // create index
	// $key = ['Station ID' => 1, 'timestamp' => -1, ];
	// $options = ['unique' => true];
	// $mongo->nano->createIndex($key, $options);
	// foreach (glob("station_files/nanodata*.csv") as $filename) {
		// $file = fopen($filename, "r");
// 		
		// // get column names from first row
		// $key = fgetcsv($file, 1000, ",");
		// $numofcols = count($key);
// 
		// // get data from next rows
		// $document = array();
		// $documents = array();
		// while ($value = fgetcsv($file, 1000, ",")){
			// if (count($value)!=$numofcols){
				// echo "file ".$filename." is corrupted.";
				// break;
			// }
// 			
			// $document['timestamp'] = null;
			// for($i=0;$i<$numofcols;$i++){
				// if ($key[$i] == "Date"){
					// $date_str = $value[$i];
				// }elseif ($key[$i] == "Time"){
					// $time_str = $value[$i];
				// }else{
					// $document[$key[$i]] = (is_numeric($value[$i]))?floatval($value[$i]):$value[$i];
				// }
			// }
			// $document['status'] = 'validate';
			// // generate timestamp from date and time fields
			// $dt_str = $date_str . ' ' . $time_str;
			// $dt = DateTime::createFromFormat('d/m/y H:i:s',$dt_str);
			// $document['timestamp'] = new MongoDB\BSON\UTCDateTime($dt->getTimestamp()*1000);
// 			
			// array_push($documents, $document);
		// }
		// fclose($file);
		// print_r($documents);
		// // insert documents to mongo
		// try{
			// $mongo->nano->insertMany($documents, ['ordered' => false]);
		// }catch (Exception $e){
			// // do nothing
			// echo "$e";
		// }
		// echo date('Y-m-d H:i:s')." Imported ".basename($filename)."\n";
// 		
		// // move imported file
		// //rename($filename,'imported_files/'.basename($filename));
?>
