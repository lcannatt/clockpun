<?php
require_once 'pc_general.php';

function p_createLogin(){
	p_header();	
	global $lpre;
	echo '<div class="topContainer"><div class="topform">';
	echo '<form action="'.$lpre.'/php/login.php" method="post"><table class="tg">
		<tr>
			<td><label>Username</label></td>
			<td><input type="text" name="username" autocomplete="username"></td>
		</tr>
		<tr>
			<td><label>Password</label></td>
			<td><input type="password" name="password" autocomplete="current-password"></td>
		</tr>
		<tr><td colspan="2"><input type="submit" value="Log In"></td></tr>
		</table>
	</form>';
	echo '</div></div>';
	p_footer();
}