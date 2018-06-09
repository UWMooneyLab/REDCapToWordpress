<?php

function proband_link(){
	global $redcap_load_loader_css;
    $redcap_load_loader_css = true;

	if(session_status() and isset($_SESSION["user"])){

		global $wpdb;

		$email = $_SESSION["email"];
		$row = $wpdb->get_row( 'SELECT record_id FROM wp_redcap WHERE email="'. $email . '";', ARRAY_N );
		$record_id = ($row[0]);

		if ($record_id=="") {
			print "No record of this user";
		}
		else {
            echo '<head>',
                    '<script type="text/javascript" src="/js/src/jquery-3.2.0.js"></script>',
                    '<script type="text/javascript" src="/wp-content/plugins/RedCap/js/profile_load.js"></script>',
                '</head>',
                '<body>',
                '<div id="content">',
                    '<div id="wording">',
                        '<p style="font-size:17px;">Loading your profile...</p>',
                    '</div>',
                    '<div class="loader"></div>',
                '</div>',
                    '<script type="text/javascript">',
                        'loading_profile("' . $record_id . '");',
                    '</script>',
                '</body>';
			}
		}

}
add_shortcode('my_account', 'proband_link');


?>