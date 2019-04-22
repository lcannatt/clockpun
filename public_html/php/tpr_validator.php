<?php

class TPR_Validator {
	public static function getParam($key, $blank="") {
		return (isset($key) && isset($key[0]))?$key:$blank;
	}

	public static function getPostParam($key, $blank="") {
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
		return trim(TPR_Validator::getParam($_GET[$key], $blank));
	}

	public static function isDigits($value) {
		return preg_match('/^\d+$/', $value);
	}

	public static function isMinLength($value, $len) {
		return isset($value[$len-1]);
	}

	public static function getReqStr($array, $key, $minlen = 0, $maxlen = 100000, $standin = "") {
		$value = $array[$key]??$standin;
		
	}
}
