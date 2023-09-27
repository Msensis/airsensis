<?php

//constants 6.2
define("C01",pow(10, 3));
define("C02",2.3*pow(10,11));
define("C03",1.0);
define("C04",100.0);
define("C05",0.027);
define("C06",0.003);
define("C07",0.1);
define("C08",0.97);
define("C09",2.22*pow(10,-8));
define("C10",333);
define("C11",0.19);
define("C12",1.81*pow(10,-5));
define("C13",1.225);
define("C14",1.38*pow(10,-23));
define("C15",3.14159);
define("C16",66*pow(10,-9));
define("C17",1.142);
define("C18",0.558);
define("C19",0.999);
define("C20",4.23*pow(10,3));
define("C21",9.81);
define("C22",1.0);
define("C23",998);
define("C24",33);
define("C25",0.3);
define("C26",0.01);
define("C27",1.0*pow(10,-5));
define("C28",5.0*pow(10,-4));
define("C29",0.8);
define("C30",2.0);



//variables 6.3
define("V01",C01*C02);
define("V02",(C03*C05+C04*C06)*C02);
define("V03",C07*C08*C02);
define("V04",(C05+C06)*C02);
define("V05",(C09*C04)/V01);
define("V06",C08*C02);
define("V07",(C09*V06)/V01);
define("V13",0);
define("V15",((7*pow(10,-4))* pow(6*pow(10,5)*C09,(1/4))));
define("V16",((C23-C13)*C21*C22*pow(V15,2)/(18*C12)));


//all array parameters should have same length
function calculateV29andV30Values($caValues, $temperatureValues, $avgDiameterValues){
	
	$valuesSize = count($caValues);
	
	$V29andV30Values = array('V29' => array(), 'V30' => array());
	$V29Values = array();
	$V30Values = array();
	
	for($i=0;$i<$valuesSize;$i++){
		
		$V29andV30Value = calculateV29andV30($caValues[$i], $temperatureValues[$i], $avgDiameterValues[$i]);
		$V29Values[$i] = $V29andV30Value['V29'];
		$V30Values[$i] = $V29andV30Value['V30'];
	}
	
	$V29andV30Values['V29'] = $V29Values;
	$V29andV30Values['V30'] = $V30Values;
	
	//$result = calculateV29andV30
	//array_push($V29andV30Values, $row);
	//var_dump($V29andV30Values);
	
	return $V29andV30Values;
}

function calculateV29andV30($ca, $temperature, $diameter){
	
	$D01 = $ca;
	//convert temperature to kelvin to get correct PECs calculation
	$temperatureKelvin = $temperature + 273;
	$D04 = $diameter;
	
	$V08 = C16/($D04/2);
	$V09 = 1+$V08*(C17+C18*exp(-C19/$V08));
	$V10 = (C14*$temperatureKelvin*$V09)/(6*C15*C12*$D04/2);
	$V11 = C12/(C13*$V10);
	$V12 = pow($V11,-0.5);
	$V14 = ((C20-C13)*pow($D04, 2)*$V09)/(18*C12);
	$V17 = (pow($D04,2)*(C20-C13)*C21*$V09)/(18*C12);
	$V18 = (2*$V14*(V16-$V17))/V15;
	$V19 = pow(10,(-3*$V18));
	$V20 = 1/(C11*($V12+V13+$V19));
	$V21 = 1/(C10+$V20)+$V17;
	$V22 = ($V21*V04)/V01;
	$V23 = C25*(C26*(($D04/2)/(($D04/2)+C27))+(1-C26)*(($D04/2)/(($D04/2)+C28)));
	$V24 = pow(( $V18/($V18+C29)),C30);
	$V25 = pow($V11,(-2/3));
	$V26 = 1/(C11*($V25+$V23+$V24));
	$V27 = (1/(C24+$V26))+$V17;
	$V28 = ($V27*V06)/V01;
	$V29 = V05+$V22;
	$V30 = V07+$V28;
	
	/* print_r('--CA--');
	print_r($ca);
	print_r('--$temperature--');
	print_r($temperatureKelvin);
	print_r('--diameter--');
	print_r($diameter);
	print_r('--V29--');
	print_r($V29);
	print_r('--V30--');
	print_r($V30); */
	
	return array('V29' => $V29,'V30' => $V30);
}

function calculateIntegration($x, $dt){
	
	$sum = 0;
	for($i=0;$i<count($x)-1;$i++){
		
		$sum = $sum + (($x[$i]+$x[$i+1])/2)*$dt[$i];
	}
	
	return $sum;
}

function calculatePecw($V31, $V32, $V33){
	
	$result = $V31/($V31 + $V32)*(V01/V02)*$V33;
	
	return $result;
}

function calculatePecs($V31, $V32, $V33){
	
	$result = $V32/($V31 + $V32)*(V01/V03)*$V33;
	
	return $result;
}

?>