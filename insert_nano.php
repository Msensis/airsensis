<?php
	require_once 'helper.php';

//$test_json='{"a":"val_a","b":"val_b","c":"val_c"}';
//$test_arr=json_decode($test_json);

/*$json = '{"id_station":"mSensis Station",
			"location":"Maroussi, Greece",
			"latitude":"38.05209479999999",
			"longitude":"23.828335400000014",
			"comment":"bla bla bla",
			"date":"16/09/2016",
			"time":"17:50:10",
			"t":"28",
			"p":"1",
			"pn":"2",
			"diam":"0.25",
			"pm":"1.2",
			"ldsa":"2.5",
			"i_filter":"fA9",
			"i_diff":"fA",
			"vcor":"125",
			"flow_sensor":"8",
			"flow_cha1":"1",
			"flow_cha2":"2",
			"flow_cha3":"3"}';
*/

if (isset($_REQUEST["sample"])){
	$json = $_REQUEST["sample"];
}else{
	echo "No sample to store";
	exit;
}


$doc=json_decode($json);


$result = $mongo->nano->insertOne( $doc );

echo "Inserted with Object ID '{$result->getInsertedId()}'";
?>
