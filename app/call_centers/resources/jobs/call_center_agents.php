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
		$hostname = urldecode($_GET['hostname']);
		$debug = "true";
//		$debug = $_GET['debug'];
		$sleep_seconds = $_GET['sleep'];
	}
	else {
		//invalid uuid
		exit;
	}

//connect to event socket
	$fp = event_socket_create($_SESSION['event_socket_ip_address'], $_SESSION['event_socket_port'], $_SESSION['event_socket_password']);

//get the agent list from event socket
	$switch_cmd = 'callcenter_config agent list';
	$event_socket_str = trim(event_socket_request($fp, 'api '.$switch_cmd));
	$agent_list = csv_to_named_array($event_socket_str, '|');

//get the agents from the database
	$sql = "select a.*, d.domain_name \n";
	$sql .= "from view_agents_xml as a, v_domains as d \n";
	$sql .= "where a.domain_uuid = d.domain_uuid \n";
	$sql .= "order by agent_name asc \n";
	//echo $sql;
	$database = new database;
	$agents = $database->select($sql, $parameters, 'all');
	unset($sql, $parameters);

	//get the extensions from the database
		$sql = "select a.*, d.domain_name, u.user_status, \n";
		$sql .= "e.extension, e.extension_uuid \n";
		$sql .= "from v_call_center_agents as a, v_domains as d, v_users as u, v_extension_users as eu, v_extensions as e \n";
		$sql .= "where a.domain_uuid = d.domain_uuid \n";
		$sql .= "and a.user_uuid = eu.user_uuid \n";
		$sql .= "and u.user_uuid = eu.user_uuid \n";
		$sql .= "and eu.extension_uuid = e.extension_uuid \n";
		$sql .= "order by extension asc \n";
		echo $sql;
		$database = new database;
		$extensions = $database->select($sql, $parameters, 'all');
		unset($sql, $parameters);

//view_array($results);
	foreach($agents as $row) {

		//update the agent status
		if (is_array($agent_list)) {
			foreach ($agent_list as $r) {
				
				if ($r['name'] == $row['call_center_agent_uuid']) {
					$agent_status = $r['status'];
				}
				foreach ($extensions as $e) {
				if ($e['call_center_agent_uuid'] == $row['call_center_agent_uuid']) {
									$agent_extension_uuid = $e['extension_uuid'];
									$agent_extension = $e['extension'];
								}
				}
			}
		}

		//answer_state options: confirmed, early, and terminated
		if ($agent_status == 'Available') {
			$answer_state = 'confirmed';
		}
		else {
			$answer_state = 'terminated';
		}

		//build the event
		if ($fp) {
			$event = "sendevent PRESENCE_IN\n";
			$event .= "proto: agent\n";
			$event .= "from: ".$agent_extension."@".$row['domain_name']."\n";
			$event .= "login: ".$row['call_center_agent_uuid']."@".$row['domain_name']."\n";
			$event .= "status: Active (1 waiting)\n";
			$event .= "rpid: unknown\n";
			$event .= "event_type: presence\n";
			$event .= "alt_event_type: dialog\n";
			$event .= "event_count: 1\n";
			$event .= "unique-id: ".$agent_extension_uuid."\n";
			$event .= "Presence-Call-Direction: outbound\n";
			$event .= "answer-state: ".$answer_state."\n";
		}

		if ($fp) {
		//send unblock event
			$eventstatus = "sendevent CUSTOM\n";
			$eventstatus .= "Event-Name: CUSTOM\n";
			$eventstatus .= "Event-Subclass: callcenter::info\n";
			$eventstatus .= "CC-Agent: ".$row['call_center_agent_uuid']."@".$row['domain_name']."\n";
			$eventstatus .= "CC-Action: agent-status-change\n";
			$eventstatus .= "CC-Agent-Status: ".$agent_status."\n";
			}

		//send message to the console
		//if (isset($debug)) {
			echo "\n";
			echo "[presence][call_center] agent+".$row['call_center_agent_uuid']." agent_status ".$agent_status." answer_state ".$answer_state."\n";
		//}

		//send the event
		$result = event_socket_request($fp, $event);
//		if (isset($debug)) {
			print_r($result, false);
//		}
		$resultstatus = event_socket_request($fp, $eventstatus);
//		if (isset($debug)) {
			print_r($resultstatus, false);
//		}

	}

	//close event socket connection
	fclose($fp);

        session_unset();
        session_destroy();
/*
* * * * * cd /var/www/html/fluxpbx && php /var/www/html/fluxpbx/app/call_centers/resources/jobs/call_center_agents.php
*/

?>
