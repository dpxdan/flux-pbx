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


function send_token(){
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-User-Agent");
header("Access-Control-Allow-Credentials: true");

$url = 'apiflux';
$apiToken = 'c6d0fc5b-2278-45ea-bbe1-a04c00320da0';
$apiUser = 'api-movidesk';
$apiPass = 'c6d0fc5b-2278-45ea-bbe1-a04c00320da0';



//$token_data = json_encode($params);
//return $token_data;                
//http_response_code(200);
//echo json_encode($params);
//send_api_message(200,$params);
$getToken = getToken($url,$apiToken,$apiUser,$apiPass);
//$getQueue = getQueue($url,$getToken);
  header("Content-Type: application/json; charset=UTF-8");
  header("Cookie: ".$_SESSION['MOVIDESK_COOKIE']."");
	http_response_code(200);
	
	echo $getToken;
	exit;
}

function getToken($url,$apiToken,$apiUser,$apiPass){
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://'.$url.'.movidesk.com/Account/Authenticate',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_HEADER => true,
  CURLOPT_SSL_VERIFYPEER => false,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => 'token='.$apiToken.'&username='.$apiUser.'&password='.$apiPass.'',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/x-www-form-urlencoded'
  ),
));
$response = curl_exec($curl);
curl_close($curl);
preg_match_all('/^Set-Cookie:\s*([^;]*)/mi',
          $response,  $match_found);
$cookies = array();
foreach($match_found[1] as $item) {
    parse_str($item,  $cookie);
    $cookies = array_merge($cookies,  $cookie);
    
}
$key = $cookies['_ASPXAUTH'];
$bearer = $cookies['Bearer'];
$token = $cookies['.ASPXAUTH'];
$responseData = $match_found;
$_SESSION['MOVIDESK_COOKIE'] = $token;
$cookie = $_SESSION['MOVIDESK_COOKIE'];

$params = array(
            'token_type'                => "bearer",
            'bearer_token'              => $bearer,
            'expires_in'                => 86000,
            'expire_time'               => 1633919058,
            'auth_token'                => $key,
            'provider'                  => 'movidesk',
            'refresh_token_expires_in'  => 604800,
            'refresh_token_expire_time' => 1634520258,
            'owner_id'                  => $cookies,
            'endpoint_id'               => $url
                  );


$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://'.$url.'.movidesk.com/ExternalLoginToken/AccessUrl?url=https://'.$url.'.movidesk.com/CallQueue/AgentsIndicator&loginToken=jby4IXqr3whQybKLVjV8M1',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_HTTPHEADER => array(
    'Cookie: .ASPXAUTH='.$key.'; Bearer='.$bearer.'; culture=pt-BR',
    'Content-Length: 0'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

//return json_encode($params);
//return $key;



}

function getQueue($url,$key){

$client = new http\Client;
$request = new http\Client\Request;
$request->setRequestUrl('https://'.$url.'.movidesk.com/CallQueue/AgentsIndicator');
$request->setRequestMethod('GET');
$request->setOptions(array());
$request->setHeaders(array(
  'Cookie' => '.ASPXAUTH='.$key.''
));
$client->enqueue($request)->send();
$response = $client->getResponse();
$agents = $response->getBody();
return $agents;
}

function send_api_token(){

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-User-Agent");
header("Access-Control-Allow-Credentials: true");


//Generate a random string.
$token = openssl_random_pseudo_bytes(52);

//Convert the binary data into hexadecimal representation.
$token = bin2hex($token);


$params = array(
            'token_type'                => "bearer",
            'access_token'              => $token,
            'expires_in'                => 86000,
            'expire_time'               => 1633919058,
            'refresh_token'             => $token,
            'refresh_token_expires_in'  => 604800,
            'refresh_token_expire_time' => 1634520258,
            'scope'                     => "ReadMessages CallControl Faxes ReadPresence Meetings VoipCalling ReadCallRecording A2PSMS ReadContacts Contacts ReadAccounts EditExtensions RoleManagement RingOut SMS TeamMessaging InternalMessages ReadCallLog SubscriptionWebhook EditMessages EditPresence",
            'owner_id'                  => "295740004",
            'endpoint_id'               => "88kkEMm0Sdi6QfiM0v9ngA"
                  );
                  
//$token_data = json_encode($params);
//return $token_data;                
//http_response_code(200);
//echo json_encode($params);
//send_api_message(200,$params);
    header("Content-Type: application/json; charset=UTF-8");
	http_response_code(200);
	echo json_encode($params);
	exit;
}


function getAgents($url,$key,$LoginToken){

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://'.$url.'.movidesk.com/ExternalLoginToken/AccessUrl?url=https://'.$url.'.movidesk.com/CallQueue/AgentsIndicator&loginToken='.$LoginToken.'',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_HTTPHEADER => array(
    'Cookie: '.$sessionCookie.'; culture=pt-BR'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
}


?>