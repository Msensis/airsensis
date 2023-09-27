<?php
	//redirect to login if session has expired or user does not have admin rights
	if (!isset($urole) || $urole!=1){
	    header('Location: login.php');
	}
?>
