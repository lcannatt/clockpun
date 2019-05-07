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

	//Copy+pasted from create_user.php
	//TO DO: Unifiy this validation so there's no copy pasted code.
	$fname=TPR_Validator::getPostParam('fname');
	if(!$fname || !TPR_Validator::isValidName($fname)){
		tpr_asyncError('Please enter a valid first name.');
	}
	$lname=TPR_Validator::getPostParam('lname');
	if(!$lname || !TPR_Validator::isValidName($lname)){
		tpr_asyncError('Please enter a valid last name.');
	}
	$email=TPR_Validator::getPostParam('email');
	//Note: Checking the length of the string this way is a little hacky and wont work with unicode data.
	//To do: enable unicode support.
	if(!$email || !TPR_Validator::isValidEmail($email)||isset($email[64])){
		tpr_asyncError('Please enter a valid email.');
	}
	$mgr=TPR_Validator::getPostParam('manager');
	if($mgr!='-1' && (!TPR_Validator::isDigits($mgr) || !$db->getIsValidManager($mgr)) ){
		tpr_asyncError('Not A Valid Manager.');
	}
	$grants=TPR_Validator::getPostParamMulti('grant');
	//Set up grants. In this case, unlinke in create_user, the active grant is just pulled from the form.
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
	if($mgr!='-1'){
		$newAccess['entry']=1;
	}
	//Hooray, ready to update!
	$success=$db->putUpdateUser($userId,$lname,$fname,$email,$mgr,$newAccess);
	if($success){
		tpr_asyncOK(['edit'=>$userId]);
	}else{
		tpr_asyncError('A Database error occured while updating user. Please note the time and contact your administrator.');
	}

}
function api_edit(){
	api_editSingle();
}

function api_resetPassMgr(){
	$userId=TPR_Validator::getPostParam('userid');
	if(TPR_Validator::isDigits($userId)){
		$db=Database::getDB();
		//generate token (12 bytes to base64 is a 16-character string)
		$recoveryCode=filter_var(base64_encode(random_bytes(12)),FILTER_SANITIZE_URL);
		$success=$db->putResetPassword($userId,$recoveryCode);
		if($success){
			tpr_asyncOK(['url'=>sp_recovery($recoveryCode)]);
		}else{
			tpr_asyncError('Server Error Occurred While Resetting Password');
		}
	}
}

function api_updatePwd(){
	$token=TPR_Validator::getPostParam('token');
	if(!TPR_Validator::isToken($token)){
		tpr_asyncError('Invalid Token, stop hacking.');
	}
	$newPw=TPR_Validator::getPostParam('password');
	$newPwConfirm=TPR_Validator::getPostParam('password2');
	if($newPw!=$newPwConfirm){
		tpr_asyncError('Passwords Dont Match');
	}else{
		$db=Database::getDB();
		$pwd=password_hash($newPw, PASSWORD_BCRYPT, ["cost"=>11]);
		$result=$db->putNewPassword($token,$pwd);
		if(!$result){
			tpr_asyncError($db->getError());
		}else{
			tpr_asyncOK(["msg"=>'Password Reset Successfully.']);
		}
	}
}