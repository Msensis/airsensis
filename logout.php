<?PHP
require_once 'helper.php';
if (isset($uid)) log_user_action($uid,UserAction::Logout,$uname);
session_destroy();
header('Location: index.php');
?>