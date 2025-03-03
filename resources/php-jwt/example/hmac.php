<?php

$key = "example_key";
$payload = array(
    "data" => [
        "name" => "ZiHang Gao",
        "admin" => true
    ],
    "iss" => "http://example.org",
    "sub" => "1234567890",
);

// default HS256 algorithm
$token = jwt_encode($payload, $key);

echo $token . PHP_EOL;
print_r(jwt_decode($token, $key));