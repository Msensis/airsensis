var host = '10.199.0.55';
var globalData;
//for material scatter
// google.charts.load('current', {'packages':['scatter']});

//for classic scatter
//google.charts.load('current', {'packages':['corechart']});
$(document).ready(function(){
	if ($('#stations .btn').val() != ''){
		$('#categories-block').show();
	}
	// main dataset selection
	$('#stations li a').click(function(e){
		e.preventDefault();
		if ($(this).attr('id') != $('#stations .btn').val()){
			$('#stations .btn').val($(this).attr('id'));
			$('#stations .btn-text').text($(this).text());
			$('#categories-block').show();
			$("#tabs_wrapper").hide();
			$('.nav-tabs a[href="#data_presentation"]').tab('show');
			$('#get_data_btn').show();
		}
	});
	$('#categories li a').click(function(e){
		e.preventDefault();
		cat_id = $(this).attr('id');
		if (cat_id != $('#categories .btn').val()){
			$('#categories .btn').val(cat_id);
			$('#categories .btn-text').text($(this).text());
			$('#metrics ul').addClass('hidden');
			$('#metrics ul').removeClass('dropdown-menu');
			$('#metrics ul#' + cat_id).removeClass('hidden');
			$('#metrics ul#' + cat_id).addClass('dropdown-menu');
			$('#metrics .btn').val('');
			$('#metrics .btn-text').text(dropdown_metric_init_label);
			$('#metrics-block').show();
			$("#tabs_wrapper").hide();
			$('.nav-tabs a[href="#data_presentation"]').tab('show');
			$('#get_data_btn').show();
		}
	});
	$('#metrics li a').click(function(e){
		e.preventDefault();
		if ($(this).attr('id') != $('#metrics .btn').val()){
			$('#metrics .btn').val($(this).attr('id'));
			$('#metrics .btn-text').text($(this).text());
			$("#tabs_wrapper").hide();
			$('.nav-tabs a[href="#data_presentation"]').tab('show');
			$('#get_data_btn').show();
		}
	});
	$('.date').on('dp.change', function (e) {
		if (e.oldDate!=null && e.date.toDate().toDateString() != e.oldDate.toDate().toDateString()){
			$("#tabs_wrapper").hide();
			$('.nav-tabs a[href="#data_presentation"]').tab('show');
			$('#get_data_btn').show();
		}
	});
	
	// corralation dataset selection
	$('#correlation-categories li a').click(function(e){
		e.preventDefault();
		cat_id = $(this).attr('id');
		$('#correlation-categories .btn').val(cat_id);
		$('#correlation-categories .btn-text').text($(this).text());
		$('#correlation-metrics ul').addClass('hidden');
		$('#correlation-metrics ul').removeClass('dropdown-menu');
		$('#correlation-metrics ul#' + cat_id).removeClass('hidden');
		$('#correlation-metrics ul#' + cat_id).addClass('dropdown-menu');
		$('#correlation-metrics .btn').val('');
		$('#correlation-metrics .btn-text').text(dropdown_metric_init_label);
	});
	$('#correlation-metrics li a').click(function(e){
		e.preventDefault();
		$('#correlation-metrics .btn').val($(this).attr('id'));
		$('#correlation-metrics .btn-text').text($(this).text());
	});

	// covariance dataset selection
	$('#covariance-categories li a').click(function(e){
		e.preventDefault();
		cat_id = $(this).attr('id');
		$('#covariance-categories .btn').val(cat_id);
		$('#covariance-categories .btn-text').text($(this).text());
		$('#covariance-metrics ul').addClass('hidden');
		$('#covariance-metrics ul').removeClass('dropdown-menu');
		$('#covariance-metrics ul#' + cat_id).removeClass('hidden');
		$('#covariance-metrics ul#' + cat_id).addClass('dropdown-menu');
		$('#covariance-metrics .btn').val('');
		$('#covariance-metrics .btn-text').text(dropdown_metric_init_label);
	});
	$('#covariance-metrics li a').click(function(e){
		e.preventDefault();
		$('#covariance-metrics .btn').val($(this).attr('id'));
		$('#covariance-metrics .btn-text').text($(this).text());
	});

	// forecast dataset selection
	$('#forecast-categories li a').click(function(e){
		e.preventDefault();
		cat_id = $(this).attr('id');
		$('#forecast-categories .btn').val(cat_id);
		$('#forecast-categories .btn-text').text($(this).text());
		$('#forecast-metrics ul').addClass('hidden');
		$('#forecast-metrics ul').removeClass('dropdown-menu');
		$('#forecast-metrics ul#' + cat_id).removeClass('hidden');
		$('#forecast-metrics ul#' + cat_id).addClass('dropdown-menu');
		$('#forecast-metrics .btn').val('');
		$('#forecast-metrics .btn-text').text(dropdown_metric_init_label);
	});
	$('#forecast-metrics li a').click(function(e){
		e.preventDefault();
		$('#forecast-metrics .btn').val($(this).attr('id'));
		$('#forecast-metrics .btn-text').text($(this).text());
	});

	// add asterisc after required fields
	$('.required').after('<span class="required">*</span>');
	
	// initialize chosen selects
	$('.chosen-select').chosen({disable_search:true});
	
	$('input[type=radio][name=presentation_group]').change(function() {
		  drawChartMode(globalData, this.value);
    });

});

// Show message
function showMessage(msg, type){
	msg_box_class = (type == 'success')?'bg-success':'bg-danger';
	$("#msg_box").text(msg);
	$("#msg_box").removeClass();
	$("#msg_box").addClass(msg_box_class);
	$("#msg_box").fadeIn();
	setTimeout(function(){ $("#msg_box").fadeOut("slow"); }, 25000);
}

// Data Analysis
function validateDataInput(){
	var station_id = $('#stations .btn').val();
	if (station_id == ""){
		showMessage("Please select station.","fail");
		return false;
	}

	var cat_id = $('#categories .btn').val();
	if (cat_id == ""){
		showMessage("Please select category.","fail");
		return false;
	}

	var metric_id = $('#metrics .btn').val();
	if (metric_id == ""){
		showMessage("Please select metric.","fail");
		return false;
	}

	var from_date = $('#from_date').data("DateTimePicker").date();
	var to_date = $('#to_date').data("DateTimePicker").date();
	if (from_date == null || to_date == null){
		showMessage("Please select dates.","fail");
		return false;
	}
	from_date = from_date.toDate();
	to_date = to_date.toDate();
	from_date.setHours(0);
	from_date.setMinutes(0);
	from_date.setSeconds(0);
	from_date.setMilliseconds(0);
	to_date.setHours(0);
	to_date.setMinutes(0);
	to_date.setSeconds(0);
	to_date.setMilliseconds(0);
	if (from_date > to_date){
		showMessage("Date period not properly defined!","fail");
		return false;
	}
	if (to_date > Date.now()){
		showMessage("Future data not available!","fail");
		return false;
	}
	
	return true;
}

function getData(){
		//sendDataAnalysis();
	// validate input data
	if (!validateDataInput()) return;
	
	var station_id = $('#stations .btn').val();
	var metric_id = $('#metrics .btn').val();
	var from_date = $('#from_date').data("DateTimePicker").date();
	//console.log(from_date);//Wed Oct 03 2018 00:00:00 GMT+0300 (Eastern European Summer Time)
	var to_date = $('#to_date').data("DateTimePicker").date();
	from_date = from_date.toDate();
	to_date = to_date.toDate();
	date_from = from_date.getFullYear() + '-' + (from_date.getMonth() + 1) + '-' + from_date.getDate();
	date_to = to_date.getFullYear() + '-' + (to_date.getMonth() + 1) + '-' + to_date.getDate();
	var offset = new Date().getTimezoneOffset();
	
	var params = {'station': station_id,
				  'metric': metric_id,
				  'date_from': date_from,
				  'date_to': date_to,
				  'browser_offset':offset};
				  console.log(params);

	// get data
	$.ajax({
		dataType: "json",
		url: "get_data.php",
		data: params
	})
	.done(function(data){
		globalData = data;
		console.log(data);
		updateDataView(data);
	})
	.fail(function(data) {
		showMessage(data['status'],"fail");
	});

}
//sent parameters in page data analysis
function sendDataAnalysis(){
	// validate input data
	if (!validateDataInput()) return;

	var station_id = $('#stations .btn').val();
	var metric_id = $('#metrics .btn').val();


	var params = 'station=' + encodeURIComponent(station_id) +
				 '&metricID=' + encodeURIComponent(metric_id) ;
	
	window.location.href = "data_analysis.php?"+ params;
	//getData();
}


function updateDataView(json){
	if (json["status"] != "success"){
		showMessage(json["error_msg"],"fail");
		return;
	}
	$("#tabs_wrapper").show();
	resetModelingFunctions(json);
	updateStatistics(json);
	updatePecs(json);
	drawChart(json);
	$('#get_data_btn').hide();
}
function drawChart(json) {
	
	//console.log(json['test']);//"2018-10-02 21:00:00.000000"   +"+00:00"
    //console.log(json["data"]["non validated"][0]["timestamp"])
	station = json["station"];
	metric = json["metric"];
	from_date_str = json["date_from"];
	to_date_str = json["date_to"];
	
	from_date_full_str = json["date_from_full"].replace(/-/g, "/");
	//test=new Date(from_date_full_str)

	to_date_full_str = json["date_to_full"].replace(/-/g, "/");

	var rows = [];
	$.each( json["data"]["non validated"], function( index, item ) {
		row = [new Date(item["timestamp"].replace(/-/g, "/")), item["value"], null];
		rows.push(row);
	});
	
	$.each( json["data"]["validated"], function( index, item ) {
		row = [new Date(item["timestamp"].replace(/-/g, "/")), null,  item["value"]];
		rows.push(row);
	});
	
	var data = new google.visualization.DataTable();
	data.addColumn('datetime', 'time');
	data.addColumn('number', 'Non validated');
	data.addColumn('number', 'Validated');
	
	data.addRows(rows);

	/* var flags = [], output = [], l = rows.length, i;
	for( i=0; i<l; i++) {
		if( flags[rows[i][0]]) continue;
		flags[rows[i][0]] = true;
		output.push(rows[i][0]);
	}  
	console.log(output);
	output.sort(function compare(a, b) {
		var dateA = new Date(a.date);
		var dateB = new Date(b.date);
		return dateA - dateB;
	});

	console.log(output);
	output.sort();
	console.log(output.sort()); 
	var splited, onlyDate,newOutput=[],hours;
	splited=output[0].toString().split(" ");
	//console.log(output[0]);
	//console.log(splited);
	hours=splited[4].toString().split(':');
	//console.log(hours);
	newFromDate=new Date();
	newFromDate.setFullYear(splited[3]);
	newFromDate.setMonth(11);
	newFromDate.setDate(splited[2]);
	newFromDate.setHours(hours[0].toString(),hours[1].toString(),hours[2].toString());

	console.log(newFromDate);
	
	var splited2, onlyDate2,newOutput2=[],hours2;
	splited2=output[(output.length)-1].toString().split(" ");
	//console.log(output[(output.length)-1]);
	//console.log(splited2);
	hours2=splited2[4].toString().split(':');
	//console.log(hours2);
	newToDate=new Date();
	newToDate.setFullYear(splited2[3]);
	newToDate.setMonth(1);
	newToDate.setDate(splited2[2]);
	newToDate.setHours(hours2[0].toString(),hours2[1].toString(),hours2[2].toString());

	console.log(newToDate);
 */
	
	/* for (j=0; j<output.length;j++){
		console.log(output[j]);
		splited=(output[j]+'').split(" ");
		console.log(splited);
		onlyDate=splited[1]+" "+splited[2]+" "+splited[3]+" "+splited[4];
		newOutput.push(onlyDate);
		console.log(newOutput[j]);
	} */
	
	
	

	var options = {
	  height: 400,
	  colors: ['#286090','#83C03E'],
	  title: station["location"] + "\n" + from_date_str+ " - "+to_date_str+"\n\n",
		titleTextStyle:{
			fontName: 'Helvetica Neue,Helvetica,Arial,sans-serif',
			fontSize:'16',
			color:'grey',
			bold: false,
		},
	 hAxis: {
		 title: "Time",
		 //ticks: output,
		 //format: 'M/d/yy',
            viewWindow: {
            min: new Date(from_date_full_str),
            max: new Date(to_date_full_str)
          },    
        gridlines: {
			count:-1,
             units: {
	            	 years: {format: 'yyyy'},
	            	 months: {format: 'MM/yyyy'},
                 days: {format: ['dd/MM']},
             hours: {format: ['HH:mm']},
			   minutes: {format: ['m:ss']},
			   seconds:{format: ['ss']}
           }
          }, 
          minorGridlines: {
            units: {
				years: {format: 'yyyy'},
	            	 months: {format: 'MM/yyyy'},
                 days: {format: ['dd/MM']},
              hours: {format: ['HH']},
              minutes: {format: ['mm']},
			 seconds:{format: ['ss']}
            }
          } 
        },
	  vAxis: {title: metric['field_name']+'  ('+metric['unit']+')'}
	};
    console.log("after")
	var chart = new google.visualization.ScatterChart(document.getElementById('chart_div'));
	
	//for material scatter
	//var chart = new google.charts.Scatter(document.getElementById('chart_div'));
	//chart.draw(data, google.charts.Scatter.convertOptions(options));
	
	var formatter = new google.visualization.DateFormat({pattern: 'd/MM/yyyy hh:m:s'});
	formatter.format(data, 0);
	
	
	chart.draw(data, options);
	
	
	
}

function drawChartMode(json, mode) {
	station = json["station"];
	metric = json["metric"];
	from_date_str = json["date_from"];
	to_date_str = json["date_to"];
	
	from_date_full_str = json["date_from_full"];
	to_date_full_str = json["date_to_full"];

	var rows = [];
	if(mode == 1 || mode == 0)
	{
		$.each( json["data"]["non validated"], function( index, item ) {
		
			row = [new Date(item["timestamp"]), item["value"], null];
			rows.push(row);
		});
	}
	
	if(mode == 2 || mode == 0)
	{
		$.each( json["data"]["validated"], function( index, item ) {
		
			row = [new Date(item["timestamp"]), null,  item["value"]];
			rows.push(row);
		});
	}
	
	var data = new google.visualization.DataTable();
	data.addColumn('datetime', 'time');
	data.addColumn('number', 'Non validated');
	data.addColumn('number', 'Validated');
	
	data.addRows(rows);

	var options = {
	  height: 400,
	  colors: ['#286090','#83C03E'],
	  chart: {
		title: station["location"],
		subtitle: from_date_str + ' - ' + to_date_str
	  },
	  hAxis: {title: 'Time',		  
		  format: 'd/MM/yyyy hh:mm:ss',
		  viewWindowMode: 'Explicit',
	      viewWindow: {min: new Date(from_date_full_str),
	                   max: new Date(to_date_full_str)}
  
      },
	  vAxis: {title: metric['label']+'  ('+metric['unit']+')', format: 'decimal'}
	};

	//        var chart = new google.visualization.ScatterChart(document.getElementById('chart_div'));
	var chart = new google.charts.Scatter(document.getElementById('chart_div'));

	var formatter = new google.visualization.DateFormat({pattern: 'd/MM/yyyy hh:m:s'});
	formatter.format(data, 0);
	
	//        chart.draw(data, options);
	chart.draw(data, google.charts.Scatter.convertOptions(options));
}

function updateStatistics(json) {
	station = json["station"];
	metric = json["metric"];
	from_date_str = json["date_from"];
	to_date_str = json["date_to"];
	min = json["min"];
	max = json["max"];
	avg = json["avg"];
	variance = json["var"];
	sdev = json["sdev"];
	
	$("#statistics span.station_id").text(station['id']);
	$("#statistics span.location").text(station['location']);
	$("#statistics span.period").text(from_date_str + ' - ' + to_date_str);
	$("#statistics span.metric").text(metric['label']);
	$("#statistics span.unit").text(metric['unit']);
	$("#statistics span#min-value").text(min.toFixed(5));
	$("#statistics span#max-value").text(max.toFixed(5));
	$("#statistics span#avg-value").text(avg.toFixed(5));
	$("#statistics span#var-value").text(variance.toFixed(5));
	$("#statistics span#sdev-value").text(sdev.toFixed(5));
	
}

function updatePecs(json) {
	peca = json["peca"];
	pecw = json["pecw"];
	pecs = json["pecs"];
	$("#risk span#peca").text(peca);
	$("#risk span#pecw").text(pecw);
	$("#risk span#pecs").text(pecs);

}

function getDataExcel(){
	// validate input data
	if (!validateDataInput()) return;

	var station_id = $('#stations .btn').val();
	var metric_id = $('#metrics .btn').val();
	var from_date = $('#from_date').data("DateTimePicker").date();
	var to_date = $('#to_date').data("DateTimePicker").date();
	from_date = from_date.toDate();
	to_date = to_date.toDate();
	date_from = from_date.getFullYear() + '-' + (from_date.getMonth() + 1) + '-' + from_date.getDate();
	date_to = to_date.getFullYear() + '-' + (to_date.getMonth() + 1) + '-' + to_date.getDate();

	var params = 'station=' + encodeURIComponent(station_id) +
				 '&metric=' + encodeURIComponent(metric_id) +
				 '&date_from=' + encodeURIComponent(date_from) +
				 '&date_to=' + encodeURIComponent(date_to);
	
	window.location.href = "get_data_excel.php?" + params;
}
function getStatisticsExcel(){
	// validate input data
	if (!validateDataInput()) return;

	var station_id = $('#stations .btn').val();
	var metric_id = $('#metrics .btn').val();
	var from_date = $('#from_date').data("DateTimePicker").date();
	var to_date = $('#to_date').data("DateTimePicker").date();
	from_date = from_date.toDate();
	to_date = to_date.toDate();
	date_from = from_date.getFullYear() + '-' + (from_date.getMonth() + 1) + '-' + from_date.getDate();
	date_to = to_date.getFullYear() + '-' + (to_date.getMonth() + 1) + '-' + to_date.getDate();

	var params = 'station=' + encodeURIComponent(station_id) +
				 '&metric=' + encodeURIComponent(metric_id) +
				 '&date_from=' + encodeURIComponent(date_from) +
				 '&date_to=' + encodeURIComponent(date_to);
	
	window.location.href = "get_statistics_excel.php?" + params;
}

/* Modeling */
function resetModelingFunctions(json){
	cat_id = $('#categories .btn').val();
	cat_txt = $('#categories .btn-text').text();
	metric_id = json["metric"]["id"];
	$('.ycategories .btn').val(cat_id);
	$('.ycategories .btn-text').text(cat_txt);
	$('.ymetrics ul').addClass('hidden');
	$('.ymetrics ul').removeClass('dropdown-menu');
	$('.ymetrics ul.' + cat_id).removeClass('hidden');
	$('.ymetrics ul.' + cat_id).addClass('dropdown-menu');
	$('.ymetrics ul li').removeClass('hidden');
	$('.ymetrics ul li.' + metric_id).addClass('hidden');
	$('.ymetrics .btn').val('');
	$('.ymetrics .btn-text').text(dropdown_metric_init_label);
	$('.result').text('');
	$('span#x_name').text(json["metric"]["label"]);
	$('input#x').attr('min',json["metric"]["min"]);
	$('input#x').attr('max',json["metric"]["max"]);
}
function getPercentile(){
	// validate input data
	if (!validateDataInput()) return;

	// check percentile input validity
	if ($('#p').val()=="" || !$('#p')[0].checkValidity()){
		showMessage("Percentile must be an integer between 1 and 100.","fail");
		return;
	}
	var p = $('#p').val();
	var station_id = $('#stations .btn').val();
	var metric_id = $('#metrics .btn').val();
	var from_date = $('#from_date').data("DateTimePicker").date();
	var to_date = $('#to_date').data("DateTimePicker").date();
	from_date = from_date.toDate();
	to_date = to_date.toDate();
	date_from = from_date.getFullYear() + '-' + (from_date.getMonth() + 1) + '-' + from_date.getDate();
	date_to = to_date.getFullYear() + '-' + (to_date.getMonth() + 1) + '-' + to_date.getDate();
	var params = {'station': station_id,
				  'metric': metric_id,
				  'date_from': date_from,
				  'date_to': date_to,
				  'function': 'percentile',
				  'p': p};
	$.ajax({
		dataType: "json",
		url: "get_modeling_calculation.php",
		data: params
	})
	.done(function(data){
		if (data["status"] != "success"){
			showMessage(data["error_msg"],"fail");
			return;
		}
		$('#percentile-result').text(data["result"]);
	})
	.fail(function() {
		showMessage("ajax error.","fail");
	});
}
function getCorrelation(){
	// validate input data
	if (!validateDataInput()) return;

	var station_id = $('#stations .btn').val();
	var metric_id = $('#metrics .btn').val();
	var metric_y_id = $('#correlation-metrics .btn').val();
	if (metric_y_id == ""){
		showMessage("Please select metric for correlation.","fail");
		return;
	}
	var from_date = $('#from_date').data("DateTimePicker").date();
	var to_date = $('#to_date').data("DateTimePicker").date();
	from_date = from_date.toDate();
	to_date = to_date.toDate();
	date_from = from_date.getFullYear() + '-' + (from_date.getMonth() + 1) + '-' + from_date.getDate();
	date_to = to_date.getFullYear() + '-' + (to_date.getMonth() + 1) + '-' + to_date.getDate();
	var params = {'station': station_id,
				  'metric': metric_id,
				  'metric_y': metric_y_id,
				  'date_from': date_from,
				  'date_to': date_to,
				  'function': 'correlation'};
	$.ajax({
		dataType: "json",
		url: "get_modeling_calculation.php",
		data: params
	})
	.done(function(data){
		if (data["status"] != "success"){
			showMessage(data["error_msg"],"fail");
			return;
		}
		$('#correlation-result').text(data["result"]);
	})
	.fail(function() {
		showMessage("ajax error.","fail");
	});
}
function getCovariance(){
	// validate input data
	if (!validateDataInput()) return;

	var station_id = $('#stations .btn').val();
	var metric_id = $('#metrics .btn').val();
	var metric_y_id = $('#covariance-metrics .btn').val();
	if (metric_y_id == ""){
		showMessage("Please select metric for covarriance.","fail");
		return;
	}
	var from_date = $('#from_date').data("DateTimePicker").date();
	var to_date = $('#to_date').data("DateTimePicker").date();
	from_date = from_date.toDate();
	to_date = to_date.toDate();
	date_from = from_date.getFullYear() + '-' + (from_date.getMonth() + 1) + '-' + from_date.getDate();
	date_to = to_date.getFullYear() + '-' + (to_date.getMonth() + 1) + '-' + to_date.getDate();
	var params = {'station': station_id,
				  'metric': metric_id,
				  'metric_y': metric_y_id,
				  'date_from': date_from,
				  'date_to': date_to,
				  'function': 'covariance'};
	$.ajax({
		dataType: "json",
		url: "get_modeling_calculation.php",
		data: params
	})
	.done(function(data){
		if (data["status"] != "success"){
			showMessage(data["error_msg"],"fail");
			return;
		}
		$('#covariance-result').text(data["result"]);
	})
	.fail(function() {
		showMessage("ajax error.","fail");
	});
}
function getForecast(){
	// validate input data
	if (!validateDataInput()) return;

	var station_id = $('#stations .btn').val();
	var metric_id = $('#metrics .btn').val();
	var metric_y_id = $('#forecast-metrics .btn').val();
	if (metric_y_id == ""){
		showMessage("Please select metric for forecast.","fail");
		return;
	}
	// check x input validity
	if ($('#x').val()=="" || !$('#x')[0].checkValidity()){
		x_name = $("span#x_name").text();
		min = $('input#x').attr('min');
		max = $('input#x').attr('max');
		showMessage(x_name + " value must be a number between " + min + " and " + max + ".","fail");
		return;
	}
	var x = $('#x').val();
	var from_date = $('#from_date').data("DateTimePicker").date();
	var to_date = $('#to_date').data("DateTimePicker").date();
	from_date = from_date.toDate();
	to_date = to_date.toDate();
	date_from = from_date.getFullYear() + '-' + (from_date.getMonth() + 1) + '-' + from_date.getDate();
	date_to = to_date.getFullYear() + '-' + (to_date.getMonth() + 1) + '-' + to_date.getDate();
	var params = {'station': station_id,
				  'metric': metric_id,
				  'metric_y': metric_y_id,
				  'date_from': date_from,
				  'date_to': date_to,
				  'function': 'forecast',
				  'x': x};
	$.ajax({
		dataType: "json",
		url: "get_modeling_calculation.php",
		data: params
	})
	.done(function(data){
		if (data["status"] != "success"){
			showMessage(data["error_msg"],"fail");
			return;
		}
		$('#forecast-result').text(data["result"]);
	})
	.fail(function() {
		showMessage("ajax error.","fail");
	});
}
