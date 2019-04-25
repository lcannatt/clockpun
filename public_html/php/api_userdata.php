<?php
//this script handles the user pull api
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

pull_single();