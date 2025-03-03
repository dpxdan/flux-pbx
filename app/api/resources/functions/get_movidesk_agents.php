<?php

//check the permission
	if (defined('STDIN')) {
		//set the include path
		$conf = glob("{/usr/local/etc,/etc}/fluxpbx/config.conf", GLOB_BRACE);
		set_include_path(parse_ini_file($conf[0])['document.root']);

		//includes files
		require_once "resources/require.php";
	}
	else {
		//only allow running this from command line
		exit;
	}


	//increase limits
		set_time_limit(0);
		ini_set('max_execution_time', 0);
		ini_set('memory_limit', '256M');
	
	//save the arguments to variables
		$script_name = $argv[0];
		if (!empty($argv[1])) {
			parse_str($argv[1], $_GET);
		}
		
		
	//set the variables
		if (isset($_GET['hostname'])) {
			$hostname = urldecode($_GET['hostname']);
		}

		if (isset($_GET['url'])) {
			$url = urldecode($_GET['url']);
		}
		
		
		if (isset($_GET['apiToken'])) {
			$apiToken = urldecode($_GET['apiToken']);
		}
		
		
		if (isset($_GET['apiUser'])) {
			$apiUser = urldecode($_GET['apiUser']);
		}


		if (isset($_GET['apiPass'])) {
			$apiPass = urldecode($_GET['apiPass']);
		}


		if (isset($_GET['debug'])) {
			if (is_numeric($_GET['debug'])) {
				$debug_level = $_GET['debug'];
			}
			$debug = true;
		}
	
	   $debug = false;	
	
	//get the hostname
		if (!isset($hostname)) {
			$hostname = gethostname();
		}
	
	
	if (!isset($url)) {
		$url = 'apiflux';
	}
	
	if (!isset($apiToken)) {
		$apiToken = 'c6d0fc5b-2278-45ea-bbe1-a04c00320da0';
	}
	
	if (!isset($apiUser)) {
		$apiUser = 'api-movidesk';
	}
	
	if (!isset($apiPass)) {
		$apiPass = 'c6d0fc5b-2278-45ea-bbe1-a04c00320da0';
	}
	

	
	//set the event socket variables
		$event_socket_ip_address = $_SESSION['event_socket_ip_address'];
		$event_socket_port = $_SESSION['event_socket_port'];
		$event_socket_password = $_SESSION['event_socket_password'];



		//end the session
			session_destroy();

	
	


$connection = pg_connect("host=127.0.0.1 port=5432 dbname=fluxpbx user=fluxpbx password=MDJJlsugl2eKVWQ4QLIp9u9W8");
$date = gmdate("Y-m-d\TH:i:s");

//connect to event socket
$fp = event_socket_create($_SESSION['event_socket_ip_address'], $_SESSION['event_socket_port'], $_SESSION['event_socket_password']);


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
$_SESSION['MOVIDESK_COOKIE'] = $key;
$cookie = $_SESSION['MOVIDESK_COOKIE'];
return $key;



}

function getAgents($url,$key){

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

function getQueue($url,$key){
$queues = null;
//$url = null;

$client = new http\Client;
$request = new http\Client\Request;
$request->setRequestUrl('https://'.$url.'.movidesk.com/TelephonyGroup/Json?Start=0&Limit=80');
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
$queues = str_replace(';', '', $string2);

return $queues;

}

$getToken = getToken($url,$apiToken,$apiUser,$apiPass);

$getAgents = getAgents($url,$getToken);

$getQueue = getQueue($url,$getToken);

$json_decoded = json_decode($getAgents,true);
foreach ($json_decoded['results'] as $key => $value){

$inattendance = $value['inAttendance'];
$userStatus = $value['userStatus'];
$telStatus = $value['statusInTelephony'];
if ($inattendance == "false"){

$inattendance = "1";

} 
else{

$inattendance = "0";
}


if ($userStatus == "1"){
$userStatus = "Available";
} 
else{
$userStatus = "Logged Out";
}


if ($telStatus == "1"){
$telStatus = "Available";
} 
else{

$telStatus = "Logged Out";
}

$agent_uuid = 'gen_random_uuid()';

$sql = "INSERT INTO v_flux_movidesk_agents VALUES (gen_random_uuid(),'".$value['branchLine']."','".$userStatus."','".$telStatus."','".$inattendance."','".$date."','0','0','".$date."','f','60','5','e4fcf990-9852-4d06-bc77-7be6a6c244fe','movidesk','movidesk-api.flux.cloud','87c54e55-14d2-49d2-91b8-a353d4614da3',NULL,NULL,NULL,NULL,'".$value['agent']."',NULL)
ON CONFLICT(branchline) DO UPDATE SET userstatus = '".$userStatus."', statusintelephony = '".$telStatus."',inattendance = '".$inattendance."',update_date = 'now()', last_update = '".$date."',agent_name = '".$value['agent']."';";
$insert = pg_query($connection,$sql);
unset($sql);


}





$json_decodedq = json_decode($getQueue,true);
foreach ($json_decodedq['results'] as $key => $value){

$queue_enabled = $value['IsActive'];
$queue_extension = $value['QueueId'];
$queue_name = $value['Name'];
$queue_id = $value['Id'];
if ($queue_enabled == "1"){

$queue_enabled = "true";

} 
else{

$queue_enabled = "false";
}

$queue_uuid = 'gen_random_uuid()';

$sqlq = "INSERT INTO v_flux_movidesk_queues VALUES (".$queue_uuid.",'87c54e55-14d2-49d2-91b8-a353d4614da3','".$queue_id."','".$queue_name."','".$queue_extension."','now()',".$queue_uuid.",'now()',".$queue_uuid.",'".$queue_enabled."','".$apiToken."','".$url."')
ON CONFLICT(queue_id) DO UPDATE SET queue_extension = '".$queue_extension."', queue_name = '".$queue_name."', queue_enabled = '".$queue_enabled."',queue_token = '".$apiToken."',update_date = 'now()',queue_url = '".$url."';";
$insert = pg_query($connection,$sqlq);
unset($sqlq);


}


//get the list
	$sql = "select d.domain_name, call_center_agent_uuid, extension, user_context, flux_movidesk_agent_uuid,branchline,userstatus as user_status,statusintelephony as agent_status,ma.agent_name, inattendance,last_call ";
	$sql .= "from v_extensions as e, v_domains as d, v_flux_movidesk_agents as ma, v_call_center_agents as a ";
	$sql .= "where e.enabled = 'true' ";
	$sql .= "and e.domain_uuid = d.domain_uuid ";
	$sql .= "and e.extension = ma.branchline::text ";
	$sql .= "and a.user_uuid = ma.user_uuid ";
	$sql .= "and e.domain_uuid = ma.domain_uuid ";
	$sql .= "and enabled = 'true' ";
	$database = new database;
	$results = $database->select($sql, $parameters, 'all');
	unset($parameters);
//	print_r($sql);

	foreach($results as $row) {
	
//	$agent_status = $row['agent_status'];
//	$user_status = $row['user_status'];
	

	if (isset($row['flux_movidesk_agent_uuid']) && isset($row['call_center_agent_uuid'])) {
									//set the call center status
	
						$command = "api callcenter_config agent set status " . $row['call_center_agent_uuid'] . " '" . $row['agent_status'] . "'";
						$command2 = "api callcenter_config agent set custom_status " . $row['call_center_agent_uuid'] . " '" . $row['user_status'] . "'";
						$command3 = "api callcenter_config agent set provider_status " . $row['call_center_agent_uuid'] . " '" . $row['flux_movidesk_agent_uuid'] . "'";
	
						$response = event_socket_request($fp, $command);
						$response2 = event_socket_request($fp, $command2);
						$response3 = event_socket_request($fp, $command3);
						//print_r($response, false);
	
					}

		}

//	$json_decoded = json_decode($getQueue,true);
	


//print_r($json_decodedq, false);
		
//		echo $getAgents;
		
		$res = $getAgents;
		echo $res;
/*
* * * * * cd /var/www/fluxpbx && php /var/www/fluxpbx/app/api/resources/functions/get_movidesk_agents.php
*/
