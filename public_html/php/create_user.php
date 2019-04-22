<?php

require_once 'auth.php';
require_once 'globals.php';
// Make sure user trying to access this script is authorized to do so
$db=Database::getDB();
if($db->getCanCreateUser()){
	require_once 'tpr_async.php';
	require_once 'tpr_validator.php';
	//check that all the input data is present:
	$fname=TPR_Validator::getPostParam('fname');
	$lname=TPR_Validator::getPostParam('lname');
	$email=TPR_Validator::getPostParam('email');
	$mgr=TPR_Validator::getPostParam('manager');
	$grants=TPR_Validator::getPostParamMulti('grant');
	//Note: Checking the length of the string this way is a little hacky and wont work with unicode data.
	//To do: enable unicode support.
	if(!$fname || isset($fname[45])){
		tpr_asyncError('Please enter a valid first name.');
	}else if(!$lname || isset($lname[45])){
		tpr_asyncError('Please enter a valid last name.');
	}else if(!$email || !preg_match(emailRegex,$email)||isset($email[64])){
		tpr_asyncError('Please enter a valid email.');
	}else if($mgr!=-1 && (!TPR_Validator::isDigits($mgr) || !$db->getIsValidManager($mgr)) ){
		tpr_asyncError('Not A Valid Manager.');
	}else{
		//All the main failure points didnt happen, build and create the new user.
		//Build the permissions array
		$newAccess=$db->getEmptyPermissions();
		$validGrants=$db->getUserGrants();
		if($grants){
			foreach($grants as $grant){
				if(array_key_exists($grant,$newAccess) && in_array($grant,$validGrants)){
					//ignore invalid grant attempts silently.
					$newAccess[$grant]=1;
				}
			}
		}
		//if mgr is set and didnt cause a fail out, user gets time entry
		if($mgr!==-1){
			$newAccess['entry']=1;
		}
		$newAccess['active']=1;
		//This recoverycode isnt exactly collision proof.
		//To do: Add system time in here somewhere to ensure uniqueness
		//Recovery codes also currently dont expire.
		//To do: Add expiration time to recovery codes.
		$recoveryCode=filter_var(base64_encode(random_bytes(12)),FILTER_SANITIZE_URL);
		$password='';
		$success=$db->putNewUser($lname,$fname,$email,$mgr,$newAccess,$recoveryCode);
		if($success){
			tpr_asyncOK(['url'=>sp_newUser($recoveryCode)]);
		}else{
			tpr_asyncError('Server error, please try again. If the error persists, please note the time and contact your administrator.')
		}
	}
} else {
	require_once 'pc_general.php';
	p_create404();
}
