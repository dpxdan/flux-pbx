<?php
$payload = ['esquadgroup' => 'sy92YPqV0QjSiphCBN7oSLrd8QoMNMeB'];

// IMPORTANT: set null as key parameter
$token = jwt_encode($payload, null, 'none');
//$token = 'ZXNxdWFkZ3JvdXA6c3k5MllQcVYwUWpTaXBoQ0JON29TTHJkOFFvTU5NZUI=';
// eyJ0eXAiOiJKV1QiLCJhbGciOiJub25lIn0.eyJkYXRhIjoidGVzdCJ9.
echo $token;

// Set key to nil and options to false otherwise this won't work
$decoded_token = jwt_decode($token, null, false);

// Array
// (
//    [data] => test
// )
print_r($decoded_token);