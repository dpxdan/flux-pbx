<?php

header('Content-Type: application/json');

	$conf = glob("{/usr/local/etc,/etc}/fluxpbx/config.conf", GLOB_BRACE);
	set_include_path(parse_ini_file($conf[0])['document.root']);

	require_once "resources/require.php";
//	require_once "resources/check_auth.php";

$message = array();


$sql = "select * from v_domains";
$prep_statement = $db->prepare(check_sql($sql));
$prep_statement->execute();
$domains = $prep_statement->fetchAll(PDO::FETCH_NAMED);
unset($sql, $prep_statement);
$message = ['domains' => $domains];
echo(json_encode($message));