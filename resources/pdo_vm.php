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
 Portions created by the Initial Developer are Copyright (C) 2008-2012
 the Initial Developer. All Rights Reserved.

 Contributor(s):
 Daniel Paixao <daniel@flux.net.br>
*/

//includes files
	require_once __DIR__ . "/require.php";

//get the contents of xml_cdr.conf.xml
	$conf_xml_string = file_get_contents($_SESSION['switch']['conf']['dir'].'/autoload_configs/voicemail.conf.xml');

//parse the xml to get the call detail record info
	try {
		$conf_xml = simplexml_load_string($conf_xml_string);
	}
	catch(Exception $e) {
		echo $e->getMessage();
	}

//define variables
	$odbc_dsn = '';
	$odbc_db_user = '';
	$odbc_db_pass = '';

//find the odbc info
	foreach ($conf_xml->profiles->profile->param as $row) {
		if ($row->attributes()->name == "odbc-dsn") {
			$odbc_array = explode(":", $row->attributes()->value);
			$odbc_dsn = $odbc_array[0];
			$odbc_db_user = $odbc_array[1];
			$odbc_db_pass = $odbc_array[2];
		}
	}

//database connection
	try {
		unset($db);
		if (empty($odbc_dsn)) {
			$db = new PDO('sqlite:'.$_SESSION['switch']['db']['dir'].'/voicemail_default.db'); //sqlite 3
		}
		else {
			$db = new PDO("odbc:$odbc_dsn", "$odbc_db_user", "$odbc_db_pass");
		}
	}
	catch (PDOException $e) {
	   echo 'Connection failed: ' . $e->getMessage();
	}

 ?>
