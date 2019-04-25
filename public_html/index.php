<?php
require_once 'php/pc_general.php';
$db=Database::getDB();
$access=$db->getUserAccess();
if($access['active']===1){
	if($access['entry']===1){
		header("Location: " . sp_enter());
	}else if($access['hr']||$access['review']){
		header("Location: " . sp_review());
	}else if($access['admin']||$access['supreme']){
		header("Location: " . sp_manage());
	}else{
		p_header(1);
		echo "<h1>You don't appear to have security to access anything here. Please reach out to your admin.</h1>";
		p_footer();
	}
	
}else{
	require_once 'php/pc_login.php';
	p_createLogin();
}
