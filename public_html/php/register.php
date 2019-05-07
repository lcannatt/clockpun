<?php
// This script is meant to bounce back and forth with the registration to validate username availability
// And facilitate the finalized registration of the user.
// Response will be json object with format {username:[string]} for username success
//											{userId:[int]} for successful creation
// Since this will be live pinging from reg screens ~once/sec, should probably implement rate limiting to prevent abuse.
require_once 'tpr_validator.php';
require_once 'tpr_async.php';

$username=TPR_Validator::getPostParam('username');

if(TPR_Validator::getPostParam('submit')){
	//Validate Input, put it in the db
	//for the moment you can do whatever you want for passwords, just not blanks
	$password=isset($_POST['password'])?$_POST['password']:'';
	$password2=isset($_POST['password2'])?$_POST['password2']:'';
	$fname=TPR_Validator::getPostParam('fname');
	$lname=TPR_Validator::getPostParam('lname');
	$email=TPR_Validator::getPostParam('email');
	$token=TPR_Validator::getPostParam('token');
	if(!$token || !$password || !$password2 || !$username || !$fname || !$lname || !$email){
		tpr_asyncError('Missing one or more fields');
	}
	if(!TPR_Validator::isToken($token)){
		tpr_asyncError('Stop Hacking Please');
	}
	if(!TPR_Validator::isValidName($fname)){
		tpr_asyncError('Invalid First Name');
	}
	if(!TPR_Validator::isValidName($lname)){
		tpr_asyncError('Invalid Last Name');
	}
	if(!TPR_Validator::isValidUserName($username)){
		tpr_asyncError('Invalid Username');
	}
	if(!TPR_Validator::isValidEmail($email)){
		tpr_asyncError('Invalid Email');
	}
	if($password!=$password2){
		tpr_asyncError('Passwords dont match');
	}
	//Cool, all the input is at least something resembling passable
	require_once 'database.php';
	$db=Database::getDB();
	if($db->getUserNameTaken($username)){
		tpr_asyncError('Username Already In Use');
	}
	//The username is also ok, we're now officially good to go
	$pwd=password_hash($password, PASSWORD_BCRYPT, ["cost"=>11]);
	$success=$db->putRegisterUser($username,$pwd,$fname,$lname,$email,$token);
	if(!$success){
		tpr_asyncError('Database Error, please note the time and contact an administrator. Error series 100');
	}else{
		tpr_asyncOK(['msg'=>"Congrats! Registration Complete."]);
	}
}else if($username){
	//sanitize, and check if it's in the db
	if(!TPR_Validator::isValidUserName($username)){
		tpr_asyncError('Invalid Username');
	}else{
		require_once 'database.php';
		$db=Database::getDB();
		if($db->getUserNameTaken($username)){
			tpr_asyncError($username);
		}else{
			$response=['username'=>$username];
			tpr_asyncOK($response);
		}
	}
}