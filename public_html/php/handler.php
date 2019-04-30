<?php

require_once 'tpr_urlhandler.php';
require_once 'globals.php';
require_once 'pc_general.php';



//parse the request
$path = parse_url($_SERVER["REQUEST_URI"])['path'];
$path = substr($path, 1); //remove the prefix '/'
//instantiate a url handler
$handler = new TPR_URLHandler();

//Register all handlers

$handler->register('/^login$/', function($vars) {
	require_once 'pc_login.php';
	p_createLogin();
});
$handler->register('/^time$/',function($vars){
	$db=Database::getDB();
	$access=$db->getUserAccess();
	if($access['active']){
		if($access['entry']){
			require_once 'pc_entry.php';
			p_createTimeLogging();
		}
	}else{
		header("Location: ". sp_home());
	}
	
});
$handler->register('/^review$/',function($vars){
	$db=Database::getDB();
	$access=$db->getUserAccess();
	if($access['active'] && ($access['review'] || $access['hr'] || $access['supreme'])){
		require_once 'pc_review.php';
		p_createReview();
	}else{
		header("Location: ". sp_home());
	}
});
$handler->register('/^manage$/',function($vars){
	$db=Database::getDB();
	if($db->getSecEditUser()){
		require_once 'pc_manage.php';
		p_createUserManagement();
	}else{
		header("Location: ". sp_home());
	}
});
$handler->register('/^logout$/', function($vars) {
	//unset the cookies
	unset($_COOKIE[USERNAME_COOKIE]);
	unset($_COOKIE[TOKEN_COOKIE]);

	//set cookie timeouts to an hour ago
	$res = setcookie(USERNAME_COOKIE, '', time()-3600);
	$res = setcookie(TOKEN_COOKIE, '', time()-3600);

	//redirect to the home page
	header("Location: ".sp_home());
});
$handler->register('/^create-account\/(.+)/',function($vars){
	$token=$vars[0];
	$db=Database::getDB();
	if($db->getLoggedIn()){
		p_create403('Error 403: Forbidden');
	}
	$userData=$db->getUserDataFromToken($token,true);
	if($userData){
		require_once 'pc_createaccount.php';
		p_createAccount($userData,$token);
	}else{
		p_create404();
	}
});
$handler->register('/^register/',function($vars){
	if($_SERVER['REQUEST_METHOD']=='POST'){
		require_once 'register.php';
	}
});
$handler->register('/^create-user/',function($vars){
	if($_SERVER['REQUEST_METHOD']=='POST'){
		require_once 'create_user.php';
	}
});
$handler->register('/^pull$/',function($vars){
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$db=Database::getDB();
		if($db->getSecPull()){
			require_once 'api_userdata.php';
			api_userPull();
		}else{
			p_create403('Error 403: Forbidden');
		}
	}
});
$handler->register('/^edit-user$/',function($vars){
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$db=Database::getDB();
		if($db->getSecEditUser()){
			require_once 'api_userdata.php';
			api_edit();
		}else{
			p_create403('Error 403: Forbidden');
		}
	}
});
$handler->register('/^get-user-time$/',function($vars){
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$db=Database::getDB();
		if($db->getSecEntry()){
			require_once 'api_entrydata.php';
			api_getDayTime();
		}else{
			p_create403('Error 403: Forbidden');
		}
	}
});
$handler->register('/^new-time$/',function($vars){
	$db=Database::getDB();
	if($db->getSecEntry()){
		require_once 'api_entrydata.php';
		api_newTimeEntry();
	}
});
$handler->register('/^get-time$/',function($vars){
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$db=Database::getDB();
		if($db->getSecEntry()){
			require_once 'api_entrydata.php';
			api_getOneTime();
		}else{
			p_create403('Error 403: Forbidden');
		}
	}
});
//do the actual handling
if(!($handler->handle($path))) {
	p_create404();
}

