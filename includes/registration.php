<?php

/*
This file manages the administrators view of the website and includes the features specific
to administration of user accounts and registering new users to the site.
*/

// registration form fields, what is displayed to the administrator on their home page
function registration_form_fields() {
	ob_start(); ?>	
		<?php 
		// show any error messages after form submission
		redcap_show_error_messages();?>
        <div style="width:100%;">
		<form id="redcap_registration_form" class="redcap_form" method="POST" style="float:left;">
			<fieldset>
				<p style="margin-top:25px;">
					<label for="redcap_user_email"><?php _e('Email'); ?></label>
					<input name="redcap_user_email" id="redcap_user_email" class="required" type="email"/>
				</p>
				<p>
					<label for="redcap_user_first"><?php _e('First Name'); ?></label>
					<input name="redcap_user_first" id="redcap_user_first" type="text"/>
				</p>
				<p>
					<label for="redcap_user_last"><?php _e('Last Name'); ?></label>
					<input name="redcap_user_last" id="redcap_user_last" type="text"/>
				</p>
				<p>
					<label for="redcap_record_id"><?php _e('Record ID'); ?></label>
					<input name="redcap_record_id" id="redcap_record_id" type="text"/>
				</p>
				<p>
					<input type="hidden" name="redcap_register_nonce" value="<?php echo wp_create_nonce('redcap-register-nonce'); ?>"/>
					<input type="submit" value="Register User"/>
				</p>
			</fieldset>
		</form>
		
		<form id="redcap_registration_form_resend" class="redcap_form" method="POST" style="float:right;">
			<fieldset>
			<p style="margin-top:25px;">
			<h3>Resend Email</h3>
				<label for ="resend_email">Email</label>
					<input name = "resend_email" id = "resend_email" class = "required" type = "text"/>
					<p class="resetpass-request-submit">
	            		<input type="submit" name="admin_resetpass-request-submit" id="admin_resetpass-request-submit" value="Send" class="button"/>
	        		</p>
	        	<p>
					<input type="hidden" name="admin_redcap_reset_nonce" value="<?php echo wp_create_nonce('admin_redcap-reset-nonce'); ?>"/>
				</p>

			</fieldset>
		</form>
        </div>

	<?php
	return ob_get_clean();
}

// checks to make sure a user is logged in, checks the php session for which
//user is logged in, checks the user access level, determines if they have 
//administrative access. If they do, display registration page, if not, redirect to account
//profile
function load_registration_form() {
	global $_SESSION;
	// only show the registration form to authorized logged-in members
	if(session_status() and isset($_SESSION["user"])) {
     	global $redcap_load_css;
		
		// only shown to the user if they are an administrator
		if($_SESSION["level"] >= 9) {
			
			// set this to true so the CSS is loaded
			$redcap_load_css = true;
			
			$output = registration_form_fields();
		} else {
			wp_redirect("/my_account");
			exit;
		}
		return $output;
	}
else {
    wp_redirect("/login");
	exit;
	}
}
add_shortcode('register_form', 'load_registration_form');
   

/* Checks to make sure that there are no conflicts in the information given in the 
registration form.register a new user. Adds a new user to the wordpress database,
the wordpress redcap database, and creates a new record including the name and
email to the redcap project. The new user is sent an email asking them to set their
password.
*/
function redcap_add_new_member() {
  	if (isset( $_POST["redcap_user_email"] ) && wp_verify_nonce($_POST['redcap_register_nonce'], 'redcap-register-nonce')) {
  		
		$user_email		= $_POST["redcap_user_email"];
		$user_first 	= $_POST["redcap_user_first"];
		$user_last	 	= $_POST["redcap_user_last"];
		$user_pass	= wp_generate_password(); //$_POST["redcap_password"];
		$record_id		= $_POST["redcap_record_id"];

		// this is required for username checks
		//require_once(ABSPATH . WPINC . '/registration.php');
 		
		if($user_email == '') {
			// empty username
			redcap_errors()->add('email_empty', __('Please enter an email'));
		}
		if(!is_email($user_email)) {
			//invalid email
			redcap_errors()->add('email_invalid', __('Invalid email'));
		}
		if(email_exists($user_email)) {
			//Email address already registered
			redcap_errors()->add('email_used', __('Email already registered'));
		}
		/*if($user_pass == '') {
			// password field is empty
			redcap_errors()->add('password_empty', __('Please enter a password'));
		}*/
		if(!check_record($record_id) and !$record_id=='') {
			// Record ID input does not exist in redcap
			redcap_errors()->add('no_record_id', __('Record ID does not exist in RedCap'));
		}
		
		$errors = redcap_errors()->get_error_messages();
		if(empty($errors)){$GLOBALS['success']=TRUE;}
		
		// only create the user in if there are no errors
		if(empty($errors)) {
			$username = $user_first;
			$int = 1;
			while (username_exists($username)) {
				$int++;
				$username = $user_first . $int;
			}
			
			$new_user_id = wp_insert_user(array(
					'user_login'				=> $username,
					'user_pass'	 		=> $user_pass,
					'user_email'		=> $user_email,
					'first_name'		=> $user_first,
					'last_name'			=> $user_last,
					'user_registered'	=> date('Y-m-d H:i:s'),
					'role'				=> 'subscriber'
				)
			);
			register_to_redcap($user_email, $user_first, $user_last, $record_id);
			$user = get_user_by('email', $user_email);
			send_code($user, TRUE);
		}
	}
}
add_action('init', 'redcap_add_new_member');

function resend_email() {
	if (isset($_POST["resend_email"]) && wp_verify_nonce($_POST['admin_redcap_reset_nonce'], 'admin_redcap-reset-nonce')) {
		if(!email_exists( $_POST['resend_email']))
		{
			redcap_errors()->add('no_user', __('No user found for email'));
			return;
			
		}
		
		$errors = redcap_errors()->get_error_messages();
		//ask tim to add this in
		if(empty($errors))
			$GLOBALS['admin_reset_request_success']= TRUE;
		if(empty($errors)) 
		{
			$user_email = $_POST["resend_email"];
			$user = get_user_by('email', $user_email);
			send_code($user, FALSE);
		}	
	}
}
add_action('init', 'resend_email')

?>