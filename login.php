<?php 
	require_once 'helper.php';
	$menuitem = 6;
	
	// already logged in
	if (isset($uid)){
		header('Location: index.php');
	}
	
	function login($username,$password){
		global $mysql, $secret, $msg, $msg_invalid_credentials, $msg_general_error;
		//prepare
		if (!($stmt = $mysql->prepare("select id, name, role from user where username=? and AES_DECRYPT(password, UNHEX(SHA2(?,512)))=?"))) {
//			 throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
			 $msg = $msg_general_error;
			 return false;
		}
		// bind params
		if (!$stmt->bind_param("sss", $username, $secret, $password)) {
//			throw new Exception("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
			 $msg = $msg_general_error;
			 return false;
		}
		// execute 
		if (!$stmt->execute()) {
			throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
			 $msg = $msg_general_error;
			 return false;
		}
		
		$stmt->store_result();
		
		if ($stmt->num_rows > 0){
			// user found
			$stmt->bind_result($uid, $uname, $urole);
			$stmt->fetch();
			$stmt->free_result();
			$stmt->close();
			// store session variables
			$_SESSION["uid"]=$uid;
			$_SESSION["uname"]=$uname;
			$_SESSION["urole"]=$urole;
			// log user action
			log_user_action($uid, UserAction::Login, $uname);
			return true;
		}else{
			// user not found
			$stmt->free_result();
			$stmt->close();
			$msg = $msg_invalid_credentials;
			return false;
		}
	}

	if ($_SERVER['REQUEST_METHOD'] == 'POST'){
		$username=$_REQUEST['username'];
		$password=$_REQUEST['password'];
		if (login($username,$password)){
			header('Location: index.php');
		}
	}
?>
<!DOCTYPE html>
<html>
<head>
	<?php include 'head.php'; ?>
	<?php echo "<script>
				var msg_empty_fields='".$msg_empty_fields."';
				</script>"; ?>
	<script>
		function submitForm(e){
			e.preventDefault();

			// remove red borders
			$('.red_border').removeClass("red_border");
			
			// check for empty fields
			var ok = true;
			$('#loginForm').find('input').each(function(){
				 if(!$(this).val()){
					 $(this).addClass("red_border");
					 if(ok){
						 $(this).focus();
						 ok=false;
					 }
				 }
			});
			if (!ok){
				alert(msg_empty_fields);
				return;
			}
			$("#loginForm").submit();
		}
	</script>
</head>
<body>
	<div class="page-wrapper">
		<div class="container main-container">
			<?php include 'top.php'; ?>
			<div class="content-area">
				<div class="title"><h3><?php echo $login_title;?></h3></div>
				<form id="loginForm" name="login" action="#" method="POST">
					<table class="formtable" align="center">
					<tr>
						<td><label><?php echo $label_username;?></label></td>
						<td><input type="text" id="username" name="username" autofocus></td>
					</tr>
					<tr>
						<td><label><?php echo $label_password;?></label></td>
						<td><input type="password" id="password" name="password"></td>
					</tr>
					</table>
					<div class="action_buttons">
						<button class="btn btn-primary" onclick="submitForm(event)"><?php echo $login_btn_text;?></button>
					</div>
				</form>
			</div>
		</div>
		<?php include 'footer.php'; ?>
	</div>
	<?php if ($msg!=null){ 
		echo "<script>alert('".$msg."');</script>";
	}?>
</body>
</html>
