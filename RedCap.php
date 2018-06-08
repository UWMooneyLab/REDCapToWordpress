<?php
/**
* Plugin Name: REDCapToWordPress
* Description: Linking WordPress user accounts to their associated REDCap Record to forward patient-driven research.
* Version: 1.0
* Author: Tim Bergquist
* Plugin URI: https://github.com/UWMooneyLab/REDCapToWordpress
* License: GPL2
*/

/*
  The code that runs during plugin activation.
  This action is documented in includes/class-redcap-activator.php
 */

function activate_redcap() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-redcap-activator.php';
    redcap_activator::activate();
}


/*
  The code that runs during plugin uninstall.
  This action is documented in includes/class-redcap-uninstall.php
 */

function uninstall_redcap() {
    require_once plugin_dir_path( _FILE_ ) . 'includes/class-redcap-uninstall.php';
    redcap_uninstaller::redcap_uninstall();
}


register_activation_hook( __FILE__, 'activate_redcap' );
register_uninstall_hook(__FILE__, 'uninstall_redcap' );




// deletes the user from the wp_redcap database when a user is deleted on the wordpress site.
// RedCap records are not deleted or altered.
function delete_user_from_redcap( $user_id ) {
	global $wpdb;
	$user_obj = get_userdata( $user_id );
	$email = $user_obj->user_email;
	print $email;
	$wpdb -> delete( 'wp_redcap', array('email' => $email ));
}
add_action( 'delete_user', 'delete_user_from_redcap' );


//Begins user session, adds a PHP session instance into the browser upon website load.
function StartSession() {
		session_start();
}
add_action('init', 'StartSession');


//Gives the wordpress logout function PHP session erasing functionality
function EndSession() {
	session_destroy();
}
add_action('wp_logout','EndSession');


//imports the other files used in this plugin
include 'includes/patient_profile.php';
include 'includes/registration.php';
include 'includes/login.php';
include 'includes/redcap_api_to_flask.php';
include 'includes/email.php';
include 'includes/request_reset.php';
include 'includes/password_reset_form4.php';



// registers all three css files for forms, loader, and copy fields.
function redcap_register_css() {
	wp_register_style('redcap-form-css', plugin_dir_url( __FILE__ ) . '/css/forms.css');
	wp_register_style('profile-loader-css', plugin_dir_url( __FILE__ ) . '/css/loader.css');
	wp_register_style('redcap-copy-css', plugin_dir_url(__FILE__). '/css/copy_fields.css');
}
add_action('init', 'redcap_register_css');


//creates a global variable that when set to true, activates copy_fields.css on the page
function redcap_copy_css() {
	global $redcap_copy_css;
 
	// this variable is set to TRUE if the short code is used on a page/post
	if ( ! $redcap_copy_css )
		return; // this means that neither short code is present, so we get out of here
 
	wp_print_styles('redcap-copy-css');
}
add_action('wp_footer', 'redcap_copy_css');


//creates a global variable that when set to true, activates loader.css on the page
function redcap_print_loader_css() {
	global $redcap_load_loader_css;
 
	// this variable is set to TRUE if the short code is used on a page/post
	if ( ! $redcap_load_loader_css )
		return; // this means that neither short code is present, so we get out of here
 
	wp_print_styles('profile-loader-css');
}
add_action('wp_footer', 'redcap_print_loader_css');


//creates a global variable that when set to true, activates forms.css on the page
function redcap_print_css() {
	global $redcap_load_css;
 
	// this variable is set to TRUE if the short code is used on a page/post
	if ( ! $redcap_load_css )
		return; // this means that neither short code is present, so we get out of here
 
	wp_print_styles('redcap-form-css');
}
add_action('wp_footer', 'redcap_print_css');


// used for tracking error messages
// adding messages to this instance will allow messages to be displayed when redcap_show_error_messages() is called
function redcap_errors(){
    static $wp_error; // Will hold global variable safely
    return isset($wp_error) ? $wp_error : ($wp_error = new WP_Error(null, null, null));
}

// displays error messages from redcap_errors()
//globals variables are also used to track progress through the website
//reset_success checks to see if the password has been successfully reset
//success checks if a user have been successfully registered
//reset_request_success checks to see if an email has been sent to a user after they request a password change.
//While these variables are false, the success messages will not be displayed on the different pages, but once
//they are TRUE, the success messages will show.
function redcap_show_error_messages() {
	if (!isset($GLOBALS['reset_success'])){
		$GLOBALS['reset_success']=FALSE;
		}
	if (!isset($GLOBALS['success'])){
		$GLOBALS['success']=FALSE;
		}
	if (!isset($GLOBALS['reset_request_success'])){
		$GLOBALS['reset_request_success']=FALSE;
	}
	if (!isset($GLOBALS['admin_reset_request_success'])){
		$GLOBALS['admin_reset_request_success']=FALSE;
	}

	
	//displays the error messages found in redcap_errors()
	if($codes = redcap_errors()->get_error_codes()) {
		echo '<div class="redcap_errors">';
		    // Loop error codes and display errors
		   foreach($codes as $code){
		        $message = redcap_errors()->get_error_message($code);
		        echo '<span class="error"><strong>' . __('Error') . '</strong>: ' . $message . '</span><br/>';
		    }
		echo '</div>';
	}

   	else if($GLOBALS['success']==TRUE) {
		echo '<div class="redcap_errors">';
			echo '<span class="success_added">User successfully added</span><br/>';
		echo '</div>';
	}
	if ($GLOBALS['reset_success']==TRUE) {
		echo '<div class="redcap_errors">';
			echo '<span class="success_added">Password Successfully Reset</span><br/>';
		echo '</div>';
	}
	if ($GLOBALS['reset_request_success']==TRUE) {
		echo '<div class="redcap_errors">';
			echo '<span class="success_added">Password reset code sent, you should receive an email shortly</span><br/>';
		echo '</div>';
	}
	if ($GLOBALS['admin_reset_request_success']==TRUE) {
		echo '<div class="redcap_errors">';
			echo '<span class="success_added">Password reset code sent, user should receive an email shortly</span><br/>';
		echo '</div>';
	}

}
?>