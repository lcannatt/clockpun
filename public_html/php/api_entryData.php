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
//get-time: returns time data for one entry
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
//update-time: updates db to reflect user input for one time data input;
function api_updateTime(){
	$db=Database::getDB();
	$startTime=TPR_Validator::getPostParam('start');
	if(!TPR_Validator::isTimeString($startTime)){
		tpr_asyncError('Invalid Start Time');
	}
	$endTime=TPR_Validator::getPostParam('end');
	if($endTime && !TPR_Validator::isTimeString($endTime)){
		tpr_asyncError('Invalid End Time');
	}
	$date=TPR_Validator::getPostParam('date');
	if(!TPR_Validator::isDateString($date)){
		tpr_asyncError('Invalid Date, stop hacking.');
	}
	$category=TPR_Validator::getPostParam('category');
	if(!TPR_Validator::isDigits($category)){
		tpr_asyncError('Invalid Category, stop hacking.');
	}
	$timeID=TPR_Validator::getPostParam('timeID');
	if(!TPR_Validator::isDigits($timeID)){
		tpr_asyncError('Invalid Time ID, stop hacking');
	}
	$comment=TPR_Validator::getPostParam('comments');
	$comment=strip_tags($comment);//not going to validate, just going to clean and avoid html injection. db will be fine.
	//Great everything is validated
	$startTimeStamp=$date.' '.$startTime;
	$endTimeStamp=($endTime?$date.' '.$endTime:null);
	$success=$db->putUpdateTime($timeID,$startTimeStamp,$endTimeStamp,$category,$comment);
	if($success){
		tpr_asyncOK(['id'=>$timeID]);
	}else{
		tpr_asyncError($db->getError());
	}
	
}
function api_deleteTime(){
	$db=Database::getDB();
	$timeID=TPR_Validator::getPostParam('timeID');
	if(!TPR_Validator::isDigits($timeID)){
		tpr_asyncError('Invalid Time ID, stop hacking');
	}
	$result=$db->putClearTime($timeID);
	if($result){
		tpr_asyncOK(['time_id'=>$timeID]);
	}else{
		tpr_asyncError($db->getError());
	}
}