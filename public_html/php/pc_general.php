<?php
require_once 'globals.php';


function p_create404($error_msg = false) {
	if($error_msg ===false) {
		global $REAL_404_STR;
		$error_msg = $REAL_404_STR;
	}

	header('HTTP/1.0 404 Not Found', true, 404);
	p_header();
	echo '<span class="error"><h1>404<h1>'.$error_msg.'</span>';
	p_footer();
}

function p_create403($error_msg) {
	header('HTTP/1.0 403 Forbidden', true, 403);
	p_header();
	echo '<span class="error"><h1>403<h1>'.$error_msg.'</span>';
	p_footer();
}

function p_createError($error_msg) {
	if(isset($_POST['ajax'])) {
		header("X-Ajax-Error: " . $error_msg);
		echo $error_msg;
		die();
	}
	p_header();
	echo '<span class="error">'.$error_msg.'</span>';
	p_footer();
}

function p_createInfo($error_msg) {
	p_header();
	echo '<span class="information">'.$error_msg.'</span>';
	p_footer();
}


function p_header($logged_in=0){
	echo '<!DOCTYPE html>
	<html lang="en">
		<head>
			<title>ClockMe</title>
			<meta charset="UTF-8">
			<meta name="description" content="ClockMe: Because I\'m worth it">
			<meta name="author" content="James Earl Krampus">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
			<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
			<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
			<link rel="manifest" href="/site.webmanifest">
			<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#806394">
			<meta name="msapplication-TileColor" content="#806394">
			<meta name="theme-color" content="#806394">
			<meta name="homedir" content="'.sp_home().'">
			<link href="'.sp_css("default").'" rel="stylesheet" type="text/css" media="all">
			<link rel="shortcut icon" type="image/x-icon" href="'.serverPrefix().'/favicon.ico">
		</head>';
	if($logged_in==1){
		p_navBarTop();
	}else{
		p_loggedOutTop();
	}
	echo '<body>';
}

function p_footer(){
	echo '</body>
	</html>';
	die();
}

function p_navBarTop(){
	$db=Database::getDB();
	$access=$db->getUserAccess();
	echo '<header>
	<div class="nav-top">
	<div class="wrapper">
		<nav class="float-l">';
	if($db->getSecEntry()){
		echo '<a href="'.sp_enter().'">Enter Time</a>';
	}
	if($db->getSecReview()){
		echo '<a href="'.sp_review().'">Review Time</a>';
	}
	if($db->getSecHr()){
		echo '<a href="'.sp_hr().'">HR View</a>';
	}
	if($db->getSecEditUser()){
		echo '<a href="'.sp_manage().'">Manage Users</a>';
	}
	echo'</nav>
		<nav class="float-r">
		<span class="dropdown-button">'.ucfirst($db->getUserFirstName()).'</span>
		<a href="'.sp_logOut().'">Log Out</a>
		</nav>';
	echo'</div></div></header>';
}

function p_loggedOutTop(){
	echo '<header>
	<div class="nav-top">
	<div class="wrapper">
	<div class="center">
	<h2>ClockMe</h2>
	</div></div></div>
	</header>';
}