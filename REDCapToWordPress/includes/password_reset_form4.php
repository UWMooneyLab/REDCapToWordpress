<?php

function password_reset_form_fields() 
{
	ob_start(); ?>
	<?php
	redcap_show_error_messages();
	if($GLOBALS['reset_success'] == true)
	{
		wp_redirect('/login');
		return;
	}
	
	if(!isset($_GET['login'])||!isset($_GET['key']))
	{
		wp_redirect(home_url());
		return;
	}
	
	?>
		
    <form id="resetpassform" class = "redcap_form" action='' method="POST" autocomplete="off" > 
       <fieldset>
	        <p style="margin-top:25px;">
	            <label for="pass1"><?php _e( 'New password', 'personalize-login' ) ?></label>
	            <input type="password" name="pass1" id="pass1" class="required" size="20" value="" autocomplete="off" />
	        </p>
	        <p>
	            <label for="pass2"><?php _e( 'Repeat new password', 'personalize-login' ) ?></label>
	            <input type="password" name="pass2" id="pass2" class="required" size="20" value="" autocomplete="off" />
	        </p>
	         
	        <p style="margin-top:25px;" ><?php echo wp_get_password_hint(); ?></p>
	 
	        <p class="resetpass-submit">
	            <input type="submit" name="resetpass-button" id="resetpass-button" class="button"/>
	        </p>
	        <p>
					<input type="hidden" name="redcap_reset_nonce" value="<?php echo wp_create_nonce('redcap-reset-nonce'); ?>"/>
				</p>
	   </fieldset>
    </form>
	<?php 
	return ob_get_clean();
}

function load_pass_reset_form() {
	global $redcap_load_css;
	$redcap_load_css = TRUE;
	
	$output = password_reset_form_fields();
	
	return $output;
}

$errors = [];

function process_password_reset()
{
	redcap_show_error_messages();
	
	if(isset ($_GET['login']) && isset ($_GET['key']))
	{	
		$user = check_password_reset_key( $_GET['key'], $_GET['login'] );
		if ( is_wp_error( $user ))
		{
			if($user->get_error_code() === 'expired_key')
			{	
				redcap_errors()->add('expired_key', __('Please reset password again, reset code has timed out'));
				return;
			}
			if($user->get_error_code() === 'invalid_key'){
				redcap_errors()->add('invalid_key', __('Please reset password again, reset code is invalid'));
			}
		}
	}
	else
	{
		//wp_redirect(home_url());
		return;
	}
	
	
	if(isset($_POST['pass1']) && wp_verify_nonce($_POST['redcap_reset_nonce'], 'redcap-reset-nonce'))
	{
		if(isset($_POST['pass2']))
		{
			if($_POST['pass1'] == $_POST['pass2']){
				reset_password( $user, $_POST['pass1'] );
			}
			else {
				redcap_errors()->add('password_mismatch', __('Passwords do not match'));
			}
		}
		else {
			redcap_errors()->add('empty_password', __('Please enter a new password'));
		}
	

	
		$errors = redcap_errors()->get_error_messages();
		if(empty($errors)){$GLOBALS['reset_success']=TRUE;}
		if(empty($errors)) 
		{
			reset_password( $user, $_POST['pass1'] );
		}
	}
}

add_action('init', 'process_password_reset');

add_shortcode('password_form', 'load_pass_reset_form');

?>
