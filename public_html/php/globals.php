<?php
require_once "login_creds.php";
require_once "auth.php";


// CONFIG //
// NON ROOT USE: If this app is not the root element in your website, specify the relative root here:
// include all but the first slash
DEFINE('LOCAL_ROOT',"");

//user requirements
$PWD_MIN_LENGTH = 10;
$REAL_404_STR = "Page does not exist";

//this is the global resource prefix
$lpre = "http" . (IN_DEV?'':'s') . "://" . $_SERVER['SERVER_NAME']
		. (IN_DEV && $_SERVER['SERVER_PORT']!=='80' ? ':'.$_SERVER['SERVER_PORT'] : '').LOCAL_ROOT;
		
function serverPrefix() {
	global $lpre;
	return $lpre;
}

function sp_css($css_filename) {
	global $lpre;
	global $RESOURCE_VERSION;
	return $lpre . '/styles/' . $css_filename .".css";
}

function sp_js($js_filename) {
	global $lpre;
	global $RESOURCE_VERSION;
	return $lpre . '/scripts/' . $js_filename . (IN_DEV?"":"-min_".$RESOURCE_VERSION) .".js";
}

function sp_home(){
	global $lpre;
	return $lpre;
}
function sp_enter(){
	global $lpre;
	return $lpre . '/time';
}
function sp_review(){
	global $lpre;
	return $lpre . '/review';
}
function sp_logout(){
	global $lpre;
	return $lpre . '/logout';
}
function sp_manage(){
	global $lpre;
	return $lpre . '/manage';
}
function sp_newUser($token){
	global $lpre;
	return $lpre . '/create-account/' . $token;
}