<?php
/*
	FluxPBX
	Version: MPL 1.1

	The contents of this file are subject to the Mozilla Public License Version
	1.1 (the "License"); you may not use this file except in compliance with
	the License. You may obtain a copy of the License at
	http://www.mozilla.org/MPL/

	Software distributed under the License is distributed on an "AS IS" basis,
	WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
	for the specific language governing rights and limitations under the
	License.

	The Original Code is FluxPBX

	The Initial Developer of the Original Code is
	Daniel Paixao <daniel@flux.net.br>
	Portions created by the Initial Developer are Copyright (C) 2008-2023
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Daniel Paixao <daniel@flux.net.br>
*/

//includes files
    require_once __DIR__ . "/require.php";

//add multi-lingual support
	$language = new text;
	$text = $language->get(null, 'resources');

//for compatibility require this library if less than version 5.5
	if (version_compare(phpversion(), '5.5', '<')) {
		require_once "resources/functions/password.php";
	}

//start the session
	if (function_exists('session_start')) {
		if (!isset($_SESSION)) {
			session_start();
		}
	}

//regenerate sessions to avoid session id attacks such as session fixation
	if (array_key_exists('security',$_SESSION) && $_SESSION['security']['session_rotate']['boolean'] == "true") {
		$_SESSION['session']['last_activity'] = time();
		if (!isset($_SESSION['session']['created'])) {
			$_SESSION['session']['created'] = time();
		} else if (time() - $_SESSION['session']['created'] > 28800) {
			// session started more than 8 hours ago
			session_regenerate_id(true);    // rotate the session id
			$_SESSION['session']['created'] = time();  // update creation time
		}
	}

//set the domains session
	if (!isset($_SESSION['domains'])) {
		$domain = new domains();
		$domain->session();
		$domain->set();
	}

//set the domain_uuid variable from the session
	if (!empty($_SESSION["domain_uuid"])) {
		$domain_uuid = $_SESSION["domain_uuid"];
	}

//define variables
	if (!isset($_SESSION['template_content'])) { $_SESSION["template_content"] = null; }

//if session authorized is not set then set the default value to false
	if (!isset($_SESSION['authorized'])) {
		$_SESSION['authorized'] = false;
	}

//session validate: use HTTP_USER_AGENT as a default value
	if (!isset($conf['session.validate'])) {
		$conf['session.validate'][] = 'HTTP_USER_AGENT';
	}

//session validate: prepare the server array
	foreach($conf['session.validate'] as $name) {
		$server_array[$name] = $_SERVER[$name];
	}
	unset($name);

//session validate: check to see if the session is valid
	if ($_SESSION['authorized'] && $_SESSION["user_hash"] !== hash('sha256', implode($server_array))) {
		session_destroy();
		header("Location: ".PROJECT_PATH."/logout.php");
	}

//if the session is not authorized then verify the identity
	if (!$_SESSION['authorized']) {

		//clear the menu
			unset($_SESSION["menu"]);

		//clear the template only if the template has not been assigned by the superadmin
			if (empty($_SESSION['domain']['template']['name'])) {
				$_SESSION["template_content"] = '';
			}

		//validate the username and password
			$auth = new authentication;
			$result = $auth->validate();

		//if not authorized
			if (empty($_SESSION['authorized']) || !$_SESSION['authorized']) {

				//log the failed auth attempt to the system to the syslog server
					openlog('FluxPBX', LOG_NDELAY, LOG_AUTH);
					syslog(LOG_WARNING, '['.$_SERVER['REMOTE_ADDR']."] authentication failed for ".$result["username"]);
					closelog();

				//redirect the user to the login page
					$target_path = ($_REQUEST["path"] != '') ? $_REQUEST["path"] : $_SERVER["PHP_SELF"];
					message::add($text['message-invalid_credentials'], 'negative');
					header("Location: ".PROJECT_PATH."/?path=".urlencode($target_path));
					exit;
			}

		//get the extensions that are assigned to this user
			if (file_exists($_SERVER["PROJECT_ROOT"]."/app/extensions/app_config.php")) {
				if (isset($_SESSION["user"]) && is_uuid($_SESSION["user_uuid"]) && is_uuid($_SESSION["domain_uuid"]) && !isset($_SESSION['user']['extension'])) {
						//get the user extension list
						$_SESSION['user']['extension'] = null;
						$sql = "select ";
						$sql .= "e.extension_uuid, ";
						$sql .= "e.extension, ";
						$sql .= "e.number_alias, ";
						$sql .= "e.user_context, ";
						$sql .= "e.outbound_caller_id_name, ";
						$sql .= "e.outbound_caller_id_number, ";
						$sql .= "e.description ";
						$sql .= "from ";
						$sql .= "v_extension_users as u, ";
						$sql .= "v_extensions as e ";
						$sql .= "where ";
						$sql .= "e.domain_uuid = :domain_uuid ";
						$sql .= "and e.extension_uuid = u.extension_uuid ";
						$sql .= "and u.user_uuid = :user_uuid ";
						$sql .= "and e.enabled = 'true' ";
						$sql .= "order by ";
						$sql .= "e.extension asc ";
						$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
						$parameters['user_uuid'] = $_SESSION['user_uuid'];
						$database = new database;
						$result = $database->select($sql, $parameters, 'all');
						if (is_array($result) && @sizeof($result) != 0) {
							foreach($result as $x => $row) {
								//set the destination
								$destination = $row['extension'];
								if (strlen($row['number_alias']) > 0) {
									$destination = $row['number_alias'];
								}

								//build the user array
								$_SESSION['user']['extension'][$x]['user'] = $row['extension'];
								$_SESSION['user']['extension'][$x]['number_alias'] = $row['number_alias'];
								$_SESSION['user']['extension'][$x]['destination'] = $destination;
								$_SESSION['user']['extension'][$x]['extension_uuid'] = $row['extension_uuid'];
								$_SESSION['user']['extension'][$x]['outbound_caller_id_name'] = $row['outbound_caller_id_name'];
								$_SESSION['user']['extension'][$x]['outbound_caller_id_number'] = $row['outbound_caller_id_number'];
								$_SESSION['user']['extension'][$x]['user_context'] = $row['user_context'];
								$_SESSION['user']['extension'][$x]['description'] = $row['description'];

								//set the context
								$_SESSION['user']['user_context'] = $row["user_context"];
								$_SESSION['user_context'] = $row["user_context"];
								$_SESSION['user_session_id'] = $_SESSION['session']['user_session_id'];
							}
						}
						unset($sql, $parameters, $result, $row);
				}
			}


		//if logged in, redirect to login destination
			if (!isset($_REQUEST["key"])) {
				if (isset($_SESSION['redirect_path'])) {
					$redirect_path = $_SESSION['redirect_path'];
					unset($_SESSION['redirect_path']);
					// prevent open redirect attacks. redirect url shouldn't contain a hostname
					$parsed_url = parse_url($redirect_path);
					if ($parsed_url['host']) {
						die("Was someone trying to hack you?");
					}
					header("Location: ".$redirect_path);
				}
				elseif (isset($_SESSION['login']['destination']['text'])) {
					header("Location: ".$_SESSION['login']['destination']['text']);
				}
				elseif (file_exists($_SERVER["PROJECT_ROOT"]."/core/dashboard/app_config.php")) {
					header("Location: ".PROJECT_PATH."/core/dashboard/");
				}
				else {
					require_once "resources/header.php";
					require_once "resources/footer.php";
				}
			}

	}

?>
