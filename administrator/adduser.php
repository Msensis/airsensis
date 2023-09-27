<?php
	require_once '../helper.php';
	require_once 'auth.php';
	$menuitem = 1;
	
	function addUser($username,$password,$name,$email,$role,$stations){
		global $mysql, $secret, $msg, $msg_type, $msg_general_error;
		
		// insert user
		if (!($stmt = $mysql->prepare("insert into user(username,password,name,email,role) values(?,AES_ENCRYPT(?, UNHEX(SHA2(?,512))),?,?,?)"))) {
//			 throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
			 $msg = $msg_general_error;
			 $msg_type = 'fail';
			 return false;
		}
		if (!$stmt->bind_param("sssssi", $username, $password, $secret, $name, $email, $role)) {
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
		
		$newuid = $mysql->insert_id;
		
		$stmt->close();
		
		// insert user stations
		if (!empty($stations)){
			if (!($stmt = $mysql->prepare("insert into user_station(user_id,station_id) values(?,?)"))) {
//  			 throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
				 $msg = $msg_general_error;
				 $msg_type = 'fail';
				 return false;
			}
			foreach ($stations as $station){
				if (!$stmt->bind_param("ii", $newuid, $station)) {
//  			throw new Exception("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
					 $msg = $msg_general_error;
					 $msg_type = 'fail';
					 return false;
				}
				if (!$stmt->execute()) {
//					throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
					 $msg = $msg_general_error;
					 $msg_type = 'fail';
					 return false;
				}
			}
			
			$stmt->close();
		}

		return true;
	}

	// Method = POST
	if ($_SERVER['REQUEST_METHOD'] == 'POST'){
		$username=$_REQUEST['username'];
		$password=$_REQUEST['password'];
		$name=$_REQUEST['name'];
		$email=$_REQUEST['email'];
		$role=$_REQUEST['role'];
		$stations=$_REQUEST['stations'];
		if (addUser($username,$password,$name,$email,$role,$stations)){
			// log user action
			log_user_action($uid, UserAction::UserAdd, $name);
			header('Location: index.php?msg='.$msg_useradd_success.'&msg_type=success');
		}
	}

	$roles = getRoles();
	$stations = getStations();
?>
<!DOCTYPE html>
<html>
<head>
	<?php include 'head.php'; ?>
	<?php echo "<script>
				var msg_empty_fields='".$msg_empty_fields."';
				var msg_invalid_email='".$msg_invalid_email."';
				var msg_password_nomatch='".$msg_password_nomatch."';
				</script>"; ?>
	<script>
		$(document).ready(function(){
			$("#stations_assignment").hide();
			$("#role").change(function(){
				if($(this).val() == "3"){
					$("#stations_assignment").show();
				}else{
					$("#stations_assignment").hide();
					$("#stations").val([]).trigger('chosen:updated');
				}
			});
		});
		
		function submitForm(e){
			e.preventDefault();
			
			// remove red borders
			$('.red_border').removeClass("red_border");
			
			// check for empty fields
			var ok = true;
			$('#useraddForm').find('input.required, select.required').each(function(){
				 if(!$(this).val()){
					 $(this).addClass("red_border");
					 $(this).next("div.chosen-container").addClass("red_border");
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
			
			//validate password
			if ($('#password').val()!=$('#password-confirm').val()){
				$('#password').val("");
				$('#password-confirm').val("");
				$('#password').addClass("red_border");
				$('#password-confirm').addClass("red_border");
				$('#password').focus();
				showMessage(msg_password_nomatch,'fail');
				return;
			}

			//validate Email
			if (!$('#email').val().match(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/)){
				$('#email').addClass("red_border");
				$('#email').focus();
				showMessage(msg_invalid_email,'fail');
				return;
			}
			
			$("#useraddForm").submit();
		}
	</script>
</head>
<body>
	<div class="page-wrapper">
		<div class="container main-container">
			<?php include 'top.php'; ?>
			<div class="content-area">
				<div class="title"><h3><?php echo $admin_adduser_title;?></h3></div>
				<form id="useraddForm" name="useraddForm" action="#" method="POST" autocomplete="off">
					<table class="formtable" align="center">
					<tr>
						<td><label><?php echo $label_full_name;?></label></td>
						<td><input class="required" type="text" id="name" name="name" autofocus></td>
					</tr>
					<tr>
						<td><label><?php echo $label_username;?></label></td>
						<td><input class="required" type="text" id="username" name="username"></td>
					</tr>
					<tr>
						<td><label><?php echo $label_password;?></label></td>
						<td><input class="required" type="password" id="password" name="password"></td>
					</tr>
					<tr>
						<td><label><?php echo $label_password_confirm;?></label></td>
						<td><input class="required" type="password" id="password-confirm" name="password-confirm"></td>
					</tr>
					<tr>
						<td><label><?php echo $label_email;?></label></td>
						<td><input class="required" type="text" id="email" name="email"></td>
					</tr>
					<tr>
						<td><label><?php echo $label_role;?></label></td>
						<td>
							<select class="chosen-select required" id="role" name="role">
							<option></option>
							<?php while ($role = $roles->fetch_object()){?>
								<option value="<?php echo $role->id;?>"><?php echo $role->name;?></option>
							<?php }?>
							</select>
						</td>
					</tr>
					<tr id="stations_assignment">
						<td><label><?php echo $label_stations;?></label></td>
						<td>
							<select class="chosen-select" multiple id="stations" name="stations[]">
							<option></option>
							<?php while ($station = $stations->fetch_object()){?>
								<option value="<?php echo $station->id;?>"><?php echo $station->id.'. '.$station->location;?></option>
							<?php }?>
							</select>
						</td>
					</tr>
					</table>
					<div class="action_buttons">
						<button class="btn btn-primary" type="button" onclick="submitForm(event)"><?php echo $add_btn_text;?></button>
						<button class="btn btn-primary" type="button" onclick="window.location.assign('index.php')"><?php echo $cancel_btn_text;?></button>
					</div>
				</form>
			</div>
		</div>
		<?php include 'footer.php'; ?>
	</div>
	<?php include '../scripts.php'; ?>
</body>
</html>
