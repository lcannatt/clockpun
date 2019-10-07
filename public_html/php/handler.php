<?php

require_once 'tpr_urlhandler.php';
require_once 'globals.php';
require_once 'pc_general.php';



//parse the request
$path = parse_url($_SERVER["REQUEST_URI"])['path'];
$path = substr($path, 1+strlen(LOCAL_ROOT)); //remove the prefix '/'
//instantiate a url handler
$handler = new TPR_URLHandler();

//Register all handlers

$handler->register('/^login$/', function($vars) {
	require_once 'pc_login.php';
	p_createLogin();
});
$handler->register('/^time$/',function($vars){
	$db=Database::getDB();
	if($db->getSecEntry()){
		require_once 'pc_review.php';
		p_createApp('entry');
	}else{
		header("Location: ". sp_home());
	}
	
});
$handler->register('/^review$/',function($vars){
	$db=Database::getDB();
	if($db->getSecReview()){
		require_once 'pc_review.php';
		p_createApp();
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
$handler->register('/^hr$/',function($vars){
	$db=Database::getDB();
	if($db->getSecHr()){
		require_once 'pc_review.php';
		p_createApp('hr');
	}
});
$handler->register('/^logout$/', function($vars) {
	//unset the cookies
	unset($_COOKIE[USERNAME_COOKIE]);
	unset($_COOKIE[TOKEN_COOKIE]);

	//set cookie timeouts to an hour ago
	$res = setcookie(USERNAME_COOKIE, '', time()-3600 , LOCAL_ROOT.'/');
	$res = setcookie(TOKEN_COOKIE, '', time()-3600, LOCAL_ROOT.'/');

	//redirect to the home page
	header("Location: ".sp_home());
});
$handler->register('/^create-account$/',function($vars){
	$token=$_GET['token'];
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
		//no security requirements, this is available when logged out.
		require_once 'register.php';
	}
});
$handler->register('/^create-user/',function($vars){
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$db=Database::getDB();
		if($db->getSecEditUser()){
			require_once 'create_user.php';
			createUser();
		}
		
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
			require_once 'api_entryData.php';
			api_getDayTime();
		}else{
			p_create403('Error 403: Forbidden');
		}
	}
});
$handler->register('/^new-time$/',function($vars){
	$db=Database::getDB();
	if($db->getSecEntry()){
		require_once 'api_entryData.php';
		api_newTimeEntry();
	}
});
$handler->register('/^get-time$/',function($vars){
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$db=Database::getDB();
		if($db->getSecEntry()){
			require_once 'api_entryData.php';
			api_getOneTime($db->getSecReview());
		}else{
			p_create403('Error 403: Forbidden');
		}
	}
});
$handler->register('/^update-time$/',function($vars){
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$db=Database::getDB();
		if($db->getSecEntry()){
			require_once 'api_entryData.php';
			api_updateTime();
		}else{
			p_create403('Error 403: Forbidden');
		}
	}
});
$handler->register('/^comment-time$/',function($vars){
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$db=Database::getDB();
		if($db->getSecReview()){
			require_once 'api_entryData.php';
			api_commentTime();
		}else{
			p_create403('Error 403: Forbidden');
		}
	}
});
$handler->register('/^delete-time$/',function($vars){
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$db=Database::getDB();
		if($db->getSecEntry()){
			require_once 'api_entryData.php';
			api_deleteTime();
		}else{
			p_create403('Error 403: Forbidden');
		}
	}
});
$handler->register('/^reset-password$/',function($vars){
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$db=Database::getDB();
		if($db->getSecEditUser()){
			require_once 'api_userdata.php';
			api_resetPassMgr();
		}else{
			p_create403('Error 403: Forbidden');
		}
	}
});
$handler->register('/^recovery$/',function($vars){
	$token=$_GET['token'];
	$db=Database::getDB();
	if($db->getLoggedIn()){
		p_create403('Error 403: Forbidden');
	}
	$userData=$db->getUserDataFromToken($token,false);
	if($userData){
		require_once 'pc_resetPass.php';
		p_createPasswordReset($userData,$token);
	}else{
		p_create404();
	}
});
$handler->register('/^pw-update/',function($vars){
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$db=Database::getDB();
		if($db->getLoggedIn()){
			p_create403('Error 403: Forbidden');
		}else{
			require_once 'api_userdata.php';
			api_updatePwd();
		}
	}else{
		p_create404();
	}
});
$handler->register('/^weekly-total$/',function($vars){
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$db=Database::getDB();
		if($db->getSecEntry()){
			require_once 'api_entryData.php';
			api_getWeeklyTotal();
		}else{
			p_create403('Error 403: Forbidden');
		}
	}else{
		p_create404();
	}
});
//do the actual handling
if(!($handler->handle($path))) {
	p_create404();
}

