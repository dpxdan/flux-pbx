<?php
/*
	FluxPBX
	Version: MPL 1.1

	The contents of this file are subject to the Mozilla Public License Version
	1.1 (the "License"); you may not use this file except in compliance with
	the License. You may obtain a copy of the License at
	http://www.mozilla.org/MPL/

	Software distributed under the License is distributed on an "AS IS" basis,
	WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
	for the specific language governing rights and limitations under the
	License.

	The Original Code is FluxPBX

	The Initial Developer of the Original Code is
	Daniel Paixao <daniel@flux.net.br>
	Portions created by the Initial Developer are Copyright (C) 2017-2022
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Daniel Paixao <daniel@flux.net.br>
	
	https://movidesk-api.flux.cloud/app/api/user{a098456f-13da-4d93-b080-4d210480545e}/token/api_key{UeEo0C8Gtbg9wBk9pBaWTAnV9QYd6VQL}
	string(27) "contacts{121548741}/address" 
	
	

*/
$conf = glob("{/usr/local/etc,/etc}/fluxpbx/config.conf", GLOB_BRACE);
set_include_path(parse_ini_file($conf[0])['document.root']);

require_once "resources/require.php";
require_once "resources/check_auth.php";
require_once "resources/functions/restapi_functions.php";
//include "token.php";

//set the include path
//	require_once "token.php";

if ($_REQUEST["rewrite_uri"] == 'token') {

send_api_token();
}

  $token = getBearerToken();
      
	if(isset($token)){
	$key = $token;
	if(isset($key)){
	send_api_token();
	exit;
	}
	else{
	send_access_denied();
	
	}

}

	if(isset($_REQUEST["rewrite_uri"])){
		$rewrite_uri = rtrim($_REQUEST["rewrite_uri"], '/');
	} 
	
	else {
		send_access_denied();
	}

	$request_method = $_SERVER["REQUEST_METHOD"];
	
	$segments = explode('/', $rewrite_uri);

	$endpoints = array();
	foreach($segments as $segment) {
		$ids = array();
		preg_match('/(.*){(.*)}/' , $segment , $ids);
		if(count($ids) == 3) {
			$endpoints[$ids[1]] = $ids[2];
		} else {
			$endpoints[$segment] = "";
		}
	}

	if (!array_key_exists('api_key', $endpoints)) {
		send_access_denied();
	}

// set request key value ready for call to check_auth
	$_REQUEST['api_key'] = $endpoints['api_key'];
	$api_endpoint_carrier = $endpoints['api_endpoint_carrier'];
//	$api_carrier = $endpoints['api_endpoint_carrier'];
	require_once "resources/check_auth.php";

	switch($request_method) {
		case "POST":
			if (!permission_exists('restapi_c')) {send_access_denied(); }
			break;
		case "GET":
			if (!permission_exists('restapi_r')) {send_access_denied(); }
			break;
		case "PUT":
			if (!permission_exists('restapi_u')) {send_access_denied(); }
			break;
		case "DELETE":
			if (!permission_exists('restapi_d')) {send_access_denied(); }
			break;
		default:
			send_access_denied();
}

$api_carrier = preg_replace('/{[^\/]*}/', '{}', $api_endpoint_carrier);
$api_carrier = preg_replace(array('/\/api_endpoint_carrier{?}?/', '/^api_endpoint_carrier{?}?\//'), '', $api_endpoint_carrier);

// remove record Ids but keep placeholders
	$rewrite_uri = preg_replace('/{[^\/]*}/', '{}', $rewrite_uri);
// remove any refernce to the api key from uri that we will compare against the DB
	$rewrite_uri = preg_replace(array('/\/api_key{?}?/', '/^api_key{?}?\//'), '', $rewrite_uri);
	
	


if (isset($api_carrier)) {

	$sql = "select * from v_restapi where api_method = :api_method and api_uri = :api_uri and api_enabled = 'true' and (domain_uuid = :domain_uuid or domain_uuid is null) order by domain_uuid asc";

	$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
	$parameters['api_method'] = $request_method;
	$parameters['api_uri'] = $rewrite_uri;

	$database = new database;

	$rows = $database->select($sql, $parameters, 'all');
	if (is_array($rows) && @sizeof($rows) != 0) {
		$api_sql = $rows[0]['api_sql'];
	} else {
		send_api_message(404, "API not found.".$rewrite_uri." - ".$request_method." - ".$_SESSION['domain_uuid']."");
	}

	unset ($parameters, $sql);

	if ($request_method == 'GET') {
		if (strpos($api_sql, ':domain_uuid') > 0){
			$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
		}
		foreach($endpoints as $key => $value){
			if ($key == 'api_key') continue;
			if (strlen($value) > 0) {
				$parameters[$key] = $value;
			}
		}

		//var_dump($parameters);
		//echo "<br>\n";
		//exit;

		$rows = $database->select($api_sql, $parameters, 'all');
		if (is_array($rows) && @sizeof($rows) != 0) {
			send_data($rows);
		} else {
			send_api_message(200, "Empty result set.");
		}
	exit;
	}

	if ($request_method == 'POST') {
		$data = json_decode(file_get_contents("php://input"), TRUE);
		if (permission_exists('restapi_domain_in_data')) {
			if (strpos($api_sql, ':domain_uuid') > 0){
				$data['domain_uuid'] = $_SESSION['domain_uuid'];
			}
		}
		if (permission_exists('restapi_new_uuid_in_data')) {
			$data['new_uuid'] = uuid();
		}

		foreach($endpoints as $key => $value){
			if ($key == 'api_key') continue;
			if (strlen($value) > 0) {
				$data[$key] = $value;
			}
		}

		//var_dump($data);
		//echo "<br>\n".$api_sql."<br>\n";
		//exit;

		$database->execute($api_sql, $data, 'all');
		send_api_message($database->message['code'], $database->message['message']);
		echo $database->message['error']['message']."\n";
		exit;
	}

	if ($request_method == 'PUT') {
		$data = json_decode(file_get_contents("php://input"), TRUE);
		if (!permission_exists('restapi_domain_in_data')) {
			if (strpos($api_sql, ':domain_uuid') > 0){
				$data['domain_uuid'] = $_SESSION['domain_uuid'];
			}
		}

		foreach($endpoints as $key => $value){
			if ($key == 'api_key') continue;
			if (strlen($value) > 0) {
				$data[$key] = $value;
			}
		}

		//var_dump($data);
		//echo "<br>\n".$api_sql."<br>\n";
		//exit;

		$database->execute($api_sql, $data, 'all');
		send_api_message($database->message['code'], $database->message['message']);
		//echo $database->message['error']['message']."\n";
		exit;
	}

	if ($request_method == 'DELETE') {

		if (strpos($api_sql, ':domain_uuid') > 0){
			$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
		}
		foreach($endpoints as $key => $value){
			if ($key == 'api_key') continue;
			if (strlen($value) > 0) {
				$parameters[$key] = $value;
			}
		}

		//var_dump($data);
		//echo "<br>\n".$api_sql."<br>\n";
		//exit;

		$database->execute($api_sql, $parameters, 'all');
		send_api_message($database->message['code'], $database->message['message']);
		//echo $database->message['error']['message']."\n";
		exit;
	}

exit;
}
else {

	$rewrite_uri = preg_replace('/{[^\/]*}/', '{}', $rewrite_uri);
	$rewrite_uri = preg_replace(array('/\/api_endpoint_carrier{}?/', '/^api_endpoint_carrier{}?\//'), '/', $rewrite_uri);
	
	
	$sql = "select * from v_restapi_endpoints where api_endpoint_method = :api_method and api_endpoint_category = 'endpoint' and api_endpoint_enabled = 'true' and api_endpoint_redirect = :rewrite_uri and api_endpoint_carrier = :api_endpoint_carrier and (domain_uuid = :domain_uuid or domain_uuid is null) order by domain_uuid asc";

	$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
	$parameters['api_method'] = $request_method;
	$parameters['api_endpoint_carrier'] = $api_endpoint_carrier;
	$parameters['rewrite_uri'] = $rewrite_uri;

	$database = new database;

	$rows = $database->select($sql, $parameters, 'all');
	if (is_array($rows) && @sizeof($rows) != 0) {
		$restapi_endpoint_uuid = $rows[0]['restapi_endpoint_uuid'];
		$api_endpoint_category = $rows[0]['api_endpoint_category'];
		$api_endpoint_uri = $rows[0]['api_endpoint_uri'];
		$domain_uuid = $rows[0]['domain_uuid'];
		$api_endpoint_redirect = $rows[0]['api_endpoint_redirect'];
		$api_endpoint_token = $rows[0]['api_endpoint_token'];
		$api_endpoint_authentication = $rows[0]['api_endpoint_authentication'];
		$api_endpoint_username = $rows[0]['api_endpoint_username'];
		$api_endpoint_password = $rows[0]['api_endpoint_password'];
		
	} else {
		send_api_message(404, "API Carrier not found .".$api_carrier." - ".$api_endpoint." - ".$_SESSION['domain_uuid']."");
	}

	unset ($parameters, $sql);

	if ($request_method == 'GET') {
		if (strpos($api_endpoint_redirect, ':domain_uuid') > 0){
			$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
		}
		foreach($endpoints as $key => $value){
			if ($key == 'api_key') continue;
			if (strlen($value) > 0) {
				$parameters[$key] = $value;
			}
		}

		$command = exec('which php')." ".$_SERVER['DOCUMENT_ROOT']."/app/api/resources/functions/get_queue.php ";
		$command .= "'".$api_endpoint_token."' '".$api_endpoint_username."' '".$api_endpoint_password."' '".$api_endpoint_uri."' '".$domain_uuid."' '".$restapi_endpoint_uuid."'";
		$result = system($command);
//		send_data(system($command));
		//send_api_message(200, "".$result."");
		
/*		

api_endpoint_token
api_endpoint_username
api_endpoint_password
api_endpoint_uri.api_endpoint_redirect




$apiToken = $argv[1];
$apiUser = $argv[2];
$apiPass = $argv[3];
$url = $argv[4];





    echo "api_endpoint_uri: ".$api_endpoint_uri."\n";
		echo "<br>\n";
		echo "rewrite_uri: ".$rewrite_uri."\n";
		echo "<br>\n";
		echo "api_endpoint_redirect: ".$api_endpoint_redirect."\n";
		echo "<br>\n";
		echo "api_endpoint_category: ".$api_endpoint_category."\n";
		echo "<br>\n";
		echo "api_endpoint_token: ".$api_endpoint_token."\n";
		echo "<br>\n";
		echo "api_carrier: ".$api_carrier."\n";
		echo "<br>\n";
		echo "api_endpoint_authentication: ".$api_endpoint_authentication."\n";
		echo "<br>\n";
		echo "api_endpoint_username: ".$api_endpoint_username."\n";
		echo "<br>\n";
		echo "api_endpoint_password: ".$api_endpoint_password."\n";
		echo "<br>\n";*/
//		echo "api_endpoint: ".$api_endpoint."\n";	


//		var_dump($parameters);
//		echo "<br>\n";
		//exit;
//		send_api_message(200, "API Carrier not found .".$api_endpoint_uri." - ".$api_endpoint_token." - ".$api_endpoint_token." - ".$api_endpoint_token." - ".$api_endpoint_authentication." - ".$_SESSION['domain_uuid']."");

//		$rows = $database->select($api_endpoint_redirect, $parameters, 'all');
/*		if (is_array($rows) && @sizeof($rows) != 0) {
			send_data($rows);
		} else {
			send_api_message(200, "Empty result set.");
		}*/
	exit;
	}

	if ($request_method == 'POST') {
		$data = json_decode(file_get_contents("php://input"), TRUE);
		if (permission_exists('restapi_domain_in_data')) {
			if (strpos($api_sql, ':domain_uuid') > 0){
				$data['domain_uuid'] = $_SESSION['domain_uuid'];
			}
		}
		if (permission_exists('restapi_new_uuid_in_data')) {
			$data['new_uuid'] = uuid();
		}

		foreach($endpoints as $key => $value){
			if ($key == 'api_key') continue;
			if (strlen($value) > 0) {
				$data[$key] = $value;
			}
		}

		//var_dump($data);
		//echo "<br>\n".$api_sql."<br>\n";
		//exit;

		$database->execute($api_sql, $data, 'all');
		send_api_message($database->message['code'], $database->message['message']);
		echo $database->message['error']['message']."\n";
		exit;
	}

	if ($request_method == 'PUT') {
		$data = json_decode(file_get_contents("php://input"), TRUE);
		if (!permission_exists('restapi_domain_in_data')) {
			if (strpos($api_sql, ':domain_uuid') > 0){
				$data['domain_uuid'] = $_SESSION['domain_uuid'];
			}
		}

		foreach($endpoints as $key => $value){
			if ($key == 'api_key') continue;
			if (strlen($value) > 0) {
				$data[$key] = $value;
			}
		}

		//var_dump($data);
		//echo "<br>\n".$api_sql."<br>\n";
		//exit;

		$database->execute($api_sql, $data, 'all');
		send_api_message($database->message['code'], $database->message['message']);
		//echo $database->message['error']['message']."\n";
		exit;
	}

	if ($request_method == 'DELETE') {

		if (strpos($api_sql, ':domain_uuid') > 0){
			$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
		}
		foreach($endpoints as $key => $value){
			if ($key == 'api_key') continue;
			if (strlen($value) > 0) {
				$parameters[$key] = $value;
			}
		}

		//var_dump($data);
		//echo "<br>\n".$api_sql."<br>\n";
		//exit;

		$database->execute($api_sql, $parameters, 'all');
		send_api_message($database->message['code'], $database->message['message']);
		//echo $database->message['error']['message']."\n";
		exit;
	}

exit;



}
?>

