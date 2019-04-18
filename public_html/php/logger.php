<?php

/* 
 * This class is one of the few exampes of OO-PHP used in
 * this codebase. Mainly done to guarantee standardization
 * of error messages in our logfiles, for easy grep'ing.
 */
class ErrorLog {
	/*
	 * The root function from which all logging messages originate.
	 * Every single logging function results in a call to this one.
	 * @param $code integer error code for grep'ing specific error classes
	 * @param $message the error message printed off with the code
	 */
	private static function logError($code, $message) {
		error_log('ELog ' .$code.': '.$message);
	}

	/*
	 * A call for lazy developers when an error or issue is unclassifiable
	 * and just needs to be reported somewhere. Avoid using if an error
	 * falls into another class of errors that is already covered.
	 * @param $message the error message
	 */
	public static function genericError($message) {
		self::logError(999, $message);
	}

	/*
	 * Internal errors caused by things breaking not due to user input.
	 * @param $message the error message
	 */
	public static function internalError($message) {
		self::logError(300, $message);
	}

	/*
	 * Errors caused by the database malfunctioning (tokens not able
	 * to be deleted, some function we expect to work unexpectedly failing).
	 * @param $message the error message
	 */
	public static function databaseError($message) {
		self::logError(100, $message);
	}

	/*
	 * Errors for the tpr database prepared query function that everything 
	 * is funneled through.
	 * @param $failloc the index of one of 4 failure points in a prepared query
	 * @param $message the message of the failure
	 * @param $sql the sql statement that was being called
	 * @param $args the arguments for the statement
	 */
	public static function databasePError($failloc, $message, $sql, $args) {
		self::logError(100+$failloc, $message.' SQL: [' . $sql . '], ARGS: ['.json_encode($args).']');
	}

	/*
	 * Errors caused by a sanitization function failing. These errors should only
	 * be thrown by a user dicking around with our urls and form values.
	 * @param $message the error message
	 */
	public static function sanitizeError($message) {
		self::logError(200, $message);
	}

	/*
	 * Logs information instead of errors. Only called by functions
	 * within this class.
	 * @param $code the error code
	 * @param $message the error message
	 */
	public static function logInfo($code, $message) {
		error_log('ILog ' . $code . ': ' . $message);
	}

	/*
	 * Logs post requests in a standardized way. Gives us a way to look back on input
	 * that failed a sanitization check.
	 * @param $title the post title
	 * @param $body the post text
	 * @param $replyingto the parent thread (if it exists)
	 * @param $board the board the post is to
	 * @param $imageset was there an image posted?
	 * @param $imagetitle the original name of the image that was uploaded
	 */
	public static function logPost($title, $body, $replyingto, $board, $imageset, $imagetitle) {
		$ip = $_SERVER['REMOTE_ADDR'];
		$message = $ip.' ::: '.$title . ' ::: ' . $body .' ::: ' . $replyingto . ' ::: '.$board.' ::: '.$imageset.' ::: '.$imagetitle;
		self::logInfo(801, $message);
	}

	/*
	 * Logs IPs of get requests.
	 */
	public static function logGet() {
		$ip = $_SERVER['REMOTE_ADDR'];

		$message =$ip . ' ::: ' .$_SERVER["REQUEST_URI"];
		self::logInfo(800, $message);
	}
}
