<?php
/* This file contains a general class definition that handles URL strings
 * based on regex pattern matching.
 */

class TPR_URLHandler {
	 //the list of regexs
	 private $patternList;
	 //the list of callback functions
	 private $patternHandler;

	 /* Magic constructor function.
	  * Just initializes vars.
	  */
	 public function __construct() {
		 //set the arrays to blanks
		 $this->patternList = [];
		 $this->patternHandler = [];
	 }

	 /* Registers a new regex pattern to be handled
	  * @param $regex the regular expression to match
	  * @param $callback the callback function that will handle a successful
	  *                  regex match. It will take a single argument: the
	  *                  list of captured groups from the regex string.
	  */
	 public function register($regex, $callback) {
		 //append to the arrays
		 $this->patternList[] = $regex;
		 $this->patternHandler[] = $callback;
	 }

	 /* Handles a given URL. Will return true upon successful match,
	  * and will return false if no matches work.
	  * @param $url the url to handle
	  */
	 public function handle($url) {
		 //loop through the handlers
		 for($i = 0; $i < sizeof($this->patternList); $i++) {
			 //reset $params to be blank
			 $params = [];
			 //check match and fill @params if found
			 if(preg_match($this->patternList[$i], $url, $params)) {
				 //remove the parameter that is the match itself
				 array_splice($params, 0, 1);
				 //handle the pattern with the callback
				 $this->patternHandler[$i]($params);
				 //return successful
				 return true;
			 }
		 }
		 //return abject failure
		 return false;
	 }
 }
