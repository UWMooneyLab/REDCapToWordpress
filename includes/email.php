<?php
/**
 * @param $user
 * @param $new_user
 *
 * New users of this plugin need to customize the email message for their own study
 * This function sends verification codes to new users of your study.
 * This is triggered from the registration page and the reset password page.
 *
 */
	function send_code($user, $new_user)
	{
		
		$key  = get_password_reset_key($user);
		
		$to  = $user->user_email;
		
		$username = $user->user_login;
		$message = sprintf(__('Hi %s'), $username) . "\r\n\r\n";
		if($new_user == TRUE)
		{
			// change this text to match your study preferences
			// this is triggered from the registration page for new users.

			$subject = 'Find My Variant Verification Code';
			$message .= __("Welcome to the Find My Variant Study.\nTo set your password, visit the following address:") . "\r\n\r\n";
		}
		else 
		{
            // change this text to match your study preferences
			// this is triggered for password resets

			$subject = 'Find My Variant Password Reset Code';
			$message .= __("To reset your password, visit the following address:") . "\r\n\r\n";
		}
		
		$message .= 'https://findmyvariant.org/password-reset?key=' . $key . '&login=' . $username;
		wp_mail( $to, $subject, $message);
	}

?>
