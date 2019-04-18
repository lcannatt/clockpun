<?php
require_once 'php/pc_general.php';
$db=Database::getDB();
$access=$db->getUserAccess();
if($access['active']===1){
	if($access['entry']===1){
		header("Location: " . sp_enter());
	}else{
		p_header(1);
		echo '<h1>Awfully hard to tell what\'s going on here, you aren\'t enabled to log time.';
		p_footer();
	}
	
}else{
	require_once 'php/pc_login.php';
	p_createLogin();
}
