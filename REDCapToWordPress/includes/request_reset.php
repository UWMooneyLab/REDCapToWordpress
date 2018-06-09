<?php

function request_password_reset_fields()
{
	ob_start();
	redcap_show_error_messages();
	//if ($GLOBALS['reset_request_success'] == TRUE)
		//wp_redirect("/login");
	
	
	?>
	
	<form id="resetpassrequest" class = "redcap_form" action='' method = "POST" >
		<fieldset>
		<p style="margin-top:25px;">
			<label for ="req_email">Email</label>
			<input name = "req_email" id = "req_email" class = "required" type = "text"/>
		<p class="resetpass-request-submit">
	            <input type="submit" name="resetpass-request-submit" id="resetpass-request-submit" class="button"/>
	        </p>
	        <p>
					<input type="hidden" name="redcap_reset_nonce" value="<?php echo wp_create_nonce('redcap-reset-nonce'); ?>"/>
				</p>
		</fieldset>
	
	</form>
	<?php 
	return ob_get_clean();
}

function load_reset_request_form() {
	global $redcap_load_css;
	$redcap_load_css = TRUE;

	$output = request_password_reset_fields();

	return $output;
}

$errors = [];

function process_reset_request()
{

	redcap_show_error_messages();
	
	if(isset($_POST['req_email'])  && wp_verify_nonce($_POST['redcap_reset_nonce'], 'redcap-reset-nonce'))
	{	
		if(!email_exists( $_POST['req_email']))
		{
			redcap_errors()->add('no_user', __('No user found for email'));
			return;
			
		}
		
		$errors = redcap_errors()->get_error_messages();
		//ask tim to add this in
		if(empty($errors))
			$GLOBALS['reset_request_success']= TRUE;
		if(empty($errors)) 
		{
			send_reset_code($_POST['req_email']);
		}	
	}
}

add_action('init', 'process_reset_request');
add_shortcode('request_reset_field', 'load_reset_request_form');

function send_reset_code($user_email)
{
	$user = get_user_by('email', $user_email);
	send_code($user, FALSE);
	
}

?>