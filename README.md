# REDCapToWordPress WordPress plugin

### Pre-requisite plugins:
    
This plugin requires the [Native PHP Sessions for WordPress plugin](https://wordpress.org/plugins/wp-native-php-sessions/).
Be sure to install this before installing REDCapToWordPress.

## Short codes

### Registration/Admin Page

Shortcode: [register_form]

This is the page where new study subjects are signed up for the study.
The default settings give only admin level users access to this page. To give anyone
access, some changes need to be made.

### Login Page

Shortcode: [login_form]

This is the page where returning users and administrators can log in to view their 
information. This page dovetails off of the built-in wordpress login functions, 
but adds functionality so that users can be linked to their records in REDCap.

### Patient Account Page
Shortcode: [my_account]

This is where the magic happens. This handles the pulling of relevant patient information
from REDCap to this patient portal.

The current setup of this plugin is optimized for the FindMyVariant study patient portal.
To configure the layout for your study, see the /includes/patient_profile.php file to adjust
what your patients will see upon loading this page.




