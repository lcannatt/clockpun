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
	if($access['active']){
		if($access['review']||$access['supreme']){
			require_once 'pc_review.php';
			p_createReview();
		}
	}else{
		header("Location: ". sp_home());
	}
});
$handler->register('/^manage$/',function($vars){
	$db=Database::getDB();
	$access=$db->getUserAccess();
	if($access['active']){
		if($access['hr']||$access['supreme']){
			require_once 'pc_manage.php';
			p_createUserManagement();
		}
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

//do the actual handling
if(!($handler->handle($path))) {
	p_create404();
}

