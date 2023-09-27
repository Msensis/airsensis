<?php
	require_once '../helper.php';
	require_once 'auth.php';
	$menuitem = 2;
	
	// check if id provided
	if (!isset($_REQUEST['id'])){
		header('Location: addstation.php');
	}
	
	$id=$_REQUEST['id'];
	
	function saveStation($location,$comment,$lat,$lng,$alt,$timezone,$contact_name,$contact_email){
		global $id, $mysql, $msg, $msg_type, $msg_general_error;
		
		// update station
		if (!($stmt = $mysql->prepare("update station set location=?,comment=?,latitude=?,longitude=?,altitude=?,timezone=?,contact_name=?,contact_email=? where id=?"))) {
//			 throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
			 $msg = $msg_general_error;
			 $msg_type = 'fail';
			 return false;
		}
		if (!$stmt->bind_param("ssssssssi", $location, $comment, $lat, $lng, $alt, $timezone,$contact_name,$contact_email,$id)) {
//			throw new Exception("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
			 $msg = $msg_general_error;
			 $msg_type = 'fail';
			 return false;
		}
		if (!$stmt->execute()) {
//  		throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
			 $msg = $msg_general_error;
			 $msg_type = 'fail';
			 return false;
		}
		
		$stmt->close();
		return true;
	}

	// Method = POST
	if ($_SERVER['REQUEST_METHOD'] == 'POST'){
		$location=$_REQUEST['location'];
		$comment=$_REQUEST['comment'];
		$lat=$_REQUEST['lat'];
		$lng=$_REQUEST['lng'];
		$alt=$_REQUEST['alt'];
		$timezone=$_REQUEST['timezone'];
		$contact_name=$_REQUEST['contact_name'];
		$contact_email=$_REQUEST['contact_email'];
		if (saveStation($location,$comment,$lat,$lng,$alt,$timezone,$contact_name,$contact_email)){
			// log user action
			log_user_action($uid, UserAction::StationEdit, $location);
			header('Location: stations.php?msg='.$msg_stationsave_success.'&msg_type=success');
		}
	}
	
	$station = getStation($id);
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
			$('#stationeditForm').find('input.required').each(function(){
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
			
			$("#stationeditForm").submit();
		}
	</script>
</head>
<body>
	<div class="page-wrapper">
		<div class="container main-container">
			<?php include 'top.php'; ?>
			<div class="content-area">
				<div class="title"><h3><?php echo $admin_editstation_title.'"'.$station->location.'"';?></h3></div>
				<form id="stationeditForm" name="stationeditForm" action="#" method="POST">
					<input type="hidden" name="id" value="<?php echo $id;?>">
					<table class="formtable" align="center">
					<tr>
						<td><label><?php echo $label_location;?></label></td>
						<td><input class="required" type="text" id="location" name="location" value="<?php echo $station->location;?>" autofocus></td>
					</tr>
					<tr>
						<td><label><?php echo $label_comment;?></label></td>
						<td><input type="text" id="comment" name="comment" value="<?php echo $station->comment;?>"></td>
					</tr>
					<tr>
						<td><label><?php echo $label_lat;?></label></td>
						<td><input class="required" type="text" id="lat" name="lat" value="<?php echo $station->latitude;?>"></td>
					</tr>
					<tr>
						<td><label><?php echo $label_lng;?></label></td>
						<td><input class="required" type="text" id="lng" name="lng" value="<?php echo $station->longitude;?>"></td>
					</tr>
					<tr>
						<td><label><?php echo $label_alt;?></label></td>
						<td><input class="required" type="text" id="alt" name="alt" value="<?php echo $station->altitude;?>"></td>
					</tr>
					<tr>
						<td><label><?php echo $label_timezone;?></label></td>
						<td><input class="required" type="text" id="timezone" name="timezone" value="<?php echo $station->timezone;?>"></td>
					</tr>
					<tr>
					<tr>
						<td><label><?php echo $label_contact_name;?></label></td>
						<td><input type="text" id="contact_name" name="contact_name" value="<?php echo $station->contact_name;?>"></td>
					</tr>
					<tr>
						<td><label><?php echo $label_contact_email;?></label></td>
						<td><input type="text" id="email" name="contact_email" value="<?php echo $station->contact_email;?>"></td>
					</tr>
					</table>
					<div class="action_buttons">
						<button class="btn btn-primary" type="button" onclick="submitForm(event)"><?php echo $save_btn_text;?></button>
						<button  class="btn btn-primary" type="button" onclick="window.location.assign('stations.php')"><?php echo $cancel_btn_text;?></button>
					</div>
				</form>
			</div>
		</div>
		<?php include 'footer.php'; ?>
	</div>
	<?php include '../scripts.php'; ?>
</body>
</html>
