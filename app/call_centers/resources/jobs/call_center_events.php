<?php
/*
	Copyright (C) 2022 Daniel Paixao <daniel@flux.net.br>

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:
	1. Redistributions of source code must retain the above copyright notice,
	   this list of conditions and the following disclaimer.
	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
	AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
	AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
	SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
	INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
	CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
	POSSIBILITY OF SUCH DAMAGE.
*/

//check the permission
	if (defined('STDIN')) {
		//set the include path
		$conf = glob("{/usr/local/etc,/etc}/fluxpbx/config.conf", GLOB_BRACE);
		set_include_path(parse_ini_file($conf[0])['document.root']);

		//includes files
		require_once "resources/require.php";
	}
	else {
		//only allow running this from command line
		exit;
	}

//increase limits
	set_time_limit(0);
	ini_set('max_execution_time', 0);
	ini_set('memory_limit', '256M');

//save the arguments to variables
	$script_name = $argv[0];
	if (!empty($argv[1])) {
		parse_str($argv[1], $_GET);
	}

//set the variables
	if (isset($_GET['hostname'])) {
		$hostname = urldecode($_GET['hostname']);
	}
	
	if (isset($_GET['debug'])) {
		if (is_numeric($_GET['debug'])) {
			$debug = $_GET['debug'];
		}
//		$debug = "true";
	}

   $debug = "false";
//   $debug_level = 2;
//get the hostname
	if (!isset($hostname)) {
		$hostname = gethostname();
	}


//set the event socket variables
	$event_socket_ip_address = $_SESSION['event_socket_ip_address'];
	$event_socket_port = $_SESSION['event_socket_port'];
	$event_socket_password = $_SESSION['event_socket_password'];

//end the session
	session_destroy();

//connect to event socket
	$socket = new event_socket;
	if (!$socket->connect($event_socket_ip_address, $event_socket_port, $event_socket_password)) {
		echo "Unable to connect to event socket\n";
	}


//loop through the switch events
	$cmd = "event json ALL";
	$result = $socket->request($cmd);
	if ($debug) { print_r($result); }

	//filter for specific events
	$cmd = "filter Event-Name CUSTOM";
	$result = $socket->request($cmd);
	if ($debug) { print_r($result); }

	while (true) {

		//check pending unblock requests
		/*
		if ((time() - $previous_time) > $interval_seconds) {
			//debug info
			if ($debug) {
				echo "time difference: ". (time() - $previous_time)."\n";
			}


			[Event-Subclass] => callcenter::info
			[Event-Name] => CUSTOM
			[Event-Date-Local] => 2023-03-12 21:52:12
			[CC-Queue] => 9010@dev2.flux.net.br
			[CC-Action] => bridge-agent-fail
			[CC-Hangup-Cause] => USER_NOT_REGISTERED
			[CC-Agent] => dc835435-d239-44da-9be4-434a7836d445
			[CC-Member-UUID] => 292430d0-cf12-4f36-bfd4-16cddf4919ff
			[CC-Member-Session-UUID] => a94349ad-c80f-4155-9e63-403f594bd7e0
			[CC-Member-CID-Name] => Cliente Teste
			[CC-Member-CID-Number] => 1131989070

			echo "event_date: ".$json_array['Event-Date-Local']."\n";
			echo "event_type: ".$json_array['CC-Member-Session-UUID']."\n";
			echo "event_agent: ".$json_array['CC-Agent']."\n";
			echo "event_action: ".$json_array['CC-Action']."\n";
			echo "event_action: ".$json_array['CC-Queue']."\n";
			echo "event_status: ".$json_array['CC-Hangup-Cause']."\n";
			echo "event_type: ".$json_array['CC-Member-CID-Name']."\n";
			echo "event_type: ".$json_array['CC-Member-CID-Number']."\n";
			echo "event_subclass: ".$json_array['CC-Member-UUID']."\n";

			//update the time
			$previous_time = time();
		}
		*/

		//reconnect to event socket
		if (!$socket->connected()) {
			//echo "Not connected to even socket\n";
			if ($socket->connect($event_socket_ip_address, $event_socket_port, $event_socket_password)) {
				$cmd = "event json ALL";
				$result = $socket->request($cmd);
				if ($debug) { print_r($result); }

				$cmd = "filter Event-Name CUSTOM";
				$result = $socket->request($cmd);
				if ($debug) { print_r($result); }
				echo "Re-connected to event socket\n";
			}
			else {
				//unable to connect to event socket
				echo "Unable to connect to event socket\n";

				//sleep and then attempt to reconnect
				sleep(1);
				continue;
			}
		}

		//read the socket
		$json_response = $socket->read_event();

		//decode the response
		if (isset($json_response) && $json_response != '') {
			$json_array = json_decode($json_response['$'], true);
			unset($json_response);
		}

		//debug info
		if ($debug) { 
			print_r($json_array);
		}

		if (is_array($json_array) && $json_array['Event-Subclass'] == 'callcenter::info') {
						//not registered so block the address
						$action = $json_array['CC-Action'];
						if ($action == 'agent-status-change') {
/*							echo "\n";
							echo "event_date: ".$json_array['Event-Date-Local']."\n";
							echo "event_name: ".$json_array['Event-Name']."\n";
							echo "event_type: ".$json_array['Event-Calling-Function']."\n";
							echo "event_subclass: ".$json_array['Event-Subclass']."\n";
							echo "event_agent: ".$json_array['CC-Agent']."\n";
							echo "event_action: ".$json_array['CC-Action']."\n";
							echo "event_status: ".$json_array['CC-Agent-Status']."\n";							
							echo "\n";*/
//						print_r($json_array);
							$agent_uuid = $json_array['CC-Agent'];
							$agent_status = $json_array['CC-Agent-Status'];
//							echo "event_status: ".$agent_status."\n";	
	//						echo "agent_uuid: ".$agent_uuid."\n";	
							
							$log_status = log_status($agent_uuid,$action,$agent_status);
							
							
							
						}
						if ($action == 'agent-custom-status-change') {
						              $agent_uuid = $json_array['CC-Agent'];
													$agent_status = $json_array['CC-Agent-Custom-Status'];
/*													echo "\n";
													echo "event_date: ".$json_array['Event-Date-Local']."\n";
													echo "event_name: ".$json_array['Event-Name']."\n";
													echo "event_type: ".$json_array['Event-Calling-Function']."\n";
													echo "event_subclass: ".$json_array['Event-Subclass']."\n";
													echo "event_agent: ".$json_array['CC-Agent']."\n";
													echo "event_action: ".$json_array['CC-Action']."\n";
													echo "event_status: ".$json_array['CC-Agent-Custom-Status']."\n";							
													echo "\n";*/
													$log_status = log_status($agent_uuid,$action,$agent_status);
													//print_r($json_array);
													
												}
						if ($action == 'bridge-agent-fail') {
						$agent_uuid = $json_array['CC-Agent'];
						$hangup_status = $json_array['CC-Hangup-Cause'];
/*						echo "\n";
						echo "event_date: ".$json_array['Event-Date-Local']."\n";
						echo "event_type: ".$json_array['CC-Member-Session-UUID']."\n";
						echo "event_agent: ".$json_array['CC-Agent']."\n";
						echo "event_action: ".$json_array['CC-Action']."\n";
						echo "event_action: ".$json_array['CC-Queue']."\n";
						echo "event_status: ".$json_array['CC-Hangup-Cause']."\n";
						echo "event_type: ".$json_array['CC-Member-CID-Name']."\n";
						echo "event_type: ".$json_array['CC-Member-CID-Number']."\n";
						echo "event_subclass: ".$json_array['CC-Member-UUID']."\n";
						echo "\n";*/
						$log_status = log_status($agent_uuid,$hangup_status);
							}
						if ($action == 'bridge-agent-start') {
													//print_r($json_array);
														}
					}

		//unset the array
		if (is_array($json_array)) {
			unset($json_array);
		}

		//debug info
		if ($debug && $debug_level == '2') {
			//current memory
			$memory_usage = memory_get_usage();

			//peak memory
			$memory_peak = memory_get_peak_usage();
			echo "\n";
			echo 'Current memory: ' . round($memory_usage / 1024) . " KB\n";
			echo 'Peak memory: ' . round($memory_peak / 1024) . " KB\n\n";
			echo "\n";
		}

	}

	

function log_status($agent_uuid,$action,$status) {
	
	//get the agents from the database
		$sql = "select * from view_agents_xml ";
		$sql .= "where call_center_agent_uuid = :agent_uuid ";
		$sql .= "order by cc_agent_name asc ";
		$parameters['agent_uuid'] = $agent_uuid;
		$database = new database;
		$agents = $database->select($sql, $parameters, 'all');
		unset($sql, $parameters);

		if (is_array($agents)) {
			foreach ($agents as $row) {
			$domain_uuid = $row['domain_uuid'];
			$user_uuid = $row['user_uuid'];
			$agent_name = $row['agent_name'];
			$agent_id = $row['agent_id'];
			$agent_uuid = $row['call_center_agent_uuid'];
//			$agent_status = $status;
				  $result['user_log_uuid'] = uuid();
					$result['user_uuid'] = $user_uuid;
					$result['user_status'] = $agent_status;
					$result['timestamp'] = 'now()';
					$result['domain_uuid'] = $domain_uuid;
					$result['username'] = $agent_name;
					$result['remote_address'] = '127.0.0.1';
					$result['user_agent'] = 'Aplicativo Call Center';
					$result['insert_user'] = $agent_uuid;
					$result['update_user'] = $agent_uuid;
					$result["authorized"] = 'true';
					$result['type'] = $action;
				
					if (file_exists($_SERVER["PROJECT_ROOT"]."/core/user_logs/app_config.php")) {
						user_logs::add($result);
					}
			}
		}
					    
	

			
	
			//send debug information to the console
			if ($debug) {
				echo "blocked address ".$ip_address .", line ".__line__."\n";
			}
	
			//unset the array
			unset($event);
		}


?>
