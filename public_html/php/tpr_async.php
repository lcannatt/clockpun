<?php
//This script contains functions to handle construction and formatting of asynchronous messages to front end

function tpr_asyncOK($indexedArray){
	//constructs a json object using key:value pairs from indexedArray
	//adds X-Status header to reply and serves the json object back to client.
	header('X-Status: ok');
	echo json_encode($indexedArray);
	die;
}
function tpr_asyncError($errormsg){
	//creates response object containing error message
	//adds X-Status header to reply and serves the json object back to client.
	$errorObj=['error'=>$errormsg];
	header('X-Status: AsyncError');
	echo json_encode($errorObj);
	die;
}