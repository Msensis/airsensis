<?php
	require_once '../helper.php';
	require_once 'auth.php';
	$menuitem = 3;

	function delete($id){
		global $uid, $mysql, $msg, $msg_type, $msg_category_delete_success, $msg_category_delete_fail, $msg_general_error;
		
		// get category name
		if (!($stmt = $mysql->prepare("select name from metrics_category where id=?"))) {
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
		
		// delete category
		if (!($stmt = $mysql->prepare("delete from metrics_category where id=?"))) {
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
		$msg = $msg_category_delete_success;
		$msg_type = 'success';

		// log user action
		log_user_action($uid, UserAction::CategoryDelete, $name);
		return true;
	}

	if ($_SERVER['REQUEST_METHOD'] == 'POST'){
		$id=$_REQUEST['id'];
		$action=$_REQUEST['action'];
		if ($action == "delete"){
			delete($id);
		}
	}
	
	$categories = getMetricsCategories();
?>
<!DOCTYPE html>
<html>
<head>
	<?php include 'head.php'; ?>
	<script>
	function delete_category(id){
		if (confirm("<?php echo $msg_category_delete_confirm;?>")){
			var form = document.createElement("form");
			var action = document.createElement("input"); 
			var idinput = document.createElement("input"); 

			form.id="delete-form";
			form.method = "POST";
			form.action = "categories.php";   

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
					<th>Name</th>
					<th>MongoDB Collection</th>
					<th>Actions</th>
				</tr>
				<?php  while ($category = $categories->fetch_object()){?>
				<tr>
					<td><?php echo $category->name;?></td>
					<td><?php echo $category->collection;?></td>
					<td>
					<button class="btn btn-default btn-xs" onclick="window.location.assign('editcategory.php?id=<?php echo $category->id;?>')">Edit</button>
					<button class="btn btn-default btn-xs" onclick="delete_category(<?php echo $category->id;?>)">Delete</button>
					</td>
				</tr>		
				<?php }?>
				</table>
				<div class="action_buttons">
					<a class="btn btn-primary" href="addcategory.php"><?php echo $add_category_btn_text;?></a>
				</div>
			</div>
		</div>
		<?php include 'footer.php'; ?>
	</div>
	<?php include '../scripts.php'; ?>
</body>
</html>
