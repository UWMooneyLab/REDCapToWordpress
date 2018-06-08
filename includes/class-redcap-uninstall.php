<?php

class redcap_uninstaller {
    /**
     * Uninstalls the plugin and deletes the redcap plugin table.
     */

    public static function redcap_uninstall() {

        global $wpdb;

        $table_name = $wpdb->prefix . "redcap";

        $sql = "DROP TABLE IF EXISTS $table_name";

        $wpdb->query($sql);
    }
}