<?php
	require_once '../helper.php';
	require_once 'auth.php';
	$menuitem = 1;

	function delete($id){
		global $uid, $mysql, $msg, $msg_type, $msg_user_delete_success, $msg_user_delete_fail, $msg_general_error;
		
		if ($id == $uid){
			 $msg = $msg_user_delete_fail;
			 $msg_type = 'fail';
			 return false;
		}
		
		// get user name
		if (!($stmt = $mysql->prepare("select name from user where id=?"))) {
//			 throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
			 $msg = $msg_general_error;
			 $msg_type = 'fail';
			 return false;
		}
		if (!$stmt->bind_param("i", $id)) {
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
		$stmt->bind_result($name);
		$stmt->fetch();
		$stmt->free_result();
		
		// delete user
		if (!($stmt = $mysql->prepare("delete from user where id=?"))) {
//			 throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
			 $msg = $msg_general_error;
			 $msg_type = 'fail';
			 return false;
		}
		if (!$stmt->bind_param("i", $id)) {
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
		$msg = $msg_user_delete_success;
		$msg_type = 'success';
		
		// log user action
		log_user_action($uid, UserAction::UserDelete, $name);
		return true;
	}

	if ($_SERVER['REQUEST_METHOD'] == 'POST'){
		$id=$_REQUEST['id'];
		$action=$_REQUEST['action'];
		if ($action == "delete"){
			delete($id);
		}
	}
	
	$users = getUsers();
?>
<!DOCTYPE html>
<html>
<head>
	<?php include 'head.php'; ?>
	<script>
	function delete_user(id){
		if (confirm("<?php echo $msg_user_delete_confirm;?>")){
			var form = document.createElement("form");
			var action = document.createElement("input"); 
			var uidinput = document.createElement("input"); 

			form.id="delete-form";
			form.method = "POST";
			form.action = "index.php";   

			action.value="delete";
			action.name="action";
			form.appendChild(action);  

			uidinput.value=id;
			uidinput.name="id";
			form.appendChild(uidinput);  

			document.body.appendChild(form);
			
			form.submit();
		}
	}
	</script>
</head>
<body>
	<div class="page-wrapper">
		<div class="container main-container">
			<?php include 'top.php'; ?>
			<div class="content-area">
				<table class="infotable" align="center">
				<tr>
					<th>Name</th>
					<th>Username</th>
					<th>Role</th>
					<th>Email</th>
					<th>Last Visit Date</th>
					<th>Actions</th>
				</tr>
				<?php  while ($user = $users->fetch_object()){?>
				<tr>
					<td><?php echo $user->name;?></td>
					<td><?php echo $user->username;?></td>
					<td><?php echo $user->role;?></td>
					<td><?php echo $user->email;?></td>
					<td><?php echo ($user->last_visit!=null)?$user->last_visit:"Never";?></td>
					<td>
					<button class="btn btn-default btn-xs" onclick="window.location.assign('edituser.php?id=<?php echo $user->id;?>')">Edit</button>
					<?php if($user->id != $uid){?>
						<button class="btn btn-default btn-xs" onclick="delete_user(<?php echo $user->id;?>)">Delete</button>
					<?php }?>
					</td>
				</tr>		
				<?php }?>
				</table>
				<div class="action_buttons">
					<a class="btn btn-primary" href="adduser.php"><?php echo $add_user_btn_text;?></a>
				</div>
			</div>
		</div>
		<?php include 'footer.php'; ?>
	</div>
	<?php include '../scripts.php'; ?>
</body>
</html>
