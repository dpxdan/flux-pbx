<?php
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



?>