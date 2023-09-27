<?php
	require_once '../helper.php';
	require_once 'auth.php';
	$menuitem = 3;
	
	function addMetric($category, $label, $field_name, $unit, $min, $max, $presentation){
		global $mysql, $msg, $msg_type, $msg_general_error;
		
		// insert metric
		if (!($stmt = $mysql->prepare("insert into metrics(category,label,field_name,unit,min,max,presentation) values(?,?,?,?,?,?,?)"))) {
//			 throw new Exception("Prepare failed: (" . $mysql->errno . ") " . $mysql->error);
			 $msg = $msg_general_error;
			 $msg_type = 'fail';
			 return false;
		}
		if (!$stmt->bind_param("isssddi", $category, $label, $field_name, $unit, $min, $max, $presentation)) {
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
		$category=$_REQUEST['category'];
		$label=$_REQUEST['label'];
		$field_name=$_REQUEST['field_name'];
		$unit=$_REQUEST['unit'];
		$min=(is_numeric($_REQUEST['min']))?$_REQUEST['min']:null;
		$max=(is_numeric($_REQUEST['max']))?$_REQUEST['max']:null;
		$presentation=$_REQUEST['presentation'];
		if (addMetric($category, $label, $field_name, $unit, $min, $max, $presentation)){
			// log user action
			log_user_action($uid, UserAction::MetricAdd, $label);
			header('Location: metrics.php?msg='.$msg_metricadd_success.'&msg_type=success');
		}
	}

	$categories = getMetricsCategories();
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
			$('#metricaddForm').find('input.required, select.required').each(function(){
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
			
			$("#metricaddForm").submit();
		}
	</script>
</head>
<body>
	<div class="page-wrapper">
		<div class="container main-container">
			<?php include 'top.php'; ?>
			<div class="content-area">
				<div class="title"><h3><?php echo $admin_addmetric_title;?></h3></div>
				<form id="metricaddForm" name="metricaddForm" action="#" method="POST">
					<table class="formtable" align="center">
					<tr>
						<td><label><?php echo $label_label;?></label></td>
						<td><input class="required" type="text" id="label" name="label" autofocus></td>
					</tr>
					<tr>
						<td><label><?php echo $label_field_name;?></label></td>
						<td><input class="required" type="text" id="field_name" name="field_name"></td>
					</tr>
					<tr>
						<td><label><?php echo $label_category;?></label></td>
						<td>
							<select class="chosen-select required" id="category" name="category">
							<option></option>
							<?php while ($category = $categories->fetch_object()){?>
								<option value="<?php echo $category->id;?>"><?php echo $category->name;?></option>
							<?php }?>
							</select>
						</td>
					</tr>
					<tr>
						<td><label><?php echo $label_unit;?></label></td>
						<td><input class="required" type="text" id="unit" name="unit"></td>
					</tr>
					<tr>
						<td><label><?php echo $label_min;?></label></td>
						<td><input type="number" id="min" name="min"></td>
					</tr>
					<tr>
						<td><label><?php echo $label_max;?></label></td>
						<td><input type="number" id="max" name="max"></td>
					</tr>
					<tr>
						<td><label><?php echo $label_presentation;?></label></td>
						<td><input class="required" type="number" id="presentation" name="presentation"></td>
					</tr>
					</table>
					<div class="action_buttons">
						<button class="btn btn-primary" type="button" onclick="submitForm(event)"><?php echo $add_btn_text;?></button>
						<button class="btn btn-primary" type="button" onclick="window.location.assign('metrics.php')"><?php echo $cancel_btn_text;?></button>
					</div>
				</form>
			</div>
		</div>
		<?php include 'footer.php'; ?>
	</div>
	<?php include '../scripts.php'; ?>
</body>
</html>
