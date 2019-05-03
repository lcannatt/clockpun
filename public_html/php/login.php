<?php
require_once 'pc_general.php';
require_once 'globals.php';

//the standard error message for an incorrect username
//or password.
DEFINE("LOGIN_FAIL_STRING",'Username or password incorrect.');
DEFINE("DEACTIVATED_STRING",'Your account has been disabled. If you think this was done in error, please contact your administrator.');
//username for login attempt
$usr = $_POST["username"];
//password for login attempt
$pwd = $_POST["password"];

//get a database instance
$db = Database::getDB();
//get the user associated with the login username
$user = $db->getUserLogin($usr);

//check the password with a constant-time verification function
$password = $user?$user['password']:"";
$loginValid = password_verify($pwd, $password);
$loginValid &= $user;
if(!$loginValid) {
	loginError(LOGIN_FAIL_STRING);
}else if(!$user['flags']%2){
	//user is disabled, do not allow to log in.
	loginError(DEACTIVATED_STRING);
}

//generate the login token (12 bytes to base64 is a 16-character string)
$token = base64_encode(random_bytes(12));

//try to save the user device and cookies
if($db->putUserDevice($user['user_id'], $token)) {
	//this timeout is 60 seconds/minute * 60 minutes/hour
	// * 24 hours/day * 30 days
	$timeout = time() + (2592000);
	//set the username cookie
	setcookie(USERNAME_COOKIE, $user['username'], $timeout, LOCAL_ROOT."/");
	//set the login token cookie
	setcookie(TOKEN_COOKIE, $token, $timeout, LOCAL_ROOT."/");
	//redirect us home
	header("Location: ".sp_home());
} else {
	//boot us out if we can't cookie (the database query failed)
	loginError('Could not save user cookie.');
	
}

/*
 * Erroring out for logins
 * @param $text the error message
 */
function loginError($text) {
	p_createError('Error logging in: ' . $text);
}
