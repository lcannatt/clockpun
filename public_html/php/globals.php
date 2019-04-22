<?php
require_once "login_creds.php";
require_once "auth.php";


// CONFIG //

//user requirements
$PWD_MIN_LENGTH = 10;
$REAL_404_STR = "Page does not exist";

//this is the global resource prefix
$lpre = "http" . (IN_DEV?'':'s') . "://" . $_SERVER['SERVER_NAME']
		. (IN_DEV && $_SERVER['SERVER_PORT']!=='80' ? ':'.$_SERVER['SERVER_PORT'] : '');
//Email Validation Regex, yanked from emailregex.com
DEFINE('emailRegex','/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD');
		
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