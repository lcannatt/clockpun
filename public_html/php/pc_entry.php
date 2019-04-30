<?php
require_once 'pc_general.php';

function p_createTimeLogging(){
	p_header(1);
	echo '<br>
	<div class="main">
	<div class="wrapper">';
	echo '	<div class="tab-contents active">
			<h3>Select a date</h3>
			<input type="date" name="date" id="date" value="'.date('Y-m-d').'"/>
			<h3>Enter Time</h3>';
			
	p_createTimeTableForDate();
	p_createEditTimeDialog();
	echo '</div>
	</div>
	</div>';
	echo '<script type="text/javascript" src="'; echo sp_js("tpr").'"></script>';
	echo '<script type="text/javascript" src="'; echo sp_js("cp_common").'"></script>';
	echo '<script type="text/javascript" src="'; echo sp_js("entry").'"></script>';
	p_footer();
}
function p_createTimeTableForDate($date=null){
	if(!$date){
		$date=date('Y-m-d');
	}
	echo '<table id="time-history" class="interactive-table">
				<tbody>
					<tr class="header-row">
						<th>Time</th>
						<th>Hours</th>
						<th>Category</th>
						<th>Comments</th>
					</tr>';
	$db=Database::getDB();
	$userTime=$db->getUserTimeForDay($date);
	foreach($userTime as $time){
		echo'		<tr>
						<input type="hidden" value="' . $time['time_id'] . '"/>
						<td>' . $time['start'] . ' - ' . ($time['end'] ? $time['end'] : '') . '</td>
						<td>' . ($time['elapsed'] ? minToTime($time['elapsed']) : '<span class="row-timer"></span>') .'</td>
						<td>' . $time['cat_name'] . '</td>
						<td>' . $time['comment'] . '</td>
					</tr>';
	}
	echo'		</tbody>
			</table>
			<input type="button" value="Add New Entry" id="new-time"/>';
}

function p_createEditTimeDialog(){
	$db=Database::getDB();
	echo '
	<form id="edit-time" class="nodisplay">
	<h4>Time Details</h4>
		<table>
			<tbody>
				<tr>
					<td><label for="start">Start Time</label></td>
					<td><input type="time" id="start" name="start"/></td>
				</tr>
				<tr>
					<td><label for="end">End Time</label></td>
					<td><input type="time" id="end" name="end"/></td>
				</tr>
				<tr>
					<td><label for="category">Category</label></td>
					<td><select id="category" name="category">';
	$categories=$db->getTimeCategories();
	foreach($categories as $cat){
		echo '<option value="'.$cat['cat_id'].'">'.$cat['cat_name'].'</option>';
	}
	echo			'</td>
				</tr>
				<tr>
					<td><label for="comments">Comments</label></td>
					<td><textarea id="comments" name="comments"></textarea></td>
				</tr>
				<tr>
					<td><input type="button" id="save" value="Save"/></td>
					<td><input type="button" id="delete" value="Delete"/></td>
				</tr>
			</tbody>
		</table>
	</form>';
}

function minToTime($minutes){
	$minutes=intval($minutes);
	$hours=intDiv($minutes,60);
	$newMinutes=($minutes % 60);
	return $hours . ':' . ($newMinutes<10?'0'.$newMinutes:$newMinutes);
}