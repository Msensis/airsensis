<?php
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding( "UTF-8");

// site global
$site_name="Airsensis";
$site_root="http://10.199.0.55:8085/airsensis/";

// Data Analysis
$dropdown_station_init_label = "Choose Station";
$dropdown_category_init_label = "Choose Category";
$dropdown_metric_init_label = "Choose Metric";
// $view_chart_btn = "View Chart";
$get_data_btn = "Get Data";
$excel_export_btn = "Export to Excel";

// error messages
$msg_general_error="An error occured.\\nAction cannot be completed.";
$msg_invalid_credentials="Invalid user name or password.";
$msg_empty_fields="Please fill in all required fields.";
$msg_invalid_email="Please provide a valid Email address.";
$msg_password_nomatch="Passwords do not match.";
$msg_missing_params="Missing parameters.";
$msg_station_auth_error="Not authorized for this station.";
$msg_unknown_function="Unknown Function.";
$msg_station_exists="Station id already exists.";

// login form
$login_title="Log in to Airsensis";
$label_username="Username:";
$label_password="Password:";
$login_btn_text="Log in";
$logout_btn_text="Log out";

// infobar
$greeting="You are logged in as ";

// administrator console
$admin_title="Administration Console";
$admin_login_title="Log in to administration console";
// form buttons
$add_btn_text="Create";
$save_btn_text="Save";
$cancel_btn_text="Cancel";
//users
$admin_adduser_title="Add new user";
$admin_edituser_title="Edit user ";
$msg_user_delete_confirm="Delete user. Are you sure?";
$msg_user_delete_success="User deleted.";
$msg_user_delete_fail="User cannot be deleted.";
$add_user_btn_text="Add new user";
// user form
$label_full_name="Full Name:";
$label_password_confirm="Retype password:";
$label_email="Email:";
$label_role="Role:";
$label_stations="Owned Stations:";
$msg_useradd_success="New user added.";
$msg_usersave_success="User saved.";
// stations
$admin_addstation_title="Add new station";
$admin_editstation_title="Edit station ";
$msg_station_delete_confirm="Delete station. Are you sure?";
$msg_station_delete_success="Station deleted.";
$msg_station_delete_fail="Station cannot be deleted.";
$add_station_btn_text="Add new station";
// stations form
$label_station_id="Station Id:";
$label_location="Location:";
$label_comment="Comment:";
$label_lat="Latitude (dd:mm:ss.sss [N/S]):";
$label_lng="Longitude (dd:mm:ss.sss [W/E]):";
$label_alt="Altitude (meters):";
$label_timezone="Tmezone (e.g. +0200):";
$label_contact_name="Contact Name:";
$label_contact_email="Contact Email:";
$msg_stationadd_success="New station added.";
$msg_stationsave_success="Station saved.";
// metrics categories
$admin_addcategory_title="Add new category";
$admin_editcategory_title="Edit category ";
$msg_category_delete_confirm="Delete category. Are you sure?";
$msg_category_delete_success="Category deleted.";
$msg_category_delete_fail="Category cannot be deleted.";
$add_category_btn_text="Add new category";
// metrics categories form
$label_name="Name:";
$label_collection="MongoDB Collection:";
$msg_categoryadd_success="New category added.";
$msg_categorysave_success="Category saved.";
// metrics
$admin_addmetric_title="Add new metric";
$admin_editmetric_title="Edit metric ";
$msg_metric_delete_confirm="Delete metric. Are you sure?";
$msg_metric_delete_success="Metric deleted.";
$msg_metric_delete_fail="Metric cannot be deleted.";
$add_metric_btn_text="Add new metric";
// metrics form
$label_label="Label:";
$label_field_name="Field Name:";
$label_category="Category:";
$label_unit="Unit:";
$label_min="Min Value:";
$label_max="Max Value:";
$label_presentation="Presentation:";
$msg_metricadd_success="New metric added.";
$msg_metricsave_success="Metric saved.";

// user actions
abstract class UserAction{
	const Login = "Login";
	const Logout = "Logout";
	const UserEdit = "User Edit";
	const UserDelete = "User Delete";
	const UserAdd = "User Add";
	const StationEdit = "Station Edit";
	const StationDelete = "Station Delete";
	const StationAdd = "Station Add";
	const MetricEdit = "Metric Edit";
	const MetricDelete = "Metric Delete";
	const MetricAdd = "Metric Add";
	const CategoryEdit = "Category Edit";
	const CategoryDelete = "Category Delete";
	const CategoryAdd = "Category Add";
}
?>
