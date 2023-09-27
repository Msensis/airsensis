<?php
	require_once 'helper.php';
	$menuitem = 2;
 		$pipeline = [['$group' => ['_id' => '$Station ID'/* ,
							   'pn_avg' => ['$avg' => '$PN'],
							   'pn_min' => ['$min' => '$PN'],
							   'pn_max' => ['$max' => '$PN'],
							   'date_min' => ['$min' => '$timestamp'],
							   'date_max' => ['$max' => '$timestamp'],
							   'ca_max' => ['$max' => '$CA'],
							   'peca_max' => ['$max' => '$PECa'],
							   'cw_max' => ['$min' => '$CW'],
							   'cs_max' => ['$min' => '$CS'],
							   'pecw_max' => ['$max' => '$PECw'],
							   'pecs_max' => ['$max' => '$PECs'], */
							   ]]];
									
	$stationsmongo = $mongo->nano->aggregate($pipeline);
	/*
	$pipeline2 = [['$group' => ['_id' => '$Station ID',
							   'pn_avg' => ['$avg' => '$PN'],
							   'pn_min' => ['$min' => '$PN'],
							   'pn_max' => ['$max' => '$PN'],
							   'date_min' => ['$min' => '$timestamp'],
							   'date_max' => ['$max' => '$timestamp'],
							   'ca_max' => ['$max' => '$CA'],
							   'peca_max' => ['$max' => '$PECa'],
							   'cw_max' => ['$min' => '$CW'],
							   'cs_max' => ['$min' => '$CS'],
							   'pecw_max' => ['$max' => '$PECw'],
							   'pecs_max' => ['$max' => '$PECs'],
							    'tamp_min' => ['$min' => '$T ambient'],
							   'prsr_min' => ['$min' => '$Pressure'],
							   'pn_min' => ['$min' => '$PN'],
							   'diameter_min' => ['$min' => '$Diameter'],
							   'pm_min' => ['$min' => '$PM'],
							   'winds_min' => ['$min' => '$Wind Speed'],
							   'windd_min' => ['$min' => '$Wind Direction'],
							   'pm10_min' => ['$min' => '$PM10'],
							   'pm25_min' => ['$min' => '$PM2.5'],
							   'o3_min' => ['$min' => '$O3'],
							   'so2_min' => ['$min' => '$SO2'],
							   'nox_min' => ['$min' => '$NOx'],
							   '16var_min' => ['$min' => '$16 VARIOUS'],
							   ]]];
									
	$stationsmongo2 = $mongo->nano->aggregate($pipeline2); */
	
	// deny access to public
	if (!isset($uid)){
		header('Location: index.php');
	}

	if (isset($_REQUEST["station"]) && hasFullAccess($_REQUEST["station"])){
		$station_id = $_REQUEST["station"];
	
		$station = getStation($station_id);
		$station_label = $station->id.'. '.$station->location;
		$station_label1 = $station->id;
		$station_value = $station_id;
	}else{
		$station_label = $dropdown_station_init_label;
		$station_value = "";
		$station_label1= "1";
	}
	
	$stations = getStations();
	$categories = getMetricsCategories();
	
?>
<!DOCTYPE html>
<html>
<head>
<!-- google.charts.load('current', {'packages':['scatter']}); for material scatter-->
	<?php include 'head.php'; ?>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
		
	      
		google.charts.load('current', {'packages':['corechart']});


    </script>
	<script type="text/javascript">
		var dropdown_metric_init_label = '<?php echo $dropdown_metric_init_label;?>';
		moment.updateLocale('en', {
			week: { dow: 1 } // Monday is the first day of the week
		});
		
		$(function () {
			$('.date').datetimepicker({
				format: "DD/MM/YYYY",
				showTodayButton: true
			});
		});
	</script>
</head>
<body>
	<div class="page-wrapper">
		<div class="container main-container">
			<?php include 'top.php'; ?>
			<div class="content-area">
				<div id="parameters">
					<div class="row">
						<div class='col-sm-2'>
							<label>Station:</label>
							 <div id="stations" class="dropdown">
								<button name="statid" class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown" value="<?php echo $station_value;?>"><span class='btn-text'><?php echo 'AirSensis: '.$station_label1;?></span>
								<span class="caret"></span></button>
								
								
								<ul class="dropdown-menu">
									<?php while ($station = $stations->fetch_object()){?>
										<li><a onclick="location.href='data_analysis.php?station=<?php echo $station->id;?>';"  style="cursor:pointer;"  id="<?php echo $station->id;?>"><?php echo 'AirSensis: '.$station->id;?></a></li>
									<?php }?>
								</ul>
								
							</div>
							
							
						</div>
						<div class='col-sm-2'>
							<div id="categories-block"  class="categories-block">
								<label>Category:</label>
								 <div id="categories" class="categories dropdown">
									<button name="metrid" class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown" value=""><span class='btn-text'><?php echo $dropdown_category_init_label;?></span>
									<span class="caret"></span></button>
									<ul class="dropdown-menu">
										<?php while ($category = $categories->fetch_object()){
											$metrics_arr[$category->id] = getCategoryMetrics($category->id);?>
											<li><a id="<?php echo $category->id;?>" href="#"><?php echo $category->name;?></a></li>
										<?php }?>
									</ul>
								</div>
							</div>
						</div>
						<div class='col-sm-2'>
							<div id="metrics-block"  class="metrics-block">
								<label>Metric:</label>
								 <div id="metrics" class="metrics dropdown">
									<button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown" value=""><span class='btn-text'><?php echo $dropdown_metric_init_label;?></span>
									<span class="caret"></span></button>
									<?php foreach ($metrics_arr as $cat_id => $metrics){?>
										<ul id="<?php echo $cat_id;?>" class="dropdown-menu">
										<?php foreach ($metrics as $metric) {
											$metric = (object) $metric;
											if ($metric->presentation == 1) {?>
											<li><a id="<?php echo $metric->id;?>" href="#"><?php echo $metric->label;}?></a></li>
											
										<?php }?>
										</ul>
									<?php }?>
								</div>
								
							</div>
						</div>
						<div class='col-sm-3'>
							<label>From Date:</label>
							<div class="form-group">
								<div class='input-group date' id='from_date'>
									<input type='text' class="form-control" />
									<span class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</span>
								</div>
							</div>
						</div>
						<div class='col-sm-3'>
							<label>To Date:</label>
							<div class="form-group">
								<div class='input-group date' id='to_date'>
									<input type='text' class="form-control" />
									<span class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</span>
								</div>
							</div>
						</div>
					</div>
					<div class="action_buttons">
						<input id="get_data_btn" class="btn btn-default" type="button" value="<?php echo $get_data_btn;?>" onclick="getData()">
					</div>
				</div>

				<div id="tabs_wrapper">
					<!-- Tabs -->
					<ul class="nav nav-tabs">
						<li class="active"><a data-toggle="tab" href="#data_presentation">Data Presentation</a></li>
						<li><a data-toggle="tab" href="#statistics">Statistics</a></li>
						<li><a data-toggle="tab" href="#modeling">Modeling</a></li>
						<li><a data-toggle="tab" href="#health_index">Health Index</a></li>
					</ul>

					<div class="tab-content">
						<!-- Data presentation -->
						<div id="data_presentation" class="tab-pane fade in active">
							<div class="radio_button">
								<table>
									<tr>
										<td>
											<label for="rad1"><input id="rad1" name="presentation_group" type="radio" value="1" />Non validated</label>
										</td>
										<td>
											<label for="rad2"><input id="rad2" name="presentation_group" type="radio" value="2" />Validated</label>
										</td>
										<td>
											<label for="rad3"><input id="rad3" name="presentation_group" type="radio" value="0" checked="checked"  />Both</label>
										</td>
									</tr>
								</table>
							</div>
							<div id="chart_div"></div>
							<div class="action_buttons">
								<input class="btn btn-primary" type="button" value="<?php echo $excel_export_btn;?>" onclick="getDataExcel()">
							</div>
						</div>

						<!-- Statistics -->
						<!-- the following code calculate the values for specified stations -->
					
						<div id="statistics" class="tab-pane fade">
							<table id="statistics_table" class="infotable">
							<tr>
								<th>Station ID</th>
								<th>Location</th>
								<th>Period</th>
								<th>Metric</th>
								<th>Unit</th>
								<th>Statistic</th>
								<th>Value</th>
							</tr>
							<tr>
								<td><span class="station_id"></span></td>
								<td><span class="location"></span></td>
								<td><span class="period"></span></td>
								<td><span class="metric"></span></td>
								<td><span class="unit"></span></td>
								<td>Min</td>
								<td class="number"><span id="min-value"></span></td>
							</tr>
							<tr>
								<td><span class="station_id"></span></td>
								<td><span class="location"></span></td>
								<td><span class="period"></span></td>
								<td><span class="metric"></span></td>
								<td><span class="unit"></span></td>
								<td>Max</td>
								<td class="number"><span id="max-value"></span></td>
							</tr>
							<tr>
								<td><span class="station_id"></span></td>
								<td><span class="location"></span></td>
								<td><span class="period"></span></td>
								<td><span class="metric"></span></td>
								<td><span class="unit"></span></td>
								<td>Average</td>
								<td class="number"><span id="avg-value"></span></td>
							</tr>
							<tr>
								<td><span class="station_id"></span></td>
								<td><span class="location"></span></td>
								<td><span class="period"></span></td>
								<td><span class="metric"></span></td>
								<td><span class="unit"></span></td>
								<td>Variance</td>
								<td class="number"><span id="var-value"></span></td>
							</tr>
							<tr>
								<td><span class="station_id"></span></td>
								<td><span class="location"></span></td>
								<td><span class="period"></span></td>
								<td><span class="metric"></span></td>
								<td><span class="unit"></span></td>
								<td>Standard deviation</td>
								<td class="number"><span id="sdev-value"></span></td>
							</tr>
							</table>
							<div class="action_buttons">
								<input class="btn btn-primary" type="button" value="<?php echo $excel_export_btn;?>" onclick="getStatisticsExcel()">
							</div>
						</div>
						
						<!-- Modeling -->
						<div id="modeling" class="tab-pane fade">
							<div class="function-block">
								<h4>Percentile</h4>
								<table class="function-table">
								<tr>
									<td>
										<label>P:</label>
										<input type="number" id="p" name="p" min="1" max="100">
									</td>
									<td>
										<input class="btn btn-default btn-sm" type="button" value="Calculate >>" onclick="getPercentile()">
									</td>
									<td>
										<span class="result" id="percentile-result"></span>
									</td>
								</tr>
								</table>
							</div>
							<div class="function-block">
								<h4>Correlation</h4>
								<table class="function-table">
								<tr>
									<td>
										<label>Category:</label>
										 <div id="correlation-categories" class="categories ycategories dropdown">
											<button class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown" value=""><span class='btn-text'></span>
											<span class="caret"></span></button>
											<ul class="dropdown-menu">
												<?php $categories->data_seek(0);
													  while ($category = $categories->fetch_object()){?>
													<li><a id="<?php echo $category->id;?>" href="#"><?php echo $category->name;?></a></li>
												<?php }?>
											</ul>
										</div>
									</td>
									<td>
										<label>Metric:</label>
										 <div id="correlation-metrics" class="metrics ymetrics dropdown">
											<button class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown" value=""><span class='btn-text'></span>
											<span class="caret"></span></button>
											<?php foreach ($metrics_arr as $cat_id => $metrics){?>
												<ul id="<?php echo $cat_id;?>" class="<?php echo $cat_id;?> dropdown-menu">
												<?php foreach ($metrics as $metric){
													$metric = (object) $metric;?>
													<li class="<?php echo $metric->id;?>"><a id="<?php echo $metric->id;?>" href="#"><?php echo $metric->label;?></a></li>
												<?php }?>
												</ul>
											<?php }?>
										</div>
									</td>
									<td>
										<input class="btn btn-default btn-sm" type="button" value="Calculate >>" onclick="getCorrelation()">
									</td>
									<td>
										<span class="result" id="correlation-result"></span>
									</td>
								</tr>
								</table>
							</div>
							<div class="function-block">
								<h4>Covariance</h4>
								<table class="function-table">
								<tr>
									<td>
										<label>Category:</label>
										 <div id="covariance-categories" class="categories ycategories dropdown">
											<button class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown" value=""><span class='btn-text'></span>
											<span class="caret"></span></button>
											<ul class="dropdown-menu">
												<?php $categories->data_seek(0);
													  while ($category = $categories->fetch_object()){?>
													<li><a id="<?php echo $category->id;?>" href="#"><?php echo $category->name;?></a></li>
												<?php }?>
											</ul>
										</div>
									</td>
									<td>
										<label>Metric:</label>
										 <div id="covariance-metrics" class="metrics ymetrics dropdown">
											<button class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown" value=""><span class='btn-text'></span>
											<span class="caret"></span></button>
											<?php foreach ($metrics_arr as $cat_id => $metrics){?>
												<ul id="<?php echo $cat_id;?>" class="<?php echo $cat_id;?> dropdown-menu">
												<?php foreach ($metrics as $metric){
													$metric = (object) $metric;?>
													<li class="<?php echo $metric->id;?>"><a id="<?php echo $metric->id;?>" href="#"><?php echo $metric->label;?></a></li>
												<?php }?>
												</ul>
											<?php }?>
										</div>
									</td>
									<td>
										<input class="btn btn-default btn-sm" type="button" value="Calculate >>" onclick="getCovariance()">
									</td>
									<td>
										<span class="result" id="covariance-result"></span>
									</td>
								</tr>
								</table>
							</div>
							<div class="function-block">
								<h4>Forecast</h4>
								<table class="function-table">
								<tr>
									<td>
										<label>Category:</label>
										 <div id="forecast-categories" class="categories ycategories dropdown">
											<button class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown" value=""><span class='btn-text'></span>
											<span class="caret"></span></button>
											<ul class="dropdown-menu">
												<?php $categories->data_seek(0);
													  while ($category = $categories->fetch_object()){?>
													<li><a id="<?php echo $category->id;?>" href="#"><?php echo $category->name;?></a></li>
												<?php }?>
											</ul>
										</div>
									</td>
									<td>
										<label>Metric:</label>
										 <div id="forecast-metrics" class="metrics ymetrics dropdown">
											<button class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown" value=""><span class='btn-text'></span>
											<span class="caret"></span></button>
											<?php foreach ($metrics_arr as $cat_id => $metrics){?>
												<ul id="<?php echo $cat_id;?>" class="<?php echo $cat_id;?> dropdown-menu">
												<?php foreach ($metrics as $metric){
													$metric = (object) $metric;?>
													<li class="<?php echo $metric->id;?>"><a id="<?php echo $metric->id;?>" href="#"><?php echo $metric->label;?></a></li>
												<?php }?>
												</ul>
											<?php }?>
										</div>
									</td>
									<td>
										<label><span id="x_name"></span> Value:</label>
										<input type="number" id="x" name="x" step="0.00001" >
									</td>
									<td>
										<input class="btn btn-default btn-sm" type="button" value="Calculate >>" onclick="getForecast()">
									</td>
									<td>
										<span class="result" id="forecast-result"></span>
									</td>
								</tr>
								</table>
							</div>
						</div>
						
						<!-- Risk -->
						<div id="health_index" class="tab-pane fade">
								<!--<div class="function-block">
								<h4>PECS</h4>
					<?php if (!isset($_REQUEST["station"])){ ?>
								<p>For showing specific station's PECS you have to choose it from the map in home page</p>
								 <div id="stations" >
									<button name="choice" class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown" value="<?php echo $station_value;?>"><span class='btn-text'><?php echo $station_label;?></span></button>
								
								 </div>
							<script>
									(function (){
													var radios = document.getElementsByName('choice');
													console.log(radios);
													for(var i = 0; i < radios.length; i++){
														radios[i].onclick = function(){
															var stid= this.value;
															 window.location.href = "http://65.52.128.15/airsensis/data_analysis.php?station=" + stid;
														}
													}
												})();
							</script>
								<p id="choiceLabel"></p>
								<?php
								}
								else{
								?>
									
								<?php 
								
									foreach ($stationsmongo as $stationmongo){
										
									if($stationmongo->_id == $station_label1){
										
										?>
									
								 
								
									
								 	

								
								<table class="function-table">
								<tr>
									<td>
										<label>PECa:</label>
									</td>
									<td>
										<span id="peca"></span>
									</td>
								</tr>
								<tr>
									<td>
										<label>PECw:</label>
									</td>
									<td>
										<span id="pecw"></span>
									</td>
								</tr>
								<tr>
									<td>
										<label>PECs:</label>
									</td>
									<td>
										<span id="pecs"></span>
									</td>
								</tr>
								
								</table>
								
								<?php
								}}}
								
								?>
							</div> -->
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php include 'footer.php'; ?>
	</div>
	
</body>
</html>
