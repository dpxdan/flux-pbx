<?php

$host     = '127.0.0.1';
$db       = 'fluxdev6';
$port     = '5432';
$user     = 'fluxpbx';
$password = 'MDJJlsugl2eKVWQ4QLIp9u9W8';

//$dsn = "pgsql:host=$host;port=$port;dbname=$db;charset=UTF8";

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$db;user=$user;password=$password");

} catch (PDOException $e) {
     echo $e->getMessage();
}
