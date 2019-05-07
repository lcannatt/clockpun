<?php

class TPR_Validator {
	//Email Validation Regex, yanked from emailregex.com
	private static $emailRegex='/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD';
	//To-Do: figure out how to account fo umlauts and the sort
	private static $nameRegex='/^[a-zA-z -]{1,45}$/';
	//Usernames must be 6-26 characters alphanumeric
	private static $userNameRegex='/^[a-zA-z0-9]{5,25}$/';
	
	
	public static function getParam($key, $blank="") {
		return (isset($key) && isset($key[0]))?$key:$blank;
	}

	public static function getPostParam($key, $blank="") {
		if (!isset($_POST[$key])){
			return $blank;
		}
		return trim(TPR_Validator::getParam($_POST[$key], $blank));
	}

	public static function getPostParamMulti($key, $blank="") {
		if(isset($_POST[$key]) && count($_POST[$key])>0){
			return $_POST[$key];
		}else{
			return $blank;
		} 
	}

	public static function getGetParam($key, $blank="") {
		if(!isset($_GET[$key])){
			return $blank;
		}
		return trim(TPR_Validator::getParam($_GET[$key], $blank));
	}

	public static function isToken($value){
		//no longer than 16 chars, no less than 0
		return(!isset($value[16])&&isset($value[0]));
	}

	public static function isDigits($value) {
		return preg_match('/^\d+$/', $value);
	}

	public static function isMinLength($value, $len) {
		return isset($value[$len-1]);
	}
	public static function isDateString($value){//not infallible but will weed out most, SQL wont accept an invalid date anyways.
		return preg_match('/^(19|20)\d{2}-[01]\d-[0-3]\d$/',$value);
	}
	public static function isTimeString($value){//not perfect but enforces format, SQL will void any invalid times
		return preg_match('/^[0-2]\d:[0-5]\d$/',$value);
	}
	public static function getReqStr($array, $key, $minlen = 0, $maxlen = 100000, $standin = "") {
		$value = $array[$key]??$standin;
		
	}
	public static function isValidName($value){
		return preg_match(TPR_Validator::$nameRegex,$value);
	}
	public static function isValidEmail($value){
		return preg_match(TPR_Validator::$emailRegex,$value);
	}
	public static function isValidUsername($value){
		return preg_match(TPR_Validator::$userNameRegex,$value);
	}
}
