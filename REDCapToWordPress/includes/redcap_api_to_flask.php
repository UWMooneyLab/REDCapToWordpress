<?php

/**
 * Each of these functions make POST and GET RESTful requests to the middleman server.
 * If you need to add more functions, add an endpoint to the middleman server and then query that endpoint with the new function.
 * I'm sorry this isn't easier :( . I'll try to automate this process.
 */

function request_data($record_id){
    $configs = parse_ini_file(dirname(__FILE__, $levels=2) . "/config.ini");

    $data = array(
    'record' => $record_id,
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $configs['middleman_url'] . '/profile_load');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
    $output = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($output, true);
    return $data;

}

function generate_next_id(){
    $configs = parse_ini_file(dirname(__FILE__, $levels=2) . "/config.ini");

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $configs['middleman_url'] . '/next_record_id');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;

}

function check_record($record_id){
    $configs = parse_ini_file(dirname(__FILE__, $levels=2) . "/config.ini");

    $data = array(
        'record' => $record_id,
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $configs['middleman_url'] . '/check_record');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

function create_record($record_id, $first_name, $last_name, $email){
    $configs = parse_ini_file(dirname(__FILE__, $levels=2) . "/config.ini");

    $data = array(
    'record' => $record_id,
    'first name' => $first_name,
    'last name' => $last_name,
    'email' => $email
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $configs['middleman_url'] . '/create_record');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
    $output = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($output, true);
    return $data;

}

function insert_data_redcap($email, $record_id){
	global $wpdb;
	$wpdb -> insert(
	'wp_redcap',
	array(
	'email' => $email,
	'record_id' => $record_id,));
}

function register_to_redcap($email, $first_name, $last_name, $record_id){

	if($record_id==''){
		$record_id=generate_next_id();
	}
	create_record($record_id, $first_name, $last_name, $email);
	insert_data_redcap($email, $record_id);
}

function get_pedigree($record_id, $token){
    $configs = parse_ini_file(dirname(__FILE__, $levels=2) . "/config.ini");

    $data = array(
        'token' => $token,
        'content' => 'file',
        'action' => 'export',
        'record' => $record_id,
        'field' => 'study_pedigree',
        'event' => '',
        'returnFormat' => 'json'
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $configs['redcap_url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
    $output = curl_exec($ch);
    curl_close($ch);
    return base64_encode($output);
    //return $token;
}

?>