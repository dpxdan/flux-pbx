<?php
ini_set('display_errors', 'Off');
header('Content-Type: application/json');


	$conf = glob("{/usr/local/etc,/etc}/fluxpbx/config.conf", GLOB_BRACE);
	set_include_path(parse_ini_file($conf[0])['document.root']);

   $php_version = phpversion();
   
   if ($php_version > 8.0) {
   require_once "resources/require.php";
   
   } else if ($php_version < 7.9) {

	require_once "resources/require.php";
	}
	else {
	exit;
	
	}
//	require_once "resources/check_auth.php";
	
	
#include 'security.php';

$domain_uuid = (isset($_GET['domain_uuid']) ? $_GET['domain_uuid']:null);
$extension = (isset($_GET['extension']) ? $_GET['extension']:null);
$password = (isset($_GET['password']) ? $_GET['password']:null);
$message = array(['message' => 'Missing info!']);
define('KEY_SECURE','h5r@mg7$#ueqdstj');
/*Show All Extensions across all domains*/
if ($domain_uuid == null and $extension == null) {
    $sql = "select domain_uuid from v_extensions ";
    $prep_statement = $db->prepare(check_sql($sql));
    $prep_statement->execute();
    $extensions = $prep_statement->fetchAll(PDO::FETCH_NAMED);
    unset($sql);

    $domains = array();
    foreach ($extensions as $extension) {
        if (!in_array($extension['domain_uuid'], $domains)) {

            $sql = "select extension_uuid,domain_uuid,extension,effective_caller_id_name,effective_caller_id_number,outbound_caller_id_name,outbound_caller_id_number from v_extensions where domain_uuid = '$extension[domain_uuid]'";
            $prep_statement = $db->prepare(check_sql($sql));
            $prep_statement->execute();
            $domainExtensions = $prep_statement->fetchAll(PDO::FETCH_NAMED);
            unset($sql);

            array_push($domains, [
                'domain_uuid' => $extension['domain_uuid'],
                'extensions' => $domainExtensions,
            ]);

        }
    }

    $message = ['domains' => $domains];
}

/*Show All extensions for a domain*/
if ($extension == null and $domain_uuid !== null) {
    $sql = "select extension_uuid,domain_uuid,extension,effective_caller_id_name,effective_caller_id_number,outbound_caller_id_name,outbound_caller_id_number from v_extensions where domain_uuid = '$domain_uuid' ";
    $prep_statement = $db->prepare(check_sql($sql));
    $prep_statement->execute();
    $result = $prep_statement->fetchAll(PDO::FETCH_NAMED);
    unset($sql);
    $message = $result;
}

/*Show an single extension under a specific domain*/
if ($extension !== null and $domain_uuid !== null) {
    $sql = "select extension_uuid,domain_uuid,extension,effective_caller_id_name,effective_caller_id_number,outbound_caller_id_name,outbound_caller_id_number from v_extensions ";
    $sql .= "where domain_uuid = '$domain_uuid' and extension = '$extension'";
    $prep_statement = $db->prepare(check_sql($sql));
    $prep_statement->execute();
    $result = $prep_statement->fetchAll(PDO::FETCH_NAMED);
    unset($sql);
    $message = $result;
}

if ($extension !== null and $domain_uuid !== null and $password !== null) {
	#$password = Security::decrypt($password, KEY_SECURE);
    $sql = "select extension_uuid,domain_uuid,extension,effective_caller_id_name,effective_caller_id_number,outbound_caller_id_name,outbound_caller_id_number from v_extensions ";
    $sql .= "where domain_uuid = '$domain_uuid' and extension = '$extension' and password = '$password'";
    $prep_statement = $db->prepare(check_sql($sql));
    $prep_statement->execute();
    $result = $prep_statement->fetchAll(PDO::FETCH_NAMED);
	$status='success';
	if(!$result){
		$status='failed';
	}
    unset($sql);
    $message = array('result'=>$status,'extension'=>$result[0]);
}

echo(json_encode($message));

