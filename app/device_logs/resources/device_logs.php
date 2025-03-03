<?php
/*
	Copyright (c) 2019-2022 Daniel Paixao <daniel@flux.net.br>
	
	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions
	are met:

		1. Redistributions of source code must retain the above copyright
		notice, this list of conditions and the following disclaimer.
	
		2. Redistributions in binary form must reproduce the above copyright
		notice, this list of conditions and the following disclaimer in the
		documentation and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS "AS IS" AND
	ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
	IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
	ARE DISCLAIMED.  IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE
	FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
	DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
	OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
	HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
	LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
	OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
	SUCH DAMAGE.
*/

//add the device_logs
	//if (__FILE__ == realpath($argv[0])) {

		//start with an empty array
			if (isset($array)) {
				unset($array);
			}

		//prepare the array
			$array['device_logs'][0]['device_log_uuid'] = uuid();
			$array['device_logs'][0]['domain_uuid'] = $_SESSION['domain_uuid'];
			$array['device_logs'][0]['device_uuid'] = $device_uuid;
			$array['device_logs'][0]['timestamp'] = 'now';
			$array['device_logs'][0]['device_address'] = $device_address;
			$array['device_logs'][0]['request_scheme'] = $_SERVER['REQUEST_SCHEME'];
			$array['device_logs'][0]['http_host'] = $_SERVER['HTTP_HOST'];
			$array['device_logs'][0]['server_port'] = $_SERVER['SERVER_PORT'];
			$array['device_logs'][0]['server_protocol'] = $_SERVER['SERVER_PROTOCOL'];
			$array['device_logs'][0]['query_string'] = $_SERVER['QUERY_STRING'];
			$array['device_logs'][0]['remote_address'] = $_SERVER['REMOTE_ADDR'];
			$array['device_logs'][0]['http_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
			$array['device_logs'][0]['http_status'] = 'OK';
			$array['device_logs'][0]['http_status_code'] = '200';
			$array['device_logs'][0]['http_content_body'] = $file_contents;

		//grant temporary permissions
			$p = new permissions;
			$p->add('device_log_add', 'temp');
			//$p->add('device_log_edit', 'temp');

		//save the data
			$database = new database;
			$database->app_name = 'device logs';
			$database->app_uuid = '78b1e5c7-5028-43e7-a05b-a36b44f87087';
			$database->save($array, false);
			//$message = $database->message;
			unset($array);

		//grant temporary permissions
			$p = new permissions;
			$p->delete('device_log_add', 'temp');
			//$p->delete('device_log_edit', 'temp');

	//}

?>
