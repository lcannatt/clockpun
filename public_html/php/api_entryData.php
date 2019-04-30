<?php
// handles requests for time entry page.
require_once 'tpr_validator.php';
require_once 'tpr_async.php';
require_once 'logger.php';
//get-user-time OR api_getDayTime: pulls time entered for the given day.
function api_getDayTime(){
	$db=Database::getDB();
	$date=TPR_Validator::getPostParam('date');
	if(TPR_Validator::isDateString($date)){
		$results=$db->getUserTimeForDay($date);
		tpr_asyncOK($results);
	}else{
		tpr_asyncError('Please stop hacking');
	}
}

//new-time: gets a valid empty time slot from the db to track existing time.
function api_newTimeEntry(){
	$db=Database::getDB();
	$empty=$db->putEmptyTime();
	if($empty){
		tpr_asyncOK(['id'=>$empty['time_id']]);
	}else{
		tpr_asyncError('Database error occurred while logging time.');
	}
}

function api_getOneTime(){
	$db=Database::getDB();
	$id=TPR_Validator::getPostParam('id');
	if(TPR_Validator::isDigits($id)){
		$data=$db->getTimeData($id);
		if($data){
			tpr_asyncOK($data);
		}else{
			tpr_asyncError('Error getting time to edit');
		}
	}
}