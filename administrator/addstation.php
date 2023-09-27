<?php
	require_once '../helper.php';
	require_once 'auth.php';
	$menuitem = 2;
	
	function addStation($id, $location,$comment,$lat,$lng,$alt,$timezone,$contact_name,$contact_email,$api_key){
		global $mysql, $msg, $msg_type, $msg_general_error, $msg_station_exists;
		
		// check if station id exists
		if (existStation($id)){
			 $msg = $msg_station_exists;
			 $msg_type = 'fail';
			 return false;
		}
		
		// insert station
		if (!($stmt = $mysql->prepare("insert into station(id,location,comment,latitude,longitude,altitude,timezone,contact_name,contact_email,api_key) values(?,?,?,?,?,?,?,?,?,?)"))) {
//			 throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
			 $msg = $msg_general_error;
			 $msg_type = 'fail';
			 return false;
		}
		if (!$stmt->bind_param("isssssssss", $id, $location, $comment, $lat, $lng, $alt, $timezone, $contact_name, $contact_email, $api_key)) {
//			throw new Exception("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
			 $msg = $msg_general_error;
			 $msg_type = 'fail';
			 return false;
		}
		if (!$stmt->execute()) {
//			throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
			 $msg = $msg_general_error;
			 $msg_type = 'fail';
			 return false;
		}

		$stmt->close();
		return true;
	}
	function cmp_keys($api_token){
		$stations = getStations();
		$same=false;
		while ($station = $stations->fetch_object()){
			if($api_token==$station->api_key) 
				$same= true; 
			}
		return $same;
	} 
	 
	function random_strings($length_of_string)
	{
		$str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		return substr(str_shuffle($str_result),0, $length_of_string);
	}
	
	// Method = POST
	if ($_SERVER['REQUEST_METHOD'] == 'POST'){
		$id=$_REQUEST['id'];
		$location=$_REQUEST['location'];
		$comment=$_REQUEST['comment'];
		$lat=$_REQUEST['lat'];
		$lng=$_REQUEST['lng'];
		$alt=$_REQUEST['alt'];
		$timezone=$_REQUEST['timezone'];
		$contact_name=$_REQUEST['contact_name'];
		$contact_email=$_REQUEST['contact_email'];
		//$key=bin2hex(random_bytes(32));
		$key=random_strings(8) . "-" . random_strings(32) . "-" . random_strings(16);
		while (cmp_keys($key)){
			$key=random_strings(8) . "-" . random_strings(32) . "-" . random_strings(16);
		}
		if (addStation($id,$location,$comment,$lat,$lng,$alt,$timezone,$contact_name,$contact_email,$key)){
			// log user action
			log_user_action($uid, UserAction::StationAdd, $location);
			header('Location: stations.php?msg='.$msg_stationadd_success.'&msg_type=success');
		}
	}
?>
<!DOCTYPE html>
<html>
<head>
	<?php include 'head.php'; ?>
	<?php echo "<script>
				var msg_empty_fields='".$msg_empty_fields."';
				var msg_invalid_email='".$msg_invalid_email."';
				</script>"; ?>
	<script>
		function submitForm(e){
			e.preventDefault();
			
			// remove red borders
			$('.red_border').removeClass("red_border");
			
			// check for empty fields
			var ok = true;
			$('#stationaddForm').find('input.required').each(function(){
				 if(!$(this).val()){
					 $(this).addClass("red_border");
					 if(ok){
						 $(this).focus();
						 ok=false;
					 }
				 }
			});
			if (!ok){
				showMessage(msg_empty_fields,'fail');
				return;
			}
			
			//validate Email
			if ($('#email').val()!='' && !$('#email').val().match(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/)){
				$('#email').addClass("red_border");
				$('#email').focus();
				showMessage(msg_invalid_email,'fail');
				return;
			}
			
			$("#stationaddForm").submit();
		}
	</script>
</head>
<body>
	<div class="page-wrapper">
		<div class="container main-container">
			<?php include 'top.php'; ?>
			<div class="content-area">
				<div class="title"><h3><?php echo $admin_addstation_title;?></h3></div>
				<form id="stationaddForm" name="stationaddForm" action="#" method="POST">
					<table class="formtable" align="center">
					<tr>
						<td><label><?php echo $label_station_id;?></label></td>
						<td><input class="required" type="text" id="id" name="id" autofocus></td>
					</tr>
					<tr>
						<td><label><?php echo $label_location;?></label></td>
						<td><input class="required" type="text" id="location" name="location"></td>
					</tr>
					<tr>
						<td><label><?php echo $label_comment;?></label></td>
						<td><input type="text" id="comment" name="comment"></td>
					</tr>
					<tr>
						<td><label><?php echo $label_lat;?></label></td>
						<td><input class="required" type="text" id="lat" name="lat"></td>
					</tr>
					<tr>
						<td><label><?php echo $label_lng;?></label></td>
						<td><input class="required" type="text" id="lng" name="lng"></td>
					</tr>
					<tr>
						<td><label><?php echo $label_alt;?></label></td>
						<td><input class="required" type="text" id="alt" name="alt"></td>
					</tr>
					<tr>
						<td><label><?php echo $label_timezone;?></label></td>
						<td><input class="required" type="text" id="timezone" name="timezone"></td>
					</tr>
					<tr>
						<td><label><?php echo $label_contact_name;?></label></td>
						<td><input type="text" id="contact_name" name="contact_name"></td>
					</tr>
					<tr>
						<td><label><?php echo $label_contact_email;?></label></td>
						<td><input type="text" id="email" name="contact_email"></td>
					</tr>
					</table>
					<div class="action_buttons">
						<button class="btn btn-primary" type="button" onclick="submitForm(event)"><?php echo $add_btn_text;?></button>
						<button class="btn btn-primary" type="button" onclick="window.location.assign('stations.php')"><?php echo $cancel_btn_text;?></button>
					</div>
				</form>
			</div>
		</div>
		<?php include 'footer.php'; ?>
	</div>
	<?php include '../scripts.php'; ?>
</body>
</html>
