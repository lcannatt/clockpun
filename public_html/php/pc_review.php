<?php
require_once 'pc_general.php';
require_once 'database.php';

function p_createReview(){
	$db=Database::getDB();
	p_header(1);
	$date=strval(date('Y-m-d H:i'));	
	$info=$db->getOverviewData($date);
	$isMonday=date('N')==1;
	if(!$isMonday){
		$date=DateTime::createFromFormat('Y-m-d H:i',$date)->modify("last monday")->format('Y-m-d H:i');
	}
	$date=substr($date,0,10);
	echo '<br>
	<div class="main">
	<div class="wrapper">';
	echo '	<div class="tab-contents active">
			<h1>Review Time</h1>
			<br>
			<h3>Week of '.$date.'</h3>
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
			echo "<tr id=\"$id\">";
			echo 	"<td>$name</td>";
			$totals=[];
			for ($dayno=2;$dayno<=6;$dayno++){
				echo '<td><div class="bar-container day">';
				echo '<div class="float-l mark-8"></div>';
				if(isset($data[$dayno])){
					foreach($data[$dayno] as $cat => $min){
						$hrs=$min/60;
						$totals[$cat]=isset($totals[$cat])?$totals[$cat]+$hrs:$hrs;
						$class=str_replace(' ','-',$cat);
						echo "<div class=\"$class hour-bar\"value=\"$hrs\">$hrs</div>";
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
				echo "<div class=\"$class hour-bar\"value=\"$hrs\">$hrs</div>";
			}
			echo "<span class=\"hours-total nodisplay\" value=\"$weeklyHrs\"></span>";
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
	echo '</div>
	</div>
	</div>';

	echo '<script type="text/javascript" src="'; echo sp_js("tpr").'"></script>';
	echo '<script type="text/javascript" src="'; echo sp_js("cp_common").'"></script>';
	echo '<script type="text/javascript" src="'; echo sp_js("review").'"></script>';
	p_footer();
}
