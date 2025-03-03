<?php
include 'get.php';

if (PHP_SAPI === 'cli') {
    $apiToken = $argv[1];
    $apiUser = $argv[2];
    $apiPass = $argv[3];
    $url = $argv[4];
    $domain_uuid = $argv[5];
    $restapi_endpoint_uuid = $argv[6];
}
else {
    $apiToken = $_GET['apiToken'];
    $apiUser = $_GET['apiUser'];
    $apiPass = $_GET['apiPass'];
    $url = $_GET['url'];
    $domain_uuid = $_GET['domain_uuid'];
    $restapi_endpoint_uuid = $_GET['restapi_endpoint_uuid'];
}

/*$apiToken = '7153d33c-4b3f-469d-9a87-05c1ac39980f';
$apiUser = 'daniel@flux.net.br';
$apiPass = 'Flux@8080';
$url = 'flux.movidesk.com';*/

$data = getQueue($url,$apiToken,$apiUser,$apiPass,$domain_uuid,$restapi_endpoint_uuid);
$json_decoded = json_decode($data,true);
header("Content-Type: application/json; charset=UTF-8");
//echo $json_decoded;
//  header("Content-Type: application/json; charset=UTF-8");
//	http_response_code(200);
	echo json_encode($data);
	exit;




/*$queue = getQueue();
$json_decoded = json_decode($queue,true);
foreach($json_decoded as $result){
$movidesk_id = $result['Id'];
$queue_name = $result['Id'];
$queue_id= $result['QueueId'];
$queue_status= $result['IsActive'];

}



print_r($id);
*/

?>