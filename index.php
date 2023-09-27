<?php
	require_once 'helper.php';
	$menuitem = 1;
	$options = array("allowDiskUse" => true);
	$now = new \MongoDB\BSON\UTCDateTime(strtotime('now') * 1000+10800000);
	$now_date=strtotime('now');
	$yes=strtotime('-1 day',strtotime('now'))* 1000+10800000;
	$yesterday = new \MongoDB\BSON\UTCDateTime($yes);
	$pipeline = [
	['$match'=>['timestamp'=>['$lt'=>$now,'$gt'=>$yesterday]]],
	['$sort'=>['timestamp'=>1]],
	['$group' => ['_id' =>'$Station ID','data'=>['$push'=>'$pm25'],'timestamps'=>['$push'=>'$timestamp']]]
	];
	$mongo->nano->createIndex(array('timestamp' => 1));
	$stations = $mongo->nano->aggregate($pipeline,$options);
?>

<!DOCTYPE html>
<html>
<head>
	<?php include 'head.php';  ?>

	<script>
		var offset = new Date().getTimezoneOffset();
		var map;
		var markers = [];
		var activeWindow;

		function initMap() {
			map = new google.maps.Map(document.getElementById('map'), {
		  		center: {lat: 38.169815, lng: 22.752736},
		  		zoom: 9
			});

		<?php 
			// Comparison function 
			foreach ($stations as $station) {
				//station must have many values because we compute hourly average but how many?
				$times_tmp = (array)$station->timestamps;

				if (count((array)$station->data)>10) {
					$pm25_avgs = array();
					$pm25_values = array();
					$timestmps = array();
					$tt = array();

					if(isset($station->data)) {
						$pm25_vals = (array)$station->data;
						$times = (array)$station->timestamps;

						for ($i = 0; $i <count($pm25_vals); $i++) {
							array_push($pm25_values,$pm25_vals[$i]);
							array_push($tt,[$times[$i]->toDateTime(),$pm25_vals[$i]]);
						}

						// Sort the array  
						usort($tt, 'date_compare');
						$avgs_pm25=[];
						$res=[];
						$tim=[];

						for ($i = 0; $i <count($tt); $i++) {
							//take the hour part e.g. 13 from 13:25:36
							$str_cpm_1=$tt[$i][0]->format('H:i:s')[0];
							$str_cpm_2=$tt[$i][0]->format('H:i:s')[1];
							$str_cpm=$str_cpm_1.$str_cpm_2;
							//take the hour part from next datapoint
							$str_cpm_1_n=$tt[$i+1][0]->format('H:i:s')[0];
							$str_cpm_2_n=$tt[$i+1][0]->format('H:i:s')[1];
							$str_cpm_n=$str_cpm_1_n.$str_cpm_2_n;
							//while the hour part from previous and next is the same gather pm2.5 values
							array_push($avgs_pm25,$tt[$i][1]);
							//when the hour changes average the pm2.5 values
							if (strcmp($str_cpm, $str_cpm_n) !== 0) {
								//pm2.5 value
								array_push($res,round(array_sum($avgs_pm25)/count($avgs_pm25),1));
								//full hour. E.g the value at 12:00 in graph is the average of 11:00 and 12:00
								array_push($tim,$tt[$i+1][0]->format('H:i:s'));
								$avgs_pm25=[];
							}
							if ($i==count($tt)-2) {
								break;
							}
						}

						$pm25_avg = array_sum($pm25_values)/count($pm25_values);

					} else {
						$pm25_values = 0;
						$$pm25_avg = 0;
					}


					array_push($pm25_avgs, $pm25_avg); 

					$station_info = getStation($station->_id);
					if ($station_info != null) {
						$minDate = date("d/m/Y H:i",strtotime('-1 day',strtotime('+3 hours',strtotime('now'))));
						$maxDate =date("d/m/Y H:i",strtotime('+3 hours',strtotime('now')));
						//fix for ERLab and ERLab2 wich are at the same location
			        	if ($station->_id=="2") {
							//$new_latitude=dms2dd($station_info->latitude);
							//$new_latitude=$new_latitude-0.5;
							 $new_latitude="37:59:26.0 N";
						} else { 
							$new_latitude=$station_info->latitude;
						}
						
		?>	
						var latLng = new google.maps.LatLng({lat: <?php echo dms2dd($new_latitude);?>, lng: <?php echo dms2dd($station_info->longitude);?>});
						
						//create Json Object station 
						var station_id = <?php echo $station->_id;?> ;
						var location = '<?php echo $station_info->location;?>' ;
						var comment = '<?php echo $station_info->comment;?>';
						var pm25 = <?php echo json_encode($res);?> ;
						var timestamps = <?php echo json_encode($tim);?> ;
						var pm25_avg = <?php echo $pm25_avg;?> ;
						var pm25_last_value = <?php echo end($pm25_values);?> ;
						var date_min = '<?php echo $minDate;?>' ;
						var date_max = '<?php echo $maxDate;?>' ;
						<?php if (hasFullAccess($station->_id)) { ?>
							var fullAccess = true;
							var contact_name = '<?php echo $station_info->contact_name;?>';
							var contact_email = '<?php echo $station_info->contact_email;?>';
						<?php } else { ?>
							var fullAccess = false;
							var contact_name = '';
							var contact_email = '';
						<?php } ?>
						var station = {
							'station_id': station_id ,
							'location': location ,
							'comment': comment ,
							'pm25': pm25 ,
							'timestamps': timestamps ,
							'pm25_avg': pm25_avg ,
							'pm25_last_value': pm25_last_value ,
							'date_min': date_min ,
							'date_max': date_max,
							'fullAccess': fullAccess,
							'contact_name': contact_name,
							'contact_email': contact_email
						};
						placeMarkerOnMap(latLng, station);

						var contentString = 
        	'<div id="info">'+
				  	'<h2>' + station.location + '</h2>' +
				  	'<p>' + station.comment + '<br />' +
						'Sample period: ' + station.date_min + ' - ' + station.date_max +
				  	'</p>' +
				  	'<p>' +
						'PM2.5 Avg: ' + parseFloat(station.pm25_avg).toFixed(1) + ' μg/m3<br />'+
				  	'</p>' +
				  '<p>' +
					'Latest PM2.5 Value: ' + parseFloat(station.pm25_last_value).toFixed(1) + ' μg/m3<br />'+
				  '</p>' +
			  '</div>' +
			  '<div id="curve_chart" style="width: 350px; height: 200px"></div>';

		if (station.fullAccess) {
			contentString += 
				'<div class="action_buttons">'+
					'<a class="btn btn-primary" href="data_analysis.php?station=' + station.station_id + '">Data Analysis</a>'+
				'</div>';
		}



		<?php	    
					} 
				} //if (count($times_tmp)>10)
			} //foreach ($stations as $station)

			function date_compare($element1, $element2) { 
			    $datetime1 = strtotime($element1[0]->format('Y-m-d H:i:s')); 
			    $datetime2 = strtotime($element2[0]->format('Y-m-d H:i:s')); 
			    return $datetime1 - $datetime2; 
			}  
		?>

		var markerCluster = new markerClusterer.MarkerClusterer({ map, markers });

	} //initMap()


	function getCircle(sid) {			
		if(sid == 7){
			var circle = {
		  		url: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png",
		  		anchor:new google.maps.Point(0, 10)  
			};
		} else {
			var circle = {
		   		url: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png",
			};
		}		
		return circle;
	}

	function placeMarkerOnMap (latLng, station) {
        var marker = new google.maps.Marker({
            position: latLng,
            icon: getCircle(station.station_id)
        });

        var contentString = 
        	'<div id="info">'+
				  	'<h2>' + station.location + '</h2>' +
				  	'<p>' + station.comment + '<br />' +
						'Sample period: ' + station.date_min + ' - ' + station.date_max +
				  	'</p>' +
				  	'<p>' +
						'PM2.5 Avg: ' + parseFloat(station.pm25_avg).toFixed(1) + ' μg/m3<br />'+
				  	'</p>' +
				  '<p>' +
					'Latest PM2.5 Value: ' + parseFloat(station.pm25_last_value).toFixed(1) + ' μg/m3<br />'+
				  '</p>' +
			  '</div>' +
			  '<div id="curve_chart" style="width: 350px; height: 200px"></div>';

		if (station.fullAccess) {
			contentString += 
				'<div class="action_buttons">'+
					'<a class="btn btn-primary" href="data_analysis.php?station=' + station.station_id + '">Data Analysis</a>'+
				'</div>';
		}

        marker.info = new google.maps.InfoWindow({
            content: contentString
        });

        markers.push(marker);

        google.maps.event.addListener(marker, 'mouseover', function() {
        	if(activeWindow != null) activeWindow.close();
            //Open new window
            marker.info.open(map, marker);
            activeWindow = marker.info;

            if (document.getElementById('curve_chart') != null)
        		drawChart(station.pm25,station.timestamps);

        });

        google.maps.event.addListener(marker, 'click', function() {
        	if(activeWindow != null) activeWindow.close();
            //Open new window
            marker.info.open(map, marker);
            activeWindow = marker.info;

            if (document.getElementById('curve_chart') != null)
        		drawChart(station.pm25,station.timestamps);

        });
    }

	</script>
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      	google.charts.load('current', {packages: ['corechart', 'line']});
		String.prototype.replaceAt = function(index, replacement) {
    		return this.substr(0, index) + replacement + this.substr(index + replacement.length);
		}

      	function drawChart(pm25,time) {
	 		var data = new google.visualization.DataTable();
	   		data.addColumn('string', 'time');
			data.addColumn('number', 'pm2.5');
	        for (var i = 0; i < pm25.length; i++) {
	           data.addRow([time[i].replaceAt(3,'00   '),pm25[i]]);
	        }
        	var options = {
	          title: 'Hourly pm2.5 mean',
	          curveType: 'function',
	          legend: { position: 'none' },
		  		hAxis: {slantedText:true, slantedTextAngle:90 }
    		};

        	var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

        	chart.draw(data, options);
      	}
    </script>

</head>
<body>
	<div class="page-wrapper">
		<div class="container main-container">
			<?php include 'top.php'; ?>
			<div id="map"></div>
		</div>
		<?php include 'footer.php'; ?>
	</div>


	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDlr1xYFnStLc3Pl7ip3rZJSTzS0nKzb5I&callback=initMap" async defer></script>
</body>
</html>	