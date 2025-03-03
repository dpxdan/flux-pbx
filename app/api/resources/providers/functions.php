<?php

/*header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-User-Agent");
header("Access-Control-Allow-Credentials: true");*/
header("Content-Type: application/json; charset=UTF-8");

$api = $_GET['uri'];

if ($api == "token"){
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
                  
                  
//header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-User-Agent");
header("Access-Control-Allow-Origin: https://gcloud.flux.cloud");
header("Access-Control-Allow-Credentials: true");
http_response_code(200);
echo json_encode($params);


}

elseif ($api == "info"){

$jsonFile = '/usr/src/widget/dist/api/api_dist/login.json';
$jsonData = json_decode(file_get_contents($jsonFile), true);


//$res = array("status" => $strStatus, "data" => $strData);

//if($clientId){
//$res = json_encode($strStatus);
//echo $res;
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-User-Agent, client-id");
header("Access-Control-Allow-Origin: https://gcloud.flux.cloud");
header("Access-Control-Allow-Credentials: true");
http_response_code(200);
echo json_encode($jsonData);

}

elseif ($api == "callerid"){

$jsonFile = '/usr/src/widget/dist/api/api_dist/caller_id.json';
$jsonData = json_decode(file_get_contents($jsonFile), true);


//$res = array("status" => $strStatus, "data" => $strData);

//if($clientId){
//$res = json_encode($strStatus);
//echo $res;
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-User-Agent, client-id");
header("Access-Control-Allow-Origin: https://gcloud.flux.cloud");
header("Access-Control-Allow-Credentials: true");
http_response_code(200);
echo json_encode($jsonData);

}

elseif ($api == "device"){


$jsonFile = '/usr/src/widget/dist/api/api_dist/device.json';
$jsonData = json_decode(file_get_contents($jsonFile), true);


//$res = array("status" => $strStatus, "data" => $strData);

//if($clientId){
//$res = json_encode($strStatus);
//echo $res;
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-User-Agent, client-id");
header("Access-Control-Allow-Origin: https://gcloud.flux.cloud");
header("Access-Control-Allow-Credentials: true");
http_response_code(200);
echo json_encode($jsonData);


}

elseif ($api == "features"){

$jsonFile = '/usr/src/widget/dist/api/api_dist/features.json';
$jsonData = json_decode(file_get_contents($jsonFile), true);


//$res = array("status" => $strStatus, "data" => $strData);

//if($clientId){
//$res = json_encode($strStatus);
//echo $res;
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-User-Agent, client-id");
header("Access-Control-Allow-Origin: https://gcloud.flux.cloud");
header("Access-Control-Allow-Credentials: true");
http_response_code(200);
echo json_encode($jsonData);

}

elseif ($api == "extension"){

$jsonFile = '/usr/src/widget/dist/api/api_dist/ext.json';
$jsonData = json_decode(file_get_contents($jsonFile), true);


//$res = array("status" => $strStatus, "data" => $strData);

//if($clientId){
//$res = json_encode($strStatus);
//echo $res;
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-User-Agent, client-id");
header("Access-Control-Allow-Origin: https://gcloud.flux.cloud");
header("Access-Control-Allow-Credentials: true");
http_response_code(200);
echo json_encode($jsonData);

}

elseif ($api == "contacts"){

$jsonFile = '/usr/src/widget/dist/api/api_dist/directory.json';
$jsonData = json_decode(file_get_contents($jsonFile), true);


//$res = array("status" => $strStatus, "data" => $strData);

//if($clientId){
//$res = json_encode($strStatus);
//echo $res;
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-User-Agent, client-id");
header("Access-Control-Allow-Origin: https://gcloud.flux.cloud");
header("Access-Control-Allow-Credentials: true");

http_response_code(200);
echo json_encode($jsonData);

}

elseif ($api == "account"){

$jsonFile = '/usr/src/widget/dist/api/api_dist/account.json';
$jsonData = json_decode(file_get_contents($jsonFile), true);


//$res = array("status" => $strStatus, "data" => $strData);

//if($clientId){
//$res = json_encode($strStatus);
//echo $res;
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-User-Agent, client-id");
header("Access-Control-Allow-Origin: https://gcloud.flux.cloud");
header("Access-Control-Allow-Credentials: true");

http_response_code(200);
echo json_encode($jsonData);

}

elseif ($api == "subscription"){

$jsonFile = '/usr/src/widget/dist/api/api_dist/subscription.json';
$jsonData = json_decode(file_get_contents($jsonFile), true);


//$res = array("status" => $strStatus, "data" => $strData);

//if($clientId){
//$res = json_encode($strStatus);
//echo $res;
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-User-Agent, client-id");
header("Access-Control-Allow-Origin: https://gcloud.flux.cloud");
header("Access-Control-Allow-Credentials: true");

http_response_code(200);
echo json_encode($jsonData);

}

elseif ($api == "dialing"){

$jsonFile = '/usr/src/widget/dist/api/api_dist/dialing.json';
$jsonData = json_decode(file_get_contents($jsonFile), true);


//$res = array("status" => $strStatus, "data" => $strData);

//if($clientId){
//$res = json_encode($strStatus);
//echo $res;
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-User-Agent, client-id");
header("Access-Control-Allow-Origin: https://gcloud.flux.cloud");
header("Access-Control-Allow-Credentials: true");

http_response_code(200);
echo json_encode($jsonData);

}

elseif ($api == "provision"){

$jsonFile = '/usr/src/widget/dist/api/api_dist/sip-provision.json';
$jsonData = json_decode(file_get_contents($jsonFile), true);


//$res = array("status" => $strStatus, "data" => $strData);

//if($clientId){
//$res = json_encode($strStatus);
//echo $res;
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-User-Agent, client-id");
header("Access-Control-Allow-Origin: https://gcloud.flux.cloud");
header("Access-Control-Allow-Credentials: true");

http_response_code(200);
echo json_encode($jsonData);

}

else {

echo "ERROR";

}

?>