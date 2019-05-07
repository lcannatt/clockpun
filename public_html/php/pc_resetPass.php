<?php
//form for password resets
require_once 'globals.php';
function p_createPasswordReset($userData,$token){
	p_header();
	echo '<br>
	<div class="main">
	<div class="wrapper">
		<div class="tab-contents active">
			<div class="tab-info">
				<h3>Reset Password</h3>
				<p>For user: '.$userData['username'].'</p>
			</div>
			<br>
			<div class="form-container">
				<form id="reg-form" action="'.sp_reset().'" method="post">
				<input type="hidden" id="token" name="token" value="'.$token.'">
					<table><tbody>
						<tr>
							<td><label for="password">New Password</label></td><td colspan="2"><input type="password" name="password" id="password"></td>
						</tr>
						<tr>
							<td><label for="password2">Confirm password</label></td><td colspan="2"><input type="password" name="password2" id="password2"></td>
						</tr>
						<tr>
							<td colspan="2"><input type="submit" name="update" value="Update" id="update" disabled="true"></input></td>
						</tr>
					</tbody></table>
				</form>';
	echo '	</div>
		</div>
	</div>
	</div>';
	echo '<script type="text/javascript" src="'; echo sp_js("tpr").'"></script>';
	echo '<script type="text/javascript" src="'; echo sp_js("cp_common").'"></script>';
	echo '<script type="text/javascript" src="'; echo sp_js("registration").'"></script>';
	p_footer();
}