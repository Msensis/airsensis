<?php

if($_SERVER["REQUEST_METHOD"] !== 'POST') {
	header($_SERVER["SERVER_PROTOCOL"]." 405 Method Not Allowed", true, 405);
	exit;
}

/*if(!isset($_SERVER['HTTP_X_API_KEY'])) {
	header($_SERVER["SERVER_PROTOCOL"]." 401 Unauthorized", true, 401);
	exit;
}

if($_SERVER['HTTP_X_API_KEY'] != "20f18306-b9ec-46eb-a811-12b32791be61") {
	header($_SERVER["SERVER_PROTOCOL"]." 401 Unauthorized", true, 401);
	exit;
}*/

$data = json_decode(file_get_contents('php://input'));

if (!$data->TimeZone) {
	header($_SERVER["SERVER_PROTOCOL"]." 400 Bad Request", true, 400);
	die('Error: Time zone is missing');
} 

$timeZone = $data->TimeZone;

if (!$data->StationID) {
	header($_SERVER["SERVER_PROTOCOL"]." 400 Bad Request", true, 400);
	die('Error: Station ID is missing');
}

if (!$data->Date || !$data->Time) {
	header($_SERVER["SERVER_PROTOCOL"]." 400 Bad Request", true, 400);
	die('Error: Date or Time is missing');
} 

//get system time (utc) and covert it to milliseconds
$now = new DateTime();
$nowInMilliseconds = date_format($now, 'Uv');

$dateTimeString = $data->Date . ' ' . $data->Time . ' ' . $timeZone ;
$date = DateTime::createFromFormat('d/m/Y H:i:s O', $dateTimeString);
$date->setTimezone(new DateTimeZone("UTC"));
$dateInMilliseconds = date_format($date, 'Uv');

if ( abs($nowInMilliseconds-$dateInMilliseconds) > 180000 ) {
	header($_SERVER["SERVER_PROTOCOL"]." 400 Bad Request", true, 400);
	die('Error:  Bad Time');
	exit;
}

$keys = [];
foreach($data as $key => $value) {
    array_push($keys, $key);
}
$values = [];

foreach($data as $key => $value) {
    array_push($values, $value);
}

$dataCSV = [
	$keys,
	$values
];

$date = new DateTime();
$filename = $data->StationID . '_' . date_format($date, 'YmdHis') . '.txt';

// open csv file for writing
$f = fopen('/home/airftp/' . $filename, 'w');

if ($f === false) {
	die('Error opening the file ' . $filename);
}

// write each row at a time to a file
foreach ($dataCSV as $row) {
	fputcsv($f, $row);
}

// close the file
fclose($f);



?>