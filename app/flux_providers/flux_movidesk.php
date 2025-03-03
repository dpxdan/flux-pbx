<?php
/*
zbx 36f5c2f95bbe7762624d7a0cf1aa792613dd0a6e11584e768aa20e0c841c9e94
*/

if (PHP_SAPI === 'cli') {
    $id = $argv[1];
//    $number = $argv[2];
    $token = $argv[2];
//    $queueId = $argv[4];
    $apiAction = $argv[3];
}
else {
    $id = $_GET['id'];
//    $number = $_GET['number'];
    $token = $_GET['token'];
//    $queueId = $_GET['queueId'];
    $action = $_GET['action'];
}

$date = gmdate("Y-m-d\TH:i:s");


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

function send_api_error()
{
	send_api_message(404, "API Error.");
}


if (isset($action) && $action == 'startCall') {
$number = $_GET['number'];
$queueId = $_GET['queueId'];
require_once 'HTTP/Request2.php';
$request = new HTTP_Request2();
$request->setUrl('https://api.movidesk.com/public/v1/asterisk_receivedCall?token='.$token.'&queueId='.$queueId.'&clientNumber='.$number.'&id='.$id.'&callDate='.$date.'');
$request->setMethod(HTTP_Request2::METHOD_POST);
$request->setConfig(array(
  'follow_redirects' => TRUE
));
try {
  $response = $request->send();
  if ($response->getStatus() == 200) {
    $extension = $response->getBody();
    $ext = str_replace('"', "", $extension);
    if ($ext == "3" || $ext == "2" || $ext == "4") {
    send_api_message(403, 'Agentes Ocupados');
    }
    else {
    
    send_api_message(200, $ext);

  }

}

}
catch(HTTP_Request2_Exception $e) {
  echo 'Error: ' . $e->getMessage();
}

}

else if (isset($action) && $action == 'cancelCall') {
require_once 'HTTP/Request2.php';
$request = new HTTP_Request2();
$request->setUrl('https://api.movidesk.com/public/v1/asterisk_canceledCall?token='.$token.'&id='.$id.'&canceledDate='.$date.'');
$request->setMethod(HTTP_Request2::METHOD_POST);
$request->setConfig(array(
  'follow_redirects' => TRUE
));
try {
  $response = $request->send();
  if ($response->getStatus() == 200) {
    echo $response->getBody();
  }
  else {
echo "2";
//    echo 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
//    $response->getReasonPhrase();
  }
}
catch(HTTP_Request2_Exception $e) {
  echo 'Error: ' . $e->getMessage();
}
}

else if (isset($action) && $action == 'transferCall') {
$ext = $_GET['ext'];
require_once 'HTTP/Request2.php';
$request = new HTTP_Request2();
$request->setUrl('https://api.movidesk.com/public/v1/asterisk_transferedCall?token='.$token.'&id='.$id.'&branchLine='.$ext.'&transferDate='.$date.'');

$request->setMethod(HTTP_Request2::METHOD_POST);
$request->setConfig(array(
  'follow_redirects' => TRUE
));

try {
  $response = $request->send();
  if ($response->getStatus() == 200) {
    send_api_message(200, 'call::transfered');
  }
  else {
send_api_error();

  }
}
catch(HTTP_Request2_Exception $e) {
  echo 'Error: ' . $e->getMessage();
}
}

else if (isset($action) && $action == 'completedCall') {
require_once 'HTTP/Request2.php';
$request = new HTTP_Request2();

$request->setUrl('https://api.movidesk.com/public/v1/asterisk_completedCall?token='.$token.'&id='.$id.'&link=https://movidesk-api.flux.net.br/app/flux_providers/download.php?id='.$id.'&completedDate='.$date.'');



$request->setMethod(HTTP_Request2::METHOD_POST);
$request->setConfig(array(
  'follow_redirects' => TRUE
));

try {
  $response = $request->send();
  if ($response->getStatus() == 200) {
    send_api_message(200, 'call::completed');
  }
  else {
send_api_error();

  }
}
catch(HTTP_Request2_Exception $e) {
  echo 'Error: ' . $e->getMessage();
}
}



else {

exit;
}