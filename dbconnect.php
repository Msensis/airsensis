<?php
// mysql
$mysql_host='localhost';
$mysql_username='root';
$mysql_password='nano';
$mysql_schema='nanomonitor';
$secret = 'mSensis nano secret';

$mysql = new mysqli($mysql_host, $mysql_username, $mysql_password, $mysql_schema);
if ($mysql->connect_errno) {
    throw new Exception("Failed to connect to MySQL: (" . $mysql->connect_errno . ") " . $mysql->connect_error);
}

if(!$mysql->query("SET NAMES 'utf8'")){
	throw new Exception('Could not set NAMES: ' . $mysql->error);
}

// mongo
require 'vendor/autoload.php'; // include Composer goodies

$mongo_client = new MongoDB\Client("mongodb://localhost:27017");
$mongo = $mongo_client->nanomonitor;

?>
