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
	Portions created by the Initial Developer are Copyright (C) 2022
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Daniel Paixao <daniel@flux.net.br>
*/

//set the include path
	$conf = glob("{/usr/local/etc,/etc}/fluxpbx/config.conf", GLOB_BRACE);
	set_include_path(parse_ini_file($conf[0])['document.root']);

//includes files
	require_once "resources/require.php";
	require_once "resources/check_auth.php";
	
//check permissions
	if (permission_exists('extension_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//get posted data
	if (is_array($_POST['search'])) {
		$search = $_POST['search'];
	}

//add the search term
	if (isset($_GET["search"])) {
		$search = strtolower($_GET["search"]);
	}

//validate the token	
	//$token = new token;
	//if (!$token->validate($_SERVER['PHP_SELF'])) {
	//	message::add($text['message-invalid_token'],'negative');
	//	header('Location: /');
	//	exit;
	//}

//include css
//	echo "<link rel='stylesheet' type='text/css' href='/resources/fontawesome/css/all.min.css.php'>\n";

//get the list of domains
	if (permission_exists('extension_all')) {
		$sql = "select * ";
		$sql .= "from v_extensions ";
		$sql .= "where true ";
		$sql .= "and enabled = 'true' \n";
		$sql .= "and domain_uuid = :domain_uuid \n";
		if (isset($search)) {
			$sql .= "	and ( ";
			$sql .= "		lower(extension) like :search ";
			$sql .= "		or lower(description) like :search ";
			$sql .= "	) ";
			$parameters['search'] = '%'.$search.'%';
		}
		$sql .= "order by extension asc ";
		$sql .= "limit 300 ";
		$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
		$database = new database;
		$extensions = $database->select($sql, $parameters, 'all');
		unset($sql, $parameters);
	}

//get the extensions
//	if (file_exists($_SERVER["PROJECT_ROOT"]."/app/flux_phone/app_config.php") && !is_cli()){
//		require_once "app/flux_phone/resources/extensions.php";
//	}

//debug information
	//print_r($extensions);

//show the extensions as json
	echo json_encode($extensions, true);

?>
