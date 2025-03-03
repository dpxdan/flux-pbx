<?php
$privateKey = file_get_contents('/etc/letsencrypt/live/fluxpbx/privkey.pem');
$publicKey = file_get_contents('/etc/letsencrypt/live/fluxpbx/privkey.pem');

$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImFjMTUwNTJiYWYwZTgwNzAyMjAzZTUzNzBiZjc5MDAzNjQwZTY0MmE5MWRiMTNkODA5NmE0YjJiZjcxNzYwNmE3ZTM4ZGMwZTZlYmYzM2ZlIn0.eyJhdWQiOiJhOGJmMDhkYi00MzUzLTQ1MjgtYjNjOC02OGI1YjVjODc3ZTYiLCJqdGkiOiJhYzE1MDUyYmFmMGU4MDcwMjIwM2U1MzcwYmY3OTAwMzY0MGU2NDJhOTFkYjEzZDgwOTZhNGIyYmY3MTc2MDZhN2UzOGRjMGU2ZWJmMzNmZSIsImlhdCI6MTY4NjA5MjQ4NCwibmJmIjoxNjg2MDkyNDg0LCJleHAiOjE2ODYxNzc1MTcsInN1YiI6Ijk2NDUyMzUiLCJncmFudF90eXBlIjoiIiwiYWNjb3VudF9pZCI6MzEzMDQ4MTUsImJhc2VfZG9tYWluIjoia29tbW8uY29tIiwidmVyc2lvbiI6InYxIiwic2NvcGVzIjpbInB1c2hfbm90aWZpY2F0aW9ucyIsImZpbGVzIiwiY3JtIiwiZmlsZXNfZGVsZXRlIiwibm90aWZpY2F0aW9ucyJdfQ.OFpV1QgoPcZ5v5fapcfDrDqnz--6GOpsj618uwQxiRwBjmGNAILnVcWHr0pI4f6BQdWvnhHUmll9wreu5Y5etkvd8t3MZouTvS0bbokN1u6ff5JUhci3ZO61Zq5qmXOFMnSoe-5dnLxL9ZOcTGqWLCIq_gQFf72bXn4-HouZlJ9oH-cDhDm2bw0hNVtq-l7E-qgpzZba2_F-cm1mA9MgNNUE-dTjujdy9aGI76G7zxbHerAdPk7PewfLDQAsnujVmolFUZr01QvlpbvzgsMfS7NQI_lYeGIbV3tk-WnYrOfBJjpEtNcmbbBK0kHEG1ccVPk0yuHPC4tk5Wp4wJ_t0Q";

$payload = array(
    "data" => [
        "name" => "ZiHang Gao",
        "admin" => true
    ],
    "iss" => "http://example.org",
    "sub" => "1234567890",
);

// none algorithm
//$token = jwt_encode($payload, null, 'none');

//echo $token . PHP_EOL;
//print_r(jwt_decode($token, null, false));

$decoded_token = jwt_decode($token, $publicKey, 'none');
print_r($decoded_token);