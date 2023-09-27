<?php
	require_once '../helper.php';
	require_once 'auth.php';
	$menuitem = 3;
	
	function addCategory($name, $collection){
		global $mysql, $msg, $msg_type, $msg_general_error;
		
		// insert category
		if (!($stmt = $mysql->prepare("insert into metrics_category(name,collection) values(?,?)"))) {
//			 throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
			 $msg = $msg_general_error;
			 $msg_type = 'fail';
			 return false;
		}
		if (!$stmt->bind_param("ss", $name, $collection)) {
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

	// Method = POST
	if ($_SERVER['REQUEST_METHOD'] == 'POST'){
		$name=$_REQUEST['name'];
		$collection=$_REQUEST['collection'];
		if (addCategory($name,$collection)){
			// log user action
			log_user_action($uid, UserAction::CategoryAdd, $name);
			header('Location: categories.php?msg='.$msg_categoryadd_success.'&msg_type=success');
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
			$('#categoryaddForm').find('input.required').each(function(){
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
			
			$("#categoryaddForm").submit();
		}
	</script>
</head>
<body>
	<div class="page-wrapper">
		<div class="container main-container">
			<?php include 'top.php'; ?>
			<div class="content-area">
				<div class="title"><h3><?php echo $admin_addcategory_title;?></h3></div>
				<form id="categoryaddForm" name="categoryaddForm" action="#" method="POST">
					<table class="formtable" align="center">
					<tr>
						<td><label><?php echo $label_name;?></label></td>
						<td><input class="required" type="text" id="name" name="name" autofocus></td>
					</tr>
					<tr>
						<td><label><?php echo $label_collection;?></label></td>
						<td><input class="required" type="text" id="collection" name="collection"></td>
					</tr>
					</table>
					<div class="action_buttons">
						<button class="btn btn-primary" type="button" onclick="submitForm(event)"><?php echo $add_btn_text;?></button>
						<button class="btn btn-primary" type="button" onclick="window.location.assign('categories.php')"><?php echo $cancel_btn_text;?></button>
					</div>
				</form>
			</div>
		</div>
		<?php include 'footer.php'; ?>
	</div>
	<?php include '../scripts.php'; ?>
</body>
</html>
