<?php
require_once 'pc_general.php';
require_once 'database.php';

function p_createReview(){
	$db=Database::getDB();
	p_header(1);
	$date=strval(date('Y-m-d H:i'));
	$info=$db->getOverviewData($date);
	echo '<br>
	<div class="main">
	<div class="wrapper">';
	echo '	<div class="tab-contents active">
			<h1>Review Time</h1>
	
			<br><br>';

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
	foreach($info as $name=>$data){
		echo '<tr>';
		echo 	"<td>$name</td>";
		$totals=[];
		for ($dayno=2;$dayno<=6;$dayno++){
			echo '<td><div class="bar-container day">';
			if(isset($data[$dayno])){
				foreach($data[$dayno] as $cat => $min){
					$hrs=$min/60;
					$totals[$cat]=isset($totals[$cat])?$totals[$cat]+$hrs:$hrs;
					$class=str_replace(' ','-',$cat);
					echo "<div class=\"$class hour-bar\"value=\"$hrs\">$hrs</div>";
				}
			}
			echo '<div class="float-l mark-8"></div>';
			echo '</div></td>';
		}
		echo '<td><div class="bar-container total">';
		$weeklyHrs=0;
		foreach($totals as $cat => $hrs){
			$weeklyHrs+=$hrs;
			$class=str_replace(' ','-',$cat);
			echo "<div class=\"$class hour-bar\"value=\"$hrs\">$hrs</div>";
		}
		echo "<span class=\"hours-total nodisplay\" value=\"$weeklyHrs\"></span>";
		echo '<div class="float-l mark-8"></div>';
		echo '</div></td>';
		echo '</tr>';
	}
	echo	'</tbody></table>';

	echo '</div>
	</div>
	</div>';

	echo '<script type="text/javascript" src="'; echo sp_js("tpr").'"></script>';
	echo '<script type="text/javascript" src="'; echo sp_js("cp_common").'"></script>';
	echo '<script type="text/javascript" src="'; echo sp_js("review").'"></script>';
	p_footer();
}
