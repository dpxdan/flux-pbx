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
	Portions created by the Initial Developer are Copyright (C) 2018
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Daniel Paixao <daniel@flux.net.br>
*/

$conf = glob("{/usr/local/etc,/etc}/fluxpbx/config.conf", GLOB_BRACE);
set_include_path(parse_ini_file($conf[0])['document.root']);
	require_once "resources/require.php";
	require_once "app/api/resources/functions/restapi_functions.php";

//default authorized to false
	$authorized = false;

//get the user settings
	$sql = "select vu.user_uuid, u.contact_uuid, vu.domain_uuid from v_user_settings as vu, v_users as u ";
	$sql .= "where user_setting_category = 'message' ";
	$sql .= "and user_setting_subcategory = 'key' ";
	$sql .= "and user_setting_value = :user_setting_value ";
	$sql .= "and vu.user_uuid = u.user_uuid ";
	$sql .= "and user_setting_enabled = 'true' ";
	$parameters['user_setting_value'] = $_GET['key'];
	$database = new database;
	$row = $database->select($sql, $parameters, 'row');
	if (is_array($row) && @sizeof($row) != 0 && is_uuid($row['user_uuid'])) {
		$domain_uuid = $row['domain_uuid'];
		$user_uuid = $row['user_uuid'];
		$contact_user = $row['contact_uuid'];
		$authorized = true;
	}
	unset($sql, $parameters, $row);

//authorization failed
	if (!$authorized) {
		//log the failed auth attempt to the system, to be available for fail2ban.
			openlog('FluxPBX', LOG_NDELAY, LOG_AUTH);
			syslog(LOG_WARNING, '['.$_SERVER['REMOTE_ADDR']."] authentication failed for ".$_SERVER['PHPSESSID']);
			closelog();

		//send http 404
			header("HTTP/1.0 404 Not Found");
			echo "<html>\n";
			echo "<head><title>404 Not Found</title></head>\n";
			echo "<body bgcolor=\"white\">\n";
			echo "<center><h1>404 Not Found</h1></center>\n";
			echo "<hr><center>nginx/1.12.1</center>\n";
//			print_r($_SERVER);
			echo "</body>\n";
			echo "</html>\n";
			exit();
	}

//get the raw input data
	$json = file_get_contents('php://input');

//decode the json into array
	$message = json_decode($json, true);

//get the source phone number
	$phone_number = preg_replace('{[\D]}', '', $message['from']);
	$phone_to = preg_replace('{[\D]}', '', $message['to']);

//get the contact uuid
	$sql = "select c.contact_uuid ";
	$sql .= "from v_contacts as c, v_contact_phones as p ";
	$sql .= "where p.contact_uuid = c.contact_uuid ";
	$sql .= "and p.phone_number = :phone_number ";
	$sql .= "and c.domain_uuid = :domain_uuid ";
	$parameters['phone_number'] = $phone_to;
	$parameters['domain_uuid'] = $domain_uuid;
	$database = new database;
	$contact_uuid = $database->select($sql, $parameters, 'column');
	unset($sql, $parameters);

//build message array
	$message_uuid = uuid();
	$array['messages'][0]['message_uuid'] = $message_uuid;
	$array['messages'][0]['domain_uuid'] = $domain_uuid;
	$array['messages'][0]['user_uuid'] = $user_uuid;
	$array['messages'][0]['contact_uuid'] = $contact_uuid;
//	$array['messages'][0]['message_uuid'] = $message_uuid;
	$array['messages'][0]['message_type'] = is_array($message['media']) ? 'mms' : 'chat';
	$array['messages'][0]['message_direction'] = 'inbound';
	$array['messages'][0]['message_date'] = 'now()';
	$array['messages'][0]['message_from'] = $message['from'];
	$array['messages'][0]['message_to'] = $message['to'];
	$array['messages'][0]['message_uuid_from'] = $contact_user;
	$array['messages'][0]['message_uuid_to'] = $contact_uuid;
	$array['messages'][0]['user_uuid_from'] = $user_uuid;
//	$array['messages'][0]['user_uuid_to'] = $contact_uuid;
	$array['messages'][0]['message_text'] = $message['text'];
	$array['messages'][0]['message_json'] = $json;

//add the required permission
	$p = new permissions;
	$p->add("message_add", "temp");

//build message media array (if necessary)
	if (is_array($message['media'])) {
		foreach($message['media'] as $index => $media_url) {
			$media_type = pathinfo($media_url, PATHINFO_EXTENSION);
			if ($media_type !== 'xml') {
				$array['message_media'][$index]['message_media_uuid'] = uuid();
				$array['message_media'][$index]['message_uuid'] = $message_uuid;
				$array['message_media'][$index]['domain_uuid'] = $domain_uuid;
				$array['message_media'][$index]['user_uuid'] = $user_uuid;
				$array['message_media'][$index]['message_media_type'] = $media_type;
				$array['message_media'][$index]['message_media_url'] = $media_url;
				$array['message_media'][$index]['message_media_content'] = base64_encode(file_get_contents($media_url));
			}
		}

		$p->add("message_media_add", "temp");
	}

//save message to the database
	$database = new database;
	$database->app_name = 'messages';
	$database->app_uuid = '4a20815d-042c-47c8-85df-085333e79b87';
	$database->save($array);
	$result = $database->message;
//	send_api_message($database->message['code'], $database->message['message']);

//remove the temporary permission
	$p->delete("message_add", "temp");
	$p->delete("message_media_add", "temp");

//convert the array to json
	$array_json = json_encode($array);

//get the list of extensions using the user_uuid
	$sql = "select * from v_domains as d, v_extensions as e ";
	$sql .= "where extension_uuid in ( ";
	$sql .= "	select extension_uuid ";
	$sql .= "	from v_extension_users ";
	$sql .= "	where user_uuid = :user_uuid ";
	$sql .= ") ";
	$sql .= "and e.domain_uuid = d.domain_uuid ";
	$sql .= "and e.enabled = 'true' ";
	$parameters['user_uuid'] = $user_uuid;
	$database = new database;
	$extensions = $database->select($sql, $parameters, 'all');
	$getsql = $sql;
	$getparameters = $parameters;
	unset($sql, $parameters);

//create the event socket connection
	if (is_array($extensions)) {
		$fp = event_socket_create($_SESSION['event_socket_ip_address'], $_SESSION['event_socket_port'], $_SESSION['event_socket_password']);
	}

//send the sip message
	if (is_array($extensions) && @sizeof($extensions) != 0) {
		foreach ($extensions as $row) {
			$domain_name = $row['domain_name'];
			$extension = $row['extension'];
			$number_alias = $row['number_alias'];

			//send the sip messages
			$command = "luarun app/messages/resources/send.lua ".$message["from"]."@".$domain_name." ".$extension."@".$domain_name."  '".$message["text"]."'";

			//send the command
			$response = event_socket_request($fp, "api ".$command);
			$response = event_socket_request($fp, "api log notice ".$command);
		}
	}
	unset($extensions, $row);

//set the file
	$file = '/tmp/sms.txt';

//save the file
	file_put_contents($file, $json);

//save the data to the file system
	file_put_contents($file, $json."\n");
	file_put_contents($file, $array_json."\nfrom: ".$message["from"]." to: ".$message["to"]." text: ".$message["text"]."\n$sql_test\njson: ".$json."\n".$array_json."\n");
	
	header("Content-Type: application/json; charset=UTF-8");
	http_response_code(200);
	echo json_encode($json);
	
	
//	send_data($array_json);

?>
