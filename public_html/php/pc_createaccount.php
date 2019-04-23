<?php
//provide forms for user to create new account info from creation link.
require_once 'pc_general.php';
function p_createAccount($userData){
	p_header();
	echo '<br>
	<div class="main">
	<div class="wrapper">
		<div class="tab-contents">
			<div class="tab-info">
				<h3>Welcome '.$userData['first_name'].'!</h3>
				<p>Please fill out/verify the info below to complete your account creation.</p>
			</div>
			<br>
			<div class="form-container">
				<form id="reg-form" action="/register" action="post">
					<table><tbody>
						<tr>
							<td><label for="username">Username</label></td><td colspan="2"><input type="text" name="username" id="username"></td>
						</tr>
						<tr>
							<td><label for="password">Password</label></td><td colspan="2"><input type="password" name="password" id="password"></td>
						</tr>
						<tr>
							<td><label for="password2">Confirm password</label></td><td colspan="2"><input type="password" name="password2" id="password2"></td>
						</tr>
						<tr>
							<td><label for="fname">First Name</label></td><td colspan="2"><input type="text" name="fname" id="fname" value="'.$userData['first_name'].'"></td>
						</tr>
						<tr>
							<td><label for="lname">Last Name</label></td><td colspan="2"><input type="text" name="lname" id="lname" value="'.$userData['last_name'].'"></td>
						</tr>
						<tr>
							<td><label for="email">Email</label></td><td colspan="2"><input type="text" name="email" id="email" value="'.$userData['email'].'"></td>
						</tr>
						<tr>
							<td colspan="2"><input type="submit" name="register" value="Register" disabled="true"></input></td>
						</tr>
					</tbody></table>
				</form>
			</div>
		</div>
	</div>';
	echo '<script type="text/javascript" src="'; echo sp_js("generics").'"></script>';
	echo '<script type="text/javascript" src="'; echo sp_js("registration").'"></script>';
	p_footer();
}