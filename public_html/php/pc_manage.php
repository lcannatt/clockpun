<?php
// THIS IS FOR RENDERING ONLY
// DOES NOT CHECK IF THE ACTIVE USER HAS PRIVILEGE TO SEE THE DATA BEING SHOWN.
// To use correctly, require_once 'pc_manage.php' and call p_createUserManagement() only once proper access has been confirmed.
// Do not link directly to this page.
require_once 'pc_general.php';
require_once 'database.php';

function p_createUserManagement(){
	p_header(1);
	// put everything in a container,  and create their contents
	echo '<br>
	<div class="main">
	<div class="wrapper">';
	//create tabs
	echo'<div class="tabs">
			<div class="cf">
				<div class="float-l">
					<span class="tabLink" name="search">Search</span><!--
					--><span class="tabLink" name="browse">Browse</span>
				</div>
				<div class="float-r">
					<span class="tabLink" name="new">Create New</span>
				</div>
			</div>
		</div>';
	//create contents
	echo '<div class="tab-contents" id="search">
			<div class="tab-info cf">
				<h3>Type a name, username, or email to search for a user</h3>
			</div>
			<input type="text">
		</div>
		<div class="tab-contents" id="browse">
			<div class="tab-info cf">
				<span class="float-l">
					<h3>Select any number to edit.</h3>
				</span>
				<span class="float-r">
					<button class="edit-user">Edit</button>
				</span>
			</div>
			<table class="user-table">
				<tr>
					<th></th>
					<th>Last</th>
					<th>First</th>
					<th>Username</th>
					<th>Email</th>
				</tr>';
	//get userlist from db, populate:
	$db=Database::getDB();
	//start off sorted by last name
	$userData=$db->getUsersForBrowse(0,20,'last_name');
	foreach($userData as $user){
		echo '<tr>
					<td><input type="checkbox" name="edit_'.$user['user_id'].'"></td>
					<td>'.$user['last_name'].'</td>
					<td>'.$user['first_name'].'</td>
					<td>'.$user['username'].'</td>
					<td>'.$user['email'].'</td>
				</tr>';
	}
	echo '</table></div>
		<div class="tab-contents" id="new">
			<div class="tab-info cf">
				<h3>Create a new user account</h3>
			</div>
			<div class="single-form">
				<form id="new-user" action="/create-user" method="post">
					<table class="table-form"><tbody>
						<tr>
							<td><label for="fname">First Name</label></td><td colspan="2"><input type="text" name="fname" id="fname"></td>
						</tr>
						<tr>
							<td><label for="lname">Last Name</label></td><td colspan="2"><input type="text" name="lname" id="lname"></td>
						</tr>
						<tr>
							<td><label for="email">Email</label></td><td colspan="2"><input type="text" name="email" id="email"></td>
						</tr>
						<tr>
							<td>Roles</td>';
	//display roles you can grant:
	$grants=$db->getUserGrants();
	if($grants){
		for($i=0;$i<count($grants);$i++){
			if($i>0){
				echo '<td></td>';
			}
			echo '<td><input type="checkbox" name="grant[]" id="grant_'.$grants[$i].'" value="'.$grants[$i].'"></td><td><label for="grant_'.$grants[$i].'">'.$grants[$i].'</td></tr>';
			if($i<count($grants)-1){
				echo '<tr>';
			}
		}
	}
	echo'			<tr>
						<td><label for="manager">Manager (optional)</label></td>
						<td><select name="manager" id="manager">
							<option value="-1"></option>';
	$managers=$db->getManagers();
	if($managers){
		foreach($managers as $manager){
			echo '<option value="'.$manager['user_id'].'">'.$manager['name'].'</option>';
		}
	}		
	echo'				</select></td>
					</tr>
					<tr><td colspan="3"><input type="submit" value="Create User"></input></td></tr>
				</tbody></table>
			</form>
		</div>
		</div>
	</div>';
	echo '<script type="text/javascript" src="'; echo sp_js("generics").'"></script>';
	echo '<script type="text/javascript" src="'; echo sp_js("tabs").'"></script>';
	echo '<script type="text/javascript" src="'; echo sp_js("manage").'"></script>';
	p_footer();
}