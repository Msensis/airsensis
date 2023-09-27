<?php
	session_start();

	//get session variables
	if (isset($_SESSION['uid'])){
		$uid=$_SESSION['uid'];
	}
	if (isset($_SESSION['uname'])){
		$uname=$_SESSION['uname'];
	}
	if (isset($_SESSION['urole'])){
		$urole=$_SESSION['urole'];
	}
	
	// get request variables
	$msg=null;
	$msg_type='success';
	if (isset($_REQUEST['msg'])){
		$msg=$_REQUEST['msg'];
	}
	if (isset($_REQUEST['msg_type'])){
		$msg_type=$_REQUEST['msg_type'];
	}
?>
