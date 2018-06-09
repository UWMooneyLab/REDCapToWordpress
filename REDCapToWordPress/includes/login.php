<?php

// login form fields
function redcap_login_form_fields() {
	ob_start(); ?>
		<?php
		// show any error messages after form submission
		redcap_show_error_messages(); ?>
 
		<form id="redcap_login_form"  class="redcap_form" action="" method="post">
			<fieldset>
				<p style="margin-top:25px;">
					<label for="redcap_user_Login">Email</label>
					<input name="redcap_user_login" id="redcap_user_login" class="required" type="text"/>
				</p>
				<p>
					<label for="redcap_user_pass">Password</label>
					<input name="redcap_user_pass" id="redcap_user_pass" class="required" type="password"/>
				</p>
				<p>
					<input type="hidden" name="redcap_login_nonce" value="<?php echo wp_create_nonce('redcap-login-nonce'); ?>"/>
					<input id="redcap_login_submit" type="submit" value="Login"/>
				</p>
			</fieldset>
		</form>
	<?php
	return ob_get_clean();
}


// user login form
function redcap_login_form() {
	global $_SESSION;

	if(!isset($_SESSION["user"])) {

		global $redcap_load_css;
 
		// set this to true so the CSS is loaded
		$redcap_load_css = true;
 
		$output = redcap_login_form_fields();
	} 
	else if ($_SESSION["level"] >=9 ){ wp_redirect("/registration/"); exit;}
	else {wp_redirect("/my_account"); exit;}
	
	return $output;
}
add_shortcode('login_form', 'redcap_login_form');


// logs a member in after submitting a form
function redcap_login_member() {
	if(isset($_POST['redcap_user_login']) && wp_verify_nonce($_POST['redcap_login_nonce'], 'redcap-login-nonce')) {
		// this returns the user ID and other info from the user name
		$user = get_user_by('email', $_POST['redcap_user_login']);
		if(empty($user)) {
			// if the user name doesn't exist
			redcap_errors()->add('empty_username', __('Invalid username'));
		}
		
 		if(!isset($_POST['redcap_user_login']) || $_POST['redcap_user_login'] == '') {
			// if no username was entered
			redcap_errors()->add('empty_username', __('Please enter a username'));
		}
		
		if(!isset($_POST['redcap_user_pass']) || $_POST['redcap_user_pass'] == '') {
			// if no password was entered
			redcap_errors()->add('empty_password', __('Please enter a password'));
		}
 
		//check the user's login with their password
		if (!empty($user)) {
			if (!wp_check_password($_POST['redcap_user_pass'], $user->user_pass, $user->ID)) {
			 //if the password is incorrect for the specified user
			redcap_errors()->add('empty_password', __('Incorrect password'));
		}
		}
 
		// retrieve all error messages
		$errors = redcap_errors()->get_error_messages();
 
		// only log the user in if there are no errors
		if(empty($errors)) {
			$credentials = array();
			$credentials['user_login'] = $user->user_login;
			$credentials['user_password'] = $_POST['redcap_user_pass'];
			wp_signon($credentials);
			global $_SESSION;
			$token = bin2hex(openssl_random_pseudo_bytes(16));
			$_SESSION["token"] = $token;
			$_SESSION["user"] = $user->user_login;
			$_SESSION["level"] = $user->user_level;
			$_SESSION["email"] = $user->user_email;
 			if ($_SESSION["level"] >=9 ){wp_redirect("/registration"); exit;}
			else {wp_redirect("/my_account"); exit;}
		}
	}
}
add_action('init', 'redcap_login_member');

?>