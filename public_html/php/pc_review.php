<?php
require_once 'pc_general.php';
require_once 'database.php';
require_once 'tpr_validator.php';

function p_createApp($role='review'){
	$db=Database::getDB();
	$hr=($role=='hr'?1:0);
	$entry=($role=='entry'?1:0);
	p_header(1);
	$inputDate=TPR_Validator::getGetParam('week');
	if($inputDate && TPR_Validator::isDateString($inputDate)){
		$date=$inputDate.' 00:00';
	}else{
		$date=strval(date('Y-m-d H:i'));
	}	
	$isMonday=DateTime::createFromFormat('Y-m-d H:i',$date)->format('N')==1;
	if(!$isMonday){
		$date=DateTime::createFromFormat('Y-m-d H:i',$date)->modify("last monday")->format('Y-m-d H:i');
	}
	$lastWeek=DateTime::createFromFormat('Y-m-d H:i',$date)->modify("last monday")->format('Y-m-d');
	$nextWeek=DateTime::createFromFormat('Y-m-d H:i',$date)->modify("next monday")->format('Y-m-d');
	$info=$db->getOverviewData($date,$role);
	$date=substr($date,0,10);
	echo '<br>
	<div class="main">
	<div class="wrapper">';
	echo '	<div class="tab-contents active">
			<h1>'.($role=='hr'?'Review All':'').($role=='review'?'Review Team':'').($role=='entry'?'Enter Time':'').'</h1>
			<br>
			<h3>Week of '.$date.'</h3>
			<form action="" method="get">
			<Button name="week" value="'.$lastWeek.'">Previous</button> <Button name="week" value="'.$nextWeek.'">Next</button>
			</form>
			<br>
			<br>';

	echo '	<table id="review-data"><tbody>
			<tr>
				<th>Name</th>
				<th>Monday</th>
				<th>Tuesday</th>
				<th>Wednesday</th>
				<th>Thursday</th>
				<th>Friday</th>
				<th>Total</th>
			</tr>';
	if($info){
		foreach($info as $name=>$data){
			$id=$data['user_id'];
			echo "<tr id=\"uid$id\">";
			echo 	"<td>$name</td>";
			$totals=[];
			for ($dayno=2;$dayno<=6;$dayno++){
				$localDate=DateTime::createFromFormat('Y-m-d',$date)->modify("+$dayno days")->modify("-2 days")->format('Y-m-d');
				echo '<td><div class="bar-container day" date="'.$localDate.'">';
				echo '<div class="float-l mark-8"></div>';
				if(isset($data[$dayno])){
					foreach($data[$dayno] as $cat => $min){
						$hrs=$min/60;
						$totals[$cat]=isset($totals[$cat])?$totals[$cat]+$hrs:$hrs;
						$class=str_replace(' ','-',$cat);
						echo "<div class=\"$class hour-bar\"value=\"$hrs\"> </div>";
					}
				}
				echo '</div></td>';
			}
			echo '<td><div class="bar-container total">';
			echo '<div class="float-l mark-8"></div>';
			$weeklyHrs=0;
			foreach($totals as $cat => $hrs){
				$weeklyHrs+=$hrs;
				$class=str_replace(' ','-',$cat);
				echo "<div class=\"$class hour-bar\"value=\"$hrs\"></div>";
			}
			echo "<span class=\"hours-total nodisplay\" value=\"$weeklyHrs\"> </span>";
			echo '</div></td>';
			echo '</tr>';
		}
	}
	echo	'</tbody></table>';
	echo 	'<br><br>
			<div class="bar-key">
				<div class="float-l mark-8"></div>
				<div class="hour-bar Work">Work</div><!--
				--><div class="hour-bar Home-Office">Home Office</div><!--
				--><div class="hour-bar PTO">PTO</div><!--
				--><div class="hour-bar Training">Training</div>
			</div>';
	p_createEditTimeDialog();
	echo '</div>
	</div>
	</div>';

	echo '<script type="text/javascript" src="'; echo sp_js("tpr").'"></script>';
	echo '<script type="text/javascript" src="'; echo sp_js("cp_common").'"></script>';
	echo '<script type="text/javascript" src="'; echo sp_js("review").'"></script>';
	p_footer();
}

function p_createEditTimeDialog(){
	$db=Database::getDB();
	global $lpre;
	echo '
	<form id="edit-time" action="'.$lpre.'/update-time" method="POST" class="nodisplay">
	<h4>Time Details</h4>
		<table>
			<tbody>
				<tr>
					<td><label for="start">Start Time</label></td>
					<td><input type="time" id="start" name="start"/></td>
					<td><input type="button" id="startNow" value="Now"/></td>
				</tr>
				<tr>
					<td><label for="end">End Time</label></td>
					<td><input type="time" id="end" name="end"/></td>
					<td><input type="button" id="endNow" value="Now"/></td>
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
					<td colspan="2"><textarea id="comments" name="comments"></textarea></td>
				</tr>
				<tr>
					<td><input type="button" id="save" value="Save"/></td>
					<td><input type="button" id="delete" value="Delete"/></td>
				</tr>
			</tbody>
		</table>
	</form>';
}