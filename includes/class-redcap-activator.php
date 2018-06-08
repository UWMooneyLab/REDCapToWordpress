<?php

/**
 * Fired during RedcapToWordPress activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    REDCapToWordPress
 * @subpackage REDCapToWordPress/includes
 * @author     Tim Bergquist <trberg@uw.edu>
 */
class redcap_activator {

    public static function activate() {

        global $wpdb;

        $table_name = $wpdb->prefix . "redcap";

        if($wpdb->get_var( "show tables like '$table_name'" ) != $table_name) {

            $sql = "CREATE TABLE `". $table_name . "` ( ";
            $sql .= "  `email`  VARCHAR(200)  NOT NULL, ";
            $sql .= "  `record_id`  text   NOT NULL, ";
            $sql .= "  PRIMARY KEY (`email`) ";
            $sql .= "); ";
            require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
            dbDelta($sql);
        }
    }
}