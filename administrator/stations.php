<?php
	require_once '../helper.php';
	require_once 'auth.php';
	$menuitem = 2;

	function delete($id){
		global $uid, $mysql, $msg, $msg_type, $msg_station_delete_success, $msg_station_delete_fail, $msg_general_error;
		
		// get station location
		if (!($stmt = $mysql->prepare("select location from station where id=?"))) {
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
		$stmt->bind_result($location);
		$stmt->fetch();
		$stmt->free_result();
		
		// delete station
		if (!($stmt = $mysql->prepare("delete from station where id=?"))) {
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
			throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
			 $msg = $msg_general_error;
			 $msg_type = 'fail';
			 return false;
		}
		$msg = $msg_station_delete_success;
		$msg_type = 'success';
		// log user action
		log_user_action($uid, UserAction::StationDelete, $location);
		return true;
	}

	if ($_SERVER['REQUEST_METHOD'] == 'POST'){
		$id=$_REQUEST['id'];
		$action=$_REQUEST['action'];
		if ($action == "delete"){
			delete($id);
		}
	}
	
	$stations = getStations();
?>
<!DOCTYPE html>
<html>
<head>
	<?php include 'head.php'; ?>
	<script>
	function delete_station(id){
		if (confirm("<?php echo $msg_station_delete_confirm;?>")){
			var form = document.createElement("form");
			var action = document.createElement("input"); 
			var idinput = document.createElement("input"); 

			form.id="delete-form";
			form.method = "POST";
			form.action = "stations.php";   

			action.value="delete";
			action.name="action";
			form.appendChild(action);  

			idinput.value=id;
			idinput.name="id";
			form.appendChild(idinput);  

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
					<th>ID</th>
					<th>Location</th>
					<th>Comment</th>
					<th>Contact Name</th>
					<th>Contact Email</th>
					<th>API Key</th>
					<th>Actions</th>
				</tr>
				<?php  while ($station = $stations->fetch_object()){?>
				<tr>
					<td><?php echo $station->id;?></td>
					<td><?php echo $station->location;?></td>
					<td><?php echo $station->comment;?></td>
					<td><?php echo $station->contact_name;?></td>
					<td><?php echo $station->contact_email;?></td>
					<td><?php echo $station->api_key;?></td>
					<td>
					<button class="btn btn-default btn-xs" onclick="window.location.assign('editstation.php?id=<?php echo $station->id;?>')">Edit</button>
					<button class="btn btn-default btn-xs" onclick="delete_station(<?php echo $station->id;?>)">Delete</button>
					</td>
				</tr>		
				<?php }?>
				</table>
				<div class="action_buttons">
					<a class="btn btn-primary" href="addstation.php"><?php echo $add_station_btn_text;?></a>
				</div>
			</div>
		</div>
		<?php include 'footer.php'; ?>
	</div>
	<?php include '../scripts.php'; ?>
</body>
</html>
