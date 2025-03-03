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
	Copyright (C) 2015 - 2021
	All Rights Reserved.

	Contributor(s):
	Daniel Paixao <daniel@flux.net.br>
*/



$conf = glob("{/usr/local/etc,/etc}/fluxpbx/config.conf", GLOB_BRACE);
set_include_path(parse_ini_file($conf[0])['document.root']);

//includes files
require_once "resources/require.php";

function getToken($url,$apiToken,$apiUser,$apiPass){
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://'.$url.'/Account/Authenticate',
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
$_SESSION['MOVIDESK_COOKIE'] = $key;
$cookie = $_SESSION['MOVIDESK_COOKIE'];
return $key;



}

function getCalls($url,$apiToken,$apiUser,$apiPass){

$key = getToken($url,$apiToken,$apiUser,$apiPass);
$client = new http\Client;
$request = new http\Client\Request;
$request->setRequestUrl('https://'.$url.'/CallQueue/InQueue');
$request->setRequestMethod('GET');
$request->setOptions(array());
$request->setHeaders(array(
  'Content-Type' => 'application/x-www-form-urlencoded',
  'Cookie' => '.ASPXAUTH='.$key.''
));
$client->enqueue($request)->send();
$response = $client->getResponse();
$string = $response->getBody();
$arr = json_decode($string,true);
$meta = $arr['results'][0];
$agent = $meta['agent'];
$branchLine = $meta['branchLine'];
$statusInTelephony = $meta['statusInTelephony'];
$next = json_encode($meta, JSON_PRETTY_PRINT);
echo $string;
//$insertDB = pgDB($url,$apiToken,$apiUser,$apiPass);
//$insertDB = pgDB();
}

function getAgent($url,$apiToken,$apiUser,$apiPass){

$key = getToken($url,$apiToken,$apiUser,$apiPass);
$client = new http\Client;
$request = new http\Client\Request;
$request->setRequestUrl('https://'.$url.'/CallQueue/AgentsIndicator');
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

function getAgents($url,$apiToken,$apiUser,$apiPass){

$key = getToken($url,$apiToken,$apiUser,$apiPass);
$client = new http\Client;
$request = new http\Client\Request;
$request->setRequestUrl('https://'.$url.'/CallQueue/AgentsIndicator');
$request->setRequestMethod('GET');
$request->setOptions(array());
$request->setHeaders(array(
  'Cookie' => '.ASPXAUTH='.$key.''
));
$client->enqueue($request)->send();
$response = $client->getResponse();
$json_decodeda = json_decode($response,true);
$agentsq = json_encode($json_decodeda['results']);
$json_decodeda = json_decode($agentsq,true);


foreach ($json_decodeda['results'] as $key => $value){
$agent = $value['agent'];
$line = $value['branchLine'];
$userstatus = $value['userStatus'];
if ($userstatus == "2") {
$userStat = "Offline";
}
else {
$userStat = "Online";
}
$telstatus = $value['statusInTelephony'];
if ($telstatus == "2") {
$telStat = "Offline";
}
else {
$telStat = "Online";
}
$busy = $value['inAttendance'];


if ($value['inAttendance'] == "") {
$busy = "Available";
}
else {
$busy = $value['inAttendance'];
}





}
$agent_uuid = uuid();
$date = gmdate("Y-m-d\TH:i:s");
$connection = pg_connect("host=127.0.0.1 port=5432 dbname=fluxpbx user=fluxpbx password=MDJJlsugl2eKVWQ4QLIp9u9W8");
$sql = "UPDATE v_flux_movidesk_agents SET agent_name = '".$agent."',userstatus = '".$userStat."',statusintelephony='".$telStat."',last_call = '".$date."', inattendance = '".$inattendance."' WHERE branchline='".$line."';
INSERT INTO v_flux_movidesk_agents (flux_movidesk_agent_uuid, agent_name, branchline, userstatus, statusintelephony, inattendance, last_call, repete, chamadas, last_update, incall, call_timeout, destination_delay, gateway_uuid, profile, domain_name, domain_uuid)
       SELECT '".$agent_uuid."', '".$agent."', '".$line."', '".$userStat."','".$telStat."','".$inattendance."','".$date."','0','0','".$date."','f','60', '5', '0893c969-c436-43e5-b4d9-35ef89e79a39', 'movidesk', 'movidesk-api.flux.cloud', '62899871-3c30-40a7-b732-ff0da1610bc8'
       WHERE NOT EXISTS (SELECT 1 FROM v_flux_movidesk_agents WHERE branchline='".$line."');";
$insert = pg_query($connection,$sql);
echo $response->getBody();
}

function getQueue($url,$apiToken,$apiUser,$apiPass,$domain_uuid,$api_uuid){
$queues = null;
//$url = null;
$key = getToken($url,$apiToken,$apiUser,$apiPass);
$client = new http\Client;
$request = new http\Client\Request;
$request->setRequestUrl('https://'.$url.'/TelephonyGroup/Json?Start=0&Limit=80');
$request->setRequestMethod('GET');
$request->setOptions(array());
$request->setHeaders(array(
  'Cookie' => '.ASPXAUTH='.$key.''
));
$client->enqueue($request)->send();
$response = $client->getResponse();
$data = trim($response->getBody());
$string = str_replace(')', '', $data);
$string2 = str_replace('(', '', $string);
$string3 = str_replace(';', '', $string2);
$json_decoded = json_decode($string3,true);
$queued = json_encode($json_decoded['results']);
$json_decodedq = json_decode($queued,true);
foreach($json_decodedq as $resultq){
$queue_id = $resultq['Id'];
$queue_name = $resultq['Name'];
$queue_extension= $resultq['QueueId'];
$queue_status= $resultq['IsActive'];
if ($queue_status == "1") {
$queue_status = "true";
}
else {
$queue_status = "false";
}
$flux_movidesk_queue_uuid = uuid();
$connection = pg_connect("host=127.0.0.1 port=5432 dbname=fluxpbx user=fluxpbx password=MDJJlsugl2eKVWQ4QLIp9u9W8");
$sql = "UPDATE v_flux_movidesk_queues SET queue_id='".$queue_id."', queue_name = '".$queue_name."',queue_extension = '".$queue_extension."',queue_enabled = '".$queue_status."', domain_uuid = '".$domain_uuid."', queue_description = '".$url."',update_date = now(),update_user = '".$api_uuid."' WHERE queue_id='".$queue_id."' and queue_name='".$queue_name."';
INSERT INTO v_flux_movidesk_queues (queue_id, queue_name, queue_extension,queue_enabled,domain_uuid,queue_description,insert_date,insert_user)
       SELECT '".$queue_id."','".$queue_name."', '".$queue_extension."', '".$queue_status."' , '".$domain_uuid."', '".$url."', 'now()', '".$api_uuid."'
       WHERE NOT EXISTS (SELECT 1 FROM v_flux_movidesk_queues WHERE queue_id='".$queue_id."' and queue_name='".$queue_name."');";

$insert = pg_query($connection,$sql);
}

//return $json_decodedq;
/*$json_decodedq = json_decode($queued,true);
foreach($json_decodedq as $resultq){
$queue_outbound_caller_id_number = $resultq['Id'];
$queue_name = $resultq['Name'];
$queue_extension= $resultq['QueueId'];
$queue_status= $resultq['IsActive'];

if ($queue_status == "1") {
$queue_status = "true";
}
else {
$queue_status = "false";
}*/



echo json_encode(
    array(
        "message" => "Successful API.",
        "queue_id" => $queue_id,
        "queue_name" => $queue_name,
        "queue_extension" => $queue_extension,
        "queue_status" => $queue_status
    ));





//return $queue;

//print_r($queue);
}

function insertDb($url,$apiToken,$apiUser,$apiPass){

$tableName = 'flux_movidesk';
$connection = pg_connect("host=127.0.0.1 port=5432 dbname=fluxpbx user=fluxpbx password=MDJJlsugl2eKVWQ4QLIp9u9W8");
$key = getToken($url,$apiToken,$apiUser,$apiPass);
$client = new http\Client;
$request = new http\Client\Request;
$request->setRequestUrl('https://'.$url.'/CallQueue/AgentsIndicator');
$request->setRequestMethod('GET');
$request->setOptions(array());
$request->setHeaders(array(
  'Cookie' => '.ASPXAUTH='.$key.''
));
$client->enqueue($request)->send();
$response = $client->getResponse();
$json_decoded = json_decode($response->getBody(),true);


foreach ($json_decoded['results'] as $id=>$row) {
    $insertPairs = array();
    foreach ($row as $key=>$val) {
        $insertPairs[addslashes($key)] = addslashes($val);
    }
    $insertKeys = '"' . implode('","', array_keys($insertPairs)) . '"';
    $insertVals = "'" . implode("','", array_values($insertPairs)) . "'";

    
    echo "INSERT INTO {$tableName} ({$insertKeys}) VALUES ({$insertVals});" . "\n";
    $sql = "INSERT INTO {$tableName} ({$insertKeys}) VALUES ({$insertVals});" . "\n";
    $insert = pg_query($connection,$sql);
}


}

function pgDB($url,$apiToken,$apiUser,$apiPass){
$key = getToken($url,$apiToken,$apiUser,$apiPass);
$connection = pg_connect("host=127.0.0.1 port=5432 dbname=fluxpbx user=fluxpbx password=MDJJlsugl2eKVWQ4QLIp9u9W8");
$agent_uuid = uuid();
$date = gmdate("Y-m-d\TH:i:s");
//$key = getToken();
$client = new http\Client;
$request = new http\Client\Request;
$request->setRequestUrl('https://'.$url.'/CallQueue/AgentsIndicator');
$request->setRequestMethod('GET');
$request->setOptions(array());
$request->setHeaders(array(
  'Cookie' => '.ASPXAUTH='.$key.''
));
$client->enqueue($request)->send();
$response = $client->getResponse();
$json_decoded = json_decode($response->getBody(),true);
foreach ($json_decoded['results'] as $key => $value){
$inattendance = $value['inAttendance'];
if ($inattendance == "false"){

$inattendance = "1";

} else{

$inattendance = "0";
}


$sql = "UPDATE v_flux_movidesk_agents SET agent_name = '".$value['agent']."',userstatus = '".$value['userStatus']."',statusintelephony='".$value['statusInTelephony']."',last_call = '".$date."', inattendance = '".$inattendance."' WHERE branchline='".$value['branchLine']."';
INSERT INTO v_flux_movidesk_agents (flux_movidesk_agent_uuid, agent_name, branchline, userstatus, statusintelephony, inattendance, last_call, repete, chamadas, last_update, incall, call_timeout, destination_delay, gateway_uuid, profile, domain_name, domain_uuid)
       SELECT '".$agent_uuid."', '".$value['agent']."', '".$value['branchLine']."', '".$value['userStatus']."','".$value['statusInTelephony']."','".$inattendance."','".$date."','0','0','".$date."','f','60', '5', '0893c969-c436-43e5-b4d9-35ef89e79a39', 'movidesk', 'movidesk-api.flux.cloud', '87c54e55-14d2-49d2-91b8-a353d4614da3'
       WHERE NOT EXISTS (SELECT 1 FROM v_flux_movidesk_agents WHERE branchline='".$value['branchLine']."');";
$insert = pg_query($connection,$sql);
unset($sql);
$line = $value['branchLine'];
$fifo_uuid = $agent_uuid;


$sql = "UPDATE fifo_outbound SET originate_string = '{fifo_member_wait=nowait}user/".$line."@movidesk-api.flux.cloud' WHERE uuid='".$fifo_uuid."' and originate_string = '{fifo_member_wait=nowait}user/".$line."@movidesk-api.flux.cloud';
INSERT INTO fifo_outbound (originate_string,fifo_name,uuid,simo_count,use_count,timeout,lag,next_avail,expires,static,outbound_call_count,outbound_fail_count,hostname,taking_calls,status,outbound_call_total_count,outbound_fail_total_count,active_time,inactive_time,manual_calls_out_count,manual_calls_in_count,manual_calls_out_total_count,manual_calls_in_total_count,ring_count,start_time,stop_time,fifo_outbound_uuid)
       SELECT '{fifo_member_wait=nowait}user/".$line."@movidesk-api.flux.cloud','movidesk@movidesk-api.flux.cloud','".$fifo_uuid."',1,0,10,10,1678436479,0,0,0,0,'glcoud.flux.cloud',1,NULL,0,0,0,0,0,0,0,0,0,0,0,'".$fifo_uuid."'
       WHERE NOT EXISTS (SELECT 1 FROM fifo_outbound WHERE originate_string = '{fifo_member_wait=nowait}user/".$line."@movidesk-api.flux.cloud');";
$insert = pg_query($connection,$sql);
unset($sql);

/*$sql = "INSERT INTO fifo_outbound (uuid,fifo_name,originate_string,simo_count,use_count,timeout,lag,next_avail,expires,static,outbound_call_count,outbound_fail_count,hostname,taking_calls,status,outbound_call_total_count,outbound_fail_total_count,active_time,inactive_time,manual_calls_out_count,manual_calls_in_count,manual_calls_out_total_count,manual_calls_in_total_count,ring_count,start_time,stop_time,fifo_outbound_uuid) VALUES ('".$fifo_uuid."','movidesk@movidesk-api.flux.cloud','{fifo_member_wait=nowait}user/".$line."@movidesk-api.flux.cloud',1,0,10,10,1678436479,0,0,0,0,'glcoud.flux.cloud',1,NULL,0,0,0,0,0,0,0,0,0,0,0,'".$fifo_uuid."');";
$insert = pg_query($connection,$sql);
unset($sql);*/

return $fifo_uuid;


}
}

function getAuthorizationHeader(){
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

function getBearerToken(){
    $headers = getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}
?>