<?php
//this script handles the user push and pull
//auth was already done in the handler, this script does not check for authorization
require_once 'database.php';
require_once 'tpr_async.php';
require_once 'tpr_validator.php';

//pull(single) 
//@param POST 'userid' an int,
//@return object {user_id,first_name,last_name,email,boss_id,flags[security]}
function pull_single(){
	$userId=TPR_Validator::getPostParam('userid');
	if(!TPR_Validator::isDigits($userId)){
		tpr_asyncError('This guy is hacking');
	}
	$db=Database::getDB();
	$userData=$db->getUser($userId);
	if($userData){
		tpr_asyncOK($userData);
	}else{
		tpr_asyncError('An Error Occured, please check the logs.');
	}
}

function api_userPull(){//may be expanded for multi pulls later, will add logic if needed
	pull_single();
}
function api_editSingle(){
	//Get userid sanitization out of the way
	$userId=TPR_Validator::getPostParam('userid');
	if(!TPR_Validator::isDigits($userId)){
		tpr_asyncError('Stop it. Get some help.');
	}
	$db=Database::getDB();

	//password reset?
	if(TPR_Validator::getPostParam('resetpw')){
		//generate token (12 bytes to base64 is a 16-character string)
		$recoveryCode=filter_var(base64_encode(random_bytes(12)),FILTER_SANITIZE_URL);
		$success=$db->putResetPassword($userId,$token);
		if($success){
			tpr_asyncOK(['userID'=>$userId]);
		}else{
			tpr_asyncError('Server Error Occurred While Resetting Password');
		}
	}else{
		//We're looking at a full user update. Validate inputs:
		tpr_asyncError('User editing is not built yet');
	}
	

}