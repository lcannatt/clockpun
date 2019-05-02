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
			<h1>Review Tima</h1>
	
			<br><br>';

	echo '	<table id="time-data"><tbody>
			<tr>
				<th>Name</th>
				<th>Category</th>
				<th>Hours Worked</th>
			</tr>
			</tbody></table>';

	echo '</div>
	</div>
	</div>';
	print_r($db->getError());
	p_footer();
}
