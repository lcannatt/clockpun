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
					<input type="button" value="Edit">
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
			<div class="single-form">';
	p_newUserForm($db);
	echo'</div>
		</div>
		</div>';
	p_createEditSingle($db);
	p_createEditMulti($db);
	echo '<script type="text/javascript" src="'; echo sp_js("tpr").'"></script>';
	echo '<script type="text/javascript" src="'; echo sp_js("cp_common").'"></script>';
	echo '<script type="text/javascript" src="'; echo sp_js("manage").'"></script>';
	p_footer();
}

function p_newUserForm($db){
	//creates the new user form
	echo'<form id="new-user" action="/create-user" method="post">
			<table class="table-form"><tbody>
				<tr>
					<td><label for="n_fname">First Name</label></td><td colspan="2"><input type="text" name="fname" id="n_fname"></td>
				</tr>
				<tr>
					<td><label for="n_lname">Last Name</label></td><td colspan="2"><input type="text" name="lname" id="n_lname"></td>
				</tr>
				<tr>
					<td><label for="n_email">Email</label></td><td colspan="2"><input type="text" name="email" id="n_email"></td>
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
			echo '<td><input type="checkbox" name="grant[]" id="n_grant_'.$grants[$i].'" value="'.$grants[$i].'"></td><td><label for="n_grant_'.$grants[$i].'">'.$grants[$i].'</td></tr>';
			if($i<count($grants)-1){
				echo '<tr>';
			}
		}
	}
	echo'		<tr>
					<td><label for="n_manager">Manager (optional)</label></td>
					<td><select name="manager" id="n_manager">
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
			</form>';
}

//Creates a blank form, front end will populate with the specific info
function p_createEditSingle($db){
	echo '<div id="edit-single" class="edit-box nodisplay">
			<div class="edit-header">
				<span class="edit-title">Editing </span>
				<div class="edit-exit float-r">[ X ]</div>
			</div>
			<div class="edit-body">';
	echo '		<form id="edit-user" action="/edit-user" method="post">
					<table class="table-form"><tbody>
						<tr>
							<td><label for="s_fname">First Name</label></td><td colspan="2"><input type="text" name="fname" id="s_fname"></td><td><input type="button" name="resetpw" value="Reset Password"></td>
						</tr>
						<tr>
							<td><label for="s_lname">Last Name</label></td><td colspan="2"><input type="text" name="lname" id="s_lname"></td>
						</tr>
						<tr>
							<td><label for="s_email">Email</label></td><td colspan="2"><input type="text" name="email" id="s_email"></td>
						</tr>
						<tr>
							<td><label for="s_grant_active">Active</label></td><td><input type="checkbox" name="grant[]" id="s_grant_active" value="active"></td>
						</tr>
						<tr>
							<td>Roles</td>';
	//show only roles you can grant:
	$grants=$db->getUserGrants();
	if($grants){
		for($i=0;$i<count($grants);$i++){
			if($i>0){
				echo '<td></td>';
			}
			echo '<td><input type="checkbox" name="grant[]" id="s_grant_'.$grants[$i].'" value="'.$grants[$i].'"></td><td><label for="s_grant_'.$grants[$i].'">'.$grants[$i].'</td></tr>';
			if($i<count($grants)-1){
				echo '<tr>';
			}
		}
	}
	echo'				<tr>
							<td><label for="s_manager">Manager (optional)</label></td>
							<td><select name="manager" id="s_manager">
								<option value="-1"></option>';
	$managers=$db->getManagers();
	if($managers){
		foreach($managers as $manager){
			echo '<option value="'.$manager['user_id'].'">'.$manager['name'].'</option>';
		}
	}		
	echo'					</select></td>
						</tr>
						<tr><td colspan="3"><input type="submit" value="Save"></input></td></tr>
					</tbody></table>
				</form>
			</div>
		</div>';
}

//Creates a blank form, front end will populate with specific data
function p_createEditMulti($db){
	echo '<div id="edit-multi" class="edit-box nodisplay">
			<div class="edit-header">
				<span class="edit-title">Editing for</span>
				<div class="edit-exit float-r">[ X ]</div>
			</div>
			<div class="edit-body">';
	echo '		<form id="edit-multi" action="/edit-multi" method="post">
					<table class="table-form"><tbody>
						<tr>
						<td>Roles</td>';
	//show only roles you can grant:
	$grants=$db->getUserGrants();
	if($grants){
		for($i=0;$i<count($grants);$i++){
			if($i>0){
				echo '<td></td>';
			}
			echo '<td><input type="checkbox" name="grant[]" id="m_grant_'.$grants[$i].'" value="'.$grants[$i].'"></td><td><label for="m_grant_'.$grants[$i].'">'.$grants[$i].'</td></tr>';
			if($i<count($grants)-1){
				echo '<tr>';
			}
		}
	}
	echo'			<tr>
						<td><label for="m_manager">Manager (optional)</label></td>
						<td><select name="manager" id="m_manager">
							<option value="-1"></option>';
	$managers=$db->getManagers();
	if($managers){
		foreach($managers as $manager){
			echo '<option value="'.$manager['user_id'].'">'.$manager['name'].'</option>';
		}
	}		
	echo'				</select></td>
					</tr>
					<tr><td colspan="3"><input type="submit" value="Save"></input></td></tr>
				</tbody></table>
			</form>
		</div>';
}