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
		exit;
	}

//increase limits
	set_time_limit(300);
	ini_set('max_execution_time',300); //5 minutes
	ini_set('memory_limit', '256M');

//save the arguments to variables
	$script_name = $argv[0];
	if (!empty($argv[1])) {
		parse_str($argv[1], $_GET);
	}

//get the primary key
	if (Is_array($_GET)) {
		$domain_uuid = urldecode($_GET['domain_uuid']);
		$extension = $_GET['extension'];
	}
	else {
		//invalid uuid
		exit;
	}
$password_length = '20';
$password_strength = '4';
$password = generate_password($password_length, $password_strength);

$db_columns = "extension_uuid, domain_uuid, extension, number_alias, password, accountcode, max_registrations, limit_max, user_context, call_group, enabled, limit_destination,effective_caller_id_name,effective_caller_id_number,outbound_caller_id_name,outbound_caller_id_number";
//$os = (isset($_GET['os']) ? $_GET['os'] : null);
if ($domain_uuid !== null and $extension !== null) {
    $sql = "select domain_name ";
		$sql .= "from v_domains ";
		$sql .= "where domain_uuid = :domain_uuid ";
		$sql .= "order by domain_name asc limit 1 ";
		$parameters['domain_uuid'] = $domain_uuid;
		$database = new database;
		$row = $database->select($sql, $parameters, 'row');
		if (is_array($row)) {
			$domain_name = $row['domain_name'];
		}
		unset($sql, $parameters, $row);
    
    
    $sql = "select * from v_extensions where extension = '".$extension."' and domain_uuid = '".$domain_uuid."'";
    $prep_statement = $db->prepare(check_sql($sql));
    $prep_statement->execute();
    $result = $prep_statement->fetchAll(PDO::FETCH_NAMED);
    unset($sql);
    if (sizeof($result) == 0) {
        $sql = "INSERT INTO v_extensions (".$db_columns.") VALUES(gen_random_uuid(), :domain_uuid, :extension, :extension, '".$password."', :extension, '3', '5', '".$domain_name."', 'api', 'true', '!USER_BUSY', :extension, :extension, :extension, :extension)";
    } else {
        $sql = "UPDATE v_extensions set number_alias = :extension where extension = :extension and domain_uuid = :domain_uuid";
    }
    $prep_statement = $db->prepare($sql);
    $prep_statement->execute(['extension' => $extension, 'domain_uuid' => $domain_uuid]);
}

?>


