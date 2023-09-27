<?php
	require_once '../helper.php';
	require_once 'auth.php';
	$menuitem = 3;

	function delete($id){
		global $uid, $mysql, $msg, $msg_type, $msg_metric_delete_success, $msg_metric_delete_fail, $msg_general_error;
		
		// get metric label
		if (!($stmt = $mysql->prepare("select label from metrics where id=?"))) {
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
		$stmt->bind_result($label);
		$stmt->fetch();
		$stmt->free_result();
		
		// delete metric
		if (!($stmt = $mysql->prepare("delete from metrics where id=?"))) {
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
		$msg = $msg_metric_delete_success;
		$msg_type = 'success';
		// log user action
		log_user_action($uid, UserAction::MetricDelete, $label);
		return true;
	}

	if ($_SERVER['REQUEST_METHOD'] == 'POST'){
		$id=$_REQUEST['id'];
		$action=$_REQUEST['action'];
		if ($action == "delete"){
			delete($id);
		}
	}
	
	$metrics = getMetrics();
?>
<!DOCTYPE html>
<html>
<head>
	<?php include 'head.php'; ?>
	<script>
	function delete_metric(id){
		if (confirm("<?php echo $msg_metric_delete_confirm;?>")){
			var form = document.createElement("form");
			var action = document.createElement("input"); 
			var idinput = document.createElement("input"); 

			form.id="delete-form";
			form.method = "POST";
			form.action = "metrics.php";   

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
					<th>Category</th>
					<th>Label</th>
					<th>Field Name</th>
					<th>Unit</th>
					<th>Range</th>
					<th>Actions</th>
				</tr>
				<?php  while ($metric = $metrics->fetch_object()){
					if (is_numeric($metric->min) && is_numeric($metric->max)){
						$range = $metric->min.' - '.$metric->max;
					}elseif (is_numeric($metric->min)){
						$range = '> ' . $metric->min;
					}elseif (is_numeric($metric->max)){
						$range = '< ' . $metric->max;
					}else{
						$range = 'undefined';
					}
				?>
				<tr>
					<td><?php echo $metric->category;?></td>
					<td><?php echo $metric->label;?></td>
					<td><?php echo $metric->field_name;?></td>
					<td><?php echo $metric->unit;?></td>
					<td><?php echo $range;?></td>
					<td>
					<button class="btn btn-default btn-xs" onclick="window.location.assign('editmetric.php?id=<?php echo $metric->id;?>')">Edit</button>
					<button class="btn btn-default btn-xs" onclick="delete_metric(<?php echo $metric->id;?>)">Delete</button>
					</td>
				</tr>		
				<?php }?>
				</table>
				<div class="action_buttons">
					<a class="btn btn-primary" href="addmetric.php"><?php echo $add_metric_btn_text;?></a>
				</div>
			</div>
		</div>
		<?php include 'footer.php'; ?>
	</div>
	<?php include '../scripts.php'; ?>
</body>
</html>
