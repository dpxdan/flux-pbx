<?php


function send_api_message($code, $text)
{
	header("Content-Type: application/json; charset=UTF-8");
	http_response_code($code);
	$data = array();
	$data['message'] = $text;
	echo json_encode($data);
	exit;
}

function send_data(&$data)
{
	header("Content-Type: application/json; charset=UTF-8");
	http_response_code(200);
	echo json_encode($data);
	exit;
}

function send_access_denied()
{
	send_api_message(403, "Access denied.");
}

function getAuthorizationHeader()
{
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    }
    else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        //print_r($requestHeaders);
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

function getBearerToken()
{
    $headers = getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}

function provider_by_name($provider) {
	$sql = "select * from v_devices ";
	$sql .= "where device_mac_address = :provider ";
	$sql .= "and device_enabled = 'true' ";
	$parameters['mac'] = $provider;
	$database = new database;
	$row = $database->select($sql, $parameters, 'row');
	return is_array($row) && @sizeof($row) != 0 ? $row : false;
	unset($sql, $parameters, $row);
}

function provider_by_ext($ext, $domain) {
	$sql = "select t1.* ";
	$sql .= "from v_devices t1 ";
	$sql .- "inner join v_device_lines t2 on t1.device_uuid = t2.device_uuid ";
	$sql .= "inner join v_domains t3 on t2.domain_uuid = t3.domain_uuid ";
	$sql .= "where t2.user_id = :ext ";
	$sql .= "and t3.domain_name = :domain ";
	$sql .= "and t3.domain_enabled = 'true' ";
	$sql .= "and t1.device_enabled = 'true' ";
	$parameters['ext'] = $ext;
	$parameters['domain'] = $domain;
	$database = new database;
	$row = $database->select($sql, $parameters, 'row');
	return is_array($row) && @sizeof($row) != 0 ? $row : false;
	unset($sql, $parameters, $row);
}

?>